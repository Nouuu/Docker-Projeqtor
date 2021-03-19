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
class ProjectExpenseMain extends Expense {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place
  public $reference; 
  public $name;
  public $idProjectExpenseType;
  public $idProject;
  public $idUser;
  public $idProvider;
  public $idContact;
  public $externalReference;
  public $Origin;
  public $idResource;
  public $idResponsible;
  public $paymentCondition;
  public $description;
  public $_sec_treatment;
  public $idStatus;  
  public $sendDate;
  public $idDeliveryMode;
  public $deliveryDelay;
  public $deliveryDate;
  public $receptionDate;
  public $idle;
  public $cancelled;
  public $_lib_cancelled;
  public $_tab_5_2_smallLabel = array('untaxedAmountShort', 'taxPct', 'tax', 'fullAmountShort','date', 'planned', 'real');
  public $plannedAmount;
  public $taxPct;
  public $plannedTaxAmount;
  public $plannedFullAmount;
  public $expensePlannedDate;
  public $realAmount;
  public $_void_1;
  public $realTaxAmount;
  public $realFullAmount;
  public $expenseRealDate;
  public $idBudgetItem;
  public $paymentDone;
  public $result;
  public $_sec_ExpenseDetail;
  public $_ExpenseDetail=array();
  public $_expenseDetail_colSpan="2";
  public $_sec_totalFinancialSynthesis;
  public $_spe_totalFinancialSynthesis;
  public $_totalFinancialSynthesis_colSpan="2";
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $isCalculated;

  public $_nbColMax=3;  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameProjectExpenseType" width="15%" >${type}</th>
    <th field="name" width="50%" >${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "idProject"=>"required",
                                  "name"=>"required",
                                  "idProjectExpenseType"=>"required",
                                  "expensePlannedDate"=>"",
                                  "idStatus"=>"required",
  								                "idUser"=>"hidden",              
                                  "day"=>"hidden",
                                  "week"=>"hidden",
                                  "month"=>"hidden",
                                  "year"=>"hidden",
                                  "idle"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "plannedTaxAmount"=>"readonly",
                                  "realTaxAmount"=>"readonly",
                                  "isCalculated"=>"hidden",
                                  "idBudgetItem"=>"canSearchForAll"
  );  
  
  private static $_colCaptionTransposition = array('idProjectExpenseType'=>'type',
  'expensePlannedDate'=>'plannedDate',
  'expenseRealDate'=>'realDate',
  'idResource'=>'businessResponsible',
  'idResponsible'=>'financialResponsible',
  'sendDate'=>'orderDate'
  );
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array("idProjectExpenseType"=>"idExpenseType",
  );

  private static $_databaseCriteria = array('scope'=>'ProjectExpense');

  private static $_databaseTableName = 'expense';
  
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
  
  /**=========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */
  public function save() {
    $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
    $paramImputOfBillLineProvider = Parameter::getGlobalParameter('ImputOfBillLineProvider');
    $old=$this->getOld();
    if($this->id){
      $provOrder = new ProviderOrder();
      $listProvOrd=$provOrder->getSqlElementsFromCriteria(array("idProjectExpense"=>$this->id));
      if($this->isCalculated or count($listProvOrd)>0){
        $this->plannedAmount = 0;
        $this->plannedTaxAmount = 0;
        $this->plannedFullAmount =0;
      }
      foreach ($listProvOrd as $prov){
        $this->plannedAmount += $prov->totalUntaxedAmount;
        $this->plannedTaxAmount += $prov->totalTaxAmount;
        $this->plannedFullAmount += $prov->totalFullAmount;
        if (!$this->expensePlannedDate) $this->expensePlannedDate=$prov->creationDate;
        $this->isCalculated = 1;
      }
      if (count($listProvOrd)==0) {
        $this->isCalculated = 0;
        if (! $this->plannedAmount and !$this->plannedFullAmount) {
          $this->expensePlannedDate=null;
        }
      }
      if ($this->isCalculated==1 and !$this->expensePlannedDate) $this->expensePlannedDate=date('Y-m-d');
      $provBill = new ProviderBill();
      $listProvBill=$provBill->getSqlElementsFromCriteria(array("idProjectExpense"=>$this->id));
      if($this->isCalculated or count($listProvBill)>0){
        $this->realAmount = 0;
        $this->realTaxAmount = 0;
        $this->realFullAmount =0;
      }
      foreach ($listProvBill as $prov){
        $this->realAmount += $prov->totalUntaxedAmount;
        $this->realTaxAmount += $prov->totalTaxAmount;
        $this->realFullAmount += $prov->totalFullAmount;
        $this->isCalculated = 1;
        if (!$this->expenseRealDate) $this->expenseRealDate=$prov->creationDate;
      }
      if (count($listProvBill)==0) {
        $this->isCalculated = 0;
        if (! $this->realAmount and !$this->realFullAmount) {
          $this->expenseRealDate=null;
        }
        if (count($this->getExpenseDetail())>0) {
          if($old->isCalculated==1){
            foreach ($this->getExpenseDetail() as $expenseD){
              if($paramImputOfBillLineProvider == "HT"){
                $this->realAmount += $expenseD->amount;
              }else{
                $this->realFullAmount += $expenseD->amount;
              }
            }
          }
        }
     }
   }
    if($this->isCalculated != 1){
        // Update amounts
      if($paramImputOfBillLineProvider == "TTC"){  
        if ($this->realFullAmount!=null) {
          if($this->taxPct > 0) {
            if ($this->taxPct==100) {
              $this->realTaxAmount=$this->realFullAmount;
              $this->realAmount=0;
            } else {
              $this->realAmount =  round($this->realFullAmount / ( 1 + ( $this->taxPct / 100 )),2);
              $this->realTaxAmount= $this->realFullAmount - $this->realAmount;
            }
          }else{
            $this->realAmount =  $this->realFullAmount;
            $this->realTaxAmount= 0;
          }
        }
      }else{
        if ($this->realAmount!=null) {
          if ($this->taxPct!=null) {
            $this->realTaxAmount=round(($this->realAmount*$this->taxPct/100),2);
          } else {
            $this->realTaxAmount=null;
          } 
          $this->realFullAmount=$this->realAmount+$this->realTaxAmount;
        }
      }
      
      if ($this->realAmount!=null) {
        if($paramImputOfAmountProvider == 'HT'){
          if ($this->taxPct!=null) {
            $this->realTaxAmount=round(($this->realAmount*$this->taxPct/100),2);
          } else {
            $this->realTaxAmount=null;
          } 
          $this->realFullAmount=$this->realAmount+$this->realTaxAmount;
        }
      } else if ($this->taxPct!=100) {
          $this->realTaxAmount=null;
          $this->realFullAmount=null;
      }  
    }
    
    if ($this->plannedAmount!=null) {
      if($paramImputOfAmountProvider == 'HT'){
        if ($this->taxPct!=null) {
          $this->plannedTaxAmount=round(($this->plannedAmount*$this->taxPct/100),2);
        } else {
          $this->plannedTaxAmount=null;
        }
        $this->plannedFullAmount=$this->plannedAmount+$this->plannedTaxAmount;
      }
    } else {
      $this->plannedTaxAmount=null;
      $this->plannedFullAmount=null;
    }
    
    if($this->realAmount == 0 and $this->realTaxAmount == 0 and $this->realFullAmount == 0 and $this->id){
      if($this->expenseRealDate){
        $this->expenseRealDate = null;
      }
    }
    
    return parent::save(); 
  }
    
  public function delete() {
    $result=parent::delete();
    if (getLastOperationStatus($result)=='OK') {
      $provOrder = new ProviderOrder();
      $listProvOrd=$provOrder->getSqlElementsFromCriteria(array("idProjectExpense"=>$this->id));
      foreach ($listProvOrd as $prov){
        $prov->idProjectExpense = null;
        $prov->save();
      }
      $provBill = new ProviderBill();
      $listProvBill=$provBill->getSqlElementsFromCriteria(array("idProjectExpense"=>$this->id));
      foreach ($listProvBill as $prov){
        $prov->idProjectExpense = null;
        $prov->save();
      }
      $provTender = new Tender();
      $listProvTender=$provTender->getSqlElementsFromCriteria(array("idProjectExpense"=>$this->id));
      foreach ($listProvTender as $tender){
        $tender->idProjectExpense = null;
        $tender->save();
      }
   }
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
    if ($colName=="expenseRealDate") {
      //$colScript .= '<script type="dojo/connect" event="onChange" >';
      //$colScript .= '  if (this.value) {';
      //$colScript .= '    dijit.byId("paymentDone").set("checked",true);';
      //$colScript .= '  }';
      //$colScript .= '  formChanged();';
      //$colScript .= '</script>';
    } else if ($colName=="paymentDone") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked && !dijit.byId("expenseRealDate").get("value")) {';
      $colScript .= '    var curDate = new Date();';
      $colScript .= '    dijit.byId("expenseRealDate").set("value",curDate);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="realAmount" or $colName=="plannedAmount" or $colName=="taxPct" or $colName=="plannedFullAmount" or $colName=="realFullAmount") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var tax=dijit.byId("taxPct").get("value");';
      $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
      $paramImputOfAmountProviderBillLine = Parameter::getGlobalParameter('ImputOfBillLineProvider');
      if (count($this->getExpenseDetail())>0 and $paramImputOfAmountProvider == 'HT' and $paramImputOfAmountProviderBillLine== 'TTC') {
          $colScript .= '  var plan=dijit.byId("plannedAmount").get("value");';
          $colScript .= '  var planTax=null;';
          $colScript .= '  var planFull=null;';
          $colScript .= '  if (!isNaN(plan)) {';
          $colScript .= '    if (!isNaN(tax)) {';
          $colScript .= '      planTax=Math.round(plan*tax)/100;';
          $colScript .= '      planFull=plan+planTax;';
          $colScript .= '    } else {';
          $colScript .= '      planFull=plan;';
          $colScript .= '    }';
          $colScript .= '  }';
          $colScript .= '  var initFull=dijit.byId("realFullAmount").get("value");';
          $colScript .= '  var initTax=null;';
          $colScript .= '  var init=null;';
          $colScript .= '  if (!isNaN(initFull)) {';
          $colScript .= '    if (!isNaN(tax)) {';
          $colScript .= '      init = initFull / (1 +( tax / 100 ) );';
          $colScript .= '      initTax=initFull-init;';
          $colScript .= '    } else {';
          $colScript .= '      init=initFull;';
          $colScript .= '    }';
          $colScript .= '  }';
          if($this->isCalculated == 0){
            $colScript .= '  dijit.byId("realTaxAmount").set("value",initTax);';
            $colScript .= '  dijit.byId("realAmount").set("value",init);';
          }
          $colScript .= '  dijit.byId("plannedTaxAmount").set("value",planTax);';
          $colScript .= '  dijit.byId("plannedFullAmount").set("value",planFull);';
      }elseif($paramImputOfAmountProvider == 'HT' and $paramImputOfAmountProviderBillLine == 'HT' or $paramImputOfAmountProvider == 'HT' and count($this->getExpenseDetail())==0 ){
        $colScript .= '  var init=dijit.byId("realAmount").get("value");';
        $colScript .= '  var plan=dijit.byId("plannedAmount").get("value");';
        $colScript .= '  var initTax=null;';
        $colScript .= '  var planTax=null;';
        $colScript .= '  var initFull=null;';
        $colScript .= '  var planFull=null;';
        $colScript .= '  if (!isNaN(init)) {';
        $colScript .= '    if (!isNaN(tax)) {';
        $colScript .= '      initTax=Math.round(init*tax)/100;';
        $colScript .= '      initFull=init+initTax;';
        $colScript .= '    } else {';
        $colScript .= '      initFull=init;';
        $colScript .= '    }';
        $colScript .= '  }';
        $colScript .= '  if (!isNaN(plan)) {';
        $colScript .= '    if (!isNaN(tax)) {';
        $colScript .= '      planTax=Math.round(plan*tax)/100;';
        $colScript .= '      planFull=plan+planTax;';
        $colScript .= '    } else {';
        $colScript .= '      planFull=plan;';
        $colScript .= '    }';
        $colScript .= '  }';
        if($this->isCalculated == 0){
          $colScript .= '  dijit.byId("realTaxAmount").set("value",initTax);';
          $colScript .= '  dijit.byId("realFullAmount").set("value",initFull);';
        }
        $colScript .= '  dijit.byId("plannedTaxAmount").set("value",planTax);';
        $colScript .= '  dijit.byId("plannedFullAmount").set("value",planFull);';
      }else{
        $colScript .= '  var initFull=dijit.byId("realFullAmount").get("value");';
        $colScript .= '  var planFull=dijit.byId("plannedFullAmount").get("value");';
        $colScript .= '  var initTax=null;';
        $colScript .= '  var planTax=null;';
        $colScript .= '  var init=null;';
        $colScript .= '  var plan=null;';
        $colScript .= '  if (!isNaN(planFull)) {';
        $colScript .= '    if (!isNaN(tax)) {';
        $colScript .= '      plan = planFull / (1 + (tax /100 ) );';
        $colScript .= '      planTax= planFull-plan;';
        $colScript .= '    } else {';
        $colScript .= '      plan=planFull;';
        $colScript .= '    }';
        $colScript .= '  }';
        $colScript .= '  if (!isNaN(initFull)) {';
        $colScript .= '    if (!isNaN(tax)) {';
        $colScript .= '      init = initFull / (1 +( tax / 100 ) );';
        $colScript .= '      initTax=initFull-init;';
        $colScript .= '    } else {';
        $colScript .= '      init=initFull;';
        $colScript .= '    }';
        $colScript .= '  }';
        if($this->isCalculated == 0){
          $colScript .= '  dijit.byId("realTaxAmount").set("value",initTax);';
          $colScript .= '  dijit.byId("realAmount").set("value",init);';
        }
        $colScript .= '  dijit.byId("plannedTaxAmount").set("value",planTax);';
        $colScript .= '  dijit.byId("plannedAmount").set("value",plan);';
      }
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="idProvider") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  refreshList("idContact", "idProvider", this.value, dijit.byId("idContact").get("value"),null, false);';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
  
  public function drawSpecificItem($item){
    global $comboDetail, $print, $outMode, $largeWidth;
    $showExpenseProjectDetail=(Parameter::getUserParameter('showExpenseProjectDetail')!='0')?true:false;
    $result="";
    if ($item=='totalFinancialSynthesis') {
      if($this->id){
        drawTabExpense($this, false);
      }
      if (!$print) {
      	$result='<div style="position:absolute;right:5px;top:3px;">';
      	$result.='<label for="showExpenseProjectDetail"  class="dijitTitlePaneTitle" s style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:250px">'.i18n('colShowDetail').'&nbsp;</label>';
      	$result.='<div class="whiteCheck" id="showExpenseProjectDetail" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.(($showExpenseProjectDetail)?'checked':'').' >';
      	$result.='<script type="dojo/connect" event="onChange" args="evt">';
      	$result.=' saveUserParameter("showExpenseProjectDetail",((this.checked)?"1":"0"));';
      	$result.=' if (checkFormChangeInProgress()) {return false;}';
      	$result.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
      	$result.='</script>';
      	$result.='</div>';
      }
      return $result;
    }
  }
  
  public function setAttributes() {
    $paramImputOfAmountProvider = Parameter::getGlobalParameter('ImputOfAmountProvider');
    if($paramImputOfAmountProvider == 'HT'){
      self::$_fieldsAttributes['plannedFullAmount']="readonly";
      self::$_fieldsAttributes['realFullAmount']="readonly";
    }else{
      self::$_fieldsAttributes['realAmount']="readonly";
      self::$_fieldsAttributes['plannedAmount']="readonly";
    }
    if($this->isCalculated == 1){
      self::$_fieldsAttributes["realAmount"]='readonly';
      self::$_fieldsAttributes["plannedAmount"]='readonly';
    }
    if (count($this->getExpenseDetail())>0) {
      self::$_fieldsAttributes['realAmount']="readonly";
      self::$_fieldsAttributes['realFullAmount']="readonly";
    }
    
  }
}
?>