import React from 'react';

import motion from './motion.twig';

import motionData from './motion.yml';

// documentation
import mdx from './motion.mdx';

/**
 * Add storybook definition for Animations.
 */
export default {
  title: 'Base/Motion',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const Usage = () => (
  <div
    dangerouslySetInnerHTML={{
      __html: motion(motionData),
    }}
  />
);
