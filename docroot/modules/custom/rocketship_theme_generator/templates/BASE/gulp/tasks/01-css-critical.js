// Generate above-the-fold css
//
// https://www.npmjs.com/package/critical
// https://www.fourkitchens.com/blog/article/use-gulp-automate-your-critical-path-css/
// https://www.fourkitchens.com/blog/article/use-grunt-and-advagg-inline-critical-css-drupal-7-theme/
// https://github.com/addyosmani/critical/releases/tag/v2.0.1

const gulp = require('gulp'),
  critical = require('critical');

const { config } = require('../config');

function criticalCSS(myDest, done) {

  critical.generate({
    inline: false,
    base: './',
    src: config.arg.criticalUrl,
    dimensions: [{
      width: 320,
      height: 480
    },{
      width: 768,
      height: 1024
    },{
      width: 1024,
      height: 768
    },{
      width: 1280,
      height: 1080
    }],
    target: {
      css: 'css/critical/' + config.arg.criticalName + '.css'
    },
    ignore: {
      atrule: ['@font-face'],
      decl: (node, value) => /url\(/.test(value)
    },
    extract: false
  });

  done();

};

/**
 * Individual tasks
 */
gulp.task('css:critical', function (done) {
  return criticalCSS(config.css.dest, done);
});
