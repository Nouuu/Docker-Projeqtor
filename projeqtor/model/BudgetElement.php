<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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
 * Budget Element is an object included in all objects that can be budgeted.
 */ 
require_once('_securityCheck.php');
class BudgetElement extends SqlElement {

  public $id;
  public $refType;
  public $refId;
  public $year;
  public $refName;
  public $budgetWork;
  public $validatedWork;
  public $assignedWork;
  public $realWork;
  public $leftWork;
  public $plannedWork;
  public $budgetCost;
  public $validatedCost;
  public $assignedCost;
  public $realCost;
  public $leftCost;
  public $plannedCost;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $elementary;
  public $expenseBudgetAmount;
  public $expenseAssignedAmount;
  public $expensePlannedAmount;
  public $expenseRealAmount;
  public $expenseLeftAmount;
  public $expenseValidatedAmount;
  public $totalBudgetCost;
  public $totalAssignedCost;
  public $totalPlannedCost;
  public $totalRealCost;
  public $totalLeftCost;
  public $totalValidatedCost;
  public $reserveAmount;
  public $idle;
// ADD BY Marc TABARY - 2017-03-09 - PERIODIC YEAR BUDGET ELEMENT  
  public $idleDateTime;
// END ADD BY Marc TABARY - 2017-03-09 - PERIODIC YEAR BUDGET ELEMENT  
  
// ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY
  public $_workVisibility;
  public $_costVisibility;
// END ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY
  
  private static $_fieldsAttributes=array(
                                  "id"=>"hidden",
                                  "refType"=>"hidden",
                                  "refId"=>"hidden",
                                  "refName"=>"hidden",
                                  "progress"=>"display,noImport",
                                  "topId"=>"hidden",
                                  "topRefType"=>"hidden",
                                  "topRefId"=>"hidden",
                                  "idle"=>"hidden",
// ADD BY Marc TABARY - 2017-03-09 - PERIODIC YEAR BUDGET ELEMENT  
                                  "idleDateTime"=>"hidden",
// END ADD BY Marc TABARY - 2017-03-09 - PERIODIC YEAR BUDGET ELEMENT  
                                  "validatedWork"=>"readonly,noImport",
                                  "assignedWork"=>"readonly,noImport",
                                  "realWork"=>"readonly,noImport",
                                  "leftWork"=>"readonly,noImport",
                                  "plannedWork"=>"readonly,noImport",
                                  "validatedCost"=>"readonly,noImport",
                                  "assignedCost"=>"readonly,noImport",
                                  "realCost"=>"readonly,noImport",
                                  "leftCost"=>"readonly,noImport",
                                  "plannedCost"=>"readonly,noImport",
                                  "elementary"=>"hidden"                            
  );   
  
  private static $_databaseTableName = 'budgetelement';
  public static $_noDispatch=false;
  public static $_noDispatchArrayBudget=array();

// ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY 
  private static $staticCostVisibility=null;
  private static $staticWorkVisibility=null;
// END ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY 

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

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }

// ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY 
  public function setVisibility($profile=null) {
    if (! sessionUserExists()) {
      return;
    }
    if (! $profile) {
      $user=getSessionUser();
      $profile=$user->getProfile();
    }
        
    if (self::$staticCostVisibility and isset(self::$staticCostVisibility[$profile]) 
    and self::$staticWorkVisibility and isset(self::$staticWorkVisibility[$profile]) ) {
      $this->_costVisibility=self::$staticCostVisibility[$profile];
      $this->_workVisibility=self::$staticWorkVisibility[$profile];
      return;
    }
    
    $user=getSessionUser();
    $list=SqlList::getList('VisibilityScope', 'accessCode', null, false);
    $hCost=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile,'scope'=>'cost'));
    $hWork=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile'=>$profile,'scope'=>'work'));
    if ($hCost->id) {
      $this->_costVisibility=$list[$hCost->rightAccess];
    } else {
      $this->_costVisibility='ALL';
    }
    if ($hWork->id) {
      $this->_workVisibility=$list[$hWork->rightAccess];
    } else {
      $this->_workVisibility='ALL';
    }
    if (!self::$staticCostVisibility) self::$staticCostVisibility=array();
    if (!self::$staticWorkVisibility) self::$staticWorkVisibility=array();
    self::$staticCostVisibility[$profile]=$this->_costVisibility;
    self::$staticWorkVisibility[$profile]=$this->_workVisibility;
  }
// END ADD BY Marc TABARY - 2017-02-16 - WORK AND COST VISIBILITY 
  
  /** ==========================================================================
   * Extends save functionality to implement wbs calculation
   * Triggers parent::save() to run defaut functionality in the end.
   * @return the result of parent::save() function
   */
  public function save() {  	
  	// Get old element (stored in database) : must be fetched before saving
    $old=new BudgetElement($this->id);
// ADD BY Marc TABARY - 2017-02-09
    
    // update topId if needed
    $topElt=null;
    if ( (! $this->topId or trim($this->topId)=='') and ( $this->topRefId and trim($this->topRefId)!='') ) {
      $crit=array("refType"=>$this->topRefType, "refId"=>$this->topRefId);
      $topElt=SqlElement::getSingleSqlElementFromCriteria('BudgetElement',$crit);
      if ($topElt) {
        $this->topId=$topElt->id;
        $topElt->elementary=0;        
      }
    }

    $crit=" topId=" . Sql::fmtId($this->id);
    $this->elementary=1;
    $lstElt=$this->getSqlElementsFromCriteria(null, null, $crit);
    if ($lstElt and count($lstElt)>0) {
      $this->elementary=0;
    } else {
      $this->elementary=1;
    }
    
// END ADD BY Marc TABARY - 2017-02-09

    $result=parent::save();

// ADD BY Marc TABARY - 2017-02-09
    if (! strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;
    }

    // update topObject
    if ($topElt) {
      if ($topElt->refId) {
        if (! self::$_noDispatch) {
          $topElt->save();   
      	} else {
      	  if ($this->elementary) { // noDispatch (for copy) and elementary : store top in array for updateSynthesis
            self::$_noDispatchArrayBudget[$topElt->id]=$topElt->id; 
      	  }
      	}
      }
    }
  
// END ADD BY Marc TABARY - 2017-02-09
    return $result;
  }

  // Save without extra save() feature and without controls
    public function simpleSave() {
    // Avoid save actions
    $result = parent::saveForced();
    return $result;
  }
  
    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
    /**
   * Delete object 
   * @see persistence/SqlElement#save()
   */
  public function delete() { 
    $result = parent::delete();
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
    //if ($this->idle and $this->leftWork>0) {
    //  $result.='<br/>' . i18n('errorIdleWithLeftWork');
    //}
   
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function deleteControl()
  {
  	$result="";
  	 
  	// Cannot delete item with real work
  	//if ($this->id and $this->realWork and $this->realWork>0)	{
  	//	$result .= "<br/>" . i18n("msgUnableToDeleteRealWork");
  	//}
  	 
  	if (! $result) {
  		$result=parent::deleteControl();
  	}
  	return $result;
  }
  
  public function getFieldAttributes($fieldName) {
    if (isset(self::$_fieldsAttributes[$fieldName])) {
      return self::$_fieldsAttributes[$fieldName];
    }
    return parent::getFieldAttributes($fieldName);
  }  
  public static function dispatchFinalize() {
    foreach (BudgetElement::$_noDispatchArrayBudget as $idOrg=>$notUsed) {
      unset(BudgetElement::$_noDispatchArrayBudget[$idOrg]);
      $org=new Organization($idOrg,false);
      $org->updateSynthesis();
    }
  }

}
?>