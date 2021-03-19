<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2015 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
*
* This file is an add-on to ProjeQtOr, packaged as a plug-in module.
* It is NOT distributed under an open source license.
* It is distributed in a proprietary mode, only to the customer who bought
* corresponding licence.
* The company ProjeQtOr remains owner of all add-ons it delivers.
* Any change to an add-ons without the explicit agreement of the company
* ProjeQtOr is prohibited.
* The diffusion (or any kind if distribution) of an add-on is prohibited.
* Violators will be prosecuted.
*
*** DO NOT REMOVE THIS NOTICE ************************************************/

require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
require_once "../tool/kanbanConstructPrinc.php";
require_once '../tool/kanbanFunction.php';
global $typeKanbanC;
global $orderBy;
$orderBy="";
if(array_key_exists('kanbanOrderBy',$_REQUEST)){
  $orderBy=$_REQUEST['kanbanOrderBy'];
  setSessionValue('kanbanOrderBy', $orderBy);
}else if(sessionValueExists('kanbanOrderBy')){
  $orderBy=getSessionValue('kanbanOrderBy');
}
$typeKanbanC="Ticket";
$seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
if(PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile) != "ALL")$seeWork=false;
if(Parameter::getUserParameter("kanbanShowIdle")==null){
  Parameter::storeUserParameter("kanbanShowIdle", false);
}
$idKanban=-1;
if (array_key_exists('idKanban',$_REQUEST)) {
  $idKanban=$_REQUEST['idKanban'];
  Parameter::storeUserParameter("kanbanIdKanban",$idKanban);
}else{
  if(Parameter::getUserParameter("kanbanIdKanban")!==null){
    $idKanban=Parameter::getUserParameter("kanbanIdKanban");
  }
}
$kanTest=new Kanban($idKanban,true);
if($kanTest->name=='')$idKanban=-1;
$json="";
$type="";
$name="";
if($idKanban!=-1){
  $kanB=new Kanban($idKanban,true);
  $json=$kanB->param;
  $type=$kanB->type;
  $name=$kanB->name;
  $jsonDecode=json_decode($json,true);
  if(!isset($jsonDecode['typeData'])){
    $jsonDecode['typeData']='Ticket';
    $kanB->param=json_encode($jsonDecode);
    $kanB->save();
  }
  $typeKanbanC=$jsonDecode['typeData'];
}
$arrayProject=array();
$hasVersion=(property_exists($typeKanbanC,'idTargetProductVersion'))?true:false;
?>
<div class="container" dojoType="dijit.layout.BorderContainer">
  <div id="titleKanban" class="listTitle" style="z-index:3;overflow:visible;min-height:65px;"
    dojoType="dijit.layout.ContentPane" region="top">
    <table width="100%">
      <tr height="100%" style="vertical-align: middle;">
        <td width="50px" align="center">          
          <div style="position:absolute;top:2px">
            <?php echo formatIcon('Kanban',32,null,true);?>
          </div>
        <td>
  <?php 
  kanbanListSelect($user,$name,$type,$idKanban);
  ?>
         </td>
    
      </tr>
    </table>
    <input type="hidden" name="objectClassManual" id="objectClassManual" value="Kanban" />
    <input type="hidden" id="objectClassList" name="objectClassList" value="<?php echo $typeKanbanC;?>">
    <input dojoType="dijit.form.TextBox" type="hidden" id="refreshActionAdd<?php echo $typeKanbanC;?>" value="-1" onchange="if(dijit.byId(this.id).get('value')!=-1)loadContent('../view/kanbanView.php?idKanban=<?php echo $idKanban;?>', 'divKanbanContainer');dijit.byId(this.id).set('value',-1);">
    <input dojoType="dijit.form.TextBox" type="hidden" id="objectClass" name="objectClass" value="<?php echo $typeKanbanC;?>">

<?php if($idKanban!=-1){?>
  <input dojoType="dijit.form.TextBox" type="hidden" id="idKanban" value="<?php echo $idKanban;?>">
  <div style="width:100%; margin: 0px 10px 3px 10px" dojoType="dijit.layout.ContentPane" region="bottom">
  <?php echo i18n("colName");?> : <input dojoType="dijit.form.TextBox" onKeyUp="kanbanStart();" class="dijit dijitReset dijitInline dijitLeft filterField rounded dijitTextBox" type="text" id="searchByName" value="<?php echo getSessionValue('kanbanname');?>">
    <?php echo i18n("colResponsible");?> : 
      <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" 
        <?php echo autoOpenFilteringSelect ();?>
        style="width: 150px;" onChange="kanbanStart();" name="searchByResponsible" id="searchByResponsible"
        value="<?php echo getSessionValue('kanbanresponsible');?>">
          <option value=""></option>
            <?php $specific='imputation';
              include '../tool/drawResourceListForSpecificAccess.php';?> 
      </select>
  <?php if($type!='Status'){echo i18n("colIdStatus");?> : 
    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" style="width: 150px;" 
    <?php echo autoOpenFilteringSelect ();?>
    onChange="kanbanStart();" name="listStatus" id="listStatus" value="<?php echo getSessionValue('kanbanstatus');?>" >
      <?php htmlDrawOptionForReference("idStatus", null);?>
    </select>
  <?php } if($type!='TargetProductVersion' and $hasVersion){echo i18n("colIdVersion"); ?> : 
    <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" style="width: 150px;" 
    <?php echo autoOpenFilteringSelect ();?>
    onChange="kanbanStart();" name="listTargetProductVersion" id="listTargetProductVersion" 
    value="<?php echo getSessionValue('kanbantargetProductVersion');?>">
      <?php if(is_numeric(getSessionValue("project"))){
        htmlDrawOptionForReference("idTargetProductVersion", null, null, false, 'idProject', getSessionValue("project"));
      }else{
        htmlDrawOptionForReference("idTargetProductVersion", null);
      }?>
    </select>
      <?php } echo i18n("sortedBy"); ?> : 
        <select dojoType="dijit.form.FilteringSelect" class="input roundedLeft" style="width:150px;margin-right:15px;"
        <?php echo autoOpenFilteringSelect ();?>
        onChange="kanbanChangeOrderBy(dijit.byId('kanbanOrderBy').get('value'),<?php echo $idKanban;?>);" name="kanbanOrderBy" id="kanbanOrderBy">
          <option <?php if($orderBy=="")echo "selected";?> value=""></option>
          <option <?php if($orderBy=="name")echo "selected";?> value="name"><?php echo i18n("colName");?></option>
          <option <?php if($orderBy=="idresponsible")echo "selected";?> value="idresponsible"><?php echo i18n("colResponsible");?></option>
          <option <?php if($orderBy=="idstatus")echo "selected";?> value="idstatus"><?php echo i18n("colIdStatus");?></option>
          <?php if ($hasVersion) {?><option <?php if($orderBy=="idtargetproductversion")echo "selected";?> value="idtargetproductversion"><?php echo i18n("colIdTargetProductVersion");?></option><?php }?>
          <option <?php if($orderBy=="id")echo "selected";?> value="id"><?php echo i18n("colId");?></option>
        </select>
        <button title="<?php echo i18n('advancedFilter')?>" class="comboButton" dojoType="dijit.form.DropDownButton" id="listFilterFilter" 
        name="listFilterFilter" style="margin-right:15px;"
        iconClass="dijitButtonIcon icon<?php echo (isset(getSessionUser()->_arrayFilters[$typeKanbanC]) && is_array(getSessionUser()->_arrayFilters[$typeKanbanC]) && count(getSessionUser()->_arrayFilters[$typeKanbanC])!=0 ? 'Active' : '');?>Filter" showLabel="false">
           <?php if (!isNewGui()){ ?>
            <script type="dojo/connect" event="onClick" args="evt">
            showFilterDialog();
          </script>
            <script type="dojo/method" event="onMouseEnter" args="evt">
            clearTimeout(closeFilterListTimeout);
            clearTimeout(openFilterListTimeout);
            openFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').openDropDown();",popupOpenDelay);
          </script>
            <script type="dojo/method" event="onMouseLeave" args="evt">
            clearTimeout(openFilterListTimeout);
            closeFilterListTimeout=setTimeout("dijit.byId('listFilterFilter').closeDropDown();",2000);
          </script>
         <?php }?>
          <div dojoType="dijit.TooltipDialog" id="directFilterList" style="z-index: 999999;display:none; position: absolute;">
            <?php 
              //$_REQUEST['filterObjectClass']=$objectClass;
              //$_REQUEST['context']="directFilterList";
              $_REQUEST['context']='directFilterList';
              $_REQUEST['contentLoad']="../view/kanbanView.php?idKanban=".$idKanban;
              $_REQUEST['container']="divKanbanContainer";
              $_REQUEST['filterObjectClass']=$typeKanbanC;
              if(isNewGui()){
                $filterObjectClass = $typeKanbanC;
                $dontDisplay = true;
                include "../tool/displayQuickFilterList.php";
              }
              //ajout de mehdi
              include "../tool/displayFilterList.php";
            ?>
           <?php if (!isNewGui()){ ?>
              <script type="dojo/method" event="onMouseEnter" args="evt">
                clearTimeout(closeFilterListTimeout);
                clearTimeout(openFilterListTimeout);
              </script>
              <script type="dojo/method" event="onMouseLeave" args="evt">
                dijit.byId('listFilterFilter').closeDropDown();
              </script>
           <?php }?>
          </div> 
        </button>
  </div>
  <?php }?>
</div>
  <div id="kanbanContainer" style="height:100%;overflow-x:scroll;padding:8px;" dojoType="dijit.layout.ContentPane" region="center" 
  onscroll="kanbanScrollTop=this.scrollTop">  
    <table width="100%" style="min-height:100%;">
      <tr>
        <?php if($idKanban!=-1)drawColumnKanban($type,$jsonDecode,$idKanban); ?>
      </tr>
    </table>
  </div>
    
  <script type="dojo/connect">       
  kanbanStart();
  </script>
</div>
<?php 

function getLastStatus(){
  $status=new Status();
  $tableName=$status->getDatabaseTableName();
  $result=Sql::query("SELECT t.id as id
    FROM $tableName t where idle=0 order by t.sortOrder desc");
  while ($line = Sql::fetchLine($result)) {
    return $line["id"];
  }
  return '';
}

function drawColumnKanban($type,$jsonD,$idKanban){
  global $typeKanbanC;
  $statusList=SqlList::getList('Status');
  $allowedStatus=array();
  $kanbanFullWidthElement = Parameter::getUserParameter ( "kanbanFullWidthElement" );
  if(count($jsonD['column'])!=0){
  	$jsonArray=array();
  	$keyJsonOrder=array();
  	$sortedColumns=array();
  	foreach ($jsonD['column'] as $itemKanban) {
  	  if($itemKanban['from']!='n'){
  	    $obj = new $type($itemKanban['from'],true);
  	    if(isset($obj->sortOrder)){
  	      $jsonArray[str_pad($obj->sortOrder,5,'0', STR_PAD_LEFT).'-'.$obj->id]=$itemKanban;
  	    }else{
  	      $jsonArray[$obj->name.'-'.$obj->id]=$itemKanban;
  	    }
  	  }else{
  	    $jsonArray['00000-'.$itemKanban['from']]=$itemKanban;
  	  }
  	}
  	ksort($jsonArray);
  	foreach ($jsonArray as $key=>$itemKanban) {
  	  $keyJsonOrder[]=$key;
  	  $sortedColumns[]=$itemKanban;
  	}
    $numCol = count($jsonD['column']);
    $isStatus=$type=="Status";
    $mapAccept=array();
    $accept="[";
    $iterateur=0;
    if(!$isStatus){ // Form Kanban on other than Status, Accept is simple : no restriction for moves
      foreach ($jsonD['column'] as $itemKanban) {
        $accept.='\'typeRow'.$itemKanban['from'].'\'';
        if($iterateur!=count($jsonD['column'])-1)$accept.=',';
      }
    }else{ // For Kanban on Status, Accept must respect workflow, corresponding to user profile
      $user=getSessionUser();
      $mapWorkflow=array();
      $curCol=null;
      $culSta=null;
      for ($i=0;$i<count($sortedColumns);$i++) {
      	$itemKanban=$sortedColumns[$i];
      	$idFrom=$itemKanban['from'];
        $allowedStatus[$idFrom]=array($idFrom=>$idFrom);
        $found=false;
      	foreach ($statusList as $idS=>$nameS) {
      		if ($found) {
      			if (isset($sortedColumns[$i+1]) and $idS==$sortedColumns[$i+1]['from']) {
      			  break;
      			} else {
      				$allowedStatus[$idFrom][$idS]=$idS;
      			}
      		} else if ($idS==$idFrom) {
      			$found=true;
      		}
      	}
      }
      //$visibleProjects=explode(',',trim(getVisibleProjectsList(true),'()'));
      foreach ($user->getAllProfiles() as $idProfile){ // For each profile of the user (on any project)
        //$idProfil=$user->getProfile($idProject);
        foreach (SqlList::getList("Status",'id') as $idStatus){ // For every status
          foreach (SqlList::getList($typeKanbanC."Type",'id') as $idTicketType){ // For every type (Ticket type or Activity Type)
            $workflowId=SqlList::getFieldFromId($typeKanbanC."Type", $idTicketType, 'idWorkflow');
            if(!isset($mapWorkflow[$workflowId])){
              $woTmp=new Workflow($workflowId);
              $mapWorkflow[$workflowId]=$woTmp->getWorkflowstatusArray();
            }
            foreach ($jsonD['column'] as $itemKanban) { // For all defined columns on the Kanban (id of status is in the from field            	
            	foreach ($allowedStatus[$itemKanban['from']] as $idStatusTo) {
            		$toPut="";
                if($idStatusTo!=$idStatus) {
                  if(isset($idProfile) 
                  && isset($mapWorkflow[$workflowId][$idStatus]) 
                  && isset($mapWorkflow[$workflowId][$idStatus][$idStatusTo])) {
                    if(isset($mapWorkflow[$workflowId][$idStatus][$idStatusTo][$idProfile])
                    && $mapWorkflow[$workflowId][$idStatus][$idStatusTo][$idProfile]) {
                      $toPut='typeRow'.$idStatus.'-'.$idTicketType.'-'.trim($idProfile);
                    }
                  }
                }              
                if($toPut!=""){
                  $exist=false;
                  if(isset($mapAccept[$itemKanban['from']]))if(strpos($mapAccept[$itemKanban['from']], $toPut) !== false)$exist=true;
                  if(!$exist){
                    if(!isset($mapAccept[$itemKanban['from']])){
                      $mapAccept[$itemKanban['from']]="'$toPut'";
                    }else{
                      $mapAccept[$itemKanban['from']].=",'$toPut'";
                    }
                  }
                }
            	}
            }
          }
        }
      }
    }
    $accept.="]";
    $percent=100/count($jsonD['column']);
    $iterateur=0;
    
    foreach ($jsonArray as $itemKanban) {
      $nextFrom=$itemKanban['from'];
      if($iterateur<count($jsonArray)-1 && $isStatus){
        $nextFrom=getNextFrom($itemKanban['from'],$jsonArray[$keyJsonOrder[$iterateur+1]]['from'],$type); //bug offset trop Ã©lever
      }else if($isStatus){
        $nextFrom=getLastStatus();
      }
      $result=queryToDo($itemKanban['from'],$nextFrom,$type,$isStatus);
      $realWork=0;
      $plannedWork=0;
      $leftWork=0;
      foreach($result as $line){
        $realWork+=$line['realwork'];
        $plannedWork+=$line['plannedwork'];
        $leftWork+=$line['leftwork'];
      }
      $nbItems=Sql::$lastQueryNbRows;
      $acceptTmp=$accept;
      if(isset($mapAccept[$itemKanban['from']]))$acceptTmp='['.$mapAccept[$itemKanban['from']].']';
      if($type=="Activity")$acceptTmp="[".SqlList::getFieldFromId("Activity", $itemKanban['from'], "idProject")."]";
      if($itemKanban['from']=="n" || $type=="TargetProductVersion")$acceptTmp="[";
      if($type=="TargetProductVersion")$acceptTmp.="'n',";
      if($itemKanban['from']=="n" || $type=="TargetProductVersion"){
        $iterateur2=0;
        foreach($jsonD['column'] as $keyy=>$vall){
          if($vall['from']!='n'){
            if($type=='Activity')$acceptTmp.=SqlList::getFieldFromId('Activity', $vall['from'], 'idProject');
            else $acceptTmp.=$vall['from'];
            $iterateur2++;
            if($iterateur2!=count($jsonD['column'])-1)$acceptTmp.=",";
          }
        }
      }
      if($itemKanban['from']=="n" || $type=="TargetProductVersion")$acceptTmp.="]";
      $destHeight=RequestHandler::getValue('destinationHeight');
      $destWidth=RequestHandler::getValue('destinationWidth');
      if ($destHeight) {
        $maxHeight=($destHeight-163);
        $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
        if ($seeWork) $maxHeight-=32;
        if (isNewGui()) $maxHeight-=6;
        $maxHeight.='px';
      } else {
        $maxHeight='100%';
      }
      if ($destWidth) {
        $nbCols=count($jsonD['column']);
        $maxWidth=((($destWidth)/$nbCols)-20)."px";
      } else {
        $maxWidth="332px";
      }
      
      echo '<td style="vertical-align:top;;width:'.$maxWidth.';min-width:332px;">
            <table style="width:100%;"><tr style="min-height:47px;height:47px;max-height:47px;">
            <td class="kanbanColumn" style="position:relative;background-color:'.((isNewGui())?'var(--color-light);border-radius:10px 10px 0 0':'#e2e4e6').';padding:3px 8px 0px;border-bottom:2px solid #ffffff;min-width:332px;">';
      getNameFromTypeKanban($itemKanban,$nextFrom,$type,$isStatus,$nbItems,$idKanban,$realWork,$plannedWork,$leftWork);
      echo '</td></tr><tr>';
      echo '
        <td class="kanbanColumn" style="overflow-y:scroll;overflow-x:hidden;display:block; height:'.$maxHeight.';max-height:'.$maxHeight.'; position:relative;background-color:'.((isNewGui())?'var(--color-light);border:2px solid var(--color-light);border-radius:0 0 10px 10px':'#e2e4e6').';padding:'.(($kanbanFullWidthElement=='on')?'8px':'6px 0px 6px 4px').';width:auto;min-width:332px;" id="dialogRow'.$itemKanban['from']. '" 
        jsId="dialogRow'.$itemKanban['from']. '" dojotype="dojo.dnd.Source" dndType="typeRow'.$itemKanban['from']. '" withhandles="true"  
        '.($acceptTmp!='[]' ? 'data-dojo-props="accept: '.$acceptTmp.'"':'').' width="'.((100/count($jsonArray))).'%" valign="top">';
      echo '
      <script type="dojo/connect" event="onDndStart" args="evt">
        anchorTmp=evt.anchor;
        evt.anchor.style.display=\'none\';
        return true;
      </script>
      <script type="dojo/connect" event="onDndCancel" args="evt">
      anchorTmp.style.display=\'block\';
        return true;
      </script>';

      getItemsFromTypeIdKanban($itemKanban['from'], $nextFrom, $type,$isStatus,$result,$jsonD);
      
      echo '</td></tr></table>
      </td>';
      $iterateur++;
      if ($iterateur<count($jsonArray)) {
        echo '
        <td style="min-width:10px;max-width:10px;width:10px" width="10px"></td>';
      }
    }
  }
}

function getNextFrom($from,$next,$type){
  $min=SqlList::getFieldFromId($type, $from, "sortOrder");
  $obT=new $type();
  $tableName=$obT->getDatabaseTableName();
  $result=Sql::query("SELECT t.id as typen, t.sortOrder as sortorder FROM $tableName t WHERE t.sortOrder>=$min order by t.sortOrder ");
  $ite=0;
  while ($line = Sql::fetchLine($result)) {
    $listId[]=$line;
  }
  $last=-1;
  foreach($listId as $line){
    if(count($listId)-1!=$ite+1){
    	if(isset($listId[$ite+1]) && $listId[$ite+1]['typen']==$next) {
    		return $line['typen'];
    	}
    }
    $last=$line['typen'];
    $ite++;
  }
  return $last;
}

function getNameFromTypeKanban($itemKanban,$to,$type,$isStatus,$nb,$idKanban,$realWork,$plannedWork,$leftWork){
  $name=$itemKanban['name'];
  $from=$itemKanban['from'];
  $itemWork['realWork']=$realWork;
  $itemWork['plannedWork']=$plannedWork;
  $itemWork['leftWork']=$leftWork;
  $itemWork['id']=$itemKanban['from'];
  $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
  if(PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile) != "ALL")$seeWork=false;
  if(($seeWork==1 || ($seeWork == null && $seeWork!=0)) && PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL")$seeWork=true; else $seeWork=false;
  $addHeight='';
  if($seeWork)$addHeight="height:65px;";
  echo '<div style="margin-bottom:10px;'.$addHeight.'>';
  if($isStatus){
    echo '<h2 style="font-size: 14px;font-weight:bold;margin: 8px 8px 2px;color:#4d4d4d">'.$name;
    if(!isset($itemKanban['cantDelete']) && myKanban($idKanban))
    echo ' <a onClick="delKanban('.$idKanban.', \''.i18n("kanbanDelColumn").'\','.$from.')" title="' . i18n('kanbanColumnDelete') . '" class="smallButton"/> '.formatSmallButton('Remove').'</a>';     
    if(myKanban($idKanban)) { 
      echo '<a onClick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'editColumnKanban\');}, true, \'&typeDynamic=addColumnKanban&typeD='.$type.'&idKanban='.$idKanban.'&idFrom='.$from.'\', true, false);"title="' . i18n('kanbanColumnEdit') . '" class="smallButton"  /> '.formatSmallButton('Edit').'</a>';     
    }
    echo '</h2><div id="numberTickets'.$from.'" class="sectionBadge">'.$nb.'</div>'.displayAllWork($itemWork);
    echo '<h3 class="kanbanTextTitle" style="font-size: 10px;font-weight:bold;margin: 0 8px 9px;">'.i18n("from").' '.SqlList::getNameFromId($type, $from).' '.i18n("to").' '.SqlList::getNameFromId($type, $to).'</h3>';
  }else{
    $nameN=SqlList::getNameFromId($type, $from);
    if($name!='')$nameN=$name;
    echo '<h2 style="font-size: 14px;font-weight:bold;margin: 8px 8px 2px;color:#4d4d4d">'.$nameN;
        if(!isset($itemKanban['cantDelete']) && myKanban($idKanban))        
        echo ' <a onClick="delKanban('.$idKanban.', \''.i18n("kanbanDelColumn").'\','.$from.')" title="' . i18n('kanbanColumnDelete') . '" class="smallButton"/> '.formatSmallButton('Remove').'</a>';      
        if(myKanban($idKanban) && $from!= 'n')
          echo ' <a onClick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'editColumnKanban\');}, true, \'&typeDynamic=addColumnKanban&typeD='.$type.'&idKanban='.$idKanban.'&idFrom='.$from.'\', true, false);" title="' . i18n('kanbanColumnEdit') . '" class="smallButton"  /> '.formatSmallButton('Edit').'</a>';          
        echo '</h2><div id="numberTickets'.$from.'" class="sectionBadge">'.$nb.'</div>'.displayAllWork($itemWork);        
  }
  echo '</div>';
}

function myKanban($idKanban){
   $kanban = new Kanban($idKanban,true);
   return $kanban->idUser==getSessionUser()->id;
}

function queryToDo($from,$nextFrom,$type,$isStatus){
  global $typeKanbanC,$hasVersion;
  global $orderBy;
  $obT=new $typeKanbanC();
  $obT2=new WorkElement();
  $obT3=new Status();
  $obT4=new Resource();
  $obT5=new TargetProductVersion();
  $obj=$obT;
  if($typeKanbanC=='Activity')$obT2=new PlanningElement();
  $tableName=$obT->getDatabaseTableName();
  if($typeKanbanC!='Requirement' and $typeKanbanC!='Action'){
    $tableName2=$obT2->getDatabaseTableName();
  }else{
    $tableName2=null;
  }
  $tableName3=$obT3->getDatabaseTableName();
  $tableName4=$obT4->getDatabaseTableName();
  if ($hasVersion) {
    $tableName5=$obT5->getDatabaseTableName();
  } else {
    $tableName5=null;
  }
  if($from=='n'){
    $listType=' is null ';
  }else{
    $listType='in ('.getIdsOfType($from,$nextFrom,$type).') ';
  }
  $arrayFilter=array();
  if(isset(getSessionUser()->_arrayFilters[$typeKanbanC]) && is_array(getSessionUser()->_arrayFilters[$typeKanbanC]))$arrayFilter=getSessionUser()->_arrayFilters[$typeKanbanC];
  $queryFrom=$tableName;
  $queryWhere=" and 1=1 ";
  $queryOrderBy='';
  $idTab=0;
  $crit=$obT->getDatabaseCriteria();
  /*foreach ($crit as $col => $val) {
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.= $obj->getDatabaseTableName() . '.' . $obj->getDatabaseColumnName($col) . "=" . Sql::str($val) . " ";
  }*/
  $table=$tableName;
  foreach ($arrayFilter as $crit) {
    if ($crit['sql']['operator']!='SORT') { // Sorting already applied above
      $split=explode('_', $crit['sql']['attribute']);
      $critSqlValue=$crit['sql']['value'];
      if (array_key_exists('isDynamic', $crit) and $crit['isDynamic']=='1' and ($crit['sql']['operator']=='IN' or $crit['sql']['operator']=='NOT IN')) {
        if ($crit['sql']['value']==0) continue;
      }
      if (substr($crit['sql']['attribute'], -4, 4) == 'Work') {
        if ($typeKanbanC=='Ticket') {
          $critSqlValue=Work::convertImputation(trim($critSqlValue,"'"));
        } else {
          $critSqlValue=Work::convertWork(trim($critSqlValue,"'"));
        }
      }
      if ($crit['sql']['operator']=='IN'
          and ($crit['sql']['attribute']=='idProduct' or $crit['sql']['attribute']=='idProductOrComponent' or $crit['sql']['attribute']=='idComponent')) {
        $critSqlValue=str_replace(array(' ','(',')'), '', $critSqlValue);
        $splitVal=explode(',',$critSqlValue);
        $critSqlValue='(0';
        foreach ($splitVal as $idP) {
          $prod=new Product($idP,true);
          $critSqlValue.=', '.$idP;
          $list=$prod->getRecursiveSubProductsFlatList(false, false); // Will work only if selected is Product, not for Component
          foreach ($list as $idPrd=>$namePrd) {
            $critSqlValue.=', '.$idPrd;
          }
        }
        $critSqlValue.=')';
      }
      if (count($split)>1 ) {
        $externalClass=$split[0];
        $externalObj=new $externalClass();
        $externalTable = $externalObj->getDatabaseTableName();
        $idTab+=1;
        $externalTableAlias = 'T' . $idTab;
        $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
        ' on ( ' . $externalTableAlias . ".refType='" . get_class($obj) . "' and " .  $externalTableAlias . '.refId = ' . $table . '.id )';
        $queryWhere.=($queryWhere=='')?'':' and ';
        $queryWhere.=$externalTableAlias . "." . $split[1] . ' '
            . $crit['sql']['operator'] . ' '
                . $critSqlValue;
      } else {
        $queryWhere.=($queryWhere=='')?'':' and ';
        if ($crit['sql']['operator']!=' exists ') {
          $queryWhere.="(".$table . "." . $crit['sql']['attribute'] . ' ';
        }
        $queryWhere.= $crit['sql']['operator'] . ' ' . $critSqlValue;
        if (strlen($crit['sql']['attribute'])>=9
        and substr($crit['sql']['attribute'],0,2)=='id'
            and ( substr($crit['sql']['attribute'],-7)=='Version' and SqlElement::is_a(substr($crit['sql']['attribute'],2), 'Version') )
                and $crit['sql']['operator']=='IN') {
          $scope=substr($crit['sql']['attribute'],2);
          $vers=new OtherVersion();
          $queryWhere.=" or exists (select 'x' from ".$vers->getDatabaseTableName()." VERS "
              ." where VERS.refType=".Sql::str($typeKanbanC)." and VERS.refId=".$table.".id and scope=".Sql::str($scope)
              ." and VERS.idVersion IN ".$critSqlValue
              .")";
        }
        if ($crit['sql']['operator']=='NOT IN') {
          $queryWhere.=" or ".$table . "." . $crit['sql']['attribute']. " IS NULL ";
        }
        if ($crit['sql']['operator']!=' exists ') {
          $queryWhere.=")";
        }
      }
    }
  }
  foreach ($arrayFilter as $crit) {
    if ($crit['sql']['operator']=='SORT') {
      $doneSort=false;
      $split=explode('_', $crit['sql']['attribute']);
      if (count($split)>1 ) {
        $externalClass=$split[0];
        $externalObj=new $externalClass();
        $externalTable = $externalObj->getDatabaseTableName();
        $idTab+=1;
        $externalTableAlias = 'T' . $idTab;
        $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
        ' on ( ' . $externalTableAlias . ".refType='" . get_class($obj) . "' and " .  $externalTableAlias . '.refId = ' . $table . '.id )';
        $queryOrderBy .= ($queryOrderBy=='')?'':', ';
        $queryOrderBy .= " " . $externalTableAlias . '.' . $split[1]
        . " " . $crit['sql']['value'];
        $doneSort=true;
      }
      if (substr($crit['sql']['attribute'],0,2)=='id' and strlen($crit['sql']['attribute'])>2 ) {
        $externalClass = substr($crit['sql']['attribute'],2);
        $externalObj=new $externalClass();
        $externalTable = $externalObj->getDatabaseTableName();
        $sortColumn='id';
        if (property_exists($externalObj,'sortOrder')) {
          $sortColumn=$externalObj->getDatabaseColumnName('sortOrder');
        } else {
          $sortColumn=$externalObj->getDatabaseColumnName('name');
        }
        $idTab+=1;
        $externalTableAlias = 'T' . $idTab;
        $queryOrderBy .= ($queryOrderBy=='')?'':', ';
        $queryOrderBy .= " " . $externalTableAlias . '.' . $sortColumn
        . " " . str_replace("'","",$crit['sql']['value']);
        $queryFrom .= ' left join ' . $externalTable . ' as ' . $externalTableAlias .
        ' on ' . $table . "." . $obj->getDatabaseColumnName('id' . $externalClass) .
        ' = ' . $externalTableAlias . '.' . $externalObj->getDatabaseColumnName('id');
        $doneSort=true;
      }
      if (! $doneSort) {
        $queryOrderBy .= ($queryOrderBy=='')?'':', ';
        $queryOrderBy .= " " . $table . "." . $obj->getDatabaseColumnName($crit['sql']['attribute'])
        . " " . $crit['sql']['value'];
      }
    }
  }
  $queryWhere.=($queryWhere)?' and ':'';
  //$queryWhere.= "$tableName.idProject in ".getVisibleProjectsList(false);
  $queryWhere.=getAccesRestrictionClause(get_class($obT),$tableName, true);
  if(Parameter::getGlobalParameter('hideItemTypeRestrictionOnProject')=='YES'){
    $user=getSessionUser();
    $objectClass=get_class($obj);
    $showIdleProjects=(sessionValueExists('projectSelectorShowIdle') and getSessionValue('projectSelectorShowIdle')==1)?1:0;
    $showIdle=1;
    $lstGetClassList = Type::getClassList();
    $objType = $obj->getDatabaseColumnName($objectClass . 'Type');
    $lstGetClassList = array_flip($lstGetClassList);
    if(in_array($objType,$lstGetClassList)){
      $queryWhere.=($queryWhere)?' and ':'';
      $queryWhere.= $user->getItemTypeRestriction($obj,$objectClass,$user,$showIdle,$showIdleProjects);
    }
  }
  $newOrderBy="";
  if($orderBy!='' && $queryOrderBy!='')$queryOrderBy=','.$queryOrderBy;
  if($orderBy=="idstatus")$newOrderBy=$tableName3.'.sortOrder';
  if($orderBy=="idresponsible")$newOrderBy=$tableName4.'.fullName';
  if($orderBy=="idtargetproductversion")$newOrderBy=$tableName5.'.name';
  if($orderBy!= "idtargetproductversion" && $orderBy!= "idresponsible" && $orderBy!= "idstatus" && $orderBy!='')$newOrderBy=$tableName.'.'.$orderBy;
  
//   if(!$isStatus){
//     $query="SELECT $tableName.id as id,
//            $tableName.name as name,
//            $tableName.id".$typeKanbanC."Type as idtickettype,
//            $tableName.idStatus as idstatus,
//            $tableName.idProject as idproject,";
//     if(property_exists($typeKanbanC, "idPriority")) {
//     	$query.="$tableName.idPriority as idpriority, ";
//     } else {
//     	$query.=" 0 as idpriority, ";
//     }
//     $query.=" $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')." as idtargetproductversion, ";
//     if(property_exists($typeKanbanC, "idActivity")){
//       $query.="$tableName.idActivity as idactivity,";  
//     }else{
//       $query.="0 as idactivity, ";
//     }
//     if(property_exists($obj, "WorkElement")){
//       $query.="$tableName2.plannedWork as plannedwork,";
//     }else if (property_exists($typeKanbanC, 'plannedWork')){
//       $query .=" $tableName.plannedWork as plannedwork,";
//     }else{
//       $query .=" 0 as plannedwork, ";
//     }
//     $query.=" $tableName2.realWork as realwork,
//               $tableName2.leftWork as leftwork,
//               $tableName.description as description,
//               $tableName.idResource as iduser,
//               $tableName3.sortOrder,
//               $tableName4.fullName as name4,
//               $tableName5.name as name5"; 
//     $query.=" FROM  $tableName2, $tableName3, $queryFrom";
//     $query.=" left join $tableName4 on $tableName.idresource=$tableName4.id";
//     $query.=" left join $tableName5 on $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')."=$tableName5.id";
//     $query.=" WHERE $tableName3.id=$tableName.idStatus";
//     $query.=" AND $tableName.".$obT->getDatabaseColumnName('id'.$type)." $listType";
//     $query.=" AND $tableName2.refType='".$typeKanbanC."' AND $tableName2.refId=$tableName.id $queryWhere";
//     $query.=" AND $tableName.idProject in ".getVisibleProjectsList(false).(Parameter::getUserParameter("kanbanShowIdle") ? '' : ' AND '.$tableName.'.idle=0');
//     if ($queryOrderBy!='' || $orderBy!='') {
//     	$query.=" order by $newOrderBy $queryOrderBy ";
//     }
//     $result=Sql::query($query);
//   }else{
    /*$result=Sql::query("SELECT $tableName.id as id,
    $tableName.name as name,
    $tableName.id".$typeKanbanC."Type as idtickettype,
    $tableName.idStatus as idstatus,
    $tableName.idProject as idproject,
    ".(property_exists($typeKanbanC, "idPriority") ? "$tableName.idPriority as idpriority, " : "")."
    $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')." as idtargetproductversion,
    $tableName.idActivity as idactivity,
    ".( (isset($tableName2)) ? " $tableName2.plannedWork as plannedwork, " : ( (property_exists($typeKanbanC, 'plannedWork'))?" $tableName.plannedWork as plannedwork,":"0 as plannedwork,") )."
    ".(isset($tableName2) ? "   $tableName2.realWork as realwork, " : "")."
    $tableName.description as description,
    ".(isset($tableName2) ? "    $tableName2.leftWork as leftwork, " : "")."
    $tableName.idResource as iduser,
    $tableName3.sortOrder,
    $tableName4.fullName as name4,
    $tableName5.name as name5
    FROM  $tableName2, $tableName3, $queryFrom left join $tableName4 on $tableName.idresource=$tableName4.id left join $tableName5 on $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')."=$tableName5.id WHERE $tableName3.id=$tableName.idStatus AND $tableName.".$obT->getDatabaseColumnName('id'.$type)." $listType AND $tableName2.refType='".$typeKanbanC."' AND $tableName2.refId=$tableName.id $queryWhere AND $tableName.idProject in ".getVisibleProjectsList(false).(Parameter::getUserParameter("kanbanShowIdle") ? '' : ' AND '.$tableName.'.idle=0').( ($queryOrderBy!='' || $orderBy!='') ? " order by $newOrderBy ".$queryOrderBy : ''));*/
    $query="SELECT $tableName.id as id,
    		$tableName.name as name,
    		$tableName.id".$typeKanbanC."Type as idtickettype,
    		$tableName.idStatus as idstatus,
    		$tableName.idProject as idproject,";
    if(property_exists($typeKanbanC, "idUrgency"))$query.="$tableName.idUrgency as idurgency,";
    if(property_exists($typeKanbanC, "idPriority")) {
      $query.="$tableName.idPriority as idpriority, ";
    } else {
      $query.=" 0 as idpriority, ";
    }
    if ($hasVersion) $query.=" $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')." as idtargetproductversion,";
    else $query.=" null as idtargetproductversion,";
    if (property_exists($typeKanbanC, "idActivity")) {
    	$query.=" $tableName.idActivity as idactivity,";
    }else{
      $query.=" null as idactivity, ";
    }
    if(isset($tableName2)){
      $query.=" $tableName2.plannedWork as plannedwork,"; 
    }else if(property_exists($typeKanbanC, 'plannedWork')){
      $query .=" $tableName.plannedWork as plannedwork, ";
    }else{
      $query .=" 0 as plannedwork, ";
    }
    if(isset($tableName2)){
      $query .=" $tableName2.realWork as realwork, ";
    }else{
      $query.= " 0 as realwork, ";
    }
    $query .=" $tableName.description as description, ";
    if(isset($tableName2)){
      $query .=" $tableName2.leftWork as leftwork, ";
    } else {  
      $query .=" 0 as leftwork, ";
    }
    $query .=" $tableName.idResource as iduser,
               $tableName3.sortOrder,
               $tableName4.fullName as name4";
    if ($hasVersion) $query .=", $tableName5.name as name5";
    $query.=" FROM ";
    if (isset($tableName2)) {
    $query.=" $tableName2,";
    }else {
      $query.="";
    }
    $query.=" $tableName3, $queryFrom"; 
    $query.=" left join $tableName4 on $tableName.idresource=$tableName4.id";
    if ($hasVersion) $query.=" left join $tableName5 on $tableName.".$obT->getDatabaseColumnName('idTargetProductVersion')."=$tableName5.id";
    $query.=" WHERE $tableName3.id=$tableName.idStatus";
    $query.=" AND $tableName.".$obT->getDatabaseColumnName('id'.$type)." $listType";
    if (isset($tableName2)) {
      $query.=" AND $tableName2.refType='".$typeKanbanC."' AND $tableName2.refId=$tableName.id $queryWhere";
    }else {
      $query.=" $queryWhere";
    }
    $query.=" AND ($tableName.idProject in ".getVisibleProjectsList(false).(($typeKanbanC=='Requirement' and getSessionValue('project')=='*')?" or $tableName.idProject is null":"").')'.(Parameter::getUserParameter("kanbanShowIdle") ? '' : ' AND '.$tableName.'.idle=0');
    if ($queryOrderBy!='' || $orderBy!='') {
      $query.=" order by $newOrderBy $queryOrderBy ";
    }
    
    $result=Sql::query($query);
  $final=array();
  while ($line = Sql::fetchLine($result)) {
    $final[]=$line;
  }
  return $final;
}

function getItemsFromTypeIdKanban($from,$nextFrom,$type,$isStatus,$result,$jsonD){
  global $typeKanbanC,$arrayProject;
  $arrayProfile=array();
  $nb=0;
  $nListQuery=array();
  foreach($result as $line) {
    $nListQuery[$line['id']]=$line;
  }
  foreach ($nListQuery as $line) {
    $idType=$from;
    $add="";
    if(!isset($arrayProject[$line['idproject']])){
      $proJ=new Project($line['idproject'],true);
      $arrayProject[$line['idproject']]=$proJ->getColor();
    }
    if(!isset($arrayProfile[$line['idproject']])){
      $arrayProfile[$line['idproject']]=getSessionUser()->getProfile($line['idproject']);
    }
    $color=$arrayProject[$line['idproject']];
    if($isStatus){
      $idType=$line['idstatus'];
      $add='-'.$line['idtickettype'].'-'.$arrayProfile[$line['idproject']];
    }
    $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
    if(($seeWork==1 || ($seeWork == null && $seeWork!=0)) && PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL")$seeWork=true; else $seeWork=false;

    $idKanban = Parameter::getUserParameter("kanbanIdKanban");
    $handle='dojoDndHandle';
    
    if(securityGetAccessRightYesNo("menu".$typeKanbanC, "update", new $typeKanbanC($line['id'],true))!="YES")$handle="";
    	$numCol = count($jsonD['column']);
    $mode = "display";
    kanbanDisplayTicket($line['id'],$type, $idKanban,$from, $line, $add, $mode);
    $nb++;
  }
}


function getIdsOfType($from,$nextFrom,$type){
  if($from==$nextFrom){
    return $from;
  }else{
    $listId=Array();
    $min=-100000000;
    $max=100000000;
    if($from!=0)$min=SqlList::getFieldFromId($type, $from, "sortOrder");
    if($nextFrom!=0)$max=SqlList::getFieldFromId($type, $nextFrom, "sortOrder");
    $sub=$min;
    if($min>$max){
      $min=$max;
      $max=$sub;
    }
    $obT=new $type();
    $tableName=$obT->getDatabaseTableName();
    //$result=Sql::query("SELECT t.id as typen FROM $tableName t WHERE t.sortOrder<=$max and t.sortOrder>=$min ");
    $result=Sql::query("SELECT t.id as typen FROM $tableName t WHERE (t.sortOrder<$max or t.id=$nextFrom) and (t.sortOrder>$min or t.id=$from)  ");
    while ($line = Sql::fetchLine($result)) {
      $listId[]=$line["typen"];
    }
    $final="";
    $ite=0;
    if (count($listId)==0) return 0;
    foreach ($listId as $idType){
      $final.=$idType;
      if(count($listId)!=$ite+1) $final.=",";
      $ite++;
    }
    return $final;
  }
}

function kanbanListSelect($user,$name,$type,$idKanban) {
  global $typeKanbanC;
  $kanban=new Kanban();
  $mineList=$kanban->getSqlElementsFromCriteria(null, false," idUser=$user->id ");
  $kanbanList=$kanban->getSqlElementsFromCriteria(null, false," idUser!=$user->id AND isShared=1 ");
  // Display Result
  echo '<div style="width:100%">
          <div style="float:left;width:20%;margin-top: 4px;">
            <div dojoType="dijit.form.DropDownButton"
              style="width: 80px;height:24px;margin:0 auto;color:#000;float:left;margin-right:15px;"
              id="kanbanListSelect" name="entity">
              <span>'.i18n("kanbanTitleButton").'</span>
                <div data-dojo-type="dijit/TooltipDialog">';
  $iterateur=0;
  echo '<span class="kanbanTextTitle" style="float:left;height:15px;font-weight:bold;" disabled="disabled" value="-2" '
      . ' title="' . i18n("kanbanSelectKanban") . '" >'.i18n("kanbanMine").'</span><br/>';
  if(count($mineList)==0)echo '<span disabled="disabled" onclick="dijit.byId(\'kanbanListSelect\').closeDropDown();" style="float:left;height:15px;" '
        . ' >&nbsp;&nbsp;&nbsp;&nbsp;'.i18n('noDataFound').'</span><br/>';
  foreach ($mineList as $line) {
    $jsonDecode=json_decode($line->param,true);
    if(!isset($jsonDecode['typeData'])){
      $jsonDecode['typeData']='Ticket';
      $line->param=json_encode($jsonDecode);
      $line->save();
    }
    $typeKanbanCTmp=$jsonDecode['typeData'];
    if (isNewGui()) echo '<div style="margin-top:5px">';
    echo '
    <div class="imageColorNewGuiNoSelection icon'.$typeKanbanCTmp.'16 icon'.$typeKanbanCTmp.' iconSize16" style="width:16px;height:16px;float:left"></div>
    <span onclick="kanbanGoToKan('.$line->id.');dijit.byId(\'kanbanListSelect\').closeDropDown();" class="menuTree" style="float:left;height:15px;'.((isNewGui())?'position:relative;top:-2px;':'').'" '
        . ' >&nbsp;&nbsp'
            . htmlEncode($line->name)
            . "</span>";
          echo '  <a class="" onClick="copyKanban('.$line->id.')" title="' . i18n('kanbanCopy'). '" >'
              .formatSmallButton('Copy')
              .'</a> ';
          echo '  <a class="" onClick="editKanban('.$line->id.')" title="' . i18n('kanbanEdit'). '" >'
              .formatSmallButton('Edit')
              .'</a> ';
          if($line->isShared==0) echo '  <a class="" onClick="plgShareKanban('.$line->id.')" title="' . i18n('kanbanShare'). '" >'
              .formatSmallButton('Share')
              .'</a> ';
          if($line->isShared==1) echo '  <a class="" onClick="plgShareKanban('.$line->id.')" title="' . i18n('kanbanUnshare'). '" >'
              .formatSmallButton('Shared')
              .'</a> ';
          echo '  <a class="" onClick="delKanban('.$line->id.', \''.i18n("kanbanDel").'\')" title="' . i18n('kanbanDelete'). '" >'
              .formatSmallButton('Remove')
              .'</a> ';
          if (isNewGui()) echo '</div>';
          else echo "<br/>";
  }
  echo '<span style="float:left;height:15px;" value="-1" '
      . ' title="' . i18n("kanbanSelectKanban") . '" ></span><br/>';
  echo '<span class="kanbanTextTitle" style="float:left;height:15px;font-weight:bold;" disabled="disabled" value="-2" '
      . ' title="' . i18n("kanbanSelectKanban") . '" >'.i18n("kanbanShared").'</span><br/>';
  if(count($kanbanList)==0)echo '<span disabled="disabled" onclick="dijit.byId(\'kanbanListSelect\').closeDropDown();" style="float:left;height:15px;" '
        . ' >&nbsp;&nbsp;&nbsp;&nbsp;'.i18n('noDataFound').'</span><br/>';
  foreach ($kanbanList as $line) {
    $jsonDecode=json_decode($line->param,true);
    if(!isset($jsonDecode['typeData'])){
      $jsonDecode['typeData']='Ticket';
      $line->param=json_encode($jsonDecode);
      $line->save();
    }
    $typeKanbanCTmp=$jsonDecode['typeData'];
    if (isNewGui()) echo '<div style="margin-top:5px">';
    echo '
        <div class="imageColorNewGuiNoSelection icon'.$typeKanbanCTmp.'16 icon'.$typeKanbanCTmp.' iconSize16" style="width:16px;height:16px;float:left"></div>
        <span onclick="kanbanGoToKan('.$line->id.');dijit.byId(\'kanbanListSelect\').closeDropDown();" class="menuTree" style="float:left;height:15px;'.((isNewGui())?'position:relative;top:-2px;':'').'" '
        . ' >&nbsp;&nbsp'
            . htmlEncode($line->name)
            . "</span>";
    echo '  <a onClick="copyKanban('.$line->id.')" title="' . i18n('kanbanCopy'). '" class="">'
        .formatSmallButton('Copy')
        .'</a> ';
    if (isNewGui()) echo '</div>';
    else echo "<br/>";
  }
  $seeWork=Parameter::getUserParameter("kanbanSeeWork".Parameter::getUserParameter("kanbanIdKanban"));
  
  if(($seeWork==1 || ($seeWork == null && $seeWork!=0)) && PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL")$seeWork=true; else $seeWork=false;
  if ($typeKanbanC=='Requirement' or $typeKanbanC=='Action') $seeWork=false;
  echo '<span style="float:left;height:30px;">&nbsp;</span><br/></div></div>';
  echo '<div dojoType="dijit.form.Button" class="detailButton" onclick="loadDialog(\'dialogKanbanUpdate\', function(){kanbanFindTitle(\'addKanban\');}, true, \'&typeDynamic=addKanban\', true, false);" "
      ." style="float:left;position:relative;margin-right:8px;margin-top:-1px;">'
      .formatIcon('KanbanAdd',22,i18n('kanbanAdd'))
      ."</div>";
  if($idKanban!=-1 && myKanban($idKanban))echo "<div dojoType=\"dijit.form.Button\" class=\"detailButton\" 
                                               style=\"float:left;position:relative;margin-right:8px;margin-top:-1px;\"
                                               onclick=\"loadDialog('dialogKanbanUpdate', function(){kanbanFindTitle('addColumnKanban');}, true, '&typeDynamic=addColumnKanban&typeD=$type&idKanban=$idKanban', true, false);\">"
                                               .formatIcon('KanbanAddColumns',22,i18n('kanbanAddColumn'))
                                               ."</div>";
  if($idKanban!=-1)echo "<div dojoType=\"dijit.form.Button\" class=\"detailButton\"  
                        style=\"float:left;position:relative;margin-top:-1px;\"
                        onclick=\"showDetail('refreshActionAdd".$typeKanbanC."',1,'".$typeKanbanC."',false,'new');\">" 
                        .formatIcon('KanbanAdd'.$typeKanbanC,22, i18n('kanbanAdd'.$typeKanbanC))
                        ."</div>";
  echo "</div><div style=\"width:60%;float:left;font-size: 16px;font-weight: bold;text-align:center;\">
                <span>$name</span>&nbsp;</div><div style=\"float:left;height:10px;padding-top:5px;width:20%;text-align:right;\">";
  if(PlanningElement::getWorkVisibiliy(getSessionUser()->idProfile)=="ALL" and $typeKanbanC!='Requirement' and $typeKanbanC!='Action' )echo i18n('kanbanSeeWork').' <div style="margin-right:8px;" dojoType="dijit.form.CheckBox" type="checkbox" class="whiteCheck" onchange="kanbanSeeWork()" '.($seeWork ? ' checked="checked"':'').'></div><br/>';
  echo i18n('labelShowIdle').' 
      		<div style="margin-right:8px;margin-top:2px;" 
       		  title="'.i18n('labelShowIdle').'" dojoType="dijit.form.CheckBox" '.(Parameter::getUserParameter("kanbanShowIdle") ? 'checked="checked"' : '').' type="checkbox" class="whiteCheck"
            id="listShowIdle" name="listShowIdle">
            <script type="dojo/method" event="onChange" >
            	kanbanShowIdle('.$idKanban.');
            </script>
          </div><br/>';
  
  echo i18n("labelKanbanFullWidthElement").' 
      		<div style="margin-right:8px;margin-top:2px;" 
            title="'.i18n("labelKanbanFullWidthElement").'" dojoType="dijit.form.CheckBox" '.((Parameter::getUserParameter("kanbanFullWidthElement")=='on')? 'checked="checked"' : '').' type="checkbox" class="whiteCheck"
            id="kanbanFullWidthElement" name="kanbanFullWidthElement">
            <script type="dojo/method" event="onChange" >
            	saveDataToSession("kanbanFullWidthElement",((this.checked)? "on":"off"),true);
            	kanbanFullWidthElement();		
            </script>
          </div><br/>'; 
 // }
}

/*function kanbanSortJsonOrder($a, $b) {
  $expa=explode('-', $a);
  $expb=explode('-', $b);
  $aVal=(count($expa)>1)?$expa[1]:$expa[0];
  $bVal=(count($expb)>1)?$expb[1]:$expb[0];
  if (is_int($aVal) and is_int($bVal)) {
    return intval($aVal)-intval($bVal);
  } else {
    return ($aVal<$bVal)?(-1):($aVal>$bVal)?1:0;
  }
}*/
?>