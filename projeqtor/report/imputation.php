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

//echo "imputation.php";
include_once '../tool/projeqtor.php';

$userId=$_REQUEST['userId'];
$userId=Security::checkValidId($userId); // only allow digits
$rangeType=$_REQUEST['rangeType'];
$rangeType=preg_replace('/[^0-9a-zA-Z]/','',$rangeType); // only allow 0-9, a-z, A-Z
$rangeValue=$_REQUEST['rangeValue'];
$rangeValue=preg_replace('/[^0-9a-zA-Z]/','',$rangeValue); // only allow 0-9, a-z, A-Z
$idle=false;
if (array_key_exists('idle',$_REQUEST)) {
  $idle=true;
}
$showPlannedWork=false; 
if (array_key_exists('showPlannedWork',$_REQUEST)) {
  $showPlannedWork=true;
}
$hideDone=Parameter::getUserParameter('imputationHideDone');
$hideNotHandled=Parameter::getUserParameter('imputationHideNotHandled');
$displayOnlyCurrentWeekMeetings=Parameter::getUserParameter('imputationDisplayOnlyCurrentWeekMeetings');
if (Parameter::getGlobalParameter('displayOnlyHandled')=="YES") {
	$hideNotHandled=true;
} 
//echo '<div style="height:10px">';
ImputationLine::drawLines($userId, $rangeType, $rangeValue, $idle, $showPlannedWork, true, $hideDone, $hideNotHandled, $displayOnlyCurrentWeekMeetings);

//echo '</div>';
?>