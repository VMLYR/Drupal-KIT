'use strict';

const gulp = require('gulp'),
    notify = require('gulp-notify'),
    imagemin = require('gulp-imagemin'),
    svgo = require('imagemin-svgo'),
    fs = require('fs');

const { config } = require('../config');
const { errorNotification } = require('./00-setup');

const $ = {
  svgSprite: require('gulp-svg-sprite'),
  size: require('gulp-size'),
};

const iconsSpriteClasses = function() {
  return gulp.src(config.sprite.src)
  .pipe($.svgSprite({
    shape: {
      spacing: {
        padding: [0, 10, 10, 10],
        box: 'content'
      },
      dimension: {
        maxWidth: 16,
        maxHeight: 16,
        precision: 2
      },
      id: { // SVG shape ID related options
        separator: '--', // Separator for directory name traversal
        generator: "rs-icon--%s", // SVG shape ID generator callback
      },
    },
    mode: {
      css: {
        dest: './',
        layout: 'diagonal',
        sprite: config.sprite.svg_css,
        bust: false,
        render: {
          scss: {
            dest: config.sprite.css2,
            template: config.sprite.template2
          }
        }
      }
    },
    svg: { // General options for created SVG files
      xmlDeclaration: true, // Add XML declaration to SVG sprite
      doctypeDeclaration: true, // Add DOCTYPE declaration to SVG sprite
      namespaceIDs: true, // Add namespace token to all IDs in SVG shapes
      namespaceIDPrefix: 'rs-', // Add a prefix to the automatically generated namespaceIDs
      namespaceClassnames: true, // Add namespace token to all CSS class names in SVG shapes
      dimensionAttributes: true // Width and height attributes on the sprite
    },
    variables: {
      mapname: 'icons'
    }
  }))
  .pipe(imagemin([
    imagemin.svgo({
      plugins: [
        { removeUselessDefs: false },
        { cleanupIDs: false },
        { removeXMLNS: false },
        { removeViewBox: false }
      ]
    }),
  ]))
  .on('error', function (err) {
    return errorNotification(this, err);
  })
  .pipe(gulp.dest('./'));
};

const iconsSprite = function() {
  return gulp.src(config.sprite.src)
    .pipe($.svgSprite({
      shape: {
        spacing: {
          padding: [0, 0, 0, 0],
          box: 'content'
        },
        dimension: {
          maxWidth: 16,
          maxHeight: 16,
          precision: 2
        },
        id: { // SVG shape ID related options
          separator: '--', // Separator for directory name traversal
          generator: "rs-icon--%s", // SVG shape ID generator callback
        },
      },
      mode: {
        css: {
          dest: './',
          layout: 'diagonal',
          sprite: config.sprite.svg,
          bust: false,
          render: {
            scss: {
              dest: config.sprite.css,
              template: config.sprite.template
            }
          }
        },
        symbol: {
          dest: './',
          sprite: config.sprite.svg_inline,
          inline: true
        },
      },
      svg: { // General options for created SVG files
        namespaceIDs: true, // Add namespace token to all IDs in SVG shapes
        namespaceIDPrefix: 'rs-', // Add a prefix to the automatically generated namespaceIDs
      },
      variables: {
        mapname: 'icons'
      }
    }))
    .pipe(imagemin([
      imagemin.svgo({
        plugins: [
          { removeUselessDefs: false },
          { cleanupIDs: false },
          { removeXMLNS: false },
          { removeViewBox: false }
        ]
      }),
    ]))
    .on('error', function (err) {
      return errorNotification(this, err);
    })
    .pipe(gulp.dest('./'));
};

/**
 * List all the icons so we can use them in Storybook
 *
 * @param {*} done
 * @returns
 */

function toTitleCase(str) {
  return str.replace(
    /\w\S*/g,
    function(txt) {
      return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    }
  );
}

const listIcons = (done) => {
  console.log('Icons:');
  const files = fs.readdirSync(config.sprite.src_folder);
  /*
  items:
  - name: 'test'
    title: 'Test'
  */
  let icon_list = 'items:';
  for (let key in files) {
    if (files.hasOwnProperty(key) && files[key].endsWith('.svg')) {
      const file = files[key];
      const name = file.split('.svg')[0];
      const title = toTitleCase(name);
      console.log('- ' + name);
      icon_list = icon_list + `
  - name: ` + name + `
    title: ` + title + ``
    }
  }

  fs.writeFileSync(config.sprite.icon_list, icon_list);

  return done;

};

const fixSVG = (done) => {

  fs.readFile(config.sprite.svg_inline, 'utf8', function (err, data) {

    if (err) {
      notify().write(err);
    }

    // let newData = data.toString();

    notify().write('find fill and stroke colors (that are not none or currentColor)');
    var rePattern = new RegExp("(?<=fill=\")(?!none|currentColor)(.*?)(?=\")", 'g'); // 'fill' value that is NOT "none" or "currentColor"
    var rePattern2 = new RegExp("(?<=stroke=\")(?!none|currentColor)(.*?)(?=\")", 'g'); // 'stroke' value that is NOT "none" or "currentColor"
    var rePattern3 = new RegExp("\"", 'g'); // 'stroke' value that is NOT "none" or "currentColor"

    notify().write('replace fill and stroke colors with currentColor so it can be changed via CSS');
    let newData = data.replace(rePattern, 'currentColor');
    newData = newData.replace(rePattern2, 'currentColor');
    newData = newData.replace(rePattern3, '\'');

    fs.writeFileSync(config.sprite.svg_inline, newData);

    done();

  });
}

/**
 * This task generates a sprite and puts it in images
 *
 */

gulp.task('icons:sprite:classes', function (done) {
  return listIcons(done) && iconsSpriteClasses();
});

gulp.task('icons:sprite:sprite', function (done) {
  return iconsSprite();
});

gulp.task('icons:sprite:cleanup', function (done) {
  return fixSVG(done);
});


gulp.task('icons:sprite', gulp.series('icons:sprite:classes', 'icons:sprite:sprite', 'icons:sprite:cleanup'));


/**
 * Exports
 *
 */
exports.iconsSprite = iconsSprite;
