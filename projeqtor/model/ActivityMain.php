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

/**
 * ============================================================================
 * Action is establised during meeting, to define an action to be followed.
 */
require_once ('_securityCheck.php');
class ActivityMain extends SqlElement {
  
  // List of fields that will be exposed in general user interface
  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id; // redefine $id to specify its visible place
  public $reference;
  public $name;
  public $idActivityType;
  public $idProject;
  public $externalReference;
  public $creationDate;
  public $lastUpdateDateTime;
  public $idUser;
  public $idContact;
  public $Origin;
  public $description;
  public $_sec_treatment;
  public $idActivity;
  public $idStatus;
  public $idResource;
  public $idMilestone;
  public $fixPlanning;
  public $_lib_helpFixPlanning;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $_sec_Assignment;
  public $_Assignment = array();
  public $_sec_Progress;
  public $ActivityPlanningElement; // is an object
  public $isPlanningActivity;
  public $_sec_productComponent;
  public $idProduct;
  public $idComponent;
  public $idTargetProductVersion;
  public $idTargetComponentVersion;
  public $_sec_predecessor;
  public $_Dependency_Predecessor = array();
  public $_sec_successor;
  public $_Dependency_Successor = array();
  public $_sec_subActivity;
  public $_spe_activity;
  // MTY - LEAVE SYSTEM
  public $_spe_isLeaveMngActivity;
  // MTY - LEAVE SYSTEM
  public $_sec_Link;
  public $_Link = array();
  public $_Attachment = array();
  public $_Note = array();
  public $_nbColMax = 3;
  
  // Define the layout that will be used for lists
  private static $_layout = '
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameActivityType" width="10%" >${idActivityType}</th>
    <th field="name" width="25%" >${name}</th>
    <th field="plannedEndDate" from="ActivityPlanningElement" width="10%" formatter="dateFormatter">${plannedDueDate}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="progress" from="ActivityPlanningElement" width="10%" formatter="percentFormatter">${progress}</th>
    <th field="nameResource" formatter="thumbName22" width="15%" >${responsible}</th>
    ';
  
  private static $_fieldsTooltip = array(
      "fixPlanning"=> "tooltipFixPlanningActivity",
  );
  
  private static $_fieldsAttributes = array(
      "id" => "nobr", 
      "reference" => "readonly", 
      "name" => "required", 
      "idProject" => "required", 
      "idActivityType" => "required", 
      "idStatus" => "required", 
      "creationDate" => "required", 
      "fixPlanning"=>"nobr",
      "handled" => "nobr", 
      "done" => "nobr", 
      "idle" => "nobr", 
      "idleDate" => "nobr", 
      "cancelled" => "nobr",
      "isPlanningActivity" => "title",
      "_spe_isLeaveMngActivity" => "hidden"
  );
  private static $_colCaptionTransposition = array(
      'idUser' => 'issuer', 
      'idResource' => 'responsible', 
      'idActivity' => 'parentActivity', 
      'idContact' => 'requestor', 
      'isPlanningActivity' => 'PlanningActivity'
  );
  
  // private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array(
      'idTargetProductVersion' => 'idVersion', 
      'idTargetComponentVersion' => 'idComponentVersion');
  
  /**
   * ==========================================================================
   * Constructor
   * 
   * @param $id the
   *          id of the object in the database (null if not stored yet)
   * @return void
   */
  function __construct($id = NULL, $withoutDependentObjects = false) {
    parent::__construct ( $id, $withoutDependentObjects );
    if ($withoutDependentObjects)
      return;
    if (Parameter::getGlobalParameter ( 'limitPlanningActivity' ) != "YES") {
      self::$_fieldsAttributes ['isPlanningActivity'] = 'hidden';
    }
// MTY - LEAVE SYSTEM
    // If it's an Leave activity (ie : Activity.idProject is the project Leave, id isLeaveSystemProject=1),
    // can't modify a lot of attributes
    $leaveProjectId = Project::getLeaveProjectId();
    if ($leaveProjectId==$this->idProject && $leaveProjectId!=null) {
      self::$_fieldsAttributes ['idActivity'] = 'hidden';
      self::$_fieldsAttributes ['idProject'] = 'readonly';
      self::$_fieldsAttributes ['idActivityType'] = 'hidden';
      self::$_fieldsAttributes ['idStatus'] = 'hidden';
      self::$_fieldsAttributes ['handled'] = 'hidden';
      self::$_fieldsAttributes ['handledDate'] = 'hidden';
      self::$_fieldsAttributes ['idle'] = 'hidden';
      self::$_fieldsAttributes ['idleDate'] = 'hidden';
      self::$_fieldsAttributes ['done'] = 'hidden';
      self::$_fieldsAttributes ['doneDate'] = 'hidden';
      self::$_fieldsAttributes ['cancelled'] = 'hidden';
      self::$_fieldsAttributes ['idResource'] = 'hidden';
      self::$_fieldsAttributes ['idContact'] = 'hidden';
      self::$_fieldsAttributes ['result'] = 'hidden';
      self::$_fieldsAttributes['ActivityPlanningElement'] = 'hidden';
      self::$_fieldsAttributes['isPlanningActivity'] = 'hidden';
      self::$_fieldsAttributes['idProduct'] = 'hidden';
      self::$_fieldsAttributes['idComponent'] = 'hidden';
      self::$_fieldsAttributes['idTargetProductVersion'] = 'hidden';
      self::$_fieldsAttributes['idTargetComponentVersion'] = 'hidden';
      self::$_fieldsAttributes['Origin'] = 'hidden';
      unset($this->_sec_Progress);
      unset($this->sec_productComponent);
      unset($this->_sec_treatment);
      unset($this->_sec_Assignment);      
      unset($this->_Assignment);
      unset($this->_sec_productComponent);
      unset($this->_sec_predecessor);
      unset($this->_Dependency_Predecessor);
      unset($this->_sec_successor);
      unset($this->_Dependency_Successor);
  }
// MTY - LEAVE SYSTEM
  }
  
  /**
   * ==========================================================================
   * Destructor
   * 
   * @return void
   */
  function __destruct() {
    parent::__destruct ();
  }
  
  // ============================================================================**********
  // GET STATIC DATA FUNCTIONS
  // ============================================================================**********
  
  /**
   * ==========================================================================
   * Return the specific layout
   * 
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /**
   * ==========================================================================
   * Return the specific fieldsAttributes
   * 
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  /**
   * ============================================================================
   * Return the specific colCaptionTransposition
   * 
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld = null) {
    return self::$_colCaptionTransposition;
  }
  
  /**
   * ========================================================================
   * Return the specific databaseColumnName
   * 
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
// MTY - LEAVE SYSTEM  
// =============================================================================================================
// DRAWING FUNCTION
// =============================================================================================================

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. 
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item,$readOnly=false,$refresh=false){
      if ($item=='isLeaveMngActivity') {
          if (isLeavesSystemActiv()) {
            $leaveProject = (Project::isTheLeaveProject($this->idProject)?1:0);
            echo '<input type="hidden" name="isLeaveMngActivity" id="isLeaveMngActivity" value='.$leaveProject.' />';
          } else { echo '';}
      }
  }
// MTY - LEAVE SYSTEM  
  
  // ============================================================================**********
  // GET VALIDATION SCRIPT
  // ============================================================================**********
  
  /**
   * ==========================================================================
   * Return the validation sript for some fields
   * 
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript ( $colName );
    
    if ($colName == "idProject") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("ActivityPlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName == "idActivity") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("ActivityPlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName == "idActivityType") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  setDefaultPlanningMode(this.value);';
      $colScript .= '  setDefaultPriority(this.value);';
      $colScript .= '</script>';
    } else if ($colName == "fixPlanning") {
      if(Parameter::getUserParameter('paramLayoutObjectDetail')=="tab"){
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= ' dijit.byId("ActivityPlanningElement_fixPlanning").set("value",dijit.byId("fixPlanning").get("value"));';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
      }
    } 
    return $colScript;
  }
  
  /**
   * =========================================================================
   * control data corresponding to Model constraints
   * 
   * @param
   *          void
   * @return "OK" if controls are good or an error message
   *         must be redefined in the inherited class
   */
  public function control() {
    $result = "";
    $old = $this->getOld(false);
    
    //Gautier #4304
    $proj = new Project($this->idProject,true);
    $projType = new ProjectType($proj->idProjectType);
    if($projType->isLeadProject){
      if (!$this->id) {
        $result .= '<br/>' . i18n ( 'cantCreateAnActivityFromLeadProject' );
      }
      if ($this->id && $old->idProject != $this->idProject) {
        //      ==> Can't associated the activity with the project dedicated to the leave
          $result .= '<br/>' . i18n ( 'cantAssociateAnActivityWithLeadProject' );
      }
    }
    //Gautier #2505
    if ($this->id && $old->idProject != $this->idProject) {
      $ass = new Assignment();
      $lstAss = $ass->getSqlElementsFromCriteria(array('idProject'=>$old->idProject,'refType'=>'Activity','refId'=>$this->id,'idle'=>'0'));
      foreach ( $lstAss as $as){
        $proj = new Project($this->idProject,true);
        $topProject = $proj->getTopProjectList(true);
        $aff = new Affectation();
        $where = " idResource = ".$as->idResource." and idProject in " . transformValueListIntoInClause($topProject);
        $affExist = $aff->countSqlElementsFromCriteria(null,$where);
        if(!$affExist){
          $result .= '<br/>' . i18n ( 'cantMoveActivityWithoutAffectedResource' );
          break;
        }
      }
    }
    
    if($this->idActivity){
      $parentAct = new Activity($this->idActivity);
      if($parentAct->ActivityPlanningElement->idWorkUnit and $parentAct->ActivityPlanningElement->idComplexity and $parentAct->ActivityPlanningElement->quantity){
        $result .= '<br/>' . i18n ( 'cantHaveParentWithWorkUnit' );
      }
    }
    
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
        // Can't create or associate an activity on the project that is dedicated to the leave
        $leaveProjectId = Project::getLeaveProjectId();
        // At creation
        //      => Can't create an activity associated with the project dedicated to the leave
        if ($this->id==null && $leaveProjectId == $this->idProject) {
          $result .= '<br/>' . i18n ( 'cantCreateAnActivityFromProjectLeave' );        
        }
        // At Update project
        if ($this->id!=null && $old->idProject != $this->idProject) {
            //      ==> Can't associated the activity with the project dedicated to the leave
            if ($this->idProject == $leaveProjectId) {
              $result .= '<br/>' . i18n ( 'cantAssociateAnActivityWithProjectLeave' );
            }
            //      ==> Can't change project if project is the leaveProject
            if ($old->idProject == $leaveProjectId) {
              $result .= '<br/>' . i18n ( 'cantChangeProjectOfActivityAssociatedWithProjectLeave' );
            }
        }

        // At Update type
        if ($this->id!=null && $old->idActivityType != $this->idActivityType) {
            //      ==> Can't change the type of an activity associated with the project dedicated to the leave
            if ($this->idProject == $leaveProjectId) {
              $result .= '<br/>' . i18n ( 'cantChangeTypeOnActivityAssociatedWithProjectLeave' );
            }
        }

        // At Update Parent Activity
        if ($this->id!=null && $old->idActivity != $this->idActivity) {
            //      ==> Can't change the Parent Activity of an activity associated with the project dedicated to the leave
            if ($this->idProject == $leaveProjectId) {
              $result .= '<br/>' . i18n ( 'cantChangeActivityParentOnActivityAssociatedWithProjectLeave' );
            }
        }

        // At Update Status
        if ($this->id!=null && $old->idStatus != $this->idStatus) {
            //      ==> Can't change the Status of an activity associated with the project dedicated to the leave
            if ($this->idProject == $leaveProjectId) {
              $result .= '<br/>' . i18n ( 'cantChangeStatusOnActivityAssociatedWithProjectLeave' );
            }
        }
    }
    
// MTY - LEAVE SYSTEM
    
    if ($this->id and $this->id == $this->idActivity) {
      $result .= '<br/>' . i18n ( 'errorHierarchicLoop' );
    } else if ($this->ActivityPlanningElement and $this->ActivityPlanningElement->id
      and ($this->idActivity!=$old->idActivity or $this->idProject!=$old->idProject)) {
      if (trim ( $this->idActivity )) {
        $parentType = 'Activity';
        $parentId = $this->idActivity;
      } else {
        $parentType = 'Project';
        $parentId = $this->idProject;
      }
      $result .= $this->ActivityPlanningElement->controlHierarchicLoop ( $parentType, $parentId );
    }
    
    if (trim ( $this->idActivity )) {
      $parentActivity = new Activity ( $this->idActivity );
      if ($parentActivity->idProject != $this->idProject) {
        $result .= '<br/>' . i18n ( 'msgParentActivityInSameProject' );
      }
    }
    $defaultControl = parent::control ();
    if ($defaultControl != 'OK') {
      $result .= $defaultControl;
    }
    if ($result == "") {
      $result = 'OK';
    }
    if($this->ActivityPlanningElement->minimumThreshold){
      $ass = new Assignment();
      $assList = $ass->getSqlElementsFromCriteria(array('refType'=>'Activity', 'refId'=>$this->id));
      if($assList){
        $res = new ResourceAll($assList[0]->idResource);
        $maxThreshold = $res->capacity*($assList[0]->rate/100);
        foreach ($assList as $assign){
          $res = new ResourceAll($assign->idResource);
        	if($res->capacity*($assign->rate/100) < $maxThreshold){
        	  $maxThreshold = $res->capacity*($assign->rate/100);
        	}
        }
        if(Work::convertWork($this->ActivityPlanningElement->minimumThreshold) > $maxThreshold){
          $workUnit = Parameter::getGlobalParameter('workUnit');
          $dayTime = 1;
          if($workUnit == 'hours'){
            $dayTime = Parameter::getGlobalParameter('dayTime');
          }
          $result=i18n('minimumThresholdInputError',array(($maxThreshold*$dayTime).Work::displayShortWorkUnit()));
        }
      }
    }
    if($result=='OK'){
      if($this->ActivityPlanningElement->idWorkUnit){
        $workUnit = new WorkUnit($this->ActivityPlanningElement->idWorkUnit);
        if($workUnit->validityDate and !SqlElement::isSaveConfirmed() and $old->ActivityPlanningElement->idWorkUnit != $this->ActivityPlanningElement->idWorkUnit ){
          $currentDateTime = date('Y-m-d');
          $today = new DateTime($currentDateTime);
          $validityDate = new DateTime($workUnit->validityDate);
          if($validityDate < $today){
            $result='<br/>' . i18n('errorValidityDate');
            $result.='<input type="hidden" name="confirmControl" id="confirmControl" value="save" />';
          }
        }
      }
    }
    return $result;
  }
  public function startDateActivity($parentVersion = null){
    
    $pe=new planningElement();
    $where = "refType ='Activity' and refId = $this->id";
    
    $pe = $pe->getSqlElementsFromCriteria(null,null,$where);
    if ($pe[0]->realStartDate) {
      $startDate = $pe[0]->realStartDate;
    }
    elseif ($pe[0]->plannedStartDate) {
      $startDate = $pe[0]->plannedStartDate;
    }
    elseif ($pe[0]->initialStartDate) {
      $startDate = $pe[0]->initialStartDate;
    }
    else{
      $startDate = null;
    }
    
    if ($parentVersion != NULL and empty($startDate)) {
      if ($parentVersion->realStartDate) {
        $startDate = $parentVersion->realStartDate;
      }
      elseif ($parentVersion->plannedStartDate) {
        $startDate = $parentVersion->plannedStartDate;
      }
      elseif ($parentVersion->initialStartDate) {
        $startDate = $parentVersion->initialStartDate;
      }
      else {
        $startDate = $parentVersion->myStartDate;
      }
      $this->ownDate = false;
    }
    
    return $startDate;
  }
  
  public function endDateActivity($parentVersion = null){
    
    $pe=new planningElement();
    $where = "refType ='Activity' and refId = $this->id";
    
    $pe = $pe->getSqlElementsFromCriteria(null,null,$where);
    
    if ($pe[0]->realEndDate) {
      $endDate = $pe[0]->realEndDate;
    }
    elseif ($pe[0]->plannedEndDate) {
      $endDate = $pe[0]->plannedEndDate;
    }
    elseif ($pe[0]->initialEndDate) {
      $endDate = $pe[0]->initialEndDate;
    }
    else{
      $endDate = null;
    }
    
    if ($parentVersion != NULL and empty($endDate)) {
      if ($parentVersion->realDeliveryDate) {
        $endDate= $parentVersion->realDeliveryDate;
      }
      elseif ($parentVersion->plannedDeliveryDate) {
        $endDate= $parentVersion->plannedDeliveryDate;
      }
      elseif ($parentVersion->initialDeliveryDate) {
        $endDate= $parentVersion->initialDeliveryDate;
      }
      else {
        $endDate= $parentVersion->myEndDate;
      }
      $this->ownDate = false;
    }
    
    
    return $endDate;
  }
  /**
   * =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * 
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  
  /**
   * =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * 
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save($onlyProject=false) {
    $oldResource = null;
    $oldIdle = null;
    $oldIdProject = null;
    $oldIdActivity = null;
    $oldTargetProductVersion = null;
    if ($this->id) {
      $old = $this->getOld (false);
      $oldResource = $old->idResource;
      $oldIdle = $old->idle;
      $oldIdProject = $old->idProject;
      $oldIdActivity = $old->idActivity;
      $oldTargetProductVersion = $old->idTargetProductVersion;
      if($old->fixPlanning and $old->ActivityPlanningElement->fixPlanning and !$this->ActivityPlanningElement->fixPlanning){
        $this->fixPlanning = 0;
        $this->ActivityPlanningElement->fixPlanning = 0;
      }
      if($old->idStatus != $this->idStatus){
        $status = new Status ($this->idStatus);
        if($status->fixPlanning and !$this->fixPlanning){
          $this->fixPlanning = 1;
          $this->ActivityPlanningElement->fixPlanning = 1;
        }
      }
    }
    if($this->fixPlanning and !$this->ActivityPlanningElement->fixPlanning)$this->ActivityPlanningElement->fixPlanning=1;
    if(!$this->fixPlanning and $this->ActivityPlanningElement->fixPlanning)$this->fixPlanning=1;
    
    // #305 : need to recalculate before dispatching to PE
    $this->recalculateCheckboxes ();
    $this->ActivityPlanningElement->refName = $this->name;
    $this->ActivityPlanningElement->idProject = $this->idProject;
    $this->ActivityPlanningElement->idle = $this->idle;
    $this->ActivityPlanningElement->done = $this->done;
    $this->ActivityPlanningElement->cancelled = $this->cancelled;
    if ($this->idActivity and trim ( $this->idActivity ) != '') {
      $this->ActivityPlanningElement->topRefType = 'Activity';
      $this->ActivityPlanningElement->topRefId = $this->idActivity;
      $this->ActivityPlanningElement->topId = null;
    } else {
      $this->ActivityPlanningElement->topRefType = 'Project';
      $this->ActivityPlanningElement->topRefId = $this->idProject;
      $this->ActivityPlanningElement->topId = null;
    }
    if ( (trim($this->idProject)!=trim($oldIdProject) and !$onlyProject) or trim($this->idActivity)!=trim( $oldIdActivity )) {
      $this->ActivityPlanningElement->wbs = null;
      $this->ActivityPlanningElement->wbsSortable = null;
    }
    $result = parent::save ();
    if (! strpos ( $result, 'id="lastOperationStatus" value="OK"' )) {
      return $result;
    }
    
    /// KROWRY HERE
    if((Parameter::getGlobalParameter('autoSetAssignmentByResponsible')=="YES" and !SqlElement::isCopyInProgress()  and !$this->ActivityPlanningElement->isManualProgress) or RequestHandler::isCodeSet('selectedResource')){ 
      $proj=new Project($this->idProject,true);
      $type=new Type($proj->idProjectType);
      $resource=(RequestHandler::isCodeSet('selectedResource'))?RequestHandler::getValue('selectedResource'):$this->idResource;
      if ($type->code!='ADM' and $resource and trim ( $resource ) != '' and ! trim ( $oldResource ) and stripos ( $result, 'id="lastOperationStatus" value="OK"' ) > 0) {
        // Add assignment for responsible
        $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', array(
            'idProfile' => getSessionUser ()->getProfile ( $this->idProject ), 
            'scope' => 'assignmentEdit') );
        if ($habil and $habil->rightAccess == 1) {
          $ass = new Assignment ();
          $crit = array('idResource' => $resource, 'refType' => 'Activity', 'refId' => $this->id);
          //$lst = $ass->getSqlElementsFromCriteria ( $crit, false );
          //if (count ( $lst ) == 0) {
          $cpt=$ass->countSqlElementsFromCriteria($crit);
          if ($cpt == 0) {
            $ass->idProject = $this->idProject;
            $ass->refType = 'Activity';
            $ass->refId = $this->id;
            $ass->idResource = $resource;
            $ass->assignedWork = 0;
            $ass->realWork = 0;
            $ass->leftWork = 0;
            $ass->plannedWork = 0;
            $ass->notPlannedWork = 0;
            $ass->rate = '100';
            if ($this->ActivityPlanningElement->validatedWork and $this->ActivityPlanningElement->validatedWork>$this->ActivityPlanningElement->assignedWork) {
              $ass->assignedWork=$this->ActivityPlanningElement->validatedWork-$this->ActivityPlanningElement->assignedWork;
              $ass->leftWork=$ass->assignedWork;
            }
            $ass->save ();
          }
        }
      }
    }
    // Change idle or idProject value => update idle and idProject for assignments
    if (($this->idle != $oldIdle) or ($this->idProject != $oldIdProject)) {
      // Add assignment for responsible
      $ass = new Assignment ();
      $crit = array("refType" => "Activity", "refId" => $this->id);
      $assList = $ass->getSqlElementsFromCriteria ( $crit, false );
      foreach ( $assList as $ass ) {
        $ass->idle = $this->idle;
        $ass->idProject = $this->idProject;
        $resAssSave=$ass->save();
        // Change idProject value => update idProject for work
        // update not done to PlannedWork : new planning must be calculated
        if ($this->idProject != $oldIdProject) {
          $work = new Work ();
          $crit = array("refType" => "Activity", "refId" => $this->id);
          $workList = $work->getSqlElementsFromCriteria ( $crit, false );
          foreach ( $workList as $work ) {
            $work->idProject = $this->idProject;
            $work->save ();
          }
          $work = new PlannedWork ();
          $crit = array("refType" => "Activity", "refId" => $this->id);
          $workList = $work->getSqlElementsFromCriteria ( $crit, false );
          foreach ( $workList as $work ) {
            $work->idProject = $this->idProject;
            $work->save ();
          }
        }
      }
    }
    if ($this->idProject != $oldIdProject) {
      $lstElt = array('Activity', 'Ticket', 'Milestone', 'PeriodicMeeting', 'Meeting', 'TestSession');
      foreach ( $lstElt as $elt ) {
        $eltObj = new $elt ();
        $crit = array('idActivity' => $this->id);
        $lst = $eltObj->getSqlElementsFromCriteria ( $crit, false, null, null, true );
        foreach ( $lst as $obj ) {
          $objBis = new $elt ( $obj->id );
          $objBis->idProject = $this->idProject;
          if ($elt=='Activity') {
            $tmpRes = $objBis->save (true);
          } else {
            $tmpRes = $objBis->save ();
          }
        }
      }
    }
    if ($oldTargetProductVersion != $this->idTargetProductVersion) {
      $vers = new Version ( $this->idTargetProductVersion );
      $idProduct = ($vers->idProduct) ? $vers->idProduct : null;
      $ticket = new Ticket ();
      $ticketList = $ticket->getSqlElementsFromCriteria ( array('idActivity' => $this->id) );
      foreach ( $ticketList as $ticket ) {
        $ticket->idTargetProductVersion = $this->idTargetProductVersion;
        if ($idProduct) {
          $ticket->idProduct = $idProduct;
        }
        $ticket->save ();
      }
    }
    
    /*
    // ticket #2822 - mehdi
    if (Parameter::getGlobalParameter ( 'autoUpdateActivityStatus' ) == 'YES' and isset($old)) {
      if ($this->idActivity) {
        $parent = new Activity ($this->idActivity);
      } else {
        $parent = new Project ($this->idProject);
      }
      if ($this->handled and $this->handled!=$old->handled) {
        if ( ! $parent->handled ) {
          $parent->handled = $this->handled;
          $parent->handledDate=date('Y-m-d');
          $allowedStatusList=Workflow::getAllowedStatusListForObject($parent);
          foreach ( $allowedStatusList as $st ) {
            if ($st->setHandledStatus) {
              $parent->idStatus=$st->id;
              $parent->save();
              break;
            }
          }  
        }
      }
      $status = new Status ($this->idStatus);
      $isStatDone=($status->setDoneStatus)?true:false;
      $isStatIdle=($status->setIdleStatus)?true:false;
      $isStatCancelled=($status->setCancelledStatus)?true:false;
      $status = new Status ($old->idStatus);
      $isOldStatDone=($status->setDoneStatus)?true:false;
      $isOldStatIdle=($status->setIdleStatus)?true:false;
      $isOldStatCancelled=($status->setCancelledStatus)?true:false;
      if ( ($isStatDone and $isStatDone!=$isOldStatDone) or ($isStatIdle and $isStatIdle!=$isOldStatIdle) or ($isStatCancelled and $isStatCancelled!=$isOldStatCancelled)) {
        $allDone=true; 
        $allIdle=true; 
        $allCancelled=true;
        
        $sons=$this->getSqlElementsFromCriteria(null, null, "idActivity=$parent->id or ( idActivity is null and idProject=$parent->id)");
        foreach ($sons as $act) {
          if (!$act->done and !$act->cancelled) $allDone=false;
          if (!$act->idle and !$act->cancelled) $allIdle=false;
          if (!$act->cancelled) $allCancelled=false;
        }
        $setToDone=($isStatDone and $isStatDone!=$isOldStatDone and $allDone)?true:false;
        $setToIdle=($isStatIdle and $isStatIdle!=$isOldStatIdle and $allIdle)?true:false;
        $setToCancelled=($isStatCancelled and $isStatCancelled!=$isOldStatCancelled and $allCancelled)?true:false;
        if ($setToDone or $setToIdle or $setToCancelled) {
          $currentParentStatus=new Status($parent->idStatus);
          if ( (! $setToDone or ($setToDone and $currentParentStatus->setDoneStatus) ) 
           and (! $setToIdle or ($setToIdle and $currentParentStatus->setIdleStatus) )  
           and (! $setToCancelled or ($setToCancelled and $currentParentStatus->setCancelledStatus) )) {
            // Nothing to do, already in a status corresponding to target
          } else {
            $allowedStatusList=Workflow::getAllowedStatusListForObject($parent);
            $saveParent=false;
            foreach ( $allowedStatusList as $st ) {
              if ($setToDone and $st->setDoneStatus) {
                $parent->idStatus=$st->id;
                $parent->done=$this->done;
                $parent->doneDate=date('Y-m-d');
                $saveParent=true;
                $setToDone=false;
              }
              if ($setToIdle and $st->setIdleStatus) {
                $parent->idStatus=$st->id;
                $parent->idle=$this->idle;
                $parent->idleDate=date('Y-m-d');
                $saveParent=true;
                $setToIdle=false;
              }  
              if ($setToCancelled and $st->setCancelledStatus) {
                $parent->idStatus=$st->id;
                $parent->cancelled=$this->cancelled;
                $parent->doneDate=date('Y-m-d');
                $saveParent=true;
                $setToCancelled=false;
              }
            }
            if ($saveParent) $parent->save();
          }
        }
      }
    }*/
    
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
        $leaveProjectId = Project::getLeaveProjectId();
    }
// MTY - LEAVE SYSTEM
    return $result;
  }
  public function setAttributes() {
    if(Parameter::getUserParameter('paramLayoutObjectDetail')=="col"){
      self::$_fieldsAttributes["fixPlanning"]='hidden';
    }
    if (Parameter::getGlobalParameter('manageMilestoneOnItems') != 'YES' and (! property_exists('Activity','_customFields') or ! in_array('idMilestone', Activity::$_customFields))) {
      self::$_fieldsAttributes["idMilestone"]='hidden';
    }
  }
  
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
  
// MTY - LEAVE SYSTEM  
  /**
   * =========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * 
   * @see persistence/SqlElement#delete()
   * @param Boolean - withControlLeave = If the delete is made by LeaveTypeMain, then withControlLeave=false
   * @return the return message of persistence/SqlElement#delete() method
   */
  public function delete($withControlLeave=true) {
      if (isLeavesSystemActiv()) {
        // Can't delete an activity of the leave system project.
        $leaveProjectId = Project::getLeaveProjectId();
        if ($leaveProjectId==$this->idProject && $withControlLeave==true && $leaveProjectId!=null) {
          $returnValue = '<b>' . i18n ( 'messageCantDeleteAnActivityAssociatedToLeaveType' ) . '</b><br/>';
          $returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
          $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
          $returnValue .= '<input type="hidden" id="lastOperation" value="delete" />';
          return $returnValue;
}
      }
      return parent::delete();
  }    
// MTY - LEAVE SYSTEM  
}
?>