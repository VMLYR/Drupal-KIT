import React from 'react';

import twig from './menu-clickthrough.twig';
import data from './menu-clickthrough.yml';

// save in a global var for reuse in other components
window.styleguide.components.atoms.menu_clickthrough = {
  base: JSON.parse(JSON.stringify(data))
};

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Menu Clickthrough'
};

export const base = () => (
  <div dangerouslySetInnerHTML={{ __html: twig(data) }} />
);
