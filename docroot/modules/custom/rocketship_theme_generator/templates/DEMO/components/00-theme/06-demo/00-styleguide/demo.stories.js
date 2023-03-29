import React from 'react';

// ** Twig templates
import pageHomeTwig from './demo--home.twig';

const merge = require('deepmerge');

// ** import of data

import pageData from '../../05-pages/00-styleguide/page.yml';
import homePageData from './demo--home.yml';
import visualHomeData from './demo-block-visual.yml';
import visualBackgroundImage from './demo-block-visual-background-image.yml';
import infoHomeData from './demo-block-info.yml';
import imageHomeData from './demo-block-image.yml';
import blogHomeData from './demo-block-blog.yml';
import ctaHomeData from './demo-block-cta.yml';

import uspData from '../../02-molecules/usp/styleguide/usp.yml';
import uspHomeData from '../../02-molecules/usp/styleguide/usp-home.yml';

// ** Merge generic page data into homepage data

// -- The modified Data imports from in Pages (eg. 'pageData')
//    are still available in their modified state in here, when imported
//    so we don't have to import and merge ALL the data again
//    we just need to import the highers level and merge with our demo data

var myHomePageData = merge(pageData  || {}, homePageData || {});
myHomePageData.page.content.layouts.header.layout_background_image = visualBackgroundImage;

// ** Add blocks data to it

myHomePageData.page.content.blocks.visual = visualHomeData;
myHomePageData.page.content.blocks.info = infoHomeData;
myHomePageData.page.content.blocks.image = imageHomeData;
// merge globally stored Media field (image variant) with the Media field of our block
myHomePageData.page.content.blocks.image.fields.media = window.styleguide.components.atoms.media.image;
myHomePageData.page.content.blocks.usp = merge(uspHomeData  || {}, uspData || {});
myHomePageData.page.content.blocks.blog = blogHomeData;
myHomePageData.page.content.blocks.cta = ctaHomeData;

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
