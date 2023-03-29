import React from 'react';

// ** Twig templates
import navTwig from './nav.twig';
import headerPrimaryTwig from './header--primary.twig';
import headerTopTwig from './header--top.twig';
import headerCompleteTwig from './header.twig';

const merge = require('deepmerge');

// ** import of data

import headerTopData from './header--top.yml';
import headerPrimaryData from './header--primary.yml';
import navData from './nav.yml';
import menuMainData from '../../../02-molecules/menus/styleguide/menu--main.yml';
import menuSecondaryData from '../../../02-molecules/menus/styleguide/menu--secondary.yml';
import languageMenuData from '../../../02-molecules/menus/styleguide/menu--language.yml';
import brandingData from '../../../02-molecules/branding/styleguide/branding-header.yml';
import socialMediaLinksData from '../../../02-molecules/social-media/styleguide/social-media-links--doormat.yml';


// ** drupalSettings

// need to fill in some drupalSettings, which is read by the imported JS
// to trigger dropdown menu
// We want to make sure we merge our nested settings with already existing drupalSettings (in JS)

// merge
languageMenuData.drupalSettings = merge(languageMenuData.drupalSettings || {}, drupalSettings || {});
// make sure merged data is in the global variable, for use with component JS files
drupalSettings = languageMenuData.drupalSettings


// ** merging of nested data: for use in nav

// attach menu: main menu molecule's data to nav for reuse
navData.menu_main = merge(menuMainData, navData.menu_main || {});

// attach menu: secondary menu molecule's data to nav for reuse
navData.menu_secondary = merge(menuSecondaryData, navData.menu_secondary || {});

// attach menu: language molecule's data to nav for reuse
navData.menu_language = merge(languageMenuData, navData.menu_language || {});

// attach block example: social links molecule's data to nav for reuse
navData.social = merge(socialMediaLinksData, navData.social || {});

// ** merging of nested data: for use in header primary

// attach branding molecule's brandingData to headerPrimaryData for reuse
headerPrimaryData.branding = merge(brandingData, headerPrimaryData.branding || {});

// attach navData to headerPrimaryData for reuse
headerPrimaryData.nav = merge(navData, headerPrimaryData.nav || {});

// ** merging of nested data: for use in header top

// attach menu: language menu molecule's data to headerTop for reuse
headerTopData.menu_language = merge(languageMenuData, headerTopData.menu_language || {});
// attach block: social block molecule's data to headerTop for reuse
headerTopData.social = merge(socialMediaLinksData, headerTopData.social || {});

// ** merging of nested data: for use in complete header
var headerCompleteData = {};
headerCompleteData.header_top = merge(headerTopData, headerCompleteData.header_top || {});
headerCompleteData.header_primary = merge(headerPrimaryData, headerCompleteData.header_primary || {});


// ** libraries

import '../../../02-molecules/menus/menu/menu--dropdown.js'
import '../../../02-molecules/menus/menu--language/menu--language.js'

// ** documentation
import mdx from './header.mdx';
// import { type, values } from 'ramda';


/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Header',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// export const topHeader = () => (
//   <div dangerouslySetInnerHTML={{ __html: headerTopTwig(headerTopData) }} />
// );

// export const mainNavigation = () => (
//   <div dangerouslySetInnerHTML={{ __html: navTwig(navData) }} />
// );

// export const primaryHeader = () => (
//   <div dangerouslySetInnerHTML={{ __html: headerPrimaryTwig(headerPrimaryData) }} />
// );

export const completeHeader = () => (
  <div dangerouslySetInnerHTML={{ __html: headerCompleteTwig(headerCompleteData) }} />
);
