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
scriptLog('   ->/view/dataCloningList.php');

$user=getSessionUser();
if(sessionValueExists('userName')){
  $userName=getSessionValue('userName');
}else{
  $userName=$user->id;
}
$userProfil = new Profile($user->idProfile);
$versionCode=$version;
$showClosed = 0;
$showClosed = Parameter::getUserParameter('dataCloningShowClosed');
$dataCloning = new DataCloning();
$versionCodeList=array($versionCode);
if($dataCloning->getVersionCodeList()){
  $versionCodeList=$dataCloning->getVersionCodeList();
}

$date = date('Y-m-d');
$addDate =  addDaysToDate(date('Y-m-d'), 1);
$wherePerDay = "requestedDate > '$date' and requestedDate < '$addDate' ";
$dataCloningCountPerDay = $dataCloning->countSqlElementsFromCriteria(null, $wherePerDay);
$dataCloningPerDay = Parameter::getGlobalParameter('dataCloningPerDay');
$dataCloningCount = i18n('colDataCloningCount', array($dataCloningPerDay-$dataCloningCountPerDay, $dataCloningPerDay));
?>
<div class="container" dojoType="dijit.layout.BorderContainer" id="dataCloningParamDiv" name="dataCloningParamDiv"> 
  <div dojoType="dijit.layout.ContentPane" region="top" id="dataCloningButtonDiv" class="listTitle" >
  <form dojoType="dijit.form.Form" name="dataCloningListForm" id="dataCloningListForm" action="" method="post" >
  <table width="100%" height="32px" class="listTitle">
    <tr height="32px">
    <td style="vertical-align:top;min-width:100px;width:20%;">
      <table >
		    <tr height="32px">
  		    <td style="width:50px;min-width:50px" align="center">  
            <?php echo formatIcon('DataCloning', 32, null, true);?>
          </td>
          <td width="200px"><span class="title"><?php echo i18n('menuDataCloning');?></span></td>
  		  </tr>
		  </table>
    </td>
      <td>   
        <table>
         <tr>
           <td nowrap="nowrap" style="text-align: right;padding-right:5px;padding-left:20px;"><?php echo i18n("colIdUser");?></td>
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
                    refreshDataCloningList();
                  </script>
                  <option value=""></option>
                  <?php
                   $specific='dataCloningRight';
                   include '../tool/drawResourceListForSpecificAccess.php';?>  
              </select>
           </td>
           <td nowrap="nowrap" style="text-align: right;padding-left:10px;padding-right:5px;"><?php echo i18n("colVersion");?></td>
           <td>
              <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
                style="width: 150px;"
                name="versionCode" id="versionCode"
                <?php echo autoOpenFilteringSelect();?>
                value="<?php if(sessionValueExists('versionCode')){
                              $versionCode =  getSessionValue('versionCode');
                              echo $versionCode;
                             }else{
                              echo $versionCode;
                             }?>">
                  <script type="dojo/method" event="onChange" >
                    saveDataToSession("versionCode",dijit.byId('versionCode').get('value'),false);
                    refreshDataCloningList();
                  </script>
                  <option value=""></option>
                  <?php
                    foreach ($versionCodeList as $version){
                      ?><option value="<?php echo $version;?>"><?php echo $version;?></option><?php
                    }
                  ?>
              </select>
           </td>
         </tr>
        </table>
      </td>
      <td style="text-align: right; align: right;">
        <table width="100%">
          <tr>
            <td width="10%" nowrap="nowrap">
              <div id="dataCloningRequestorCount" style="font-weight:bold;">
                <?php echo $dataCloningCount;?>
              </div>
            </td>
            <td width="90%" nowrap="nowrap" style="padding-right:2%">
              <?php echo i18n("labelShowIdle");?>
              <?php htmlDrawSwitch('listShowIdle', ($showClosed=='1')?1:0);?>
              <div title="<?php echo i18n('showIdleElements')?>" dojoType="dijit.form.CheckBox" 
                 class="whiteCheck" type="checkbox" id="listShowIdle" name="listShowIdle"
                 style="<?php if (isNewGui()) echo 'display:none;';?>"
                <?php if ($showClosed=='1') { echo ' checked="checked" '; }?> >
                <script type="dojo/method" event="onChange" >
                  saveUserParameter('dataCloningShowClosed',((this.checked)?'1':'0'));
                  refreshDataCloningList();
                </script>
              </div>
            </td>
            <td width="10%" nowrap="nowrap" style="test_align:right;padding-right:4px" >
            <?php if($userProfil->profileCode == 'ADM'){?>
              <button id="parameterDataCloning" dojoType="dijit.form.Button" showlabel="false"
              title="<?php echo i18n('menuDataCloningParameter');?>"
              iconClass="imageColorNewGui iconParameter32 iconParameter iconSize32" class="button detailButton">
                <script type="dojo/method" event="onClick" args="evt">
                  loadMenuBarItem('DataCloningParameter', 'DataCloningParameter', 'bar');  
                </script>
              </button>
              <button id="refreshDataCloningButton" dojoType="dijit.form.Button" showlabel="true"
              title="<?php echo i18n('buttonRefreshList');?>"
              iconClass="imageColorNewGui iconButtonRefresh32 iconButtonRefresh iconSize32" class="button detailButton">
              <script type="dojo/method" event="onClick" args="evt">
	             refreshDataCloningList();
              </script>
            </button> 
            <?php }?> 
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  </form>
  </div>
  <div id="dataCloningWorkDiv" name="dataCloningWorkDiv" dojoType="dijit.layout.ContentPane" region="center" >
    <div id="dataCloningListDiv" name="dataCloningListDiv">
      <?php DataCloning::drawDataCloningList($userName, $versionCode);?>
    </div>
  </div>  
</div>