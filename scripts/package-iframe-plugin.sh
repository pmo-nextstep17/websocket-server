#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_PARENT="$ROOT_DIR/wordpress-plugin"
PLUGIN_DIR="$PLUGIN_PARENT/iframe-clean-viewer"
OUTPUT_ZIP="$PLUGIN_PARENT/iframe-clean-viewer.zip"

if [[ ! -d "$PLUGIN_DIR" ]]; then
  echo "Plugin directory not found: $PLUGIN_DIR" >&2
  exit 1
fi

rm -f "$OUTPUT_ZIP"
(
  cd "$PLUGIN_PARENT"
  zip -r "$(basename "$OUTPUT_ZIP")" "$(basename "$PLUGIN_DIR")" >/dev/null
)

echo "Created: $OUTPUT_ZIP"
