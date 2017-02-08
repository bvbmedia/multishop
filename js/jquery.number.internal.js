(function($){
    "use strict";
    /**
     * Substitutions for keydown keycodes.
     * Allows conversion from e.which to ascii characters.
     */
    var _keydown = {
        codes : {
            46 : 127,
            188 : 44,
            109 : 45,
            190 : 46,
            191 : 47,
            192 : 96,
            220 : 92,
            222 : 39,
            221 : 93,
            219 : 91,
            173 : 45,
            187 : 61, //IE Key codes
            186 : 59, //IE Key codes
            189 : 45, //IE Key codes
            110 : 46  //IE Key codes
        },
        shifts : {
            96 : "~",
            49 : "!",
            50 : "@",
            51 : "#",
            52 : "$",
            53 : "%",
            54 : "^",
            55 : "&",
            56 : "*",
            57 : "(",
            48 : ")",
            45 : "_",
            61 : "+",
            91 : "{",
            93 : "}",
            92 : "|",
            59 : ":",
            39 : "\"",
            44 : "<",
            46 : ">",
            47 : "?"
        }
    };
    /**
     * jQuery number formatter plugin. This will allow you to format numbers on an element.
     *
     * @params proxied for format_number method.
     *
     * @return : The jQuery collection the method was called with.
     */
    $.fn.number = function( number, decimals, dec_point, thousands_sep ){

        // Enter the default thousands separator, and the decimal placeholder.
        thousands_sep	= (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
        dec_point		= (typeof dec_point === 'undefined') ? '.' : dec_point;
        decimals		= (typeof decimals === 'undefined' ) ? 0 : decimals;

        // Work out the unicode character for the decimal placeholder.
        var u_dec			= ('\\u'+('0000'+(dec_point.charCodeAt(0).toString(16))).slice(-4)),
                regex_dec_num	= new RegExp('[^'+u_dec+'0-9]','g'),
                regex_dec		= new RegExp(u_dec,'g'),
                typeTimer;

        // If we've specified to take the number from the target element,
        // we loop over the collection, and get the number.
        if( number === true )
        {
            // If this element is a number, then we add a keyup
            if( this.is('input:text') )
            {
                // Return the jquery collection.
                return this.on({

                                   /**
                                    * Handles keyup events, re-formatting numbers.
                                    *
                                    * Uses 'data' object to keep track of important information.
                                    *
                                    * data.c
                                    * This variable keeps track of where the caret *should* be. It works out the position as
                                    * the number of characters from the end of the string. E.g., '1^,234.56' where ^ denotes the caret,
                                    * would be index -7 (e.g., 7 characters from the end of the string). At the end of both the key down
                                    * and key up events, we'll re-position the caret to wherever data.c tells us the cursor should be.
                                    * This gives us a mechanism for incrementing the cursor position when we come across decimals, commas
                                    * etc. This figure typically doesn't increment for each keypress when to the left of the decimal,
                                    * but does when to the right of the decimal.
                                    *
                                    * @param object e			: the keyup event object.s
                                    *
                                    * @return void;
                                    */
                                   'keydown.format' : function(e){
                                       // Define variables used in the code below.
                                       var data	= $(this).data('numFormat'),
                                           code	= (e.keyCode ? e.keyCode : e.which),
                                           chara	= ''; //unescape(e.originalEvent.keyIdentifier.replace('U+','%u')),

                                       if (_keydown.codes.hasOwnProperty(code)) {
                                           code = _keydown.codes[code];
                                       }
                                       if (!e.shiftKey && (code >= 65 && code <= 90)){
                                           code += 32;
                                       } else if (!e.shiftKey && (code >= 69 && code <= 105)){
                                           code -= 48;
                                       } else if (e.shiftKey && _keydown.shifts.hasOwnProperty(code)){
                                           //get shifted keyCode value
                                           chara = _keydown.shifts[code];
                                       }

                                       if( chara == '' ) chara = String.fromCharCode(code);

                                       // Stop executing if the user didn't type a number key, a decimal character, backspace, or delete.
                                       if( code != 46 && code != 8 && code != 45 && code != 127 && chara != dec_point && !chara.match(/[0-9]/) )
                                       {
                                           // We need the original keycode now...
                                           var key = (e.keyCode ? e.keyCode : e.which);
                                           if( // Allow control keys to go through... (delete, backspace, tab, enter, escape etc)
                                           key == 46 || key == 8 || key == 127 || key == 9 || key == 27 || key == 13 ||
                                           // Allow: Ctrl+A, Ctrl+R, Ctrl+P, Ctrl+S, Ctrl+F, Ctrl+H, Ctrl+B, Ctrl+J, Ctrl+T, Ctrl+Z, Ctrl++, Ctrl+-, Ctrl+0
                                           ( (key == 65 || key == 82 || key == 80 || key == 83 || key == 70 || key == 72 || key == 66 || key == 74 || key == 84 || key == 90|| key == 61 || key == 173 || key == 48) && ( e.ctrlKey || e.metaKey ) === true ) ||
                                           // Allow: Ctrl+V, Ctrl+C, Ctrl+X
                                           ( (key == 86 || key == 67 || key == 88) && ( e.ctrlKey || e.metaKey ) === true ) ||
                                           // Allow: home, end, left, right
                                           ( (key >= 35 && key <= 39) ) ||
                                           // Allow: F1-F12
                                           ( (key >= 112 && key <= 123) )
                                           ){
                                               return;
                                           }
                                           // But prevent all other keys.
                                           e.preventDefault();
                                           return false;
                                       }
                                   },

                                   /**
                                    * Handles keyup events, re-formatting numbers.
                                    *
                                    * @param object e			: the keyup event object.s
                                    *
                                    * @return void;
                                    */
                                   'keyup.format' : function(e){
                                       var typed_value=this.value;
                                       if (dec_point!='.') {
                                           if (typed_value.indexOf('.')>-1 && typed_value.indexOf(dec_point)==-1) {
                                               this.value = typed_value.replace('.', dec_point);
                                           }
                                       }
                                   },

                                   /**
                                    * Reformat when pasting into the field.
                                    *
                                    * @param object e 		: jQuery event object.
                                    *
                                    * @return false : prevent default action.
                                    */
                                   'paste.format' : function(e){
                                       // Defint $this. It's used twice!.
                                       var $this	= $(this),
                                           original	= e.originalEvent,
                                           val		= null;

                                       // Get the text content stream.
                                       if (window.clipboardData && window.clipboardData.getData) { // IE
                                           val = window.clipboardData.getData('Text');
                                       } else if (original.clipboardData && original.clipboardData.getData) {
                                           val = original.clipboardData.getData('text/plain');
                                       }

                                       // Do the reformat operation.
                                       $this.val(val);

                                       // Stop the actual content from being pasted.
                                       e.preventDefault();
                                       return false;
                                   }
                               })

                                // Loop each element (which isn't blank) and do the format.
                                .each(function(){

                                    var $this = $(this).data('numFormat',{
                                        c				: -(decimals+1),
                                        decimals		: decimals,
                                        thousands_sep	: thousands_sep,
                                        dec_point		: dec_point,
                                        regex_dec_num	: regex_dec_num,
                                        regex_dec		: regex_dec,
                                        init			: this.value.indexOf('.') ? true : false
                                    });

                                    // Return if the element is empty.
                                    if( this.value === '' ) return;

                                    // Otherwise... format!!
                                    $this.val($this.val());
                                });
            }
            else
            {
                // return the collection.
                return this.each(function(){
                    var $this = $(this), num = +$this.text().replace(regex_dec_num,'').replace(regex_dec,'.');
                    $this.number( !isFinite(num) ? 0 : +num, decimals, dec_point, thousands_sep );
                });
            }
        }

        // Add this number to the element as text.
        return this.text( $.number.apply(window,arguments) );
    };

    //
    // Create .val() hooks to get and set formatted numbers in inputs.
    //

    // We check if any hooks already exist, and cache
    // them in case we need to re-use them later on.
    var origHookGet = null, origHookSet = null;

    // Check if a text valHook already exists.
    if( $.isPlainObject( $.valHooks.text ) )
    {
        // Preserve the original valhook function
        // we'll call this for values we're not
        // explicitly handling.
        if( $.isFunction( $.valHooks.text.get ) ) origHookGet = $.valHooks.text.get;
        if( $.isFunction( $.valHooks.text.set ) ) origHookSet = $.valHooks.text.set;
    }
    else
    {
        // Define an object for the new valhook.
        $.valHooks.text = {};
    }

    /**
     * Define the valHook to return normalised field data against an input
     * which has been tagged by the number formatter.
     *
     * @param object el			: The raw DOM element that we're getting the value from.
     *
     * @return mixed : Returns the value that was written to the element as a
     *				  javascript number, or undefined to let jQuery handle it normally.
     */
    $.valHooks.text.get = function( el ){

        // Get the element, and its data.
        var $this	= $(el), num, negative,
            data	= $this.data('numFormat');

        // Does this element have our data field?
        if( !data )
        {
            // Check if the valhook function already existed
            if( $.isFunction( origHookGet ) )
            {
                // There was, so go ahead and call it
                return origHookGet(el);
            }
            else
            {
                // No previous function, return undefined to have jQuery
                // take care of retrieving the value
                return undefined;
            }
        }
        else
        {
            // Remove formatting, and return as number.
            if( el.value === '' ) return '';


            // Convert to a number.
            num = +(el.value
                    .replace( data.regex_dec_num, '' )
                    .replace( data.regex_dec, '.' ));

            // If we've got a finite number, return it.
            // Otherwise, simply return 0.
            // Return as a string... thats what we're
            // used to with .val()
            return (el.value.indexOf('-') === 0 ? '-' : '')+( isFinite( num ) ? num : 0 );
        }
    };

    /**
     * A valhook which formats a number when run against an input
     * which has been tagged by the number formatter.
     *
     * @param object el		: The raw DOM element (input element).
     * @param float			: The number to set into the value field.
     *
     * @return mixed : Returns the value that was written to the element,
     *				  or undefined to let jQuery handle it normally.
     */
    $.valHooks.text.set = function( el, val )
    {
        // Get the element, and its data.
        var $this	= $(el),
            data	= $this.data('numFormat');

        // Does this element have our data field?
        if( !data )
        {

            // Check if the valhook function already exists
            if( $.isFunction( origHookSet ) )
            {
                // There was, so go ahead and call it
                return origHookSet(el,val);
            }
            else
            {
                // No previous function, return undefined to have jQuery
                // take care of retrieving the value
                return undefined;
            }
        }
        else
        {


            console.log(val);


            var num = $.number( val, data.decimals, data.dec_point, data.thousands_sep );

            // Make sure empties are set with correct signs.
//			if(val.indexOf('-') === 0 && +num === 0)
//			{
//				num = '-'+num;
//			}

            return $.isFunction(origHookSet) ? origHookSet(el, num) : el.value = num;
        }
    };

    /**
     * The (modified) excellent number formatting method from PHPJS.org.
     * http://phpjs.org/functions/number_format/
     *
     * @modified by Sam Sehnert (teamdf.com)
     *	- don't redefine dec_point, thousands_sep... just overwrite with defaults.
     *	- don't redefine decimals, just overwrite as numeric.
     *	- Generate regex for normalizing pre-formatted numbers.
     *
     * @param float number			: The number you wish to format, or TRUE to use the text contents
     *								  of the element as the number. Please note that this won't work for
     *								  elements which have child nodes with text content.
     * @param int decimals			: The number of decimal places that should be displayed. Defaults to 0.
     * @param string dec_point		: The character to use as a decimal point. Defaults to '.'.
     * @param string thousands_sep	: The character to use as a thousands separator. Defaults to ','.
     *
     * @return string : The formatted number as a string.
     */
    $.number = function( number, decimals, decPoint, thousandsSep ){
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
        var n = !isFinite(+number) ? 0 : +number
        var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
        var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
        var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
        var s = ''

        var toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec)
            return '' + (Math.round(n * k) / k).toFixed(prec)
        }

        // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || ''
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }

        return s.join(dec);
    }

})(jQuery);