<?php 
use Spipu\Html2Pdf\Tests\ExamplesTest;
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
 * Assignment defines link of resources to an Activity (or else)
 */ 
require_once('_securityCheck.php');
class ExpenseDetail extends SqlElement {

  // extends SqlElement, so has $id
  public $id;
  public $idProject; 
  public $idExpense; 
  public $idExpenseDetailType; 
  public $name;
  public $description;
  public $externalReference;
  public $expenseDate; 
  public $amount; 
  public $value01;
  public $value02;
  public $value03;
  public $unit01;
  public $unit02;
  public $unit03;
  public $idle;
  
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

// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /**
   * Save object 
   * @see persistence/SqlElement#save()
   */
  public function save() {
    $result = parent::save();
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;     
    }
    $exp=new ProjectExpense($this->idExpense,false);
    if (!$exp->id) {
     $exp=new IndividualExpense($this->idExpense,false);
    }
    $exp->updateAmount();
    return $result;
  }
  
  /**
   * Delete object and dispatch updates to top 
   * @see persistence/SqlElement#save()
   */
  public function delete() {
  	$ref=$this->idExpense;
  	$result = parent::delete();
    $exp=new ProjectExpense($ref,false);
    if (! $exp->id) {
      $exp=new IndividualExpense($ref,false);
    }
    $exp->updateAmount();  	
  	return $result;
  }
    
/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control(){
    $result="";
    $result = parent::control();
    return $result;
  }
  
  public function getFormatedDetail() {
  	$result="";
  	if ($this->value01 or $this->unit01) {
  		$result.=($result)?' <b>x</b> ':'';
  		$result.=htmlDisplayNumericWithoutTrailingZeros($this->value01) . " " . $this->unit01;
  	}
    if ($this->value02 or $this->unit02) {
      $result.=($result)?' <b>x</b> ':'';
      $result.=htmlDisplayNumericWithoutTrailingZeros($this->value02) . " " . $this->unit02;
    }
    if ($this->value03 or $this->unit03) {
      $result.=($result)?' <b>x</b> ':'';
      $result.=htmlDisplayNumericWithoutTrailingZeros($this->value03) . " " . $this->unit03;
    }
    return $result;
  }

//   public static function addExpenseDetailFromBillLines($objectClass,$objectId,$expenseId,$projectId=null) {
//     if (!$projectId) {
//       $exp=new Expense($expenseId);
//       $projectId=$exp->idProject;
//     }
//     $billLine = new BillLine();
//     $critArray=array('refType'=>$objectClass,'refId'=>$objectId);
//     $lines=$billLine->getSqlElementsFromCriteria($critArray);
//     foreach ($lines as $line) {
//       $det=new ExpenseDetail();
//       $det->amount=$line->amount;
//       $det->externalReference=mb_substr($line->description, 0,100);
//       $det->idExpense=$expenseId;
//       $det->idProject=$projectId;
//       $det->name=mb_substr(str_replace("\n", " ", $line->detail), 0,100);
//       if (!$det->name) $det->name=i18n('BillLine').' #'.$line->line;
//       $res=$det->save();
//     }
//   }
}
?>