jQuery(document).ready(function($) {
  "use strict";
$("#user_menu_con li span").click(function(event){
	event.stopPropagation();
	$('.anony-user-dropdown').toggle();
	var $j  = $(this).find("i");
	$j.each(function(){
		if($(this).hasClass('fa-angle-down')){
			$(this).removeClass('fa-angle-down').addClass('fa-angle-up');
		}else{
			$(this).removeClass('fa-angle-up').addClass('fa-angle-down');
		}
	});
});

$(document).click( function(){
$('.anony-user-dropdown').hide();
$('#user_menu_con li span i').each(function(){
				if($(this).hasClass("fa-angle-up")){
					$(this).removeClass('fa-angle-up').addClass('fa-angle-down');
				}

			});
});
});
