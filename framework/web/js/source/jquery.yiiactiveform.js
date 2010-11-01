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
	 * @param options map settings for the active form plugin. Please see {@link CActiveForm::options} for availablel options.
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
					beforeValidateAttribute : settings.beforeValidateAttribute,
					afterValidateAttribute : settings.afterValidateAttribute,
					validatingCssClass : settings.validatingCssClass
				}, attribute);
				settings.attributes[i].value = $('#'+attribute.inputID).val();
			});
			$(this).data('settings', settings);

			var submitting=false;  // whether it is waiting for ajax submission result
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

				if(settings.timer!=undefined) {
					clearTimeout(settings.timer);
				}

				settings.timer = setTimeout(function(){
					if(submitting)
						return;
					if(attribute.beforeValidateAttribute==undefined || attribute.beforeValidateAttribute($form, attribute)) {
						$.each(settings.attributes, function(){
							if (this.status == 2) {
								this.status = 3;
								$.fn.yiiactiveform.getInputContainer(this).addClass(this.validatingCssClass);
							}
						});
						$.fn.yiiactiveform.validate($form, function(data) {
							var hasError=false;
							$.each(settings.attributes, function(){
								if (this.status > 0) {
									hasError = $.fn.yiiactiveform.updateInput(this, data) || hasError;
								}
							});
							if(attribute.afterValidateAttribute!=undefined) {
								attribute.afterValidateAttribute($form,attribute,data,hasError);
							}
						});
					}
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
					$form.data('submitObject',$(this));
				});
				var validated = false;
				$form.submit(function(){
					if (validated)
						return true;
					if(settings.timer!=undefined) {
						clearTimeout(settings.timer);
					}
					submitting=true;
					if(settings.beforeValidate==undefined || settings.beforeValidate($form)) {
						$.fn.yiiactiveform.validate($form, function(data){
							var hasError = false;
							$.each(settings.attributes, function(i, attribute){
								hasError = $.fn.yiiactiveform.updateInput(attribute, data) || hasError;
							});
							$.fn.yiiactiveform.updateSummary($form, data);
							if(settings.afterValidate==undefined || settings.afterValidate($form, data, hasError)) {
								if(!hasError) {
									validated = true;
									var $button = $form.data('submitObject') || $form.find(':submit:first');
									// TODO: if the submission is caused by "change" event, it will not work
									if ($button.length)
										$button.click();
									else  // no submit button in the form
										$form.submit();
									return false;
								}
							}
							submitting=false;
						});
					}
					else {
						submitting=false;
					}
					return false;
				});
			}

			/*
			 * In case of reseting the form we need to reset error messages
			 * NOTE1: $form.reset - does not exist
			 * NOTE2: $form.live('reset',...) does not work
			 */
			$form.bind('reset',function(){
				/*
				 * because we bind directly to a form reset event, not to a reset button (that could or could not exist),
				 * when this function is executed form elements values have not been reset yet,
				 * because of that we use the setTimeout
				 */
				setTimeout(function(){
					$.each(settings.attributes, function(i, attribute){
						attribute.status = 0;
						var $error = $('#'+attribute.errorID);
						var $container = $.fn.yiiactiveform.getInputContainer(attribute);

						$container
							.removeClass(attribute.validatingCssClass)
							.removeClass(attribute.errorCssClass)
							.removeClass(attribute.successCssClass);

						$error.html('').hide();

						/*
						 * without the setTimeout() call val() would return the entered value instead of the reseted value
						 */
						attribute.value = $('#'+attribute.inputID).val();

						/*
						 * If the form is submited (non ajax) with errors, labels and input gets the class 'error'
						 */
						$('label,input',$form).each(function(){
							$(this).removeClass('error');
						});
					});
					$('#'+settings.summaryID+' ul').html('');
					$('#'+settings.summaryID).hide();
					//.. set to initial focus on reset
					if(settings.focus != undefined && !window.location.hash)
						$(settings.focus).focus();
				},1);
			});

			/*
			 * set to initial focus
			 */
			if(settings.focus != undefined && !window.location.hash)
				$(settings.focus).focus();
		});
	};

	/**
	 * Returns the container element of the specified attribute.
	 * @param attribute object the configuration for a particular attribute.
	 * @return jquery the jquery representation of the container
	 */
	$.fn.yiiactiveform.getInputContainer = function(attribute) {
		if(attribute.inputContainer == undefined)
			return $('#'+attribute.inputID).closest('div');
		else
			return $(attribute.inputContainer).filter(':has("#'+attribute.inputID+'")');
	};

	/**
	 * updates the error message and the input container for a particular attribute.
	 * @param attribute object the configuration for a particular attribute.
	 * @param messages array the json data obtained from the ajax validation request
	 * @return boolean whether there is a validation error for the specified attribute
	 */
	$.fn.yiiactiveform.updateInput = function(attribute, messages) {
		attribute.status = 1;
		var hasError = messages!=null && $.isArray(messages[attribute.inputID]) && messages[attribute.inputID].length>0;
		var $error = $('#'+attribute.errorID);
		var $container = $.fn.yiiactiveform.getInputContainer(attribute);
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

	/**
	 * updates the error summary, if any.
	 * @param form jquery the jquery representation of the form
	 * @param messages array the json data obtained from the ajax validation request
	 */
	$.fn.yiiactiveform.updateSummary = function(form, messages) {
		var settings = $(form).data('settings');
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
	};

	/**
	 * Performs the ajax validation request.
	 * This method is invoked internally to trigger the ajax validation.
	 * @param form jquery the jquery representation of the form
	 * @param successCallback function the function to be invoked if the ajax request succeeds
	 * @param errorCallback function the function to be invoked if the ajax request fails
	 */
	$.fn.yiiactiveform.validate = function(form, successCallback, errorCallback) {
		var $form = $(form);
		var settings = $form.data('settings');
		$.ajax({
			url : settings.validationUrl,
			type : $form.attr('method'),
			data : $form.serialize()+'&'+settings.ajaxVar+'='+$form.attr('id'),
			dataType : 'json',
			success : function(data) {
				if (data != null && typeof data == 'object') {
					successCallback(data);
				}
			},
			error : function() {
				if (errorCallback!=undefined) {
					errorCallback();
				}
			}
		});
	};

	/**
	 * Returns the configuration for the specified form.
	 * The configuration contains all needed information to perform ajax-based validation.
	 * @param form jquery the jquery representation of the form
	 * @return object the configuration for the specified form.
	 */
	$.fn.yiiactiveform.getSettings = function(form) {
		return $(form).data('settings');
	};

	$.fn.yiiactiveform.defaults = {
		ajaxVar: 'ajax',
		validationUrl: undefined,
		validationDelay: 200,
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
		beforeValidateAttribute: undefined, // function(form, attribute) : boolean
		afterValidateAttribute: undefined,  // function(form, attribute, data, hasError)
		beforeValidate: undefined, // function(form) : boolean
		afterValidate: undefined,  // function(form, data, hasError) : boolean
		/**
		 * list of attributes to be validated. Each array element is of the following structure:
		 * {
		 *     model : 'ModelClass', // the model class name
		 *     name : 'name', // attribute name
		 *     inputID : 'input-tag-id',
		 *     errorID : 'error-tag-id',
		 *     value : undefined,
		 *     status : 0,  // 0: empty, not entered before,  1: validated, 2: pending validation, 3: validating
		 *     focus : undefined,  // jquery selector that indicates which element to receive input focus initially
		 *     validationDelay: 200,
		 *     validateOnChange : true,
		 *     validateOnType : false,
		 *     hideErrorMessage : false,
		 *     inputContainer : undefined,
		 *     errorCssClass : 'error',
		 *     successCssClass : 'success',
		 *     validatingCssClass : 'validating',
		 *     beforeValidateAttribute: undefined, // function(form, attribute) : boolean
		 *     afterValidateAttribute: undefined,  // function(form, attribute, data, hasError)
		 * }
		 */
		attributes : []
	};

})(jQuery);