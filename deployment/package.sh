#!/bin/bash

# Define variables
APP_NAME="JAKS"
VERSION=$1 # Version number passed as an argument to the script
ROOT_DIR=$(realpath "$(dirname "$0")/..") # Go up one directory from the deployment directory
BUILD_DIR="${ROOT_DIR}/build"
DIST_DIR="${ROOT_DIR}/dist"
PACKAGE_NAME="${APP_NAME}-${VERSION}.zip"

# Check if version argument is provided
if [ -z "$VERSION" ]; then
    echo "Version number argument is missing. Usage: $0 <version>"
    exit 1
fi

# Create a clean build directory
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR

echo "Contents of JAKS directory:"
ls -l $ROOT_DIR

# Copy files to the build directory
cp -r $ROOT_DIR/* $BUILD_DIR/

# Exclude the build, dist, and deployment directories from being copied into themselves
rm -rf $BUILD_DIR/build $BUILD_DIR/dist $BUILD_DIR/deployment

echo "Contents of build directory after copying:"
ls -l $BUILD_DIR


# Create a clean distribution directory
mkdir -p $DIST_DIR

# Package the application
cd $BUILD_DIR
zip -r "${DIST_DIR}/${PACKAGE_NAME}" .
cd ..

echo "Build package created: ${DIST_DIR}/${PACKAGE_NAME}"

scp "${DIST_DIR}/${PACKAGE_NAME}" ko58@10.244.108.27:/home/ko58/deploy