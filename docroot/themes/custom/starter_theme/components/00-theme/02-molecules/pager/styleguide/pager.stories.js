import React from 'react';

import pagerTwig from '../_pager.twig';
import pagerMiniTwig from '../_mini-pager.twig';

import pagerData from './pager.yml';
import pagerEllipsisLeftData from './pager-prev-ellipses.yml';
import pagerEllipsisRightData from './pager-ellipses.yml';
import pagerEllipsisData from './pager-both-ellipses.yml';
import pagerminiData from './mini-pager.yml';


// documentation
import mdx from './pager.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Pager',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const fullPager = () => (
  <div dangerouslySetInnerHTML={{ __html: pagerTwig(pagerData) }} />
);

export const pagerWithEllipisLeft = () => (
  <div dangerouslySetInnerHTML={{ __html: pagerTwig(pagerEllipsisLeftData) }} />
);

export const pagerWithEllipisRight = () => (
  <div dangerouslySetInnerHTML={{ __html: pagerTwig(pagerEllipsisRightData) }} />
);

export const pagerWithEllipis = () => (
  <div dangerouslySetInnerHTML={{ __html: pagerTwig(pagerEllipsisData) }} />
);

export const miniPager = () => (
  <div dangerouslySetInnerHTML={{ __html: pagerMiniTwig(pagerminiData) }} />
);
