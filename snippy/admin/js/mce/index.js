(function(tinymce) {

	'use strict';

	var popup = document.getElementById('snippy--mce-shortcode-popup');
	popup.parentNode.removeChild(popup);
	popup.style.display = '';


	function repositionSnippyList() {
		var node = snippyButton.$el[0];
		var rect = node.getBoundingClientRect();
		popup.style.left = rect.left + (rect.width * .5) + 'px';
		popup.style.top = rect.top + window.scrollY + 'px';
	}

	function closeSnippyList(cb) {

		popup.removeEventListener('click', cb);

		window.removeEventListener('scroll', repositionSnippyList);
		window.removeEventListener('resize', repositionSnippyList);

		document.body.removeChild(popup);
	}

	function openSnippyList(editor, cb) {

		// make invisible if already visible
		if (popup.parentNode) {
			closeSnippyList(pickShortcode);
			return;
		}

		repositionSnippyList();

		window.addEventListener('scroll', repositionSnippyList);
		window.addEventListener('resize', repositionSnippyList);

		function pickShortcode(e) {

			if (e.target.nodeName !== 'A') {
				return;
			}

			e.preventDefault();

			var selection = editor.selection.getContent({format : 'text'});

			var value = e.target.textContent;

			var output = '[' + value + ' ';

			var rawPlaceholders = e.target.getAttribute('data-placeholders');
			var placeholders = JSON.parse(rawPlaceholders);

			output += placeholders.map(function(placeholder) {
				return placeholder.name + '="' + placeholder.value + '"';
			}).join(' ');

			output = output.trim();

			if (selection.length) {
				output += ']';
				output += selection;
				output += '[/' + value + ']';
			}
			else {
				output += '/]';
			}

			closeSnippyList(pickShortcode);

			cb(output);

		}

		document.body.appendChild(popup);
		popup.addEventListener('click', pickShortcode);

	}

	var snippyButton = null;

	function addSnippyPlugin(editor, url) {

		// Add insert shortcode button
		editor.addButton('snippy', {
			title: 'Insert Snippy Shortcode',
			image: url + '/icon.svg',
			cmd: 'snippy_insert_shortcode',
			onpostrender: function() {
				snippyButton = this;
			}
		});

		// Add insert command
		editor.addCommand('snippy_insert_shortcode', function() {

			// open
			openSnippyList(editor, function(selected){
				editor.execCommand('mceReplaceContent', false, selected);
			});

		});
	}

	// load snippy
	tinymce.PluginManager.add( 'snippy', addSnippyPlugin );

}(tinymce));