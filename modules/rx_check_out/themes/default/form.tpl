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
        <td align="right"><b>{$When.LABEL}: </b></td>
        <td align="left">{$When.INPUT}</td>
        <td align="right"><b>{$date.LABEL}: </b></td>
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
<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr class="letra12">
        <td align="right" width="50"><b>{$room.LABEL}: </b></td>
        <td align="left"  width="40">{$room.INPUT}</td>
        <td align="right" width="130"><b>{$asModel.LABEL}: </b></td>
        <td align="left"  width="130">{$asModel.INPUT}</td>
        <td align="right" width="50"><b>{$group.LABEL}: </b></td>
        <td align="left"  width="40">{$group.INPUT}</td>
        <td align="right" width="60"><b>{$paid.LABEL}: </b></td>
        <td align="left"  width="35">{$paid.INPUT}</td>
        <td align="right" width="35"><b>{$details.LABEL}: </b></td>
        <td align="left"  width="35">{$details.INPUT}</td>
        <td align="right" width="150"><b>{$sending_by_mail.LABEL}: </b></td>
        <td align="left"  width="35">{$sending_by_mail.INPUT}</td>
        <td align="right" widht="100"></td>
    </tr>
</table>
    {if $bil eq '1'}
<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr class="letra12">
        <td align="right" width="100"><b>{$call_number}</b> {$Call}</td>
        <td align="left"  width="175"><b>{$Total}</b> : {$total}</td>
        <td align="right" width="100"><a style="text-decoration: none;" href="roomx_billing/{$bil_link}" target="_next"><button type="button">{$Display}</button></a></td>
        <td align="right" widht="200"></td>
     </tr>
</table>
    {/if}



<input class="button" type="hidden" name="id" value="{$ID}" />