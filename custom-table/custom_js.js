$(document).on('click','#show,#hide',function (){
var get_text = $(this).text();
var button_name =""; 
if(get_text =="hide"){
	button_name = get_text;
	var option = $('#hide_column').val();
	var option_value = $('#hide_column').find(":selected").text();
	var option_key = $('#hide_column').find(":selected").val();
}
if(get_text =="show"){
	button_name = get_text;
	var option = $('#expand_column').val();
	var option_value = $('#expand_column').find(":selected").text();
	var option_key = $('#expand_column').find(":selected").val();

}
	console.log(option);
	    if (option != '') {
				$.ajax({ 
					url: ajax_object.ajax_url,
			     	data: {action: 'show_columns',option_value:option_value,option_key:option_key,button_name:button_name},
			     	type : 'POST',
		     success: function(output) {
				 console.log(output);
				 location.reload();	
		       }
			});

    	}	
});