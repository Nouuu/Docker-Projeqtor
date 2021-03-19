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
class Message extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $idMessageType;
  public $startDate;
  public $endDate;
  public $idle;
  //public $_sec_message;
  public $description;
  public $_sec_detail;
  public $idProfile;
  public $idProject;
  public $idAffectable;
  public $showOnLogin;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="50%">${title}</th>
    <th field="colorNameMessageType" width="15%" formatter="colorNameFormatter">${idMessageType}</th>
    <th field="startDate" width="15%" formatter="dateFormatter" >${startDate}</th>
    <th field="endDate" width="15%" formatter="dateFormatter" >${endDate}</th>'
    ;
  
  private static $_colCaptionTransposition = array('name'=> 'title', 'description'=>'message','idAffectable'=>'idUser');
  
  private static $_fieldsAttributes=array("name"=>"required", 
                                  "idMessageType"=>"required"
  );  
  private static $_databaseColumnName = array('idAffectable'=>'idUser');
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    if (! $this->id) {
      $this->showOnLogin=1;
    }
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

/** ==========================================================================
 * Return the validation sript for some fields
 * @return the validation javascript (for dojo frameword)
 */
  // Florent ticket #4030
public function getValidationScript($colName) {
   $colScript = parent::getValidationScript($colName);
  if ($colName=="startDate" ) {
    $colScript .= '<script type="dojo/connect" event="onChange" >';
    $colScript .="var end=dijit.byId('endDate');";
    $colScript .="var start=dijit.byId('startDate').get('value');";
    $colScript .="end.set('dropDownDefaultValue',this.value);";
    $colScript .="end.constraints.min=start;"; 
    $colScript .= 'formChanged();';
    $colScript .= '</script>';
  }else if ($colName=="endDate"  ) {
    $colScript .= '<script type="dojo/connect" event="onChange" >';
    $colScript .="var end=dijit.byId('endDate').get('value');";
    $colScript .="var start=dijit.byId('startDate');";
    $colScript .="start.set('dropDownDefaultValue',this.value);";
    $colScript .="start.constraints.max=end;";
    $colScript .= 'formChanged();';
    $colScript .= '</script>';
  }else if ($colName=="startDateBis" ) {
    $colScript .= '<script type="dojo/connect" event="onChange" >';
    $colScript .="var start =dojo.byId('startDate').value;";
    $colScript .="var end =dojo.byId('endDate').value;";
    $colScript .= "if (start == end) {";
    $colScript .="      var endBis=dijit.byId('endDateBis');";
    $colScript .="      var startBis=dijit.byId('startDateBis').get('value');";
    $colScript .= "     endBis.set('dropDownDefaultValue',this.value);";
    $colScript .= "     endBis.constraints.min=startBis;";
    $colScript .= "}";
    $colScript .= 'formChanged();';
    $colScript .= '</script>';
  }else if ($colName=="endDateBis" ) {
    $colScript .= '<script type="dojo/connect" event="onChange" >';
    $colScript .="var start =dojo.byId('startDate').value;";
    $colScript .="var end =dojo.byId('endDate').value;";
    $colScript .= "if (start == end) {";
    $colScript .="    var endBis=dijit.byId('endDateBis').get('value');";
    $colScript .="    var startBis=dijit.byId('startDateBis');";
    $colScript .="    startBis.set('dropDownDefaultValue',this.value);";
    $colScript .="    startBis.constraints.max=endBis;";
    $colScript .= "}";
    $colScript .= 'formChanged();';
    $colScript .= '</script>';
  }
  
  return $colScript;
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
  
   /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  public function setAttributes() {
    if (isNewGui()) {
      if ($this->showOnLogin) self::$_fieldsAttributes["showOnLogin"]="hidden";
      self::$_fieldsAttributes["_sec_detail"]="hidden";
      self::$_fieldsAttributes["idProfile"]="hidden";
      self::$_fieldsAttributes["idProject"]="hidden";
      //if (Parameter::getUserParameter('paramLayoutObjectDetail')=='tab') self::$_fieldsAttributes["_sec_message"]="hidden";
    } else {
      //self::$_fieldsAttributes["_sec_message"]="hidden";
    }
  }
}
?>