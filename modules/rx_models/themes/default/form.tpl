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
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_edit" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right"><b>{$model.LABEL}: </b></td>
        <td align="left">{$model.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$price.LABEL}: </b></td>
        <td align="left">{$price.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$guest.LABEL}: </b></td>
        <td align="left">{$guest.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$vat.LABEL}: </b></td>
        <td align="left">{$vat.INPUT} <span  class="required">*</span></td>
    </tr>
</table>

<input class="button" type="hidden" name="id" value="{$ID}" />