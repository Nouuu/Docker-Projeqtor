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

// Header
include_once '../tool/projeqtor.php';
include_once('../tool/formatter.php');

$paramProject=trim(RequestHandler::getId('idProject',false));
$paramProjectType=trim(RequestHandler::getId('idProjectType',false));
$paramOrganization=trim(RequestHandler::getId('idOrganization',false));

// Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . htmlEncode(SqlList::getNameFromId('Project', $paramProject)) . '<br/>';
}
if ($paramProjectType!="") {
  $headerParameters.= i18n("colIdProjectType") . ' : ' . htmlEncode(SqlList::getNameFromId('ProjectType', $paramProjectType)) . '<br/>';
}
if ($paramOrganization!="") {
  $headerParameters.= i18n("colIdOrganization") . ' : ' . htmlEncode(SqlList::getNameFromId('Organization', $paramOrganization)) . '<br/>';
}

include "header.php";

//REQUEST
$queryWhereTender=getAccesRestrictionClause('Tender',false);
$queryWhereOrder=getAccesRestrictionClause('ProviderOrder',false);
$queryWhereBill=getAccesRestrictionClause('ProviderBill',false);
$queryWhereTerm=getAccesRestrictionClause('ProviderTerm',false);
$queryWherePayment=getAccesRestrictionClause('ProviderPayment',false);

$queryWherePlus="";
if ($paramProject!="") {
  $queryWherePlus.=" and idProject in " . getVisibleProjectsList(true, $paramProject);
}
if ($paramProjectType!="") {
  $proj=new Project();
  $projTable=$proj->getDatabaseTableName();
  $queryWherePlus.=" and idProject in (select id from $projTable where idProjectType=$paramProjectType)";
}
if ($paramOrganization!="") {
  $proj=new Project();
  $projTable=$proj->getDatabaseTableName();
  $queryWherePlus.=" and idProject in (select id from $projTable where idOrganization=$paramOrganization)";
}

echo '<table  width="95%" align="center">';
//title    
echo'<tr>';
echo' <td style="width: 25%" class=""> </td>';
echo' <td class="assignHeader"  style="width:30%">'.i18n('colUntaxedAmount').'</td>';
echo' <td class="assignHeader"  style="width:30%">'.i18n('colFullAmount').'</td>';
echo' <td class="assignHeader"  style="width:15%">'.i18n('colWorkElementCount').'</td>';
echo'</tr>';

//TENDER
$clauseStatus=transformListIntoInClause(SqlList::getListWithCrit('tenderStatus', array('isSelected'=>'1')));
$providerTender = new Tender();
$listTender = $providerTender->getSqlElementsFromCriteria(null, false, $queryWhereTender . $queryWherePlus .' and idTenderStatus in '.$clauseStatus);
$untaxedAmount = 0;
$fullAmount = 0;
foreach ($listTender as $tender ){
  $untaxedAmount += $tender->totalUntaxedAmount;
  $fullAmount += $tender->totalFullAmount;
}
echo'<tr>';
echo' <td class="assignHeader" style="width:25%">'.i18n('menuTender').'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($untaxedAmount).'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($fullAmount).'</td>';
echo' <td class="assignData" align="center" style="width:15%">'.count($listTender).'</td>';
echo'</tr>';
//ORDER
$providerOrder = new ProviderOrder();
$listProviderOrder = $providerOrder->getSqlElementsFromCriteria(null, false, $queryWhereOrder . $queryWherePlus);
$untaxedAmount = 0;
$fullAmount = 0;
$untaxedAmountTerm = 0;
$fullAmountTerm =0;
$nbTerm = 0;

foreach ($listProviderOrder as $order ){
  $untaxedAmount += $order->totalUntaxedAmount;
  $fullAmount += $order->totalFullAmount;
  //TERM idProviderBill is null
  $providerTerm = new ProviderTerm();
  $listProviderTerm = $providerTerm->getSqlElementsFromCriteria(null, false, $queryWhereTerm . $queryWherePlus .' and idProviderOrder='.$order->id.' and idProviderBill is null');
  foreach ($listProviderTerm as $term){
    $nbTerm++;
    $untaxedAmountTerm += $term->untaxedAmount;
    $fullAmountTerm += $term->fullAmount;
  }
}
echo'<tr>';
echo' <td class="assignHeader" style="width:25%">'.i18n('menuProviderOrder').'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($untaxedAmount).'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($fullAmount).'</td>';
echo' <td class="assignData" align="center" style="width:15%">'.count($listProviderOrder).'</td>';
echo'</tr>';
//BILL
$providerBill = new ProviderBill();
$listProviderBill = $providerBill->getSqlElementsFromCriteria(null, false, $queryWhereBill . $queryWherePlus);
$untaxedAmount = 0;
$fullAmount = 0;
$fullAmountPayment =0;
$nbPayment = 0;
foreach ($listProviderBill as $bill ){
  $untaxedAmount += $bill->totalUntaxedAmount;
  $fullAmount += $bill->totalFullAmount;
  //PAYMENT
  $payment = new ProviderPayment();
  $listProviderPayment = $payment->getSqlElementsFromCriteria(null, false, $queryWherePayment. ' and idProviderBill='.$bill->id);
  foreach ($listProviderPayment as $provPayment){
    $nbPayment++;
    $fullAmountPayment += $provPayment->paymentAmount;
  }
  //TERM BILL
  $providerTerm = new ProviderTerm();
  $listProviderTerm = $providerTerm->getSqlElementsFromCriteria(null, false, $queryWhereTerm . $queryWherePlus .' and idProviderBill='.$bill->id);
  foreach ($listProviderTerm as $term){
    $nbTerm++;
    $untaxedAmountTerm += $term->untaxedAmount;
    $fullAmountTerm += $term->fullAmount;
  }
}
echo'<tr>';
echo' <td class="assignHeader" style="width:25%">'.i18n('menuProviderBill').'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($untaxedAmount).'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($fullAmount).'</td>';
echo' <td class="assignData" align="center" style="width:15%">'.count($listProviderBill).'</td>';
echo'</tr>';
//term
echo'<tr>';
echo' <td class="assignHeader" style="width:25%">'.i18n('menuProviderTerm').'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($untaxedAmountTerm).'</td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($fullAmountTerm).'</td>';
echo' <td class="assignData" align="center" style="width:15%">'.$nbTerm.'</td>';
echo'</tr>';
//payment
echo'<tr>';
echo' <td class="assignHeader" style="width:25%">'.i18n('menuProviderPayment').'</td>';
echo' <td class="assignHeader" align="right" style="width:30%"></td>';
echo' <td class="assignData" align="right" style="width:30%">'.htmlDisplayCurrency($fullAmountPayment).'</td>';
echo' <td class="assignData" align="center" style="width:15%">'.$nbPayment.'</td>';
echo'</tr>';
echo '</table>';

?>
