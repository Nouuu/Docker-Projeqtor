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
 * Acknowledge an operation
 */
 require_once ('../tool/projeqtor.php');
 if($_SERVER['REQUEST_METHOD'] != 'POST') {
	require_once '../tool/projeqtor.php';
	traceHack("ack.php without POST method == XSS hacking attempt" );
	exit;
 }
// TODO (SECURITY) : Check protection of Result (but not htmlentities as it contains expected divs)
if (RequestHandler::isCodeSet('resultAck')) {
  $result=RequestHandler::getValue('resultAck');
  //$result=preg_replace('//','',$result); // TODO (SECURITY) : To be checked
  $result=str_replace('\"','"',$result);
  $result=str_replace("\'","'",$result);
  echo $result;
} else if (RequestHandler::isCodeSet('resultAckDocumentVersion')) {
  $result=RequestHandler::getValue('resultAckDocumentVersion');
  $result=str_replace('\"','"',$result);
  $result=str_replace("\'","'",$result);
  echo $result;
} else {
	echo("ack type not recognized");
  echo 'errorAck';
}
?>
