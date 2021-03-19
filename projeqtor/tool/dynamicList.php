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
 * Get a list , dynamically.
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/dynamicList.php');
$ref1Type=$_REQUEST['linkRef1Type'];
Security::checkValidClass($ref1Type);

$ref1Id=$_REQUEST['linkRef1Id'];
$ref2Type=SqlList::getNameFromId('Linkable', $_REQUEST['linkRef2Type']);
Security::checkValidClass($ref2Type);

//$id=$_REQUEST['id'];

$obj=new $ref1Type($ref1Id);

$crit = array ( 'idle'=>'0', 'idProject'=>$obj->idProject);

$objList=new $ref2Type();
$list=$objList->getSqlElementsFromCriteria($crit,false,null);
if ($ref2Type=="Project") {
	$wbsList=SqlList::getList('Project','sortOrder');
  $sepChar=Parameter::getUserParameter('projectIndentChar');
  if (!$sepChar) $sepChar='__';
}
?>
<select id="linkRef2Id" multiple="false" name="linkRef2Id"
onchange="enableWidget('dialogLinkSubmit');"  
class="selectList" >
 <?php
 foreach ($list as $lstObj) {
   $val=$lstObj->name;
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
   echo "<option value='$lstObj->id'>#".htmlEncode($lstObj->id)." - ".htmlEncode($val)."</option>";
 }
 ?>
</select>