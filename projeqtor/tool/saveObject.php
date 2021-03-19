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

/**
 * ===========================================================================
 * Save the current object : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 * The old values are fetched in $currentObject of SESSION
 * Only changed values are saved.
 * This way, 2 users updating the same object don't mess.
 */
require_once "../tool/projeqtor.php";
// Get the object class from request

if (! array_key_exists ( 'objectClassName', $_REQUEST )) {
  throwError ( 'objectClassName parameter not found in REQUEST' );
}
$className = $_REQUEST ['objectClassName'];

if ($className == "Workflow") {
  ini_set ( 'max_input_vars', 5000 );
}
$ext = "";
if (! array_key_exists ( 'comboDetail', $_REQUEST )) {
  // Get the object from session(last status before change)
  $obj=SqlElement::getCurrentObject(null,null,true,false);
  if (! is_object ( $obj )) {
    throwError ( 'last saved object is not a real object' );
  }
  // compare expected class with object class
  if ($className != get_class ( $obj )) {
    throwError ( 'last save object (' . get_class ( $obj ) . ') is not of the expected class (' . $className . ').' );
  }
} else {
  $obj=SqlElement::getCurrentObject(null,null,true,false,true);
  $ext = "_detail";
}
if (array_key_exists ( 'confirmed', $_REQUEST )) {
  if ($_REQUEST ['confirmed'] == 'true') {
    SqlElement::setSaveConfirmed ();
  }
}
Sql::beginTransaction ();
// get the modifications (from request)
$newObj = new $className ();
if ($className=='PeriodicMeeting') RequestHandler::unsetCode('moveToAfterCreate');
$newObj->fillFromRequest ( $ext );

if ($newObj->id == '0') {
  $newObj->id = null;
}
if ($newObj->id and $obj->id and $newObj->id != $obj->id) {
  throwError ( 'last save object (' . get_class ( $obj ) . ' #' . $obj->id . ') is not the expected object (' . $className . ' #' . $newObj->id . ').' );
}
if ((get_class($newObj)=='Ticket') and $newObj->id and (! is_object($newObj->WorkElement) or ! $newObj->WorkElement->id)) {
  $we=SqlElement::getSingleSqlElementFromCriteria('WorkElement', array('refType'=>'Ticket','refId'=>$newObj->id));
  $newObj->WorkElement=$we;
  $newObj->fillFromRequest($ext); // Execute again fillFromRequest to get data
}
$result='';
$isStop=false;
if (array_key_exists('checklistDefinitionId',$_REQUEST) and array_key_exists('checklistId',$_REQUEST)) {
  $included=true;
  include "controlChecklist.php";
  $included=false;
  if(trim($result)!=''){
    $isStop=true;
    $result.=($newObj->control()!='OK')?$newObj->control():'';
    Sql::rollbackTransaction ();
    $status = getLastOperationStatus ( $result );
  }else{
    $result='OK';
  }
  
}
// save to database
if($isStop==false){
    if (RequestHandler::isCodeSet('selectedResource') and ($newObj->id or RequestHandler::getValue('selectedResource')=='false')) {
      RequestHandler::unsetCode('selectedResource');
    }
    
    if(get_class ( $newObj )=='Activity' and RequestHandler::isCodeSet('selectedResource')){
      $selectedRes=RequestHandler::getValue('selectedResource');
      if ($selectedRes=='false') $selectedRes=null;
      $result = $newObj->save ($selectedRes);
    }else{
      $result = $newObj->save ();
    }
  
  // Check if checklist button must be displayed
  $crit = "nameChecklistable='" . get_class ( $newObj ) . "'";
  $type = 'id' . get_class ( $newObj ) . 'Type';
  if (property_exists ( $newObj, $type )) {
    $crit .= ' and (idType is null ';
    if ($newObj->$type) {
      $crit .= " or idType='" . $newObj->$type . "'";
    }
    $crit .= ')';
  }
  $cd = new ChecklistDefinition ();
  $cdList = $cd->getSqlElementsFromCriteria ( null, false, $crit );
  if (count ( $cdList ) > 0 and $newObj->id) {
    $buttonCheckListVisible = "visible";
  } else {
    $buttonCheckListVisible = "hidden";
  }
  echo '<input type="hidden" id="buttonCheckListVisibleObject" value="'.$buttonCheckListVisible.'" />';
  
  $status = getLastOperationStatus ( $result );
  // Message of correct saving
  if ($status == "OK") {
    Sql::commitTransaction ();
  } else {
    Sql::rollbackTransaction ();
  }
  
  if ($status == "OK" and $className=='Project') {
    if ($newObj->name!=$obj->name or $newObj->idProject!=$obj->idProject) {
      echo '<input type="hidden" id="needProjectListRefresh" value="true" />';
    }
  }
  if ($status == "OK") {
    if (! array_key_exists ( 'comboDetail', $_REQUEST )) {
      SqlElement::setCurrentObject(new $className ( $newObj->id ));
    }
  }
  if ($status == "OK") {
    //$createRight=securityGetAccessRightYesNo('menu' . $className, 'create');
    //if (!$newObj->id) {
    //  $updateRight=$createRight;
    //} else {
      $updateRight=securityGetAccessRightYesNo('menu' . $className, 'update', $newObj);
    //}
    $deleteRight=securityGetAccessRightYesNo('menu' . $className, 'delete', $newObj);
    //$newObj
    //echo "<input type='hidden' id='createRightAfterSave' value='$createRight' />";
    echo "<input type='hidden' id='updateRightAfterSave' value='$updateRight' />";
    echo "<input type='hidden' id='deleteRightAfterSave' value='$deleteRight' />";
  }
  $globalResult=$result;
  $globalStatus=$status;
  if (array_key_exists('checklistDefinitionId',$_REQUEST) and array_key_exists('checklistId',$_REQUEST)) {
    $included=true;
    include "saveChecklist.php";
    $included=false;
    if ($globalStatus=='NO_CHANGE' and $status=='OK') {
      //$status = "OK";
      //$result => keep status of checklist save
    } else {
      $status=$globalStatus;
      $result=$globalResult;
    }
  }
  if (array_key_exists('joblistDefinitionId',$_REQUEST)) {
    $included=true;
    include "saveJoblist.php";
    $included=false;
    if ($globalStatus=='NO_CHANGE' and $status=='OK') {
      //$status = "OK";
      //$result => keep status of joblist save
    } else {
      $status=$globalStatus;
      $result=$globalResult;
    }
  }
}
echo '<div class="message' . $status . '" >' . formatResult ( $result ) . '</div>';

function formatResult($result) {
  if (array_key_exists ( 'comboDetail', $_REQUEST )) {
    $res = $result;
    $res = str_replace ( '"lastOperationStatus"', '"lastOperationStatusComboDetail"', $res );
    $res = str_replace ( '"lastSaveId"', '"lastSaveIdComboDetail"', $res );
    $res = str_replace ( '"lastOperation"', '"lastOperationComboDetail"', $res );
    return $res;
  } else {
    return $result;
  }
}
?>