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
class IndicatorValue extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;
  public $code;
  public $type;
  public $refType;
  public $refId;
  public $idIndicatorDefinition;
  public $targetDateColumnName;
  public $targetDateTime;
  public $targetValue;
  public $warningTargetDateTime;
  public $warningTargetValue;
  public $warningSent;
  public $alertTargetDateTime;
  public $alertTargetValue;
  public $alertSent;
  public $handled;
  public $done;
  public $idle;
  public $status;
  
  public $_noHistory=true;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
    <th field="refType" width="20%">${name}</th>
    <th field="refId" width="20">${code}</th>
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';

  private static $_fieldsAttributes=array("name"=>"required");
    
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

    /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return the fieldsAttributes
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
    
  static public function addIndicatorValue($def, $obj) {
  	$class=get_class($obj);
  	if (property_exists($obj, 'idStatus')) {
  	  $stat=new Status($obj->idStatus);
  	  if ($stat->isCopyStatus) { // Status "copied" : do not generate indicator alerts
  	  	return;
  	  }
  	}
  	if (property_exists($obj, 'idProject')) {
  	  if ($class=='Project') {
  	    $proj=$obj;
  	  } else {
  	    $proj=new Project($obj->idProject, true);
  	  }
  	  if ($proj->isUnderConstruction) { // Project "under construction" : do not generate indicator alerts
  	    return;
  	  }
  	}
  	if ($def->nameIndicatorable!=$class) {
  		errorLog("ERROR in IndicatorValue::addIndicatorValue() => incoherent class between def ($def->nameIndicatorable) and obj ($class) ");
  		return;
  	}
  	$crit=array('idIndicatorDefinition'=>$def->id, 'refType'=>$class, 'refId'=>$obj->id);
  	$indVal=new IndicatorValue();
  	$lst=$indVal->getSqlElementsFromCriteria($crit, true);
  	if (count($lst)==1) {
  		$indVal=$lst[0];
  	} else if (count($lst)==0) {
  		$indVal=new IndicatorValue();
  		$indVal->idIndicatorDefinition=$def->id;
  		$indVal->refType=$class;
  		$indVal->refId=$obj->id; 		
  		$indVal->warningSent='0';
  		$indVal->alertSent='0';
  	} else {
  		$cpt=count($lst);
      errorLog("ERROR in IndicatorValue::addIndicatorValue() => more than 1 (exactely $cpt) line of IndicatorValue for refType=$class, refId=$obj->id, idIndicatorDefinition=$def->id");
      return;  		
  	}
  	$fld="";
  	$fldVal=null;
  	$sub="";
    $indVal->idle=$obj->idle;
    if (property_exists($obj, 'handled')) {
      $indVal->handled=$obj->handled;
    }
    $targetDateColumnName=$indVal->targetDateColumnName;
    if ($targetDateColumnName and $obj and property_exists($obj, $targetDateColumnName)) {
      $indVal->done=(trim($obj->$targetDateColumnName))?'1':'0';
    } else if (property_exists($obj, 'done')) {
      $indVal->done=$obj->done;
    }
    $indVal->code=$def->codeIndicator;
    $indVal->type=$def->typeIndicator;
  	$ind=new Indicator($def->idIndicator);
  	$indVal->targetDateColumnName=$ind->targetDateColumnName;
  	if ($ind->type=="delay") {
  		$fld=$ind->name;
  		if ($class=='Risk' or $class=='Issue') {
  			$fld=str_replace('Due','End',$fld);
  		}
  		$sub=$class . "PlanningElement";
  		if ( (substr($fld,-7)=='EndDate' or substr($fld,-9)=='StartDate') and property_exists($obj, $sub) ) {
  		  $indVal->targetDateTime=$obj->$sub->$fld;
  		  $indVal->targetDateTime.=(strlen($indVal->targetDateTime)=='10')?" 00:00:00":"";
  	  } else if($ind->code=="YEARLY" && $indVal->targetDateTime) {
  			// Date is already set for a yearly indicator; must be overwritten only if day and month have changed
  			if(substr($indVal->targetDateTime, 5, 5) != substr($obj->$fld, 5, 5)) {
  				$indVal->targetDateTime = $obj->$fld;
  				$indVal->targetDateTime .= (strlen($indVal->targetDateTime) == 10) ? " 00:00:00" : "";
  				// Also reset warning and alert sent states (otherwise can be stuck as true)
  				$indVal->warningSent = 0;
  				$indVal->alertSent = 0;
  			}
  	  } else {
    	  $indVal->targetDateTime=$obj->$fld;
    	  if ($fld=="meetingDate" and property_exists($obj,'meetingStartTime')) $indVal->targetDateTime.=" ".$obj->meetingStartTime;
    	  $indVal->targetDateTime.=(strlen($indVal->targetDateTime)=='10')?" 00:00:00":"";
    	}
  	  if (! trim($indVal->targetDateTime)) {
  	  	if ($indVal->id) {
  	  		$indVal->delete();
  	  	}
  	  	return;
  	  }
  	  if (trim($indVal->targetDateTime)=="00:00:00") $indVal->targetDateTime=null;
  	  $indVal->targetValue=null;
  	  $indVal->warningTargetValue=null;
  	  $indVal->alertTargetValue=null;
  	  if (trim($indVal->targetDateTime)) {
  	  	if ($def->warningValue) $indVal->warningTargetDateTime=addDelayToDatetime($indVal->targetDateTime, (-1)*$def->warningValue, $def->codeWarningDelayUnit);
  	    if ($def->alertValue) $indVal->alertTargetDateTime=addDelayToDatetime($indVal->targetDateTime, (-1)*$def->alertValue, $def->codeAlertDelayUnit);
  	  }
  	  $indVal->checkDates($obj);  	  
  	} else if ($ind->type=="percent") {
  		$indVal->checkPercent($obj,$def);
    } else {
      errorLog("ERROR in IndicatorValue::addIndicatorValue() => unknown indicator type = $ind->type");
    }
    $indVal->save();
  	
  }
  
  public function checkPercent($obj,$def) {
  	$pe=get_class($obj).'PlanningElement';
  	$this->status="";
  	switch ($this->code) {
      case 'PCOVC' :   //PlannedCostOverValidatedCost
      	$this->targetValue=$obj->$pe->validatedCost;
      	$value=$obj->$pe->plannedCost;
      	break;
      case 'PCOAC' :   //PlannedCostOverAssignedCost
      	$this->targetValue=$obj->$pe->assignedCost;
      	$value=$obj->$pe->plannedCost;
      	break;
      case 'PWOVW' :   //PlannedWorkOverValidatedWork
      	$this->targetValue=$obj->$pe->validatedWork;
      	$value=$obj->$pe->plannedWork;
        break;
      case 'PWOAW' :   //PlannedWorkOverAssignedWork
      	$this->targetValue=$obj->$pe->assignedWork;
      	$value=$obj->$pe->plannedWork;
        break;
      case 'RWOVW' :   //RealWorkOverValidatedWork
        $this->targetValue=$obj->$pe->validatedWork;
        $value=$obj->$pe->realWork;
        break;
      case 'RWOAW' :   //RealWorkOverAssignedWork
        $this->targetValue=$obj->$pe->assignedWork;
        $value=$obj->$pe->realWork;
        break;
      case 'CACS' :   // Revenue more than Commands
      	$this->targetValue=$obj->$pe->commandSum;
      	if(!$this->targetValue)$this->targetValue=0.1; // Enter non zero value so that alert is raised even if no command exist
      	$value=$obj->$pe->revenue;
      	break;
  	  case 'CABS' :   // Bill More than Revenue
  		  $this->targetValue=$obj->$pe->revenue;
  		  if(!$this->targetValue)$this->targetValue=0; // Enter zero value so that alert is not raised when not managing Revenue
  		  $value=$obj->$pe->billSum;
  		  break;
  	}
  	$this->warningTargetValue=$this->targetValue*floatval($def->warningValue)/100;
  	$this->alertTargetValue=$this->targetValue*floatval($def->alertValue)/100;
  	$targetValue=floatval($this->targetValue);
  	$value=floatval($value);
  	$this->_currentValue=$value;
  	if ($value>$this->warningTargetValue and $targetValue and $def->warningValue!==null and $def->warningValue!==0) { // V4.5.0 : raise warning only if target value is set
  		if (! $this->warningSent) {
        $this->sendWarning();
        $this->warningSent='1';  
  		}		
  	} else {
  		$this->warningSent='0';  
  	}
    if ($value>$this->alertTargetValue and $targetValue and $def->alertValue!==null and $def->alertValue!==0) { // V4.5.0 : raise alert only if target value is set
    	if (! $this->alertSent) {
        $this->sendAlert();
        $this->alertSent='1';
      }      
    } else {
    	$this->alertSent='0';
    }
    if ($obj->done) {
      if ($value>$targetValue) {
      	$this->status="KO";
      } else {
      	$this->status="OK";
      }   	
    }
   }

  public function checkDates($obj=null) {
    if ($this->type!='delay') {
  		return;
  	}
  	if ($this->idle or ($this->done and $this->code!='DELAY' and $this->code != 'YEARLY')) {
  		return;
  	}
  	$targetControlColumnName='done';
  	$this->status='';
  	switch ($this->code) {
  		case 'IDDT' :   //InitialDueDateTime
  		case 'ADDT' :   //ActualDueDateTime
      case 'IDD' :    //InitialDueDate
      case 'ADD' :    //ActualDueDate
      	if (substr($this->code,-3)=='DDT'){
          $date=date('Y-m-d H:i:s');
      	} else {
      		$date=date('Y-m-d H:i:s');
      	}
        if ($obj and $obj->done) {
          if (substr($this->code,-3)=='DDT'){
          	$date=$obj->doneDateTime;
          } else {
          	$date=$obj->doneDate . " 00:00:00";
          }
          $this->status=($date>$this->targetDateTime)?'KO':'OK';
        }
      	break;
      case 'IED' :    //InitialEndDate
      case 'VED' :    //ValidatedEndDate
      case 'PED' :    //PlannedEndDate
      	$date=date('Y-m-d'). " 00:00:00";
        if ($obj and $obj->done) {
        	$date=$obj->doneDate . " 00:00:00";
        	$this->status=($date>$this->targetDateTime)?'KO':'OK';
        }        
      	break;
      case 'ISD' :    //InitialStartDate
      case 'VSD' :    //ValidatedStartDate
      case 'PSD' :    //PlannedStartDate
      	$date=date('Y-m-d');
        if ($obj and property_exists($obj,'handledDate') and $obj->handled) {
          $date=$obj->handledDate . " 00:00:00";
          $this->status=($date>$this->targetDateTime)?'KO':'OK';
        }
        $pe=(($obj)?get_class($obj):'').'PlanningElement';
        if ($obj and property_exists($obj,$pe) and property_exists($obj->$pe,'realStartDate') and $obj->$pe->realStartDate and $obj->$pe->realStartDate<$date) {
        	$date=$obj->$pe->realStartDate . " 00:00:00";
        	$this->status=($date>$this->targetDateTime)?'KO':'OK';
        }        
        break;
      case 'DELAY' : // name of field to compare on columnName
        $date=date('Y-m-d H:i:s');
        $targetControlColumnName=$this->targetDateColumnName;
        if ($obj and trim($obj->$targetControlColumnName)) {
          if (substr($targetControlColumnName,-8)=='DateTime'){
            $date=$obj->$targetControlColumnName;
          } else if (substr($targetControlColumnName,-4)=='Date'){ 
            $date=$obj->$targetControlColumnName . " 00:00:00";
          } else {
            if ($obj->$targetControlColumnName) {
              return; // $targetControlColumnName is not a date, so if set don't update indicator
            }
          }
          $this->status=($date>$this->targetDateTime)?'KO':'OK';
        }
        break;

  	  // Send indicator every year
  	  case 'YEARLY': // name of field to compare on columnName
  	  	$date = date('Y-m-d H:i:s');
  		  break;
  	}
  	$cancelled=false;
  	if ($obj and property_exists($obj, 'cancelled') and $obj->cancelled) $cancelled=true;
    if (trim($this->warningTargetDateTime) and $date>=$this->warningTargetDateTime and !$this->done and !$cancelled) {
      if (! $this->warningSent ) {
        $this->sendWarning();
      }
      $this->warningSent='1';
    } else if (trim($this->warningTargetDateTime) and $date>$this->warningTargetDateTime and $this->done and !$cancelled) {
      if (! $this->warningSent) {
        $this->sendWarning();
      }
      $this->warningSent='1';
    } else {
      $this->warningSent='0';
    }
    if (trim($this->alertTargetDateTime) and $date>=$this->alertTargetDateTime and !$this->done and !$cancelled) {
      if (! $this->alertSent) {
        $this->sendAlert();
      }
      $this->alertSent='1';
    } else if (trim($this->alertTargetDateTime) and $date>$this->alertTargetDateTime and $this->done and !$cancelled) {
      if (! $this->alertSent) {
        $this->sendAlert();
      }
      $this->alertSent='1';
    } else {
      $this->alertSent='0';
    }

	// If we are sending alerts yearly, we need to update dates
	if($this->code == 'YEARLY') {
		if(!$obj) {
			$obj = new $this->refType($this->refId);
		}

		$def = new IndicatorDefinition($this->idIndicatorDefinition);

		// Update targetDateTime each year
		$targetDateTime = date('Y') . '-' . substr($this->targetDateTime, 5);

		// If the previously calculated targetDateTime is in the past, it means next target date time is actually next year
		if(date('Y-m-d H:i:s') > $targetDateTime) { 
			$targetDateTime = intval(date('Y') + 1) . '-' . substr($this->targetDateTime, 5); 
		}
		
		// Only update if current targetDateTime is not more far in the future
		if($this->targetDateTime <= $targetDateTime) {
			$this->targetDateTime = $targetDateTime;
			
			// Now, we can update the warningTargetDate and alertTargetDate
			$warningTargetDateTime = $this->warningTargetDateTime;
			$alertTargetDateTime = $this->alertTargetDateTime;
			if (trim($this->targetDateTime)) {
				$this->warningTargetDateTime = addDelayToDatetime($this->targetDateTime, (-1) * $def->warningValue, $def->codeWarningDelayUnit);
				$this->alertTargetDateTime = addDelayToDatetime($this->targetDateTime, (-1) * $def->alertValue, $def->codeAlertDelayUnit);
			}

			// Reset sent statuses if it has changed (for example next year)
			if(substr($warningTargetDateTime, 0, 10) != substr($this->warningTargetDateTime, 0, 10)) {
				$this->warningSent = 0;
			}
			if(substr($alertTargetDateTime, 0, 10) != substr($this->alertTargetDateTime, 0, 10)) {
				$this->alertSent = 0;
			}
		}
		
		// Will stop sending alerts when closed (idle)
		$this->save();
	}
  	if (!$obj) $this->save();
  }
  
  public function sendAlert() {
  	$this->send('ALERT');
  	$this->alertSent='1';
  }
  
  public function sendWarning() {
  	$this->send('WARNING');  	
  	$this->warningSent='1';
  }
  
  public function send($type) {
  	$currency=Parameter::getGlobalParameter('currency');
  	$currencyPosition=Parameter::getGlobalParameter('currencyPosition');
    $def=new IndicatorDefinition($this->idIndicatorDefinition);
    $obj=new $this->refType($this->refId);
    $arrayAlertDest=array();
  	if ($def->mailToUser==0 and $def->mailToAccountable==0 and $def->mailToResource==0 
  	and $def->mailToProject==0 and $def->mailToProjectIncludingParentProject==0
    and $def->mailToLeader==0  and $def->mailToContact==0 and $def->mailToAssigned==0
    and $def->mailToManager==0 and $def->mailToOther==0 and $def->mailToSubscribers==0
    and $def->alertToUser==0 and $def->alertToAccountable==0 and $def->alertToResource==0 
  	and $def->alertToProject==0 and $def->alertToProjectIncludingParentProject==0
    and $def->alertToLeader==0  and $def->alertToContact==0 and $def->alertToAssigned==0
    and $def->alertToManager==0 and $def->alertToSubscribers) {
      return false; // exit not a status for mail sending (or disabled) 
    }
    $dest="";
    if ($def->mailToUser or $def->alertToUser) {
      if (property_exists($obj,'idUser')) {
        $user=new User($obj->idUser);
        if ($def->alertToUser) {
        	$arrayAlertDest[$user->id]=$user->name;
        }
        $newDest = "###" . $user->email . "###";
        if ($def->mailToUser and $user->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToResource or $def->alertToResource) {
      if (property_exists($obj, 'idResource')) {
        $resource=new Resource($obj->idResource);
        if ($def->alertToResource and $resource->isUser) {
          $arrayAlertDest[$resource->id]=$resource->name;
        }
        $newDest = "###" . $resource->email . "###";
        if ($def->mailToResource and $resource->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }    
    }
    if ($def->mailToAccountable or $def->alertToAccountable) {
      if (property_exists($obj, 'idAccountable')) {
        $resource=new Resource($obj->idAccountable);
        if ($def->alertToAccountable and $resource->isUser) {
          $arrayAlertDest[$resource->id]=$resource->name;
        }
        $newDest = "###" . $resource->email . "###";
        if ($def->mailToAccountable and $resource->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToProject or $def->mailToLeader or $def->alertToProject or $def->alertToLeader) {
      $aff=new Affectation();
      $crit=array('idProject'=>(get_class($obj)=='Project')?$obj->id:$obj->idProject, 'idle'=>'0');
      $affList=$aff->getSqlElementsFromCriteria($crit, false);
      if ($affList and count($affList)>0) {
        foreach ($affList as $aff) {
          $resource=new Resource($aff->idResource);
          $usr=new User($aff->idResource);
          $canRead=true; // Acces right control is no need : email alets contain only id, name and alert.
          //if ($usr and $usr->id) {
          //  $canRead=(securityGetAccessRightYesNo('menu' . get_class($obj), 'read', $obj, $usr)=='YES');
          //  $canRead=true;
          //}
          if ($canRead and ! $resource->dontReceiveTeamMails) {
	          if ($def->alertToProject and $resource->isUser) {
	          	$arrayAlertDest[$resource->id]=$resource->name;
	          }
	          if ($def->mailToProject) {
	            $newDest = "###" . $resource->email . "###";
	            if ($resource->email and strpos($dest,$newDest)===false) {
	              $dest.=($dest)?', ':'';
	              $dest.= $newDest;
	            }
	          }
          }
          if (($def->mailToLeader or $def->alertToLeader) and ($aff->idProfile or $resource->idProfile)) {
            $profile=($aff->idProfile)?$aff->idProfile:$resource->idProfile;
						$prf=new Profile($profile);
            if ($prf->profileCode=='PL') {
            	if ($def->alertToLeader) {
            		$arrayAlertDest[$resource->id]=$resource->name;
            	}
              $newDest = "###" . $resource->email . "###";
              if ($def->mailToLeader and $resource->email and strpos($dest,$newDest)===false) {
                $dest.=($dest)?', ':'';
                $dest.= $newDest;
              }
            }
          }
        }
      }
    }
    if ($def->mailToManager or $def->alertToManager) {
      if (property_exists($obj,'idProject')) {
        $project=new Project((get_class($obj)=='Project')?$obj->id:$obj->idProject);
        $manager=new Affectable($project->idResource);
        if ($def->alertToManager) {
          $arrayAlertDest[$manager->id]=$manager->name;
        }
        $newDest = "###" . $manager->email . "###";
        if ($def->mailToManager and $manager->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToSubscribers or $def->alertToSubscribers) {
    	$crit=array('refType'=>get_class($obj), 'refId'=>$obj->id);
    	$sub=new Subscription();
    	$lstSub=$sub->getSqlElementsFromCriteria($crit);
    	foreach ($lstSub as $sub) {
    		$resource=new Affectable($sub->idAffectable);
    		if ($def->alertToSubscribers) {
    			$arrayAlertDest[$resource->id]=($resource->name)?$resource->name:$resource->userName;
    		}
    		$newDest = "###" . $resource->email . "###";
    		if ($def->mailToSubscribers and $resource->email and strpos($dest,$newDest)===false) {
    			$dest.=($dest)?', ':'';
    			$dest.= $newDest;
    		}
    	}
    }
    if ($def->mailToAssigned or $def->alertToAssigned) {
      $ass=new Assignment();
      $crit=array('refType'=>get_class($obj),'refId'=>$obj->id);
      $assList=$ass->getSqlElementsFromCriteria($crit);
      foreach ($assList as $ass) {
        $res=new Resource($ass->idResource);
        if ($def->alertToAssigned) {
          $arrayAlertDest[$res->id]=$res->name;
        }
        $newDest = "###" . $res->email . "###";
        if ($res->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToContact or $def->alertToContact) {
      if (property_exists($obj,'idContact')) {
        $contact=new Contact($obj->idContact);
        if ($def->alertToContact and $contact->isUser) {
        	$arrayAlertDest[$contact->id]=$contact->name;
        }
        $newDest = "###" . $contact->email . "###";
        if ($def->mailToContact and $contact->email and strpos($dest,$newDest)===false) {
          $dest.=($dest)?', ':'';
          $dest.= $newDest;
        }
      }
    }
    if ($def->mailToProjectIncludingParentProject or $def->alertToProjectIncludingParentProject) {
      $aff = new Affectation ();
      $proj = new Project($def->idProject);
      $critWhere="idProject in ".transformValueListIntoInClause($proj->getTopProjectList(true));
      $affList = $aff->getSqlElementsFromCriteria ( null, false, $critWhere);
      if ($affList and count ( $affList ) > 0) {
        foreach ( $affList as $aff ) {
          $resource = new Resource ( $aff->idResource );
          if ($def->alertToProjectIncludingParentProject and $resource->isUser) {
            $arrayAlertDest[$resource->id]=$resource->name;
          }
          if ($def->mailToProjectIncludingParentProject) {
            if ($aff->idResource == getSessionUser ()->id) {
              $usr = getSessionUser ();
            } else {
              $usr = new User ( $aff->idResource );
            }
            if (! $resource->dontReceiveTeamMails) {
              $newDest = "###" . $resource->email . "###";
              if ($resource->email and strpos ( $dest, $newDest ) === false) {
                $dest .= ($dest) ? ', ' : '';
                $dest .= $newDest;
              }
            }
          }
        }
      }
    }
    if ($def->mailToOther) {
      if ($def->otherMail) {
        $otherMail=str_replace(';',',', $def->otherMail);
        $otherMail=str_replace(' ',',', $otherMail);
        $split=explode(',',$otherMail);
        foreach ($split as $adr) {
          if ($adr and $adr!='') {
            $newDest = "###" . $adr . "###";
            if (strpos($dest,$newDest)===false) {
              $dest.=($dest)?', ':'';
              $dest.= $newDest;
            }
          }
        }
      }
    }
    if ($dest=="" and count($arrayAlertDest)==0) {
      return false; // exit no addressees 
    }
    $dest=str_replace('###','',$dest);
    
    $paramMailMessage='${type} - ${item} #${id} - ${name}';
    $paramMailMessage.='<BR/>' . i18n('indicator') . ' : ${indicator}'; 
    
    // substituable items
    $item=i18n(get_class($obj));
    $id=$obj->id;
    $name=$obj->name;
    $status=(property_exists($obj, 'idStatus'))?SqlList::getNameFromId('Status', $obj->idStatus):"";
    //gautier #2297
    $nameProject="";
    if(property_exists($obj, 'idProject')){
      $nameProject = SqlList::getNameFromId('Project', $obj->idProject);
    }   
    $indicator=SqlList::getNameFromId('Indicator',$def->idIndicator);
    $target="";
    $warningTarget="";
    $alertTarget="";
    $currentValue="";
    if ($this->type=="delay") {
    	$target=htmlFormatDateTime(trim($this->targetDateTime),false,true);
    	$warningTarget=htmlFormatDateTime(trim($this->warningTargetDateTime),false, true);
    	$alertTarget=htmlFormatDateTime(trim($this->alertTargetDateTime),false, true);
    	$currentValue=htmlFormatDateTime(date('Y-m-d H:i'));
    } else if ($this->type=="percent") {
    	
    	if (substr($this->code,-1)=='W') {
    	  $target=Work::displayWork($this->targetValue) . ' ' . Work::displayShortWorkUnit();
    	  $warningTarget=Work::displayWork($this->warningTargetValue) . ' ' . Work::displayShortWorkUnit();
    	  $alertTarget=Work::displayWork($this->alertTargetValue) . ' ' . Work::displayShortWorkUnit();
    	  if (isset($this->_currentValue)) $currentValue=Work::displayWork($this->_currentValue) . ' ' . Work::displayShortWorkUnit();
    	} else {
    		if ($currencyPosition=='before') {
    			$befCur=$currency;
    			$aftCur='';
    		} else {
    			$befCur='';
    			$aftCur=$currency;
    		}
    		$target=$befCur . ' ' . htmlEncode($this->targetValue) . ' ' . $aftCur;
        $warningTarget=$befCur . ' ' . htmlEncode($this->warningTargetValue) . ' ' . $aftCur;
        $alertTarget=$befCur . ' ' . htmlEncode($this->alertTargetValue) . ' ' . $aftCur;
        if (isset($this->_currentValue)) $currentValue=$befCur . ' ' . htmlEncode($this->_currentValue). ' ' . $aftCur;
    	}
    }
    $arrayFrom=array('${type}','${item}','${id}','${name}','${status}','${indicator}');
    $arrayTo=array($type, $item, $id, $name, $status, $indicator);
    $title=ucfirst(i18n($type)) .' - '. $item . ' #' . $id; 
    
    $message='<table style="margin-top:10px;width:100%">';
    $message.='<tr style="margin-top:10px;"><td colspan="3" style="border:1px solid grey; cursor:pointer;" onClick="gotoElement(\''.get_class($obj).'\','.htmlEncode($obj->id).');">' . htmlEncode($name) . '</td></tr>';
    //gautier #2297
    if($nameProject!=""){
      $message.='<tr style="height:20px"><td width="35%" align="right" >' . i18n('colIdProject') . '</td><td >&nbsp;:&nbsp;</td><td >' . $nameProject . '</td>';
    }
    $message.='<tr style="height:20px"><td width="35%" align="right" >' . i18n('colIdIndicator') . '</td><td>&nbsp;:&nbsp;</td><td >' . $indicator . '</td>';
    $message.='<tr style="height:20px"><td width="35%" align="right">' . i18n('targetValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $target . '</td>';
    $message.=($warningTarget and $type=="WARNING")?'<tr><td width="35%" align="right">' . i18n('warningValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $warningTarget . '</td>':'';
    $message.=($alertTarget and $type=="ALERT")?'<tr><td width="35%" align="right">' . i18n('alertValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $alertTarget . '</td>':'';
    $message.=($currentValue)?'<tr style="height:20px"><td width="35%" align="right">' . i18n('currentValue') . '</td><td>&nbsp;:&nbsp;</td><td>' . $currentValue . '</td>':'';
    $message.='</table><br/>';
    $messageAlert=$message;
    $message.=$obj->getMailDetail();
    $messageMail='<html>' . "\n" .
      '<head>'  . "\n" .
      '<title>' . $title . '</title>' . "\n" .
      '</head>' . "\n" .
      '<body>' . "\n" .
      '<b>' . $title . '</b><br/>' . "\n" .
      $message . "\n" .
      '</body>' . "\n" .
      '</html>';
    $messageMail = wordwrap($messageMail, 70); // wrapt text so that line do not exceed 70 cars per line
    if ($dest!="") {     
      $resultMail=sendMail($dest, $title, $messageMail, $obj);
    }
    if (count($arrayAlertDest)>0) {
      foreach ($arrayAlertDest as $id=>$name) {     	
      	// Create alert
      	$alert=new Alert();
      	$alert->idProject=$obj->idProject;
      	$alert->refType=get_class($obj);
      	$alert->refId=$obj->id;
      	$alert->idIndicatorValue=$this->id;
      	$alert->idUser=$id;
      	$alert->alertType=$type;
      	$alert->message=$messageAlert;
      	$alert->title=$title;
      	$alert->readFlag=0;
      	$alert->alertInitialDateTime=date('Y-m-d H:i:s');
      	$alert->alertDateTime=date('Y-m-d H:i');
      	$alert->idle=0;
      	$alert->save();
      } 
    }
  	
  }
  
  public function getShortDescription() {
  	$result=SqlList::getNameFromId('IndicatorDefinition', $this->idIndicatorDefinition);
  	return $result;
  }
  
  public function getShortDescriptionArray() {
    $result=array('indicator'=>'','target'=>'');
  	$result['indicator']=SqlList::getNameFromId('IndicatorDefinition', $this->idIndicatorDefinition);
    if ($this->type=='delay') {
      $result['target']=$this->targetDateTime;
    }
    return $result;
  }
  
  public function save() {
  	$this->targetDateTime=trim($this->targetDateTime);
  	if ($this->targetDateTime=='00:00' or $this->targetDateTime=='00:00:00') $this->targetDateTime='';
  	$this->warningTargetDateTime=trim($this->warningTargetDateTime);
  	if ($this->warningTargetDateTime=='00:00' or $this->warningTargetDateTime=='00:00:00') $this->warningTargetDateTime='';
  	$this->alertTargetDateTime=trim($this->alertTargetDateTime);
  	if ($this->alertTargetDateTime=='00:00' or $this->alertTargetDateTime=='00:00:00') $this->alertTargetDateTime='';
  	return parent::save();
  }
  
}
?>