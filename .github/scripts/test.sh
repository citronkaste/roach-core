#!/usr/bin/env bash
set -euo pipefail

php -S 127.0.0.1:8000 -t tests/Server > /tmp/roach-fixture-server.log 2>&1 &
server_pid=$!
trap 'kill "$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true' EXIT

ready=false
for attempt in {1..40}; do
    if ! kill -0 "$server_pid" 2>/dev/null; then
        break
    fi
    if curl --fail --silent --connect-timeout 1 --max-time 2 http://127.0.0.1:8000/ping > /dev/null; then
        ready=true
        break
    fi
    sleep 0.25
done

if [[ "$ready" != true ]]; then
    printf '%s\n' 'Fixture server failed to become ready; inspect /tmp/roach-fixture-server.log.' >&2
    exit 1
fi

php vendor/bin/phpunit "$@"
