import React from 'react';

import file from './file.twig';

import fileData from './file.yml';
import filePDFData from './file-pdf.yml';
import fileImageData from './file-image.yml';

// documentation
import mdx from './file.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Links/File',
  component: file,
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const defaultFile = () => (
  <div dangerouslySetInnerHTML={{ __html: file(fileData) }} />
);

export const imageFile = () => (
  <div dangerouslySetInnerHTML={{ __html: file(fileImageData) }} />
);


export const pdfFile = () => (
  <div dangerouslySetInnerHTML={{ __html: file(filePDFData) }} />
);
