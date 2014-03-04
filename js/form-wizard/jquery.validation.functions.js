function isValidDate(year, day, month){
    var date = new Date(year, (month - 1), day);
    var DateYear = date.getFullYear();
    var DateMonth = date.getMonth();
    var DateDay = date.getDate();
    if (DateYear == year && DateMonth == (month - 1) && DateDay == day && year > 1000)
        return true;
    else 
        return false;
}

function isValidDate2(year, month, day){
	var date = new Date(year, (month - 1), day);
    var DateYear = date.getFullYear();
    var DateMonth = date.getMonth();
    var DateDay = date.getDate();
    alert(month);
    alert(day);
    alert(DateMonth);
    if (DateYear == year && DateMonth == (month - 1) && DateDay == day && year > 1000)
        return true;
    else 
        return false;

}

/*
 * This function checks if there is at-least one element checked in a group of check-boxes or radio buttons.
 * @id: The ID of the check-box or radio-button group
 */
function isChecked(id){
    var ReturnVal = false;
    jQuery("#" + id).find('input[type="radio"]').each(function(){
        if (jQuery(this).is(":checked")) 
            ReturnVal = true;
    });
    jQuery("#" + id).find('input[type="checkbox"]').each(function(){
        if (jQuery(this).is(":checked")) 
            ReturnVal = true;
    });
    return ReturnVal;
}
/*
 * variabel needed
 * 
 */


