#!/bin/sh

# Run this script every after cloning the project.

PROJECT_PATH="/srv/www/proj266/"

cd "$PROJECT_PATH"
#
sudo chown apache:webdev "$PROJECT_PATH"logs -R
sudo chown apache:webdev "$PROJECT_PATH"php_sessions -R
sudo chown apache:webdev "$PROJECT_PATH"php_uploads -R
#
cd "$PROJECT_PATH"WebContent
sudo chown -R apache:webdev "$PROJECT_PATH"WebContent/coverage
#
sudo chmod g+w "$PROJECT_PATH" -R
