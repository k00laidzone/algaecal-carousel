    jQuery(document).ready( function( $ ) {
    	 //$(document).ready(function() {

            $('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:3,
                slideMargin: 0,
                speed:500,
                auto:false,
                loop:true,
				adaptiveHeight: true,
                onSliderLoad: function() {
                    $('#image-gallery').removeClass('cS-hidden');
                }  
            });
		});