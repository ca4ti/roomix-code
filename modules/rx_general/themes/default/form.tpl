
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

<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr class="letra12">
        <td align="right" valign="top"><b>{$operating_mode.LABEL} : </b></td>
        <td align="left" valign="top">{$operating_mode.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><hr style="border-style: dotted; border-width: 1px; width: 100%;"></td>
        <td align="left" valign="top"><i><b>{$Functions}</i></b></td>
    </tr>
    <tr class="letra12">
        <td align="right" width="200" valign="top"><b>{$locked_when_check_out.LABEL} : </b></td>
        <td align="left" valign="top">{$locked_when_check_out.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$calling_between_rooms.LABEL} : </b></td>
        <td align="left" valign="top">{$calling_between_rooms.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$rmbc.LABEL} : </b></td>
        <td align="left" valign="top">{$rmbc.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><hr style="border-style: dotted; border-width: 1px; width: 100%;"></td>
        <td align="left" valign="top"><i><b>{$Company}</i></b></td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top" width="200"><b>{$Logo.LABEL} : </b></td>
        <td align="left" valign="top">
            <input name="file_record" id="file_record" type="file" value="{$file_record_name}" size='30' />
            <br><br><div align='left'><img src="{$LOGO}" WIDTH=50% HEIGHT="50%" border = "1"></div>
        </td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top" width="200"><b>{$company.LABEL} : </b></td>
        <td align="left" valign="top">{$company.INPUT} <span  class="required">*</span></td>
    </tr>
    <tr class="letra12">
        <td align="right" width="200" valign="top"><b>{$mail.LABEL} : </b></td>
        <td align="left" valign="top">{$mail.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><hr style="border-style: dotted; border-width: 1px; width: 100%;"></td>
        <td align="left" valign="top"><i><b>{$RoomXDialPlan}</i></b></td>
    </tr>
    <tr class="letra12">
        <td align="right" width="200" valign="top"><b>{$minibar.LABEL} : </b></td>
        <td align="left" valign="top">{$minibar.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$clean.LABEL} : </b></td>
        <td align="left" valign="top">{$clean.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$reception.LABEL} : </b></td>
        <td align="left" valign="top">{$reception.INPUT} <span  class="required">*</span></td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><hr style="border-style: dotted; border-width: 1px; width: 100%;"></td>
        <td align="left" valign="top"><i><b>{$Tax}</i></b></td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><b>{$vat_1.LABEL} : </b></td>
        <td align="left" valign="top">{$vat_1.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" width="200" valign="top"><b>{$vat_2.LABEL} : </b></td>
        <td align="left" valign="top">{$vat_2.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" width="200" valign="top"><b>{$rounded.LABEL} : </b></td>
        <td align="left" valign="top">{$rounded.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" valign="top"><hr style="border-style: dotted; border-width: 1px; width: 100%;"></td>
        <td align="left" valign="top"><i><b>{$discount.LABEL}</i></b></td>
    </tr>
    <tr class="letra12">
        <td align="right" width="200" valign="top"><b>{$discount.LABEL} : </b></td>
        <td align="left" valign="top">{$discount.INPUT}</td>
    </tr>
</table>

<input class="button" type="hidden" name="id" value="{$ID}" />