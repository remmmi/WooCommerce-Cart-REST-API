#!/bin/bash
# github-updater-prep.sh
# Usage: ./github-updater-prep.sh path/to/main-plugin-file.php "owner/repository"

MAIN_FILE="$1"
REPO_NAME="$2"

# Headers to add, using the repository name for GitHub Plugin URI
ADDITIONAL_HEADERS="\
GitHub Plugin URI: $REPO_NAME
"

# Check if each header already exists, and if not, append it to the main plugin file
for HEADER_LINE in "${ADDITIONAL_HEADERS[@]}"; do
  if ! grep -qF "$HEADER_LINE" "$MAIN_FILE"; then
    echo "$HEADER_LINE" >> "$MAIN_FILE"
  fi
done
