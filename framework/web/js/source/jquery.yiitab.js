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

	$.extend($.fn, {
		yiitab: function() {

			function activate(id) {
				var $tab=$(id);
				var $container=$tab.parent();
				$container.find('>ul a').removeClass('active');
				$container.find('>ul a[href="'+id+'"]').addClass('active');
				$container.children('div').hide();
				$tab.show();
			}

			this.find('>ul a').click(function(event) {
				activate($(event.target).attr('href'));
				return false;
			});

			// activate a tab based on the current anchor
			var url = decodeURI(window.location);
			var pos = url.indexOf("#");
			if (pos >= 0) {
				var id = url.substring(pos);
				if (this.find('>ul a[href="'+id+'"]').length > 0) {
					activate(id);
					return;
				}
			}
		}
	});

})(jQuery);
