function Popup_Alert(content)
{
	$(function() {
		var SplitText = "Title";
		var $dialog = $('<div></div>')
		.html(SplitText )
		.dialog({
        title: 'Alert',
		width: 600,
		modal: true,
		 buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}});
		$dialog.dialog('open');
		$dialog.html(content);
	});
}