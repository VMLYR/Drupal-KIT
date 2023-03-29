/*jslint node: true */

const gulp = require('gulp');
const path = require('path');
const themePath = process.cwd();
const themeName = path.basename(process.cwd());

// fetch command line arguments
// https://www.sitepoint.com/pass-parameters-gulp-tasks/
let arg = (argList => {

  let arg = {}, a, opt, thisOpt, curOpt;
  for (a = 0; a < argList.length; a++) {

    thisOpt = argList[a].trim();
    opt = thisOpt.replace(/^\-+/, '');

    if (opt === thisOpt) {

      // argument value
      if (curOpt) arg[curOpt] = opt;
      curOpt = null;

    }
    else {

      // argument name
      curOpt = opt;
      arg[curOpt] = true;

    }

  }

  return arg;

})(process.argv);

let config = {
  // We can pass variables to various gulp tasks in Terminal
  // Example: gulp css:prod --fontCache false
  // The variable gets stored into the 'arg' object here below
  // Some of these have defaults, as you can see here

  // only share certain arguments from Terminal
  // and give them a default value
  arg: {
    delay: arg.projectDelay || false,
    url: arg.projectUrl || false, // 'http://PROJECT_NAME.docker.localhost:81'
    fontCache: arg.fontCache || true,
    criticalUrl: arg.criticalUrl || false,
    criticalName: arg.criticalName || ''
  },

  // file locations
  paths: {
    base: './',
    theme: themePath,
  },
  themeName: themeName,
  htmlTwig: './components/00-theme/05-pages/html/_html.twig',
  html: './components/00-theme/05-pages/html',
  iconfont: {
    src: 'icons/**/*.svg',
    dest: 'fonts/iconfont',
    fontName: 'iconfont',
    sass: './../../components/00-theme/00-base/01-helpers/02-mixins/_mixins-iconfont.scss',
    template: './gulp/templates/_iconfont.scss'
  },
  sprite: {
    src: 'icons/**/*.svg',
    src_folder: 'icons',
    svg_css: 'images/generated/sprite-css.svg',
    svg_inline: 'images/generated/sprite-inline.svg',
    css: './components/00-theme/00-base/01-helpers/02-mixins/_mixins-sprite.scss',
    css2: './components/00-theme/00-base/09-icons/_icons-sprite.scss',
    template: './gulp/templates/_sprite.scss',
    template2: './gulp/templates/_sprite-classes.scss',
    icon_list: './components/00-theme/01-atoms/04-images/styleguide/icons.yml'
  },
  fonts: {
    src: './css/style.fonts.css',
    dest: './css'
  },
  images: {
    src: './images/source/**/*',
    dest: './images/generated'
  },
  css: {
    src: ['components/00-theme/**/*.scss',
      'components/01-content-blocks/**/*.scss',
      'components/02-features/**/*.scss',
      '!components/*/00-base/_libs.scss',
      '!components/*/00-base/01-helpers/05-templates/*.scss',
      '!components/*/00-base/04-vendors/**/*.scss',
      '!components/*/example-*.scss',
      '!components/*/tpl/**/*.scss',
      '!components/*/css/**/*.scss',
      '!components/*/00-example/**/*.scss',
      '!components/*/styleguide/**/*.scss',
      '!components/*/00-styleguide/**/*.scss',
      '!components/00-theme/05-pages/00-styleguide/pages.scss',
      '!components/02-features/00-examples/**/*.scss'
    ],
    dest: 'css',
    base: {
      src: ['components/00-theme/00-base/**/*.scss',
        'components/00-theme/01-atoms/**/*-helpers.scss',
        '!components/*/00-base/_libs.scss',
        '!components/*/00-base/01-helpers/05-templates/*.scss',
        '!components/*/00-base/04-vendors/**/*.scss',
        '!components/*/example-*.scss',
        '!components/*/tpl/**/*.scss',
        '!components/*/css/**/*.scss',
        '!components/*/00-example/**/*.scss',
        '!components/*/styleguide/**/*.scss',
        '!components/*/00-styleguide/**/*.scss',
        '!components/00-theme/05-pages/00-styleguide/pages.scss'
      ]
    },
    components: {
      theme: {
        src: ['components/00-theme/**/*.scss',
          '!components/*/00-base/_libs.scss',
          '!components/*/00-base/01-helpers/05-templates/*.scss',
          '!components/*/00-base/04-vendors/**/*.scss',
          '!components/*/example-*.scss',
          '!components/*/tpl/**/*.scss',
          '!components/*/css/**/*.scss',
          '!components/00-theme/*/00-example/**/*.scss',
          '!components/00-theme/*/styleguide/**/*.scss',
          '!components/00-theme/*/00-styleguide/**/*.scss',
          '!components/00-theme/05-pages/00-styleguide/pages.scss'
        ]
      },
      contentblocks: {
        src: [
          'components/01-content-blocks/**/*.scss',
          '!components/*/example-*.scss',
          '!components/01-content-blocks/*/00-example/**/*.scss',
          '!components/01-content-blocks/*/styleguide/**/*.scss',
          '!components/01-content-blocks/*/00-styleguide/**/*.scss'
        ],
      },
      features: {
        src: [
          'components/02-features/**/*.scss',
          '!components/02-features/*/example-*.scss',
          '!components/02-features/*/00-example/**/*.scss',
          '!components/02-features/*/styleguide/**/*.scss',
          '!components/02-features/*/00-styleguide/**/*.scss',
          '!components/02-features/00-examples/**/*.scss'
        ]
      }
    },
    mail: {
      src: ['css/style.mail.css'],
      dest: './'
    },
    editor: {
      src: ['css/style.editor.css'],
      dest: 'css',
    },
  },
  js: {
    src: ['components/00-theme/00-base/**/*.js',
      'components/00-theme/**/*.js',
      'components/01-content-blocks/**/*.js',
      'components/02-features/**/*.js',
      '!components/00-theme/**/*.min.js',
      '!components/00-theme/00-base/**/*.min.js',
      '!components/00-theme/01-atoms/00-example/**/*.js',
      '!components/**/styleguide/**/*.js',
      '!components/**/00-styleguide/**/*.js',
      '!components/02-features/00-examples/**/*.js'
    ],
    dest: 'js/dest',
    tmp: 'js/tmp',
    global: {
      src: ['components/00-theme/00-base/**/*.js',
        '!components/00-theme/00-base/**/*.min.js',
        '!components/00-theme/01-atoms/00-example/**/*.js',
        '!components/**/styleguide/**/*.js',
        '!components/**/00-styleguide/**/*.js'
      ]
    },
    components: {
      src: ['components/00-theme/**/*.js',
        'components/01-content-blocks/**/*.js',
        'components/02-features/**/*.js',
        '!components/00-theme/00-base/**/*.js',
        '!components/00-theme/**/*.min.js',
        '!components/00-theme/01-atoms/00-example/**/*.js',
        '!components/**/styleguide/**/*.js',
        '!components/**/00-styleguide/**/*.js',
        '!components/02-features/00-examples/**/*.js'],
      theme: {
        src: ['components/00-theme/**/*.js',
          '!components/00-theme/00-base/**/*.js',
          '!components/00-theme/**/*.min.js',
          '!components/00-theme/01-atoms/00-example/**/*.js',
          '!components/**/styleguide/**/*.js',
          '!components/**/00-styleguide/**/*.js']
      },
      contentblocks: {
        src: ['components/01-content-blocks/**/*.js',
          '!components/01-content-blocks/**/00-example/**/*.js',
          '!components/01-content-blocks/**/styleguide/**/*.js',
          '!components/01-content-blocks/**/00-styleguide/**/*.js'],
      },
      features: {
        src: ['components/02-features/**/*.js',
          '!components/02-features/**/00-example/**/*.js',
          '!components/02-features/**/styleguide/**/*.js',
          '!components/02-features/**/00-styleguide/**/*.js',
          '!components/02-features/00-examples/**/*.js']
      }
    }
  },
  favicon: {
    datafile: './favicons/generated/faviconData.json',
    master: {
      SVG: './favicons/source/favicon.svg',
      SVGMobile: './favicons/source/favicon-mobile.svg',
      PNG: './favicons/source/favicon.png',
      PNGMobile: './favicons/source/favicon-mobile.png'
    },
    src: './favicons/source/',
    dest: './favicons/generated/',
    srcico: './favicons/generated/favicon.ico'
  },
  stylelintConfig: {
    "plugins": [
      "stylelint-order"
    ],
    "rules": {
      "color-hex-case": "lower",
      "font-family-no-duplicate-names": true,
      "font-family-no-missing-generic-family-keyword": true,
      "function-calc-no-unspaced-operator": true,
      "function-comma-space-after": "always",
      "function-max-empty-lines": 0,
      "function-name-case": "lower",
      "function-parentheses-space-inside": "never",
      "function-url-quotes": "always",
      "number-no-trailing-zeros": true,
      "string-quotes": ["double", {
        "avoidEscape": true
      }],
      "unit-case": "lower",
      "unit-no-unknown": true,
      "value-keyword-case": "lower",
      "value-list-comma-newline-before": "never-multi-line",
      "value-list-comma-newline-after": "always-multi-line",
      "value-list-comma-space-before": "never-single-line",
      "value-list-comma-space-after": "always-single-line",
      "value-list-max-empty-lines": 0,
      "property-case": "lower",
      "declaration-block-no-duplicate-properties": [ true, {
        "ignore": ["consecutive-duplicates-with-different-values"]
      } ],
      "declaration-block-no-shorthand-property-overrides": true,
      "declaration-block-semicolon-newline-after": "always",
      "declaration-block-trailing-semicolon": "always",
      "declaration-block-single-line-max-declarations": 1,
      "declaration-colon-space-before": "never",
      "declaration-colon-space-after": "always",
      "declaration-bang-space-before": "always",
      "declaration-bang-space-after": "never",
      "block-closing-brace-newline-after": [ "always", {
        "ignoreAtRules": ["if", "else", "elseif"]
      } ],
      "block-no-empty": true,
      "block-opening-brace-newline-after": "always",
      "block-opening-brace-space-before": "always",
      "selector-attribute-quotes": "always",
      "selector-combinator-space-after": "always",
      "selector-combinator-space-before": "always",
      "selector-pseudo-class-case": "lower",
      "selector-pseudo-element-case": "lower",
      "selector-pseudo-element-colon-notation": "double",
      "selector-type-case": "lower",
      "selector-max-empty-lines": 0,
      "selector-max-id": 1,
      "selector-max-specificity": ["1,3,3", {
        ignoreSelectors: ["html", "body", ":global", ":local"]
      }],
      "selector-list-comma-newline-after": "always",
      "comment-whitespace-inside": "always",
      "indentation": 2,
      "max-empty-lines": 2,
      "max-nesting-depth": [5, {
        ignore: ["blockless-at-rules", "pseudo-classes"],
        ignoreAtRules: [ "/^if/", "/^else/", "/^media/", "/^include/"]
      }],
      "no-duplicate-selectors": true,
      "no-extra-semicolons": true,
      "order/properties-order": [
        {
          groupName: "Positioning",
          order: arg.lintingOrder || "flexible",
          properties: [
            "position",
            "z-index",
            "top",
            "right",
            "bottom",
            "left"
          ],
        },
        "display",
        {
          groupName: "Box model: behavior",
          order: arg.lintingOrder || "flexible",
          properties: [
            "float",
            "overflow",
            "overflow-x",
            "overflow-y",
            "box-sizing",
            "flex",
            "flex-basis",
            "flex-direction",
            "flex-flow",
            "flex-grow",
            "flex-shrink",
            "flex-wrap",
            "order",
            "align-content",
            "align-items",
            "align-self",
            "justify-content",
            "justify-items",
            "justify-self"
          ],
        },
        {
          groupName: "Box model: dimensions",
          order: arg.lintingOrder || "flexible",
          properties: [
            "height",
            "min-height",
            "max-height",
            "width",
            "min-width",
            "max-width",
            "margin",
            "padding"
          ],
        },
        "border",
        {
          groupName: "Border group 1",
          order: arg.lintingOrder || "flexible",
          properties: [
            "border-width",
            "border-style",
            "border-color",
          ]
        },
        "border-image",
        {
          groupName: "Border group 2",
          order: arg.lintingOrder || "flexible",
          properties: [
            "border-image-source",
            "border-image-slice",
            "border-image-width",
            "border-image-outset",
            "border-image-repeat"
          ]
        },
        {
          groupName: "Border group 3",
          order: arg.lintingOrder || "flexible",
          properties: [
            "border-top",
            "border-right",
            "border-bottom",
            "border-left"
          ]
        },
        {
          groupName: "Border group 4",
          order: arg.lintingOrder || "flexible",
          properties: [
            "border-top-style",
            "border-top-width",
            "border-top-color",
            "border-right-style",
            "border-right-width",
            "border-right-color",
            "border-bottom-style",
            "border-bottom-width",
            "border-bottom-color",
            "border-left-color",
            "border-left-style",
            "border-left-width"
          ],
        },
        "border-radius",
        {
          groupName: "Border group 5",
          order: arg.lintingOrder || "flexible",
          properties: [
            "border-top-left-radius",
            "border-top-right-radius",
            "border-bottom-left-radius",
            "border-bottom-right-radius"
          ]
        },
        "list-style",
        {
          groupName: "List",
          order: arg.lintingOrder || "flexible",
          properties: [
            "list-style-type",
            "list-style-position",
            "list-style-image"
          ],
        },
        "font",
        {
          groupName: "Font",
          order: arg.lintingOrder || "flexible",
          properties: [
            "font-family",
            "font-size",
            "font-weight",
            "line-height",
            "text-align",
            "text-decoration",
            "text-indent",
            "text-shadow",
            "text-rendering",
            "letter-spacing",
            "vertical-align",
            "white-space",
            "word-break",
            "word-spacing",
          ],
        },
        "color",
        "background",
        {
          groupName: "Background group",
          order: arg.lintingOrder || "flexible",
          properties: [
            "background-color",
            "background-image",
            "background-position",
            "background-size",
            "background-repeat",
            "background-attachment",
            "background-clip",
            "background-origin"
          ],
        },
        "box-shadow",
        "transform",
        {
          groupName: "Transform",
          order: arg.lintingOrder || "flexible",
          properties: [
            "transform-origin",
            "transform-style",
            "perspective",
            "perspective-origin",
          ],
        },
        "transition",
        {
          groupName: "Transition",
          order: arg.lintingOrder || "flexible",
          properties: [
            "transition-property",
            "transition-duration",
            "transition-timing-function",
            "transition-delay"
          ],
        },
        "animation",
        {
          groupName: "Animation",
          order: arg.lintingOrder || "flexible",
          properties: [
            "animation-name",
            "animation-duration",
            "animation-timing-function",
            "animation-delay",
            "animation-iteration-count",
            "animation-direction",
            "animation-fill-mode",
            "animation-play-state",
          ],
        }
      ]
    }
  }
};

exports.config = config;
