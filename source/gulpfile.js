/*
 * V 2.1
 *
 * This file is set up to run standard build and testing tasks over
 * ALL themes in the theme/custom directory.
 *
 * 😃 No changes are needed on startup. 😃
 *
 * !!! Files are NOT minified !!!
 * Minification should be handled through Advanced Aggregation Drupal module
 *
 * === Piping ===
 * Provided in this file are the default path and task configurations.
 * Files follows the same path as where it started from.
 * scss to css - js to js - images to images - icons to icons - fonts to fonts
 *
 * `source/themes/custom/project/js`
 *  is piped to
 * `docroot/themes/custom/project/js`
 *
 * === Example files ===
 * An `example.` prefix can be added to scss and js files.
 * The prefixed files will not be linted or piped.
 * ex: `styles/libraries/node/example.full.scss`
 *
 * === Preset tasks ===
 * gulp = runs all build tasks
 * gulp test = runs all testing tasks
 * gulp watch = starts the watchers (builds & tests)
 *
 * === Gulp file structure ===
 * 1. Configs
 * 2. Build Tasks
 * 3. Testing Tasks
 * 4. Watchers
 * 5. Exports
 */

const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const changed = require('gulp-changed');
const imagemin = require('gulp-imagemin');
const iconfont = require('gulp-iconfont');
const iconfontCss = require('gulp-iconfont-css');
const sassLint = require('gulp-sass-lint');
const eslint = require('gulp-eslint');
const fs = require('fs');
const merge  = require('merge2');
const { src, dest, watch, parallel, series } = require('gulp');

/**************************************
 * 1. Configs                         *
 **************************************/

// === Theme name === //
// Gets an array of all the theme names
let files = fs.readdirSync('themes/custom/');
let config = [];

// Removes hidden files and update the key value
for (const value of files) {
  if (value.startsWith(".")) {
    continue;
  }
  config.push({themeName: value});
}

// === Custom === //
// overrides the dest path after the theme name
// ../docroot/themes/custom/themeName/customDest/*
const customDest = '';

/**************************************
 * 2. Build Tasks                     *
 **************************************/

function buildSass() {
  const tasks = config.map(function(entry) {
    return src(['themes/custom/' + entry.themeName + '/styles/**/*.scss', '!themes/custom/' + entry.themeName + '/styles/**/example.*.scss'])
        .pipe(sourcemaps.init())
        .pipe(sass({
          noCache: true,
          outputStyle: 'compact',
          lineNumbers: false,
          includePaths: [],
          sourceMap: true
        }))
        .pipe(sourcemaps.write('./maps'))
        .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/' + customDest + '/css'));
  });

  return merge(tasks);
}

function buildJavascript() {
  const tasks = config.map(function(entry) {
    return src(['themes/custom/' + entry.themeName + '/scripts/**/*.js', '!themes/custom/themes/custom/' + entry.themeName + '/scripts/**/example.*.js'])
      .pipe(sourcemaps.init())
      .pipe(changed('../docroot/themes/custom/' + entry.themeName + '/js'))
      .pipe(sourcemaps.write('./maps'))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/' + customDest + '/js'));
  });

  return merge(tasks);
}

function buildImages() {
  const tasks = config.map(function(entry) {
    return src('themes/custom/' + entry.themeName + '/images/**/*')
      .pipe(changed('../docroot/themes/custom/' + entry.themeName + '/' + customDest + '/images'))
      .pipe(imagemin({progressive: true}))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/' + customDest + '/images'));
  });

  return merge(tasks);
}

function buildFonts() {
  const tasks = config.map(function(entry) {
    return src('themes/custom/' + entry.themeName + '/fonts/**/*')
      .pipe(changed('../docroot/themes/custom/' + entry.themeName + '/fonts'))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/' + customDest + '/fonts'));
  });

  return merge(tasks);
}

function buildIcons() {
  const runTimestamp = Math.round(Date.now()/1000);

  const tasks = config.map(function(entry) {
    return src('themes/custom/' + entry.themeName + '/icons/**/*.svg')
      .pipe(iconfontCss({
        fontName: 'themeIcons',
        path: 'themes/custom/' + entry.themeName + '/styles/vendor/_icons-template.scss',
        targetPath: '../../../../../../source/themes/custom/' + entry.themeName + '/styles/vendor/_icons.scss',
        fontPath: 'themes/custom/' + entry.themeName + '/' + customDest + '/fonts/icons'
      }))
      .pipe(iconfont({
        fontName: 'themeIcons',
        prependUnicode: false,
        formats: ['ttf', 'eot', 'woff'],
        timestamp: runTimestamp,
        normalize: true,
        fontHeight: 1001
      }))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/' + customDest + '/fonts/icons'));
  });

  return merge(tasks);
}

/**************************************
 * 3. Testing Tasks                   *
 **************************************/

function testSassLint() {
  const tasks = config.map(function(entry) {
    return src(['themes/custom/' + entry.themeName + '/styles/**/*.scss', '!themes/custom/' + entry.themeName + '/styles/**/example.*.scss'])
      .pipe(sassLint({ sasslintConfig: '.sass-lint.yml' }))
      .pipe(sassLint.format())
      .pipe(sassLint.failOnError());
  });

  return merge(tasks);
}

function testJsLint() {
  const tasks = config.map(function(entry) {
    return src(['themes/custom/' + entry.themeName + '/scripts/**/*.js', '!themes/custom/' + entry.themeName + '/scripts/**/example.*.js'])
      .pipe(eslint())
      .pipe(eslint.format())
      .pipe(eslint.failAfterError());
  });

  return merge(tasks);
}

/**************************************
 * 4. Watchers                        *
 **************************************/

function watchSass() {
  config.map(function(entry) {
    watch(['themes/custom/' + entry.themeName + '/styles/**/*.scss', '!themes/custom/' + entry.themeName + '/styles/**/example.*.scss'], series(buildSass, testSassLint));
  });
}

function watchJavascript() {
  config.map(function(entry) {
    watch(['themes/custom/' + entry.themeName + '/scripts/**/*.js', '!themes/custom/' + entry.themeName + '/scripts/**/example.*.js'], series(buildJavascript, testJsLint));
  });
}

function watchImages() {
  config.map(function(entry) {
    watch('themes/custom/' + entry.themeName + '/images/**/*', buildImages);
  });
}

function watchFonts() {
  config.map(function(entry) {
    watch('themes/custom/' + entry.themeName + '/fonts/**/*', buildFonts);
  });
}

function watchIcons() {
  config.map(function(entry) {
    watch('themes/custom/' + entry.themeName + '/icons/**/*.svg', buildIcons);
  });
}

/**************************************
 * 5. Exports                         *
 **************************************/

exports.default = series(buildFonts, buildIcons, buildImages, buildJavascript, buildSass);
exports.test = series(testJsLint, testSassLint);
exports.watch = parallel(watchFonts, watchImages, watchIcons, watchJavascript, watchSass);
