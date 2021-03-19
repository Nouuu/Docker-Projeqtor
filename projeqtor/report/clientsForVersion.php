<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Julien PAPASIAN
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
include_once '../tool/projeqtor.php';
include_once "../tool/jsonFunctions.php";

$print = false;
if (array_key_exists('print', $_REQUEST)) {
    $print = true;
    include_once('../tool/formatter.php');
}
$paramProduct = trim(RequestHandler::getId('idProduct'));
$paramProductVersion = trim(RequestHandler::getId('idProductVersion'));
$paramListTickets = RequestHandler::getBoolean('listTickets');
$paramShowDetail = RequestHandler::getBoolean('showDetail');
$paramStatus = RequestHandler::getValue('idStatus');

$crit='1=1';
// Header
$headerParameters = "";
if ($paramProduct) {
    $headerParameters.= i18n("colIdProduct") . ' : ' . htmlEncode(SqlList::getNameFromId('Product', $paramProduct)) . '<br/>';
    $crit.=' and idProduct='.Sql::fmtId($paramProduct);
}
if ($paramProductVersion) {
    $other=new OtherTargetProductVersion();
    $otherTable=$other->getDatabaseTableName();
    $ticket = new Ticket();
    $ticketTable=$ticket->getDatabaseTableName();
    $headerParameters.= i18n("colIdProductVersion") . ' : ' . htmlEncode(SqlList::getNameFromId('ProductVersion', $paramProductVersion)) . '<br/>';
    $crit.=' and (idTargetProductVersion='.Sql::fmtId($paramProductVersion);
    $crit.=" or exists (select 'x' from $otherTable other where other.refType='Ticket' and other.refId=$ticketTable.id and other.scope='TargetProductVersion' )";
    $crit.=')';
}
if ($paramListTickets) {
  $headerParameters.= i18n("colListTickets"). '<br/>';
}
if ($paramStatus) {
  $headerParameters.= i18n("colIdStatus") . ' : ';
  $cpt=0;
  $in='(0';
  foreach($paramStatus as $status) {
    if ($cpt>0) $headerParameters.=', ';
    $headerParameters.=htmlEncode(SqlList::getNameFromId('Status', $status));
    $in.=','.$status;
    $cpt++;
  }
  $in.=')';
  $crit.=' and idStatus in '.$in;
  $headerParameters.='<br/>';
}
if (! $paramProduct && ! $paramProductVersion) {
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    echo i18n('messageNoData',array(i18n('Product').' / '.i18n('ProductVersion'))); 
    echo '</div>';
    if (!empty($cronnedScript)) goto end; else exit;
}

$arrayFilter=jsonGetFilterArray('Report_Ticket', false);
if (count($arrayFilter)>0) {
  $obj=new Ticket();
  $querySelect="";
  $queryFrom="";
  $queryOrderBy="";
  $idTab=0;
  jsonBuildWhereCriteria($querySelect,$queryFrom,$crit,$queryOrderBy,$idTab,$arrayFilter,$obj);
}

$outMode=RequestHandler::getValue('outMode',false,'html');

if ($outMode == 'csv') {
    include_once "headerFunctions.php";
} else {
    include "header.php";
}

$ticket = new Ticket();
$other=new OtherClient();
$lstTicket=$ticket->getSqlElementsFromCriteria(null, false, $crit, 'id', true);

if (checkNoData($lstTicket)) if (!empty($cronnedScript)) goto end; else exit;

$arrayClient=array();

foreach($lstTicket as $ticket) {
  if (property_exists('Ticket', 'idClient')) {
    addClient($ticket->idClient,$ticket);
    if (property_exists('Ticket', '_OtherClient')) {
      $lstOther=$other->getSqlElementsFromCriteria(array('refType'=>'Ticket','refId'=>$ticket->id));
      foreach($lstOther as $other) {
        addClient($other->idClient,$ticket);
      }
    }
  } else {
    $client=SqlList::getFieldFromId('Contact', $ticket->idContact, 'idClient');
    addClient($client,$ticket);
  }
}

uasort($arrayClient, function($a,$b) {return strnatcmp($a['name'], $b['name']);} );

if ($outMode == 'csv') {
  exportCsv();
} else {
  printResult();
}

function addClient($idClient,$ticket) {
  global $arrayClient;
  if (!$idClient) return;
  if (!isset($arrayClient[$idClient])) {
    $client=new Client($idClient);
    $arrayClient[$idClient]=array('name'=>$client->name,'city'=>$client->city,'country'=>$client->country, 'tickets'=>array());
  }
  $arrayClient[$idClient]['tickets'][$ticket->id]=array('id'=>$ticket->id,'name'=>$ticket->name,'description'=>$ticket->description,'status'=>$ticket->idStatus);
}

function exportCsv() {
  $nl="\r\n";
  global $arrayClient,$paramListTickets;
  echo chr(239) . chr(187) . chr(191); // Microsoft Excel requirement
  echo i18n('colClientName') . ';';
  echo i18n('colCity') . ';';
  echo i18n('colCountry');
  if ($paramListTickets) {
    echo ';';
    echo i18n('Ticket') . ';';
    echo i18n('colName') . ';';
    echo i18n('colDescription') . ';';
    echo i18n('colIdStatus'); // ! no ; before nl
  }
  echo $nl;
  foreach($arrayClient as $client) {
    if ($paramListTickets) {
      foreach ($client['tickets'] as $ticket) {
        echo formatText($client['name']) . ';';
        echo formatText($client['city']) . ';';
        echo formatText($client['country']) . ';';
        echo $ticket['id'] . ';';
        echo formatText($ticket['name']) . ';';
        echo formatText($ticket['description']) . ';';
        echo formatText(SqlList::getNameFromId('Status',$ticket['status'])); // ! no ; before nl
        echo $nl;
      }
    } else {
      echo formatText($client['name']) . ';';
      echo formatText($client['city']) . ';';
      echo formatText($client['country']); // ! no ; before nl
      echo $nl;
    }
  }
}
function formatText($val) {
  //$val=encodeCSV($val);
  if (isTextFieldHtmlFormatted($val)) {
    $text=new Html2Text($val);
    $val=$text->getText();
  } else {
    $val=br2nl($val);
  }
  $val=str_replace('"','""',$val);
  return '"'.$val.'"';
}
function printResult() {
  global $arrayClient,$paramListTickets,$paramProduct,$paramProductVersion,$paramShowDetail;
  $clientCss='border: 1px solid #A0A0A0;padding:5px 10px;';
  $ticketCss='border: 1px solid #A0A0A0;padding:2px 5px;vertical-align:top;';
  $title="";
  if ($paramProductVersion) {
    $title=i18n('ProductVersion').' #'.$paramProductVersion.' - '.SqlList::getNameFromId('ProductVersion', $paramProductVersion);
  } else {
    $title=i18n('Product').' #'.$paramProduct.' - '.SqlList::getNameFromId('Product', $paramProduct);
  }
  echo "<table style='width:90%; margin-left: auto; margin-right: auto;'>";
  echo " <tr><td colspan='3' class='reportTableHeader' style='font-size: 150%; font-weight: bold;'>".$title."</td></tr>";
  echo " <tr><td colspan='3'>&nbsp;</td></tr>";
  echo "  <tr class='reportTableHeader'>";
  echo "    <td style='width:70%' class='reportTableHeader'>".i18n('colClientName')."</td>";
  echo "    <td style='width:15%' class='reportTableHeader'>".i18n('colCity')."</td>";
  echo "    <td style='width:15%' class='reportTableHeader'>".i18n('colCountry')."</td>";
  echo "  </tr>";
  foreach($arrayClient as $client) {
    echo "  <tr class='reportData'>";
    echo "    <td style='width:70%;text-align:left;$clientCss' class='reportData'>".$client['name']."</td>";
    echo "    <td style='width:15%;text-align:center;$clientCss' class='reportData'>".$client['city']."</td>";
    echo "    <td style='width:15%;text-align:center;$clientCss' class='reportData'>".$client['country']."</td>";
    echo "  </tr>";
    if ($paramListTickets) {
      echo "  <tr class='reportData'>";
      echo "    <td colspan='3' style='width:100%;' class='reportData'>";
      echo "      <table style='width:100%'>";
      echo "        <tr class=''>";
      echo "          <td style='width:10%;text-align:right' >".i18n('menuTicket')."&nbsp;&nbsp;&nbsp;</td>";
      echo "          <td style='width:5%;text-align:center' class='reportTableLineHeader'>".i18n('colId')."</td>";
      echo "          <td style='width:".(($paramShowDetail)?'25':'70')."%;text-align:center' class='reportTableLineHeader'>".i18n('colName')."</td>";
      if ($paramShowDetail) echo "          <td style='width:45%;text-align:center' class='reportTableLineHeader'>".i18n('colDescription')."</td>";
      echo "          <td style='width:15%;text-align:center' class='reportTableLineHeader'>".i18n('colIdStatus')."</td>";
      echo "        </tr>";
      foreach ($client['tickets'] as $ticket) {
        echo "        <tr class=''>";
        echo "          <td style='width:10%;text-align:right' >&nbsp;</td>";
        echo "          <td style='width:5%;text-align:center;$ticketCss' class=''>".$ticket['id']."</td>";
        echo "          <td style='width:".(($paramShowDetail)?'25':'70')."%;text-align:left;$ticketCss' class=''>".$ticket['name']."</td>";
        if ($paramShowDetail) echo "          <td style='width:45%;text-align:left;font-size:80%;$ticketCss' class=''>".$ticket['description']."</td>";
        echo "          <td style='width:15%;text-align:center;$ticketCss' class=''>".SqlList::getNameFromId('Status',$ticket['status'])."</td>";
        echo "        </tr>";
      }
      echo "      </table>";
      echo "    </td>";
      echo "  </tr>";
    }
  }
  echo "</table>";
}

end:
