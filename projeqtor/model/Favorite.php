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
 * Parameter is a global kind of object for parametring.
 * It may be on user level, on project level or on global level.
 */ 
require_once('_securityCheck.php');
class Favorite extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visiblez place 
  public $idUser;
  public $scope;
  public $idReport;
  public $idMenu;
  public $sortOrder;
  public $idle;
  
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
  
  function delete() {
  	$p=new FavoriteParameter();
  	$res=$p->purge("idFavorite=".$this->id);
  	return parent::delete();
  }
  
  static function drawReportList() {
    $list=self::getReportList();
    if (count($list)==0) {
      return;
    }
    echo '<form dojoType="dijit.form.Form" id="favoriteReportsForm" name="todayParametersForm" onSubmit="return false;">';
    echo '<table style="width:100%">';
    echo '<tr><td class="" colspan="4" height="20px" style="text-align:center;font-weight:bold;">'.i18n('favoriteReports').'</td></tr>';
    echo '<tr><td colspan="4">&nbsp;</td></tr>';
    echo '</table>';
    echo '<table id="dndFavoriteReports" jsId="dndFavoriteReports" dojotype="dojo.dnd.Source"
        singular=true
        dndType="favoriteReports" withhandles="true" class="container" style="height:10px;width:100%;cellspacing:0; cellpadding:0;">';
    foreach($list as $rpt) {
      $params=FavoriteParameter::returnReportParameters($rpt['reportObject']);
      $paramsFavorite=FavoriteParameter::returnFavoriteReportParameters($rpt['favoriteObject']);
      foreach ($paramsFavorite as $pName=>$pValue) {
        $params[$pName]=$pValue;
      }
      $urlParam="";
      foreach ($params as $paramName=>$paramValue) {
        $urlParam.=($urlParam or strpos($rpt['fileName'],'?')>0)?'&':'?';
        $urlParam.=htmlEncode($paramName).'='.htmlEncode($paramValue);
      }
      $fileName=$rpt['fileName'];
      $orientation=$rpt['orientation'];
      $favorite=$rpt['favoriteObject'];
      
      echo '<tr id="favoriteRow' . htmlEncode($favorite->id). '" class="dojoDndItem" dndType="favoriteReports" style="height:10px;">';
      echo '<td class="dojoDndHandle handleCursor" style="vertical-align:top;"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;</td>';
      echo '<td style="width:20px;vertical-align:top;">';
      echo '<a '; echo 'onClick="removeFavoriteReport(' . htmlEncode($favorite->id). ');" />';
      if(isNewGui()){
        echo formatNewGuiButton('Remove', 16, true);
      }else{
        echo formatSmallButton('Remove');
      }
      echo '</a>';
      echo '<input type="hidden" name="favoriteReport' . htmlEncode($favorite->id). '" id="favoriteReport' . htmlEncode($favorite->id). '" value="0" />';
      echo '</td>';
      echo '<td  style="vertical-align:top;">';
      $cmd="";
      $cmd="dojo.byId('favoriteForm').reportName.value='". htmlEncode(i18n($rpt['name']),'quotes')."';";
      $cmd.="showPrint('../report/".htmlEncode($fileName).$urlParam."', 'favorite',null,null,'$orientation');";   
      echo '<div class="selectableList" onClick="'.$cmd.'">'.i18n($rpt['name']).'</div>';
      echo '<input type="hidden" style="width:100px"
       id="favoriteReportOrder' . htmlEncode($favorite->id). '" name="favoriteReportOrder' . htmlEncode($favorite->id). '"
       value="' . htmlEncode($favorite->sortOrder). '"/>';
      echo '</td>';
      echo '<td style="padding:2px 5px 0px 5px;font-size:80%;vertical-align:top;">';
      echo ReportParameter::displayParameters($paramsFavorite);
      echo '</td>';
      echo '</tr>';
    }
    echo '</table>';
    echo '</form>';
  }
  
  static function getReportList() {
    $result=array();
    $f=new Favorite();
    $fl=$f->getSqlElementsFromCriteria(array('idUser'=>getSessionUser()->id), false, null, 'sortOrder asc');
    foreach ($fl as $f) {
      $r=new Report($f->idReport);
      $result[]=array('id'=>$f->idReport,
          'name'=>$r->name, 
          'fileName'=>$r->file, 
          'orientation'=>$r->orientation, 
          'favoriteObject'=>$f,
          'reportObject'=>$r);
    }
    return $result;
  }
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********

}
?>