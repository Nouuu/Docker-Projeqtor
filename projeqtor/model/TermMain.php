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
 * defines a term for a payment
 */ 
require_once('_securityCheck.php');
class TermMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idProject;
  public $idBill;
  public $idUser;
  public $idResource;
  public $creationDate;
  public $idle;
  public $done;
  public $_sec_Price;
  public $_tab_3_2_smallLabel = array('real', 'validated', 'planned', 'amount', 'date');
  public $amount;
  public $validatedAmount; 
  public $plannedAmount;
  public $date;
  public $validatedDate;
  public $plannedDate;
  public $_sec_trigger;
  public $_Dependency_Predecessor=array();
  public $_Note=array();
  public $_sec_Link;
  public $_Link=array();
  
  public $_nbColMax=3;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="nameProject" width="10%">${idProject}</th>
    <th field="name" width="15%">${name}</th>
    <th field="amount" width="10%" formatter="costFormatter">${realAmount}</th>
    <th field="date" width="10%" formatter="dateFormatter">${realDate}</th>
  	<th field="validatedAmount" width="10%" formatter="costFormatter">${validatedAmount3}</th>
    <th field="validatedDate" width="10%" formatter="dateFormatter">${validatedDate}</th>
  	<th field="plannedAmount" width="10%" formatter="costFormatter">${plannedAmount2}</th>
    <th field="plannedDate" width="10%" formatter="dateFormatter">${plannedDate2}</th>
    <th field="idBill" width="5%" formatter="booleanFormatter" >${isBilled}</th>
    ';
  
  private static $_fieldsAttributes=array("name"=>"required",
                                          "idProject"=>"required",
  								                        "idBill"=>"readonly",
                                          "validatedAmount"=>"readonly",
                                          "validatedDate"=>"readonly",
                                          "plannedAmount"=>"readonly",
                                          "plannedDate"=>"readonly"
  );  
  
  private static $_colCaptionTransposition = array("idUser"=>"issuer",'idResource'=>"responsible");
  
  //private static $_databaseColumnName = array('realAmount'=>'amount');
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    //$noTrigger=true;
    if ($id) {
      $crit=array('successorRefType'=>'Term', 'successorRefId'=>$this->id);
      $dep=new Dependency();
      $depList=$dep->getSqlElementsFromCriteria($crit, false);
      if (! count($depList)) {
      	self::$_fieldsAttributes["validatedAmount"]="";
    		self::$_fieldsAttributes["validatedDate"]="";
   			self::$_fieldsAttributes["plannedAmount"]="";
 				self::$_fieldsAttributes["plannedDate"]="";
      }
    }
    $this->setCalculatedFromActivities();
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
//  protected function getStaticDatabaseColumnName() {
//    return self::$_databaseColumnName;
//  }
 /** =========================================================================
   * Overrides SqlElement::deleteControl() function to add specific treatments
   * @see persistence/SqlElement#deleteControl()
   * @return the return message of persistence/SqlElement#deleteControl() method
   */  
  
  public function deleteControl() {
  	$result = "";
  	if ($this->idBill){
  		$result .= "<br/>" . i18n("cannotDeleteBilledTerm");
  	}
  	if (! $result) {  
      $result=parent::deleteControl();
    }
    return $result;
  }
  
  
/** =========================================================================
   * Overrides SqlElement::save() function to add specific treatments
   * @see persistence/SqlElement#save()
   * @return the return message of persistence/SqlElement#save() method
   */  

	public function save() {
	    if($this->idBill){
	      $this->done = 1;
	    }
		$this->setCalculatedFromActivities();
		$result = parent::save();	
		KpiValue::calculateKpi($this);
		return $result;
	}
	
	public function setCalculatedFromActivities() {
		if ($this->id) {
			$crit=array('successorRefType'=>'Term', 'successorRefId'=>$this->id);
			$dep=new Dependency();
			$depList=$dep->getSqlElementsFromCriteria($crit, false);
			if (! count($depList)) return;
			$valAmount=0;
			$valDate=null;
			$plaAmount=0;
			$plaDate=null;
			foreach ($depList as $dep) {
				$obj=new PlanningElement($dep->predecessorId);
				$valAmount+=$obj->validatedCost;
				$plaAmount+=$obj->plannedCost;
				if ($obj->validatedEndDate and (! $valDate or $valDate<$obj->validatedEndDate)) {
					$valDate=$obj->validatedEndDate;
				}
				if ($obj->plannedEndDate and (! $plaDate or $plaDate<$obj->plannedEndDate)) {
					$plaDate=$obj->plannedEndDate;
				}
			}
			$this->validatedAmount=$valAmount;
			$this->plannedAmount=$plaAmount;
			$this->validatedDate=$valDate;
			$this->plannedDate=$plaDate;
		}
	}
  
}
?>