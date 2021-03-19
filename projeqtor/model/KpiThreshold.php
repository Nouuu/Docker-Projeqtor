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
 * Line defines right to the application for a menu and a profile.
 */  
require_once('_securityCheck.php'); 
class KpiThreshold extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $idKpiDefinition;
  public $thresholdValue;
  public $thresholdColor;
  public $_noHistory=true; // Will never save history for this object
  
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
 
  public function drawKpiThresholdList($critArray,$idKpiDefinition) {
    global $print;
    $result='<table style="width:100%;">';
    $thresholdList=$this->getSqlElementsFromCriteria($critArray,false,null,'thresholdValue asc',false,true);
    $canUpdate=securityGetAccessRightYesNo('menuKpiDefinition', 'update') == "YES";
    $result.= '<tr>';
    if (! $print and $canUpdate) {
      $result.= '<td class="linkHeader" style="text-align:center;width:10%;white-space:nowrap;">';
      $result.= '  <a onClick="addKpiThreshold(\'' . $idKpiDefinition . '\');" '
            .'title="' . i18n('addKpiThreshold') . '" > '.formatSmallButton('Add').'</a>';
    }
    $result.= '<td class="linkHeader" style="width:50%;">'.i18n('colName').'</td>';
    $result.= '<td class="linkHeader" style="width:20%;">'.i18n('colValue').'</td>';
    $result.= '<td class="linkHeader" style="width:20%;">'.i18n('colColor').'</td>';
    $result.= '</tr>';
    foreach ($thresholdList as $th) {
      $result.= '<tr>';
      if (! $print and $canUpdate) {
        $result.= '<td class="linkData" style="text-align:center;width:10%;white-space:nowrap;">';
        $result.= '  <a onClick="removeKpiThreshold(\'' . htmlEncode($th->id) . '\');" '
                .'title="' . i18n('removeKpiThreshold') . '" > '.formatSmallButton('Remove').'</a>';
        $result.= '  <a onClick="editKpiThreshold(\'' . htmlEncode($th->id) . '\');" '
            .'title="' . i18n('editKpiThreshold') . '" > '.formatSmallButton('Edit').'</a>';
        $result.= '</td>';
      }
      $result.= '<td class="linkData" style="width:50%;">'.$th->name.'</td>';
      $result.= '<td class="linkData" style="width:20%;">'.htmlDisplayNumericWithoutTrailingZeros($th->thresholdValue).'</td>';
      $result.= '<td class="linkData" style="width:20%;">'.colorFormatter($th->thresholdColor).'</td>';
      $result.= '</tr>';
    }
    $result .="</table>";
    return $result;
  }
}
?>
