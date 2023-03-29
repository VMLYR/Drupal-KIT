import React from 'react';

import heading from './page-title.twig';

import headingPageData from './page-title.yml';
import headingPageLinkData from './page-title-link.yml';

// documentation
import mdx from './page-title.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Page title',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const pageTitle = () => (
  <div dangerouslySetInnerHTML={{ __html: heading(headingPageData) }} />
);
export const pageTitleLink = () => (
  <div dangerouslySetInnerHTML={{ __html: heading(headingPageLinkData) }} />
);

