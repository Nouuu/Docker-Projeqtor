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
/** ============================================================================
 * Reason of ending a Employment Contract.
 */ 
require_once('_securityCheck.php'); 
class EmploymentContractEndReasonMain extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $name;
  public $final;
  public $idle;
  public $_nbColMax=3;
  
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="name" width="30%">${name}</th>
    <th field="final" width="5%" formatter="booleanFormatter">${final}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required"
      );    
  
  private static $_databaseTableName = 'employmentcontractendreason';
      
    
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
    protected function getStaticDatabaseTableName() {
        $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
        return $paramDbPrefix . self::$_databaseTableName;
    }

    /** ==========================================================================
     * Return the specific fieldsAttributes
     * @return the fieldsAttributes
    */
    protected function getStaticFieldsAttributes() {
        return self::$_fieldsAttributes;
    }
    
    static function isFinal($id=null) {
        if ($id==null) { return false;}
        $reason = new EmploymentContractEndReason($id);
        if ($reason->final==1) {return true;} else {return false;}
    }
}
?>