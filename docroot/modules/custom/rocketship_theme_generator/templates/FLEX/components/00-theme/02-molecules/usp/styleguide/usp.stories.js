import React from 'react';

import uspTwig from './usp.twig';

import uspData from './usp.yml';
import uspHomeData from './usp-home.yml';

// documentation
import mdx from './usp.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/USP',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// export const socialMediaLinks = () => (
//   <div dangerouslySetInnerHTML={{ __html: SocialMediaLinksTwig(SocialMediaLinksData) }} />
// );

export const uspHome = () => (
  <div dangerouslySetInnerHTML={{ __html: uspTwig({...uspData, ...uspHomeData}) }} />
);
