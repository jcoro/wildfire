





$(function() {
    // setup ul.tabs to work as tabs for each div directly under div.panes
    $("ul.tabs").tabs("div.panes > div");
});


$(document).ready(function () { 
     
    $('#nav li').hover(
        function () {
            //show its submenu
            $('ul', this).slideDown(100);
 
        }, 
        function () {
            //hide its submenu
            $('ul', this).slideUp(100);         
        }
    );
     
});

