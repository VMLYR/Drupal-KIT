import React from 'react';

import button from './button.twig';
import buttons from './buttons.twig';
// import buttonsContentBlock from './buttons-field-cb-text.twig';

import buttonsData from './buttons.yml';
import buttonsSizesData from './buttons-sizes.yml';
import buttonsNegativeData from './buttons-negative.yml';

import buttonFieldMultipleData from './buttons-field.yml';
// import buttonFieldContentBlockMultipleData from './buttons-field-cb-text.yml';
import buttonCKEMultipleData from './buttons-cke.yml';

// documentation
import mdx from './buttons.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Buttons',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// Loop over items in buttons data to show each one in the example below.
const buttonsList = buttonsData.map((d) => button(d)).join('');

export const defaultButtons = () => (
  <div dangerouslySetInnerHTML={{ __html: buttonsList }} />
);

const buttonsSizesList = buttonsSizesData.map((d) => button(d)).join('');
export const buttonSizes = () => (
  <div dangerouslySetInnerHTML={{ __html: buttonsSizesList }} />
);

// Loop over items in buttons data to show each one in the example below.
const buttonsNegativeList = buttonsNegativeData.map((d) => button(d)).join('');

export const negativeButtons = () => (
  <div dangerouslySetInnerHTML={{ __html: buttonsNegativeList }} />
);

export const buttonsInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: buttons(buttonCKEMultipleData) }} />
);

export const buttonsAsField = () => (
  <div dangerouslySetInnerHTML={{ __html: buttons(buttonFieldMultipleData) }} />
);

// Not really needed here, as it is in the ContentBlocks component variants as well
//
// export const buttonsInContentBlock = () => (
//   <div dangerouslySetInnerHTML={{ __html: buttonsContentBlock(buttonFieldContentBlockMultipleData) }} />
// );


