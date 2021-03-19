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

function kanbanAddPrinc($line) {
	$addInPrinc = '';
	$kanbanFullWidthElement = Parameter::getUserParameter ( "kanbanFullWidthElement" );
	if ($kanbanFullWidthElement == "on") {
		if ($line ['idstatus']) {
			$addInPrinc .= '<div style="float:left;margin-bottom:1px;margin-left:3px;'.((isNewGui())?'border-radius:4px;height:24px;max-height:24px;margin-top:1px;':'height:21px;max-height:21px;margin-top:2px;').';overflow:hidden;white-space:nowrap;width:25%">' . colorNameFormatter ( SqlList::getNameFromId ( "Status", $line ['idstatus'] ) . '#split#' . SqlList::getFieldFromId ( "Status", $line ['idstatus'], 'color' ), $line ['id'] ) . '</div>';
		}
		if(isset($line ['idurgency'])){
		  $addInPrinc .= '<div style="float:left;margin-bottom:1px;margin-left:3px;'.((isNewGui())?'border-radius:4px;height:24px;max-height:24px;margin-top:1px;':'height:21px;max-height:21px;margin-top:2px;').';overflow:hidden;white-space:nowrap;width:25%">' . colorNameFormatter ( SqlList::getNameFromId ( "Urgency", $line ['idurgency'] ) . '#split#' . SqlList::getFieldFromId ( "Urgency", $line ['idurgency'], 'color' ), $line ['id'] ) . '</div>';
		}
		if ($line ['idtargetproductversion']) {
			$versionName = SqlList::getNameFromId ( "TargetProductVersion", $line ['idtargetproductversion'] );
			if ($versionName == $line ['idtargetproductversion']) {
				$versionName = SqlList::getNameFromId ( "ProductVersion", $line ['idtargetproductversion'] );
			}
			$addInPrinc .= '
    	<div title="'.i18n('colIdTargetProductVersion').'" style="float:left;" class="kanbanVersion" style="border:1px solid red;border-left:1px solid #e0e0e0;margin-left:5px;">
			  <div class="imageColorNewGuiNoSelection iconProductVersion16 iconProductVersion iconSize16" style="margin-left:10px;margin-top:5px;width:16px;height:16px;float:left"></div>
        <div id="targetProductVersion' . $line ['id'] . '" style="float:left;margin:5px 0 0 2px;overflow:hidden;">
          ' . $versionName . '
        </div>
      </div>';
		}
		if (isset ($line['idactivity']) && $line['idactivity'] != 0) {
			$addInPrinc .= '
      <div title="'.((isset($line['WorkElement']))?i18n('colPlanningActivity'):i18n('colParentActivity')).'" style="float:left;">  
        <div class="imageColorNewGuiNoSelection iconActivity16 iconActivity iconSize16" style="margin-left:10px;margin-top:5px;width:16px;height:16px;float:left"></div>
        <div class="kanbanActivity" style="margin:5px 0 0 2px;overflow:hidden;float:left;">
          ' . SqlList::getNameFromId ( "Activity", $line ['idactivity'] ) . '
        </div>
      </div>';
		}
	} else {
		if ($line ['idstatus']) {
			$addInPrinc .= '<div style="height:20px" >' . colorNameFormatter ( SqlList::getNameFromId ( "Status", $line ['idstatus'] ) . '#split#' . SqlList::getFieldFromId ( "Status", $line ['idstatus'], 'color' ), $line ['id'] ).'</div>';
		}
		if ($line ['idtargetproductversion']) {
			$versionName = SqlList::getNameFromId ( "TargetProductVersion", $line ['idtargetproductversion'] );
			if ($versionName == $line ['idtargetproductversion']) {
				$versionName = SqlList::getNameFromId ( "ProductVersion", $line ['idtargetproductversion'] );
			}
			$addInPrinc .= '
      <table style="margin: 2px;">
        <tr title="'.i18n('colIdTargetProductVersion').'">
          <td>
            <div class="imageColorNewGuiNoSelection iconProductVersion16 iconProductVersion iconSize16" style="width:16px;height:16px;float:left"></div>
          </td>
          <td id="targetProductVersion' . $line ['id'] . '"  style="float:left;overflow:hidden;max-width:120px;margin-left:2px;">
            ' . $versionName . '
          </td>
        </tr>
      </table>';
		}
		if (isset ( $line ['idactivity'] )&& $line ['idactivity']!= 0) {
			$addInPrinc .= '
      <table style="margin: 2px;">
        <tr title="'.((isset($line['WorkElement']))?i18n('colPlanningActivity'):i18n('colParentActivity')).'" >
          <td>
            <div class="imageColorNewGuiNoSelection iconActivity16 iconActivity iconSize16" style="width:16px;height:16px;float:left"></div>
          </td>
          <td style="float:left;overflow:hidden;max-width:120px;margin-left:2px;">
            ' . SqlList::getNameFromId ( "Activity", $line ['idactivity'] ) . '
          </td>
        </tr>
      </table>';
		}
	}
	return $addInPrinc;
}
function displayAllWork($line, $type = 0, $numberLetter = 2) {
	global $typeKanbanC;
	$seeWork = Parameter::getUserParameter ( "kanbanSeeWork" . Parameter::getUserParameter ( "kanbanIdKanban" ) );
	if (($seeWork == 1 || ($seeWork == null && $seeWork != 0)) && PlanningElement::getWorkVisibility ( getSessionUser ()->idProfile ) == "ALL") {
		$seeWork = true;
	} else {
		$seeWork = false;
	}
	if (! $seeWork) {
		return '';
	}
	if (! isset ( $line ['plannedWork'] )) {
		$line ['plannedWork'] = 0;
	}
	if (! isset ( $line ['realWork'] )) {
		$line ['realWork'] = 0;
	}
	if (! isset ( $line ['leftWork'] )) {
		$line ['leftWork'] = 0;
	}
	if (! isset ( $line ['assignedWork'] )) {
		$line ['assignedWork'] = 0;
	}
	$formatter = 'workFormatter';
	if ($typeKanbanC == 'Ticket')
		$formatter = 'kanbanImputationFormatter';
	if ($type == 0) {
	  if($line['id'] != "n"){
      $id = $line['id'];
      $pe = new PlanningElement();
      $crit = array('refId'=>$id,'refType'=>"Activity");
      
      $peLst = $pe->getSqlElementsFromCriteria($crit,false);
      foreach ($peLst as $test){
        $assW = $test->assignedWork;
        $realW = $test->realWork;
        $leftW = $test->leftWork;
      }
      $idKanban = (Parameter::getUserParameter ( "kanbanIdKanban" ));
      $kanban = new Kanban($idKanban);
        
      if ($typeKanbanC == 'Ticket' && $kanban->type == 'Activity') {
        echo '
          <div style="margin-top:2px;"><table style="float:left;margin-right:10px;margin-top:5px;">
            <tr>
              <td class="linkHeader" style="padding:3px;cursor:auto;min-width:40px">' . i18n ( 'colAssigned' ) . '</td>
                <td class="linkHeader" style="padding:3px;cursor:auto;min-width:40px">' . i18n ( 'colReal' ) . '</td>
                <td class="linkHeader" style="padding:3px;cursor:auto;min-width:40px">' . i18n ( 'colLeft' ) . '</td>
              </tr>
              <tr>
                <td id="assignedWorkA' . $id . '" valueWork="' . str_replace ( ',', '.', $assW != null ? $assW : 0 ) . '" class="linkData" WorkFormat="' . Work::displayShortWorkUnit () . '" style="padding:3px;cursor:auto;text-align:center;">' . $formatter ( $assW ) . '</td>
                <td id="realWorkA' . $id . '" valueWork="' . str_replace ( ',', '.', $realW != null ? $realW : 0 ) . '" class="linkData" WorkFormat="' . Work::displayShortWorkUnit () . '" style="padding:3px;cursor:auto;text-align:center;">' . $formatter ( $realW ) . '</td>
                <td id="leftWorkA' . $id . '" valueWork="' . str_replace ( ',', '.', $leftW != null ? $leftW : 0 ) . '" class="linkData" WorkFormat="' . Work::displayShortWorkUnit () . '" style="padding:3px;cursor:auto;text-align:center;">' . $formatter ( $leftW ) . '</td>
              </tr>
            </table>';
      }else {
        echo '<div style="">';
      }
    }else {
      echo '<div style="margin-top:6px;">';
    }
		return '
      <table style="float:right;margin-top:5px;">
        <tr>
          <td class="linkHeader" style="padding:3px;cursor:auto;min-width:40px">' . i18n ( 'colEstimated' ) . '</td>
          <td class="linkHeader" style="padding:3px;cursor:auto;min-width:40px">' . i18n ( 'colReal' ) . '</td>
          <td class="linkHeader" style="padding:3px;cursor:auto;min-width:40px">' . i18n ( 'colLeft' ) . '</td>
        </tr>
        <tr>
          <td id="plannedWorkC' . $line ['id'] . '" valueWork="' . str_replace ( ',', '.', $line ['plannedWork'] != null ? $line ['plannedWork'] : 0 ) . '" class="linkData" WorkFormat="' . Work::displayShortWorkUnit () . '" style="padding:3px;cursor:auto;text-align:center;">' . $formatter ( $line ['plannedWork'] ) . '</td>
          <td id="realWorkC' . $line ['id'] . '" valueWork="' . str_replace ( ',', '.', $line ['realWork'] != null ? $line ['realWork'] : 0 ) . '" class="linkData" WorkFormat="' . Work::displayShortWorkUnit () . '" style="padding:3px;cursor:auto;text-align:center;">' . $formatter ( $line ['realWork'] ) . '</td>
          <td id="leftWorkC' . $line ['id'] . '" valueWork="' . str_replace ( ',', '.', $line ['leftWork'] != null ? $line ['leftWork'] : 0 ) . '" class="linkData" WorkFormat="' . Work::displayShortWorkUnit () . '" style="padding:3px;cursor:auto;text-align:center;">' . $formatter ( $line ['leftWork'] ) . '</td>
        </tr>
        <tr>
          <td>
          </td>
        </tr>
      </table>
    </div>';
	}
	if ($type == 1) {
	  if (isNewGui()) return '
       <table style="cursor:move;width:100%;">
         <tr>
           <td id="plannedWork' . $line ['id'] . '" 
               valueWork="' . str_replace ( ',', '.', $line ['plannedwork'] != null ? $line ['plannedwork'] : 0 ) . '" 
               class="" title="' . i18n ( 'colEstimated' ) . '" style="width:33%;text-align:center;padding:0px 3px 3px 3px;font-size:80%;">
               <span style="font-size:90%;color:var(--color-medium);">'.i18n ( 'colEstimated' ).'</span><br/>' . $formatter ( $line ['plannedwork'] ) . '</td>
           <td id="realWork' . $line ['id'] . '" 
               valueWork="' . str_replace ( ',', '.', $line ['realwork'] != null ? $line ['realwork'] : 0 ) . '" 
               class="" title="' . i18n ( 'colReal' ) . '" style="width:33%;text-align:center;padding:0px 3px 3px 3px;font-size:80%;">
               <span style="font-size:90%;color:var(--color-medium);">'.i18n ( 'colReal' ).'</span><br/>' . $formatter ( $line ['realwork'] ) . '</td>
           <td id="leftWork' . $line ['id'] . '" 
               valueWork="' . str_replace ( ',', '.', $line ['leftwork'] != null ? $line ['leftwork'] : 0 ) . '" 
               class="" title="' . i18n ( 'colLeft' ) . '" style="width:33%;text-align:center;padding:0px 3px 3px 3px;font-size:80%;">
               <span style="font-size:90%;color:var(--color-medium);">'.i18n ( 'colLeft' ).'</span><br/>' . $formatter ( $line ['leftwork'] ) . '</td>
         </tr>
       </table>'; 
	    
		else return '
       <table style="cursor:move;width:100%;">
         <tr>
           <td id="plannedWork' . $line ['id'] . '" valueWork="' . str_replace ( ',', '.', $line ['plannedwork'] != null ? $line ['plannedwork'] : 0 ) . '" class="linkData" title="' . i18n ( 'colEstimated' ) . '" style="text-align:center;padding:3px;">' . $formatter ( $line ['plannedwork'] ) . '</td>
           <td id="realWork' . $line ['id'] . '" valueWork="' . str_replace ( ',', '.', $line ['realwork'] != null ? $line ['realwork'] : 0 ) . '" class="linkData" title="' . i18n ( 'colReal' ) . '" style="text-align:center;padding:3px;">' . $formatter ( $line ['realwork'] ) . '</td>
           <td id="leftWork' . $line ['id'] . '" valueWork="' . str_replace ( ',', '.', $line ['leftwork'] != null ? $line ['leftwork'] : 0 ) . '" class="linkData" title="' . i18n ( 'colLeft' ) . '" style="text-align:center;padding:3px;">' . $formatter ( $line ['leftwork'] ) . '</td>
         </tr>
       </table>';
	}
	return '';
}
function kanbanImputationFormatter($value) { // This function for V5.5.2 compatibility, as equivalent does not exist yet in GeneralWork class
	return Work::displayImputation ( $value ) . ' ' . Work::displayShortImputationUnit ();
}
?>