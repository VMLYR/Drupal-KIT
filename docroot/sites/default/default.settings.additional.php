<?php

/**
 * Fast 404 pages for specific paths (missing files).
 */
$config['system.performance']['fast_404']['exclude_paths'] = '/\/(?:styles)|(?:system\/files)\//';
$config['system.performance']['fast_404']['paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$config['system.performance']['fast_404']['html'] = '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

/**
 * Local database configuration.
 *
 * @note This is purposely left empty since the docksal settings file
 * automatically includes the correct database settings for docksal.
 */
$databases = [];

/**
 * Include server-specific configuration.
 */
# @todo Acquia Configuration
//if (file_exists('/var/www/site-php')) {
//  require '/var/www/site-php/PROJECTNAME/PROJECTNAME-settings.inc';
//}
# @todo AWS/Custom Configuration (replace $_SERVER variables as needed)
//$databases['default']['default'] = [
//  'database' => $_SERVER['RDS_DB_NAME'],
//  'username' => $_SERVER['RDS_USERNAME'],
//  'password' => $_SERVER['RDS_PASSWORD'],
//  'prefix' => '',
//  'host' => $_SERVER['RDS_HOSTNAME'],
//  'port' => $_SERVER['RDS_PORT'],
//  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
//  'driver' => 'mysql',
//];
# @todo Pantheon Configuration
// include __DIR__ . "/settings.pantheon.php";
# @todo Platform.sh Configuration
// include __DIR__ . "/settings.platformsh.php";

/**
 * Include environment-specific (local, dev, prod, docksal, etc.) settings.
 *
 * REMEMBER: Make sure this is included AFTER the server-specific config.
 */
$environments_settings = __DIR__ . '/../environments.settings.php';
if (file_exists($environments_settings)) {
  include $environments_settings;
}
