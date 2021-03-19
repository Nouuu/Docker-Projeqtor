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
 * Presents an object. 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/dashboardTicketMain.php'); 
  $user=getSessionUser();
  $nbDay=7;
 //FLORENT
  $valFil = RequestHandler::getValue('filterSynthesis');
  if(isset($valFil)){
    if($valFil!=Parameter::getUserParameter("filterSynthesis") ){
      if(Parameter::getUserParameter("filterSynthesis")!=null){
        $valFil="1";
      }
      Parameter::storeUserParameter("filterSynthesis", $valFil);
      $valFil="0";
      if(Parameter::getUserParameter("dashboardTicketMainTabPosition")){
        $tabPosition=Parameter::getUserParameter("dashboardTicketMainTabPosition");
        $tabPosition=json_decode($tabPosition,true);
        $tabPosition['Status']['withParam']=$valFil;
        Parameter::storeUserParameter("dashboardTicketMainTabPosition", json_encode($tabPosition));
      }
    }else{
      $valFil="0";
      Parameter::storeUserParameter("filterSynthesis", $valFil);
      $valFil="1";
      if(Parameter::getUserParameter("dashboardTicketMainTabPosition")){
        $tabPosition=Parameter::getUserParameter("dashboardTicketMainTabPosition");
        $tabPosition=json_decode($tabPosition,true);
        $tabPosition['Status']['withParam']=$valFil;
        Parameter::storeUserParameter("dashboardTicketMainTabPosition", json_encode($tabPosition));
      }
    }
  }
  //END
  
  if(isset($_REQUEST['dashboardTicketMainNumberDay'])){
    $nbDay=$_REQUEST['dashboardTicketMainNumberDay'];
    if(!is_numeric($nbDay))$nbDay=7;
    Parameter::storeUserParameter("dashboardTicketMainNumberDay", $nbDay);
  }
  if(Parameter::getUserParameter("dashboardTicketMainNumberDay")){
    $nbDay=Parameter::getUserParameter("dashboardTicketMainNumberDay");
  }else{
    Parameter::storeUserParameter("dashboardTicketMainNumberDay", $nbDay);
  }
  
    if(Parameter::getUserParameter("dashboardTicketMainTabPosition")){
      $tabPosition=Parameter::getUserParameter("dashboardTicketMainTabPosition");
    }else{
      $tabPosition='
      {
      "orderListLeft":["TicketType","Priority","Product","Component"],
      "orderListRight":["OriginalProductVersion","TargetProductVersion","Contact","Resource","Status"],
      "TicketType":{"title":"dashboardTicketMainTitleType","withParam":true,"idle":true},
      "Priority":{"title":"dashboardTicketMainTitlePriority","withParam":true,"idle":true},
      "Product":{"title":"dashboardTicketMainTitleProduct","withParam":true,"idle":true},
      "Component":{"title":"dashboardTicketMainTitleCompoment","withParam":true,"idle":true},
      "OriginalProductVersion":{"title":"dashboardTicketMainTitleOriginVersion","withParam":true,"idle":true},
      "TargetProductVersion":{"title":"dashboardTicketMainTitleTargetVersion","withParam":true,"idle":true},
      "Contact":{"title":"dashboardTicketMainTitleUser","withParam":true,"idle":true},
      "Resource":{"title":"dashboardTicketMainTitleResponsible","withParam":true,"idle":true},
      "Status":{"title":"dashboardTicketMainTitleStatus","withParam":false,"idle":true}
      }
      ';
      Parameter::storeUserParameter("dashboardTicketMainTabPosition", $tabPosition);
    }
  $addParam=addParametersDashboardTicketMain();
  if($addParam!=""){
    $addParam=', "paramAdd":"'.$addParam.'"';
  }
  $tabPosition=json_decode($tabPosition,true);
  if(isset($_REQUEST['updatePosTab'])){
    $decodeRequest=json_decode($_REQUEST['updatePosTab'],true);
    $tabPosition['orderListLeft']=$decodeRequest['addLeft'];
    $tabPosition['orderListRight']=$decodeRequest['addRight'];
    for ($ite=0; $ite<sizeof($decodeRequest['iddleList']); $ite++){
      $tabPosition[$decodeRequest['iddleList'][$ite]["name"]]["idle"]=$decodeRequest['iddleList'][$ite]["idle"];
    }
    Parameter::storeUserParameter("dashboardTicketMainTabPosition", json_encode($tabPosition));
  }
  //BEGIN - ADD qCazelles - Dashboard : filter by type - Ticket 154
  $filterTypes = '';
  $filterTypesArray = array();
  if (Parameter::getUserParameter("dashboardTicketMainTypes")) {
    $filterTypes = Parameter::getUserParameter("dashboardTicketMainTypes");
    $filterTypesArray = explode(', ', $filterTypes);
  }
  $defaultProject = null;
  if (Project::getSelectedProject(true,true)) {
    $defaultProject = Project::getSelectedProject(true,true);
  } else {
    $table = SqlList::getList ( 'Project', 'name', null );
    $restrictArray = array();
    if (! $user->_accessControlVisibility) {
      $user->getAccessControlRights (); // Force setup of accessControlVisibility
    }
    if ($user->_accessControlVisibility != 'ALL') {
      $restrictArray = $user->getVisibleProjects ( true );
    }
    if (count ( $table ) > 0) {
      foreach ( $table as $idTable => $valTable ) {
        if (count ( $restrictArray ) == 0 or isset ( $restrictArray [$idTable] )) {
          $firstId = $idTable;
          break;
        }
      }
      $defaultProject = $firstId;
    }
  }
  $listRestrictedType = Type::listRestritedTypesForClass('TicketType', $defaultProject, null, null);
  if (count( $listRestrictedType ) == 0) {
    $listType = SqlList::getList ( 'TicketType' );
  } else {
    $listType = array();
    foreach ($listRestrictedType as $idType) {
      $listType[$idType] = SqlList::getNameFromId('Type', $idType);
    }
  }
  if (count($listType) <= 12) {
    $colWidthType = round(80 / count($listType));
  } else {
    $colWidthType = 8;
  }
  $filterOnType = null;
  //if no checkbox is used but the combobox is
  if ($filterTypes == '' and array_key_exists('dashboardTicketMainType', $_REQUEST)) {
    $filterOnType = ($_REQUEST['dashboardTicketMainType'] == 'null') ? '' : $_REQUEST['dashboardTicketMainType'];
  }
  if ($filterTypes != '' or $filterOnType != null) {
    unset($tabPosition['TicketType']);
    if (in_array('TicketType', $tabPosition['orderListLeft'])) {
      unset($tabPosition['orderListLeft'][array_search('TicketType', $tabPosition['orderListLeft'])]);
    } elseif (in_array('TicketType', $tabPosition['orderListRight'])) {
      unset($tabPosition['orderListRight'][array_search('TicketType', $tabPosition['orderListRight'])]);
    }
  }
  //END - ADD qCazelles - Dashboard : filter by type - Ticket 154

  if(isset($_REQUEST['goToTicket'])){
    addParamToUser($user);
  }else{
?>
<div dojo-type="dijit.layout.BorderContainer" class="container">
<input type="hidden" name="objectClassManual" id="objectClassManual" value="DashboardTicket" />
	<div dojo-type="dijit.layout.ContentPane" id="parameterButtonDiv"
		class="listTitle" style="z-index: 9999; overflow: visible" region="top">
		<form dojoType="dijit.form.Form" id="dashboardTicketMainForm" action="" method="post" >
<?php /* CHANGE qCazelles - Dashboard : filter by type - Ticket 154
    Old
    <table width="100%">
    <tr height="32px" >
    <td width="50px" align="center"><?php echo formatIcon('TicketDashboard', 32, null, true);?></td>
    New */
			?>
		<table width="<?php if(!isNewGui()){?>40%<?php }else{ ?>95% <?php }?>">
			<tr height="32px" >
			<?php //Here i correct a bug with standard themes (no icon) ?>
				<td width="50px" align="center"><?php echo formatIcon('DashboardTicket', 32, null, true);?></td>
<?php //END CHANGE qCazelles - Dashboard : filter by type - Ticket 154 ?>
				<td><span class="title"><?php echo i18n('dashboardTicketMainTitle');?>&nbsp;</span>
				</td>
				<?php //BEGIN - ADD qCazelles - Dashboard : filter by type - Ticket 154 ?>
				<?php if(!isNewGui()){ ?>
        <td style="vertical-align: middle; text-align:right;" width="5px">
           <span class="nobr">&nbsp;&nbsp;&nbsp;
          <?php echo i18n("colType");?>
          &nbsp;</span>
        </td>
				<td width="5px">
        	<select title="<?php echo i18n('filterOnType'); ?>" type="text" class="filterField roundedLeft" dojoType="dijit.form.FilteringSelect"
        	<?php echo autoOpenFilteringSelect();
        	if ($filterTypes != '') echo ' readOnly'; ?>
        	id="typeFilter" name="typeFilter" style="width:200px">
              <?php htmlDrawOptionForReference('idTicketType', $filterOnType); ?>
          	<script type="dojo/method" event="onChange" >
              if (this.value != ' ') {
                changeParamDashboardTicket('dashboardTicketMainType=' + this.value);
              } else {
                changeParamDashboardTicket('dashboardTicketMainType=null');
              }
            </script>
           </select>
        </td>
        <td width="36px">
        	<button title="<?php echo i18n('filterOnType');?>"
	             dojoType="dijit.form.Button"
	             id="iconTypeButton" name="iconTypeButton"
	             iconClass="dijitButtonIcon dijitButtonIconFilter" class="detailButton" showLabel="false">
	             <script type="dojo/connect" event="onClick" args="evt">
               <?php if ($filterTypes == '') { ?>
			           if (dijit.byId('barFilterByType').domNode.style.display == 'none') {
							     dijit.byId('barFilterByType').domNode.style.display = 'block';
						     } else {
							     dijit.byId('barFilterByType').domNode.style.display = 'none';
						     }
						     dijit.byId('barFilterByType').getParent().resize();
                 saveDataToSession("displayByTypeList_TicketDashboard", dijit.byId('barFilterByType').domNode.style.display, true);
               <?php } ?>
          		  </script>
	        </button>
		  	</td>
      <?php }else{   	
      $displayTypes=Parameter::getUserParameter("displayByTypeList_TicketDashboard");
		   if (!$displayTypes) $displayTypes='none'; ?>
		  <td align="right">
      <div class="listTitle" id="barFilterByType"
      data-dojo-type="dijit/layout/ContentPane" region="top"
      style="min-height:20px; display: <?php echo $displayTypes;?>">
      	<table>
      		<tr>
      			<td style="font-weight:bold"><?php echo i18n('filterOnType'); ?>&nbsp;&nbsp;</td>
      <?php
      $cptType=0;
      foreach ($listType as $idType => $nameType) {    
        if ($cptType!=0 and $cptType%5==0) echo "</tr><tr><td></td>";
        $cptType++;
    	?>
    				<td>
    					<div dojoType="dijit.form.CheckBox" type="checkbox" <?php echo ((in_array($idType, $filterTypesArray)) ? 'checked' : ''); ?>
    					id="showType<?php echo $idType; ?>" name="showType<?php echo $idType; ?>" title="<?php echo htmlEncode($nameType); ?>"
    					onClick="changeParamDashboardTicket('dashboardTicketMainTypes=<?php echo $idType ?>')"></div>
    					<?php echo htmlEncode($nameType); ?>&nbsp;&nbsp;
    				</td>
    	<?php
    	}
    	?>
      		</tr>
      	</table>
      </div>
      </td>
    <?php }?>
			</tr>
		</table>
		<?php 
		$displayTypes=Parameter::getUserParameter("displayByTypeList_TicketDashboard");
		if (!$displayTypes) $displayTypes='none';
		if(!isNewGui()){
		?>
    <div class="listTitle" id="barFilterByType"
    data-dojo-type="dijit/layout/ContentPane" region="top"
    style="min-height:20px; z-index:999;display: <?php echo $displayTypes;?>">
    	<table style="position:relative;top:2px;left:3px">
    		<tr>
    			<td style="font-weight:bold"><?php echo i18n('filterOnType'); ?>&nbsp;:&nbsp;</td>
    <?php
    $cptType=0;
    foreach ($listType as $idType => $nameType) {
  	?>
  				<td>
  					<div dojoType="dijit.form.CheckBox" type="checkbox" <?php echo ((in_array($idType, $filterTypesArray)) ? 'checked' : ''); ?>
  					id="showType<?php echo $idType; ?>" name="showType<?php echo $idType; ?>" title="<?php echo htmlEncode($nameType); ?>"
  					onClick="changeParamDashboardTicket('dashboardTicketMainTypes=<?php echo $idType ?>')"></div>
  					<?php echo htmlEncode($nameType); ?>&nbsp;&nbsp;
  				</td>
  	<?php
  	}
  	?>
    		</tr>
    	</table>
    </div>
    <?php }?>
    </form>
  </div>
  <?php //END - ADD qCazelles - Dashboard : filter by type - Ticket 154 ?>
	<div dojo-type="dijit.layout.ContentPane" region="center" style="height:100%;overflow:auto;">
	<?php if(!isNewGui()){?>
		<div
			style="width: 97%; margin: 0 auto; height: 90px; padding-bottom: 15px; border-bottom: 1px solid #CCC;">
			<table width="100%" class="dashboardTicketMain">
				<tr>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left" ><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainAllTicket=0')"
									href="#"><?php echo i18n("dashboardTicketMainAllIssues").addSelected("dashboardTicketMainAllTicket",0);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainAllTicket=2')"
									href="#"><?php echo i18n("dashboardTicketMainUnclosed").addSelected("dashboardTicketMainAllTicket",2);?></a></td>
							</tr>
							<tr>
							
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainAllTicket=1')"
									href="#"><?php echo i18n("dashboardTicketMainUnresolved").addSelected("dashboardTicketMainAllTicket",1);?></a></td>
							</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainRecent=1')"
									href="#"><?php echo i18n("dashboardTicketMainAddedRecently").addSelected("dashboardTicketMainRecent",1);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainRecent=2')"
									href="#"><?php echo i18n("dashboardTicketMainResolvedRecently").addSelected("dashboardTicketMainRecent",2);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainRecent=3')"
									href="#"><?php echo i18n("dashboardTicketMainUpdatedRecently").addSelected("dashboardTicketMainRecent",3);?></a></td>
							</tr>
							<tr>
								<td align="left"><?php echo i18n("dashboardTicketMainNumberDay");?>&nbsp;:&nbsp;<div
										dojoType="dijit.form.NumberTextBox"
										id="dashboardTicketMainNumberDay" style="width: 30px"
										onChange="if(isNaN(this.value))dijit.byId('dashboardTicketMainNumberDay').set('value',7);
          loadContent('dashboardTicketMain.php?dashboardTicketMainNumberDay='+dijit.byId('dashboardTicketMainNumberDay').get('value'), 'centerDiv', 'dashboardTicketMainForm');
          "
										value="<?php echo $nbDay;?>"></div></td>
							</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainToMe=1')"
									href="#"><?php echo i18n("dashboardTicketMainAssignedToMe").addSelected("dashboardTicketMainToMe",1);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainToMe=2')"
									href="#"><?php echo i18n("dashboardTicketMainReportedByMe").addSelected("dashboardTicketMainToMe",2);?></a></td>
							</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('dashboardTicketMainUnresolved=1')"
									href="#"><?php echo i18n("dashboardTicketMainUnscheduled").addSelected("dashboardTicketMainUnresolved",1);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardTicket('filterSynthesis=1')"
									href="#"><?php echo i18n("filterSynthesis").addSelected("filterSynthesis",1);?></a></td>
							</tr>
						</table>
					</td>
					<td valign="top" >
						<button id="updateTabDashboardTicketMain"
							dojoType="dijit.form.Button" showlabel="false"
							title="<?php echo i18n('menuParameter');?>"
							iconClass="iconParameter16">
							<script type="dojo/connect" event="onClick" args="evt">
                               dijit.byId('popUpdatePositionTab').show();
                            </script>
						</button>
					</td>
				</tr>
			</table>
		</div>
<?php }else{
  
  $paramDashboardTicketMainAllTicket = RequestHandler::getValue('dashboardTicketMainAllTicket');
  $paramDashboardTicketMainRecent = RequestHandler::getValue('dashboardTicketMainRecent');
  $paramDashboardTicketMainToMe = RequestHandler::getValue('dashboardTicketMainToMe');
  ?>
<div style="width: 97%; margin: 0 auto; height: 90px; padding-bottom: 15px; border-bottom: 1px solid #CCC;">
			<table width="100%" class="dashboardTicketMain">
				<tr>
				<td valign="top" style="width:30%">
						<table>
						<tr height="37px"><td>&nbsp;&nbsp;<?php echo ucfirst(i18n('filterByTicket'));?></td> </tr>
								<tr>
								<?php 
								$paramDashboardTicketMainAllTicket=0;
  				        if(Parameter::getUserParameter("dashboardTicketMainAllTicket")!=null){
  				          $paramDashboardTicketMainAllTicket=Parameter::getUserParameter("dashboardTicketMainAllTicket");
  				        }
								?>
								  <td width="320px">
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainAllTicket=0')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramDashboardTicketMainAllTicket==0){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('AllIssues');?></li>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainAllTicket=2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardTicketMainAllTicket==2){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('unclosed');?></li>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainAllTicket=1')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardTicketMainAllTicket==1){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('Unresolved');?></li>
                    </ul>
								  </td>
								 <?php  $dashboardTicketMainUnresolved=null;
  				        if(Parameter::getUserParameter("dashboardTicketMainUnresolved")!=null){
  				          $dashboardTicketMainUnresolved=Parameter::getUserParameter("dashboardTicketMainUnresolved");
  				        } ?>
								  <td>
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainUnresolved=1')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($dashboardTicketMainUnresolved==1){ echo "data-dojo-props='selected:true'"; }?> ><?php echo ucfirst(i18n("dashboardRequirementMainUnscheduled"));?></li>
                    </ul>
								  </td>
								</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr><td>
							 <table>
						        <tr height="37px">
						          <td>&nbsp;&nbsp;<?php echo i18n('filterDateByTicket');?></td> 
        						  <td style="width:10%;white-space:nowrap;" align="right">
        							 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ucfirst(i18n('since'));?>&nbsp;
        						  </td>
        						  <td align="left">		
                      <div style="width:30px;" class="filterField rounded" dojoType="dijit.form.TextBox" value="<?php echo $nbDay;?>"
                       type="text" id="dashboardTicketMainNumberDay" name="dashboardTicketMainNumberDay" onChange="if(isNaN(this.value))dijit.byId('dashboardTicketMainNumberDay').set('value',7);
                            loadContent('dashboardTicketMain.php?dashboardTicketMainNumberDay='+dijit.byId('dashboardTicketMainNumberDay').get('value'), 'centerDiv', 'dashboardTicketMainForm');">
                      </div>
                      </td>
                      <td style="width:10%;white-space:nowrap;" align="right">
        							 <?php echo i18n('days');?>&nbsp;
        						</td>
										</tr>
							 </table> 
							 </td> </tr>
							<?php 
							    $paramDashboardTicketMainRecent =null;
							    if(Parameter::getUserParameter("dashboardTicketMainRecent")!=null){
  				          $paramDashboardTicketMainRecent=Parameter::getUserParameter("dashboardTicketMainRecent");
  				        } ?>
								  <td>
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainRecent=1')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramDashboardTicketMainRecent==1){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('AddedRecently');?></li>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainRecent=2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardTicketMainRecent==2){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('ResolvedRecently');?></li>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainRecent=3')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardTicketMainRecent==3){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('updatedRecently');?></li>
                    </ul>
								  </td>
						  </tr>
						</table>
					</td>
					<td valign="top" style="width:20%">
						<table>
						<tr height="37px"><td>&nbsp;&nbsp;<?php echo i18n('filterCreateByTicket');?></td> </tr>
							<tr>
								<?php 
							    $paramDashboardTicketMainRecent =null;
							    if(Parameter::getUserParameter("dashboardTicketMainToMe")!=null){
  				          $paramDashboardTicketMainToMe=Parameter::getUserParameter("dashboardTicketMainToMe");
  				        } ?>
								  <td>
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainToMe=1')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramDashboardTicketMainToMe==1){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('AssignedToMe');?></li>
                      <li onClick="changeParamDashboardTicket('dashboardTicketMainToMe=2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardTicketMainToMe==2){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('ReportedByMe');?></li>
                    </ul>
								  </td>
						  </tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
    					<table style="margin-top:6px;">
    				<tr height="25px">
    					<td style="vertical-align: middle; text-align:left;" width="5px">
           <span class="nobr"><?php echo i18n("colType");?></span>
        </td> </tr><tr>
				<td width="5px">
        	<select title="<?php echo i18n('filterOnType'); ?>" type="text" class="filterField roundedLeft" dojoType="dijit.form.FilteringSelect"
        	<?php echo autoOpenFilteringSelect();
        	if ($filterTypes != '') echo ' readOnly'; ?>
        	id="typeFilter" name="typeFilter" style="width:200px">
              <?php htmlDrawOptionForReference('idTicketType', $filterOnType); ?>
          	<script type="dojo/method" event="onChange" >
              if (this.value != ' ') {
                changeParamDashboardTicket('dashboardTicketMainType=' + this.value);
              } else {
                changeParamDashboardTicket('dashboardTicketMainType=null');
              }
            </script>
           </select>
        </td>
        <td width="36px">
        	<button title="<?php echo i18n('filterOnType');?>"
	             dojoType="dijit.form.Button"
	             id="iconTypeButton" name="iconTypeButton"
	             iconClass="dijitButtonIcon dijitButtonIconFilter" class="detailButton" showLabel="false">
	             <script type="dojo/connect" event="onClick" args="evt">
               <?php if ($filterTypes == '') { ?>
			           if (dijit.byId('barFilterByType').domNode.style.display == 'none') {
							     dijit.byId('barFilterByType').domNode.style.display = 'block';
						     } else {
							     dijit.byId('barFilterByType').domNode.style.display = 'none';
						     }
						     dijit.byId('barFilterByType').getParent().resize();
                 saveDataToSession("displayByTypeList_TicketDashboard", dijit.byId('barFilterByType').domNode.style.display, true);
               <?php } ?>
          		  </script>
	        </button>
		  	</td>
    							</tr>
    						</table>
					</td>
					<td valign="top" >
						<button style="margin-top:10px;" id="updateTabDashboardTicketMain" class="resetMargin detailButton notButton"
							dojoType="dijit.form.Button" showlabel="false"
							title="<?php echo i18n('menuParameter');?>"
							iconClass="iconParameter iconSize22  <?php if(isNewGui()){?> imageColorNewGui <?php }?>">
							<script type="dojo/connect" event="onClick" args="evt">
                dijit.byId('popUpdatePositionTab').show();
              </script>
						</button>
						
					</td>
			  </tr>
			</table>

		</div>
<?php } ?>
		<div style="width: 97%; margin: 0 auto; padding-bottom: 50px;">
			<div style="width: 50%; float: left; padding-bottom: 50px;">
      <?php 
      foreach ($tabPosition["orderListLeft"] as $key){
        $nAddP="";
        if($tabPosition[$key]['withParam'])$nAddP=$addParam;
        if($tabPosition[$key]['idle'])echo addTab('{"groupBy":"'.$key.'","withParam":"'.$tabPosition[$key]['withParam'].'","title":"'.$tabPosition[$key]['title'].'"'.$nAddP.'}');
      }
      ?>
    </div>
			<div style="width: 50%; float: left; padding-bottom: 50px;">
      <?php 
      foreach ($tabPosition["orderListRight"] as $key){
        $nAddP="";
        if($tabPosition[$key]['withParam'])$nAddP=$addParam;
        if($tabPosition[$key]['idle'])echo addTab('{"groupBy":"'.$key.'","withParam":"'.$tabPosition[$key]['withParam'].'","title":"'.$tabPosition[$key]['title'].'"'.$nAddP.'}');
      }
      ?>
      </div>
		</div>
	</div>
</div>

<div id="popUpdatePositionTab" dojoType="dijit.Dialog"
	onHide="loadContent('dashboardTicketMain.php', 'centerDiv');" title="<?php echo i18n("listTodayItems");?>">
  <?php createPopUpDnd($tabPosition);?>
</div>
<?php 
  }
  global $total1;
  global $total2;
  $total1=null;
  $total2=null;
function addTab($param){
  global $total1;
  global $total2;
  $param=json_decode($param,true);
  $ajoutGroupBy="t.id".$param["groupBy"];
  $ajoutWhere=" $ajoutGroupBy=a.id ";
  $paramAdd="";
  $total=0;
  $obT=new Ticket();
  $tableName=$obT->getDatabaseTableName();
  if(isset($param['paramAdd'])){
    $paramAdd=$param['paramAdd'];
    if($total1==null){
      $result=Sql::query("SELECT COUNT(*) as nbline FROM $tableName t WHERE t.idProject in ".getVisibleProjectsList(false)." $paramAdd ");
      if (Sql::$lastQueryNbRows > 0) {
        $line = Sql::fetchLine($result);
        $total1=$line['nbline'];
      }
    }
    $total=$total1;
  }else{
    if($total2==null){
      $result=Sql::query("SELECT COUNT(*) as nbline FROM $tableName t WHERE t.idProject in ".getVisibleProjectsList(false));
      if (Sql::$lastQueryNbRows > 0) {
        $line = Sql::fetchLine($result);
        $total2=$line['nbline'];
      }
    }
    $total=$total2;
  }
  $canGoto=true;
  if (! securityCheckDisplayMenu(null,'Ticket') and ! securityCheckDisplayMenu(null,'TicketSimple')) {
    $canGoto=false;
  }
  $result=Sql::query("SELECT COUNT(*) as nbline, $ajoutGroupBy as idneed FROM $tableName t WHERE $ajoutGroupBy is not null AND t.idProject in ".getVisibleProjectsList(false)." $paramAdd GROUP BY $ajoutGroupBy ");
  if ($total > 0) {
    $res=array();
    $totT=0;
    while ($line = Sql::fetchLine($result)) {
      $object= new $param["groupBy"]($line['idneed'],true);
      //BEGIN - ADD qCazelles - Dashboard : filter by type - Ticket 154
      //The name of a entered into service version was not displayed, by definition a TargetProductVersion can't be eis
      if (!$object->id and property_exists($object, '_constructForName')) {
        if ($param["groupBy"]=='TargetProductVersion') $classObject='OriginalProductVersion';
        if ($param["groupBy"]=='TargetComponentVersion') $classObject='OriginalComponentVersion';
        if (isset($classObject) and $classObject) $object=new $classObject($line['idneed'], true);
      }
      //END - ADD qCazelles - Dashboard : filter by type - Ticket 154
      $idU=$object->name;
      if(isset($object->sortOrder)){
        $idU=$object->sortOrder.'-'.$object->id;
      }
      $res[$idU]["name"]=$object->name;
      $res[$idU]["nb"]=$line['nbline'];
      $res[$idU]["id"]=$object->id;
      if(isset($object->color))$res[$idU]["color"]=$object->color;
      $totT+=$line['nbline'];
    }
    $addIfNoParam="";
    if(!$param['withParam'])$addIfNoParam='<span style="font-style:italic;color:#999999;">&nbsp;('.i18n('noFilterClause').')</span>';
    ksort($res);
    echo '<h2 style="color:#333333;font-size:16px;">'.trim(i18n("dashboardTicketMainTitleBase"))." ".(i18n($param["title"])).$addIfNoParam."</h2>";
    echo "<table width=\"95%\" class=\"tabDashboardTicketMain\">";
    echo '<tr><td class="titleTabTicket">'.i18n($param["title"]).'</td><td class="titleTabTicket">'.i18n("dashboardTicketMainColumnCount").'</td><td class="titleTabTicket">'.i18n("dashboardTicketMainColumnPourcent")."</td></tr>";
    foreach ($res as $idSort=>$nbline){
      if ($canGoto) $name='<a href="#" onclick="stockHistory(\'Ticket\',null,\'object\');loadContent(\'dashboardTicketMain.php?goToTicket='.$param["groupBy"].'&val='.$nbline['id'].'\', \'centerDiv\', \'dashboardTicketMainForm\');">'.$nbline["name"].'</a>';
      else $name=$nbline["name"];
      $addColor=$name;
      if(isset($nbline["color"])){
        $addColor="<div style=\"background-color:".$nbline["color"].";border:1px solid #AAAAAA;border-radius:50%;width:20px;height:18px;float:left;\">&nbsp;</div><div style=\"color:".$nbline["color"].";radius:50%;width:10px;height:10px;float:left;\">&nbsp;</div>"
                 ."<div style=\"float:left;\">".$name."</div>";
      }
      echo "  <tr>";
      echo "    <td width=\"50%\">";
      echo $addColor;
      echo "    </td>";
      echo "    <td width=\"10%\">";
      echo $nbline["nb"];
      echo "    </td>";
      echo "    <td width=\"40%\">";
      echo '<div style="background-color:#3c78b5;margin-top: 3px;position:relative;height:13px;width:'.round(100*($nbline["nb"]/$total)).'px;float:left;">&nbsp;</div><div style="position:relative;margin-left:10px;width:50px; float: left;">'.round(100*($nbline["nb"]/$total))." %</div>";
      echo "    </td>";
      echo "  </tr>";
    }
    if($total-$totT>0){
      echo "  <tr>";
      echo "    <td width=\"50%\">";
      if ($canGoto) echo '<a class="styleUDashboard" href="#" onclick="stockHistory(\'Ticket\',null,\'object\');loadContent(\'dashboardTicketMain.php?goToTicket='.$param["groupBy"].'&undefined=true\', \'centerDiv\', \'dashboardTicketMainForm\');">'.i18n("undefinedValue").'</a>';
      else echo i18n("undefinedValue");
      echo "    </td>";
      echo "    <td width=\"10%\">";
      echo '<span>'.($total-$totT).'</span>';
      echo "    </td>";
      echo "    <td width=\"40%\">&nbsp;";
      echo '<div style="background-color:#3c78b5;margin-top: 3px;position:relative;height:13px;width:'.round(100*(($total-$totT)/$total)).'px;float:left;">&nbsp;</div><div style="position:relative;margin-left:10px;width:50px; float: left;">'.round(100*(($total-$totT)/$total))." %</div>";
      echo "    </td>";
      echo "  </tr>";
    }
    echo "  <tr>";
    echo "    <td width=\"50%\">";
    if ($canGoto) echo '<a class="styleADashboard" href="#" onclick="stockHistory(\'Ticket\',null,\'object\');loadContent(\'dashboardTicketMain.php?goToTicket='.$param["groupBy"].'\', \'centerDiv\', \'dashboardTicketMainForm\');">'.i18n("dashboardTicketMainAllIssues").'</a>';
    else echo i18n("dashboardTicketMainAllIssues");
    echo "    </td>";
    echo "    <td width=\"10%\">";
    echo '<span style="font-weight: bold;">'.$total.'</span>';
    echo "    </td>";
    echo "    <td width=\"40%\">&nbsp;";
    echo "    </td>";
    echo "  </tr>";
    echo "</table>";
    echo '<div style="width:95%;height:2px;margin-top:0px;margin-bottom:35px;background-color:#CCCCCC"></div>';
  }else{
    echo '<h2 style="color:#333333;font-size:16px;">'.(substr(i18n("dashboardTicketMainTitleBase"),-1)==" "?i18n("dashboardTicketMainTitleBase"):i18n("dashboardTicketMainTitleBase")." ").(i18n($param["title"]))."</h2>";
    echo '<span style="color:#333333;font-size:14px;font-style: italic;">'.i18n("noDataFound").'</span>';
    echo '<div style="width:95%;height:3px;margin-top:13px;margin-bottom:20px;background-color:#CCCCCC"></div>';
  }
}

function addSelected($param,$value){
  if(Parameter::getUserParameter($param)!=null){
    if(Parameter::getUserParameter($param)==$value){
      return "&nbsp;&nbsp;<img src=\"css/images/iconSelect.png\"/>";
    }
  }
}

function createPopUpDnd($tabPosition){

    if(isNewGui()){
      echo '<table><tr><td style="padding-left:30px">';
      echo ucfirst(i18n("filterSynthesis"));
      echo'   </td>';
      $valueSwitch = "off";
      if(Parameter::getUserParameter("filterSynthesis")!=null){
        $valueSwitchValue=Parameter::getUserParameter("filterSynthesis");
        if($valueSwitchValue)$valueSwitch="on";
      }
      echo' <td>&nbsp;';
      echo '<div  id="showIdleSwitchAS112" name="showIdleSwitchAS112" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value='.$valueSwitch.' leftLabel="" rightLabel="" >';
      echo '<script type="dojo/method" event="onStateChanged" >
                  changeParamDashboardTicket("filterSynthesis=1");
                </script>
                </div>
                </td>';
      echo '</td></tr></table>';
    }
    
  echo '<table><tr><td valign="top"><table id="dndDashboardLeftParameters" jsId="dndDashboardLeftParameters" dojotype="dojo.dnd.Source" dndType="tableauBordLeft"
               withhandles="true" style="width:300px;cellspacing:0; cellpadding:0;" data-dojo-props="accept: [ \'tableauBordRight\',\'tableauBordLeft\' ]"> ';
echo '<tr><td colspan="3">&nbsp;</td></tr>';
foreach ($tabPosition['orderListLeft'] as $tableauBordLeftItem) {
  echo '<tr style="height:24px" id="dialogDashboardLeftParametersRow' .$tableauBordLeftItem. '"
              class="dojoDndItem" dndType="tableauBordLeft" style="height:10px;">';
  echo '<td valign="top" style="padding-right:10px;" class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif">&nbsp;</td>';
  echo '<td valign="top" style="padding-right:10px;"><div id="tableauBordTabIdle' .$tableauBordLeftItem. '" 
                 dojoType="dijit.form.CheckBox" type="checkbox" '.($tabPosition[$tableauBordLeftItem]['idle']?' checked="checked"':'').'>
                </div></td>';
  echo "<td valign=\"top\" style=\"padding-right:10px;\"><span class='nobr'>".(substr(i18n("dashboardTicketMainTitleBase"),-1)==" "?i18n("dashboardTicketMainTitleBase"):i18n("dashboardTicketMainTitleBase")." ").(i18n($tabPosition[$tableauBordLeftItem]['title']))."</span>";
  echo '</td>';
  echo '</tr>';
}
echo '</table></td><td width="20px;"></td>';
echo '<td valign="top"><table id="dndDashboardRightParameters" jsId="dndDashboardRightParameters" dojotype="dojo.dnd.Source" dndType="tableauBordRight"
               withhandles="true" style="width:300px;cellspacing:0; cellpadding:0;" data-dojo-props="accept: [ \'tableauBordRight\',\'tableauBordLeft\' ]">';
echo '<tr><td colspan="3">&nbsp;</td></tr>';
foreach ($tabPosition['orderListRight'] as $tableauBordRightItem) {
  echo '<tr style="height:24px" id="dialogDashboardRightParametersRow' .$tableauBordRightItem. '"
              class="dojoDndItem" dndType="tableauBordRight" style="height:10px;">';
  echo '<td valign="top" style="padding-right:10px;" class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif">&nbsp;</td>';
  echo '<td valign="top" style="padding-right:10px;"><div id="tableauBordTabIdle' .$tableauBordRightItem. '" 
               dojoType="dijit.form.CheckBox" type="checkbox" '.($tabPosition[$tableauBordRightItem]['idle']?' checked="checked"':'').'>
              </div></td>';
  echo "<td valign=\"top\" style=\"padding-right:10px;\"><span class='nobr'>".(substr(i18n("dashboardTicketMainTitleBase"),-1)==" "?i18n("dashboardTicketMainTitleBase"):i18n("dashboardTicketMainTitleBase")." ").(i18n($tabPosition[$tableauBordRightItem]['title']))."</span>";
  echo '</td>';
  echo '</tr>';
}
echo '</table></td></tr>
              <tr><td></td><td></td><td></td></tr>
              <tr><td align="center" colspan="3">
              <button style="margin-right:15px;" class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId(\'popUpdatePositionTab\').hide();">
                '.i18n("buttonCancel").'
              </button>
              <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="confirmChangeTabBordTicketMain" onclick="protectDblClick(this);changeDashboardTicketMainTabPos();return false;">
                '.i18n("buttonOK").'
              </button>
              </td></tr>
              </table>';
}

function addParametersDashboardTicketMain($prefix="t"){
  $user=getSessionUser();
  $result="";
  $allTicket="0";
  if(isset($_REQUEST['dashboardTicketMainAllTicket'])){
    Parameter::storeUserParameter("dashboardTicketMainAllTicket", $_REQUEST['dashboardTicketMainAllTicket']);
  }
  if(Parameter::getUserParameter("dashboardTicketMainAllTicket")!=null){
    $allTicket=Parameter::getUserParameter("dashboardTicketMainAllTicket");
  }else{
    Parameter::storeUserParameter("dashboardTicketMainAllTicket", $allTicket);
  }
  if($allTicket=="1")$result.=" AND $prefix.done=0 ";
  if($allTicket=="2")$result.=" AND $prefix.idle=0 ";

  $recent="0";
  $nbDay=7;
  if(isset($_REQUEST['dashboardTicketMainRecent'])){
    if(Parameter::getUserParameter("dashboardTicketMainRecent")!=null){
      if($_REQUEST['dashboardTicketMainRecent']==Parameter::getUserParameter("dashboardTicketMainRecent"))$_REQUEST['dashboardTicketMainRecent']="0";
    }
    Parameter::storeUserParameter("dashboardTicketMainRecent", $_REQUEST['dashboardTicketMainRecent']);
  }

  if(Parameter::getUserParameter("dashboardTicketMainNumberDay")!=null){
    $nbDay=Parameter::getUserParameter("dashboardTicketMainNumberDay");
  }
  if(Parameter::getUserParameter("dashboardTicketMainRecent")!=null){
    $recent=Parameter::getUserParameter("dashboardTicketMainRecent");
  }
  if (Sql::isPgsql()) {
    if($recent=="1")$result.=" AND $prefix.creationDateTime>=current_date - INTERVAL '" . intval($nbDay) . " day' ";
    if($recent=="2")$result.=" AND $prefix.doneDateTime>=current_date - INTERVAL '" . intval($nbDay) . " day' ";
    if($recent=="3")$result.=" AND $prefix.id IN (SELECT t2.refId FROM history t2 WHERE t2.refId=$prefix.id AND t2.refType='Ticket' AND t2.operationDate>=current_date - INTERVAL '" . intval($nbDay) . " day' ) ";
  } else {
    if($recent=="1")$result.=" AND $prefix.creationDateTime>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY) ";
    if($recent=="2")$result.=" AND $prefix.doneDateTime>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY) ";
    if($recent=="3")$result.=" AND $prefix.id IN (SELECT t2.refId FROM history t2 WHERE t2.refId=$prefix.id AND t2.refType='Ticket' AND t2.operationDate>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY)) ";
  }

  if(isset($_REQUEST['dashboardTicketMainToMe'])){
    if(Parameter::getUserParameter("dashboardTicketMainToMe")!=null){
      if($_REQUEST['dashboardTicketMainToMe']==Parameter::getUserParameter("dashboardTicketMainToMe"))$_REQUEST['dashboardTicketMainToMe']="0";
    }
    Parameter::storeUserParameter("dashboardTicketMainToMe", $_REQUEST['dashboardTicketMainToMe']);
  }

  $toMe="";
  if(Parameter::getUserParameter("dashboardTicketMainToMe")!=null){
    $toMe=Parameter::getUserParameter("dashboardTicketMainToMe");
  }
  if($toMe=="1")$result.=" AND $prefix.idResource=".$user->id." ";
  if($toMe=="2")$result.=" AND $prefix.idUser=".$user->id." ";
  $unresolved="";
  
  if(isset($_REQUEST['dashboardTicketMainUnresolved'])){
    if(Parameter::getUserParameter("dashboardTicketMainUnresolved")!=null){
      if($_REQUEST['dashboardTicketMainUnresolved']==Parameter::getUserParameter("dashboardTicketMainUnresolved"))$_REQUEST['dashboardTicketMainUnresolved']="0";
    }
    Parameter::storeUserParameter("dashboardTicketMainUnresolved", $_REQUEST['dashboardTicketMainUnresolved']);
  }
  if(Parameter::getUserParameter("dashboardTicketMainUnresolved")!=null){
    $unresolved=Parameter::getUserParameter("dashboardTicketMainUnresolved");
  }
  if($unresolved=="1")$result.=" AND $prefix.idTargetProductVersion is null ";
  //Florent
  if(Parameter::getUserParameter("filterSynthesis")== null){
    if(RequestHandler::getValue('filterSynthesis')== null){
      $valFil='1';
      Parameter::storeUserParameter("filterSynthesis", $valFil);
    }
  }
  // end
  //BEGIN - ADD qCazelles - Dashboard : filter by type - Ticket 154
  $filterTypes = '';
  if (Parameter::getUserParameter("dashboardTicketMainTypes")) {
    $filterTypes = Parameter::getUserParameter("dashboardTicketMainTypes");
  }
  if (isset($_REQUEST['dashboardTicketMainTypes'])) {
    if ($filterTypes == '') {
      $filterTypes = $_REQUEST['dashboardTicketMainTypes'];
      $filterTypesArray = array($filterTypes);
    } else {
      $filterTypesArray = explode(', ', $filterTypes);
      if (in_array($_REQUEST['dashboardTicketMainTypes'], $filterTypesArray)) {
        unset($filterTypesArray[array_search($_REQUEST['dashboardTicketMainTypes'], $filterTypesArray)]);
      } else {
        $filterTypesArray[] = $_REQUEST['dashboardTicketMainTypes'];
      }
      $filterTypes = implode(', ', $filterTypesArray);
    }
    Parameter::storeUserParameter("dashboardTicketMainTypes", $filterTypes);
  }
  if ($filterTypes == '' and array_key_exists('dashboardTicketMainType', $_REQUEST)) {
    $filterTypes = ($_REQUEST['dashboardTicketMainType'] == 'null') ? '' : $_REQUEST['dashboardTicketMainType'];
  }
  if ($filterTypes != '') $result .= " AND $prefix.idTicketType in (" . $filterTypes . ")";
  //END - ADD qCazelles - Dashboard : filter by type - Ticket 154
  return $result;
}

function addParamToUser($user){
  $user->_arrayFilters['Ticket']=array();
  $objectClass=$_REQUEST['goToTicket'];
  $obRef=new Ticket();
  $iterateur=0;
  if(isset($_REQUEST['val'])){
    $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='id'.$objectClass;
    $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='=';
    $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=$_REQUEST['val'];
    $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('id'.$objectClass);
    $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']='=';
    $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=SqlList::getNameFromId($objectClass, $_REQUEST['val']);
  }else{
    $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='id'.$objectClass;
    $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='SORT';
    $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']='asc';
    $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('id'.$objectClass);
    $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']=i18n('sortFilter');
    $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=i18n('sortAsc');
    if(isset($_REQUEST['undefined'])){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='id'.$objectClass;
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='is null';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']='';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('id'.$objectClass);
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']=i18n("isNotEmpty");
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']='';
      $iterateur++;
    }
  }
  $iterateur++;
  $tabPosition=Parameter::getUserParameter("dashboardTicketMainTabPosition");
  $tabPosition=json_decode($tabPosition,true);
  if($tabPosition[$objectClass]["withParam"]){
    $allTicket=Parameter::getUserParameter("dashboardTicketMainAllTicket");
    if($allTicket=="0"){
      $user->_arrayFilters['Ticket'][$iterateur]['disp']["attribute"]=i18n('labelShowIdle');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']["operator"]="";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']["value"]="";
      $user->_arrayFilters['Ticket'][$iterateur]['sql']["attribute"]='idle';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']["operator"]='>=';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']["value"]='0';
      $iterateur++;
    }
    if($allTicket=="1"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='done';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='=';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']='0';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('done');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']='=';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=i18n('no');
      $iterateur++;
    }
    if($allTicket=="2"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='idle';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='=';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']='0';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idle');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']="=";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=i18n("yes");
      $iterateur++;
    }
    
    $recent=Parameter::getUserParameter("dashboardTicketMainRecent");
    $nbDay=Parameter::getUserParameter("dashboardTicketMainNumberDay");
    if (preg_match('/[^\-0-9]/', $nbDay) == true) {
      $nbDay="";
    }
    if($recent=="1"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='creationDateTime';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='>=';
      //$user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=(-$nbDay)+'';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('creationDateTime');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']=">=";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=i18n('today').' -'.$nbDay.' '.i18n('days');
      if (Sql::isPgsql()) {
        $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']= "NOW() + INTERVAL '" . (intval($nbDay)*(-1)) . " day'";
      } else {
        $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']= "ADDDATE(NOW(), INTERVAL (" . (intval($nbDay)*(-1)) . ") DAY)";
      }
      $iterateur++;
    }
    
    if($recent=="2"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='doneDateTime';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='>=';
      //$user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=(-$nbDay)+'';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('doneDateTime');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']=">=";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=i18n('today').' -'.$nbDay.' '.i18n('days');
      if (Sql::isPgsql()) {
        $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']= "NOW() + INTERVAL '" . (intval($nbDay)*(-1)) . " day'";
      } else {
        $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']= "ADDDATE(NOW(), INTERVAL (" . (intval($nbDay)*(-1)) . ") DAY)";
      }
      $iterateur++;
    }
      //if($recent=="2")$result.=" AND $prefix.doneDateTime>=NOW() - INTERVAL '" . intval($nbDay) . " day' ";
      //if($recent=="2")$result.=" AND $prefix.doneDateTime>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY) ";
    if($recent=="3"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='id';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='IN';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=i18n("dashboardTicketMainLastUpdate");
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']=">=";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=i18n('today').' -'.$nbDay.' '.i18n('days');
      if (Sql::isPgsql()) {
        $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=" (SELECT t2.refId FROM history t2 WHERE t2.refId=ticket.id AND t2.refType='Ticket' AND t2.operationDate>=NOW() - INTERVAL '" . intval($nbDay) . " day' ) ";
      } else {
        $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=" (SELECT t2.refId FROM history t2 WHERE t2.refId=ticket.id AND t2.refType='Ticket' AND t2.operationDate>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY)) ";
      }
      $iterateur++;
    }
    
    $toMe=Parameter::getUserParameter("dashboardTicketMainToMe");
    if($toMe=="1"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='idResource';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='=';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=$user->id;
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idResource');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']="=";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=$user->name;
      $iterateur++;
    }
    if($toMe=="2"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='idUser';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='=';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']=$user->id;
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idUser');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']="=";
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']=$user->name;
      $iterateur++;
    }
    
    $unresolved=Parameter::getUserParameter("dashboardTicketMainUnresolved");
    if($unresolved=="1"){
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute']='idTargetProductVersion';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator']='is null';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value']='';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idTargetProductVersion');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator']=i18n('isEmpty');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value']='';
      $iterateur++;
    }
    //BEGIN - ADD qCazelles - Dashboard : filter by type - Ticket 154
    $types = '';
    if (Parameter::getUserParameter("dashboardTicketMainTypes") != null) {
      $types = Parameter::getUserParameter("dashboardTicketMainTypes");
      $typesArray = explode(', ', $types);
      $typesNames = '';
      foreach ($typesArray as $idType) {
        $typesNames .= htmlEncode(SqlList::getNameFromId('Type', $idType)) . ', ';
      }
      $typesNames = substr($typesNames, 0, -2);
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['attribute'] = 'idTicketType';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['operator'] = 'IN';
      $user->_arrayFilters['Ticket'][$iterateur]['sql']['value'] = '(' . $types . ')';
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['attribute'] = $obRef->getColCaption('idTicketType');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['operator'] = i18n('amongst');
      $user->_arrayFilters['Ticket'][$iterateur]['disp']['value'] = $typesNames;
      $iterateur++;
    }
    //END - ADD qCazelles - Dashboard : filter by type - Ticket 154
  }else{
    $user->_arrayFilters['Ticket'][$iterateur]['disp']["attribute"]=i18n('labelShowIdle');
    $user->_arrayFilters['Ticket'][$iterateur]['disp']["operator"]="";
    $user->_arrayFilters['Ticket'][$iterateur]['disp']["value"]="";
    $user->_arrayFilters['Ticket'][$iterateur]['sql']["attribute"]='idle';
    $user->_arrayFilters['Ticket'][$iterateur]['sql']["operator"]='>=';
    $user->_arrayFilters['Ticket'][$iterateur]['sql']["value"]='0';
    $iterateur++;
  }
  setSessionUser($user);
  $_REQUEST['objectClass']='Ticket';
  if (! securityCheckDisplayMenu(null,'Ticket') and securityCheckDisplayMenu(null,'TicketSimple')) {
    $_REQUEST['objectClass']='TicketSimple';
  }
  include 'objectMain.php';
}

?>