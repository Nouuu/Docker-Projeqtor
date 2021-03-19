<?php
/* * * COPYRIGHT NOTICE *********************************************************
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
 * ** DO NOT REMOVE THIS NOTICE *********************************************** */

/* ============================================================================
 * List of parameter specific to a user.
 * Every user may change these parameters (for his own user only !).
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/galleryParameters.php');
$user = getSessionUser();
$profile = new Profile($user->idProfile);
$allowedEntities = array('Quotation', 'Command', 'Contract', 'Bill');
foreach ($allowedEntities as $index=>$ent) {
  if (!SqlElement::class_exists($ent)) {
    unset($allowedEntities[$index]);
  }
}
$defaultEntity = (isset($_REQUEST['entity']) && in_array($_REQUEST['entity'], $allowedEntities)) ? $_REQUEST['entity'] : 'Bill';
$defaultStartDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$defaultEndDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$defaultSupplierValue = isset($_REQUEST['idClient']) ? $_REQUEST['idClient'] : '';
$defaultIdTypeValue = '';
?>
<form id="galleryForm" name="galleryForm" onSubmit="return false;">
    <table style="width:100%;">
        <thead>
            <tr>
                <th class="reportTableHeader"><?= i18n('colElement') ?></th>
                <th class="reportTableHeader"><?= i18n('colStartDate') ?></th>
                <th class="reportTableHeader"><?= i18n('colEndDate') ?></th>
                <th class="reportTableHeader"><?= i18n('colClient') ?></th>
                <th class="reportTableHeader"><?= i18n('colType') ?></th>
                <th class="reportTableHeader">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="reportTableData" style="background:var(--color-background)">
                    <select dojoType="dijit.form.FilteringSelect" class="input" 
                    <?php echo autoOpenFilteringSelect();?>
                            style="width: 200px;"
                            id="entity" name="entity"
                            onchange="changeGalleryEntity();
                                    return false;">
                        <?php
                        foreach($allowedEntities as $entity) {
                            echo '<option value="'.$entity.'"'.(($defaultEntity == $entity) ? ' selected="selected"' : '') .'>'.i18n($entity).'</option>';
                        }
                        ?>
                    </select>
                </td>
                <td class="reportTableData" style="background:var(--color-background)">
                    <div style="width:100px; text-align: center; color: #000000;" 
                         dojoType="dijit.form.DateTextBox" 
                         <?php
                         if (sessionValueExists('browserLocaleDateFormatJs')) {
                             echo ' constraints="{datePattern:\'' . getSessionValue('browserLocaleDateFormatJs') . '\'}" ';
                         }
                         ?>
                         invalidMessage="<?php echo i18n('messageInvalidDate'); ?>" 
                         value="<?php echo $defaultStartDate; ?>"
                         hasDownArrow="true"
                         id="startDate" name="startDate">
                    </div>
                </td>
                <td class="reportTableData" style="background:var(--color-background)">
                    <div style="width:100px; text-align: center; color: #000000;" 
                         dojoType="dijit.form.DateTextBox" 
                         <?php
                         if (sessionValueExists('browserLocaleDateFormatJs')) {
                             echo ' constraints="{datePattern:\'' . getSessionValue('browserLocaleDateFormatJs') . '\'}" ';
                         }
                         ?>
                         invalidMessage="<?php echo i18n('messageInvalidDate'); ?>" 
                         value="<?php echo $defaultEndDate; ?>"
                         hasDownArrow="true"
                         id="endDate" name="endDate">
                    </div>
                </td>
                <td class="reportTableData" style="background:var(--color-background)">
                    <select dojoType="dijit.form.FilteringSelect" class="input" 
                    <?php echo autoOpenFilteringSelect();?>
                            style="width: 200px;"
                            id="idClient" name="idClient">
                                <?php htmlDrawOptionForReference("idClient", $defaultSupplierValue, null, false); ?>
                    </select>
                </td>
                <td class="reportTableData" style="background:var(--color-background)">
                    <select dojoType="dijit.form.FilteringSelect" class="input" 
                    <?php echo autoOpenFilteringSelect();?>
                            style="width: 200px;"
                            id="id<?= $defaultEntity ?>Type" name="id<?= $defaultEntity ?>Type">
                                <?php htmlDrawOptionForReference("id".$defaultEntity."Type", $defaultIdTypeValue, null, false); ?>
                    </select>
                </td>
                <td class="reportTableData" style="background:var(--color-background)">
                    <button title="<?php echo i18n('galleryShow') ?>"   
                            dojoType="dijit.form.Button" type="submit" 
                            id="gallerySubmit" name="gallerySubmit" 
                            iconClass="dijitButtonIcon dijitButtonIconDisplay" class="detailButton whiteBackground" showLabel="false"
                            onclick="runGallery();
                                    return false;">
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</form>