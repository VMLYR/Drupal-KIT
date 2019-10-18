# Drupal 8 KIT

VMLY&R's Drupal 8 KIT is a distribution which helps build and maintain
Drupal 8 projects.

* [About KIT](#about-kit)
* [Getting Started](#installation)
* [Post-installation and provider-related configuration](#post-installation-and-provider-related-configuration)
* [Notes & Suggestions](#notes-and-suggestions)
* [Theme Development](#theme-development)



## About KIT

Via a suite of Docksal commands, an installation
profile, and a base theme, the distribution provides support
for the following:
 * [Common modules](#common-modules-+-_composer.json_)
 * [Composer project scaffolding](#composer-project-scaffolding)
 * [Docksal command suite](#docksal-command-suite)
 * [Using Docksal for CI and build processes](#docksal-+-ci-and-build-processes)
 * [Drupal site configuration](#drupal-site-configuration)
 * [Multi-environment configuration and development](#multi-environment-configuration-and-development)
 * [Multi-site configuration and development](#multi-site-support)
 * [Standardized theme management and structure](#standardized-theme-management-and-structure)

### Common modules + _composer.json_

KIT uses composer for handling project dependencies. The distribution
includes a selection of Modules and libraries by default. The list was
determined using either the following criteria:
 - *Best-practices* – Drupal 8 Core is a much more viable product
 out-of-the-box than Drupal 7 was, but there are still certain modules
 and configuration that we need and should be using for every site,
 including support for multiple environments. Examples of these are
 Blazy for lazy-loading images, Advanced Aggregation for better
 style/script management, Configuration Sync + Config Split (instead of
 Features).
 - *Standardization* – being an open-source project, Drupal has a tendency
  of having multiple modules with similar solutions to similar problems.
  We've added items to the list by default to help standardize the tools
  to handle certain problems, an example being a field to include a view
  on an entity.

### Composer project scaffolding
KIT enforces structure by adding it's own scaffolding, which runs after
Drupal's default scaffolding on composer install and update. Drupal's
scaffolding automatically wipes and re-copies the files which get copied
outside of Drupal core and into public directories
(like default.settings.php, htaccess, robots.txt, etc.). VMLY&R
additionally creates the standard config folder structure, as well as
removes and updates files that should be modified via patches
(like .htaccess) or compiled (like drupal.settings.additional.php
compiling into drupal.settings.php) instead of being checked-into the
repository directly.

### Docksal command suite
The project uses [Docksal](https://docksal.io/) for local-environment
development and project-building. The following is a list of Docksal
commands that come included in the project:
 - `init` – Typically used when building or rebuilding the project
 and its dependencies. The command will start docksal, download
 dependencies, make sure that the project's relevant sites' directories
 and databases exits, build front-end artifacts, and import databases
 from another environment. The command takes an optional parameter
 `builder`, which is explained in detail below under the
 "Docksal + CI and build processes" section.
 - `init-deps` – Used when project dependencies need to be redownloaded 
 and installed. Currently consists of Composer and NPM.
 - `init-services` – Used when project-based command and tool dependencies
 need to be redownloaded and installed. Currently consists of validating 
 that nvm, npm, and node are available in the container.
 - `k` – A simple wrapper command to make it easier to run kit commands.
 For example, `fin k gulp` instead of `fin kit/gulp`.
 - `pre-deploy` – This is used in situations where there needs to be 
 pre-deployment cleanup / modifications. Currently used to remove files 
 from build artifact that don't need to be on an external environment.

Additional commands are included via the `vmlyr-drupal/kit-docksal-commands`
package and installed under a "kit" sub directory in the docksal commands
directory. See the README.md file in that directory after composer is
installed for more information on those commands.

### Docksal + CI and build processes

Docksal and KIT's commands can be used for running build processes. The
command `init` can toggle "CI mode" by running appending the
`ci` command: `fin init ci`. This is best used when
docksal is used to create the build files to be released. By default,
the builder runs composer in --no-dev mode, and auto-removes
build-related files amound other things.

### Drupal site configuration
This composer project comes with 2 VMLY&R-created Drupal profiles:
 - _Biplane_ – This profile has a lot of required site-configuration and
 relevant modules installed and setup by default. It's fairly bare-bones
 besides items related to best-practices and standardization, but it
 does take care of a lot of monotony that comes with installing a new
 Drupal instance. Some examples are:
   - Default environments (local, remote_dev, remote_stage, remote_prod)
   and their relevant config-splits and settings and modules.
   - Core files modified/removed by default, like .htaccess _https_
   overrides, default settings.php changes, using RobotsTXT module
   instead of the included core file, etc.
   - Lighthouse-related settings, like Image Optimization, Responsive
   Images + Focal Point, Advanced Aggregation, Blazy, etc.
   - Various settings and needed for go-live on new sites, like setting
   up Advanced Aggregation for production instances, default metatags
   for global and Node pages, XML Sitemap defaults, disabling anonymous
   user registration, etc.
 - _Blackbird_ – This profile builds off of the _Biplane_ profile, but is
 a little more opinionated. It includes helpful Paragraph components,
 Image Styles, Media implementations, among a slew of additional
 configuration. To get the full use of the additional components, make
 sure to run `fin kit/theme` and generate a new theme off of the 
 "Blackbird" option of the same name.

### Multi-environment configuration and development

Multi-environment configuration is pre-configured as part of the
distribution via the default `settings.php` file and the _Biplane_ and _Blackbird_ profiles.

#### Environments + _Settings.php_ file
The each site's default `settings.php file` includes the
`/sites/environment.settings.php`, which has configuration to toggle the
status of relevant configuration based on the current site's
environment. Remote environments, such as those hosted by Acquia,
Pantheon, or Platform.sh, have `$_ENV` or `$_SERVER` variables which
tell the site that it is a certain environment and to enable certain
configuration or settings. When working on the site locally, all
configuration splits should say either "inactive". The "local"
config_split status is only enabled by the fin command `conf` on
export/import. This makes sure that configuration can be exported
regularly without a certain environment taking accidental precedence.

Development and docksal settings files are automatically included via
the `environment.settings.php` file, if they exist. These files are
ignored by git and helpful for development overrides such as disabling
cache or allowing verbose error reporting. The KitScriptHandler script,
which is ran by composer, automatically copies a `settings.local.php`
and `settings.docksal.php` into each sites directories to better assist
local development.

#### Environments + the VMLY&R Profiles _Biplane_ and _Blackbird_
The VMLY&R profiles include config-split options by default, and have
additional tasks during install to establish the default configuration
and each split's configuration, as well as import as the local
environment before the installation is complete.

The four included default configurations include:
 - Local (_local_) – Local development configuration overrides, such as
 disabling page cache and advanced aggregation, and enabling development
 modules.
 - Development (_remote_dev_) - Mainly enabling _Shield_ authentication
 and Stage File Proxy.
 - Stage (_remote_stage_) – Mainly enabling _Shield_ authentication and
 Stage File Proxy.
 - Production (_remote_prod_) – Enables Syslog and form Captcha.

### Multi-site support

Multi-site support is baked into the project via our supplied Docksal
tools. When another site needs to be added, it's as simple as creating a
the new drush alias file and running `composer install`. The command will
build out the site folder structure, copy the default settings.php file
into the site directory and is then ready for installation. The `kit/sync`
and `kit/conf` tools both support syncing and exporting/importing any
site in a multi-site Drupal instance.

### Standardized theme management and structure
KIT automatically includes a base theme (Bazo) and two scaffolding
themes which use Bazo as a base theme.

#### _Bazo_ base theme
The _Bazo_ base theme is meant to help assist its child-themes. It
includes standard templates but moves most of the classes into its
preprocesses to allow them to be more-easily removed later by the child
preprocess. The two more-important factors of the subtheme are:
 - Automatic Drupal library attachment – Bazo automatically attaches
 child-theme libraries to their related entities as long as they follow
 a naming convention:
   - theme-name/entity-id
   - theme-name/entity-id--view-mode
   - theme-name/entity-id--view-mode--bundle

   This not only helps your project stay organized, but it allows
 front-end developers to attach their libraries to components without
 needing to touch PHP.

   Note: notice the _entity-id--view-mode--bundle_ is different than
 Drupal's default theme-name convention of _entity-id--bundle--view-mode_.
 This was done purposely, because typically all bundles of a certain
 view-mode will share a library versus all view-modes sharing a library
 of a certain bundle. This allows a universal library to be included for
 view-modes and then more-specific implementations that are
 bundle-specific also be included .
 - Automatic attribute-variable conversion – Baso automatically converts
 specified arrays to attribute variables in a "postprocess" function
 that are listed in the preprocess's
 `$variables['#attribute_variables']` array. IE:
   - `$variables['#attribute_variables'][] = 'figcaption_attributes';`
   - `$variables['#attribute_variables'][] = 'wrapper_attributes';`
   - `$variables['#attribute_variables'][] = 'figcaption_attributes';`
   - `$variables['#attribute_variables'][] = 'image_attributes';`

#### Scaffolded child-themes via a Docksal command
VMLY&R has a couple scaffolding themes included to build from, but
they're not included in the project directly. Instead they can be
generated via the `fin kit/theme` command.

## Installation

Getting a running site takes only a few steps for a project.

1. [Install Docksal](https://docksal.io/installation) if it's not already installed.
1. Install the project.
    1. Use composer to create the new project. *Note: try not to use hyphenated project names if possible, docksal currently has weird issues with projects with hyphens.*
       ```
       fin run-cli "composer create-project --no-install vmlyr-drupal/kit [FOLDER_NAME_HERE]"
       ```
    1. Change into the directory.
        ```
        cd [FOLDER_NAME_HERE]
        ```
    1. If this directory was not already a git project, initialize the
    new repository
        ```
        git init
        ```
    1. If this project has a remote repository, add the remote origin
        ```
        git remote add origin [REMOTE_REPOSITORY_URL_HERE]
        ```
        
1. Run `fin start` in the project to create the Docksal project.
1. Open each site's Drush alias file (`/drush/sites/` to update the
local URI as well as any relevant server information if it's already
known. _(note: Your local should have been listed by Docksal at the end
of running `fin start`)_
1. If running a multisite install, open `/sites/sites.php` folder to
 make sure domains are mapped to the correct site.

    **Docksal's domain name is the project folder name in alphanumeric
    appended with .docksal. (I.E. _drupal-8-kit_ becomes
    _drupal8kit.docksal_)**
    ```
    $sites['domainname.docksal'] = 'www';
    $sites['www.domainname.docksal'] = 'www';
    ```
    **External domains are mapped here as well.**
    ```
    $sites['www.domainname.com'] = 'www';
    $sites['dev-www.domainname.com'] = 'www';
    $sites['stg-www.domainname.com'] = 'www';
    $sites['prod-www.domainname.com'] = 'www';
    ```
    **Docksal supports subdomains, and Drupal supports mapping
    subdomains on a multi-site install.**
    ```
    $sites['subdomain.domainname.docksal'] = 'subdomain';
    ```
    **External subdomains are mapped here as well.**
    ```
    $sites['subdomain.domainname.com'] = 'subdomain';
    $sites['dev-subdomain.domainname.com'] = 'subdomain';
    $sites['stg-subdomain.domainname.com'] = 'subdomain';
    $sites['prod-subdomain.domainname.com'] = 'subdomain';
    ```
1. Run `fin init`
1. Open a browser and go to the site; it should have been redirected to
the site install page.
1. Walk through the install process. _(note: the site should
automatically start installing after a profile is selected; if it
doesn't, and it asks for database settings, reload the
`/core/install.php` URL without any GET parameters)_
1. Fill out the site configuration. If using either of the VMLY&R
Profiles and the environment URLs are unknown, make a best guess at what
they could be
_(we suggest following the `//ENV-WWW.SITE_PROD.DOMAIN` structure)_.
The domains are used for indicating current environment and using other
environment's assets via _Stage File Proxy_. Setting these up now helps
not needing to set these in multiple places later on during development.
1. If you selected either of the VMLY&R profiles, upon saving
configuration, the site should export all relevant configuration into
the site's sync directory and import as the local environment.
1. Installation is complete once redirected to the homepage of the site.
1. To start building your own theme, run `fin kit/theme` to
generate a new theme + theme source setup based on our example scaffold
themes. If the _Blackbird_ profile was installed, we suggest scaffolding
from the _Blackbird_ theme option of the same name. If you're not using 
_Blackbird_ and want a more simple starting point for you theme we 
suggest scaffolding from Biplane scaffold theme.

## Post-installation and provider-related configuration
Based on the hosting provider, some configuration needs to be created or 
updated. Similarly, some configuration will also be unneeded and can be 
removed.

### Jenkins
@TODO Jenkins setup walk-through.

### Bitbucket pipelines
To enable bitbucket pipeline builds, rename `bitbucket-pipelines-example.yml` 
to `bitbucket-pipelines.yml`. Note: Bitbucket Pipelines needs to be 
enabled in bitbucket to work.
#### Setup
There is a small amount of configuration to get pipelines talking to 
your external repository after copying the pipelines file into the 
repository. In your Bitbucket account:
- go to Settings > Pipelines > Settings and toggle "Enable Pipelines"
- go to Settings > Pipelines > Repository Variables and add a variable named `DESTINATION_REPOSITORY` with the url to your hosting providers repository (example: `ssh://codeserver.example.drush.in:2222/~/repository.git` for Pantheon).
- go to Settings > Pipelines > Deployments and configure/create the Deployment environments you want to connect to.
  - Rename the environment, make sure it matches the `deployment` key:value inside your pipelines file (example: `deployment: Development`)
  - Add a variable here named `DESTINATION_REPOSITORY_BRANCH` and put the value as the branch you want to push into on your hosting providers repository (example: `master`).
- go to Settings > Pipelines > SSH Keys. These keys are what are used to connect to your hosting provider repository.
  - Generate a key pair. _Note: Acquia requires a stronger key-pair than the default that Bitbucket generates. Instead of clicking the generate button that Bitbucket provides, you will need to create the keypair manually by running `ssh-keygen -b 4096` in your local terminal (consider naming it something descriptive) and then adding its private and public keys to Bitbucket._
  - Take the public key and add it to a user on the hosting provider repository. It's best to use either a deployment key if the provider supports it, or create a service account solely for connecting the provider to pipelines (example: a user on the provider with pipelines@yourwebsite.com that is solely for holding the connection to pipelines).
  - Take the host address of the hosting provider's repository and place it in the "Host addresses" field in the Known Hosts area, then fetch the fingerprint to make sure the connection is validated.
You should now be able to push up your change and watch the pipelines kick off. 

_Note: sometimes it takes some playing-around-with to make sure that pipelines can connect to the hosting-provider repository, such as recreating the key pairs._

#### Default pipelines provided
##### Branch: Master
This pipeline watches the master branch, and when code is merged into it, automatically builds and deploys the code to the relevant "Development" environment repository.

This pipeline uses the default "Build package" and "Deploy package" pipeline steps. The "Deploy package" step defaults to run the "Development" deployment. 

_In scenarios where there should be a development branch building to the Development environment, and the master branch building to Stage or Production environments, the `deployment` would be changed to the relevant environment name, and a new branch-based pipeline would be created to push to Development._
##### Custom: Feature
This pipeline takes any branch and allows to build to a custom "feature" branch on the hosting provider repository. This allows for easily creating "Feature" environments on the hosting provider.

This pipeline uses the default "Build package" and "Deploy package" pipeline steps. "The Deploy package" step defaults to run a "Feature" deployment, which will need to be created under the Settings > Pipelines > Deployments tab on Bitbucket Pipelines. 

To run this pipeline:
- go to the "Branches" section of your Bitbucket account
- click the "..." to the very right of the branch that you want to build
- select "Run pipeline for a branch"
- select the "custom: feature" pipeline
- fill out what you want the feature branch on the hosting provider repo to be called in the "DESTINATION_REPOSITORY_BRANCH" field (ex: feature/header-redesign)
- click "Run"

##### Pull-Requests
This pipeline automatically runs on pull-requests. It does a full build and then lints the code; it fails if any issues arise.

This pipeline uses the default "Build package" and "Test package" pipeline steps. It does not deploy code.

### Files included in KIT
The following are grouped to give context for which 
files/directories can be modified or removed.

###### Provider-specific files: Acquia
- `/hooks/common`
- `/hooks/dev`
- `/hooks/prod`
- `/hooks/samples`
- `/hooks/test`
###### Provider-specific files: AWS
- `/.ebextensions`
###### Provider-specific files: Pantheon
- `/pantheon.yml`
- `/scripts/pantheon/*`
###### Provider-specific files: Platform
###### Universal files
- `/docroot/web.config` (we don't typically use ASP.NET; kept for reference if needed in the project)
- `/env.example` (we don't suggest env files; kept for reference if needed in the project)
- `/package.json` (was used by packagist to create project and no longer needed)
- `/travis.yml` (we don't typically use travis; kept for reference if needed in the project)

### Provider-specific modifications
Modifications to make to the project based on which hosting provider is 
chosen.

###### Provider-specific modifications: Acquia
1. Remove unnecessary files and directories for Acquia:
    - [List of universal files to remove](#universal-files)
    - [List of AWS files and directories](#provider-specific-files-aws)
    - [List of Pantheon files and directories](#provider-specific-files-pantheon)
    - [List of Platform.sh files and directories](#provider-specific-files-platform)
1. Settings.php modifications
    1. Open `/docroot/sites/www/settings.php` and find the "Include server-specific configuration." section.
    1. Uncomment the Acquia portion.
    1. Remove uneeded server-specific configuration from other providers.
###### Provider-specific modifications: AWS
1. Remove unnecessary files and directories for Platform.sh:
    - [List of universal files to remove](#universal-files)
    - [List of Acquia files and directories](#provider-specific-files-acquia)
    - [List of Pantheon files and directories](#provider-specific-files-pantheon)
    - [List of Platform.sh files and directories](#provider-specific-files-platform)
1. Settings.php modifications
    1. Open `/docroot/sites/www/settings.php` and find the "Include server-specific configuration." section.
    1. Uncomment the AWS portion.
    1. Modify appropriately.
    1. Remove uneeded server-specific configuration from other providers.
###### Provider-specific modifications: Pantheon
1. Remove unnecessary files and directories for Pantheon:
    - [List of universal files to remove](#universal-files)
    - [List of Acquia files and directories](#provider-specific-files-acquia)
    - [List of AWS files and directories](#provider-specific-files-aws)
    - [List of Platform.sh files and directories](#provider-specific-files-platform)
1. [Rename /docroot to /web](#renaming-docroot-to-web).
1. Create symlink from installed sites directory to sites/default/files
    1. cd to /sites/www (or other site directory if multisite).
    1. run `rm -rf files` to remove current files directory.
    1. run `ln -s ../default/files files` to create symlink to default files directory (which is then symlinked to /files on Pantheon's end). Make sure that a /default/files directory exists locally so files have a place to go.
    1. run `git add files` and commit symlink to repo.
1. Place post-deploy script in the correct place for pantheon to read it (inside the web directory).
    1. Create `/web/private/scripts` directory.
    1. Move `/scripts/pantheon/post_deploy.php` file into `/web/private/scripts`.
    1. Remove empty `/scripts/pantheon` folder.
1. Settings.php modifications
    1. Copy over the settings.pantheon.php file from your initial pantheon install into your `www` (or relevant sites directory).
    1. Open `/web/sites/www/settings.php` and find the "Include server-specific configuration." section.
    1. Uncomment the Pantheon portion.
    1. Remove uneeded server-specific configuration from other providers.
    
###### Provider-specific modifications: Platform.sh
1. Remove unnecessary files and directories for Platform.sh:
    - [List of universal files to remove](#universal-files)
    - [List of Acquia files and directories](#provider-specific-files-acquia)
    - [List of AWS files and directories](#provider-specific-files-aws)
    - [List of Pantheon files and directories](#provider-specific-files-pantheon)
1. Settings.php modifications
    1. Copy over the settings.platformsh.php file from your initial Platform.sh install into your `www` (or relevant sites directory).
    1. Open `/docroot/sites/www/settings.php` and find the "Include server-specific configuration." section.
    1. Uncomment the Platform.sh portion.
    1. Remove uneeded server-specific configuration from other providers.

@TODO Some files and configuration are missing for Platform.sh, which still needed to be included in KIT.

### Renaming docroot to web
Some providers require a different docroot directory.
1. Rename `docroot` directory to `web`.
1. Update `docroot` references to `web` in the following files:
    - `/.docksal/docksal.env`
    - `/.gitignore`
    - `/composer.json` (will need to run composer install afterward to regenerate autoload.php file)
    - `/drush/*` files (`/drush/sites/www.site.yml`, etc.)
    - `/patches/htaccess.patch`
    - `/source/gulpfile.js`
    - `/source/eslintrc.js`

## Notes and suggestions
#### Environment aliases
```
www.domainname.com
dev-www.domainname.com
stg-www.domainname.com
prod-www.domainname.com (for pre-go-live)
```
We've found that adding an environment prefix to the production url is a
low-effort/high-reward action. This allows for environment URLs that are
easier to remember and access, both for those working on the site and
for clients.

## Theme Development

When creating a new theme using the `fin kit/theme` command you'll have two directory structures created for you:

1. **docroot/themes/custom/yourtheme** - This is meant to only contain the files Drupal needs to render the site - e.g. css, javascript, images, template files. It does **not** contain any source files used to generate those files (e.g. sass files). It's important to note that some files under this directory are generated (see next point) and some (e.g. template files) should be edited directly.

1. **source/themes/custom/yourtheme** - Contains all of the source files used to generate the files in docroot/themes/custom/yourtheme.

Gulp is used to process the files in _source_ and writes the corresponding output to the matching theme directory under _docroot_. The default gulp file (source/gulpfile.js) is set up with a set of basic tasks needed by most themes. It is also set up to run those tasks in all of the directories under source/themes/custom. This allows the developer to have multiple themes (e.g. a base theme and child themes) under development and use a single command to build them all.

The following functionality is provided by the default gulp file:

- Sass - Processes all of the scss files under _styles_ in each theme directory and generates source maps. The output is places in _docroot/themes/custom/yourtheme/css_.
- Javascript - Copies and generates source maps for all js files under _scripts_ in each theme directory. The output is placed in _docroot/custom/yourtheme/js_. 
- Images - Minifies all images under _images_ and places the output in _docroot/custom/yourtheme/images_.
- Fonts - Copies all the files under _fonts_ to _docroot/custom/yourtheme/fonts_.
- Icons - Compiles all the svg files under _icons_ and writes the out put to the _docroot/custom/yourtheme/styles/vendor_ and _docroot/custom/yourtheme/fonts/icons_ directories.
- Testing - Linting of sass and javascript files as configured in _source/.sass-lint.yml_ and _source/.eslintrc_.

Tasks provided by the default gulp file are:

- default - Builds the Sass, Javascript, Images, Fonts, and Icons as described above.
- test - Runs the sass and javascript linters.
- watch - Builds everything included in the default task as file change in the _source_ directory structure.
