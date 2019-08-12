<?php

echo "Enabling maintenance mode.\n";
passthru('drush sset system.maintenance_mode 1 --strict=0');

echo "Updating database.\n";
passthru('drush updatedb -y --strict=0');

echo "Importing configuration.\n";
passthru('drush cim sync -y --strict=0');

echo "Clearing site cache.\n";
passthru('drush cr');

echo "Disabling maintenance mode.\n";
passthru('drush sset system.maintenance_mode 0 --strict=0');
