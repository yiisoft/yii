/**
 * jQuery Yii ListView plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

;(function($) {
	/**
	 * yiiListView set function.
	 * @param options map settings for the list view. Availablel options are as follows:
	 * - ajaxUpdate: array, IDs of the containers whose content may be updated by ajax response
	 * - ajaxVar: string, the name of the GET variable indicating the ID of the element triggering the AJAX request
	 * - pagerClass: string, the CSS class for the pager container
	 * - sorterClass: string, the CSS class for the sorter container
	 * - updateSelector: string, the selector for choosing which elements can trigger ajax requests
	 * - beforeAjaxUpdate: function, the function to be called before ajax request is sent
	 * - afterAjaxUpdate: function, the function to be called after ajax response is received
	 */
	$.fn.yiiListView = function(options) {
		return this.each(function(){
			var settings = $.extend({}, $.fn.yiiListView.defaults, options || {}),
			$this = $(this),
			id = $this.attr('id');

			if(settings.updateSelector == undefined) {
				settings.updateSelector = '#'+id+' .'+settings.pagerClass.replace(/\s+/g,'.')+' a, #'+id+' .'+settings.sorterClass.replace(/\s+/g,'.')+' a';
			}
			$.fn.yiiListView.settings[id] = settings;

			if(settings.ajaxUpdate.length > 0) {
				$(document).on('click.yiiListView', settings.updateSelector,function(){
					// Check to see if History.js is enabled for our Browser
					if (settings.enableHistory && window.History.enabled) {
						// Ajaxify this link
						var url = $(this).attr('href'),
							params = $.deparam.querystring(url);

						delete params[settings.ajaxVar];
						window.History.pushState(null, null, $.param.querystring(url.substr(0, url.indexOf('?')), params));
					} else {
						$.fn.yiiListView.update(id, {url: $(this).attr('href')});
					}
					return false;
				});
			}

			if (settings.enableHistory && settings.ajaxUpdate !== false && window.History.enabled) {
				$(window).bind('statechange', function() { // Note: We are using statechange instead of popstate
					var State = window.History.getState(); // Note: We are using History.getState() instead of event.state
					$.fn.yiiListView.update(id, {url: State.url});
				});
			}
		});
	};

	$.fn.yiiListView.defaults = {
		ajaxUpdate: [],
		ajaxVar: 'ajax',
		pagerClass: 'pager',
		loadingClass: 'loading',
		sorterClass: 'sorter'
		// updateSelector: '#id .pager a, '#id .sort a',
		// beforeAjaxUpdate: function(id) {},
		// afterAjaxUpdate: function(id, data) {},
		// url: 'ajax request URL'
	};

	$.fn.yiiListView.settings = {};

	/**
	 * Returns the key value for the specified row
	 * @param id string the ID of the list view container
	 * @param index integer the zero-based index of the data item
	 * @return string the key value
	 */
	$.fn.yiiListView.getKey = function(id, index) {
		return $('#'+id+' > div.keys > span:eq('+index+')').text();
	};

	/**
	 * Returns the URL that generates the list view content.
	 * @param id string the ID of the list view container
	 * @return string the URL that generates the list view content.
	 */
	$.fn.yiiListView.getUrl = function(id) {
		var settings = $.fn.yiiListView.settings[id];
		return settings.url || $('#'+id+' > div.keys').attr('title');
	};

	/**
	 * Performs an AJAX-based update of the list view contents.
	 * @param id string the ID of the list view container
	 * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
	 * the URL to be requested is the one that generates the current content of the list view.
	 */
	$.fn.yiiListView.update = function(id, options) {
		var settings = $.fn.yiiListView.settings[id];
		$('#'+id).addClass(settings.loadingClass);
		options = $.extend({
			type: 'GET',
			url: $.fn.yiiListView.getUrl(id),
			success: function(data,status) {
				$.each(settings.ajaxUpdate, function(i,v) {
					var id='#'+v;
					$(id).replaceWith($(id,'<div>'+data+'</div>'));
				});
				if(settings.afterAjaxUpdate != undefined)
					settings.afterAjaxUpdate(id, data);
				$('#'+id).removeClass(settings.loadingClass);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#'+id).removeClass(settings.loadingClass);
				alert(XMLHttpRequest.responseText);
			}
		}, options || {});

		if(options.data!=undefined && options.type=='GET') {
			options.url = $.param.querystring(options.url, options.data);
			options.data = {};
		}
		options.url = $.param.querystring(options.url, settings.ajaxVar+'='+id);

		if(settings.beforeAjaxUpdate != undefined)
			settings.beforeAjaxUpdate(id);
		$.ajax(options);
	};

})(jQuery);