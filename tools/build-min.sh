#!/bin/bash
# Build minified theme assets (theme.min.css, theme.min.js). Run before every release.
# inc/enqueue.php loads the .min files when they exist.
set -e
T="$(cd "$(dirname "$0")/.." && pwd)/wp-content/themes/booming-venture/assets"
npx --yes lightningcss-cli@1.30.1 --minify --targets '>= 0.25%' "$T/css/theme.css" -o "$T/css/theme.min.css"
npx --yes terser@5.39.0 "$T/js/theme.js" --compress --mangle -o "$T/js/theme.min.js"
npx --yes terser@5.39.0 "$T/js/chat.js" --compress --mangle -o "$T/js/chat.min.js"
wc -c "$T/css/theme.css" "$T/css/theme.min.css" "$T/js/theme.js" "$T/js/theme.min.js" "$T/js/chat.js" "$T/js/chat.min.js"
