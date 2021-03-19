<?php
use Matrix\Operators\Operator;
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
$monthArray=array();
function colorNameFormatter($value,$idTicket=-1, $minHeight='10') {
  global $print,$outMode,$outModeBack;
  $notRounded=true;
  if ($value) {
    $tab=explode("#split#",$value);
    if (count($tab)>1) {
      if (count($tab)==2) { // just found : val #split# color
        $val=$tab[0];
        $color=$tab[1];
        $order='';
      } else if (count($tab)==3) { // val #split# color #split# order
          $val=$tab[1];
          $color=$tab[2];
          $order=$tab[0];
      } else { // should not be found
        return value;
      }
      if (! trim($color)) $color='#FFFFFF';
      $foreColor=getForeColor($color);
//       return '<div '.($idTicket!=-1 ? 'id="status'.$idTicket.'"' : '').' style="vertical-align:middle;'
//           .(($notRounded)?'border:0px;padding:6px 3px;height:100%;':'border:1px solid #CCC;border-radius:10px;padding: 5px;display:inline-block;')
//           .(($color=='transparent')?'font-weight:bold;font-style:italic;':'')
//           .'text-align: center;'.(($print and $outMode=='pdf')?'width:95%;min-height:18px;':'') . 'background-color: ' . $color . '; color:' . $foreColor . ';">' 
//           .'<div style="position:relative;margin:0 auto;">'.$val.'</div>'
//           .'</div>';
      return '<table style="width:100%;height:100%;min-height:'.$minHeight.'px;border-collapse: collapse;">'
            .' <tr style="height:100%;min-height:'.$minHeight.'px">'
            .'  <td '.($idTicket!=-1 ? 'id="status'.$idTicket.'"' : '').' style="vertical-align:middle;border:0px;'
            . (($print and $outMode!='pdf' and $outModeBack=='pdf')?'font-size:10pt;':'')
            .(($color=='transparent')?'font-style:italic;':'')
            .'text-align: center;'.(($print and $outMode=='pdf')?'width:95%;min-height:18px;':'') . 'background-color: ' . $color . '; color:' . $foreColor . ';">'
            .$val
            .'</td></tr></table>';

    } else {
      return $value;
    }
  } else { 
    return ''; 
  }
}
function classNameFormatter($value) {
  global $outMode;
  $classId=$value;
  $className=i18n($value);
  if ($outMode=='pdf') return '<div><table><tr><td><img src="../view/css/customIcons/grey/icon'.$value.'.png" style="width:16px;height:16px"/>&nbsp;</td><td>'.$className.'</td></tr></table></div>';
  else return '<div><table><tr><td><div class="icon'.$classId.'16 icon'.$classId.' iconSize16">&nbsp;</div></td><td>&nbsp;</td><td>'.$className.'</td></tr></table></div>';
}
function colorTranslateNameFormatter($value) {
	global $print,$outMode;
	$notRounded=true;
	if ($value) {
		$tab=explode("#split#",$value);
		if (count($tab)>1) {
			if (count($tab)==2) { // just found : val #split# color
				$val=$tab[0];
				$color=$tab[1];
				$order='';
				return colorNameFormatter(i18n($val)."#split#".$color);
			} else if (count($tab)==3) { // val #split# color #split# order
				$val=$tab[1];
				$color=$tab[2];
				$order=$tab[0];
				return colorNameFormatter($order."#split#".i18n($val)."#split#".$color);
			} else { // should not be found
				return i18n(value);
			}
		} else {
			return i18n($value);
		}
	} else {
		return '';
	}
}

function booleanFormatter($value) {
  if ($value==1) { 
    return '<div style="width:100%;text-align:center"><img src="img/checkedOK.png" width="12" height="12" /></div>'; 
  } else { 
    return '<div style="width:100%;text-align:center"><img src="img/checkedKO.png" width="12" height="12" /></div>'; 
  }
}

function colorFormatter($value) {
  if ($value) { 
    //return '<table width="100%"><tr><td style="background-color: ' . $value . '; width: 100%;">&nbsp;</td></tr></table>';
    return colorNameFormatter("&nbsp;#split#".$value); 
  } else { 
    return ''; 
  }
}

function dateFormatter($value) {
  if (strlen($value)==19) $value=substr($value,0,10);
  return htmlFormatDate($value,false);
}

function timeFormatter($value) {
  return htmlFormatTime($value,false);
}

function dateTimeFormatter($value) {
  return htmlFormatDateTime($value,false);
}

function translateFormatter($value) {
  if ($value) { 
    return i18n($value); 
  } else { 
    return ''; 
  }
}

function percentFormatter($value, $withProgressBar=false) {
  if ($value!==null) {
    if ($withProgressBar) {
      $pctTxt ='<div style="width:100%;text-align:center;">'.$value.'&nbsp;%</div>';
      $pctTxt.='<div style="height:3px;width:100%;position: relative; bottom:0px;">';
      $pctTxt.='<div style="height:3px;width:'.$value.'%;position: absolute;left:0%;background-color:#AAFFAA">&nbsp;</div>';
      $pctTxt.='<div style="height:3px;width:'.(100 - $value).'%;position: absolute;left:'.$value.'%; background-color:#FFAAAA">&nbsp;</div>';
      $pctTxt.='</div>';
      return $pctTxt;
    } else {
      return $value . '&nbsp;%';
    }
  } else {
    return ''; 
  }
}

function progressFormatter($value,$displayProgressText) {
  if ($value!==null) {
    $pct = intval($value, 10);
    $pctTxt='<div style="width:100%;text-align:center;">'.$displayProgressText.$pct.'&nbsp;%</div>';
    $pctTxt.='<div style="height:3px;width:100%;position: relative; bottom:0px;">';
    $pctTxt.='<div style="height:3px;width:'.$pct.'%;position: absolute;left:0%;background-color:#AAFFAA">&nbsp;</div>';
    $pctTxt.='<div style="height:3px;width:'.(100 - $pct)
       .'%;position: absolute;left:'.$pct
       .'%; background-color:#FFAAAA">&nbsp;</div>';
    $pctTxt.='</div>';
    return $pctTxt;
  } else {
    return '';
  }
}


function numericFormatter($value) {
  return ltrim($value,"0");
}

function sortableFormatter($value) {
  $tab=explode(".",$value);
  $result='';
  foreach ($tab as $val) {
    $result.=($result!="")?".":"";
    $result.=ltrim($val,"0");
  }
  return $result; 
}

function thumbFormatter($objectClass,$id,$size) {
	$image=SqlElement::getSingleSqlElementFromCriteria('Attachment', array('refType'=>$objectClass, 'refId'=>$id));
  if ($image->id and $image->isThumbable()) {
    return '<img src="'.getImageThumb($image->getFullPathFileName(),$size).'" />';
  } else {
  	return formatLetterThumb($id,$size);
  }
}

function formatLetterThumb($idUser,$size,$userName=null,$floatLetter="right", $idTicket=null) {
  global $print;
	if (!$userName) $userName=SqlList::getNameFromId('Affectable',$idUser);
	$arrayColors=array('#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#34495e', '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#2c3e50', '#f1c40f', '#e67e22', '#99CC00', '#e74c3c', '#95a5a6', '#d35400', '#c0392b', '#bdc3c7', '#7f8c8d');
	//'#3366FF','#FF9900','#99CC00', 
	$ind=(trim($idUser))?$idUser%count($arrayColors):0;
	$bgColor=(isset($arrayColors[$ind]))?$arrayColors[$ind]:'#000000';
	$fontSize=($size==32)?24:(($size==16)?10:15);
	if($print){
	  $result='<span style="position:relative;color:#ffffff;background-color:'.$bgColor.';float:left;font-size:'.$fontSize.'px;border-radius:50%;font-weight:300;text-shadow:none;text-align:center;border:1px solid #eeeeee;height:'.($size-2).'px;width:'.($size-2).'px; top:1px;" >';
	}else{
	  $result='<span style="color:#ffffff;background-color:'.$bgColor.';float:'.$floatLetter.';font-size:'.$fontSize.'px;border-radius:50%;font-weight:300;text-shadow:none;text-align:center;border:1px solid #eeeeee;height:'.($size-2).'px;width:'.($size-2).'px; top:1px;"'
	  		. ' onMouseOver="showBigImage(null,null,this,\''.$userName.'\',false);" onMouseOut="hideBigImage();" '
	  		. (($idTicket>0) ? 'id="responsible'.$idTicket.'"' : '') .'valueuser="'.$userName.'">';
	}
  $result.=strtoupper(mb_substr($userName,0,1,'UTF-8'));
	$result.='</span>';
	return $result;
}

function numericFixLengthFormatter($val, $numericLength=0) {  
  if ($numericLength>0) {
    $val=str_pad($val,$numericLength,'0', STR_PAD_LEFT);
  }
  return $val;
}

function workFormatter($value) {
  //$val=ltrim($value,"0");
  return Work::displayWorkWithUnit($value);
}
function imputationFormatter($value) {
  //$val=ltrim($value,"0");
  return Work::displayImputationWithUnit($value);
}

function costFormatter($value) {
	return htmlDisplayCurrency($value);
}

function iconFormatter($value) {
  if (! $value) return "";
  return '<img src="icons/'.$value.'" />';
}
function formatIconThumb($value,$size,$float) {
  if (! $value) return "";
  if (! file_exists('../view/icons/'.$value)) return "";  
  //return '<img src="icons/'.$value.'" />';
  
  $radius=round($size/2,0);
//  $res='<img style="border: 1px solid #AAA;width:'.$size.'px;height:'.($size).'px;float:'.$float.';border-radius:'.$radius.'px"';
  $res='<img style="border: 0;width:'.$size.'px;height:'.($size).'px;float:'.$float.';"';
  $res.=' src="../view/icons/'.$value.'" ';
//   if (! $print and ($known or $alwaysDisplayBigImage)) {
//     $res.=' onMouseOver="showBigImage(\'Affectable\',\''.$userId.'\',this,\''.$title.'\''.(($known)?",false":",true").',\''.$nocache.'\');" onMouseOut="hideBigImage();"';
//   } else if (!$known and $userName) {
//     $res.=' onMouseOver="showBigImage(\'Affectable\',\''.$userId.'\',this,\''.$title.'\',true,\''.$nocache.'\');" onMouseOut="hideBigImage();"';
//   }
  $res.='/>';
  return $res;
}

function formatUserThumb($userId,$userName,$title,$size=22,$float='right',$alwaysDisplayBigImage=false,$idTicket=-1) {
	global $print;
	if ($print) return "";//$userName;
    if (! $userId) return '';
	$radius=round($size/2,0);
	$file=Affectable::getThumbUrl('Affectable', $userId, $size);
	$searchNocache=strpos($file,'?');
	$nocache='';
	if ($searchNocache) {
	  $nocache=substr($file, $searchNocache);
	  $pos=strpos($nocache,'#');
	  if ($pos>0) $nocache=substr($nocache,0,$pos);
	}
	$known=(substr($file,0,23) != '../view/img/Affectable/')?true:false;
// 	if ($title) {
// 	  $title=htmlEncode(i18n('thumb'.$title.'Title',array('<b>'.$userName.'</b>')),'quotes');
// 	} else if ($userName) {
	  $title=htmlEncode($userName,'quotes');
// 	}
	if (substr($file,0,6)=='letter') {
		$res=formatLetterThumb($userId, $size,$title,$float,$idTicket);
	} else {
	  $res='<img '.($idTicket!=-1 ? 'id="responsible'.$idTicket.'"' : '').' valueuser="'.$title.'" style="border: 1px solid #AAA;width:'.$size.'px;height:'.($size).'px;float:'.$float.';border-radius:'.$radius.'px"';
	  $res.=' src="'.$file.'" ';
		// Ceci est la partie quand on passe la souris sur l'image de la barre ( le "a" de admin par exemple )
		if (! $print and ($known or $alwaysDisplayBigImage)) {
			$res.=' onMouseOver="showBigImage(\'Affectable\',\''.$userId.'\',this,\''.$title.'\''.(($known)?",false":",true").',\''.$nocache.'\');" onMouseOut="hideBigImage();"';
		} else if (!$known and $userName) {
		  $res.=' onMouseOver="showBigImage(\'Affectable\',\''.$userId.'\',this,\''.$title.'\',true,\''.$nocache.'\');" onMouseOut="hideBigImage();"';
		}
		$res.='/>';
	}
	return $res;
}

function formatColorThumb($col,$val, $size=20, $float='right',$name="") {
  $class=substr($col,2);
  if (! SqlElement::class_exists($class)) return ''; 
  $color=SqlList::getFieldFromId($class, $val, 'color');
  if (! $color) return '';
  $radius=round($size/2,0);
  if (isNewGui()) $radius=5;
  $res='<div style="border: 1px solid #AAAAAA;background:'.$color.';';
  if (isNewGui()) $res.="margin-top:4px;margin-right:5px;";
  $width=$size-2;
  $height=$size-2;
  if (isNewGui()) {
    $width=$size-0;
    $height=$size;
  }
  $res.='width:'.$width.'px;height:'.$height.'px;float:'.$float.';border-radius:'.$radius.'px"';
  //$res.=' onMouseOver="drawGraphStatus();"';
  if($name!="")$res.=' onMouseOver="showBigImage(null,null,this,\''.$name.'\');" onMouseOut="hideBigImage();"';
  $res.='>&nbsp;</div>';
  return $res;
}
function formatDateThumb($creationDate,$updateDate,$float='right',$size=22,$addName="") {
  global $print;
  if ($print) return "";//htmlFormatDate($creationDate);
  if (! trim($creationDate) and ! trim($updateDate)) return '';
  $today=date('Y-m-d');
  $date=($updateDate)?$updateDate:$creationDate;
  $date=substr($date,0,10);
  $color="White";
  if ($date==$today) {
    $color='Red';
  } else if (addWorkDaysToDate($date,2)==$today) {
    $color='Yellow';
  } 
  $title='';
  if($creationDate)$title=i18n('thumbCreationTitle',array('<b>'.htmlFormatDate($creationDate,false,false).'</b>'));
  if ($updateDate and $updateDate!=$creationDate) {
    if($title==''){
      $title.="<i>".i18n('thumbUpdateTitle',array('<b>'.htmlFormatDate($updateDate,false,false).'</b>')).'</i>';
    }else{
      $title.="<br/><i>".i18n('thumbUpdateTitle',array('<b>'.htmlFormatDate($updateDate,false,false).'</b>')).'</i>';
    }
  }
  $title=htmlEncode($title,'quotes');
  //$file="../view/css/images/calendar$color$addName$size.png";
  $res='<span style="position:relative;float:'.$float.';padding-right:3px">';
  $res.='<a ';
	//$res.=' src="'.$file.'" ';
	if (! $print) {
	  $res.=' onMouseOver="showBigImage(null,null,this,\''.$title.'\');" onMouseOut="hideBigImage();"';
	}
	$res.='>';
	$res.="<div class='calendar$color$addName$size calendar$color$addName iconSize$size cancelColorImage' style=';width:".$size."px;height:".$size."px;".(($size==16)?'position:relative;top:4px;':'')."' >&nbsp;</div>";
	$res.='</a>';
	
  $month=getMonthName(substr($date, 5,2),5);
  $day=substr($date, 8,2);
  $dispDate=htmlFormatDate($date,true);
  if (substr($dispDate,4,1)=='-') {
    $dispDate=substr($dispDate,5);
  } else {
    $dispDate=substr($dispDate,0,5);
  }
  switch ($size) {
    case 16:
      $fontSize=0;
      $width=14;
      $float="float:right;";
      $top=6;
      break;
    case 22:
	    $fontSize=6.5;
	    $width=20;
	    $float="float:right;";
	    $top=8;
	    break;
	  case 32:
	    $fontSize=8;
	    $dispDate.='<br/>'.substr($date, 0,4);
	    $width=31;
	    $float="";
	    $top=10;
	    break;
	  default:
	    $fontSize=11;
	    $width=10;
	    $float="";
	}
	$res.='<div style="z-index:0;color:#000;background: transparent;pointer-events:none;text-align:center;'
	    .'width:'.$width.'px;'.$float.';position:absolute;top:'.$top.'px;font-size:'.$fontSize.'px;">'.$dispDate.'</div>';
	$res.='</span>';  
	return $res;
}

//ADD qCazelles - Ticket #170
function formatDateThumbWithText($date,$text,$float='right',$size=22,$addName="") {
  global $print;
  if ($print) return "";//htmlFormatDate($creationDate);
  $today=date('Y-m-d');
  $dateTrunc=substr($date,0,10);
  $color="White";
  if ($dateTrunc==$today) {
    $color='Red';
  } else if (addWorkDaysToDate($dateTrunc,2)==$today) {
    $color='Yellow';
  }
  $title=i18n($text,array('<b>'.htmlFormatDate($date).'</b>'));
  $title=htmlEncode($title,'quotes');
  $file="../view/css/images/calendar$color$addName$size.png";
  $res='<span style="position:relative;float:'.$float.';padding-right:3px">';
  $res.='<a ';
  //$res.=' src="'.$file.'" ';
  if (! $print) {
    $res.=' onMouseOver="showBigImage(null,null,this,\''.$title.'\');" onMouseOut="hideBigImage();"';
  }
  $res.='>';
  $res.="<div class='calendar$color$addName$size' style=';width:".$size."px;height:".$size."px;' >&nbsp;</div>";
  $res.='</a>';
  
  $month=getMonthName(substr($date, 5,2),5);
  $day=substr($date, 8,2);
  $dispDate=htmlFormatDate($date,true);
  if (substr($dispDate,4,1)=='-') {
    $dispDate=substr($dispDate,5);
  } else {
    $dispDate=substr($dispDate,0,5);
  }
  switch ($size) {
    case 22:
      $fontSize=6.5;
      $width=20;
      $float="float:right;";
      $top=8;
      break;
    case 32:
      $fontSize=8;
      $dispDate.='<br/>'.substr($date, 0,4);
      $width=31;
      $float="";
      $top=10;
      break;
    default:
      $fontSize=11;
      $width=10;
      $float="";
  }
  $res.='<div style="z-index:0;color:#000;background: transparent;pointer-events:none;text-align:center;'
      .'width:'.$width.'px;'.$float.';position:absolute;top:'.$top.'px;font-size:'.$fontSize.'px;">'.$dispDate.'</div>';
      $res.='</span>';
      return $res;
}
//END ADD qCazelles - Ticket #170

function formatPrivacyThumb($privacy, $team,$size=22) {
  // privacy=3 => private
  // privacy=2 => team
  // privacy=1 => public 
  if ($privacy == 3) {
    $title=htmlEncode(i18n('private'),'quotes');
    //echo '<img style="float:right;padding-right:3px" src="img/private.png" />';
    echo '<span style="float:right;padding-right:3px">';
    echo formatIcon('Fixed',$size,$title,false);
    echo '</span>';
  } else if ($privacy == 2) {
    $title=htmlEncode(i18n('team')." : ".SqlList::getNameFromId ('Team',$team ),'quotes');
    //echo '<img title="'.$title.'" style="float:right;padding-right:3px" src="img/team.png" />';
    echo '<span style="float:right;padding-right:3px">';
    echo formatIcon('Team',$size,$title,false);
    echo '</span>';
  }
}
function formatPrivacyThumbResult($privacy, $team,$size=22) {
  // privacy=3 => private
  // privacy=2 => team
  // privacy=1 => public
  $result='';
  if ($privacy == 3) {
    $title=htmlEncode(i18n('private'),'quotes');
    //echo '<img style="float:right;padding-right:3px" src="img/private.png" />';
    $result.= '<span style="float:right;padding-right:3px">';
    $result.= formatIcon('Fixed',$size,$title,false);
    $result.= '</span>';
  } else if ($privacy == 2) {
    $title=htmlEncode(i18n('team')." : ".SqlList::getNameFromId ('Team',$team ),'quotes');
    //$result.= '<img title="'.$title.'" style="float:right;padding-right:3px" src="img/team.png" />';
    $result.= '<span style="float:right;padding-right:3px">';
    $result.= formatIcon('Team',$size,$title,false);
    $result.= '</span>';
  }
  return $result;
}

function formatCommentThumb($comment,$img=null) {
  global $print;
  if ($print) return "";//$userName;
  $res='';
  if (! trim($comment)) return '';
  $title=htmlEncode($comment,'title');
  $res.='<span onMouseOver="showBigImage(null,null,this,\''.$title.'\');" onMouseOut="hideBigImage();"  style="margin-right:5px">';
  if ($img) {
    $res.='<img src="'.$img.'"/>';
  } else {
    $res.= formatSmallButton('Comment');
  }
  $res.= '</span>';
  return $res;
}

function getMonthName($month,$maxLength=0) {
  global $monthArray;
  if (! $month or $month==0) return '';
  if (!isset($monthArray) or count($monthArray)==0) {
    $monthArray=array(i18n("January"),i18n("February"),i18n("March"),
      i18n("April"), i18n("May"),i18n("June"),
      i18n("July"), i18n("August"), i18n("September"),
      i18n("October"),i18n("November"),i18n("December"));
  }
  $dispMonth=$monthArray[$month-1];
  if ($maxLength) {
    if ($maxLength=='auto') {
      $dispMonth=substr($dispMonth,0,4);
      if (strpos('aàeéèêiîïoôuù',substr($dispMonth,-1))!==false) {
        $dispMonth=substr($dispMonth,0,3);
      }
    } else {
      $dispMonth=substr($dispMonth,0,$maxLength);
    }
  }
  return $dispMonth;
}

function diffValues(&$old,&$new) {
  if ($old) {
    $array=Diff::compare(diffReplaceEOL($old), diffReplaceEOL($new));
    $arrayOld=array();
    $arrayNew=array();
    foreach ($array as $id=>$line) {
      if ($line[1]==Diff::DELETED) {
        $arrayOld[$id]=$line;
      } else if ($line[1]==Diff::INSERTED) {
        $arrayNew[$id]=$line;
      }
    }
    if ( (count($arrayNew)+count($arrayOld))<count($array)) { // Set Diff only if diff is shorter than original
      $new=nl2br(Diff::toString($arrayNew));
      $old=nl2br(Diff::toString($arrayOld));
    }
  }
}
function diffReplaceEOL($valIn) {
  $val=preg_replace('/<p(.)*?>/', "\n", $valIn);
  $val=preg_replace('/<td(.)*?>/', "\n", $val);
  $val=preg_replace('/<tr(.)*?>/', "\n", $val);
  $val=preg_replace('/<table(.)*?>/', "\n", $val);
  $val=str_replace(array('&nbsp;','<br />','<br/>','<div>','</div>','</p>','</td>','</tr>','</table>','<tbody>','</tbody>','color:white'),
                   array(' '     ,"\n"    ,"\n"   ,"\n"   ,''      ,''    ,''     ,''     ,''        ,''       ,''        ,'color:grey'),
                   $val);
  if (substr_count($val,'<o:p> </o:p>')>0 or substr_count($val,'<o:p></o:p>')>0) {
    $val=strip_tags($val);
    //return $valIn;
  }
  return $val;
}
function privateFormatter($value) {
  if ($value==0) {
    return "";
  } else {
    return '<div style="width:100%;text-align:center"><img style="height:16px" src="img/private.png" /></div>';
  }
}

function activityStreamDisplayNote ($note,$origin){
  global $print,$user, $userRessource;
  $inlineUserThumb=true;
  $rightWidthScreen=RequestHandler::getNumeric('destinationWidth');
  $userId = $note->idUser;
  $userName = SqlList::getNameFromId ( 'User', $userId );
  if ($inlineUserThumb) $userNameFormatted = '<span style="position:relative;margin-left:20px"><div style="position:absolute;top:'.((isNewGui())?'1':'-1').'px;left:-30px;width:25px;">'.formatUserThumb($note->idUser, $userName, 'Creator',16).'&nbsp;</div><strong>' . $userName . '</strong></span>';
  else $userNameFormatted = '<span ><strong>' . $userName . '</strong></span>';
  $idNote = '<span>#' . $note->id . '</span>';
  $objectClass=$note->refType;
  $objectId=$note->refId;
  if ($objectClass=='Ticket' and ! securityCheckDisplayMenu(null, $objectClass)) $objectClass='TicketSimple';
  $ticketName = '<span class="streamLink" style="margin-left:22px;position:relative;" onClick="gotoElement(\''.htmlEncode($objectClass).'\',\''.htmlEncode($note->refId).'\')">' 
      .'<div style="width:16px;position:absolute;top:0px;">'. formatIcon($note->refType, 16) . '</div>' . i18n($note->refType) . ' #' . $note->refId ;
  $ticketName.='</span>';
  if ($origin=='activityStream') $ticketName.=' | '.SqlList::getNameFromId($note->refType, $note->refId);
  
  if ($note->updateDate)  $colCommentStream = i18n ( 'activityStreamUpdateComment', array ($idNote) );
  else  $colCommentStream = i18n ( 'activityStreamCreationComment', array ($idNote ) );
  if (!$user) $user=getSessionUser();
  if (!$userRessource) $userRessource=new Affectable($user->id);

  $obj=new $objectClass($objectId,true);
  $canUpdate=securityGetAccessRightYesNo('menu' . $objectClass, 'update', $obj) == "YES";
  $canRead=securityGetAccessRightYesNo('menu' . $objectClass, 'read', $obj) == "YES";
  if ($origin=='activityStream' and !$canRead) {
    return ;
  }
  $resultNote='';
  $objectIsClosed=(isset($obj) and property_exists($obj, 'idle') and $obj->idle)?true:false;
  if ($objectIsClosed) $canUpdate=false;
  $isNoteClosed=getSessionTableValue("closedNotes", $note->id);
  if ($note->idPrivacy == 1 or ($note->idPrivacy == 3 and $user->id == $note->idUser) or ($note->idPrivacy == 2 and $userRessource->idTeam == $note->idTeam)) {
    $resultNote.= '<tr style="height:100%;">';
    $noteDiscussionMode = Parameter::getUserParameter('userNoteDiscussionMode');
    if($noteDiscussionMode == null){
      $noteDiscussionMode = Parameter::getGlobalParameter('globalNoteDiscussionMode');
    }
    if($noteDiscussionMode == 'YES'){
      for($i=0; $i<$note->replyLevel; $i++){
      	if($i >= 5){
      		break;
      	}
      	$resultNote.= '<td class="noteData" colspan="1" style="width:3%;border-bottom:0px;border-top:0px;border-right:solid 2px;font-size:100% !important;"></td>';
      }
      $resultNote.= '<td colspan="'.(6-$note->replyLevel).'" class="noteData" style="width:100%;font-size:100% !important;"><div style="float:left;">';
    }else{
      $resultNote.= '<td colspan="6" class="noteData" style="width:100%;font-size:100% !important;"><div style="float:left;">';
    }

    
    /*echo formatUserThumb($note->idUser, $userName, 'Creator',22,'left');
    echo '    <div style="float:left;clear:left;width:22px; margin-right:5px;position:relative">';
    echo formatIcon("MessageStream",22);
    echo ' <div style="position:absolute;top:0px;left:12px">';
    echo formatPrivacyThumb($note->idPrivacy, $note->idTeam,16);
    echo '</div>';
    echo '    </div>';
    */
    $resultNote.= '    <div style="float:left;width:22px;margin-left:6px;margin-bottom:6px">';
    $resultNote.= '      <div style="float:left;clear:left;margin-top:6px;width:22px;position:relative">';
    $resultNote.=          formatIcon("MessageStream",22);
    $resultNote.= '        <div style="xposition:absolute;top:0px;left:12px">';
    $resultNote.=            formatPrivacyThumbResult($note->idPrivacy, $note->idTeam,16);
    $resultNote.= '        </div>';
    $resultNote.= '      </div>';
    if (!$inlineUserThumb) {
    $resultNote.= '      <div style="float:left;clear:left;margin-top:6px;width:22px;">';
    $resultNote.=          formatUserThumb($note->idUser, $userName, 'Creator',22,'left');
    $resultNote.= '      </div>';
    }
    $resultNote.= '    </div>';
     
    
    
    $resultNote.= '</div><div style="margin-left:0px;margin-top:6px">';
    $resultNote.= '<table style="float:right;"><tr><td>';
    //if($origin=="objectStream" || $origin=="objectStreamKanban") {
    if($origin=="objectStream") {
          if ($note->idUser == $user->id and !$print and $canUpdate){
            $resultNote.=  '<div style="float:right;" ><a onClick="removeNote(' . htmlEncode($note->id) . ');" title="' . i18n('removeNote') . '" > '.formatSmallButton('Remove').'</a></div>';
          }
          if (!$print and $canUpdate) {
            $resultNote.=  '<div style="float:right;" ><a onClick="addNote(true,' . htmlEncode($note->id) . ');" title="' . i18n('replyToThisNote') . '" > '.formatSmallButton('Reply').'</a></div>';
          }
    }
    $resultNote.= '</td></tr><tr><td>';
    $resultNote.= '<div "style=float:right;"><a  id="imgCollapse_'.$note->id.'" style="float:right;'.((isNewGui() and $origin=='objectStream')?'margin-top:3px;':'').'" onclick="switchNoteStatus('.$note->id.');">'.formatSmallButton('Collapse'.(($isNoteClosed)?'Open':'Hide')).'</a></div>';
    $resultNote.= '</div></td></tr></table>';
    
    $maxWidth='100%';
    if ($origin=='objectStream') {
      $rightWidth=(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))-30).'px';
      if (isNewGui())   {
        if (Parameter::getUserParameter('paramScreen')=='top') {
          if (Parameter::getUserParameter('paramRightDiv')=='bottom') {
            $menuLeftOpen=(Parameter::getUserParameter('isMenuLeftOpen')=='false')?0:1;
            if ($menuLeftOpen) $innerNoteWidth=(intval((intval(getSessionValue('screenWidth'))-250)*0.7)-50).'px'; // menu open
            else $innerNoteWidth=(intval(intval(getSessionValue('screenWidth'))*0.7)-50).'px';
          } else { // trailing
            $innerNoteWidth=(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))-40).'px';
          }
        } else { // 'left'
          if (Parameter::getUserParameter('paramRightDiv')=='bottom') {
            $innerNoteWidth=(intval(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))*0.7)-50).'px';
          } else { // trailing
            $innerNoteWidth=(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))-40).'px';
          }
        }
        $maxWidth=(intval($innerNoteWidth)+50).'px';
      }
    } else {
    	if (RequestHandler::isCodeSet('destinationWidth')) {
        $rightWidth=(RequestHandler::getNumeric('destinationWidth')-30).'px';
        if (isNewGui()) $innerNoteWidth=(RequestHandler::getNumeric('destinationWidth')-80).'px';
    	} else {
    		$rightWidth="100%";
    	}
    	$maxWidth=$rightWidth;
    }
    $resultNote.= '<div class="activityStreamNoteContainer" style="padding-left:4px;max-width:'.$maxWidth.'">';
    $strDataHTML=$note->note;
    $resultNote.= '<div><div style="margin-top:2px;margin-left:37px;">'.(($origin!='objectStream')?$ticketName."&nbsp;|&nbsp;":"").$userNameFormatted.'&nbsp'.$colCommentStream.'</div>'; 
  	$resultNote.= '<div style="margin-top:3px;margin-left:37px;">'.formatDateThumb($note->creationDate,$note->updateDate,"left",16).'</div>';
  	if($note->updateDate){
  	 $resultNote.= '<div style="margin-top:8px;">'.htmlFormatDateTime($note->updateDate,false).'</div></div>';    	 
    } else {
     $resultNote.= '<div style="margin-top:8px;">'.htmlFormatDateTime($note->creationDate,false).'</div></div>';
    }
    $noteImgWidth=intval($rightWidthScreen)-30;
    if ($origin=='activityStream') $noteImgWidth-=40;
    $strDataHTML=htmlSetClickableImages($strDataHTML,$noteImgWidth);
    if($rightWidthScreen<100){
      $resultNote.= '<div class="activityStreamNoteContent activityStreamNote" id="activityStreamNoteContent_'.$note->id.'" style="display:block;height:'.(($isNoteClosed)?'0px':'100%').';margin-left:'.(($origin=='activityStream')?'36':'0').'px;margin-bottom:'.(($isNoteClosed)?'0px':'10px').';word-break:break-all;'.((isset($innerNoteWidth))?'width:'.$innerNoteWidth:'').'">';
      if($noteDiscussionMode != 'YES'){
      	if($note->idNote != null){
      		$resultNote.= '<span style="position:relative;float:left;padding-right:5px">'.formatIcon('Reply', 16, 'reply to note #'.$note->idNote).'</span>';
      	}
      }
      $resultNote.= $strDataHTML;
      $resultNote.= '</div></div></td></tr>'; 
    } else {
      $resultNote.= '<div class="activityStreamNoteContent activityStreamNote" id="activityStreamNoteContent_'.$note->id.'" style="display:block;height:'.(($isNoteClosed)?'0px':'100%').';margin-left:'.(($origin=='activityStream')?'36':'0').'px;margin-bottom:'.(($isNoteClosed)?'0px':'10px').';'.((isset($innerNoteWidth))?'width:'.$innerNoteWidth:'').'">';
      if($noteDiscussionMode != 'YES'){
      	if($note->idNote != null){
      		$resultNote.= '<span style="position:relative;float:left;padding-right:5px">'.formatIcon('Reply', 16, 'reply to note #'.$note->idNote).'</span>';
      	}
      }
      $resultNote.= $strDataHTML.'</div></div></td></tr>';
    } 
  }
  return $resultNote;
}


function activityStreamDisplayHist ($hist,$origin){
  $text='';
  $reftText='';
  $inlineUserThumb=true;
  $isAssign=false;
  $isAff=false;
  $isDovVers=false;
  $isTestCaseRun=false;
  $isLink=false;
  $attachment=false;
  $gotoAndStyle=' style="margin-left:18px;" ';
  $userId = $hist->idUser;
  $userName = ($hist->idUser!='')?SqlList::getNameFromId ( 'Affectable', $userId ):lcfirst(i18n('unknown'));
  $operation=$hist->operation;
  $change=$hist->colName;
  $oldVal=$hist->oldValue;
  $newVal=$hist->newValue;
  $date=$hist->operationDate;
  $objectClass=$hist->refType;
  $objectId=$hist->refId;
  $idProject=$hist->idProject;
  $currentUser=getSessionUser();
  $prof=$currentUser->idProfile;
  if ($objectClass=='Note') return;   // Already managed through other way
  if ($objectClass=='ProductStructure')return;              
  if ($objectClass=='Link') return;                 // Will be displayed on each item
  if (substr($change,0,6)=='|Note|') return;        // Already managed through other way
  if(substr($hist->refType, -15) == 'PlanningElement')return;
  if ($inlineUserThumb) $userNameFormatted = '<span style="font-weight:bold;position:relative;margin-left:20px;"><div style="position:absolute;top:'.((isNewGui())?'1':'-1').'px;left:-30px;width:25px;">'.formatUserThumb($userId, $userName, 'Creator',16).'&nbsp;</div><strong>' . $userName . '</strong></span>';
  else $userNameFormatted = '<span style="font-weight:bold;"><strong>' . $userName . '</strong></span>';
  if(preg_match( '|Attachement|',$change) or preg_match('|Attachment|',$change)){
    if(strpos($change, '|Attachement|'))$attach=explode('|', substr($change,(strpos($change, '|Attachement|')+1)));
    else $attach=explode('|', substr($change,(strpos($change, '|Attachment|')+1)));
    $objectClass=$attach[0];
    if ($objectClass=='Attachement') $objectClass='Attachment';
    $objectId=intval($attach[1]);
    $objectAttach=new $objectClass($objectId);
    if($objectAttach->id!=''){
      $attachment=true;
    }else{
      return;
    }
    $attName='<span style="font-weight:bold;">'.$objectAttach->fileName.'</span>';
  }
  $isPool=false;
  if(($objectClass=='Affectation' or $objectClass=='Assignment' or $objectClass=='DocumentVersion') and $operation!='delete'){
    $object= new $objectClass($objectId);
    if($object->id!=''){
      switch ($objectClass){
      	case 'Affectation':
      	  if (!($object->idResource or $object->idResourceSelect)) return;
      	  $isAff=true;
      	  $resource= new Affectable(((!$object->idResourceSelect)?$object->idResource:$object->idResourceSelect));
      	  if($resource->isResourceTeam==1){
      	    $isPool=true;
      	  }
      	  $objectClass='Project';
      	  $objectId=$object->idProject;
      	  break;
      	case 'Assignment':
      	  if (!$object->idResource) return;
      	  $isAssign=true;
      	  $resource=new Resource($object->idResource);
      	  $objectClass=$object->refType;
      	  $objectId=$object->refId;
      	  break;
      	case 'DocumentVersion':
      	  $isDovVers=true;
      	  $docVers='<span style="font-weight:bold;">'.$object->fullName.'</span>';
      	  $objectClass='Document';
      	  $objectId=$object->idDocument;
      	  break;
      	case 'TestCaseRun':
      	  $isTestCaseRun=true;
      	  $testSession=='<span style="font-weight:bold;">'.$object->id.'</span>';
      	  $objectClass='TestSession';
      	  $objectId=$object->idTestSession;
      	  break;
      }
      if(isset($resource)){
        $menu=($isPool==false)?'Resource':'ResourceTeam';
        if ( securityCheckDisplayMenu(null, $menu) and securityGetAccessRightYesNo('menu'.$menu, 'read', $resource)=="YES") {
          $gotoResource=' class="streamLink" onClick="gotoElement(\''.htmlEncode($menu).'\',\''.htmlEncode($resource->id).'\')"';
        }else{
          $gotoResource=' ';
        }
        $resourceName='<span '.$gotoResource.' class="'.((isNewGui() and isset($gotoResource) and $gotoResource!='')?'classLinkName':'').'">'.$resource->name.'</span>';
      }
    }else{
      return;
    }
  }
  if (substr($change,0,4)=='Link') {
    $isLink=true;
    $linkExpl=explode('|',$change);
    $linkedClass=$linkExpl[2];
    $linkedId=$linkExpl[3];
  }
  if ($objectClass=='Ticket' and ! securityCheckDisplayMenu(null, $objectClass)) $objectClass='TicketSimple';
  $rightAcces=SqlElement::getSingleSqlElementFromCriteria('HabilitationOther',array('idProfile'=>$prof,'scope'=>'combo'));
  $testObj=new $objectClass($objectId,true);
  if (securityCheckDisplayMenu(null, $objectClass) and securityGetAccessRightYesNo('menu'.$objectClass, 'read', $testObj)=="YES") {
    //if($rightAcces->rightAccess==1){
      $gotoAndStyle=' class="streamLink '.((isNewGui())?'classLinkName':'').'" style="margin-left:18px;" onClick="gotoElement(\''.htmlEncode($objectClass).'\',\''.htmlEncode($objectId).'\')"';
    //}
  }else{
    return;
  }
  $elementName = '<span '.$gotoAndStyle.'><div style="width:16px;position:absolute;top:9px;">'.formatIcon($objectClass, 16).'</div>&nbsp;'.i18n(str_replace('Simple','',$objectClass)).'&nbsp;#'.$objectId.'</span>';
  if ($origin=='activityStream') {
    $tmpName=SqlList::getNameFromId($objectClass, $objectId);
    if ($tmpName!=$objectId) $elementName.='&nbsp;|&nbsp;'.$tmpName;
  } 
  if($operation=='update' and $change=='idStatus'){
    $newStatus=new Status($newVal);
    $oldStatus=new Status($oldVal);
    $oldStatusName='<span style="font-weight:bold;">'.$oldStatus->name.'</span>';
    $newStatusName='<span style="font-weight:bold;">'.$newStatus->name.'</span>';
    $text=i18n('changeStatusStream',array($oldStatusName,$newStatusName));
    $reftText=$elementName.'&nbsp;|&nbsp;';    
    $icon=formatIcon("ChangedStatus",22);
  }else if($operation=='insert'){
    $reftText=$elementName.'&nbsp;|&nbsp;';
    $icon=formatIcon("NewElement",22);
    if($isAssign){
      $text=i18n('assignResource').'&nbsp;'.$resourceName;
      $icon=formatIcon("ChangedStatus",22);
    }else if($isAff){
      $text=i18n('addedAffResource').'&nbsp;'.$resourceName;
      $icon=formatIcon("ChangedStatus",22);
    }else if($isDovVers){
      $text=i18n('addedDocVersion').'&nbsp;'.$docVers;
    }else if($isTestCaseRun){
      $text=i18n('addedTestCase').'&nbsp;'.$testSession;
    }else if($isLink){
      $gotoLink='';
      if ( securityCheckDisplayMenu(null, $linkedClass) and securityGetAccessRightYesNo('menu'.$linkedClass, 'read', '')=="YES") {
        $gotoLink=' class="streamLink '.((isNewGui())?'classLinkName':'').'" style="margin-left:18px;" onClick="gotoElement(\''.htmlEncode($linkedClass).'\',\''.htmlEncode($linkedId).'\')"';
      }
      $text=i18n('addedLink').'<span '.$gotoLink.'>'.i18n($linkedClass).' #'.intval($linkedId).'</span>';
      $icon=formatIcon("LinkStream",22);
    }else if($attachment){
      $icon=formatIcon("ChangedStatus",22);
      $text=i18n('addedAttachment').'&nbsp;'.$attName;
    }else{
      $text=i18n('createdElementStream');
    }
  }else if($operation=='delete'){
    $reftText=$elementName.'&nbsp;|&nbsp;';
    $text=i18n('deletedElementStream');
    $icon=formatIcon("DeleteElement",22);
    if($isLink){
      $text=i18n('deletedLink').'&nbsp;'.i18n($linkedClass).' #'.intval($linkedId);
      $icon=formatIcon("LinkStream",22);
    } else if ($isAff) {
      $icon=formatIcon("ChangedStatus",22);
    } else if ($isAssign) {
      $icon=formatIcon("ChangedStatus",22);
    }
  } else {
    return;
  }
  $result='';
  if($origin=='objectStream'){
    $result.= '<tr style="height:100%;">';
    $result.= '  <td colspan="6" class="noteData" style="width:100%;border-top:0;font-size:100% !important;">';
    $result.= '    <div style="float:left;">';
    $result.= '      <div style="float:left;width:22px;margin-left:6px;margin-bottom:6px;">';
    $result.= '        <div style="float:left;clear:left;margin-top:6px;width:22px;position:relative">';
    $result.=          $icon;
    $result.= '        </div>';
    $result.= '      </div>';
    if (! $inlineUserThumb) {
    $result.= '      <div style="float:left;clear:left;margin-top:6px;width:22px;">';
    $result.=          formatUserThumb($hist->idUser, $userName, 'Creator',22,'left');
    $result.= '      </div>';
    }
    $result.= '    </div>';
    $result.= '    <div style="margin-left:0px;margin-top:6px;">';
    $result.= '      <div style="margin-top:2px;margin-left:37px;">'.$userNameFormatted.'&nbsp;'.$text.'</div>';
    $result.= '      <div style="margin-top:3px;margin-left:37px;">'.formatDateThumb($date,null,"left",16).'</div>';
    $result.= '      <div style="margin-top:8px;margin-bottom:5px">'.htmlFormatDateTime($date,false).'</div>';
    $result.='     <div>';
    $result.= '  </td>';
    $result.= '</tr>';
  }else{
    $result.= '<tr style="height:100%;">';
    $result.= '  <td colspan="6" class="noteData" style="border-left:unset;width:100%;border-top:0;font-size:100% !important;position:relative;">';
    $result.= '    <div style="float:left;width:22px;margin-left:6px;margin-top:6px;margin-bottom:6px">';
    $result.= '      <div style="float:left;max-width:26px">';
    $result.=          $icon;
    $result.= '      </div>';
    if (! $inlineUserThumb) {
    $result.= '      <div style="float:left;clear:left;margin-top:6px;width:22px;">';
    $result.=          formatUserThumb($hist->idUser, $userName, 'Creator',22,'left');
    $result.= '      </div>';
    }
    $result.= '    </div>';    
    $result.= '    <div style="float:left;width:90%;'.((isNewGui())?'':'margin-top:6px').';display:inline-block;margin-left:5px;margin-bottom:6px;">';
    $result.= '      <div style="margin-top:2px;margin-left:10px;">'.$reftText.''.$userNameFormatted.'&nbsp;'.$text.'</div>';
    $result.= '      <div style="margin-top:3px;margin-left:10px;position:relative;">'.formatDateThumb($date,null,"left",16).'</div>';
    $result.= '      <div style="margin-top:8px;margin-left:10px;">&nbsp;'.htmlFormatDateTime($date,false).'</div>';
    $result.='     <div>';
    if (Parameter::getGlobalParameter('logLevel')>=3) {
      $result.= '      <div style="position:absolute;right:10px;top:6px;color:grey">';
      $result.=        'histo#'.$hist->id;
      $result.= '      </div>';
    }
    $result.= '  </td>';
    $result.= '</tr>';
 }
 return $result;
}

function activityStreamDisplayMail($mail,$origin,$activityStreamShowClosed=false){
  $reftText='';
  $elementName='';
  $inlineUserThumb=true;
  $gotoAndStyle=' style="margin-left:18px;" ';
  $userId = $mail->idUser;
  $userName = ($mail->idUser!='')?SqlList::getNameFromId ( 'Affectable', $userId ):lcfirst(i18n('unknown'));
  $dest=$mail->mailTo;
  $mailStatus=$mail->mailStatus;
  $date=$mail->mailDateTime;
  $objectClass=( $mail->idMailable!='')?SqlList::getNameFromId ( 'Mailable', $mail->idMailable,false):'';
  $objectId=$mail->refId;
  
  if ($inlineUserThumb) $userNameFormatted = '<span style="font-weight:bold;position:relative;margin-left:20px;"><div style="position:absolute;top:'.((isNewGui())?'1':'-1').'px;left:-30px;width:25px;">'.formatUserThumb($userId, $userName, 'Creator',16).'&nbsp;</div><strong>' . $userName . '</strong></span>';
  else $userNameFormatted = '<span style="font-weight:bold;"><strong>' . $userName . '</strong></span>';
  $testObj=($objectClass)?new $objectClass($objectId):null;
  if ($objectClass=='Ticket' and !securityCheckDisplayMenu(null, $objectClass)) $objectClass='TicketSimple';
  if ($mail->idMailable!='' and  securityCheckDisplayMenu(null, $objectClass) and securityGetAccessRightYesNo('menu'.$objectClass, 'read', $testObj)=="YES") {
    $gotoAndStyle=' class="streamLink '.((isNewGui())?'classLinkName':'').'" style="margin-left:18px;" onClick="gotoElement(\''.htmlEncode($objectClass).'\',\''.htmlEncode($objectId).'\')"';
  } else {
    return;
  }
  if($origin=='activityStream' and $objectClass!='' and $objectId!=''){
    $obj= new $objectClass($objectId);
    if($obj->idle==1 and !$activityStreamShowClosed)return;
  }
  if($mail->idMailable!='')$elementName = '<span '.$gotoAndStyle.'><div style="width:16px;position:absolute;top:9px">'.formatIcon($objectClass, 16).'</div>&nbsp;'.i18n(str_replace('Simple','',$objectClass)).'&nbsp;#'.$objectId.'</span>';
  if ($origin=='activityStream') {
    $tmpName=SqlList::getNameFromId($objectClass, $objectId);
    if ($tmpName!=$objectId) $elementName.='&nbsp;|&nbsp;'.$tmpName;
  }
  
  if($mail->idMailable!='')$reftText=$elementName.'&nbsp;|&nbsp;';
  $icon=formatIcon("MailSentMsg",22);
  $text=lcfirst(i18n('mailActivityStrameSendTo',array($dest)));
  $showMail="";
  if ( securityCheckDisplayMenu(null, get_class($mail)) and securityGetAccessRightYesNo('menu'.get_class($mail), 'read', $mail)=="YES") {
    $showMail='<div class="roundedButtonSmall" style="width:20px;height:16px;display:inline-block;margin-left:20px;" title="'.i18n('showMail',array($mail->id)).'"><div class="iconGoto" style="z-index:500;width:16px;height:10px;display:inline-block;padding-right:5px;" onClick="gotoElement(\''.htmlEncode(get_class($mail)).'\',\''.htmlEncode($mail->id).'\')" title="'.i18n('showMail',array($mail->id)).'" style="widht:16px;height:16px;"></div></div>';
  }
  $rightWidth=(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))-30).'px';
  if (isNewGui())   {
    if (Parameter::getUserParameter('paramScreen')=='top') {
      if (Parameter::getUserParameter('paramRightDiv')=='bottom') {
        $menuLeftOpen=(Parameter::getUserParameter('isMenuLeftOpen')=='false')?0:1;
        if ($menuLeftOpen) $innerMailWidth=(intval((intval(getSessionValue('screenWidth'))-250)*0.7)-50).'px'; // menu open
        else $innerMailWidth=(intval(intval(getSessionValue('screenWidth'))*0.7)-50).'px';
      } else { // trailing
        $innerMailWidth=(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))-40).'px';
      }
    } else { // 'left'
      if (Parameter::getUserParameter('paramRightDiv')=='bottom') {
        $innerMailWidth=(intval(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))*0.7)-50).'px';
      } else { // trailing
        $innerMailWidth=(intval(Parameter::getUserParameter('contentPaneRightDetailDivWidth'.$objectClass))-40).'px';
      }
    }
    $maxWidth=(intval($innerMailWidth)+50).'px';
  }
  $result='';
  if($origin=='activityStream'){
    $result.= '<tr style="height:100%;">';
    $result.= '  <td colspan="6" class="noteData" style="border-left:unset;width:100%;border-top:0;font-size:100% !important;position:relative;">';
    $result.= '    <div style="float:left;width:22px;margin-left:6px;margin-top:6px;margin-bottom:6px">';
    $result.= '      <div style="float:left;max-width:26px">';
    $result.=          $icon;
    $result.= '      </div>';
    if (! $inlineUserThumb) {
      $result.= '      <div style="float:left;clear:left;margin-top:6px;width:22px;">';
      $result.=          formatUserThumb($mail->idUser, $userName, 'Creator',22,'left');
      $result.= '      </div>';
    }
    $result.= '    </div>';
    $result.= '    <div style="float:left;width:90%;'.((isNewGui())?'':'margin-top:6px').';display:inline-block;margin-left:5px;margin-bottom:6px;">';
    $result.= '      <div style="margin-top:2px;margin-left:10px;">'.$reftText.''.$userNameFormatted.'&nbsp;'.$text.$showMail.'</div>';
    $result.= '      <div style="margin-top:3px;margin-left:10px;position:relative;">'.formatDateThumb($date,null,"left",16).'</div>';
    $result.= '      <div style="margin-top:8px;margin-left:10px;">&nbsp;'.htmlFormatDateTime($date,false).'</div>';
    $result.='     <div>';
    $result.= '    <div  class="activityStreamMailTitle" style="width:'.((isset($innerMailWidth))?$innerMailWidth:'90%').';margin-top:16px;display:block;margin-left:5px;margin-bottom:6px;">';
    $result.=       htmlEncode($mail->mailTitle);
    $result.='     <div>';
    if (Parameter::getGlobalParameter('logLevel')>=3) {
      $result.= '      <div style="position:absolute;right:10px;top:6px;color:grey">';
      $result.=        'mail#'.$mail->id;
      $result.= '      </div>';
    }
    $result.= '  </td>';
    $result.= '</tr>';
  }else{
    $result.= '<tr style="height:100%;">';
    $result.= '  <td colspan="6" class="noteData" style="width:100%;border-top:0;font-size:100% !important;">';
    $result.= '    <div style="float:left;">';
    $result.= '      <div style="float:left;width:22px;margin-left:6px;margin-bottom:6px;">';
    $result.= '        <div style="float:left;clear:left;margin-top:6px;width:22px;position:relative">';
    $result.=          $icon;
    $result.= '        </div>';
    $result.= '      </div>';
    if (! $inlineUserThumb) {
      $result.= '      <div style="float:left;clear:left;margin-top:6px;width:22px;">';
      $result.=          formatUserThumb($hist->idUser, $userName, 'Creator',22,'left');
      $result.= '      </div>';
    }
    $result.= '    </div>';
    $result.= '    <div style="margin-left:0px;margin-top:6px;">';
    $result.= '      <div style="margin-top:2px;margin-left:37px;">'.$userNameFormatted.'&nbsp;'.$text.$showMail.'</div>';
    $result.= '      <div style="margin-top:3px;margin-left:37px;">'.formatDateThumb($date,null,"left",16).'</div>';
    $result.= '      <div style="margin-top:8px;margin-bottom:5px">'.htmlFormatDateTime($date,false).'</div>';
    $result.='     <div>';
    $result.= '    <div class="activityStreamMailTitle" style="width:'.((isset($innerMailWidth))?$innerMailWidth:'90%').';margin-top:16px;display:block;margin-left:5px;margin-bottom:6px;">';
    $result.=       htmlEncode($mail->mailTitle);
    $result.='     <div>';
    $result.= '  </td>';
    $result.= '</tr>';
  }
  return $result;
}

function suppr_accents($str, $encoding='utf-8'){
  $str = htmlentities($str, ENT_NOQUOTES, $encoding);
  $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
  $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
  $str = preg_replace('#&[^;]+;#', '', $str);
  return $str;
}
?>