=== Popup Modal ===
Contributors: teckel
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=99J6Y4WCHCUN4&lc=US&item_name=Popup%20Modal&item_number=Popup%20Modal%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: popup, modal, lightbox, coupon, print, simple, easy, flexible, dialog, WYSIWYG, box, multiple, maker, pop-up, popups, modals, box, form, window, printing, simply, click, frequency, scroll, scrolling, color, background, plugin, wp, wordpress, expires
Requires at least: 3.5
Tested up to: 6.2
Stable tag: 2.2.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easily create responsive WYSIWYG modals that popup at your desired frequency and optionally print with a single click.


== Description ==

Easily create responsive WYSIWYG popup modals that are displayed at a user-specified frequency and optionally printed with a single click (great for coupons!).  Simple to use, yet advanced options:

* Disable popup: (check to disable, uncheck to enable)
* Frequency of popup: (Define how frequently each popup will display: every refresh, once per session, various minutes, various hours, weekly, monthly, yearly, or only once)
* Popup expires after: (Specify the date the popup will expire, or never expire) [date picker UI]
* Display popup on: (All pages, front page only, or interior pages only)
* Print option: (Allow printing of the popup)
* Popup location: (Centered, top center, bottom center)
* Maximum width: (Define the maximum width of the popup in pixels)
* Show title: (Allow the popup title to be visible or hidden for each popup)
* Text color: (Select a foreground text color for each popup window) [color picker UI]
* Background color: (Select a background color for each popup window) [color picker UI]
* Button location (No buttons "X" to close, left, center, or right)
* Test popup (while creating in the admin console)

== Installation ==

= For an automatic installation through WordPress: =

1. Select **Add New** from the WordPress **Plugins** menu in the admin area.
2. Search for **Popup Modal**.
3. Click **Install Now**, then **Activate Plugin**.

= For manual installation via FTP: =

1. Upload the **popup-modal** folder to the **/wp-content/plugins/** directory.
2. Activate the plugin from the **Plugins** screen in your WordPress admin area.

= To upload the plugin through WordPress, instead of FTP: =

1. From the **Add New** plugins page in your WordPress admin area, select the **Upload Plugin** button.
2. Select the **popup-modal.zip** file, click **Install Now** and **Activate Plugin**.


== Frequently Asked Questions ==

= Are there any settings for Popup Modal? =

When adding/editing a popup modal, below the popup content area are **Popup Options** which allow you to configure options for each popup.  Options include:

* Disable popup: (check to disable, uncheck to enable)
* Frequency of popup: (Define how frequently each popup will display: every refresh, once per session, various minutes, various hours, weekly, monthly, yearly, or only once)
* Popup expires after: (Specify the date the popup will expire, or never expire) [date picker UI]
* Display popup on: (All pages, front page only, or interior pages only)
* Print option: (Allow printing of the popup)
* Popup location: (Centered, top center, bottom center)
* Maximum width: (Define the maximum width of the popup in pixels)
* Show title: (Allow the popup title to be visible or hidden for each popup)
* Text color: (Select a foreground text color for each popup window) [color picker UI]
* Background color: (Select a background color for each popup window) [color picker UI]
* Button location (No buttons "X" to close, left, center, or right)
* Test popup (while creating in the admin console)

= Can I have multiple popups? =

You sure can!  The plugin was designed to show one popup at a time if multiple popups are scheduled to be displayed.  The first popup will display, and when the user closes the popup the next popup will appear.  It's suggested that you don't have too many popups published at the same time to avoid users getting hammered by a bunch of popups.  Remember, the popup frequency is for each user.  Therefore, just because you're no longer seeing the popup, doesn't mean your users aren't.

= How is the popup frequency controlled? =

The popup frequency is controlled via cookies set on the user's browser.  When a popup occurs, a cookie is set which expires at the specified frequency (for example, in an hour).  After the cookie expires, the popup with happen again.

= What if a user is blocking cookies? =

Popup Modal will work just fine if a user is blocking third-party cookies.  However, if they're blocking first-party cookies as well, they're going to see a LOT of popups and have many problems on most websites.  Blocking third-party cookies is fine (and good for privacy), as is clearing cookies when your browser closes.  But blocking all cookies will render a lot of the web useless.  I wouldn't worry too much about it.  Not many people block first-party cookies and those that do are accustomed to creating exceptions when required.


== Screenshots ==

1. Popup Modal options for each popup.
2. Easily create warning messages about scheduled website or service downtimes
3. Simple example of what a popup can look like (full WYSIWYG editor so it can be anything)
4. Optionally, generate a print console to print just the popup (great for coupons!)


== Changelog ==

= v2.2.0 - 2/24/2023 =
* Verified working with WordPress v6.2

= v2.1 - 2/1/2021 =
* Updated contact email address, verified working with WordPress v5.6, include local jquery-ui.css file.

= v2.0 - 12/23/2016 =
* Links that open in a new tab now close correctly.  However, consider not using target="_blank" (the user should decide if they want a new tab open, not the website).  Using target="_blank" is considered poor UX and is being considered for depreciation from the HTML spec.

= v1.9 - 12/22/2016 =
* Image links in your popup will now work correctly.

= v1.8 - 6/22/2016 =
* Links in your popup will now work correctly.

= v1.7 - 5/13/2016 =
* The "Test Popup" button in the editor now displays the popup more accurately.
* Added revision history to popups.

= v1.6 - 5/3/2016 =
* Shortcodes now supported in popup modal body (if well-behaved).
* Includes login/logout/registration shortcode: [pm-login] but most shortcodes should work.
* Test popup while creating in the admin console.
* Escape key (ESC) will close popups.
* CSS modified to accomidate printing with "background graphics" turned on in browser.

= v1.5 - 4/28/2016 =
* Popup location can be specified: Centered, top center, bottom center.
* Option to add buttons to close or print popup and specify the button location.
* New option to disable popup.
* Small update to default print CSS for better formatting (you can always override the CSS with your own).

= v1.4 - 4/22/2016 =
* Added expires after date option via a date picker UI.

= v1.3 - 4/21/2016 =
* Text color and background color options now offer a color palette picker.
* Added new popup frequencies of 1, 5, 10, 15 and 30 minutes.
* Cookie value now human-readable that shows the next time the popup will display.
* If you change the popup frequency, the associated cookie (on the administrator's browser) will be deleted for testing purposes.

= v1.2 - 4/19/2016 =
* Added option to define the maximum pixel width of each popup modal.
* Added option to hide or show title for each popup modal.
* Added option to change foreground text color for each popup modal.
* Re-organized options in a more logical order.
* Cleaned up screen and print CSS.
* Removed scrolling option, automatically adds scrollbar if popup is taller than window.

= v1.1 - 4/15/2016 =
* Added features: Frequency of popup, show popup only on interior pages, allow or block page scrolling, setting individual background color.

= v1.0 - 4/11/2016 =
* Initial release.