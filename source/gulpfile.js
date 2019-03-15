/* eslint-disable */
/*jshint esversion: 6*/

// ========= Packages ========= //
var changed = require('gulp-changed');
var eslint = require('gulp-eslint');
var gulp = require('gulp-help')(require('gulp'), { hideDepsMessage: true, afterPrintCallback: cliNotes });
var gulpUtil = require('gulp-util');
var iconfont = require('gulp-iconfont');
var iconfontCss = require('gulp-iconfont-css');
var imagemin = require('gulp-imagemin');
var include = require('gulp-include');
var merge = require('merge-stream');
var newer = require('gulp-newer');
var runSequence = require('run-sequence');
var sass = require('gulp-sass');
var sassLint = require('gulp-sass-lint');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

// ========= Global vars ========= //
var runTimestamp = Math.round(Date.now()/1000);
var iconFontName = 'themeIcons';
var CONFIGS = require('require-dir')('./gulp_config');

// ========= Tasks ========= //

// === Tasks: Default === //
gulpUtil.env.type = 'production';
gulpUtil.env.build_type = 'build';
gulp.task('default', false, ['help']);

// === Tasks: Build === //
gulp.task('build', 'Production ready build for client code.', (done) => {
  build(done);
});

// === Tasks: Build--dev === //
gulp.task('build--dev', 'Development ready build for client code.', (done) => {
  gulpUtil.env.type = 'development';
build(done);
});

// === Tasks: Watch === //
gulp.task('watch', 'Watch source files for changes and build on update', ['build--dev'], () => {
  gulpUtil.env.type = 'development';
gulpUtil.env.build_type = 'watch';

var javascriptSources = [];
var javascriptDest = [];
var sassSources = [];
var sassDest = [];
var imageSources = [];

Object.keys(CONFIGS).forEach(function (key) {
  var config = CONFIGS[key];

  if (config.javascript && config.javascript.src && config.javascript.dest) {
    javascriptSources.push(config.javascript.src);
  }

  if (config.javascript && config.javascript.src && config.javascript.dest) {
    javascriptDest.push(config.javascript.dest);
  }

  if (config.sass && config.sass.src && config.sass.dest) {
    sassSources.push(config.sass.src);
  }

  if (config.sass && config.sass.src && config.sass.dest) {
    sassDest.push(config.sass.dest);
  }

  if (config.images && config.images.src && config.images.dest) {
    imageSources.push(config.images.src);
  }
});

gulp.watch(javascriptSources, () => runSequence('_build.javascript', '_js-lint'));
gulp.watch(sassSources, () => runSequence('_build.sass', '_sass-lint'));
gulp.watch(imageSources, () => runSequence('_build.images'));
});

// ========= Sub-tasks ========= //

// === Sub-tasks: javascript === //
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

// === Sub-tasks: js-lint === //
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

// === Sub-tasks: sass === //
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

// === Sub-tasks: sass lint === //
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

// === Sub-tasks: images === //
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

// === Sub-tasks: fonts === //
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

// === Sub-tasks: iconFont === //
gulp.task('_build.iconFont', 'Build the icon font and move to distribute', () => {
  var tasks = [];

Object.keys(CONFIGS).forEach(function (key) {
  var config = CONFIGS[key];

  if (config.iconFont && config.iconFont.src && config.iconFont.dest && config.iconFont.scssTemplate && config.iconFont.scssFile && config.iconFont.filePath) {
    tasks.push(gulp.src(config.iconFont.src)
        .pipe(include()).on('error', console.log)
        .pipe(iconfontCss({
          fontName: iconFontName,
          path: config.iconFont.scssTemplate,
          targetPath: config.iconFont.scssFile,
          fontPath: config.iconFont.filePath
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
          // uncomment to see full logging output
          // console.log(glyphs, options);
        })
        .pipe(gulp.dest(config.iconFont.dest)));
  }
});

return merge(tasks);
});

// ========= Functions ========= //

// === Functions: build === //
function build(done) {
  runSequence(
      ['_build.fonts', '_build.iconFont', '_build.images', '_build.javascript', '_js-lint', '_build.sass', '_sass-lint'], done
  );
}

// === Functions: swallowError === //
function swallowError(error) {
  console.log(error.toString());
  this.emit('end');
}

// === Functions: isProd === //
function isProd() {
  return gulpUtil.env.type === 'production';
}

// === Functions: isBuild === //
function isBuild() {
  return gulpUtil.env.build_type === 'build';
}

// === Functions: cliNotes === //
function cliNotes() {
  console.log('  * _tasks are private sub tasks. Only use if necessary. *');
}
