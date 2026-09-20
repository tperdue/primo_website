#!/usr/bin/env bash

set -Eeuo pipefail

if [[ ${SSH_ORIGINAL_COMMAND:-} =~ ^deploy[[:space:]]+([0-9a-f]{40})$ ]]; then
    exec sudo -n /usr/local/sbin/primo-deploy "${BASH_REMATCH[1]}"
fi

echo 'This SSH key is restricted to Primo deployments.' >&2
exit 2
