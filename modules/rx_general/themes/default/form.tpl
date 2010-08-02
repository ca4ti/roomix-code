
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
        <td align="right"><b>{$operating_mode.LABEL} : </b></td>
        <td align="left">{$operating_mode.INPUT}</td>
        <td align="right"><b>{$locked_when_check_out.LABEL} : </b></td>
        <td align="left">{$locked_when_check_out.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$calling_between_rooms.LABEL} : </b></td>
        <td align="left">{$calling_between_rooms.INPUT}</td>
        <td align="right"><b>{$Logo.LABEL} : </b></td>
        <td align="left"><img src="{$LOGO}" align="left" WIDTH=50% HEIGHT="50%"><br>
            <input name="file_record" id="file_record" type="file" value="{$file_record_name}" size='30' />
        </td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$reception.LABEL} : </b></td>
        <td align="left" valign="top">{$reception.INPUT} <span  class="required">*</span></td>
        <td align="right" valign="top"><b>{$company.LABEL} : </b></td>
        <td align="left" valign="top">{$company.INPUT} <span  class="required">*</span></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$clean.LABEL} : </b></td>
        <td align="left">{$clean.INPUT}</td>
        <td align="right"><b>{$minibar.LABEL} : </b></td>
        <td align="left">{$minibar.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$rmbc.LABEL} : </b></td>
        <td align="left">{$rmbc.INPUT}</td>
        <td align="right"></td>
        <td align="left"></td>
    </tr>
</table>

<input class="button" type="hidden" name="id" value="{$ID}" />