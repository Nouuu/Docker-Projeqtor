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
require_once "../tool/formatter.php";

$displayNothing = false;
$currentYear=strftime("%Y");
$currentMonth = strftime("%m");
$currentUser= getCurrentUserId();
$resource= new Resource($currentUser);
$team=($resource->idTeam!="")?$resource->idTeam:0;
$orga=($resource->idOrganization!="")?$resource->idOrganization:0;
$user= new User($currentUser);
$lstProj=$user->getAffectedProjects();
$userName=($team==0 and $orga==0)?$currentUser:0;

?>

<div dojoType="dijit.layout.BorderContainer" id="consultationPlannedWorkManualParamDiv" name="consultationPlannedWorkManualParamDiv">  
  <div dojoType="dijit.layout.ContentPane" region="top" id="consPlannedWorkManualButtonDiv" class="listTitle" splitter="false" >
      <form dojoType="dijit.form.Form" name="listFormConsPlannedWorkManual" id="listFormConsPlannedWorkManual" action="" method="post" >
      <input id="reportFile" name="reportFile" value="showIntervention" hidden/>
      <input id="reportId" name="reportId" value="109" hidden/>
      <table width="100%" height="64px" class="listTitle">
        <tr height="32px">
          <td width="50px" align="center">
            <?php echo formatIcon('ConsultationPlannedWorkManual', 32, null, true);?>
          </td>
          <td width="300px" > 
            <span class="title"><?php echo i18n('menuConsultationPlannedWorkManual');?></span>
          </td>
          <td>   
            <table>
             <tr>
             <!-- Selector user -->
               <td nowrap="nowrap" style="text-align: right;">
                  <?php echo i18n("colIdResource");?> &nbsp;
                  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                    style="width: 175px;"
                    name="userName" id="userName"
                    <?php echo ($team==0 and $orga==0)?"readonly":autoOpenFilteringSelect();?>
                    value="<?php echo ($team==0 and $orga==0)?$currentUser:0;?>">
                      <script type="dojo/method" event="onChange" >
                        refreshConsultationPlannedWorkManualList();
                        saveDataToSession();
                      </script>
                  <option value=""></option>
                  <option value="<?php echo $currentUser?>"><span><?php echo htmlEncode($resource->name)?></span></option>
                  </select>
               </td>
               <!-- Selector Team -->
               <td>
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo i18n("team");?> &nbsp;
              <td nowrap="nowrap" style="text-align: right;">
                  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                    style="width: 175px;" name="idTeam" id="idTeam" readonly
                    value="<?php echo $team;?>">
                      <?php htmlDrawOptionForReference('idTeam', null)?>
                  </select>
               </td>
              <td>
              <!-- Selector organization -->
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo i18n("colIdOrganization");?> &nbsp;
              </td>
              <td nowrap="nowrap" style="text-align: right;">
                  <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                    style="width: 175px;" name="idOrganization" id="idOrganization" readonly
                    value="<?php echo $orga;?>">
                      <?php htmlDrawOptionForReference('idOrganization', null)?>
                  </select>
               </td>
             </tr>
            </table>
          </td>
        </tr>
        <tr height="32px"  >
          <td colspan="2">
            <table width="100%"  >
              <tr height="27px">
                <td style="min-width:32px"> 
                  <button id="addTodayButton" dojoType="dijit.form.Button" showlabel="false" onclick="saveReportInToday();"
                    title="<?php echo i18n('showInToday');?>"
                    iconClass="dijitButtonIcon dijitButtonIconToday" class="detailButton">
                  </button> 
                  <button title="<?php echo i18n('print')?>"  
                   dojoType="dijit.form.Button" 
                   id="printButton" name="printButton"
                   iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" showLabel="false">
                    <script type="dojo/method" event="onClick" args="evt">
                      showPrint('../report/plannedWorkManual.php?idProject='+dijit.byId('idProject').get('value')+'&idResource='+dijit.byId('userName').get('value')+'&idTeam='+dijit.byId('idTeam').get('value')+'&idOrganization='+dijit.byId('idOrganization').get('value')+'&yearSpinner='+dijit.byId('yearSpinner').get('value')+'&monthSpinner='+dijit.byId('monthSpinner').get('value'), 'print');
                    </script>
                  </button>   
                </td>
              </tr>
            </table>
          </td>
          <td>
            <table>
               <tr>
                 <td nowrap="nowrap" style="text-align: right;">
                    <?php echo i18n("colIdProject");?> &nbsp;
                    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                      style="width: 175px;"
                      name="idProject" id="idProject"
                      <?php echo autoOpenFilteringSelect();?>
                      value="<?php if(sessionValueExists('idProjectCons')){
                                    $idProject =  getSessionValue('idProjectCons');
                                    echo $idProject;
                                   }else{
                                    echo 0;
                                   }?>">
                        <script type="dojo/method" event="onChange" >
                          saveDataToSession("idProjectCons",dijit.byId('idProject').get('value'),true);
                          refreshConsultationPlannedWorkManualList();
                        </script>
                         <option value=""></option>
                        <?php
                          foreach ($lstProj as $key => $val){
                            echo '<option  value="' . $key . '"';
                            echo '><span >'. htmlEncode($val) . '</span></option>';
                          }
                        ?>  
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
                  value="<?php if(sessionValueExists('yearSpinnerCons')){
                                  $yearSpinner = getSessionValue('yearSpinnerCons') ;
                                  echo $yearSpinner;
                                }else{
                                  echo $currentYear;    
                                }?>" 
                  smallDelta="1"
                  id="yearSpinner" name="yearSpinner" >
                  <script type="dojo/method" event="onChange" >
                   saveDataToSession("yearSpinnerCons",dijit.byId('yearSpinner').get('value'),false);
                   refreshConsultationPlannedWorkManualList();
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
                   value="<?php if(sessionValueExists('monthSpinnerCons')){
                                  $monthSpinner = getSessionValue('monthSpinnerCons') ;
                                  echo $monthSpinner;  
                                 }else{
                                  echo $currentMonth;    
                                 }?>" 
                   smallDelta="1"
                   id="monthSpinner" name="monthSpinner" >
                   <script type="dojo/method" event="onChange" >
                var year=dijit.byId('yearSpinner').get('value');
                if (this.value==13) {
                  dijit.byId('monthSpinner').set('value','01');
                  year=parseInt(year)+1;
                  dijit.byId('yearSpinner').set('value',year);
                } else if (this.value==0) {
                  dijit.byId('monthSpinner').set('value','12');
                  year=parseInt(year)-1;
                  dijit.byId('yearSpinner').set('value',year);
                }
                saveDataToSession("monthSpinnerCons",dijit.byId('monthSpinner').get('value'),false);
                refreshConsultationPlannedWorkManualList();
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
    $size=30;
    PlannedWorkManual::setSize($size);
    ?>
    <!-- body with table reaonly -->
    <div id="fullConsPlannedWorkManualList" name="fullConsPlannedWorkManualList" dojoType="dijit.layout.ContentPane" region="center" splitter="false">
      <div dojoType="dijit.layout.BorderContainer" >
        <div  dojoType="dijit.layout.ContentPane" region="top" splitter="true" style="height:30%">
        <?php  $listResource = array();
              $resourceId = null;$inIdTeam = null;$inIdOrga = null;$onlyRes = false;
              if($userName!=0){
                $resourceId = $currentUser;
              }
              if($orga!=0){
                $inIdOrga = $orga;
              }
              if($team!=0){
                $inIdTeam = $team;
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
                }
              }?>
    
          <div id="activityTableCons" name="activityTableCons" style="margin:20px;min-width:1575px">
          <?php if(!$displayNothing){
                  if(isset($idProject)){
                    if(trim($idProject)==''){
                      PlannedWorkManual::drawActivityTable(null,$yearSpinner.$monthSpinner,true);
                    }else{
                      PlannedWorkManual::drawActivityTable($idProject,$yearSpinner.$monthSpinner,true);
                    }
                  }else{
                    PlannedWorkManual::drawActivityTable(null,$yearSpinner.$monthSpinner,true);
                  }
               } ?>
          </div>
        </div>
        <div  dojoType="dijit.layout.ContentPane" region="center" style="overflow:auto">
          <div style="position: absolute; left:20px;top:20px;">
          <?php 
            if(!$displayNothing){
              //MODALITES
              InterventionMode::drawList(true);
            }
          ?>
          </div>
          <div id="consPlannedWorkManualInterventionDiv"  name="consPlannedWorkManualInterventionDiv" style="min-width:1123px;left:485px;top:20px;position:absolute;">
            
                  <?php 
                  if(!$displayNothing){
                    $listMonth=array($yearSpinner.$monthSpinner);
                    if(!$onlyRes){
                      foreach ($listResource as $id=>$val){
                        $listResource[$id]=$val->id;
                      } 
                    }
                  } ?>
               
                  <?php //TAB RESOURCES
                  if(!$displayNothing){
                    PlannedWorkManual::drawTable('intervention',$listResource, $listMonth, null, true);
                  } ?>
          </div>
        </div>
      </div>
    </div>
  </div>  
</div>
