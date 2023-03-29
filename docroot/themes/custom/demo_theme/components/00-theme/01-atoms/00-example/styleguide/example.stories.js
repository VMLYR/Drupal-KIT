import React from 'react';

// import react component file
import Example from '../react/Example.component';

// import Styleguide component's twig file
import exampleTwig from './example.twig';

// import data
import exampleData from './example.yml';
import exampleAltData from './example-alt.yml';

// documentation
import mdx from './example.mdx';

/**
 * Storybook Definition.
 */
export default {
  title: 'Atoms/00-example',
  component: Example,
  parameters: { docs: { page: mdx } }, // needed to load an mdx file for documentation: componentName.mdx
};

export const react = () => <Example>React Example</Example>;
react.parameters = { foo: 'bar' };

export const twig = () => (
  <div dangerouslySetInnerHTML={{ __html: exampleTwig(exampleData) }} />
);
export const twigPrimary = () => (
  <div dangerouslySetInnerHTML={{ __html: exampleTwig(exampleAltData) }} />
);
