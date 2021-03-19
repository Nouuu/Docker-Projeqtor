<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/** ============================================================================
 * 
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListPredefinedText.php');

$idTextable="";
$idType="";

if (isset($objectClass) and isset($objectId)) {
  $obj=new $objectClass($objectId);
  $refType=$objectClass;
  $nameType='id'.$objectClass.'Type';
  if (property_exists($obj, $nameType)) {
    $idType=$obj->$nameType;
  }
  $idTextable=SqlList::getIdFromTranslatableName('Textable', $refType);
} else {
  $refType=$_REQUEST['objectClass'];
  $idType=$_REQUEST['objectType'];
  $idTextable=SqlList::getIdFromTranslatableName('Textable', $refType);
  echo ("refType=$refType, idType=$idType, idTextable=$idTextable");
}
$crit="scope='Note' and (idTextable is null or idTextable=" . Sql::fmtId($idTextable) .")";
$crit.=" and (idType is null or idType=" . Sql::fmtId($idType) .") and idle=0";

$txt=new PredefinedNote();
$list=$txt->getSqlElementsFromCriteria(null, false, $crit, 'name asc');
if (count($list)==0) {
	return;
}
?>
<label for="dialogNotePredefinedNote" <?php if (isNewGui()) echo 'style="padding-top:10px;"';?>><?php echo i18n("colPredefinedNote");?>&nbsp;<?php if (!isNewGui()) echo ':';?>&nbsp;</label>
<select id="dialogNotePredefinedNote" name="dialogNotePredefinedNote" dojoType="dijit.form.FilteringSelect"
<?php echo autoOpenFilteringSelect();?>
onchange="noteSelectPredefinedText(this.value);"
class="input" style="width:345px">
 <option value=""></option>
 <?php
 foreach ($list as $lstObj) {
   echo '<option value="' . $lstObj->id .'" >'.htmlEncode($lstObj->name).'</option>';
 }
 
 ?>
</select>