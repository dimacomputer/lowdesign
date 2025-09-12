module.exports = {
  plugins: [
    'removeDimensions',
    'removeScriptElement',
    {
      name: 'removeAttrs',
      params: {
        attrs: ['style', 'class', 'id', 'data-*', 'on*'],
      },
    },
    {
      name: 'convertColors',
      params: {
        currentColor: true,
      },
    },
  ],
};
