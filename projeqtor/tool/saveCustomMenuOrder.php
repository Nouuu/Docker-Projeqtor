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
 * Save Today displayed info list
 */
require_once "../tool/projeqtor.php";

$idSourceFrom = RequestHandler::getValue('idSourceFrom');
$idSourceTo = RequestHandler::getValue('idSourceTo');
$idRow = RequestHandler::getValue('idRow');
$defaultMenu = RequestHandler::getValue('defaultMenu');
$customArray = RequestHandler::getValue('customArray');
$customArray = explode(',', $customArray);
unset($customArray[0]);
$customArrayOrder = array_flip($customArray);
$customArray = implode("','", $customArray);

Sql::beginTransaction();
if($idSourceFrom == $idSourceTo){
  $where = "idUser=".getSessionUser()->id." and idRow=".$idRow." and name in ('".$customArray."')";
}else{
  $where = "idUser=".getSessionUser()->id." and name in ('".$customArray."')";
}
$customMenu=new MenuCustom();
$customMenuList=$customMenu->getSqlElementsFromCriteria(null, false, $where);
foreach ($customMenuList as $menu) {
	$menu->sortOrder = $customArrayOrder[$menu->name];
	$menu->idRow = $idRow;
	$menu->save();
	unset($customArrayOrder[$menu->name]);
}
foreach ($customArrayOrder as $name=>$sortOrder){
  $customMenu->name = $name;
  $customMenu->idUser = getSessionUser()->id;
  $customMenu->idRow = $idRow;
  $customMenu->sortOrder = $sortOrder;
  $customMenu->save();
}
Sql::commitTransaction();
?>