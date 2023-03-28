#Rocketship SEO

Many thanks to Varbase for the setup for this module. We would simply use that
one, but our flow doesn't support SEO admin roles or Yoast.

Provide SEO Core features and settings.

Contains defaults and base fields used by other features, with the purpose of
 making all our content types as SEO-optimized as possible out of the box.

Contains:
- Metatag base field for nodes
- Description field for nodes
- Canonical image field for nodes
- Social Large and Social Small image style
- Google Analytics settings
- Metatag defaults (including defaults for Metatag Schema)
- pathauto settings
- redirect settings
- rdf mapping for User

### Extra third party settings
Select which fields to place in the sidebar in the node edit form. They'll be
 placed under a Details fieldset, with explanations that the fields in this 
 section can/are visible on overviews (can also be changed). These settings 
 can be found in Content Type edit form.
 
### 'next' and 'prev' links added to header of HTML
For all Views that use the standard Pager or Mini Pager. Note that Mini Pager
is currently broken for Search API Views.

Google likes this, though it will naturally only work if you only have one pager
per page. If you've got two views on the same page and they both use pagers
only one will "win" and get added to the header.

### Exclude homepage from simple sitemap!
If you don't, it'll get indexed using its alias instead of /.
So, exclude that node and set up a "custom link" for your sitemap of 
simple "/" so the frontpage gets indexed properly.  
This should happen automatically if you enable rocketship_content.

### Unsupported modules
Yoast SEO (Real-time SEO) isn't supported at the moment because only the 2.x 
branch has integration with paragraphs, and that one may contain breaking 
changes with each new update (still in alpha and maintainer explicitly said 
so).

So until then, no Yoast

### Metatags Content
Metatags are set up for generic content types. This presumes each content type has the field_description
and field_media_canonical_image fields added to it.

Changes can of course be made to the default metatags on a per bundle basis, but these are some sane defaults:

- description
- abstract
- shortlink
- canonical
- title
- hreflang_xdefault
- opengraph:
  - image
  - description
  - type
  - url
- twitter:
  - url
  - description
  - image
  - type
- schema:
  - image
  - description
  - headline
  - created date
  - author (important, create your content with a proper user!)
  - publisher
  - changed

The global metatags, which will be used on non-content pages, rely on a share-image.png being present in 
the themes which represents the site as a whole.
