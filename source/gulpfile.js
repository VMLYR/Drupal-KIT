/*
 * This file is set up to run standard build and testing tasks over
 * ALL themes in the theme/custom directory.
 *
 * Provided in this file are the default path and task configurations.
 *
 * *** 😃 No changes are needed on startup. 😃 ***
 *
 * Preset tasks:
 * gulp = runs all build tasks
 * gulp test = runs all testing tasks
 * gulp watch = starts the watchers (builds & tests)
 *
 * 1. Configs
 * 2. Build Tasks
 * 3. Testing Tasks
 * 4. Watchers
 * 5. Exports
 */

const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const mode = require('gulp-mode')();
const changed = require('gulp-changed');
const uglify = require('gulp-uglify');
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

let files = fs.readdirSync('themes/custom/');
let config = [];

for (const value of files) {
  if (value.startsWith(".")) {
    continue;
  }
  config.push({themeName: value});
}

/**************************************
 * 2. Build Tasks                     *
 **************************************/

function buildSass() {
  const tasks = config.map(function(entry) {
    return src(['themes/custom/' + entry.themeName + '/styles/**/*.scss', '!themes/custom/' + entry.themeName + '/styles/**/example.*.scss'])
        .pipe(mode.development(sourcemaps.init()))
        .pipe(mode.production(sass({
          outputStyle: 'compressed',
          includePaths: []
        })))
        .pipe(mode.development(sass({
          noCache: true,
          outputStyle: 'compressed',
          lineNumbers: false,
          includePaths: [],
          sourceMap: true
        })))
        .pipe(mode.development(sourcemaps.write('./maps')))
        .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/css'));
  });

  return merge(tasks);
}

function buildJavascript() {
  const tasks = config.map(function(entry) {
    return src(['themes/custom/' + entry.themeName + '/scripts/**/*.js', '!themes/custom/themes/custom/' + entry.themeName + '/scripts/**/example.*.js'])
      .pipe(mode.development(changed('../docroot/themes/custom/' + entry.themeName + '/js')))
      .pipe(mode.development(sourcemaps.init()))
      .pipe(mode.production(uglify()))
      .pipe(mode.development(sourcemaps.write('./maps')))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/js'));
  });

  return merge(tasks);
}

function buildImages() {
  const tasks = config.map(function(entry) {
    return src('themes/custom/' + entry.themeName + '/images/**/*')
      .pipe(mode.development(changed(entry.destDir + '/img')))
      .pipe(imagemin({progressive: true}))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/images'));
  });

  return merge(tasks);
}

function buildFonts() {
  const tasks = config.map(function(entry) {
    return src('themes/custom/' + entry.themeName + '/fonts/**/*')
      .pipe(mode.development(changed('../docroot/themes/custom/' + entry.themeName + '/fonts')))
      .pipe(changed('../docroot/themes/custom/' + entry.themeName + '/fonts'))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/fonts'));
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
        fontPath: 'themes/custom/' + entry.themeName + '/fonts/icons'
      }))
      .pipe(iconfont({
        fontName: 'themeIcons',
        prependUnicode: false,
        formats: ['ttf', 'eot', 'woff'],
        timestamp: runTimestamp,
        normalize: true,
        fontHeight: 1001
      }))
      .pipe(dest('../docroot/themes/custom/' + entry.themeName + '/fonts/icons'));
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
