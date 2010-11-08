
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

<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr class="letra12">
        <td align="right" valign="top"><b>{$operating_mode.LABEL} : </b></td>
        <td align="left" valign="top">{$operating_mode.INPUT}</td>
        <td align="right" width="200" valign="top"><b>{$locked_when_check_out.LABEL} : </b></td>
        <td align="left" valign="top">{$locked_when_check_out.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$calling_between_rooms.LABEL} : </b></td>
        <td align="left" valign="top">{$calling_between_rooms.INPUT}</td>
        <td align="right" valign="top" width="200"><b>{$Logo.LABEL} : </b><br><br><div align='center'><img src="{$LOGO}" WIDTH=50% HEIGHT="50%" border = "1"></div><br></td>
        <td align="left" valign="top">
            <input name="file_record" id="file_record" type="file" value="{$file_record_name}" size='30' />
        </td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$reception.LABEL} : </b></td>
        <td align="left" valign="top">{$reception.INPUT} <span  class="required">*</span></td>
        <td align="right" valign="top" width="200"><b>{$company.LABEL} : </b></td>
        <td align="left" valign="top">{$company.INPUT} <span  class="required">*</span></td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$clean.LABEL} : </b></td>
        <td align="left" valign="top">{$clean.INPUT}</td>
        <td align="right" width="200" valign="top"><b>{$minibar.LABEL} : </b></td>
        <td align="left" valign="top">{$minibar.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$rmbc.LABEL} : </b></td>
        <td align="left" valign="top">{$rmbc.INPUT}</td>
        <td align="right" width="200" valign="top"></td>
        <td align="left" valign="top"></td>
    </tr>
</table>

<input class="button" type="hidden" name="id" value="{$ID}" />