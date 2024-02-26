# Lando

We use Lando for local development. Lando acts as a manager or docker and docker-compose.

## Installing Lando

-- Howto install on MacOS

## Lando Files

Drupal KIT uses the following lando files on nearly every site.

1. [lando.base.yml](###.lando.base.yml)
2. [lando.yml](###.lando.yml)
3. [lando.local.yml](#.lando.local.yml)

### .lando.base.yml

This file contains the default settings and tooling that is distributed with Drupal KIT. Keeping Drupal KIT settings in this file allows Drupal KIT to be updated without worrying about breaking project specific settings.

**WARNING:** This file should not be modified outside of Drupal KIT updates.

### .lando.yml

This file contains the project specific settings for your Drupal setup.

**You must have a .lando.yml file with a name property in every project!**

#### Properties

- **name**: The **name** property must be present in this file to differentiate your project from other Lando projects running within your host. The **name** property must be unique and is never in lando.base.yml.

- **proxy**: The **proxy** propert can be used to expose specific ports or services specific to your project.

Example lando.yml:

```
name: MyDrupalSite
proxy:
  node:
    - newkitsb.lndo.site:6006
```

### .lando.local.yml

The .lando.local.yml file is used for Lando configure specific to an individual developer's machine or setup. This file is included in .gitignore and should never be included as part of a project that is distributed beyond a local computer.

This file allows developers to make changes such as changing database credentials, adding additional local services, or developer specific tooling.

## Services

### appserver

An Ubuntu, Apache, MySQL, PHP service that is where your Drupal applications executes. By default it exposes Apache at <name>.lando.local, with /docroot as the webroot for Apache.

Within the appserver service, the root of this project is located at /app, so that docroot would be located at /app/docroot.

### PHP Configuration

PHP can be configured by editing the .lando.php.ini file in the root directory.

### database

A MySQL database server located within the Docker network as **database**.

By default the credentials are as follows:

```
username: user
password: user
database: www
host: database
```

## Tooling

### drop-tables

Runs the script at /app/scripts/mysql/drop-tables.sql against the www database.

This will drop all tables within the database, useful for re-installling the application from scratch.

### grumphp

Runs /app/vendor/bin/grumphp run frm within the appserver.

Used to run linting against files using GrumPHP

### yarn

Runs yarn commands against appserver

### xdebug-on

Turns on Apache XDebug within the appserver service.

XDebug should not be left on all the time, and only makes sense to turn on when doing specific debugging.

See the [xdebug](xdebug.md) documentation for more information.

### xdebug-off

XDebug should not be left on all the time, and only makes sense to turn on when doing specific debugging.

See the [xdebug](xdebug.md) documentation for more information.

## Other

### VSCode

A /.vscode/ directory exists that contains the launch.json file for VSCode.

This file is already setup to work with xdebug and Lando. Running ``lando xdebug-on`` and turning on debugging within VSCode should start your debugging process.