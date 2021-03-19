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
 * Parameter is a global kind of object for parametring.
 * It may be on user level, on project level or on global level.
 */ 
require_once('_securityCheck.php');
class Today extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visiblez place 
  public $idUser;
  public $scope;
  public $staticSection;
  public $idReport;
  public $sortOrder;
  public $idle;
  
  public static $staticList=array('Projects','AssignedTasks','ResponsibleTasks', 'AccountableTasks', 'IssuerRequestorTasks','ProjectsTasks','Documents');
  public $_noHistory=true; // Will never save history for this object
  
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
  
  function delete() {
  	$p=new TodayParameter();
  	$res=$p->purge("idToday=".$this->id);
  	return parent::delete();
  }

// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********

  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  
  public static function insertStaticItems() {
    $user=getSessionUser();
    $sort=0;
    foreach (self::$staticList as $static) {
      $crit=array('idUser'=>$user->id, 'scope'=>'static', 'staticSection'=>$static);
      $sort+=1;
      $item=SqlElement::getSingleSqlElementFromCriteria('Today', $crit);
      if (!$item->id) {
        $item->sortOrder=$sort;
        $item->idle=0;
        $item->scope='static';
        $item->save();
      }
    }
  }
}
?>