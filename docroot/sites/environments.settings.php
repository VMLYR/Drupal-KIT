<?php

// Set hash_salt from file.
$settings['hash_salt'] = file_get_contents($app_root . '/../config/salt.txt');

// Set config sync directory after server configuration (since Acquia overwrites
// the directory in their include files.)
// @note: Drupal suggests keeping sync directory at least 1 level below the
// site-specific config directory (IE "/config/www/default/" instead of
// "/config/www/"). This makes sure other site-specific files, like a hash-salt
// file or config-split-specific files, can be associated and read.
$config_directories[CONFIG_SYNC_DIRECTORY] = $app_root . '/../config/' . basename($site_path) . '/default';

// Setup per-environment config sync settings.
$config['config_split.config_split.remote_dev']['status'] = FALSE;
$config['config_split.config_split.remote_prod']['status'] = FALSE;
$config['config_split.config_split.remote_stage']['status'] = FALSE;
$config['config_split.config_split.local']['status'] = FALSE;

// Set Environment indicator default.
$config['environment_indicator.indicator']['bg_color'] = '#0080ff';
$config['environment_indicator.indicator']['fg_color'] = '#e6e6e6';
$config['environment_indicator.indicator']['name'] = 'Local';

// Determine passed-in environment.
$site_environment = '';
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $site_environment = $_ENV['AH_SITE_ENVIRONMENT'];
} elseif (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
    $site_environment = $_ENV['PANTHEON_ENVIRONMENT'];
} elseif (isset($_ENV['SITE_ENVIRONMENT'])) {
  $site_environment = $_ENV['SITE_ENVIRONMENT'];
} elseif (isset($_SERVER['SITE_ENVIRONMENT'])) {
  $site_environment = $_SERVER['SITE_ENVIRONMENT'];
}

// Enable appropriate config overrides.
switch ($site_environment) {
  case 'remote_prod':
  case 'prod':
    $config['config_split.config_split.remote_prod']['status'] = TRUE;
    $config['environment_indicator.indicator']['bg_color'] = '#000000';
    $config['environment_indicator.indicator']['fg_color'] = '#ffffff';
    $config['environment_indicator.indicator']['name'] = 'Prod';
    break;

  case 'remote_stage':
  case 'stage':
  case 'test':
    $config['config_split.config_split.remote_stage']['status'] = TRUE;
    $config['environment_indicator.indicator']['bg_color'] = '#008040';
    $config['environment_indicator.indicator']['fg_color'] = '#e6e6e6';
    $config['environment_indicator.indicator']['name'] = 'Stage';
    break;

  case 'remote_dev':
  case 'dev':
    $config['config_split.config_split.remote_dev']['status'] = TRUE;
    $config['environment_indicator.indicator']['bg_color'] = '#ffcc66';
    $config['environment_indicator.indicator']['fg_color'] = '#333333';
    $config['environment_indicator.indicator']['name'] = 'Dev';
    break;

  case 'local':
    $config['config_split.config_split.local']['status'] = TRUE;
    break;
}

// Enable docksal settings overrides.
if (file_exists($app_root . '/' . $site_path . '/settings.docksal.php')) {
  include $app_root . '/' . $site_path . '/settings.docksal.php';
}

// Enable local settings overrides.
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
