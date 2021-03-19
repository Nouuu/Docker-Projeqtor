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
 * ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/today.php');
$user=getSessionUser();
$profile=new Profile($user->idProfile);
$cptMax=Parameter::getUserParameter('maxItemsInTodayLists');
if (!$cptMax) {
  $cptMax=100;
}

SqlElement::$_cachedQuery['Project']=array();
SqlElement::$_cachedQuery['ProjectPlanningElement']=array();
SqlElement::$_cachedQuery['PlanningElement']=array();

$templateProjectList=Project::getTemplateList();

$collapsedList=Collapsed::getCollaspedList();

$pe=new ProjectPlanningElement();
$pe->setVisibility();
$workVisibility=$pe->_workVisibility;
$costVisibility=$pe->_costVisibility;

$displayWidth=RequestHandler::getValue('destinationWidth');
$twoCols=($displayWidth>1400)?true:false;
if (!isNewGui() ) $twoCols=false;
$twoCols=false;
//echo "width=$displayWidth";

$arrayCols=array('Ticket', 'Activity', 'Milestone', 'Action', 'Risk', 'Issue', 'Question', 'Requirement','Delivery');
$showCol=array();
foreach ($arrayCols as $col) {
  $prjVisLst=$user->getVisibleProjects();
  $crit='idProject in '.transformListIntoInClause($prjVisLst);
  if ($col=='Action') $crit.=' and '.SqlElement::getPrivacyClause();
  $obj=new $col();
  $cptCol[$col]=$obj->countGroupedSqlElementsFromCriteria(null, array('idProject', 'done', 'idle'), $crit);
  $showCol[$col]=securityCheckDisplayMenu(null, $col);
}

if (array_key_exists('refreshProjects', $_REQUEST)) {
  setSessionValue('todayCountScope', (array_key_exists('countScope', $_REQUEST))?$_REQUEST['countScope']:'todo');
  showProjects();
  exit;
}
if(array_key_exists('refreshMessage', $_REQUEST)) {
  showMessages();
  exit;
}
if(array_key_exists('refreshWorkDiv', $_REQUEST)) {
  showAssignedTasks();
  exit;
}
if(array_key_exists('refreshRespDiv', $_REQUEST)) {
  showResponsibleTasks();
  exit;
}
if(array_key_exists('refreshFollowDiv', $_REQUEST)) {
  showIssuerRequestorTasks();
  exit;
}
if(array_key_exists('refreshDocumentDiv', $_REQUEST)) {
  showDocuments();
  exit;
}

function showMessages() {
  global $cptMax;
  $user=getSessionUser();
  $msg=new Message();
  if(sessionValueExists('showAllMessageTodayVal')){
    $showAllMessage=getSessionValue('showAllMessageTodayVal');
  }else if(RequestHandler::isCodeSet('showAllMessageToday')){
    $showAllMessage=RequestHandler::getValue('showAllMessageToday');
  }else{
    $showAllMessage='false';
  }
  $where="idle=0";
  $where.=" and (idUser is null or idUser='".Sql::fmtId($user->id)."')";
  $where.=" and (idProfile is null or idProfile in ".transformListIntoInClause($user->getAllProfiles()).")";
  $where.=" and (idProject is null or idProject in ".transformListIntoInClause($user->getVisibleProjects()).")";
  
  $sort="id desc";
  $listMsg=$msg->getSqlElementsFromCriteria(null, false, $where, $sort);
  if (count($listMsg)>0 ) {
    $cpt=0;
    echo '<input id="showAllMessageToday" name="showAllMessageToday"  hidden value="'.$showAllMessage.'" />';
    echo '<form id="todayMessageForm" name="todayMessageForm">';
    echo '<table align="center" style="width:100%">';
    foreach ($listMsg as $msg) {
      #Florent ticket 4030
      $startDate=$msg->startDate;
      $endDate=$msg->endDate;
      $today=date('Y-m-d H:i:s');
      if( $startDate <= $today && $endDate >= $today or $startDate=='' && $endDate=='' or $startDate<= $today && $endDate==''){
          $cpt++;
          if ($cpt>$cptMax and $showAllMessage=='false') {
            echo '<tr><td colspan="2" class="messageData"><div>'.i18n('limitedDisplay', array($cptMax)).'</div><div style="cursor:pointer;width:16px;height:16px;margin-left:50%;" class="iconDisplayMore16" onclick="refreshTodayList(\'Message\',\'true\')" title="'.i18n('displayMore').'" >&nbsp;</div></td></tr>';
            break;
          }
          $type=new MessageType($msg->idMessageType);
          echo '<tr><td class="messageHeader" style="color:'.htmlEncode($type->color).';">'.htmlEncode($msg->name).'</td></tr>';
          echo '<tr><td class="messageData" >'.htmlEncode($msg->description, 'formatted').'</td></tr>';
          }
        else{
          
        } 
      }
    if($showAllMessage=='true'){
      echo '<tr style="text-align: center;font-weight:bold;"><td colspan="18"  class="messageData"><div style="cursor:pointer;width:16px;height:16px;margin-left:50%;" class="iconReduceDisplay16" onclick="refreshTodayList(\'Message\',\'false\')" title="'.i18n('reduceDisplayToday').'">&nbsp;</div></td></tr>';
    }
    echo '</table>';
    echo '</form>';
  }
}

function showProjects() {
  global $cptMax, $print, $workVisibility, $templateProjectList, $arrayCols, $showCol, $cptCol;
  $user=getSessionUser();
  $parmSizeProject=Parameter::getUserParameter('sizeDisplayProjectToday');
  $prjVisLst=$user->getVisibleProjects();
  $prjLst=$user->getHierarchicalViewOfVisibleProjects(true);
  $lstProj=array();
  foreach ($prjLst as $idProject=>$p){
    $lstProj[]=substr($idProject,1);
  }
  if(sessionValueExists('showAllProjectTodayVal')){
    $showAllProject=getSessionValue('showAllProjectTodayVal');
  }else if(RequestHandler::isCodeSet('showAllProjectToday')){
    $showAllProject=RequestHandler::getValue('showAllProjectToday');
  }else{
    $showAllProject='false';
  }
  $showProject=securityCheckDisplayMenu(null, 'Project');
  $showOne=false;
  if ($showProject) $showOne=true;
  foreach ($arrayCols as $col) {
    $cptFld='cpt'.$col;
    $$cptFld=$cptCol[$col];
    if ($showCol[$col]) $showOne=true;
  }
  $obj=new Project();
  $cptsubProject=$obj->countGroupedSqlElementsFromCriteria(null, array('idProject'), 'idProject in '.transformListIntoInClause($prjVisLst));
  $showIdle=false;
  $showDone=false;
  $countScope='todo';
  if (sessionValueExists('todayCountScope')) {
    $countScope=getSessionValue('todayCountScope');
  }
  if ($showOne and count($prjLst)>0) {
    echo '<div style="width:100%; overflow-x:auto" >';
    echo '<input id="showAllProjectToday" name="showAllProjectToday"  hidden value="'.$showAllProject.'" />';
    echo '<form id="todayProjectsForm" name="todayProjectsForm">';
    echo '<table align="center" style="width:100%">';
    echo '<tr><td style="text-align:left;width:10%" class="tabLabel" >';
    echo '<span class="nobr">'.i18n('titleCountScope').' : </span>';
    echo '</td>';
    if ($print) {
      echo '<td style="text-align:left;" class="tabLabel">';
      echo '<label>'.i18n('titleCount'.ucfirst($countScope)).'&nbsp;</label>';
      echo '</td>';
    } else {
      
      echo '<td style="text-align:right; width:5%" class="tabLabel">';
      echo '<label for="countScopeTodo">'.i18n('titleCountTodo').'&nbsp;</label>';
      echo '</td><td style="text-align:left;" class="tabLabel">';
      echo '<input '
          .(($countScope=='todo')?'':'onChange="refreshTodayProjectsList();"')
          .' type="radio" dojoType="dijit.form.RadioButton" name="countScope" id="countScopeTodo" '
          .(($countScope=='todo')?'checked':'').' value="todo" />';
      echo '</td>';
      echo '<td style="text-align:right; width:5%" class="tabLabel">';
      echo '<label for="countScopeNotClosed">'.i18n('titleCountNotClosed').'&nbsp;</label>';
      echo '</td><td style="text-align:left;" class="tabLabel">';
      echo '<input '
          .(($countScope=='notClosed')?'':'onChange="refreshTodayProjectsList();"')
          .' type="radio" dojoType="dijit.form.RadioButton" name="countScope" id="countScopeNotClosed" '
          .(($countScope=='notClosed')?'checked':'').' value="notClosed" />';
      echo '</td>';
      echo '<td style="text-align:right; width:5%" class="tabLabel">';
      echo '<label for="countScopeAll">'.i18n('titleCountAll').'&nbsp;</label>';
      echo '</td><td style="text-align:left;" class="tabLabel">';
      echo '<input '
          .(($countScope=='all')?'':'onChange="refreshTodayProjectsList();"')
          .' type="radio" dojoType="dijit.form.RadioButton" name="countScope" id="countScopeAll" '
          .(($countScope=='all')?'checked':'').' value="all" />';
      echo '</td>';
      echo "<td style='width:50%'>&nbsp;</td>";
    }
    echo '</tr>';
    echo '</table></form>';
    $width=($print)?'45':'55';
    $lstProj=implode(",",$lstProj);
    echo '<table align="center" style="width:100%; ">';
    echo '<tr>'.'  <td class="messageHeader" colspan="'.(($showProject)?'3':'1').'">'.i18n('menuProject');
    if(!$print){
      echo '     <div id="showProjectToDay" class="ganttExpandOpened"';
      echo '      style="float:left; width:16px; height:13px;"';
      echo '      onclick="showProjectToDay(0,\''.$lstProj.'\')">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
      echo '     <div id="hideProjectToDay" class="ganttExpandClosed"';
      echo '      style="float:left; width:16px; height:13px;"';
      echo '      onclick="showProjectToDay(1,\''.$lstProj.'\')">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
    }
    echo '</td>';
    if ($showProject) echo '  <td class="messageHeader" colspan="2" width="'.($width).'px;"><div xstyle="width:50px; xoverflow: hidden; xtext-overflow: ellipsis;">'.ucfirst(i18n('progress')).'</div></td>';
    if ($workVisibility=='ALL' and $showProject) {
      echo '  <td class="messageHeader" width="'.$width.'px;"><div xstyle="width:50px; xoverflow: hidden; xtext-overflow: ellipsis;">'.ucfirst(i18n('colLeft')).'</div></td>';
      echo '  <td class="messageHeader" width="'.$width.'px;"><div xstyle="width:50px; xoverflow: hidden; xtext-overflow: ellipsis;">'.ucfirst(i18n('colMargin')).'</div></td>';
    }
    if ($showProject) echo '  <td class="messageHeader" width="5%"><div xstyle="width:80px; xoverflow: hidden; xtext-overflow: ellipsis;">'.ucfirst(i18n('colEndDate')).'</div></td>'
                          .'  <td class="messageHeader" width="5%"><div xstyle="width:60px; xoverflow: hidden; xtext-overflow: ellipsis;">'.ucfirst(i18n('colLate')).'</div></td>';
    foreach ($arrayCols as $col) {
      if (!$showCol[$col]) continue;
      echo '  <td class="messageHeader" width="'.$width.'px;"><div style="width:'.($width+10).'px; overflow: hidden;text-align:center;text-overflow: hidden;">'.i18n('menu'.$col).'</div></td>';
    }
    echo '</tr>';
    $cpt=0;
    $countPro=-1;
    $levels=array();
    foreach ($prjLst as $sharpid=>$sharpName) {
      $cpt++;
      $visibleRows=array();
      $countPro++;
      if($parmSizeProject!='' and $parmSizeProject==$countPro and $showAllProject=='false'){
        echo '<tr style="text-align: center;font-weight:bold;"><td colspan="18"  class="messageData"><div >'.i18n('limitedDisplay', array($parmSizeProject)).'</div><div style="cursor:pointer;width:16px;height:16px;margin-left:50%;" class="iconDisplayMore16" onclick="refreshTodayProjectsList(\'true\')" title="'.i18n('displayMore').'" >&nbsp;</div></td></tr>';
        break;
      }
      $split=explode('#', $sharpName);
      $wbs=$split[0];
      $name=str_replace('&sharp;', '#', $split[1]);
      $id=substr($sharpid, 1);
      $project= new Project($id);
      $isSubProj=$project->getTopProjectList();
      $listSub=array();
      $hiddenSubProj=true;
      //florent
      $subProj=$project->getSubProjectsList();
      $user=getCurrentUserId();
      $colParent = SqlElement::getSingleSqlElementFromCriteria('Collapsed', array('scope'=>'todayProjectRow_'.$id, 'idUser'=>$user));
      $idProj=$id;
      foreach ($subProj as $idSub=>$sub){
        $listSub[]=$idSub;
        $critArray=array('scope'=>'todayProjectRow_'.$idSub, 'idUser'=>$user);
        $col = SqlElement::getSingleSqlElementFromCriteria('Collapsed', $critArray);
          $visibleRows[]=$idSub;
        if($col->id=='' and $hiddenSubProj==true){
          $hiddenSubProj=false;
        }
        $newSub=new Project($idSub);
        $asSub=$newSub->getSubProjectsList();
        if($asSub){
          foreach ($asSub as $idsub2=>$sub2){
            $visibleRows[]=$idsub2;
          }
        }
      }
      $pose='';
      $showForced=false;
      if(count($isSubProj)!=0 ){
        $pose=trim(strpos($lstProj,$isSubProj[0]));
      }
      if($pose==''){
        $showForced=true;
      }
      if($colParent->id=="" and count($isSubProj)!=0 and  $showForced==false){
        continue;
      }
      if($hiddenSubProj ){
        $class="ganttExpandOpened";
      }else{
        $class="ganttExpandClosed";
      }
      //
      foreach ($arrayCols as $col) {
        $nbItem[$col]=countFrom($cptCol[$col], $id, '', $countScope);
        $nbItemAll[$col]=countFrom($cptCol[$col], $id, 'All', $countScope);
        $nbItemTodo[$col]=countFrom($cptCol[$col], $id, 'Todo', $countScope);
        $nbItemDone[$col]=countFrom($cptCol[$col], $id, 'Done', $countScope);
        $nbItem[$col]=($nbItemAll[$col]==0)?'':$nbItem[$col];
      }     
      $prjPE=SqlElement::getSingleSqlElementFromCriteria('ProjectPlanningElement', array('refType'=>'Project', 'refId'=>$id));
      $endDate=$prjPE->plannedEndDate;
      $endDate=($endDate=='')?$prjPE->validatedEndDate:$endDate;
      $endDate=($endDate=='')?$prjPE->initialEndDate:$endDate;
      $progress='0';
      if ($prjPE->realWork!='' and $prjPE->plannedWork!='' and $prjPE->plannedWork!='0') {
        $progress=$prjPE->progress;
      }
      $real=$prjPE->realWork;
      $left=$prjPE->leftWork;
      $margin=$prjPE->marginWorkPct;
      if ($margin!==null) {
        $margin='<div style="color:'.(($margin==0)?'#555555':(($margin<0)?'#DD0000':'#00AA00')).';">'.$margin.'&nbsp;%</div>';
      }
      $planned=$prjPE->plannedWork;
      $late='';
      if ($prjPE->plannedEndDate!='' and $prjPE->validatedEndDate!='') {
        $late=dayDiffDates($prjPE->validatedEndDate, $prjPE->plannedEndDate);
        $late='<div style="color:'.(($late>0)?'#DD0000':'#00AA00').';">'.$late;
        $late.=" ".i18n("shortDay");
        $late.='</div>';
      }
      $wbs=$prjPE->wbsSortable;
      $split=explode('.', $wbs);
      //$level=count($split); // Old way...
      $level=0;
      $testWbs='';
      foreach($split as $sp) {
        $testWbs.=(($testWbs)?'.':'').$sp;
        if (isset($levels[$testWbs])) $level=$levels[$testWbs]+1;
      }
      $levels[$wbs]=$level;
      $tab="";
      for ($i=1; $i<=$level; $i++) {
        $tab.='&nbsp;&nbsp;&nbsp;';
        // $tab.='...';
      }
      $show=false;
      if (array_key_exists($id, $prjVisLst)) {
        $show=true;
      }
      if (array_key_exists($id, $templateProjectList)) {
        $show=false;
      }
      $cptSubPrj=(isset($cptsubProject[$id]))?$cptsubProject[$id]:0;
      if(count($prjLst)==1){
        $show=true;
      }
      if ($show or $cptSubPrj>0) {
        $goto="";
        $proj=new Project($id);
        if (!$print and $show and securityCheckDisplayMenu(null, 'Project') and 
        // and securityGetAccessRightYesNo('menuProject', 'read', $prj)=="YES"
        array_key_exists($id, $prjVisLst)) {
          $goto=' onClick="gotoElement(\'Project\',\''.$id.'\');setSelectedProject(\''.htmlEnCode($proj->id).'\',\''.htmlEnCode($proj->name).'\',\'selectedProject\');" style="border-right:0px;text-align: left;cursor: pointer;'.($show?'':'color:#AAAAAA;').'" ';
        } else {
          $goto=' style="border-right:0px;text-align: left;"';
        }
        $healthColor=SqlList::getFieldFromId("Health", $proj->idHealth, "color");
        $healthName=SqlList::getNameFromId("Health", $proj->idHealth);
        $healthIcon=SqlList::getFieldFromId("Health", $proj->idHealth, "icon");
        $trendIcon=SqlList::getFieldFromId("Trend", $proj->idTrend, "icon");
        $trendName=SqlList::getNameFromId("Trend", $proj->idTrend);
        $styleHealth=($print)?'width:10px;height:10px;margin:1px;padding:0;-moz-border-radius:6px;border-radius:6px;border:1px solid #AAAAAA;':'';
        echo '<tr style="text-align: center">';
        echo '  <td class="messageData" >';
        if($subProj and !$print){
          echo '     <input id="group_asSub_'.$idProj.'" hidden value="'.implode(',', $listSub).'">';
          echo '     <div id="group_'.$idProj.'" class="'.$class.'"';
          echo '      style="float:left; width:16px; height:13px;"';
          echo '      onclick="expandProjectInToDay(\''.$idProj.'\',\''.implode(",", $listSub).'\',\''.implode(',', $visibleRows).'\');">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
        }else{
          echo '     <div id="group_'.$idProj.'"';
          echo '      style="float:left; width:16px; height:13px;"';
          echo '     >&nbsp;&nbsp;&nbsp;&nbsp;</div>';
        }
        echo '<div '.$goto.' style="width:100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; " class="'.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'">'.$tab.htmlEncode($name);
        echo '</div></td>';
        if ($showProject) {
          echo ' <td class="messageData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" style="vertical-align:middle;width:12px;margin:0;padding:0;spacing:0;border-left:0px;border-right:0px;" '.$goto.' >'.(($trendIcon)?'    <img height="12px" src="icons/'.$trendIcon.'" title="'.$trendName.'"/>':'').'  </td>';
          if ($healthIcon) {
            echo ' <td class="messageData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" style="vertical-align:middle;width:12px;margin:0;padding:0;spacing:0;border-left:0px;border-right:0px;" '.$goto.' >'.' <img height="12px" src="icons/'.$healthIcon.'" title="'.$healthName.'"/>'.'  </td>';
          } else {
            echo '  <td class="messageData '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" style="width:14px;margin:0;padding:0;spacing:0;border-left:0px;" '.$goto.' >'.'    <div class="colorHealth" style="'.$styleHealth.'background:'.$healthColor.';" title="'.$healthName.'">&nbsp;</div>'.'  </td>';
          }
        }
        if ($showProject) echo '  <td style="width:'.$width.'px" class="messageDataValue'.($show?'':'Grey').' colorNameData">'.($show?displayProgress(htmlDisplayPct($progress), $planned, $left, $real, true, true):'').'</td>';
        if ($showProject) echo '  <td style="width:'.$width.'px" class="messageDataValue'.($show?'':'Grey').'">'.($show?SqlList::getNameFromId('OverallProgress', $proj->idOverallProgress):"").'</td>';
        if ($workVisibility=='ALL' and $showProject) {
          echo '  <td class="messageDataValue'.($show?'':'Grey').'">'.($show?Work::displayWorkWithUnit($left):'').'</td>';
          echo '  <td class="messageDataValue'.($show?'':'Grey').'">'.($show?$margin:'').'</td>';
        }
        if ($showProject) echo '  <td class="messageDataValue'.($show?'':'Grey').'" NOWRAP>'.($show?htmlFormatDate($endDate):'').'</td>'
                              .'  <td class="messageDataValue'.($show?'':'Grey').'">'.($show?$late:'').'</td>';
        foreach ($arrayCols as $col) {
          if (!$showCol[$col]) continue;
          echo '  <td class="messageDataValue'.($show?'':'Grey').' colorNameData" onclick=\'gotoElement("'.$col.'",null);stockHistory("'.$col.'",null,"object");setSelectedProject("'.htmlEnCode($proj->id).'","'.htmlEnCode($proj->name).'","selectedProject");\' style="cursor: pointer;">'.($show?displayProgress($nbItem[$col], $nbItemAll[$col], $nbItemTodo[$col], $nbItemDone[$col]):'').'</td>';
        }
        echo '</tr>';
      }
    }
    if($showAllProject=='true'){
      echo '<tr style="text-align: center;font-weight:bold;"><td colspan="18"  class="messageData"><div style="cursor:pointer;width:16px;height:16px;margin-left:50%;" class="iconReduceDisplay16" onclick="refreshTodayProjectsList(\'false\')" title="'.i18n('reduceDisplayToday').'">&nbsp;</div></td></tr>';
    }
    echo '</table>';
    echo '</div>';
  }
}

function showDocuments() {
  if (!securityCheckDisplayMenu(null, 'Document')) return;
  $user=getSessionUser();
  $prjDocLst=$user->getVisibleProjects();
  $approver=new Approver();
  $critApprover=array('refType'=>"Document", 'idAffectable'=>$user->id);
  $critApprover2=array('refType'=>"DocumentVersion", 'approved'=>'1', 'idAffectable'=>$user->id);
  $critApprover3=array('refType'=>"Decision", 'approved'=>'0','idAffectable'=>$user->id);
  $listApprover=$approver->getSqlElementsFromCriteria($critApprover, false, null);
  $listApprover2=$approver->getSqlElementsFromCriteria($critApprover2, false, null);
  $listApprover3=$approver->getSqlElementsFromCriteria($critApprover3, false, null);
  $arrayDec=array();
  $arrayDoc=array();
  $arrayDocVers=array();
  // Liste of document version approved and approved by me
  foreach ($listApprover2 as $valApp2) {
    $docVers=new DocumentVersion($valApp2->refId);
    $doc=new Document($docVers->idDocument);
    if ($docVers->id==$doc->idDocumentVersion) {
      $arrayDocVers[$docVers->idDocument]=$doc->id;
    }
  }
  // List of document approved by me
  foreach ($listApprover as $valApp) {
    // recupérer version document new documentversion ($valApp->refId) , acceder au document , si iddocumentversion du document est bien la version recupérer dans le foreach , alors je stocke le $arrayDoc[iddudocument]
    $arrayDoc[$valApp->refId]=$valApp->refId;
  }
  foreach ($listApprover3 as $valApp) {
  	// recupérer version document new documentversion ($valApp->refId) , acceder au document , si iddocumentversion du document est bien la version recupérer dans le foreach , alors je stocke le $arrayDoc[iddudocument]
  	$arrayDec[$valApp->refId]=$valApp->refId;
  }
  $arrayD=array_diff($arrayDoc, $arrayDocVers);
  if (count($arrayD)==0) $arrayD=array(0=>" ");
  $whereDocument="id in ".transformListIntoInClause($arrayD);
  $whereActivity="1=0";
  $where=$whereActivity;
  $whereTicket=$where;
  $whereMeeting=$whereTicket;
  $whereDecision="id in ".transformListIntoInClause($arrayDec);
  showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument,$whereDecision, 'Today_DocumentDiv', 'documentsApproval');
}

function countFrom($list, $idProj, $type, $scope) {
  $cpt00=(isset($list[$idProj.'|0|0']))?$list[$idProj.'|0|0']:0;
  $cpt01=(isset($list[$idProj.'|0|1']))?$list[$idProj.'|0|1']:0;
  $cpt10=(isset($list[$idProj.'|1|0']))?$list[$idProj.'|1|0']:0;
  $cpt11=(isset($list[$idProj.'|1|1']))?$list[$idProj.'|1|1']:0;
  if ($type=='All') {
    return $cpt00+$cpt01+$cpt10+$cpt11;
  } else if ($type=='Todo') {
    return $cpt00;
  } else if ($type=='Done') {
    return $cpt10;
  } else {
    if ($scope=='todo') {
      return $cpt00;
    } else if ($scope=='notClosed') {
      return $cpt00+$cpt10;
    } else {
      return $cpt00+$cpt01+$cpt10+$cpt11;
    }
  }
}

$cptDisplayId=0;

function displayProgress($value, $allValue, $todoValue, $doneValue, $showTitle=true, $isWork=false) {
  global $cptDisplayId, $print, $workVisibility;
  if (!$workVisibility) {
    $pe=new ProjectPlanningElement();
    $pe->setVisibility();
    $workVisibility=$pe->_workVisibility;
  }
  if ($value==='') {
    return $value;
  }
  $width=($print)?'45':'65';

  $green=($allValue!=0 and $allValue)?round($width*($allValue-$todoValue)/$allValue, 0):$width;
  $red=$width-$green;
  
  $cptDisplayId+=1;
  $result='<div style="position:relative; height:100%;width:'.$width.'px" id="displayProgress_'.$cptDisplayId.'">';
  $result.='<div style="overflow:hidden;position:absolute; height:100%;left:0px; width:'.$green.'px;background: #AAFFAA;">&nbsp;</div>';
  $result.='<div style="position:absolute; width:'.$red.'px;height:100%;left:'.$green.'px;background: #FFAAAA;">&nbsp;</div>';
  $result.='<div style="position:relative;">'.$value.'</div>';
  $result.='</div>';
  if ($showTitle and !$print and (!$isWork or $workVisibility=='ALL')) {
    $result.='<div dojoType="dijit.Tooltip" connectId="displayProgress_'.$cptDisplayId.'" position="below">';
    $result.="<table>";
    if ($isWork) {
      $result.='<tr style="text-align:right;"><td>'.i18n('real').'&nbsp;:&nbsp;</td><td style="background: #AAFFAA">'.Work::displayWorkWithUnit($doneValue).'</td></tr>';
      $result.='<tr style="text-align:right;"><td>'.i18n('left').'&nbsp;:&nbsp;</td><td style="background: #FFAAAA">'.Work::displayWorkWithUnit($todoValue).'</td></tr>';
      $result.='<tr style="text-align:right;font-weight:bold; border-top:1px solid #101010"><td>'.i18n('sum').'&nbsp;:&nbsp;</td><td>'.Work::displayWorkWithUnit($allValue).'</td></tr>';
    } else {
      $result.='<tr style="text-align:right;"><td>'.i18n('titleNbTodo').'&nbsp;:&nbsp;</td><td style="background: #FFAAAA">'.($todoValue).'</td></tr>';
      $result.='<tr style="text-align:right;"><td>'.i18n('titleNbDone').'&nbsp;:&nbsp;</td><td style="background: #AAFFAA">'.($doneValue).'</td></tr>';
      $result.='<tr style="text-align:right;"><td>'.i18n('titleNbClosed').'&nbsp;:&nbsp;</td><td style="background: #AAFFAA">'.($allValue-$todoValue-$doneValue).'</td></tr>';
      $result.='<tr style="text-align:right;font-weight:bold; border-top:1px solid #101010"><td>'.i18n('titleNbAll').'&nbsp;:&nbsp;</td><td>'.($allValue).'</td></tr>';
    }
    $result.='</table>';
    $result.='</div>';
  }
  return $result;
}

function showAssignedTasks() {
  if (!securityCheckDisplayMenu(null, 'Activity') and !!securityCheckDisplayMenu(null, 'Meeting') ) return;
  $user=getSessionUser();
  $ass=new Assignment();
  $act=new Activity();
  $meet=new Meeting();
  $where="1=0";
  $whereTicket=$where;
  $whereDocument=$where;
  $whereActivity=" (exists (select 'x' from ".$ass->getDatabaseTableName()." x "."where x.refType='Activity' and x.refId=".$act->getDatabaseTableName().".id and x.idResource='".Sql::fmtId($user->id)."')".") and idle=0 and done=0";
  $whereMeeting=str_replace(array('Activity', $act->getDatabaseTableName()), array('Meeting', $meet->getDatabaseTableName()), $whereActivity);
  $whereDecision=$where;
  showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument,$whereDecision, 'Today_WorkDiv', 'todayAssignedTasks');
}

function showAccountableTasks() {
  if (!getSessionUser()->isResource) return;
  $user=getSessionUser();
  $where="1=0";
  $whereTicket="idAccountable='".Sql::fmtId($user->id)."' and idle=0 and done=0";
  $whereActivity=$where;
  $whereMeeting=$whereActivity;
  $whereDocument=$whereMeeting;
  $whereDecision=$where;
  showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument,$whereDecision, 'Today_AccDiv', 'todayAccountableTasks');
}

function showResponsibleTasks() {
  if (!getSessionUser()->isResource) return;
  $user=getSessionUser();
  $ass=new Assignment();
  $act=new Activity();
  $where="(idResource='".Sql::fmtId($user->id)."'".") and idle=0 and done=0";
  $whereTicket=$where;
  $whereActivity=$where;
  $whereMeeting=$whereActivity;
  $whereDocument="1=0";
  $whereDecision=$whereDocument;
  showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument,$whereDecision, 'Today_RespDiv', 'todayResponsibleTasks');
}

function showIssuerRequestorTasks() {
  $user=getSessionUser();
  $where="(idUser='".Sql::fmtId($user->id)."'".") and idle=0 and done=0";
  $whereTicket="(idUser='".Sql::fmtId($user->id)."'"." or idContact='".Sql::fmtId($user->id)."'".") and idle=0 and done=0";
  $whereActivity=$whereTicket;
  $whereMeeting=$where;
  $whereDocument="1=0";
  $whereDecision=$whereDocument;
  showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument,$whereDecision, 'Today_FollowDiv', 'todayIssuerRequestorTasks');
}

function showProjectsTasks() {
  $where="(idProject in ".getVisibleProjectsList().") and idle=0 and done=0";
  $whereTicket=$where;
  $whereActivity=$where;
  $whereMeeting=$where;
  $whereDocument="1=0";
  $whereDecision=$whereDocument;
  showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument,$whereDecision, 'Today_ProjectTasks', 'todayProjectsTasks');
}

function showActivitiesList($where, $whereActivity, $whereTicket, $whereMeeting, $whereDocument, $whereDecision, $divName, $title) {
  // Assign idRess idUser idCont Items
  // $where : NO YES YES NO Milestone, Risk, Action, Issue, Opportunity, Decision, Question, Quote, Order, Bill
  // $whereActivity : YES YES YES YES Activity
  // $whereTicket : NO YES YES YES Ticket
  // $whereMeeting : YES YES YES NO Meeting, TestSession
  global $cptMax, $print, $cptDisplayId, $collapsedList, $templateProjectList;
  $user=getSessionUser();
  $crit=array('idUser'=>$user->id, 'idToday'=>null, 'parameterName'=>'periodDays');
  $tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter', $crit);
  $periodDays=$tp->parameterValue;
  $crit=array('idUser'=>$user->id, 'idToday'=>null, 'parameterName'=>'periodNotSet');
  $tp=SqlElement::getSingleSqlElementFromCriteria('TodayParameter', $crit);
  $periodNotSet=$tp->parameterValue;
  $ass=new Assignment();
  $act=new Activity();
  $order="";
  $list=array();
  $ticket=new Ticket();
  $showAllLib=substr($divName,6);
  if(sessionValueExists('showAll'.$showAllLib.'TodayVal')){
    $showAllToday=getSessionValue('showAll'.$showAllLib.'TodayVal');
  }else if(RequestHandler::isCodeSet('showAll'.$showAllLib.'Today')){
    $showAllToday=RequestHandler::getValue('showAll'.$showAllLib.'Today');
  }else{
    $showAllToday='false';
  }
  $listTicket=$ticket->getSqlElementsFromCriteria(null, null, $whereTicket, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listTicket);
  $activity=new Activity();
  $listActivity=$activity->getSqlElementsFromCriteria(null, null, $whereActivity, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listActivity);
  $milestone=new Milestone();
  $listMilestone=$milestone->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listMilestone);
  $risk=new Risk();
  $listRisk=$risk->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listRisk);
  $opportunity=new Opportunity();
  $listOpportunity=$opportunity->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listOpportunity);
  // gautier #2840
  $question=new Question();
  $listQuestion=$question->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listQuestion);
  // krowry #2915
  $document=new Document();
  $listDoc=$document->getSqlElementsFromCriteria(null, false, $whereDocument);
  $list=array_merge($list, $listDoc);
  $decision=new Decision();
  $listDec=$decision->getSqlElementsFromCriteria(null, false, $whereDecision);
  $list=array_merge($list, $listDec);
  $action=new Action();
  $listAction=$action->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listAction);
  $issue=new Issue();
  $listIssue=$issue->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listIssue);
  $meeting=new Meeting();
  $listMeeting=$meeting->getSqlElementsFromCriteria(null, null, $whereMeeting, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listMeeting);
  $session=new TestSession();
  $listSession=$session->getSqlElementsFromCriteria(null, null, str_replace(array('Meeting', $meeting->getDatabaseTableName()), array(
      'TestSession', 
      $session->getDatabaseTableName()), $whereMeeting), $order, null, true, $cptMax+1);
  $list=array_merge($list, $listSession);
  $decision=new Decision();
  $listDecision=$decision->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listDecision);
  $requirement=new Requirement();
  $listRequirement=$requirement->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listRequirement);
  $quotation=new Quotation();
  $listQuotation=$quotation->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listQuotation);
  $command=new Command();
  $listCommand=$command->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listCommand);
  $bill=new Bill();
  $listBill=$bill->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listBill);
  $calltender=new CallForTender();
  $listcalltender=$calltender->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listcalltender);
  $tender=new Tender();
  $listtender=$tender->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listtender);
  $orderToProvider=new ProviderOrder();
  $listOrderToProvider=$orderToProvider->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listOrderToProvider);
  $providerTerm=new ProviderTerm();
  $listProviderTerm=$providerTerm->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listProviderTerm);
  $providerBill=new ProviderBill();
  $listProviderBill=$providerBill->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listProviderBill);
  $term=new Term();
  $listTerm=$term->getSqlElementsFromCriteria(null, null, $where, $order, null, true, $cptMax+1);
  $list=array_merge($list, $listTerm);
  
  if (!$print or !array_key_exists($divName, $collapsedList)) {
    if (!$print and !(array_key_exists('refresh'.$showAllLib, $_REQUEST))) {
      echo '<div id="'.$divName.'" dojoType="dijit.TitlePane"';
      echo ' open="'.(array_key_exists($divName, $collapsedList)?'false':'true').'"';
      echo ' onHide="saveCollapsed(\''.$divName.'\');"';
      echo ' onShow="saveExpanded(\''.$divName.'\');"';
      echo ' title="'.ucfirst(i18n($title)).'"';
      echo '>';
    } else {
      echo '<div class="section">'.ucfirst(i18n($title)).'</div><br/><div>';
    }
    echo '<input id="showAll'.$showAllLib.'Today" name="showAll'.$showAllLib.'Today"  hidden value="'.$showAllToday.'" />';
    echo '<form id="today'.$showAllLib.'Form" name="today'.$showAllLib.'Form">';
    echo '<table align="center" style="width:100%">';
    echo '<tr>'.' <td class="messageHeader" width="6%">'.ucfirst(i18n('colId')).'</td>'.' <td class="messageHeader" width="12%">'.ucfirst(i18n('colIdProject')).'</td>'.'  <td class="messageHeader" width="12%">'.ucfirst(i18n('colType')).'</td>'.'  <td class="messageHeader" width="40%">'.ucfirst(i18n('colName')).'</td>'.'  <td class="messageHeader" width="8%">'.ucfirst(i18n('colDueDate')).'</td>'.'  <td class="messageHeader" width="12%">'.ucfirst(i18n('colIdStatus')).'</td>'.'  <td class="messageHeader" width="3%" title="'.i18n('isIssuerOf').'">'.ucfirst(i18n('colIssuerShort')).'</td>'.'  <td class="messageHeader" width="3%" title="'.i18n('isAccountableOf').'">'.ucfirst(i18n('colAccountableShort')).'</td>'.'  <td class="messageHeader" width="3%" title="'.i18n('isResponsibleOf').'">'.ucfirst(i18n('colResponsibleShort')).'</td>'.'</tr>';
    $cpt=0;
    $listEcheance=array();
    foreach ($list as $elt) {
      $echeance="";
      if (property_exists($elt, 'idProject') and array_key_exists($elt->idProject, $templateProjectList)) continue;
      $class=get_class($elt);
      if ($class=='Ticket') {
        $echeance=($elt->actualDueDateTime)?$elt->actualDueDateTime:$elt->initialDueDateTime;
        $echeance=substr($echeance, 0, 10);
      } else if ($class=='Activity' or $class=='Milestone' or $class=="TestSession" or $class=="Meeting") {
        $pe=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', array('refType'=>$class, 'refId'=>$elt->id));
        $echeance=($pe->realEndDate)?$pe->realEndDate:(($pe->plannedEndDate)?$pe->plannedEndDate:(($pe->validatedEndDate)?$pe->validatedEndDate:$pe->initialEndDate));
      } else if ($class=="Risk" or $class=="Issue" or $class=="Opportunity") {
        $echeance=($elt->actualEndDate)?$elt->actualEndDate:$elt->initialEndDate;
      } else if ($class=="Action") {
        $echeance=($elt->actualDueDate)?$elt->actualDueDate:$elt->initialDueDate;
      } else if ($class=='CallForTender') {
      	$echeance=$elt->expectedTenderDateTime;
      } else if ($class=='Tender') {
      	$echeance=($elt->offerValidityEndDate)?$elt->offerValidityEndDate:$elt->expectedTenderDateTime;
      } else if ($class=='ProviderOrder') {
      	$echeance=$elt->deliveryExpectedDate;      	
      } else if ($class=='ProviderTerm' or $class=='Bill') {
      	$echeance=$elt->date;      	
      } else if ($class=='ProviderBill') {
      	$echeance=($elt->paymentDueDate)?$elt->paymentDueDate:$elt->date;      	
      } else if ($class=='Quotation') {
       $echeance=$elt->validityEndDate;
      } else if ($class=='Command') {
      	$echeance=($elt->validatedEndDate)?$elt->validatedEndDate:$elt->initialEndDate;      	
      } else if ($class=='Term') {
      	$echeance=($elt->date)?$elt->date:(($elt->validatedDate)?$elt->validatedDate:$elt->plannedDate);      	
      }
      
      $listEcheance[$echeance.'#'.$class.'#'.$elt->id]=$elt;
    }
    ksort($listEcheance);
    foreach ($listEcheance as $idList=>$elt) {
      $cptDisplayId++;
      $idType='id'.get_class($elt).'Type';
      $class=get_class($elt);
      $split=explode('#', $idList);
      $echeance=$split[0];
      if ($periodDays) {
        if (!$echeance) {
          if (!$periodNotSet) {
            continue;
          }
        } else {
          if ($echeance>addDaysToDate(date("Y-m-d"), $periodDays)) {
            continue;
          }
        }
      }
      $cpt++;
      if ($cpt>$cptMax and $showAllToday=='false') {
        echo '<tr><td colspan="9" class="messageData" style="text-align:center;"><div><b>'.i18n('limitedDisplay', array($cptMax)).'</b></div><div style="cursor:pointer;width:16px;height:16px;margin-left:50%;" class="iconDisplayMore16" onclick="refreshTodayList(\''.$showAllLib.'\',\'true\')" title="'.i18n('displayMore').'" >&nbsp;</div></td></tr>';
        break;
      }
      $status="";
      $statusColor="";
      $displayColorStatus="";
      if (property_exists($elt, 'idStatus')){
        $statusColor=SqlList::getFieldFromId('Status', $elt->idStatus, 'color');
        $status=SqlList::getNameFromId('Status', $elt->idStatus);
        $displayColorStatus = htmlDisplayColoredFull($status, $statusColor);
      }
      $status=($status=='0')?'':$status;
      $goto="";
      $classGoto=$class;
      if ($classGoto=='Ticket' and (!securityCheckDisplayMenu(null, $classGoto) or !securityGetAccessRightYesNo('menu'.$classGoto, 'read', $elt)=="YES")) {
        $classGoto='TicketSimple';
      }
      if (!$print and securityCheckDisplayMenu(null, $classGoto) and securityGetAccessRightYesNo('menu'.$classGoto, 'read', $elt)=="YES") {
        $goto=' onClick="gotoElement('."'".$classGoto."','".htmlEncode($elt->id)."'".');" style="cursor: pointer;" ';
      }
      $alertLevelArray=$elt->getAlertLevel(true);
      $alertLevel=$alertLevelArray['level'];
      $color="background-color:#FFFFFF";
      if ($alertLevel=='ALERT') {
        $color='background-color:#FFAAAA;';
      } else if ($alertLevel=='WARNING') {
        $color='background-color:#FFDDAA;';
      } else if ($alertLevel=='CRITICAL') {
        $color='background-color:#FF5555;';
      }
      echo '<tr '.$goto.' id="displayWork_'.$cptDisplayId.' '.((isNewGui() and isset($goto) and $goto!='')?'classLinkName':'').'" >';
      if (!$print and $alertLevel!='NONE') {
        echo '<div dojoType="dijit.Tooltip" connectId="displayWork_'.$cptDisplayId.'" position="below">';
        echo $alertLevelArray['description'];
        echo '</div>';
      }
      $type="";
      if(property_exists($elt, 'id'.$class.'Type')){
        $type = SqlList::getNameFromId($class.'Type', $elt->$idType);
      }
      echo '  <td class="messageData" style="'.$color.'">'.'<table><tr><td>'.formatIcon($class, 16, i18n($class)).'</td><td>&nbsp;</td><td>#'.$elt->id.'</td></tr></table></td>'.'  <td class="messageData" style="'.$color.'">'.htmlEncode(SqlList::getNameFromId('Project', $elt->idProject)).'</td>'.'  <td class="messageData" style="'.$color.'">'.$type.'</td>'.'  <td class="messageData" style="'.$color.'">'.htmlEncode($elt->name).'</td>'.'  <td class="messageDataValue" style="'.$color.'" NOWRAP>'.htmlFormatDate($echeance).'</td>'.'  <td class="messageData colorNameData" style="'.$color.'">'.$displayColorStatus.'</td>'.'  <td class="messageDataValue" style="'.$color.'">'.htmlDisplayCheckbox($user->id==$elt->idUser).'</td>';
      if (property_exists($elt, 'idAccountable')) echo '  <td class="messageDataValue" style="'.$color.'">'.htmlDisplayCheckbox($user->id==$elt->idAccountable).'</td>';
      else echo '  <td class="messageDataValue" style="'.$color.'">'.htmlDisplayCheckbox(null).'</td>';
      if (property_exists($elt, 'idAuthor')) {
        echo '  <td class="messageDataValue" style="'.$color.'">'.htmlDisplayCheckbox($user->id==$elt->idAuthor).'</td>';
      } else {
        echo '  <td class="messageDataValue" style="'.$color.'">'.htmlDisplayCheckbox($user->id==$elt->idResource).'</td>';
      }
      echo '</tr>';
    }
    if($showAllToday=='true'){
      echo '<tr style="text-align: center;font-weight:bold;"><td colspan="9"  class="messageData"><div style="cursor:pointer;width:16px;height:16px;margin-left:50%;" class="iconReduceDisplay16" onclick="refreshTodayList(\''.$showAllLib.'\',\'false\')" title="'.i18n('reduceDisplayToday').'">&nbsp;</div></td></tr>';
    }
    echo "</table>";
    echo "</div><br/>";
    echo "</form>";
  }
}

$today=new Today();
$crit=array('idUser'=>$user->id, 'idle'=>'0');
$todayList=$today->getSqlElementsFromCriteria($crit, false, null, 'sortOrder asc');
// initialize if empty
if (count($todayList)==0) {
  Today::insertStaticItems();
  $todayList=$today->getSqlElementsFromCriteria($crit, false, null, 'sortOrder asc');
}
$print=false;
if (isset($_REQUEST['print'])) {
  $print=true;
}
$paramRefreshDelay=Parameter::getUserParameter('todayRefreshDelay');
if (!$paramRefreshDelay) $paramRefreshDelay=5;
$paramScrollDelay=Parameter::getUserParameter('todayScrollDelay');
if (!$paramScrollDelay) $paramScrollDelay=10;
?>

<input type="hidden" name="objectClassManual" id="objectClassManual" value="Today" />
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div style="overflow: <?php echo(!$print)?'auto':'hidden';?>;padding:10px" id="detailDiv" dojoType="dijit.layout.ContentPane" region="center">
    <?php 
    if ($twoCols) {?>
      <table style=""><tr><td style="width:50%; border:1px solid green">
    <?php 
    }
    if (!$print) {?>
    <div class="parametersButton">
      <button id="todayRefreshButton" dojoType="dijit.form.Button"
        showlabel="false" title="<?php echo i18n('enableRefresh');?>"
        style="width: 28px" class="detailButton"
        iconClass="dijitButtonIcon dijitButtonIconRefresh">
        <script type="dojo/connect" event="onClick" args="evt">
        if(typeof refreshEnabled === 'undefined') {
          enterFullScreen();
          if (menuActualStatus == 'visible' || !menuHidden) {
            hideShowMenu(false);
          }
          if (dojo.byId('menuBarShow')) dojo.byId('menuBarShow').style.top='0px';
          if (dojo.byId('statusBarDiv')) dojo.byId('statusBarDiv').style.top='-4px';
          if (dojo.byId('centerDiv')) {
            dojo.byId('centerDiv').style.top='0px';
            var heightCenterDiv = dojo.byId('centerDiv').style.height;
            heightCenterDiv = heightCenterDiv.substring(0,heightCenterDiv.length-2);
            heightCenterDiv =  parseInt(heightCenterDiv)+82;
            dijit.byId("centerDiv").resize({h :heightCenterDiv});
          }
          if (dijit.byId("toolBarDiv")) dijit.byId("toolBarDiv").resize({h :0});
         if (dijit.byId("statusBarDiv"))  dijit.byId("statusBarDiv").resize({h :0});
          var msgParams=new Array();
          msgParams[0]='<?php echo $paramScrollDelay;?>';
          msgParams[1]='<?php echo $paramRefreshDelay;?>';
          showInfo(i18n("enableRefreshDone",msgParams));
          formChanged();
          // Check if old animation is not still running
          var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
          if(typeof myReq !== 'undefined') {
            window.cancelAnimationFrame(myReq);
          }
          function animateScrollReport() {
            function scrollToAnchor(myNode) {
              if(typeof myNode !== 'undefined') {
                dojox.fx.smoothScroll({
                  node: myNode,
                  win: dojo.byId('detailDiv')
                }).play();
              }
            }
            window.requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
            var start = null;
            var reportNodes = dojo.query('#detailDiv .dijitTitlePane');
            var nbReports = reportNodes.length;
            var nbTimes = 1;
            var i = 0;
            function step(timestamp) {
              var progress;
              if(nbReports == 0) { // When refreshing
                reportNodes = dojo.query('#detailDiv .dijitTitlePane');
                nbReports = reportNodes.length;
                requestAnimationFrame(step);
              } else {
                if (start === null) start = timestamp;
                progress = timestamp - start;
                var scrollDelay=<?php echo $paramScrollDelay;?>;
                if (progress > (scrollDelay * 1000 * nbTimes)) {
                  if(i >= reportNodes.length) {
                    i = 0;
                    ++nbTimes;
                  }
                  scrollToAnchor(reportNodes[i]);
                  if(i < reportNodes.length) {
                    ++i;
                    ++nbTimes;
                  }
                  myReq = requestAnimationFrame(step);
                } else {
                  myReq = requestAnimationFrame(step);
                }
              }
            }
            requestAnimationFrame(step);
          }
          animateScrollReport();
          var refreshDelay=<?php echo $paramRefreshDelay;?>;
          refreshEnabled = setInterval(function() {
            formChangeInProgress=false;
            var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
            if(typeof myReq !== 'undefined') {
              window.cancelAnimationFrame(myReq);
            }
            loadMenuBarItem('Today', 'Today', 'tree');
            animateScrollReport();
          }, refreshDelay * 60 * 1000);
        } else {
          exitFullScreen();
          formChangeInProgress=false;
          if (dojo.byId('statusBarDiv')) dojo.byId('statusBarDiv').style.top='30px';
          if (dijit.byId("toolBarDiv")) dijit.byId("toolBarDiv").resize({h :30});
          if (dijit.byId("statusBarDiv")) dijit.byId("statusBarDiv").resize({h :52});
          if (dojo.byId('menuBarShow')) dojo.byId('menuBarShow').style.top='81px';
          if (dojo.byId('centerDiv')) {
            dojo.byId('centerDiv').style.top='81px';
            var heightCenterDiv = dojo.byId('centerDiv').style.height;
            heightCenterDiv = heightCenterDiv.substring(0,heightCenterDiv.length-2);
            heightCenterDiv =  parseInt(heightCenterDiv)-81;
            dojo.byId('centerDiv').style.height=heightCenterDiv+'px';
          }
          showInfo(i18n("disableRefreshDone"));
          clearTimeout(refreshEnabled);
          var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
          if(typeof myReq !== 'undefined') {
            window.cancelAnimationFrame(myReq);
          }
          delete refreshEnabled;
        }
        </script>
      </button>
      <button id="todayParametersButton" dojoType="dijit.form.Button"
        showlabel="false" title="<?php echo i18n('menuParameter');?>"
        style="width: 28px" class="detailButton"
        iconClass="imageColorNewGui iconParameter iconSize22">
        <script type="dojo/connect" event="onClick" args="evt">
          loadDialog('dialogTodayParameters', null, true);
        </script>
      </button>
      <button id="todayPrintButton" dojoType="dijit.form.Button"
        showlabel="false" title="<?php echo i18n('print');?>"  class="detailButton"
        iconClass="dijitButtonIcon dijitButtonIconPrint">
        <script type="dojo/connect" event="onClick" args="evt">
          showPrint('../view/today.php');
        </script>
      </button>    
    </div>    
    <?php 
    } 
    $titlePane="Today_message";
    if (! isNewGui() and (!$print or !array_key_exists($titlePane, $collapsedList))) {
      if (!$print) {?>   
      <div dojoType="dijit.TitlePane"
        open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
        id="<?php echo $titlePane;?>"
        onHide="saveCollapsed('<?php echo $titlePane;?>');"
        onShow="saveExpanded('<?php echo $titlePane;?>');"
        title="<?php echo i18n('menuMessage');?>">  
      <?php 
      } else {?>
      <div class="section"><?php echo i18n('menuMessage');?></div>
      <br />
      <div>    
      <?php
      }   
      //showMessages();
      ?>
      </div>
      <br /><?php
    }
    $paramFirstPage=Parameter::getUserParameter('startPage');
    if ($paramFirstPage=='startGuide.php' and !$print) {
      $titlePane="Today_startGuide";
      ?>
      <div dojoType="dijit.TitlePane"
          open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
          id="<?php echo $titlePane;?>"
          onHide="saveCollapsed('<?php echo $titlePane;?>');"
          onShow="saveExpanded('<?php echo $titlePane;?>');"
          title="<?php echo i18n('startGuideTitle');?>">  
        <?php include "startGuide.php";?>
      </div>
      <br />
    <?php
    }
    foreach ($todayList as $todayItem) {
      if ($todayItem->scope=='static' and $todayItem->staticSection=='Projects') {
        $titlePane="Today_project";
        if (!$print or !array_key_exists($titlePane, $collapsedList)) {
          if (!$print) {?> 
            <div dojoType="dijit.TitlePane"
              open="<?php echo ( array_key_exists($titlePane, $collapsedList)?'false':'true');?>"
              id="<?php echo $titlePane;?>"
              onHide="saveCollapsed('<?php echo $titlePane;?>');"
              onShow="saveExpanded('<?php echo $titlePane;?>');"
              title="<?php echo i18n('menuProject');?>">
          <?php 
          } else {?>
            <div class="section"><?php echo i18n('menuProject');?></div><br />
            <div>    
          <?php
          }
          showProjects();
          ?>
          </div>
          <br /><?php
        }
      } else if ($todayItem->scope=='static' and $todayItem->staticSection=='AssignedTasks') {
        showAssignedTasks();
      } else if ($todayItem->scope=='static' and $todayItem->staticSection=='ResponsibleTasks') {
        showResponsibleTasks();
      } else if ($todayItem->scope=='static' and $todayItem->staticSection=='AccountableTasks') {
        showAccountableTasks();
      } else if ($todayItem->scope=='static' and $todayItem->staticSection=='IssuerRequestorTasks') {
        showIssuerRequestorTasks();
      } else if ($todayItem->scope=='static' and $todayItem->staticSection=='Documents') {
        showDocuments();
      } else if ($todayItem->scope=='static' and $todayItem->staticSection=='ProjectsTasks') {
        if ($profile->profileCode=='PL') {
          showProjectsTasks();
        }
      } else if ($todayItem->scope=='report') {
        $rpt=new Report($todayItem->idReport);
        $titlePane="Today_report_".$todayItem->id;
        if (!$print or !array_key_exists($titlePane, $collapsedList)) {
          if (!$print) {
            // echo '<div id="'.$titlePane.'_wait">... loading...</div>';
            echo '<div dojoType="dijit.TitlePane" style="overflow-x:auto"';
            echo ' open="'.(array_key_exists($titlePane, $collapsedList)?'false':'true').'"';
            echo ' id="'.$titlePane.'"';
            echo ' title="'.i18n('colReport').' &quot;'.i18n($rpt->name).'&quot;" >';
            echo ' <script type="dojo/connect" event="onHide" args="evt">';
            echo ' saveCollapsed("'.$titlePane.'");';
            echo ' setTimeout(\'dijit.byId("'.$titlePane.'").set("content","");\',100);';
            echo ' </script>';
            echo ' <script type="dojo/connect" event="onShow" args="evt">';
            echo '   saveExpanded("'.$titlePane.'");';
            $params=TodayParameter::returnReportParameters($rpt);
            $paramsToday=TodayParameter::returnTodayReportParameters($todayItem);
            foreach ($paramsToday as $pName=>$pValue) {
              $params[$pName]=$pValue;
            }
            $urlParam=((strpos($rpt->file, '?')>0)?'&':'?')."fromToday=true";
            foreach ($params as $paramName=>$paramValue) {
              $urlParam.=($urlParam or strpos($rpt->file, '?')>0)?'&':'?';
              $urlParam.=$paramName.'='.$paramValue;
            }
            echo '   loadReport("../report/'.$rpt->file.$urlParam.'","'.$titlePane.'");';
            echo ' </script>';
            echo '<img src="../view/css/images/treeExpand_loading.gif" />';
          } else {
            echo '<div class="section">'.i18n('colReport').' &quot;'.i18n($rpt->name).'&quot;</div><br/><div>';
            $params=TodayParameter::returnReportParameters($rpt);
            $paramsToday=TodayParameter::returnTodayReportParameters($todayItem);
            foreach ($paramsToday as $pName=>$pValue) {
              $params[$pName]=$pValue;
            }
            $urlParam="";
            foreach ($params as $paramName=>$paramValue) {
              $_REQUEST[$paramName]=$paramValue;
            }
            $reportFile=explode('?', $rpt->file);
            include '../report/'. $reportFile[0];
	    }
	  	echo '</div>';
	  	echo '<br/>';
	  }
	
  }

} 
   if ($twoCols) {?>
	    </td>
	    <td style="width:100px;border:1px solid red;vertical-align:top;">
	    <div style="height:500px">
	    <?php include('../view/activityStreamList.php');?>
	    </div>
	  	</td>
	    </tr></table>
	  <?php 
	  }	  ?>
  </div>
</div>