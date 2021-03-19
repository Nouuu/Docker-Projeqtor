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
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/parameter.php');

$type=$_REQUEST['type'];
$criteriaRoot=array();
$user=getSessionUser();
$manual=ucfirst($type);

$collapsedList=Collapsed::getCollaspedList();

$parameterList=Parameter::getParamtersList($type);
switch ($type) {
	case ('userParameter'):
		$criteriaRoot['idUser']=$user->id;
		$criteriaRoot['idProject']=null;
		break;
	case ('projectParameter'):
		$criteriaRoot['idUser']=null;
		$criteriaRoot['idProject']=null;
		break;
	case ('globalParameter'):
		$criteriaRoot['idUser']=null;
		$criteriaRoot['idProject']=null;
		break;
	case ('habilitation'):
	case ('habilitationReport'):
	case ('accessRight'):
	case ('accessRightNoProject'):
	case ('habilitationOther'):
		break;
	default:
		traceHack('parameter : unknown parameter type '.$type);
		exit;		 
}
Security::checkDisplayMenuForUser(ucfirst($type));
/** =========================================================================
 * Design the html tags for parameter page depending on list of paramters
 * defined in $parameterList
 * @param $objectList array of parameters with format
 * @return void
 */
function drawTableFromObjectList($objectList) { 
	global $criteriaRoot, $type, $collapsedList;
	$displayWidth='98%';
	$longTextWidth="500px";
	$arrayReadOnly=array();
	if ($type=='globalParameter' and (Parameter::getGlobalParameter('imputationUnit')=='hours' or Parameter::getGlobalParameter('workUnit')=='hours') ) {
	  $work=new Work();
	  $cpt=$work->countSqlElementsFromCriteria(array());
	  if ($cpt>0) {
	    $arrayReadOnly['dayTime']=true;
	  }
	}
	$paramSelectedTab=($type=='globalParameter')?Parameter::getUserParameter('globalParameterSelectedTab'):null;
	if (array_key_exists('destinationWidth',$_REQUEST)) {
	  $width=$_REQUEST['destinationWidth'];
	  $width-=30;
	  $displayWidth=$width.'px';
	  $longTextWidth=($width-348-((isNewGui())?70:0)).'px';
	  
	} else {
	  if (sessionValueExists('screenWidth')) {
	    $detailWidth = round((getSessionValue('screenWidth') * 0.8) - 15) ; // 80% of screen - split barr - padding (x2)
	  } else {
	    $displayWidth='98%';
	  }
	}
	$hasTab=false;
	$hasColumn=false;
	$hasSection=false;
	$requiredFields=array();
	if (SSO::isSamlEnabled()) {
	  $requiredFields=array('SAML_idpId','SAML_idpCert','SAML_SingleSignOnService','SAML_SingleLogoutService','SAML_attributeUid');
	}
	foreach($objectList as $code => $format) {
    $requiredClass='';
    if (in_array($code,$requiredFields)) $requiredClass= 'required';
		$criteria=$criteriaRoot;
		$criteria['parameterCode']=$code;
		$helpTitle=str_replace('&#13',"\n",i18n('help'. ucfirst($code)));
		// fetch the parameter saved in Database
		if ($type=='userParameter') {
			$obj=new Parameter();
			$obj->parameterCode=$code;
			$obj->parameterValue=Parameter::getUserParameter($code);
		} else if ($type=='globalParameter') {
			$obj=new Parameter();
			$obj->parameterCode=$code;
			$obj->parameterValue=Parameter::getGlobalParameter($code);
		} else {
		  $obj=SqlElement::getSingleSqlElementFromCriteria('Parameter', $criteria);
		}
		if ($type=='userParameter') { // user parameters may be stored in session
		  if (sessionValueExists($code)) {
				$obj->parameterValue=getSessionValue($code);
			}
		}
		if ($format=='tab') {
		  if ($hasSection) {
		    echo '</table></div><br/>'; // close the section level
		  }
		  if ($hasColumn) { // close the column  level
		    echo '</td></tr></table>';
		  }
		  if ($hasTab) {
		    echo '</div>'; // close the tab level
		  } else {
		    echo '<div dojoType="dijit/layout/TabContainer" id="parameterTabContainer" style="width: 100%; height: 100%;">'; // Start the tabContanier Level
		  }
		  echo '<div id="'.substr($code,3).'" onShow="saveDataToSession(\'globalParameterSelectedTab\',\''.$code.'\',true);" dojoType="dijit/layout/ContentPane" title="'.i18n($code).'" '.(($paramSelectedTab==$code)?'selected="true"':'').'>'; // New tab level
		  $hasTab=true;
		  $hasColumn=false;
		  $hasSection=false;
		} else if ($format=='newColumn' or $format=='newColumnFull') {
		  if ($hasSection) {
		    echo '</table></div><br/>'; // close the section level
		  }
		  if ($hasColumn) { // close the column level
		    echo '</td>';
		  } else {
		    echo '<table style="width:99%; margin-left:1%;vertical-align:top;"><tr style="height:100%">'; // Open the table column container
		  }
		  if ($format=='newColumn') {
		    echo '<td style="width:50%;vertical-align:top;">';
		  } else { // $format=='newColumnFull'
		    echo '</tr><tr><td colspan="2" style="width:50%;vertical-align:top;">';
		  }
		  $hasColumn=true;
		  $hasSection=false;
		} else if ($format=="section") { // New section
		  if ($hasSection) {
		    echo '</table></div>'; // close the section level
		  }
			echo '<br/>';
			$divName=$type.'_'.$code;
			echo '<div id="' . $divName . '" dojoType="dijit.TitlePane"';
			echo ' open="' . (array_key_exists($divName, $collapsedList)?'false':'true') . '"';
			echo ' onHide="saveCollapsed(\'' . $divName . '\');"';
			echo ' onShow="saveExpanded(\'' . $divName . '\');"';
			echo ' title="' . i18n($code) . '" style="width:98%; position:relative;"';
			echo '>';
			echo '<table>';
			$hasSection=true;
	  } else {
		  echo ($code=='paramAttachmentNum' or $code=='paramAttachmentNumMail')?'<tr hidden> ':'<tr> '; // open the line level (must end with  a </td></tr>)
		  echo '<td class="crossTableLine"><label class="label largeLabel" for="' . $code . '" '.((isNewGui())?' style="margin-top:-2px;" ':'').' title="' . $helpTitle . '">' 
		              . (($format!='photo')?i18n('param' . ucfirst($code) ) . ((isNewGui())?'':' :').'&nbsp;':'')
		         .'</label></td><td style="position:relative">';
			if ($format=='list') {
				$listValues=Parameter::getList($code);
				echo '<select dojoType="dijit.form.FilteringSelect" class="input '.$requiredClass.'" name="' . $code . '" id="' . $code . '" ';
				echo autoOpenFilteringSelect();
				echo ' title="' . $helpTitle. '" style="width:200px">';
				if ( ($type=='userParameter' and $code!='startPage') or $code=='versionNameAutoformat' or $code=='SAML_allow_login' or $code=='paramMailerType' or $code=='newGui') {
					echo $obj->getValidationScript($code);
				}
				foreach ($listValues as $value => $valueLabel ) {
					$selected = ($obj->parameterValue==$value)?'selected':'';
					$value=str_replace(',','#comma#',$value); // Comma sets an isse (not selected) when in value
					echo '<option value="' . $value . '" ' . $selected . '>' . $valueLabel . '</option>';
				}
				echo '</select>';
			} else if ($format=='time') {
				echo '<div dojoType="dijit.form.TimeTextBox" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . $helpTitle . '"';
				echo ' type="text" maxlength="5" ';
				if (sessionValueExists('browserLocaleTimeFormat')) {
				  echo ' constraints="{timePattern:\'' . getSessionValue('browserLocaleTimeFormat') . '\'}" ';
				}
				echo ' style="width:50px; text-align: center;" class="input" ';
				echo ' value="T' . htmlEncode($obj->parameterValue) . '" ';
				echo ' hasDownArrow="false" ';
				echo ' >';
				echo $obj->getValidationScript($code);
				echo '</div>';
			} else if ($format=='number' or $format=='longnumber') {
				echo '<div dojoType="dijit.form.NumberTextBox" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . $helpTitle . '"';
				echo ($format=='longnumber')?' style="width: 100px;" ':' style="width: 50px;" ';
				//echo ' constraints="{places:\'0\'}" ';
				echo ' class="input" ';
				if (isset($arrayReadOnly[$code])) echo " readonly ";
				echo ' value="' .  htmlEncode($obj->parameterValue)  . '" ';
				echo ' >';
				echo NumberFormatter52::completeKeyDownEvent($obj->getValidationScript($code));
				echo '</div>';
			}else if ($format=='text' or $format=='password') {
				echo '<div dojoType="dijit.form.TextBox" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . $helpTitle . '"';
				echo ($code=='paramAttachmentMaxSize' or $code=='paramAttachmentMaxSizeMail')?' style="width: 100px;text-align: center;" ':' style="width: 200px;" ';
				echo ' class="input '.$requiredClass.'" ';
				if ($format=='password') echo ' type="password" ';
				//florent
				if($code=='paramAttachmentMaxSize' or $code=='paramAttachmentMaxSizeMail'){
				 $valChar=1;
				 $char=Parameter::getGlobalParameter('paramAttachmentNum');
				 if($code=='paramAttachmentMaxSizeMail'){
				   $char=Parameter::getGlobalParameter('paramAttachmentNumMail');
				 }
				 
				    switch ($char) {
				 	  case "K":
				 	    $valChar=1024;
				 	  break;
				 	  case "M":
				 	    $valChar=1024*1024;
				 	  break;
				 	  case "G":
				 	   $valChar=1024*1024*1024;
				 	  break;
				 	  case "T":
				 	    $valChar=1024*1024*1024*1024;
				 	  break;
				    }
				   $numb= number_format(intval($obj->parameterValue)/$valChar,0,' ',' ');
				 echo ' value="' .  htmlEncode(strval($numb).$char) . '" ';
				}else{
				  echo ' value="' .  htmlEncode($obj->parameterValue) . '" ';
				}
				//end
				echo ' >';
				echo $obj->getValidationScript($code);
				echo '</div>';
		  }else if ($format=='display') {
		      if($code=="mailerTestMessage"){
		        echo '<div class="" style="width:212px;position:relative;min-height:18px">';
		      } else {
		        echo '<div class="" style="width:'.$longTextWidth.';position:relative;min-height:18px">';
		      }
				  echo '<input type="hidden" name="'.$code.'" id="'.$code.'" value="'.htmlEncode($obj->parameterValue).'"/>';
				  echo '<div id="'.$code.'_iconMessageMail" name="'.$code.'_iconMessageMail" style="display:none;right:0;position:absolute;pointer-events:none">';
				  echo '<a onclick="mailerTextEditor('.$code.');" id="mailerTextEditor" title="' . i18n('editMailerTestMessageIcon') . '">'.formatSmallButton('Edit').'</a>';
				  echo '</div>';
				  echo '<div style="background:white;color: #555555;margin: 5px 2px 5px 0px;padding: 2px 5px 6px 5px;border: 1px solid #d4d4d4;border-radius: 5px 5px 5px 5px;" name="'.$code.'_display" id="'.$code.'_display" onmouseover="displayImageEditMessageMail(\''.$code.'\');" onmouseout="hideImageEditMessageMail(\''.$code.'\');" onclick="mailerTextEditor(\''.$code.'\');"';
				  if($code=="mailerTestMessage"){
				   	echo ' style="word-wrap:break-word;width:200px;display:inline-block;min-height:18px" ';
				  } else {
				    echo ' style="width:'.$longTextWidth.';word-wrap:break-word;display:inline-block;min-height:18px';
				  }
				  echo '</div>';
				  echo ($obj->parameterValue)?$obj->parameterValue:"&nbsp;";
				  echo '</div>';
				  echo '</div>';
			}else if ($format=='longtext') {
				echo '<textarea dojoType="dijit.form.Textarea" ';
				echo ' name="' . $code . '" id="' . $code . '"';
				echo ' title="' . $helpTitle . '"';
				echo ' style="width: '.$longTextWidth.';" ';
				echo ' class="input" ';
				echo ' >';
				echo $obj->parameterValue;
				//echo $obj->getValidationScript($code);
				echo '</textarea>';
			} else if ($format=='photo') { // for user photo 
			  //echo "</td></tr>";
			  $user=getSessionUser();
			  echo '<input type="hidden" id="objectId" value="'.htmlEncode($user->id).'"/>';
			  echo '<input type="hidden" id="objectClass" value="User"/>';
			  echo '<input type="hidden" id="parameter" value="true"/>';
			  echo '<div style="position:relative;height:100px;width:120px;top:-20px;xleft:50px;">';
			    $imageHtml=$user->drawSpecificItem('image');
			    echo $imageHtml;
			  echo '</div>';
			} else if ($format=='specific') {
			  if ($code=='password') {
			    $title=i18n('changePassword');
			    echo '<button class="roundedVisibleButton" id="changePassword" dojoType="dijit.form.Button" showlabel="true"';
			    if (0) {
			      $result .= ' disabled="disabled" ';
			    }
			    echo ' title="' . $title . '" style="vertical-align: middle;">';
			    echo '<span>' . $title . '</span>';
			    echo '<script type="dojo/connect" event="onClick" args="evt">';
			    echo ' requestPasswordChange();';
			    echo '</script>';
			    echo '</button>';
			  } else if ($code=='markAlertsAsRead') {
			    $title=$helpTitle;
			    echo '<button class="roundedVisibleButton" id="markAlertsAsRead" dojoType="dijit.form.Button" showlabel="true"';
			    echo ' iconClass="imageColorNewGui iconNotification22 iconNotification iconSize22" ';
			    echo ' title="' . $title . '" style="vertical-align: middle;">';
			    echo '<span>' . i18n('paramMarkAlertsAsRead') . '</span>';
			    echo '<script type="dojo/connect" event="onClick" args="evt">';
			    echo ' maintenance("read","Alert");';
			    echo '</script>';
			    echo '</button>';
			  } else if ($code=='showSubscribedItems') {
			    $title=$helpTitle;
			    echo '<button class="roundedVisibleButton" id="showSubscribedItems" dojoType="dijit.form.Button" showlabel="true"';
			    echo ' iconClass="imageColorNewGui dijitButtonIcon dijitButtonIconSubscribe" ';
			    echo ' title="' . $title . '" style="vertical-align: middle;">';
			    echo '<span>' . i18n('showSubscribedItemsList') . '</span>';
			    echo '<script type="dojo/connect" event="onClick" args="evt">';
			    echo '  showSubscriptionList("'.getSessionUser()->id.'");';
			    echo '</script>';
			    echo '</button>'; 
			  } else if ($code=='team') {
			  	$usr=getSessionUser();
			  	$res=new Resource($usr->id);
			  	$team=new Team($res->idTeam);
			  	echo $team->name;
			  } else if ($code=='organization') {
			  	$usr=getSessionUser();
			  	$res=new Resource($usr->id);
			  	$orga=new Organization($res->idOrganization);
			  	echo $orga->name;
			  } else if ($code=='profile') {
			  	  $usr=getSessionUser();
			  	  $prof=new Profile($usr->idProfile);
			  	  echo i18n($prof->name);
			  } else if ($code=='mailerTest') {
			    $title=$helpTitle;
			    echo '<div style="vertical-align:top">';
			    echo '<button id="testMail" dojoType="dijit.form.Button" showlabel="false"';
			    echo ' class="detailButton" iconClass="dijitButtonIcon dijitButtonIconEmail " ';
			    echo ' title="' . $title . '" style="vertical-align: middle;">';
			    //echo '<span>' . i18n('paramMailerTest') . '</span>';
			    echo '<script type="dojo/connect" event="onClick" args="evt">';
			    echo '  showWait();';
			    echo '  dojo.byId("testEmailResult").innerHTML="";';
			    echo '  var callbackAfterSave=function(){var hide=function(){hideWait();};loadDiv("../tool/sendMailTest.php","testEmailResult",null,hide);};';
			    echo '  loadDiv("../tool/saveParameter.php","testEmailSaveResult", "parameterForm", callbackAfterSave);';
			    echo '</script>';
			    echo '</button>';
			    echo '<div id="testEmailResult" style="padding-left:10px;display:inline-block;"></div>';
			    echo '<div id="testEmailSaveResult" style="display:none;"></div>';
			    echo '</div>';
			  } else if ($code=='automaticPlanningDifferential' or $code=='automaticPlanningComplete' or strpos($code, 'imputationAlertCron') !== false) { 
			      if(strpos($code, 'imputationAlertCron') !== false){
			          CronExecution::drawCronExecutionDefintion($code);
			      }else{
			          CronExecution::drawCronExecutionDefintion(substr($code,9));
			      }
			  } else if ($code=='SAML_metadata') {         
			    echo '<div style="vertical-align:top">';
			    echo '<button id="getSpMetadata" dojoType="dijit.form.Button" showlabel="true"';
			    echo 'class="roundedVisibleButton" iconClass="dijitButtonIcon dijitButtonIconDisplay" ';
			    echo ' title="'.$helpTitle.'" ';
			    echo ' style="vertical-align: middle;">'.i18n("SAML_getSpMetadata");
			    //echo '<span>' . i18n('paramMailerTest') . '</span>';
			    echo '<script type="dojo/connect" event="onClick" args="evt">';
			    echo " window.open('".SSO::getSettingValue('entityId')."','projeqtorMetadata');";
			    echo '</script>';
			    echo '</button>';
			    echo '</div>';
			  } else if ($code=='SAML_spCertMessage') {
			    echo '<div style="vertical-align:top">';
			    echo i18n('SAML_spCertMessage');
			    echo '</div>';
			  }
			}else if($format=='color'){
			  if($code=='newGuiThemeColor') $theming="setColorTheming(this.value,dojo.byId('".$type."_newGuiThemeColorBis').value);";
			  if($code=='newGuiThemeColorBis') $theming="setColorTheming(dojo.byId('".$type."_newGuiThemeColor').value,this.value);";
			  if($type=='userParameter'){
			    if($code=='newGuiThemeColor') $theming="dojo.byId('menuUserColorPicker').value=this.value;".$theming;
			    if($code=='newGuiThemeColorBis') $theming="dojo.byId('menuUserColorPickerBis').value=this.value;".$theming;
			    echo '<input type="color" id="'.$type.'_'.$code.'" name="'.$type.'_'.$code.'" onInput="'.$theming.'" onChange="saveDataToSession(\''.$code.'\',this.value.substr(1),true);setGlobalNewGuiThemeColor(\''.$code.'\', this.value.substr(1));'.$theming.'" value="#'.$obj->parameterValue.'" style="height: 24px;width: 145px;border-radius: 5px 5px 5px 5px;" />';
			  }else{
			    echo '<input type="color" id="'.$type.'_'.$code.'" name="'.$type.'_'.$code.'" onInput="'.$theming.'" onChange="saveDataToSession(\''.$code.'\',this.value.substr(1));setGlobalNewGuiThemeColor(\''.$code.'\', this.value.substr(1));'.$theming.'" value="#'.$obj->parameterValue.'" style="height: 24px;width: 145px;border-radius: 5px 5px 5px 5px;" />';
			  }
			  echo '<input type="hidden" id="'.$code.'" name="'.$code.'" value="'.$obj->parameterValue.'"/>';
			  if ($code=='newGuiThemeColor') drawColorDefaultThemes($type.'_newGuiThemeColor',$type.'_newGuiThemeColorBis',52,155);
			}
			//if ($format!='photo') {
			echo '</td></tr>'; // close the line level
			//}
		}
	}
	
	if ($hasSection) echo '</table><br/></div><br/>'; // Close the Section level
	if ($hasColumn) echo '</td></tr></table>'; // Close the column level
	if ($hasTab) echo '</div></div>'; // Close the tab level and the tab container level
	echo '</div>'; // Tab container level
}
?>
<input
  type="hidden" name="objectClassManual" id="objectClassManual"
  value="<?php echo $manual;?>" />
<div class="container" dojoType="dijit.layout.BorderContainer">
<div id="parameterButtonDiv" class="listTitle" style="z-index:3;overflow:visible"
  dojoType="dijit.layout.ContentPane" region="top">
<table width="100%">
  <tr height="100%" style="vertical-align: middle;">
    <td width="50px" align="center"><?php echo formatIcon(ucfirst($type), 32, null, true);?></td>
    <td><span class="title"><?php echo str_replace(" ","&nbsp;",i18n('menu'.ucfirst($type)))?>&nbsp;</span>
    </td>
    <td width="10px">&nbsp;</td>
    <td width="50px">
    <button id="saveParameterButton" dojoType="dijit.form.Button"
      showlabel="false"
      title="<?php echo i18n('buttonSaveParameters');?>"
      iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton">
        <script type="dojo/connect" event="onClick" args="evt">              
          submitForm("../tool/saveParameter.php","resultDivMain", "parameterForm", true);
        </script>
    </button>
    <div dojoType="dijit.Tooltip" connectId="saveButton"><?php echo i18n("buttonSaveParameter")?></div>
    </td>
    <td style="position:relative;">
    
    </td>
  </tr>
</table>
</div>
<div id="formDiv" dojoType="dijit.layout.ContentPane" region="center"
  style="overflow-y: auto; overflow-x: hidden;">
<form dojoType="dijit.form.Form" id="parameterForm" jsId="parameterForm"
  name="parameterForm" encType="multipart/form-data" action="" method="">
  <input type="hidden" name="parameterType" value="<?php echo $type;?>" />
  <?php 
  if ($type=='habilitation') {
  	htmlDrawCrossTable('menu', 'idMenu', 'profile', 'idProfile', 'habilitation', 'allowAccess', 'check', null,'idMenu') ;
  } else if ($type=='accessRight') {
  	htmlDrawCrossTable('menuProject', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfile', 'idMenu');
  } else if ($type=='accessRightNoProject') {
  	$titlePane="habilitation_ReadWritePrincipal";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuPrincipal') . '">';
  	htmlDrawCrossTable('menuReadWritePrincipal', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProject') ;
  	echo '</div><br/>';
  	$titlePane="habilitation_ReadWriteConfiguration";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuConfiguration') . '">';
  	htmlDrawCrossTable('menuReadWriteConfiguration', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProject') ;
  	echo '</div><br/>';
  	$titlePane="habilitation_ReadWriteTool";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuTool') . '">';
  	htmlDrawCrossTable('menuReadWriteTool', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProjectSimple') ;
  	echo '</div><br/>';
  	$titlePane="habilitation_ReadWriteEnvironment";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuEnvironmentalParameter') . '">';
  	htmlDrawCrossTable('menuReadWriteEnvironment', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProjectSimple') ;
  	echo '</div><br/>';
  	$titlePane="habilitation_ReadWriteAutomation";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuAutomation') . '">';
  	htmlDrawCrossTable('menuReadWriteAutomation', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProjectSimple') ;
  	echo '</div><br/>';
  	$titlePane="habilitation_ReadWriteList";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuListOfValues') . '">';
  	htmlDrawCrossTable('menuReadWriteList', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProjectSimple') ;
  	echo '</div><br/>';
  	$titlePane="habilitation_ReadWriteType";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('menuType') . '">';
  	htmlDrawCrossTable('menuReadWriteType', 'idMenu', 'profile', 'idProfile', 'accessRight', 'idAccessProfile', 'list', 'accessProfileNoProjectSimple') ;
  	echo '</div><br/>';
  } else if ($type=='habilitationReport') {
  	htmlDrawCrossTable('report', 'idReport', 'profile', 'idProfile', 'habilitationReport', 'allowAccess', 'check', null, 'idReportCategory') ;
  } else if ($type=='habilitationOther') {
  	$titlePane="habilitationOther_Imputation";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionImputationDiary') . '">';
  	htmlDrawCrossTable(array('imputation'=>i18n('imputationAccess'), 
  	                         'workValid'=>i18n('workValidate'),
  	                         'diary'=>i18n('diaryAccess'),
  			                     'subscription'=>i18n('canSubscribeForOthers'),
  	                         'scheduledReport'=>i18n('autoSendReportAccess')), 
  	    'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'accessScopeSpecific') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_WorkCost";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionWorkCost') . '">';
  	htmlDrawCrossTable(array('work'=>i18n('workAccess'),'cost'=>i18n('costAccess')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'visibilityScope') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_AssignmentManagement";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionAssignmentManagement') . '">';
  	htmlDrawCrossTable(array('assignmentView'=>i18n('assignmentViewRight'),'assignmentEdit'=>i18n('assignmentEditRight')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Buttons";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionButtons') . '">';
  	htmlDrawCrossTable(array('combo'=>i18n('comboDetailAccess'),'checklist'=>i18n('checklistAccess'),'joblist'=>i18n('joblistAccess'),'multipleUpdate'=>i18n('buttonMultiUpdate')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_PlanningRight";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionPlanningRight') . '">';
  	htmlDrawCrossTable(array('planning'=>i18n('planningRight'),'planningWithOveruse'=>i18n('canPlanWithInfiniteCapacity'),'resourcePlanning'=>i18n('resourcePlanningRight'),'changeValidatedData'=>i18n('changeValidatedData'),'changePriorityProj'=>i18n('changePriorityProject'),'changePriorityOther'=>i18n('changePriorityOther'),'changeManualProgress'=>i18n('changeManualProgress'),'validatePlanning'=>i18n('validatePlanning')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Consolidation";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionConsolidation') . '">';
  	htmlDrawCrossTable(array('lockedImputation'=>i18n('buttonLockedImputation'),'validationImputation'=>i18n('buttonValidationImputation')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Unlock";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionUnlock') . '">';
  	htmlDrawCrossTable(array('document'=>i18n('documentUnlockRight'),'requirement'=>i18n('requirementUnlockRight')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Report";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionReport') . '">';
  	htmlDrawCrossTable(array('reportResourceAll'=>i18n('reportResourceAll')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Financial";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('tabFinancial') . '">';
  	htmlDrawCrossTable(array(
  	    'generateProjExpense'=>i18n('generateProjectExpenseButton'),
  	    'situation'=>i18n('situationRight')),
  	    'scope', 'profile','idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_Delete";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('sectionDelete') . '">';
  	htmlDrawCrossTable(array(
  	    'canChangeNote'=>i18n('canChangeNote'),
  	    'canDeleteAttachment'=>i18n('canDeleteAttachment'),
  	    'canForceDelete'=>i18n('canForceDelete'),
  	    'canDeleteRealWork'=>i18n('canDeleteRealWork'), 
  	    'canForceClose'=>i18n('canForceClose'),
  	    'canUpdateCreation'=>i18n('canUpdateCreationInfo'),
  	    'viewComponents'=>i18n('viewComponents')), 
  	  'scope', 'profile','idProfile', 'habilitationOther', 'rightAccess', 'list', 'listYesNo') ;
  	echo '</div><br/>';
  	$titlePane="habilitationOther_ResourceVisibility";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('resourceVisibility') . '">';
  	htmlDrawCrossTable(array('resVisibilityList'=>i18n('resourceVisibilityList'),'resVisibilityScreen'=>i18n('resourceVisibilityScreen')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listTeamOrga') ;
  	echo '</div><br/>';
// ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY        
  	$titlePane="habilitationOther_OrganizationVisibility";
  	echo '<div dojoType="dijit.TitlePane"';
  	echo ' open="' . ( array_key_exists($titlePane, $collapsedList)?'false':'true') . '"';
  	echo ' id="' . $titlePane . '" ';
  	echo ' onHide="saveCollapsed(\'' . $titlePane . '\');"';
  	echo ' onShow="saveExpanded(\'' . $titlePane . '\');"';
  	echo ' title="' . i18n('organizationVisibility') . '">';
  	htmlDrawCrossTable(array('orgaVisibilityList'=>i18n('organizationVisibilityList'),'orgaVisibilityScreen'=>i18n('organizationVisibilityScreen')), 'scope', 'profile', 'idProfile', 'habilitationOther', 'rightAccess', 'list', 'listOrgaSubOrga') ;
  	echo '</div><br/>';
// END ADD BY Marc TABARY - 2017-02-20 - ORGANIZATION VISIBILITY        
  } else {
  	drawTableFromObjectList($parameterList);
  }
  ?></form>
</div>
</div>
