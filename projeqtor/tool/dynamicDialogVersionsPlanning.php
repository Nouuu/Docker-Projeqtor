<?php
/*
 * @author : qCazelles
 */
?>
<table>
  <tr>
    <td>
      <form id='versionsPlanningForm' name='versionsPlanningForm' onSubmit="return false;">
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section">SELECT PRODUCT VERSIONS</td></tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
          <tr>
            <td class="dialogLabel"  >
              <label for="productVersionName" ><?php echo i18n('colName'); ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
				<select size="14" id="productVersionsListId" name="productVersionsListId[]"
                multiple class="selectList" onchange="enableWidget('dialogProductVersionsSubmit');"  ondblclick="saveProductContext();" value="">
                  <?php htmlDrawOptionForReference('idProductVersion', null, null, true); ?>
              </select>            
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td align="center">
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogVersionsPlanning').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" disabled dojoType="dijit.form.Button" type="submit" id="dialogProductVersionsSubmit" onclick="protectDblClick(this);displayVersionsPlanning();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>