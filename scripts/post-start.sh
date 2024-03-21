#!/usr/bin/env bash

# Post-start actions.
cd /app

composer install --no-suggest --no-interaction --no-progress
drush cr
drush cim -y
drush cr
drush uli