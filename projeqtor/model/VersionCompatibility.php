<?php
/*
 *	@author: qCazelles 
 */

require_once('_securityCheck.php');
class VersionCompatibility extends SqlElement {
	
	public $id;
	public $idVersionA;
	public $idVersionB;
	public $comment;
	public $creationDate;
	public $idUser;
	public $idle;
	
	/** ==========================================================================
	 * Constructor
	 * @param $id the id of the object in the database (null if not stored yet)
	 * @return void
	 */
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
	}
	
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {
		parent::__destruct();
	}
	
	public function control() {
		$result="";
		$checkCrit="(idVersionA=$this->idVersionA and idVersionB=$this->idVersionB) or (idVersionA=$this->idVersionB and idVersionB=$this->idVersionA)";
		if ($this->id) $checkCrit.=" and id!=$this->id";
		$vc=new VersionCompatibility();
		$check=$vc->getSqlElementsFromCriteria(null, false, $checkCrit);
		if (count($check)>0) {
			$result.='<br/>' . i18n('errorDuplicateLink');
		}
		if ($this->idVersionA==$this->idVersionB) {
			$result.='<br/>' . i18n('errorVersionLoop');
		}
		
		$defaultControl=parent::control();
		if ($defaultControl!='OK') {
			$result.=$defaultControl;
		}if ($result=="") {
			$result='OK';
		}
		
		return $result;
	}
	
}
?>