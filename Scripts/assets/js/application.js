//
// LUSH EFFECTS PREVIWER 
// ++++++++++++++++++++++++++++++++++++++++++

!function ($) {

  $(function(){

    var $window = $(window)

    // side bar
    $('#effect-preview-block').affix({
      offset: {
        top: 50
      }
    })
    
    var $effectList = $('.effect-list');
    for(e in effectList) {
    	$effectList.append('<li><a href="#">' + effectList[e] + '</a></li>')
    }

	$(document)
		.on('mouseenter', '.effect-list > li > a', function(e) {
			e.preventDefault();
			var $this = $(this);

			lushDemoPreview($this.text())
		})
		.on('mouseleave', '.effect-list > li > a', function() {
			lushDemoPreview(false)
		})    

  })

	function lushDemoPreview(effectName) {
		var markup, $slider,
			dataIn, dataOut,
			$previewBlock = $('#effect-preview-block > .lush-slider')
	
		if(effectName === false && $previewBlock.length) {
			$previewBlock.remove();
			return				
		}
	
		if(effectName == "") return;
		
		//if(typeof createEffect != 'undefined')
			//$('#custom-css').text(createEffect(effectName))
		
		dataIn  = ' data-slide-in="at 300 from ' + effectName + ' during 1000"'
		dataOut = ' data-slide-out="at 500 to ' + effectName + ' during 500"'
	
		markup = '<div class="lush-slider no-skin no-shadow">' +
				   		'<div class="lush">' +
					  		'<div id="demo-effect-block" ' + dataIn + dataOut + '>Demo effect</div>'+
				 '</div></div>';
	
		$previewBlock = $(markup).appendTo('#effect-preview-block');
		
		$previewBlock.show()
						
		$('.lush-slider').lush({
			baseWidth: 748,
			baseHeight: 300,
			slider: {
				navigation: false,
				responsive: false,
				shadow: false
			}
		})			
	}


}(window.jQuery)


var effectList = [
	'left',
	'right',
	'top',
	'bottom',
	'left-bottom',
	'right-bottom',
	'right-top',
	'left-top',
	'fade',
	'left-fade',
	'right-fade',
	'top-fade',
	'bottom-fade',
	'front',
	'back',
	'left-top-rotate',
	'right-top-rotate',
	'left-bottom-rotate',
	'right-bottom-rotate',
	'front-rotate',
	'back-rotate',
	'front-rotate2',
	'back-rotate2',
	'back-left',
	'back-right',
	'back-bottom',
	'back-top',
	'front-left',
	'back-right',
	'front-bottom',
	'front-top',
	'flip',
	'flip-h',
	'roll-top',
	'roll-bottom',
	'roll-left',
	'roll-right',
	'bounce',
	'bounce-left',
	'bounce-right',
	'bounce-top',
	'bounce-bottom',
	'rotate',
	'left-down-rotate',
	'right-down-rotate',
	'left-up-rotate',
	'right-up-rotate',
	'left-speed',
	'right-speed',
	'hinge'
];
