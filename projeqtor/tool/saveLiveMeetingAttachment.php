<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
* Copyright 2009-2016 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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


require_once "../tool/projeqtor.php";

$objClass=(RequestHandler::isCodeSet('objectClass'))?RequestHandler::getValue('objectClass'):false;
$objName=(RequestHandler::isCodeSet('objName'))?RequestHandler::getValue('objName'):false;
$idProj=(RequestHandler::isCodeSet('idProj'))?RequestHandler::getValue('idProj'):false;


$getType= new Type();
$where= "scope= '".$objClass."'";
$listType=$getType->getSqlElementsFromCriteria(null,false,$where);
$type='id'.$objClass.'Type';
if(count($listType)==0){
  echo "KO - no type for $objClass";
  exit;
}
$typeObj=reset($listType);
$valType=$typeObj->id;
$statusList=SqlList::getList('Status');
$valStatus=null;
foreach ($statusList as $idS=>$nameS) {
  $valStatus=$idS;
  break;
}

Sql::beginTransaction();

$obj=new $objClass();
$obj->name=$objName;
$obj->idStatus=$valStatus;
$obj->$type=$valType;
$obj->creationDate= date('Y-m-d');
$obj->idProject=$idProj;
$right=securityGetAccessRightYesNo('menu'.$objClass, 'create', $obj );
if ($right != 'YES') {
  $result = '<br/>' . i18n ( 'errorCreateRights' );
  $result .= ' <span style="font-style:italic">('.i18n($objClass).')</span>';
  echo $result;
  exit;
}
$result=$obj->simpleSave();
displayLastOperationStatus($result);

?>