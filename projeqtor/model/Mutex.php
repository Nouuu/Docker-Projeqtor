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


require_once('_securityCheck.php');
class Mutex
{
    var $lockname;
    var $timeout;
    var $locked;
 
    function __construct($name, $timeout = 10)
    {
        $this->lockname = $name;
        $this->timeout = $timeout;
        $this->locked = -1;
    }
 
    function reserve()
    {
    	if (!$this->lockname) return;
    	if (Sql::isMysql()) {
        $rs = Sql::query("SELECT GET_LOCK('".$this->lockname."', ".$this->timeout.") as mutex");
        $line=Sql::fetchLine($rs);
        $this->locked = $line['mutex'];
        //mysqli_free_result($rs);
    	} else if (Sql::isPgsql()) {
    		$prefix=Parameter::getGlobalParameter('paramDbPrefix');
    		$rs=Sql::query("LOCK TABLE ".$prefix."mutex IN ACCESS EXCLUSIVE MODE");
    		$rs=Sql::query("SELECT * FROM ".$prefix."mutex WHERE name='".$this->lockname."'");
    		if (!is_array($rs) or count($rs)==0) {
    			$rs=Sql::query("INSERT INTO ".$prefix."mutex (name) VALUES ('".$this->lockname."')");
    		} 
    	}
    }
 
    function release()
    {
    	if (!$this->lockname) return;
    	if (Sql::isMysql()) {
        $rs = Sql::query("SELECT RELEASE_LOCK('".$this->lockname."') as mutex");
        $line=Sql::fetchLine($rs);
        $this->locked = !$line['mutex'];
        //mysqli_free_result($rs);
    	} else if (Sql::isPgsql() and 0) {
    		$prefix=Parameter::getGlobalParameter('paramDbPrefix');
    		$rs=Sql::query("LOCK TABLE ".$prefix."mutex IN ACCESS SHARE MODE");
    	}
    }
 
    public function isFree()
    {
    	if (!$this->lockname) return true;
    	if (Sql::isMysql()) {
        $rs = Sql::query("SELECT IS_FREE_LOCK('".$this->lockname."') as mutex");
        $line=Sql::fetchLine($rs);
        $lock = (bool)$line['mutex'];
        //mysqli_free_result($rs);
        return $lock;
    	} else {
    	  $dbName=Sql::getDbName();
    	  $prefix=Sql::getDbPrefix();
    	  $querySearched="SELECT * FROM ".$prefix."mutex WHERE name=''".$this->lockname."''";
    	  $query="SELECT a.query FROM pg_stat_activity a JOIN pg_locks l ON l.pid = a.pid";
    	  $query.=" WHERE a.datname='$dbName' and a.query like '%$querySearched%' and l.relation=(SELECT oid FROM pg_class WHERE relname = '".$prefix."mutex');";
    	  $rs=Sql::query($query);
    	  if (!is_array($rs) or count($rs)==0) return true;
    	  else return false;
    	}
    }
}
 