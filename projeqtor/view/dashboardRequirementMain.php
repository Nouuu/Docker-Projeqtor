<?php
/*
 *  @author: qCazelles - Requirements dashboard - Ticket 90 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/dashboardRequirementMain.php');
  $user=getSessionUser();
  $nbDay=7;
  if(isset($_REQUEST['dashboardRequirementMainNumberDay'])){
    $nbDay=$_REQUEST['dashboardRequirementMainNumberDay'];
    if(!is_numeric($nbDay))$nbDay=7;
    Parameter::storeUserParameter("dashboardRequirementMainNumberDay", $nbDay);
  }
  if(Parameter::getUserParameter("dashboardRequirementMainNumberDay")){
    $nbDay=Parameter::getUserParameter("dashboardRequirementMainNumberDay");
  }else{
    Parameter::storeUserParameter("dashboardRequirementMainNumberDay", $nbDay);
  }
  if(Parameter::getUserParameter("dashboardRequirementMainTabPosition")){
    $tabPosition=Parameter::getUserParameter("dashboardRequirementMainTabPosition");
  }else{
    $tabPosition='
      {
      "orderListLeft":["RequirementType","Priority","Product","Component"],
      "orderListRight":["TargetProductVersion","TargetComponentVersion","Contact","Resource","Status"],
      "RequirementType":{"title":"dashboardTicketMainTitleType","withParam":true,"idle":true},
      "Priority":{"title":"dashboardTicketMainTitlePriority","withParam":true,"idle":true},
      "Product":{"title":"dashboardTicketMainTitleProduct","withParam":true,"idle":true},
      "Component":{"title":"dashboardTicketMainTitleCompoment","withParam":true,"idle":false},
      "TargetProductVersion":{"title":"dashboardTicketMainTitleTargetVersion","withParam":true,"idle":true},
      "TargetComponentVersion":{"title":"dashboardRequirementMainTitleTargetComponentVersion","withParam":true,"idle":false},
      "Contact":{"title":"dashboardTicketMainTitleUser","withParam":true,"idle":true},
      "Resource":{"title":"dashboardTicketMainTitleResponsible","withParam":true,"idle":true},
      "Status":{"title":"dashboardTicketMainTitleStatus","withParam":false,"idle":true}
      }
      ';
    Parameter::storeUserParameter("dashboardRequirementMainTabPosition", $tabPosition);
  }
  $addParam=addParametersDashboardRequirementMain();
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
    Parameter::storeUserParameter("dashboardRequirementMainTabPosition", json_encode($tabPosition));
  }
  $filterTypes = '';
  $filterTypesArray = array();
  if (Parameter::getUserParameter("dashboardRequirementMainTypes")) {
    $filterTypes = Parameter::getUserParameter("dashboardRequirementMainTypes");
    $filterTypesArray = explode(', ', $filterTypes);
  }
  $defaultProject = null;
  if (sessionValueExists ( 'project' ) and getSessionValue ( 'project' ) != '*') {
    $defaultProject = getSessionValue ( 'project' );
    if(strpos($defaultProject, ',') !== null){
      $defaultProject = '*';
    }
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
  $listRestrictedType = Type::listRestritedTypesForClass('RequirementType', $defaultProject, null, null);
  if (count( $listRestrictedType ) == 0) {
    $listType = SqlList::getList ( 'RequirementType' );
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
  if ($filterTypes == '' and array_key_exists('dashboardRequirementMainType', $_REQUEST)) {
    $filterOnType = ($_REQUEST['dashboardRequirementMainType'] == 'null') ? '' : $_REQUEST['dashboardRequirementMainType'];
  }
  if ($filterTypes != '' or $filterOnType != null) {
    unset($tabPosition['RequirementType']);
    if (in_array('RequirementType', $tabPosition['orderListLeft'])) {
      unset($tabPosition['orderListLeft'][array_search('RequirementType', $tabPosition['orderListLeft'])]);
    } elseif (in_array('RequirementType', $tabPosition['orderListRight'])) {
      unset($tabPosition['orderListRight'][array_search('RequirementType', $tabPosition['orderListRight'])]);
    }
  }  
if(isset($_REQUEST['goToRequirement'])){
  addParamToUser($user);
}else{
  ?>
<div dojo-type="dijit.layout.BorderContainer" class="container">
<input type="hidden" name="objectClassManual" id="objectClassManual" value="DashboardRequirement" />
	<div dojo-type="dijit.layout.ContentPane" id="parameterButtonDiv"
		class="listTitle" style="z-index: 3; overflow: visible" region="top">
		<form dojoType="dijit.form.Form" id="dashboardRequirementMainForm" action="" method="post" >
		<table width="<?php if(!isNewGui()){?>40%<?php }else{ ?>95% <?php }?>">
			<tr height="32px" >
				<td width="50px" align="center"><?php echo formatIcon('DashboardRequirement', 32, null, true);?></td>
				<td><span class="title"><?php echo i18n('dashboardRequirementMainTitle');?>&nbsp;</span>
				</td>
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
              <?php htmlDrawOptionForReference('idRequirementType', $filterOnType); ?>
          	<script type="dojo/method" event="onChange" >
              if (this.value != ' ') {
                changeParamDashboardRequirement('dashboardRequirementMainType=' + this.value);
              } else {
                changeParamDashboardRequirement('dashboardRequirementMainType=null');
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
                 saveDataToSession("displayByTypeList_RequirementDashboard", dijit.byId('barFilterByType').domNode.style.display, true);
          			 <?php } ?>
                </script>
	        </button>
		  	</td>
		  	<?php }else{ 
		            $displayTypes=Parameter::getUserParameter("displayByTypeList_RequirementDashboard");
            		if (!$displayTypes) $displayTypes='none';
            		?> <td align="right">
        <div class="listTitle" id="barFilterByType" style="min-height:20px;display: <?php echo $displayTypes;?>"
        data-dojo-type="dijit/layout/ContentPane" region="top">
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
      					onClick="changeParamDashboardRequirement('dashboardRequirementMainTypes=<?php echo $idType ?>')"></div>
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
		$displayTypes=Parameter::getUserParameter("displayByTypeList_RequirementDashboard");
		if (!$displayTypes) $displayTypes='none';
		if(!isNewGui()){
		?>
    <div class="listTitle" id="barFilterByType"
    data-dojo-type="dijit/layout/ContentPane" region="top"
    style="height:20px; display: <?php echo $displayTypes;?>">
    	<table style="position:relative;top:2px;left:3px">
    		<tr>
    			<td style="font-weight:bold"><?php echo i18n('filterOnType'); ?>&nbsp;:&nbsp;</td>
    <?php
    foreach ($listType as $idType => $nameType) {
  	?>
  				<td>
  					<div dojoType="dijit.form.CheckBox" type="checkbox" <?php echo ((in_array($idType, $filterTypesArray)) ? 'checked' : ''); ?>
  					id="showType<?php echo $idType; ?>" name="showType<?php echo $idType; ?>" title="<?php echo htmlEncode($nameType); ?>"
  					onClick="changeParamDashboardRequirement('dashboardRequirementMainTypes=<?php echo $idType ?>')"></div>
  					<?php echo htmlEncode($nameType); ?>&nbsp;&nbsp;
  				</td>
  	<?php
  	}
  	?>
    		</tr>
    	</table>
    </div>
    <?php } ?>
    </form>
  </div>
	
	<div dojo-type="dijit.layout.ContentPane" region="center" style="height:100%;overflow:auto;">
		<div
			style="width: 97%; margin: 0 auto; height: 90px; padding-bottom: 15px; border-bottom: 1px solid #CCC;">
			<table width="100%" class="dashboardTicketMain">
			<?php if(!isNewGui()){?>
				<tr>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainAllRequirement=0')"
									href="#"><?php echo i18n("dashboardRequirementMainAllIssues").addSelected("dashboardRequirementMainAllRequirement",0);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainAllRequirement=2')"
									href="#"><?php echo i18n("dashboardRequirementMainUnclosed").addSelected("dashboardRequirementMainAllRequirement",2);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainAllRequirement=1')"
									href="#"><?php echo i18n("dashboardRequirementMainUnresolved").addSelected("dashboardRequirementMainAllRequirement",1);?></a></td>
							</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainRecent=1')"
									href="#"><?php echo i18n("dashboardRequirementMainAddedRecently").addSelected("dashboardRequirementMainRecent",1);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainRecent=2')"
									href="#"><?php echo i18n("dashboardRequirementMainResolvedRecently").addSelected("dashboardRequirementMainRecent",2);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainRecent=3')"
									href="#"><?php echo i18n("dashboardRequirementMainUpdatedRecently").addSelected("dashboardRequirementMainRecent",3);?></a></td>
							</tr>
							<tr>
								<td align="left"><?php echo i18n("dashboardTicketMainNumberDay");?>&nbsp;:&nbsp;<div
										dojoType="dijit.form.NumberTextBox"
										id="dashboardRequirementMainNumberDay" style="width: 30px"
										onChange="if(isNaN(this.value))dijit.byId('dashboardRequirementMainNumberDay').set('value',7);
          										loadContent('dashboardRequirementMain.php?dashboardRequirementMainNumberDay='+dijit.byId('dashboardRequirementMainNumberDay').get('value'), 'centerDiv', 'dashboardRequirementMainForm');"
										value="<?php echo $nbDay;?>"></div></td>
							</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainToMe=1')"
									href="#"><?php echo i18n("dashboardRequirementMainAssignedToMe").addSelected("dashboardRequirementMainToMe",1);?></a></td>
							</tr>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainToMe=2')"
									href="#"><?php echo i18n("dashboardRequirementMainReportedByMe").addSelected("dashboardRequirementMainToMe",2);?></a></td>
							</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
							<tr>
								<td align="left"><a style="cursor:pointer"
									onClick="changeParamDashboardRequirement('dashboardRequirementMainUnresolved=1')"
									href="#"><?php echo i18n("dashboardRequirementMainUnscheduled").addSelected("dashboardRequirementMainUnresolved",1);?></a></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<button id="updateTabDashboardRequirementMain"
							dojoType="dijit.form.Button" showlabel="false"
							title="<?php echo i18n('menuParameter');?>"
							iconClass="iconParameter16">
							<script type="dojo/connect" event="onClick" args="evt">
                  dijit.byId('popUpdatePositionTab').show();
              </script>
						</button>
					</td>
				</tr>
				<?php }else{?>
				<tr>
				  <td valign="top" style="width:30%">
						<table>
						<tr height="37px"><td>&nbsp;&nbsp;<?php echo ucfirst(i18n('filterByTicket'));?></td> </tr>
								<tr>
								<?php 
								$paramDashboardRequierementMain=0;
  				        if(Parameter::getUserParameter("dashboardRequirementMainAllRequirement")!=null){
  				          $paramDashboardRequierementMain=Parameter::getUserParameter("dashboardRequirementMainAllRequirement");
  				        }
								?>
								  <td width="320px">
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainAllRequirement=0')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramDashboardRequierementMain==0){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('AllIssues');?></li>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainAllRequirement=2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardRequierementMain==2){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('unclosed');?></li>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainAllRequirement=1')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardRequierementMain==1){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('Unresolved');?></li>
                    </ul>
								  </td>
								   <?php  $dashboardTicketMainUnresolved=null;
  				        if(Parameter::getUserParameter("dashboardRequirementMainUnresolved")!=null){
  				          $dashboardTicketMainUnresolved=Parameter::getUserParameter("dashboardRequirementMainUnresolved");
  				        } ?>
								  <td>
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainUnresolved=1')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($dashboardTicketMainUnresolved==1){ echo "data-dojo-props='selected:true'"; }?> ><?php echo ucfirst(i18n("dashboardRequirementMainUnscheduled"));?></li>
                    </ul>
								  </td>
								</tr>
						</table>
					</td>
					<td valign="top" style="width:25%">
						<table>
						  <tr>
						    <td>
						      <table>
						        <tr height="37px">
						          <td>&nbsp;&nbsp;<?php echo i18n('filterDateByTicket');?></td>
						          <td style="width:10%;white-space:nowrap;" align="right">
        							 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ucfirst(i18n('since'));?>&nbsp;
        						  </td>
        						  <td align="left">		
                      <div style="width:30px;" class="filterField rounded" dojoType="dijit.form.TextBox" value="<?php echo $nbDay;?>"
                       type="text" id="dashboardRequirementMainNumberDay" name="dashboardRequirementMainNumberDay" onChange="if(isNaN(this.value))dijit.byId('dashboardRequirementMainNumberDay').set('value',7);
          										loadContent('dashboardRequirementMain.php?dashboardRequirementMainNumberDay='+dijit.byId('dashboardRequirementMainNumberDay').get('value'), 'centerDiv', 'dashboardRequirementMainForm');">
                      </div>
                      </td>
                      <td style="width:10%;white-space:nowrap;" align="right">
        							 <?php echo i18n('days');?>&nbsp;
        						</td>
										</tr>
									</table>
						  </tr>
							<tr>
								<?php 
								  $paramnDateDashboardRequirement=null;
  				        if(Parameter::getUserParameter("dashboardRequirementMainRecent")!=null){
  				          $paramnDateDashboardRequirement=Parameter::getUserParameter("dashboardRequirementMainRecent");
  				        }
								?>
								  <td>
								     <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainRecent=1')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramnDateDashboardRequirement==1){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('AddedRecently');?></li>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainRecent=2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramnDateDashboardRequirement==2){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('ResolvedRecently');?></li>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainRecent=3')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramnDateDashboardRequirement==3){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('updatedRecently');?></li>
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
							    $paramDashboardRequierementRecent =null;
							    if(Parameter::getUserParameter("dashboardRequirementMainToMe")!=null){
  				          $paramDashboardRequierementRecent=Parameter::getUserParameter("dashboardRequirementMainToMe");
  				        } ?>
								  <td>
								    <ul style="top:-8px;" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainToMe=1')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramDashboardRequierementRecent==1){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('AssignedToMe');?></li>
                      <li onClick="changeParamDashboardRequirement('dashboardRequirementMainToMe=2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramDashboardRequierementRecent==2){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('ReportedByMe');?></li>
                    </ul>
								  </td>
						  </tr>
						</table>
						<td valign="top" style="width:25%">
						<table style="margin-top:6px;">
					   <tr height="25px">
					     <td style="vertical-align: middle; text-align:left;" width="5px">
                <span class="nobr"><?php echo i18n("colType");?></span>
               </td>
             </tr>
             <tr>
				      <td>
        	     <select title="<?php echo i18n('filterOnType'); ?>" type="text" class="filterField roundedLeft" dojoType="dijit.form.FilteringSelect"
        	       <?php echo autoOpenFilteringSelect();
        	       if ($filterTypes != '') echo ' readOnly'; ?>
        	       id="typeFilter" name="typeFilter" style="top:-8px;width:200px">
                  <?php htmlDrawOptionForReference('idRequirementType', $filterOnType); ?>
                	<script type="dojo/method" event="onChange" >
                   if (this.value != ' ') {
                    changeParamDashboardRequirement('dashboardRequirementMainType=' + this.value);
                    } else {
                      changeParamDashboardRequirement('dashboardRequirementMainType=null');
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
                 saveDataToSession("displayByTypeList_RequirementDashboard", dijit.byId('barFilterByType').domNode.style.display, true);
          			 <?php } ?>
                </script>
	        </button>
		  	</td>
							</tr>
						</table>
					</td>
					<td valign="top" >
						<button style="margin-top:10px;" id="updateTabDashboardRequirementMain" class="resetMargin detailButton notButton"
							dojoType="dijit.form.Button" showlabel="false"
							title="<?php echo i18n('menuParameter');?>"
							iconClass="iconParameter iconSize22  <?php if(isNewGui()){?> imageColorNewGui <?php }?>">
							<script type="dojo/connect" event="onClick" args="evt">
                dijit.byId('popUpdatePositionTab').show();
              </script>
						</button>
						
					</td>
				</tr>
				<?php }?>
			</table>

		</div>
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
	onHide="loadContent('dashboardRequirementMain.php', 'centerDiv');" title="<?php echo i18n("listTodayItems");?>">
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
    $ajoutGroupBy="r.id".$param["groupBy"];
    $ajoutWhere=" $ajoutGroupBy=a.id ";
    $paramAdd="";
    $total=0;
    $canGoto=true;
    if (! securityCheckDisplayMenu(null,'Requirement')) {
      $canGoto=false;
    }
    $obR=new Requirement();
    $tableName=$obR->getDatabaseTableName();
    if(isset($param['paramAdd'])){
      $paramAdd=$param['paramAdd'];
      if($total1==null){
        $result=Sql::query("SELECT COUNT(*) as nbline FROM $tableName r WHERE (r.idProject in ".getVisibleProjectsList(false)." OR (r.idProject is null AND r.idProduct is not null)) $paramAdd");
        if (Sql::$lastQueryNbRows > 0) {
          $line = Sql::fetchLine($result);
          $total1=$line['nbline'];
        }
      }
      $total=$total1;
    }else{
      if($total2==null){
        $result=Sql::query("SELECT COUNT(*) as nbline FROM $tableName r WHERE (r.idProject in ".getVisibleProjectsList(false)." OR (r.idProject is null AND r.idProduct is not null))");
        if (Sql::$lastQueryNbRows > 0) {
          $line = Sql::fetchLine($result);
          $total2=$line['nbline'];
        }
      }
      $total=$total2;
    }
    
    $result=Sql::query("SELECT COUNT(*) as nbline, $ajoutGroupBy as idneed FROM $tableName r WHERE $ajoutGroupBy is not null AND (r.idProject in ".getVisibleProjectsList(false)." OR (r.idProject is null AND r.idProduct is not null)) $paramAdd GROUP BY $ajoutGroupBy ");    if ($total > 0) {
      $res=array();
      $totR=0;
      while ($line = Sql::fetchLine($result)) {
        $object= new $param["groupBy"]($line['idneed'],true);
        if (!$object->id and property_exists($object, '_constructForName')) {
          if ($param["groupBy"]=='TargetProductVersion') $classObject='OriginalProductVersion';
          if ($param["groupBy"]=='TargetComponentVersion') $classObject='OriginalComponentVersion';
          $object=new $classObject($line['idneed'], true);
        }
        $idU=$object->name;
        if(isset($object->sortOrder)){
          $idU=$object->sortOrder.'-'.$object->id;
        }
        $res[$idU]["name"]=$object->name;
        $res[$idU]["nb"]=$line['nbline'];
        $res[$idU]["id"]=$object->id;
        if(isset($object->color))$res[$idU]["color"]=$object->color;
        $totR+=$line['nbline'];
      }
      $addIfNoParam="";
      if(!$param['withParam'])$addIfNoParam='<span style="font-style:italic;color:#999999;">&nbsp;('.i18n('noFilterClause').')</span>';
      ksort($res);
      echo '<h2 style="color:#333333;font-size:16px;">'.trim(i18n("dashboardTicketMainTitleBase"))." ".(i18n($param["title"])).$addIfNoParam."</h2>";
      echo "<table width=\"95%\" class=\"tabDashboardTicketMain\">";
      echo '<tr><td class="titleTabRequirement">'.i18n($param["title"]).'</td><td class="titleTabTicket">'.i18n("dashboardTicketMainColumnCount").'</td><td class="titleTabTicket">'.i18n("dashboardTicketMainColumnPourcent")."</td></tr>";
      foreach ($res as $idSort=>$nbline){
        if ($canGoto) $name='<a href="#" onclick="stockHistory(\'Requirement\',null,\'object\');loadContent(\'dashboardRequirementMain.php?goToRequirement='.$param["groupBy"].'&val='.$nbline['id'].'\', \'centerDiv\', \'dashboardRequirementMainForm\');">'.$nbline["name"].'</a>';
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
      if($total-$totR>0){
        echo "  <tr>";
        echo "    <td width=\"50%\">";
        if ($canGoto) echo '<a class="styleUDashboard" href="#" onclick="stockHistory(\'Requirement\',null,\'object\');loadContent(\'dashboardRequirementMain.php?goToRequirement='.$param["groupBy"].'&undefined=true\', \'centerDiv\', \'dashboardRequirementMainForm\');">'.i18n("undefinedValue").'</a>';
        else echo i18n("undefinedValue");
        echo "    </td>";
        echo "    <td width=\"10%\">";
        echo '<span>'.($total-$totR).'</span>';
        echo "    </td>";
        echo "    <td width=\"40%\">&nbsp;";
        echo '<div style="background-color:#3c78b5;margin-top: 3px;position:relative;height:13px;width:'.round(100*(($total-$totR)/$total)).'px;float:left;">&nbsp;</div><div style="position:relative;margin-left:10px;width:50px; float: left;">'.round(100*(($total-$totR)/$total))." %</div>";
        echo "    </td>";
        echo "  </tr>";
      }
      echo "  <tr>";
      echo "    <td width=\"50%\">";
      if ($canGoto) echo '<a class="styleADashboard" href="#" onclick="stockHistory(\'Requirement\',null,\'object\');loadContent(\'dashboardRequirementMain.php?goToRequirement='.$param["groupBy"].'\', \'centerDiv\', \'dashboardRequirementMainForm\');">'.i18n("dashboardRequirementMainAllIssues").'</a>';
      else echo i18n("dashboardRequirementMainAllIssues");
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
    echo '<table><tr><td valign="top"><table id="dndDashboardLeftParameters" jsId="dndDashboardLeftParameters" dojotype="dojo.dnd.Source" dndType="tableauBordLeft"
               withhandles="true" style="width:300px;cellspacing:0; cellpadding:0;" data-dojo-props="accept: [ \'tableauBordRight\',\'tableauBordLeft\' ]"> ';
    echo '<tr><td colspan="3">&nbsp;</td></tr>';
    $requirement=new Requirement();
    if (Parameter::getGlobalParameter('manageComponentOnRequirement') != 'YES') {
      if (in_array('Component', $tabPosition['orderListLeft'])) {
        unset($tabPosition['orderListLeft'][array_search('Component', $tabPosition['orderListLeft'])]);
      } else {
        unset($tabPosition['orderListRight'][array_search('Component', $tabPosition['orderListRight'])]);
      }
      if (in_array('TargetComponentVersion', $tabPosition['orderListLeft'])) {
        unset($tabPosition['orderListLeft'][array_search('TargetComponentVersion', $tabPosition['orderListLeft'])]);
      } else {
        unset($tabPosition['orderListRight'][array_search('TargetComponentVersion', $tabPosition['orderListRight'])]);
      }
    }
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
              <tr><td align="right"></td><td></td><td align="left">
              <button style="margin-right:15px;" class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId(\'popUpdatePositionTab\').hide();">
                '.i18n("buttonCancel").'
              </button>
              <button class="mediumTextButton" dojoType="dijit.form.Button" type="submit" id="confirmChangeTabBordRequirementMain" onclick="protectDblClick(this);changeDashboardTicketMainTabPos();return false;">
                '.i18n("buttonOK").'
              </button>
              </td></tr>
              </table>';
  }
  
  function addParametersDashboardRequirementMain($prefix="r"){
    $user=getSessionUser();
    $result="";
    $allRequirement="0";
    if(isset($_REQUEST['dashboardRequirementMainAllRequirement'])){
      Parameter::storeUserParameter("dashboardRequirementMainAllRequirement", $_REQUEST['dashboardRequirementMainAllRequirement']);
    }
    if(Parameter::getUserParameter("dashboardRequirementMainAllRequirement")!=null){
      $allRequirement=Parameter::getUserParameter("dashboardRequirementMainAllRequirement");
    }else{
      Parameter::storeUserParameter("dashboardRequirementMainAllRequirement", $allRequirement);
    }
    if($allRequirement=="1")$result.=" AND $prefix.done=0 ";
    if($allRequirement=="2")$result.=" AND $prefix.idle=0 ";
    
    $recent="0";
    $nbDay=7;
    if(isset($_REQUEST['dashboardRequirementMainRecent'])){
      if(Parameter::getUserParameter("dashboardRequirementMainRecent")!=null){
        if($_REQUEST['dashboardRequirementMainRecent']==Parameter::getUserParameter("dashboardRequirementMainRecent"))$_REQUEST['dashboardRequirementMainRecent']="0";
      }
      Parameter::storeUserParameter("dashboardRequirementMainRecent", $_REQUEST['dashboardRequirementMainRecent']);
    }
    
    if(Parameter::getUserParameter("dashboardRequirementMainNumberDay")!=null){
      $nbDay=Parameter::getUserParameter("dashboardRequirementMainNumberDay");
    }
    if(Parameter::getUserParameter("dashboardRequirementMainRecent")!=null){
      $recent=Parameter::getUserParameter("dashboardRequirementMainRecent");
    }
    if (Sql::isPgsql()) {
      if($recent=="1")$result.=" AND $prefix.creationDateTime>=current_date - INTERVAL '" . intval($nbDay) . " day' ";
      if($recent=="2")$result.=" AND $prefix.doneDate>=current_date - INTERVAL '" . intval($nbDay) . " day' ";
      if($recent=="3")$result.=" AND $prefix.id IN (SELECT r2.refId FROM history r2 WHERE r2.refId=$prefix.id AND r2.refType='Requirement' AND r2.operationDate>=current_date - INTERVAL '" . intval($nbDay) . " day' ) ";
    } else {
      if($recent=="1")$result.=" AND $prefix.creationDateTime>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY) ";
      if($recent=="2")$result.=" AND $prefix.doneDate>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY) ";
      if($recent=="3")$result.=" AND $prefix.id IN (SELECT r2.refId FROM history r2 WHERE r2.refId=$prefix.id AND r2.refType='Requirement' AND r2.operationDate>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY)) ";
    }
    
    if(isset($_REQUEST['dashboardRequirementMainToMe'])){
      if(Parameter::getUserParameter("dashboardRequirementMainToMe")!=null){
        if($_REQUEST['dashboardRequirementMainToMe']==Parameter::getUserParameter("dashboardRequirementMainToMe"))$_REQUEST['dashboardRequirementMainToMe']="0";
      }
      Parameter::storeUserParameter("dashboardRequirementMainToMe", $_REQUEST['dashboardRequirementMainToMe']);
    }
    
    $toMe="";
    if(Parameter::getUserParameter("dashboardRequirementMainToMe")!=null){
      $toMe=Parameter::getUserParameter("dashboardRequirementMainToMe");
    }
    if($toMe=="1")$result.=" AND $prefix.idResource=".$user->id." ";
    if($toMe=="2")$result.=" AND $prefix.idUser=".$user->id." ";
    $unresolved="";
    
    if(isset($_REQUEST['dashboardRequirementMainUnresolved'])){
      if(Parameter::getUserParameter("dashboardRequirementMainUnresolved")!=null){
        if($_REQUEST['dashboardRequirementMainUnresolved']==Parameter::getUserParameter("dashboardRequirementMainUnresolved"))$_REQUEST['dashboardRequirementMainUnresolved']="0";
      }
      Parameter::storeUserParameter("dashboardRequirementMainUnresolved", $_REQUEST['dashboardRequirementMainUnresolved']);
    }
    
    if(Parameter::getUserParameter("dashboardRequirementMainUnresolved")!=null){
      $unresolved=Parameter::getUserParameter("dashboardRequirementMainUnresolved");
    }
    if($unresolved=="1")$result.=" AND $prefix.idTargetProductVersion is null ";
    
    $filterTypes = '';
    if (Parameter::getUserParameter("dashboardRequirementMainTypes")) {
      $filterTypes = Parameter::getUserParameter("dashboardRequirementMainTypes");
    }
    if (isset($_REQUEST['dashboardRequirementMainTypes'])) {
      if ($filterTypes == '') {
        $filterTypes = $_REQUEST['dashboardRequirementMainTypes'];
        $filterTypesArray = array($filterTypes);
      } else {
        $filterTypesArray = explode(', ', $filterTypes);
        if (in_array($_REQUEST['dashboardRequirementMainTypes'], $filterTypesArray)) {
          unset($filterTypesArray[array_search($_REQUEST['dashboardRequirementMainTypes'], $filterTypesArray)]);
        } else {
          $filterTypesArray[] = $_REQUEST['dashboardRequirementMainTypes'];
        }
        $filterTypes = implode(', ', $filterTypesArray);
      }
      Parameter::storeUserParameter("dashboardRequirementMainTypes", $filterTypes);
    }
    if ($filterTypes == '' and array_key_exists('dashboardRequirementMainType', $_REQUEST)) {
      $filterTypes = ($_REQUEST['dashboardRequirementMainType'] == 'null') ? '' : $_REQUEST['dashboardRequirementMainType'];
    }
    if ($filterTypes != '') $result .= " AND $prefix.idRequirementType in (" . $filterTypes . ")";
    
    return $result;
  }
  
  function addParamToUser($user){
    $user->_arrayFilters['Requirement']=array();
    $objectClass=$_REQUEST['goToRequirement'];
    $obRef=new Requirement();
    $iterateur=0;
    if(isset($_REQUEST['val'])){
      $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='id'.$objectClass;
      $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='=';
      $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']=$_REQUEST['val'];
      $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('id'.$objectClass);
      $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']='=';
      $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=SqlList::getNameFromId($objectClass, $_REQUEST['val']);
    }else{
      $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='id'.$objectClass;
      $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='SORT';
      $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']='asc';
      $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('id'.$objectClass);
      $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']=i18n('sortFilter');
      $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=i18n('sortAsc');
      if(isset($_REQUEST['undefined'])){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='id'.$objectClass;
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='is null';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']='';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('id'.$objectClass);
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']=i18n("isNotEmpty");
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']='';
        $iterateur++;
      }
    }
    $iterateur++;
    $tabPosition=Parameter::getUserParameter("dashboardRequirementMainTabPosition");
    $tabPosition=json_decode($tabPosition,true);
    if($tabPosition[$objectClass]["withParam"]){
      $allRequirement=Parameter::getUserParameter("dashboardRequirementMainAllRequirement");
      if($allRequirement=="0"){
        $user->_arrayFilters['Requirement'][$iterateur]['disp']["attribute"]=i18n('labelShowIdle');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']["operator"]="";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']["value"]="";
        $user->_arrayFilters['Requirement'][$iterateur]['sql']["attribute"]='idle';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']["operator"]='>=';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']["value"]='0';
        $iterateur++;
      }
      if($allRequirement=="1"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='done';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='=';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']='0';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('done');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']='=';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=i18n('no');
        $iterateur++;
      }
      if($allRequirement=="2"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='idle';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='=';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']='0';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idle');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']="=";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=i18n("yes");
        $iterateur++;
      }
      
      $recent=Parameter::getUserParameter("dashboardRequirementMainRecent");
      $nbDay=Parameter::getUserParameter("dashboardRequirementMainNumberDay");
      if (preg_match('/[^\-0-9]/', $nbDay) == true) {
        $nbDay="";
      }
      if($recent=="1"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='creationDateTime';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='>=';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('creationDateTime');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']=">=";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=i18n('today').' -'.$nbDay.' '.i18n('days');
        if (Sql::isPgsql()) {
          $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']= "NOW() + INTERVAL '" . (intval($nbDay)*(-1)) . " day'";
        } else {
          $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']= "ADDDATE(NOW(), INTERVAL (" . (intval($nbDay)*(-1)) . ") DAY)";
        }
        $iterateur++;
      }
      
      if($recent=="2"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='doneDate';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='>=';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('doneDate');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']=">=";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=i18n('today').' -'.$nbDay.' '.i18n('days');
        if (Sql::isPgsql()) {
          $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']= "NOW() + INTERVAL '" . (intval($nbDay)*(-1)) . " day'";
        } else {
          $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']= "ADDDATE(NOW(), INTERVAL (" . (intval($nbDay)*(-1)) . ") DAY)";
        }
        $iterateur++;
      }
      if($recent=="3"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']='id';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='IN';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=i18n("dashboardTicketMainLastUpdate");
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']=">=";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=i18n('today').' -'.$nbDay.' '.i18n('days');
        if (Sql::isPgsql()) {
          $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']=" (SELECT r2.refId FROM history r2 WHERE r2.refId=Requirement.id AND r2.refType='Requirement' AND r2.operationDate>=NOW() - INTERVAL '" . intval($nbDay) . " day' ) ";
        } else {
          $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']=" (SELECT r2.refId FROM history r2 WHERE r2.refId=Requirement.id AND r2.refType='Requirement' AND r2.operationDate>=ADDDATE(NOW(), INTERVAL (-" . intval($nbDay) . ") DAY)) ";
        }
        $iterateur++;
      }
      
      $toMe=Parameter::getUserParameter("dashboardRequirementMainToMe");
      if($toMe=="1"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']=$obRef->getDatabaseColumnName('idResource');
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='=';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']=$user->id;
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idResource');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']="=";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=$user->name;
        $iterateur++;
      }
      if($toMe=="2"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']=$obRef->getDatabaseColumnName('idUser');
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='=';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']=$user->id;
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idUser');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']="=";
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']=$user->name;
        $iterateur++;
      }
      $unresolved=Parameter::getUserParameter("dashboardRequirementMainUnresolved");
      if($unresolved=="1"){
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute']=$obRef->getDatabaseColumnName('idTargetProductVersion');
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator']='is null';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value']='';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute']=$obRef->getColCaption('idTargetProductVersion');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator']=i18n('isEmpty');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value']='';
        $iterateur++;
      }
      $types = '';
      if (Parameter::getUserParameter("dashboardRequirementMainTypes") != null) {
        $types = Parameter::getUserParameter("dashboardRequirementMainTypes");
        $typesArray = explode(', ', $types);
        $typesNames = '';
        foreach ($typesArray as $idType) {
          $typesNames .= htmlEncode(SqlList::getNameFromId('Type', $idType)) . ', ';
        }
        $typesNames = substr($typesNames, 0, -2);
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['attribute'] = $obRef->getDatabaseColumnName('idRequirementType');
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['operator'] = 'IN';
        $user->_arrayFilters['Requirement'][$iterateur]['sql']['value'] = '(' . $types . ')';
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['attribute'] = $obRef->getColCaption('idRequirementType');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['operator'] = i18n('amongst');
        $user->_arrayFilters['Requirement'][$iterateur]['disp']['value'] = $typesNames;
        $iterateur++;
      }
    }else{
      $user->_arrayFilters['Requirement'][$iterateur]['disp']["attribute"]=i18n('labelShowIdle');
      $user->_arrayFilters['Requirement'][$iterateur]['disp']["operator"]="";
      $user->_arrayFilters['Requirement'][$iterateur]['disp']["value"]="";
      $user->_arrayFilters['Requirement'][$iterateur]['sql']["attribute"]='idle';
      $user->_arrayFilters['Requirement'][$iterateur]['sql']["operator"]='>=';
      $user->_arrayFilters['Requirement'][$iterateur]['sql']["value"]='0';
      $iterateur++;
    }
    setSessionUser($user);
    $_REQUEST['objectClass']='Requirement';
    include 'objectMain.php';
  }
  
  ?>