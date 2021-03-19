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
require_once "../tool/liveMeetingFunc.php";
if (! array_key_exists ( 'idMeeting', $_REQUEST )) {
  throwError ( 'Parameter idMeeting not found in REQUEST' );
}
$idMeeting = $_REQUEST ['idMeeting'];
$meeting = new Meeting ( $idMeeting );
if (! $meeting->id) {
  throwError ( 'Parameter idMeeting not found in DBBASE' );
}
$liveMeeting = new LiveMeeting ();
$findMeLi = $liveMeeting->getSqlElementsFromCriteria(array('idMeeting' => $idMeeting),true);
if (count ( $findMeLi ) != 0) $liveMeeting = reset ( $findMeLi );
else $liveMeeting->idMeeting=$idMeeting;
$param = "{}";
$noModif=true; //a changer si ya une modif
$arrayToDel=array();
$arrayToAdd=array();
$timeOrganizator=0;
$time=0;
if(count($meeting->_Assignment)==0){
  $liveMeeting->param=null;
  $liveMeeting->result=$meeting->result;
  $resSaveLM=$liveMeeting->save();
  if (getLastOperationStatus($resSaveLM)=='INVALID') traceLog("liveMeetingView.php => save LM without assignment : $resSaveLM");
}else if($liveMeeting->result!=$meeting->result){
  $liveMeeting->result=$meeting->result;
  $resSaveLM=$liveMeeting->save();
  if (getLastOperationStatus($resSaveLM)=='INVALID') traceLog("liveMeetingView.php => save LM with assignment : $resSaveLM");
}
if (isset ( $liveMeeting->param )){
  $param = json_decode ( $liveMeeting->param, true );
  if($param["meetingEndTime"]!=$meeting->meetingEndTime || $param["meetingStartTime"]!=$meeting->meetingStartTime)$noModif=false;
  foreach ( $meeting->_Assignment as $assignment ) {
    if(!isset($param[$assignment->idResource])){
      $noModif=false;
      $param[$assignment->idResource]= array (
          "name" => SqlList::getNameFromId ( "Affectable", $assignment->idResource ),
          "time" => 0,
          "color" => 'rgb(0, 128, 0)',
          "organizator" => false,
          "canSpeak" => true
      );
    }
  }
  if(count($param)!=count($meeting->_Assignment)){
    $noModif=false;
  }
  foreach ( $param as $key=>$line ) {
    if(is_int($key)){
      $find=false;
      foreach ( $meeting->_Assignment as $assignment ) {
        if($key==$assignment->idResource)$find=true;
      }
      if(!$find){
        $arrayToDel[]=$key;
        $noModif=false;
      }
    }
  }
}
if (isset ( $liveMeeting->param ) && $noModif) {
  $param = json_decode ( $liveMeeting->param, true );
} else if(count ( $meeting->_Assignment )>0) {
  $arrayTempo=array();
  if($liveMeeting->param){
    foreach ($arrayToDel as $key){
      unset($param[$key]);
    }
  }
  $thereIsOrganizator=false;
  foreach ( $meeting->_Assignment as $assignment ) {
    if(!isset($arrayTempo[$assignment->idResource]) && (!$liveMeeting->param || $param[$assignment->idResource]['canSpeak'])){
      $arrayTempo[$assignment->idResource]=0;
      if($liveMeeting->param){
        $arrayTempo[$assignment->idResource]=$param[$assignment->idResource]['time'];
        if($param[$assignment->idResource]['organizator']){
          $thereIsOrganizator=true;
        }
      }
    }
  }
  $explodeEnd = explode ( ":", $meeting->meetingEndTime );
  $explodeStart = explode ( ":", $meeting->meetingStartTime );
  if (isset($explodeEnd [1]) and isset($explodeStart [1])) {
    $timeTot=(($explodeEnd [0] + $explodeEnd [1] / 60 - ($explodeStart [0] + $explodeStart [1] / 60)) * 60 * 60);
    $time = (($explodeEnd [0] + $explodeEnd [1] / 60 - ($explodeStart [0] + $explodeStart [1] / 60)) * 60 * 60) / count ( $arrayTempo );
  } else {
    $timeTot=0;
    $time=0;
  }
  $timeOrganizator=0;
  if($thereIsOrganizator && count ( $arrayTempo )!=1){
    $timeOrganizator=2*$timeTot/(count ( $arrayTempo )+1);
    $time=$timeTot/(count ( $arrayTempo )+1);
  }else if($thereIsOrganizator && count ( $arrayTempo ) == 1){
    $timeOrganizator=$timeTot;
    $time=0;
  }else if(count ( $arrayTempo ) == 1){
    $timeOrganizator=0;
    $time=$timeTot;
  }
  date_default_timezone_set ( 'UTC' );
  $timeFormat = date ( 'H:i:s', $time );
  if(!$liveMeeting->param){
    $createArrayParam = array ();
    foreach ( $meeting->_Assignment as $assignment ) {
      $createArrayParam [$assignment->idResource] = array (
          "name" => SqlList::getNameFromId ( "Affectable", $assignment->idResource ),
          "time" => $timeFormat,
          "color" => 'rgb(0, 128, 0)',
          "organizator" => false,
          "canSpeak" => true
      );
    }
    $createArrayParam['lastTime']=$time;
    $createArrayParam['lastTimeOrganizator']=$timeOrganizator;
    $param = $createArrayParam;
  }else{
    foreach ( $meeting->_Assignment as $assignment ) {
      if($param[$assignment->idResource]['canSpeak']){
        if($param[$assignment->idResource]['color']=='rgb(0, 128, 0)'){
          if($param[$assignment->idResource]['organizator']){
            if(timeToTimeStamp($param[$assignment->idResource]['time'])!=0)$param[$assignment->idResource]['time']=date ( 'H:i:s', abs($timeOrganizator-($param['lastTimeOrganizator']-timeToTimeStamp($param[$assignment->idResource]['time']))));
            else $param[$assignment->idResource]['time']=date ( 'H:i:s', $timeOrganizator );
          }else{
            if(timeToTimeStamp($param[$assignment->idResource]['time'])!=0)$param[$assignment->idResource]['time']=date ( 'H:i:s', abs($time-($param['lastTime']-timeToTimeStamp($param[$assignment->idResource]['time']))));
            else $param[$assignment->idResource]['time']=date ( 'H:i:s', $time );
          }
        }else{
          if($param[$assignment->idResource]['organizator']){
            if(timeToTimeStamp($param[$assignment->idResource]['time'])!=0)$param[$assignment->idResource]['time']=date ( 'H:i:s', abs($timeOrganizator-($param['lastTimeOrganizator']+timeToTimeStamp($param[$assignment->idResource]['time']))));
            else $param[$assignment->idResource]['time']=date ( 'H:i:s', $timeOrganizator );
          }else{
            if(timeToTimeStamp($param[$assignment->idResource]['time'])!=0)$param[$assignment->idResource]['time']=date ( 'H:i:s', abs($time-($param['lastTime']+timeToTimeStamp($param[$assignment->idResource]['time']))));
            else $param[$assignment->idResource]['time']=date ( 'H:i:s', $time );
          }
        }
      }
    }
    $param['lastTime']=$time;
    $param['lastTimeOrganizator']=$timeOrganizator;
  }
  $param["meetingEndTime"]=$meeting->meetingEndTime;
  $param["meetingStartTime"]=$meeting->meetingStartTime;
  $liveMeeting->param = json_encode ( $param );
}
$liveMeeting->idMeeting = $idMeeting;
$liveMeeting->save ();

$detailHeight = '350px';
$detailWidth = '98%';

$result = "";
if (isset ( $liveMeeting->result ))
  $result = $liveMeeting->result;

$typeLoadBottom='normal';
if(Parameter::getUserParameter('storeParameterBottomLiveMeeting')!=null){
  $typeLoadBottom=Parameter::getUserParameter('storeParameterBottomLiveMeeting');
}
function timeToTimeStamp($time){
  $explode=explode(":", $time);
  if(count($explode)==3){
    return $explode [0]*3600 + $explode [1]*60+$explode [2];
  }else if(count($explode)==2){
    return $explode [0]*3600 + $explode [1]*60;
  }else{
    return $time;
  }
}
$hidecouters=(Parameter::getUserParameter('hideCounters')!='')?Parameter::getUserParameter('hideCounters'):'false';
?>
<div class="container <?php if (!isNewGui()) echo 'statusBar';?>" style="color: #000;"
	dojoType="dijit.layout.BorderContainer">
	<div id="titleLiveMeeting" class="listTitle"
		style="z-index: 3; overflow: visible; height:40px"
		dojoType="dijit.layout.ContentPane" region="top">
	  <input type="hidden" name="objectClassManual" id="objectClassManual" value="LiveMeeting" />
		<table width="100%">
			<tr height="100%" style="vertical-align: middle;height:36px;">
				<td width="50px" align="middle" style="<?php if (isNewGui()) echo 'position:relative;top:2px';?>"><?php echo formatIcon('Meeting', 32,null,true)?></td>
				<td ><span class="title"><?php echo i18n("Meeting").' : '.$meeting->name;?></span></td>
				<td style="width:80px;"><label for='hideCounters' style="width:250px;margin-top:3px;margin-right:5px;text-shadow:none;visibility:<?php echo (count ( $meeting->_Assignment )==0)?'hidden':'visible'; ?>"><?php echo i18n('hideCounters');?></label></td>
				<td style="width:30px;"><div id="hideCounters" name="hideCounters" dojoType="dijit.form.CheckBox" type="checkbox"  onclick="hideCounters();" style="margin-top:7px;visibility:<?php echo (count ( $meeting->_Assignment )==0)?'hidden':'visible'; ?>" <?php echo ($hidecouters=='true' or count ( $meeting->_Assignment )==0 )?'checked':'';?>></div></td>
				<td style="width:157px;position:relative;text-align:left">
						<div style="position:absolute;height:24px; padding: 2px 5px; top:6px;border-radius: 5px;margin-right: 20px;background-color: <?php echo (isNewGui())?'#FFF;border:1px solid var(--color-light);left:5px;width:70px;':'#DDD;width:78px;';?>">
								<button iconClass="iconLiveMeetingPlay22 iconLiveMeetingPlay iconSize22" class="detailButton" style="position:absolute;top:-1px;"
									dojoType="dijit.form.Button"
									title="<?php echo i18n('liveMeetingTitlePlay');?>"
									onclick="liveMeetingGoPlay();" 
									id="playPauseButton"></button>
								<button iconClass="iconLiveMeetingStop22 iconLiveMeetingStop iconSize22" class="detailButton" style="position:absolute;top:-1px;left:45px;"
									dojoType="dijit.form.Button"
									title="<?php echo i18n('liveMeetingTitleStop');?>"
									onclick="liveMeetingGoStop(<?php echo $idMeeting;?>);" 
									id="stopButton"></button>
					  </div>
					  <div style="position:absolute;width:85px; height:22px; padding: 2px 10px; margin-right: 20px; top:12px;left:100px;">
							  <button id="switchDivBottom" dojoType="dijit.form.Button"
							  <?php if($typeLoadBottom=='normal'){ ?> 
							  title="<?php echo i18n('liveMeetingTitleSwitchBottomKanban');?>"
							  iconClass="imageColorNewGui iconKanban22 iconKanban iconSize22" class="detailButton" style="cursor:pointer;padding:3px;margin-top:-10px;margin-left:-2px;height:23px"
							  <?php }else{ ?>
							  title="<?php echo i18n('liveMeetingTitleSwitchBottomNormal');?>"
							  iconClass="imageColorNewGui iconActionQuestionDecision22 iconActionQuestionDecision iconSize22" class="detailButton" style="cursor:pointer;padding:3px;margin-top:-10px;margin-left:-2px;height:23px"
							  <?php } ?>						  
									onclick="if(typeLoadBottom=='normal'){dijit.byId('switchDivBottom').setAttribute('iconClass','imageColorNewGui iconActionQuestionDecision22 iconActionQuestionDecision iconSize22');dijit.byId('switchDivBottom').setAttribute('title','<?php echo i18n('liveMeetingTitleSwitchBottomNormal');?>');typeLoadBottom='kanban';loadContent('../view/kanbanViewMain.php?storeParameterBottomLiveMeeting=true', 'divBottom');}else{dijit.byId('switchDivBottom').setAttribute('iconClass','imageColorNewGui iconKanban22 iconKanban iconSize22');dijit.byId('switchDivBottom').setAttribute('title','<?php echo i18n('liveMeetingTitleSwitchBottomKanban');?>');typeLoadBottom='normal';loadContent('../view/liveMeetingViewBottom.php?idMeeting=<?php echo $meeting->id;?>', 'divBottom');}"
									id="switchDivBottom"></button>
                      </div>
				</td>
				<td style="width:32px;position:relative;">
				  <button id="saveButton" style="margin-top:0px;margin-right:15px;position: relative;top:4px"
						dojoType="dijit.form.Button" showlabel="false"
						title="<?php echo i18n('buttonSave', array(i18n("Meeting").' : '.$meeting->name));?>"
						disabled="disabled"
						iconClass="dijitButtonIcon dijitButtonIconSave"
						class="detailButton">
						<script type="dojo/connect" event="onClick" args="evt">
		          liveMeetingSave();
            </script>
					</button>
				</td>
				<td style="width:32px;">
					<button id="exitButton" style="margin-top:0px;margin-right:15px; position: relative;top:4px"
						dojoType="dijit.form.Button" showlabel="false"
						title="<?php echo i18n('liveMeetingTitleStop');?>"
						onclick="liveMeetingGoStop(<?php echo $idMeeting;?>);"
						iconClass="dijitButtonIcon dijitButtonIconExit"
						class="detailButton">
					</button>
					</td>
			</tr>
		</table>
		<input type="hidden" id="liveMeetingId"
			value="<?php echo $liveMeeting->id;?>" />
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="liveMeetingDivCenter"
		style="padding: 10px;overflow: hidden">
		<script type="dojo/connect" event="resize" args="evt">
             dojo.xhrPost({
               url : "../tool/saveDataToSession.php?saveUserParam=true"
                  +"&idData=contentPaneBottomLiveMeeting"
                  +"&value="+dojo.byId("divBottom").offsetHeight
             });
             dojo.xhrPost({
               url : "../tool/saveDataToSession.php?saveUserParam=true"
                  +"&idData=contentPaneTopLiveMeeting"
                  +"&value="+dojo.byId("liveMeetingDivCenter").offsetHeight
             });
             liveMeetingResizeEditor();
    </script>
		<form id='liveMeetingForm' name='liveMeetingForm'
			onSubmit="return false;">
			<input dojoType="dijit.form.TextBox" type="hidden"
				id="liveMeetingTimeOrganizator" name="liveMeetingTimeOrganizator" value="<?php echo $timeOrganizator;?>">
			<input dojoType="dijit.form.TextBox" type="hidden"
				id="liveMeetingTime" name="liveMeetingTime" value="<?php echo $time;?>">
			
			<input dojoType="dijit.form.TextBox" type="hidden"
				id="refreshBottomAction" value="-1"
				onchange="if(dijit.byId(this.id).get('value')!=-1 && dijit.byId(this.id).get('value')!='')loadContent('../view/liveMeetingViewBottom.php?idMeeting=<?php echo $meeting->id;?>&typeObj=Action&idObj='+this.value, 'divBottom');dijit.byId(this.id).set('value',-1);">
			<input dojoType="dijit.form.TextBox" type="hidden"
				id="refreshBottomDecision" value="-1"
				onchange="if(dijit.byId(this.id).get('value')!=-1 && dijit.byId(this.id).get('value')!='')loadContent('../view/liveMeetingViewBottom.php?idMeeting=<?php echo $meeting->id;?>&typeObj=Decision&idObj='+this.value, 'divBottom');dijit.byId(this.id).set('value',-1);">
			<input dojoType="dijit.form.TextBox" type="hidden"
				id="refreshBottomQuestion" value="-1"
				onchange="if(dijit.byId(this.id).get('value')!=-1 && dijit.byId(this.id).get('value')!='')loadContent('../view/liveMeetingViewBottom.php?idMeeting=<?php echo $meeting->id;?>&typeObj=Question&idObj='+this.value, 'divBottom');dijit.byId(this.id).set('value',-1);">
		  <div class="<?php if (!isNewGui()) echo 'statusBar';?>"
				style="background-image:none !important;color: #000; height: 100%; width: 100%; min-width: 970px; margin: 0 auto; margin-top: 10px;">
				<table id="tabeTimeEditor" width="100%"  style="position:relative;display:<?php echo ($hidecouters!='true')?'block':'none'; ?>;">
					<tr>
						<td width="95%" style="min-width: 891px;height:100%;">
                          <?php
                          if(count ( $meeting->_Assignment )!=0)generateSpeakTimeEditor ( $param );
                          ?>
                          </td>
						<td width="5%" style="min-width: 67px; vertical-align: top;">
					</tr>
				</table>
				<?php 
				$topHeight=Parameter::getUserParameter('contentPaneTopLiveMeeting');
				$topHeight=($topHeight)?$topHeight:'300';
				$editorType=getEditorType();
				if ($editorType=='CKInline') $editorType='CK';
				
				?>
				<div style="width:100%; height:100%">
					<input id="liveMeetingResultEditorType"
						name="liveMeetingResultEditorType" type="hidden"
						value="<?php echo $editorType;?>" />
                   <?php if ($editorType=="CK") {?> 
                    <textarea style="width:<?php echo $detailWidth;?>; height:<?php echo $detailHeight;?>"
                    name="liveMeetingResult" id="liveMeetingResult"><?php echo htmlspecialchars($result);?></textarea>
                  <?php
                  } else if ($editorType == "text") {
                    $text = new Html2Text ( $result );
                    $val = $text->getText ();
                    $topHeight-=135;
                    ?>
                    <textarea id='text2' onKeyUp="formChangeInProgress=true;" dojoType="dijit.form.Textarea" 
                    id="liveMeetingResult" name="liveMeetingResult" splitter="true"
                    style="width: 100%;height:<?php echo$topHeight;?>px;max-height:<?php echo $topHeight;?>px;min-height:<?php echo $topHeight;?>px;"
                    maxlength="4000"
                    class="input"
                    onClick="dijit.byId('liveMeetingResult').setAttribute('class','');"><?php echo $val;?></textarea>
                  <?php } else {
                    $topHeight-=130?>
                    <textarea dojoType="dijit.form.Textarea" type="hidden" id='text3'
                     id="liveMeetingResult" name="liveMeetingResult"
                     style="display:none;"><?php echo htmlspecialchars($result);?></textarea>
              		  <div data-dojo-type="dijit.Editor" id="liveMeetingResultEditor"
                       data-dojo-props="onChange:function(){window.top.dojo.byId('liveMeetingResult').value=arguments[0];}
                        ,plugins:['removeFormat','bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 
                                  'justifyRight', 'justifyFull','|','insertOrderedList','insertUnorderedList','|']
                        ,onKeyDown:function(event){window.top.onKeyDownFunction(event,'liveMeetingResultEditor',this);}
                        ,onBlur:function(event){window.top.editorBlur('liveMeetingResultEditor',this);}
                        ,extraPlugins:['dijit._editor.plugins.AlwaysShowToolbar','foreColor','hiliteColor']"
                        style="color:#606060 !important; background:none; width:100%;overflow:none;height:<?php echo $topHeight;?>px"
                        ><?php echo $result;?></div>
                  <?php }?>
                  </div>
			</div>
		</form>
	</div>
<?php $bottomHeight=Parameter::getUserParameter('contentPaneBottomLiveMeeting');
  	$bottomHeight=($bottomHeight)?$bottomHeight.'px':'215px';?>
	<div id="divBottom" dojoType="dijit.layout.ContentPane" region="bottom"
		splitter="true" class="<?php if (!isNewGui()) echo 'statusBar';?>" 
		style="background-image:none !important;color: #000; height:<?php echo $bottomHeight;?>;min-width: 970px; width: 100%; margin: 0 auto; margin-top: 15px;">
		
      <?php 
      if($typeLoadBottom=='normal'){
        generateBottom($meeting);
      }else{
        $_REQUEST['needInclude']=false;
        include '../view/kanbanViewMain.php';
      } ?>
			</div>
	<script type="dojo/connect">       
    liveMeetingStart();
    typeLoadBottom='<?php echo $typeLoadBottom;?>'
</script>
</div>