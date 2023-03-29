import React from 'react';

// import library files
import '../forms.js';

import textfieldsTwig from './textfields.twig';
import textfieldContainersTwig from './textfield-containers.twig';
import select from './select.twig';
import checkboxTwig from './_checkbox-item.twig';
import checkboxesTwig from './checkboxes.twig';
import radioTwig from './_radio-item.twig';
import radiosTwig from './radios.twig';
import buttonsTwig from './buttons.twig';

import textfieldsData from './textfields.yml';
import selectOptionsData from './select.yml';
import checkboxData from './checkbox.yml';
import checkboxesData from './checkboxes.yml';
import radioData from './radio.yml';
import radiosData from './radios.yml';
import buttonsData from './buttons.yml';

// import './buttons.yml';


// documentation
import mdx from './forms.mdx';


/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Forms',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const textfields = () => (
  <div dangerouslySetInnerHTML={{ __html: textfieldsTwig(textfieldsData) }} />
);

export const textfieldContainers = () => (
  <div dangerouslySetInnerHTML={{ __html: textfieldContainersTwig(textfieldsData) }} />
);

export const selectDropdowns = () => (
  <div dangerouslySetInnerHTML={{ __html: select(selectOptionsData) }} />
);

export const singleCheckbox = () => (
  <div dangerouslySetInnerHTML={{ __html: checkboxTwig(checkboxData) }} />
);

export const multipleCheckboxes = () => (
  <div dangerouslySetInnerHTML={{ __html: checkboxesTwig(checkboxesData) }} />
);

export const singleRadioButton = () => (
  <div dangerouslySetInnerHTML={{ __html: radioTwig(radioData) }} />
);

export const multipleRadioButtons = () => (
  <div dangerouslySetInnerHTML={{ __html: radiosTwig(radiosData) }} />
);

export const buttons = () => (
  <div dangerouslySetInnerHTML={{ __html: buttonsTwig(buttonsData) }} />
);
