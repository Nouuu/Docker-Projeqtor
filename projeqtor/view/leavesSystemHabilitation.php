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
 * Screen management of Leaves System Habilitations
 * 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/leavesSystemHabilitation.php');
  $user=getSessionUser();
  $idUser = $user->id;
  $profile=new Profile($user->idProfile);
  $manager = new EmployeeManager($idUser);
  $isManager = $manager->isManager();
  $print=false;
  if (isset($_REQUEST['print'])) {
  	$print=true;
  }
  
?>

<input type="hidden" name="objectClassManual" id="objectClassManual" value="leavesSystemHabilitation" />
<div class="container" dojoType="dijit.layout.BorderContainer">
    <!--------------------------->
    <!-- TITLE AND BUTTON SAVE -->
    <!--------------------------->
    <div id="leavesSystemHabilitationButtonDiv" class="listTitle" style="z-index:3;overflow:visible"
         dojoType="dijit.layout.ContentPane" region="top">
        <table width="100%">
            <tr height="100%" style="vertical-align: middle;">
                <td width="50px" align="center"><?php echo formatIcon("LeavesSystemHabilitation", 32, null, true);?></td>
                <td><span class="title"><?php echo i18n('menuLeavesSystemHabilitation');?>&nbsp;</span></td>
                <td width="10px">&nbsp;</td>
                <td width="50px">
                    <button id="saveParameterButton" dojoType="dijit.form.Button"
                            showlabel="false"
                            title="<?php echo i18n('buttonSave');?>"
                            iconClass="dijitButtonIcon dijitButtonIconSave" class="detailButton">
                        <script type="dojo/connect" event="onClick" args="evt">
                            submitForm("../tool/saveLeavesSystemHabilitation.php","resultDivMain", "LeavesSystemHabilitationForm", true);
                        </script>
                    </button>
                    <div dojoType="dijit.Tooltip" connectId="saveParameterButton"><?php echo i18n("saveLeavesSystemHabilitation")?></div>
                </td>
                <td style="position:relative;"></td>
            </tr>
        </table>
    </div>
    <!---------->
    <!-- FORM -->
    <!---------->
    <div id="formDiv" dojoType="dijit.layout.ContentPane" region="center"
         style="overflow-y: auto; overflow-x: hidden;">
        <form dojoType="dijit.form.Form" id="LeavesSystemHabilitationForm" jsId="LeavesSystemHabilitationForm"
              name="LeavesSystemHabilitationForm" encType="multipart/form-data" action="" method="">
            <!---------->
            <!-- LIST -->
            <!---------->
            <?php
                $habilitationList = getLeavesSystemHabilitationSortByOrderMenu();
                $menusList = getLeavesSystemMenu();
                foreach ($habilitationList as $key => $hab) {
                    $class = substr($hab->menuName,4);
                    if (SqlElement::class_exists($class)) {
                        $hasIdUser = property_exists($class, "idUser");
                        $hasIdEmployee = property_exists($class, "idEmployee");
                    } else {
                        $hasIdUser = false;
                        $hasIdEmployee = false;
                    }
                    $pass = false;
                    $find = false;
                    foreach($menusList as $menu) {
                        // No real habilitation for menus that are'nt linked to object or item
                        if ($hab->menuName == $menu->name) {
                            if ($menu->type == "menu") {
                                $pass = true;                                
                                break;
                            }
                            // Don't treat habilitation without menu (In theory, can't coming)
                            $find = true;
                        }
                    }                    
                    if (!$pass and $find) {
            ?>
            <!-- Habilitations Item -->
            <div id="title<?php echo $hab->menuName; ?>" 
                 data-dojo-type="dijit/TitlePane" 
                 data-dojo-props='open:true, title: "<?php echo htmlEncode(i18n($hab->menuName));?>"'>
                <table style="width:96%; margin-left:2%; text-align:center;">
                    <!-- HEADER -->
                    <tr style="height: 20px;">
                        <td class="tabLabel" style="text-align:center; width: 25%;"><?php echo i18n("colIdProfile");?></td>
                        <td class="tabLabel" style="text-align:center; width: 15%;"><?php echo i18n("viewAccess");?></td>
                        <?php if (  $hab->menuName == 'menuLeaveCalendar' or 
                                    $hab->menuName == 'menuDashboardEmployeeManager') {?>
                        <td style="text-align:center; width: 15%;"></td>
                        <?php } else { ?>
                        <td class="tabLabel" style="text-align:center; width: 15%;"><?php echo i18n("readAccess");?></td>
                        <?php } ?>
                        <?php if (  $hab->menuName == 'menuEmployee' or 
                                    $hab->menuName == 'menuEmployeeManager' or 
                                    $hab->menuName == 'menuLeaveCalendar' or 
                                    $hab->menuName == 'menuDashboardEmployeeManager') {?>
                        <td style="text-align:center; width: 15%;"></td>
                        <?php } else { ?>
                        <td class="tabLabel" style="text-align:center; width: 15%;"><?php echo i18n("createAccess");?></td>
                        <?php } ?>
                        <?php if (  $hab->menuName == 'menuLeaveCalendar' or 
                                    $hab->menuName == 'menuDashboardEmployeeManager') {?>
                        <td style="text-align:center; width: 15%;"></td>
                        <?php } else { ?>
                        <td class="tabLabel" style="text-align:center; width: 15%;"><?php echo i18n("updateAccess");?></td>
                        <?php } ?>
                        <?php if (  $hab->menuName == 'menuEmployee' or 
                                    $hab->menuName == 'menuEmployeeManager' or 
                                    $hab->menuName == 'menuLeaveCalendar' or 
                                    $hab->menuName == 'menuDashboardEmployeeManager') {?>
                        <td style="text-align:center; width: 15%;"></td>
                        <?php } else { ?>
                        <td class="tabLabel" style="text-align:center; width: 15%;"><?php echo i18n("deleteAccess");?></td>                    
                        <?php } ?>
                    </tr>
                        <!-- Leaves Admin Habilitation -->
                    <tr style="text-align:center;">
                        <td><?php echo i18n("leavesSystemAdmin");?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"A",$hab,'viewAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"A",$hab,'readAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"A",$hab,'createAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"A",$hab,'updateAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"A",$hab,'deleteAccess');?></td>
                    </tr>
                        <!-- Manager Habilitation -->
                    <tr style="text-align:center;">
                        <td><?php echo i18n("leaveManager");?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"M",$hab,'viewAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"M",$hab,'readAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"M",$hab,'createAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"M",$hab,'updateAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"M",$hab,'deleteAccess');?></td>
                    </tr>
                        <!-- Manager of Employee Habilitation -->
                    <tr style="text-align:center;">
                        <td><?php echo i18n("managerOfEmployee");?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"m",$hab,'viewAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"m",$hab,'readAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"m",$hab,'createAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"m",$hab,'updateAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"m",$hab,'deleteAccess');?></td>
                    </tr>
                        <!-- Employee Habilitation -->
                    <tr style="text-align:center;">
                        <td><?php echo i18n("Employee");?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"E",$hab,'viewAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"E",$hab,'readAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"E",$hab,'createAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"E",$hab,'updateAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"E",$hab,'deleteAccess');?></td>
                    </tr>
                        <!-- Creator Habilitation -->
                    <?php 
                        if ($hasIdUser) { 
                    ?>    
                    <tr style="text-align:center;">
                        <td><?php echo i18n("creator");?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"O",$hab,'viewAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"O",$hab,'readAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"O",$hab,'createAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"O",$hab,'updateAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"O",$hab,'deleteAccess');?></td>
                    </tr>
                    <?php } ?>
                        <!-- Self Habilitation -->
                    <?php 
                        if ($hasIdEmployee or $class=="Employee") { 
                    ?>    
                    <tr style="text-align:center;">
                        <td><?php echo i18n("self");?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"S",$hab,'viewAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"S",$hab,'readAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"S",$hab,'createAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"S",$hab,'updateAccess');?></td>
                        <td><?php echo drawHabililitation($hab->menuName,"S",$hab,'deleteAccess');?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>                        
            <?php
                    }
                }
            ?>
        </form>
    </div>    
</div>

<?php
    function drawHabililitation($menuName, $type, $item, $vCRUD) {
        $habilitation = $item->$vCRUD;
        $listValues=array('yes','no');
        $displayValues=array('yes' => i18n('displayYes'), 'no' => i18n('displayNo'));
        if (strpos($habilitation,$type)===false) {
            $result ='no'; 
        } else {
            $result ='yes';            
        }
        switch ($type) {
            case 'A' :
                $habilitationType = i18n('leavesSystemAdmin');
                break;
            case 'M' :
                $habilitationType = i18n("Manager");
                break;
            case 'E' :
                $habilitationType = i18n("Employee");
                break;
            case 'm' :
                $habilitationType = i18n('managerOfEmployee');
                break;
            case 'O' :
                $habilitationType = i18n('creator');
                break;
            case 'S' :
                $habilitationType = i18n('self');
                break;
        }
        
        $readonly = false;
        // Force "A" (Admin) for all types (View - Read - Create - Update - Delete) if menu is LeavesSystemHabilitation
        if ($menuName=="menuLeavesSystemHabilitation" and $type=="A") {
            $result='yes';
            $readonly = true;
        }
        
        $hide = false;
        // Special case 'menuEmployee' and 'menuEmployeeManager : In fact is't Resource
        // == > Can't create and delete (done throw Resource)
        if (($menuName=="menuEmployee" or $menuName=="menuEmployeeManager") and 
            ($vCRUD=="createAccess" or $vCRUD=="deleteAccess")) {
            $hide=true;
        }
        
        // Special case 'menuLeaveCalendar', 'menuDashboardEmployeeManager' : In fact don't represent a Leave System Class 
        // => Only access to menu is needed
        if (($menuName=="menuLeaveCalendar" or $menuName=="menuDashboardEmployeeManager") and $vCRUD!="viewAccess") {
            $hide=true;
        }
                
        $checked = ($result=='yes' ? 'checked' : '');
        $htmlId = $menuName.'_'.$type.'_'.$vCRUD.'_'.$item->id;
        $htmlName = $menuName.'_'.$type.'_'.$vCRUD.'_'.$item->id;
        if ($hide) {
            echo '<div style="display:none"';
        }
        echo '<input '.($readonly?"readOnly":""). ' dojoType="dijit.form.CheckBox" type="checkbox" ' . $checked . ' id="' . $htmlId . '" name="' . $htmlName . '" />'; 
        if ($hide) {echo '</div>';}
    }
?>