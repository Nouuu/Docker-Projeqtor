<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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
 * Used in EmployeeManager - Represents the link between Employee and EmployeeManager.
 */ 
require_once('_securityCheck.php'); 
class EmployeesManaged extends SqlElement {
    
    // List of fields that will be exposed in general user interface
    public $id;    // redefine $id to specify its visible place 
    public $idle;
    public $idEmployee;
    public $idEmployeeManager;
    public $startDate;
    public $endDate;
    
    // Define the layout that will be used for lists
  private static $_layout='';

  private static $_fieldsAttributes=array();  
  
  private static $_colCaptionTransposition = array();
  
  private static $_databaseColumnName = array();
    
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


// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    return $colScript;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    
    $result = parent::save();
    return $result;
    
  }
    
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    
    if ($this->idle==0) {
        $activManagersList = $this->getManagersForThisEmployee();
        // Can't have more than one manager in the same time
        if ($this->startDate==null and $this->endDate==null and count($activManagersList)>0) {
            $result = '<br/>'.i18n("CantHaveMoreThatOneManagerAtTheSameTime");
        } else {
            foreach($activManagersList as $manager) {
                if ($manager->id != $this->id) {
                    if ($manager->startDate==null and $manager->endDate==null) {
                        $result = '<br/>'.i18n("CantHaveMoreThatOneManagerAtTheSameTime");
                        break;
                    }
                    if ($this->endDate < $manager->startDate or $this->startDate > $manager->endDate) {
                        continue;
                    } else {
                        $result = '<br/>'.i18n("CantHaveMoreThatOneManagerAtTheSameTime");
                        break;
                    }
                }
            }
        }
    }
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
        $result.=$defaultControl;
    }
    
    if ($result == "") {$result='OK';}
    
    return $result;
  }
  
// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================
    public function getManagersForThisEmployee($withClosed=false) {
        $crit = array("idEmployee" => $this->idEmployee);
        if (!$withClosed) {
            $crit["idle"] = "0";
        }
        return $this->getSqlElementsFromCriteria($crit);        
    }
    
    public function getEmployeesForThisManager($withClosed=false) {
        $crit = array("idEmployeeManager" => $this->idEmployeeManager);
        if (!$withClosed) {
            $crit["idle"] = "0";
        }
        return $this->getSqlElementsFromCriteria($crit);        
    }  

}
?>