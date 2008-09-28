/**
 * jQuery Yii plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

;(function($) {

$.yii = {
	version : '1.0',

	submitForm : function (element, url) {
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
		f.submit();
	}
};

})(jQuery);
