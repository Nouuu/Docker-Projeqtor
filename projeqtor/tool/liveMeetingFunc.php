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

function generateAttachment($type,$meeting,$last=false){
  $countGood=0;
	echo '<td width="33%" style="position:relative;min-width:300px;padding:5px;border-radius:10px;height:100%;background-color:'.((isNewGui())?'var(--color-light)':'#EEE').';vertical-align:top;">'
        .'<div style="height:38px;width:32px">'.formatIcon($type,32,null,false).'</div>'
        .'<div class="messageData" style="'.((isNewGui())?'color:var(--color-dark);top:4px;':'top:7px;').'position:absolute;background:url();border:0;left:50px;font-size:200%;">'.i18n('menu'.$type).'</div>'
        .'<div style="white-space:nowrap;overflow:hidden;top:'.((isNewGui())?'4':'14').'px;left:48%;width:50%;position:absolute;text-align:right;" dojoType="dijit.layout.ContentPane">'
        .'<div style="width:85%;" dojoType="dijit.form.TextBox" id="new'.$type.'" name="new'.$type.'" ></div>'
        .'<a id="addLink" onClick="addNewLinkMeeting(\'new'.$type.'\',\'refreshBottom'.$type.'\','.$meeting->idProject.');" title="'. i18n('addLink') .'" >'
        .formatSmallButton('Add')
        .'</a></div>'
        .'';
  echo '<table style="width:100%"><tr>'
    .'<td class="linkHeader" style="width:5%"><a class="roundedButtonSmall" onClick="dijit.byId(\'refreshBottom'.$type.'\').set(\'value\',\'\');showDetail(\'refreshBottom'.$type.'\',1,\''.$type.'\');" title="' . i18n('addLink') . '" >'
       .formatSmallButton('Add')
       .'</a></td>
    <td class="linkHeader" style="width:5%">' . i18n('colId') . '</td>
    <td class="linkHeader" style="width:50%">' . i18n('colName') . '</td>
    <td class="linkHeader" style="width:20%">' . i18n('colResponsible') . '</td>
    <td class="linkHeader" style="width:10%">' . i18n('colDueDate') . '</td>
    <td class="linkHeader" style="width:10%">' . i18n('colIdStatus') . '</td></tr>
    ';
  $arrayObj=array();
  foreach ($meeting->_Link as $line) {
    $obj='';
    if ($line->ref1Type==$type){
      $obj=new $type($line->ref1Id);
    }
    if ($line->ref2Type==$type){
      $obj=new $type($line->ref2Id);
    }
    if ($obj!='') {
      if ($type=='Decision') {
        $dueDate=$obj->decisionDate;
      } else if ($obj->actualDueDate) {
        $dueDate=$obj->actualDueDate;
      } else {
        $dueDate=$obj->initialDueDate;
      }
      $arrayObj[$dueDate.'#'.$obj->id]=$obj;
    }      
  }
  ksort($arrayObj);
  foreach ($arrayObj as $obj) {
      $countGood++;
      $dueDate='';
      if ($type=='Decision') {
        $dueDate=htmlFormatDate($obj->decisionDate);
      } else {
        if ($obj->initialDueDate==$obj->actualDueDate or !$obj->actualDueDate) {
          $dueDate=htmlFormatDate($obj->initialDueDate);
        } if (!$obj->initialDueDate) {
          $dueDate=htmlFormatDate($obj->actualDueDate);
        } else {
          $dueDate='<span style="text-decoration: line-through;">'.htmlFormatDate($obj->initialDueDate).'</span><br/>'.htmlFormatDate($obj->actualDueDate);
        }
        
      }
      echo '<tr><td class="linkData">'
          .'<a onClick="dijit.byId(\'refreshBottom'.$type.'\').set(\'value\',\''.$obj->id.'-\');"" >'
       .formatSmallButton('Remove')
       .'</a>
           
        </td>
        <td class="linkData" style="position:relative;">'.'#'.$obj->id.'</td>
        <td class="linkData" style="cursor: pointer;" onclick="showDetail(\'refreshBottom'.$type.'\',1,\''.$type.'\',false,'.$obj->id.');">'.$obj->name .'</td>
        <td class="linkData" >'.SqlList::getNameFromId('Affectable',$obj->idResource).'</td>
        <td class="linkData" >'.$dueDate.'</td>
        <td class="linkData colorNameData">'.colorNameFormatter(SqlList::getNameFromId("Status", $obj->idStatus).'#split#'.SqlList::getFieldFromId("Status", $obj->idStatus,'color')).'</td></tr>';
  }
  echo '</table></td>';
  if(!$last)echo '<td style="min-width:20px;"></td>';
}

function generateSpeakTimeEditor($param){
  $countParam=0;
  foreach($param as $key=>$line){
    if(is_int($key))$countParam++;
  }
  $calculWidth=160*$countParam;
  echo '<div id="containerRessource" class="statusBarFlat" style="color:#000;width:100%;float:left;overflow-x:auto;overflow-y:hidden;position:relative;height:10%;min-height:102px;max-height:102px;"><table id="containerRessourceTab" width="'.$calculWidth.'" style="height:10%;min-height:60px;position:relative;"><tr>';
  $iterateur=0;
  foreach($param as $key=>$line){
    if(is_int($key)){
      $playIcon='<div class="iconLiveMeetingPlay iconSize16" dojoType="dijit.layout.ContentPane" style="float:left;margin-top:8px;" id="playPauseButtonItem'.$key.'"></div>';
      if($iterateur==0)$playIcon='<div class="iconLiveMeetingPlayS iconSize16" dojoType="dijit.layout.ContentPane" style="float:left;margin-top:8px;" id="playPauseButtonItem'.$key.'"></div>';
      echo'<td title="'.i18n('liveMeetingTitleSpeakResource',array($line['name'])).'" onclick="liveMeetingStartTimerSpeak(this.id,\''.$line['name'].'\',\''
          .i18n('liveMeetingBreak').'\');if(dojo.byId(\'blockFor'.$key.'\').getAttribute(\'title\')==\''
          .i18n('liveMeetingTitlePause').'\')dojo.byId(blockFor'.$key.').setAttribute(\'title\',\''
          .i18n('liveMeetingTitleSpeakResource',array($line['name'])).'\'); else dojo.byId(blockFor'.$key.').setAttribute(\'title\',\''
          .i18n('liveMeetingTitlePause').'\');" id="blockFor'.$key.'" isorganizator="'.($line['organizator'] ? '1' : '0')
          .'" canspeak="'.$line['canSpeak'].'" width="150" style="border-radius:5px 5px 0 0;position:relative;cursor:pointer;height:92px;min-height:92px; width:150px;max-width:150px;padding:5px; vertical-align:top; background-color:'.((isNewGui())?'var(--color-light)':'#EEE').';">
          <div style="width:100%;position:relative;max-height:21px;overflow:hidden;white-space:nowrap;"><h4 id="nameFor'.$key.'" style="margin:0;font-size:13px;">'
          .$line['name'].'</h4></div><div id="timeFor'.$key.'" style="width:100%;text-align:center;font-weight:bold;font-size:25px;color:'.$line['color'].'">'
          .$line['time'].'</div>
          '.$playIcon.
          '<button id="buttonChangeTimer'.$key.'" dojoType="dijit.form.Button" iconClass="'.((!$line['organizator'] && $line['canSpeak']) ? 'iconLiveMeetingNormal iconSize22' : ($line['organizator'] ? 'iconLiveMeetingOrganizator iconSize22' : 'iconLiveMeetingCanSpeak iconSize22')).'" style="'.($countParam<=1?'display:none;':'').'width:22px;height:22px; z-index:10000;position:relative;float:right;bottom:0;">
              <script type="dojo/connect" event="onClick" args="evt">
              if((dojo.byId(\'blockFor'.$key.'\').getAttribute(\'isorganizator\')==0 && dojo.byId(\'blockFor'.$key.'\').getAttribute(\'canspeak\')==1) || (thereIsOragnizator()!=-1 && thereIsOragnizator()!='.$key.')){
                liveMeetingDontSpeak('.$key.');
              }else{
                liveMeetingIsOrganizator('.$key.');
              }
              evt.cancelBubble = true; if (evt.stopPropagation) evt.stopPropagation();
              </script>
            </button>
           <div style="float:right;bottom:0;margin-top:3px;margin-right:5px;">'.formatUserThumb($key, null, null, 22, 'right').'</div>
          </td><td width="10"></td>';
      $iterateur++;
    }
  }
  echo '<tr></table></div>';
}

function generateBottom($meeting){
  Parameter::storeUserParameter('storeParameterBottomLiveMeeting', 'normal');
  echo '<div style="padding:0 10px;"><table width="100%" style="min-height:200px;"><tr>';
  generateAttachment("Action",$meeting);
  generateAttachment("Decision",$meeting);
  generateAttachment("Question",$meeting,true);
  echo '</tr></table></div>';
    
}
?>