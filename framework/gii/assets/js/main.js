$(document).ready(function() {
	$('.common-generator .check-all a').click(function() {
		if($(this).hasClass('all'))
			$(".common-generator td input").attr('checked', 'checked');
		else if($(this).hasClass('none'))
			$(".common-generator td input").removeAttr('checked');
		return false;
	});
	/*
	$('.giiform .row input').tooltip({
	    position: "center right",
		offset: [-2, 10]
	});
	*/

	$('.giiform .row input').change(function(){
		$('.giiform .feedback').hide();
		$('.giiform input[name="generate"]').hide();
	});
});