'use strict';
const gulp = require('gulp'),
    cssBase64 = require('gulp-css-base64'),
    rename = require('gulp-rename');

const { config } = require('../config');

const fontsInline = function() {
  return gulp.src([config.fonts.src])
    .pipe(cssBase64({
      baseDir: './'
    }))
    .pipe(gulp.dest(config.fonts.dest));
};

gulp.task('fonts:inline', function () {
  return fontsInline();
});

/**
 * Exports
 *
 */
exports.fontsInline = fontsInline;
