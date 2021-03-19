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

// Get the KpiThreshold info
if (! array_key_exists('kpiThresholdId',$_REQUEST)) {
  throwError('kpiThresholdId parameter not found in REQUEST');
}
$kpiThresholdId=$_REQUEST['kpiThresholdId'];
Security::checkValidId($kpiThresholdId);

if (! array_key_exists('kpiDefinitionId',$_REQUEST)) {
  throwError('kpiDefinitionId parameter not found in REQUEST');
}
$kpiDefinitionId=$_REQUEST['kpiDefinitionId'];
Security::checkValidId($kpiDefinitionId);

if (! array_key_exists('kpiThresholdName',$_REQUEST)) {
  throwError('kpiThresholdName parameter not found in REQUEST');
}
$kpiThresholdName=$_REQUEST['kpiThresholdName'];

if (! array_key_exists('kpiThresholdValue',$_REQUEST)) {
  throwError('kpiThresholdValue parameter not found in REQUEST');
}
$kpiThresholdValue=$_REQUEST['kpiThresholdValue'];
Security::checkValidNumeric($kpiThresholdValue);

if (! array_key_exists('kpiThresholdColor',$_REQUEST)) {
  throwError('kpiThresholdColor parameter not found in REQUEST');
}
$kpiThresholdColor=$_REQUEST['kpiThresholdColor'];

Sql::beginTransaction();
$kpiThreshold=new KpiThreshold($kpiThresholdId);
$kpiThreshold->idKpiDefinition=$kpiDefinitionId;
$kpiThreshold->name=$kpiThresholdName;
$kpiThreshold->thresholdValue=$kpiThresholdValue;
$kpiThreshold->thresholdColor=$kpiThresholdColor;
$result=$kpiThreshold->save();
displayLastOperationStatus($result);
?>