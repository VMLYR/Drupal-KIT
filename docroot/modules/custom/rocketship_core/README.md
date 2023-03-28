Rocketship Core
-----

This module contains patches. For these patches to apply, your project
should require [`cweagans/composer-patches`](https://github.com/cweagans/composer-patches).
Read that project's README to set up your project to work with dependency
patching.

One bug that sometimes crops up with dependency patches, is that composer
doesn't pick them up immediately (if, say, a new release has an extra patch).
Either check the composer log or the composer.lock to make sure all patches
are applied properly, or run your update command twice.

## Default Content Default Language
If you install the site using Rocketship Installer, you will see a step
where you can select a language to be used for "default content", meaning
the various migrates that set up demo or default content. This form can also
be accessed at /admin/config/regional/rocketship-default-language.

## Custom Tokens

### Rocketship Menu Parent Alias token:

* Used mostly for node path aliases
* If the node is in a menu, fetches the parent's path alias and prepends it
  to the current node's alias
* If that parent is also a node with the same token and is also in a menu,
  that means its alias already contained their own parent so you can safely
  build a nice alias structure based on the menu.
* Also includes hook_path_update to update all children's aliases if a parent
  changes theirs
* todo: trigger same logic when someone re-orders the menu links

### Paged current page token

* A new token, [current-page:paged-url], is available. It is identical to the
  normal current-page:url token but it adds the page query parameter if present.

### Current page theme path

* A new token, [current-page:theme-path], which returns the path to the current page's active theme.

## Submodules

### Menu Clickthrough
Adds description and image fields to menu items. Combined with the accompanying block content
editors can render part of a menu tree, and the title, description and image will be shown.

### Rocketship Blocks
Contains all of our custom content block types for use with Layout Builder
, as well as some custom block plugins.

### Rocketship Blocks Content
This module contains a set of migrates which will set up a demo page showcasing all of our
custom content block types.

### Rocketship Content
This module contains a set of migrates which will set up the homepage, 404 and 403 page.

### Rocketship Page
This module contains the "Page" content type.

### Rocketship SEO
This module contains our defaults for SEO purposes.

### Rocketship Social Widgets
Provides social sharing links for content entities.

### Rocketship Styleguide
Extends the Styleguide contrib module with Rocketship-specific elements.

## Block Visibility Conditions

#### NodeUUID
Allows a block visibility condition to be set based on the Node UUID of the current node.

## Breakpoints:
* Contains breakpoint definitions used by responsive image styles.

## Search API
* Contains a search api server (database) and index which is used by the Page content type, and ideally each
content type you create.

## Field storage
* Contains field storage definitions for fields used by other submodules.

## Image Styles
* Contains all the basic image styles (based on ratios). These are then used
  to create specific Responsive image styles which are linked to content types
  and view modes and the like. There should be no need to create any more basic
  image styles, if the design allows it of course. Use responsive image styles
  wherever possible!
* Also contains "Preview" image style, which only scales the width. This is
  the image style to use for Focal Point widgets.
* We are also slowly shifting over to the drimage module, which generates image styles
on the fly based on viewport width, etc. We're still waiting on SEO feedback to finalize
this switch.

## Translation information
* We have hidden the language selector when creating content. Instead, we show what language the user is 
creating something in.

## Migrate
### TokenReplacer processor
A processor which will replace any global context tokens. Can be used to replace e.g. tokens in body fields
during import.

## Custom Fields

#### ContentBlockTitleReplacement
This field exposes a checkbox, which when checked, allows you to replace the current
node's title with whatever you fill in in the accompanying textfield. If left unchecked,
the matching formatter will print the node's title. The replacement title supports minimal
HTML in the form of `<em>` and `<strong>`.

#### LabelValueField
Custom field with two inputs (both textfield). Useful when the client wants to create a list of label: values  
For example, dimensions of a package, properties of a building, etc. Has a normal formatter defined, as 
well as a Table formatter. Also contains a "promoted" value. This is used in the Formatters, to only show 
certain values on teasers for example. Access to the checkbox is locked with a permission.  
NOTE: we can't filter on this field, as the label, which signifies what it is, is also defined by the user.  

#### RocketshipDisplayField
Custom computed field that always has a single value. Used to replace the loss
of Display Suite Fields now that we use Layout Builder. You can add this computed
field to your content type and then use or create custom formatters that will support
formatter settings (which is the problem with hook_extra_fields).

#### TabbedItem
Custom field defining a (plaintext) title and (formatted) body. When set to multiple values, 
this field can be used to create tabbed items. Each title will become a tab, and its body the tab
contents.

#### TitleDescriptionField
Custom field containing plaintext title and body.

## Widgets
The above custom fields naturally have their own widgets, but in addition to those we also have:

#### LinkTargetWidget
Extends normal Link widget and exposes an option to set the link target. Should work with any normal
link formatter.

#### TagSelectionTextfieldWidget
This widget, combined with its formatter, can be applied to any `string` field to allow
the content editor to select the tag to wrap the field in.  
NOTE: because this works with an existing field type, the only way to store the data is with the
normal value. It essentially adds the tag to use to the value, delimited by `***`. So always use the
corresponding formatter. And when moving away from this widget/formatter, you'll have to fix the data
in a hook_update.

#### A tweak to Entity Autocomplete Matcher
Not a widget per se, but an alteration to how entity autocomplete works. This change
add the bundle as well to the output (and wraps it all in some HTML for easier theming).

## Formatters
The above custom fields and widgets have their own formatter, but in addition to those we also have:

### Computed Field Formatters:
These are designed to be used with the RocketshipDisplayField custom field.

#### CanonicalLink
* Outputs the entity's canonical URL with formatter specified link text (e.g. "Read more"). 
* Supports various tags to wrap the output in
* Supports custom CSS classes

#### ConfigurableLink
* Provides configurable link which can be placed in any display mode.
* Available configuration options are:
  * link text
  * link URL (with autocomplete support)
  * CSS classes
* Token input is supported for title and URL

#### ScrollToFormatter
* Takes an identifier and some text
* Outputs a link with the identifier as the href (+ # of course)
* Always uses current page, appends #identifier to it

#### TimeAgoFormatter
* Outputs the time an entity was created as "X minutes/hours/etc ago"
* Updates with AJAX, has fallback normal date format.

### Other Formatters
#### AuthorRender
* Field type: boolean
* Output the author when the field value evaluates to true.

#### BreadcrumbRender
* Field type: boolean
* Outputs the breadcrumb if the field value evaluates to true

#### ClassyLinkFormatter
* Field type: link
* Adds option to add extra classes to the output

#### DownloadLinkFileFormatter
* Field type: file
* Extends GenericFileFormatter, adds extra option for a fallback title

#### HeaderTextFieldFormatter
* Field type: string & string_long
* Wraps output in selected wrapper

#### LinkVideoEmbedColorbox
* Field type: video_embed_field
* Alternative to thumbnail link which opens video in colorbox
* This one lets you select a field or fallback text, and use that to build a
  link which will open the video in a popup. Extra fallback; if javascript
  fails it's still a link to the video.

#### MediaGenericFileFormatter
* Field type: file
* Grabs the Media entity the file is attached to and prints that name instead of the raw file name.

#### MultiViewModeEntityReferenceEntityFormatter
* Field type: entity_reference
* Allows you to select what view mode to render the referenced entity in based on its bundle.
* E.g. render 'page' nodes in view mode A and 'blog' nodes in view mode B

#### PostDateRender
* Field type: boolean
* If checked, outputs the created date of the entity.
* Currently format is hardcoded, will be fixed so it's part of the formatter settings

#### RelatedPaddedReferenceItemFormatter
* Field type: entity_reference
* Uses other reference fields from the entity to determine the relationship (still
  needs extra filter to make sure those reference field reference content
  entities, not config entities)
* The conjunction within a single field is currently OR, so if an entity has
  term A and B, entities that have term A OR B will pop up. Plans for AND will
  have to wait, can't be done using EntityQuery, will require a refactor to a
  database query.
* You can select the conjunction *between* the multiple fields, however. If
  you select AND, then there will have to be a match in every field before the
  entity can be used to pad the list
* Naturally the entities that are manually added to the entity reference
  field this formatter is on are excluded, as is the entity itself
* You can set how much it should pad. If you set it to 5, it will add
  entities if needed to reach 5. By default, it will attempt to reach the
  cardinality for the field, unless it is infinite then it won't pad at all
  unless a manual pad limit is set.
* You can select one other field to sort by and set the sort direction
* You can only select this formatter if the entity has at least one other
  reference field that can be used to create a "relationship"
* You can only select this formatter on reference fields which reference the
  same entity and bundle as the entity the field is attached to
* 'Force padding' will pad the list to reach the limit even if there aren't
  enough items with the relationship. If it only finds 2 items that meet the
  criteria, but the limit is 5, it'll grab 3 other items to reach the limit.

TLDR:
If you've got a "related products" reference field, the user can fill in one
or two products and you can set the formatter to add other related products
until the limit is reached. It'll do that using the reference fields selected
to create a relationship.

#### StaticLinkFormatter
* Field type: link
* Allows developer to set text to be used as the link text instead of using
  user defined text
* Don't forget to disable asking for link text in field settings
* Useful if the link text is always the same, such as "Visit this website"

#### WebformRender
* Field type: boolean
* Renders a selected webform when the field value evaluates to true

## Layout Builder

For the basic page content type, Layout Builder is the way to go. And we still use basic pages
with Layout Builder to create overview/landing pages. But now we don't need an intermediate
overview Paragraph, we can simply embed the corresponding views/facets/etc. directly using
Layout Builder.

We've added a fair few additions to the Layout Builder experience after having ran our own
UX feedback trials.

- No sidebar. As soon as a CkEditor field pops up, it becomes unworkable. So we've made sure all
  Layout Builder forms open in a modal instead. Partly thanks to *Layout Builder Modal* module.
- Expanded previews. We've replaced the checkbox that toggled content preview with a dropdown, so
  people can choose to only view the content (true preview), only view the editing UI, or view both.
- We've created several Layouts that work perfectly with our own themes, but should also prove useful
  for anyone wanting a good jumping off point. We'll go over those shortly.
- Blocks export a UUID as well in their config, used to make the migrates work.

We've also expanded the default functionality with contrib modules.

- *Section Library* allows site editors to create reusable layouts and templates
- *Layout Builder Restrictions by Role* allows site developers to restrict what blocks, 
what layouts, and what blocks in what layouts certain roles can use.
- *Layout Builder Lock* allows developers to lock certain sections from change. E.g. define a header 
section that content editors can not place any blocks before, or edit any of the blocks inside of that
section.

##### Layout Builder & Translations
Rocketship adds a patch which allows the *block titles* of blocks added to a layout to be translated.
This block title is essentially an admin-only label, but there can be use-cases where you
want this title visible in the frontend. And for that, you need to be able to translate it.

The patch makes default layout translation possible, HOWEVER, for layout overrides we are currently
using this module:

- [Layout Builder Asymmetric Translation](https://www.drupal.org/project/layout_builder_at)

It allows users to create translations that have different blocks per language.

Field labels and custom (content) blocks can be translated using their corresponding
config translation without the patch or any fancy workarounds. They work much the same
way as if you weren't using Layout Builder at all.

### Layouts

All of our custom layouts support the following:

- Add extra classes to the outermost section wrapper
- Add a BEM modifier
- Change padding at top of layout
- Change padding at bottom of layout
- Select background color for layout
- Select background image for layout
- Select full-width or normal width for the background

We have one, two, three, four and three-col-dynamic layouts. We also have a Carousel Layout,
where the blocks you select will be placed in a carousel.

##### One-Col

The one-col layout has option to enable sub-regions. Because field group does not play nicely
with layout builder (yet?) this was the easiest way to allow you to group fields within a single
layout.

##### Two, Three, Four Col
All multiple column layouts, including Dynamic, have extra options:

- Reverse Layout: If checked, the first column becomes the second column and vice-versa.
  On small screens (eg. phone, where you don't have multiple columns), the first column will
  always remain on top, no matter if this option is checked or not. Use case: if you always
  want an image to be on top, on a phone screen, you would always put the Image block in column 1.
  Then you can use the 'Reverse' option to make the Image show in the second column on normal screens.

- Column sizing: how big each column has to be, eg. 50/50, or 25/75, or 25/50/25, etc.

##### Carousel Layout
- Has extra options to determine how many slides to show at certain breakpoints.
- Whether to autoplay the carousel.
- Vertical alignment options: top, middle, bottom.


## Tweaks

### Custom ConfigInstaller service
All this service decorator does is ignore "Config Exists" exceptions for our own modules. Sometimes
we have to override some existing configuration, and this allows us to do that easily.
