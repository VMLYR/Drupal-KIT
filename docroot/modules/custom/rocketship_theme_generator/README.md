# Rocketship Theme Generator

This module contains a PHP-script to generate component based themes for use with [the Rocketship distribution](https://www.drupal.org/project/dropsolid_rocketship_profile) and its various components), based on certain presets.

The generated themes make use of Gulp as a workflow tool and Sass for preprocessing the CSS, as well as Storybook for styleguide generation. All info about how to use it, is present in the README file of your generated themes and the built Storybook styleguide.

This module replaces the 4 Rocketship themes previously maintained here, which should be considered legacy:
- https://www.drupal.org/project/rocketship_theme_demo
- https://www.drupal.org/project/rocketship_theme_flex
- https://www.drupal.org/project/rocketship_theme_starter
- https://www.drupal.org/project/rocketship_theme_minimal

Some things the resulting themes do:

- Templates, CSS and JS are component-based
- Provides font loading options
- Uses Sass (with globbing) to make CSS
- Can provide Sourcemaps for CSS and JS
- Can generate favicons
- Can generate critical CSS files
- Can generates a custom icon font or icon sprite
- Has better responsive tables
- Can override colors for the Rocketship Paragraphs (or Content Blocks, depending on the version)  background-color palette
- Example styling for content types (eg. blog)
  - !important! The Libraries are NOT loaded by default.

## Requirements

### for using the module
- php 7.3+

### for using the generated themes
- A Drupal Rocketship distribution using Rocketship Core 5.x (for themes 2.x), 6.x (for themes 3.x) and up
- Node: 14.x
- NPM: 6.x
- a recent version of gulp-cli
- dependent Drupal modules:
  - responsive_image
  - components
  - unified_twig_ext
  - search

See theme's Readme file for more info on versions and updates

## Usage

Generating your theme can be done by running the php script in this module, with options:

`php scripts/generate-theme.php`

Use these options:
- **name**: The name of your theme. Don't use dashes or special characters.
- **machine-name**: the machine name
- **theme-path**: location of the generated theme relative to where the script is located (the `dist` folder inside the module is default)
- **preset**:
  - minimal: a setup with only some basic and structural CSS
  - starter: some more styling, including for the Rocketship elements as well
  - flex: more styling for the Rocketship elements, following the Rocketship Flex guidelines & presets
  - demo: styling and presets for a Demo site
- **author**: person developing the theme
- **description**: a short description that goes in the info, composer & README files

## Presets

The presets follow a cascading principle:
- **minimal**: inherits all its contents from the `templates/themes/BASE` folder
- **starter**: does the same + overwrites files with what is in the `STARTER` folder
- **flex**: does the same thing as `starter` + overwrites files with what is located in the `FLEX` folder
- **demo**: Inherits from all the previous + adds custom stuff for the demo site

So you can see that the themes that will be generated using these presets, will go from very little files & configuration to a very full set of files and configuration.

## Generate

You should pass your options in this format (don't forget the single quotes if using spaces):
`php scripts/generate-theme.php --name='My theme' --machine-name=my_theme`

Examples per theme preset:

```
php scripts/generate-theme.php --name='Rocketship Minimal' --machine-name='rocketship_theme_minimal' --preset=minimal --author='rembrandx' --description='Component-based Drupal theme for use with Dropsolid Rocketship install profile, modules and other components.' --theme-path='dist'
```

```
php scripts/generate-theme.php --name='Rocketship Starter' --machine-name='rocketship_theme_starter' --preset=starter --author='rembrandx' --description='Component-based Drupal theme for use with Dropsolid Rocketship install profile, modules and other components.' --theme-path='dist'
```

```
php scripts/generate-theme.php --name='Rocketship Flex' --machine-name='rocketship_theme_flex' --preset=flex --author='rembrandx' --description='Component-based Drupal theme for use with Dropsolid Rocketship install profile, modules and other components.' --theme-path='dist'
```

```
php scripts/generate-theme.php --name='Rocketship Demo' --machine-name='rocketship_theme_demo' --preset=demo --author='rembrandx' --description='Component-based Drupal theme for use with Dropsolid Rocketship install profile, modules and other components.' --theme-path='dist'
```

### Demo theme
A demo theme with extensive styling for all current Rocketship components, such as Rocketship's Layouts and Content Blocks. To be used as a demo of what Dropsolid Rocketship can do.
If you want the build a site, you should use one of the other theme presets.

### Flex theme
A theme for building Rocketship websites.<br />
Comes with some pre-defined styling and Sass/Twig files to get you started.<br />
The styling follows the standards and structures for Dropsolid's 'Flex' projects, in which for Rocketship components play a big part (eg. Rocketship Layouts, Content Blocks, … some styling related to Content Types).

Contains more styling that the Rocketship Minimal theme but less than the Rocketship Demo theme: Styled RS Content Blocks

### Starter
Comes with some basic styling and Sass/Twig files to get you started.<br />
Contains a bit more pre-defined styles that the Rocketship Minimal theme but less than the Rocketship Flex theme or Rocketship Demo theme: Structural CSS for Content Blocks and Content Types.

### Minimal
A theme for building Rocketship websites.
Comes with some very basic styling and Sass/Twig files to get you started.
The most bare-bones of the Rocketship themes: structural CSS for the RS Content Blocks

## Generate theme extentions

Some Rocketship modules add new functionality (eg. new Content Types or Block Types). For ease of reference, let's call those 'Features'.<br />
If those new Features need extensive styling, there's a good chance the module already contains a bunch of theming assets (eg. Sass files or JS) with the purpose of being copied over to the themes themselves (because that's where theming happens).<br />
This happens automagically when the module is installed, if it has a hook_install() function connecting it to the theme generator.

This install function runs during module install, which calls the ThemeExtentionGenerator class of the theme generator, which copies the appropriate files over to ALL the themes.<br />
Why all the themes? Because if the module is activated during a site installation, there might not be an active theme yet. So we target all of them to include the styling that is needed for the Feature to look decent.<br />
By default, the styling will be either really bare-bones, or based on the Demo theme, because that looks the best. Of course, when you start theming your project, you'll have to change things to make them match whatever design you are working from.

If you make and add a new theme after the module was installed, you'll have to copy those files from the Feature's module manually. They should be located in `my_module/assets/theming` and copied or merged with your new theme.<br />

**Note: not all Rocketship modules that add new functionality have these theming assets, in which case, you need to override whatever theming and libraries yourself, to get the styling you want.**

**Note 2: as it stands, extending theming in this way is NOT compatible with child themes. This is because of the way globbing the Sass-files works, where you need to know the name of your parent theme so this is not generic the way we need it.**

**Note 3: your themes need to be located in `/themes/custom`, on level 0, for the generator to put the files in the correct place**

### Developing a theme extention

There are 2 ways to do this:
- make an entirely separate component
- extend or add components to the `00-theme` or `01-content-blocks` folders of the theme

**1) Separate component**

_recommended for new functionality_

First, you need to make the Rocketship Theme Generator a dependency and tell it is okay to handle the theming files for your module. <br />
This is done by adding a dependency to the info yml, and by triggering a custom hook from your modul's install file:

Inside `my_module.info.yml`

```
dependencies:
  - rocketship_theme_generator
```

Inside `my_module.module`

```
function my_module_install() {

  // need to pass the module name so we know where stuff needs to get copied from
  $args = array(
    'module_name' => 'my_module'
  );

  // Find the hook that copies the theming from this module into the themes
  // (should only be 1 such hook, and it is in rocketship_theme_generator)
  \Drupal::moduleHandler()->invokeAll('generate_theme_extention', $args);

}
```

Then you would put your theming into a self-contained folder, nested in your theme's `components/02-features`, while developing.<br />
because any folders inside 'features' will be compiled by Gulp's CSS tasks without additional development needed in the theme.<br />
When finished, you copy all your component files & folders to your module's `assets/theming` folder.</br>
So for example: `your_theme/components/02-features/my-component` ends up in `assets/theming/components/02-features/my-component`.

Don't forget to include all the library files (generated CSS and JS) into your module's `assets/theming/css`and `assets/theming/js/dest` folders.
Your module also needs to have a snippet file called `assets/theming/libraries.yml` containing the library definition. Otherwise, there is no way to actually load your styling out-of-the-box.

Example snippet in `libraries.yml`
```
feature_my_component:
  css:
    theme:
      css/style.my-component.css: {}
      css/style.my-component.print.css: {}
  js:
    js/dest/my-component.js: {}
  dependencies:
    - core/jquery
    - core/drupal
    - core/once
```

Now you need to make sure your library gets activated when the module is installed, by using your module's `.module` file, eg. by adding a `HOOK_preprocess_HOOK` function.

```
function my_module_preprocess_page(&$variables) {
  // find active theme name
  $active_theme = \Drupal::service('theme.manager')->getActiveTheme()->getName();
  // attach component library
  $variables['#attached']['library'][] = $active_theme . '/feature_my_new_component';
```

**In short:<br />**
You set up your module's install function to trigger a custom hook.<br />
Once your module's install function has run, the component and the library files end up copied to the theme<br />
and your theme library yml will be extended with the library definition.<br/>
The new library gets loaded by the module itself, pointing to the active theme. So even if no further front-end development is happening, your new component styling is loaded and the component looks good & works in the front-end.

Uninstalling the module will NOT remove the component assets from your theme. This is because themes are custom parts of a Drupal project and subject to further changes and development. Having a module remove parts of it could break the theme.<br />
However, uninstalling the module WILL prevent the library from being active on your project, so there won't be any extra load to the pages.

**2) Adding to 00-theme or 01-content-blocks**

_recommended ONLY if still developing the front-end of the project_

Same setup as before, and you also add add your files to your module's `assets/theming` folder.<br />
Eg. if you want to add a new molecule to content-blocks, you place files in:<br />
`assets/theming/components/00-theme/01-atoms/my-new-atom`<br />
You can't extend or modify existing files (because that could break existing theming).

However, it is no use adding a new library snippet nor library files (CSS and JS files) for your extra styling, because `00-theme` and content blocks already have single libraries and CSS files that are attached globally. Any new files you add to those folders will already automagically be picked up by Gulp and compiled into those global libraries during theming of the site.

While you COULD add a new, separate library, it is useless to have compiled CSS and JS be loaded when you turn on the module. Once you would start making theming changes, your new styling would get loaded twice: updated theming in the normal theming CSS and the old theming that never gets recompiled, via a separate library.

So this way this type of extending a theme is only useful if you know front-end development (and thus CSS Gulp tasks) will happen AFTER your module has been turned on and the theming doesn't have to be visible out-of-the-box. It's useless for demo-projects without development.

### Customizing component theming

Once your theme extention lives in the theme you are working on, you can simply update the CSS and JS by using the normal Gulp tasks, such as:

- `gulp css:features:prod`: if you only want to update the Feature css
- etc…
