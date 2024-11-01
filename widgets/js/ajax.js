		function proposeLocationWithAjax(){
			// get name from form input
			var thename = jQuery("input#location").val();
			//var adata = 
	
			jQuery.post(
    			// see tip #1 for how we declare global javascript variables
    			ebAjaxSearchLocation.ajaxurl,
    			{
        			// here we declare the parameters to send along with the request
        			// this means the following action hooks will be fired:
        			// wp_ajax_nopriv_ and wp_ajax_
        			action : 'eb_ajax_hook',
			 
        			// other parameters can be added along with "action"
        			name : 'Test'
    			},
    			function( response ) {
        			alert( response + " Dude..." );
    			}
			);
		}
		
		
		function submit_me(){
// get name from form input
var thename = jQuery("input#name").val();
jQuery.post(the_ajax_script.ajaxurl, { 'action': 'the_ajax_hook' } ,
 function(response_from_the_action_function){
// jQuery("#response_area").html(response_from_the_action_function);
alert('duude: '+response_from_the_action_function);
 }
 );
}