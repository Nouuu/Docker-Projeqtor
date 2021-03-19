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
 * ProductType defines the type of a Product.
 */ 
require_once('_securityCheck.php');
class DeliverableType extends Type {

  // Define the layout that will be used for lists
    
  
   private static $_fieldsAttributes=array('idWorkflow'=>'hidden',
    "mandatoryResultOnDone"=>"hidden",
    "_lib_mandatoryOnDoneStatus"=>"hidden",
    "lockHandled"=>"hidden",
    "_lib_statusMustChangeHandled"=>"hidden",
    "lockDone"=>"hidden",
    "_lib_statusMustChangeDone"=>"hidden",
    "lockIdle"=>"hidden",
    "_lib_statusMustChangeIdle"=>"hidden",
    "lockCancelled"=>"hidden",
    "_lib_statusMustChangeCancelled"=>"hidden",
    "mandatoryResourceOnHandled"=>"hidden",
    "_lib_mandatoryOnHandledStatus"=>"hidden");

   // Define the layout that will be used for lists
   private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="70%">${name}</th>
    <th field="code" width="10%">${code}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
   
  private static $_databaseCriteria = array('scope'=>'Deliverable');
  
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
    return array_merge(parent::getStaticFieldsAttributes(),self::$_fieldsAttributes);
  }
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
}
?>