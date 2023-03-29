import React from 'react';

// ** Twig templates
import doormatTwig from './doormat.twig';
import footerPrimaryTwig from './footer--primary.twig';

const merge = require('deepmerge');

// ** import of data

import doormatData from './doormat.yml';
import brandingData from '../../../02-molecules/branding/styleguide/branding-doormat.yml';
// import menuSitemap from '../../../02-molecules/menus/styleguide/menu--sitemap.yml';
// import socialMediaLinksData from '../../../02-molecules/social-media/styleguide/social-media-links--doormat.yml';
// import contactDoormatData from '../../../../02-features/f006-office/02-molecules/f006-office-contact/styleguide/contact--doormat.yml';

import footerPrimaryData from './footer--primary.yml';
import copyrightData from '../../../02-molecules/copyright/styleguide/copyright.yml';
import poweredData from '../../../02-molecules/powered/styleguide/powered.yml';
// import menuFooterPrimary from '../../../02-molecules/menus/styleguide/menu--footer--primary.yml';


// ** merging of nested data: for use in doormat

// merge data:  molecule's data to doormat for reuse
doormatData.branding = merge(brandingData, doormatData.branding || {});
// doormatData.menu_sitemap = merge(menuSitemap, doormatData.menu_sitemap || {});
// doormatData.social = merge(socialMediaLinksData, doormatData.social || {});
// doormatData.contact = merge(contactDoormatData, doormatData.contact || {});

footerPrimaryData.copyright = merge(copyrightData, footerPrimaryData.copyright || {});
footerPrimaryData.powered = merge(poweredData, footerPrimaryData.powered || {});
// footerPrimaryData.menu_footer_primary = merge(menuFooterPrimary, footerPrimaryData.menu_footer || {});

// ** documentation
import mdx from './footer.mdx';
// import { type, values } from 'ramda';


/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Footer',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const doormat = () => (
  <div dangerouslySetInnerHTML={{ __html: doormatTwig(doormatData) }} />
);

export const primaryFooter = () => (
  <div dangerouslySetInnerHTML={{ __html: footerPrimaryTwig(footerPrimaryData) }} />
);
