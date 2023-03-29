import React from 'react';

import ul from '../list--ul/_ul.twig';
import ol from '../list--ol/_ol.twig';
import dl from '../list--dl/_dl.twig';

import ulData from './ul.yml';
import olData from './ol.yml';
import dlData from './dl.yml';

import ulCKEData from './ul-cke.yml';
import olCKEData from './ol-cke.yml';
import dlCKEData from './dl-cke.yml';

import ulCKEWrappedData from './ul-cke-wrapped.yml';
import olCKEWrappedData from './ol-cke-wrapped.yml';

// documentation
import mdx from './lists.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Lists',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// ** the only lists we care about are in CKE, so don't need the 'default' ones in Styleguide
//
// export const unorderedList = () => (
//   <div dangerouslySetInnerHTML={{ __html: ul(ulData) }} />
// );
// export const orderedList = () => (
//   <div dangerouslySetInnerHTML={{ __html: ol(olData) }} />
// );
export const definitionList = () => (
  <div dangerouslySetInnerHTML={{ __html: dl(dlData) }} />
);

export const unorderedListInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: ul(ulCKEData) }} />
);
export const orderedListInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: ol(olCKEData) }} />
);
// export const definitionListInCKE = () => (
//   <div dangerouslySetInnerHTML={{ __html: dl(dlCKEData) }} />
// );

export const unorderedListWithWrappedItemsInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: ul(ulCKEWrappedData) }} />
);
export const orderedListWithWrappedItemsInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: ol(olCKEWrappedData) }} />
);
