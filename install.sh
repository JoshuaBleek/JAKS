#!/bin/bash

# DATA BASE INFO PASSED from the deployment scripts
DB_USER="$1"
DB_PASS="$2"
DB_NAME="$3"
DB_TABLE="$4"
PACKAGE_NAME="$5"
VERSION="$6"

#THE DEPLOYMENT STATUS IS RECORDED HERE
log_deployment_status() {
    local status=$1
    local errorMessage=${2:-''}

    
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
    UPDATE $DB_TABLE
    SET status='$status', error_message='$errorMessage'
    WHERE bundlename='$PACKAGE_NAME' AND version='$VERSION';"
}

#THIS IS THE LOGIC FOR A SUCCESS OR A FAIL ONCE DEPLOYED TO QA or Prod
deployment_success=true 

if [ $deployment_success ]; then
    log_deployment_status "success"
echo "Deployment successful for $PACKAGE_NAME version $VERSION."
else
    log_deployment_status "fail" "An error occurred during deployment." 
echo "Deployment failed for $PACKAGE_NAME version $VERSION."
fi

#THIS TEST THE LOGIC FOR A FAIL AND WILL ROLLBACK TO THE LAST GOOD VERSION

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
        DEPLOYMENT_DIR="/home/alikhan/JAKS"

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


