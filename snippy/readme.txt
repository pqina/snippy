=== Snippy ===
Contributors: pqina
Donate link: https://codecanyon.net/user/pqina/portfolio?ref=pqina
Tags: shortcode, short code, build, create, javascript
Requires at least: 4.5
Tested up to: 5.3.0
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Makes it easy to create your own custom shortcodes.


== Description ==

Use Snippy to quickly create your own custom shortcodes.

A Snippy shortcode is built by combining bits. A bit can be a file, like a **Stylesheet** or a **Script**, or a piece of code, like **HTML**, **CSS** or **JavaScript**.

The "code" bits can contain placeholders, which are automatically made accessible as shortcode attributes.

*Time for a quick example.*

Let's create a shortcode to embed YouTube videos. We'll add an HTML bit and set the it's value to the YouTube embed iframe.

`<iframe src="https://youtube/embed/JZYYJY4yoK4"/>`

Now to make this bit a bit more flexible we will replace the YouTube video id with a placeholder value `{{id}}`.

`<iframe src="https://youtube/embed/{{id}}"/>`

*Yay! We've finished our first bit!*

Now we can create a new Snippy shortcode and add the YouTube iFrame HTML bit. After saving the shortcode we can use our new and shiny YouTube shortcode in the text editor.

`[[youtube id=JZYYJY4yoK4]]`

*Ready for more?*

The below 3 minute YouTube video shows how you can use Snippy to turn a jQuery plugin into a WordPress plugin.

<iframe src="https://youtube.com/embed/JZYYJY4yoK4"></iframe>


== Installation ==

1. Download the zip file.
1. Log into WordPress, hover over *Plugins* and click *Add new*
1. Click on the *Upload Plugin* button.
1. Select the zip file you downloaded.
1. Click *Install Plugin*.
1. Click *Activate*.
1. Navigate to the 'Snippy' menu on the left and setup your first shortcode.


== Frequently Asked Questions ==

= Can I use JavaScript or CSS files instead of HTML =

Yes, you can select JS and CSS files to be added to a bit, Snippy will automatically load the files when the shortcode is used on a page.

Please note that your server might prevent uploading files with a .js or .css extension. If that's the case you have to alter the server security settings to allow uploading of these files.


= How do I define placeholders =

You can define placeholders in bits by wrapping text in brackets like this: `{{placeholder}}`.

Suppose you want to create a placeholder for a YouTube video. You'd replace the YouTube video id with `{{id}}`.

`<iframe src="https://youtube/embed/{{id}}"/>`

Now the attribute id is available in any shortcode that uses the YouTube HTML bit.

`[[youtube id=JZYYJY4yoK4]]`


= How do I set a placeholder default value =

Placeholder default values can be set by following the placeholder name with a semicolon and then the default value.

`{{name:John Doe}}`


= Which default placeholders can I use =

The following list of placeholders have a special function:

* `{{content}}` is always replaced with the content wrapped by your shortcode.
* `{{date_today}}` is replaced with an ISO8601 representation of today's date.
* `{{date_tomorrow}}` is replaced with an ISO8601 representation of today's date.
* `{{unique_id}}` is replaced with a uniquely generated id.
* `{{shortcode_id}}` is replaced with the id of the current shortcode.
* `{{bit_id}}` is replaced with the id of the current bit.
* `{{page_id}}` is replaced with the current page id.
* `{{page_relative_url}}` relative url to the current page.
* `{{page_absolute_url}}` absolute url to the current page (includes the domain).
* `{{theme}}` current theme name.
* `{{theme_root_uri}}` theme directory URI.
* `{{template_directory_uri}}` current theme directory URI.
* `{{admin_url}}` current admin url.
* `{{nonce_field:action,name}}` generate a nonce field.


== Screenshots ==

1. Creating an HTML bit containing a YouTube iframe snippet and setting the `{{id}}` placeholder.
2. Creating a `[youtube]` shortcode that makes use of the YouTube iframe snippet.
3. Adding the shortcode to the page and setting a YouTube video id.


== Changelog ==

= 1.4.1 =

* Test with WordPress 5.3


= 1.4.0 =

* Add `{{admin_url}}` and `{{nonce_field}}` placeholders.


= 1.3.5 =

* Fixed problem where snippy shortcode menu was rendered before doctype


= 1.3.4 =

* Fixed problem where paging would not work


= 1.3.3 =

* Tested with WordPress 5.0.0


= 1.3.0 =

* When using multiple placeholders with the same name, they will only show up once
* Add `bit_id` and `shortcode_id` placeholders


= 1.2.0 =

* HTML bits can now contain shortcodes
* Add more placeholders


= 1.1.1 =

* Fix problem where shortcode and bits tables would not show paging control


= 1.1.0 =

* Add "local" or "remote" resource bit which makes possible the option to include CDN resources.
* Add a starter set of dynamic placeholders
* Only admin can now edit Snippy shortcodes


= 1.0 =

* Initial release


== Upgrade Notice ==

Improved parsing of bits, HTML bits can now contain shortcodes. Three additional default placeholders were added.