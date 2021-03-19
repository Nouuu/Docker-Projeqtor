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

function getGraphImgName($root) {
  global $reportCount;
  //$user=getSessionUser();
  $reportCount+=1;
  $name=Parameter::getGlobalParameter('paramReportTempDirectory');
  $name.="/user" . getCurrentUserId() . "_";
  $name.=$root . "_";
  $name.=date("Ymd_His") . "_";
  $name.=$reportCount;
  $name.=".png";  
  return $name;
}

function testGraphEnabled() {
  global $graphEnabled;
  if ($graphEnabled) {
    return true;
  } else {
    //echo '<table width="95%" align="center"><tr><td align="center">';
    //echo '<img src="../view/img/GDnotEnabled.png" />'; 
    //echo '</td></tr></table>';
    return false;
  }  
}

function checkNoData($result,$month=null) {
  global $outMode;
  if (count($result)==0) {
    if ($outMode=='pdf' or $outMode=='excel') { ob_clean(); }
    echo '<table width="95%" align="center"><tr height="50px"><td width="100%" align="center">';
    echo '<div style="background: #FFDDDD;font-size:150%;color:#808080;text-align:center;padding:20px">';
    if(!$month){
      echo i18n('reportNoData');
    }else{
      echo i18n('reportNoDataForPeriod')." ".$month;
    }
    echo '</div>';
    echo '</td></tr></table>';
    if ($outMode=='pdf') {
      finalizePrint();
    }
    return true;
  }
  return false;
}

function hex2rgb($hex) {
  $hex = str_replace("#", "", $hex);
  if(strlen($hex) == 3) {
    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
  } else {
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
  }
  $rgb = array('R'=>$r, 'G'=>$g, 'B'=>$b);
  //return implode(",", $rgb); // returns the rgb values separated by commas
  return $rgb; // returns an array with the rgb values
}

function getFontLocation($font) {
  $current=dirname_recursive(__FILE__,2);
  return "$current/external/pChart2/fonts/$font.ttf";
}
function dirname_recursive($path, $count=1){
  if ($count > 1){
    return dirname(dirname_recursive($path, --$count));
  }else{
    return dirname($path);
  }
}
$page=1;
$lastName=null;
function excelName($name=null, $quote='"') {
  global $lastName, $page;
  if (!$name) $name=RequestHandler::getValue('reportName');
  if ($name==$lastName) {
    $page++;
    $name.=" ($page)";
  } else {
    $lastName=$name;
  }
  return ' _excel-name='.$quote.substr(str_replace(' - ',' ',$name),0,31).$quote.' ';
  
}
function excelFormatCell($cellType='data',$width=null, $color=null, $bgcolor=null, $bold=null, $hAlign=null, $vAlign=null,$fontSize=null,$valueType=null,$noWrap=false, $noBorder=false) {
  // cellType = data, header, subheader
  $format="";
  if ($width) {
    $format=" _excel-dimensions='{"
        .'"column":{"width":'.$width.'}'
        ."}' ";
  }
  $borderColor="aaaaaa";
  if (!$color) $color='000000';
  else $color=ltrim($color,'#');
  if ($bgcolor) {
    $bgcolor=ltrim($bgcolor,'#');
  } else {
    if ($cellType=='data') {
      $bgcolor='ffffff';
    } else if ($cellType=='header') {
      $bgcolor=getColorFromTheme('header');
      $color='ffffff';
      if (! $hAlign) $hAlign='center';
      if (! $vAlign) $vAlign='center';
      if ($bold===null) $bold=true;
    } else if ($cellType=='subheader') {
      $bgcolor=getColorFromTheme('subheader');
      $color='ffffff';
      $borderColor=getColorFromTheme('rowheader');
    } else if ($cellType=='subheaderred') {
      $bgcolor=getColorFromTheme('subheader');
      $color='F50000';
      $borderColor=getColorFromTheme('rowheader');
    } else if ($cellType=='rowheader') {
      $bgcolor=getColorFromTheme('rowheader');
      $color=getColorFromTheme('dark');
      if (! $hAlign) $hAlign='left';
      if (! $vAlign) $vAlign='center';
    }
    if ($bgcolor=='eeeeee') {
      $color='000000';
      $borderColor='aaaaaa';
    }
  }
  if (! $vAlign) $vAlign='center';
  if (! $hAlign) $hAlign='center';
  if ($bold===null) $bold=false;
  if (!$fontSize) $fontSize='11';
  $numberFormat='';
  if ($valueType=='work') {
    //$format.=" _excel-explicit='n' ";
    //$numberFormat='0.0#';
  }
  $format.=" _excel-styles='{"
      .'"alignment":{"horizontal":"'.$hAlign.'","vertical":"'.$vAlign.'"'.(($noWrap)?'':',"wrapText":true').'},'
      .'"font":{"size":'.$fontSize.',"color":{"rgb":"'.$color.'"}'.(($bold==true)?',"bold":true':'').'},'
      .'"fill":{"fillType":"solid","color":{"rgb":"'.$bgcolor.'"}}'
      .(($noBorder)?'':',"borders":{"outline":{"borderStyle":"thin","color":{"rgb":"'.$borderColor.'"}}}')
      .(($numberFormat)?',"numberFormat":{"formatCode":"'.$numberFormat.'"}':'')
    ."}' ";
  
  return $format;
}
function excelFormatLine($height=null) {
  // cellType = data, header, subheader
  $foramt="";
  if ($height) {
    $format="_excel-dimensions='{"
        .' "row":{"rowHeight":'.$height.'}'
      ."}' ";
  }
  return $format;
}
function getColorFromTheme($val) {
  $array=array(
      "ProjeQtOrFlatBlue"=>array("dark"=>"545381","header"=>"7b769c","subheader"=>"a6a0bc","rowheader"=>"cdcadb"),
      "ProjeQtOrFlatRed"=>array("dark"=>"833e3e","header"=>"b07878","subheader"=>"bda1a6","rowheader"=>"ddcdce"),
      "ProjeQtOrFlatGreen"=>array("dark"=>"537665","header"=>"779a84","subheader"=>"86a790","rowheader"=>"c9dbce"),
      "ProjeQtOrFlatGrey"=>array("dark"=>"656565","header"=>"898989","subheader"=>"AEAEAE","rowheader"=>"D4D1D1"),
      "default"=>array("dark"=>"000000","header"=>"909090","subheader"=>"eeeeee","rowheader"=>"eeeeee")
  );
  $theme=Parameter::getUserParameter('theme');
  $row=(isset($array[$theme]))?$array[$theme]:$array['default'];
  $result=(isset($row[$val])) ?$row[$val]:'ff0000';
  return $result;
}
?>