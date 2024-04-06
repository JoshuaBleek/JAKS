#!/bin/bash

# Define variables
REMOTE_SERVER="user@remote-server"
REMOTE_DIR="/path/to/remote/directory"
PREVIOUS_VERSION="myapp-1.0.tar.gz"

# Rollback to previous version
ssh "$REMOTE_SERVER" "tar -xzf $REMOTE_DIR/$PREVIOUS_VERSION -C $REMOTE_DIR && systemctl restart myapp.service"
echo "Rollback complete"

