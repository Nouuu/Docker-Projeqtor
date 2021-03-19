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
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/calendar.php');

  $user=getSessionUser();
  $collapsedList=Collapsed::getCollaspedList();
  $currentYear=strftime("%Y") ;
  $idCalendarDefinition=0;
  if (isset($_REQUEST['year'])) {
    $currentYear=$_REQUEST['year'];
    $currentYear=Security::checkValidYear($currentYear);
  }
  if (isset($_REQUEST['idCalendarDefinition'])) {
  	$idCalendarDefinition=$_REQUEST['idCalendarDefinition'];
  	Security::checkValidId($idCalendarDefinition);
  }
  if (isset($_REQUEST['copyYearFrom'])) {
  	$from=$_REQUEST['copyYearFrom'];
    $from=Security::checkValidId($from);
  	copyYear($from,$idCalendarDefinition, $currentYear);
  }
  if (isset($_REQUEST['day'])) {
  	$day = trim($_REQUEST['day']);
	  $day=Security::checkValidDateTime($day);
    switchDay($day,$idCalendarDefinition);
    $currentYear=substr($day,0,4);
  }
  
  //Damian
  $weekDay = RequestHandler::getValue('calendarDayFrom');
  $work = RequestHandler::getValue('calendarWorkFrom');
  if($weekDay and $work){
  	setDayWork($weekDay, $work, $currentYear, $idCalendarDefinition);
  }
  
  $cal=new Calendar;
  //$currentYear=date('YYYY');
  $cal->setDates($currentYear.'-01-01');
  $cal->idCalendarDefinition=$idCalendarDefinition;
  $result= $cal->drawSpecificItem('calendarView');
  echo $result;

function switchDay ($day,$idCalendarDefinition) {
  global $bankHolidays, $bankWorkdays;
  $cal=SqlElement::getSingleSqlElementFromCriteria('Calendar',array('calendarDate'=>$day, 'idCalendarDefinition'=>$idCalendarDefinition));
  if (!$cal->id) {
    $cal->setDates($day);
    $cal->idCalendarDefinition=$idCalendarDefinition;
    if (isOpenDay($day,$idCalendarDefinition)) {
      $cal->isOffDay=1;
    } else {
      $cal->isOffDay=0;
    }
    $cal->save();
  } else {
    $cal->delete();
  }
  $bankHolidays=array();
  $bankWorkdays=array();
}

function copyYear($from, $to, $currentYear) {
  if ($from==$to) return;
	$cal=new Calendar();
	$calList=$cal->getSqlElementsFromCriteria(array('idCalendarDefinition'=>$from, 'year'=>$currentYear));
	foreach ($calList as $cal) {
		$cp=SqlElement::getSingleSqlElementFromCriteria('Calendar',array('idCalendarDefinition'=>$to, 'day'=>$cal->day));
		$cp->setDates($cal->calendarDate);
		$cp->idCalendarDefinition=$to;
		$cp->name=$cal->name;
		$cp->isOffDay=$cal->isOffDay;
		$cp->idle=$cal->idle;
		$cp->save();
	}
}

//Damian
function setDayWork($weekDay, $work, $currentYear, $idCalendarDefinition) {
	$y=$currentYear;
	for ($m=1; $m<=12; $m++) {
		$mx=($m<10)?'0'.$m:''.$m;
		$time=mktime(0, 0, 0, $m, 1, $y);
		for ($d=1;$d<=date('t',strtotime($y.'-'.$mx.'-01'));$d++) {
			$dx=($d<10)?'0'.$d:''.$d;
			$day=$y.'-'.$mx.'-'.$dx;
			$iDay=strtotime($day);
			if (date('l',$iDay) == $weekDay) {
			  if($work == 'open' and isOpenDay($day,$idCalendarDefinition) != 1){
			    switchDay ($day,$idCalendarDefinition);
			  }elseif($work == 'off' and isOpenDay($day,$idCalendarDefinition)){
			    switchDay ($day,$idCalendarDefinition);
			  }
			}
		}
	}
}
?>