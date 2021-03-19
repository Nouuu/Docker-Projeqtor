<?php
//var_dump($_REQUEST);
$userName=RequestHandler::getId('userName');
$yearSpinner=RequestHandler::getNumeric('yearSpinner');
$print=true;

?>
 <div id="fullWorkDiv" name="fullWorkDiv" dojoType="dijit.layout.ContentPane" region="center" >
    <div id="workDiv" name="workDiv">
        <?php Absence::drawActivityDiv($userName, $yearSpinner);?>
    </div>
    <div id="calendarDiv" name="calendarDiv">
        <?php Absence::drawCalandarDiv($userName, $yearSpinner);?>
     </div>
  </div>  