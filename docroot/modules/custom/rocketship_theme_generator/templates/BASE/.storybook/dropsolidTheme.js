// Documentation on theming Storybook: https://storybook.js.org/docs/configurations/theming/
import { create } from '@storybook/theming/create';
import imageFile from '../logo.svg';

export default create({
  base: 'light',
  brandTitle: 'Dropsolid',
  brandUrl: '.',
  brandImage: imageFile,
});
