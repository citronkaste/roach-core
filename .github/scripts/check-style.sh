#!/usr/bin/env bash
set -euo pipefail

# Enforce the current style on changes since the fork's 1.1.0 baseline.
# Never let CI rewrite a branch or mass-format untouched upstream history.
base="${1:-1.1.0}"
git rev-parse --verify "${base}^{commit}" > /dev/null
mapfile -d '' paths < <(git diff --name-only --diff-filter=ACMR -z "$base" -- ':(glob)src/**/*.php' ':(glob)tests/**/*.php')

if (( ${#paths[@]} == 0 )); then
    printf '%s\n' 'No changed PHP source/test files to check.'
    exit 0
fi

php vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no --config=.php-cs-fixer.php --path-mode=intersection "${paths[@]}"
