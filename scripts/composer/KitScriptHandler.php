<?php

/**
 * @file
 * Contains \DrupalProject\composer\KitScriptHandler.
 */

namespace DrupalProject\composer;

use Drupal\Component\Utility\Crypt;
use Drupal\Composer\Plugin\Scaffold\Handler;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;

class KitScriptHandler {

  /**
   * Modify core-composer-scaffold config prior to scaffold event.
   *
   * @param \Composer\Script\Event $event
   *   The composer event.
   */
  public static function scaffold($event) {
    /** @var \Composer\Script\Event $event */

    // Exit early if this isn't ran as a script.
    if (!is_a($event, \Composer\Script\Event::class)) {
      return;
    }

    // Get Dev mode from environment variable (set on install or update).
    $dev_mode = (getenv('COMPOSER_DEV_MODE') === '1' || getenv('COMPOSER_DEV_MODE') === FALSE);
    $fs = new Filesystem();
    $composer = $event->getComposer();
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $drupalRoot = $drupalFinder->getDrupalRoot();

    // Clear scripts so that we don't re-run the scaffold pre- and post-commands twice.
    $composer->getPackage()->setScripts([]);

    // Get drush aliases
    exec('drush sa --format=json --root=' . $drupalRoot, $output);
    $drush_aliases = (is_array($output)) ? json_decode(implode('', $output), TRUE) : [];
    $drush_aliases = (is_array($drush_aliases)) ? array_diff_key($drush_aliases, ['self' => [], 'none' => []]) : [];

    // Get sites from aliases.
    $sites = [];
    foreach (array_keys($drush_aliases) as $drush_alias) {
      $sites[] = explode('.', str_replace('@', '', $drush_alias), 2)[0];
    }
    $sites = array_unique($sites);

    // Get extra config to modify it.
    $extra = $event->getComposer()->getPackage()->getExtra();
    $web_root = isset($extra['drupal-scaffold']['locations']['web-root']) ? $extra['drupal-scaffold']['locations']['web-root']: '';
    $config_root = isset($extra['drupal-scaffold']['locations']['config-root']) ? $extra['drupal-scaffold']['locations']['config-root'] : 'config/';

    // Reset file mapping.
    $extra['drupal-scaffold']['file-mapping'] = [];


    // Removing core from packages and resetting allowed-packages to nothing so
    // that way no packages are ran other than our current scaffold script.
    $package_core = $composer->getRepositoryManager()->getLocalRepository()->findPackage('drupal/core', '*');
    $composer->getRepositoryManager()->getLocalRepository()->removePackage($package_core);
    $extra['drupal-scaffold']['allowed-packages'] = [];

    // Create each site's directories and files.
    foreach ($sites as $site) {
      $extra['drupal-scaffold']['file-mapping']["{$config_root}/{$site}/default/.gitkeep"] = [
        'path' => 'scripts/scaffold/.gitkeep',
        'overwrite' => FALSE,
      ];
      $extra['drupal-scaffold']['file-mapping']["[web-root]/sites/{$site}/files/.htaccess"] = [
        'path' => "scripts/scaffold/default-sites-files.htaccess",
        'overwrite' => FALSE,
      ];

      // Check whether the file exists before adding it (append doesn't
      // support overwrite, so this is the only way to handle this).
      if (!$fs->exists("{$web_root}/sites/{$site}/settings.php")) {
        $extra['drupal-scaffold']['file-mapping']["[web-root]/sites/{$site}/settings.php"] = [
          'append' => 'scripts/scaffold/default.settings.php--append.txt',
          'default' => "{$web_root}/core/assets/scaffold/files/default.settings.php",
        ];
      }

      // Only run when in dev mode (not in ci).
      if ($dev_mode) {
        $extra['drupal-scaffold']['file-mapping']["[web-root]/sites/{$site}/settings.docksal.php"] = [
          'path' => 'scripts/scaffold/example.settings.docksal.php',
          'overwrite' => FALSE,
        ];

        // Check whether the file exists before adding it (append doesn't
        // support overwrite, so this is the only way to handle this).
        if (!$fs->exists("{$web_root}/sites/{$site}/settings.local.php")) {
          $extra['drupal-scaffold']['file-mapping']["[web-root]/sites/{$site}/settings.local.php"] = [
            'append' => 'scripts/scaffold/example.settings.local.php--append.txt',
            'default' => "{$web_root}/core/assets/scaffold/files/example.settings.local.php",
          ];
        }
      }
    }

    // Add additional items back to the package.
    $composer->getPackage()->setExtra($extra);

    // Re-generate hashsalt.
    $event->getIO()->write('Generating Hash Salt');
    $fs->dumpFile($config_root . '/salt.txt', Crypt::randomBytesBase64(55));

    // Run modified scaffold.
    $handler = new Handler($composer, $event->getIO());
    $handler->scaffold();
  }

  /**
   * Write success message to output.
   *
   * @param \Composer\Script\Event $event
   *   The composer event.
   * @param $message
   *   The message to print.
   * @param $status
   *   The message status.
   */
  protected static function write(Event $event, $message, $status = NULL) {
    switch($status) {
      case 'success':
        $message = "\033[0;32m{$message}\e[0m";
        break;

      case 'success-bg':
        $message = "\033[30;42m{$message}\e[0m";
        break;

      case 'warning':
        $message = "\033[1;33m{$message}\e[0m";
        break;

      case 'error':
        $message = "\033[0;31m{$message}\e[0m";
        break;

    }
    $event->getIO()->write($message);
  }

  /**
   * Write sub-section message to output.
   *
   * @param \Composer\Script\Event $event
   *   The composer event.
   * @param $message
   *   The message to print.
   * @param $status
   *   The message status.
   */
  protected static function writeSub(Event $event, $message, $status = NULL) {
    self::write($event, '  ' . $message, $status);
  }

}
