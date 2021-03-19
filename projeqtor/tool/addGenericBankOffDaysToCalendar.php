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

// MTY - GENERIC DAY OFF

/* ============================================================================
 * Add off days to calendar of year passed in parameter with CalendarBankOffDays of Calendar Definition passed in parameter
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/addGenericBankOffDaysToCalendar.php');

$easterDays = array(
                    0 => i18n("easter"),
                    1 => i18n("ascension"),
                    2 => i18n("pentecost"),
                    3 => i18n("holyfriday"),
                    );
  
  
$user=getSessionUser();
$collapsedList=Collapsed::getCollaspedList();
$currentYear=strftime("%Y") ;
$idCalendarDefinition=false;
$clear = 0;
if (isset($_REQUEST['year'])) {
  $currentYear=$_REQUEST['year'];
  $currentYear=Security::checkValidYear($currentYear);
}
if (isset($_REQUEST['idCalendarDefinition'])) {
      $idCalendarDefinition=$_REQUEST['idCalendarDefinition'];
      Security::checkValidId($idCalendarDefinition);
}
if (isset($_REQUEST['clear'])) {
      $clear = Security::checkValidBoolean($_REQUEST['clear']);
}

if ($clear) {
    // Clear old for idCalendarDefintion and year
    $calClear = new Calendar();
    $clauseCal = "idCalendarDefinition=".$idCalendarDefinition." AND year='".$currentYear."'";
    $result=$calClear->purge($clauseCal);    
} else {

  $calBank = new CalendarBankOffDays();
  $crit = array("idCalendarDefinition" => $idCalendarDefinition);
  $calBankList = $calBank->getSqlElementsFromCriteria($crit);
  foreach ($calBankList as $obj) {
      if ($obj->easterDay!=null) {
          $iEaster = getEaster($currentYear);
          if ($obj->easterDay==0) {
              $calendarDate = date('Y-m-d', $iEaster + 86400);            
              $name = $easterDays[0];
          } else if ($obj->easterDay==1) {
  	        $calendarDate = date ('Y-m-d', $iEaster + (86400*39));
              $name = $easterDays[1];
          } else if ($obj->easterDay==2) {
  	        $calendarDate = date ('Y-m-d', $iEaster + (86400*50));
              $name = $easterDays[2];                        
          } else if ($obj->easterDay==3) {
  	        $calendarDate = date ('Y-m-d', $iEaster - (86400*2));
              $name = $easterDays[3];                        
          }
      } else {
          $calendarDate = $currentYear . "-" . ($obj->month>9?$obj->month:"0".$obj->month) . "-" . ($obj->day>9?$obj->day:"0".$obj->day);
          $name = $obj->name;
      }
      $cal = new Calendar();
      // Search if calendar day off exists
      $critCal = array(
                          "idCalendarDefinition" => $idCalendarDefinition,
                          "calendarDate" => $calendarDate
                      );
      $calList = $cal->getSqlElementsFromCriteria($critCal);
      if (count($calList)>0) {
          $first = true;
          foreach ($calList as $cObj) {
              if ($first) { // If first => Update
                  $first=false;
                  // Exists and is'nt off day => Become off day
                  if (!$cObj->isOffDay) {
                      $cObj->isOffDay = 1;
                      $cObj->name = $name;
                      $cObj->save();
                  }
              } else { // Not first => Delete (It's duplicate)
                  $cObj->delete();
              }
          }
      } else {
          // Does'nt exists => create it
          $theCal = new Calendar();
          $theCal->idCalendarDefinition = $idCalendarDefinition;
          $theCal->calendarDate = $calendarDate;
          $theCal->name = $name;
          $theCal->isOffDay = 1;
          addBankOffDayDay($theCal);
      }
  }
}
$cal=new Calendar;
$cal->setDates($currentYear.'-01-01');
$cal->idCalendarDefinition=$idCalendarDefinition;
$result= $cal->drawSpecificItem('calendarView');
echo $result;


function addBankOffDayDay($theCal) {
  global $bankHolidays, $bankWorkdays;
  $idCalendarDefinition = $theCal->idCalendarDefinition;
  $day = $theCal->calendarDate;
  $cal=SqlElement::getSingleSqlElementFromCriteria('Calendar',array('calendarDate'=>$day, 'idCalendarDefinition'=>$idCalendarDefinition));
  if (!$cal->id) {
    if (isOpenDay($day,$idCalendarDefinition)) {
      $cal->isOffDay=1;
    } else {
      $cal->isOffDay=0;
    }
    $theCal->save();
  }
  
  $bankHolidays=array();
  $bankWorkdays=array();
  
  $result;
}

?>