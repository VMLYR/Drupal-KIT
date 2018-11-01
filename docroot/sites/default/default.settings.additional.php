<?php

/**
 * Fast 404 pages for specific paths (missing files).
 */
$config['system.performance']['fast_404']['exclude_paths'] = '/\/(?:styles)|(?:system\/files)\//';
$config['system.performance']['fast_404']['paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$config['system.performance']['fast_404']['html'] = '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

/**
 * Local Database configuration.
 *
 * @note This is purposely left empty since the docksal settings file
 * automatically includes the correct database settings for docksal.
 */
$databases = [];

/**
 * Include Server-specific configuration here.
 */
# @todo include server specific information here

/**
 * Include environment-specific (local, dev, prod, docksal, etc.) settings.
 *
 * REMEMBER: Make sure this is included AFTER the server-specific config.
 */
$environments_settings = __DIR__ . '/../environments.settings.php';
if (file_exists($environments_settings)) {
  include $environments_settings;
}
