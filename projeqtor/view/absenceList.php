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

$user=getSessionUser();
$currentYear=strftime("%Y") ;
$yearSpinner = $currentYear;
?>

<div dojoType="dijit.layout.BorderContainer" id="paramDiv" name="paramDiv">  
  <div dojoType="dijit.layout.ContentPane" region="top" id="absenceButtonDiv" class="listTitle" >
  <form dojoType="dijit.form.Form" name="listForm" id="listForm" action="" method="post" >
  <table width="100%" height="64px" class="listTitle">
    <tr height="32px">
      <td width="50px" align="center">
        <?php echo formatIcon('Absence', 32, null, true);?>
      </td>
      <td width="200px" > 
      <?php if(isNewGui()){ ?> <div style="position:absolute;top:7px;"> <?php }?>
        <span class="title"><?php echo i18n('menuAbsence');?></span>
        <?php if(isNewGui()){ ?></div> <?php }?>
      </td>
      <?php if(isNewGui()){ ?><td style="position:absolute;"> <?php }else{ ?><td> <?php }?>
        <table>
         <tr>
           <td nowrap="nowrap" style="text-align: right;">
              <?php echo i18n("colIdResource");?> &nbsp;
              <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                style="width: 150px;"
                name="userName" id="userName"
                <?php echo autoOpenFilteringSelect();?>
                value="<?php if(sessionValueExists('userName')){
                              $userName =  getSessionValue('userName');
                              echo $userName;
                             }else{
                              if($user->isResource){
                                $userName = $user->id;
                              }else{
                                $userName = 0;
                              }
                              echo $userName;
                             }?>">
                  <script type="dojo/method" event="onChange" >
                    saveDataToSession("userName",dijit.byId('userName').get('value'),false);
                    saveDataToSession("inputAssId",'');
                    saveDataToSession('selectAbsenceActivity','');
                    saveDataToSession('inputIdProject','');
                    refreshAbsenceList();
                  </script>
                  <?php 
                   $specific='imputation';
                   include '../tool/drawResourceListForSpecificAccess.php';?>  
              </select>
           </td>
           <td>
           &nbsp;&nbsp; <?php echo i18n("year");?> &nbsp;
          </td>
          <td>
            <div style="width:70px; text-align: center; color: #000000;" 
              dojoType="dijit.form.NumberSpinner" 
              constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
              intermediateChanges="true"
              maxlength="4" class="roundedLeft"
              value="<?php if(sessionValueExists('yearSpinnerAbsence')){
                              $yearSpinner = getSessionValue('yearSpinnerAbsence') ;
                            echo $yearSpinner;
                           }else{
                            echo $currentYear;    
                           }?>" 
              smallDelta="1"
              id="yearSpinner" name="yearSpinner" >
              <script type="dojo/method" event="onChange" >
                   saveDataToSession("yearSpinner",dijit.byId('yearSpinner').get('value'),false);
                   refreshAbsenceList();
              </script>
            </div>
          </td> 
         </tr>
        </table>
      </td>
      <?php if(isNewGui()){?> 
      <td style="position:absolute;top:0px; right:7px;"> 
              <button title="<?php echo i18n('print')?>"  
               dojoType="dijit.form.Button" 
               id="printButton" name="printButton"
               iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" showLabel="false">
                <script type="dojo/method" event="onClick" args="evt">
                  showPrint('../report/absenceReport.php?userName='+dijit.byId('userName').get('value')+'&yearSpinner='+dijit.byId('yearSpinner').get('value'), 'print');
                </script>
              </button>
              <button title="<?php echo i18n('reportPrintPdf')?>"  
               dojoType="dijit.form.Button" 
               id="printButtonPdf" name="printButtonPdf"
               iconClass="dijitButtonIcon dijitButtonIconPdf" class="detailButton" showLabel="false" style="display:none;">
                <script type="dojo/method" event="onClick" args="evt">
                  showPrint('../report/absenceReport.php?userName='+dijit.byId('userName').get('value')+'&yearSpinner='+dijit.byId('yearSpinner').get('value'), 'print', null, 'pdf');
                </script>
              </button>               
              <button id="refreshButtonAlwaysAvailable" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('buttonRefreshList');?>"
                iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
                <script type="dojo/method" event="onClick" args="evt">
	                 refreshAbsenceList();
                </script>
              </button> 
            </td>
      <?php }?>
    </tr>
      <?php if(!isNewGui()){?>
    <tr height="32px"  >
      <td colspan="2">
        <table width="100%"  >
          <tr height="27px">
            <td style="min-width:200px"> 
              <button title="<?php echo i18n('print')?>"  
               dojoType="dijit.form.Button" 
               id="printButton" name="printButton"
               iconClass="dijitButtonIcon dijitButtonIconPrint" class="detailButton" showLabel="false">
                <script type="dojo/method" event="onClick" args="evt">
                  showPrint('../report/absenceReport.php?userName='+dijit.byId('userName').get('value')+'&yearSpinner='+dijit.byId('yearSpinner').get('value'), 'print');
                </script>
              </button>
              <button title="<?php echo i18n('reportPrintPdf')?>"  
               dojoType="dijit.form.Button" 
               id="printButtonPdf" name="printButtonPdf"
               iconClass="dijitButtonIcon dijitButtonIconPdf" class="detailButton" showLabel="false" style="display:none;">
                <script type="dojo/method" event="onClick" args="evt">
                  showPrint('../report/absenceReport.php?userName='+dijit.byId('userName').get('value')+'&yearSpinner='+dijit.byId('yearSpinner').get('value'), 'print', null, 'pdf');
                </script>
              </button>               
              <button id="refreshButtonAlwaysAvailable" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('buttonRefreshList');?>"
                iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
                <script type="dojo/method" event="onClick" args="evt">
	                 refreshAbsenceList();
                </script>
              </button> 
            </td>
          </tr>
        </table>
      </td>
    </tr> 
      <?php }?>
  </table>
  </form>
  </div>
  <div id="fullWorkDiv" name="fullWorkDiv" dojoType="dijit.layout.ContentPane" region="center" >
    <div id="workDiv" name="workDiv">
        <?php Absence::drawActivityDiv($userName, $yearSpinner);?>
    </div>
    <div id="calendarDiv" name="calendarDiv">
        <?php Absence::drawCalandarDiv($userName, $yearSpinner);?>
     </div>
  </div>  
</div>