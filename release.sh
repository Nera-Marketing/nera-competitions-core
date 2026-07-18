#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# release.sh — Build & release nera-competitions-standard to its GitHub repo.
#
# Usage:
#   ./release.sh          # reads version from style.css (Version header)
#   ./release.sh 1.2.0    # override version (optional leading v: v1.2.0)
#
# Requirements: git, npm, php + build-wp-release-zip.php (or zip), gh (optional). Optional SSH: origin → $GITHUB_REMOTE
#
# Cross-platform: Linux, macOS, Windows Git Bash (MSYS). WSL behaves like Linux (rsync/cp).
# Push branch: default main; override with RELEASE_GIT_BRANCH=master if needed.
#
# What it does:
#   1. Resolves version from argument or style.css
#   2. npm run build in frontend/ and lty-result-screens/
#   3. Copies theme to a clean temp dir; sets Version (style.css), NERA_VERSION, @version in functions.php;
#      syncs readme.txt Stable tag and nera-theme-update.json (version, details_url, download_url, last_updated)
#   4. Builds nera-competitions-standard-VERSION.zip (PHP ZipArchive preferred; then zip.exe — never PowerShell
#      Compress-Archive, which uses backslashes and breaks WordPress theme updates)
#   5. Syncs the clean tree into this git repo, commits, pushes branch + tag (see RELEASE_GIT_BRANCH; default main)
#   6. gh release create / upload on github.com
#
# Excludes from the work tree copy: .git, node_modules, .env*, release artifact zip, release.sh.
# Push uses your repo's `origin` URL (set to $GITHUB_REMOTE or HTTPS). gh uses github.com.
# ─────────────────────────────────────────────────────────────────────────────
set -e

THEME_DIR="$(cd "$(dirname "$0")" && pwd)"
THEME_SLUG="nera-competitions-standard"
GITHUB_REPO="Nera-Marketing/nera-competitions-core"
GITHUB_REMOTE="git@github.com-nera:${GITHUB_REPO}.git"
# github.com-nera = SSH Host alias only (same as plugin release.sh). gh uses GH_HOST=github.com (HTTPS).

PID="$$"
_RELEASE_TMP="${TMPDIR:-/tmp}"
_RELEASE_TMP="${_RELEASE_TMP%/}"
WORK_DIR="${_RELEASE_TMP}/${THEME_SLUG}-release-${PID}"
STAGE_ZIP_PARENT="${_RELEASE_TMP}/${THEME_SLUG}-zipparent-${PID}"

msys_win_path() {
  local dir="$1"
  local w=""
  if command -v cygpath >/dev/null 2>&1; then
    w="$(MSYS_NO_PATHCONV=1 cygpath -aw "$dir" 2>/dev/null)" || w=""
  fi
  if [ -z "$w" ]; then
    w="$(cd "$dir" && pwd -W 2>/dev/null)" || w=""
  fi
  printf '%s' "$w"
}

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
# Composer (Timber + PHP deps) — must run before zipping; Convesio host has no composer.
if [ -f "$THEME_DIR/composer.json" ]; then
  if ! command -v composer >/dev/null 2>&1; then
    echo "ERROR: composer not found (required for Timber dependency)."
    exit 1
  fi
  echo "▶ Installing PHP dependencies (composer, prod only)..."
  ( cd "$THEME_DIR" && composer install --no-dev --optimize-autoloader --no-interaction )
  if [ ! -f "$THEME_DIR/vendor/autoload.php" ]; then
    echo "ERROR: vendor/autoload.php missing after composer install"
    exit 1
  fi
fi

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
    --exclude='starters' \
    --exclude='release.sh' \
    --exclude='.DS_Store' \
    --exclude='*.bak' \
    --exclude='.env.local' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude="${THEME_SLUG}-*.zip" \
    "$THEME_DIR/" "$WORK_DIR/"
else
  cp -a "$THEME_DIR"/. "$WORK_DIR"/
  rm -rf "$WORK_DIR/.git" 2>/dev/null || true
  rm -rf "$WORK_DIR/frontend/node_modules" "$WORK_DIR/lty-result-screens/node_modules" 2>/dev/null || true
  rm -rf "$WORK_DIR/starters" 2>/dev/null || true
  rm -f "$WORK_DIR/release.sh" "$WORK_DIR/.DS_Store" 2>/dev/null || true
  rm -f "$WORK_DIR/.env" "$WORK_DIR/.env.local" 2>/dev/null || true
  rm -f "$WORK_DIR/${THEME_SLUG}"-*.zip 2>/dev/null || true
  find "$WORK_DIR" -name '*.bak' -type f -delete 2>/dev/null || true
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
  sed -i "s/^Requires PHP:.*/Requires PHP: 8.1/" "$STYLE_CSS"
  sed -i "s/define('NERA_VERSION', '[^']*');/define('NERA_VERSION', '${VERSION}');/" "$FUNC_PHP"
  sed -i "s/^ \\* @version .*/ * @version ${VERSION}/" "$FUNC_PHP"
else
  sed -i '' "s/^Version:.*/Version: ${VERSION}/" "$STYLE_CSS"
  sed -i '' "s/^Requires at least:.*/Requires at least: 6.0/" "$STYLE_CSS"
  sed -i '' "s/^Tested up to:.*/Tested up to: 6.8/" "$STYLE_CSS"
  sed -i '' "s/^Requires PHP:.*/Requires PHP: 8.1/" "$STYLE_CSS"
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
    sed -i "s/\"last_updated\": *\"[^\"]*\"/\"last_updated\": \"${LAST_UPDATED}\"/" "$JSON_META"
  else
    sed -i '' "s/\"version\": *\"[^\"]*\"/\"version\": \"${VERSION}\"/" "$JSON_META"
    sed -i '' "s|\"details_url\": *\"[^\"]*\"|\"details_url\": \"${DETAILS_URL}\"|" "$JSON_META"
    sed -i '' "s|\"download_url\": *\"[^\"]*\"|\"download_url\": \"${DOWNLOAD_URL}\"|" "$JSON_META"
    sed -i '' "s/\"last_updated\": *\"[^\"]*\"/\"last_updated\": \"${LAST_UPDATED}\"/" "$JSON_META"
  fi
fi

# ── 4. Build distributable zip (PUC uses the release asset URL) ─────────────────
ZIP_PATH="$THEME_DIR/${THEME_SLUG}-${VERSION}.zip"
echo "▶ Creating zip..."
rm -f "$ZIP_PATH"
rm -rf "$STAGE_ZIP_PARENT"
mkdir -p "$STAGE_ZIP_PARENT"
cp -a "$WORK_DIR" "$STAGE_ZIP_PARENT/${THEME_SLUG}"
STAGE_SLUG_DIR="$STAGE_ZIP_PARENT/${THEME_SLUG}"
# WordPress expects `/` path separators inside the zip. Prefer PHP ZipArchive first:
# Windows `zip.exe` / some tools can still produce archives that confuse core on update
# ("Could not copy file …\assets\"). PHP addFile uses forward slashes reliably.
if command -v php >/dev/null 2>&1 && [ -f "$THEME_DIR/build-wp-release-zip.php" ]; then
  echo "▶ Building zip with PHP ZipArchive (WP-safe paths)..."
  php "$THEME_DIR/build-wp-release-zip.php" "$STAGE_SLUG_DIR" "$ZIP_PATH"
elif command -v zip >/dev/null 2>&1; then
  ( cd "$STAGE_ZIP_PARENT" && zip -rq "$ZIP_PATH" "${THEME_SLUG}" )
elif [ -x "/c/Program Files/Git/usr/bin/zip.exe" ]; then
  ( cd "$STAGE_ZIP_PARENT" && "/c/Program Files/Git/usr/bin/zip.exe" -rq "$ZIP_PATH" "${THEME_SLUG}" )
else
  echo "ERROR: Need php + build-wp-release-zip.php, or zip (e.g. Git usr/bin/zip.exe)."
  echo "       Do not use PowerShell Compress-Archive for WordPress theme zips."
  exit 1
fi
rm -rf "$STAGE_ZIP_PARENT"

if [ ! -s "$ZIP_PATH" ]; then
  echo "ERROR: Zip is missing or empty: $ZIP_PATH"
  exit 1
fi
echo "▶ Zip OK ($(wc -c < "$ZIP_PATH" | tr -d ' ') bytes)"

# ── 5. Commit + push (protected main: no orphan temp repo / no --force on main) ──
echo "▶ Syncing release tree into git working tree..."
cd "$THEME_DIR"

# Release commit author: prefer repo .git/config (--local), then global, then defaults.
RELEASE_AUTHOR_NAME="$(git config --local user.name 2>/dev/null || true)"
RELEASE_AUTHOR_EMAIL="$(git config --local user.email 2>/dev/null || true)"
if [ -z "$RELEASE_AUTHOR_NAME" ]; then
  RELEASE_AUTHOR_NAME="$(git config --global user.name 2>/dev/null || true)"
fi
if [ -z "$RELEASE_AUTHOR_EMAIL" ]; then
  RELEASE_AUTHOR_EMAIL="$(git config --global user.email 2>/dev/null || true)"
fi
if [ -z "$RELEASE_AUTHOR_NAME" ]; then
  RELEASE_AUTHOR_NAME="Minh Le"
fi
if [ -z "$RELEASE_AUTHOR_EMAIL" ]; then
  RELEASE_AUTHOR_EMAIL="minh@nera.marketing"
fi
git config user.name "$RELEASE_AUTHOR_NAME"
git config user.email "$RELEASE_AUTHOR_EMAIL"

if command -v rsync >/dev/null 2>&1; then
  rsync -a "$WORK_DIR/" "$THEME_DIR/"
else
  if [ -f "/c/Windows/System32/robocopy.exe" ]; then
    WIN_SRC="$(msys_win_path "$WORK_DIR")"
    WIN_DST="$(msys_win_path "$THEME_DIR")"
    if [ -n "$WIN_SRC" ] && [ -n "$WIN_DST" ]; then
      echo "▶ rsync not found — using robocopy (Windows)..."
      set +e
      MSYS_NO_PATHCONV=1 /c/Windows/System32/robocopy.exe "$WIN_SRC" "$WIN_DST" /E /R:2 /W:1 /NFL /NDL /NJH /NJS
      rc=$?
      set -e
      if [ "$rc" -ge 8 ]; then
        echo "ERROR: robocopy failed (exit $rc)"
        exit 1
      fi
    else
      echo "▶ rsync not found — pwd -W unavailable — using cp -a (Git Bash / Windows)..."
      ( cd "$WORK_DIR" && cp -a . "$THEME_DIR/" )
    fi
  else
    echo "▶ rsync not found — using cp -a (Git Bash / Windows)..."
    ( cd "$WORK_DIR" && cp -a . "$THEME_DIR/" )
  fi
fi

git add -A
if git diff --staged --quiet; then
  echo "▶ No staged changes after sync — skipping commit (tree already matches release)."
else
  git commit -m "Release $TAG" -q
fi

PUSH_BRANCH="${RELEASE_GIT_BRANCH:-main}"
echo "▶ Pushing ${PUSH_BRANCH} to origin..."
git push origin "$PUSH_BRANCH"

if git rev-parse "$TAG" >/dev/null 2>&1; then
  git tag -d "$TAG" 2>/dev/null || true
fi
git tag -a "$TAG" -m "Release $TAG" 2>/dev/null || git tag "$TAG"

echo "▶ Pushing tag $TAG..."
git push origin "refs/tags/${TAG}" --force

# ── 6. GitHub Release (gh) ─────────────────────────────────────────────────────
GH_CMD=""
if command -v gh >/dev/null 2>&1; then
  GH_CMD="gh"
elif [ -x "/opt/homebrew/bin/gh" ]; then
  GH_CMD="/opt/homebrew/bin/gh"
elif [ -x "/usr/local/bin/gh" ]; then
  GH_CMD="/usr/local/bin/gh"
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
