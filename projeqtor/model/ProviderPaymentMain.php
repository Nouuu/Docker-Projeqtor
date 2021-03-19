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
 * PAYMENT
 */ 
require_once('_securityCheck.php');
class ProviderPaymentMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idProviderPaymentType;
  public $idProject;
  public $description;
  public $idUser;
  public $creationDate;
  public $_sec_treatment;
  public $idPaymentMode;
  public $paymentDate;
  public $paymentAmount;
  public $paymentFeeAmount;
  public $paymentCreditAmount;
  public $idProviderTerm;
  public $idProviderBill;
  public $referenceProviderBill;
  public $idProvider;
  public $providerBillAmount;
  public $idle;
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
 
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="nameProviderPaymentType" width="15%" >${idProviderPaymentType}</th>
    <th field="nameProvider" width="10%" >${idProvider}</th>
    <th field="referenceProviderBill" width="10%" >${referenceProviderBill}</th>
    <th field="name" width="25%">${name}</th>
    <th field="namePaymentMode" width="15%" >${idPaymentMode}</th>
    <th field="paymentDate" formatter="dateFormatter" width="10%" >${paymentDate}</th>
    <th field="paymentAmount" formatter="costFormatter" width="10%" >${paymentAmount}</th>  
    ';

  private static $_fieldsAttributes=array("name"=>"required",
      "idProviderPaymentType"=>"required",
      "paymentDate"=>"required",
      "idPaymentMode"=>"required",
      "paymentDate"=>"required",
      "paymentAmount"=>"required",
      "paymentCreditAmount"=>"readonly",
      "idProvider"=>"readonly",
      "referenceProviderBill"=>"readonly",
      "providerBillAmount"=>"readonly"
  );
  
  private static $_colCaptionTransposition = array('idUser'=>'issuer',
      'paymentFeeAmount'=>'paymentFee',
      'paymentCreditAmount'=>'paymentCredit');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    if (!$this->id) {
      $this->paymentDate=date('Y-m-d');
    }
    parent::__construct($id,$withoutDependentObjects);
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
   * Return the specific databaseColumnName
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
    $old=$this->getOld();

    if ($this->idProviderBill and !$this->idProject) {
      $pb=new ProviderBill($this->idProviderBill);
      $this->idProject=$pb->idProject;
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
  // gautier #4345
  public function delete() {
    $result=parent::delete();
    if (getLastOperationStatus($result)=='OK') {
      if($this->idProviderBill){
        $bill=new ProviderBill($this->idProviderBill);
        $bill->retreivePayments(true,true);
      }
    }
    return $result;
  }
  
  public function save() {
    $old=$this->getOld();
    $this->paymentCreditAmount=$this->paymentAmount-$this->paymentFeeAmount;
    if ($this->idProviderTerm and ! $this->idProviderBill) {
      $term=new ProviderTerm($this->idProviderTerm);
      if ($term->idProviderBill) {
        $this->idProviderBill=$term->idProviderBill;
      }
    }
    if ($this->idProviderBill) {
      $bill=new ProviderBill($this->idProviderBill);
      $this->idProvider=$bill->idProvider;
      $this->referenceProviderBill=$bill->reference;
      $this->providerBillAmount=$bill->totalFullAmount;
    }
    $result=parent::save();
    if (isset($bill) and $bill->id) {
      $bill->retreivePayments();
      if ($old->idProviderBill and $old->idProviderBill!=$this->idProviderBill) {
        $oldBill=new ProviderBill($old->idProviderBill);
        $oldBill->paymentDone=0;
        $oldBill->paymentAmount-=$old->paymentAmount;
        if ($oldBill->paymentAmount==0) $oldBill->paymentDate=null;
        $oldBill->retreivePayments();
      }
    } else if ($old->idProviderBill) {
      $oldBill=new ProviderBill($old->idProviderBill);
      $oldBill->paymentDone=0;
      $oldBill->paymentAmount-=$old->paymentAmount;
      if ($oldBill->paymentAmount==0) $oldBill->paymentDate=null;
      $oldBill->retreivePayments();
    }
    // ProviderTerm isPaid
    if($old->idProviderTerm and $old->idProviderTerm != $this->idProviderTerm){
      $provTerm = new ProviderTerm($old->idProviderTerm);
      $provTerm->updatePaidFlag();
    }
    if($this->idProviderTerm){
      $provTerm = new ProviderTerm($this->idProviderTerm);
      $provTerm->updatePaidFlag();
    }
    return $result;
  }
  
  public function getValidationScript($colName) {
  
    $colScript = parent::getValidationScript($colName);
    if ($colName=="paymentAmount" || $colName=="paymentFeeAmount") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var feeAmount=dijit.byId("paymentFeeAmount").get("value");';
      $colScript .= '  if (!feeAmount) feeAmount=0;';
      $colScript .= '  var amount=dijit.byId("paymentAmount").get("value");';
      $colScript .= '  if (!amount) amount=0;';
      $colScript .= '  dijit.byId("paymentCreditAmount").set("value",amount-feeAmount);';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if($colName=="idProviderBill"){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  var bill=dijit.byId("idProviderBill").get("value");';
      $colScript .= ' if(bill){ ';
      $colScript .= '  var amount=dijit.byId("paymentAmount").get("value");';
      $colScript .= '  var term=dijit.byId("idProviderTerm").get("value");';
      $colScript .= '  if(!amount && !term){ ';
      $colScript .= '    providerPaymentIdProviderBill();';
      $colScript .= '  } }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if($colName=="idProviderTerm"){
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= ' var term=dijit.byId("idProviderTerm").get("value");';
      $colScript .= ' if(term){ ';
      $colScript .= '  var amount=dijit.byId("paymentAmount").get("value");';
      $colScript .= '  if(!amount){ ';
      $colScript .= '    providerPaymentIdProviderTerm();';
      $colScript .= '  } }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } 
    return $colScript;
  }
}
?>