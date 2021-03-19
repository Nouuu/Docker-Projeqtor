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

include_once("../tool/projeqtor.php");

$idProjectType=null;
if (isset($_REQUEST['idProjectType'])) {
  $idProjectType=$_REQUEST['idProjectType'];
  Security::checkValidId($idProjectType);
}
$idProject=null;
if (isset($_REQUEST['idProject'])) {
  $idProject=$_REQUEST['idProject'];
  Security::checkValidId($idProject);
}
$idProfile=null;
if (isset($_REQUEST['idProfile'])) {
  $idProfile=$_REQUEST['idProfile'];
  Security::checkValidId($idProfile);
}
if (!$idProjectType && !$idProject && !$idProfile) {
  $error="idProjectType or idProject or idProfile is mandatory in request";
  errorLog($error);
  echo $error;
  exit;
}
if ($idProject) {
  $help=i18n('helpRestrictTypesProjectInline');
} else if ($idProjectType) {
  $help=i18n('helpRestrictTypesProjectTypeInline');
} else if ($idProfile) {
  $help=i18n('helpRestrictTypesProfileInline');
} else {
  $help=i18n('helpRestrictTypesInline');
}
echo '<span style="white-space:nowrap">'.$help.'</span><br/><br/>';?>
<form dojoType="dijit.form.Form" id="restrictTypesForm" jsId="restrictTypesForm" name="restrictTypesForm" encType="multipart/form-data" action="" method="" >
<?php
echo '<input type="hidden" name="idProjectType" id="idProjectTypeParam" value="'.(($idProject)?'':$idProjectType).'" />';
echo '<input type="hidden" name="idProject" id="idProjectParam" value="'.$idProject.'" />';
echo '<input type="hidden" name="idProfile" id="idProfile" value="'.$idProfile.'" />';
$lstCustom=Type::getClassList(($idProfile)?true:false);
if ($idProject) {
  $canUpdate=(securityGetAccessRightYesNo('menuProject', 'update', new Project($idProject,true)) == 'YES');
} else if ($idProjectType) {
  $canUpdate=(securityGetAccessRightYesNo('menuProjectType', 'update', new ProjectType($idProjectType)) == 'YES');
} else if ($idProfile) {
  $canUpdate=(securityGetAccessRightYesNo('menuProfile', 'update', new Profile($idProfile)) == 'YES');
} else {
  errorLog(" dynamicDialogRestrictTypes : no parameter set idType=$idType, idProject=$idProject, idProjectType=$idProjectType, idProfile=$idProfile)");
  $canUpdate="NO";
}

unset($lstCustom['VersionType']);
echo "<table style='width:100%'>";
foreach ($lstCustom as $class=>$nameClass) {
  echo "<tr style='padding-bottom:20px'><td class='dialogLabel' valign='top' style='min-width:250px'><label style='min-width:250px'>$nameClass".Tool::getDoublePoint()."</label></td>";
  echo "<td>";
  $list=SqlList::getList($class);
  if ($idProject and $idProjectType) {
    $restrict=Type::listRestritedTypesForClass($class,null,$idProjectType,null,true);
    if (count($restrict)) {
      foreach($list as $id=>$val) {
        if (!in_array($id, $restrict)) {
          unset($list[$id]);
        }
      }
    }
  }
  $cpt=0;
  $cols=5;
  echo "<table><tr style='height:20px'>";
  foreach ($list as $id=>$val) {
    $name="checkType_$id";
    $rt=Type::getSpecificRestrictTypeValue($id,$idProject,$idProjectType,$idProfile);
    if ($cpt % $cols ==0 and $cpt!=0) echo "</tr><tr style='height:20px'>";
    echo "<td style='vertical-align:top;'><table style='vertical-align:top;min-width:150px;width:150px;padding:2px 5px;'><tr>";
    echo "<td style='width:20px;vertical-align:top;'><div dojoType='dijit.form.CheckBox' type='checkbox' name='$name' id='$name' ";
    echo " class='greyCheck' style='' ".(($rt)?' checked ':'');
    if (!$canUpdate) echo " readonly ";
    echo ' ></div></td>';
    echo "<td>$val</td>";
    echo "<td style='width:5px'>&nbsp;</td>";
    echo "</tr></table>";
    echo "<input type='hidden' name='class_$name' value='$class' />";
    echo "</td>";
    $cpt++;
  }
  echo "</tr></table>";
  echo "</td>";
  echo "</tr>";
  echo "<tr style='height:5px;'><td colspan='$cols'>&nbsp;</td></tr>";
}
echo "</table>";
?>
</form>
<table style="width:100%">
    <tr>
      <td align="center">
        <input type="hidden" id="dialogNoteAction">
        <button class="mediumTextButton"  dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogRestrictTypes').hide();">
          <?php echo i18n(($canUpdate)?"buttonCancel":"comboCloseButton");?>
        </button>
        <?php if ($canUpdate) {?>
        <button class="mediumTextButton"  id="dialogRestrictTypesSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);saveRestrictTypes();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
        <?php }?>
      </td>
    </tr>
</table>
