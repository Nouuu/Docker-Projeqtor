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
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once('_securityCheck.php');
class IndicatorDefinitionPerProject extends IndicatorDefinition {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $idIndicatorable;
  public $name;
  public $nameIndicatorable;
  public $idType;
  public $idProject;
  public $idIndicator;
  public $codeIndicator;
  public $typeIndicator;
  public $warningValue;
  public $idWarningDelayUnit;
  public $codeWarningDelayUnit;
  public $alertValue; 
  public $idAlertDelayUnit;
  public $codeAlertDelayUnit;
  public $idle;
  public $_sec_SendMail;
  public $mailToContact;
  public $mailToUser;
  public $mailToAccountable;
  public $mailToResource;
  public $mailToProject;
  public $mailToProjectIncludingParentProject;
  public $_lib_globalProjectTeam;
  public $mailToLeader;
  public $mailToManager;
  public $mailToAssigned;
  public $mailToSubscribers;
  public $mailToOther;
  public $otherMail;
  public $_sec_InternalAlert;
  public $alertToContact;
  public $alertToUser;
  public $alertToAccountable;
  public $alertToResource;
  public $alertToProject;
  public $alertToProjectIncludingParentProject;
  public $_lib_globalProjectTeamAlert;
  public $alertToLeader;
  public $alertToManager;
  public $alertToAssigned;
  public $alertToSubscribers;
  public $isProject;
  
  public $_isNameTranslatable = true;

  public $_noCopy;
  
  public $_nbColMax=3;
    
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameIndicatorable" formatter="translateFormatter" width="1%" >${element}</th>
    <th field="nameIndicator" width="30%" formatter="translateFormatter">${idIndicator}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="warningValue" width="8%" formatter="numericFormatter">${warning}</th>
    <th field="nameWarningDelayUnit" width="12%" formatter="translateFormatter">${unit}</th>
    <th field="alertValue" width="8%" formatter="numericFormatter">${alert}</th>
    <th field="nameAlertDelayUnit" width="12%" formatter="translateFormatter">${unit}</th> 
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"hidden",
                                  "idType"=>"hidden",
                                  "idProject"=>"required",
                                  "warningValue"=>"nobr",
                                  "alertValue"=>"nobr",
                                  "nameIndicatorable"=>"hidden",
                                  "codeIndicator"=>"hidden",
                                  "typeIndicator"=>"hidden",
                                  "codeWarningDelayUnit"=>"hidden",
                                  "codeAlertDelayUnit"=>"hidden",
                                  "mailToOther"=>"nobr",
                                  "otherMail"=>"invisible",
                                  "isProject"=>"hidden",
                                  "mailToProjectIncludingParentProject" => "nobr",
                                  "alertToProjectIncludingParentProject" => "nobr",
                                  "mailToAccountable"=>"invisible",
                                  "mailToAssigned"=>"invisible",
                                  "alertToAccountable"=>"invisible",
                                  "alertToAssigned"=>"invisible"
  );  
  
    private static $_colCaptionTransposition = array('idIndicatorable'=>'element',
                                                     'idType'=>'type',
                                                     'warningValue'=>'warning',
                                                     'alertValue'=>'alert',
                                                     'alertToUser'=>'mailToUser',
                                                     'mailToAccountable'=>'idAccountable',
                                                     'alertToAccountable'=>'idAccountable',
                                                     'alertToResource'=>'mailToResource',
                                                     'alertToProject'=>'mailToProject',
                                                     'alertToContact'=>'mailToContact',
                                                     'alertToLeader'=>'mailToLeader',
                                                     'alertToManager'=>'mailToManager',
                                                     'alertToAssigned'=>'mailToAssigned',
                                                     'alertToSubscribers'=>'mailToSubscribers',
                                                     'alertToProjectIncludingParentProject'=>'mailToProjectIncludingParentProject',
                                                     'otherMail'=>'email');
  
    
    private static $_databaseTableName = 'indicatordefinition';
    
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
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  public function setAttributes() {
    parent::setAttributes();
    self::$_fieldsAttributes=array_merge_preserve_keys(self::$_fieldsAttributes,parent::getStaticFieldsAttributes());
  }
  
}
?>