const fs = require('fs');
var glob = require("glob");

// fetch command line arguments
// https://www.sitepoint.com/pass-parameters-gulp-tasks/
let arg = (argList => {

  let arg = {}, a, opt, thisOpt, curOpt;
  for (a = 0; a < argList.length; a++) {

    thisOpt = argList[a].trim();
    opt = thisOpt.replace(/^\-+/, '');

    if (opt === thisOpt) {

      // argument value
      if (curOpt) arg[curOpt] = opt;
      curOpt = null;

    }
    else {

      // argument name
      curOpt = opt;
      arg[curOpt] = true;

    }

  }

  return arg;

})(process.argv);

const myFiles = arg.location + '/main.*.bundle.js';

glob(myFiles, {nonull:true}, function (er, files) {
  // files is an array of filenames.
  // If the `nonull` option is set, and nothing
  // was found, then files is ["**/*.js"]
  // er is an error object or null.
  if (er !== null) {
    console.log('log error');
    console.log(er);
  }

  // Fix relative paths (as used in the CSS) to load fonts, images and icons
  // in generated Storybook

  for (let i in files) {
    const f = files[i];

    fs.readFile(f, 'utf8', function (err,data) {

      if (err) {
        return console.log(err);
      }

      // find: url(\"../fonts/
      // replace with: url(\"fonts/
      // eg: url(\"../fonts/ -> url(\"fonts/")

      const reg_fonts = /url\(\\"\.\.\/fonts\//g;
      const reg_images = /url\(\\"\.\.\/images\//g;
      const reg_icons = /url\(\\"\.\.\/icons\//g;
      const reg_inline_svg = /images\/generated\/sprite-inline\.svg\#/g;

      var result = data.replace(reg_fonts, 'url(\\"fonts\/');
      result = result.replace(reg_images, 'url(\\"images\/');
      result = result.replace(reg_icons, 'url(\\"icons\/');
      result = result.replace(reg_inline_svg, 'generated\/sprite-inline.svg\#');

      fs.writeFile(f, result, 'utf8', function (err) {
        if (err) return console.log(err);
      });
    });
  }

});
