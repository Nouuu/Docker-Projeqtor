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
if (!isset($outMode)) $outMode='html';
include_once('headerFunctions.php');
$reportContext=true;
// Header
//echo "<page_header>";
if (Parameter::getGlobalParameter('logLevel')>='4') {
  debugTraceLog($_SERVER['SCRIPT_FILENAME']);
}
projeqtor_set_time_limit(300);
projeqtor_set_memory_limit('512M');

// Security : check that no special car appears in the request
// Note: This check is not good enough, need to filter values based on context.
foreach ($_REQUEST as $reqParam=>$reqValue) {
	if ($reqParam=='reportName') {
    // Report name can have spec car. Will be escaped on display
	} else if ($reqParam=='refId') {
		if (! is_numeric($reqValue) ) {
			$refId='0';
		}
	} else {
		if(!is_array($reqValue)) {
			if ($reqValue!=Sql::fmtStr($reqValue)) {
				traceHack("improper value '$reqValue' for request parameter '$reqParam' while calling a report");
				if (!empty($cronnedScript)) goto end; else exit;
			}
		} else {
			foreach($reqValue as $v) {
				if ($v!=Sql::fmtStr($v)) {
					traceHack("improper value '$v' for request parameter '$reqParam' while calling a report");
					if (!empty($cronnedScript)) goto end; else exit;
				}
			}
		}
	}
}
if ($outMode=='excel') $headerParameters=rtrim(br2nl($headerParameters),"\n");
echo '<table _excel-name="'.i18n("menuParameter").'" style="width:100%">';
echo '<tr>';
if ($outMode!='excel') echo "<td style='width:1%' class='' ".excelFormatCell('data',1,null,null,null,null,null,null,null,null,true).">&nbsp;</td>";
echo "<td style='border-radius:5px 0 0 5px;width:10%' class='reportHeader' ".excelFormatCell('header',20,null,null,null,null,null,null,null,null,true).">" . i18n('colParameters') . "</td>";
if ($outMode!='excel') echo "<td style='border-top:1px solid var(--color-darker);border-bottom:1px solid var(--color-darker);width:1%' class='' ".excelFormatCell('data',1,null,null,null,null,null,null,null,null,true).">&nbsp;</td>";
if ($outMode!='excel') echo "<td style='border-top:1px solid var(--color-darker);border-bottom:1px solid var(--color-darker);width:1%' ".excelFormatCell('data',1,null,null,null,null,null,null,null,null,true).">&nbsp;</td>";
echo "<td style='white-space:nowrap;width:10%;padding-right:20px;border:1px solid var(--color-darker);border-left:0px;' ".excelFormatCell('data',50,null,null,false,'left',null,null,null,null,true).">"; 
echo $headerParameters;
echo "</td>";
echo "<td align='center' style='width:40%; font-size: 100%; font-weight: normal;' ".excelFormatCell('header',80,null,null,null,null,null,16,null,null, true).">"; 

if (array_key_exists('reportName', $_REQUEST)) {
  if ($outMode!='excel') echo '<table><tr><td class="reportHeader reportHeaderRounded" style="text-align: center; padding: 10px 30px 10px 30px;">';
  echo htmlEncode(ucfirst($_REQUEST['reportName']),'html');
  if ($outMode!='excel') echo '</td></tr></table>';
}
echo "</td>";
if ($outMode!='excel') echo "<td style='width:1%'>&nbsp;</td>";
echo "<td style='width:15%; text-align:right' ".excelFormatCell('data',30,null,null,false,'right',null,null,null,null,true).">";
echo  htmlFormatDate(date('Y-m-d')) . " " . date('H:i');
echo "</td>";
echo "<td style='width:1%'>&nbsp;</td>";
echo "</tr></table>";
echo "<br/>";
//echo "</page_header>";

$graphEnabled=true;
if (! function_exists('ImagePng')) {
  $graphEnabled=false;
  errorLog("GD Library not enabled - impossible to draw charts");
}
if (! function_exists('imageftbbox')) {
  $graphEnabled=false;
  errorLog("GD Library or FreeType Librairy incorrect or not correctly installed - impossible to draw charts");
}

$rgbPalette=array(
6=>array('B'=>200, 'G'=>100, 'R'=>100),
7=>array('B'=>100, 'G'=>200, 'R'=>100),
8=>array('B'=>100, 'G'=>100, 'R'=>200),
9=>array('B'=>200, 'G'=>200, 'R'=>100),
10=>array('B'=>200, 'G'=>100, 'R'=>200),
11=>array('B'=>100, 'G'=>200, 'R'=>200),
0=>array('B'=>250, 'G'=> 50, 'R'=> 50),
1=>array('B'=> 50, 'G'=>250, 'R'=> 50),
2=>array('B'=> 50, 'G'=> 50, 'R'=>250),
3=>array('B'=>250, 'G'=>250, 'R'=> 50),
4=>array('B'=>250, 'G'=> 50, 'R'=>250),
5=>array('B'=> 50, 'G'=>250, 'R'=>250)
);

$arrayColors=array('#1abc9c', '#2ecc71', '#3498db', 
'#9b59b6', '#34495e', '#16a085', '#27ae60', '#2980b9', 
'#8e44ad', '#2c3e50', '#f1c40f', '#e67e22', '#99CC00',
'#e74c3c', '#95a5a6', '#d35400', '#c0392b', '#bdc3c7', '#7f8c8d');

end:

?>