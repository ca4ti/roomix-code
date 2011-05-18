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
        <td align="right"><b>{$room.LABEL}: </b></td>
        <td align="left">{$room.INPUT}</td>
        <td align="right"><b>{$group.LABEL}: </b></td>
        <td align="left">{$group.INPUT}</td>
        <td align="right"><b>{$paid.LABEL}: </b></td>
        <td align="left">{$paid.INPUT}</td>
        <td align="right"><b>{$details.LABEL}: </b></td>
        <td align="left">{$details.INPUT}</td>
        <td align="right"><b>{$sending_by_mail.LABEL}: </b></td>
        <td align="left">{$sending_by_mail.INPUT}</td>
    </tr>
    {if $bil eq '1'}
    <tr class="letra12">
        <td align="center"></td>
        <td align="center"></td>
        <td align="center"></td>
        <td align="right"><b>{$call_number}</b> {$Call}</td>
        <td align="center"><b>{$Total}</b> : {$total}</td>
        <td align="center"><a style="text-decoration: none;" href="roomx_billing/{$bil_link}" target="_next"><button type="button">{$Display}</button></a></td>
        <td align="center"></td>
        <td align="center"></td>
        <td align="center"></td>
        <td align="center"></td>
    </tr>
    {/if}
</table>



<input class="button" type="hidden" name="id" value="{$ID}" />