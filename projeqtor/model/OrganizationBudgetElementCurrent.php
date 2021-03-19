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
class OrganizationBudgetElementCurrent extends OrganizationBudgetElement {

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
 
// ADD BY Marc TABARY - 2017-02-27 - ORGANIZATION BUDGET

  /** ==========================================================================
   * Extends save functionality to implement update toIp
   * Triggers parent::save() to run defaut functionality in the end.
   * @return the result of parent::save() function
   */
  public function save() {
// ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
    if (Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {
      $returnValue= '<input type="hidden" id="lastSaveId" value="" />';
      $returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
      $returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
      return i18n ( 'messageNoChange' ).$returnValue;
    }    
// END ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT      
    // Update total budget
    $this->totalBudgetCost = $this->budgetCost + $this->expenseBudgetAmount;

    //$result=parent::save();    
    //return $result;
    
    // Get old element (stored in database) : must be fetched before saving
    $old=$this->getOld();

    // Update budget of parent organizations, if budget change
    if ($this->budgetWork !== $old->budgetWork or 
        $this->budgetCost != $old->budgetCost or 
        $this->expenseBudgetAmount !== $old->expenseBudgetAmount) {

        // Get Parent organization of BudgetElement
        $parentOrga = $this->getOrganizationParent();
        // If exists
        if ($parentOrga != null and $parentOrga->id) {
            // Calculate new budgets for parent organization
            $diffBudgetWork = $this->budgetWork - $old->budgetWork;
            $diffBudgetCost = $this->budgetCost - $old->budgetCost;
            $diffBudgetExpenseAmount = $this->expenseBudgetAmount - $old->expenseBudgetAmount;
            $diffTotalBudgetCost = $diffBudgetCost + $diffBudgetExpenseAmount;
            
            $bec=$parentOrga->OrganizationBudgetElementCurrent;
//            $old=$bec;
           
            $bec->budgetWork+=$diffBudgetWork;
            $bec->budgetCost+=$diffBudgetCost;
            $bec->expenseBudgetAmount+=$diffBudgetExpenseAmount;
            $bec->totalBudgetCost+=$diffTotalBudgetCost;
            
            $bec->save();
            // Get Parent of parent
            $parentOrga = $bec->getOrganizationParent();
}
    }
        
    $result=parent::save();    

    return $result;
  }
  
  public function delete() {
    if (Parameter::getGlobalParameter('useOrganizationBudgetElement')!="YES") {
      $returnValue= '<input type="hidden" id="lastSaveId" value="" />';
      $returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
      $returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
      return i18n ( 'messageNoChange' ).$returnValue;
    }
    return parent::delete();
  }
// END ADD BY Marc TABARY - 2017-02-27 - ORGANIZATION BUDGET
  
}?>