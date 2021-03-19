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
class Expense extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $idProject;
  public $idBudgetItem;
  public $idResource;
  public $idUser;
  //public $idExpenseType;
  public $name;
  public $description;
  public $_sec_treatment;
  public $idStatus;  
  public $expensePlannedDate;
  public $plannedAmount;
  public $plannedTaxAmount;
  public $plannedFullAmount;
  public $expenseRealDate;
  public $realAmount;
  public $realTaxAmount;
  public $realFullAmount;
  public $day;
  public $week;
  public $month;
  public $year;
  public $idle;
  //public $_sec_Detail;
  public $_ExpenseDetail=array();
  public $_Attachment=array();
  public $_Note=array();


  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameExpenseType" width="15%" >${type}</th>
    <th field="name" width="20%" >${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("idProject"=>"required",
                                  "name"=>"required",
                                  "idExpenseType"=>"required",
                                  "idStatus"=>"required",
  								                "idUser"=>"hidden",
                                  "day"=>"hidden",
                                  "week"=>"hidden",
                                  "month"=>"hidden",
                                  "year"=>"hidden"
  );  
  
  private static $_colCaptionTransposition = array('expensePlannedDate'=>'plannedDate',
  'expenseRealDate'=>'realDate'
  );
    
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

  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    }
    return $colScript;
  }

  public function control() {
  	$result="";
  	//if (! $this->plannedAmount and ! $this->realAmount) {
  	//	$result.= '<br/>' . i18n('msgEnterRPAmount');
  	//}
    //if (! $this->expensePlannedDate and ! $this->expenseRealDate) {
    //  $result.= '<br/>' . i18n('msgEnterRPDate');
    //}
  	$paramInputExpense = Parameter::getGlobalParameter('ImputOfAmountProvider');
  	if ($paramInputExpense=='TTC') {
  	  if ($this->realAmount==0) $this->realAmount=null;
  	  if ( ($this->plannedFullAmount and ! $this->expensePlannedDate )
  	      or (! $this->plannedFullAmount and $this->plannedFullAmount!=='0'  and $this->expensePlannedDate ) ){
  	    $result.= '<br/>' . i18n('msgEnterPlannedDA');
  	  }
  	  if ( ($this->realFullAmount and ! $this->expenseRealDate )
  	      or ( ! $this->realFullAmount and $this->realFullAmount!==0 and $this->expenseRealDate ) ){
  	    $result.= '<br/>' . i18n('msgEnterRealDA');
  	  }  	  
  	} else {
      if ( ($this->plannedAmount and ! $this->expensePlannedDate ) 
        or (! $this->plannedAmount and $this->plannedAmount!=='0'  and $this->expensePlannedDate ) ){
        $result.= '<br/>' . i18n('msgEnterPlannedDA');	
      }
      if ($this->realAmount==0) $this->realAmount=null;
      if ( ($this->realAmount and ! $this->expenseRealDate ) 
        or ( ! $this->realAmount and $this->realAmount!==0 and $this->expenseRealDate ) ){
        $result.= '<br/>' . i18n('msgEnterRealDA');  
      }
  	}
  	$defaultControl = parent::control ();
  	if ($defaultControl != 'OK') {
  	  $result .= $defaultControl;
  	}
    if ($result=="") {
    	return 'OK';
    } else {
    	return $result;
    }
  }
  
  public function save() {
  	$old=$this->getOld();
    //$this->idUser=$this->idResource; 
    $this->name = mb_substr($this->name,0,100);
    if ($this->expenseRealDate) {
    	$this->setDates($this->expenseRealDate);
    } else {
    	$this->setDates($this->expensePlannedDate);
    }
    $result=parent::save();
    
    $pe=SqlElement::getSingleSqlElementFromCriteria('ProjectPlanningElement', array('refType'=>'Project','refId'=>$this->idProject));
    if (!$pe->id) debugTraceLog("Expense.php save() - cannot retreive ProjectPlanningElement with refType='Project' and refId=$this->idProject"); 
    else $pe->updateExpense();
    if ($old->idProject and $old->idProject!=$this->idProject) {
      $peOld=SqlElement::getSingleSqlElementFromCriteria('ProjectPlanningElement', array('refType'=>'Project','refId'=>$old->idProject));
      if (!$peOld->id) debugTraceLog("Expense.php save() - cannot retreive old ProjectPlanningElement with refType='Project' and refId=$old->idProject");
      else $peOld->updateExpense();
    }
    
    if ($this->idBudgetItem) {
      $item=new Budget($this->idBudgetItem);
      Budget::$_consolidate=true;
      $resB=$item->save();
      Budget::$_consolidate=false;
    }
    if ($this->idBudgetItem!=$old->idBudgetItem and $old->idBudgetItem) {
      $item=new Budget($old->idBudgetItem);
      $item->save();
    }
    return $result;
  }
  
  public function delete() {
    $result=parent::delete();
    $pe=SqlElement::getSingleSqlElementFromCriteria('ProjectPlanningElement', array('refType'=>'Project','refId'=>$this->idProject));
    $pe->updateExpense();
    if ($this->idBudgetItem) {
      $item=new Budget($this->idBudgetItem);
      $item->save();
    }
    return $result;
  }

  public function getExpenseDetail() {
  	$result=array();
    $ed=new ExpenseDetail();
    $crit=array('idExpense'=>$this->id);
    $edList=$ed->getSqlElementsFromCriteria($crit, false, null, 'expenseDate');
    return $edList;
  }
  
  public function updateAmount() {
  	if (count($this->_ExpenseDetail)==0) {
  		return;
  	}
  	$total=0;
  	$date=date('Y-m-d');
  	foreach ($this->_ExpenseDetail as $ed) {
  		$total+=$ed->amount;
  		if ($ed->expenseDate) $date=$ed->expenseDate;
  	} 
  	$paramImputOfBillLineProvider = Parameter::getGlobalParameter('ImputOfBillLineProvider');
  	if($paramImputOfBillLineProvider == "HT" or SqlElement::is_a($this,'IndividualExpense') ){
  	  $this->realAmount=$total;
  	}else{
  	  $this->realFullAmount=$total;
  	}
  	if (! $this->expenseRealDate) {
  	  $this->expenseRealDate=$date;
  	}
  	$this->save();
  }
  
  public function setDates($workDate) {
    $year=substr($workDate,0,4);
    $month=substr($workDate,5,2);
    $day=substr($workDate,8,2);
    $this->day=$year . $month . $day;
    $this->month=$year . $month; 
    $this->year=$year;
    if (weekNumber($workDate)=='01' and $month=='12') {$year+=1;}
    else if (weekNumber($workDate)>50 and $month=='01') {$year-=1;};
    $this->week=$year.weekNumber($workDate);
  }
  
}
?>