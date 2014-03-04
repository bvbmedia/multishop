/**
 * @author Saepulloh
 * @date 29 Juni 2010
 */
(function(jQuery){
    var ValidationErrors = new Array();
    jQuery.fn.validate = function(options){
        options = jQuery.extend({
            expression: "return true;",
            message: "",
            error_class: "error-no",
            valid_class: "error-yes",
            default_class: "error-space",
            error_field_class: "ErrorField",
            pengecualian: "left-this",
            live: true
        }, options);
        var SelfID = jQuery(this).attr("id");
        var namThisClass = jQuery(this).attr("class");
       // console.log(namThisClass);
        var getClass = "";
        if (namThisClass == undefined){
            getClass = namThisClass;
        } else {
            getClass = namThisClass.split(' ')[0];
        }
       
       
        var unix_time = new Date();
        unix_time = parseInt(unix_time.getTime() / 1000);
        if (!jQuery(this).parents('form:first').attr("id")) {
            jQuery(this).parents('form:first').attr("id", "Form_" + unix_time);
        }
        var FormID = jQuery(this).parents('form:first').attr("id");
        if (!((typeof(ValidationErrors[FormID]) == 'object') && (ValidationErrors[FormID] instanceof Array))) {
            ValidationErrors[FormID] = new Array();
        }
        if (options['live']) {
        	
            if (jQuery(this).find('input').length > 0) {
				 
                jQuery(this).find('input').bind('blur', function(){
					if (validate_field("#" + SelfID, options)) {
                        if (options.callback_success) 
                            options.callback_success(this);
                    }
                    else {
                        if (options.callback_failure) 
                            options.callback_failure(this);
                    }
                });
                
                jQuery(this).find('input').bind('focus keypress click', function(){
                    //jQuery("#" + SelfID).next('.' + options['error_class']).remove();
                	jQuery("#" + SelfID).next().removeClass(options['error_class']);
                	jQuery("#" + SelfID).next().addClass(options['valid_class']);
                    jQuery("#" + SelfID).removeClass(options['error_field_class']);
                    /**
                    jQuery(".ul-display-error").find("li").remove("."+getClass);
                    if (jQuery(".item-error").length == 1 || jQuery(".item-error").length== 0) {
                    	jQuery(".error_msg").fadeOut("fast");
                    }
                    */
                });
               
            }
            else {
            	var bind_var = 'blur keyup keypress change click';
            	
                jQuery(this).bind(bind_var, function(){
                    validate_field(this);
                   
                });
                jQuery(this).bind("click focus", function(){
                	jQuery(this).next('.' + options['error_class']).removeClass(options['error_class']);
                	/**
                	jQuery(".ul-display-error").find("li").remove("."+getClass);
                    if (jQuery(".item-error").length == 1 || jQuery(".item-error").length== 0) {
                    	jQuery(".error_msg").fadeOut("fast");
                    }
                    */
                });
                jQuery(this).bind("keyup", function(){
                	 if (jQuery(this).val() == ''){
                     	jQuery(this).next('.' + options['default_class']).removeClass(options['valid_class']);
                     	jQuery(this).next('.' + options['default_class']).removeClass(options['error_class']);
                     	/**
                     	jQuery(".ul-display-error").find("li").remove("."+getClass);
                     	
                        if (jQuery(".item-error").length == 1 || jQuery(".item-error").length== 0) {
                        	jQuery(".error_msg").fadeOut("fast");
                        }
                        */
     				}
                });
               
				
                //jQuery(this).bind('focus keypress', function(){
				//alert("tes");
                  //  jQuery(this).next('.' + options['error_class']).fadeOut("fast", function(){
                    //    jQuery(this).remove();
                    //});
                    //jQuery(this).removeClass(options['error_field_class']);
                //});
            }
        }
        jQuery(this).parents("form").submit(function(){
            if (validate_field_submit('#' + SelfID))
            	 return true;
            else 
                return false;
        });
        function validate_field(id){
            var self = jQuery(id).attr("id");
            var expression = 'function Validate(){' + options['expression'].replace(/VAL/g, 'jQuery(\'#' + self + '\').val()') + '} Validate()';
            
            var validation_state = eval(expression);
            if (!validation_state) {
            	
            	
            	switch(getClass)
            	{
            	case 'company':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		}
            		break;
            	case 'delivery_company':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		}
            		break;
            	case 'middle_name':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		} else {
	               		  if (jQuery(id).next('.' + options['error_class']).length == 0) {
	         					 jQuery(id).next().removeClass(options['valid_class']);
	                         	 jQuery(id).next().addClass(options['error_class']);
	                             jQuery(id).addClass(options['error_field_class']);
	                         }
	                         if (ValidationErrors[FormID].join("|").search(id) == -1) 
	                             ValidationErrors[FormID].push(id);
	                         return false;
	               	}
            	 break;
            	case 'delivery_mobile':
            		if (jQuery(id).next("."+options['pengecualian']).length != 0 ){
            	 		return true;
	               	}  else {
	               		if (jQuery(id).val() == ''){
	            			jQuery(id).next().addClass(options['valid_class']);
	            			return true;
	            		} else {
	            			if (jQuery(id).next('.' + options['error_class']).length == 0) {
	            				jQuery(id).next().removeClass(options['valid_class']);
	            				jQuery(id).next().addClass(options['error_class']);
	            				jQuery(id).addClass(options['error_field_class']);
	            			}
	            			if (ValidationErrors[FormID].join("|").search(id) == -1) 
	            				ValidationErrors[FormID].push(id);
	            			return false;
	            		}
	               	}
            		
            		break;
            	case 'mobile':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		} else {
            			if (jQuery(id).next('.' + options['error_class']).length == 0) {
            				jQuery(id).next().removeClass(options['valid_class']);
            				jQuery(id).next().addClass(options['error_class']);
            				jQuery(id).addClass(options['error_field_class']);
            				
            			}
            			if (ValidationErrors[FormID].join("|").search(id) == -1) 
            				ValidationErrors[FormID].push(id);
            			return false;
            		}
            		break;
            	default:
            	 	if (jQuery(id).next("."+options['pengecualian']).length != 0 ){
            	 		return true;
	               	}  else {
	               		  if (jQuery(id).next('.' + options['error_class']).length == 0) {
	         					 jQuery(id).next().removeClass(options['valid_class']);
	                         	 jQuery(id).next().addClass(options['error_class']);
	                             jQuery(id).addClass(options['error_field_class']);
	                         }
	                         if (ValidationErrors[FormID].join("|").search(id) == -1) 
	                             ValidationErrors[FormID].push(id);
	                         
	                         return false;
	               	}
            	}
            	
           
            }
            else {
            	//valid expresions
            	
               	 jQuery(id).next().removeClass(options['error_class']);
            	 jQuery(id).removeClass(options['error_field_class']);
            	 jQuery(id).next().addClass(options['valid_class']);
            	 
	                for (var i = 0; i < ValidationErrors[FormID].length; i++) {
	                    if (ValidationErrors[FormID][i] == id) 
	                        ValidationErrors[FormID].splice(i, 1);
	                }
                return true;
            }
        }
        
        //validation if submit was send
        function validate_field_submit(id){
            var self = jQuery(id).attr("id");
            var expression = 'function Validate(){' + options['expression'].replace(/VAL/g, 'jQuery(\'#' + self + '\').val()') + '} Validate()';
            
            var validation_state = eval(expression);
            if (!validation_state) {
            	
            	switch(getClass)
            	{
            	case 'company':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		}
            		break;
            	case 'delivery_company':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		}
            		break;
            	case 'middle_name':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		} else {
	               		  if (jQuery(id).next('.' + options['error_class']).length == 0) {
	         					 jQuery(id).next().removeClass(options['valid_class']);
	                         	 jQuery(id).next().addClass(options['error_class']);
	                             jQuery(id).addClass(options['error_field_class']);
	                         }
	               		  	//Display box message	
	               		  	if (jQuery(".ul-display-error").find("li").hasClass(getClass)){
                    	      //alert("tes");
	                          } else {
	                        	  jQuery("<li>").appendTo(".ul-display-error").text(options['message']).addClass(getClass).addClass("item-error");
	                          }
	                         if (ValidationErrors[FormID].join("|").search(id) == -1) 
	                             ValidationErrors[FormID].push(id);
	                         return false;
	               	}
            	 break;
            	case 'delivery_middle_name':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		} else {
            			if (jQuery(id).next('.' + options['error_class']).length == 0) {
            				jQuery(id).next().removeClass(options['valid_class']);
            				jQuery(id).next().addClass(options['error_class']);
            				jQuery(id).addClass(options['error_field_class']);
            			}
            			//Display box message	
            			if (jQuery(".ul-display-error").find("li").hasClass(getClass)){
            				//alert("tes");
            			} else {
            				jQuery("<li>").appendTo(".ul-display-error").text(options['message']).addClass(getClass).addClass("item-error");
            			}
            			if (ValidationErrors[FormID].join("|").search(id) == -1) 
            				ValidationErrors[FormID].push(id);
            			return false;
            		}
            		break;
            	case 'delivery_mobile':
            		if (jQuery(id).next("."+options['pengecualian']).length != 0 ){
            	 		return true;
	               	}  {
	               		if (jQuery(id).val() == ''){
	            			jQuery(id).next().addClass(options['valid_class']);
	            			return true;
	            		} else {
	            			if (jQuery(id).next('.' + options['error_class']).length == 0) {
	            				jQuery(id).next().removeClass(options['valid_class']);
	            				jQuery(id).next().addClass(options['error_class']);
	            				jQuery(id).addClass(options['error_field_class']);
	                           	 	
	            			}
	            			//Display box message	
	               		  	if (jQuery(".ul-display-error").find("li").hasClass(getClass)){
	                  	      //alert("tes");
	                          } else {
	                        	  jQuery("<li>").appendTo(".ul-display-error").text(options['message']).addClass(getClass).addClass("item-error");
	                          }
	            			if (ValidationErrors[FormID].join("|").search(id) == -1) 
	            				ValidationErrors[FormID].push(id);
	            			return false;
	            		}
	               	}
            		
            		break;
            	case 'mobile':
            		if (jQuery(id).val() == ''){
            			jQuery(id).next().addClass(options['valid_class']);
            			return true;
            		} else {
            			if (jQuery(id).next('.' + options['error_class']).length == 0) {
            				jQuery(id).next().removeClass(options['valid_class']);
            				jQuery(id).next().addClass(options['error_class']);
            				jQuery(id).addClass(options['error_field_class']);
            			}
            			//Display box message	
               		  	if (jQuery(".ul-display-error").find("li").hasClass(getClass)){
                  	      //alert("tes");
                          } else {
                        	  jQuery("<li>").appendTo(".ul-display-error").text(options['message']).addClass(getClass).addClass("item-error");
                          }
            			if (ValidationErrors[FormID].join("|").search(id) == -1) 
            				ValidationErrors[FormID].push(id);
            			return false;
            		}
            		break;
            	default:
            	 	if (jQuery(id).next("."+options['pengecualian']).length != 0 ){
            	 		return true;
	               	}  else {
	               		  if (jQuery(id).next('.' + options['error_class']).length == 0) {
	         					 jQuery(id).next().removeClass(options['valid_class']);
	                         	 jQuery(id).next().addClass(options['error_class']);
	                             jQuery(id).addClass(options['error_field_class']);
                            	 jQuery(".error_msg").fadeIn();
	                             
	                         }
	               		  	//Display box message	
	               		  	if (jQuery(".ul-display-error").find("li").hasClass(getClass)){
                      	      //alert("tes");
	                          } else {
	                        	  jQuery("<li>").appendTo(".ul-display-error").text(options['message']).addClass(getClass).addClass("item-error");
	                          }
	                         if (ValidationErrors[FormID].join("|").search(id) == -1) 
	                             ValidationErrors[FormID].push(id);
	                         //alert(getClass);
	                         return false;
	               	}
            	} //end switch
            	
           
            } // end for invalid validations
            else {
            	//valid expresions
            	
               	 jQuery(id).next().removeClass(options['error_class']);
            	 jQuery(id).removeClass(options['error_field_class']);
            	 jQuery(id).next().addClass(options['valid_class']);
            	 
	                for (var i = 0; i < ValidationErrors[FormID].length; i++) {
	                    if (ValidationErrors[FormID][i] == id) 
	                        ValidationErrors[FormID].splice(i, 1);
	                }
	                
	                jQuery(".ul-display-error").find("li").remove("."+getClass);
	                if (jQuery(".item-error").length== 1 || jQuery(".item-error").length == 0) {
	                	jQuery(".error_msg").fadeOut("fast");
	                }
                
                return true;
            }
        }
    };
    jQuery.fn.validated = function(callback){
        jQuery(this).each(function(){
            if (this.tagName == "FORM") {
                jQuery(this).submit(function(){
                    if (ValidationErrors[jQuery(this).attr("id")].length == 0) 
                        callback();
					return false;
                });
            }
        });
    };
})(jQuery);
