/**
 * jQuery yiiactiveform plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 * @since 1.1.1
 */

;(function($) {
	/**
	 * yiiactiveform set function.
	 * @param map settings for the active form plugin. Please see {@link CActiveForm::options} for availablel options.
	 */
	$.fn.yiiactiveform = function(options) {
		return this.each(function() {
			var settings = $.extend({}, $.fn.yiiactiveform.defaults, options || {});
			var $form = $(this);
			var id = $form.attr('id');
			if(settings.validationUrl == undefined)
				settings.validationUrl = $form.attr('action');
			$.each(settings.attributes, function(i,attribute){
				settings.attributes[i] = $.extend({
					validationDelay : settings.validationDelay,
					validateOnChange : settings.validateOnChange,
					validateOnType : settings.validateOnType,
					hideErrorMessage : settings.hideErrorMessage,
					inputContainer : settings.inputContainer,
					errorCssClass : settings.errorCssClass,
					successCssClass : settings.successCssClass,
					validatingCssClass : settings.validatingCssClass
				}, attribute);
				settings.attributes[i].value = $('#'+attribute.inputID).val();
			});
			$(this).data('settings', settings);

			var getInputContainer = function(attribute) {
				if(attribute.inputContainer == undefined)
					return $('#'+attribute.inputID).closest('div');
				else
					return $(attribute.inputContainer).filter(':has("#'+attribute.inputID+'")');
			};

			// updates the error message and the input container for a particular attribute
			var updateInput = function(attribute, messages) {
				attribute.status = 1;
				var hasError = messages && $.isArray(messages[attribute.inputID]) && messages[attribute.inputID].length>0;
				var $error = $('#'+attribute.errorID);
				var $container = getInputContainer(attribute);
				$container.removeClass(attribute.validatingCssClass)
					.removeClass(attribute.errorCssClass)
					.removeClass(attribute.successCssClass);

				if(hasError) {
					$error.html(messages[attribute.inputID][0]);
					$container.addClass(attribute.errorCssClass);
				}
				else {
					$container.addClass(attribute.successCssClass);
				}
				if(!attribute.hideErrorMessage)
					$error.toggle(hasError);

				attribute.value = $('#'+attribute.inputID).val();

				return hasError;
			};

			// updates the error summary
			var updateSummary = function(messages) {
				if (settings.summaryID == undefined)
					return;
				var content = '';
				$.each(settings.attributes, function(i, attribute){
					if(messages && $.isArray(messages[attribute.inputID])) {
						$.each(messages[attribute.inputID],function(j,message){
							content = content + '<li>' + message + '</li>';
						});
					}
				});
				$('#'+settings.summaryID+' ul').html(content);
				$('#'+settings.summaryID).toggle(content!='');
			}

			// performs AJAX validation
			var ajaxValidate = function(successCallback) {
				$.ajax({
					url : settings.validationUrl,
					type : $form.attr('method'),
					data : $form.serialize()+'&'+settings.ajaxVar+'='+id,
					dataType : 'json',
					success : successCallback,
					error : function() {
						$.each(settings.attributes, function(i, attribute){
							//updateInput(attribute, []);
						});
					}
				});
			};

			var validate = function(attribute, forceValidate) {
				if (forceValidate)
					attribute.status = 2;
				$.each(settings.attributes, function(){
					if (this.value != $('#'+this.inputID).val()) {
						this.status = 2;
						forceValidate = true;
					}
				});
				if (!forceValidate)
					return;

				if(settings.timer!=undefined)
					clearTimeout(settings.timer);
				settings.timer = setTimeout(function(){
					$.each(settings.attributes, function(){
						if (this.status == 2) {
							this.status = 3;
							getInputContainer(this).addClass(this.validatingCssClass);
						}
					});
					ajaxValidate(function(data) {
						$.each(settings.attributes, function(){
							if (this.status == 3) {
								updateInput(this, data);
							}
						});
					});
				}, attribute.validationDelay);
			};

			$.each(settings.attributes, function(i, attribute) {
				if (attribute.validateOnChange) {
					$('#'+attribute.inputID).change(function(){
						validate(attribute,false);
					}).blur(function(){
						if(attribute.status!=2 && attribute.status!=3)
							validate(attribute, !attribute.status);
					});
				}
				if (attribute.validateOnType) {
					$('#'+attribute.inputID).keyup(function(){
						if (attribute.value != $('#'+attribute.inputID).val())
							validate(attribute, false);
					});
				}
			});

			if (settings.validateOnSubmit) {
				$form.find(':submit').live('mouseup keyup',function(){
					$form.data('submitObject',this);
				});
				var validated = false;
				$form.submit(function(){
					if (validated)
						return true;
					ajaxValidate(function(data){
						var hasError = false;
						$.each(settings.attributes, function(i, attribute){
							hasError = updateInput(attribute, data) || hasError;
						});
						updateSummary(data);
						if(!hasError) {
							validated = true;
							var button = $form.data('submitObject') || $form.find(':submit:first');
							// TODO: if the submission is caused by "change" event, it will not work
							if (button)
								button.click();
							else  // no submit button in the form
								$form.submit();
							validated = false;
						}
					});
					return false;
				});
			}
		});
	};

	$.fn.yiiactiveform.defaults = {
		ajaxVar: 'ajax',
		validationUrl: undefined,
		validationDelay: 100,
		validateOnSubmit : false,
		validateOnChange : true,
		validateOnType : false,
		hideErrorMessage : false,
		inputContainer : undefined,
		errorCssClass : 'error',
		successCssClass : 'success',
		validatingCssClass : 'validating',
		summaryID : undefined,
		timer: undefined,
		/**
		 * list of attributes to be validated. Each array element is of the following structure:
		 * {
		 *     inputID : 'input-tag-id',
		 *     errorID : 'error-tag-id',
		 *     value : undefined,
		 *     status : 0,  // 0: not validated,  1: validated, 2: pending validation, 3: validating
		 *     validationDelay: 100,
		 *     validateOnChange : true,
		 *     validateOnType : false,
		 *     hideErrorMessage : false,
		 *     inputContainer : undefined,
		 *     errorCssClass : 'error',
		 *     successCssClass : 'success',
		 *     validatingCssClass : 'validating',
		 * }
		 */
		attributes : []
	};

})(jQuery);