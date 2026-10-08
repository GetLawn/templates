<?php
/** Checks the installed release, pending jobs, and stored migration results. */
declare(strict_types=1);

/** Reads the release recorded by the worker; a missing file means no result yet. */
function readVersion(string $path): ?string {
    if (!is_file($path)) {
        return null;
    }
    $version = file_get_contents($path);
    if ($version === false) {
        throw new RuntimeException('Could not read background job status.');
    }
    return trim($version);
}

try {
    require '/var/www/html/config/config.php';
    require '/var/www/html/version.php';
    if (!($CONFIG['installed'] ?? false) || ($CONFIG['maintenance'] ?? false)
        || ($CONFIG['version'] ?? null) !== implode('.', $OC_Version)) {
        fwrite(STDOUT, "Nextcloud is still upgrading.\n");
        exit(75);
    }
    if ($CONFIG['dbtype'] !== 'pgsql') {
        throw new RuntimeException('This template requires PostgreSQL.');
    }
    require '/var/www/html/lib/base.php';
    $db = \OCP\Server::get(\OCP\IDBConnection::class);
    $locked = $db->executeQuery("SELECT pg_try_advisory_lock_shared(hashtext('lawn-nextcloud-background-jobs'))::integer")->fetchOne();
    if ((int)$locked !== 1) {
        fwrite(STDOUT, "Nextcloud background jobs are running.\n");
        exit(75);
    }
    if (readVersion('/var/www/lawn-migrations/failed-version') === $CONFIG['version']) {
        throw new RuntimeException('Nextcloud background jobs failed. See the nextcloud container logs.');
    }
    if (readVersion('/var/www/lawn-migrations/completed-version') !== $CONFIG['version']) {
        fwrite(STDOUT, "Nextcloud has not finished its initial background job runs.\n");
        exit(75);
    }
    $prefix = $CONFIG['dbtableprefix'];
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $prefix)) {
        throw new RuntimeException('Invalid database table prefix.');
    }

    // Check for scheduled migration and repair jobs while the worker is idle.
    $jobs = $db->executeQuery("SELECT count(*) FROM {$prefix}jobs
        WHERE strpos(class, :migration) > 0 OR strpos(class, :repair) > 0 OR class = :groups",
        ['migration' => '\\Migration\\', 'repair' => '\\Repair\\',
        'groups' => 'OCA\\Circles\\BackgroundJob\\SyncGroupCirclesJob']);
    if ((int)$jobs->fetchOne() !== 0) {
        fwrite(STDOUT, "Nextcloud has scheduled migration or repair jobs.\n");
        exit(75);
    }

    if (readVersion('/var/www/lawn-migrations/group-migration-version') === $CONFIG['version']) {
        // The group job catches individual failures; check the resulting memberships.
        foreach (\OCP\Server::get(\OCP\IGroupManager::class)->search('') as $group) {
            $circleId = $db->executeQuery("SELECT unique_id FROM {$prefix}circles_circle
                WHERE source = 2 AND name = :name",
                ['name' => 'group:' . $group->getGID()])->fetchOne();
            if ($circleId === false) {
                throw new RuntimeException('Nextcloud has not migrated every group to Circles.');
            }
            foreach ($group->getUsers() as $user) {
                $memberships = $db->executeQuery("SELECT count(*) FROM {$prefix}circles_member
                    WHERE circle_id = :circle AND user_id = :user AND user_type = 1
                        AND coalesce(instance, '') = '' AND status = 'Member'",
                    ['circle' => $circleId, 'user' => $user->getUID()])->fetchOne();
                if ((int)$memberships === 0) {
                    throw new RuntimeException('Nextcloud has not migrated every group membership to Circles.');
                }
            }
        }
    }

    if ($OC_Version[0] >= 33) {
        $previewMigrationComplete = $db->executeQuery("SELECT configvalue FROM {$prefix}appconfig
            WHERE appid = 'core' AND configkey = 'previewMovedDone'")->fetchOne() === '1';
        if (!$previewMigrationComplete) {
            fwrite(STDOUT, "Nextcloud is still migrating preview records.\n");
            exit(75);
        }
        // The upstream worker can report completion after an individual file fails.
        $previews = $db->executeQuery("SELECT count(*) FROM {$prefix}filecache
            WHERE path ~ :legacyPath AND mimetype <> (
                SELECT id FROM {$prefix}mimetypes WHERE mimetype = 'httpd/unix-directory')",
            ['legacyPath' => '^' . preg_quote('appdata_' . $CONFIG['instanceid']
            . '/preview/', '~') . '[0-9]+/[^/]+$']);
        if ((int)$previews->fetchOne() !== 0) {
            throw new RuntimeException('Nextcloud reported migrated previews but old preview records remain.');
        }
    }
    fwrite(STDOUT, "Nextcloud migrations have finished.\n");
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
