// Lint-js Task
'use strict';
const gulp = require('gulp'),
    jshint = require('gulp-jshint'),
    map = require('map-stream');

const { config } = require('../config');

let error = false;

const errorReporter = function () {
  return map(function (file, cb) {
    if (!file.jshint.success) {
      error = true;
    }
    cb(null, file);
  });
};

function jsLint() {
  return gulp.src(config.js.src) // 'js/*.js'
    .pipe(jshint())
    .pipe(jshint.reporter('default'));
}

function jsLintPrecommit(done) {
  return gulp.src(config.js.src) // 'js/*.js'
    .pipe(jshint())
    .pipe(jshint.reporter('default'))
    .pipe(errorReporter())
    .on('error', function (err) {
      if (error) {
        this.emit('end');
      }
    })
    .on('end', function() {
      if (error) {
        process.exitCode = 1;
      }
      done();
    });
}

gulp.task('js:lint', function () {
  return jsLint();
});

gulp.task('js:lint:precommit', function (done) {
  return jsLintPrecommit(done);
});

exports.jsLint = jsLint;
