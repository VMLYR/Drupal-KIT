// Watch Files For Changes
'use strict';
const gulp = require('gulp');

const { config } = require('../config');
// const { setup } = require('./00-setup');
// const { favicon } = require('./01-favicon');
// const { cssDev } = require('./01-css');
const { buildCSSThemeDev } = require('./01-css');
const { buildCSSContentBlocksDev } = require('./01-css');
const { buildCSSFeaturesDev } = require('./01-css');
// const { cssProd } = require('./01-css');
const { cssMail } = require('./01-css');
const { cssEditor } = require('./01-css');
const { cssLint } = require('./01-css-lint');
const { buildJsGlobalDev } = require('./01-js');
const { buildJsComponentsDev } = require('./01-js');
// const { jsDev } = require('./01-js');
// const { jsProd } = require('./01-js');
const { jsLint } = require('./01-js-lint');
const { optimizeImages } = require('./01-images');

//   var bs = require('browser-sync').get('bs');

function watch(done) {

  // Lint the Sass + compile same sources to CSS
  gulp.watch(config.css.src, gulp.series(cssLint, buildCSSThemeDev, buildCSSContentBlocksDev, buildCSSFeaturesDev));

  // separate the mail and editor css
  gulp.watch(config.css.mail.src, gulp.series(cssMail));
  gulp.watch(config.css.editor.src, gulp.series(cssEditor));

  // lint the src JS + compile same sources
  // don't bundle the js like we did with the CSS, doesn't seem to want to compile then
  // gulp.watch(config.js.src, ['js:lint', 'js:global:dev', 'js:components:dev']);
  gulp.watch(config.js.global.src, gulp.series(jsLint, buildJsGlobalDev));
  gulp.watch(config.js.components.src, gulp.series(jsLint, buildJsComponentsDev));

  // https://github.com/BrowserSync/browser-sync/issues/392
  // .on('change', function(evt) {
  //   bs.reload();
  // });

  // Optimize images
  // gulp.watch(config.images.src, gulp.series(optimizeImages));

  done();

}

gulp.task('watch', function (done) {
  watch(done);
});

exports.watch = watch;
