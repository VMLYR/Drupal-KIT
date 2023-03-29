// Compile Our Sass
'use strict';
const gulp = require('gulp'),
  dartSass = require('sass'),
  gulpSass = require('gulp-sass'),
  rename = require('gulp-rename'),
  replace = require('gulp-replace'),
  flatten = require('gulp-flatten'),
  sourcemaps = require('gulp-sourcemaps'),
  // concat = require('gulp-concat'),
  autoprefixer = require('gulp-autoprefixer'),
  sassGlob = require('gulp-sass-glob'),
  cleanCSS = require('gulp-clean-css'),
  gulpif = require('gulp-if'),
  notify = require('gulp-notify'),
  bs = require('browser-sync').get('bs');

const sass = gulpSass(dartSass);
const { config } = require('../config');
const { errorNotification } = require('./00-setup');

let timeStamp = new Date().getTime();

/**
 *
 * functions for compiling css per part
 */
 function buildCSSProd(mySrc, myDest) {

  return gulp.src( mySrc )
  // Use globbing so we don't need to manually add the imports
  .pipe(sassGlob())

  .pipe(sass.sync({
    outputStyle: 'expanded',
  }))

  .pipe(autoprefixer({
    browsers: ['> 1%', 'last 3 versions', 'IE > 10'],
    cascade: false,
    grid: false
  }))

  /**
   * We won't be needing this because AdvAgg does it for us
   * + it messes up git merges
   */
  // .pipe(
  //   cleanCSS({compatibility: '*'}) //'*' = default, includes IE10+
  // )

  // replace relative paths for files
  .pipe(flatten())

  // Cache busting the font files
  .pipe(gulpif(config.arg.fontCache === true,
      replace('?cache_busting_variable', ''),
      replace('cache_busting_variable', timeStamp)
  ))

  .pipe(gulp.dest(myDest));

}

function buildCSSDev(mySrc, myDest) {

  return gulp.src( mySrc )

  // Use globbing so we don't need to manually add the imports
    .pipe(sassGlob().on('error', function (err) {
      // Message errors to Mac OS X, Linux or Windows
      notify().write(err.formatted);
      this.emit('end');
    }))

    // add sourcemaps
    .pipe(sourcemaps.init({
      // largeFile: true,
    }))

    // An identity sourcemap will be generated at this step
    .pipe(sourcemaps.identityMap())

    .pipe(sass.sync({
      outputStyle: 'expanded', // don't minify here because errors with maps
    }).on('error', function (err) {
      errorNotification(this, err);
    }))

    // prefixes IE10 and higher
    .pipe(autoprefixer({
      browsers: ['> 1%', 'last 3 versions', 'IE > 10'],
      cascade: false,
      grid: false
    }).on('error', function (err) {
      errorNotification(this, err);
    }))

    /**
     * We won't be needing this for development
     * + it messes up git merges
     */
    // .pipe(
    //   cleanCSS({compatibility: '*'}) //'*' = default, includes IE10+
    // )

    .pipe(
      sourcemaps.write('./' + myDest, {
        // includeContent: false,
        // sourceRoot: '.',
        sourceMappingURL: function (file) {
          // // We add a timestamp for cachebusting (edit: no need for development, Drupal is smart enough)
          // return file.relative + '.map?build=' + new Date().getTime();
          return file.relative + '.map';
        }
      }).on('error', function (err) {
        errorNotification(this, err);
      })
    )

    // replace relative paths for files
    .pipe(flatten())

    // Cache busting the font files
    .pipe(gulpif(config.arg.fontCache === true,
        replace('?cache_busting_variable', ''),
        replace('cache_busting_variable', timeStamp)
    ))

    .pipe(gulp.dest(myDest))

    // fix for use with browsersync url
    .pipe(
      gulpif(
        config.arg.url !== false,
        bs.stream({match: '**/*.css'})
      )
    );
}

/**
 * Separate tasks so we can run them async
 */

// -- for development

const buildCSSThemeDev = function() {
  return buildCSSDev(config.css.components.theme.src, config.css.dest);
};

const buildCSSContentBlocksDev = function() {
  return buildCSSDev(config.css.components.contentblocks.src, config.css.dest);
};

const buildCSSFeaturesDev = function() {
  return buildCSSDev(config.css.components.features.src, config.css.dest);
};

gulp.task('css:theme:dev', async function () {
  return buildCSSThemeDev();
});

gulp.task('css:contentblocks:dev', async function () {
  return buildCSSContentBlocksDev();
});

gulp.task('css:features:dev', async function () {
  return buildCSSFeaturesDev();
});

gulp.task('css:message:dev', function (done) {
  notify().write("Don't commit development CSS. Use 'gulp css:prod' instead.");
  notify().write("Sourcemap files won't work on environments and lead to errors & overhead");
  done();
});


// -- for production

gulp.task('css:theme:prod', async function () {
  return buildCSSProd(config.css.components.theme.src, config.css.dest);
});

gulp.task('css:contentblocks:prod', async function () {
  return buildCSSProd(config.css.components.contentblocks.src, config.css.dest);
});

gulp.task('css:features:prod', async function () {
  return buildCSSProd(config.css.components.features.src, config.css.dest);
});

/**
 *  Copy & rename the css-files needed for mail and editor
 *  the 'watch' task should also listen to changes in that src,
 *  in order to properly update properly
 */

const cssMail = function() {
  return gulp.src(config.css.mail.src)
    .pipe(rename('mail.css'))
    .pipe(cleanCSS({compatibility: '*'})) //'*' = default, includes IE10+
    .pipe(gulp.dest(config.paths.base));
};

const cssEditor = function() {
  return gulp.src(config.css.editor.src)
    .pipe(rename('editor.css'))
    .pipe(gulp.dest(config.css.dest));
};

gulp.task('css:mail', function () {
  return cssMail();
});

gulp.task('css:editor', function () {
  return cssEditor();
});

/**
 * Minify fonts css because it will be injected in header
 */

const cssFonts = function() {
  return gulp.src(config.css.dest + '/style.fonts.css')
    .pipe(cleanCSS({compatibility: '*'})) //'*' = default, includes IE10+
    .pipe(gulp.dest(config.css.dest));
};

gulp.task('css:fonts', function () {
  return cssFonts();
});

/**
 * bundle the css tasks into 1 big task
 */

gulp.task('css:dev', gulp.series('css:message:dev','css:theme:dev', 'css:contentblocks:dev','css:features:dev', 'css:fonts', 'css:mail', 'css:editor'));
gulp.task('css:prod', gulp.series('css:theme:prod', 'css:contentblocks:prod','css:features:prod', 'css:fonts', 'css:mail', 'css:editor'));


/**
 * Exports
 */

exports.cssDev = gulp.series('css:message:dev','css:theme:dev', 'css:contentblocks:dev','css:features:dev', 'css:fonts', 'css:mail', 'css:editor');
exports.cssProd = gulp.series('css:theme:prod', 'css:contentblocks:prod','css:features:prod', 'css:fonts', 'css:mail', 'css:editor');

exports.buildCSSThemeDev = buildCSSThemeDev;
exports.buildCSSContentBlocksDev = buildCSSContentBlocksDev;
exports.buildCSSFeaturesDev = buildCSSFeaturesDev;

exports.cssMail = cssMail;
exports.cssEditor = cssEditor;
