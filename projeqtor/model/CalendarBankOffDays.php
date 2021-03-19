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
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project. (ajout eliott: error in the description of this class ?)
 */  
require_once('_securityCheck.php'); 
class CalendarBankOffDays extends SqlElement {

  // List of fields that will be exposed in general user interface
    public $id;    // redefine $id to specify its visible place 
    public $idCalendarDefinition;
    public $name;
    public $month;
    public $day;
    public $easterDay;  
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

    public function control(){
        $result=parent::control();
        if ($result=="OK") { $result=""; }
                
        // If not easter day => month and day required
        if ($this->easterDay==null and ($this->month==null or $this->day==null)) {
            $result = i18n("errorWhenNotEasterDayMonthAndDayAreRequired");
        }
        
        // If month and day not null => easter day = null
        if ($this->easterDay!=null and ($this->month!=null or $this->day!=null)) {
            $result .= '<br/>'.i18n("errorWhenEasterDayMonthAndDayMustBeNull");
        }
        
        if ($this->easterDay==null and $this->month!=null and $this->day!=null) {
            // Day must be <= last day of month year
            $lastDay = lastDayOfMonth($this->month, 2016); // Take a leap year
            if ($this->day > $lastDay) {
                $result .= '<br/>'.i18n("errorDayIsGreaterThanLastDayOfMonth");                                    
            }
            
            // Can't have two BankOffDay with the same Month and day
            $crit = array(
                "idCalendarDefinition" => $this->idCalendarDefinition,
                "easterDay" => null,
                "day" => $this->day,
                "month" => $this->month
                         );
            $calBank = SqlElement::getFirstSqlElementFromCriteria("CalendarBankOffDays", $crit);
            if (isset($calBank->id)) {
                if ($calBank->id != $this->id) {
                    $result .= '<br/>'.i18n("errorCantHaveTwoBankOffDayWithSameMonthAndDay");                    
                }
            }
        }
        
        // Can't have two easter day with the same value
        if ($this->easterDay!=null and $this->month==null and $this->day==null) {
            $crit = array(
                "idCalendarDefinition" => $this->idCalendarDefinition,
                "easterDay" => $this->easterDay,
                "day" => null,
                "month" => null
                         );
            $calBank = SqlElement::getFirstSqlElementFromCriteria("CalendarBankOffDays", $crit);
            if (isset($calBank->id)) {
                if ($calBank->id != $this->id) {
                    $result .= '<br/>'.i18n("errorCantHaveTwoBankOffDayWithSameEasterdayValue");
                }
            }
        }
                
        return ($result==""?"OK":$result);
    }
  
// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
    
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
    
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
       
}
?>
