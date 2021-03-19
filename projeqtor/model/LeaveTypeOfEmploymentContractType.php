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
 * Used in EmploymentContractTypeMain
 */ 
require_once('_securityCheck.php'); 
class LeaveTypeOfEmploymentContractType extends SqlElement {
    
    // List of fields that will be exposed in general user interface
    public $id;    // redefine $id to specify its visible place 
    public $idEmploymentContractType;
    public $idLeaveType;
    public $startMonthPeriod;
    public $startDayPeriod;
    public $periodDuration;
    public $quantity;
    public $earnedPeriod;
    public $isIntegerQuotity;
    public $isUnpayedAllowed;
    public $isJustifiable;
    public $isAnticipated;
    public $validityDuration;
    public $nbDaysAfterNowLeaveDemandIsAllowed;
    public $nbDaysBeforeNowLeaveDemandIsAllowed;
    public $idle;

    
  // Define the layout that will be used for lists
  private static $_layout='';

  private static $_fieldsAttributes=array();  
  
  private static $_colCaptionTransposition = array();
  
  private static $_databaseColumnName = array();
  
//    private static $_databaseTableName = '';
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {      
    parent::__construct($id,$withoutDependentObjects);
    //a quantity cannot be equal to 0 for this object
    /*if($this->quantity==0){
        $this->quantity=null;
    }*/
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
        $old = $this->getOld();
        $result = parent::save();
        $lastStatus = getLastOperationStatus($result);
        if ($lastStatus!="OK" and $lastStatus!="NO_CHANGE") {return $result;}

        // On change of : earnedPeriod, isIntegerQuotity, periodDuration, quantity, startDayPeriod, startMonthPeriod
        // => Must calculate new leave earned for concerned EmployeeLeaveEarned
        if ($this->earnedPeriod != $old->earnedPeriod or 
            $this->isIntegerQuotity != $old->isIntegerQuotity or
            $this->periodDuration != $old->periodDuration or
            $this->quantity != $old->quantity or
            $this->startDayPeriod != $old->startDayPeriod or
            $this->startMonthPeriod != $old->startMonthPeriod
           ) {
              $resultE = setLeaveEarnedForContractType($this);
              if (getLastOperationStatus($resultE)!="OK" and getLastOperationStatus($resultE)!="NO_CHANGE") {
                  $resultE = htmlSetResultMessage( null, 
                                                  getResultMessage($resultE)." "."InUpdateOfLeaveEarnedForCalculation", 
                                                  false,
                                                  "", 
                                                  "CalculationOfNewLeaveEarned",
                                                  getLastOperationStatus($resultE)
                                                 );
                  return $resultE;                      
              }          
        }
        return $result;
    }

  /**=========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * @see persistence/SqlElement#delete()
   * @return the return message of persistence/SqlElement#delete() method
   */
    public function delete() {
        $old = $this->getOld();
        $result = parent::delete();
        $lastStatus = getLastOperationStatus($result);
        if ($lastStatus!="OK" and $lastStatus!="NO_CHANGE") { return $result; }
        $resultE = setLeaveEarnedForContractType($old);
        if (getLastOperationStatus($resultE)!="OK" and getLastOperationStatus($resultE)!="NO_CHANGE") {
            $resultE = htmlSetResultMessage( null, 
                                            getResultMessage($resultE)." "."InUpdateOfLeaveEarnedForCalculation", 
                                            false,
                                            "", 
                                            "CalculationOfNewLeaveEarned",
                                            getLastOperationStatus($resultE)
                                           );
            return $resultE;                      
        }
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
    $old = $this->getOld();
    
    //constraint of unicity: there can only exist one LeaveTypeOfEmploymentContractType with the same idLeaveType AND idEmploymentContractType
    $crit=['idLeaveType' => $this->idLeaveType,'idEmploymentContractType'=>$this->idEmploymentContractType];
    $testUni=SqlElement::getSingleSqlElementFromCriteria('LeaveTypeOfEmploymentContractType', $crit, true);
    if($testUni){
        if($testUni->id != $this->id){
            $result.='<br/>' . i18n('ErrorAlreadyExistALvTypeOfContractTypeWithSameIdLvTypeAndIdContractType');
        }
    }
    
    //constraint: $earnedPeriod <= $periodDuration
    if($this->earnedPeriod!=null && $this->periodDuration!=null && !($this->earnedPeriod<=$this->periodDuration) ){
        $result.='<br/>' . i18n('ErrorEarnedDurationSuperiorToPeriodDuration');
    }
    
    //constraint: startMonthPeriod/earnedPeriod/quantity/periodDuration/validityDuration must be defined/undefined at the same time(if the set of startDayPeriod is removed, it should also be added to this condition)
//    if(! ( ($this->startMonthPeriod==null && $this->quantity===null && $this->earnedPeriod==null && $this->periodDuration==null && $this->validityDuration==null) ||
    if(! ( ($this->startMonthPeriod==null && $this->earnedPeriod==null && $this->periodDuration==null && $this->validityDuration==null) ||
           ($this->startMonthPeriod!=null && $this->quantity!=null && $this->earnedPeriod!=null && $this->periodDuration!=null && $this->validityDuration!=null) ) ){
        $result.='<br/>' . i18n('ErrorInTheAttributesNeededForCalculation');
    }
    
    //quantity must be a modulo of 0.5
    if($this->quantity!==null && trim($this->quantity)!==""){
        if(!(fmod($this->quantity, 0.5) == 0)){
            $result.='<br/>' . i18n('errorQuantityNotModuloOfZeroPointFive');
        }
    }
    
    // For change isAnticipated = true to false
    // => If activ EmployeeLeaveEarned concerned have leftQuantity < 0
    // => Message
    if (!$this->isAnticipated and $old->isAnticipated) {
        // Retrieve contract type
        $contractType = new EmploymentContractType($this->idEmploymentContractType);
        // Retrieve impacted contracts
        $crit = array("idle" => "0",
                      "idEmploymentContractType"=> $contractType->id
                     );
        $contract = new EmploymentContract();
        $contractsList = $contract->getSqlElementsFromCriteria($crit);
        foreach ($contractsList as $cnt) {
          // Retrieve impacted EmployeeLeaveEarned
          $theDate = new DateTime();
          $currentDate = $theDate->format("Y-m-d");
          $clauseWhere = "leftQuantity < 0 AND idle=0 AND idEmployee = $cnt->idEmployee AND idLeaveType = $this->idLeaveType AND startDate <= '$currentDate' AND endDate > '$currentDate'";
            $lvE = new EmployeeLeaveEarned();
            $lvEList = $lvE->getSqlElementsFromCriteria(null,false, $clauseWhere);
            foreach ($lvEList as $lvEi) {
                $empl = new Employee($lvEi->idEmployee);
                $result .= '<br/>'.i18n('Employee ').$empl->name.i18n('hasTakenAnticipatedLeavesForThisActualPeriod');
            }
        }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
        $result.=$defaultControl;
    }
    
    if ($result == "") $result='OK';
    
    return $result;
  }
  
// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================

  /**
   * ========================================================================
   * Retrieve a list of objects from the Database
   * Called from an empty object of the expected class
   * This function is redefined here to reset quantity to NULL when it's equals to 0
   * 
   * @param array $critArray
   *          the critera as an array
   * @param boolean $initializeIfEmpty
   *          indicating if no result returns an
   *          initialised element or not
   * @param string $clauseWhere
   *          Sql Where clause (alternative way to define criteria)
   *          => $critArray must not be set
   * @param string $clauseOrderBy
   *          Sql Order By clause
   * @param boolean $getIdInKey          
   * @return SqlElement[] an array of objects
   */
  /*public function getSqlElementsFromCriteria($critArray, $initializeIfEmpty = false, $clauseWhere = null, $clauseOrderBy = null, $getIdInKey = false, $withoutDependentObjects = false, $maxElements = null) {
      $result = parent::getSqlElementsFromCriteria($critArray, $initializeIfEmpty, $clauseWhere, $clauseOrderBy, $getIdInKey, $withoutDependentObjects, $maxElements);
      foreach($result as $lvTypeOf){
          if($lvTypeOf->quantity==0){
              $lvTypeOf->quantity=null;
          }
      }
      return $result;
  }*/
}
?>
