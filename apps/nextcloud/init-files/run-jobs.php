<?php
/** Runs Nextcloud's scheduler while retaining migration failures for Lawn's check. */
declare(strict_types=1);

require '/var/www/html/lib/base.php';
require '/var/www/html/config/config.php';
require '/var/www/html/version.php';

if (!($CONFIG['installed'] ?? false) || ($CONFIG['maintenance'] ?? false)
    || \OCP\Util::needUpgrade()) {
    exit(75);
}

// Keep the migration check from reading job results while the scheduler runs.
$db = \OCP\Server::get(\OCP\IDBConnection::class);
$db->executeQuery("SELECT pg_advisory_lock(hashtext('lawn-nextcloud-background-jobs'))");
$version = implode('.', $OC_Version);
$prefix = $CONFIG['dbtableprefix'];
if (!preg_match('/^[a-zA-Z0-9_]+$/', $prefix)) {
    throw new RuntimeException('Invalid database table prefix.');
}
// Check group results only when Nextcloud schedules its group migration.
$pendingGroupMigrationQuery = $db->executeQuery("SELECT count(*) FROM {$prefix}jobs WHERE class = :class",
    ['class' => 'OCA\\Circles\\BackgroundJob\\SyncGroupCirclesJob']);
if ((int)$pendingGroupMigrationQuery->fetchOne() !== 0
    && file_put_contents('/var/www/lawn-migrations/group-migration-version', $version) === false) {
    throw new RuntimeException('Could not record a pending group migration.');
}
$recordFailure = static function () use ($version): void {
    if (file_put_contents('/var/www/lawn-migrations/failed-version', $version) === false) {
        throw new RuntimeException('Could not record a Nextcloud migration failure.');
    }
};

$events = \OCP\Server::get(\OCP\EventDispatcher\IEventDispatcher::class);
$events->addListener(\OCP\Log\BeforeMessageLoggedEvent::class,
    static function (\OCP\Log\BeforeMessageLoggedEvent $event) use ($recordFailure): void {
        $entry = $event->getMessage();
        if ($event->getLevel() < 3 && !($event->getLevel() >= 2 && isset($entry['exception']))) {
            return;
        }
        $message = $entry['message'] ?? '';
        if ($event->getApp() === 'migration' || str_contains($message, '\\Migration\\')
            || str_contains($message, '\\Repair\\')
            || str_contains($message, '\\Circles\\BackgroundJob\\SyncGroupCirclesJob')) {
            $recordFailure();
            return;
        }
        // Some jobs catch errors and log messages without their class name.
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $call) {
            $class = $call['class'] ?? '';
            if (str_contains($class, '\\Migration\\') || str_contains($class, '\\Repair\\')) {
                $recordFailure();
                return;
            }
        }
    });
$events->addListener(\OC\Repair\Events\RepairErrorEvent::class,
    static function () use ($recordFailure): void { $recordFailure(); });

// Keep Nextcloud's own job selection, execution, and diagnostics.
require '/var/www/html/cron.php';
