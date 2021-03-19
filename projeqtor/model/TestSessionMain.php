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
 * Action is establised during meeting, to define an action to be followed.
 */ 
require_once('_securityCheck.php');
class TestSessionMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idTestSessionType;
  public $idProject;
  public $idProduct;
  public $idVersion;
  public $externalReference;
  public $creationDateTime;
  public $idUser;
  public $description;
  public $_sec_treatment;
  public $idActivity;
  public $idTestSession;
  public $idStatus;
  public $idResource;
  //public $startDate;
  //public $endDate;
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
  public $_Assignment=array();
  public $_sec_Progress;
  public $TestSessionPlanningElement;
  public $_sec_testCaseSummary;
  public $_tab_6_2_smallLabel = array('countTotal','countPlanned', 'countPassed', 'countBlocked', 'countFailed','work','workElementCount','');
  public $countTotal;
  public $countPlanned;
  public $countPassed;
  public $countBlocked;
  public $countFailed;
  public $sumPlannedWork;
  public $_void_1;
  public $pctPlanned;
  public $pctPassed;
  public $pctBlocked;
  public $pctFailed;
  public $idRunStatus;
  public $_tab_5_1_smallLabel = array('testSummary','', '','','countIssues','');
  public $runStatusIcon;
  public $runStatusName;
  public $_void_2;
  public $_void_3;
  public $countIssues;
  public $_sec_predecessor;
  public $_Dependency_Predecessor=array();
  public $_sec_successor;
  public $_Dependency_Successor=array();
  public $_sec_TestCaseRun;
  public $_testCaseRun_colSpan="2";
  public $_TestCaseRun=array();
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  
  public $_nbColMax=3;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameTestSessionType" width="15%" >${type}</th>
    <th field="name" width="30%" >${name}</th>
    <th field="colorNameRunStatus" width="10%" formatter="colorNameFormatter">${testSummary}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" formatter="thumbName22" width="15%" >${responsible}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "idProject"=>"required",
                                  "name"=>"required", 
                                  "idTestSessionType"=>"required",
                                  "idStatus"=>"required",
                                  "creationDateTime"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idUser"=>"hidden",
                                  "countTotal"=>"display",
                                  "countPlanned"=>"display",
                                  "countPassed"=>"display",
                                  "countFailed"=>"display",
                                  "sumPlannedWork"=>"display",
                                  "countBlocked"=>"display",
                                  "countIssues"=>"display",
                                  "noDisplay1"=>"calculated,hidden",
                                  "pctPlanned"=>"calculated,display,html",
                                  "pctPassed"=>"calculated,display,html",
                                  "pctBlocked"=>"calculated,display,html",
                                  "pctFailed"=>"calculated,display,html",
                                  "noDisplay3"=>"calculated,hidden",
                                  "idRunStatus"=>"display,html,hidden,forceExport",
                                  "runStatusIcon"=>"calculated,display,html",
                                  "runStatusName"=>"calculated,display,html",
                                  "startDate"=>"hidden", 
                                  "endDate"=>"hidden",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idResource'=> 'responsible',
                                                   'idActivity'=>'parentActivity',
                                                   'idTestSession'=>'parentTestSession',
                                                   'idRunStatus'=>'testSummary'
  );
  
  //private static $_databaseColumnName = array();
  private static $_databaseColumnName = array();
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    $this->getCalculatedItem();
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
   * Return the specific databaseColumnName
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
    if ($colName=="idProject" ) {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("TestSessionPlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
     if ($colName=="idActivity") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("TestSessionPlanningElement_wbs").value=""; ';
      $colScript .= '  if (trim(this.value)) dijit.byId("idTestSession").set("value",null); ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
     if ($colName=="idTestSession" ) {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("TestSessionPlanningElement_wbs").value=""; ';
      $colScript .= '  if (trim(this.value)) dijit.byId("idActivity").set("value",null); ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idTestSessionType") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  setDefaultPlanningMode(this.value);';
      $colScript .= '</script>';
    } 
    return $colScript;
  }

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $old=$this->getOld();
    if (!trim($this->idProject) and !trim($this->idProduct)) {
      $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdProject') . " " . i18n('colOrProduct')));
    }
    //Gautier #4304
    $proj = new Project($this->idProject,true);
    $projType = new ProjectType($proj->idProjectType);
    if($projType->isLeadProject){
      if (!$this->id) {
        $result .= '<br/>' . i18n ( 'cantCreateATestSessionFromLeadProject' );
      }
      if ($this->id && $old->idProject != $this->idProject) {
        //      ==> Can't associated the activity with lead project 
        $result .= '<br/>' . i18n ( 'cantAssociateATestSessionWithLeadProject' );
      }
    }
    if ($this->TestSessionPlanningElement and $this->TestSessionPlanningElement->id
      and ($this->idActivity!=$old->idActivity or $this->idProject!=$old->idProject)){
      if (trim($this->idActivity)) {
        $parentType='Activity';
        $parentId=$this->idActivity;
      } else {
        $parentType='Project';
        $parentId=$this->idProject;
      }
      $result.=$this->TestSessionPlanningElement->controlHierarchicLoop($parentType, $parentId);
    }
    if ($this->id and $this->id==$this->idTestSession) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if ($this->TestSessionPlanningElement and $this->TestSessionPlanningElement->id){
      $parent=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>'TestSession','refId'=>$this->idTestSession));
      $parentList=$parent->getParentItemsArray();
      if (array_key_exists('#' . $this->TestSessionPlanningElement->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }
    if (trim($this->idActivity)) {
      $parentActivity=new Activity($this->idActivity);
      if ($parentActivity->idProject!=$this->idProject) {
        $result.='<br/>' . i18n('msgParentActivityInSameProject');
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {

  	$old=$this->getOld();
  	if (! trim($this->idRunStatus)) $this->idRunStatus=5;
  	
  	$this->recalculateCheckboxes();
    $this->TestSessionPlanningElement->refName=$this->name;
    $this->TestSessionPlanningElement->idProject=$this->idProject;
    $this->TestSessionPlanningElement->idle=$this->idle;
    $this->TestSessionPlanningElement->done=$this->done;
    $this->TestSessionPlanningElement->cancelled=$this->cancelled;
    if ($this->idActivity and trim($this->idActivity)!='') {
      $this->TestSessionPlanningElement->topRefType='Activity';
      $this->TestSessionPlanningElement->topRefId=$this->idActivity;
      $this->TestSessionPlanningElement->topId=null;
    } else if ($this->idTestSession and trim($this->idTestSession)!=''){
    	$this->TestSessionPlanningElement->topRefType='TestSession';
      $this->TestSessionPlanningElement->topRefId=$this->idTestSession;
      $this->TestSessionPlanningElement->topId=null;
    } else  if ($this->idProject and trim($this->idProject)!=''){
      $this->TestSessionPlanningElement->topRefType='Project';
      $this->TestSessionPlanningElement->topRefId=$this->idProject;
      $this->TestSessionPlanningElement->topId=null;
    } else {
    	$this->TestSessionPlanningElement->topRefType=null;
      $this->TestSessionPlanningElement->topRefId=null;
      $this->TestSessionPlanningElement->topId=null;
    }
    if (trim($this->idProject)!=trim($old->idProject) or trim($this->idActivity)!=trim($old->idActivity)) {
      $this->TestSessionPlanningElement->wbs=null;
      $this->TestSessionPlanningElement->wbsSortable=null;
    }
  	$result=parent::save();
    return $result;
  }
  
  public function copy() {
    $newObj=parent::copy();
    $new=$this->copyTestCasRun($newObj);
    return $new;
  
  }
  public function copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments = false, $withAffectations = false, $toProject = null, $toActivity = null, $copyToWithResult = false, $copyToWithVersionProjects = false) {
    $newObj=parent::copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments, $withAffectations, $toProject, $toActivity, $copyToWithResult, $copyToWithVersionProjects);
    $new=$this->copyTestCasRun($newObj);
    return $new;
  }
  private function copyTestCasRun($newObj) {
    $copyResult=$newObj->_copyResult;
    // Copy TestCaseRun for session
    $newId=$newObj->id;
    $crit=array('idTestSession'=>$this->id);
    $tcr=new TestCaseRun();
    $list=$tcr->getSqlElementsFromCriteria($crit);
    foreach ($list as $tcr) {
      $new=new TestCaseRun();
      $new->idTestSession=$newId;
      $new->idTestCase=$tcr->idTestCase;
      $new->idRunStatus='1';
      $new->sortOrder=$tcr->sortOrder;
      $new->_copy=true;
      $new->save();
    }
    $new=new TestSession($newId);
    $new->_noHistory=true;
    $new->save();
    $new->updateDependencies();
    $new->_copyResult=$copyResult;
    unset($new->_noHistory);
    return $new;
  }
  
  public function updateDependencies() {
  	$this->_noHistory=true;
  	$this->countBlocked=0;
  	$this->countFailed=0;
  	//add atrancoso ticket 120
  	$this->sumPlannedWork=0;
  	//end add atrancoso ticket 120
  	$this->countIssues=0;
  	$this->countPassed=0;
  	$this->countPlanned=0;
  	$this->countTotal=0;
  	foreach($this->_TestCaseRun as $tcr) {
  	  $this->countTotal+=1;
      if ($tcr->idRunStatus==1) {
        $this->countPlanned+=1;
      }
  		if ($tcr->idRunStatus==2) {
  			$this->countPassed+=1;
  		}
  	  if ($tcr->idRunStatus==3) {
        $this->countFailed+=1;
      }
  	  if ($tcr->idRunStatus==4) {
        $this->countBlocked+=1;
      }
      //add atrancoso ticket 120
      $tc=SqlElement::getSingleSqlElementFromCriteria('TestCase',array('id'=>$tcr->idTestCase));
      $this->sumPlannedWork+=($tc->plannedWork)?$tc->plannedWork:0;
      //end add ticket 120 
  	}
  	foreach($this->_Link as $link) {
  		if ($link->ref2Type=='Ticket') {
  			$this->countIssues+=1;
  		}
  	}
  	if ($this->countFailed>0) {
      $this->idRunStatus=3; // failed
    } else if ($this->countBlocked>0) {
      $this->idRunStatus=4; // blocked
    } else if ($this->countPlanned>0) {
      $this->idRunStatus=1; // planned
    } else if ($this->countTotal==0) {
      $this->idRunStatus=5; // empty
    } else {
      $this->idRunStatus=2; // passed
    }  
  	$this->save();
  	
  }
  
   public function getCalculatedItem(){
   	 if ($this->countTotal!=0) {
       $this->pctPlanned='<i>('.htmlDisplayPct(round($this->countPlanned/$this->countTotal*100)).')</i>';
       $this->pctPassed='<i>('.htmlDisplayPct(round($this->countPassed/$this->countTotal*100)).')</i>';
       $this->pctFailed='<i>('.htmlDisplayPct(round($this->countFailed/$this->countTotal*100)).')</i>';
       $this->pctBlocked='<i>('.htmlDisplayPct(round($this->countBlocked/$this->countTotal*100)).')</i>';
     }
     if ($this->id) {
       $name=SqlList::getNameFromId('RunStatus', $this->idRunStatus,false);
       $this->runStatusName=i18n($name);
       $this->runStatusIcon='<img src="../view/css/images/icon'.ucfirst($name).'22.png" />';
     }
  }
  
  public function drawSpecificItem($item){
//scriptLog("Project($this->id)->drawSpecificItem($item)");   
    $result="";
    if ($item=='separator_progress') {
    	$result .='<div style="height:5px;">&nbsp;</div>';
      $result .='<div class="section" style="height:14px;">';
      $result .="&nbsp;".i18n('menuTestCase')."&nbsp;";
      $result .='</div>';
      return $result;
    }
  }  
}
?>