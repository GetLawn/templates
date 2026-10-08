#!/bin/sh
# Start the worker after the image finishes installation or its database upgrade.
set -eu
mkdir -p /var/www/lawn-migrations /docker-entrypoint-hooks.d/before-starting
chown www-data:www-data /var/www/lawn-migrations
rm -f /var/www/lawn-migrations/completed-version
hook=/docker-entrypoint-hooks.d/before-starting/10-lawn-background-jobs.sh
printf '%s\n' '#!/bin/sh' '/bin/sh /var/www/lawn-cron.sh &' > "$hook"
chmod +x "$hook"
exec /entrypoint.sh "$@"
