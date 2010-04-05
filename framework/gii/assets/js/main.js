$(document).ready(function() {
	$('.common-generator input[name="checkAll"]').click(function() {
		$('.common-generator .confirm input').attr('checked', this.checked);
	});

	$('.common-generator td.confirm input').click(function() {
		$('.common-generator input[name="checkAll"]').attr('checked', !$('.common-generator td.confirm input:not(:checked)').length);
	});
	$('.common-generator input[name="checkAll"]').attr('checked', !$('.common-generator td.confirm input:not(:checked)').length);


	$('.form .row.sticky input:not(.error), .form .row.sticky select:not(.error), .form .row.sticky textarea:not(.error)').each(function(){
		var value;
		if(this.tagName=='SELECT')
			value=this.options[this.selectedIndex].text;
		else if(this.tagName=='TEXTAREA')
			value=$(this).html();
		else
			value=$(this).val();
		if(value=='')
			value='[empty]';
		$(this).before('<div class="value">'+value+'</div>').hide();
	});

	$('.form.gii .row.sticky .value').live('click', function(){
		$(this).hide();
		$(this).next().show().get(0).focus();
	});


	$('.form.gii .row input, .form.gii .row select').tooltip({
	    position: "center right",
		offset: [-2, 10]
	});

	$('.form.gii .row input').change(function(){
		$('.form.gii .feedback').hide();
		$('.form.gii input[name="generate"]').hide();
	});

	$('.form.gii .view-code').click(function(){
		var title=$(this).attr('rel');
		$.fancybox.showActivity();
		$.ajax({
			type: 'POST',
			cache: false,
			url: $(this).attr('href'),
			data: $('.form.gii form').serializeArray(),
			success: function(data){
				$.fancybox(data, {
					'title': title,
					'titlePosition': 'inside',
					'titleFormat': function(title, currentArray, currentIndex, currentOpts) {
						return '<div id="tip7-title"><span><a href="javascript:;" onclick="$.fancybox.close();">close</a></span>' + (title && title.length ? '<b>' + title + '</b>' : '' ) + '</div>';
					},
					'showCloseButton': false,
					'autoDimensions': false,
					'width': 800,
					'height': 'auto'
				});
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$.fancybox('<div class="error">'+XMLHttpRequest.responseText+'</div>');
			}
		});
		return false;
	});

	$('#fancybox-inner .close-code').live('click', function(){
		$.fancybox.close();
		return false;
	});
});