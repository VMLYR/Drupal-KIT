// Default Task
'use strict';
const gulp = require('gulp');

const { setup } = require('./00-setup');
// const { favicon } = require('./01-favicon');
const { cssDev } = require('./01-css');
const { cssProd } = require('./01-css');
// const { cssMail } = require('./01-css');
// const { cssEditor } = require('./01-css');
const { cssLint } = require('./01-css-lint');
const { jsDev } = require('./01-js');
const { jsProd } = require('./01-js');
const { jsLint } = require('./01-js-lint');
const { optimizeImages } = require('./01-images');
const { watch } = require('./03-watch');

// -- gulp 3
//
// var runSequence = require('run-sequence');
//
// gulp.task('dev', function(callback) {
//   runSequence(
//     ['setup', 'css:lint', 'js:lint', 'css:dev', 'js:dev', 'images', 'watch'],
//     ['css:mail', 'css:editor']
//   );
// });
//
// gulp.task('prod', function(callback) {
//   runSequence(
//     ['setup', 'css:prod', 'js:prod', 'images', 'favicon'],
//     ['css:mail', 'css:editor']
//   );
// });
//
// gulp.task('default', function(callback) {
//   runSequence(
//     'dev'
//   );
// });

// -- gulp 4

gulp.task('dev:1', gulp.series(setup, cssLint, jsLint, cssDev, jsDev));

gulp.task('dev', gulp.series('dev:1'));


gulp.task('prod:1', gulp.series(setup, cssProd, jsProd/*, optimizeImages*/));

gulp.task('prod', gulp.series('prod:1'));


gulp.task('default', gulp.series('dev', 'watch'));
