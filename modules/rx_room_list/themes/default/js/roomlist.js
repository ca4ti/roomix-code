function FindDetails(detail)
{
	$(function() {
		var SplitText = "Title";
		var $dialog = $('<div></div>')
		.html(SplitText )
		.dialog({
        title: 'Info',
		width: 600,
		modal: true,
		 buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}});
		$dialog.dialog('open');
		$dialog.html(detail);
	});
}

function Transfer(id, exten, Vtitle, Vcancel, Vvalid) {
$(function() {
	    $( "#DialTrans"+exten ).dialog({ 
		width: 'auto', 
		autoOpen: false, 
		modal: true,
		title: Vtitle,
		buttons: {
			'Cancel': function() {
				$( this ).dialog( "close" );
			},

			'Valid': function() {
				window.location.href='index.php?menu=rx_list&action=transfer&register_id=' + id + '&from=' + exten + '&to='+ $("#select"+exten).val();
			}
		}
		});
		$(":button:contains('Cancel')").html(Vcancel) ;
        $(":button:contains('Valid')").html(Vvalid) ;
		$( "#DialTrans"+exten ).css("visibility", 'visible');
		$( "#DialTrans"+exten ).dialog("open");
});
}

function dnd_status(extension,status)
{
	var arrAction			= new Array();
	arrAction["action"]		= "DND";
	arrAction["Who"]		= extension;
	arrAction["rawmode"]	= "yes";
	if (status == "YES")
	{
		arrAction["DND_St"]	= "YES";
		request("index.php",arrAction, false, function(arrData,statusResponse,error)
		{
			document.getElementById("dnd"+extension).innerHTML = "<img src='modules/rx_room_list/images/dnd.png' onclick='dnd_status(\""+extension+"\",\"NO\")'>";
    		});
	}
	else
	{
		arrAction["DND_St"]	= "NO";
		request("index.php",arrAction, false, function(arrData,statusResponse,error)
		{
			document.getElementById("dnd"+extension).innerHTML = "<img src='modules/rx_room_list/images/d.png' onclick='dnd_status(\""+extension+"\",\"YES\")'>";	
    		});
	}
	
}

function clean_status(extension,status)
{
	var arrAction			= new Array();
	arrAction["action"]		= "CLEAN";
	arrAction["Who"]		= extension;
	arrAction["rawmode"]	= "yes";
	if (status == "YES")
	{
		arrAction["CLEAN_St"]	= "YES";
		request("index.php",arrAction, false, function(arrData,statusResponse,error)
		{
			document.getElementById("clean"+extension).innerHTML = "<img src='modules/rx_room_list/images/1.png' onclick='clean_status(\""+extension+"\",\"NO\")'>";
		});

	}
	else
	{
		arrAction["CLEAN_St"]	= "NO";
		request("index.php",arrAction, false, function(arrData,statusResponse,error)
		{
			document.getElementById("clean"+extension).innerHTML = "<img src='modules/rx_room_list/images/0.png' onclick='clean_status(\""+extension+"\",\"YES\")'>";	
		});

	}
}

function Ack_wakeup(extension, file)
{
	var arrAction				= new Array();
	arrAction["action"]			= "Ack_Wakeup";
	arrAction["Wakeup_File"]	= file;
	arrAction["rawmode"]		= "yes";
	request("index.php",arrAction, false, function(arrData,statusResponse,error)
	{
		document.getElementById("Ack"+extension).innerHTML = " ";
	});
}


