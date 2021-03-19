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
scriptLog('   ->/tool/saveVersionProject.php');
// Get the info
if (! array_key_exists('versionProjectId',$_REQUEST)) {
  throwError('versionProjectId parameter not found in REQUEST');
}
$id=($_REQUEST['versionProjectId']);

if (! array_key_exists('versionProjectProject',$_REQUEST)) {
  throwError('versionProjectProject parameter not found in REQUEST');
}
$project=($_REQUEST['versionProjectProject']);

if (! array_key_exists('versionProjectVersion',$_REQUEST)) {
  throwError('versionProjectVersion parameter not found in REQUEST');
}
$version=($_REQUEST['versionProjectVersion']);

if (! array_key_exists('versionProjectStartDate',$_REQUEST)) {
  throwError('versionProjectStartDate parameter not found in REQUEST');
}
$startDate=($_REQUEST['versionProjectStartDate']);

if (! array_key_exists('versionProjectEndDate',$_REQUEST)) {
  throwError('versionProjectEndDate parameter not found in REQUEST');
}
$endDate=($_REQUEST['versionProjectEndDate']);

$idle=0;
if (array_key_exists('versionProjectIdle',$_REQUEST)) {
  $idle=1;
}
Sql::beginTransaction();
$versionProject=new VersionProject($id);

$versionProject->idProject=$project;
$versionProject->idVersion=$version;
$versionProject->idle=$idle;
$versionProject->startDate=$startDate;
$versionProject->endDate=$endDate;

global $doNotUpdateAllVersionProject; // for Perfs improvment
$doNotUpdateAllVersionProject=true;

$result=$versionProject->save();

$versionProject->propagateCreationToComponentVersions();
$doNotUpdateAllVersionProject=false; // Finish perfs improvment

// Message of correct saving
displayLastOperationStatus($result);
?>