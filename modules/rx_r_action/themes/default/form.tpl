<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        {if $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="view" value="{$APPLY}">&nbsp;&nbsp;
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr class="letra12">
        <td align="right" width="300"><b>{$room.LABEL} : </td>
        <td align="left" width="200"><b>{$SELECT} </td>
	 <td align="left" width="300">  </td>
    </tr>
    <tr class="letra12">
        <td align="right" width="300"><b>{$action_ci.LABEL} : </td>
        <td align="left" width="200">{$action_ci.INPUT} </td>
	 <td align="left" width="300">  </td>
    </tr>
    <tr class="letra12">
        <td align="right" width="300"><b>{$action_co.LABEL} : </b></td>
        <td align="left" width="200">{$action_co.INPUT} </td>
	 <td align="left" width="300">  </td>
    </tr>
</table>
<input class="button" type="hidden" name="id" value="{$ID}" />