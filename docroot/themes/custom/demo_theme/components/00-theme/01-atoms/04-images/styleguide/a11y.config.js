module.exports = {
  storybookBuildDir: 'styleguide',
  pa11y: {
    includeNotices: false,
    includeWarnings: false,
    runners: ['axe'],
  },
  // A11y linting is done on a component-by-component
  // basis, which results in the linter reporting some errors that
  // should be ignored. These codes and descriptions allow for those
  // errors to be targeted specifically.
  ignore: {
    codes: ['landmark-one-main', 'page-has-heading-one'],
    descriptions: ['Ensures all page content is contained by landmarks'],
  },
  // List of storybook component IDs defined and used in this project.
  components: [
    'base-colors--palettes',
    'base-motion--usage',
    'atoms-00-example--react',
    'atoms-00-example--twig',
    'atoms-00-example--twig-primary',
    'atoms-links-file--default-file',
    'atoms-links-file--pdf-file',
    'atoms-links-link--link-field',
    'atoms-links-link--empty-link',
    'atoms-links-link--link-in-cke',
    'atoms-headings--default-headings',
    'atoms-headings--headings-in-cke',
    'atoms-headings--linked-headings-in-cke',
    'atoms-text--inline-elements-example',
    'atoms-text--paragraphs-example',
    'atoms-text--blockquote-example',
    'atoms-text--pre-example',
    'atoms-lists--definition-list',
    'atoms-lists--unordered-list-in-cke',
    'atoms-lists--unordered-list-with-wrapped-items-in-cke',
    'atoms-lists--ordered-list-with-wrapped-items-in-cke',

    'atoms-images--custom-icons',
    'atoms-images--image',
    'atoms-images--responsive-image',
    'atoms-images--figure',
    'atoms-images--images-in-cke',
    'atoms-images--figures-in-cke',

    'atoms-forms--textfields-examples',
    'atoms-forms--single-checkbox',
    'atoms-forms--multiple-checkboxes',
    'atoms-forms--single-radiobutton',
    'atoms-forms--multiple-radiobuttons',
    'atoms-forms--select-dropdowns',
    'atoms-forms--buttons',

    'atoms-tables--table-without-th',
    'atoms-tables--table-with-th-in-head',
    'atoms-tables--table-with-th-in-body',
    'atoms-tables--table-with-th-in-head-and-body',

    'atoms-buttons--default-buttons',
    'atoms-buttons--negative-buttons',
    'atoms-buttons--buttons-in-cke',
    'atoms-buttons--buttons-as-field',
    'atoms-buttons--buttons-in-paragraph',

    'atoms-video--youtube-video',

    'atoms-page-title--page-title',
    'atoms-page-title--page-title-link',

    'molecules-links--list-of-links',
    'molecules-links--list-of-inline-links',
    'molecules-menus--tabs',
    'molecules-menus--breadcrumbs',
    'molecules-menus--inline-menu',
    'molecules-menus--main-menu',
    'molecules-menus--secondary-menu',
    'molecules-menus--account-menu',
    'molecules-menus--language-menu',

    'molecules-pager--full-pager',
    'molecules-pager--pager-with-ellipis-left',
    'molecules-pager--pager-with-ellipis-right',
    'molecules-pager--pager-with-ellipis',
    'molecules-pager--mini-pager',

    'molecules-status--status'
  ],
};
