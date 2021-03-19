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
 * Profile defines right to the application or to a project.
 */ 
require_once('_securityCheck.php');
class AccessProfileAll extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visiblez place 
  public $name;
  public $idAccessScopeRead;
  public $idAccessScopeCreate;
  public $idAccessScopeUpdate;
  public $idAccessScopeDelete;
  public $sortOrder=0;
  public $idle;
  public $description;
  public $isNonProject;
  //public $_sec_void;
  
  public $_isNameTranslatable = true;
  private static $_databaseTableName = 'accessprofile';
  private static $_fieldsAttributes=array("name"=>"required", 
                                  "idAccessScopeRead"=>"required",
                                  "idAccessScopeCreate"=>"required",
                                  "idAccessScopeUpdate"=>"required",
                                  "idAccessScopeDelete"=>"required",
                                  "isNonProject"=>"hidden"
  );  

  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="name" width="25%" formatter="translateFormatter">${name}</th>
    <th field="nameAccessScopeRead" width="15%" formatter="translateFormatter">${idAccessScopeRead}</th>
    <th field="nameAccessScopeCreate" width="15%" formatter="translateFormatter">${idAccessScopeCreate}</th>
    <th field="nameAccessScopeUpdate" width="15%" formatter="translateFormatter">${idAccessScopeUpdate}</th>
    <th field="nameAccessScopeDelete" width="15%" formatter="translateFormatter">${idAccessScopeDelete}</th>
    <th field="sortOrder" width="5%">${sortOrderShort}</th>         
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';
  
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
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
 
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
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
 
}
?>