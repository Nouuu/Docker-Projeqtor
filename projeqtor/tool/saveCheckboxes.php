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
 * This script stores checkboxes' states
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveCheckboxes.php');
header("Content-Type: text/plain"); 
$toStore = (isset($_REQUEST["toStore"])) ? $_REQUEST["toStore"] : NULL;
$toStore=explode(";",$toStore);
$objClass = (isset($_REQUEST["objectClass"])) ? $_REQUEST["objectClass"] : NULL;
Security::checkValidClass($objClass);

$user=getSessionUser();
$idUser = $user->id;
$obj=new $objClass();
Sql::beginTransaction();
$cs=new ColumnSelector();
$cs->purge("scope='export' and idUser=$idUser and objectClass='$objClass'");
foreach ($toStore as $store) {
	if (trim($store)) {
	  $cs=new ColumnSelector();
		$cs->scope='export';
		$cs->idUser=$idUser;
		$cs->objectClass=$objClass;
		$cs->field=$store;
		$cs->name=$obj->getColCaption($store);
		$cs->hidden=1;
		$res=$cs->save();
	}
}
Sql::commitTransaction();

?>
