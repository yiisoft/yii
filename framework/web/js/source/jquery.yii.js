/**
 * jQuery Yii plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
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
		};
		if (typeof url == 'string' && url != '') {
			f.action = url;
		};
		var inputs = [];
		jQuery.each(params, function(name, value) {
			var input = document.createElement("input");
			input.setAttribute("type", "hidden");
			input.setAttribute("name", name);
			input.setAttribute("value", value);
			f.appendChild(input);
			inputs.push(input);
		});

		jQuery(f).trigger('submit');

		jQuery.each(inputs, function() {
			f.removeChild(this);
		});
	}
};

})(jQuery);
