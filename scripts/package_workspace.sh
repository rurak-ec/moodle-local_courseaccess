#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC_DIR="${1:-${ROOT_DIR}/workspace/local_course_conditions}"
BUILD_DIR="${ROOT_DIR}/build"
STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_ZIP="${BUILD_DIR}/course_conditions_${STAMP}.zip"
STAGE_DIR="$(mktemp -d)"

if [[ ! -d "${SRC_DIR}" ]]; then
  echo "ERROR: workspace plugin not found: ${SRC_DIR}" >&2
  echo "Expected the plugin source at workspace/local_course_conditions." >&2
  exit 1
fi

mkdir -p "${BUILD_DIR}"

cleanup() {
  rm -rf "${STAGE_DIR}"
}
trap cleanup EXIT

# Moodle expects the local plugin folder name inside local/ to be "course_conditions"
# (the plugin name without the "local_" type prefix), not "local_course_conditions".
cp -a "${SRC_DIR}" "${STAGE_DIR}/course_conditions"

(
  cd "${STAGE_DIR}"
  zip -rq "${OUT_ZIP}" "course_conditions" -x '*.DS_Store' '*__MACOSX*' '*/.git/*'
)

echo "OK: package created"
echo "  ${OUT_ZIP}"
