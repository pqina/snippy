=== Plugin Name ===
Contributors: pqina
Tags: shortcode, short code, custom, build, create, edit, javascript, jquery, plugin
Requires at least: 4.6
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

With Snippy you can build your own shortcodes and define which resources (bits) are required when the shortcode is used on a page.


== Description ==

Use Snippy to create custom Shortcodes.

A Snippy Shortcode consists of bits. Each bit can be a **Stylesheet**, a **JavaScript file** or a snippet of **HTML**, **CSS** or **inline JavaScript**.

The HTML, CSS and JavaScript bits can contain placeholders, which automatically made accessible as shortcode attributes.

Time for an example.

=== YouTube video embed ===

Create an HTML bit and set the HTML to the YouTube embed snippet.

```
<iframe type="text/html" width="640" height="360"
  src="https://www.youtube.com/embed/S7JjNq6feK0"
  frameborder="0"></iframe>
```

Now replace the YouTube video id with a placeholder.

```
<iframe type="text/html" width="640" height="360"
  src="https://www.youtube.com/embed/{{id}}"
  frameborder="0"></iframe>
```

Create a new Snippy Shortcode and add the YouTube HTML bit. You can now use the YouTube shortcode in your text editor like this `[youtube id=S7JjNq6feK0]`.

If the HTML bit requires styles or JavaScript files, you can add those as bits as well.


== Installation ==

1. Download the zip file.
1. Log into WordPress, hover over *Plugins* and click *Add new*
1. Click on the *Upload Plugin* button.
1. Select the zip file you downloaded.
1. Click *Install Plugin*.
1. Click *Activate*.
1. Navigate to the 'Snippy' menu on the left and setup your first Shortcode.


== Frequently Asked Questions ==

= How do I use placeholders =

You can define a placeholder in a bit using brackets like this: `{{placeholder}}`.

For example, suppose you want to create a placeholder for a YouTube video.
```
<iframe type="text/html"
        src="https://www.youtube.com/embed/{{id}}?autoplay=1"></iframe>
```

If you would use the above bit in a Snippy shortcode named "youtube" you can pass the id to the YouTube bit as follows:

```
[youtube id=S7JjNq6feK0]
```



== Changelog ==

= 1.0 =
* Initial release