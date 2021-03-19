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
 * Opportunity.
 */ 
require_once('_securityCheck.php');
class OpportunityMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idOpportunityType;
  public $idProject;
  public $creationDate;
  public $idUser;
  public $idSeverity;
  public $idLikelihood;
  public $idCriticality;
  public $impactCost;
  public $projectReserveAmount;
  public $Origin;
  public $cause;
  public $impact;
  public $description;
  public $_sec_treatment;
  public $idStatus;
  public $idResource;
  public $idPriority;
  public $initialEndDate; // is an object
  public $actualEndDate;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  //public $_sec_linkAction;
  //public $_Link_Action=array();
  //public $_sec_linkIssue;
  //public $_Link_Issue=array();
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();

  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="4%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameOpportunityType" width="10%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameSeverity" width="5%" formatter="colorNameFormatter" >${idSeverity}</th>
    <th field="colorNameLikelihood" width="5%" formatter="colorNameFormatter" >${opportunityImprovement}</th>
    <th field="colorNameCriticality" width="5%" formatter="colorNameFormatter" >${idCriticality}</th>
    <th field="colorNameStatus" width="8%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" formatter="thumbName22" width="8%" >${responsible}</th>
    <th field="colorNamePriority" width="5%" formatter="colorNameFormatter" >${idPriority}</th>
    <th field="actualEndDate" width="8%" formatter="dateFormatter">${actualEndDate}</th>
    <th field="handled" width="4%" formatter="booleanFormatter" >${handled}</th>
    <th field="done" width="4%" formatter="booleanFormatter" >${done}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';
  
  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idProject"=>"required",
                                  "idStatus"=>"required",
                                  "idOpportunityType"=>"required",
                                  "creationDate"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "projectReserveAmount"=>"readonly"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer',
                                                   'idResource'=> 'responsible',
                                                   'idOpportunityType'=>'type',
                                                   'idSeverity'=>'opportunitySignificance',
                                                   'cause'=>'opportunitySource',
                                                   'impactCost'=>'opportunityImprovement',
                                                   'projectReserveAmount'=>'projectReserveGain');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
  
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
   * Return the specific databaseTableName
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

    if ($colName=="idSeverity" or $colName=="idLikelihood") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';  
      $colScript .= htmlGetJsTable('Severity', 'value');
      $colScript .= htmlGetJsTable('Likelihood', 'value');
      $colScript .= htmlGetJsTable('Criticality', 'value');
      $colScript .= '  var serverityValue=0; var likelihoodValue=0; var criticalityValue=0;';
      $colScript .= '  var filterSeverity=dojo.filter(tabSeverity, function(item){return item.id==dijit.byId("idSeverity").value;});';
      $colScript .= '  var filterLikelihood=dojo.filter(tabLikelihood, function(item){return item.id==dijit.byId("idLikelihood").value;});';
      $colScript .= '  dojo.forEach(filterSeverity, function(item, i) {serverityValue=item.value;});';
      $colScript .= '  dojo.forEach(filterLikelihood, function(item, i) {likelihoodValue=item.value;});';
      $colScript .= '  calculatedValue = Math.round(serverityValue*likelihoodValue/2);';
      $colScript .= '  var filterCriticality=dojo.filter(tabCriticality, function(item){return item.value==calculatedValue;});';
      $colScript .= '  if ( filterCriticality.length==0) {';
      $colScript .= '    var filterCriticality=dojo.filter(tabCriticality, function(item,i){if (i==0) return true; else return item.value<=calculatedValue;});';
      $colScript .= '  }';
      $colScript .= '  if (trim(dijit.byId("idSeverity").value) && trim(dijit.byId("idLikelihood").value))';
      $colScript .= '    dojo.forEach(filterCriticality, function(item, i) {dijit.byId("idCriticality").set("value",item.id);});';
      $colScript .= '  else dijit.byId("idCriticality").reset();';
      if ($colName=="idLikelihood") {
        $colScript .= 'stock=dijit.byId("impactCost").get("value");';
        $colScript .= 'dijit.byId("impactCost").set("value",null);';
        $colScript .= 'dijit.byId("impactCost").set("value",stock);';
      }
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="initialEndDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("actualEndDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("actualEndDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="actualEndDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("initialEndDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("initialEndDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';           
    } else if ($colName=="impactCost") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Likelihood', 'valuePct');
      $colScript .= '  var likelihoodValue=0;';
      $colScript .= '  var filterLikelihood=dojo.filter(tabLikelihood, function(item){return item.id==dijit.byId("idLikelihood").value;});';
      $colScript .= '  dojo.forEach(filterLikelihood, function(item, i) {likelihoodValue=item.valuePct;});';
      $colScript .= '  calculatedValue = Math.round(dijit.byId("impactCost").get("value")*likelihoodValue)/100;';
      $colScript .= '  dijit.byId("projectReserveAmount").set("value",calculatedValue);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
    return $colScript;
  }
  public function save() {
    if ($this->impactCost and $this->idLikelihood) {
      $likelihood=new Likelihood($this->idLikelihood);
      $this->projectReserveAmount=round($this->impactCost*$likelihood->valuePct/100,0);
    } else {
      $this->projectReserveAmount=0;
    }
    $result=parent::save();
    PlanningElement::updateSynthesis('Project', $this->idProject);
    return $result;
  }
}
?>