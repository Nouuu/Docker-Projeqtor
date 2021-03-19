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
/**
 * the dialog for the table of EmployeesManaged objects in EmployeeManagerMain.php
 */

// LEAVE SYSTEM
include_once ("../tool/projeqtor.php");

//two modes are defined for the creation of the form in the popup, add and edit, each one permitting to differenciate a call from the action of creation or the action of editing
if(! array_key_exists ( 'addMode', $_REQUEST ) || ! array_key_exists ( 'editMode', $_REQUEST )){
    throwError ( 'Parameters addMode/updateMode not found in REQUEST' );
}
$addMode= $_REQUEST['addMode'];
$editMode= $_REQUEST['editMode'];
Security::checkValidBoolean($addMode);
Security::checkValidBoolean($editMode);

//addMode and editMode cannot be true (or false) at the same time
if( !($addMode==='true' && $editMode==='false') && !($addMode==='false' && $editMode==='true') ){  
    throwError ( 'A wrong mode was given' );
}

if(! array_key_exists ( 'idEmployeeManager', $_REQUEST )){
    throwError ( 'Parameter idEmployeeManager not found in REQUEST' );
}
$idEmployeeManager = $_REQUEST ['idEmployeeManager'];
Security::checkValidId ( $idEmployeeManager );

//if $addMode, set all the attributes to null, except idEmployeeManager which stay the same for the two modes
if($addMode==="true"){
    $id=null;
    $idEmployee=null;
    $startDate=null;
    $endDate=null;
    $idle=null;
}

//if $editMode, test and set all the variables with the informations passed in the request
if($editMode==="true"){
    if(! array_key_exists ( 'id', $_REQUEST )){
        throwError ( 'Parameter id of EmployeesManaged not found in REQUEST' );
    }
    $id = $_REQUEST ['id'];
    Security::checkValidId ( $id );
    
    if(! array_key_exists ( 'idEmployee', $_REQUEST )){
        throwError ( 'Parameter idEmployee not found in REQUEST' );
    }
    $idEmployee = $_REQUEST ['idEmployee'];
    Security::checkValidId ( $idEmployee );
    
    if(! array_key_exists ( 'startDate', $_REQUEST )){
        throwError ( 'Parameter startDate not found in REQUEST' );
    }
    $startDate = $_REQUEST ['startDate'];
    
    if(! array_key_exists ( 'endDate', $_REQUEST )){
        throwError ( 'Parameter endDate not found in REQUEST' );
    }
    $endDate = $_REQUEST ['endDate'];
    
    if(! array_key_exists ( 'idle', $_REQUEST )){
        throwError ( 'Parameter idle not found in REQUEST' );
    }
    $idle = $_REQUEST ['idle'];
}

?>

<table>
        <tr>
            <td>
                <form dojoType="dijit.form.Form" id='employeesManagedForm'
                    name='employeesManagedForm' onSubmit="return false;">

                    <input id="idEmployeesManaged" name="idEmployeesManaged" type="hidden" 
			value="<?php echo $id;?>" />
                                           
                    <input id="idEmployeeManagerEmployeesManaged" name="idEmployeeManagerEmployeesManaged" type="hidden"
			value="<?php echo $idEmployeeManager;?>" />
                    
                    <table>                                                                        
                        <tr>
                            <td class="dialogLabel" >
                                <label for="idEmployeeEmployeesManaged"><?php echo i18n('Employee');?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td>
                                <select id="idEmployeeEmployeesManaged"  dojoType="dijit.form.FilteringSelect"
                                    name="idEmployeeEmployeesManaged" class="input required" value="<?php echo $idEmployee;?>" required>
                                        <?php
                                            $employeeList = getUserVisibleResourcesList(true, "List",'', false,true,false,true);
                                            foreach($employeeList as $key=>$val) {
                                                echo '<option value="' . $key . '"';
                                                if ( $key==$idEmployee ) { 
                                                    echo ' SELECTED ';
                                                }
                                                echo '><span >'. htmlEncode($val) . '</span></option>';
                                            }
                                        ?>
                                </select>    
                            </td>
                        </tr>
                        <tr>
                            <td class="dialogLabel" >
                              <label for="startDateEmployeesManaged" ><?php echo i18n("colStartDate");?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td>
                                <input  id="startDateEmployeesManaged" name="startDateEmployeesManaged" 
                                        value="<?php echo $startDate; ?>"  
                                        dojoType="dijit.form.DateTextBox" 
                                        constraints="{datePattern:browserLocaleDateFormatJs}"
                                        onChange=" var end=dijit.byId('endDateEmployeesManaged');end.set('dropDownDefaultValue',this.value);
                                                   var start = dijit.byId('startDateEmployeesManaged').get('value');end.constraints.min=start;"
                                        style="width:100px" />
                            </td>
                        </tr>
                        <tr>
                            <td class="dialogLabel" >
                              <label for="endDateEmployeesManaged" ><?php echo i18n("colEndDate");?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td>
                                <input  id="endDateEmployeesManaged" name="endDateEmployeesManaged" 
                                        value="<?php echo $endDate; ?>"  
                                        dojoType="dijit.form.DateTextBox" 
                                        constraints="{datePattern:browserLocaleDateFormatJs}"
                                        style="width:100px" />
                            </td>
                        </tr>
                        <tr>
                            <td class="dialogLabel" >
                                <label for="idleEmployeesManaged"><?php echo i18n('colIdle');?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td>
                                <input id="idleEmployeesManaged" name="idleEmployeesManaged"
                                     dojoType="dijit.form.CheckBox"
                                     type="checkbox"
                                     <?php echo (($idle==1)?' checked ':'');?>
                                />
                            </td>    
                        </tr>
                    </table>                    
                </form>
            </td>
        </tr>
        <tr>
            <td align="center"><input type="hidden" id="employeesManagedAction">
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="button" onclick="dijit.byId('dialogEmployeesManaged').hide();">
                    <?php echo i18n("buttonCancel");?>
                </button>
		<button class="mediumTextButton" dojoType="dijit.form.Button"
                    type="submit" id="dialogEmployeesManagedSubmit"
                    onclick="protectDblClick(this);saveEmployeesManaged();return false;">
                    <?php echo i18n("buttonOK");?>
                </button>
            </td>
        </tr>
</table>