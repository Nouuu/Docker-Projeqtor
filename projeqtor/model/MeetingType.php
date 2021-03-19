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
 * MeetingType defines the type of an issue.
 */ 
require_once('_securityCheck.php');
class MeetingType extends Type {

  public $idPlanningMode;
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
                                          "lockNoLeftOnDone"=>"nobr",
                                          "scope"=>"hidden");
  private static $_databaseCriteria = array('scope'=>'Meeting');
  private static $_colCaptionTransposition = array('description'=>'meetingAgenda');
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    $this->idPlanningMode=16;
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

  /** ========================================================================
   * Return the specific database criteria
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
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