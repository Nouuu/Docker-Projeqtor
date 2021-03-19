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

/* ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
  require_once "../tool/projeqtor.php";
  require_once "../tool/formatter.php";
  scriptLog('   ->/view/logfiles.php');
  
  $user=getSessionUser(); 

echo '<table>';
echo '<tr>';
echo '<td width="50%" class="reportTableHeader">'.i18n('colName').'</td>';
echo '<td width="20%" class="reportTableHeader">'.i18n('colDate').'</td>';
echo '<td width="20%" class="reportTableHeader">'.i18n('colSize').'</td>';
echo '<td width="10%" class="reportTableHeader"></td>';
echo '</tr>';
$list=Logfile::getList(true);
$list=array_reverse($list);
setSessionValue('logFilesList', $list);
foreach ($list as $id=>$file) {
  echo '<tr>';
  echo '<td class="reportTableData" style="text-align:left;padding:0px 10px">'.$file['name'].'</td>';
  echo '<td class="reportTableData" style="padding:0px 10px">'.htmlFormatDateTime($file['date'],false).'</td>';
  echo '<td class="reportTableData">'.byteSize($file['size']).'</td>';
  echo '<td class="reportTableData">';
  echo '<a href="../tool/download.php?class=Logfile&id=' . $id . '" target="printFrame" title="' . i18n('helpDownload') . '">';
  echo formatSmallButton('Download');
  echo '</a>&nbsp;&nbsp;';
  echo '<a style="cursor:pointer" title="'. i18n('helpLogfile').'" onClick="showLogfile(\''.$file['name'].'\');" >';
  echo formatSmallButton('View');
  echo '</a>';
  echo '</td>';
  echo '</tr>';
}
echo '</table>';

function byteSize($size) {
  $letter=i18n('byteLetter');
  if ($size<1000) {
    return $size.'&nbsp;'.$letter;
  }
  $size=round($size/1024,1);
  if ($size<1000) {
    return $size.'&nbsp;K'.$letter;
  }
  $size=round($size/1024,1);
  if ($size<1000) {
    return $size.'&nbsp;M'.$letter;
  }
  $size=round($size/1024,1);
  if ($size<1000) {
    return $size.'&nbsp;G'.$letter;
  }
}
?>