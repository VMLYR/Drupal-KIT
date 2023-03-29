Sources:
- https://www.zachleat.com/web/critical-webfonts/
- https://www.zachleat.com/web/webfont-glossary/#subsetting
- https://github.com/zachleat/web-font-loading-recipes/blob/master/critical-foft-polyfill.html

**Example font-face**
```
@include  font-face((
  font-family: "LatoSubset",
  font-style: normal,
  font-weight: normal,
  file-path: "../fonts/Lato/lato-regular-webfont",
  unicode-range: "U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF" // Latin extended
));

@include  font-face((
  font-family: "Lato",
  font-style: normal,
  font-weight: normal,
  file-path: "../fonts/Lato/lato-regular-webfont",
  unicode-range: "U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF" // Latin extended
));

@include  font-face((
  font-family: "LatoBold",
  font-style: normal,
  font-weight: bold,
  file-path: "../fonts/Lato/lato-bold-webfont",
  unicode-range: "U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF" // Latin extended
));

@include  font-face((
  font-family: "LatoItalic",
  font-style: italic,
  font-weight: normal,
  file-path: "../fonts/Lato/lato-italic-webfont",
  unicode-range: "U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF" // Latin extended
));
```
**Example font-families use**
```
body {
  font-family: "LatoSubset", sans-serif;
}

.font-text01-loaded body {
  font-family: "Lato", sans-serif;
}

strong {
  font-family: "LatoSubset", sans-serif;
  font-weight: bold;
}

.font-text01-loaded strong {
  font-family: "LatoBold", sans-serif;
  font-weight: 700;
}
```

**Example JS (in Twig)**
```
var font-latosubset = new FontFaceObserver('LatoSubset');

Promise.all([font-latosubset.load(null, 10000)]).then(function () {

  document.documentElement.className += " font-latosubset-loaded";

  // step 2: load the full fonts
  var fontA = new FontFaceObserver('Lato');
  var fontB = new FontFaceObserver('LatoBold', {
      weight: 700
    });
  var fontC = new FontFaceObserver('LatoItalic', {
      style: 'italic'
    });
  Promise.all([
    fontA.load(null, 10000),
    fontB.load(null, 10000),
    fontC.load(null, 10000)
  ]).then(function () {
    document.documentElement.className += " fonts-loaded";
    // Optimization for Repeat Views
    sessionStorage.fontsLoadedCriticalFoftPolyfill = true;
  });

});
```
