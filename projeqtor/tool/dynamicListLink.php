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
scriptLog('   ->/tool/dynamicListLink.php');
$ref1Type=$_REQUEST['linkRef1Type'];
Security::checkValidClass($ref1Type);

$ref1Id=$_REQUEST['linkRef1Id'];
//$ref2Type=SqlList::getNameFromId('Linkable', $_REQUEST['linkRef2Type']);
$ref2TypeObj=new Linkable($_REQUEST['linkRef2Type']); // SqlElement base constructor validates numeric argument.
$ref2Type=$ref2TypeObj->name;
Security::checkValidClass($ref2Type);

//$id=$_REQUEST['id'];
$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);

$obj=new $ref1Type($ref1Id);
if ($ref2Type) {
  $objList=new $ref2Type();
  if (property_exists($objList, "idProject") and property_exists($obj, "idProject")) {
    $crit = array ( 'idle'=>'0', 'idProject'=>($ref1Type=='Project')?$obj->id:$obj->idProject);
    $list=$objList->getSqlElementsFromCriteria($crit,false,null);
  } else if ($ref2Type=='DocumentVersionFull' or $ref2Type=='DocumentVersion') {
    $doc=new Document();
  	$critWhere = "idle='0' and exists(select 'x' from " . $doc->getDatabaseTableName() . " doc where doc.id=idDocument ";
  	if (property_exists($obj, "idProject")) $critWhere.="and doc.idProject='" . Sql::fmtId($obj->idProject) . "'";
    $critWhere.=")";
    $list=$objList->getSqlElementsFromCriteria(null,false,$critWhere, 'id desc');
  } else {
  	$crit = array ( 'idle'=>'0');
  	$list=$objList->getSqlElementsFromCriteria($crit,false,null, 'id desc');
  }
} else {
  $list=array();
}
if ($ref2Type=="Project") {
  $wbsList=SqlList::getList('Project','sortOrder');
  $sepChar=Parameter::getUserParameter('projectIndentChar');
  if (!$sepChar) $sepChar='__';
  $wbsLevelArray=array();
}
?>
<select id="linkRef2Id" size="14" name="linkRef2Id[]" multiple
onchange="selectLinkItem();"  ondblclick="saveLink();"
class="selectList" >
 <?php
 $found=array();
 foreach ($list as $lstObj) {
   $sel="";
   if (in_array($lstObj->id,$selectedArray)) {
    $sel=" selected='selected' ";
    $found[$lstObj->id]=true;
   }
   $val=$lstObj->name;
   if ($ref2Type=="Contact" ) {
     if ($obj and $obj->id and property_exists($obj, 'idClient')) {
       if ($obj->idClient and $obj->idClient!=$lstObj->idClient) {
         continue;
       }
     }
   }
   if ($ref2Type=="Project" and $sepChar!='no') {
     $wbs=$wbsList[$lstObj->id];
     $wbsTest=$wbs;
     $level=1;
     while (strlen($wbsTest)>3) {
       $wbsTest=substr($wbsTest,0,strlen($wbsTest)-6);
       if (array_key_exists($wbsTest, $wbsLevelArray)) {
         $level=$wbsLevelArray[$wbsTest]+1;
         $wbsTest="";
       }
     }
     $wbsLevelArray[$wbs]=$level;
     $sep='';for ($i=1; $i<$level;$i++) {$sep.=$sepChar;}
     $val = $sep.$val;
   }
   echo "<option value='$lstObj->id'" . $sel . ">#".htmlEncode($lstObj->id)." - ".htmlEncode($val)."</option>";
 }
 foreach ($selectedArray as $selected) {
	 if ($selected and ! isset($found[$selected]) ) {
	   $lstObj=new $ref2Type($selected);
	   echo "<option value='$lstObj->id' selected='selected' >#".htmlEncode($lstObj->id)." - ".htmlEncode($lstObj->name)."</option>";
	 }
 }
 ?>
</select>