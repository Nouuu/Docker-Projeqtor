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
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

$idProjectType=null;
if (isset($_REQUEST['idProjectType'])) {
  $idProjectType=$_REQUEST['idProjectType'];
  Security::checkValidId($idProjectType);
}
$idProject=null;
if (isset($_REQUEST['idProject'])) {
  $idProject=$_REQUEST['idProject'];
  Security::checkValidId($idProject);
}
$idProfile=null;
if (isset($_REQUEST['idProfile'])) {
  $idProfile=$_REQUEST['idProfile'];
  Security::checkValidId($idProfile);
}
$lstCustom=Type::getClassList(($idProfile)?true:false);
Sql::beginTransaction();
$result="";
$rt=new RestrictType();
$status="NO_CHANGE";
foreach ($lstCustom as $class=>$name) {
  $list=SqlList::getList($class);
  foreach ($list as $id=>$val) {
    $name="checkType_$id";
    $crit=array('idType'=>$id);
    if ($idProject) {
      $crit['idProject']=$idProject;
    } else if ($idProjectType) {
      $crit['idProjectType']=$idProjectType;
    } else if ($idProfile) {
      $crit['idProfile']=$idProfile;
    }
    $result="";
    if (isset($_REQUEST['class_'.$name])) {
      $class=$_REQUEST['class_'.$name];
      $crit['className']=$class;
    }
    $rt=SqlElement::getSingleSqlElementFromCriteria('RestrictType', $crit);
    if (isset($_REQUEST[$name])) {
      if (!$rt->id) {
        $result=$rt->save();        
      }
    } else {
      if ($rt->id) {
        $result=$rt->delete();
      } 
    }
    if ($result) {
      $status=getLastOperationStatus($result);
      if ($status=='ERROR') {
        displayLastOperationStatus($result);
        exit;
      }
    }
  }
}

if ($status=='ERROR') {
  Sql::rollbackTransaction();
  echo '<div class="messageERROR" >' . $result . '</div>';
} else if ($status=='OK'){
  Sql::commitTransaction();
  Type::clearRestrictTypeCache();
  echo '<div class="messageOK" >' . i18n('messageParametersSaved') . '</div>';
} else {
  Sql::rollbackTransaction();
  echo '<div class="messageNO_CHANGE" >' . i18n('messageParametersNoChangeSaved') . '</div>';
}
echo '<input type="hidden" id="lastOperation" name="lastOperation" value="save">';
echo '<input type="hidden" id="lastOperationStatus" name="lastOperationStatus" value="' . $status .'">';

?>