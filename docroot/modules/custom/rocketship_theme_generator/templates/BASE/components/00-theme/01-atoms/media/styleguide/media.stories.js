import React from 'react';

import mediafieldTwig from './media--field.twig';
// import mediaVideoTwig from './media--video.twig';
// import mediaImageTwig from './media--image.twig';

import mediaFieldData from './media--field.yml';
import mediaFieldMultipleData from './media--field--multiple.yml';

import mediaVideoData from './media--video.yml';
import videoData from '../../08-video/styleguide/video.yml';
import videoFieldData from '../../08-video/styleguide/video--field.yml';

import mediaImageData from './media--image.yml';
import imageFieldData from '../../04-images/styleguide/blazy-responsive.yml';
import imageFieldMultipleData from '../../04-images/styleguide/blazy-responsive--multiple.yml';
import imageFieldMultipleSameData from '../../04-images/styleguide/blazy-responsive--multiple-same.yml';

// documentation
import mdx from './media.mdx';

// merge video stuff for video field
var videoFieldDataMerged = { ...videoFieldData, ...videoData};

// merge into media field
var mediaFieldImageData = JSON.parse(JSON.stringify(mediaFieldData));
mediaFieldImageData.type = 'image';
mediaFieldImageData.reference = JSON.parse(JSON.stringify(mediaImageData));
mediaFieldImageData.reference.field = JSON.parse(JSON.stringify(imageFieldData));

// merge multiple images into media field
var mediaFieldImagesMultipleData = JSON.parse(JSON.stringify(mediaFieldMultipleData));
mediaFieldImagesMultipleData.type = 'image';
for (var key in imageFieldMultipleData) {
  var imageField = imageFieldMultipleData[key];
  var reference = JSON.parse(JSON.stringify(mediaImageData));
  reference.field = JSON.parse(JSON.stringify(imageField));

  mediaFieldImagesMultipleData.items = mediaFieldImagesMultipleData.items || [];
  mediaFieldImagesMultipleData.items.push({
    'reference': reference
  });
}

// merge multiple images, in same ratio, into media field
var mediaFieldImagesMultipleSameData = JSON.parse(JSON.stringify(mediaFieldMultipleData));
mediaFieldImagesMultipleSameData.type = 'image';
for (var key in imageFieldMultipleSameData) {
  var imageField = imageFieldMultipleSameData[key];
  var reference = JSON.parse(JSON.stringify(mediaImageData));
  reference.field = JSON.parse(JSON.stringify(imageField));

  mediaFieldImagesMultipleSameData.items = mediaFieldImagesMultipleSameData.items || [];
  mediaFieldImagesMultipleSameData.items.push({
    'reference': reference
  });
}

var mediaFieldVideoData = JSON.parse(JSON.stringify(mediaFieldData));
mediaFieldVideoData.type = 'video';
mediaFieldVideoData.reference = mediaVideoData;
mediaFieldVideoData.reference.field = videoFieldDataMerged;

// save in a global var for reuse in other components
window.styleguide.components.atoms.media = {
  image: JSON.parse(JSON.stringify(mediaFieldImageData)),
  images_multiple: JSON.parse(JSON.stringify(mediaFieldImagesMultipleData)),
  images_multiple_same: JSON.parse(JSON.stringify(mediaFieldImagesMultipleSameData)),
  video: JSON.parse(JSON.stringify(mediaFieldVideoData))
};

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Media',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const MediaImage = () => (
  <div dangerouslySetInnerHTML={{ __html: mediafieldTwig(mediaFieldImageData) }} />
);

export const MediaMultipleImages = () => (
  <div dangerouslySetInnerHTML={{ __html: mediafieldTwig(mediaFieldImagesMultipleData) }} />
);

export const MediaVideo = () => (
  <div dangerouslySetInnerHTML={{ __html: mediafieldTwig(mediaFieldVideoData) }} />
);

