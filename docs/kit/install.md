# Install

## Steps

1. Ensure lando is installed [read how](lando.md).

1. Clone GitHub repository (Maybe don't, download instead?)

    ```git clone <repo> <directory>```

    Delete it?

2. Create .lando.yml

    ```echo "name: YOURAPPNAME" > .lando.yml```

3. Lando Start

    ```lando start```

4. Composer Install

    This SHOULD be done as part of lando start

5. Drupal Site Install

    1. CLI
    1. Browser

        1. Visit ***YOURAPPNAME***.lando.local in your favorite browser.

        1. Select English

              **TODO** Make US English the default

        1. Select the Enterprise profile.

        1. Enter the following database credentials (Should be set by default):

            **TODO** Add credentials by default if using lando.

            ```
            username: user
            password: user
            database: www
            host: database
            ```

        1. Enter site information

        1. **NOT DONE**: Select options

            1. Create theme
            1. Setup GA
            1. Generate pages

1. Create Theme

1. Create README

    **TODO**: Create a command to create documentation

1. Git init

    ```git init```

    ```git add .```

    ```git commit -m "Initial commit```

    ```git remote add origin <>```

1. Setup Drush for host
