import React from 'react';

import cookieTwig from '../_cookie-policy-popup.twig';

import cookieData from './cookie.yml';


// documentation
import mdx from './cookie.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Cookie Compliance',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const banner = () => (
  <div dangerouslySetInnerHTML={{ __html: cookieTwig(cookieData) }} />
);
