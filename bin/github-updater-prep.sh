#!/bin/bash
# github-updater-prep.sh
# Usage: ./github-updater-prep.sh path/to/main-plugin-file.php "owner/repository"

MAIN_FILE="$1"
REPO_NAME="$2"

# Define the new headers to add
NEW_HEADERS=$(cat <<-END
 * GitHub Plugin URI: $REPO_NAME
END
)

# Backup the original file
cp "$MAIN_FILE" "${MAIN_FILE}.bak"

# Check if the headers are already present to avoid duplicates
if ! grep -q "GitHub Plugin URI: $REPO_NAME" "$MAIN_FILE"; then
  # Use awk to insert the new headers right after "Requires PHP" line without adding an extra blank line
  awk -v new_headers="$NEW_HEADERS" '
    BEGIN { added = 0 }
    /Requires PHP:/ && !added {
      print $0       # Print the current line (Requires PHP)
      print new_headers  # Print new headers after Requires PHP
      added = 1
      next
    }
    { print $0 }  # Print the rest of the file as is
  ' "$MAIN_FILE" > "${MAIN_FILE}.tmp" && mv "${MAIN_FILE}.tmp" "$MAIN_FILE"
else
  echo "Headers already present in $MAIN_FILE"
fi

# Delete the backup file after successful modification
rm -f "${MAIN_FILE}.bak"