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
 * History reflects all changes to any object.
 */ 
require_once('_securityCheck.php');
class HistoryArchive extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $refType;
  public $refId;
  public $operation;
  public $colName; 
  public $oldValue;
  public $newValue;
  public $operationDate;
  public $idUser;
  public $isWorkHistory;
  public $idProject;
  
  public static $_storeDate;
  public static $_storeItem;
  public $_noHistory=true; // Will never save history for this object
  public static $_avoidLoop=false;
  
  private static $_databaseTableName = 'historyarchive';
  
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

  /** ===========================================================================
   * Store a new History trace (will call ->save)
   * @param $refType type of object updated
   * @param $refId id of object updated
   * @param $operation 
   * @param $colName name of column updated
   * @param $oldValue old value of column (before update)
   * @param $newValue new value of column (after update)
   * @return boolean true if save is OK, false either
   */
  
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  function save(){
    return parent::save();
  }
  
}
?>