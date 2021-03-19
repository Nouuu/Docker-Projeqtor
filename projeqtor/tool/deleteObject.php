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
 * Delete the current object : call corresponding method in SqlElement Class
 */

require_once "../tool/projeqtor.php";

// Get the object from session(last status before change)
$obj=SqlElement::getCurrentObject(null,null,true,false);
if (! is_object($obj)) {
  throwError('last saved object is not a real object');
}

// Get the object class from request
if (! array_key_exists('objectClassName',$_REQUEST)) {
  throwError('className parameter not found in REQUEST');
}
$className=$_REQUEST['objectClassName'];
if ($className=='Project') {
  Project::$_deleteProjectInProgress=true;
}
// compare expected class with object class
if ($className!=get_class($obj)) {
  throwError('last save object (' . get_class($obj) . ') is not of the expected class (' . $className . ').'); 
}
if (array_key_exists('confirmed',$_REQUEST) ) {
  if ($_REQUEST['confirmed']=='true') {
  	SqlElement::setDeleteConfirmed();
  }
}
Sql::beginTransaction();

$obj=new $className($obj->id); // Get the last saved version, to fetch last version for array of objects
$peName=$className.'PlanningElement';
$topItemType=null;
$topItemId=null;
// delete from database
if (property_exists($obj,$peName)) {
  PlanningElement::$_noDispatch=true;
  $topItemType=$obj->$peName->topRefType;
  $topItemId=$obj->$peName->topRefId;
}

$result=$obj->delete();
$resultStatus=getLastOperationStatus($result);
if ($resultStatus=='OK' and $topItemType and $topItemId) {
  PlanningElement::$_noDispatch=false;
  PlanningElement::updateSynthesis($topItemType, $topItemId);
  $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>$topItemType,'refId'=>$topItemId));
  $pe->renumberWbs();
}
BudgetElement::dispatchFinalize();

// Message of correct saving
if ($resultStatus=="ERROR") {
	Sql::rollbackTransaction();
  echo '<div class="messageERROR" >' . $result . '</div>';
} else if ($resultStatus=="OK" ) {
	Sql::commitTransaction();
  echo '<div class="messageOK" >' . $result . '</div>';
  SqlElement::unsetCurrentObject();
} else  if ($resultStatus=="INVALID") {
  Sql::rollbackTransaction();
  echo '<div class="messageWARNING" >' . $result . '</div>';
} else {
	Sql::commitTransaction();
  echo '<div class="messageWARNING" >'. $result .'</div>';
}
echo '<input type="hidden" id="buttonCheckListVisibleObject" value="hidden" />';
?>