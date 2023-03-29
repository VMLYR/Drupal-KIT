import React from 'react';

import twig from './tabbed-item.twig';
import data from './tabbed-item.yml';

// save in a global var for reuse in other components
window.styleguide.components.atoms.tabbed_item = {
  base: JSON.parse(JSON.stringify(data))
};

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Tabbed item'
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: twig(data) }} />
);
