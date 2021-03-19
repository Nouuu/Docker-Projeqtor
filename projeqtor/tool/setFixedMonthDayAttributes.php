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

/** ===========================================================================
 * Set visibility on attributes of NotificationDefinition
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/setFixedMonthDayAttributes.php');

if (! array_key_exists('colName',$_REQUEST)) {
  throwError('colName parameter not found in REQUEST');
}
$colName = $_REQUEST['colName'];

if (! array_key_exists('idNotificationDefinition',$_REQUEST)) {
  throwError('idNotificationDefinition parameter not found in REQUEST');
}
$idNotificationDefinition = $_REQUEST['idNotificationDefinition'];

if (! array_key_exists('everyMonth',$_REQUEST)) {
  throwError('everyMonth parameter not found in REQUEST');
}
$everyMonth = $_REQUEST['everyMonth'];

if (! array_key_exists('everyYear',$_REQUEST)) {
  throwError('everyYear parameter not found in REQUEST');
}
$everyYear = $_REQUEST['everyYear'];

$_REQUEST['objectClass']='NotificationDefinition';
$_REQUEST['objectId']=$idNotificationDefinition;
$_REQUEST['everyMonth']=$everyMonth;
$_REQUEST['everyYear']=$everyYear;
$_REQUEST['colName']=$colName;
include '../view/objectDetail.php';

?>