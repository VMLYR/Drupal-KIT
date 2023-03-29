import React from 'react';

// import libary files
import '../status.js';

import statusTwig from '../_status.twig';

import statusData from './status.yml';


// documentation
import mdx from './status.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Status',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const status = () => (
  <div dangerouslySetInnerHTML={{ __html: statusTwig(statusData) }} />
);
