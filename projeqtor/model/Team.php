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
class Team extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $idResource;
  public $idle;
  public $description;
  public $_sec_members;
  public $_spe_members;
  public $_spe_affectMembers;
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="85%">${name}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  private static $_colCaptionTransposition = array('idResource'=>'teamManager');
  
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
  
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  public function drawSpecificItem($item){
    global $print;
    $showClosedResources=(Parameter::getUserParameter('showClosedResources')!='0')?TRUE:FALSE;
    $result = "";
    if ($item=='members') {
      $ress=new Resource();
      if ($this->id) {
        $result .= $ress->drawMemberList($this->id, $showClosedResources);
      }
      return $result;
    } else if ($item=='affectMembers') {
    	
    	if ($this->id and !$print) {
	    	$result .= '<button id="affectTeamMembers" dojoType="dijit.form.Button" showlabel="true"'; 
	      $result .= ' class="roundedVisibleButton" title="' . i18n('affectTeamMembers') . '" >';
	      $result .= '<span>' . i18n('affectTeamMembers') . '</span>';
	      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
	      $result .=  '  affectTeamMembers(' . htmlEncode($this->id) . ');';
	      $result .= '</script>';
	      $result .= '</button>';
	      return $result;
    	}
    }
  }
  
  public function getMembers() {
    $result=array();
    $crit=array('idTeam'=>$this->id);
    $res=new Resource();
    $resList=$res->getSqlElementsFromCriteria($crit, false);
    foreach ($resList as $res) {
      if (!$res->idle) $result[$res->id]=$res->name;
    }
    return $result;
  }
  
}
?>