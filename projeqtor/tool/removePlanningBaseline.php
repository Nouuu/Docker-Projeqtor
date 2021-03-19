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
 * Run planning
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/removePlanningBaseline.php');

if (! array_key_exists('baselineId',$_REQUEST)) {
  throwError('baselineId parameter not found in REQUEST');
}
$id=$_REQUEST['baselineId']; // validated to be numeric in SqlElement base constructor
Security::checkValidId($id);

$baseline=new Baseline($id);
if ($baseline->idUser!=getSessionUser()->id) {
  throwError('invalid user : you cannot delete baseline created by someone else');
}

projeqtor_set_time_limit(600);
Sql::beginTransaction();
$result=$baseline->deleteWithPlanning();
$result.= '<input type="hidden" id="lastPlanStatus" value="OK" />';
ob_start();
displayLastOperationStatus($result);
ob_clean();

include "dynamicDialogPlanBaseline.php";

?>