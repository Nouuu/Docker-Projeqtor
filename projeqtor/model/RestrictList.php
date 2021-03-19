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
 * List of orginable items
 */ 
require_once('_securityCheck.php');
class RestrictList extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProfile;
  public $showAll;
  public $showStarted;
  public $showDelivered;
  public $showInService;
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********

// PBE : Direct SQL must be banned !!! (the query won't work if paramDbPrefix is set)  
//   function createSqlRow() {
//     $cpt=$this->countSqlElementsFromCriteria(null,"idProfile='$this->idProfile'");
//     if ($cpt == 0) {
//       $query = "INSERT INTO restrictlist (idProfile, showAll, showStarted, showDelivered, showInService) VALUES ($this->idProfile, 1, 0, 0, 0)";
//       Sql::query($query);
//     }
//   }
  
  function getCheckedInfo() {
    $arr = array("idProfile" => $this->idProfile);
    $obj = $this->getSingleSqlElementFromCriteria(get_class($this), $arr);
    return (array("showAll"=>$obj->showAll, "showStarted"=>$obj->showStarted, "showDelivered"=>$obj->showDelivered, "showInService"=>$obj->showInService));
  }
}
?>