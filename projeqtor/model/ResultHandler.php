<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : antonio
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
 * ============================================================================
 * This abstract class is design to handle and control $result values
 */
/**
 * Description of menuUtil
 *
 * @author antonio
 */
class ResultHandler {
  const TYPE_CONTROL = 1;
  const TYPE_INSERT = 2;
  const TYPE_UPDATE = 3;
  const TYPE_DELETE = 4;
  const TYPE_COPY = 5;
  static $me;
  public $type;
  public $message;
  public $control;
  public $nodataMsg;
  public $status;
  public $savedId;
  
  static function getInstance() {
  if (! isset ( self::$me )) {
    self::$me = new ResultHdl ();
  }
  return self::$me;
}
  
  // Constructor set private because this class implements singleton and should not be directly instanciated, use ResultHandler::getInstance()
  private function __construct($type = null, $status = null, $message = null, $control = null, $nodataMsg = null, $savedId = '') {
    $this->type = $type;
    $this->message = $message;
    $this->control = $control;
    $this->nodataMsg = $nodataMsg;
    $this->status = $status;
    $this->savedId = $savedId;
  }
  
  public function formatMessage() {
    return '<div class="message' . $this->status . '" >' . $this->resultMessage () . '</div>';
  }
  
  public function resutlMessage() {
    if ($this->type == self::TYPE_CONTROL) {
      return $this->controlMessage ();
    } else if ($this->type == self::TYPE_DELETE) {
      return $this->deleteMessage ();
    } else if ($this->type == self::TYPE_INSERT) {
      return $this->insertMessage ();
    } else if ($this->type == self::TYPE_UPDATE) {
      return $this->updateMessage ();
    } else if ($this->type == self::TYPE_COPY) {
      return $this->copyMessage ();
    }
  }
  
  protected function updateMessage() {
    $returnValue = $this->message;
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $this->status . '" />';
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->saveId ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
    $returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage ( $this->nodataMsg ) . '" />';
    return $returnValue;
  }
  
  public function insertMessage() {
    $returnValue = $this->message;
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $this->status . '" />';
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->savedId ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="insert" />';
    return $returnValue;
  }
  
  public function controlMessage() {
    if ($this->status == "CONFIRM") {
      $this->message = i18n ( 'messageConfirmationNeeded' );
    } else {
      $this->message = i18n ( 'messageInvalidControls' );
    }
    
    $returnValue = '<b>' . $this->message . '</b>';
    $returnValue .= '<br/>' . $this->control;
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $this->status . '" />';
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEnstatus ( $this->savedId ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
    return $returnValue;
  }
  
  protected function deleteMessage() {
    $returnValue = $this->message;
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $this->status . '" />';
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->saveId ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="delete" />';
    $returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage ( $this->nodataMsg ) . '" />';
    return $returnValue;
  }
  
  protected function copyMessage() {
    $returnValue = $this->message;
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $this->status . '" />';
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->savedId ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="copy" />';
    return $returnValue;
  }
}