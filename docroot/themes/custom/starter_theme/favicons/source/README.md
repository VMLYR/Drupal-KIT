Automatic favicon generation

Requirements:
- Images must be a png or svg
- if PNG, it must be at least 512x512 (it must be square)
- Images must be named favicon.svg (or favicon.png if no other option) and favicon-mobile.svg (or .png)

Instructions:
- Add your image(s) to this folder.
  - the mobile favicon is optional. If you don't add it, the icon image on iOS and Android will be the same as the general favicon.
- Replace the `colorPrimary` variable in the gulp favicon task with an hexadecimal value of the website primary color
- Replace the `themeFull` variable in the favicon task with the name of the website, this name will be used when saving a shortcut the site on a mobile device.
- run the gulp task: gulp favicon

## Notes on usage:

Please be advised that the ‘look’ of the icon on mobile devices, is a combination of: <br />
- settings in the favicon Gulp task itself
- styling enforced by the device’s OS
- and only then you have the image itself.

So you might not always need to a separate image, if the only change is background and spacing/size in the icon (or 'tile').
Eg. background-color of the icon and spacing around the icon image itself can be set in the favicon task itself, without needing to make a separate image for mobile (so that saves time & effort). <br />
Just look for the 2 'design' keys, which set all these options per type of favicon. <br />

The rounding corners of an icon with a background-color, is something that’s can be left up to the OS of the device itself (eg. different versions for Android launchers have their own styles for icons, including making shapes and setting shadows. Same for different iOS versions). It might lead to a weird look when trying to enforce that yourself.
