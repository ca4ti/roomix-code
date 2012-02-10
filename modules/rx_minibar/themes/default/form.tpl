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
        <td align="right" nowrap></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" border="0" >
    <tr class="letra12">
        <td align="right" width="10%"><b>{$Digits}</b></td>
        <td align="center" width="20%"><b>{$Labels}</b></td>
        <td align="left" width="20%"><b>{$Prices}</b></td>
        <td align="left" width="30%"><b>{$VAT} {$vat.LABEL} : {$vat.INPUT}</b></td>
        <td align="left" width="20%"> </td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t1.LABEL} : </b></td>
        <td align="left">{$t1.INPUT}</td>
        <td align="center">{$cost_1.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t2.LABEL} : </b></td>
        <td align="left">{$t2.INPUT}</td>
        <td align="center">{$cost_2.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t3.LABEL} : </b></td>
        <td align="left">{$t3.INPUT}</td>
        <td align="center">{$cost_3.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t4.LABEL} : </b></td>
        <td align="left">{$t4.INPUT}</td>
        <td align="center">{$cost_4.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t5.LABEL} : </b></td>
        <td align="left">{$t5.INPUT}</td>
        <td align="center">{$cost_5.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t6.LABEL} : </b></td>
        <td align="left">{$t6.INPUT}</td>
        <td align="center">{$cost_6.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t7.LABEL} : </b></td>
        <td align="left">{$t7.INPUT}</td>
        <td align="center">{$cost_7.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t8.LABEL} : </b></td>
        <td align="left">{$t8.INPUT}</td>
        <td align="center">{$cost_8.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t9.LABEL} : </b></td>
        <td align="left">{$t9.INPUT}</td>
        <td align="center">{$cost_9.INPUT}</td>
        <td align="left"></td>
    </tr>
    <tr class="letra12">
        <td align="right"><b>{$t0.LABEL} : </b></td>
        <td align="left">{$t0.INPUT}</td>
        <td align="center">{$cost_0.INPUT}</td>
        <td align="left"></td>
    </tr>
</table>
<input class="button" type="hidden" name="id" value="{$ID}" />