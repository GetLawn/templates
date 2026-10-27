#!/bin/bash
set -e

if [[ ${START_DOCKER_SERVICE:-false} == true ]]; then
  # PID files can outlive the VM that created them.
  for pidfile in /var/run/docker.pid /var/run/docker/containerd/containerd.pid; do
    if [[ -f $pidfile ]]; then
      echo "Removing stale Docker PID file: $pidfile"
      rm -f "$pidfile"
    fi
  done
fi

exec /entrypoint.sh "$@"
