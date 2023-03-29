import React from 'react';

import links from '../_links.twig';

import linksData from './links.yml';
import linksInlineData from './links-inline.yml';

// documentation
import mdx from './links.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Links',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const listOfLinks = () => (
  <div dangerouslySetInnerHTML={{ __html: links(linksData) }} />
);

export const listOfInlineLinks = () => (
  <div dangerouslySetInnerHTML={{ __html: links(linksInlineData) }} />
);
