#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC_DIR="${1:-${ROOT_DIR}/workspace/local_course_access}"
BUILD_DIR="${ROOT_DIR}/build"
STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_ZIP="${BUILD_DIR}/course_access_${STAMP}.zip"
STAGE_DIR="$(mktemp -d)"

if [[ ! -d "${SRC_DIR}" ]]; then
  echo "ERROR: workspace plugin not found: ${SRC_DIR}" >&2
  echo "Expected the plugin source at workspace/local_course_access." >&2
  exit 1
fi

mkdir -p "${BUILD_DIR}"

# NOTE: The AMD modules are ES6 (import/export) and are compiled to amd/build/
# with `grunt amd` (rollup). Do NOT copy amd/src over amd/build here -- the
# committed amd/build/*.min.js is the rollup output and must ship as-is.

cleanup() {
  rm -rf "${STAGE_DIR}"
}
trap cleanup EXIT

# Moodle expects the local plugin folder name inside local/ to be "course_access"
# (the plugin name without the "local_" type prefix), not "local_course_access".
cp -a "${SRC_DIR}" "${STAGE_DIR}/course_access"

(
  cd "${STAGE_DIR}"
  zip -rq "${OUT_ZIP}" "course_access" -x '*.DS_Store' '*__MACOSX*' '*/.git/*'
)

echo "OK: package created"
echo "  ${OUT_ZIP}"
