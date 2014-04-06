function FindGuest(find_guest)
{
    var arrAction              = new Array();
    arrAction["action"]        = "find_guest";
    arrAction["who"]		   = find_guest;
    arrAction["rawmode"]       = "yes";
    /*arrAction["menu"]        = "rx_check_in";*/
	if(find_guest.length == 0) {
		// Hide the suggestion box.
		$('#suggestions').hide();
		} 
              else 
              {
    		request("index.php",arrAction, false, function(arrData,statusResponse,error)
    			{
			if(arrData.length >0) {
				$('#suggestions').show();
				$('#autoSuggestionsList').html(arrData);
				}
    			}
    		);
	}
}
	
function fill(thisValue) {
    var arrAction		= new Array();
    var arrData		= "";
    arrAction["action"]     = "guest_id";
    arrAction["guest_ID"]	= thisValue;
    arrAction["rawmode"]    = "yes";
    setTimeout("$('#suggestions').hide();", 200);
    if(thisValue){
    	request("index.php",arrAction, false, function(arrData,statusResponse,error)
		{
               $('#first_name').val(arrData["first_name"]);
               $('#last_name').val(arrData["last_name"]);
               $('#address').val(arrData["address"]);
               $('#cp').val(arrData["cp"]);
               $('#city').val(arrData["city"]);
               $('#phone').val(arrData["phone"]);
               $('#mobile').val(arrData["mobile"]);
               $('#fax').val(arrData["fax"]);
               $('#mail').val(arrData["mail"]);
		 $('#NIF').val(arrData["NIF"]);
		 $('#Off_Doc').val(arrData["Off_Doc"]);
       	});
    }
}

