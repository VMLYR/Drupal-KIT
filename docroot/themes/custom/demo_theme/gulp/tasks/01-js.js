// Concatenate & Minify JS
'use strict';
const gulp = require('gulp'),
  sourcemaps = require('gulp-sourcemaps'),
  concat = require('gulp-concat'),
  rename = require('gulp-rename'),
  flatten = require('gulp-flatten'),
  uglify = require('gulp-uglify-es').default,
  gulpif = require('gulp-if'),
  notify = require('gulp-notify'),
  bs = require('browser-sync').get('bs'),
  pump = require('pump');

const { config } = require('../config');
const { errorNotification } = require('./00-setup');

const options = {};


/**
 *
 * functions for compiling JS per part
 */

// Development

const buildJsComponentsDev = function (done) {

  const mySrc = config.js.components.src,
    myDest = config.js.dest;

  return gulp.src(mySrc)
    .pipe(sourcemaps.init())
    .on('error', function (err) {
      errorNotification(this, err);
    })

    // replace relative paths for files
    .pipe(flatten({ includeParents: 0}))

    // // copy to temp location to perform more tasks on it
    // .pipe(gulp.dest(config.js.tmp))

    // .pipe(rename({
    //   suffix: '.min'
    // }))

    // .pipe(uglify())

    .on('error', function (err) {
      errorNotification(this, err);
    })
    .pipe(
      sourcemaps.write('./' + myDest, {
        sourceMappingURL: function (file) {
          // // We add a timestamp for cachebusting
          // return file.relative + '.map?build=' + new Date().getTime();
          return file.relative + '.map';
        }
      })
    )

    // replace relative paths for files
    .pipe(flatten())

    .pipe(gulp.dest(myDest))

    .pipe(gulpif(config.arg.url !== false, bs.stream({match: '**/*.js'})));
};

const buildJsGlobalDev = function (done) {

  const mySrc = config.js.global.src,
    myDest = config.js.dest;

  pump([

    gulp.src(mySrc),

    sourcemaps.init().on('error', function (err) {
      errorNotification(this, err);
    }),

    // merge files into 1
    concat('scripts.js', options).on('error', function (err) {
      errorNotification(this, err);
    }),

    // gulp.dest(config.js.tmp),

    // rename({
    //   suffix: '.min'
    // }).on('error', function (err) {
    //   errorNotification(this, err);
    // }),

    // uglify().on('error', function (err) {
    //   errorNotification(this, err);
    // }),

    sourcemaps.write('.', {
      sourceMappingURL: function (file) {
        // We add a timestamp for cachebusting
        // return file.relative + '.map?build=' + new Date().getTime();
        return file.relative + '.map';
      }
    }).on('error', function (err) {
      // console.error('Error in compress task', err.toString());
      errorNotification(this, err);
    }),

    gulp.dest(myDest)
      .on('error', function (err) {
        // console.error('Error in compress task', err.toString());
        errorNotification(this, err);
      }),

    gulpif(config.arg.url !== false, bs.stream({match: '**/*.js'}))

  ]);

  done();
};


// Production

const buildJsComponentsProd = function (done) {

  const mySrc = config.js.components.src,
    myDest = config.js.dest;

  return gulp.src(mySrc)

    // replace relative paths for files
    .pipe(flatten({ includeParents: 0}))

    // .pipe(gulp.dest(config.js.tmp))

    // .pipe(rename({
    //   suffix: '.min'
    // }))
    .pipe( uglify())

    // replace relative paths for files
    .pipe(flatten())

    .pipe(gulp.dest(myDest));
};

const buildJsGlobalProd = function (done) {

  const mySrc = config.js.global.src,
    myDest = config.js.dest;

  pump([

      gulp.src(mySrc),

      // merge files into 1
      concat('scripts.js', options),

      // gulp.dest(config.js.tmp),

      // rename({
      //   suffix: '.min'
      // }),

      uglify(),

      gulp.dest(myDest)

    ]
  );

  done();
};

/**
 * Components
 */


gulp.task('js:components:dev', function(done) {

  return buildJsComponentsDev(done);

});

gulp.task('js:components:prod', function(done) {

  return buildJsComponentsProd(done);

});


/**
 * Global
 */

gulp.task('js:global:dev', function (done) {

  return buildJsGlobalDev(done);

});

gulp.task('js:global:prod', function (done) {

  return buildJsGlobalProd(done);

});

/**
 * Combined tasks
 */

gulp.task('js:message:dev', function (done) {
  notify().write("Don't commit development JS. Use 'gulp js:prod' instead.");
  notify().write("Sourcemap files won't work on environments and lead to errors & overhead");
  done();
});

gulp.task('js:dev', gulp.series('js:message:dev', 'js:global:dev', 'js:components:dev'));
gulp.task('js:prod', gulp.series('js:global:prod', 'js:components:prod'));

/**
 * Exports
 */

exports.buildJsGlobalDev = buildJsGlobalDev;
exports.buildJsComponentsDev = buildJsComponentsDev;

exports.jsDev = gulp.series('js:message:dev', 'js:global:dev', 'js:components:dev');
exports.jsProd = gulp.series('js:global:prod', 'js:components:prod');
