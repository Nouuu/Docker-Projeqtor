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
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
require_once('_securityCheck.php');
class ProjectMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $_spe_rf; 
  public $name;
  public $idProjectType;
  public $idOrganization;
  public $idCategory;
  public $organizationInherited;
  public $organizationElementary;
  public $codeType;
  public $idClient;
  public $idContact;
  public $projectCode;
  public $contractCode;
  public $clientCode;
  public $idProject;
  public $idSponsor;
  public $idResource;
  public $idUser;
  public $creationDate;
  public $lastUpdateDateTime;
  public $color;
  public $longitude;
  public $latitude;
  public $description;
  public $objectives;
  public $_sec_Progress;
  public $ProjectPlanningElement; // is an object
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_sec_treatment;
  public $idStatus;
  public $idHealth;
  public $idQuality;
  public $idTrend;
  public $idOverallProgress;
  public $fixPlanning;
  public $_lib_helpFixPlanning;
  public $fixPerimeter;
  public $_lib_helpFixPerimeter;
  public $isUnderConstruction;
  public $_lib_helpUnderConstruction;
  public $excludeFromGlobalPlanning;
  public $_lib_helpExcludeFromGlobalPlanning;
  public $commandOnValidWork;
  public $_lib_helpCommandOnValidWork;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  
  public $_sec_ProductprojectProducts;
  public $_ProductProject=array();
  
  public $_sec_VersionprojectVersions;
  public $_VersionProject=array();
  
  public $_sec_Subprojects;
  public $_spe_subprojects;
  
  public $_sec_restrictTypes;
  public $_spe_restrictTypes;
  
  public $_sec_predecessor;
  public $_Dependency_Predecessor=array();
  
  public $_sec_successor;
  public $_Dependency_Successor=array();
  
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();

  // hidden
  public $sortOrder;
// ELIOTT - LEAVE SYSTEM  
  public $isLeaveMngProject=0;//Indicate that if it's the project dedicated to store the leave as work
// ELIOTT - LEAVE SYSTEM  
  public $_nbColMax=3;
  
  private static $_administrativeProjectList=null;
  public static $_deleteProjectInProgress=false;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="wbsSortable" from="ProjectPlanningElement" formatter="sortableFormatter" width="10%" >${wbs}</th>
    <th field="name" width="30%" >${projectName}</th>
    <th field="nameProjectType" width="15%" >${type}</th>
    <th field="color" width="5%" formatter="colorFormatter">${color}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="progress" from="ProjectPlanningElement" width="10%" formatter="percentFormatter">${progress}</th>
    <th field="plannedEndDate" from="ProjectPlanningElement" width="10%" formatter="dateFormatter">${plannedEnd}</th>  
    ';
  
  private static $_fieldsTooltip = array(
      "fixPlanning"=> "tooltipFixPlanning",
      "fixPerimeter"=> "tooltipFixPerimeter",
      "isUnderConstruction" => "tooltipUnderConstruction",
      "excludeFromGlobalPlanning" => "tooltipExcludeFromGlobalPlanning",
      "commandOnValidWork"=> "tooltipCommandOnValidWork",
  );
// Removed in 1.2.0 
//     <th field="wbs" from="ProjectPlanningElement" width="5%" >${wbs}</th>
// Removed in 2.0.1
//  <th field="nameRecipient" width="10%" >${idRecipient}</th>
  

  private static $_fieldsAttributes=array("name"=>"required",                                   
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "handled"=>"nobr",
                                  "sortOrder"=>"hidden",
                                  "codeType"=>"hidden",
                                  "idProjectType"=>"required",
                                  "longitude"=>"hidden", "latitude"=>"hidden",
                                  "idStatus"=>"required",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "organizationInherited"=>"hidden",
                                  "organizationElementary"=>"hidden",
                                  "fixPlanning"=>"nobr",
                                  "fixPerimeter"=>"nobr",
                                  "isUnderConstruction"=>"nobr",
                                  "excludeFromGlobalPlanning"=>"nobr",
                                  "commandOnValidWork"=>"nobr",
  		                            "isLeaveMngProject"=>"hidden",
  		                             "locked"=>"hidden"
  );   
 
  private static $_colCaptionTransposition = array('idResource'=>'manager',
   'idProject'=> 'isSubProject',
   'idProjectType'=>'type',
   'idContact'=>'billContact',
   'idUser'=>'issuer');

  private static $_subProjectList=array();
  private static $_subProjectFlatList=array();
  private static $_drawSubProjectsDone=array();
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    if ($id=='*') {$id='';}
  	parent::__construct($id,$withoutDependentObjects);
  	// MTY - LEAVE SYSTEM
  	if (isLeavesSystemActiv()) {
  	  global $doNotRestrictLeave;
  		// Can't see or modify a lot of attributs if this project is the leave project
  		if ($this->isLeaveMngProject==1 and ! $doNotRestrictLeave) {
  			self::$_fieldsAttributes['idProjectType']='readonly';
  			self::$_fieldsAttributes['idProject']='hidden';
  			self::$_fieldsAttributes['idStatus']='hidden';
  			self::$_fieldsAttributes['idHealth']='hidden';
  			self::$_fieldsAttributes['isUnderConstruction']='hidden';
			  self::$_fieldsAttributes['fixPerimeter']='hidden';
			  self::$_fieldsAttributes['excludeFromGlobalPlanning']='hidden';			
  			self::$_fieldsAttributes['handled']='hidden';
  			self::$_fieldsAttributes['handledDate']='hidden';
  			self::$_fieldsAttributes['done']='hidden';
  			self::$_fieldsAttributes['doneDate']='hidden';
  			self::$_fieldsAttributes['idle']='hidden';
  			self::$_fieldsAttributes['idleDate']='hidden';
  			self::$_fieldsAttributes['cancelled']='hidden';
  			self::$_fieldsAttributes['idClient']='hidden';
  			self::$_fieldsAttributes['idContact']='hidden';
  			self::$_fieldsAttributes['idSponsor']='hidden';
  			self::$_fieldsAttributes['idResource']='hidden';
  			self::$_fieldsAttributes['fixPlanning']='hidden';
  			self::$_fieldsAttributes['commandOnValidWork']='hidden';
  			self::$_fieldsAttributes['ProjectPlanningElement']='hidden';
			  self::$_fieldsAttributes['_sec_treatment']='hidden';
	
  			unset($this->_sec_Progress);
  			unset($this->_sec_Affectations);
  			unset($this->_spe_affectations);
  			unset($this->_lib_cancelled);
  			unset($this->_sec_ProductprojectProducts);
  			unset($this->_ProductProject);
  			unset($this->_sec_VersionprojectVersions);
  			unset($this->_VersionProject);
  			unset($this->_sec_Subprojects);
  			unset($this->_spe_subprojects);
  			unset($this->_sec_restrictTypes);
  			unset($this->_spe_restrictTypes);
  			unset($this->_sec_predecessor);
  			unset($this->_Dependency_Predecessor);
  			unset($this->_sec_successor);
  			unset($this->_Dependency_Successor);
  		}
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
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }  

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("idleDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
      $colScript .= '    }';
//       $colScript .= '    if (! dijit.byId("done").get("checked")) {';
//       $colScript .= '      dijit.byId("done").set("checked", true);';
//       $colScript .= '    }';  
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idleDate").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="done") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("doneDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("doneDate").set("value", null); ';
//       $colScript .= '    if (dijit.byId("idle").get("checked")) {';
//       $colScript .= '      dijit.byId("idle").set("checked", false);';
//       $colScript .= '    }'; 
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idProject") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("ProjectPlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="idProjectType") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  setDefaultCategory(this.value);';
      $colScript .= '</script>';
    }
    
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
// MTY - LEAVE SYSTEM

static function getLeaveProject() {
    if (!getSessionValue('leaveProject')) {
        $crit=['isLeaveMngProject' => '1'];
        $prj = SqlElement::getSingleSqlElementFromCriteria('Project', $crit, true);
        if (isset($prj->id)) {
            setSessionValue('idLeaveProject', $prj->id);
            setSessionValue('leaveProject', $prj);
        } else {
            setSessionValue('idLeaveProject', null);
            setSessionValue('leaveProject', null);
        }    
    } 
    return getSessionValue('leaveProject');
}
  
  /** ==========================================================================
   * Get the project's id that is the leave project (ie : isLeaveMngProject=1)
   * If not done, store in session this id
   * @return integer The leave project's id
*/
static function getLeaveProjectId() {
    if (!getSessionValue('idLeaveProject')) {
        Project::getLeaveProject();
    }
    $ret = getSessionValue('idLeaveProject');
    return $ret;
}

private function setLeaveProjectId() {
    setSessionValue('idLeaveProject', $this->id);
}

private function setLeaveProject() {
    setSessionValue('leaveProject', $this);
}


/** ==========================================================================
 * Return true if the leave Project is visible for the connected user 
 * @return boolean True if leave system is activ and user is administrator
*/
static function isProjectLeaveVisible() {
    $user = getSessionUser();
    $ret = (($user->idProfile==1 and isLeavesSystemActiv())?true:false);
    return $ret;
}

/** ==========================================================================
   * Return true if the project that have the id passed in parameter is the leave project
   * @param $id integer The project's id to test if it's the leave project
   * @return boolean
*/
static function isTheLeaveProject($id=null) {
    if ($id==null) {
        return false;        
    }
    $ret = ($id==self::getLeaveProjectId()?true:false);
    return $ret;
}

// MTY - LEAVE SYSTEM

  
  /** ==========================================================================
   * Retrieves the hierarchic sub-projects of the current project
   * @return an array of Projects as sub-projects
   */
  public function getSubProjects($limitToActiveProjects=false, $withoutDependantElement=false) {
  scriptLog("Project($this->id)->getSubProjects($limitToActiveProjects)");  	
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
    if ($this->id=='*') {
      $crit['idProject']='';
    } else {
      $crit['idProject']=$this->id;
    }

    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    $sorted=SqlList::getListWithCrit('Project',$crit,'name');
    //$subProjects=$this->getSqlElementsFromCriteria($crit, false);
    //uasort($subProjects,'wbsProjectSort');
    $subProjects=array();
    foreach($sorted as $projId=>$projName) {
// MTY - LEAVE SYSTEM
        if (isLeavesSystemActiv()) {
          if (self::isTheLeaveProject($projId) && !self::isProjectLeaveVisible()) {continue;}
        }
// MTY - LEAVE SYSTEM        
      $subProjects[$projId]=new Project($projId, $withoutDependantElement);
    }
    return $subProjects;
  }

  /** ==========================================================================
   * Retrieves the hierarchic sub-projects of the current project
   * @return an array of Projects as sub-projects
   */
  public function getSubProjectsList($limitToActiveProjects=false) {
  scriptLog("Project($this->id)->getSubProjectsList(limitToActiveProjects=$limitToActiveProjects)");
    if ($this->id==null or $this->id=='') {
      return array();
    }
    $crit=array();
    if ($this->id=='*') {
      $crit['idProject']='';
    } else {
      $crit['idProject']=$this->id;
    }
    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    $sorted=SqlList::getListWithCrit('Project',$crit,'name', null, ! $limitToActiveProjects);
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
        // For the Leave Project, if it's not visible by connected user ==> not taken into account
        if (array_key_exists(self::getLeaveProjectId(), $sorted) && !self::isProjectLeaveVisible()) {
            unset($sorted[self::getLeaveProjectId()]);
        }
    }
// MTY - LEAVE SYSTEM
    
    return $sorted;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the hierarchic sub-projects of the current project
   * @return an array containing id, name, subprojects (recursive array)
   */
  public function getRecursiveSubProjects($limitToActiveProjects=false) {
    scriptLog("Project($this->id)->getRecursiveSubProjects($limitToActiveProjects)");
    $key=$this->id.'-'.(($limitToActiveProjects)?'active':'all');
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
      if (self::isTheLeaveProject($this->id) && !self::isProjectLeaveVisible()) {return null;}
    }
// MTY - LEAVE SYSTEM
    if (isset(self::$_subProjectList[$key])) {
    	return self::$_subProjectList[$key];
    }    	
    $crit=array('idProject'=>$this->id);
    if ($limitToActiveProjects) {
      $crit['idle']='0';
    }
    //$obj=new Project();
    $subProjects=$this->getSqlElementsFromCriteria($crit, false,null,null,null,true) ;
    $subProjectList=null;
    foreach ($subProjects as $subProj) {
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
      if (self::isTheLeaveProject($subProj->id) && !self::isProjectLeaveVisible()) {continue;}
    }  
// MTY - LEAVE SYSTEM
      $recursiveList=null;
      $recursiveList=$subProj->getRecursiveSubProjects($limitToActiveProjects);
      $arrayProj=array('id'=>$subProj->id, 'name'=>$subProj->name, 'subItems'=>$recursiveList);
      $subProjectList[]=$arrayProj;
    }
    self::$_subProjectList[$key]=$subProjectList;
    return $subProjectList;
  }
  
  /** ==========================================================================
   * Recusively retrieves all the sub-projects of the current project
   * and presents it as a flat array list of id=>name
   * @return an array containing the list of subprojects as id=>name
   * 
   */
  public function getRecursiveSubProjectsFlatList($limitToActiveProjects=false, $includeSelf=false) {
  scriptLog("Project($this->id)->getRecursiveSubProjectsFlatList($limitToActiveProjects,$includeSelf)");   	
    $key=$this->id.'-'.(($limitToActiveProjects)?'active':'all').(($includeSelf)?'-withSelf':'');
    if (isset(self::$_subProjectFlatList[$key])) {
      return self::$_subProjectFlatList[$key];
    }
    $tab=$this->getRecursiveSubProjects($limitToActiveProjects);
    $list=array();
    if ($includeSelf) {
      $list[$this->id]=$this->name;
    }
    if ($tab) {
      foreach($tab as $subTab) {
        $id=$subTab['id'];
        $name=$subTab['name'];
        $list[$id]=$name;
        $subobj=new Project();
        $subobj->id=$id;
        $sublist=$subobj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
        if ($sublist) {
          $list=array_merge_preserve_keys($list,$sublist);
        }
      }
    }
    self::$_subProjectFlatList[$key]=$list;
    return $list;
  }

  private static $topProjectListArray=array();
  public function getTopProjectList($includeSelf=false) {
  scriptLog("Project($this->id)->getTopProjectList($includeSelf)");
    if (isset(self::$topProjectListArray[$this->id.'#'.$includeSelf])) {
    	return self::$topProjectListArray[$this->id.'#'.$includeSelf];	
    }
    if ($includeSelf) {
// MTY - LEAVE SYSTEM
        if (isLeavesSystemActiv()) {
            if (!self::isTheLeaveProject($this->id) || self::isProjectLeaveVisible()) {
      return array_merge(array($this->id),$this->getTopProjectList(false));
    }
        } else {
// MTY - LEAVE SYSTEM        
            return array_merge(array($this->id),$this->getTopProjectList(false));            
        }
    }
    if (! $this->idProject) {
      return array();
    } else {
      $topProj=new Project($this->idProject);
      $topList=$topProj->getTopProjectList();
      $result=array_merge(array($this->idProject),$topList);
      self::$topProjectListArray[$this->id.'#'.$includeSelf]=$result;
      return $result;
    }
  }
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
//scriptLog("Project($this->id)->drawSpecificItem($item)");
    global $drawCheckBox;
    $result="";
    if ($item=='subprojects') {
      if(isNewGui()){
        $result .="<table style='margin-left:20px;'><tr><td >";
      }else{
        $result .="<table><tr><td class='label' valign='top'><label>" . i18n('subProjects') . "&nbsp;:&nbsp;</label>";
        $result .="</td><td>";
      }
      if ($this->id) {
        $drawCheckBox = 'false';
        $result .= $this->drawSubProjects();
      }
      $result .="</td></tr></table>";
      return $result;
    /*} else if ($item=='affectations') {
      $aff=new Affectation();
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('resources') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        $result .= $aff->drawAffectationList(array('idProject'=>$this->id,'idle'=>'0'),'Resource');
      }
      $result .="</td></tr></table>";
      $result .="<table><tr><td class='label' valign='top'><label>" . i18n('contacts') . "&nbsp;:&nbsp;</label>";
      $result .="</td><td>";
      if ($this->id) {
        $result .= $aff->drawAffectationList(array('idProject'=>$this->id,'idle'=>'0'),'Contact');
      }
      $result .="</td></tr></table>";
      return $result;*/
    } else if ($item=='affectations') {
      $aff=new Affectation();
      $critArray=array('idProject'=>(($this->id)?$this->id:'0'));
      $affList=$aff->getSqlElementsFromCriteria($critArray, false);
      drawAffectationsFromObject($affList, $this, 'ResourceAll', false);  
      drawAffectationsFromObject($affList, $this, 'Contact', false);
      drawAffectationsFromObject($affList, $this, 'User', false);
      return $result;
    } else if ($item=='rf') { 
    	global $flashReport, $print;
    	if (! $print and $this->id and isset($flashReport) and ($flashReport==true or $flashReport=='true')) {
    		$user=getSessionUser();
    		$crit=array('idProfile'=>$user->getProfile($this->id), 'idReport'=>51);
    		$hr=SqlElement::getSingleSqlElementFromCriteria('HabilitationReport', $crit);
    		if ($hr and $hr->allowAccess=='1') {
	    		$top=30;$left=10;
	    		$result.='<div style="position: absolute; top:'.$top.'px;left:'.$left.'px;">'
	    		  . '<button id="printButtonRf" dojoType="dijit.form.Button" showlabel="false"'
	    		  . ' title="'.i18n('flashReport').'"'
	          . ' iconClass="iconFlash" >'
	          . ' <script type="dojo/connect" event="onClick" args="evt">'
	          . '  showPrint("../report/projectFlashReport.php?idProject='.htmlEncode($this->id).'");'
	          . ' </script>'
	          . '</button>'  
	          . '<button id="printButtonPefRf" dojoType="dijit.form.Button" showlabel="false"'
	          . ' title="'.i18n('flashReport').'"'
	          . ' iconClass="iconFlashPdf" >'
	          . ' <script type="dojo/connect" event="onClick" args="evt">'
	          . '  showPrint("../report/projectFlashReport.php?idProject='.htmlEncode($this->id).'", null, null, "pdf");'
	          . ' </script>'
	          . '</button>'  
	          . '</div>';
    		}
        return $result;
    	}
    } else if ($item=='restrictTypes') {
      global $print;
      if (!$this->id) return '';
      if (!$print) {
        $result.= '<button id="buttonRestrictTypes" dojoType="dijit.form.Button" showlabel="true"'
          . ' title="'.i18n('helpRestrictTypesProject').'" iconClass="iconType16" class="roundedVisibleButton" >'
          . '<span>'.i18n('restrictTypes').'</span>'
          . ' <script type="dojo/connect" event="onClick" args="evt">'
          . '  var params="&idProject='.$this->id.'";'
          . '  params+="&idProjectType="+dijit.byId("idProjectType").get("value");'    
          . '  loadDialog("dialogRestrictTypes", null, true, params);'
          . ' </script>'
          . '</button>';
        $result.= '<span style="font-size:80%">&nbsp;&nbsp;&nbsp;('.i18n('helpRestrictTypesProjectInline').')</span>';
      }
      $result.='<table style="witdh:100%"><tr><td class="label" style="width:220px">'.i18n('existingRestrictions').Tool::getDoublePoint().'</td><td>';
      $result.='<div id="resctrictedTypeClassList" style="position:relative;left:5px;top:2px">';
      $list=Type::getRestrictedTypesClass($this->id,null,null);
      $cpt=0;
      foreach ($list as $cl) {
        $cpt++;
        $result.=(($cpt>1)?', ':'').$cl;
      }
      $result.='</div>';
      $result.='</td></tr><tr><td colspan="2">&nbsp;</td></tr></table>';
      return $result;
    }
  }
  

  /** =========================================================================
   * Specific function to draw a recursive tree for subprojects
   * @return string the html table for the given level of subprojects
   *  must be redefined in the inherited class
   */  
  public function drawSubProjects($selectField=null, $recursiveCall=false, $limitToUserProjects=false, $limitToActiveProjects=false, $level=0) {
    scriptLog("Project($this->id)->drawSubProjects(selectField=$selectField, recursiveCall=$recursiveCall, limitToUserProjects=$limitToUserProjects, limitToActiveProjects=$limitToActiveProjects)");
    global $outMode, $drawCheckBox;
  	self::$_drawSubProjectsDone[$this->id]=$this->name;
    if ($limitToUserProjects) {
      $user=getSessionUser();
      if (! $user->_accessControlVisibility) {
        $user->getAccessControlRights(); // Force setup of accessControlVisibility
      }
      if ($user->_accessControlVisibility != 'ALL') {      
        $visibleProjectsList=$user->getHierarchicalViewOfVisibleProjects($limitToActiveProjects);
      } else {
      	$visibleProjectsList=array();
      }
      $reachableProjectsList=$user->getVisibleProjects($limitToActiveProjects);
    } else {  
      $visibleProjectsList=array();
      $reachableProjectsList=array();
    }  
    $result="";
    $clickEvent=' onClick=""';
    if ($outMode=='html' or $outMode=='pdf') $clickEvent='';
    if ($limitToUserProjects and $user->_accessControlVisibility != 'ALL' and ! $recursiveCall) {
    	$subList=array();
    	foreach($visibleProjectsList as $idP=>$nameP) {
    		$split=explode('#',$nameP);
    		if (strpos($split[0],'.')==0) {
    		    $proj = new Project(substr($idP,1));
      	        $subList[substr($idP,1)]=$proj->name;
    		}
    	}
    } else {
  	  $subList=$this->getSubProjectsList($limitToActiveProjects,true);
    }
    if ($selectField!=null and ! $recursiveCall) { 
      $result .= '<table ><tr><td>';
      $clickEvent=' onClick=\'setSelectedProject("*", "<i>' . i18n('allProjects') . '</i>", "' . $selectField . '");\' ';
      if ($outMode=='html' or $outMode=='pdf') $clickEvent='';
      $result .= '<div ' . $clickEvent . ' class="'.(($outMode=='html' or $outMode=='pdf')?'':'menuTree').'" style="width:100%;height:22px;margin-top:-3px;padding-top:3px;margin-bottom:5px;">';
      $result .= '<i>' . i18n('allProjects') . '</i>';
      $result .= '</div></td></tr></table>';
    }
    if(isNewGui()){
      $result .='<table style="width:100%;margin-top:10px;">';
    }else{
      $result .='<table style="width:100%;">';
    }
    $handled = 0;
    if (count($subList)>0) {
      $level++;
      $showHandlelProject=Parameter::getUserParameter('projectSelectorShowHandlelProject');
      foreach ($subList as $idPrj=>$namePrj) {
        if($showHandlelProject){
          $subProj = new Project($idPrj);
          if($subProj->handled == '1' and $subProj->done == '0'){
          	$handled = 1;
          }else{
          	$checkList = $subProj->getRecursiveSubProjectsFlatList($limitToActiveProjects);
          	if(count($checkList)>0){
          		foreach ($checkList as $idSubPrj=>$nameSubPrj){
          			$obj = new Project($idSubPrj);
          			if($obj->handled == '1' and $obj->done == '0'){
          				$handled = 1;
          			}
          		}
          	}
          }
          if($handled == 0){
          	continue;
          }
          $handled = 0;
        }
        $showLine=true;
        $reachLine=true;
        if (array_key_exists($idPrj,self::$_drawSubProjectsDone)) {
        	$showLine=false;
        }
        if ($limitToUserProjects) {
          if ($user->_accessControlVisibility != 'ALL') {
            if (! array_key_exists('#' . $idPrj,$visibleProjectsList)) {
              $showLine=false;
            }
            if (! array_key_exists($idPrj,$reachableProjectsList)) {
              $reachLine=false;
            }
          }  
        }
        if ($showLine) {
          $checked = '';
          if(strpos(getSessionValue('project'), ',') != null){
            $arrayProj = explode(',', getSessionValue('project'));
            if(in_array($idPrj, $arrayProj))$checked='checked';
          }else{
            if (getSessionValue('project')==$idPrj) $checked='checked';
            else $checked='';
          }
        	$prj=new Project($idPrj);
        	$left = 0;
        	if($level > 1){
        	  $left = -(20*$level);
        	  if($level >= 2){
        	  	$left -= ($level-2)*20;
        	  }
        	}
          $result .='<tr style="height:25px">';
          if(!$drawCheckBox){
            $result .='<td valign="top" width="20px"><div dojoType="dijit.form.CheckBox" type="checkbox" class="projectSelectorCheckbox" style="float:left;position:relative;left:'.$left.'px" id="checkBoxProj'.$idPrj.'" value="'.$idPrj.'" '.$checked.'>';
            $result .='</div>';
            $result .='<input type="hidden" id="projectSelectorName'.$idPrj.'" name="projectSelectorName'.$idPrj.'" value="'.$prj->name.'"></td>';
          }
          if(!isNewGui()){
            $result .='<td valign="top" width="20px"><img src="css/images/iconList16.png" height="16px" /></td>';
          }else{
            $styleMargin = "margin-right:5px;margin-top:3px;";
            if(!$drawCheckBox){
              $styleMargin = "";
            }
            if ($outMode!='pdf') $result .='<td valign="top" width="20px"><div style="'.$styleMargin.'" class="imageColorNewGuiNoSelection iconProject iconSize16"></div></td>';
            else $result .='<td valign="top" width="20px"><img src="../view/css/images/iconList16.png" style="width:16pxheight:16px" />&nbsp;</td>';
          }
          if ($selectField==null) {
            $result .= '<td valign="top" class="'.(($outMode=='html' or $outMode=='pdf')?'':'display').'"  NOWRAP>' . (($outMode=='html' or $outMode=='pdf')?htmlEncode($prj->name):htmlDrawLink($prj));
          } else if (! $reachLine) {
            $result .= '<td style="#AAAAAA;" NOWRAP><div class="'.(($outMode=='html' or $outMode=='pdf')?'':'display').'" style="width: 100%;">' . htmlEncode($prj->name) . '</div>';
          } else {
            $clickEvent=' onClick=\'setSelectedProject("' . htmlEncode($prj->id) . '", "' . htmlEncode($prj->name,'parameter') . '", "' . $selectField . '");\' ';
            if ($outMode=='html' or $outMode=='pdf') $clickEvent='';
            $result .='<td>';
            $borderNone = "";
            if(isNewGui()){
              $borderNone="border:none !important;";
            }
            $result .='<div ' . $clickEvent . ' class="'.(($outMode=='html' or $outMode=='pdf')?'':'menuTree').'" style=" '.$borderNone.' width:100%;color:black;height:25px;margin-top:-6px;padding-top:5px">';
            $result .= htmlEncode($prj->name);
            $result .='</div>';
          }
          $result .= $prj->drawSubProjects($selectField,true,$limitToUserProjects,$limitToActiveProjects, $level);
          $result .= '</td></tr>';
        }
      }
    }
    $result .='</table>';
    return $result;
  }

  public function drawProjectsList($critArray) {
//scriptLog("Project($this->id)->drawProjectsList(implode('|',$critArray))");  	
    $result="<table>";
    $prjList=$this->getSqlElementsFromCriteria($critArray, false);
    foreach ($prjList as $prj) {
// MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
        // For the Leave Project, if it's not visible by connected user ==> not taken into account
        if (self::isTheLeaveProject($prj->id) && !self::isProjectLeaveVisible()) {continue;}
    }
// MTY - LEAVE SYSTEM        
      $result.= '<tr><td valign="top" width="20px"><img src="css/images/iconList16.png" height="16px" /></td><td>';
      $result.=htmlDrawLink($prj);
      $result.= '</td></tr>';
    }
    $result .="</table>";
    return $result; 
  }
  
   /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    // #305 : need to recalculate before dispatching to PE
    $old=$this->getOld();
    $this->recalculateCheckboxes();
    if (!$this->id) {
      //$this->isUnderConstruction=1; // Will post this later...
    }
    if(SqlList::getFieldFromId("Status", $this->idStatus, "setHandledStatus")!=0) {
      $this->isUnderConstruction=0;
    }
    //$old=$this->getOld();
    //$oldtype=new ProjectType($old->idProjectType);
    $type=new ProjectType($this->idProjectType);
    
    $noMoreAdministrative=false;
    if ($this->codeType=='ADM' and $type->code!='ADM') {
    	$noMoreAdministrative=true;
    }
    $this->codeType=$type->code;
    
    $this->ProjectPlanningElement->refName=$this->name;
    $this->ProjectPlanningElement->idProject=$this->id;
    $this->ProjectPlanningElement->idle=$this->idle;
    $this->ProjectPlanningElement->done=$this->done;
    $this->ProjectPlanningElement->cancelled=$this->cancelled;
    if ($this->idProject and trim($this->idProject)!='') {
      $this->ProjectPlanningElement->topRefType='Project';
      $this->ProjectPlanningElement->topRefId=$this->idProject;
      $this->ProjectPlanningElement->topId=null;
    } else {
      $this->ProjectPlanningElement->topId=null;
      $this->ProjectPlanningElement->topRefType=null;
      $this->ProjectPlanningElement->topRefId=null;
    }
    if (trim($this->idProject)!=trim($old->idProject)) {    	
      $this->ProjectPlanningElement->wbs=null;
      $this->ProjectPlanningElement->wbsSortable=null;
      $this->sortOrder=null;
    } else if (! $this->sortOrder and $this->ProjectPlanningElement->wbsSortable) {
      $this->sortOrder=$this->ProjectPlanningElement->wbsSortable;
    }
    $this->ProjectPlanningElement->color=$this->color;
    
    // Organization
    if ( (!$this->id or $this->idProject!=$old->idProject) and ! trim($this->idOrganization) and $this->idProject ) {
      $topProj=new Project($this->idProject,true);
      $this->idOrganization=$topProj->idOrganization;
    }
    $subProj=$this->getSubProjects(false,false);
    $this->organizationElementary=(count($subProj)==0)?0:1;
    $this->ProjectPlanningElement->idOrganization=$this->idOrganization;
    $this->ProjectPlanningElement->organizationInherited=$this->organizationInherited;
    $this->ProjectPlanningElement->organizationElementary=$this->organizationElementary;
    
    // SAVE
    $result = parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    } 
    
    if (! $old->id or $this->idle!=$old->idle or $this->idProject!=$old->idProject) {
    	if ($old->idProject) {
        User::resetAllVisibleProjects($this->id, null);
    	} else {
    		User::resetAllVisibleProjects(null, null);
    	}
    }
    
    if(!$this->isCopyInProgress()){
    //gautier #2577
      if (Parameter::getGlobalParameter('allocateResponsibleToProject')=="YES") {
          $crit=array('idProject'=>$this->id, 'idResource'=>$this->idResource);
          $aff=new Affectation();
          $affLst=$aff->getSqlElementsFromCriteria($crit, false);
          if(count($affLst) == 0 ){
            if($this->idResource != null){
              $affManag=new Affectation();
              $affManag->idProject=$this->id;
              $affManag->idResource=$this->idResource;
              $affManag->save();
            }
          }
      }
    } 
    // Dispatch Organization 
    if ($this->idOrganization) {
      $this->dispatchOrganizationToSubProjects($old->idOrganization);
    }
    
    if ($this->idle) {
      $crit=array('idProject'=>$this->id, 'idle'=>'0');
      $vp=new VersionProject();
      $vpLst=$vp->getSqlElementsFromCriteria($crit, false);
      foreach ($vpLst as $vp) {
        $vp->idle=$this->idle;
        $vp->save();
      }
    }
    // Create affectation for Manager.
    if ($this->idUser) {
      if (securityGetAccessRight('menuProject', 'update', null)!="ALL"){
        $id=($this->id)?$this->id:Sql::$lastQueryNewid;
        $crit=array('idProject'=>$id, 'idResource'=>$this->idUser);
        $aff=SqlElement::getSingleSqlElementFromCriteria('Affectation', $crit);
        if ( ! $aff or ! $aff->id) {
        	$aff=new Affectation();
        	$aff->_automaticCreation=true;
        	$aff->idResource=$this->idUser;
        	$aff->idProject=$id;
        	$aff->idProfile=getSessionUser()->idProfile;
        	if (securityGetAccessRightYesNo('menuProject', 'update', null, getSessionUser()) != "YES") {
        	  $crit=array('idProject'=>$this->idProject, 'idResource'=>$this->idUser);
        	  $affTop=SqlElement::getSingleSqlElementFromCriteria('Affectation', $crit);
        	  $aff->idProfile=$affTop->idProfile;
        	}
        	$resAff=$aff->save();
        } else if (! $this->idle and $aff->idle) {
          $aff->_automaticCreation=true;
        	$aff->idle=0;
        	$resAff=$aff->save();
        }
      }
    }

    if ($this->idle) {
      Affectation::updateIdle($this->id, null);
    }
    if ($noMoreAdministrative) {
    	 $ass=new Assignment();
    	 $lstAss=$ass->getSqlElementsFromCriteria(array('idProject'=>$this->id));
    	 foreach ($lstAss as $ass) {
    	 	 if ($ass->realWork==0 and $ass->leftWork==0) {
    	 	 	 $ass->delete();
    	 	 }
    	 }
    }
    //gautier #2486
    if(($this->idle)==1){
      $plw=new PlannedWork();
      $clause=('idProject ='.$this->id);
      $purg=$plw->purge($clause);
    }
    //damian
    if($this->fixPerimeter != $old->fixPerimeter){
    	$listProjPerim = $this->getRecursiveSubProjectsFlatList();
    	foreach ($listProjPerim as $id=>$name){
    		$subProj = new Project($id);
    		if($this->id == $subProj->idProject){
    		  $subProj->fixPerimeter = $this->fixPerimeter;
    		}
    		$subProj->save();
    	}
    }
    // MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
    	if ($old->isLeaveMngProject==0 && $this->isLeaveMngProject==1) {
    		$this->setLeaveProjectId();
    		$this->setLeaveProject();
    	}
    }
    // MTY - LEAVE SYSTEM
    
    // report name to project situation
    if ($this->name!=$old->name) {
      $ps=SqlElement::getSingleSqlElementFromCriteria('ProjectSituation', array('idProject'=>$this->id));
      if ($ps->id) {
        $ps->name=i18n('ProjectSituation').' - '.$this->name;
        $ps->save();
      }
    }
    if($this->commandOnValidWork != $old->commandOnValidWork){
      //$this->ProjectPlanningElement->updateSynthesisObj();
      $this->updateValidatedWork(true);
    }
    return $result; 

  }
  public function delete() {
    // MTY - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
    	if ($this->isLeaveMngProject) {
    		$returnValue = i18n('cantDeleteTheLeaveMngProject');
    		$returnStatus = "INVALID";
    		$returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
    		$returnValue .= '<input type="hidden" id="lastOperation" value="save" />';
    		$returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
    		return $returnValue;
    	}
    }
    // MTY - LEAVE SYSTEM
  	$result = parent::delete();
  	if($this->idProject){
  	  $proj = new Project($this->idProject);
  	  $proj->ProjectPlanningElement->updateCA();
  	}
  	 if(getLastOperationStatus($result)=="OK"){
  	  if($this->isSelectedProjectMultiple()){
  	    $selectedProj = explode(',', getSessionValue('project'));
  	    if(in_array($this->id, $selectedProj)){
  	      $pos = array_search($this->id, $selectedProj);
  	      unset($selectedProj[$pos]);
  	      if(count($selectedProj) > 0){
  	        setSessionValue('project', implode(',', $selectedProj));
  	      }else{
  	        setSessionValue('project', '*');
  	      }
  	    }
  	  }
  	 User::resetAllVisibleProjects($this->id,null);
  	}
    return $result;
  }
  
  public function dispatchOrganizationToSubProjects($oldOrganization) {
    $subProj=$this->getSubProjects(false,false); // Must refresh subProject, to be sure to get latest values (for instance when moving project, retreive correct WBS)
    foreach ($subProj as $sp) {
      if ( ! $sp->idOrganization or ($sp->organizationInherited and $sp->idOrganization==$oldOrganization) ) {
        $sp->idOrganization=$this->idOrganization;
        $sp->organizationInherited=1;
        $resSp=$sp->simpleSave();
        $sp->dispatchOrganizationToSubProjects($oldOrganization);
      } else if ($sp->organizationInherited) {
        $sp->organizationInherited=0;
        $sp->simpleSave();
      }
    }
  }
  // Ticket #1175
  public function updateValidatedWork($force=false) {
  	if (! $this->id) return;
  	$consolidateValidated=Parameter::getGlobalParameter('consolidateValidated');
  	$lst=null;
  	$sumValidatedWork=0;
  	$sumValidatedCost=0;
  	$projList = $this->getRecursiveSubProjectsFlatList(true, true);
  	$projList = '('.implode(',', array_flip($projList)).')';
  	if($this->commandOnValidWork == 1){
  	  $order=new Command();
  	  $queryWhere='idProject in ' . $projList . ' AND cancelled=0';
  	  $sumValidatedWork=$order->sumSqlElementsFromCriteria('validatedWork', null, $queryWhere);
  	  $sumValidatedCost=$order->sumSqlElementsFromCriteria('totalUntaxedAmount', null, $queryWhere);
  	}else if ($force==true) { // Update when changing mode (commandOnValidWork) or to consolidate Parent
  	  $lst=null;
  	  $prj=new Project();
  	  $queryWhere="topRefType='Project' and topRefId=$this->id and cancelled=0";
  	  $pe=new PlanningElement();
  	  $sumValidatedWork=$pe->sumSqlElementsFromCriteria('validatedWork', null, $queryWhere);
  	  $sumValidatedCost=$pe->sumSqlElementsFromCriteria('validatedCost', null, $queryWhere);
  	} else {
  	  return;
  	}
  	//$this->ProjectPlanningElement->validatedWork=$sumValidatedWork;
  	//$this->ProjectPlanningElement->validatedCost=$sumValidatedCost;
  	
  	if (! $this->ProjectPlanningElement->elementary) {
  		if ($consolidateValidated=="ALWAYS") {
  			$this->ProjectPlanningElement->validatedWork=$sumValidatedWork;
  			$this->ProjectPlanningElement->validatedCost=$sumValidatedCost;
  			$this->ProjectPlanningElement->validatedCalculated=1;
  		} else if ($consolidateValidated=="IFSET") {
  		  $this->ProjectPlanningElement->validatedCalculated=0;
  			if ($sumValidatedWork) {
  				$this->ProjectPlanningElement->validatedWork=$sumValidatedWork;
  				$this->ProjectPlanningElement->validatedCalculated=1;
  			} 
  			if ($sumValidatedCost) {
  				$this->ProjectPlanningElement->validatedCost=$sumValidatedCost;
  				$this->ProjectPlanningElement->validatedCalculated=1;
  			}
  		}
  	}
  	
  	$this->save();
  	
  	if (trim($this->idProject)!='') {
  		$prj=new Project($this->idProject);
  		$prj->updateValidatedWork(true);
  	}
  }
  // Ticket END
  
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $old=$this->getOld();
    //Gautier #4304
    // PBER - disable this control (client request)
//     if($this->idProjectType and $this->id){
//       $projType = new ProjectType($this->idProjectType);
//       if($projType->isLeadProject){
//         //change type of project to lead Project
//         if ($old->idProjectType != $this->idProjectType) {
//           $planElement = new PlanningElement();
//           $cptList = $planElement->countSqlElementsFromCriteria(null,"idProject=$this->id and (refType='Activity' or refType='TestSession')");
//           if($cptList) $result .= '<br/>' . i18n ( 'cantChangeTypeOfProjectToLeadProjectIfYouHaveActivities' );
//         }
//       }
//     }
    if ($this->id and $this->id==$this->idProject) {
      $result.='<br/>' . i18n('errorHierarchicLoop');
    } else if ($this->ProjectPlanningElement and $this->ProjectPlanningElement->id){
      $parent=SqlElement::getSingleSqlElementFromCriteria('PlanningElement',array('refType'=>'Project','refId'=>$this->idProject));
      $parentList=$parent->getParentItemsArray();
      if (array_key_exists('#' . $this->ProjectPlanningElement->id,$parentList)) {
        $result.='<br/>' . i18n('errorHierarchicLoop');
      }
    }
    
    if ($this->longitude!=null and $this->latitude!=null) {
      if ($this->longitude > 180 || $this->longitude < -180 || $this->latitude < -90 || $this->latitude > 90) {
        $result = i18n('invalidGpsData');
      }
    }
    
    if ($this->id and $this->excludeFromGlobalPlanning==1 and $old->excludeFromGlobalPlanning==0) {
      $pe=new PlanningElement();
      $peTable=$pe->getDatabaseTableName();
      $pexStart=PlanningElementExtension::$_startId;
      $dep=new Dependency();
      $depTable=$dep->getDatabaseTableName();
      $where=" idProject=$this->id and exists (select 'x' from $depTable dep "
          ." where (dep.predecessorId=$peTable.id and dep.successorId>$pexStart) or (dep.predecessorId>$pexStart and dep.successorId=$peTable.id)"
          .")";
      $cpt=$pe->countSqlElementsFromCriteria(null,$where);
      if ($cpt>0) {
        $result.='<br/>' .i18n('cannotExcludeFromGlobalPlanning',array($cpt));
      }
    }
    // ELIOTT - LEAVE SYSTEM
    if (isLeavesSystemActiv()) {
    	// For the leave system Project (isLeaveMngProject=1)
    	if($this->isLeaveMngProject==1){
    		// Check if there is already one project with the attribute $isLeaveMngProject set to 1
    		$isLvPrjRequest = $this->getFirstSqlElementFromCriteria('Project', array("isLeaveMngProject"=>1));
    		if (!isset($isLvPrjRequest->id)) {
    			$isLvPrjRequest=null;
    		}
    		if($isLvPrjRequest) {
    			if ($isLvPrjRequest->id != $this->id){
    				$result .= '<br/>' .i18n('leaveMngProjectAlreadyExists');
    			}
    		}
    		// The Leave System Project can't have parent
    		if ($this->idProject>0) {
    			$result .= '<br/>' . i18n('leaveMngProjectCantHaveParentProject');
    		}
    	}
    }
    // ELIOTT - LEAVE SYSTEM
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public static function getAdminitrativeProjectList($returnResultAsArray=false, $withLeaveProject=true) {
    if (self::$_administrativeProjectList!==null) {
      $arrayProj=self::$_administrativeProjectList;
    } else {
    	$arrayProj=array();
    	$arrayProj[]=0;
    	$type=new ProjectType();
    	$critType=array('code'=>'ADM');
    	$listType=$type->getSqlElementsFromCriteria($critType, false);
    	foreach ($listType as $type) {
    	  $proj=new Project(); 
    		$critProj=array('idProjectType'=>$type->id);
        $listProj=$proj->getSqlElementsFromCriteria($critProj, false);
        foreach ($listProj as $proj) {
  // MTY - LEAVE SYSTEM
                  if (isLeavesSystemActiv()) {
                      $isLvePrjt = self::isTheLeaveProject($proj->id);
                      if ($isLvePrjt and $withLeaveProject==false) {continue;}
                      //if (self::isTheLeaveProject($proj->id) && !self::isProjectLeaveVisible()) {continue;}
                  }  
  // MTY - LEAVE SYSTEM
        	$arrayProj[$proj->id]=$proj->id;
        }
    	}
    }
    self::$_administrativeProjectList=$arrayProj;
  	if ($returnResultAsArray) return $arrayProj;
  	return '(' . implode(', ',$arrayProj) . ')';
  }

  public static function getFixedProjectList($returnResultAsArray=false) {
    $arrayProj=array();
    $arrayProj[]=0;
    $proj=new Project(); 
    $critProj=array('fixPlanning'=>'1', 'idle'=>'0');
    $listProj=$proj->getSqlElementsFromCriteria($critProj, false);
    foreach ($listProj as $proj) {
// MTY - LEAVE SYSTEM
      if (isLeavesSystemActiv()) {  
        if (self::isTheLeaveProject($proj->id) && !self::isProjectLeaveVisible()) {continue;}
      }
// MTY - LEAVE SYSTEM        
      $arrayProj[]=$proj->id;
      $sublist=$proj->getRecursiveSubProjectsFlatList(true);
      if ($sublist and count($sublist)>0) {
        foreach($sublist as $subId=>$subName) {
          $arrayProj[]=$subId;
        }
      }
    }
    if ($returnResultAsArray) return $arrayProj;
    return '(' . implode(', ',$arrayProj) . ')';
  }
  
  //damian
  public static function getFixedProjectPerimeterList($returnResultAsArray=false){
    $arrayProj = array();
    $proj = new Project();
    $listProj = $proj->getSqlElementsFromCriteria(array('fixPerimeter'=>'1', 'idle'=>'0'));
    foreach ($listProj as $projPerim){
      $arrayProj[$projPerim->id] = $projPerim->name;
      $sublist = $projPerim->getRecursiveSubProjectsFlatList(true);
      if($sublist and count($sublist) > 0) {
        foreach ($sublist as $subId=>$subName){
          $arrayProj[$subId] = $subName;
        }
      }
    }
    if ($returnResultAsArray) return $arrayProj;
    return '(' . implode(', ',$arrayProj) . ')';
  }
  
  public function getColor() {
    $color="#777777";
    if ($this->color) {
      $color=$this->color;
    } else if ($this->idProject) {
      $top=new Project($this->idProject);
      $color=$top->getColor();
    }
    return $color;
  }
  
  public static function getTemplateList() {
    $result=array();
    $types=SqlList::getListWithCrit('ProjectType',array('code'=>'TMP'));
    foreach($types as $typId=>$typName) {
      $projects=SqlList::getListWithCrit('Project', array('idProjectType'=>$typId));
      $result=array_merge_preserve_keys($result,$projects);
    }
    return $result;
  }
  public static function getTemplateInClauseList() {
    $list=self::getTemplateList();
    $in='(0';
    foreach ($list as $id=>$name) {
      $in.=','.$id;
    }
    $in.=')';
    return $in;
  }
  
// ADD BY Marc TABARY - 2017-03-17 - COPY ACTIVITY PRICE WHEN COPY PROJECT
  public function copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments=false, $withAffectations=false,
                         $toProject=null, $toActivity=null, $copyToWithResult=false, $copyToWithVersionProjects=false, $copyToWithActivityPrice=false) {

    // Control that copy is not directly copied into structure of copied project 
    if ($toProject) {
      $sub=$this->getSubProjectsList(false);
      if ($toProject==$this->id or isset($sub[$toProject])) {
        $result=i18n('copyNotAsSubOfCurrent').'<br/>';
        $this->_copyResult=$result;
        return $this;
      }
    }
    $result=parent::copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments, $withAffectations ,
    		                 $toProject, $toActivity, $copyToWithResult, $copyToWithVersionProjects, $copyToWithActivityPrice);
    if($copyToWithVersionProjects==true){
      $vp=new VersionProject();
      $crit=array('idProject'=>$this->id);
      $list=$vp->getSqlElementsFromCriteria($crit);
      foreach ($list as $vp) {
        $vp->idProject=$result->id;
        $vp->id=null;
        $vp->save();
      }
    }
    if($copyToWithActivityPrice==true) {
        $ap = new ActivityPrice();
        $crit=array('idProject'=>$this->id);
        $list=$ap->getSqlElementsFromCriteria($crit);
        foreach ($list as $ap) {
            $ap->idProject=$result->id;
            $ap->id=null;
            $ap->save();
        }
    }
    if($withAffectations==true){
      $aff = new Affectation();
      $crit=array('idProject'=>$this->id);
      $list=$aff->getSqlElementsFromCriteria($crit);
      foreach ($list as $aff) {
        $aff->idProject=$result->id;
        $aff->id=null;
        $aff->save();
      }
    }
    
    return $result;
  } 
  
  public static function setNeedReplan($id) {
    if (PlanningElement::$_noDispatch) return;
    $adminProjects=Project::getAdminitrativeProjectList(true);
    if (isset($adminProjects[$id])) return;
    $proj=SqlElement::getSingleSqlElementFromCriteria("ProjectPlanningElement",array('refType'=>'Project','refId'=>$id),true);
    if (!$proj->id or $proj->needReplan==true) return;
    $proj->needReplan=true;
    $proj->simpleSave();
  }
  public static function unsetNeedReplan($id) {
    $proj=SqlElement::getSingleSqlElementFromCriteria("ProjectPlanningElement",array('refType'=>'Project','refId'=>$id),true);
    if (PlannedWork::$_planningInProgress and $id) {
      // Attention, we'll execute direct query to avoid concurrency issues for long duration planning
      // Otherwise, saving planned data may overwrite real work entered on Timesheet for corresponding items.
      $ppe=new ProjectPlanningElement();
      $ppeTable=$ppe->getDatabaseTableName();
      $query="UPDATE $ppeTable SET needReplan=0 WHERE refType='Project' and refId=$id";
      Sql::query($query); 
    } else {
      $proj->needReplan=false;
      $proj->simpleSave();
    }
    if ($proj->topId) {
      $top=new ProjectPlanningElement($proj->topId);
      if ($top->needReplan) {
        $count=$top->countSqlElementsFromCriteria(array('refType'=>'Project','topId'=>$top->id, 'needReplan'=>'1'));
        if ($count==0) {
          self::unsetNeedReplan($top->refId);
        }
      }
    }
  }
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
  public function setAttributes() {
    if (SqlList::getFieldFromId("Status", $this->idStatus, "setHandledStatus")!=0) self::$_fieldsAttributes["isUnderConstruction"]="readonly,nobr";
    if (Parameter::getGlobalParameter('allowTypeRestrictionOnProject')!='YES') {
      unset($this->_sec_restrictTypes);
      unset($this->_spe_restrictTypes);
    }
    if ($this->ProjectPlanningElement and $this->ProjectPlanningElement->realWork>0) {
      self::$_fieldsAttributes["isUnderConstruction"]="readonly,nobr";
    }
  }
  
  public static function getSelectedProject($clearStarForAll=false,$clearMultiSelection=false) {
    $proj=null;
    if (sessionValueExists('project')) {
      $proj=getSessionValue('project');
    }
    if ($clearStarForAll and $proj=='*') $proj=null; // Selected = all
    if ($clearMultiSelection and strpos($proj,',')!==null) $proj=null; // Selected = multi
    return $proj;
  }
  public static function isSelectedProject() {
    if (sessionValueExists('project')) {
      $proj=getSessionValue('project');
      if ($proj=='*' or !$proj) return false;
      else return true;
    } else {
      return false;
    }
  }
  public static function isSelectedProjectMultiple() {
    if (sessionValueExists('project')) {
      $proj=getSessionValue('project');
      if (strpos($proj,',')!==null) return true;
      else return false;
    } else {
      return false;
    }
  }
  public static function getSelectedProjectList() {
    $proj=self::getSelectedProject(true,false);
    $projList=explode(',',$proj);
    return $projList;
  }
  public static function setSelectedProject($proj){
    setSessionValue('project', $proj);
  }
}
?>