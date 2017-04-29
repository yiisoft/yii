/**
 * jQuery Yii plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

;(function($) {

$.yii = {
	version : '1.0',

	submitForm : function (element, url, params) {
		var f = $(element).parents('form')[0];
		if (!f) {
			f = document.createElement('form');
			f.style.display = 'none';
			element.parentNode.appendChild(f);
			f.method = 'POST';
		}
		if (typeof url == 'string' && url != '') {
			f.action = url;
		}
		if (element.target != null) {
			f.target = element.target;
		}

		var inputs = [];
		$.each(params, function(name, value) {
			var input = document.createElement("input");
			input.setAttribute("type", "hidden");

			// jQuery restriction workaround is used here
			// see this note from the jQuery docs (http://api.jquery.com/submit/):
			// forms and their child elements should not use input names or ids that conflict with
			// properties of a form, such as submit, length, or method
			// name conflicts can cause confusing failures
			input.setAttribute("name", name == 'submit' ? 'submit-element' : name);

			input.setAttribute("value", value);
			f.appendChild(input);
			inputs.push(input);
		});

		// remember who triggers the form submission
		// this is used by jquery.yiiactiveform.js
		$(f).data('submitObject', $(element));

		$(f).trigger('submit');

		$.each(inputs, function() {
			f.removeChild(this);
		});
	}
};

})(jQuery);
