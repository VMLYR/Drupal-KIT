// Setup
'use strict';

const gulp = require('gulp');
const fs = require('fs');
const notify = require('gulp-notify');
const bs = require('browser-sync').create('bs');

const { config } = require('../config');


/**
 * Functionality
 */

// only load browser-sync if we pass a project URL to Gulp (for the CSS/JS tasks)
if (config.arg.url !== false) {

  // var bs = require('browser-sync').create('bs');

  bs.init({
    proxy: config.arg.url,
    //logLevel: 'debug',
    online: 'false',
    stream: true,
    injectChanges: true,
    reloadDelay: config.arg.delay ? 500 : 0
  });
}

/**
 * Setup:
 * - favicon message
 * - …
 * @param done
 */
var setup = function(done) {

  // Check if the favicons are already generated
  fs.stat('./favicons/generated/faviconData.json', function (err, stat) {
    if (err != null) {
      notify().write("Favicons don't exist yet. Add your image and run: gulp favicon or check README.md in favicons/source");
    }
  });
  done();
};

/** generic error notifications
 *  - return using .on('error', function (err) { }
 */
var errorNotification = function(self, err) {
  // Message errors to Mac OS X, Linux or Windows
  notify().write(err.formatted);
  self.emit('end');
};


/**
 * Tasks
 */

gulp.task('setup', function (done) {
  return setup(done);
});


/**
 * Exports
 */

exports.setup = setup;
exports.errorNotification = errorNotification;
