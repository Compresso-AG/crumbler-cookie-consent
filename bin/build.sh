#!/usr/bin/env bash
#
# Build a clean, distributable plugin ZIP for WordPress.org.
#
# - Exports only COMMITTED files (via `git archive`) so the build is reproducible
#   and never contains uncommitted cruft.
# - Strips every path listed in .distignore (dev files, hidden files, CI assets).
# - Places the plugin inside a top-level `crumbler-cookie-consent/` folder, as
#   WordPress requires.
#
# Usage:  bin/build.sh            (build from HEAD)
# Output: crumbler-cookie-consent-<version>.zip in the repo root.

set -euo pipefail

SLUG="crumbler-cookie-consent"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

# Read the version from the readme Stable tag.
VERSION="$(sed -n 's/^Stable tag: *//p' readme.txt | tr -d '\r' | head -n1)"
if [ -z "$VERSION" ]; then
    echo "Error: could not read 'Stable tag' from readme.txt" >&2
    exit 1
fi

BUILD="$ROOT/build"
STAGE="$BUILD/$SLUG"
ZIP="$ROOT/$SLUG-$VERSION.zip"

echo "Building $SLUG $VERSION ..."
rm -rf "$BUILD"
mkdir -p "$STAGE"

# Export committed files into build/<slug>/.
git archive --format=tar --prefix="$SLUG/" HEAD | tar -x -C "$BUILD"

# Remove everything listed in .distignore from the export.
while IFS= read -r line || [ -n "$line" ]; do
    line="$(printf '%s' "$line" | tr -d '\r')"
    [ -z "$line" ] && continue
    case "$line" in \#*) continue ;; esac
    rm -rf "${STAGE:?}/${line#/}"
done < .distignore

# Belt and braces: drop any stray hidden/OS files.
find "$STAGE" -name '.DS_Store' -delete 2>/dev/null || true

# Create the ZIP (-X strips extra file attributes for a clean archive).
rm -f "$ZIP"
( cd "$BUILD" && zip -rqX "$ZIP" "$SLUG" )

echo "Created: $ZIP"
echo
echo "Contents:"
unzip -l "$ZIP"

# Sanity check: fail if any hidden (dot) file slipped in.
if unzip -l "$ZIP" | awk '{print $4}' | grep -E '/\.[^/]' >/dev/null; then
    echo
    echo "WARNING: hidden files detected in the archive (see above)." >&2
    exit 1
fi
