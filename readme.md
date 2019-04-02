# Drupal 8 KIT

VMLY&R's Drupal 8 KIT is a distribution which helps build and maintain
Drupal 8 projects. Via a suite of Docksal commands, an installation
profile, and a forthcoming base theme, the distribution provides support
for the following:
 * Common modules
 * Composer project scaffolding
 * Docksal command suite
 * Using Docksal for CI and build processes
 * Drupal site configuration
 * Multi-environment configuration and development
 * Multi-site configuration and development
 * Standardized theme management and structure

### Common modules (composer.json)

KIT uses composer for handling project dependencies. The distribution
includes a selection of Modules and libraries by default. The list was
determined using either the following criteria:
 - Best-practices – Drupal 8 Core is a much more viable product
 out-of-the-box than Drupal 7 was, but there are still certain modules
 and configuration that we need and should be using for every site,
 including support for multiple environments. Examples of these are
 Blazy for lazy-loading images, Advanced Aggregation for better
 style/script management, Configuration Sync + Config Split (instead of
 Features).
 - Standardization – being an open-source project, Drupal has a tendency
  to having multiple modules with similar solutions to similar problems.
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
 - `init-project` – Typically used when building or rebuilding the project
 and its dependencies. The command will start docksal, download
 dependencies, make sure that the project's relevant sites' directories
 and databases exits, build front-end artifacts, and import databases
 from another environment. The command takes an optional parameter
 `builder`, which is explained in detail below under the
 "Docksal + CI and build processes" section.
 - `init-deps` – Used when project-based command and tool dependencies
 need to be redownloaded and installed. Currently consists of Composer
 and NPM.

Additional commands are included via the `vmlyr-drupal/kit-docksal-commands`
package and installed under a "kit" sub directory in the docksal commands
directory. See the README.md file in that directory after composer is
installed for more information on those commands.

### Docksal + CI and build processes

Docksal and KIT's commands can be used for running build processes. The
command `init-project` can toggle "build mode" by running appending the
`builder` command: `fin init-project builder`. This is best used when
docksal is used to create the build files to be released. By default,
the builder runs composer in --no-dev mode, and auto-removes
build-related files amound other things.

### Drupal site configuration
This composer project comes with 2 VMLY&R-created Drupal profiles:
 - _Profilo_ – This profile has a lot of required site-configuration and
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
 - _Kastoro_ – This profile builds off of the _Profilo_ profile, but is
 a little more opinionated. It includes helpful Paragraph components,
 Image Styles, Media implementations, among a slew of additional
 configuration. To get the full use of the additional components, make
 sure to run `fin/init-theme` and generate a new theme off of the "Denim"
 theme option.

### Multi-environment configuration and development

Multi-environment configuration is pre-configured as part of the
distribution via the default `settings.php` file and the _Profilo_ and _Kastoro_ profiles.

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

#### Environments + the VMLY&R Profiles (_Profilo_ and _Kastoro_)
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
generated via the `fin kit/init-theme` command.

## Installation

Getting a running site takes only a few steps for a project.

1. [Install Docksal](https://docksal.io/installation) if it's not already installed.
1. Install the project.
    1. Use composer to create the new project. *Note: try not to use hyphenated project names if possible, docksal currently has weird issues with projects with hyphens.*
       ```
       fin run-cli "composer create-project vmlyr-drupal/kit [FOLDER_NAME_HERE] 8.*"
       ```
    1. Change into the directory.
        ```
        cd [FOLDER_NAME_HERE]
        ```
    1. If this directory was not already a git project, initialize the
    new repository and add the remote origin
        ```
        git init
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
1. Run `fin init-project`
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
1. To start building your own theme, run `fin kit/init-theme` to
generate a new theme + theme source setup based on our example scaffold
themes. If the _Kastoro_ profile was installed, we suggest scaffolding
from _Denim_.

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
