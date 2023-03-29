import React from 'react';

// ** Twig templates
import pageHomeTwig from './demo--home.twig';

const merge = require('deepmerge');

// ** import of data

import pageData from '../../05-pages/00-styleguide/page.yml';
import homePageData from './demo--home.yml';

// ** import rest of blocks and layouts data here, if you have them in separate files

// ** Examples:
// -- Layout data:
// import visualBackgroundImage from './demo-block-visual-background-image.yml';
// -- Block data:
// import imageData from './demo-block-image.yml';
// import visualData from '../../02-molecules/visual/styleguide/visual.yml';
// import visualHomeData from '../../02-molecules/visual/styleguide/visual-home.yml';


// ** Merge generic page data into homepage data

// -- The modified Data imports from in Pages (eg. 'pageData')
//    are still available in their modified state in here, when imported
//    so we don't have to import and merge ALL the data again
//    we just need to import the highers level and merge with our demo data

var myHomePageData = merge(pageData  || {}, homePageData || {});

// ** Merge separate blocks & layouts data into it

// ** Example:
// -- for extending a layout, eg. in include background-image
// myHomePageData.page.content.layouts.header.layout_background_image = visualBackgroundImage;

// ** Example:
// -- extending a block by merging 2 data files into it
// myHomePageData.page.content.blocks.visual = merge(visualHomeData  || {}, visualData || {});
// -- extending a block by merging 1 data file into it
//    + extending the media field by merging the globally stored Media Atom component's data into it
// myHomePageData.page.content.blocks.image = imageHomeData;
// myHomePageData.page.content.blocks.image.fields.media = window.styleguide.components.atoms.media.image;


// ** Styling for Storybook only

import '../../05-pages/00-styleguide/pages.scss';


// ** libraries

import '../../02-molecules/menus/menu/menu--dropdown.js'
import '../../02-molecules/menus/menu--language/menu--language.js'


// ** documentation
import mdx from './demo.mdx';


/**
 * Storybook Definition.
 */
export default {
  title: 'Demo/Pages',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const Homepage = () => (
  <div dangerouslySetInnerHTML={{ __html: pageHomeTwig(myHomePageData) }} />
);
