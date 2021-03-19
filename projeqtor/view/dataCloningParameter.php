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
scriptLog('   ->/view/dataCloningParameter.php');

$user=getSessionUser();
$userName=$user->id;
?>
<div class="container" dojoType="dijit.layout.BorderContainer" id="dataCloningParameterTopDiv" name="dataCloningParameterTopDiv">  
  <div dojoType="dijit.layout.ContentPane" region="top" id="dataCloningParameterButtonDiv">
  <form dojoType="dijit.form.Form" name="dataCloningParameterForm" id="dataCloningParameterForm" action="" method="post" >
  <table width="100%" class="listTitle">
    <tr>
	    <td width="50px" align="center">
        <?php echo formatIcon('DataCloningParameter', 32, null, true);?>
      </td>
      <td width="200px"><span class="title"><?php echo i18n('menuDataCloningParameter');?></span></td>
      <td align="right" style="padding-right:1%;">
        <button id="saveParameterButton" dojoType="dijit.form.Button"
          showlabel="false"
          title="<?php echo i18n('buttonSaveParameters');?>"
          iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton">
          <script type="dojo/connect" event="onClick" args="evt">              
                submitForm("../tool/saveParameter.php","resultDivMain", "parameterForm", true);
              </script>
        </button>
      </td>
    </tr>
  </table>
  </form>
  </div>
  <div id="dataCloningParameterDiv" name="dataCloningParameterDiv" dojoType="dijit.layout.ContentPane" region="center" >
    <form dojoType="dijit.form.Form" name="parameterForm" id="parameterForm" action="" method="post" >
      <input type="hidden" name="parameterType" value="dataCloning" />
      <div id="dataCloningParameterCenterDiv" name="dataCloningParameterCenterDiv">
        <?php DataCloning::drawDataCloningParameter();?>
      </div>
    </form>
  </div>  
</div>