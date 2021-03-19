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

/** ===========================================================================
 * Display the column selector div
 */

require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/planningColumnSelector');

$columns=Parameter::getPlanningColumnOrder();
$columnsAll=Parameter::getPlanningColumnOrder(true);
$desc=Parameter::getPlanningColumnDescription();
  
foreach ($columnsAll as $order=>$col) {
    if ( (isset($resourcePlanning) and ($col=='ValidatedWork' or $col=='Resource' or substr($col,-4)=='Cost') )
        or (isset($portfolioPlanning) and ($col=='Resource' or $col=='IdPlanningMode') )
        or (isset($versionPlanning) and (substr($col,-4)=='Cost'))) {
        // nothing
	} else if ( ! SqlElement::isVisibleField($col) ) {
		// nothing 
	}else {
	  if (isNewGui()) {
	    if ($col=='Name') {
  		  echo '<div style="padding: 2px;width:100%;height:34px;cursor:default" id="columnSelector'.$col.'" >';		
  		  echo '<span style="display:inline-block;width:15px;float:left;"><img style="width:6px" src="css/images/iconNoDrag.gif" />&nbsp;&nbsp;</span>'; 
  		}elseif (!isset($contractGantt) and ($col=='ExterRes' or $col=='ObjectType')){
  		  echo '<div style="padding: 2px;width:100%;height:34px;cursor:default;display:none;" class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn">';
  		  echo '<span style="float:left" class="dojoDndHandle handleCursor"><img style="width:10px;position:relative;top:8px;left:5px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}else if (isset($contractGantt) && $col!='ExterRes' && $col!='ObjectType' && $col!='StartDate' && $col!='EndDate' && $col!='Resource' && $col!='IdStatus' &&  $col!='Duration'){
  		  echo '<div style="padding: 2px;width:100%;height:34px;cursor:default;display:none;" class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn" >';
  		  echo '<span style="float:left" class="dojoDndHandle handleCursor"><img style="width:10px;position:relative;top:8px;left:5px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}else if (!isset($portfolioPlanning) and ($col=='IdHealthStatus' or $col=='QualityLevel' or $col=='IdTrend' or $col=='IdOverallProgress')){
  		  echo '<div style="padding: 2px;width:100%;height:34px;cursor:default;display:none;" class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn">';
  		  echo '<span style="float:left" class="dojoDndHandle handleCursor"><img style="width:10px;position:relative;top:8px;left:5px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}else {
        echo '<div style="padding: 2px;width:100%;height:34px;cursor:default" class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn">';
        echo '<span style="float:left;" class="dojoDndHandle handleCursor"><img style="width:10px;position:relative;top:8px;left:5px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}
  		$disabledClass=($col=='Id' or $col=='Name' )?'mblSwitchDisabled':'';
  		echo '<div  id="checkColumnSelector'.$col.'Sw" class="colorSwitch '.$disabledClass.'" data-dojo-type="dojox/mobile/Switch" value="'.((substr($columns[$order],0,6)!='Hidden')?'on':'off').'" leftLabel="" rightLabel="" ';
  		echo ' style="position:relative; float:left; left:5px;top:11px;z-index:99;" ';
  		echo '>';
  		echo '<script type="dojo/method" event="onStateChanged" >';
  		echo '  dijit.byId("checkColumnSelector'.$col.'").set("checked",(this.value=="on")?true:false);';
  		echo '</script>';
  		echo '</div>';
  	  echo '<span dojoType="dijit.form.CheckBox" type="checkbox" id="checkColumnSelector'.$col.'" style="display:none" ' 
  	    . ((substr($columns[$order],0,6)!='Hidden')?' checked="checked" ':'') 
  	    . (($col=='Name')?' readonly':'')
  	    . ' onChange="changePlanningColumn(\'' . $col . '\',this.checked,\'' . $order . '\');" '
  	    . '></span><label for="checkColumnSelector'.$col.'" class="checkLabel" style="position:relative;top:9px;float:none;left:15px;white-space:nowrap">';
  	  echo '&nbsp;';
  	  if($col=='ExterRes'){
  	    if(isset($objectClass) and $objectClass=='ClientContract'){
  	      echo i18n('colIdClient') . "</label>";
  	    }else if($objectClass=='SupplierContract'){
  	      echo i18n('colIdProvider') . "</label>";
  	    }
  	  }else {
  	    echo i18n('col' . $col) . "</label>";
  	  }
  	  echo '<div style="float: right; text-align:right">&nbsp;';
  	  echo '<div dojoType="dijit.form.NumberSpinner" id="planningColumnSelectorWidthId'.$order.'" ';
  	  echo (substr($columns[$order],0,6)=='Hidden')?'disabled="disabled" ':'';
  	  echo ' onChange="changePlanningColumnWidth(\'' . $col . '\',this.value)"; ';
  	  echo ' constraints="{ min:'.(($col=='Name')?'200':'20').', max:500, places:0 }"';
  	  echo ' style="width:50px; text-align: center;" value="'.htmlEncode($desc[$col]['width']).'" >';
  	  echo '</div>'; // NumberSpinner
  	  echo '&nbsp;</div>'; // style="float: right
  	  echo '</div>'; // id=columnSelector
	  } else {
  	  if ($col=='Name') {
  		  echo '<div style="padding: 2px;" id="columnSelector'.$col.'" >';		
  		  echo '<span style="display:inline-block;width:15px;"><img style="width:6px" src="css/images/iconNoDrag.gif" />&nbsp;&nbsp;</span>'; 
  		}elseif (!isset($contractGantt) and ($col=='ExterRes' or $col=='ObjectType')){
  		  echo '<div class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn" style="display:none;">';
  		  echo '<span class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}else if (isset($contractGantt) && $col!='ExterRes' && $col!='ObjectType' && $col!='StartDate' && $col!='EndDate' && $col!='Resource' && $col!='IdStatus' &&  $col!='Duration'){
  		  echo '<div class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn" style="display:none;">';
  		  echo '<span class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}else if (!isset($portfolioPlanning) and ($col=='IdHealthStatus' or $col=='QualityLevel' or $col=='IdTrend' or $col=='IdOverallProgress')){
  		  echo '<div class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn" style="display:none;">';
  		  echo '<span class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}else {
            echo '<div class="dojoDndItem" id="columnSelector'.$col.'" dndType="planningColumn">';
            echo '<span class="dojoDndHandle handleCursor"><img style="width:6px" src="css/images/iconDrag.gif" />&nbsp;&nbsp;</span>';
  		}
  	  echo '<span dojoType="dijit.form.CheckBox" type="checkbox" id="checkColumnSelector'.$col.'" ' 
  	    . ((substr($columns[$order],0,6)!='Hidden')?' checked="checked" ':'') 
  	    . (($col=='Name')?' readonly':'')
  	    . ' onChange="changePlanningColumn(\'' . $col . '\',this.checked,\'' . $order . '\');" '
  	    . '></span><label for="checkColumnSelector'.$col.'" class="checkLabel" style="white-space:nowrap">';
  	  echo '&nbsp;';
  	  if($col=='ExterRes'){
  	    if(isset($objectClass) and $objectClass=='ClientContract'){
  	      echo i18n('colIdClient') . "</label>";
  	    }else if($objectClass=='SupplierContract'){
  	      echo i18n('colIdProvider') . "</label>";
  	    }
  	  }else {
  	    echo i18n('col' . $col) . "</label>";
  	  }
  	  echo '<div style="float: right; text-align:right">&nbsp;';
  	  echo '<div dojoType="dijit.form.NumberSpinner" id="planningColumnSelectorWidthId'.$order.'" ';
  	  echo (substr($columns[$order],0,6)=='Hidden')?'disabled="disabled" ':'';
  	  echo ' onChange="changePlanningColumnWidth(\'' . $col . '\',this.value)"; ';
  	  echo ' constraints="{ min:'.(($col=='Name')?'200':'20').', max:500, places:0 }"';
  	  echo ' style="width:50px; text-align: center;" value="'.htmlEncode($desc[$col]['width']).'" >';
  	  echo '</div>'; // NumberSpinner
  	  echo '&nbsp;</div>'; // style="float: right
  	  echo '</div>'; // id=columnSelector
  	}
	}
}

?>