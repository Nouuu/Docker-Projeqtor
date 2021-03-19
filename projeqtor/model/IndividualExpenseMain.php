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
class IndividualExpenseMain extends Expense {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place
  public $reference; 
  public $name;
  public $idIndividualExpenseType;
  public $idProject;
  public $idResource;
  public $idUser;
  public $description;
  public $_sec_treatment;
  public $idStatus;  
  public $idResponsible;
  public $_tab_2_2 = array('amount','paymentDateShort', 'plannedAmount', 'realAmount');
  public $plannedAmount;
  public $expensePlannedDate;
  public $realAmount;
  public $expenseRealDate;
  public $idBudgetItem;
  public $paymentDone;
  public $idle;
  public $cancelled;
  public $_lib_cancelled;
  public $_sec_ExpenseDetail;
  public $_ExpenseDetail=array();
  public $_expenseDetail_colSpan="2";
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();

  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameIndividualExpenseType" width="15%" >${type}</th>
    <th field="nameResource" formatter="thumbName22" width="15%" >${idResource}</th>
    <th field="name" width="25%" >${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "scope"=>"hidden",
                                  "idProject"=>"required",
                                  "name"=>"required",
                                  "idIndividualExpenseType"=>"required",
                                  "expensePlannedDate"=>"",
                                  "plannedFullAmount"=>"hidden",
                                  "plannedTaxAmount"=>"hidden",
                                  "realFullAmount"=>"hidden",
                                  "realTaxAmount"=>"hidden",
                                  "idResource"=>"required",
                                  "idStatus"=>"required",
  								                "idUser"=>"hidden",
                                  "day"=>"hidden",
                                  "week"=>"hidden",
                                  "month"=>"hidden",
                                  "year"=>"hidden",
                                  "idle"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "idBudgetItem"=>"canSearchForAll"
  );  
  
  private static $_colCaptionTransposition = array('idIndividualExpenseType'=>'type',
  'expensePlannedDate'=>'plannedDate',
  'expenseRealDate'=>'realDate',
  'idResponsible'=>'responsible'
  );
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array("idIndividualExpenseType"=>"idExpenseType");

  private static $_databaseCriteria = array('scope'=>'IndividualExpense');

  private static $_databaseTableName = 'expense';
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
      
    if (count($this->getExpenseDetail())>0) {
      self::$_fieldsAttributes['realAmount']="readonly";
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
      $colScript .= '  if (this.checked && dijit.byId("paymentDate") && !dijit.byId("paymentDate").get("value")) {';
      $colScript .= '    var curDate = new Date();';
      $colScript .= '    dijit.byId("paymentDate").set("value",curDate);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="paymentDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.value && dijit.byId("paymentDone")) {';
      $colScript .= '    dijit.byId("paymentDone").set("checked",true);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    
    return $colScript;
  }
  public function save() {
    $this->plannedFullAmount=$this->plannedAmount;
    $this->realFullAmount=$this->realAmount;
    //$this->idUser=$this->idResource; // To reactivate if we want that team member can update their Expense created by other user.
    return parent::save();
  }
}
?>