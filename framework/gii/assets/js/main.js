$(document).ready(function() {
	$('.common-generator .check-all a').click(function() {
		if($(this).hasClass('all'))
			$(".common-generator td input").attr('checked', 'checked');
		else if($(this).hasClass('none'))
			$(".common-generator td input").removeAttr('checked');
		return false;
	});

	$('.common-generator .giiform .row input').tooltip({
	    position: "center right",
		offset: [-2, 10]
	});

	$('.giiform .row input').change(function(){
		$('.giiform .feedback').hide();
		$('.giiform input[name="generate"]').hide();
	});

	$('#fancybox-inner .close-code').live('click', function(){
		$.fancybox.close();
		return false;
	});

	$('.giiform .view-code').click(function(){
		$.fancybox.showActivity();
		$.ajax({
			type: 'POST',
			cache: false,
			url: $(this).attr('href'),
			data: $('.giiform form').serializeArray(),
			success: function(data){
				$.fancybox(data, {
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
});