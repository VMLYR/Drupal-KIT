import React from 'react';

import headingsTwig from './headings.twig';
import headingFieldsTwig from './heading-fields.twig';

import headingData from './headings.yml';
import headingFieldsData from './heading-fields.yml';
import headingCKEData from './headings-cke.yml';
import headingCKELinksData from './headings-cke-links.yml';

// documentation
import mdx from './headings.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Headings',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// Loop over items in headingData to show each one in the example below.
const headingsMap = headingData.map((d) => headingsTwig(d)).join('');
export const defaultHeadings = () => (
  <div dangerouslySetInnerHTML={{ __html: headingsMap }} />
);

// Loop over items in headingData to show each one in the example below.
const headingFieldsMap = headingFieldsData.map((d) => headingFieldsTwig(d)).join('');
export const headingFields = () => (
  <div dangerouslySetInnerHTML={{ __html: headingFieldsMap }} />
);

// Loop over items in headingData to show each one in the example below.
const headingsCKEMap = headingCKEData.map((d) => headingsTwig(d)).join('<div class="text-strong"><br /></div>');
export const HeadingsInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: headingsCKEMap }} />
);

// Loop over items in headingData to show each one in the example below.
const headingsCKELinksMap = headingCKELinksData.map((d) => headingsTwig(d)).join('<div class="text-strong"><br /></div>');
export const LinkedHeadingsInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: headingsCKELinksMap }} />
);
