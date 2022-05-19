<?php

/**
 * @file
 * Drupal docksal settings file.
 */

// Workaround for permission issues with NFS shares in Vagrant.
$settings['file_chmod_directory'] = 0777;
$settings['file_chmod_file'] = 0666;

// Reverse proxy configuration (Docksal's vhost-proxy).
if (PHP_SAPI !== 'cli') {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = array($_SERVER['REMOTE_ADDR']);
  // HTTPS behind reverse-proxy.
  if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' &&
    !empty($settings['reverse_proxy']) && in_array($_SERVER['REMOTE_ADDR'], $settings['reverse_proxy_addresses'])
  ) {
    $_SERVER['HTTPS'] = 'on';
    // This is hardcoded because there is no header specifying the original port.
    $_SERVER['SERVER_PORT'] = 443;
  }
}

// Setup redis support.
if (\Drupal::hasService('cache.backend.redis')) {
  $settings['redis.connection']['host'] = 'redis';
  $settings['redis.connection']['port'] = NULL;
  $settings['cache']['default'] = 'cache.backend.redis';
  $settings['redis.connection']['base'] = 8;
}

/**
 * Docksal Database configuration.
 */
$databases['default']['default'] = array (
  'database' => basename($site_path),
  'username' => 'user',
  'password' => 'user',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
