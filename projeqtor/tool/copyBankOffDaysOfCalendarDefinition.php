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
 * Copy Calendar Bank off days from calendar definition to another calendar definition
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/copyBankOffDaysCalendarDefinition.php');

  $idCalendarDefinition=0;
  if (isset($_REQUEST['idCalendarDefinition'])) {
  	$idCalDefTo=$_REQUEST['idCalendarDefinition'];
  	Security::checkValidId($idCalDefTo);
  }
  if (isset($_REQUEST['copyFrom'])) {
  	$idCalDefFrom=$_REQUEST['copyFrom'];
        $idCalDefFrom=Security::checkValidId($idCalDefFrom);
  }

  $bankOffDaysFrom = new CalendarBankOffDays();
  $crit = array("idCalendarDefinition" => $idCalDefFrom);
  $bankOffDayFromList = $bankOffDaysFrom->getSqlElementsFromCriteria($crit);
  $crit = array("idCalendarDefinition" => $idCalDefTo);
  $bankOffDayToList = $bankOffDaysFrom->getSqlElementsFromCriteria($crit);
  foreach ($bankOffDayFromList as $bFrom) {
      $found = false;
      foreach($bankOffDayToList as $bTo) {
          if ($bFrom->month == $bTo->month and $bFrom->day == $bTo->day and $bFrom->easterDay == $bTo->easterDay) {
              $found = true;
              break;
          }
      }
      if (!$found) {
          $newBankOffDay = new CalendarBankOffDays();
          $newBankOffDay->idCalendarDefinition = $idCalDefTo;
          $newBankOffDay->month = $bFrom->month;
          $newBankOffDay->day = $bFrom->day;
          $newBankOffDay->easterDay = $bFrom->easterDay;
          $newBankOffDay->name = $bFrom->name;
          $newBankOffDay->simpleSave();
      }
  }
  
  $calDef = new CalendarDefinition($idCalDefTo);
  $result= $calDef->drawSpecificItem('bankOffDays');
  echo $result;
