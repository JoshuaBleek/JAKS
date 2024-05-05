#!/bin/bash

# Arguments passed from deployment.sh
DB_USER="$1"
DB_PASS="$2"
DB_NAME="$3"
DB_TABLE="$4"
PACKAGE_NAME="$5"
VERSION=$(echo "$PACKAGE_NAME" | sed 's/.*-\([0-9]*\(\.[0-9]*\)\?\)\.zip/\1/i')
STATUS="$7"  # Added this to accept the initial status


# Function to log deployment status
log_deployment_status() {
    local status=$1
    local errorMessage=${2:-''} # Optional error message

    # Updating the status in the MySQL database
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
    UPDATE $DB_TABLE
    SET status='$status', error_message='$errorMessage'
    WHERE bundlename='$PACKAGE_NAME' AND version='$VERSION';"
}

# Simulate deployment attempt here
# This is where you would extract your package, move files to the correct location, etc.

# Simulate running tests to verify deployment
# Replace or extend this section with actual test execution logic
deployment_success=true # Placeholder for actual success/failure detection

if [ $deployment_success ]; then
    log_deployment_status "success"
echo "Deployment successful for $PACKAGE_NAME version $VERSION."
else
    log_deployment_status "fail" "An error occurred during deployment." # Example error message
echo "Deployment failed for $PACKAGE_NAME version $VERSION."
fi

# This script assumes a simple pass/fail outcome for demonstration purposes.
# Extend and modify the deployment and testing logic as per your actual requirements.

# After deployment and testing logic

if [ "$deployment_success" == "false" ]; then
    echo "Deployment failed, initiating rollback..."

    last_good_version=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN -e "
    SELECT version FROM $DB_TABLE
    WHERE status='success' AND bundlename='$PACKAGE_NAME'
    ORDER BY deploy_time DESC
    LIMIT 1;")

    if [ -n "$last_good_version" ]; then
        echo "Rolling back to version $last_good_version..."
        PACKAGE_ARCHIVE="/home/deploy/${PACKAGE_NAME}-${last_good_version}.zip"
        DEPLOYMENT_DIR="/home/josh/JAKS-Deployment"

        if [ -f "$PACKAGE_ARCHIVE" ]; then
            rm -rf "${DEPLOYMENT_DIR:?}/*"
            unzip "$PACKAGE_ARCHIVE" -d "$DEPLOYMENT_DIR"
            echo "Rollback to $last_good_version completed successfully."
        else
            echo "Error: Archive for last good version $last_good_version not found."
        fi
    else
        echo "No previous successful version found for rollback."
    fi
fi


