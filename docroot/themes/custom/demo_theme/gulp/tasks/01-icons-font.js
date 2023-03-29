// Iconfont task
'use strict';
const gulp = require('gulp'),
    iconfont = require('gulp-iconfont'),
    iconfontCss = require('gulp-iconfont-css'),
    runTimestamp = Math.round(Date.now() / 1000);

const { config } = require('../config');

const iconsFont = function() {
  return gulp.src([config.iconfont.src])
    .pipe(iconfontCss({
      path: config.iconfont.template,
      fontName: config.iconfont.fontName,
      targetPath: config.iconfont.sass,
      fontPath: '../fonts/' + config.iconfont.fontName + '/'
    }))
    .pipe(iconfont({
      fontName: config.iconfont.fontName, // required
      normalize:true,
      fontHeight: 1001,
      formats: ['svg', 'ttf', 'eot', 'woff', 'woff2'],
      prependUnicode: true, // recommended option
      timestamp: runTimestamp // recommended to get consistent builds when watching files
    }))
    .pipe(gulp.dest(config.iconfont.dest));

};

gulp.task('icons:font', function () {
  return iconsFont();
});

/**
 * Exports
 *
 */
exports.iconsFont = iconsFont;
