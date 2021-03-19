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
class PlanningElementExtension   extends SqlElement {

  // List of fields that will be exposed in general user interface
  
  public $id;
  public $refType;
  public $refId;
  public $topId;
  public $topRefType;
  public $topRefId;
  public $wbs;
  public $wbsSortable;
  public static $_startId=1000000000;
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" width="0%" >${id}</th>
    <th field="refType" formatter="classNameFormatter" width="10%" >${refType}</th>
    <th field="refType" formatter="numericFormatter" width="5%" ># ${id}</th>
    ';

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
  

  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);
    return $colScript;
  }
  
  /** ==========================================================================
   * Insert item in PlanningElementExtension if not exists in PlanningElement
   * Will return the PlanningElementExtension object if found or inserted
   * @param unknown $type
   * @param unknown $id
   */
  public static function checkInsert($type, $id, $wbs=null, $wbsSortable=null) {
    $peName=$type.'PlanningElement';
    if (property_exists($type, $peName)) return null; // Nothing to do if PlanningElement exists
    $pex=SqlElement::getSingleSqlElementFromCriteria('PlanningElementExtension',array('refType'=>$type,'refId'=>$id));
    if ($pex->id) { // Exists : just check is $wbs if different 
      if (($wbs and $wbs!=$pex->wbs) or ($wbsSortable and $wbsSortable!=$pex->wbsSortable)) {
        $pex->wbs=$wbs;
        if ($wbsSortable) $pex->wbsSortable=$wbsSortable;
        else $pex->wbsSortable=formatSortableWbs($wbs);
        $pex->save();
        return $pex;
      } else {
        return $pex;
      }
    }
    $pex->refType=$type;
    $pex->refId=$id;
    if ($wbs) {
      $pex->wbs=$wbs;
      if ($wbsSortable) $pex->wbsSortable=$wbsSortable;
      else $pex->wbsSortable=formatSortableWbs($wbs);
    }
    $pex->save();
    return $pex;
  }
  
  public static function checkDelete($type, $id) {
    // TODO clean unsuefull PEX table lines
    if (!$type or !$id) return;
    $where="(predecessorRefType='$type' and predecessorRefId=$id) or (successorRefType='$type' and successorRefId=$id)";
    $dep=new Dependency();
    $cpt=$dep->countSqlElementsFromCriteria(null,$where);
    if ($cpt==0) {
      $pex=SqlElement::getSingleSqlElementFromCriteria('PlanningElementExtension', array('refType'=>$type,'refId'=>$id));
      if ($pex->id) $pex->delete();
    }
  }
  
  public function save() {
    if (!$this->topRefType or !$this->topRefId) {
      $type=$this->refType;
      $item=new $type($this->refId);
      $this->topRefType='Project';
      $this->topRefId=$item->idProject;
    } 
    return parent::save();
  }
  
  public function getFakeId() {
    if (! $this->id) return null;
    return self::$_startId + $this->id;
  }
  
  public function getFromGlobalPlanningElement($id) {
    $id-=self::$_startId;
    return new PlanningElementExtension($id);
  }
  
}
?>