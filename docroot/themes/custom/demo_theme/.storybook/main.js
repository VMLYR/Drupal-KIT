module.exports = {
  core: {
    builder: "webpack5",
  },
  staticDirs: [{ from: '../icons', to: '/icons' }, { from: '../images/generated', to: '/images/generated' }, { from: '../images/source', to: '/images/source' }, { from: '../fonts', to: '/fonts' }],
  "stories": [
    "../components/**/*.stories.mdx",
    "../components/**/*.stories.@(js|jsx|ts|tsx)"
  ],
  "addons": [
    "@storybook/addon-a11y",
    "@storybook/addon-actions",
    "@storybook/addon-links",
    "@storybook/addon-docs",
    "@storybook/addon-viewport",
  ]
}
