/* eslint-disable */
/*jshint esversion: 6*/

var gulp = require('gulp-help')(require('gulp'), { hideDepsMessage: true, afterPrintCallback: cliNotes });
var bs = require('browser-sync').create();
var gulpUtil = require('gulp-util');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var del = require('del');
var imagemin = require('gulp-imagemin');
var sass = require('gulp-sass');
var sassLint = require('gulp-sass-lint');
var eslint = require('gulp-eslint');
var changed = require('gulp-changed');
var runSequence = require('run-sequence');
var merge = require('merge-stream');
var include = require('gulp-include');
var Stream = require('stream');
var newer = require('gulp-newer');
var browserSync = require('browser-sync').create();
var iconfont = require('gulp-iconfont');
var iconfontCss = require('gulp-iconfont-css');

var runTimestamp = Math.round(Date.now()/1000);
var iconFontName = 'themeIcons';

gulpUtil.env.type = 'production';
gulpUtil.env.build_type = 'build';

var CONFIGS = require('require-dir')('./gulp_config');

gulp.task('default', false, ['help']);

gulp.task('build', 'Production ready build for client code.', (done) => {
    build(done);
});

gulp.task('build--dev', 'Development ready build for client code.', (done) => {
    gulpUtil.env.type = 'development';
    build(done);
});

gulp.task('browser-sync', ['build--dev', 'watch'], () => {
  // CSS Files to inject.
  var files = [
    '../docroot/modules/features/**/css/*.css',
    '../docroot/modules/custom/**/css/*.css',
    '../docroot/themes/custom/**/css/*.css'
  ];

  /* Uncomment the following lines and modify the domain to use browsersync. */
  // browserSync.init(files, {
  //   proxy: 'https://www.local-domain-here.docksal',
  //   notify: true,
  //   open: false
  // });
});

gulp.task('watch', 'Watch source files for changes and build on update', ['build--dev'], () => {
  gulpUtil.env.type = 'development';
  gulpUtil.env.build_type = 'watch';

  var javascriptSources = [];
  var sassSources = [];
  var imageSources = [];
  var fontSources = [];

  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];

    if (config.javascript && config.javascript.src && config.javascript.dest) {
      javascriptSources.push(config.javascript.src);
    }

    if (config.sass && config.sass.src && config.sass.dest) {
      sassSources.push(config.sass.src);
    }

    if (config.images && config.images.src && config.images.dest) {
      imageSources.push(config.images.src);
    }

    if (config.fonts && config.fonts.src && config.fonts.dest) {
      fontSources.push(config.fonts.src);
    }
  });

  gulp.watch(javascriptSources, () => runSequence('_build.javascript', '_js-lint'));
  gulp.watch(sassSources, () => runSequence('_build.sass'));
  gulp.watch(imageSources, () => runSequence('_build.images'));
  gulp.watch(fontSources, () => runSequence('_build.fonts'));
});

gulp.task('_build.iconFont', 'Build the icon font and move to distribute', () => {
  var tasks = [];
  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];

    if (config.iconFont && config.iconFont.src && config.iconFont.dest && config.iconFont.scssFile && config.iconFont.filePath) {
      tasks.push(gulp.src(config.iconFont.src)
        // .pipe(include()).on('error', console.log)
        .pipe(iconfontCss({
          fontName: iconFontName,
          path: 'scss',
          targetPath: config.iconFont.scssFile,
          fontPath: config.iconFont.filePath,
        }))
        .pipe(iconfont({
          fontName: iconFontName, // required
          prependUnicode: false, // recommended option
          formats: ['ttf', 'eot', 'woff'], // default, 'woff2' and 'svg' are available
          timestamp: runTimestamp, // recommended to get consistent builds when watching files
          normalize: true, // scale them to the height of the highest icon
          fontHeight: 1001
        }))
        .on('glyphs', function (glyphs, options) {
          // CSS templating, e.g.
          // console.log(glyphs, options);
        })
        .pipe(gulp.dest(config.iconFont.dest)));
    }
  });
  return merge(tasks);
});


gulp.task('_build.javascript', 'Build JavaScript and move to distribute', () => {
  var tasks = [];
  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];

    if (config.javascript && config.javascript.src && config.javascript.dest) {
      tasks.push(gulp.src(config.javascript.src)
        .pipe(include()).on('error', console.log)
        .pipe(isBuild() ? gulpUtil.noop() : changed(config.javascript.dest))
        .pipe(isProd() ? gulpUtil.noop() : sourcemaps.init())
        .pipe(isProd() ? uglify() : gulpUtil.noop())
        .pipe(isProd() ? gulpUtil.noop() : sourcemaps.write('./maps'))
        .pipe(gulp.dest(config.javascript.dest)));
    }
  });
  return merge(tasks);
});

gulp.task('_sass-lint', 'Lint Sass to check for style errors', () => {
  var tasks = [];
  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];

    if (config.sass && config.sass.src && config.sass.dest) {
      tasks.push(gulp.src(config.sass.lintSrc)
        .pipe(sassLint({
          sasslintConfig: '.sass-lint.yml'
        }))
        .pipe(sassLint.format())
        .pipe(sassLint.failOnError())
        .on('error', swallowError));
    }
  });
  return merge(tasks);
});

gulp.task('_js-lint', 'Lint JS to check for style errors', () => {
  var tasks = [];
  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];

    if (config.javascript && config.javascript.src && config.javascript.dest) {
      tasks.push(gulp.src(config.javascript.src)
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError())
        .on('error', swallowError));
    }
  });
  return merge(tasks);
});

gulp.task('_build.sass', 'Build Sass and compile out CSS', () => {
  var tasks = [];
  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];

    if (config.sass && config.sass.src && config.sass.dest) {
      tasks.push(gulp.src(config.sass.src)
            .pipe(isProd() ? gulpUtil.noop() :  sourcemaps.init())
            .pipe(isProd() ? sass({
              outputStyle: 'compressed',
              includePaths: []
            }) : sass({
              noCache: true,
              outputStyle: "compressed",
              lineNumbers: false,
              includePaths: [],
              sourceMap: true
            }).on('error', swallowError))
            .pipe(isProd() ? gulpUtil.noop() : sourcemaps.write('./maps'))
            .on('error', swallowError)
            .pipe(gulp.dest(config.sass.dest)));
    }
  });
  return merge(tasks);
});

gulp.task('_build.images', 'Compress and distribute images', () => {

  var tasks = [];

  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];
    if (config.images && config.images.src && config.images.dest) {
      tasks.push(gulp.src(config.images.src)
        .pipe(isBuild() ? gulpUtil.noop() : changed(config.images.dest))
        .pipe(newer(config.images.dest))
        .pipe(imagemin({progressive: true}))
        .pipe(gulp.dest(config.images.dest)));
    }
  });
  return merge(tasks);
});

gulp.task('_build.fonts', 'Distribute local fonts', () => {

  var tasks = [];

  Object.keys(CONFIGS).forEach(function (key) {
    var config = CONFIGS[key];
    if (config.fonts && config.fonts.src && config.fonts.dest) {
      tasks.push(gulp.src(config.fonts.src)
          .pipe(isBuild() ? gulpUtil.noop() : changed(config.fonts.dest))
          .pipe(newer(config.fonts.dest))
          .pipe(gulp.dest(config.fonts.dest)));
    }
  });
  return merge(tasks);
});

function build(done) {
  runSequence(
      ['_build.fonts', '_build.iconFont', '_build.images', '_build.javascript', '_js-lint', '_build.sass', '_sass-lint'],
      done
  );
}

function swallowError(error) {
    console.log(error.toString());

    this.emit('end');
}

function isProd() {
    return gulpUtil.env.type === 'production';
}
function isBuild() {
    return gulpUtil.env.build_type === 'build';
}

function cliNotes() {
    console.log('  * _tasks are private sub tasks. Only use if necessary. *\n\n\n');
}
