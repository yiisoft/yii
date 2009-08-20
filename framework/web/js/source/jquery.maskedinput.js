/// <reference path="../../../lib/jquery-1.2.6.js" />
/*
* Copyright (c) 2007-2008 Josh Bush (digitalbush.com)
*
* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following
* conditions:
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
* OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
* OTHER DEALINGS IN THE SOFTWARE.
*/

/*
* Version: 1.2.0
* Release: 2008-12-07
*/
(function($) {
	var pasteEventName = ($.browser.msie ? 'paste' : 'input')+".mask";

	$.mask = {
		//Predefined character definitions
		definitions: {
			'9': "[0-9]",
			'a': "[A-Za-z]",
			'*': "[A-Za-z0-9]"
		},
		addPlaceholder: function(c, r) {//Deprecated, this is going away in a future release.
			$.mask.definitions[c] = r;
		}
	};

	$.fn.extend({
		//Helper Function for Caret positioning
		caret: function(begin, end) {
			if (this.length == 0) return;
			if (typeof begin == 'number') {
				end = (typeof end == 'number') ? end : begin;
				return this.each(function() {
					if (this.setSelectionRange) {
						this.focus();
						this.setSelectionRange(begin, end);
					} else if (this.createTextRange) {
						var range = this.createTextRange();
						range.collapse(true);
						range.moveEnd('character', end);
						range.moveStart('character', begin);
						range.select();
					}
				});
			} else {
				if (this[0].setSelectionRange) {
					begin = this[0].selectionStart;
					end = this[0].selectionEnd;
				} else if (document.selection && document.selection.createRange) {
					var range = document.selection.createRange();
					begin = 0 - range.duplicate().moveStart('character', -100000);
					end = begin + range.text.length;
				}
				return { begin: begin, end: end };
			}
		},
		unmask: function() { return this.trigger("unmask"); },
		mask: function(mask, settings) {
			if (!mask && this.length > 0) {
				var input = $(this[0]);
				var locked = input.data("locked");
				return $.map(input.data("buffer"), function(c, i) {
					return locked[i] ? null : c;
				}).join('');
			}
			settings = $.extend({
				placeholder: "_",
				allowPartial: false,
				completed: null
			}, settings);

			//Build Regex for format validation
			var re = new RegExp("^" +
			$.map(mask.split(""), function(c, i) {
				return $.mask.definitions[c] || ((/[A-Za-z0-9]/.test(c) ? "" : "\\") + c);
			}).join('') +
			"$");

			return this.each(function() {
				var input = $(this);
				var buffer = new Array(mask.length);
				var locked = new Array(mask.length);
				var ignore = false;  			//Variable for ignoring control keys
				var firstNonMaskPos = null;

				//Build buffer layout from mask & determine the first non masked character
				$.each(mask.split(""), function(i, c) {
					locked[i] = ($.mask.definitions[c] == null);
					buffer[i] = locked[i] ? c : settings.placeholder;
					if (!locked[i] && firstNonMaskPos == null)
						firstNonMaskPos = i;
				});
				input.data("buffer", buffer).data("locked", locked);

				function focusEvent() {
					var pos = checkVal();
					writeBuffer();
					setTimeout(function() {
						input.caret(pos);
					}, 0);
				};

				function keydownEvent(e) {
					var pos = $(this).caret();
					var k = e.keyCode;
					ignore = (k < 16 || (k > 16 && k < 32) || (k > 32 && k < 41));

					//delete selection before proceeding
					if ((pos.begin - pos.end) != 0 && (!ignore || k == 8 || k == 46)) {
						clearBuffer(pos.begin, pos.end);
					}
					//backspace and delete get special treatment
					if (k == 8) {//backspace
						while (pos.begin-- >= 0) {
							if (!locked[pos.begin]) {
								buffer[pos.begin] = settings.placeholder;
								writeBuffer();
								$(this).caret(Math.max(firstNonMaskPos, pos.begin));
								return false;
							}
						}
					} else if (k == 46) {//delete
						clearBuffer(pos.begin, pos.begin + 1);
						writeBuffer();
						$(this).caret(Math.max(firstNonMaskPos, pos.begin));
						return false;
					} else if (k == 27) {//escape
						clearBuffer(0, mask.length);
						writeBuffer();
						$(this).caret(firstNonMaskPos);
						return false;
					}
				};

				function keypressEvent(e) {
					if (ignore) {
						ignore = false;
						//Fixes Mac FF bug on backspace
						return (e.keyCode == 8) ? false : null;
					}
					e = e || window.event;
					var k = e.charCode || e.keyCode || e.which;
					var pos = $(this).caret();

					if (e.ctrlKey || e.altKey) {//Ignore
						return true;
					} else if ((k >= 41 && k <= 122) || k == 32 || k > 186) {//typeable characters
						var p = seekNext(pos.begin - 1);
						if (p < mask.length) {
							var c = String.fromCharCode(k);
							if (new RegExp($.mask.definitions[mask.charAt(p)]).test(c)) {
								buffer[p] = c;
								writeBuffer();
								var next = seekNext(p);
								$(this).caret(next);
								if (settings.completed && next == mask.length)
									settings.completed.call(input);
							}
						}
					}
					return false;
				};

				function clearBuffer(start, end) {
					for (var i = start; i < end && i < mask.length; i++) {
						if (!locked[i])
							buffer[i] = settings.placeholder;
					}
				};

				function writeBuffer() { return input.val(buffer.join('')).val(); };

				function checkVal() {
					//try to place characters where they belong
					var test = input.val();
					var pos = firstNonMaskPos;
					for (var i = 0; i < mask.length; i++) {
						if (!locked[i]) {
							buffer[i] = settings.placeholder;
							while (pos++ < test.length) {
								var reChar = new RegExp($.mask.definitions[mask.charAt(i)]);
								if (test.charAt(pos - 1).match(reChar)) {
									buffer[i] = test.charAt(pos - 1);
									break;
								}
							}
							if (pos > test.length)
								break;
						}
					}
					valid = writeBuffer().match(re);
					if (!valid && !settings.allowPartial) {
						input.val("");
						clearBuffer(0, mask.length);
					}
					return valid ? mask.length : (settings.allowPartial ? i : firstNonMaskPos);
				};

				function seekNext(pos) {
					while (++pos < mask.length) {
						if (!locked[pos])
							return pos;
					}
					return mask.length;
				};

				input
					.one("unmask", function() {
						input
							.unbind(".mask")
							.removeData("buffer")
							.removeData("locked");
					})
					.bind("focus.mask", focusEvent)
					.bind("blur.mask", checkVal)
					.bind("keydown.mask", keydownEvent)
					.bind("keypress.mask", keypressEvent)
					.bind(pasteEventName, function() { setTimeout(checkVal, 0); });

				checkVal(); //Perform initial check for existing values
			});
		}
	});
})(jQuery);