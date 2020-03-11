jQuery(document).ready(function($) {
	"use strict";

	$('#anony-uc-menu-toggle').on('click', function(e){
		e.preventDefault();
		$('.anony-user-dropdown').toggle();

		var $j  = $(this).find("i");
		$j.each(function(){
			if($(this).hasClass('fa-toggle-down')){
				$(this).removeClass('fa-toggle-down').addClass('fa-toggle-up');
			}else{
				$(this).removeClass('fa-toggle-up').addClass('fa-toggle-down');
			}
		});

	});
});
