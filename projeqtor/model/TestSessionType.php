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
 * QuestionType defines the type of a Question.
 */ 
require_once('_securityCheck.php');
class TestSessionType extends Type {

  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place
  public $name;
  public $code;
  public $idWorkflow;
  public $idTestSessionPlanningMode;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $_sec_Behavior;
  public $mandatoryDescription;
  public $_lib_mandatoryField;
  public $mandatoryResourceOnHandled;
  public $_lib_mandatoryOnHandledStatus;
  public $mandatoryResultOnDone;
  public $_lib_mandatoryOnDoneStatus;
  public $lockHandled;
  public $_lib_statusMustChangeHandled;
  public $lockDone;
  public $_lib_statusMustChangeDone;
  public $lockIdle;
  public $_lib_statusMustChangeIdle;
  public $lockCancelled;
  public $_lib_statusMustChangeCancelled;
  public $showInFlash;
  public $internalData;
  // Define the layout that will be used for lists
    
  private static $_fieldsAttributes=array("name"=>"required",
      "idWorkflow"=>"required",
      "mandatoryDescription"=>"nobr",
      "mandatoryResourceOnHandled"=>"nobr",
      "mandatoryResultOnDone"=>"nobr",
      "mandatoryResolutionOnDone"=>"hidden",
      "_lib_mandatoryResolutionOnDoneStatus"=>"hidden",
      "lockHandled"=>"nobr",
      "lockDone"=>"nobr",
      "lockIdle"=>"nobr",
      "lockCancelled"=>"nobr",
      "internalData"=>"hidden",
      "showInFlash"=>"hidden",
      "idPlanningMode"=>"hidden",
      "idTestSessionPlanningMode"=>"required",
      "lockNoLeftOnDone"=>"nobr",
      "scope"=>"hidden");
  private static $_colCaptionTransposition = array('idTestSessionPlanningMode'=>'defaultPlanningMode');
  private static $_databaseColumnName = array('idTestSessionPlanningMode'=>'idPlanningMode');
  private static $_databaseCriteria = array('scope'=>'TestSession');
  
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
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  /** ========================================================================
   * Return the specific databaseColumnName
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
}
?>