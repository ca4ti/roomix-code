function FindDetails(detail)
{
	alert(detail);
}

function dnd_status(extension,status)
{
	var arrAction		= new Array();
	arrAction["action"]	= "DND";
	arrAction["Who"]	= extension;
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
	var arrAction		= new Array();
	arrAction["action"]	= "CLEAN";
	arrAction["Who"]	= extension;
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

