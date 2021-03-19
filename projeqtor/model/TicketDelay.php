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
class TicketDelay extends Delay {

  // Define the layout that will be used for lists
    
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $idTicketType;
  public $idUrgency;
  public $idProject;
  public $value;
  public $idDelayUnit;
  public $idle;
  //public $_sec_void;
  public $isProject;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="nameTicketType" width="20%">${idTicketType}</th>
    <th field="nameUrgency" width="20%">${urgency}</th>
    <th field="nameProject" width="20%">${idProject}</th>
    <th field="value" width="10%" formatter="numericFormatter">${value}</th>
    <th field="nameDelayUnit" width="20%" formatter="translateFormatter">${unit}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("idTicketType"=>"required",
                                          "idType"=>"hidden", 
                                          "idUrgency"=>"required",
                                          "value"=>"required, nobr",
                                          "idDelayUnit"=>"required",
                                          "scope"=>"hidden",
                                          "isProject"=>"hidden",
  );
  
  private static $_databaseCriteria = array('scope'=>'Ticket');
  
  private static $_databaseColumnName = array("idTicketType"=>"idType");
  
  private static $_colCaptionTransposition = array('idDelayUnit'=>'unit');
  
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
  
  public function control() {
    $result="";
    $crit="scope='Ticket' and idType=" . Sql::fmtId($this->idTicketType) . " and idUrgency=" . Sql::fmtId($this->idUrgency) . " and id<>" . Sql::fmtId($this->id);
    if(property_exists($this, 'idProject') and $this->idProject){
      $crit.=  " and idProject=" . Sql::fmtId($this->idProject);
    } else {
      $crit.=  " and idProject is null";
    }
    $list=$this->getSqlElementsFromCriteria(null, false, $crit);
    if (count($list)>0) {
      $result.="<br/>" . i18n('errorDuplicateTicketDelay',null);
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {
    if ($this->idProject) $this->isProject=1;
    else $this->isProject=0;
    return parent::save();
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
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
    /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }  

}
?>