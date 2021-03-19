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
 * RiskType defines the type of a risk.
 */ 
require_once('_securityCheck.php');
class WorkPeriod extends SqlElement {

	 public $id;
	 public $idResource;
   public $periodRange;
   public $periodValue;
   public $submitted;
   public $submittedDate;
   public $validated;
   public $validatedDate;
   public $idLocker;
   public $comment;
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
  public static function getWorkPeriod($id) {
    if (strpos($id,'_')>0) {
      $split=explode('_',$id);
      $workPeriod = SqlElement::getSingleSqlElementFromCriteria('WorkPeriod', array('periodRange'=>'week','periodValue'=>$split[0],'idResource'=>$split[1]));
      if (! $workPeriod->id) {
        $workPeriod->idResource=$split[1];
        $workPeriod->periodRange='week';
        $workPeriod->periodValue=$split[0];
        $workPeriod->submitted=0;
        $workPeriod->validated=0;
      }
      return $workPeriod;
    } else {
      $workPeriod = new WorkPeriod($id, true);
      return $workPeriod;
    }
  }
}
?>