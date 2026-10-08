#!/bin/sh
# Keep Pi-hole configuration with its databases across image updates.
set -eu

configuration=/data/pihole/config
if [ ! -d "$configuration" ]; then
    staging=$(mktemp -d /data/pihole/config.XXXXXX)
    trap 'rm -rf "$staging"' EXIT
    cp -a /etc/pihole/. "$staging/"
    mv "$staging" "$configuration"
    trap - EXIT
fi

if [ ! -L /etc/pihole ]; then
    rm -rf /etc/pihole
    ln -s "$configuration" /etc/pihole
fi

exec /usr/bin/start.sh "$@"
