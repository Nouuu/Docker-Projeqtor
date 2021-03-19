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
 * Planning element is an object included in all objects that can be planned.
 */ 
require_once('_securityCheck.php');
class MeetingPlanningElementMain extends PlanningElement {

  public $id;
  public $idProject;
  public $refType;
  public $refId;
  public $refName;
  public $_tab_4_2_smallLabel=array('validated','assigned', 'real', 'left', 'work','cost');
  public $validatedWork;
  public $assignedWork;
  public $realWork;
  public $leftWork;
  public $validatedCost;
  public $assignedCost;
  public $realCost;
  public $leftCost;
  //public $_tab_1_1=array('','priority');
  public $_tab_1_1_smallLabel_1 = array('', 'color');
  public $color;
  public $priority;
  public $idMeetingPlanningMode;
  
  private static $_fieldsAttributes=array(
    "initialStartDate"=>"hidden",
    "validatedStartDate"=>"hidden",
    "plannedStartDate"=>"hidden,noImport",
    "realStartDate"=>"hidden,noImport",
    "initialEndDate"=>"hidden",
    "validatedEndDate"=>"hidden",
    "plannedEndDate"=>"hidden,noImport",
    "realEndDate"=>"hidden,noImport",
    "initialDuration"=>"hidden",
    "validatedDuration"=>"hidden",  
    "plannedDuration"=>"hidden,noImport",
    "realDuration"=>"hidden,noImport",
    "initialWork"=>"hidden",
    "validatedWork"=>"",
    "assignedWork"=>"readonly,noImport", 
    "realWork"=>"readonly,noImport",
    "leftWork"=>"readonly,noImport",
    "plannedWork"=>"hidden,noImport",
  	"notPlannedWork"=>"hidden",
    "initialCost"=>"hidden",
    "validatedCost"=>"",
    "assignedCost"=>"readonly,noImport",
    "realCost"=>"readonly,noImport",
    "leftCost"=>"readonly,noImport",
    "plannedCost"=>"hidden,noImport",
    "progress"=>"hidden,noImport",
    "expectedProgress"=>"hidden,noImport",
    "wbs"=>"hidden,noImport",
    "idMeetingPlanningMode"=>"hidden,required,noImport",
    "plannedStartFraction"=>"hidden",
    "plannedEndFraction"=>"hidden",
    "validatedStartFraction"=>"hidden",
    "validatedEndFraction"=>"hidden",
    "priority"=>"hidden"
  );   
  
  private static $_databaseTableName = 'planningelement';
  //private static $_databaseCriteria = array('refType'=>'Meeting'); // Cannot use auto filter as PeriodicMeeting is a Meeting (no PeriodicMeetingPlanningElement)
  
  private static $_databaseColumnName=array(
    "idMeetingPlanningMode"=>"idPlanningMode"
  );
  private static $_colCaptionTransposition = array('initialStartDate'=>'requestedStartDate',
      'initialEndDate'=> 'requestedEndDate',
      'initialDuration'=>'requestedDuration'
  );
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
  	$this->idMeetingPlanningMode=16;
    parent::__construct($id,$withoutDependentObjects);
  }
  
  private function hideWorkCost() {
    unset($this->_tab_4_2_smallLabel);
  	self::$_fieldsAttributes['validatedWork']='hidden';
    self::$_fieldsAttributes['assignedWork']='hidden';
    self::$_fieldsAttributes['realWork']='hidden';
    self::$_fieldsAttributes['leftWork']='hidden';
    self::$_fieldsAttributes['validatedCost']='hidden';
    self::$_fieldsAttributes['assignedCost']='hidden';
    self::$_fieldsAttributes['realCost']='hidden';
    self::$_fieldsAttributes['leftCost']='hidden';
    //self::$_fieldsAttributes['priority']='hidden';
  }
  private function showWorkCost() {
  	$this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');
  	//$this->_sec_progress=true;
    self::$_fieldsAttributes['validatedWork']='';
    self::$_fieldsAttributes['assignedWork']='readonly';
    self::$_fieldsAttributes['realWork']='readonly';
    self::$_fieldsAttributes['leftWork']='readonly';    
    self::$_fieldsAttributes['validatedCost']='';
    self::$_fieldsAttributes['assignedCost']='readonly';
    self::$_fieldsAttributes['realCost']='readonly';
    self::$_fieldsAttributes['leftCost']='readonly';
    //self::$_fieldsAttributes['priority']='';
  }
  
  // ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY 
  private function showValidated() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='';
    self::$_fieldsAttributes['assignedWork']='hidden';
    self::$_fieldsAttributes['realWork']='hidden';
    self::$_fieldsAttributes['leftWork']='hidden';    
    self::$_fieldsAttributes['validatedCost']='';
    self::$_fieldsAttributes['assignedCost']='hidden';
    self::$_fieldsAttributes['realCost']='hidden';
    self::$_fieldsAttributes['leftCost']='hidden';
  }

  private function showOnlyWork() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='';
    self::$_fieldsAttributes['assignedWork']='readonly';
    self::$_fieldsAttributes['realWork']='readonly';
    self::$_fieldsAttributes['leftWork']='readonly';    
    self::$_fieldsAttributes['validatedCost']='hidden';
    self::$_fieldsAttributes['assignedCost']='hidden';
    self::$_fieldsAttributes['realCost']='hidden';
    self::$_fieldsAttributes['leftCost']='hidden';
  }
  
  private function showOnlyCost() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='hidden';
    self::$_fieldsAttributes['assignedWork']='hidden';
    self::$_fieldsAttributes['realWork']='hidden';
    self::$_fieldsAttributes['leftWork']='hidden';    
    self::$_fieldsAttributes['validatedCost']='';
    self::$_fieldsAttributes['assignedCost']='readonly';
    self::$_fieldsAttributes['realCost']='readonly';
    self::$_fieldsAttributes['leftCost']='readonly';
  }

  private function showOnlyValidatedWorkAndAllCost() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='';
    self::$_fieldsAttributes['assignedWork']='hidden';
    self::$_fieldsAttributes['realWork']='hidden';
    self::$_fieldsAttributes['leftWork']='hidden';    
    self::$_fieldsAttributes['validatedCost']='';
    self::$_fieldsAttributes['assignedCost']='readonly';
    self::$_fieldsAttributes['realCost']='readonly';
    self::$_fieldsAttributes['leftCost']='readonly';
  }

  private function hideWorkAndShowValidatedCost() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='hidden';
    self::$_fieldsAttributes['assignedWork']='hidden';
    self::$_fieldsAttributes['realWork']='hidden';
    self::$_fieldsAttributes['leftWork']='hidden';    
    self::$_fieldsAttributes['validatedCost']='';
    self::$_fieldsAttributes['assignedCost']='hidden';
    self::$_fieldsAttributes['realCost']='hidden';
    self::$_fieldsAttributes['leftCost']='hidden';
  }

  private function showAllWorkAndValidatedCost() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='';
    self::$_fieldsAttributes['assignedWork']='readonly';
    self::$_fieldsAttributes['realWork']='readonly';
    self::$_fieldsAttributes['leftWork']='readonly';    
    self::$_fieldsAttributes['validatedCost']='';
    self::$_fieldsAttributes['assignedCost']='hidden';
    self::$_fieldsAttributes['realCost']='hidden';
    self::$_fieldsAttributes['leftCost']='hidden';
  }
  
  private function showOnlyValidatedWorkAndHideCost() {
    $this->_tab_4_2_smallLabel = array('validated','assigned', 'real', 'left', 'work','cost');

    self::$_fieldsAttributes['validatedWork']='';
    self::$_fieldsAttributes['assignedWork']='hidden';
    self::$_fieldsAttributes['realWork']='hidden';
    self::$_fieldsAttributes['leftWork']='hidden';    
    self::$_fieldsAttributes['validatedCost']='hidden';
    self::$_fieldsAttributes['assignedCost']='hidden';
    self::$_fieldsAttributes['realCost']='hidden';
    self::$_fieldsAttributes['leftCost']='hidden';
  }
// END - ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY 

  public function setAttributes() {
// MODIFY BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY
    if (!$this->_workVisibility or !$this->_costVisibility) $this->setVisibility();
    $workVisibility=$this->_workVisibility;
    $costVisibility=$this->_costVisibility;
    $wcVisibility = $workVisibility.$costVisibility;
    switch ($wcVisibility) {
        case "NONO" :
        $this->hideWorkCost();
            break;
        case "NOALL" :
            $this->showOnlyCost();
            break;
        case "NOVAL" :
            $this->hideWorkAndShowValidatedCost();
            break;
        case "ALLALL" :
          $this->showWorkCost();
            break;
        case "ALLNO" :
            $this->showOnlyWork();
            break;
        case "ALLVAL" :
            $this->showAllWorkAndValidatedCost();
            break;
        case "VALVAL" :
            $this->showValidated();
            break;
        case "VALALL" :
            $this->showOnlyValidatedWorkAndAllCost();
            break;
        case "VALNO" :
            $this->showOnlyValidatedWorkAndHideCost();
            break;
        default:
          $this->hideWorkCost();
            break;
      }
// END MODIFY BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY
  	//global $workVisibility,$costVisibility;
// COMMENT BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY
//    if (! $this->id) {
//      //$this->hideWorkCost();
//    } else {
//      if ($workVisibility!='ALL' or $costVisibility!='ALL') {
//        $this->hideWorkCost();
//      } else {
//        /*$ass=new Assignment();
//        $cptAss=$ass->countSqlElementsFromCriteria(array('refType'=>$this->refType, 'refId'=>$this->refId));
//        if ($cptAss>0) {*/
//          $this->showWorkCost();
//        /*} else {
//          $this->hideWorkCost();
//        } */
//      }
//    }
// END COMMENT BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY
    }
    /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

    /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
//   /** ========================================================================
//    * Return the specific database criteria
//    * @return the databaseTableName
//    */
//   protected function getStaticDatabaseCriteria() {
//     return self::$_databaseCriteria;
//   }  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  
  /** ========================================================================
   * Return the generic databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
  	$meeting=new $this->refType($this->refId);
  	$old=new MeetingPlanningElement($this->id);
  	if (!$this->id) {
  	  if (!$this->priority) {
  		  $this->priority=1; // very high priority
  	  }
  		$this->idMeetingPlanningMode=16; // fixed planning  		
  	}
  	if ($this->refType=='Meeting' and $meeting->idPeriodicMeeting) {
  		$this->topRefType='PeriodicMeeting';
  		$this->topRefId=$meeting->idPeriodicMeeting;
  	} else if ($meeting->idActivity) {
  		$this->topRefType='Activity';
      $this->topRefId=$meeting->idActivity;
  	} else {
  		$this->topRefType='Project';
  		$this->topRefId=$meeting->idProject;
  	}
  	if ($this->refType=='Meeting') {
  	  $this->validatedStartDate=$meeting->meetingDate;
  	  $this->plannedStartDate=$meeting->meetingDate;
  	  if ($this->realStartDate) $this->realStartDate=$meeting->meetingDate;
  	  $this->validatedEndDate=$meeting->meetingDate;
  	  $this->plannedEndDate=$meeting->meetingDate;
  	  if ($this->realStartDate) $this->realEndDate=$meeting->meetingDate;    
  	} else if ($this->refType=='PeriodicMeeting') {
      $this->validatedStartDate=$meeting->periodicityStartDate;
    }
  	
  	$this->validatedStartFraction=calculateFractionFromTime($meeting->meetingStartTime);
  	$this->validatedDuration=calculateFractionBeetweenTimes($meeting->meetingStartTime,$meeting->meetingEndTime);
  	$this->validatedEndFraction=$this->validatedStartFraction+$this->validatedDuration;
  	
  	//$this->validatedWork=0;
    $this->idProject=$meeting->idProject;
    $this->refName=$meeting->name;
    $this->idle=$meeting->idle;
    if (isset($meeting->done)) {
      $this->done=$meeting->done;
    }
    if (! $this->assignedCost) $this->assignedCost=0;
    if (! $this->realCost) $this->realCost=0;
    if (! $this->leftCost) $this->leftCost=0;
    if (trim($old->idProject)!=trim($this->idProject) or trim($old->topId)!=trim($this->topId) 
    or trim($old->topRefType)!=trim($this->topRefType) or trim($old->topRefId)!=trim($this->topRefId)) {
    	$this->wbs=null; // Force recalculation
    	$this->topId=null;
    }
    return parent::save();
  }
  
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $mode=null;
    $meeting=new $this->refType($this->refId,true);
    if (! $this->idMeetingPlanningMode) {
      $this->idMeetingPlanningMode=16;
    }   
    if (!$this->priority) {
      $this->priority=1; // very high priority
    }
    if ($this->refType=='Meeting') {
      if (! $meeting->meetingDate) $meeting->meetingDate=date('Y-m-d');
      $this->validatedStartDate=$meeting->meetingDate;
      $this->validatedEndDate=$meeting->meetingDate;
    } else if ($this->refType=='PeriodicMeeting') {
      if (! $meeting->periodicityStartDate) $meeting->periodicityStartDate=date('Y-m-d');
      $this->validatedStartDate=$meeting->periodicityStartDate;
      $this->validatedEndDate=$meeting->periodicityStartDate;
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
    
  }
}
?>