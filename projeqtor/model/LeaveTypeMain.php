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

/* ============================================================================
 * LeaveType defines the type of a leave.
 */  
require_once('_securityCheck.php');

class LeaveTypeMain extends SqlElement {
    
    public $_sec_Description;
        public $id;
        public $name;
        public $color;
        public $idActivity;
        public $sortOrder=0;
    
    public $_sec_treatment;
        public $idWorkflow;
        public $idle;

        public $notificationOnCreate;
        public $notificationOnUpdate;
        public $notificationOnDelete;
        public $notificationOnTreatment;
        public $alertOnCreate;
        public $alertOnUpdate;
        public $alertOnDelete;
        public $alertOnTreatment;
        public $emailOnCreate;
        public $emailOnUpdate;
        public $emailOnDelete;
        public $emailOnTreatment;
        
    public $_sec_sendInfo;
        public $_spe_sendInfo;

    public $_sec_contractualValues;
        public $_spe_explaination;
        public $_spe_onAllOrDefault;
        public $_spe_startMonthPeriod;
        public $_spe_startDayPeriod;
        public $_spe_periodDuration;
        public $_spe_quantity;
        public $_spe_earnedPeriod;
        public $_spe_isIntegerQuotity;
        public $_spe_validityDuration;
        public $_spe_isUnpayedAllowed;
        public $_spe_isJustifiable;
        public $_spe_isAnticipated;
        public $_spe_nbDaysAfterNowLeaveDemandIsAllowed;
        public $_spe_nbDaysBeforeNowLeaveDemandIsAllowed;
    
    // Define the layout that will be used for lists
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="50%">${name}</th>
    <th field="color" width="20%" formatter="colorFormatter">${color}</th>
    <th field="sortOrder" width="15%">${sortOrderShort}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
    
    private static $_fieldsAttributes=array(
        "name"=>"required", 
        "notificationOnCreate"=>"hidden",
        "notificationOnUpdate"=>"hidden",
        "notificationOnDelete"=>"hidden",
        "notificationOnTreatment"=>"hidden",
        "alertOnCreate"=>"hidden",
        "alertOnUpdate"=>"hidden",
        "alertOnDelete"=>"hidden",
        "alertOnTreatment"=>"hidden",
        "emailOnCreate"=>"hidden",
        "emailOnUpdate"=>"hidden",
        "emailOnDelete"=>"hidden",
        "emailOnTreatment"=>"hidden",
//        "idWorkflow"=>"required,readonly",
        "idWorkflow"=>"required",
        "idActivity"=>"hidden",
        "color"=>"required",
        "_spe_explaination"=>"readonly",
        "sortOrder"=>"required"
        );
    
    private static $_databaseTableName = 'leavetype'; 
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
  
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
  	$paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
  	return $paramDbPrefix . self::$_databaseTableName;
  }
  
   /** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
            
    $defaultControl=parent::control();
       if ($defaultControl!='OK') {
               $result.=$defaultControl;
       }
    //constraint: $earnedPeriod <= $periodDuration
    if($this->_spe_earnedPeriod!=null && $this->_spe_periodDuration!=null && !($this->_spe_earnedPeriod<=$this->periodDuration) ){
        $result.='<br/>' . i18n('ErrorEarnedDurationSuperiorToPeriodDuration');
    }
    
    //constraint: startMonthPeriod/earnedPeriod/quantity/periodDuration/validityDuration must be defined/undefined at the same time(if the set of startDayPeriod is removed, it should also be added to this condition)
    if(! ( ($this->_spe_startMonthPeriod==null && $this->_spe_quantity==null && $this->_spe_earnedPeriod==null && $this->_spe_periodDuration==null && $this->_spe_validityDuration==null) ||
           ($this->_spe_startMonthPeriod!=null && $this->_spe_quantity!=null && $this->_spe_earnedPeriod!=null && $this->_spe_periodDuration!=null && $this->_spe_validityDuration!=null) ) ){
        $result.='<br/>' . i18n('ErrorInTheAttributesNeededForCalculation');
    }
    
    //quantity must be a modulo of 0.5
    if($this->_spe_quantity!==null && trim($this->_spe_quantity)!==""){
        if(! (fmod($this->_spe_quantity, 0.5) == 0)){
            $result.='<br/>' . i18n('errorQuantityNotModuloOfZeroPointFive');
        }
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
    $old=$this->getOld();
    if ($old->idWorkflow != $this->idWorkflow and $this->id>0) {
        $wf = new Workflow($old->idWorkflow);
        $oldWfStatuses = $wf->getWorkflowStatusList(-1,"id");
    }
        
    // Send Information
    if (isNotificationSystemActiv()) {
        $this->notificationOnCreate="";
        $this->notificationOnCreate = (RequestHandler::getValue('_spe_notificationOnCreateManager')=='on'?"M":"");
        $this->notificationOnCreate .= (RequestHandler::getValue('_spe_notificationOnCreateEmployee')=='on'?"E":"");
        $this->notificationOnUpdate="";
        $this->notificationOnUpdate = (RequestHandler::getValue('_spe_notificationOnUpdateManager')=='on'?"M":"");
        $this->notificationOnUpdate .= (RequestHandler::getValue('_spe_notificationOnUpdateEmployee')=='on'?"E":"");
        $this->notificationOnDelete="";
        $this->notificationOnDelete = (RequestHandler::getValue('_spe_notificationOnDeleteManager')=='on'?"M":"");
        $this->notificationOnDelete .= (RequestHandler::getValue('_spe_notificationOnDeleteEmployee')=='on'?"E":"");
        $this->notificationOnTreatment="";
        $this->notificationOnTreatment = (RequestHandler::getValue('_spe_notificationOnTreatmentManager')=='on'?"M":"");
        $this->notificationOnTreatment .= (RequestHandler::getValue('_spe_notificationOnTreatmentEmployee')=='on'?"E":"");
    }
    
    $this->alertOnCreate="";
    $this->alertOnCreate = (RequestHandler::getValue('_spe_alertOnCreateManager')=='on'?"M":"");
    $this->alertOnCreate .= (RequestHandler::getValue('_spe_alertOnCreateEmployee')=='on'?"E":"");
    $this->alertOnUpdate="";
    $this->alertOnUpdate = (RequestHandler::getValue('_spe_alertOnUpdateManager')=='on'?"M":"");
    $this->alertOnUpdate .= (RequestHandler::getValue('_spe_alertOnUpdateEmployee')=='on'?"E":"");
    $this->alertOnDelete="";
    $this->alertOnDelete = (RequestHandler::getValue('_spe_alertOnDeleteManager')=='on'?"M":"");
    $this->alertOnDelete .= (RequestHandler::getValue('_spe_alertOnDeleteEmployee')=='on'?"E":"");
    $this->alertOnTreatment="";
    $this->alertOnTreatment = (RequestHandler::getValue('_spe_alertOnTreatmentManager')=='on'?"M":"");
    $this->alertOnTreatment .= (RequestHandler::getValue('_spe_alertOnTreatmentEmployee')=='on'?"E":"");
    
    $this->emailOnCreate="";
    $this->emailOnCreate = (RequestHandler::getValue('_spe_emailOnCreateManager')=='on'?"M":"");
    $this->emailOnCreate .= (RequestHandler::getValue('_spe_emailOnCreateEmployee')=='on'?"E":"");
    $this->emailOnUpdate="";
    $this->emailOnUpdate = (RequestHandler::getValue('_spe_emailOnUpdateManager')=='on'?"M":"");
    $this->emailOnUpdate .= (RequestHandler::getValue('_spe_emailOnUpdateEmployee')=='on'?"E":"");
    $this->emailOnDelete="";
    $this->emailOnDelete = (RequestHandler::getValue('_spe_emailOnDeleteManager')=='on'?"M":"");
    $this->emailOnDelete .= (RequestHandler::getValue('_spe_emailOnDeleteEmployee')=='on'?"E":"");
    $this->emailOnTreatment="";
    $this->emailOnTreatment = (RequestHandler::getValue('_spe_emailOnTreatmentManager')=='on'?"M":"");
    $this->emailOnTreatment .= (RequestHandler::getValue('_spe_emailOnTreatmentEmployee')=='on'?"E":"");

    $resultClass = parent::save();
    
    if(strpos($resultClass,"OK")===false){
        return $resultClass;
    }
        
    //to create the new activity for the leaveType and it's assignements
    if($old->id == NULL){
        $pjLeaveId = Project::getLeaveProjectId();
        
        // Type of activity corresponding to leaves activity
        $lvActType= SqlElement::getFirstSqlElementFromCriteria("Type", array("scope"=>"Activity","code"=>"LEAVESYST"));
        if (!(isset($lvActType->id))) {
            return htmlSetResultMessage("ERROR", "NoActivityTypeForLeaveSystem", true, "", "search", "ERROR");
        }
        
        $actPlanElmt = new ActivityPlanningElement();
        $actPlanElmt->idActivityPlanningMode=1;
        
        $act = new Activity();
        $act->idProject = $pjLeaveId;
        $act->name = $this->name;
        $act->idActivityType = $lvActType->id;//how to find the id of the type LeaveActivityType safely and surely (what if someone modified it) ?
        $act->creationDate = (new DateTime('now'))->format('Y-m-d');
        $act->idStatus = 1;
        $act->ActivityPlanningElement = $actPlanElmt;
        $act->idle=0;
        
        // Planning Element
        $act->ActivityPlanningElement->refName = $act->name;
        $act->ActivityPlanningElement->idProject = $act->idProject;
        $act->ActivityPlanningElement->idle = $act->idle;
        $act->ActivityPlanningElement->topRefType = 'Project';
        $act->ActivityPlanningElement->topRefId = $act->idProject;
        $act->ActivityPlanningElement->topId = null;
        $act->ActivityPlanningElement->wbs = null;
        $act->ActivityPlanningElement->wbsSortable = null;
        
        $resultS=$act->simpleSave();
        if(strpos($resultS, "OK")===false){
                return $resultS;   
        }
        
        // Creation of LeaveTypeOfEmploymentContractType for existing Contract type
        $onDefault = (RequestHandler::getValue('onDefault')=='on');
        $onAll = (RequestHandler::getValue('onAll')=='on');

        if ($onAll or $onDefault) {
            $critTpEmpCntTp = array("idle" => "0");
            if ($onDefault) {
                $critTpEmpCntTp["isDefault"] = 1;
            }
            $contractType = new EmploymentContractType();
            $contractTypeList = $contractType->getSqlElementsFromCriteria($critTpEmpCntTp);
            foreach ($contractTypeList as $contractType) {
                $lvTpEmpCntType = new LeaveTypeOfEmploymentContractType();
                $lvTpEmpCntType->idEmploymentContractType = $contractType->id;
                $lvTpEmpCntType->idLeaveType= $this->id;
                $lvTpEmpCntType->idle=0;
                $lvTpEmpCntType->earnedPeriod = RequestHandler::getValue('_spe_earnedPeriod');
                $lvTpEmpCntType->isIntegerQuotity = (RequestHandler::getValue('_spe_isIntegerQuotity')=='on'?1:0);
                $lvTpEmpCntType->isAnticipated = (RequestHandler::getValue('_spe_isAnticipated')=='on'?1:0);
                $lvTpEmpCntType->isJustifiable = (RequestHandler::getValue('_spe_isJustifiable')=='on'?1:0);
                $lvTpEmpCntType->isUnpayedAllowed = $this->_spe_isUnpayedAllowed;
                $lvTpEmpCntType->nbDaysAfterNowLeaveDemandIsAllowed = $this->_spe_nbDaysAfterNowLeaveDemandIsAllowed;
                $lvTpEmpCntType->nbDaysBeforeNowLeaveDemandIsAllowed = $this->_spe_nbDaysBeforeNowLeaveDemandIsAllowed;
                $lvTpEmpCntType->periodDuration = RequestHandler::getValue('_spe_periodDuration');
                $lvTpEmpCntType->quantity = RequestHandler::getValue('_spe_quantity');
                $lvTpEmpCntType->startDayPeriod = RequestHandler::getValue('_spe_startDayPeriod');
                $lvTpEmpCntType->startMonthPeriod = RequestHandler::getValue('_spe_startMonthPeriod');
                $lvTpEmpCntType->validityDuration = RequestHandler::getValue('_spe_validityDuration');
                $lvTpEmpCntType->nbDaysAfterNowLeaveDemandIsAllowed = RequestHandler::getValue('_spe_nbDaysAfterNowLeaveDemandIsAllowed');
                $lvTpEmpCntType->nbDaysBeforeNowLeaveDemandIsAllowed = RequestHandler::getValue('_spe_nbDaysBeforeNowLeaveDemandIsAllowed');

                $resultS= $lvTpEmpCntType->save();
                if(strpos($resultS, "OK")===false and strpos($resultS,"NO_CHANGE")==false){
                    return $resultS;
                }                
            }
        }
        
        $rsEmp=new Resource();
        $rsEmpList = $rsEmp->getSqlElementsFromCriteria(array('isEmployee'=>1));
        foreach($rsEmpList as $employee){
            
            // Create a Employee Leave Earned
            $empLE=new EmployeeLeaveEarned();
            $empLE->idLeaveType=$this->id;
            $empLE->idEmployee=$employee->id;
            $empLE->setLeavesRight(true);
            $resultS = $empLE->save();
            if(strpos($resultS, "OK")===false and strpos($resultS,"NO_CHANGE")==false){
                return $resultS;   
            }
            
            // Create an  assignment to the corresponding activity
            $ass = new Assignment();
            $ass->idProject = $pjLeaveId;
            $ass->idResource = $employee->id;
            $ass->refType = "Activity";
            $ass->refId = $act->id;
            $resultS=$ass->simpleSave();
            if(strpos($resultS, "OK")===false){
                return $resultS;   
            }
        }
        $this->idActivity = $act->id;
        $resultClass = $this->save();
    } else {
        if ($old->idWorkflow != $this->idWorkflow) {
            // ==================================
            // WORKFLOW HAS CHANGE
            // ==================================
            $wf = new Workflow($this->idWorkflow);
            $newWfStatuses = $wf->getWorkflowStatusList(-1,"id");
            
            // But No difference between status in old workflow and status in new workflow
            // => Nothing else to do
            if (count(twoArraysObjects_diff($oldWfStatuses, $newWfStatuses))===0) { return $resultClass; }

            $alertLess="";
            $alertMore="";

            // For each leaves with this type, see impact
            $whereLeaveTypeClause = "idLeaveType=".$this->id;

            // ==================================
            // LESS STATUS IN NEW WORKFLOW STATUS        
            // ==================================
            // Search for less status in new workflow statuses
            $statusLess=array();    
            foreach($oldWfStatuses as $key=>$value) {
                if (!in_array($value,$newWfStatuses)) {
                    $statusLess[$key] = $value;                
                }        
            }
            // If less status => Do something
            if (count($statusLess)>0) {        
                // Search for Leaves that have :
                //    - a status is the less status list
                //          AND
                //    - a leave type associated with this workflow
                // STATUS
                $whereStatusClause = "";
                foreach($statusLess as $status) {
                    $whereStatusClause .= $status->id.",";
                }
                $whereStatusClause = "idStatus in (".substr($whereStatusClause,0,-1).")";

                $whereClause = $whereStatusClause. " AND ". $whereLeaveTypeClause;

                // Search the leaves
                $leave = new Leave();
                $leaveList = $leave->getSqlElementsFromCriteria(null,false,$whereClause);
                // Leaves => set statusOutOfWorkflow = 1
                if (count($leaveList)>0) {
                    $alertLess = "ChangeWorkflowWithLeavesHavingStatusOutOfWorkflow";
                    // For each leaves that have lost status in the new workflow
                    $queryWhere = "id in (";
                    foreach($leaveList as $leave) {
                        $queryWhere .= $leave->id.",";
                    }
                    $queryWhere = substr($queryWhere,0,-1).")";
                    $query = "update ".$leave->getDatabaseTableName()." set statusOutOfWorkflow=1 WHERE ".$queryWhere;
                    SqlDirectElement::execute($query);
                }
            }

            // ==================================
            // MORE STATUS IN NEW WORKFLOW STATUS        
            // ==================================
            $statusMore=array();    
            foreach($newWfStatuses as $key=>$value) {
                if (!in_array($value,$oldWfStatuses)) {
                    $statusMore[$value->id] = $value;                
                }        
            }
            // If more status => something to do
            if (count($statusMore)>0) {
                // WHERE STATUS
                $whereStatusClause = "";
                foreach($statusMore as $status) {
                    $whereStatusClause .= $status->id.",";
                }
                $whereStatusClause = "idStatus in (".substr($whereStatusClause,0,-1).")";

                // Search for Leaves that have :
                //    - a status is the more status list
                //          AND
                //    - statusOutOfWorkflow = 1 or statusSetLeaveChange = 1
                //          AND
                //    - a leave type associated with this workflow            
                $whereStatusOutSetChangeClause = " AND (statusOutOfWorkflow=1 OR statusSetLeaveChange=1)";            
                $whereClause = $whereStatusClause. " AND ". $whereLeaveTypeClause.$whereStatusOutSetChangeClause;
                // Search the leaves
                $leave = new Leave();
                $leaveList = $leave->getSqlElementsFromCriteria(null,false,$whereClause);
                // No Leave and no less alert => Nothing else to do
                if (count($leaveList)===0 and $alertLess=="") {return $resultClass;}

                // Update Leave's statusOutOfWorkflow=0 and statusSetLeaveChange=0 with transition resynchronize with the status
                // For each leave : Has setXXXXLeave resynchronize with transition
                $leaveToChangeResynchronized = array();
                foreach($leaveList as $leave) {
                    if ($leave->submitted==$statusMore[$leave->idStatus]->setSubmittedLeave AND
                        $leave->accepted==$statusMore[$leave->idStatus]->setAcceptedLeave AND
                        $leave->rejected==$statusMore[$leave->idStatus]->setRejectedLeave                        
                       )  {
                        array_push($leaveToChangeResynchronized, $leave->id);
                    }
                }
                $lR = count($leaveToChangeResynchronized);
                if ($lR>0) {
                    $queryWhere = "id in (";
                    for($i=0; $i<$lR; $i++) {
                        $queryWhere .= $leaveToChangeResynchronized[$i].",";
                    }
                    $queryWhere = substr($queryWhere,0,-1).")";
                    $query = "update ".$leave->getDatabaseTableName()." set statusOutOfWorkflow=0, statusSetLeaveChange=0 WHERE ".$queryWhere;
                    SqlDirectElement::execute($query);
                }

                // For each leave : Has setXXXXLeave of the status change
                $leaveToChangeStatusSetLeaveChange = array();
                foreach($leaveList as $leave) {
                    foreach ($leaveToChangeResynchronized as $leaveR) {
                        if ($leave->id == $leaveR) {continue;}
                    }
                    if (($leave->submitted==1 and $statusMore[$leave->idStatus]->setSubmittedLeave==0) OR
                        ($leave->submitted==0 and $statusMore[$leave->idStatus]->setSubmittedLeave==1)    
                       )  {
                        array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                        continue;
                    }
                    if (($leave->accepted==1 and $statusMore[$leave->idStatus]->setAcceptedLeave==0) OR
                        ($leave->accepted==0 and $statusMore[$leave->idStatus]->setAcceptedLeave==1)    
                       )  {
                        array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                        continue;
                    }
                    if (($leave->rejected==1 and $statusMore[$leave->idStatus]->setRejectedLeave==0) OR
                        ($leave->rejected==0 and $statusMore[$leave->idStatus]->setRejectedLeave==1)    
                       )  {
                        array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                        continue;
                    }
                    if (($leave->rejected==0 and $leave->accepted==0 and $leave->submitted==0 and 
                        ($statusMore[$leave->idStatus]->setRejectedLeave==1 or 
                         $statusMore[$leave->idStatus]->setAcceptedLeave==1 or
                         $statusMore[$leave->idStatus]->setSubmittedLeave==1))    
                       )  {
                        array_push($leaveToChangeStatusSetLeaveChange, $leave->id);
                        continue;
                    }
                }
                $l = count($leaveToChangeStatusSetLeaveChange);
                if ($l>0) {
                    $alertMore = "StatusSetTransitionLeaveHasChange";
                    $queryWhere = "id in (";
                    for($i=0; $i<$l; $i++) {
                        $queryWhere .= $leaveToChangeStatusSetLeaveChange[$i].",";
                    }
                    $queryWhere = substr($queryWhere,0,-1).")";
                    $query = "update ".$leave->getDatabaseTableName()." set statusOutOfWorkflow=0, statusSetLeaveChange=1 WHERE ".$queryWhere;
                    SqlDirectElement::execute($query);
                }
            }

            if ($alertLess!="" or $alertMore!="") {
                // Send Notification or Alert or email
                // Sender = User
                $receivers[0] = getSessionUser();            
                // Receiver = leaves admin
                $receivers[1] = getLeavesAdmin();

                $title = i18n("ChangesOnWorkflowHasImpactOnLeaves");
                $alertMore = ($alertMore==""?"":"".i18n("AND").$alertMore);
                $content = i18n($alertLess).($alertLess!=""?" ":"").$alertMore;
                $name = strtoupper(i18n("LeaveType"))." - ".i18n("maintenanceOnLeavesRequired");
                sendNotification($receivers, $this, "WARNING", $title, $content, $name);       
            }            
        }    
    }
    
    return $resultClass;
  }
  
  public function delete() {
      
    $result = parent::delete();
    if(strpos($result, "OK")===false){
        return $result;   
    }
    
    //to delete all the lines in employeeLeaveEarned with this->id (the id of the LeaveType which is being deleted)
    $crit = "idLeaveType = ".$this->id;
    $lvEarned= new EmployeeLeaveEarned();
    $resultP= $lvEarned->purge($crit);
    //return the error
    if(strpos($resultP, "OK")===false){
     return $resultP;   
    }   
//to delete the activity and it's dependencies
    $actLvType = new Activity($this->idActivity);
    $actLvTypeId = $actLvType->id;
    
    $critPurge = "refType = 'Activity' and refId = ".$actLvTypeId;
    $ass= new Assignment();
    $resultP = $ass->purge($critPurge);
    if(strpos($resultP, "OK")===false){
     return $resultP;   
    }
    
    $plElem = new PlanningElement();
    $resultP = $plElem->purge($critPurge);
    if(strpos($resultP, "OK")===false){
     return $resultP;   
    }
    
    $plElemBaseline = new PlanningElementBaseline();
    $resultP = $plElemBaseline->purge($critPurge);
    if(strpos($resultP, "OK")===false){
     return $resultP;   
    }
    
    $resultD = $actLvType->purge("id=$actLvTypeId");
    if(strpos($resultD, "OK")===false){
     return $resultD;   
    }
    
    //to delete the LeaveTypeOfEmploymentContractType
    $critPurge = "idLeaveType = ".$this->id;
    $lvTpContractType = new LeaveTypeOfEmploymentContractType();
    $resultP = $lvTpContractType->purge($critPurge);
    if(strpos($resultP, "OK")===false){
     return $resultP;   
    }
    
    //to delete the CustomEarnedRulesOfEmploymentContractType
    $customRulesContractType = new CustomEarnedRulesOfEmploymentContractType();
    $resultP = $customRulesContractType->purge($critPurge);
    if(strpos($resultP, "OK")===false){
     return $resultP;   
    }

    return $result;
  }

    /**=========================================================================
     * Return the leaveTypeList
     * @param integer $idle -1 = all - 0 with idle=0 - 1 with idle=1
     * @param integer $idWorkflow = Workflow associated to the leave type
     * @return array of leaveType objects
     */
    static function getList($idle=-1, $idWorkflow=null) {
        $leaveType = new LeaveType();
        $crit=array();
        if ($idWorkflow>0) {
            $crit["idWorkflow"] = $idWorkflow;
        }
        if ($idle!=-1) {
            $crit["idle"] = ($idle==0?"0":"1");
        }
        if (count($crit)===0) {
            $leaveTypeList = $leaveType->getSqlElementsFromCriteria(null);
        } else {
            $leaveTypeList = $leaveType->getSqlElementsFromCriteria($crit);            
        }
        return $leaveTypeList;
    }

    /**=========================================================================
     * Return the list of workflows associated to the leave types
     * @param integer $idleWorkflow -1 = all - 0 with idle=0 - 1 with idle=1
     * @param integer $idleLeaveType -1 = all - 0 with idle=0 - 1 with idle=1
     * @return array of Workflow objects
     */
    static function getWorkflowList($idleWorkflow=-1, $idleLeaveType=-1) {
        $leaveTypeList = self::getList($idleLeaveType);
        $wklistId = array();
        foreach($leaveTypeList as $leaveType) {
            if (!array_key_exists($leaveType->idWorkflow, $wklistId)) {
                array_push($wklistId,$leaveType->idWorkflow);                
            }
        }
        $l = count($wklistId);
        if ($l===0) { return array();}
        
        $whereClause = "id in (";
        for ($i=0; $i<$l;$i++) {
            $whereClause .= $wklistId[$i].",";
        }
        $whereClause = substr($whereClause,0,-1).")";
        if ($idleWorkflow!=-1) {
            $whereClause .= " AND idle = ".($idleWorkflow==0?0:1);
        }

        $wk = new Workflow();
        $wkList = $wk->getSqlElementsFromCriteria(null,false,$whereClause);
        return $wkList;
    }
    
    /**=========================================================================
     * Return the list of statuses of workflows associated to the leave types
     * @param boolean $orderBySortOrder If true, status are ordered by they sortOrder
     * @param integer $idleStatus -1 = all - 0 with idle=0 - 1 with idle=1
     * @param integer $idleWorkflow -1 = all - 0 with idle=0 - 1 with idle=1
     * @param integer $idleLeaveType -1 = all - 0 with idle=0 - 1 with idle=1
     * @return array of Status objects
     */
    static function getStatusList($orderBySortOrder=true,$idleStatus=-1,$idleWorkflow=-1, $idleLeaveType=-1) {
        $wkfList = self::getWorkflowList($idleWorkflow, $idleLeaveType);
        $theStatusList = array();
        foreach($wkfList as $wkf) {
            $lstStatus = $wkf->getWorkflowstatus();

            foreach($lstStatus as $status) {
                if (!array_key_exists($status->idStatusFrom, $theStatusList)) {
                    $theStatus = new Status($status->idStatusFrom);
                    if ($idleStatus===-1 or ($idleStatus===0 and $theStatus->idle===0) or ($idleStatus===1 and $theStatus->idle===1)) {
                        $theStatusList[$status->idStatusFrom] = $theStatus;
                    }
                }
                if (!array_key_exists($status->idStatusTo, $theStatusList)) {
                    $theStatus = new Status($status->idStatusTo);
                    if ($idleStatus===-1 or ($idleStatus===0 and $theStatus->idle===0) or ($idleStatus===1 and $theStatus->idle===1)) {
                        $theStatusList[$status->idStatusTo] = $theStatus;
                    }
                }
            }
        }
        if ($orderBySortOrder) {
            usort($theStatusList, function($a, $b)
                {
                    return strcmp($a->sortOrder, $b->sortOrder);
                }
            );            
        }
        return $theStatusList;
    }
    
    
    private function drawSendInfo($readOnly=false, $refresh=false) {
        if ($readOnly) {
            $clReadOnly="readonly";
        } else {
            $clReadOnly="";            
        }
        $result = '<table style="width:100%">';
        $result .= "    <tr>";
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("colNotificationReceivers")."</th>";
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("creation")."</th>";
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("updating")."</th>";
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("deleting")."</th>";
        $result .= '        <th class="assignHeader" style="width:20%">'.i18n("treatment")."</th>";
        $result .= "    </tr>";
        if (isNotificationSystemActiv()) {
            $result .= "    <tr>";
            $result .= '        <td rowspan="2" class="assignHeader" style="width:20%">'.i18n("notification")."</td>";
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnCreateManager" name = "_spe_notificationOnCreateManager" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnCreate,"M")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Manager");
            $result .= '        </td>';
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnUpdateManager" name = "_spe_notificationOnUpdateManager" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnUpdate,"M")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Manager");
            $result .= '        </td>';
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnDeleteManager" name = "_spe_notificationOnDeleteManager" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnDelete,"M")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Manager");
            $result .= '        </td>';
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnTreatmentManager" name = "_spe_notificationOnTreatmentManager" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnTreatment,"M")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Manager");
            $result .= '        </td>';
            $result .= "    </tr>";
            $result .= "    <tr>";
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnCreateEmployee" name = "_spe_notificationOnCreateEmployee" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnCreate,"E")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Employee");
            $result .= '        </td>';
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnUpdateEmployee" name = "_spe_notificationOnUpdateEmployee" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnUpdate,"E")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Employee");
            $result .= '        </td>';
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnDeleteEmployee" name = "_spe_notificationOnDeleteEmployee" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnDelete,"E")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Employee");
            $result .= '        </td>';
            $result .= '        <td class="linkData">';
            $result .= '            <div id="_spe_notificationOnTreatmentEmployee" name = "_spe_notificationOnTreatmentEmployee" ';
            $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
            $result .=                   $clReadOnly;
            $result .=                   (strpos($this->notificationOnTreatment,"E")===false?"":" checked").'>';
            $result .= '            </div>&nbsp'.i18n("Employee");
            $result .= '        </td>';
            $result .= "    </tr>";
        }
        
        $result .= "    <tr>";
        $result .= '        <td rowspan="2" class="assignHeader" style="width:20%">'.i18n("displayAlert")."</td>";
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnCreateManager" name = "_spe_alertOnCreateManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnCreate,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnUpdateManager" name = "_spe_alertOnUpdateManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnUpdate,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnDeleteManager" name = "_spe_alertOnDeleteManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox" onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnDelete,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnTreatmentManager" name = "_spe_alertOnTreatmentManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnTreatment,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= "    </tr>";
        $result .= "    <tr>";
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnCreateEmployee" name = "_spe_alertOnCreateEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnCreate,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnUpdateEmployee" name = "_spe_alertOnUpdateEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnUpdate,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnDeleteEmployee" name = "_spe_alertOnDeleteEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnDelete,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_alertOnTreatmentEmployee" name = "_spe_alertOnTreatmentEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->alertOnTreatment,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= "    </tr>";
        
        $result .= "    <tr>";
        $result .= '        <td rowspan="2" class="assignHeader" style="width:20%">'.i18n("displayMail")."</td>";
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnCreateManager" name = "_spe_emailOnCreateManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnCreate,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnUpdateManager" name = "_spe_emailOnUpdateManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnUpdate,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnDeleteManager" name = "_spe_emailOnDeleteManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnDelete,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnTreatmentManager" name = "_spe_emailOnTreatmentManager" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnTreatment,"M")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Manager");
        $result .= '        </td>';
        $result .= "    </tr>";
        $result .= "    <tr>";
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnCreateEmployee" name = "_spe_emailOnCreateEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnCreate,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnUpdateEmployee" name = "_spe_emailOnUpdateEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnUpdate,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnDeleteEmployee" name = "_spe_emailOnDeleteEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnDelete,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= '        <td class="linkData">';
        $result .= '            <div id="_spe_emailOnTreatmentEmployee" name = "_spe_emailOnTreatmentEmployee" ';
        $result .= '                 dojoType="dijit.form.CheckBox" type="checkbox"  onChange="formChanged();"';
        $result .=                   $clReadOnly;
        $result .=                   (strpos($this->emailOnTreatment,"E")===false?"":" checked").'>';
        $result .= '            </div>&nbsp'.i18n("Employee");
        $result .= '        </td>';
        $result .= "    </tr>";
        $result .= "</table>";
        
        return $result;
    }


    /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
    $result="";
    
    switch($item) {
        case 'explaination' :
            $result = '<textarea dojoType="dijit.form.Textarea" readonly';
            $result .= ' rows="2" style="max-height:150px; height:150px; width:700px; background-color:transparent; border=0px;" ';
            $result .= ' class="generalColClass" >';
            $result .= i18n('explainationOfHowWorksLeaveTypeOfEmploymentContractType');
            $result .= '</textarea>';            
            break;
        case 'onAllOrDefault':
            $onDefault= '<div id="onDefaultDiv" style="display:inline-block;"> ';
            $onAll= '<div id="onAllDiv" style="display:inline-block;"> ';

            $onDefault .= '<input data-dojo-type="dijit/form/CheckBox" id="onDefault" name="onDefault" checked="true" ';
            $onDefault .= 'onChange="';
            $onDefault .= "    if (dijit.byId('onDefault').checked) { ";
            $onDefault .= "       dijit.byId('onAll').set('checked',false);";
            $onDefault .= '    }';
            $onDefault .= '"';
            $onDefault.= '>';

            $onAll .= '<input data-dojo-type="dijit/form/CheckBox" id="onAll" name="onAll" ';
            $onAll .= 'onChange="';
            $onAll .= "        if (dijit.byId('onAll').checked) { ";
            $onAll .= "       dijit.byId('onDefault').set('checked',false);";
            $onAll .= '    }';
            $onAll .= '"';
            $onAll.= '>';

            $result  = '<tr class="detail generalRowClass">';
            $result .= '<td style="width: 200px;">';
            $result .=  $onDefault;
            $result .= '<label for="onDefault">'. i18n("onDefault") .'&nbsp:&nbsp;</label></div></td><td>';
            $result .=  $onAll;
            $result .= '<label  for="onAll">'. i18n("onAll") .'&nbsp:&nbsp;</label></div>';
            $result .=  '</td></tr>';  
            break;
        case 'startMonthPeriod':
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_startMonthPeriod">'.i18n("colStartMonthPeriod").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_startMonthPeriod" value="6" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:1,max:12,places:0}" name="_spe_startMonthPeriod" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'startDayPeriod':
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_startDayPeriod">'.i18n("colStartDayPeriod").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_startDayPeriod" value="1" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:1,max:31,places:0}" name="_spe_startDayPeriod" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'periodDuration':
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_periodDuration">'.i18n("colPeriodDuration").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_periodDuration" value="12" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:1,max:99999,places:0}" name="_spe_periodDuration" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'quantity' :
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_quantity">'.i18n("colQuantity").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_quantity" value="25" ';
            $result .= 'data-dojo-props="smallDelta:0.5, largeDelta:1.0, constraints:{min:0.5,max:999.5,places:1}" name="_spe_quantity" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'earnedPeriod' :
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_earnedPeriod">'.i18n("colEarnedPeriod").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_earnedPeriod" value="12" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:1,max:99999,places:0}" name="_spe_earnedPeriod" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'isIntegerQuotity':
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_isIntegerQuotity">'.i18n("isIntegerQuotity").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input data-dojo-type="dijit/form/CheckBox" type="checkbox" id="_spe_isIntegerQuotity" value="" ';
            $result .= 'name="_spe_isIntegerQuotity"  />';
            $result .= '</td></tr>';
            break;
        case 'validityDuration' :
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_validityDuration">'.i18n("colValidityDuration").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_validityDuration" value="24" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:1,max:99999,places:0}" name="_spe_validityDuration" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'nbDaysAfterNowLeaveDemandIsAllowed' :
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_nbDaysAfterNowLeaveDemandIsAllowed">'.i18n("colNbDaysAfterNowLeaveDemandIsAllowed").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_nbDaysAfterNowLeaveDemandIsAllowed" value="0" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:0,max:99999,places:0}" name="_spe_nbDaysAfterNowLeaveDemandIsAllowed" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'nbDaysBeforeNowLeaveDemandIsAllowed' :
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_nbDaysBeforeNowLeaveDemandIsAllowed">'.i18n("colNbDaysBeforeNowLeaveDemandIsAllowed").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input class="input" data-dojo-type="dijit/form/NumberSpinner" id="_spe_nbDaysBeforeNowLeaveDemandIsAllowed" value="90" ';
            $result .= 'data-dojo-props="smallDelta:1, constraints:{min:0,max:99999,places:0}" name="_spe_nbDaysBeforeNowLeaveDemandIsAllowed" style="width:100px"/>';
            $result .= '</td></tr>';
            break;
        case 'isAnticipated' :
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_isAnticipated">'.i18n("colIsAnticipated").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input data-dojo-type="dijit/form/CheckBox" type="checkbox" id="_spe_isAnticipated" value="" ';
            $result .= 'name="_spe_isAnticipated"  />';
            $result .= '</td></tr>';
            break;
            break;
        case 'isJustifiable':
            $result = '<tr><td>';
            $result .= '<label class="label longLabel" for="_spe_isJustifiable">'.i18n("isJustifiable").'&nbsp:</label>';
            $result .= '</td><td>';
            $result .= '<input data-dojo-type="dijit/form/CheckBox" type="checkbox" id="_spe_isJustifiable" value="" ';
            $result .= 'name="_spe_isJustifiable"  />';
            $result .= '</td></tr>';
            break;
        case 'sendInfo' :
            $result = $this->drawSendInfo($readOnly, $refresh);
            break;
    }    
     return $result;
  }
  
  public function setAttributes() {
    if ($this->id>0) {
      unset($this->_sec_contractualValues);
      unset($this->_spe_earnedPeriod);
      unset($this->_spe_isAnticipated);
      unset($this->_spe_isJustifiable);
      unset($this->_spe_isUnpayedAllowed);
      unset($this->_spe_isIntegerQuotity);
      unset($this->_spe_nbDaysAfterNowLeaveDemandIsAllowed);
      unset($this->_spe_nbDaysBeforeNowLeaveDemandIsAllowed);
      unset($this->_spe_periodDuration);
      unset($this->_spe_quantity);
      unset($this->_spe_startDayPeriod);
      unset($this->_spe_startMonthPeriod);
      unset($this->_spe_validityDuration);
      unset($this->_spe_onAllOrDefault);
      unset($this->_spe_explaination);
    }
    if (!$this->id) {
      $wf=SqlElement::getSingleSqlElementFromCriteria('Workflow', array('isLeaveWorkflow'=>'1'));
      if ($wf->id) {
        $this->idWorkflow=$wf->id;
      }
    }
  }
      
}
?>
