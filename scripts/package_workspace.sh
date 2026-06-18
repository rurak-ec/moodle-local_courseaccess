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

# ---------------------------------------------------------------------------
# Keep AMD build/ in sync with src/.
# Moodle serves amd/build/*.min.js, NOT amd/src/*.js. These modules are
# authored as legacy AMD (define([...], function(){})), so no transpilation is
# needed and build is a verbatim copy of src. Re-mirroring here guarantees the
# packaged build can never be a stale copy of the source.
# NOTE: if a module is ever rewritten as a native ES6 module (import/export),
# this verbatim copy is NOT enough -- it must be compiled with `grunt amd`.
# ---------------------------------------------------------------------------
AMD_SRC_DIR="${SRC_DIR}/amd/src"
AMD_BUILD_DIR="${SRC_DIR}/amd/build"
if [[ -d "${AMD_SRC_DIR}" ]]; then
  mkdir -p "${AMD_BUILD_DIR}"
  for srcfile in "${AMD_SRC_DIR}"/*.js; do
    [[ -e "${srcfile}" ]] || continue
    base="$(basename "${srcfile}" .js)"
    target="${AMD_BUILD_DIR}/${base}.min.js"
    if [[ ! -f "${target}" ]] || ! cmp -s "${srcfile}" "${target}"; then
      echo "Syncing AMD build: ${base}.js -> ${base}.min.js"
      cp -a "${srcfile}" "${target}"
    fi
  done
fi

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
