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
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/absenceList.php');
$idProject = "";
$displayNothing = false;
$currentYear=strftime("%Y");
$currentMonth = strftime("%m");
?>

<div dojoType="dijit.layout.BorderContainer" id="plannedWorkManualParamDiv" name="plannedWorkManualParamDiv">  
  <div dojoType="dijit.layout.ContentPane" region="top" id="plannedWorkManualButtonDiv" class="listTitle" splitter="false" >
      <form dojoType="dijit.form.Form" name="listFormPlannedWorkManual" id="listFormPlannedWorkManual" action="" method="post" >
      <table width="100%" height="64px" class="listTitle">
        <tr height="32px">
          <td width="50px" align="center">
            <?php echo formatIcon('PlannedWorkManual', 32, null, true);?>
          </td>
          <td width="300px" > 
            <span class="title"><?php echo i18n('menuPlannedWorkManual');?></span>
          </td>
          <td>   
            <table>
             <tr>
               <td nowrap="nowrap" style="text-align: right;">
                  <?php echo i18n("colIdResource");?> &nbsp;
                  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                    style="width: 175px;"
                    name="userNamePlanned" id="userNamePlanned"
                    <?php echo autoOpenFilteringSelect();?>
                    value="<?php if(sessionValueExists('userNamePlanned')){
                                  $userName =  getSessionValue('userNamePlanned');
                                  echo $userName;
                                 }else{
                                  echo 0;
                                 }?>">
                      <script type="dojo/method" event="onChange" >
                    saveDataToSession("userNamePlanned",dijit.byId('userNamePlanned').get('value'),true);
                    refreshPlannedWorkManualList();
                  </script>
                  <option value=""></option>
                      <?php 
                       $specific='imputation';
                       include '../tool/drawResourceListForSpecificAccess.php';?>  
                  </select>
               </td>
               <td>
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo i18n("team");?> &nbsp;
              <td nowrap="nowrap" style="text-align: right;">
                  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                    style="width: 175px;"
                    name="idTeamPlannedWorkManual" id="idTeamPlannedWorkManual"
                    <?php echo autoOpenFilteringSelect();?>
                    value="<?php if(sessionValueExists('idTeamPlannedWorkManual')){
                                    $team = getSessionValue('idTeamPlannedWorkManual');
                                    echo $team;
                                 }else{
                                    echo 0;
                                 }?>">
                      <script type="dojo/method" event="onChange" >
                    saveDataToSession("idTeamPlannedWorkManual",dijit.byId('idTeamPlannedWorkManual').get('value'),true);
                    refreshPlannedWorkManualList();
                  </script>
                      <?php htmlDrawOptionForReference('idTeam', null)?>
                  </select>
               </td>
              <td>
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo i18n("colIdOrganization");?> &nbsp;
              </td>
              <td nowrap="nowrap" style="text-align: right;">
                  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                    style="width: 175px;"
                    name="idOrganizationPlannedWorkManual" id="idOrganizationPlannedWorkManual"
                    <?php echo autoOpenFilteringSelect();?>
                    value="<?php if(sessionValueExists('idOrganizationPlannedWorkManual')){
                                    $idOrganization =  getSessionValue('idOrganizationPlannedWorkManual');
                                    echo $idOrganization;
                                 }else{
                                    echo 0;
                                 }?>">
                      <script type="dojo/method" event="onChange" >
                    saveDataToSession("idOrganizationPlannedWorkManual",dijit.byId('idOrganizationPlannedWorkManual').get('value'),true);
                    refreshPlannedWorkManualList();
                  </script>
                      <?php htmlDrawOptionForReference('idOrganization', null)?>
                  </select>
               </td>
               <?php if(isNewGui()){?>
               <td style="min-width:200px"> 
                  <button  style="position:absolute;right:40px;top:0;" title="<?php echo i18n('print')?>"  
                   dojoType="dijit.form.Button" 
                   id="printButton" name="printButton"
                   iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" showLabel="false">
                    <script type="dojo/method" event="onClick" args="evt">
                      showPrint('../report/plannedWorkManual.php?idProject='+dijit.byId('idProjectPlannedInt').get('value')+'&idResource='+dijit.byId('userNamePlanned').get('value')+'&idTeam='+dijit.byId('idTeamPlannedWorkManual').get('value')+'&idOrganization='+dijit.byId('idOrganizationPlannedWorkManual').get('value')+'&yearSpinner='+dijit.byId('yearPlannedWorkManual').get('value')+'&monthSpinner='+dijit.byId('monthPlannedWorkManual').get('value'), 'print');
                    </script>
                  </button>
                  <button style="position:absolute;right:0px;top:0;" id="refreshButtonAlwaysAvailable" dojoType="dijit.form.Button" showlabel="false"
                    title="<?php echo i18n('buttonRefreshList');?>"
                    iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
                    <script type="dojo/method" event="onClick" args="evt">
	                   refreshPlannedWorkManualList();
                    </script>
                  </button>
                </td>
               <?php }?>
             </tr>
            </table>
          </td>
        </tr>
        <tr height="32px"  >
          <td colspan="2">
          <?php if(!isNewGui()){?>
            <table width="100%"  >
              <tr height="27px">
                <td style="min-width:200px"> 
                  <button title="<?php echo i18n('print')?>"  
                   dojoType="dijit.form.Button" 
                   id="printButton" name="printButton"
                   iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" showLabel="false">
                    <script type="dojo/method" event="onClick" args="evt">
                      showPrint('../report/plannedWorkManual.php?idProject='+dijit.byId('idProjectPlannedInt').get('value')+'&idResource='+dijit.byId('userNamePlanned').get('value')+'&idTeam='+dijit.byId('idTeamPlannedWorkManual').get('value')+'&idOrganization='+dijit.byId('idOrganizationPlannedWorkManual').get('value')+'&yearSpinner='+dijit.byId('yearPlannedWorkManual').get('value')+'&monthSpinner='+dijit.byId('monthPlannedWorkManual').get('value'), 'print');
                    </script>
                  </button>
                  <button id="refreshButtonAlwaysAvailable" dojoType="dijit.form.Button" showlabel="false"
                    title="<?php echo i18n('buttonRefreshList');?>"
                    iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
                    <script type="dojo/method" event="onClick" args="evt">
	                   refreshPlannedWorkManualList();
                    </script>
                  </button>
                </td>
              </tr>
            </table>
            <?php } ?>
          </td>
          <td>
            <table>
               <tr>
                 <td nowrap="nowrap" style="text-align: right;">
                    <?php echo i18n("colIdProject");?> &nbsp;
                    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                      style="width: 175px;"
                      name="idProjectPlannedInt" id="idProjectPlannedInt"
                      <?php echo autoOpenFilteringSelect();?>
                      value="<?php if(sessionValueExists('idProjectPlannedIntervention')){
                                    $idProject =  getSessionValue('idProjectPlannedIntervention');
                                    echo $idProject;
                                   }else if(sessionValueExists('project') and getSessionValue('project')!="" and  getSessionValue('project')!="*" 
                                       and !isset($idTeam) and !isset($idOrganization) and !isset($idUser) ){
                                     if(strpos(getSessionValue('project'),',')){
                                      echo 0;
                                     }else{
                                       $idProject =  getSessionValue('project');
                                       echo $idProject;
                                     }
                                   }else {
                                    echo 0;
                                   }?>">
                        <script type="dojo/method" event="onChange" >
                    saveDataToSession("idProjectPlannedIntervention",dijit.byId('idProjectPlannedInt').get('value'),true);
                    refreshPlannedWorkManualList();
                  </script>
                        <?php htmlDrawOptionForReference('idProject', null);?>  
                    </select>
                 </td>
                 <td>
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo i18n("year");?> &nbsp;
              </td>
              <td>
                <div style="width:70px; text-align: center; color: #000000;" 
                  dojoType="dijit.form.NumberSpinner" 
                  constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
                  intermediateChanges="true"
                  maxlength="4" class="roundedLeft"
                  value="<?php if(sessionValueExists('yearPlannedWorkManual')){
                                  $yearSpinner = getSessionValue('yearPlannedWorkManual') ;
                                  echo $yearSpinner;
                                }else{
                                  echo $currentYear;    
                                }?>" 
                  smallDelta="1"
                  id="yearPlannedWorkManual" name="yearPlannedWorkManual" >
                  <script type="dojo/method" event="onChange" >
                   saveDataToSession("yearPlannedWorkManual",dijit.byId('yearPlannedWorkManual').get('value'),false);
                   refreshPlannedWorkManualList();
              </script>
                </div>
              </td> 
              <td>
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo i18n("month");?> &nbsp;
              </td>
              <td>
                <div style="width:55px; text-align: center; color: #000000;" 
                   dojoType="dijit.form.NumberSpinner" 
                   constraints="{min:0,max:13,places:0,pattern:'00'}"
                   intermediateChanges="true"
                   maxlength="2" class="roundedLeft"
                   value="<?php if(sessionValueExists('monthPlannedWorkManual')){
                                  $monthSpinner = getSessionValue('monthPlannedWorkManual') ;
                                  echo $monthSpinner;  
                                 }else{
                                  echo $currentMonth;    
                                 }?>" 
                   smallDelta="1"
                   id="monthPlannedWorkManual" name="monthPlannedWorkManual" >
                   <script type="dojo/method" event="onChange" >
                var year=dijit.byId('yearPlannedWorkManual').get('value');
                if (this.value==13) {
                  dijit.byId('monthPlannedWorkManual').set('value','01');
                  year=parseInt(year)+1;
                  dijit.byId('yearPlannedWorkManual').set('value',year);
                } else if (this.value==0) {
                  dijit.byId('monthPlannedWorkManual').set('value','12');
                  year=parseInt(year)-1;
                  dijit.byId('yearPlannedWorkManual').set('value',year);
                }
                saveDataToSession("monthPlannedWorkManual",dijit.byId('monthPlannedWorkManual').get('value'),false);
                refreshPlannedWorkManualList();
              </script>
                </div>
               </td>
               </tr>
              </table>
          </td>
        </tr> 
      </table>
      </form>
    </div>
    <?php 
    if(!isset($yearSpinner))$yearSpinner=$currentYear;
    if(!isset($monthSpinner))$monthSpinner=$currentMonth;
    
    
    $noNeed = false;
    $listResource = array();
    $resourceId = null;$inIdTeam = null;$inIdOrga = null;$onlyRes = false;
    if(isset($userName)){
      $resourceId = trim($userName);
    }
    if(isset($idOrganization)){
      $inIdOrga = trim($idOrganization);
    }
    if(isset($team)){
      $inIdTeam = trim($team);
    }
    if ($resourceId and !$inIdTeam and !$inIdOrga) {
      $listResource[0] = $resourceId;
      $onlyRes=true;
    }else{
      $res = new Resource();
      if(!$resourceId and $inIdTeam and !$inIdOrga){
        $listResource = $res->getSqlElementsFromCriteria(array('idTeam'=>$inIdTeam,'idle'=>'0'));
      }elseif(!$resourceId and !$inIdTeam and $inIdOrga){
        $listResource = $res->getSqlElementsFromCriteria(array('idOrganization'=>$inIdOrga,'idle'=>'0'));
      }elseif($resourceId and $inIdTeam and $inIdOrga){
        $listResource = $res->getSqlElementsFromCriteria(array('id'=>$resourceId,'idTeam'=>$inIdTeam,'idOrganization'=>$inIdOrga,'idle'=>'0'));
      }elseif($resourceId and $inIdTeam and !$inIdOrga){
        $listResource = $res->getSqlElementsFromCriteria(array('id'=>$resourceId,'idTeam'=>$inIdTeam,'idle'=>'0'));
      }elseif($resourceId and !$inIdTeam and $inIdOrga){
        $listResource = $res->getSqlElementsFromCriteria(array('id'=>$resourceId,'idOrganization'=>$inIdOrga,'idle'=>'0'));
      }elseif(!$resourceId and $inIdTeam and $inIdOrga){
        $listResource = $res->getSqlElementsFromCriteria(array('idTeam'=>$inIdTeam,'idOrganization'=>$inIdOrga,'idle'=>'0'));
      }elseif(!$resourceId and !$inIdTeam and !$inIdOrga and trim($idProject)!="" ){
//gautier test
        $listResourceObj = array();
        $project = new Project($idProject);
        $aff = new Affectation();
        $listProj = transformListIntoInClause($project->getRecursiveSubProjectsFlatList(false,true));
        $where = " idProject in ".$listProj;
        $listResources = $aff->getSqlElementsFromCriteria(null,false,$where);
        foreach ($listResources as $valueRes){
          if($valueRes->idResource){
            $listResource[]=$valueRes->idResource;
          }
        }
        $noNeed = true;
      }else{
        $displayNothing = true;
      }
    }
    $size=30;
    PlannedWorkManual::setSize($size);
    $topHeight=Parameter::getUserParameter('contentPanePlanningManualTopAreaHeight');
    if ($topHeight) $topHeight.='px';
    else $topHeight='30%';
    ?>
    <div id="fullPlannedWorkManualList" name="fullPlannedWorkManualList" dojoType="dijit.layout.ContentPane" region="center" splitter="false">
      <div dojoType="dijit.layout.BorderContainer" >
        <div  dojoType="dijit.layout.ContentPane" region="top" splitter="true" style="height:<?php echo $topHeight;?>" id="planningManualTopArea" onscroll="dojo.byId('planningManualBottomArea').scrollLeft=(this.scrollLeft);">
          <script type="dojo/connect" event="resize" args="evt">
            saveContentPaneResizing("contentPanePlanningManualTopAreaHeight", dojo.byId("planningManualTopArea").offsetHeight, true);
         </script>
          <div id="activityTable" name="activityTable" style="margin:20px;min-width:1575px">
          <?php if(!$displayNothing){
                  if(isset($idProject)){
                    if(trim($idProject)==''){
                      PlannedWorkManual::drawActivityTable(null,$yearSpinner.$monthSpinner);
                    }else{
                      PlannedWorkManual::drawActivityTable($idProject,$yearSpinner.$monthSpinner);
                    }
                  }else{
                    PlannedWorkManual::drawActivityTable(null,$yearSpinner.$monthSpinner);
                  }
               } ?>
          </div>
        </div>
        <div  dojoType="dijit.layout.ContentPane" region="center" style="overflow:auto" id="planningManualBottomArea" onscroll="dojo.byId('planningManualTopArea').scrollLeft=(this.scrollLeft);">
          <div style="position: absolute; left:20px;top:20px;">
          <?php 
            if(!$displayNothing){
              //MODALITES
              InterventionMode::drawList();
            }
          ?>
          </div>
          <div id="plannedWorkManualInterventionDiv"  name="plannedWorkManualInterventionDiv" style="min-width:1123px;left:<?php echo (isNewGui())?485:474;?>px;top:20px;position:absolute;">         
                  <?php 
                  if(!$displayNothing){
                    $listMonth=array($yearSpinner.$monthSpinner);
                    if(!$onlyRes and !$noNeed){
                      foreach ($listResource as $id=>$val){
                        $listResource[$id]=$val->id;
                      } 
                    }
                  } ?>
               
                  <?php //TAB RESOURCES
                  if(!$displayNothing){
                    PlannedWorkManual::drawTable('intervention',$listResource, $listMonth, null, false);
                  } ?>
          </div>
        </div>
      </div>
    </div>
  </div>  
</div>
