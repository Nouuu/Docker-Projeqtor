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

if (! array_key_exists('operation',$_REQUEST)) {
  throwError('operation parameter not found in REQUEST');
}
$operation=$_REQUEST['operation'];

if (! array_key_exists('class',$_REQUEST)) {
  throwError('class parameter not found in REQUEST');
}
$class=$_REQUEST['class'];

$isReport=(RequestHandler::isCodeSet('isReport') and isNewGui())?RequestHandler::getValue('isReport'):'false';
$userId=getSessionUser()->id;
$menuName=($isReport=='false')?'menu'.ucfirst($class):$class;
$menuId=SqlList::getIdFromName('Menu', $menuName);
if (!$menuId) {
  //throwError("impossible to reteive menu id from name '$menuName'");
}
$ms=SqlElement::getSingleSqlElementFromCriteria('MenuCustom', array('idUser'=>$userId,'name'=>$menuName));
if ($operation=='add') {
  if ($ms->id) {
    throwError("impossible to store already existing custom menu '$class' for user '$userId'");
  } 
  
  $ms->idUser=$userId;
  $ms->idMenu=$menuId;
  $ms->name=$menuName;
  $ms->idRow=(isNewGui())?Parameter::getUserParameter('idFavoriteRow'):null;
  if(isNewGui()){
    $sortOrder=$ms->getMaxValueFromCriteria('sortOrder',array('idRow'=>$ms->idRow));
    $ms->sortOrder=$sortOrder+1;
  }
  $result=$ms->save();
  //echo $result;
} else if ($operation=='remove') {
  if (! $ms->id) {
    throwError("impossible to delete none existing custom menu '$class' for user '$userId'");
  }
  $result=$ms->delete();
  //echo $result;
} else {
  throwError("incorrect value for parameter operation='$operation'");
}
$currentMenu=Parameter::getUserParameter('defaultMenu');
echo $currentMenu;
?>