import React from 'react';

const merge = require('deepmerge');

import sectionVariantsTwig from './section--carousel--variants.twig';

import carouselSettings from './layout-carousel.yml';
import introData from './intro.yml';
import sectionData from './section--carousel.yml';
import sectionVariantsData from './section--carousel--variants-spacing-vert.yml';


// ** Merge Text block variants data, as well as the carousel sections variants

var cbTextData = window.styleguide.components.molecules.text.content_length;
var sectionVariantsTextObject = sectionVariants.getGroup(cbTextData, sectionData, sectionVariantsData);

// ** Merge Image block variants data, as well as the 2-col sections variants

// var cbMediaImageData = window.styleguide.components.molecules.media.image.base;
var cbMediaImageData = window.styleguide.components.molecules.media.image.ratios;
var sectionVariantsImageObject = sectionVariants.getGroup(cbMediaImageData, sectionData, sectionVariantsData);

// ** Merge Media - video block variants data, as well as the carousel sections variants

// var cbMediaVideoData = window.styleguide.components.molecules.media.video.base;
var cbMediaVideoData = window.styleguide.components.molecules.media.video.layouts;
var sectionVariantsVideoObject = sectionVariants.getGroup(cbMediaVideoData, sectionData, sectionVariantsData);

// ** Merge FAQ block variants data, as well as the carousel sections variants

var cbFAQData = window.styleguide.components.molecules.faq.base;
var sectionVariantsFAQObject = sectionVariants.getGroup(cbFAQData, sectionData, sectionVariantsData);

// ** Merge Testimonial block variants data, as well as the carousel sections variants

var cbTestimonalData = window.styleguide.components.molecules.testimonial.variants;
var sectionVariantsTestimonialObject = sectionVariants.getGroup(cbTestimonalData, sectionData, sectionVariantsData);

// ** Merge USP block variants data, as well as the carousel sections variants

var cbUSPData = window.styleguide.components.molecules.usp.base;
var sectionVariantsUSPObject = sectionVariants.getGroup(cbUSPData, sectionData, sectionVariantsData);

// dummy slider data via DrupalSettings
window.drupalSettings.rocketshipUI_layout_carousel = carouselSettings;

// Libraries
import '../../00-section/styleguide/layouts.js';
import '../../../../00-theme/01-atoms/04-images/styleguide/blazy.load.js';
import '../../../../00-theme/01-atoms/04-images/00-image/images.js';
import './layout-carousel.js';

// Extra component styling
import '../../../../00-theme/05-pages/00-styleguide/pages.scss';

// documentation
import mdx from './section--carousel.mdx';


/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Content Sections/Carousel/Vertical spacing',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// ** Section in Carousel

// -- Vert spacing

// Text
export const with001Text = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...{'intro': introData}, ...sectionVariantsTextObject}) }} />
);

// Image
export const with002MediaImage= () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...{'intro': introData}, ...sectionVariantsImageObject}) }} />
);

// video
export const with002MediaVideo = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...{'intro': introData}, ...sectionVariantsVideoObject}) }} />
);

// FAQ
export const with003FAQ = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...{'intro': introData}, ...sectionVariantsFAQObject}) }} />
);

// Testimonial
export const with004Testimonial = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...{'intro': introData}, ...sectionVariantsTestimonialObject}) }} />
);

// USP
export const with005USP = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...{'intro': introData},...sectionVariantsUSPObject}) }} />
);
