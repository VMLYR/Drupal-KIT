import React from 'react';

import imageTwig from '../00-image/_image.twig';
import blazyTwig from './blazy.twig';
import blazyResponsiveTwig from './blazy-responsive.twig';
import imageResponsiveTwig from './image-responsive.twig';
// import imageResponsive from '../00-image/_image--responsive.twig';
import iconTwig from './icons.twig';
import figureTwig from './figure.twig';
import figuresTwig from './figures.twig';
import imagesTwig from './images.twig';

import iconData from './icons.yml';
import iconSizesData from './icon-sizes.yml';
import imageData from './image.yml';
import blazyData from './blazy.yml';
import blazyResponsiveData from './blazy-responsive.yml';
import blazyResponsiveMultipleData from './blazy-responsive--multiple.yml';
import blazyResponsiveMultipleSameData from './blazy-responsive--multiple-same.yml';
import imageResponsiveData from './image-responsive.yml';
import imageResponsivePictureData from './image-responsive-picture.yml';
import figureData from './figure.yml';
import figuresCKEData from './figures.yml';
import imagesCKEData from './images.yml';


// import library files
// import '../icons/svgxuse.min.js';
import '../00-image/images.js';
import './blazy.load.js';


// save in a global var for reuse in other components
window.styleguide.components.atoms.image = {
  base: JSON.parse(JSON.stringify(imageData)),
  blazy: JSON.parse(JSON.stringify(blazyData)),
  image_responsive: JSON.parse(JSON.stringify(imageResponsiveData)),
  blazy_responsive: JSON.parse(JSON.stringify(blazyResponsiveData)),
  blazy_responsive_multiple: JSON.parse(JSON.stringify(blazyResponsiveMultipleData)),
  blazy_responsive_multiple_same: JSON.parse(JSON.stringify(blazyResponsiveMultipleSameData))
};

// documentation
import mdx from './images.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Images',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const CustomIcons = () => (
  <div dangerouslySetInnerHTML={{ __html: iconTwig({...iconData, ...{'type': 'default'}}) }} />
);

export const CustomIconsSizes = () => (
  <div dangerouslySetInnerHTML={{ __html: iconTwig({...iconData, ...{'type': 'sizes'}, ...iconSizesData}) }} />
);

export const CustomIconsInALink = () => (
  <div dangerouslySetInnerHTML={{ __html: iconTwig({...iconData, ...{'type': 'link'}}) }} />
);

export const Image = () => (
  <div dangerouslySetInnerHTML={{ __html: imageTwig(imageData) }} />
);

export const BlazyImage = () => (
  <div dangerouslySetInnerHTML={{ __html: blazyTwig(blazyData) }} />
);

export const ResponsiveImage = () => (
  <div dangerouslySetInnerHTML={{ __html: imageResponsiveTwig(imageResponsiveData) }} />
);

export const ResponsivePicture = () => (
  <div dangerouslySetInnerHTML={{ __html: imageResponsiveTwig(imageResponsivePictureData) }} />
);

export const BlazyResponsiveImage = () => (
  <div dangerouslySetInnerHTML={{ __html: blazyResponsiveTwig(blazyResponsiveData) }} />
);

export const Figure = () => (
  <div dangerouslySetInnerHTML={{ __html: figureTwig(figureData) }} />
);

export const ImagesInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: imagesTwig(imagesCKEData) }} />
);

export const FiguresInCKE = () => (
  <div dangerouslySetInnerHTML={{ __html: figuresTwig(figuresCKEData) }} />
);
