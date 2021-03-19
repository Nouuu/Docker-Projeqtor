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
class QuotationMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idQuotationType;
  public $idProject;
  public $idUser;
  public $creationDate;
  public $Origin;
  public $idRecipient;
  public $idClient;
  public $idContact;
  public $description;
  public $additionalInfo;
  public $_sec_treatment;
  public $idStatus;
  public $idResource;  
  public $sendDate;
  public $idDeliveryMode; 
  public $validityEndDate;
  public $idLikelihood;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  //public $initialWork;
  //public $initialPricePerDayAmount;
  //public $initialAmount; 
  public $initialEndDate;
  public $idActivityType;
  public $idPaymentDelay;
  public $_tab_5_1_smallLabel = array('untaxedAmountShort', 'tax', '', 'fullAmountShort', 'estimatedWork', 'amount');
  public $untaxedAmount;
  public $taxPct;
  public $taxAmount;
  public $fullAmount;
  public $plannedWork;
  public $comment;  

  //public $_sec_BillLine;
  public $_BillLine=array();
  public $_BillLine_colSpan="2";
  public $_sec_situation;
  public $idSituation;
  public $_spe_situation;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameClient" width="10%" >${idClient}</th>
    <th field="nameQuotationType" width="10%" >${idQuotationType}</th>
    <th field="name" width="15%" >${name}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" formatter="thumbName22" width="10%" >${responsible}</th>
    <th field="validityEndDate" width="10%" formatter="dateFormatter" >${offerValidityEndDate}</th>
  	<th field="untaxedAmount" formatter="costFormatter" width="10%" >${untaxedAmount}</th>
  	<th field="fullAmount" formatter="costFormatter" width="10%" >${fullAmount}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", 
                                  "idQuotationType"=>"required",
  		                            "idProject"=>"required",
  		                            "reference"=>"readonly",
                                  "name"=>"required", 
                                  "idStatus"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
  								                "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "taxAmount"=>"calculated,readonly",
                                  "idSituation"=>"readonly"
  );  
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer', 
                                                   'idResource'=> 'responsible',
  		                      'validityEndDate'=>'offerValidityEndDate',
  													'idActivity'=>'linkActivity',
  		                      'initialEndDate'=>'actualEndDate',
  		                      //'initialWork'=>'estimatedEffort',
  		                      //'initialAmount'=>'plannedAmount',
  		                      //'initialPricePerDayAmount'=>'pricePerDay',
                            'description'=>'request',
                            'idPaymentDelay'=>'paymentDelay',
                            'idDeliveryMode'=>'sendMode', 
                            'plannedWork'=>'estimatedWork','idSituation'=>'actualSituation');
  private static $_databaseColumnName = array('taxPct'=>'tax');
//  private static $_databaseColumnName = array('idResource'=>'idUser');
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($withoutDependentObjects) return; 
    if (count($this->_BillLine)) {
      self::$_fieldsAttributes['untaxedAmount']='readonly';
      self::$_fieldsAttributes['plannedWork']='readonly';
    }
    if ($this->fullAmount) {
      $this->taxAmount=$this->fullAmount-$this->untaxedAmount;
    }
    if(trim(Module::isModuleActive('moduleSituation')) != 1){
    	self::$_fieldsAttributes['_sec_situation']='hidden';
    	self::$_fieldsAttributes['idSituation']='hidden';
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

/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
   		
    if ($this->validityEndDate and $this->sendDate and $this->validityEndDate<$this->sendDate) {
      $result.='<br/>' . i18n('errorStartEndDates',array(i18n('colSendDate'),i18n('colOfferValidityEndDate')));
    }
   	$defaultControl=parent::control();
  	
   	if ($defaultControl!='OK') {
   		$result.=$defaultControl;
   	}
   	
   	if ($result=="") $result='OK';
	
    return $result;
  }

  
  /** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $old=$this->getOld();
  	$result='';
    if (trim($this->id)=='') {
    	// fill the creatin date if it's empty - creationDate is not empty for import ! 
    	if ($this->creationDate=='') $this->creationDate=date('Y-m-d');
	  }
	  if (trim($this->idClient)) {
	    $client=new Client($this->idClient);
	    if ($client->taxPct!='' and !$this->taxPct) {
	      $this->taxPct=$client->taxPct;
	    }
	    if (!trim($this->idPaymentDelay)) {
	      $this->idPaymentDelay=$client->idPaymentDelay;
	    }
	  }
    $this->name=trim($this->name);
    $paramImputOfBillLineClient = Parameter::getGlobalParameter('ImputOfBillLineClient');
	  $billLine=new BillLine();
	  $crit = array("refType"=> "Quotation", "refId"=>$this->id);
	  $billLineList = $billLine->getSqlElementsFromCriteria($crit,false);
	  if (count($billLineList)>0) {
  	  $amount=0;
  	  $numberDays=0;
  	  foreach ($billLineList as $line) {
  	    $amount+=$line->amount;
  	    $numberDays+=$line->numberDays;
  	  }
  	  if($paramImputOfBillLineClient == 'HT'){
    	  $this->untaxedAmount=$amount;
  	  }else{
  	    $this->fullAmount=$amount;
  	  }
  	  $this->plannedWork=$numberDays;
	  }
	  if($paramImputOfBillLineClient == 'HT'){
      $this->fullAmount=$this->untaxedAmount*(1+$this->taxPct/100);
	  }else{
	    $this->untaxedAmount=$this->fullAmount/(1+$this->taxPct/100);
	  }
    if($this->idSituation){
    	$situation = new Situation($this->idSituation);
    	if($this->idProject != $situation->idProject){
    		$critWhere = array('refType'=>get_class($this),'refId'=>$this->id);
    		$situationList = $situation->getSqlElementsFromCriteria($critWhere,null,null);
    		foreach ($situationList as $sit){
    		  $sit->idProject = $this->idProject;
    		  $sit->save();
    		}
    		ProjectSituation::updateLastSituation($old, $this, $situation);
    	}
    }
    $result = parent::save();
    return $result;
  }
  
  // Save without extra save() feature and without controls
  public function simpleSave() {
    return parent::saveForced();
  }
    /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    
    $colScript = parent::getValidationScript($colName);
    if ($colName=="untaxedAmount" || $colName=="taxPct" || $colName=="fullAmount" ) {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $paramImputOfAmountClient = Parameter::getGlobalParameter('ImputOfAmountClient');
      if (count($this->_BillLine)) {
        $paramImputOfAmountClient = Parameter::getGlobalParameter('ImputOfBillLineClient');
      }
      if($paramImputOfAmountClient == 'HT'){
        $colScript .= '  updateBillTotal();';
      }else{
        $colScript .= '  updateBillTotalTTC();';
      }
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idProject") {
    	$colScript .= '<script type="dojo/connect" event="onChange" >';
    	$colScript .= '  setClientValueFromProject("idClient",this.value);';
    	$colScript .= '  formChanged();';
    	$colScript .= '</script>';
    } else if ($colName=="idClient") {
    	$colScript .= '<script type="dojo/connect" event="onChange" >';
    	$colScript .= '  refreshList("idContact", "idClient", this.value, null, null, false);';
    	$colScript .= '  formChanged();';
    	$colScript .= '</script>';
    }   
    return $colScript;
  }
    
  private function zeroIfNull($value) {
  	$val = $value;
  	if (!$val || $val=='' || !is_numeric($val)) {
  		$val=0;
  	} else { 
  		$val=$val*1;
  	}
  	
  	return $val;
  	
  }
  
  public function setAttributes() {
    if (count($this->_BillLine)) {
      self::$_fieldsAttributes['untaxedAmount']='readonly';
      self::$_fieldsAttributes['fullAmount']='readonly';
    }
    $paramImputOfAmountClient = Parameter::getGlobalParameter('ImputOfAmountClient');
    if($paramImputOfAmountClient == 'HT'){
      self::$_fieldsAttributes['fullAmount']="readonly";
    }else{
      self::$_fieldsAttributes['untaxedAmount']="readonly";
    }
  }
  
  public function drawSpecificItem($item, $included=false) {
  	global $print, $comboDetail, $nbColMax;
  	$result = "";
  	if ($item == 'situation') {
  		$situation = new Situation();
  		$situation->drawSituationHistory($this);
  	}
  	return $result;
  }
  
}
?>