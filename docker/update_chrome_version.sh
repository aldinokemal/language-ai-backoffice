#!/bin/bash

# Script to update Chrome headless version in base.Dockerfile
# Usage: ./update_chrome_version.sh [--dry-run]

set -e

DRY_RUN=false
if [ "$1" = "--dry-run" ]; then
    DRY_RUN=true
fi

# Get the latest stable Chrome version
echo "Fetching latest Chrome stable version..."
LATEST_VERSION=$(curl -s "https://versionhistory.googleapis.com/v1/chrome/platforms/linux/channels/stable/versions" | \
    jq -r '.versions[0].version' 2>/dev/null || \
    curl -s "https://versionhistory.googleapis.com/v1/chrome/platforms/linux/channels/stable/versions" | \
    grep -o '"version":"[^"]*"' | head -1 | cut -d'"' -f4)

if [ -z "$LATEST_VERSION" ]; then
    echo "Error: Could not fetch latest Chrome version"
    exit 1
fi

echo "Latest Chrome stable version: $LATEST_VERSION"

# Check current version in Dockerfile
DOCKERFILE="dockerfile/base.Dockerfile"
CURRENT_VERSION=$(grep "ARG CHROME_VERSION=" "$DOCKERFILE" | cut -d'=' -f2)

if [ "$CURRENT_VERSION" = "$LATEST_VERSION" ]; then
    echo "Chrome version is already up to date ($CURRENT_VERSION)"
    exit 0
fi

echo "Current version: $CURRENT_VERSION"
echo "Updating to: $LATEST_VERSION"

SED_COMMAND="sed -i"
if [ "$(uname)" = "Darwin" ]; then
    SED_COMMAND="sed -i ''"
fi

if [ "$DRY_RUN" = true ]; then
    echo "[DRY RUN] Would update $DOCKERFILE"
    echo "$SED_COMMAND \"s/ARG CHROME_VERSION=.*/ARG CHROME_VERSION=$LATEST_VERSION/\" \"$DOCKERFILE\""
else
    # Update the Dockerfile
    $SED_COMMAND "s/ARG CHROME_VERSION=.*/ARG CHROME_VERSION=$LATEST_VERSION/" "$DOCKERFILE"
    echo "Updated $DOCKERFILE successfully"
    echo ""
    echo "Next steps:"
    echo "1. Review the changes: git diff $DOCKERFILE"
    echo "2. Test the build: make buildx-push-base"
    echo "3. Commit and push: git add $DOCKERFILE && git commit -m \"Update Chrome headless to $LATEST_VERSION\""
fi
