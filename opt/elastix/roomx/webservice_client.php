<?php
  // Setting up session 
  //--------------------
  $useragent 	= $_SERVER['HTTP_USER_AGENT'];
  $strCookie 	= 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
  session_write_close();
  
  $ch			= curl_init();
  $host 		= "193.107.20.29";					// Elastix server , IP address or URL like: elx.example.com
  $url 			= "https://".$host."/index.php";	//---------------------------------------------------------
  
  // Setting up user & password for Elastix server
  //----------------------------------------------
  $user 	= "admin";					// It could be another user account which could have any access to roomx application.
  $password = "zzgxpwxc";				//-----------------------------------------------------------------------------------
  $fields 	= array( 'input_user'=>$user, 'input_pass'=>$password, 'submit_login'=>'Submit');
  $postvars = '';
  foreach($fields as $key=>$value) {
    $postvars .= $key . "=" . $value . "&";
  }

  // Connecting to Elastix server and keeping the session opened.
  //-------------------------------------------------------------
  curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false); 
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch,CURLOPT_HEADER, 0);
  curl_setopt($ch,CURLOPT_POST, True);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, True);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION, True);
  curl_setopt($ch,CURLOPT_COOKIESESSION, True);
  curl_setopt($ch,CURLOPT_USERAGENT, $useragent);
  curl_setopt($ch,CURLOPT_COOKIE, $strCookie );
  curl_setopt($ch,CURLOPT_COOKIEJAR,'cookie.txt');
  curl_setopt($ch,CURLOPT_COOKIEFILE,'./tmp');
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
  curl_setopt($ch,CURLOPT_TIMEOUT, 20);
  curl_setopt($ch,CURLOPT_URL,$url);
  $response = curl_exec($ch);
  
  // Cleaning the first response.
  //-----------------------------
  $response = '';
  $postvars = '';
  
  // Session is still opened, we send our request
  //-----------------------------------------------
  //	Set function=funtion_below at the end of the url.
  //	$url = "http://".$host."/index.php?menu=rx_check_in&action=ws&function=number_of_rooms";
  //
  //	-- Functions --		-- Arguments --				-- Comments --
  //	number_of_rooms : 					; Return the number of rooms in your hotel.
  //	
  //	check_booking	: start=yyyy/mm/dd 	; Date start
  //					  end=yyyy/mm/dd	; Date end
  //
  //	add_booking		: room_id=x			; Index number of room in the database
  //					  start=yyyy/mm/dd 	; Date start
  //					  end=yyyy/mm/dd	; Date end
  //					  payment_mode_b=x  ; Payment mode for booking (Required)
  //										; 1 = Credit card
  //										; 2 = Cach 
  //										; 3 = Bank Check
  //										; 4 = Bank Draft
  //										; 5 = PayPal
  //										; 6 = Other
  //
  //					  guest_id=x		; Index number of guest in the database
  //
  //					  money_advance=x	; Money advance, value is interger. (Required)
  //
  //					  num_guest=x		; Guest supply into the room. (Default = 0)
  //	
  //					  confirmed=x		; Booking confirmed by the guest or not.
  //										; 1 = Confirmed
  //										; 0 = Not Confirmed (Default)
  //
  //					  booking_number=x  ; Booking number up to 15 characters. (Alfa & Num).
  //
  //	find_guest		: first_name=?		; Enter the first name (required if mail is unknown)
  //					  last_name=?		; Enter the last name (required if mail is unknown)
  //					  mail=?			; Enter the email address (Required if first and last name are unknown)
  //
  //	get_all_guests	:					; Returns the list of the whole guests stored in your database.
  //
  //	add_guest		: first_name=?		; First name (required)
  //					  last_name=?		; Last name (required)
  //					  address=?			; Guest address (required)
  //					  cp=?				; zip code (required)
  //					  city=?			; City (required)
  //					  phone=?			; Phone number (required if mobile number is unknown)
  //					  mobile=?			; Mobile number (required if phone number is unknown)
  //					  fax=?				; Fax number
  //					  mail=?			; Email address
  //					  tin=?				; TIN number (EU)
  //					  Off_Doc			; Official document number like for example an ID card, passport or else.
  //
  
  $url = "http://".$host."/index.php?menu=rx_check_in&action=ws&function=number_of_rooms";

  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_POST, False);
  curl_setopt($ch,CURLOPT_POSTFIELDS, "");
  curl_setopt($ch,CURLOPT_HEADER, 0);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, True);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION, True);
  
  // Getting the entire html page 
  //-----------------------------
  $response = curl_exec($ch);  
  
  // Getting our values
  //-----------------------
  $startsAt 	= strpos($response, "<response>") + strlen("<response>");
  $endsAt 		= strpos($response, "</response>", $startsAt);
  $content 		= substr($response, $startsAt, $endsAt - $startsAt);
  
  // Our XML response
  //-----------------
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
		"<!-- Response -->\n".
		"<response>\n".
		$content."\n".
		"</response>\n";

  curl_close ($ch);
?>