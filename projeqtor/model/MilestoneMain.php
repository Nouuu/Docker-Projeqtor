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
class MilestoneMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idMilestoneType;
  public $idProject;
  public $creationDate;
  public $lastUpdateDateTime;
  public $idUser;
  public $Origin;
  public $description;
  public $_sec_treatment;
  public $idActivity;
  public $idStatus;
  public $idResource;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $idTargetProductVersion;
  public $result;
  public $_sec_Progress;
  public $MilestonePlanningElement; // is an object
  public $_sec_predecessor;
  public $_Dependency_Predecessor=array();
  public $_sec_successor;
  public $_Dependency_Successor=array();
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();  
  // Define the layout that will be used for lists
  
  public $_nbColMax=3;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="20%" >${idProject}</th>
    <th field="nameMilestoneType" width="10%" >${idMilestoneType}</th>
    <th field="name" width="40%" >${name}</th>
    <th field="plannedEndDate" from="MilestonePlanningElement" width="10%" formatter="dateFormatter">${plannedDueDate}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idProject"=>"required",
                                  "idStatus"=>"required",
                                  "idMilestoneType"=>"required",
                                  "creationDate"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer', 
                                                   'idResource'=> 'responsible',
                                                   'idActivity' => 'parentActivity',
                                                   'idTargetProductVersion'=>'targetVersion');
  
  private static $_databaseColumnName = array('idTargetProductVersion'=>'idVersion');
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($withoutDependentObjects) return; // No real use yet, but no to forget as item has $Origin
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
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idProject") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("MilestonePlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idActivity") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  dojo.byId("MilestonePlanningElement_wbs").value=""; ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idMilestoneType") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  setDefaultPlanningMode(this.value);';
      $colScript .= '</script>';
    } 
    return $colScript;
  }

  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    // #305 : need to recalculate before dispatching to PE
    $this->recalculateCheckboxes();
    $old=$this->getOld();
    $this->MilestonePlanningElement->refName=$this->name;
    $this->MilestonePlanningElement->idProject=$this->idProject;
    $this->MilestonePlanningElement->idle=$this->idle;
    $this->MilestonePlanningElement->done=$this->done;
    $this->MilestonePlanningElement->cancelled=$this->cancelled;
    if ($this->idActivity and trim($this->idActivity)!='') {
      $this->MilestonePlanningElement->topRefType='Activity';
      $this->MilestonePlanningElement->topRefId=$this->idActivity;
      $this->MilestonePlanningElement->topId=null;
    } else {
      $this->MilestonePlanningElement->topRefType='Project';
      $this->MilestonePlanningElement->topRefId=$this->idProject;
      $this->MilestonePlanningElement->topId=null;;
    } 
    if (trim($this->idProject)!=trim($old->idProject) or trim($this->idActivity)!=trim($old->idActivity)) {
      $this->MilestonePlanningElement->wbs=null;
      $this->MilestonePlanningElement->wbsSortable=null;
    }
    $result=parent::save();
    
    if ($this->idResource!=$old->idResource and Parameter::getGlobalParameter('updateIncomingResponsibleFromMilestone')!='NO') {
      $link=new Link();
      $crit=array("ref2Type"=>"Milestone","ref2Id"=>$this->id,"ref1Type"=>"Incoming");
      $list=$link->getSqlElementsFromCriteria($crit);
      foreach ($list as $link) {
        $incom=new Incoming($link->ref1Id);
        if ($incom->idResource!=$this->idResource) {
          $incom->idResource=$this->idResource;
          $incom->save();
        }
      }
    }
    if ($this->idResource!=$old->idResource and Parameter::getGlobalParameter('updateDeliverableResponsibleFromMilestone')!='NO') {
      $link=new Link();
      $crit=array("ref2Type"=>"Milestone","ref2Id"=>$this->id,"ref1Type"=>"Deliverable");
      $list=$link->getSqlElementsFromCriteria($crit);
      foreach ($list as $link) {
        $deliv=new Deliverable($link->ref1Id);
        if ($deliv->idResource!=$this->idResource) {
          $deliv->idResource=$this->idResource;
          $deliv->save();
        }
      }
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
    $old=$this->getOld();
    if ($this->MilestonePlanningElement and $this->MilestonePlanningElement->id
      and ($this->idActivity!=$old->idActivity or $this->idProject!=$old->idProject)){
      if (trim($this->idActivity)) {
        $parentType='Activity';
        $parentId=$this->idActivity;
      } else {
        $parentType='Project';
        $parentId=$this->idProject;
      }
      $result.=$this->MilestonePlanningElement->controlHierarchicLoop($parentType, $parentId);
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
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
}
?>