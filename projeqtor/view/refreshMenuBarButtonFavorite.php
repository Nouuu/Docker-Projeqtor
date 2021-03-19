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

/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
require_once "../tool/formatter.php";
scriptLog('   ->/view/refreshMenuBarButtonFavorite.php');

$item = RequestHandler::getValue('item');
$defaultMenu = RequestHandler::getValue('menuFilter');
?>
<table style="width:100%;height:100%;">
 <tr>
   <td style="padding-right:5px" class="<?php if($defaultMenu=='menuBarCustom')echo 'imageColorNewGuiSelected';?>" id="favoriteButton" title="<?php echo i18n('Favorite');?>" onclick="menuNewGuiFilter('menuBarCustom', '<?php echo $item;?>');"><?php echo formatNewGuiButton('Favoris', 22, true);?></td>
   <td style="padding-right:10px" class="<?php if($defaultMenu=='menuBarRecent')echo 'imageColorNewGuiSelected';?>" id="recentButton" title="<?php echo i18n('Recent');?>" onclick="editFavoriteRow(true);menuNewGuiFilter('menuBarRecent', '<?php echo $item;?>');"><?php echo formatNewGuiButton('Recent', 22, true);?></td>
 </tr>
</table>
