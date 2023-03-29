import React from 'react';

import brandingSiteTwig from './branding-site.twig';
import brandingBlockTwig from './branding-block.twig';

import brandingHeaderData from './branding-header.yml';
import brandingDoormatData from './branding-doormat.yml';

// import libaries
import '../../../01-atoms/04-images/styleguide/blazy.load.js';
import '../../../01-atoms/04-images/00-image/images.js';


// documentation
import mdx from './branding.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Branding',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const brandingInHeader = () => (
  <div dangerouslySetInnerHTML={{ __html: brandingSiteTwig(brandingHeaderData) }} />
);

export const brandingInDoormat = () => (
  <div dangerouslySetInnerHTML={{ __html: brandingBlockTwig(brandingDoormatData) }} />
);
