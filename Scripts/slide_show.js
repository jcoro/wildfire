/*

*/
$(document).ready(function(){
$.slide_show = function() {
		var id = "#slide_" + show_id;
		if(!start){
			$(previous).fadeOut(1000);
			$(id).delay(0).fadeIn(1000);
		}
		if (show_id == count){
			show_id = 1;
		}
		else{show_id++;}
		previous = id;
		if(start){
			setTimeout("$.set_timer()",500);
			start = false;
		}
	};
	$.set_timer = function() {
		setInterval( "$.slide_show()", 5000);
	}
	var start = true;
	var previous = "";
	var count = $(".slider li").length;
	var show_id = 1;
	$('.slider li:first').show();
	$.slide_show();
	
	
});   
