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

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/tool/refreshButtonAutoSendReport.php');

$sendFrequency = '';

foreach ($_REQUEST as $paramName=>$paramValue){
  if($paramName == 'sendFrequency'){
    $sendFrequency = $paramValue;
  }
}
?>
<table>
  <tr>
    <td style="height:36px">
      <label for="everyDays" class="dialogLabel" style="text-align:right;"><?php echo i18n('showAllDays').Tool::getDoublePoint();?></label>
      <input type="radio" data-dojo-type="dijit/form/RadioButton"  class="marginLabel"
        id="everyDays" name="sendFrequency" value="everyDays" <?php if($sendFrequency == 'everyDays'){echo 'checked';}?>
      onchange="this.checked?refreshRadioButtonDiv():'';"/>
    
    </td>
  </tr>
  <tr style="height:36px">
    <td>
      <label for="everyOpenDays" class="dialogLabel" style="text-align:right;"><?php echo i18n('showAllOpenDays').Tool::getDoublePoint();?></label>
      <input type="radio" data-dojo-type="dijit/form/RadioButton"  class="marginLabel"
        id="everyOpenDays" name="sendFrequency" value="everyOpenDays" <?php if($sendFrequency == 'everyOpenDays'){echo 'checked';}?>
      onchange="this.checked?refreshRadioButtonDiv():'';"/>
    
    </td>
  </tr>
  <tr>
    <td>
      <input type="radio" data-dojo-type="dijit/form/RadioButton" 
        id="everyWeeks" name="sendFrequency" value="everyWeeks" <?php if($sendFrequency == 'everyWeeks'){echo 'checked';}?>
      onchange="this.checked?refreshRadioButtonDiv():'';"/>&nbsp;&nbsp;
    <label for="everyWeeks" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('showAllWeeks').Tool::getDoublePoint();?></label>
    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft"
      style="width:100px;" name="weekFrequency" id="weekFrequency" <?php echo autoOpenFilteringSelect();?>
      <?php if($sendFrequency != 'everyWeeks'){?> readonly <?php }?>>>
      <?php echo htmlReturnOptionForWeekdays(1, true);?>
      </select>
     </div>
    </td>
  </tr>
  <tr>
    <td>
      <input type="radio" data-dojo-type="dijit/form/RadioButton" 
        id="everyMonths" name="sendFrequency" value="everyMonths"  <?php if($sendFrequency == 'everyMonths'){echo 'checked';}?>
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