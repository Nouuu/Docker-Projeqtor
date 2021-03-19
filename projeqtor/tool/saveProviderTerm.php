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

/**
 * ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/tool/saveProviderTerm.php');

$mode=RequestHandler::getValue('mode');
$objectClass=RequestHandler::getValue('providerTermObjectClass',false,'ProviderOrder');
$idProviderOrder=RequestHandler::getId('providerOrderId');
$idProject=RequestHandler::getId('providerOrderProject');
$isLine=RequestHandler::getValue('providerOrderIsLine');
$idProviderTerm=RequestHandler::getId('idProviderTerm');
$date=RequestHandler::getDatetime('providerTermDate');
$name=RequestHandler::getValue('providerTermName');
$taxPct=RequestHandler::getNumeric('providerTermTax');
Sql::beginTransaction();
$result="";

if ($mode=='edit') {
  if ($isLine==1) {
    $untaxedAmount=RequestHandler::getNumeric('providerTermUntaxedAmount');
    $taxAmount=RequestHandler::getNumeric('providerTermTaxAmount');
    $fullAmount=RequestHandler::getNumeric('providerTermFullAmount');
    $providerTerm=new ProviderTerm($idProviderTerm);
    $providerTerm->idProject=$idProject;
    if ($objectClass=='ProviderOrder')  $providerTerm->idProviderOrder=$idProviderOrder;
    else if ($objectClass=='ProviderBill')  $providerTerm->idProviderBill=$idProviderOrder;
    $providerTerm->date=$date;
    $providerTerm->name=$name;
    $providerTerm->untaxedAmount=$untaxedAmount;
    $providerTerm->taxPct=$taxPct;
    $providerTerm->taxAmount=$taxAmount;
    $providerTerm->fullAmount=$fullAmount;
    $res=$providerTerm->save();
  } else {
    $providerTerm=new ProviderTerm($idProviderTerm);
    $providerTerm->idProject=$idProject;
    $providerTerm->idProviderOrder=$idProviderOrder;
    $providerTerm->date=$date;
    $providerTerm->name=$name;
    $providerTerm->taxPct=$taxPct;
    
    $billLine=new BillLine();
    $critArray=array("refType"=>"ProviderOrder", "refId"=>$idProviderOrder);
    $number=$billLine->countSqlElementsFromCriteria($critArray);
    
    $providerTerm->untaxedAmount=0;
    $providerTerm->taxAmount=0;
    $providerTerm->fullAmount=0;
    
    for ($i=1; $i<=$number; $i++) {
      $untaxedAmount=RequestHandler::getNumeric('providerTermUntaxedAmount'.$i);
      $taxAmount=RequestHandler::getNumeric('providerTermTaxAmount'.$i);
      $fullAmount=RequestHandler::getNumeric('providerTermFullAmount'.$i);
      $discount=RequestHandler::getNumeric('providerTermDiscountAmount'.$i);
      $providerTerm->untaxedAmount+=($untaxedAmount-$discount);
      $providerTerm->taxAmount+=$taxAmount;
      $providerTerm->fullAmount+=$fullAmount;
    }
    $res=$providerTerm->save();
    
    $billLine=new BillLine();
    $critArray=array("refType"=>"ProviderTerm", "refId"=>$providerTerm->id);
    $listBillLineList=$billLine->getSqlElementsFromCriteria($critArray);
    $tab=array();
    $i=1;
    foreach ($listBillLineList as $list) {
      $tab[$i]=$list->id;
      $i++;
    }
    for ($i=1; $i<=$number; $i++) {
      $rate=RequestHandler::getValue('providerTermPercent'.$i);
      $idBillLine=RequestHandler::getId('providerOrderBillLineId'.$i);
      $newBillLine=new BillLine($tab[$i]);
      $newBillLine->refType="ProviderTerm";
      $newBillLine->refId=$providerTerm->id;
      $newBillLine->idBillLine=$idBillLine;
      $newBillLine->rate=$rate;
      $newBillLine->price=RequestHandler::getNumeric('providerTermUntaxedAmount'.$i);
      $newBillLine->save();
    }
  }
} else {
  if ($isLine=='false') {
    $order=new $objectClass($idProviderOrder);
    $totalUntaxedAmount=$order->totalUntaxedAmount;
    $totalFullAmount=$order->totalFullAmount;
    $numberOfTerms=RequestHandler::getValue('providerTermNumberOfTerms');
    if (!$numberOfTerms or !is_numeric($numberOfTerms)) $numberOfTerms=1;
    
    $untaxedAmount=round(RequestHandler::getNumeric('providerTermUntaxedAmount'),2);
    $taxAmount=round(RequestHandler::getNumeric('providerTermTaxAmount'),2);
    $fullAmount=round(RequestHandler::getNumeric('providerTermFullAmount'),2);
    
    $lastDayOfMonth=date('t',strtotime(substr($date,0,7).'-01'));
    $dayOfDate=substr($date,-2);
    $isLastDay=($lastDayOfMonth==$dayOfDate)?true:false;
    $day=($isLastDay)?'last':$dayOfDate;
    
    for ($nb=1;$nb<=$numberOfTerms;$nb++) {
      $providerTerm=new ProviderTerm();
      $providerTerm->idProject=$idProject;
      if ($objectClass=='ProviderBill') {
        $providerTerm->idProviderBill=$idProviderOrder;
      } else {
        $providerTerm->idProviderOrder=$idProviderOrder;
      }
      $providerTerm->date=$date;  
      $providerTerm->name=$name.' '.$date;
      if ($nb==$numberOfTerms and $numberOfTerms>1) {
        $untaxedAmount=round($totalUntaxedAmount,2);// avoid roundings
        //To get Full amount correct
        $fullAmount=$totalFullAmount; 
        $taxAmount=$fullAmount-$untaxedAmount; 
        //To get untaxed and tax amount correct
        //$taxAmount=round($untaxedAmount*$taxPct/100,2); 
        //$fullAmount=$untaxedAmount+$taxAmount;
      }
      $providerTerm->untaxedAmount=$untaxedAmount;
      $providerTerm->taxPct=$taxPct;
      $providerTerm->taxAmount=$taxAmount;
      $providerTerm->fullAmount=$fullAmount;
      if (property_exists($order, 'idResource')) $providerTerm->idResource=$order->idResource;
      $res=$providerTerm->save();
      $totalUntaxedAmount-=$untaxedAmount;
      $totalFullAmount-=$fullAmount;
      $date=sameDayOfNextMonths($date, $day);
    }
  } else {
    $order=new $objectClass($idProviderOrder);
    $providerTerm=new ProviderTerm();
    $providerTerm->idProject=$idProject;
    $providerTerm->idProviderOrder=$idProviderOrder;
    $providerTerm->date=$date;
    $providerTerm->name=$name.' '.$date;
    $providerTerm->taxPct=$taxPct;
    $providerTerm->untaxedAmount=0;
    $providerTerm->taxAmount=0;
    $providerTerm->fullAmount=0;
    $billLine=new BillLine();
    $critArray=array("refType"=>"ProviderOrder", "refId"=>$idProviderOrder);
    $number=$billLine->countSqlElementsFromCriteria($critArray);
    for ($i=1; $i<=$number; $i++) {
      $untaxedAmount=RequestHandler::getNumeric('providerTermUntaxedAmount'.$i,false,0);
      $taxAmount=RequestHandler::getNumeric('providerTermTaxAmount'.$i,false,0);
      $fullAmount=RequestHandler::getNumeric('providerTermFullAmount'.$i,false,0);
      $discount=RequestHandler::getNumeric('providerTermDiscountAmount'.$i,false,0);
      $providerTerm->untaxedAmount+=($untaxedAmount-$discount);
      $providerTerm->taxAmount+=$taxAmount;
      if (property_exists($order, 'idResource')) $providerTerm->idResource=$order->idResource;
      $providerTerm->fullAmount+=$fullAmount;
    }
    $res=$providerTerm->save();
    for ($i=1; $i<=$number; $i++) {
      $rate=RequestHandler::getValue('providerTermPercent'.$i);
      $idBillLine=RequestHandler::getId('providerOrderBillLineId'.$i);
      $newBillLine=new BillLine();
      $newBillLine->refType="ProviderTerm";
      $newBillLine->refId=$providerTerm->id;
      $newBillLine->idBillLine=$idBillLine;
      $newBillLine->rate=$rate;
      $newBillLine->price=RequestHandler::getNumeric('providerTermUntaxedAmount'.$i);
      $newBillLine->save();
    }
  }
}

if (!$result) {
  $result=$res;
}
// Message of correct saving
displayLastOperationStatus($result);

?>