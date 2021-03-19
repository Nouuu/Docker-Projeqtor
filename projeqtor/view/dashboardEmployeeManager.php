<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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

// LEAVE SYSTEM

/* ============================================================================
 * 
 * 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/dashboardEmployeeManager.php');  
  $user=getSessionUser();
  if ($user->isEmployee==0) {
        $result = '<div class="messageNotificationWarning" style="height:50px;text-align:center">';
        $result .= i18n('YouMustBeAnEmployeeToAccessAtTheDashboardEmployeeManager');
        $result .= '</div>';
        echo $result;
      return;
  }
  
  $imgDelay = 'css/images/iconDelay32.png';
  
  $currentYear = (new DateTime())->format("Y");
  $firstYear = $currentYear-20;
  $lastYear = $currentYear+3;

  $idEmployeeRequest=0;
  if (array_key_exists("idEmployee", $_REQUEST)) {
      $idEmployeeRequest = $_REQUEST['idEmployee'];
  }
  // List of managed employees
  $employeeList = getUserVisibleResourcesList(true, "List",'', false,true,true,true);
  asort($employeeList, SORT_NATURAL | SORT_FLAG_CASE);
  if ($idEmployeeRequest!=0) {
      $name = $employeeList[$idEmployeeRequest];
      $employeeListRequest[$idEmployeeRequest] = $name;
  } else {
      $employeeListRequest = $employeeList;
  }
  
  $idStatusRequest=0;
  if (array_key_exists("idStatus", $_REQUEST)) {
      $idStatusRequest = $_REQUEST['idStatus'];
  }
  $idTypeRequest=0;
  if (array_key_exists("idLeaveType", $_REQUEST)) {
      $idTypeRequest = $_REQUEST['idLeaveType'];
  }
  $yearRequest=(new DateTime())->format("Y");
  if (array_key_exists("year", $_REQUEST)) {
      $yearRequest = $_REQUEST['year'];
  }
  $monthRequest=(new DateTime())->format("m");
  if (array_key_exists("month", $_REQUEST)) {
      $monthRequest = ($_REQUEST['month']<10?"0":"").$_REQUEST['month'];
  }

  $lastDayOfMonth=lastDayOfMonth((int)$monthRequest, $yearRequest);
  $startDateRequest = $yearRequest."-".$monthRequest."-01";
  $endDateRequest = $yearRequest."-".$monthRequest."-".$lastDayOfMonth;

  $idUser = $user->id;
  $profile=new Profile($user->idProfile);
  $manager = new EmployeeManager($idUser);
  $isManager = $manager->isManager();
  
  // THE LEAVE TYPES
  $leaveTypes = LeaveType::getList();
  if ($idTypeRequest==0) {
      $nbTypes = 1;
  } else {
      $nbTypes = count($leaveTypes);
  }
  $leaveTypesColor = null;
  $leaveTypeRequestColor="#000000";
  $leaveTypeRequestName="";
  foreach($leaveTypes as $leaveType) {
      // THE LEAVES TYPES Color
      $leaveTypesColor[$leaveType->id]=$leaveType->color;
      if ($leaveType->id == $idTypeRequest) {
        $leaveTypeRequestColor=$leaveType->color;
        $leaveTypeRequestName=$leaveType->name;          
      }
  }
  
  // THE LEAVE WORKFLOW STATUS
  $listStatus = LeaveType::getStatusList();
  
  $statusListFirstLetter[0] = "U";
  $leaveStatusColor[0]="#000000";
  foreach ($listStatus as $status) {
      // Status FirstLetter name;
      $statusListFirstLetter[$status->id] = substr(strtoupper($status->name), 0,1);
      // Status color
      $leaveStatusColor[$status->id] = $status->color;
  }
      
  $print=false;
  if (isset($_REQUEST['print'])) {
  	$print=true;
  }
  
?>

<input type="hidden" name="objectClassManual" id="objectClassManual" value="dashboardEmployeeManager" />
<input type="hidden" name="idEmployeeCalendar" id="idEmployeeCalendar" value="<?php echo $idUser; ?>" />
<input type="hidden" name="idUserCalendar" id="idUserCalendar" value="<?php echo $idUser; ?>" />
<input type="hidden" name="isManagerCalendar" id="isManagerCalendar" value="<?php echo ($isManager?1:0); ?>" />
<input type="hidden" name="typeOfExportFile" id="typeOfExportFile" value="<?php echo Parameter::getUserParameter("typeExportXLSorODS"); ?>" />
<div  class="container" dojoType="dijit.layout.BorderContainer" >
    <div style="overflow: <?php echo(!$print)?'auto':'hidden';?>;padding-bottom:10px" id="detailDiv" dojoType="dijit.layout.ContentPane" region="center">        
    <!-- content of ObjectMain -->
<!--------------->
<!-- THE TITLE -->
<!--------------->
        <table class="listTitle" width="100%">
            <tr height="32px;" style="vertical-align: middle;">
                <td width="50px" align="center"><?php echo formatIcon("DashboardEmployeeManager", 32, null, true);?></td>
                <td width="200px"><span class="title"><?php echo i18n('menuDashboardEmployeeManager');?>&nbsp;</span></td>
                <?php
                    // No leave type or no status => End of story
                    if ($leaveTypesColor==null or $statusListFirstLetter==null) {
                ?>
            </tr>
        </table>
        <table style="vertical-align:middle;text-align:center;height:90%;width:100%">
            <tr>
                <td style="height:100%;width:100%;"><span style="font-size:250%;font-weight:bold;"><?php echo i18n('DashboardEmpty');?></span></td>                
            </tr>
        </table>
    </div>
</div>    
                <?php
                        exit;
                    }
                ?>
                <!----------------->
                <!-- SELECT YEAR -->
                <!----------------->
                <td style="width:150px;vertical-align:middle;">
                    <div style="width:150px; margin:0 auto;">
                        <img id="yearPrev" name = "yearPrev" src="css/images/left.png"
                             onclick="nextPrevYearDashboardEmployeeManager(-1,<?php echo $firstYear; ?>,<?php echo $lastYear; ?>)"
                             title="<?php echo i18n("previous"); ?>"
                             style="width:16px; height:16px; cursor:pointer;position:relative;top:5px;">
                        <select id="yearSelect" name="yearSelect" dojoType="dijit.form.FilteringSelect"  data-dojo-id="yearSelect"
                                class="filterField roundedLeft"  xlabelType="html" style="width:80px;font-weight:bold;text-align:center;"
                                <?php  echo autoOpenFilteringSelect();?>
                        >
                        <?php
                            for($i=$firstYear; $i<=$lastYear;$i++) {
                                echo '<option value="' . $i . '"';
                                if ($yearRequest == $i) {
                                    echo " SELECTED ";
                                }
                                echo '>';
                                echo '  <span >'. $i . '</span>';
                                echo '</option>';
                            }
                        ?>        
                        <script type="dojo/connect" event="onChange" args="evt">
                            loadContentDashboardEmployeeManager();
                        </script>
                        </select>
                        <img id="yearNext" name = "yearNext" src="css/images/right.png" 
                             onclick="nextPrevYearDashboardEmployeeManager(1,<?php echo $firstYear; ?>,<?php echo $lastYear; ?>)"
                             title="<?php echo i18n("next"); ?>"
                             style="width:16px; height:16px; cursor:pointer;position:relative;top:5px;">
                    </div>
                </td>                    
                <!------------------>
                <!-- SELECT MONTH -->
                <!------------------>
                <td style="width:170px;">
                    <div style="width:170px; margin:0 auto;">
                        <img id="monthPrev" name = "monthPrev" src="css/images/left.png" 
                             onclick="nextPrevMonthDashboardEmployeeManager(-1)"
                             title="<?php echo i18n("previous"); ?>"
                             style="width:16px; height:16px; cursor:pointer;position:relative;top:5px;">
                        <select id="monthSelect" name="monthSelect" dojoType="dijit.form.FilteringSelect"  data-dojo-id="monthSelect"
                                class="filterField roundedLeft"  xlabelType="html" style="width:100px;font-weight:bold;text-align:center;"
                                <?php  echo autoOpenFilteringSelect();?>
                        >
                        <?php
                            $monthArray= getArrayMonth(null);
                            foreach($monthArray as $num => $name) {
                                echo '<option value="' . ($num+1) . '"';
                                if ($monthRequest == ($num+1)) {
                                    echo " SELECTED ";
                                }
                                echo '>';
                                echo '  <span >'. $name . '</span>';
                                echo '</option>';
                            }
                        ?>        
                        <script type="dojo/connect" event="onChange" args="evt">
                            loadContentDashboardEmployeeManager();
                        </script>
                        </select>
                        <img id="monthNext" name = "monthNext" src="css/images/right.png" 
                             onclick="nextPrevMonthDashboardEmployeeManager(1)"
                             title="<?php echo i18n("next"); ?>"
                             style="width:16px; height:16px; cursor:pointer;position:relative;top:5px;">
                    </div>
                </td>
                <!--------------------->
                <!-- SELECT EMPLOYEE -->
                <!--------------------->
                <td width="240px">
                    <div style="width:240px; margin:0 auto;">
                    <?php if(isNewGui()){?><table><tr><td><?php }?>
                        <label style="text-shadow: none;width:80px;"
                               for='employeeSelect'><?php echo (i18n("colIdEmployee")).Tool::getDoublePoint();?>
                        </label>
                        <?php if(isNewGui()){?> </td><td> <?php } ?>
                        <select id="employeeSelect" name="employeeSelect" dojoType="dijit.form.FilteringSelect" 
                                data-dojo-id="employeeSelect" class="filterField roundedLeft" xlabelType="html" style="width:120px;"
                                <?php  echo autoOpenFilteringSelect();?>
                        >
                        <?php 
                            echo '<option value="0"';
                            if ($idEmployeeRequest==0) {
                                echo ' SELECTED ';
                            }
                            echo '><span ></span></option>';
                            foreach($employeeList as $id => $name) {
                                echo '<option value="' . $id . '"';
                                if ($idEmployeeRequest==$id) {
                                    echo ' SELECTED ';
                                }
                                echo '><span >'. htmlEncode($name) . '</span></option>';
                            }
                        ?>
                            <script type="dojo/connect" event="onChange" args="evt">
                                loadContentDashboardEmployeeManager();
                            </script>
                        </select>
                         <?php if(isNewGui()){?>  </td></tr></table> <?php } ?>
                    </div>
                </td>
                <!----------------------->
                <!-- SELECT LEAVE TYPE -->
                <!----------------------->
                <td width="240px">
                    <div style="width:240px; margin:0 auto;">
                    <?php if(isNewGui()){?><table><tr><td><?php }?>
                        <label style="text-shadow: none;width:80px;"
                               for='leaveTypeSelect'><?php echo (i18n("colType")).Tool::getDoublePoint();?>
                        </label>
                     <?php if(isNewGui()){?> </td><td> <?php } ?>
                        <select id="leaveTypeSelect" name="leaveTypeSelect" dojoType="dijit.form.FilteringSelect"  data-dojo-id="leaveTypeSelect"
                                class="filterField roundedLeft" xlabelType="html" style="width:120px;"
                                <?php  echo autoOpenFilteringSelect();?>
                        > 
                        <?php 
                            echo '<option value="0"';
                            if ($idTypeRequest==0) {
                                echo ' SELECTED ';
                            }
                            echo '><span ></span></option>';
                            foreach($leaveTypes as $lvT) {
                                echo '<option value="' . $lvT->id . '"';
                                if ($idTypeRequest==$lvT->id) {
                                    echo ' SELECTED ';
                                }
                                echo '><span >'. htmlEncode($lvT->name) . '</span></option>';
                            }
                        ?>
                            <script type="dojo/connect" event="onChange" args="evt">
                                loadContentDashboardEmployeeManager();
                            </script>
                        </select>
                        <?php if(isNewGui()){?>  </td></tr></table> <?php } ?>
                    </div>
                </td>
                <!------------------->
                <!-- SELECT STATUS -->
                <!------------------->
                <td width="240px">
                    <div style="width:240px; margin:0 auto;">
                    <?php if(isNewGui()){?><table><tr><td><?php }?>
                        <label style="text-shadow: none;width:80px;"
                               for='leaveStatusSelect'><?php echo (i18n("colIdStatus")).Tool::getDoublePoint();?>  
                        </label>
                         <?php if(isNewGui()){?> </td><td> <?php } ?>
                        <select id="leaveStatusSelect" name="leaveStatusSelect" dojoType="dijit.form.FilteringSelect" 
                                data-dojo-id="leaveStatusSelect" class="filterField roundedLeft" xlabelType="html" style="width:120px;"
                                <?php  echo autoOpenFilteringSelect();?>
                        >
                        <?php 
                        
                            echo '<option value="0"';
                            if ($idStatusRequest==0) {
                                echo ' SELECTED ';
                            }
                            echo '><span ></span></option>';
                            foreach($listStatus as $status) {
                                echo '<option value="' . $status->id . '"';
                                if ($idStatusRequest==$status->id) {
                                     echo ' SELECTED ';
                                 }
                                 echo '><span >'. htmlEncode($status->name) . '</span></option>';
                            }
                        ?>        
                            <script type="dojo/connect" event="onChange" args="evt">
                                loadContentDashboardEmployeeManager();
                            </script>
                        </select>
                         <?php if(isNewGui()){?>  </td></tr></table> <?php } ?>
                    </div>
                </td>
                <td style="position:relative;"></td>
            </tr>
        </table>
    
        <!--------------------------------->
        <!-- LIST OF EXISTING LEAVE TYPE -->
        <!--------------------------------->
        <table style="margin-top:10px;margin-bottom:5px;font-size:12px;">
            <th class="label" style="font-size:13px;"><b><?php echo (i18n("colType")); ?> : </b></th>
            <?php
                foreach($leaveTypes as $lvt) {
                    $textColor = oppositeColor($lvt->color);
                    $echo  = '<td>&nbsp;</td>';
                    $echo .= '<td>';
                    $echo .= ' <span class="leaveType" style="background-color:'.$lvt->color.';color:'.$textColor.';">&nbsp;'.$lvt->name.'&nbsp;</span>';
                    $echo .= '</td>';
                    echo $echo;
                }
            ?>
        </table>
        <!------------------------------------------------------->
        <!-- LIST OF STATUS OF WORKFLOW DEDICATED TO THE LEAVE -->
        <!------------------------------------------------------->                
        <table style="font-size:12px;margin-bottom:5px;">
            <th class="label" style="font-size:13px;"><b><?php echo (i18n("colIdStatus")); ?> : </b></th>
            <td></td>
            <td>
                <?php
                    $textColor = oppositeColor($leaveStatusColor[0]);
                    echo '<td>
                            <span class="leaveStatus" style="background-color:'.$leaveStatusColor[0].'; color:'.$textColor.';">
                                &nbsp;'.$statusListFirstLetter[0].'
                            </span>:'.i18n("unknown").'
                        </td>
                    ';
                ?>
            </td>            
            <?php
                foreach($listStatus as $key=>$status) {
                    $textColor = oppositeColor($status->color);
                    echo '<td>&nbsp;</td>
                        <td>
                            <span class="leaveStatus" style="background-color:'.$status->color.'; color:'.$textColor.';">
                                &nbsp;'.substr(strtoupper($status->name),0,1).'
                            </span>:'.$status->name.'
                        </td>
                    ';
                }
            ?>
        </table>
        <table style="width:100%; height:90%;text-align:center;border-top:1px solid black;">
            <tr style="height:100%;width:100%;vertical-align:top;">
                <!------------------------->
                <!-- THE LEAVES CALENDAR -->
                <!------------------------->
                <td style="width:60%;">
                    <table style="height:5%;vertical-align:top;text-align:center;margin-bottom:15px;">
                        <!----------->
                        <!-- TITLE -->
                        <!----------->
                        <td style="width:20%;font-size:12px;">
                            <b><?php echo (i18n("leaveCalendar")); ?></b>
                        </td>
                        <td style="width:30%;font-size:12px;">
                          <div style="margin-left:20%;">
                            <table>
                              <tr>
                                <td><?php echo i18n("leaveRequestAfterLeaveDate");?></td>
                                <td width="30px"><img src="<?php echo $imgDelay;?>" style="width:16px;height:16px;left:5px;"></td>
                              </tr>
                            </table>
                          </div>
                        </td>
                        <!--------------------->
                        <!-- EXPORT TO EXCEL -->
                        <!--------------------->
                        <td style="width:10%;font-size:12px">
                          <table>
                              <tr>
                                <td><b><?php echo (i18n("dialogExport")); ?></b></td>
                                <td><div style="width:50px; margin:0 auto;">
                                      <button id="exportLeaveCalendar" dojoType="dijit.form.Button"showlabel="false"
                                        title="<?php echo i18n('buttonExcel');?>"iconClass="dijitButtonIcon dijitButtonIconExcel" class="detailButton">
                                        <script type="dojo/connect" event="onClick" args="evt">
                                          exportLeaveCalendarOfDashboardEmployeeManager();
                                        </script>
                                      </button>
                                    </div>
                                </td>
                              </tr>
                            </table>
                    </table>
                    <!---------------------->
                    <!-- CALENDAR CONTENT -->
                    <!---------------------->
                    <table style="margin-left:20px;vertical-align:top;text-align:center;">
                        <!------------>
                        <!-- HEADER -->
                        <!------------>
                        <tr style="height:15px;">
                            <td rowspan="2" class="assignHeader"><?php echo (i18n("Employee")); ?></td>
                            <?php
                                $extraStyleOffDay = " background-color:#E7E7E7 !important;";
                                for($i=1;$i<=31;$i++) {
                                    $extraStyle = "";
                                    $htmlClass="linkData";
                                    $date = $yearRequest.'-'.$monthRequest.'-'.($i<10?"0":"").$i;
                                    $isOffDay=isOffDay($date);
                                    $dayOfWeek = date('l', strtotime($date));
                                    $dayOfWeekI18n = i18n($dayOfWeek);
                                    if ($i>$lastDayOfMonth) {
                                            $extraStyle = $extraStyleOffDay;
                                            $htmlClass="";
                                    } else {
                                        $extraStyle = ($isOffDay?$extraStyleOffDay:"");
                                    }
                                    $extraStyleDay[$i]=$extraStyle;
                                    $htmlClassDay[$i]=$htmlClass;
                                    if ($i>$lastDayOfMonth) {
                                        echo '<td colspan="2"></td>';
                                    } else {
                                        echo '<td colspan="2" style="width:20px;'.$extraStyle.'" class="assignHeader">'.$dayOfWeekI18n[0].'</td>';
                                    }
                                }
                            ?>                            
                        </tr>
                        <tr>
                            <?php
                                for($i=1;$i<=31;$i++) {
                                    if ($i>$lastDayOfMonth) {
                                        echo '<td colspan="2"></td>';
                                    } else {    
                                        echo '<td colspan="2" style="width:20px;'.$extraStyleDay[$i].'" class="assignHeader">'.$i.'</td>';
                                    }
                                }
                            ?>                                                        
                        </tr>
                        <!----------->
                        <!-- LINES -->
                        <!----------->                        
                            <?php
                                $employeeLeavesDay=null;
                                $lvTpHasWfStWithSubmitted=null;
                                foreach($employeeListRequest as $key => $name) {
                                    $employee = new Employee($key);
                                    $employees[$employee->id] = $employee;
                                    // Retrieve leaves of month for the employee
                                    $leavesDay=getLeavesInArrayDateForAPeriodAndAnEmployee($employee,$startDateRequest,$endDateRequest,$idStatusRequest,$idTypeRequest);
                                    $employeeLeavesDay[(int)$key]=$leavesDay;
                                    if(strlen($name)>=15){
                                      $showName="";
                                      if($employee->initials!=''){
                                        $showName=$employee->initials;
                                       }else{
                                        $words=mb_split(' ',str_replace(array('"',"'"), ' ',$employee->name));
                                        foreach ($words as $word) {
                                          $showName.=(mb_substr($word,0,1,'UTF-8'));
                                        }
                                      }
                                    }else{
                                      $showName=$name;
                                    }
                                    echo '<tr style="height:15px;">';
                                    echo '  <td class="assignHeader"><div title="'.$name.'">'.$showName.'</di></td>';
                                    for($i=1;$i<=31;$i++) {
                                        $tdStyleAM = 'border-right:0px;max-width:5px;min-width:5px;width:5px;text-align:center;';
                                        $tdStylePM = 'border-left:0px;max-width:5px;min-width:5px;width:5px;text-align:center;';
                                        $AMValue=""; $PMValue="";
                                        $AMextraStyle = "";
                                        $PMextraStyle = "";
                                        $date = $yearRequest."-".$monthRequest."-".($i<10?"0":"").$i;
                                        $isOffDay=isOffDay($date, $employee->idCalendarDefinition);
                                        $motif="";
                                        $idLeave=0;
                                        $idStatus=0;
                                        $idLeaveType=0;
                                        $submitted=0;
                                        $rejected=0;
                                        $accepted=0;
                                        $statusOutOfWorkflow=0;
                                        $statusSetLeaveChange=0;
                                        $requestDateTime=null;
                                        $idTypeAM = 0;
                                        $idTypePM = 0;
                                        $idStatusAM = 0;
                                        $idStatusPM = 0;
                                        if ($leavesDay!=null) { 
                                            if (array_key_exists($date, $leavesDay)) {
                                                $motif = ($isOffDay?"":$leavesDay[$date]['motif']);
                                                $submitted= ($isOffDay?0:$leavesDay[$date]['submitted']);
                                                $rejected= ($isOffDay?0:$leavesDay[$date]['rejected']);
                                                $accepted= ($isOffDay?0:$leavesDay[$date]['accepted']);
                                                $statusOutOfWorkflow= ($isOffDay?0:$leavesDay[$date]['statusOutOfWorkflow']);
                                                $statusSetLeaveChange= ($isOffDay?0:$leavesDay[$date]['statusSetLeaveChange']);
                                                $idStatus = ($isOffDay?0:$leavesDay[$date]['idStatus']);
                                                
                                                if(array_key_exists('idTypeAM', $leavesDay[$date])){
                                                  $idTypeAM = ($isOffDay?0:$leavesDay[$date]['idTypeAM']);
                                                }
                                                if(array_key_exists('idTypePM', $leavesDay[$date])){
                                                  $idTypePM = ($isOffDay?0:$leavesDay[$date]['idTypePM']);
                                                }
                                                
                                                if(array_key_exists('idStatusAM', $leavesDay[$date])){
                                                  $idStatusAM = ($isOffDay?0:$leavesDay[$date]['idStatusAM']);
                                                }
                                                if(array_key_exists('idStatusPM', $leavesDay[$date])){
                                                  $idStatusPM = ($isOffDay?0:$leavesDay[$date]['idStatusPM']);
                                                }
                                              
                                                $idLeave = ($isOffDay?0:$leavesDay[$date]['idLeave']);
                                                $idLeaveType = ($isOffDay?0:$leavesDay[$date]['idType']);
                                                if ($submitted==0 and $rejected==0 and $accepted==0) {
                                                    if ($lvTpHasWfStWithSubmitted==null or !array_key_exists($idLeaveType, $lvTpHasWfStWithSubmitted)) {
                                                        $tp = new LeaveType($idLeaveType);
                                                        $wf = new Workflow($tp->idWorkflow);
                                                        $lvTpHasWfStWithSubmitted[$idLeaveType] = ($wf->hasSetStatusOrLeave("setSubmittedLeave")==false?0:1);
                                                    }
                                                    if ($lvTpHasWfStWithSubmitted[$idLeaveType]==0) {
                                                        $submitted=1;
                                                    }
                                                } 
                                                $leaveStartDate = ($isOffDay?0:$leavesDay[$date]['startDate']);
                                                $leaveEndDate = ($isOffDay?0:$leavesDay[$date]['endDate']);
                                                $requestDateTime = ($isOffDay?null:$leavesDay[$date]['requestDateTime']);
                                                $bgColorType = $leaveTypesColor[$leavesDay[$date]['idType']];
                                                $colorType = oppositeColor($bgColorType);
                                                //gautier
                                                if($idTypePM != 0){
                                                  $bgColorTypePM = $leaveTypesColor[$idTypePM];
                                                  $colorTypePM = oppositeColor($bgColorTypePM);
                                                }
                                                if($idTypeAM != 0){
                                                  $bgColorTypeAM = $leaveTypesColor[$idTypeAM];
                                                  $colorTypeAM = oppositeColor($bgColorTypeAM);
                                                }
                                                //gautier
                                                if($idStatusAM!=0 or $idStatusPM!=0){
                                                  $colorStatusAM="#000000";
                                                  $colorStatusPM="#000000";
                                                  if($idStatusAM!=0){
                                                    $colorStatusAM = $leaveStatusColor[$idStatusAM];
                                                  }
                                                  if($idStatusPM!=0){
                                                    $colorStatusPM = $leaveStatusColor[$idStatusPM];
                                                  }
                                                  if ($isOffDay) {
                                                    $borderAM = 'border-right:0px;';
                                                    $borderPM = 'border-left:0px;';
                                                  } else {
                                                    if ($idStatusAM!=0 and $idStatusPM!=0) {
                                                      $borderAM = 'border: 3px solid '.$colorStatusAM.';border-right:0px;';
                                                      $borderPM = 'border: 3px solid '.$colorStatusPM.';border-left:0px;';
                                                    } else {
                                                      $borderAM = 'border: 3px solid '.$colorStatusAM.';';
                                                      $borderPM = 'border: 3px solid '.$colorStatusPM.';';
                                                    }
                                                  }
                                                }else{
                                                  if (array_key_exists($leavesDay[$date]['idStatus'], $leaveStatusColor)) {
                                                    $colorStatus = $leaveStatusColor[$leavesDay[$date]['idStatus']];
                                                  } else {
                                                    $colorStatus = "#000000";
                                                  }
                                                  if ($isOffDay) {
                                                    $borderAM = 'border-right:0px;';
                                                    $borderPM = 'border-left:0px;';
                                                  } else {
                                                    if ($leavesDay[$date]['AM'] and $leavesDay[$date]['PM']) {
                                                      $borderAM = 'border: 3px solid '.$colorStatus.';border-right:0px;';
                                                      $borderPM = 'border: 3px solid '.$colorStatus.';border-left:0px;';
                                                    } else {
                                                      $borderAM = 'border: 3px solid '.$colorStatus.';';
                                                      $borderPM = 'border: 3px solid '.$colorStatus.';';
                                                    }
                                                  }
                                                }
                                                
                                                $tdStyle = 'max-width:2px;min-width:2px;width:2px;text-align:center;';
                                                $theExtraStyle='background-color:'.$bgColorType.' !important; color:'.$colorType.' !important;';
                                                $AMValue = "";
                                                $AMextraStyle = "";
                                                $PMValue = "";
                                                $PMextraStyle = "";
                                                if ($leavesDay[$date]['AM']) {
                                                    if (array_key_exists($leavesDay[$date]['idStatus'], $statusListFirstLetter)) {
                                                        $AMValue = $statusListFirstLetter[$leavesDay[$date]['idStatus']];
                                                    } else {
                                                        $AMValue = $statusListFirstLetter[1];
                                                    }
                                                    $AMextraStyle = $theExtraStyle.$borderAM;
                                                    if($idTypeAM != 0){
                                                      $AMextraStyle='background-color:'.$bgColorTypeAM.' !important; color:'.$colorTypeAM.' !important;';
                                                      $AMextraStyle.=$borderAM;
                                                    }
                                                }
                                                if ($leavesDay[$date]['PM']) {
                                                    if (array_key_exists($leavesDay[$date]['idStatus'], $statusListFirstLetter)) {
                                                        $PMValue = $statusListFirstLetter[$leavesDay[$date]['idStatus']];
                                                    } else {
                                                        $PMValue = $statusListFirstLetter[1];                                                        
                                                    }
                                                    $PMextraStyle = $theExtraStyle.$borderPM;
                                                    if($idTypePM != 0){
                                                      $PMextraStyle='background-color:'.$bgColorTypePM.' !important; color:'.$colorTypePM.' !important;';
                                                      $PMextraStyle.=$borderPM;
                                                    }
                                                }
                                                $tdStyleAM .= $tdStyle;
                                                $tdStylePM .= $tdStyle;
                                            } else {
                                                $AMValue=""; $PMValue="";
                                                $AMextraStyle = "";
                                                $PMextraStyle = "";                                                
                                            }
                                        }
                                        if ($i>$lastDayOfMonth) {
                                            echo '<td colspan="2"></td>';
                                        } else {
                                            $title = "";
                                            $onClick="";
                                            $cursor="";
                                            $imgOrSpace="&nbsp;&nbsp;";
                                            if ($idLeave) {
                                                // Date with a leave
                                                if ($statusOutOfWorkflow==1 or $statusSetLeaveChange==1 ) {
                                                    // Out Of Workflow or unsynchronized => Goto element to maintain.
                                                    $imgOrSpace='<span style="color:red; font-weight:bold;">'.($statusOutOfWorkflow==1?"W":"S").'</span>';                                                    
                                                    $title .= i18n(($statusOutOfWorkflow==1?"maintainStatusOutOfWorkflow":"maintainStatusSetLeaveChange"));
                                                    $title .= "\r\r ".i18n("clickToAccessToLeave");
                                                    $onClick='onClick="gotoElement(\'Leave\','.htmlEncode($idLeave).');"';
                                                    $cursor = 'cursor:pointer;';
                                                } else {
                                                    if ($submitted==1) {
                                                        // Leave submitted => Can Valid or Cancel
                                                        $title .= i18n("clickToValidOrCanceled");
                                                        $title .= "\r".' Date = '.$leaveStartDate;
                                                        $title .= ' -- '.$leaveEndDate;
                                                        $onClick='onClick="getWorkflowStatusesOfLeaveType(null,'.$idLeaveType.');validOrCancelLeave('.$idLeave.',\''.$motif.'\');"';
                                                        $cursor = 'cursor:pointer;';
                                                    }
                                                    $theRequestDate = ($requestDateTime==""?"":substr($requestDateTime, 0,10));
                                                    if ($theRequestDate>$date) {
                                                        // Recording date > date
                                                        $title .= "\r".i18n("colRequestDateTime")." = ".$requestDateTime;
                                                        $margin=0;
                                                        if(($leavesDay[$date]['PM']!='1' and $leavesDay[$date]['AM']=='1') or ($leavesDay[$date]['PM']=='1' and $leavesDay[$date]['AM']!='1')){
                                                        	$margin=-5;
                                                        }
                                                        $imgOrSpace = '<img src="'.$imgDelay.'" style="width:14px; height:14px;margin-left:'.$margin.'px;">';
                                                    }
                                                    if ($accepted==1) {
                                                        $imgOrSpace='<span style="color:green; font-weight:bold;">A</span>';                                                    
                                                    }    
                                                    if ($rejected==1) {
                                                        // Leave Rejected => A reason to title
                                                        $title .= "\r".i18n("reason")." = ".$motif;
                                                        $imgOrSpace='<span style="color:black; font-weight:bold;">R</span>';                                                    
                                                    }    
                                                }
                                            }    
                                            echo '<td style="'.$tdStyleAM.$AMextraStyle.($isOffDay?$extraStyleOffDay:"").'" class="'.$htmlClassDay[$i].'">';
                                            if ($idLeave and $AMValue!="") {
                                                echo '  <a  style="width:10px;height:10px;'.$cursor.'" ';
                                                echo '      title="'.$title.'"';
                                                echo '    '.$onClick.'>';
                                                echo $imgOrSpace;
                                                echo '</a>';
                                            }                                            
                                            echo '</td>';
                                            
                                            echo '<td style="'.$tdStylePM.$PMextraStyle.($isOffDay?$extraStyleOffDay:"").'" class="'.$htmlClassDay[$i].'">';
                                            if ($idLeave and $PMValue!="") {
                                                echo '  <a  style="width:10px;height:10px;'.$cursor.'" ';
                                                echo '      title="'.$title.'"';
                                                echo '    '.$onClick.'>';
                                                if ($AMValue=="") {echo $imgOrSpace;} else {echo '&nbsp;&nbsp;';}
                                                echo '</a>';
                                            }
                                            echo '</td>';
                                        }
                                    }
                                    echo '</tr>';
                                }
                            ?>
                    </table>
                </td>    
                <td style="width:39%;">
                    <table style="width:100%;">
                        <!------------------->
                        <!-- THE SYNTHESIS -->
                        <!------------------->
                        <tr>
                            <th style="text-align:center;vertical-align:top;width:100%;max-height:15px;">
                                <!--------------------->
                                <!-- SYNTHESIS TITLE -->
                                <!--------------------->
                                &nbsp;
                            </th>
                        </tr>
                        <tr style="height:39px;"><td><b><?php echo (i18n("synthesis")); ?></b></td></tr>
                        <tr>
                            <td style="text-align:center;vertical-align:top;width:100%;">
                                <!----------------------->
                                <!-- SYNTHESIS CONTENT -->
                                <!----------------------->
                                <table style="width:100%;height:90%;border:1px solid">
                                    <!---------------------->
                                    <!-- SYNTHESIS HEADER -->
                                    <!---------------------->                                        
                                    <tr>
                                        <!-- Column EMPLOYEE -->
                                        <th rowspan="2" style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("Employee")); ?>
                                        </th>
                                        <!-- Column TOTAL -->
                                        <?php
                                            if ($idTypeRequest==0) {
                                        ?>
                                        <td colspan="2" style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("sum")); ?>                                            
                                        </td>
                                            <?php } ?>
                                        <!-- Columns LEAVE TYPE -->
                                        <?php
                                            if ($idTypeRequest==0) {
                                                $nbTypes=1;
                                                $colorStyleType[]="";
                                                foreach($leaveTypes as $leaveType) {
                                                    $nbTypes++;
                                                    $bgColorType='background-color:'.$leaveType->color.' !important;';
                                                    $colorType = 'color:'.oppositeColor($bgColorType).' !important;';
                                                    $colorStyle = $bgColorType.$colorType;
                                                    $colorStyleType[]=$colorStyle;
                                                    echo '<td colspan="2" style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader">';
                                                    echo $leaveType->name;
                                                    echo '</td>';                        
                                                }
                                            } else {
                                                $bgColorType='background-color:'.$leaveTypeRequestColor.' !important;';
                                                $colorType = 'color:'.oppositeColor($bgColorType).' !important;';
                                                $colorStyle = $bgColorType.$colorType;
                                                $colorStyleType[]=$colorStyle;
                                                echo '<td colspan="2" style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader">';
                                                echo $leaveTypeRequestName;
                                                echo '</td>';                        
                                            }
                                        ?>                                        
                                    </tr>
                                    <tr>
                                        <!-- Columns Taken - Left -->
                                        <?php
                                            if ($idTypeRequest==0) {
                                                $iE = $nbTypes;
                                            } else {
                                                $iE = 1;
                                            }    
                                            for ($i=0;$i<$iE;$i++) {
                                                echo '<td style="text-align:center;vertical-align:middle;'.$colorStyleType[$i].'" class="assignHeader">';
                                                echo (i18n('taken'));
                                                echo '</td>';
                                                echo '<td style="text-align:center;vertical-align:middle;'.$colorStyleType[$i].'" class="assignHeader">';
                                                echo (i18n('colLeft'));
                                                echo '</td>';
                                            }
                                        ?>
                                    </tr>
                                    <!--------------------->
                                    <!-- SYNTHESIS LINES -->
                                    <!--------------------->                                        
                                        <?php
                                            $totalTakenTotal=0;
                                            $totalLeftTotal=null;
                                            foreach($leaveTypes as $leaveType) {
                                                $takenTotalType[$leaveType->id]=null;
                                                $leftTotalType[$leaveType->id]=null;                                                
                                            }
                                            foreach($employeeListRequest as $id => $name) {
                                                $emp = $employees[$id];
                                                $leftByType = $emp->getLeftLeavesByLeaveType($idTypeRequest);
                                                $totalLeft = null;
                                                foreach($leftByType as $key => $left) {
                                                    if ($left!=null) {$totalLeft += $left;}
                                                }
                                                if ($totalLeft!=null) {
                                                    $totalLeftTotal += $totalLeft;
                                                }
                                                
                                                $totalTaken=0;
                                                $theLeavesDays = $employeeLeavesDay[$id];
                                                if ($theLeavesDays!=null) {
                                                    foreach($theLeavesDays as $date) {
                                                        if ($date['idStatus']!=9) {
                                                            $totalTaken += $date['quantity'];
                                                        }
                                                    }
                                                }
                                                $totalTakenTotal += $totalTaken;
                                                echo '<tr>';
                                                // Column 'Employee'
                                                echo '  <td style="text-align:center;vertical-align:middle;" class="assignHeader">';
                                                echo $name;
                                                echo '  </td>';
                                                if ($idTypeRequest==0) {
                                                    // Column 'Total' Taken - Left
                                                    // Taken
                                                    echo '  <td style="text-align:center;vertical-align:middle;" class="assignHeader">';
                                                    echo $totalTaken;
                                                    echo '  </td>';
                                                    // Left
                                                    echo '  <td style="text-align:center;vertical-align:middle;" class="assignHeader">';
                                                    echo ($totalLeft===null?"-":$totalLeft);
                                                    echo '  </td>';
                                                    // Columns LeaveType Taken - Left
                                                    foreach($leaveTypes as $leaveType) {
                                                        $taken=0;
                                                        if ($theLeavesDays!=null) {
                                                            foreach($theLeavesDays as $date) {
                                                                if ($date['idStatus']!=9) {
                                                                    $taken += ($date['idType']==$leaveType->id?$date['quantity']:0);
                                                                }
                                                            }
                                                        }
                                                        $takenTotalType[$leaveType->id] += $taken;
                                                        if (array_key_exists($leaveType->id, $leftByType)) {
                                                            $left=$leftByType[$leaveType->id];
                                                        } else { $left=null; }
                                                        if ($left!==null) {
                                                            $leftTotalType[$leaveType->id] += $left;
                                                        }
                                                        $bgColorType='background-color:'.$leaveType->color.' !important;';
                                                        $colorType = 'color:'.oppositeColor($bgColorType).' !important;';
                                                        $colorStyle = $bgColorType.$colorType;                                                        
                                                        // Taken 
                                                        echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader">';
                                                        echo $taken;
                                                        echo '  </td>';                                                    
                                                        // Left 
                                                        echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader">';
                                                        echo ($left===null?"-":$left);
                                                        echo '  </td>';
                                                    }
                                                } else {
                                                    $left=null;
                                                    if (array_key_exists($idTypeRequest, $leftByType)) {
                                                        $left = $leftByType[$idTypeRequest];
                                                    }
                                                    if ($left!==null) {
                                                        $leftTotalType[$idTypeRequest] += $left;
                                                    }
                                                    $takenTotalType[$idTypeRequest] += $totalTaken;
                                                    // Column LeaveType Requested Taken 
                                                    echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyleType[0].'" class="assignHeader">';
                                                    echo $totalTaken;
                                                    echo '  </td>';
                                                    // Column LeaveType Requested Left 
                                                    echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyleType[0].'" class="assignHeader">';
                                                    echo ($left==null?"-":$left);
                                                    echo '  </td>';
                                                }
                                                echo '</tr>';
                                            }
                                        ?>
                                    <!----------->
                                    <!-- TOTAL -->
                                    <!----------->
                                    <tr style="height:15px;">
                                        <td class="assignHeader"><b><?php echo (i18n("sum"));?></b></td>
                                    <?php
                                        if ($idTypeRequest==0) {
                                    ?>
                                        <td style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <b><?php echo $totalTakenTotal; ?></b>
                                        </td>
                                        <td style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <b><?php echo ($totalLeftTotal===null?"-":$totalLeftTotal); ?></b>
                                        </td>
                                    <?php
                                        }
                                        foreach($leaveTypes as $leaveType) {
                                            if ($idTypeRequest!=0 and $idTypeRequest!=$leaveType->id) { continue; }
                                            $bgColorType='background-color:'.$leaveType->color.' !important;';
                                            $colorType = 'color:'.oppositeColor($bgColorType).' !important;';
                                            $colorStyle = $bgColorType.$colorType;                                                        
                                            // Taken 
                                            echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader"><b>';
                                            echo $takenTotalType[$leaveType->id];
                                            echo '  </b></td>';                                                    
                                            // Left 
                                            echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader"><b>';
                                            echo ($leftTotalType[$leaveType->id]===null?"-":$leftTotalType[$leaveType->id]);
                                            echo '  </b></td>';                                            
                                        }
                                    ?>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!----------------------------->
                        <!-- THE LEAVES TO PROCESSED -->
                        <!----------------------------->
                        <tr style="height:50%;">
                        <tr style="height:10px;"><td>&nbsp;</td></tr>                            
                            <th style="text-align:center;vertical-align:top;width:100%;height:20px;max-height:20px;">
                                <!----------->
                                <!-- TO PROCESSED - TITLE -->
                                <!----------->
                                <div style="height:20px"><b><?php echo (i18n("leavesToProcessed")); ?></b></div>
                                <table style="width:100%;height:90%;border:1px solid">
                                    <!------------------------->
                                    <!-- TO PROCESSED HEADER -->
                                    <!------------------------->                                        
                                    <tr>
                                        <!-- Column EMPLOYEE -->
                                        <th colspan="2" style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("colIdEmployee")); ?>
                                        </th>
                                        <!-- Column TYPE -->
                                        <th style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("colType")); ?>
                                        </th>
                                        <!-- Column StartDate & AMPM -->
                                        <th style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("startDate")); ?>
                                        </th>
                                        <!-- Column EndDate & AMPM -->
                                        <th style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("endDate")); ?>
                                        </th>
                                        <!-- Column Nb Days -->
                                        <th style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("nbDays")); ?>
                                        </th>
                                        <!-- Column Request Date Time -->
                                        <th style="text-align:center;vertical-align:middle;" class="assignHeader">
                                            <?php echo (i18n("requestedDate")); ?>
                                        </th>                                        
                                    </tr>
                                    <?php
                                        $cursor = 'cursor:pointer;';
                                        $lvTpHasWfStWithSubmitted==null;                                                                                                                     
                                        foreach($employees as $employee) {
                                            $lvList=$employee->getEmployeeLeavesBetweenDateToProcess($startDateRequest,$endDateRequest, $idTypeRequest, "startDate ASC",-1,-1,-1);
                                            foreach ($lvList as $leave) {                                                
                                                if ($lvTpHasWfStWithSubmitted==null or !array_key_exists($leave->idLeaveType, $lvTpHasWfStWithSubmitted)) {
                                                    $tp = new LeaveType($leave->idLeaveType);
                                                    $wf = new Workflow($tp->idWorkflow);
                                                    $lvTpHasWfStWithSubmitted[$leave->idLeaveType] = ($wf->hasSetStatusOrLeave("setSubmittedLeave")==false?0:1);
                                                }
                                                // To maintain => Action => GoTo
                                                if ($leave->statusOutOfWorkflow==1 or $leave->statusSetLeaveChange==1) {
                                                    $onClick='onClick="gotoElement(\'Leave\','.htmlEncode($leave->id).');"';  
                                                    echo '<tr>';                                            
                                                    // Action = To Maintain
                                                    echo '  <td>';
                                                    $title = i18n("maintainStatusOutOfWorkflow")."\r\r ".i18n("clickToAccessToLeave");
                                                    if ($leave->statusOutOfWorkflow==1) {
                                                        $title = i18n("maintainStatusOutOfWorkflow")."\r\r ".i18n("clickToAccessToLeave");
                                                        echo '  <a  title="'.$title.'" style="width:10px;height:10px;'.$cursor.'"'.$onClick.'>';
                                                        echo '      <span class="leaveStatus" style="background-color:red; color:white;">';
                                                        echo '              &nbsp;W&nbsp;';
                                                        echo '      </span>';
                                                        echo '  </a>';                                                        
                                                    }
                                                    if ($leave->statusSetLeaveChange==1) {
                                                        $title = i18n("maintainStatusSetLeaveChange")."\r\r ".i18n("clickToAccessToLeave");
                                                        echo '  <a  title="'.i18n("maintainStatusSetLeaveChange").'" style="width:10px;height:10px;'.$cursor.'"'.$onClick.'>';
                                                        echo '      <span class="leaveStatus" style="background-color:red; color:white;">';
                                                        echo '              &nbsp;S&nbsp;';
                                                        echo '      </span>';
                                                        echo '  </a>';                                                        
                                                    }
                                                    echo '  </td>';                                                                                                            
                                                } else {
                                                    $submitted = ($lvTpHasWfStWithSubmitted[$leave->idLeaveType]==0?1:0);
                                                    if (($leave->submitted==0 and $submitted==0) or 
                                                         $leave->rejected==1 or $leave->accepted==1) {
                                                        continue;
                                                    } else {
                                                        echo '<tr>';                                            
                                                        // Action = Validated - Rejected
                                                        echo '  <td>';
                                                        foreach($listStatus as $key=>$status) {
                                                            if ($status->setRejectedLeave==1 or $status->setAcceptedLeave==1) {
                                                                $onClick='onClick="getWorkflowStatusesOfLeaveType(null,'.$leave->idLeaveType.');validOrCancelLeave('.$leave->id.',\''.$leave->comment.'\',\''.($status->setRejectedLeave==1?'CAN':'VAL').'\');"';  
                                                                $textColor = oppositeColor($status->color);
                                                                $title = i18n(($status->setRejectedLeave==1?"colRejected":"colAccepted"));
                                                                echo '  <a  title="'.$title.'" style="width:10px;height:10px;'.$cursor.'"'.$onClick.'>';
                                                                echo '      <span class="leaveStatus" style="background-color:'.$status->color.'; color:'.$textColor.';">';
                                                                echo '              &nbsp;'.substr(strtoupper($status->name),0,1).'&nbsp;';
                                                                echo '      </span>';
                                                                echo '  </a>';
                                                            }
                                                        }                                                
                                                        echo '  </td>';                                                        
                                                    }
                                                }
                                                // Employee Name
                                                echo '  <td style="text-align:center;vertical-align:middle;" class="assignHeader">';
                                                echo $employee->name;
                                                echo '  </td>';
                                                // Type                                                
                                                $bgColorType='background-color:'.$leaveTypesColor[$leave->idLeaveType].' !important;';
                                                $colorType = 'color:'.oppositeColor($bgColorType).' !important;';
                                                $colorStyle = $bgColorType.$colorType;
                                                foreach($leaveTypes as $lvt) {
                                                    if ($lvt->id == $leave->idLeaveType) {
                                                        $leaveTypeName = $lvt->name;
                                                        break;
                                                    }
                                                }
                                                echo '  <td style="text-align:center;vertical-align:middle;'.$colorStyle.'" class="assignHeader">';
                                                echo $leaveTypeName;
                                                echo '  </td>';                        
                                                // Column StartDate & AMPM
                                                echo '  <td style="text-align:center;vertical-align:middle;" class="linkData">';
                                                echo htmlFormatDate($leave->startDate). " ". ($leave->startAMPM=="AM"?i18n("morning"):i18n("afternoon"));
                                                echo '  </td>';                                                    
                                                // Column EndDate & AMPM
                                                echo '  <td style="text-align:center;vertical-align:middle;" class="linkData">';
                                                echo htmlFormatDate($leave->endDate). " ". ($leave->endAMPM=="AM"?i18n("morning"):i18n("afternoon"));
                                                echo '  </td>';                                                    
                                                // Column Nb Days
                                                echo '  <td style="text-align:center;vertical-align:middle;" class="linkData">';
                                                echo $leave->nbDays;
                                                echo '  </td>';                                                    
                                                // Column Request Date Time
                                                echo '  <td style="text-align:center;vertical-align:middle;" class="linkData">';
                                                echo htmlFormatDate($leave->requestDateTime);
                                                echo '  </td>';                                                    
                                                echo '</tr>';
                                            }
                                        }
                                    ?>
                                </table>
                            </th>
                        </tr>
                        
                        
                        
                    </table>
                </td>
                <td width="1%">
                </td>    
            </tr>
        </table>    
    </div>
</div>

<!---------------------------------------------------------------->
<!-- THE DIALOG BOX TO VALID OR CANCEL A LEAVE IN THE DASHBOARD -->
<!---------------------------------------------------------------->                    
<div id="leaveValidCancelPopup" 
     data-dojo-type="dijit.Dialog" 
     title="<?php echo i18n('validOrCancelLeave'); ?>">                        
    <table>
        <tr>
            <td colspan="2">
                <input type='hidden' data-dojo-type="dijit/form/TextBox"  id='popupLeaveId' />
                <label for='popupLeaveStatus'><?php echo i18n("colIdStatus");?> : </label>
                <select id='popupLeaveStatus' dojoType='dijit.form.Select' required>
                    <?php
                    foreach($listStatus as $status) {
                        if ($status->id==1) {continue;}
                        echo '<option value="' . $status->id . '"';
                         echo '><span >'. htmlEncode($status->name) . '</span></option>';
                    }
                    ?>
                </select>
            </td>
        </tr>    
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
            <td colspan="2">
                <label for="popupReason"><?php echo i18n("reason"); ?> : </label>
                <input type="text" 
                       id="popupReason" 
                       data-dojo-type="dijit/form/TextBox" 
                       style="width: 40em;" 
                       data-dojo-props="maxLength:255"/>
            </td>
        </tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
            <td>
                <button data-dojo-type="dijit/form/Button"
                        id="validateButtonDashboardPopup"
                        data-dojo-id="validateButtonDashboardPopup" 
                        onclick="saveValidOrCancelLeaveStatus();"
                        type="button">
                    <?php echo i18n("buttonValid"); ?>
                </button>
            </td>
            <td style="text-align:right;">
                <button data-dojo-type="dijit/form/Button" 
                        data-dojo-id="cancelButtonDashboardPopup"
                        onclick="dijit.byId('leaveValidCancelPopup').hide();"
                        type="button">
                    <?php echo i18n("buttonCancel"); ?>
                </button>
            </td>  
        </tr>
    </table>
</div>

<?php
  //include_once "../view/leaveCalendarPopupErrorAndResult.php";
