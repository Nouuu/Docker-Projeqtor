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
 * creation of the description of the content for a bill.
 */  
require_once('_securityCheck.php'); 
class BillMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $billId;
  public $name;
  public $idBillType;
  public $idProject;
  public $idUser;
  public $creationDate;
  public $date;
  public $idPaymentDelay;
  public $paymentDueDate;
  public $idClient;
  public $idContact;
  public $idRecipient;
  public $Origin;
  public $_spe_billingType;
  public $_sec_treatment;  
  public $idStatus;
  public $idResource;
  public $sendDate;
  public $idDeliveryMode;
  public $done;
  public $idle;
  public $cancelled;
  public $_lib_cancelled;
  public $_tab_5_1_smallLabel = array('untaxedAmountShort', 'tax', '', 'fullAmountShort','commandAmountPctShort', 'amount');
  public $untaxedAmount;
  public $taxPct;
  public $taxAmount;
  public $fullAmount;
  public $commandAmountPct;
  public $_tab_3_1_smallLabel = array('date', 'amount', 'paymentComplete', 'payment');
  public $paymentDate;
  public $paymentAmount;
  public $paymentDone;
  public $_spe_paymentsList;
  public $paymentsCount;
  public $description;
  public $billingType;   
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
    <th field="reference" width="10%" >${reference}</th>  
    <th field="nameClient" width="15%" >${idClient}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="name" width="15%" >${name}</th>
    <th field="date" formatter="dateFormatter" width="10%" >${date}</th>  
    <th field="nameRecipient" width="10%" >${idRecipient}</th>
    <th field="fullAmount" formatter="costFormatter" width="10%" >${fullAmount}</th>
    <th field="colorNameStatus" formatter="colorNameFormatter" width="10%" >${idStatus}</th>
    ';
  
  private static $_fieldsAttributes=array('name'=>'required','id'=>'nobr',
  										'idStatus'=>'required',
                      'reference'=>'nobr,size1/3',
                      'idBillType'=>'required',
                      'idProject'=>'required',
// BEGIN - ADD BY TABARY - See billId      
                      'billId'=>'size1/3,display',
// END - ADD BY TABARY - See billId                            
                      'taxAmount'=>'calculated,readonly',
  										'idPrec'=>'required',
                      'billingType'=>'hidden',
                      'fullAmount'=>'readonly',
                      'untaxedAmount'=>'readonly',
                      "idle"=>"nobr",
                      "cancelled"=>"nobr",
                      'paymentDueDate'=>'readonly',
                      'paymentsCount'=>'hidden',
                      'idSituation'=>'readonly'
  );  
  
  private static $_colCaptionTransposition = array('description'=>'comment',
                                                   'idContact'=>'billContact',
                                                   'idPaymentDelay'=>'paymentDelay',
                                                   'idDeliveryMode'=>'sendMode',
                                                   "idUser"=>"issuer",
                                                   'idResource'=>'responsible',
                                                   'paymentDone'=>'paymentComplete',
                                                   'idSituation'=>'actualSituation'
  );
  
  private static $_databaseColumnName = array('taxPct'=>'tax');
  public $_calculateForColumn=array("name"=>"concat(coalesce(reference,''),' - ',name,' (',coalesce(fullAmount,0),')')");
  public $_sortCriteriaForList="fullAmount, id";
    
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($withoutDependentObjects) return;
    if (! $this->id) {
      $this->commandAmountPct=100;
    }
    if ($this->done) {
    	self::$_fieldsAttributes['idClient']='readonly';
    	self::$_fieldsAttributes['idBillType']='readonly';
    	self::$_fieldsAttributes['date']='readonly';
    	self::$_fieldsAttributes['idProject']='readonly';
    	self::$_fieldsAttributes['idRecipient']='readonly';
    	self::$_fieldsAttributes['idContact']='readonly';
    	self::$_fieldsAttributes['taxPct']='readonly';
    	self::$_fieldsAttributes['idPaymentDelay']='readonly';
    }
    if (count($this->_BillLine)) {
    	self::$_fieldsAttributes['idProject']='readonly';
    }
    if ($this->fullAmount) {
      $this->taxAmount=$this->fullAmount-$this->untaxedAmount;
    }
    if ($this->paymentDone) {
      self::$_fieldsAttributes['paymentDate']='readonly';
      self::$_fieldsAttributes['paymentAmount']='readonly';
    }
    if ($this->paymentsCount>0) {
      self::$_fieldsAttributes['paymentDate']='readonly';
      self::$_fieldsAttributes['paymentAmount']='readonly';
      self::$_fieldsAttributes['paymentDone']='readonly';
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
    
    // When bill is done
    if ( $this->done ) {
    	// some data is mandatory
      if ( ! $this->date ){
    	  $result.="<br/>" . i18n('messageMandatory',array(i18n('colDate')));
      }
      if ( ! trim($this->idClient) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdClient')));
      }
      if ( ! trim($this->idContact) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdContact')));
      }
      if ( ! trim($this->idRecipient) ){
        $result.="<br/>" . i18n('messageMandatory',array(i18n('colIdRecipient')));
      }
      // Lines must exist when bill is done
    	if(!$this->id) {
    		$result.="<br/>" . i18n('errorEmptyBill');
    	} else {   	
    		$line = new BillLine();
    		$crit = array("refId"=>$this->id);
    		$lineList = $line->getSqlElementsFromCriteria($crit,false);
    		if (count($lineList)==0) {
    			$result.="<br/>" . i18n('errorEmptyBill');
    		}
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
  

  /** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  public function deleteControl()
  {
  	$result="";
  	
  	// Cannot delete done bill
  	$status=new Status($this->idStatus);
  	if ($status->setDoneStatus)	{
  		$result .= "<br/>" . i18n("errorDeleteDoneBill");
  	}
  	
  	// Cannot delete bill with lines
    /*$line = new BillLine();
    $crit = array("refId"=>$this->id);
    $lineList = $line->getSqlElementsFromCriteria($crit,false);
    if (count($lineList)>0) {
      $result.="<br/>" . i18n('errorControlDelete') . "<br/>&nbsp;-&nbsp;" . i18n('BillLine') . " (" . count($lineList) . ")";; ;
    }*/
  	
    if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  
  /** =========================================================================
   * Overrides SqlElement::delete() function to add specific treatments
   * @see persistence/SqlElement#delete()
   * @return the return message of persistence/SqlElement#delete() method
   */  
  public function delete()
  {
  	$result = parent::delete();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }	
    $crit=array('idBill'=>$this->id);
    $w=new Work();
    $lstWork=$w->getSqlElementsFromCriteria($crit);
    foreach($lstWork as $work) {
      $work->idBill=null;
      $work->simpleSave();
    }
    if(Module::isModuleActive('moduleGestionCA')){
      $project = new Project($this->idProject);
      $project->ProjectPlanningElement->updateCA(true);
    }
	  return $result;
  }
    

  /** =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */  

	public function save() {
		$oldBill = $this->getOld();
	
		// billingType
		$proj=new Project($this->idProject);
		$type=new ProjectType($proj->idProjectType);
		$this->billingType=$type->internalData;

		// Calclate bill id
		if ($this->done and ! $this->billId) {
			$numStart=Parameter::getGlobalParameter('billNumStart');
			$bill = new Bill();
			$crit = array("done"=> "1");
			$billList = $bill->getSqlElementsFromCriteria($crit,false);
			$num=count($billList)+$numStart;
			$this->billId = $num;
			$this->setReference();
		}

		// Get Client
		if (! trim($this->idClient)) {
			$this->idClient=$proj->idClient;
		}
		// get Contact
	  if (! trim($this->idContact)) {
      $this->idContact=$proj->idContact;
    }

		// Get the tax from Client / Contact / Recipient 
		if (trim($this->idClient)) {
			$client=new Client($this->idClient);
			if ($client->taxPct!='' and !$this->taxPct) {
		  	$this->taxPct=$client->taxPct;
			}
			if (!trim($this->idPaymentDelay)) {
			  $this->idPaymentDelay=$client->idPaymentDelay;
			}
		}
	  if (trim($this->idRecipient)) {
      $recipient=new Recipient($this->idRecipient);
      if ($recipient->taxFree) {
      	$this->taxPct=0;
      }
    }
		if (trim($this->idPaymentDelay) and $this->date) {
		  $delay=new PaymentDelay($this->idPaymentDelay);
		  $date=addDaysToDate($this->date, $delay->days);
		  if ($delay->endOfMonth) {
		    $date=date("Y-m-t", strtotime($date));
		  }
		  $this->paymentDueDate=$date;
		}
		if ($this->paymentAmount==$this->fullAmount and $this->fullAmount>0) {
		  $this->paymentDone=1;
		}
		
		// calculate amounts for bill lines
		$paramImputOfAmountClient = Parameter::getGlobalParameter('ImputOfBillLineClient');
		$billLine=new BillLine();
		$crit = array("refType"=> "Bill", "refId"=>$this->id);
    $billLineList = $billLine->getSqlElementsFromCriteria($crit,false);
    $amount=0;
    foreach ($billLineList as $line) {
    	$amount+=$line->amount;
    }
    if($paramImputOfAmountClient == "HT"){
      $this->untaxedAmount=$amount;
      $this->fullAmount=$amount*(1+$this->taxPct/100);
    }else{
      $this->fullAmount=$amount;
      $this->untaxedAmount=$this->fullAmount/(1+$this->taxPct/100);
    }
    $this->retreivePayments(false);
    
	if($this->idSituation){
    	$situation = new Situation($this->idSituation);
    	if($this->idProject != $situation->idProject){
    		$critWhere = array('refType'=>get_class($this),'refId'=>$this->id);
    		$situationList = $situation->getSqlElementsFromCriteria($critWhere,null,null);
    		foreach ($situationList as $sit){
    		  $sit->idProject = $this->idProject;
    		  $sit->save();
    		}
    		ProjectSituation::updateLastSituation($oldBill, $this, $situation);
    	}
    }
	$result=parent::save();
	
	if(Module::isModuleActive('moduleGestionCA')){
		$project = new Project($this->idProject);
		$projectList = $project->getRecursiveSubProjectsFlatList(true, true);
		$projectList = array_flip($projectList);
		$projectList = '(0,'.implode(',',$projectList).')';
		$where = 'idProject in '.$projectList.' and idle = 0';
		$paramAmount = Parameter::getGlobalParameter('ImputOfAmountClient');
		$billAmount = ($paramAmount == 'HT')?'untaxedAmount':'fullAmount';
		$project->ProjectPlanningElement->billSum = $this->sumSqlElementsFromCriteria($billAmount, null, $where);
		$project->ProjectPlanningElement->updateCA(true);
	}
	return $result;
	}  

	/** ==========================================================================
	 * Return the validation sript for some fields
	 * @return the validation javascript (for dojo frameword)
	 */
	public function getValidationScript($colName) {
	
		$colScript = parent::getValidationScript($colName);
		if ($colName=="untaxedAmount" || $colName=="taxPct" || $colName=="fullAmount") {
		  $paramImputOfAmountClient = Parameter::getGlobalParameter('ImputOfBillLineClient');
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      if($paramImputOfAmountClient == "HT"){
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
	
  public function drawSpecificItem($item){
  	global $print,$displayWidth;
  	$labelWidth=175; // To be changed if changes in css file (label and .label)
  	$largeWidth=( (intval($displayWidth)+30) / 2) - $labelWidth;
    $result="";
    if ($item=='billingType') {
    	$result .="<table><tr><td class='label' valign='top'><label>" . i18n('colBillingType') . "&nbsp;".((isNewGui())?'':':&nbsp;')."</label>";
      $result .="</td><td style='".((isNewGui()?'padding-top:5px;':''))."'>";
      if ($print) {
      	$result.=i18n('billingType'.$this->billingType);
      } else {
	      /*$result .='<input dojoType="dijit.form.TextBox" class="input" ';
	      if ($this->billingType) {
	        $result .=' value="' .  i18n('billingType'.$this->billingType) . '"';
	      } 
	      $largeWidth=setWidthPct($displayWidth, $print, $largeWidth, $this)/2;
	      $result.=' style="width:100%;"';
	      $result.=' readonly="readonly"';
	      $result .='/>';*/
        if ($this->billingType) $result .= ((isNewGui()?'"':'')). i18n('billingType'.$this->billingType) .((isNewGui()?'"':''));
      }
	    $result .= '</td></tr></table>';
    } else if ($item=='paymentsList') {
      if (!$this->id) return '';
      $pay=new Payment();
      $payList=$pay->getSqlElementsFromCriteria(array('idBill'=>$this->id));
      //$result.='</td><td>';
      $result.='<div style="position:relative;top:0px;left:80px;width:350px; ">';
      $result.='<table style="width:100%">';
      foreach ($payList as $pay) {
        $result.='<tr class="noteHeader pointer" onClick="gotoElement(\'Payment\','.htmlEncode($pay->id).');">';
        $result.='<td style="padding:0px 5px; width:20px;">';
        $result.= formatSmallButton('Payment');
        $result.='</td>';
        $result.='<td style="width:30px">#'.htmlEncode($pay->id).'</td><td>&nbsp;&nbsp;&nbsp;</td>';
        $result.='<td style="padding:0px 5px;text-align:left;width:250px">'.htmlEncode($pay->name).'</td>';
        $result.='<td style="padding:0px 5px;text-align:right;width:50px">'.htmlDisplayCurrency($pay->paymentAmount,false).'</td>';
        $result.='</tr>';
      }
      $result.='</table>';
      $result.='</div>';
    }else if($item=='situation'){
      $situation = new Situation();
      $situation->drawSituationHistory($this);
    }
    return $result;
  }
  
  // Save without extra save() feature and without controls
  public function simpleSave() {
     return parent::saveForced();
  }
  
  public function retreivePayments($save=true,$isDeletePayment=false) {   
    $pay=new Payment();  
    if ($this->id) {
      $payList=$pay->getSqlElementsFromCriteria(array('idBill'=>$this->id));
    } else {
      $payList=array();
    }
    if (count($payList)==0 or $this->id==null) {
      $this->paymentsCount=0;
      $this->paymentDone=0;
      if($isDeletePayment){
        $this->paymentDate = null;
        $this->paymentAmount = 0;
      }
      if ($save) {
        $this->simpleSave();
      }
      return;
    }
    $this->paymentsCount=count($payList);
    $this->paymentAmount=0;
    $this->paymentDate='';
    $this->paymentDone=0;
    foreach ($payList as $pay) {
      $this->paymentAmount+=$pay->paymentAmount;
      if ($pay->paymentDate>$this->paymentDate) $this->paymentDate=$pay->paymentDate;
    }
    if ($this->paymentAmount>=$this->fullAmount and $this->fullAmount>0) $this->paymentDone=1;
    if ($save) {
      $this->simpleSave();
    }
  }

}
?>