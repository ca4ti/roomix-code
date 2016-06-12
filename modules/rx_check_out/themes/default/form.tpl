{$popup}
<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        <td align="left"><b>{$When.LABEL} : </b></td>
        <td align="left">{$When.INPUT}</td>
        <td align="right"><b>{$date.LABEL} : </b></td>
        <td align="left">{$date.INPUT}</td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap>
            <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
        </td>
    </tr>
</table>
<br>
<table class="tabForm" style="font-size: 16px;" align="center" border="0">
    <tr class="letra12">
        <td align="right" width="50"><b>{$room.LABEL} : </b></td>
        <td align="left"  width="40">{$room.INPUT}</td>

        <td align="right" width="170"><b>{$asModel.LABEL} : </b></td>
        <td align="left"  width="170">{$asModel.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" width="90"><b>{$group.LABEL} : </b></td>
        <td align="left"  width="50">{$group.INPUT}</td>
		
        <td align="right" width=""><b>{$discount.LABEL} : </b></b></td>
        <td align="left">{$discount.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$paid.LABEL} : </b></td>
        <td align="left">{$paid.INPUT}</td>

        <td align="right"><b>{$payment_mode.LABEL} :</b></td>
        <td align="left">{$payment_mode.INPUT} </td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$details.LABEL} : </b></td>
        <td align="left">{$details.INPUT}</td>

        <td align="right"><b>{$sending_by_mail.LABEL} : </b></td>
        <td align="left">{$sending_by_mail.INPUT} </td>
    </tr>
</table>
    {if $bil eq '1'}
<br><br>
<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr class="letra12">
        <td align="left"  width="50"><a style="text-decoration: none;" href="roomx_billing/{$bil_link}" target="_next"><button type="button">{$Display}</button></a></td>
        <td align="Left"  width="100"><b>{$call_number}</b> {$Call}</td>
        <td align="left"  width="375"><b>{$Total}</b> : {$total}</td>
     </tr>
</table>
    {/if}



<input class="button" type="hidden" name="id" value="{$ID}" />