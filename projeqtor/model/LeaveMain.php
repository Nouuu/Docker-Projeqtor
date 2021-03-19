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
// ELIOTT - LEAVE SYSTEM
/** ============================================================================
 * LeaveMain
 * Management of the leave
 ** ============================================================================ */  
require_once('_securityCheck.php'); 
class LeaveMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
    public $id;    // redefine $id to specify its visible place
    public $idUser; // Creator of the leave
    public $idLeaveType;
    public $startDate;
    public $_spe_startAMPM;
    public $startAMPM;
    public $endDate;
    public $_spe_endAMPM;
    public $endAMPM;
    public $nbDays;
    public $_spe_nbDaysRemaining;
    public $_nbRemain;
    public $comment;
  public $_sec_treatment;
    public $_spe_isJustifiable;
    public $idStatus;
    public $_spe_isOutOfWorkflowOrDesynchronized;
    public $statusOutOfWorkflow;
    public $statusSetLeaveChange;
    public $submitted;
    public $rejected;
    public $accepted;
    public $_spe_transition;
    public $idEmployee; // Employee that takes the leave
    public $requestDateTime;
    public $idResource; //the id of the resource who validated the leave
    public $processingDateTime;
    public $idle;
  public $_sec_maintenance;
    public $_spe_status;
  public $_Attachment=array();

  public $_nbColMax=3;
  
  private $__workflowList;
  
    // Define the layout that will be used for lists
    private static $_layout='
        <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
        <th field="nameEmployee" formatter="thumbName22" width="12%" >${employee}</th>
        <th field="nameLeaveType" width="5%" >${type}</th>
        <th field="startDate" width="8%" formatter="dateFormatter">${startDate}</th>
        <th field="endDate" width="8%" formatter="dateFormatter">${endDate}</th>
        <th field="nbDays" formatter="decimalFormatter" width="4%" ># ${nbDays}</th>
        <th field="colorNameStatus" width="8%" formatter="colorNameFormatter">${idStatus}</th>
        <th field="statusOutOfWorkflow" width="5%" formatter="booleanFormatter">${statusOutOfWorkflow}</th>
        <th field="statusSetLeaveChange" width="5%" formatter="booleanFormatter">${statusSetLeaveChange}</th>
        <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
        ';

    private static $_fieldsAttributes=array(
        "startDate" => "required",
        "endDate" => "required",
        "idLeaveType" => "required",
        "nbDays" => "readonly",
        "idResource" => "readonly,nocombo,invisible",
        "processingDateTime" => "readonly,invisible",
        "idEmployee" => "readonly",
        "requestDateTime" => "readonly",
        "startAMPM" => "hidden",
        "endAMPM" => "hidden",
        "idStatus" => "required",
        "idUser" => "readonly",
        "submitted" => "hidden",
        "rejected" => "hidden",
        "accepted" => "hidden",
        "statusOutOfWorkflow" => "hidden",
        "statusSetLeaveChange" => "hidden",
        "idle" => "readonly"
        );  

    private $___dFieldsAttributes=array();

    private static $_fieldsTooltip = array(
        "statusOutOfWorkflow" => "tooltipStatusOutOfWorkflow",
        "statusSetLeaveChange" => "tooltipStatusSetLeaveChange"
    );  
      
    private static $_colCaptionTransposition = array('idUser' => 'issuer',
                                                    'idResource' => 'validator'
                                                   );

    private static $_databaseColumnName = array();

    private static $_databaseTableName = 'employeeleaveperiod'; 
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
           
    parent::__construct($id,$withoutDependentObjects);
    $neutralStatus=true;
    if ($this->idStatus!=NULL) {
        $theStatus = new Status($this->idStatus);
        $neutralStatus = $theStatus->isLeaveNeutralStatus();
    }
    if($this->idStatus!=NULL and !$neutralStatus){
        self::$_fieldsAttributes['idLeaveType']='readonly';
        self::$_fieldsAttributes['startDate']='readonly';
        self::$_fieldsAttributes['endDate']='readonly';
        self::$_fieldsAttributes['comment']='readonly';
        self::$_fieldsAttributes['idStatus']='readonly';
        if ($this->submitted==1) {
            $this->___dFieldsAttributes["processingDateTime"] = "readonly,invisible";
            $this->___dFieldsAttributes["idResource"] = "readonly,nocombo,invisible";            
        } else {
            $this->___dFieldsAttributes["processingDateTime"] = "readonly";
            $this->___dFieldsAttributes["idResource"] = "readonly,nocombo";
        }
    }
    //At the creation of a new leave 
    if($id==NULL){
        $this->___dFieldsAttributes["requestDateTime"] = "invisible";
        // Initialize idEmployee with the user connected id
        $this->idEmployee = getSessionUser()->id;
        //Initialize nbDays to 1
        $this->nbDays=1;
        
        $lvType= new LeaveType();
        $lvTypeList = $lvType->getSqlElementsFromCriteria(null,false,"idle=0","sortOrder ASC");
        if (count($lvTypeList)==0) {
            $this->idLeaveType= null;
        } else {
            $this->idLeaveType = $lvTypeList[0]->id;
        }
        //Initialize idStatus to first status of the workflow
        $wfList=$this->getWorkflowStatusesOfLeaveType($this->idEmployee, $this->idEmployee, $this->idLeaveType);
        if (count($wfList)>0) {
            reset($wfList);
            $this->idStatus = key($wfList);
        } else {
            $this->idStatus = 1;
        }
    }
    // If connected user is'nt Manager and is'nt leave Admin and is'nt Manager of Employee
    if (!isLeavesManager(getSessionUser()->id) and 
        !isLeavesAdmin(getSessionUser()->id) and 
        !isManagerOfEmployee(getSessionUser()->id, $this->idEmployee)) {
        // idEmployee and idStatus are Readonly
        self::$_fieldsAttributes['idEmployee']="readonly";
        $this->___dFieldsAttributes["idEmployee"] = "readonly";
        if ($this->rejected==0 and $this->accepted==0 and $this->idEmployee==getSessionUser()->id) {
            self::$_fieldsAttributes['idStatus']="required";
            $this->___dFieldsAttributes["idStatus"] = "required";
        } else {
            self::$_fieldsAttributes['idStatus']="readonly";
            $this->___dFieldsAttributes["idStatus"] = "readonly";
        }
//        self::$_fieldsAttributes['idStatus']="readonly";        
    } else {
        // If Status is not neutral
        if (!$neutralStatus) {
            // idEmployee is readonly
            self::$_fieldsAttributes['idEmployee']="readonly";
            $this->___dFieldsAttributes["idEmployee"] = "readonly";
            // If connected user is manager or leave admin
            if (isLeavesManager(getSessionUser()->id) or 
                isLeavesAdmin(getSessionUser()->id) or 
                isManagerOfEmployee(getSessionUser()->id, $this->idEmployee) ) {
                // idStatus = required
                self::$_fieldsAttributes['idStatus']="required";
            } else {
                if ($this->rejected==0 and $this->submitted==0 and $this->idEmployee== getSessionUser()->id) {
                    self::$_fieldsAttributes['idStatus']="required";                    
                } else {
                    self::$_fieldsAttributes['idStatus']="readonly";
                }
            }
        } else { // status is neutral AND (manager or leave admin)
            if ($this->id==null) {
                // idEmployee is required
                self::$_fieldsAttributes['idEmployee']="required";
                $this->___dFieldsAttributes["idEmployee"] = "required";
            } else {
                if ($this->idUser != getSessionUser()->id) { // Is'nt the creator
                    // idEmployee, idLeaveType, startDate, endDate, startAMPM, endAMPM are readonly
                    self::$_fieldsAttributes['idEmployee']="readonly";
                    $this->___dFieldsAttributes["idEmployee"] = "readonly";
                    $this->___dFieldsAttributes["idLeaveType"] = "readonly";
                    $this->___dFieldsAttributes["startDate"] = "readonly";
                    $this->___dFieldsAttributes["endDate"] = "readonly";
                    $this->___dFieldsAttributes["startAMPM"] = "readonly,hidden";
                    $this->___dFieldsAttributes["endAMPM"] = "readonly,hidden";                    
                }
            }
        }    
    }
    
    // If leave with status no more in workflow associated to its leave type
    // => NO CHANGE POSSIBLE
    if ($this->statusOutOfWorkflow==1 and $this->id>0) {
//        self::$_fieldsAttributes['statusOutOfWorkflow']="readonly";
//        self::$_fieldsAttributes['statusSetLeaveChange']="readonly";
        self::$_fieldsAttributes['idStatus']="readonly";        
        self::$_fieldsAttributes['idLeaveType']='readonly';
        self::$_fieldsAttributes['startDate']='readonly';
        self::$_fieldsAttributes['endDate']='readonly';
        self::$_fieldsAttributes['comment']='readonly';
        self::$_fieldsAttributes['idEmployee']="readonly";
        $this->___dFieldsAttributes["idEmployee"] = "readonly";
    }

    // If leave with status setXXXXXLeave change
    // => NO CHANGE POSSIBLE
    if ($this->statusSetLeaveChange==1 and $this->id>0) {
//        self::$_fieldsAttributes['statusOutOfWorkflow']="hidden";
//        self::$_fieldsAttributes['statusSetLeaveChange']="readonly";
        self::$_fieldsAttributes['idStatus']="readonly";        
        self::$_fieldsAttributes['idLeaveType']='readonly';
        self::$_fieldsAttributes['startDate']='readonly';
        self::$_fieldsAttributes['endDate']='readonly';
        self::$_fieldsAttributes['comment']='readonly';
        self::$_fieldsAttributes['idEmployee']="readonly";
        $this->___dFieldsAttributes["idEmployee"] = "readonly";
    }
    
    if ($this->statusOutOfWorkflow==0 and $this->statusSetLeaveChange==0) {
        unset($this->_sec_maintenance);
        unset($this->_spe_status);
    }
    
    $workflowList = $this->getWorkflowStatusesOfLeaveType();
    $this->__workflowList="";
    $i=0;
    foreach($workflowList as $wf) {
        $this->__workflowList .= 'wfStatusArray['.$i.']='.json_encode($wf).';'; 
        $i++;
    }
    // idUser is'nt readonly if connected user is admin
    if (getSessionUser()->idProfile==1) {
        self::$_fieldsAttributes['idUser']="";
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

  /**
   * Return the fields tooltip
   * @return array The fields tooltip
   */ 
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  } 
   
  /** ==========================================================================
   * Return the specific layout
   * @return string the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return array the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return array the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }

  /** ========================================================================
   * Return the specific databaseTableName
   * @return string the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
    
  /** ========================================================================
   * Return the specific databaseTableName
   * @return string the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  /**
   * Get the dynamic attributes (or static if dynamic not found) of the field that name is passed in parameter
   * @param String $fieldName : The fieldName for witch get attributes
   * @return String Attributes of the field
   */
  public function getFieldAttributes($fieldName) {
    if (array_key_exists ( $fieldName, $this->___dFieldsAttributes )) {
      return $this->___dFieldsAttributes[$fieldName];
    } else {
        return parent::getFieldAttributes($fieldName);
    }      
  }
  
 /* ========================================================================================
  * SQL ELEMENT FUNCTIONS
    ======================================================================================== */   
  
  /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $neutral = (($this->submitted==0 and $this->accepted==0 and $this->rejected==0)?true:false);
    if ($this->id!=null) {
        $old=$this->getOld();
        $oldNeutral = (($old->submitted==0 and $old->accepted==0 and $old->rejected==0)?true:false);
    } else {
        $oldNeutral = $neutral;
    }    
    
        // Can't take leave if employee has no unclosed contract
        $empl = new Employee($this->idEmployee);
        if ($empl->hasAllClosedContracts()) {
            $result.= "<br/>".i18("NoUnclosedContractForThisEmployeeCantTakeLeaves");
            return $result;
        }
    
        //test if a leaveType exists
        $lvType = new LeaveType();
        $lvTypeRes = $lvType->getSqlElementsFromCriteria(array());
        if(! $lvTypeRes){
            $result.='<br/>' . i18n('leaveTypesDontExist');
        }
        
        // leaveType can't be null
        if ($this->idLeaveType==null) {
            $result.= "<br/>".i18("LeaveTypeMandatory");
        }
        
        // Can't take a leave after end of contract
        $contract = EmploymentContract::getActualEmploymentContractForAnEmployee($this->idEmployee);
        if ($contract->endDate!=null) {
            $contractEndDate = $contract->endDate->format("Y-m-d");
            $endDateString = $this->endDate->format("Y-m-d");
            if ($endDateString > $contractEndDate) {
                $result.='<br/>' . i18n('CantTakeLeaveAfterEndOfContract');            
            }
        }
        
        if ($this->id!=null) {
            //Only him self or it's manager or leave Admin can change something if leave is not submitted, accepted, rejected
            if ($this->submitted==0 and $this->accepted==0 and $this->rejected==0) {
                if ($this->idEmployee != getSessionUser()->id and 
                    !isManagerOfEmployee(getSessionUser()->id, $this->idEmployee) and
                    !isLeavesAdmin(getSessionUser()->id)) {
                    if ($old->idUser != $this->idUser or 
                        $this->idLeaveType != $old->idLeaveType or
                        $this->idEmployee != $old->idEmployee or
                        $this->idResource != $old->idResource or
                        $this->idle != $old->idle or
                        $this->endDate != $old->endDate or
                        $this->startDate != $old->startDate or
                        $this->startAMPM != $old->startAMPM or
                        $this->endAMPM != $old->endAMPM or
                        $this->comment != $old->comment) {
                        $result .= "<br/>".i18n("OnlySelfEmployeeOrManagerCanChangeSomething");
                    }
                }
            } else {
                // Status has changed AND submitted = 1 AND accepted = 0 AND rejected = 0
                if ( $this->idStatus != $old->idStatus AND 
                     $old->submitted == 1 AND $old->accepted == 0 AND $old->rejected == 0) 
                {
                    // If user is'nt the employee manager or a leave Admin
                    if (!isManagerOfEmployee(getSessionUser()->id, $this->idEmployee) and
                        !isLeavesAdmin(getSessionUser()->id))
                    {
                        // => the new status can't be with setAcceptedLeave=1, setRejectedLeave=1
                        $theStatus = new Status($this->idStatus);
                        if ($theStatus->setRejectedLeave==1 OR $theStatus->setAcceptedLeave==1) {
                            $result .= "<br/>".i18n("NotAllowedToChangeInThisStatus");                            
                        }
                    } else {
                        if (!isManagerOfEmployee(getSessionUser()->id, $this->idEmployee) &&
                            !isLeavesAdmin(getSessionUser()->id)) {
                            $result .= "<br/>".i18n("EmployeeManagerOrLeaveSystemAdminCanChangeStatus");
                        }                        
                    }
                }                    
                
            }
        }
        
        //if nbDays==0, then the leave is composed entirely of OffDays or the dates are wrong
        if($this->nbDays<=0){
            $result.='<br/>' . i18n('nbDaysCantBeInferiorOrEqualToZero');
        }
        
        //if the endDate is set before the startDate
        if($this->startDate > $this->endDate){
            $result.='<br/>' . i18n('invalidEndDate');
        }
        
        // StartDate nb days before or after now
        if ($contract!=null) {
            $contractType = $contract->getEmploymentContractType();
            if ($contractType->id>0) {
                $crit = array("idle" => '0',
                              "idEmploymentContractType" => $contractType->id,
                              "idLeaveType" => $this->idLeaveType
                             );
                $lvTpOf = SqlElement::getFirstSqlElementFromCriteria("LeaveTypeOfEmploymentContractType", $crit);
                if ($lvTpOf) {
                    if ($lvTpOf->nbDaysAfterNowLeaveDemandIsAllowed!=null) {
                        $intervalPrior = $lvTpOf->nbDaysAfterNowLeaveDemandIsAllowed;                        
                    } else {
                        $intervalPrior = 9999;
                    }
                    if ($lvTpOf->nbDaysBeforeNowLeaveDemandIsAllowed!=null) {
                        $intervalFutur = $lvTpOf->nbDaysBeforeNowLeaveDemandIsAllowed;
                    } else {
                        $intervalFutur = 9999;
                    }
                } else {
                    $intervalPrior = 9999;
                    $intervalFutur = 9999;                    
                }
            } else {
                $intervalPrior = 9999;
                $intervalFutur = 9999;
            }
        } else {
            $intervalPrior = 9999;
            $intervalFutur = 9999;
        }
        
        if (!isManagerOfEmployee(getSessionUser()->id, $this->idEmployee) and
            !isLeavesAdmin(getSessionUser()->id)) {        
            if ($intervalPrior<9999) {
                if( $this->startDate < ((new DateTime('now'))->sub(new DateInterval('P'.$intervalPrior.'D'))->format('Y-m-d')) and ($neutral OR $this->submitted==1) ){
                    $result.='<br/>' . i18n('cantTakeAStartDatePriorTo',array($intervalPrior)); 
                }
            }
            if ($intervalFutur<9999) {
                if( $this->startDate > ((new DateTime('now'))->add(new DateInterval('P'.$intervalFutur.'Y'))->format('Y-m-d')) and ($neutral OR $this->submitted==1)){
                    $result.='<br/>' . i18n('cantTakeAStartDateSuperiorTo',array($intervalFutur));
                }
            }
        }
            
        //if startDate and endDate are set to the same day and the leave start the afternoon and end in the morning, throw an error
        if($this->startDate==$this->endDate and $this->startAMPM==='PM' and $this->endAMPM==='AM'){
            $result.='<br/>' . i18n('CantStartPMEndAMIfOneDay');
        }
        
        //to test if the leave overlap with other existing leave 
        $thisStartDateRqFormat = (new DateTime($this->startDate))->format('Y-m-d');
        $thisEndDateRqFormat = (new DateTime($this->endDate))->format('Y-m-d');  
        //the request select all the leaves of the requester that overlap with the current one
        //for the overlap, the half-days are taken into account
//        $clauseWhere="idEmployee=".$this->idEmployee." AND rejected <> 1 "//select the leaves of the requester which are not rejected
        $clauseWhere="idEmployee=".$this->idEmployee." "//select the leaves of the requester
                //and the leaves included between the dates of the new leave
                . "AND ((startDate >'$thisStartDateRqFormat' AND endDate <'$thisEndDateRqFormat') "
                //and the leaves which include the dates of the new leaves
                . "OR (startDate <'$thisStartDateRqFormat' AND endDate >'$thisEndDateRqFormat') "
                //and the leaves which include the startDate of the new leave (and their endDate must be inferior or equal to the endDate of the new leave so the request doesn't include all the leaves after endDate
                . "OR (startDate <='$thisStartDateRqFormat' AND endDate >'$thisStartDateRqFormat'  AND endDate <='$thisEndDateRqFormat') "
                //and the leaves which include the endDate of the new leave 
                . "OR (startDate <'$thisEndDateRqFormat' AND endDate >='$thisEndDateRqFormat' AND startDate >= '$thisStartDateRqFormat') "
                //and the leaves which does not respect a particular case (example: it's possible to take a leave which start the 14/06/18 PM and end the 16/06/18 AM with two leaves with the first ending the 14/06/18 AM and the second beginning the 16/06/18 PM)
                . "OR (endDate='$thisStartDateRqFormat' AND startDate='$thisEndDateRqFormat' AND (NOT (endAMPM='AM' AND '$this->startAMPM'='PM')) AND (NOT ('$this->endAMPM'='AM' AND startAMPM='PM')) AND (NOT('$thisStartDateRqFormat'='$thisEndDateRqFormat')) ) "
                //...
                . "OR (endDate='$thisStartDateRqFormat' AND NOT (endAMPM='AM' AND '$this->startAMPM'='PM') AND (NOT('$thisStartDateRqFormat'='$thisEndDateRqFormat')) ) "
                
                . "OR (startDate='$thisEndDateRqFormat' AND NOT ('$this->endAMPM'='AM' AND startAMPM='PM') AND (NOT('$thisStartDateRqFormat'='$thisEndDateRqFormat')) )  "
                
                . "OR (startDate='$thisStartDateRqFormat' AND startDate='$thisEndDateRqFormat' AND startDate=endDate AND ( NOT( (startAMPM='PM' AND endAMPM='PM' AND '$this->startAMPM'='AM' AND '$this->endAMPM'='AM') OR (startAMPM='AM' AND endAMPM='AM' AND '$this->startAMPM'='PM' AND '$this->endAMPM'='PM') ) ) ) "
                . ")"; 
        
        if($this->id != NULL){//to exclude this leave of the request
            $clauseWhere.="AND id <> ".$this->id;
        }
        $clauseWhere.=" AND rejected = 0";
        $clauseOrderBy = "startDate, endDate ASC";
        $queryResult=$this->getSqlElementsFromCriteria ( null, false, $clauseWhere, $clauseOrderBy );
        if($queryResult){//if the query returned some leaves then error
            $result.='<br/>' . i18n('LeavesCannotOverlapWithEachOther');
        }       
        //end overlap test
   	
        // No more left days and anticipated days => Can't take the leave
        $nbAllowedAnticipated = getNbAnticipatedAllowedFormEmployeeAndLeaveType($this->idEmployee, $this->idLeaveType);
        $leftDays = $this->getRemainingDays(0,$this->idEmployee, $this->idLeaveType);
        if ($leftDays < 0 and $nbAllowedAnticipated<=0 and $neutral and $oldNeutral) {
            $result .='<br/>' . i18n('ErrorCantTakeMoreLeaves');
        }
        
        $defaultControl=parent::control();
   	if ($defaultControl!='OK') {
            // Force Change Status if 
            //     - not a new leave
            //                  AND
            //     - old->statusOutOfWorkflow = 1 or old->statusSetLeaveChange = 1
            //                  AND
            //     - this->statusOutOfWorkflow = 0 and this->statusSetLeaveChange = 0
            if ($this->id>0 AND 
                ($old->statusOutOfWorkflow==1 OR $old->statusSetLeaveChange==1) AND
                ($this->statusOutOfWorkflow==0 AND $this->statusSetLeaveChange==0)
               ) {
                $msgChangeStatusNotAllowed = "<br/>".i18n("errorWorflow");
                if ($defaultControl==$msgChangeStatusNotAllowed) {$defaultControl="";}
            }
            $result.=$defaultControl;
   	}
        
        if ($result=="") {$result='OK';}
	
    return $result;
}

  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $old=$this->getOld();
    // Initialize idUser with the user connected id
    if ($this->id==NULL) {
        $this->idUser = getSessionUser()->id;
    }
    
    // Status has changed throw maintenance
    $theNewStatus = RequestHandler::getValue('newIdStatus');
    if ($theNewStatus!=null and $theNewStatus!="" and $theNewStatus!=0 and $this->id!=NULL) {
        $this->idStatus = $theNewStatus;
        $this->statusOutOfWorkflow=0;
        $this->statusSetLeaveChange=0;
    }

    if ($this->id==NULL){
        // Initialize idEmployee with the user connected id if is null
        if ($this->idEmployee==null) {
            $this->idEmployee = getSessionUser()->id;
        }    
        // Initialize requestDateTime to current date time
        $this->requestDateTime = (new DateTime('now'))->format('Y-m-d\TH:i:s');
    }
        
    $status = new Status($this->idStatus);
    $leaveType = new LeaveType($this->idLeaveType);
    $workflow = new Workflow($leaveType->idWorkflow);
    $hasSubmittedLeave = $workflow->hasSetStatusOrLeave("setSubmittedLeave");
    $hasAcceptedLeave =  $workflow->hasSetStatusOrLeave("setAcceptedLeave");
    
    if ($status->setSubmittedLeave==0 and $status->setAcceptedLeave==0 and $status->setRejectedLeave==0) {        
        $this->submitted = ((!$hasSubmittedLeave and $hasAcceptedLeave)?1:0);
        $this->rejected = 0;
        $this->accepted = 0;
    } else {
        // Init submitted, rejected, accepted
        if ($status->setRejectedLeave==1) {
            $this->submitted = 0;
            $this->rejected = 1;
            $this->accepted = 0;            
        } elseif ($status->setAcceptedLeave==1) {
            $this->submitted = 0;
            $this->rejected = 0;
            $this->accepted = 1;
        } elseif ($status->setSubmittedLeave==1) {
            $this->submitted = 1;
            $this->rejected = 0;
            $this->accepted = 0;            
        } else {
            $this->submitted = 0;
            $this->rejected = 0;
            $this->accepted = 0;                        
        }
    }
    
    //to save the id of the validator
    if($this->idStatus!=NULL and ($this->rejected==1 or $this->accepted==1)){
        $this->idResource = getSessionUser()->id;
        $this->processingDateTime = (new DateTime('now'))->format('Y-m-d\TH:i:s');   
    } else {
        $this->idResource = NULL;
        $this->processingDateTime = NULL;
    } 
   
    //to set the hidden parameters startAMPM and endAMPM 
    if (RequestHandler::getValue('startAM')=='on'){
        $this->startAMPM='AM';
    }
    if (RequestHandler::getValue('startPM')=='on'){
        $this->startAMPM='PM';
    }
    if (RequestHandler::getValue('endAM')=='on'){
        $this->endAMPM='AM';
    }
    if (RequestHandler::getValue('endPM')=='on'){
        $this->endAMPM='PM';
    }
    
    //to recalculate nbDays 
    if($old->startDate!=$this->startDate || $old->endDate!=$this->endDate || $old->startAMPM!=$this->startAMPM || $old->endAMPM!=$this->endAMPM || $old->nbDays!=$this->nbDays){
        $this->calculateNbDays();
    }
    
    //to update the attribute leftQuantity of the leaveEarned(s) concerned by this leave 
    $resultClass="";
    if($this->id==null){//create
        $resultClass.=$this->updateLeftQOfLeaveEarned($this->idLeaveType,$this->idEmployee,$this->nbDays,null,true);
    }else if($this->idEmployee != $old->idEmployee || $this->idLeaveType!=$old->idLeaveType){//if the leaveType/idEmployee changed, then delete the leave and create a new (simpliest way to deal with these changes)
        $resultClass.=$this->updateLeftQOfLeaveEarned($old->idLeaveType,$old->idEmployee,$old->nbDays,null,false,false,true);
        $resultClass.=$this->updateLeftQOfLeaveEarned($this->idLeaveType,$this->idEmployee,$this->nbDays,null,true);
    }else if ($old->nbDays != $this->nbDays){//update
        $resultClass.=$this->updateLeftQOfLeaveEarned($this->idLeaveType,$this->idEmployee,$this->nbDays,(float)$old->nbDays,false, true);
    }else if ($old->idStatus!=$this->idStatus && $this->rejected == 1){//delete
        $resultClass.=$this->updateLeftQOfLeaveEarned($this->idLeaveType,$this->idEmployee,$this->nbDays,null,false, false, true);
    }else if ($old->idStatus!=$this->idStatus && $old->rejected==1 && $this->rejected == 0){//create
        $resultClass.=$this->updateLeftQOfLeaveEarned($this->idLeaveType,$this->idEmployee,$this->nbDays,null,true);
    }
    //if there was an error during the execution of updateLeftQOfLeaveEarned(), stop
    if($resultClass!=""){
        return $resultClass;
    }
    $resultClass = parent::save();
    if(strpos($resultClass,"OK")===false){
        return $resultClass;
    }
    
    //create the plannedWorks for the leave if ((rejected=0 and accepted=0) or submitted = 1
    if (($this->rejected==0 and $this->accepted==0 and $hasAcceptedLeave) OR $this->submitted==1) {
        $resultS = $this->createWorkOrPlannedWorkForLeave('PlannedWork');        
    } else { // delete plannedWorks
        $clause = "idLeave=$this->id";
        $pWork = new PlannedWork();
        $resultS = $pWork->purge($clause);
    }
    if(strpos($resultS, "OK")===false and strpos($resultS, "NO_CHANGE")===false){
        return $resultS;   
    }
    
    //create the works for the leave if 
    //   - accepted changed from 0 to 1
    // OR
    //   - workflow has'nt status with setAcceptedLeave = 1
    if ( ($old->accepted == 0 and $this->accepted == 1) or !$hasAcceptedLeave){
        $resultS = $this->createWorkOrPlannedWorkForLeave('Work');
    }

    if(strpos($resultS, "OK")===false and strpos($resultS, "NO_CHANGE")===false){
        return $resultS;   
    }
    
    //delete the works for the leave if the accepted changed from 1 to 0
    if ($this->accepted==0 and $old->accepted==1) {
        $clause = "idLeave=$this->id";
        $work = new Work();
        $resultS = $work->purge($clause);
    }

    if(strpos($resultS, "OK")===false and strpos($resultS, "NO_CHANGE")===false){
        return $resultS;   
    }
    
    // Send Notification or email and alert
    if (getLastOperationStatus($resultClass)=='OK') {
        $this->sendLeaveInfo($old);
    }
    
    return $resultClass;
  }
  
  
  /**
   * =========================================================================
   * control data corresponding to Model constraints, before deleting an object
   *
   * @param
   *          void
   * @return "OK" if controls are good or an error message
   *         must be redefined in the inherited class
   */
  public function deleteControl() {
    $result = "";
    
    // Employee can't delete leave that is submitted, rejected or accepted
    if ($this->idEmployee == getSessionUser()->id) {
        $leaveType = new LeaveType($this->idLeaveType);
        $workflow = new Workflow($leaveType->idWorkflow);
        $hasSubmittedLeave = $workflow->hasSetStatusOrLeave("setSubmittedLeave");
        
        if ($this->rejected==1 or $this->accepted==1 or ($this->submitted==1 and $hasSubmittedLeave)) {
            $result.='<br/>' . i18n('cantDeleteASubmittedRejectedAcceptedLeave');
        }
    }
    
    $defaultDeleteControl=parent::deleteControl();
    if ($defaultDeleteControl!='OK') {
        $result.=$defaultDeleteControl;
    }
    
    
    
    if ($result == "") {
      $result = 'OK';
    }
    
    return $result;
  }
  
  
  
  public function delete() {
    $result = "";
    //to update the column left of employeeLeaveEarned
    $result .= $this->updateLeftQOfLeaveEarned($this->idLeaveType,$this->idEmployee,$this->nbDays, null, false, false, true);
    //if there was an error during the execution of updateLeftQOfLeaveEarned(), stop
    if($result!=""){
        return $result;
    }
    
    $result .= parent::delete();

    //to delete all the works associated to this leave
    $work=new Work();
    $resultP=$work->purge("idLeave = ".$this->id);
    if(strpos($resultP, "OK")===false){
        return $resultP;   
    }
    //to delete all the planned works associated to this leave
    $pWork=new PlannedWork();
    $resultP=$pWork->purge("idLeave = ".$this->id);
    if(strpos($resultP, "OK")===false){
        return $resultP;   
    }

    return $result;
  }

 /* ========================================================================================
  * VALIDATION SCRIPT
    ======================================================================================== */   
  
   /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    //to set startDate and endDate at the creation of a new leave (this is not made in the __construct() as a call to getSqlElementsFromCriteria 
    //or to new Leave() seems to create an infinite loop in that function
    if($this->id==NULL){
        $firstOpenDay=$this->getFirstOpenDateAtCreation();
        $this->startDate = $firstOpenDay;
        $this->endDate = $firstOpenDay;
    }  
    $colScript = parent::getValidationScript($colName);
    if (!$this->idLeaveType) $this->idLeaveType=0;
    if (!$this->idStatus) $this->idStatus=0;
    if($colName=="idStatus"){//stuck here, dojo always reset the style after the style.display = none
        $colScript.='<script type="dojo/connect" event="onChange" >';
        $colScript .='var idRes = dijit.byId("idEmployee").value;';
        $colScript .='var idUser = '. getSessionUser()->id.';';        
        $colScript .='var nbDays = '. $this->nbDays.';';
        $colScript .='var idLeaveType = '. $this->idLeaveType.';';
        $colScript .='var idStatus = '. $this->idStatus.';';
        $colScript .='if (wfStatusArray.length===0) {';
        $colScript .='    '.$this->__workflowList;
        $colScript .='}';
        $colScript .= 'calculateNbRemainingDays("fromLeaveMain", idRes, idUser, nbDays, idStatus, idLeaveType);';
        $colScript .= '</script>';
    }
    
    if($colName=="startDate"){
        $colScript .='<script type="dojo/connect" event="onChange" >';
        $colScript .='var idRes = dijit.byId("idEmployee").value;';
        $colScript .='var idUser = '. getSessionUser()->id.';';        
        $colScript .='var nbDays = '. $this->nbDays.';';
        $colScript .='var idLeaveType = '. $this->idLeaveType.';';
        $colScript .='var idStatus = '. $this->idStatus.';';
        $colScript .='if (this.value > dijit.byId("endDate").value) {';
        $colScript .='    dijit.byId("endDate").setValue(this.value);';
        $colScript .='}';
        $colScript .="calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);";
        $colScript .='if (wfStatusArray.length===0) {';
        $colScript .='    '.$this->__workflowList;
        $colScript .='}';
        $colScript .= 'calculateNbRemainingDays("fromLeaveMain", idRes, idUser, nbDays, idStatus, idLeaveType);';
        $colScript .= '</script>';
    }
    
    if($colName=="endDate"){
        $colScript .='<script type="dojo/connect" event="onChange" >';
        $colScript .='var idRes = dijit.byId("idEmployee").value;';
        $colScript .='var idUser = '. getSessionUser()->id.';';        
        $colScript .='var nbDays = '. $this->nbDays.';';
        $colScript .='var idLeaveType = '. $this->idLeaveType.';';
        $colScript .='var idStatus = '. $this->idStatus.';';
        $colScript .='if (this.value < dijit.byId("startDate").value) {';
        $colScript .='    dijit.byId("startDate").setValue(this.value);';
        $colScript .='}';
        $colScript .="calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);";
        $colScript .='if (wfStatusArray.length===0) {';
        $colScript .='    '.$this->__workflowList;
        $colScript .='}';
        $colScript .= 'calculateNbRemainingDays("fromLeaveMain", idRes, idUser, nbDays, idStatus, idLeaveType);';
        $colScript .= '</script>';
    } 

    if($colName=="idEmployee"){
        $colScript .='<script type="dojo/connect" event="onChange" >';
        $colScript .='var idEmployee = this.value;';
        $colScript .='var idUser = '. getSessionUser()->id.';';        
        $colScript .='var nbDays = '. $this->nbDays.';';
        $colScript .='var idLeaveType = '. $this->idLeaveType.';';
        $colScript .='var idStatus = '. $this->idStatus.';';
        $colScript .="calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idEmployee, idUser);";
        $colScript .="getLeftByLeaveType(idEmployee);";
        $colScript .='if (wfStatusArray.length===0) {';
        $colScript .='    '.$this->__workflowList;
        $colScript .='}';
        $colScript .= 'calculateNbRemainingDays("fromLeaveMain", idEmployee, idUser, nbDays, idStatus, idLeaveType);';
        $colScript .= '</script>';
    } 

    if($colName=="idLeaveType"){
        $colScript .='<script type="dojo/connect" event="onChange" >';
        $colScript .='var idRes = dijit.byId("idEmployee").value;';
        $colScript .='var idUser = '. getSessionUser()->id.';';
        $colScript .='var nbDays = '. $this->nbDays.';';
        $colScript .='var idLeaveType = '. $this->idLeaveType.';';
        $colScript .='var idStatus = '. $this->idStatus.';';
        $colScript .='if (wfStatusArray.length===0) {';
        $colScript .='    '.$this->__workflowList;
        $colScript .='}';
        $colScript .='getWorkflowStatusesOfLeaveType("fromLeaveMain",dijit.byId("idLeaveType").value,idRes,idUser,nbDays,idStatus,idLeaveType);';
        $colScript .="calculateHalfDaysForLeave('startDate', 'endDate', 'startAM', 'startPM', 'endAM', 'endPM', 'nbDays', idRes, idUser);";
        $colScript .= 'calculateNbRemainingDays("fromLeaveMain", idRes, idUser, nbDays, idStatus, idLeaveType);';
        $colScript .= '</script>';
    } 

    return $colScript;
  }

 /* ========================================================================================
  * DRAW FUNCTIONS
    ======================================================================================== */   
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    $result="";
    
    switch($item) {
        case 'isOutOfWorkflowOrDesynchronized' :
            if ($this->statusOutOfWorkflow==0 and $this->statusSetLeaveChange==0) {return "";}
            $msg = ($this->statusOutOfWorkflow==1?"tooltipStatusOutOfWorkflow":"tooltipStatusSetLeaveChange");
            $result = '<div class="messageNotificationAlert" style="height:80px;" id="msgAlert" >';
            $result .= i18n($msg);
            $result .= '</div>';            
            break;
        case 'isJustifiable' :
            if (isJustifiableOfLvType($this->idEmployee, $this->idLeaveType)) {
                $result = '<div class="messageNotificationWarning" >';
                $result .= i18n('ThisLeaveMustBeJustified');
                $result .= '</div>';            
            } else {
                $result="";
            }
            break;
        case 'startAMPM' :
            $result=$this->drawStartAMPM($readOnly);
            break;
        case 'endAMPM' :
            $result=$this->drawEndAMPM($readOnly);
            break;
        case 'nbDaysRemaining':
            $result=$this->drawNbDaysRemaining();
            break;
        case 'status' :
            $result = $this->drawWorkflowStatus();
            break;
        case 'transition' :
            $result = $this->drawTransition();
            break;
    }    
     return $result;
  }
  
  public function drawWorkflowStatus() {      
    $onChange = 'changeStatusInMaintenanceOfLeave('.getSessionUser()->id.');';
    $statusList = $this->getWorkflowStatusesOfLeaveType();  
    
    $result  = '<tr class="detail generalRowClass">';
    $result .= '    <td>';
    $result .= '        <div style="width:0px;heigth:0px;" id="newIdStatus" name = "newIdStatus"';
    $result .= '             dojoType="dijit.form.TextBox" type="textbox" hidden';
    $result .= '             value=0';
    $result .= '        </div>';
    $result .= '    </td>';
    $result .= '</tr>';
    $result .= '<tr class="detail generalRowClass">';
    $result .= '    <td class="label" style="text-align:right;width:145px">';
    $result .= '        <label for="workflowStatus" class="generalColClass" style=";">'.i18n("changeStatus").'&nbsp;:&nbsp;';
    $result .= '        </label>';
    $result .= '    </td>';
    $result .= '    <td style="width:436px;">';
    $result .= '        <select data-dojo-type="dijit/form/ComboBox" id="workflowStatus" name="workflowStatus"';
    $result .= '                style="width:310px;" onchange="'.$onChange.'">';
    foreach ($statusList as $status) {
        $result .= '                <option '.($this->idStatus==$status->id?"selected":"").' value='.$status->id.'>'.$status->name.'</option>';
    }
    $result .= '        </select>';
    $result .= '    </td>';
    $result .= '</tr>';
      
      
      return $result;
  }
  
  public function drawTransition() {

        if ($this->statusOutOfWorkflow==1 or $this->statusSetLeaveChange==1) {
            $titleLeave = i18n("leave");
        } else {
            $titleLeave = "";
        }
    
        $result = '<table style="width:100%">';
        $result .= "    <tr>";
        if ($this->statusOutOfWorkflow==1 or $this->statusSetLeaveChange==1) {
            $result .= '        <th class="assignHeader" style="width:20%">'.i18n("transition").'</th>';
        } else {
            $result .= '        <th rowspan="2" class="assignHeader" style="width:20%">'.i18n("transition").'</th>';            
        }
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("colSubmitted").'</th>';
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("colAccepted").'</th>';
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("colRejected").'</th>';
        $result .= "    </tr>";

        $result .= "    <tr>";
        if ($this->statusOutOfWorkflow==1 or $this->statusSetLeaveChange==1) {
            $result .= '        <td class="assignHeader" style="width:20%">'.$titleLeave.'</td>';
        }
        $result .= '        <td class="linkData" style="text-align:center;">';
        $result .= '            <div id="_spe_submitted" name = "_spe_submitted" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" readonly';
        $result .=                   ($this->submitted==0?"":" checked").'>';
        $result .= '            </div>';
        $result .= '        </td>';
        $result .= '        <td class="linkData" style="text-align:center;">';
        $result .= '            <div id="_spe_accepted" name = "_spe_accepted" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" readonly';
        $result .=                   ($this->accepted==0?"":" checked").'>';
        $result .= '            </div>';
        $result .= '        </td>';
        $result .= '        <td class="linkData" style="text-align:center;">';
        $result .= '            <div id="_spe_rejected" name = "_spe_rejected" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" readonly';
        $result .=                   ($this->rejected==0?"":" checked").'>';
        $result .= '            </div>';
        $result .= '        </td>';
        $result .= "    </tr>";
        
        if ($this->statusOutOfWorkflow==1 or $this->statusSetLeaveChange==1) {
            $status = new Status($this->idStatus);
            $bgC="background-color:red !important;";
            $result .= "    <tr>";
            $result .= '        <td class="assignHeader" style="width:20%">'.i18n("colIdStatus")."</td>";
            $result .= '        <td id ="td_submittedS" class="linkData" style="text-align:center;'.($this->submitted!=$status->setSubmittedLeave?$bgC:"").'">';
            $result .= '            <div id="_spe_submittedS" name = "_spe_submittedS" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" readonly';
            $result .=                   ($status->setSubmittedLeave==0?"":" checked").'>';
            $result .= '            </div>';
            $result .= '        </td>';
            $result .= '        <td id ="td_acceptedS" class="linkData" style="text-align:center;'.($this->accepted!=$status->setAcceptedLeave?$bgC:"").'">';
            $result .= '            <div id="_spe_acceptedS" name = "_spe_acceptedS" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" readonly';
            $result .=                   ($status->setAcceptedLeave==0?"":" checked").'>';
            $result .= '            </div>';
            $result .= '        </td>';
            $result .= '        <td id ="td_rejectedS" class="linkData" style="text-align:center;'.($this->rejected!=$status->setRejectedLeave?$bgC:"").'">';
            $result .= '            <div id="_spe_rejectedS" name = "_spe_rejectedS" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" readonly';
            $result .=                   ($status->setRejectedLeave==0?"":" checked").'>';
            $result .= '            </div>';
            $result .= '        </td>';
            $result .= "    </tr>";
            
        }
        
        $result .= '</table>';
        
        return $result;
  }
  
  
  /**
   * to draw the spe startAMPM
   * @return string
   */ 
  public function drawStartAMPM($theReadOnly){
      if (!$theReadOnly) {  
        $readOnly = (strpos($this->getFieldAttributes("startAMPM"),"readonly")===false?"":"readonly");
    } else {
        $readOnly= "readonly";
    }    
    $idUser = getSessionUser()->id;
    //to check the checkboxes according to the values of startAMPM in the db
    $inputAM='<div style="display:inline-block;">';
    $inputPM='<div style="display:inline-block;">';
    $onChange="";
    if($this->idStatus!=NULL and ($this->submitted==1 or $this->rejected==1 or $this->accepted==1)){
        $inputAM.='<input data-dojo-type="dijit/form/CheckBox" id="startAM" name="startAM" readonly';
        $inputPM.='<input data-dojo-type="dijit/form/CheckBox" id="startPM" name="startPM" readonly';
    }else{
        $inputAM.='<input data-dojo-type="dijit/form/CheckBox" id="startAM" name="startAM" '.$readOnly.' onchange='.$onChange.'"changesStartAM('.$idUser.','.$this->nbDays.','.$this->idStatus.','.$this->idLeaveType.')" ';
        $inputPM.='<input data-dojo-type="dijit/form/CheckBox" id="startPM" name="startPM" '.$readOnly.' onchange='.$onChange.'"changesStartPM('.$idUser.','.$this->nbDays.','.$this->idStatus.','.$this->idLeaveType.')" ';
    }
    
    if(! $this->id){
        $inputAM.=' checked>';
        $inputPM.='>';
    }else{
        $crit= array('id'=>$this->id);
        //$lvResp = $this -> getSqlElementsFromCriteria ( $crit );
        //if($lvResp[0]->startAMPM=='AM'){
        if ($this->startAMPM=='AM') {
            $inputAM.=' checked>';
            $inputPM.='>';
        //}else if($lvResp[0]->startAMPM=='PM'){
        }else if($this->startAMPM=='PM'){
            $inputAM.='>';
            $inputPM.=' checked>';
        }
    }
    $result  = '<tr class="detail generalRowClass startAMPMClass"><td class="label" style="text-align:right;width:145px">'
            . '<label for="startAMPM" class="generalColClass startAMPMClass" style=";">'.i18n("StartAMPM").'&nbsp;:&nbsp;</label></td>';
    $result .= '<td style="width:436px;">';
    $result .=  $inputAM;   
    $result .= '<label style="width: 23px;" for="startAM">AM</label></div>';
    $result .=    $inputPM ;
    $result .= '<label style="width: 23px;" for="startPM">PM</label></div>';
    $result .='</td></tr>';
    return $result;
  }
  
  /**
   * to draw the spe startAMPM
   * @return string
   */ 
  public function drawEndAMPM($theReadOnly){
    if (!$theReadOnly) {  
        $readOnly = (strpos($this->getFieldAttributes("startAMPM"),"readonly")===false?"":"readonly");
    } else {
        $readOnly= "readonly";
    }    
    $idUser = getSessionUser()->id;
//    $inputAM= '<div style="display:inline-block;"><input data-dojo-type="dijit/form/CheckBox" id="endAM" name="endAM" onchange="changesEndAM('.$idUser.')" ';
//    $inputPM= '<div style="display:inline-block;"><input data-dojo-type="dijit/form/CheckBox" id="endPM" name="endPM" onchange="changesEndPM('.$idUser.')" ';

    $inputAM= '<div style="display:inline-block;"> ';
    $inputPM= '<div style="display:inline-block;"> ';

    $onChange="";
    if($this->idStatus!=NULL and ($this->submitted==1 or $this->rejected==1 or $this->accepted==1)){
        $inputAM.= '<input data-dojo-type="dijit/form/CheckBox" id="endAM" name="endAM" readonly';
        $inputPM.= '<input data-dojo-type="dijit/form/CheckBox" id="endPM" name="endPM" readonly';
    }else{
        $inputAM.= '<input data-dojo-type="dijit/form/CheckBox" id="endAM" name="endAM" '.$readOnly.' onchange='.$onChange.'"changesEndAM('.$idUser.','.$this->nbDays.','.$this->idStatus.','.$this->idLeaveType.')" ';
        $inputPM.= '<input data-dojo-type="dijit/form/CheckBox" id="endPM" name="endPM" '.$readOnly.' onchange='.$onChange.'"changesEndPM('.$idUser.','.$this->nbDays.','.$this->idStatus.','.$this->idLeaveType.')" ';
    }
    if($this->id==NULL){
        $inputAM.= '>';
        $inputPM.= ' checked>';
    }else{
        $crit= array('id'=>$this->id);
        //$lvResp = $this -> getSqlElementsFromCriteria ( $crit );
        //if($lvResp[0]->endAMPM=='AM'){
        if($this->endAMPM=='AM'){  
            $inputAM.= ' checked>';
            $inputPM.= '>';
        //}else if($lvResp[0]->endAMPM=='PM'){
        }else if($this->endAMPM=='PM'){
            $inputAM.= '>';
            $inputPM.= ' checked>';
        }
    } 
    $result  = '<tr class="detail generalRowClass endAMPMClass"><td class="label" style="text-align:right;width:145px">'
            . '<label for="endAMPM" class="generalColClass endAMPMClass" style=";">'.i18n("EndAMPM").'&nbsp;:&nbsp;</label></td>';
    $result .= '<td style="width:436px;">';
    $result .=  $inputAM;
    $result .= '<label style="width: 23px;" for="endAM">AM</label></div>';
    $result .=  $inputPM;
    $result .= '<label style="width: 23px;" for="endPM">PM</label></div>';
    $result .=  '</td></tr>';  
    
    return $result;
  }
  
  /**
   * to draw the spe nbDaysRemaining
   * @return string
   */ 
  public function drawNbDaysRemaining(){
    $value=$this->getRemainingDays(0, $this->idEmployee,$this->idLeaveType);
    if ($value==null) {
        $value="";
    }
    
    $display = "display:inline-block;";        
      
    $result='<tr class="detail generalRowClass nbDaysRemainingClass"><td class="label" style="text-align:right;width:145px">'
          . '<label for="nbDaysRemaining" class="generalColClass nbDaysRemaining" style=";">'.i18n("nbDaysRemaining").'&nbsp;:&nbsp;</label></td><td style="width:436px;">';
    $result .=  '<input data-dojo-type="dijit/form/TextBox" id="idNbDaysRemaining" style="'.$display.' width: 4.25em;text-align: right;" readonly value='.$value.'></td></tr>';
    return $result;
  }
  
 /* ========================================================================================
  * MISCELANIOUS FUNCTIONS
    ======================================================================================== */
  
    function getWorkflowStatusesOfLeaveType($idUser=null, $idEmployee=null, $idType=null) {
        if ($idUser===null) {
            $idUser = getSessionUser()->id;
        }
        if ($idEmployee===null) {
            $idEmployee=$this->idEmployee;
        }
        if ($idType===null) {
            $idType= $this->idLeaveType;
        }
        
        $isAll=false;
        if (isLeavesAdmin($idUser) or isManagerOfEmployee($idUser, $idEmployee)) {
            $isAll=true;
        }

        $theLeaveType = new LeaveType($idType);
        $theWorkflow = new Workflow($theLeaveType->idWorkflow);

        $lstStatus = $theWorkflow->getWorkflowstatus();
        $theStatusList = array();
        foreach($lstStatus as $status) {
            if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
                $theStatus = new Status($status->idStatusFrom);
                if (($isAll===false and $theStatus->setRejectedLeave==0 and $theStatus->setAcceptedLeave==0) or $isAll===true) {
                    $theStatusList[$status->idStatusFrom] = $theStatus;
                }
            }
            if (!array_key_exists($status->idStatusTo, $theStatusList)) {
                $theStatus = new Status($status->idStatusTo);
                if (($isAll===false and $theStatus->setRejectedLeave==0 and $theStatus->setAcceptedLeave==0) or $isAll===true) {
                    $theStatusList[$status->idStatusTo] = $theStatus;
                }
            }
        }
        usort($theStatusList, function($a, $b){return strcmp($a->sortOrder, $b->sortOrder);});            
        return $theStatusList;
    }
  
  
    /**
     * Get in array idProject, idActivity, idAssignment of this leave
     * @return Array[ idProject / idActivity / idAssignment ]
     */
    function getIdProjectIdActivityIdAssignmentOfThis() {
        $result = array();
        $result['idProject'] = Project::getLeaveProjectId();
        $lvType = new LeaveType($this->idLeaveType);
        $result['idActivity'] = $lvType->idActivity;
        $critRqAss=array(
            "idResource" => $this->idEmployee,
            "idProject" => $result['idProject'],
            "refType" => "Activity",
            "refId"=>$result['idActivity']
        );
        $result['idAssignment']=null;
        $ass = sqlElement::getSingleSqlElementFromCriteria("Assignment", $critRqAss);
        if (isset($ass->id)) {
            $result['idAssignment']=$ass->id;
        }
        return $result;
    }
  
    /**
     * Create work or planned work (depending of parameter) for this leave
     * @param string $workOrPlannedWork : 'Work' or 'PlannedWork'
     */
    private function createWorkOrPlannedWorkForLeave($workOrPlannedWork) {
        $workOrPWork=new $workOrPlannedWork();
        $clause = "idLeave=$this->id";
        $workOrPWork->purge($clause);
        
        $resultS = "OK";
    
        $pjLeaveId = Project::getLeaveProjectId();
        $lvType = new LeaveType($this->idLeaveType);
        $lvActId = $lvType->idActivity;
        $critRqAss=array(
            "idResource" => $this->idEmployee,
            "idProject" => $pjLeaveId,
            "refType" => "Activity",
            "refId"=>$lvActId
        );
        $ass = new Assignment();
        $assRq = $ass->getSqlElementsFromCriteria($critRqAss);
        
        $startDateTime = new Datetime($this->startDate);
        $endDateTime = new Datetime($this->endDate);
        $itDateTime = clone($startDateTime);
        $resEmp = new Resource($this->idEmployee);
        while($itDateTime<=$endDateTime){
          //gautier #4371
          if(isOffDay($itDateTime->format('Y-m-d'),$resEmp->idCalendarDefinition)!=1){
            $workOrPWork=new $workOrPlannedWork();
            if($itDateTime->format('Y-m-d')==$startDateTime->format('Y-m-d') and $this->startAMPM=="PM"){
              $workOrPWork->work=0.5;
            }else if($itDateTime->format('Y-m-d')==$endDateTime->format('Y-m-d') and $this->endAMPM=="AM"){
              $workOrPWork->work=0.5;
            }else{
              $workOrPWork->work=1.0;
            }
            $workOrPWork->idResource=$this->idEmployee;
            $workOrPWork->idProject=$pjLeaveId;
            $workOrPWork->refType="Activity";
            $workOrPWork->refId=$lvActId;
            $workOrPWork->idAssignment=$assRq[0]->id;
            $workOrPWork->setDates($itDateTime->format('Y-m-d'));
            $workOrPWork->idLeave = $this->id;
            $resultS=$workOrPWork->simpleSave();
            if(strpos($resultS, "OK")===false){
                return $resultS;   
            }
          }
          $itDateTime->add(new DateInterval('P1D'));            
        }
        return $resultS;
    }
      
    /**
    * return a list of the leaveEarned of this leave
    * @param int idLeaveType
    * @param int idEmployee
    * @param boolean $reverse if true, Order by startDate DESC, else by startDate ASC
    * @return Array[EmployeeLeaveEarned] 
    */
    protected function getEmpLeaveEarnedListOfThisLeave($idLeaveType,$idEmployee,$reverse=false) {
      $clauseWhere="idLeaveType=".$idLeaveType." AND idEmployee=".$idEmployee." AND idle=0";
      if($reverse){
          $clauseOrderBy="startDate DESC";
      }else{
          $clauseOrderBy="startDate ASC";
      }
      $empLE=new EmployeeLeaveEarned();
      $empLEList = $empLE->getSqlElementsFromCriteria(null, false,$clauseWhere,$clauseOrderBy);
      return $empLEList;
    }

    /**
     * calculate the attribute nbDays of this leave
     */
    public function calculateNbDays($fromCalendarDefinition=false){
        $nbEffectiveLeaveDays=0;
        if ($this->idEmployee == getSessionUser()->id) { 
            $idCalDef = getSessionUser()->idCalendarDefinition;
        } else {
            $empl = new Resource($this->idEmployee);
            $idCalDef = $empl->idCalendarDefinition;
        }
        $cal=new Calendar();
        $cal->setDates(date('Y').'-01-01');
        $arrayOffDays=explode("#", $cal->getOffDayList($idCalDef) );
        $arrayExceptionnalWorkDays=explode("#", $cal->getWorkDayList($idCalDef) );
        $arrayOffDaysOfTheWeek = $this -> arrayOfOffDaysOfTheWeek($fromCalendarDefinition);
        $startDay=new DateTime($this->startDate);
        $currentDay=new DateTime($this->startDate);
        $lastDay=new DateTime($this->endDate);
        
        while($currentDay <= $lastDay){
            if( ($arrayOffDaysOfTheWeek[ date_format($currentDay,'N') ] === 0 && in_array($currentDay->format('Ymd'), $arrayOffDays)==FALSE) || in_array($currentDay->format('Ymd'), $arrayExceptionnalWorkDays)==TRUE){
                if($this->startAMPM=="PM" && $this->endAMPM=="AM" && $startDay==$lastDay){
                    //pass
                }else if($this->startAMPM=="PM" && $startDay==$currentDay){
                    $nbEffectiveLeaveDays+=0.5;
                }else if($this->endAMPM=="AM" && $lastDay==$currentDay){
                    $nbEffectiveLeaveDays+=0.5;
                }else{
                    $nbEffectiveLeaveDays+=1;
                }
            }
            
            $currentDay->add(new DateInterval('P1D'));
        }
        $this->nbDays=$nbEffectiveLeaveDays;
 }
  
/**
 * ajout eliott: function which return an array containing the days of the week which are an offDay in general, 
 * the first day of the string is sunday, if there is a 1, this day is an offDay, used in save() to calculate nbDays
 * @return array
 */
  public function arrayOfOffDaysOfTheWeek($directFromDatabase=false){
    $isOffDay=array();
    if ($directFromDatabase) {
        $in = "('OpenDayMonday', 'OpenDayTuesday', 'OpenDayWednesday', 'OpenDayThursday', 'OpenDayFriday', 'OpenDaySaturday', 'OpenDaySunday')";
        $p=new Parameter();
        $crit=" (idUser IS NULL AND idProject IS NULL AND parameterCode IN $in)";
        $lst=$p->getSqlElementsFromCriteria(null, false, $crit);
        foreach ($lst as $param) {
            if ($param->parameterCode=='OpenDayMonday') {
                $isOffDay['1'] = ($param->parameterValue=="offDays"?1:0);
            } elseif ($param->parameterCode=='OpenDayTuesday') {
                $isOffDay['2'] = ($param->parameterValue=="offDays"?1:0);                
            } elseif ($param->parameterCode=='OpenDayWednesday') {
                $isOffDay['3'] = ($param->parameterValue=="offDays"?1:0);                
            } elseif ($param->parameterCode=='OpenDayThursday') {
                $isOffDay['4'] = ($param->parameterValue=="offDays"?1:0);                
            } elseif ($param->parameterCode=='OpenDayFriday') {
                $isOffDay['5'] = ($param->parameterValue=="offDays"?1:0);                
            } elseif ($param->parameterCode=='OpenDaySaturday') {
                $isOffDay['6'] = ($param->parameterValue=="offDays"?1:0);                
            } elseif ($param->parameterCode=='OpenDaySunday') {
                $isOffDay['7'] = ($param->parameterValue=="offDays"?1:0);                
            }
        }
    } else {
        $isOffDay['1']=(Parameter::getGlobalParameter('OpenDayMonday')=="offDays"?1:0);
        $isOffDay['2']=(Parameter::getGlobalParameter('OpenDayTuesday')=="offDays"?1:0);
        $isOffDay['3']=(Parameter::getGlobalParameter('OpenDayWednesday')=="offDays"?1:0);
        $isOffDay['4']=(Parameter::getGlobalParameter('OpenDayThursday')=="offDays"?1:0);
        $isOffDay['5']=(Parameter::getGlobalParameter('OpenDayFriday')=="offDays"?1:0);
        $isOffDay['6']=(Parameter::getGlobalParameter('OpenDaySaturday')=="offDays"?1:0);
        $isOffDay['7']=(Parameter::getGlobalParameter('OpenDaySunday')=="offDays"?1:0);
    }
    return $isOffDay;
  }
  
  
  /**
   * return an array of the query to know the quantity of the leavetype of the leave
   * 
   * @return array  an array containing an object employeeleaveearned if idLeaveType is set, else an array containing the string "KO"
   */
  public function getQuantityStartDateEndDateOfLeaveType(){
      if($this->idLeaveType==NULL){
          return array("KO");
      }
      //if it's a new leave,
      if($this->id==NULL){
          $crit= array("idLeaveType" => $this->idLeaveType, "idEmployee" => getSessionUser () -> id);
      }else{//if the leave was already created
        $crit= array("idLeaveType" => $this->idLeaveType, "idEmployee" => $this->idEmployee);
      }
      $empLE= new EmployeeLeaveEarned();
      $critQuantityStartEndDateList= $empLE->getSqlElementsFromCriteria($crit);
      return $critQuantityStartEndDateList;      
  }
  
  
  /**
   * return the number of (left) days remaining for a leave of a given leavetype
   * @param type $idle
   * @param type $idEmployee
   * @param type $idLeaveType
   * @return int/string: if the leavetype has a quantity then the number returned is the number of days remaining, else it's ""
   */
  public function getRemainingDays($idle=0,$idEmployee=null,$idLeaveType=null){        
      if ($idEmployee==null or $idLeaveType==null) {return "";}
      
      $emp = new Employee($idEmployee);
      $leftList = $emp->getLeftLeavesByLeaveType($idLeaveType);
      if ($leftList) {
          return $leftList[$idLeaveType];
      } else {
          return "";
      }
  }
  
  
  /**
   * return a string containing a date (format Y-m-d) which is the first open day that doesn't overlap with the dates of already existing leaves 
   * to prefill the attributes startDate and endDate at the creation of a new leave
   * 
   * @return string a string containing the first open date available at the creation of a leave (id=null) 
   */
  public function getFirstOpenDateAtCreation(){
    $today=(new DateTime())->format('Y-m-d'); 
    
    //an array containing all the exceptionnal offdays of the current year
    $arrayOffDays=explode("#", Calendar::getOffDayList(getSessionUser()->idCalendarDefinition) );
    
    //an array containing the seven of the week and if each one is an openDay or OffDay
    $arrayOffDaysOfTheWeek = $this -> arrayOfOffDaysOfTheWeek();

    //a request to get all the leaves the employee asking for a new leave has already taken with a date superior than the date the leave was created
    $clauseWhereSelectLvs = "idEmployee= ".$this->idEmployee." AND (startDate >='$today' OR endDate >= '$today')";
    $clauseOrderByAscDates = "startDate, endDate ASC";
    $lvList=$this->getSqlElementsFromCriteria(null, false, $clauseWhereSelectLvs, $clauseOrderByAscDates);
    
    //the iterator for the new date
    $current=(new DateTime($today));
    
    //the condition for the while
    $cond=false;
    //two iterators to iterate in $lvList
    $iterator=0;
    $oldIt=0;
    $len = count($lvList);


    //while an appropriate date was not found
    while(! $cond){
        //if there are still leaves in $lvList
        
        if($iterator< $len){
          //if $current is between the dates of the leave then it becomes the endDate of the leave +1 d
          $lvStart = new DateTime($lvList[$iterator]->startDate);
          $lvEnd = new DateTime($lvList[$iterator]->endDate);
        
          if( $lvStart <= $current and $lvEnd >= $current){

              if($iterator<$len-1){
                  $lvStartNext = new DateTime($lvList[$iterator+1]->startDate);
                  $lvEndNext = new DateTime($lvList[$iterator+1]->endDate);
                  //to increment the iterator of one if there is two leaves on the same days like in this example : (15/06/18 to 18/06/18 endAMPM : AM and 18/06/18 to 18/06/18 startAMPM = endAMPM = PM)
                  if(($lvStart <= $current and $lvEnd >= $current && $lvEnd==$lvStartNext && $lvStartNext==$lvEndNext)){
                      $iterator++;
                  }
                    $current=(new DateTime($lvList[$iterator]->endDate))->add(new DateInterval('P1D'));
              }else{
                  $current=(new DateTime($lvList[$iterator]->endDate))->add(new DateInterval('P1D'));
              }
            $oldIt=$iterator;
            $iterator++;
            }
        }
        //if current was modified in this iteration then iterate once more to be able to test 
        //if there is two consecutives leaves in $lvList 
        if($oldIt!=$iterator){
            $oldIt=$iterator;
        }else{//else if $current was not modified in this iteration yet then            
            //if current is not an offday (week end) and not an exceptionnal off day then current is the first appropriate open day and the while can end
            if( $arrayOffDaysOfTheWeek[ date_format($current,'N') ] === 0 and in_array($current->format('Ymd'), $arrayOffDays)===FALSE){
                $cond = true;
            }else{//else $current +1 d
                $current=$current->add(new DateInterval('P1D'));
            }
        }
    }
    
    return $current->format("Y-m-d");
  }
  
  /**
   * to update the leftQuantity attributes of the LeaveEarned linked to this leave (called in save and delete of LeaveMain)
   * @param int $idLeaveType
   * @param int $idEmployee
   * @param int $nbDays
   * @param int $oldDays (needed if $update == true)
   * @param bool $create
   * @param bool $update
   * @param bool $delete
   * @return string 
   */
  function updateLeftQOfLeaveEarned($idLeaveType,$idEmployee,$nbDays,$oldDays=null,$create=false,$update=false,$delete=false){
      //if $oldDays isn't set and $update==true, stop
      $returnErrorPrefix="<b>".i18n("messageInvalidControls")."</b><br/>";
      $returnErrorHiddenPart = '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
      $returnErrorHiddenPart .= '<input type="hidden" id="lastOperation" value="save" />';
      $returnErrorHiddenPart .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
      
      $returnErrorCantTakeMoreLeaves = $returnErrorPrefix . '<br/><b>' . i18n('ErrorCantTakeMoreLeaves') . '</b><br/>' . $returnErrorHiddenPart;
      
      if(($update==true) && $oldDays==null){
          return $returnErrorPrefix . '<br/><b>' . i18n('errorMissingOldDaysInUpdateLeftInLeaveMain') . '</b><br/>' . $returnErrorHiddenPart;
    //error
      }
      
      //to test if there is only one mode set to true
      $testModeArray=array(
                        "create"=>"$create",
                        "update"=>"$update",
                        "delete"=>"$delete"
                      );
      if( array_count_values($testModeArray)["1"]!=1 ){
          return $returnErrorPrefix . '<br/><b>' . i18n('errorModeUpdateLeftInLeaveMain') . '</b><br/>' . $returnErrorHiddenPart;
      }
      
      $empLEList = $this->getEmpLeaveEarnedListOfThisLeave($idLeaveType,$idEmployee);
      
      if(! $empLEList){
          return $returnErrorPrefix . '<br/><b>' . i18n('ErrorThisEmployeeDoesntHaveLeaveEarned') . '</b><br/>' . $returnErrorHiddenPart;
      }
      //if there is no quantity, the employee can ask as many leaves as he wants, 
      //so there is no need to modify leftQuantity 
      if($empLEList[0]->quantity==null){
          return "";
      }
      
      $nbAllowedAnticipated = getNbAnticipatedAllowedFormEmployeeAndLeaveType($this->idEmployee, $this->idLeaveType);
      
      //if there is only one limited leaveEarned
      if(count($empLEList)==1){
          if ($create==true){
              $leftDays = $this->getRemainingDays(0,$this->idEmployee, $this->idLeaveType);
              if ($leftDays<=0 and $nbAllowedAnticipated<=0) {
                  return $returnErrorCantTakeMoreLeaves;                  
              }
              $nDaysToSubstract=$empLEList[0]->leftQuantity - $nbDays;
              //if there is not enough quantity
//              if ($nDaysToSubstract < 0 && !$isAnticipated)){
              if ($nDaysToSubstract < 0 && $nbAllowedAnticipated < abs($nDaysToSubstract)){
                  return $returnErrorCantTakeMoreLeaves;
              }
//              if ( ($nDaysToSubstract >= 0) || ($isAnticipated && $nDaysToSubstract < 0) ){
              if ( ($nDaysToSubstract >= 0) || ($nbAllowedAnticipated >= abs($nDaysToSubstract)) ){
                  $empLEList[0]->leftQuantity=$nDaysToSubstract;
                  $resultS=$empLEList[0]->simpleSave();
                  if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                    return $resultS;   
                  }
              }
              
          } else if($update==true){
              //$diff=$empLEList[0]->leftQuantity - ($nbDays - $oldDays);
              $diffNbDays = ($nbDays - $oldDays);
              if($diffNbDays==0){
                  
              } else if($diffNbDays<0){
                  $diff = min(-$diffNbDays, $empLEList[0]->quantity - $empLEList[0]->leftQuantity);
                  $empLEList[0]->leftQuantity=$empLEList[0]->leftQuantity+$diff;
                  $resultS=$empLEList[0]->simpleSave();
                  if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                    return $resultS;   
                  }
              } else if($diffNbDays>0){
                  $diff = $empLEList[0]->leftQuantity - $diffNbDays;
//                  if(!$isAnticipated && $diff < 0){
                  if($nbAllowedAnticipated < abs($diff) && $diff < 0){
                      return $returnErrorCantTakeMoreLeaves;
                  }
//                  if( ($diff >= 0) || ($isAnticipated && $diff < 0) ){
                  if( ($diff >= 0) || ($nbAllowedAnticipated >= abs($diff)) ){
                      $empLEList[0]->leftQuantity=$diff;
                      $resultS=$empLEList[0]->simpleSave();
                      if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                        return $resultS;   
                      }
                  }
              }
              
              
          } else if($delete==true){
              $empLEList[0]->leftQuantity=$empLEList[0]->leftQuantity + min($nbDays,$empLEList[0]->quantity-$empLEList[0]->leftQuantity);
              $resultS=$empLEList[0]->simpleSave();
              if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                return $resultS;
              }
          }
      
    //if $empLEList is composed of more than one leaveEarned
      } else {
          $empLEListLength = count($empLEList);
          $nbDaysIterator=$nbDays;
          $totalQuantityLeft=0;
          foreach($empLEList as $lvEarned){
              $totalQuantityLeft+=$lvEarned->leftQuantity;
          } 
          
          if($create==true){
              $empLEList = $this->getEmpLeaveEarnedListOfThisLeave($idLeaveType,$idEmployee);              
//              if($totalQuantityLeft < $nbDays && !$isAnticipated){
              if($totalQuantityLeft < $nbDays && ($nbAllowedAnticipated < abs($nbDays-$totalQuantityLeft))){
                  return $returnErrorCantTakeMoreLeaves;
              }
              for($i=0; $i < $empLEListLength-1; $i++){
                  if($nbDaysIterator==0){
                      break;
                  }else{
                      $decrement=min($empLEList[$i]->leftQuantity,$nbDaysIterator);
                      $empLEList[$i]->leftQuantity = $empLEList[$i]->leftQuantity - $decrement;
                      $nbDaysIterator=$nbDaysIterator-$decrement;
                      $resultS=$empLEList[$i]->simpleSave();
                      if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                        return $resultS;
                      }
                      //error
                  }
              }
              if($nbDaysIterator>0){
                $decrement=$empLEList[$empLEListLength-1]->leftQuantity - $nbDaysIterator;
                //error decrement < 0 and not isAnticipated ?
                $empLEList[$empLEListLength-1]->leftQuantity=$empLEList[$empLEListLength-1]->leftQuantity-$nbDaysIterator;
                $resultS=$empLEList[$empLEListLength-1]->simpleSave();
                if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                  return $resultS;   
                }
                
              }
          }
          
          if($update==true){
              $diff = $nbDays - $oldDays;
              if($diff==0){ 
                  //pass
              } else if($diff>0){
//                  if($totalQuantityLeft < $diff && !$isAnticipated){
                  if($totalQuantityLeft < $diff && ($nbAllowedAnticipated < abs($diff-$totalQuantityLeft))){
                    return $returnErrorCantTakeMoreLeaves;
                  }
                  $empLEList = $this->getEmpLeaveEarnedListOfThisLeave($idLeaveType,$idEmployee);
                  for($i=0; $i < $empLEListLength-1; $i++){
                    if($diff==0){
                        break;
                    }else{
                        $decrement=min($diff,$empLEList[$i]->leftQuantity);
                        $empLEList[$i]->leftQuantity=$empLEList[$i]->leftQuantity-$decrement;
                        $diff=$diff-$decrement;
                        $resultS=$empLEList[$i]->simpleSave();
                        if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                          return $resultS;
                        }
                        
                    }
                }
                if($diff!=0){
                    $empLEList[$empLEListLength-1]->leftQuantity = $empLEList[$empLEListLength-1]->leftQuantity - $diff;
                    $resultS=$empLEList[$empLEListLength-1]->simpleSave();
                    if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                      return $resultS;   
                    }
                    
                }
              }else if($diff<0){
                  $empLEList = $this->getEmpLeaveEarnedListOfThisLeave($idLeaveType,$idEmployee,true);
                  for($i=0; $i < $empLEListLength; $i++){
                    if($diff==0){
                        break;
                    }else{
                        $increment = min(-$diff,$empLEList[$i]->quantity-$empLEList[$i]->leftQuantity);
                        $empLEList[$i]->leftQuantity=$empLEList[$i]->leftQuantity + $increment;
                        $diff=$diff + $increment;
                       $resultS=$empLEList[$i]->simpleSave();
                       if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                         return $resultS;   
                       }
                    }
                  } 
              }
              
          }
          
          if($delete==true){
              $nbDaysToAdd = $nbDays;
              $empLEList = $this->getEmpLeaveEarnedListOfThisLeave($idLeaveType,$idEmployee,true);
              $empLEListLength=count($empLEList);
              $nbDaysToAdd=$nbDays;
              for($i=0; $i < $empLEListLength; $i++){
                  if($nbDaysToAdd==0){
                      break;
                  }else{
                      $increment=min($empLEList[$i]->quantity - $empLEList[$i]->leftQuantity, $nbDaysToAdd);
                      $empLEList[$i]->leftQuantity = $empLEList[$i]->leftQuantity + $increment;
                      $nbDaysToAdd = $nbDaysToAdd - $increment;
                      $resultS=$empLEList[$i]->simpleSave();
                      if(strpos($resultS, "OK")===false && strpos($resultS, "NO_CHANGE")===false){
                        return $resultS;
                      }
                  }
              }
          }
          
      }
      return "";
  }  
//fin ajout

  /**
   * Determine if information on leave is to send.
   * @param string $action : Action doing on the leave. Avalaible values are : OnCreate, OnUpdate, OnDelete, OnTreatment ie change of status)
   * @param string $typeOfSend : Way of sending. Avalaible values are : notification, email, alert
   * @param string $typeOfReceiver : Type of receiver. Avalaible values are : A = LeaveAdministrator - M = Manager of employee - E = Employee
   * @return boolean : True if information on leave is to send by the typeOfSend, for the action and the type of receiver
   */
  private function isInfoOnLeaveToSend($action=null, $typeOfSend=null, $typeOfReceiver=null) {
      $theLeaveType = new LeaveType($this->idLeaveType);
      if ($action==null or $typeOfReceiver==null or $typeOfSend==null) { return false; }
      $theField = $typeOfSend.$action;
      if (strpos($theLeaveType->$theField, $typeOfReceiver)===false) {return false; }
      return true;
  }
  
  /**
   * Send information on leave by notification, alert, email in function of the type of leave
   * @param Leave $old : Old values of the leave
   * @param boolean $delete : True if deleting of leave
   * @return nothing
   */
  private function sendLeaveInfo($old,$delete=false) {
      
    if ($this->submitted==0 and $this->rejected==0 and $this->submitted==0) {
        return;
    }  
      
    // RECEIVERS AND REQUESTER
    $receivers = array();
    $requester = null;
    // Get employee's leave
    $employee = new Employee($this->idEmployee);
    // Get manager of this employee's leave
    $manager = $employee->getActivManager();
    // Get leaves admin
    $leavesAdmin = getLeavesAdmin();
    // Determine the receiver
    if ($this->idUser == $this->idEmployee) { // Creator = Employee
        // receiver = manager
        $manager->__Ame = "M";
        array_push($receivers, $manager);
        // requester = Employee
        $requester = $employee;
        $who = i18n("Employee");
    } elseif ($this->idUser == $manager->id) { // Creator = Manager
        // receiver = Employee
        $employee->__Ame = "E";
        array_push($receivers, $employee);
        // requester = Manager
        $requester = $manager;            
        $who = i18n("Manager");
    } elseif ($this->idUser == $leavesAdmin->id) { // Creator = Leaves Admin (that is'nt the actual employee's manager)
        // receivers = Employee - Manager
        $manager->__Ame = "M";
        array_push($receivers, $manager);
        $employee->__Ame = "E";
        array_push($receivers, $employee);
        // requester = Leaves admin
        $requester = $leavesAdmin;            
        $who = i18n("leavesAdmin");
    } else { // Something goes wrong : Leave requester is employee or it's manager or the leave admin
        // receivers = Employee - Manager - Leaves admin
        $manager->__Ame = "M";
        array_push($receivers, $manager);
        $employee->__Ame = "E";
        array_push($receivers, $employee);
        $leavesAdmin->__Ame = "M";
        array_push($receivers, $leavesAdmin);            
        // requester = the connected user
        $requester = getSessionUser();     
        $who = i18n("User");
    }
    
    // Don't send Notification or email or alert to him self
    foreach($receivers as $key => $receiver) {
        if ($receiver->id == $this->idUser) {
            unset($receivers[$key]);
        }
    }
    
    if (empty($receivers)) {
        return;
    }

    $newLeaveType = new LeaveType($this->idLeaveType);

    // CAUSE
    $cause = "";
    $action = "";
    // Determine cause of save
    if ($delete) {
        $action = "OnDelete";
        $cause = i18n("Deleted");
    } elseif ($old->id==null) { // New Leave
        if ($this->submitted==1) {
            $action = "OnCreate";
            $cause = i18n("Requested");
        }
    } else {
        if ($old->idStatus != $this->idStatus AND $this->rejected==1 AND $old->rejected==0) {
            $action = "OnTreatment";
            $cause = i18n("Rejected");
        }
        if ($old->idStatus != $this->idStatus AND $this->accepted==1 AND $old->accepted==0) {
            $action = "OnTreatment";
            $cause = i18n("colAccepted");
        } 
        if ($old->idStatus != $this->idStatus AND $this->submitted==1 AND $old->submitted==0) {
            $action = "OnTreatment";
            $cause = i18n("colSubmitted");
        } 
        if (($old->rejected==1 or $old->submitted==1 or $old->accepted==1) and 
            $this->rejected==0 and $old->submitted==0 and $old->accepted==0) {
            $action = "OnTreatment";
            $cause .= ($cause==""?"":" ".i18n("AND")." ").i18n("Reopened");
        }
        if ($old->idLeaveType != $this->idLeaveType and $old->idLeaveType!=null) {
            $oldLeaveType = new LeaveType($old->idLeaveType);
            $cause .= ($cause==""?"":" ".i18n("AND")." ").i18n("changeLeaveType")." ".i18n("from")." ".$oldLeaveType->name." ".i18n("to")." ".$newLeaveType->name;
        }
        if ($old->startDate != $this->startDate or
                   $old->startAMPM != $this->startAMPM or
                   $old->endDate != $this->endDate or
                   $old->endAMPM != $this->endAMPM
                 ) {
            $action = "OnUpdate";
            $cause .= ($cause==""?"":" ".i18n("AND")." ").i18n("changeLeaveDate")." ";
            $cause .= i18n("old")." ".i18n("startDate"). " = ". $old->startDate. " ".$old->startAMPM." ";
            $cause .= i18n("endDate"). " = ". $old->endDate. " ".$old->endAMPM." ";
            $cause .= i18n("new")." ".i18n("startDate"). " = ". $this->startDate. " ".$this->startAMPM." ";
            $cause .= i18n("endDate"). " = ". $this->endDate. " ".$this->endAMPM;
        }
        if ($old->idEmployee != $this->idEmployee) {
            $action = "OnUpdate";
            $cause .= ($cause==""?"":" ".i18n("AND")." ").i18n("changeLeaveEmployee");
        }
    }

    if ($cause=="") {
        return;
    }
    
    if (isNotificationSystemActiv()) {
        // NOTIFICATIONS
        $menu = SqlElement::getFirstSqlElementFromCriteria("Menu", array("name" => "menuLeave"));
        if (!isset($menu->id)) {
            $idMenu = null;
        } else {
            $idMenu = $menu->id;
        }
        $notifType = SqlElement::getFirstSqlElementFromCriteria("Type", array("name" => "INFO", "scope" => "Notification"));
        if (!isset($notifType->id)) {
            $notifType = SqlElement::getFirstSqlElementFromCriteria("Type", array("scope" => "Notification"));
        }
        if (isset($notifType->id)) {
            $idNotifType = $notifType->id;
        } else {
            $idNotifType = null;
        }
        $notifiable = SqlElement::getFirstSqlElementFromCriteria("Notifiable", array("notifiableItem" => "Leave"));
        if (isset($notifiable->id)) {
            $idNotifiable = $notifiable->id;
        } else {
            $idNotifiable = null;
        }
        // Prepare notification values
        $notif = new Notification();
        $notif->idResource = $this->idUser;
        $notif->idNotifiable = $idNotifiable;
        $notif->notifiedObjectId = $this->id;
        $notif->idNotificationDefinition=null;
        $notif->idMenu=$idMenu;
        $notifDate = new DateTime();
        $notif->notificationDate = $notifDate->format("Y-m-d");
        $notif->notificationTime = $notifDate->format("H:i:s");
        $notif->idNotificationType = $idNotifType;
        if ($delete or $old->id==null) {
            $notif->name = i18n("leave")." #".$this->id." ".i18n("is")." ".$cause." - ".$newLeaveType->name;
            $content = i18n("startDate"). " = ". $this->startDate. " ".$this->startAMPM." ";
            $content .= i18n("endDate"). " = ". $this->endDate. " ".$this->endAMPM;
            $notif->content = $newLeaveType->name." ".$content." ".$this->comment;
        } else {
            $notif->name = $newLeaveType->name." #".$this->id." ".i18n("is")." ".i18n("changed")." ".i18n("by")." ".$requester->userName;
            $notif->content = $cause;
        }
        $notif->title = i18n("informations")." ".i18n("FOR")." ".i18n("leave")." #".$this->id;
        $notif->sendEmail = 0;
        $notif->idle=0;
    } 
    
    // For each receivers
    foreach($receivers as $key => $receiver) {
        $user = new User($receiver->id);
        // NOTIFICATIONS
        if ($this->isInfoOnLeaveToSend($action,'notification',$receiver->__Ame)==true) {
            // Must be set before access right
            $readAllowed=false;
            if (isNotificationSystemActiv()) {
                $notif->idUser = $receiver->id;
                // Get access to menu Notification for receiver
                $readAllowed = (securityGetAccessRightYesNo("menuNotification", "read", $notif, $user)=="YES");
            }
            // Notification allowed and Notification system activ
            if (($readAllowed) and isNotificationSystemActiv()) {
                // Send Notification
                $notif->id=null;
                $notif->simpleSave();
            }
        }
        
        // ALERT
        if ($this->isInfoOnLeaveToSend($action,'alert',$receiver->__Ame)==true) {
            // Must be set before access right
            $alert=new Alert();
            $alert->idUser=$receiver->id;
            // Get access to menu Alert
            $readAllowed = (securityGetAccessRightYesNo("menuAlert", "read", $alert, $user)=="YES");
            // Alert allowed
            if ($readAllowed) {
                // Emit alert
                if ($delete or $old->id==null) {
                    $title = i18n("leave")." ".i18n("is")." ".$cause." - ".$newLeaveType->name. " ". i18n("FOR")." ".$requester->name;
                    $message = i18n("by")." ".$user->name." ".i18n("comment")." : ".$this->comment;
                } else {
                    $title = i18n("leave")." ".i18n("is")." ".i18n("changed")." ".i18n("FOR")." ".$requester->name;
                    $message = $cause;
                }
                $theDate = new DateTime();
                
                $alert=new Alert();
                $alert->idUser=$receiver->id;
                $alert->alertType=htmlspecialchars("INFO",ENT_QUOTES,'UTF-8');
                $alert->alertInitialDateTime=$theDate->format("Y-m-d H:i:s");
                $alert->alertDateTime=$theDate->format("Y-m-d H:i:s");
                $alert->title=htmlspecialchars($title,ENT_QUOTES,'UTF-8');
                $alert->message=htmlspecialchars($message,ENT_QUOTES,'UTF-8');
                $alert->simpleSave();
            }
        }

        // EMAIL
        if ($this->isInfoOnLeaveToSend($action,'email',$receiver->__Ame)==true) {
            // If receiver as email => send email
            if ($receiver->email!=null) {
                if ($delete or $old->id==null) {
                    $subject = i18n("leave")." ".i18n("is")." ".$cause." - ".$newLeaveType->name." ".i18n("FOR")." ".$requester->name;
                    $messageBody = i18n("by")." ".$user->name." ".i18n("comment")." : ".$this->comment;
                } else {
                    $subject = i18n("leave")." ".i18n("is")." ".i18n("changed")." ".i18n("FOR")." ".$requester->name;
                    $messageBody = $cause;
                }
                sendMail($receiver->email, $subject, $messageBody);
            }
        }
    }    
  }  
}
?>
