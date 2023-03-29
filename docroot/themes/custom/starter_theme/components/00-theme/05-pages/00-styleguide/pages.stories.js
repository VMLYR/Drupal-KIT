import React from 'react';

// ** Twig templates
import pageTwig from './page.twig';

const merge = require('deepmerge');

// ** import of data

import pageData from './page.yml';

import tabsData from '../../02-molecules/menus/styleguide/tabs.yml';

import headerTopData from '../../03-organisms/header/styleguide/header--top.yml';
import headerPrimaryData from '../../03-organisms/header/styleguide/header--primary.yml';
import languageMenuData from '../../02-molecules/menus/styleguide/menu--language.yml';

import doormatData from '../../03-organisms/footer/styleguide/doormat.yml';
// import menuSitemap from '../../02-molecules/menus/styleguide/menu--sitemap.yml';
import brandingData from '../../02-molecules/branding/styleguide/branding-doormat.yml';
// import socialMediaLinksData from '../../02-molecules/social-media/styleguide/social-media-links--doormat.yml';
// import contactDoormatData from '../../../02-features/f006-office/02-molecules/f006-office-contact/styleguide/contact--doormat.yml';

import footerPrimaryData from '../../03-organisms/footer/styleguide/footer--primary.yml';
// import menuFooterPrimary from '../../02-molecules/menus/styleguide/menu--footer--primary.yml';
import copyrightData from '../../02-molecules/copyright/styleguide/copyright.yml';
import poweredData from '../../02-molecules/powered/styleguide/powered.yml';

// ** drupalSettings

// Reuse drupalSettings that were merged in complete header Organism
drupalSettings = languageMenuData.drupalSettings;


// ** Data imports, like headerPrimaryData
//    contain all the modifications we did to them before (eg. in primary header Organism)
//    eg. navData is still in header_primary
//    so we don't have to import and merge ALL the data from Organisms again
//    we just need to import and nest them in 'pageData' (see below)


// ** Reuse the header data that was previously defined in the complete header Organism

pageData.header_top = headerTopData;
pageData.header_primary = headerPrimaryData;

// ** reuse the footer and doormat data all in pageData

pageData.doormatData = doormatData;
pageData.footerPrimaryData = footerPrimaryData;


// ** Add some extra data for certain page variants (eg. reuse tabs data)
var loggedInPageData = {};
loggedInPageData.tabs = tabsData;


// ** Styling for Storybook only

import './pages.scss';


// ** libraries

import '../../02-molecules/menus/menu/menu--dropdown.js'
import '../../02-molecules/menus/menu--language/menu--language.js'

// ** documentation
import mdx from './pages.mdx';


/**
 * Storybook Definition.
 */
export default {
  title: 'Pages/Basic',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const anonymous = () => (
  <div dangerouslySetInnerHTML={{ __html: pageTwig(pageData) }} />
);

export const loggedIn = () => (
  <div dangerouslySetInnerHTML={{ __html: pageTwig({...pageData, ...loggedInPageData}) }} />
);

