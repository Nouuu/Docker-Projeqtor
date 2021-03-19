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

include_once "../tool/projeqtor.php";
header ('Content-Type: text/html; charset=UTF-8');
scriptLog('   ->/tool/importHelp.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtor.css" />
  <link rel="stylesheet" type="text/css" href="../view/css/projeqtorFlat.css" />
</head>

<body class="white" onLoad="window.top.hideWait();" style="overflow: auto; ">
<?php 
$class='';

if (! array_key_exists('elementType',$_REQUEST)) {
  throwError('elementType parameter not found in REQUEST');
}
$elementType = $_REQUEST['elementType'];
Security::checkValidId($elementType); // elementType is id in Importable table

$class=SqlList::getNameFromId('Importable',$elementType,false);
Security::checkValidClass($class); 

// Note: $fileType is not used - commenting out.
/*
if (! array_key_exists('fileType',$_REQUEST)) {
  throwError('fileType parameter not found in REQUEST');
}
$fileType=$_REQUEST['fileType'];
*/

//echo $class . '<br/>';
$obj=new $class();
$fields=getFields($obj);

echo '<TABLE WIDTH="100%" style="border: 1px solid black">';
echo '<TR>';
foreach ($fields as $value=>$foo) {
  echo '<TH class="messageHeader" style="color:#000000;">' . $value . "</TH>";  
}
echo '</TR><TR>';
foreach ($fields as $value) {
  $split=explode('#',$value);
  echo '<td class="messageData" style="color:#000000;">' . $split[0] . '</td>';
}
echo '</TR><TR>';
foreach ($fields as $value) {
  $split=explode('#',$value);
  $val=$split[1];
  if ($val!='date' and $val!='datetime') {
    $val.='('.$split[2].')';
  }
  echo '<td class="messageData" style="color:#000000;">' . $val . '</td>';
}
echo '</TR>';
echo "</TABLE>";

function getFields($obj, $included=false) {
  $fields=array();
  foreach($obj as $fld=>$val) {
    $firstCar=substr($fld,0,1);
    $threeCars=substr($fld,0,3);
    if ($firstCar=="_") {
      // don't display
    } else if ( $included and ($fld=='id' or $threeCars=='ref' or $threeCars=='top' 
                            or $fld=='idle' 
                            //or $threeCars=='wbs'
                            )) {
      // don't display
    } else if ( strpos($obj->getFieldAttributes($fld),'hidden')!==false or strpos($obj->getFieldAttributes($fld),'calculated')!==false or strpos($obj->getFieldAttributes($fld),'noImport')!==false) {
      // don't display
    } else if ($firstCar==ucfirst($firstCar)) {
      //echo $fld . '<br/>';
      $subObj=new $fld();
      $subFields=getFields($subObj,true);
      $fields=array_merge($fields,$subFields);
    } else {
      $fields[$fld]=$obj->getColCaption($fld) . '#' . $obj->getDataType($fld) . '#' . $obj->getDataLength($fld);
    }
  }
  return $fields;
}
?>
</body>
</html>
