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
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class StatusMail extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $idMailable;
  public $idType;
  public $idProject;
  public $idStatus;
  public $idEventForMail;
  public $idEmailTemplate; //add Gmartin Ticket #157
  public $idle;
  public $_sec_SendMail;
  public $mailToContact;
  public $mailToUser;
  public $mailToAccountable;
  public $mailToResource;
  public $mailToFinancialResponsible;
  public $mailToSponsor;
  public $mailToProject;
  public $mailToProjectIncludingParentProject;
  public $_lib_globalProjectTeam;
  public $mailToLeader;
  public $mailToManager;
  public $mailToAssigned;
  public $mailToSubscribers;
  public $mailToOther;
  public $otherMail;
  public $isProject;
  
  public $_noCopy;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameMailable" formatter="translateFormatter" width="11%" >${idMailable}</th>
    <th field="nameType" formatter="nameFormatter" width="9%" >${type}</th>
    <th field="nameProject" width="7%">${idProject}</th>
    <th field="colorNameStatus" width="6%" formatter="colorNameFormatter">${newStatus}</th>
    <th field="nameEventForMail" formatter="translateFormatter" width="10%" >${orOtherEvent}</th>
    <th field="mailToContact" width="5%" formatter="booleanFormatter" >${mailToContact}</th>    
    <th field="mailToUser" width="5%" formatter="booleanFormatter" >${mailToUser}</th>
    <th field="mailToResource" width="5%" formatter="booleanFormatter" >${mailToResource}</th>
    <th field="mailToFinancialResponsible" width="5%" formatter="booleanFormatter" >${mailToFinancialResponsible}</th>
    <th field="mailToProject" width="5%" formatter="booleanFormatter" >${mailToProject}</th>
    <th field="mailToProjectIncludingParentProject" width="5%" formatter="booleanFormatter" >${mailToProjectIncludingParentProject}</th>
    <th field="mailToLeader" width="5%" formatter="booleanFormatter" >${mailToLeader}</th>
    <th field="mailToManager" width="5%" formatter="booleanFormatter" >${mailToManager}</th>
    <th field="mailToAssigned" width="5%" formatter="booleanFormatter" >${mailToAssigned}</th>
    <th field="mailToSubscribers" width="5%" formatter="booleanFormatter" >${mailToSubscribers}</th>
    <th field="mailToOther" width="5%" formatter="booleanFormatter" >${mailToOther}</th>
    <th field="idle" width="4%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("idMailable"=>"", 
                                  "mailToOther"=>"nobr",
                                  "otherMail"=>"",
                                  "idType"=>"nocombo", 
  		                            "mailToSponsor"=>"hidden,calculated",
                                  "isProject"=>"hidden",
                                  "mailToProjectIncludingParentProject" => "nobr",
                                  "mailToAccountable"=>"invisible",
                                  "mailToAssigned"=>"invisible",
                                  "mailToFinancialResponsible"=>"invisible"
  );  
  
  private static $_colCaptionTransposition = array('idStatus'=>'newStatus',
  'otherMail'=>'email',
  'idEventForMail'=>'orOtherEvent',
  "mailToAccountable"=>"idAccountable",
  'idType'=>'type');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
  
  private static $_databaseTableName = 'statusmail';

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

  public function setAttributes() {
    if ($this->id) {
      self::$_fieldsAttributes["idMailable"]='readonly';
      $mailable=SqlList::getNameFromId('Mailable', $this->idMailable,false);
      if ($mailable!="Activity" and $mailable!="TestSession" and $mailable!="Meeting" and $mailable!="PeriodicMeeting") {
        self::$_fieldsAttributes["mailToAssigned"]='invisible';
      } else {
        self::$_fieldsAttributes["mailToAssigned"]='';
      }
      if ($mailable=="ProjectExpense") {
      	self::$_fieldsAttributes["mailToFinancialResponsible"]='';
      	self::$_colCaptionTransposition["mailToResource"]="businessResponsible";
      }else if ($mailable=="IndividualExpense") {
      	self::$_fieldsAttributes["mailToFinancialResponsible"]='';
      	self::$_colCaptionTransposition["mailToResource"]="resource";
      	self::$_colCaptionTransposition["mailToFinancialResponsible"]="responsible";
      } else {
      	self::$_fieldsAttributes["mailToFinancialResponsible"]='invisible';
      }
      if (Parameter::getGlobalParameter('manageAccountable')!='YES') {
        self::$_fieldsAttributes["mailToAccountable"]='invisible';
      } else if (!property_exists($mailable,'idAccountable')) {
        self::$_fieldsAttributes["mailToAccountable"]='invisible';
      } else {
        self::$_fieldsAttributes["mailToAccountable"]='';
      }
    } 
    if ($this->mailToOther=='1') {
      self::$_fieldsAttributes['otherMail']='';
    } else {
      self::$_fieldsAttributes['otherMail']='invisible';
    }
    
  }
  
  public function control() {
    $result="";
    if (! trim($this->idMailable)) {
    	$result.='<br/>' . i18n('messageMandatory',array(i18n('colElement')));
    }
    $crit="idMailable=" . Sql::fmtId($this->idMailable);
    if (trim($this->idStatus)) {
    	$crit.=" and idStatus=" . Sql::fmtId($this->idStatus) ;
    }
    if (trim($this->idEventForMail)) {
      $crit.=" and idEventForMail=" . Sql::fmtId($this->idEventForMail);
    }
    if (trim($this->idType)) {
      $crit.=" and idType=" . Sql::fmtId($this->idType);
    } else {
      $crit.=" and idType is null";	
    }
    if(property_exists($this, 'idProject') and $this->idProject){
      $crit.=  " and idProject=" . Sql::fmtId($this->idProject);
    } else {
      $crit.=  " and idProject is null";
    }
    $crit.=" and id<>" . Sql::fmtId($this->id);
    $list=$this->getSqlElementsFromCriteria(null, false, $crit);
    if (count($list)>0) {
      $result.="<br/>" . i18n('errorDuplicateStatusMail',null);
    }
    if (!trim($this->idStatus) and !trim($this->idEventForMail)) {
    	$result.="<br/>" . i18n('messageMandatory',array(i18n('colNewStatus')." ".i18n('colOrOtherEvent')));
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
    if ($this->idProject) $this->isProject=1;
    else $this->isProject=0;
    return parent::save();
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
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    
    $colScript = parent::getValidationScript($colName);
    $colScript = " ";
    if ($colName=="mailToOther") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var fld = dijit.byId("otherMail").domNode;';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    dojo.query(".generalColClass.otherMailClass").forEach(function(domNode){domNode.style.display="inline-block";});';
      //$colScript .= '    dojo.style(fld, {visibility:"visible"});';
      $colScript .= '  } else {';
      //$colScript .= '    dojo.style(fld, {visibility:"hidden"});';
      $colScript .= '    dojo.query(".generalColClass.otherMailClass").forEach(function(domNode){domNode.style.display="none";});';
      $colScript .= '    dijit.byId("otherMail").set("value","");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idStatus") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.value!=" ") { ';
      $colScript .= '    dijit.byId("idEventForMail").set("value"," ");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idEventForMail") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.value!=" ") { ';
      $colScript .= '    dijit.byId("idStatus").set("value"," ");';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="mailToAssigned") {
    	$colScript .= '<script type="dojo/connect" event="onClick" >';
    	$colScript .= ' mailable=dijit.byId("idMailable");';
    	$colScript .= ' mVal=mailable.get("displayedValue");';
    	$colScript .= ' if (this.checked && mVal!=i18n("Activity") && mVal!=i18n("Meeting") && mVal!=i18n("TestSession")) { ';
    	$colScript .= '   showAlert(i18n("msgIncorrectReceiver"));';
    	$colScript .= '   this.checked=false;';
    	$colScript .= ' }'; 
    	$colScript .= '</script>';
    } else if ($colName=='idMailable') { 
      $colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
      $colScript .=' var mailable=mailableArray[this.value];';
      $colScript .=' var isAccountableArray=new Array();';
      if (parameter::getGlobalParameter('manageAccountable')=='YES') {
        $list=SqlList::getListNotTranslated('Mailable');
        foreach ($list as $id=>$name) {
          if (property_exists($name, 'idAccountable')) {
            $colScript .= "isAccountableArray['" . $name . "']='" . $name . "';";
          }
        }
      }
      $colScript .= '  dijit.byId("idType").set("value",null);';
      $colScript .= '  refreshList("idType","scope", mailable);';
      $colScript .= '  dijit.byId("idEventForMail").reset();';
      $colScript .= '  refreshList("idEventForMail","scope", mailable, null);';
      //gmartin begin Ticket #157 - Fixed PBE
      $colScript .= '  dijit.byId("idEmailTemplate").set("value", null);';
      $colScript .= '  if (this.value) {';
      $colScript .= '    refreshList("idEmailTemplate","idMailable", this.value, null, null, null, "idType",null);';
      $colScript .= '  } else {';
      $colScript .= '    refreshList("idEmailTemplate","idMailable", null);';
      $colScript .= '  }';
      //gmartin end - Fixed PBE
      $colScript .= '  if (mailable=="Activity" || mailable=="TestSession" || mailable=="Meeting" || mailable=="PeriodicMeeting") {';
      $colScript .= '    dojo.query(".generalRowClass.mailToAssignedClass").forEach(function(domNode){domNode.style.display="table-row";});';
      $colScript .= '    dojo.query(".generalColClass.mailToAssignedClass").forEach(function(domNode){domNode.style.display="inline-block";});';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("mailToAssigned").set("checked",false);';
      $colScript .= '    dojo.query(".mailToAssignedClass").forEach(function(domNode){domNode.style.display="none";});';
      $colScript .= '  }';
      $colScript .= '  if (mailable=="IndividualExpense") {';
      $colScript .= '    dojo.query(".generalColClass.mailToResourceClass").forEach(function(domNode){if(domNode.nodeName == "LABEL"){
                         domNode.innerHTML=i18n("colResource")+"&nbsp;:&nbsp;";}});';
      $colScript .= '    dojo.query(".generalRowClass.mailToFinancialResponsibleClass").forEach(function(domNode){domNode.style.display="table-row";});';
      $colScript .= '    dojo.query(".generalColClass.mailToFinancialResponsibleClass").forEach(function(domNode){domNode.style.display="inline-block";});';
      $colScript .= '    dojo.query(".generalColClass.mailToFinancialResponsibleClass").forEach(function(domNode){if(domNode.nodeName == "LABEL"){
                         domNode.innerHTML=i18n("colResponsible")+"&nbsp;:&nbsp;";}});';
      $colScript .= '  } else if (mailable=="ProjectExpense") {';
      $colScript .= '    dojo.query(".generalColClass.mailToResourceClass").forEach(function(domNode){if(domNode.nodeName == "LABEL"){
                         domNode.innerHTML=i18n("colBusinessResponsible")+"&nbsp;:&nbsp;";}});';
      $colScript .= '    dojo.query(".generalColClass.mailToFinancialResponsibleClass").forEach(function(domNode){if(domNode.nodeName == "LABEL"){
                         domNode.innerHTML=i18n("colFinancialResponsible")+"&nbsp;:&nbsp;";}});';
      $colScript .= '    dojo.query(".generalRowClass.mailToFinancialResponsibleClass").forEach(function(domNode){domNode.style.display="table-row";});';
      $colScript .= '    dojo.query(".generalColClass.mailToFinancialResponsibleClass").forEach(function(domNode){domNode.style.display="inline-block";});';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("mailToFinancialResponsible").set("checked",false);';
      $colScript .= '    dojo.query(".mailToFinancialResponsibleClass").forEach(function(domNode){domNode.style.display="none";});';
      $colScript .= '    dojo.query(".generalColClass.mailToResourceClass").forEach(function(domNode){if(domNode.nodeName == "LABEL"){
                         domNode.innerHTML=i18n("colResponsible")+"&nbsp;:&nbsp;";}});';
      $colScript .= '  }';
      $colScript .= ' if(isAccountableArray[mailable]==mailable) {';
      $colScript .= '    dojo.query(".generalRowClass.mailToAccountableClass").forEach(function(domNode){domNode.style.display="table-row";});';
      $colScript .= '    dojo.query(".generalColClass.mailToAccountableClass").forEach(function(domNode){domNode.style.display="inline-block";});';
      $colScript .= '  } else {';
      $colScript .= '    dojo.query(".mailToAccountableClass").forEach(function(domNode){domNode.style.display="none";});';
      $colScript .= '  }';
      $colScript .= '</script>';
    }
    if ($colName=='idType') {
    	//Ticket #157 - Fixed PBE
    	$colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
    	$colScript .= '  dijit.byId("idEmailTemplate").set("value", null);';
    	$colScript .= '  var mailable=dijit.byId("idMailable").get("value");';
    	$colScript .= '  if (!mailable) {';
    	$colScript .= '    refreshList("idEmailTemplate","idMailable", null);';
    	$colScript .= '  } else if (this.value) {';
    	$colScript .= '    refreshList("idEmailTemplate","idMailable", mailable, null, null, null, "idType",this.value);';
    	$colScript .= '  } else {';
    	$colScript .= '    refreshList("idEmailTemplate","idMailable", mailable, null, null, null, "idType",null);';
    	$colScript .= '  }';
    	$colScript .= '</script>';
    	//Fixed PBE
    }
    return $colScript;
  }
}
?>