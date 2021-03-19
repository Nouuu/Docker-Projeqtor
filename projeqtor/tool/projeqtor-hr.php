<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Salto Consulting
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
/**
 * SPECIALS FUNCTIONS FOR LEAVE SYSTEM
 */

/** used in view/getLeaves.php 
 * return if the current User is an employee or not
 * @return array containing the result of the request
 */
function getIsEmployee(){
    $thisUserId=getSessionUser()->id;
    $crit=array("id" => $thisUserId);
    $resource=new Resource();
    $reqRes=$resource->getSqlElementsFromCriteria($crit);
  
    return $reqRes;
}

/**
 * return an array containing all the leaves of an employee between the parameters $startDateGiven and $endDateGiven, if he is a resource and an employee, else return an empty array
 * @param String $startDateGiven
 * @param String $endDateGiven
 * @return Leave[]
 */
function getUserLeaves($startDateGiven,$endDateGiven, $idEmp=null){
  if (strlen($startDateGiven)==8) { $startDateGiven=substr($startDateGiven,0,4).'-'.substr($startDateGiven,4,2).'-'.substr($startDateGiven,6,2);  }
  if (strlen($endDateGiven)===8) { $endDateGiven=substr($endDateGiven,0,4).'-'.substr($endDateGiven,4,2).'-'.substr($endDateGiven,6,2);  }
    if ($idEmp!=null) {
        if ($idEmp == getSessionUser()->id) {
            $emp = (getSessionUser()->isEmployee?getSessionUser():null);
        } else {
            $critArray=array("id"=>$idEmp, "isEmployee"=>'1');
            $emp = SqlElement::getFirstSqlElementFromCriteria('Employee', $critArray);
            if (isset($emp->_singleElementNotFound)) {
                $emp=null;
            }
        }
    } else {
        $emp = (getSessionUser()->isEmployee?getSessionUser():null);        
    }
    if(!$emp || ! $startDateGiven || ! $endDateGiven || $startDateGiven > $endDateGiven){//if the current user isn't a resource or $startDate/$endDate are not defined
        return -1;
    }
    $lv=new Leave();
    //$clauseWhere to get all the leaves between $startDateGiven and $endDateGiven, if $startDateGiven is between the startDate and endDate of the leave (if the leave began before $startDateGiven...) OR if the startDate of the leave is between $startDateGiven and $endDateGiven
    $clauseWhere="idEmployee = ". $emp->id." AND ((startDate <= '".$startDateGiven."' AND endDate >= '".$startDateGiven."') OR (startDate >= '".$startDateGiven."' and startDate <= '".$endDateGiven."'))";
    $clauseOrderBy = "startDate, endDate ASC";
    $lvList=$lv->getSqlElementsFromCriteria(null, false, $clauseWhere, $clauseOrderBy);
    return $lvList;
}

/**
 * Return the black or white color in function of the color passed in parameter
 * @param string hex : The color in Hexa to opposite
  * @returns string : #FFFFFF or #000000
 */
function oppositeColor($hex) {
    if (!$hex) {return '#000000';}
    if ($hex[0]==='#') {
        $hex = substr($hex,1);
    }    
    // convert 3-digit hex to 6-digits.
    if (strlen($hex) === 3) {
        $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
    }
    if (strlen($hex) !== 6) {
        return '#FFFFFF';
    }
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
        return ($r * 0.299 + $g * 0.587 + $b * 0.114) > 186
            ? '#000000'
            : '#FFFFFF';
}

/**
 * Determine if a resource is a Leave Administrator
 * @param integer $id of resource to test if he is leave administrator. If id is null, test on connected user
 * @return boolean True if it's a leave administrator
 */
function isLeavesAdmin($id=null) {
    if ($id==null) {
        $id = getSessionUser()->id;
    }
    
    $idLeavesAdmin = Parameter::getGlobalParameter("leavesSystemAdmin");
    $result = ($idLeavesAdmin == $id);
    return $result;    
}

/**
 * Get the leaves admin resource object
 * @return \Resource The leave admin resource object
 */
function getLeavesAdmin() {
    $idLeavesAdmin = Parameter::getGlobalParameter("leavesSystemAdmin");
    $leavesAdmin = new Resource($idLeavesAdmin);
    return $leavesAdmin;
}

/**
 * Determine if a resource is a Leave Manager
 * @param integer $id of resource to test if he is manager. If id is null, test on connected user
 * @return boolean True if it's a leaveManager
 */
function isLeavesManager($id=null) {
    if ($id==null) {
        $id = getSessionUser()->id;
    }
    
    $manager = new EmployeeManager($id);
    if ($manager->id==null) { return false;}
    return $manager->isManager();
}

/**
 * Determine if a resource (idManager) is a Leave Manager of a resource (idEmployee)
 * @param integer $idManager : Id of resource to test. If null, resource is the connected user
 * @param integer $idEmployee : Id of resource to test if it's an employee managed by idManager
 *                              If null, return true if manager has employee to manage. Else false
 * @return boolean : True if idManager is a Leave Manager of idEmployee
 */
function isManagerOfEmployee($idManager=null, $idEmployee=null) {
    if ($idManager==null) {
        $idManager = getSessionUser()->id;
    }
    
    $manager = new EmployeeManager($idManager);
    if ($manager->id==null) { return false;}
    
    if ($idEmployee==null) { 
        return $manager->hasManagedEmployees();
    }
    return $manager->isManagerOfEmployee($idEmployee);
    
}

/**
 * Return true if the class and id passed in parameters are leave System object (class and id) dedicated to the leave system
 * ie : is the project dedicated to the leave system.
 * @param String $class = The class to test
 * @param Integer $idI = The id to test
 * @return boolean
 */
function isLeaveMngConditionsKO($class, $idI) {
    $theLeaveClasses = array(
        'Project', 
        'Activity', 
        'EmploymentContract',
        'EmploymentContractType',
    );
    $id = (int) $idI;
    if (!in_array($class,$theLeaveClasses)) { return false; }
    // Can't delete or copy an EmploymentContract if idle=0. Delete is done with deleted resource
    if ($class=="EmploymentContract") { 
        $contract = new EmploymentContract($idI);
        if ($contract->idle==0) { return true;} else {return false;}
    }
    // Can't delete or copy an EmploymentContractType that is the default type (isDefault=1)
    if ($class=="EmploymentContractType") {
        $contractT = new EmploymentContractType($idI);
        if ($contractT->isDefault) { return true;} else {return false;}
    }
    $leaveMngProjectId = Project::getLeaveProjectId();
    // Project and isLeaveMngProject = 1 => Depending on Leave System
    if ($class=="Project" and $leaveMngProjectId == $id) { return true; }
    // Activity and idProject = getLeaveProjectId => Depending on Leave System
    if ($class=="Activity") {
        $act = new Activity($id);
        if ($leaveMngProjectId == $act->idProject) { return true; }
    }

    return false;
}

/**
 * Init (leave System is activ) or purge (leave System is'nt activ) the elements of the leave system
 * @param Boolean $leavesSystemActiv = true if leave System is activ
 */
function initPurgeLeaveSystemElements($leavesSystemActiv) { 
    $leaveNotifiablesList = array("Leave", "EmployeeLeaveEarned", "Workflow", "Status", "LeaveType");
    
    // Parameter is 'leavesSystemActiv'
    // It's value is NO
    if ($leavesSystemActiv == "NO") {        
        // Delete notifiable
        $notifiable = new Notifiable();
        foreach($leaveNotifiablesList as $item) {
            $result = $notifiable->purge("notifiableItem = '".$item."'");
            $lastStatus = getLastOperationStatus($result);
            if ($lastStatus!="OK" and $lastStatus!="NO_CHANGE") {
                traceLog("InitLeaveSystemElement - Purge leave system item notifiable $item");            
                traceLog($result);
                return htmlSetResultMessage(null, 
                                            getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on notifiable's leave system delete", 
                                            false,
                                            "", 
                                            "NotifiableLeave delete",
                                            getLastOperationStatus($result));
            }            
        }
        $clause = "idProject=". ((Project::getLeaveProjectId())?Project::getLeaveProjectId():0);
        // Work corresponding to leaves
        $wk = new Work();
        $result = $wk->purge($clause);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge work where $clause");            
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on work's leave system delete", 
                                        false,
                                        "", 
                                        "Works delete",
                                        getLastOperationStatus($result));
        }    
        // PlannedWork corresponding to leaves
        $wk = new PlannedWork();
        $result = $wk->purge($clause);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge plannedwork where $clause");            
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on  planned work's leave system delete", 
                                        false,
                                        "", 
                                        "Planned Works delete",
                                        getLastOperationStatus($result));
        }    
        // Assignment corresponding to the project dedicated to the leave system
        $ass = new Assignment();
        $result = $ass->purge($clause);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge Assignment where $clause");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on assignment's leave system delete", 
                                        false,
                                        "", 
                                        "Assignments purge",
                                        getLastOperationStatus($result));
        }    
        // Activities corresponding to the project dedicated to the leave system
        $act = new Activity();
        $result = $act->purge($clause);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge Activity where $clause");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on activities leave system delete", 
                                        false,
                                        "", 
                                        "Activities purge",
                                        getLastOperationStatus($result));
        }    
        // Type of scope Activity and code = 'LEAVESYST'
        $tp = new Type();
        $where = "scope='Activity' AND code='LEAVESYST'";
        $result = $tp->purge($where);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge Type where $where");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on type's leave system delete", 
                                        false,
                                        "", 
                                        "Type purge",
                                        getLastOperationStatus($result));
        }    
        // Project dedicated to leave system
        $prj = new Project();
        $prjId = $prj->id;
        $result = $prj->purge("isLeaveMngProject=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge Project where isLeaveMngProject=1");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on project's leave system delete", 
                                        false,
                                        "", 
                                        "Project purge",
                                        getLastOperationStatus($result));
        }
        unsetSessionValue('idLeaveProject');
        unsetSessionValue('leaveProject');
        unsetSessionValue('visibleProjectsList');
        if (sessionValueExists('project')) {
            if (getSessionValue('project') == $prjId) {
                unsetSessionValue('project');
            }
        }
        
        // Planning element of activities and project
        $pl = new PlanningElement();
        $result = $pl->purge($clause);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge PlanningElement where $clause");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on planning's leave system delete", 
                                        false,
                                        "", 
                                        "Planning purge",
                                        getLastOperationStatus($result));
        }    
        // Leaves
        $lv = new Leave();
        $result = $lv->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge Leave");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on leave's leave system delete", 
                                        false,
                                        "", 
                                        "Leaves purge",
                                        getLastOperationStatus($result));
        }    
        // EmployeesManaged
        $empManaged = new EmployeesManaged();
        $result = $empManaged->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge EmployeesManaged");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on Employees Managed's leave system delete", 
                                        false,
                                        "", 
                                        "Employees Managed purge",
                                        getLastOperationStatus($result));
        }    

        // LeaveType
        $lvType = new LeaveType();
        $result = $lvType->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge LeaveType");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on leave type's leave system delete", 
                                        false,
                                        "", 
                                        "Leave Types purge",
                                        getLastOperationStatus($result));
        }    
        // LeaveTypeOfEmploymentContractType
        $lvToCntT = new LeaveTypeOfEmploymentContractType();
        $result = $lvToCntT->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge leaveTypeOfEmploymentContractType");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on LeaveTypeOfEmploymentContractType's leave system delete", 
                                        false,
                                        "", 
                                        "LeaveTypeOfEmploymentContractType purge",
                                        getLastOperationStatus($result));
        }    
        // EmployeeLeaveEarned
        $emplLE = new EmployeeLeaveEarned();
        $result = $emplLE->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge EmployeeLeaveEarned");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on Employee leave earned's leave system delete", 
                                        false,
                                        "", 
                                        "Employees Leaves Earned purge",
                                        getLastOperationStatus($result));
        }    
        // CustomRulesOfEmploymentContractType
        $custRules = new CustomEarnedRulesOfEmploymentContractType();
        $result = $custRules->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge CustomRuleOfEmploymentContractType");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on Custom Rule's leave system delete", 
                                        false,
                                        "", 
                                        "Custom Rules purge",
                                        getLastOperationStatus($result));
        }    
        // EmploymentContractType
        $emplCntT = new EmploymentContractType();
        $result = $emplCntT->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge EmploymentContractType");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on Employment Contract type's leave system delete", 
                                        false,
                                        "", 
                                        "Employment contract types purge",
                                        getLastOperationStatus($result));
        }    
        setSessionValue('idDefaultEmploymentContractType', null);
        // EmploymentContract
        $emplCnt = new EmploymentContract();
        $result = $emplCnt->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge EmploymentContract");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on Employment contract's leave system delete", 
                                        false,
                                        "", 
                                        "Employment contracts purge",
                                        getLastOperationStatus($result));
        }    
        // EmploymentContractEndReason
        $endR = new EmploymentContractEndReason();
        $result = $endR->purge("1=1");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge EmploymentContractEndReason");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on contract end reason's leave system delete", 
                                        false,
                                        "", 
                                        "Employment Contract End Reason purge",
                                        getLastOperationStatus($result));
        }    
        // Update : Resource = set isEmployee = 0 if isEmployee = 1
        $res=new Resource();
        $resTable=$res->getDatabaseTableName();
        $update = "UPDATE $resTable SET isEmployee=0 WHERE isEmployee=1";
        $res = Sql::query($update);
        if (!$res) {
            traceLog("InitLeaveSystemElement - ERROR on QUERY = ".$update);
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on resource's update", 
                                        false,
                                        "", 
                                        "Resource's update",
                                        "ERROR");
        }    
        
        // Type : scope Manager
        $typeM = new Type();
        $result = $typeM->purge("scope='Manager'");
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Purge Type for Manager Scope");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM ERASING ABORTED. Error on Type scope 'Manager' delete", 
                                        false,
                                        "", 
                                        "Type scope 'Manager' purge",
                                        getLastOperationStatus($result));
        }    
        
        
        // Sql::query don't refresh userSession.
        // ==> The sessionUser values must be updated for the connected user if isEmployee was 1
        if (getSessionUser()->isEmployee==1) {
            $user = getSessionUser();
            $user->isEmployee=0;
            $user->idEmploymentContract=null;
            setSessionUser($user);
        }
        return htmlSetResultMessage(null, 
                                    getResultMessage($result)."<br/>LEAVE SYSTEM ERASED", 
                                    false,
                                    "", 
                                    "Leave system erased",
                                    "OK");
                
    } else { // LEAVE SYSTEM IS ACTIV
        // Create the leave system notifiable items
        $notifiable = new Notifiable();
        foreach($leaveNotifiablesList as $item) {
            $notifiable->notifiableItem = $item;
            $notifiable->name = i18n($notifiable->notifiableItem);
            $notifiable->idle=0;
            $notifiable->id=null;
            $result = $notifiable->simpleSave();
            $lastStatus = getLastOperationStatus($result);
            if ($lastStatus!="OK") {
                traceLog("InitLeaveSystemElement - create leave system item notifiable $item");            
                traceLog($result);
                return htmlSetResultMessage(null, 
                                            getResultMessage($result)."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Error on leave system notifiable item creation", 
                                            false,
                                            "", 
                                            "Leave notifiable creation",
                                            getLastOperationStatus($result));
            }            
        }
        // Create a workflow for the leave system
        $wf=SqlElement::getSingleSqlElementFromCriteria('Workflow', array('isLeaveWorkflow'=>'1'));
        if (! $wf->id) {
          $wf=new Workflow(); 
          $wf->name=i18n("colIsLeaveMngWorkflow");
          $wf->sortOrder=999;
          $wf->isLeaveWorkflow=1;
          $wf->save();
          $prf=new Profile();
          $prfListAll=SqlList::getList('Profile');
          $prfAdmin=array();
          $prfList=$prf->getSqlElementsFromCriteria(array('profileCode'=>'ADM'),null,null,'id asc');
          foreach ($prfList as $prfTmp) {
            $prfAdmin[$prfTmp->id]=$prfTmp->id;
          }
          $prfLeader=array();
          $prfList=$prf->getSqlElementsFromCriteria(array('profileCode'=>'PL'),null,null,'id asc');
          foreach ($prfList as $prfTmp) {
            $prfLeader[$prfTmp->id]=$prfTmp->id;
          }
          $prfMember=array();
          $prfList=$prf->getSqlElementsFromCriteria(array('profileCode'=>'TM'),null,null,'id asc');
          foreach ($prfList as $prfTmp) {
            $prfMember[$prfTmp->id]=$prfTmp->id;
          }
          $stList=SqlList::getList('Status');
          foreach($stList as $keySt=>$nameSt) {
            $stRecorded=$keySt;
            break;
          }
          $st=new Status();
          $st->name=i18n('colSubmitted');
          $st->color='#0000ff';
          $st->setSubmittedLeave=1;
          $st->sortOrder=990;
          $st->save();
          $stSubmitted=$st->id;
          $st=new Status();
          $st->name=i18n('colAccepted');
          $st->color='#00ff00';
          $st->setAcceptedLeave=1;
          $st->sortOrder=992;
          $st->save();
          $stAccepted=$st->id;
          $st=new Status();
          $st->name=i18n('colRejected');
          $st->color='#ff0000';
          $st->setRejectedLeave=1;
          $st->sortOrder=994;
          $st->save();
          $stRejected=$st->id;
          foreach($prfListAll as $idPrf=>$namePrf) {
            $wfs=new WorkflowStatus();
            $wfs->idWorkflow=$wf->id;
            $wfs->idStatusFrom=$stRecorded;
            $wfs->idStatusTo=$stSubmitted;
            $wfs->idProfile=$idPrf;
            $wfs->allowed=1;
            $wfs->save();
            if (isset($prfAdmin[$idPrf]) or isset($prfLeader[$idPrf])) {
              $wfs=new WorkflowStatus();
              $wfs->idWorkflow=$wf->id;
              $wfs->idStatusFrom=$stSubmitted;
              $wfs->idStatusTo=$stAccepted;
              $wfs->idProfile=$idPrf;
              $wfs->allowed=1;
              $wfs->save();
              $wfs=new WorkflowStatus();
              $wfs->idWorkflow=$wf->id;
              $wfs->idStatusFrom=$stSubmitted;
              $wfs->idStatusTo=$stRejected;
              $wfs->idProfile=$idPrf;
              $wfs->allowed=1;
              $wfs->save();
            }
          }
        }
        $theWorkFlowId=$wf->id;
        
        // Create the project dedicated to the leave System
        $critType = array("scope" => 'Project', "code" => 'ADM');
        $admProjectType = SqlElement::getFirstSqlElementFromCriteria("Type", $critType);
        if (!isset($admProjectType->id)) {
            $msg = "No Type with scope='Project' and code='ADM'";
            traceLog("InitLeaveSystemElement - Create Project dedicated to Leave System - No Type with scope='Project' and code='ADM'");
            return htmlSetResultMessage(null, 
                                        $msg."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Error on project's leave system creation", 
                                        false,
                                        "", 
                                        "Leave dedicated Project creation",
                                        "ERROR");            
        }
        $prj = new Project();
        $prj->name = strtoupper(i18n('colIdProject')).' - '. strtoupper(i18n('leave'));
        $prj->description = i18n('leaveProjectDescription');
        $prj->idProjectType = $admProjectType->id;
        $prj->idStatus=1;
//        $prj->creationDate = new DateTime();
        $prj->codeType = 'ADM';
        $prj->idOrganization=null;
        $prj->organizationInherited=0;
        $prj->organizationElementary=0;
        $prj->idUser=1;
        $prj->isLeaveMngProject=1;
        $prj->idle=0;
        $prj->sortOrder=null;    
        $prj->ProjectPlanningElement->refName=$prj->name;
        $prj->ProjectPlanningElement->idProject=$prj->id;
        $prj->ProjectPlanningElement->idle=$prj->idle;
        $prj->ProjectPlanningElement->topId=null;
        $prj->ProjectPlanningElement->topRefType=null;
        $prj->ProjectPlanningElement->topRefId=null;
        $prj->ProjectPlanningElement->wbs=null;
        $prj->ProjectPlanningElement->wbsSortable=null;
        $prj->ProjectPlanningElement->idOrganization=$prj->idOrganization;
        $prj->ProjectPlanningElement->organizationInherited=$prj->organizationInherited;
        $prj->ProjectPlanningElement->organizationElementary=$prj->organizationElementary;
        $result = $prj->save();
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Create Project dedicated to Leave System");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Error on project's leave system creation", 
                                        false,
                                        "", 
                                        "Project creation",
                                        getLastOperationStatus($result));
        }    
        
        // The new type of activity for the leaves
        $tp = new Type();
        $tp->name = i18n('leave');
        $tp->scope = 'Activity';
        $tp->sortOrder=100;
        $tp->idWorkflow = $theWorkFlowId;
        $tp->code = 'LEAVESYST';
        $tp->idle=1;
        $result = $tp->simpleSave();
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Create Activity Type dedicated to Leave System");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Can't create Activity Type", 
                                        false,
                                        "", 
                                        "Type creation",
                                        "ERROR");
        }    
        
        // The new types of manager
        $tp = new Type();
        $tp->name = 'Administrative';
        $tp->scope = 'Manager';
        $tp->sortOrder=100;
        $tp->idWorkflow = $theWorkFlowId;
        $tp->code = 'LEAVESYST';
        $result = $tp->simpleSave();
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Create Manager Type 'Administrativ' dedicated to Leave System");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Can't create Manager 'Administrativ' Type", 
                                        false,
                                        "", 
                                        "Type creation",
                                        "ERROR");
        }    
        
        $tp = new Type();
        $tp->name = 'Hierarchical';
        $tp->scope = 'Manager';
        $tp->sortOrder=101;
        $tp->idWorkflow = $theWorkFlowId;
        $tp->code = 'LEAVESYST';
        $result = $tp->simpleSave();
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Create Manager Type 'Hierarchical' dedicated to Leave System");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Can't create Manager 'Hierarchical' Type", 
                                        false,
                                        "", 
                                        "Type creation",
                                        "ERROR");
        }    

        // Create EmploymntContractType :
        //              - name = 'Default Employment Contract Type
        //              - idRecipient = null
        //              - idWorkflow = The leave mng Workflow
        //              - idManagmentType = null
        //              - isDefault = 1
        $emplCntType = new EmploymentContractType();
        $emplCntType->id=null;
        $emplCntType->idle=0;
        $emplCntType->name = i18n("DefaultEmploymentContractType");
        $emplCntType->idRecipient= null;
        $emplCntType->idWorkflow = $theWorkFlowId;
        $emplCntType->idWorkflow = null;
        $emplCntType->idManagementType = null;
        $emplCntType->isDefault = 1;
        $result = $emplCntType->simpleSave();
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElement - Create Default Employment Contrat Type");
            traceLog($result);
            return htmlSetResultMessage(null, 
                                        getResultMessage($result)."<br/>LEAVE SYSTEM INITIALIZATION ABORTED. Can't create the default Employment Contract Type", 
                                        false,
                                        "", 
                                        "Employment Contract Type creation",
                                        "ERROR");
        }            
    }
}

/**
 * Init (leave System is activ) or purge (leave System is'nt activ) the elements of the leave system for the resource passed in parameter
 * @param Resource The resource for which init or purge leave system elements 
 */
function initPurgeLeaveSystemElementsOfResource($resource=null) {
if ($resource==null) {return;}    
    if ($resource->isEmployee == 0) {
        $clausePrjAndRes = "idProject=". Project::getLeaveProjectId(). " and idResource=".$resource->id;
        $clauseRes = "idEmployee=".$resource->id;
        $clauseManager = "idEmployeeManager=".$resource->id;
        // Purge : - Assignment
        //         - Work
        //         - PlannedWork
        //         - Leave
        //         - EmployeeLeaveEarned
        //         - EmploymentContract
        //         - EmployeesManaged
        // Work corresponding to the project dedicated to the leave system
        $work = new Work();
        $result = $work->purge($clausePrjAndRes);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - Purge Work where $clausePrjAndRes");
            traceLog($result);
            return $result;
        }
        // PlannedWork corresponding to the project dedicated to the leave system
        $pWork = new PlannedWork();
        $result = $pWork->purge($clausePrjAndRes);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - Purge Planned Work where $clausePrjAndRes");
            traceLog($result);
            return $result;
        }
        // Assignment corresponding to the project dedicated to the leave system
        $ass = new Assignment();
        $result = $ass->purge($clausePrjAndRes);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - Purge Assignment where $clausePrjAndRes");
            traceLog($result);
            return $result;
        }
        // Leaves
        $lv = new Leave();
        $lv->purge($clauseRes);
        // EmployeeLeaveEarned
        $emplLE = new EmployeeLeaveEarned();
        $result = $emplLE->purge($clauseRes);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - Purge Leave where $clauseRes");
            traceLog($result);
            return $result;
        }
        // EmploymentContract
        $emplCnt = new EmploymentContract();
        $result = $emplCnt->purge($clauseRes);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - EmploymentContract where $clauseRes");
            traceLog($result);
            return $result;
        }
        // EmployeesManaged
        $emplManaged = new EmployeesManaged();
        $result = $emplManaged->purge($clauseRes);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - EmployeesManaged where $clauseRes");
            traceLog($result);
            return $result;
        }
        $result = $emplManaged->purge($clauseManager);
        if (getLastOperationStatus($result)!="OK") {
            traceLog("InitLeaveSystemElementsOfResource - EmployeesManaged where $clauseManager");
            traceLog($result);
            return $result;
        }        
        return $result;
    } else {
        // Leave system is activ
        if (isLeavesSystemActiv()) {
            // Create EmploymentContract with EmploymentContractType that have isDefault = 1 
            //      EmploymntContract :
            //              - name = $resource->name - Initial Contract
            //              - startDate = Now
            //              - endDate = null
            $emplCnt = new EmploymentContract();
            $emplCnt->id=null;
            $emplCnt->idle=0;
            $emplCnt->name = $resource->name." - ".i18n("InitialEmploymentContract");
            $emplCnt->startDate= date("Y-m-d");
            $emplCnt->endDate=null;
            $emplCnt->mission="";
            $emplCnt->idEmployee=$resource->id;
            $emplCnt->idUser= getLeavesAdmin()->id;
            $emplCnt->idEmploymentContractType = EmploymentContractType::getDefaultEmploymentContractTypeId();
            $emplCnt->idStatus=1;
            $emplCnt->idEmploymentContractEndReason=null;
            $result = $emplCnt->simpleSave();
            if (getLastOperationStatus($result)!="OK") {
                traceLog("InitLeaveSystemElementsOfResource - Create EmploymentContract");
                traceLog($result);
                return $result;
            }
            
            $crit=array();
            $lvType=new LeaveType();
            $lvTypeList=$lvType->getSqlElementsFromCriteria($crit);
            $pjLeaveId = Project::getLeaveProjectId();

            foreach($lvTypeList as $leaveType){
                $empLE = new EmployeeLeaveEarned();
                $empLE->idEmployee=$resource->id;
                $empLE->idLeaveType=$leaveType->id;
                $empLE->idUser= getLeavesAdmin()->id;
                $empLE->setLeavesRight();
                $result = $empLE->simpleSave();
                if(strpos($result, "OK")===false){
                    traceLog("InitLeaveSystemElementsOfResource - Create EmployeeLeaveEarned");
                    traceLog($result);
                    return $result;   
                }

                $ass = new Assignment();
                $ass->idProject = $pjLeaveId;
                $ass->idResource = $resource->id;
                $ass->refType = "Activity";
                $ass->refId = $leaveType->idActivity;
                $result=$ass->simpleSave();
                if(strpos($result, "OK")===false){
                    traceLog("InitLeaveSystemElementsOfResource - Create Assignment");
                    traceLog($result);
                    return $result;   
                }
            }                        
            return $result;
        }
    }
    return null;
}

/**
 * used in dynamicDialogCustomEarnedRulesOfEmpContractType.php
 * @return array
 */
function getRulableItems(){
    $result=[];
    $crit = array();
    $rulableItem=new RulableForEmpContractType();
    $rulableItemsList=$rulableItem->getSqlElementsFromCriteria($crit);
    foreach($rulableItemsList as $item){
        $result[$item->rulableItem] = i18n($item->rulableItem);
    }
    return $result;
}

/**
 * used in dynamicDialogCustomEarnedRulesOfEmpContractType.php
 * @return array
 */
function getFieldsOfFirstRulableItem(){
    $crit = array();
    $rulableItem = SqlElement::getFirstSqlElementFromCriteria('RulableForEmpContractType', $crit);
    $fields = getObjectClassTranslatedFieldsList($rulableItem->rulableItem);
    return $fields;
    
}

/**
 * Get the leave system menus as array of Menu object
 * @return Array[Menu] : array of leave system menu object
 */
function getLeavesSystemMenu() {
    if (!sessionValueExists("leavesSystemMenus")) {
        $crit = array ("isLeavesSystemMenu" => '1');
        $orderByLeaves = "sortOrder ASC";
        $menu = new Menu();
        $menusList = $menu->getSqlElementsFromCriteria($crit,false,null,$orderByLeaves,false,true);
        setSessionValue('leavesSystemMenus', $menusList);
    } else {    
        $menusList = getSessionValue("leavesSystemMenus");
    }
    return $menusList;
}

/**
 * Get an array of the Leaves System Menus
 * @return Array[id=>name] : List of Leaves System Menus
 */
function getLeavesSystemMenuList() {
    $menusList = getLeavesSystemMenu();
    $listMenus = array();
    foreach($menusList as $menu) {
        $listMenus[$menu->id] = $menu->name;
    }
    return $listMenus;
}

/**
 * Determine if menu with id passed in parameter is a leave system menu
 * @param type $id : Menu's id to test
 * @return boolean : True if it's a leave system menu
 */
function isLeavesSystemMenu($id=null) {
    if ($id==null) {return false;}
    $listMenus = getLeavesSystemMenuList();
    return (array_key_exists($id, $listMenus));
}

/**
 * Determine if menu with name passed in parameter is a leave system menu
 * @param string $menuName : Menu's name to test
 * @return boolean : True if it's a leave system menu
 */
function isLeavesSystemMenuByMenuName($menuName=null) {
    if ($menuName==null) {return false;}
    $listMenus = getLeavesSystemMenuList();
    foreach($listMenus as $id => $name) {
        if ($menuName == $name) { return true; }
    }
    return false;
}


/**
 * Get the leaves System habilitations
 * @return Array : List of leaves System Habilitation
 */
function getLeavesSystemHabilitation() {
    if (!sessionValueExists("leavesSystemHabilitation")) {
        $lvHab = new LeavesSystemHabilitation();
        $lvHabList = $lvHab->getSqlElementsFromCriteria(null,false,null,null,false,true);
        setSessionValue('leavesSystemHabilitation', $lvHabList);        
    } else {
        $lvHabList = getSessionValue("leavesSystemHabilitation");
    }
    return $lvHabList;
}

/**
 * Get the leaves System habilitations order by the corresponding sortOrder's menu
 * @return Array : List of leaves System Habilitation
 */
function getLeavesSystemHabilitationSortByOrderMenu() {
    $habilitationsList = getLeavesSystemHabilitation();
    $menusList = getLeavesSystemMenu();
    $habilitationsSortByOrderMenu=null;
    foreach($menusList as $menu) {
        foreach($habilitationsList as $hab) {
            // No real habilitation for menus that are'nt linked to object or item
            if ($hab->menuName == $menu->name) {
                $habilitationsSortByOrderMenu[]=$hab;
            }
        }
    }
    return $habilitationsSortByOrderMenu;
}

/**
 * Get the menus that are in leaves System habilitations (LeavesSystemHabilitation) 
 * @return Array : List of menu's names
 */
function getMenusInLeavesHabilitation() {
    if (!sessionValueExists("leavesSystemHabilitation")) {
        $lvHab = new LeavesSystemHabilitation();
        $lvHabList = $lvHab->getSqlElementsFromCriteria(null,false,null,null,false,true);
        setSessionValue('leavesSystemHabilitation', $lvHabList);        
    } else {
        $lvHabList = getSessionValue("leavesSystemHabilitation");
    }
    
    $list = array();
    foreach($lvHabList as $hab) {
        array_push($list, $hab->menuName);
    }
    
    return $list;
}


/**
 * Return an array of menus that are Leave System Menu and the access Rights
 * @param boolean $restrictToExistingMenu : If true, gives only habilitation for menus existing in table Menu
 * @return Array = menuName => array(Access Type =>  List right Type)
 *                  Access Type = menu = AccÃ¨s to menu and screen
 *                                read = Read the object linked by menu
 *                                create
 *                                update
 *                                delete
 *                  Right Type = A : Leave Administrator
 *                               M : Leave Manager
 *                               m : Leave Manager of owner (idEmployee)
 *                               E : Employee
 *                               O : Owner of object
 *                               S : user is idEmployee of object
 */
function leavesSystemHabilitationList($restrictToExistingMenu=false) {
    $menusList = getLeavesSystemMenu();    
    $lvHabList = getLeavesSystemHabilitation();
            
    $list = array();

    foreach ($menusList as $theMenu) {
        $list[$theMenu->name]["id"] = $theMenu->id;
        $list[$theMenu->name]["type"] = $theMenu->type;                            
        // For a Menu that is a menu (not access to object or to item)
        if ($theMenu->type=="menu") {
            // Force Habilitation : view = "AME" - read,create,update,delete=" "
            $list[$theMenu->name]["menu"] = "AME";
            $list[$theMenu->name]["read"] = " ";
            $list[$theMenu->name]["create"] = " ";
            $list[$theMenu->name]["update"] = " ";
            $list[$theMenu->name]["delete"] = " ";
        } else {
            // Take habilitation if exists
            $theHab = null;
            foreach($lvHabList as $hab) {
                if ($hab->menuName == $theMenu->name) {
                    $theHab = $hab;
                    break;
                }
            }
            // Habilitation for menu exists
            if (!is_null($theHab)) {
                $list[$theMenu->name]["menu"] = $theHab->viewAccess;
                $list[$theMenu->name]["read"] = $theHab->readAccess;
                $list[$theMenu->name]["create"] = $theHab->createAccess;
                $list[$theMenu->name]["update"] = $theHab->updateAccess;
                $list[$theMenu->name]["delete"] = $theHab->deleteAccess;                
            }
            // Habilitation for menu does'nt exist
            else {                
                $list[$theMenu->name]["menu"] = " ";
                $list[$theMenu->name]["read"] = " ";
                $list[$theMenu->name]["create"] = " ";
                $list[$theMenu->name]["update"] = " ";
                $list[$theMenu->name]["delete"] = " ";
            }
        }
    }
    
    // No restriction on menu
    if (!$restrictToExistingMenu) {
        foreach($lvHabList as $hab) {
            // Add the habilitation if not exists
            if (!array_key_exists($hab->menuName, $list)) {
                $list[$hab->menuName]["id"] = null;
                $list[$hab->menuName]["type"] = null;                            
                $list[$hab->menuName]["menu"] = $hab->viewAccess;
                $list[$hab->menuName]["read"] = $hab->readAccess;
                $list[$hab->menuName]["create"] = $hab->createAccess;
                $list[$hab->menuName]["update"] = $hab->updateAccess;
                $list[$hab->menuName]["delete"] = $hab->deleteAccess;                                
            }
        }
    }
    return $list;
}

function leavesSystemMenuI18nList() {
    $leaveMenusList = getLeavesSystemMenuList();
    $list=array();
    foreach ($leaveMenusList as $id => $name) {
        array_push($list, i18n($name));
    }
    return $list;    
}

/**
 * Determine if a HR Menu is to show or not
 * Don't show when : 
 *     - parameter leavesSystemActiv = NO
 * @param String $menuName : The menu with this name to test
 * @return boolean True if the menu passed in parameter is to show
 */
function showLeavesSystemMenu($menuName) {
    $leaveMenusList = leavesSystemHabilitationList(true);
    $user = getSessionUser();
    $isLeavesAdmin = isLeavesAdmin();
    $isEmployee = (($user->isEmployee==1)?true:false);    
    $isManager = isLeavesManager();
    $isManagerOfEmployees = isManagerOfEmployee();
    // Leave System is activ
    if (isLeavesSystemActiv()) {
        // Menu is a leave menu
        if (array_key_exists($menuName, $leaveMenusList)) {
            $theMenuLeaveRight = $leaveMenusList[$menuName]['menu'];
            // Menu is an admin menu AND user is an admin
            if (strpos($theMenuLeaveRight,"A")!==false and $isLeavesAdmin) 
                {return true;}
            // Menu is a Manager Menu AND user is a Manager    
            elseif (strpos($theMenuLeaveRight,"M")!==false and $isManager) 
                {return true;}
            // Menu is an Employee Menu AND user is an Employee
            elseif (strpos($theMenuLeaveRight,"E")!==false and $isEmployee)
                {return true;}
            // Menu is a manager of Employees menu AND user is an manager of Employee
            elseif (strpos($theMenuLeaveRight,"m")!==false and $isManagerOfEmployees)
                {return true;}
            // Menu is for Owner or creator => Don't take care
            // If it's owner or creator, that is to say that have access by other way (Admin, Employee, Manager, Manager Of Employee
            elseif (strpos($theMenuLeaveRight,"O")!==false or strpos($theMenuLeaveRight,"S")!==false)
                {return false;}
            // Other case    
            else {return false;}    
            // Menu is an manager menu AND user is an
        } else {
            // Menu is'nt a leave menu
            return true;
        }
    // Leave System is'nt activ
    } else {
        // Menu is a leave menu => Don't show
        if (array_key_exists($menuName, $leaveMenusList)) {return false;} else {return true;}
    }       
}

/**
 * ===========================================================================
 * Get the Leave System access right for a menu and an access type
 *
 * @param string $menuName The name of the menu; should be 'menuXXX'
 * @param string $accessType requested
 *          access type : 'menu', 'read', 'create', 'update', 'delete'
 * @param Object $obj The object for which retrieve access right
 * @param User $user The user for which retrieve access right
 * @param Boolean $yesNo See returnIf true, returns YES, NO or NOTALEAVEELEMENT. Else return NO, OWN, ALL, NOTALEAVEELEMENT, _XXXX_
 * @return the access right. 
 *              'NOTALEAVEELEMENT' => If it's not a leave system element
 *              '_XXXX_' => If it's inherited by the standard access right - XXXX is the inherited Class Name
 *         If $yesNo = True
 *              'NO' => no access
 *              'YES' => Access
 *          Else
 *              'NO' => no access
 *              'OWN' => element of creator, employee or manager of employee
 *              'ALL' => any element
 */
function securityGetLeaveSystemAccessRight($menuName, $accessType, $obj, $user, $yesNo=false) {
if (substr($menuName,0,4)!='menu') {
    return "NO";
}    
$class = substr($menuName,4);

$isDebug = false;
if ($isDebug) {
traceLog("===================================================================================");    
traceLog("securityGetLeave - menuName = $menuName - accessType = $accessType - yesNo = $yesNo"); 
traceLog("securityGetLeave - Obj");
traceLog($obj);
}
    if (!in_array($menuName, getMenusInLeavesHabilitation())) {
//    if (!array_key_exists($menuName, leavesSystemHabilitationList())) {
if ($isDebug) {
traceLog("NOTALEAVEELEMENT");
}
        return 'NOTALEAVEELEMENT';
    }
        
    // Leave system inactiv
    if (!isLeavesSystemActiv()) {return "NO";}

    // The user
    if (! $user) {
      if (! sessionUserExists()) {
        global $maintenance;
        if ($maintenance) {
          return ($yesNo?'YES':'ALL');
        } else {
          return 'NO'; //return 'NO'; // This is a case that should not exist unless hacking attempt or use of F5
        }
      } else {
        $user = getSessionUser();
      }
    } 

    // What kind of user
    $isLeavesAdmin = isLeavesAdmin();
    $isEmployee = ($user->isEmployee==1?true:false);    
    $isManager = isLeavesManager();
    
if ($isDebug) {traceLog("securityGetLeave - UserId = $user->id");};

    // Update with $obj->id at null => New Object => Creation
    if ($accessType == 'update' and $obj and $obj->id == null) {
        $accessType = "create";
    }
               
    // Load content of Leave System Right
    $leaveHabilitation = leavesSystemHabilitationList();

    // For CustomEarnedRulesOfEmploymentContractType and leaveTypeOfEmploymentContractType
    // ==> If they haven't menu, access rights are thoses of EmploymentContractType
    if ( $menuName == "menuCustomEarnedRulesOfEmploymentContractType" or
         $menuName == "menuLeaveTypeOfEmploymentContractType") {
        if (!isLeavesSystemMenuByMenuName($menuName)) {
            $menuName = "menuEmploymentContractType";
        }
    }

    // For EmployeesManaged
    // ==> If it haven't menu, access rights are thoses of EmployeeManager
    if ( $menuName == "menuEmployeesManaged") {
        if (!isLeavesSystemMenuByMenuName($menuName)) {
            $menuName = "menuEmployeeManager";
        }
    }
        
    // The rights (vCRUD) of menuName
    $rights = $leaveHabilitation[$menuName];
    
    // Special case of Employee or EmployeeManager : It's in fact a Resource
    // ==> Can't create or delete (It's done throw Resource)
    if ($menuName=="menuEmployee" or $menuName=="menuEmployeeManager") {
if ($isDebug) {traceLog("securityGetLeave - Special case of Employee or EmployeeManager");}        
        $rights['create']="";
        $rights['delete']="";
    }
    
    // The profiles allowed for access Type
    $profileRights = $rights[$accessType];
        
if ($isDebug) {traceLog("securityGetLeave - actionRights = $profileRights");};
    
    // =========================================
    // Cases (AME) = Admin - Manager - Employee
    // =========================================
    if (strpos($profileRights, "A")!==false or strpos($profileRights, "M")!==false or strpos($profileRights, "E")!==false) {
if ($isDebug) {traceLog('securityGetLeave - In AME cases');}
        // Admin
        if ($isLeavesAdmin and strpos($profileRights,"A")!==false) {
            $result = ($yesNo?'YES':'ALL');
if ($isDebug) {traceLog("securityGetLeave - Admin ET A - result = $result");}
            return $result;
        }
        // Manager
        elseif ($isManager and strpos($profileRights,"M")!==false) {
            $result = ($yesNo?'YES':'ALL');
if ($isDebug) {traceLog("securityGetLeave - MANAGER ET M - result = $result");}
            return $result;            
        }
        // Employee
        elseif ($isEmployee and strpos($profileRights,"E")!==false) {
            $result = ($yesNo?'YES':'ALL');
if ($isDebug) {traceLog("securityGetLeave - EMPLOYEE ET E - result = $result");}
            return $result;
        }
    }
    
    // =========================================
    // Case (m) : Manager of Employee
    // =========================================
    if (strpos($profileRights,"m")!==false) {
if ($isDebug) {traceLog('securityGetLeave - In m case');}
        // No object => return NO or OWN
        if (!$obj) { 
            $result = ($yesNo?'NO':'OWN');
if ($isDebug) {traceLog("securityGetLeave - obj null - result = $result");}
            return $result;            
        }
        $class = get_class($obj);
        $idEmployee=null;
        // If class is Employee, the reference is id
        if ($class=="Employee") {
            $idEmployee = $obj->id;
        }
        // Else, the reference is idEmployee if it exists
        elseif (property_exists($class, "idEmployee")) {
            $idEmployee=$obj->idEmployee;
        }
        if ($idEmployee!=null and isManagerOfEmployee($user->id, $idEmployee)) {
            $result = ($yesNo?'YES':'ALL');
if ($isDebug) {traceLog("securityGetLeave - Is manager of employee - result = $result");}
            return $result;
        }
    }
    
    // =========================================
    // Case (O) : OWN - user = idUser
    // =========================================
    if (strpos($profileRights, "O")!==false) {
        // No object => Can't determine owner => return NO
if ($isDebug) {traceLog('securityGetLeave - In O case');}
        if (!$obj) { 
            $result = ($yesNo?'NO':'OWN');
if ($isDebug) {traceLog("securityGetLeave - obj null - result = $result");}
            return $result;            
        }
if ($isDebug) {traceLog('securityGetLeave - obj not null');}
        $class = get_class($obj);
if ($isDebug) {traceLog("securityGetLeave - obj class =$class");}
        // Case of class "Employee
//        if ($class=="Employee") {
//            if ($user->id == $obj->id) { 
//                $result = ($yesNo?'YES':'OWN');
//if ($isDebug) {traceLog("securityGetLeave - userId = objId - result =$result");}
//                return $result;                
//            }            
//        }
        // In leave Management = Owner is idUser
        if (property_exists($class, "idUser")) {
if ($isDebug) {traceLog("securityGetLeave - obj idUser exist - userId=$user->id - objUserId=$obj->idUser");}
            if ($user->id == $obj->idUser) {
                $result = ($yesNo?'YES':'OWN');
if ($isDebug) {traceLog("securityGetLeave - userId = objUserId - result =$result");}
                return $result;
            }
        }
//        if (property_exists($class, "idEmployee")) {
//if ($isDebug) {traceLog("securityGetLeave - obj idEmployee exist - userId=$user->id - objEmployeeId=$obj->idEmployee");}
//            if ($user->id == $obj->idEmployee) { 
//                $result = ($yesNo?'YES':'OWN');
//if ($isDebug) {traceLog("securityGetLeave - userId = objIdEmployee - result =$result");}
//                return $result;                
//            }
//        }
    }
    
    // =========================================
    // Case (S) : SELF - user = idEmployee
    // =========================================
    if (strpos($profileRights, "S")!==false) {
        // No object => Can't determine employee => return NO
if ($isDebug) {traceLog('securityGetLeave - In S case');}
        if (!$obj) { 
            $result = ($yesNo?'NO':'OWN');
if ($isDebug) {traceLog("securityGetLeave - obj null - result = $result");}
            return $result;            
        }
if ($isDebug) {traceLog('securityGetLeave - obj not null');}
        $class = get_class($obj);
if ($isDebug) {traceLog("securityGetLeave - obj class =$class");}
        // Case of class "Employee
        if ($class=="Employee") {
            if ($user->id == $obj->id) { 
                $result = ($yesNo?'YES':'OWN');
if ($isDebug) {traceLog("securityGetLeave - userId = objId - result =$result");}
                return $result;                
            }            
        }
        // In leave Management = Self is idEmployee
        if (property_exists($class, "idEmployee")) {
if ($isDebug) {traceLog("securityGetLeave - obj idEmployee exist - userId=$user->id - objEmployeeId=$obj->idEmployee");}
            if ($user->id == $obj->idEmployee) { 
                $result = ($yesNo?'YES':'OWN');
if ($isDebug) {traceLog("securityGetLeave - userId = objIdEmployee - result =$result");}
                return $result;                
            }
        }
    }

if ($isDebug) {traceLog("securityGetLeave - END - No Case found - result =NO");}
    return "NO";
}

/**
 * Get the first profile with a profileCode equals to ADM
 * @return Profile : Return the profile or null if not profile with ADM profileCode found
 */
function getFirstADMProfile() {
    $crit = array("idle"=>"0", "profileCode"=>"ADM");
    $admProfile = SqlElement::getFirstSqlElementFromCriteria("Profile", $crit);
    if (isset($admProfile->id)) {
        return $admProfile;
    }
    return null;
}

/**
 * Get the actual contractual values.
 *      => For employee contract that is activ (idle=0. Only one)
 *          => For contract type associated to the contract and the leave's leave type
 *              => Object LeaveTypeOfEmploymentContractType with idle = 0
 * @param integer $idEmployee : The employee for which retrieve the contractual values
 * @param integer $idLeaveType : The Leave Type for which retrieve the contractual values
 * @param EmploymentContract $empContract : By reference - Return the activ contract
 * @return LeaveTypeOfEmploymentContractType : Object contening the values. Null if not found
 */
function getActualLeaveContractualValues($idEmployee=null, $idLeaveType=null, &$empContract=null) {
    if ($idEmployee==null or $idLeaveType==null) {
        return null;
    }

    $critContract=array("idEmployee"=>$idEmployee,"idle"=>"0");

    $empContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract",$critContract);
    if (!isset($empContract->id)) {
        $empContract = null;
        return null;
    }
    
    $critLvTypeOfEmpContractType = array(
                "idLeaveType"=>$idLeaveType, 
                "idEmploymentContractType"=>$empContract->idEmploymentContractType, 
                "idle"=>"0"
                );
    
    $lvTypeOfEmpContractType = SqlElement::getFirstSqlElementFromCriteria("LeaveTypeOfEmploymentContractType",$critLvTypeOfEmpContractType);
    if (!isset($lvTypeOfEmpContractType->id)) {
        return null;
    }

    return $lvTypeOfEmpContractType;    
}

function getActualLeaveConstractCustomQuantity($idLeaveType=null,$contract=null, $idEmployee=null) {
    if ($contract==null or $idLeaveType==null) { return 0;}
    $critCustomRightContractType = array(
                "idLeaveType"=>$idLeaveType, 
                "idEmploymentContractType"=>$contract->idEmploymentContractType, 
                "idle"=>"0"
                );
    
    $customRightContractType = SqlElement::getFirstSqlElementFromCriteria("CustomEarnedRulesOfEmploymentContractType",$critCustomRightContractType);
    if (!isset($customRightContractType->id)) {
        return 0;
    }
    $customQuantity = getCustomLeaveEarnedQuantity($customRightContractType, $idEmployee);
    return $customQuantity;
}

/**
 * Get the actual contractual values for an employee.
 *      => For employee contract that is activ (idle=0. Only one)
 *          => For contract type associated to the contract and the leave's leave type
 *              => Object LeaveTypeOfEmploymentContractType with idle = 0
 * @param integer $idEmployee : The employee for which retrieve the contractual values
 * @return LeaveTypeOfEmploymentContractType[] : Array of Objects contening the values. Null if not found
 */
function getActualLeaveContractualValuesForAllLeaveTypes($idEmployee=null) {
    if ($idEmployee==null) {
        return null;
    }

    $critContract=array("idEmployee"=>$idEmployee,"idle"=>"0");

    $empContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract",$critContract);
    if (!isset($empContract->id)) {
        $empContract = null;
        return null;
    }
    
    $critLvTypeOfEmpContractType = array(
                "idEmploymentContractType"=>$empContract->idEmploymentContractType, 
                "idle"=>"0"
                );
    
    $lvTypeOfEmpContractType = new LeaveTypeOfEmploymentContractType();
    $result = $lvTypeOfEmpContractType->getSqlElementsFromCriteria($critLvTypeOfEmpContractType);

    return $result;    
}

/**
 * Get the actual Leave Earned for an employee and a leave type.
 *      Actual Leave Earned conditions
 *          => Leave Earned not closed
 *          => Current Date is between Leave Earned start date and Leave Earned end date
 * @param integer $idEmployee : The employee for which retrieve the contractual values
 * @param integer $idLeaveType : The Leave Type for which retrieve the contractual values
 * @return EmployeeLeaveEarned : Object contening the values. Null if not found
 */
function getActualEmployeeLeaveEarned($idEmployee=null, $idLeaveType=null) {
    if ($idEmployee==null or $idLeaveType==null) {
        return null;
    }
    $currentDate = new DateTime();
    $currentDateString = $currentDate->format("Y-m-d");
    $clauseWhere = "idle=0 AND idEmployee=$idEmployee AND idLeaveType=$idLeaveType";
    $clauseWhere .= " AND '$currentDateString' >= startDate AND '$currentDateString' <= endDate";
    
    $emplLeaveEarned = new EmployeeLeaveEarned();
    $emplLeaveEarnedList = $emplLeaveEarned->getSqlElementsFromCriteria(null,false,$clauseWhere);
    if ($emplLeaveEarnedList) {
        return $emplLeaveEarnedList[0];
    }
    return null;
}

/**
 * Get the actual Leaves Earned for an employee.
 *      Actual Leave Earned conditions
 *          => Leave Earned not closed
 *          => Current Date is between Leave Earned start date and Leave Earned end date
 * @param integer $idEmployee : The employee for which retrieve the contractual values
  * @return EmployeeLeaveEarned[] : Array of Objects contening the values. Null if not found
 */
function getActualEmployeeLeavesEarnedForAllLeaveTypes($idEmployee=null) {
    if ($idEmployee==null or $idLeaveType==null) {
        return null;
    }
    $currentDate = new DateTime();
    $currentDateString = $currentDate->format("Y-m-d");
    $clauseWhere = "idle=0 AND idEmployee=$idEmployee ";
    $clauseWhere .= " AND '$currentDateString' >= startDate AND '$currentDateString' <= endDate";
    
    $emplLeaveEarned = new EmployeeLeaveEarned();
    $emplLeaveEarnedList = $emplLeaveEarned->getSqlElementsFromCriteria(null,false,$clauseWhere);
    if ($emplLeaveEarnedList) {
        return $emplLeaveEarnedList[0];
    }
    return null;
}

/**=========================================================================
 * return a boolean to know if the leaveType of this employeeLeaveEarned is Anticipated or not 
 * (the function go look into the lvTypeOfEmpContractType (attribute isAnticipated) of the leaveType to know if it is indeed anticipated) 
 * @param int $idEmployee
 * @param int $idLeaveType
 * @return boolean True if is Anticipated, else false
 */
function isAnticipatedOfLvType($idEmployee,$idLeaveType){
    $isAnticipated=false;

    if($idEmployee==null || $idLeaveType==null){
        return false;
    }
    $lvTypeOfEmpContractType = getActualLeaveContractualValues($idEmployee,$idLeaveType);
    
  if($lvTypeOfEmpContractType){
      if($lvTypeOfEmpContractType->isAnticipated==1){
          $isAnticipated = true;
      }
  }
  return $isAnticipated;
} 

/**
 * Get the number of anticipated days allowed for the employee and the leave type
 * @param integer $idEmployee
 * @param integer $idLeaveType
 * @param boolean $actual : If true, take in count, left of actual leave Earned
 * @return integer The number of anticipated days allowed for the employee and the leave type.
 *                 999999 if infinite
 */
function getNbAnticipatedAllowedFormEmployeeAndLeaveType($idEmployee,$idLeaveType, $actual=false) {
    if (!isAnticipatedOfLvType($idEmployee, $idLeaveType)) {
        // Not Allowed => return 0;
        return 0;
    }
    
    // The actual leave Earned to retrieve left
    $emplLeaveEarned = getActualEmployeeLeaveEarned($idEmployee, $idLeaveType);
    // Not found => Infinite
    if ($emplLeaveEarned==null) {
        return 999999;
    }
    
    // To retrieve the earned quantity for the next period 
    $right = $emplLeaveEarned->getLeavesRight(true,false);
    // No quantity allowed if earned quantity for the next period is null
    if ($right['quantity']==null) {
        return 0;
    }
    
    if ($emplLeaveEarned->leftQuantity<0 and $actual) {
        // If left of actual leave Earned is negative, that is to say, anticiped leaves were yet taken
        //      ==> anticipated quantity = max between 0 and earned quantity for the next period plus left 
        return max(0,$right['quantity']+$emplLeaveEarned->leftQuantity);        
    } else { // Else, allowed quantity = earned quantity for the next period
        return $right['quantity'];
    }
}


/**=========================================================================
 * return a boolean to know if the leaveType of this employeeLeaveEarned is Justifiable or not 
 * (the function go look into the lvTypeOfEmpContractType (attribute isAnticipated) of the leaveType to know if it is indeed anticipated) 
 * @param int $idEmployee
 * @param int $idLeaveType
 * @return boolean True if is Justifiable, else false
 */
function isJustifiableOfLvType($idEmployee,$idLeaveType){
    $isJustifiable=false;

    if($idEmployee==null || $idLeaveType==null){
        return false;
    }
    $lvTypeOfEmpContractType = getActualLeaveContractualValues($idEmployee,$idLeaveType);
    
  if($lvTypeOfEmpContractType){
      if($lvTypeOfEmpContractType->isJustifiable==1){
          $isJustifiable = true;
      }
  }
  return $isJustifiable;
} 

/**
 * Get the custom quantity for a Custom Earned Rule
 * @param CustomEarnedRulesOfEmploymentContractType $custom : The custom Earned Rules object for which get custom quantity
 * @param integer $idEmployee : The employee's id for which get Custom Leave Earned Quantity. If null, if connected user
 * @return int : The custom quantity
 */
function getCustomLeaveEarnedQuantity($custom=null, $idEmployee=null) {
    if ($custom==null) {return 0;}
    
    if ($idEmployee==null) {
        $idEmployee = getSessionUser()->id;
    }
    
    $rule = $custom->rule;
    $tables = $custom->transformWordsInArrayClassField($rule);
    foreach($tables as $tbl => $fld) {
        $table = $tbl;        
    }
    $obj = new $table();
    $rule = str_replace($table.'.', '', $rule);
    $rule = str_replace('${', '', $rule);
    $rule = str_replace('}', '', $rule);
    
    $clauseWhere = str_replace($table.'.', '', $custom->whereClause);
    $clauseWhere = str_replace('${', '', $clauseWhere);
    $clauseWhere = str_replace('}', '', $clauseWhere);
    if ($table=="Employee") {
        $clauseWhere .= ($clauseWhere==""?"":" AND ")."id=$idEmployee ";
    } else {
        if (property_exists($table, "idEmployee")) {
            $clauseWhere .= ($clauseWhere==""?"":" AND ")."idEmployee=$idEmployee ";
        } else {
            return 0;
        }
    }
    if (property_exists($table, "idLeaveType")) {
        $clauseWhere .= "AND idLeaveType=$custom->idLeaveType ";        
    }
    if (property_exists($table, "idEmploymentContractType")) {
        $clauseWhere .= "AND idEmploymentContractType=$custom->idEmploymentContractType ";        
    }
    
    $query = "SELECT $rule as 'value' FROM ". $obj->getDatabaseTableName(). " WHERE ". $clauseWhere;
    $result = Sql::query($query);
    if (! $result) {
        return 0;
    }
    if (Sql::$lastQueryNbRows > 0) {
      $obj = new stdClass();
      $line = Sql::fetchLine($result);
      while ($line) {
        $objectsQuery[]=$line;
        $line = Sql::fetchLine($result);
      }
    } else {
        $objectsQuery=array();
    }
    $theValue = 0;
    foreach($objectsQuery as $obj => $attr) {
        if (is_numeric($attr['value'])) {            
            $theValue += $attr['value'];
        }
    }
    $quantity = $theValue*$custom->quantity;
    return $quantity;
}

function setLeaveEarnedForContractType($obj=null) {
    if ($obj==null) {
        $result = htmlSetResultMessage( null, 
                                        " No object in parameter in Update Of Leave Earned For Calculation", 
                                        false,
                                        "", 
                                        "CalculationOfNewLeaveEarned",
                                        "KO"
                                  );
        return $result;                              
    }
    $result="";
    // Retrieve contract type
    $contractType = new EmploymentContractType($obj->idEmploymentContractType);
    // Retrieve impacted contracts
    $crit = array("idle" => "0",
                  "idEmploymentContractType"=> $contractType->id
                 );
    $contract = new EmploymentContract();
    $contractsList = $contract->getSqlElementsFromCriteria($crit);
    foreach ($contractsList as $cnt) {
      // Retrieve impacted EmployeeLeaveEarned
      $theDate = new DateTime();
      $currentDate = $theDate->format("Y-m-d");
      $clauseWhere = "idle=0 AND idEmployee = $cnt->idEmployee AND idLeaveType = $obj->idLeaveType AND startDate <= '$currentDate' AND endDate > '$currentDate'";
        $lvE = new EmployeeLeaveEarned();
        $lvEList = $lvE->getSqlElementsFromCriteria(null,false, $clauseWhere);
        foreach ($lvEList as $lvEi) {
            $lvEi->setLeavesRight(true);
            $result = $lvEi->save();
            if (getLastOperationStatus($result)!="OK" and getLastOperationStatus($result)!="NO_CHANGE") {
              $result = htmlSetResultMessage( null, 
                                          getResultMessage($result)." "."InUpdateOfLeaveEarnedForCalculation", 
                                          false,
                                          "", 
                                          "CalculationOfNewLeaveEarned",
                                          getLastOperationStatus($result)
                                        );
              return $result;
            }
        }
    }
    if ($result=="") {
        $result = htmlSetResultMessage( null, 
                                    "Success In Update Of Leave Earned For Calculation", 
                                    false,
                                    "", 
                                    "CalculationOfNewLeaveEarned",
                                    "OK"
                                  );
        
    }
    return $result;
}

/**
 * Get leaves in array date for a period and an employee or for an array Leaves
 * @param Employee $employee Required => Return null else
 * @param string $startDate Formated 'YYYY-mm-dd' Required => Return null else
 * @param string $endDate Formated 'YYYY-mm-dd' Required => Return null else
 * @param integer $idStatus Status for which retrieves leaves. If null, no filter
 * @param integer $idType Leave type for which retrieves leaves. If null, no filter
 * @param Array[EmployeeLeavePeriod] $arrayLeaves Array of leaves to process. If null, search leaves with other parameters
 * @result Array[Date][ AM-PM / idType / idStatus / quantity ]
 */
function getLeavesInArrayDateForAPeriodAndAnEmployee($employee=null, 
                                                     $startDate=null, 
                                                     $endDate=null,
                                                     $idStatus=null,
                                                     $idType=null,
                                                     $arrayLeaves=null) {
    if ($employee==null or $startDate==null or $endDate==null) { return null;}
    
    $leavesInArrayDate=null;
    
    if ($arrayLeaves==null) {
        $whereClause = "idle=0 AND idEmployee=$employee->id AND startDate<='$endDate' AND endDate>='$startDate'";
        if ($idStatus>0) {
            $whereClause .= " AND idStatus=".$idStatus;
        }
        if ($idType>0) {
            $whereClause .= " AND idLeaveType=".$idType;
        }
        $orderBy = "startDate ASC";
        $leave = new Leave();
        $arrayLeaves = $leave->getSqlElementsFromCriteria(null, false, $whereClause,$orderBy);
    }
    
    $lvTpHasWfStWithSubmitted = null;
    
    foreach($arrayLeaves as $leave) {
        $lStartDate = $leave->startDate;        
        $lEndDate = $leave->endDate;
        $sAMPM = $leave->startAMPM;
        $eAMPM = $leave->endAMPM;
        $type = $leave->idLeaveType;
        if ($lvTpHasWfStWithSubmitted==null or !array_key_exists($type, $lvTpHasWfStWithSubmitted)) {
          $tp = new LeaveType($type);
          $wf = new Workflow($tp->idWorkflow);
          $lvTpHasWfStWithSubmitted[$type] = ($wf->hasSetStatusOrLeave("setSubmittedLeave")==false?0:1);
        }
        $status = $leave->idStatus;
        $idLeave = $leave->id;
        $motif = $leave->comment;
        $requestDateTime = $leave->requestDateTime;
        $processingDateTime = $leave->processingDateTime;
        if ($status==1 and $lvTpHasWfStWithSubmitted[$type]==0) {
          $submitted = 1;            
        } else {
          $submitted = $leave->submitted;
        }
        $rejected = $leave->rejected;
        $accepted = $leave->accepted;
        $statusOutOfWorkflow = $leave->statusOutOfWorkflow;
        $statusSetLeaveChange = $leave->statusSetLeaveChange;
        $date = $lStartDate;
        while ($date <= $lEndDate) {
            if ($leavesInArrayDate!=null and array_key_exists($date, $leavesInArrayDate) and $leavesInArrayDate[$date]['quantity']==1){
                $nextDate = new DateTime($date);
                $nextDate->add(new DateInterval("P1D"));
                $date = $nextDate->format("Y-m-d");
                continue;
            }
            $leavesInArrayDate[$date]['startDate']=$lStartDate.' - '.$sAMPM;
            $leavesInArrayDate[$date]['endDate']=$lEndDate.' - '.$eAMPM;
            $leavesInArrayDate[$date]['idType']=$type;
            $leavesInArrayDate[$date]['idStatus']=$status;
            $leavesInArrayDate[$date]['idLeave']=$idLeave;
            $leavesInArrayDate[$date]['motif']=$motif;
            $leavesInArrayDate[$date]['requestDateTime']=$requestDateTime;
            $leavesInArrayDate[$date]['processingDateTime']=$processingDateTime;
            $leavesInArrayDate[$date]['submitted']=$submitted;
            $leavesInArrayDate[$date]['rejected']=$rejected;
            $leavesInArrayDate[$date]['accepted']=$accepted;
            $leavesInArrayDate[$date]['statusOutOfWorkflow']=$statusOutOfWorkflow;
            $leavesInArrayDate[$date]['statusSetLeaveChange']=$statusSetLeaveChange;            
            if ($date == $lStartDate and $date == $lEndDate) {
                // Leave as 0.5 or 1 day
                if ($sAMPM=='AM' and $eAMPM=='PM') {
                    // 1 day
                    $leavesInArrayDate[$date]['AM']=true;
                    $leavesInArrayDate[$date]['PM']=true;
                    $leavesInArrayDate[$date]['quantity']=1;
                } else {
                  if(!isset($leavesInArrayDate[$date]['AM'])){
                    $leavesInArrayDate[$date]['AM']=false;
                  }
                  if(!isset($leavesInArrayDate[$date]['PM'])){
                    $leavesInArrayDate[$date]['PM']=false;
                  }
                    // 0.5 day
                    if($sAMPM=='AM' and $eAMPM=='AM'){
                      $leavesInArrayDate[$date]['AM']=($eAMPM=='AM');
                      $leavesInArrayDate[$date]['quantity']=0.5;
                      $leavesInArrayDate[$date]['idTypeAM']=$type;
                      $leavesInArrayDate[$date]['idStatusAM']=$status;
                    }else{
                      $leavesInArrayDate[$date]['PM']=($eAMPM=='PM');
                      $leavesInArrayDate[$date]['quantity']=0.5;
                      $leavesInArrayDate[$date]['idTypePM']=$type;
                      $leavesInArrayDate[$date]['idStatusPM']=$status;
                    }
                }
            } elseif($date == $lStartDate) {
                // It's the start date
                if ($sAMPM=='AM') {
                    // Start Morning ==> Full day
                    $leavesInArrayDate[$date]['AM']=true;
                    $leavesInArrayDate[$date]['PM']=true;                    
                    $leavesInArrayDate[$date]['quantity']=1;
                } else {
                    // Start Afternoon => Half day
                    $leavesInArrayDate[$date]['AM']=false;
                    $leavesInArrayDate[$date]['PM']=true;                    
                    $leavesInArrayDate[$date]['quantity']=0.5;
                    $leavesInArrayDate[$date]['idTypePM']=$type;
                }
            } elseif ($date == $lEndDate) {
                // It's the end date
                if ($eAMPM=='AM') {
                    // Start Morning ==> Half day
                    $leavesInArrayDate[$date]['AM']=true;
                    $leavesInArrayDate[$date]['PM']=false;
                    $leavesInArrayDate[$date]['quantity']=0.5;
                    $leavesInArrayDate[$date]['idTypeAM']=$type;
                } else {
                    // Start Afternoon => Full day
                    $leavesInArrayDate[$date]['AM']=true;
                    $leavesInArrayDate[$date]['PM']=true;
                    $leavesInArrayDate[$date]['quantity']=1;
                }                
            } else {
                // Not a one day leave and not the start date and not the end date
                // => Full day
                $leavesInArrayDate[$date]['AM']=true;
                $leavesInArrayDate[$date]['PM']=true;                    
                $leavesInArrayDate[$date]['quantity']=1;
            }
            $nextDate = new DateTime($date);
            $nextDate->add(new DateInterval("P1D"));
            $date = $nextDate->format("Y-m-d");
        }
    }
    if ($leavesInArrayDate!=null) {
        foreach($leavesInArrayDate as $date => $item) {
            // Let only dates between startDate and endDate (in parameters)
            if ($date < $startDate or $date > $endDate) {
                unset($leavesInArrayDate[$date]);
            } else {
                // Let only dates on
                if (isOffDay($date, $employee->idCalendarDefinition)) {
                    unset($leavesInArrayDate[$date]);
                }
            }        
        }
    }
    return $leavesInArrayDate;
}

/**
 * Check if validity duration of leaves Earned of an employee are overlap
 * If overlap, close the leave Earned
 * @param integer $idEmployee : Id of Employee for which test validaty of leaves Earned
 * @param LeaveType[] $lvTypesList List of Leavetype
 */
function checkValidity($idEmployee) {
    $currentDate = new DateTime();
    $currentDateString = $currentDate->format("Y-m-d");
    
    // Leave Earned of the employee
    $lvEarned = new EmployeeLeaveEarned();
    $crit = array("idle" => '0',
                  "idEmployee" => $idEmployee);
    $emplLeavesEarned = $lvEarned->getSqlElementsFromCriteria($crit);
    // Actual leaves rights of employee
    $actualRights = getActualLeaveContractualValuesForAllLeaveTypes($idEmployee);
    if ($actualRights==null) { return 'OK';}
    
    foreach($emplLeavesEarned as $lvEarned) {
        if ($lvEarned->endDate==null) { continue;}
        foreach($actualRights as $right) {
            if ($right->validityDuration<1) {continue;}
            if ($lvEarned->idLeaveType == $right->idLeaveType) {
                $startDate = new DateTime($lvEarned->startDate);
                $startDate->add(new DateInterval("P".$right->validityDuration."M"));
                $startDateString = $startDate->format("Y-m-d");
                if ($startDateString < $currentDateString) {
                    $lvEarned->idle=1;
                    $lvEarned->leftQuantityBeforeClose = $lvEarned->leftQuantity;
                    $result = $lvEarned->simpleSave();
                    if (getLastOperationStatus($result)!="OK" and getLastOperationStatus($result)!="NO_CHANGE") {
                        return "KO - checkValidity - Updating idle = 1 for Leave Earned Id = $lvEarned->id";
                    }
                }
            } 
        }
    }
    return 'OK';
}

/**
 * Check if end date of leaves Earned of an employee are overlap
 * If overlap, create a new leaves Earned for the new period
 * @param Employee $idEmployee : Id of Employee for which test validaty of leaves Earned
 */
function checkLeaveEarnedEnd($idEmployee) {
    $currentDateString = date("Y-m-d");
    
    // Leave Earned of the employee
    $arrayType=array(); // Check if Earned period already exists and active (avoid creation of dupplicates)
    $lvEarned = new EmployeeLeaveEarned();
    $crit = array("idle" => '0',
                  "idEmployee" => $idEmployee);
    $emplLeavesEarned = $lvEarned->getSqlElementsFromCriteria($crit,null,null,'endDate desc');
    foreach($emplLeavesEarned as $lvEarned) {
        if ($lvEarned->endDate==null) {
          $arrayType[$lvEarned->idLeaveType]="OK";
          continue;
        }
        $endDateString = $lvEarned->endDate;
        if ($endDateString < $currentDateString and ! isset($arrayType[$lvEarned->idLeaveType])) {
            $newLvEarned = new EmployeeLeaveEarned();
            $newLvEarned->idEmployee=$lvEarned->idEmployee;
            $newLvEarned->idLeaveType=$lvEarned->idLeaveType;
            $newLvEarned->idle=0;
            $newLvEarned->setLeavesRight(true);
            $result = $newLvEarned->simpleSave();
            if (getLastOperationStatus($result)!="OK" and getLastOperationStatus($result)!="NO_CHANGE") {
                return "KO - checkLeaveEarnedEnd - Creating new Leave Earned for employee Id = $idEmployee - Leave Type Id = $lvEarned->idLeaveType\n $result";
            }
        } else {
          $arrayType[$lvEarned->idLeaveType]="OK";
        }
    }
    return 'OK';
}

function checkEarnedPeriod($idEmployee) {
    $currentDate = new DateTime();
    $currentDateString = $currentDate->format("Y-m-d");
    $currentYearMonth = $currentDate->format("Ym");

    // Leave Earned of the employee
    $lvEarned = new EmployeeLeaveEarned();
    $crit = "idle = 0 AND idEmployee = $idEmployee";
    $emplLeavesEarned = $lvEarned->getSqlElementsFromCriteria(null,false,$crit);

    // Actual leaves rights of employee
    $actualRights = getActualLeaveContractualValuesForAllLeaveTypes($idEmployee);
    if ($actualRights==null) {
        return 'OK';
    }
    foreach($emplLeavesEarned as $lvEarned) {
        if ($lvEarned->startDate==null or $lvEarned->endDate==null) { continue;}
        if ($lvEarned->endDate < $currentDateString and 
            $lvEarned->lastUpdateDate!=null and 
            $lvEarned->endDate < $lvEarned->lastUpdateDate) {continue;}
        foreach($actualRights as $right) {
            if ($right->earnedPeriod<1) {continue;}
            if ($lvEarned->idLeaveType == $right->idLeaveType) {
                $lastUpdateDate = new DateTime($lvEarned->lastUpdateDate);
                $lastUpdateDate->add(new DateInterval("P".$right->earnedPeriod."M"));
                $earnedPeriodNextYearMonth = $lastUpdateDate->format("Ym");
                $endDateYearMonth = (new DateTime($lvEarned->endDate))->add(new DateInterval("P1D"))->format("Ym");
                if (min((int)$earnedPeriodNextYearMonth,(int)$endDateYearMonth) <= (int)$currentYearMonth or $lvEarned->lastUpdateDate==null) {
                    $rightArr = $lvEarned->getLeavesRight(true);
                    if ($rightArr['quantity'] != $lvEarned->quantity) {
                        $lvEarned->leftQuantity = $lvEarned->leftQuantity + ($rightArr['quantity']-$lvEarned->quantity);
                        $lvEarned->quantity = $rightArr['quantity'];                        
                    }
                    $lvEarned->lastUpdateDate = $currentDateString;
                    $result = $lvEarned->simpleSave();
                    if (getLastOperationStatus($result)!="OK" and getLastOperationStatus($result)!="NO_CHANGE") {
                        return "KO - Updating Leave Earned $lvEarned->id with new quantity and left for employee Id = $idEmployee - Leave Type Id = $lvEarned->idLeaveType";
                    }
                }
                break;
            }
        }    
    }
 
    return 'OK';
}
