#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# release.sh — Build & release nera-competitions-standard to its GitHub repo.
#
# Usage:
#   ./release.sh          # reads version from style.css (Version header)
#   ./release.sh 1.2.0    # override version (optional leading v: v1.2.0)
#
# Requirements: git, ssh key for git@github.com-nera, gh (optional), npm (for Vite/Tailwind builds).
#
# What it does:
#   1. Resolves version from argument or style.css
#   2. npm run build in frontend/ and lty-result-screens/
#   3. Copies theme to a clean temp dir; sets Version (style.css), NERA_VERSION, @version in functions.php;
#      syncs readme.txt Stable tag and nera-theme-update.json when present
#   4. git push to Nera-Marketing/nera-competitions-standard (main + tag vX.Y.Z)
#   5. Builds nera-competitions-standard-VERSION.zip next to this script
#   6. gh release create / upload on github.com
#
# Excludes from the zip: .git, node_modules, .env*, release artifact zip, release.sh.
# github.com-nera is an SSH Host alias only. gh always uses GH_HOST=github.com (HTTPS).
# ─────────────────────────────────────────────────────────────────────────────
set -e

THEME_DIR="$(cd "$(dirname "$0")" && pwd)"
THEME_SLUG="nera-competitions-standard"
GITHUB_REPO="Nera-Marketing/nera-competitions-core"
GITHUB_REMOTE="git@github.com-nera:${GITHUB_REPO}.git"

PID="$$"
WORK_DIR="/tmp/${THEME_SLUG}-release-${PID}"
STAGE_ZIP_PARENT="/tmp/${THEME_SLUG}-zipparent-${PID}"

cleanup() {
  rm -rf "$WORK_DIR" "$STAGE_ZIP_PARENT" 2>/dev/null || true
}
trap cleanup EXIT

# ── 1. Resolve version ────────────────────────────────────────────────────────
if [ -n "${1:-}" ]; then
  VERSION="${1#v}"
else
  VERSION=$(grep -m1 '^Version:' "$THEME_DIR/style.css" | sed 's/^Version:[[:space:]]*//;s/[[:space:]]*$//')
fi

if [ -z "$VERSION" ]; then
  echo "ERROR: Could not determine version. Pass it as an argument: ./release.sh 1.2.0"
  exit 1
fi

TAG="v${VERSION}"

echo "──────────────────────────────────────────"
echo " Releasing $THEME_SLUG $TAG"
echo "──────────────────────────────────────────"

for cmd in git grep sed; do
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "ERROR: required command not found: $cmd"
    exit 1
  fi
done

# ── 2. Build assets ───────────────────────────────────────────────────────────
if ! command -v npm >/dev/null 2>&1; then
  echo "ERROR: npm is not in PATH (required for frontend + lty-result-screens build)."
  exit 1
fi
echo "▶ Building frontend (Vite)..."
( cd "$THEME_DIR/frontend" && npm run build )
echo "▶ Building lty-result-screens (Tailwind)..."
( cd "$THEME_DIR/lty-result-screens" && npm run build )

# ── 3. Clean temp copy ────────────────────────────────────────────────────────
rm -rf "$WORK_DIR"
mkdir -p "$WORK_DIR"

echo "▶ Copying theme files..."
if command -v rsync >/dev/null 2>&1; then
  rsync -a \
    --exclude='.git' \
    --exclude='frontend/node_modules' \
    --exclude='lty-result-screens/node_modules' \
    --exclude='release.sh' \
    --exclude='.DS_Store' \
    --exclude='.env.local' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude="${THEME_SLUG}-*.zip" \
    "$THEME_DIR/" "$WORK_DIR/"
else
  cp -a "$THEME_DIR"/. "$WORK_DIR"/
  rm -rf "$WORK_DIR/.git" 2>/dev/null || true
  rm -rf "$WORK_DIR/frontend/node_modules" "$WORK_DIR/lty-result-screens/node_modules" 2>/dev/null || true
  rm -f "$WORK_DIR/release.sh" "$WORK_DIR/.DS_Store" 2>/dev/null || true
  rm -f "$WORK_DIR/.env" "$WORK_DIR/.env.local" 2>/dev/null || true
  rm -f "$WORK_DIR/${THEME_SLUG}"-*.zip 2>/dev/null || true
fi

echo "▶ Setting Version in style.css + NERA_VERSION + @version in functions.php (for PUC)..."
STYLE_CSS="$WORK_DIR/style.css"
FUNC_PHP="$WORK_DIR/functions.php"
if [ ! -f "$STYLE_CSS" ] || [ ! -f "$FUNC_PHP" ]; then
  echo "ERROR: Missing style.css or functions.php in work tree"
  exit 1
fi

if sed --version >/dev/null 2>&1; then
  sed -i "s/^Version:.*/Version: ${VERSION}/" "$STYLE_CSS"
  sed -i "s/^Requires at least:.*/Requires at least: 6.0/" "$STYLE_CSS"
  sed -i "s/^Tested up to:.*/Tested up to: 6.8/" "$STYLE_CSS"
  sed -i "s/^Requires PHP:.*/Requires PHP: 7.4/" "$STYLE_CSS"
  sed -i "s/define('NERA_VERSION', '[^']*');/define('NERA_VERSION', '${VERSION}');/" "$FUNC_PHP"
  sed -i "s/^ \\* @version .*/ * @version ${VERSION}/" "$FUNC_PHP"
else
  sed -i '' "s/^Version:.*/Version: ${VERSION}/" "$STYLE_CSS"
  sed -i '' "s/^Requires at least:.*/Requires at least: 6.0/" "$STYLE_CSS"
  sed -i '' "s/^Tested up to:.*/Tested up to: 6.8/" "$STYLE_CSS"
  sed -i '' "s/^Requires PHP:.*/Requires PHP: 7.4/" "$STYLE_CSS"
  sed -i '' "s/define('NERA_VERSION', '[^']*');/define('NERA_VERSION', '${VERSION}');/" "$FUNC_PHP"
  sed -i '' "s/^ \\* @version .*/ * @version ${VERSION}/" "$FUNC_PHP"
fi

README_TXT="$WORK_DIR/readme.txt"
if [ -f "$README_TXT" ]; then
  echo "▶ Syncing Stable tag in readme.txt to ${VERSION}..."
  if sed --version >/dev/null 2>&1; then
    sed -i "s/^Stable tag: .*/Stable tag: ${VERSION}/" "$README_TXT"
  else
    sed -i '' "s/^Stable tag: .*/Stable tag: ${VERSION}/" "$README_TXT"
  fi
fi

JSON_META="$WORK_DIR/nera-theme-update.json"
if [ -f "$JSON_META" ]; then
  echo "▶ Syncing nera-theme-update.json (self-hosted PUC metadata)..."
  DOWNLOAD_URL="https://github.com/${GITHUB_REPO}/releases/download/${TAG}/${THEME_SLUG}-${VERSION}.zip"
  DETAILS_URL="https://github.com/${GITHUB_REPO}/releases/tag/${TAG}"
  LAST_UPDATED="$(date -u '+%Y-%m-%d %H:%M:%S' 2>/dev/null || date -u '+%Y-%m-%d %H:%M:%S')"
  if sed --version >/dev/null 2>&1; then
    sed -i "s/\"version\": *\"[^\"]*\"/\"version\": \"${VERSION}\"/" "$JSON_META"
    sed -i "s|\"details_url\": *\"[^\"]*\"|\"details_url\": \"${DETAILS_URL}\"|" "$JSON_META"
    sed -i "s|\"download_url\": *\"[^\"]*\"|\"download_url\": \"${DOWNLOAD_URL}\"|" "$JSON_META"
  else
    sed -i '' "s/\"version\": *\"[^\"]*\"/\"version\": \"${VERSION}\"/" "$JSON_META"
    sed -i '' "s|\"details_url\": *\"[^\"]*\"|\"details_url\": \"${DETAILS_URL}\"|" "$JSON_META"
    sed -i '' "s|\"download_url\": *\"[^\"]*\"|\"download_url\": \"${DOWNLOAD_URL}\"|" "$JSON_META"
  fi
  # Optional last_updated line — add only if file already has key (theme JSON minimal set omits it)
  if grep -q '"last_updated"' "$JSON_META" 2>/dev/null; then
    if sed --version >/dev/null 2>&1; then
      sed -i "s/\"last_updated\": *\"[^\"]*\"/\"last_updated\": \"${LAST_UPDATED}\"/" "$JSON_META"
    else
      sed -i '' "s/\"last_updated\": *\"[^\"]*\"/\"last_updated\": \"${LAST_UPDATED}\"/" "$JSON_META"
    fi
  fi
fi

# ── 4. Push to GitHub ─────────────────────────────────────────────────────────
echo "▶ Pushing to GitHub ($GITHUB_REMOTE)..."
cd "$WORK_DIR"

git init -b main >/dev/null
git config user.name "Tan Nguyen"
git config user.email "tan@neramarketing.co.uk"
git remote add origin "$GITHUB_REMOTE"

git add -A
git commit -m "Release $TAG" -q
git tag "$TAG"
git push origin main --force
git push origin "$TAG" --force

# ── 5. Create zip ─────────────────────────────────────────────────────────────
ZIP_PATH="$THEME_DIR/${THEME_SLUG}-${VERSION}.zip"
echo "▶ Creating zip..."
rm -f "$ZIP_PATH"
rm -rf "$STAGE_ZIP_PARENT"
mkdir -p "$STAGE_ZIP_PARENT"
cp -a "$WORK_DIR" "$STAGE_ZIP_PARENT/${THEME_SLUG}"
if command -v zip >/dev/null 2>&1; then
  ( cd "$STAGE_ZIP_PARENT" && zip -rq "$ZIP_PATH" "${THEME_SLUG}" )
else
  if command -v cygpath >/dev/null 2>&1; then
    ZIP_WIN=$(cygpath -w "$ZIP_PATH")
    SRC_WIN=$(cygpath -w "$STAGE_ZIP_PARENT/${THEME_SLUG}")
  else
    ZIP_WIN="$ZIP_PATH"
    SRC_WIN="$STAGE_ZIP_PARENT/${THEME_SLUG}"
  fi
  MSYS2_ARG_CONV_EXCL='*' powershell.exe -NoProfile -Command "Compress-Archive -LiteralPath '$SRC_WIN' -DestinationPath '$ZIP_WIN' -Force"
fi
rm -rf "$STAGE_ZIP_PARENT"

if [ ! -s "$ZIP_PATH" ]; then
  echo "ERROR: Zip is missing or empty: $ZIP_PATH"
  exit 1
fi
echo "▶ Zip OK ($(wc -c < "$ZIP_PATH" | tr -d ' ') bytes)"

# ── gh release ────────────────────────────────────────────────────────────────
GH_CMD=""
if command -v gh >/dev/null 2>&1; then
  GH_CMD="gh"
elif [ -f "/c/Program Files/GitHub CLI/gh.exe" ]; then
  GH_CMD="/c/Program Files/GitHub CLI/gh.exe"
elif [ -f "/c/Program Files (x86)/GitHub CLI/gh.exe" ]; then
  GH_CMD="/c/Program Files (x86)/GitHub CLI/gh.exe"
elif [ -n "${HOME:-}" ] && [ -f "${HOME}/AppData/Local/Programs/GitHub CLI/gh.exe" ]; then
  GH_CMD="${HOME}/AppData/Local/Programs/GitHub CLI/gh.exe"
fi

if [ -n "$GH_CMD" ]; then
  echo "▶ Checking gh auth (github.com)..."
  if ! ( export GH_HOST=github.com && "$GH_CMD" auth status -h github.com >/dev/null 2>&1 ); then
    echo "ERROR: gh is not logged in for github.com."
    echo "       Run: gh auth login -h github.com"
    echo "       Zip kept at: $ZIP_PATH"
    exit 1
  fi

  echo "▶ Publishing GitHub Release $TAG (using: $GH_CMD)..."
  (
    export GH_HOST=github.com
    if "$GH_CMD" release view "$TAG" --repo "$GITHUB_REPO" >/dev/null 2>&1; then
      echo "▶ Release exists — uploading / replacing zip asset..."
      "$GH_CMD" release upload "$TAG" "$ZIP_PATH" --repo "$GITHUB_REPO" --clobber
    else
      "$GH_CMD" release create "$TAG" \
        --repo "$GITHUB_REPO" \
        --title "$TAG" \
        --notes "Release $TAG" \
        "$ZIP_PATH"
    fi
  )
  rm -f "$ZIP_PATH"
else
  echo ""
  echo "⚠  gh (GitHub CLI) not found — skipped GitHub Release upload."
  echo "    Zip:     $ZIP_PATH"
  echo "    Manual:  https://github.com/${GITHUB_REPO}/releases/new?tag=${TAG}"
  echo ""
fi

echo ""
echo "✅ Done! Tag $TAG is on GitHub."
if [ -n "$GH_CMD" ]; then
  echo "   Release: https://github.com/${GITHUB_REPO}/releases/tag/${TAG}"
  echo ""
  echo "After release, bump style.css Version, NERA_VERSION, readme Stable tag, and nera-theme-update.json in"
  echo "this working copy to match (or pull from GitHub). PUC checks GitHub every few hours."
else
  echo "   Tag: https://github.com/${GITHUB_REPO}/releases/tag/${TAG}"
fi
