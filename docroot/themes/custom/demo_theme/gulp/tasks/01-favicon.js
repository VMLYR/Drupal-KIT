// Favicon generation task
// uses the RealFavicon API: https://realfavicongenerator.net/
'use strict';

const gulp = require('gulp'),
  fs = require('fs'),
  replace = require('gulp-replace'),
  notify = require('gulp-notify'),
  sizeOf = require('image-size'),
  realFavicon = require('gulp-real-favicon'),
  path = require('path');

const { config } = require('../config');

let masterPicture = null;
let masterPictureMobile = null;

const masterPictureSVG = config.favicon.master.SVG;
const masterPictureSVGMobile = config.favicon.master.SVGMobile;

const masterPicturePNG = config.favicon.master.PNG;
const masterPicturePNGMobile = config.favicon.master.PNGMobile;


const themeName = path.basename(process.cwd());

let colorPrimary = '#10bfff';
let colorWhite = '#ffffff';

// File where the favicon markups are stored
const FAVICON_DATA_FILE = config.favicon.datafile; //'./favicons/generated/faviconData.json';

const _validFileExtensions = [".svg"];

function isSVG(sFileName) {
  if (sFileName.length > 0) {
    let blnValid = false;
    for (let j = 0; j < _validFileExtensions.length; j++) {
      const sCurExtension = _validFileExtensions[j];
      if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() === sCurExtension.toLowerCase()) {
        blnValid = true;
        break;
      }
    }

    if (!blnValid) {
      return false;
    }
  }

  return true;
}

const replaceVarsTwig = function() {
  notify().write('filling in Safari colors based on Sass main colors: ' + colorPrimary);

  return gulp.src(config.htmlTwig)
    .pipe(replace(/fav_var_color/g, colorPrimary))
    .pipe(gulp.dest(config.html));
}

const moveFavicon = function() {
  notify().write('Move the ico file to the root of the theme');

  return gulp.src(config.favicon.srcico)
    .pipe(gulp.dest('./'));
}

const generate = function(done) {

  notify().write('generate the icons');

  const re = new RegExp('_', 'g');
  const re2 = new RegExp('-', 'g');
  const re3 = new RegExp('theme', 'g');
  const re4 = new RegExp('8', 'g');
  const themeSpaced = themeName.replace(re, ' ');
  const themeSpaced2 = themeSpaced.replace(re2, ' ');
  const themeShort = themeSpaced2.replace(re3, '');
  const themeShort2 = themeShort.replace(re4, '');

  String.prototype.capitalize = function() {
    return this.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
  };

  const themeFull = themeShort2.capitalize();

  // Generate images and manifest
  // src: https://realfavicongenerator.net/api/non_interactive_api
  //

  if (masterPictureMobile !== null) {

    notify().write('masterPictureMobile');
    notify().write(masterPictureMobile);

    realFavicon.generateFavicon({
      masterPicture: masterPicture,
      dest: config.favicon.dest,
      iconsPath: '/themes/custom/' + themeName + '/favicons/generated',
      design: {
        ios: {
          masterPicture: masterPictureMobile,
          pictureAspect: 'backgroundAndMargin',
          backgroundColor: colorWhite,
          margin: '18%',
          assets: {
            ios6AndPriorIcons: false,
            ios7AndLaterIcons: true,
            precomposedIcons: false,
            declareOnlyDefaultIcon: true
          }
        },
        desktopBrowser: {},
        androidChrome: {
          masterPicture: masterPictureMobile,
          pictureAspect: 'backgroundAndMargin',
          margin: '18%',
          themeColor: colorPrimary,
          backgroundColor: colorWhite,
          manifest: {
            name: themeFull,
            display: 'browser',
            orientation: 'notSet',
            onConflict: 'override',
            declared: true
          },
          assets: {
            legacyIcon: false,
            lowResolutionIcons: false
          }
        }
      },
      settings: {
        scalingAlgorithm: 'Mitchell',
        errorOnImageTooSmall: true,
        readmeFile: false,
        htmlCodeFile: false,
        usePathAsIs: false
      },
      markupFile: FAVICON_DATA_FILE
    }, function () {

      replaceVarsTwig();

      moveFavicon();

      done();
    });

  } else {

    realFavicon.generateFavicon({
      masterPicture: masterPicture,
      dest: config.favicon.dest,
      iconsPath: '/themes/custom/' + themeName + '/favicons/generated',
      design: {
        ios: {
          pictureAspect: 'backgroundAndMargin',
          backgroundColor: colorWhite,
          margin: '18%',
          assets: {
            ios6AndPriorIcons: false,
            ios7AndLaterIcons: true,
            precomposedIcons: false,
            declareOnlyDefaultIcon: true
          }
        },
        desktopBrowser: {},
        androidChrome: {
          pictureAspect: 'backgroundAndMargin',
          margin: '18%',
          themeColor: colorPrimary,
          backgroundColor: colorWhite,
          manifest: {
            name: themeFull,
            display: 'browser',
            orientation: 'notSet',
            onConflict: 'override',
            declared: true
          },
          assets: {
            legacyIcon: false,
            lowResolutionIcons: false
          }
        }
      },
      settings: {
        scalingAlgorithm: 'Mitchell',
        errorOnImageTooSmall: true,
        readmeFile: false,
        htmlCodeFile: false,
        usePathAsIs: false
      },
      markupFile: FAVICON_DATA_FILE
    }, function () {

      replaceVarsTwig();

      moveFavicon();

      done();
    });

  }

}

const checkUpdate = function() {
  var currentVersion = JSON.parse(fs.readFileSync(FAVICON_DATA_FILE)).version;
	return realFavicon.checkForUpdates(currentVersion, function(err) {
		if (err) {
			throw err;
		}
  });
}

const mobileCheck = function (done) {

  notify().write('Generating favicons… (this may take a while)');

  // Check if a mobile icon exists
  fs.access(masterPictureSVGMobile, function (err, stat) {

    if (err == null) {

      notify().write('Found a separate favicon for mobile (SVG)');

      // set mobile to svg & use it for generation
      masterPictureMobile = masterPictureSVGMobile;
      generate(done);

    } else {

      // check for PNG version
      fs.access(masterPicturePNGMobile, function (err, stat) {

        if (err == null) {

          notify().write('Found a separate favicon for mobile (PNG)');

          // set mobile to png & use it for generation
          masterPictureMobile = masterPicturePNGMobile;
          generate(done);

        } else {
          // generation without mobile icon
          generate(done);
        }

      });
    }

  });

}

// make the favicon
gulp.task('favicon', function (done) {

  // Check for updates on RealFaviconGenerator (think: Apple has just
  // released a new Touch icon along with the latest version of iOS).
  var currentVersion = JSON.parse(fs.readFileSync(FAVICON_DATA_FILE)).version;
	realFavicon.checkForUpdates(currentVersion, function(err) {
		if (err) {
			throw err;
		} else {
      // Check if an SVG exists
      fs.access(masterPictureSVG, function (err, stat) {

        if (err == null) {

          // The file exists but check if it meets our minimum dimensions
          const dimensions = sizeOf(masterPictureSVG);

          if (dimensions.width === dimensions.height) {
            masterPicture = masterPictureSVG;
            mobileCheck(done);
          }
          else {
            // The dimensions are incorrect
            notify().write('The dimensions of the source image are incorrect.');
            notify().write('Must be square but they are: ' + dimensions.width + ' and ' + dimensions.height + ')');
          }

        }
        else {

          // fall back to PNG
          fs.access(masterPicturePNG, function (err, stat) {

            if (err == null) {

              // The file exists but check if it meets our minimum dimensions
              const dimensions = sizeOf(masterPicturePNG);

              if (dimensions.width === dimensions.height && dimensions.height > 511) {

                masterPicture = masterPicturePNG;
                mobileCheck(done);
              }
              else {
                // The dimensions are incorrect
                notify().write('The dimensions of the source image are incorrect.');
                notify().write('Must be square and bigger than 511px but they are: ' + dimensions.width + ' and ' + dimensions.height + ')');
              }

            }
            else {
              notify().write('Please add the favicon to source folder + fill in correct extention and filename in gulp/config.js');
            }
          });

        }
      });

    }
  });

  done();

});

/**
 * Exports
 */

exports.favicon = 'favicon';
