<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        {if $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="view" value="{$APPLY}">&nbsp;&nbsp;
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
    </tr>
</table>
<table style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right" valign="top" width="110"><b>{$last_name.LABEL} : </b></td>
        <td align="left" valign="top" width="120">{$last_name.INPUT} </td>
        <td align="right" valign="top" width="120"><b>{$first_name.LABEL} : </b></td>
        <td align="left" valign="top" width="120">{$first_name.INPUT} </td>
        <td align="left" valign="top" width="500"> </td>
    </tr>
</table>
<table style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right" valign="top" width="107"><b>{$address.LABEL} : </b></td>
        <td align="left">{$address.INPUT}</td>
	 <td align="left">
		<table  style="font-size: 16px;" width="100%" >
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
        			<td align="left">{$NIF.INPUT}</td>
        			<td align="right"><b>{$Off_Doc.LABEL} : </td>
        			<td align="left">{$Off_Doc.INPUT}</td>
    			</tr>
		</table>
        </td>
    </tr>
</table>
<input class="button" type="hidden" name="id" value="{$ID}" />