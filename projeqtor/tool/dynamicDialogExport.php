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
require_once "../tool/projeqtor.php";
if (! array_key_exists('objectClass',$_REQUEST)) {
  throwError('objectClass parameter not found in REQUEST');
}
$objectClass=$_REQUEST['objectClass'];
Security::checkValidClass($objectClass);
//gautier
if($objectClass == 'Work'){
  $week = RequestHandler::getValue('dateWeek');
  $month = RequestHandler::getValue('dateMonth');
  $isMyUser = RequestHandler::getValue('userId');
}

$obj=new $objectClass();
$idUser = getSessionUser()->id;
$cs=new ColumnSelector();
$crit=array('scope'=>'export','objectClass'=>$objectClass, 'idUser'=>$user->id);
$csList=$cs->getSqlElementsFromCriteria($crit);
$hiddenFields=array();
foreach ($csList as $cs) {
	if ($cs->hidden) {
		$hiddenFields[$cs->field]=true;
	}
}
$arrayDependantObjects=array('Document'=>array('_DocumentVersion'=>'withSection'));
$htmlresult='<td valign="top" style="width:1px">';
$contextForAttributes='global';
$fieldsArray=$obj->getFieldsArray(true);
if ((isset($fieldsArray['_sec_description']) or isset($fieldsArray['_sec_Description'])) and $objectClass!='Work')  $fieldsArray = array('_sec_description' => '_sec_description') + array('hyperlink' => 'Hyperlink') + $fieldsArray;
else $fieldsArray = array('_sec_Description' => '_sec_description') + $fieldsArray; // Fix : do nopt show link for item without description (Work for instance)
foreach($fieldsArray as $key => $val) {
  if ($key=='_sec_Description') {
    unset($fieldsArray[$key]);
    continue;
  }
	if ( ! SqlElement::isVisibleField($val) ) {
		unset($fieldsArray[$key]);
    continue;
	}
	if ($objectClass=='GlobalView' and $key=='id') {
	  unset($fieldsArray[$key]);
	  continue;
	}
	if (substr($val,0,5)=='_sec_') {
		if (strlen($val)>6) {
			$section=substr($val,5);
			if ($section=='Assignment' or $section=='Affectations' or substr($section,0,14)=='Versionproject'
       or $section=='Subprojects' or $section=='Approver' or $section=='ExpenseDetail' 
       or $section=='predecessor' or $section=='successor' or $section =='TestCaseRun'
       or $section=='Projects' or $section=='Link' or $section=='Note' or $section=='Attachment') {
			  unset($fieldsArray[$key]);
			  continue;
			}
			$fieldsArray[$key]=i18n('section' . ucfirst($section));
		}
  } else if (substr($key,0,1)=='_' or strtoupper(substr($key,0,1))==substr($key,0,1) ){ // Object
    if (isset($arrayDependantObjects[$objectClass]) and isset($arrayDependantObjects[$objectClass][$key])) {
      $included=ltrim($key,'_');
      if (SqlElement::class_exists($included)) {
        $crit=array('scope'=>'export','objectClass'=>$included, 'idUser'=>$user->id);
        $csList=$cs->getSqlElementsFromCriteria($crit);
        foreach ($csList as $cs) {
          if ($cs->hidden) {
            $hiddenFields[$included.'_'.$cs->field]=true;
          }
        }
        if ($arrayDependantObjects[$objectClass][$key]=='withSection') {   
          $fieldsArray['_sec_'.$included]=i18n('_sec_'.$included);//i18n('section' . ltrim($key,'_'));
        }
        $incObj=new $included();
        foreach ($incObj as $incKey=>$incVal) {
          if (substr($incKey,0,1)=='_') continue;
          if ($incKey=='refType' or $incKey=='refId' or $incKey=='id'.$objectClass) continue;
          if ($incObj->isAttributeSetToField($incKey,'noExport')) continue;
          $fieldsArray[$included.'_'.$incKey]=i18n('col'.ucfirst($incKey));
        }
      }
    }
    unset($fieldsArray[$key]);
    continue;
	} else {
	  $fieldsArray[$key]=$obj->getColCaption($val);
	}
	if(isset($fieldsArray[$key]) and substr($fieldsArray[$key],0,1)=="["){
		unset($fieldsArray[$key]);
		continue;
	}
}

// ADD BY Marc TABARY - 2017-03-20 - EXPORT - DON'T DRAW SECTION WITHOUT FIELD
$fieldsArrayNext = $fieldsArray;
foreach($fieldsArray as $key=>$val) {
    if(substr($key,0,5)=="_sec_") {
        reset($fieldsArrayNext);
        $next_=true;
        while(current($fieldsArrayNext)!=$val and $next_!==false) {
            $next_ = next($fieldsArrayNext);
        }
        $next_ = next($fieldsArrayNext);
        if ($next_!==false) {
            $next_key = key($fieldsArrayNext);
            if(substr($next_key,0,5)=='_sec_') {
                unset($fieldsArray[$key]);
            }
        }
    }    
}
// END ADD BY Marc TABARY - 2017-03-20 - EXPORT - DON'T DRAW SECTION WITHOUT FIELD

$countFields=count($fieldsArray);
$htmlresult.='<input type="hidden" dojoType="dijit.form.TextBox" id="column0" name="column0" value="'.$countFields.'">';
$index=1;
$last_key = end($fieldsArray);
$allChecked="checked";
foreach($fieldsArray as $key => $val){
	if(substr($key,0,5)=="_sec_"){
		if($val!=$last_key) {
			$htmlresult.='</td><td style="vertical-align:top;width: 200px;" valign="top">';
			$htmlresult.='<div class="section" style="display:table-cell;width:195px;height:32px;vertical-align:middle;marin:auto"><b>'.$val.'</b>';
			$htmlresult.='</div><br/>';
			if ($key=='_sec_DocumentVersion') {
				$htmlresult.='<div class="noteHeader" style="width:94%;height:100%">';
				$htmlresult.= '<table style="width:100%"><tr>';
				$htmlresult.='<td><input type="checkbox" dojoType="dijit.form.CheckBox" id="documentVersionAll" name="documentVersionAll" 
						onChange="dijit.byId(\'documentVersionLastOnly\').set(\'checked\',!this.checked);" />'.i18n('all').'</td>';
				$htmlresult.='<td><input type="checkbox" dojoType="dijit.form.CheckBox" id="documentVersionLastOnly" name="documentVersionLastOnly" 
						onChange="dijit.byId(\'documentVersionAll\').set(\'checked\',!this.checked);" checked=checked />'.i18n('colCurrentDocumentVersion').'</td>';
				$htmlresult.= '</tr></table>';
				$htmlresult.='</div><br/>';
			}
		}
	} else if(substr($key,0,5)=="input"){
	}else {
		$checked='checked';
		if (array_key_exists($key, $hiddenFields) or $key=='hyperlink') {
			$checked='';
			$allChecked='';
		}
    if (substr($key,0,9)=='idContext' and strlen($key)==10) {
      $ctx=new ContextType(substr($key,-1));
      $val=$ctx->name;
    } 
		$htmlresult.='<input type="checkbox" '.((isNewGui())?'class="whiteCheck" style="margin-top:0px;margin-bottom:4px;"':'').' dojoType="dijit.form.CheckBox" id="column'.$index.'" name="column'.$index.'" value="'.$key.'" '.$checked.' />';
		$htmlresult.='<label for="column'.$index.'" class="checkLabel" '.((isNewGui())?'style="font-size:100%;"':'').'>&nbsp;'.$val.'</label><br/>';
		$index++;
	}
}
$htmlresult.='</td>';
$htmlresult.="<br/>";
?>
<form id="dialogExportForm" name="dialogExportForm">
<table style="width: 100%;">
  <tr>
    <td colspan="2" class="reportTableHeader section"><?php echo i18n("chooseColumnExport");?></td>
  </tr>
  <tr><td colspan="2" >&nbsp;</td></tr>
  <tr <?php if (isNewGui()) echo 'style="height:40px;"'; ?>>
    <td>
      <input type="checkbox" <?php echo (isNewGui())?'class="whiteCheck" style="margin-top:0px;margin-bottom:2px;"':'';?> dojoType="dijit.form.CheckBox" id="checkUncheck" name="checkUncheck" value="Check" onclick="checkExportColumns();" <?php echo $allChecked?> />
      <label for="checkUncheck" class="checkLabel" style="font-size:100%"><?php echo i18n("checkUncheckAll")?></label>&nbsp;&nbsp;&nbsp;
    </td>
    <td>
      <input type="checkbox" dojoType="dijit.form.Button" id="checkAsList" class="dynamicTextButton " name="checkAsList" onclick="checkExportColumns('aslist');" 
       showLabel="true" label="<?php echo i18n("checkAsList")?>" />
    </td>
  </tr>
  <tr <?php if (isNewGui()) echo 'style="height:30px;"'; ?>>
    <td style="width:350px;text-align:right" class="dialogLabel"><?php echo i18n("exportReferencesAs")?> <?php echo (isNewGui())?'':':;';?>&nbsp;</td>
    <td > <select dojoType="dijit.form.FilteringSelect" class="input"
           <?php echo autoOpenFilteringSelect();?>
				   style="width: <?php echo (isNewGui())?'200':'150';?>px;" name="exportReferencesAs" id="exportReferencesAs">         
           <option value="name"><?php echo i18n("colName");?></option>                            
           <option value="id"><?php echo i18n("colId");?></option>
            </select>
    </td>
  </tr>
  <tr <?php if (isNewGui()) echo 'style="height:30px;"'; ?>>
    <td style="width:300px;text-align:right;" class="dialogLabel"><?php echo i18n("exportHtml")?> <?php echo (isNewGui())?'':':;';?>&nbsp;</td>
    <td > 
                    <?php if (isNewGui()) {?>
                  <div  id="exportHtmlSwitch" class="colorSwitch" data-dojo-type="dojox/mobile/Switch" value="off" 
                    leftLabel="" rightLabel="" style="width:10px;position:relative; left:10px;top:2px;z-index:99;" >
  		              <script type="dojo/method" event="onStateChanged" >
  		                dijit.byId("exportHtml").set("checked",(this.value=="on")?true:false);
  		              </script>
  		             </div>
  		          <?php }?>
    <div type="checkbox" dojoType="dijit.form.CheckBox" id="exportHtml" <?php if (isNewGui()) echo 'style="display:none;"';?> name="exportHtml" ></div></td>
  </tr>
    <!--Add a separator to use for csv files - F.KARA #458-->
    <tr <?php if (isNewGui()) echo 'style="height:30px;"'; ?>>
        <td style="width:300px;text-align:right" class="dialogLabel"><?php echo i18n("paramCsvSeparator")?> <?php echo (isNewGui())?'':':;';?>&nbsp</td>
        <td >
            <select dojoType="dijit.form.FilteringSelect" class="input"
                value="<?php echo Parameter::getUserParameter('csvSeparator');?>"
                onChange="saveDataToSession('csvSeparator',this.value,true);"
                <?php echo autoOpenFilteringSelect();?>
                     style="width: <?php echo (isNewGui())?'40':'150';?>px;" name="separatorCSV" id="separatorCSV">
                <option value=";">;</option>
                <option value=",">,</option>
            </select>
        </td>
    </tr>
    <!--END a separator to use for csv files - F.KARA #458-->
   <?php if(!isNewGui() and $objectClass != 'Work' ){?>
  <tr><td colspan="2" >&nbsp;</td></tr>
  
  <?php  } if( $objectClass == 'Work' ){?>
  <tr>
    <td style="width:300px;text-align:right" class="dialogLabel"><?php echo i18n("exportDateAs")?> <?php echo (isNewGui())?'':':;';?>&nbsp;</td>
    <td > <select dojoType="dijit.form.FilteringSelect" class="input" 
           <?php echo autoOpenFilteringSelect();?>
				   style="width:<?php echo (isNewGui())?'200':'150';?>px;" name="exportDateAs" id="exportDateAs">         
           <option value="<?php echo 'W'.$week ;?>"> <?php echo i18n("selectWeek");?> </option>                            
           <option value="<?php echo 'M'.$month ;?>"><?php echo i18n("selectMonth");?></option>
           <option value="<?php echo 'Y'.substr($month,0,4);?>"><?php echo i18n("selectYear");?></option>
           <option value="<?php echo 'All' ;?>"><?php echo i18n("getAll");?></option>
			    </select></td>
  </tr>
  <tr>
    <td style="width:300px;text-align:right" class="dialogLabel"><?php echo i18n("exportRessourceAs")?> <?php echo (isNewGui())?'':':;';?>&nbsp;</td>
    <td > <select dojoType="dijit.form.FilteringSelect" class="input" 
           <?php echo autoOpenFilteringSelect();?>
				   style="width: <?php echo (isNewGui())?'200':'150';?>px;" name="exportRessourceAs" id="exportRessourceAs">         
           <option value="<?php echo 'C'.$isMyUser ;?>"> <?php echo i18n("selectResource");?> </option>                            
           <option value="<?php echo 'A' ;?>"><?php echo i18n("allResource");?></option>
			    </select></td>
  </tr>
  <?php }?>
  </table>
<table style="width: 100%;">
  <tr>
  <?php  echo $htmlresult;?>
  </tr>
</table>
<div style="height:10px;"></div>    
<div style="height:5px;border-top:1px solid #AAAAAA"></div>    
<table style="width: 100%">
  <tr>
    <td style="width: 50%; text-align: right;">
    <button align="right" dojoType="dijit.form.Button" class="mediumTextButton"
      onclick="closeExportDialog();">
      <?php echo i18n("buttonCancel");?></button>&nbsp;
    </td>
    <td style="width: 50%; text-align: left;">&nbsp;
    <button align="left" dojoType="dijit.form.Button" class="mediumTextButton"
      id="dialogPrintSubmit"
      onclick="executeExport('<?php echo $objectClass;?>','<?php echo $idUser;?>');">
      <?php echo i18n("buttonOK");?></button>
    </td>
  </tr>
</table>
</form>
