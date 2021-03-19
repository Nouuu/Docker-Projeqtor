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

/*
 */
require_once ('_securityCheck.php');
class WorkElementMain extends SqlElement {
	
	// extends SqlElement, so has $id
	public $id; // redefine $id to specify its visiblez place
	public $refType;
	public $refId;
	public $idActivity;
	public $idProject;
	public $refName;
	public $_tab_3_1 = array('estimated', 'real','left','ticketWork');
	public $plannedWork;
	public $realWork;
	public $leftWork;
	public $realCost;
	public $leftCost;
	public $_spe_run;
	public $_spe_dispatch;
	public $idUser;
	public $ongoing;
	public $ongoingStartDateTime;
	
	public $done;
	public $idle;
	public $_nbColMax=3;
	
	private static $_fieldsAttributes = array (
	    "id" => "hidden",
	    "refType" => "hidden",
			"refId" => "hidden",
			"refName" => "hidden",
			"realWork" => "noImport",
	    "realCost" => "hidden,noImport",
	    "leftCost" => "hidden",
			"ongoing" => "hidden",
			"ongoingStartDateTime" => "hidden",
			"idUser" => "hidden",
			"idActivity" => "hidden",
	    "idProject" => "hidden",
			"leftWork" => "readonly",
			"done" => "hidden",
			"idle" => "hidden"
	);
	private static $_colCaptionTransposition = array (
			'plannedWork' => 'estimatedWork' 
	);
	
	/**
	 * ==========================================================================
	 * Constructor
	 * 
	 * @param $id the
	 *        	id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct ( $id );
	}
	
	/**
	 * ==========================================================================
	 * Destructor
	 * 
	 * @return void
	 */
	function __destruct() {
		parent::__destruct ();
	}
	
	// ============================================================================**********
	// GET STATIC DATA FUNCTIONS
	// ============================================================================**********
	
	/**
	 * ==========================================================================
	 * Return the specific fieldsAttributes
	 * 
	 * @return the fieldsAttributes
	 */
	protected function getStaticFieldsAttributes() {
	  global $hideScope;
		if (! $this->id and !$hideScope) {
			self::lockRealWork ();
		}
		return self::$_fieldsAttributes;
	}
	public static function lockRealWork() {
		self::$_fieldsAttributes ['realWork'] = 'readonly,noImport';
	}
	/**
	 * ============================================================================
	 * Return the specific colCaptionTransposition
	 * 
	 * @return the colCaptionTransposition
	 */
	protected function getStaticColCaptionTransposition($fld=null) {
		return self::$_colCaptionTransposition;
	}
	
	public function save($noDispatch=false) {
	  if (!$this->id) { // No WorkElement retreived
	    $topObject=new $this->refType($this->refId,true);
	    $profile=getSessionUser()->getProfile($topObject);
	    $hWork=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array('idProfile' => $profile,'scope' => 'work'));
	    if ($hWork and $hWork->id) {
	      $visibility=SqlList::getFieldFromId('VisibilityScope', $hWork->rightAccess, 'accessCode', false);
	      if ($visibility != 'ALL') {
	        return "OK";
	      }
	    }
	    $this->ongoing=0;
	    $this->ongoingStartDateTime=null;
	  }
    $old = $this->getOld ();
		$ass=null;
		if (! sessionUserExists()) return parent::save ();
		$user = getSessionUser();
		// Update left work
		$this->leftWork = $this->plannedWork - $this->realWork;
		if ($this->leftWork < 0 or $this->done or $this->idle) {
			$this->leftWork = 0;
		}
		
		// Retrive informations from Ticket and possibly Parent Activity (from Ticket)
		$top = null;
		$topProject=null;
		if ($this->refType) {
			$top = new $this->refType ( $this->refId ); // retrieve Ticket
			$this->refName=$top->name;
			$this->idProject=$top->idProject;
			$topProject = $top->idProject; // Retrive project from Ticket
			if (isset ( $top->idActivity )) { // Retrive Activity from Ticket
				$this->idActivity = $top->idActivity;
			}
		} else { // Should never come here; workElement is always sub-item of Ticket
			// $top = new Project();
			//$topProject = $this->idProject;
		}
		
		// Set done if Ticket is done
		if ($top and isset ( $top->done ) and $top->done == 1) {
			$this->leftWork = 0;
			$this->done = 1;
		}		
    
		if ($top and property_exists ( $top, 'idActivity' ) and ! $noDispatch) {
			$this->idActivity = $top->idActivity;
		}
		$result = parent::save ();
		
		if ($top and property_exists ( $top, 'idActivity' ) and ! $noDispatch) {
			// Check if changed Planning Activity
			if (! trim ( $old->idActivity ) and $old->idActivity != $this->idActivity) {
			  // If Activity changed, retrieve existing work
				$crit = array (
						'refType' => $this->refType,
						'refId' => $this->refId 
				);
				$work = new Work ();
				$workList = $work->getSqlElementsFromCriteria ( $crit );
				// Assign existing work to the activity (preserve link through idWorkElement)
				foreach ( $workList as $work ) {
					$work->refType = 'Activity';
					$work->refId = $this->idActivity;
					$ass=self::updateAssignment ( $work, $work->work, true );
					$work->idAssignment=($ass)?$ass->id:null;
					$work->idWorkElement=$this->id;
					$resWork=$work->save ();
					if (getLastOperationStatus($resWork)!='OK') {
					  return $resWork;
					}
          if ($ass) {
            $resAss=$ass->saveWithRefresh();
            if (getLastOperationStatus($resAss)!='OK') {
              return $resAss;
            }
          }
				}
			}
		}

		if ($noDispatch) {
		  return $result;
	  }
		$diff = $this->realWork - $old->realWork;
		// If realWork has changed (not through Dispatch screen), update the work
		if ($diff != 0) {
			// Set work to Ticket
			$idx = - 1;
			// Will retrive work for current WorkElement, Current resource
			$crit = array (
				'idWorkElement' => $this->id,
				'idResource' => $user->id 
				);
			// If change is add work, will input current date
			if ($diff > 0) {
				$crit ['workDate'] = date ( 'Y-m-d' );
			}
			$work = new Work ();
			$workList = $work->getSqlElementsFromCriteria ( $crit, true, null, 'day asc' );
			if (count ( $workList ) > 0) { // If work exists, retrive the last one
			  $idx=count($workList)-1;
				$work=$workList [$idx];
				//$work = end($workList);
			} else { // If work does not exist, will create new one
				$work = new Work ();
				$work->refType = $this->refType;
				$work->refId = $this->refId;
				$work->idResource = $user->id;
				$work->idProject = $topProject;
				$work->dailyCost = null;
				$work->idWorkElement=$this->id;
				$work->cost = 0;
			}
			if ($diff > 0) {
				$work->work += $diff;
				$work->setDates ( date ( 'Y-m-d' ) );
				if ($work->work < 0) { // Should never happen as $diff is > 0 here 
					$work->work = 0;
				}
				if ($this->idActivity) {
				  $work->refType = 'Activity';
				  $work->refId = $this->idActivity;
				} else { // Ensure work is correcly set to ref item
					$work->refType = $this->refType;
					$work->refId = $this->refId;
				}
				$work->idProject = $topProject;
				$ass=self::updateAssignment ( $work, $diff );
				$work->idAssignment=($ass)?$ass->id:null;
				$work->idWorkElement=$this->id;
				$resWork=$work->save ();
				if (getLastOperationStatus($resWork)!='OK') {
				  return $resWork;
				}
				if ($ass) {
				  $resAss=$ass->saveWithRefresh();
				  //if (getLastOperationStatus($resAss)!='OK') {
				  //  return $resAss; // No $ass already updated, so will lead to NO_CHANGE
				  //}
				}
			} else {
			  // Remove work : so need to remove from existing (reverse loop on date) 
				while ( $diff < 0 and $idx >= 0 ) {
					$valDiff = 0;
					if ($work->work + $diff >= 0) {
						$valDiff = $diff;
						$work->work += $diff;
						$diff = 0;
					} else {
						$valDiff = (- 1) * $work->work;
						$diff += $work->work;
						$work->work = 0;
					}
					$ass=self::updateAssignment ( $work, $valDiff );
					$work->idAssignment=($ass)?$ass->id:null;
					$work->idWorkElement=$this->id;
					$resWork="";
					if ($work->work == 0) {
						if ($work->id) {
							$resWork=$work->delete ();
						}
					} else {
						$resWork=$work->save ();
					}
					if ($resWork!="" and getLastOperationStatus($resWork)!='OK') {
					  return $resWork;
					}
  				if ($ass) {
  				  $resAss=$ass->saveWithRefresh();
  				  //if (getLastOperationStatus($resAss)!='OK') {
  				  //  return $resAss; // No $ass already updated, so will lead to NO_CHANGE
  				  //}
  				}
					$idx --;
					if ($idx >= 0) { // Retrieve previous work element
						$work = $workList [$idx];
					} else if ($diff!=0) { // Not more work for current user, but could not remove all difference (exiting work for other user)
					  // Reaffect work !!!
					  $this->realWork=$diff*(-1);
					  $resSaveLeft=$this->save(true); // Save but do not try and dispatch any more
					}
				}
			}
		}
		
		// UPDATE COSTS
		$wk=new Work();
		$costs=$wk->sumSqlElementsFromCriteria(array('cost'), array('idWorkElement'=>$this->id));
		if ($costs) {
		  $this->realCost=$costs['sumcost'];
		  if ($this->realWork!=0) {
		    $this->leftCost=round($this->leftWork*$this->realCost/$this->realWork,0);
		  }
		  $resCost=$this->save(true);
		  if (getLastOperationStatus($result)=='NO_CHANGE') $result=$resCost;
		}
		
		// UPDATE PARENTS
		if ($top and property_exists ( $top, 'idActivity' ) and $top->idActivity) { 
		  // Update Activity
			$ape = SqlElement::getSingleSqlElementFromCriteria ( 'ActivityPlanningElement', array (
					'refType' => 'Activity',
					'refId' => $top->idActivity 
			) );
			if ($ape and $ape->id) {			
				$ape->updateWorkElementSummary ();
			}
		}
		if ($old->id and $top and property_exists($top,'idActivity') and $old->idActivity and $old->idActivity!=$this->idActivity) {
		  // Update Old Activity (if changed)
		  $ape = SqlElement::getSingleSqlElementFromCriteria ( 'ActivityPlanningElement', array (
		      'refType' => 'Activity',
		      'refId' => $old->idActivity
		  ) );
		  if ($ape and $ape->id) {
		    $ape->updateWorkElementSummary ();
		  }
		}
		if (!trim($this->idActivity) and getLastOperationStatus($result)!='NO_CHANGE') { 
		  // Work not counted on activity => update project
		  ProjectPlanningElement::updateSynthesis('Project',$this->idProject);
		}
		if (!$old->idActivity and $old->id and $old->idProject!=$this->idProject and getLastOperationStatus($result)!='NO_CHANGE') {
		  // Take into acocunt project change
		  ProjectPlanningElement::updateSynthesis('Project',$old->idProject);
		}
		
		return $result;
	}
  function control() {
    global $saveDispatchMode;
    $result="";
    $old = $this->getOld ();
    $diff=$this->realWork-$old->realWork;
    if ($diff < 0 and $saveDispatchMode!=true ) {
      $crit = array (
          'idWorkElement' => $this->id,
          'idResource' => getSessionUser()->id
      );
      $w=new Work();
      $sum=$w->sumSqlElementsFromCriteria('work', $crit);  
      if ($sum+$diff<0) {
        $result.='<br/>' . i18n('errorRemoveTooMuchWork',array(Work::displayImputationWithUnit($sum)));
      }
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  // Save without extra save() feature and without controls
  public function simpleSave() {
    $result = parent::saveForced();
    return $result;
  }
  
	public static function updateAssignment($work, $diff) {
		if ($work->refType != 'Activity') {
			return null;
		}
		$ass = new Assignment ();
		$crit = array (
				'refType' => $work->refType,
				'refId' => $work->refId,
				'idResource' => $work->idResource 
		);
		$lstAss = $ass->getSqlElementsFromCriteria ( $crit );
		if (count ( $lstAss ) > 0) {
			$ass = $lstAss [count ( $lstAss ) - 1];
			if ($ass->rate===null) $ass->rate=100;
		} else {
			$ass = new Assignment ();
			$ass->refType = $work->refType;
			$ass->refId = $work->refId;
			$ass->idResource = $work->idResource;
			$ass->isNotImputable='1';
			$ass->rate=100;
		}
		$ass->leftWork -= $diff;
		$ass->realWork += $diff;
		$ass->realCost += $diff * $work->dailyCost;
		
		//gautier  #4257
		if($ass->leftWork==0 OR $ass->leftWork < 0){
		  $assP = new Assignment();
		  $lstPull = $assP->countSqlElementsFromCriteria(array('refId'=>$ass->refId,'refType'=>$ass->refType,'isResourceTeam'=>1));
		  if($lstPull){
		    $listAssPool = $assP->getSqlElementsFromCriteria(array('refId'=>$ass->refId,'refType'=>$ass->refType,'isResourceTeam'=>1));
		    $resTeam = new ResourceTeamAffectation();
		    foreach ($listAssPool as $assOfPool){
		      $resTeam->countSqlElementsFromCriteria(array('idResource'=>$ass->idResource,'idResourceTeam'=>$assOfPool->idResource));
		      if($resTeam){
		        if($ass->leftWork < 0){
		          $assOfPool->leftWork = $assOfPool->leftWork - abs($ass->leftWork);
		          $assOfPool->save();
		        }else{
		          if($assOfPool->leftWork > 0){
		            $assOfPool->leftWork = $assOfPool->leftWork - $diff;
		            $assOfPool->save();
		          }
		        }
		      }
		    }
		  }
		}
		
		if ($ass->leftWork < 0 or $ass->leftWork == null) {
			$ass->leftWork = 0;
		}
		if ($ass->realWork < 0) {
			$ass->realWork = 0;
		}
		$ass->save();
		return $ass;
	}
	public function start() {
		// First, stop all ongoing work
		getSessionUser()->stopAllWork ();
		// Then start current work
		$this->idUser = getSessionUser()->id;
		$this->ongoing = 1;
		$this->ongoingStartDateTime = date ( 'Y-m-d H:i:s' );
		$this->save ();
		// save to database
	}
	
	/**
	 */
	public function stop() {	  
		$start = $this->ongoingStartDateTime;
		$stop = date ( 'Y-m-d H:i:s' );
		$work = workTimeDiffDateTime ( $start, $stop );
		$this->realWork += $work;
		$this->idUser = null;
		$this->ongoing = 0;
		$this->ongoingStartDateTime = null;
		$this->save ();
	}
	
	/**
	 * =========================================================================
	 * Draw a specific item for the current class.
	 * 
	 * @param $item the
	 *        	item. Correct values are :
	 *        	- subprojects => presents sub-projects as a tree
	 * @return an html string able to display a specific item
	 *         must be redefined in the inherited class
	 */
	public function drawSpecificItem($item, $readOnly=false, $included=false) {
		global $print, $comboDetail, $nbColMax;
		$result = "";
		if ($this->refType) {
		  $refObj = new $this->refType ( $this->refId );
		} else {
		  $refObj = new Ticket();
		}
		if ($item == 'run' and ! $comboDetail and ! $this->idle and !$readOnly) {
			if ($print or $this->isAttributeSetToField('realWork', 'readonly')) {
				return "";
			}
			$user = getSessionUser();
			$title = i18n ( 'startWork' );
			if ($this->ongoing) {
				$title = i18n ( 'stopWork' );
			}
			$canUpdate = (securityGetAccessRightYesNo ( 'menu' . $this->refType, 'update', $refObj ) == 'YES');			
			$result .= '<div style="position:absolute; right:2px;width:150px !important';
      if (isNewGui()) $result .= ' text-align: center; text-align: right;">';
      else $result .= ' border: 0px solid #FFFFFF; -moz-border-radius: 15px; border-radius: 15px; text-align: right;">';     
      if ($user->isResource and $canUpdate and $this->id) {
				$result .= '<button id="startStopWork" dojoType="dijit.form.Button" showlabel="true"';
				if (($this->ongoing and $this->idUser != $user->id) or ! $user->isResource) {
					$result .= ' disabled="disabled" ';
				}
				if (isNewGui()) $result .= ' title="' . $title . '" style="vertical-align: middle;min-width:200px; max-width:300px;position:relative;top:3px" class="roundedVisibleButton">';
				else $result .= ' title="' . $title . '" style="vertical-align: middle;" >';
				$result .= '<span>' . $title . '</span>';
				$result .= '<script type="dojo/connect" event="onClick" args="evt">';
				$result .= 'startStopWork("' . (($this->ongoing) ? 'stop' : 'start') . '","' . htmlEncode($this->refType) . '",' . htmlEncode($this->refId) . ');';
				$result .= '</script>';
				$result .= '</button><br/>';
				
			}
			if ($canUpdate and $this->id and property_exists($this,'_spe_dispatch')) {
			  $result.=$this->drawSpecificItem('dispatch', $readOnly, true); // Attention : must be kept call here, to preserve correct position
			}
			if ($this->ongoing) {
			  $result.='<span style="font-size:80%; font-style: italic; color:#a0a0a0;padding-right:7px;">';
				if ($this->idUser == $user->id) {
					// $days = workDayDiffDates($this->ongoingStartDateTime, date('Y-m-d H:i'));
					if (substr ( $this->ongoingStartDateTime, 0, 10 ) != date ( 'Y-m-d' )) {
						// $result .= i18n('workStartedSince', array($days));
						$result .= i18n ( 'workStartedAt', array (
								substr ( $this->ongoingStartDateTime, 11, 5 ) . ' (' . htmlFormatDate ( substr ( $this->ongoingStartDateTime, 0, 10 ) ) . ')' 
						) );
					} else {
						$result .= i18n ( 'workStartedAt', array (
								substr ( $this->ongoingStartDateTime, 11, 5 ) 
						) );
					}
				} else {
					$result .= i18n ( 'workStartedBy', array (
							SqlList::getNameFromId ( 'Resource', $this->idUser ) 
					) );
				}
				$result.='</span>';
			}
			$result .= '</div>';
			return $result;
		} else if ($item == 'dispatch' and ! $comboDetail and ! $this->idle and ! $readOnly and $included) {
			if ($print or $this->isAttributeSetToField('realWork', 'readonly')) {
				return "";
			}
			$user = getSessionUser();
			$canUpdate = (securityGetAccessRightYesNo ( 'menu' . $this->refType, 'update', $refObj ) == 'YES');
			if ($canUpdate and $this->id) {
			  if (isNewGui()) $result .= '<div style="position:absolute; right:0px;top:-34px;text-align:right;">';
			  else $result .= '<div style="position:absolute; right:0px;width:80px !important;top:-24px;text-align:right;">';
				$result .= '<button id="dispatchWork" dojoType="dijit.form.Button" showlabel="true"';
				if (isNewGui()) $result .= ' title="'.i18n('dispatchWork').'" style="max-width:150px;min-width:100px;vertical-align: middle;" class="roundedVisibleButton">';
				else $result .= ' title="'.i18n('dispatchWork').'" style="max-width:77px;vertical-align: middle;">';
				$result .= '<span>' . i18n('dispatchWorkShort') . '</span>';
				$result .= '<script type="dojo/connect" event="onClick" args="evt">';
				$result .= 'dispatchWork("' . htmlEncode($this->refType) . '",' . htmlEncode($this->refId) . ');';
				$result .= '</script>';
				$result .= '</button>';
				$result.='</div>';
			}
		}
		return $result;
	}
	// ============================================================================**********
	// GET VALIDATION SCRIPT
	// ============================================================================**********
	
	/** ==========================================================================
	 * Return the validation sript for some fields
	 * @return the validation javascript (for dojo framework)
	 */
	public function getValidationScript($colName) {
	  $colScript = parent::getValidationScript($colName);
	
	  if ($colName=="plannedWork" or $colName=="realWork") {
	    $colScript .= '<script type="dojo/connect" event="onChange" >';
	    $colScript .= '  var done=dijit.byId("done").get("checked");';
	    $colScript .= '  var real=dijit.byId("WorkElement_realWork").get("value");';
	    $colScript .= '  var planned=dijit.byId("WorkElement_plannedWork").get("value");';
	    $colScript .= '  if (done) {';
	    $colScript .= '    dijit.byId("WorkElement_leftWork").set("value", 0);';
	    $colScript .= '  } else {';
	    $colScript .= '    var left=planned-real;';
	    $colScript .= '    if (left<0) left=0;';
	    $colScript .= '    dijit.byId("WorkElement_leftWork").set("value", left);';
	    $colScript .= '  } ';
	    $colScript .= '  formChanged();';
	    $colScript .= '</script>';
	  }
	  return $colScript;
	
	}
}
?>