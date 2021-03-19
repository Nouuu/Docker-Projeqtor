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
 * Stauts defines list of Priorities an activity or action can get in (lifecylce).
 */ 
require_once('_securityCheck.php');
class ReportParameter extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idReport;
  public $name;
  public $paramType;
  public $defaultValue;
  public $sortOrder;
  public $multiple;
  public $idle; 
  // Define the layout that will be used for lists
  
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
  
  public static function displayParameters($params) {
    echo "<table>";
    foreach ($params as $col=>$val) {
      $stCol=$col;
      if (substr($col,0,2)=='id' and strlen($col)>2) {
        $class=substr($col,2);
        if (SqlElement::class_exists($class)) {
          $col=i18n($class);
          $val=SqlList::getNameFromId($class, $val);
        }
      } else if ($col=="periodType" or $col=="periodValue") {
        $col="";
        $val="";
      } else if ($col=="periodScale" and isset($params['periodValue'])) {
        $col=i18n("colPeriod");
        $val=$params['periodValue'].' '.i18n($val);
        unset($params['periodValue']);
      } else if (substr($col,-7)=="Spinner") {
        $col=i18n(substr($col,0, -7));
      } else if ($col=="requestor" or $col=="issuer" or $col=="responsible") {
        $col=i18n('col'.ucfirst($col));
        $val=SqlList::getNameFromId('Affectable', $val);
      } else if ($col=="listShowMilestone") {
        $col=i18n('col'.ucfirst($col));
        $val=SqlList::getNameFromId('MilestoneType', $val);
      } else {
        if (substr($col,-4)=="Date") {
          $val=htmlFormatDate($val);
        }
        if (i18n('col'.ucfirst($col))!="[$col]") {
          $col=i18n('col'.ucfirst($col));
        } else {
          $col=i18n($col);
        }
        if (i18n($val)!="[$val]") {
          $val=i18n($val);
        } else if ($val=='on') {
          $val=i18n('displayYes');
        } 
      }
      if ($col and $val) {
        echo '<tr>';
        echo '<td>'.$col.'&nbsp;:&nbsp;'.$val.'</td>';
        echo '</tr>';
      }
    }
    echo "</table>";
  }
  
}
?>