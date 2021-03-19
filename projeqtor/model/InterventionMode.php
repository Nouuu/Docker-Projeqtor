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
 * RiskType defines the type of a risk.
 */ 
require_once('_securityCheck.php');
class InterventionMode extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;
  public $name;
  public $letter;
  public $sortOrder=0;
  public $idle;
  public $idleDate;
  //public $_sec_void;
  
  //public $_isNameTranslatable = true;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="50%">${name}</th>
    <th field="letter" width="40%">${letter}</th>
    <th field="sortOrder" width="20%">${sortOrderShort}</th>  
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array(
      "name"=>"required",
      "letter"=>"required",
      "idle"=>"nobr"
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
  
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
  public static function drawList($readonly=false) {
    $im=new InterventionMode();
    $list=$im->getSqlElementsFromCriteria(array('idle'=>'0'));
    echo '<table>';
    echo '<tr>';
    echo '<td class="reportTableHeader" colspan="2" style="width:220px">'.i18n('menuInterventionMode').'</td>';
    echo '</tr>';
    if(sessionValueExists('selectInterventionPlannedWorkManual'))$isSaveSessionInterventionPlannedWorkManual = getSessionValue('selectInterventionPlannedWorkManual');
    $valueLetter = null;
    $valueId = null;
    foreach ($list as $im) {
      $onClick=($readonly)?'':'onClick="selectInterventionMode('.$im->id.',\''.$im->letter.'\');"';
      $cursor=($readonly)?"normal":"pointer";
      $class  = "";
      if(isset($isSaveSessionInterventionPlannedWorkManual)){
        if($isSaveSessionInterventionPlannedWorkManual == $im->id){
          $class = "dojoxGridRowSelected";
          $valueLetter = $im->letter;
          $valueId = $im->id;
        }
      }
      echo '<tr class=" '.$class.' interventionModeSelector interventionModeSelector'.$im->id.'" style="border:1px solid #a0a0a0;height:25px;cursor'.$cursor.'" '.$onClick.'>';
      echo '<td class="dojoxGridCell interventionModeSelector interventionModeSelector'.$im->id.'" style="width:20px;text-align:center">'.$im->letter.'</td>';
      echo '<td class="dojoxGridCell interventionModeSelector interventionModeSelector'.$im->id.'" style="width:200px">'.$im->name.'</td>';
      echo '</tr>';
    }
    echo '</table>';
    echo '<input type="hidden" id="idInterventionMode" value="'.$valueId.'" style="width:20px;background:#ffe0e0" />';
    echo '<input type="hidden" id="letterInterventionMode" value="'.$valueLetter.'" style="width:20px;background:#ffe0e0" />';
  }
  
}
?>