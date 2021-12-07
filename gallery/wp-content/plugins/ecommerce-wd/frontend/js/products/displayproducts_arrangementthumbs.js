jQuery(document).ready(function () {
	Change_productpanelWidth();
});

jQuery( window ).resize(function() {
  Change_productpanelWidth();
});

function Change_productpanelWidth(){
	if(jQuery("#wd_shop_for_width").outerWidth(true)<242){
		jQuery('.wd_shop_bottom_checkout_buttons').each(function () {			
			jQuery(this).removeClass( "pull-right").addClass( "clear_buttons_float" );
		});
	}
	else{
		jQuery('.wd_shop_bottom_checkout_buttons').each(function () {			
			jQuery(this).removeClass( "clear_buttons_float").addClass( "pull-right" );
		});
	}
}