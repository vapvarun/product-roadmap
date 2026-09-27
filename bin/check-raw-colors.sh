#!/usr/bin/env bash
# Fail when a raw colour (hex, rgb(), hsl()) appears in a frontend source.
#
# Every frontend colour comes from assets/css/roadmap-tokens.css, the one file
# that holds colour values and their dark-mode swaps. A raw colour anywhere
# else skips dark mode and every future contrast fix. Use a token; if a value
# is genuinely data (an owner-chosen colour default), keep it and put
# "raw-color-ok: <why>" on the same line.
#
# Usage: bin/check-raw-colors.sh   (from anywhere; exits 1 on a finding)
set -euo pipefail
cd "$(dirname "$0")/.."

files=(
	vue-app/src
	assets/css/changelog-frontend.css
	assets/css/roadmap-single.css
	assets/css/roadmap-archive.css
	templates
	includes/class-roadmap-shortcode.php
)

# (^|[^&\w]) skips HTML entities such as &#9660; and identifiers.
pattern='(^|[^&[:alnum:]_])#([[:xdigit:]]{3}|[[:xdigit:]]{4}|[[:xdigit:]]{6}|[[:xdigit:]]{8})([^[:alnum:]_]|$)|(^|[^[:alnum:]_-])(rgba?|hsla?)\('

for path in "${files[@]}"; do
	# A stale path would make grep error out and the check pass silently.
	[ -e "$path" ] || { echo "check-raw-colors: listed path not found: $path"; exit 2; }
done

hits=$(grep -rnE "$pattern" "${files[@]}" | grep -v 'raw-color-ok' || true)
if [ -n "$hits" ]; then
	echo "Raw colours found. Use a token from assets/css/roadmap-tokens.css:"
	echo "$hits"
	exit 1
fi
echo "OK: no raw colours in frontend sources."
