import React from 'react';

// import Styleguide component's twig file
import addressTwig from './address.twig';
import emailTwig from './email.twig';
import phoneTwig from './phone.twig';

// import data
import addressData from './address.yml';
import emailData from './email.yml';
import phoneData from './phone.yml';

// documentation
import mdx from './contact.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/Contact',
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const address = () => (
  <div dangerouslySetInnerHTML={{ __html: addressTwig(addressData) }} />
);

export const email = () => (
  <div dangerouslySetInnerHTML={{ __html: emailTwig(emailData) }} />
);

export const telephone = () => (
  <div dangerouslySetInnerHTML={{ __html: phoneTwig(phoneData) }} />
);
