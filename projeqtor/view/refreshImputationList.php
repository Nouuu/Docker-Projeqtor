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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refrehImputationList.php'); 

$rangeType=$_REQUEST['rangeType'];
$rangeValue=$_REQUEST['rangeValue'];
$userId=$_REQUEST['userId'];
$idle=false;
if (array_key_exists('idle',$_REQUEST)) {
    $idle=$_REQUEST['idle'];
}

$year=0;
if (array_key_exists('yearSpinnerT',$_REQUEST)) {
  $year=$_REQUEST['yearSpinnerT'];
}

$week=0;
if (array_key_exists('weekSpinnerT',$_REQUEST)) {
  $week=$_REQUEST['weekSpinnerT'];
}
if (intval($week)<10) $week='0'.intval($week);

$month=substr(date('Y-m-d',firstDayofWeek($week,$year)),5,-3);

$showPlannedWork=0;
if (array_key_exists('showPlannedWork',$_REQUEST)) {
    $showPlannedWork=1;
}
Parameter::storeUserParameter('imputationShowPlannedWork',$showPlannedWork);

$showId=0;
if (array_key_exists('showIdT',$_REQUEST)) {
  $showId=1;
}
Parameter::storeUserParameter('showId',$showId);
if($showId==1)$showId=true; else $showId=false;

$hideDone=0;
if (array_key_exists('hideDone',$_REQUEST)) {
	$hideDone=1;
}
Parameter::storeUserParameter('imputationHideDone',$hideDone);

$hideNotHandled=0;
if (array_key_exists('hideNotHandled',$_REQUEST)) {
	$hideNotHandled=1;
}
Parameter::storeUserParameter('imputationHideNotHandled',$hideNotHandled);

$displayOnlyCurrentWeekMeetings=0;
if (array_key_exists('displayOnlyCurrentWeekMeetings',$_REQUEST)) {
	$displayOnlyCurrentWeekMeetings=1;
}
Parameter::storeUserParameter('imputationDisplayOnlyCurrentWeekMeetings',$displayOnlyCurrentWeekMeetings);
?>
<form dojoType="dijit.form.Form" id="listForm" action="" method="post" >
  <input type="hidden" name="userId" id="userId" value="<?php echo htmlEncode($userId);?>"/>
  <input type="hidden" name="rangeType" id="rangeType" value="<?php echo htmlEncode($rangeType);?>"/>
  <input type="hidden" name="rangeValue" id="rangeValue" value="<?php echo htmlEncode($rangeValue);?>"/>
  <input type="checkbox" name="idle" id="idle" style="display: none;"/>
  <input type="checkbox" name="showPlannedWork" id="showPlannedWork" style="display: none;">
  <input type="checkbox" name="showIdT" id="showIdT" style="display: none;" />
  <input type="checkbox" name="hideDone" id="hideDone" style="display: none;" />
  <input type="checkbox" name="hideNotHandled" id="hideNotHandled" style="display: none;" />
  <input type="hidden" name="yearSpinnerT" id="yearSpinnerT" value=""/>
  <input type="hidden" name="weekSpinnerT" id="weekSpinnerT" value=""/>
  <input type="hidden" name="daysWorkFuture" id="daysWorkFuture" value="0"/>
  <input type="hidden" name="daysWorkFutureBlocking" id="daysWorkFutureBlocking" value="0"/>
  <input type="hidden" name="objectClass" id="objectClass" value="Work"/>
  <input type="hidden" name="dateWeek" id="dateWeek" value="<?php echo $year.$week; ?>"/>
  <input type="hidden" name="dateMonth" id="dateMonth" value="<?php echo $year.$month; ?>"/>
  <input type="checkbox" name="displayOnlyCurrentWeekMeetings" id="displayOnlyCurrentWeekMeetings" style="display: none;" />
  <input type="hidden" id="page" name="page" value="../report/imputation.php"/>
  <input type="hidden" id="outMode" name="outMode" value="" />
<?php 
ImputationLine::drawLines($userId, $rangeType, $rangeValue, $idle, $showPlannedWork, false, $hideDone, $hideNotHandled, $displayOnlyCurrentWeekMeetings, $week, $year, $showId);
?>
</form>