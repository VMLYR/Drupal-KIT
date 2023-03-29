import React from 'react';

const merge = require('deepmerge');

// import section2ColTwig from './section--2-col.twig';
import sectionVariantsTwig from './section--2-col--variants.twig';

import sectionData from './section--2-col.yml';
import sectionVariantsData from './section--2-col--variants-columns.yml';


// ** Merge Text block variants data, as well as the sections variants

var cbTextData = window.styleguide.components.molecules.text.alignments;
var sectionVariantsTextObject = sectionVariants.get(cbTextData, 'cb_text', sectionData, sectionVariantsData);

// ** Merge Image block variants data, as well as the sections variants

// var cbMediaImageData = window.styleguide.components.molecules.media.image.base;
var cbMediaImageData = window.styleguide.components.molecules.media.image.layouts;
var sectionVariantsImageObject = sectionVariants.get(cbMediaImageData, 'cb_image', sectionData, sectionVariantsData);

// ** Merge Media - video block variants data, as well as the sections variants

// var cbMediaVideoData = window.styleguide.components.molecules.media.video.base;
var cbMediaVideoData = window.styleguide.components.molecules.media.video.layouts;
var sectionVariantsVideoObject = sectionVariants.get(cbMediaVideoData, 'cb_video', sectionData, sectionVariantsData);

// ** Merge FAQ block variants data, as well as the sections variants

var cbFAQData = window.styleguide.components.molecules.faq.base;
var sectionVariantsFAQObject = sectionVariants.get(cbFAQData, 'cb_faq', sectionData, sectionVariantsData);

// ** Merge Testimonial block variants data, as well as the sections variants

var cbTestimonalData = window.styleguide.components.molecules.testimonial.variants;
var sectionVariantsTestimonialObject = sectionVariants.get(cbTestimonalData, 'cb_testimonial', sectionData, sectionVariantsData);

// ** Merge USP block variants data, as well as the sections variants

var cbUSPData = window.styleguide.components.molecules.usp.base;
var sectionVariantsUSPObject = sectionVariants.get(cbUSPData, 'cb_usp', sectionData, sectionVariantsData);

// ** Merge Focus block variants data, as well as the sections variants

var cbFocusData = window.styleguide.components.molecules.focus.base;
var sectionVariantsFocusObject = sectionVariants.get(cbFocusData, 'cb_focus', sectionData, sectionVariantsData);

// ** Merge Photo Gallery block variants data, as well as the sections variants

var cbPhotoGalleryData = window.styleguide.components.molecules.photo_gallery.all;
var sectionVariantsPhotoGalleryObject = sectionVariants.get(cbPhotoGalleryData, 'cb_photo_gallery', sectionData, sectionVariantsData);

// ** Merge Form block variants data, as well as the sections variants

var cbFormData = window.styleguide.components.molecules.form.base;
var sectionVariantsFormObject = sectionVariants.get(cbFormData, 'cb_form', sectionData, sectionVariantsData);

// ** Merge Menu Overview block variants data, as well as the sections variants

var cbMenuOverviewData = window.styleguide.components.molecules.menu_overview.base;
var sectionVariantsMenuOverviewObject = sectionVariants.get(cbMenuOverviewData, 'cb_menu_overview', sectionData, sectionVariantsData);

// ** Merge Related Items block variants data, as well as the sections variants

var cbRelatedItemsData = window.styleguide.components.molecules.related_items.base;
var sectionVariantsRelatedItemsObject = sectionVariants.get(cbRelatedItemsData, 'cb_related_items', sectionData, sectionVariantsData);


// Libraries
import '../../00-section/styleguide/layouts.js';
import '../../../../00-theme/01-atoms/04-images/styleguide/blazy.load.js';
import '../../../../00-theme/01-atoms/04-images/00-image/images.js';
import '../../../02-molecules/007-photo-gallery/styleguide/photo-gallery.js';

// Extra component styling
import '../../../../00-theme/05-pages/00-styleguide/pages.scss';

// documentation
import mdx from './section--2-col.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Content Sections/2 columns/Colum size',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

// ** Section in 2 columns

// -- Cols size

// Text + other
export const with001Text = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsTextObject, ...{'block_type': 'cb_text'}}) }} />
);

// Image + other
export const with002MediaImage = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsImageObject, ...{'block_type': 'cb_image'}}) }} />
);

// Video + other
export const with002MediaVideo = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsVideoObject, ...{'block_type': 'cb_video'}}) }} />
);

// FAQ
export const with003FAQ = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsFAQObject, ...{'block_type': 'cb_faq'}}) }} />
);

// Testimonial
export const with004Testimonial = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsTestimonialObject, ...{'block_type': 'cb_testimonial'}}) }} />
);

// USP
export const with005USP = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsUSPObject, ...{'block_type': 'cb_usp'}}) }} />
);

// Focus
export const with006Focus = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsFocusObject, ...{'block_type': 'cb_focus'}}) }} />
);

// Photo Gallery
export const with007PhotoGallery = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsPhotoGalleryObject, ...{'block_type': 'cb_photo_gallery'}}) }} />
);

// Form
export const with008Form = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsFormObject, ...{'block_type': 'cb_form'}}) }} />
);

// Menu Overview
export const with009MenuOverview = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsMenuOverviewObject, ...{'block_type': 'cb_menu_overview'}}) }} />
);

// Related Items
export const with010RelatedItems = () => (
  <div dangerouslySetInnerHTML={{ __html: sectionVariantsTwig({...sectionVariantsRelatedItemsObject, ...{'block_type': 'cb_related_items'}}) }} />
);
