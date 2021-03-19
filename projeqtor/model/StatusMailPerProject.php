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
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class StatusMailPerProject extends StatusMail {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $idMailable;
  public $idType;
  public $idProject;
  public $idStatus;
  public $idEventForMail;
  public $idle;
  public $_sec_SendMail;
  public $mailToContact;
  public $mailToUser;
  public $mailToAccountable;
  public $mailToResource;
  public $mailToFinancialResponsible;
  public $mailToSponsor;
  public $mailToProject;  
  public $mailToProjectIncludingParentProject;
  public $_lib_globalProjectTeam;
  public $mailToLeader;
  public $mailToManager;
  public $mailToAssigned;
  public $mailToSubscribers;
  public $mailToOther;
  public $otherMail;
  public $isProject;
  
  public $_noCopy;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameMailable" formatter="translateFormatter" width="11%" >${idMailable}</th>
    <th field="nameType" formatter="nameFormatter" width="9%" >${type}</th>
    <th field="nameProject" width="7%">${idProject}</th>
    <th field="colorNameStatus" width="6%" formatter="colorNameFormatter">${newStatus}</th>
    <th field="nameEventForMail" formatter="translateFormatter" width="10%" >${orOtherEvent}</th>
    <th field="mailToContact" width="6%" formatter="booleanFormatter" >${mailToContact}</th>    
    <th field="mailToUser" width="6%" formatter="booleanFormatter" >${mailToUser}</th>
    <th field="mailToResource" width="6%" formatter="booleanFormatter" >${mailToResource}</th>
    <th field="mailToProject" width="6%" formatter="booleanFormatter" >${mailToProject}</th>
    <th field="mailToLeader" width="6%" formatter="booleanFormatter" >${mailToLeader}</th>
    <th field="mailToManager" width="6%" formatter="booleanFormatter" >${mailToManager}</th>
    <th field="mailToAssigned" width="6%" formatter="booleanFormatter" >${mailToAssigned}</th>
    <th field="mailToSubscribers" width="6%" formatter="booleanFormatter" >${mailToSubscribers}</th>  
    <th field="mailToOther" width="6%" formatter="booleanFormatter" >${mailToOther}</th>
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

    private static $_fieldsAttributes=array("idMailable"=>"", 
                                  "mailToOther"=>"nobr",
                                  "otherMail"=>"",
                                  "idType"=>"nocombo", 
  		                            "mailToSponsor"=>"hidden,calculated",
                                  "idProject"=>"required",
                                  "isProject"=>"hidden",
                                  "mailToProjectIncludingParentProject" => "nobr",
                                  "mailToAccountable"=>"invisible",
                                  "mailToAssigned"=>"invisible",
                                  "mailToFinancialResponsible"=>"invisible"
  );  
  
  private static $_colCaptionTransposition = array('idStatus'=>'newStatus',
  'otherMail'=>'email',
  'idEventForMail'=>'orOtherEvent',
  "mailToAccountable"=>"idAccountable",
  'idType'=>'type');
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
  
  private static $_databaseTableName = 'statusmail';
  
  private static $_databaseCriteria = array('isProject'=>'1');
    
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

 public function setAttributes() {
   parent::setAttributes();
   self::$_fieldsAttributes=array_merge_preserve_keys(self::$_fieldsAttributes,parent::getStaticFieldsAttributes());
   if ($this->id) {
     $mailable=SqlList::getNameFromId('Mailable', $this->idMailable,false);
     if ($mailable=="ProjectExpense") {
       self::$_colCaptionTransposition["mailToResource"]="businessResponsible";
     }else if ($mailable=="IndividualExpense") {
       self::$_colCaptionTransposition["mailToResource"]="resource";
       self::$_colCaptionTransposition["mailToFinancialResponsible"]="responsible";
     }
   }
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

  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
  /** ========================================================================
   * Return the specific databaseTableName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  public function getValidationScript($colName) {
  
    $colScript = parent::getValidationScript($colName);
    return $colScript;
  }
  
}
?>