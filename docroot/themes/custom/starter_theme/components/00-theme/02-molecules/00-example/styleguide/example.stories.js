import React from 'react';

import exampleBlockTwig from './example-block.twig';

import exampleBlockData from './example-block.yml';
import blazyResponsiveData from '../../../01-atoms/04-images/styleguide/blazy-responsive.yml';
import titleDescriptionData from '../../../01-atoms/title-description/styleguide/title-description.yml';

// Extend data with data of other components
exampleBlockData.fields.image = blazyResponsiveData;
exampleBlockData.fields.title_description = titleDescriptionData;
// Of couse, you can simply add your own data in your block's main data file
// Or even merge 2 datafiles (see Organisms for info on that)


// Sometimes, your block relies on drupalSettings (in Twig or even in a JS library)
// since we don't have access to the Drupal database and the real Settings, you have to pass your own:
//
import exampleSettings from './example-settings.yml';

// We want to make sure we merge our nested settings with already existing drupalSettings (in JS)

const merge = require('deepmerge');
// merge
exampleSettings.drupalSettings = merge(exampleSettings.drupalSettings || {}, drupalSettings || {});
// make sure merged data is in the global variable, for use with component JS files
drupalSettings = exampleSettings.drupalSettings

// add your Drupal libraries here again (since twig's attach_library won't work in Storybook)
// always using a relative path

import '../../../01-atoms/04-images/styleguide/blazy.load.js';
import '../../../01-atoms/04-images/00-image/images.js';
import '../example.js'

// documentation
import mdx from './example.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/00-example',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// normal block, normal data
export const exampleBlock = () => (
  <div dangerouslySetInnerHTML={{ __html: exampleBlockTwig(exampleBlockData) }} />
);

// no data to pass? send an empty object
export const exampleBlockNoData = () => (
  <div dangerouslySetInnerHTML={{ __html: exampleBlockTwig({}) }} />
);

// Pass multiple sets of data:
// eg. want to send our drupalSettings yml, as well as the normal data
// eg. to have a basic generic set of data + some extra data (or data overrides)
export const exampleBlockExtraData = () => (
  <div dangerouslySetInnerHTML={{ __html: exampleBlockTwig({...exampleSettings, ...exampleBlockData}) }} />
);
