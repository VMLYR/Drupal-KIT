<?php

/**
 * This file exists as an alias example for Drush 8 projects. Unless otherwise
 * required, we suggest using Drush 9 on future projects and removing this file.
 */

$aliases['local'] = [
  'site-env' => 'local',
  'uri' => 'http://www.PROJECTNAME.docksal',
  'root' => '/var/www/docroot',
];
$aliases['remote_dev'] = [
  'site-env' => 'dev',
  'uri' => 'http://dev-www.PROJECTNAME.com',
  'host' => '',
  'user' => '',
  'root' => '',
];
$aliases['remote_stage'] = [
  'site-env' => 'stage',
  'uri' => 'http://stg-www.PROJECTNAME.com',
  'host' => '',
  'user' => '',
  'root' => '',
];
$aliases['remote_prod'] = [
  'site-env' => 'prod',
  'uri' => 'http://www.PROJECTNAME.com',
  'host' => '',
  'user' => '',
  'root' => '',
];
