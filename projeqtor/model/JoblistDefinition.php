<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Julien PAPASIAN
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
 * Habilitation defines right to the application for a menu and a profile.
 */
require_once('_securityCheck.php');
class JoblistDefinition extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place
  public $name;
  public $idChecklistable;
  public $nameChecklistable;
  public $idType;
  //public $lineCount;

  public $idle;
  public $_sec_JobDefinition;
  public $_spe_JobDefinition;
  public $_JobDefinition=array();
  public $_jobDefinition_colSpan="2";
  public $_noCopy;

    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameChecklistable" formatter="translateFormatter" width="20%" >${element}</th>
    <th field="nameType" width="20%" >${type}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"hidden",
                                  "idType"=>"nocombo",
                                  "nameChecklistable"=>"hidden",
  		                            //"lineCount"=>"readonly"
  );

    private static $_colCaptionTransposition = array('idType'=>'type', 'idChecklistable'=>'element');

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

  public function save() {
  	$Checklistable=new Checklistable($this->idChecklistable);
  	$this->nameChecklistable=$Checklistable->name;
  	return parent::save();
  }

    /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
  	$colScript = parent::getValidationScript($colName);
  	if ($colName=='idChecklistable') {
  		$colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
  		$colScript .= '  dijit.byId("idType").set("value",null);';
  		$colScript .= '  refreshList("idType","scope", checklistableArray[this.value]);';
  		$colScript .= '</script>';
  	}
    return $colScript;
  }

  public function control(){
    $result="";
    if (! trim($this->idChecklistable)) {
    	$result.='<br/>' . i18n('messageMandatory',array(i18n('colElement')));
    }

    $crit=array('idChecklistable'=>trim($this->idChecklistable),
                'idType'=>trim($this->idType));
    $elt=SqlElement::getSingleSqlElementFromCriteria('JoblistDefinition', $crit);
    if ($elt and $elt->id and $elt->id!=$this->id) {
      $result.='<br/>' . i18n('errorDuplicateChecklistDefinition');
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }

  public function drawSpecificItem($item){
    global $print, $outMode, $largeWidth;
    $result="";
    if ($item=='JobDefinition') {
      drawJobDefinitionFromObject($this);
      return $result;
    }
  }
}
?>
