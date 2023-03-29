// Lint-scss Task
'use strict';
const gulp = require('gulp'),
    postcss = require('gulp-postcss'),
    reporter = require('postcss-reporter'),
    syntaxSCSS = require('postcss-scss'),
    notify = require('gulp-notify'),
    stylelint = require('stylelint');

const cached  = require('gulp-cached');

const { config } = require('../config');

let error = false;
let errorMessages = '';

/**
 * Linting for the Sass files: separate in functions
 */

function cssLint(src) {
  var processors = [
    stylelint({
      config: config.stylelintConfig,
      fix: true,
      files: src
    }),
    reporter({
      clearMessages: true
    })
  ];

  return gulp.src(src, {base: "./"})
    .pipe(cached(cssLint))
    .pipe(postcss(processors, {syntax: syntaxSCSS}))
    .pipe(cached(cssLint))
    .pipe(gulp.dest('./'));
}

function cssLintPrecommit(done) {
  var self = this;
  var src = config.css.src;
  var processors = [
    stylelint({
      config: config.stylelintConfig,
      files: src
    }),
    reporter({
      throwError: true,
      formatter: function(input) {
        if (Object.keys(input.messages).length) {
          error = true;
          errorMessages = input.messages;
        }
      }
    })
  ];

  return gulp.src(config.css.src)
    .pipe(cached(cssLint))
    .pipe(postcss(processors, {syntax: syntaxSCSS}))
    .on('error', function (err) {
      if (error) {
        this.emit('end');
      }
    })
    .on('end', function() {
      notify().write("end");
      if (error) {
        for (let key in errorMessages) {
          notify().write(errorMessages[key]);
        }
        process.exitCode = 1;
      }
      done();
    });
}

/**
 * Use the functions in tasks
 */

// ** for development

// All css at once

const CssLintAll = function() {
  return cssLint(config.css.src);
};

gulp.task('css:lint:all', function () {
  return CssLintAll(config.css.src);
});

// Component's Theme css folder

const CssLintTheme = function() {
  return cssLint(config.css.components.theme.src);
};

gulp.task('css:lint:theme', function () {
  return CssLintTheme();
});

// Component's ContentBlocks css folder

const CssLintContentBlocks = function() {
  return cssLint(config.css.components.contentblocks.src);
};

gulp.task('css:lint:contentblocks', function () {
  return CssLintContentBlocks();
});

// Component's Features css folder

gulp.task('css:lint:features', function () {
  return CssLintFeatures();
});

const CssLintFeatures = function() {
  return cssLint(config.css.components.features.src, CssLintFeatures);
};

// ** Linting all CSS in precommit hook

const CssLintPrecommit = function(done) {
  return cssLintPrecommit(done);
};

gulp.task('css:lint:precommit', function (done) {
  return CssLintPrecommit(done);
});

/**
 *  run the tasks in bundles
 */

gulp.task('css:lint', gulp.series('css:lint:theme', 'css:lint:contentblocks','css:lint:features'));

/**
 * Exports
 */

exports.cssLint = gulp.series('css:lint:theme', 'css:lint:contentblocks','css:lint:features');

exports.CssLintAll = CssLintAll;
exports.CssLintTheme = CssLintTheme;
exports.CssLintContentBlocks = CssLintContentBlocks;
exports.CssLintFeatures = CssLintFeatures;
