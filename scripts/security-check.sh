#!/bin/sh
set -eu
ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
PATTERN='BEGIN (RSA|OPENSSH|EC) PRIVATE KEY|gh[pousr]_[A-Za-z0-9]{20,}|AIza[0-9A-Za-z_-]{20,}|sk-[A-Za-z0-9]{20,}'
if grep -RniE "$PATTERN" "$ROOT" --exclude-dir=.git --exclude='*.zip'; then
  echo 'Potential secret detected. Review before commit.' >&2
  exit 1
fi
for f in .env.local .env.dev.local .env.prod.local; do
  if [ -e "$ROOT/$f" ]; then
    echo "Local secret file exists (expected locally, must remain untracked): $f"
  fi
done
echo 'No common secret pattern detected in project files.'
