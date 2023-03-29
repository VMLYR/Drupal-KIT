const fs = require('fs');
const f = 'node_modules/twig/twig.js';

// Fix for issue with Twig: https://github.com/twigjs/twig.js/issues/708
fs.readFile(f, 'utf8', function (err,data) {
  if (err) {
    return console.log(err);
  }
  var result = data.replace(/id: state.template.id,/g, 'id: state.template.id + \'-override\',');

  fs.writeFile(f, result, 'utf8', function (err) {
    if (err) return console.log(err);
  });
});
