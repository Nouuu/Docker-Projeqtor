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

// LEAVE SYSTEM

// RULES
// x. resources list for idEmployee is limited to activ Employees 
//          Done in view/objectDetail.php  Comment = // Restrict list to Employee (isEmployee=1) if object's class = EmploymentContract
// x. At the contract creation :
//      - resource associated to the contract is required and not readonly
//          Done in construct
//      - Readonly for idle, endDate, reason, idStatus
//          Done in construct
// x. endDate, reason are required when associated resource is closed
//          Done in construct
// x. Can only update the following fields of a contract if associated resource is closed :
//          - endDate (if null)
//          - reason (if null)
//          Done in construct
// x. Can't close a contract IF
//      - end date is'nt valid or reason is null
//          Done in control
// x. endDate validity :
//      - endDate >= startDate and endDate !=null
//          Done in isEndDateValid called in control
// x. Only one unclosed contract for a resource
//          Done in control
// x. If endDate is valid and not null  => then contract must be closed
//      => idle=1
//      => reason required
//          Done in getValidationScript
//          Done is construct
// x. If endDate is null or not valid => contract can't be closed
//      => idle = 0 and readonly
//      => reason not required && null
//          Done in getValidationScript
//          Done in control
// x. On save :
//      If idle = 0 => endDate = null and reason = null
//          Done in save

/* ============================================================================
 * 
 */  
require_once('_securityCheck.php');

class EmploymentContractMain extends SqlElement {
    public $_sec_Description;
    public $id;
    public $name;
    public $mission;
    public $idEmployee;
    public $idUser;
    public $idTeam;
    public $idOrganization;
    public $idEmploymentContractType;
    public $startDate;    
    public $_sec_treatment;
    public $idStatus;
    public $idle;
    public $endDate; // End Date must be > to startDate
    public $idEmploymentContractEndReason; // Is required if $endDate not null
    public $_Attachment=array();
    public $_Note=array();
    
    private $___dFieldsAttributes=array();
    
    // Define the layout that will be used for lists
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="35%">${name}</th>
    <th field="nameEmployee" formatter="thumbName22" width="30%" >${employee}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
    
    private static $_fieldsAttributes=array(
        "name"=>"required",
        "idStatus"=>"required",
        "idEmployee"=>"required,readonly",
        "idTeam"=>"readonly",
        "idOrganization"=>"readonly",
        "idEmploymentContractType"=>"required",
        "idStatus"=>"required",
        "startDate"=>"required",
        );

  private static $_fieldsTooltip = array(
                                            "startDate" => "tooltipLeaveContractStartDate"
                                        );

    
  private static $_colCaptionTransposition = array('idEmployee'=>'employee',
                                                   'idUser' => 'issuer'
                                                  );
    
    //private static $_databaseTableName = ''; 
    /** ==========================================================================
    * Constructor
    * @param $id the id of the object in the database (null if not stored yet)
    * @return void
    */ 
    function __construct($id = NULL, $withoutDependentObjects=false) {
        parent::__construct($id,$withoutDependentObjects);
        // At the creation
        if ($this->id==null) {
           // resource associated to the contract is required and not readonly
           $this->___dFieldsAttributes['idEmployee'] = "required";         
           // Readonly for idle, endDate, reason, idStatus, idTeam, idOrganization
           $this->___dFieldsAttributes['idTeam'] = "readonly";
           $this->___dFieldsAttributes['idOrganization'] = "readonly";
           $this->___dFieldsAttributes['idle'] = "readonly";         
           $this->___dFieldsAttributes['endDate'] = "readonly";         
           $this->___dFieldsAttributes['idEmploymentContractEndReason'] = "readonly";
           $this->___dFieldsAttributes['idStatus'] = "readonly";         
        } else {
           $this->___dFieldsAttributes= array(); 
        }

        $res = new Resource($this->idEmployee);
        // Resource associated to the EmploymentContract is closed OR This is closed
        if ($res->idle or $this->idle>0) {
            // endDate, reason are required when associated resource is closed or this is closed
            $this->___dFieldsAttributes['endDate'] = "required";
            $this->___dFieldsAttributes['idEmploymentContractEndReason'] = "required";
            // Can only update the following fields of a contract if associated resource is closed :
            //          - endDate (if null)
            //          - reason (if null)
            $this->___dFieldsAttributes['name'] = "readonly,required";
            $this->___dFieldsAttributes['mission'] = "readonly";
            $this->___dFieldsAttributes['idEmployee'] = "readonly,required";
            $this->___dFieldsAttributes['idTeam'] = "readonly,required";
            $this->___dFieldsAttributes['idOrganization'] = "readonly,required";
            $this->___dFieldsAttributes['idEmploymentContractType'] = "readonly,required";
            $this->___dFieldsAttributes['startDate'] = "readonly,required";
            $this->___dFieldsAttributes['name'] = "readonly";  
            //$this->setAttributes();          
        }
        
        // If endDate is valid and not null
        if ($this->isEndDateValid()=='' and $this->endDate!=null and $this->id>0) {
            // => reason required
            $this->___dFieldsAttributes['idEmploymentContractEndReason'] = "required";            
        } 
        // If endDate is null => 
        //      - reason = "readonly" and null
        //      - idle=0 and readonly
        if ($this->endDate==null) {
            $this->idle=0;
            $this->___dFieldsAttributes['idle'] = "readonly";
            $this->___dFieldsAttributes['idEmploymentContractEndReason'] = "readonly";
            $this->idEmploymentContractEndReason=null;
            
        }
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

  /** ========================================================================
   * Return the specific fieldsTooltip
   * @return array the fieldsTooltip
   */
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }

  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
    
  /**
   * Get the dynamic attributes (or static if dynamic not found) of the field that name is passed in parameter
   * @param String $fieldName : The fieldName for witch get attributes
   * @return String Attributes of the field
   */
  public function getFieldAttributes($fieldName) {
    if (array_key_exists ( $fieldName, $this->___dFieldsAttributes )) {
      $theFieldAttributes = $this->___dFieldsAttributes[$fieldName];
    } else {
        $theFieldAttributes = parent::getFieldAttributes($fieldName);
    }
    return $theFieldAttributes;
  }
  
    /** =========================================================================
     * control data corresponding to Model constraints
     * @param void
     * @return "OK" if controls are good or an error message 
     *  must be redefined in the inherited class
    */
    public function control(){        
        $old = $this->getOld();
        $result=parent::control();
        if ($result=='OK') {$result="";}
        
        // Only one unclosed contract for a resource
        if ( ($this->id >0 and $this->idle != $old->idle and $this->idle == 0) or 
             (is_null($this->id) and $this->idle == 0)
           ) {
            $emplCList = $this->getEmploymentContractsForAnEmployee($this->idEmployee,false,false);
            if (count($emplCList)) {
                $result .= "</br>".i18n("CantHaveTwoContractsUnclosedForAnEmployee");
            }
        }
                
        // Test if EndDate is valid
        $resEndDate = $this->isEndDateValid();
        if ($resEndDate!="") {
            $result .= "</br>".$resEndDate;
        }
        
        // End Reason is mandatory where end Date is not null
        if ($this->endDate!=null and $this->idEmploymentContractEndReason==null) {
                $result .= "</br>".i18n("EndReasonIsMandatoryWhenEndDateIsNotNull");            
        }
        
        // Can't close a contract IF
        //      - end date is'nt valid or end date is null or reason is null
        if ($this->idle and ($resEndDate!="" or $this->endDate==null or !$this->idEmploymentContractEndReason)) {
            $result .= "</br>".i18n("CantCloseEndDateInvalidOrEndReasonEmpty");
        }

        if ($result=="") $result='OK';
	
        return $result;
    }

  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $old = $this->getOld();
    // Must close a contract if endDate is inferior to current Date
    if ($this->isEndDateValid()=='' and $this->endDate!=null and $this->id>0 and $this->idle==0) {
        $currentDate = (new DateTime())->format("Y-m-d");
        if ($this->endDate<$currentDate) {
            $resultU = htmlSetResultMessage( null, 
                                            "MustCloseContractWhereEndDateIsInferiorToCurrentDate", 
                                            false,
                                            "", 
                                            "save",
                                            "INVALID"
                                          );
            return $resultU;
        }
    }
     
    $res = new Resource($this->idEmployee);
    $this->idTeam = $res->idTeam;
    $this->idOrganization = $res->idOrganization;
    
    $result = parent::save();
    
    if(strpos($result,"OK")===false){
        return $result;
    }
   
    // At least, one unclosed contract for a resource, if end reason is not final
    if ($this->idle != $old->idle and $this->idle == 1 and !EmploymentContractEndReasonMain::isFinal($this->idEmploymentContractEndReason)) {
        $emplCList = $this->getEmploymentContractsForAnEmployee($this->idEmployee,false,false);
       if (!count($emplCList)) {
            // Create a new unclosed contract with startDate = this endDate + 1 day;
            $unclosedCtr = new EmploymentContract();
            $unclosedCtr->idEmployee = $this->idEmployee;
            $unclosedCtr->idEmploymentContractType = $this->idEmploymentContractType;
            $unclosedCtr->idTeam = $this->idTeam;
            $unclosedCtr->idOrganization = $this->idOrganization;
            $theStartDate = new DateTime($this->endDate);
            $theStartDate = $theStartDate->add(new DateInterval('P1D'));
            $unclosedCtr->startDate = $theStartDate->format('Y-m-d');
            $unclosedCtr->endDate=null;
            $unclosedCtr->idle = 0;
            $empl = new Employee($this->idEmployee);
            $unclosedCtr->name = $empl->name ." - ".$unclosedCtr->startDate." - ".i18n("NEW"). " ". i18n("unclosed"). " ". i18n("employmentContract");
            $resultU = $unclosedCtr->save();
            if (getLastOperationStatus($resultU)!="OK") {                
                $resultU = htmlSetResultMessage( null, 
                                                getResultMessage($resultU)."<br/>InCreationOfNewUnclosedContract", 
                                                false,
                                                "", 
                                                "AtLeastOneContractsUnclosedForAnEmployee",
                                                getLastOperationStatus($resultU)
                                              );
                return $resultU;
            }
        }
    }
    
    // Start date or contract type changes => calculates quantity and left of concerned employee leave earned
    if ($old->startDate != $this->startDate or $this->idEmploymentContractType != $old->idEmploymentContractType) {
        $theDate = new DateTime();
        $currentDate = $theDate->format("Y-m-d");
        $clauseWhere  = "idle=0 AND idEmployee = $this->idEmployee AND ";
        $clauseWhere .= "((startDate <= '$currentDate' AND endDate > '$currentDate') OR (startDate IS NULL))";
        $leaveEarned = new EmployeeLeaveEarned();
        $lvEList = $leaveEarned->getSqlElementsFromCriteria(null,false,$clauseWhere);
        foreach( $lvEList as $lvE) {
            $lvE->setLeavesRight(true);
            if ($lvE->startDate!=null and $lvE->endDate!=null and $lvE->leftQuantity!==null) {
                $nbDays=(float)0.0;
                $leaves = $lvE->getThisLeaves(array(1,12));
                foreach($leaves as $leave) {
                    $nbDays += $leave->nbDays;
                }
                $lvE->leftQuantity -= (float)$nbDays;
            }
            $resultU=$lvE->simpleSave();
            if (getLastOperationStatus($resultU)!="OK" and getLastOperationStatus($resultU)!="NO_CHANGE") {
                $resultU = htmlSetResultMessage( null, 
                                                getResultMessage($resultU)."<br/>InUpdateOfLeaveEarnedForCalculation", 
                                                false,
                                                "", 
                                                "CalculationOfNewLeaveEarned",
                                                getLastOperationStatus($resultU)
                                              );
                return $resultU;
            }
        }
    }
    
    // At the contract closure and if the employee has no more unclosed contract
    $empl = new Employee($this->idEmployee);
    if ($this->idle==1 and $empl->hasAllClosedContracts()) {
        // => delete all leaves with status = 1 of the employee
        $crit = "idStatus=1 AND idEmployee = " . $this->idEmployee;
        $leaves = new Leave();
        $resultU = $leaves->purge($crit);
        if (getLastOperationStatus($resultU)!="OK" and getLastOperationStatus($resultU)!="NO_CHANGE") {
            $resultU = htmlSetResultMessage( null, 
                                            getResultMessage($resultU)."<br/>InDeleteRecordedLeavesWhenClosingContract", 
                                            false,
                                            "", 
                                            "saveContract",
                                            getLastOperationStatus($resultU)
                                          );
            return $resultU;
        }
        // Close the leave earned of the employee
        $critLv = array("idle" => '0',
                        "idEmployee" => $this->idEmployee);
        $lvE = new EmployeeLeaveEarned();
        $lvEList = $lvE->getSqlElementsFromCriteria($critLv);
        foreach($lvEList as $lvE) {
            $lvE->idle = 1;
            $lvE->leftQuantity=0;
            $resultU = $lvE->simpleSave();
            if (getLastOperationStatus($resultU)!="OK" and getLastOperationStatus($resultU)!="NO_CHANGE") {
                $resultU = htmlSetResultMessage( null, 
                                                getResultMessage($resultU)."<br/>InClosingLeaveEarnedWhenClosingContract", 
                                                false,
                                                "", 
                                                "saveContract",
                                                getLastOperationStatus($resultU)
                                              );
                return $resultU;
            }
        }
    }
    return $result;
  }
  
  public function deleteControl() {
    $result = parent::deleteControl();
    if ($result=="OK") { $result = "";}

    // At least, one contract for a resource
    if ($this->idle == 0) {
        $emplCList = $this->getEmploymentContractsForAnEmployee($this->idEmployee,true,false);
        if (!count($emplCList)) {
            $result .= "</br>".i18n("AtLeastOneContractForAnEmployee");
        }
    }

    if ($result=="") $result='OK';

    return $result;
  }
  
  public function delete() {
    $result = parent::delete();
    
    return $result;
  }

  /** ==========================================================================
   * Return the validation script for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    
    $colScript = parent::getValidationScript($colName);

    if($colName=="endDate"){
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        // If endDate is valid and not null
        //      => reason required 
        $colScript .= '  if(this.value!=null && this.value >= dijit.byId("startDate").getValue()) {';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("required", true);';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("readOnly", null);';
        $colScript .= '    dojo.addClass(dijit.byId("idEmploymentContractEndReason").domNode,"required");';
        // If endDate < currentDate => idle=1
        $colScript .= '    var maDate = (new Date()).toISOString().substr(0,10);';
        $colScript .= '    if (maDate>this.value.toISOString().substr(0,10)) {';
        $colScript .= '         dijit.byId("idle").setValue(1);';
        $colScript .= '    } else {';
        $colScript .= '     if ('.$this->idle.'==0) { dijit.byId("idle").setValue(null);}';
        $colScript .= '    }';
        $colScript .= '  }';
        $colScript .= '  if (this.value==null || this.value < dijit.byId("startDate").getValue()) {';
        // If endDate is null or not valid => contract can't be closed
        //      => idle = 0 and readonly
        //      => reason readonly && null
        $colScript .= '    dijit.byId("idle").setValue(null);';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").setValue(null);';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("required", null);';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("readOnly", true);';
        $colScript .= '    dojo.removeClass(dijit.byId("idEmploymentContractEndReason").domNode,"required");';
        $colScript .= '  }';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';        
    }

    if($colName=="idle"){
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= '  var oldIdle = '.$this->idle.';';        
        $colScript .= '  if(this.checked==false && oldIdle == 1) {';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("required", true);';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("readOnly", null);';
        $colScript .= '    dojo.addClass(dijit.byId("idEmploymentContractEndReason").domNode,"required");';
        $colScript .= '    dijit.byId("endDate").set("required", false);';
        $colScript .= '    dijit.byId("endDate").set("readOnly", null);';
        $colScript .= '    dojo.removeClass(dijit.byId("endDate").domNode,"required");';
        $colScript .= '    var maDate = (new Date()).toISOString().substr(0,10);';
        $colScript .= '    if (maDate>dijit.byId("endDate").value.toISOString().substr(0,10)) {';
        $colScript .= '         dijit.byId("endDate").setValue(maDate);';
        $colScript .= '    }';
        $colScript .= '  }';
        $colScript .= '  if(this.checked==true) {';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("required", true);';
        $colScript .= '    dijit.byId("idEmploymentContractEndReason").set("readOnly", null);';
        $colScript .= '    dojo.addClass(dijit.byId("idEmploymentContractEndReason").domNode,"required");';
        $colScript .= '    dijit.byId("endDate").set("required", true);';
        $colScript .= '    dijit.byId("endDate").set("readOnly", null);';
        $colScript .= '    dojo.addClass(dijit.byId("endDate").domNode,"required");';
        $colScript .= '  }';        
        $colScript .= '  formChanged();';
        $colScript .= '</script>';        
    }
    return $colScript;    
    
  }
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    $result="";
    
     return $result;
  }

    /**
     * Return the EmploymentContracts list of the resource (id passed in parameters)
     * @param Integer $idEmployee : The employee for which get the EmploymentContract
     * @param Boolean $withClosed : If false, only the EmploymentContracts with idle=0
     * @param Boolean $selfInclude : If false, this employmentContract is'nt in the list
     * @return EmploymentContract[] : List of EmploymentContract for the employee
     */
    public function getEmploymentContractsForAnEmployee($idEmployee, $withClosed=true, $selfInclude=true) {
        $clauseWhere = "idEmployee = ".$idEmployee;
        if (!$withClosed) {
            $clauseWhere .= " AND idle=0";
        }
        if (!$selfInclude) {
            $clauseWhere .= " AND id".($this->id?"<>".$this->id:" IS NOT NULL");
        }
        $listEmplC = $this->getSqlElementsFromCriteria(null, false, $clauseWhere);
        return $listEmplC;
    }

    public static function getActualEmploymentContractForAnEmployee($idEmployee=null) {
        if ($idEmployee==null) {
            return null;
        }
        $crit = array("idle" => "0",
                      "idEmployee" => $idEmployee
                     );
        $contract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract", $crit);
        if (!isset($contract->id)) {
            return null;
        }
        return $contract;
    }
    
    /**
     * Control if EndDate is valid
     * @return string : '' if endDate is valid - A message to show if not valid
     */
    public function isEndDateValid() {
        if ($this->endDate==null) {
            return '';
        }
        // EndDate must be >= StartDate
        if ($this->endDate < $this->startDate) {
            return i18n('EndDateMustBeSuperiorOrEqualToStartDate');
        } else {
            return '';
        }
    }
    
    public function getEmploymentContractType() {
        return new EmploymentContractType($this->idEmploymentContractType);
    }
}
?>
