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

Sql::beginTransaction();
$user=getSessionUser();
$crit=array('idUser'=>$user->id);
$today=new Today();
$todayList=$today->getSqlElementsFromCriteria($crit, false, 'sortOrder asc');
foreach ($todayList as $item) {
	if (isset($_REQUEST['dialogTodayParametersDelete' . $item->id]) and $_REQUEST['dialogTodayParametersDelete' . $item->id]=='1') {
		$item->delete();
	} else {
		if (isset($_REQUEST['dialogTodayParametersIdle' . $item->id])) {
			$item->idle=0;
		} else {
			$item->idle=1;
		}
		if (isset($_REQUEST['dialogTodayParametersOrder' . $item->id])) {
			$item->sortOrder=$_REQUEST['dialogTodayParametersOrder' . $item->id];
		}
		$item->save();
	}
}
// PeriodDays
$crit=array('idUser'=>$user->id,'idToday'=>null,'parameterName'=>'periodDays');
$tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter',$crit);
if (isset($_REQUEST['todayPeriodDays'])) {
	$tp->parameterValue=$_REQUEST['todayPeriodDays'];
} else {
	$tp->parameterValue='';
}
$tp->save();

//PeriodNotSet
$crit=array('idUser'=>$user->id,'idToday'=>null,'parameterName'=>'periodNotSet');
$tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter',$crit);
if (isset($_REQUEST['todayPeriodNotSet'])) {
  $tp->parameterValue=1;
} else {
  $tp->parameterValue=0;
}
$tp->save();

if (isset($_REQUEST['todayRefreshDelay'])) {
  $delay=$_REQUEST['todayRefreshDelay'];
  if (!$delay or !is_numeric($delay)) $delay=5;
  Parameter::storeUserParameter('todayRefreshDelay',$delay);
}
if (isset($_REQUEST['todayScrollDelay'])) {
  $delay=$_REQUEST['todayScrollDelay'];
  if (!$delay or !is_numeric($delay)) $delay=10;
  Parameter::storeUserParameter('todayScrollDelay',$delay);
}

Sql::commitTransaction();

include "../view/today.php";
?>