#!/bin/bash

# Deployment Script on the Deployment VM

# Variables for the MySQL database
DB_USER="ko58"
DB_PASS="jaks123!"
DB_NAME="deployment_db"
DB_TABLE="deployment_logs"

# Variables for deployment
DEPLOY_DIR="/home/ko58/deploy"
PACKAGE_NAME=$1 # The package name is passed as an argument to the script
VERSION=$(echo "$PACKAGE_NAME" | sed 's/.*-\([0-9]*\.[0-9]*\)\.zip/\1/i') # Extract version number

# Check if the package is provided
if [ -z "$PACKAGE_NAME" ]; then
    echo "Package name argument is missing. Usage: $0 <package_name>"
    exit 1
fi

# Move to the deployment directory
cd "$DEPLOY_DIR"

# Unzip the package
unzip -o "$PACKAGE_NAME"

# Define Josh's QA VM 
QA_VM="10.244.90.52"

# Define remote deployment directory on the Josh's QA VM
REMOTE_DEPLOY_DIR="/home/josh/JAKS-Deployment"

# Log the initial deployment to the MySQL database as 'new'
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
INSERT INTO $DB_TABLE (bundlename, version, status, deploy_time)
VALUES ('$PACKAGE_NAME', '$VERSION', 'new', NOW())
ON DUPLICATE KEY UPDATE
version=VALUES(version), status='new', deploy_time=NOW();"

# Copy the install script (and any other necessary files) to the QA VM
scp "install.sh" "${QA_VM}:${REMOTE_DEPLOY_DIR}"

# Echo for clarity in output
echo "Starting SSH connection to QA VM with verbose logging..."

# SSH command with verbose logging
ssh -vvv "${QA_VM}" "bash ${REMOTE_DEPLOY_DIR}/install.sh '$DB_USER' '$DB_PASS' '$DB_NAME' '$DB_TABLE' '$PACKAGE_NAME' '$VERSION'"

# Echo to confirm the end of the operation
echo "SSH command execution completed."


echo "Deployment to QA completed for package $PACKAGE_NAME"
