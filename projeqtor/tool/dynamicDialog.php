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

include_once '../tool/projeqtor.php';
if (! array_key_exists('dialog', $_REQUEST)) {
	throwError('dialog parameter not found in REQUEST');
}
$dialog=$_REQUEST['dialog'];

$dialog=Security::checkValidAlphanumeric($dialog);
if (strtolower(substr($dialog,0,6))!='dialog' and strtolower(substr($dialog,0,4))!='list') {
  traceHack("dynamicDialog called with not allowed dialog parameter '$dialog'");
}
if ($dialog=="dialogLogfiles" or $dialog=="dialogLogfile") {
  Security::checkDisplayMenuForUser('Admin');
}
$dialogFile="../tool/dynamic".ucfirst($dialog).'.php';
if (file_exists($dialogFile)) {
	include $dialogFile;
} else {
	echo "ERROR dialog=".htmlEncode($dialog)." is not an expected dialog";
}