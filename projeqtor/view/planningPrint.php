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
scriptLog('   ->/view/planningPrint.php');
?>
  <div style="border-right: 2px solid grey; z-index:30; position:relative; overflow:hidden;" class="ganttDiv" 
    id="leftGanttChartDIV_print" name="leftGanttChartDIV_print">
  </div>
  <div style="xborder: 2px solid green; overflow:hidden; position: absolute; top: 0px;" xclass="ganttDiv" 
    id="GanttChartDIV_print" name="GanttChartDIV_print" >
    <div style="overflow:hidden;" class="ganttDiv"
      id="topGanttChartDIV_print" name="topGanttChartDIV_print">
    </div>
    <div style="xborder: 2px solid red; z-index:30; position: relative; top: 43px;" class="ganttDiv"
      id="rightGanttChartDIV_print" name="rightGanttChartDIV_print">
    </div>
  </div>
  <div id="ganttDiv"></div>