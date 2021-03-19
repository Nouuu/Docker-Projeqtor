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
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

require_once "../tool/projeqtor.php";

// Get the bill line info
$lineId=null;
if (array_key_exists('billLineId',$_REQUEST)) {
  $lineId=$_REQUEST['billLineId']; // validated to be numeric value in SqlElement base constructor.
}

if (! array_key_exists('billLineRefType',$_REQUEST)) {
  throwError('billLineRefType parameter not found in REQUEST');
}
$refType=$_REQUEST['billLineRefType'];
Security::checkValidClass($refType);

if (! array_key_exists('billLineRefId',$_REQUEST)) {
  throwError('billLineRefId parameter not found in REQUEST');
}
$refId=$_REQUEST['billLineRefId'];
Security::checkValidId($refId);

if (! array_key_exists('billLineLine',$_REQUEST)) {
	throwError('billLineLine parameter not found in REQUEST');
}
$lineNum=$_REQUEST['billLineLine'];
Security::checkValidNumeric($lineNum);

$quantity=null;
if (array_key_exists('billLineQuantity',$_REQUEST)) {
  $quantity=$_REQUEST['billLineQuantity'];
  Security::checkValidNumeric($quantity);
}

$numberDays=null;
if (array_key_exists('billLineNumberDays',$_REQUEST)) {
  $numberDays=$_REQUEST['billLineNumberDays'];
  Security::checkValidNumeric($numberDays);
}

$idTerm="";
if (array_key_exists('billLineIdTerm',$_REQUEST)) {
   $idTerm=$_REQUEST['billLineIdTerm'];
   Security::checkValidId($idTerm);
}

$idResource="";
if (array_key_exists('billLineIdResource',$_REQUEST)) {
   $idResource=$_REQUEST['billLineIdResource'];
   Security::checkValidId($idResource);
}

$idActivityPrice="";
if (array_key_exists('billLineIdActivityPrice',$_REQUEST)) {
   $idActivityPrice=$_REQUEST['billLineIdActivityPrice'];
   Security::checkValidId($idActivityPrice);
}

$startDate="";
if (array_key_exists('billLineStartDate',$_REQUEST)) {
  $startDate=$_REQUEST['billLineStartDate'];
  Security::checkValidDateTime($startDate);
}


$endDate="";
if (array_key_exists('billLineEndDate',$_REQUEST)) {
  $endDate=$_REQUEST['billLineEndDate'];
  Security::checkValidDateTime($startDate);
}

$description=null;
if (array_key_exists('billLineDescription',$_REQUEST)) {
  $description=$_REQUEST['billLineDescription'];
}

$detail=null;
if (array_key_exists('billLineDetail',$_REQUEST)) {
  $detail=$_REQUEST['billLineDetail'];
}

$price=null;
if (array_key_exists('billLinePrice',$_REQUEST)) {
  $price=$_REQUEST['billLinePrice'];
  Security::checkValidNumeric($price);
}

$unit=null;
if (array_key_exists('billLineUnit',$_REQUEST)) {
  $unit=$_REQUEST['billLineUnit'];
}
$extra=0;
if (array_key_exists('billLineExtra',$_REQUEST)) {
  $extra=1;
}

$lineId=trim($lineId);
if ($lineId=='') {
  $lineId=null;
} 
$billingType='M';
if (array_key_exists('billLineBillingType',$_REQUEST)) {
  $billingType=$_REQUEST['billLineBillingType'];
  Security::checkValidAlphanumeric($billingType);
}
//gautier
$catalogSpecification = "";
$boolCatalog = false;
if (array_key_exists('billLineIdCatalog',$_REQUEST)) {
  $boolCatalog = true;
  $catalog=new Catalog($_REQUEST['billLineIdCatalog']);
  $catalogSpecification = $catalog->specification;
}//end 

Sql::beginTransaction();
$line=new BillLine($lineId);
$line->refType=$refType;
$line->refId=$refId;
$line->line=$lineNum;
$line->quantity=$quantity;
$line->numberDays=$numberDays;
$line->idTerm=$idTerm;
$line->idResource=$idResource;
$line->idActivityPrice=$idActivityPrice;
$line->startDate=$startDate;
$line->endDate=$endDate;
$line->description=$description;
$line->detail=$detail;
$line->price=$price;
$line->idMeasureUnit=$unit;
$line->extra=$extra;
$line->billingType=$billingType;
//gautier #2516
if($boolCatalog){
  $line->idCatalog=$catalog->id;
  if(!$lineId and $line->refType=="Bill"){
    $bill=new Bill($line->refId);
    if(!$bill->description or strpos($bill->description,$catalogSpecification )=== FALSE){ 
      $bill->description .= $catalogSpecification;
    }
    $bill->save();
  }
  if(!$lineId and $line->refType=="Quotation"){
    $quot=new Quotation($line->refId);
    if(!$quot->comment or strpos($quot->comment,$catalogSpecification )=== FALSE){ 
      $quot->comment .= $catalogSpecification;
    }
    $quot->save();
  }
  if(!$lineId and $line->refType=="Command"){
    $order=new Command($line->refId);
    if(!$order->comment or strpos($order->comment,$catalogSpecification )=== FALSE){ 
      $order->comment .= $catalogSpecification;
    }
    $order->save();
  }
}//end
$result=$line->save();

// Message of correct saving
displayLastOperationStatus($result);
?>