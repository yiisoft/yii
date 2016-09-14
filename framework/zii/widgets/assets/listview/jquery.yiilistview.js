/**
 * jQuery Yii ListView plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

(function ($) {
    var yiiXHR = {}, methods,
        listSettings = [];
    /**
     * yiiListView set function.
     * @param options map settings for the list view. Availablel options are as follows:
     * - ajaxUpdate: array, IDs of the containers whose content may be updated by ajax response
     * - ajaxVar: string, the name of the request variable indicating the ID of the element triggering the AJAX request
     * - ajaxType: string, the type (GET or POST) of the AJAX request
     * - pagerClass: string, the CSS class for the pager container
     * - sorterClass: string, the CSS class for the sorter container
     * - updateSelector: string, the selector for choosing which elements can trigger ajax requests
     * - beforeAjaxUpdate: function, the function to be called before ajax request is sent
     * - afterAjaxUpdate: function, the function to be called after ajax response is received
     */
    methods = {
        init: function (options) {
            var settings = $.extend({
                ajaxUpdate: [],
                ajaxVar: 'ajax',
                ajaxType: 'GET',
                pagerClass: 'pager',
                loadingClass: 'loading',
                sorterClass: 'sorter',
                // updateSelector: '#id .pager a, '#id .sort a',
                // beforeAjaxUpdate: function(id) {},
                // afterAjaxUpdate: function(id, data) {},
                // url: 'ajax request URL'
            }, options || {});
            settings.sorterClass = settings.sorterClass.replace(/\s+/g, '.');

            return this.each(function () {
                var $this = $(this),
                    id = $this.attr('id'),
                    pagerSelector = '#' + id + ' .' + settings.pagerClass.replace(/\s+/g, '.') + ' a',
                    sortSelector = '#' + id + ' .' + settings.sorterClass.replace(/\s+/g, '.') + ' a'
                    ;

                settings.updateSelector = pagerSelector + ', ' + sortSelector;

                listSettings[id] = settings;
                if (settings.ajaxUpdate.length > 0) {
                    $(document).off('click.yiiListView', settings.updateSelector);
                    $(document).on('click.yiiListView', settings.updateSelector, function () {
                        if (settings.enableHistory && window.History.enabled) {
                            var href = $(this).attr('href');
                            if (href) {
                                var url = href.split('?'),
                                    params = $.deparam.querystring('?' + (url[1] || ''));

                                delete params[settings.ajaxVar];

                                var updateUrl = $.param.querystring(url[0], params);
                                window.History.pushState({url: updateUrl}, document.title, updateUrl);
                            }
                        } else {
                            $.fn.yiiListView.update(id, {url: $(this).attr('href')});
                        }
                        return false;
                    });

                    if (settings.enableHistory && window.History.enabled) {
                        $(window).bind('statechange', function () { // Note: We are using statechange instead of popstate
                            var State = window.History.getState(); // Note: We are using History.getState() instead of event.state
                            if (State.data.url === undefined) {
                                State.data.url = State.url;
                            }
                            $.fn.yiiListView.update(id, State.data);
                        });
                    }
                }
            });
        },

        /**
         * Returns the key value for the specified row
         * @param row integer the row number (zero-based index)
         * @return string the key value
         */
        getKey: function (row) {
            return this.children('.keys').children('span').eq(row).text();
        },

        /**
         * Returns the URL that generates the list view content.
         * @return string the URL that generates the list view content.
         */
        getUrl: function () {
            var sUrl = listSettings[this.attr('id')].url;
            return sUrl || this.children('.keys').attr('title');
        },

        /**
         * Performs an AJAX-based update of the list view contents.
         * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
         * the URL to be requested is the one that generates the current content of the list view.
         * @return object the jQuery object
         */
        update: function (options) {
            var customError;
            if (options && options.error !== undefined) {
                customError = options.error;
                delete options.error;
            }
            return this.each(function () {
                var $list = $(this),
                    id = $list.attr('id'),
                    settings = listSettings[id];

                options = $.extend({
                    type: settings.ajaxType,
                    url: $list.yiiListView('getUrl'),
                    success: function (data) {
                        var $data = $('<div>' + data + '</div>');
                        $.each(settings.ajaxUpdate, function (i, el) {
                            var updateId = '#' + el;
                            $(updateId).replaceWith($(updateId, $data));
                        });
                        if (settings.afterAjaxUpdate !== undefined) {
                            settings.afterAjaxUpdate(id, data);
                        }
                    },
                    complete: function () {
                        yiiXHR[id] = null;
                        $list.removeClass(settings.loadingClass);
                    },
                    error: function (XHR, textStatus, errorThrown) {
                        var ret, err;
                        if (XHR.readyState === 0 || XHR.status === 0) {
                            return;
                        }
                        if (customError !== undefined) {
                            ret = customError(XHR);
                            if (ret !== undefined && !ret) {
                                return;
                            }
                        }
                        switch (textStatus) {
                            case 'timeout':
                                err = 'The request timed out!';
                                break;
                            case 'parsererror':
                                err = 'Parser error!';
                                break;
                            case 'error':
                                if (XHR.status && !/^\s*$/.test(XHR.status)) {
                                    err = 'Error ' + XHR.status;
                                } else {
                                    err = 'Error';
                                }
                                if (XHR.responseText && !/^\s*$/.test(XHR.responseText)) {
                                    err = err + ': ' + XHR.responseText;
                                }
                                break;
                        }

                        if (settings.ajaxUpdateError !== undefined) {
                            settings.ajaxUpdateError(XHR, textStatus, errorThrown, err, id);
                        } else if (err) {
                            alert(err);
                        }
                    }
                }, options || {});
                if (options.type === 'GET') {
                    if (options.data !== undefined) {
                        options.url = $.param.querystring(options.url, options.data);
                        options.data = {};
                    }
                }

                if (settings.ajaxVar)
                    options.url = $.param.querystring(options.url, settings.ajaxVar + '=' + id);

                if (yiiXHR[id] != null) {
                    yiiXHR[id].abort();
                }
                //class must be added after yiiXHR.abort otherwise ajax.error will remove it
                $list.addClass(settings.loadingClass);

                if (settings.beforeAjaxUpdate != undefined)
                    settings.beforeAjaxUpdate(id, options);
                yiiXHR[id] = $.ajax(options);
            });
        }
    };

    $.fn.yiiListView = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.yiiListView');
            return false;
        }
    };

/******************************************************************************
 *** DEPRECATED METHODS
 *** used before Yii 1.1.9
 ******************************************************************************/
    $.fn.yiiListView.settings = listSettings;
    /**
     * Returns the key value for the specified row
     * @param id string the ID of the list view container
     * @param row integer the row number (zero-based index)
     * @return string the key value
     */
    $.fn.yiiListView.getKey = function (id, row) {
        return $('#' + id).yiiListView('getKey', row);
    };

    /**
     * Returns the URL that generates the list view content.
     * @param id string the ID of the list view container
     * @return string the URL that generates the list view content.
     */
    $.fn.yiiListView.getUrl = function (id) {
        return $('#' + id).yiiListView('getUrl');
    };

    /**
     * Performs an AJAX-based update of the list view contents.
     * @param id string the ID of the list view container
     * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
     * the URL to be requested is the one that generates the current content of the list view.
     */
    $.fn.yiiListView.update = function (id, options) {
        $('#' + id).yiiListView('update', options);
    };

})(jQuery);
