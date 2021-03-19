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

// TODO (SECURITY) : should be disabled until thoroughly fixed from security vulnerabilities (i.e. directory traversal)

if (! array_key_exists('logname',$_REQUEST)) {
	throwError('Parameter logname not found in REQUEST');
}
$logname=$_REQUEST['logname'];

if ($logname=='last') {
  $log=Logfile::getLast();
} else {
  $log=new Logfile($logname);
}
?>
<table style="width: 100%;" >
 <tr>
   <td style="width: 100%;" align="center">
     <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogLogfile').hide();">
       <?php echo i18n("close");?>
     </button>
   </td>
 </tr>      
</table>
<pre id="logTableContainer">
<?php 
echo htmlEncode(file_get_contents($log->filePath));
?>
</pre>
<table style="width: 100%;" >
 <tr>
   <td style="width: 100%;" align="center">
     <button dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogLogfile').hide();">
       <?php echo i18n("close");?>
     </button>
   </td>
 </tr>      
</table>




