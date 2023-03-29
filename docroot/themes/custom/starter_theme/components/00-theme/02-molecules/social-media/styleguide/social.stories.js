import React from 'react';

import SocialMediaLinksTwig from './social-media-links.twig';
import SocialMediaShareTwig from './social-media-share.twig';

import SocialMediaLinksDoormatData from './social-media-links--doormat.yml';
import SocialMediaShareData from './social-media-share.yml';

// documentation
import mdx from './social.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Molecules/Social Media',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const socialMediaLinksForDoormat = () => (
  <div dangerouslySetInnerHTML={{ __html: SocialMediaLinksTwig(SocialMediaLinksDoormatData) }} />
);

export const socialMediaShare = () => (
  <div dangerouslySetInnerHTML={{ __html: SocialMediaShareTwig(SocialMediaShareData) }} />
);
