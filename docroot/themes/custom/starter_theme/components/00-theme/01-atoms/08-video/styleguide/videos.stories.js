import React from 'react';

import videoTwig from './video.twig';
import videoFieldTwig from './video--field.twig';

import videoData from './video.yml';
import videoFieldData from './video--field.yml';

// documentation
import mdx from './videos.mdx';

// merge data: first level
const videoFieldDataMerged = { ...videoFieldData, ...videoData};

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Video',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};


export const YoutubeVideo = () => (
  <div dangerouslySetInnerHTML={{ __html: videoTwig(videoData) }} />
);

export const videoField = () => (
  <div dangerouslySetInnerHTML={{ __html: videoFieldTwig(videoFieldDataMerged) }} />
);
