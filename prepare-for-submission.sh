#!/bin/bash

# Script to prepare the Pin CTA plugin for WordPress.org submission
# This script removes hidden files, creates a clean zip file, and updates the SVN repository

# Exit on error
set -e

# Set variables
PLUGIN_NAME="pin_ctas"
TEMP_DIR="temp_${PLUGIN_NAME}"
OUTPUT_DIR="$HOME/Desktop"
SVN_TRUNK_DIR="/Users/jward/Projects/pin-cta-svn/trunk"
SVN_TAGS_DIR="/Users/jward/Projects/pin-cta-svn/tags"

# Extract version number from the main plugin file
VERSION=$(head -20 pin-cta.php | grep "Version:" | sed -E 's/.*Version: ([0-9.]+).*/\1/')

if [ -z "$VERSION" ]; then
    echo "Error: Could not extract version number from plugin file."
    exit 1
fi

echo "Preparing plugin version $VERSION for submission..."

# Check if SVN directories exist
if [ ! -d "$SVN_TRUNK_DIR" ]; then
    echo "Error: SVN trunk directory not found at $SVN_TRUNK_DIR"
    exit 1
fi

if [ ! -d "$SVN_TAGS_DIR" ]; then
    echo "Error: SVN tags directory not found at $SVN_TAGS_DIR"
    exit 1
fi

# Create a temporary directory
echo "Creating temporary directory..."
mkdir -p "$TEMP_DIR"

# Copy all plugin files to the temporary directory, excluding hidden files and directories
echo "Copying plugin files..."
rsync -av --exclude=".*" --exclude="$TEMP_DIR" --exclude="prepare-for-submission.sh" --exclude="global-gitignore.txt" --exclude="cleanup-ds-store.sh" . "$TEMP_DIR/"

# Remove any macOS hidden files that might have been copied
echo "Removing any remaining hidden files..."
find "$TEMP_DIR" -name ".DS_Store" -type f -delete
find "$TEMP_DIR" -name "._*" -type f -delete

# Create the zip file
echo "Creating zip file..."
cd "$TEMP_DIR"
zip -r "${OUTPUT_DIR}/${PLUGIN_NAME}.zip" .
cd ..

# Update SVN trunk
echo "Updating SVN trunk directory..."

# Clear the trunk directory first (with safety check)
if [ -d "$SVN_TRUNK_DIR" ] && [ "$(ls -A "$SVN_TRUNK_DIR")" ]; then
    echo "Clearing existing files in trunk..."
    rm -rf "${SVN_TRUNK_DIR:?}"/*
fi

# Copy all files to the trunk directory
echo "Copying files to trunk..."
rsync -av --exclude=".*" --exclude="$TEMP_DIR" --exclude="prepare-for-submission.sh" --exclude="global-gitignore.txt" --exclude="cleanup-ds-store.sh" . "$SVN_TRUNK_DIR/"

# Create a new tag directory with the current version
echo "Creating tag directory for version $VERSION..."
TAG_DIR="$SVN_TAGS_DIR/$VERSION"
TAG_CREATED=true

if [ -d "$TAG_DIR" ]; then
    echo "Warning: Tag directory for version $VERSION already exists."
    read -p "Do you want to overwrite it? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Overwriting existing tag..."
        rm -rf "${TAG_DIR:?}"/*
    else
        echo "Skipping tag creation."
        TAG_CREATED=false
    fi
else
    echo "Creating new tag directory..."
    mkdir -p "$TAG_DIR"
fi

# Copy all files to the tag directory if not skipped
if [ "$TAG_CREATED" != "false" ]; then
    echo "Copying files to tag directory..."
    rsync -av --exclude=".*" --exclude="$TEMP_DIR" --exclude="prepare-for-submission.sh" --exclude="global-gitignore.txt" --exclude="cleanup-ds-store.sh" . "$TAG_DIR/"
    echo "Tag $VERSION created successfully."
fi

# Clean up
echo "Cleaning up..."
rm -rf "$TEMP_DIR"

echo "Done! ${PLUGIN_NAME}.zip is ready for submission."
echo "The zip file has been saved to: ${OUTPUT_DIR}/${PLUGIN_NAME}.zip"
echo "SVN trunk has been updated at: $SVN_TRUNK_DIR"
if [ "$TAG_CREATED" != "false" ]; then
    echo "New tag has been created at: $TAG_DIR"
fi
echo "Remember to commit the changes to SVN repository manually." 