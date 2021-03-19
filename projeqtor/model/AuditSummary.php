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
 * Action is establised during meeting, to define an action to be followed.
 */ 
require_once('_securityCheck.php'); 
class AuditSummary extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $auditDay;
  public $firstConnection;
  public $lastConnection;
  public $numberSessions;
  public $minDuration;
  public $maxDuration;
  public $meanDuration;
  
  public $_noHistory;
  public $_readOnly=true;
  
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
  
  static function updateAuditSummary($day) {
    global $remoteDb;
    if (isset($remoteDb) and $remoteDb) return;
  	AuditSummary::finishOldSessions($day);
  	$audit=new Audit();
  	$crit=array('auditDay'=>$day);
  	$summary=SqlElement::getSingleSqlElementFromCriteria('AuditSummary', $crit);
  	$summary->numberSessions=0;
  	$summary->auditDay=$day;
  	$summary->firstConnection=null;
  	$summary->minDuration=null;
  	$summary->maxDuration=null;
  	$totDuration=0;
  	$list=$audit->getSqlElementsFromCriteria($crit);
  	foreach($list as $audit) {
      if (! $summary->firstConnection or $audit->connectionDateTime<$summary->firstConnection) {
  		  $summary->firstConnection=$audit->connectionDateTime;
      }  
      if ($audit->disconnectionDateTime>$summary->lastConnection) {
        $summary->lastConnection=$audit->disconnectionDateTime;
      }
      $summary->numberSessions++;
      if (! $summary->minDuration or $audit->duration<$summary->minDuration) {
      	$summary->minDuration=$audit->duration;
      } 
      if ($audit->duration>$summary->maxDuration) {
        $summary->maxDuration=$audit->duration;
      }
      if ($audit->lastAccessDateTime and $audit->connectionDateTime) {
        $totDuration+=strtotime($audit->lastAccessDateTime)-strtotime($audit->connectionDateTime);
      }
  	}
    if ($summary->numberSessions>0) {
  	  $meanDuration=round($totDuration/$summary->numberSessions,0);   
	    $hh=floor($meanDuration/3600);
	    $meanDuration-=$hh*3600;
	    $mm=floor($meanDuration/60);
	    $meanDuration-=$mm*60;  
	    $ss=$meanDuration;
	    if ($hh>=24) {
	      $summary->meanDuration='23:59:59';
	    } else {
	      $summary->meanDuration=$hh.':'.$mm.':'.$ss;
	    }   
    } else {
    	$summary->meanDuration='00:00:00';
    }
  	$result=$summary->save();
  	return $result;
  }
    
   static function finishOldSessions($day) {
     global $remoteDb;
     if (isset($remoteDb) and $remoteDb) return;
     global $simuIndex;
     if (isset($simuIndex)) return;
   	 $crit="auditDay < '" . $day . "' and idle=0";
   	 $audit=new Audit();
   	 $list=$audit->getSqlElementsFromCriteria(null, false, $crit);
   	 $delay=Parameter::getGlobalParameter('alertCheckTime');
   	 if (! $delay or $delay < 30) { $delay=30 ;}
   	 foreach ($list as $audit) {
   	 	 $duration=strtotime(date('Y-m-d'))-strtotime($audit->lastAccessDateTime);
       if ($duration>5*$delay) { // Very old connection, idle now, must be closed
    	 	 //$audit->requestDisconnection=1;
         $audit->idle=1;
   	 	   $audit->disconnectionDateTime=$audit->lastAccessDateTime;
         $res=$audit->save();
   	   } 
   	 }    	 
   }    
}
?>