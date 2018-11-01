# Drupal 8 KIT

VML's Drupal 8 KIT is a distribution which helps build and maintain
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
(like default.settings.php, htaccess, robots.txt, etc.). VML
additionally creates the standard config folder structure, as well as
removes and updates files that should be modified via patches
(like .htaccess) or compiled (like drupal.settings.additional.php
compiling into drupal.settings.php) instead of being checked-into the
repository directly.

### Docksal command suite
The project uses [Docksal](https://docksal.io/) for local-environment
development and project-building. The following is a list of
KIT-specific Docksal commands:
 - `check-url` – Runs through the collection of URLs listed in the
 `.docksal/configuration.urlcheck.yml` file and checks whether any of
 them return a 200.
 - `check-watchdog` – Counts the current number of errors currently
 listed in a sites watchdog log. This is best used in-tandem with
 check-urls on CI pull-request builds to check whether any of the pages
 are throwing warnings or errors. The builder can then set a threshold
 on allowed number of warnings or errors.
 - `composer` – Run `composer` inside the docksal container with no need
 to install composer locally. First removes all relevant dependency
 folders and files to help resolve any folder/file permission issues
 that can arise due to Drupal's permissions-hardening
 - `conf` – A wrapper command used in lieu of `drush cex`/`cim` which
 helps handle environment-specific configuration export and import.
 **Please Note: To correctly export _as_ an environment, the environment
 first needs to be imported. The changes can then be made and exported.
 This makes sure that there is no environment cross-polution. Typically,
 importing and exporting as "local" is the most fool-proof way of
 handling configuration unless an environment-specific change needs to
 be made.**
 - `ddrush` – Runs any Drush command inside the project docroot.
 - `eslint` – Run eslint against specified files and/or directories.
 - `init-project` – Typically used when building or rebuilding the project
 and its dependencies. The command will start docksal, download
 dependencies, make sure that the project's relevant sites' directories
 and databases exits, build front-end artifacts, and import databases
 from another environment.
 - `init-deps` – Used when project-based command and tool dependencies
 need to be redownloaded and installed. Currently consists of Composer
 and NPM.
 - `init-db` – To initialize or reinitialize one or multiple site's
  databases based on their drush alias files.
 - `lint` – Run lint against specified files and/or directories.
 - `phpcbf` – Run Code Beautifier & Fixer (`phpcbf`) against a given
 path.
 - `phpcs` – Run Code Sniffer (`phpcs`) against a given path.
 - `swig` – A wrapper for running front-end development tools, like
 `gulp-watch` and `gulp-build`.
 - `sync` – Sync database (and optionally files) to a local environment
 from an external environment. After the database is imported, the site
 runs various updates and imports configuration as a specified
 (default: local) environment.


### Docksal + CI and build processes

Docksal and KIT's commands can be used for running build processes. The
command `init-project` can toggle "build mode" by running appending the
`builder` command: `fin init-project builder`.

### Drupal site configuration
KIT comes with a VML profile, which has a lot of required
site-configuration and relevant modules installed and setup by default.
It's fairly bare-bones besides items related to best-practices and
standardization, but it does take care of a lot of monotony that comes
with installing a new Drupal instance. Some examples are:
 - Default environments (local, remote_dev, remote_stage, remote_prod)
 and their relevant config-splits and settings and modules.
 - Core files modified/removed by default, like .htaccess _https_
 overrides, default settings.php changes, using RobotsTXT module instead
 of the included core file, etc.
 - Lighthouse-related settings, like Image Optimization, Responsive
 Images + Focal Point, Advanced Aggregation, Blazy, etc.
 - Various settings and needed for go-live on new sites, like setting up
 Advanced Aggregation for production instances, default metatags for
 global and Node pages, XML Sitemap defaults, disabling anonymous user
 registration, etc.

### Multi-environment configuration and development

Multi-environment configuration is pre-configured as part of the
distribution via the default `settings.php` file and the VML profile.

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
cache or allowing verbose error reporting. The fin `init-project` / `init-sites`
commands automatically copy a `settings.local.php` and
`settings.docksal.php` into each sites directories to better assist
local development.

#### Environments + VML Profile
The VML profile includes config-split options by default, and has
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
the new drush alias file and running `init-sites`. The command will
build out the site folder structure, copy the default settings.php file
into the site directory and is then ready for installation. The `sync`
and `conf` tools both support syncing and exporting/importing any site
in a multi-site Drupal instance.

### Standardized theme management and structure (WIP)
KIT will eventually include a Drupal base theme (named Bazo), which will
feature a standardized library and theme architecture that more-easily
enables the interaction between frontend and backend development and
saves time on standardizing markup and assets of reusable components.

## Installation

Getting a running site takes only a few steps for a project.

1. [Install Docksal](http://docksal.readthedocs.io/en/master/getting-started/env-setup/) if it's not already installed.
1. Duplicate the contents of the KIT repository into a new repository
    1. Copy the repository into a new folder.
        ```
        git clone --depth=1 --branch=master ssh://git@bitbucket-ssh.uhub.biz:7999/vmlnadrupal/drupal-8-kit.git [FOLDER_NAME_HERE]
        ```
    1. Change into the directory.
        ```
        cd [FOLDER_NAME_HERE]
        ```
    1. Remove KIT's .git file and initialize a new repository.
        ```
        rm -rf .git && git init
        ```
    1. Add your new projects remote repository
        ```
        git remote add origin [REMOTE_REPOSITORY_URL_HERE]
        ```
1. Run `fin start` to create the Docksal project.
1. Open each site's Drush alias file (`/drush/sites/` to update the
local URI as well as any relevant server information if it's already
known. _(note: Your local should have been listed by Docksal at the end
of running `fin start`)_
1. Open `/sites/sites.php` folder to make sure domains are mapped to
the correct site.

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
1. Fill out the site configuration. If using the VML Profile and the
environment URLs are unknown, make a best guess at what they could be
_(we suggest following the `//ENV-WWW.SITE_PROD.DOMAIN` structure)_.
The domains are used for indicating current environment and using other
environment's assets via _Stage File Proxy_. Setting these up now helps
not needing to set these in multiple places later on during development.
1. If you selected the VML profile, upon saving configuration the site
should export all relevant configuration into the site's sync directory
and import as the local environment.
1. Installation is complete once redirected to the homepage of the site.

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
