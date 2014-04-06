<br>

<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
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
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">&nbsp;&nbsp;
            {$BOOKING}&nbsp;&nbsp;{if $mode eq 'edit'}<b><div align="center"><h1>{$booking_mode}</h1></b></div>{/if}
         </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
			<div class='myDialog'>
				<div class='myProgressBar'> </div>
			</div>

<table border=0>
	<tr align="left">
		<td>
			<table class="tabForm" style="font-size: 16px; border-bottom: 0px" width="100%">
    				<tr class="letra12" align = "left" >
        				<td align="right" width="150"><b>{$date.LABEL} : </b></td>
        				<td align="left"  width="150">{$date.INPUT} <span  class="required">*</span></b></td>

        				<td align="right" width="150"><b>{$date_co.LABEL} : </b></td>
        				<td align="left" >{$date_co.INPUT} <span  class="required">*</span></b></td>
    				</tr>
    				<tr class="letra12">
        				<td align="right"><b>{$room.LABEL} : </td>
        				<td align="left">{$room.INPUT} </b></td>

        				<td align="right" valign="top">{$num_guest.INPUT}<b>: </b></td>
        				<td align="left" valign="center"><b>{$num_guest.LABEL} </b></td>
					</tr>
    				<tr class="letra12">
        				<td align="right" valign="top"><b>{$last_name.LABEL} : </b></td>
        				<td align="left" valign="top"><div>{$last_name.INPUT} <span  class="required">*</span></div>
			   			<div class="suggestionsBox" id="suggestions" style="display: none;">
			   			<img src="{$SRCIMG}/upArrow.png" style="position: relative; top: -12px; left: 10px;" alt="upArrow" />
                        			<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
                        		</div></td>

        				<td align="right" valign="top"><b>{$first_name.LABEL} : </b></td>
        				<td align="left" valign="top">{$first_name.INPUT} <span  class="required">*</span></td>
    				</tr>
    				<tr class="letra12">
        				<td align="right">{if $mode eq 'edit'}<b>{$payment_mode_b.LABEL} : </b>{/if}</td>
        				<td align="left">{if $mode eq 'edit'}{$payment_mode_b.INPUT} <span  class="required">*</span>{/if}</td>

        				<td align="right">{if $mode eq 'edit'}<b>{$money_advance.LABEL} : </b>{/if}</td>
        				<td align="left">{if $mode eq 'edit'}{$money_advance.INPUT} <span  class="required">*</span>{/if}</td>
    				</tr>
    				<tr class="letra12">
        				<td align="right" valign="top" width="107"><b>{$address.LABEL} : </b></td>
        				<td align="left">{$address.INPUT}</td>

        				<td align="left"valign="top"> </td>
        				<td align="left"valign="top"> </td>
    				</tr>
    				<tr class="letra12">
        				<td align="right" width="107"><b>{$cp.LABEL} : </b></td>
        				<td align="left">{$cp.INPUT}</td>
        				
					<td align="right"><b>{$city.LABEL} : </b></td>
        				<td align="left">{$city.INPUT}</td>
    				</tr>
    				<tr class="letra12">
        				<td align="right"><b>{$phone.LABEL} : </b></td>
        				<td align="left">{$phone.INPUT}</td>
    
					<td align="right"><b>{$mobile.LABEL} : </b></td>
        				<td align="left">{$mobile.INPUT}</td>
    				</tr>
    				<tr class="letra12">
        				<td align="right"><b>{$mail.LABEL} : </b></td>
        				<td align="left">{$mail.INPUT}</td>

					<td align="right"><b>{$fax.LABEL} : </td>
        				<td align="left">{$fax.INPUT}</td>
    				</tr>
    				<tr class="letra12">
        				<td align="right"><b>{$NIF.LABEL} : </b></td>
        				<td align="left">{$NIF.INPUT} <span  class="required">*</span></td>

					<td align="right"><b>{$Off_Doc.LABEL} : </td>
        				<td align="left">{$Off_Doc.INPUT} <span  class="required">*</span></td>
    				</tr>
    				<tr class="letra12">
        				<td align="right"><div style='display:none;'>{$booking.INPUT}</div></td>
        				<td align="left"></td>
        
					<td align="right"> </td>
        				<td align="left"> </td>
    				</tr>
			</table>
		</td>
		<td valign="top">
			{$checkin_img}
		</td>
	</tr>
</table>
<br><br><br>
<input class="button" type="hidden" name="id" value="{$ID}" />