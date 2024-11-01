var box = 1;
var custombox = 1;
var x = 1;
var y = 1;

/*
 * To add and delete custom fields in custom field creater page 
 */
jQuery(document).ready(function() {	

	if( jQuery("#add_custom_field").length > 0 ) {
		jQuery("#add_custom_field").click(function(){
	        jQuery("#dynamic-custom-field-list").append('<li id="custom-field-list['+x+']"> \
	        	<input type="text" name="option_page_name[field_name'+x+']" id="field_name'+x+'" />\
	        	<a href="javascript:void(0);" class="remove_field">\
	        	<input type="button" value="X">\
	        	</a>\
	        	</li>');
	        x++;	   
	    });	
	}
    
    jQuery("#dynamic-custom-field-list").on("click",".remove_field", function(){ 
		jQuery(this).parent('li').remove();
	})
  
});

/*
 * To add and delete custom fields in post page
 */
jQuery(document).ready(function() {	 
	
	jQuery("#add_fields_of_post").click(function(){

		jQuery.ajax({
		    type: "POST",
		    url: ajaxurl,
		    data: {
		    	action: 'wp_cp_get_customfield',
		    	security: wp_cp_comparison_ajax_obj.nonce, 
		    },

		    success: function (data) {          
			    jQuery("#dynamic-list").append('<li id="'+box+'"> \
			    	<select name="custom_fields['+box+']" id="custom_fields['+box+']">\
			    	<option value="">Select</option> \
			    	</select>\
			    		<input type="text" placeholder="Value" name="custom_fields[value'+box+']" id="custom_fields[value'+box+']" />\
			    		<a href="javascript:void(0);" class="remove_field">\
			    	<input type="button" value="X"></a></li>');

		        for (var i = 0; i < data.length; i++) {
		        	//jQuery('#custom_fields['+box+']').append('<option value="'+data[i]+'">'+data[i]+'</option>');
		        	document.getElementById('custom_fields['+box+']').innerHTML+='<option value="'+data[i]+'">'+data[i]+'</option>';
				}
				box++;		
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
		});
        	   
    });	
    
    jQuery("#dynamic-list").on("click",".remove_field", function(){ 
		jQuery(this).parent('li').remove();
	});
  
});


/**
* @param arr array of custom fields 
*
* @param countBox id of the next custom fields to add
*
* Add extra custom fields in custom post fields and displaying the custom fields in the  selectbox
*/ 
function addField(countBox,arr)
{
	var myList = document.getElementById(countBox);
	if (myList){
		countBox = countBox + 1;
		addField(countBox,arr);
	}
	else{
		jQuery("#dynamic-list").append('<li id="'+countBox+'"> \
			<select name="custom_fields['+countBox+']" id="custom_fields['+countBox+']"></select>\
			 <input type="text" placeholder="Value" name="custom_fields[value'+countBox+']" id="custom_fields[value'+countBox+']" />\
			  <a href="javascript:void(0);" class="remove_field">\
			  <input type="button" value="X">\
			  </a> \
			  </li>');

		for (var i = 0; i < arr.length; i++) {
			document.getElementById('custom_fields['+countBox+']').innerHTML+='<option value="'+arr[i]+'">'+arr[i]+'</option>';
		}
		jQuery("#custom_fields["+countBox+"] option:selected").attr('disabled','disabled');
		
	}
	jQuery("#dynamic-list").on("click",".remove_field", function(){ 
			jQuery(this).parent('li').remove();
		});
    
}

/**
 * @param id of the li to delete a custom field in post fields
 *
 * Delete custom field in post fields
 */
function deleteField(id){
	var myList = document.getElementById(id);
	myList.innerHTML = '';
}


/**
 * @param id of the li to delete a custom field in custom field creator page
 *
 * Delete custom field in custom field creator page
 */
function deleteCustomField(id){
	var myList = document.getElementById(id);
	myList.innerHTML = '';
}

/**
 * @param id of the custom post type
 *
 * Delete the custom post type by calling php method using ajax
 */

function deletePostType(id){
	var result = confirm("Want to delete?");
	if (result) {
		jQuery.ajax({
		    type: "POST",
		    url: ajaxurl,
		    data: {
		    	action: 'wp_cp_comparison_deletepost',
		    	security: wp_cp_comparison_ajax_obj.nonce, 
		    	id: id
		    },

		    success: function (data) {            
			    window.location.reload();		
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
		});
	}
}

/**
 * @param id of custom post type
 *
 * Calling Ajax for getting the details of the custom post type and display it on the custom post type      creator page and edit the details
 */

function editPostType(id){
	var supports_array = ["title","editor","author","thumbnail","trackbacks","custom-fields","comments","revisions","page-attributes","post-formats"];
	var taxonomie_array = ["category","post_tag","product_cat","product_tag","product_shipping_class"];
	jQuery.ajax({
	    type: "POST",
	    url: ajaxurl,	    
	    data: {
	    	action: 'wp_cp_comparison_editPost', 
	    	security: wp_cp_comparison_ajax_obj.nonce,
	    	id: id
	    },

	    success: function (data) {
	    	jQuery.each(data.data, function (key, value) {
	    		console.log(key);
	    		if (Array.isArray(value)){
	    			if (key == "supports"){
	    				for (var i = 0; i < supports_array.length; i++) {
		    				if (value.includes(supports_array[i])){
		    					document.getElementById(supports_array[i]).checked = true;
		    				}
		    			}	
	    			}
	    			else{
	    				for (var i = 0; i < taxonomie_array.length; i++) {
		    				if (value.includes(taxonomie_array[i])){
		    					document.getElementById(taxonomie_array[i]).checked = true;
		    				}
		    			}	
	    			}
	    			
	    		}
	    		else{
	    			document.getElementById(key).value = value ;	
	    		}
	    		document.body.scrollTop = 0;
  				document.documentElement.scrollTop = 0;
					
		    });        
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
	});	

}


