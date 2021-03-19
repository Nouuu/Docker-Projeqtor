<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Matthias Nowak : fix to avoid infinite loop in getRecursivePredecessor()
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
 * Planning element is an object included in all objects that can be planned.
 */ 
require_once('_securityCheck.php');
class PlanningElementBaseline extends PlanningElement {
 
  public $idBaseline;
  public $isGlobal;
  public $_noHistory;
  
  private static $_fieldsAttributes=array();
  
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
// GET VALIDATION SCRIPT
// ============================================================================**********
 
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    return $colScript;
  }
  
  /** ==========================================================================
   * Extends save functionality to implement wbs calculation
   * Triggers parent::save() to run defaut functionality in the end.
   * @return the result of parent::save() function
   */
  public function save() {  	
    $result=parent::simpleSave();
    return $result;
  }

  // Save without extra save() feature and without controls
  public function simpleSave() {
    $result = parent::saveForced();
    return $result();
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
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function deleteControl() {
  	$result="";
  	if (! $result) {
  		$result=parent::deleteControl();
  	}
  	return $result;
  }
  
  public static function getWorkVisibility($profile) {
    if (! self::$staticWorkVisibility or ! isset(self::$staticWorkVisibility[$profile]) ) {
      $pe=new PlanningElementBaseline();
      $pe->setVisibility($profile);
    }
    return self::$staticWorkVisibility[$profile];
  }
  public static function getCostVisibility($profile) {
    if (! self::$staticCostVisibility or ! isset(self::$staticCostVisibility[$profile]) ) {
      $pe=new PlanningElementBaseline();
      $pe->setVisibility($profile);
    }
    return self::$staticCostVisibility[$profile];
  }
  
  public function setVisibility($profile=null) {
    if (! sessionUserExists()) {
      return;
    }
    if (! $profile) {
      $user=getSessionUser();
      $profile=$user->getProfile($this->idProject);
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
  
  public function getFieldAttributes($fieldName) {
    if (! $this->_costVisibility or ! $this->_workVisibility) {
      $this->setVisibility();
    }
    if ($this->_costVisibility =='NO') {
      if (substr($fieldName,-4)=='Cost'
       or substr($fieldName,0,7)=='expense'
       or substr($fieldName,0,5)=='total'
       or substr($fieldName, 0,13) == 'reserveAmount') {
         return 'hidden';
      }
    } else if ($this->_costVisibility =='VAL') {
      if ( (substr($fieldName,-4)=='Cost' and $fieldName!='validatedCost')
       or (substr($fieldName,0,7)=='expense' and $fieldName!='expenseValidatedAmount')
       or (substr($fieldName,0,5)=='total' and $fieldName!='totalValidatedCost')
       or substr($fieldName, 0,13) == 'reserveAmount') {
         return 'hidden';
      }
    }
    if ($this->_workVisibility=='NO') {
      if (substr($fieldName,-4)=='Work') {
         return 'hidden';
      }
    } else if ($this->_workVisibility=='VAL') {
      if ( substr($fieldName,-4)=='Work' and $fieldName!='validatedWork') {
         return 'hidden';
      }
    }
    if ($this->id and $this->validatedCalculated) {
    	if ($fieldName=='validatedWork' or $fieldName=='validatedCost') {
    	  return "readonly";
    	}
    }
    if ($this->id and $this->validatedExpenseCalculated) {
      if ($fieldName=='expenseValidatedAmount' and $this->$fieldName>0) {
        return "readonly";
      }
    }
    return parent::getFieldAttributes($fieldName);
  }  
  
  static function comparePlanningElementSimple($a, $b) {
    if ($a->_sortCriteria<$b->_sortCriteria) {
      return -1;
    }
    if ($a->_sortCriteria>$b->_sortCriteria) {
      return +1;
    }
    return 0;       
  }
}
?>