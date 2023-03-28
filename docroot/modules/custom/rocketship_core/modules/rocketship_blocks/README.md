## Blocks

### Overview page
Has a new overview for Content Blocks, accessible by webadmins. It's a View, 
so change away after installation. This one is placed at admin/content. 
There's also a redirect from the original library at admin/structure because 
that one is still the normal redirect after an edit or translate action. The 
original path is a lot harder to tweak, as it is both a route (backup) and a 
View, and altering both seems a bit much but we may do that in the future.

### Custom blocks

* copyright block
    * Outputs this year and Dropsolid
* legal block, block config has two links one for terms of use and one for 
privacy policy
    * will eventually probably be upgraded/integrated with entity_legal or 
    something more robust
* SearchRedirectBlock:
    * Exposes a simple input with search and reset buttons.
    * Reset is optional
    * Can select where to redirect to. Has support for <current> token.
    * Can select what query parameter the input is attached to (what the user
     entered)
    * Useful for search. Can, for example, redirect to 
    /search?search-key=user-input

### Custom block types

#### FAQ
Create a list of frequently asked questions and answers on a specific page, with a classic FAQ-styling.

#### Focus
Add a block with a striking look-and-feel, with a clear call-to-action to boot. Ideal for content you want to emphasize.

#### Forms
Place one of your forms on your page.

#### Image
Add an image to your section. The image can be stretched to break out of its container (or column) if needed.

#### Menu Overview
Select a menu to show it on the page, or select "current page" to show menu items from the main menu that are below this page.

#### Photo Gallery
Add images to your page in a clean, mobile-friendly way. Choose between a simple grid or a fancy masonry effect.

#### Related Items
Select other pages that are related to this one, and they'll be shown in a visually interesting list.

#### Testimonial
Show a quote of a customer with an optional small profile picture, name and function.

#### Text
Add a block of text with some optional fields, such as title, teaser, …

#### Title
Add the title of your node, in the heading tag of your choice. Include optional fields such as subtitle and teaser.

#### USP
Add blocks of this type to your section, to construct a list of the strong points of your company, product or service.

#### Video
Integrate a YouTube or Vimeo video on your page.