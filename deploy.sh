#!/bin/bash

# Define variables
PACKAGE_NAME="myapp-1.0.tar.gz"
REMOTE_SERVER="user@remote-server"
REMOTE_DIR="/git/JAKS/remote-directory"

# Transfer package to remote server
scp "$PACKAGE_NAME" "$REMOTE_SERVER:$REMOTE_DIR"
echo "Package transferred to remote server"

# Extract package and restart application
ssh "$REMOTE_SERVER" "tar -xzf $REMOTE_DIR/$PACKAGE_NAME -C $REMOTE_DIR && systemctl restart myapp.service"
echo "Application deployed"

