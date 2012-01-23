<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">&nbsp;&nbsp;
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px; border-bottom: 0px" width="100%" >
    <tr class="letra12">
        <td align="right"><b>{$room.LABEL}: </td>
        <td align="left">{$room.INPUT} </b></td>
        <td align="right"><b>{$date.LABEL}: </b></td>
        <td align="left">{$date.INPUT} <span  class="required">*</span></b></td>
        <td align="right"><b>{$date_co.LABEL}: </b></td>
        <td align="left">{$date_co.INPUT} <span  class="required">*</span></b></td>
        <td align="right"><b>{$booking.LABEL}: </b></td>
        <td align="left">{$booking.INPUT}</b></td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$last_name.LABEL}: </b></td>
        <td align="left" valign="top"><div>{$last_name.INPUT} <span  class="required">*</span></div>
			   <div class="suggestionsBox" id="suggestions" style="display: none;">
			   	<img src="{$SRCIMG}/upArrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                        <div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
                        </div></td>
        <td align="right" valign="top"><b>{$first_name.LABEL}: </b></td>
        <td align="left" valign="top">{$first_name.INPUT} <span  class="required">*</span></td>
        <td align="right" valign="top"><b>{$num_guest.LABEL}: </b></td>
        <td align="left" valign="top">{$num_guest.INPUT}</td>
        <td align="right" valign="top">{$BOOKING}</td>
        <td align="left"valign="top"> </td>
    </tr>
</table>
<table style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right" valign="top" width="107"><b>{$address.LABEL}: </b></td>
        <td align="left">{$address.INPUT}</td>
	 <td align="left">
		<table  style="font-size: 16px;" width="100%" >
    			<tr class="letra12">
        			<td align="right" width="107"><b>{$cp.LABEL}: </b></td>
        			<td align="left">{$cp.INPUT}</td>
        			<td align="right"><b>{$city.LABEL}: </b></td>
        			<td align="left">{$city.INPUT}</td>
    			</tr>
    			<tr class="letra12">
        			<td align="right"><b>{$phone.LABEL}: </b></td>
        			<td align="left">{$phone.INPUT}</td>
        			<td align="right"><b>{$mobile.LABEL}: </b></td>
        			<td align="left">{$mobile.INPUT}</td>
    			</tr>
    			<tr class="letra12">
        			<td align="right"><b>{$mail.LABEL}: </b></td>
        			<td align="left">{$mail.INPUT}</td>
        			<td align="right"><b>{$fax.LABEL}: </td>
        			<td align="left">{$fax.INPUT}</td>
    			</tr>
		</table>
        </td>
    </tr>
</table>
<br><br><br>

<input class="button" type="hidden" name="id" value="{$ID}" />