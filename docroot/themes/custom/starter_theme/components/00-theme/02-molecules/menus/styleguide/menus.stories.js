import React from 'react';

import tabsTwig from './tabs.twig';

import breadcrumbsTwig from '../breadcrumbs/_breadcrumbs.twig';
import menuTwig from './menu.twig';

import tabsData from './tabs.yml';

import breadcrumbsData from './breadcrumbs.yml';
import inlineMenuData from './inline-menu.yml';
import mainMenuData from './menu--main.yml';
import secondaryMenuData from './menu--secondary.yml';
import accountMenuData from './menu--account.yml';
import languageMenuData from './menu--language.yml';
import sitemapMenuData from './menu--sitemap.yml';

const merge = require('deepmerge');

// need to fill in some drupalSettings, which is read by the imported JS
// to trigger dropdown for language menu
// We want to make sure we merge our nested settings with already existing drupalSettings (in JS)

// merge
languageMenuData.drupalSettings = merge(languageMenuData.drupalSettings || {}, drupalSettings || {});
// make sure merged data is in the global variable, for use with component JS files
drupalSettings = languageMenuData.drupalSettings


// libraries
import '../menu/menu--dropdown.js'
import '../menu--language/menu--language.js'


// documentation
import mdx from './menus.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Menus',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const tabs = () => (
  <div dangerouslySetInnerHTML={{ __html: tabsTwig(tabsData) }} />
);

export const breadcrumbs = () => (
  <div dangerouslySetInnerHTML={{ __html: breadcrumbsTwig(breadcrumbsData) }} />
);

export const inlineMenu = () => (
  <div dangerouslySetInnerHTML={{ __html: menuTwig(inlineMenuData) }} />
);

export const mainMenu = () => (
  <div dangerouslySetInnerHTML={{ __html: menuTwig(mainMenuData) }} />
);

// export const secondaryMenu = () => (
//   <div dangerouslySetInnerHTML={{ __html: menuTwig(secondaryMenuData) }} />
// );

// export const accountMenu = () => (
//   <div dangerouslySetInnerHTML={{ __html: menuTwig(accountMenuData) }} />
// );

export const languageMenu = () => (
  <div dangerouslySetInnerHTML={{ __html: menuTwig(languageMenuData) }} />
);

// export const sitemapMenu = () => (
//   <div dangerouslySetInnerHTML={{ __html: menuTwig(sitemapMenuData) }} />
// );
