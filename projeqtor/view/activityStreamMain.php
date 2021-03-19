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

/*
 * ============================================================================ Presents an object.
 */
require_once "../tool/projeqtor.php";
?>
<div dojoType="dijit.layout.BorderContainer" class="container" style="overflow-y:auto;width:100%;">
	<input type="hidden" name="objectClassManual" id="objectClassManual" value="ActivityStream" />
	<div dojoType="dijit.layout.ContentPane" id="activityStreamParameterDiv" class="listTitle" style="z-index: 3; overflow: visible;width:100%;height:<?php echo (isNewGui())?'126':'125';?>px;" region="top">
	  <?php include "../view/activityStreamParameter.php";?>
  </div>
	<div dojoType="dijit.layout.ContentPane" id="activityStreamListDiv" region="center" style="overflow-y:overlay;width:100%;overflow-y:hidden;z-index:999">
	  <?php include "../view/activityStreamList.php";?>
	</div>
</div>