<?php
/*
 *	@author: qCazelles 
 */

include_once("../tool/projeqtor.php");

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

$languageId=null;
if (array_key_exists('languageId',$_REQUEST)) {
	$languageId=$_REQUEST['languageId'];
	Security::checkValidId($languageId);
}

$listClass = 'Language';
$scopeClass='ProductLanguage';
if ($objectClass=='Product' or $objectClass=='Component') {
  $scope=$objectClass;
  $scopeClass='ProductLanguage';
} else if ($objectClass=='ProductVersion' or $objectClass=='ComponentVersion') {
  $scope=str_replace('Version','',$objectClass);
  $scopeClass='VersionLanguage';
} else {
  errorLog("ERROR : dynamicDialogProductLanguage to neither 'Product' nor 'Component' nor 'ProductVersion' nor 'ComponentVersion' but to  '$objectClass'");
  exit;  
}
$str=new $scopeClass($languageId);
$listId = $str->idLanguage;

$object=new $objectClass($objectId);

$critFld = null;
$critVals = null;
if ($objectClass == 'ProductVersion' or $objectClass == 'ComponentVersion') {
	if ($objectClass == 'ProductVersion') $typeId = 'idProduct';
	if ($objectClass == 'ComponentVersion') $typeId = 'idComponent';
	$critFld = 'id';
	$critVals = array();
	$vals = array();
	foreach (SqlList::getListWithCrit('ProductLanguage', array('idProduct'=>$object->$typeId), 'idLanguage') as $idLanguage) {
		$vals[] = $idLanguage;
	}	
	$critVals[] = $vals;
}

?>
<table>
  <tr>
    <td>
      <form id='productLanguageForm' name='productLanguageForm' onSubmit="return false;">
      	<input id="productLanguageObjectClass" name="productLanguageObjectClass" type="hidden" value="<?php echo $objectClass;?>" />
        <input id="productLanguageObjectId" name="productLanguageObjectId" type="hidden" value="<?php echo $objectId;?>" />
        <input id="productLanguageScopeClass" name="productLanguageScopeClass" type="hidden" value="<?php echo $scopeClass;?>" />  
        <input id="productLanguageScope" name="productLanguageScope" type="hidden" value="<?php echo $scope;?>" />
        <table>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td colspan="2" class="section"><?php echo i18n('sectionProductLanguage',array(i18n($objectClass),intval($objectId).' '.$object->name));?></td></tr>
          <tr><td>&nbsp;</td><td>&nbsp;</td></tr>  
          <tr>
            <td class="dialogLabel">
              <label for="productLanguageName" ><?php echo i18n('Language'); ?>&nbsp;<?php if(!isNewGui()){?>:<?php }?>&nbsp;</label>
            </td>
            <td>
				<select size="14" id="productLanguageListId" name="productLanguageListId[]"
                <?php if (!$languageId) echo 'multiple'; ?> class="selectList" onchange="enableWidget('dialogProductLanguageSubmit');"  ondblclick="if (this.value) saveProductLanguage();" value="">
                  <?php htmlDrawOptionForReference('id'.$listClass, $listId, $object, true, $critFld, $critVals);?>
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
      <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogProductLanguage').hide();">
        <?php echo i18n("buttonCancel");?>
      </button>
      <button class="mediumTextButton" disabled dojoType="dijit.form.Button" type="submit" id="dialogProductLanguageSubmit" onclick="protectDblClick(this);saveProductLanguage();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>

