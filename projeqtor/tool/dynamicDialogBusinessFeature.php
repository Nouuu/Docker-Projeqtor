<?php
/*
 * @author : qCazelles
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

//ADD qCazelles - Business Feature (Correction)
$businessFeatureId=null;
if ( array_key_exists('businessFeatureId',$_REQUEST)) {
  $businessFeatureId=$_REQUEST['businessFeatureId'];
}
$bf=new BusinessFeature($businessFeatureId);
//END ADD qCazelles - Business Feature (Correction)
?>
<table>
  <tr>
    <td>
      <form id='businessFeatureForm' name='businessFeatureForm' onSubmit="return false;">
      	<input id="businessFeatureObjectClass" name="businessFeatureObjectClass" type="hidden" value="<?php echo $objectClass;?>" />
        <input id="businessFeatureObjectId" name="businessFeatureObjectId" type="hidden" value="<?php echo $objectId;?>" />
        <?php //ADD qCazelles - Business Feature (Correction)
        if ($businessFeatureId!=null) { ?>
        <input id="businessFeatureId" name="businessFeatureId" type="hidden" value="<?php echo $businessFeatureId;?>" />
        <?php }
        //END ADD qCazelles - Business Feature (Correction) ?>
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section"><?php echo i18n('sectionBusinessFeature',array(i18n($objectClass),intval($objectId).' '.$object->name));?></td></tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
          <tr>
            <td class="dialogLabel"  >
              <label for="businessFeatureName" ><?php echo i18n('colName'); ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
            <?php //CHANGE qCazelles - Business Feature (Correction)
            //Old
						//<input type="text" size="100" id="businessFeatureName" name="businessFeatureName" value="" onkeyup="enableWidget('dialogBusinessFeatureSubmit');" />
            //New ?>
            <input type="text" size="100" id="businessFeatureName" name="businessFeatureName" value="<?php echo $bf->name;?>" onkeyup="enableWidget('dialogBusinessFeatureSubmit');" />
            <?php //END CHANGE qCazelles - Business Feature (Correction)?>
            </td>
          </tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td align="center">
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogBusinessFeature').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" disabled dojoType="dijit.form.Button" type="submit" id="dialogBusinessFeatureSubmit" onclick="protectDblClick(this);saveBusinessFeature();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>