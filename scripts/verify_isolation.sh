#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Isolation check for: ${ROOT_DIR}"

for dir in workspace scripts docs; do
  if [[ ! -d "${ROOT_DIR}/${dir}" ]]; then
    echo "ERROR: missing directory ${ROOT_DIR}/${dir}" >&2
    exit 1
  fi
done

if [[ -d "${ROOT_DIR}/workspace/local_course_access" ]]; then
  echo "OK: workspace/local_course_access found"
else
  echo "WARN: workspace/local_course_access not found"
fi

if [[ -f "${ROOT_DIR}/workspace/local_course_access/version.php" ]]; then
  echo "OK: version.php present"
fi

echo "OK: base structure looks good for development."
