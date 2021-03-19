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
 * Used in LeaveTypeMain, this object is used to update the table employeeleaveearned
 * and contains the periods of leaves for the employee
 */ 
require_once('_securityCheck.php'); 
class EmployeeLeaveEarnedMain extends SqlElement {

  // List of fields that will be exposed in general user interface
    public $_sec_description;
    public $id;    // redefine $id to specify its visible place 
    public $idUser; // the creator
    public $idEmployee;//the employee
    public $idLeaveType;
    public $_sec_treatment;
    public $_spe_buttonInitWithContractualValues;
    public $startDate;
    public $endDate;
    public $quantity;
    public $leftQuantity;
    public $lastUpdateDate;
    public $leftQuantityBeforeClose;
    public $idle;
    public $_Attachment=array();
    public $_Note=array();
    
    public $_nbColMax=3;
    
  // Define the layout that will be used for lists
  private static $_layout='
          <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
          <th field="nameEmployee" formatter="thumbName22" width="15%" >${Employee}</th>
          <th field="nameLeaveType" width="5%" >${type}</th>
          <th field="startDate" width="8%" formatter="dateFormatter">${startDate}</th>
          <th field="endDate" width="8%" formatter="dateFormatter">${endDate}</th>
          <th field="quantity" formatter="numericFormatter" width="4%" ># ${quantity}</th>
          <th field="leftQuantity" formatter="numericFormatter" width="4%" ># ${leftQuantity}</th>
          <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
          ';

  private static $_spinnersAttributes = array(
      );  

  
  private static $_fieldsAttributes=array(
      "idEmployee"=>"required",
      "idLeaveType"=>"required",
      "leftQuantity"=>"readonly",
      "leftQuantityBeforeClose"=>"hidden",
      "lastUpdateDate"=>"readonly"
  );
  
  private static $_colCaptionTransposition = array('idUser' => 'issuer');
  
  private static $_databaseColumnName = array();
  
//    private static $_databaseTableName = '';
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->id!=null){
        self::$_fieldsAttributes['idLeaveType']='readonly';
        self::$_fieldsAttributes['idEmployee']='readonly';
        if($this->quantity!=null){
            self::$_fieldsAttributes['quantity']='readonly';
        }
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

  /** ==========================================================================
   * Return the generic spinnerAttributes
   * @return array[name,value] : the generic $_spinnerAttributes
   */
  protected function getStaticSpinnersAttributes() {
      if(!isset(self::$_spinnersAttributes)) {return array();}
      return self::$_spinnersAttributes;
  }
  
// ============================================================================
// DRAW FUNCTION
// ============================================================================

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item, $readOnly=false, $refresh=false, $canUpdate=true){
    $result="";
    switch ($item) {
        case "buttonInitWithContractualValues":
            $result = $this->drawButtonInitWithContractualValues($item,$canUpdate);
            break;

        default:           
            break;
    }
     return $result;
  }
  
  private function drawButtonInitWithContractualValues($item, $canUpdate) {
    global $print;

    $result="";
    
    if ($this->id>0) {
        $values = $this->getLeavesRight(true);
    }
    if (!$canUpdate and $this->idle==0 and $this->id>0 and 
        $values['startDate']==null and $values['endDate']==null and $values['quantity']===null) {
            $result = "<table><tr><td class='label' style='width:200px;' valign='top'><label><i>".i18n ( 'noActualContractualLimitationForThisLeaveType' )."</i></label>";
            $result .= "</td>";
            $result .= "</tr></table>";
            return $result;
    }
    
    if ($print or !$canUpdate or $this->idle==1) {return "";}
    
    if ($this->id>0) {
        if ($values['startDate']==null and $values['endDate']==null and $values['quantity']===null) {
            $result .= "<table><tr><td class='label' style='width:200px;' valign='top'><label><i>".i18n ( 'noActualContractualLimitationForThisLeaveType' )."</i></label>";
            $result .= "</td>";
            $result .= "</tr></table>";            
        } else {
            $result .= '<div style="display:none;"><input data-dojo-type="dijit/form/CheckBox" id="'.$item.'" name="'.$item.'"></div>';;
            $result .= "<table><tr><td class='label' valign='top'><label>&nbsp;</label>";
            $result .= "</td><td>";
            $result .= '<button id="bt_'.$item.'" dojoType="dijit.form.Button" showlabel="true"';
            $result .= ' title="' . i18n ( 'titleInitWithContractualValues' ) . '" style="vertical-align: middle;">';
            $result .= '<span>' . i18n ( 'initWithActuelContractValues' ) . '</span>';
            $result .= '<script type="dojo/connect" event="onClick" args="evt">';
            if ($values['startDate']!=null) {
                $result .= '    if (!dijit.byId("startDate").readOnly) {';
                $result .= '        dijit.byId("startDate").setValue("'.$values['startDate'].'");';
                $result .= '        formChanged();';
                $result .= '    }';
            }
            if ($values['endDate']!=null) {
                $result .= '    if (!dijit.byId("endDate").readOnly) {';
                $result .= '        dijit.byId("endDate").setValue("'.$values['endDate'].'");';
                $result .= '        formChanged();';
                $result .= '    }';
            }
            $result .= '    if (!dijit.byId("quantity").readOnly) {';
            $result .= '        dijit.byId("quantity").setValue('.$values['quantity'].');';
            $result .= '        formChanged();';
            $result .= '    }';
            $result .= '    if (!dijit.byId("leftQuantity").readOnly) {';
            $result .= '        dijit.byId("leftQuantity").setValue('.$values['left'].');';
            $result .= '        formChanged();';
            $result .= '    }';
            $result .= '</script>';
            $result .= '</button>';
            $result .= "</td></tr></table>";
        }
    } else {
        $result .= "<table><tr><td class='label' style='width:200px;' valign='top'><label>".i18n ( 'initWithActuelContractValues' )."&nbsp;:</label>";
        $result .= "</td><td>";
        $result .= '<input title="'.i18n('titleInitWithContractualValues').'" data-dojo-type="dijit/form/CheckBox" id="'.$item.'" name="'.$item.'">';
        $result .= "</td></tr></table>";
        
    }
    return $result;  
  }
  
// ============================================================================
  /**=========================================================================
     * Return the leave earned for an employee
     * @param integer $idle 0 or 1
     * @param integer $idEmployee : Employee for which retrieve Leave Earned
     * @return Array[EmployeeLeaveEarned] : List of EmployeeLeaveEarned objects
     */
    public static function getList($idle=null,$idEmployee=null) {
        $lvEarned = new EmployeeLeaveEarned();
        $crit=[];
        if ($idle!==null) {
            $crit["idle"]="$idle";
        }
        if($idEmployee){
            $crit["idEmployee"]=$idEmployee;
        }
        $lvEarnedList = $lvEarned->getSqlElementsFromCriteria($crit);
        return $lvEarnedList;
    }
              
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {      
    //it's possible to modify the attribute leftQuantity if the leaveEarned is the last one with the same leaveType and resource
      //TO DO: take into account the profil Admin
    $old=$this->getOld();
    if($this->id!=NULL){
        $clauseWhere="idLeaveType=".$this->idLeaveType." and idEmployee=".$this->idEmployee." AND idle=0";
        $clauseOrderBy="startDate DESC";
        $lvEarnedList = $this->getSqlElementsFromCriteria(null,false,$clauseWhere, $clauseOrderBy);
        if($this->quantity!=null && $lvEarnedList){
            //going with the principle that there cannot be at the same time a leaveEarned with  a quantity null and another with a quantity set
            if($lvEarnedList[0]->startDate==$this->startDate && $lvEarnedList[0]->endDate==$this->endDate){
                self::$_fieldsAttributes['leftQuantity']='';
                self::$_fieldsAttributes['quantity']='';
            }
        }
    }
    
    $colScript = parent::getValidationScript($colName);
    
    
    if($colName=="quantity"){
        if($this->quantity!=null && $lvEarnedList){
            //going with the principle that there cannot be at the same time a leaveEarned with  a quantity null and another with a quantity set
            if($lvEarnedList[0]->startDate==$this->startDate && $lvEarnedList[0]->endDate==$this->endDate){
                $colScript.='<script type="dojo/connect" event="onChange" >';
                $colScript.='if(this.value=='.$old->quantity.'){';
                $colScript.='   dijit.byId("leftQuantity").set("readOnly", null);';
                $colScript.='}else{';
                $colScript .= '    dijit.byId("leftQuantity").set("readOnly", true);';
                $colScript.='}';
                $colScript .= '</script>';
            }
        }
        
    }
    
    if($colName=="leftQuantity"){
        if($this->quantity!=null && $lvEarnedList){
            //going with the principle that there cannot be at the same time a leaveEarned with  a quantity null and another with a quantity set
            if($lvEarnedList[0]->startDate==$this->startDate && $lvEarnedList[0]->endDate==$this->endDate){
                $colScript.='<script type="dojo/connect" event="onChange" >';
                $colScript.='if(this.value=='.$old->leftQuantity.'){';
                $colScript.='   dijit.byId("quantity").set("readOnly", null);';
                $colScript.='}else{';
                $colScript .= '    dijit.byId("quantity").set("readOnly", true);';
                $colScript.='}';
                $colScript .= '</script>';
            }
        }
        
    }
    
    return $colScript;
  }
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save($withInitialLeftQuantity=false) {
    $old=$this->getOld();
        
    //to modify leftQuantity depending on the value of quantity
    if(($this->id==null and $this->quantity!=null) || ($old->quantity==null && $this->quantity!=null)){
        if (!$withInitialLeftQuantity) {
            $this->leftQuantity = $this->quantity;
        }
    }else if($old->quantity!=null && $this->quantity==null){
        $this->leftQuantity = null;
    }else if($old->quantity!=null && $this->quantity!=null && (float)$old->quantity!=$this->quantity && $old->leftQuantity!=null && $this->leftQuantity!=null && (float)$old->leftQuantity==$this->leftQuantity){
        $quantityDiff= $this->quantity - (float)$old->quantity;
        
        if($this->leftQuantity+$quantityDiff<=0){
            $isAnticipated = isAnticipatedOfLvType($this->idEmployee, $this->idLeaveType);
            
            if($isAnticipated){
                $this->leftQuantity+=$quantityDiff;
            }else{
                if($this->leftQuantity+$quantityDiff<=0){
                    $this->leftQuantity=0;
                }
                else{
                    $this->leftQuantity+=$quantityDiff;
                }
                
            }
        }else{
            $this->leftQuantity+=$quantityDiff;
        }
    }
        
    if (RequestHandler::getValue('buttonInitWithContractualValues')=='on'){
        $this->setLeavesRight();
    }
    if ($this->leftQuantity!=$old->leftQuantity or $this->quantity!=$old->quantity or 
        $this->startDate!=$old->startDate or $this->endDate != $old->endDate) {
        $currentDate = new DateTime();
        $currentDateString = $currentDate->format("Y-m-d");
        $this->lastUpdateDate = $currentDateString;
    }
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
    $result=parent::control();
    if ($result=="OK") { $result=""; }
    
    $old = $this->getOld();
    
    if($this->idLeaveType==NULL){
        $result.='<br/>' . i18n('idLeaveTypeMandatory');
    }
    
    if($this->idEmployee==NULL){
        $result.='<br/>' . i18n('idEmployeeMandatory');
    }

    // At least one EmployeeLeaveEarned unclosed by leave type
    if ($old->idle==0 and $this->idle==1 and $this->quantity!==null) {
        $unclosedEmpLE = $this->getEmployeeLeaveEarnedForAnEmployee($this->idEmployee,$this->idLeaveType, false,false);
        if (!count($unclosedEmpLE)) {
        $result.='<br/>' . i18n('AtLeastOneOpenedEmployeeLeaveEarned');            
        }
    }
    
    // startDate/endDate/quantity must be defined/undefined at the same time
    if($this->leftQuantity==null and $this->quantity==0) {
        $this->leftQuantity = $this->quantity;
    }
    if( ! (
           ($this->startDate==null && $this->endDate==null && $this->quantity==null && $this->leftQuantity==null) || 
            ($this->startDate!==null && $this->endDate!==null && ($this->quantity!==null && $this->quantity!=="") && 
             ($this->leftQuantity!==null && $this->leftQuantity!=="") 
            ))
           )
    {
        $result.='<br/>' . i18n('errorQuantityStartDateEndDateMustBeDefinedTogether');
    }
    
    //if the endDate is set before the startDate
    if($this->startDate > $this->endDate){
        $result.='<br/>' . i18n('invalidEndDate');
    }
    
    if($this->quantity!=null && $this->quantity<=0){
        $result.='<br/>' . i18n('invalidQuantity');
    }
    
    //TO DO: MESSAGE CONFIRM
    //multiple infinite or infinite=>finite
    //control if there is already a leaveEarned (with same idEmployee/idLeaveType) with null dates and quantity 
    //(a leaveEarned with null dates and quantity means that the employee can ask as many leaves as he wants  for this leaveType, so there cannot be at the same time 
    //a leaveEarned which indicate the right to ask an unlimited number of leaves 
    //and another with a quantity which indicate that the employee can ask a limited number of leaves) 
    $clauseWhere="idEmployee = ".$this->idEmployee." AND idLeaveType = ".$this->idLeaveType." AND quantity IS NULL AND idle=0";
    if($this->id!=NULL){
        $clauseWhere.=" AND id <>".$this->id;
    }
    $lvEarnedQNullList=$this->getSqlElementsFromCriteria(null,false,$clauseWhere);
    if($lvEarnedQNullList){
        $result.='<br/>' . i18n('errorMustDeleteNullLeaveEarnedFirst');
    }
    
    //TO DO: MESSAGE CONFIRM
    //finite=>infinite
    $clauseWhere="idEmployee = ".$this->idEmployee." AND idLeaveType = ".$this->idLeaveType." AND quantity IS NOT NULL AND idle=0";
    if($this->id!=NULL){
        $clauseWhere.=" AND id <>".$this->id;
    }
    $lvEarnedQNotNullList=$this->getSqlElementsFromCriteria(null,false,$clauseWhere);
    if($this->quantity==null && $lvEarnedQNotNullList){
        $result.='<br/>' . i18n('errorMustCloseNotNullLeaveEarnedFirst');
    }
    
    //control if there is already a leaveEarned with the same dates/idEmployee/idLeaveType
    if($this->startDate!=null && $this->endDate!=null){
        $start=(new DateTime($this->startDate))->format('Y-m-d');
        $end=(new DateTime($this->endDate))->format('Y-m-d');
        $clauseWhere="idLeaveType=".$this->idLeaveType." AND idEmployee=".$this->idEmployee." AND idle=0 AND ( (startDate>='$start' AND NOT (startDate>'$end')) OR "
                . "(endDate<='$end' AND NOT (endDate<'$start')) OR "
                . "(startDate<='$start' AND endDate>='$end') )";
        if($this->id!=null){
            $clauseWhere.="AND id <>".$this->id;
        }
        $list=$this->getSqlElementsFromCriteria(null,false,$clauseWhere);
        if($list){
            $result.='<br/>' . i18n('errorLeaveEarnedOverlap');
        }
    }
    
    
    //TO DO: MESSAGE CONFIRM
    //a left cannot be superior to quantity
    if($this->quantity!=NULL){
        if($this->leftQuantity > $this->quantity){
            $result.='<br/>' . i18n('errorLeftQSuperiorToQuantity');
        }
    }
    
    $old = $this->getOld();
    //constraint: can't create a leaveEarned with a startDate that is not valid anymore compared to today 
    if($this->startDate!=$old->startDate || $this->endDate != $old->endDate){
        $critContract=array("idEmployee"=>$this->idEmployee,"idle"=>"0");
    
        $empContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract",$critContract);
        $critLvTypeOfEmpContractType = array(
                    "idLeaveType"=>$this->idLeaveType, 
                    "idEmploymentContractType"=>$empContract->idEmploymentContractType, 
                    "idle"=>"0"
                    );
        $lvTypeOfEmpContractType = SqlElement::getFirstSqlElementFromCriteria("LeaveTypeOfEmploymentContractType",$critLvTypeOfEmpContractType);
        if($lvTypeOfEmpContractType){
            if($lvTypeOfEmpContractType->validityDuration!=null){
                $thisStartDateTime=new DateTime($this->startDate);
                $testStart=new DateTime("now");
                $testDateInterval = new DateInterval("P".$lvTypeOfEmpContractType->validityDuration."M");
                $testStart->sub($testDateInterval);
                if($thisStartDateTime < $testStart){
                    $result.='<br/>' . i18n('errorLvEarnedStartDateNotValidAnymore');
                }
            }
        }
    }
    
    //quantity and leftQuantity must be modulos of 0.5
    if($this->quantity!==null && trim($this->quantity)!=="" && $this->leftQuantity!==null && trim($this->leftQuantity)!==""){
        if(! (fmod($this->quantity, 0.5) == 0) || ! (fmod($this->leftQuantity, 0.5) == 0)){
            $result.='<br/>' . i18n('errorLeftQuantityOrQuantityNotModuloOfZeroPointFive');
        }
    }
    
    $defaultControl=parent::control();
       if ($defaultControl!='OK') {
               $result.=$defaultControl;
       }

       if ($result=="") $result='OK';
       
    return $result;
  }
   
    /**
     * Overwrite parent function
     * @return string
     */
    public function deleteControl() {
        $result = parent::deleteControl();
        if ($result=="OK") { $result="";}
        
        // At least one EmployeeLeaveEarned unclosed
        if ($this->idle==0 and $this->quantity!==null) {
            $unclosedEmpLE = $this->getEmployeeLeaveEarnedForAnEmployee($this->idEmployee,$this->idLeaveType, false,false);
            if (!count($unclosedEmpLE)) {
            $result.='<br/>' . i18n('AtLeastOneOpenedEmployeeLeaveEarned');            
            }
        }
        if ($result=="") {$result='OK';}
        return $result;
    }
  
// =============================================================================================================
// MISCELANOUS FUNCTION
// =============================================================================================================
    /**
     * Return the EmployeeLeaveEarned list of the resource (id passed in parameters)
     * @param Integer $idEmployee : The employee for which get the EmployeeLeaveEarned
     * @param Integer $idLeaveType : The leave type for which get the EmployeeLeaveEarned. If null, all leave type
     * @param Boolean $withClosed : If false, only the EmployeeLeaveEarned with idle=0
     * @param Boolean $selfInclude : If false, this EmployeeLeaveEarned is'nt in the list
     * @return EmployeeLeaveEarned[] : List of EmployeeLeaveEarned for the employee
     */
    public function getEmployeeLeaveEarnedForAnEmployee($idEmployee, $idLeaveType=null, $withClosed=true, $selfInclude=true) {
        $clauseWhere = "idEmployee = ".$idEmployee;
        if ($idLeaveType!=null) {
            $clauseWhere .= " AND idLeaveType=$idLeaveType";            
        }
        if (!$withClosed) {
            $clauseWhere .= " AND idle=0";
        }
        if (!$selfInclude) {
            $clauseWhere .= " AND id".($this->id?"<>".$this->id:" IS NOT NULL");
        }        
        $listEmpLE = $this->getSqlElementsFromCriteria(null, false, $clauseWhere);
        return $listEmpLE;
    }
        
    /**
     * Get the Leaves right for this Employee Leave Earned
     * @param boolean $isQuantityToCalculate : If true, quantity and left are calculated in function of the start date contrat and actual date
     *                                         Else, quantity and left are stored values if LeaveTypeOfLeaveContractType
     * @param boolean $forActualPeriod       : If true, get of actual period else for the next period
     * @return Array : Keys (quantity, left, startDate, endDate) => value
     */
    public function getLeavesRight($isQuantityToCalculate=false, $forActualPeriod=true) {
        $leavesRight['quantity']=null;
        $leavesRight['left']=null;
        $leavesRight['startDate']=null;
        $leavesRight['endDate']=null;
        
        $contract=null;
        $right = getActualLeaveContractualValues($this->idEmployee, $this->idLeaveType, $contract);
        if ($contract==null) {
            return $leavesRight;
        }
        $customQuantity = getActualLeaveConstractCustomQuantity($this->idLeaveType, $contract, $this->idEmployee);
        $contractStartDateString = $contract->startDate;
        if ($contract->endDate!=null) {
            $endDate=new DateTime($contract->endDate);
        } else {
            $endDate = null;
        }

        if ($right!=null) {
            if ($right->periodDuration and trim($right->periodDuration)!="") {
                if (!$right->earnedPeriod or trim($right->earnedPeriod)=="") {
                    $right->earnedPeriod = $right->periodDuration;                            
                }                
            }
            $currentDate = new DateTime();
            if ($forActualPeriod) { // For actual period
                $currentDateString = $currentDate->format("Y-m-d");
                $year = $currentDate->format("Y");
            } else { // For next period
                // Contract is ended and end contract date < current Date => No quantity earned
                if ($contract->endDate!=null and $endDate < $currentDate) {                    
                    return $leavesRight;                        
                }
                
                if ($right->periodDuration and trim($right->periodDuration)!="") {
                    // For earned period < period duration => No quantity earned
                    if ($right->earnedPeriod < $right->periodDuration) {
                        return $leavesRight;
                    }
                    // Year is the current year + period duration, if not null
                    $currentDate = new DateTime();
                    $currentDate->add(new DateInterval('P'.$right->periodDuration.'M'));
                    $currentDateString = $currentDate->format("Y-m-d");
                    $year = $currentDate->format("Y");
                } else {
                    return $leavesRight;
                }   
                // If fact, it's like contract start date is the current date
                $beginDate = new DateTime();
                $beginDateString = $beginDate->format("Y-m-d");
                if ($contractStartDateString < $beginDateString) {
                    $contractStartDateString = $beginDateString;
                }
            }
            if ($right->startDayPeriod and trim($right->startDayPeriod)!="") {
                $day = ($right->startDayPeriod>9?$right->startDayPeriod:"0".$right->startDayPeriod);                
            } else {
                $day="01";
            }
            if ($right->startMonthPeriod and trim($right->startMonthPeriod)!="") {
                $month = ($right->startMonthPeriod>9?$right->startMonthPeriod:"0".$right->startMonthPeriod);
                $startDate = new DateTime($year."-".$month."-".$day);
                $leavesRight['startDate'] = $startDate->format("Y-m-d");
            } else {
                $month = $currentDate->format("m");
                $startDate = new DateTime($year."-".$month."-".$day);
            }
            if ($right->periodDuration and trim($right->periodDuration)!="" and $endDate==null) {
                $endDate = clone $startDate;
                $endDate->add(new DateInterval('P'.$right->periodDuration.'M'));
                $endDate->sub(new DateInterval("P1D"));
                $leavesRight['endDate'] = $endDate->format("Y-m-d");
            }
                        
            // No endDate => Quantity is infinite
            if ($endDate==null) {
                if ($contract->endDate!=null) {
                    $contractEndDateString = $contract->endDate->format("Y-m-d");
                    // If contract has endDate < currentDate => no right
                    if ($contractEndDateString < $currentDateString) {
                        $leavesRight['quantity']=0+$customQuantity;
                        $leavesRight['left']=0+$customQuantity;
                        $leavesRight['startDate']=$startDate->format("Y-m-d");
                        $leavesRight['endDate']=$endDate->format("Y-m-d");
                        return $leavesRight;
                    } else { // endate = contract end Date
                        $endDate = clone $contract->endDate;
                    }
                } else {
                    if ($right->quantity==0) {
                        $leavesRight['quantity']=0;
                    }
                    return $leavesRight;
                }    
            } else {
                if ($contract->endDate !=null and $contract->endDate < $currentDate) {
                    $leavesRight['endDate'] = $contract->endDate;
                }
            }
            // No calculation => Quantity and left are stored values
            if (!$isQuantityToCalculate) {
                $leavesRight['quantity']=($customQuantity==0?$right->quantity:($right->quantity==null?$customQuantity:$right->quantity+$customQuantity));                
                $leavesRight['left']=$leavesRight['quantity'];
                return $leavesRight;
            }
            
            $contractStartDate= new DateTime($contractStartDateString);
            $periodDuration = $right->periodDuration;            
            $quantity = $right->quantity;
            $earnedPeriod = $right->earnedPeriod;
            // Start and End Date for calculation
            if ($earnedPeriod<$periodDuration) {
                $calcStartDate = clone $startDate;
                $calcStartDateString = $calcStartDate->format("Y-m-d");
                if ($calcStartDateString<$contractStartDateString) {
                    $calcStartDate = clone $contractStartDate;
                    $calcStartDateString = $contractStartDateString;                    
                }

                $calcEndDate = new DateTime();
                $calcEndDateString = $calcEndDate->format("Y-m-t");
                $calcEndDate = new DateTime($calcEndDateString);                
                $calcEndDate->sub(new DateInterval("P".$earnedPeriod."M"));
                $calcEndDateString = $calcEndDate->format("Y-m-d");
                if ($calcEndDateString<$calcStartDateString) {
                    $calcEndDate = clone $calcStartDate;
                    $calcEndDateString = $calcStartDateString;
                }
                
            } else {
                $calcStartDate = clone $contractStartDate;
                $calcStartDateString = $contractStartDateString;

                $calcEndDate = clone $endDate;
                $calcEndDate->sub(new DateInterval('P'.$earnedPeriod.'M'));
                $calcEndDateString = $calcEndDate->format("Y-m-d");
            }
            // StartDate of activ Employment Contract is greater that endDate of calculation
            // => Quantity and Left = 0
            if ($contractStartDateString >= $calcEndDateString) {
                $leavesRight['quantity']=0+$customQuantity;                
                $leavesRight['left']=$leavesRight['quantity'];
                return $leavesRight;
            }
            
            // Calculate difference in month between calcEndDate and contract start date
            $diff = $calcEndDate->diff($calcStartDate);
            $diffMonths = ($diff->format('%y') * 12) + $diff->format('%m')+1;
            // Must have integer quotity or not
            if ($right->isIntegerQuotity) {
                $quotityDays=0;
                $quotity = round($quantity/$periodDuration,0);
            } else {
                $quotityDays = abs($contractStartDate->format("d") - $startDate->format("d"))/30;
                $quotity = $quantity/$periodDuration;
            }
            
            $earned = (float) ($quotity*$diffMonths) - $quotityDays;
            $earnedRounded = round($earned,1);
            $mod = (fmod($earnedRounded,0.5)>=0.5?0.5:0);
            if ($diffMonths>$periodDuration) {
                $theQuantity = $quantity;
            } else {
                $theQuantity = min($quantity,round($earnedRounded,0)-$mod);
            }
            if ($forActualPeriod) {
                $leavesRight['quantity'] = $theQuantity+$customQuantity;
            } else {
                $leavesRight['quantity'] = max(0,$right->quantity - $theQuantity)+$customQuantity;
            }
            // Left is initialized with old left + different between old quantity and calculated quantity
            if ($theQuantity>0) {
                $diffQuantity = $theQuantity - $this->quantity;
                $left = $this->leftQuantity + $diffQuantity + $customQuantity;
                if ($left<0) {
                    $leavesRight['left']=0;
                } else {
                    $leavesRight['left']=min($leavesRight['quantity'],$left);
                }
            } else {
                $leavesRight['left']=0;
            }
        }
        return $leavesRight;
    }
    
    /**
     * Set quantity, left, startDate, endDate of this with activ contractual values
     */
    public function setLeavesRight($isQuantityToCalculate=false) {
        $leavesRight = $this->getLeavesRight($isQuantityToCalculate, true);
        $this->quantity = $leavesRight['quantity'];
        $this->leftQuantity = $leavesRight['left'];                        
        $this->startDate = $leavesRight['startDate'];
        $this->endDate = $leavesRight['endDate'];
    }

    public function getThisLeaves($arrayStatus=array()) {
        $critLeaves = "idEmployee=$this->idEmployee AND idLeaveType=$this->idLeaveType AND startDate<='$this->endDate' AND endDate>='$this->startDate'";
        $inStatus = "";
        if (!$arrayStatus) {
            foreach($arrayStatus as $idStatus) {
                if ($inStatus=="") { $inStatus=" AND idStatus IN ("; }
                $inStatus .= $idStatus.",";
            }
            $inStatus = substr($inStatus, 0, -1).")";
        }
        $critLeaves .= $inStatus;
        $leave = new Leave();
        $leaves = $leave->getSqlElementsFromCriteria(null,false,$critLeaves);
        return $leaves;
    }
}
?>
