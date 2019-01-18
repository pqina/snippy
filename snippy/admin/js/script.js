var Snippy = (function(undefined){

	function showLocalField() {
		showNodes(document.querySelectorAll('.snippy--resource-type-local'));
		hideNodes(document.querySelectorAll('.snippy--resource-type-remote'));
	}

	function showRemoteField() {
		showNodes(document.querySelectorAll('.snippy--resource-type-remote'));
		hideNodes(document.querySelectorAll('.snippy--resource-type-local'));
	}

	function showFileField(isText) {

		showNodes(document.querySelectorAll('.snippy--bit-resource-field'));
		hideNodes(document.querySelectorAll('.snippy--bit-text-field'));

		if (!isText) {
			exports.Bits.editor.setValue(document.querySelector('.snippy--bit-resource-original').textContent);
		}
	}

	function showTextField(isText) {

		showNodes(document.querySelectorAll('.snippy--bit-text-field'));
		hideNodes(document.querySelectorAll('.snippy--bit-resource-field'));

		if (!isText) {
			var textarea = document.getElementById('snippy-bit-editor-textarea');
			exports.Bits.editor.setValue('');
		}
	}

	var exports = {
		Bits:{
			showLocalField:showLocalField,
			showRemoteField:showRemoteField,
			showTextField:showTextField,
			showFileField:showFileField,
			loadEditor:loadEditor,
			editor:null,
			setEditorFormat:setEditorFormat
		}
	};

	function setEditorFormat(format) {

		if (format === 'resource') {
			return;
		}

		if (format === 'js') {
			format = 'javascript';
		}

		exports.Bits.editor.setMode('ace/mode/' + format);
	}

	function hideNodes(nodes) {
		[].slice.call(nodes).forEach(function(node) {
			node.style.display = 'none';
		});
	}

	function showNodes(nodes) {
		[].slice.call(nodes).forEach(function(node) {
			node.style.display = '';
		});
	}

	function updatePlaceholders(value) {
		var placeholders = document.querySelector('.snippy--bit-placeholders');
		var matches = value.match(/({{[a-z_]+(?::.+?){0,1}}})/gi) || [];
		var tags = [];
		placeholders.innerHTML = matches.map(function(str) {
			var tag = str.match(/^{{([a-z_]+)/i)[0].replace(/^{{/,'');
			if (tags.indexOf(tag) !== -1) {
				return null;
			}
			tags.push(tag);
			var value = '';
			if (str.indexOf(':')!==-1 && tag !== 'content') {
				value = str.match(/:(.+)}}$/)[0].substr(1).replace(/}}$/,'');
			}
			return '<li><span class="snippy--bit-placeholder-name">' + tag + '</span><span class="snippy--bit-placeholder-default">' + value + '</span></li>';
		}).filter(function(item){ return item !== null }).join('');
	}

	function loadEditor() {

		// get reference to our text area
		var textarea = document.getElementById('snippy--bit-editor-textarea');

		// start editor session
		var editor = ace.edit('snippy--bit-editor');
		editor.setTheme('ace/theme/chrome');
		editor.setBehavioursEnabled(false);
		editor.setWrapBehavioursEnabled(false);
		editor.setShowFoldWidgets(false);
		editor.setShowInvisibles(false);
		editor.setOptions({
			minLines: 5,
			maxLines: 25
		});

		// set mode
		var type = document.querySelector('.snippy--bit-type-toggle input[name="type"]:checked').value;
		if (type === 'resource') {
			type = 'html';
		}
		editor.session.setOption('useWorker', false);
		editor.session.setMode('ace/mode/' + type);
		var editorSession = editor.getSession();

		// sync textarea with editor
		editorSession.setValue(textarea.value);
		editorSession.on('change', function() {
			textarea.value = editorSession.getValue();
			updatePlaceholders(textarea.value);
		});

		updatePlaceholders(textarea.value);

		// expose editor
		exports.Bits.editor = editorSession;
	}

	document.addEventListener('DOMContentLoaded', function() {
		if ('ace' in window) {
			loadEditor();
		}
	});

	return exports;

} ());