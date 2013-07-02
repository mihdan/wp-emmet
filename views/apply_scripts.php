<script>
!function($) {
	var editorKey = '<?php echo WP_EMMET_DOMAIN; ?>-editor',
		options = <?php echo $this->Options->toJSON(); ?>,
		keymap = {
			Tab: 'expand_abbreviation_with_tab',
			Enter: 'insert_formatted_line_break_only'
		},
		mimeTypes = {
			php: 'application/x-httpd-php',
			html: 'text/html',
			css: 'text/css',
			js: 'text/javascript',
			json: 'application/json'
		};

	if (options.override_shortcuts) {
		$.each(options.shortcuts, function(type, key) {
			keymap[key] = type.replace(/\s|\//g, '_').replace('.', '').toLowerCase();
		});
		window.emmetKeymap = keymap;
	}

	$(function() {
		$('#content, #newcontent').each(function() {
			var file = $(this).closest('form').find('input[name="file"]').val(),
				editor = CodeMirror.fromTextArea(this, $.extend({}, options.editor, {
					mode: mimeTypes[file ? file.split('.').pop() : 'html']
				}));
			$(this).data(editorKey, editor);
		});

		if (typeof wp !== 'undefined' &&
				typeof wp.media !== 'undefined' &&
				typeof wp.media.editor !== 'undefined') {
			wp.media.editor.insert = function(h) {
				var editor = $('#content').data(editorKey);
				editor.doc.replaceSelection(h);
			};
		}

		if (typeof switchEditors !== 'undefined') {
			switchEditors.switchto = function(el) {
				var params = el.id.split('-'),
					$textarea = $(tinymce.DOM.get(params[0])),
					editor = $textarea.data(editorKey),
					isHTML = params[1] === 'html';

				if (!isHTML) {
					editor.toTextArea();
					editor.disabled = true;
					$textarea.data(editorKey, editor);
				}

				if (isHTML && !editor.disabled) {
					return;
				}

				this.go(params[0], params[1]);

				if (isHTML) {
					editor = CodeMirror.fromTextArea(editor.getTextArea(), editor.options);
					editor.disabled = false;
					$textarea.data(editorKey, editor);
				}
			};
		}

		if (typeof QTags !== 'undefined') {
			QTags.TagButton.prototype.callback = function(element, canvas, ed) {
				var cursor,
					editor = $(canvas).data(editorKey),
					text = editor.doc.getSelection(),
					startPos = text.indexOf(this.tagStart),
					endPos = text.indexOf(this.tagEnd);

				console.log(this);

				if (startPos !== -1 && endPos !== -1) {
					text = text.substring(this.tagStart.length, endPos);
				} else {
					text = this.tagStart + text + this.tagEnd;
				}

				editor.doc.replaceSelection(text);

				cursor = editor.doc.getCursor();
				editor.doc.setSelection(cursor, cursor);
			};
		}

		if (typeof wpLink !== 'undefined') {
			wpLink.htmlUpdate = function() {
				var data = this.getAttrs(),
					editor = $(this.textarea).data(editorKey),
					attrs = '';

				$.each(data, function(name, value) {
					if (value) {
						attrs += ' ' + name + '="' + value + '"';
					}
				});

				editor.replaceSelection('<a' + attrs + '>' + editor.getSelection() + '</a>');

				this.close();
			};
		}
	});
}(jQuery);
</script>
