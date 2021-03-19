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
scriptLog('   ->/view/galleryShow.php');

$allowedEntities = array('Quotation', 'Command', 'Contract', 'Bill');
foreach ($allowedEntities as $index=>$ent) {
  if (!SqlElement::class_exists($ent)) {
    unset($allowedEntities[$index]);
  }
}
$entityDateField = array('Quotation' => 'doneDate',
    'Command' => 'validatedStartDate',
    'Contract' => 'validatedStartDate',
    'Bill' => 'date'
);
$paramEntity = 'Bill';
if (array_key_exists('entity', $_REQUEST) && in_array($_REQUEST['entity'], $allowedEntities)) {
    $paramEntity = trim($_REQUEST['entity']);
}
$paramStartDate = '';
if (array_key_exists('startDate', $_REQUEST)) {
    $paramStartDate = trim($_REQUEST['startDate']);
}
$paramEndDate = '';
if (array_key_exists('endDate', $_REQUEST)) {
    $paramEndDate = trim($_REQUEST['endDate']);
}
$paramClient = '';
if (array_key_exists('idClient', $_REQUEST)) {
    $paramClient = trim($_REQUEST['idClient']);
}
$paramType = '';
if (array_key_exists('id' . $paramEntity . 'Type', $_REQUEST)) {
    $paramType = trim($_REQUEST['id' . $paramEntity . 'Type']);
}

$where = getAccesRestrictionClause($paramEntity, false);
if (trim($paramStartDate) != '') {
    $where.= " and " . $entityDateField[$paramEntity] . " >= '" . htmlEncode($paramStartDate)."'";
}
if (trim($paramEndDate) != '') {
    $where.= " and " . $entityDateField[$paramEntity] . " <= '" . htmlEncode($paramEndDate)."'";
}
if (trim($paramClient) != '') {
    $where.= " and idClient = " . htmlEncode($paramClient);
}
if (trim($paramType) != '') {
    $where.= " and id" . $paramEntity . "Type = " . htmlEncode($paramType);
}
$order = " " . $entityDateField[$paramEntity] . " DESC";
$obj = new $paramEntity();
$lstObj = $obj->getSqlElementsFromCriteria(null, false, $where, $order);
?>
<div style="padding:10px">
    <?php
    if (count($lstObj)) {
        $listIds = array();
        $result = array();
        foreach ($lstObj as $obj) {
            $listIds[] = $obj->id;
            $result[$obj->id] = array();
            $result[$obj->id]['obj'] = $obj;
        }

        $where = getAccesRestrictionClause('Attachment', false);
        $where.= " and refType = '" . $paramEntity . "'";
        $where.= " and refId in (" . implode(',', $listIds) . ")";
        $atch = new Attachment();
        $lstAtch = $atch->getSqlElementsFromCriteria(null, false, $where, null);
        foreach ($lstAtch as $atch) {
            $result[$atch->refId]['atch'][] = $atch;
        }
        ?>
        <table style="width: 100%">
            <?php
            foreach ($result as $obj) {
                ?>
                <tr>
                    <th class="reportTableHeader">
                        <a style="color: #FFF" href="#" onclick="gotoElement('<?= $paramEntity ?>','<?php echo $obj['obj']->id ?>')"><?php echo $obj['obj']->name; ?></a>
                    </th>
                </tr>
                <?php
                if (array_key_exists('atch', $obj)) {
                    foreach ($obj['atch'] as $atch) {
                        ?>
                        <tr>
                            <td class="reportTableData">
                                <?php
                                if (substr($atch->mimeType, 0, 5) == 'image') {
                                    ?>
                                    <img src="../tool/download.php?class=Attachment&amp;id=<?= $atch->id ?>&amp;nodl=1" alt="<?= $atch->fileName ?>" />
                                    <?php
                                } else {
                                    ?>
                                    <object data="../tool/download.php?class=Attachment&amp;id=<?= $atch->id ?>&amp;nodl=1" type="<?= $atch->mimeType ?>"<?= (($atch->mimeType == 'application/pdf') ? ' style="width:100%;min-height:400px;"' : '') ?>>
                                        <a href="../tool/download.php?class=Attachment&amp;id=<?= $atch->id ?>">Download <?= $atch->fileName ?></a>
                                    </object>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td class="reportTableData"><em>' . i18n('noAttachmentFound') . '</em></td></tr>';
                }
            }
            ?>
        </table>
        <?php
    } else {
        echo '<em>' . i18n('noDataToDisplay') . '</em>';
    }
    ?>
</div>
