/*
 ### jQuery Star Rating Plugin v2.61 - 2009-01-23 ###
 * Home: http://www.fyneworks.com/jquery/star-rating/
 * Code: http://code.google.com/p/jquery-star-rating-plugin/
 *
	* Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 ###
*//*
	Based on http://www.phpletter.com/Demo/Jquery-Star-Rating-Plugin/
 Original comments:
	This is hacked version of star rating created by <a href="http://php.scripts.psu.edu/rja171/widgets/rating.php">Ritesh Agrawal</a>
	It transforms a set of radio type input elements to star rating type and remain the radio element name and value,
	so could be integrated with your form. It acts as a normal radio button.
	modified by : Logan Cai (cailongqun[at]yahoo.com.cn)
	
 Last modified by Qiang Xue on Feb. 26, 2009
    added support to allow input names like post[rating] (previously this won't work).	
*/

/*# AVOID COLLISIONS #*/
;if(window.jQuery) (function($){
/*# AVOID COLLISIONS #*/
	
	// IE6 Background Image Fix
	if ($.browser.msie) try { document.execCommand("BackgroundImageCache", false, true)} catch(e) { }
	// Thanks to http://www.visualjquery.com/rating/rating_redux.html
	
	// default settings
	$.rating = {
		cancel: 'Cancel Rating',   // advisory title for the 'cancel' link
		cancelValue: '',           // value to submit when user click the 'cancel' link
		split: 0,                  // split the star into how many parts?
		
		// Width of star image in case the plugin can't work it out. This can happen if
		// the jQuery.dimensions plugin is not available OR the image is hidden at installation
		starWidth: 16,
		
		//NB.: These don't need to be defined (can be undefined/null) so let's save some code!
		//half:     false,         // just a shortcut to settings.split = 2
		//required: false,         // disables the 'cancel' button so user can only select one of the specified values
		//readOnly: false,         // disable rating plugin interaction/ values cannot be changed
		//focus:    function(){},  // executed when stars are focused
		//blur:     function(){},  // executed when stars are focused
		//callback: function(){},  // executed when a star is clicked
		
		// required properties:
		groups: {},// allows multiple star ratings on one page
		event: {// plugin event handlers
			fill: function(n, el, settings, state){ // fill to the current mouse position.
				//if(window.console) console.log(['fill', $(el), $(el).prevAll('.star_group_'+n.replace(/\[|\]/g, "_")), arguments]);
				this.drain(n);
				$(el).prevAll('.star_group_'+n.replace(/\[|\]/g, "_")).andSelf().addClass('star_'+(state || 'hover'));
				// focus handler, as requested by focusdigital.co.uk
				var lnk = $(el).children('a'); val = lnk.text();
				if(settings.focus) settings.focus.apply($.rating.groups[n].valueElem[0], [val, lnk[0]]);
			},
			drain: function(n, el, settings) { // drain all the stars.
				//if(window.console) console.log(['drain', $(el), $(el).prevAll('.star_group_'+n.replace(/\[|\]/g, "_")), arguments]);
				$.rating.groups[n].valueElem.siblings('.star_group_'+n.replace(/\[|\]/g, "_")).removeClass('star_on').removeClass('star_hover');
			},
			reset: function(n, el, settings){ // Reset the stars to the default index.
				if(!$($.rating.groups[n].current).is('.cancel'))
					$($.rating.groups[n].current).prevAll('.star_group_'+n.replace(/\[|\]/g, "_")).andSelf().addClass('star_on');
				// blur handler, as requested by focusdigital.co.uk
				var lnk = $(el).children('a'); val = lnk.text();
				if(settings.blur) settings.blur.apply($.rating.groups[n].valueElem[0], [val, lnk[0]]);
			},
			click: function(n, el, settings){ // Selected a star or cancelled
				$.rating.groups[n].current = el;
				var lnk = $(el).children('a'); val = lnk.text();
				// Set value
				$.rating.groups[n].valueElem.val(val);
				// Update display
				$.rating.event.drain(n, el, settings);
				$.rating.event.reset(n, el, settings);
				// click callback, as requested here: http://plugins.jquery.com/node/1655
				if(settings.callback) settings.callback.apply($.rating.groups[n].valueElem[0], [val, lnk[0]]);
			}      
		}// plugin events
	};
	
	$.fn.rating = function(instanceSettings){
		if(this.length==0) return this; // quick fail
		
		instanceSettings = $.extend(
			{}/* new object */,
			$.rating/* global settings */,
			instanceSettings || {} /* just-in-time settings */
		);
		
		// loop through each matched element
		this.each(function(i){
			
			var settings = $.extend(
				{}/* new object */,
				instanceSettings || {} /* current call settings */,
				($.metadata? $(this).metadata(): ($.meta?$(this).data():null)) || {} /* metadata settings */
			);
			
			////if(window.console) console.log([this.name, settings.half, settings.split], '#');
			
			// Generate internal control ID
			var n = (this.name || 'unnamed-rating');
   
			// Grouping
			if(!$.rating.groups[n]) $.rating.groups[n] = {count: 0};
			i = $.rating.groups[n].count; $.rating.groups[n].count++;
			
			// Accept readOnly setting from 'disabled' property
			$.rating.groups[n].readOnly = $.rating.groups[n].readOnly || settings.readOnly || $(this).attr('disabled');
			
			// Things to do with the first element...
			if(i == 0){
				// Create value element (disabled if readOnly)
				$.rating.groups[n].valueElem = $('<input type="hidden" name="' + n + '" value=""' + (settings.readOnly ? ' disabled="disabled"' : '') + '/>');
				// Insert value element into form
				$(this).before($.rating.groups[n].valueElem);
				
				if($.rating.groups[n].readOnly || settings.required){
					// DO NOT display 'cancel' button
				}
				else{
					// Display 'cancel' button
					$(this).before(
						$('<div class="cancel"><a title="' + settings.cancel + '">' + settings.cancelValue + '</a></div>')
						.mouseover(function(){ $.rating.event.drain(n, this, settings); $(this).addClass('star_on'); })
						.mouseout(function(){ $.rating.event.reset(n, this, settings); $(this).removeClass('star_on'); })
						.click(function(){ $.rating.event.click(n, this, settings); })
					);
				}
			}; // if (i == 0) (first element)
			
			// insert rating option right after preview element
			eStar = $('<div class="star"><a title="' + (this.title || this.value) + '">' + this.value + '</a></div>');
			$(this).after(eStar);
			
			// Half-stars?
			if(settings.half) settings.split = 2;
			
			// Prepare division settings
			if(typeof settings.split=='number' && settings.split>0){
				var stw = ($.fn.width ? $(eStar).width() : 0) || settings.starWidth;
				var spi = (i % settings.split), spw = Math.floor(stw/settings.split);
				$(eStar)
				// restrict star's width and hide overflow (already in CSS)
				.width(spw)
				// move the star left by using a negative margin
				// this is work-around to IE's stupid box model (position:relative doesn't work)
				.find('a').css({ 'margin-left':'-'+ (spi*spw) +'px' })
			};
			
			// Remember group name so controls within the same container don't get mixed up
			$(eStar).addClass('star_group_'+n.replace(/\[|\]/g, "_"));
			
			// readOnly?
			if($.rating.groups[n].readOnly)//{ //save a byte!
				// Mark star as readOnly so user can customize display
				$(eStar).addClass('star_readonly');
			//}  //save a byte!
			else//{ //save a byte!
				$(eStar)
				// Enable hover css effects
				.addClass('star_live')
				// Attach mouse events
				.mouseover(function(){ $.rating.event.drain(n, this, settings); $.rating.event.fill(n, this, settings, 'hover'); })
				.mouseout(function(){ $.rating.event.drain(n, this, settings); $.rating.event.reset(n, this, settings); })
				.click(function(){ $.rating.event.click(n, this, settings); });
			//}; //save a byte!
			
			////if(window.console) console.log(['###', n, this.checked, $.rating.groups[n].initial]);
			if(this.checked) $.rating.groups[n].current = eStar;
			
			//remove this checkbox
			$(this).remove();
			
			// reset display if last element
			if(i + 1 == this.length) $.rating.event.reset(n, this, settings);
		
		}); // each element
			
		// initialize groups...
		for(n in $.rating.groups)//{ not needed, save a byte!
			(function(c, v, n){ if(!c) return;
				$.rating.event.fill(n, c, instanceSettings || {}, 'on');
				$(v).val($(c).children('a').text());
			})
			($.rating.groups[n].current, $.rating.groups[n].valueElem, n);
		//}; not needed, save a byte!
		
		return this; // don't break the chain...
	};
	
	
	
	/*
		### Default implementation ###
		The plugin will attach itself to file inputs
		with the class 'multi' when the page loads
	*/
	$(function(){ $('input[type=radio].star').rating(); });
	
	
	
/*# AVOID COLLISIONS #*/
})(jQuery);
/*# AVOID COLLISIONS #*/
