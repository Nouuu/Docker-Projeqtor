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
class ProviderBillMain extends SqlElement {
  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id; 
  public $reference;
  public $name;
  public $idProviderBillType;
  public $idProject;
  public $idUser;
  public $creationDate;
  public $date;
  public $Origin;
  public $idProvider;
  public $externalReference;
  public $description;
  public $additionalInfo;
  //treatment
  public $_sec_treatment;
  public $idStatus;
  public $idResource;
  public $idContact;
  public $paymentCondition;
  public $paymentDueDate;
  public $lastPaymentDate;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
   public $_tab_4_3_smallLabel = array('untaxedAmountShort', 'tax', '', 'fullAmountShort','initial','discount', 'countTotal');
  //init
  public $untaxedAmount;
  public $taxPct;
  public $taxAmount;
  public $fullAmount;
  //remise
  public $discountAmount;
  public $_label_rate;
  public $discountRate;
  public $discountFullAmount;
  //total
  public $totalUntaxedAmount;
  public $_void_2;
  public $totalTaxAmount;
  public $totalFullAmount;
  public $discountFrom;
  public $idProjectExpense;
  public $_button_generateProjectExpense;
  public $_tab_3_1_smallLabel = array('date', 'amount', 'paymentComplete', 'payment');
  public $paymentDate;
  public $paymentAmount;
  public $paymentDone;
  public $_spe_paymentsList;
  public $paymentsCount;
  public $comment;
  public $_BillLine=array();
  public $_BillLine_colSpan="2";
  public $_sec_ProviderTerm;
  public $_ProviderTerm=array();
  public $_BillLineTerm=array();
  public $_BillLineTerm_colSpan="2";
  public $_sec_situation;
  public $idSituation;
  public $_spe_situation;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();

  public $_nbColMax=3;
 
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameProviderBillType" width="10%" >${idProviderBillType}</th>
    <th field="name" width="25%" >${name}</th>
    <th field="colorNameStatus" width="10%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="nameResource" formatter="thumbName22" width="10%" >${responsible}</th>
    <th field="paymentDueDate" width="10%" formatter="dateFormatter" >${paymentDueDate}</th>
    <th field="untaxedAmount" width="10%" formatter="costFormatter">${untaxedAmount}</th>
    <th field="totalUntaxedAmount" width="10%" formatter="costFormatter">${totalUntaxedAmount}</th>
  ';
  
  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
      "name"=>"required",
      "idProviderBillType"=>"required",
      "idStatus"=>"required",
      "handled"=>"nobr",
      "done"=>"nobr",
      "idle"=>"nobr",
      "idPaymentDelay"=>"hidden",
      "totalTaxAmount"=>"readonly",
      "taxAmount"=>"readonly",
      "totalUntaxedAmount"=>"readonly",
      "totalTaxAmount"=>"readonly",
      "totalFullAmount"=>"readonly",
      "externalReference"=>"required",
      "idleDate"=>"nobr",
      "cancelled"=>"nobr",
      "validatedWork"=>"readonly",
      "initialPricePerDayAmount"=>"hidden",
      "addPricePerDayAmount"=>"hidden",
      "validatedPricePerDayAmount"=>"hidden",
      //'paymentDueDate'=>'readonly',
      'paymentsCount'=>'hidden',
      'lastPaymentDate'=>'hidden',
      "idProject"=>"required",
      "discountFrom"=>"hidden",
      "idSituation"=>"readonly");
 
  
  private static $_colCaptionTransposition = array('idResource'=> 'responsible', 'idSituation'=>'actualSituation');
  public $_calculateForColumn=array("name"=>"concat(coalesce(externalReference,''),' - ',name,' (',coalesce(totalFullAmount,0),')')");
  private static $_databaseColumnName = array();
  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
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
  
  public function save() {
    $old=$this->getOld();
   if (trim($this->idProvider)) {
      $provider=new Provider($this->idProvider);
      if ($provider->taxPct!='' and !$this->taxPct) {
        $this->taxPct=$provider->taxPct;
      }
    }
    // Update amounts
    if ($this->untaxedAmount!=null) {
    } else {
      $this->taxAmount=null;
      $this->fullAmount=null;
    }  
    if ($this->totalUntaxedAmount!=null) {
    } else {
      $this->totalTaxAmount=null;
      $this->totalFullAmount=null;
    }
    
    //generate project expense
    if(RequestHandler::getBoolean('generateProjectExpenseButton')){
      $canCreate=securityGetAccessRightYesNo('menuProjectExpense', 'create')=="YES";
      if($canCreate){
        if(trim(RequestHandler::getValue('objectClassName'))==get_class($this)){
          $projExpense = new ProjectExpense();
          $lstType=SqlList::getList('ProjectExpenseType');
          reset($lstType);
          $projExpense->idProjectExpenseType=key($lstType);
          $lstStatus=SqlList::getList('Status');
          reset($lstStatus);
          $projExpense->idStatus=key($lstStatus);
          $projExpense->name = $this->name;
          $projExpense->idProject = $this->idProject;
          $projExpense->taxPct = $this->taxPct;
          $projExpense->realAmount = $this->totalUntaxedAmount;
          $projExpense->realTaxAmount = $this->totalTaxAmount;
          $projExpense->realFullAmount = $this->totalFullAmount;
          // #3717 : also copy provider; contact, externalReference
          $projExpense->idProvider = $this->idProvider;
          $projExpense->idContact = $this->idContact;
          $projExpense->externalReference = $this->externalReference;
          // #3717 : end
          if($this->date){
            $projExpense->expenseRealDate = $this->date;
          }else{
            $projExpense->expenseRealDate = date('Y-m-d');
          }
          $projExpense->save();
          $this->idProjectExpense = $projExpense->id;
          //ExpenseDetail::addExpenseDetailFromBillLines(get_class($this),$this->id,$projExpense->id,$projExpense->idProject);
        }
      }
    }
    $result=parent::save();
    
    //convert project expense  to bill lines
    if($this->idProjectExpense){
      $billLine = new BillLine();
      $critArray=array('refType'=>'ProviderBill','refId'=>$this->id);
      $cptBillLine=$billLine->countSqlElementsFromCriteria($critArray, false);
      if ($cptBillLine < 1) {
        $term=new ProviderTerm();
        $critArray=array('idProviderBill'=>$this->id);
        $cpt=$term->countSqlElementsFromCriteria($critArray, false);
        if ($cpt < 1 ) {
          $expD = new ExpenseDetail();
          $critArray=array('idExpense'=>$this->idProjectExpense);
          $listExpD = $expD->getSqlElementsFromCriteria($critArray);
          $number = 1;
          foreach ($listExpD as $exp){
            $detail =  SqlList::getNameFromId('ExpenseDetailType', $exp->idExpenseDetailType)."\n". $exp->getFormatedDetail();
            $detail = str_replace('<b>', '', $detail) ;
            $detail = str_replace('</b>', '', $detail) ;
            $billLine = new BillLine();
            $billLine->line = $number;
            $billLine->refType = 'ProviderBill';
            $billLine->refId = $this->id;
            $billLine->description = $exp->name;
            $billLine->detail = $detail;
            $billLine->price = $exp->amount;
            $billLine->quantity = 1;
            $billLine->save();
            $number++;
          }
        }
      }
    }
    $paramImputOfBillLineProvider = Parameter::getGlobalParameter('ImputOfBillLineProvider');
    $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
    $billLine=new BillLine();
    $crit = array("refType"=> "ProviderBill", "refId"=>$this->id);
    $billLineList = $billLine->getSqlElementsFromCriteria($crit,false);
    $paramImput=$paramImputOfAmountProvider;
    if (count($billLineList)>0) {
      $paramImput=$paramImputOfBillLineProvider;
      $amount=0;
      foreach ($billLineList as $line) {
        $amount+=$line->amount;
      }
      if($paramImputOfBillLineProvider == 'HT'){
        $this->untaxedAmount=$amount;
      }else{
        $this->fullAmount=$amount;
      }
    } else if (!$old->id) {
      $providerTerm=new ProviderTerm();
      $crit = array("idProviderBill"=> $this->id);
      $providerTermList = $providerTerm->getSqlElementsFromCriteria($crit,false);
      if (count($providerTermList)>0) {
        $amountHT = 0;
        $amountTTC=0;
        foreach ($providerTermList as $line) {
          $amountHT+=$line->untaxedAmount;
          $amountTTC+=$line->fullAmount;
        }
        $this->untaxedAmount=$amountHT;
        $this->fullAmount=$amountTTC;
      }
    }
    if($paramImput == 'HT'){
      if ($this->discountFrom=='rate' or floatval($this->untaxedAmount)==0) {
        $this->discountAmount=round($this->untaxedAmount*$this->discountRate/100,2);
      } else {
        $this->discountRate=round(100*$this->discountAmount/$this->untaxedAmount,2);
      }
      $this->taxAmount=round($this->untaxedAmount*$this->taxPct/100,2);
      $this->fullAmount=$this->taxAmount + $this->untaxedAmount;
      $this->totalUntaxedAmount=$this->untaxedAmount-$this->discountAmount;
      $this->totalTaxAmount=round($this->totalUntaxedAmount*$this->taxPct/100,2);
      $this->totalFullAmount=$this->totalUntaxedAmount+$this->totalTaxAmount;
      $this->discountFullAmount=$this->fullAmount-$this->totalFullAmount;    
    }else{
      if ($this->discountFrom=='rate' or floatval($this->fullAmount)==0) {
        $this->discountFullAmount=round($this->fullAmount*$this->discountRate/100,2);
      } else {
        $this->discountRate=round($this->discountFullAmount/$this->fullAmount,2);
      }
      $this->untaxedAmount=round($this->fullAmount / (1+($this->taxPct/100)),2);
      $this->taxAmount=$this->fullAmount-$this->untaxedAmount;
      $this->totalFullAmount=$this->fullAmount - $this->discountFullAmount;
      $this->totalUntaxedAmount= round($this->totalFullAmount / (1 + ( $this->taxPct / 100 ) ),2 );
      $this->totalTaxAmount=$this->totalFullAmount-$this->totalUntaxedAmount;
      $this->discountAmount=$this->untaxedAmount-$this->totalUntaxedAmount;
    }
    
    if ($this->paymentAmount==$this->totalFullAmount and $this->totalFullAmount>0) {
      $this->paymentDone=1;
    }
    
    parent::simpleSave();
    
    if($old->idProjectExpense != null and $old->idProjectExpense!=$this->idProjectExpense){
      $projExpense = new ProjectExpense($old->idProjectExpense);
      if ($projExpense->id) $projExpense->save();
    }
    // Update expense linked to bill
    if($this->idProjectExpense){ 
      $projExpense = new ProjectExpense($this->idProjectExpense);
      if (!$projExpense->expenseRealDate) {
        if($this->date){
          $projExpense->expensePlannedDate = $this->date;
        }else{
          $projExpense->expenseRealDate = date('Y-m-d');
        }
      }
      //gautier #4477
      if($this->paymentDone){
        $pbill = new ProviderBill();
        $listProviderBill = $pbill->getSqlElementsFromCriteria(array('idProjectExpense'=>$this->idProjectExpense));
        $isPaymentDone = 1;
        foreach ($listProviderBill as $billProv){
          if(!$billProv->paymentDone){
            $isPaymentDone = 0;
          }
        }
        $projExpense->paymentDone = $isPaymentDone;
      }else{
        if($projExpense->paymentDone)$projExpense->paymentDone=0;
      }
      $projExpense->save();
      if(!$old->idProjectExpense){
        $expenseLink = Parameter::getGlobalParameter('ExpenseLink');
        if($expenseLink){
          $link = new Link();
          $listLink = $link->getSqlElementsFromCriteria(array('ref1Type'=>get_class($this),'ref1Id'=>$this->id));
          foreach ($listLink as $lnk){
            $class = $lnk->ref2Type;
            $newObj = new $class($lnk->ref2Id);
            if(property_exists($newObj, 'idProjectExpense')){
              if(!$newObj->idProjectExpense){
                $newObj->idProjectExpense = $this->idProjectExpense;
                $newObj->save();
              }
            }
          }
        }
      }
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
    return $result;
  }
  
   public function control(){
    $result="";
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function delete() {
    $result=parent::delete();
    if (getLastOperationStatus($result)=='OK') {
      if($this->idProjectExpense){
        $projExpense = new ProjectExpense($this->idProjectExpense);
        $projExpense->save();
      }
    }
    return $result;
  }
  
  public function copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments = false, $withAffectations = false, $toProject = NULL, $toActivity = NULL, $copyToWithResult = false,$copyToWithVersionProjects=false) {
    $result=parent::copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks); 
    return $result;
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
    if ($colName=="untaxedAmount" or $colName=="taxPct" or $colName=="discountAmount" or $colName=="discountFullAmount" or $colName=="fullAmount") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= ' if (avoidRecursiveRefresh) { return;}';
      $colScript .= ' avoidRecursiveRefresh=true;';
      $colScript .= ' setTimeout(\'avoidRecursiveRefresh=false;\',500);';
      if ($colName=="discountAmount" or $colName=="discountFullAmount") {      
        $colScript .= '   dijit.byId("discountFrom").set("value","amount");';
      }
      $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
      if (count($this->_BillLine)) {
        $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfBillLineProvider');
      }
      $colScript .= '     updateFinancialTotal("'.$paramImputOfAmountProvider.'","'.$colName.'");';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }else if ($colName=="discountRate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (avoidRecursiveRefresh) return;';
      $colScript .= '  avoidRecursiveRefresh=true;';
      $colScript .= '  setTimeout(\'avoidRecursiveRefresh=false;\',500);';
      $colScript .= '  var rate=dijit.byId("discountRate").get("value");';
      $colScript .= '  var untaxedAmount=dijit.byId("untaxedAmount").get("value");';
      $colScript .= '  var fullAmount=dijit.byId("fullAmount").get("value");';
      $colScript .= '  if (!isNaN(rate)) {';
      $colScript .= '    dijit.byId("discountFrom").set("value","rate");';
      $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
      if (count($this->_BillLine)) {
        $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfBillLineProvider');
      }
      if($paramImputOfAmountProvider == 'HT'){
        $colScript .= '    var discount=Math.round(untaxedAmount*rate)/100;';
        $colScript .= '    dijit.byId("discountAmount").set("value",discount);';
        $colScript .= '    var discountFull=Math.round(fullAmount*rate)/100;';
        $colScript .= '    dijit.byId("discountFullAmount").set("value",discountFull);';
      }else{
        $colScript .= '    var discountFull=Math.round(fullAmount*rate)/100;';
        $colScript .= '    dijit.byId("discountFullAmount").set("value",discountFull);';
        $colScript .= '    var discount=Math.round(untaxedAmount*rate)/100;';
        $colScript .= '    dijit.byId("discountAmount").set("value",discount);';
      }
      $colScript .= '     updateFinancialTotal("'.$paramImputOfAmountProvider.'","'.$colName.'");';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idProject") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  refreshList("idProjectExpense", "idProject", this.value, null, null, false);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }else if ($colName=="idProjectExpense") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= ' var idExpense=dijit.byId("idProjectExpense").get("value");';
      $colScript .= 'if(idExpense != " "){ ';
      $colScript .= '  dojo.query("._button_generateProjectExpenseClass").style("display", "none"); }else{ dojo.query("._button_generateProjectExpenseClass").style("display", "block"); }';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
  
  public function drawSpecificItem($item){
    global $print,$displayWidth;
    $labelWidth=175; // To be changed if changes in css file (label and .label)
    $largeWidth=( (intval($displayWidth)+30) / 2) - $labelWidth;
    $result="";
    if ($item=='paymentsList') {
      if (!$this->id) return '';
      $pay=new ProviderPayment();
      $payList=$pay->getSqlElementsFromCriteria(array('idProviderBill'=>$this->id));
      //$result.='</td><td>';
      $result.='<div style="position:relative;top:0px;left:80px;width:350px; ">';
      $result.='<table style="width:100%">';
      foreach ($payList as $pay) {
        $result.='<tr class="noteHeader pointer" onClick="gotoElement(\'ProviderPayment\','.htmlEncode($pay->id).');">';
        $result.='<td style="padding:0px 5px; width:20px;">';
        $result.= formatSmallButton('ProviderPayment');
        $result.='</td>';
        $result.='<td style="width:30px">#'.htmlEncode($pay->id).'</td><td>&nbsp;&nbsp;&nbsp;</td>';
        $result.='<td style="padding:0px 5px;text-align:left;width:250px">'.htmlEncode($pay->name).'</td>';
        $result.='<td style="padding:0px 5px;text-align:right;width:50px">'.htmlDisplayCurrency($pay->paymentAmount,false).'</td>';
        $result.='</tr>';
      }
      $result.='</table>';
      $result.='</div>';
    } else if ($item=='generateProjectExpense') {
        echo '<div id="' . $item . 'Button" name="' . $item . 'Button" ';
        echo ' title="' . i18n('generateProjectExpense') . '" class="greyCheck generalColClass _button_generateProjectExpenseClass" ';
        echo ' dojoType="dijit.form.CheckBox"  type="checkbox" >';
        echo '</div> ';
        echo ' ('.i18n("generateProjectExpenseFrom").')';
    }else if($item=='situation'){
      $situation = new Situation();
      $situation->drawSituationHistory($this);
    } 
    return $result;
  }
  
  public function setAttributes() {
    if (count($this->_BillLine)) {
      self::$_fieldsAttributes['untaxedAmount']='readonly';
      self::$_fieldsAttributes['fullAmount']='readonly';
    }
    //Gautier #4445
    if (count($this->_ProviderTerm)) {
      self::$_fieldsAttributes['taxPct']='readonly';
      self::$_fieldsAttributes['untaxedAmount']='readonly';
    }
    if ($this->paymentDone) {
      self::$_fieldsAttributes['paymentDate']='readonly';
      self::$_fieldsAttributes['paymentAmount']='readonly';
      self::$_fieldsAttributes['fullAmount']='readonly';
      self::$_fieldsAttributes['untaxedAmount']='readonly';
      self::$_fieldsAttributes['fullAmount']='readonly';
      self::$_fieldsAttributes['taxPct']='readonly';
      self::$_fieldsAttributes['discountAmount']='readonly';
      self::$_fieldsAttributes['discountRate']='readonly';
      self::$_fieldsAttributes['discountFullAmount']='readonly';
    }
    if ($this->paymentsCount>0) {
      self::$_fieldsAttributes['paymentDate']='readonly';
      self::$_fieldsAttributes['paymentAmount']='readonly';
      self::$_fieldsAttributes['paymentDone']='readonly';
    }
    $habil=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>getSessionUser()->getProfile($this->idProject), 'scope'=>'generateProjExpense'));
    if($this->idProjectExpense or $habil->rightAccess == '2'){
      self::$_fieldsAttributes['_button_generateProjectExpense']='hidden';
    }
    
      if (count($this->_BillLine)) {
      $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfBillLineProvider');
    }else{
      $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
    }
    if($paramImputOfAmountProvider == 'HT'){
      self::$_fieldsAttributes['fullAmount']="readonly";
      self::$_fieldsAttributes['discountFullAmount']="readonly";
    }else{
      self::$_fieldsAttributes['untaxedAmount']="readonly";
      self::$_fieldsAttributes['discountAmount']="readonly";
    }
    
  }
  
  public function retreivePayments($save=true,$isDeletePayment=false) {
    $pay=new ProviderPayment();
    if ($this->id) {
      $payList=$pay->getSqlElementsFromCriteria(array('idProviderBill'=>$this->id));
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
      $this->save();
    }
  }

}
?>