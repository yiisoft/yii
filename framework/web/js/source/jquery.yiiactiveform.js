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
	$.fn.yiiactiveform = function(settings) {
		var settings = $.extend({}, $.fn.yiiactiveform.defaults, settings || {});
		return this.each(function() {
			$form = $(this);
			var id = $form.attr('id');
			if(settings.validationUrl == undefined)
				settings.validationUrl = $form.attr('action');
			$.each(settings.attributes, function(i,attribute){
				settings.attributes[i] = $.extend({
					validationDelay : settings.validationDelay,
					validateOnChange : settings.validateOnChange,
					errorLabelCssClass : settings.errorLabelCssClass,
					successLabelCssClass : settings.successLabelCssClass,
					errorInputCssClass : settings.errorInputCssClass,
					successInputCssClass : settings.successInputCssClass,
					errorMessageCssClass : settings.errorMessageCssClass,
					successMessageCssClass : settings.successMessageCssClass,
					successMessage : settings.successMessage
				}, attribute);
				$('#'+attribute.inputID).value = $('#'+attribute.inputID).val();
			});
			$.fn.yiiactiveform.settings[id] = settings;

			// updates the input field, label, and validation result for a particular attribute
			var updateInput = function(attribute, messages) {
				var hasError = messages && $.isArray(messages[attribute.inputID]) && messages[attribute.inputID].length>0;
				if(hasError) {
					$('#'+attribute.errorID).html(messages[attribute.inputID][0]);
				}
				else if(attribute.successMessage!=false) {
					$('#'+attribute.errorID).html(attribute.successMessage);
				}

				$('#'+attribute.errorID).toggle(hasError || !hasError && attribute.successMessage!=false);

				if(attribute.errorMessageCssClass!==false) {
					$('#'+attribute.errorID).toggleClass(attribute.errorMessageCssClass, hasError);
				}
				if(attribute.successMessageCssClass!==false) {
					$('#'+attribute.errorID).toggleClass(attribute.successMessageCssClass, !hasError);
				}
				if(attribute.errorInputCssClass!==false) {
					$('#'+attribute.inputID).toggleClass(attribute.errorInputCssClass, hasError);
				}
				if(attribute.successInputCssClass!==false) {
					$('#'+attribute.inputID).toggleClass(attribute.successInputCssClass, !hasError);
				}
				if(attribute.errorLabelCssClass!==false) {
					$("label[for='"+attribute.inputID+"']").toggleClass(attribute.errorLabelCssClass, hasError);
				}
				if(attribute.successLabelCssClass!==false) {
					$("label[for='"+attribute.inputID+"']").toggleClass(attribute.successLabelCssClass, !hasError);
				}

				$('#'+attribute.inputID).value = $('#'+attribute.inputID).val();

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
							if (attribute.status == 3) {
								settings.attributes[i].status = 1;
							}
						});
					}
				});
			};

			if (settings.validateOnSubmit) {
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
							$form.submit();
							validated = false;
						}
					});
					return false;
				});
			}

			var validate = function() {
				$.each(settings.attributes, function(i, attribute){
					if (attribute.status == 2)
						settings.attributes[i].status = 3;
				});
				ajaxValidate(function(data) {
					$.each(settings.attributes, function(i, attribute){
						if (attribute.status == 3) {
							settings.attributes[i].status = 1;
							updateInput(attribute, data);
						}
					});
				});
			};

			$.each(settings.attributes, function(i, attribute) {
				var validateLater = function() {
					settings.attributes[i].status = 2;
					if(settings.timer!=undefined)
						clearTimeout(settings.timer);
					settings.timer=setTimeout(validate, attribute.validationDelay);
				};
				if (attribute.validateOnChange) {
					$('#'+this.inputID).change(function(){
						validateLater();
					}).blur(function(){
						if(!settings.attributes[i].status) {
							validateLater();
						}
					});
				}
			});
		});
	};

	$.fn.yiiactiveform.defaults = {
		ajaxVar: 'ajax',
		validationUrl: undefined,
		validationDelay: 100,
		validateOnSubmit : false,
		validateOnChange : true,
		errorLabelCssClass : 'error',
		successLabelCssClass : 'success',
		errorInputCssClass : 'error',
		successInputCssClass : 'success',
		errorMessageCssClass : 'errorMessage',
		successMessageCssClass : 'successMessage',
		successMessage : false,
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
		 *     errorLabelCssClass : 'error',
		 *     successLabelCssClass : 'success',
		 *     errorInputCssClass : 'error',
		 *     successInputCssClass : 'success',
		 *     errorMessageCssClass : 'errorMessage',
		 *     successMessageCssClass : 'successMessage',
		 *     successMessage: 'success message',
		 * }
		 */
		attributes : []
	};

	$.fn.yiiactiveform.settings = {};

})(jQuery);