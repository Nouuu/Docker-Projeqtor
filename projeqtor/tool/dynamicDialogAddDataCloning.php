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

scriptLog('dynamicDialogAddDataCloning.php');
$user = getSessionUser();
$userId = $user->id;
if(sessionValueExists('userName') and getSessionValue('userName') != ''){
  $userId = getSessionValue('userName');
}
$dataCloning = new DataCloning();
$cronExecution = SqlElement::getSingleSqlElementFromCriteria('CronExecution', array('fonctionName'=>'dataCloningCheckRequest'));
$date = date('Y-m-d');
$addDate =  addDaysToDate(date('Y-m-d'), 1);
$wherePerDay = "requestedDate > '$date' and requestedDate < '$addDate'";
$dataCloningCountPerDay = $dataCloning->countSqlElementsFromCriteria(null, $wherePerDay);
$dataCloningCountTotal = $dataCloning->countSqlElementsFromCriteria(array("idle"=>"0", "idResource"=>$userId));
$dataCloningPerDay = Parameter::getGlobalParameter('dataCloningPerDay');
$res = new Affectable($userId);
$dataCloningTotalObj=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther', array("scope"=>"dataCloningTotal", "idProfile"=>$res->idProfile));
$dataCloningTotal = $dataCloningTotalObj->rightAccess;

$plannedDate = date('Y-m-d', $cronExecution->nextTime);
$plannedHours = date('H:i', $cronExecution->nextTime);
$idDataCloningParent = RequestHandler::getId('idDataCloningParent');
?>
  <table>
    <tr>
      <td>
        <form dojoType="dijit.form.Form" id='addDataCloningForm' name='addDataCloningForm' onSubmit="return false;">
          <input type="hidden" id="idDataCloningParent" name="idDataCloningParent" value="<?php echo $idDataCloningParent?>"/>
          <table width="100%" style="white-space:nowrap">
            <tr>
              <td>
                <label for="dataCloningUser" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('colUser').Tool::getDoublePoint();?></label>
                <select dojoType="dijit.form.FilteringSelect" class="input" xlabelType="html"
                style="width: 150px;" name="dataCloningUser" id="dataCloningUser" required
                <?php echo autoOpenFilteringSelect();?>
                value="<?php echo $userId;?>">
                   <script type="dojo/connect" event="onChange" args="evt">
                     refreshDataCloningCountDiv(this.value);
                   </script>
                   <?php $specific='dataCloningRight';
                   include '../tool/drawResourceListForSpecificAccess.php';?>
                 </select>
  			 </td>
            </tr>
            <tr>
             <td></br></td>
           </tr>
            <tr>
              <td>
                <label for="dataCloningName" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('colName').Tool::getDoublePoint();?></label>
                <input data-dojo-type="dijit.form.TextBox"
  				          id="dataCloningName" name="dataCloningName"
  				          style="width: 300px;"
  				          maxlength="30"
  				          class="input required" value=""/>
  		      </td>
            </tr>
            <tr>
             <td></br></td>
           </tr>
            <tr>
              <td>
                <label for="dataCloningPlannedDate" class="dialogLabel marginLabel" style="text-align:right;"><?php echo i18n('colPlannedDate').Tool::getDoublePoint();?></label>
  				      <div dojoType="dijit.form.DateTextBox" disabled
               <?php if (sessionValueExists('browserLocaleDateFormatJs')) {
  							echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
  						 }?>
               id="dataCloningPlannedDate" name="dataCloningPlannedDate"
               type="text" maxlength="10" hasDownArrow=false
               style="width:90px; text-align:center;" class="input rounded"
               value="<?php echo $plannedDate;?>">
               </div>
               <div dojoType="dijit.form.TimeTextBox" name="dataCloningPlannedHours" id="dataCloningPlannedHours" disabled
                    type="text" maxlength="5" style="margin-left:5px;width:50px; text-align: center;" class="input rounded"
                    value="T<?php echo $plannedHours;?>" hasDownArrow="false">';
               </div>
  				    </td>
            </tr>
            <tr>
             <td></br></td>
           </tr>
           <tr>
             <td></br></td>
           </tr>
           <tr>
           <td>
            <div id="labelDataCloningCountDiv" name="labelDataCloningCountDiv" dojoType="dijit.layout.ContentPane" region="center">
             <table align="center" >
              <tr>
                <td style="text-align:center;" class="dialogLabel">
                  <?php echo i18n('colDataCloningCount', array($dataCloningPerDay-$dataCloningCountPerDay, $dataCloningPerDay));?>
                </td>
              </tr>
              <tr>
                <td style="text-align:center;" class="dialogLabel">
                  <?php echo i18n('colDataCloningCountTotal', array($dataCloningTotal-$dataCloningCountTotal, $dataCloningTotal));?>
                </td>
              </tr>
            </table>
           </div>
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
        <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogAddDataCloning').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="button" id="dialogAddDataCloningSubmit" type="submit" onclick="saveDataCloning();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
  </table>