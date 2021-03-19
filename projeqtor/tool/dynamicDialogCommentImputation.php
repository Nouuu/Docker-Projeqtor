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
 * Habilitation defines right to the application for a menu and a profile.
 */ 
require_once "../tool/projeqtor.php";

$idAssignment=-1;
if (array_key_exists('idAssignment',$_REQUEST)) {
  $idAssignment=$_REQUEST['idAssignment'];
}

$week=-1;
if (array_key_exists('week',$_REQUEST)) {
  $week=$_REQUEST['week'];
}

$year=-1;
if (array_key_exists('year',$_REQUEST)) {
  $year=$_REQUEST['year'];
}

$refType=-1;
if (array_key_exists('refTypeComment',$_REQUEST)) {
  $refType=$_REQUEST['refTypeComment'];
}

$refId=-1;
if (array_key_exists('refIdComment',$_REQUEST)) {
  $refId=$_REQUEST['refIdComment'];
}


$commentImputation='';
if (array_key_exists('commentImputation',$_REQUEST)) {
  $commentImputation=$_REQUEST['commentImputation'];
}
$commentImputationNote=false;
if(Parameter::getUserParameter("commentImputationNote")!=null && Parameter::getUserParameter("commentImputationNote")==1)$commentImputationNote=true;
if(trim($commentImputation)==''){
?>
  <form id='commentImputationForm' name='commentImputationForm' onSubmit="return false;" >
  <table style="width:100%;">
  <?php 
  if($idAssignment!=-1 && $week!=-1 && $year!=-1){
  ?>
    <tr>
      <td>
        <div dojoType="dijit.form.Textarea" required="true" class="input required" style="width:500px;"  name="commentImputation" id="commentImputation"></div>
      </td>
    </tr>
    <tr>
      <td>
       <br/>
      </td>
    </tr>
    <tr>
      <td style="float:right;">
      <?php echo i18n('commentImputationSaveNote');?>&nbsp;
        <div dojoType="dijit.form.CheckBox" type="checkbox" class="greyCheck"
         name="commentImputationNote" id="commentImputationNote" <?php if($commentImputationNote)echo 'checked';?>>
        </div>
      </td>
    </tr>
    <tr>
      <td>
       <br/>
      </td>
    </tr>
  <?php 
  }else if($idAssignment!=-1){
  $assignment=new Assignment($idAssignment);
  ?>
    <tr>
      <td>
        <div style="min-width: 500px;max-height: 500px; overflow: auto;" name="commentImputation" id="commentImputation">
        <?php echo htmlEncode($assignment->comment,'withBR');?>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        &nbsp;
      </td>
    </tr>
  <?php 
  }
  ?>
    <tr>
      <td align="center">
        <?php 
        if($idAssignment!=-1 && $week!=-1 && $year!=-1){
        ?>
          <button class="mediumTextButton" dojoType="dijit.form.Button" type="button" onclick="dijit.byId('dialogCommentImputation').hide();formChangeInProgress=false;">
            <?php echo i18n("buttonCancel");?>
          </button>
          <button class="mediumTextButton" id="dialogCommentImputationSubmit" dojoType="dijit.form.Button" type="submit" onclick="protectDblClick(this);commentImputationSubmit('<?php echo $year.'\',\''.$week;?>',<?php echo $idAssignment;?>,'<?php echo $refType;?>',<?php echo $refId;?>);return false;">
            <?php echo i18n("buttonOK");?>
          </button>
        <?php 
        }else if($idAssignment!=-1){
        ?>
          <button class="mediumTextButton"  id="dialogCommentImputationSubmit" dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogCommentImputation').hide();formChangeInProgress=false;">
            <?php echo i18n("buttonOK");?>
          </button>
        <?php 
        }
        ?>
      </td>
    </tr>
  </table>
  </form>
<?php 
}else{
  $commentImputationNote=false;
  if (array_key_exists('commentImputationNote',$_REQUEST)) {
    $commentImputationNote=true;
  }
  Parameter::storeUserParameter("commentImputationNote", $commentImputationNote ? 1 : 0);
  $finalComment='['.$year.'-'.$week.']: '.$commentImputation;
  $assignment=new Assignment($idAssignment);
  $assignment->comment=$finalComment."\n\n".$assignment->comment;
  $assignment->save();
  if($commentImputationNote){
    $note = new Note();
    $note->idUser=getSessionUser()->id;
    $note->creationDate=date("Y-m-d H:i:s");
    $note->refId=$refId;
    $note->refType=$refType;
    $note->note=nl2brForPlainText($finalComment);
    $note->idPrivacy=1;
    $note->save();
  }
  echo $finalComment;
}
?>