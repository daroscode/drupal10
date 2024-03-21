#!/usr/bin/env bash

# Post-start actions.
cd /app
drush cr
drush updatedb -y
drush cr
drush cim -y