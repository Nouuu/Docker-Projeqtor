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
scriptLog('   ->/tool/dynamicListLinkProviderTerm.php');

$selected=null;
if (array_key_exists('selected',$_REQUEST)) {
  $selected=$_REQUEST['selected'];
}
$selectedArray=explode('_',$selected);
$providerBillId = RequestHandler::getId('providerBillId');
$provBill = new ProviderBill($providerBillId);
$obj=new ProviderTerm();
$critFld=array();
$critVal=array();
$critFld[] ='idProviderBill';
$critVal[] = null;
$critFld[] ='idProject';
$critVal[] = $provBill->idProject;
if($provBill->taxPct > 0 ){
  $critFld[]='taxPct';
  $critVal[]=$provBill->taxPct;
}
ob_start();
htmlDrawOptionForReference('idProviderTerm', null, $obj, true,$critFld,$critVal);
$listOption=ob_get_clean();
$listFull=array();
$split=explode('</option><option',$listOption);
foreach ($split as $string) {
	if (!trim($string)) continue;
	$start=strpos($string,'value="')+7;
  if (!$start) continue;
  $end=strpos($string,'"',$start);
  $val=substr($string,$start,$end-$start);
  $listFull[$val]=SqlList::getNameFromId('ProviderTerm', $val);
}
foreach ($selectedArray as $val) {
  $listFull[$val]=SqlList::getNameFromId('ProviderTerm', $val);
}
?>
<select id="linkProviderTerm" name="linkProviderTerm[]"
size="14" multiple class="selectList" 
ondblclick="saveProviderTermFromProviderBill();"
class="selectList" >
 <?php
 //echo $listOption;
 foreach ($listFull as $val=>$name) {
   $sel=(in_array($val, $selectedArray))?" selected='selected' ":'';
   echo "<option value='$val'"  . $sel . ">".htmlEncode($name)."</option>";
 }
 ?>
</select>