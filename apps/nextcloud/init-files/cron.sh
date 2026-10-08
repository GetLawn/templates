#!/bin/sh
# Run Nextcloud background jobs three times before recording the installed version.
set -eu
cd /var/www/html
state=/var/www/lawn-migrations
version=$(php -r 'require "version.php"; echo implode(".", $OC_Version);')

run_background_jobs() {
    while :; do
        result=0
        php -f /var/www/run-jobs.php || result=$?
        if [ "$result" -ne 75 ]; then
            break
        fi
        sleep 5
    done
    if [ "$result" -ne 0 ]; then
        printf '%s\n' "$version" > "$state/failed-version"
        echo "Nextcloud background jobs failed." >&2
        exit "$result"
    fi
    if [ -f "$state/failed-version" ] && [ "$(cat "$state/failed-version")" = "$version" ]; then
        echo "Nextcloud background jobs failed." >&2
        exit 1
    fi
}

for run in 1 2 3; do
    run_background_jobs
done
printf '%s\n' "$version" > "$state/completed-version"

while :; do
    sleep 300
    run_background_jobs
done
