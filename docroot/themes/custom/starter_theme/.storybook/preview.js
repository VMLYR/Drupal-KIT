import { configure, addDecorator, addParameters } from '@storybook/react';
import { useEffect } from "@storybook/client-api";
// import { withA11y } from '@storybook/addon-a11y';
import '@storybook/addon-console';

import { DocsPage, DocsContainer } from '@storybook/addon-docs';
import '@storybook/addon-docs';

// import { MINIMAL_VIEWPORTS } from '@storybook/addon-viewport';

// Theming
import dropsolidTheme from './dropsolidTheme';

// Viewports

const customViewports = {
  iPhone5: {
    name: 'iPhone 5',
    styles: {
      width: '320px',
      height: '568px',
    },
  },
  smallMobile: {
    name: 'Moto G4',
    styles: {
      width: '360px',
      height: '640px',
    },
  },
  iPhone678: {
    name: 'iPhone 6/7/8',
    styles: {
      width: '375px',
      height: '667px',
    },
  },
  iPhone678_landscape: {
    name: 'iPhone landscape ',
    styles: {
      width: '667px',
      height: '375px',
    },
  },
  iPad: {
    name: 'iPad',
    styles: {
      width: '768px',
      height: '1024px',
    },
  },
  iPadPro: {
    name: 'iPad Pro',
    styles: {
      width: '1024px',
      height: '1366px',
    },
  },
  desktopSmall: {
    name: 'desktop small',
    styles: {
      width: '1024px',
      height: '768px',
    },
  },
  desktopDefault: {
    name: 'desktop default',
    styles: {
      width: '1200px',
      height: '900px',
    },
  },
  wideScreen: {
    name: 'desktop widescreen',
    styles: {
      width: '1440px',
      height: '1200px',
    },
  }
};

addParameters({
  docs: {
    theme: dropsolidTheme,
    container: DocsContainer,
    page: DocsPage,
  },
  a11y: {
    element: '#root',
    config: {},
    options: {},
    manual: false,
  },
  viewport: {
    viewports: customViewports
  },
});


// DRUPAL MODULES/CORE CSS
// form stuff
import '../../../../core/themes/stable/css/user/user.module.css';
import '../../../../core/themes/classy/css/components/file.css'; // for file upload field
// blazy
import '../../../../modules/contrib/blazy/css/blazy.css';
import '../../../../modules/contrib/blazy/css/components/blazy.loading.css';
// responsive video
import '../../../../modules/contrib/video_embed_field/css/video_embed_field.responsive-video.css';


// DRUPAL THEME GLOBAL CSS
import '../components/00-theme/style.scss';
import '../components/00-theme/style.fonts.scss';
// DRUPAL THEME Content Blocks CSS
import '../components/01-content-blocks/style.content-blocks.scss';
// DRUPAL THEME Features CSS
// import '../components/02-features/f001-news/style.news.scss';
// import '../components/02-features/f002-blog/style.blog.scss';
// import '../components/02-features/f006-office/style.office.scss';
// import '../components/02-features/f007-realisation/style.realisation.scss';
// import '../components/02-features/f008-service/style.service.scss';
// import '../components/02-features/f009-product/style.product.scss';
// import '../components/02-features/f012-job/style.job.scss';
// import '../components/02-features/f014-event/style.event.scss';

// Storybook-specific
import "../components/00-theme/storybook.scss";

addDecorator(storyFn => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return storyFn()
});

// addDecorator(withA11y);

const Twig = require('twig');
const twigDrupal = require('twig-drupal-filters');
import bemTwigExtension from '../components/_twig-components/functions/bem.function'
import withoutTwigFilter from '../components/_twig-components/filters/without.filter'
import translateTwigFilter from '../components/_twig-components/filters/translate.filter'
import cleanClassTwigFilter from '../components/_twig-components/filters/cleanclass.filter'
const twigAddAttributes = require('add-attributes-twig-extension');
import addClassTwigExtension from '../components/_twig-components/functions/add_class.function'
import createAttributeTwigExtension from '../components/_twig-components/functions/create_attribute.function'
import iconTwigExtension from '../components/_twig-components/functions/svgy.function'

Twig.cache();

twigDrupal(Twig);
bemTwigExtension(Twig);
withoutTwigFilter(Twig);
translateTwigFilter(Twig);
cleanClassTwigFilter(Twig);
twigAddAttributes(Twig);
addClassTwigExtension(Twig);
createAttributeTwigExtension(Twig);
iconTwigExtension(Twig);

// set a global namespace so we can better share variables and data across components
import './globals.js';

import './section-variants.js';

// If in a Drupal project, it's recommended to import a symlinked version of drupal.js.
import './drupal/drupal.js';
import './drupal/drupalSettingsLoader.js';

// import globally used libraries
import './jquery-global.js';
import 'jquery-once';
import 'jquery-ui';
import 'jquery-form';
import '../js/libs/modernizr.custom.js';

// accompanying styling
// TO DO: have a way to get the js & the css from the same place,
//        either core or node_modules, not a mix of both
import '../../../../core/assets/vendor/jquery.ui/themes/base/theme.css';
import '../../../../modules/contrib/webform/css/webform.element.flexbox.css';
import '../../../../modules/contrib/webform/css/webform.element.file.button.css';
import '../../../../modules/contrib/webform/css/webform.element.datelist.css';
import '../../../../modules/contrib/webform/css/webform.element.details.toggle.css';

// DRUPAL MODULES/CORE JS

// blazy (got rid of it bc it doesn't work properly, replaced with fake loader)
// import './blazy-global.js';
// import './dblazy-global.js';
// import '../../../../core/misc/debounce.js';
// import '../../../../modules/contrib/blazy/js/blazy.load.js';

import 'slick-carousel';
import '../../../../libraries/slick/slick/slick.css';
// import '../../../../libraries/slick/slick/slick.js';

import 'magic-grid';

// DRUPAL THEME GLOBAL JS
import '../components/00-theme/00-base/06-scripts/00-helpers.js';
import '../components/00-theme/00-base/06-scripts/01-base.js';

// DRUPAL THEME COMPONENT JS
// which is loaded globally via theme's .info yml
import '../js/dest/search-block.js';
import '../js/dest/menu--mobile.js';
