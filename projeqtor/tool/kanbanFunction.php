<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
******************************************************************************
*** WARNING *** T H I S    F I L E    I S    N O T    O P E N    S O U R C E *
******************************************************************************
*
* Copyright 2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
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

require_once "../tool/kanbanConstructPrinc.php";
function kanbanDisplayTicket($id, $type, $idKanban, $from, $line, $add, $mode) {
	global $typeKanbanC, $arrayProject;
	$kanB = new Kanban ( $idKanban, true );
	$json = $kanB->param;
	$jsonDecode = json_decode ( $json, true );
	$idType = $from;
	if ($type=='Status' and isset($line['idstatus'])) {
		$idType=$line['idstatus'];
	}
	if (! $typeKanbanC) {
		$typeKanbanC = $jsonDecode ['typeData'];
	}
	$handle = 'dojoDndHandle';
	if (securityGetAccessRightYesNo ( "menu" . $typeKanbanC, "update", new $typeKanbanC ( $line ['id'], true ) ) != "YES")
		$handle = "";
	
	$proJ = new Project ( $line ['idproject'], true );
	$arrayProject [$line ['idproject']] = $proJ->getColor ();
	$color = $arrayProject [$line ['idproject']];
	
	$destWidth=RequestHandler::getValue('destinationWidth');
	if (!$destWidth) $destWidth=1920;
	$nbCol=(isset($jsonDecode['column']) and is_array($jsonDecode['column']))?count($jsonDecode['column']):1;
	$spaces=10*($nbCol+1);
	$ticketWidth=(($destWidth-$spaces)/$nbCol)-40;
	if ($ticketWidth<305) $ticketWidth=305;
	$nbTktPerCol=intval($ticketWidth/150);
	$ticketWidthSmall=(round($ticketWidth/$nbTktPerCol,1)-(2*$nbTktPerCol)+3).'px';
	
	
	if (isset ( $line ['description'] )) {
	  $description=$line ['description'];
	  $text = new Html2Text ($description);
	  $descr = $text->getText ();
	  $descr=htmlspecialchars($descr);
	  if (strlen ($description) > 4000) {
		  $descr1 = substr ( $descr, 0, 4000);
			$ticketDescr = nl2brForPlainText ( $descr1 );
			$descr2 = substr ( $descr, 0, 200 );
			$ticketDescr2 = nl2brForPlainText ( $descr2 );
	  } else {
	    	    $ticketDescr=str_replace('<img ','<img style="max-width:'.($ticketWidth-10).'px;" ',$description);
	    $descr2 = substr ( $descr, 0, 200);
	    $ticketDescr2 = nl2brForPlainText ( $descr2 );
	  }
	} else {
		$ticketDescr = '<div style="font-style:italic; color:#CDCADB; ">' . i18n ( "kanbanNoDescription" ) . '</div>';
		$ticketDescr2 = '<div style="font-style:italic; color:#CDCADB; ">' . i18n ( "kanbanNoDescription" ) . '</div>';
	}
	
	$kanbanFullWidthElement = Parameter::getUserParameter ( "kanbanFullWidthElement" );
	$titleObject=$typeKanbanC . ' #' . $line ['id'] 
	  ." (".SqlList::getNameFromId('Type', $line['idtickettype']).')'
	  ."\n".i18n('Project').' #'.$line['idproject'].' - '.$proJ->name;
	if ($kanbanFullWidthElement == "on") {
		$numCol = count ( $jsonDecode ['column'] );
		
		echo ' <script type="dojo/connect">
        //divWidthKanban(' . $id . ',\'' . $type . '\',' . $numCol . ');
       </script>';
		if ($mode != "refresh") {
			echo '
      <div class="dojoDndItem ' . $handle . ' ticketKanBanStyleFull ticketKanBanColor " style="'.((isNewGui())?'border-radius:5px;':'').'border-left:3px solid ' . $color . ';" fromC="' . $from . '" id="itemRow' . $line ['id'] . '-' . $type . '"
      dndType="' . ($type == 'Status' ? 'typeRow' . $idType . $add : ($type == 'TargetProductVersion' ? $from : SqlList::getFieldFromId ( $typeKanbanC, $line ['id'], "idProject" ))) . '">';
		}
		echo '
      <div id="titleTicket' . $line ['id'] . '" class="kanbanTitleTicketFull" style="background-color:'.((isNewGui())?'var(--color-lighter)':'#F1F1F1').';border-bottom: 1px solid #AAAAAA;">
    	  <div style="font-size:12px;text-align:center;color:#000;" >
          <span style="float:left;font-size: 9px;background:' . $color . ';color:' . htmlForeColorForBackgroundColor ( $color ) . '; border:1px solid #AAAAAA;padding:1px 1px;margin:1px 2px 1px 1px;" title="' . $titleObject . '">#' . $line ['id'] . '</span>
          <span style="float:right;font-size: 9px;max-height:12px;max-width:50px;overflow:hidden;background:white;color:black;border:1px solid #AAAAAA;padding:1px 2px;margin:1px 2px 0px 0px;">' . SqlList::getNameFromId('Type', $line['idtickettype']) . '</span>
          <span id="name' . $line ['id'] . '">' . htmlEncode ( $line ['name'] ) . '</span>
        </div>
      </div>
      <div id="objectDescr' . $line ['id'] . '" dojoType="dijit.layout.ContentPane" region="center" class=""
        style="width:98%;max-width:'.$ticketWidth.'px;padding:4px;font-size:12px;font-family:arial;word-wrap:break-word;max-height:300px;overflow-y:auto;cursor:move;border-top:1px solid #CDCADB;border-bottom:1px solid #CDCADB;"
        onScroll="kanbanShowDescr(\'description\',\'' . $typeKanbanC . '\', ' . $line ['id'] . ');">
        ' . $ticketDescr . '
      </div>
      <input dojoType="dijit.form.TextBox" id="descr_' . $line ['id'] . '" type="hidden" value="truncated" />
        ' . displayAllWork ( $line, 1, 4 ) . '
      <div style="margin:2px 0;">
    	  <div style="float:left; margin:2px;" id="userThumbTicket' . $line ['id'] . '" >
          ' . formatUserThumb ( $line ['iduser'], SqlList::getNameFromId ( "Affectable", $line ['iduser'] ).'<br/><span style="font-size:80%"><i>('.i18n('colResponsible').')</i></span>', "", 22, 'left', false, $line ['id'] ) . '
        </div>
          ' . (isset ( $line ['idpriority'] ) ? '
        <div style="float:left;height:22px;margin-top:'.((isNewGui())?'-3px;margin-left:4px;':'3px;').'" id="kanbanPriority">
          ' . formatColorThumb ( "idPriority", $line ['idpriority'], 22, 'left', SqlList::getNameFromId ( "Priority", $line ['idpriority'] ) ) . '
        </div>' : '') . '
      </div>
      <div id="divPrincItem' . $line ['id'] . '" style="cursor:move;">
       ' . kanbanAddPrinc ( $line ) . '
      </div> ';
      $object= new $typeKanbanC ($line['id']);
	 $nbBadge=((isset($object->_Note))?count ($object->_Note):'');
	 $margin=($nbBadge>9)?'-10':'-7';
	 $badge= '<div id="kanbanBadge_'.$line['id'].'" class="kanbanBadge" style="">'.$nbBadge.'</div>';
      echo '<table style="margin:2px 2px 0 2px;width:58px;float:right;vertical-align: middle;">
        <tr>
            <td>
              <div id="badges" style="position:relative">
              <div id="addComent" onclick="activityStreamKanban(' . $line ['id'] . ', \'' . $typeKanbanC . '\', \''.$type.'\');" style="margin-bottom:2px;margin-right:8px;margin-top:2px;" title=" ' . i18n ( 'commentImputationAdd' ) . ' ">
                ' . formatSmallButton ( 'AddComment' ) . '
                    <div  style="pointer-events: none;position:absolute;bottom:'.((isNewGui())?'-3px':'-1px').';margin-left:'.$margin.'px;width:5px;">
                    '.((count($object->_Note)!=0)?$badge:'').'
                    </div>
              </div>
            </div>
            </td>
            <td>
          	  <div class="roundedButtonSmall"
                style="width:16px;height:16px;cursor:pointer;vertical-align:text-bottom;margin-right:5px;float:left;"
                onclick="showDetail(\'refreshActionAdd' . $typeKanbanC . '\',1,\'' . $typeKanbanC . '\',false,' . $line ['id'] . ');" title="' . i18n('kanbanEditItem',array($line ['id'])) . '>"
                ' . formatSmallButton ( 'Edit',false ) . '
              </div>
            </td>
            <td>
              <div class="roundedButtonSmall"
                style="width:20px;height:16px;cursor:pointer;float:right;vertical-align:text-bottom;"
         		    onclick="gotoElement(\'' . $typeKanbanC . '\',' . htmlEncode ( $line ['id'] ) . ', true);"	title="' .i18n('kanbanGotoItem',array($line ['id'])) . '" style="width:18px;">
         		   ' . formatSmallButton ( 'Goto',true ) . '     
              </div>
            </td>
        </tr>
      </table>';
    //echo '</div>';
		if ($mode != "refresh") {
			echo '</div>';
		}
	} else {
		// if button is unchecked elements are in normal mode
		if ($mode != "refresh") {
			echo '
    <div class="dojoDndItem ' . $handle . ' ticketKanBanStyle ticketKanBanColor " style="width:'.$ticketWidthSmall.';border-left:3px solid ' . $color . ';" fromC="' . $from . '" id="itemRow' . $line ['id'] . '-' . $type . '"
    dndType="' . ($type == 'Status' ? 'typeRow' . $idType . $add : ($type == 'TargetProductVersion' ? $from : SqlList::getFieldFromId ( $typeKanbanC, $line ['id'], "idProject" ))) . '">';
		}
		echo ' 
      <div id="titleTicket' . $line ['id'] . '" class="kanbanTitleTicket" style="background-color:'.((isNewGui())?'var(--color-lighter)':'#F1F1F1').'; border-bottom: 1px solid #AAAAAA;">
        <div style="color:#000; " id="name' . $line ['id'] . '">
          <span style="position:relative;top:-1px;float:left;font-size: 9px;background:' . $color . ';color:' . htmlForeColorForBackgroundColor ( $color ) . '; border:1px solid #AAAAAA;padding:0.5px 1px 0 0.5px;margin:1px 0px 0px 0px;" title="'.$titleObject.'">#' . $line ['id'] . '</span>
          <span style="float:left;clear:left;font-size: 8px;max-height:10px;max-width:25px;overflow:hidden;border:1px solid #a0a0a0;color:#a0a0a0;background:white;padding:0px 1px;margin:'.((isNewGui())?'3':'0').'px 2px 0px 0px;" title="'.$titleObject.'">' . SqlList::getFieldFromId('Type', $line['idtickettype'],'code') . '</span>	
          ' . htmlEncode ( $line ['name'] ) . '
        </div>
      </div>
      <div id="divPrincItem' . $line ['id'] . '" style="cursor:move;margin-top:'.((isNewGui())?'38':'32').'px;">
        ' . kanbanAddPrinc ( $line ) . '        
      </div>
      <div id="objectDescr' . $line ['id'] . '" class=""
        style="padding:5px;font-size:12px;font-family:arial;max-height:55px;'.((isNewGui())?'min-height:55px;':'').'overflow-y:scroll;border-top:1px solid #CDCADB;border-bottom:1px solid #CDCADB;"
        onScroll="kanbanShowDescr(\'description\',\'' . $typeKanbanC . '\', ' . $line ['id'] . ');" >
          ' . $ticketDescr2 . '
      </div>
      <input dojoType="dijit.form.TextBox" id="descr_' . $line ['id'] . '" type="hidden" value="truncated" />
        ' . displayAllWork ( $line, 1, 4 ) . '
      <div style="cursor:move;width:100%;height:26px;bottom:0;">
        <div style="float:left;width:22px;height:22px; margin-top:2px; margin-left:2px;" id="userThumbTicket' . $line ['id'] . '">
          ' . formatUserThumb ( $line ['iduser'], SqlList::getNameFromId ( "Affectable", $line ['iduser'] ).'<br/><span style="font-size:80%"><i>('.i18n('colResponsible').')</i></span>', "", 22, 'left', false, $line ['id'] ) . '
        </div>
        	' . (isset ( $line ['idpriority'] ) ? '
        <div style="float:left;margin-left:5px;width:22px;height:22px;margin-top:'.((isNewGui())?'-2':'3').'px;margin-left:2px">
          ' . formatColorThumb ( "idPriority", $line ['idpriority'], 20, 'left', SqlList::getNameFromId ( "Priority", $line ['idpriority'] ) ) . '
        </div>' : '').'';
		$object= new $typeKanbanC ($line['id']);
		$nbBadge=((isset($object->_Note) )?count ($object->_Note):'');
		$margin=($nbBadge>9)?'-10':'-7';
		$badge= '<div id="'.$line['name'].'BadgeTab" class="kanbanBadge" style="">'.$nbBadge.'</div>';
       echo  '<table style="float:right;margin:2px;">
          <tr>
            <td>
              <div id="badges" style="position:relative">
              <div id="addComent" onclick="activityStreamKanban(' . $line ['id'] . ', \'' . $typeKanbanC . '\', \''.$type.'\');" style="margin-bottom:2px;margin-right:8px;margin-top:2px;" title=" ' . i18n ( 'commentImputationAdd' ) . ' ">
                ' . formatSmallButton ( 'AddComment' ) . '
                    <div  style="pointer-events: none;position:absolute;bottom:'.((isNewGui())?'-3px':'-1px').';margin-left:'.$margin.'px;width:5px;">
                    '.((count($object->_Note)!=0)?$badge:'').'
                    </div>
              </div>
            </div>
            </td>
            <td>
          	  <div class="roundedButtonSmall"
                style="width:16px;height:16px;cursor:pointer;vertical-align:text-bottom;margin-right:5px;float:left;"
                onclick="showDetail(\'refreshActionAdd' . $typeKanbanC . '\',1,\'' . $typeKanbanC . '\',false,' . $line ['id'] . ');" title="' . i18n('kanbanEditItem',array($line ['id'])) . '>"
                ' . formatSmallButton ( 'Edit',false ) . '
              </div>
            </td>
            <td>
              <div class="roundedButtonSmall"
                style="width:20px;height:16px;cursor:pointer;float:right;vertical-align:text-bottom;"
         		    onclick="gotoElement(\'' . $typeKanbanC . '\',' . htmlEncode ( $line ['id'] ) . ', true);"	title="' .i18n('kanbanGotoItem',array($line ['id'])) . '" style="width:18px;">
         		   ' . formatSmallButton ( 'Goto',true ) . '     
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>';
		if ($mode != "refresh") {
			echo '</div>';
		}
	}
}

?>