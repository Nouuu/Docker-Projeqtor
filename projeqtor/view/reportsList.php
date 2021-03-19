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
scriptLog('   ->/view/reportsList.php');

//$objectClass='Task';
//$obj=new $objectClass;
?>

  
<div dojoType="dijit.layout.BorderContainer">
<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px">
<table width="100%" height="32px" class="listTitle" >
  <tr height="27px">
    <td width="50px" align="center">
      <?php echo formatIcon('Reports',32,null,true);?>
    </td>
    <td><span class="title"><?php echo i18n('menuReports');?></span></td>
    <td>   
      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
        <table style="width: 100%;">
          <tr>
            <td>
              <input type="hidden" id="objectClass" name="objectClass" value="" /> 
              <input type="hidden" id="objectId" name="objectId" value="" />
              &nbsp;&nbsp;&nbsp;
            </td>
            <td>
            </td>
            <td></td>
            <td style="text-align: right; align: right;">
            </td>
          </tr>
        </table>    
      </form>
    </td>
    <td style="width:36px">
      <button id="gotoAutoSendReportList" dojoType="dijit.form.Button" showlabel="false"
              title="<?php echo i18n('autoSendReportAccess');?>"
              iconClass="imageColorNewGui iconAutoSendReport22 iconAutoSendReport iconSize22" class="detailButton">
              <script type="dojo/method" event="onClick" args="evt">
	             loadContent('../view/autoSendReportList.php','centerDiv');;
              </script>
            </button> 
    </td>
  </tr>
</table>
</div>
<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv" onresize="">
  <script type="dojo/connect" event="resize" args="evt">
   if (dojo.byId('gridContainerDiv') && dojo.byId('gridContainerDiv').offsetHeight) {
     var divHeight=(dojo.byId('gridContainerDiv').offsetHeight)-40+'px';
     dojo.byId('reportMenuList').style.height=divHeight;
     dojo.byId('reportParametersDiv').style.height=divHeight;
   }
  </script>
  <table style="height:100%">
    <tr>
      <td width="5px" height="35px">&nbsp;</td>
      <td class="tabLabel">
        <div style="position:relative;left:100px"><?php echo i18n('colParameters');?></div>
      </td>
    </tr>
    <tr style="height:100%">
      <td valign="top" style="width:40%" >
        <div id="reportMenuList" style="width:558px;overflow-y:auto;overflow-x:hidden;height:96%;">
           <?php include "reportListMenu.php";?>
        </div>
      </td>
      <td ></td>
      <td valign="top">
        <div id="reportParametersDiv" dojoType="dijit.layout.ContentPane" region="right" style="height:100%; overflow-y:auto;"></div>
      </td>   
    </tr>
    
  </table>
</div>
</div>
