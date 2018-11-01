<?php

/**
 * @file
 * Contains \DrupalProject\composer\KitScriptHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;

class KitScriptHandler {

  public static function cleanDirectories($event) {
    self::write($event, '=== Running KIT pre-install ===', 'success');

    // Exit early if DrupalFinder doesn't exist.
    if (!class_exists('DrupalFinder\DrupalFinder')) {
      self::write($event, 'Skipping directory cleaning: DrupalFinder doesn\'t exist');
      return;
    }

    // Initialize filesystem.
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $drupalRoot = $drupalFinder->getDrupalRoot();

    $dirs = [
      'KIT Docksal Tools' => '.docksal/commands/kit/',
      'Vendor' => 'vendor/',
      'Drupal Core' => "{$drupalRoot}/core",
      'Libraries' => "{$drupalRoot}/libraries/*/",
      'Contrib Modules' => "{$drupalRoot}/modules/contrib",
      'Contrib Themes' => "{$drupalRoot}/themes/contrib"
    ];

    // Remove directories.
    foreach ($dirs as $title => $dir) {
      self::write($event, "Removing directory: {$title}");
      exec("sudo rm -rf $dir");
    }

    // Reloading package repositories.
    self::write($event, 'Reloading: packages');
    $event->getComposer()->getRepositoryManager()->getLocalRepository()->reload();
  }

  public static function scaffold(Event $event) {
    self::write($event, '=== Running KIT post-install ===', 'success');
    $fs = new Filesystem();
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $drupalRoot = $drupalFinder->getDrupalRoot();

    $dir_config = "{$drupalRoot}/../config";
    $dir_sites = "{$drupalRoot}/sites";

    $dirs = [
      '../config',
      'libraries',
      'modules',
      'profiles',
      'themes',
    ];

    // Creating required directories.
    self::write($event, 'Creating: required directories');
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupalRoot . '/'. $dir)) {
        $fs->mkdir($drupalRoot . '/'. $dir);
        $fs->touch($drupalRoot . '/'. $dir . '/.gitkeep');
      }
    }

    // Remove various scaffolded files.
    self::write($event, 'Removing: various scaffolded files');
    $files = [
      $drupalRoot . '/robots.txt',
      $drupalRoot . '/../.docksal/commands/kit/.git',
      $drupalRoot . '/../.docksal/commands/kit/composer.json'
    ];
    foreach ($files  as $file) {
      if ($fs->exists($file)) {
        $fs->remove($file);
      }
    }

    // Set file names to create compiled default settings file.
    $file_name_default = $drupalRoot . '/sites/default/default.settings.php';
    $file_name_additional = $drupalRoot . '/sites/default/default.settings.additional.php';

    // Make sure that the contents were successfully loaded (offset set to 5 to
    // remove <?php at the beginning of the file).
    $additional_content = file_get_contents($file_name_additional, false, NULL, 6);
    if (is_bool($additional_content)) {
      $additional_content = '';
    }

    // Append additional filed to compiled file.
    self::write($event,'Updating: default.settings.php file');
    file_put_contents($file_name_default, $additional_content, FILE_APPEND);

    // Creating hash-salt.
    if (!$fs->exists($dir_config . '/salt.txt')) {
      self::writeSub($event,'Invoking: hash-salt file creation');
      $fs->dumpFile($dir_config . '/salt.txt', \Drupal\Component\Utility\Crypt::randomBytesBase64(55));
    }
    else {
      self::write($event,'Skipping: hash-salt file creation - already exists');
    }

    // Get drush aliases
    exec('drush sa --format=php', $output);
    $drush_aliases = unserialize(implode('', $output));

    // Get sites from aliases.
    $sites = [];
    foreach (array_keys($drush_aliases) as $drush_alias) {
      $sites[] = explode('.', str_replace('@', '', $drush_alias), 2)[0];
    }
    $sites = array_unique($sites);

    // Create each site's directories andfiles.
    foreach ($sites as $site) {
      self::write($event, 'Site: ' . $site, 'success');

      // Create site directory.
      $dir_site = "{$dir_sites}/{$site}";
      if (!$fs->exists($dir_site)) {
        self::writeSub($event,'Invoking: site folder creation');
        $fs->mkdir($dir_site);
      }
      else {
        self::writeSub($event,'Skipping: site folder creation - already exists');
      }

      // Update site directory permissions.
      self::writeSub($event,'Invoking: update site folder permissions');
      $fs->chmod($dir_site, 0755);

      // Create site settings file.
      if (!$fs->exists($dir_site . '/settings.php')) {
        self::writeSub($event,'Invoking: site settings.php replication from default');
        $fs->copy($drupalRoot . '/sites/default/default.settings.php', $dir_site . '/settings.php');
      }
      else {
        self::writeSub($event,'Skipping: site settings.php replication - already exists');
      }

      // Create site docksal settings file.
      if (!$event->isDevMode()) {
        self::writeSub($event,'Skipping: site settings.docksal.php replication - operating in no-dev mode');
      }
      else if (!$fs->exists($dir_site . '/settings.docksal.php')) {
        self::writeSub($event,'Invoking: site settings.docksal.php replication from default');
        $fs->copy($drupalRoot . '/sites/default/default.settings.docksal.php', $dir_site . '/settings.docksal.php');
      }
      else {
        self::writeSub($event,'Skipping: site settings.docksal.php replication - already exists');
      }

      // Create site local settings file.
      if (!$event->isDevMode()) {
        self::writeSub($event,'Skipping: site settings.local.php replication - operating in no-dev mode');
      }
      else if (!$fs->exists($dir_site . '/settings.local.php')) {
        self::writeSub($event,'Invoking: site settings.local.php replication from default');
        $fs->copy($drupalRoot . '/sites/example.settings.local.php', $dir_site . '/settings.local.php');
      }
      else {
        self::writeSub($event,'Skipping: site settings.local.php replication - already exists');
      }

      // Create site config directory.
      $dir_config_site = "{$dir_config}/{$site}";
      if (!$fs->exists($dir_config_site)) {
        self::writeSub($event,'Invoking: site config folder creation');
        $fs->mkdir($dir_config_site);
        $fs->touch($dir_config_site . '/.gitkeep');
      }
      else {
        self::writeSub($event,'Skipping: site config folder creation');
      }

      // Create site default config directory.
      $dir_config_site_default = $dir_config_site . '/default';
      if (!$fs->exists($dir_config_site_default)) {
        self::writeSub($event,'Invoking: site default config folder creation');
        $fs->mkdir($dir_config_site_default);
        $fs->touch($dir_config_site_default . '/.gitkeep');
      }
      else {
        self::writeSub($event,'Skipping: site default config folder creation - already exists');
      }
    }

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
