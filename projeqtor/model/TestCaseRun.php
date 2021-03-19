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
class TestCaseRun extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $idTestCase;
  public $idTestSession;
  public $comment;
  public $sortOrder;
  public $idRunStatus;
  public $statusDateTime;
  public $idTicket;
  public $idle;
  public $result;

  
  private static $_colCaptionTransposition = array('idRunStatus'=> 'idStatus',
                                                   'idTicket'=>'ticket',
                                                   );
    
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
 
  /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  // ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********


/** =========================================================================
   * control data corresponding to Model constraints
   * @param void
   * @return "OK" if controls are good or an error message 
   *  must be redefined in the inherited class
   */
  public function control($allowDuplicate=false){
    $result="";
    
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }
    if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save($allowDuplicate=false) {

  	$new=($this->id)?false:true;
  	$old=$this->getOld();
  	
  	if (! $allowDuplicate and property_exists($this, '_copy') and $this->_copy) { // Already controlled on saveTestCaseRun.php, but must be done again for copy of TestSession
  	  $crit=array('idTestCase'=>$this->idTestCase,'idTestSession'=>$this->idTestSession);
  	  $lst=$this->getSqlElementsFromCriteria($crit);
  	  if (count($lst)>0) {
  	    return "not copied";
  	  }
  	}
  	
  	$result=parent::save();
  	
  	// Store link to ticket if idTicket is set
  	if (trim($this->idTicket)) {      
      if ($this->idTicket!=$old->idTicket) {
      	$link=new Link();
      	$link->ref1Type='TestSession';
      	$link->ref1Id=$this->idTestSession;
      	$link->ref2Type='Ticket';
      	$link->ref2Id=$this->idTicket;
      	$link->comment=i18n('TestCase') . ' #' . $this->idTestCase;
      	$link->save();
      }
  	}
  	
  	if ($new) {
  		// on insertion, insert sub-test cases if exists
  	  $tc=new TestCase();
  	  $crit=array('idTestCase'=>$this->idTestCase,'idle'=>'0');
  	  $list=$tc->getSqlElementsFromCriteria($crit);
  	  foreach ($list as $tc) {
  	  	$crit=array('idTestCase'=>$tc->id,'idTestSession'=>$this->idTestSession);
		    $lst=$this->getSqlElementsFromCriteria($crit);
		    if (count($lst)==0 or $allowDuplicate) {
		    	$tcr=new TestCaseRun();
	  	  	$tcr->idTestCase=$tc->id;
	        $tcr->idTestSession=$this->idTestSession;
	        $tcr->comment=$this->comment;
	        $tcr->idRunStatus=$this->idRunStatus;
	        $tcr->statusDateTime=$this->statusDateTime;
	        $tcr->idTicket=$this->idTicket;
	        $tcr->idle=$this->idle;
	        $tcr->sortOrder=$this->sortOrder;
	        $res=$tcr->save();
	  	    if (stripos($res,'id="lastOperationStatus" value="OK"')>0 ) {
		        $deb=stripos($res,'#');
		        $fin=stripos($res,' ',$deb);
		        $resId=substr($res,$deb, $fin-$deb);
		        $deb=stripos($result,'#');
		        $fin=stripos($result,' ',$deb);
		        $result=substr($result, 0, $fin).','.$resId.substr($result,$fin);
			    }
        }
  	  }	
  	}
  	
  	$session=new TestSession($this->idTestSession);
    $session->updateDependencies();
    $test=new TestCase($this->idTestCase);
    $test->updateDependencies();
    
    // List all Resquirements linked to the test case
    $link=new Link();
    $crit=array('ref1Type'=>'Requirement', 'ref2Type'=>'TestCase', 'ref2Id'=>$this->idTestCase);
    $listLink=$link->getSqlElementsFromCriteria($crit);
    foreach ($listLink as $link) {
      $req=new Requirement($link->ref1Id);
      $req->updateDependencies();
	    // Store link to ticket (on requirement) if idTicket is set
	    if (trim($this->idTicket)) {      
	      if ($this->idTicket!=$old->idTicket) {
	        $linkR=new Link();
	        $linkR->ref1Type='Requirement';
	        $linkR->ref1Id=$req->id;
	        $linkR->ref2Type='Ticket';
	        $linkR->ref2Id=$this->idTicket;
	        $linkR->comment=i18n('TestCase') . ' #' . $this->idTestCase;
	        $linkR->save();
	      }
	    }
    }
    // Store history for TestSession
  	return $result;
  }
  
  public function delete() {
  	
  	$result=parent::delete();
    $link=new Link();
    $crit=array('ref1Type'=>'Requirement', 'ref2Type'=>'TestCase', 'ref2Id'=>$this->idTestCase);
    $listLink=$link->getSqlElementsFromCriteria($crit);
    foreach ($listLink as $link) {
      $req=new Requirement($link->ref1Id);
      $req->updateDependencies();
    }
    $session=new TestSession($this->idTestSession);
    $session->updateDependencies();
    $test=new TestCase($this->idTestCase);
    $test->updateDependencies();
    return $result;
  }
  
  public static function sort($a,$b) {
    if ($a->sortOrder == $b->sortOrder) {
    		if ($a->id < $b->id) {
    		  return -1;
    		} else {
    		  return 1;
    		}
    } else if ($a->sortOrder < $b->sortOrder) {
    		return -1;
    } else {
    		return 1;
    }
  }
  
  
}
?>