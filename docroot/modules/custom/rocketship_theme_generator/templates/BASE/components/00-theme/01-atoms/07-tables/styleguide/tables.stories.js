import React from 'react';

import tableData from './table--default.yml';
import tableStripedData from './table--striped.yml';

// import library files
import '../tables.js';

import tableNoThTwig from './table-no-th.twig';
import tableThHeadTwig from './table-th-head.twig';
import tableThBodyTwig from './table-th-body.twig';
import tableThHeadBodyTwig from './table-th-head-body.twig';

// documentation
import mdx from './tables.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Tables',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const tableWithoutTh = () => (
  <div dangerouslySetInnerHTML={{ __html: tableNoThTwig(tableData) }} />
);

export const tableStriped = () => (
  <div dangerouslySetInnerHTML={{ __html: tableNoThTwig(tableStripedData) }} />
);

export const tableWithThInHead = () => (
  <div dangerouslySetInnerHTML={{ __html: tableThHeadTwig(tableData) }} />
);

export const tableWithThInBody = () => (
  <div dangerouslySetInnerHTML={{ __html: tableThBodyTwig(tableData) }} />
);

export const tableWithThInHeadAndBody = () => (
  <div dangerouslySetInnerHTML={{ __html: tableThHeadBodyTwig(tableData) }} />
);
