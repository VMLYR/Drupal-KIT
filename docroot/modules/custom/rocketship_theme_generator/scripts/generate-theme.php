<?php

class GenerateTheme
{

  public function setParameters() {

    /**
     * Collect arguments passed to the script
     */
    $defaultValues = array(
      'name' => 'Rocketship Minimal',
      'machine-name' => 'rocketship_theme_minimal',
      'preset' => 'minimal',
      'theme-path' => 'dist',
      'author' => 'rembrandx',
      'description' => 'Component-based Drupal theme for use with Dropsolid Rocketship install profile, modules and other components.',
    );

    // adding ':' to an argument, indicates it as required
    // eg. 'theme:' would mean theme is a required argument
    // Without a colon here, only the presence of the argument would be detected, not the actual value
    $givenArguments = getopt('', array(
      'name:',
      'machine-name:',
      'preset:',
      'theme-path:',
      'author:',
      'description:'
    ));

    $options = array_merge(
      $defaultValues,
      $givenArguments
    );

    $contents = array(
      'skeleton_dirs' => 'templates',
      'dir' => $options['theme-path'] . '/' . $options['machine-name'],
      'kit_dirs' => array(
        '.storybook',
        'gulp',
        'images',
        'icons',
        'js',
        'css',
        'sh',
        'favicons',
        'fonts',
        'components',
        'config',
        'node_modules',
        'patches',
        'styleguide',
      ),
      'kit_files' => array(
        '.eslintrc.json',
        '.gitignore',
        '.nvmrc',
        'check_node_version.js',
        'Gulpfile.js',
        'logo.svg',
        'favicon.ico',
        'screenshot.png',
        'share-image.png',
        'node_modules/.metadata_never_index',
      ),
      'kit_files_temp' => array(

        '/' . 'README.md.gtemp',

        '/' . 'package.json.gtemp',
        '/' . 'package-lock.json.gtemp',

        '/'. 'BASE.info.yml.gtemp',
        '/'. 'BASE.libraries.yml.gtemp',
        '/'. 'BASE.layouts.yml.gtemp',
        '/'. 'BASE.breakpoints.yml.gtemp',
        '/'. 'BASE.theme.gtemp',
      )
    );


    $parameters = array_merge($options, $contents);

    $this->parameters = $parameters;

    $this->theme_name = $parameters['name'];
    $this->machine_name = $parameters['machine-name'];
    $this->theme_preset = $parameters['preset'];
    $this->theme_path = $parameters['theme-path'];
    $this->author = $parameters['author'];
    $this->description = $parameters['description'];
    $this->skeleton_dirs = dirname($_SERVER['PHP_SELF']) . '/../' . $parameters['skeleton_dirs'];
    $this->dir = $parameters['dir'];
    $this->kit_dirs = $parameters['kit_dirs'];
    $this->kit_files = $parameters['kit_files'];
    $this->kit_files_temp = $parameters['kit_files_temp'];

    $this->presets = ['minimal', 'starter', 'flex', 'demo'];
  }

  /**
   * Copy files & directories from kit directory into custom theme directory.
   */
  private function copyKitFilesDirs() {

    // recursively copy the BASE folders first
    foreach ($this->kit_dirs as $kit_dir) {

      $source = $this->skeleton_dirs . '/BASE/' . $kit_dir;
      $dest = $this->dir . '/' . $kit_dir;

      // copy only if original dir exists && target dir does not already exist
      if ( is_dir( $source ) && !is_dir( $dest )) {

        $this->copyr([
          'source' => $source,
          'dest' => $dest
        ]);
      }
    }

    // copy BASE files if they don't exist in destination yet
    foreach ($this->kit_files as $kit_file) {

      $source = $this->skeleton_dirs . '/BASE/' . $kit_file;
      $dest = $this->dir . '/' . $kit_file;

      // copy only if original file exists && target file does not already exist
      if ( file_exists( $source ) && !file_exists( $dest )) {

        $this->copyr([
          'source' => $source,
          'dest' => $dest
        ]);
      }
    }

    // ** copy more root files from BASE, which are template-based (.gtemp)
    //    these need name or variable replacements

    if ( isset($this->kit_files_temp) ) {
      foreach ($this->kit_files_temp as $kit_file) {
        $this->copyr([
          'source' => $this->skeleton_dirs . '/BASE' . '/'. $kit_file,
          'dest' => $this->dir
        ]);
      }
    }

    $this->copyr([
      'source' => $this->skeleton_dirs . '/BASE' . '/storybook-templates/'. 'webpack.config.js.gtemp',
      'dest' => $this->dir . '/' . '.storybook' . '/webpack.config.js'
    ]);

    // IF DEMO or FLEX or STARTER preset, need to use Minimal as a base
    // so first:
    // recursively go through the folders to copy only folders that don't exist yet
    // recursively copy (and overwrite) any files from the original folders to the same place in the target folders

    if ($this->theme_preset !== 'minimal') {

      foreach ($this->kit_dirs as $kit_dir) {

        $source = $this->skeleton_dirs . '/MINIMAL';
        $dest = $this->dir;

        $this->copyr([
          'source' => $source,
          'dest' => $dest
        ]);

      }

    }

    // IF FLEX or DEMO preset, need to use Starter to inherit from
    // so first:
    // recursively go through the folders to copy only folders that don't exist yet
    // recursively copy (and overwrite) any files from the original folders to the same place in the target folders

    if ($this->theme_preset !== 'minimal' && $this->theme_preset !== 'starter') {

      foreach ($this->kit_dirs as $kit_dir) {

        $source = $this->skeleton_dirs . '/STARTER';
        $dest = $this->dir;

        $this->copyr([
          'source' => $source,
          'dest' => $dest
        ]);

      }

    }

    // IF DEMO preset, need to use Flex as a base
    // so first:
    // recursively go through the folders to copy only folders that don't exist yet
    // recursively copy (and overwrite) any files from the original folders to the same place in the target folders

    if ($this->theme_preset === 'demo') {

      foreach ($this->kit_dirs as $kit_dir) {

        $source = $this->skeleton_dirs . '/FLEX';
        $dest = $this->dir;

        $this->copyr([
          'source' => $source,
          'dest' => $dest
        ]);

      }

    }

    // recursively go through the folders to copy only folders that don't exist yet
    // recursively copy (and overwrite) any files from the original folders to the same place in the target folders
    // This happens for the DEMO preset only (unless other presets were added after that)

    foreach ($this->kit_dirs as $kit_dir) {

      $source = $this->skeleton_dirs . '/' . strtoupper($this->theme_preset);
      $dest = $this->dir;

      $this->copyr([
        'source' => $source,
        'dest' => $dest
      ]);

    }

    // Check for theme dependencies specific for Dropsolid projects
    $dependencies = "docroot/modules/contrib/dropsolid_theme_dependencies/scripts/install_theme_dependencies.php";
    if ( file_exists( $dependencies )) {
      $machine_name = $this->machine_name;
      $theme_preset = $this->theme_preset;
      include $dependencies;
    }

  }

  /**
   *
   */
  private function startsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    return substr( $haystack, 0, $length ) === $needle;
  }

  /**
   *
   */
  private function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
  }

  /**
   * Copy a file, or recursively copy a folder and its contents
   * Also replaces theme name & machine name in twig files + special template files (.gtemp)
   * Modified from original
   *
   * @link              http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
   * @param $source     Source path
   * @param $dest       Destination path
   */
  private function copyr(array $parameters)
  {

    $source = $parameters['source'];
    $dest = $parameters['dest'];

    $replacements = [
      ['%theme_name%', $this->theme_name],
      ['%machine_name%', $this->machine_name],
      ['%preset%', $this->theme_preset],
      ['%theme_prefix%', str_replace("_","-", $this->machine_name)],
      ['%description%', $this->description],
      ['%author%', $this->author]
    ];

    /**
     * replaceable variables:
     * theme_name
     * machine_name
     * description
     * author
     * preset eg. 'flex'
     * theme_prefix = machine_name with all '_' replaced with '-'
     */

    // Simple copy for a file + replace some variables if it is a twig file & render_parameters exists,
    // if a source exists as a file (note: we will also overrides existing files)
    if (is_file($source)) {

      // if file or directory already exists at destination
      if (file_exists($dest)) {

        // if destination is also a file, can overwrite it
        // if dest is a folder, need to make a temp new dest using the same filename
        if (is_file($dest)) {
          // get content from source
          $data = file_get_contents($source);
          // replace in destination file
          file_put_contents($dest, $data);
        } else {
          // build a new file destination based on source file name & dest folder
          $path_parts = pathinfo($source);
          $file_name = $path_parts['basename'];

          // override $dest
          $dest = $dest . '/' . $file_name;
          copy($source, $dest);
          // set read and execute rights correctly
          chmod($dest, 0755);
        }

      // otherwise, copy file to new location
      } else {
        copy($source, $dest);
        // set read and execute rights correctly
        chmod($dest, 0755);
      }

      $data = file_get_contents($source);
      file_put_contents($dest, $data);
      $path_parts = pathinfo($source);

      // remove conditional chunks if needed

      if (isset($path_parts) && isset($path_parts['extension']) && $path_parts['extension'] === 'gtemp') {

        // for chunks with single setting if statements, eg. {% if !flex %}…{% fi !flex %}
        foreach ($this->presets as $preset ) {

          if ($preset === $this->theme_preset) {
            // remove chunks specifically NOT meant for current theme preset
            $chunk_regex_neg = "/{%\s*if\s*\!" . $preset . "\s*%}(?s)\s*\n(.*){%\s*fi\s*\!" . $preset . "\s*%}\s*\n/";
            $data = preg_replace($chunk_regex_neg, '', $data);

            // remove only the conditional statements for the ones that ARE meant for this preset
            $chunk_regex_pos_01 = "/{%\s*if\s*" . $preset . "\s*%}(?s)\s*\n/";
            $chunk_regex_pos_02 = "/{%\s*fi\s*" . $preset . "\s*%}\s*\n/";
            $data = preg_replace($chunk_regex_pos_01, '', $data);
            $data = preg_replace($chunk_regex_pos_02, '', $data);

          }

          // chunks meant for other presets
          foreach ($this->presets as $other_preset ) {

            if ($other_preset !== $this->theme_preset) {
              // remove the chunks with positive conditionals
              $chunk_regex_pos = "/{%\s*if\s*" . $other_preset . "\s*%}(?s)\s*\n(.*){%\s*fi\s*" . $other_preset . "\s*%}\s*\n/";
              $data = preg_replace($chunk_regex_pos, '', $data);

              // remove only the conditional statements for the negative ones
              $chunk_regex_neg_01 = "/{%\s*if\s*\!" . $other_preset . "\s*%}(?s)\s*\n/";
              $chunk_regex_neg_02 = "/{%\s*fi\s*\!" . $other_preset . "\s*%}\s*\n/";
              $data = preg_replace($chunk_regex_neg_01, '', $data);
              $data = preg_replace($chunk_regex_neg_02, '', $data);
            }
          }

          // for chunks with multiple conditions
          foreach ($this->presets as $preset ) {

            foreach ($this->presets as $other_preset ) {

              if ($this->presets !== $other_preset) {

                // current theme preset is NOT in the conditionals
                if ($preset !== $this->theme_preset && $other_preset !== $this->theme_preset) {

                  // remove chunks specifically NOT meant for current theme preset, eg. our preset is flex and conditional is % if demo or minimal %
                  $chunk_regex_pos = "/{%\s*if\s*" . $preset . "\s*or\s*" . $other_preset . "\s*%}(?s)\s*\n(.*){%\s*fi\s*" . $preset . "\s*or\s*" . $other_preset . "\s*%}\s*\n/";
                  $data = preg_replace($chunk_regex_pos, '', $data);

                  // remove only the conditional statements for the ones that ARE meant for this preset eg. our preset is flex and conditional is % if !demo and !minimal %
                  $chunk_regex_neg_01 = "/{%\s*if\s*\!". $preset . "\s*and\s*\!" . $other_preset . "\s*%}(?s)\s*\n/";
                  $chunk_regex_neg_02 = "/{%\s*fi\s*\!". $preset . "\s*and\s*\!" . $other_preset . "\s*%}\s*\n/";
                  $data = preg_replace($chunk_regex_neg_01, '', $data);
                  $data = preg_replace($chunk_regex_neg_02, '', $data);

                } else {
                  // remove chunks specifically NOT meant for current theme preset, eg. our preset is flex: % if !flex and !minimal %
                  $chunk_regex_neg = "/{%\s*if\s*\!". $preset . "\s*and\s*\!" . $other_preset . "\s*%}(?s)\s*\n(.*){%\s*fi\s*\!" . $preset . "\s*and\s*\!" . $other_preset . "\s*%}\s*\n/";
                  $data = preg_replace($chunk_regex_neg, '', $data);

                  // remove only the conditional statements for the ones that ARE meant for this preset
                  $chunk_regex_pos_01 = "/{%\s*if\s*". $preset . "\s*or\s*" . $other_preset . "\s*%}(?s)\s*\n/";
                  $chunk_regex_pos_02 = "/{%\s*fi\s*". $preset . "\s*or\s*" . $other_preset . "\s*%}\s*\n/";
                  $data = preg_replace($chunk_regex_pos_01, '', $data);
                  $data = preg_replace($chunk_regex_pos_02, '', $data);
                }
              }

            }

          }
        }
      }

      // replace variables if needed

      if (isset($path_parts) && isset($path_parts['extension']) && ($path_parts['extension'] === 'twig' || $path_parts['extension'] === 'gtemp')) {

        foreach ($replacements as $replacement) {
          $data = str_replace($replacement[0],$replacement[1], $data);
        }

        file_put_contents($dest, $data);

      }

      if (isset($path_parts) && isset($path_parts['extension']) && $path_parts['extension'] === 'gtemp') {

        $file_name = $path_parts['basename'];

        // if file is prefixed, replace prefix with the machine name

        if (strpos($file_name, 'BASE')) {
          $file_name = str_replace('BASE',$this->machine_name, $file_name);
        }

        foreach ($this->presets as $preset ) {
          if (strpos($file_name, strtoupper($preset))) {
            $file_name = str_replace(strtoupper($preset),$this->machine_name, $file_name);
          }
        }

        // if any other part of name contains the theme prefix

        if ($this->startsWith($file_name, 'BASE')) {
          $file_name = str_replace('BASE',$this->machine_name, $file_name);
        }

        foreach ($this->presets as $preset ) {
          if ($this->startsWith($file_name, strtoupper($preset))) {
            $file_name = str_replace(strtoupper($preset),$this->machine_name, $file_name);
          }
        }

        // remove the extention, if any
        $file_name = preg_replace('/.gtemp$/', '', $file_name);

        // if prefix or extention was actually replaced, we need te write out the new filename
        if ($file_name != $path_parts['basename']) {
          $new_dest = str_replace($path_parts['basename'], $file_name, $dest);
          rename($dest, $new_dest);
          // set read and execute rights correctly
          chmod($new_dest, 0755);
        }

      }

    }

    // if source exists as directory
    // see about copying (or making) the deeper dirs (& files)
    else if ( is_dir( $source )) {

      // Make destination directory
      // if doesn't exist yet
      if (!is_dir($dest)) {
        mkdir($dest);
        // set read and execute rights correctly
        chmod($dest, 0755);
      }

      $dir = dir($source);

      while (false !== $entry = $dir->read()) {

        // Skip pointers
        if ($entry == '.' || $entry == '..') {
          continue;
        }

        // Deep copy directories
        $this->copyr([
          'source' => "$source/$entry",
          'dest' => "$dest/$entry"
        ]);
      }

      // Clean up
      $dir->close();
    }

    return true;
  }

  /**
   * Generate the custom theme based on the arguments.
   */
  public function init()
  {

    // check that path exists
    if (file_exists($this->theme_path)) {
      if(!is_dir($this->theme_path)) {
        throw new \RuntimeException(
          sprintf(
            "Unable to generate the bundle as the directory '%s' exists but is a file.",
            $this->theme_path
          )
        );
      }
    } else {
      // try to make the folder
      mkdir($this->theme_path);
    }

    // if already exists, check that we can copy files to new theme folder

    if (file_exists($this->dir)) {
      if (!is_dir($this->dir)) {
        throw new \RuntimeException(
          sprintf(
            "Unable to generate the bundle as the target directory '%s' exists but is a file.",
            realpath($this->dir)
          )
        );
      }

      $files = scandir($this->dir);
      if ($files != ['.', '..']) {
        trigger_error("Unable to generate the bundle as the target directory " . $this->dir . " already exists or is not empty.", E_USER_WARNING);
        return;
      }

      if (!is_writable($this->dir)) {
        throw new \RuntimeException(
          sprintf(
            "Unable to generate the bundle as the target directory '%s' is not writable.",
            realpath($this->dir)
          )
        );
      }

    // create the theme folder, if doesn't exist yet
    } else {
      mkdir($this->dir);
      // set read and execute rights correctly
      chmod($this->dir, 0755);
    }

    echo "Generating theme $this->theme_name \n";

    // ** copy the rest of the theme files from BASE + whatever preset folder is chosen
    //     also replaces theme name & machine name in twig files + special template files

    $this->copyKitFilesDirs();

  }

}

$class = new GenerateTheme();

$class->setParameters();
$class->init();
