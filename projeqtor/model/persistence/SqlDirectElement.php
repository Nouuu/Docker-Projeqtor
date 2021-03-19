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
 * abstract class to define a view as a direct Sql resource
 */ 
if (file_exists('../_securityCheck.php')) include_once('../_securityCheck.php');
abstract class SqlDirectElement {

  public $request;

  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct() {
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
  }

    /** ==========================================================================
     * Returns an array (lines) of corresponing objects
     * @param string $query Query to get Lines
     * @return SqlElement[]
     */
  public function getLines($query) {
    $result = Sql::query($query); 
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine($result);
      while ($line) {
        $obj=clone($this);
        // get all data fetched
        foreach ($obj as $col_name => $col_value) {
          if (substr($col_name,0,1)=="_") {
            // not a fiels, just for presentation purpose
          } else if (ucfirst($col_name) == $col_name) {
            //$obj->{$col_name}=$obj->getDependantSqlElement($col_name);
          } else {
            $obj->{$col_name}=$line[$obj->getDatabaseColumnName($col_name)];
          }
        }
        $objects[]=$obj;
        $line = Sql::fetchLine($result);
      }
    } else {
      if ($initializeIfEmpty) {
        $objects[]=$defaultObj; // return at least 1 element, initialized with criteria
      }
    }
    return $objects;
  }
  
  static public function execute($query) {
    $result = Sql::query($query); 
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
}
?>