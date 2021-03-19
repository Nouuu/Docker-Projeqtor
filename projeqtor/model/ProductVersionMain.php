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
class ProductVersionMain extends Version {

  // List of fields that will be exposed in general user interface
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $scope;
  public $idProduct;
  public $versionNumber;
  public $idProductVersionType;
  public $name;
  public $idContact;
  public $idResource;
  public $creationDate;
  public $idUser;
  public $_tab_4_4 = array('initial', 'planned', 'real', 'done', 'startDate', 'deliveryDate', 'eisDate', 'endDate');
  public $initialStartDate;
  public $plannedStartDate;
  public $realStartDate;
  public $isStarted;
  public $initialDeliveryDate;
  public $plannedDeliveryDate;
  public $realDeliveryDate;
  public $isDelivered;
  public $initialEisDate;
  public $plannedEisDate;
  public $realEisDate;
  public $isEis;
  public $initialEndDate;
  public $plannedEndDate;
  public $realEndDate;
  public $idle;
  public $idMilestone;
  public $idStatus; //ADD qCazelles - Ticket #53
  public $description;
  public $_sec_VersionprojectProjects;
  public $_VersionProject=array();
  public $_sec_ProductVersionComposition;
  public $_productVersionComposition=array();
  //ADD dFayolle
  public $_spe_hideClosedComposition;
  //END dFayolle
  public $_spe_flatStructure;
  //ADD qCazelles - Version compatibility
  public $_sec_ProductVersionCompatibility;
  public $_productVersionCompatibility=array();
  //END ADD qCazelles - Version compatibility
  
  //ADD qCazelles - LANG 2
  public $_sec_language;
  public $_productLanguage;
  public $_sec_context;
  public $_productContext;
  //END ADD qCazelles - LANG 2
  public $_sec_Tenders;
  public $_spe_tenders;
  //ADD qCazelles
  public $_sec_delivery;
  public $_versionDelivery;
  //END ADD qCazelles
  public $_sec_Ticket;
  public $_spe_tickets;
  public $_sec_Link;
  public $_Link = array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="20%" >${versionName}</th>
    <th field="nameProduct" width="15%" >${productName}</th>
    <th field="nameProductVersionType" width="10%" >${type}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="plannedEisDate" width="7.5%" formatter="dateFormatter">${plannedEis}</th>
    <th field="realEisDate" width="7.5%" formatter="dateFormatter">${realEis}</th>
    <th field="plannedEndDate" width="7.5%" formatter="dateFormatter">${plannedEnd}</th>
    <th field="realEndDate" width="7.5%" formatter="dateFormatter">${realEnd}</th>
    <th field="isEis" width="5%" formatter="booleanFormatter" >${isEis}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array(
      "name"=>"required", 
      "idProduct"=>"required",
      "scope"=>"hidden",
      "idStatus"=>"required", //ADD qCazelles - Ticket #53
      "idProductVersionType"=>"required", // ADD PBE - Ticket #53
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
  
  private static $_databaseColumnName = array('idProductVersionType'=>'idVersionType');
  
  private static $_databaseTableName = 'version';
  private static $_databaseCriteria = array('scope'=>'Product');
  
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
    if (Parameter::getGlobalParameter('productVersionOnDelivery') != 'YES') {
    	self::$_fieldsAttributes['_sec_delivery']='hidden';
    	self::$_fieldsAttributes['_versionDelivery']='hidden';
    }
    if (Parameter::getGlobalParameter('manageTicketVersion') != 'YES') {
      self::$_fieldsAttributes["_sec_Ticket"]='hidden';
      self::$_fieldsAttributes["_spe_tickets"]='hidden';
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
      if ($colName=="versionNumber" or $colName=="idProduct") {
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
    global $print;
    $result="";
    if ($item=='tenders') {
       Tender::drawListFromCriteria('id'.get_class($this),$this->id);
    } else if ($item=='flatStructure' and !$print and $this->id) {
      $result=parent::drawFlatStructureButton('ProductVersion',$this->id);
      return $result;
    } 
    // ADD tLaguerie & dFayolle
    $showClosedItemCompositionProduct=(Parameter::getUserParameter('showClosedItemCompositionProduct')!='0')?true:false;
    if ($item=='hideClosedComposition' and !$print and $this->id){
      $result.='<div style="position:absolute;right:5px;top:3px;">';
      $result.='<label for="showClosedItemCompositionProduct" class="dijitTitlePaneTitle" style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:'.((isNewGui())?'50':'150').'px">'.i18n('labelShowIdle'.((isNewGui())?'Short':'')).'</label>';
      $result.='<div id="hideClosedComposition" style="'.((isNewGui())?'margin-top:14px':'').'"  dojoType="dijit.form.CheckBox" type="checkbox" '.(($showClosedItemCompositionProduct)?'checked':'');
      $result.=' title="'.i18n('labelShowIdle').'" >';
      $result.='<script type="dojo/connect" event="onChange" args="evt">';
      $result.=' saveUserParameter("showClosedItemCompositionProduct",((this.checked)?"1":"0"));';
      $result.=' if (checkFormChangeInProgress()) {return false;}';
      $result.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
      $result.=' </script>';
      $result.='</div>';
      $result.='</div>';
    }
    // END tLaguerie & dFayolle
    return $result;
  }
  
  /* START ADD molives 11/04/2018 Ticket 105 */
  public function defaultLanguageVersion()
  {
    $product = new Product($this->idProduct);
    $lang = new ProductLanguage();
    $crit=array('scope'=>"Product",'idProduct'=>$product->id);
    
    $listLangProduct=$lang->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    $cptLang = count($listLangProduct);
    
    $langVersion = new VersionLanguage();
    $crit2 =array('idVersion'=>$this->id);
    $listLangProductVersion=$langVersion->getSqlElementsFromCriteria($crit2,null,null,null,null,true);
    $cptLangVersion = count($listLangProductVersion);
    
    //if parent component have only 1 language and component version has no language
    if ($cptLang == 1 && $cptLangVersion == 0){
      $langVersion->idLanguage = $listLangProduct[0]->idLanguage;
      $langVersion->scope = $this->scope;
      $langVersion->idVersion = $this->id;
      $langVersion->creationDate = date('Y-m-d');
      $returnvalue = $langVersion->save();
    }
    return 1;
  }
  /* END ADD molives 11/04/2018 Ticket 105 */
  
  
  public function save() {
    global $doNotUpdateAllVersionProject;
    $old=$this->getOld();
    $this->scope='Product';
    $paramNameAutoformat=Parameter::getGlobalParameter('versionNameAutoformat');
    if ($paramNameAutoformat=='YES') {
      $separator=Parameter::getGlobalParameter('versionNameAutoformatSeparator');
      $this->name=SqlList::getNameFromId('Product', $this->idProduct).$separator.$this->versionNumber;
    }
  	$result=parent::save();
  	$this->defaultLanguageVersion();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
  	if ($this->idle) {
  		VersionProject::updateIdle('Version', $this->id);
  	}
  	$compList=array();
  	if ($this->id and $old->idProduct!=$this->idProduct) {
  	  $p=new Product($this->idProduct,true);
  	  $compList=$p->getComposition(false,true);
  	  $pold=new Product($old->idProduct,true);
  	  $compList=array_merge_preserve_keys($pold->getComposition(false,true),$compList);
  	} else {
  	  $doNotUpdateAllVersionProject=true;
  	}
  	foreach($compList as $compId=>$compName) {
  	  $comp=new Component($compId,true);
  	  $comp->updateAllVersionProject();
  	}
  	// Propagate Product-Project link to Version-Project link
  	if ($old->idProduct and $old->idProduct!=$this->idProduct) {
  	  $pp=new ProductProject();
  	  $ppList=$pp->getSqlElementsFromCriteria(array('idProduct'=>$old->idProduct),null,null,null,null,true);
  	  foreach ($ppList as $pp) {
  	    $vp=SqlElement::getSingleSqlElementFromCriteria('VersionProject', array('idVersion'=>$this->id, 'idProject'=>$pp->idProject));
  	    if ($vp->id) $vp->delete();
  	  }
  	}
  	if ($this->idProduct) {
  	  $pp=new ProductProject();
  	  $ppList=$pp->getSqlElementsFromCriteria(array('idProduct'=>$this->idProduct),null,null,null,null,true);
  	  foreach ($ppList as $pp) {
  	    $vp=SqlElement::getSingleSqlElementFromCriteria('VersionProject', array('idVersion'=>$this->id, 'idProject'=>$pp->idProject));
  	    if (! $vp->id) {
  	      $vp->idVersion=$this->id;
  	      $vp->idProject=$pp->idProject;
  	    }
  	    $vp->startDate=$pp->startDate;
  	    $vp->endDate=$pp->endDate;
  	    $vp->idle=$pp->idle;
  	    $vp->save();
  	  }
  	}
  	//gautier #subscription
  	if($old->idProduct != $this->idProduct){
  	  parent::changeVersionOfProduct();
  	}
  	parent::addVersionSubProduct();
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
  
  // Retrive composition in terms of component versions (will not retreive product versionss in the composition of the product version)
  public function getComposition($withName=true,$reculsively=false) {
    $result=array();
    $list=ProductVersionStructure::getComposition($this->id,(($reculsively)?'all':1));
    foreach ($list as $pvsSharp=>$pvs) {
      $result[$pvs]=($withName)?SqlList::getNameFromId('ComponentVersion', $pvs):$pvs;
    }
    return $result;
  }
  public function delete() {
    global $doNotUpdateAllVersionProject;
    $doNotUpdateAllVersionProject=true;
    $result=parent::delete();
    $pvs=new ProductVersionStructure();
    $crit=array('idProductVersion'=>$this->id);
    $list=$pvs->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $pvs) {
      $pvs->delete();
    }
    $doNotUpdateAllVersionProject=false;
    $ps=new ProductStructure();
    $psList=$ps->getSqlElementsFromCriteria(array('idProduct'=>$this->idProduct));
    foreach($psList as $ps) {
      $comp=new Component($ps->idComponent);
      $comp->updateAllVersionProject();
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
  	//add pbe ticket #135
  	$this->initialStartDate = NULL;
  	$this->plannedStartDate = NULL;
  	$this->realStartDate = NULL;
  	$this->isStarted=null;
  	$this->initialDeliveryDate = NULL;
  	$this->plannedDeliveryDate = NULL;
  	$this->realDeliveryDate = NULL;
  	$this->isDelivered=null;
  	//end pbe ticket #135
    $result=parent::copy(); 
    $pp=new VersionProject();
    $crit=array('idVersion'=>$this->id);
    $list=$pp->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $pp) {
      $pp->idVersion=$result->id;
      $pp->id=null;
      $pp->save();
    }
    $pvs=new ProductVersionStructure();
    $crit=array('idProductVersion'=>$this->id);
    $list=$pvs->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    foreach ($list as $pvs) {
      $pvs->idProductVersion=$result->id;
      $pvs->id=null;
      $pvs->creationDate=date('Y-m-d');
      $pvs->save();
    }
    //ADD qCazelles - Version compatibility
    $vc=new VersionCompatibility();
    $crit=array('idVersionA'=>$this->id);
    $list=$vc->getSqlElementsFromCriteria($crit,null,null,null,null,true);
    $crit=array('idVersionB'=>$this->id);
    foreach ($vc->getSqlElementsFromCriteria($crit,null,null,null,null,true) as $vcItem) {
    	$list[]=$vcItem;
    }
    foreach ($list as $vc) {
    	if ($vc->idVersionA==$this->id) $vc->idVersionA=$result->id;
    	else $vc->idVersionB=$result->id;
    	$vc->id=null;
    	$vc->creationDate=date('Y-m-d');
    	$vc->save();
    }
    //END ADD qCazelles - Version compatibility
    //add atrancoso ticket#149
    // Copy language
    $lang = new VersionLanguage();
    $listLang=$lang->getSqlElementsFromCriteria(array('idVersion'=>$this->id),null,null,null,null,true);
    //gautier #4350
    foreach($listLang as $lang){
      //$lang->id = NULL;
      $newLang = new VersionLanguage();
      $newLang->creationDate = date('Y-m-d');
      $newLang->idLanguage = $lang->idLanguage;
      $newLang->idVersion = $result->id;
      $newLang->scope = $result->scope; //Add mOlives - bugLanguage - 19/04/2018
      $newLang->save();
    }
    //end add atrancoso ticket#149
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
    $ps=new ProductStructure();
    $psList=$ps->getSqlElementsFromCriteria(array('idProduct'=>$result->idProduct));
    foreach($psList as $ps) {
      $comp=new Component($ps->idComponent);
      $comp->updateAllVersionProject();
    }
    return $result;
  } 
}
?>