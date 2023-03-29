<?php

namespace Drupal\rocketship_theme_generator;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\File\FileSystemInterface;

class ThemeExtentionGenerator {

  /**
   * @var string
   */
  private $module_name;

  /**
   * @var number
   */
  private $levels;

  /**
   * @var bool
   */
  private $override_files;

  /**
   * @var array
   */
  private $arrayTest = [];

  /**
   * themeExtentionGenerator constructor.
   */
  public function __construct($module_name, $levels, $override_files = FALSE) {
    $this->module_name = $module_name;
    $this->levels = $levels;
    $this->override_files = $override_files;
  }

  public function generateThemeExtention() {

    // check for theming component files: assets/theming -> subfolders & files
    // -- structures same as in theme: css, js/dest, components/01-atoms/my_component, components/my_new_feature (requires gulp changes), etc…
    // check for existence of the 'components' folder (will need to house our files there), otherwise throw an error
    // check for RS themes
    // -- why not copy only to active theme? Because we might need/want to activate another RS theme later
    // -- Check for string 'package: Rocketship Theme' inside the theme's .info file
    // check that the files & folder of our new component don't already exist, otherwise just give an info message

    // if all good, create folders & copy files over
    // -- means we can extend theme to add new components easily
    //    also means we can add new files to the existing components subfolders

    // Call a php script, located in the Rocketship Theme Generator module
    // -- check module is there (should be in dependencies of this module)
    // -- build a path to theme generator & the correct script, coming from current module path



    // - go to module and recursively copy all the files and folders
    //   to equivalent places in the themes
    //   - meaning: need to cd to the themes and NOT override existing files

    // find the module's theming assets

    $module_handler = \Drupal::service('module_handler');
    // path to the module we're copying assets from
    $module_path = $module_handler->getModule($this->module_name)->getPath();
    // path to the assets to copy
    $theming_assets_path = $module_path . '/' . 'assets/theming';
    $this->module_path = $module_path;
    $this->theming_assets_path = $theming_assets_path;

    // no theme might be active yet, so just save path to the themes folder
    // and we'll recursively dive in later
    $themes_path = 'themes/custom';
    $this->themes_path = $themes_path;

    // want to be able to show warnings or messages
    $messenger = \Drupal::messenger();

    $can_generate = true;

    $messenger->addWarning(t('Unable to generate the bundle as the theming assets location @s exist but is a file.', [
      '@s' => $this->theming_assets_path,
    ]));

    // ** check that our theming assets folder exists

    if (file_exists($module_path)) {
      if (file_exists($theming_assets_path)) {
        if(!is_dir($theming_assets_path)) {

          $can_generate = false;

          $messenger->addWarning(t('Unable to generate the bundle as the theming assets location @s exist but is a file.', [
            '@s' => $this->theming_assets_path,
          ]));

          // throw new \RuntimeException(
          //   sprintf(
          //     "Unable to generate the bundle as the theming assets location '%s' exists but is a file.",
          //     $this->theming_assets_path
          //   )
          // );
        }
      } else {

        $can_generate = false;

        $messenger->addWarning(t('Unable to generate the bundle as the theming assets location @s does not exist.', [
          '@s' => $this->theming_assets_path,
        ]));

        // throw new \RuntimeException(
        //   sprintf(
        //     "Unable to generate the bundle as the theming assets location '%s' does not exist.",
        //     $this->theming_assets_path
        //   )
        // );
      }
    }

    if (file_exists($themes_path)) {
      if(!is_dir($themes_path)) {

        $can_generate = false;

        $messenger->addWarning(t('Unable to generate the bundle as @s exists but is a file.', [
          '@s' => $this->themes_path,
        ]));

        // throw new \RuntimeException(
        //   sprintf(
        //     "Unable to generate the bundle as '%s' exists but is a file.",
        //     $this->themes_path
        //   )
        // );
      }
    } else {

      $can_generate = false;

      $messenger->addWarning(t('Unable to generate the bundle as @s does not exist.', [
        '@s' => $this->themes_path,
      ]));

      // throw new \RuntimeException(
      //   sprintf(
      //     "Unable to generate the bundle as '%s' does not exist.",
      //     $this->themes_path
      //   )
      // );
    }

    // if our assets and the theme folder exists, recursively copy the folders/files
    // to ALL the themes (because we don't know what theme the user may switch to later on)

    if ($can_generate) {

      $this->copyKitFilesDirs();

    }

  }

  /**
   * Copy files & directories from kit directory into custom theme directory.
   */
  private function copyKitFilesDirs() {

    $file_system = \Drupal::service('file_system');

    // for all themes folders in $this->themes_path
    $theme_dirs = array_filter(glob($this->themes_path . '/*'), 'is_dir');

    foreach ($theme_dirs as $theme) {

      $theme_name = preg_replace('/'. preg_quote($this->themes_path . '/', '/') . '/', '', $theme);

      // - we have 2 possible files for libraries snippet per component: one for a child theme and one for a normal RS theme
      // - so also 2 diff Sass files for correct globbing: one with rel links to parent theme as well as child theme
      // - detect if a theme is a child theme by looking for absense of 'base theme: classy' in the info yml
      // - if it is a child theme, it should copy only the 'child' libraries
      // - + also replace the correct parent machine name in the globbing Sass file
      // - otherwise, copy the 'normal' snippet and change nothing in it

      $info_file = $theme . '/' . $theme_name . '.info.yml';
      $info_data = file_get_contents($info_file);
      $base_theme = 'base theme: classy';
      $package = 'package: Rocketship Theme';
      $is_child = FALSE;
      $parent_theme = null;

      // only copy files for Rocketship themes

      if (stristr($info_data, $package)) {

        // if theme does not have Classy as a base theme,
        // it must be a child theme
        if (!stristr($info_data, $base_theme)) {

          $is_child = TRUE;

          // find the specific line for the base theme
          $info_lines = file($info_file, FILE_SKIP_EMPTY_LINES);

          foreach ($info_lines as $line) {
            if (stristr($line, 'base theme:')) {
              // find the machine name of the base theme
              $replaced_name = preg_replace('/'. preg_quote('base theme:', ':') . '/', '', $line);
              $parent_theme = trim($replaced_name);
            }
          }
        }

        // ** Take the libraries files first: copy and make changes

        $files = $file_system->scanDirectory($this->theming_assets_path, '/^libraries.yml|libraries.child.yml/');
        foreach ($files as $file) {

          // We don't want to copy the files that already exist,
          // as they could already have been modified.
          // Removing the files when we uninstall could also be a problem if the files
          // are referenced somewhere else. Since showing an error that it was not
          // possible to copy the files is also confusing, we silently do nothing.

          // copy the appropriate libraries snippet for either child theme or normal theme
          // depending on if we are in a child theme or not
          if (($file->filename == 'libraries.child.yml' && $is_child)
            || ($file->filename == 'libraries.yml' && $is_child === FALSE)) {

            $original_file = $theme . '/' . $theme_name . '.libraries.yml';
            $dest_file = $original_file;

            // if theme has a libraries file
            if (file_exists($original_file)) {

              // get content from source libraries file
              $original_data = file_get_contents($original_file);
              $comment = '#library copied from ' . $this->module_name;

              // if the source file is yet unchanged, update it
              if (!stristr($original_data, $comment)) {

                // append comment including with name of module, so we can detect if it was added
                file_put_contents($dest_file, "\n" . $comment, FILE_APPEND);

                // get content from new libraries snippet file
                $snippet_data = file_get_contents($file->uri);

                // replace in destination file
                file_put_contents($dest_file, "\n" . $snippet_data, FILE_APPEND);
              }

            }

          }

        }

        // ** now all the other files: copy them over to the theme

        $files = $file_system->scanDirectory($this->theming_assets_path, '/.*\.*/');
        foreach ($files as $file) {

          $new_asset = preg_replace('/'. preg_quote($this->theming_assets_path, '/') . '/', '', $file->uri);
          $destination_file = $theme . $new_asset;
          $destination_dir = preg_replace('/'. preg_quote($file->filename, '/') . '/', '', $destination_file);

          // for the non-libraries files
          if ($file->filename != 'libraries.yml' && $file->filename != 'libraries.child.yml') {

            // We don't want to copy the files that already exist,
            // as they could already have been modified.
            // Removing the files when we uninstall could also be a problem if the files
            // are referenced somewhere else. Since showing an error that it was not
            // possible to copy the files is also confusing, we silently do nothing.

            if (!file_exists($destination_file)) {

              // TO DO:
              // - for child theme: do not copy the normal style.*.scss files but only the style.child.*.scss files

              // prepare the folder where we're copying the file to
              $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
              // copy the file
              $file_system->copy($file->uri, $destination_file, FileSystemInterface::EXISTS_ERROR);

              // TO DO:
              // - for child theme: replace the machine names in the copied child style.child.*.scss files

              // if we are in a child theme AND our file is a .scss file that is not a paritial scss file
              // we need to find and replace the parent theme's name with the correct one
              if ($is_child && $parent_theme !== null && stristr($filenameeee, '%' . $parent_theme . '%')) {

                //

              }


            }

          }

        }

      }

    }

  }

}
