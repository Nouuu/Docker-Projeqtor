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
 * Client is the owner of a project.
 */ 
require_once('_securityCheck.php');
class KpiDefinition extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $code;
  public $idle;
  public $description;
  public $_sec_thresholds;
  public $_spe_thresholds;
  public $_noDelete=true;
  public $_noCreate=true;
  
  public static $_kpiDefinitionList=null;
  
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="50%" >${name}</th>
    <th field="code" width="10%" >${code}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
    
    private static $_fieldsAttributes=array(
        "code"=>"readonly"
    );
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
  
  /** =========================================================================
   * Draw a specific item for the current class.
   * @param $item the item. Correct values are :
   *    - subprojects => presents sub-projects as a tree
   * @return an html string able to display a specific item
   *  must be redefined in the inherited class
   */
  public function drawSpecificItem($item){
    $result="";
    if ($item=='thresholds' ) {
     
      if ($this->id) {
        $th=new KpiThreshold();
        $crit=array('idKpiDefinition'=>$this->id);
        $result.= $th->drawKpiThresholdList($crit,$this->id);
      }
      
      return $result;
    } 
  }
  
  public static function getKpiDefinitionList() {
    if (self::$_kpiDefinitionList) {
      return self::$_kpiDefinitionList;
    } 
    $sessionList=getSessionValue('kpiDefinitionList',null,true);
    if ($sessionList) {
      self::$_kpiDefinitionList=$sessionList;
      return self::$_kpiDefinitionList;
    }
    $tmp_kd=new KpiDefinition();
    $list=$tmp_kd->getSqlElementsFromCriteria(array('idle'=>'0'));
    self::$_kpiDefinitionList=array();
    foreach ($list as $kd) {
      self::$_kpiDefinitionList[$kd->code]=$kd;
    }
    setSessionValue('kpiDefinitionList', self::$_kpiDefinitionList);
    return self::$_kpiDefinitionList;
  }
  
}
?>