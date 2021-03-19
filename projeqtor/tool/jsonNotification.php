<?PHP
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
 * Get the list of objects, in Json format, to display the grid list
 */
    require_once "../tool/projeqtor.php"; 
    scriptLog('   ->/tool/jsonNotification.php');
    $theEcho = '{"identifier":"id",' ;
    $theEcho .= 'label: "name",';
    $theEcho .= ' "items":[';
    
    $theEcho .= getNotifications();
    
    $theEcho .= ' ] }';
    
    echo $theEcho;
    
    function getNotifications() {
        $paramIconSize=Parameter::getUserParameter('paramIconSize');
        if(isNewGui())$paramIconSize=16;
        $paramIconSize=16;
            
        // The 'unread' status
//        $idStatus = SqlElement::getSingleSqlElementFromCriteria("Status", array("name" => "unread"))->id;
        $idStatusNotification = 1;
        
        // The connected user
        $userId = getSessionUser()->id;

        $n=new Notification();
        $nTable=$n->getDatabaseTableName();
        $nd=new NotificationDefinition();
        $ndTable=$nd->getDatabaseTableName();
        $t=new Type();
        $tTable=$t->getDatabaseTableName();
        $a=new Notifiable();
        $aTable=$a->getDatabaseTableName();
        // Select party of SqlQuery
        $select  = "SELECT ";
        $select .= "$nTable.id AS id, $nTable.notifiedObjectId AS idObjectNotif, ";
        $select .= "$tTable.name AS type, $tTable.id as typeId, ";
        $select .= "$aTable.name AS objectClassName, $aTable.notifiableItem AS notifiableItem, ";
        $select .= "$ndTable.name AS definition";
        
        // From party of SqlQuery
        $from = " FROM $nTable ";
        
        // InnerJoin party of SqlQuery
        $innerJoin  = "INNER JOIN $tTable ON $tTable.id = $nTable.idNotificationType ";
        $innerJoin .= "LEFT JOIN $aTable ON $aTable.id = $nTable.idNotifiable ";
        $innerJoin .= "LEFT JOIN $ndTable ON $ndTable.id = $nTable.idNotificationDefinition ";
        
        // Only unread notifications with notificationDate <= current date
        $theDate = new DateTime();
        $theCurrentDate = $theDate->format('Y').'-'.$theDate->format('m').'-'.$theDate->format('d');        
        
        // Where party of SqlQuery
        $where  = " WHERE $nTable.idle=0";
        $where .= " AND $nTable.idStatusNotification=$idStatusNotification";
        $where .= " AND $nTable.idUser=$userId";
        //$where .= " AND $nTable.notificationDate<='".$theCurrentDate."' ";
        //$where .= " AND IF(ISNULL($nTable.notificationTime) OR $nTable.notificationDate<DATE(NOW()),(1=1),$nTable.notificationTime<TIME(NOW()))";
        $where .= " AND (      $nTable.notificationDate<'$theCurrentDate'";
        $where .= "       OR ( $nTable.notificationDate='$theCurrentDate' AND $nTable.notificationTime IS NULL )";
        $timeNow=(Sql::isPgsql())?'CURRENT_TIME':'TIME(NOW())';
        $where .= "       OR ( $nTable.notificationDate='$theCurrentDate' AND $nTable.notificationTime IS NOT NULL AND $nTable.notificationTime<$timeNow )";
        $where .= "     )";
        // Order By party of SqlQuery
        $orderBy = "ORDER BY typeId asc, objectClassName asc, definition asc";
        
        // The Sql Query
        $query = $select . $from . $innerJoin . $where . $orderBy;
        
        // Execute the Query
        $result = Sql::query($query);
        // At less, one result
        if (Sql::$lastQueryNbRows > 0) {
          $notifList=array();
          $obj = new stdClass();
          $line = Sql::fetchLine($result);
          // Read each line of result
          while ($line) {
            if (Sql::isPgsql()) { // Must replace lowercase data with cased values
              foreach (array('idObjectNotif','typeId', 'objectClassName', 'notifiableItem') as $fld) {
                $line[$fld]=$line[strtolower($fld)];
              }
            }
            $notifList[]=$line;
            $line = Sql::fetchLine($result);
          }
        } else { // No result
            $notifList=array();
        }
                
        // If no notification, Only Total
        if (count($notifList)===0) {
            $totalCount=0;
            $theId=1;
            $iconRoot="iconSum";
            $row  = '{id:"' . $theId . '"';
            $row .= ', name:"'. str_replace('"', "''", ucfirst(i18n("colCountTotal"))) . ' ('.$totalCount.')"';
            $row .= ', objClass:""';
            $row .= ', objId:""';
            //$row .= ', iconClass:"iconSum' . $paramIconSize. '"';
            $row .= ', iconClass:"'.$iconRoot.$paramIconSize.' '.$iconRoot.' iconSize'.$paramIconSize.'"';
            $row .= ', count:"'.$totalCount.'"';
            $row .= ', isTotal:"YES"';
            $row .= ', type:"folder"';
            $row .=  ', children : []}';

            return $row;            
        }
        
        $previousType = "";
        $previousObjectClass = "";
        $previousDefinition = "";
        $previousObjectId = "";
        $arrayTree = [];
        foreach($notifList as $notif) {
            // Notification created by hand
            if (is_null($notif["idObjectNotif"])) {
                $notif["idObjectNotif"] = $notif["id"];
                $notif["objectClassName"] = i18n("NotificationManual");
                $notif["notifiableItem"] = "NotificationManual";                
                $notif["definition"] = i18n("withoutNotificationDefinition");
            }
            
// MTY - LEAVE SYSTEM            
            // Notification without definition
            if(is_null($notif["definition"])) {
                $notif["definition"] = i18n("withoutNotificationDefinition");                
            }
// MTY - LEAVE SYSTEM
            
            // First Level is 'Type'
            if ($notif["type"] <> $previousType) { // New Type
                $arrayTree[$notif["type"]] = ["count" => 1, 
                                              "type" => $notif["type"],
                                              "objectClass" => null
                                             ];
                $previousType = $notif["type"];
                $previousObjectClass="";                
            } else { // Same Type
                $arrayTree[$notif["type"]]["count"]++;
            }
            // Second Level is Notifiable
            if ($notif["objectClassName"] <> $previousObjectClass) { // New Notifiable
                $arrayTree[$notif["type"]]["objectClass"][$notif["objectClassName"]] = 
                                                ["count" => 1,
                                                 "objectClassName" => $notif["objectClassName"],
                                                 "notifiableItem" => $notif["notifiableItem"],
                                                 "definition" => null
                                                ]; 
                $previousObjectClass = $notif["objectClassName"];
                $previousDefinition="";
            } else { // Same Notifiable
                $arrayTree[$notif["type"]]["objectClass"][$notif["objectClassName"]]["count"]++;
            }            
            // Third Level is NotificationDefinition
            if ($notif["definition"] <> $previousDefinition) { // New Definition
                $arrayTree[$notif["type"]]["objectClass"][$notif["objectClassName"]]["definition"][$notif["definition"]] = 
                        [   "count" => 1,
                            "definition" => $notif["definition"],
                            "objectId" => null
                        ];
                $previousDefinition = $notif["definition"];
                $previousObjectId="";
            } else { // Same Definition
                $arrayTree[$notif["type"]]["objectClass"][$notif["objectClassName"]]["definition"][$notif["definition"]]["count"]++;
            }
            
            // Last Level is ObjectId
            if ($notif["idObjectNotif"] <> $previousObjectId) {
                $arrayTree[$notif["type"]]["objectClass"][$notif["objectClassName"]]["definition"][$notif["definition"]]["objectId"][$notif["idObjectNotif"]] = 
                        ["count" => 1,
                         "idObject" => $notif["idObjectNotif"],
                        ];
                $previousObjectId = $notif["idObjectNotif"];
            } else {
                $arrayTree[$notif["type"]]["objectClass"][$notif["objectClassName"]]["definition"][$notif["definition"]]["objectId"][$notif["idObjectNotif"]]["count"]++;
            }
        }
        
        $nbRows=0;
        $theId=0;
        $theEcho = "";
        $totalCount=0;
        // First Level => Type            
        foreach ($arrayTree as $type) {
            $totalCount += $type['count'];
            if ($nbRows>0) {$theEcho .= ', ';}
            $nbRows++;
            $theId++;
            $iconRoot='icon' .ucfirst(strtolower($type["type"]));
            $row  = '{id:"'. $theId . '"';
            $row .= ', name:"'. str_replace('"', "''",i18n($type["type"])) . ' ('.$type['count'].')"';
            $row .= ', objClass:""';
            $row .= ', objId:""';
            //$row .= ', iconClass:"icon' .ucfirst(strtolower($type["type"])).$paramIconSize. '"';
            $row .= ', iconClass:"'.$iconRoot.$paramIconSize.' '.$iconRoot.' iconSize'.$paramIconSize.'"';
            $row .= ', count:"'.$type['count'].'"';
            $row .= ', isTotal:"NO"';
            $row .= ', type:"folder"';
            $theEcho .= $row;
            
            $theEcho .=  ', children : [';
            
            // Second Level = Notifiable
            $listObjClass = $type["objectClass"];
            $nbRowsObjectClass=0;           
            foreach($listObjClass as $objectClass) {
                if ($nbRowsObjectClass>0) {$theEcho .= ', ';}
                $nbRowsObjectClass++;
                $theId++;
                $iconRoot='icon'.$objectClass["notifiableItem"];
                $row  = '{id:"' . $theId . '"';
                $row .= ', name:"'. str_replace('"', "''",$objectClass["objectClassName"]) . ' ('.$objectClass['count'].')"';
                $row .= ', objClass:""';
                $row .= ', objId:""';
                //$row .= ', iconClass:"icon' .$objectClass["notifiableItem"].$paramIconSize. '"';
                $row .= ', iconClass:"'.$iconRoot.$paramIconSize.' '.$iconRoot.' iconSize'.$paramIconSize.'"';
                $row .= ', count:"'.$objectClass['count'].'"';
                $row .= ', isTotal:"NO"';
                $row .= ', type:"folder"';
                $theEcho .= $row;

                $theEcho .= ', children : [';
                // Third Level = Definition
                $listDef = $objectClass["definition"];
                $nbRowsDef=0;
                $iconRoot="iconNotificationDefinition";
               foreach($listDef as $definition) {
                    if ($nbRowsDef>0) {$theEcho .= ', ';}
                    $nbRowsDef++;
                    $theId++;
                    $row  = '{id:"' . $theId . '"';
                    $row .= ', name:"'. str_replace('"', "''",$definition["definition"]) . ' ('.$definition['count'].')"';
                    $row .= ', objClass:""';
                    $row .= ', objId:""';
                    //$row .= ', iconClass:"iconNotificationDefinition'.$paramIconSize. '"';
                    $row .= ', iconClass:"'.$iconRoot.$paramIconSize.' '.$iconRoot.' iconSize'.$paramIconSize.'"';
                    $row .= ', count:"'.$definition['count'].'"';
                    $row .= ', isTotal:"NO"';
                    $row .= ', type:"folder"';
                    $theEcho .= $row;
                    
                    $theEcho .= ', children : [';                    
                    // Last Level = Object Id
                    $listIdObj = $definition["objectId"];
                    $nbRowsId=0;
                    $iconRoot="iconGoto";
                    foreach($listIdObj as $objectId) {
                        if ($nbRowsId>0) {$theEcho .= ', ';}
                        $nbRowsId++;
                        $theId++;
                        $row  = '{id:"' . $theId . '"';
                        $row .= ', name:"#'. str_replace('"', "''",$objectId["idObject"]) . ' ('.$objectId['count'].')"';
                        $row .= ', objClass:"'.$objectClass["notifiableItem"].'"';
                        $row .= ', objId:"'.$objectId["idObject"].'"';
                        //$row .= ', iconClass:"iconGoto' . $paramIconSize. '"';
                        $row .= ', iconClass:"'.$iconRoot.$paramIconSize.' '.$iconRoot.' iconSize'.$paramIconSize.'"';
                        $row .= ', count:"'.$objectId['count'].'"';
                        $row .= ', isTotal:"NO"';
                        $row .= ', type:"folder"}';
                        $theEcho .= $row;
                    }
                    $theEcho .= ']}';
                }
                $theEcho .= ']}';
            }
            $theEcho .= ']}';
        }
        // The total
        $theId++;
        $iconRoot="iconSum";
        if ($nbRows>0) {$theEcho .= ', ';}
        $row  = '{id:"' . $theId . '"';
        $row .= ', name:"'. str_replace('"', "''", ucfirst(i18n("colCountTotal"))) . ' ('.$totalCount.')"';
        $row .= ', objClass:""';
        $row .= ', objId:""';
        //$row .= ', iconClass:"iconSum' . $paramIconSize. '"';
        $row .= ', iconClass:"'.$iconRoot.$paramIconSize.' '.$iconRoot.' iconSize'.$paramIconSize.'"';
        $row .= ', count:"'.$totalCount.'"';
        $row .= ', isTotal:"YES"';
        $row .= ', type:"folder"';
        $theEcho .= $row;

        $theEcho .=  ', children : []}';

        return $theEcho;
    }    
?>
