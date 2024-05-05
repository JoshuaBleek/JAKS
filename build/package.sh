#!/bin/bash

# Define variables
APP_NAME="JAKS"
VERSION=$1 # Version number passed as an argument to the script
BUILD_DIR="./build"
DIST_DIR="./dist"
PACKAGE_NAME="${APP_NAME}-${VERSION}.zip"

# Check if version argument is provided
if [ -z "$VERSION" ]; then
    echo "Version number argument is missing. Usage: $0 <version>"
    exit 1
fi

# Create a clean build directory
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR

echo "Contents of src directory:"
ls -l ./git/JAKS

# Copy files to the build directory
cp -r ./git/JAKS/* ./build/

echo "Contents of build directory after copying:"
ls -l $BUILD_DIR

# Compile code if necessary (placeholder for compilation step)

# Create a clean distribution directory
mkdir -p $DIST_DIR

# Package the application
cd $BUILD_DIR
zip -r "../${DIST_DIR}/${PACKAGE_NAME}" *
cd ..

scp "${DIST_DIR}/${PACKAGE_NAME}" ko58@192.168.1.227:/home/ko58/deploy
