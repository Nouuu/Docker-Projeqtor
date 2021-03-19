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
 * Save some information to session (remotely).
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicListApprover.php');
$refType=$_REQUEST['approverRefType'];
Security::checkValidClass($refType);

$refId=$_REQUEST['approverRefId'];

$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$obj=new $refType($refId);

$objList=new Affectable();
$aff=new Affectation();
$critWhere = "idle='0' and exists(select 'x' from " . $aff->getDatabaseTableName() . " aff ";
$critWhere .= " where aff.idResource=" . $objList->getDatabaseTableName() . ".id ";
$critWhere .= ($obj->idProject)?" and aff.idProject='" . Sql::fmtId($obj->idProject) . "'":"";
$critWhere .= ")";
$list=$objList->getSqlElementsFromCriteria(null,false,$critWhere, 'name asc');

?>
<select id="approverId" size="14"" name="approverId[]" multiple
onchange="selectApproverItem();"  ondblclick="saveApprover();"
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $lstObj) {
   $sel="";
   if (in_array($lstObj->id,$selectedArray)) {
    $sel=" selected='selected' ";
    $found[$lstObj->id]=true;
   }
   $name=($lstObj->name)?$lstObj->name:$lstObj->userName;
   echo "<option value='$lstObj->id'" . $sel . ">#".htmlEncode($lstObj->id)." - ".htmlEncode($name)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {
	   $lstObj=new Affectable($selected);
	   $name=($lstObj->name)?$lstObj->name:$lstObj->userName;
	   echo "<option value='$lstObj->id' selected='selected' >#".htmlEncode($lstObj->id)." - ".htmlEncode($name)."</option>";
	 }
 }
 ?>
</select>