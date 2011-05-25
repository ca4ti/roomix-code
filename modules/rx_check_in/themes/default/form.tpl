<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
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
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right"><b>{$room.LABEL}: </td>
        <td align="left">{$room.INPUT} <span  class="required">*</span></b></td>
        <td align="right"><b>{$date.LABEL}: </b></td>
        <td align="left">{$date.INPUT} <span  class="required">*</span></b></td>
        <td align="right"><b>{$date_co.LABEL}: </b></td>
        <td align="left">{$date_co.INPUT} <span  class="required">*</span></b></td>
        <td align="right"><b>{$booking.LABEL}: </b></td>
        <td align="left">{$booking.INPUT} <span  class="required">*</span></b></td>
    </tr>

    <tr class="letra12">
        <td align="right"><b>{$first_name.LABEL}: </b></td>
        <td align="left">{$first_name.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$last_name.LABEL}: </b></td>
        <td align="left">{$last_name.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$num_guest.LABEL}: </b></td>
        <td align="left">{$num_guest.INPUT} <span  class="required">*</span></td>
        <td align="right"> </td>
        <td align="left"> </td>
    </tr>
</table>
<table style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right"><b>{$address.LABEL}: </b></td>
        <td align="left">{$address.INPUT}</td>
    </tr>
</table>
<table  style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right"><b>{$cp.LABEL}: </b></td>
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
<input class="button" type="hidden" name="id" value="{$ID}" />