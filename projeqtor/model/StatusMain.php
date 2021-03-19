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
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
require_once('_securityCheck.php');
class StatusMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $setHandledStatus;
  public $setDoneStatus;
  public $setIntoserviceStatus; //ADD qCazelles - Ticket #53
  public $setIdleStatus;
  public $setCancelledStatus;
  public $fixPlanning;
  public $_lib_helpFixPlanning;
  public $color;
  public $sortOrder=0;
  public $idle;
  //public $_sec_void;
  public $isCopyStatus;
// MTY - LEAVE SYSTEM  
  public $_sec_Leave;  
  public $setSubmittedLeave;
  public $setAcceptedLeave;
  public $setRejectedLeave;
// MTY - LEAVE SYSTEM  
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="30%">${name}</th>
    <th field="setHandledStatus" width="10%" formatter="booleanFormatter">${setHandledStatus}</th>
    <th field="setDoneStatus" width="10%" formatter="booleanFormatter">${setDoneStatus}</th>
    <th field="setIdleStatus" width="10%" formatter="booleanFormatter">${setIdleStatus}</th>
    <th field="setCancelledStatus" width="10%" formatter="booleanFormatter">${setCancelledStatus}</th>
    <th field="color" width="10%" formatter="colorFormatter">${color}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>  
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsTooltip = array(
      "fixPlanning"=> "tooltipFixPlanningActivity",
  );
  
  private static $_fieldsAttributes=array(
// MTY - LEAVE SYSTEM        
      "setSubmittedLeave"=>"hidden",
      "setRejectedLeave"=>"hidden",
      "fixPlanning"=>"nobr",
      "setAcceptedLeave"=>"hidden",
// MTY - LEAVE SYSTEM  
      "isCopyStatus"=>"hidden", 
      "name"=>"required"
  );
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
// MTY - LEAVE SYSTEM  
    if (isLeavesSystemActiv()) {
        self::$_fieldsAttributes['setSubmittedLeave']="";
        self::$_fieldsAttributes['setAcceptedLeave']="";
        self::$_fieldsAttributes['setRejectedLeave']="";
    } else {
        unset($this->_sec_Leave);
  }
// MTY - LEAVE SYSTEM  
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
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  public function deleteControl() {
    $result="";
    if ($this->isCopyStatus==1) {    
      $result="<br/>" . i18n("msgCannotDeleteStatus");
    }
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
// MTY - LEAVE SYSTEM  
  public function save() {
      $old = $this->getOld();
      $result = parent::save();
      
      if ($this->setSubmittedLeave != $old->setSubmittedLeave or
          $this->setAcceptedLeave != $old->setAcceptedLeave or
          $this->setRejectedLeave != $old->setRejectedLeave
         ) {
        
        // ==============================  
        // UNSYNCHRONIZED => SYNCHRONIZED  
        // ==============================  
        // Update leaves that where unsynchronized and are synchronized now
        $whereClause = "idStatus = ".$this->id." AND statusSetLeaveChange=1 AND statusOutOfWorkflow=0 AND (1=1";
        // SUBMITTED
        if ($this->setSubmittedLeave != $old->setSubmittedLeave) {
            $whereClause .= " AND submitted=".($this->setSubmittedLeave==1?1:0);            
}
        // ACCEPTED
        if ($this->setAcceptedLeave != $old->setAcceptedLeave) {
            $whereClause .= " AND accepted=".($this->setAcceptedLeave==1?1:0);            
        }
        // REJECTED
        if ($this->setRejectedLeave != $old->setRejectedLeave) {
            $whereClause .= " AND rejected=".($this->setRejectedLeave==1?1:0);            
        }
        $whereClause .= ")";
        $leave = new Leave();
        // Set statusSetLeaveChange = 0
        $query = "update ".$leave->getDatabaseTableName()." set statusSetLeaveChange=0 WHERE ".$whereClause;
        SqlDirectElement::execute($query);

        // ==============================  
        // SYNCHRONIZED => UNSYNCHRONIZED  
        // ==============================          
        // Check if leaves with this status are unsynchronized with setXXXXLeave
        $whereClause = "idStatus = ".$this->id." AND statusSetLeaveChange=0 AND statusOutOfWorkflow = 0 AND (1=0 ";
        // SUBMITTED
        if ($this->setSubmittedLeave != $old->setSubmittedLeave) {
            $whereClause .= " OR submitted=".($this->setSubmittedLeave==1?0:1);            
        }
        // ACCEPTED
        if ($this->setAcceptedLeave != $old->setAcceptedLeave) {
            $whereClause .= " OR accepted=".($this->setAcceptedLeave==1?0:1);            
        }
        // REJECTED
        if ($this->setRejectedLeave != $old->setRejectedLeave) {
            $whereClause .= " OR rejected=".($this->setRejectedLeave==1?0:1);            
        }
        $whereClause .=")";
        // Search leave concerned by change
        $leaveList = $leave->getSqlElementsFromCriteria(null,false,$whereClause);
        $l=count($leaveList);
        // No leave => Nothing else to do
        if ($l===0) { return $result;}
          
        // Set statusSetLeaveChange = 1
        $query = "update ".$leave->getDatabaseTableName()." set statusSetLeaveChange=1 WHERE ".$whereClause;
        SqlDirectElement::execute($query);

        // Send Notification or Alert or email
        // Sender = User
        $receivers[0] = getSessionUser();            
        // Receiver = leaves admin
        $receivers[1] = getLeavesAdmin();

        $title = i18n("ChangesOnStatusHasImpactOnLeaves");
        $content = i18n("StatusSetTransitionLeaveHasChange");
        $name = strtoupper(i18n("Status"))." - ".i18n("maintenanceOnLeavesRequired");
        sendNotification($receivers, $this, "WARNING", $title, $content, $name);        
      }           
      return $result;
  }
  
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
  
  /* ========================================================================================
  * VALIDATION SCRIPT
    ======================================================================================== */   
  
   /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    
    if($colName=="setSubmittedLeave"){
        $colScript.='<script type="dojo/connect" event="onChange" >';
        $colScript.='    statusSetLeaveFalse(this,"Rejected","Validated");';
        $colScript.= '</script>';
    }
    if($colName=="setRejectedLeave"){
        $colScript.='<script type="dojo/connect" event="onChange" >';
        $colScript.='    statusSetLeaveFalse(this,"Submitted","Validated");';
        $colScript.= '</script>';
    }
     if($colName=="setAcceptedLeave"){
        $colScript.='<script type="dojo/connect" event="onChange" >';
        $colScript.='    statusSetLeaveFalse(this,"Submitted","Rejected");';
        $colScript.= '</script>';
    }
   
    return $colScript;
  }

  /* ========================================================================================
  * MISCELANIUS FUNCTIONS
    ======================================================================================== */   
    public function isLeaveNeutralStatus() {
        return (($this->setAcceptedLeave==1 or $this->setRejectedLeave==1 or $this->setSubmittedLeave==1)?false:true);
    }
  
// MTY - LEAVE SYSTEM  

  
}?>