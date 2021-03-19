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
 * Assignment defines link of resources to an Activity (or else)
 */ 
require_once('_securityCheck.php');
class ExpenseDetailType extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;
  public $name;
  public $sortOrder;
  public $value01; 
  public $unit01;
  public $value02;
  public $unit02;
  public $value03;
  public $unit03;
  public $idle;
  public $description;
  public $_sec_scope;
  public $individual;
  public $project;
  
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="26%" >${name}</th>
    <th field="sortOrder" width="5%">${sortOrder}</th>
    <th field="value01" width="5%" >${value}</th>
    <th field="unit01" width="10%" >${unit}</th>
    <th field="value02" width="5%" >${value}</th>
    <th field="unit02" width="10%" >${unit}</th>
    <th field="value03" width="5%" >${value}</th>
    <th field="unit03" width="10%" >${unit}</th>
    <th field="individual" width="7%" formatter="booleanFormatter">${individualExpense}</th>
    <th field="project" width="7%" formatter="booleanFormatter">${projectExpense}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
    
      private static $_fieldsAttributes=array("name"=>"required",
                                              "value01"=>"nobr",
                                              "value02"=>"nobr",
                                              "value03"=>"nobr"
      );
      
      private static $_colCaptionTransposition = array('value01'=>'valueUnit', 
                                                   'value02'=> 'valueUnit',
                                                   'value03' => 'valueUnit',
                                                   'unit01'=>'unit', 
                                                   'unit02'=>'unit',
                                                   'unit03'=>'unit',
                                                   "project"=>'projectExpense',
                                                   'individual'=>'individualExpense');
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if ($this->value01==0) $this->value01=null;
    if ($this->value02==0) $this->value02=null;
    if ($this->value03==0) $this->value03=null;
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /**
   * Save object 
   * @see persistence/SqlElement#save()
   */
  public function save() {
    $result = parent::save();
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
    if ( ($this->value01 and ! $this->unit01)  
      or ($this->value02 and ! $this->unit02)  
      or ($this->value03 and ! $this->unit03) ) {
    	 $result.='<br/>' . i18n('errorValueWithoutUnit');
    } 
    
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
}
?>