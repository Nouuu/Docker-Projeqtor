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
 * Save some information about planning columns status.
 */
require_once "../tool/projeqtor.php";

Sql::beginTransaction();
$user=getSessionUser();
$action=$_REQUEST['action'];
if ($action=='status') {
  $status=$_REQUEST['status'];
  Security::checkValidAlphanumeric($status);
  $item=$_REQUEST['item'];
  Security::checkValidAlphanumeric($item);
  $crit=array('idUser'=>$user->id, 'idProject'=>null, 'parameterCode'=>'planningHideColumn'.$item);
  $param=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
  if ($param and $param->id) {
  	if ($status=='hidden') {
  		$param->parameterValue='1';
  		$param->save();
  	} else {
  		$param->delete();
  	}
  } else {
  	if ($status=='hidden') {
  		$param=new Parameter();
  		$param->idUser=$user->id;
  		$param->idProject=null;
  		$param->parameterCode='planningHideColumn'.$item;
  		$param->parameterValue='1';
  		$param->save();
  	}
  }
} else if ($action=='width') {
  $width=$_REQUEST['width'];
  Security::checkValidAlphanumeric($width);
  $item=$_REQUEST['item'];
  Security::checkValidAlphanumeric($item);
  $crit=array('idUser'=>$user->id, 'idProject'=>null, 'parameterCode'=>'planningColumnWidth'.$item);
  $param=SqlElement::getSingleSqlElementFromCriteria('Parameter', $crit);
  $param->parameterValue=intval($width);
  $param->save();
}
Sql::commitTransaction();
?>