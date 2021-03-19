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
class ResourceCapacity extends SqlElement {

  public $id;
  public $idResource;
  public $capacity;
  public $description;
  public $startDate;
  public $endDate;
  public $idle;
  
  
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
  
  public function control(){
    $result="";
    
    $resCap = new ResourceCapacity();
    $listResCap = $resCap->getSqlElementsFromCriteria(array('idResource'=>$this->idResource));
    foreach ($listResCap as $cap){
      if($cap->id != $this->id){
        if($this->startDate >= $cap->startDate and $this->startDate <= $cap->endDate){
          $result = i18n('errorPeriodCantOverlap');
        }
        if($this->endDate >= $cap->startDate and $this->endDate <= $cap->endDate){
          $result = i18n('errorPeriodCantOverlap');
        }
      }
    }
    
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
}
?>