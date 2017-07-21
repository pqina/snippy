=== Snippy ===
Contributors: pqina
Donate link: https://codecanyon.net/user/pqina/portfolio?ref=pqina
Tags: shortcode, short code, custom, build, create, edit, javascript, jquery, plugin
Requires at least: 4.5
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Makes it easy to create your own custom shortcodes.


== Description ==

Use Snippy to quickly create your own custom Shortcodes.

A Snippy Shortcode consists of bits. Each bit is either a **Stylesheet**, a **Script** or a piece of **HTML**, **CSS** or **JavaScript**.

The HTML, CSS and JavaScript bits can contain placeholders, which are automatically made accessible through shortcode attributes.

Time for an example.

**YouTube video embed**

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
        src="https://www.youtube.com/embed/{{id}}"></iframe>
```

If you would use the above bit in a Snippy shortcode named "youtube" you can pass the id to the YouTube bit as follows:

```
[youtube id=S7JjNq6feK0]
```

== Screenshots ==

1. Creating an HTML bit containing a YouTube iframe snippet and setting the `{{id}}` placeholder.
2. Creating a [youtube] shortcode that makes use of the YouTube iframe snippet.
3. Adding the shortcode to the page and setting a YouTube video id.

== Upgrade Notice ==

Test


== Changelog ==

= 1.0 =
* Initial release