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

ini_set('soap.wsdl_cache_enabled', 0); // Delete cache
ini_set('default_socket_timeout', 180);
class wsServer {
  function getVersion($parm) {
    return 'V1.5.0';
  }
}

try {
  $server = new SoapServer('monFormat.wsdl',  array('trace' => 1,'encoding'    => 'UTF-8'));
  $server -> setclass('wsServer');
  $server->setPersistence(SOAP_PERSISTENCE_REQUEST);
} catch (Exception $e) {
  echo 'WS Error '.$e;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  try {
    $server -> handle();}
  catch (Exception $e) {
    echo 'WS Error '.$e;
  }
} else {
  echo '<strong>This SOAP server can handle following functions : </strong>';
  echo '<ul>';
  foreach($server -> getFunctions() as $func) {
    echo '<li>' , $func , '</li>';
  }
  echo '</ul>';
}
?>