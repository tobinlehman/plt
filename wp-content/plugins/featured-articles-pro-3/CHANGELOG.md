3.1.5.1 [Apr/11/2016]
- Added the possibility to select slider posts by post tags;
- Added the possibility to display sliders on tag pages.

3.1.5 [Feb/3/2016]
- Complete rewrite of CodeFlavors upgrade class;
- More explicit plugin activation errors.
- Solved a script slider bug that was ignoring mouse over navigation when set in slider settings.
- Solved plugin activation errors if Lite version of the plugin is already installed and activated.

3.1.4
- Implemented new actions ( fapro_after_slide_title_field, fapro_slide_options_fields ) that allow implementation of new fields when editing slides;
- Created 2 new templating functions: get_slide_option( $option ) and the_slide_option( $option, $before, $after, $echo_empty, $echo ) that can be used inside the slides loop to display any slide option for the current slide in loop;
- Solves a bug that prevents slide custom image from being displayed if image is found in post content;
- Added new filter "fa_filter_slider_post_slides" that allows manipulation of resulting slides when displaying sliders;
- Updated video modal to close when clicking on the semi transparent background.
- Updated CodeFlavors Upgrade class by adding the corresponding slug and plugin keys to WP transient value.

3.1.3
- Solved slider theme editor bug that wasn't displaying the settings of different slider sections;
- Solved taxonomies table pagination bug;
- Solved taxonomies table search bug;
- Solved bug that prevented displaying more than 5 sliders in slider widget, plugin Dynamic areas and shortcode.

3.1.2
- Plugin compatible with WordPress 4.3 (scheduled for release on August 18th, 2015)
- Modified default slider image size option when using WP sizes from "thumbnail" to "full"
- Modified default slide option "Allow slide editing on post edit" from false to true; this will display the slide edit metabox when editing posts
- Added image autodetection in post content even when displaying sliders in front-end
- Added new option in plugin Settings page to prevent image autodetect when displaying sliders
- Refined image detection regexp to include images having only src attribute
- Allow Docs meta box only on custom post type slide edit screen, not all allowed post types
- Solved an error when creating slider from manually selected posts, if deleting all posts from WP the plugin would issue an error.
- Prevent 'Auto Draft' to be set as slide title when slide editing is set on posts and pages (from plugin Settings page).

3.1.1
- Solved a bug related to slider expiration date that was using server time instead of WP time and was causing 
sliders to expire at the wrong date.

3.1
- Theme Accordion starter script modified to fire on document ready event instead of window load
- Solved a bug in slider script that in some cases was loading wrong image position leaving visible only part of the image
- Introduced a new option for theme Simple that controls if the bar timer should be displayed or not when autoslide is on
- Added new filter "fa-preload-styles" that allows setting of different styling for slider preloaders
- Modified templating function "the_fa_image()" to set class "no-image" on video container if slide doesn't have image but has video attached to it
- Modified slider class to dissalow loading of scripts if slider theme doesn't require them. This can be done by passing the handles used to enqueue scripts: slider, accordion, carousel, jquery-mobile, jquery-transit, round-timer, video-player
- Added a new parameter to templating function "the_fa_video()" that allows displaying of overlay link to trigger video playback
- New option to display slide edit settings on allowed posts edit pages
- Solved a display bug for sliders published into dynamic areas that wasn't displaying correctly the pages where the slider is published in sliders admin table.
- Solved a display bug for sliders published into dynamic areas that wasn't displaying correctly the pages where the slider is published in dynamic areas page.  
- Solved a bug that was displaying sliders if slider created from a post type that used to exist but doesn't at the current time
- Solved a bug that wasn't redirecting correctly and outputted a PHP error when saving plugin settings on some installations
- Added shortcode support for Lite version when upgrading ( FA_Lite shortcode supported )
- YouTube Data API V3 support ( also introduces new option in plugin Settings page for API key )
- New slider theme based on Nivo Slider script
- Solved a bug in slider theme Navobar navigation
- Brand new video embed script
- New option for theme Simple to adjust the height to each slide content while navigating slides
- Solved complex animations bug
- Added new animation effects

3.0.3
- lower z-indexes to maximum 3 in scripts and all themes CSS
- theme List modified to display content correctly
- theme Navobar was modified to display correctly
- solved bug for themes Accordion and Navobar that was looking for images on wrong address

3.0.2
- solved a bug that was removing read more links for posts when a post was edited and saved and an image was automatically detected into the post contents

3.0.1
- removed typo for sliders ordered by comments
- solved bug for theme Simple that didn't displayed timers correctly for multiple slides in page
- added alert on plugins page to not allow users to activate Lite if PRO is installed and active
- added Settings link to plugin row in Plugins page
- solved an error display bug issued by the plugin when retrieving a non-exitent option from plugin options
- added removal notices in Plugins page for previous versions of Featured Articles below 3.X
- updated class FA_Update->delete_options() to remove options: fa_lite_categories, fa_lite_home and fa_lite_pages
- updated class FA_Shortcodes->shortcode_fa_slider() to stop if shortcode runns within a slider to avoid infinite loop
- changed minified starter.js to starter.min.js
- solved bug that was displaying PHP warning when placing sliders into template files by using the PHP code
- added PHP code metabox in slider editing screen