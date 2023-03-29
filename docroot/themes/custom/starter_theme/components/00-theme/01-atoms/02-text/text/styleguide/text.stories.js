import React from 'react';

import inlineElements from './inline-elements.twig';
import paragraphs from './paragraphs.twig';
import pre from './pre.twig';
// import hr from './hr.twig';

import blockquote from '../_blockquote.twig';

import blockquoteData from './blockquote.yml';

// documentation
import mdx from './text.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Text',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const inlineElementsExample = () => (
  <div dangerouslySetInnerHTML={{ __html: inlineElements({}) }} />
);

export const paragraphsExample = () => (
  <div dangerouslySetInnerHTML={{ __html: paragraphs({}) }} />
);
export const blockquoteExample = () => (
  <div dangerouslySetInnerHTML={{ __html: blockquote(blockquoteData) }} />
);
export const preExample = () => (
  <div dangerouslySetInnerHTML={{ __html: pre({}) }} />
);
// export const hrExample = () => (
//   <div dangerouslySetInnerHTML={{ __html: hr({}) }} />
// );
