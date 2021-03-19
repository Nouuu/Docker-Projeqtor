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
scriptLog('   ->/view/galleryMain.php');
?>
<input type="hidden" name="objectClassManual" id="objectClassManual" value="Gallery" />
<div class="container" dojoType="dijit.layout.BorderContainer">
    <div id="listGalleryDiv" dojoType="dijit.layout.ContentPane" region="top" splitter="true" style="height:62px;">
        <?php include 'galleryParameters.php' ?>
    </div>
    <div id="detailGalleryDiv" dojoType="dijit.layout.ContentPane" region="center">
    </div>
</div>