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

/* ==================================================================================
 * AssignmentRecurring describes how assignment is to be dispatched on recUrring task
 */ 
require_once('_securityCheck.php');
class AssignmentSelection extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;
  public $idAssignment;
  public $idResource;
  public $startDate;
  public $endDate;
  public $userSelected;
  public $selected;
  
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

  public static function addResourcesFromPool($idAssignment,$idPool,$userSelected) {
    $rtf=new ResourceTeamAffectation();
    $rList=$rtf->getSqlElementsFromCriteria(array('idResourceTeam'=>$idPool));
    $res="";
    foreach ($rList as $r) {
      $ar=SqlElement::getSingleSqlElementFromCriteria('AssignmentSelection', array('idResource'=>$r->idResource,'idAssignment'=>$idAssignment));
      if (! $ar->id) {
        $res=$ar->save();
      } else {
        if ($ar->userSelected and $ar->idResource!=$userSelected) {
          $ar->userSelected=0;
          $res=$ar->save();
        } else if (! $ar->userSelected and $ar->idResource==$userSelected) {
          $ar->userSelected=1;
          $res=$ar->save();
        }
      }
    }
    return $res;
  }
  
  public static function getListForAssignment($idAssignment) {
    $as=new AssignmentSelection();
    $rList=$as->getSqlElementsFromCriteria(array('idAssignment'=>$idAssignment));
    return $rList;
  }
  
  public static function drawListForAssignment($idAssignment,$realWork=0) {
    echo "<table style='width:100%'>";
    echo "  <tr>";
    echo "    <td class='assignHeader' style='width:40%'>".i18n('colIdResource')."</td>";
    echo "    <td class='assignHeader' style='width:20%'>".i18n('colPlannedEndDate')."</td>";
    echo "    <td class='assignHeader' style='width:20%'>".i18n('colSelected')."</td>";
    echo "    <td class='assignHeader' style='width:20%'>".i18n('colYourSelection')."</td>";
    echo "  </tr>";
    $userSelected=null;
    foreach (self::getListForAssignment($idAssignment) as $as) {
      if ($as->userSelected) $userSelected=$as->idResource;
      echo "  <tr>";
      echo "    <td class='assignData verticalCenterData'>".SqlList::getNameFromId('Resource', $as->idResource)."</td>";
      echo "    <td class='assignData centerData verticalCenterData'>".(($as->endDate)?htmlFormatDate($as->endDate,true):i18n('colNotPlannedWork'))."</td>";
      echo "    <td class='assignData centerData verticalCenterData'>".(($as->selected)?"<img src='../view/img/check.png' />":"")."</td>";
      echo "    <td class='assignData centerData verticalCenterData' style='white-space:nowrap'>";
      echo "      <input dojoType='dijit.form.CheckBox' class='dialogAssignmentManualSelectCheck' id='dialogAssignmentManualSelectCheck_$as->idResource'";
      echo "       onChange='assignmentUserSelectUniqueResource(this.checked,$as->idResource);' ";
      if  ($as->userSelected) echo " checked=checked ";
      echo "/>";
      if ($realWork==0) {
      echo "     <button class='textButton' dojoType='dijit.form.Button' onclick='protectDblClick(this);saveAssignment($as->idResource);return false;' title=\"".i18n('helpDefinitiveSelection')."\">";
      echo i18n("buttonDefinitiveSelection"); 
      echo "     </button>";
      }
      echo "</td>";
      echo "  </tr>";
    }
    echo "</table>";
    echo "  <input type='hidden' id='dialogAssignmentManualSelect' name='dialogAssignmentManualSelect' value='$userSelected' />";
  }
}
?>