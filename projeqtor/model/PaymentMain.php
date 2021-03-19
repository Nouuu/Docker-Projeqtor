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
class PaymentMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idPaymentType;
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
  public $idBill;
  public $referenceBill;
  public $idClient;
  public $idRecipient;
  public $billAmount;
  public $idle;
  
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
 
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="namePaymentType" width="10%" >${idPaymentType}</th>
    <th field="name" width="25%">${name}</th>
    <th field="namePaymentMode" width="10%" >${idPaymentMode}</th>
    <th field="paymentDate" formatter="dateFormatter" width="10%" >${paymentDate}</th>
    <th field="paymentAmount" formatter="costFormatter" width="10%" >${paymentAmount}</th>  
    <th field="referenceBill" width="15%" >${referenceBill}</th>
    <th field="nameClient" width="15%" >${idClient}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required",
      "idPaymentType"=>"required",
      "paymentDate"=>"required",
      "idPaymentMode"=>"required",
      "paymentDate"=>"required",
      "paymentAmount"=>"required",
      "paymentCreditAmount"=>"readonly",
      "idClient"=>"readonly",
      "idRecipient"=>"readonly",
      "referenceBill"=>"readonly",
      "billAmount"=>"readonly"
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
    
    if ($this->idBill and !$this->idProject) {
      $b=new Bill($this->idBill);
      $this->idProject=$b->idProject;
    }
    
    // Chek that bill is not already paid
    if ( trim($this->idBill) and (trim($this->idBill)!=trim($old->idBill))) {
      $bill=new Bill($this->idBill);
      if ($bill->paymentsCount>0 and $bill->paymentDone) {
        $result.="<br/>" . i18n('billAlreadyPaid',array($bill->id, $bill->name, $bill->reference));
      } else {
        $paidBill=$this->paymentAmount;
        if ($bill->paymentsCount>0) $paidBill+=$bill->paymentAmount;
        if ( $paidBill > $bill->fullAmount) {
          $result.="<br/>" . i18n('paymentExceedBill',array($paidBill, $bill->fullAmount));
        }
      }
    } else if ( trim($this->idBill) and $this->paymentAmount > $old->paymentAmount) {
      $bill=new Bill($this->idBill);
      $paidBill=$bill->paymentAmount+$this->paymentAmount-$old->paymentAmount;
      if ( $paidBill > $bill->fullAmount) {
        $result.="<br/>" . i18n('paymentExceedBill',array($paidBill, $bill->fullAmount));
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
  
  // gautier #4345
  public function delete() {
    $result=parent::delete();
    if (getLastOperationStatus($result)=='OK') {
      if($this->idBill){
        $bill=new Bill($this->idBill);
        $bill->retreivePayments(true,true);
      }
    }
    return $result;
  }
  
  public function save() {
    $old=$this->getOld();
    $this->paymentCreditAmount=$this->paymentAmount-$this->paymentFeeAmount;
    if ($this->idBill) {
      $bill=new Bill($this->idBill);
      $this->idRecipient=$bill->idRecipient;
      $this->idClient=$bill->idClient;
      $this->referenceBill=$bill->reference;
      $this->billAmount=$bill->fullAmount;
    }
    $result=parent::save();
    if (isset($bill) and $bill->id) {
      $bill->retreivePayments();
      if ($old->idBill and $old->idBill!=$this->idBill) {
        $oldBill=new Bill($old->idBill);
        $oldBill->paymentDone=0;
        $oldBill->paymentAmount-=$old->paymentAmount;
        if ($oldBill->paymentAmount==0) $oldBill->paymentDate=null;
        $oldBill->retreivePayments();
      }
    } else if ($old->idBill) {
      $oldBill=new Bill($old->idBill);
      $oldBill->paymentDone=0;
      $oldBill->paymentAmount-=$old->paymentAmount;
      if ($oldBill->paymentAmount==0) $oldBill->paymentDate=null;
      $oldBill->retreivePayments();
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
    } 
    return $colScript;
  }
}
?>