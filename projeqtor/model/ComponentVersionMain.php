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
class ComponentVersionMain extends Version {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $scope;
  public $idComponent;
  public $versionNumber;
  public $idComponentVersionType;
  public $name;
  public $idContact;
  public $idResource;
  public $creationDate;
  public $idUser;
  //CHANGE qCazelles - dateComposition
  //OLD
  //public $_tab_4_2 = array('initial', 'planned', 'real', 'done', 'eisDate', 'endDate');
  //NEW
  public $_tab_4_4 = array('initial', 'planned', 'real', 'done', 'startDate', 'deliveryDate', 'eisDate', 'endDate');
  //ADD
  public $initialStartDate;
  public $plannedStartDate;
  public $realStartDate;
  public $isStarted;
  public $initialDeliveryDate;
  public $plannedDeliveryDate;
  public $realDeliveryDate;
  public $isDelivered;
  //END ADD qCazelles - dateComposition
  public $initialEisDate;
  public $plannedEisDate;
  public $realEisDate;
  public $isEis;
  public $initialEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $idle;
  public $idStatus; //ADD qCazelles - Ticket #53
  public $description;
  public $_sec_ComponentVersionStructure;
  public $_componentVersionStructure=array();
  //ADD dFayolle
  public $_spe_hideClosedStructure;
  //END dFayolle
  public $_sec_ComponentVersionComposition;
  public $_componentVersionComposition=array();
  //ADD dFayolle
  public $_spe_hideClosedComposition;
  //END dFayolle
  //ADD qCazelles - dateComposition
  public $_spe_flatStructure;
  //END ADD qCazelles - dateComposition
  public $_sec_Tenders;
  public $_spe_tenders;
  //ADD qCazelles - LANG 2
  public $_sec_language;
  public $_productLanguage;
  public $_sec_context;
  public $_productContext;
  public $_sec_Ticket;
  public $_spe_tickets;
  public $_sec_Activity;
  public $_spe_activity;
  public $_spe_hideClosedActivity;
  //END ADD qCazelles - LANG 2
  public $_sec_Link;
  public $_Link = array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="20%" >${versionName}</th>
    <th field="nameComponent" width="15%" >${componentName}</th>
    <th field="nameComponentVersionType" width="10%" >${type}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="plannedEisDate" width="7.5%" formatter="dateFormatter">${plannedEis}</th>
    <th field="realEisDate" width="7.5%" formatter="dateFormatter">${realEis}</th>
    <th field="plannedEndDate" width="7.5%" formatter="dateFormatter">${plannedEnd}</th>
    <th field="realEndDate" width="7.5%" formatter="dateFormatter">${realEnd}</th>
    <th field="isEis" width="5%" formatter="booleanFormatter" >${isEis}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required", 
      "idComponent"=>"required",
      "idContact"=>"hidden",
      "scope"=>"hidden",
      "idProduct"=>"hidden",
      "idStatus"=>"required", //ADD qCazelles - Ticket #53
      "idComponentVersionType"=>"required", //ADD PBE - Ticket #53
      "idVersionType"=>"hidden"
  );   

  //CHANGE qCazelles - dateComposition
  //Old
  //private static $_colCaptionTransposition = array('idContact'=>'contractor', 'idResource'=>'responsible'
  //);
  //New
  private static $_colCaptionTransposition = array('idContact'=>'contractor', 'idResource'=>'responsible', 'deliveryDate'=>'versionDeliveryDate'
  );
  //END CHANGE qCazelles - dateComposition
  
  private static $_databaseColumnName = array('idComponent'=>'idProduct','idComponentVersionType'=>'idVersionType');
  private static $_databaseTableName = 'version';
  private static $_databaseCriteria = array('scope'=>'Component');
  
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
    $paramNameAutoformat=Parameter::getGlobalParameter('versionNameAutoformat');
    if ($this->id and $this->isStarted) {
      self::$_fieldsAttributes["initialStartDate"]='readonly';
      self::$_fieldsAttributes["plannedStartDate"]='readonly';
      self::$_fieldsAttributes["realStartDate"]='readonly';
    }
    if ($this->id and $this->isDelivered) {
      self::$_fieldsAttributes["initialDeliveryDate"]='readonly';
      self::$_fieldsAttributes["plannedDeliveryDate"]='readonly';
      self::$_fieldsAttributes["realDeliveryDate"]='readonly';
      self::$_fieldsAttributes["isStarted"]='readonly';
    }
    if ($this->id and $this->isEis) {
      self::$_fieldsAttributes["initialEisDate"]='readonly';
      self::$_fieldsAttributes["plannedEisDate"]='readonly';
      self::$_fieldsAttributes["realEisDate"]='readonly';
      self::$_fieldsAttributes["isDelivered"]='readonly';
    }
    if ($paramNameAutoformat=='YES') {
      self::$_fieldsAttributes['name']='readonly';
      self::$_fieldsAttributes['versionNumber']='required';
    } else {
      self::$_fieldsAttributes['versionNumber']='hidden';
    }
    $displayMilestonesStartDelivery=Parameter::getGlobalParameter('displayMilestonesStartDelivery');
    if ($displayMilestonesStartDelivery!='YES') {
      self::$_fieldsAttributes['initialStartDate']='hidden';
      self::$_fieldsAttributes['plannedStartDate']='hidden';
      self::$_fieldsAttributes['realStartDate']='hidden';
      self::$_fieldsAttributes['isStarted']='hidden';
      self::$_fieldsAttributes['initialDeliveryDate']='hidden';
      self::$_fieldsAttributes['plannedDeliveryDate']='hidden';
      self::$_fieldsAttributes['realDeliveryDate']='hidden';
      self::$_fieldsAttributes['isDelivered']='hidden';
    }
    if (Parameter::getGlobalParameter('manageTicketVersion') != 'YES') {
      self::$_fieldsAttributes["_sec_Ticket"]='hidden';
      self::$_fieldsAttributes["_spe_tickets"]='hidden';
    }
    
    if (Parameter::getGlobalParameter('displayListOfActivity') != 'YES') {
      self::$_fieldsAttributes["_sec_Activity"]='hidden';
      self::$_fieldsAttributes["_spe_activity"]='hidden';
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
  
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
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
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    if ($colName=="initialEisDate") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (! dijit.byId("plannedEisDate").get("value")) {'; 
      $colScript .= '  dijit.byId("plannedEisDate").set("value",this.value);'; 
      $colScript .= '};';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    if ($colName=="initialEndDate") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (! dijit.byId("plannedEndDate").get("value")) {'; 
      $colScript .= '  dijit.byId("plannedEndDate").set("value",this.value);'; 
      $colScript .= '};';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    if ($colName=="realEisDate") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (this.value) {'; 
      $colScript .= '  dijit.byId("isEis").set("checked",true);';
      $colScript .= '} else {;';
      $colScript .= '  dijit.byId("isEis").set("checked",false);';
      $colScript .= '};'; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    if ($colName=="isEis") { 
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (this.checked) { ';
      $colScript .= '  if (! dijit.byId("realEisDate").get("value")) {';
      $colScript .= '    var curDate = new Date();';
      $colScript .= '    dijit.byId("realEisDate").set("value", curDate); ';
      $colScript .= '  }';
      $colScript .= '} else {;';    
      $colScript .= '  dijit.byId("realEisDate").set("value", null); ';
      $colScript .= '};';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';  
    }
    if ($colName=="realEndDate") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (this.value) {'; 
      $colScript .= '  dijit.byId("idle").set("checked",true);'; 
      $colScript .= '} else {;';
      $colScript .= '  dijit.byId("idle").set("checked",false);';
      $colScript .= '};'; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    if ($colName=="idle") { 
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= 'if (this.checked) { ';
      $colScript .= '  if (! dijit.byId("realEndDate").get("value")) {';
      $colScript .= '    var curDate = new Date();';
      $colScript .= '    dijit.byId("realEndDate").set("value", curDate); ';
      $colScript .= '  }';   
      $colScript .= '} else {;';    
      $colScript .= '  dijit.byId("realEndDate").set("value", null); '; 
      $colScript .= '};';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';  
    }
    $paramNameAutoformat=Parameter::getGlobalParameter('versionNameAutoformat');
    if ($paramNameAutoformat=='YES') {
      if ($colName=="versionNumber") {
        $colScript .= '<script type="dojo/method" event="onKeyPress" >';
        $colScript .= '  setTimeout(\'updateVersionName("'.Parameter::getGlobalParameter("versionNameAutoformatSeparator").'");\',100);';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
      }
      if ($colName=="versionNumber" or $colName=="idComponent") {
        $colScript .= '<script type="dojo/connect" event="onChange" >';
        $colScript .= '  updateVersionName("'.Parameter::getGlobalParameter("versionNameAutoformatSeparator").'");';
        $colScript .= '  formChanged();';
        $colScript .= '</script>';
      }
    }
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  

  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are : 
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    $result="";
    $showClosedActivity=(Parameter::getUserParameter('showClosedActivity')!='0')?true:false;
    // ADD dFayolle
    // Checked or not
    $showClosedItemComposition=(Parameter::getUserParameter('showClosedItemComposition')!='0')?true:false;
    $showClosedItemStructure=(Parameter::getUserParameter('showClosedItemStructure')!='0')?true:false;
    // END dFayolle
    if ($item=='tenders') {
       Tender::drawListFromCriteria('id'.get_class($this),$this->id);
    }
    //ADD qCazelles - dateComposition
  	global $print;
    if ($item=='flatStructure' and !$print and $this->id) {
  		$result=parent::drawFlatStructureButton('ComponentVersion', $this->id);
  		return $result;
    }
  	if ($item=='hideClosedActivity' and !$print and $this->id){  	  
  	  $result.='<div style="position:absolute;right:5px;top:3px;">';
  	  $result.='<label for="showClosedActivity" class="dijitTitlePaneTitle" style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:'.((isNewGui())?'50':'150').'px">'.i18n('labelShowIdle'.((isNewGui())?'Short':'')).'</label>';
  	  $result.='<div id="hideClosedActivity" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.(($showClosedActivity)?'checked':'');  	  
  	  $result.=' title="'.i18n('labelShowIdle').'" >';
  	  $result.='<script type="dojo/connect" event="onChange" args="evt">';
  	  $result.=' saveUserParameter("showClosedActivity",((this.checked)?"1":"0"));';
  	  $result.=' if (checkFormChangeInProgress()) {return false;}';
  	  $result.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
  	  $result.=' </script>';
  	  $result.='</div>';
  	  $result.='</div>';
    }
    
    // ADD tlaguerie & dFayolle ticket 366 and 367
  	if ($item=='hideClosedComposition' and !$print and $this->id){
  	  $result.='<div style="position:absolute;right:5px;top:3px;">';
      $result.='<label for="showClosedItemComposition" class="dijitTitlePaneTitle" style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:'.((isNewGui())?'50':'150').'px">'.i18n('labelShowIdle'.((isNewGui())?'Short':'')).'</label>';
      $result.='<div id="hideClosedComposition" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.(($showClosedItemComposition)?'checked':'');
      $result.=' title="'.i18n('labelShowIdle').'" >';
      $result.='<script type="dojo/connect" event="onChange" args="evt">';
      $result.=' saveUserParameter("showClosedItemComposition",((this.checked)?"1":"0"));';
      $result.=' if (checkFormChangeInProgress()) {return false;}';
      $result.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
      $result.=' </script>';
      $result.='</div>';
      $result.='</div>';
    }
    if ($item=='hideClosedStructure' and !$print and $this->id){
        $result.='<div style="position:absolute;right:5px;top:3px;">';
        $result.='<label for="showClosedItemStructure" class="dijitTitlePaneTitle" style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:'.((isNewGui())?'50':'150').'px"">'.i18n('labelShowIdle'.((isNewGui())?'Short':'')).'</label>';
        $result.='<div id="hideClosedStructure" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.(($showClosedItemStructure)?'checked':'');
        $result.=' title="'.i18n('labelShowIdle').'" >';
        $result.='<script type="dojo/connect" event="onChange" args="evt">';
        $result.=' saveUserParameter("showClosedItemStructure",((this.checked)?"1":"0"));';
        $result.=' if (checkFormChangeInProgress()) {return false;}';
        $result.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
        $result.=' </script>';
        $result.='</div>';
        $result.='</div>';
    }
    // END tlaguerie & dFayolle ticket 366 and 367
  	return $result;
  	//END ADD qCazelles - dateComposition
  }
  
  /* START ADD molives 11/04/2018 Ticket 105 */
  public function defaultLanguageVersion()
  {
    $component = new Component($this->idComponent);
    $lang = new ProductLanguage();
    $crit=array('scope'=>"Component",'idProduct'=>$component->id);
    
    $listLangComp=$lang->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    $cptLang = count($listLangComp);
    
    $langVersion = new VersionLanguage();
    $crit2 =array('idVersion'=>$this->id);
    $listLangCompVersion=$langVersion->getSqlElementsFromCriteria($crit2,null,null,null,null,true);
    $cptLangVersion = count($listLangCompVersion);
    
    //if parent component have only 1 language and component version has no language
    if ($cptLang == 1 && $cptLangVersion == 0){
      $langVersion->idLanguage = $listLangComp[0]->idLanguage;
      $langVersion->scope = $this->scope;
      $langVersion->idVersion = $this->id;
      $langVersion->creationDate = date('Y-m-d');
      $returnvalue = $langVersion->save();
    }
    return 1;
  }
  /* END ADD molives 11/04/2018 Ticket 105 */
  
  
  public function save() {
    $date_tab = array('Done', 'StartDate', 'DeliveryDate', 'EisDate', 'EndDate');
    foreach ($date_tab as $date) {
      $initial = 'initial' . $date;
      $planned = 'planned' . $date;
      $real = 'real' . $date;
      if (!empty($this->$real) and empty($this->$planned)) $this->$planned = $this->$real;
      if (!empty($this->$planned) and empty($this->$initial)) $this->$initial = $this->$planned;
    }
    $this->idProduct=$this->idComponent; // idProduct set from Version parent object, but may be not set, so avoid erasing.
    $old=$this->getOld();
    $this->scope='Component';
    $paramNameAutoformat=Parameter::getGlobalParameter('versionNameAutoformat');
    if ($paramNameAutoformat=='YES') {
      $separator=Parameter::getGlobalParameter('versionNameAutoformatSeparator');
      $this->name=SqlList::getNameFromId('Component', $this->idComponent).$separator.$this->versionNumber;
    }
  	$result=parent::save();
  	$this->defaultLanguageVersion();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
  	if ($this->idle) {
  		VersionProject::updateIdle('Version', $this->id);
  	}
  	if ($this->idComponent!=$old->idComponent) {
  	  $comp=new Component($this->idComponent,true);
  	  $comp->updateAllVersionProject();
  	  if (trim($old->idComponent)) {
  	    $comp=new Component($old->idComponent,true);
  	    $comp->updateAllVersionProject();
  	  }
  	}
  	//gautier #subscription
  	if($old->idComponent != $this->idComponent){
  	  parent::changeVersionOfProduct();
  	}
  	parent::addVersionSubProduct();
  	return $result;
  }
  public function delete() {
    global $doNotUpdateAllVersionProject;
    $doNotUpdateAllVersionProject=true;
    $result=parent::delete();
    $vp=new VersionProject();
    $vpList=$vp->getSqlElementsFromCriteria(array('idVersion'=>$this->id),null,null,null,null,true);
    foreach ($vpList as $vp) {
      $vp->delete();
    }
    $pvs=new ProductVersionStructure();
    $crit=array('idProductVersion'=>$this->id);
    $list=$pvs->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $pvs) {
      $pvs->delete();
    }
    $crit=array('idComponentVersion'=>$this->id);
    $list=$pvs->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $pvs) {
      $pvs->delete();
    }
    $doNotUpdateAllVersionProject=false;
    $comp=new Component($this->idComponent);
    $comp->updateAllVersionProject();
    return $result;
  }
  public function getLinkedProjects($withName=true) {
    $vp=new VersionProject();
    $result=array();
    $vpList=$vp->getSqlElementsFromCriteria(array('idVersion'=>$this->id),null,null,null,null,true);
    foreach ($vpList as $vp) {
      $result[$vp->idProject]=($withName)?SqlList::getNameFromId('Project', $vp->idProject):$vp->idProject;
    }
    return $result;
  }
  public function getLinkedProductVersions($withName=true) {
    $result=array();
    $vpList=ProductVersionStructure::getStructure($this->id);
    foreach ($vpList as $idVers) {
      $vers=new Version($idVers);
      if ($vers->scope=='Product') {
        $result[$idVers]=($withName)?$vers->name:$vers->id;
      }
    }
    return $result;
  }
  public function copy() {
    global $doNotUpdateAllVersionProject;
    $doNotUpdateAllVersionProject=true;
  	$this->initialEisDate=null;
  	$this->plannedEisDate=null;
  	$this->realEisDate=null;
  	$this->isEis=null;
  	$this->initialEndDate=null;
  	$this->plannedEndDate=null;
  	$this->realEndDate=null;
  	$this->idle=null;
  	//add atrancoso ticket #135
	  $this->initialStartDate = NULL;
	  $this->plannedStartDate = NULL;
	  $this->realStartDate = NULL;
	  $this->isStarted=null;
	  $this->initialDeliveryDate = NULL;
	  $this->plannedDeliveryDate = NULL;
	  $this->realDeliveryDate = NULL;
	  $this->isDelivered=null;
  	//end add adtrancoso ticket #135
    $result=parent::copy();
    
    $pvs=new ProductVersionStructure();
    // Copy Composition
     $crit=array('idProductVersion'=>$this->id);
     $list=$pvs->getSqlElementsFromCriteria($crit,null,null,null,null,true);
     foreach ($list as $pvs) {
       $pvs->idProductVersion=$result->id;
       $pvs->id=null;
       $pvs->creationDate=date('Y-m-d');
       $pvs->save();
     }
    // Copy Structure
    if (!property_exists($this, '_copyVersionStructure')) {
    	$this->_copyVersionStructure='Copy';
    }
    if ($this->_copyVersionStructure=='Copy' or $this->_copyVersionStructure=='Replace') {
	    $crit=array('idComponentVersion'=>$this->id);
	    $list=$pvs->getSqlElementsFromCriteria($crit,null,null,null,null,true);
	    foreach ($list as $pvs) {
	      $pvs->idComponentVersion=$result->id;
	      if ($this->_copyVersionStructure=='Copy') {
	        $pvs->id=null;
	      }
	      $pvs->creationDate=date('Y-m-d');
	      $pvs->save();
	    }
    }
    // Copy language
    $lang = new VersionLanguage();
    $listLang=$lang->getSqlElementsFromCriteria(array('idVersion'=>$this->id),null,null,null,null,true);
    foreach($listLang as $lang){
      $cptExists=$lang->countSqlElementsFromCriteria(array('idVersion'=>$result->id,'scope'=>$result->scope,'idLanguage'=>$lang->idLanguage));
      if ($cptExists>0) continue;
      $lang->id = NULL;
      $lang->idVersion = $result->id;
      $lang->scope = $result->scope; //Add mOlives - bugLanguage - 19/04/2018
      $lang->save();
    }
    //add atrancoso ticket#160
    // Copy context
    $cont = new VersionContext();
    $listCont=$cont->getSqlElementsFromCriteria(array('idVersion'=>$this->id),null,null,null,null,true);
    foreach($listCont as $cont){
      $cont->id = NULL;
      $cont->idVersion = $result->id;
      $cont->save();
    }
    //end add atrancoso ticket#160
    $doNotUpdateAllVersionProject=false;
    $comp=new Component($result->idComponent);
    $comp->updateAllVersionProject();
    return $result;
  }

  //ADD aDaspe ticket #368
  public function buildTreeProductWhereComponentIsUsed($obj, &$arrayProduct = array())
  {
      //If the item we catch is a product, we put it in the array and return this array
      if (isset($obj->scope) && $obj->scope == 'Product') {
          $arrayProduct[] = $obj;

      } else if (isset($obj->scope) && $obj->scope == 'Component') {

          //Otherwise
          $pvs = new ProductVersionStructure();
          $version = new Version();
          //We fetch all the ID's of items that use the component we clicked on
          $listIdProductVersion = $pvs->getSqlElementsFromCriteria(array(
              "idComponentVersion" => $obj->id
          ));
          //for every item gathered, recursive call on the function
          foreach ($listIdProductVersion as $pv) {
              $v=new Version($pv->idProductVersion);
              self::buildTreeProductWhereComponentIsUsed($v, $arrayProduct);
          }
      }
      return $arrayProduct;
  }
  //END aDaspe

}
?>