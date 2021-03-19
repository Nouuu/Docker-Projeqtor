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
class DeliveryMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  //public $scope;    //REMOVED qCazelles - bug scope Delivery
  public $name;
  //public $idDeliverableType;  //REMOVED qCazelles
  public $idDeliveryType;
  public $idProject;
  public $externalReference;
  //ADD qCazelles
  public $idProductVersion;
  //END ADD qCazelles
  public $idUser;
  public $idContact;
  public $creationDateTime;
  public $description;
  public $_sec_validation;
  //ADD qCazelles
  public $idStatus;
  //END ADD qCazelles
  //public $idDeliverableWeight;
  public $idResource;
  public $idMilestone;
  public $_tab_4_1=array('initial','planned','real','ValidationDate','dueDate');
  public $initialDate;
  public $plannedDate;
  public $realDate;
  public $validationDate;
  //public $realDate;
  //public $validationDate;
  //ADD qCazelles
  public $handled;
  public $handledDateTime;
  public $done;
  public $doneDateTime;
  public $idle;
  public $idleDateTime;
  public $cancelled;
  public $_lib_cancelled;
  //END ADD qCazelles
  public $idDeliverableStatus;
  public $result;
  public $_sec_Link_Deliverable;
  public $_Link_Deliverable=array();
  //ADD qCazelles
  public $_sec_Link_Activity;
  public $_Link_Activity=array();
  //END ADD qCazelles
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();


  public $_nbColMax=3;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameDeliveryType" width="10%" >${idDeliveryType}</th>
    <th field="name" width="30%" >${name}</th>
    <th field="nameUser" formatter="thumbName22" width="10%">${issuer}</th>
    <th field="colorNameDeliverableStatus" width="12%" formatter="colorNameFormatter">${idDeliverableStatus}</th>
    <th field="colorNameDeliverableWeight" width="12%" formatter="colorNameFormatter">${idDeliverableWeight}</th>
    <th field="plannedDate" width="8%" >${plannedDate}</th>
    <th field="realDate" width="8%" >${realDate}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idProject"=>"required",
                                  "idDeliveryType"=>"required",
                                  "creationDateTime"=>"hidden",
                                  //"scope"=>"hidden",    //REMOVED qCazelles - bug scope Delivery
                                  "idActivity"=>"title",
                                  "idStatus"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
                                  "idleDateTime"=>"nobr",
                                  "cancelled"=>"nobr"
  );  
  
  private static $_colCaptionTransposition = array('idDeliverableStatus'=>'idDeliveryStatus',
                                                    'idUser'=>'issuer', //nom du champ + tab
                                                    'idContact' =>'requestor', //nom du champ + tab
  );
  
  private static $_databaseColumnName = array();
  
  private static $_databaseTableName = 'delivery';
  //CHANGE qCazelles - bug scope Delivery
  //Old
  //private static $_databaseCriteria = array('scope'=>'Delivery');
  //New
  private static $_databaseCriteria = array();
  //END CHANGE qCazelles - bug scope Delivery
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if (!$this->id and getSessionUser()->isContact) {
          $this->idContact=getSessionUser()->id;
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
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
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
    } else     if ($colName=="idle") {   
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
      //ADD qCazelles
    } else if ($colName=="idProject") {
     $colScript .= '<script type="dojo/connect" event="onChange" >';
     $colScript .= '  refreshList("idProductVersion", "idProject", this.value);';     
     $colScript .= '</script>';
    }
    return $colScript;
  }
    
  public function save() {
    $old=$this->getOld();
    $result=parent::save();
    if ($this->idResource!=$old->idResource and Parameter::getGlobalParameter('updateMilestoneResponsibleFromDeliverable')!='NO') {
      $link=new Link();
      $crit=array("ref1Type"=>"Deliverable","ref1Id"=>$this->id,"ref2Type"=>"Milestone");
      $list=$link->getSqlElementsFromCriteria($crit);
      foreach ($list as $link) {
        $mile=new Milestone($link->ref2Id);
        if ($mile->idResource!=$this->idResource) {
          $mile->idResource=$this->idResource;
          $mile->save();
        }
      }
    }
    if ($this->idDeliverableStatus){ // if idDeliverableStatus exist
      $link=new Link(); 
      $crit=array("ref1Type"=>"Deliverable");
      $list2=$link->getSqlElementsFromCriteria($crit);
      foreach ($list2 as $link2) {
        if($link2->ref1Type=='Deliverable'){
         $deliverable = new Deliverable($link2->ref1Id);
         $deliverable->idDeliverableStatus=$this->idDeliverableStatus;
         $test=$deliverable->save();
        }
      }
    }
    KpiValue::calculateKpi($this);
    return $result;
  }
  
  //ADD qCazelles
  public function control() {
    $result="";
    if (trim($this->idProductVersion)) {
      $linkedVersion=new ProductVersion($this->idProductVersion);
      if ( !in_array($this->idProject,$linkedVersion->getLinkedProjects(false))) {
        $result.='<br/>' . i18n('msgVersionNotLinkedToProject');
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
  public function setAttributes() {
  	if (Parameter::getGlobalParameter('productVersionOnDelivery') != 'YES') {
  		self::$_fieldsAttributes['idProductVersion']='hidden';
  	} 
  	if (Parameter::getGlobalParameter('manageMilestoneOnItems') != 'YES') {
  	  self::$_fieldsAttributes["idMilestone"]='hidden';
  	}
  }
}
?>