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
scriptLog('   ->/view/autoSendReportList.php');

$user=getSessionUser();
$userName=$user->id;
$idReceiver=$user->id;
$showValidated = '';
$showSubmitted = '';
$currentDay=date('Y-m-d');
$currentWeek = weekNumber(getSessionValue('weekImputationValidation'));
$currentYear = date('Y',strtotime(getSessionValue('weekImputationValidation')));
$currentMonth = date('m',strtotime(getSessionValue('weekImputationValidation')));
if ($currentWeek==1 and $currentMonth>10 ) {
	$currentYear+=1;
}
if ($currentWeek>50 and $currentMonth==1 ) {
	$currentYear-=1;
}
$firstDay = date('Y-m-d', firstDayofWeek($currentWeek, $currentYear));
$lastDay = lastDayofWeek(weekNumber($currentDay), date('Y',strtotime($currentDay)));
//style="padding-left:10px;"
?>
<div class="container" dojoType="dijit.layout.BorderContainer" id="autoSendReportParamDiv" name="autoSendReportParamDiv">  
  <div dojoType="dijit.layout.ContentPane" region="top" id="autoSendReportButtonDiv" class="listTitle" >
  <form dojoType="dijit.form.Form" name="autoSendReportListForm" id="autoSendReportListForm" action="" method="post" >
  <input type="hidden" id="idSendReport" name="idSendReport" value="" />
  <table width="100%" height="64px" class="listTitle">
    <tr height="32px">
    <td style="vertical-align:top;min-width:100px;width:20%;">
      <table >
		    <tr height="32px">
  		    <td width="50px" align="center">
            <?php echo formatIcon('AutoSendReport', 32, null, true);?>
          </td>
          <td width="200px"><span class="title"><?php echo i18n('menuAutoSendReport');?></span></td>
  		  </tr>
  		  <tr height="32px">
          <td>
            <button id="refreshImputationValidationButton" dojoType="dijit.form.Button" showlabel="false"
              title="<?php echo i18n('buttonRefreshList');?>"
              iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
              <script type="dojo/method" event="onClick" args="evt">
	             refreshAutoSendReportList(null);
              </script>
            </button> 
          </td>
        </tr>
		  </table>
    </td>
      <td>   
        <table>
         <tr>
           <td nowrap="nowrap" style="text-align: right;padding-right:5px;"><?php echo i18n("colIdUser");?></td>
           <td>
              <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                style="width: 150px;"
                name="userName" id="userName"
                <?php echo autoOpenFilteringSelect();?>
                value="<?php if(sessionValueExists('userName')){
                              $userName =  getSessionValue('userName');
                              echo $userName;
                             }else{
                              echo $userName;
                             }?>">
                  <script type="dojo/method" event="onChange" >
                    saveDataToSession("userName",dijit.byId('userName').get('value'),false);
                    refreshAutoSendReportList(null);
                  </script>
                  <option value=""></option>
                  <?php
                   $specific='imputation';
                   include '../tool/drawResourceListForSpecificAccess.php';?>  
              </select>
           </td>
           
           <td nowrap="nowrap" style="text-align: right;padding-left:10px;padding-right:5px;"><?php echo i18n("colReceiver");?></td>
           <td>
              <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                style="width: 150px;"
                name="idReceiver" id="idReceiver"
                <?php echo autoOpenFilteringSelect();?>
                value="<?php if(sessionValueExists('idReceiver')){
                              $receiver =  getSessionValue('idReceiver');
                              echo $idReceiver;
                             }else{
                              echo $idReceiver;
                             }?>">
                  <script type="dojo/method" event="onChange" >
                    saveDataToSession("idReceiver",dijit.byId('idReceiver').get('value'),false);
                    refreshAutoSendReportList(null);
                  </script>
                  <?php 
                  $specific='imputation';
                  if ($user->allSpecificRightsForProfilesOneOnlyValue($specific, 'ALL')) {?>
                  <option value=""></option>
                  <?php
                  }
                  include '../tool/drawResourceListForSpecificAccess.php';?>  
              </select>
           </td>
         </tr>
        </table>
      </td>
    </table>
      </td>
    </tr>
  </table>
  </form>
  </div>
  <div id="autoSendReportWorkDiv" name="autoSendReportWorkDiv" dojoType="dijit.layout.ContentPane" region="center" >
    <div id="autoSendReportListDiv" name="autoSendReportListDiv">
      <?php AutoSendReport::drawAutoSendReportList($userName, $idReceiver);?>
    </div>
  </div>  
</div>