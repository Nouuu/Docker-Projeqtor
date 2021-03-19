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
 * Menu defines list of items to present to users.
 */ 
require_once('_securityCheck.php');
class WorkflowProfile extends SqlElement {
  public $id; 
  public $idWorkflow;
  public $idProfile;
  public $checked;
  
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
  
// PBE : Direct SQL must be banned !!! (the query won't work if paramDbPrefix is set)   
//  function createSqlRow() {
//    $query = "INSERT INTO workflowprofile(idWorkflow, idProfile, checked) SELECT ".$this->idWorkflow.", ".$this->idProfile.", ".$this->checked." FROM dual WHERE NOT EXISTS (SELECT * FROM workflowprofile WHERE idWorkflow = ".$this->idWorkflow." AND idProfile = ".$this->idProfile.")";
//    Sql::query($query);
//  }
  
  function getCheckedInfo() {
    $arr = array("idWorkflow" => $this->idWorkflow, "idProfile" => $this->idProfile);
    $obj = $this->getSingleSqlElementFromCriteria(get_class($this), $arr);
    return ($obj?$obj->checked:1);
  }
  
  function getAuthorizationProfileList($idWorkflow) {
    $result = $this->getSqlElementsFromCriteria(array("idWorkflow"=>$idWorkflow, "checked"=>"0"));
    return ($result);
  }
}
?>