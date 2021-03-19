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

scriptLog('dynamicDialogAutoSendReport.php');
$user = getSessionUser();
$resourceProfile = new Profile($user->idProfile);

foreach (getUserVisibleResourcesList(true) as $id=>$name){
	if($user->id == $id){
		$userName=$name;
	}
}
$currentDay = date('Y-m-d');

if(sessionValueExists('reportParametersForDialog')){
	$param = getSessionValue('reportParametersForDialog');
}
$idReport = '';
$periodType = '';
$periodValue = '';
$yearSpinner = '';
$monthSpinner = '';
$weekSpinner = '';
$startDate = '';
$endDate = '';

foreach ($param as $name=>$value){
	if($name == 'reportId'){
		$idReport = $value;
	}
	if($name == 'periodType'){
		$periodType = $value;
	}
	if($name == 'periodValue'){
		$periodValue = $value;
	}
	if($name == 'yearSpinner'){
		$yearSpinner = $value;
	}
	if($name == 'monthSpinner'){
		$monthSpinner = $value;
	}
	if($name == 'weekSpinner'){
		$weekSpinner = $value;
	}
}
$sendFrequency = 'everyDays';
$report = new Report($idReport);
$isWorkPlan = (substr($report->file, 0, 18) == 'globalWorkPlanning')?true:false;
?>
  <table>
    <tr>
      <td>
        <form dojoType="dijit.form.Form" id='autoSendReportForm' name='autoSendReportForm' onSubmit="return false;">
          <table width="100%" style="white-space:nowrap">
            <input type="hidden" id="idReport" name="idReport" value="<?php echo $idReport;?>"/>
            <?php if($periodType != ''){?>
            <tr>
              <td class="assignHeader"><?php echo i18n('colParameters');?></td>
            </tr>
            <?php }?>
             <?php if ($yearSpinner != '') {?>
            <tr>
              <td>
                <label for="yearParam" class="dialogLabel" style="margin-top:10px;text-align:right;"><?php echo i18n('year').Tool::getDoublePoint();?></label>
                <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft"
                  style="margin-top:10px;width:120px;" name="yearParam" id="yearParam" <?php echo autoOpenFilteringSelect();?>
                  value="<?php if($yearSpinner >= date('Y')){ echo "current";}else if($yearSpinner <= date('Y')-1){echo "previous";}?>">
                  <option value="current"><?php echo i18n('setToCurrentYear');?></option>
                  <option value="previous"><?php echo i18n('setToPreviousYear');?></option>
                </select>
              </td>
            </tr>
            <?php }?>
            <?php if ($periodType == 'month' and !$isWorkPlan) {?>
            <tr>
              <td>
                <label for="monthParam" class="dialogLabel" style="margin-top:10px;text-align:right;"><?php echo i18n('month').Tool::getDoublePoint();?></label>
                <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft"
                  style="margin-top:10px;width:120px;" name="monthParam" id="monthParam" <?php echo autoOpenFilteringSelect();?>
                  value="<?php if($monthSpinner >= date('m')){ echo "current";}else if($monthSpinner <= date('m')-1){echo "previous";}?>">
                  <option value="current"><?php echo i18n('setToCurrentMonth');?></option>
                  <option value="previous"><?php echo i18n('setToPreviousMonth');?></option>
                </select>
              </td>
            </tr>
            <?php }else if($periodType == 'year' and $monthSpinner != '') { ?>
            <tr>
              <td>
                <label for="monthParam" class="dialogLabel" style="margin-top:10px;text-align:right;"><?php echo i18n('startMonth').Tool::getDoublePoint();?></label>
                <div style="margin-top:10px;width:50px; text-align: center; color: #000000;" 
                   dojoType="dijit.form.NumberSpinner" 
                   constraints="{min:1,max:12,places:0,pattern:'00'}"
                   intermediateChanges="true"
                   maxlength="2"
                   value="<?php echo $monthSpinner;?>" smallDelta="1" class="input roundedLeft"
                   id="monthParam" name="monthParam" >
                 </div>
              </td>
            </tr>
            <?php }?>
            <?php if ($periodType == 'week' and !$isWorkPlan) {?>
            <tr>
              <td>
                <label for="weekParam" class="dialogLabel" style="margin-top:10px;text-align:right;"><?php echo i18n('week').Tool::getDoublePoint();?></label>
                <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft"
                  style="margin-top:10px;width:120px;" name="weekParam" id="weekParam" <?php echo autoOpenFilteringSelect();?>
                  value="<?php if($weekSpinner >= date('W')){ echo "current";}else if($weekSpinner <= date('W')-1){echo "previous";}?>">
                  <option value="current"><?php echo i18n('setToCurrentWeek');?></option>
                  <option value="previous"><?php echo i18n('setToPreviousWeek');?></option>
                </select>
              </td>
            </tr>
            <?php }?>
            <tr>
              <td></br></td>
            </tr>
            <tr>
              <td class="assignHeader"><?php echo ucfirst(i18n('colFrequency'));?></td>
            </tr>
            <tr>
              <td></br></td>
            </tr>
            <tr><td>
              <table width="100%">
                <tr>
                  <td>
                    <div id="radioButtonDiv" name="radioButtonDiv" dojoType="dijit.layout.ContentPane" region="center">
                      <table>
                        <tr style="height:36px">
                          <td>
                            <label for="everyDays" class="dialogLabel " style="text-align:right;"><?php echo i18n('showAllDays').Tool::getDoublePoint();?></label>
                            <input type="radio" data-dojo-type="dijit/form/RadioButton" class="marginLabel"
                              id="everyDays" name="sendFrequency" value="everyDays" <?php if($sendFrequency == 'everyDays'){echo 'checked';}?>
                              onchange="this.checked?refreshRadioButtonDiv():'';" />
                            
                          </td>
                        </tr>
                        <tr style="height:36px">
                          <td>
                            <label for="everyOpenDays" class="dialogLabel " style="text-align:right;"><?php echo i18n('showAllOpenDays').Tool::getDoublePoint();?></label>
                            <input type="radio" data-dojo-type="dijit/form/RadioButton" 
                              id="everyOpenDays" name="sendFrequency" value="everyOpenDays" class="marginLabel"
                              onchange="this.checked?refreshRadioButtonDiv():'';"/>
                            
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="radio" data-dojo-type="dijit/form/RadioButton" 
                              id="everyWeeks" name="sendFrequency" value="everyWeeks"
                              onchange="this.checked?refreshRadioButtonDiv():'';"/>&nbsp;&nbsp;
                            <label for="everyWeeks" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('showAllWeeks').Tool::getDoublePoint();?></label>
                            <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft"
                              style="width:100px;" name="weekFrequency" id="weekFrequency" <?php echo autoOpenFilteringSelect();?>
                              <?php if($sendFrequency != 'everyWeeks'){?> readonly <?php }?>>>
                              <?php echo htmlReturnOptionForWeekdays(1, true);?>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <input type="radio" data-dojo-type="dijit/form/RadioButton" 
                              id="everyMonths" name="sendFrequency" value="everyMonths"
                              onchange="this.checked?refreshRadioButtonDiv():'';"/>&nbsp;&nbsp;
                            <label for="everyMonths" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('showAllMonths').Tool::getDoublePoint();?></label>
                            <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft"
                            style="width:100px;" name="monthFrequency" id="monthFrequency" <?php echo autoOpenFilteringSelect();?>
                            <?php if($sendFrequency != 'everyMonths'){?> readonly <?php }?>>
                            <?php echo AutoSendReport::htmlReturnOptionForMinutesHoursCron(date('d'),false,true);?>
                            </select>
                          </td>
                        </tr>
                      </table>
                    </div>
                  </td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td></br></td>
            </tr>
            <tr>
              <td>
                <label for="sendTime" class="dialogLabel marginLabel" style="text-align:right;"><?php echo ucfirst(i18n('hours')).Tool::getDoublePoint();?></label>
                <div dojoType="dijit.form.TimeTextBox" name="sendTime" id="sendTime"
                    invalidMessage="<?php echo i18n('messageInvalidTime')?>" 
                    type="text" maxlength="5" required="true"
                    style="width:50px; text-align: center;" class="input rounded required" required
                    value="T<?php if(sessionValueExists('sendTime')){echo getSessionValue('sendTime');}else{
                    echo date('H:i');}?>" hasDownArrow="false">
                </div>
              </td>
            </tr>
            <tr>
              <td></br></td>
            </tr>
            <tr>
              <td>
                <label for="name" class="dialogLabel marginLabel" style="text-align:right;"><?php echo ucfirst(i18n('colSendName')).Tool::getDoublePoint();?></label>
                <input data-dojo-type="dijit.form.TextBox"
  				          id="name" name="name"
  				          style="width: 300px;"
  				          maxlength="4000"
  				          class="input" value="<?php if(sessionValueExists('name')){ echo $name;}?>"/>
  				    </td>
            </tr>
            <tr>
              <td></br></td>
            </tr>
            <tr>
              <td class="assignHeader"><?php echo i18n('sectionReceivers');?></td>
            </tr>
            <tr>
              <td></br></td>
            </tr>
            <tr>
              <td>
                <label for="destinationInput" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('sectionReceivers').Tool::getDoublePoint();?></label>
                <select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html"
                style="width: 150px;" name="destinationInput" id="destinationInput" required
                <?php echo autoOpenFilteringSelect();?>
                value="<?php echo $user->id;?>">
                  <option value=""></option>
                  <?php $specific='scheduledReport';
                   include '../tool/drawResourceListForSpecificAccess.php';?>  
                 </select>
  				    </td>
            </tr>
            <tr>
              <td  >
                <label for="otherDestinationInput" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('colOtherReceivers').Tool::getDoublePoint();?></label>
                <textarea type="text" dojoType="dijit.form.Textarea" 
  				          id="otherDestinationInput" name="otherDestinationInput"
  				          style="width: 302px;" maxlength="4000" class="input"></textarea>
  				    </td>
            </tr>
          </table>
        </form>
     </td>
   </tr>
   <tr>
     <td></br></td>
   </tr>
   <table width="100%">
    <tr>
      <td align="center">
        <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogAutoSendReport').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="button" id="dialogAutoSendReportSubmit" type="submit" onclick="saveAutoSendReport();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
  </table>