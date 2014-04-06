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
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="right"><b>{$date_start.LABEL}: </b></td>
        <td align="left">{$date_start.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$date_end.LABEL}: </b></td>
        <td align="left">{$date_end.INPUT} <span  class="required">*</span></td>
        <td align="right"><b>{$type_of_report.LABEL}: </b></td>
        <td align="left">{$type_of_report.INPUT}</td>
    </tr>

</table>
{if $Comments neq ''}
	<div align="left" class="ReportBox">{$Comments}</div>
	<div align="Center"><br><img src="{$CheckInOutGraph}"></div>
{/if}
<input class="button" type="hidden" name="id" value="{$ID}" />