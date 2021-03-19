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
class ChangeRequestMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    
  public $reference;
  public $name;
  public $idChangeRequestType;
  public $idProject;
  public $idProduct;
  public $idComponent;  
  public $externalReference;
  public $creationDateTime;
  public $idUser;
  public $idContact;
  public $Origin;
  public $idBusinessFeature;
  public $idUrgency;
  public $initialDueDate;
  public $actualDueDate;
  public $description;
  public $reason;
  public $potentialBenefit;
  public $_sec_treatment;
  public $idStatus;
  public $idResource;
  public $idCriticality;
  public $idFeasibility;
  public $idRiskLevel;
  public $idPriority; 
  public $plannedWork;
  public $plannedCost;
  public $idTargetProductVersion;
  public $idTargetComponentVersion;  
  public $idMilestone;
  public $handled;
  public $handledDate;
  public $approved;
  public $approvedDate;
  public $_lib_by;
  public $idAffectable;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $analysis;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="8%" >${idProject}</th>
    <th field="nameProduct" width="8%" >${idProduct}</th>
    <th field="nameChangeRequestType" width="8%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" formatter="thumbName22" width="10%" >${responsible}</th>
    <th field="nameTargetProductVersion" width="10%" >${idTargetProductVersion}</th>
    <th field="handled" width="5%" formatter="booleanFormatter" >${handled}</th>
    <th field="approved" width="5%" formatter="booleanFormatter" >${approved}</th>
    <th field="done" width="5%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idStatus"=>"required",
                                  "creationDateTime"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "idUser"=>"hidden",
                                  "idProject"=>"required",
                                  "approved"=>"nobr",
                                  "approvedDate"=>"nobr",
                                  "_lib_by"=>"nobr",
                                  "idAffectable"=>""
  );  
  
  private static $_colCaptionTransposition = array('idResource'=> 'responsible',
                                                   'idTargetProductVersion'=>'targetVersion', 
                                                   'idRiskLevel'=>'technicalRisk',
                                                   'plannedWork'=>'estimatedEffort',
                                                   'plannedCost'=>'estimatedBudget',
                                                   'idAffectable' => 'approver',
                                                   'approvalDate'=>"dateApproved"
                                                   );
  
  private static $_databaseColumnName = array();
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($withoutDependentObjects) return; 
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }
  
  public function setAttributes() {
    if($this->id){
      //self::$_fieldsAttributes['approved']='visible,nobr';
      //self::$_fieldsAttributes['approvedDate']='visible,nobr';
      //self::$_fieldsAttributes['idAffectable']='visible,nobr';
      //self::$_fieldsAttributes['_lib_by']='visible,nobr';
    }
    if (!$this->id) {
      self::$_fieldsAttributes['approved']='readonly,nobr';
    }
    $manageComponentOnChangeRequest=Parameter::getGlobalParameter('manageComponentOnChangeRequest');
    if ($manageComponentOnChangeRequest!='YES') {
      self::$_fieldsAttributes['idComponent']='hidden';
      self::$_fieldsAttributes['idTargetComponentVersion']='hidden';
    }
    if (Parameter::getGlobalParameter('manageMilestoneOnItems') != 'YES') {
      self::$_fieldsAttributes["idMilestone"]='hidden';
    }
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

    if ($colName=="initialDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("actualDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("actualDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="actualDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("initialDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("initialDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';           
    }else if ($colName=="approved") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("approved").get("checked")==true) { ';
      $colScript .= '    var curDate = new Date();';
      $colScript .= '    var curUserId = '.getCurrentUserId ().';';
      $colScript .= '    dijit.byId("approvedDate").set("value", curDate); ';
      $colScript .= '  } else { ';
      $colScript .= '   dijit.byId("approvedDate").set("value", null);';
      $colScript .= '   dijit.byId("idApprover__idResource").set("value",null); ';
      $colScript .= '  }  ';
      $colScript .= '  formChanged();';
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
    
    if (!trim($this->idProject) and !trim($this->idProduct)) {
      $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdProject') . " " . i18n('colOrProduct')));
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
  	
  	$result=parent::save();
    return $result;
  }
  
}
?>