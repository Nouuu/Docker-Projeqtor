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
$paramClosedItems=RequestHandler::getBoolean('showClosedItems');;
$paramShowExpenses=RequestHandler::getBoolean('showExpense');;

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
if ($paramClosedItems!="") {
  $headerParameters.= i18n("colShowClosedItems") . ' : ' . i18n('displayYes') . '<br/>';
}  
if ($paramShowExpenses!="") {
  $headerParameters.= i18n("colShowExpense") . ' : ' . i18n('displayYes') . '<br/>';
}

include "header.php";

$arrayClassInitial=array(
    "Tender"=>"O",
    "ProviderOrder"=>"C",
    "ProviderBill"=>"B",
    "ProviderTerm"=>"T",
    "ProviderPayment"=>"P",
    "ProjectExpense"=>"X"
);

$queryWhereTender=getAccesRestrictionClause('Tender',false);
$queryWhereOrder=getAccesRestrictionClause('ProviderOrder',false);
$queryWhereBill=getAccesRestrictionClause('ProviderBill',false);
$queryWhereTerm=getAccesRestrictionClause('ProviderTerm',false);
$queryWhereExpense=getAccesRestrictionClause('ProjectExpense',false);

// $clauseOrderByTender=" expectedTenderDateTime asc";
// $clauseOrderByOrder=" deliveryExpectedDate asc";
// $clauseOrderByBill=" date asc";
// $clauseOrderByTerm=" date asc";
// $clauseOrderByExpense=" coalesce(expenseRealDate,expensePlannedDate) asc";

$clauseOrderByTender=" id asc";
$clauseOrderByOrder=" id asc";
$clauseOrderByBill=" id asc";
$clauseOrderByTerm=" id asc";
$clauseOrderByExpense=" id asc";

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
if(!$paramClosedItems){
  $queryWherePlus.=" and idle=0";
}

echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('Tender');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';

$obj=new Tender();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereTender . $queryWherePlus, $clauseOrderByTender);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colName') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProvider') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdTenderStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colRequestDate') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colExpectedTenderDate') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colReceptionDateTime') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colDiscountRate') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colTaxPct') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalFullAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '</tr>';
foreach ($lst as $tender) {
  echo '<tr>';
  $done=($tender->done)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . $arrayClassInitial['Tender'] . htmlEncode($tender->id) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('TenderType', $tender->idTenderType) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . htmlEncode($tender->name).'</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('Provider',$tender->idProvider) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%;max-width:50px"><div>' . formatColor('TenderStatus',$tender->idTenderStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($tender->requestDateTime,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($tender->expectedTenderDateTime,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($tender->receptionDateTime,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:7%;max-width:50px"><div>' . formatColor('Status',$tender->idStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('Resource',$tender->idResource)  . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($tender->untaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($tender->discountRate) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($tender->totalUntaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($tender->taxPct) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($tender->totalFullAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . listLinks($tender) . '</td>';
  echo '</tr>';
}
unset($tender);
echo '</table><br/><br/>';
echo '</page><page>'; 


echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('ProviderOrder');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';
$obj=new ProviderOrder();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereOrder . $queryWherePlus, $clauseOrderByOrder);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colName') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProvider') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProjectExpense') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDeliveryExpectedDate') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDeliveryDoneDate') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDeliveryValidationDate') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colDiscountRate') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colTaxPct') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalFullAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '</tr>';
foreach ($lst as $order) {
  echo '<tr>';
  $done=($order->done)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . $arrayClassInitial['ProviderOrder'] . htmlEncode($order->id) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('ProviderOrderType', $order->idProviderOrderType) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . htmlEncode($order->name).'</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('Provider',$order->idProvider) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('ProjectExpense',$order->idProjectExpense) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($order->deliveryExpectedDate,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($order->deliveryDoneDate,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($order->deliveryValidationDate,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:7%;max-width:50px"><div>' . formatColor('Status',$order->idStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('Resource',$order->idResource)  . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($order->untaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($order->discountRate) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($order->totalUntaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($order->taxPct) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($order->totalFullAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . listLinks($order) . '</td>';
  echo '</tr>';
}
unset($order);
echo '</table><br/><br/>';
echo '</page><page>';


echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('ProviderBill');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';
$obj=new ProviderBill();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereBill . $queryWherePlus, $clauseOrderByBill);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colType') . '</td>';
echo '<td class="largeReportHeader" style="width:15%">' . i18n('colName') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProvider') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProjectExpense') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDate') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colPaymentDueDate') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colPaymentDate') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colDiscountRate') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colTaxPct') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalFullAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '</tr>';
$listBillsClause='(0';
foreach ($lst as $bill) {
  $listBillsClause.=','.$bill->id;
  echo '<tr>';
  $done=($bill->done)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . $arrayClassInitial['ProviderBill'] . htmlEncode($bill->id) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('ProviderBillType', $bill->idProviderBillType) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:15%">' . htmlEncode($bill->name).'</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('Provider',$bill->idProvider) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('ProjectExpense',$bill->idProjectExpense) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($bill->date,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($bill->paymentDueDate,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($bill->paymentDate,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:7%;max-width:50px"><div>' . formatColor('Status',$bill->idStatus) . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('Resource',$bill->idResource)  . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($bill->untaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($bill->discountRate) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($bill->totalUntaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($bill->taxPct) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($bill->totalFullAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . listLinks($bill) . '</td>';
  echo '</tr>';
}
$listBillsClause.=')';
unset($bill);
echo '</table><br/><br/>';
echo '</page><page>';


echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('ProviderTerm');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';
$obj=new ProviderTerm();
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereTerm . $queryWherePlus, $clauseOrderByTerm);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:22%">' . i18n('colName') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProvider') . '</td>';
echo '<td class="largeReportHeader" style="width:9%">' . i18n('colIdProviderOrder') . '</td>';
echo '<td class="largeReportHeader" style="width:9%">' . i18n('colIdProviderBill') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDate') . '</td>';
echo '<td class="largeReportHeader" style="width:7%">' . i18n('colIdStatus') . '</td>';
echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResponsible') . '</td>';
echo '<td class="largeReportHeader" style="width:9%">' . i18n('colPayment') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalUntaxedAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colTaxPct') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colTotalFullAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '</tr>';
$listTermsClause='(0';
foreach ($lst as $term) {
  $listTermsClause.=','.$term->id;
  echo '<tr>';
  $done=($term->isPaid)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . $arrayClassInitial['ProviderTerm'] . htmlEncode($term->id) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:22%">' . htmlEncode($term->name).'</td>';
  $provider="";
  if ($provider=="" and $term->idProviderBill) {
    $obj=new ProviderBill($term->idProviderBill);
    $provider=SqlList::getNameFromId('Provider',$obj->idProvider);
  }
  if ($provider=="" and $term->idProviderOrder) {
    $obj=new ProviderOrder($term->idProviderOrder);
    $provider=SqlList::getNameFromId('Provider',$obj->idProvider);
  }
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . $provider . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:9%">' . SqlList::getNameFromId('ProviderOrder',$term->idProviderOrder) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:9%">' . SqlList::getNameFromId('ProviderBill',$term->idProviderBill) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($term->date,true) . '</td>';
  if ($term->isPaid) {
    $status=formatColorFromNameAndColor(i18n('colIsPaid'), "#ccffcc");
  } else if ($term->isBilled) {
    $status=formatColorFromNameAndColor(i18n('colIsBilled'), "#ffffcc");
  } else {
    $status=formatColorFromNameAndColor(i18n('billingTypeN'), "#ffccff");
  }
  echo '<td class="largeReportData' . $done . '" style="width:7%;max-width:50px"><div>' . $status . '</div></td>';
  echo '<td class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('Resource',$term->idResource)  . '</td>';
  $pay=new ProviderPayment();
  $list=$pay->getSqlElementsFromCriteria(array('idProviderTerm'=>$term->id));
  $payment='';
  foreach ($list as $pay) {
    $payment.=($payment=='')?'':'<br/>';
    $payment.=htmlDisplayCurrency($pay->paymentAmount) .' | '. htmlFormatDate($pay->paymentDate,true);
  }
  echo '<td class="largeReportData' . $done . '" style="width:9%;text-align:right;">' . $payment . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($term->untaxedAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($term->taxPct) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($term->fullAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . listLinks($term) . '</td>';
  echo '</tr>';
}
$listTermsClause.=')';
unset($term);
echo '</table><br/><br/>';
echo '</page><page>';


echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
echo i18n('ProviderPayment');
echo '</td></tr>';
echo '<tr><td>&nbsp;</td></tr>';
echo '</table>';
$obj=new ProviderPayment();
$queryWherePayment=' idProviderBill in '.$listBillsClause.' or idProviderTerm in '.$listTermsClause;
$clauseOrderByPayment='paymentDate asc';
$lst=$obj->getSqlElementsFromCriteria(null, false, $queryWherePayment, $clauseOrderByPayment);
echo '<table  width="95%" align="center">';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
echo '<td class="largeReportHeader" style="width:22%">' . i18n('colName') . '</td>';
echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProvider') . '</td>';
echo '<td class="largeReportHeader" style="width:9%">' . i18n('colIdProviderTerm') . '</td>';
echo '<td class="largeReportHeader" style="width:9%">' . i18n('colIdProviderBill') . '</td>';
echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDate') . '</td>';
echo '<td class="largeReportHeader" style="width:11%">' . i18n('colIssuer') . '</td>';
echo '<td class="largeReportHeader" style="width:12%">' . i18n('colIdPaymentType') . '</td>';
echo '<td class="largeReportHeader" style="width:12%">' . i18n('colIdPaymentMode') . '</td>';
echo '<td class="largeReportHeader" style="width:6%">' . i18n('colPaymentAmount') . '</td>';
echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
echo '</tr>';
foreach ($lst as $payment) {
  echo '<tr>';
  $done=($payment->idle)?'Done':'';
  echo '<td class="largeReportData' . $done . '" style="width:3%">' . $arrayClassInitial['ProviderPayment'] . htmlEncode($payment->id) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:22%">' . htmlEncode($payment->name).'</td>';
  echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('Provider',$payment->idProvider) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:9%">' . SqlList::getNameFromId('ProviderTerm',$payment->idProviderTerm) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:9%">' . SqlList::getNameFromId('ProviderBill',$payment->idProviderBill) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($payment->paymentDate,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:11%">' . SqlList::getNameFromId('Affectable',$payment->idUser)  . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:12%">' . SqlList::getNameFromId('ProviderPaymentType',$payment->idProviderPaymentType)  . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:12%">' . SqlList::getNameFromId('PaymentMode',$payment->idPaymentMode)  . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($payment->paymentAmount,true) . '</td>';
  echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . listLinks($payment) . '</td>';
  echo '</tr>';
}
unset($payment);
echo '</table><br/><br/>';

if ($paramShowExpenses) {
  echo '</page><page>';
  
  echo '<table  width="95%" align="center"><tr><td style="width: 100%" class="section">';
  echo i18n('ProjectExpense');
  echo '</td></tr>';
  echo '<tr><td>&nbsp;</td></tr>';
  echo '</table>';
  $obj=new ProjectExpense();
  $lst=$obj->getSqlElementsFromCriteria(null, false, $queryWhereExpense . $queryWherePlus, $clauseOrderByExpense);
  echo '<table  width="95%" align="center">';
  echo '<tr>';
  echo '<td class="largeReportHeader" style="width:3%">' . i18n('colId') . '</td>';
  echo '<td class="largeReportHeader" style="width:7%">' . i18n('colType') . '</td>';
  echo '<td class="largeReportHeader" style="width:15%">' . i18n('colName') . '</td>';
  echo '<td class="largeReportHeader" style="width:8%">' . i18n('colIdProvider') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%">' . i18n('colIdBudgetItem') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%">' . i18n('colOrderDate') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%">' . i18n('colDeliveryExpectedDate') . '</td>';
  echo '<td class="largeReportHeader" style="width:5%">' . i18n('colReceptionDate') . '</td>';
  echo '<td class="largeReportHeader" style="width:7%">' . i18n('colIdStatus') . '</td>';
  echo '<td class="largeReportHeader" style="width:10%">' . i18n('colResponsible') . '</td>';
  echo '<td class="largeReportHeader" style="width:6%">' . i18n('colPlannedAmount2') . '</td>';
  echo '<td class="largeReportHeader" style="width:6%">' . i18n('colRealAmount') . '</td>';
  echo '<td class="largeReportHeader" style="width:3%">' . i18n('colTaxPct') . '</td>';
  echo '<td class="largeReportHeader" style="width:6%">' . i18n('colPlannedFullAmount') . '</td>';
  echo '<td class="largeReportHeader" style="width:6%">' . i18n('colRealFullAmount') . '</td>';
  echo '<td class="largeReportHeader" style="width:3%">' . i18n('colLink') . '</td>';
  echo '</tr>';
  foreach ($lst as $expense) {
    echo '<tr>';
    $done=($expense->paymentDone)?'Done':'';
    echo '<td class="largeReportData' . $done . '" style="width:3%">' . $arrayClassInitial['ProjectExpense'] . htmlEncode($expense->id) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:7%">' . SqlList::getNameFromId('ProjectExpenseType', $expense->idProjectExpenseType) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:15%">' . htmlEncode($expense->name).'</td>';
    echo '<td class="largeReportData' . $done . '" style="width:8%">' . SqlList::getNameFromId('Provider',$expense->idProvider) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:5%">' . SqlList::getNameFromId('Budget',$expense->idBudgetItem) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($expense->sendDate,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($expense->deliveryDate,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:5%;text-align:center;">' . htmlFormatDate($expense->receptionDate,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:7%;max-width:50px"><div>' . formatColor('Status',$expense->idStatus) . '</div></td>';
    echo '<td class="largeReportData' . $done . '" style="width:10%">' . SqlList::getNameFromId('Resource',$expense->idResource)  . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($expense->plannedAmount,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($expense->realAmount,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . htmlDisplayPct($expense->taxPct) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($expense->plannedFullAmount,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:6%;text-align:right;">' . htmlDisplayCurrency($expense->realFullAmount,true) . '</td>';
    echo '<td class="largeReportData' . $done . '" style="width:3%;text-align:center;">' . listLinks($expense) . '</td>';
    echo '</tr>';
  }
  unset($expense);
  echo '</table><br/><br/>';
}

function listLinks($objIn) {
  global $arrayClassInitial;
  $lstTemp=Link::getLinksAsListForObject($objIn);
  $lst=array();
  foreach($lstTemp as $lnk) {
    addExtra($lnk['type'],$lnk['id'],$lst);
  }
  addLinksForObject($objIn,$lst);
  ksort($lst);
  $res='<table style="width:100%; margin:0 ; spacing:0 ; padding: 0">';
  foreach ($lst as $link) {
    if (! isset($arrayClassInitial[$link['type']])) continue;
    $obj=new $link['type']($link['id']);
    $style=(isset($obj->done) and $obj->done)?'style="text-decoration: line-through;"':'';
    $res.='<tr><td '. $style . '>' . $arrayClassInitial[$link['type']] . $link['id'] . '</td></tr>';
      
  }
  $res.='</table>';
  return $res;
}

function addLinksForObject($objIn,&$lst) {
  global $paramShowExpenses;
  if (get_class($objIn)=='Tender') {
    if ($paramShowExpenses and $objIn->idProjectExpense) addExtra('ProjectExpense',$objIn->idProjectExpense,$lst);
  } else if (get_class($objIn)=='ProviderOrder') {
    addExtraList('ProviderTerm',array('idProviderOrder'=>$objIn->id),$lst);
    if ($paramShowExpenses and $objIn->idProjectExpense) addExtra('ProjectExpense',$objIn->idProjectExpense,$lst);
  } else if (get_class($objIn)=='ProviderBill') {
    addExtraList('ProviderPayment',array('idProviderBill'=>$objIn->id),$lst);
    addExtraList('ProviderTerm',array('idProviderBill'=>$objIn->id),$lst);
    if ($paramShowExpenses and $objIn->idProjectExpense) addExtra('ProjectExpense',$objIn->idProjectExpense,$lst);
  } else if (get_class($objIn)=='ProviderTerm') {
    addExtraList('ProviderPayment',array('idProviderTerm'=>$objIn->id),$lst);
    if ($objIn->idProviderOrder) addExtra('ProviderOrder',$objIn->idProviderOrder,$lst);
    if ($objIn->idProviderBill) addExtra('ProviderBill',$objIn->idProviderBill,$lst);
    if ($paramShowExpenses and $objIn->idProjectExpense) addExtra('ProjectExpense',$objIn->idProjectExpense,$lst);
  } else if (get_class($objIn)=='ProviderPayment') {
    if ($objIn->idProviderTerm) addExtra('ProviderTerm',$objIn->idProviderTerm,$lst);
    if ($objIn->idProviderBill) addExtra('ProviderBill',$objIn->idProviderBill,$lst);
  } else if (get_class($objIn)=='ProjectExpense' and $paramShowExpenses) {
    addExtraList('Tender',array('idProjectExpense'=>$objIn->id),$lst);
    addExtraList('ProviderOrder',array('idProjectExpense'=>$objIn->id),$lst);
    addExtraList('ProviderBill',array('idProjectExpense'=>$objIn->id),$lst);
    addExtraList('ProviderTerm',array('idProjectExpense'=>$objIn->id),$lst);
  }
}
function addExtraList($class,$crit,&$lst) {
  $extra=SqlList::getListWithCrit($class,$crit);
  foreach ($extra as $id=>$name) {
    addExtra($class,$id,$lst);
  } 
}
function addExtra($class,$id,&$lst) {
  $lst[$class.'#'.$id]=array('type'=>$class,'id'=>$id);
}
?>
