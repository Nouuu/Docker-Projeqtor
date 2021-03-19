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
if (! array_key_exists('action',$_REQUEST)) {
  throwError('action parameter not found in REQUEST');
}
$action=$_REQUEST['action'];
if ($action=='status') {
  if (! array_key_exists('status',$_REQUEST)) {
	throwError('status parameter not found in REQUEST');
  }
  $status=$_REQUEST['status'];
  if (! array_key_exists('item',$_REQUEST)) {
	throwError('item parameter not found in REQUEST');
  }
  $item=$_REQUEST['item'];
  $cs=new ColumnSelector($item);
  if (! $cs->id) {
  	errorLog("ERROR in saveSelectedColumn, impossible to retrieve ColumnSelector($item)");
  } else {
  	$cs->hidden=($status=='hidden')?1:0;
    $cs->save();
  }
} else if ($action=='reset') {
  if (! array_key_exists('objectClass',$_REQUEST)) {
	throwError('objectClass parameter not found in REQUEST');
  }
  $objectClass=$_REQUEST['objectClass'];
  Security::checkValidClass($objectClass);
  $clause="scope='list' and objectClass='$objectClass' and idUser=$user->id ";
  $cs=new ColumnSelector();
  $resPurge=$cs->purge($clause); 
} else if ($action=='width') {
  if (! array_key_exists('width',$_REQUEST)) {
    throwError('width parameter not found in REQUEST');
  }
  $width=$_REQUEST['width'];
  if (! array_key_exists('item',$_REQUEST)) {
    throwError('item parameter not found in REQUEST');
  }
  $item=$_REQUEST['item'];
  $cs=new ColumnSelector($item);
  if (! $cs->id) {
    errorLog("ERROR in saveSelectedColumn, impossible to retrieve ColumnSelector($item)");
  } else {
    $cs->widthPct=$width;
    $cs->save();
  }
}
Sql::commitTransaction();
?>