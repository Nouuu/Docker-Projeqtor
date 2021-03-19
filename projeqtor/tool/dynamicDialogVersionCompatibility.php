<?php
/*
 * 	@author: qCazelles
 */
if (! array_key_exists('objectClass',$_REQUEST)) {
	throwError('Parameter objectClass not found in REQUEST');
}
$objectClass=$_REQUEST['objectClass'];
Security::checkValidClass($objectClass);

if (! array_key_exists('objectId',$_REQUEST)) {
	throwError('Parameter objectId not found in REQUEST');
}
$objectId=$_REQUEST['objectId'];
Security::checkValidId($objectId);

$object=new $objectClass($objectId);

$str=new VersionCompatibility();

$listClass='ProductVersion';
$critClass='ProductVersion';

//ADD aGaye - Ticket 179
$directAccessToList='false';
$paramDirect=Parameter::getUserParameter('directAccessToComponentList');
if ($paramDirect=='YES') {
  $directAccessToList='true';
}
//END aGaye - Ticket 179
?>
<table>
  <tr>
    <td>
      <form id='versionCompatibilityForm' name='versionCompatibilityForm' onSubmit="return false;">
        <input id="versionCompatibilityObjectClass" name="versionCompatibilityObjectClass" type="hidden" value="<?php echo $objectClass;?>" />
        <input id="versionCompatibilityObjectId" name="versionCompatibilityObjectId" type="hidden" value="<?php echo $objectId;?>" />
        <input id="versionCompatibilityListClass" name="versionCompatibilityListClass" type="hidden" value="<?php echo $listClass;?>" />
        <?php //ADD aGaye - Ticket 179?>
        <input id="directAccessToList" name="directAccessToList" type="hidden" value="<?php echo $directAccessToList;?>" />
        <input id="directAccessToListButton" name="directAccessToListButton" type="hidden" value="dialogVersionCompatibilitySubmit" />
        <?php //END aGaye - Ticket 179?>
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section"><?php echo i18n('sectionVersion' ,array(i18n($objectClass),intval($objectId).' '.$object->name));?></td></tr>  
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
          <tr>
            <td class="dialogLabel"  >
              <label for="versionCompatibilityListId" ><?php echo i18n($listClass) ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <select size="14" id="versionCompatibilityListId" name="versionCompatibilityListId[]"
                multiple class="selectList" onchange="enableWidget('dialogVersionCompatibilitySubmit');"  ondblclick="saveVersionCompatibility();" value="">
                  <?php htmlDrawOptionForReference('id'.$listClass, null, null, true); ?>
              </select>
            </td>
            <td style="vertical-align: top">
              <div style="position:relative">
              <button id="versionCompatibilityDetailButtonProduct" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('showDetail') . ' '. i18n('ProductVersion');?>"
                iconClass="iconSearch22 iconSearch iconSize22 imageColorNewGui" class="notButton notButtonRounded">
                <script type="dojo/connect" event="onClick" args="evt">
                <?php $canCreate=securityGetAccessRightYesNo('menuProductVersion', 'create') == "YES"; ?>
                showDetail('versionCompatibilityListId', <?php echo $canCreate;?>, 'ProductVersion', true);
                </script>
              </button>
              <img style="position:absolute;right:-5px;top:0px;height:12px;" src="../view/css/images/iconProductVersion16.png" />
              </div>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
        </table>
        <table>  
          <tr>
            <td class="dialogLabel" >
              <label for="versionCompatibilityComment" ><?php echo i18n("colComment") ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
              <textarea dojoType="dijit.form.Textarea"
                id="versionCompatibilityComment" name="versionCompatibilityComment"
                style="width: 400px;"
                maxlength="4000"
                class="input"><?php echo $str->comment;?></textarea>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td align="center">
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogVersionCompatibility').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" disabled dojoType="dijit.form.Button" type="submit" id="dialogVersionCompatibilitySubmit" onclick="protectDblClick(this);saveVersionCompatibility();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>