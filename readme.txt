=== Awesome Google Review ===
Contributors: spiderdunia
Tags: Tags: awesome, reviews, awesomeness, googlereview, googlereviews, reviewgoogle, upload
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display and manage Google Reviews effortlessly with Awesome Google Review. Enhance your sites credibility with real-time customer feedback.


== Description ==
Awesome Google Review is a powerful and user-friendly plugin designed to showcase your business Google Reviews on your WordPress site. With seamless integration and easy configuration, this plugin helps you enhance your sites credibility and attract more customers by displaying authentic customer feedback.

### Key Features:
- **Automated Review Import**: Automatically fetch and display the latest Google Reviews for your business.
- **Customizable Display**: Choose from various layout options to match your sites design.
- **Shortcode Support**: Easily embed reviews anywhere on your site using shortcodes.
- **Cron Job Management**: Efficiently manage the cron jobs responsible for fetching and updating reviews.
- **Admin Panel**: User-friendly admin interface for managing settings and viewing review data.
- **Secure and Reliable**: Ensures data integrity and security while interacting with Google APIs.
- **Responsive Design**: Fully responsive, ensuring your reviews look great on all devices.
- **Free API Key**: Get started easily with a free API key included: beau62e081f846bbb5f452e426de67d7.

Whether you run a small local business or a large enterprise, Awesome Google Review is the perfect solution to leverage customer testimonials and build trust with your audience.

== Usage ==

To display all reviews for a specific term, use the following function in your theme or plugin:

$all_reviews = get_all_reviews_by_term($term_id, $review_flag = false);
$term_id: The ID of the term for which you want to fetch reviews.
$review_flag: Set to true to display only 5-star reviews. Set to false to display all reviews.


== Installation ==
1. Upload the `awesome-google-review` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin settings in the admin panel.
4. Use the included free API key: `beau62e081f846bbb5f452e426de67d7`.


== Frequently Asked Questions ==
= How do I get started with Awesome Google Review? =

Simply install and activate the plugin, then navigate to the settings page in the WordPress admin panel to configure your Google API key and other settings.

= Can I customize the appearance of the reviews? =

Yes, the plugin offers multiple layout options and customization settings to match your sites design.

= Does the plugin automatically update the reviews? =

Yes, the plugin uses cron jobs to periodically fetch and update the reviews displayed on your site.


== Screenshots ==
1. https://api.spiderdunia.com/admin-panel-settings-page.png
2. https://api.spiderdunia.com/review-display-options.png
3. https://api.spiderdunia.com/example-of-reviews-displayed-on-a-website.png

== Changelog ==

= 1.4.2 =

SVG star code updated.

= 1.4.1 =

Initial release of Awesome Google Review. Please upgrade to enjoy the latest features and improvements.
