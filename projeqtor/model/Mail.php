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
class Mail extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $idUser;
  public $mailDateTime;
  public $mailTo;
  public $mailStatus;
  public $idle;
  public $mailTitle;
  public $_sec_MailItem;
  public $idProject;
  public $idMailable;
  public $refId;
  public $idStatus;
  public $_sec_MailText;
  public $_mailText_colSpan="2";
  public $mailBody;
  
  private static $_lastErrorMessage=null;
  public $_noHistory=true;
  
    private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="10%" >${idProject}</th>
    <th field="nameUser" formatter="thumbName22" width="10%" >${sender}</th>
    <th field="mailTitle" width="50%" >${mailTitle}</th>
    <th field="mailDateTime" width="10%" >${mailDateTime}</th>
    <th field="mailStatus" width="10%" >${mailStatus}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
    
    private static $_fieldsAttributes=array('mailBody'=>'displayHtml',
        'mailTitle'=>'readonly');
       
    private static $_databaseColumnName = array('idMailable'=>'refType');
    
    private static $_colCaptionTransposition = array('refId'=> 'id');
    
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
  
    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  public function save() {
  	$this->mailBody=substr($this->mailBody,0,65536); // Limit for MySql Text field
  	return parent::save();
  }
  
  public static function getLastErrorMessage() {
    return self::$_lastErrorMessage;
  } 
  public static function setLastErrorMessage($msg) {
    self::$_lastErrorMessage=$msg;
  }
  
  public static function isMailGroupingActiv() {
    return (Parameter::getGlobalParameter('mailGroupActive')=="YES"?true:false);
  }
  public static function getMailGroupPeriod() {
    if (!self::isMailGroupingActiv()) return -1;
    $period=Parameter::getGlobalParameter('mailGroupPeriod');
    return (($period)?$period:'60');
  }
  public static function getResultMessage($mailResult) {
    if ($mailResult) {
      if ($mailResult=='TEMP') {
        return i18n('emailScheduled');
      } else {
        return i18n('mailSent');
      }
    }
  }
}
?>