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

/** ===========================================================================
 * Delete multiple objects
 */

require_once "../tool/projeqtor.php";

// Get the object class from request
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('objectClass parameter not found in REQUEST');
}
$className=$_REQUEST['objectClass'];
Security::checkValidClass($className);
if ($className=='Project') {
  Project::$_deleteProjectInProgress=true;
}
if (! array_key_exists('selection',$_REQUEST)) {
  throwError('selection parameter not found in REQUEST');
}
$selection=trim($_REQUEST['selection']);
$selectList=explode(';',$selection);

if (!$selection or count($selectList)==0) {
	 $summary='<div class=\'messageWARNING\' >'.i18n('messageNoData',array(i18n($className)),ENT_QUOTES,'UTF-8').'</div >';
	 echo '<input type="hidden" id="summaryResult" value="'.$summary.'" />';
	 exit;
}
$cptOk=0;
$cptError=0;
$cptWarning=0;
$cptNoChange=0;
echo "<table>";
SqlElement::setDeleteConfirmed();
$selectListSorted=array();
foreach ($selectList as $id) {
  $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>$className,'refId'=>$id));
  if ($pe->id) {
    $selectListSorted[$pe->wbsSortable]=$id;
  } else {
    $selectListSorted[$id]=$id;
  }
}
krsort($selectListSorted);
foreach ($selectListSorted as $id) {
	if (!trim($id)) { continue;}
	Security::checkValidId($id);
	Sql::beginTransaction();
	echo '<tr>';
	echo '<td valign="top"><b>#'.$id.'&nbsp:&nbsp;</b></td>';

	$item=new $className($id);
	if (property_exists($item, 'locked') and $item->locked) {
		Sql::rollbackTransaction();
    $cptWarning++;
    echo '<td><span class="messageWARNING" >' .i18n($className). " #" . htmlEncode($item->id) . ' '.i18n('colLocked'). '</span></td>';
		continue;
	}
  $resultSave=$item->delete();
	$resultSave=str_replace('<br/><br/>','<br/>',$resultSave);
	$statusSave = getLastOperationStatus ( $resultSave );
	if ($statusSave=="ERROR" ) {
	  Sql::rollbackTransaction();
	  $cptError++;
	} else if ($statusSave=="OK") {
	  Sql::commitTransaction();
	  $cptOk++;
	} else if ($statusSave=="NO_CHANGE") {
	  Sql::commitTransaction();
	  $cptNoChange++;
	} else { 
	  Sql::rollbackTransaction();
	  $cptWarning++;
  }
  echo '<td><div style="padding: 0px 5px;" class="message'.$statusSave.'" >' . $resultSave . '</div></td>';
  echo '</tr>';
}
echo "</table>";
$summary="";
if ($cptError) {
  $summary.='<div class=\'messageERROR\' >' . $cptError." ".i18n('resultError') . '</div>';
}
if ($cptOk) {
  $summary.='<div class=\'messageOK\' >' . $cptOk." ".i18n('resultOk') . '</div>';
}
if ($cptWarning) {
  $summary.='<div class=\'messageWARNING\' >' . $cptWarning." ".i18n('resultWarning') . '</div>';
}
if ($cptNoChange) {
  $summary.='<div class=\'messageNO_CHANGE\' >' . $cptNoChange." ".i18n('resultNoChange') . '</div>';
}
echo '<input type="hidden" id="summaryResult" value="'.$summary.'" />';
?>