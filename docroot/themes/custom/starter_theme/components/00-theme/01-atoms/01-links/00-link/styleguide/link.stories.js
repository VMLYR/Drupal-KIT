import React from 'react';

import link from './link.twig';
import linkFieldTwig from './link-field.twig';

import linkFieldData from './link-field.yml';
import linkEmptyData from './link-field-empty.yml';
import linkFieldBackData from './link-field-back.yml';
import linkFieldMoreData from './link-field-more.yml';
import linkFieldMoreBigData from './link-field-more-big.yml';
import linkCKEData from './link-cke.yml';

// documentation
import mdx from './link.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Links/Link',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const linkField = () => (
  <div dangerouslySetInnerHTML={{ __html: linkFieldTwig(linkFieldData) }} />
);

export const emptyLinkField = () => (
  <div dangerouslySetInnerHTML={{ __html: linkFieldTwig(linkEmptyData) }} />
);

export const linkBack = () => (
  <div dangerouslySetInnerHTML={{ __html: linkFieldTwig(linkFieldBackData) }} />
);

export const linkMore = () => (
  <div dangerouslySetInnerHTML={{ __html: linkFieldTwig(linkFieldMoreData) }} />
);

export const linkMoreBig = () => (
  <div dangerouslySetInnerHTML={{ __html: linkFieldTwig(linkFieldMoreBigData) }} />
);

export const linkInCke = () => (
  <div dangerouslySetInnerHTML={{ __html: link(linkCKEData) }} />
);
