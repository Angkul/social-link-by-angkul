=== Social Link by Angkul ===
Contributors: angkul
Tags: social, social icon, floating button, chat button
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.0.7
Requires PHP: 7.4
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A floating action button (FAB) that shows expandable social and contact links on any WordPress site.

== Description ==

Social Link by Angkul adds a customizable floating action button to your website. Visitors can click it to reveal your social media and contact links — or display them always visible in Direct mode.

**Features**

* FAB mode (click to expand) or Direct mode (always visible)
* Supports Phone, WhatsApp, LINE, Facebook, Messenger, Telegram, Instagram, TikTok, X (Twitter), Email, Map, Calendar, Discord, WeChat, YouTube, LinkedIn, VK, and Link
* Per-item background color (brand color, custom, or gradient)
* Show/hide each item on Desktop or Mobile independently
* Up to 8 menu items, drag to reorder
* Position: left or right, adjustable bottom offset
* Adjustable border radius for the main button
* Smooth entrance animations
* Compatible with Polylang and WPML

== Installation ==

1. Upload the `social-link-by-angkul` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings → Social Link** to configure your button and social links

== Frequently Asked Questions ==

= How do I add social links? =
Go to Settings → Social Link, scroll to the Social Links section, click "Add Item", choose an icon, enter a label and URL, then save.

= Can I show different links on mobile and desktop? =
Yes — each item has a toggle for Desktop and Mobile visibility independently.

= Does it work with page builders like Elementor? =
Yes. The plugin uses high-specificity CSS to prevent Elementor from overriding the button styles.

= Can I change the button color? =
Yes. You can set a two-color gradient for the main button in Settings → Social Link → Button Gradient.

= Does it support multilingual sites? =
Yes. The plugin integrates with Polylang and WPML for translating the button title, subtitle, and item labels.

= What is Direct mode? =
In Direct mode, all social links are always visible on the page without needing to click the FAB button. Useful for sidebars or fixed sections.

== Screenshots ==

1. Main FAB button in collapsed state
2. FAB button expanded showing social links
3. Admin settings panel

== Changelog ==

= 1.0.7 =
* Add show/hide per device (Desktop/Mobile) for the Main Button
* Main button now expands to match the menu width (capped at 280px)
* Smoother, snappier hover response on menu items
* Softer, more premium shadows on the button and menu items
* Fix hover transition stutter caused by stagger delays
* Fix stale width measurement before webfonts finished loading
* Entrance stagger now covers all 8 menu items
* Use file modification time for cache busting of CSS/JS

= 1.0.6 =
* Update plugin icon and banner images

= 1.0.5 =
* Update plugin icon and banner images

= 1.0.4 =
* Add plugin icon and banner to the update screen
* Add FAQ, screenshots, and full changelog to readme

= 1.0.3 =
* Fix text padding on expanded FAB button

= 1.0.2 =
* Fix truncated plugin files causing PHP errors
* Add border-radius setting for main button
* Fix CSS specificity conflict with Elementor kit styles
* Add btn_radius to activation defaults

= 1.0.1 =
* Fix SVG icons not displaying (wp_kses_post was lowercasing viewBox attribute)
* Add multilingual support for Polylang and WPML
* Add Settings link on Plugins page
* Add GitHub-based auto-update via Plugin Update Checker

= 1.0.0 =
* Initial release
