<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.1.1
*/namespace
Adminer;if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];const
VERSION="6.1.1";error_reporting(24575);set_error_handler(function($ud,$wd){return!!preg_match('~^Undefined (array key|offset|index)~',$wd);},E_WARNING|E_NOTICE);$Zd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Zd||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$W){$cn=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($cn)$$W=$cn;}}$_COOKIE=array_filter($_COOKIE,'is_scalar');if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$jc=adminer()->credentials();$H=Driver::connect($jc[0],$jc[1],$jc[2]);return(is_object($H)?$H:null);}function
idf_unescape($t){if(!preg_match('~^[`\'"[]~',$t))return$t;$ng=substr($t,-1);return
str_replace($ng.$ng,$ng,substr($t,1,-1));}function
q($P){return
connection()->quote($P);}function
idx($Ga,$w,$j=null){return($Ga&&array_key_exists($w,$Ga)?$Ga[$w]:$j);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|number|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
text_type(){return'char|text'.(JUSH=="sql"?'|enum|set':'');}function
is_user_type($T){return
in_array($T,idx(driver()->structuredTypes(),lang(0),array()));}function
full_type_sql(array$l){$T=$l["type"];return(is_user_type($T)?idf_escape($T).substr($l["full_type"],strlen($T)):$l["full_type"]);}function
is_searchable(array$l,array$W){if(!isset($l["privileges"]["where"]))return
false;if(preg_match('~NULL$~',$W["op"]))return
true;$T=$l["type"];$Dk=$W["val"];$Za='binary$|bytea|raw|image|bfile|^vector$'.(JUSH=="mssql"?'|^timestamp$':'|^bit').(JUSH=="oracle"?'|^blob|^long|rowid':'');if(preg_match("~$Za~",$T))return
false;if(preg_match(number_type(),$T)){$Th='-?\d+(\.\d+)?';return(bool)preg_match('~^'.$Th.(preg_match('~IN$~',$W["op"])?"( *, *$Th)*":'').'$~',$Dk);}if(preg_match('~^(small)?date|^timestamp~',$T))return(bool)preg_match('~^\d+-\d+-\d+~',$Dk);if(preg_match('~^time~',$T))return(bool)preg_match('~^\d+:\d+~',$Dk);if(preg_match('~^bool~',$T)||(JUSH=="mssql"&&$T=="bit"))return(bool)preg_match('~^(t|f|true|false|[01])$~i',$Dk);return
true;}function
remove_slashes(array$Y,$Zd=false){$H=array();foreach($Y
as$w=>$W)$H[stripslashes($w)]=(is_array($W)?remove_slashes($W,$Zd):($Zd?$W:stripslashes($W)));return$H;}function
bracket_escape($t,$Sa=false){static$Cm=array(':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5');return
strtr($t,($Sa?array_flip($Cm):$Cm));}function
url_escape($P){static$Cm=array();if(!$Cm){$Cm=array(' '=>'+');foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$nb)$Cm[$nb]=sprintf('%%%02X',ord($nb));for($r=0;$r<256;$r++){if($r<32||$r>126)$Cm[chr($r)]=sprintf('%%%02X',$r);}}return
strtr((string)$P,$Cm);}function
min_version($Bn,$Ig="",$g=null){$g=connection($g);$al=$g->server_info;if($Ig&&preg_match('~([\d.]+)-MariaDB~',$al,$_)){$al=$_[1];$Bn=$Ig;}return$Bn&&version_compare($al,$Bn)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_set($ti,$X){return(function_exists('ini_set')?\ini_set($ti,$X):false);}function
ini_bool($Df){$W=ini_get($Df);return(preg_match('~^(on|true|yes)$~i',$W)||(int)$W);}function
ini_bytes($Df){$W=ini_get($Df);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
max_input_vars($I,$Hi){$Mg=(int)ini_get("max_input_vars");return($Mg?(int)floor(($Mg-$Hi)/$I):0);}function
max_input_vars_error(){$Df="max_input_vars";return
lang(1,"<b>$Df = ".ini_get($Df)."</b>");}function
sid(){static$H;if($H===null)$H=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$H;}function
set_password($An,$M,$U,$D){$_SESSION["pwds"][$An][$M][$U]=($_COOKIE["adminer_key"]&&is_string($D)?array(encrypt_string($D,$_COOKIE["adminer_key"])):$D);}function
get_password(){$H=get_session("pwds");if(is_array($H))$H=($_COOKIE["adminer_key"]?decrypt_string($H[0],$_COOKIE["adminer_key"]):false);return$H;}function
get_val($F,$l=0,$Qb=null){$Qb=connection($Qb);$G=$Qb->query($F);if(!is_object($G))return
false;$I=$G->fetch_row();return($I?$I[$l]:false);}function
get_vals($F,$d=0){$H=array();$G=connection()->query($F);if(is_object($G)){while($I=$G->fetch_row())$H[]=$I[$d];}return$H;}function
get_key_vals($F,$g=null,$dl=true){$g=connection($g);$H=array();$G=$g->query($F);if(is_object($G)){while($I=$G->fetch_row()){if($dl)$H[$I[0]]=$I[1];else$H[]=$I[0];}}return$H;}function
get_rows($F,$g=null,$k="<p class='error'>"){$Qb=connection($g);$H=array();$G=$Qb->query($F);if(is_object($G)){while($I=$G->fetch_assoc())$H[]=$I;}elseif(!$G&&!$g&&$k&&(defined('Adminer\PAGE_HEADER')||$k=="-- "))echo$k.adminer()->error()."\n";return$H;}function
unique_array($I,array$v){foreach($v
as$u){if(preg_match("~^(PRIMARY|UNIQUE)$~",$u["type"])&&!$u["partial"]){$H=array();foreach($u["columns"]as$w){if(!isset($I[$w]))continue
2;$H[$w]=$I[$w];}return$H;}}}function
where_function($xe,$d,array$l){if($xe=="md5")return
driver()->md5($d,$l)?:$d;return(in_array($xe,driver()->functions)||in_array($xe,driver()->grouping)?apply_sql_function($xe,$d):$d);}function
where(array$Z,array$m=array()){$H=array();foreach((array)$Z["where"]as$w=>$W){$w=bracket_escape($w,true);$d=idf_escape($w);$l=idx($m,$w,array());$Td=$l["type"];$Qf=$l&&(is_blob($l)||preg_match('~binary~',$Td));$H[]=$d.($Qf&&!is_utf8($W)?" = ".driver()->quoteBinary($W):(JUSH=="sql"&&$Td=="json"?" = CAST(".q($W)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$l["full_type"])?"::jsonb = ".q($W)."::jsonb":(JUSH=="sql"&&is_numeric($W)&&preg_match('~\.~',$W)?" LIKE ".q($W):(JUSH=="mssql"&&strpos($Td,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$W)):" = ".unconvert_field($l,q($W)))))));if(JUSH=="sql"&&preg_match('~char|text~',$Td)&&preg_match("~[^ -@]~",$W))$H[]="$d = ".q($W)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$w)$H[]=idf_escape($w)." IS NULL";foreach((array)$Z["col"]as$r=>$Bb){$W=idx($Z["val"],$r);$H[]=where_function(idx($Z["fun"],$r),idf_escape($Bb),idx($m,$Bb,array())).($W!==null?" = ".q($W):" IS NULL");}return
implode(" AND ",$H);}function
where_columns(array$m){$H=array();foreach((array)$_GET["null"]as$w)$H[$w]=true;foreach(array_keys((array)$_GET["where"])as$w)$H[bracket_escape($w,true)]=true;foreach((array)$_GET["col"]as$Bb)$H[$Bb]=true;return
array_intersect_key($H,$m);}function
where_check($W,array$m=array()){parse_str($W,$qb);remove_slashes(array(&$qb));return
where($qb,$m);}function
where_link($r,$d,$X,$qi="="){$ni=($X!==null?$qi:"IS NULL");return"&where[$r][col]=".url_escape($d).($ni!=first(adminer()->operators())?"&where[$r][op]=".url_escape($ni):"")."&where[$r][val]=".url_escape($X);}function
convert_fields(array$e,array$m,array$L=array()){$H="";foreach($e
as$w=>$W){if($L&&!in_array(idf_escape($w),$L))continue;$Ha=convert_field($m[$w]);if($Ha)$H
.=", $Ha AS ".idf_escape($w);}return$H;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),array(";"=>"%3B",","=>"%2C"));}function
cookie($A,$X,$xg=2592000){header("Set-Cookie: $A=".rawurlencode($X).($xg?"; expires=".gmdate("D, d M Y H:i:s",time()+$xg)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($A=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
get_url($ln,$Zb){$http_response_header=null;$vd=array();set_error_handler(function($ud,$k)use(&$vd){$vd[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$k);return
true;});$H=file_get_contents($ln,false,$Zb);restore_error_handler();$Ue=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);return
array($H,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($Ue,0,''),$_)?$_[1]:''),(array)$Ue,($H===false?implode("\n",$vd):''),);}function
json_decode_exact($Yf){$Yf=preg_replace('~"(\\\\u0001(?:[^"\\\\]|\\\\.)*+")|"(?:[^"\\\\]|\\\\.)*+"(*SKIP)(*FAIL)~','"\\\\u0001$1',$Yf);return
json_decode(preg_replace('~"(?:[^"\\\\]|\\\\.)*+"(*SKIP)(*FAIL)|-?\d[-+.\deE]*+~','"\\\\u0001$0"',$Yf));}function
json_scalar($W){return(is_string($W)&&substr($W,0,1)=="\1"?substr($W,1):$W);}function
json_encode_exact($W,$fe=0){return
preg_replace('~"\\\\u0001(-?\d[^"\\\\]*)"|(")\\\\u0001(\\\\u0001(?:[^"\\\\]|\\\\.)*+")|"(?:[^"\\\\]|\\\\.)*+"(*SKIP)(*FAIL)~','$1$2$3',json_encode($W,$fe));}function
get_settings($dc){parse_str($_COOKIE[$dc],$el);return$el;}function
get_setting($w,$dc="adminer_settings",$j=null){return
idx(get_settings($dc),$w,$j);}function
save_settings(array$el,$dc="adminer_settings"){$X=http_build_query($el+get_settings($dc));cookie($dc,$X);$_COOKIE[$dc]=$X;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==PHP_SESSION_NONE))session_start();}function
stop_session($ie=false){$on=ini_bool("session.use_cookies");if(!$on||$ie){session_write_close();if($on&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($w){return$_SESSION[$w][DRIVER][SERVER][$_GET["username"]];}function
set_session($w,$W){$_SESSION[$w][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($An,$M,$U,$i=null){$kn=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($i!==null?"db|":"").($An=='mssql'||$An=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$kn,$_);return"$_[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($An!="server"||$M!=""?url_escape($An)."=".url_escape($M)."&":"")."username=".url_escape($U).($i!=""?"&db=".url_escape($i):"").($_[2]?"&$_[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Eg,$ch=null){if($ch!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Eg!==null?$Eg:$_SERVER["REQUEST_URI"]))][]=$ch;}if($Eg!==null){if($Eg=="")$Eg=".";header("Location: $Eg");exit;}}function
query_redirect($F,$Eg,$ch,$Sj=true,$Cd=true,$Nd=false,$qm=""){if($Cd){$_l=microtime(true);$Nd=!connection()->query($F);$qm=format_time($_l);}$tl=($F?adminer()->messageQuery($F,$qm,$Nd):"");if($Nd){adminer()->error
.=adminer()->error().$tl.script("messagesPrint();")."<br>";return
false;}if($Sj)redirect($Eg,$ch.$tl);return
true;}class
Queries{static$queries=array();static$start=0;}function
remember_query($F){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$F:(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";");}function
queries($F){remember_query($F);return
connection()->query($F);}function
apply_queries($F,array$S,$xd='Adminer\table'){foreach($S
as$Q){if(!queries("$F ".$xd($Q)))return
false;}return
true;}function
queries_redirect($Eg,$ch,$Sj){$Mj=implode("\n",Queries::$queries);$qm=format_time(Queries::$start);return
query_redirect($Mj,$Eg,$ch,$Sj,false,!$Sj,$qm);}function
format_time($_l){return
lang(2,max(0,microtime(true)-$_l));}function
relative_uri($kn=''){return
preg_replace_callback('~^[^?]*~',function($_){return
str_replace(":","%3A",$_[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',($kn?:$_SERVER["REQUEST_URI"])));}function
remove_from_uri($Oi=""){return
substr(preg_replace("~(?<=[?&])($Oi".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($A,$yc=false){$Vd=$_FILES[$A];if(!$Vd)return
null;foreach($Vd
as$w=>$W)$Vd[$w]=(array)$W;$H=array();foreach($Vd["error"]as$w=>$k){if($k)return$k;$n=$Vd["name"][$w];$ym=$Vd["tmp_name"][$w];$Xb=file_get_contents($yc&&preg_match('~\.gz$~',$n)?"compress.zlib://$ym":$ym);if($yc){$_l=substr($Xb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$_l))$Xb=iconv("utf-16","utf-8",$Xb);elseif($_l=="\xEF\xBB\xBF")$Xb=substr($Xb,3);}$H[]=array($n,$Xb);}return$H;}function
get_file($w,$yc=false,$Ec=""){$Yd=get_files($w,$yc);if(!is_array($Yd))return$Yd;$H='';foreach($Yd
as$Vd){$Xb=$Vd[1];$H
.=$Xb;if($Ec)$H
.=(preg_match("($Ec\\s*\$)",$Xb)?"":$Ec)."\n\n";}return$H;}function
upload_error($k){$Ug=($k==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($k?lang(3).($Ug?" ".lang(4,$Ug):""):lang(5));}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
utf8_length($W){return
strlen(preg_replace('~[\x80-\xBF]~','',$W));}function
format_number($W){preg_match('~^#+([^#0]+)(?:(#+)\1)?(#*0)$~u',lang(6),$_);$il=strlen($_[3]);$H=number_format($W,0,".","");$H=preg_replace('~\B(?=(\d{'.(strlen($_[2])?:$il).'})*\d{'.$il.'}$)~',$_[1],$H);return
strtr($H,preg_split('~~u',lang(7),-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$R,$w){$W=idx($R,$w,'?');if(!is_numeric($W))return
h($W);if($W<0)return'?';$Ca=($w=="Rows"&&(JUSH=="sqlite"||$R["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));return($Ca?"~ ":"").format_number($W);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($Q,$Od=false){$H=table_status($Q,$Od);return($H?reset($H):array("Name"=>$Q));}function
column_foreign_keys($Q){$H=array();foreach(adminer()->foreignKeys($Q)as$o){foreach($o["source"]as$W)$H[$W][]=$o;}return$H;}function
fields_from_edit(){$H=array();foreach((array)$_POST["field_keys"]as$w=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$w];$_POST["fields"][$W]=$_POST["field_vals"][$w];}}foreach((array)$_POST["fields"]as$w=>$W){$A=bracket_escape($w,true);$H[$A]=array("field"=>$A,"full_type"=>"","type"=>"","privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>true,"auto_increment"=>($A==driver()->primary),);}return$H;}function
dump_headers($if,$uh=false){$H=adminer()->dumpHeaders($if,$uh);$Ji=$_POST["output"];if($Ji!="text"||$H=="tar"){$Mb=($Ji!="text"&&$Ji!="file"&&preg_match('~^[0-9a-z]+$~',$Ji)?".$Ji":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($if).".$H$Mb");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$H;}function
dump_csv(array$I){$Pm=$_POST["format"]=="tsv";foreach($I
as$w=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Pm?'\t':'[,;]|^$').'~',$W))$I[$w]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($Pm?"\t":";")),$I)."\r\n";}function
parse_csv($mc,$Ok){$H=array();preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$mc,$Kg);foreach($Kg[0]as$I){preg_match_all("~((?>\"[^\"]*\")+|[^$Ok]*)$Ok~",$I.$Ok,$Lg);$H[]=$Lg[1];}return$H;}function
csv_value($W){return(preg_match('~^".*"$~s',$W)?str_replace('""','"',substr($W,1,-1)):$W);}function
apply_sql_function($p,$d){return($p?($p=="unixepoch"?"DATETIME($d, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$d)"):$d);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($n){if(is_link($n))return;$pe=@fopen($n,"c+");if(!$pe)return;@chmod($n,0660);if(!flock($pe,LOCK_EX)){fclose($pe);return;}return$pe;}function
file_write_unlock($pe,$qc){rewind($pe);fwrite($pe,$qc);ftruncate($pe,strlen($qc));file_unlock($pe);}function
file_unlock($pe){flock($pe,LOCK_UN);fclose($pe);}function
first(array$Ga){return
reset($Ga);}function
password_file($gc){$n=get_temp_dir()."/adminer.key";if(!$gc&&!file_exists($n))return'';$pe=file_open_lock($n);if(!$pe)return'';$H=stream_get_contents($pe);if(!$H){$H=rand_string();file_write_unlock($pe,$H);}else
file_unlock($pe);return$H;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($W,$z,array$l,$om,array$ij=array()){if(is_array($W)){$H="";if(array_filter($W,'is_array')==array_values($W)){$dg=array();foreach($W
as$V)$dg+=array_fill_keys(array_keys($V),null);foreach(array_keys($dg)as$ag)$H
.="<th>".h($ag);foreach($W
as$V){$H
.="<tr>";foreach(array_merge($dg,$V)as$vn)$H
.="<td>".select_value($vn,$z,$l,$om,$ij);}}else{foreach($W
as$ag=>$V)$H
.="<tr>".($W!=array_values($W)?"<th>".h($ag):"")."<td>".select_value($V,$z,$l,$om,$ij);}return"<table>$H</table>";}if(!$z)$z=adminer()->selectLink($W,$l);if($z===null){if(is_mail($W))$z="mailto:$W";if(is_url($W))$z=$W;}$W=driver()->value($W,$l);$H=adminer()->editVal($W,$l);if($H!==null){if(!is_utf8($H))$H="\0";elseif($om!=""&&is_shortable($l))$H=shorten_utf8($H,max(0,+$om),"",$ij);else$H=highlight_matches($H,$ij);}return
adminer()->selectVal($H,$z,$l,$W);}function
is_blob(array$l){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$l["type"])&&!in_array($l["type"],idx(driver()->structuredTypes(),lang(0),array()));}function
is_identity_always(array$l){return$l["auto_increment"]&&(JUSH=="mssql"||$l["default"]=="GENERATED ALWAYS AS IDENTITY");}function
is_mail($kd){$Ja='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Wc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$hj="$Ja+(\\.$Ja+)*@($Wc?\\.)+$Wc";return
is_string($kd)&&preg_match("(^$hj(,\\s*$hj)*\$)i",$kd);}function
is_url($P){$Wc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($Wc?\\.)+$Wc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$P);}function
is_ipv6($pa){$q='[\da-f]{1,4}';$Pf='\d{1,3}(\.\d{1,3}){3}';return(bool)preg_match("~^(($q:){7}$q|($q:){6}$Pf|(($q:)*$q)?::(($q:)*($q|$Pf))?)$~iD",$pa);}function
is_shortable(array$l){return!preg_match('~'.number_type().'|date|time|year~',$l["type"]);}function
url_host($ef){return(strpos($ef,":")!==false?"[$ef]":$ef);}function
server_parts(array$bj){return
array("scheme"=>(string)$bj["scheme"],"host"=>(string)$bj["host"],"port"=>(string)$bj["port"],"socket"=>(string)$bj["socket"],"path"=>(string)$bj["path"],);}function
parse_server($M){if($M=="")return
server_parts(array());if($M[0]==":"&&!is_ipv6($M)){$gk=substr($M,1);if(preg_match('~^\d+$~D',$gk))return
server_parts(array("port"=>$gk));return(preg_match('~^/[-\w.:/]*$~D',$gk)?server_parts(array("socket"=>$gk)):null);}$Bk="";if(preg_match('~^([-+.\w]+)://~',$M,$_)){$Bk=strtolower($_[1]);$M=substr($M,strlen($_[0]));}if(preg_match('~^\[(.+)](:(\d+))?(/[-\w./]*)?$~D',$M,$_))return(is_ipv6($_[1])?server_parts(array("scheme"=>$Bk,"host"=>$_[1],"port"=>$_[3],"path"=>$_[4])):null);if(is_ipv6($M))return
server_parts(array("scheme"=>$Bk,"host"=>$M));if(preg_match('~^(/[-\w./]*)(:(\d+))?$~D',$M,$_))return
server_parts(array("scheme"=>$Bk,"host"=>$_[1],"port"=>$_[3]));return(preg_match('~^([-\w.]*)(:(\d+))?(/[-\w./]*)?$~D',$M,$_)?server_parts(array("scheme"=>$Bk,"host"=>$_[1],"port"=>$_[3],"path"=>$_[4])):null);}function
count_rows($Q,array$Z,$Rf,array$q){$F=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($Rf&&(JUSH=="sql"||count($q)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$q).")$F":"SELECT COUNT(*)".($Rf?" FROM (SELECT 1$F GROUP BY ".implode(", ",$q).") x":$F));}function
slow_query($F){$i=adminer()->database();$rm=adminer()->queryTimeout();$kl=driver()->slowQuery($F,$rm);$g=null;if(!$kl&&support("kill")){$g=connect();if($g&&($i==""||$g->select_db($i))){$eg=number(get_val(connection_id(),0,$g));echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$eg&token=".get_token()."'); }, 1000 * $rm);");}}ob_flush();flush();$H=@get_key_vals(($kl?:$F),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$H;}function
get_token(){$Pj=rand(1,1e6);return($Pj^$_SESSION["token"]).":$Pj";}function
verify_token(){list($zm,$Pj)=explode(":",$_POST["token"]);return($Pj^$_SESSION["token"])==$zm&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"));}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($P,$Kc=""){$_a=array_flip(str_split(compress_alphabet()));$x=strlen($P);$yn=($x?13*($x-1)/2-$_a[$P[0]]:0);$Za="";$gk=0;$hk=0;for($r=1;$r<$x;$r+=2){$gk=($gk<<13)+$_a[$P[$r]]*93+$_a[$P[$r+1]];$hk+=13;while($hk>=8&&$yn>=8){$hk-=8;$yn-=8;$Za
.=chr($gk>>$hk);$gk&=(1<<$hk)-1;}}if($Za=="")return"";if($Kc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,array('dictionary'=>$Kc)),$Za,ZLIB_FINISH);return($Kc==""&&function_exists('gzinflate')?gzinflate($Za):inflate($Za,$Kc));}function
inflate($Za,$Kc=""){$ug=array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258);$vg=array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0);$Oc=array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577);$Qc=array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13);$H=$Kc;$E=0;do{$ae=inflate_bits($Za,$E,1);$T=inflate_bits($Za,$E,2);if(!$T){$E=($E+7)&~7;$x=inflate_bits($Za,$E,16);$E+=16;$H
.=substr($Za,$E>>3,$x);$E+=$x<<3;}else{if($T==1){$Cg=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Rc=array_fill(0,30,5);}else{$Bg=inflate_bits($Za,$E,5)+257;$Pc=inflate_bits($Za,$E,5)+1;$wi=array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);$ih=array_fill(0,19,0);$hh=inflate_bits($Za,$E,4)+4;for($r=0;$r<$hh;$r++)$ih[$wi[$r]]=inflate_bits($Za,$E,3);$jh=inflate_table($ih);$wg=array();while(count($wg)<$Bg+$Pc){$Ml=inflate_symbol($Za,$E,$jh);if($Ml==16)$wg=array_merge($wg,array_fill(0,inflate_bits($Za,$E,2)+3,end($wg)));elseif($Ml==17)$wg=array_merge($wg,array_fill(0,inflate_bits($Za,$E,3)+3,0));elseif($Ml==18)$wg=array_merge($wg,array_fill(0,inflate_bits($Za,$E,7)+11,0));else$wg[]=$Ml;}$Cg=array_slice($wg,0,$Bg);$Rc=array_slice($wg,$Bg);}$Dg=inflate_table($Cg);$Tc=inflate_table($Rc);while(($Ml=inflate_symbol($Za,$E,$Dg))!=256){if($Ml<256)$H
.=chr($Ml);else{$x=$ug[$Ml-257]+inflate_bits($Za,$E,$vg[$Ml-257]);$Sc=inflate_symbol($Za,$E,$Tc);$bi=strlen($H)-$Oc[$Sc]-inflate_bits($Za,$E,$Qc[$Sc]);for($r=0;$r<$x;$r++)$H
.=$H[$bi+$r];}}}}while(!$ae);return($Kc==""?$H:substr($H,strlen($Kc)));}function
inflate_bits($Za,&$E,$fc){$H=0;for($r=0;$r<$fc;$r++){$H+=((ord($Za[$E>>3])>>($E&7))&1)<<$r;$E++;}return$H;}function
inflate_table(array$wg){$Q=array();$Ab=0;for($ab=1;$ab<=max($wg);$ab++){foreach($wg
as$Ml=>$x){if($x==$ab){$Q[$ab][$Ab]=$Ml;$Ab++;}}$Ab<<=1;}return$Q;}function
inflate_symbol($Za,&$E,array$Q){$Ab=0;$ab=0;do{$Ab=($Ab<<1)+inflate_bits($Za,$E,1);$ab++;}while(!isset($Q[$ab][$Ab]));return$Q[$ab][$Ab];}function
script($pl,$Bm="\n"){return"<script".nonce().">$pl</script>$Bm";}function
script_src($ln,$Ac=false){return"<script src='".h($ln)."'".nonce().($Ac?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($yd,$Le,$Da=null){$Fa=array();foreach(array_slice(func_get_args(),2)as$W)$Fa[]=json_encode($W,256);return" data-on$yd='".str_replace(array('&','<',"'"),array('&amp;','&lt;','&#039;'),"$Le(".implode(", ",$Fa).")")."'";}function
input_hidden($A,$X=""){return"<input type='hidden' name='".h($A)."' value='".h($X)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($P){return
str_replace(array('&','<','"',"'","\0"),array('&amp;','&lt;','&quot;','&#039;','&#0;'),$P);}function
nl_br($P){return
str_replace("\n","<br>",$P);}function
checkbox($A,$X,$tb,$ig="",$c="",$zb="",$kg=""){$H="<input type='checkbox' name='$A' value='".h($X)."'".($tb?" checked":"").($ig==""&&$zb?" class='$zb'":"").($kg?" aria-labelledby='$kg'":"").$c.">";return($ig!=""?"<label".($zb?" class='$zb'":"").">$H".h($ig)."</label>":$H);}function
optionlist($B,$Kk=null,$pn=false){$H="";foreach($B
as$ag=>$V){$vi=array($ag=>$V);if(is_array($V)){$H
.='<optgroup label="'.h($ag).'">';$vi=$V;}foreach($vi
as$w=>$W)$H
.='<option'.($pn||is_string($w)?' value="'.h($w).'"':'').($Kk!==null&&($pn||is_string($w)?(string)$w:$W)===$Kk?' selected':'').'>'.h($W);if(is_array($V))$H
.='</optgroup>';}return$H;}function
group_system(array$Bh,$Ak=false){$H=array();$Nl=array();foreach($Bh
as$A){if($Ak?driver()->isSystem(DB,$A):driver()->isSystem($A))$Nl[]=$A;else$H[]=$A;}if($Nl)$H[lang(8,'')]=$Nl;return$H;}function
html_select($A,array$B,$X="",$c="",$kg=""){static$ig=0;$jg="";if(!$kg&&substr($B[""],0,1)=="("){$ig++;$kg="label-$ig";$jg="<option value='' id='$kg'>".h($B[""]);unset($B[""]);}return"<select name='".h($A)."'".($kg?" aria-labelledby='$kg'":"")."$c>".$jg.optionlist($B,$X)."</select>";}function
html_radios($A,array$B,$X="",$Ok=""){$H="";foreach($B
as$w=>$W)$H
.="<label><input type='radio' name='".h($A)."' value='".h($w)."'".($w==$X?" checked":"").">".h($W)."</label>$Ok";return$H;}function
confirm($ch=""){return
on('click','confirmClick',$ch?:lang(9));}function
print_fieldset($s,$tg,$En=false){echo"<fieldset><legend>","<a href='#fieldset-$s' class='toggle'>$tg</a>","</legend>","<div id='fieldset-$s'".($En?"":" class='hidden'").">\n";}function
bold($bb,$zb=""){return($bb?" class='active $zb'":($zb?" class='$zb'":""));}function
js_escape($P){return
str_replace("<","\\x3C",addcslashes($P,"\r\n'\\"));}function
js_escape_re($P){return
addcslashes(preg_quote($P,"/"),"\r\n");}function
pagination_href($C){return
remove_from_uri("page|next").($C?"&page=$C".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($C,$nc){return" ".($C==$nc?($C?"<b>".($C+1)."</b>":$C+1):'<a href="'.h(pagination_href($C)).'">'.($C+1)."</a>");}function
hidden_fields(array$Ij,array$nf=array(),$yj=''){$H=false;foreach($Ij
as$w=>$W){if(!in_array($w,$nf)){if(is_array($W))hidden_fields($W,array(),$w);else{$H=true;echo
input_hidden(($yj?$yj."[$w]":$w),$W);}}}return$H;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
on_upload_progress(&$jn){$jn=(ini_bool("session.upload_progress.enabled")&&ini_get("session.upload_progress.name")?rand_string():"");return($jn?on('submit','uploadProgress',ME."upload=$jn",SESSION_NAME."=$jn"):"");}function
file_input($c,$gk=""){$Og="max_file_uploads";$Pg=ini_get($Og);$Ug="upload_max_filesize";$Vg=ini_bytes($Ug);$uj=ini_bytes("post_max_size");if($uj&&$uj<$Vg){$Ug="post_max_size";$Vg=$uj;}$Wg=ini_get($Ug);return(ini_bool("file_uploads")?"<input type='file'$c".on('change','fileChange',(int)$Pg,lang(10,"$Og = $Pg"),$Vg,lang(10,"$Ug = $Wg")).">$gk":lang(11));}function
enum_input($T,$c,array$l,$X,$nd=""){preg_match_all("~".driver()->enumLength."~",$l["length"],$Kg);$yj=($l["type"]=="enum"?"val-":"");$tb=(is_array($X)?in_array("null",$X):$X===null);$H=($l["null"]&&$yj?"<label><input type='$T'$c value='null'".($tb?" checked":"")."><i>$nd</i></label>":"");foreach($Kg[0]as$W){$W=stripcslashes(idf_unescape($W));$tb=(is_array($X)?in_array($yj.$W,$X):$X===$W);$H
.=" <label><input type='$T'$c value='".h($yj.$W)."'".($tb?' checked':'').'>'.h(adminer()->editVal($W,$l)).'</label>';}return$H;}function
input(array$l,$X,$p,$Qa=false,$gn=false){$A=h(bracket_escape($l["field"]));echo"<td class='function'>";$td=driver()->enumLength($l);if($td){$l["type"]="enum";$l["length"]=$td;}$B=($l["type"]=="enum"||$l["type"]=="set");if(is_array($X)&&!$p&&!$B)$p="json";$Yf=($p=="json"||preg_match('~^jsonb?$~',$l["full_type"]));if($Yf&&$X!=''&&(JUSH!="pgsql"||$l["type"]!="json")&&(is_array($X)||!$_POST["save"]))$X=(is_array($X)?json_encode($X,128|64|256):json_encode_exact(json_decode_exact($X),128|64|256));$fk=($gn&&is_identity_always($l));if($fk&&!$_POST["save"])$p=null;$ye=(isset($_GET["select"])||$fk?array("orig"=>lang(12)):array())+adminer()->editFunctions($l);$c=" name='fields[$A]".($B?"[]":"")."'".($Qa?" autofocus":"");echo
driver()->unconvertFunction($l)." ";$Q=$_GET["edit"]?:$_GET["select"];if($l["type"]=="enum")echo
h($ye[""])."<td>".adminer()->editInput($Q,$l,$c,$X);else{$Ne=(in_array($p,$ye)||isset($ye[$p]));$be=0;foreach($ye
as$w=>$W){if($w===""||!$W)break;$be++;}echo(count($ye)>1?"<select name='function[$A]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($ye,$p===null||$Ne?$p:"")."</select>":h(reset($ye)))."<td".($be&&count($ye)>1?on('input','skipOriginal',$be):"").">";$Ff=adminer()->editInput($Q,$l,$c,$X);if($Ff!="")echo$Ff;elseif(preg_match('~bool~',$l["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked":"")."$c value='1'>";elseif($l["type"]=="set")echo
enum_input("checkbox",$c,$l,(is_string($X)?explode(",",$X):$X));elseif(is_blob($l)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$A'>";elseif($Yf)echo"<textarea$c cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($nm=preg_match('~text|lob|memo~i',$l["type"]))||preg_match("~\n~",$X)){if($nm&&JUSH!="sqlite")$c
.=" cols='50' rows='12'";else{$J=min(12,substr_count($X,"\n")+1);$c
.=" cols='30' rows='$J'";}echo"<textarea$c>".h($X).'</textarea>';}else{$Um=driver()->types();$Sm=$Um[$l["type"]];$Ga=preg_match('~\[]~',$l["full_type"]);if($Ga)$Xg=0;elseif(preg_match('~date|time|year~',$l["type"])){$xj=($l["length"]==""&&JUSH=="pgsql"?6:$l["length"]);$qe=(preg_match('~time~',$l["type"])&&preg_match('~^[1-9]\d*$~',$xj)?$xj+1:0);$Xg=($Sm?$Sm+$qe:0);}elseif(!preg_match('~int|vector~',$l["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$l["length"],$_))$Xg=(preg_match("~binary~",$l["type"])?2:1)*$_[1]+($_[3]?1:0)+($_[2]&&!$l["unsigned"]?1:0);else$Xg=($Sm?$Sm+($l["unsigned"]?0:1):0);echo"<input".((!$Ne||$p==="")&&preg_match('~^'.int_type().'$~',$l["type"])&&!$Ga?" type='number'":"")." value='".h($X)."'".($Xg?" data-maxlength='$Xg'":"").(preg_match('~char|binary~',$l["type"])&&$Xg>20?" size='".($Xg>99?60:40)."'":"")."$c>";}echo
adminer()->editHint($Q,$l,$X),(count($ye)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$l){$t=bracket_escape($l["field"]);$p=idx($_POST["function"],$t);if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?idf_escape($l["field"]):false);if($p=="NULL")return"NULL";if(is_blob($l)&&ini_bool("file_uploads")){$Vd=get_file("fields-$t");if(!is_string($Vd))return
false;return
driver()->quoteBinary($Vd);}$X=idx($_POST["fields"],$t);if($X===null)return
false;if($l["type"]=="enum"||driver()->enumLength($l)){$X=idx($X,0);if($X=="orig"||!$X)return
false;if($X=="null")return"NULL";$X=substr($X,4);}if($l["auto_increment"]&&$X=="")return
null;if($l["type"]=="set")$X=implode(",",(array)$X);if($p=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}return
adminer()->processInput($l,$X,$p);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Nk="<ul>\n";foreach(table_status('',true)as$Q=>$R){$A=adminer()->tableName($R);if(isset($R["Engine"])&&$A!=""&&(!$_POST["tables"]||in_array($Q,$_POST["tables"]))){$G=connection()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($Q),array(),$R)),1));if(!$G||$G->fetch_row()){$Ej="<a href='".h(ME."select=".url_escape($Q)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$A</a>";echo"$Nk<li>".($G?$Ej:"<p class='error'>$Ej: ".adminer()->error())."\n";$Nk="";}}}echo($Nk?"<p class='message'>".lang(13):"</ul>")."\n";}function
on_help($nm,$hl=0){return
on('mouseover','helpMouseover',$nm,$hl).on('mouseout','helpMouseout');}function
on_help_value($Zj="",$ek=""){return
on('mouseover','helpValueMouseover',$Zj,$ek).on('mouseout','helpMouseout');}function
edit_form($Q,array$m,$I,$gn,$k='',$F='',$qm=''){$Vl=adminer()->tableName(table_status1($Q,true));page_header(($gn?lang(14):lang(15)),$k,array("select"=>array($Q,$Vl)),$Vl);adminer()->editRowPrint($Q,$m,$I,$gn,$F,$qm);if($I===false){echo"<p class='error'>".lang(16)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$id=false;$Ln=($gn&&!isset($_GET["select"])?where_columns($m):array());$ac=(count($Ln)!=count($m));if(!$ac)$Ln=array();if(!$m)echo"<p class='error'>".lang(17)."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$Qa=!$_POST;foreach($m
as$A=>$l){echo"<tr".($Ln[$A]?on('change','whereChange'):"")."><th>".adminer()->fieldName($l);$j=idx($_GET["set"],bracket_escape($A));if($j===null){$j=$l["default"];if($l["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$j,$bk))$j=$bk[1];if(JUSH=="sql"&&preg_match('~binary~',$l["type"]))$j=bin2hex($j);}$X=($I!==null?($l["type"]=="set"&&is_array($I[$A])?implode(",",$I[$A]):(is_bool($I[$A])?+$I[$A]:$I[$A])):(!$gn&&$l["auto_increment"]?"":(isset($_GET["select"])?false:$j)));if(!$_POST["save"]&&is_string($X))$X=adminer()->editVal($X,$l);if(($gn&&!isset($l["privileges"]["update"]))||$l["generated"])echo"<td class='function'><td>".select_value($X,'',$l,null);else{$id=true;$p=($_POST["save"]?idx($_POST["function"],bracket_escape($A),""):($gn&&preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$gn&&$X==$l["default"]&&preg_match('~^[\w.]+\(~',$X))$p="SQL";if(preg_match("~time~",$l["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$p="now";}if($l["type"]=="uuid"&&$X=="uuid()"){$X="";$p="uuid";}if($Qa!==false)$Qa=($l["auto_increment"]||$p=="now"||$p=="uuid"?null:true);input($l,$X,$p,$Qa,$gn);if($Qa)$Qa=false;}}if(!fields($Q)&&driver()->primary!="")echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($id){echo"<input type='submit' value='".lang(18)."'>\n";if(!isset($_GET["select"])&&$ac){$Lc=($Ln&&($k!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($gn?lang(19):lang(20))."' title='Ctrl+Shift+Enter'$Lc".($gn?on('click','ajaxForm',lang(21)):"").">\n";}}echo($gn?"<input type='submit' name='delete' value='".lang(22)."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
repeat_pattern($hj,$x){return
str_repeat("$hj{0,65535}",$x/65535)."$hj{0,".($x%65535)."}";}function
shorten_utf8($P,$x=80,$Il="",array$ij=array()){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$x).")($)?)u",$P,$_))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$x).")($)?)",$P,$_);$x=strlen(isset($_[2])?$_[1]:preg_replace('~\n[^\n]*\z~',"\n",$_[1]));return
highlight_matches($P,$ij,$x).$Il.(isset($_[2])?"":"<i>…</i>");}function
highlight_matches($P,array$ij,$x=null){if($x===null)$x=strlen($P);$H="";$E=0;if($ij&&@preg_match_all("((?|".implode("|",$ij)."))su",$P,$Kg,PREG_OFFSET_CAPTURE)){foreach($Kg[0]as$_){list($nm,$_l)=$_;if($nm!=""&&$_l<$x){$od=min($_l+strlen($nm),$x);$H
.=h(substr($P,$E,$_l-$E))."<mark>".h(substr($P,$_l,$od-$_l))."</mark>";$E=$od;}}}return$H.h(substr($P,$E,$x-$E));}function
icon($hf,$A,$gf,$tm,$c=""){return"<button ".($A?"type='submit' name='$A'":"draggable='true' tabindex='-1'")." title='".h($tm)."' class='icon icon-$hf".($A?"":" jsonly")."'$c><span>$gf</span></button>";}function
copy_icon(){$ec=lang(23);return"<a href='' class='jsonly icon-copy' title='$ec'><span>$ec</span></a>";}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('+c(<]iDp;+<8]XG-X#ETBP{IAOo`HAD0Z,$t2FfTr3g#Vd(TVf>Cx5+d&ycL<,9"B6]b"oPK(+THBssK@e0=xnaBRf;$]A0]jsW_*Ibe$(2;yd5/}P:0_3xBmwvnkq<ydKh3e?rW3UW8c6iorbru~.;FOKSUq)z0u4OH&MRf7i:
N[U_7;F1p9i(5s29CE=`-:Ze`.kn@9RF4QL-+YuW|6!U&>e-zoVW6?4.A8t`/B@/ELQUIrIU%7Kvs3S?k#p2;1H
X/y@s?AR7M+0t;gg~I=j9:,1HW;ic+?$`BB#$=qSuRwY!<DX3Ny>D#NN*xh]"#vZCW/M>Y@mG3*h%?kSmN=OT;f?cTPrvH,vnUVam2HYmY(?/@
0?NF?2Ol^+5I
U%GE]1O0$>ys`u;eY*<H,E)jU7$
/G"I;uWG$9)5gsRW{:{e~U~.8AE.5W!
iWp-~OBKj1jL_EwRB?Y&i9o<;$`HciPwu)}$:S%2WeZN_EB+TMeR~sZ$~6Tlb.PFzj2c5D6$IrS,VAG6-$YN,Jsp.hB-"5(?g%)]_85S;K}Q)!]Ug5W7nWvawCpy>%pke1%;C>,8*br=&cDBB+3bJLku@UA6Pj:[LNyDz#!]HdCU2Vp/Tlj+yr(,J,*Wh<pP&Q5qxHb/^oeg5%v+WD+:w2T0+%DHK8Z6Oq>bbmt"W9"Gv0Dk;uesBj-9`dThaOFtw)#)nPV3&XyFGk01&HN4/Q{y|&Q`8uS0E,^"<CZDxnmK?l<Ff_g^SD_u$Z@<+<tC&b^k)8;L,&z5t.6)K0^"X#G_Rn:/T#T(cmAla^WGNtZS4XlovO
"JS=mYRH05NTD)D$P<[|2YAPQ@>B#ogdeOCH)M?8)+*D+`r2kS@J7:27wU2&LL!0(7rXo)F2]gv#2me/LOGGPS`9P@Wp!c9C)IEitF`CKjug?LA"_w?5f5ERLbV%"Sekh?;mD9kN
Kgs*3=$EYJJoJm;SuFwOv5@2rMMTVhHs
L]vkS}w&YkWCkI/+1n,j:]OE&&-ng%CfmYK`SO(N(v3A:cVgy
Z)dKHyOq`7+i0G]M(r,[9,?ZYq
]/"%_Hv07O6xyQ^+0#-*3$SpP!ci1={S}nLm3E:8k`7A`ng!$yH-j]G/|y}d8Vhy6XZ5FNRiRhJ:f23:43;VJp2REp"2/PU]Gt#F?,).3Oi]/oy2tM5RMl*RzvJsW2/:.fEIoUE.
4l]"?S9(cOr~IfBV51Su7^`]oq&3b5HUl~B4/Sb^C7,cE.H-tQV*^R#rEmw+$qICNudLR<En:sNot2

KY)]Xs7@-;-#x`3qs]OY=[_lc=L3=|G6qr8vNfc@]Vm(F,&^2w_="t$w3x[VZM*ybGUsmQORjL@FP4&?
5C3C%d%,g6fMf@kAN>(<M)9lHg<(aowp0%0gvB
>uekZH[Qhb3m3EZu69I0Z3Sk!}C,Cs`CJNb|ho&F+_Fv1V*9@fd39
&)f27Wk->IL*%gW1AqC,hV&i=^gG(P&
jb8I5PFUY-4Y_YjN@_kBFm$)`,?gA<HJm&6&N`7b69`OSgA1e]O:Jd%MEx>s2|
d%"mb?yp
C1+?Ml&im[ZB*N!c%e=]R
6B%O;a,NRqW|U}tM;5Z6t7o/?c>a9WsG1Tk!?#]gn|vgx{qBT_505u#2(M@LqfO(RlG=I)aR6fMs]
nyL/y6bx-,d|UacKJhi=PhxG2`Qhl5=kK?AT_AUf&(NI!:9i)TXDh`9<_N>d$}d?99epf"Lte7lxQz/KD0&
ZA8jpQrjlcQ`!?dHd(6IFPRu1E.&@RfvL"p*6zsdlUE%#5/[>7_)D3jrqi%&e%U492k}9j(NS]G2KOF?I*ib%:`c)lY)b}l`asY{0YvMx=TkbBq:&ne?mKPtlwO9<i6[J=Jvib,r+Fh4>y%C9%Yf)_/A$Z=)PHmQN[3@8SPov"@_3L7^5<?Dc?#_LK9z`l1?RA7mfD#{AcNbJjBY6}GCA*S
jaGHPp.*:(DO&{E97kVgEB_Mj
eyxuy@#C.PNGx>5*Frh_a_R?jDEH
0SJj74wk2(zJX$+G~tIe<)cUp$,i.@m:h$uqJkHp`rw5Oi^B#;af$OYt3F5ljH4E)n
D.E35kH:ddpC<:o)_ZTHo`JxY;Qo/~4cD]Kka#&Jg*
y8e"5(G+TB3VllVi"P,;M4*yU*:INGrm[E8&;fe79XPN-#HZv5M?*PHeu.j&qH`E)#ewBDs*XVW1sO4U^:"lWmS1I>hX!E/xZ:hSZq%;>_)s>QJx!o{4A%]JSThrG%Aq<$N00!#oQX&8YX@`nJxO-^f-2`Z#9H&@%7^<DA?mq`x3NL0"#LEPiR>]RVtp;MmL/xD4|W(@Q5=oF=5mMmOh/Ed]Sn#e4w!Xa3S0b5Zs2b4[JPU>Y-Q_NdQDebkvBS_`_2,Kmh/dlNO,=7^iJKsDWC+Y}eCrN]v7WqiU|@~@STU"M@~Z[ZQJfm!ZJ=,hW_1eaXa:~*LCm$prSpmT^XnJF)]#[MQtoKXlVgoIg#irnY4IhC=FVmq=3*Yv8]G>Mm=I"2/G")dC3B*e8=5rlIaC%)KY<2qP_N!X`(xkecZi(l?<@TCJAua%LjXkPpuSBqc%1"{/VvHRN]7dh00ywYgc4M/2.9$N-_EFZ=#GUk@,3jn5zb@70neZ0+4dd?x*0kYA~rA!AKSR.)6S~whrt^m2NhrAvS`$=p)^aw:ss1zm[bmvTR?tU->o$OHx",{x
vU@iBfpiWCxF%}yCyf`rpPj5N%[zq+ww-"f!bm,qDw*~l4Q$y|vWbth!KtKd!<p&2<BU,[sQmx7pn}fhC^bKwW4>csrq@JyewRu|l7Ko>ggj>ayQr+wB9$qURwa"&%IN+g5arv+VyAd"lDSyM|Pi5Y*5K<uvJ778nz<2xCBq&]*7p+X);jN9`i
o`,]_p5MuX3RtD*CcKdl&)hNr;rJ(d}KjWtTq]NRh(Xpx$ql^bW(3*]%@M)BntuCI]vqpX|v[,gS`tfFX)bclBkp~:F0/Ja^>h1q+MZ-U_2J4WqqJM;oN-/r/E{Qh9U1c@v[7r.C_i_Tje
0J`0("b_3TT`&!Lb&)u:F|(8mo,ScbAqi}[zj#5U
qY)>5Sz68#-"=vXaWY*E-<xKi+M0fX&e_Fbu
=+7.L*v{tw=j#l^DKec|yzcIm<)55UCRw`=/jRj^H,.qZ;Or,k1@?K$_(+XuOf%3qnQbR!3|l0",RrV!Quq[g.46^QGSk~KSyRE20k2B<-5b/TC4A_H,iUtJuYn%uR,cv9E@z)vGMXa
U7Y2)a(JKS0}aSk]F6_NGG[gkas.8:b|UpDNo7)#1:#^u(_HCcA[=y$0/DoQ*-u-Y(kgPrvr]SA.64*juL2`E:o"&N
&,C8%TeT~=V^yWF8S.B@gi_qm<d1?S%?e_=p!.T[#Bw5ZN+cEZIcA.cq*akE?G
:i+IU0S
U^_[@PL}3e`rc&=dEd1i
]Vb1Iot<#!x+zvS0U:]
Otkx6Tg
{N]Sq-dY*#Tq]m>cOeiMi<NR)uU1n>uWeM*X(]|raF9XW
Jk~L;&knB$^)Y91*G
*QyvX*Fy8cX";/[vR-M$^`qM>FPjg8vVhe8E
<@m+-r<%+Mnz^_hSjahRL@hlq&&P(j:BN~O5?x,=/)siN?uPspLjm?2uWg?;QeUeu;Ribp+C$IU0Uuc/m2BD<~juKOZbKB>_fBjkwO`#:]yJnD>.S0E/^Qhj9Zv<_:WwIvaK=N.)@)bOq;)Q87@L*l%Yk<s^`g1Cat#ti>&u6x&d+11d
/U#+W!`B:ZvZ&fc_/`sZceqo}[)20D-y$oVu9m_uA]j;-U7bB<|?7P|@2nE2@V?K98_5-g+ILk?^8*sC(bm*TB$7WbTag
9Q|r`/x[gbit95y)ed+c[J^bxHu4Bt13vMc@ORH,*amJeT
3qE<,,dHeMwr3;dgO>U(4bF.oRf}L*Ig:K0;UhO]&n>,++r:ue_nFS)#f9u6p-?syidcp)g?JNdnUG>eY+Z;Q
ic*@g5_.(zZ+YP)7WTcSVV64E0hX_i$-S*f>RIkT6ld.Z.><DZX71s.
WoV<&#()`85SxZ)5=HnlbK"*8mBsyhtbT&MSD&=h;.d+S7EUaT<uXX<57JdOk<fN,pSbM2v
)Gt5CBx_X;Xmx[w@arJubk5w^{]:
g*qcDVgqw;JdkVn3F.8$@p,:"FTh[*DX9m$V*:+c!axkZ)oj,UU@4RHdkI_C-nv@Bbb"2Il%Ec`V/]F!T7fe:-a?F&-iS-6v-`v1N"_kxs%h/QW`a,XTZX,EAi}J]q`kTc2LhWS
KS/b&hmAg*8t%vv$ER(=+^RuQ+RY1t{08ll%O;+4{X7>#p:nsa+_mEK"WOgW;oA/x=!OiUz/D[9&Ty.4oid/0)gkT19r~W^qH<o6j<O!J)P3
8@`.:n<:I^>H1d_z]a..A=+~7R`By];s-$4h19^+&_Wk7|H|u@b)l)m`d>1.U~vpYshDNjbBe4QTR`F$k[dSsdQOF5APIhwwcKCo@.rtDG^NYTsUbFiJyvxd1h&s]W+:x^L%tX');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('$Osc2b7V>fj0U7tCw8TNfbT`5e<0!4x49EeL1n%e,E<_WZ?>@wWMzpUD:UubAB^u7,;ZJ.!U!ODBJ$p,I?B8.F[0L@ZP|U.08;>OMB~NvqOKctbeXn{?PG0IyC%OS?)r=jqOH2M?hb}k!R/`..+tcti.<c<RYfU_-K2A
vCxFn<6{L^r&8pu@R.[?Y;Q%DJhl3UE#:f_^Gy.{7^;|fNj6ajaX/-y3:kA
^e)ZU{TubDn#S5p^*>QuANxT;&o~2R.180pfCDp{Af#.9i9,-(*Y&"#eJu7R(uH>BnL38{cb_!.Xt`+GTj["w]mNugo|W%7muWs%-r2M[*vyw`Oi6fN"fuoOa;Cx<jivJ:1;k/yuSo;e7`7Ib+sTqL`5Z!<_Msdu^T1o?NxnfVqUcZ(31mO1mW&?l3hyqkSs<KDz)JOYNX.qcbw.a-hF9!u8),&16yAnEfk#`",*pieSR"^
:QnVS6*:q5["XTfNZdqS`4.wq&WqR$ff;}))8p/))?(NoZ>?`%8E(L)Qbr/mvjYq3odVYIZmt];gErx>%}pw8jHUEMo^U6FzrG6!>r%.Fl,i8GQ7.)96y=aANQAV
I&mM@=8CtkDDN#eQ><"PT&0&"
MqJq.V[OT-W:;AFl}kzYka3jtFJR2
a(@dy4=2dZ`=+P>(`R1E_t2_SsXn^QTobmely7V_<d>rQWl@v,kyf0vur:xH._r2kS"fa0O7^l]f4?(
>1U6U+VK-57o7x8oQWpsfb%dt4*EQPSa_B
R5pEdml@-Xb&>I"-^*scQ1=E/ctCOk5vsYn%D90P*?o@rb?
W?IHCMCI&bJI9sK8]T5bt76}rq_*)G[9K2;FAd)taZI^BtuBX+sK60V2H]NeE|*vCruwDpOK^kn6m6f6&1s^Ucs*]9vP^6+%wutWM/66l4Fj)WO0?_+RGg2|jW.*v?W?ZXyua"T+&(XWj;>M!:kHFApzq<`|]GHW(vkK32!q%[A`AHO1`}O*ZI?@w}[)');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('+]bcjnsZ323o!;f/.e+fx8n;[f71r0pc]giW`oV!D5mSIO;.F)#>?N&/uKRvUtUkWix^XgjX;sS9{(3
eB-
eJD[z_=IA3FpiUH:=MZ3k@qANy
,pQ[T_3Z)Wvk_gT[Jd2khOG[lbtSJi%Xc~x,$3rQ`e7zs`9hczx~5/)NGI&}cu=mR0U@T!doS{^#,i$18TPKy|Ut2;;<LDrZ*rx?t3j]uV0wBhgRMVF+5rB"o&w`p]@K$P3@jPh+.?b}q$QhMV-w*DRoP)N{L2JTta*c.*>bWJuFy>FjBaYa^O=]J40HjoD4_^T?rw%#V3SaEJ,@haT<%p+hZz%2WiK*x/Ujaht!%qA^VCZ%24?IYc7SxrR/#P0:US@|Fn%2c_mX-0TL`9<sF_kip3+A]LVm4i_B)GKtx"@.D*hh<7$~]-CYZwFt@Oa4x]vbQ|`u+7g+B6IsXQ3H9^K]$9EnvPx3cO;lKlZbjaFHFIy,ncrDLW[Tn<w@a^MaXrb^LUyM7x^;r12)nqu8iDuIM(&I)|_X"Ta}5J+vE8Uf0-m`=4yNV{@ctgiirE-<
?TDx$]G!:iP](g0Z*9Tox-iN*VRy5Z>W|@*CdPh,qgPeFeMj{gObW[)Bvq#<xTo_E9Ma8.QeBgQwGwbq:@%v*h-K?(EG6roY+s6+fSd4qJe8ihH3!oX$R%<=8sjBnS#^E#;AmlT"iBvkm.HK$!dmmiw(<TJiS*Fu)T`tfj)T
@N%}vxH6(]h%)|`r.SEbR&c(LU&vpH"+5`VMV)-ZbIT#<fD?n>8n2J%va!Mv$m.^hRe%-X-j8i^ltxz"+|(/%&CpSH2U#0L{*B3d4
_yY.,rLR)Vu[*cHPfLnsT%2)YL*l"Qrl)l]e?}*q</a6L:)^n}&4kh
$76?e_vQYFJn_$lIesca`%Ewhj%@RVQhWxqp+.jV5;r-HtRBw:=<Sk)v"OJJtc%O%sO:
ZZ&+Q1[:U&fw2;M5@lX{[^,8rZPTbc9_#(EBnTtG;ck;)R4J&Zs.JeIACX?%N(wf_FTvYw0/xQqtaLx0CKViZm]I1+UWX/z$S9P&84YP;E=~j^9RosV!7zfSE#83?W&67RHN5rXJPDCS2Gyv;d!O@S#*/Uo2_|#Mu=br..K!3_K;71(Uy~vxanxZS)w%e;THdO(#U)!#!GJB^&t[rKBocXn!w<n)YgNp!K#5tt=Q,r#DG:?&t8?)!?"%DU_7IMM7ZkymqVXhSO*|CkdoTA3Uncu[!&/UNEkv$d-~Q*Y<41A./WpZv1ekD>a6K,o&o{FA<,hX="<85cJg1"q`ACg$xfbTN^G(18VLq|V^0zEub|d9kf63V>g9m>$"Zen)a{$+a[j><8U7*GC%x99n>"T[6}?,bk*,q-nZ9yH=^Sqtfqs+*[fgu)b>FMLo[`a<HAX%w/b<L.xjMB]jfV,Ar6qJ-IvMed8h$K;tjyg2&/*Iq[?l-Gu<2MghB>KoD{5rWJrFV]bxI9H:=zbb7Td4o7a.Ga2ZLG`$+KgEw@fGI~tV,k-Nd=YwM{)h61ry(c(L/g5$-cWtXw=Q4,CNs`:|aRsFGM+*-V_`%rI/YL@:?n(>E
/n2exjX%P[Dq]yI,HU[R3mauTku
0AA+R`a:Co6f388ML#DT)"c(m-FoQw:%p0
PG&r"LiSMarC.JWx4eaW//o9%OUE[0S6z&C/p4(Bg2oXd8!##.{#w]{VW@hQ7fU7o:tbCrEEJbz:6(cZrU0Xda`4xVx!+Sr@c2&GQ]F):Yb?,oH$K=2j2W%07YqKDDoSheV3Kw.3@aW+w*B#4Z36q<!qp1PyQkpGS9Q<-V(^VFyG;w.H_s5LVHIY&/6]::OcSEM!@>EdFKirB$BjXBihOE=7tX"fY"#JZ?@Rym;[F@lmHfkJOU*h/!MXOdxLq`d<FUof^^.We>/tP!
oZuZPZ8^WdOIo#ty^2XU`|0/.trk[&21Vcp<:iH=NZ0ja;jtf&gI8o]iT]vsA^G)4V5//@E%tfMjh6j$+8wQs:Pc&6-_w^U&]In#`fvwt6#4:&L0:}G^TL=USoe%m{FH;,kcYR@`?q:)8pQ7H?c^Ua;GAk9K%p"fbcHuJ?<=6AT2(R*xu?bcvJKK1j@|g0`v(s$PNT=Z#L,%Q_C/a)6W+41|JQs:,1-J=t%2n.5cAt6l1AiKxX5xDG-iTXMpZBT=_X:=C4WdkI&-`(.GobZFC9!yC%C%/Q(V*k-!uQ?f5>Nt#36)EAcL2%Sn0e]E^+Gd]E4%Ci@ycI9C7j"@kRwz%J5W;$ioW;Co2:4g`/lcCnj@1G*py6%(m?I
>n6Y!F]B-&^2@,;/Y(Rnu%
]vb+wc+CKNq3w+w0D(]GoV^?fNN?,[+?;,"XGP<!b!&aS!S>`Ef:XW`PJXr>)1K&i;)lNuL!uR7/(<lxsGy137GOLb]Fnb{Q0;?J!6cGsEYr"Y?Ub0,cG9p8(2YP]j]KZ&QBDC/"2WjK>"/S&o8b2Fb6wZ]L)+<w7iPj#)yu#L"[sr`cp/__ynn$Cp^DHqvP(DXD^N?;~EeOS.aI-,bRWxnj54PhTJ)tt)hTKpj85%TlGg/JD"Br_)C3*Niig;Pp+XAIn(ilG0HITlWiG-!Giqi#F0nRZt&"Cm:`qUNeim?P-gE#PI/
a"/;T^g$lh(6(/gH;^mRQ/GJh
?ypWph-5Q:YV0biLHp}BJA[;=f#6io7b2@)3<Q8:h[ha$a3u%YhD2pVtC+)Sxldu`/!Dco{+*5mmGR^S[<=QShX]M[{NRMC$p3YYLT:T7)%uAc/"dRz"d%df`,&kOKUrG"u%=Q8G4_+YgWmeiBeyG<RMbn@!=7A.:b<i.=k.4WVYNE&4C"l.8GqVU"^Y]!G=d7hP]C69ADn
Wvsg{r$C!a"K(/onhFLaJ=$R.Omru/!cNR{Ooj)>;Cw:](`AVamlBKN@J8hW@15F-MR>V<FN2r^I9#6t}@=#@acn%tdDD"J
jpU3@l{VIqpQo0V`%op*XE&Lk,D0D

xn"@?(c%ua3"y(U2U=!R[OD9?CRH.z?t>VQgh~gFpv#DLi
rg]W*rmj~c0w(QWr]HbdnZmi(6!ICUX5JR~5cMVF[m#Qtrp5BW#D6_#CyQe+;$$JaCk<"0#RWu?mJ2elz9{U%lCxJ6F"_DDRtG:Jnx}[i,fyXsGHmF8UVDO`@S)o`gCQMbROQmJl=C>a~.lq1gJm;O(U/!3L68.#0p4fp$@"cnV^$=!/?+HnGA0M)H.ssyic]=86rn.GSyW_`->;8v_w?.WJt(.2xDx2HN0/6qdh8!!0QQ91=ViyJ_?dJFNHhpmd4;.;N9)-UNWq-UQl6<Agta%>%$:*|)0g
%9%$*g"<sb^<Sa*I[2Fc"tXBV5[7c}Jtdvl_p/B"aE]QY"dl
6esCs`m[Z6EiX^@*`hK.7i;YFeI3Ps^i`Cv+#^y1?#BJqHMJ,_I9%M/X0ZZ]U-*j*+sP6AqxD-HO":lZ6DPIGKK[VVuPV1qv4xdg1uS.=.qCkD0t[ZU#CnhjYc!/I,)g=Qzi#kX1&Ya<QbA[QFb47o_`YTHY-`*r*ysBg4vu.m*#&6rCX
08-%~P<J+!tt"JZ&|RjU$y*!Q8)&Tnmg1S$m@0=wV@C#UY9Fh,5Tr4*m3[H=k2(Bhn*<QaHQGSggLQ[!]B*<Ikc"6j_AnX:Qd27cP@*6.F.Dhhae3
4P[)cu5IL
w5LV^NLS$.
ZL%GKv6M4QId20GC5y!09*o|VB]kwj
Jy`;0-Z7[yzB>CJ*dYIL2!rN/9Lh+r7@bl|E{F4o3m[E99r*/d"Q~2lwO9w/|W*%|,,oJ%=V{Lk!j]ZxhOqvHq_tavh^lS/6aD3h$%Jg`hb5b$in*5XOd4LBVSnOTxU]mCzFoC"-_ELTHLT)kXWL,R-;bQC,4#mT0sCNd0-Q8b33F%*1<=3>[aPI#Y4C"uW&gNI08vYLz_~[.$W$*nNW03v:usSeB1y+TG
-3[TiIP}a5#v98
4fx`{q8JC4n+H,50rd%HR&<&.YNiT#McvCx:An}wM?zy3R4Wf@2B_l
1K-sEyR0RKw,eEPM,cP/l:xRF"c6,5k7R:]iHi%oWKkr:6:PjxJ:h?!!f&1#n0PBRErrk#i,Um5KlNtp[v47v|
}N
.g3]x9K_G_balSiTHSNLq=u"D^q=TAPwKhA|3;*z$<@6y6A{U_f+bqYO(mb*-qB7Wg+Bl:Re7Il0"R-?:+Ny;neN%Roruy>G9}l
#HpLLx.R+u[0p="vLXx9Co)n$IUO1R-xqei2o17.6q3R"N?L]`k.fAb,ychZL("
Q1_8_%xB?)#*&NZ"C~--G=D~*W8Lw<"DbGj}Q2lg[oW=U)g+A[0WNDZ-X?^~p2^.R_T_jCX
JL
C`MPV3|j>6^!x"e!mOv?<2;
3n*RrA*66X-73frAk<`5@.{-UJs8lX6n+rzX]=_6}YPL=LIpQpGd&3)04Vq9GkDF,+E_WpFO"_DSn59WL#A2#
8#*Q[9lfRgy)gc!5,4~TVG;.{@@Ic96^PxdNN5R._`v(ZoN_V@]m3D%?~_@dmjPWPY;EYp,3tZG1lrO(oms>=>koRuv=sL@)3P_I$lCjwE+3KVwwCGPQn9`uz??K];C:37X)$d!RXp25ZfHM;=^"l<b/BK()%Gah-l8d;:X"rj4n4;
"Z6+4bL0c.288W%,W}fl-?e?0]JmAy-WCiT&m8F_He1|!Ln>v|et>>l7[0j+q5ew=)[Ur:g+G^[BC4b&;==x`x
N4&K99M=l)]e{d9?7<c"p^1yvkVCE3KTwV2oW^}F@Ux52M&5lUo
lo5D.OTi4h]/m/On7v@.zh&rhN$rBcp3%h@[s<C9F[9`3,C:-#Z`_LxJY.WKkV<*9U,g0/3$$?0:)Tv3]QEoOoU/A.&D"MV
-0IfkO!+,akP2`
G{#4tcXao><,UIw=qI*l8!tw,s3`q+D#%TxVm1?haUucyv0aO(+zry@++ZVa9|ZKRby$U}?zP
FcKtb?[K3{g+>fk)%Rl.oO;nbhM8]FH{v<CPqz@),e/*Br+?s,f0S41U`q_)g!-u$l.RvhLI>PME!t`xSs>k50UL8zJ`Jq1ys~##^;Csq:Xn,u>{Ab1n=/B
PG[+v?UWeqD}SwE{V)-v=G0d3{$Dv+Ma"X@i!Tfcf,L_0EKmF5KjX+khI:$b.%0?v&joC2;&8xKau8$,d>M`UCLc/w#!_`Z7Yh)}5R((iQX3By^J,
cxa<<o"/j`VL.X
>!.#vw=(LXH%1"wS2riY5KbNv
C5`,O<q<myrro98;u!E4Z_!UlE{IaM6L~j?RYXq(s?8]K++e;="4?vM7*0/P{0-!"$6kkek*wZ5-VcM^Zlp
e?)9O]=[G8u_"NL8Q5,6J0e5mutP)YX%%V`s)t$^teGh)-zrr1"d[UrEV$Amm;LO5aQ&w+>0pf+
%Z_Hq9a..%8F)?kab5AN[v3QvG$Zgda/SUk#[dfFo0o`/^_?YXcI"9jt8eQ9o7AdFisX.x;p>+-%y!wy
2hAt_#3uo
h2AdSC:Cf19DqI`v6LMXQ}3>1#l;VmVR0w>Y&BQR5o6XwdX<G&rt9jZs#6_R>,g?y3&}pJ>")++%Ev8|!1(,,[Uk)EOK=LjDN|
c@~9-eayBO]hH9U(LpCZ>[@s~AL2_Ck^|aMRPbn]Hg[wSSHF5ZRy+=jP<u}e8.uf+K/(w.B
uFvkHSuKMPTU^m09cl6)/w~&9YfwrxsRcVv)@(]i"#i*{V_,#&ZDsG?#ynuiyODAY4-u,EaV)dHjqifId;BZq9}>!O.=/OtGIA*54=pO;%B$-9eE<gm:{V98btXGl
IR(mdPD%Kls;l:/YQS{s5[uh_y)6JQoSRncC;8|rh:(ZfCFsy6%fG<O0R7!P
hlGw`;@EA`/
kzGQ4,vjx<>#nbco2*+W;t8,igkvTZR,va1EciB$+aP[-MhyA=BHrRdOZ3/.YT[3TW)NEpNhg`Nu8vivOBqv[a;_Ac7d9QG<1y55P*$y>%Sy7VYI
H#Ws7i@*(7fvj-6:4!RLClZdT`+nFMg7y3,^YnJuJSFtvXgXe%vW$@!n8/[BEq[b>(0$g89d,88adF8bJeTeo"Z,~j-$GrMQ~u&J_PsNT<C$*bsB6JHCRmC7N?bj
Qb*C`%;G.5Kgo*-[[i1dll)bACWQCAS>0fYiYm*jk8W%!o2-j>^3AAL(.(bf<+h"[IC]6K0R)fcXLu2paX6@jfw,f&XPi`"LiuV23vb@_0gK[9RQ!<.Si=@^V9.?"vI0QTm!=ogcjX$@g,AIQv-[?oQKoNWjc}>-NWdYjLZHt*a[[8Nx,xs)09]YBad8-e*3D,eU)DN%%WBUZM#B6xujBxx?T_AA&:/{jbJlr2m
F?B@vB_LZ(_f_nem+js./A:?l(&N)27K0e*K4?W{Tn2}-(:T%WHD4R+X^/
x:8ozp1K4EygaNxf9Xnc@C(A1^_tlN3%Q0]QAovhfB9=^DY<"Ii![
(?9j"(-CjV(.*5)8c[c3
eZMsQ{+3XNC3.clwgl]B2R;Wcb-2;AoTBk>lc#s!e!9hFe
%#%0VLJldV"$:Y-J&o-gl>=nM0a8/R~izD~0f^10db2GOvuc4A9n~4[E_]_l@
oP(/Q^4VP_J<oZQr4liC:]?c
Wk
J`F#b5?Y=Q
(^m"WrcbHX!lUI?QZ6KJBc"p3[C45UazRp:GH]9ho"%<C>mnsebJh=GKJ*fHvkT<n`HBw8fl`3Ya9OTC>T6034P<0OJW
UK&FYF-=cj2^IIbi6=yP43|C;xvKjg>Ig*@3N]MF}!ECY$XAvqJFFVNin(&/E<NFh_7IjdU^QX+BeJ#9mP6f(RpU)
SXJeJ+09yhFm/7d*X@+OqnzbwBnqkBw$$_>,&!8K?eIcU?0Eq@e_A(b1EDv)Wi,lRr?aYGJ^wusZ!";5b9NqJ9D;g"9b(=~MQO%*:irDV1
C:-Q_N_>9$2&-m"U1B7$0e.5pb+b9`hEE;_0MS52IU`q).p)=`
t,I2D)My3gn*J^k=5QLIp"J(&i5=~P9!;
ML>$}H}2Rw(rlcI@,^?Q^tuyyvms%Vsqk>FQKS$(OxxHbIk/v*oHVcQT_gPf/6BH_<.9TU2j<@MY58r(`#FkB;o:9^5de3~*Pa7E-+%/&P`6in6ILyJ_@=I!
XpL&w@:]j6pR%[]M%0?gJKdoYgJOyq^Ctxx$%AA/#=4,$D?Q;lPqZYl)9F[rFkatDrTxBN6p]g,U)OKRf_bY#(N|iC>ig&YW6DK2_t#g!{$<8dAC8tn-j$mjfCA}[I2F9gB6Hy:~$&F1`=UkpA^oAb5@Hn6n=<0Z#!H6;Bb57^v6Mz6W#I=A7#Zra_@=gLwvd5mA=Ie:T1sIAHhV*"k|a!_[_{H8i$i~hMI,XSq%QQuk*W`i9h"(?(+R:nr=)%QhftNOV#`>Eol?J
G;,d2%Tg!X<z8~%|o`Ke9uQOt[Ty_eYKf>&s]A@V4=M$(8P]dNdd?!^m)0gg]`a`UrakR<.Q!L#Si]I4%#HWxssw,6_dr1^,APm8oi#JB#?Xc!CCH<;B;:S%9}x,&jbTR][ox+eWYqh;"v,0m8JMVC9pZ>bwn<
$L6q!/l;H7H"6c:K$vzZ8bOv0L~,3G?JF&;r
d+ksBA
`"RNav(;4Z{]_3s/
g.5E^&qBh{%8+9D[vAH5yq5d^AB~sV-LffFw*WVnHplmqGgp%]:1tJTUo+dJh_*]qzr:Ktb$20^:)b2Oo9NwSP[<N]).X_J1ld<$ym
G<Q=~Wm?IkF2liZp2Zw%H8,NO*W#~J|&)0&4Er{^Mr>=Cggl-)/dIZY%tI."9pT<fH<$RjA*Ek5c+amBKEBB6)IE2)|yHu:GWCvA:K2!Vl`=p,W^Aw8soaiRWKYC~%vtY9Q/]k`
57/]kq
["<=Y$c!*ex%>+NW_4EIP#HKJXex_~Du::gqX-s^mjJjq#tOr~0Ss9:28q&+_vGH#:VM>treh"lLjbsAXBC<laL2M~9}dqD_cKi)[{PYqnBgj@-?g3SL09Jz_9AEQ>19:@/[>B,lQvmTc!I=0kK*FgWHk9;mX[;hq;)NOQj{XJ<AIh2O2#v9R#>LBtYk#WS.&TQ{p$#Zi,E61%?Oj`
dgV$*Z"gyvkb|go*~8v]e-IRoQaLX+j)Mo%7gi6iEst6]bQt.l+KKsUU^Fv6h_cZf!1.*LqCdI$
v@JE-"aWQ5@N
b#6*oj$Xhs&Clp#5$=`5SaMi&yt*l,f|IZxs""iW6|)NI_g8khqZ4yB=l`!_d=mUa$M&P#d!+rvP_ip,P`dtme985-5e00Ai;l,2C5w@(.CAuKqo3hEc8hJ(%[-9Be.TtzNa5O9aUQw(n|6o-/N!>@sk!6vB
.M!/jS+;=)HcqxX,V*dY2DXM7:L/la*"^_Jv{^Gi}8Z;q0pK>^GRXtN]iV0>,K-9CXe"+/=M(CUB&.QNi/H7eMp`$Xj6o2^2#-?!{)?]*kFb$ihDD_lgQ7-3cui4f*+*&A}wIgjmL96p#30G[1AUE^6HDEnvY<j6;O3t5(X/qX-U+:0wK3EfqbgE?(ULLVQ<9*]jrlf&$nT3mfO+7Z9v`7GF
!{oPAJE2
Fdbf_29];ZW<08D=EJs^j:wWj&+*
3EJp?{^83(se5GYRl[_ed-vJHM+R?1DC
/*F1-rR"|^ritP_d(cawR;+`{G;^HBO[-Q|Ld0|:?BV3!5EI),an7%rJR
%9W4m!WCJ%UWRE)rlRIlbaHa*0rx05A5(V77O#5JqEwY*7}Wf,D]<L3GQm1e!df<6Y1c_
SG<2wm!kuF9FQ&d=N0?6m)WS6ZTic,4ECxerL0C%Uj9&p#L^:m:l`m~GefYA9H^mo6.LNCL7{<_(?_Qx<+PR~JVPS2Sb.1-"7,dlq9Y<
ua/+[I=ku)pN5EKo=1=Oi&j[2$xHk&(a<_?}=[qr"`s("8d79F0H>6>_N:Q53u0`kvDb=WDJ+ErSZ_5G[EK+r6&.[y5{-]oQl`*+[!_[1#)b?(4R4Zkw(Lkq+~<k#D$XWOM8gmk<d+9uOxZ}c::+b5#|u-y@^y3]0%1C"7N"WmvMAkZqOq`7T+wNsKtWqne~s<#eDN%6?myE"Ff[y($d;{q8JkS9V5"Q`ZN5gD,a"pV`J-)!HxuSb-t^,|:[&}3`W0mit*Dz]$L!^!QBV=0#yCes&V`%EELo<YedQt+h#QF#8]>l6pgr&X]ON+?Y)l-Vo-qT`7)y5MX3@rHyk~Bsrg@yD_<Z
3Y5G(!L*[A}Z$g?Z317X+ET8ih#W8EYwQrqreK/T^3gp"d;8D!ly2Q>"vs9.SiSfYd5/"v=lH0NS-k}]2C<5VT[B+[`7YqvcBlpXgNQ<Q
|4Qg/m0<VN0<"7eOmO?&soo_zm1j-W5;$U^UJk|40R]FQ=al+R_;T2pHc@;P;toD+u).o$liO3:oq%hF)scW,8gGt_C(U464>VN)
_mB=wcY|rK,(0-!-gkT$^&^
n)@OY*f;O47-[P[4#n!a*6!8*d#tvFm(DE9pA,9R.oRS!<3U9%m-!_w-AF<,u_B=1k`|6cn(s>_^Spjc&.r{YcAW`A:(m!j!J~C.dyt=``(z-%p*7/]QeVb`HNK[J2]b-x0.[{OXP`fXJT/BxXDl#=11Jn4m$u0FB@]<_>S}Ly+Pr}lcIA)/,0.%R(JnO&0foKXvv,F#]~cz"0o(]8>ss]u{97p+)]YjlCPHxYz$itOOp
9:*hsj8OAsa70T7l(S>Px1_h=+u]3hMo9ubQ?jA!m7Rg_>Bg25CKo|)xx-tqN(GcHICh=T)j"$<@sMr~=e7J@@_{J%H`3E6NZK_`vW_&A4r2Ku2W:o`
jNvf3-D{UuPSNa6p:%NyHH9%)-1gk_3blX8&*.&ZMl;}w?t?^Apy9NhDB;03${r81f9|".S:to
0@efXc@UdvO9frU<N<JWsVrItX[Wo15tvQm9>)8+~tmVdPwR-EPK>rD&uSiexas8Fx!w8/-aYmE[[,9X((K6[23d{AcWp6CCj<{S%*s%!0_o53)GY;&KPf4Lhm03a#R6sZsRU/P;bJYDHD>YO(cIPd$k["WYEuKS)j0J":%<4,[*iIZ"QNAok376s[^c%%%EY2L=)>eRmgK6bPZys#L"]M:OH@aros[*{,5_MFj^/3&J<XV[H+6
lhs4JD4]$oS4)L|T+1WhvP4,S>ADFH0r/g?hah^4nK9;0[9LDix=b$q40-##|3(c!;a1H!&1|-<G-xEd
641I/R.5i9X5:%Bt8Q*~A;Nk-FN{F<jUW5T#!VM56Z)Ob3BQ@>l&[z64!.KtT;`C7psi/A&g[8kw2&4I
Gtpc*.D5Hate[^3)3"o$]tzSg]`]v:s2e9Ze^3u1#Fv^kT<&kwb2bSY;3#YU{&+@AgqqZ(Qw-%I0CgWKXMv%>>0+VXlD3b[cCO%VCR$Lda^=D"?QB/m(vqO$ww*ZCa{;ut@]R7sPeWOfQDP;xf`$M%g^trgT6/`U<;xeISB?MIF,SU#yTto=lAy6J
T5`cy1IFar6R@DIe&4yu%vb!ZK5*<a7k2E|d=T
>^#M)@n?q
0XGM9]yJ7t$=dLt!(3H-HKTf_XTzLP2TS!PZhuT
p>WhQ{k9b6uC#OY~Jz5=ui#U$KL@mU6vxaLA+oG9RtfZ`Bb[Ox<t]sAz:aF7T]<umXj!dP04Qkge+]`ak]*]AUw,WNOOPiItP19Xpk-x0yi5LR]AbFMride|:M)}@&FG^Q#IS>L)*7Hpb|]O&0fBx[b$*adz)bcP&}imPSiLjgN
qS=X+^eCIcKkyUw:s4v|mYBpl}bn35NpFBh@&%p)S%x`<@v>D%ckjOrC%Q#`6`u{y<Xgv7>;Vl6|u_%"LMKjE<QA=jxiMul:Z5%v/y9lQsM{1)I"!TUMOakBX9Ejny13$@R#r|mW6#hz3_JU^vefL7WF
if#p%ynvaW>k[PWhLg_JOYm`9lfxD6`w!iiZ4tsn"<3I{GixaBF</R}DAHl8#`4PnJ|sw^}qse@M=4WA`uqkr%|=Q=;GonIc9")OfWDnZ)M
R2(%5N0Jnd=(%D]cBmX1:+!1L]Y0K2yr33mDxgG!Qf3F{sZ&,0&eCg?tGNucS6DSE3VQAV,`"9e#p/kIz<=
KCR[put6=eGG5tc%oxlHN@!0p
9YZbNZsLT@73O7x:5l[.H%?=yf+V7XDou5-N+ufhs*]-9ti)f:PBh6=BfM5B4>D`&CnfA&6({7X`AXTt4`}e|;)/()M]>Ya+~0Oikmun?.2S}OR_
TuMd$%=tO#E([Jg&rr@7!]t(y:qTWb:!JJmdAW0=Ni/1nln,[,FSgp*yRDUy^%&hG
e|y>24%K&*#*qW?1po#6.*F0K;ga*yH0^]+(8-!IJ!Wx=LtTw2-(%uY+y912>0fA1inE-5+YOfQ;;JRWtt,nt!("K2Zi[iz(KyAIG,Xe1SrHExWvVxs61&]
[740]:+ucfOTlBa<T0sNN["5sPWG-#yTI,^~x0DmKdb{F@a-@Mn%[SpP:+lZ1e,3<
y0!(^t]0
kIB7pa0-803@K,QE.DR0^ff"zrN2xgF_GcPm4uN_d@Y0U!L%r_v&$7NK!d5(e76*O!Fu"`+G%Kk*koM6:x;R!A"w"
iF$l?L8M*ffwZW/+D?MiSGRgPc`1DpBvY1Xrv^RijgdZ|v*X8!r94g;gB=T2PeaOgPrjk48N$SCsJeuO$MEnJ--&.XIEqy?Ld0C^[#Z/_Q?uPvEB{R}ur"ikR&7#.`QpbI*`)Rr%!%uDX@nGXe3wh4r50^fR0x#MxjdJR,zF9A{&A&XAFn>WzNOX|"Tg#X^A*Od({nijg
>]s=x,:_,)aoD>uG7Is.k]=LnS9c0^Lo87wNMsn]C;La~f?<p0(B-3[[rHS5N$l9jpOVndNb3Sr[kEQge=n#f+w1*Ls3$Uxtpqa78v</l7H5=Ff,qXGm?#io[4yWi>o[OP115)$@rFHKy(vL.TR<RS!UGGMYQAnYkBnjz<5cLsvq0QA35tzWH0V$u]4.&YbcNyO/,vhyK]"n}TULs_[_YlCIjH#g~VHHEVHK@6.2l%Z6$ioT)l0SXG>@^7iC<<Kch=rr7revyUyLYy
6ndml50sy8w@F=P706-vg*vX-?4VeO4#+p3xV3oPR{h?K^q1L!Jxcw:_nj.6Ez7I?Wl`F+eD[O:ISMYG?owcP3t7WjowPZV]8?o"u=&53zGA^7]0j0[Hq^v*;>cg,BtDIVy*_,:
8kq)$/:MS;sQD#%WHtwfo^s++wc]")2Z2+o+yB_-r&k*hPgtO0bf@]cM2vkMb)=#(_qu=_kgl8/TsfF3QnMU
}g1ID,}3B-g88D/f]s.YYr~s2^ihOo8E%M=H^-kGq8mT$;7N+"843lHUXC|CPY["6UtK7aP+"j[I2w$x-Tm"}XB3[tNp;I*l?4p#1k7O}-"e
V0y#?Kqwb*c;ISid-vo>Ujs"Mt%DsP%]]/y8d]5c&-_05$u]AHUsxV;AWkIw*LDW9FUr8L_aaOb"4>I1#amfo!X!k+f0Tj02e9r_<CpH@:[5sdB?(efo@C-CT|r^NbuS:RsK`oNs,gA9aydD%CXEih>3P`RwkLT_#
9"qi[WdBlNb1Gm9lhxhzIXKr5Md4
L:_y@,ReKBq5HNaMt4I*vK|h$_<9xrDjzYUsv)kcE,dX|)LqDAVmcc2A7W>j!QzDr-,Am^AebNTF<bvD{Y(hqTwXo]_hqprwn:#=rp|L>yVa;h[EM=Bn}tMf
m~eCL?nvHc?Cy[p-$aWOY/vmU6/IYbi8suCN9nq$4qdL9:6N/U3OU33bC$`i*YUy&0)ujztp&J&aRT>8PY;u,+)P;p&_h-vTKPGZo(e0&l+:LG?Yx4/3?;=yN-4#26?VDLnqke&J,Nl(KR.h6Yf^@9]"s&Fxsj^|N>t[HSD:8^P+<P:3l#dYb#QAN)-GA<D2W=?M
=puG1x,g|1&r8;.9~ue5u"@^
5nF5>nZ
ehc%NIGlZ9&9l#W6Ah["nLy)Q|W7&!xERr!:*Q_?PX3R80H$;|1ESQ<:]0!a5GEz"~0=d])(CqWzd_Yu9%tK!=kFrnFlR_Twq+tW^1EMw|0SgHg4Pkv&^Y#IeC*b1F"!lQTW=v!TJR4~7pR,V7U2Rs1cKM2@7Hu~Yq.qZ#$qD]rZq4;f`!V$0AjS[BIF?9K-"7v8j#GjDK-:hz+,&$yYd{DiLJXg+!Es^w)l0l8SN)J-F[TTq*//XxOvRO,o+Cy|H1]eu:<,&#a:P}OI.&x,Bd@C+YudeY]p
]Eu1_+fQp2Q1Od[WIM:KmCd8:$vlZ-W[2;e36o/(dgt"B5f5lp+@wt)&eq&&vjp,<]WRiV*s
BFt)*&yo>e3w
Ot9!rS;K;BEUuL}jdN:/-k=7#J^Gj:C4
49v4g;T>MUW(mWW[xyj7r8(Xv/=`.]Yu:PI<?"eA+6Vpwt#$OvXRVoC[<dwfYGto5CM$%Gj0_E<2:y3gl"2}Ye.*GPRG$fd#CTj>6FD8eTMWv!jshCX$=!V}b*v*lkTi-BThMGV^O/]&LDLP#Buq;I]aBYZ>5maDK]*,X7,$*a+9p,^Ae2rrN@1O**U2;K%>_EgJ!MYb:CW9-9mOIlG~6LvpvN]^K5D9,8m+L>Esg~[9qwbx%#gzNA*EYwL[$Ja|v.SE9G<a1Ag-GmZT`4#u/*#Ge,]LE<I--kvq<LUKFOeh!;$2a7CI*2Cfq3hQh4%ycfUwM!r3*W`^)mNm&Bx9PQr[_A-OS"DN3oE[8jR^1Q(=rO!:.6^&[J!p.3pC"QBWZy"4X6S#1HOp54e4Rlc^!}R<sR)WY"+?0_k]KPSbl@7VTDg7mD:4J_r*g
KF59)lxe%>DL%lNuCxE}`M2CjZ)165WITmN!w}A+*|J_2>+E1=USO29O$f6!o8eLoPs:*Wc?/+T`;F8NgR,mev[=2v&)5<T,1|J{p
nJbrKOK[nG/AkuC[sv7*%8-MlYC^JCO#dJQ<D1%RP8MkdWuze!]HlU83o7EN/%dB=)"zkb,5H.2uU>#q+]y69)3h+$hl.rx4BRU~ty"|(@xP47surH%P8GE68XgZ^a?*g1E@-6QJJjN.mtw6rW1gN"UU@B[$Esr|#JCrsci[
LACqACE/rbzN-HLUf8?Im"ML@n4^#H0%2tZ#,i#vQk`S{qnH/2Q:xAvd1*}pN]`?Tgo*`PZKgZNVmLAf}n?XS>TRzL7Bl4VV:7(2"T0qnq`->i?2"n5d!AGn9u"9&;7+h7~yw!Q');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string(',hs]`s>Ze.2)6qix%iS;"ALoU!:VdVll-:e*CY7Z{p_&G^XreTQ=+k:67@Do}yna
K%WsOP@hnnK[tVaD?ebrpeVa-gDTbtWyy$6;kZA>vi5L5D=][K7rS2AS]`Z=Qcu@R0]^cygCJ8sfb=["VK7SBYXB(YRexw?Ra>Ln,Aku7_M6rI]`O0^a=Zlay-omR,a^jW]Wp+]{KlZ+y.m`x-pswg.erK]-+VFQF^B|idw=hcEZ@(>tuZ7w=6M{;(*6xOj@oJpZ([mVusM;.EM6!Bo?&#>%M:+Yd@F-=Y6#^`/;GSg,yCc2HjYPwgV}g)[kH>kJU^-7#DW1m@/O!"rp<4=c88N<ZxNeyPd%G^=K[EYlj/d3&Xxo.IQKU8&SuUVBLiqU=Hj[hvKX=OoFgKp{)sKR^rfCH7w]bZAt_wz%up[N/#RYq.=1L!i{bS6q<bTmr+1d7*AwuuJ8[94S@)ERp6a1C
^(B%R%QskaCdaloA[bK<OR6HKSpJZ|FeSAbkGZOS(O4@W<
ww`3f(]c^
>2)_#Q9vuV7D1;8QKN#yVMv^GVCUXbq7:1nq8FBtvVU4pBHf{Ee0g`1#gLKv
gP6meG[7F5PVyLjS"}2&XsxZq4*4x1r1wnj[tdRN?W^CA^@gaV[^3~Qn#ZBOgp
wS3#]y[d(V&`Em{klrHAOyO(uxg*iyz=IRc78k=X*0o!^l4n`S$6HEk(O<h&tn`xv!GC4J_t5[|ufBwW~oJm:
{0%?LduGd)-c(Pc3!`^h*kg5"a,:eb3xMqEy0W.LFjKIo"XImQ2QtbcpisuB)HLI)Re:;6}xzX}w?aNM^@23GZftjV=V_Ag/HM%XyiQ`#d=*l"5(Iso%/Nn<lE(BL]$.mv%=-c0o%auOwL_RjkLv5^QWngYa*yIBiXN1te~9{./)}ksZHw=twtOsQV#Z(w?
GgG)NjT`dSK]D,ys4EhvndSMrGz5"P6^#sr/V8^K3X~^
7p.QY#Q%c9unY$r
Cg;"r9gj[L&=[G,uhnL?ktwo1]w`4UxM^Sy,u8x6HnEw^Sg$m;4{Y.@p58%&)hGE2#QMp2JtJINu?&WRZ2)A.6Oqn4u0MJEh+8N@l=2c.g,{>;(N]pSNyfn;Y$H(c|b[$Y<um*pk=eRgtp?Sm,sdz&PjVH%(lOZ;uBD=6ipwU;cLr`/W4lmGq
f,Re,6+bU8?H6bG)c~%=).m#DElMKqI
&mUHq1MDFUsXo0>p_PLGu
u*j_*7]masg>1u$qsEC]U=O?^enMSHxa6L({l:wdn~
y0;X{FOh^CNj19|H"UdaohDv4g7t%s<lB.P8f_t:bR/KDR/I.rkv
/SE}!JT+?:RbRUim^Nrqt5]^vf6rqo@RA+jUMnC<yLOq@2uv;C0qSJC[&"n/Y;B$wPVnk"1u&GhhgwEA>te.tWV<C4i/]-7Ka]s!1suj3Gte=:=@@6**=!
+-Xp)O4
A<vj_;)t}+8PTpI>RXleA8%M@m7x:9N#u/#%N&jc6c~eupp9?v;$VKdR/K[i|97FwgR^D&a=rwh7*y|1DED?vu>Ah7xq3LKF-%:*8r<n~B|%Ie.GfuI[PJvCQWR#S+[r=!|"0;3S{yJqEjO!)qS"C;GxfLdmVkzomP2^?Kq<t7?]-V9
d:|/WV+&YI&,N6}m&Y:^(+y8V>aIQSQ76s:]qO0qrfe%rYEiZn}o"4<Y+#J!X0frWjv_v
Mm$`wa$)XGzA3kpPGZy!mfMqrF{*%={?}?ee1wvdpCxY0abHGD6S#vB(^Xi1
Xap1>ux|u(W%s,N%/<H]f`f4B-HGFDDqr@S9o(bLbvlA#8Ga+lskBBTw%}MsA,B.RZ[l<K9S"/CRx1a_?m^o![q("kg0vSE"MFA|gs$`-DxBhV:5xhv1XgK.KP
D)@v0&]JSD)x1Y$uVf*wQ1N(F-5QM<WG*Hy,Kkw&ht>$j0{K[D/_QEB`Pt5_fP%D
_W]%`3eL`uv<gN^|lpj"TUU8Ys60Y"a]7TIs%O/;u!yI!$FSyu>rcRK|70cU-7d"@evi_vKeA%H;V=c!se>BItx~)1<BPO/hLX0A!"VtvCsW>z`y,~6HWR7XKQQ-]1]]7N!`WjNfyV[i[Un9"TcIJ$Gh7bV7ycE"Uv`#qidzdcJ_wfkU<-i<pqJ>Lbc3:@31-{`hOXVKQ:TA`SpP[SSCvSGb*AeT__/].S8eCu]G,]X+0l]9GctU+w$gw!&JSumhnV8tA(*|3716m>d/tf?LJ]-r0..Oh0@v7gZux^]XiP_QB~N7e0WG<0XH(=ZVNssS#U-PTRK_&*,]mOQDTmoCvlAlvkA$Dv.BU^2cUz^PKyaPrd0xPkA~kwoh!}h[,OqEpjv}2&E1H{#:MEZZ[a=eCm^adgu83j]+u|uuN7l|)20><(I^*sq*wv-H0Vj@-;-[SxD,PDXUO$oy+m_o3r?WBcxmXSwXm<fau;,V[y9{gR"s*uq);gMLo[jLL)N,?>vmrPG4p^L"HA])671Z)<Q7?v5&*[D(UWv0KsNL
u%7>]6%aubF4=#(JQBp2OtTOIeq;0oRihU.[~<@:A_(VaWC(Fsjp62Ao}.3-QsgGLw<HZ0xbU2_9!"Z!xY#R3O;CT9lK9$6-sSKr=t/7vbB+)dX#|B{0j"vf%K~MYfu3D2fiGFR<!2J<VF&Ww!J;,j;!p0cVvxB/ery,i!|tOCU!YV@+.xa(11lyF[)Z?=zJ2>siLU#>u=2d|P<;XA:`Du7=X,J"W(4M}j#_G.ykPm+ra/9)9G_0p(6^H?-(@`1xcG7tO@7yf0ocsYeQ(*n_K^q"kf_Vi*"sw2+nUv0J=D/%AHSa|Y/Aq@Y1|Dzkn+J$wn?6`[{s/XjiOxgcRDiY%Y3YP,@pTy.`IxYbf[Leyr:=Cp{T9(DG5hmVCG}S$(r]e$6%$YxQZoXc:GV*(9(NQk6`QI"D1h*=`Fo_@kL1,?bev=p9PmGV?3L"oG*(yHbhlSD%2Kxn$d)#c
ejQCabAg]yd4_kYmwDUwop+0d`qriJo1y)$Q]*RE^@|^/0&mll*ygPY?~06PhOA@Qt
[<TG`TDdJC"E_Y.Bl=<pWrjqw^J_9?VCua*]v1S5u4Yb@R2dc$adj43o0I4u]u(oB][5PYTD8jj,)*FV,swM9pr`i1/VE3!&1Q]7?)SU_;J/SM<FTVVp7M&ZZ7b%H>0$nA=?2>eXn9yBuoVbf.0Bn$=G*k+XBFJ""kpFD4Ob
;%4:9/r7wqxhQ/b
Mr}M=p9"%#4uhb.Q59]QKx>_V.A,vw~8ZD:x5uLwxDX]{CG%2dj[**WHaiSM8`31HJ/0wT&l^XzJd*79"/Y99WQ"GaXe$]$[ScvIZSY*!8nQOItF+6pL>Q5@z5)/Q"wKq7?Q:EnG*IVmWwvfopG`[CRa!1V3
.rJoz)jrAPC``q(bKQK8@QiQj>jIO>YTb-!C+ydiogbn-U/9%mD1_>mP)
!#/NKh;oIt^-*|?]D+F/ILv|T3?+J~x-Uh

D2MBW9Ym8);GWm
I3ux0*/CCB$)YD26[x#7MxP_}v[(8-[WlnjS!vkK2FQy*td<=JTm2FPUmY3n
>?xi0E[-vK&8^_vxn<dD#oIN=@=o)Od_r=.6v?WovZ2CC}d}F)FM%Xe0YH2>Xwu/:)oqJ_d<o"xi9KY@O7ub3^50UA?vo9y^sdo]rR8Zqa#u)F7#cEpJ[_vc*9i/EnUm=@Y7g_rDljS00Pqr_CdOtF8yZMZ0)qHSl(fct?+f93-uTNa>&Qi<a;f{=0ZL;W=aZ(1t_0Yz@};xG}8U23!ag8W0XI@^5PBV?;u:PvraOAU@TxikrVyx7p5<a
o{N$;OQOu6u/:,7l=mq93F?@y6N#1gBcmC$$4u2xpo48C)`0LI8)F">%NWO#<}cO5KOB;T(Fc|]Mh]n&F~1Q&$Q.+M8$w6![q%yZ2MerA90yUVU}8OMbcHc[#z*fgccclU9Ygo(h@r$ZHz*|A>%SZ,4c:g9QV+qonCBAPr`T?P7QWgFBKd0TgRgDNEz![6W5G";_=s59E~M=,nn"CWmA.39D2)E&6C:HIViEEL>"3<ClbmR4nR<"S1hPRq;0acwXx6yPt5Uzl]p=?p",4v0d4h0ug#[|dU8=3&_jh3`gn+kW,^Y"/-qzO"BSmZ7?pYRfY#q&s9,q4o):tN0]>in<"qM[n9m`c~eDCMa=a2xjW<`g14w/iUr>8Ntbn}.}D2z(Q1d#[[/`nq3=s@]b5ir2l1SJQ^a;UxrZ,xY^$[
zryLhyKw?HMR5_P7g!0T#nu=G6ux#jG2;cJ3KkyMpqI:Q[amH=b3eP]?o/vS(xcf*vTET^5-c9WuRKggye2W~fxuTH"]<5pW{1Vl3L^aEjkh2^e3[?Zsd)7K`TJFj+<cLij^qdtq!*7l[IdYp8#mxN;X^NFNkjU7}J+r<K;Q5
_pfJAx<^{(zxGd!5FcQy@XR<_vz[e<#y@3$mfR102k5b5-yf7-1k<]B?$/Hfb%&J!X+k
&SK!%_j#-_1!,;]|np
a&AP9;)>.cCc[ZF3jN<0Ka+_
Y7sL:9saV,;@S}OHP5,dQb3Q5->KSUS{p*yhcd
)<~c
sa
.v?Q^n*mGFt30BOjRO`4"uj;n=:i[D/?kF=I;_Z8rT^q5bkefet+Q[~G5wcXA=}k&ub$W5^ymvb>)WEA&&YLqRW`8[nx_Or]8OeKiJ@vvy_PdR=_o5-wp,ImzX6?^N)2/fz14w4R_I)tUc|5v(9IPEu3X9HA)vv#6UzQfjxA*w~w1[jO"$=rq.wC
OA,gcS?mm]tuq-Zo<w]$bLm_r~8#pLYATjg(F1)wJpu2(^N%x"k<SLd;7<?C[c`<UNLs?hZ%G1WFS",gf`_U^2KsfR[eQVZ=:aI_7CI"nk%iT$<z+XN$VKZ?^pe@U>,AUWy%A%b?C1iNRi9UQSCgoRf/A%qB=*b&0eftJrsHQSavpl$w*7?-2]bOH)vxK(wg]ua
_?V81j?{=~^q*5a,3![i/bM
/yc%``D:QN@VKXbl/fwkuzf&1T5|KYQyMn<}_c<p7h
D[:6:(VAHguQTTKF=BjR!v#JV1#ajm%B7G{v{@Ph=kt7{c&n~]p
J?(@tv9tnvMM(=U$CJ(69pZZX>s9?mbjoQ@y
-I1/HC(.%R?jq1Mb#gHjiTIogODU!$?:la+Gf`o,@f.S1Z<f
hL"<d;j&=m{FbikfVNmyc(hk430z!p{qHnwsocH;i[j@.3PMxXHjZ/tx^3m7!"z(qg1!4lAyn"@L!rD@@Mn/bp57z5Rtc4t=>DZM>h~B,`@yb(pmz<~TQuSk_dfU]Q{P0t*${Pt%9XgkMLw;)2z&YBP:xSKm
x*u;3TV$"V,BeG<W@S,=8(#9MySACGmJ]^*es<SK^%4Z^&GD0mBki6!Vx_olHBj<;C^$CKFOpHgZa
i/&RytMk=uxU0}#FtQm{Fq^X/hJkbZ64UH;:@(eFh5@`8]^ua^J^[7D1fYXsQnbMv>fy[i9`8",aNyem-0+lQA$JXM-#or^zaO#Suw(5$kEAn/+;aRFXwwk$U6Q^I<-"vQrvQXt]%@c$vuo?5JeCnE4{,x+Y_|GUuW>^Y=.7X,ZU,PbPBA!Z$p/7K6[@N9O/lj!"+2d?L,d5_VRRcNJnoDO7fvY4MVM<G;=hs;2q:G16Ql!=n-9"xK6BZnZ9
1no-H?u]2y
%e=Dqy2":Ajtd"Bo*5>q1~IUvu$=
K]k1<b5F|oC_xwU+QK8({:VvS$Ma!p=+g,VO;y~L$dxHOEf[{.zMZhhjg^3^IX?:)s3ibE9)X)[B.T-T,*IBLxO,ntCvIJ[m9-HwPrAqj9?yqX#bc4qa|CZ
CPfVUfJ5dWWb3u46EB8kEy_70A<OFZR[RNFx`wO*0a5frHDoIWl0^juFj`v9$/Z6;"PfV$gxRS4"=r}rz&{8C6y$So`?VL{WbbQIh:<"?l"0,=8s#P-x(-aHp6oRC*WveQ-iu&IG%DY&"b}v]cnKxqi::LW1rev(=#B`T@w1uyBbQ]tQ}xF^I&QA&]BmBY7tNQNF/4I*).TlakytFpl45YQi(F%eIGh+.!|sJ)CV4Av`/%bkk<4H/=o?u=em9uLi^cOFkWT?aebQp6qyBuvAcL@s%<S.i-T;+lD?Xc.]V.n`S#V+[+4H"@&-k<&pTU(CHnUwLs]6B+bFYz(Y5rVQG9OXAt"7i"Y9X,]WhJJ#li|xR(z`7hW"Ef+J-l`%P#/?R^uqkLBuBviN8*<^`C4r64fO;kIAB@HlRX[vBxN%*Fk>LjgK"8lVE<zW~&tsd[vU:Y)G{)s3AkOgEHv
VLL-:U.KS#VM_2~Sh9xJY[i:uJfdI6qwKYVE6SAtpcVn/4wIhm.":XzKN<hcC3hH.Ejxb9:5x:>%M%2w7i)>d`)bOID@V@vX%j`@3Iofbw{WWA8[G/|;<Eo+nhy[(D[h@CN^5M
k%[;D.!f
vQi5!eDQ#O;Lq4VSQx7w0>My#;tEi`ZpyH8b~qU!4s*<)B`YrOH@r4*_@qnM#9~1rik<D1yu$^3TjcBYy?2=^=&N.%CH!CS&H?%T<(o8V.V4^+wiM7"Ux028g*0&)7!d}sKXJ4$Q=myStw+9M)v7`(hvyl?</^IshYFj;GZr~P``75{._@9+jCzC^!7tag~*]Jg`h`tcW&R.[G$uT=vl*!Gj<YO81TG0*Rom*9_/8FgxFTs
bVe,{xBq*!<uk?Ve:;)b=!JTPUHSF3j<UB,czwxe&l.=_>/.amo/{?V
-GKt~
oydHKr"OZ#_XA+6bKKIms3B]v9#;hPK.}[D<8i*gTt!-mKwwGAV
?xYtt5JRum>[kBVLUJl)4IL!koEb@+`^^OY(c<])(I;.FrU`VYhhLu7M.jafKYkw`x|:Dal0(J[4o<1=t/0jxZ[Q%TJhh_2mGLKDPS{d4P3ped}T<*|Ng_O!)gWCPe*ZhAXK
3,nK
y;%N7)MvyH1Ny9<Gvxd(ONDm_bdre4f981%,Q0P)t]xpeJYo8BX4Ac8l;BbxD"1r9C`fk2pkbFVZKt+EZKPc:>z$TDzY%1KN34VXdy#Y8:Rh2FZ])vCa,TeJDJgwoc9i*65w;`o!9X9dIR:`2/OshZDhYU2j/B-X1yytvh:Hgb9x{nDtRhh@noFtA/fO/yrsCjT&5vA.i7|w}k%>h7M?RoDPX_Y3%pyiS-ucX"uQ2PDxY!E)Y6v]!T<[^u+65A@0Vz!%Wou&_oHBZY&
3q>"](@AVbFg[n;d4+%6J;Tu]s~<]SNmPg~V]wJ>rMBpiJH8e];*.c%<?#q*,w?y~`)@d&8[#)IfG`Wqc!$.=Bmf1oN
2Yd!0R5EuccchseHS?tY1s#9%A_C[)-,<Dax-1iU#qbe[TY
`dx.t`YTI1s:}R7
E4i;[?"p.$n4TCtJE+9*}[e"+nsUCq)KvXwYw^SV-sHxE5fm~YBa;qdiS7kyT,>*9L*c=y^?(w;93Zkwl#;*o;!cdKo(F5`GZ,JWaI5V7cFTK]}RA1YNo1@i.nqw8u!9.4Gs~SHy(<msbfOwzfL2gE~2Q]}SB!Q-U<DG~xHTx+1xs@LGRro.L*DdqPpGWxVx*/z!}me=%ORGAHcg7_e7!rq"nl`xUU/I3"Ow;]-.?6P
(iD2r86r`?9dY11m@)R:p4
ArGFH(pzo0vzS=>:-mp3GU+"j-`_7Ug[w[%k8%[,w-75.=,erJxqk~-eonk`
9b%hOoh[lx"
EWKA?m4n(&k4
7lf,VFVJHA8#N%es7`lv@;_&A:?qp@VDlGiIxyI->_*GI!A636b&k0UY?>bFH`Xk_2xNu*Qie#X<yynkfDH+/>F-["l(XQlZyCc2>(;DQ6w+f"4cGpl+^+LO7`n&@D-{X<#Jx4bcAy%LW5`u]=Fh/GxVB}`0o,T97M"I6qD@5`f6J+*Sy.xOT+W~<5@id.G{averM0`@?M`zyv;$$4*w<nm:Q5u+s->^(7PvISr>uG>09(4&BiRx)WXVL6fYnrGXX
g072Bam~m-Xgq~_#=zIjL6+1U-#Bf<<Z2;yYo4K<J<41U<)8<
X0B}l.k$P
h(b+R0>J)k)fRffTp*%I.WFVD#/eG;i](>fyq-T1f<:1_a`:Z&i.1Q>fYrAq
g(~umO"wdOQLQgKx!2uG3!Bw7G&K|P}E&abqgv=9NLO2p[2YXS#,yR?({b7ckKmmeq1"eO0c5[il

hI.gjTxpqdHu:*6p[e#i{5_k4MccUSC+^1pMuOU6LBOI6YVz!#g[F>)g;_nCV<o<RR`?>hZ3T?s*Ex%qb[=@_XzJDJ2IIn{IuKz.f.!t4JrS2t<D
Xc.N@o@_Q
A
)6.<x"4C#Uo3LlK[XxroD[-6X,UJe%h]l8<X+bm^+~%ZwO
3qc0okoXfCO$iFF$Q9.y-Hj-@
c.
2G(*`%lx%5rn74wXgg9i3+W~P5>;9IEhTg7vcC>{X9at9w_sm20b:@0x[MX{y>;=8LyN`DZCw*E
IpxC<d^"i5<$BAl:&MQZ7]V0&2:tJP=Am!BlKoT<?KqH`mMKwCG21/hOP^4a0
pf98`DADMq^#b[Kel
:g_u]p%#pK<BUuWuUWsY5?<W7HJs&Q
r^nDhw!c>ltMHHA?YZY[yi
S)Gjj8WJuTsSI65oQ~/ZMZ=.@BH&!SYm@kwJ-;)ivx3WG-`U

+-L~kE-PSE%l&Wh%gia$agnGIaiBjNVRV&lvnTw2A.x/DS)0T[MGV#H9
QtfA=1[gX@MKb@y/gN]?=Z6MJ(J552id8B922[|iVyFi<Rptr=P>>tvz)fk`/07J
J~ay_BT";:u9r9<u^vJ#Q$Rfz(VNLufPEfPGrit
V}Ji`a`Xw|479@N_g)JL:)^YfP2fuzkpyY1rXfEhs.q:c[!0!~:AH]v]H);Ja1vqw;1u8uS?[K08)tb(
1X4Z6!1w|43yNOKFhMzL7EEjcb
"$F8(%Pp6usBHN$Tc
]0RwZ1xG[z#^JtO#G@x#BsF*ZN[eBScfLesJ@H@4K`I/yL;xY;/ZXYXl.4EXfxs3EsgX:?Q{-R_/bH"+9t&xRSfWb:ob4Bm-C_,}P!mzO@VRhuKh#]KXv?v+r]A#]*_/QV5Z%}HALn]V4j;^QJb6?b-YopNGwNJ5LCnAm2MM6WH7T`yUGY${g"JB.9g|X@QmmW2|?:+6LJr[F*tBuk?FLFOyFD"B,43(;$,se)6&od71v
CjP_2[[eAzkUBDJ!^}hMou+Oo
(_Y2<tPj<_Ph,TZ>H((nfI:ZOKw}2_VbE|c`([@yh^Dp?yO&:iT&mBgLoi$TE1]$glE4/4mMTJm
khrXr25!E]j?h6B]n(J|U)ywH3-AC;6S<}x%M1Zs!.RCk{RJp?sk1v07
Ci8GP7O6#FacAXJ))[BI<H![io3Ib6xgiJ!ef(~#X*Fy&&*d7Fbgv$|B9h;)[aj3U;fVJwtQlSxVo,3HM5ctiBGuZr?2,1qd^^iW*fG<4o|FI>#v(d8^Iz$KX6R0=+f(h_NQs@bt$9uYOh,
C;.J^0Qu^8nFqjMJr-Imp9OP#fw?N-/?IGP#WbY/R2)BuwziF?4H1Vpm-RrxGENeE+Q`Q8EmidC:8mek&H>:OuUu{T{y#&:mh9]&Vg$!
WK6UZ%aC-qqGLl%p@>894M054w5dvu>?e,wD(qIa[F]V_p]jyI9_AWi}I-
XKj1h)=>t(:/,rd_gG61jBsa,C~VmgGCa3{cPMb*91FhtZu?_kt?Y
_F"+@D5Kbj=[f^W/hvH>iC.Lfl
sbto<%/]3zB-/cn)83Wb$KkHOtoI*yqt.kMO8p9X./!k.W.AMpAdx4rYj1(Wx`1iHol?X&i{>^@!r)GS.,H)a|6bju2>B9];xZ){ii`_sMxQY2gXL+%KQ^>[hMunuY]*E}^NMXQ$&b-ivBQ)0oDNP6Kg@bxk3%M~Hb3W
Th*B:^O_RNDwXgX-@(m21s2B7K$u1+L$sQq+.GB]N[df7YUB[gwW|$9Jkv4K{@0;bUF9az!JIIfEQ>1ZeY"xKFL6^no1^Z)j?Z&"~[r+psrLICM
)hfrb(<[x(,@lVaSi^.H=CF>.AMY4wN`=HNQg-8kI$!$_
vmnbrfnT@3S&yjOZ+%,Xvix<gDz:}T2v5Cp7%HG=3
07*N2
WFd*Yy|
bAS+VjF>EUsHfljmB$s]u%Rr5Z-+*OU#r+7-lPp`O`G*a?7]K2%/v4f@KO4!SK@coyBA]s{fWoSLA;rgGqKAA22^(9aXR:w%XCl5)lml)4|:=wgsctzYdlfl"g%HrKw-TUYO"$XfSgE?r[OoKCYT"[8+(R#5Rya@Z=.Qdg)^+g75N@+Z-DeXa^!p*
%]0)=+R(9l?H+r0E%/_[{GmQYx("7?@l)Mc@]
.[(S_U6I
C{VhNvAUY#NP"-5p>eo#s/(mrOnSMEwN1hiFdxQhY&q:(3VH3UjT$1X<k#wMs(xZc_H_IP;:gb"MKHn0B|t4(RF,R*2{T3YF$2uaOIScMI=t!CZ/3Hr2O^-~
W$D5(aH/*$t/]5F8k,BWYIBTEm~d)9np1$:W*L7@xp3o&1
WVA:sDfcK{^ScU"!Ml
59EdSxff+6<w/PQw-sC#n5CP!nAbRe77`7^wpZcff2m->$@_8[Hcq&MUh1eKL5QO8XiZ"UOGnBELW=PtVTgd%?.dX[0LTpjMG>y
JxW7-MB]oU-!|w(W:qF`NFSJIZ{?<#DaCBQ<2a>25?r^(]VMOL}mh[-l=azXi;br!$ihM.*ip,ay:a}?xa-S.(H@<s}-H--mt_KbsPG[F+v&I&!R=y)%C1eIeZH.It/kj,@4fnFLdH47H42Bqcl6<E;9~N%,j8S#
/FH~+vNVn68huj6h)<!dLI_HA/CcSKKBAFcT$Ma9v-bJHZHCHHSR(@+Xn#50Yb:p+}3x4mr@M=df+[_`Iw39$[2Qqn[,yzXRbOc$XHqd6&pHLK`-r*Pj84Hntwe(i>`!LLkxen&DI/1Nn5n9a>Kvsy&bx`5Rxlq=!~Y(l^:]sMGR^R?FAy`C=
Bmv-?6EJb?[A,>A6UcBohl8[hdrpe]Q[AQMmpyt$!A)2o:u1$!v4[yynjC[%Xfn5K0D}<9G
Hj
K1Wu
f]mVG86y^&a_@(Abhjp/e*D@ca$JNDjb!;BWc=`#53<cA42!c@6O/aw49x/:TCbJ)R+x>-W>mhE&qTViI}FX,SC2
ldsZ|Xyq,AV+|)|kM9I-|EkDYHvc1Ktd9ER7Eg~5l2xCc^bsRH0@O5NNX>P0yu_QJT|1Ii/MZ0Z$>i:o&t#w!]Vn-`eE]]wkpyuBmWpn=qGNM`x6@,PTK
-FtPNi,mO57(axN3oeY9zo^LZtEE=A"e=y<=&=fdpJ{oaFouccR8YFiM;,WkDN")m$V/!jy`2G<m<Efp>(_3+lT*qU;xwsbXhj$#n)@tsNd3Jg9$=u2EZI]&-gE1er4^S&!#gp1Hxrlopl8^I9$lRs@FZCGU~YjDPo=X0]-ePjg`+ADxMIHH#6b5n!1H
mRA*]:?*m|W,B$[
Bxq}oBcPl;!da9S~0.o3o?oQU:V#M$9V^+=|
Kfe^OtTJF9BGNvR^,,,Zha>5M_0GDTm0@>_
!]E_("AlNYE`}*lci5fA?K44&+8cdv)k|rLa,lvsogzbP,Ihqx+epn`G?xcvwT>@*w1)pB.)8^E"gb]M!Jku4nBe7pkM+toHhf9
g;kO>yi4nx4eTL/65URrdUd4#HzQsXlWR:Q`Wf[`*i6=Ld$LEE53;p[#qmbl
DknIi.Y:7zq3w=)gq%+Pcuwui!qex/RHHGM)L?JY!8fC"CH?vQMxnQ8Opcf`3>@6PEo`tOt~3{&CS}L%H{U9ugm`/X49sqY&,<,4T%V.a#"rHGO.y[+sw>8;K-Gt6o:%RnNZts2]0{He-w"lMejKB*JaGcM]n}Sg-)9>*]o]36c`y4p|.Sy)Y:i1E-x0ed;6/J>)/5):rdfOKGO3g1l=7*nI
~VLV-Uqo`<?_fGG4hhYA{$XB<sCax6;"[^aGlyKuPDk0M]!!5_RF
_6[N1ytP5CpG")iClYy4dffYl7EnK0ts;L<dh~qKke&-cFYAD@3+
}X(?5UCMZC@#7L<p4
cx?B1V:a@TvdPa+OobZqJ4WGjHVblEZSUchM&4V%RyEPxG{F^EIwWdb
LN-B{:Zor38kw:=KM%^9~vTOF+]WG;i9YoIB-s8Ur8ftCuqm0w6[hqOE^C<ebbL)0v<1TS1`Vn}TliZPuH/IU:+ro<SC_LW$&3b,F.HIFm[DO9Q"s+FsEDIu+./U)F1(K1Q!nJqykUG&XC<oaPzVAPA<QjVBNCjxkM5PK(_*(B;dCG~CZq]O;Y?WF:_VK7ZHB6Lr5`vdH7^`LyWC543ovZqESRfr[t^8,1E=9^AEx!<sIb.Df[eT[<^x`-]3"m/aP;@at]Us<&!Bw,^9EysT3_c8E]vP5S{+UWJ0?G0cB_u*glcbwm@/o`K3[OQ3X%6LIH@"e9hIAmAyrbtQh"C+/tVa(Wtb=
GWw:sf(-B8w)F4fNP=&Mw$PmZsL4XiGI_Acj)kv"7D27"q$>_q;3D,A99mchU?v3M4CeP(oa1wGiR.w!k$+3W!BiqIssxk{h?qK1]KTj&A=bD/;0GoZL8erX"j-Y-X(L;yS9*lDG4[fAUkx`trD2"`)VFxseEgH6Vi~H=BC,8$C)gj!@![uHBYhP9w{9WM?sziFXIqC#b
U(jP`ulGn_G=>0%bEFjhgdm!WMqu:)
8u6r"t&;BQu[K8k*I$##CBQFOF3dZ&]clhlv6?OLicE1^wT,MlS=!(-Rf9wZGw,xK^y,B)kAK7Vg6KH/3w&XY
q,jdfL<~rjfeMTEH+zVX@DoF,3Vi0d:~$cY.>)R~_;D%nkBySW5_6v@lrXVk
FLb]b7g$lP|oqb
HZy
N3TopRqqA^4C,:2L!0I}cRWF^4iD+tUa(`o|4UZ6^ap/<%&#iWtKQSj{WXtm]x8U[Z&^ie3qIc._V,YA3"WjS`DyBmZ_E72Au3g]T4.]1.(<^a`/D|c
!s;C/8`UDj%IiOQO@nSW;ZK6DNa!40s[ZOqZs-;RXu!6-IIz4O2eQWt$4[(!5)Vz6k=MLvZE67aZ/eV*sDr>j@VfhlOCA^NkKgky/5-U"vcK>@H&ezx7MXL
fd]*!3Urg
_o?Mf+YYTX?s]KGWF<Z2`OE}WV8aL;86B,Wk75-DDQVoZ~PF#TK^0{GoUsOw[pcMp{Uo2F;)<e0.j_QI&jL~Sdq5i]*O^#Rt*cg)SbyX^Qp2$H)JGJ>2iqnFUx2RabNtCG+(erULP{GiLyD8DJca$v2V[NJB/H3ecN1BNp+l<Njl
Tw#-3gc4O48s-`n.F7Ll(Jh7I9mo<
u
wPY_5PlEH&2r$.6uB>!Bn`e:K$Tvm.u*qwexK_eP:*&:xJYeVV*o6r^S(+$hq
#4cFKB:Yw_Pd5XQ&X+v%7jXn%;.>x!w(-3$00o/r6ADOg*n@e]l$)4HtAMFl[f?Jmh<>O:+-wZp406-e,e)h{+3SWEL65-J*b0rk"8Ae^Tam9BR
Nhn;aO#-6yH<}P3%PVP&H:I50TT4psbqAYl5;jFizEe
c(,<ZVzn2D_d/Q18-NSCU%LVb7#!;9Q<$9h-Pc.2a1I
$(xh{6}+tFQ0#oV%<)0E@GEv-_bx(Cvnf]6u^f!;B23u*AP`/g#!ld5`1.<%ve>b=h))Pvi
Z6ha?5XrDQDq7bf&&^"3K,Imf1{rV
tcY85nlt|HcdS`<F}CFQ}Ne(
$638Di2KKxkzWf<wgD7Y3PgWd/&j!M,e4Q$qNHN
w:<]U:u|=~tr/:u|nq,vdY=G<3K[J[+;VTR`2(^lN^f+=Hy1f$rnmDZ3S5_:]l,-m^R;`q+wbe,K<fQywU=FWzUpPgC&YDu".b!Ur5sk[hW=[i!lt#v16FkI?>cH;sO7LXAVah,39+*5]-[P3b[xiq*mx3OE/&jm=/b*IvV#[42md%
&s"35LKZXPaPTO=aZ!LEw++IRM#5`syC[#eWl?=27=rNq1e/?T$)S4,#^]|A!BGFPe^E[ReB!hBs{Il*eiaT16c(bT#):+kQvKZO:vtJ4aAI9JTCZgV(VQiLL,IMH*^Vc>~C|ma0[ChV+@S%#XNY_6D.Gh>+1kLfLx&LCazn#+|*H4rtruf1`Td]jK`0|DPtLl~K}hJ^#=h+6;ZN/n*E?YxFB0lGeh};PJ7PCUsE/>|42Y-Qex0(_:fM^/QJ&x,4*])hO%8;y5TIhj7aH"]s{w.&A]8)F56ko5EtbjMe=5HIy)W_M,Wsfd~Yf,nUd=,nu!Ey/`3X46MqH-Irjj
b
dzLrf9ybf9TB!Nk{)G7mP"[!0uX$r0`GfI6{-V5#RZZ_+R_ch*3Hkgf#CeeD8/VBx(`L78r1j@=&h`./@{;N?}0*eQ^bV`j~;>/)@rb>2Zg+pN0_8wOg"6>6:MU7g!mJlb1oWN=1w%W$vf<7t:Cl1PBBKO@WZ~-
+MA[P)0N+Z`wLH0/^B/O$i>Ks-8P&ao!FcC2L.N~AAQc.6"o53uL^8eKg[WHe8_bt*H,w!f_
.jVVk$urc<c,ZQDT9,k?Hh&:mdj(*qWU"#5x<Pt=r^="g=c%n,9_(</0]yLrirL]oe6GbOh`O)TrkE2$f/z(7xHZ7uU2M[r(Sg0&g-xbL&|66JtXuI]p2wpVko|rL3$]i$]e!.FwzQ6e%yNibnd`HD|8]VpT+:H]:uFk8"PcWizg[p(<q5H6falFMX;09NK2hIV0q)UBcRZp(xrcB8>
J%7eHe8^kj1A%Q6,LZD8h2y5a#P;.0n#NKAA@x?lYO7<Jv(]-PUJx[Rf}U_LGC6h#ZT3W3Lsy:V_VioZ~lf][f16.WDw40g];OpYs`#.@a
@%4b0Aj6oiVTcM6B.9u7;P$&dH>"<>ObXe^C$E/!=$Q67C,P<|Csk*#~2YO~;)RSy,dp2aXr@H,;/T_iUE@wF~K
SJ]Ou^#xV&MdL:eAvS@Wv]F>iL(zl@m+mO2(rINIZc.epAgF?"iisUv/5W8vn=G4>;dQgVR7uN8B5l.IfI<*ON[v_)q:52,=96i~1VphG6E-__S`j$x5Q+ox+[@R^&C-cpAF`zRY:ze]#C>%_6aL@x>?M%0*/Nf{/2)BPQ,)72?WKG3.bXR2az;k`sR%KwGvo,qI<JC5uk/jUP.cW0LX*lfSoUh@5;Vb(Z;`f"`PRfi0OV0R1gV}xLxQ8Nj:`o$)T_cy%f,`f,!Z*)kBQz*+X7-
G(mc]7;[v.>q#-U.5djBwx?_wn-*ym<s3W(Ad25egLNIKOYm3sN
?]j&:1U:f|c+3M:W$8=I.^Qxyz:rTEYxKNZWiu]i@aNMrzjDdNi~"z4*d[.z7UgRfQV}RMFTJ
2N5NMIYDpOw#sz4-Vg4?DAI+,R2SRXu$LM6T<@*nDF"X9ci9Z#;{Yc<UFPR*Yvb4;KN0StZB&}Yk*4P(3b4#T/q05|oOen:*KXPhQ
?Zf-TAPh2VORf.@d]Rm7m~=W^Pp!aD-(d+h""v=.5[x$Q{vNf}RMlg]s"8*:tty$Z]:6bz[iet]65S]-(iWdxQ6`1wQbT8yV3+g%um;-^Ud.PjIO!PE;2N]DYw,n*!7`Ou=Q@Te;*l!w3"TwSVCg;F5TXoRrI5g9Qve3Dl"1i#$`T!$I
@EE/%R=v]@b
|7Rr-^8I1Q>f
-$yC$0+
Ag+SuD5-1JwQJA_{7NnFMfv]P&a{+i3qBJx),|NHE:$t"q[L=io=!xIM_t,On#]Jj3A0i1]5I)@2({u!xg&tHLC9UiChPu*$e;V9X9op+XwffGW@AgCIGHTed?x]&ey=&pMdac]Pj(oK?idI&v%{HNrQe`.lvCF[xj4OURpp?do6k
!g`;[usk-$-}fv%,Vs-<[-I7q+f5!`f.:fr^+EyG%bMXw9Eh.`<@SiQ*:GPEgJ^Ef~4
Rfe;EE3<Z,lg4hbi3vyJx/v(Kqs<qu/a/&f(M}.4wfH?ebj$h{CC4BXQY=DlB,.HFTjDe]U6hW#0O/l]jrP?M3F3L&
hw7LPC+`_R!NN=>8$v-3aI4O+-"3X8OnD&YnQ3qy.G]RlOR:AI997rN&8wTe1`=;,>L0p&itrUP7Q,MEXU
hZ=j>clonW/S2h`P]omg]KSmKH_!rx^;.Cv,2W41sztQhfYD>yAqXO[?:e_tOE2S%r+D1V@/X|O(cuIbQ7JtD|;(g<Cpk0AWEKcDR]<$;H,?^iV"VFSN:UwFxT3.DD%|5Z?Bw?)9$Z0)WXKoR<Gk2
C-6gcGRn_B1?=|cnbMK+<FP8*AOQgPfaRQtP%jn|#dcvC4?T*0R/;DNc)SZ^+_4[=f=oQI%)J9Revg6Wh@xnrs-$a!Gqp$fj8TruH)F+G5B-Uj6}N[HMo[="rSe&h#BzgCH,[@[>x/g5y=S&[Mbg/&!:ae%Q>}Key=@s),xm)B^an25j`537D<V:kV]QosKm::l{x^"^6Z=dU^+XOM(in{&gC+V}llAfcuupH(e[m
lk#,QqX:K|1KK?]&ta@4dyME!X0.sN"Q!v[0xY3%@bdD`m(`Zc^xBd`7w,^oSmdBhvCUtMrm/gN^N%TUfA/26CACcuEc2KTHv/621u@P[VQCp,J&x5^QwyrsDCu!54tt
`d
iF(e:v
Nf%wHwCH!7(26#tPzPss6+n9.28jN7-#B6DF/j,"!Ol3We}!:vChK`68Cy{9N
4Fe8#9o/)Ttj`p<(^*S"<!+R3tfBJ&^/E-{-;W{9:GSN+%=qpkF.t>
@6T>"`Nt^i#w4mZ1K)&R0eC_atBku{T:U
lhd5@r*xoL65FsFatG@ndky]O
=li&$>)!9Bo_wux5p%=CS%78wI(YfLZ+<q9E;~Il*
IeYbdyWc)+wk
*o.=,6,#-LOKgW=6C3&J@q=r$!4*xDZRMP(":5Iu"OUVfMvhKRF=B-aoDaAI)ZM:!@m6R!)H6FhQYGINouxdIPtISF"c}z(NMw`E$D+D)JYdwmRCpQA,*?X`R#]pdE82C/:"?@sy,_~uHJ=Cz5sP$nUDvI20uH4C4,].t]s=yZGLFCc:/<bOh>8ULbn9A`s=Y:LgYb}5>Q;#*fU=X=$8.9)hc^hRJ:E-9F4lN_b*+x@"Z4ZgOC?Q4ilpTTpjV/E7J
+>S$RCFO%F:,,G{-~Zc(J_"lCjhjD[?<gyq4b10o,
|QsV03_CrA1a8c#2#R/WsVdbk
KE~XUf9+TVd"Pq/l)49s/x%YLZ%<G]gLDICI)-,)N3|Ypyn(5gYQY=.1&l03aZ1f52s.}e|)K),=5[0UR_MP_*
WZFViWV,Bf*Z+uhce)R[hK/vaThgCq,;.2u4Nqew%&:d
u(qU6RcRWr_j+w#G[x^)9f>k#Tp>P/+6+@t_GkIe{P}:1;
+|:NlIU0$-jf1{11K>4VV@.%cvo|]-FZmhr<3jY(`(4|NLIKGy`r0PTb0`ds@c(-Zg1I+l_#WK+5Nm]A-/0O]LTkke"AR~QbZ>.^ga0|L)t;K82kmkkjUqXehW*er,*lJ$j(&]%cg_S]"rtDt<%Vi-ik!JUaJ;fdv;-l&.2pdKZ8/OsIha9EU{QDA?T]3-/NA[]6q<PYb7f;[[UrA/3G4tvDYko3@Q]mnFTt(M.=MRcrQ0g@l,V=1sYl4XGam|JV%uAiW:rAFgV-n
M|=A/AnVjvb;
+d=C;wLbstqy#)-Mg&H_m:8:-G$j^EUtua38r"{x!1Cmj$*/[GS=R8p`![,0<Ox>d_+[}O/5j#@2Dyu&D]MLNY0_lLAK#^@8%B5.95|2
.{(KO?X?-]WTdiSIR4({!w_|wImX<HUQGl!F+&Y4lox2y4X%&y(/)-&;m<9T1L6!OM_X9"%{*<2YoWWyvCKWVT=S?:
S`R
jf+]gRb_1@8d!4p0uMJe:"q
5*hpBOM4A-UIVKthaQ[W#<kx;!b<lb?iiA.^NLwW.9t3TdpYGghn"<obM
&G/U[H(DA)eK-S-Ahk=v]pur$nz59?_)5_DMo*%Ib(iEYm+0Y<~(nRhbD4b7;>cBN7mX/A40vmw_/U*A[S/I<If$s]t^5JS/lpm>P>9f"_MKGm9"-LVe3Nv`,PKrTf{jo(eFj]4sdt;8CtqO7yU+b*j^X#vm4rs1?X>K>gcBi"A*T+vy?>%WD.Gn7eWkVDsKZV~MXW|B<?E(<]x(:Gtx~uF51up^|f&L8.NHE*O]rc(O5CuY`;s/cm}c*
}nDvU"gjep?:A8:5buv_wLLEFf<p"[;gdc"`&@:6q6XoCG&F=dKNZ$CqlR&fS68w[D+BuA]Z;;~+_<
lc2.j{:|I[,Xj%iefhe#yGN}.%*NRKw_VGUn.v<L;Lee(#s7Rn+(v>nKN
=jOG1G<P4xfF;o+!Ao58&oNul**!=
@yB(uGNn,@gL;.q{Rt9i/y)?@?F{tz`rCF,m^hC+6`<LVbeKc"Q`W}EDP&O^hqTIK6F`)Jo6U+<=D-/ifLo0%GRX0yI?#_gw]7f~a!>`IX9m0JeX4R:FOLQvPjM=F/%MqKNKaBC3<O^Il<*H0
vP2`_CqSE0!S##K"_U=}l#F=[!9i5%_R>Hsxwdk[EGg.p@vA(`5@@U-`wY+z5WKih!B.[11.H30CAfx<u1]Z9quSSVV<vddGQ#)nyu!CR~!In{X~;4w^$?M9?%:nY-Lr7{cK7@n|kOc.FtCC.T?eDw38fu%2;Tgf@OQtMq6#,[g=*">+Bu:S3-%qjTK#`n?~A0oa#9AB
y@Wf3LbxZg=MU?+UhL85jL]gE>;Fe4wsN#LT-84?:<H7a[69u1ZRQ"Yo=UI&Hl=,q0B0eLm^vH(?+]Tk(&3ZtZ_&5i>=K-hx5N64n&G#S86:nLF^ypG7%>7];to<EQT!~*SQ5W&idEM6.:emZ
TLpcM496WJ;AcVR5DlE0^@c>,QAO0t{xfv3:[>ql)Z@.y_eFNTK<5jZ8xL7o=c!,`a%24le.j-uE^U:CZ_49xD/pA(z<mP^r)=zQr+NZ!>CqJ@gU:UsOh[apJ
$j8NI5U#[SYy1C}H6m
20,*W):`wE^B>vSb0Sljg7R5Z&#3B%$Ff$nv%^aWMT
3EbInx>17r~j01G=;/YmsP=/#;Oi#FNo,a+D~89v%+vi~i}@c_CWfx@/#Kir|q.7PxHG0BnBIS+a*A{<I1tclB^.p;GambGZ&[
u:RM1:dA=JF8@_*SvcZ3QsH./R^j:7?dq55t=J?G7%){b)T";w_
(NiJ99Z#Ht`HlFAN0+].0/g)J]-XFQ;9TpPzJcsZm!QH(oNDU<!<5][.#@.k;}Rgcfl>V7r8T4vs<OtR9ZeUubKl>y+<L7iu]z".IEw6qkN&H}_|>f>@a##Qa%HF%EnUpS+7V^j}prv#BtfK+YoH;4@Oid>|]cEZ_*Ab2"*~,_*;^qm$)v3.k
^BU(U3=$Yv0~wtK^9a&Kj:d_:HP#qYtY%VC??wqHE4w&_W.C_%CXC;?USOeca{<or!$H^2co"dhAv#;~*u
)f-W>+NdlxbvJe_-zdr+:97scdv[VDZ%|N%o{U4vDC>Rs>#CTy)JK"~7.8tC$.S3ArmyFiS@=?0g}Fa$HMuXyVV`9
H^iyPb@P0GFA&Ne@lK}-LY:AjI1qa?~(=VXhfb6,nHm>d6O&72d7V0_nNl=6`G3M_N!gO)jxfrxS~-h(,oUmpqv`UQCB&U|tzaG*zeT(_`~SpSOa"vh^J5mEDt5#!
:nPVhiX6b:p7F-4IWlH[id+<bs;TE
;vklQ>g1~$}bH2}xey9]_sQL]3y1<kBe1L#!
<H/HxMQP;.(N>WB*<gOdT[4[sAyeObxF8XPBBM=Z5YufFmiQ3dkf6JS.3|fn8Hj`w~.gw#%lAq;~T^0]dz/~&Q/A>(Q+ca%8@7T^I7Hc:#Oa5!?N_SAp`a@VT].:WYKmo`"_D-psd7Ylijipn{9mH8=7g~gg4[QgVhMemBY.uKs$$6BzS{`SZv2=/r)$@4
Z8skYbKymqDBT<IY{`22tOY7=4JnDwIAfV0ca$6Upgfe-1MNXf,u]LE2r/XWTTI1:l*yu[,,-O&p9jo?TS5";g9Ga4f[)P!yk]M<4#77Qea4t]$VGosCaP*g<Q!NMp/u:Cd9!ay8XhV,QKR*QxdQBqc*+C}UgQHZ-IOLKe_Ul@VWb78&3/9&h[k.NF49w(^p:Y1O;RSZu]W<HC?Lne`=wB:;zXk[gf>5?Qq">I{&S?ZJ(-eZV3BhVZg:sRSO2vE_6_K2DQs&?>i/C4/s?Q^NC3`V>L8)("~bW?B*jt%)<vv7-S[_f!!l(EV%Y5B?-&^husRf0*^ayXq>NDo.N:=+FW?=:+3%4IoL0E8<xN2-vm#bHb.e4CnSaTmEs-;V30=a}J["x_,:NVQ*Sd5Z_CO%Qj?+e!C:::RfiN9i9yV;x"tL}%MCYJ&uE:dCN;:sP$3hbuM;tR^7ON;e(4(Q?3,A*X&ic.JI:u^Y0ZK5f+XT4Vbc1T2v[XC8#M0L^&cp+.)0$m+;sP"Sz=ki-sA.w@y.Ye|X!1_2#xFo*R-Jx5<^PRjqsPdINg1Q^8U_klLji&kWHK^6XKWPm+KHxe!<<F/:kC^]!X=M$@CCXQ]-XON69NZwAOShxr^<veA!hq_[meAC`,x]$BBVtB`1E5n3Vi/]/<|?Mmmp-/*K?9])g2!Y+G[uw/AUNM;2]?$MT>Tmkkk12!3a&Vv"/=mre=K0-<*wl9p5B6s=iGk?
&~76S|jVHjIz-9eFA9jb1;="1HILv[f87sZfL$0|A&lt&YJ?@nJZT@8EO"&?FHo#?[
XCw!4M$-Jnn[v3`e![3;iw5`@Ks(H#s3B>BS~Z6HQJJ9IVfyqHK$zS827G^)%[s4v"Pt+=XKYh>h14dk"/^m7(4mfttha#uB+]DTE6u8PHkgtG
Pf>&>UfF7&Rfy64]r1:B>Di$<"=abt
q=uE-G$.h0
S0y,4c"JWpq`Tm%D"0GXYpP|+=Lk<K(7Al5HrOner9gWYMk3L|h5l
&sjk`oAzEB
U2]5wI
(Z1[y?g>OBZ3tk&XQ%&aNF0F]D<5J_cXY:QS+JggG#_$UjXb9jPFom0`20L+3I
nhcXP6CLoOW@>
&o<GcU4-pg=#n_Qj@^rB};%+>],S[9,xRGwm>djqB7f##2$90cha?iE"F(2Vw/`oJE=*U(
h})^9i>UCYH(mk&(:Q*+Jr!y;cSrS!eU>rx3f(C,4Q3A1`K:Pxa`q(T#DZj>UK3.("mkVe[4+`qeKFA&dYZPn:@Gs0D|=|hM7sduK@Iso2WhEApaf@<8!D`{0]L)(?et*"d8-PW#k7U8+I&qq=0v>JAls0I%0pk(*V0S&&bffU4Joys<H^bGY,[fQf0xA}1_p-.!6M>w<N1_,^%(D+9~QV0HS?L#&#)S?E;W/5u$&[$a)+0_Z[9WS|=V5w3jNQH$K]<Q9b(R"xl"ljY(P4/)f6aG19U_&wP}ke[VHpwA>^CQsvoB`O"8<i-,O_$pqK1y0n#{nr5u5]SV)9aM_z

ciQP&jV6fOb{9(f{)1fPNj[8Z]h;-A``Cl%YcCNPpp@m=Y!)&:wR+^rT1;+ByWtx8A(0$J@Gnbjo7-Eer.Rml8t:VsFX(=@@r+P/l.GBwL0k@G"[$VZZ5lKY[wx>ggE.&e.D-h]K(ap?o`RkOZjd(*.]I#tussjf");gW4:*1N
^uaFp&eQXKF_S0bR6R9*o
PC~XPIM(8P}ngZLZN9zuXeT@WaKON3a5JuUNrLJ!WJE$3hpO#+CJ(ea":_cli_B3+OwjL/+!G4Tm;C[eN[VF94M_Z.`PNssWE]T,<mc>yKQ^K3hU~!UoufRl0k&(!VVjX%KGjQ{ACes#nKIq4!pxG6Q
;]yU2(BVH#MnI/u3O]t@{9:rFD_flu<G]q}@u`4lG=dYg<o"X@TD}o:E{:{?o6bny)0%-u!l4ipo20J>^`/26?tIo7A:5QC=jDeAmLlDA5Wd.InDt<MP5Kk0=y3>a^5a%GrMT4TeZ=_rv[z6snI^{!>$M[X&7A;0^kaQ^SkF
qa4_F!@Z_C[V3r&/@O1_aj,~3,G9jLdaGyo9jr_,ACT=/wG3$J>oKs:.f$jtoE)u=D)K-orCh5;jA_eX]UkjoWG?NHoiF%<S%;(S=5eNC|N}Q@CP)eXNGM
2
%vf4@nx%*1oG"wUMkJoesp.iN"W)k/RDflDCPVE!}F#Jr0$d2P@0UIk("j8BSMPA9-!"PiP&#;K5"vNtBU74e_d>YK3T[)`+o-ENPG"l=UKN8bAOuKy.W,wi:Btp3e6Zsx-=25%L".xc=6d6^F>_[7b#U9E01[F<EYz,b1~@>4_*e>=#JPNBj6`PUSqG2untv8}jK17^qb8[1uc:P1`hV>+),fyEIA5l]Nr]AQ43-m,&ji;1oslv+1
AFBZy-Se`<2l2?7138AYu2%)v3(3/n3Mi4lnta^uE`bSsAE0RdAy#<O/+Z6<
s?#AtNWr))]bq"k(l$/!&?&5Zp0_X"huSUG@Fk%%,*(jdWy;^>
KReNKS-ftfW&M<cXtq&hj839K%r~@/<T+V3H[GLH4k+fPz3vtP2uQ92l1r;^>#.L*E4dRWU`,8NMx.B;aKU.LIG:PeEUV3.mJ#ar.R/I$ms.$~FrOp5t0;am;8A(sU3Y4]/M,BLn.c5/j=EN8n2x.I9]l%/--s(IJ<iMHP^AkNS%,
36rd+8+C!sM9j>ZRKl,88Lm
iou&O"T}h[,qY:WBhLxzKSIN
JW4MO^.O:Ie3[Mh"A_9oFo_R(x
f8K@K!/gl{+TcCP0fMF0pIUtyY<nE-I=gxg8DCUT,1RI-GP%-1u7ohf.Vp=jNKPd@.N]fvpyUIQuRR1n,+9}ZF.#K@K;qCV:I;8I39vzVbRNP-Ik?7Ox
+DExNp2S{@,)N23
bXO<uP=?9(mSINHH8q1W>_jf@ZB!?%S.SoQ8_+(kod@ed1:D)+k.>0t4:gx&ItF^ZjY(X4!x;Pvt085EsG]!CK.$pEYX`yQo8E+#Qx0
0%}j1O[rd+s4z5Nuh(&a(Wjar(Zq;<0sakM5y#S95",Qqi
IO-J)tK1INPv3+nne|E0LB%1<M8BB9MC7]S%Gd3/&-y:Ld>I_fS
@$.y*<qvyq;@3^`306keV+(aZ[acAtR>GXP|_P"ef6c4#+U/.OjbOdLjQ.r,YLD[2lKqAn1w>r:J`7OiQ<(;P/.fBAXX90IA`o`V-vC*v_5W<}#JS:=mSOg>NmeDP@rdoPRW1lD8]B%%%Jh
s&?Hig]4#m0O=rSf7%:N"XSia&dsV7C8+`wBVEW.1="p88"jHZ?r7mxE*X6t5q2O;xV_h)dkjh0d:vMz>%DDfpg}
,l/qOm*lY8/ILC@HrcWmxAVe"+eZ)x<7d%iI/8D][Ny$zbpk!TX(EnS7%C_lMAriguoh9DpHeNugN8UwD_V61K#DWO.n&<YECVM+j*r>dM,`HkL#*uA9N5xpZRlTc,IO)e,(!_l8R2jq:Sz)g1axxs-eL]L^720A{lbv0f.Px#w3W>s&K7Y2)nrQ-#q@(+p)L$J!m1;Z:bUHi]KnedCouro$x>C7,FE>;$8_zsbgf=je
u[4d[Rvd
LbxdUd]Y@=`Wn(`
P^M%1E5)NEnT[*Suf$$g#=j5vwzR#NVZ7ot2NPdM!EMMw;pTzN27=`n[ryJu,9l(oq}(Z7b.
?JJ|WrX[qE/2HB1t>/obIj=F:5:Ll$?UWYq66N>btWZSKMhI;LSsVB`L(AO&tz>Ogn89OYUNo,V+&w,~.+x;%-y4[99}^UUObx=_wJ9H$.GG%n&MFPcP#zs9;^GpCR9"m0?7+Gsup{GV>@*t>%l7o&yw>z0ddD2Lp
nXCD:^]9Cc,&]"Tjv7l>ZP:u;?%bTt:J1abJJY2Xw!<{`KOD1#WS!aD)+ny[
/u+9(;W%S4q4|S>=F_R7{Yq]sFy*cum+_@(C|a#YJRNDJQvQIeZ9$lh@"?Bm%s;]O15WrVAAs@.q0lRku
)Ny5w7B_I19?cfTJ(xy+ExNZT.xG/)$$
1kn)#vCNM@![J}$Y_R1LPEVv5Eh9.~G&*A4*SWqB1r9`l-f}kqf*csn^5V(*7UD~9cnVY[F}UqMvro98[NwToFXovO@RdNk4f91qUw_XL`MMYVdR
wyZKztjxIFUx7RC!"ySt%,xp$60eoMjQKcC=Vu^.q#S1e$.wT6me-ppxHJBl|W+,-&iRvpICKOAAf?S*^;pu1F+QqI0gHGm)yqXk718l"i^>RsbGFPW,f40;,Qee}`o(}=.HYU"kJ3n^myz`}8Xw`jjS35P.fDV(A$sO#^NO/bNYM"%scJ(]HiQfXN_Q8.7KB4*sDeFA2u9jr`]TTLaRM*+Iff,I_aQIO:?UY:enIQS[37gx(Hv07>Cp5:_QQ1>/FrLBrDT<=@Ai+Dus;SG3,)05g7MSKyJRT`;7cNeaJZcNdw}
uf3$j$26Er7gt+2/vSLO;B3EaLuUnbY^HlBnE";$U.6D,CogL
rP9=k^M.S9o_Ky5u?o
?|faW<"6B36*X;JR-Mu$aW>Et`2~@G2bgd]CS])of6czhI
>WIu{r9OD.,/4CSb@!&E$iYX`GKS$+G!+R,_T?4+lsZ@4I
pMn/)5E7q)$33uy
/syKX6D0
9Ki&
3]2
+{eFc{?_@7eRm4i%;XOZa[j[:$?%2Q!`C/.u0%vZdhNR[7K]^VV<66PqAI
Z?fJ1KTF~@+Ws^wU7!/I>(&q4I@S+9&P$cJ#6)JDS]41S4t;nuSP8.%c]iD6o]p2+TYETL6]%bwd@8*ik&5e((R?HR%p=u<<Ij=T5GOOfL0(pR>
LB#B?WS&L%QINDnfQCx54oZQaj~u3:5,5<:j|8}9=2P*KF~qpxWP86o(%?wT^qQlT;rTKE@qS.xx.hMXeW{Xx6s9m!BgAigjE<#P{4yclEfP6P
h/ZN<@r!riS^^+xNa#Cm]+g}Y,Kq/mY"4pyK4gXYLH?`]@FFnm8`h7hR!<Ys@3O8g)P0q
8Uz!_%K1qG!OfG=|EbDV*u]R4r
%*wr4!B7WmMgMxF=_EYB&a4e~[ZK)/OX`Qim]
zT4w
(E7BgNb8]B"hh1c,auF3
5-*6
uaGJ*1G}A_K,4
d3<,EU0}YnF}5~Rp,Yn!-oo[mm1
Qv("oyA6=X4U
vQDkX`7v9_QM#rJdHWd5XJtX=L$sF2$[9VF$V8:@aj-Xwk$)mxjF>T.qNM_;8^A2&A}@q(U+][ear0ZG8r7&DHawkS`c@L.#w//kB7~uoTsoDg!;cuo"J*}=P/o!Nouz!Y2y=RdUD6)1&Ru9IZFiXcF=5+,;pN^FRAq2df4*]o7BYp;C8h(8=ua1qe~/xqZc:=!)
mv*/M!#hiR1cjV_^gL%r]Kh7*IUDWBd,_NNl>CG`cq7;R$//w:mxWj^R`WqMg[NkZW>["0qHVuulZtt}T/J#)k=zNanGJ;-Gr9`%SV7+p:FzUp&L+XvwMuA1@?-$e>Khq9@$Gp-8?#bF0-O0Y#@l#*QPh<p2*dmR9w;kthOV,vFkk~A&M;Q~i)q>3+NM3")hbx
[8?u9</nv?Z__3j`j*:5dZEc^(ZllDBqA0q[K5>uqt(vD9mL"`
8*fQ$TIynSIT:[)=u#Q#pJ&76|3wVvB_v8P/:86+qA3w1b(x9yoUyY?
-#I23g7sa;cgU~q<mzhOyTwY#"tnl.IEMwoo9m4]B2lEP9Y!<P.bZR,ri(VORN.AC(+2rjnA`H)Y5W7D8:?Ad.am*seo&#ljCIQxoW:CSN#?Jd<g8bmtB`NBYn,B8sG(CZ;XnJL2h"W+^wl20#l08;L4njnLjgah?]9gQIx*w`;DkPVU*Vn:HZe>Gx=o&/OWRm_I/=H@A@^`,H(*ACo7fp;X*o#A_[/z;]g`w+Cwa)
X+JKuBLm2*Vecvf-h&dO{3)1f+H4&/edOx<j<4u]R
|s(Q_`%X.do/kGq8Z!+R>/Dw_1Cayepj>9$QMxXqs5O%nk%HvUCkfEf
s^TvT]DmzRmm8sL=75
H"]ixF2Ns%_D@2(XeTinXi<
j$YRNHb(ioNw4L7e8FTy:,AcRzY+Yx;E(D/;aHBU9q^>(Ds[(G9i2zZ]ey)!O642g5::L.<1383nrMjq$<U-!@;2ij_i$372Lx01aQ5g<-7NO8.xo$,s?eEK.E<Go`$2fHt|4aeFF7hr^{O?J<qXFT]xnqz(<IU5%tEv?)+zBBPSiA.zI=o[/q]lI5<X!<e|%QA}>a!]gSRcF^Q$`WHv4}tM.vE@(-36rOu4sdR8nkECXMWGW]kIMcJn/p3ngcOQ@OR#YHvSAy>g/;/r@AUCknM1CqReAxX}H>]U<HB12cPX1r%&**5%M`q:ca%!%9XX0Pp#<Hp|tAH~SfDo?wEal2hs0H$=Zwko
2O0o@-ch1sn8:#b?MTTr%E?qmI(kd$O_/,!sI;)I:rJJIn5M*m_>i&leq>n(ms7EvG"Q1rc4FMglREmgJdY5(geTZ"T;LC@Q6[d#AA6/ii~hx2aY2rR3N^r1+aS4LOsm0?rj`b`P%^Z6>dETS:X*KsUb`,j`a%A?]BoKc76g1q-+)!VEfLI-AuT"?+?;.6?#wic5$*m54F74!Oe6g#GkO23/JYajCVV59/lIPn6m2TLT~C{rlJF;X[GSC;WE[%
*gl*eZ"U+KI5JM^{#(`x]@mM>!,Z12KgqR)2wOgGawIqc<iw[VQd5eB@)at_Y|3che4@3aZq94&$Gx0x):jOdOg~Qe8"f(8[QE`GR)w~*YQQt4u-Q>MiPYsg5(X*0{P(RgJ1rdZ=W!^n
":ni&3BwS:Dy-Yb2F0)rU[-<B&+={(d:68j$Lk5(b:Y
eK:By;sj-QrJ!f.nYI"bLwB;N5Lm$bKIyr:F"9in5w!2m*4I-Fh$`@o8tBqlcPCpc29/M`+DN9?]F9G)mh(qA%jmTH~Md2m)DBlCgt0]!:cA95nZdS$S+9^1
u4`>?F$)"kdw=/[5^P#sG6/`E&&LjiZ7Kljf`iY5:R/1@m_ant#u5v8]:#J_C}:]iioU:#n
i"*`i{.tUDm=6AYTuKw#!l&_(+@9WU7qi9ona{-iTJ9e4B-C9$bs1M2Skfi<s1>!V5QN=sA*5,>d^b5;,1%
>3+e5z"jW>KV/s2_14,+s4ydIFyg
cEg%}2w79p$n%nAB><1qEZ`bPkD<:QZlE3"KI]>Ah/M,yunS2qV$4#4#>kXh^[gJ~7;V&y5.xR!9DO6pKgK6,iM;5gF-nSeORgE^[8`,x^*2Y-C6qF"Ta,0aVGHP7Xg^u9C!^ULQ{OF_@@.<C7LiwO7;$L<D~=HXeS1R%])16uiYWlM?a;b)N/MP4#tW<Dz`$0|tQr0gm#{RYuua6M`.7@a1|]FVA`6>QR6&_
@!7f46+abTkS1D_X{/T$we5fQ*7.(1JWPni5xjBO#w2.R%#q=HpAz#{7ilbg>S,$5`fE`,Av&V37+<z*V0kLL#
BX[48`l]8sI-xtX@8hYs
9<S&E@anm/7n<elb*v}>ktB:j?~4,Fg[c:Q_dh]+("?.m26)x+AN`S8hBZRwkCqFVe8UV_?f5m6K|lVeP4:oc`muwbib-y?U[Ag,<m"rwBWR^cW7}w>.[3OZLYvV4%u]I_lvmFw?:1PTp*RH:7=DTR=hnaoWE@cnvjs$<[|@Z.3-uh"8Bx"$Xc=JwLZ>e[rE:8[PYVPF{t*k->i"qtw:>Koo_`cGrKKcuS-tLAtM#ltR/6_Ap[C]:tQ$)7%L/)^gVfu)^/Fry?|XhCE!gL}JF*?WFq/1_*3Z?]xvHI&NBY;bn6yLT)"[6%%aLq=Y?BhZOgp^s:GJA6
pSD&KUF}x2TwJL>*XTn<6LA+@-B9?}%*e:a#9e3Hz(o<Ty:bXjnXA^4y=2o|lF3Mb]Gb@s,
+XBhd5Ol1=Uo=#<FY-?i)!-zqaZ2oOA^,2hTG#C;DM5Ej`.cO[ao]Wbu0j&4R:cUtvsJ5mT#0nZZ)varbNv/*68Qlt2X2g*gZuvv=?]J>0QVl)xh/mszQa$T9U:LE7+v@$ZJ[I-;^yuNhSQ%q6K.O9$K3/<*jMvBg)RP?^Xm4OaW_$ZK#geu)
/ij9u%+et]TRfNQgsgrwS&461*^:ej1+f;@p5!>M,n[k!QL&x*3heKbf`$[GQGfk)V%rhQq)CMUp3L)vU[8Z4<e<t23C1yn(xg/SuwV0p*K5kFv_7rx%7-?:]vap<+&w]l.(9SLx"xnTJd0(BJOu^"9m55K*Sk8:=]sVId(|4G2]nB,fJ=$fu0yv^`]QeGS94s1Y"I]V/U;#`C<^Y3kA;47=8Y&hT!*j4
K}*qoZ/+kDFx)Sn9Egkm#t_V%qC[m,&vL$g`4A7"tZ0`ueg$O~"t%$S_P9W][um2p*2+$1o}kH!OKzMsh+r
,D9U2LbcxZy0sYU3tf1nT;jMI(k0*|nQ9?BE[daV!$E@[i:U0v9)t>vH9&#m0WygkwCg4>n-plAT`WIs#O+asBsflJ?
owkxhhZrSRTNdfLI5%`QxX@vBfer;[Iq!#*P1eADXnpQ(l:6ITU9qBjVw2#5H(p,OIIj"^e(@i!zHg[zJ<KM=wt+F!.,e9thqXwJjm((tl(7QAN9AW
e)!BDLKgB7+h^R%m@O#i}7J/;BnT0.2@4-&@@DLG7gb1,*VFp42lzd{SD5<@!2(cMxJ.R&X9)9U=nCG
Cqbk,[j<`B!=V2MXB0T1.TO4]Em5e3*OD&$pcPl#~Xl,148poA#cL_>3QI7vfD*q,?k=U`%v91culBHLd[5pT<eKMRhTkov^*]mJ
Dlw
.}@:CdF5=(7ak9$,<HMXuQ1hXR^7_ss(8L3U<#ObUGX,ooq`p:,+dI5{BV"2hD8oDm`P@GR1ee
$Xnq_T_=WXOE$(VVlnhaW
`j;Tyd7?a[Q*$
pUE1Kh3y?.62Z?$18_])5@>])I;k((RrXEPk7d,kbKlgYqQKp;JrGlDdGmtwOUaLCW#@I[e>~R~9j]%W2B[4zC/v:UTVG#p1_*gMIR;7J)dIFIppNH/byM.h?Rm.!XBIjs+E9_IMnF<iU5#h1Hlf@A$U.@i8>6G%Goxo$u+-Y1Ufw,]oV7HxtE8P<Xw_=ZgV6`t!iut?a$1J/r/r=:-H}_^i)-vEePbA<q*pZ7UvY:COH+HsnsgSF&|V:;B-:x30YK%]&bgtI%-rwGR=8YNfq4P^GO0Uz7UqL^C?VjKnxlJgX+WFs)b$|F{Sav9fS?UpOi3A/W<7fwsePo8`yR4p`S;2}LZ=c(r.bO6MXu&;y&!2!!f<KFHh16WhVyjMlJ11Or#cFGy#@CZc1tXF-VnW8E/-rly>WkjYij}FP$tOtF`">Mb
}f}dFP
oIe]aHDDby9c7[yoAJ0{`v6EHdTR!KL7LGDGl_cHurPhC4x1Z*<kvB,ryf+4X.w!O3nI]cB:yYsj^dw"^=IXlMv|t9-@M_r(eldA`K<&0BiGkZs3xGwwP.VzZr6K#@Py[dM.>e+Jo7)vE@:IxcQ:BM9ms$4HvT_eRMHI_MHOb<[RUQMFkoo~d(V%_*u+SCO12Q3pg37jMO@aO&t5m@j5u|R,HPdcn@M)ZD2
v]WdbIp<*mB[XVrFL}Y<6Yb[r?kESt`[lyhO?hxZ,GtTeIcdk^k@`hleAI.(M8wUY3y>x{A2qc_T8b^k7<6J_[sRWS%#GtIpv_]V8B,,^u4x^u6/Qcq.
kOMn=&^WDPe
^W)({_OL,RXKF`H7EqDs"!<o-k6*|jWF.6<JO<V^t_ehYuxRmO2LuAE
I7^n?a=4d5(Y!R9N=IGFu:u@`m-k9$aE(I&^IuHZwo9*u
^j?I84,PZ
zgCDKU3Gb,4YL)ZffFIO3na^Du1n+A72YN"L4h8K]>{X@e!kqb~C]`X.#E#8nFG)PIRSwBSec465t@bdXbJ.l9L?mK:]d"ds_.5^SY%,ep&v"&do;5Q64y=/aQkm>coV/Z)mU$SBYv
RLr`Zz=`Gg@)L3]-
?T(_~0h2-oi9o-?k(Z0K
g_]H_EHWtc4EmPc!ShBw7),-bF=fu
LIIosOhw
Jh3rKMsf(i48;_Vt3,Z<bHk_|FM%3n5
C3X4@adM"muvMMg6CyUq0v?[&l>hIsHx!1-siF!&@i6gmI;Rba^ihT6Q|)ERBu(wPGG#cwKES(;g9eN@x(~5.o?>G,E(lwe!i>e>xR6U%
nwjkmh]MR[+LLsTmnMUFmq6[pW1HaS.a6W/y|Jt48sB58L1wBq:
#O~+G9,@:kq7&KJt;PpQ5Lq
8`]6qH@bFSVHC%<1_FQ5SEh?"1#!B8`18(CT-z#?Ao"Sge2ePI!Ql`u*Sfad>;rY`IBW!d^?QL)@B`"43T%UK!-A~b=m0:pWA(Bp%(y:myx"3b++ajpPlwiftt5;lm|M
!ePNT
T`Y@]#X0T;`m99:qwkK)XsmJ3.g<bnbX<GgGn"(9l`X_G1/9A~mn+V%~JDAH<G`SV`7OoR(K/6A0lB-XgmN*kRH@jEVer+NspEH9&ELR<X^PUt*_cOCo1!ZxC%
)T
/z]qA<:}EMlzw
l"?E>D6
sZL4USs0uD4N!?bwN>rRw1xX-9"*Z=ylj|U"Iv;N>DZ}yfR`?U4[GOn|Woq:]QgVA<dZg=wYvOu+h3CC:?t>W8cL78OBlM-[+CJEZb2J;qv|mun3V7jGl32Dsg?^MYIH`@4Z7{*P_hw"1)WD^54Yx#rx<YF"NRu
d/V)WTEQ0%a2_r^PeW_%qWiV`73tit9&gr*:,^o`/4n^6%788g4w1FU%pAbNrho[p0
qS8?87J&ERF[3mow4BS<T-uS4@F!8CKU3?|c[!dv~<mm.-Zq4u3vR.U)WO*K&V*(H`_YYnUT;R{U/$S[<I+mmEn)G4zQWKr)1(UY-mz6e@j>N*vYdsy-1e>.!_k@?E!r%fg+V!<.4cR3lld%x?2&KHn[c<ymMZ:A,x(ha>7oZTsE[tj<I>rWwQ4NDE&>v5+HA_Uh)U-OzTeF5-sO_ogt3GDvI9;(OM]E1bJmUqr]+kjohLhkj54ZDSVp^0BVHk=0X;:.0O~YLkjx=O5J[JZhI2:Qycb,V,LShGLffn}BZglxVCL.<[N-NZXq{PlV)VCvI!XF%RhKw3S,4n|[[bRFj.Sr%reN
mRA*]XXAbkR{xfhmT7#|X7l-ldoz-ba2`7[ax^@GN3]Zp?BQgg7:S,PHHObXFVM:agvTI.k*206EhpIO,t!D4*w}HQFQ5%egi}MZFu[lV}AgNsxN`w]J+dDh42vEMB*r*7nb7.Y}K7]%L>i`<".?
_b#Ik5(]uqjhE0gg~O>SSIQn.E)xNd&5U/QI,Nc*PdgJl$4q+*=cdH!A_C@be@~$BC4rRoCWgl5
U0?PdtRQ%KC6+U9Xzw(!ml@hH:GEGxXy4sMc1D0IB&ajs49sxl6>86;XX..P_`h#O-hRrA/,M!$iT/VcI]ig~8Twt$POnR{sE:!j&hhJt$lOv`~.!V*-hCfAM/KQQP7Q&CS%n%M$~n&C;8YF8AH"w:uLOf|Yt,/$s5,msPC"}/5JN/5yYAm]7&D=;9*
qEXj4V?v]bEZ9Rth5oSdI/v&[Nv${=!=BQPO
&GJu=s3Vhl&,7AL:OQ52&~2o*P2rHKu61OQ{!2udw.2ya.sa:/=TfXyT`tFN-|Ai^y,+$i>o
deU82l
?A%%2hY{c64D!_J!%xh]":0<2~BO?y/~O}g3(>j.k-eso8e#/}*S*7Uw9o%>l$YIZ{3YSoHpDZ/ENi;-7H90K`=LdFm&2s)4$$Oc)_qRtoip^dFES@4Et"+<r^A_ZO9<_o0Tj5V;`"PavgS*4aQyXWXbC8aB6/b=n;<
C_k"L:n;W.]BF!xk^L>BBxcbU.nY;d@3Pi_Tbh$L9bX6ny@-FfM89?XR%6:{IaMn<mt+1(Y@U`(u]SVLP]ZE%Z9B4Y7M8h?z]h8ThQI5MF_o8kp@i"@q<]i^G07%CFq&G:_#*G3,L.pVG-%a(.N*4C2[gV@RIhuU%CcnY:z#W1gFTaQeoV[/6lSQgQbQ1V1cEpSh1--ge>lIQxEf#^>g9-YJ1iHL1Kcs7nb7gspqW/A#Nb_Z2B!2WFTg$Pp{rN$u53^@Si,?1Z(oEx>=e"$9^Lu[h)=a
Kq:0][+gyu"6js8.,u`,$xmZtmXmxVF?:(D?P-)1B9UP6hl$.cDMipJ)!]>P[R|W
YPUDHyOS%=O5Jk^[*TUfSBYT4la&JP3(y&@CY#B@Ie0LFzZJ:Z2c.r/(ax1C2$7NJl5ZlAOoqlyP*&)(wqGo?n28K;a,5:"SwNJQ*.OSt`9j+bl-.~deH,B$UDf{GR*P4EV|*Z)#CTfn8kqA1YJ.HM.hg8F]s7MADFvo0)aMv}*~k<"IMU<LTSV`,n)n!`mfCqd"BS-2,YIc!y?/@5DJUyRF&
k(_db=[r)YXzcT#Ze&^88~d4oJcA&gHSTN,u[Hl_x~iNg1]ZJ}+DHK@%?u+fRsDy!e6u,EmpY5[jSfi#Ne^Rx|%E^0<&3mjI<uZ}Y*<64$0_$uQVoOk.gI&^K9GkfPa{N#_sWzs%-LW*o^=MA+X>?<&Yf`K
@nr9!xV/?QIJ?UkQ#m6
M81HnabwkSrkN3e*3*pss[^FI_O
8N0<(*k7^Ps%Wq[MF#T[(QQKwJ<]OW^:Coo9RTOx=oh>^{tH1gW7Yy"wDjqr_g=cu#$3CydfcW33#@gaHhSOt8h9Tr;It0X
h6tBbr"Gbrh8byLbU3f2-9dw>YwMG-9(8&#Y&5.Vo*1M%,6j[HMtbPU=;1/U%wt?/@Mq*7pX[_;=D_K`MvqKNpmd</$>18N/F)+389H$"XH2NW+*$?qOPfy/(gX}c;09pVi(suC,X7oQF>OCn->9gD`o"x-|cG`E>0ja8u<
OD1FS5nnJaG:s-+MnXump.&pj@IyUB]Vd4e+d:/-5[VFbouTV,)9xs&Zx.0JBAP8+lb*;XA_,$#xj[c2O#lCFd/qbERLc}`@r

v"_
g-F-hrC`f+
3^*hD6fmm290D
51#>%7m$`^4?_09)`BoI>k!CQhn3[~=1S7;PUJ/lOZJ]
~dF`64mLY,R)|8L&S+xdH!bfP3IBxU
F{`MY[(-]A.ad"khK]mr3V9HHz6A
lBi9&rvV~aNw3L63:?a@IDJR~spQw[hG@1PXf+zcUe^$Y8vvulNw.f%7RX|4CfQ:jN3ry-!W?q#eRP:D?2-3(il<|x*>Sl[_L1<w<8qe$OjI=1QJs&m4,uL:t`5]1-O
ri+09NNtaL`en1`U*s78V,4JF5Kb^Uc!u22O32/%2>=OJyc_M)WPh!F`x+`i5/1MF5%pD8=lWAp!K<)Z"7=_RP)=HHCXq")5rgC_YG6YrU?"20buOQkbjmPbg@I>yo)UwWd"sS-<I>x)nQ>4^3zilo+Fplx4vKGTa!cHn;Fqciz"p2oV/Cs=oWqp*QMT?*3hvhqwKxGqbJRDk[8M6@)3Xcr^]e5*q6wu>)a:L-da]vEX6%9D,8+mzDjy;W)Ym&m*gdIxwm.]w92[^yIbb""I%Nb?{"T?X]!k@-jG;_UW
1(o<<S_o?$KtaGx%4KBijr6)2v>Tw[LJ.0wwm6]ta;<FN"^~pB!+I!&*Gfi-+XZ`$Rhfb7Z)a5:qt*H*WFJHocUpz":=wM4jsJM(b5^8!b@*n]k#k$w[B*2~#VZ&PBnI+:hlHTTF;1>~Y3RS`Zlk^kqCGJGpw/!1d63XV3l/_A7=(QLpp1T<&_nuHdW0b}tNAy#D=0/htAgJF&HpcjS(<.j2,9R-j=@/1R>FisxmQe>Kq4L5t!lA!Osx_hl??!bOq:n&(N]Xv8D42k,SAcnhks!+_[ktPpB-H1dt+<s1lG9<VUheQD2ABw3o]PRHyqy)K@vMDgEP@puI/`KRBh+R*eGO[[kyvT0DMBY|ss]Zc}Fgxd7R7X77RST{20B{cLe+Rmidi1h[kDPtE9rvb3S13;O6!*oj(xDFXvY"wkAGb7*Xv50i,V7U9trEWk7TL:M,nSjB!0FB!;67[Lc]1Rl(qmNX!.(C1%!|1mM"R[YoUR7EB8j$NI4arQf
d_TPr5m{HNEU3NPNv<pH-)")y.lmrZ+d2htK9PyU]ZcbcB:a?fP*1CK&8j-^>!<yIxMgiGn{JM3%R:fkV|&*%aCHY%uDX}7}.5v
h,OPKX:H3Hx<sk^V6qUCwW4cmj]+9i:`yHgo/MmhQ7c}!K2HEOY]J;Y~>Vb&$Mq{R%GO>&t#l~geO#K"q*MAS{sWpdT}->3e.5Vjr?CLuWTh(Dsv%pBjyp!+"&<IKXS24Ngvh$xk9PPp<iW"K4)nIdx/HbX:ulV--Aj?k~<eCrw"61kOb#E|YU<Kkj&Ye_pdD?5|hCY=q9me.(ToOvIWAW!}3z+.t-k}))<Ehm
wwGk%ifQ4lN*"V&a_,sOCcc>[4cSej@^"qNo^Tn11X$0WEE!B$ae8oD
yCJ;r3}Of&Bf!*^+;d:=*ejuWL]<68;TY,)4BT40UC&m5;Im(:cq?972*(qVZ?-F1];_lWlhcDKbf;g(IL|
$
bo/CT(N?z@DlbC$frsUVH>nVRNg@X*NuItARFg^@0YT1aQYGtTe&EgrPPII$;S4qUedNe"ZP{hT*
%~:54d4;G!;dFz<0D"m&R(.)<R4]mrH82>(Ef5(Q
;u^F~SZP&9ch,lgpcEi9&5;qy
)oi$&DQZ:Z&eIHB#jFV[S1aFiP7-TZ?q{`njTFwtq7P^Yb>lG7pykrUMrco_*6(VOWYg&AB?<"vRSCY=v-""2U^;,)
sIICe6u23J4e6AMJ!4/qcLyU*Py<YiV}do!nN(wm3f:%TNxSRl,EnG6<lS+sj5N`Vhl|xnT^0bj3I/1(fRRF,y]O1y@]YQ(bsM:_HFm;!uWsRT>@i:^YpT"Z
oiSPXTj7R%9-qtm=/.qZ=A)ZB!nX|H=Obi7AO"kgDBWAt[H5QfY)aL1Gu>~]iy#terT9(Ci;lwMHPTk&)S!_/.wL<LuWg:C(tQq57C(-xcInJ_c[heY>]t-r,C!9tM<l3k(rq3w4?!{xq%1,8T"<8)9<4p`]VSIcJF;(k%tM1A`$_hm(r(UB%CnWJ-8`4DhFHy?@8,539G8
-Ub]oEC.sbHBa2cJ&mBQ*L+El_IY,Y&*,*Q*9SD."3t7}3JJ1"Zl
%X1G.D8vwQ2A5=-"[xB;VW]r(!/tfQi"FU9K:i${ZA-jmmu|c{M4!|4[Xg;[e83S2let$NI
uC,J)R8WNk/3MSZ.7`TJSF@pC[`(JlOYj6jU"}"U%W^0%*/5/Y9!k#a?6|)uIKMF#6@"K$[@/EZ&!Ur{[/o-eTw4w>GHspT]w/VPftj4@I/BxuB&;$fky"!BZEL0E`ip!lYmMf"YkfKQ$T_&"DyM&Bguw1GX&QQ.EeF7i
P@TTw`&[vv>Tft:FlNjP@PV;);3?u4IF<d&)Ob&YbDC9h@d)qQ7{,IFx3=u@Ce2C>|33uV/g3-r^tT?A@8xtPq[+;}TR=W8(#}"[wP<)/~!Xq`EkU}Pr
xQ:)C?"H^UZc/UoC
7C%EV46w_.$H/qT[6$3fAy:[F%YucUk%aA`yxgmGoGe-$=^>K{l">S*#vAvWes5OU^$RdM@]lVUR^HQs^BgQK_,n@F*IUe/4&)Jl<e5K:Z)oAp.dpp*y&|+HT?@E9e8@v4c$>u4]%GFXZX>($
ro`nPF9SmDPa]`OyfZaN]tl97AG#Hj<KrP)jJxpYy]:!kKK*C_j-o@iOF;@1p?0
OLh/GcX5f[1eX*7pl:$_BGF#6
/SY+^oT#xl01pHKAq2$NKu]Q>k@d2%Ka/4aD]-d[r/9PE.uqQ
H(NXV`EoFbycPhI8&~WwL9e47<&W.p;Cepbh.;f]gJRUjZ+u>NX/,;i2-92s^/Kg-EqpfGaDVC97e?:;9:Y`QVOZk.6HjV$N8dM;>9t.i8$<&yO{Sc2&9C=i-4(B##%@I6pT&_EYoZh4_0&a-q8HZ@A_I`j#l3U;/;u~ms#oaoNuiprS
a%axwFg)H$.r"_;e0<;D4e
o9
a$FWa5MZ)X{.MGP[&2AB]juQiJq@xlK!v^K=WfJxTN+Qin6fA):hKiwxBu
^-ot%j><d8GW9spZQIiZdrBZ(V^=H<wraS^NC-937=q
1a^D]KeU=Nlf3wwEYum*a
b2KGV<(:5NOseQ(N%yz#wTUvMQ_+B,bSQS,Ef!5!juN.3XoL2YVAECwwi+W6l1Y~fa4_Va
_.w+"OU!-A9(vro($F^wu``IeaqcSHh-#%I1ju?Uo<-QKW4*{)Ohe@l8}]73"R@gHE6+vdO363t3G#FvbwF.S6"De`F#ae,-3D+cK2HoXg?TB;:lVn&
)R~bzX|)3%+:iTV@CB$tkT[RS<sqPR`m<gyLS=HVQZ9[)!Tp}uo5.%HoBg6t{v[]6u@<!3g*LvG@Tw9W[`V&Q^xsax?9kS/<#&Iqc6zSi-C7dA"rWu-Vs!GENfl9H&XC<NySS="MPk
;h,Q?qR|a$#N11q<B4P6L,j<VMb)c+)l!;@&"Md)e(fk0(AP4~=&:FAt3As?>I0Prxv^Hju:;U0,!|*#Zg_LFTQ03N#7.qY{j&f;UePu(;r*/47t.+6MYnlM!S)kU4/>u[=eE4^bu-ua7>%j>VLh*1kixt=O4j51E~XjDwkHX?t
eJgRkXi?+KB-CDQycTiga}9CTbdSi7&WJ;$rQGcS<-KqX%2FAE+TFnIY$&<_lnZ1^^-k%/n;v:R-:8+hFPYlG1oQ<so2Z=4[9~mH/lCi:o88+|QyQC<.quGh1XbBHN1yZi>ihX:MHqr<!?`>jf(32#,W6bKGp3`D7:4$3F,C-b#$H6QmQ;Y@tb6xOz&oFluwY:W%>v8h:FhS!^Me9u0
/F4mKC?%R|d--uT}
i
aP;C=*[gM$o$)E,47Rc$jU
_$YB)=4KS}/9Cq<2?Vt_AH2gh;cgih/W,uy8[TkNb
t8x?2CKoiVQ}>4>2c3aS4>2OV<I]al)X+g@Zn{?CirVku_0.3I@7n!]<f<n"j9%A<8gho29EE|+o(gR0-;r{[vL
y-hv)6KTinJ#-UIv:H;q2xH*>o7"140#)i@Ta$K>S{o.l(
^a-(-ei4e
7w$)fA1hMpU]DP]KKpx;N?]TK)y3{`&O[95
QTL:8a3^Z7G1gGQ3mUGj7WHpUJLo-_<f!`[F[j8M$ji[b@NCru"qd9NvU"Bp!Q4v}S.CaLc<E3VERIjE)Z2S5vFH5BMI.xvW+8IyI:&s1!Iw"cPLZ
hpFm$R3(K23>MHeV5"WRq8h_IomAm;Ro2epQ6xpU2muRAM*4zeZVp%Osfa
Muy-n.Od;jEyk5muKqmkP}saTT]=F-I(JLkd5iklCS7$#b8wL.ahQ7hi[IY
^Hgd^^K31#8,BL(&7%.n(qf-BaMzq4K&O3GvKEl&&_JHvW/=,n:Z:cVyxW]Us(jLpRQ#NL0ULse_p2b0[iK>sRVJ"=C:8eyepD+MR[pJ!%)N,B6tI(+A?tt>g321M-1M5pZ%9x1TX~:M(oole4PXbymhR,V*896HI"84lH
Sxd0Z>GLR_$4M!(gSx?N1mL#Ld~7*Dc^Zp7RG*T@-nGJBW|1L:Kt
O#nV)=n7H4fw&);IW?J|+(M:h.q8@*aI![Uu?NlPsm%l(sHw6~[AK
k4DL>;=Q0Nc^86?s"+FX[)lnheTa/]6k7QG;`DC!>dLQ$bGT
I<dl_)&>9lGeZ#xAY5FKru*l17e?W9,2inR<U"RvbL,V&"[*D;[jH3e1GyhQ(^}t5R,x!(:Nc3%4)7o0?7lFZCeKgWh0JwvHCgqX(UX8c@o2#4tdk-`^c#AtAmabTkOG$[aR5r?_ET8&WcNdz
i`#mnSWuF@lq0ZY@VhjwwisQ:bQ>6xn=CrUfOgI5KyEo9IgRT*O
,N+j&6>4s"WOBd,%_?0jF_=sT0wWAw=kr4!cqAbo:Z]lY^IV#@8<F"%$.N<MT0[?G%S-ed1E=";d?HB&jv%^uw~<Jf0Z0=I#QxR!1$L99gQk6;c(M7a2h(Rle
=pm4ii"!s!qhkmA9J<a!u`Fcj&JpO7I9OX9`cpYQqJ0vb<d#jFn<k@IfK3Y@rDIE/$K$&lm&8jy#m?0U0nZ`:hk^^]C8T%@yk6$6nHkk-,wCL;VbH@Nh];C5Rup%[4isA+h^ifx%xto*s2GEkL4
j"?0*8;(KMP=c
m3<
YXY&1Ytk,8vcq$H^W^J4?D!rbba7
1Qxo6LDIeID4@O7:OW6(U6i=E,0ws.dU-IB_#]
I*12nQe&&"C;@]Q-uqyHRj-p4j&=`d9q5S=bR6>Guvt8ZpMkig^9g4?qNLD54tPj*+Ix(>u"]ZaIRBVxwW_uq/csv=53zF2^NVa)`VFEQddo5-J1,ashAG8um*(oL?EP_M"S`OzclKFbehY.ULtrEL;%h2n6m*~+YOaD<j?^")HCwd"DolTvj.@sR58h:E(R~4CT";GmAu?wjUJDx6`D}6?V%PbI81FyNU8jw5gDQV/]9Y(Gm79)E1):S%7D}<3jWcwJ.sD7t%8#Sss0PaP(Ih0yIu.A93i5o<)^Hw6crc<i;NHTq?r,&o]lg-gfbH563E&U3/!w6"w#Ay_-;ijoseXN5kk+l#A@m3}MbQv$t7Fnm>Vt^&c#da7E4l#1hoC<b=r2WaCJcn1S=B+2)b44`fNSx#mbrNWgYd}l,Tv!~<I3;o:_,72V(.>N-v:Q1t(ae6|+49<0=nep=mx3&0TwZ.@uQ4@VZx>.tx2o9*yWlw{WpQo&}+rXB;:qB^Y
;:,
%w}h0/JVCWqlVxPd_EhB</{S`EKK;s;UB)~WHL3G[nO-NJS0&D>D$#>f^IMq
)D16i
xPVJP["VyY##s3a9@F1ZbPPmGqM&P<aWBe!WqRV2gOykk6$^:$KNxlZHo2DfON62%~NM7bIH@eb33nLEL<N+8W5Uh=?qIQ?~Ni)n&!TFF1XDNfDMrvG~5P1R25sFZIL]KbaGeDZ4pt].nLSs%C@Knoh*c`8_-f,X`|<Qbc1WQMNIk}e=Sz,ejaj!TNtbv~Z)E%5P))505.,_x*XS-Pon
Hk@&cX^CF%j*;%
ZBi#xc(ipBee>jHwsHyl)?sv^}K>R?et;<r,qv$+<,8[UDWAx*90+eLzaHYmSp7r*RqoLy8W2WEx(hg8jWDnnnk}qbRl@AQFC^2QXGC=1U^Y
VYEK
Kd[=:Ua[YT*r]-k:GWtr18,)hNT~J_gTHMr!8=u~sz:Im{7B,vf_YlU(On)zJ/UPH=LW*YUzOhN@"/qkGllE5}^[sog955TI2G5e+nmp9PnDIF(f*T4!KrQM0f#aEPc,O#P_tb8^6M%t]K/M[5C}4u(lyku?L5QoZ7Y7GdpLjVC{vhC[xQrQFip~xN"q_k1
#c4{>GWQ@MMB5oUM,r`y1oN5NoZ(pAI_:n-;-j>|;yegujj<m~2,TbWC
idp$4RpVjmro#D4;o[F0vEx>`[E($_uX%9k&}uF=*ihW>3#e2%:yB,,-?K[$aG~[~8n&TQcN`2**w7JL/"V)S;Nip.}d*aD_+AKQs/Y9V:$7a0}_rtzO}=>d]h
erJjeDahm^&CH2sO,eV|k4lDufp[+odVq[MXv[puh~:a8~3GU[Am.._"<kfz_H#]p
>M%{hiJ=0|2sZJBNlYNN`oOy]e>*akBFfW
+]<P95&HbUdE^!lS393D}cGuiupUwkz;bnbkzOd>m&$_("iENZ&If^w?eU-aTjF5<]O>jC-.IIu<ihb(brqB4SBu6VC_;0.@1b3Ek=}ge&
0,0.*43>Ky^{nq<84(&Ld[M`2I5bP]>kgbnU;L]!Ouws<:?`=e/p>m8+>;HLp*.Z;E<e$$f!Xquz6r8"W(1Mp5(+!-2$N34<EmSg2!+ir)WmQq?R$n*0fvskCLF9I(%)gbvq<wt~;_<DWyCS&?sP8/%S.E[OAx4ci*>HvQa_+kHs-XlbO.q<u%f_eOjThIiD0fkfcf*Fwpl%,F<EXk5XH8Q|#YWw./W";.hl6@1TO~;m:;E+b|@U?.rqIPCf5ajTFAj>l,<t.aP(:~x}&e8X4n;pyU!cwwOaHWrKcD:X<w@2<QBe,"xe(#T=,iK,8l:CiIwq`kj=WCQuuei=o[8]2p:yxT-Yet<b?jrgHO[6m|<ZbOBY^1LkcY=f0cBSvdBMB!cd5~VHT@jVB$VEBl`3S`Bno$%GDkp
C&CNhAqQ
Pv
b)$eLeM:aeOn:?(P*&w5Qp@hq7@!^~b|oUS4NCkm$ZD[f.v
,4GoOAuZ8*BC#4PTdqX&EkM
[#kBo@xAsW1Mu_Msa+NKH|fQ[X^AU<+5"@3T(gU
=Ps2N89]K]tSkDS7P64r$M]a(RF}<Z$&LB#t=XE|r:OcZ4Pn([!r$$
M88O*Cf)C=@J@8Ox]=sAN8|RQDB-qgrGzg|^|v+N:Jm5<vFS8@=U4j(LKi.4W4w=Q>38I>SQCIA,igu$H
os/LABX14;:nG`W],ei.0
=s0rH3NR?hc&Lb4wEItO7,x*HMp`]wR3sO>t.d+C$,RbWecrQdN#@3o,"]=**2}7vI,(:bSVPAPl]C`tNR*mejhv!oDvyAT!*yX!&ae>"q?4t+7mPXJVP:=0BA/n$HA0GQf,$Jz
JffSwV<^_j8ASEEtW]en)ZRBLA>4CF^uzxf;yU~VtyUx%^1?Z7?#AM(hV!r,biKu[M/B?!kdXMlIb1vZDaL
i-]cA.S0`.DE4NM,aHtv1AhO:kgVqd0N?#rKUr?p{$@G/wk46Q8cKZ^a&94:)[G66ap1si-+[
[.K*E@pP]xZ(pl3.SM.k|BBn#5f0:=yogBvTTO~J`MANxK,tbsBj<IKGK"UI<H!Vm4,dT]}Kz7wE,h8"yT<jqa
I?FlKzq%!jt86BZG-.wK2}GC5fj4QE"[d&CGokE)O8u7b5lZSk(.lZah3U84ay6]U^.x<t/861KetksWJk
[JIk8=z!34$F|qQ%m%)e#a]Roq+&6Or["haWw>mcdSU]d/$1}mx?}Icjob&1TfJhb2hd*"1mdZ0ITg,>;pr2l,IO~Y3;YGRew]O"XKI%=mpqj2:67e:VRAWPH@pB:8OTDaTX,y9>jyK^86lv!-ls_Hs=7L68e76>Yv;G36z;Ne1gaBSQM.]^gW%vbf9E{3+$X?U?}Nnm}V}g+P4Q!9)$ILAg#d=cnn2,?BT&6aDVTG
dr:X2=HU"p:9u61kb->2o=tt.:R#V+v|059rw;@v&?Rx4)^Q]S)mM
2DOAar)heRn*m9*OM>8gR,viN7<~mrU"cKQ.c|)YuyXRD_@_4MKy1NN%&hI"i8Kg27<y<zmk$&og
;QjxKDGKfpGP(a>JYuxgoE-Tg*AdQ<Ni2Mzy`wPiDO0i(a(0+ksrIt?u$+)wFvAw4u(3~GHvem2"?HAH>?#SRM+=hQUtOKfKdRA?t<;iNsB.#6?7EmqItrz@_=SI^#~Uj_WTJV
;GZmj)1Jn~CUkrsQb@/zQf8,][E7?>.2Kx;v+PAPpvo8bq2mwfmjUJW~#QT^ts$[tjP
_*i=kmCOL;#Hc>C#v=ct+~BYyU[K7lh_^F7VBPAfTn(Of0@;t6x]yqY.cS6S)ufzkh=;P_1v]>kpt9l~G?FAVuRfYzhru:5O?UZv@/k(`HUA2|CU8FXHG4,1x43[=tD*EE0rLk:=[4w7xS!$
`FcjS:BCPpZ>BNt1PQcM0bv"g3Dv&)Z9umlKp-z*7e8K<)W_U8F5qT~qj_&&`L4s~;vhsGTZQKOfBI4CAa6=6XU6791JuqNKC*hGf,LqXr:*Tn[@A_D6cKT,6ns!LEto5vZ>mfexO->5rZlhSQ.iV?,HR61y_X
"qLk#fo/)^c(jiHXW;tmH3;^=n=o)PF7>sg.2s+RIP%I,:b%,Qf:v"-2MTDF*w/y0+NI3+/%hAP`?.2N.[]t+2E;xtl03[N+.QAk$)^_G
R@^@cefPT
fAcm/nw>TNPm:
K}tDo{=OsgZ<ygvgfb,vW+n
g}K.NA)R7d&Fc9i(.rrue#tiupdkqfo0ZRwg?"!]<OLrT2rVH3$h6hy1^sO~ec4[@Z?H%@UVeevSn2O@oiL/oxvY-6m0(l4by(!x7{/e*fV4Ba((Be8r,b/~
UF$2EKG?)ye6|6q.VvRSDlr1Q9E)RD~F@SvlOI2Sz4$$^#al(^PYmcg)vJOrfB`*G65R1SdJ{Tl1r-!gG,PPymMU[sh;SZ)7!K8Yub5*]0[PCRN;Ent&fqkS>OMQwf_wjIPw22&oW:g@@bkKA!,3}#BGW>UOzC#+9/baYEj8@25-8Xo1.+<%Z_yZIwo,1D&3Y&+S}@~dM>3$W00iwC8(+OVeQ4=eRTtQ=0frvP~+K4WbQ!
x(o`)*nEf4?Ag"x7=oy:_*opx1pA@MRC=N&4)`o80WhNZeed>hr1P~W8w?w!DO9;-VdKi.!UI
<jN*Pi8Y8ps0"RSUFeE4=CF6Xs!e!/p7>~vbbjfA)F
u:BT//_(&nYVT>3"N2[Q2h9b1GavASc[$"<jhNeU&ByvIQ,1f5?+d%fO~8V=95,yR>cP]z!7dv46mh-@9L[gu6mJf;4b_lqqcjEy%K>qW03XA=+iNa^63lCupYEW4h?^HUq.p"{HQJA3-PMmJ6d(3c~=RD["4lw:#*Urf?@lgJlo8V!=v_O/0:@3s8;YIu-!8?6FIy4NwJGKXdU`
TfW4cg)vK:L:q_]JLWZ8tNBHPf3s$!YL-.^%0Wd/y=]~uW70sWb%x{>@0ZyQNCo&)o`;o#[>n=idWDH/xRrmI^L*y0h_`[4Fa~?xX"Xlmx6$pwn}A@bHV9upBYUE4Pd!H)!9<?r
yR_<<BW
hj;AgUV4I99g>Oc:"D/l<MV/IB.[>-)+oE
;hvVP&Y8tkVqESXfgKXCGe
hKSO-wEYKOja/>*tiV:H@el
#<:s)gZYf#9gH*rCa.&fWH5/IX4gecc?13X0.XV[m)5Up1E^Er9d6vLJEvu><:uy/}LJ6(d@HSkgtnv/?_n{ZCijCee6FDnXJ3%t9x*z^P2BpaN#Y4cx4Bao"iNi6ygt/?Iq;;`:wC)OYRS@:bwCjm87XgvC6wVVqRLMltx@gS]l4F.$,%2g,CaTblq4NOJ:d.s)^v#Q`K,K.XpzJh%vm$yG%B&~%6hz&!WjmT4Xj^`sRGFMOVncCt1nNnWE`*8{Tm"teVVG4w:u74l}%;R.J3$:/5L^@xU4nA(CT>"Y$.<%?g/At3ud`oMn*:KGw11`p5)P
8?h/Bb[c/,yli*=<3&tegiA[ebp*Re|hv(IK",$?P.4:6B1E{H6Z06!-.R$a>M</]3l/=h,f<4}AS9nhEAAtPS~V9v4(b,.2b<h&q+z7g%<a7tJ!(DgRQ%OaL1@p8,2h
@:Ww@2[p(4V[L64w0joE5Gr)ZTQSsUrWR`
cDpE|x*l/@+c0f)cM`B_P4qDm.ty*rgrYawCQ/?%uf";=T-ne7eatNRye
Imm4:_OiYte*4I"/4a$G1L_pP5"1?M2=h:ZsO;f[%#@ej6mEI/JDH;"NpVH&a?f?ZVTs6d-luo*hw*TxhR^Ck%@0mNEX%0WU#I@vn9/(J%Nlb!fDaT0>*iD:@3Bf;k?quIonV;>$Vacof4E1)VKF#cp8_fdNvVRtyZ_@2kInhbQ^4K8kKH?/9Mr5
dSV(p9(,mFo3(UY|2$r4Qsxl4$^;6mQz/ka45.c:Xeq"Bg<b843OSkc4sVgee.mq5WNb:=@HydxQaIm5?1R;vC[0[He;I|&m,Ky!-vZ-"exS*{r$qx,J:0!Fp;Z0/>rNlD1K`wN.v!TMv^Y;0-/&,XrP*i@XJ$)UbGB+*cSN!03^x-`(Q
o3kM;CUEg)UN(Ds]kB
TV52#4rn`&S4Tr0/#tF?/R*vzVeLmpg7;_wc,L/z)KdM^XrlGp[,oUrb8mfjpLGq!c}BGi3v;k`J8o[2wm6
kTr/c/IVPI(X0l<07((Ra1jkx6B#4Z,&&l<ADNB7SGzFA-RlOIdZ[c%hK.M6e@@w
U,Yvkj0c.pf~M7gFs?+)0v)uK,nA]KEWy)c[^cF9Aq+7PhpoMh8:FZ(k$=rhAXKx9Eg+mw+
Q3I!2>$XGS0hDnur;`.M,r4it.d90$Du3[ucdY>D
n:;KiT&2?kM!~uq%
Wb
cVq[lXOlC&#^"9bPW`S!keyHyrCu2n$PA((,nsoG=2}jSLE80OVFRl)`=P?*(jv+LL<%wE72#)9!GH0OpDaB`+d(5Wzsn4l%BW5W>F>7n@}:xo$[E6(>M1bF%kN`:)_[yKba(RA//H@98ap6|]H0VMLDq@^8x7k*h@:=_E=$6]vT+$082t6Sh2^Y!)P`(npm}Vv2#0diZI5^es?KoU
I^H33Pvqoa;9k!]XdD=_vuemj|x
4-fz<
nQx$cWV
Ut`JbuffSDX%
K7DAA3l@=A9(l3.XQ7|(loS>)Z%>MNmPV]$.Ab=6zGw.QG`&"I~Pb>sZyP+#qJIl^]/3.(^+{y42or/&Vd(Rq3g*z9@nRdH%I5$WBB2mjRQgvx@mip;XWQQ.P*|JeOAFkXrrz-^=p6{(iOY=SvzH5
IscnLn0+^Vw9oy0ode>DxvKZ."Pqk]B2<0Wqg5bc)8|D#O--.w0E[)~EK=A(Eq|4EyKER;]e:AO]KI:N.Io1C4~o_+SW@bzfBCMCXcc=Sg9yEp6v|-7U3ErsIJJo_0DJ]sK4Sp?W}4o[$,p?(@LR
b$]Hinok@4)4K!4(YF<*Je%VnWPVTY(S!syny=mREpV>Scnp12R9h|Z|O|(2NbcOeYj!ZrSq#S<Iv`5H$:ruxn!iM1?X$%EqdAtgG83EMs?u3iAHNApNbI(82_!.M~*
&Jdki$d36^q.Fx9Vs,[5F?k=afX-9Pqcq`@IqUtMA^qQS9onQ;Y#,`:)8OY:TQ+ysO!AnVR38Yx"ohZ8jGDgA/vh9G,!4LiuTU*<#e[=<>NeH!qGp_>.omv~p^CCpJwkB}dP=S$/MJ#UGi9SI/7A*h3%uF/a9g3MN_P1KDshsy2N).qOq.H(5`nrqo`{gSq2$HZCq0u,5`x
[l+}%lWBAqQ#d(1g[2oX3gX^L,q-rkI^]ko&>{;z3y$s^<CW3CL&A::Bu]s5yGW)OV+RWkW3KA/kM2_-mO:51}Jr82o=+V;"qe`K?5!t@71
MVok%v2&_w(md^Fq(q>n:^vDa?Y_]6
T3K=k+s_$s%CiVb>@^sdjo)he0Er?q1<a]+qwdEV*%2%#&#&5OJ(
_s4J%@BUjT7ipeFG7H<dwuPk<:sw^|FiaOVIK,lZU,exS[y<!<NJb2>6
M7H1b8>QU7H]m4TcZ21_u3|p^7BykBxT*Tw]"3>GxZmZ/`)jly`Q9p|YGB68$%]aW,:Erd)ueR}fcp/_:yENzD!4yYa#1#z75W"tt>j3[kVoI1.Vf:]E{/-T@$vdMr.v%(GJ]<28_,1C>TYrSc"]:bdx?O<#;G<ez({GUX&179l*KL~R1v5:o/9`p2s!9w(D|@Yv9t:#D`{EbO?n=bW[g^51?OH?E$2OH.K;+t~v^-TS?hbi3N6Ge+@3q]h]{y}j_7No^EmMV3=%Db^rM"<f>r1j4b#3.7u:q+`MJ@qRM,-$Pu%U*Ezqxq0p6ugvzR@-jx3Y#&mGgVGnB`bY5JJnmle6+,vIFEqJ9wA;+So-GK/m>JZE,Qhe6bZA<kxJBHS:U"MrJ;5%;o;0KP(1cx-K*_pp
?ko,Y.cyTE2*,d/+#p?Wuz%w"/"gIY,%BrLHe2hT]!m@=
KC7!Li/E.IPu,fAzoj<@6vB|6}6q&w2S`)*W[}*YGi"@;P&>3!<j*,0%[{8-)B.F3zfJCij}h:3xsKTnK6:3h0L.abs
Orv4
|Y#qdJBy3m*G
B0eA+i]$2qb>d~Rp:FFNCy]q%I"LPi7Q2
0bXwP<VyRSd`so%ceA"x1ie3*sM!4*IP:x^z,lO/lJ!sP0Bd^"G)Lj!D($q"8d/x[jf5>jMP,OuG&Oh[o*T64b8Is"vcY/3$,UR=D1Xe)$9E3>q88}#`
1#@fRo]6Q[5+u2Vaj7mta;(.%k)=K(.1W%`wjxLruMvR8*r<ji_!7_YKWS=Rq[`2j2O19-j_3n
hNWggNGD-i6x"nwT0p#M7~#y:O$1mcd"8TttwC%NNMkS<jJ?@]O%E^oDVEl!M~KO&^vR83iN=XaV;0R15jsx<W4^DPidNT5j"aIoJq^`erAc%QY+0iA#@&!di!g`x&&^q%>UQV^58>2Tn}TXg?*%(nN&2p$!mnuF1{B|JX*FLOfBL}icDa+S..p5vflb#L^rwJ6(:!PCJp!NH:mOgQ-=c7Mb.sr3j8bkWA$
xYZ<=nhHnP7kQ"]4(:MvrE>4&qDf:tegQP>Y+=+Sd]ehR-,^hHpk&9j+WI5z01%IM`@E0,k(li
xA~?+i-Kxh7P)?:1A`5ov`{0Y#3*e?]X!<}%i^>#7jH#WNj
2Z`>0qKI$t0^(b1_lDxRQkwPBXjlB"ECx
(tCmgjjtH<o
zs2bLOzPxN8%Sud$8_D8Xmn^+e]UXvLyyWlt7z)Gz!PgZ:(Qs;lUS<Qq3O}_"Z4*Jrk?!XK[Otv32.>szR}<qJdJhBPbJ&:-SaXRDsz)OOEB)A#D>%I,11.^baQVlgiTVV<>C^]LHQZeIs6GftAody0R`..Ato&(GWLN=3?FS@sqYU<4(F+XGrfBsi;s5p|5NL>YH"9COWea>[L-$"
-PEqR,k$c.A*^3#e/
<xuC2*Pg=6fG46Z$C!
q
|`L,5TPA?VLLPk6/sY@oir2%7Rk$c.}i}gBS@2M9<[-&=wvSZyuufyN,6nCq<%A2T1MTFl"QVZ29>/*Z`^N;Q+nth!L-,;rRvO~bzEH;DsyJu9]fe!2,)+IH/F+)Wwf2.*R,|%+L-j
EIs1lGqn
GUwg[A,a5mS+=,C3&wWY*.4XQ+u`jMIL#1K(,@}%W/9]|6#Z)^k]t(~FVrM?;,rBLe>,Yr)9X+2YnpYc0YwPBcI&.fe%-oF%"l!aq4F,jbCx-X&*%VEyx/avysPL%fG5e"}U[NmD02Mk_r:53T1ObtTkgG9N@-<unYVn4Spg_Aw>wrr!;!H%=Bd:X_[,.Clh]QhI,q&D!E.43Ss)^bF&{6G09qUu8^E%)d-h+AT40wROWh,4zZzqqg4t#Uxp~IN$RvJ4%hra{4DIkfEGs+F(IL4V`+H*GlK3S<T!m`ScDn565c"8hg"&
j+Ut;a:
aWC
Uz):61_zF?k~CPK%Z{E(i|Sx//,H__htScHt&,q0W+d:9yT]2KfXcA`8TRCR?O:?RuGOI)G=c]g
Z_-k-5]_[p0Ypbaf4bCa:~/%b54F6?DAEY+#waM9"/x
qT.e1nXULaag:zV9-zi!eB(
a_xRd3sF:XmtUD;DK:3.4k;Yqvi|:rB2xUifZ-FLiq@2UYpbAwU4vrU;B+>RY!7.G{YRE:kC/~Q;QcE{"eF
f)
l=}<_m:cw-qccd0)"Wv%}!D3xkD%nC{UffSK!Z0^SgibF4}dN<?Dx?v_%2K^pPS*1y:3L6dDWk.ai)2vs:!)OUR0Q34^M=,A*TYa
RQV^a<hLH=0h!7x1UwSX8*6D_.F7"f&^y>&nO6`&5|b_kZtOCm*j//EpU6C"J%8asIP,&+?4Wa+ug5[=na6ukBmwWZuaOP<!J(3I=+6vw*t>cbM3vH/V@x7kg~c%gRKyE+e~sp&oOu$CfCV2GxT~Z,7zlqY(_h4pY=o[a15%BN@>711[VFyXwHH2:#B&jUL8da<T_E1~Os8KH2C]FbsX!]y_3vtC:A0Dy"m)5vdHtJR?U2UW${g<[f+Si.s9AsD6A>jO6O();F29AYcRO1FboS<7&V4&@(YkFJmhQE0z(u3?7"N1Jv-p=z2}wb;]mg%>V".4&rkn["lO+h+M>,pn&D1Tj-lN)Nt9IIYL
+r8hEh>#!NSd-aN*C$>pe7=#?wPMHXSZeca$8tJBgA[3`[!kB_:VkI{Areq7+^[T0R"#|tz#yfqZ
R@AZ03c+kRN+,?Y~9t^r@=pGjb?4X]Av"/"tjXNP*;wVY*xL)p"(xq)!ODa?NXK+#LgPcI$x+d]e36SAoW?Ai(w=4143S43{lfnf?xU$;8t|Gr$Z"Qj;wt#&i_XZu0mV]A$GX9Q]Z#5@Q#Yi1oN/yNDDe/;qQRl?V5dp<L@$49ru#+a(/ny]=Gqs29FC*C8uipvu9=H$.?:mnz(RdD4Xj"KB,
!~cx`ek6d
*-,3lOGQ8JuJ$|sNWtb]I:ha%A0>fqC{dkd4k0x(pmVu?y#+4W?W/*j*FQD6
p*K)S#N[`=5Shjly=GltmC~2&kG]u#=<M"eT8KX$]um:v-`aY+tX.w#h4?3K%f#-j^=.&6F%a:<*K]]aD[%h8XZet-jTDa#^%?_[Oe4ie_@k#y(k#(M$.TJ3$403T1fE]wkf5Qt
LL;$7E1p<oX<QbxH"$h#4Ov9!SlM-i6nU[&X2v5OW0sbE6Ulg0HXnO&dKBk2V7(Sy:<c2-VL;]`n`%!ItJz5#SG+1,Xr:UJ<E2A$jAMRxf8QBA8M%6tc2WaMia?q+EYve@snm<02i@B3(g}f*)ZMND}x~Tyx<PKxIp,=kfF3-6^EB62!zN841ey_V=Bnov6+>iCd<>7TpJ@fa
nv|1oHrvyC4C
r@<ns;a+_yvogRdc:))eO+hTC
F`.2?Bt/UWKp5K+LWjZtaJ`!RnJPij42G~634Tvo1Y!2%:AltY]=6)#PbIWfVb7X_8>{O*)A(0VK;zT_*P(;hV/c
3i^#"UXiWo!Im89]hl/gnq:,,2<nrlA4DG{s>G&),;{O)]W$MO//^cm]1e:w$Y=yX0"S@MlJ];EQci5=x02+4q:CN2cLG*$w+7id"9f=pRQlVmi5l2kiJq&I*(]yM_(D~lLLFA$c:#RKhOulj$>
FtGso:,u1Wbwxcj&e+Xn%$}5&S2P`*A^U;I=R7
,ceJe.xaVMRrP{[IR-hpnm$[:cohVQTv^YL]l-
m[XU`?TG)t)n2EY;cfNDuwM)B]Wy#][i]eM-VLOiU^&,W+l%9uxx+k]YK)zGp$QGzhNoxmyT`]wq4npy0$.jc4w+<h9QD:Kx}NZH}lL2q3{-?)6ism*/6Qro5_rV/i"xfmUA7>imTSA@]_2w/%SIl/FP(P_fn3nqV]sU5U^r6LZWyv~X}i?eYxSK(h
v5U`:W[t^TC=nh;-v[ivdM"5<HK[=61+Aa9;H,FtSXamHem^YtR,bjKGa)`1i(ymmap%n$-u@R)^DK,FH/1wjdN-n#x(hCe3^`=&4I:3kV_zmY.Hjb4zt(,Qe%mij0mj/Uwl0!#-*3;+QqERVR">I{m24=WYf"S#F/B:VGTv)$o>S`eE&i<dSY/Bxm?IaT!tFJ5tatw$*T,ad6$?aLllvNaKX^6lN^7/V[P.go>:?>p}mVjP_Mt-"Ma_Mqx7]+;b;G5w;*l,
%1&Zrn2u1f-QU@k6lobg{qYc2?^wZEq09fOGYY3=+2;"WA=Sri;*_rChIi}c3I`;^L(t2G<>LmusFP?]
D5aJjb2#UX3hm[F6Bq@Yc*KAv>BpVhV<1aX&>RpCy/WcIP6^Dva01WqqU<hX0s?|i_>7Mi0k(b5MDl4JUsL*@Ov9+hvz*G%fZ:lJ<+=S]>m(,{kyc;H^^i*iFK2=@s
Lu%UxL6Y)ha:Dq&^@`&>M]MNZ0AmW
fFD+Q0f8Bcp>*2;k*w(?X-z]<UGEZm%p:=I#H2(ZbmK?MoTYdA+V(`9%5f$g7sd>z7c_VA1LKgzF[b/
~j]Z$B(#
r),6%wa:
Cb5k5fbbI4V;X/pf.r>
IZp43IZ9GHGr=-"C^icSK`(h=h*VC]oFh]]I}[f;b$5_TIyADt_Dn5?Tq4YGW1,IIwCF:A.rklf7m1*0b9B6CKwq19|&*J<.p:Qo^psMWYHMU59+<]z,Z;btW!;2;aXead&+aZ1%nji)~h)WrM/i7>]de@@7T:X0|<cweS%bdc.uw<MN[7{[(Ky$=&&ufG`wWQ!6-"3HfR+:q_s+St]sn%wrp4C2v1UCs66rp/!n+Xy3#]6h}d((5K
k;b(YNe<NVKRXJI;?=*1<CxqTo`dajaaddTI6v#GNH6`?RhTP53$VOFa60l[L-cN+eKgDH5Y1V!ndjC"8yKDu_8[
Rq:Od]<UYfeZKa"Abp5y778*YYR1"tl].HNT7dh(3a(Ct)5*yjFw}vwnTN2X
P$hRLjxBax_A%iW^AX8BW&pWmJoop_P]-QFbVd(*57okgAnQ8DJ;Idk$)fTkviJXSBnj;oU@f0.
Bd!8,tf}"7v?U-5Pvlts@toWk[.sL?r)L`%];krk"eEQ(Do`;c=%,r:@I]M(t!gWwx/HGqi>TV8.JRUgT^kl(Ojh^NZv`j9(FHd
fh->*0ZoA`OCMjuDi9w~!%oa?{JLTo=j;"LZ
O^wmt*2YcPd9a]{A+(gnm-T89S0`eq)UqtoVX<]gvuH-OBD#^yK:KHcW%u>T:,o$[2je+,#uc>A2W3&>f]rZJnr;C@[Lh,79vxg
K<&ERFt03^c<1mb3#La8"ylOSHXTk7_0#s1pl4TpPx~R6e$N1@,WwEd8kRMdG!tXSQ}dV-Fb8=crWh;2|t?,rbYUi0K1nxzXhlDfR8s1FE>g}YrPVS?fGs{D/"i5YZQg}?iav<*C0p.rB4|/%,!h09r&Jz&dVsL=_X>CN/rYs`41z=Y]5RrMi!?eK2hjIo?ha`9isEs%e[pkevf(^^m!ve">OpmoxOiGHwBXb1+79c_HB4v;=,ZQFg]AZAMkQR|EPQ3Bo742FexIc,.=o^uwPwSZ!#Fb{r3HF^zC{L86o-Smh?#cMb~1R,~q6ixvc??Mnce;w<^#8LbH]h"(U@)6f;3>[(Cx<D6/(Kaq4b0uzNl6uuL2F$Ef1/~$)mrdfsGi~Hx`QY)%N/xkrE1+}O8UbCh&0(u$67v2qY#x1lSk^vS->k^dkp|r|q[+?xe["3uEOv(Kr7]OQu29O,EU&[.H*YjPnvG0%UrS@f~C:OG<e_B13e34<V_uQ_-8gGS9xQl&He(H^Bb(`<r3~eaCHd-QzWt_xQ/uD<%^U`}fXfJ.0LM[;rC3m(n1:R9Yi+SwH`zKU<@N[se<@MeELusf`nYWfxnwo@nX!ypPg#py8df*T%xe1f(#QqO7J/G:1j
a9cW]f_Hdzn=b*A-CYaOo3b^XmTwTg/l6BfgR0;glbM!_%*-ZL2Y8WMfFD;N@aBF$Pdeak=neHOtI0!?8;B_!}=x.dE|tEVU20Uf<evD2qef"Tyioi*uBq?!X6JiolB!.VH%OpHJxagdm|<OuxLW]*&^R3W`]KDqwZ3,2nbuS|"4CB2qv}BxNZhdllCc39p-d1tNkD/QMa#R1`&M
)ZbBynde?bqY4RTi$p-XIV)+5cV7;nMy;+#/

)O2vZ.gHk@!uUL
!S
F%!+f
m1N7ec[AM?.IlCI12iV&|=%iOS~c=7>4FGc!hR730Mpe
:S5OLotG-::J0BT24d?B*aG6Kp3R#9,Xy^C(Ug0<G{mC0U6lbS]7?]A,z(<SA;n3tN`;yno)');}elseif($_GET["file"]=="worker.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*M-:_crV?&ivhwW00>hyk#FBR?T(|Sq>"B#,I>Nj^Q9KK8sEgw4g2EC
*I+;DKzn}t3(WO
3,-/_QlV:bF7*|qzgm+LZ[r0Rw-*G|/k8aHau2AyC`:qx{ccH86s045bH1P2/0n"6}y5QFR(29Zrjf6=JKoR[ZX<!#*8i<5q.ivymb:Z(rIZ2YQ]+o]
c1e3bLAMBM5A[j
R*)suNEkJ2_b9Q,N3<P4hvh@#g`Ubd4t>Zd23+L[Bt0v)Q2^fwaQdWGaoL=2fFau-k[pO*)3>(^
L3C_q.O7n@ic/h_PH^?3P(D2iqS^rMAuO_swO`)*TiGN,)~(n+#u:.`d2HKi,UCsH@.]A%*j08LgKJ|@kX+bbB8Zu(St;xKfJ=}=9(nou8]":5u&EO~jfR@9f.[9)!m!m>c&!6F6vOL1`]MawSXj)67Xp@ygy7)9*q,X#[h/L6mfb@W@"#ngdi7B#e$3RlPyr[q*H-nfIGG."G="QLE1bbI!fYOm7erh[>m4s7/_nwA');}elseif($_GET["file"]=="logo.svg"){header("Content-Type: image/svg+xml");echo
decompress_string('%s_VkbOV?&!t"do^rQ`p`ZU/yp&Ye3upHt|/H&y4sA3gG1#^TM/psE"F.!L"b-TOg,=_&!?SQvUpK1NG?UVTm6[[a>X*ZfBqY!LKF
fO{QWHay6P%Mxk-@i/qV|wo57>CjpjQuWGGgYH{O@sDx@a=t3J8^4xXkLUkz!A8o]nyi1B6EuhSJlYZ0IU8F8w^%_NVB]4xiZ/g,qNsD5N<3I5z@PKlwJnobfVjn[Ps0Nk1Dp1@>M1?3j7#!a_W13^gLnlX:$#{3
8i+/0=Y3h6/1,{i{28SyT]@Eu=r"Cz(I8et3V~F)%#.@C6^yX&2~ff.YQQ(5WnhDY<DSV20%H2f?Um0n)kC6+)X&<0DhT=GdXfG>W}N
_itFLhYXgQ-?9$q+dW7/s)Vvp*s9<u=9Wu"5]B@h-)l%Z0$vcYCQ:>M#CF$ONU$8f3.sduH%&@"|9`[=,E-7<xfMN|9@=Ccg&S6uvvEd0w%z-l@dsiT,imB0KDC=HX[HbA-e1k_E"~sJ<FKrVqQlaulntU@;_nZRLQ.qyk*ch&y@KSbULF^1JuDW`W+bWA."U,D&Z89.[5Y.EDYJ$A]=t5LNi>n}`Oc
*0;=MJ_8W,XQa$w^=ohbWF!H30]5ctm(Bky7@-Lh7OogMB"*');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");define('Adminer\SESSION_NAME',session_name());if(isset($_GET["upload"])){$Kj=null;if(!defined("SID")&&$_COOKIE[SESSION_NAME]!=""){session_start();$Kj=$_SESSION[ini_get("session.upload_progress.prefix").$_GET["upload"]];}header("Content-Type: application/json; charset=utf-8");echo
json_encode(isset($Kj["bytes_processed"])?array($Kj["bytes_processed"],$Kj["content_length"]):array());exit;}if(function_exists('session_status')?session_status()==PHP_SESSION_NONE:!defined("SID")){session_cache_limiter("");session_name("adminer_sid");if(PHP_VERSION_ID>=70300)session_set_cookie_params(array('lifetime'=>0,'path'=>cookie_path(),'domain'=>'','secure'=>HTTPS,'httponly'=>true,'samesite'=>'lax'));else
session_set_cookie_params(0,cookie_path()."; SameSite=lax","",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Zd);$_POST=remove_slashes($_POST,$Zd);$_COOKIE=remove_slashes($_COOKIE,$Zd);}if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",PHP_VERSION_ID>=70100?-1:16);function
lang($t,$Th=null){$Fa=func_get_args();$Fa[0]=Lang::$translations[$t]?:$t;return
call_user_func_array('Adminer\lang_format',$Fa);}function
lang_format($Em,$Th=null){if(is_array($Em)){$E=($Th==1?0:(LANG=='cs'||LANG=='sk'?($Th&&$Th<5?1:2):(LANG=='fr'?(!$Th?0:1):(LANG=='pl'?($Th%10>1&&$Th%10<5&&$Th/10%10!=1?1:2):(LANG=='sl'?($Th%100==1?0:($Th%100==2?1:($Th%100==3||$Th%100==4?2:3))):(LANG=='lt'?($Th%10==1&&$Th%100!=11?0:($Th%10>1&&$Th/10%10!=1?1:2)):(LANG=='lv'?($Th%10==1&&$Th%100!=11?0:($Th?1:2)):(LANG=='ro'?(!$Th||($Th%100>0&&$Th%100<20)?1:2):(in_array(LANG,array('bs','hr','ru','sr','uk'))?($Th%10==1&&$Th%100!=11?0:($Th%10>1&&$Th%10<5&&$Th/10%10!=1?1:2)):1)))))))));$Em=$Em[$E];}$Em=str_replace("'",'’',$Em);$Fa=func_get_args();array_shift($Fa);$me=str_replace("%d","%s",$Em);if($me!=$Em)$Fa[0]=format_number($Th);return
vsprintf($me,$Fa);}function
langs(){return
array('en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','uz'=>'Oʻzbekcha','pl'=>'Polski','pt'=>'Português','pt-br'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-tw'=>'繁體中文','ko'=>'한국어',);}function
switch_lang(){echo"<form action='' method='post'>\n<div id='lang'>","<label>".lang(24).": ".html_select("lang",langs(),LANG,on('change','formSubmit'))."</label>"," <input type='submit' value='".lang(25)."' class='hidden'>\n",input_token(),"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];redirect(remove_from_uri());}$ba="en";if(idx(langs(),$_COOKIE["adminer_lang"])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(idx(langs(),$_SESSION["lang"]))$ba=$_SESSION["lang"];else{$ja=array();preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$Kg,PREG_SET_ORDER);foreach($Kg
as$_)$ja[$_[1]]=(isset($_[3])?$_[3]:1);arsort($ja);foreach($ja
as$w=>$Lj){if(idx(langs(),$w)){$ba=$w;break;}$w=preg_replace('~-.*~','',$w);if(!isset($ja[$w])&&idx(langs(),$w)){$ba=$w;break;}}}define('Adminer\LANG',$ba);class
Lang{static$translations;}function
get_compressed($lg){switch($lg){case"en":return'&X/+K5IAP*41^o=NV84<ar`WbJi99)^Gv(KW;azZ+/m8!qRJhJ&w,.aDesho}tI2qU`wgZ[M60;N6N(l{DZ5O
;FhWhyA1[WBl-:!wiE`B(G%X*E}Tfe`?GsojWg;`/^7Zylzk46!+p!!QlLEDv@Xy:,a6Elzp{[OZ45hFs^
B1]HOsTYLU`i$fj{<Fw~<NZB/x(b.shVW:si``$;0Oq:fcJgA6Bs)Tz%yAtWp=iVtQW8t{qVO6hMKk,"l/GUtKX~A]2K[r`T^$w+G6JB6q0aTYB([!3JC}L6$;@Kg^iwZk"Ldi[Zs7jGyHvC.@j@%%m:7)6rFa@R5E)31;/J]78qhJI>x,DiI
EtUX4k={1xXn=}MCO2v,Ix/MMr6<yim=tCY?_N`Wt|rDr{vPjvv=+Z_?H%
c6Jh(+f=H?Q/mS)u0f8m?E9r2P5]K@e%es%J7t*9{dW7c#5rj2Glark(i8=@?O9?VKnF{ehwU*IA<:IOf`mN`Cadl&?+b6vN%ASDhsVj}&nD8fQG.DyV:Ebi75K#(k/B
mH-.+j4X7_##K@NUkda0seU]Qse~w5:<x00bR1ci&mwQkv3$c>PDt@FQS|(5wbsa1_f=)e-zAh#bRvC4
_2CDbgU)5,rKbk3`EIJXqg2j[=M6DrhY]O;)p/H_^Co7&fO;6V=7ddk#BAN!qsJlaOt7SA]?sVWUVr#]iC&9yf0A.-6I;32tg&7Mp?xAar0!]xngXErh]jUMU<9Pq,6OOh+M6IGlLpauB-Rr4y-ab@y?Qe|89]Bng^g"P6ZN[Af$byqU8B.P4_Beg^84nK]eM0ye!^I]+]@--P$bd]x_&4UTcR?-m*5Ar=[h3@"`>ZLQ37.`G!j;;@]L3?SW&^JaOJ&LW
"oDO^8jmM.-TI=Mkht8G.BO;!.[rYX$x^36JNyJ%4?7!qN7J[sBy|A#+&R%9fqcox[zORW]z"<zD)b[y1lrRE!Pl~uqB#vfl-AzL|:<NftTEfWKw@<"w%Nj`=*$m7E{izT""3/#@</0Hfx[g)HzP~
j%tUzwU9opn0%@|?<,V;:UQ##Qc%;Z`?{5`YiFN9dimn6X77ch@*N=S/kjJ<#ER!Yn]d.4X87sb=g^h3BF[:Z^a]<P"
bW~sFXBp(Fsj4?k/g=3"
X0)>ZMbgOR1&93f-vl2MNY^2WZjV;De]xD`Y:
@CZx?>9TFRgJR]@<y1q>-BGGc"0X$sCy:4pNqvQUKZ
[5$F&OGZWE2Iop%Jr3uRIVU(-J`*R<qH@hsaQ%jaM7?e<jFOn;F,FPp67<`u<JZ&O$s(?aeT*l8
HDn_CV!kXxa@lm&rWx#7[s[r_[9b@a#mVd{?Rb[h2yKXpe@hm[O$;Z+qam;3W8%(@=lfrUbK/Wz&odZ^5uIdjl3l9!d=g$=R;(h6r.C0*SkJ%u2FB-OxQSSJ(-ATO60@Y0`OMoZ:*.F$Hft+Smn-/Y{Qg40ODWv6>>ma!4ZE,D8?m]^Sl6j/)DISoBn&^%T
F3+KfMicOxWo^.c@&g$Mk6=>u]5!ca!n33?a$ea;GXn<=ll
!
$y^.eF81h8uD@?Hq@v|p$fIF@5xuS"T^:?vt0B_xF+!]3l;W!kANz)J?up%AEWHj}5n^KQNUnYza]1mmluY2}_HC%4-(J&T,n
UV&lICjNgk-emW5xeT^8]@X3(9cI{R5]<S]kj7%PD4+S)
?/Vom,H[p62Y7G>e-C=A_YL:@:7nY6#t%EUlX79ykf^y624PKmXM1H2&]lD<FPw$OuK?w,@b}l_Y8[9Ctk^Z5:]
SMxhkKeq[C,vOa`+hAl8[cS4m`1RDCd5AV)@|dg5DD,.,5_,xZUVS2
]wlKWujS!aeP.$2}>Q7<JQ3naG$m2r4A8w.)$P;j6[;>k6]_(@BCe<"tiWI];G&Xo>dxYh;5ru_[pG:KOc@GnR$4`qlKE|1(9fq20^!T.7#ofz$Hk*&LH]hkT"Rnkvi3p,G9F_)bwT
v1ZQdF]Wnt<[O3W"E@8VW&aA;.X:5js)!EaZKMESzx[QBJB>ggsE1mqt]5dmF;aoE$ml.vR42I_UA.w!ZpK!%
rb_>1!yTED/YGZx(v9vjx#yf~:
AW9"jkRU3|:UT&r=w2%:Chv~LN7Rb[B>Z88[
P-IElSgm7
saz6@:e8_pAnP5YnUtTWRxLVqycp8x|%wQ=(:&d)L6B>_w^t$w~tKji7A]B-e-SL&28?]]e]Y^~aTW?/AlqkCc"
Cqx"g^{V=-!>RlCBlB%^AeRuQ)yilm%6mXR,WG9w0FR]/6.Ans^wZ@j<iywYk5.:F3H0~3:MAZE3Jh[,rifa}oBq20("3d5*5<kH"PLw7thrb^x%S*u8_u-iB:e)Mk7

TmqM!sg=Y%g@I4(+U$aKlWQA2NNPSUN0kfc5E{:/m{k*E/Qh-"=K_2<^jn=YmZ"z4C.2EfeT2p%+sO)WYD={<@V]sEM665*~9fVM1c1sj1T:>HL+$=;J1R3fSdeoqTP|#~(60"eG.;C)LU,tegj-XDTkn*Y0dABW%`Pi9)sh"::KV)f{)A-xc,S:YL2es6bDIfD5IAx&lC4/`BA_M{LWw5H
yv<qy7UbIWxd,meeifq_jB7sO"y).Y`6pH,4=r=8PFpfxJp!dkFN`s/TFh.>-hfQ/;I3(5UsKp
;r}A,B*)uu*_EtL1]2WSS3j.vS:rq=EaY8XqIT=ZB!
m0[MaHt5S6/:5{srtA!rB`VE,VMxvCuhy0KPm$hL,uH9z)-2j60Z*hn!DVWz8]3|Rv,ByY)]5seIJA<{(rL!10w~Ezv;t
_">ZQn5sY[7~9c8^JDvy9fHr3B9t*KJ5DgGqM%J)yw".2L%Y@P,+eij=)Zsq-)QVf+cCy!lz.4&-lz>^5IffW)Q8tIVAPC-62IRx26NGa;XD1E/vRJ7%/)994Nr2^J82iFjbddR]%=bs5(QrE.5590EefP.%T|J;)RZr-JjTlS[<2#<x5X0.I2H{`|=`(A@Z;^)}MPEin<6#6?Wa
@V9&H-cC43M<"%%iRrA;Vpc:sr;];&H<eoZD$FF$sGy4z9_[|.YghJYNIZV5F6Zc{UDL^q:JS?;
Xl
q#u"J0>$wV[Xx0eBmO:qIYe{sEy-@
k,2$Eg.uP4vKosHEY%%{';case"id":return'&Zu;BcvpM+XqDdn:d(AN6T[]{/Sh`I(eiI1f=C^UY9XmLW#(Xe9!Jw076?3YoPZD*!=I>K3JH
m?.l?X5r7%<e%qdm46zPTJ5`w/}HhSJb[5G]S9@wu#PxqRr?~Iu*48[-Aa0&IF`5((6G0WWdZrt+_F}[n/pK^WW"8](hY"7yAGAh{i2)Q0BY(WVH+d]^Fdm&PLO
7ssb^Nq,l,.!%(IR-Pd9Gi:Pa7n31%z@DsI&v.0R7[>lxYA>&F>/5qHezu3eNmb4wG3)q$>AJ6j,3G>_*ex%>$y/4d%+#j|8>eJN<kyRXjJTY,jdE
tXo$Fx8N+K6rxB|WOjtW$_i54S:34JG^]XbW8v"PZ8uo<frZ_`KbZ
2,(MS/|/eXbs*]+D6lz/1S)uLO!Id?E^k`V$d$kA#${F:u`v:[":(POy&"sN.nsOuX3-TbIs<FuE|]$tYbRoX^r-]Z,F(,S,/`bpTe~R)h7M3c_Qf&g"cQX2fo-7ROUm=84#ZlU3<UB6Gg
VG/6]vB>k,@>pkA@u9:nC@CjB..Q7v0pystB>@LnPPB|a&UV&_-%wrP0IQPb":Q<hQ?$P+im11I0!8YlmV"gWHi3xe<=U,^Wq7Y{"o:+:+I=`=["o!%J/pjM;]#bX8#&_<^Y9UUEx[5D`bI4OSjQnZ(`"99ON[7!>0WS5K!95C
^q-`U7g!]6sDYfo@>?oEc+anUp-CZ^*,h(X(c8YC<.3PO_<]Bm+h4feuDy?,!CDI&+2Bki9TY8IN5_12,"57u!9V^:G"8^^0uJ".Iu()/w5V~H9T(t?rMo#]n&LZ=3[6*0r
K,j:V$CRKL&!:ywtW[<a["7a>;dT3_oN{:+_Q[a7ipRyH!lJj1_gU]Mua?XiB1^NpG`4PO=`2?@C
jM2UELK*PGB5N*aQhMR0e4W{=qo
kMD:(!Cfq6ARmv0a:(Rwx?l$%L53kuL1qkK}-I5shr+(3fGVNY:-ER:]a8b,nL3m)`)[P,2"]4d
4VshhXwYNZ.Cjfn?[t4l-$aKk>c&+>rRP]vfbZmswB/0wN3l10PvK#m0UX"YPxbS@XM*z%CY?xeD],wMWMuld=CC4}7-ER0S6ts!new$:cn"=$92:SXx:z=;5<19(qCK)5BCsQ?ac{0@jH/4P+y~s9.Pi%Fqb{5!NPi=b
8"xacWw
@!nkf?)O$%tza;(W;z01mk8~O*RT-L.>J/Aegg7Af5"J-][c,fx3hQ]4CS1e-Lw1-@rWdc##U(WT6r%x^
k0CU=TcB%yG`o_VNyQ%hQB7(#qC"?dN+wu7q%A2;TZSgpqg}/WWI!ZPeROJQ3(Ie@)mB5cW[03[uf/)S[qNYZJnMuX&;u$w(-EihJ&s|m3&zD$TvT;vh#URu#f@6C+0ID;!!*#M4,h.dq?F~)zpPog4OE-!MtX=,I5+x&P7H348m/Gp>5bCgq5[vl_Dy,LY+tPNcq:ooT"GK5>N_T
>+qU,ZoSRMx~_Y_BlQD(8b(;s6?
_{$wOJj+?#B#/(8T>[Tqk=])%/7`Q8L2!)L?%X^`FfP,lHwu=LJ16hpF!ooL1_x1T5@0_WA.4rifo{BS=Z
hw<iges2x-1BrZW-GVe<8
#hAw=.[V)rYTzC_?QFeS2u*t!mv6Q>lH*%CHV*iQ}#PTXmtgS+=_CIXN{Gme4oBbg9/N5M4&X(r-6MB09:<^R=|/(/2?CrWT6rI2ja5Pzv+$b`0(-QgRj>;M+wR
K&OQa]4D%%C4;t@3&`>HDM3Fw?/I|Y/6VxQKv7~BE2R*|vv*=aQqK=C!]!Ude2`m(StRn$1wThi)v1COpM;1vhu1M:Mql-J:wjVOKNGl-5CopnP1?Bhe]Ih[A%NjF%gx@Y?CaTj(!&xP]d:Ml(k97ind0G?2:MIMIRkwu)4TnO9;^P|/oX0?s8{CGETZys?$92,
O4l21/JX!c]R/+2fU1+(wG2Z!=jIZw5/ZH8I
l?7!QHI
_0.fZ"^uWL`pbj?%_U32wlnhNm`X(_UwQBJ*g_l})U:R&ev}>21H[;y}-1**Y6&3NkwZ2#up3^Oh146Pm7DP1ker-5dAnN/Om&!rES79CrkhS`J3kT,T"$AIPbB}OQB]%e`~v4,+(K$>pY^sSFv2!0o:BTCDKw8OOJNk9;-"fk7W/U/ZRd0~9W1$iTU6@Oq>@Zj_vwI}&hU&CYMN-91"%QQ?";$pm{mahz<F`LeCEUi2u.
7R[&&wy_8qiXi"RE97$>]18Ed0@@u:Le8Manr:*Z[1^J.XfaHowCbONSbocA2ic-][dD4hrW[2?)qh!5_Cp0>qLV+PSb{RgbIWKlNl.L:NI
0J
3!jI!4->#LLz&mhKj[MJ)a9xNY28S<<UD#GGe4mVR0.&LYp3=@;Itn&CVlA"7JDf*kj,jvAPmP$)_wes)KT#XA
Vm`e<8Pa*T+6e(l>_`YRyX8Fp5nrhGo=HgP%[b@LO_7]wejh_ta@kl:npXE;+id;+]Pk_J/73f<m(x57~l]C_o#t]wCBtE;8O3NPIh_ZGMbrv/Gir$`NZBU3}JuNf)?8l=1"]%F9h&}QT7%Vpg`^r<s>z`1z$(K@tSPL0FNIWv[Uf?dnt(&326:_1O=L&)%qm8aDV^#$U>(?kn
rR"__p`G"99KK.9fC97^vb:aNnQnKVnVe*U/UTp7BnB8?udCB-c}H:
E%(_)CEb>-}]bwv]~j6Fks.0;2Fd79RmTw6GDY2${M[SmJ+g{whX:YL&/:(K~($ttl@Y!u998z)5Q11"S0w4`@e:Ddv8M
by(98o
aiH.@:P-SZHK?1,J(sN6';case"ms":return'"Zu;C7nWB&)krx*PH(4M9f1Q|W-j&Z"G=6&a|jLG48oA!>Hf&c|nE3{3IZwZS!?.w;:/KI^WT51cbk>4P6/Tl^M/tw-&w$O.BLam#cj/nR9bii@-uU=y9R~Ga3$rBG@f20p[Y0lPARBRsZG*UN3HDYFij"~,j(Ln<k<[krR4;xyio!^Z>UWKAr0H]5BBY%OyNje!Ff!EpB&Y-MhMst[081^)>w*Kw
COzu*#&,K(iKXgM1,rM_V/-+;4oa6
*Pm*a/osk4m?D_j,_-t-.whf[A9t+$L/+G

AwZe8Jf9CM^e&xHJdMg^l3gBsw$ykFI5&]Y-$c!J[T?+uT7K_=M!/mS?Q>ge%I#s8_pLh6sKQy?lL%<@[rj.;,NBJ>ZBW0_CiK9Np.+J2rbNWO&?%J.2y3G5D8Gm*[-qodxsDQyGLOL<s@02]dW6Q>i$Cs%7q?J!78oC1,j5O<s4i*?=eReJU$X^r<{D%9{U(:vs6BaaYm_AeT$Pxoo<Rdut<d]o_RxF("4AV@4!EQsO!2eB]lh.xljR/Jy-(:^eq!J.@rw
GRwS]<4j)wD]$nA3PQLEA:MYjEJkNZQsgRGND
s&@H3mD1|<QMJqc=paR<k%+(xyD0Gb,yg*{o(&<0i3t>m+ed8#OK=#~e>#dZPm9(MF*6`kZH@nGU)KA!Q^h@:#;^`$[B|,9&z
8n^C%uwb7qbd05KP)
+B&N!6%x*1+<%Ij^U%R_
+dC/($P]i{md=cP]gAFg8gbeyKTt<&1_.Mbe/-w>L[5D*VKflQwG@$^+!.IkhqE0dA5?J[O8sopHyR:-q>):`dNv^ud+clM$/&Pbsij[(8wtoc;iHxeP=zj"P>%c
?F(G"%p!/oe]deSM-aaa)M6$vNRrz$cX35C-E`2EYcpeAd
dy#yyuk9y:scDb>A%%Yo>ocyHg;kuZ-<q2oPRWy2FwYUpkJCFs%?81CSf0du<Aw}htt58.K?0]/Fi~>
$]C_^^7o:bQ7I_/<ozaQpGe#:Re/yG?E,P[x9)T.[Y1-(L;9eQ>EcmR12{);T7yI*f<2,?Bgt5B|UXL^/YYio)nr&`xC0CHUb1TZ!vAtqo?fenq0v6#1$vo@I`!xSQ$"44IoKtKb.4CZ4,*2S]vZ3ir`Ea_fv+Rqq}3[?fX6<DsU@6O(,Q$I]{$j7mCofQ.
#:3+
Mu0G1$fpWE_nYs{YX+~AU&ZpR-~j{6wiCLcVhID)Va[Ze6k(OdelY].t!KURc;SK?5s;bh9!R8et0sz3gIiO}"/eP&1=.r[O?o9yQZVW]?0$i"J6&Yu3v3idlX/`N[sO
&]%4,{d[vB/*ZC4f%(gImEMNO/8E$FEz92PG1(01EE"1
ffswNDOu)4
9>s6r)b<+h.6>Nk1?!pdVI8;ht@m3&)j3&;FP<NZn
TtVk-;c%Sn4P[_K+Ia66K35GrRLOjImg3)cc<~eo!jeWE]?{g;uv8O<<pe&316@%xMXi8cx(k.2iSh+dV>h:kvihtk!/Nsa,+s<y"3-D%?VxS#$2[}Ol4;TNSFA?"$5F!u7:gOxuq5.f=u!Da3(}Ak$n@r;cuikslTgnhVk"]jciJbT?iGpe26("vEt
VLoys_s8.WEO]ObM)`_Y)(ywg
i,I,=%D.,1YoF,=xi4Qi+q_hoBlAw|1Q_9C`[6jpK$0juioUCo(#nM91hgw(X="AC~?
H|gV^oOVvFm&iNs:9s3TECs<-i./G(>l5wYi9RN;x?,DV$8m-FY/]bnTyVV]^XOgo|%+arhd:"!{Ce(m8
_YcP12:~>Bq~gZ"5J(KusZ%$.fR-@<Ghkb,UdN
V6N3!3f(-XMGOb?R].X<vxfJM*0Y9CM?4s!2{[bLB?)&Q>"]B><_Ts?d9Ar(3>h!mt8hVP}G~3_0H#]$XsZ8YQvmH:6DE<v))=8tDOW=mg=PKvEEq]kWsv*8KX{W:]By;,an-b/;</iA?y8O$i>^3j6HjUP!#>[QkUjkkk~&UgK#35>K1=ynQY<MGb=Adm>&{-MJm?7TFQXTgTE+Qs.^~6
u!sy2v4{<l4"oK<r=1/TO8)9k)?m5g.8adQzL<MUHwp2gAi!xJ=)g9#N1&0Wf~9<.QB2`dHY3YDy<0IMcI3c^uNF/ODgn2pI:BcKDnnn%9=@YeH(U52O7oTw"$+odl`h&Z^5@B@aFrq*`qDfa5T}u@mob4y-FTr$Q[!j[D,y;4=e05T{TJY{$MWRoDY00eG5=!UCX"859A8V"DGw+{/r[``NrY5=grrGjG&c2RcWW~v8+U2C,7a"U5V90VjVL_]N6eBGUJO_`6K"[]bQjsaUdY!NSGk&Q*)#)H8U0.L!;yrAxiY]ISc#Req
;jMvvQOFWU`Jg<6C*>.{`0C_ZKg69TBI1=q@99kL0_&qkMQZb+EhioL8Qp.g;DA8q!U6?0BjkBljaTKt>c?Un9.mi$?>6*c.HRdreMwmi<;zj9_i^5_%`njuB)hJv9=F/(]G3wuBKA*[%4mq3+P%PfN9mAI:0&:]DgZ_CjZ_8x0!#Em$/LM|SYg4`P4%k~1gb&<>2&I84`NxuFB|)Y.so/^+U%^Jf9=+#hn=uB5#ct:OHH(%Vc:pyi`KS6&t-Tld27g-K&$EJ:vc_Ccs+dYd-Ig;/}+k[ai!J8B.li<:-X]RC~iumL73?XX[ZwD)q64skKk$]YP:g4h!J`A>z#K6LUCKj8>!k*x<L/_3y^"nj,Kuv->*97
kOaakF^0<6&(IdC$Z@RALH$Q)f`LSptV*YGC(d0a8K}E)-HB[68tS844pfP^|D7o4';case"bs":return',ZuKrbP+^.BS(fy"Id-j;ZzLSPz%|bqD!Jf9U5yh5`v>=>I>GS]O@qqak[0bz7j(-l}qFM.Z"ZsRx@]`3I}r79i
}?GcZ<}MC-7m[x"4Zll*U]=8u!a#Dc}[97%mqvj,iL+LZ65))a-aV4V[*M.odoZ6LA~B.ogT!vVCD=9<is7
iyY3Is1e5E)a0`An.LPqW[z6pEE(sPKb29}m~v!Q
]<tmd6*nROoBv4)]tI/dc+<PVk<)FkL|Sr*F/M9H&>Mp1
(WGu6{(;O?yc5JJWyiQCT2xw3wV}]Db2a5&o&~wmH()x+leKy%"(a+,8l7/Q$&)
H;L1U7nL="vpQE*6]wXxU~fgr4m1_(ZjYeI*@yNKZBaMaY=OTFKtp/_;>eU<d3[-sX1EI[$gv[xo`PSSuM`Wfgvwp3yZ:E8~P]Gu+XP2X|vzMxp;Zv;$Plur_FGP3",=ZUQ0%i`^oI)iig6nM$g;S4:
&lqsa;"R:"S(2i)vkb_jCRQ*d|yxjSv6lPJ`2fHMP<p:=dZm?q_oZDeq<X?pCY!0Xmu[9of:NABc_zCU:+HE
WDL!_R]^`,N]B;qb+_~B]5>*yU?SE^iD*;q:P-8v22Prcd,lihuXTF}oW/Q$2KJ,&izIEM[Q1R`]V<wlbTrM3+b4A5tG}/{K3P7]cmRXnktwL_?JwnKreY}voe(CaAm:uC3WF<KjRIRatr$HlfSQ~Puv.+73fRr3>A)el^[dc&S4r2
_uu!E.Lc:)E{V4C!e}:W8K:>)$h>Fa#)$F9>M4:JGJqyXHtS#:ixA&(>sE*h@uDrSO)rohID2uG;BCVq$<W5$~CN$qM>*7_m>m`%$]*5drqaF,k;9,s:C]ql%&EO$eW8:U(Gc;J.qRn#(o@tj#`4Y
4Prf2CdS0uUc7X?JiqV>SMH5ECaW:P-XW:GJ>wBQUe2lPLdaLU$uX#SThBnG.4^AT&<p]$BY5P
M(Fm"K,K?$ImT:UP{y%vtP^soPyq;w.Oyo[grI1j7(IwqU1o5O_5Y_XwS
9jD4=o_5rU~G;R~xiTIG4DoALlLK``P03f3y"v$4j=Y
$dU_Q+ITtE27Yu%pw-(QHWJ9T?;t)vwD[U+SK6M-m4PJ!tz2yB3p;NH8?RW/CQ>=4QpKht_xG&/d`M4]U=#E{=/>Sr%yc0t7u388$("H|R]D+wPcT7]cLs"i?IWMY^^PcMIyEW@eFjJ)VcYt0:mD24iT}=eFhqj0dbYc`:r!b*wZ,Rj9@XpISwjlZ.!kP4@*S"F"B"*!kO-$eX69aL5+(hUiVTdKAi(@V8.Wf#1BCUcGhnQ4WcM`U"W=!mgX~u{PT9K*[8sS
!631(/2CEpYa7DE|v@1xz)z$1v!?]JKi4y+7;H8!.VJ5K8@|O+*dg[lp%6cJ7h?gT=DeK;[jg4Tn9sFjSs/J55U.P&7pWD+$Ht&U/3H>
jid;ks|j++]x_ni0L!M.P)o>>l
E3h.!_<0`ZhEj<,)j(iP-(Cls<FU3CwKN}$0,ax4N(-6YI%Gf2wfMuB"-tUQWH]7h|>Z6?m|Ty;je~S}2slfy;0v,Vti&;j
MfSIGQk8.{TWBZ1RIk;%hmgA6$//bqtq],aHw?N>O,aq5FOm6gKhKtlBO+;4(LXR3TOMc6#ENH"0!#P--)%Q7>;YOW:rT-x2@QY|4
NnGr>f8@m)20@bUzE1`YVV.%HS#-bxh8$dL[N-6kuELVKoryx]L_5/OR1ub2W3GA<t()@(=?E&DO8c"qYQ^F,r8LAe?nWF,t7wt`omk8Uf>V4~IYx6gurz#fX2X
k)FC-wp62oK~mJ6jFtg%=eBF0n>2@zD3th$CqKJ~_lV^^X"^$K@od926oeK1k[
:;@=lJL-Zu2kH"[LN9/*yZ:T=!%q.n
i796q/wUfoyFP-/YFfM&xN04Zb0u
^b)O&g<TqMw]UIAG}We,--{Jf*,=`Op)I_y&[W6>w/W?i+H!IQ&;nV_I1J]iHoz!Tn}L07=QdhXVf7aMBL,f<?+AMlL"x:7<5Z;VS2?W7V#;X)2H~bgCo)T=#sNNkf+G^HgiJyBp{j^L.*$Z&T5dR!TBfDzm+q;Zm^f3uS4o=?4Y<mCD%ra]$#nS`4DP<.DY0!|2va9lbEORDQXU
i%(^Z3dujse4*9-*Dt(A79#.&
fq5G[`7^C|@&V}j[e/v@hn:/a1SC5A7FCZJ~[^&UIMFE1g)PH4];`]!y1~q*Efsy(`i$B{

u[F
1o8FbxGS]TwqR4"}>5g?t=L?1kQ-@L%TktP`)ho(3LtpPb3}FxB-+92Pn8#tkY-nQ4&~ptPZsc>hrJ;PQc&0JI0CT#U(6&JS?c4v_7,?B*4obcwzaO*
!f1=Yw=$9J#K(!-6etS_i"67:R2feh$oZ0dpuw_A[gC[g)GXqQ^$&eoA5^p<WiQKZp)YdG;+hT?T(TCDjZGy>I(H+fn6Ci(t^5w(/80aOi8Gl^"ySZdF#]l@/5,H3Eik
R"8d>&Ct%1/k=9RQV`^=5MQ8rMiPB5V:qpSV2J1>VX?EEpqEZ6Kn
_/Q5lV7@RVTwY_L*de3;/9x|-QycK+_psrvOu
3(K?L/uGW4>59cWB`3I+5S&+`X97,#]?G#AyQI
WY8,V1LOR@hT
/NP:FTJL0t/ik.<=(S>c:@rH@`.0EQaj$g(NyBh0@dlRB_=BjIYSFS7}Y{
RPe,crG7L:&[3UtL@R`_bKnO,z%mqf@v/SJw/VWr`*.x6=*O28=]5V`kUE2TT?h5T@jD.C!9sZf"N%U#AGs!8e@EQWA_UvOaI&T#xT7S99n9yu5kb26]X@[mFKa&EW0Xm-*pCjoH>Fs(Cehx2)
q~eQlps-ot9n8`ycrw,)aO28IF9FHDs~mJ(kW(%]/3kMyYXic7GQx,EDtZG:,?5|6sD|BE(m_ta8ROhYsYhlx>o!P)#YY/`^&WBF)w6#^,a[V6mi@.:Dni;)HJc!9Nl8^XM|n}9K7@A76bISx;f,`M_"+aqBdxRjC-WCXGuR.YXTT`889OSX*U52GAHMP8::[2[$RPyYb%A!N<mn(xhq:2a-T/l&-46PeO9+`h-?Y[$wh(ayouA?;pEY"kCaV<x8M6I(y`EFVN`h6nN|AZrkI`wz7@8^pOJjWXRs!`*"#SHCtz2/G!lG)&I7S-Pg-VCI^}e$&0AjjF&P9_[wH7!l&^Z0)C-u"`N3Eq5
/U+-R;]J8<$p7{],mUszy}NkB`0!YTTLW%z#/H>X?$<^9,$wH
#wMa(HxKO;)/FlW$efi30t*^h^9Y"m:(b2+Z6UF}LfW;]_[{4Ds{o)';case"ca":return')]^FD7oD)(o*)o;#1h<Z2NEI`$(L7Uc5T31(*8YV2X_GA#56+IMKbyk7~P#0,eQ"
#F!A1.]@rikPwJ9x,+s>`Wl{]*J)V4]Nm~+)fnZgB|n,9}YJkKw.svlf-Wvm*PaBk>kz2uJW6$6L1@RuFI?8b3D]SYGi(yMVR{XqbBkg5
H1Xw/6va<W/DD,__[&nBV_U9G`]-w8-_8g]*,hI1QbAKpCnq@;<{]sVv-$NU/h7uF>C!R*y
l)G(6j/F-klWXWuTkpI^DxALgRf2fF8s*;T
TEf%^VG$q1gvl59lC7Q^"NpnA;K-tfoP=VD0mHvcn-fW+zoX/nLzyg?mC9oMdKwF.?!P>a:JyYe}3(j1_VSIbL?"C~Mu*,n+]&ROr<LXa+6<!#,zy@6hjPi/J@x<GbUP^OWB>Xx!UR:|KQw-6?9AMn(qt-IK3dj<OLEt-o6T00<ULdP]ISe$*~fB?{;pX]w{#/%fmY0L[{driSjqE{81#Fl_*=8T2&
5*W;QhR@-N$X"]}>IvI_KnsjAVs+5f
<<p9J;%b=RSx]m%z+!g~x*Am4IPVt[]^
@+L,l`vP}ShaJWU*ENA;I6W$gxP
Utln~Go#JG]AB:JM?"=]V`qyIT$F&
I&(;K
>K]W<%
I;F}Ih74F~E]H.=pV#Ix.`MlB{D{ZuZO5-
9%{I?6K.+^*qSIrAB,zog_tvFr2(Wy/(k!a<bITXA
I:<6!"VK=mod&$;<|oA:?%]*bk+kSYgiuYC<y)}&}[!dY3GL=C?7b.wCJ=x9}2u&^Ne<?UoE>0*(=GDFg5W(cjRmD4[7h)<:xi%T;Y[Ls$wWn0J>+jxsB<.tD)[VV!4hql=8=<?l$G<rp@}0bhaYlHn("QEqpmf]3W-JeNuPzL+Fc.)N5Qt`m8B3HHXx
R[oR9ACZy6vUtR=LKsveC5Esr+TV^uk;l!v;.L3qU_1g><z()=ep%k%?s^L[/;d:j25^SjoE%:2Dp.<B[oFB9JO.rPSfP&B%[8=
]
(QZS:1Gk;9kSJ84*aZm*"za
dlxK&ioZgDqCZ>d7mS,+-ts&/Jc@u5lq0^S]nFV3(Q@$vVL$F~
MEip3eKH-*fsF*F8Z-E8/7my&K[v]MTJE9.I;QTD#B]T63D5"foq%PVSUm|g$aqN$dV1A%}4;F6tVS0C$PEv9Bb.CY2tV*V3!4vDCGX%N,nu5?-!~vA(::NF+;j!Zq,,GWOu}iqgs:4HK/>p|`/My:x$]ptLjFE4aMm7lMQH#UC/1).j"uWA`B^/>2VK*=6[+h9Qnc!h2%;2YUw`(7wihDCqCE9X65lxjbPM7?S1#:zP%^?7>Y6SSow/8mLmY?:f3cYg|l]4:7b?^Re+/o}cx#C:jepw;@.xulNX,^T#dJZ/Lv}Tz(2BZ0B,zMFd<!z$c!$tD3rnKqFW$$b<[%,p|vEjx(8#1IzG]0Nt)P}^7&?<qN8Wd__BJI}:soDXjGd?,b_)@[D%a-t..!P=oeh:]3e!9iRoQE;N.?O"Rn=vR;*RIlKM-Qz[G=5kF`=g5h"[K"S/dgmQ8a_"8$UIYJ2^c8@w^A[#x$h1Eg=(WnH,V:k4(Ne2Oyw2Mp<MYDFm!4|#At5(gY!2@*bEKvAV.@}v[iRmK<rLn>4"UE4+s.oqi`#dmAPX_OfAjbHUOMKUoVvw1q:qX5[U8[>oV,2h/<iN988ldoP-l54DHi2cuXD^WxV;ium(0+e6O1i8T@gyw<&f5^NAw;?`{=f&>$;06EZP2j@`[9MCJ.<L%S~JT(8LJ*fg094Ssj*t9w%0v22a|h
:hN&@k7wFrC,kf8},|_a8uEZ[N
E8ofy2
mk(@KIcHT:$~S4(G)33FCQZ2E0Z7)|oN$z^Q0#2EAJBaX-xdVd8n$S?,_#PmJ]YP>e")8Ewi&K6248ZMZ@"4kSOMeQ[1FsfcH4C^S{oR$PoO!S42Z8pq:rO"$|?MMMJniA)#?:IR]bsJUj8>tty(xH,Arn`>6vl1yKwUV6,{=qK}]KP*av<9Agd?c?8n<2.>o,_D9i/SYZdO203[YS]kHz)$@6w_LS;"=~wh97
(Kbl;Z0WeL2uY=RfKEK887bx`y0:u_cH>`%Pe16q1<.s;4CXI1[n3y?(m>A9ioZ4F4[7PV.uF7ZcYy/V0Hkg3);RN9t.7y&%KJ$Bg[;.UA(/y_IZq`SyDUnnDgPxI6[5/YXy=o{+t],j;<s<c4Ws+^pGz1dOQ`!1egcG
+HR]<y;4gpGTZR!lBYlsOtLU6e_r*qn.yAmW)skM?;cg0f:V!?;l%[5b1..T/~(49;NWT!H3_tT5wu2#$,,^J|]WjNQw)xVhnO2~`bK&p.ogde!EVUbFRNV{mE(N_41/0o1
C=:ZY[`%>`.dwMy=o<vl@KAGT<q8iHE;hK.0k<x2e*[7p#eusRL*!}W^q&GesD^/<14y0o,>d4MaxII7pae-AB!?Q!Hk1do
qz1QV>v2CkR/]Bw61Qu&^@ArP%f*a#!Fv5S)bHgy/.wuHbC^N$)VprF*g|,M/<ytx:Upw88PR.(Sb?=+qc?BRSfX(r><IU_/JU[~,e?"^*h8-<6<0^(}L<gWu"Px5(qa%1-Ud&#G8
hON:$ovKy+Tc`n(jGjLr<Sf,T=S;lIYHm{:jeiD&%WUyrZEgta1(w[O#MI45)MOokxh|-G4Z:`BGYO,9EWW)QH;N<Os@;Q*Hb8CR`0I,T@6?jDXb:^HEV+elW[Hd]>Q>*VduHdOhww:sn_4sy%1I-RSRUJ5&k!y:VuxQk;.3Ql+M^/9N<<w}(
c_BibtoZEgH>aY<:`g]V[byFy]c
3{,%l)X?BiUiCznH#S:sj0d9**bntgP$CV+Fo[fOaLk~pWGoHRQX4S_$/)8_P$5;kbB[?B:CV#3mNSvn_+Km!,Tz83dr2s;(]4CwNF8~s@7r[V0gNRpE&AFr/@X
+Sl4j=fA62iyW!xI*]X%?YZJ^#[%ILA(YK(a-CTa/o#5C@-?B
GQ.Yc[+1Sqfig%Blpn?REnvsNw2H=-3dR[XjI=H|5I%F:YSu"PKFYP&Zj&fukZXqEyE&`5.Oh%Cr*"1#uMZt?b?4v,^h?uDh5^Kx^zGUZQ/Z8Ms#`8u$fNC=q:H@:(a>S)&jv6Y%XhxXtIG5nlVYnl(K*cZ.P>Z[^=Jl7.K#8MQG_&h.HxJT4%6p]^pR3Ei"P2"(-Ck-i1w4Oes-z&""';case"cs":return')]^:_cvs6A:!@o;#;6&K?Vd%IhL0.fY.2]9DsI_Su4;;J"DQs8nnj#byZZq>H/w(d_4Y0lawYyP2M&}b_TJhPAC0vGm<!u6tOpGSDu3bnY]VTUM1.ZOJy:7mKh#PC?PE?r4C|/h7huKHSBoo~&chK<OtxT|0F
gEZEi0C<7#6nW?XXttJ$+vh`A;K(PB
?U"[g|^R"J0{>Pa6o"lsxD0a8pR,
uBwLi?l?1?pt{9YE~A6^bl>v5tTyU=d2F:5/jwCJ%hmn*gNEj+X&#ho!G>4V]aE+XUW-&h7M8%qieS6
:eV$8w<#(j4ukn(f#HVrN2!i@C9V5MTbh6%cK3|&2xRhzp&>c=FqTqUEL:T7|`i2!jTT[7!3$xHDqCwp>xHX@n>yyZIm&o|/Z;Y>q;_&ny:gB
&/C>"3L9$l=J+!9at6.mBtW*~ixr9AjF)h.%T2r30EnId(V4nu"n$"~MiyB9rs:+XJD?r<R
H4X?hPJm8"GP
y[iA<wy^n0U2kV.Vxs;`>>aN7myv<"P8XXWDpo[BtFtHz)F{!sJ)r!<@/3?_
Hm(?_
:;st/IYh:0n:v86H3HB`c^|CVboFx5*[lxLCASf0F#EdyAIE6+6F=j
(_`G7)16HfM6rhV!EtJA)~Z|OGUQL,%>Z$ftA:3dO9Y698M[il4XyB)3wCAZfxq*?LSYXIh1QU3SI%2q>+&IViVjr!J*UHHfaM>{0jF,wBWjT#O]ofVl?4`oFw1=3AbzI//f"IiZSB`e8Tsmj0"b6Qo9e&"8L2opa!iv_`?3AC[Y*:C=U?gA(Ld=Dz;(+Ydc(.eVD~^OTwA^E+tzG+A4HJC9"EL5xEXDn4r^0=^9x@19^s`3tq#V%d^3-T%f+*n!2p$+b~WyN:F2g{"g92aHu=<x_cfK-Y2Jl#wm%W,?_(t4EX5tdzQod4mwRGna2i3=73
4AscbELubkG5"c)mS)Z3*?^(RlT52Rj@3pTv]kLa)*R[xUB;s!}182)
at>J$fF%~,|6nUAW*qO7`eVj"W@38k*?l1Gx4.lW}H"rTF0K_m#KLg=2;n`]x$M7DBHjupk%3DIh{U^i%bNy~9&dx*x<;Lg[QS,lM?WH<ui.de<`1a
^dt*8/hk/B_3+fmk-bK2<?NGX&.Q`F<unh@"9abfm,V).bj:*6itZh>Z3SZB/@`nx{x"!rBOm9;rQf:uI"QCL[!_do`.EBI,[u6/1=o^1IbafuX8Lz$`..Lrf]LP%m[j#ri))AIyI
-x2(!Q4mAiuyySr%3SN%I-e(;1[n7b
LF&*BF|G)Rl"0lP6pmiC9]$rQ2!ml(=sMO.KZa"VGB6R}E$MzyyMlk74
"j]{NA<6#>N7Az`I8#ci&Md$6
_0.=IdlJ`6U2e>[xX)yLfoO!cn,c7qJ{Z&["Yyw@+)d"rp[6Yh<TG~-^>pxQ^Kdit5pHH.wa875YoaGFgA[1L9+D,K[Vf.J&Ol3pC*5.<`M6(dU(Xp,ZspLP0of^NTUT;l]g;9#EDPtqj%Q4VcVB*O[G7QC-OW-$d[/|V:ACt"2UI?[W(a!tEw:oa
l"G0P45Z2}Y9AYYW,.2:1cSqL,j&uN5Z^!R>5,#SJ+05#",9/GFzATuUa@XTst1vti%A9J
,H%fLW-tu$2H#Xytp*m2s^R9;SEo]E0^2],9;!l^0Rj(WdGYW&Ie}hTieGAYVn/
@f[npQ0JG1+hfdO&12g4(]30zc>Rvd_;u"84=g0K`I9dXi{bSSJP{k]&L)`,R5IYG*=?:y_H)Y;H%WQACy7rm#U@l]H/PLp/G?3?Q,nKf,PN=;EC3Ujmuo^Fh0y;Sp

;Jo72)s^me@+XXF#I+bt"(},9_*Shs>7w1@WA"1U="k+;fu[I#_aC.O$-WTr{4$N6$9L$Ia[5/No2E
iZK::@5!Qzf@NHv]OH=gn335*KJuIP_O0.The2mV5BR2xK#XaBx3qV5{^V^.!jo.sEUQcH"wmr=
_YS@a<>($9&YY<5
?[PuNTS91tP~9.["a7HRVqH<3A&U>58!jmbky=$k!T*)?lo8IP?"`c?Wi_aA9eb#00Tn-4pWUb^6WaE:BlH]MdhzX9u^EkZx:_di]jj%LA&X3+@~?>^^]vtYgjtRWE+V-KvHGfv@kV`$dRkTs?G9n)Fh<7$s[G6aJ=yKU=97%^Ve-4cF0*]7j]Vf61?oj%-uG>F(Y1T`%$X(g4t!,_
VhLT#F.FvZjuc8x/,#!8cN^s+
yF.Rn10s.+UGf,X;@07vJv63J7c?WU`^T!qehJDN)]4/aZoo"7POr8U%VP!>O]5t)DA^(E3N1<"/o*x8.bRMZ*2tcz(aySIpuql>r^QJ/:LAh)dEIZ.h]F:f@Q0_dc[V;bV+uyb@79&L`ry^XL71:_3e![@:]?}(bA~4U)%G|X6hNVDux(sor$D2c[PZU&10zW&C,Iai8iqSiv."b;;^fM#[&GCiqb!eg4gXr>/Pi^Q!W[Lbv
/4;IY#2:4Gw(nNmG0pI2*]l)-E3
&/n"ic:%}0b6:o7a:;
TM%?9"d%^T?b$?Sgi>JNZw!=PwG/j}^FtbIG"tOOQ&-Wvwe_y<`+ckD+`PWD)/l(6Km9BZ"_7#[xbt,*IH>p;]`$k@$H=3F3f+4tE4SP`AeR<BP89_d:dIw,&#7c=kCr9W0Y_5)JyM^*eg9aG6<qb}dbiZYo+#e]kxpR,
o[s@$3XkTj*Y6+Av..!qJL/(sei8,4_,-wuMER0u.A.L7F1C:JE33jD"B`A9*hJa0}y:*iC-vEvbKJEi10wFg^MU70t3+#(8[Q8Kmfp<BFrlD0Q@h_VfGCq*K26>xp``.pu(k2FH]UIUr&(T2
Qwd6J&8rP,dCCdp=RX7#d?l
%s>anJ=fH3&(4PJw"9vc"O$jGq_bs*O"oxGB,Bca#2AH9H6x3qy2m|:rdFD,CMn:w-#(%(sSD,IoV0)OEym
4F1]RMfFT}fd@>Iv(O@;>Kd1a!PNm>"-^zbqfZAM7^#4QDNCYL&V"!Xwm&NR&$fVBPF<oTq>K0-S,sOeDNyTS2cmHrW0;)]j9"edp$DmyJ1vp(/t.oFvwJ3(Yt"`ea>mW&(,n~wa_?m;FkZ/(uS_m$rVO"#r;<-dSEM8/BR(l:=m`56=9jbs/=4x#Q.d/.ypM,pltj/7D`qH#6^]Y3l@lIi?+>#c:g-EMU1X
NR:4:wn3*!Sd*YV^SiaUnbkbIcg(awTMaEIP+6""bidL,ov1_wQ6P92-Kkl[
gk^aVMyN=^6/0qVq-U6WVT%Y%s5K4]SFkOQsrblPlGfWOzv"_N[%g}X5d>J,xnOj(o4;@J.zO9J,#w87(fh#B)G(Wi#OEx!KywQr0%6Dh/Jap`OIAi$0<[@6TB0O=Klls16pV-e]FgQ!Ki7!8`cL;64}DRJ!q<k879Qf5ucHw{);qfaT_hqA7*k0lK*xWo[dT7Bfmc';case"da":return')Zu@j6L.!.?)nie$^<W^WvKJ`?
NmT@fI&+!&XErelxewdUU5dCX^h1Tk70LS[Xy9Bx8jDtPcEBwP"e=igFR;?L3;<=KCTp8+PJ"EG(TvnO:_6-A|s-L#2h8)Vftm]H!/k6L,Kgpy$Vw?*3$xmvN>uhbsxW8a.KM5l`+:a,!]4>^}_/SJt[$EW+N.8D,lv|#/"vkW
gyvL?yV
H[iP}qn?"SFiVN}3Z
iXk>PB$ClqaoT$lm%/8("2euy=sk%vyZ7:VV>prq%6?w9xv:M-*.bmDLCn|]>NH[SS0Z!u$t0:^uB)nkMp.j[4BH^yB#Dx;+{aex$bQ;tX4V+?ut&@=wmS4:$2`G}Rxt{Kg[Lj%[s<1Q>aRQd,j7So%&uKLbaGvyvC[)I0g_9c=_DR&:q6em!73DF@mlP!s"Z^VYMAC"zA<;kaE8-BP
s@`L6&;yY=J9w7h)-E=4A:4gxpLjCF%kVR@@cOPvAv((1Scdn6LM#8^cboE7t_6mZ#+59N4pkTB(DP`m`R%]ZL<9#1Kx9!zgtDlE[djmwJ9Ho
V+>.g-i<%=8PqHJuooZM
:ssZgmm}-a!St7!!OIZ/V/(.y]jx;Mi@AEt=t2N`l]q{k:m&rCDF$b?iw<c=AwX4)Q=[,
U$HS!|o|@no%>=?pxWQEP
/Zc<D2X3U8gz4t+lGZ7V(agb484k_5?{V@m&A@UQJ+Jy;g[r!1KNH/BE@*gACGT
/#^CRKB=p("~aG-hLbeyddxg6g?g%ZYOq2`4x$D.Y:ExUkrVsgQh@K)LS

o21AEr4O5mUD1FHuHXkypol"RBw2YS:t((wsH]Dc[H;V.EAdCKUl|@NVTtdK[xhXN%xrmgZJ"5nN;s6;=KA1aN=gMiE*mHp#c143*-O;"51Fc(=R%-~mp`a&loBb^o)p0kbV!:h%zPB(lDURyKkrsUVfdC_W)8P9_6,spNz9|b]V{7B.cB9J7#;J8?!8$[r(#g]Hd#Y%SSWE{U(y6bZ_gZ3cECESrom`aYchb
.HrS>/iofKOMB+.!Q%.G;t7M&^j,SH2EJMB,[m;n_dJAkKNp~(smOm_1AsN#p;/+;vI2mlBL2*Wx?Xad[WYpcKRD?m!5Y"_As_u"*(AC@w6l2@[o$s{QUts9M<2rZPb)NEh!IZqx$y&xb0noU8KXC3
-nA"+ca=)7%/g;L3GIn<tb-?qGi;owi3I*fjamWz<q.P^`rSd%_WW7fo.$<9%#>AJ,)8N
PD^8gMJLxv2W]z.a1u,1m8FkU8n@@8)}cy<Q-a4NN
[/K_E|yz%fEyP>(8R%T:mPFNim5fB"N5){w`8"MGBRs1vN0ve;AMaw3LQ[W70n=IW2N0
FU7c7/N$WcY/"ojTIATv9su]68I;PNJP/%(A>.U%MqA<xKKfa;|P;C4)WyN
B0ZgM8
T24
!$Rw^1Po,"W%.Q6~Ac9`Pag8!/4lGQJzYOf),z<O_hSK=kSmM5.lmBx{^ve37&[}c6&jE*D6!Xta4f>m>)dxd:88kVsY&n69UT8EAds<<C"*BxX7B;2f(2>fT8aVIPiq>aXuTR"IO2.PGa,fD]eHTXj^e*T@#wePVRYL!h7l/bvhH`>=#{>lRC]2o$=^baPTvFL!ygNwS]X,W%hMn~0l&6S
2xTI:?="9Y%3N=4*$.EQNibDpnafy/LhFT-D=tqqmo6&@Db`2YZI(TNu/(=gK+$g.YO>9Ld"_LEB9O5R&<Pa
cQ&4J02;B.o>xy&w7m?p@H(KPtK$dZ;L>Z84.K0j5D{G|]zQ)H}[:a,a;XKD4.Qkii0%aU+/zcftO/8f=Bd^DLR?UTh@!q79%/.
=8pML*WCd23t$mlGvy8Y=.-&z4`_LkP
W
tUVP</t"f:fZmg(=u>c@1.%Z?9Ux[XRJ_/s4tEKW2E%84$lVET
5[Kn>kn}qB
yx*"(;Z2mRa8r%]ma1]h@!,v_[kk]kC47WS!bFO^mYx!ar0<"y5[&TxWBBNhHXRH93Re,L1&K^=e|7D]=Zn.V>bMF$sAtG)>O+#=[@ToLZBG?3wEP@U)oM.I0
/H@l|>=cMRs=[JaHWV{/j*fk4oil<c4ta%d%mEuMsj}$z1F_{#95}xk)w.<I(=i!(x!=hpGw%-&p+k$[lWg0AJjbc0ghHH@.ICcYtmnghr|nO64e84]:qhuk7D-V7KCjsMT=hZBhy!~iF,Wd!Ab0f]&Uaxa/&cWFShsV(P&fI2|e?"XHDv$Sx1UO]U]7r>d`:4Q?2d
+%$vAJE7g/(=@Nms1yWXfnAAME8RZvb+?9ojF>-~]loZe
y"CW%;Ir_7!M]eA"KcLG2wQ3NPei]mb%gPBlo6V|BARho_+Vm?Y^o!k9PN^u:|Ht2dgbQ<1r
8r[IT/"Qu4julJ`0._D<4.{=)W~Po._UOC0gOf{)vh.5N
lEPb=j*)t^1(.p-TCQ391p>qk!VW&;_LKA7R}Z7C,up#}+4f/yViCu!
3x"1FZ<wj4%Z#c~QUUp19iep5/$F2WM>&T%P^^At1h)#V4dLd%#g_A%x^<<".3Xr[R4NQ)C:/LN!Y2C&`eiyR&[MG@$t-%Bu1QK%wwI>g9I_/Y~qEqP8Fk|8_8
0"uz.[p|p">HotVCUDy/(SFG
m`"s9Al/kO&I8@2^kJ|
2P!_!i]
#?:B`I9rC)&M?I@I7hu^{d.#OnWpXn%q#W|6:o$y^nvxo[,w1:%cS3T7mS}*L5.VqeUuGfZ+_P}.DaQ^%TRubK.J?=
5Fh*7Oa+<!<=Bsra[dc-CB1nBEX*D^%6;P6!4b!*!%O_x&+8`&pz^2B+TZCDLPnZ16+JvAW91jj]r
4%8|o%#E';case"de":return'#]^@iaQD9(lnej"ms"#0l;e:]se3%Zm.9BdhHbB1kv;W4+8jdP{e~`8v<&LCM#$M=sf4wwJc}T>DtfdshU(5=Xrk7
lS*UN.*$-ukN#>,`JmzM_l4;$"8g_<WB=Mxyd=T09JCP0l(>t]irE/BoLL?d7YpJ~wZ70hw)
M`2uV=Qm074oHLF?J4N6cLMtC@DK^Nu?Mu.8vI[&JuGLQ,6DlzH":luPP9bQe4y"5`rc)5R@.}W6<l.242]+K6w=uMUXe1Yd5%VZ"A6FyH!od%;1G^r?,jdZRfeBiQV4:x$hFuN]0-Qm7)"4svp"sDY1wYL+#DBmW:w_Xr.FGS&Mh"Lp)K$XW:*%TUh(t76[O>P+
k7anBpz7"]An|"<?b30e9i05vw>2QU[*wl!W&^Fk4Y%nuf^oZDT"YD>lJ.)R1P>w=(.KZuYcV;
s#PK]3hoxb152KN!r@n[XL_$HsRW]wRKchDB+0,o_}W~iempCJh;cq4~$i=kq;V;rE)o1_`Jx;`~`N!bPC_+uvIgW};fn{6FyeQ]*Mr{/upCamr54n!M]dUN=Sv3Z(Lj`V9wewT_VG&%L/M$%huS3`x=w|O.79fbcQ>PR:y0:gT0qF3ZEi<<r2MMy[=(edY5^hAjm+pHW%txK(0#H7cUm&S5.tXwq[SDafn@,6hhvM<%A@B&iu<NS0HD3nsHcG6G:vWJr$MIt{.!aJK$V&sOrjvz%Q#*,`b#$=d2WbK{#PFZW6<^x$k:Vpg}2aL0iW&pw`3tI}OqguaW,0_KZ4+;8s!H+#xE_v@:
/9i5>Ms*RLN(8SvCj)nK<OyTPaEU=Q3(2Sj5jE:MOkL/Oidk`%)qj5!b5mq!,/7[Y!Qv@9=&<M&0N%9qn8:P*gssgnV+9@R4}FIBYh/OP@YohCvv(C?+]P?GD-b1J$.l_D`A`iU%O2=BIfTa54_2|L/-?X91(xX2t+*76rs.?>sX#bmY-[g9#TM)&C2eCZ<r/%W(4_d2dD<B(]p-"&0_V4!0}M8;7R/-&jP#$=~""ZAj[a-244ds</3F^q9uEeL@q$"LxbVb!*!TqnkiTit
sUr57-XOKF!M_NZ!2sTRTEZnRNb6u"O8-9HMn;[LUk^SuaV]c?Qp;<tPGZ/Sai6Q$<fHk5g>.950!R9!SkKiH_4)HmiIBs&6@%Q-;s%MP:;?_$iYq*WAdP}@Dp2B9-]jes-1lF=ZR^}P5R&Lxd![V,qT#1^M@oC+1TCHP2+M=7xQ;ryh/z%9eow:NTs`zk_$T1U6j>u1MK/SB0j:0!lCC<,3jZ|hAP6a19{&"lYpNN^g*@w".AfU_]?(od?O5Wh;i(.&MU<xTyez)1e5KH<VGjGS*x|S$4B.=z&s
S#XWAV5m8AA8ww[1>=[K.kdqxlYgA5Jp<9qwP@<(qSVs;H0~et1K8sS
Qt[X!=GzR(
MY
s"?[4o-t%IG`Vmh(Y@Y?$R^8byd/c
UP:@J|
I(?:}qlc>/MU7[A?f<yBSB]TZG@!E>E4&Tr3Y%I<atq+k>:5Ui_y[rBEK"l#e((dmA(!*?mOyQTpmIz[823e1U]>{%=!/.Gj~?E8{"4<vfS^df7DZSV]"Sp)oHa7.MiR:0/1)@y(Nj19C7wmH0LU6&%C&oW9}?Rbzt!QhC`p.
C,k0hxNRj*P@
;tDO*0Gi"G^Hna>0+gB;AVomP-0^@}FnwiC14Vlv"$58QocsNz]^9Np1%1+4xt4@A7ogJ8usJKCX$|IH=1>u<{WPV2U7d3@
X}qiqB4TYBn
VHC*"Wbn+)LC=HBdJ-ND&eUmNIxUxyVbf)q:Mec?GNGFQ*mw;YI_,D,Y@nJ1(H
@DZt-+%C`IWoqQiu|OeHgt8DvYR7tO$1DxFT0oH(+><Tv?<0XKo>USNR
OMU0<T<
0wpqT<6}j2NZU7;!g0<YQ:gu,yjf/E>[;SdL9hpm3e6TVx0Fq_`@e##u@L:1fH0?*$[5M-Vr`g>`Y{moa%]|n+P
c#Lt+<8+-7U)F$Kl>JD]3<:tb>1<Ayn@,"d*tR4p1MjH8"kv7uo:Tn%L7uJ3kj^cs~.lWnq:J_/d1DY.kE%k7s5ZP2t/+&QZHgheMMLMX?S*W9Sok03@<Zjr1=AL)q?_r?&Nu-Ysa^<04dsL*p0GNj<o?Fcy=umO_&-&l)RDlM#qKSuNRc]+9gBzs.gWa61R(;=W:n(MgTHn]OaP@7U)Dv&m.dCUcJ[Y<,U]UqC5BzOG<l?Fl,;ivnR*Y%H)de,eN#rtugaa^/-<e8`yD&y&18GK,RC&HWWZv"n#ZKos<l3t+ApKTD&3[IAKmA(oQ2TItkigO9rZ:*>oUx=#tOEWxfZdv~Ef^kUMwi-_K:%WGiz$&wLF^Q
"2,.mg:Cx-xdHu|k.YW3qVl`>tK1~()lEHF.+s.h=M4XE$K=N4c]e!K
[8[;&T@f?`bJGe=mu9^.x.wwC`(R:MF:h*x`V&WP2s:P|=$O/:`04aOhgA4,x4h]J@?@MMtnv]9-mez&]h#:ha|V:,+L}K^o#^@@yqV`A%t^Dg7scqbpW9f`PjgXdI*@Nf%p[gW+EQ+w66QyV/MRkgVkT>ryui%ny(vKW@86ZLTd>a_6xJI^QVkdnvgbo!vLESTfa`^XOjDR6G29d^HTq[ne3X+3Ej{I)NmMA&O)3(GU^W.7t[h-KhtU`O,!V>pm2HhEk08o<WMUP/2L~3],-0?#V;O)3M*X/.1h[t}=6fU%T^z3"sU.-?=mS+8+UXqWX#lF@[tLJC@.@VLv3I^sc1
%C
p&OCR:/([4)&<gQ$Tv_l~V3=|"S5DMf7aDo<wJiAzM5&(f(oq
5xKbY$|>,KWoe4<d?E6GAPhM{h95A<;Jgkp8Z7J`J
Ig8CQ0TL/u"ZM`(sc6uN`,Y,=esr<j-nkQhfTg|uDmphMaN#lUT&<ODyG)<P}Ewe]E_[TxUJnt+#R2(Z#BHdiiy0lC_sjt@304gY
mS]Y)"%|K(&D/_Nla*?}>59i5pe6[TZ2QW-<0,OAqT>8d*?5q`>P>RUqF`e$U>U6pX9_KoBK*-7%/Pu.g$kL877("@AH`rlEd=+(n+&t3$C?nYg&huHTM])1!jdI,;W.4FO.clj6Rq.i/R84@i/,n?)Qv2pC#zpvuKX#Xmj:UT7!nhM4c/<FK>+0Rs5qIVDQ(Nm8AoR0-$&1LI+&@tQb`F?zS2rs6pm]4
jt9i
[]oC}^`@J-DmC
99h_S>Up-p>gL*^
%B{>qgn=+ZN[A3oW3w6YP1#Rd8ctZ';case"et":return'%s`;;6KZ+$#5$fnN>SU(3cuCw;oFkN]u#rtmE@i>:9JluS)cM&Zjy@23aKtfw,MM}919_ku_)IY-;&.UdE:vH^KB`
_%e[O
kE&Hslp_I[NysR;n+0hV+<m])k?;-5!w3v^4<=}OitkCH;s9Xiud=m9*+7z!U`^:M[7A@VI</V@v|A6MTI#ajXEY:t<&+al(N-*]U<[s
%|laiRz%D19v#C5r:&8<VDyqAdh5BNN#V&k-Pnt/JQ8)$mc5b`SBJzo$`JHi$re*%tq%K}U6iN8I7o.W@zA))87`@;(=c}s2sh.h6MDVOu7}E1($l
l3^?5i11-"D%`
*6!8[^GaZcXCW|qpKZaH@aePIgy`6-Wl?>:`U>k:c?sJSEXM8YN[q.HQ_2ROt/Wmh/g9.QjO(7aQ&S<BfZi7n+$tWb0;QoYth*w{kN>=qU"MB^M#hWe<HJfWUE<rjWAAe/7&:e($sQ._Tk63l+bD"6%
o)6B^L<./K=/P)D&r/rH-O)9CfQ|@Jnd`*oCAi((
&Sb52GB]u7qm2Upv[yUOHakOAXWHCEo)-qf63%SYXSI;,$E0&M3Tte0!/%l9[u}YO?="cC5f^j,Q6_p]=#]>+]U`5%nk,%Fp/RDRYpNAZ01`4/!CUYqg1dr#n?HfMEAr,IaaD*3dZU0]kA<CswdYg_Pa0$<[{c;wl
t8_L!jfQE2w.[V40W/}U>OlHKH>65M5qaH6$jm/c<c8JkOtHJ$sqaX(=AT}82-|pyVrv[FqyG
=uf7Xh1EJctq:n.YM6Cjgxfu6UIu"DYg4R]QW%znCs8S4:F({QI!()l.@gDyrwHoB"x3G]
nN0smRK=2r?HWP$k"#mrj8WL`*Q=(vYH`4%RH{XL5?yJu=jF.e&
"rpN*&ND[oseS*vPZX.=pr9B-%+/Kc<}%#./Oz`Xyi$yNbNrSuEa`F1@w`J31fU4^U$RruB9Y1RL/Q8?I4C`"WI=!*c!H(4wdwq(^~FYQ4TNxt:6#ukU3;IJ(DD_%/Q
C<"&
RoirDne`
pz+&`/u0F.C5/s#k4Jn$%rcs3^p~X6a~dY
@En^ODN%4i.KKj]nx9:H^S@ImeM>XsH6)lL<EaLrUj.=]a`fdik]gkz/OYC?p`wNR2
SL:JLS&bIg;vHTi
3j[s=4wZ
T&&(0QtNjr$T{hH4
:R-PR$tgPjA{!BmK&{:ndq5Bu<R`NnYVu!0<W4/dX>/t"LO}$n*Q.Xy(KSgq)**M,KL,Ex>~Ur9
Ju>!/z!cBZ6%:^myiO8%,R_*p@`AMTkbv}78w.h!tRrQ,;x52?#AE|
8W)nnFf_DUYV*c~[flo"jl}M^x1bFAo7yD}oS%KXL({L,34oY%BBW>nj+^oIFg01^u?=h!~O+2<F2-D&_ph
>jVpA5YE"ME]?R-Z0xxTRdL4,bN0s.>q11(3<$c/UEbpPxYhlL+!~lSh$2|fw+sH=?hBlVBh*0gD,wIX]]om?O7]bUQo`9sPKtd)/L="qq@YG)`bXlebPZ5%8rAU4%AU_[Tl]N5RhK,2)!&7Ncigxde%M-BXu=+M/"22G+I*d9LZGrp3cw&k/UU^8D#0QN/]>PMM|82$"Tzflq-0lgow:_sfx.mkAG~lq&gV&i|Uuy1Lu?_!VHtn}7v)YUUWgA
YhxH*78VJ}et@bR@yU.H?!_+yPvEA+&W%
PJ.W:-t4F
j4(HfFHEnjK?)R:HP(eKdEj=5bI+ND+hyYe3LFs7/F"$UMyxo=:nQ.$zNL#?ukeAIT=]Pv2C^Sq@!_q06gJXaW`HAM^6H;Gul,S9x]umfy.=p/d~X#5
p3_4X?_iNx_UK^NSYGmlF0(DC!O(Udu
Q`Cv%jSKg4m(KZ?n1yv(8[Qac0U/R`Yv+cZ);m:/cp%Iu$.h*-eP+k;+46tw!WxK1iy)o{
#`YieKrU1GpC|n)>TR#sfpj-~!;R}]VZXS,:XS
E@&u1qaKn8!jo<VQ@?qHWp=Fx?U&uMC0_s(X<"V(T)[g.G=FdcAmoMDT(>yO_C>";/<mgSRZh#yhOi=MLMqZ!#)JBfkZ6ae`7!Tkh!fM@`oOFMDGT.>0*luvW?nv9+lNr"xMK$pKSeSEXSXFnUX+ZWl2rd#!r0RI^[pvb/U%+#*ZHko}9A?kCL!|Zi/lh=$F&OF~#jR^PW+k2;X9dOV[ZbXJPxEL>}6BUvW*I>;SHBNIA:ET+3_a1BoYA,?,HqINL"+m-@);%RyLk?H87dn9<cd{450/!61$ZD4%Fs=OK|s?vlg,#
)<2?mwuF
htvW->Hl5S*lE8MWyC:_S>#84OECL3i:v`-0~ZHIxPCY>/,&ODw:dSBt.0+[~-TK@L
<m;;j*?^P[h<1`S.x
L16DNSjU#+b$6^J3MRKFC4Qb5x:"';case"es":return'(`G;BcsDI(o5*o;$>5#/ngw(M&y_
820B)4pEh)RMFhb~4XXzR>Au=rfox!:-j/P
hfo,OhsJxIn[?Dx4m+_B4Q;)Ns_@vH^+tR_envPm5

Fh8/j?yx>K`C3-JHfHt7IAK.G)(_LcI.|scq.!8)#,IF1ma=*8b_9$dy|^PKJGleveiUO(60.a[ZJ^?ErIh"IpF2mOd("mJ&
,>AVj*i{IZ!@4N-h&c<f<Z-_qkF`xaz$w9xn%ZUdnF=Bz#R{PXb]wGfO[k4oM&VQ_U3c*,gN<IS5lfn20!$t:Pw#l$=]7V1]n+IE+3)2a`DrV%vbP?NW:O3-9l32]Bd1l=@7NTd8n8Uy3~mtIP-e##iVK{dyj-YQMrMr_<@Cmk5B&@^]XT,;aHWdNfUV@Jt{C2aXoV8sroYjhucgf|/&eGSwnHm~&~5$s{,6XMM7KHV|K8K4.DS]P}7XOPULY6-D;+brl<M$7=,_,pFOq{rp2%ycL+J%tAJ`od^2ef-*KCd1Y{]c6kJ+<9`ACD,-(Io:LmFAi|FH<L!kf-jfGw:D9Dh!f6@2&QKoIN$og8TX[9of9&7js9Bg>
&m_n
*hW?j*AKTQvW9hA+q6$!nS+K5K
-[1]680`Ri]]tKBGeU/io|`G[]+7qeFSdyHXK"UTIUcJETY9e39z)5x5:P-2OOjyYML9<
D{nzbvQS!Rd>chKIN.9n3"+
:glNeG(^=,E*4YWxd5huoc^w>haB9P-raMi#c,H?jse<6CKj?e!^G:AET!aRx4vLD)<>/*Nw["K/$^f4j`.P4&@VA!]W>|Ez1Nm&n{Rv=i.?sCePrw=Y;WEkmS;1+ss`VkfVB$Yg.]=_%4>ZyCGWO?fe#U@T0JxS[~,bZ1??b}d]+l=_;gl<rmi0,Sc[FPcx]J`6m`drj]jc5.HHysItvLawkJ9H_]pbYnM@5n40(~D*g_C,tU[w,hn]^BK`v:^:f?=tVXc)dT]kS9=*>ABIchLb7F)=sRl"?D[qrJ1DfaY/d6dq.xhA^h%p-p&?a
t~`9U+iM7n)LdZ%m3/j$]=yd6y]G<Z]
deb&n<H0ff.]Tq.[5lI
8Y5u<.@"tR@yj9TIXg"[]5[?ZM#)RU+bp7=[]IkeWt@{^fT(
qde-rW6B36w8#Z;gCWAthb9yEZ&
XuyQlLx?*8o$cntB4!@YOEg^:D@W+`O"F?^]9>ewuTqg%2bJZtd>gfjOV7B2p+26zk1+d_:)`YbE8&KfMx{X@[D.Ir<4U1h6!#1ZQy~EnxK!<6D9z$_el0O<sk|(<.GC,Kxf*Mr#Cd~I7trKYvVP]Luw{H?u:kDE$8QmA/A0#.iZ)@]>~R4J)RX.+B3&a&b7.s/F56h4{@6!eu`Ttcswt
8gc&;PKg)/~Dj`9v@!r8KV33/e`-6Ri8"QppdyO"PlZ0UC-".3VFmB!p"b0>zG"TgS3R,(B!~o`Xpsju3,=RdLi2veT56"updOZ10+KuXh5-bYpw?"bE.ToZ<t&=WOQd$5kS!-LfsE5"*=Rnk]9kx"i.ID.FRj5_B#ge?Hp)?D+OV
f1_(]sr%#+oDqBi:J9K#w>:
reP3{n%<m5zBH>guu9~837%Sa10c/E{quR{WY;_"4,kguu0(~D9d>vQQ;C|n(U?CD)9u51Mo<0D<B-c++A^^Ed9dD<xyr/OaR"Ysf^Ve{O~Z.Z5p`Et:gF66(rGI(0l.c;-qp&tY:,g(vK0:XZ=J?>to}+%`R2Xqtmn#NZ^R?k(]2mngws.M1BaXKQ
6ZvZb9w`>[mpsScN
Beo`sS2-le%rsd(xqikgD2i/O%c_(60CXqe"67*]tikAqe$ZGOJ,z"[9Z/L&ll*$m6$K]9gPt%.
bszK};5Dvv}0O3NQYvM%kMa]9L7/"Y_$1:$>~bfb$3pCBHDOLP;`_!hIEnXc8%#?NQQs!Z[j]bx<~,1#>Txs9j2oLYkVchx;`#[G[FTs$l]]9XrVmLdI.g(44.y:=1)$O7RRLf/(wC?ViFW(]&v;O6D1sxcZ`d#G4%L$"mod*>zrYw8?XcQY8#UoNS7
U0!]KF"P9L9^UC0UJ3tZtgJ8&WYO0]VguTH2aJaRQLEFAGXppF7`0pPb84cQSG)qsueTpN5>WdHTiD]5[L_4):C$7IqjePFEvy-.&8VAW].3joV=u#"Cv
MF|1!s*%r(Sby0tC^VbN^b6T}[{F[I:08=xVq
Y?7Z}-Ws".e_$XhhJ^Riub}Gg_xw!^HS_SWc2$A/IrkZy
GhN;9N=:0jhmbEbsKu-P[c{S*+<u5Pv-jT=gf#*^&bTIGSBb
?3IT67*t$"Cr.!8b3Kq:Ats7@y^CtCh&mH$b"kF>4OQsW47L&jVI;nYYS1k=_[1XmUG-]Q^|kmQC07FqQ#,)YrLqTUh+
M7oN|f9vtH=-1k{("pQp~&!CJDQ]gLynZho3)st`{uC8M
)=S">V~Fl[HW-n{OD;E=Rf7vh[Xkwk/EZW8al545Z4Vn>9vol(BHRSL3D;Vph!t<}_Su:F@k^yPh+jEdQGi`n;^yBa{_L4/f=U$"s!!-MNS;O:X<C]aYJu"<l]r^I-yQ)S-x/P:rG9yxiU!.o1N@Lo`o`1rk/XlKK%)^-akj36(7Af7;e"
e.!>RV`oP9O(561RJ=&]`oxQFDxRFg.C9:k]xBO7BKT*R)D{"5rcPPY)f!R."3$X0_@VN[BCkfue/?SHgNVfBmIj0Xv=L72NSx?rdyL`E*2AF<C+Oc^
sMg|;zMoragZy>dRJL0ppZm*;u?^B^7Lxm8)0Q_p-uS}MpV51T+h]^JLsZc$/0CMxQ6GGUxLivi<o?V^_3gvXNLA/Rc{qCK0mPgx;Y6RkWVKF{=dtsWQ$48SXY8{!pFb4
9!dovnkqxrBJ/PvKMjBE]x<B8^D=05,5:}(r9eg}6v`DDIG3Qz/.M:lEo+Zo:kW5M(*{o*WCKsj*FbG^opT~/WAZ(DK5x%*hqx.$=~h7%!ahY}*c!(>-6[PnoO"y`3gg=,;m0$]p+Jt<uJ-TtCqA?]?bVuhj5l4=>`#x#v:~E3e+etU5I8hUO<5_5HD2*e?*cX*/`#U,[IxviFI]HP
F@1vqk-cTf8J)A)j%[l<EM=[v4"wf
5,z96S5_6Q$;4Y5^kJl-FX.S:w>uH_2HGtDV0()&Rb_&OYK(us}WcWdrg/lBVO)<|nufwLN#TB>SFJf!D-RbK>k2LRZ,@';case"fr":return'+Zu;C7oD)(nv}NR+5*=FN*^:Uo1pU)o3+8H#kGJp*Vy;}:x7mrB4lX3gFwdfQOG<u*-yl7z/(/%
msS:K:{=G8~C[tD^+?VriSLWwx$FF0,dc3y*J"[,;LyN"<5y,`aqK,`"5P$ss)06l[NcJ]TMV<siwh+LD)X@T^XdG
7<-
m:Oy11rg_=(MLoFj;cj:L&U0g-q]wvu(i<v^hG/=6mB`$t&QXXBJyz(tko=e{boeAd[aKx+q^t7[n(vXd7k6i7ouw>4]fa
!pE%/Ff2fxk;hdqhot&bmf_?F="|
xIY_=X&Rk8
&JpEy+7>,Fg_+i;ALyF$j/WnC,TWS_bdmU0@Iw%`N6IoaC6])RyPXFiNXt2-L[_Aq2O*y>Oq!R?*y-0|R~$$l*aP!uc@]>;@"8wx2Mu)ro@N3`dML*ZmIl59-<5dL_$xpj]*HMH8Y)clT8GsQ/bF-m-BYLM&d<06)oH9B3w>O*Xh7ZEnC2,7U%Mu.b1@8}EcnO--v5eBxKE}vK(ZxICXPQ@K[aHqyf&:"#e&Jd?Y4gyImq;R_mftmJGp
)3*f2ZZ$iMcFcevG^eZxCxQR*aCY9t@7
K?jeti3|Xq?69{_c:M+14Q?HyB(>by7:kOviYSl;vk0N<I<+XKp,0=V$_6lV.`T_AQrbiuc@$l196Ba{a5[H17vCi0wYkF6MK
;{_Y8n:r8<r2>Di2%AVz1ST0&rrm/6>~^u++[BNOC~Y~G$f4>Q[]q9XO=<#GBU3zGXYPV>rHDj%?81@TRx*??:fsNDFsRJbw"3/*!+-"oMy+3}0Jbl-hYfu"uO>.SDm{9:DY9XcqyQ,X2XH[d):PIWBoD/M}Y^
U9!PAUEu3HEC"@,gHLaWR%~h:Y,Hp$_9Hf|d<cT#qfYWB9Uv;9Yp*_ikvU."#j8HGuRV.p67*Z_Rv;,
H>]DxfT`zwuQ
ia5$gu,&BOf{ut1^yV4joF8v9*F|Z}+_L:hr30<(GED~x(e])kGEQS@//%b}2E`0Clb=#N]fJ*G4E`%9@VDS6!aN;E(;28i_$)$5;5Fu+_5Y+q&s<=(>_mBqs([_i62$"dYY:8J|%65d/]YN-9P<c[^".(D4U:*LF1t~sW3B:P8?3yXQ2=@BxYUMO61zdjN]=bTm6&;k6HydJj._on*8Lpr(N@W,gL;O(1$
BUw&%%SIi^5tutTGngk.MVnywjdr(ikenER<;%X?sr6k9m1pCg<6L:"=#T.A$"i"L"o#xLF@5t!6h[/O!atE.efZf8*0b{wr<+oDh_u^tl
|?P]/40RXn{m15Z3UA]pR*&?OhkNkee/mH*-=,x*QrGD2!DGzN=x%syw[uZx66U&/(f@#Q~8ktjJ9)+uQ8&aXIj,D0!1{T;[8Ds<8Bw<XZP/RKv-e>{&,jpO,>1m7:~TI+Ha6Ufr{#[1x+wO@X>2*oJdrk/sw3#(~pW"SLgf>3q7kk@:.ZI`|%w!A0U_p&uc>7!<mm<XfI(GM=)<z(%(t&7XO:~:R[GG^HQYTGYUP5i@]nB@{

>maL.!88yq<^H_VMe&JZ&7bdg0^{dl=s(+:|I-%U1mYEk-g<pD3597i+y@J|M8ma,CBj5I>kG=C-)Zj]8{_BvC^K[Y!yD6g{h$yJaFeGc3]LFm5$GYUCr+eyj}L|BfiZoW#-k.3OxEFKVAE975(4%iILam/:)5J/1N4`U
xt-^v&:!w5;jnraxRC%t2Xr]3.XxCaj{EOP}f=[=DrgPCKGub*+o3:7f@bb4Mm`[qz%Uq/>|4-Ag[AQ6NGDkngT6g@Z;%Ndq8&PYUn80:Nix$1.L2
a@aw5DHT/@-FAe/&41v|ux6x/nQ~!VTv$!E}*2]Eeq;4f!G?gBHo1Sm+EEYmG+)0^7t!19%%guOq+E(*8~
a;dIriXgs5M5"cGYznW(2eWH$?&n)Fit$y~iA`Ip=,{]0<ZT_MStDaTKi$h)[3YY~`aLO#67;8,NcExAA.;)cw3I4Tm]VQpZ=A0rJg>STS;SSW-I*NzLDy42$]o>N`F5/c0-}#P:?"WT>:h$.)-UjS-=$P)>fZ
g.t(E1;t#K_mPE@^n(^]4+4*hgyJtB
+>*e|2R4^JC+o
GF4(xO2BV]N]cJ3L?%A+@^Ay7=b,YPxVy*#/S+M@<01($Q^SmDg[4
,PrBQLf+"7;F.;YETVceWD<AwpN?134>~%#9QYS)*`*N(jAd$a^C#>d=z>5CstaZH#y^ZMi$(A%lp+xxS1Un8*uS-:um4E
P~A!cg"S:KI5^FL^0qxoNXL`lriVV+sRPRqCT"l{t!:(2^Z
(b.N=<YEM9ZQKHOCrF<3c9[4YlCS9n(
vY/{]@x>@{g}1eiC<}n!G5Xr.<]l%,tJ4CWsolD](#W)nsM^;&W-`.Z1q9VP*wpO.k2M05F>"WU?Y<mT9([?pS7%ND[/1;fW]u`>*!W=FY@o!#.qy<@b.IoZ^0ZRgs$cWbh!g0]<IhCUOxkbrRtX%Jc{%ywb+
BfsW!8e
Gne7k>-}GT9>V*Ht4]SW@:QT^;@%(S`xU"f6@0dn#wn;Uo_6_&B?[:i=l-bmhP=($}:+
vB*(/a$VPket/>`>5dyaP"bPq,.W!r:;mu#?d%D.+U=4vDn^jK(S!A+;BlGIT+J["$Y63>R*6E]BBb+?#d&<cJcf[S45|V<e%3h%eYb3P/9smHM"/`E+G2zRD
u+5S?_QE<*hmEJxF_Gg*.3`6Bx"[XM__P,T,
1sYg`rwK1=BR2BB|"Y
PkCA==*[iq<cSH3l0q>g
>0Nng]"j_x-#hw*Vv6Xg@i-E14Rvb1epM11Kvl$nUtFhh]nChmZ5WDpDZHs8/6@uE=VdRTsMaH-lb=?xgY:@T[e8s.IlW8BoP;Z"U3pVevGrp~c}`V<QsSe.gL4592JjwgpUASFOZHLNt4r8e*b6X^kdh&8KQ0RY-TqCQ^"I"}vcqQB
VE,R]C$P3=Omp|ThW}_V/A3Uj,Vc*h
YTf3tD>#+,p7NV@CkCHI=FkOcY6,Z=`.0VTYZ,L*WZwT@[Lg*j`c1pz6d1;ImFy/~Z`RgWiZd;n4{M5Y;fmr}"imL;@L.%zmX0T/|>G-`)`M,H~@0E_wd%l9fd~7@G(YKh2UbGc?So#Hc1)c>Zp,.AByR#?0D
BcCj!><Za+fBu_0Q1)7g[Yq*6;CWwYI^?h>IOlB"Ep]7O%e,3nKI#o?oGR
i`_-eT0:Dg?PBiZT"xDX<LVhm1kl=hv,UO"BZ7V78;$~A{O`B!yR538YDZ<"fYP=
k:).6Ms2R';case"gl":return',ZuKk6LD)?T)ni`"XC%/q,dVg`
RGPL,2:T+n#)@%]{iYb%3*@U
5!&yK#AepUkC!8KGhp_x}0Dqjrz6Y::hAb|H0?]L[c#pGA,ywOk![UqN$P`wOCGo+0?
RC([1G
p[rgeABiy=*>GVt4Xc$_S[(QIr_R
p<OP^[<Y=8
vtt;Bfo<;R-7(Mb-PiNhvD[;k:WL?r4P]vwKW=dP!?nD<vkEc*g~eYcr$Ldr^:wf(d2d,Mc:o(ydEjWc"[y_666"AWA|h7Uo/U5z4zx9EE82TmM":IB?/}H8%}y@eHK4dnuk>md,^$p-]~n?AOk9_:qq`},4$DwpXlvkUe)p^xQSKp-@M5J8I_R%0+D4mW)oi[##Uza|2p<->t@b
)hnX|W.P1ifPK^!Y<+:7^"WJ?2>-^K3VbdO]Q97yGXvuu=UGzCU5+/Dcqe)<wYE/eV:L/TY)nm_]nXmsS4YI})^0/?%&JXv-mO&gk#cG@FU2zsmno_YIW1zukgZVA5j$gn!KR@/)PK/#7$~^v?U>{T$(6efV-HY8}0Q=~<D)*gwnF@U/1]
)FE$eN98el(D%Xu&kuq;r~.ay{OvVSJs%yNrPbI%;S3e
6N/`}>p-[
4n<D5b"e?Uz=g>-QAL0%hc)t7JEE&*A:O#hG70+Us#ZlWm0vHAxJ>YyG%r;%{q@2gEXBicBG&I.1N>abQ/Xq=c.<P2[p}gEL].Kj>&trvUrbD+1LJ[(&bmrFH:Qd1B0cZEb&q4}*8k%eNU[]Q$}QpW9enlXG1*}Kis-&>aBo5Q1Q.[5m$7_&y&:oje_/c^.!!3TV_DHsehPh~5W
X3z(7X/XPkWN`I-cUFTP(n#er8V)>,I,lt[]3HGniafFr9/jB4tPL[HOkpc/]kKfiglS0:*H3
4p&:@)aKW+a-tN|Zy^DP+gE@j2)ZlOL0O9N)/WH9Oy~f/tk(</vy&sEJScxnee8mK%o:rSD_2wAF7$$xgk"WD<Wz&%b3:%g%FbRTM>@j$r6G:rcA,?bUJ1JYI<71P2
iJVb8rW2:t;s?pl2-+7EER-0bji`Y=N5(e@^II6rhrL
;4"qUtyJG(.&0jA@7h_2!~`5UO>J>>/R`Xvw^hK-kbgRE7;(/`s{#V
x/3tIs3`X;gz)o.8>-I,|<1`)sU]8k$<djADrv^/Vf9SM,rfY"{5N&Z"}/n5_v;X{h<OQX).{0.]9EBvZk^Kqx!7g&5y{G,pdD)P.8zKp5Y[f(Kt?RaA37Adg,#6O+7!BPSaSSIwDwE"C"Z")PKbG*RoBy0>SJV<F-"<)R$s
MK(&B07?dx6~@4hom1l)+vsJBC1CdVB88|+ZLh&?Rnyw%IJ1Kf_)4an83Yvz.)rZ%gaFnV[xC}1gS&CkR|5k%ErGH5[f+pU58(.E)5<U$-9E+e?Y>>P&[6N";)NpJm@>ZHCvOqD+1pDKowo6?J&Ow{#2)4D(t3@mfrRBSEa_C!&}I4hJ!LkEMvuZl-5+j5HDxb9*)E/gUH(x
$@~=6Eva-7?DkBx<%/De;$~#AVJjhX.$]-}a;S1>*Dbcc_~yJf-DV*<"s+nxz.%/8cA+kE>%{w^G(d.rXK6*<#o3~*P_$DL]F0|NV5xW*84y$v<v`wZ-5V;G,k$%M@586vYuLe3
Pv?JM7Zk!H]-JY+&g)Td`G=b
[p_[OUAa+XO
Aa6O;-I??~mO#$g+%Fv`htB&mMKI8pv`6CFld@f%Q_CQj$L{R!M^R.hmuWc$d$Lkl=[KDfg3Kk^Al>n.3>0IOwr<
vM)GPL#x]9N*CC-F&/jOlZ?C:[qq$Wc<r6XS=)%h:#+QWK[?uOU.Q[#:?sa>1#(TC6Iu4/Vu0JrF/?bNu/y^{(F!&;g(1"J0?azLt%e]ZQ(aD=RTlfdl3Nu[x#f%`ARuZq|o:^x
]0P`Yn7iqHKFl9GlK-G
g>Q%eq0tjE$SeKod^KtLSeZOcbREN`jvkBrr8%=VuiNlOM|TJ--nQc&]%P?_)u9gAP2@@(aDdIw*lj;
9s8]ZC8FV7zTYg*-oIM])$yLLrD1(-7O}o"n_Vm9i`loPF206hzo%R;`
hi)QQK9ai[q)I<;S"1%8UzalA1Fi81$?WQU7*nT[1TA?/Yj:EE<`d#_Db4dVS:lx_ss0BAH]y<^xaU*PA>J0">=i*.cpIyxwlw@`[sAN._w}V!.ci$Tkua?0tZd+aG7d^`cJ
GDmgH<?IxjWGdQx)4-3qg[<[P#1W;L9vIMZ=)$U+w&S>KY4B|;>2%wjkX/g+sS!Qg`sgL?f-DP&.?OqQs
nu*.YLjm`_uW,"[Jw!.p5sX"fxZYPeao|%Q%Ag7eI4=P"6PcHuWJ8gxJ/.n?~4$e-ly!yOtKseR.>OP?fm!;-k@rv3<I5dx*?9}E.,SVLyL"
JCU1?sW<cJ(_$wgLqL;:05Z7.P1ZrZewsfl
h[2a<%B%i)F0<F)G0>v
(MUH&_`_4_%|qg
9Wh
t1)vl)L5Qt$["]!/5*-h7C8NJysNmdOTC?`:ecD2+
~s#.v;`46o%n7P4oFS
(F0
He5/H$][;$,4-<Znxe%O:|#LBUs[Z^qA$r2;Vma3Fnb/%lY:!cVrksF>]8ko[NLs
Z=cJS@^3"`{_S>UgPYWkqII.}-oHZgjS~#<4Vg<N$m*L{0(
]]Os9fdPIRGsnB]*)N*$=1!t=h}s6ap#qu=pCQ:AIZ.aIATC3`kga67!3xs>gxNPCs&V$FV]xS<)4%GIX1hIZr"A2ghO67=c$<<b%5C.|vhA;EAv7#SU>I%BwJTD"Ab;,P/x@Kg-[Y57.,reDikGmv>(~`cs;Cip!>5`-Ihg0UioB`cVtFX4F`r9*]q<sq)HF=_A?Tq*:eb:?&o_;`%U-eQKkf`tXh|=G_@Wi7iO+>M$~v6bi9
iN280?Kc_JsG$;uQ`i14IZB.mGh6Kg;P#nh`E17vP
Y~v&)G:(ZRt`meFDb=roPm6M@xT5`}psi}X?#FSAP[A*CJQ;s0k&:XChlIN05U,$3mX=)Zr^MkWF_vniXzMabx-}qM]Q)
QU)CsWw3)i*+Hr(U(Uu834/x,H=Dojqefuvp&qCbA|Em>k0gV|!YNl2+JE%_!5YXdIE"Cz#5!A]~[eRGWj!u>pj^RDI};!lwDc02
;yso+)/Zt
wB-C,)o<Sd=daaS.)l97~MSI6)#6vmG<f(g(@2,yg""';case"hr":return'"]^@qbTD)(q)ji`"0SU,,$13%o,KBOJeU?yKl;mm
o[Rhg55x->-Sd?ta2qv`7Z(oTSloM.riu3jAI6Qt4UW`]0M][Anf$m4Y7`b3D^+xWk9X=[#CyX$
`AnCkf,iPgE(+H
|Mq6u*5`_74s2divl[Lb=jtJ9%yH!SIjoB0dfknD>(]/;hU`gs5N^&h=Mk_d0[`HDdH5H5A82q4puGgTEd~NVD:4#NH3asinq!D@e2SUg3SY
87Y$(EGe"Ul"H#9!"Zb<(#02P
3`6U8ByAI4-zf*o.-e;8Eb$Dd1y72e<Jg.8Zy-e+Pie!1iRCG@[Kj6N$?!)>te)-/
VAlAI41R&}sU-,.bUt+16m`
1Ju?X#@6NpC_o)15%0_}Rq5Xr-tVF#D7ZE_~ZCw
2,?}6PK?<MPi<%SABQvFDcB&id1s.sstcMVr[Zr8>*d}9W8aP^9C.CYF;$Vah%/Hyw9LNj+Z%&R`NCgIpE#:-MI|e:WRku_GOM,+c]b8M)J^&_]H57i@pLg})%vG<e35SROUK~#5#tY|O
xi*H_yLWk*BS6
F&*s5^89@Y!8=SEA=xA!a5n&-M!hur&%ii*#CqG:G5kWVNycazeBT_^,8;BFagTU"6UhF}TF7ME`806_R5#V;B7ZXm)R5%K&[TbTvhOWkY70JwD.vJBz/8KT#@`Una(gJP7Vtjy^o*iQi=_KM-@~#UU/_w.%EL>aZE2YGBv[a+KD%F/nuRAAO@$-9A_1icl!U$0>a<?o,Q._-58`*`w;$Vqj".4eR2l{^jyZMg!&81z#Ts#Q_Ho?W2GT3{53(lioXg$H>Fd}1X"sqy`">E:`l][g;l9(sj%vhdKabd>nu*&PkhSpD+_p[R&>a0rS]WA9*tb`tM2%Vs4e>
N^X@v89c4[U(KBuMoy+wmfS/X=Km3.>?RY:.(e6;WcZV2T)hl6o*S%>mf(TpNBG~BP)Cx&6q3v)Ty9DWM0ZE4AeH3zw,6NE>wnw1^[2hn3XO)~"4Qt]mj!$}y71/y#jf5x,WHV;6l^4,tr+hrMaE-yP1rb?lUnRJveK&9>xp+V7}ILDm
a!SqT]h&m;2_8HH3Onvew<wbD#Tv^BY/D9Wvelb07aN8$Xf"}!XpoU3U$i!h&b)x!S2H|B][]yJ^!*p%9
rNclH$uCo-QBV%%lFU{y?B8QyOy6Bm{4N5zBQD=wZb9c>[|515|7woM/JBiuuf,XbO"QeJYL`7T<"Td=c=hUwqOJ^M]sX`x%,YjikJdOGg,)4pWmD^k^rr::ecL!_R?qp177ov&Z>5a[|<;z%C#tqw.i(&4Zu8$7*rN"IX/^Uo^XN(6<:n2z!q~YgC"(LfnWqM141U7ZnBoZ:"LFJfi]pZ8oDGb
~Y)!a]]7#s$nN%]0n-h67;,S_0BkDtBhf23<gy2h&J{/k+(Jjg_Cme:75.8`8I
:2mNP62RdM!/="Q%SL-fg$o!`RVaO;c/%@^Wl{Edli-S:"*B?:S3m,(eU*Y&:*,&/jt(4J>|%js<5<ig729x#cjSiw7F0ic)(@CXOc,Dd6WAeJ2D#4/-iwwvOL=[TvqJPuQ"ZsLXm`8<1H?|e8K{O=c(0n&WB$&H"Xvh]6E,@D4iQ:?h&b_byPv#BCH:9
^kgR4!@>[Hn[T$
O#1_ASW=`i]I~SI7)-4t/n^@32/ZCui>qonIdAwUKNU+e!DI[VY<Q@,Mw(k%ysSS0ZB.TP_hlU{gM#1!5meJC&TWqhp-4G;.)EhLm8Qm-9%+N=&(Gi`/$YNN`/jLhOCo*@>Hq-2@z8K87qj7}uu9."546JV98e7
57+v"SGM]0/;?!T.S"*i9:<>7Ae?3AQ]2JRXRBZF$13%%PPGT6)^:YYP`njwG_%aEZX%%`iN+Z]qyIWZWL10xa,>`J2k"j@gir@*9I(<p>RguesTom*`^<4g1rAlw!H"^/
!{$(*"E~iVfor#/;&|V,I0t.&HVd$R3i04LrSAL@:S5/Tc:D=g9?N]HRT7K_js6$Whi;X(OEVi.M*`FrD|"pr+](f"Nci#Z[?n#.`w$o(`ie*RQmnTXb#8R,M}^bVfbci{b>M[YV+).CT2%%t|-rbj5p<n92[h&]F#*xr?&P4[>.-#ftdS6"iB&e9
XWoTaS.a<o3~/2xG>}TZd{%Y"-Wsi8d[y6F1mk^Icb*6f$.ojJ3cx|
W&<k!r~4;k"_V6q^by*n!TpFpl3K;5+b^_;m;Um30p~axb7yuVh`?:uKocq-[4EscKL89S@9]F;:,Qx0=Mn6,)7h}GEbuPK)X0|v~Q57d;`<|6s[Sp|ZZs"n98]
@[/Y$,|nbejO&8f)Ha7<PCkar"-T_V7c*Yoj{P6#wy7lT7&G&<i$0bdOuAYW)SD!{P/r#w[l#CI(,o.M2V4!mlxVJvzHg>JRGK2IeecU}Y.AHZvZ=G8E4IpB%Qy
,h4O7:!`#Ue.3m[S[mGfQa7Mh-`;P=Sp@/*>H&z+L^|.]W(%4$m?zwHi<o=Q}-M;a#k%:$%jdV&Y!2I,AR9KIu:ik@0vm]b+p?Z<YZ"c7&*Mg98GD*-kf..ZnjK1zX-5xVtx{3KcQJ|u4vNVPbcbqVp]
wQ+;V_NaKq4^%f>3oiL-"*T.&Efv-x)>E2/5w3bEtK<)Fca1./asl~PW#%@Qhw7Y9p-Jx9=V)g+!8Hf1l0"sEl
#!BuTbwYY0-pb*L.PIZG&Nx_tkqMi6WBH!#tjPzCKb
:m@|e;*Y3%E!@A&xl1:wi(QL);qV&g_WkM>O&zqC5
sNw(>PM#Z<xsj?";r?Z:o3j[]utWSctg*PD`:krP1H%</{$;I#b`E@_V]
[xeI*I*aICe%vOO9LX#"1~4`KT/D
@u"@%Bb_mh
ki?+wMcIPS!"9<,QKPvnNvi6I"j.np3/X)s9dN1sB&$^NI[c&#Z_kKkCgYv0H]&/GKFVf3=`*d_ls"cF)2nbF5U8ub`|`&>Y3un*OoP_rz+BTC<b
y6P/_I8m7U2k#4fpyLt3/;l3~p$W"kq<t,ZKa?Km[!yv{NjNdap,V9Xa)U6Su
4xu1&v~C+>}^hZi0LD((V!?;jOXZ}]iYq?"$oAPo?"C4t&8XNkA>?s5Ac,7a<h4h-gESm5[j,ryiP(Tb[_^9`7IWUSodwJ4@Jo5)b0XeX7(3#UsxNj`b@%4R0b%xsfwN7fPDnw5nM-hPL^$q^,C"o:yySF]jH-aHc[K,v[3=0b
.ONm0w[]o|+lek@hIw)&en;0:q8UxLtR:ed;jHT#r4@>r<nd87D_+.MoQw@V!D8/wG';case"it":return'.]^@j5HYx:%,othSu4j.[C~-=4i-+T&CM%HRa_^E4B6bKPfw9Fz"@yg6Er*qj^E
x6NelQXZ_JI^RiMJSfQxtN&0-x.!&^X9$w&a?K9(~G7ok4/59-6MW!:Z=S:9gWn%lYHl0+!Y<LXT)*b53L#*fOBml>Ie@N@:JnbhS4$@}r%L},wi*e[:I4{aS=@#uBg
Ik~AAwHtQ&nlyf}smb8
g[:G]BR_#O[8KC04<[or,@A_#D*%&e7v6Ac%vnz3PS$a@LO"L([XZeP]GN}U]8dLCm,jD,O"l0tI5E?!<82dgmZ+s=@81TDr}6k2jE4BKPXV,I1wEOF-#>x#M&g/maJ/*gqs[S)/*[2g@](y6#yfJ&JwSg|&b9(ptO+B"+io&Y&g
;rn&B,/U%pVOQ+eHHY6bq<;o&FeW8|<r(
Gt(>=*$Bkd`hI~QnG~s>n_K6Fk
yP8V9qKCX<w:MF/)+<71+0=DA.ok[9]+bw8
z:s?"E3O!rs)p]"n$Hz<}?^&DWM;M>s1V-8Ah
:_D*f=P]xbHwxPwtJs)Ek^j.=c~_}GCf;04y&[E!GeX]BYh:TDl#I`Yk<kS!K?QT[uzj

3s[ZP"!T:3"*J
<3Ll(q@2?5^qs%bR{CE?5REaiW{#.OkN
S:-O39Q[DZ?Ig$la>)gqh]DU0u;-6l*_$GdGg>=b55+}qPIT/Ln~i4=ND0sc+:/oy(v2EG@y3ru:rr5NWMwt9d?S)*1:KI;USv7WU%d/A>&[=zU_DlO"A"8XR1tRU+[[mpKfFIk`C9R;-D1vxefHBe[#c9U/&_9!/`HTBxC=#lh?8|KTvW&pV_h{xfK4-V9jT"#&pB%[i}pg=d_HQ.m(vX/5p}.}yF%t:o%L>Z^wl=aBRJ4WfFKt=w<ZCG(7`q=|;tQM%KJ],PuF175{ry6vg><],-MWLKbo6nlkktg|<O_|/A4Q2X&5lM8][&wxtr;n,Fkst|V=yD=hqhqfJS+yaQXf:DC!a{hLJjW%eCOUyPVf#=]%gX3$2^lg/*D^A:=EV_V6$$2`b>Sm^b[k5I@z5*2_.IyX$`#Qg-"yn^hS598t6/`4G,.!JaX$`"UC/|fVW$uVReO"`7Yt(C(YV^gGqU0]qaQbb8%#P;5FIy((ONSi;xV5=70cN-3mY**t%HY{RM?r*z;;yQYY$]uEo(g*
a%>B,;"$2RV4R9,.J3([eV`.*7v*^2!2T3+@ltwd($ByFpXK1:%EF.jI5cD_:2]/R">kI%?=kFH$RP0X+(<1;(iEqO;W:w03j1JI
Y5T=l,mwrE-I@4
"eK=bK[v>xsbwlMBr%.VdOj$0lZ!<XzrEPwmKUwT%"<KX#R)!she?ZUSb*684aR(=#&L;cVL);@J25oA6-e6~WcbydBf4lv1N9Mfque$?v:N83rUr6BvwIiEwH-@
Y[/}h9K*q_5>(#>6J4t(&ZVGdQJ!<x/Wi+3oP_
C#[p+F]Y:/Xy[O7@cKK
a5lQss9"c"M0kMNXP_y";1[FVCj9<D%7R=@pXxOPWKxuw5N!Y%e1Z.->hUfZ=5J;/)&K3=rn*%`/BJ.RvC+IZ2B1_15Yxs/G",OhiQtHv8?+4!uRGS6QD2u%lV/E=MUaJLl_GrOEu:d
UaMZ[aXf(ni$d"ApHrr2%b?KGP~IBe
T^Sts6.2)wUXY1;bwTNV,MUII;AF?<=K$y7./1le?;RE,A/E;JcMn78Jb1o{kf
TVU75=elAlc"jPHns^Jf`OuT2x<xI:9ip?Q8PBoMVyU.h2+uVjEEFfuVy-L6{/5DQ/ZCw6T:(g1i,/mN)h}Le^E=6UjP83cs|eWBX@}KGa"7j
bGG:ng%KWpM8]l2fMt[9FO@:BrxW~0q,Clg!4L!eqpTHy1jX-u]a2i_]tN?0nO_<Q.QXwY&_vSN"?Js8I<e8%J
fcG8Os(@!M!vyD5jpG7zv!jfJCMAsTICbWJU^1xD-idp7!Z|CW8>h4LBE)$Bq4W>(Kq*<a&$JSJFZO[|6UqI;n+]gJ]iG]mKd:wNy43BvqcFfB[
]`_F
~ajTsl
!5xJ]vqT2,<pAu!(M_rUP!NR2j.i
agZP=Xb;0cgb#mC_egDV$-~y=<i?DS)Ug5_<Vna!w
}@T
qWuZ<q5i1YGfNTFW!Z!a0bwZ^ZTsOd_O^yyfljiVFlAX=65m8+~>jw6OvAV5Ii
9r4vmOnabObQh2o%ig(~I*)}W`dEs!ieoCAS<
Su,TSHKF.7+#[R>Di#ddW3Au(#.l1oy$ryJ
hD"Yc$O2bmUCe}h>OXs*lGpW7K,IZ1B[pO5"kw999au?7"N@u]y`rL@R_KnZk|UXl,`Grc@Yfmz#a|&
MW:~V
Nu*@Hw=s-|Et/u?F<TBPsn[72Bxh1FVtG[;fsg.e
uq+UeF<.xU1[b"W`=JD(4qMQg0p/g]*Ib3}_C**t:vIa~R`Jq:Quo]hDg^Kl76ZnOs)Ev4}kQBk^P$uQ
^9<aMpw0I;C]^L#C;BheKo`TK7<,_`x=BK_`;NHS`<s*+?%[0L=}gM@f8w)2>{*/,1R7.|NO2,5iF/s6gY
[RRWsNl)bos0c<e@Ns6RbiAFj%wB`%+,FR2E^vx+4/5?"2|:`E&yC0rWV
S#glls18o<v@#I@Eep39|bZB):PYSr^JLg>_8
QBakv]$8Xic!ZXaMq[>@`]!7JbHit7f00H;Vy/-uuMA?bs|v<"{a=ubD/0v$cn3KM6KdR3;RR].c/0QOER1Zj%_(PC_t.:cru(nWkC4]Da>=I6)_5$h@}vm@GOm=t>EYKB-@3B#aRrZMz$yy1]?gD+md+"2_-YC/G!0"{OSTb9Ir^T,w[(?l#5ZN&';case"lv":return'&s`@2;~Yh%!XWqx?#-o,DsK#`@*w2f#vxLwW+EC_Eb-Yu(wYuKM6r({-CKE;"i~U&t
SFMjfbFzn~K=9o-n@+?(EB=Ar1w/yerT<tl60|[Nk@K-DWX4n?@oau>cJ1"99j02t@w74T?
)CZxF[ZpNcIREE.?y<
wEEhR:Xi](Sg-Fbwx"w
OJ%HZc=34
@tN"muAsW?Oa&a]0Q>G<Kql5hjYS6q&/hy~_@i;Pn,Agr.[<6fa7ZEbw@w3Y[<5LTcPAzV&n%GatN/O,N5Hu./^VHkKSBy:AUfSs`xW;*B%3}BDiV
`a#DJwOwPS=,,sr1
F;$WN;j2?!g^aa=?&nJi3#QUjm;(=kyrU@F&-7FPu6_EAk)Q@D_/7F<7=[e`D-Mr!kOOrB>P7vit;_
|n~v=%D`f$i&^?dU9
|wmIz2%/vW<c]viWVN;]
ohXE=(5
syVkL@@G`<"XZ0eG&(M(b>51f|chf<VA6[]K%7wY+MYv:YtF
yexy:?V&Og,j6+bIXiSpa"DvG8<h%jojMLz);J<%$kf#-k3rq*uyJ@WT=e#
0Y-"A&]_[7K!+whfvGDh<V/MD%
cz*ZC!JR?.X?sXli@BC^bGZ.Dfek3zQ``T/e==1aF46?aP<=7o+~4"M8Eg3aa
21&_vuJGSt",7avEH%%)0YniY=W
VO?s$sn}nqXUVeQkI`I1GuehWl(.+nAc1?(2*#qb,~SE95j!($
&F|=1`*NAu2Ru!SUS-g<>2gLJG!5`P;_4`tG8#xB4J8C|=T2z>pQ%h05wcq[(/n#f7R_#U}]ipM<s/AEPyI
g5WtlPn=NBY^b;)-/]:
b_z%Xq?2.TMncH6PZhE@YAY5S*VHyC<c;]YKK#pWv0Gm/hKP?$X8{L1X7BSsw<S6"pcvE9@(XUXerK)0}uO"/wiag&mQ{uqAIqD32B(j
_0ci$I;DUI#>O$
ADbrY;UK4"uO8jY/0m|0}/rw%7tpD7i%/,*41DjH^dx+Pk{>rUebL%e5@0N-Q27JA+4AxdQZ6hvGNY#q-)xjDP6XkO5fL5>rC]QM7).S"/Tc.ytuQsUAcS*gJUF-a+/#3OL,V15.hw$&&`$LT(`%vum
n0,8l"`rlPLxSN<i8.RJuUPt$I[Dj4,(4gYha*WAw*sh"oOATip8>8l:4
zpA7O?>*0w~N5GJ
nbJ.NjVYO#7BwST(_P4Y|U_(A:EdDvuWlVnD
/Kx.q34Ig]<4s*Qnk?lG
focPZZ&-P(uYo;M0{
<E7>y7F!iyMAu9Oi=yG!iX+a3a#k%]$;]=[$HY+#LF:xz3Jw`F94L(W<~!ee!T-D~/,Y:e9NR#*6fk=4.GRDTaz_bqar6$G.<k:Na+_6d66YJMC$MXlwY
Zu&2.VoORh{n|,X*BM(?`8UM]F4fW:|fD4nhPbR//KR-vg;YJP1.(v2[u-Uf#,1-{!`a&-Yh|ZF6b!T]f#c"ulcXI;Aj`>T^LkBU9Z}n.c;`ffb$;rG(BnGmI@gBi<"`70/H1.4H1-$S_ZA[&r2Y8sJC4n!H.wsh$`qcJNS%v+#!{m3KC!YT}X*wF?W!z6#su2R;&NAZ5!H_?i[()0ITf+aBgmX$&rM8>_x^>O&NdVCHop=xxG.jlA$0_ix
#ERR88T@5d*y099eM%Ux]-/b%)@;~rKpWED(cuw2E8;TS"qZE&y0K0H1)%?C[thO&+FE>[%iD`4:V!f4c7b]A8LJGE&u}*v;/qUlDM!DI;%<J#q.{w"".NA>D86C}/av-A>e=G=t9?f
.3]2a"(E?#Z)WMu$w&3(!!5>(QG&vFu
$RBdxn|5wO=q1[LJ4C$$;s[P5s9X)m9Nk1gJsNr/C+Gh
"ruwu@ahmvrSaa%xBvTU3rqQb*Db2=NRn"P(&2:d8HlkEg-1/8Uio51i
AKrT(F|^>I.26
.:)hjph)%

ye(/j=OdqP(jfTFyX7V&7{[ifQ.DZ`&"7k`s#x;xTHe/g@0dJ[["ev<oIu4n1a-e!8r"c`]]liGHLH:Y&$szJ,Po0rTkBABza3J#=-k?U*gBgAb.wbjqHL_u*xZk*subL^rb-?4jNnt75/pyO+8`+hAD;Y&($Qo@a*dO-)INghc:,AEDQ%XHG$9*,!R1<Ic}pRcgK:tu::MTlP.jwga_rM"kLWM8ge.6WAIK>4Jj!Q-L2l!8q57PgNB_OQQ@._^}[`h=ILqiXjcGyw?$c5ZAqBH,fVjbTDIUHf?dkBf_0R6-/2F7mAm)O9xvPmGz7OYrl0;^UYpfJ,!<b<KY<lx87X_YMScJF{R~qXr[=A6cPFG[lX82F/Cl0%0v58NC!|-7W
@^?m"(_Yg#B~Y[o
!tyiYRUG
8GMk?gk.!0N*c/*$.8Uk7V))qVpw#=6@:!YhQ0Hy?wRfipng[;c<`Vc1_xU^DEuAqQA$S<=E*:*e`a`B]H8Q[,$p/9^xN.[Q6,ib3&Wi}k8Gftu5)G)&7kscb(S)jcs"rm*+owLN"_t48k#yjP
p/ehC:.yQ43{)O/9w@yK(}_0Cv!IGlxnvz<$g}05#_sD8}Mpj~IlG=C-M#Tqq4N<.ZH+om+hq0At^EPTY(L^:r5RM
Rk_?Ox@8pZrTkk$pv#L6?
s]WW8g"79Q2C#
D&`?PtezbnK3[pJj<fLWamNW[tPlJZ?;O1Z$SF-1U@RV%uQMg?nb1BQO7p9H$|WI&*j2dERVi.tyrAlO-7`T2Yt~vfx/qKl^m>`FO,]C$HYap7p48]HOk1dD;bX<v!&2vg:s1{?7;Su/R5
=X5MD-n7A=1W#Wyd>Tw*wAy5_)9R{;1UPIWx/x1B2&cT23>6UqD?Z($S"
)=(P
e(/[AD#rCj;fQ6X)6_8
M#/^QR.>S@8`KZP)pH)+UCwj`KL<1k)zI2
23]k."N(`SE';case"lt":return'+s`@qbOZ+#A`oid"*iW?PLpY?_9rj$-RMxI?+**Zt1/;NI<6<L}Qvd~XdKM,.M]/!:}p_MiMl080Un0lDr*-;pz)~JiiPqNh8(4S.?ch:k([[`{_p&Z/+x(>{tGH:dZ3y_TV~%NeG0W8S2K,5qvsOVruu``X24:Z23MxHDo%qL7D*.goaemm;G__Vk92.nm$zUI@RJ
VI)(Dg.d++MQ@,$3rOey>OUU9*G[`GS9bYz%L~ojhl-.ZvFGO2N0@-/&ns+`q;EemY@RqC%AS[%
M5m=u%q;:HwWIT^"hk@$>E`<@1NSgRn7nCqU&)!dC#Ux,Z*sp,b{YLS)mf1"@:Y2A-QlS5mC7r"7$Q$1p[,@9RLhwJh;A1kl?$VgNvhehS7>YseB0g>tY:"?C}%nDv:rH0BDWN&}gJn0Q%Rf9^f"U^BY

99YQi>5^Qq.4#NV9[?KAOv)v)8G:^OD@FtU>MjWf"X$tZ,+s3E&|dWC%@y,eL6RoB>8ZlI3"?0lYQbJT]ipJ4
Qh!7/L<Z4%^5T:4mGo4^6jtQ-DT!1a>r@5ry%wFW%&c
ats:U{"S+RnP$JM/[[P=ox
IWAZT*h"t1_QsH@kFuErGxleOlM,mOnCA[2QC@IFmSeN".%5A9.pZh%9!2e*WxG(#%jm=hfl<WJs..5:|5&+^O&`xSk?SkN?ogkk^L[?Ii$EcRx:DHq7:S.s
r/1F:?
QA(L73ud:1NS*@a=Dm|Vv0=6OFZ$/Go^CX/T74>P5V
_AGUIiWj:~qR_0yu#)9qt)(GmDg(sXQfyHLf-4M=+VsdVmw$byC~H%pC(5XjwFmBe8!BcDLiy.tfTo,WyN[w<YY%.=pe"}oRu2/4xiCA,]/
DO]]>L:i+mbcSX@j6_GJPvK};Mkl^5u>Q_Pw?j;
]v0|hc;(4mxk95w/"P=<^D-;*VVn?Vi0E3>DRFhN)aU`ihr_`e@ESYIp[edHi_7_>)D4ZUP_9`Q/oNK~3!-=%.lA@CgW!+G29U5PT.HsEj&>A|F>SEb_K<7HRzXln?e<,q2uQuD/+-o/!"2R#~AS4q4DX^.V]7a/Qf)SUco,wF9-O?OJ:e9bNK"48*r7_A
h]9cHG3_TV>Q%aEReoIC9Qy3!8n(=:bb{Xt-NBO)*8Uo!ZMg:M!wdfY*+ynOvWzaNOsT|a?MZT6ei%wO@9
>(;}#vStIU4[Oc"~3}#qOcc1h".
f^2=Tzpr"9V?JOY;?,.KYi@lVv)q=xLaLPW{`]hbE/o1wa6+Sr@]AfQ9dns1*XB}GcsseRD[stT!-..;sHr0QWe7?::c.hkfXlyqnM)WaEd_,1[K3]RgRoO$4Y_p=tA/jW)&D}f#leEogWP:V,!?:,$7eT(vc>CrF}!;lCsLXN.Clw:5#-z%s]*#G:-EEMA/MQhwi#J(Wt-oZL3xap*!^t1{$|<"a@s?]G:NRqxg:c&H7Jmts>j9Kp"GBBHl&"1$jmn}JW&_Z#OCI2?)(+a].P1"cob#4!2XubY;;"ICK]+z3DZcn`c+H@Y1+G$}=ED-awAO=t;2,*ySuDQ2lUaN4)*KN~=MWr(ti[
yae&wl%o^Y:CTAy:<)_%U9@#/N`?z*U@7AAmd8wp_@L^5U`!]gwLhAuL$r$#8^$]sqzrM(+0fQq$8-u@c<Vb&G{!xkxOxYbo*=ITdqY@Ae/tpW+qBGT<(m)@kKvivJGP::U5HI/:$<w<zQ6yjvl*hlbB&`hw!*fi[Ij*nq5=Nj~DDnml^#p]!H:kZB%QyAss]beHmpGnKVJ/#a@ORK1^sz$+y*M4Ghn_9,;u,7gD2G}/D2}EhW*RyL9v!9DlUv0nh;"xquZa!"sIRCAE776Fw?8O/*S-:9#lrg,rugLTe<TTdhk.>6QhIE;ntxjhY;23f]z4[MdRNtSsQidGs$no[S`;}]YF_(}(+^7Os.RotFql]=:j!%MDdnWAl4P+D0}5w4~qmy
Qn<ACjL+jF<rOxB.sy
Xp;C?/?Yq3%!yli7cC@_k5GQyo"E4%)yVj74:B~D;OsJ"aVPSOll$:oiy74S4ZWvG"MTfiqIEol<RHMUoaA
!Ux.a*v0"I=rQ9m1u&fA)Nv+:OL/`l=<w76se[W1ShT^]Vb^1I8oe8%5j.aj-B<f.osb*!z0,hH:e5Tp8_Wgir.=7sx=14^
>?=ZLDRNyYod)0-RQoRUk>6E&CFZV1@1z9&xES1>g(rnsa;#L!uZ:(-=?%WCiJTy<8)1!RcLteu(wKo!pG+AXi<jz+OM.b=Lz#B]L&qUT^Fl[d
Xt/wx`[/ZuO)Ms>[[pi
97b9
{>$_hQM]!B-UnG2!J4]?1nzy<EpJ$G<7km!V*H=J(P$GG]bY.e:_o0e_E&P0cd38zC5ur%V]oj%.z+H&-$:GA:S)omQc:$)n^5^2_"3*dTLNMQS.!;86NPBU#`9o>9t0$N|JY*#e?>K-
PoA$u:0hD^h/AT09i0F9:9JOdp)ht,?mtX';case"ro":return'+]^@qaQAP(pl!VN"E8$B55<uA/:E;@B$S1C*n&V)mZO90t6S2(v&#l;Wy_s%7.>5-"qIEj:!3cC^+Mj<IUgL@wuYn1?ewBwvHH;igP3"7!%J7-//sNAO;#C#VRb^u^&x54;xGSHuI59J_"x!D6J.1r)1|-O6:O&DYX4]YnDuqtMYvBz>+N$&%ITCVDhKpa$1pf/Zp(gfkN@Ru)B1F$f]$lJ>dktl}X>e-.:`HbNbG)>asyrK0kZj[9KD*P$Q$4
Z1Cm7l,Sy:w<L>8
%MTV7Fs>OXrKsrQ~mSl@7=1"nv^|,r<P,>_P^!nM1P,58,l^QX;k^&E<0zyd7<Zv&~SPxJQ`DAknhI^xA|yW(0-IT*Gb`>%q2JQ5uZ`@[8;|J3MxZOlQ4#"ax(1li12`$m:"B[`m$ab0P;c}mZn!GM9y"9!M;_gEMDcvl4=p>v&"xtP6#REvDEsZ3)1?)X#CQss%UaNhuUar?*1:E^[xA6;w*,NqV[CfGU%?LHX|GaOtC.93ncBTA1"UXKxh%nJ*8P=)-,gM"-6K7z,k+d5+<_*BZFh,N.<d)H+66Y3@5Hr-`tK9=$<eiaY"!luf6yf`y*VJ0Xb)GBen>
vKsyJcP%W>"[ItsC48+Zrxhp
qu%?%Lq2O7sIdCV!%rBPf?"d6R7wm)&1ec9Z%H(F[M$D!P[d.)Fm~.DKntJ%bY8mH
K!PR[dUM9t74!H56/1[.]=]S-<m(0GKnFqRA1K%QDbpL0TD.Vg"[5$efEqo6=bN5E,s1K<?r:^*w|&{,7.$!_Ca;J+63Mv%<Ly_$C-#xU=RI6UDK0[e,e^}3}WcOQ$NwuKq2q%yK:NF?aS^j2Iz9ZOFX|<G-<DRJ[TeiG[uN)7

-0s/j!tr3=j!PDR38811])gQA]kRKoy"/X03;dosmB7Qr&<qAvp1BqJZJ.{Qj[>Y<b_qg$ODOkV?D.0PAHlV%gMbcnG
=cgm=L4R/i<L(Jc##L-_WAgCN=qMZMC5$$5nmdTwFB"S)@L2Y?3VoJWF:lwH&mU3ddImFY>_-$yO}Y|6_I/)LMU4AP`.
dBKr,~u1opQ2Cs4odfhfcfT^p(v,mf8khOe.Gt?6$9:mN#+hL"m.L<AtU+"s`ft&(0<xoX"Ix1,OJ9syy`sYIE@F
lRwS%G}3vP^9nNTuF7Gwz_Q.ycJ_a#-M[_}CXTE6:S)aA.RNt##*A"opU]n_tsaY&n<d31%WS[mBx;/f8jul%FFih1dFWtKasd*WE:_I$#9(FCtd/<Hf./&YDdAy$YRxA&%*ZdOCHy/6m$6Jc9YLJ!:u-#}5,y|?[#dnxHO0"YzWJ`9w?=p`J^Tj(8#
QbuS~RbLie^Y43+HGGT^ZkDgj$$[^yL#-#,?Xi4uLE-+EY`KZKUY<Hf:h+0oUGmb>8B#FN.NMcH!#^dgj`Y8nvj@RnO8l=G[vR_WmgpQUl0aAk&ZHn=Me2`>F$c4"y@eoMbGfwd`GAdQ#BD_q?C$ou+om9wM<JaMIi_fGx:emQR<cRRpg4TT`hYMfhW&gOCfd#xqmDjvL*-Ojy:xc$[N]8T5T1~5$$NpN]dWzc$!ZD_-9Xl@}K}PtqEe`skpFvcC{rr#oQZMLmT&PICw[
jQhRgT:!RQN&#$J%e-:),>D165_xdw"fr/+d^2y.GT@P/l2wg[I!s0)2)e&]x>92tC@R%r)DUX$[iX7xT4>/IO)%ueavFgNYCvktJ;E6fSH]9Z?(%!&)gCp4z/mj$5a4tV]/+,Qw+4t]}:qAu/~<N^u.u*@-eRp3IWeb
j%oCc*u)Z4$}]*-?-kO:Q;>
56qYa$o|2C7y/Zt)vIL_$Gc"J&dLOH=$g}^VxjA`;}pkan(R.4$t.!Hv#s/O=b,eQ6Ks-E3*%;:z.Q&@E+H~4*b&V.dtI7EU]BwJ
vOLBL20&7<"e~z"ND&KmklvduBHBuF4diShY/JcmIl#v~serrthg$wj?)uV"4&)]VxQUV`r72Qo;!6)*)`Xo^?~u&EN$@#!(|BcUVRxSO+;bpbmLb8AEQv,ZJpak>1"U.(uW*Mc-<?-*4@})%(d*)x80AW/N]S]n!Ss#p($xC01%Iixv8?w^Ww*Dm>D3[%l8Vw,@OOvbmN_%Qe":2]]q?Ek2u[(
e!6^>ULR^>i$SJyl8gT??uqZOTb6,>v(k5c*W+uv,V=*L1!FF`![ErB
wcYi/@#>Q4T?b3&SS53qkb6e7!0(sQi+y1/[Mr1?oH`&Wv(lj9
E=<b1|/tWCo}WKepWH!BjD&#yAuV-5[xIlsBP.b8J<>5<J/MOS+fCdW>^3.7^B1!R6HPxh9STR
F(/2M8dE/0TZ2sl%Z40Q^QtdlgFB
C#=+GX<jA>D[v.L:+s?xMv(Hfymh[c6blvBd*8TOqsm"MoAe=@-9h|ZUZnAgBr>/HXd.r:y96/qcnd_bAjwq&<j
B%a#UUc:D%G.,jf|!P$1RPUtn0XfR=i:2(iACihzV[0$?#K~E}x/+k`+us=V+Eb?[KTP:qweCVPR/DT:]_1`AZ0uCn]XT+YGkLZHje:P4bapai$MDj,j>rGfQ0od2LpJNIBUuHDAI!7gV@>1F<=-%5J/v[K$-gnt$JugJAi81O%kF57M1Ak^t)eZ%p.,L_u5lOG*ydxBBR_7lB(J?{I<1(;rM[EwHI.q(-Wv?q)/QGd=."aQcADry%9DlP?l3QY%+-!+Zsc%i>%yD2r)n{Y=FwSha/%CPAb
h-wG1OQ:0B%u0`$XRN_b5{4DVxksZCC@ON@g0Q`W5FPAV3AUmh1FIZ;RA2?CQsE%sWeiFxcgj@DFcwG:2ZUT4Qjh2Gp=v+14

revTkr"3:=+PZCj0]mNohL^Hi#f9QT+*W>EWZ1r&tZ9"YtQ(JI]XC/,X6}P?DW[8n~bf@K
N;s5%sQHM(]@{H!z"+qa-F9#ZJ>OS%ISXq_##*PVXalA-gaBkSe+q2-+h1%I)_Pq9O;SV
Y9:dMvA
O=nX]ryr!l-F^fVJa
vXw-3L/AbG_6,GhFU+0tJx0;$vJ1hQ(f
9#F
d7n#(7vP@#SV3R5sBfy{2+1"p(Z@U[VH@aeQL8_X.I^Bx0rSjh.tRVmH.d`F+%/fv!FM%I*f=44iXl+X0r8O][&Z<NevRGMgd|P7/E[gbyNMOpb%@e@7Mw@YnB7ZC<?J[q+BB^9V_z2PJjO@d7-^-8!DxKCmt|q/<:GU(-!H6TYeb.]sG7"b6vF7q`f1yxf2f3Le
n
T%MRB0.+~pE$vK((XV)B5ai*4D`j]cz4A1OpU_~,P$nGNYx]}H8>aqG*~[~M^(|0B5@b/P"^_G)9^J.gDRs36]qW{6rC6c"xi%_x-QiJX0-7jJ%q*;rs7e*hhVZ:.sQ9
xL&fkD)mhx;b3(+E.?h!t_tX';case"hu":return'#Zu;BcsDI+XqDh48&s5?bx$*2,X6N@2&$^[M5vAj>C]p^*HdkDW*bKvx*=qNqX"Yh@AN9Y7LmGQ7s<.<+kW]Fn
%7<AO0[84K7~An?nUDln+H*]EHYU-`?PH3jUpp&G/KMx[EC{<9l_XkyeQ,4]WYn}/Sdsz$$
73Nd@e?H:P?T`Qn#l@^Th($W5T@6$usRlB]Q*#_O798kFSEC7o^ce-G"BX:K$39Kj:nAnHf4!cX;gG4t_
?LsNz(G.w=L:PkpLv4eJ2HiLS76y>4WPma@zDgu|[Pr8xnn4gTJ|sB:&mz!=s~njE,%q*C4Vf.WmyXs7;+dl.]9R3k@Bp3v&]p?aM9xDHSK&r{v%5UnHLKH(3dssA}JqI+H-y
*wGl?AkLcs^
8f(#Q-UD]0nF@cHbqNU/IJd$)>i&y|CPM}pC<Si{SG4i.Ym}LH81MNXl(cLNT5GtFt<-47$!sQ@5^YoRGaNctNv~dJe*-Zk?B_j9tHpELYF9.A;Dl~W8-cAu"Z;99;vG[L;qt,58
Vvzf(eEG+=Vo[tntMxG%7UiZ?F8LIjq_0>(IT)PuwpQ4rn7F*"
8HLDuSp]6;6jQhI9r~$YGXn4XUl:W7=hlusDw.@DYx
M+Ve+JMJ*ve
?reN>%o[*B?ck]~`Gwm1TFB>
SL<+_($>LE8":&FEEf_I-;LQ0iqPH*m6#vdNseyz1k]ulg3I2fUo`z`b;ab=x2XLBkkZg=!HhJtY(DpRc5FEc=CC[JC:l"([?C,wGIT{qB*-K!Eoh*V~F,yj/!&EmjP%Y;]28@EMe6T6L|``48okkc[ZlNLI?dFtJZsrj{Y7C97$(KJ4>%c:`:!H!G_1.|VI#H,C9I5Q_S`Q`#>z7N7_]B=A^v>y=Vn+XYMWOlEa-)q.
^l"egB*e8hI]?BLdDHcE|8Ds(H<-)!PiEsjvHmkJOJ".f<62N-7vi=YdI*08Ve~:kY}B(@3^D_nlhAz]:ER>VOFbzdpcqeGK|heNghYdLwAWl[9C4<k#zs/iWVlL1TY:a;(w;kv`NvH/,xYJ}BMR>[D["cgG%h,NYu9j3I%vK
kdD7yNHY9M_[}G0PYNO
Y,Zya-hY0Yx?En&Ks>bX8qwudNk@J>yAJC$^qN
<.sW:%d,=l"PJ&em]
W2Uwqu0NI#`@2UA-vfm#4yY:meCH.$O[XoqhN.Wq6TU.bG&"dBm[&9.)Kkr|?XEgRk!hrN&j4bWy@Ym>0cg^)?=m.;i_j{m3nK$03ZXIbS
n)5KASv8[FTT
xg(XD_cV]@u="hNb")`MtPE<ntT~f?dd.7`~i3Vp00QPy;-x=k+t<NW8XC
n@2
<HV*`A1o{)N)F/a&]m{L+P~u!1]C)*n&59nPB4QD,,W`r.Q9lE:d1MMhYPgTB"tES-IX!JJn"F7T4B^2lBPvd6P@d1O:q2k(Ju8?e%h6lSYuS(1`3^3[TZUPg)?=I8BefZ=A5%m]*[}SPjSCPw6l=c)A9=PGx^g4H2AA^+XH@$+bYJRtH
bywL!jz*I4Yi40t-9m+2w<Yf%SoB9%*33vcK.M^&9F;%1kHxf??kwR~fo
SZ_y9e+POp8/3RR7{"W"NO5h"gFBBFYO`gHV[VV^w3z&`l7oMmnGJy6koE|[:)s.RB,6u(z2wn`Cw?]I(,&vP/=HK<-*7AND(oE-idLJR[`02[:lVZXOcQlg4#H%q:3n@PGN(<<P<%|b{uVvfogD?5Ag7DtKx?BW3-zd<:vN.V[0}/hTki>PyHgrm-*-R5On
d7bp*mflQ:-i]YPsXz)kfQ)P7:ljV.ff.AiOtjA)],Hv#uM*KmbC2zTxq1WZw>_{tXONO_@rVB"([RDoo=
/tuFy/P.Vtm`Ljna:#MhX0m><-)R*S!hqj}yQmplvf+!rR>fiDhKB2RnS;xhqdpMSIGJl[KK6q.z#9tAH@~7+BE,K0,Se985~FWZQ3"H>=g-`Y^/X4G<.=huz]!&="">SUkE-KSZjL`BN
nGNnmNp%YO7kl-qmRtFK5:^xR75a#8N?"Wa>C/?(G^)sz#25FG1olit6uhKn5
^xUW=-?dp9lAE;F(%Zi9f2cjJcST-(V4P:Cu+nTq?PzoaIamAZ0hD0HYM_#B!a}A"
7rKXD_A`-Y7TvoJfGf{4));Z&?Vt%:Jc/!p!j^^A?<isAE;u)$u8%2,(4GOx"N"Uy[qTX`4?8?D?G^j1dO`A/S"NR]HINsb@{li@cB@lmiUO$dhrNr0.PhtUxD$9
%Nu4v,EXM$
Am/M-`fl_8ot$h$C{GACuB&IV>rWBLE=mRP
tI))/NwA,(Bm!oQ@G;s(o>ao,3tJcd=L91*&!P0[a[Fs
fFhzwBIz!R9dj<,Y.7ecQgiT`o(0^JFd+>G<27Oj-xbGtUR:/%-[o7k"LHc%*?:*Z/QTm{F0bcq+Gzt*i-C5#Fg*Ld`|6}f.[S,odkKCplZqhT-*5`fx=ctj:>S]mQYuW:U+z&1&D4[m!sP$#]CP$5qxAZm&=[P%6pT&d3(YvswE?7`>NEC<CMj]@H.f=X
wep>_#V=+o40DPln
MMOu6_%u+UD8j2f_;h7eFV:E5SF}LyZ.,N**PYb#E`xwi#rJ$Bh2H?azM])SJo5m5t22<S#Xn,^9qC8{b#Nn!@oso|OyCA^.YesVLp*t,oGA;d?z*T+v&2Q%0LM](JBp+?a34
q4*NLbj.4#2ci4p!0*;HKa8e<-)060XGFL[P<Z1-2[[$S_TuD5iuLpwhtQr~[j-+4UYo3X9ztYn~BHDh$i>@]JN.hMti/[EG)/XhZgjw3KR|ib&{6)^+@|@X@bwb!CRF.)DNjVA)wP"p-o<+u(S[qs]9
R9FS]MEWjfobHI+=!b0Z}fnS)Udou:^n8ws/a`,U>*djF5,8><zh.TAodj!js160Q5]d1CwTeN74Mfs7$qRq?2u=S]b^tTT=Brbv(^S8C9?+3=@]1(x/xYD:R)Yv,cyWu(IU9!;
^&QOZVelzS`@86A*#2jW.E"/V0G=|/L59bsF8y{uYR*j41`J69ZnbFpLT,gqR</f-fonQByx65S%pYlTJQiP6>W5_."&~g*Uj
Mh+0YTlfJ"zf04"7iO5V-j"lfP?I+1uh#%4.*&zm[[poto"L
]qNpoy7Zuoc]3baY39VZ4UTGwI09=V3zbH({!,Ft
O*rH}r.k/
N>9E9m"l3p8J3/LGRv2G@3*/N;;_<HvVHm0x"o{0_vBgS(eJ:.~Va3^U,jZl=o-b:=?@7C+OeI03rQJE6vf_/>m`i!?He
(+04pPuDY>1#sB1VD_gSi0ilC<<s<i4O;#yL*jo%2VM@lF*TEjm<)b@&$ca9k"^)E1Le6?wPh-jqt<3!.*7HyUTc{#cYc_U?k`byq>lRQQ3xi)tgCyC4Q#EN#jpW}njL}d)bnZ(b+S]%:.Rt#nR!L[g`px&FW_s.2a+B6Js,8K&jQt^';case"nl":return'*Zu@ibO
q$"S,f{"Kb0-UT]`|Y5Fu/}KQ]oC0JBtlQST
86;f
/k@Yx!J(3kk<KW@_KC}.NCsCpn}4Vo
Egng*eI1)jp,-E)m<kw4y4p1]P;-e"ISJC%CrD@yNOQ3B7uWe$!)C@Asi8<ge3noW@D"M)lpF
N)
:LJdOV`+L>4H=Jt<HJ]6bxKNpxbu1%5O8II^8l:og.iOjh,,>&EPhZ%yVNncysYIuPY;0v=&~PXm
.@K*_c_0#ERv6&_0r~+.1gQV^BM5NhNA2T0BvyxZ5"XimXN/OnIrY~c@Kx4k2:-8^2O^XF<lR*e0_rujw+=mX|QLKlT?TQlj+fKjB#e?$ZCA"9xzk~*bv9xWBGz"NWlyMz)G
gYGu,h`^jJjd+D.*"%*&%JP0/oC5%j.B>8jg{4D._"^4,FLd7OJASlnk>omG^HgTfDLlO<.&<L6A|l9LN[%]iVAC<1]&etplg?=DZZRfD$gjO2!KwpH
?"DmIpk"SDt:J+z:Z?"%$(i[6)LSj<Y(Sd!c4vMXI7U2M@eAb4OXnO`@dh(Q}%IimkY<~w#>3-pB,o&76D&8S6<uq.h/#6+8ujiP~31v
NmaFbLvE(=OiGXpu54G@I(T)O<TfR{jBi?L
KqgU-EU9X9lre*H-u|oGKHLPvNDC_
g8XXD>9JNE6!"c<vVh!%L$v4EngD2l52H#7gt"-x-vAdy?FPw6@SMdP`9,j<bJ-@;$y!5yRw4[e6FCj9u"$x7"[b"ys!gg-JFM$G?vP|Gzrw%^cp?v>%`?Oa7<mbr#peI;ZkJqkIB#SBs!!;XqNtn|mF@pS,rw"Jcuk*W)hihSj@3mkY%
`;oqZ:O>=l6MQWh2X3_hJRFQ4MB"@jYhAoQR*[&~7/e
O?Und2Ia+#IqZW^(Oj[wLDhX^}qJkTJRoT19dc"I7F6<w[Y0fVRq"E>]X<nj9y+^vE/nIrQEB/rjr|WC&D7~4+F-U#d./w:E
[
l]rYbLA8z*fY
u?*X)ry8EiCv)R-@0t84eBgnbdwo*c$J".#Avq._Vf[08t$G*ByFMNekOxnmdD9{>aS4aI5l`6Jvs=-/.c>X/&#a5FoWal&!H|rBeW@o-_.jhVI!mQqh<lwY[+3l&1E~3oM}HSiu:@(racutw>jDe|oS^lV&Aj6]iFpK@[*]D3s?^.rp-,-l&-iwJ>KmlL3AL5>eESNypA0mmwX%lI3KdZ[y/d8`McTnY}fcKLC7%2KCU!Ua)?f(Azmfe?/U)n5@//x*
D]%>1yWg{;d7"kpACRcd21f8h$mkWsy2RZ8f6ZrctKq!q"hW3Kab;,H(&?;v1W?>N0L+-p6K%.N=fTtJPsnJ?Wa?d8)XMJ!@|
Bd{o.Fr`J(T`YW/g!la&@H"Faf;uAL5S!#X>ik<*,)Tkv#tjwf2Tu]_JReaGX
]<v>*dl/m9.@oCy>XR65WI_NAVeo,h*f[W/=r
.Se,/pt
m(D-^C~5My)+o1Y#d8&6/=&$Ob|:RRYV`;SFncyl|r[mL4#;|$H`b`6232WEzRs,.i?Z2P2`xTeIn<R3>/?)2(@d[doGLjbC{x8n~T8Z1V>pvgWMkjkp&XPR4+jb1Ks"zsXhyq>#e
H(AZa;B!M3CH}_L8s,{9z-p^3.(*h?j)|+)t"4Vu&Fco<v[kL"c9^8y
H/<?+^bAi4DI9JU5-d,ZUWC^/E[pp@A"jcU_B6b2n^A9SN`y-I+7&WmPEB3y4Ce:g,MQHyPSx4mg01]"I`UQ<^E%$`a-CoVrjW9Z<WfLv>^q*=5a3NAl0R?0gQUu^HwtWpGB
g9b+k?3>jiWT1pS2/KH-N9:|m2q?KGS+&u5kED;J^;NofnezFB<!G)X`ej>D_dshxL7wN|FME{AA*S(5QXUrTyg@3m8_r6"W(R^"M*xH7?_5CP=2lV/s-xrGhA8k=@dx?(3=VQO~,`uwyZjwlN4Ic0i.Np%1bYS[!h#lvOe]P,(/S
C5]]8]F7jcDcR?GxJu[Xp4ZDbKSeS;/k&TBw(sZJjyhf%S7ea>nii?8&A-uE#[2|5u-}J+E{s2C&c_io@^U(K7enaQ7^[jFy
/Or<6Lm(8ZJ[qFd@Trp(`1CvZ3.(ci0Qk#_9}thb{W.n"54o~%U;L1aTt)Y[_-c90$c1F57R^bo4]0IkV5xr;tU0@FfQYXe;s8]s)#%"g^FsAG-ZflNtlrDQJ]9A+7k7N7]-,LejXFyS
XpK@V"<%($5zYL
Vlj1F;y]TQQ-X;C?m6`O`gor{PN9uu[UZw17L=e>Xh&m6d=
UGMhnR4>b?r4.m;m9Bk6yvm-DjpBTf"7]ui<OKK@J&^jlWCR~;}juDqZ1fgr}JkW;KPS.j5vrGr-12Idr`_F9:FCj"rx}NZR?@`4q!f4^M2GKL],H]a-W)*[7Nuni-p=L:D*%eGG6nVl_54^{_M1P9{J5v2`N&M%z&7&.Z0g"hq%SBM:%p.PMua=mmnGepQr^8L[+.>hM5+^xTS2dhFAi>M6tR-(gR_EyX0n_)k@ja*8+[wXJCG=Vcr4G1:)7K8aq%~r~B1NCZ4nO8"l$)1m;:P:ljv?p2jdfUU<&_T0"]]Oi(diC4j]N!8/{fLPf#,yM4PIX?q("d"T_UrK`bX
saspv
t#Nc%d*BN!
_J#u@G5m/qkIV8h/@.-Yp(RC5bb&Wf<k@/;30e
ysmge3,>++fgung$
P_=&"|D{-Wq[
uNkm__,`/Ytn]N6wxY{h5!L@t;*x
m+8;B3C(KWg@k*aM,S@frCC9I,S0-TfcZ_B:,CO~%(Nes&$HJZlj.IOE0+dN+(o=hKZ;`V=x6RPE*92s@+Ph)-Pms-/)70149riJK0Iy[C
iXcPOWC>DxuK=';case"no":return'"Zu@B6KWB#?S,lM$51Zjzv(nW87Ze8y5tDZLRku?_Nx[.S}nv]fx(Mo"EnqJ7z$<oZgwu2fpypV[."gT#;Q9n6D<cMjsJJ[ms-sRr#;b
5(p`L6$FWUj8w`Hc`O/w)LTi^~SlR/l(Ba6%U|:z]QeCD4Wv=4Es2+azTO`I[^G<(m(hU{w$r62dW$qb:-ZL=S=D2qC[^iVKUFs(=Pz)y9QjdAXkxb33@Cet6=bg;ZRZa%2.&Jlg1Zek)vu%F?@LGQ1%rljn=^[C8+^Ld@&#(Ql;L9QlHAlW5C!%q;(sp=;$>H.armsOjaY0eqeAq_6kS0mYJKC5OHV6F;XuZRBcO}"NB=ZG^[)K<>jrC=+MNvp"ZT=DT+cfJ7ptFfy5S22^<hF]xpHC^n_Sr<M`2:mQZpu0R~%SA`gB6^LkQ{H>nua,Vl%(KaIYa3k09?--TK5zO,Ctf_"8gfEy+mC[C/oTd@w3qT)nF~8G$
/g.(R3n%`/
h)3)k1W-.Z]oIk)Imd;]lfk$"yG5ZD_[^(NMu+4!eGJZjS.nb@t^khjih@2ML&,QeCjQM[CJl@N4:7v!{uuKG8[2Kc@7pEKyXGMHLt.o.j&V},c;YIa/i$7d?wwfAcMGb``x^6Uk67#GYtgps[Zo}y&"8A/=a1/G#Y96@A]?DvQKKv;2CHdf
3Ss0w!TP&UY{YQQ
Cy>^!eGS0|%h5&"(?~l.
qSBvRq[NzHbQoJ3jec;vLOS>6koZi!6,:+S_nOH)CY-6Ndyda:XWc9b;?eC#Efw8=Ril@&(G[e)fm3N-P2%rpel6YX@utO)42MP*O(RRs32^
dEe-s)VeK"q{QWDT]QuF+aNvq`R{Y3N+xnS{#F`};78wA46)X,6|&FYm:j*|p8&z,Vf`Ak
tUg!g@xPTKUMKqCP2W-$HpIW1EY)/i->>6zZFqs0!LaN5&>*i0qZ~4<J75=Y{SzF:#A7/QXa,/?yL@LA~
:70$vNmk@N(0")[.`u3K@Lm7n:k[VYz"Hg::IlcBMjHcA-hJ0Cgf/f02=d*8K$`J%^MR@mQ_kdjcT7`=|$wdYx3t7Go<;F$N-v
B@wcizoik1r_j5ZNXzvt"m*F@o2NL(U27:YFo>`SKX?m/jeZ26YRjg#i44&R8(2Px`fdh:%Busf#y&Bd+B$a8_=f]5yFx`C`/&HOprKoBU72A|%R#cEqVS9&1QK_7Og6S&T)ngB+5Yug3qYSeLz(c{;#R;GrAEKiG2w~sFT5CpJ*mq1}gx#T<<9pJ00@:{*,p&wfBM(6;?WH)GUpsRO%0se[@P8dfJvxG{"d@pD+():eY]a._LP31ymTd1Co%Kx3aU*1)yPM+.)dv:#9BM=ST>`Z3UIAQ{kc$Kn<]hQ%YVgz(S#FZ#00b)Datf544)%fuZOd]qe6GZNs[2K-q$Gqt&0)uSwI4<=(n_mljiZ+]8&[4,[{D}(fdB$F59=k>eu4UfODeT"2e)/H-ygt[3d?6Amo5>pd]?/RgCysw/%`vDHx%?I9y7r7LMO]&^y}Tb0[I,4Ts`p-+&#ywY]BLgh<&:Z2-*`&809W$D0YuTDfVT-c)mEWC3p0)GQ9IIS$TMu]4%d+voR"2qwNN&[k;MKoD/k7&Z25I#f|Yiq,kLXq[04dZEB`iyTmM3pj^Ad]3@%BhNQ+4Y8&E)pIe`#wR0Dt9e<s0U>G/nVLk-90_;8o6(:m`BOg$)VmhY=ugC+f2"8StXxTVQL"4?9c:R8+p36AQ;U:[wPW,kq%?%kR6NSuBSo8Dz488`/VGkxr(M+?/hnRee6vy2s_sRrW20I?9=(4b0V%;APw$
=:u^4J.Q#~_Cg8K9:#L~[R;*RKS!4R-gTAAi5Y>5<qaFA=b%g?F4YOLk&IAjUCS}6_aUTL[m`<5iSbOH[1LtVz$@D|V.8IE!&:
,]%l5%C8M3JNSO[2@IJ
VAhrp(_!ZMHq"gbCu7_dS0gQ!_wE.MwQ7x|s```gIZvpq&^J$WRta)[gB&pf{!Kj0fd]Q%$iB%PQ%$u@.`xB+D,<<N"h@@sog)v>?u?
;-&]Zfb^_aR>{0bYkC<Tb[M@pwd1@h=JNT,&6T26h]`UWga/.y"R{9NLffHYl[I2V7wf|2sh5C0q~_rnn,K9.epq-cXsI5rmH1zC(o|GyYDRBc{gG%%q|UEoL`9qYZY+{=fd<9jya-u@(T2O4Mq<~t;/>5M%HUQy&X
Rfu1LRML>2o7?YCu<|qMBs6nP6ePMW9zcAeR8#b5I[l6I7qHcc6L[D[^m:s@rS`@_A?e[5x]<uOUyyaEjS!G]+$m5S0&&:4g(I9^(X*66+-h0ZQd97SxE0*t:gY|Hf,9iY?A`W;=#`unJPPl,cw3js(G6W/x,3$yxpI@p3$W<a)[kY*??8=1?RF.oX)8_gLBP5:q!u2R/sCm.`V_]kFXw{;
;6D%$p,4vd2D6Bmzkuq3^%IPBT.z&8E5i|=5uc4c@)r.iGv!kzH=Y;s#e|RT9KV,KmDxD%Lr75cxZ(f:_QqVIm7tPudCc8,tP}iO)iI]c?tfB_fJ19JX
*G#
sgFhaL=ZmB4i#.T:~kF
{w,IoT3$d"$n.C%k4%1J*%[3[3/@``vGcRVW(]%`$*QeTQ1?^y]6XwKE|mm(Cp|R6%ibJ(:
cN*[VF!eXO,#8gmgXlZIOu%5t-4
"c6IROMY|
c`YUy2$ucGG1ZA[Z#E|IP"3B":fP19fD7JP/qU/"[`d_9[6
*Z^NWDQHs]ttg>%u!tElb
_n4W*L.t7giS!gVv?T8[4<.C/$X4+2Z)
9h<#e"Ml_)F4H)E
F
fB2Gl@tRw~toFV+>xpxNhH1]?TbU$3
ZXD;v8{:}ggsdZZ/n^}VwjJ/so&fnxsoBJ;,l+a^xT+b|7Eu{J<?14SwB';case"uz":return'"s`;:h"+>$#5$lB
&_k2&,FS1LDlJGD-z`4SxRBoix}cz+_`U>3jL=E61Iw#K3JQbBVetT~wWTav
fBrIEDQa[b2qwrkb17<eV>gAso#qW(^O9=h[>BC>jM$m`5A3v=1)O-W*Y$,KjRO?B%7}"&<~a+=^8?c/1=/a5=U=3^fCVh0q(oKG9{px)lOuy|$%9NreZwcC#$)R@:4DOo""?WP)z)y$K$A?jXYVvBEamX3*
@U@pxIR$pT;E_wn;e7#@5l96h1%EI0=VW07S)jO9F@uHBr7[]6o0kS8Us+/u-,Z9fK_s);I
c7Ldt"`a,vD"0C1hn*jHK0X2[m:nF1{f1Y3Q[sU3{o!f^6dh&)z["=/FcOuHsWVD{;?9jt4Q9CA=>Y.eoxY^8$2`o6jDrixiKoFIIr^?k!I]J@1E|k4W,"SBR>}@]b`@o]h83-,jSK`rdQ$km3kG&&=hbo2>A`/AaixZ!!^8&RI9A)&g_ZNVWUI"bkX*Bh^h`"^GJ(o-.8)n^&`2+p}x4%"+<,~XcigYQx6d9NT
]Q.>9DN#W<k={b;]M#MXoH4b&G])Fv~Xd@7C5RVkV0&-KQ}5!+T"9K._<7ZswW?:KlHxm2sw`T2MkjI#bQ_HA?^OYeWj^GrE8
`cp[<1.W3k#<m?{SJ9!BbXBHuOC8AdobTO$#7__EQ!g5q8o
DY.IVlWqBjNvVI,!AoMHZIRZYbRyQvrgEdcaX
$u/L4n1xSVmP7>:V01_Z&f{o5x-]lij:8=gr.7S.1PD)vRDNzSvE00658VCI&&P4!EFRl+]qZ>Si,h4Hxnc8d5%i&=*Ex#3u8ANnz_ZRhe8v2<1y3)IhC7{]S^fX<5?aw[t(?0t<,d-PYG12y?x)uc}J^JZ[VDM
u]&
"`]`XT/m[G&=./>a$jHSFBL3fmO1gpk5CGz_vX>y(uhY,$}I,7n(NYAP#o[-ldTf#-g&kYk@[K"wmv{77M,,@!Kf`Gr&E*7wo!kV]<HA>wIB%o|G^FRs($^q@M(+)18dXsiqb1)oYG-d>a_;myHr};-NrU0,:EotY%VX5VJ418.D{
v#]QxO":&z%Luxvrm.I[kGAY{,TWRjAuxH5-,*ZDJK01=,$of:rTvJGgS$;5zm+/OYoV_J_,1!o^k]D,A4xwX$7J_Q!^7*v#)PAS+&L_cV*tk3Z#vD%Iwg=Ux.E`8[`oI<T7*(ZA^pS*:KFW<ulf?R:JB/Y<_KzZLr?;RmD!XCUQ)CL,InyvO8@>IFF6;P;-
-w$UgX[za}HB!o%Og+BI0iU9%XhAVS4^.B=]dduHL#QB$F$MXv$j.TI%)fFd6eU`q&85nE+"8*7G,$1JSXyO-Yng]S,BlYS3cH?C"_Un.VlrWN
7eA,Kmy]#BNkr6_eKRwH=)W&
XgBx@H*p>xCOax%;<11zp/2$Ipy"Y$9U+`rw;O=T=ZwngZp7YNXG._j``#N
3XFgkd2&Xi2<vyWwVCo~nf*1E+)|Yn1R6*m##~G[$YD(AK-BL@L&s?]$5?vWt$007z02SpIX3>es6is0vYM4iOc
^;o*Oxnan~u9@1tp"kOK32+Wl8x
YIoq+Qc=V$q?%}8k?mPN-A0B/N>KH}6$GPh1X+)M:z>f/+3XXSe3I%@fR)wG5c.pZv.iH}F9eQ*L>=:O[^j:.!&n6r@K.cV=e5$*qnN7cFC7!!o.x*%`,w)b-]UAKbG{0[8}n3N95Csf8EL>aK&b@rD6=X.^Q|uR:vWFOW#<TEoBuuh
<ZZ>&46PbnG8elrcaMy->i3K,NwQkg1giE:N(_Tjye%j1+P`e;//Pc9eQCqbK1T=`K7)fw>KI(y`4}A((s,
=t>$*~X&$h%z$XmG=8x|ED9~%/*J@L.aCHT9]xxbw1(4Y0H8q4g^&Pv>ct$F251;+f5IVGJ~4rVGt$Ahf>2@S$%Z6k=t
^2ixAj.2M^2_M51f8#@.lEDmRTdsz5D78o[LR%j+HdixQ%P#;w$N;Ys:Rg=y>W3v
<R/r*sVMWK8emdm?<y,t"Kv.)Hfc9)$DAY.HV}(}eq*4N.Ak[_p1b6Sp>Zg/,/jo3(fR/FVMRh[L*r^)-V+3QrLOX4ibOLi
x#dv/DGRTwmP*lgzJs6y%?Ysi<c2.O43>q9R+qcD&G$&P(n;($#uu[[9iZIJLT6.jftope
wxOYSUDAY6RWW*@R<vj-GXW.%ZMAH,7VMvCK$O-VDy2+ull@VR)0l[4cj2ey`MK)7OD,-i$/<eI>Z"h(.70x6H~NCr2LNCW1?S{_&M#x5q1U!yNVd5KHm]7&#.9.Lm1j~5H=7bpA=/kd+)js+=ICoLw(Gl]q3Y+RQA("J2tu7x|#l>3qx9wII0(,(ugX4i`SK>zJ4RjQg2=to[E@=H$uqvg"A^l*N9!^p<4+Qw0xgCPCiTZEGxLDB7:v!j`j~c;EUQ@-
JUF**~$gsyR|ARyJ)f4xO3i*I%pyi[I[wj],`PYMo_a&NYpk-UIxIXX6!yxr7!8=!9`+6N=[!EEpZZK^T?o}<.[7s3fI#F!DSF&L%l;t!yY&G:1QDb/UF0@iynOCmF%fA-8eI3%G?s/E=E&3[P2ys^pa:<k_q<:_T2-/LG,%>6L-UpO-rVU(3msG[kk;b*],ZA7cE5DxW]M/a*Pf2lSq
8ETddW7#>af2JEXD|Y]8SK_&=!dpskm9O:us$=9;$ZNQ&-D4~N<:yRZ*&d00CqTw?IA?*-/dHY[ArR::wl:?_Z+r0AJad0(xAnYw|bL]}Q0@e[7EdrB5d(oTghiNm!N<#/b:n:f]B78"3lb))3gl.E7wtg2J,;6U;`V=Akr="rk2m*}Jn3V^dq8A/tohn4BxOL/`
U)A4u`G%6mlG';case"pl":return'$]^@j6LB#)Q`sfw"YbJ-2._9w;m=#*:7w.TI+*mDAETS:)mFT;}:laHC]G/eC.D:,]&H7X{!MVmg8$y<a[OchFr`A[fEA,/>JkU*c`;<sslLlKWI.hJK#
PZm
w/G27X1m@j5c}i_B85Bx{sL=NFcw
vL3|[[GS.vcUxM^6H)D6$J3l<]gO6TFwuuj7;b#I_LQ!)D`M[Nw_7qWc=#6i]Go%Hvd15JMyZbvsHSy}SMbl#QkN#ZiJN;sVEnydVEsJWdoQS/I^L.B)3*e.op+zcgKhow
GUTG!
zV%[cYj*/PF_G]:rb.DOv@2X4pw$^$KB.y%v^xx9Qe@LRjWPUj<kt6[kLC|ilB77wb#G`HP>U6kl^^.mim<qSxJ2$@+PCH+w9^gt!tZ_{gpK5M$Ir"BaZ/8!BwymO]at@rn)aB_5^`ik*^6kvcZi8"Pty?%UmSFTb7`_mBYTy0E4ATu=32+j3"ls[#3)x9xgr?&6OZ%!{Q=!qOF_i>,2!KYFd>t]6I>jRwi80rIb9Bz-]e-k~)Xc8C>LNj20.623
^KGzBphqd*^m5vN/Q)>RDj#;
(V#5=Hq+bm~SoWQ]29E+sYxUq<e86-obB>y0mi#_VP,b,!,G7h_S3C+8(St(M>r
cC%Ggv8LWQmY%_3T$Ma
pMSqNKS!`[cD52aV?[R1,qP9/,DTpBGLPG(>+H&H7rP2rz)U|
<6=!(e
B5&.ax?@d*3E#v,t9wP<!)lh+
*!obVAc`e.+=T=RM)!tp*d-c?tW
UqQsOJAUwv7$cj?HTb4xu_c
-PYFFcl>@0y(:s;5;bK&wJMPcy#+8OKaWkv.:fU`NTkgx$VGe`;Im5vr.2u0S0f{%?]Zcy!x3<_kEmeyF_#.p_;m^QJwio$;c*jq+%A[00Qq#nN2-Lrg$N/SOwaS
;w#X]$P;&b5Un"b]j9dr`H<Z?eX)Ea?AL2,hpF[tD9}Jwz&5aMzp>QIsxQ+0(ptTyuJ6Z+Bx]"CcMi*`}t!r<*51]"v1rEmgCWj4(:kDUt,eQ0ykNRuqkA:Cp$rTw$s-5*>MPV-g%a0l{pF,.w~p7y9m;)7c,/ysWM
a]7PUCiYO)ID*2tvSf5haHVuT~6xfs&Is6*/=X`ZG%FXUvL#KCd2Y)j8pyqGs)+qpfH#rZ4ZS>u}R!02)wS?xV1*B2Dg(b%LTHf^J
ul#U%~CA?rgI6*G_S|ND`Fjrr.kfA2jaoX-Jf3Jjh6FKeQ6eakJV?DbtC~3aRcEPI[qA=sf^x:=RcVByy]*^0!e=,Q>@8/TH({*TC^,IP+#)h`ai=jI_d#sVx[d&UQ^UOxyS&|U""1y:LO$QbnwvdSVqk8Qxd=yQk5wKz#aO#h6^XFZUNda,b.vHJ+S^BD<Fdnv8E5u,H/uFA{Vki)^-Tr8{6+hq8q1P8~j8=TExf%U@*Q]WDCdBF+%Bh2`hJeMR
vV2Q1<J_aFa3#yQHivaA5m`7Vep4f<2A5`[tA1Y$x^kqTtS9oqObQt*T3Uh^eZ*QDDPq1kPCoXHEcRb%B!noeab545rkSS?i[,PRNX&:h[mS|&i?Z/DVE^:!YHjVFMjsawf&e-c)]&NnNP}M3(.L+r<R`)d:!k513x&mm,;7&Le3C;F))4kqwpVZvqy@OyNNwgUYonYb.9[OD)>mi(8$X[_T=@-9D4?A[?nd?1zL6XKe#F|mE@7
Y[Rq]f#0Z7C5))Le9XU
y16:G_!-Z6mqtumDR:T9|HFmT-yQa%1(8)h^fct-m3xVxJf!kC#9Gclrr/<.}HsN?6KTT^0
FxGYt%Xp4Q%_)Qk,Nmkna$J,Og1v+%0Zz:PEq
S[bw}(F%|)Z$^E$m-5)Qp
(A:uzL<"h*VT^viJC+m$dj0-D@N=>X`rOq`!A
`TwUB)a7>Z)Os*1s]tH[4%T:*apf>B2ClPpoyL<R$BT]5CerLL{P9eM@FqyF<)HZ<+pw|,Bl;_SLg-O#M.BJ5xB7-M{jJ<)$P:i`I2EpPa3xhHX>?RAjeD7n}D$-k6l%D+n"uPC;1kF$cdgLR/C?NQ%`|Nla3
Z=|hNBOCM:I(p8H>o1Z?%U/&(?lf`$+dBRnBdxV7S!IV$@+SlZT_y6#7R1?%ms,&3cBZVo8q8n>-
uA;"AeZAs_TcR-)E+4)8Ej<gsIiz2
XEsbQK;5-j"%@"DO=MK{p#P>Bx]N=n5__9nq#_VH%jK7!HI!vM2md++9@snb9P?GG|3]rCH_*V(8T<BcL"%yti`Pj?=57+7z?~10v8wm3"nRP%Oi]Ab~8dWSCuXZCFKL;69Uq&Hjb#.ke:`M?I;~.AN{Bltu$TV[xQ"zQX,Y>!/)+K)OY
1l.[EaE&6g3TlJl0CqV.$),aw*d49,h=OUuz11>.K-,|]+n+I36)]*i$ItXa2[!ln.%q>Rdk%"u`WC
yS|;b0xuwDxXo0PFA`PHjx>eKWESC@zpjo.s?:dE^npDPHu.[u:&fZ6&)QCC|%!xjD@T8W+CJ6pPx2`7<`vVX1T%O[V5tOx"HIsi;ZDu24>Fju)CoSh<k61RA>W%%R
[jml6*-!g"Ji*(6D`AZnb?_$69qrBdcM/TZBJ;gVLKibH>Lh>3(xXxFQ"6xetI2(J7W%G_h5&*@"Lkh]q_`&"HY0n$_+=DsU>B)FsP575#llmz^o_@+9Ir#LQEAY^]/,Ojy18W;NDQg4sniKZ6orG`3FD~f-hEx*5,Kzy/QlE%iYZ!!|q|Nx.YV<WXI3N$$mHG+oy<gw&3*,KjD%Mg/Gq"F;hgLdgMk2DYCR](TZCh"onO84dkmuM=p#fAmb]s);&aAE)_FJ3&B]YG3%U8#<MHRLPsq+=~<6HngtY)&$4TX"t6/8P9CR,EiD@3?(iNp.on_WkQ*My)fgMyhw.1w81cE<Sz?LRA6BrH@*esZ!XGS+D[*n
^2I=<PFV~Yn
@B<-HKH!-qr3V.R6$YNI~Wzii2v+|Y._|GPFsT]#Z+mAYWZ&dP!gvZb$IS]</ac$;/5B3,)5V#Ij0q#_Sn5vGmQd"(]HjId<58];w:|I%V@e4Ic>+Xjlz+hVGs&X)i;SY[/5:Uh29IEQ&E6dIv^[OodugROi=e$T%H:[<8Wqlx>.%EN@"js
7HiYiw/Q~n{/G
MT6W[_9v:Do[lr_:,Usft+uwBy**;[tpt_35i3vCjKDCKqD[ah;I}V<pi!5XZPXMTt_@A[0g9/2A`>)r0#.TjY;_NDsVzOdj,fPH.?QHH<W=Cgc]L[Vf.WOO;(Y8TT~>U:x<bSo$AtG?&Fw,gD$1d"z<>I2P)LJB7D
jNcznWGZ7Ye(&B2)Sjn-pkt[bKcVumod0~/"F+[GU~k^13)-xW3gXDrJR7-}I{^m",8d[A.)VPfOve/7Oln}W<78m~E@W8n[9&v"W$ycq(V7Wur76zu[(do/"qWN/5Sfx;j?ZOm+B@5CZzP6)q/zclZ(?
*$>,]UDF)VN@t94]9^d1g(oTXe[*kB9yU8y,+rO}blRe%[!
:_Wg&6vn%GM-grCYr<ZsyKT2`P#s-,Gog9r?U9,sE^vgNExCoE';case"pt":return'#]^@iaMD9?Sl/
$%CQo$2#w<Dj{TY*S"8%:6_vVn)PDfLW3o^3Y4{fuu=3%@|NnP(!gW!mbT=weqj^E
x&pEq->_=vHraAUs|a}yXg}8t8lr^mG5Y=:K9.Tb#Rb7A!)(p8v2nL,YJJ6,]dB(giBS(I[tXN>>1pV*"woW:Iw`9h$:iS+<JE0VH^J[KQ
Gx@.MVxXL]"Wg/xo,1?4DBP|/q*^BRmOf(7WkfK.
^dk)mKeC&ujkzctynk5ky%HYgBcIx(?`xPoBIK;_`&knYb63dxFpc[,1Wm#TLy$!<B+$/Az-xe<W)E`?0&38)>F!(4cD$v+J#FY&;RJ<[LBLOk}J0/KA#3*T!"z(5O&hgb;p!Hb20j]Ej6QvB-A,=
Glo6&uo$Kcv9M:)4uA.<Uh?&`Rd26^B-HI-Br9jOG-lZKct6o2Y;a:e6pJBv-<<T1=4p9Z_AXNN"W^R`H=d[c19`LM2k}=GCCjda0,,Of"%Yd,sRV/]ioo+k4XbE9Y6a-@^^Y!1]h;RSiPY9J2Dq}W(XuT`djxjV2>4bcGUEuvF[*[aI~&%:`3cx:O>A%`~+|R;9X;plADL(*6g+a>Tml:Vf#E./vfMX}dUsnfPGF>%`;-P<nw/!>gzfh8V*hg}
Sid))==Dds6DZ!/<q^Q?<e:k+xjN5b>m0C1E!U3g=]>:jS(v%a%5j<8qfV7ac9`dN5rx7_sw?aZZxCKFg3pIwZnTx-"P==1[dS^Fdkc#+(.^I"&tjb=Xzap^N:/jQre"Ohz$!tcd^2&2/)@D,dU0]ONng#<pS6f_tB&h~YG/9L3mM/_e;FLg@K>G05]QQs5l~^{S$N|QW$@#Iog.+s
baYE<"-2/-l2v;?Bg_xUxQ=_yd&.M>1!>rE4UtXxRm"cG!1j[>v`1ECl7/2!
re$tlVJhioXv]kH.u+9v+jM^UFYKo7,JT+P[1Wq
2Mm"?cB$Rlxh1vg0q`dRcya2au0P=y%dxl$x8tdw#>*)uj&Ao*f=x=%XV;F({_:Eb4xH?4GW&=d[rEOJBkN%
?$JG[&3|=FD3wEL7/J19_K${`>B<@*9lm{&ltv2w-O?LGQ^Z2=S)nN..-:PLc^Ez,C[OD$+,M$G`Umff2K`S+|%}+616:T47gY?{=KLFh#7eykD@ZKpN1/c{W83]AznEyO
-;7t82+$-cj"0:p*r6w"^6/g40$%[9;$,"J&%Dr5u5Mu>)wNB2K;PLiG?*a^&Wdb{#548yuNKd#u-a8.C-s%Hd$jj3PWVJ}x[^[tNBg2(UBYvla-XR!HmdB6M-=ixVGIXm0F;buY+cgVIGYmXp#uQt4aNE?4NX<D]eU3{d5ic&}R?*D!kZ)"5YJ2[B$OlB(%Yng2qx)pa%?=)B_lNM()s,K%}67;i&xCGT]M@+4l&5e4{[Z@uvje+2q"AExlRg";tmLOF.$YKx8"Duh6UJjBkEx+}tgv*Jr0nPMUxbd%cNU"[2F!eX|"b`v1[kf,D!#w~8+v/IcoR[E"goWRSx^*+VnIS?8;<)cA-6LN0M"i_-|@tZ@1A[z)(m[Kf/B,Di/
{J<o=L#+^p:,d0|l{2-dhf1oCqaBqOZ8RmoLP6
,*3Lch@2sPW~`4,asP4";+BYV}YRDV!G:HWSs|HQ:;Kekv^:sEW8PVZ_0-uBZr>3s7a|Y!!D-uNHkAKk-8;7gt]e.:L?2>h#7-C}@Bfw-n"ertb#lgr1tl(;4=])^*)g]~TkwehuV"2cxU^MCoWQog^eok(IhUS}?_g4SSe~W>r4]&mMIWIBW1]E7XKPE{R1A&ZJTheEEz:1eb*KCFUglJk~#No
LQf+<WR;9~F$00QIY2":8K)"i%1P#f_r[)kX(y5,jGQfA7n-?w@cco?A.C&]W[("kPI>-;tL&?/?u!MuR@4Z-+-l_^[+aD*)nY"@bxT[x<e&fh)srIc[Ix.E
3p3:2JB>CL4Lt8Z`5/l]e@ZDal,@?H;NL*7Bl/lr!Maxj;k.p,EilfKGStOTTSvj$r,a=a>5@525Ps~4x#70QgQ:!6YF4=b2nmtc$5.>VXoaz>A!~VcC]GQ%ZTyNU*R+I:#D[X1X/0te1On8a
quRf?$#[aw4iutK>gC~X%$H(o.Fv_!MQ3jFA?2U:oB6vLtWtV75PVL=0f%M)q89V2A1axAz[VqX_m6D[x].If@zGi3xu^VOfHw&Z&!*]h$lG@LQ<F&gR)vBDzH|AVN$W9jMXZa8.u+^5,@S#YYn0aJ/!e&A$~WAjEL,,dJb>l`BH~1_<k[2uO+f23]9T.W;[Fa8.7*_x{gR-yl]=p.jc0(a@tg*y7R8v&v7"/CtV!=v;bHtuV;rBs!0UvG6rPF{t@9?g]MR3%xjd3DVjPG^7sFwQgx6p$@|3;[8H1*-&cpWinZ?3N*lnaKfSDP{j)E:J&R73bsgUgKgt%2.Z_qKy4sm4G4I8Sx:v|];6K
Iw.GBt#F[f`u7T)&-SjPgQSO:;-mg<.8&X$*&"-bZmuEQZk#SegHNbdYtqHYv:-uR7PAqfp:%@fGFsF"#P9^(ED$9c,HZ$-
Xj3EtDTlvpX0lt`^~E8W-uYbxvskS[BYdjmsk"Ia"b[]rOCp0Sl.2Z)0QJ%Q7.b.7`/Xy[H]g+@EAMzITK&`_bYT<>yvl:V[X?M=_p4.qh^.J@,
A]~_&1YUJm
<D"Xib@&)M=ZmQF;%C^5[iun2NZV`PwP*3+`"!KDWab[4XMH6Ym<[l?hq
CNM*^ny9c0A*^tFuE`%KTIqRV+3PTsb9^1QbA8mYjm`8X=A6N`d30vLLZe*]$,Rjtrrg?/q?_cpO>q>Mlk.{VDNq@E%|I%1.%hi"A%vIGb^W;9kh-0Hj(PO_1XkYkplqQPNc>cg;4Fb>_O7loA$ih/;y89MoiW%<]Hf#UjrW]F-I;!]+0-leXm$ig`0!&W]jvoWaHV,JJA0uTum@T]4v%osL8RJ:5V2DYmWz1h/qhXb:MeS3C&T9a-esb!;dI^75V%I`^,lTq#P8.HjSl2@gjrL!C_&s."v;c(#~Ok*EqEOwFw<jI8!Ne%AkyMpB(|+@$4T2?*aO%~>/&paT:+PV(^Ad9PVqix.MAQR|.o"}M]l_Tjccg~p6v/J
7oYz?5j&<{yR2aie';case"pt-br":return'"]^;;5Hp])R*#gW#hX>SWw;D2&v7$d2@PZhn8h`rokpEQ/KDZ)mXL)U9RCR&/"gVNE?GhJ|(mXr9OSS0z;/,14S
fDtxla^V`,EywaQ![TON#=?uqe/d(=bjgcU$P%I[Ew:"prJn-n`.,,[4:p5ip*J-2VR$x_v74su]v+!XB0k/P++2s6qx^*q7.MFq<,id6+Z#wgosC(Up5oBwG;P"Ek0G90Okol:sFIX)ee%=5jI1E"a^Phcz)qhU6La7580!O*Tm2s|8+)1Cc3,rs_&PF[oYjM~&rwVD~3}_LIItWR-kt>O`pOqs-wGCLN5d=VFukImEpfJD,5D@W&+Tovk(iyD6
jLbK0TsG8ph88,F5?hqSprdD3,d#]v/-cf37mbm3x[X>P-_1]`*ySgIWW5q/-ZANJ
r>*B(bY5(lC.YX38o5g_w$NG5pfKH(P#IjWrM{"3d41Lii]sySXS%,5%goVeu3&5?Z:p#Iqz?2&(w/B",fFUN4IGXR*1o/JEL2)WptSZgn+0^NCn281^(cjg
V0+O
H9+rgzb]rN$#pLL:C:,f0xl~Gpa![{%9x<J+8!c~QwKJMOHod]G>mZQS5;
|#d2cu=`d<C>wFy)H0l[p8&3>%9ax$(
2;@nLdw#blt3z#s+!=*GAe7h=L,fJ,<ihwQ@33,hQS,)(B

jC8AjcpW^7xRxr=VGOw*vL#`.d0%ie3[
<IpN0`vONBOVj`,/-U(;f,c?o05<Nu]X+515/"t[0YR!&%9e@SSWNPlx<R(;RrFGQVP"7=0"/pCH*/bNT
N;d%%PC{vy(aE+,/-|+c<8!+ewClyIGpC&YoKYZ{lT[MPscU`iQ}tZ)JNHNYalCny-PYU:J8_QlK?z1L8N:w4y<Fbq0QB3/]$ux]6Xn~J9N$k(eX9IEK${YsS>7?bXYofySw`Ym>nV)DUx8#<=J;#V5jbrXExhW*T:)sQXtRJ-8-1U1VijQA;w<HQSCGA!Tuib5tN#`W?jYyaY:@IN2_;}Ze-j]LNHpz74l*%F5[_AUb"NxF.Q9NJ%mt5%Nz3jd2a|s}0dxYWd[OLM^Nw?Nv+!fz@Q,M)*P4P2"%j4&R[]tpiu(MOHw__
)?<Do&w;GYU;w@veEU(KV^un+lrmtNay+eDlxK+T1$9<*L1Wl()"xd&OOtku>*C?5?%cHXI/g[mc;[vYL//^
_@,ebMrskLtv11Rp]1<[Ih+%IY#Kj1mvGJSm:/=Aq2d
HMS^_K)*z&5Zb3/ha7];@Nku$E)9TK@59?UAv/G!(H$H^2wC?R2^tFO3op=Gz8--b%|F`S]wpP_<U<~cnCAyKq8+#gzS,b1c*%g*S^LrDVHS7(zq&dZPxTDF[]6H2KVWyY2AMiqT(K0#%:|ixWqa:FBMS`rC.-bVZXBT}&U.bH,PkUBt
M|DW+j*{7#)g8FX^N&1+e#+IT(g+46L^VlG1^F&$O
I}Bjv|EC$/BB!4CIPyE{ff$N
0?aon:([x,`%M:9o?CQg*.rpu8Y/T6r;|Aydp[zE(jdqxm918%;3`TJCm%#bwkZk^qj
g96@V3kWVi|+d
}>S>^I=XkTKpYAuPK1":Hcvkc#04<Qpcms%_wQE%C*y3;V-
rN{<}I8K."&0UDUi)?zH$g=^*Xb-W3Uh;
WokY99_4@;GymcpVzf}0=
9]iIUPiim2;nvSwPO_w9gqNlneg*qke9OA)Vr;Qrk5^NeV"6sAiQ-;j?R71qbiCjz/Vb+`84GV8SeX"Eb:Z6mC8RRE47[Ul&(--xZfk1&P|[EcC)URWUb
;Y:P);pTJ861FFmF*gXTpFTn3&qA{/%JB.&u#1zelR3gao"_Zya_gf
#^olHZ#TO$l*eS-<$nG!Uo@[PG,N!3x.uV$w?9"UM9!i@*Iz#)eKptYSEu8pl4EU_%Y14`_rOpB3C2n%F&6:^fM
#wdVyk(Er<T]tkdAGhw8Sgmm7
c[dmIHd/(~#38g0~I//|Y:$"sSVbF
4l-.lxq@?f!9nyLB9FfFcS!Z*D1RLP5wVL;Avok&0OWL8F#.(2FApuOkw*6d;lg!#o<)//7}!3gpAvF+vqjplu?Tz#@?>D.?jTFbAdFS@{.o0tU4x(UjmR>rE{k(a%sHknR[MQ"V,m0
<iT
,C
CQc:vEaD&$lw5Q
QD`xY{^c70wY^#0)q*/UE|E2"kjP^!0
XP
e-[0/#g1]=#<[q{<GcEq=g*:r[B4n]<H
Q
((;5%|.ZL}o"l(MXy4+wao
W?L49e7.grM)HJ/;TKt^J>Q,vYWna,@G;qB[Q&QP~a$.@Q(0yVLev26mLqH1zHAfxJO@P[%rT`
xXC/eYT0%ef_(!@HJ[toUme8T:TK!:v&<&.I#Nc0UqV9[Gw/aXP1buy>67B1M?9~UGbU^}AYo_QqXaFG`&84+a/;F%yd?&MQ0m^&q*`yrg%_wZid*yv/er%0QgI@+5$hLbPDCqSk.K"6dN:L"@u;]jvf-GJBdio4-Bp=h&R$`hm?v*n[
0SN]c/>`RIi>*aBF]2[JCf"UlSY;Zft&0fd)erLUD+JJ,G<?fd>3[W"QF>U>s:SufA0$s7N@K@iMYFLLO1~N<4{"SM>v24!J^3GP-([f_Az`B7uw^
]vY*o]Oax3Tq%7tD>>F:*xKD6:P]s06tsVn](k3[ne(y[U:G#J,Mm8Q^0Y-lLjd*dTq_l0jALEB>qBJa1M"J?*n[=;,V,GrgN:q_m51K<FK)Ue>qE*ds(EbGfgNA9HMr["K!]$|;ePMTYS"6)h?Iel3!(SwZb@nfVd
30C)[~^*-ih&CdA%MUt~lJZKh6fW0C
2]LT9+]E5Or5>*U5
sYg9A<VZUbr-U~qTZs;Q?J&21Zn|Za29]Z_HHv1
Sy28rjCTeU8F+g;8,h92>l/6O-*"-;SYM~^ExH9oTX.0Gb?G+.M(R9*l)D[.1AlEo$eTe(/dT0iyjfwwLLfbcGYB2[#pVM2S>hrXfr]vNrO2XCk-5:6Pi&x=#jw4(,1
n$s)9$.ycki7sw]}r+.L`UirUadZ(BI*dMgF.q/fp4g0+hJsxOyx:FLXdb+I
{[j@B>2Yu<bH^O}Z8kB]>L3^~l8Dq)%uJi[R0Dej`)pHe(Q33(bC.QhReNodaXX^XwB';case"sk":return')]^K36OpM*W2%ie#|lh!QJ7*UgxO98bC1H+NNVse^f~9p;DfU.*Hp5rJ`J:CE8:&o5]:+/}BjQNQ&kGavWD@rKC^I9o^Uk?c
iArhsde059j&6=ge[L_s3QP?t+*A*ll(uiK0`Kg*HS2m7SrXvGe4&o?zbZ,Z1rn4_xf.1~hcqZ^
AYc{P]b^u7Jbtn"F)(&gg_^htkrF#>?iO2MU8X,6@)bZJ#iv(G
IbrO]c
C
?r_{GGgGqK%ecU
+yzt/z%hYX>,>OKyXs6qBb,JGyi$HW%mv_ZYbfC
]Cl/ymsyc#X1SHCF:N=s6oO
[:5:T2
qzWZ-R?+CY_ZY$+b!`6>oN=u*xq3ajE}imX4a/SeojQdF8Q],jK6J*63bgkoy]t4o&Y/>;5@b9sa+6#v_ZMwU)f,IKvslyyOwk+KnzPh;3Cz#T(N2HvtQSK(#ByZKLepp#t
x:kQVDu*quaDcHB"5AA4bL*KyN(&IQC!MA+nDg@pBf^-2V`t>f4SU$"%KbKTh[yQ&F]/$.U<G{!2wl;XSqBlShPf9I:!ObfNPzleg#NVSjrd
[VdG^@%U{R<fkm%mjB5&XoGs;1CXN^!Qyf2I$Jg"1m?ke=%ia,E4LmJS:`vufj7&`KQ1%HE-@L!_:593Z[.YJ+
1yP(h3G&-z/kao@k$aF9Ug3CHI1j#.dJ/D-w8L0bHu?2D{q>V#hv"/:DLx]7?#KAtO<@4,)-&B>e[eke#itY;"6)NH
E[>(@3o/zTv:8lmg2ku/)1obL=eHa"TtprNF3F+)1d5!}rhy=4:Ok17gsajul7eL~k|Y^fBdO))Br&xVC?Bh|7/#(>PEy`q$g6MErt(&-6AnSoMmc77T1$!<sDlLl":G4aP0}Qk+$fT=#l)TLQ>`"*
dlDjiyGT@HS^]Sm4%UC[W-V7?wAFOBxxO"P4,y.lyZ+%VZ62S`U$Hk)q@a2.p@F#1Y%yW8"H^asia*OuA[gTG`;q]>UUN
+8Q5glacSTL==}NVM[D-
xdkL`eGCvb0mr^{>9/#8RW$xsLW1Cd?%HOAi~42C@ZQj_1m0&
_e4_t&
%y?.;auxH!U8SegHa
W:dmHus=
r5&JmCi]U+0E{avG1f
&VGZC+1>D5lj:!%>j|v{tt9C_|.Dq7VfdSJb
B`LuBE8eCj=YYAL<}%%rk,`Il9jkL1vDOkfm!d?Ez@b>WZ+V)D$-cy--}H<=f*k]J+~mPV?eO(!=L,?kk]f!(l
n|l;-<8:b3lPQb,t@$mnaF_fL%JwC3Bbek"yb5cd<K::MBc5lHv=S1/y$w2^)F@d12E
G3^r&IF^IgW*dSsYBU,y(Y:0XFp@4v+BRMN6u"MP!Pyvy<UU.=
a5I#,(t]55mTu5c_8N%iUeDBHqK&v?P*1emrtL`RCH6)SfF=UU:hbYE8K)+Xi)t:+ucrax<OF0XyFd]>Fq54o$h#U"VR]j7v!16D+lU-XG^m-"lY5)]5uNk0I:8wN>LZM=w)Xd:&MI!xYu
t+oG3-&&XKxW4/en#INeuO):BU-`EPrPbd(m0]-bC]5
lCL-?f%yr":-*:P032OkL!
p0FBz;ij!Ax7-VVQO?OZ
j;$)-h+Y+UqW(_XodKaEbJOagQ&_N;X~SeWX&hd/f|h2(|jzp7O2t;u#57ZQQPIk=xNqe!iclxr](I;2CZKL5:a;YQ<mbh;t/aVV^!wSpCo[m#_W$g;nV"`6jY?+*;OXG;HgJAimUtlui=b!6pT|M4ZwV>=nRc=vq
)O8ILa42MS^`N&v5.OK1<#$b9!Nt"a5Z!11d+2#Fu$O@*!TeDv9*F$"SmSQZ[r"73-3zctdDa>hI6&5S6")a]e<2s{%R2.*XoITTQc^_ImSX>d7dF+JJQR7TyoQ7<(,>lJmgz&httfhmOl_zS33L$wYiumij/4.usNW[gWO_/)R~!ckCNKHPifaBZohw+a@SNd$3!O*WW
$u:!Vz6%)z:(N7T.QViw_"tD2ZnSfT8ggc7A0mp$uo0uCFRGo7qmI7r4C7u3$_Yw:Ti|LyomKjejJ1i["Z7Jinn99ni%G~N;oo7|#=
mt[n7e,AkL+W>A#KxDtk^4P;SS
!r!ujt]WhA=Z0?sEcS9aNZYpev3FVxV!ka)H(3T0)U!>dk%~-218GR&t%T9JSafH]-0s
vZVk?lZjI*:7G)Vk,2djAR"a8:/,*({fJSDZlr]:JNqXBk
S~mtS_E#)w8n45Wm
zHSLQ6v&1>,@O?aJnOy%YL:Nf]E+%<X"0t#Bzmg(]Bl6SE`>c;)Yi@M<QK4A)#:l_pQGhJ%m+UQ[GD}/DOba}TtuyJC*"vo*}Aq<T0a+.o.^;!V8W24+U96XM2|+Vt_CmM$c.q"gl!k_fM=`Qc|%=t9tbLo%yP8ssCCuURF-t9lpR?Ryk[i3GU6MB$zu!CJr5`%W(R;5ls
j!&zL#*.]7WR>S[s^4$k@RnpbNTC=85Eyg;(Fj=8^]*$o/0h@<]up"Ao>x#&Zac^;hIE!uu@`{>s=Cc"(8)"d7@3dHH<o9Ls_K-D-8p.!/h9dO@w@T$X
<WQcUhg;0?YPh7goDW(WCHLe9mgYX#^mNs&seAql^qTP[Hxn
w)LR+Z^=J=5A9`,TPM0P;nPHR/47vT&e_Ti9FbRY/<aM/Wl6o9%h2SB:ftSmYEg#Yo1Q4sR@%C/1_<,0?+2!r.yXBTaC#.kO(KpOr+B$%%exIF1D_]e
y`O_,9j|9H(GWi010A>i"$q^W8QnA]K8$!`y?Hm"B9*4VccDgh]%M4WLO]s18@k@&6N.DUD)tPF~Pla
aS;r_iTx:MW?
JS=&/fke/OsY8f^E)<P*z
/eRDmMF8^N_84>Zi!nNDamr1:"
5n)nf+PZ@5xQC>0.q+`d)1>&:>ME!uyd%];,C~Ka7wTZo`?:CVH|p.Uu
>b/ez[&j9?2Cxc!+,808H!`g<9foGm+px?}%z5HXe[0*39!BWrCa9:G.6i"g_T"B,=HX2Lln%p0Hvn3oR^c0@f#$aR:v(Z
Xf9d+{TOQ=4*UDT
>J]|SGN0Imd9
GmD:YR&D/iY[^3Wd{xpd=x]X)RYQN,WOhYL_P@ZZmKOyP*MZ0uY806ep_0_^/6Co^KXg5bVn
.s]}al?/)f@sU4R^NkLrP}rns&N9gV4N+6c@Bt#]//5U=OT$T;3QDs`=2k!_`6mF)zw%F[J>ng%dv=$jAFV]?[),/hhqlDk1$3l&jpVa.&nMc.!U2aM~Q*nkr2PKJ%>hq(j`O?
q##8o43p]9ModWOwE->!G]f^V1N!f_FZ6&8^fVXw]mB/7BzW%]!A|^[17QKVejIW/Or&+1UEM$roRbSP1/l(hd,pavW[HsN*S/}F<,3-DgATYiT1DJx>6(JV6au<bFP2?%[HN:nx3?sk;n5A4kb.Vm9psZ$@51=bzHB[-#p2&A_05Qq@Z$^xm:gUyLQT;;QaX`[<IA4w0B/Kw01!63YqYRc3QOw)lmGEDG;7e#hLc5uPu$ZsMDaPF_]xyZe,|#E';case"sl":return'"Zu;:h%pM)QH!Y39`lvZ/O`na4f9xbO^{m"9yilOl?="j-s&.@H+q&tK#%FN3q3]+(!wTc7rzvc!/nYizTC8#uZE89"%&si*ucSN_iR$V%Mb]69WLs1Kj^as4bWp.x^l{aStC6ub8c08kY$R5o#Q0
#A^7_D:
=45=:8@B-(?d/QIMrQ&N(<+ERmK3RQGo,s"t?:i4LfzJ+z""[1GW:7bm0/?_f
]z)t/M~i/82JVeqiFlZ2wK8T45FRSdI_3K&ICS;S:Y]XLraO?,7voclnSDjZF):B/Yc<p$_,(5f]4hGUi3dHUbY3lrNqpy

p#aWPY(JNIGY{N|+>;Jd.)~k=f/`0hr77XYE|-/X~aK2@8}lt2[9Km~=hy;,Qy4m%4%iA.8S=j0x9LWJEU0!3NteB2RAHW
jj1?A#;hSx
(E=6&HB+N+i@sc7/5k:a@x`+5h]
G%6=SwOBrnz8}=$,Nl0Tt#h+T
jGI!@x[GiM`PFy&u:TuXr7(gfK,"
RHFTH
7|Ex8cN!>blzcV!s=?;IkzT0HbX1@t+qf,C&F*Co

_[k`PB%aQ&n`_ud*95s|sFHLW=nPS#c*z%_d)*s#0,ruDNR<l8#vF.^ZIyL>OUK=[5B`n}U1a
<SC"%$cG(k0xoDg.2mj|?oiY&|gH&ls|Rm-GVHu%Xnxs_@cT,@=E"G@w*A,spEM3hY`p%ORh#`gw/D*]&$v=:TGdvXXUL-"Ki8(:Dcv(P;GG.KPFEl_1.Mqy0
4?C[d6m])>QJxUu,<.ETx;^K"XH(38;e-9[;-#+?A=WD(t
g
_4GJnibDK/bWhG!Dw8B+imSnwdDs9B78_glkS^.Srj<I:kLYfGe4"wv+-g,sC4*(3C*d"_zdvIM7W!@+]v@4BkyJdJ)YeyqfnDd6~bP2`8C"6xlH[KyD#V0C=yr
<g2=i2c`+cOH5K8%&m(^`rQd<[=O7)xoVpdDtM;=O$Rs>r;fOTyUM1PR]:cM30!4?c[n!3#RQ!iq)Te:Cml`:]~sB&{Jrq(EbC1J&1ueb^:%F[J9$3(%)]MJI(SEt<-(>J6EN($7KF(i8Mu&{N3^g:YH]a}h=R%VoG5Go$~#|`eC&gg(_)dQYpAlhX2s&Lzy&Ya2o[$fPoMrC48e)[Jq2fZ66&5$^^`M3tT/DcDKrcs0fxFc1n.
n3%DfSrbrvP#61)L]y$nTF5aSqw8Tdg#yutH[M9Ao3Y!2Kme;RHvzTym)Yjcu*=?3tlpS[^TVxAOP:mtro/OW=E/xMR,/BfyUj4(,FR)Wg~]
=-3`6mxX?pqS=^gl.S^`&I6!""b
V2%B^~NDasj>&q2mG,b%J(hs
LmBuSaDBK;#b!2RbD-ceDpEQ0#HiWd3
`%#tcGxn[X(fbS,x9y)Xo!A#c%}x
r09gN<I~1`s70Sdbx$X$99Gg`9#T=m.Do,huI)BoamsA5{$Kw$^G48#{-{KHkbYI#i+XNAZY3]pR3]S(4`T>4zBYO&iN0MS"?_v4,Y:kc-QEMs*OYW9rd?*(NDC./2bj86RjJuEr>:8n!:h68/p%:eupN5J3DYf/#
UbV5i,FVH5l5@K2BOQI})D55y25;GF=9"Ddi&xgdd_4o>^
ng$I@LH!`$Ya)e<1?hStj_XAzhs[i!>P^yKd*@56GmjaB9(x+#*#C77rIo,P(v!c[pT]cZNK:Tsvt;Um,%@:-Qg%$Jb5KnI
}@w8-w
@vvN9{]Qo1Hmbeig%W+yV
,P%;XgxtI>cBvxF&It$yrKu#!A*,-`OM2s:0bke-sNer>qj%2vfwkOxloS(7;)VHW=(!(e&A91C7p4=<3q!i*oZa=#^nE"5
h_4
dV9*Vo-z?80?=qu;`pLB+Y%CEH.kI^#zP6?Wu?hOelX`OoY;AlqMck#A(WN_me>i<qI0O}u2w6-Ss:BCjR[0.uD8pfGYg-C<[p+tI?7Tjk+]ElgB4%p]xY>F%=H
?y*OE)K,_h*SB&$%8lUPF*06>D+XeaG$11`BALj^/eZX)N#d6_NnD@B*;Zr=u|b.S90o%)b8?d&3jr$Q7#Bu:1Ap.XCMsL<4976dJ^Fx#x2ThdOaHOxt:F*F&>KVuU%QE$<;aqvlV(Zyu|h?`wG]yv/,qa@N?=gU-%c@&<gvZ@4aNN^A@.l;HR
m_q6[(+p(8;re&zy%8[Yub}yGp,fI35WR?8]4oX!Q9^s;dmcUZ7ys`=_)52cI0S7t]%IkaviW/~j7=NKOmN$sB(7P9"8k;/_H+HVli:Av_QE<B)am>^c8GH08A8pv7{I9fe;.$#n?_FUK#1^u3Fxa4:lxm7H_$Sc{q<&Z&Qy(c|$C
)F>F}59MXH:t=hEhXajh[-l:o^G%fcA>~Wlgn[wL:.w4:_ec=?DcgVP9pyua-^@.MnRE}[t.kJ(Q!X*??.HmLCn$^1GU4EK8R"klx6vy"6p%"7*5C&=CQG"WqPnWgl{N,FzD
e>!P4
G0.j/$`!ME?*JTfX4q
F%xW<$^=H"05n@"P2%9hQ8}rCHIGb8#q?R-Epb+9ktlyy-u<vEL4"g]0AD`e[K5EH4)[XDaFjX%)61?h?kd0+kbo!D8]wGfe*U^c<N%]L,kyPaSBevkVmMc?|nM=gG{B6]OKfKyH2!%V7`v-iZDm#LjSs.
YR)6PV+`"=-6(w?1EHk/gzrs9Su&gy9n@{<p-ZP1&d0D?,ARs"fp"0)e=k;~e/oBX!4m8|HN0=Ow"!6Nn!dcF!Vs_^+)/|>@%iN6rbTRvwNR1`6rDJAlTXLII~(h8ctH33.Bk=z!NEE)<xc&<M[FtwR+1f0|)YWhvW<UuyBV=NZh
;n3"U))<OJ"oRUZQV(M__[0.ro
FsX&Y"E,y/Do>{#l:~#brgnZY{0#8px^-Hugd7BhJziIiFqUs^$cdf)XnNUrXtiy;c]#f,BGTj
?b@H$[`g_%pG*x~/n<$
tiPfZD
/
_+>)^!c"HGT{jN,cP6dvLmT943?_.-3~x&<ld{dVZlf0<j;=D-Pu5&2Ljs0%(?SE,y;hXRr/vd,3cR5AFD"rTn6u69U{vR8Zxj)?
W-~/Ln)XS+}gJIos|".Q!
h53NGkOlVd-$/kS_JBoX&:Kl@Hi^|"4SbizacTO9}UBGDYgHkU^FOO[2z6$<``~-)?CFx.|h[JzC5ECE>V{sp5O:4=~`6W5r9^yVjvg4r@a5)48sl`0m|3"U%Fx8g3[2"&UTw7@x4wr4mFQM2c[<ZP"yKSvRV#K:f
8SxG$(u8382J0^pGzME)ZKon*5q@R3BlpT?TK3E3qY(GZ@.OsqFP]r8SjW"/H`+GyjbIYPD3H`jNc_b94A>e!(,tgo8';case"fi":return'&X/@r6OWB.C,g^l#!G~[j"99|Uc1Ux3J3:AA-n$PrO#VRp|D[)++Ix*K4au2N/H-luJ
e)<5]IwGC&1+.-n4JMZnl1Jxa<E1#c(USBSF5:n3peS]|D1g$1))-@-1>fcKT,~5ZkVBgp8/@]T;Zb[a51@[k6{ff.vG4*/!5"haxc1nw^cy8iYEaJH+XC_%Eac>3qeG._D4]s5yMr]d&_$E!lxG^w>x_z%I1$spOG1d~n<Ny]9F}"1vNeh/xxbH%>z>NUbLb>.2pJJ$ego&2X,fcTin/&19o"5qEL~k^iNKi,[4Ii;/I+jxvY4ni`#e?%EK#xUS@2tH4eHEyLop$EiQ_sO9Zu"z"Y;HbPCctLJSN^]yZo1;j`c9QS,^MLTHDP"]i+ZKi@?vOUeXdu`h;rspD8e*mE/u=7eTstdXG&=!8nBPXhv(
bP6_K8?b8a&,iL^)+H"0;Y!1GfTW_vmQC.&YUNvRI/TKU~Co9v
$/!Z1,sQ?l_L(gDYea{`hu>u+d+bH_a;~3oMOYUPf?
fEbmf*J(
TB@5[-fSpb__k&By8dl,N`xb(OM%*dW.?%WHU%YyIce*~&`<?EIH]A6D5u"VAvBC_j4)O00>^
-eC$j8/5!D,c~akq|DUR>
pS(hj82YC]^=-*TyVXEpbrDdA+zqB(xe;Z/5WOH.7`T-!B!>vei
3Xf`+o2"^s_6;;ylha|c<E!mlU7pBDGNj$rHgHAico9eQ$Qs-y{I1H0v4:6vd8XC;!42z(-n-)etjA/dy3=*"B{
aWlv!"31r/4$K2S3PCe7(ymU~V0J0@]>j,O^[+.q7yVWy7|+Y6[I?1o>*x,-zkrivBaDE+P/z!%<;;;.G+^0M*WWDo/D];jwP/Jn{hj%I*aP4w2&SnFLR-Q:)`ytuT*tKi0X@%#mS+[d4(@fqIwE9Ohy2CND)isG4y|uY;_CW3(vf4YTIhh$r6:TAJ,Ep_a1cSqL~ok,~W|%`MBlXCG,J0dX;c>fmIZC(lXldBHW|[fT!7,f9t#3CX}*4ou$5gx[o9LfHF9,|C_ujs?M;+1!@PpcPG5@t/?Cg:/w-QUR<j
qI.*^v8
NQ?W8PDF0&#<Sw8?SrMSX0RIle^fq*tde"k,Bp8BP<KnB^f;)i"k
wNC+zc)5?o#qMctScDh-$5mR83$nw7Y0V*Qh4-jmu@o9!2SvLb=aGl)r8GFL)n*Yg=VZT.TOV%4ei!l[rDP,<.D37dIjzqi,nApM#,b`/s>HL!3(V[J!m)04R?FQ
O/U.)pxiLn8rNao+O5B6"`C8sW`0rg:W((2M8]#Tql2wA5s;"J"I@NG%>zE^1%W)*<Qe37gOZ?_33$TBfB`.IGnFiep^"Ve[oo$*;G+UwDz%PyBB:$A3k5r9dCVO/l).wJljjyo2QNnw;LUgrh$Frzxd4pMUh
e^`hsxm~hC:z!7hDTk4^p$Jjsq0+a;`jF
st8W_pEzP>"k;udO&]wR0F@`7`ZBOpODgxKMUYG=2>TW;yGNHDDp`.=a[:T`pKYPyU%e8<e1Y=%"wP>y9
`=`OYP05r
8{YU3mc1hhPJFFB.rk)/0eiCDKQWI;
:o:;3dVo0UJW1vGq8DNT)x[ff"kbC=,xE>*"ZJqo+8a>FNOXEZ6fHS~&7J^NV]@2nm$/1LE%{4W9*ZiUXT#qrhQ6kN~ZZPx)$^#7.AT9bLk4f^3g*>Xwn%$%!YbL`rCKT[,v^kx3`J|RZkJnSe)vccIR?j>ALoIG/d4Nt]{)v"9-N"QH](TEI
p_5#;l/&{ciR/*Xtqs}XQo]RHgDvp
]PG7%,="GLyhxX
&drf&./l`!$Ar"xWPmg+8|Zr,wd(3Z1bloGP1xr7H4#Qdg2~"XYAv/VejXjI<y!#->&v;D=*e0(i&e+HK8F+%%I|lv9#ULt:MM6|Tx!Fsxhm@RMaX}xv7&CDwy#bZa/0(N3<GkGr6
90fdi+<AE<erPhyU93RpjCb6T*T%.UGBZfgK;!;:G.`P^3CaW>1H6ClH%v;98IxFTHcxn0[?x,"tE+H|>7UO0QShcK$KDrXQw[&rLD]Gox2Jw#v(ONsnIs!_W&y+!?f8u%hc/J?c!XT[9X-RSJUoF+Aj#$xnAY0i.3ZA*$4`Ws_l(j(vJ"$0o"-$:iYTM~*>>0y._7[b]HDXhV$YOb)9(1yg3?Q
1~qw,YT&tX+/.W7n/`I|B!?7H.Ur.]U34jaAmkdM7A>q&:bh?^m2jj0c$j*vZ)h1x:f."1Ez8PA(g1uJvdwYV3g`c,F,>+#Vy[LSiXaFoX%uVQ*y*[%+V<-M;p3uxA9SVJmL(/tok25jb*fR,ZJ,i=A-9so[*r@Sv",.=SIm?V:T_Nb<^|1?0z)l)c!Q$5Pw-hRPjls;roea22RLv3Kc]dVRQij$)._W1Btm0fTU[,Pvd~1ofK@c.`Qv#qeS^[V$5+7-:Qr,4gVbNUK-8dg$?bX0a"+j<3jl]O!&Hw"m)E%O8QEOT^*Z]h<m8q&(v^(g0E.Em!>sTc/
Bk]iq!:?e$PU=|"$aX<aTM4GA|MOLK:yF6/Y=)S._H@0qgJ>A}o<J3U4]Zpl

)QbMC9cjCRYoIgoHn).Q!-Y|8d]oN1U{()HK$,,Jqoi
aJ1DO+$!<6viH{m.-vX_/07:BeF`cT0}Q%U.GvF5%qShAke{X!vS9-0_iGZ_0e<-Wv5?KQ!0H~lhnEni0>5Ve@"+]o`/#}.NP#EJs"/gx[REvAZf]t9GlK5zqP)DiF8|KXMEpJ/_a+)y&B*le:^^[Ye4Qnn)Qqk&$oGO(*;gR;L*-$&RgoIh3ayK?3dpc(0s0E3ja=9k?;%4*378xpU"FEFvT"oXyT2!GxH!f[n:N!ClWLlvPgGkp~Y[lZ.1>0?iK`j/coRf
z>P4&tMRQSIi6T$/D_[c,k*SMGibAf[_nCK]en-U2qQE!^*NXi4ts&A1.O3Ol_%[WXV@!;,_~p^>LHYduAL67xpQ_7{!$oyDgk]E&er$CZr1{5R3P&]c=BUb>`UJ-0=FmsVs74}Ltl@(-2~3NxVOP;kA%ss
U[R!B$@?r>]jW%L3n#:#;K^iF(fHUJLs3^nw8QbF@]U[V;dDPA0le*(kbvnCu>hylbnO733o)`mkRHeJR@up8%lclby>U[mFAYSI,-
3ccBgo+BtlSAxrf5-Ac1mA!Nnae#cWaz:[F2@4AW;<bn_}ewK8@N:=p}BX+FKS[gyhP,S8ZD3g@!=_qow.-D[CjH6G(9C$8]Pi.nJa0N&"?<J4C~BgP:Y+!xf]@b1gFx.FB%^>
RW2mBoGmzuUBFR^b`O)';case"sv":return'$Zu;:bSZ+$",Si`#XX(ZKD{%BRru,]96,f1nEGATU43FO.VU;B$ld?R"Iaz6)t
nj.U4V
msO:4fo]PRWyUt-vMFk6Aa<DEIws%NznreJqb_qW$JUA90vN
MX7OVmb"xeFm@XWXiPW&_"Qe>dX_s>lnUv+_xcykP^#itxbGyoSf2gA+.
+,o
O5Z,"b.{15n6&X1,JGGCOm!2.,JO_S$[tOHGz&u^=":iOOUtq
Y$
uuIL?y=/;UQeaxZHo
]1Dvw`?
Jxz/--`5cEGL8e*e6AV#jgmD"*7D_:AD7S{u~_<+M6;%4U8#r+IyhVTs|AY=wm&#F?~Ir!c@4xZaZp`7in5Y_!~@w7=LxfC0CC`R7&@(P`Uy=X1srYGWSG/fZL|pF1UIbx(pn2q4o?holviW<iM4=X.bOD(iK_&M0a2dnE,gZ1w1]/uScBLnP*8<?!)h;)DIR-P1&1$AiT9dxXD,3bw&w5s)bYKr4r<$n"*DPpln:$M+$hs1t,ntcLD1:PqSc8E.FE
T&C-m{Fd?+6RuZ8V3a__@xSRDK6l#UL1&D$yUfc
77Ix7uN*W_jpTCQic(T0>"^x$m&NuJ
D8|?LOhjU<Z6ygnJ]7AiXef8CR"va+6-evbK{d-7#B,&;j[ibh?piDBR!fT"*X`Y=1m^$2%Y7@a6U&pC{JL0$
R#xj{y0urUPE|:B8.S5;rd+])xR*akXc6fumiZ}Jn)rjC:}Q,$i!I@[N0&>T@d=L;;$=UOuPmv@4I)y!N<z7"Z./NVF1~4l9brEhlX{#DX*(@=o0KeQf!<cJvT@0xDt/1>Ph;M0y|;zh0ZO&p5&&XHcu6@O3*0wlMP"+Io_^D1by8qp8,M;`xmoVp-s;b"IEv+JZaY&JG+c,,4],)8w8RJIDXTZbxv,9Bon=#Pvs)cu;7X&8|u"x}0rHWqQ0o-pDN@~1&_I:DDQE"Z=DnO`37rji"_j%YEHseMxh9mZAuJ6.fgw+F"S1w`*gSyO>!3#][Ge3+f2j
rIS]0(lf2l^zy1rn!/&}Tr2T2$xXSh+LGk1EJ7&OeOX2#p
}c4ur<!qDCx40s=6Kv"p(LaawL$9vVtn]!Ss]x)dKfQm[,P>#H+tU!>xQuO@JF:*3B:5%UtF6r|Q@ra0/2TC;q}sv$p:i
*2ab&`96zYhU1!MVNuCovAUtk7Ga$K!L>b+q<B.PlOI3S8bM=
aH:F%,|!~BN4znZH)^Iga%Atl+U;efnE$#XQD""#B:B.@fKn^]u%}?s&i(=pTgp=!25$us5T<"IAs,)nz_d=r5CS;"^AJ`w.=RNtO<_Q8R!"mviG|NZQ;u#1+Y9
sgS9okrrYl`UWN-"7D?,{Ze<yA+(z"of]4YxM<1E!
[%HQ-+mVo]c1h4%1A$"]uR2y!/{eQtm0#!
]GIAH[E[>":5T/,#.Q892"c9`v$8/5>eB]!rZt"2Blk2&11d``&g1}HVOQ^!8H]3Ip*<Mep|(s.$E;2@eUl"m
U`_50gvgtNkn(XYGn_>X&P:^V2"C1e-2y$v&oA^m/%$>Al!YHAZw"@_?/5*L(f1P22x`qV,eHr87/ceH]cxlh-*#LZU$ss!]$+P)e|Gz*.8=.$DSoBNhO{[pgg#VPS-8[R/+i=!*s-S5Ge#?!X_TJi=1@zxi%*7Tl|^ApKK;c,WTLojEMMj:P$JK/e:z+E*o9;lW-aIiP}JWjC+MDKoCMn71;((T(ja6"L$tDlX2]L/X8F4?aowFJ+5*XPS[WrYMT^ao]E$9o``B%dEvmorPJ#
UCX4ZOPm^e|?2i4wLSa?Qj]pLCeZ?e<B:Tg/c=thd4H7T3j@(0-T-]_/U#r`yrqU
3;>ex{
E"cU
*Of+Tk;!sQN#>sPVtlR0-1AyqYj<7FU-n
sA_kJt!?xY&M[n<s:+CdW_"e2kI$`{0>pW<y>!9`</"g`cD;$cOjC[oW*CEQN,vC>6<p
gcZH[%5B=*i9KL47;.v,OY!+n!>1:B$qs[QHwTR?g+:.FjkdE>]QVyy,$S)_/ce)l+0l]f$ta,kRxUQq_<>"FK>+*
^RnwQ%q=`Q
"[G]R^9+hC?fO/=)FA`/+(JG&sN6Q#w6uq"+],Gu@/lDlMJk7t`5*~$d0X?V%QOE=EeN(q[Q:nrgJ6C`6)2K=6>Y6__/*T$C$&xDaVXr]F!un6
8MUt1_O@?w1LJd
kq+(wz7neJU,fG8+Qf
vVA]Oa6&0X5l(MR!B;KxkiEPlg:vR1u:<)s7}g;E[^MV)*;X^K~V_8Ja(#=G2+
fEa2ca`D)Q0Z$s%Bg5e*m2PTZP3BQG!`U4]EnX,Zr5FQ"~t#-l?5Ssh1xX]NKK
q>6EMITlYCqKL*[i/oHt<D@iJ/z2">]YT?!d2,]+>tTCQKFeZeMZpB#)E(?k$vY<E:3#6M0+nf!(f2}$*oS<,o
,/#"IVAn(<]ITw=?/_yS5,lKQNKbnHf2U3*W2hVACDk`WLQ(Mq*y>Z<RUqqFAXnS<fH3?Gbzy=-F2Tn)ZGNq?%6?X8R3fBkDWAoqV`Af)P)FC*nvB]Q+d:?v&}mdhUqX*BTPi^DG@Jj!<JY6xzujvQJB;vg[$%
)6C[jof,sW3]703T*%dWw$CX7dpD:oHt+G
r."9b[i?+3jckCLDT{P?)>d2<%bH>*WoDW^zNmq_N&"?LTh.ju8<e%YMGVHfuq@g:8VwjBhL).vZJbWmm-BPs)NfEcA_NK%fQqrdABU8J$
;WG%s/Vh;V9<
5G1}x1c@kmW~_qFe>WvBIC>DpeaLBD>
*_a+Qm9M8A#X;vv$ukhrNF#WXi!d8w-W(P@2$m>I;g(LK^9Qsh@&l>tu/x.gOTJ`j1w45@1BV8,l]LFQ(g>,qz8cf{_tE@Fed3vnLdM>p[T_5{[(p!Tq/,dyuCs}Q{OE(u*;ew9:d2By,uv>
.aILbTc"q!3l?eH3u%PgTePnW5LoVFevG%?`(%><";aW.?5kbiKdri/7+`FEpVOME:7
7K|d4';case"vi":return'&X/;B]A.W1*^ArU1vC5J;V9mgLNdk7t#QWY^C_#V3k(?hkR=ernLc!)$(3"$4I!C{bD1*DS&Xw-DCVuOjOhr1vgcWu@?%]Q
BR7q>
v?ehEtv6g)ZK;=>ky:2a4ra^Gew@#u5hp,LO%X"QVyyL?-I+3<*J@>p650wG?XJD/r7XnhY2xanXNT%pKw{yqkMDg?nQvVy]%cJgo+u:
K9e!cgE
d)pPvbTuXnDYAC.y.5BVIn)]0$kVJyUamQ?y==yxAFCG;y^aWtMwdnb
Z9=nx^Zq0AH6Kv!|>It,To
4GavY3;4k50rnq3nSuoXmS.0t6O+FLvu
wQ$Qr[WYUBkD5Mb-_oV!Jp_[C(L`M4O;Z[
.
YA7(6%(&FGN>TF*S!GUF#p;!TK&uasm`}NeRj618!LUrdV(,}MVB|kLyHBa^?j=CA!t<{?Q>{HPEb.sBqPj6Ym{xQrU@Kc}kgN"=JYZ=^@FcVm2JP[L,q``@%o*5
!/dKN5mK>O`*+{gR..eh,|#XoO-d2t>nR@wJ]%cTuCkW&c=6Gn0]7>@5RQ<"vUIf,n#S^s]EqHe),q0FXwU/!4Q{`tYdEmhnH?kGFsJ1,zAzb3Ci5OsB#az&#uesZREPs=:Q$fsXnFgVnGM{`mXlxc9~.6S,=[ZCvjf",w]dJDH)DdhO4I%GE*Q=//56Atm7b&+fwv6!2q^%5X/Uh2[l9|6}qYXk22=ZpE=XsDK,V4H63NfVbbIF;wue[yNu9jFC=&ZOu3*C9~C_cD]W!Ls1
4C_+QS
?{k4XmjG7Ra&k5_}nq.O]FGQt?7;3.kP79)PQ1N5@T[M_j^5mq6l:}1J.iGtyc0*L#VTr1<z]EfY[TQqu=s=$]sc<;rFV:$l2x<U3%OmnJyAHG]|9%[!.T=aXpH1qNwY<%W!FxVKE0Q`07?HL&`p<7Zr^<*Y@sR)4+[D7PvUO1Chc>(1!fLfxNg&"9Ok^*3)Od,@yT>o
$;,Kt-``t0(F^:JKN$}yEGU<k5Cw/:f#3/=Pl!&Hl>zt)LKjN:%6qKU,A"ZQXgqUW4wQwE<8+v8Q{.uX0[4xK$pbfA"h9QUPtxxk>6]+uZO:cb|/u+`.e)^mSdcc!"{%Al0u*DaM"qM6{PBnB*;rs=!PL><j:IlL85J&/Tot4tE(NP*:^!3-M[Q>kZ)M.G|<@v3(C!:-4U~LLWeT"0n4,T9@?kh#~Y>HIa?*s1LwbZd;UH-(^A(9:x%eraL6RUZ^LD.b&-w".)c8S5"M}v[00[sBBc8amm;U8N>MojU1_H
-g2l+CBI+U*n`xTbk[2leEmHGc?1G;f9F@s]hUV_(.M_,qD/!Sv^2/FAkV_:141bQ`xed./{x>(5+sxk-0MJ[Hcr=gf"Yn2xLxF
sF;mV&[a;o
517_Dv+liI?HAz&T^OKb`x}b]@S,q>Q1Q)/".s57#T[JD
{c*1jkEwW>ETFA}Zm@>?g<F={K%QWi?I^l+88Zjto9&csdem+T>(JEQtq$5G`l_Oxm35ZM3aKT@*F.LW!P^FfR@Y7n%qO8QiHY~a[@j#y/A4m]-4:nq7jt%]m""Q+if
N??,bbGWy$)3c9)7jV}-5Jeu^>1gRXx!ukD3IN[Xi<hw!cyco>s"{bKq+@o<x1-OV3PTG>bya"tGz9BJC>n%;j$6Q<+105?AT21t%#lncx3p5P8>tc>R|kbj5`}$=/|i6=Z_M"w)[$OpJ(#(gjsHw$DH="o6H@gm3PoprSj?(kfWE^]87-":l%y1.N!:6<-"kq1`s(hVryeozt$9l#uI<i5/mu|.eT#dirb=LS50rsSvx<rNBdvtjO"DZ/`:(hjM=8c
MhS
R80.-a<kc^^$cfHvGh)azG^EgUtt])/?bqk;0<n%JZRjYdBd;<FO9ug[|d^L:Wu[(/Kr~i3[*=aT/(IvaCS]tP[E-_,k]3y@Bkanh9[DiZ1i^sYJBvAFN0+//ycU8%N$MO[!_>^0vY>CcjJ^IdjL($@:$oFc#+9e4<]1/4.M!U#+EjO;$u^DQWrPALJZ*7+&VnvA@2fT3L}4bEzC*.Gm4Gl0#
]vBD<.{efoZ8}jej:I%cHvqYCb$7w4%JRar3+a597J,sujk&.fq4lS~)G>!U]e-n^#A<]w3Y=+F0OBEaL4)(=.n1y6B@x:md3D~#g_<4p$mYA3-VtvJf3N=$2ol1ltID_2CMM6qQRFXGpTw=Y9qW[%E&c(>i!H1CnjghZW*cEu<Ke(_htJ",l&SUp>ct}/nFvf_Oy8WYj=A+@u-SBj$PFMr9J
KU`"d2cw#I&Xk["0jPv:}O{Ak>{@QGC(PeRC8e-QHfy=HJq8J*@fm&`1ta"R7vD_FUAe@R"Z0-;Y|M[2dT8T"SKU#+d7_cY-]^HGuccP:P#Y}
_eb],G+yKY##(fO0-_@PINIk36T&<yq%)*a^<a-0]t#hxcvlBtPIxgY5FHjsjfn8(y;L8q-FkW+0]s,5(GtRKdb[-9$8-K8!dEBJm:DW|4jQ0xV(m$o.q5j+i<B)pnhRX-t,s[N]M`qN*uf?WapfawyHVN#%=vm[#9G6!dhqs^pyk`TdYw|.#
/Z3t@7!M*O*2"c"sk>8402k6aU_3x&xHQ=nuH95E7`D"F!#h]2RG;73is&%CIdC
*K9=z<-?LdYgzcY!>J}<d(+
#5>$@?>Naj9oVVTnb/^DD>!Y1_8$2Hq0ZYsYtylrBFsIFa4@OT#@YmnYn$_rAn2VI_V`Gr{R1!m]]H%H`2&FVQ3PO%TAm(iZ|nSY5/477$8SGJv3T#VQX/Qk$3h^`Xn_eWh!PKwyyyc2%U!^-3t7jG!P2m3@>Dsy
eT./F*bMb~su6^&biVuPXn1Nl-Mp&T[%;|p`m6FxMAc*ZMfq+vFh/!I+I{F}gKDA/M_B+vne-[I}?1=25"jb=Ugy+f?cc&]aqRCV=B79*,FEP@_xC
Q|QbMSn[)V1L``Inc|]r?Pyb^b:K;%T]s$g<G.^gqN20=:.x.;
EJSCOcRWTi7&Bl!)[w>%-S$pj19InW0`8#iaaxCxhp}lpZO@BDUjrYA$yp%0-Qer7G6d8@6JU3;n0c<;*JoZ3]r^TPxW7MAZ^RE^6bdbjk8mJr]L*lJ5,O/DP;q,a>bTX=HcN^F]
<WdC)
$lcyS
h(W0.{70x[=Z:o]-eU,ra3eByk6(*~
99)7hU@Ex>d:%b*VxnuF5:~.#x)7}CQnBBp]A<J>AccQ$K8_<^>T"v5CpAgLXrJH74l_Rs0":LQYK;ly6Njm_H8s$Sa#L`:*r=rBy^5T~4`y1?6i+mKh_H#5fEXagWp-6+X@#D
6lDCjE:qUjx+H{K3#t;_O)?p:3,;XbUpUD?dbwE_)ST"AAXLidp^`3r-Oydu`cEwgk)F>,=#-4EV0r3F2Ow#V=W_,p';case"tr":return'.UF@ibP.!E%,oo;"4<08D4>3XKS3c3}ts=TNbO5d.DvDZk10;NGpcS
xfx12I^!b<vOm4ymX#I$7LI5J
3@4K,Vy:SDaiSJL12^X{OjcuQfmy%.#A8cw()B6bN0OIGi>e<CD+!Q*_)j4WT})NKxB`+oJ:wxoNxVfK@FefNXM`sI@b8=<^x,L$M
KN/,27p4;|bJpn!*jtsxuxmy%S6)c"4Frd*gU4#Zr
iX0Ws5$J
!IP#`q&@gp`ohgp#*h,EL@]<oJWNp5-B:Zt`@1eQZ[F5Bg@A6aJ3|daFk9(!.3+__>4*fCdqYQ9UJmN>f#.31Clo6lH&uWh<j=F4f:>^0a7C0ik&Sys7zP^jnkx$-cX$;I5*13NCL$mZ&v~>tPw[u^o<Roa"jtKL@
<5XW956(},c)*3erk<f%6mR`!vKrT`OxL>)o`=SZk;LeZ(SKJcmt{YK0
k5aKJ:[FW#KJv:
*GmUPpl*4(?J{sz&g][4l%/1CK(wG
!6_)vSOojo=b&=k#@L2`r>|`!oVA86tQf_76)49!
3Rd<-GU,Ip:|f,agD_!v*c5ARcNTE)S:Ld@":0=8eWKSIXx@:C^`&PXLJIb~v8?54G-6oxxP&ZO[%Ka-OlwP[
BBfp@g>?`2$9?mJ>
xZ%QqA&R)Z@8`R!"w!$DkNvyfR;J[*pl.[xI9Sqs:kcc^sk(&P>GC/e#EZjrrA6ZS%HtEY2a1Z3lrf*OUo:BuBev~*JY86]d]M=f.*1LoEym5ddsZEu;Hx)X*0q9Q@x#bp6F7?abwaL3x9eteN/"+]z,;dCDu*fK+7b%l:~I<;{U*3X)8"P:i=)J}(k@hq
+m;o
QPM8=`vA?Q0.L"QK<Y{bSq$evL@x)WQ0]C&*ys;#,%^R
No
,);ds1tWmvPWY4BfQIte$3k*Xq]jyrT`6Ef:ud
BGG<Zt3zhbZ
"@Hf==-Iq%dhXK)mv`CTcAwZZoiI#ECrN#oV0Ew>bRA%p15%c[&F^e!1X3=wd>d(BB&[=nKkIG_txcKL7oh*!?uUouMf!@wSCu0bD?vyw=[iGn"Q8T>K63PC.b&q,V5h3J3@BLZdZ[RSmNnt4JHi,g,C67BovF_.C?pGr/${@,Lq+K.!B|N?^Zr%U(+ECTW%]VEcKiuWRg$Hx[OUCq]^5Jp9QC]Mn@
275+Ip~peIzvd%0yW
Dks]|fWS4"C_j%~i"Cxo:hHhmM>aH`dd9$-p}IK7/0tgg!##9G3`M%-a%!fj0eZg7X2,;E$k"CaTJ6f%hmgs_9X!jv2Eb
/e7jER{y&.:G?dLMI0W3#g:oN.&k_HH+^5MFU8-;W>Wcjk^/[3*%z7{NM*r74^/o/l}6WfK._;u$eA}y`?S4{qLQubouH]Gq$Jiwtwe5V"4LCq
4%"d8>>nG6gARZ"<L7co6Mms29_g3!"#U!3H`kn`Gt9PCcNYbz!$+pn^%l,ph/$q9~&E$#l{!3ar!/sbZ_,U7}#WNp86*&[-F;V#i`S:7TSv$200wX9%z)Z
7/J.:[%^1Zb42(RBiWj`JND~4XEcL`eAO/_"Ct+c=Pe_wNgQBh?l-,:{Pz%<uo!n&5E|">Su/?4^Ke4*RX8F9"_75gm#EyA#x%%1J=InLah{]f+v(FFt/E]vfjP=D4=%2g?7+9%[Kt=`ab/*P}9$!d9Q54^@S,c$o=yT?Eu<e"P|b%rEG)kbtK<l_It4WM=?m0g20525),,5a]2EP;N6[q(a@!_?!arwa|DM?-s~z&!o*>W./jpD3Pp"8n^7>^mVIb@~.FMFkVgs(R.~+>Fy<]CisAJbJk%C:uT0={0NJ:]Qe:Y.IdE~,Q=sjV0
:{#g!7&yu[c6
DOc#~fndI*+i4$Xfhx!hzm)I|XYXC*|)n767jcb([L+u<%@W_(T9#u>O`e`EixC4V^n&3_*TxtnjTIJPNW*x8Q`7briD88$epgC.{((eM!Q`#"Rp_>=H<YSUz)|ZV,x:|5iUg([#|*EDLF+p,Xj@,fdBtp)(!m16qm%/1bs5BFgPQwYB$$bl)KOiBA>"E4(*z_B8q7@#&k-
<Q"yBi{:6v32z&+*qQfjCQ_NnK4S[FZ>rhE?G^`n]*I#VQ?s5UU.osvy#I?1U:Pv;[E4^GuN9$REI^tUH/EurUG9+&{&-Wxh*%:ev@G<=^
$m/[l}<g`^1"FrrX*X<6ks4D,E?^U,yLNK2fxl(fF0&yqCVhi_>~T^p,4d/(o+!JR^/2w;-XN2vus$[|:2PI>0Lq(%BJr8F;X0/9^Y)?44yoC:HaD:%~k@1-]%>@d0k^&Y1M!HNGDjvnieHsyI,W>42AIW53^X2:`x*E!L&3;FG.>slR]GdgP*>[CWn$1N6f35Y==QiBSCAsu|!arRREf*>T;qa+4EQ@%O.vt,Q$pH&*Kdrc]7Ax@Gjc>`otem.A.rn6h@16`N6gu8xr.(kd*{^14%(W,4`W9MZTM2NhW<VHnJpnE^0[vY8UyG"kg?WGB^0)6f:,(
A`a7FVdFJPmWod#t2y(m47MdZj+Zd_O5dL=ls{]5n%a7+6YYbK5y+6V>Ryte&*ld1tiS4`XUk0YJea3_a+1VvU,4WdO&G
<#[*a4NE&CecU"(F-],av8O0t=<+
NRhObA&(&wv
hiSJGaa*SXWyj]7E;?9H8b699P0QT

u1XY[U!#!tVwJ3]|6;5?Hcoyg}.l=OV$Ji_;<aET/".fr9
H?>"tA5Xj6)1r_bk!1b`pYAL
SUHY[A,Jn5-TamEZ(baF(EFI"-M)$n&j@F1^EYTWov(sXKX%-e=1,zg<tK/{tULe5KBC@7$xkC_lQ3AN`uCk;jEo
eS;w%I?E6g)nxvK/Refx!C;"gQ(0@rTVJDDHehr1_*o!~3<V1<tF,C-FqL8^,Zb;,%&@%F.bn8m0{/[:(-&G*D"@P%<W^@5QF7-@@J-5QKW<xSO*pJ.6<+<n[,enM;"dJC5XAqY(HyVO~c!avg[w`&+(Cl=>fnL>Spp(X[U9^t1w?/NAvCs.>UXAkKD0]EtgRW;H`m7V[CiwoISN3P(0u@i04k0-hE5e[0jZfRW.W6BoqH?*
neY,4e4x4dFB$c1{F[@z
S[%%FICy|NpC.ew^%EXwr8Y5Ub3F?[vQmD9S&Jo0VV/hrlcleDqED_?0dgg]
HO"-MeeYkN!-,ryAf(pM71%KnWx)+cP1wN.eDq,A+1Ncv
+CJs7x0H2~J
-KoTNZ7<MPmcFk+2mJ%VyL:dh>mPqjlGUv0%N-rSB@GkYS$q*WV|c/s)!bfDk6*"Lh,O"*P_A]NPQ2-ig#yqAl+tk
N&';case"bg":return'*ev@qg~pM(q4ko6>bFSpu@NpHj<
nF9qpTJ9|Gtro]`1FIm?O`76nCi(@8FSu],OAOpQ5rqw"Wy*;Pq^/
[OY8p=LtUxRXcC<mYT%ls)V`
WOqvybJgha]8M),YNsX7<[QNbHA:>Xxqn@+(By47@&d/W,AAXE8WId9yKh%6K~`rAyJ=B|cxp&iX:@QnXfc9pcZd42w#:,^.Ea)rjq<wD?ZI`Y
_.79!QdjJO^`{ulnsF"n`&mXaS/E|lBUk`6<jDQ=[-Q#$;^#[:-D|=)0T*yM7D_xu*7
br&lVIn=LRg[GX>gtR@I>Bi&m_hv:v$M7Hb4xq
nl)?1,ws]*9+3dgXeCOzXi^8Ml1z(3J/y>M0R;r4YM$
&e^="rxWPHPFhVe..jvsHE52b|@TcST`GP@dExLyBO@l>"UWAK]>Mo^-`&Pw5#BAd)NteIr]terv)X<$Gd*>51m1L"Eu.y"j#}Py1mi^HchL6`dXcqju!|kdxviM:ufW`h;JRQg3C~N),|TG47djLZTr082u^`-8tyno.=WHdYSWd}W3[":qbx
YmNWe,N.?j,O-Jeykq"sS2c>w*GG<_`?,M5)<+m11*}]XNSJrdv!`y@*&XQkkeJd}4vm1G:b0lq1eB|,FI
uB+3N]v3t_%sT^9Ddc;+L
=4o_/B%2D+4sm$5.`4SGE[E,VU)UIVRP9Gc]Tm<Us[:X:,7pG$US</N60OjTW3CTuz1z93DM;Em~@dh[;1P(W(R=C2iG2-/jRV6s+gp)U3lh,!4S<{V2*DuA/b/3axV7Di4>$ca3))3I!d4lcYV{^)i1>[^|oHLEQ?(ZO~n6D{,lUA*>Tpe>e,i2KVXBi]Z6KSk:WVDY*$!RaxcoqE;Y-/(+pm/nA@,v(=.yUO1VTD]fG*%TsC.I0gg0]"H&N<Xz$=RDfO!"a4NhTeqUJA@I9sOAh.fFDKk-mS=/`~a8-//CK2m&;wx&E%bF!*(*)Wkn,A-uT-Jn-pZL]YrS&Zn<>B[~JbP*c~(lqG[g#]`_TBJo2kZF(8Lmkib37w:VA{&E.mXFU}@?#z1VL]k`lDpG&k"4FdF?J?#bn
MWCIWEJ1nwDp=r^S%z4;aw`9lO8EyN*g-M+e;`VXc%eB
^dShf=`
!LDom
4@iraB=ZmUycDY_pfd`Fp[<p1##um(+X=@K9hXTF~`SCVHmR"z$$ci(O[jl!o3_ud<zWrgk[7ClB#F+;ImW*;t?Z`(adzCw4`G|r*i("6&gaxu"G|9wOIyKvE13q|ep5@_8jMe3v:AL>v]1W}:VeLW
F[d7oaa>yJqp&%U4ZoX]%tx6)B5+ogTCEMt^d[F#VF(ouT-[Cij;(ralpwC5"^9=>N;tOZ-6#KH4huv(K>,ERh1[R*)RNS9|sd#xC?6S9I
Zq}9|F4@"Xr7A[44Z#@FIj.TF%}IE4dt@.L7)?^wFFqaVSs/5#sspfO6S:~4~GqB(WxY!8<,933%EA$N7MmY5G["LEZ"4;i&?3/PF(u=s>tn72m,%_vj&7y&z=(Os;-c[#O++:pI,tQyTRk@$7F9Na;KpL(7a9=gcJ7
OMA/Za"^pXd@S3AO<Y[=}kN<.!>#%p>N@ZM`{aq8fk;Jp`PCc7QcbpL7AD*0SlPsquT%ajx@+$G!T?[@(%]^2.l]yXZZW#XBuK4m8+><JbUa
M^-3W9K$SAvr7~xRT,[[h2"&];^x>1GbBR++/(
$C9VoCZaAKB8*/$JZ1H3E[]-7p/#<pOok]6k9SyqWVu^IJG0!#:Hbj0EIl%#k--+67z1oUl<xB(,S*VgI>>Y556n"HG3J8gr%>4(XPdl?A
d=Z+"yI&Ivq]8$=!"qrlxhKk(:5sXHiZ=f]QuJU&ds8M(&f)>kW8kE5kfzVG.]Cc2:.v$z"9/ffv7J-q(@Vq=,`0)]uUPC.bI]t:.6yRe;<V?UTr![tfA/0JoRI7tiP~r"QY#YqT.6Kib41}$*jEuJ]KF%i;?ayL*}+]JE/zD+,T)z!-0>dvs_2C
BTR:|(HA)p0x|;F,^aGV)vgbhR,m+%!5@,=>++3]4l:J>9%Y:JbQS/HLe7Urqg"j2CJiGRz(R:>:]%xZ*-3-s(9$)fwX7XT_qrw)2QYRtV_7_>q2;f-Sv@{!!,Q5=L/#s)J@-<G.INzocl=C3fBJ-1+p"E{Siwo;2mp"ebv=!YA4-^UEM4GA[A}W-4Cu&Wr

aVaDb`gZENmWDqu=R~w*!g(u2IvX-H+K"wEM@G&|uR2f0h6ri2#3AyW@(TBuB|3ygnwL4F_U*_J{$d0/B=]41(1BMfge]72?1H3a7Yic-rbHPx"E^0,mAb5QXEy2Yp%D@:tXfaNCE?
}W*"F!".<KwqBMxR@ykZMwz":Q
#Q/T-$
47{pMU+X}=ThFLN.B^6p_M(Y+n
1F(URD]t5MAweTZ7cC6{ti0iXS@u6Ou.XeYxg5Xou|<YMW;(n6wT><iHbfbd!i>M"K*Yd*9&TzDhJK.gK.=2N6Ed;[Z/!5C*9o`,[IE6[Ho5M~U:kfF<Vz-&%j*sLZ%T+~.jZ1Yk#h>_3:>CI=M]Kj^nZbnXWiG<+<!3:
b1FA]:Eo!~/>utA,dNAu"g@$h9G=q9bY0L4.:,2,Zs4A:"wq>NN!BAZi#^tD@4$P5c``!hAjF%>?!DJ
QkO=w~i)0>f7gKm3B#^!,#,10qh^@Z!`YCD(7Db5bo-fC[l9o=J%fb24/>=*j<msvLW2oscM0~)fx;L?PxV5dOx4exD>RoOR-)Qp1/$J[FKQO#*IN_C+eSxj=fN4oW%ReRY}BGIDSDlYGRr32yl]@?S?3|3!scWv&Qtm)"-,>.(<aBLyO,a3=qYoV+3kHR*9*w4_)E(BE>G3e~`]5XI6QK
[CTwz^^*tr3Z=[2bX$.T((:^Mg+`7p>$=%"E}*a00cm<#m("GFlS~[^xqi2&(J1B2i2s^qj;E;kQo!d*]n#Gjv+6*:-?uwxl4c]%(kxZi*&Hb>}-*i.DFG1f<XOaWip!6OJKrvzHc2
QZ3wH>6*Wz5GknRTb)s50oMG?8g6SgeUYJEb1^:)PBj0u!wsOUZN=31/:_#pWlp
+v${@%FG69$0ZT;{6vfd.s)Ck3I"(z82iNVm$pT.^=j2R3dRi_u&I+uBYCFTIl9K%EI*
bM"1,0FE71(03rd1fNT2Q.>ucC3TVJrgV-zPq>U0TJX<`p;-8;*B`,b9b$ltnIfB?hWoMlzc+2pILCAT`m|T:PmPpXzioOS4_>suHk.Ar8xA|<T#UnaC."v+9aXIIBHl(Tj/d5_<VbK<7HvQ9"j`$h(wfZ
e>I^2^LqRo]rFNt%bzD{mH%_C.s<:bg:#K+@>h0F,x`e<N%F<Bb@+75XV{7EF_)hu2cy^i-^k*d[AlvAJ!O$o-[(VX>CsY^Xvq9(S|^Vc.>Z9RZo<5+w8PX|XqI"(fuefjhI`X#J4_rsXQb|?cp:evl9ko/`_-!8]M46,yv6Q$n?;TfXpC6oZ,C*I>Ag]K2j.Ldqv&hKHuw!ebI
h?Y)y*W=;
=ox*qk^6x|u"XxLu3fi>Ffl[,1&?P^51aU,I6(g24F3dM//t[yKYo[[TdP:VhF6X)JrEC.[&eSiD$3P/-XRGw$3g&S
?!f^sk#SB+rWz0?]H=fV%leKU+>N><,uP&6@rG?@a+Lgu#Nn+ZOx}V;uD55xSW_]bPT/I3^%7<5BQ(V@N"F6!&@
;*m#4!M7774a(=aoT$]mU?Geo8ihxhVerhH]ZvCk<W7&erJ,>kXKiOzmx+)h*kH
/v/G&[cnTlzlur,Dwh$Oe0jaaBXvg+F)w<SW:PtR7A@.yDvQArkKX0B4w8gk1?:4e
DI%rel%v-bDG1i3&y
1I|fFwAxC/gO*>HAUDmjBR!f6a,h++[SHa~7;i}OiZfqn!!tHKm<]7x9O
-Obn?3?]_azXq=pbJo~U~0
c)aW>w5,_:iG>iHZj/5#Paquk7Oh%cbRkT6Hq|4(v{gY^E]_2`*)6Dv
>qK-JN3^vnfaUZvu+k2ecP7RK=';case"el":return')h_KraLZ;.C4khB#+H4=ZK.O6S;7>ZM$G-;$a#L74?#ZvN4ibm%=f-((86Jqg9Eii<Qeps$=aKg[*?D
_K&ofUNd^"E^_R{t5?S
eN$p7rhcz(oFsh(:d_|bG!pl$@+cqL_=(5ZcP;Y77X/%*sPW
DQEea3au#rF6rt4NOpx<8Q@w**tqx:4hHQ?3[+09+P2Ask=Lb]yc1ldTG;o#_nG0h)@[m;=>tl_|X?=@rm>n=qsO9/X5Yo`4x8Tw@=X#_n[BfU)[x6_2D`NUl~y`0P4~<`AUpV$}s"qyLl"k0@_eR_O/Y:enRc=<3rqn=BE"yaV}F<+>Gz+99]<vY{5(5
0"o<,iDP$/>=-4)Roth|]?"!V/SIVAVNk<NV-}yh@*(*jve9N{gDRI!
QGj-i{R2/]Fvix*k=qc5d"CM"P6ZxL=QA:B4ERY#LC7>?Y,K&L:t!$_@LKja*$-~&A&1sND8E9-hnofsKB9$OM6$$l7?Eb:!#+d:cOlpB`9RZP@lbt-M21id,h^rck)]2:,NCqEZp}d<47oI`)P*!CwvD:7{w])RN>fr%-`MIpv$Ve(CwQVoXJ9I;O.|O0fX@n
^t+6Jch<emDj?5>bCi"DngPZ>,Uuc0-cvhe3lG?[62
rB`Qc2H!3g/]2)=v?]>xw!SZd!S`-5vnFD@{C8]89lloEI5SbKXeSEJ~ZE$uN]apF
oLL?S=C7t(916&"]^C&FD%2Ne_iE@uL@Ep;3<7^/Hw;w%<G`.UZQ@._svqB;
!h`Lz%<8GltV^!-kuW?.X%5kSuAdL5AvbetA85m-XqvUdl(vT6AU`R!.F-AvH@Wd31Orq.it0(|]~nNCnfM-xEH1}nf!OS|MB;>o(blqq1Xie;2`sB>Sfl/7N=1QJ[72F.Do;?>9k@{s1:*=M?"f,(&W)i?#b1yR6gND.){0H9`g:6@>Mi
N&E$5/UyEoPqQ,O=@!)3Kv9]#aId7OR-PI%-m@K8"Uy}*:nSm00
*5&+N
v#LCbfDYV1vN)
!;NxX;[/"R**[;p1>JdHhz^FFRR/-X-zmw-tT}n*A{yegrGI$?^:$>i3:iJcQgg7Ff<u#Y1obRS{`jv<^k!FRU!._`x-d.>v>j##uAd5a=f4vwW|9|6G!b.(6PyX*;%~tU2MX^)UUhcj<h#Oh:w`O]09"HG
9e,U=
P1-dEB^cRPxl]U9FlC%Sn.Byv)u&7`!d8kLV-J&]>d!A/e<6TKr%o6=f9M;WHM(b>pC_;PhteXaSk}#!mlN,6hI*;}!Abr_K!yLi$Nu%N.)-(RpnrS+eSB3_Jq"DqwS`y]R@WTYGxCvuw@y::3XV:h=&/-qJhOq6P,,^a-kM.LD(YMt^GN"U%6"i(_4m]rKDcDjb(v_hvB0PqZGah]2NZg3K$~t8G%CCXEM-w$P1D$qF`4>Ci4:x$?1_T<W@lvHW(C0~9r0iooMG;i,<C&&9B$"w@ud|K,#O71e_X|.s/`Fi"bFgOCZXQAjB)
9NPiK#TMt"PZ&FWU"d??qCx70^jU8~515E!r%K2X"#)b"8dQHqi}1>3#eRcAcc[qc"m91W95;:C=(&<d)?v0
q!W6<QY$+[7xjGLfQ[1S7.+um),&akvUojavp_j+eB$z!7ohKkAYI+eS*fD`Yik$)KN.L0<tb"kAv*@.NSsEz$9@@H_R5s>xxw]R^T0xm*?qt2+O;f=l^U45D$q5~+j
3juiVhr-;m"r0ozE>
UF/hYZ9n}XgnpGg@<f9w:qGfx3(%OCL:*%c$c>
_<3:xKi3
dn1ptEV35Ds]9IV3)OSgJ.7k|m>cu/4gj=fPF2No
8TEUa+:bQ.)}<u@]H6i8/tOP5L5RRX"|IOB"d9(oLNLg)I#F0O>`F`-4hbuCGo4px-$aqFow#/FXO3IqtcTx/KoAw#Pk#4hrxc-!3nf/Z(Ylx*NZ<.O+<<eZb=<=;S
i(&d|
)flGKRF9-2TT]c+H[fDM1s19}ot6CZoRj9J?e0%h~j4@(75B<?gk:bRo/<Z1KTHUa]dt"*Sv@-mMu^`#gKZCv%gu),9!L<$S#U].;*/pbdZSe=?Z@3nmH^M`P1Nn<G^"6Ptj6w@?HU^$LO1MaGFtcQp03@PWs1G,UXOT;S!pf1SaKEM^t+!;I`^6dCNR(pv9-FJ_9eunj@sv@I;LY^jaIW2jRP@.QRz=a#!<.hes(!]!bfQ$Z1HJL=I?T;Gw}@jcjq>:6!B+Hh}Z
)BNE^zU;[dekYi%L9UM-huf9dF[K/e6Q]IRQ0Oyv
w8x9#bpeP=IuaCyIO7s1
/CdU-VNItf&60gN{$2*XZ_;!/)7k*#(Cqe2#)@9DgK&#D20U4UP&CF+>6DW;oK%BM:#,L{+ol5cpL.;I*EmK2>3S3}@
xYTJ@mH
TiHxb}.Hm45ZP)v<vEAe8V&Q;6J+
:/L>Ffp*7]u;~mgH@#-XU?BciHML;62Ijce?wr7`O(mP]0m$Ro,._Alb!&qF.R`)n3L
ai/ZOvD0U_$+`I3:%fS)`Pj:(qAT*3:c!bgy>3xc|_|x7;^$#QRkl_xe;?xl;#,so
05^N,BChR!@!(T]1sj&DKOOV|No-;RaA+lnOs=o*N3Qpm/:cm%Smtpp<xL$1n*W]>)X.P<~#80rv;e4Q~uwYgM/AD6Yt+5gC/IPY[CM4LW,Hx09Ws1ts9x}Uo_Zx?%O9?=o.IcQJ)1$#2"}"s3"V|ZDu=/qeiP0Kbk5*<c63yDDT#-9?V>Y4hk_.h]-2w%|YXG{[|2e"Qj}m;<~cE.]:Fv6*&=
E%F6YC#E4,O-K.+&=6](I$.jZp8{j,/&O-?yg+:k4D:lK/SqBPtl0m)p,9dxW]88pj)t"{
I)Fsl9=Fg`ANvKMA$)`)Ji}OEON[
n?&IA>s{.8y""k"?B_
{+qrG@,-^O4`3xE4)g$g(^AB^x=-KxIL|6bBj]PP`xH&K3OZ-Vlti(E;dildv[NT>?-B>/1I%Yp:>^O1].fA@AX^ORC>4Eqjl_l:=4R?Tre[?(
(#7k?AjD-jlw$rQ#vvsl<1?2Zwfn4|?Dl/L!#4U$_t(CdtC7u`ic25#/>CA{=,Be/)4@u+unJ4Fy[}j<^t3m-,o8K"T,%hAJ$8I@iNljnCVKNbfb@A;&4L+:G"i"-*nbN/Q6=rE^BSN+dS$1.coKh.,DLifaGd,mY5n5M("&ynu6,LEGh`o6Ao2tZ,wJXF"U<hv:^o>e7[opz)x4Y}H*@skf)`>`$>$5>rne>1u<qom{82ux,cF/<Z;7%itl=VJAc+H[^Q^0-wH<?>k7>Fca+o6Mlb8T1"(LJHM/Cy](0Q;;viZxDd07^qS_)o->`~5PB6mQF]G^]aGfaF/E^,4x*Oj7%f=lm|O>%ZLCirZ7$J/^$0B1Q;=^:~K:Sr70%X-Q%:-=_eA$I+qu^o7cJMEyb4e{awv+l%-}In`#e)m>:zm}_FN;IRS!>fGZrF"WU:5}^w]$sm3j16*V=xD=C})70F`3aJ.]sv5U"/$eKPM?tR?9y^TFP,!B6p[bPb
mi%=-O=v}eB=|!*NthnrPC6K42QmPyCAA3T8+Ic.fn$>:U*d!X&?Wr1X>t5%zPWJQFPAG_N]IdJTjcmOZ*TuG_Z,u_qUesV9(:l3`2x`+3je^5Km7?5X(*>3*9f&},]3f1p;-v,e_y#DKtkO6IG=w?V)(J>*o_ji&T>SoeAxaF|`6&cl.Zw!0?VXYfZwHMEM`/m?`]&roXoESK=<Om~$vPS;5qiq`!"*h+S=ly?@5YH
ZM^B4UK/b+>R0_!kt6&b_Pyx;Nyr0b=N8]PV9FynpoR0fr{1#gmgr;=Ie/1ePj~$Q@wJaYfF{Gvknk+_1tzeN%LkCn5839s.<[tM/B<J{vGVldA[gJSJ/V26:7t;A4y@FA&Lc)Npy=2$G,Nfo&&MHa3Fd
1gU>LM;9^s`a<FggMY8N!E/LAUCs?I)IU6?])cNcUWF4cu&Z+
7p16;9_H~,+fKDB<m`IEGBMY33
^`Pij*MUUgxF9QF;R[hGEo$E*dno78)Ps];UU2@356f@_4jOWJ+S8]uOU0pp<@x$6N0r(tlJm-9zM5$VlCW`5=Y6"~e!4we)341{9Y/Y]X9Z6fvkZ9P0P{L+$4+tlpS)u1lDRA7JfEe,
WsXcl_TPg@tozttFy:8:u:AnU:pIEA.9VwE^yrV[eoAyNFb7&gvkD7p`q1D2)jGE*?/3P^^!EPC&$hf+t)9e@h?1zZsf()f/WsC=g;wo=_gE]eyOd0P`{Fq*c<S4oZ{aKy{j^254o$?OjbX5,vNmCh{Rj!CF}K!VzW:ws;%Retc!OGoHP;ijC$^cwq1EwW:gPWCh?@i`Gj$s8N7<9<K+&;GL
7%#ify;Wi3CwH2$=*t-<M@E0FHb{%:,/xo7$[{*HsoVg1QtFi{v#1iMUew-X5Un02FktL,-dmaat#4RV,%3Ju<N&Xeb/dGp&_606:76-54(I5us91
K9g`o$yo>v';case"ru":return'&evLUaLs&+Y,!lT&tK#=Zm<+M@PcB&>MT59"P-t4uW+k3F^@W^"Ft6GiyQ#@F?+jKokBB=IBf0I;InoXkK+p`qsPl9b>mGLXjhQqKhGqDUAG~xNR%/(w>Z/LJ#xeFn<UGWV;q@5=Y
}m>4=p6r`lHx{hXjq0cn4S14d`jAAQfwDY8NkEuY]on
SYMk#J*Ti_c0SOQEWkcQ;UGqX_TYN1]LjK]7W]fM0e3cWFb^TZoNK]kY1/
1j(`/osNPU,,+Kgm`;MNoNBe
0SP@Wh(nANwNz$mDwm2_>a:p67ObGch`sAakgT.@:B?@9k`lbBzu8TNccii*/:ib@rF`!o-8K5A(x(iYg0-4T;Usl/Wx<AeYFRZw@q!yoEC.zqWBp(IAmA|?y>lH=bJGx.cCj]Py3/9Nvub>?&;D?Ff!u^2)g_&cjI*r_QaE{!eX}C:-ABE#kGMqZo}>sD2Ut$LtZTB@
&(LEIhwJYKaO]F(.<ILVQ#p1eCYT>~W#N1/kxNBwgm"Ox}aBa`R&[O"KiD1_2?:#`Ov+v_0a)E+(6Ag!RlM{M;n=GmrU8Nn&U9*F^9Yn`Wi3HB5cRP:9@d$Ia#n+]dR[+?Hucb8!3SglmOIp7uNI+sc1F/VnHvBR$sXIJF;joX@XQ_xMOD71NL4RfrZo6o_-qhfQFHviO+t{x93G^N_`V/.I"W%xrih5l^wkj4I8H,cE8L.gqL[4_Ra=[aKMpT1@$VU?PF1+mFgYO7-3-D]>K^7DPR*lpN3~],yRqe]!<_
b$HU@SZ)af4o_%7<mV%km[^f5`[
(`*KQfv>#`<;njadw-KBVq+c/o(rD*N>FaBXY_1[OH0dP5m*jj4SuBQOz,:GPd!7"w<(FL?g;fCj9SB<Z3^U$bw,u$=brDj-[IYLv6.Jc.-M#8fJcA2$(W4#;I:-w6|v945N`G3HtQOMZ
woz=Qa>TP<.=D/bU8@o:`(sBlS:^"P)"t
,Xz(dy*w.Z&Y5:;3!JHw3d>o6:/xPhY
$M:)/,W*HLM5@n**TC&L:o?&.!pbVZ+4Z>_U)=R;SI&30qeRvKa3%i_#y2>5+tYM,T##d)b?]IBBEW
!.`%#WHe$BN@6/d,_v*s4<9&8u?*ft-!Y"!XBb^SH-AocUX;2D5%DHK7Ufg7+U"6>QTq0;d1Zs&Zj&C2<"Di>x)feB1*XH2z_1J_8zP9YOb^Amx{r6FTeZ3Hwv@G*EXQqzK
reS7RD1C%z]S<[cJZ+4LG"bMtF
VDAVWIQ22o<q?U#XwiF&[UCa3SK][3
TTI)-[blu]!ss1pTA-Wsg70ew5wl:[Y9MvYHJXwQ.&;Fm(/#!05~BrBrW0tC"Z""8K)[G,QYIht!fX;ff<;?Me0N<D9V2Qca%H.-=NkrYhusF48)G]KPeZc}cIVD;f2g$}]=&PTayf=]
P-mb>dIj.`1:c,|3pxm[!xdp3rQqXSO-*)1%650oS6^d)m?_l=3&K(WQ.3To*KT2rWF!}`A6E,15fn3%r3<v<A:o1LV6"/bWY#+oA]81WQ5mU^%9cI,MwYN:Ax04e:MBoI0
2Kn3A9%Xq2x_-K+Yl]8xDI/&CNOITP^?X9qH*CzG5vEW?qOX:ETNCvuq$#x;x,l"mY[J_o{FRT0X,-3uhwn-V
]D/R5MVBH^lu?RgNZ=kAk`bUIr^O7)gsr/}+)tJkDREZj8:gnikl![@8be*Cgaun!oFN$`KhLMB@.2Ud1@mj5tL`?&[WB`ZW^xb`&q2TYWbf2d?/*X$URe
9,jgw406GqOg.qK:QjBO%TCM(-*aAok@#6;^)kd}9ZUSeRyE*oNj5,clPPU-VrLPr:K0*bvvo-5pl&?A1`mb^PBpkg._)wyPQ0i02OK&6s0hu?LVMBgVw*HGUdvd
qWASm/?`Y4=UB![j0rz;mKcOADFOm7U/sE6
w@Uou
AKmghH67ps@?r+0F/Rre{/u7Q2)^-!7U
`l+/J!+dD9L+UORb?`9H"
D}#I:O2|v2U)b<bnwP[nVfUT0=$0s(DQmIudrR4$6gcU9[M|?m5fM;E(5wv<Rg-!EF6Dca0Sbp2zmInY@{@m2wDr4/7:V.F0t?%^nz?RDtaUK]?q3WmMAg/2ms4N?tUO>%3f$6+S>,9pA{d99FPc0,T1q&5]x]vf[6R}6AiK)*0zWtfA*Ue`%SS-B?Kkmc,GE?8mB6:haTb1KB0*UVaU&5nsX{4>
P7ShTLY?M$ZgxaWSa0)gx!$*Ix(n5(I[y5?SObNQ$1RPoaOQ/Z,sp)5FZ813G($+c_)K&ZH&<RQU(sX.r]Wxwy7xtR~.@TFg|fMVSYHclmQU!Gw?v:L@5G*g^Iq(D$Wy0f#9XfU
j=8"KFJ+Qa6SthL94FC@UKIG<>a<8JruY6Z0eJ}3J_>;MGuZh7"F#H$v(`iPHQf.j

>q91P(4w`f$8p8L<2s>*do">o~
2VD[%
*F~#N!B>^W[4-)>eJOAZ;D~i427x2m.:lW~PoGt)mg}KpYq"@O}km$30)AP0xf0lmJEgBhn8dWMG!
iaMj"f,Yk4Xa+emb(O^H-FKH6>;]c_y?_XgL$%Pkg:Zh`IeZt@:tMXba^cj0
Z?;#a>B`TKb2ayqtjnUML5!f2WkY#M_z`IJBgLI/$7)_=#HYhYEl!B!TRN3HrL_bH]>ICeM9JTNH[HTeI{Q4k+NPloFyk&]"73lwP{bE-F,rMlHsK`2Wn7Ny:|k9+qD/;oTLrMh0*>T@;Pl`RL@6?g#Q?rK:9tu/ArQwi!l[1OG_PGtm,"&G/"Fj`}^+Wtwx_Hn*v%1KvD99a3n@S<0Cr|7MbhuTjVJo]W9cx_EUk,CA`NA+GHM:tgE;iv0ACTpOe8<6qf-=$QV|oXR)SoI"e<-:kgx*h*eSQ[L62T0`SK5[8=g?R&k#l*"%2gE1Gx6hM2
X(7V#UT,[jjQ/6L>[[t=IRvflRKh$b*R
cz!5Y1?+rmDq3=:[>N$ec9i,69DqES"^KO[?s&A8eQ6J8-_TK@m{KskaCNgO7r3S0%4n9Sy
m-yq`Mk^
13Rm^hcpn]TfD@<_,g6B?i[TP5[sfHgy$C*b,<iipmr14hV3ME[omt5`&1FSr%b8owV"yHAk/nq%mus7RTH9`DEoQs.W.g~_cO-[`/1J%B[$e<osyI4!HM]m!wr;;yi<IbyibqHxn+i^VVD;Z3f_Si+2>BY]VSl3VV%LW%1I_YoRK0)i7D/@&vKFoP(0DAolXs
b|dH?QC3Jx]Tq`$*ZE<@"2ewyR/p@hNRP/mk_~)b<|[P1Utnkk[-M0gHn.7FPm>`mzO@/
Kvyt*-4+&;E=re];TD8+J*+3;mGhb
(huVYp3ZG8npc?VqoGJgLZ1Xaa9J+TVE!g!:.x**e,WwPHA6DDm<P.O5f?jp3)@CFPsh$^"!7lx/[GrgL6dk9.lG)FXag~^jBPamG!;[XRd[P;<{ak0ug1sdi#LN*+UDQ{:k,]8G:U^i/S.U*q3Zo!hL`BWrMR-1unPAiKdUe$iKPdMdmG>~HV&qEk#!f?7_2;"SB9J}8S`;UsBZm[kvLE?cc#02F+b#TFWDfyfi1%4XuV=`VLR4p{1)O|ZXsTG&a0kCh+P(/oQkQ*t<bE:]v)RbAsqeXO&r7c7>Sdtx[,h:#Alh5Kw+Y1His=x"%%q8dwE[kY:(tW;AHkvU)sEwU&QS(m[Wo5]PERR:C~hCme&uvN>V&CcdrcCCT2wAyaMf@xh_j-&8WI06LhMAM1hR#rdZhokcs>bPvU5,ccuF*X+>tEg-w|giw@`CAu4^s<jSsH`yF@pSofb)XQ){O)PHt0BJu8ufmWTQGlB0axX_y.>/C60olc(e.^?`9iCFw/c/<>Biln^
wg]d&4R7gydZCX*UbE+jP%3!,Ar}b1.~Ml<Us*9yX]*a>-a9jn$-eL6v,DvsX?hrS$=A2Ih>nLw$B$J&>+;f4WZW45B67Zl)9R*b0@"KW,kc73/[T#20@_+smo,9&;o~IO>=JxfD].(5o!O+5I=/4C;-wD!r?Xg
<zvK2P)x6mTq/;.sG<KTv)+j1P`jkwl,cSxYwf]t`Z[
2.*@$hbjI_MR_Z;2Aj$mbyhvI}6D,3nd9LaNaLb7x*mrr115p/YVjy;-kjGtd<!FuqeAQfHd?J3BE#P1InLfuxs{n<^p0oF:,Dw]^yZ
f*F?xkJmm.W!5c.|ixi+%~;*6XVm+?Sov7&J]Ryd4k=C2hWOaIa53lHvm&7rT-aK4dNLiSY}X<UH$6&+h,:rTwNUE/WU5|3f9Af=@ci3.LH^xWT9^MpHFdp8]7M2@d=/fT)4YH9=,VKFS`4tdVZ+I!K*Z-b|WGy1RY
JP[&_6#XYx+Zj8g(X]O,PU}f)@&V_AnSTWT-bky/zn--"82kOER=RN6';case"sr":return'"c0@qbP.!.Bw(Y="Hd(ht+I,^CIdTDDT/foI!Ter5)C%F(7^zG#Y8t*>@(}*SRwMSLtJQcZvDD34`h~4(OFONDv^+o!MJ<")n2:AKME?xfd5p[zHNeE!jK1CJkmiO>Ku2CGM.wcMpvKx0RR]Qe6;gl>L8<Z
~#sOwBlh8U`SK@/^=l
FnMNC+RPF}3Iwf3oL8A=
,%ux%tavr]fHPeO0|1WH)v_K"Mde_v>9OT]$&"*9FDPOzTnrPP9qk.$N+)#_mg&EZ%|?#m_2zgCkk1g;tm7p6r-79o0LwYZa~w
!Aj_%FZz$*4<0EdMIW`T0Y]*Yz:%mW2NP%h~w9<HgOq7a"(GCS;*N-nJCsQ1NwsA0X_~U(K$@meEDzuF[etxmjAU:n8)^jxqgn(Maw0D$Xd`#LtbZdbtcZCO*lNmHv:n?yD?>PS<);JI+`DuLs2vuJ2Mwt$]y2x01Q%Y6m?ONO-Ve=^.
$@tW@gdxfIW<)x7VpPrN5]yk}LgI"VB8lC?5?$3Nq8mpT1qCC!S,T7C5tnU:EC)f27tCMmvGz&U7Ev2X7,
$`#7qOnJU/9G#LO+XtQF`Wfr)BR~?7=L8B"|9`R;Ob8X+mhwLF
!;=aG<kL,VN_`xfjh&!-09o.plYaJ,A,P:_1NOyy$IIQ.Mt3O@]uC!wL|]lF4Ja

T!g@j~Mt2PB!@V?3[_e%&N-ek1*;Y0i%3nDc?Mtw8HDM4_;P?W!jM!z$.?!yC)cqZ{llrl1rTBkad9J!Z3Eqbg%herpo-B[p:*n,[PnUCrC0>pVl;k2Zih!*]F
:T/lSlyr:Qr/dOHj/"egXW>DQq^$61lZ9wZ[}N,apwRw>n%B}Yv5UoMq4
gu#+MBhJODu17.qz&ExrS>??tbR:i-a$x17Vs?28E^S%t]b/2qzJhWt=03?y##7gELv>]8lve^k2F:^;|WV4fm^Izd#ukB2,})=lt^JVLK1e&r/"VbT;g:#-#Xkj%w(L3Dqjq1.81B.Na19VHe*>.bn9{I6O
SZOww[xH.mSZ/0mxg9tjY~ExeY69B|1=jWP:6DoSyPscY$6
P%d}%S`xhh@BElM+OqEtR;Izn>@yhlL-I>@^<i15^>.ktn1?pSDHorP=p$1Yh=>9q"!w@IA)e^y<T4Y
5qHbTlK
7ROMF4%ke-cH#5K::*i/]=iGHw<r]9<s#n<sQKE[Yh@8;}p0P#J%z(WY*w`^Dh6_G+H.9;wiA})%$/A6:FVo3j9
`R;fR<
sm78_FG-cj"u3l;HSv3rE-fbryAql@v;M`3!n+?6k"VYZ!*CMk(>[JyuH;Y9!xto,Nt1*`)bAWVGmkE>Q47n$%iTPFD<q1vnVG"miyx,?np;*0su.d=,+r`[/WkTjeVD]:@uPc,;!2H>3.Wina]1KY_
Loyp,r&*JmwULE"UMY8ON`8FdR#Ck4rdf5Cp^k7J_-{He86GptQ&+rr(^G*pTVt_78;QgJ#i7OIVGMN>A`jTbPLxzh4LZ4wqDBxI5Sx7~k::S.3A#h3nl@01IE~?EN%fXf_=~F`O[oT(3[s]KlF0c=YYkm(0"8h!+5wRaWjG$LQBNv|LHt=R7?Hj$FRh4-a>ZT$p7=av9[zP=a[xsma^3yr$KUP,*bzod*sQGJxt[EGx*h#h?SS)4aY671H#-**dO28rJ6rw!:{q+Gp$x$~xY>W<jF`48P4VL@IvG>uJ%QL(vQL)1k09cu-W6qE
hH!6cKfR=BRL!?uTSFop?un.U7@T$;zgH"Hj)V}Q=8}/rld37q(;u@XYAbUv?Z`h>yL]!@;vLpP*pBeb_/}rW]gi*X:Zp#h=[?ZqUAoOHwC>*MW];@FPn!%Csp=
>_Clu@9"Kop
r<ii(go:,kwy0b|M~.d0ybTcea
OAT:
TwD]*vp0_E`P_BJ;eU~@
!((z`Dbt?lW]3hGWBN#uT6^/YE4ctM_;.&YlC:q^$4dSQ2x=m_pgQ@>]nTOKG
wy]6:<n1rmP,;2/5:7*vy{VQDc?"&TW:1je9X|$H8)shVs^Thc1<g)(~
.@B@muF7=H5R?OhV
BV@
<O`t->)EU,^22hH*X=GJ/3>$r$FB=0TMDg+R4#kr&Xc96JPB,6+"QNjG#VDoD$>f?}*C=n`d_e9VE5YY.wG
R$Ngw/4vnV5dsAvz$VOs7WaB%{cXx2%xq@X"mR2Pf(#aE"6c:iH/YpU56~%Lkpt(`RYFhRo-Vi<u^8nB(Y$AUu54
*
r=1tS)EZstJ9%f;!]T"
"TYiR;Sb;G];l[0K(B(@x(VV
@;?._:+4[kZ?b)94
Cr<WbK[7U^g@}
^@sf_=Q]y^dW.D^<?=Sc"[9`FetXYGZ9#GY,T,V*_=,4)v
yL"5w)ihZCGu,N
Uu@(PEc1,:OJ[ObD_oGc72;rf$B6[S$R3hxu^x3X
SX;Z3RX71b!fKcIMV4yCjUG~^<wL<
-E
23zZD43<N>H1Lp%LL5eyM(fO4^ID3?h`@Kddh=x)mGWOx4mG^EejN`h9]e(qz2.dZilTXdq+j-mu_mTW<oJ$NV,!jT@5WpzyXcCua4>95@UC}<igGp
jY4gKVD7Z[UAFs<WmmwgJb1X:__&j-a#0yDOT
*eDAfdc<uc.0=fopg+9c?M_i<Z5l&{P215oF^92`jCGTRnQ!l;F`^]xig]CZxXAT
:-v!{oX:RNxKlT"yA;G?(NtR,;[
ha)7;]-g-Y(cS)}V=AM??l)PSFVn*?p`@:/;|1OZ[nvOxppCO$&!dy["uoz0rJQoD=pD*R>2|$8LNYo[g&5
XuMt&vkT"ZFw%inK9/oASPK^}t"KviMInrC,{:hywmJ"fYj"!,U#_Fk^[wcbpR,Nna)QqtA(*6CDVylX;fut
57
j.+T+"mh#*oqP
%e/)-"ecx`%82;oo5dc*BsZ0M(^#2`Zh!&nX*cweWXPopQ[n`FQp{>FfSJ]DWEYIk<"&o
Ec/-DT~HRfLT
yjA?o;_aEFFm:]s!T^G4yJN<e/<#R@L/-Qd/<"[I7ta-B0w4VW)6k&4ok`#$wTl7_TanXDZki
aK:F8xhJSJA5K3U})kv|1"p@&JTtC^0E)J)8V%*mk_f8x*Hw19U*G.syqi+qAQe.f|l!<Xf+epD@kJ2qrlY}
]]~dd[<:h%;>rNZQK,G?3^!3zG-*a1v_5Eot^qTPY?2Q$.S&Ks!JD4ATmy~nV^P1+E"3-leIVSgR-(zZ8*V6F_`[7.t
z9J+bkX(*+]PcUbIZb9syQ#.%qP8BDxP:qVEiXm7ZFUK"!jgHWbxi.8w`b*E.9OB<#X3"`*"acj9-fuFkp?oyX~eqD`(>t:^t6b,Giaj.:1m!L{3zrde9SmWBc^rT3Z=5gQ=W&_oc=-MDq741xI3?
JFmqg5UlV-&1^^~/Y[!A}%0V.kN!g5qAy`he;^=WXBn&zcm;dxA2>i/Q0KaEXqD=z3&SSUu40,<0#yKIjdOKXGpeS<Vw8j-vE$OcFRf@d(^E*0I@XyE>Z+5nT`^6jG.E!cH[%p:IPH?-xrmn/APy#[[$
.`ji.5hgbPNiI/jJ7`]HX
aQ3/$7vQ&8`}#P)/%_5/4":$"#Soq`PE*
ifbnUU0Xj[&DwRd-IL)FMk")11QBKj)U%6/Qc0X9J}c5b3$9q;8Z;g0z42^/E6u.sA"M3F"S-ZYJDs5Z`]/.iVW!]-mS4pY^n
Z7)Os6n3H:Ayn,k@AARbx>,``k:?bGRuMt<`22`t)W1|M2d^M2w?
+c=M-pK3OUD3Wpx/E2XbdU)eiC@&o9$-k5I4;*iQk`UOUDh8MUl7T%*Z=0K#;
F^CQJWCME^XL+]vy>)2FjTI<=WH%~2/eQ71Y9ZOknfc=og8NO>Ua3Js*XgpK|Mt<;R}ezZ<B,Frr4lnCdI|DFpgH~am@)=BIWr1[DMbM%@7v@mwI2L1q5p8t948[F[sGf?oK1G&I_@SQ,n]bps4B0A5(#a0[gd*[D/DVtIA0vCQUwj4tB8B].pi%}6tb)wH';case"uk":return'"ev@qf{p=(p)js?]d;OoR?}VWJC;l3;SDYio*/-j$Z{;?$m#hQ#9gX?4}qt""8;$J)Ch3#lg@s27@fV#Yp^Ndm!H1rE7@crjuh;L.iXVtyv`|y=OU<kZDk0_f*W?V^
E8m<SBVR+,c`3c<HMpYNUS^/Vi=tm%dWuUgUofo?HS*IwseuKg3q5XCc)j=:sJ;8wu8j[8Rq3Qt#QP,"B[L;p}3S],VqIlCSj]@]*g(5OP4c<
W<+[T%y"%D8Tvso4ce<!uG8_W6xNB{HauVvmbgyJGy+x5gEfL}NQw~w9;EW,1oU8PcdMm+G8$6D|^U]4puCU]a%;0>sCz%e@tfM
;%LRYYDg#KJ^NICSOO6ck3qkDx$[z(T+V83;)f#>EzjfCGfl7BG#.LL?%y
(r*Ay
?Ov%B2J`4,Q0@G]-kt&QdUUq]*8=arTy11(3gGJje#Ex+s7brHjpkKFtwPrQE[caLu7(,t^>l;Lg!^W`,EyD|c5U!=CblgV2*6"g_IkN.IR&(XB:<V@`Z,Q&BJs:ul`+7)KG&5
fVMU(q-^URpJXwdl<Be)f4W
t|GlPR1vt*g=qp<=m=DJz%uES238r(pMLA9L$~HULe:xt_Z`^ul8;}.L<A`&O4X#WrXIxtnDCY)_<p
U6H1:DnLf-v+"C0V}>2*>WR`2=.7Y1jNo5JrHB%Yo#@mEsB:`4>R4<mm>*!$ti2<ya.uWy?AIX
G54d:fPT<(14mAg)67D3!YdB."Od?,]LLVO}YtLSFsc;ff?qN?v0fOn,X]8(bN.~lCpxpiWwU7@&`0>^sSdocgx?y~`yuw,NZwe{1MIX`bH$]c(<bwq[<GQjQMv#*YQz=hywgA=fnY^]6::1Y&/n(KZ,<ZhqFwpN
j`UO_M>)#ML?5R^C+x$*;!?;2%eA8vTt@T94z$L<nZ1KdB.BNGdhOt
ZS5M!WL?s{SP-N`M+zQBP%[jw|@.1"N1hWE&^]Q(0m"qKC(>aTIm]n=}_qZuPd$7GT=X4~Ss"[@2A6%r_S?+?xJ;OpCe9F-H;.s%6nud5nlm*_mg=QB$A$3fGtI.>pG&&%n41kwt$z";"iB3(NM%7z_nxe>32cg6hvZL^$u".siIt)2>#%&|K7n7WAI!uk/s?dR*OePJB[nR"W$>0WB27E@ZwH=/W:Dp8xcOL
-Dflg>`Zjt%9D(bd"q25%I*!Kr[{4p$`kAYvZ`"!bJnHD4/=u-a]Hu"W"$gE)9(K<Y%aRr+mn0yTcWSeIY9iO$;/oo+,^Ki]MJt8V0BXEF[_?E#Fik15i"O/0+G;JCh?@tbIZ-F^r)iFV//VJ1-hD][H;9$aM^.A#/3"3)KC"]v6T|.*5@rsox@+;+h)o#wav"@:r(#BrRA^w[X(;,We%r5$%lDQj2DUhD2jEz6:BbK_4iSomS]&M&Q+IseelB52l*&0N5Qs;:P/%Rbn4OH!@WIv,?UgJ{Jx(OI}e3o9uDeX?&<*/J;UPd*~;WODr*l5k^Nf0::@plE+aECwynlQ`tD!tbPHF0*{v]>@iPN}<[Pg,:14)K:DAE<G!9VS=MSh$SnI1n5"(WQ|a5:X1WH^]F]cqQ_o9]H4.OLH[uo3
!s{7"p{F.(~!e0?9,rLwRDp+<UHjuvmy@cxnttQ]a<f%#55[Zy!=B6KAdz(3]@q7Z1e4!XvaL%=tbO6o3l=_+,V&C0|uo9UNAF,QQ3-:{;<WuQo:t%
Gl&2!-b-:m@dJi"KLO18S]h|I8uF(b_tEjyT`t.1I9h/+]9UXmbiB3LaZ1Yzi
sTv9s_Aex++eed>9RI]WW=ZW2A/%9xDL-/<6]:B>STYD$p-3I1?bU>D;K=^%pI(7&FN1I)Ctt@Hg@zw@i[Vltl
xjJx8FS
Q:!COXlQ6Vk:
#%JT#EZR`gR*i3VSA@WtO$EV76j9!S4jgzfzqy^L47N(e%2W^k;s=]%"n$La#sVp)45[v8hi+-:,<IS;WYoe!
,Z`ZK8(*&2-kBTuP4_aqEwYlD^bf1`P0OYgHss#Lgk?AiAgq6Z$hb6%wVr!]iZ6e72pq*]GSm}1(@1j?;Y^OOO>6`X%c9s*PF?_{XLri3qEM8uqDD#sA7K)pu&i$Hu?h"nxJd;-`
%4}A"H^j
Y(h_<(5ML".Pp-*E(2BeCaWkyg@-NBjH/K2mD@[y[pFa
y@O?gSAmC5Cq~+6C/XNop1:Pm,iCB8s%2AaVp/qjkGOD,arsirrC:eeRV(1xFKS+ajzGc9S_RfPe3N3#v?r3W?A.G757~&z:l8RF9E[r6I(VB+He`p[=]
IhhZ{4Fg3sU`{)*8)AP+;b7N;m3Ev*}lx1K?RHO.g:7Q=)jO%bR1O>gav6.w!$V2&y~U7<"5HkHC(.VT2SDS7@v5HXYHiG6CD$WJe`*DSMYuO+X=ls(G].]t<5:nO@Vx}.%mv&lU}XoA].y!D0{]~)(2Ric@dDSZX^_H:<5
__wYj-LAIsJEQH%Qlb`NQB=GUdA1lsxbiqGT^+egeY}d4v5K~[woc;h>Sf^``79EpuP?Uxy(<pS&Gj?)9-+^+4-KtCmmh+:)?PxHZ,R<J^)nUmI>dW+Fj5_k
2=mFI=Hp+QJ6g0DOAG+Na*y1FX>;Dom)`ULvkSsqfr+g;nrY:NShm|px5=B/GTsVEmHQ4$$~bM(1kC+qd@+wt@40B`=2DLBz:9Yoq[$Sx=]Dg0j>1eHy-!B/W^n[I
*+4eXX7uHQA?iuw{6k.A7])Lh>>U!ikb2_?q?f5@@8ytkB8]2L/Ix$rqJ#yGh);&g>?Rxe]I"i$S9~4Ur
=uJ!1oU9d[EV]"j6b;$6)atx8^RVV:
(j&&EMC#TAJp&&qQ-Ta1F9}5G/pX~5kE`0N8~`p9{/UVXsK6n_(!=3EPNgNL~$_TVgC(
9i^EYjMV@1i65ruWSRvSgS^t#`xl
HV:CVc$F%G>-tA^X8@n=2GSR3Q|0g0_R+VBnH76k)6+P8=-0e#_f$v[xBb<KFBj^Alb6*&rlXg|g&03C$N)Gv+8w%yju%TbwvNd?cR{tI`Cre2>q}[*<1J6<|r|g`I*8&)/Ka.i8le14ld3no*PqC3`uJNbK#g)
x-9
x=gb)*Upn6FPDA_)3Lt)^g%8(J#12qDKY(lZNT;aw"SX*71rJEq<r9#-7g<2gTX*[P"`euhx13=s#X2xlTI
u;xJ7qZGiUh$KnI:&JLNsc)8WmY(%9%e)mVh2OU_(KX?;Ok-qm)7R/@E$dr6at`k9OMRfWvX-WMmwdv0"AEVPvN9v`eZ|;]p?%t(@A06ne164H3*%8w-A?!@iG,$.)fZeN^?S0{.|v@M74._{a~CmKpv;xXVItPgE,[@arR?4)yj%noo7E6.)F76,p$yNt>NAV/e2Z(WS$Cb
uEak<-Mvu(]O2K1mG1>6sVG#3rw%^B>@&~bdZ3Y|^H^a_tiDY5gnRn(FO.U*@2rrd>tyj9v[.Yw)owt55
L8-;5oy-2hq$*>XDhV@stzHPB#M$nIaLYjyjaHeDs
X0>}ZLBZXOOP$pU#gdeA5EIU
ESIAS+KTe#<>FV#P[LG9t9ycNJj).*^TWbCu%)`z)Jtnq][d47kI.?n,,9.mU5+)HhmAL*zp/VZEcRbdei.^E]C1kGg-9av>=friSp49]oz0>m<NUL?n(C8*fxkt"IZvaE"c;m<4Rbg>]sOc<:v`w>%!whZ4r.qQ{8xQ|$WYJ)>Rx^>ARu2fk6;eA>X=>Ce)_GFnTD+^#Fy_$YX)t:MO_
3eMi3y&%1cTn"PR]b&v^FK/6KJIuBi68/qV"ae(Ry"a=.Rs4BmTtk4TKF)u6ngh43WGX":DUt3BSHaPqFYp6Mi@LrlC7{M#K+cM%E(}egaL2FD?i!8q@/V*q~1"6.g/*+`qhS9SaL-zmbM*hF
4)?t;fub<MB.yfxHZnv8b.?gJ<BAVu/]qsfONJ$GB_"$YDpNU`A*aox:vf!n~Ocm*Q}bfmJ[`4vNlNtnTMlGOMXSD7~n!4v0!,66$Vyvt#LGp^``{aUJSUMR<DMa$ZjGaYZ
"`nwRc{kNV-Q]a+Z7hiW$qjb2iRS(LTK+eXvV[8*L.pmu-=K6CRM;LaQ/4i6`x@TqJR]l#Na?
#&gv$)o-?y0:$Cdx|?,O|,Wk`oTl73[b)[|yy3"gBJR*aP|j]Tl57Q;AL3nf{BHa<_5_21lWD71`sIOnIn33AmBk+y+C1>oKZ%)I`7CSokRWT?AFE"AWj2e@47,i2_@67"IYf';case"he":return'(s`0:6KZ+&iq4.1ENu"<
S:`d-[8t)Itqh{Z
egQ1BVSGea:uvL
[c/b=4*jkZA%7&.Ppu%L:xYM.t.!wk9-m
H;xw71rbwfxE($r4!cVS5nJH6PG2i*}.=lzi(5o,TgjNc=jFP_SRpJXB:h"3QO>BLin)Y9+dOuT0t7#`RA,%5HJ@LgObP"Jsarr:)T$THa]DK)GJ6B~HFp8>52E^/!/80ldcP$|aXvM3x1BDI%|CrN`?)-tP*`OJb*q
6IlgV.k<Klc-K:v2gpVXKq@gI(>1D$3I[
1;z`v%8y#jOum]OCwOf`,PNt"cj
k"(<w.lP4S)]e@#%)FLPAI7Ugr,=q<oXIAP
eI-7dCa`T*y^Zgy(opqyrH^E/3qI{BiM+5q
@cRpXSB%rAmw9m{A=LL5?]TmoQuU6TD0;?8NiG1GkTKL@dh:5M@S&2hrHo*
[lfVPFl[w^:"Q8<?kF>
=rgIcL9!!<,$:tCo5^<[fT[7o/HCc<IdzVpXD6V6C1R"VGZO/$:SN%U](U=qkbg,u:fNO]a2yG_,7]&InNnS@u3VlLtid?/CH%aI}4Y^z@tuwi|2^b0096r*YwZ+R3fluHTf)_Uiab8IlJ!,7S-?WOtZD5d_jXz&&L">#$D=ZrnF#6Spbizf;m{nKBCga6wj|Tb<?]Ee3j$AtDLL..>Sn#}d`4M8qXZ9`&I[5ph#~^~-;Yg-Oogu:MBo^.)3GJ;fWNuG?i!l{#^$W+4oqd&]@p*12?<TWhwtm/#;S
VFz[d36irq%b9ipyxE
@;lz0S6=nhZVGFMDxW@,dk4)7Ny@uwLaq{"<<:dWZ&0^dt!f]*RRDb3_gT4b%=gmQG<qZt>;t$@.,3330gc54zM[y{OUe"Q1AEl
;!o(a-l:Uuod;J%f,GQd.QdW.Qx-Hyq*QK=<g<9l.[X]B#wwq_N#,
yAO
R}Hbie"$.>P/>vHs`?xz=#"<B:u4>=4Exr9ksAY,T83vAUo#8Z;SbrF8pe?[R9OYZ^BJBCUm5/ff)8f:qyj<E{t_O8!|uAdfh|YxZX(IZ`<a,S5t?77omij"Op">>E%_7`Zdao($PqiA"`ntt]D/[g"mwVH#T6A!c0HX6V4y[{gABRGTw6urhb>OdO]UDLiF&2i#KH(n#oKn:ben(xNZh>ZhM?;}r$2l6:==x<=`<%<FILWB%UF!3n9cjIHJ
#CU))jA3VxS#*y+>JFzf3Aw>raW;es}Qpj$+*i!Svb6b1Er<6n~?[Gdi<]ebb[w%Cx0Y.@ci9)^<MUz(Z9s*e?ar{v,jbN!QB@?MV-|P#qe_:${FVy&)};N)1J>mXg1x0SUE,.
Ku2WD9)UJi7Kl0/rT%dsu8gmIPp[PHA7F~4isXm,g->^GT<8!W4XgJA5W-PNlLv(QjFKtZOlaqHFsvn<aK
|^CS#uEL*1tD@1419iI;Jp$:<tGMM+s2f_|A#jM=0dM*0
Y7bSBOt7fDuD[Yl<~t.]?g+;X5!nJhAEvJp]v,C1Ke$/AsGe4uxN8Gvbkx:_uH9<SsuI8Y/iH[w3:E@)H:NcXgwJsSONQf/2[&gRTkB-u*30_[*+WOI@b]o9)chN}t1:#M,WCjFyfXgPjyBXg^N"9rA5(C/]Rn
9f,|IlGLAKf3]|)&->lF8_.^adPEn9;"0OSZ6VK4%Me}Uh?<J1.uBnZS82Yv0j)>O9)iiAe!o<sHs2<>(/lRR
y_]u>[0L`<WxhukJAl^.GK_L(D(BFkSO>}jI,#Meu%w(b/p&=mKntpOjALY,#z
hKPaYnGYi5pQEj$I|%:%fj]X9+%J#_.:GRMm/I:RQ?"_VUdNsP(2X&d^q@/xP.8A<6?L-AiZI^}KEQ,1aB!/d`qJ})~w3+H27q3P$u/I_k_cXXQhQw=`=ae,i
Z5yE$_*8Fh1G!_*@)===yLTTB/8w/^^/>(`<W.xWTAz]rJ&K~j><a7xchwqCtPf>MHP2FeajeLQ`Z,67Y]>s0]ud
$sU~3r>iZ
cEY`(YJ9pli,2p6U,B?^j>".w9]J!0RQKMn[@URP/K7G$IET._"f$07tp!*i6A*I`7Y]TU6FQ6LE<wRtY$4=REeoYxTekeuh?4c;I]=}&#.!uKk<@ji@.!`*Lht7N.DTtF<m/MJhH;P/F$LEZ{nX;hB:YppJXxn3^~>AAK!!a<L/E@p~6*CcI^?&VIZ~urgr1^fiOzeOmt]$7{3PG"1*AVI2YXL9xL)]KmH?h;*iYs_OB*Ijg,:<653VFy2dhXi3;CQ6-GgX<LZQ3$6B/5MiwSK7irYlKMv"kBF/p]R93b"<GdK6R|BGFwU_Yr62Qsj!@aoQwyBQnio"qk>OOVa%YgaxDaC|>+.rS9lZ4_E_X1i5.wO_Rm!dLO;BJbv_jv$PMSU6;kjr7U?[SLtx*e0j
w1z&9d*6T15nkiR?%dgtLuVMDUzrN1Hn2(v+T<cMVZww&5a"2`3B#y1$IjLo/3UV;g^[]b|)q8`!JwiJ]XR4{0s1Hi0<i4"q%P=1!(ojFXL,m)}fzOr,Vem+GB;[=-s0nm"m4:<h:iYe(no2/H^Q?MYEHY8Y*uZ@c;b7lFw9)q`d]>BS@*%ZF=^yy,@';case"ar":return'%c0@b5Lp=(n@+r#$0N&YGjp>-s@o<8
x`!VSS"~PPByeVeK3)WI[HDb`kD-5FuJirf]CkqYr54UdXj
cl*}.-f_-y;|?vbMLun|u(mWl?t(7`41J7xkBO4*o"x,o$:isFOHVrn:DFJG&=1hx;3Cmj7wkbmR)iJEK9e~v0DyV,kF1Jq.F8MlI7en]QVEMX++.hX=8byVWhwm%pQ?[Hh3b
kRcZRBS42h3MEP_HBa6|aIN{U]Z|nO;EUHSC@"B;y]2K:U1CcTOA[DOrAL/XVTnO7zAt#(wVV`n:+W3hMHt:"./rM}&/+mf:n|j_jkMgtDxH`ZureS._^5w[C6n^wFL0t/lKQx7KK-SLIOg
wp@~*/aio
-(m`9ygP;Sno:.YU:pLUU#RK
$q1PpQcEDucV&+;;_EOsAbI=r?8CTykSS;4GMtwrshTz)
khN1LCKNtl72gu#MZysqW=$xzM{
_Ri0;Ddx7yK%rVf<SJa%,x:`rIcwpO6`uIMnEguB6tDbv`b0OK&d6A{w[z")dRz@}X/YY&P;}P!XcEVtN)W<ZrX-O!!3O=ib><|UDK3B66bkEN~6@d[KNqF1/6)u@p,=7J&_W,mZwE"J}94h<
H0Sg=vUoVfBb1"Z]]XzT[NATxfQhjZC>X7m0HvxJSfX1^u@t#qXtOu:En@+wB2(4Q%x[;sz`BDp&rWb^u8]P|TU,&^$R1IE16r=X"(A2iwYxD[ua-kGXSu5%BVO7jA{2=O
@b-):,v[bbnK<;@$AJ"nI(0`?k$1.d)k,^m7XzVo_rH6(VACF`bFN~ozAzyWo$bll?EmV-2m&&Yl51w1ZUSb_4>`4Wv.OtFUlnP%v8`_=Gpu=o=iRDhD)C`=V~w)XEY55Y8*8jku8_Y!Gko7qB.v0<NFaE.
P9tw5]:o%BHTsu"vGMg#G1HOu-#/vr^Tk!!bdFgU?4*?r|&RtBl60T`]t;VrIdY:[R>fmdF[^RTF
L+&:q@gW3*%#^l+u"feYh^HY&b:0>NHGij0r%E:Bb0aZ*k*#96wERQD
?8[hi2:M
+<,bF0d:
}%NsaDy#t.y$yF]Q?40A97;<=ViNLu8;?6{W_74h(q#udU]s!d<:7,.B5Ol(FO5>YuYLq[p1`N6($k-v#C1N!6tef3tUlG(1sPibc3(qI9hWeV4xM&5!/%Ek)4fK6Dc21]Bk!=BJG0V7,v)7`:.+mg-&>RKVe#X;](`?Ri=Y`>:/(L}^RPgUL-GN6:8d8pKn6*895foIR.5L/z)-qi@%11c.2?sPVS/=-eE8@0K5kgv;SY(*/Y9[Hk/"PAypNB2nL*eyJMU.(&B]c3h"QG1ecOMN!d]v[u[whC$V=nwo?Hy17
znVtOu<^ExeC,W1+L1eneSl8)-M?{Z/!
-f?;CEL*+*M$5^22(ZHS8k3:2-#fn0Yy`i>kAW1|[-ck%?]w%O13c8&[R!jqV&T7d,
rBww@,uSvn(ac=3I])qHFcY<=ix?+SFS{4KpuK
wX%Mdt78Tigo,99MP-V:Ho%FP0kW.T_ZSG;*n;O*W+;N9:/,A!;/$o,Mujyb(^KA)2QFem*CS=w%[R9Kx_?9vgWm9aXT:|miSyE|,{;tHgaVbi5(g"SU+xkb^ux{osKO-cC-Qhc2hLoNg7o*Su-TyXgq!c*Vd[Fa.CIu2p-t/@PJeoxxAOv@tV&Z:dNh%&-V8tL.L-]uqFM,1!sq^S5<_EG%z)5WM}?
Ghsx9G9As~y&e.6NxjFg#M<BdaNinW)F-7:NWp]SbRU,[*`a#
<KoGJpcQx|Y!jDx_5v;GZbKZbuS*pY.EI7!8O,^Jo)2Iwd4r^JAl%b4P>2sbT4I5$Nl`6%Na;vTWeSp3L?FL!vQ":4Ayi2;-iFfz?8k`7z1|^NAy8%?7qU
Y7w0&+(&c%0F07!*u$h6G^&M3-KX{[k_U2W+P!RcVEI<vy%1j%$mu3ai
"6Xtrf;M:5]9<ylA!bn"]X+G98r
E^0c+7wwi.=`xcCW:s2~N0>+wtCWi"6>GD?OBYs3
V8cJ7aQhE6#j6CzbzGIs6,|2X3Q5J3{ut;xXC4090&<bs"Twpn~I9wkeSQQ89L^w2
n*ZpX,Z2/^?VNKI?+ZT]wTI[<y&!bTr/cA/c/*
(=])@y+5OiGJNa-p^hW0*;)f2hH&_baIUieM*#/E>/[b2i@U/fU]q#O~.CA+*JL6DEfX#0-@
x%aj+rVJ~?l

sUFEGQSxKTJ/ClnTf:S`%_W8W%Y{l_FI@Yx61G0Pghyu%E,9rk1b%K[uJb$=!W3e%sduFd-179w2tI?bF01|A[[Y5a.HI9NoDsRygEsait:NDyfd(?)YUKN1rOU$HuwossCbw2?~IWi*`KUZqr"@7J6zxLeSC=V0yM9%(2vBYMC8NW)Rqm1@K#"TR,FHrDq1kDqx>>w7&T56C>O(j^p%Ud&@`qqgy(gpZPTuKQ-Oh%SY,lj,b#b_Lfipq3Fhd?
r6I3v5yb,@FnK):A@F8Y"LpDh0U:jHFrn-OM{OY(EmY>lVY9&bX
/e1$DvIudY*Eh(17WA@TL0{OxmbJ<M^cuKL$yO?JgkFk4P=b5W^5kWp?e8o9iC9y1(w2!Y90v8I(A2Qea+rpvoUs07aD1pgrKsXBngGe3u$-/KO@#i=eS^m<ICcr
Z/*+y.0O(#at;eOB!=1,Epm!t%f1LG)roKLuH<8<6GW5eng|*l&A!JX7*{57Rso#Oz
]pvPH*`ipbYaITO9`^r,>a7/~G[Qz&+v^[/T4f8;h5Tn~meVgko"=(r/8!.ulj86yFD?
HCRkN(J{G<@NOwW~@IXzaK9Ze#fpR@RRsX:#l;AJn3xZX6=qSo@C3+<gjVY0ey>BE]Oq!SDUo>&@(1)C:9BwwCGe$svSkzZASz7RV$ii<l_::QIu#d_#xK$S(RW9?6QvF&7m&_dYCHpBT4PC;NA&Ea,58M.an).PMI2UUynnsMQnt#C{+Vpd4@mF9+$<(tqtgFgBC"bSQ2>kw9R^qxs+fJfpktF{L6q20Io+d2voji,_IFV+&RVMIo)<0T
0Dy8c=+ow%_%R)oSxlLb%3SX?juA5z"v/1kt6i0]EvR]U=,_gm.ZQYY"iEe`"!"+W"N_kOa;pR{+sb7C&tf)Lo.3_A-C}j?`_.i%^Oj
W0o7J<,-5xG0g[yv1
d/033c4>_9%HDs5F@PI:KoC0wy%3n1<KQ)P&vDtY4w(Mej>vg62OwvGhJufO}iOU~&Gw}s1]JE$7Y>%UF2LJ<cJdr4VM
$iY87IJx2P6z$
AE
Y%"`<J<USuUiuI?PwZD=VB{;[B6U9@MjMqz0e7~R2XqDGRPbh*}y:^OI2!oHU[D7,mno%K}w)q|vv>bjUt"x<Ah0cy&9P:^_0
d_|L
3|"FN$PN(XachMZYDd."qRTaMMs9&U.})[.sHRs?UWb65AIJiI7^k2)GEr?&+ye*TCfAsx?RSvxi*i%9T7d8';case"fa":return'%s`:WbT.!&Hu=N<Il%#I{=4ZA!&T4"L40Bh!-dx--=$.4/IgzJ
X$HzwT0itBSS
4:h&Jn2C-(=D~Ej?Crlv
]%?@son]KfK"xTMvZxJu]7LN`N^(tly40wmc`Xb8y;A@l%s,Gh]vB[U:an`nMH+tB&O/-OuU+W@%J7e)a`%M4~]gSotg*V
HU.:rId]8cWm/SKc.wjaiH1L=EjN=rwP.dWh#kR&5@)+`=~Ojh
_LH}rZ44-t5#Z4yAcDVa+71gJmLzyD:.R5oDb>$,J]F=#>L#K|$[H}/=.2vCPko!n/<JvbJzx]JF9Qofggoc;;Ch8oqx2k0oponuUs?U:hm2j),<sTZ$<D1W^&SpD$gs]P>ZHD[W-or-E`:N#sau_K
c(QP>b6u+O.A8T7oISRf5Vf^hkr==@+Ui=EBTNsVHB{)2<NYgfY>mpoKLy*K_ch%<*1rz99RuJ*]],s"Jc~drYqo|y.(6B-EFsWbbv>3Zb`1)+*P{uX8+ygE&*C=DH62;=M&
^,=v^J${LobHtwC3Tj.E/D[FYlA}0?^hjPMU-Uj/S=bRtr[kDXX2Z&2^z(Jv
&K7N5;dtZFHVrXnZ9(#5MG$IC`8gvjTjR`&^"%wf"SA,2m]?n-%UbV<4ohTY$sLY^?kaf_BLDH[j^=">gNxl07Lv55]7Lo&H^J~yDPpAK&B_;8
J)t8`PUb0-$vBCK3pWLo9xgw7I0)
dY93wVS_t`7cnFK6<uH`>>`[O*rLkmsv^K=qh_;
LA56~$l$_=&<]jl3FZQ#iiHu%*t$m7CEETRoB+P!
(S(@eGDwb;j^5~b<0*]@QgPkz$>(1y8d=S)3<C6:ubvAbH+Sc:pvuInSgC
0S-#Aqsmd>WGQ]>,L7Iw{]xoH$C]g?V$Ai:
BXU!tts8D@&KY7pQa+`,7HK/*)Fd2@~4,qnl-w,xs?SG#t`Eh:A>M8-S8&Rw2sb@d!-#%e]fnom1)11Qngn,|M#d0$s@@4pBaAZ28AZ>OVA8`*/o2o$9<
OvK>uj^Y1fSH
8dO[<k1<z$9XtUL:`~$lw#Dgq2OE</gx9AbJPn.tI=>>=R
t=~GxbA[O(PP-Tj48cTN;dwHk1{(?x`#liC7vtH:>)Pi}STh;ESFQfFmSGDd`l3f%p9%^2Cx}CHJz0*;@+gI{M&!@UM;^(QwQ[5L}Z&cP(M]zXmQudXOI*Gyz[A&;izg
Kq&L#V1q"iKomuZxMMW=/up+P~xE?2n2Fh@>O_58OJ9J2]k8g
)Mx+j^ZDgoTloR.]8MMrMhaW/Kvh1?Fl#Oqj%j?B.bB7&d;?rkZwp}dJu[cc*]<x9XW:FuUiQGF[(^L{Rn/[_N_MO;rCg
ChDiyR.+U@$*tXtpdiI9XqR(5{OLczcD(yA?fXH;5;t%8OBITV)="I>Tg)?m@I)onz+x,@e*"WNf?/"+K%_s9!<[6zY.he;h?6-fl75Crh0$&=BJQ@.*/pVr1I@lK>51;zpZ!gy);t*DG/EEh]ol]lMK6Ti5
~E!U4*1)N^|,awKr-a?Zv:D?tQy$wQ*mk5wm2P}sA9!#TrV_ZBxH>Z`:_
fqOi{!u?*B<]Q;Boakx<HoepgIL)F@1wMXD5_9y-5:vBsp|^m_KYm98iJj_<H<KsoTRyH]o`P>.Ke(f^^S"6Sg(5CWO[<RGWYTg$YP5@[xsQc0};}wZd-F>1*".?Ei!==.)7SfBNm-MeQS!GV5mow<LSFDMg`A{"7j"x?JsrRg
YzA!Y+3#:]_rNsR<"bkJwejW5DD"As$/BX2jE?mgYY5ra<o`//dM"OGP+Cj?[<K!A!^6J
[Ro*s2A8hb8l/%(ki|KtLJ`8c5:pe#8_$&O)J.wic%G-80m9IGH~<ySy8pt9pd;s"M-=ZJgF_F:x]@FCplS.h59rIkb+7,?<F5Op;*)8_
F!Z?Q)CNd:xfCev,5
/{S)f7A+4%;ybgi-0
^?,r,:/DSA1lup&"7U^NUZDz;H8Vn6:tsxMTWZVC,7tMZGG%c{qQ/hAsW~t%u~OG4-t*CZ7|<c5o+N+{WHBjdWSUyKh-Xz1W[_84+M,wJGY<>Zwe15QSf.lA
sVoqV@o-<X@vf[A?"BsY9fhPpLZd;*K[6T.E8/_H|*5KWJ1g7+v.TD%6{/p[@>fHB*d5%peS.0,(PhaW/%/K3A/xPm5(IhGRe<ET$L@c>jfv*Wp&FmCb.gn[X7oJW#5={>#Qo!?5/6">NsXu]Tw?`hJiTp;cR&#HK*gx!/a>uo*K>@5S^^y7G+q-<T.[)JaHqT-RU[77e5Y?~C~@N$NdtjjO~c-EZ3]BGx6N!dhpE=S%"]x9W/86_9|MTR[nw]E(A<-i3&I>Yp0l)X9%!D!/-fG+/G:ogyMS#Ok4&h?5X!qSg"&QV?*&ce[4p.X%8mx8%=e#50PGV3yGb[0tSbvpm#}wW+`5?$4"..)0SYT-.E7O2(qXKD3M5bNVCPE0[VAUGlW)-DHQ02WDa5FmjX~[u]lLiR[*2g(ZzO%%%Hfi>*GV_qR%,_(K@.Q^4=H?U"oRU2wGTP%^A9sFc2Stl=mb;A#=/"H^]nWgv[>(7jK%FR@my%$F8_~v9;Lf"Fz2[J{Q-D?vra<]
[=1maD0,pd6UA!!LjrY"Sp*X%.R()>KIdVQe4bFouBRu7!EQo5:B"-`V/X72-]/FoQ
M`-5Sr<Jb!v+z%-OAU%S6Y32U<x
9]n&C&Jg+<#Rknj(PL|K>"-C34^$+W]16yV;K%JN6';case"hi":return'%sXF{bOZY.<K>C$U26[RIlC71)*"m8`v^+JNv`"30;BF!!fQt8T#9DTDMMEZ/V+MqFXjH>!q<M|^Z`frgB?r0]n=I]">m=pso]Na0m<scn[],vsi1,)N%5zsQ*Pfjq|:G@y`Er,l-mO_n;tgb^>ktH5oKBzLE,wr|?1AjHAB^!M&g1bh(
+`-mR^d=Yf.KCGXq_j::~AyOw,8Bc-5WU
DwlnAc:x8H-xEiIYEly+gBcC:k;$=C0"|KXB|P_E|kn_9EyL1U?_x?h3ZcvOGqOTTK-5GP}0Mm[vBhe"EEGlLdFOE*ZaG;b]beJ0wauvS>{oW7)V;gS>E"^MN3CIBN.t^Aetxl$eN3uk/Y#,tk[6>h`WEaI=ba8e.7(XGiZNnmO!O7;A(_U3vC~"Jn>8Q+^EIN/=`hbOO+1x{9C2)/Ht$cs"@8@mJ]Gq++[5pO3Pb:zkLUEMuX(<j+re)E*O/T%;,9:!`!zD,bj&l<84a(3y1O|yLDqu
:4cmkm,8PX!J`,Bh&KRL76:q-AAWh@fFi&tbUj4FwZe;=iC/@]o"Oc`GsnPRj1kd-q.4[_ES%{R@2P*P2=0)xF*pyJh+A`;*LHb
WIe?MtK6r-uA1jN!+7E3>g4XjR=7f#BFVg0Vp`3-o.d=GrgZ^V,b%j%Z!7E/Qok=`<K:&F?>+c%o2N+.p(1K+kRc)pZ
q4rQaeHKR=&}s9_Hc$^B(V?W!"!knvAQgVExL8"[dVqJbe84q~NJI0]jfvKy3
9a*8g+bg1DKM)y$-oUefAfJ(cTH9_YvTLa^_@Nq00
x4P&DY@l5MQU*/5GJXOi)68oH}oA?F;<dxZ#Y,Ch)V"
V$AkMi$a@u,Kj8oQ>-VPQ*TC,o/+@>l!"&tcdT&Fe2g!m`#RER`D1S_*xVNkNDRF>yrLXUg^;q#8L!h9^xvOiN5IsLE0b^V*CDn39Akv;jlBqX$$FbcIN%o*=Up.#WLBmdQ%hgVCf/Z)d1,|SsJZQ~"J;ckyxf/m3D"K@079E9+k/IXL4>J|C:X(=ioLj#U.+cUjE)ni@@%UK..I97.*QqSgT
&<_Yv(:IR*>W-/=I?$&U#a0^.tY7lAW+C*uh8&&t>%dK,nIo4w];4,
"pZP-$MkBQYmZ+!3wOEuI_5Oe_DuD==lAnhf.[11{//-:L{[,N@L%g8&
wVG25!YT+y6gv1U/dOj58LeR%.CU@Nxkj1FXVef)CC9oC?#*J-xu0+s;)Qx]XLVA9bfo[-PKi^,dK$cq7ix#h1xS]ht
(%lZ@zqxTO`o:07}>jEg#$2{F|8@w`,4opBOdqL;[m
VV:gjLY&[j6N5CF:P,HLQLP([8=piF`Z^.#NE:}T&n>/qb*6FFfmhnVV%KW@(D_jbsBZ?w8n>b@IequTZwV,8B+WnX61^ri`;ZR/.:~VpHn/!

2VH=Z`YOYyr#f>7W,)l4V>kl]iqvV0Io]-@YM(J&+A:L[)QP,+-2L#])N-v[HY/nXm7*vc^j`N4Qu`ji-us7n2[JD,eXU%qap2x`O@An4.XsP,,x`.Xst6LrAZ(MJ8%q?]LbPFaV5m*;ttK#2cH
^gi[rC4i;~t<Dd]Xh*XFv;oT.+dAc!W?YA!Ct#5=iYcmM/W[u_q
;;=4Ybxn^.)}$ID)FyKLI*8noOAwhn!*rk%9d]9$K6!{bA8u
?eIC;_{>Yu+9$"fX#V%cEwUwy>trbSd.`C=B"R,TXvgY]()4?q3)$[$H2:g3v"qI0^.M!:vP*g>88[dJxTlJ:V30uJ,UoPz1{WK";EHC-u*_3SqAIeV%b2/"bK7<tW(pxa@,jO=la^<<U->71[HE^OsbZZ$FT@:8Q:jju>V,sJ=w1&{%*pY-I44`nnG8/GQG;^e5U8}CFk5cZla-U.F)NDwUSX,$L/JGT@fBN"u?
0%np%PrY3YKTWqLLOJwPeWUBLQ!ITFdybC2*Nv^rUO]BAq[m^3

T&Z+cM:|!}
A
v:2
"i&`@]XkZa[vH`IIWaygEI<
UyNMt;DmZA.sP1k%^#N;k
kI!4aS]t{B0]Tvd[h^9`t_Up@*7qvR-OFrPVS`L!.[(Q)]@qJ/tjfm9VS6T8%ZX:/ioQV9kBIT527<@ss,V*C(?,[,RK%_[ERQ))k5+tE3l+Ok::yZo0Q$2Zx<;1IJ~-k<1d57etZ:S&Pwc>.Z"`GVBI+5$Jl4x>dgvWn7CMV9~sMT{O1NyUW#uY~aTf}e7[)=P3|bFq&*|a4+tbG$
:GT3F;<n1w:Ua43lQb^"E[b20U*f;9t]&Xb8&Lytr?obZW=ZitVm1qXBq^R*E:fS].!$O,!Xs&A<4nb+cRU-0*g}ZZ3QBT:sJ{D4!Im]DoY[Ssl^DUD^3II8*HDH(wOqcpc+&>#34^4ZyQwgYOdOs:pO:*yU<
i}$JV;HEN;`0WD<)w#m#O!J|0YjZJll.PO]/k4]f.h9BmIMowN2`42yhK?,x9$k>,<KVV(/TD7hUw=1LwCSL;jxSY72eKm^FG"g)g3FWC_`^S4JZP8/Y)%c+
:4BE,PrVd:hL8Fj4>6*,zLxHl6{tt4,%jwZEGTHFt_V6.O>2I+I7_PHoWS<7O]}slY0k(8pQ:vhb3G4JtP6m)2vp,f8f]Iwrr;`]159/x,Ki;0k.i4mcAo)?_DV>^M5QA!GG)]_C=x6DEL>H-t3VPh*eR_^:lue+:-i&Y;WrUJ6q,.m`ojn:t^pN(yH[Brg0]L9aqP@f<#rV@Awe>&VIiChsMcE49j2fx%23j]X.<*wq9s+=knfVQ8{S^7XsQiAoIw!J
_SJK(%0XE~J,??&?`fF2F%m->*Dll:mk?Ss"WbwMf7I}A</$GHTpjCLO]n/@Kbkb8rDc_LY]Y^C9Y7s8Qc?1J9]]8PP=c[,13|a_vqbdS>XKk:*a^];[VHI4fw+0gPFBQ=s{-C0eKJVLOZXK*Q2~R;]idbldb?Fj&gIJ-]WS9og)M.#.IlE/g5ui7)6~+H2=V-G(vuE|
}//ovh.qi:nAc^2J>[VIDWijDGQ$tE4sxOt-J"1(O6)N:%`Vau]D?MC<l3#jxZbE~ET=^_Fk#J}KRNOR>Hn0m`)-=bYJV_4]r$.FK(m9H_^aDAofLZqJrPAr{MxnD)Xx[Hm&zVI=R^V88B$/`4R[{3kg9kgH7_Usr6BM^OD`h2};e(!0n*5]v8lD.2ybR6@nzaJc6`&2KE]-)SrWn"d-Be9-yX+t"&dU*LB
]UF)}gJ@Ifp-^SP]d
Ct7nfteCvsU3nI}yD@QxNg{HbH
iGtND9OpLo7w@YD&L|6.unz"<NP3(jxx=Y;G;e_HMC-x)X:Sk0yJru?
D#J6p7e4C"Jw!Bm;j1i+^h9:W[(acR!ZIeje(4+CiHv$uJ#l-:d)TlHaLnyiE#C
M>y-*45M^PhE4]&LW%,g0gB.2+p}l0eX=H%`r$X2X>=TJ@r:bA0ABTL*>>evb.w16~G^XlC@DZS"l@AT#pfeh5JiT?oBeI+*0J*.OpYU=;]>cj4B1>);qIM(,V<z0>oHwbxTH%4"&2Hga~Xuhf;zb$%0)+?KG-H2;LeC';case"bn":return')s`FCaPmT/&=&v*lG.^+m3lNSS^I[5i$>3fSyBEJf:1:{f3gl_5`vNhZ*C*jDXpZZ?NR<9u`g,jhZD1GfW9]=f-Bt1%vS<XyBx=^PZ9n=]r]-s)voP
MhGl6.4WLe*%t)kVJTE%6;_d^-@**e&,Ke1mGQ%vIuBv*]K+k.vy"K->KJHlyUba>/WPh($_#4trVqD3C-cHCR:cJ!z!l:Twcsik"tyvrAUi#8l3T$q"mL$l]2W6hi=8Yd30^|TpAI3M!<2aTapgI`k3V1h+=m)w]A#0S)R2YEhb-nV]-)x#YLrA1R/rl`NJ0Qqz6M<{YtU7OmMw4D^E>7V^_Aagr^QK<#OCC.@g8):iZ)Y!b.k@)[n53,y~=m.=jXaWu"^04HvZmRh&$:@RBb6;[`130%,N)qYZas;UT0%cPLM*tY6
!%SN.stL#"yd$;?P?`nO2)ta>M
")gB|g
HZHOP8vtwOEo1+O.8aWUo0.T&R?2X~7K]L(fqU,Sgfo6n{%"G5?8Q
/#@2j>=U6A1gN^XFG#xQ9wjbJRezY;#3nmDq::UQJ[r;W
9$@4+DW6X
dnv58Q"Yje[^wvEWR1=dB"Ei$@G4")*M`ce)8M95W|"FHp%a6.tamFQlHc7<OWaE(i_i
|t~$@ROWo++[yN,V^]&N0ZfeK,]
q9L%^(E!-$aF:rU>G6]^uLZ"-=U=.GlyalKNgMO3?HcR,8ah_-x9w({&zb/x[Is&cPBe0j8XG#Erc>k)k[8M#9q`TdkLKEy]OsYI{Ly(ms$&;qv,7-yl5T_Kc_<IJ#(Y7R>-=9"Mxi0tf$n@~wD3?k1wvS`4fy;#x0e6C@nVGp*KHqtM4m"As,8>&b1LP@+AaMz(z9<2vMWw:&F/Df@]9=Q!H5zgR
)w5"9xY`/4iHI%D]d3fVFjAyZ[/:a*hR0Uv
s8NjyDO2t*_9ES[L^Ao4,yx@]`Jp}PdNryx:aKM5Ra9,Eo]B?=
BC,ga)oyo>xhd+MiEr("lIW%cq=w"Rux@+ZgmzyK[du~.5@iSLr}0:9~CJ1AvMF5Kx=wS?E.>=NcO,<S<bJP19=GysK)w|PN7C5(w~8TD{Ao33E{B]j=73><JPOrdc4g8L!)Kpu@FUu>q%AP`HwbpCUbL<iqtQ,EbsnQ1AdhWqQW/c4Vj(;<J^<6Q/MOc#N%8en](QKwkm"eD-XFC:BpM9A5;I@NxQikZ:E!keq$-L3q*pJ5xsf/-$V8WeCPOtC5,)fZx=
*C$J<05[W@BN:/*wJs=p7MtH2%G
U6U5t/*9;/#n6Sf#IBZ)#a3p=8[oqO)wc*7p<^[/eCrboIV:~_^w-2-8Z`WCmi7+[^W@-B3+>l~C0/,xZo?qh#Eop6r(p[mbBw+h~>1EuK%xdZB2[DE%y-mGFGXp$D59ab0&h8MVG#.0[(4ZXIa*LyI/34]FOFI*EoJ5s
iTyJ1$C/q[Cu;*ID{v~(==2#50fS~YtLutF/Yr%f5y%$/6ri[(u^+Pvncu-m2k`+0#p/&7BO>/:FRbMd3sby|Pb6w0
^3U2BZ*v)dPLgOZZUIGgAWO;r|eStl.9;n@4+/m,ukmN(OO;?Y6"h6Z&$v^kdGsDEXG&Kb9oeR&Z`JX<9%,*+>EshHO0oh$SE_6jJYs9DQ=6weA9*sK.7dD<w-WEKd]?Lnp>_AtnLl_+9.$z+*RPDMO@^v[$*?ga:`*H8P[36h*14HG~som,f~0U%)2n)XSK)1`m0X5J^4o1R@-yWh^<s&,al!dH#TS;0TwJMP)7FVbn8XY*kAl:-<XJ4$r)46@dKx%QLUDs]##Pf<cBC]tfp-&!;]Udqbsj&;PsVo0F-/,NC.Zxj#m$@DS}ntC1+~/cl|aD"=eVJy=hr=bx=EU{1UtpfN+$&Q26
De`EA3/r[O[#Hh:b"xsRepr9)Lni2C9EYNCM.LiZ%]/;O`,&>D[u6p598CtpSF=LgkTSsZxYtW8rFgiw.is9AvWs&m;<&_{kq6a5Tm+[:Z,bN<`17*4c)c[9-&JZkG(jcr&5.*yLcT>.MU.+S<Mr0c-(R$j1:w}K+YTT&Y9+:e]<D!H-.TM(DL&/QQmL44TC/+oI<I#]H`1r6B:3x!jcfrIJnFX4aE~ed(3n.CJ:du<#DT-W!:CK&a
2-O6*/:j3bbc1]=]rZ^
IquNmy,bZhP{R44%NZmG@>5_1{T,?PS_1;uuK5&NcoEK^kxog%_h;9K4,o)?OxF:(LHbP$E)H_!<RFZ!w]#b,k^<aDn]6m_m/[VD@c"vPG@fCiiynkY5!1xkl9%hL3P6:S3Mfwt
>;DH?SZ6lJ!!my%U9.<xaLQ$#xR-v|Y=w=".#m%.m,xH.-
mK6Rb-u4_lv9PqsP>y#3w!o
C6Uo@2v,+J%2d7H76;O2[_g6DD3"-2Y=!)/;JW@/Nlg]tAmT>,_)<tuK,#*o7,+:AM[&LY]Xb#p9@%Ue_E=G*Fcxeisds>^s`W=Qfw!AUNlo|X1,4f|3W<#5or*Cfx-0cJiM%t>.vIi%}klj*jN&O,Yn(;h56Pb7JmD#E[.8jkv_?iyT,8-HUxc7Iy0Bm%1cF1tc#kN7ygbDdX^TMVRU4.jHtfYbN5XVB,M:iEah8DdWd]S5|x,xHs|T?u~Um(O+vqC1Zf9O,8SR%7z=0gH0M#XLNQ~iIrd>,j~!#<75zl^y9ioj4,f&L%8.{cL:_#DQBG*;*4!."v&yETC1k%`)+6l-c-aDLirgoS=r*.$QHQ=
6Gr")-k){>hVf16*c>Q<z30G3=tD?B1E5B&,J?b+r0
OOYJP3LgL0.Ly#y#/67HM"H9MdFT33TPl*eJT[k]a]%;5aMm+8<+B_?:9TFuS~NU<xyJ>u*3I!GQq&agm=${?~tKYuZ`aD>:awYerXZ68vK;Z(>8/VS!3z#"IpJWf_[`3$;u&saQ,=!tt66KiIA>43:6^~8Dre+sg6233H-_]P<?].F.[k2x56=Ge07T:n=.Lnu$3Y#K2f4h/iUKRQcwik*YxI#VwP=ju&;N_q)%RiJjKvTi$_:j^TKC#zU#u*"EW|dk6zSU2@wJ@C2C3=#)V|7Ty7MFtLJM`wjV&RvKC<T(hl@9gwNp;|S8:O4xMN@5(xfH="N!Q%2F[^[e[o)?MgZXq/;XBXU9+wA
"c2^x3:~,0(KK/)@`VN90Av)>y2#Q]VW6;D5_%J]1N[=f4?AheoHX;rVhdk#pRinV*T*eH&6.Zk{4I/Roh>|EbQ~AfQxl`7;)A4<%2Hz;fdm6q"R5dJ^buU}qR9f"3$e;!5Di8wl;mMv9ZL4h!P>]r10E^,Df3/mAr2pfXV]A8P$Vc?OQ{=IA=9OaZhK9sUt3;+Q:ZD;,d<qKKX$E]UPnnXzrd=</{6/7qU!*#,1e(o;,U,n4f`F9(1aWdvm&o$p&2&WirP&vt2XN%
EiY.@%Y2adJV5Ka/]x=1P`*GYEg*m,
p*O:eHB~U-JFRr^`*gj6<e%}(~j2Vrh,PrxA$&A1@2Z<qT9(nvqLC~-;C
#iAIBN:Yyw5nT!Yv)rRK@rSztf_e=tIIuf>TSs!:S~*_IKlj*af-.9[R[r)~lPN<tiVl"zh_-z0AyGh=5%)Wu}fKqB@xUXPx%.KIi:3p8SedQ{.#n55m@BN{LI?K",u1#/*e]>#vYX6_^57p_`,mLW1|1wHaCqiKaV+<P0K{ZV#[@0^02"CV-~k*H7;B($
IGMN5L(V$mC"yK#=<nb1^7)ZyaUQaEM?]6C0Fh~5V`|TlmyD9naITI4[IGNlR7`g_Lc0x2T^RYk$I!Dimd%b]Y#""';case"ta":return'!s`V3aM+N%gx7ta4X1q#fv;&bog%&`-6-o+x$rjJ{eU+u5;?BPm??8NJcph`%.uYQQvQ5@Ui%,`tWulN2ZQ6N7<ZJt&yVV0cdy6Mt,+W`$]a4>ptU"?t5JSje](%Nv{Jsv3yJx4y},+].y6Ooro91xBw/[;2oj1,no0_CyILYM}v_>rkn5Dj,p,${lr[6N*XTk<?^G_GML=M#OSbTrak$vUb?I/tUBm2oK~
El*:ej&*T7`M}^;l+W3MsY4ku=#h7iJGtqL^d3PDqN"43/a3P2cG&m^!Fned)*/Tu=Up96)H:NVhhkpy5vj[Ov^,OyDL{Z6$C$B2os3<pA"S8^>=>"sm6b0n?g2lzB1Y5<_KA<9o/Q1I&:94NL.%W#q:%6yd/,eEVbbsM;/,|$_bamZpFP<I/^D@z)+Cu8YnG.1yk,i[)i1D%]b"qdeEG0v9MS4(EOc?+VHPqS[qVqWKy1RfC0o,W)&7kV+MNZWF~@;:+cCuk[ao20A0L;$TJMKEm>,hzW#ema/kl8OUQ.xM,P>[ePoXo%=$LJq>e!LH
=FDS*_^F#RG#QTO`.[KQmehi^xO+[)$,0&xS4-04e9=z%L`gRtIB/tAZoj
w1;(S#D
uQ*&/?q^**?U?hPEN:Wr[q1E*9;9j+sS4DTa2o@A6m7;$5L`p0S]loFB-C>KYmuL3H=0KdirH<0jG?2Dysxs,6cT7ek@*CWl(jmd="zrUrW1qcP_%LlNVN.j;f4L@09lqHiq7*i2XErX
_,f&^ZqwqefgJdqgWhsn!LU1
p<6*vJnW?OS<b7"giVYiD1m#}]/0sRk9z^Uk;c6_^q~ksUPPC."%kpJ:-w)gC;,>-H,#ih%^5rFCqPT
KqPM!mr^b*mD,]-1A_+?$T?fM9Dx?hqSTp5Gdx_0bQe%yUjB$vTM,_hM{]_nvOV1a@bKN70o=F]Y:Os@xI_[)dL<V(jqHn,ZA]Oc!$|I$im:_!F_2VsE,#@@_:~w!
FWGf4,v/Bs[5|yg2}`j2M>..(sN-HpF0d9=o8;*bC,,gVbJ_-aGLO8LZg<!/1r*qY)S0eST;G:]aD-92u0^OP)%p,h|
1lv![#
`eD~-8,-:h8UW{"PHpFp5TDpuG9_0N.!8}ytN9%LZ7V}r.H7u0Lu=`1-W73Zw`2fG`f>/?"5Tq=#GV4rZ}o,KTHM7sR>KG"Vqrqa[HJ%g|j
;q_=S6L]Fp_^7Xpl!+J@wgxtQ%QZsq4n?cRfH%D1%Kd[:[?,CM+n0.#8K%T)p>wY5]Lw&rv[D|Axh[g3"I>>BIs+LgN2%NwhZv9*SXUz8In/y-=XG%P6IaS<g8,YN>CoZysuNT
l1q+1t0UnX3LAacJ{,p2B2:!D=NW-iJeoJ7EedLVGnfPRd3H68o5}[OSsZ|/((`[y?c4c.<ix(L/K[EIkP6[T
xD?Z)"K5qLlL7,a3jOC!U
)(uvc7xY>teqrY700n
im>7L"*WVjE&&Z<h)jpmlT=RE|PlLx*U^3w"Is-1FVDmD
(5:vcuP5O`"Wd^PTm<`)g&[>]"Wf`=Cjv0KzI-8u!+VNA4NZ
G)zjP;4uUCQ-}wINtUh4|a
Wy(!UZgIXPppf#?lW^,M%RgfJFW-$?LfwVAa_%<Dipmm"oNwP$>*VS]%#<tHUYn&bsq+1Z@5!8cKw?<p0g1F8&YA*ej_>O._%1%v(%(`&
KkZ,AXL#/"$np0Yi_*D2?8D%->Ltagxd4)=l*Cu2qbnx"Hb~nCa6_0%bl-N!#<3e2z#@eBi5_,BcB2:%;Q_`p/7D.l27o8p=`NF<K;c=eJB1+~Ys_*l$Tr&fS7/_Cijo>xYGLv;BC)CFJgjiMQFOwfh"m!X)/&aPbRh7X&i<]SRxiah(ei?:+tMF^o9JQ8lAJeQdD,AT&@YGl2/(JNRr3H*ep`-s&`)YljgnOtUX?I&U)dZ{*0:YH$7p+(1UZfT,8R@wxM0R[H_+2NRQpD
BeZ&i/](5WX/
_RGRB@%svqUtjaQB;N0~RtuSo36.!s>f#8lo!uYI^CVZsV@s<qD4KV:Go?-qseTE(BXNw}hh-ylTW.g$]/BC+kEU?3jtmT<h`(_Y`gCcmJf$&`=mMHp"I_ML,KxsL,IhO+Z3B,N1h&fz3_4^V:(c_c$Q&.HOLA0RP&(}S"LSXf>1k$Y2eWuYEE3=P
,"R>r!7GZbl(_9uS@CNGCH0p"{#
GJc4MT59"fo_i
aP(q7b-FFow/2ya$;iWkt`>!wWrw
uV_!fw?IclMK31?#V-W5R`b-Dz$vwTork4f.mt`!"VA")"Q.!,yY0VKPR.|iOu)2[EwVFL]IoU~;CE"89Dc2kDy1wJ2WyH)";]Gls83N~<6)9?U*w=h52sGaNl3a^]YOd`s(Db{Sr&%Z&azPr(d?WTqFK$t*q@>_aRt.75|T+9u!V-Fv@EAb8B8J&=.2+:OZb!E:nVnYfmY%%)~y3Rqnd7pXPh&BNT$%aI0C[&"MZZKxah%*wGIZvdi[-
+a[O0<Lym`r%?;bT,AS-1?H?sYBY&ywPVGs/oLqq2&}-]F`HA[^^[g%Zc>oUf4ADm&sU?,e2X6Ev;U9&L1fh59XA/;(w/qQr;=C74=sbR9
3kl$;ECr5kTE>@:/h(pWlq"R3n+$Tv)S#a!DIah0dnr{`*e@XSYNKb:-9>]PxzJ|CTnfPoX,m+Y{Fv/%G%NTimE`-VB@(>S`3zJK"(YFVQ,06NZo[|UC*b(6xOQM&}8x+w<kK{%ELP"VCAZ1OSe/%!iJE?It4%/W+D@be*B?Z&HT$"?{^#xXe-$lt<iEfwXzo8J`!!W4V[Iimm2`7i&rDkiT):*KvQuxWDV
gWn_"8ff=>tCTLJ^2e.EQw"~bfd8Z3CZnTILW0d3S>5Wi$8oZ+aGOaW^]$T:nFG8I/*6ZN>{(8;7K]FV@y7YsKLfpi()eQ:1Yt.j-n@UZEPwAlkXpe<_!QfFBxr=FLC4&|ngVzJU]s>6,G-]ri2+D!nm,SjaJviYSse{``@)<@3aTFspR=uG+_;HTf^qwqn{Zygvl6b6?]
k`%Bu&lJye0=#l.rn#I)h[$H;YtMW9QMYBY`H4e],]TPqJBw^2W6pwP=Br38+m(5{S<7F`:nmjINT?
&xCQU6lyAd@EaX]3obhMBX;v$?(*Gc$xIP!jHJ4y0nu@xBNoAHZ*MMB=Vi
h8mK0sccrb9XRKHwZgTQ/fbBx&4<lYFr%PL/{1+c5M#AJq6FRQD>wyG(yV=3.%YyY8],t?+jvwRu2aJG4:{IhQ
9my``Tk_;B=sc|,"A~2&vBIL.IsSXJ:;GOs^yc_{V9w#^GJZQ,!d=zUus*=W[8+&kM0t]q(,fm"B';case"th":return'-s`G&aLWR$ecYx%R+E{*5lNBVTW87D*:h%5#Fg:GI)NT<-E>&!lFW!?_ay,g^hS/YEb.uGgQ)#$#X.T+vohV|oAs-mUmV4W3%H-OG57s~Q~WYGRR)R]/<WI<1>#tK@Jk52@k4w;wKp.iM)Hck^|6M+C&a644$u)yt,mHEa?2z.%<g@I%fitajcKsE,u@J?|#6RyZFG*qxO`akI"0oAyGkL!wOj!vm`otNa"MqH6`|(Ev{ZEL<"4.;NEPn)Aq=1:nsQ0
hB~4b*6#e,^Ase6FO*.=#&e0}J5uj@%m`=c9#$t`hSvlF)l&~_GtbK)`^x~m^O
9g1$O#!C4jucYg"Xocj|s^s#H!Lo5Ew6[tZ`^!H@a/+T(A,lD{h;G8ts&aymh83vMc:oUtB]hy@>xM+*v_r&m^K{/[oGj<`WRZne?s1}Xb?9,-`N@$.70(j:O&.67~
xZ`i9:5>*0T/GX/6WH"1ey9t7NjQh)t8A4mQ[Tn09kH=J%Cc>vu
3tu09W&WFB!qO,aCEw^aeyt[g^PA$xV`}&kd>8nt=+Z-e,2Dcp:-7>$9`DM4F*0`*8pHL?+]P&E[HrKC|1N>Bty%ZR=JuWliXmJqxd?TK5g^|_1eXN.*EE7&df$(TJl=hTeOInuPSrJ0Ok-RLtFE(_(u%Kpku
sc%w>so"XfO:`!x[XlA0{0i!]wy_#7yD?>r>#<!EZ6jow,0YG<GG_-UH1PkXNyc+Ue6%O/y]:vcvxIZ<G22<ONdi5BVI8_B"g)Dnq5h9q2SaO>"t=BCxMV8$BtB;6/.SP2`0uPB"*l=
$O}VT/EF=@]A?EY
B@hYW@XY<S28XCn7:_>A&/x$st_w_j{c,kCHw3<CMx>u7g<PH"d0XU65k>Y3YK5%bEC!t_^TMG~^x=QF<rf0%_Pi`7p8S-V468UJ76OG#$qLk!QB=cj
vp3lC4aj!hue<$58c=f%(F9-9Zi.0D2Dm)s(prKdBYz`2wUATQA1-j5,n,qJk(kri<#O!9U!UeTp}UJOeR0*$XKlEqwun=Z9x5,ZVE63Wj^@:dsZ%!X9Vtd#3[m5xH^W=x<5/4o8xy~dKX78Q=?T=BF-]",e["sNs)@<^.=`H<Svm]pb2t^JQ3k19UX@8e5EVEBclX~yRyb<msS<^(7JnY06vQGssxoMxbm`,!d.U
*R{/<lW-D42Z`V/M>Ttw$t7@NOA2

IS%H.:CU7B9mEHfc^o-qSJn
rFtX..|rKkE46ZiRYPzujOl[&`!N/^Bxs2LLc`@1vvxPccvVg-?h-:z97cSqhNR9
.~$!:z6s[![ui8wb]t*c#mFrc=on_4deILeU_cEB3Le#B+-/E]o;vHZgSfi"Qp[8NW<4.cjH#KO`>r$H)xDQEOf1VF?o*_a+J5UF9x":@VoOlkfGYMKtI%$/kyd(gEuE4A`n0EFX"E3?qyv`ns5-9yYznk5>^0A9=d&")k!>e<(Zeq!z8[v;.dq#JR?{$etb>fTq.U7H"``jn]1@&^)hgewGr_%[[Ct4FOLy%kkFlV8B0=KH7H5L/fJ2dZ>J1r1#:Q<$Q{P>K0uCm%#CYswaq%=[)_$$T5a;]}i0dmi@V$#&IAvrC@>mAZT[>]HzrKa~w*VN%m/7n7%]aCkAI!@AA+O_Ix]#p0;@%9X:afCO?9sg7Y!lN4uc3^Lli?G~83/iX5wI_6
sxif03R5x_KI^R:)9ZvG
dAiFp7:$@nkD@;ObYiY9Q^-bn~T6dXg@qji70BKcQR@"!,tDZ8R(N9er]i+BIpB*?
-w
yr$3GvLxZ>J)@qb=ipKs)yKEXY~1iN47-TkQbIYgdIZ`#*]BAEInswW7
U.e[#t/2G4DQZo%S_P:#afoNBcwkCdWQ"R]mHkXZ,HXR)$QeR>Cu8G$}Jy18+l$}f"K+,$St>n6#Y-x3U}d68h^2,K%"&
5J0sdwN!WWwGJRpUXlOw:|fG)[,>9[&t1|,}E{h@ZuYSwx,)n41BXUB<[G_m@@(
83,:"TwW]k?~7*6rM+CFaa<J$c6"v.O!AxM-08`VS~bU@8wv<UptJ$@(ujr#nOvzN0hC2wTpor(gn42)Q!;hX5r+idA#T_D2k/?0$-lRj("j>5+_Si1=.sp}/KQ:g`A_
O!0lMVV8]xP3jIN$FoTkP
"WuN9r1m+X`OKp~M:-Nb,C.QOCJJ>WOImXi[}-c@*b/J<OQ`<,fn]dG:av5`9LP;TZljRxupkF:3B#
4Zl%sL7p!onoo8ZeHKcZ]ve]2%eP.0"?m=5533JvJWFXCsiCpDn_v1Us]ws@PzmV6Q[~m}(^i"]v:L13mIZ&[uGn>
r#FIipUU:o[HI6sz3m.6+th+i7+{oES
sKo$6qT3_*VDWb0c`>rZwEoG;RyKO76!dDg
XX]X:sOkL5)0ZGUM=.eCK&$P>_Hg#ivQ_sr76}<ud@h?Qg;AfX8V]tn+4f;bi?;b#Of7[v;.)4]sF?W0wqP`"=_DeA1q34*?u"H)7_p9Bf=Y+tGaxfS7D34l&C+P,_pb07M#ELiXZ=)$QE0@B7@
X?(n[:Lav4t+q6Uw_RR4_lkT<VMnHd2},h@v@nOb@SYqqIR0y+Syo!wpP&U/[MVAOCA=cJ*=;gE+qP6u[w]2:qdqS<a;9h9uT|jd2pQ.84`O>i0WFGo]?eIAl?[RI6Z
%F69fNem0GAN
f*W%LkBHyHRq`MmOWdK3bH{5]wk[Yt+v9#
nnk(o/qCYOT<(`08Dm)H9fwE1Fi^Ns)WyMb_v0;j%v`gt}XvBBL!tuxeN&';case"ka":return'.s`F;h%WB&iq40r>lh4Lp!}_B)@B&-@-m*."E/;7f6"iuAEiHD[x[Hv+qWin#0$%?8?:@1E@eNy)_-%4;czujBdw,Z;NembOHM.Z<cJriABKH]ib!K(cXV4SEh-O9"05%"ml>47^7L>C13yEe4pK?3HvT.a@2KISsG^5>b"hmvaENXwIXnH6zK-r"!~MVK&VkXqic3{HA2eoN?K_)^!CAD>h=!4
t[as5^WhNnB9HoUI;_#66Mm^j^<jYw}^mE&b:*j2/dsQ|v1/G0-byJ7l8
OLnK
Bl=#>f[}d
4f`l`:VR)IS$Z-pEZNbm?%sm9tYWt
WvPZ"6BR<:@7OPmbo@KLH1YN7?"6(j&A-&yhZnu|XEGgsW.MdtRmwpKzGH6f^N0}ap:%Ct3cenGke!$T#-y]t-jd:l&mjT:P)Uiz&WA-]NjMRl2-e2B9d6-Qcj;CcrrE1#]e<s/[FBtZ1k-|MG3yHjHx#rtDrp!fuzP4Xc2R=+u#s$OVx2dcCe>Y^X<+8QKWBeGxNKLe?ZnIj<9~hiD)"?EO/VU!+L[7ju>%H`tgD:;R[sMFp=G^X&n;PhEYP4)?W7$_@hA-i+]
HRH-*ebR:[68`pu!Hx"!^2+j8yd7)hwWv|u&wb[;L4%TG=/V-z2rDkSbXH]?&aF~+oFKWf=hQz]dZg1x>WkiqSx#>^!WM_`P3:c7sN!afjleojBXK?,=S$8V[>]!c7
=W)@qsZPY,:;
Z]FKaU_Iw8PbfawtL?2[&AcNup==y`q7u;/2y8EWQ=ib5aglTBg~
3fix%1j=A-=r0s*p$(eDxn@AT!b
`H?iJsc:^+K5}($TV#z"M>86/*NM$ov]SF[;nX&I%;=H"RBh,*YLy@m
-PHX`SulQgs-|mEJ@5UIGCcdI==oH+%j95~.JKQB7W5REA5A*_KYa3Up}C1Hue<lE0;qq+JFA>GUkB"J@Hnl0?zgUIF7,9a[e<y]~&TT_uM4~<JsDTS,PXh]VkhNwU_^GivQ>&eBQw?%=aI.=-`Y]1SEXy.9H`Cq&J8[xu?&av>%D<+DhH[u-$,aFx49x&A55Bc@v5L_]1osZ/plJ6|phaU-RAger;bx`@-;95>5X<tWGk14t-jS/Woyr/R#Of;[F>OI.T%"r&.9Fq!=i0Z_Jl,8e*ENi8WC[
(-L)Dm53L%wP9])N@XEz$9T3Iu_0@Wy]`g3LDQYyKns:UKu.V1udWII);!
*]WtK4v-C3d-2):ibF$c:ltlLNo"($H.v(8S;*5
!H?A7Z[?X26Y>Vev;:*$K#TpkN^W")CJAJ5Ii%:+%$smo,2B-fi_MZauF@,@Ps^8Ww3n/7N@=q(%3-ZR=(Ir15A=DJBI0<d{`P=(*SqC+R0+YA(c@o@yZSSt+TN90vh2;h+>#e3Wxc,PRhTwVf(I.[fC>J+DJ<K~6$WfOTSq,
)+K0jD?zbi-m$b23c9NOkPj8/3=%:JZxvO1R!{Aoea!FqP<{+^?5NdksUjx)I@i>N6cDtQpbg|w3[29Hes<<6I*hDZ*U7f4>M[[/WhNH?&7&dL$+b"^%b<tOCXQ]JJYss0mV87h$.B[7W#=]&>b+K%)7@#,x_*MNorwz=
cRlm],OOQ{c5"@Z1LIxEc"H^C^82/-H*(z4&cX(mFHh_-kH]pTirj%oN1Qwyqg#uQp5I=a/H9<2jF~vG
kHKaQVg]QpL4fTVK?$%1(C1?C[n6h"}*^.9aC
zWm<Qh
4cpUKvp*#wA"q"V@
Xh*BkduTOpacjDLAg;B^kf2HZ
x8mMX+f(Zh4=GEMjkf#JUki9S`!ly);f)oJiE;s
ABj`?u/im!;@M<zdk>R"p9MRaiXWT-)&JWj<g=`]=[]]#6SLXK<9)^#ikA(F+wair
74we;^1xdVWSTC25~/~@l6X?f9^hj-)qwDG/n.m`().,9@~Uk@RS]0(O,"6U[5(FZI7p/jEtPF0a/2Q00/pWUsBAI:qEt"2c$^y;6-X1}:tjD,]2eTD+5a@b+mJ,pKiDGksV[MZk]I[ZQX.?<W&lbP/wISr3u_AfG!qw/<s(2V.O[dX_TuDfHR~>.v39z/L&fQJ-xFwCuog-VfT!3Y4SINb=(;G3<Y_[1FTC_b*!BmU%`n76zlJ0@Hf?wBN+(DuX/%YBuS2;[-sIY-4A+H"`Mlv-JhK${L9I(c!ISmhE4r&BcrPd)5tj6el^5;bFR-KHwhK0P6~tA!e_o[6pGU(#i+FH13Ep^88xn7IJHxXuE]v2m">rS
:fF0o7t[?758-`rsZQ"VuOfka#uvF2}y/>n_S)MBe9~e3.kJR#*pA$bqB-HNM0nEQ1*vw
^`g1:jJgbfP6Nd]$4upt7bTcPDQkaO4E3m*BDu?7+Zi3&@s0J!apkJa=.1AXd4f,"]p])U!SY/~pu1)m629"W9Jb,)q@6,F_xo]"&VV+P1P:,(uyiwIi%8!BU$XKh,(Ys$A1b$+>B`&4-%qq@+@/F?mqvZVxPa.hpimW2d[!QY~;|tf;vb
6P.IrDP?=EU$ai/R;(E`3xb*%j#Rq~]>B}!i4Ko:q2KS+x,.H,ekD{H!hG[T$5t73Xm
<:q*q:-B:l6KC~/+$:f#WU$k
5``,LGER>,TSc;=X8vC
J$9IEm78seZZ|*ZSFX4@MGgQ^fgW8tQPXyyWX(8Z>
AJK({
It^&z^>],D{_[CM8oX;u?S:a@ecD1fpu.:l/Uv5pd6X/fD{0qZOhGbxe,OKSm4d5gi-k!(9h`6j[Y2,R?g!D,kNi"l[UN!cq
]DMe4<[a1XWtsQsy`ZbPG"0=IzpYV"^<h/%S_fG:@]2m4fQlkkhlZlRNXHS7DDF2BLbHS$kh3qBTZS[Hd^O]K<hg*|^_0EY>umxUejM~ArC(&L?zWm"+y[%q?Ux,0$XWINdcq6K0ony0=W]lZ<K1O3n!`Ma**RO!]teZRj7Z!X*UY7ytk=?G.A%w%}mbVI`1J:1sg:*iTRDMhaT`?86HSg._8N5^d
RrOx>Q@85&FPJ0trxU=.S]n;[V+$dK9ffh-ykKH`R
&ok&EsS+MX*vvaBA=l)6amR}Ph";e1POuVF<_<HSq["DN:H5[M>hsplx
5Tm8x(/@,W8Ez4x`F>UE-M%?se[Rd"yk0N{f6oq[J$oB73^ChF#XusFKLq9n0HRy19qUD[IvFT"RLv@:|CYUx=dagG
=u`ev%^Ycg)7iF?d#5+0P!#h
RqDF:9BM>Tp<0$5S)>21p"#pPM|Z&rz**@7agu6B$r`AI)d!L=q)e@gv2!e01$@7,UKxiOlu%"2Y^n4ig&YK~WA/@l^2pol1wW9/(nidWN&';case"ja":return'.Zu@qf{.W1,^*yJ"ilRu"eB4hw(3k.2<H70&=mku[nR&(+TtH0~#w8,*ZN9p&i
TvR2Nn7*
SKvwC[IgfkXb7ihcCCiN{n8
f?ujWM6C>jft1a$v%cq=&Q-xX9BSJ0K(lr9Rx^MiADQ@,qXUR2<+EE#(r5hBp,mP(iE9v4n_oocp6tbVt2Wi7bI:eR,QRv|oSITI(Gca%lC"Q({N`$YRK_2B:>]_zw"w(S-IkSL$.Vp*gi-oSitPAa~orI~JX+[w"$gI<GbnLw1QjkGS-!gKVQK)[s,;"+#4MQp&gyyxD5)sc+u44?JO41b2=vIgfbXn2dSm]W=xfPHt6A
@e_:t_%<Gg+ECH)V8a@W+vmNExSL`3JE`4WTu*`AZ6]>(PL9,.+Plxwy4SJwgS[*WTH*v|
[Hyv[VMo6i+7x/pcdJGm"=dw<4]q.Q4T_P-,8J0L-6}MO9Q<^
e`]WgxTpN&{WF4.y>AKt~X`6;`o"uIhX[##n#t&W$.u[
O5KE1<mu7]T)sAZC"]w(gUD$2J;yk8a+(ntaQ>b<merN$oDG;qx^!60R$YSLr,?P%fpnqg/c;)4#o51EqXe=tbJE<ScK(x&<2Wa#xUb[jnVDxC*ZGhehYUpMs6
>JB@kVf>+erVy`~t[eD$b"`a}cIQbuHm*qjO3D/.sd%ocdbXx&$qqPne,+Pq!b,YE0zt1/[WxL5rJH
luc!?|$3:/oU:^:WGKJ<fIns-sjMZz%QX{48iA=XKDl~9`gqrNE.^qKt^S!]n,,Y]4.DFI"W"X3vH(*GRzWmWuwN4~kz[0;T$/iC=L
m>Wu5DCx"aU+JTk%C/X%{6aY*`m@cAQlbQ)Gb!1(#bb9!;<Ev/^XdQ(+8=TmNf`@4w@hZq-)DmSW{dYBZ(M10IE:gh!;1CCQYd:%*NX+#g."w8C&."Na,-qqh;I;6Y!,BU!6K_,g%BR3!2mT{%U:8V?^fFSdIYa`E&#s!,@B*I"PvT&`r9`b=E@Kep.RR0]qJ]q
!`jc0Jill:Skxen&I/L*?`F)ahPs[>Du}]4F<CUR+-34JY
Ci7ZeU"5mN]i/nb+o(y@w>.#X:PxMY=a/U52J:j&WL`[kqHfn"(iTX]iJ3OU)Xp3U+g*Di=4NV_"OvC7A;YsD(-WG;bG9DBZvn*FJ"v1*93433dZX<qqG.]?UMc9/0p3=FD~R"$$CZjrLa-)&tbU1kbOgqSN%W+Tw?W178?fnqg_%LROs4y?l{:|LfZQB2^=gR"fF;ei,yFv3vEKYiiXsq4Hn:7VhA
`
P8!upXphzV`D`O6`ra:]bEn[F@|rsPY:@"U%T@HhIwb=`GO9^m}>+wUY"7oj5<Rn<Iga9o]M?=GImmgg}N6nLh/K$D$VQPp#73bSFDfZ>L)OPYXC^(i]]n,N<,JQIYUGnqJM*3lT3do7=drazOu`Z_uDqcS8<aP2wIS%i-md95<+mN/eDWZVv[zMQgKIeep0@@=Bv&eL
#ey*fWiP%GP[/b.Vyk!VUuCOQJ3I+X>beON
(=x@KN"Eaw:Q@&$Z+xP;r.9-504Wop4>nnLnY&7x[|<OZfi^PR0tIw>gNvXPuEZiktx}rMl?y_"|4?.wP>M.90YlxZ2+@~+nH.6e2BEL7#_9K{pvr(UDL]kQE")qG@%OSP]H;=d1J.8StD,pWEAt&84tQ`p?fF"dY~YVEE3F_
rk)FJ-8{Kw8AHH`cusj+Z@BbqhFt:gwRM4MZ)yxR.9t;bT-$^rOqPR&PP=.X9iPy<gC{"T6,uYOo%O!Of
TH&KE<^iT)2&L0m)(M>Ge~XRpn/2Qa(X4L6!t3@a#s0j<8c~b+d!LcDOE5c/$kcn%4R)Y=?Y=Y#gNX)3bP4RXrMm70;^={;MIjCTNiIhOk?)_{([l~20e5QI.5)2(}S[1+B`:4@`d9[6H$Pi$R?4ELVrmLs7>OQ~1{ROFEZRq{IsDQ%RX%u?r_Hbe"KIt(gc%o7,;]gNl>a"e
+S#<2c@g93.X:q$?k1,Q?aj
Xx3:*#QSTTQ"(v.;6dx]*e3.!#o$C$XkPZuvxhj2uJ=W^zlVGdZUQAW+A5`egn;.?,E)[6`BomM];y3pR!.v<*Fw!@Uk[~SkK"fA18vM3eZZ/cA/?/Dh_b&SJ#_2R>(aQ!?QgTvdQX:dneE"P>QQX-v=e%KQTR`cI!.[wGxvOI$G;icw-~pgB2)f>8m[k@s>*>xHPs>yW}kk+*q"S<&X;dW^Q3/P[@B_*
euD&;]4y3Pw1whXPX8OR=tgN_[A-Q*1AFU++lz]mOhBvU3wKE_4@N1Md?@Tf]0m|5aQ}?4Jg/fS$h%a^OrHXX2DB7De?f+.P(U$QKuS@tSw4V9x7`%c+g8)@X
=/:@E3M5ca
l2U$QS/Ce@.aMn9X$>sYlR<4hKI4dsDlHoB*M=/h5Af;v*qM$O$h2,U-.4,[x;3W
QFojD,lw
Sv+Sw6
%p)wW$yI0kSRLnWB"&rHkaD0fW>xWt#-g^!|;sZc]aH/B"M},8?~8,ROu?P^nl6xFXec]Zxn8@H3>o24+:9,`^o{9]!Xd-7R1O;~s!mUO|=w<gTkO_%FK-mv+jAM;a,EvR[[u-w(kRlS:vy,m~RQ?Gf/fb._,lbIeae2ARVlcz$i<k
EBU%Ts/(V*>y448F/p5`>[=R]q{]aP6EDCudwDS^g=Ln/svI%_92AAI23KG[Vw7C^GouT#6Q$_eOEj+0=@Uy4HlxY4~D7@{K)!Y%o2wQUZE_bc^AH%mOm?;>X%6,W#HE5AzqFn+e<qD&w%fEuOv@(41p"6kxg+]]q+XZB>ee@R)u?M$R%:aWj::w:TKO6S)bCG]hLhU3C>Z!YGCf>EK0FW5u~poeujzsYd./ROaGc&JovH4*1&|L2N(nlW;t~h68ch1J49TMUgnFP;<#-#3bV?:Q/V@-5%0h!?1B"%BKBDn=Y9G)8ZRI5D
ylBB8rJ(;>tQh,ur*ALX=-X.8YK`(C4+BzUpv=C<g]YGNRF/.ZF7gN
o4G*20%up%M+b&Y$;XW85vk-6W!o^xZ935LUDEu7[xu1LWE6zMGKZB>XwB)ErW%r2Wm,euqe(WgV}CVZ/FaoQQD2^p`IdV/66d_=EQCXpoJ9[CPfzZ.[`(ZYdC.PCYa@.2@"j9ZuMj1^Ii{tj:gC}`~Q7&*23UX,AS.&%j!Pn^v?i9TJOQ1$i;=imYC<$fQha<m"Zj@+JK:@sc[LEnWnf@|c2!]_BFXezxzr8USUcE+/SQ~M9WcEqo?/"5ElB$nwD0=w>xbUy1GwJJF2)DBYIC_?0wp)e8&m|9Ou#q6>UDu%F>-pEgaRM
l7@OY+IfF^5B_UVXn[Dg|UVC"rWmcg7j?`b&&=L*92PJv^}u`F:X]+;lYcKs%#hp-&](([T4y5!aA[0KK:bU0W*N?%73BbnbOF
S-`($MG/
UI4vUtzqRcR?{<lbPDA:{rukvI(5hnMI(G
W*!@3a<lV(hdQcIdhj%PgOv]/
[,>Hn_cA%7B!?P;6j+bw9v:AxgN&';case"zh":return'*UF5`g".W1*S*vC!K#1m~LCW}vaVwSH5.$&Y4-8Cg.g<5YGNnN!9L/{QqjT"dKP*`.,BK>UVJyA:/r2vQt(CW):dpMbxlRCitV|?GKk8l`1qs6#@?Dsu$liVglD6u6%)%X=U)2)c2e4/`j]*PV5Y#@wJy9qc
x/=bo)f}po$uj7?u*/b]s#&&4CX[4D)7f*jxfWOdjj0xx
oX
4KAbs@cNvDFLTE3`06m#_0X8&^(#XE9p#ij$}n}WH
eIj(5]mJ<OL-HQfcy<66H(DX~^kGm7K6|gGX5M7g!]ln9GyVkrtB9QUXp8N0ZLx=Plb@IE&mTgwy<X=WL5q]A2MW|eDZ^(/,w^8.GGkQ?J_Q]]+$VHce:-(s#-vT4ZOb-T!GrTHMmbAN/?|6C9he)NBQpbYJ;s>iRH,rDN:R&D~<:X[?Pn@.N@=
F^Q]%ELRgl|Qt8N.TMLUrnLxW?eo1]ix|=-q@MtIb2V95Ph5.:L[&]nFHGX:g>~Z>T}n]%EYbMBrPUqi1XQfl<vYyP!%$I4(}
?W=7GiQaVKtM2ABq7R!c5!.vb/Tp.<.`r1)K_;Ds~nTv[>J]TpH02LriQ[%5Q`K0sbX7>,2]~<XUP$?&T?[[cmk*1g3->Vy*r%fVx9kTo_j3a<h@7+p)P./3xJ+vP;]6F5bKl*jd,DN(sx@kKV+Xg"_o(9Dkw)+k#(lMbA5>Y(J$POxaI$,,ouN:5X;r(%=%Tls?Dj}(W<rnclO?}Sax5Hzg^$QJu$/96
bUp$`M7?$*-H~V7b{ZT<*]H1jX!]7&67y
(dZrkamAf^p]=WTLKcjT+MGiAwlf{*3s%n!rl9;wNQmCqvK5ZU|suWUs}bSSxCuv:(9dnv@Q)900Su.Xlh$$cEnYC%=U=K1
B&B75g4X#+.BwqaZ2(T?d-NR@+:6NUgpWQ*qkEyPp!A:Q=r10diI{e
BLCdZ~bq!t.~w%whMXdLr[[4aByLPFH=t+t=N}E11p@;9tTXJzh1x@]qMbcI-#fh@5uzwg[!;IuH6^cTjR3A7<B&JCS;:CW_?<fXrJ:ei`i<&<Kx()5JrHGvu&lu5OPqvhUUqvYG/9MYTed/jkxz^1RBU8ZVTf/i0"Qv"W>I&>1Us:g`[l9<Sg3eW9:L:z6m4_5VIrtw$|MeR^!iF/ncUiS9y&K#^TC0`KFbRuB<Fc[O
sb`1axgJPQnC$/zPdP;Yc@?s7/B;!Xm+hiwd(E`].#ceLDj]P$vwXu=rM/b:mR~kt"!N@]S/}
cvBc:3X2q=/&9>ZI~uyJ}C^ts
-D/Eb0ASw]-GtcV%IX`_&u!yA?GS?!!H5m"=?Pz>4^4lTnd7f
{Ees=yj]!fB)ush]7t%5ceGM
z")/63vWN%J~
}dXJbpC7zGoc$l&
"McOw2/XX;7h}(t]VoXuKJ^R8Tk"d/ZIzvGz(:j3eU_g1qHi2*>trK;G"6YE1ddB$^)cLC]mGSi!R4,W:isC+qCT7G29C"4f?s7krXq@5,/USlaxow`rU>m$zjr_gS7@M&A$6ld^L0_HXAB#z%Sr=@B8~xk)rlT-H"}O
hEw=c+a#<=al%L&~&gk7YPAN;%MH@8vp2J(Amzu-itwKEeOm/a%6JjnP1"CEoK5jEP2yh84C!]dD5|,G]c_iM>or<%u@<f71VM(lEc[(ri.&S-%4$F;7MRS%T2;>P(=|twD_!x`W]W7[oW"nAv$i$fy`tT.E!{:Xrr&e$(dd8:gcvs62G+h5eV=M1
rPr^RpxV#}f8GlxPU=8cyLRCR.7K)8qtCO"r<I_vBmYB0+Nm%
:WC$<2/ZPPm0B]p_/-mhFv+}DH9d
+0bLLre0]`Db5lT-[;7prPy*ZlG+,(C=sI{S,^O0<#d/+s#*B]a<BRJlF9h@G:pRX;[$hYj/qSu9%sp7WFN#jJ:
3!-!W(%kCJ6h}!J"*I|9pGK,x?VB!p6adn.,3v(b4L,2>X!6RYp7?)@X`Vk$TkgG}>2u_HDtx`}4C1fdj4SS(;D^O"wT^Sf(`iY?PU{,?fRRo%29n!ZL"ICWWKU-ne9q"2u=WI)%*dAdhG}Ol;Ku"J:t
UOd!SY(oDm+7yGG}#9JZT5e3>[E}]z^@"EXF/{qy<2sTx2G5)>_1XU0(DSk>/O[9*)?iT7.NOw%L;6dJYt%
QgFsno7Ms)F`X2Ma-"0$%O>S*@2P#EtcV@)cm*p{z)PZ&}nO"&V|]7D]qpTzc@X*D`oTb&N@Bf]s7nG.rXE!f~1EAios<Cv0*Ghi-IOEvY._f5#YXDyu`FsC2[fs]PK<"S$,9hP6q9aMHbI_%]C]FIXd!<
|r.)G7pP:m.R$PtPCMwLcxR(^CI4Z
+"v2H:0q2f`iKfxJrCpI9@KtW[n_O-u,3lW6YQMy
Q4`ly^3BHnMpst6&KAVoBOv{#oD92.;:-WR2-H&&MSHxu-m/a(,eTk+bdn"HcZg84(7{BM1feUbKXfZ_(P8ruo:G2=(I`hVr6|o<@:xvR$%J<urw%)BTR~<-*b:tfH9+pCfw6D8
^+a
w,DZZl))TfWn_>H5gzAKT$Qh,eV3,pTYAc$F)t,*+ReS[n:DY22I!)>v5}R5?OHiQ6Jww<">]0U/qZU6TQ%0#t?Dgv:/f7/Z>EiJ?F3^oxIM?/Tv1=B1iKZ>M[jV9=.kbf]HnZcS;iPHWL3003gJu1/.T;`cFn@N?|?t2<,>ZAGVRQqI@wV-0=7n=Imx`vC
g0.A+/6U
Hb[uf&GK(R#]P
Ydsl"Mf#Sm+frB"-kxpJ8-)""bU>@[_;%DoU{"gwQZ3*)]v4Ul`;.@7T4YJ%,6U]zic:jHuY<"yp%@4FO7<IZ<krE3nZC4fDnEnFK*s3^Rg:91Sh>BtlXY5qgmxD&=x/a=P9+`;fkZ<J4xq/Dg"")B.RsJa/d%nDWKptWA"
^$m1[t;W;;2r,&Z)!b9tJT4-bYK*q53PNq/kU3t2FDcS:B)FT_vfhozDI6;AaIIIllA:TA{wmW;S_t}5B>70@>e_~03O<VMvr7UH?7of%I,`><2<5X;J
.?7JwA';case"zh-tw":return'(UF;2f{WrGlf3)ldJsA^5c:sN`XqzyW>k"m^X=o(@?;$:?6-
Vp9h0Z+"0Dr8(D#^0wv%LCZ<D.>Ki}]u8P5]7HY"ifNaaqrq&E[~F5*)Bo]ns`0G2ITVjIUgV!ZPur
u;^n3],?cY=MYj,X9(B<pR;v[t~SLV(w7ma?)1z8VDF1i/BX%>]cZC_R$MhikD1IKiK6c866T,0n[(:ew]DuVj!-.5@O1POxy]W2!vZ)t%)@;=37&I}s,JIYNkOC$?
cIcMmh04-npIi|n#L_/[D}_f_~=(S{t:N5E`>MhC@v]`oGVp/(!(cZ>JGQi<s>OmEOZ>75K$3LW6mvkDVGjy[~&AK`)IE(C*t0,ns{a.ryZ}C/oNsqU2K_szdiq>%Iy}EwR70ZSnwb^]dkGGw8Rp%$`Sh5!)M+?j,]a}8Q<gW$%`Pj)?6DJtrA?.DpDk#H+4EZ/`kL;Wb(D=sv
{1VG!g^JzF"1ZBW0i*T:Yn;IGCZjr!nklN9!Nk5ZDE#7o)<K:a-[2BRii]g>ZIAd>]X>hy+Bv:-mc)Qcg<VjgAB=:k6OPdJ[XbGGaB)W/&iPB*uJf.8KqphvLpdP!`j*6/$xb)1Nt1^Q_8)pFvA.BP>SRvWip8jgmhr(A?re:r=E-BRyjJ_+I8A)QVSBL=.hxUp3TIb)9;g2m%.L`n$EdOBYKWCy6bW8y4{oLy5%A6K@,8BnATrEW_h2zuS])m_Vd.G5*e`E*5e+zEZ/@OD>]
s]&eo-$K8ef^G[5iNcb91%rm/"eF&x_`maf8gL]1XgO%^e!wMc&H.LqL0MsL7n_^5O{Z=/(rvmM=S`]4cKR!g:V:_lo%B3kAg-M`8T+
&0vZt&82:h<kD8q283~:4hQ+]h6?-Bi*".jx@u>H]MAge^^tGb5IQ/jwN8U
cZwg}XN4([PH0k%)>M`^RS^I<5$,;IdlC@RaJM-s3csp).4.!.F>^C`i)*9ol(xM.h*v"GBOng%(s_22Gor5x`YN|dh0^x7yaq5B2VkWn3QPJwrNAY)F~K5iuy%?V7DO9P`V|_67DB{5}<Ucl!Bh}uojc*w.V+w
fZV%5]-W/4+a-)r88i5JDKo7{Chh-+ZmZ?x5clVIpY<uH-OB(>{B4+07X2YuAiqX![W/:1xF#!}r+w;I?"Gidyfl5jE]L_7!zJ4kM?`f_%p/OCz`AnAsw!U"LO2HdvC#.ml;1xmA>q%mdXItFmM_scW9|AS6Xh-er-95-AqQe=.b7n0"QIR,[k]C|XDJbGOWM#~MoZr(OY,&;4L95"PpqT%k.e]E&I<BW-?16k*y`EFiA$gEopK]XfkyH>EG0e}I)iM%aDQ+rxgM4+GgPsXTOkqTou}r)_OSSjb<L%bU?e6f5`FDCd!RW]NUtVImlCwTp$-gx3K/RnuQ|9)ev]rxz!+uy^#;rNhtLo%_2d:KA@;4;v""l":BDa<*tHt]MI$@Z]uKz7xs
$1)/dw1!5IJoB
+>vBn#gJE0?W1IR?r^BD/]eG^Xe57+6w(^([N.c]!nU|JHDL[oI*5mZgB}<3LkAe-)2UQ3T]SpQ[E=aBA%c>$;Cliz-Q$D
t!E`QR>c5/lJ*@ao7n+rT?Q8(Y>8
nZ$w.rpX_u"Rcmyo<"`;E1eSus<|FN>r_wor-U,i521U_}$1&&Aen<"NX#&"Ko7X^4_VMhC-/{$gkqt]VZo>GJO+2rL}84V"sg[V8ceSY6w|-&PBk%GKH.6lmyb9MC9,JW6]z)i9:W9+6?p|#CN,sU?J*%D&:<ag%hqNR=i?=(sn0{i)[-8A&Z)5]saqLyOy-pgs[F8z@{?w/=st@=7qhJ4[??btu;"dSw#S.?&?LN,9CMuLDN!4;a>3.(;CM">B=!(39C8xcS!g,.L1]B<@uzxU/1_r!j^!]X=kW:Re6/WTc3axdwmG@YZP.:+`aN;UAr#6HdCe-((wcyV`fp]"PFMB0e@#.QE?#U-HY)&"5U6%vl:qw+`}n>P&%Hvzl%]{+c`xD""s>b<J`iNhl&opx*M=a&;4U:A9f/lZt6hTAd.(:j?J?1T`o7gj]%5PQ"!0cqAs^jNjo82TjKc%*X=}!}wa;df5e@E3tzP~>{1;T`)p#?R:9fa}tJHMnoLg=m[[kiY>xf9&?$&07pJ?".P~YHq
lki~-&UJ81(2Or=#bC"#)6K=_h(LXg[L)Z8ez)o8(f+o9^=U3crU0^*ts6D$qLh]+{A<PSO7JsH"lP5Wto@
W%GO]{[@eMs4u6x19X:h]EJ24%AkA[o`t
g^RQ:vMV<=WEF}KA8o<r.n-e@Q$~F?:jv;]4K4G4`BpT=-X@e,FIC3C1$SYkQE/[:b@u#@,G`r!UDeRfK4_xxnuV6XgfkL9t$51Jl0*zLLP1PPWDM[Y#&<EzkyVneEsdf9MWx<@`/k#yTogubH()M}4]fWx|eP^jq-ILo#U!g/40Y7)&$cl[#dIicsu9#Ha}4df{ceVwxPEjV5_gfVAQnB`8ABug"!8]a4P$#7[ugy]e$ftCn*f*`@YG67(l?u$U,?&4uBYR39r#xO;|j}*e&VER>^>W7<EqgJ&y,0aVwQD)(09m<se)BDL5A@ZX)L-HHn1OrycH,0fBn9::x^O>D/[3bPmbg5T~G=^{g}(U@*T"-*#nhsj!YSpW3F[94fcs;UNU"xE4nX=ewN1,S,D/Hyn^M=2ytnwarw#If
LjYo!CI>_LCdP,&D"=Bg+FUeP7b&m!EXy"h=KOlIw71jOtb$aa5~&{^Z7D+gxpbbQ9K.KMeeq=2R@tkjktbBvNt(X>g{2$S_OMYIZKX"B1E`t,.yl-P#H*N;+pR9P<Xp!XPAY6stUntVss5g#eyF-FoZ.iBA9AH~4>qQms!XQHfPGmW83[,.&:7*>>bT,N5A=fSr*X
bazHrCe"+lC#Z<"%B#UTkkhDFOU2Qn6qP+&PB8[g*e.E}l7xUO2vi$fqjIIibc7m#"H3)h;>KIm1#QH##-t]B$&,i/3[q4Ixi6U9ekP#e&v@jfJR7!$.09yc@W2E[
1)KP@6OC*j*^|GU]G(MVJHn718[qIV_$I,zOfdX&wHEf5CV-VP#!;*F;noN7+B&/8.<ks"X^{TmCQy7;ZNh4mvmKyi=rj-W;.7+pZ1_e3p|;B="fO6bTel>""';case"ko":return')Zu1$g~Z+/fR|L`N{5eZoBxw._ug;sl^~cU/&"G:rYTjL-u$qT@";Fwg?Oi(E!YUO;qcd#KK&%iv}R3")?dS;em^Y&U[lM1bAiP,6)5<lcWdr1LRN.Z0>hQ&>Mw^;MA/g[
>h+V,aA%bx.?,q"YoC/e@|[7D26Pa4u#,;MHtl":iBqz_Wg:7Q`zQ~aRHk3<&;+qf8E_kM:b0A0(RN]9$"=fnD-h;[L;QUuw@aKVi(lr^Xy[B,hNaU%G]K=89k420I=/QccOI.kl&QC}4JE:rmcX$r:NH~pJxiv:/+`g<O
lv$cqBVM5d%->JAk&;
.$GSRn7B9[>nXuw`mof6(lp*#c,q:Xge6])"8vP1HAHAoH;|&~?}vtIjaB*@@"!`Aj3E1~IvRmn[wf[WY_Q1pk/,^:v0M}7^Uarss@Z]nu9=Z|v3avElwK;F+V"j$Y!}<_rHU1DW07mzjAE8#Hc|8-eM7&bwd0)(-+p[<0*OwO("1s[MWKh/`.C!Lt%gbrupA?$hO;oNfho9-Z%.i&dOKsaQKEc|SElS])v&_|Xt2d0CggK?^{)[2A0V8!uL>2Pxj[:9:Z%L-3DFFpl?U4D+Vffsv;>bj?rH&+%MG6Izh;&@>#VAcEOcBkHW"hnWP(/<0Ob9.<3B9*UwY28%
o_`COcU"yC42^sgKF-si2?ylHp^*-_P/Wdl/5E%fZjnNfu`oBj#73CCd{7#i":m*`UJswq:^;
]Uld"0%%efA_2P/4ny$UG3AJDfPa#6-
l_3Rm)?M,oBaz4<U$y@h*8"r%0*su:y&,/Z]rc6$:N*2_xZr1o9s;K?vn;n/bG;*C58("o>u4xx>[S{g^>l/.e3&!t
8c^!QOwa6_Ak[%r|anWtMalGp/MDo8f(
1uG/yGu9#ZH?OfzINC%N?Kpu3jL35dfQOIE96/MO[CqK}1C)4#1dJW@.$N/7v+{MZT_B6$aLePeU}II.KDoa-8eQzkAJ9W^R4F%Q-v#/1>(.8r@%D-ky
x|H"aPW}w)VVl&Hp6;blLoEa<;NZlO`#^8gR7[Tk8h$aLKYb-~A=iA("*p`-2YKHm{U#P=T>G3yGtW>Gw^XoE*yztDi>S0kxMD/~o0?PrffySqy7:+SyyXY(<LS5)*<2_B=ky$G0L~C~C(x00gM{uu=G%b+][lLLn=n_<T,OF3&3(?P=@UCHT7)>Or:,Q(^AAs=O1pp%KWm,.j"$K*4=!&
F>RO{+NvjJCM($Upe[b$a4Jb!a~RC7&axP}]T67OXpq[~oGsFRp^#eKQ]G/oxW]Z3b1qwIl+^18(]HsFp,QH2)!S"!HuN^otyCRi*44SEla)94PM1W0#QKmF%U$[}]^<8#Hk8-ND;R@Qw),>"e^f1UL-Q6i,LfWC*HXU~Qb3lSa9!CgQ#!iyFK-:yK0Bgc

z%b$kJxsyAkjGTAlCTI&^WT#$`M0O*L;7+SHtBrL~d-yb)"aZBeF
<&c{sYM^rOhrTQ.?5(<4n,4BLg]i]|y5DGI#:?B7gyE
@61dkXtU0KGK&x
+BB2ybE"j(HMt*Vlf^Mybx*<?L"SM/i]sm>(mEKN&=RnOU|&o7$r1^/Ey&^<@?sbbX5)1Qm%]SbH3t/[o&o31%v6mt.o#K`d(,
YYO#9~3=Fzu!pH%$alsXb#3c+RHG]`JdHpY1ABLx&^E!wSxX-4?~Wv>`01X3svS_do*i@=(fL@c81C!3;FSriM+<G2O;eHt}K!Tis3&7=4,ce!^bAK=]0vD|UrXA-2@],M(3D}46!I#mZ~t="oEhHV+P:#-W%S_]YmG{xgFmc7x7v|2fwU.qW1.v$,[dj&UtD5gkD3f:I>V:4^"!#82UyZUj;ObNy7-@hz1JMDUwGuKcl~AQ.;;dM?y3>2kpbDWn$xr4
E@uOuw1(VP[W.X-BvJHx1(1rYCMd]^s)V4NrTm6X.qHGDPeuqVQaB)&%&hnGk?{6_8DM6jQ^=Q1B@TCl2#=[W#iuQ7!Jd-^QIROT%S}e=H]<X0y_
=~;TR?+EP{?1leb:hl01SHFs)8b@os=wK[
r?KlHpUl?KaZZ@]nrNi!J1|W+#
3,;dH9X1J7Lvpmmf*z#m=:s)Fq]jdP=X"HZ#H0v8SG7YWgl6R#td=~&Ch"r5uTSSe&RKh!Q)BjDh6E$<x]PB1*;q0I>4JDsj(ygN$gsTjlH<O3f]C?x"$$v%)Yt{`R)6/uXF-C]Pj!bcs*yXSPkUYW.tvTO$f_nyfeZj>_&/_/-+j=Y%qKH+?<u(%:>@%AO&Xua=AQqI4g@iM/4p&h
}#q]#!r&dEFQ#)nv]-r
&v:5vA
(mScs(IWGibybbWI&lU#2,$*jf^7],V(7cAv)n#[Z_Nc<fX}F+V5]:<wZY_^mP,v#aRH/n
_I>3nPRBI(~[F`#i&^&gC:Q@b%lDT)}>!sW29=P?`VUDhk,N`!`?iO89oU2uYd8M8_s,.N{;)Sy!]dtO=$PoFr`I]NjjBuqc(1NlyX)JOw0,$cjW
eBNkZq^W:V,oJ~`-u(/[.ZdvCx4ul%%3RH%~"byFA@hy;zi"jXUo#KCBj;.I!YHHwYQX4t,lh$PY`=KJU8XD;.f(R<(!ei/8R1fyk6lg!fk)D-A~QmlNuty@hjp@RqGDG/mHEzHU[yB"K."b"qZ*A-RjK9o@[r!(uNP7haN<BkV6l0u>[kb6gF,9A,754upTAWHt91q"]@1K6fV-SCtl]}RH:JMFmu[NP"Mn
Ti#G!Q*qKA5S-vPp};_.e!/!v_&L_4E**wwn%&qmP)<M>nUQVmaG*R=37i-W%fW=w,~sznn>>1`35"PPp,.[APhiZ.GDKb3x^dD7tkmxiC-67[tHI<ZmiE2]O$EL3+lB[_~<CWF^]w{hl,<lC`-0ekZ%Be6Z5oLjm/hm9W]@sGxa^P62)SM7S,O$lc?V#_
]Ao6JKcZ!8UGh
H*)LL8"9ho?fogFjR+4cS(=;<tO}H1N<2oo7
-]sD8Mx<F:fB,9A8f>7/UX}.iv
P-:@:Nu|A]nx2sYP=z
M9avIF;wg5_H}/MiGL;dxJ7]
0XkKt?@TS"1zV+JMWK1S>0]:_-t2eYqkwvo.GwUY&T5dZc"NL=$g_#&REWTM&X*$sjs]IS03d|_BFg@jt_y$%/U<FzTb)%o<*OBeOL^5@dIU-S1{=VC.U^n3Lk5}F}qGWvC71(kz3lTC`GXY1IB#!H@,Trt[QnH!
EiS4#moM0T6spS[)TEW+3L9i%`t;MRYMi`W[UX0d-^IuKmf]9O^:]GyO[PyPrdKm7`#1h_lXis9:sKz?c=Cd{78H7xi^u=YyO.^&#"(PxlG622(7!Hmd0>vMw+]';}return"";}$Gm=LANG.crc32(get_compressed(LANG));$Fm=$_SESSION["translations"];if(!is_string($Fm)||$_SESSION["translations_version"]!=$Gm){$Fm=decompress_string(get_compressed(LANG),(LANG!="en"?decompress_string(get_compressed("en")):""));$_SESSION["translations"]=$Fm;$_SESSION["translations_version"]=$Gm;}Lang::$translations=array();foreach(explode("\n",$Fm)as$W)Lang::$translations[]=(strpos($W,"\t")?explode("\t",$W):$W);abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach(array$M,$U,$D);abstract
function
quote($P);abstract
function
select_db($tc);abstract
function
query($F,$Vm=false);function
multi_query($F){return$this->multi=$this->query($F);}function
store_result(){return$this->multi;}function
next_result(){return
false;}function
inTransaction(){return
false;}function
begin(){return!!$this->query("BEGIN");}function
commit(){return!!$this->query("COMMIT");}function
rollback(){return!!$this->query("ROLLBACK");}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($ed,$U,$D,array$B=array(),$zb='PDO'){$B[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$B[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new$zb($ed,$U,$D,$B);}catch(\Exception$Ad){return$Ad->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($P){return$this->pdo->quote($P);}function
query($F,$Vm=false){$G=$this->pdo->query($F);$this->error="";if(!$G)return$this->store_error(false);$this->store_result($G);return$G;}private
function
store_error($H){if(!$H){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(26);}return$H;}function
store_result($G=null){if(!$G){$G=$this->multi;if(!$G)return
false;}if($G->columnCount()){$G->num_rows=$G->rowCount();return$G;}$this->affected_rows=$G->rowCount();return
true;}function
next_result(){$G=$this->multi;if(!is_object($G))return
false;$G->_offset=0;return@$G->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}function
begin(){return$this->store_error($this->pdo->beginTransaction());}function
commit(){return!$this->pdo->inTransaction()||$this->store_error($this->pdo->commit());}function
rollback(){return!$this->pdo->inTransaction()||$this->store_error($this->pdo->rollBack());}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($oh){$H=$this->fetch($oh);return($H?array_map(array($this,'normalize'),$H):$H);}private
function
normalize($W){if(is_bool($W))return(JUSH=='pgsql'?($W?"t":"f"):+$W);if(PHP_VERSION_ID<70100&&is_float($W)&&is_finite($W)){for($xj=15;$xj<17;$xj++){$H=sprintf("%.$xj"."G",$W);if((float)$H===$W)return$H;}return
sprintf("%.17G",$W);}return(is_resource($W)?stream_get_contents($W):$W);}function
fetch_field(){return(object)$this->getColumnMeta($this->_offset++);}function
seek($bi){for($r=0;$r<$bi;$r++)$this->fetch();}}}function
add_driver($s,$A){SqlDriver::$drivers[$s]=$A;}function
get_driver($s){return
SqlDriver::$drivers[$s];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;static$passwords=true;static$serverSchemes=array();static$serverPorts=array();static$serverSocket=false;static$serverPath=false;static$serverFile=false;protected$conn;protected$types=array();var$delimiter=";";var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$fulltextOperator="AGAINST";var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();var$primary="";var$query="";static
function
jushModule(){return"";}static
function
jushAutocomplete(array$S,$Bl){$dm=array();foreach($S
as$Q=>$O){if(!$O["dependent"])$dm[$Q]=array();}foreach(driver()->allFields()as$Q=>$m){foreach($m
as$l)$dm[$Q][]=$l["field"];}return"jush.autocompleteSql('".idf_escape("")."', ".json_encode($dm).", ".json_encode($Bl).")";}static
function
connect($M,$U,$D){if(static::$serverFile)$bj=server_parts(array("path"=>$M));else{$bj=parse_server($M);if(!$bj||($bj["scheme"]&&!in_array($bj["scheme"],static::$serverSchemes))||($bj["socket"]&&!static::$serverSocket)||($bj["path"]&&!static::$serverPath)||(substr($bj["host"],0,1)=="/"&&!static::$serverSocket))return
lang(27);if($bj["port"]!=""&&($bj["port"]>65535||($bj["port"]<1024&&!in_array($bj["port"],static::$serverPorts))))return
lang(28);}$f=new
Db;return($f->attach($bj,$U,$D)?:$f);}static
function
disconnect(){}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$l){}function
unconvertFunction(array$l){}function
select($Q,array$L,array$Z,array$q,array$wi=array(),$y=1,$C=0,$Ej=false){$Rf=(count($q)<count($L));$F=adminer()->selectQueryBuild($L,$Z,$q,$wi,$y,$C);if(!$F)$F="SELECT".limit(($_GET["page"]!="last"&&$y&&$q&&$Rf&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$L)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($q&&$Rf?"\nGROUP BY ".implode(", ",$q):"").($wi?"\nORDER BY ".implode(", ",$wi):""),$y,($C?$y*$C:0),"\n");$this->query=$F;$_l=microtime(true);$H=$this->conn->query($F,(!$y&&!$Ej?1:0));if($Ej)echo
adminer()->selectQuery($F,$_l,!$H);return$H;}function
delete($Q,$Nj,$y=0){$F="FROM ".table($Q);return
queries("DELETE".($y?limit1($Q,$F,$Nj):" $F$Nj"));}function
update($Q,array$N,$Nj,$y=0,$Ok="\n"){$Y=array();foreach($N
as$w=>$W)$Y[]="$w = $W";$F=table($Q)." SET$Ok".implode(",$Ok",$Y);return
queries("UPDATE".($y?limit1($Q,$F,$Nj,$Ok):" $F$Nj"));}function
insert($Q,array$N){return
queries("INSERT INTO ".table($Q).($N?" (".implode(", ",array_keys($N)).")\nVALUES (".implode(", ",$N).")":" DEFAULT VALUES").$this->insertReturning($Q));}function
insertReturning($Q){return"";}function
insertUpdate($Q,array$J,array$Cj){foreach($J
as$N){$Z=array();foreach($N
as$w=>$W){if(isset($Cj[idf_unescape($w)]))$Z[]="$w = $W";}if(!($Z&&$this->update($Q,$N," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($Q,$N))return
false;}return
true;}function
begin(){remember_query("BEGIN");return$this->conn->begin();}function
commit(){remember_query("COMMIT");return$this->conn->commit();}function
rollback(){remember_query("ROLLBACK");return$this->conn->rollback();}function
slowQuery($F,$rm){}function
operators($Pl){return
array();}function
convertSearch($t,array$W,array$l){return$t;}function
value($W,array$l){return(method_exists($this->conn,'value')?$this->conn->value($W,$l):$W);}function
quoteBinary($yk){return
q($yk);}function
md5($d,array$l){}function
typeName(\stdClass$l){return(isset($l->native_type)?$l->native_type:"");}function
warnings(){}function
tableHelp($A,$Vf=false){}function
inheritsFrom($Q){return
array();}function
inheritedTables($Q){return
array();}function
partitionsInfo($Q){return
array();}function
hasCStyleEscapes(){return
false;}function
hasEstimatedRows(){return
false;}function
isSystem($i,$K=""){return
information_schema($i,$K);}function
lineComment(){return"--";}function
engines(){return
array();}function
supportsIndex(array$R){return!is_view($R);}function
supportsAlterIndex(array$R){return
true;}function
supportsAlterTable(array$Pl){return
true;}function
indexAlgorithms(array$Pl){return
array();}function
indexOpclasses(){return
array();}function
shadowTables($Q){return
array();}function
fulltextSql($A,array$u,$F,$cb){return"MATCH (".implode(", ",array_map('Adminer\idf_escape',$u["columns"])).") AGAINST (".q($F).($cb?" IN BOOLEAN MODE":"").")";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$H=array();if(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$I){$I["null"]=($I["nullable"]=="YES");$H[$I["tab"]][]=$I;}}return$H;}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($ud,$k){if(ini_bool("html_errors"))$k=html_entity_decode(strip_tags($k));$k=preg_replace('~^[^:]*: ~','',$k);$this->error=$k;}function
attach(array$M,$U,$D){$i=adminer()->database();set_error_handler(array($this,'_error'));$qj=$M["port"];$ef=($M["host"]?:$M["socket"]);$this->string="host='$ef'".($qj?" port=$qj":"")." user='".addcslashes($U,"'\\")."' password='".addcslashes($D,"'\\")."'";$zl=adminer()->connectSsl();if(isset($zl["mode"]))$this->string
.=" sslmode=$zl[mode]";$this->link=@pg_connect("$this->string dbname='".($i!=""?addcslashes($i,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$i!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($P){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$P):"'".pg_escape_string($this->link,$P)."'");}function
value($W,array$l){return($l["type"]=="bytea"&&$W!==null?pg_unescape_bytea($W):$W);}function
select_db($tc){if($tc==adminer()->database())return$this->database;$H=@pg_connect("$this->string dbname='".addcslashes($tc,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($H)$this->link=$H;return$H;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($F,$Vm=false){if(self::$untrusted)$G=(@pg_query($this->link,"BEGIN READ ONLY")?@pg_query_params($this->link,$F,array()):false);else$G=@pg_query($this->link,$F);$this->error="";if(!$G){$this->error=pg_last_error($this->link);$H=false;}elseif(!pg_num_fields($G)){$this->affected_rows=pg_affected_rows($G);$H=true;}else$H=new
Result($G);if(self::$untrusted)@pg_query($this->link,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$H;}function
warnings(){if(PHP_VERSION_ID>=70100){$H=implode("\n",pg_last_notice($this->link,PGSQL_NOTICE_ALL));pg_last_notice($this->link,PGSQL_NOTICE_CLEAR);}else$H=pg_last_notice($this->link);return
nl_br(h($H));}function
inTransaction(){$O=pg_transaction_status($this->link);return$O==PGSQL_TRANSACTION_INTRANS||$O==PGSQL_TRANSACTION_INERROR;}function
copyFrom($Q,array$J){$this->error='';set_error_handler(function($ud,$k){$this->error=(ini_bool('html_errors')?html_entity_decode($k):$k);return
true;});$H=pg_copy_from($this->link,$Q,$J);restore_error_handler();return$H;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($G){$this->result=$G;$this->num_rows=pg_num_rows($G);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$H=new
\stdClass;$H->orgtable=pg_field_table($this->result,$d);$H->name=pg_field_name($this->result,$d);$H->native_type=pg_field_type($this->result,$d);return$H;}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach(array$M,$U,$D){$i=adminer()->database();$qj=$M["port"];$ef=($M["host"]?:$M["socket"]);$ed="pgsql:host='$ef'".($qj?" port=$qj":"")." client_encoding=utf8 dbname='".($i!=""?addcslashes($i,"'\\"):"postgres")."'";$zl=adminer()->connectSsl();if(isset($zl["mode"]))$ed
.=" sslmode=$zl[mode]";return$this->dsn($ed,$U,$D);}function
select_db($tc){return(adminer()->database()==$tc);}function
query($F,$Vm=false){$H=(self::$untrusted?$this->readOnlyQuery($F):parent::query($F,$Vm));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$H;}private
function
readOnlyQuery($F){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){list(,$this->errno,$this->error)=$this->pdo->errorInfo();return
false;}$G=$this->pdo->prepare($F);$H=false;if($G&&$G->execute()){$this->store_result($G);$H=$G;}else{list(,$this->errno,$this->error)=($G?$G->errorInfo():$this->pdo->errorInfo());if(!$this->error)$this->error=lang(26);}$this->pdo->query("COMMIT");return$H;}function
warnings(){}function
copyFrom($Q,array$J){$H=$this->pdo->pgsqlCopyFromArray($Q,$J);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$H;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($F){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$F),$_)){$J=explode("\n",$_[2]);$this->multi=false;$this->affected_rows=count($J);return$this->copyFrom($_[1],$J);}return
parent::multi_query($F);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";static$serverSocket=true;var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";private$userTypes=array();function
operators($Pl){return
array("=","<",">","<=",">=","!=","~","~*","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$D){$f=parent::connect($M,$U,$D);if(is_string($f))return$f;$Bn=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$Bn)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$Bn);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(29)=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),lang(30)=>array("date"=>10,"time"=>8,"timestamp"=>19,"timestamptz"=>25,"interval"=>0),lang(31)=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),lang(32)=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),lang(33)=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),lang(34)=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types[lang(31)]["json"]=4294967295;$this->types[lang(35)]=array("int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0);if(min_version(9.4,0,$f))$this->types[lang(31)]["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f)){$this->generated[]="STORED";if(min_version(18,0,$f))$this->generated[]="VIRTUAL";}$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$l){$ci=$this->userTypes[$l["type"]];return($ci&&!preg_match('~]$~',$l["length"])?type_values($ci):"");}function
setUserTypes(array$Um){$this->userTypes=array_flip($Um);$this->types[lang(0)]=array_fill_keys(array_keys($this->userTypes),0);}function
insertReturning($Q){$Na=array_filter(fields($Q),function($l){return$l['auto_increment'];});return(count($Na)==1?" RETURNING ".idf_escape(key($Na)):"");}function
insertUpdate($Q,array$J,array$Cj){$e=array_keys(reset($J));$Pb=array();$gn=array();foreach($e
as$w){if(isset($Cj[idf_unescape($w)]))$Pb[]=$w;else$gn[]="$w = EXCLUDED.$w";}if(!$Pb||!min_version(9.5)||count($Pb)!=count($Cj))return
parent::insertUpdate($Q,$J,$Cj);$yj="INSERT INTO ".table($Q)." (".implode(", ",$e).") VALUES\n";$Il="\nON CONFLICT (".implode(", ",$Pb).")".($gn?" DO UPDATE SET ".implode(", ",$gn):" DO NOTHING");$Y=array();$x=0;foreach($J
as$N){$X="(".implode(", ",$N).")";if($Y&&strlen($yj)+$x+strlen($X)+strlen($Il)>1e6){if(!queries($yj.implode(",\n",$Y).$Il))return
false;$Y=array();$x=0;}$Y[]=$X;$x+=strlen($X)+2;}return
queries($yj.implode(",\n",$Y).$Il);}function
slowQuery($F,$rm){$this->conn->query("SET statement_timeout = ".(1000*$rm));$this->conn->timeout=1000*$rm;return$F;}function
convertSearch($t,array$W,array$l){$hj=preg_match('(LIKE|^!?~)',$W["op"]);$Ch=preg_match('~^(character( varying)?|text|citext|bpchar|name)$~',$l["type"])||(!$hj&&preg_match('~'.number_type().'|^(date|time|timetz|timestamp|timestamptz|boolean)$~',$l["type"]));return($Ch&&!preg_match('~\[]$~',$l["full_type"])?$t:"CAST($t AS text)");}function
quoteBinary($yk){return"'\\x".bin2hex($yk)."'";}function
md5($d,array$l){if(is_blob($l)||preg_match('~'.text_type().'~',$l["type"]))return"MD5($d)";}function
warnings(){return$this->conn->warnings();}function
tableHelp($A,$Vf=false){$Ag=array("information_schema"=>"infoschema","pg_catalog"=>($Vf?"view":"catalog"),);$z=$Ag[$_GET["ns"]];if($z)return"$z-".str_replace("_","-",$A).".html";}function
inheritsFrom($Q){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
inheritedTables($Q){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
partitionsInfo($Q){$I=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($Q))->fetch_assoc():null);if($I){$c=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $I[partrelid] AND attnum IN (".str_replace(" ",", ",$I["partattrs"]).")");$fb=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$fb[$I["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$c)),);}return
array();}function
tableOid($Q){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($Q)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
allFields(){$H=array();$J=get_rows("SELECT c.relname AS tab, a.attname AS field, a.attnotnull::int,
	format_type(a.atttypid, a.atttypmod) AS full_type, i.indrelid AS primary
FROM pg_class c
JOIN pg_attribute a ON a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped
LEFT JOIN pg_index i ON i.indrelid = c.oid AND i.indisprimary AND a.attnum = ANY(i.indkey)
WHERE c.relnamespace = $this->nsOid
AND c.relkind IN ('r', 'm', 'v', 'f', 'p')".(min_version(10)?"
AND c.relispartition IS NOT TRUE":"")."
ORDER BY c.relname, a.attnum",$this->conn);foreach($J
as$I){parse_full_type($I);$I["null"]=!$I["attnotnull"];$H[$I["tab"]][]=$I;}return$H;}function
indexAlgorithms(array$Pl){static$H=array();if(!$H)$H=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$H;}function
indexOpclasses(){static$H=array();if(!$H&&$this->conn->flavor!='cockroach')$H=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$H;}function
supportsIndex(array$R){return$R["Engine"]!="view";}function
hasCStyleEscapes(){static$hb;if($hb===null)$hb=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$hb;}function
hasEstimatedRows(){return
true;}function
isSystem($i,$K=""){return($K!=""?information_schema($i,$K)||preg_match('~^pg_~',$K):in_array($i,array("postgres","template1"))||($this->conn->flavor=='cockroach'&&$i=="system"));}}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return($_POST["schema_style"]===""&&$_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($t);}function
get_databases($he){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$y,$bi=0,$Ok=" "){return" $F$Z".($y?$Ok."LIMIT $y".($bi?" OFFSET $bi":""):"");}function
limit1($Q,$F,$Z,$Ok="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$Ok):" $F".(is_view(table_status1($Q))?$Z:$Ok."WHERE (tableoid, ctid) = (SELECT tableoid, ctid FROM ".table($Q).$Z.$Ok."LIMIT 1)"));}function
db_collation($i,array$Eb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($i));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$F="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$F
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$F
.="
ORDER BY 1";return
get_key_vals($F);}function
count_tables(array$h){$H=array();foreach($h
as$i){if(connection()->select_db($i))$H[$i]=count(tables_list());}return$H;}function
table_status($A="",$Od=false){static$Qe;if($Qe===null)$Qe=get_val("SELECT 'pg_table_size'::regproc");$Sk=(!$Od&&min_version(10));$H=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Qe?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".($Sk?"seq.last_value":"NULL")." AS \"Auto_increment\"".(min_version(10)?",
	relispartition::int AS dependent":"")."
FROM pg_class c
".($Sk?"LEFT JOIN (
	SELECT d.refobjid, max(s.last_value) AS last_value
	FROM pg_depend d
	JOIN pg_class sc ON sc.oid = d.objid AND sc.relkind = 'S' AND sc.relnamespace = ".driver()->nsOid."
	JOIN pg_sequences s ON s.schemaname = current_schema() AND s.sequencename = sc.relname
	WHERE d.classid = 'pg_class'::regclass AND d.refclassid = 'pg_class'::regclass AND d.deptype IN ('a', 'i')
	".($A!=""?"AND d.refobjid = ".driver()->tableOid($A):"")."
	GROUP BY d.refobjid
) seq ON seq.refobjid = c.oid
":"")."WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($A!=""?"AND relname = ".q($A):"ORDER BY relname"))as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$R){return
in_array($R["Engine"],array("view","materialized view"));}function
fk_support(array$R){return
true;}function
parse_full_type(array&$I){static$xa=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz','time without time zone'=>'time','time with time zone'=>'timetz',);preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$I["full_type"],$_);list(,$T,$x,$I["length"],$oa,$Ga)=$_;$I["length"].=$Ga;$sb=$T.$oa;if(isset($xa[$sb])){$I["type"]=$xa[$sb];$I["full_type"]=$I["type"].$x.$Ga;}else{$Wm=idf_unescape($T);$I["type"]=(is_user_type($Wm)?$Wm:$T);$I["full_type"]=$I["type"].$x.$oa.$Ga;}}function
fields($Q){$H=array();foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	t.typcategory,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
JOIN pg_type t ON t.oid = a.atttypid
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".driver()->tableOid($Q)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$I){parse_full_type($I);if(in_array($I['attidentity'],array('a','d')))$I['default']='GENERATED '.($I['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$I["generated"]=idx(array("s"=>"STORED","v"=>"VIRTUAL"),$I["attgenerated"],"");$I["composite"]=($I["typcategory"]=="C");$I["null"]=!$I["attnotnull"];$I["auto_increment"]=$I['attidentity']||preg_match('~^nextval\(~i',$I["default"])||preg_match('~^unique_rowid\(~',$I["default"]);$I["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(!$I['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$I["default"],$_)&&($_[2]!=""||preg_match("~^('.*'|NULL)\$~s",$_[1])))$I["default"]=($_[1]=="NULL"?null:idf_unescape($_[1]).$_[2]);$H[$I["field"]]=$I;}return$H;}function
indexes($Q,$g=null){$g=connection($g);$H=array();$Wl=driver()->tableOid($Q);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Wl AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($g->flavor=='cockroach'?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Wl
ORDER BY indisprimary DESC, indisunique DESC",$g)as$I){$ck=$I["relname"];$H[$ck]["type"]=($I["indisprimary"]?"PRIMARY":($I["indisunique"]?"UNIQUE":"INDEX"));$H[$ck]["columns"]=array();$H[$ck]["descs"]=array();$H[$ck]["algorithm"]=$I["amname"];$H[$ck]["partial"]=$I["partial"];$yf=preg_split('~(?<=\)), (?=\()~',$I["indexpr"]);foreach(explode(" ",$I["indkey"])as$zf)$H[$ck]["columns"][]=($zf?$e[$zf]:array_shift($yf));foreach(explode(" ",$I["indoption"])as$_f)$H[$ck]["descs"][]=(intval($_f)&1?'1':null);$H[$ck]["opclasses"]=($I["opclasses"]!=""?explode(" ",$I["opclasses"]):array());$H[$ck]["lengths"]=array();}return$H;}function
foreign_keys($Q){$H=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($Q)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$I){$I['deferrable']=($I['deferrable']?'':'NOT ').'DEFERRABLE'.($I['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$I['definition'],$_)){$I['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$_[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$_[2],$Jg)){$I['ns']=idf_unescape($Jg[2]);$I['table']=idf_unescape($Jg[4]);}$I['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$_[3])));$I['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$_[4],$Jg)?$Jg[1]:'NO ACTION');$I['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$_[4],$Jg)?$Jg[1]:'NO ACTION');$H[$I['conname']]=$I;}}return$H;}function
view($A){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($A).")")));}function
collations(){return
array();}function
information_schema($i,$K=""){$Nl=array("information_schema","pg_catalog","pg_toast");if(connection()->flavor=='cockroach'){$Nl[]="crdb_internal";$Nl[]="pg_extension";}return
in_array($K!=""?$K:get_schema(),$Nl);}function
error(){$H=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$H,$_))$H=$_[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($_[3]).'})(.*)~','\1<b>\2</b>',$_[2]).$_[4];return
nl_br($H);}function
create_database($i,$Db){return
queries("CREATE DATABASE ".idf_escape($i).($Db?" ENCODING ".idf_escape($Db):""));}function
drop_databases(array$h){connection()->close();return
apply_queries("DROP DATABASE",$h,'Adminer\idf_escape');}function
rename_database($A,$Db){connection()->close();return!!queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($A));}function
auto_increment(){return"";}function
alter_table($Q,$A,array$m,array$je,$Jb,$pd,$Db,$Na,$Yi){$b=array();$Mj=array();if($Q!=""&&$Q!=$A)$Mj[]="ALTER TABLE ".table($Q)." RENAME TO ".table($A);$Pk="";foreach($m
as$l){$d=idf_escape($l[0]);$W=$l[1];if(!$W)$b[]="DROP $d";else{$xn=$W[5];unset($W[5]);if($l[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($Q!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($Q!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($d!=$W[0])$Mj[]="ALTER TABLE ".table($A)." RENAME $d TO $W[0]";$b[]="ALTER $d TYPE$W[1]";$Qk=$Q."_".idf_unescape($W[0])."_seq";$b[]="ALTER $d ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($Qk).")":"DROP DEFAULT"));if(isset($W[6]))$Pk="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Qk)." OWNED BY ".idf_escape($Q).".$W[0]";$b[]="ALTER $d ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($l[0]!=""||$xn!="")$Mj[]="COMMENT ON COLUMN ".table($A).".$W[0] IS ".($xn!=""?substr($xn,9):"''");}}if($Q==""){$b=array_merge($b,$je);$O="";if($Yi){$_b=(connection()->flavor=='cockroach');$O=" PARTITION BY $Yi[partition_by]($Yi[partition])";if($Yi["partition_by"]=='HASH'){$Zi=+$Yi["partitions"];for($r=0;$r<$Zi;$r++)$Mj[]="CREATE TABLE ".idf_escape($A."_$r")." PARTITION OF ".idf_escape($A)." FOR VALUES WITH (MODULUS $Zi, REMAINDER $r)";}else{$_j="MINVALUE";foreach($Yi["partition_names"]as$r=>$W){$X=$Yi["partition_values"][$r];$Ui=" VALUES ".($Yi["partition_by"]=='LIST'?"IN ($X)":"FROM ($_j) TO ($X)");if($_b)$O
.=($r?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W))."$Ui";else$Mj[]="CREATE TABLE ".idf_escape($A."_$W")." PARTITION OF ".idf_escape($A)." FOR$Ui";$_j=$X;}$O
.=($_b?"\n)":"");}}array_unshift($Mj,"CREATE TABLE ".table($A)." (\n".implode(",\n",$b)."\n)$O");}else{if($b)array_unshift($Mj,"ALTER TABLE ".table($Q)."\n".implode(",\n",$b));if($je)$Mj[]="ALTER TABLE ".table($A)."\n".implode(",\n",$je);}if($Pk)array_unshift($Mj,$Pk);if($Jb!==null)$Mj[]="COMMENT ON TABLE ".table($A)." IS ".q($Jb);foreach($Mj
as$F){if(!queries($F))return
false;}if($Na!=""){foreach(fields($A)as$Rd=>$l){if($l["auto_increment"])return!!queries("SELECT setval(pg_get_serial_sequence(".q(table($A)).", ".q($Rd)."), $Na)");}}return
true;}function
alter_indexes($Q,$b){$gc=array();$Zc=array();$Mj=array();foreach($b
as$W){if($W[0]!="INDEX")$gc[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$Zc[]=idf_escape($W[1]);else$Mj[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($gc)array_unshift($Mj,"ALTER TABLE ".table($Q).implode(",",$gc));if($Zc)array_unshift($Mj,"DROP INDEX ".implode(", ",$Zc));foreach($Mj
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables(array$S){return!!queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$S)));}function
drop_kinds(array$S){$H=array("MATERIALIZED VIEW"=>array(),"VIEW"=>array(),"TABLE"=>array());foreach($S
as$A=>$R)$H[strtoupper($R["Engine"])][]=table($A);return
array_filter($H);}function
drop_views(array$Dn){return
drop_tables($Dn);}function
drop_tables(array$S){$Cl=array();foreach($S
as$Q)$Cl[$Q]=table_status1($Q);foreach(drop_kinds($Cl)as$gg=>$Bh){if(!queries("DROP $gg ".implode(", ",$Bh)))return
false;}return
true;}function
move_tables(array$S,array$Dn,$hm){foreach(array_merge($S,$Dn)as$Q){$O=table_status1($Q);if(!queries("ALTER ".strtoupper($O["Engine"])." ".table($Q)." SET SCHEMA ".idf_escape($hm)))return
false;}return
true;}function
trigger($A,$Q){if($A=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q)." AND trigger_name = ".q($A);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$I)$e[]=$I["event_object_column"];$H=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$I){if($e&&$I["Event"]=="UPDATE")$I["Event"].=" OF";$I["Of"]=implode(", ",$e);if($H)$I["Event"].=" OR $H[Event]";$H=$I;}return$H;}function
triggers($Q){$H=array();$ye=array();foreach(get_rows('SELECT t.tgname, r.routine_schema AS ns, r.specific_name AS function, r.routine_name AS name
FROM pg_catalog.pg_trigger t
JOIN information_schema.routines r ON substring(r.specific_name, \'[0-9]+$\')::oid = t.tgfoid
WHERE NOT t.tgisinternal AND t.tgrelid = (
	SELECT c.oid FROM pg_catalog.pg_class c JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = current_schema() AND c.relname = '.q($Q).'
)')as$I)$ye[array_shift($I)]=$I;foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q))as$I){$Jm=trigger($I["trigger_name"],$Q);$H[$Jm["Trigger"]]=array($Jm["Timing"],$Jm["Event"]);if($ye[$Jm["Trigger"]])$H[$Jm["Trigger"]][]=$ye[$Jm["Trigger"]];}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($A,$T){$B=routine_options($T);$Lk=array_intersect_key(array("VOLATILITY"=>"CASE p.provolatile WHEN 'i' THEN 'IMMUTABLE' WHEN 's' THEN 'STABLE' ELSE 'VOLATILE' END","NULL_INPUT"=>"CASE WHEN p.proisstrict THEN 'RETURNS NULL ON NULL INPUT' ELSE 'CALLED ON NULL INPUT' END","SECURITY"=>"CASE WHEN p.prosecdef THEN 'SECURITY DEFINER' ELSE 'SECURITY INVOKER' END","PARALLEL"=>"CASE p.proparallel WHEN 's' THEN 'PARALLEL SAFE' WHEN 'r' THEN 'PARALLEL RESTRICTED' ELSE 'PARALLEL UNSAFE' END",),$B);foreach($Lk
as$w=>$L)$Lk[$w]="$L AS \"$w\"";$J=get_rows('SELECT r.routine_definition AS definition, LOWER(r.external_language) AS language, '.($Lk?implode(', ',$Lk).', ':'').'r.*
FROM information_schema.routines r
LEFT JOIN pg_catalog.pg_proc p ON p.oid::text = substring(r.specific_name, \'[0-9]+$\')
WHERE r.routine_schema = current_schema() AND r.specific_name = '.q($A));if(!$J)return
array();$H=$J[0];$H["options"]=array_intersect_key($H,$B);$H["returns"]=array("type"=>preg_replace('~^_(.*)~','\1[]',"$H[type_udt_name]"));$H["fields"]=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($A)."
ORDER BY ordinal_position");return$H;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(connection()->flavor=='cockroach'?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){$H=array();foreach(get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language")as$mg)$H[$mg]=(preg_match('~sql$~',$mg)?"pgsql":"txt");return$H;}function
routine_options($pk){$_b=(connection()->flavor=='cockroach');$Gk=($_b?array():array("SECURITY"=>array("SECURITY INVOKER","SECURITY DEFINER")));if($pk=="PROCEDURE")return$Gk;return
array("VOLATILITY"=>array("VOLATILE","STABLE","IMMUTABLE"),"NULL_INPUT"=>array("CALLED ON NULL INPUT","RETURNS NULL ON NULL INPUT"),)+$Gk+($_b?array():array("PARALLEL"=>array("PARALLEL UNSAFE","PARALLEL RESTRICTED","PARALLEL SAFE"),));}function
routine_id($A,array$I){$H=array();foreach($I["fields"]as$l){$x=$l["length"];$H[]=$l["type"].($x?"($x)":"");}return
idf_escape($A)."(".implode(", ",$H).")";}function
last_id($G){$I=(is_object($G)?$G->fetch_row():array());return($I?$I[0]:0);}function
explain(Db$f,$F){return$f->query("EXPLAIN $F");}function
found_rows(array$R,array$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$bk))return$bk[1];}function
types($Kd=false){$_b=connection()->flavor=='cockroach';$hg=($_b?"'e'":"'b','c','d','e'".(min_version(9.2)?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".driver()->nsOid."
AND t.typtype IN ($hg)".($_b?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($Kd?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($s){$td=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $s ORDER BY enumsortorder");return($td?"'".implode("', '",array_map('addslashes',$td))."'":"");}function
collation_name($ci){return(min_version(9.1)?"(SELECT collname FROM pg_collation WHERE oid = $ci AND collname != 'default')":"NULL");}function
type_definition($s){$T=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $s"));$H=array("kind"=>($T?$T["typtype"]:""),"definition"=>"");if(!$T||!$T["defined"])return$H;switch($H["kind"]){case'e':$Y=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $s ORDER BY enumsortorder");$H["definition"]="AS ENUM (".implode(", ",array_map('Adminer\q',$Y)).")";break;case'c':$e=array();foreach(get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $T[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum")as$I)$e[]=idf_escape($I["attname"])." $I[full_type]".($I["collation"]?" COLLATE ".idf_escape($I["collation"]):"");$H["definition"]="AS (\n\t".implode(",\n\t",$e)."\n)";break;case'd':$Wc=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type WHERE oid = $s"));$H["definition"]="AS $Wc[base]".($Wc["collation"]?" COLLATE ".idf_escape($Wc["collation"]):"").($Wc["typdefault"]!=""?" DEFAULT $Wc[typdefault]":"").($Wc["notnull"]?" NOT NULL":"");foreach(get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $s AND contype != 'n' ORDER BY conname")as$I)$H["definition"].=" CONSTRAINT ".idf_escape($I["conname"])." $I[definition]";break;case'r':$Qj=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
".collation_name("rngcollation")." AS collation,
NULLIF(rngcanonical, 0)::regproc::text AS canonical,
NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(min_version(14)?",
(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range WHERE rngtypid = $s"));$B=array();foreach(array("subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1)as$w=>$xd){if($Qj[$w]!="")$B[]=strtoupper($w)." = ".($xd?idf_escape($Qj[$w]):$Qj[$w]);}$H["definition"]="AS RANGE (".implode(", ",$B).")";}return$H;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)get_val("SELECT current_schema()");}function
set_schema($K,$g=null){$_GET["ns"]=$K;$H=get_val("SELECT set_config('search_path', ".q(idf_escape($K)).", false) FROM pg_namespace WHERE nspname = ".q($K),0,$g);driver()->setUserTypes(types(true));return!!$H;}function
drop_sql(array$S){$H="";foreach(drop_kinds($S)as$gg=>$Bh)$H
.="DROP $gg IF EXISTS ".implode(", ",$Bh).";\n";return($H?"$H\n":"");}function
foreign_keys_sql($Q){$H="";$ee=foreign_keys($Q);ksort($ee);foreach($ee
as$de=>$ce){$H
.="ALTER TABLE ONLY ".table($Q)." ADD CONSTRAINT ".idf_escape($de)." ".preg_replace_callback('~( REFERENCES )([^(.]+)\(~',function(array$_){return$_[1].table(idf_unescape($_[2]))."(";},$ce["definition"]).";\n";}return($H?"$H\n":$H);}function
indexes_sql($Q,$Cj=""){$H="";$F="SELECT indexdef, quote_ident(schemaname) || '.' || quote_ident(tablename) AS qualified, quote_ident(current_database()) AS db
FROM pg_catalog.pg_indexes
WHERE schemaname = current_schema() AND tablename = ".q($Q).($Cj!=""?" AND indexname != ".q($Cj):"");foreach(get_rows($F,null,"-- ")as$I)$H
.="\n\n".str_replace(array(" $I[db].$I[qualified] USING "," $I[qualified] USING ")," ".table($Q)." USING ",$I["indexdef"]).";";return$H;}function
create_sql($Q,$Na,$Gl){$kk=array();$Sk=array();$Tk=array();$Rk=array();$O=table_status1($Q);if(is_view($O)){$Cn=view($Q);$gc="CREATE ".strtoupper($O["Engine"])." ".table($Q)." AS ".rtrim($Cn["select"],";").";";return
rtrim($gc.indexes_sql($Q),';');}$m=fields($Q);if(count($O)<2||empty($m))return"";$H="CREATE TABLE ".table($O['Name'])." (\n    ";$Ul=q(table($O['Name']));foreach($m
as$l){$Uk="";if($l['default']=="nextval('$O[Name]_$l[field]_seq')"){$Uk=table("$O[Name]_$l[field]_seq");$l['default']=null;$l['full_type']=preg_replace('~int(eger)?~','serial',$l['full_type']);}$Si=idf_escape($l['field']).' '.full_type_sql($l).preg_replace_callback('~(nextval\(\')([^.\']+)\'~',function(array$_){return$_[1].str_replace("'","''",table(idf_unescape($_[2])))."'";},default_value($l)).($l['null']?"":" NOT NULL");$kk[]=$Si;if(preg_match('~nextval\(\'([^\']+)\'\)~',$l['default'],$Kg)){$Qk=$Kg[1];$sl=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Qk)):"SELECT * FROM $Qk"),null,"-- "));$Pk=table(idf_unescape($Qk));$Sk[]=($Gl=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Pk;\n":"")."CREATE SEQUENCE $Pk INCREMENT $sl[increment_by] MINVALUE $sl[min_value] MAXVALUE $sl[max_value]"." CACHE $sl[cache_value];";if(get_val("SELECT pg_get_serial_sequence($Ul, ".q($l['field']).")"))$Tk[]="\n\nALTER SEQUENCE $Pk OWNED BY ".table($O['Name']).".".idf_escape($l['field']).";";if($Na)$Rk[]=$Pk;}elseif($Na&&$l['auto_increment']){$Pk=($Uk?"":get_val("SELECT pg_get_serial_sequence($Ul, ".q($l['field']).")::regclass"));$Rk[]=($Pk?table(idf_unescape($Pk)):$Uk);}}if(!empty($Sk))$H=implode("\n\n",$Sk)."\n\n$H";$Cj="";foreach(indexes($Q)as$wf=>$u){if($u['type']=='PRIMARY'){$Cj=$wf;$kk[]="CONSTRAINT ".idf_escape($wf)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$u['columns'])).")";}}foreach(driver()->checkConstraints($Q)as$Rb=>$Tb)$kk[]="CONSTRAINT ".idf_escape($Rb)." CHECK ($Tb)";$H
.=implode(",\n    ",$kk)."\n)";$Ui=driver()->partitionsInfo($O['Name']);if($Ui)$H
.="\nPARTITION BY $Ui[partition_by]($Ui[partition])";$H
.=(min_version(12)?"":"\nWITH (oids = ".($O['Oid']?'true':'false').")").";";$H
.=implode($Tk);if($O['Comment'])$H
.="\n\nCOMMENT ON TABLE ".table($O['Name'])." IS ".q($O['Comment']).";";foreach($m
as$Rd=>$l){if($l['comment'])$H
.="\n\nCOMMENT ON COLUMN ".table($O['Name']).".".idf_escape($Rd)." IS ".q($l['comment']).";";}$H
.=indexes_sql($Q,$Cj);foreach(array_filter($Rk)as$Pk){$sl=first(get_rows("SELECT last_value, is_called::int FROM $Pk",null,"-- "));if($sl['is_called'])$H
.="\n\nDO \$\$ BEGIN PERFORM setval(".q($Pk).", $sl[last_value]); END \$\$;";}return
rtrim($H,';');}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
truncate_all_sql(array$S){return($S?"TRUNCATE ".implode(", ",array_map('Adminer\table',$S)).";\n\n":"");}function
trigger_sql($Q){$O=table_status1($Q);$H="";foreach(triggers($Q)as$Im=>$Hm){$Jm=trigger($Im,$O['Name']);$H
.="\nCREATE TRIGGER ".idf_escape($Jm['Trigger'])." $Jm[Timing] $Jm[Event] ON ".table($O['Name'])." $Jm[Type] $Jm[Statement];;\n";}return$H;}function
use_sql($tc,$Gl=""){$A=idf_escape($tc);$H="";if(preg_match('~CREATE~',$Gl)){if($Gl=="DROP+CREATE")$H="DROP DATABASE IF EXISTS $A;\n";$H
.="CREATE DATABASE $A;\n";}return"$H\\connect $A";}function
use_schema_sql($K,$Gl){$A=idf_escape($K);$H="";if(preg_match('~CREATE~',$Gl)){if($Gl=="DROP+CREATE")$H="DROP SCHEMA IF EXISTS $A CASCADE;\n";$H
.="CREATE SCHEMA IF NOT EXISTS $A;\n";}return$H."SET search_path TO $A";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field(array$l){if(preg_match('~^(geometry|geography)$~',$l["type"])&&strpos($l["full_type"],"[")===false)return"ST_AsEWKT(".idf_escape($l["field"]).")";}function
unconvert_field(array$l,$H){return($l["composite"]?"$H::".full_type_sql($l):$H);}function
support($Pd){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table'.'|transaction_ddl|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|deferrable').(connection()->flavor=='cockroach'||!min_version(9.1)?'':'|extension').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Pd);}function
kill_process($s){return
queries("SELECT pg_terminate_backend(".number($s).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach(array$M,$U,$D){$this->link=new
\SQLite3($M["path"]);if(method_exists($this->link,'setAuthorizer'))$this->link->setAuthorizer(array($this,'authorize'));$Bn=\SQLite3::version();$this->server_info=$Bn["versionString"];return'';}function
query($F,$Vm=false){$G=@$this->link->query($F);$this->error="";if(!$G){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($G->numColumns())return
new
Result($G);$this->affected_rows=$this->link->changes();return
true;}function
quote($P){return(is_utf8($P)?"'".$this->link->escapeString($P)."'":"x'".bin2hex($P)."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($G){$this->result=$G;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$Um=array(1=>"integer","real","text","blob","null");$d=$this->offset++;return(object)array("name"=>$this->result->columnName($d),"native_type"=>$Um[$this->result->columnType($d)],);}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach(array$M,$U,$D){$H=$this->dsn(DRIVER.":".$M["path"],"","",array(),(class_exists('Pdo\Sqlite')?'Pdo\Sqlite':'PDO'));if(!$H&&method_exists($this->pdo,'setAuthorizer'))$this->pdo->setAuthorizer(array($this,'authorize'));return$H;}function
quote($P){return(is_utf8($P)?parent::quote($P):"x'".bin2hex($P)."'");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{private$attaching=false;function
attach(array$M,$U,$D){parent::attach($M,$U,$D);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($n){$F="ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$n)?$n:dirname($_SERVER["SCRIPT_FILENAME"])."/$n")." AS a";$this->attaching=true;$Ka=is_readable($n)&&$this->query($F);$this->attaching=false;if($Ka)return!self::attach(server_parts(array("path"=>$n)),'','');return
false;}function
authorize($ka,$Ea){return($ka!=24||$Ea===''||$this->attaching?0:1);}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";static$passwords=false;static$serverFile=true;protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$fulltextOperator="MATCH";var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");function
operators($Pl){$H=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");if(preg_match('~^fts\d+$~i',(string)idx($Pl,"Engine")))$H[]="MATCH";$H[]="SQL";return$H;}static
function
connect($M,$U,$D){return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");if(min_version(3.37,0,$f))$this->types[0]["any"]=0;}function
structuredTypes(){return
array_keys($this->types[0]);}function
quoteBinary($yk){return"x".q(bin2hex($yk));}function
typeName(\stdClass$l){$H=strtolower(idx((array)$l,'sqlite:decl_type',parent::typeName($l)));return
idx(array("string"=>"text","double"=>"real"),$H,$H);}function
engines(){$H=array("table");if(min_version("3.8.2")){if(min_version(3.37)){$H[]="STRICT";$H[]="STRICT, WITHOUT ROWID";}$H[]="WITHOUT ROWID";}return$H;}private
function
isVirtual(array$R){$pd=$R["Engine"];return$pd!=""&&!in_array($pd,array_merge(array("view"),$this->engines()));}function
supportsIndex(array$R){return!is_view($R)&&!$this->isVirtual($R);}function
supportsAlterIndex(array$R){return$this->supportsIndex($R);}function
supportsAlterTable(array$Pl){return!$this->isVirtual($Pl);}function
shadowTables($Q){$H=array();if(min_version(3.37)){foreach(get_vals("SELECT name FROM pragma_table_list WHERE schema = 'main' AND type = 'shadow' ORDER BY name")as$A){if(preg_match('(^'.preg_quote($Q).'_[^_]*$)',$A))$H[]=array("table"=>$A,"ns"=>"");}}return$H;}function
fulltextSql($A,array$u,$F,$cb){return
idf_escape($A)." MATCH ".q($F);}function
insertUpdate($Q,array$J,array$Cj){$Y=array();foreach($J
as$N)$Y[]="(".implode(", ",$N).")";return
queries("REPLACE INTO ".table($Q)." (".implode(", ",array_keys(reset($J))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($A,$Vf=false){if(preg_match('~^sqlite_(seq|stat.)~',$A,$_))return"fileformat2.html#$_[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$A))return"schematab.html";}function
checkConstraints($Q){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q),0,$this->conn),$Kg);return
array_combine($Kg[2],$Kg[2]);}function
allFields(){$H=array();if(min_version(3.16)){$J=get_rows('SELECT m.name AS tab, p.name AS field, p.type, p."notnull", p.pk AS '.idf_escape("primary")."
FROM sqlite_master m, pragma_table_".(min_version(3.31)?"x":"")."info(m.name) p
WHERE m.type IN ('table', 'view')".(min_version(3.31)?"
AND p.hidden != 1":"").(min_version(3.37)?"
AND m.name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (m.name LIKE 'sqlite_%'), m.name, p.cid",$this->conn);foreach($J
as$I){$I["type"]=type_affinity($I["type"]);$I["null"]=!$I["notnull"];$H[$I["tab"]][]=$I;}}else{foreach(tables_list()as$Q=>$T){foreach(fields($Q)as$l)$H[$Q][]=$l;}}return$H;}}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return
idf_escape($t);}function
get_databases($he){return
array();}function
limit($F,$Z,$y,$bi=0,$Ok=" "){return" $F$Z".($y?$Ok."LIMIT $y".($bi?" OFFSET $bi":""):"");}function
limit1($Q,$F,$Z,$Ok="\n"){return(preg_match('~^INTO~',$F)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$Ok):" $F WHERE rowid = (SELECT rowid FROM ".table($Q).$Z.$Ok."LIMIT 1)");}function
db_collation($i,array$Eb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
virtual_module($tl){return(preg_match('~^CREATE\s+VIRTUAL\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:"[^"]*+"|`[^`]*+`|\[[^\]]*+\]|[^\s(]+)\s+USING\s+([a-z0-9_]+)~i',$tl,$_)?$_[1]:"");}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view')".(min_version(3.37)?" AND name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$h){return
array();}function
db_status(){$Mi=get_val("PRAGMA page_size");$re=get_val("PRAGMA freelist_count")*$Mi;return
array("Data_length"=>get_val("PRAGMA page_count")*$Mi-$re,"Index_length"=>0,"Data_free"=>$re,);}function
table_status($A="",$Od=false){$H=array();$J=array();if(!$Od&&$A==""){connection()->query("PRAGMA optimize = 0x10002");$J=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1 GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment".(min_version(3.37)?", name IN (SELECT name FROM pragma_table_list WHERE type = 'shadow') AS dependent":"")." FROM sqlite_master WHERE type IN ('table', 'view') ".($A!=""?"AND name = ".q($A):"ORDER BY (name LIKE 'sqlite_%'), name"))as$I){if($I["Engine"]=="table"){$tl=preg_replace('~(?:\s|--[^\n]*|/\*.*?\*/)+$~s','',$I["sql"]);$Il=preg_replace('~.*\)~s','',$tl);$I["Engine"]=virtual_module($I["sql"])?:(implode(", ",array_filter(array((preg_match('~\bSTRICT\b~i',$Il)?"STRICT":0),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Il)?"WITHOUT ROWID":0),)))?:"table");}unset($I["sql"]);$I["Rows"]=idx($J,$I["Name"],0);$H[$I["Name"]]=$I;}if(!$Od){foreach(get_rows("SELECT * FROM sqlite_sequence".($A!=""?" WHERE name = ".q($A):""),null,"")as$I)$H[$I["name"]]["Auto_increment"]=$I["seq"];}return$H;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support(array$R){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
type_affinity($T){$T=strtolower($T);return(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric")))));}function
fields($Q){$H=array();$tl=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));$Hj=array("select"=>1,"where"=>1,"order"=>1);if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$Q))$Hj+=array("insert"=>1,"update"=>1);$te=preg_match('~^fts\d+$~i',virtual_module($tl));foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($Q).")")as$I){if($I["hidden"]==1)continue;$A=$I["name"];$T=strtolower($I["type"]);$j=$I["dflt_value"];$H[$A]=array("field"=>$A,"type"=>($te?"text":type_affinity($T)),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~",$j,$_)?str_replace("''","'",$_[1]):($j=="NULL"?null:$j)),"null"=>!$I["notnull"],"privileges"=>$Hj,"primary"=>$I["pk"],);if($I["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$tl))$H[$A]["auto_increment"]=true;}$t='[(,]\s*(("[^"]*+")+|[a-z0-9_]+)';$gk='(?:[^,()\']|\'[^\']*+\'|\([^)]*+\))*?';preg_match_all('~'.$t.'\s+text\b'.$gk.'COLLATE\s+(\'[^\']+\'|[a-z0-9_]+)~i',$tl,$Kg,PREG_SET_ORDER);foreach($Kg
as$_){$A=str_replace('""','"',preg_replace('~^"|"$~','',$_[1]));if($H[$A])$H[$A]["collation"]=trim($_[3],"'");}preg_match_all('~'.$t.'\s'.$gk.'GENERATED\s+ALWAYS\s+AS\s*\((.+?)\)\s+(STORED|VIRTUAL)~i',$tl,$Kg,PREG_SET_ORDER);foreach($Kg
as$_){$A=str_replace('""','"',preg_replace('~^"|"$~','',$_[1]));if($H[$A]){$H[$A]["default"]=$_[3];$H[$A]["generated"]=strtoupper($_[4]);}}return$H;}function
indexes($Q,$g=null){$g=connection($g);$H=array();$tl=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q),0,$g);if(preg_match('~^fts\d+$~i',virtual_module($tl)))return
array($Q=>array("type"=>"FULLTEXT","columns"=>array_keys(fields($Q)),"lengths"=>array(),"descs"=>array()));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$tl,$_)){$H[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$_[1],$Kg,PREG_SET_ORDER);foreach($Kg
as$_){$H[""]["columns"][]=idf_unescape($_[2]).$_[4];$H[""]["descs"][]=(preg_match('~DESC~i',$_[5])?'1':null);}}if(!$H){foreach(fields($Q)as$A=>$l){if($l["primary"])$H[""]=array("type"=>"PRIMARY","columns"=>array($A),"lengths"=>array(),"descs"=>array(null));}}$yl=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($Q),$g);foreach(get_rows("PRAGMA index_list(".table($Q).")",$g)as$I){$A=$I["name"];$u=array("type"=>($I["unique"]?"UNIQUE":"INDEX"));$u["lengths"]=array();$u["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($A).")",$g)as$wk){$u["columns"][]=$wk["name"];$u["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($A).' ON '.idf_escape($Q),'~').' \((.*)\)$~i',$yl[$A],$bk)){preg_match_all('/("[^"]*+")+( DESC)?/',$bk[2],$Kg);foreach($Kg[2]as$w=>$W){if($W)$u["descs"][$w]='1';}}if(!$H[""]||$u["type"]!="UNIQUE"||$u["columns"]!=$H[""]["columns"]||$u["descs"]!=$H[""]["descs"]||!preg_match("~^sqlite_~",$A))$H[$A]=$u;}return$H;}function
foreign_keys($Q){$H=array();foreach(get_rows("PRAGMA foreign_key_list(".table($Q).")")as$I){$o=&$H[$I["id"]];if(!$o)$o=$I;$o["source"][]=$I["from"];$o["target"][]=$I["to"];}return$H;}function
view($A){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($A))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($i,$K=""){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($A){$Kd="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Kd)\$~",$A)){connection()->error=lang(36,str_replace("|",", ",$Kd));return
false;}return
true;}function
create_database($i,$Db){if(file_exists($i)){connection()->error=lang(37);return
false;}if(!check_sqlite_name($i))return
false;try{$z=new
Db();$z->attach(server_parts(array("path"=>$i)),'','');}catch(\Exception$Ad){connection()->error=$Ad->getMessage();return
false;}$z->query('PRAGMA encoding = "UTF-8"');$z->query('CREATE TABLE adminer (i)');$z->query('DROP TABLE adminer');return
true;}function
drop_databases(array$h){connection()->attach(server_parts(array("path"=>":memory:")),'','');foreach($h
as$i){if(!check_sqlite_name($i))return
false;if(!@unlink($i)){connection()->error=lang(37);return
false;}}return
true;}function
rename_database($A,$Db){if(!check_sqlite_name($A))return
false;connection()->attach(server_parts(array("path"=>":memory:")),'','');connection()->error=lang(37);return@rename(DB,$A);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($Q,$A,array$m,array$je,$Jb,$pd,$Db,$Na,$Yi){$nn=($Q==""||$je||$pd);foreach($m
as$l){if($l[0]!=""||!$l[1]||$l[2]){$nn=true;break;}}$b=array();$Gi=array();foreach($m
as$l){if($l[1]){$b[]=($nn?$l[1]:"ADD ".implode($l[1]));if($l[0]!="")$Gi[$l[0]]=$l[1][0];}}if(!$nn){foreach($b
as$W){if(!queries("ALTER TABLE ".table($Q)." $W"))return
false;}if($Q!=$A&&!queries("ALTER TABLE ".table($Q)." RENAME TO ".table($A)))return
false;}elseif(!recreate_table($Q,$A,$b,$Gi,$je,$Na,array(),"","",$pd))return
false;if($Na){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Na WHERE name = ".q($A));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($A).", $Na)");queries("COMMIT");}return
true;}function
recreate_table($Q,$A,array$m,array$Gi,array$je,$Na="",$v=array(),$ad="",$na="",$pd=""){if($Q!=""){if(!$m){foreach(fields($Q)as$w=>$l){if($v)$l["auto_increment"]=0;$m[]=process_field($l,$l);$Gi[$w]=idf_escape($w);}}$Dj=false;foreach($m
as$l){if($l[6])$Dj=true;}$cd=array();foreach($v
as$w=>$W){if($W[2]=="DROP"){$cd[$W[1]]=true;unset($v[$w]);}}foreach(indexes($Q)as$cg=>$u){$e=array();foreach($u["columns"]as$w=>$d){if(!$Gi[$d])continue
2;$e[]=$Gi[$d].($u["descs"][$w]?" DESC":"");}if(!$cd[$cg]){if($u["type"]!="PRIMARY"||!$Dj)$v[]=array($u["type"],$cg,$e);}}foreach($v
as$w=>$W){if($W[0]=="PRIMARY"){unset($v[$w]);$je[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($Q)as$cg=>$o){foreach($o["source"]as$w=>$d){if(!$Gi[$d])continue
2;$o["source"][$w]=idf_unescape($Gi[$d]);}if(!isset($je[" $cg"]))$je[]=" ".format_foreign_key($o);}queries("BEGIN");}$mb=array();foreach($m
as$l){if(preg_match('~GENERATED~',$l[3]))unset($Gi[array_search($l[0],$Gi)]);$mb[]="  ".implode($l);}$mb=array_merge($mb,array_filter($je));foreach(driver()->checkConstraints($Q)as$qb){if($qb!=$ad)$mb[]="  CHECK ($qb)";}if($na)$mb[]="  CHECK ($na)";$lm=($Q!=""&&$Q==$A?"adminer_$A":$A);if(!$pd&&$Q!="")$pd=idx(table_status1($Q),"Engine");if(!queries("CREATE TABLE ".table($lm)." (\n".implode(",\n",$mb)."\n)".($pd!="table"&&in_array($pd,driver()->engines())?" $pd":"")))return
false;if($Q!=""){if($Gi&&!queries("INSERT INTO ".table($lm)." (".implode(", ",$Gi).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($Gi)))." FROM ".table($Q)))return
false;$Nm=array();foreach(triggers($Q)as$Lm=>$sm){$Jm=trigger($Lm,$Q);$Nm[]="CREATE TRIGGER ".idf_escape($Lm)." ".implode(" ",$sm)." ON ".table($A)."\n$Jm[Statement]";}$Na=$Na?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($Q));if(!queries("DROP TABLE ".table($Q))||($Q==$A&&!queries("ALTER TABLE ".table($lm)." RENAME TO ".table($A)))||!alter_indexes($A,$v))return
false;if($Na)queries("UPDATE sqlite_sequence SET seq = $Na WHERE name = ".q($A));foreach($Nm
as$Jm){if(!queries($Jm))return
false;}queries("COMMIT");}return
true;}function
index_sql($Q,$T,$A,$e){return"CREATE $T ".($T!="INDEX"?"INDEX ":"").idf_escape($A!=""?$A:uniqid($Q."_"))." ON ".table($Q)." $e";}function
alter_indexes($Q,$b){foreach($b
as$Cj){if($Cj[0]=="PRIMARY")return
recreate_table($Q,$Q,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$W){if(!queries($W[2]=="DROP"?"DROP INDEX ".idf_escape($W[1]):index_sql($Q,$W[0],$W[1],"(".implode(", ",$W[2]).")")))return
false;}return
true;}function
truncate_tables(array$S){return
apply_queries("DELETE FROM",$S);}function
drop_views(array$Dn){return
apply_queries("DROP VIEW",$Dn);}function
drop_tables(array$S){return
apply_queries("DROP TABLE",$S);}function
move_tables(array$S,array$Dn,$hm){return
false;}function
trigger($A,$Q){if($A=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$t='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$Mm=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$t\\s*(".implode("|",$Mm["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($t))?\\s+ON\\s*$t\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($A)),$_);if(!$_)return
array();$Wh=$_[3];return
array("Timing"=>strtoupper($_[1]),"Event"=>strtoupper($_[2]).($Wh?" OF":""),"Of"=>idf_unescape($Wh),"Trigger"=>$A,"Statement"=>$_[4],);}function
triggers($Q){$H=array();$Mm=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q))as$I){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$Mm["Timing"]).')\s*(.*?)\s+ON\b~i',$I["sql"],$_);$H[$I["name"]]=array($_[1],$_[2]);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
last_id($G){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain(Db$f,$F){return$f->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$R,array$Z){}function
types($Kd=false){return
array();}function
create_sql($Q,$Na,$Gl){$H=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($Q));foreach(indexes($Q)as$A=>$u){if($A==''||$u['type']=='FULLTEXT')continue;$H
.=";\n\n".index_sql($Q,$u['type'],$A,"(".implode(", ",array_map('Adminer\idf_escape',$u['columns'])).")");}return$H;}function
truncate_sql($Q){return"DELETE FROM ".table($Q);}function
use_sql($tc,$Gl=""){return"";}function
trigger_sql($Q){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q)));}function
show_variables(){$H=array();foreach(get_rows("PRAGMA pragma_list")as$I){$A=$I["name"];if($A!="pragma_list"&&$A!="compile_options"){$H[$A]=array($A,'');foreach(get_rows("PRAGMA $A")as$I)$H[$A][1].=implode(", ",$I)."\n";}}return$H;}function
show_status(){$H=array();foreach(get_vals("PRAGMA compile_options")as$ti)$H[]=explode("=",$ti,2)+array('','');return$H;}function
convert_field(array$l){}function
unconvert_field(array$l,$H){return$H;}function
support($Pd){return
preg_match('~^(check|columns|database|drop_col|dump|fast_status|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$Pd);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result,$warnings,$transaction=false;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$k){$this->errno=$k["code"];$this->error
.="$k[message]\n";}$this->error=rtrim($this->error);}function
attach(array$M,$U,$D){sqlsrv_configure("WarningsReturnAsErrors",0);$Sb=array("UID"=>$U,"PWD"=>$D,"CharacterSet"=>"UTF-8","ReturnDatesAsStrings"=>true);if(isset($_GET["sql"])&&!self::$instance)$Sb["MultipleActiveResultSets"]=false;$zl=adminer()->connectSsl();if(isset($zl["Encrypt"]))$Sb["Encrypt"]=$zl["Encrypt"];if(isset($zl["TrustServerCertificate"]))$Sb["TrustServerCertificate"]=$zl["TrustServerCertificate"];$i=adminer()->database();if($i!="")$Sb["Database"]=$i;$qj=$M["port"];$this->link=@sqlsrv_connect($M["host"].($qj?",$qj":""),$Sb);if($this->link){$Af=sqlsrv_server_info($this->link);$this->server_info=$Af['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($P){return
unicode_prefix($P)."'".str_replace("'","''",$P)."'";}function
select_db($tc){return$this->query(use_sql($tc));}function
query($F,$Vm=false){$G=sqlsrv_query($this->link,$F);$this->error="";if(!$G){$this->get_error();return
false;}return$this->store_result($G);}function
multi_query($F){$this->result=sqlsrv_query($this->link,$F);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($G=null){if(!$G)$G=$this->result;if(!$G)return
false;$this->warnings=sqlsrv_errors(SQLSRV_ERR_WARNINGS);if(sqlsrv_field_metadata($G))return
new
Result($G);$this->affected_rows=sqlsrv_rows_affected($G);return
true;}function
next_result(){if(!$this->result)return
false;$H=sqlsrv_next_result($this->result);if($H===false){$this->get_error();$this->result=null;return
true;}return!!$H;}function
warnings(){$H=array();foreach((array)$this->warnings
as$Gn)$H[]=$Gn["message"];return$H;}function
inTransaction(){return$this->transaction||(isset($_GET["sql"])&&get_val("SELECT @@TRANCOUNT",0,$this));}function
begin(){$this->transaction=sqlsrv_begin_transaction($this->link);if(!$this->transaction)$this->get_error();return$this->transaction;}function
commit(){if($this->transaction&&!sqlsrv_commit($this->link)){$this->get_error();return
false;}$this->transaction=false;return
true;}function
rollback(){if(!$this->transaction&&isset($_GET["sql"]))return!!$this->query("IF @@TRANCOUNT > 0 ROLLBACK");if($this->transaction&&!sqlsrv_rollback($this->link)){$this->get_error();return
false;}$this->transaction=false;return
true;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($G){$this->result=$G;}function
fetch_assoc(){return
sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC);}function
fetch_row(){return
sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC);}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$Um=array(-155=>"datetimeoffset","time",-152=>"xml","varbinary","sql_variant",-11=>"uniqueidentifier","ntext","nvarchar","nchar","bit","tinyint","bigint","image","varbinary","binary","text",1=>"char","numeric","decimal","int","smallint","float","real","float",12=>"varchar",91=>"date","time","datetime",);$l=$this->fields[$this->offset++];$H=new
\stdClass;$H->name=$l["Name"];$H->native_type=idx($Um,$l["Type"],"");return$H;}function
seek($bi){for($r=0;$r<$bi;$r++)sqlsrv_fetch($this->result);}}function
last_id($G){return(string)get_val("SELECT SCOPE_IDENTITY()");}function
explain(Db$f,$F){$f->query("SET SHOWPLAN_ALL ON");$H=$f->query($F);$f->query("SET SHOWPLAN_ALL OFF");return$H;}}else{abstract
class
MssqlDb
extends
PdoDb{function
quote($P){return
unicode_prefix($P).parent::quote($P);}function
select_db($tc){return$this->query(use_sql($tc));}function
lastInsertId(){return$this->pdo->lastInsertId();}function
inTransaction(){return
parent::inTransaction()||(isset($_GET["sql"])&&get_val("SELECT @@TRANCOUNT"));}function
rollback(){return(parent::inTransaction()||!isset($_GET["sql"])?parent::rollback():!!$this->query("IF @@TRANCOUNT > 0 ROLLBACK"));}function
warnings(){$G=$this->multi;if(!is_object($G))return
array();$k=$G->errorInfo();return
array((string)$k[2]);}}function
last_id($G){return
connection()->lastInsertId();}function
explain(Db$f,$F){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach(array$M,$U,$D){$qj=$M["port"];$ed="sqlsrv:Server=$M[host]".($qj?",$qj":"").(isset($_GET["sql"])&&!self::$instance?";MultipleActiveResultSets=0":"");$zl=adminer()->connectSsl();foreach(array("Encrypt","TrustServerCertificate")as$w){if(isset($zl[$w]))$ed
.=";$w=".($zl[$w]?1:0);}return$this->dsn($ed,$U,$D,array(\PDO::SQLSRV_ATTR_DIRECT_QUERY=>true));}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach(array$M,$U,$D){$qj=$M["port"];$ll=$M["socket"];$B=array(1002=>true);$H=$this->dsn("dblib:charset=utf8;host=$M[host]".($qj!=""?";port=$qj":($ll!=""?";unix_socket=$ll":"")),$U,$D,$B);if(!$H){$this->query("SET ANSI_NULLS, QUOTED_IDENTIFIER, CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON");$this->server_info=get_val("SELECT CAST(SERVERPROPERTY('ProductVersion') AS varchar(20))",0,$this);}return$H;}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";static$serverSocket=true;var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$inout="|OUTPUT";private$unknownTypes=array();function
operators($Pl){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");}static
function
connect($M,$U,$D){if($M=="")$M="localhost:1433";return
parent::connect($M,$U,$D);}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(29)=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"numeric"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20,"vector"=>0,),lang(30)=>array("date"=>10,"smalldatetime"=>19,"datetime"=>23,"datetime2"=>19,"time"=>8,"datetimeoffset"=>26),lang(31)=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823,"uniqueidentifier"=>36,"xml"=>2147483647,"json"=>2147483647,"sql_variant"=>8000,"hierarchyid"=>892,),lang(32)=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),lang(34)=>array("geometry"=>0,"geography"=>0),);$Um=array_flip(get_vals("SELECT name FROM sys.types WHERE is_user_defined = 0 ORDER BY name"));if($Um){foreach($this->types
as$q=>$Fe){foreach($Fe
as$T=>$x){if(isset($Um[$T]))unset($Um[$T]);else
unset($this->types[$q][$T]);}if(!$this->types[$q])unset($this->types[$q]);}$this->unknownTypes=array_keys($Um);}}function
types(){return
parent::types()+array_fill_keys($this->unknownTypes,0);}function
structuredTypes(){return
array_merge(parent::structuredTypes(),$this->unknownTypes);}function
typeName(\stdClass$l){return
idx((array)$l,'sqlsrv:decl_type',parent::typeName($l));}function
insertUpdate($Q,array$J,array$Cj){$m=fields($Q);$gn=array();$Z=array();$N=reset($J);$e="c".implode(", c",range(1,count($N)));$gb=0;$Gf=array();foreach($N
as$w=>$W){$gb++;$A=idf_unescape($w);if(!$m[$A]["auto_increment"])$Gf[$w]="c$gb";if(isset($Cj[$A]))$Z[]="$w = c$gb";else$gn[]="$w = c$gb";}$Y=array();foreach($J
as$N)$Y[]="(".implode(", ",$N).")";if($Z){$jf=queries("SET IDENTITY_INSERT ".table($Q)." ON");$H=queries("MERGE ".table($Q)." USING (VALUES\n\t".implode(",\n\t",$Y)."\n) AS source ($e) ON ".implode(" AND ",$Z).($gn?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$gn):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($jf?$N:$Gf)).") VALUES (".($jf?$e:implode(", ",$Gf)).");");if($jf)queries("SET IDENTITY_INSERT ".table($Q)." OFF");}else$H=queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($N)).") VALUES\n".implode(",\n",$Y));return$H;}function
begin(){remember_query("BEGIN TRANSACTION");return$this->conn->begin();}function
convertSearch($t,array$W,array$l){return(preg_match('~^(bit|n?text|xml|json|vector|uniqueidentifier|sql_variant|hierarchyid|geography|geometry)$~',$l["type"])?"CAST($t AS nvarchar(max))":$t);}function
quoteBinary($yk){return"0x".bin2hex($yk);}function
warnings(){$H=array();foreach($this->conn->warnings()as$ch){$ch=trim(preg_replace('~^(\[[^]]+])+~','',$ch));if($ch!="")$H[]=$ch;}return
nl_br(h(implode("\n",$H)));}function
tableHelp($A,$Vf=false){$Ag=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$z=$Ag[get_schema()];if($z)return"relational-databases/system-$z".preg_replace('~_~','-',strtolower($A))."-transact-sql";}function
isSystem($i,$K=""){return($K!=""?information_schema($i,$K)||preg_match('~^(guest|db_(owner|accessadmin|securityadmin|ddladmin|backupoperator|(deny)?data(reader|writer)))$~',$K):in_array($i,array("master","tempdb","model","msdb")));}}function
unicode_prefix($P){return(strlen($P)!=utf8_length($P)?"N":"");}function
idf_escape($t){return"[".str_replace("]","]]",$t)."]";}function
table($t){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($t);}function
get_databases($he){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($F,$Z,$y,$bi=0,$Ok=" "){return($y?" TOP (".($y+$bi).")":"")." $F$Z";}function
limit1($Q,$F,$Z,$Ok="\n"){return
limit($F,$Z,1,0,$Ok);}function
db_collation($i,array$Eb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($i));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables(array$h){$H=array();foreach($h
as$i){connection()->select_db($i);$H[$i]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$H;}function
table_status($A="",$Od=false){$H=array();$jl=array();foreach(get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats
GROUP BY object_id",null,"")as$I){$Vh=$I["object_id"];unset($I["object_id"]);$jl[$Vh]=$I;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine,
	(SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($A!=""?"AND name = ".q($A):"ORDER BY name"))as$I){$Vh=$I["object_id"];unset($I["object_id"]);$H[$I["Name"]]=$I+idx($jl,$Vh,array());}return$H;}function
is_view(array$R){return$R["Engine"]=="VIEW";}function
fk_support(array$R){return
true;}function
type_length($T,array$I){return(preg_match("~char|binary~",$T)?($I["max_length"]==-1?"max":intval($I["max_length"])/($T[0]=='n'?2:1)):($T=="decimal"?"$I[precision],$I[scale]":(preg_match('~^(datetime2|datetimeoffset|time)$~',$T)?$I["scale"]:($T=="vector"?(intval($I["max_length"])-8)/4:""))));}function
fields($Q){$Lb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($Q).", 'column', NULL)");$H=array();$Ql=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($Q));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name,
	COALESCE(bt.name, t.name) type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.types bt ON t.system_type_id = bt.user_type_id AND t.is_user_defined = 1
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($Ql))as$I){$T=$I["type"];$x=type_length($T,$I);$H[$I["name"]]=array("field"=>$I["name"],"full_type"=>$T.($x!=""?"($x)":""),"type"=>$T,"length"=>$x,"default"=>(preg_match("~^\(N?'(.*)'\)$~s",$I["default"],$_)?str_replace("''","'",$_[1]):$I["default"]),"default_constraint"=>$I["default_constraint"],"null"=>$I["is_nullable"],"auto_increment"=>$I["is_identity"],"collation"=>$I["collation_name"],"privileges"=>($T=="timestamp"?array("select"=>1,"where"=>1,"order"=>1):array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1)),"primary"=>$I["is_primary_key"],"comment"=>$Lb[$I["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($Ql))as$I){$H[$I["name"]]["generated"]=($I["is_persisted"]?"PERSISTED":"VIRTUAL");$H[$I["name"]]["default"]=$I["definition"];}return$H;}function
indexes($Q,$g=null){$H=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($Q),$g)as$I){$A=$I["name"];$H[$A]["type"]=($I["is_primary_key"]?"PRIMARY":($I["is_unique"]?"UNIQUE":"INDEX"));$H[$A]["lengths"]=array();$H[$A]["columns"][$I["key_ordinal"]]=$I["column_name"];$H[$A]["descs"][$I["key_ordinal"]]=($I["is_descending_key"]?'1':null);}return$H;}function
view($A){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($A))));}function
collations(){$H=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$Db)$H[preg_replace('~_.*~','',$Db)][]=$Db;return$H;}function
information_schema($i,$K=""){return
in_array($K!=""?$K:get_schema(),array("INFORMATION_SCHEMA","sys"));}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($i,$Db){return
queries("CREATE DATABASE ".idf_escape($i).(preg_match('~^[a-z0-9_]+$~i',$Db)?" COLLATE $Db":""));}function
drop_databases(array$h){return!!queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$h)));}function
rename_database($A,$Db){if(preg_match('~^[a-z0-9_]+$~i',$Db))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $Db");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($A));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($Q,$A,array$m,array$je,$Jb,$pd,$Db,$Na,$Yi){$b=array();$Lb=array();$Ci=fields($Q);foreach($m
as$l){$d=idf_escape($l[0]);$W=$l[1];if(!$W)$b["DROP"][]=" COLUMN $d";else{$W[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$W[1]);$Lb[$l[0]]=$W[5];unset($W[5]);if(preg_match('~ AS ~',$W[3]))unset($W[1],$W[2]);if($l[0]=="")$b["ADD"][]="\n  ".implode("",$W).($Q==""?substr($je[$W[0]],16+strlen($W[0])):"");else{$j=$W[3];unset($W[3]);unset($W[6]);if($d!=$W[0])queries("EXEC sp_rename ".q(table($Q).".$d").", ".q(idf_unescape($W[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$W)][]="";$Bi=$Ci[$l[0]];if(default_value($Bi)!=$j){if($Bi["default"]!==null)$b["DROP"][]=" ".idf_escape($Bi["default_constraint"]);if($j)$b["ADD"][]="\n $j FOR $d";}}}}if($Q==""){$ma=(array)$b["ADD"];foreach($je
as$w=>$W){if(!is_string($w))$ma[]="\n$W";}return
queries("CREATE TABLE ".table($A)." (".implode(",",$ma)."\n)");}if($Q!=$A)queries("EXEC sp_rename ".q(table($Q)).", ".q($A));if($je)$b[""]=$je;foreach($b
as$w=>$W){if(!queries("ALTER TABLE ".table($A)." $w".implode(",",$W)))return
false;}foreach($Lb
as$w=>$W){$Jb=substr($W,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($A).", @level2type = N'Column', @level2name = ".q($w));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $Jb,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($A).",
@level2type = N'Column',
@level2name = ".q($w));}return
true;}function
alter_indexes($Q,$b){$u=array();$Zc=array();foreach($b
as$W){if($W[2]=="DROP"){if($W[0]=="PRIMARY")$Zc[]=idf_escape($W[1]);else$u[]=idf_escape($W[1])." ON ".table($Q);}elseif(!queries(($W[0]!="PRIMARY"?"CREATE $W[0] ".($W[0]!="INDEX"?"INDEX ":"").idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q):"ALTER TABLE ".table($Q)." ADD PRIMARY KEY")." (".implode(", ",$W[2]).")"))return
false;}return(!$u||queries("DROP INDEX ".implode(", ",$u)))&&(!$Zc||queries("ALTER TABLE ".table($Q)." DROP ".implode(", ",$Zc)));}function
found_rows(array$R,array$Z){}function
foreign_keys($Q){$H=array();$mi=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");$K=get_schema();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($Q).", @fktable_owner = ".q($K))as$I){$o=&$H[$I["FK_NAME"]];$o["db"]=($I["PKTABLE_QUALIFIER"]==DB?"":$I["PKTABLE_QUALIFIER"]);$o["ns"]=($I["PKTABLE_OWNER"]==$K?"":$I["PKTABLE_OWNER"]);$o["table"]=$I["PKTABLE_NAME"];$o["on_update"]=$mi[$I["UPDATE_RULE"]];$o["on_delete"]=$mi[$I["DELETE_RULE"]];$o["source"][]=$I["FKCOLUMN_NAME"];$o["target"][]=$I["PKCOLUMN_NAME"];}return$H;}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Dn){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Dn)));}function
drop_tables(array$S){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$S)));}function
move_tables(array$S,array$Dn,$hm){return
apply_queries("ALTER SCHEMA ".idf_escape($hm)." TRANSFER",array_merge($S,$Dn));}function
trigger($A,$Q){if($A=="")return
array();$J=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($A));$H=reset($J);if($H)$H["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$H["text"]);return($H?:array());}function
triggers($Q){$H=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($Q))as$I)$H[$I["name"]]=array($I["Timing"],$I["Event"]);return$H;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
routine($A,$T){$Cc=get_val("SELECT m.definition
FROM sys.objects o
JOIN sys.sql_modules m ON m.object_id = o.object_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.name = ".q($A)." AND o.type = ".q($T=="PROCEDURE"?"P":"FN"));if(!$Cc)return
array();$H=array("definition"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',$Cc),"fields"=>array());foreach(get_rows("SELECT p.name, TYPE_NAME(p.user_type_id) [type], p.max_length, p.precision, p.scale, p.is_output
FROM sys.parameters p
JOIN sys.objects o ON p.object_id = o.object_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.name = ".q($A)."
ORDER BY p.parameter_id")as$I){$Td=$I["type"];$x=type_length($Td,$I);$l=array("field"=>preg_replace('~^@~','',$I["name"]),"type"=>$Td,"length"=>$x,"full_type"=>$Td.($x!=""?"($x)":""),"null"=>true,"inout"=>($I["is_output"]?"OUTPUT":""),);if($l["field"]=="")$H["returns"]=$l;else$H["fields"][]=$l;}return$H;}function
routines(){return
get_rows("SELECT o.name SPECIFIC_NAME, o.name ROUTINE_NAME,
	CASE o.type WHEN 'P' THEN 'PROCEDURE' ELSE 'FUNCTION' END ROUTINE_TYPE, TYPE_NAME(p.user_type_id) DTD_IDENTIFIER
FROM sys.objects o
LEFT JOIN sys.parameters p ON o.object_id = p.object_id AND p.parameter_id = 0
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.type IN ('P', 'FN')
ORDER BY o.name");}function
routine_languages(){return
array();}function
routine_options($pk){return
array();}function
routine_id($A,array$I){return
table($A);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($K,$g=null){$_GET["ns"]=$K;return!!get_val("SELECT 1 FROM sys.schemas WHERE name = ".q($K),0,$g);}function
create_sql($Q,$Na,$Gl){if(is_view(table_status1($Q))){$Cn=view($Q);return"CREATE VIEW ".table($Q)." AS $Cn[select]";}$m=array();$Cj=false;foreach(fields($Q)as$A=>$l){$W=process_field($l,$l);if($W[6])$Cj=true;$m[]=implode("",$W);}foreach(indexes($Q)as$A=>$u){if(!$Cj||$u["type"]!="PRIMARY"){$e=array();foreach($u["columns"]as$w=>$W)$e[]=idf_escape($W).($u["descs"][$w]?" DESC":"");$A=idf_escape($A);$m[]=($u["type"]=="INDEX"?"INDEX $A":"CONSTRAINT $A ".($u["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($Q)as$A=>$qb)$m[]="CONSTRAINT ".idf_escape($A)." CHECK ($qb)";return"CREATE TABLE ".table($Q)." (\n\t".implode(",\n\t",$m)."\n)";}function
foreign_keys_sql($Q){$m=array();foreach(foreign_keys($Q)as$je)$m[]=ltrim(format_foreign_key($je));return($m?"ALTER TABLE ".table($Q)." ADD\n\t".implode(",\n\t",$m).";\n\n":"");}function
truncate_sql($Q){return"TRUNCATE TABLE ".table($Q);}function
use_sql($tc,$Gl=""){return"USE ".idf_escape($tc);}function
use_schema_sql($K,$Gl){$A=idf_escape($K);return($Gl=="DROP+CREATE"?"DROP SCHEMA IF EXISTS $A;\n":"")."IF SCHEMA_ID(".q($K).") IS NULL EXEC(".q("CREATE SCHEMA $A").")";}function
trigger_sql($Q){$H="";foreach(triggers($Q)as$A=>$Jm)$H
.=create_trigger(" ON ".table($Q),trigger($A,$Q)).";";return$H;}function
convert_field(array$l){}function
unconvert_field(array$l,$H){return$H;}function
support($Pd){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|procedure|routine|scheme|sql|table|transaction_ddl|trigger|view|view_trigger)$~',$Pd);}}add_driver("oracle","Oracle");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");function
easy_connect(array$M){return
url_host($M["host"]).($M["port"]!=""?":$M[port]":"").$M["path"];}if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";private$link,$transaction=false;function
_error($ud,$k){if(ini_bool("html_errors"))$k=html_entity_decode(strip_tags($k));$k=preg_replace('~^[^:]*: ~','',$k);$this->error=$k;}function
attach(array$M,$U,$D){$this->link=@oci_new_connect($U,$D,easy_connect($M),"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$k=oci_error();return($k?$k["message"]:lang(26));}function
quote($P){return"'".str_replace("'","''",$P)."'";}function
select_db($tc){return$this->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($tc));}function
query($F,$Vm=false){$G=oci_parse($this->link,$F);$this->error="";if(!$G){$k=oci_error($this->link);$this->errno=$k["code"];$this->error=$k["message"];return
false;}set_error_handler(array($this,'_error'));$H=@oci_execute($G,($this->transaction?OCI_NO_AUTO_COMMIT:OCI_COMMIT_ON_SUCCESS));restore_error_handler();if($H){if(oci_num_fields($G))return
new
Result($G);$this->affected_rows=oci_num_rows($G);oci_free_statement($G);}return$H;}function
timeout($sh){return
function_exists('oci_set_call_timeout')&&oci_set_call_timeout($this->link,$sh);}function
inTransaction(){return$this->transaction;}function
begin(){$this->transaction=true;return
true;}function
commit(){return$this->end_transaction(@oci_commit($this->link));}function
rollback(){return$this->end_transaction(@oci_rollback($this->link));}private
function
end_transaction($H){$this->transaction=false;if(!$H){$k=oci_error($this->link);$this->errno=$k["code"];$this->error=$k["message"];}return$H;}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($G){$this->result=$G;}private
function
convert($I){foreach((array)$I
as$w=>$W){if(is_a($W,'OCILob')||is_a($W,'OCI-Lob'))$I[$w]=$W->load();}return$I;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$H=new
\stdClass;$H->name=oci_field_name($this->result,$d);$T=oci_field_type($this->result,$d);$H->native_type=idx(array(100=>"binary_float","binary_double"),$T,$T);return$H;}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";function
attach(array$M,$U,$D){return$this->dsn("oci:dbname=//".easy_connect($M).";charset=AL32UTF8",$U,$D);}function
select_db($tc){return$this->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($tc));}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";static$serverPath=true;var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
operators($Pl){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$D){$f=parent::connect($M,$U,$D);if(is_object($f))$f->query("ALTER SESSION SET CURSOR_SHARING = FORCE"." NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'"." NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF'"." NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF TZH:TZM'");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(29)=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),lang(30)=>array("date"=>19,"timestamp"=>29,"interval year"=>12,"interval day"=>28),lang(31)=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),lang(32)=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),lang(34)=>array("sdo_geometry"=>0),);}function
begin(){return$this->conn->begin();}function
convertSearch($t,array$W,array$l){$T=$l["type"];$hj=strpos($W["op"],"LIKE")!==false;if($T=="xmltype")return"XMLSERIALIZE(CONTENT $t AS VARCHAR2(4000))";if($T=="json")return"JSON_SERIALIZE($t)";if(preg_match('~^(date|timestamp)~',$T))return"TO_CHAR($t, 'YYYY-MM-DD HH24:MI:SS')";if(preg_match('~char~',$T)||(preg_match('~clob~',$T)&&$hj))return$t;return(!$hj&&preg_match(number_type(),$T)?$t:"TO_CHAR($t)");}function
quoteBinary($yk){return"HEXTORAW(".q(bin2hex($yk)).")";}function
typeName(\stdClass$l){return
strtolower(parent::typeName($l));}function
hasCStyleEscapes(){return
true;}function
select($Q,array$L,array$Z,array$q,array$wi=array(),$y=1,$C=0,$Ej=false){if(in_array("*",$L)){$bc=array();$ne=false;foreach(fields($Q)as$A=>$l){$Ha=convert_field($l);$ne=($ne||$Ha);$bc[]=($Ha?"$Ha AS ":"").idf_escape($A);}if($ne)$L=$bc;}return
parent::select($Q,$L,$Z,$q,$wi,$y,$C,$Ej);}function
allFields(){$H=array();$J=get_rows('SELECT c.table_name "tab", c.column_name "field", c.data_type "type", c.nullable "nullable",
	c.data_precision "precision", c.data_scale "scale", c.char_col_decl_length "char_length"
FROM all_tab_columns c
WHERE '.where_owner("c.owner").'
ORDER BY c.table_name, c.column_id',$this->conn);foreach($J
as$I){$x="$I[precision],$I[scale]";$I["length"]=(strpos($I["type"],"(")?"":($x==","?$I["char_length"]:$x));$I["type"]=strtolower($I["type"]);$I["null"]=($I["nullable"]=="Y");$H[$I["tab"]][]=$I;}return$H;}}function
idf_escape($t){return'"'.str_replace('"','""',$t).'"';}function
table($t){return
idf_escape($t);}function
get_databases($he){$H=get_vals("SELECT username FROM all_users WHERE oracle_maintained = 'N' ORDER BY 1");return($H?:get_vals("SELECT username FROM all_users ORDER BY 1"));}function
limit($F,$Z,$y,$bi=0,$Ok=" "){return($bi?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $F$Z) t WHERE rownum <= ".($y+$bi).") WHERE rnum > $bi":($y?" * FROM (SELECT $F$Z) WHERE rownum <= ".($y+$bi):" $F$Z"));}function
limit1($Q,$F,$Z,$Ok="\n"){return" $F$Z";}function
db_collation($i,array$Eb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
where_owner($Ki="owner"){return"$Ki = ".q(DB);}function
views_table($e){return"(SELECT $e FROM all_views WHERE ".where_owner().")";}function
objects_table(){return"(SELECT object_name, DECODE(object_type, 'VIEW', 'view', 'table') object_type FROM all_objects WHERE ".where_owner()." AND object_type IN ('TABLE', 'VIEW'))";}function
tables_list(){return
get_key_vals("SELECT * FROM ".objects_table()." ORDER BY 1");}function
count_tables(array$h){$H=array();foreach($h
as$i)$H[$i]=get_val("SELECT COUNT(*) FROM all_objects WHERE object_type IN ('TABLE', 'VIEW') AND owner = ".q($i));return$H;}function
table_status($A="",$Od=false){$H=array();$Dk=q($A);if($Od||$A!=""){foreach(get_rows('SELECT object_name "Name", object_type "Engine" FROM '.objects_table().($A!=""?" WHERE object_name = $Dk":"").' ORDER BY 1')as$I)$H[$I["Name"]]=$I;return$H;}foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i
	JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE '.where_owner("t.owner")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM ".views_table("view_name")."
ORDER BY 1")as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support(array$R){return
true;}function
fields($Q){$H=array();$jf=null;foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($Q)." AND ".where_owner()." ORDER BY column_id")as$I){$T=$I["DATA_TYPE"];$x="$I[DATA_PRECISION],$I[DATA_SCALE]";if($x==",")$x=$I["CHAR_COL_DECL_LENGTH"];elseif(strpos($T,"("))$x="";$j=$I["DATA_DEFAULT"];if($j!==null){$j=rtrim($j);if(preg_match("~^'(.*)'\$~s",$j,$_))$j=str_replace("''","'",$_[1]);}if($I["IDENTITY_COLUMN"]=="YES"){if($jf===null)$jf=get_key_vals("SELECT column_name, generation_type FROM all_tab_identity_cols WHERE table_name = ".q($Q)." AND ".where_owner());$j="GENERATED ".$jf[$I["COLUMN_NAME"]].($I["DEFAULT_ON_NULL"]=="YES"?" ON NULL":"")." AS IDENTITY";}$Hj=array("insert"=>1,"select"=>1,"update"=>1,"order"=>1);if($I["DATA_TYPE_OWNER"]==""||$T=="XMLTYPE")$Hj["where"]=1;$H[$I["COLUMN_NAME"]]=array("field"=>$I["COLUMN_NAME"],"full_type"=>$T.($x?"($x)":""),"type"=>strtolower($T),"length"=>$x,"default"=>$j,"null"=>($I["NULLABLE"]=="Y"),"auto_increment"=>($I["IDENTITY_COLUMN"]=="YES"),"privileges"=>$Hj,);}return$H;}function
table_constraints($Q,$g=null){$H=array();foreach(get_rows('SELECT c.constraint_name "name", c.constraint_type "type", c.r_owner "r_owner", c.r_constraint_name "r_constraint", c.delete_rule "delete_rule", cc.column_name "column"
FROM all_constraints c
JOIN all_cons_columns cc ON cc.owner = c.owner AND cc.constraint_name = c.constraint_name
WHERE c.constraint_type IN (\'P\', \'U\', \'R\') AND '.where_owner("c.owner")." AND c.table_name = ".q($Q).'
ORDER BY cc.position',$g)as$I){$A=$I["name"];$H[$A]["type"]=$I["type"];$H[$A]["r_owner"]=$I["r_owner"];$H[$A]["r_constraint"]=$I["r_constraint"];$H[$A]["delete_rule"]=$I["delete_rule"];$H[$A]["columns"][]=$I["column"];}return$H;}function
indexes($Q,$g=null){$H=array();$Vb=array();foreach(table_constraints($Q,$g)as$A=>$Ub)$Vb[$A]=$Ub["type"];foreach(get_rows("SELECT aic.*, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($Q)." AND ".where_owner("aic.table_owner")."
ORDER BY aic.column_position",$g)as$I){$wf=$I["INDEX_NAME"];$Hb=$I["DATA_DEFAULT"];$Hb=($Hb?trim($Hb,'"'):$I["COLUMN_NAME"]);$T=idx($Vb,$wf);$H[$wf]["type"]=($T=="P"?"PRIMARY":($T=="U"?"UNIQUE":"INDEX"));$H[$wf]["columns"][]=$Hb;$H[$wf]["lengths"][]=($I["CHAR_LENGTH"]&&$I["CHAR_LENGTH"]!=$I["COLUMN_LENGTH"]?$I["CHAR_LENGTH"]:null);$H[$wf]["descs"][]=($I["DESCEND"]&&$I["DESCEND"]=="DESC"?'1':null);}uasort($H,function($ia,$Ra){$wi=array("PRIMARY"=>0,"UNIQUE"=>1,"INDEX"=>2);return$wi[$ia["type"]]-$wi[$Ra["type"]];});return$H;}function
view($A){$J=get_rows('SELECT text "select" FROM '.views_table("view_name, text").' WHERE view_name = '.q($A));return($J?$J[0]:array());}function
collations(){return
array();}function
information_schema($i,$K=""){return
in_array($K!=""?$K:$i,array("INFORMATION_SCHEMA","SYS","SYSTEM"));}function
error(){return
h(connection()->error);}function
explain(Db$f,$F){$f->query("EXPLAIN PLAN FOR $F");return$f->query("SELECT * FROM plan_table");}function
found_rows(array$R,array$Z){}function
auto_increment(){return"";}function
alter_table($Q,$A,array$m,array$je,$Jb,$pd,$Db,$Na,$Yi){$b=$Zc=array();$Ci=($Q?fields($Q):array());foreach($m
as$l){$W=$l[1];if($W&&$l[0]!=""&&idf_escape($l[0])!=$W[0])queries("ALTER TABLE ".table($Q)." RENAME COLUMN ".idf_escape($l[0])." TO $W[0]");$Bi=$Ci[$l[0]];if($W&&$Bi){$di=process_field($Bi,$Bi);if($W[2]==$di[2])$W[2]="";}if($W){list($W[2],$W[3])=array($W[3],$W[2]);$b[]=($Q!=""?($l[0]!=""?"MODIFY (":"ADD ("):"  ").implode($W).($Q!=""?")":"");}else$Zc[]=idf_escape($l[0]);}if($Q=="")return
queries("CREATE TABLE ".table($A)." (\n".implode(",\n",array_merge($b,$je))."\n)");return(!$b||queries("ALTER TABLE ".table($Q)."\n".implode("\n",$b)))&&(!$Zc||queries("ALTER TABLE ".table($Q)." DROP (".implode(", ",$Zc).")"))&&($Q==$A||queries("ALTER TABLE ".table($Q)." RENAME TO ".table($A)));}function
alter_indexes($Q,$b){$Zc=array();$Mj=array();foreach($b
as$W){if($W[0]!="INDEX"){$W[2]=preg_replace('~ DESC$~','',$W[2]);$gc=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");array_unshift($Mj,"ALTER TABLE ".table($Q).$gc);}elseif($W[2]=="DROP")$Zc[]=idf_escape($W[1]);else$Mj[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q)." (".implode(", ",$W[2]).")";}if($Zc)array_unshift($Mj,"DROP INDEX ".implode(", ",$Zc));foreach($Mj
as$F){if(!queries($F))return
false;}return
true;}function
foreign_keys($Q){$H=array();$km=array();foreach(table_constraints($Q)as$A=>$Ub){if($Ub["type"]=="R"){$H[$A]=array("source"=>$Ub["columns"],"target"=>array(),"on_delete"=>$Ub["delete_rule"],"on_update"=>null,);$km[$A]=array($Ub["r_owner"],$Ub["r_constraint"]);}}if($km){$Z=array();foreach($km
as$hm)$Z[]="(owner = ".q($hm[0])." AND constraint_name = ".q($hm[1]).")";foreach(get_rows("SELECT owner, constraint_name, table_name, column_name FROM all_cons_columns WHERE ".implode(" OR ",array_unique($Z))." ORDER BY position")as$I){foreach($km
as$A=>$hm){if($hm==array($I["OWNER"],$I["CONSTRAINT_NAME"])){$H[$A]["db"]=$I["OWNER"];$H[$A]["table"]=$I["TABLE_NAME"];$H[$A]["target"][]=$I["COLUMN_NAME"];}}}}return$H;}function
trigger($A,$Q){if($A=="")return
array();$J=get_rows('SELECT trigger_name "Trigger", trigger_type "Type", triggering_event "Event", trigger_body "Statement"
FROM all_triggers
WHERE trigger_name = '.q($A)." AND ".where_owner());$H=reset($J);if($H){$T=$H["Type"];$H["Timing"]=(preg_match('~^(BEFORE|AFTER|INSTEAD OF)~',$T,$_)?$_[1]:$T);$H["Type"]=(preg_match('~EACH ROW~',$T)||$T=="INSTEAD OF"?"FOR EACH ROW":"");}return($H?:array());}function
triggers($Q){$H=array();foreach(get_rows("SELECT trigger_name, trigger_type, triggering_event FROM all_triggers WHERE table_name = ".q($Q)." AND ".where_owner())as$I)$H[$I["TRIGGER_NAME"]]=array(preg_replace('~ (STATEMENT|EACH ROW)$~','',$I["TRIGGER_TYPE"]),$I["TRIGGERING_EVENT"]);return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE","INSERT OR UPDATE","INSERT OR DELETE","UPDATE OR DELETE","INSERT OR UPDATE OR DELETE"),"Type"=>array("FOR EACH ROW",""),);}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Dn){return
apply_queries("DROP VIEW",$Dn);}function
drop_tables(array$S){return
apply_queries("DROP TABLE",$S);}function
last_id($G){return"0";}function
create_database($i,$Db){$H=queries("CREATE USER ".idf_escape($i)." NO AUTHENTICATION");return($H?queries("GRANT UNLIMITED TABLESPACE TO ".idf_escape($i)):$H);}function
drop_databases(array$h){$H=true;foreach($h
as$i)$H=!!queries("DROP USER ".idf_escape($i)." CASCADE")&&$H;return$H;}function
rename_database($A,$Db){return!!queries("ALTER USER ".idf_escape(DB)." RENAME TO ".idf_escape($A));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$H=array();$J=get_rows('SELECT * FROM v$instance');foreach(reset($J)as$w=>$W)$H[]=array($w,$W);return$H;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess
LEFT JOIN v$sql sql ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$l){if($l["type"]=="sdo_geometry")return"SDO_UTIL.TO_WKTGEOMETRY(".idf_escape($l["field"]).")";}function
unconvert_field(array$l,$H){return($l["type"]=="sdo_geometry"?"SDO_UTIL.FROM_WKTGEOMETRY($H)":$H);}function
support($Pd){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|sql|status|table|trigger|variables|view|view_trigger)$~',$Pd);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.1.1+43f4678f")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($gc=false){return
password_file($gc);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
verifyLoginToken(){return
true;}function
serverName($M){return
h($M);}function
database(){return
DB;}function
databases($he=true){return
get_databases($he);}function
pluginsLinks(){}function
operators($Pl=null){return
driver()->operators($Pl);}function
schemas(){$H=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$H))array_unshift($H,$_GET["ns"]);return$H;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$kc){return$kc;}function
verifyVersion(){return
true;}function
serviceWorker(){service_worker();}function
manifest(){$ef=$_SERVER["HTTP_HOST"]?:$_SERVER["SERVER_NAME"];$Mk=preg_replace('~\?.*~','',ME)?:'.';return
array('name'=>"Adminer".($ef!=""?" - $ef":""),'short_name'=>'Adminer','description'=>lang(38),'start_url'=>$Mk,'scope'=>$Mk,'display'=>'minimal-ui','icons'=>array(array('src'=>preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.1.1+43f4678f",'sizes'=>'any','type'=>'image/svg+xml')),);}function
head($pc=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$H=array();foreach(array("","-dark")as$oh){$n="adminer$oh.css";if(file_exists($n)){$Vd=file_get_contents($n);$H["$n?v=".crc32($Vd)]=($oh?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Vd)?'':'light'));}}return$H;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.lang(39).'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,on('change','loginDriver'))),adminer()->loginFormField('server','<tr><th>'.lang(40).'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".lang(41)."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.lang(42).'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("fire(qs('#username').form['auth[driver]'], 'change');")),adminer()->loginFormField('password','<tr><th>'.lang(43).'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.lang(44).'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".lang(45)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(46))."\n";}function
loginFormField($A,$Ve,$X){return$Ve.$X."\n";}function
login($Fg,$D){if($D=="")return
lang(47).require_password_link(null);if(!Driver::$passwords)return
lang(48).require_password_link($D);if(!password_required())return
lang(49).require_password_link($D);return
true;}function
tableName(array$Pl){return
h($Pl["Name"]);}function
fieldName(array$l,$wi=0){$T=$l["full_type"].($l["null"]?" NULL":"");$Jb=$l["comment"];return'<span title="'.h($T.($Jb!=""?($T?": ":"").$Jb:'')).'">'.h($l["field"]).'</span>';}function
commentValue($T,$Jb){if($Jb==""||$T=='TABLE'||$T=='COLUMN')return
h($Jb);$wj=function($yk,$kb='td'){return
preg_replace('~^~m','<tr>',preg_replace('~\|~',"<$kb>",preg_replace('~\|$~m',"",rtrim($yk))));};$Q='(\+--[-+]+\+\n)';$I='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$Q?$I$Q?($I*)$Q?~m",function($_)use($wj){return"<table>\n".($_[1]?"<thead>".$wj($_[2],'th')."<tbody>\n":$wj($_[2])).$wj($_[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($Jb))))."</pre>\n";}function
commentInput($T,$c,$Jb){$X=h($Jb);return(preg_match('~\n~',$X)?"<textarea$c rows='2' cols='".($T=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$X</textarea>":"<input$c value='$X'>");}function
selectLinks(array$Pl,$N=""){$A=$Pl["Name"];echo'<p class="links">';$Ag=array();if($A!="")$Ag["select"]=lang(50);if(support("table")||support("indexes"))$Ag["table"]=lang(51);if(support("table")){if(is_view($Pl)){if(support("view"))$Ag["view"]=lang(52);}elseif(function_exists('Adminer\alter_table')&&$A!="")$Ag["create"]=lang(53);}if($N!==null)$Ag["edit"]=lang(54);foreach($Ag
as$w=>$W)echo" <a href='".h(ME)."$w=".url_escape($A).($w=="edit"?$N:"")."'".bold(isset($_GET[$w])).">$W</a>";echo"\n";}function
foreignKeys($Q){return
foreign_keys($Q);}function
backwardKeys($Q,$Ol){return
array();}function
backwardKeysPrint(array$Ta,array$I){}function
selectQuery($F,$_l,$Nd=false){$H="\n";if(!$Nd&&($Hn=driver()->warnings())){$s="warnings";$H=", <a href='#$s' class='toggle'>".lang(55)."</a>"."$H<div id='$s' class='hidden'>\n$Hn</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$F))."</code> <span class='time'>(".format_time($_l).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($F)."' class='hover'>".lang(14)."</a>":"").$H;}function
sqlCommandQuery($F){return
shorten_utf8(trim($F),1000);}function
sqlPrintAfter(){}function
explain(Db$f,$F,array$_i){$G=explain($f,$F);if(!$G)return"";ob_start();print_select_result($G,$f,$_i);return
ob_get_clean();}function
rowDescription($Q){return"";}function
rowDescriptions(array$J,array$ke){return$J;}function
selectLink($W,array$l){}function
selectVal($W,$z,array$l,$Fi){$H=($W===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$l["type"])&&!preg_match("~var~",$l["type"])?"<code>$W</code>":(preg_match('~^jsonb?$~',$l["full_type"])?"<code class='jush-json'>$W</code>":$W)));if(is_blob($l)&&!is_utf8($W))$H="<i>".lang(56,strlen($Fi))."</i>";return($z?"<a href='".h($z)."'".(is_url($z)?target_blank():"").">$H</a>":$H);}function
editVal($W,array$l){return$W;}function
config(){return
array();}function
tableStructurePrint(array$m,$Pl=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".lang(57)."<th>".lang(58).(support("comment")?"<th>".lang(59):"")."<tbody>\n";$tn=(support("type")?types():array());foreach($m
as$l){echo"<tr><th>".h($l["field"]);$T=h($l["full_type"]);$Db=h($l["collation"]);echo"<td><span title='$Db'>".(in_array($T,$tn)?"<a href='".h(ME.'type='.url_escape($T))."'>$T</a>":$T.($Db&&isset($Pl["Collation"])&&$Db!=$Pl["Collation"]?" $Db":""))."</span>",($l["null"]?" <i>NULL</i>":""),($l["auto_increment"]?" <i>".lang(60)."</i>":""),(isset($l["default"])?" <span title='".lang(61)."'>[<b>".($l["generated"]?"<code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($l["default"])),80,"</code>"):h($l["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$l["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$v,array$Pl){$Ti=false;foreach($v
as$A=>$u)$Ti|=!!$u["partial"];echo"<table>\n";$zc=first(driver()->indexAlgorithms($Pl));foreach($v
as$A=>$u){ksort($u["columns"]);$Ej=array();foreach($u["columns"]as$w=>$W)$Ej[]="<i>".h($W)."</i>".($u["lengths"][$w]?"(".h($u["lengths"][$w]).")":"").($u["descs"][$w]?" DESC":"");echo"<tr title='".h($A)."'>","<th>".h($u["type"]).($zc&&$u['algorithm']!=$zc?" (".h($u['algorithm']).")":""),"<td>".implode(", ",$Ej);if($Ti)echo"<td>".($u['partial']?"<code class='jush-".JUSH."'>WHERE ".h($u['partial']):"");echo"\n";}echo"</table>\n";}function
namePattern($T){if($T=="FOREIGN"||$T=="CHECK")return"";if($T=="TRIGGER")return"{table}_{timing}{event}";return(JUSH=="sql"?"":"{table}_")."{columns}";}function
selectColumnsPrint(array$L,array$e){print_fieldset("select",lang(62),$L);$r=0;$L[""]=array();foreach($L
as$w=>$W){$W=idx($_GET["columns"],$w,array());$d=select_input(" name='columns[$r][col]' data-default=''".on('change',($w!==""?'selectFieldChange':'selectAddRow')),$e,$W["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$r][fun]",array(-1=>"")+array_filter(array(lang(63)=>driver()->functions,lang(64)=>driver()->grouping)),$W["fun"]," data-default=''".on('change',($w!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($d)":$d)."</div>\n";$r++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$v,$Pl=null){print_fieldset("search",lang(65),$Z);foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$u["columns"]))."</i>) ".h(driver()->fulltextOperator)," <input type='search' name='fulltext[$r]' value='".h(idx($_GET["fulltext"],$r))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$r]",1,isset($_GET["boolean"][$r]),"BOOL"):''),"</div>\n";}$ri=adminer()->operators($Pl);foreach(array_merge((array)$_GET["where"],array(array()))as$r=>$W){if(!$W||(("$W[col]$W[val]"!=""||preg_match('~NULL$~',$W["op"]))&&in_array($W["op"],$ri)))echo"<div>".select_input(" name='where[$r][col]' data-default=''".on('change',($W?'selectFieldChange':'selectAddRow')),$e,$W["col"],"(".lang(66).")"),html_select("where[$r][op]",$ri,$W["op"]," data-default='".h(first($ri))."'".on('change','selectFirstChange')),"<input type='search' name='where[$r][val]' value='".h($W["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$wi,array$e,array$v){print_fieldset("sort",lang(67),$wi);$r=0;foreach((array)$_GET["order"]as$w=>$W){if($W!=""){echo"<div>".select_input(" name='order[$r]' data-default=''".on('change','selectFieldChange'),$e,$W),checkbox("desc[$r]",1,isset($_GET["desc"][$w]),lang(68))."</div>\n";$r++;}}echo"<div>".select_input(" name='order[$r]' data-default=''".on('change','selectAddRow'),$e),checkbox("desc[$r]",1,false,lang(68))."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($y){echo"<fieldset><legend>".lang(69)."</legend><div>","<input type='number' name='limit' class='size' value='".h($y?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($om){echo"<fieldset><legend>".lang(70)."</legend><div>","<input type='number' name='text_length' class='size' value='".h($om)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$v){echo"<fieldset><legend>".lang(71)."</legend><div>","<input type='submit' value='".lang(62)."'>"," <span id='noindex' title='".lang(72)."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($v
as$u){$oc=reset($u["columns"]);if($u["type"]!="FULLTEXT"&&$oc)$e[$oc]=1;}$e[""]=1;foreach($e
as$w=>$W)json_row($w);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$ld,array$e){}function
selectColumnsProcess(array$e,array$v){$L=array();$q=array();foreach((array)$_GET["columns"]as$w=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],driver()->functions)||in_array($W["fun"],driver()->grouping)))){$L[$w]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],driver()->grouping))$q[]=$L[$w];}}return
array($L,$q);}function
selectSearchProcess(array$m,array$v,$Pl=null){$H=array();foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$r)!="")$H[]=driver()->fulltextSql($r,$u,$_GET["fulltext"][$r],isset($_GET["boolean"][$r]));}$ri=adminer()->operators($Pl);foreach((array)$_GET["where"]as$w=>$W){$W+=array("col"=>"","op"=>first($ri),"val"=>"");$_GET["where"][$w]=$W;$Bb=$W["col"];if(("$Bb$W[val]"!=""||preg_match('~NULL$~',$W["op"]))&&in_array($W["op"],$ri)){if($W["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$Ob=array();foreach(($Bb!=""?array($Bb=>$m[$Bb]):$m)as$A=>$l){$yj="";$Nb=" $W[op]";if(preg_match('~IN$~',$W["op"]))$Nb
.=" ".($W["val"]!=""?process_in($W["val"]):"(NULL)");elseif($W["op"]=="SQL")$Nb=" $W[val]";elseif(preg_match('~^(I?LIKE) %%$~',$W["op"],$_))$Nb=" $_[1] ".q("%$W[val]%");elseif($W["op"]=="FIND_IN_SET"){$yj="$W[op](".q($W["val"]).", ";$Nb=")";}elseif(!preg_match('~NULL$~',$W["op"]))$Nb
.=" ".q($W["val"]);if($Bb!=""||is_searchable($l,$W))$Ob[]=$yj.driver()->convertSearch(idf_escape($A),$W,$l).$Nb;}$H[]=(count($Ob)==1?$Ob[0]:($Ob?"(".implode(" OR ",$Ob).")":"1 = 0"));}}return$H;}function
selectOrderProcess(array$m,array$v){$H=array();foreach((array)$_GET["order"]as$w=>$W){if($W!="")$H[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$w])?" DESC".(JUSH=='pgsql'&&idx($m[$W],"null")?" NULLS LAST":""):"");}return$H;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$ke){return
false;}function
selectQueryBuild(array$L,array$Z,array$q,array$wi,$y,$C){return"";}function
messageQuery($F,$qm,$Nd=false){restart_session();$bf=&get_session("queries");if(!idx($bf,$_GET["db"]))$bf[$_GET["db"]]=array();if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$bf[$_GET["db"]][]=array($F,time(),$qm);$vl="sql-".count($bf[$_GET["db"]]);$H="<a href='#$vl' class='toggle'>".lang(73)."</a> ".copy_icon()."\n";if(!$Nd&&($Hn=driver()->warnings())){$s="warnings-".count($bf[$_GET["db"]]);$H="<a href='#$s' class='toggle'>".lang(55)."</a>, $H<div id='$s' class='hidden'>\n$Hn</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $H<div id='$vl' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($F,1e4)."</code></pre>".($qm?" <span class='time'>($qm)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($bf[$_GET["db"]])-1)).'">'.lang(14).'</a>':'').'</div>';}function
error(){return
error();}function
editRowPrint($Q,array$m,$I,$gn,$F='',$qm=''){echo($F!=""?"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$F))."</code> <span class='time'>($qm)</span>\n":"");}function
editFunctions(array$l){$H=($l["null"]?"NULL/":"");$Re=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$w=>$ye){if(!$w||(!isset($_GET["call"])&&$Re)){foreach($ye
as$hj=>$W){if(!$hj||preg_match("~$hj~",$l["type"]))$H
.="/$W";}}if($w&&$ye&&!preg_match('~set|bool~',$l["type"])&&!is_blob($l))$H
.="/SQL";}if($l["auto_increment"]&&!$Re)$H=lang(60);return
explode("/",$H);}function
editInput($Q,array$l,$c,$X){if($l["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='orig' checked><i>".lang(12)."</i></label> ":"").enum_input("radio",$c,$l,$X,"NULL");return"";}function
editHint($Q,array$l,$X){return"";}function
processInput(array$l,$X,$p=""){if($p=="SQL")return$X;$A=$l["field"];$H=q($X);if(preg_match('~^(now|getdate|uuid)$~',$p))$H="$p()";elseif(preg_match('~^current_(date|timestamp)$~',$p))$H=$p;elseif(preg_match('~^([+-]|\|\|)$~',$p))$H=idf_escape($A)." $p $H";elseif(preg_match('~^[+-] interval$~',$p))$H=idf_escape($A)." $p ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&JUSH!="pgsql"?$X:$H);elseif(preg_match('~^(addtime|subtime|concat)$~',$p))$H="$p(".idf_escape($A).", $H)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$p))$H="$p($H)";return
unconvert_field($l,$H);}function
dumpOutput(){$H=array('text'=>lang(74),'file'=>lang(75));if(function_exists('gzencode'))$H['gz']='gzip';return$H;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpPrint(){}function
dumpDatabase($i){}function
dumpTable($Q,$Gl,$Vf=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Gl)dump_csv(array_keys(fields($Q)));}else{if($Vf==2){$m=array();foreach(fields($Q)as$A=>$l)$m[]=idf_escape($A)." ".full_type_sql($l);$gc="CREATE TABLE ".table($Q)." (".implode(", ",$m).")";}else$gc=create_sql($Q,$_POST["auto_increment"],$Gl);set_utf8mb4($gc);if($Gl&&$gc){if(($Gl=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$Vf==1)echo"DROP ".($Vf==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($Vf==1)$gc=remove_definer($gc);echo"$gc;\n\n";}}}function
dumpData($Q,$Gl,$F,array$L=array(),array$Z=array(),array$q=array(),array$wi=array()){if($Gl){$Qg=(JUSH=="sqlite"?0:1048576);$m=array();$kf=false;if($_POST["format"]=="sql"){if($Gl=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($Q).";\n";$m=fields($Q);if(JUSH=="mssql"){foreach($m
as$l){if($l["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$kf=true;break;}}}}$G=($F!=""?connection()->query($F,1):driver()->select($Q,($L?:array("*")),$Z,$q,$wi,0));if($G){$Gf="";$eb="";$dg=array();$ze=array();$Il="";$Qd=($Q!=''?'fetch_assoc':'fetch_row');$fc=0;while($I=$G->$Qd()){if(!$dg){$Y=array();foreach($I
as$W){$l=$G->fetch_field();if(idx($m[$l->name],'generated')){$ze[$l->name]=true;continue;}$dg[]=$l->name;$w=idf_escape($l->name);$Y[]="$w = VALUES($w)";}$Il=($Gl=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($Gl=="table"){dump_csv($dg);$Gl="INSERT";}dump_csv($I);}else{if(!$Gf)$Gf="INSERT INTO ".table($Q)." (".implode(", ",array_map('Adminer\idf_escape',$dg)).") VALUES";foreach($I
as$w=>$W){if($ze[$w]){unset($I[$w]);continue;}$l=$m[$w];$I[$w]=($W===null?"NULL":($W===false?0:unconvert_field($l,preg_match(number_type(),$l["type"])&&!preg_match('~\[~',$l["full_type"])&&is_numeric($W)?$W:(!is_blob($l)||is_utf8($W)?q($W):driver()->quoteBinary($W)))));}$yk=($Qg?"\n":" ")."(".implode(",\t",$I).")";if(!$eb)$eb=$Gf.$yk;elseif(JUSH=='mssql'?$fc%1000!=0:strlen($eb)+4+strlen($yk)+strlen($Il)<$Qg)$eb
.=",$yk";else{echo$eb.$Il;$eb=$Gf.$yk;}}$fc++;}if($eb)echo$eb.$Il;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($kf)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
dumpFilename($if){return
friendly_url($if!=""?$if:(SERVER?:"localhost"));}function
dumpHeaders($if,$uh=false){$Ji=$_POST["output"];$Id=(preg_match('~sql~',$_POST["format"])?"sql":($uh?"tar":"csv"));header("Content-Type: ".($Ji=="gz"?"application/x-gzip":($Id=="tar"?"application/x-tar":($Id=="sql"||$Ji!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($Ji=="gz"){ob_start(function($P){return
gzencode($P);},1e6);}return$Id;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.lang(76)."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?lang(77):lang(78))."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.lang(79)."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".lang(80)."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".lang(81)."</a>\n":""),(support("sequence")?"<a href='#sequences'>".lang(82)."</a>\n":""),(support("type")?"<a href='#user-types'>".lang(0)."</a>\n":""),(support("event")?"<a href='#events'>".lang(83)."</a>\n":"");return
true;}function
navigation($nh){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$Mh=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$Mh)<0?h($Mh):"").version_iframe()."</a>","</span></h1>\n";switch_lang();if($nh=="auth"){$Ji="";foreach((array)$_SESSION["pwds"]as$An=>$bl){foreach($bl
as$M=>$un){$A=h(get_setting("vendor-$An-$M")?:get_driver($An));foreach($un
as$U=>$D){if($A&&$D!==null){$xc=$_SESSION["db"][$An][$M][$U];foreach(($xc?array_keys($xc):array(""))as$i)$Ji
.="<li><a href='".h(auth_url($An,$M,$U,$i))."'>($A) ".h("$U@").($M!=""?adminer()->serverName($M):"").h($i!=""?" - $i":"")."</a>\n";}}}}if($Ji)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$Ji</ul>\n";}else{$S=array();if($_GET["ns"]!==""&&!$nh&&DB!=""){connection()->select_db(DB);$S=table_status('',true);}adminer()->syntaxHighlighting($S);adminer()->databasesPrint($nh);$la=array();if(DB==""||!$nh){if(support("sql")){$la['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".lang(73)."</a>";$la['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".lang(84)."</a>";}$la['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".lang(85)."</a>";}$qf=$_GET["ns"]!==""&&!$nh&&DB!="";if($qf&&function_exists('Adminer\alter_table'))$la['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".lang(86)."</a>";$la=adminer()->menuActions($la,$nh);echo($la?"<p class='links'>\n".implode("\n",$la)."\n":"");if($qf){if($S)adminer()->tablesPrint($S);else
echo"<p class='message'>".lang(13)."</p>\n";}}}function
syntaxHighlighting(array$S){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.1.1+43f4678f",true);$qh=preg_replace('~<(?=/script)~i','<\\',Driver::jushModule());echo($qh?script("addEventListener('DOMContentLoaded', () => {\n$qh\n});"):"");if(support("sql")){echo"<script".nonce().">\n";if($S){$Ag=array();foreach($S
as$Q=>$T)$Ag[]=js_escape_re($Q);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$Ag).')(?!\$)\b/g',false);$xl=array("sql","check","event","procedure","trigger","view","type","table","processlist");if(support("routine")&&array_intersect_key($_GET,array_flip($xl))){foreach(routines()as$I)json_row(js_escape(ME).'function='.url_escape($I["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($I["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$W)echo"jushLinks.$W = jushLinks.".JUSH.";\n";if(array_intersect_key($_GET,array_flip(array("sql","check","event","procedure","trigger","view")))){$Bl=(isset($_GET["trigger"])?array('INSERT INTO','UPDATE','DELETE FROM'):(isset($_GET["check"])?array():(isset($_GET["view"])?array('SELECT'):null)));$Pa=Driver::jushAutocomplete($S,$Bl);echo($Pa?"addEventListener('DOMContentLoaded', () => { autocompleter = $Pa; });\n":"");}}echo"</script>\n";}echo
script("syntaxHighlighting('".doc_version()."', '".connection()->flavor."');");}function
databasesPrint($nh){if(support("single_db"))return;$h=adminer()->databases();if(DB&&$h&&!in_array(DB,$h))array_unshift($h,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$uc=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".lang(44)."'>".lang(87).": ".($h?html_select("db",array(""=>"")+group_system($h),DB,$uc):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".lang(25)."'".($h?" class='hidden'":"").">\n";if(support("scheme")){if($nh!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".lang(88).": ".html_select("ns",array(""=>"")+group_system(adminer()->schemas(),true),$_GET["ns"],$uc)."</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</p></form>\n";}function
menuActions(array$la,$nh){return$la;}function
tablesPrint(array$S){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($S
as$Q=>$O){$Q="$Q";$A=adminer()->tableName($O);if($A!=""&&!$O["dependent"])echo'<li><a href="'.h(ME).'select='.url_escape($Q).'"'.bold($_GET["select"]==$Q||$_GET["edit"]==$Q,"select hover")." title='".lang(50)."'>".lang(89)."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($Q).'"'.bold(in_array($Q,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($O)?"view":"structure"))." title='".lang(51)."'>$A</a>":"<span>$A</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($s){return
kill_process($s);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$drivers=array();var$driverFiles=array();var$error='';private$hooks=array();function
__construct($pj){$Yc=SqlDriver::$drivers;$Xe=" href='https://www.adminer.org/plugins/#use'".target_blank();if($pj===null){$pj=array();$Xa="adminer-plugins";if(is_dir($Xa)){foreach(glob("$Xa/*.php")as$n){$Wd=SqlDriver::$drivers;$this->includeOnce($n);foreach(array_diff_key(SqlDriver::$drivers,$Wd)as$s=>$A)$this->driverFiles[$s]=$n;}}if(file_exists("$Xa.php")){$sf=$this->includeOnce("$Xa.php");if(is_array($sf)){foreach($sf
as$w=>$mj)$pj[is_object($mj)?get_class($mj):$w]=$mj;}else$this->error
.=lang(90,"<b>$Xa.php</b>",$Xe)."<br>";}foreach(get_declared_classes()as$zb){if(!$pj[$zb]&&(preg_match('~^Adminer\w~i',$zb)||is_subclass_of($zb,'Adminer\Plugin'))){$Xj=new
\ReflectionClass($zb);$Wb=$Xj->getConstructor();if($Wb&&$Wb->getNumberOfRequiredParameters())$this->error
.=lang(91,$Xe,"<b>$zb</b>","<b>$Xa.php</b>")."<br>";else$pj[$zb]=new$zb;}}}$Lf=array_filter($pj,function($mj){return!is_object($mj);});if($Lf){$this->error
.=lang(92,$Xe)."<br>";$pj=array_diff_key($pj,$Lf);}$this->drivers=array_diff_key(SqlDriver::$drivers,$Yc);$this->plugins=$pj;$qa=new
Adminer;$pj[]=$qa;$Xj=new
\ReflectionObject($qa);foreach($Xj->getMethods()as$kh){foreach($pj
as$mj){$A=$kh->getName();if(method_exists($mj,$A))$this->hooks[$A][]=$mj;}}}function
includeOnce($n){return
include_once"./$n";}static
function
checksum($n){$Vd=str_replace("\r","",file_get_contents($n));$Vd=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$Vd);return
dechex(crc32($Vd));}function
checksums(){$Xd=array_values($this->driverFiles);foreach($this->plugins
as$mj){$Xj=new
\ReflectionObject($mj);$Xd[]=$Xj->getFileName();}$H=array();foreach($Xd
as$n)$H[basename($n,'.php')]=self::checksum($n);return$H;}static
function
officialChecksums(){return
array('adminer.js'=>'a0599090','backward-keys'=>'e65981f5','before-unload'=>'2a613523','config'=>'722eb4af','dark-switcher'=>'3d490dea','database-hide'=>'e304a899','designs'=>'ed7e44e3','dump-alter'=>'896b579e','dump-bz2'=>'f0d0e336','dump-date'=>'adc7f1c7','dump-json'=>'767dd321','dump-xml'=>'4fc3cd60','dump-zip'=>'93817d96','edit-foreign'=>'72ad1562','edit-textarea'=>'a24c3cc','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'1e24970e','file-upload'=>'10add0e8','foreign-system'=>'ebb4c654','frames'=>'b0e1d11a','highlight-codemirror'=>'c5716555','highlight-monaco'=>'edd1b0af','highlight-prism'=>'267948e5','import-csv'=>'d429c77','login-ip'=>'4d174fea','login-otp'=>'5b5a68af','login-passkey'=>'f69f2f06','login-password-less'=>'e150daac','login-reverse-proxy'=>'24558ea2','login-servers'=>'19c42e45','login-ssl'=>'6ed147bc','login-table'=>'811f8cef','menu-links'=>'c78461b3','name-patterns'=>'84c10d09','remote-color'=>'ddeecc48','row-numbers'=>'eec8698c','select-email'=>'f84fbd2c','select-foreign'=>'fe3e58c8','select-image'=>'f55c0231','slugify'=>'dec64713','sql-gemini'=>'c60ab309','sql-log'=>'8e435000','table-indexes-structure'=>'a90cc0c9','table-structure'=>'a8458e02','tables-filter'=>'ec2bcd6e','timeout'=>'97321caf','version-github'=>'627cadf9','version-noverify'=>'966937e9','clickhouse'=>'92ca960d','elastic'=>'1582a04d','firebird'=>'1cccfc19','igdb'=>'4063cc0b','imap'=>'3da1022b','mongo'=>'63486492','redis'=>'79824392','simpledb'=>'b8e2cc7d',);}function
__call($A,array$Qi){$Fa=array();foreach($Qi
as$w=>$W)$Fa[]=&$Qi[$w];$H=null;foreach($this->hooks[$A]as$mj){$X=call_user_func_array(array($mj,$A),$Fa);if($X!==null){if(!self::$append[$A])return$X;$H=$X+(array)$H;}}return$H;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($t,$Th=null){$Fa=func_get_args();$Fa[0]=idx($this->translations[LANG],$t)?:$t;return
call_user_func_array('Adminer\lang_format',$Fa);}}class
Password{private$password_hash;private$password_matches=null;function
__construct($dj){$this->password_hash=$dj;}function
description(){return
lang(93);}function
credentials(){$D=get_password();return
array(SERVER,$_GET["username"],($this->passwordMatches($D)&&!password_required()?"":$D));}function
login($Fg,$D){if($this->passwordMatches($D))return
true;}protected
function
passwordMatches($D){if($this->password_matches===null)$this->password_matches=(function_exists('password_verify')&&password_verify(strval($D),$this->password_hash));return$this->password_matches;}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\mysqli{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach(array$M,$U,$D){mysqli_report(MYSQLI_REPORT_OFF);$qj=$M["port"];$nd=("$M[host]$qj$M[socket]"=="");$zl=adminer()->connectSsl();$qn=($zl&&($zl['key']||$zl['cert']||$zl['ca']||isset($zl['verify'])));if($qn)$this->ssl_set($zl['key'],$zl['cert'],$zl['ca'],'','');$H=@$this->real_connect((!$nd?$M["host"]:ini_get("mysqli.default_host")),(!$nd||$U!=""?$U:ini_get("mysqli.default_user")),(!$nd||$U.$D!=""?$D:ini_get("mysqli.default_pw")),null,($qj!=""?intval($qj):ini_get("mysqli.default_port")),($qj!=""?null:$M["socket"]),($qn?($zl['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($H?'':$this->error);}function
set_charset($ob){if(parent::set_charset($ob))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $ob");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($P){return"'".$this->escape_string($P)."'";}function
inTransaction(){return
false;}function
begin(){return$this->begin_transaction();}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach(array$M,$U,$D){if(ini_bool("mysql.allow_local_infile"))return
lang(94,"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$qj="$M[port]$M[socket]";$A=$M["host"].($qj!=""?":$qj":"");$this->link=@mysql_connect(($A!=""?$A:ini_get("mysql.default_host")),($A.$U!=""?$U:ini_get("mysql.default_user")),($A.$U.$D!=""?$D:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($ob){return
mysql_set_charset($ob,$this->link)||mysql_set_charset('utf8',$this->link);}function
quote($P){return"'".mysql_real_escape_string($P,$this->link)."'";}function
select_db($tc){return
mysql_select_db($tc,$this->link);}function
query($F,$Vm=false){$G=@($Vm?mysql_unbuffered_query($F,$this->link):mysql_query($F,$this->link));$this->error="";if(!$G){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($G===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($G);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($G){$this->result=$G;$this->num_rows=mysql_num_rows($G);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$H=mysql_fetch_field($this->result,$this->offset++);$H->orgtable=$H->table;$H->native_type=idx(array("string"=>"varchar","real"=>"double"),$H->type,$H->type);return$H;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach(array$M,$U,$D){$B=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);if(isset($_GET["select"]))$B[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$zl=adminer()->connectSsl();if($zl){if($zl['key'])$B[\PDO::MYSQL_ATTR_SSL_KEY]=$zl['key'];if($zl['cert'])$B[\PDO::MYSQL_ATTR_SSL_CERT]=$zl['cert'];if($zl['ca'])$B[\PDO::MYSQL_ATTR_SSL_CA]=$zl['ca'];if(isset($zl['verify']))$B[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$zl['verify'];}$ef=$M["host"];$qj=$M["port"];$ll=$M["socket"];return$this->dsn("mysql:charset=utf8".($ef!=""?";host=$ef":'').($qj!=""?";port=$qj":($ll!=""?";unix_socket=$ll":"")),$U,$D,$B);}function
set_charset($ob){return$this->query("SET NAMES $ob");}function
select_db($tc){return$this->query("USE ".idf_escape($tc));}function
query($F,$Vm=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Vm);return
parent::query($F,$Vm);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";static$serverSocket=true;var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");var$partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");function
operators($Pl){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$D){$f=parent::connect($M,$U,$D);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($yk=iconv("windows-1252","utf-8//IGNORE",$f))>strlen($f))$f=$yk;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array(lang(29)=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),lang(30)=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),lang(31)=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),lang(95)=>array("enum"=>65535,"set"=>64),lang(32)=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),lang(34)=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types[lang(31)]["json"]=4294967295;if(min_version('',10.7,$f)){$this->types[lang(31)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$f)){$this->types[lang(33)]["inet6"]=39;if(min_version('','10.10',$f))$this->types[lang(33)]["inet4"]=15;}if(min_version(9,11.7,$f))$this->types[lang(29)]["vector"]=16383;if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$l){return(preg_match("~binary~",$l["type"])?"<code class='jush-sql'>UNHEX</code>":($l["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):($l["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geom|point|linestring|polygon~",$l["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($Q,array$N){return($N?parent::insert($Q,$N):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
insertUpdate($Q,array$J,array$Cj){$e=array_keys(reset($J));$yj="INSERT INTO ".table($Q)." (".implode(", ",$e).") VALUES\n";$Y=array();foreach($e
as$w)$Y[$w]="$w = VALUES($w)";$Il="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=array();$x=0;foreach($J
as$N){$X="(".implode(", ",$N).")";if($Y&&(strlen($yj)+$x+strlen($X)+strlen($Il)>1e6)){if(!queries($yj.implode(",\n",$Y).$Il))return
false;$Y=array();$x=0;}$Y[]=$X;$x+=strlen($X)+2;}return
queries($yj.implode(",\n",$Y).$Il);}function
slowQuery($F,$rm){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$rm FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$_))return"$_[1] /*+ MAX_EXECUTION_TIME(".($rm*1000).") */ $_[2]";}}function
convertColumn($t,array$l){if(preg_match("~binary~",$l["type"]))return"HEX($t)";if($l["type"]=="bit")return"BIN($t + 0)";if($l["type"]=="vector")return($this->conn->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."($t)";if(preg_match("~geom|point|linestring|polygon~",$l["type"]))return(min_version(8)?"ST_":"")."AsWKT($t)";return"";}function
convertSearch($t,array$W,array$l){return($this->convertColumn($t,$l)?:(preg_match('~'.text_type().'~',$l["type"])&&!preg_match("~^utf8~",$l["collation"])&&preg_match('~[\x80-\xFF]~',$W['val'])?"CONVERT($t USING ".charset($this->conn).")":$t));}function
typeName(\stdClass$l){$A=parent::typeName($l);if($A!=""){$Um=array("TINY"=>"tinyint","SHORT"=>"smallint","LONG"=>"int","INT24"=>"mediumint","LONGLONG"=>"bigint","NEWDECIMAL"=>"decimal","VAR_STRING"=>"varchar","STRING"=>"char",);return
idx($Um,$A,strtolower($A));}$Um=array("decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",);$H=idx($Um,$l->type,"");return($l->charsetnr==63?str_replace(array("text","varchar","char"),array("blob","varbinary","binary"),$H):$H);}function
quoteBinary($yk){return"X".q(bin2hex($yk));}function
md5($d,array$l){if(is_blob($l)||preg_match('~'.text_type().'~',$l["type"]))return"MD5(".(is_blob($l)||preg_match("~^utf8~",$l["collation"])?$d:"CONVERT($d USING ".charset($this->conn).")").")";}function
warnings(){$G=$this->conn->query("SHOW WARNINGS");if($G&&$G->num_rows){ob_start();print_select_result($G);return
ob_get_clean();}}function
tableHelp($A,$Vf=false){$Hg=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($Hg?"$A-table/":str_replace("_","-",$A)."-table.html"));if(DB=="sys")return($Hg?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$A)).".html"));if(DB=="mysql")return($Hg?"mysql$A-table/":"system-schema.html");}function
partitionsInfo($Q){$se="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$G=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $se ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$I=($G?$G->fetch_row():null);if(!$I)return
array();$H=array();list($H["partition_by"],$H["partition"],$H["partitions"])=$I;$Zi=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $se AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$H["partition_names"]=array_keys($Zi);$H["partition_values"]=array_values($Zi);return$H;}function
checkConstraints($Q){$H=parent::checkConstraints($Q);return($this->conn->flavor=='maria'?$H:array_map('stripslashes',$H));}function
hasCStyleEscapes(){static$hb;if($hb===null){$wl=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$hb=(strpos($wl,'NO_BACKSLASH_ESCAPES')===false);}return$hb;}function
hasEstimatedRows(){return
true;}function
isSystem($i,$K=""){return
information_schema($i,$K)||in_array($i,array("mysql","sys"));}function
lineComment(){return"#|-- ";}function
engines(){$H=array();foreach(get_rows("SHOW ENGINES")as$I){if(preg_match("~YES|DEFAULT~",$I["Support"]))$H[]=$I["Engine"];}return$H;}function
indexAlgorithms(array$Pl){return(preg_match('~^(MEMORY|NDB)$~',$Pl["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($t){return"`".str_replace("`","``",$t)."`";}function
table($t){return
idf_escape($t);}function
get_databases($he){$H=get_session("dbs");if($H===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$_l=microtime(true);$H=($he?slow_query($F):get_vals($F));if(microtime(true)-$_l>0.1){restart_session();set_session("dbs",$H);stop_session();}}return$H;}function
limit($F,$Z,$y,$bi=0,$Ok=" "){return" $F$Z".($y?$Ok."LIMIT $y".($bi?" OFFSET $bi":""):"");}function
limit1($Q,$F,$Z,$Ok="\n"){return
limit($F,$Z,1,0,$Ok);}function
db_collation($i,array$Eb){$H=null;$gc=get_val("SHOW CREATE DATABASE ".idf_escape($i),1);if(preg_match('~ COLLATE ([^ ]+)~',$gc,$_))$H=$_[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$gc,$_))$H=$Eb[$_[1]][-1];return$H;}function
logged_user(){return
get_val("SELECT CURRENT_USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$h){$H=array();foreach($h
as$i)$H[$i]=count(get_vals("SHOW TABLES IN ".idf_escape($i)));return$H;}function
table_status($A="",$Od=false){$H=array();$F="SELECT ENGINE AS Engine, TABLE_NAME AS Name, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($A!=""?"AND TABLE_NAME = ".q($A):"ORDER BY Name");$K=array();foreach(($Od?array():get_rows($F))as$I)$K[$I["Name"]]=$I;$Bj=null;foreach(get_rows($Od?$F:"SHOW TABLE STATUS".($A!=""?" LIKE ".q(addcslashes($A,"%_\\")):""))as$I){$Fi=idx($K,$I["Name"]);if($Fi){if($I["Comment"]!==$Fi["Comment"]&&$I["Comment"]!==$Bj)$I["Error"]=$I["Comment"];$Bj=$I["Comment"];$I["Comment"]=$Fi["Comment"];$I["Engine"]=$Fi["Engine"];}if($I["Engine"]=="InnoDB")$I["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$I["Comment"]);if(!isset($I["Engine"]))$I["Comment"]="";if($A!="")$I["Name"]=$A;$H[$I["Name"]]=$I;}return$H;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support(array$R){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$R["Engine"]);}function
parse_type($ve){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$ve,$_);return
array($_[1],$_[2],ltrim($_[3].$_[4]));}function
fields($Q){$Hg=(connection()->flavor=='maria');$H=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$I){$l=$I["COLUMN_NAME"];$T=$I["COLUMN_TYPE"];$_e=$I["GENERATION_EXPRESSION"];$Ld=$I["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Ld,$ze);list($Tm,$x,$en)=parse_type($T);$j=$I["COLUMN_DEFAULT"];if($j!=""){$Uf=preg_match('~text|json~',$Tm);if(!$Hg&&$Uf)$j=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($j));if($Hg||$Uf){$j=($j=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($_){return
stripslashes(str_replace("''","'",$_[1]));},$j));}if(!$Hg&&preg_match('~binary~',$Tm)&&preg_match('~^0x(\w*)$~',$j,$_))$j=pack("H*",$_[1]);}$H[$l]=array("field"=>$l,"full_type"=>$T,"type"=>$Tm,"length"=>$x,"unsigned"=>$en,"default"=>($ze?($Hg?$_e:stripslashes($_e)):$j),"null"=>($I["IS_NULLABLE"]=="YES"),"auto_increment"=>($Ld=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Ld,$_)?$_[1]:""),"collation"=>$I["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$I[PRIVILEGES],where,order")),"comment"=>$I["COLUMN_COMMENT"],"primary"=>($I["COLUMN_KEY"]=="PRI"),"generated"=>($ze[1]=="PERSISTENT"?"STORED":$ze[1]),);}return$H;}function
indexes($Q,$g=null){$H=array();foreach(get_rows("SHOW INDEX FROM ".table($Q),$g)as$I){$A=$I["Key_name"];$H[$A]["type"]=($A=="PRIMARY"?"PRIMARY":($I["Index_type"]=="FULLTEXT"?"FULLTEXT":($I["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$I["Index_type"])?$I["Index_type"]:"INDEX"):"UNIQUE")));$H[$A]["columns"][]=$I["Column_name"];$H[$A]["lengths"][]=($I["Index_type"]=="SPATIAL"?null:$I["Sub_part"]);$H[$A]["descs"][]=null;$H[$A]["algorithm"]=$I["Index_type"];}return$H;}function
foreign_keys($Q){static$hj='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$H=array();$hc=get_val("SHOW CREATE TABLE ".table($Q),1);if($hc){preg_match_all("~CONSTRAINT ($hj) FOREIGN KEY ?\\(((?:$hj,? ?)+)\\) REFERENCES ($hj)(?:\\.($hj))? \\(((?:$hj,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$hc,$Kg,PREG_SET_ORDER);foreach($Kg
as$_){preg_match_all("~$hj~",$_[2],$pl);preg_match_all("~$hj~",$_[5],$hm);$H[idf_unescape($_[1])]=array("db"=>idf_unescape($_[4]!=""?$_[3]:$_[4]),"table"=>idf_unescape($_[4]!=""?$_[4]:$_[3]),"source"=>array_map('Adminer\idf_unescape',$pl[0]),"target"=>array_map('Adminer\idf_unescape',$hm[0]),"on_delete"=>($_[6]?:"RESTRICT"),"on_update"=>($_[7]?:"RESTRICT"),);}}return$H;}function
view($A){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($A),1)));}function
collations(){$H=array();foreach(get_rows("SHOW COLLATION")as$I){if($I["Default"])$H[$I["Charset"]][-1]=$I["Collation"];else$H[$I["Charset"]][]=$I["Collation"];}ksort($H);foreach($H
as$w=>$W)sort($H[$w]);return$H;}function
information_schema($i,$K=""){return($i=="information_schema")||(min_version(5.5)&&$i=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($i,$Db){return
queries("CREATE DATABASE ".idf_escape($i).($Db?" COLLATE ".q($Db):""));}function
drop_databases(array$h){$H=apply_queries("DROP DATABASE",$h,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$H;}function
rename_database($A,$Db){$H=false;if(create_database($A,$Db)){$S=array();$Dn=array();foreach(tables_list()as$Q=>$T){if($T=='VIEW')$Dn[]=$Q;else$S[]=$Q;}$H=(!$S&&!$Dn)||move_tables($S,$Dn,$A);drop_databases($H?array(DB):array());}return$H;}function
auto_increment(){$Oa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$u){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$u["columns"],true)){$Oa="";break;}if($u["type"]=="PRIMARY")$Oa=" UNIQUE";}}return" AUTO_INCREMENT$Oa";}function
alter_table($Q,$A,array$m,array$je,$Jb,$pd,$Db,$Na,$Yi){$b=array();foreach($m
as$l){if($l[1]){$j=$l[1][3];if(preg_match('~ GENERATED~',$j)){$l[1][3]=(connection()->flavor=='maria'?"":$l[1][2]);$l[1][2]=$j;}$b[]=($Q!=""?($l[0]!=""?"CHANGE ".idf_escape($l[0]):"ADD"):" ")." ".implode($l[1]).($Q!=""?$l[2]:"");}else$b[]="DROP ".idf_escape($l[0]);}$b=array_merge($b,$je);$O=($Jb!==null?" COMMENT=".q($Jb):"").($pd?" ENGINE=".q($pd):"").($Db?" COLLATE ".q($Db):"").($Na!=""?" AUTO_INCREMENT=$Na":"");if($Yi){$Zi=array();if($Yi["partition_by"]=='RANGE'||$Yi["partition_by"]=='LIST'){foreach($Yi["partition_names"]as$w=>$W){$X=$Yi["partition_values"][$w];$Zi[]="\n  PARTITION ".idf_escape($W)." VALUES ".($Yi["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$O
.="\nPARTITION BY $Yi[partition_by]($Yi[partition])";if($Zi)$O
.=" (".implode(",",$Zi)."\n)";elseif($Yi["partitions"])$O
.=" PARTITIONS ".(+$Yi["partitions"]);}elseif($Yi===null)$O
.="\nREMOVE PARTITIONING";if($Q=="")return
queries("CREATE TABLE ".table($A)." (\n".implode(",\n",$b)."\n)$O");if($Q!=$A)$b[]="RENAME TO ".table($A);if($O)$b[]=ltrim($O);return($b?queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b)):true);}function
alter_indexes($Q,$b){$mb=array();foreach($b
as$W)$mb[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return
queries("ALTER TABLE ".table($Q).implode(",",$mb));}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$Dn){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Dn)));}function
drop_tables(array$S){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$S)));}function
move_tables(array$S,array$Dn,$hm){$dk=array();foreach($S
as$Q)$dk[]=table($Q)." TO ".idf_escape($hm).".".table($Q);if(!$dk||queries("RENAME TABLE ".implode(", ",$dk))){$Dc=array();foreach($Dn
as$Q)$Dc[table($Q)]=view($Q);connection()->select_db($hm);$i=idf_escape(DB);foreach($Dc
as$A=>$Cn){if(!queries("CREATE VIEW $A AS ".str_replace(" $i."," ",$Cn["select"]))||!queries("DROP VIEW $i.$A"))return
false;}return
true;}return
false;}function
copy_tables(array$S,array$Dn,$hm){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$A=($hm==DB?table("copy_$Q"):idf_escape($hm).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $A"))||!queries("CREATE TABLE $A LIKE ".table($Q))||!queries("INSERT INTO $A SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$I){$Jm=$I["Trigger"];list($yd,$Wh)=trigger_event($I);if(!queries("CREATE TRIGGER ".($hm==DB?idf_escape("copy_$Jm"):idf_escape($hm).".".idf_escape($Jm))." $I[Timing] $yd".($Wh!=""?" $Wh":"")." ON $A FOR EACH ROW\n$I[Statement];"))return
false;}}foreach($Dn
as$Q){$A=($hm==DB?table("copy_$Q"):idf_escape($hm).".".table($Q));$Cn=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $A"))||!queries("CREATE VIEW $A AS $Cn[select]"))return
false;}return
true;}function
trigger_event(array$I){$_d=explode(",",$I["Event"]);$H=array();foreach(array("DELETE","INSERT","UPDATE")as$yd){if(in_array($yd,$_d))$H[]=$yd;}$H=implode(" OR ",$H);if(in_array("UPDATE",$_d)&&min_version('','12.0.1')&&preg_match('~\s(?:BEFORE|AFTER)\s+(.+?)\s+ON\s~is',get_val("SHOW CREATE TRIGGER ".idf_escape($I["Trigger"]),2),$_)&&preg_match('~\bOF\s+(.+)~is',$_[1],$Wh))return
array("$H OF",$Wh[1]);return
array($H,"");}function
trigger($A,$Q){if($A=="")return
array();$J=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($A));$H=reset($J);if($H)list($H["Event"],$H["Of"])=trigger_event($H);return($H?:array());}function
triggers($Q){$H=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$I){list($yd)=trigger_event($I);$H[$I["Trigger"]]=array($I["Timing"],$yd);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>(min_version('','12.0.1')?array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",):array("INSERT","UPDATE","DELETE")),"Type"=>array("FOR EACH ROW"),);}function
routine($A,$T){$J=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, COLLATION_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND SPECIFIC_NAME = ".q($A)."
ORDER BY ORDINAL_POSITION");$m=array();foreach($J
as$I){$ve=$I["DTD_IDENTIFIER"];list($Tm,$x,$en)=parse_type($ve);$m[]=array("field"=>$I["PARAMETER_NAME"],"type"=>$Tm,"length"=>$x,"unsigned"=>$en,"null"=>true,"full_type"=>$ve,"inout"=>($T=="FUNCTION"?"":$I["PARAMETER_MODE"]),"collation"=>$I["COLLATION_NAME"],);}$H=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	ROUTINE_DEFINITION definition,
	LOWER(EXTERNAL_LANGUAGE) language,
	IF(DEFINER = CURRENT_USER(), '', DEFINER) definer,
	IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC', 'NOT DETERMINISTIC') is_deterministic,
	SQL_DATA_ACCESS data_access,
	CONCAT('SQL SECURITY ', SECURITY_TYPE) security
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($A))->fetch_assoc();if(!$H)return
array();$H['options']=array("DEFINER"=>$H['definer'],"DETERMINISTIC"=>$H['is_deterministic'],"SQL_DATA_ACCESS"=>$H['data_access'],"SQL_SECURITY"=>$H['security'],"COMMENT"=>$H['comment'],);if($m&&$m[0]['field']=='')$H['returns']=array_shift($m);$H['fields']=$m;return$H;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return(min_version(9,99)?array("sql"=>"sql","javascript"=>"js"):array());}function
routine_options($pk){return
array("DEFINER"=>array(),"DETERMINISTIC"=>array("NOT DETERMINISTIC","DETERMINISTIC"),"SQL_DATA_ACCESS"=>array("CONTAINS SQL","NO SQL","READS SQL DATA","MODIFIES SQL DATA"),"SQL_SECURITY"=>array("SQL SECURITY DEFINER","SQL SECURITY INVOKER"),"COMMENT"=>array(),);}function
routine_id($A,array$I){return
idf_escape($A);}function
last_id($G){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$F){return$f->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$F);}function
found_rows(array$R,array$Z){return($Z||$R["Engine"]!="InnoDB"?null:$R["Rows"]);}function
create_sql($Q,$Na,$Gl){$H=get_val("SHOW CREATE TABLE ".table($Q),1);if(!$Na)$H=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$H);return$H;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
use_sql($tc,$Gl=""){$A=idf_escape($tc);$H="";if(preg_match('~CREATE~',$Gl)&&($gc=get_val("SHOW CREATE DATABASE $A",1))){set_utf8mb4($gc);if($Gl=="DROP+CREATE")$H="DROP DATABASE IF EXISTS $A;\n";$H
.="$gc;\n";}return$H."USE $A";}function
trigger_sql($Q){$H="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$I){list($I["Event"],$I["Of"])=trigger_event($I);$H
.="\n".create_trigger(" ON ".table($I["Table"]),$I+array("Type"=>"FOR EACH ROW")).";\n";}return$H;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$l){return
driver()->convertColumn(idf_escape($l["field"]),$l);}function
unconvert_field(array$l,$H){if(preg_match("~binary~",$l["type"]))$H="UNHEX($H)";if($l["type"]=="bit")$H="CONVERT(b$H, UNSIGNED)";if($l["type"]=="vector")$H=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($H)";if(preg_match("~geom|point|linestring|polygon~",$l["type"])){$yj=(min_version(8)?"ST_":"");$H=$yj."GeomFromText($H, $yj"."SRID($l[field]))";}return$H;}function
support($Pd){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$Pd);}function
kill_process($s){return
queries("KILL ".number($s));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($Kd=false){return
array();}function
type_values($s){return"";}function
type_definition($s){return
array("kind"=>"","definition"=>"");}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($K,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(isset($_GET["db"])?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));if(isset($_GET["manifest"])){header("Content-Type: application/manifest+json; charset=utf-8");header("Cache-Control: no-cache");echo
json_encode(adminer()->manifest(),64|256);exit;}function
page_header($tm,$k="",$db=array(),$um="",$Ph=false,$Uc=""){if($Ph){header("HTTP/1.1 404 Not Found");$k=($k?:lang(96));}page_headers();if(is_ajax()&&$k){page_messages($k);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$vm=$tm.($um!=""?": $um":"");$wm=strip_tags($vm.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'',LANG,'\' dir=\'',lang(97),'\' class=\'',lang(97),' nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$wm,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.1.1+43f4678f"),'">
';$lc=adminer()->css();if(is_int(key($lc)))$lc=array_fill_keys($lc,'light');$Oe=in_array('light',$lc)||in_array('',$lc);$Me=in_array('dark',$lc)||in_array('',$lc);$pc=($Oe?($Me?null:false):($Me?:null));$Zg=" media='(prefers-color-scheme: dark)'";if($pc!==false)echo"<link rel='stylesheet'".($pc?"":$Zg)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.1.1+43f4678f")."'>\n";echo"<meta name='color-scheme' content='".($pc===null?"light dark":($pc?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.1.1+43f4678f");if(adminer()->head($pc))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.1.1+43f4678f")."'>\n";if(adminer()->manifest())echo"<link rel='manifest' href='".h(preg_replace('~\?.*~','',ME)."?manifest=")."' crossorigin='use-credentials'>\n";foreach($lc
as$ln=>$oh){$c=($oh=='dark'&&!$pc?$Zg:($oh=='light'&&$Me?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$c href='".h($ln)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape(lang(98))."';
const numberFormat = '".js_escape(lang(6))."';
const numberDigits = '".js_escape(lang(7))."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".lang(99)."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($db!==null){$z=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($z?:".").'">'.get_driver(DRIVER).'</a> » ';$z=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$M=adminer()->serverName(SERVER);$M=($M!=""?$M:lang(40));if($db===false)echo"$M\n";else{echo"<a href='".h($z.(DB!=""&&support("single_db")?"&db=":""))."' accesskey='1' title='Alt+Shift+1'>$M</a> » ";$Fk="";if(is_string($db)){$Fk=$db;$db=array();}if($_GET["ns"]!=""||(DB!=""&&is_array($db))){$vc="$z&db=".url_escape(DB).(support("scheme")?"&ns=":"").(support("single_table")?"&select=":"");echo'<a href="'.h($vc.($_GET["ns"]==""?$Fk:"")).'">'.h(DB).'</a> » ';}if(is_array($db)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1).$Fk).'">'.h($_GET["ns"]).'</a> » ';foreach($db
as$w=>$W){$Fc=(is_array($W)?$W[1]:h($W));if($Fc!="")echo"<a href='".h(ME."$w=").url_escape(is_array($W)?$W[0]:$W)."'>$Fc</a> » ";}}echo"$tm\n";}}echo"<h2>$vm$Uc</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($k);adminer()->serviceWorker();$h=&get_session("dbs");if(DB!=""&&$h&&!in_array(DB,$h,true))$h=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();if($Ph){page_footer($Ph===true?"":$Ph);exit;}}function
service_worker(){$ak=has_passwords();$Ab=($ak?"navigator.serviceWorker.register('".js_escape(preg_replace('~\?.*~','',ME)."?file=worker.js&version=6.1.1+43f4678f")."', {scope: location.pathname}).catch(() => {});":"navigator.serviceWorker.getRegistration().then(registration => registration && registration.unregister());
	caches.keys().then(keys => keys.forEach(key => key.startsWith('adminer-') && caches.delete(key)));");echo
script("if (navigator.serviceWorker) {\n\t$Ab\n}");}function
has_passwords(){foreach((array)$_SESSION["pwds"]as$bl){foreach($bl
as$un){foreach($un
as$D){if($D!==null)return
true;}}}return
false;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$kc){$Te=array();foreach($kc
as$w=>$W)$Te[]="$w $W";header("Content-Security-Policy: ".implode("; ",$Te));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
design_checksums(){$rn=array();foreach(array_keys(adminer()->css())as$ln)$rn[preg_replace('~\?.*~','',$ln)]=true;$H=array();foreach(array("adminer.css","adminer-dark.css")as$n){if($rn[$n]&&file_exists($n)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($n),$_);$H[$n]=array((string)$_[1],Plugins::checksum($n));}}return$H;}function
official_design_checksums(){return
array('adminer-border/adminer.css'=>'ec757f3e','adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','cpanel/adminer.css'=>'59ce604e','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'ef0e1948','konya/adminer.css'=>'2b409696','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'e1ac813d','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'5ab360e7','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'216f097b','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'81be9a85','rmsoft/adminer.css'=>'6cd4a237','rmsoft_blue-dark/adminer.css'=>'32102a8','rmsoft_blue/adminer.css'=>'7d8d5b18','win98/adminer.css'=>'e82d63c3',);}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$Oh;if(!$Oh)$Oh=base64_encode(rand_string());return$Oh;}function
page_messages($k){$kn=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$gh=idx($_SESSION["messages"],$kn);if($gh){echo"<div class='message'>".implode("</div>\n<div class='message'>",$gh)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$kn]);}if($k)echo"<div class='error'>$k</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($nh=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($nh);echo"</div>\n";if($nh!="auth")echo'<form action="" method="post">
<p class="logout">
<span title="',lang(42),'">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'',lang(100),'\' id=\'logout\'>
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($wh){while($wh>=2147483648)$wh-=4294967296;while($wh<=-2147483649)$wh+=4294967296;return(int)$wh;}function
long2str(array$V,$Fn){$yk='';foreach($V
as$W)$yk
.=pack('V',$W);if($Fn)return
substr($yk,0,end($V));return$yk;}function
str2long($yk,$Fn){$V=array_values(unpack('V*',str_pad($yk,4*ceil(strlen($yk)/4),"\0")));if($Fn)$V[]=strlen($yk);return$V;}function
xxtea_mx($Qn,$Pn,$Jl,$ag){return
int32((($Qn>>5&0x7FFFFFF)^$Pn<<2)+(($Pn>>3&0x1FFFFFFF)^$Qn<<4))^int32(($Jl^$Pn)+($ag^$Qn));}function
encrypt_string($Dl,$w){if($Dl=="")return"";$w=array_values(unpack("V*",pack("H*",md5($w))));$V=str2long($Dl,true);$wh=count($V)-1;$Qn=$V[$wh];$Pn=$V[0];$Lj=floor(6+52/($wh+1));$Jl=0;while($Lj-->0){$Jl=int32($Jl+0x9E3779B9);$fd=$Jl>>2&3;for($Li=0;$Li<$wh;$Li++){$Pn=$V[$Li+1];$vh=xxtea_mx($Qn,$Pn,$Jl,$w[$Li&3^$fd]);$Qn=int32($V[$Li]+$vh);$V[$Li]=$Qn;}$Pn=$V[0];$vh=xxtea_mx($Qn,$Pn,$Jl,$w[$Li&3^$fd]);$Qn=int32($V[$wh]+$vh);$V[$wh]=$Qn;}return
long2str($V,false);}function
decrypt_string($Dl,$w){if($Dl=="")return"";if(!$w)return
false;$w=array_values(unpack("V*",pack("H*",md5($w))));$V=str2long($Dl,false);$wh=count($V)-1;$Qn=$V[$wh];$Pn=$V[0];$Lj=floor(6+52/($wh+1));$Jl=int32($Lj*0x9E3779B9);while($Jl){$fd=$Jl>>2&3;for($Li=$wh;$Li>0;$Li--){$Qn=$V[$Li-1];$vh=xxtea_mx($Qn,$Pn,$Jl,$w[$Li&3^$fd]);$Pn=int32($V[$Li]-$vh);$V[$Li]=$Pn;}$Qn=$V[$wh];$vh=xxtea_mx($Qn,$Pn,$Jl,$w[$Li&3^$fd]);$Pn=int32($V[0]-$vh);$V[0]=$Pn;$Jl=int32($Jl-0x9E3779B9);}return
long2str($V,true);}$kj=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$W){list($w)=explode(":",$W);$kj[$w]=$W;}}function
add_invalid_login(){$Va=get_temp_dir()."/adminer-invalid";foreach(glob("$Va*")?:array($Va)as$n){$pe=file_open_lock($n);if($pe)break;}if(!$pe)$pe=file_open_lock("$Va-".rand_string());if(!$pe)return;$Nf=json_decode(stream_get_contents($pe),true);$qm=time();if($Nf){foreach($Nf
as$Of=>$W){if($W[0]<$qm)unset($Nf[$Of]);}}$Lf=&$Nf[adminer()->bruteForceKey()];if(!$Lf)$Lf=array($qm+30*60,0);$Lf[1]++;file_write_unlock($pe,json_encode($Nf));}function
check_invalid_login(array&$kj){$Nf=array();foreach(glob(get_temp_dir()."/adminer-invalid*")as$n){$pe=file_open_lock($n);if($pe){$Nf=json_decode(stream_get_contents($pe),true);file_unlock($pe);break;}}$w=adminer()->bruteForceKey();$Lf=idx($Nf,$w,array());$Nh=($Lf[1]>29?$Lf[0]-time():0);if($Nh>0){$k=lang(101,ceil($Nh/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$w==$_SERVER["REMOTE_ADDR"])$k
.='<br>'.lang(102,'<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($k,$kj,false);}}function
password_required(){static$H;if($H===null){$H=(bool)get_session("password_required");if(!$H){$jc=adminer()->credentials();$H=!is_object(Driver::connect($jc[0],$jc[1],""));if($H)set_session("password_required",true);}}return$H;}function
require_password_link($D){$rh="<a href='https://www.adminer.org/password/'".target_blank().">".lang(103)."</a>";if(!function_exists('password_hash'))return" $rh";$nj=($D!==null?$D:base64_encode(substr(pack("H*",rand_string()),0,12)));$Se=password_hash($nj,PASSWORD_DEFAULT);$n="adminer-plugins.php";$Ed=file_exists("adminer-plugins.php");if($Ed)$Jf=($D!==null?lang(104,"<b>$n</b>"):lang(105,"<b>$n</b>","<b>$nj</b>"));else{$n="<button name='password_less' value='".h($Se)."' class='link'>$n</button>";$Jf=($D!==null?lang(106,$n):lang(107,$n,"<b>$nj</b>"));}$zg="\t<a>new</a> Adminer\\Password(<span class='jush-apo'>'".h($Se)."'</span>),";$H="<p>$Jf
<pre><code class='jush'>".($Ed?$zg:"&lt;?php\n<a>return</a> <a>array</a>(\n$zg\n);")."</code></pre>
<p>$rh
";return" <a href='#password-less' class='toggle'>".lang(108)."</a>
<div id='password-less' class='hidden'>".($Ed?$H:"<form action='' method='post'>\n".$H.input_token()."</form>")."</div>";}if(preg_match('~^[-\w$./]+$~',$_POST["password_less"])&&verify_token()){header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=adminer-plugins.php");echo"<?php\nreturn array(\n\tnew Adminer\\Password('$_POST[password_less]'),\n);\n";exit;}$Ma=$_POST["auth"];if($Ma&&(!adminer()->verifyLoginToken()||verify_token())){session_regenerate_id();$An=$Ma["driver"];$M=$Ma["server"];$U=$Ma["username"];$D=(string)$Ma["password"];$i=$Ma["db"];set_password($An,$M,$U,$D);$_SESSION["db"][$An][$M][$U][$i]=true;if($Ma["permanent"]){$w=implode("-",array_map('base64_encode',array($An,$M,$U,$i)));$Fj=adminer()->permanentLogin(true);$kj[$w]="$w:".base64_encode($Fj?encrypt_string($D,$Fj):"");cookie("adminer_permanent",implode(" ",$kj));}if(!array_diff(array_keys($_POST),array("auth","token"))||$An!=DRIVER||$M!=SERVER||$U!==$_GET["username"]||$i!=DB)redirect(auth_url($An,$M,$U,$i));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){Driver::disconnect();foreach(array("pwds","db","dbs","queries")as$w)set_session($w,null);unset_permanent($kj);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(109).' '.lang(110));}elseif($kj&&!$_SESSION["pwds"]){session_regenerate_id();$Fj=adminer()->permanentLogin();foreach($kj
as$w=>$W){list(,$yb)=explode(":",$W);list($An,$M,$U,$i)=array_map('base64_decode',explode("-",$w));set_password($An,$M,$U,decrypt_string(base64_decode($yb),$Fj));$_SESSION["db"][$An][$M][$U][$i]=true;}}function
unset_permanent(array&$kj){foreach($kj
as$w=>$W){list($An,$M,$U,$i)=array_map('base64_decode',explode("-",$w));if($An==DRIVER&&$M==SERVER&&$U==$_GET["username"]&&$i==DB)unset($kj[$w]);}cookie("adminer_permanent",implode(" ",$kj));}function
auth_error($k,array&$kj,$Mf=true){$cl=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$cl]||$_GET[$cl])&&!$_SESSION["token"])$k=lang(111);elseif($Mf&&($D=get_password())!==null){restart_session();add_invalid_login();if($D===false)$k
.=($k?'<br>':'').lang(112,target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);unset_permanent($kj);}}if(!$_COOKIE[$cl]&&$_GET[$cl]&&ini_bool("session.use_only_cookies"))$k=lang(113);$Qi=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$Qi["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(45),$k,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth","token")))echo"<p class='message'>".lang(114)."\n";echo
input_token(),"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($kj);page_header(lang(115),lang(116,implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){check_invalid_login($kj);$jc=adminer()->credentials();$f=Driver::connect($jc[0],$jc[1],$jc[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Fg=null;if(!is_object($f)||($Fg=adminer()->login($_GET["username"],get_password()))!==true){$k=(is_string($f)?nl_br(h($f)):(is_string($Fg)?$Fg:lang(117))).(preg_match('~^ | $~',get_password())?'<br>'.lang(118):'');auth_error($k,$kj);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header(lang(100),lang(119));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Ma&&$_POST["token"])$_POST["token"]=get_token();$k='';if($_POST){if(!verify_token()){header("HTTP/1.1 403 Forbidden");$k=lang(119).' '.lang(120);}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){header("HTTP/1.1 413 Content Too Large");$k=lang(121,"<b>post_max_size</b>");if(isset($_GET["sql"]))$k
.=' '.lang(122);}function
print_select_result($G,$g=null,array$_i=array(),&$y=0,&$gd=false){$Ag=array();$v=array();$e=array();$S=array();$Cj=array();$id=array();$Um=array();$H=array();$ph=$gd;$gd=false;for($r=0;(!$y||$r<$y)&&($I=$G->fetch_row());$r++){if(!$r){echo"<div class='scrollable'>\n","<table class='nowrap odds'".($ph?on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown'):"").">\n","<thead><tr>";for($Xf=0;$Xf<count($I);$Xf++){$l=$G->fetch_field();$A=$l->name;$Q=(isset($l->table)?$l->table:"");$zi=(isset($l->orgtable)?$l->orgtable:"");$yi=(isset($l->orgname)?$l->orgname:$A);$Tm=driver()->typeName($l);if($_i&&JUSH=="sql")$Ag[$Xf]=($A=="table"?"table=":($A=="possible_keys"?"indexes=":null));elseif($zi!=""){$wa=($Q!=""?$Q:$zi);if($Q!="")$H[$Q]=$zi;if(!isset($v[$wa])){if(!isset($Cj[$zi])){$Cj[$zi]=array();foreach(indexes($zi,$g)as$u){if($u["type"]=="PRIMARY"){$Cj[$zi]=array_flip($u["columns"]);break;}}}$S[$wa]=$zi;$v[$wa]=$Cj[$zi];$e[$wa]=$Cj[$zi];}if(isset($e[$wa][$yi])){unset($e[$wa][$yi]);$v[$wa][$yi]=$Xf;$Ag[$Xf]=$wa;}elseif($ph&&isset($l->orgname)&&$l->db==DB&&!is_blob(array("type"=>$Tm)))$id[$Xf]=array($wa,$yi,preg_match('~text|json|lob~',$Tm));}$Um[$Xf]=$Tm;echo"<th title='".h(trim(($zi!=""?"$zi.$yi":($l->name!=$yi?$yi:""))." ".$Tm))."'>".h($A).($_i?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($A),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}foreach($id
as$Xf=>$kb){if($e[$kb[0]])unset($id[$Xf]);}echo"<tbody>\n";}$lf=array();foreach($v
as$wa=>$u){if($u&&!$e[$wa]){$t="";foreach($u
as$Bb=>$Xf){if($I[$Xf]===null){$t=null;break;}$t
.="&where[".url_escape(bracket_escape($Bb))."]=".url_escape($I[$Xf]);}$lf[$wa]=$t;}}echo"<tr>";foreach($I
as$w=>$W){$z="";if(isset($Ag[$w])){if($_i&&JUSH=="sql"){$Q=$I[array_search("table=",$Ag)];$z=ME.$Ag[$w].url_escape($_i[$Q]!=""?$_i[$Q]:$Q);}elseif(idx($lf,$Ag[$w])!==null)$z=ME."edit=".url_escape($S[$Ag[$w]]).$lf[$Ag[$w]];}$c="";$kb=idx($id,$w);if($kb&&idx($lf,$kb[0])!==null&&is_utf8($W)){$gd=true;$c=" data-name='".h("val[".bracket_escape($S[$kb[0]])."][".bracket_escape(substr($lf[$kb[0]],1))."][".bracket_escape($kb[1])."]")."' data-text='".($kb[2]?1:0)."'";}$W=select_value($W,$z,array('type'=>(preg_match('~binary~',$Um[$w])?'blob':$Um[$w])),null);echo"<td".(preg_match(number_type(),$Um[$w])?" class='number'":"")."$c>$W";}}$y=$r;echo($r?"</table>\n</div>":"<p class='message'>".lang(16))."\n";return$H;}function
textarea($A,$X,$J=10,$Fb=80,$Zf=JUSH){echo"<textarea name='".h($A)."' rows='$J' cols='$Fb' class='sqlarea jush-".h($Zf)."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($c,array$B,$X="",$lj=""){if($B&&$X!=""&&!isset($B[$X]))$B=array($X=>$X)+$B;$gm=($B?"select":"input");return"<$gm$c".($B?"><option value=''>$lj".optionlist($B,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$lj'>");}function
json_row($w,$W=null,$xd=true){static$be=true;if($be)echo"{";if($w!=""){echo($be?"":",")."\n\t\"".addcslashes($w,"\r\n\t\"\\/").'": '.($W!==null?($xd?'"'.addcslashes($W,"\r\n\"\\/").'"':$W):'null');$be=false;}else{echo"\n}\n";$be=true;}}function
flat_collations(){$Eb=collations();return(is_array(reset($Eb))?call_user_func_array('array_merge',array_values($Eb)):$Eb);}function
edit_type($w,array$l,array$Eb,array$le=array(),array$Md=array()){$T=(string)$l["type"];echo"<td><select name='".h($w)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($T&&!array_key_exists($T,driver()->types())&&!isset($le[$T])&&!in_array($T,$Md))$Md[]=$T;$El=driver()->structuredTypes();if($le)$El[lang(123)]=$le;echo
optionlist(array_merge($Md,$El),$T),"</select><td>","<input name='".h($w)."[length]' value='".h($l["length"])."' size='3'".(!$l["length"]&&preg_match('~var(char|binary)$~',$T)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($Eb?"<input list='collations' name='".h($w)."[collation]'".option_types($T,'('.text_type().')$')." value='".h($l["collation"])."' placeholder='(".lang(124).")'>":''),(driver()->unsigned?"<select name='".h($w)."[unsigned]'".option_types($T,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$l["unsigned"]).'</select>':''),(isset($l['on_update'])?"<select name='".h($w)."[on_update]'".option_types($T,'timestamp|datetime').'>'.optionlist(array(""=>"(".lang(125).")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"CURRENT_TIMESTAMP":$l["on_update"])).'</select>':''),($le?"<select name='".h($w)."[on_delete]'".option_types($T,'`')."><option value=''>(".lang(126).")".optionlist(explode("|",driver()->onActions),$l["on_delete"])."</select> ":" ");}function
option_types($T,$Um){return" data-types='".h($Um)."'".(preg_match("~$Um~",$T)?"":" class='hidden'");}function
process_length($x){if(JUSH=="mssql"&&preg_match('~^\s*\(?\s*max\s*\)?\s*$~i',$x))return"(max)";$sd=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$sd(?:\\s*,\\s*$sd)*+\\s*\\)?\\s*\$~",$x)&&preg_match_all("~$sd~",$x,$Kg)?"(".implode(",",$Kg[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$x)));}function
process_in($W){$sd=driver()->enumLength;if(preg_match("~^\\s*\\(?\\s*$sd(?:\\s*,\\s*$sd)*+\\s*\\)?\\s*\$~",$W)&&preg_match_all("~$sd~",$W,$Kg))return"(".implode(", ",$Kg[0]).")";$H=array();foreach(explode(",",$W)as$Wf)$H[]=q(trim($Wf));return"(".implode(", ",$H).")";}function
process_type(array$l,$Cb="COLLATE"){return" ".(is_user_type($l["type"])?idf_escape($l["type"]):$l["type"]).process_length($l["length"]).(preg_match(number_type(),$l["type"])&&in_array($l["unsigned"],driver()->unsigned)?" $l[unsigned]":"").(preg_match('~'.text_type().'~',$l["type"])&&$l["collation"]?" $Cb ".(JUSH=="mssql"?$l["collation"]:q($l["collation"])):"");}function
process_field(array$l,array$Qm){if($l["on_update"])$l["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$l["on_update"]);return
array(idf_escape(trim($l["field"])),process_type($Qm),($l["null"]?" NULL":" NOT NULL"),default_value($l),(preg_match('~timestamp|datetime~',$l["type"])&&$l["on_update"]?" ON UPDATE $l[on_update]":""),(support("comment")&&$l["comment"]!=""?" COMMENT ".q($l["comment"]):""),($l["auto_increment"]?auto_increment():null),);}function
default_value(array$l){if($l["default"]===null)return"";$j=str_replace("\r","",$l["default"]);$ze=$l["generated"];$P=!preg_match('~]$~',$l["length"])&&(preg_match('~char|binary|text|json|enum|set|String~',$l["type"])||driver()->enumLength($l));return(in_array($ze,driver()->generated)?(JUSH=="mssql"?" AS ($j)".($ze=="VIRTUAL"?"":" $ze"):" GENERATED ALWAYS AS ($j) $ze"):(preg_match('~^GENERATED ~i',$j)?" $j":" DEFAULT ".($P||preg_match('~^(?![a-z])~i',$j)?(JUSH=="sql"&&preg_match('~text|json~',$l["type"])?"(".q($j).")":q($j)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($j)":$j)))));}function
edit_fields(array$m,array$Eb,$T="TABLE",array$le=array()){$m=array_values($m);$_c=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$Kb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($T=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($T=="TABLE"?lang(127):lang(128)),"<th id='label-type'>".lang(58)."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<th id='label-length'>".lang(129),"<th>".lang(130);if($T=="TABLE")echo"<th id='label-null'>NULL\n","<th><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".lang(60)."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'cockroach'=>"serial",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<th id='label-default'$_c>".lang(61),(support("comment")?"<th id='label-comment'$Kb>".lang(59):"");$og=!support("move_col");echo"<td>".icon("plus","add[".($og?count($m):0)."]","+",lang(131),($og?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($m
as$r=>$l){$r++;$Ai=$l[($_POST?"orig":"field")];$Mc=(isset($_POST["add"][$r-1])||(isset($l["field"])&&!idx($_POST["drop_col"],$r)))&&(support("drop_col")||$Ai=="");echo"<tr".($Mc?"":" hidden").">\n",($T=="PROCEDURE"?"<td>".html_select("fields[$r][inout]",explode("|",driver()->inout),$l["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",lang(132))." ":"");if($Mc)echo"<input name='fields[$r][field]' value='".h($l["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$r-1])?" autofocus":"").">";echo
input_hidden("fields[$r][orig]",$Ai);edit_type("fields[$r]",$l,$Eb,$le);if($T=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$r][null]",1,$l["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$r'".($l["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$_c>".(driver()->generated?html_select("fields[$r][generated]",array_merge(array("","DEFAULT"),driver()->generated),$l["generated"])." ":checkbox("fields[$r][generated]",1,$l["generated"],"","","","label-default"));$c=" name='fields[$r][default]' aria-labelledby='label-default'";$X=h($l["default"]);echo(preg_match('~\n~',$l["default"])?"<textarea$c rows='2' cols='30' style='vertical-align: bottom;'>\n$X</textarea>":"<input$c value='$X'>");if(support("comment")){$c=" name='fields[$r][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$Kb>".adminer()->commentInput('COLUMN',$c,$l["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$r]","+",lang(131))." ":""),($Ai==""||support("drop_col")?icon("cross","drop_col[$r]","x",lang(133)):"");}}function
process_fields(array&$m){if($_POST["add"]){$m=array_values($m);array_splice($m,key($_POST["add"]),0,array(array()));}return$_POST["add"]||$_POST["drop_col"];}function
drop_create($Zc,$gc,$bd,$mm,$dd,$Eg,$fh,$dh,$eh,$gi,$Hh){if($_POST["drop"])query_redirect($Zc,$Eg,$fh);elseif($gi=="")query_redirect($gc,$Eg,$eh);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($Eg,$dh,queries($Zc)&&queries($gc)&&driver()->commit());driver()->rollback();}elseif($gi!=$Hh){$ic=queries($gc);queries_redirect($Eg,$dh,$ic&&queries($Zc));if($ic&&$bd)queries($bd);}else
queries_redirect($Eg,$dh,queries($mm)&&queries($dd)&&queries($Zc)&&queries($gc));}function
create_trigger($ki,array$I){$sm=" $I[Timing] $I[Event]".(preg_match('~ OF~',$I["Event"])?" $I[Of]":"");return"CREATE TRIGGER ".idf_escape($I["Trigger"]).(JUSH=="mssql"?$ki.$sm:$sm.$ki).preg_replace('~[\s;]+$~',''," $I[Type]\n$I[Statement]").";";}function
q_dollar($P){$Ec='$$';while(strpos($P.$Ec,$Ec)!=strlen($P))$Ec='$_'.substr($Ec,1);return$Ec.$P.$Ec;}function
routine_collate($Db){static$pb=array();if($Db&&!$pb){foreach(collations()as$ob=>$zn){foreach((array)$zn
as$W)$pb[$W]=$ob;}}return($pb[$Db]?"CHARACTER SET ".q($pb[$Db])." ":"")."COLLATE";}function
create_routine($pk,array$I){$N=array();$m=$I["fields"];ksort($m);foreach($m
as$l){if($l["field"]!=""){$Ef=(preg_match("~^(".driver()->inout.")\$~",$l["inout"])?$l["inout"]:"");$N[]="\n  ".(JUSH=="mssql"?"@$l[field]".process_type($l).($Ef?" $Ef":""):($Ef?"$Ef ":"").idf_escape($l["field"]).process_type($l,routine_collate($l["collation"])));}}$Bc="";$B=array();foreach(routine_options($pk)as$w=>$Y){$X=idx($I["options"],$w,"");if($w=="DEFINER")$Bc=($X?" $w=".implode("@",array_map('Adminer\q',explode("@",$X,2))):"");elseif(!$Y){if($X!="")$B[]="$w ".q($X);}elseif($X!=reset($Y)&&in_array($X,$Y))$B[]=$X;}$mg=$I["language"];$Cc=preg_replace('~[\s;]+$~','',$I["definition"]);$Vc=(JUSH=="pgsql"||($mg&&$mg!="sql"));$Pi=($N?implode(",",$N)."\n":"");return"CREATE$Bc $pk ".table(trim($I["name"])).(JUSH=="mssql"&&$pk=="PROCEDURE"?rtrim($Pi):" ($Pi)").($pk=="FUNCTION"?"\nRETURNS".process_type($I["returns"],routine_collate($I["returns"]["collation"])):"").($mg?" LANGUAGE $mg":"").($B?"\n".implode(" ",$B):"").($Vc?" AS ".q_dollar("\n".trim($Cc)."\n"):(JUSH=="mssql"?"\nAS":"")."\n$Cc;");}function
remove_definer($F){$Bc=implode("@",array_map('Adminer\idf_escape',explode("@",logged_user(),2)));return
preg_replace('(^([A-Z =]+) DEFINER='.preg_quote($Bc).')','\1',$F);}function
object_name($T,$Q,array$e){return
str_replace(array("{table}","{columns}"),array($Q,implode("_",$e)),adminer()->namePattern($T));}function
format_foreign_key(array$o,$A=""){$i=$o["db"];$Qh=$o["ns"];return($A!=""?" CONSTRAINT ".idf_escape($A):"")." FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$o["source"])).") REFERENCES ".($i!=""&&$i!=$_GET["db"]?idf_escape($i).".":"").($Qh!=""&&$Qh!=$_GET["ns"]?idf_escape($Qh).".":"").idf_escape($o["table"])." (".implode(", ",array_map('Adminer\idf_escape',$o["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").($o["deferrable"]?" $o[deferrable]":"");}function
tar_file($n,$xm){$H=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($xm->size),decoct(time()));$vb=8*32;for($r=0;$r<strlen($H);$r++)$vb+=ord($H[$r]);$H
.=sprintf("%06o",$vb)."\0 ";echo$H,str_repeat("\0",512-strlen($H));$xm->send();echo
str_repeat("\0",511-($xm->size+511)%512);}function
doc_version(){$al=connection()->server_info;if(JUSH=='oracle'){preg_match('~(?:.* |^)(\d+)\.\d+\.\d+\.\d+\.\d+~s',$al,$_);return($_[1]>=18?$_[1]:"19");}$Zj=(JUSH=='sql'||connection()->flavor=='cockroach'?'~^\d+\.\d+~':'~^\d\.?\d~');$Bn=(preg_match($Zj,$al,$_)?$_[0]:"");if(JUSH=='mssql')return($Bn>=15?"sql-server-ver$Bn":($Bn==12?"azuresqldb-current":"sql-server-2017"));return$Bn;}function
doc_link(array$gj,$nm="📖"){$Bn=doc_version();$mn=array('sql'=>"https://dev.mysql.com/doc/refman/$Bn/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$Bn)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://docs.oracle.com/en/database/oracle/oracle-database/$Bn/",);if(connection()->flavor=='maria'){$mn['sql']="https://mariadb.com/kb/en/";$gj['sql']=($gj['mariadb']?:str_replace(".html","/",$gj['sql']));}if(connection()->flavor=='cockroach'&&$gj['cockroach']){$mn['pgsql']="https://docs.cockroachlabs.com/docs/v$Bn/";$gj['pgsql']=$gj['cockroach'];}return($gj[JUSH]?" <a href='".h($mn[JUSH].$gj[JUSH].(JUSH=='mssql'?"?view=$Bn":""))."'".target_blank()." class='doc' title='".lang(134)."'>$nm</a>":"");}function
db_size($i){if(!connection()->select_db($i))return"?";$H=0;foreach(table_status()as$R)$H+=$R["Data_length"]+$R["Index_length"];return
format_number($H);}function
set_utf8mb4($gc){static$N=false;if(!$N&&preg_match('~\butf8mb4~i',$gc)){$N=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!="")page_header(lang(44).": ".h(DB),adminer()->error(),true,"","db");else{if(!isset($_GET["db"])&&support("single_db")){$h=adminer()->databases();if($h)redirect(ME."db=".url_escape($h[0]));}if($_POST["db"]&&!$k)queries_redirect(substr(ME,0,-1),lang(135),drop_databases($_POST["db"]));page_header(lang(136),$k,false);echo"<p class='links'>\n";foreach(array('database'=>lang(137),'privileges'=>lang(80),'processlist'=>lang(138),'variables'=>lang(139),'status'=>lang(140),)as$w=>$W){if(support($w))echo"<a href='".h(ME)."$w='>$W</a>\n";}echo"<p>".lang(141,get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".lang(142,"<b>".h(logged_user())."</b>")."\n";$h=adminer()->databases();if($h){$Bk=support("scheme");$Eb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".lang(44).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(143)."</a>":"")."<th>".lang(144)."<th>".lang(145)."<th>".lang(146)." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".lang(147)."</a>"."<tbody>\n";$h=($_GET["dbsize"]?count_tables($h):array_flip($h));foreach($h
as$i=>$S){$ok=h(preg_replace('~&db=[^&]*~','',ME))."db=".url_escape($i);$s=h("Db-".$i);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$i,in_array($i,(array)$_POST["db"]),"","","",$s):""),"<th><a href='$ok' id='$s'>".h($i)."</a>";$Db=h(db_collation($i,$Eb));echo"<td>".(support("database")?"<a href='$ok".($Bk?"&amp;ns=":"")."&amp;database=' title='".lang(76)."'>$Db</a>":$Db),"<td align='right'><a href='$ok&amp;schema=' id='tables-".h($i)."' title='".lang(79)."'>".($_GET["dbsize"]?format_number($S):"?")."</a>","<td align='right' id='size-".h($i)."'>".($_GET["dbsize"]?db_size($i):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".lang(148)." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".lang(149)."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$qa=adminer();$pj=($qa
instanceof
Plugins?$qa->plugins:array());$Yc=($qa
instanceof
Plugins?$qa->drivers:array());$Jc=design_checksums();if($pj||$Yc||$Jc){$wb=($qa
instanceof
Plugins?$qa->checksums():array());$Yh=Plugins::officialChecksums();$hn=function($ln){return" (<a href='$ln'".target_blank()." class='update'>".VERSION."</a>)";};$oj=function($Vd)use($wb,$Yh,$hn){return($wb[$Vd]&&$Yh[$Vd]&&$wb[$Vd]!==$Yh[$Vd]?$hn("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".lang(150)."</h3>\n<ul>\n";foreach($pj
as$mj){$Xj=new
\ReflectionObject($mj);$Gc=(method_exists($mj,'description')?$mj->description():"");if(!$Gc){if(preg_match('~^/[\s*]+(.+)~',$Xj->getDocComment(),$_))$Gc=$_[1];}$Ck=(method_exists($mj,'screenshot')?$mj->screenshot():"");echo"<li><b>".get_class($mj)."</b>".h($Gc?": $Gc":"").($Ck?" (<a href='".h($Ck)."'".target_blank().">".lang(151)."</a>)":"").$oj(basename((string)$Xj->getFileName(),'.php'))."\n";}foreach($Yc
as$s=>$A)echo"<li><b>".h($s)."</b>: ".h($A).$oj(basename((string)$qa->driverFiles[$s],'.php'))."\n";if($Jc){$ai=official_design_checksums();foreach($Jc
as$n=>$Ic){list($A,$vb)=$Ic;$Zh=$ai["$A/$n"];echo"<li><b>".h($n)."</b>".h($A?": $A":"").($Zh&&$Zh!==$vb?$hn("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~&db=[^&]+~','\0&ns='.url_escape(get_schema()),relative_uri()));if(!set_schema($_GET["ns"]))page_header(lang(88).h(": $_GET[ns]"),adminer()->error(),true,"","ns");}}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($Yb){$this->size+=strlen($Yb);fwrite($this->handler,$Yb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if($_GET["select"]!=""&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$m=fields($a);header("Content-Type: application/octet-stream");$Y=array_merge((array)$_GET["where"],(array)$_GET["val"]);header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$Y)).".".friendly_url($_GET["field"]));$L=array(idf_escape($_GET["field"]));$G=driver()->select($a,$L,array(where($_GET,$m)),$L);$I=($G?$G->fetch_row():array());echo
driver()->value($I[0],$m[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$m=fields($a);if(!$m)$k=adminer()->error();$R=table_status1($a);$A=adminer()->tableName($R);$k=$k?:h($R["Error"]);page_header(($m&&is_view($R)?$R['Engine']=='materialized view'?lang(152):lang(153):lang(154)).": ".($A!=""?$A:h($a)),$k,array(),"",!$m,($m?doc_link(array(JUSH=>driver()->tableHelp($a,is_view($R)))):""));$nk=array();foreach($m
as$w=>$l)$nk+=$l["privileges"];adminer()->selectLinks($R,(isset($nk["insert"])||!support("table")?"":null));$Jb=$R["Comment"];if($Jb!="")echo"<p class='nowrap'>".lang(59).": ".adminer()->commentValue('TABLE',$Jb)."\n";if($m)adminer()->tableStructurePrint($m,$R);function
tables_links(array$S){echo"<ul>\n";foreach($S
as$I){$z=preg_replace('~ns=[^&]*~',"ns=".url_escape($I["ns"]),ME);echo"<li><a href='".h($z."table=".url_escape($I["table"]))."'>".($I["ns"]!=$_GET["ns"]?"<b>".h($I["ns"])."</b>.":"").h($I["table"])."</a>";}echo"</ul>\n";}$Cf=driver()->inheritsFrom($a);if($Cf){echo"<h3>".lang(155)."</h3>\n";tables_links($Cf);}if(support("indexes")&&driver()->supportsIndex($R)){echo"<div>\n","<h3 id='indexes'>".lang(156)."</h3>\n";$v=indexes($a);if($v)adminer()->tableIndexesPrint($v,$R);if(driver()->supportsAlterIndex($R))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.lang(157)."</a>\n";echo"</div>\n";}if(!is_view($R)&&driver()->supportsAlterTable($R)){if(fk_support($R)){echo"<div>\n","<h3 id='foreign-keys'>".lang(123)."</h3>\n";$le=foreign_keys($a);if($le){echo"<table>\n","<thead><tr><th>".lang(158)."<th>".lang(159)."<th>".lang(126)."<th>".lang(125)."<td class='hover'><tbody>\n";foreach($le
as$A=>$o){echo"<tr title='".h($A)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$o["source"]))."</i>";$z=($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".url_escape($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".url_escape($o["ns"]),ME):ME));echo"<td><a href='".h($z."table=".url_escape($o["table"]))."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($A)).'">'.lang(160).'</a>',"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.lang(161)."</a>\n","</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".lang(162)."</h3>\n";$rb=driver()->checkConstraints($a);if($rb){echo"<table>\n";foreach($rb
as$w=>$W)echo"<tr title='".h($w)."'>","<td><code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($W)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($w))."'>".lang(160)."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.lang(163)."</a>\n","</div>\n";}}if(support(is_view($R)?"view_trigger":"trigger")&&driver()->supportsAlterTable($R)){echo"<div>\n","<h3 id='triggers'>".lang(164)."</h3>\n";$Nm=triggers($a);if($Nm){echo"<table>\n";foreach($Nm
as$w=>$W){echo"<tr valign='top'><td>".h($W[0])."<td>".h($W[1])."<th>".h($w)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($w))."'>".lang(160)."</a>";$pk=$W[2];if($pk){$rk=preg_replace('~ns=[^&]*~',"ns=".url_escape($pk["ns"]),ME).'function='.url_escape($pk["function"]).'&name='.url_escape($pk["name"]);echo", <a href='".h($rk)."' title='".h($pk["name"])."'>".lang(165)."</a>";}echo"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.lang(166)."</a>\n","</div>\n";}$fl=driver()->shadowTables($a);if($fl){echo"<h3 id='shadow-tables'>".lang(167)."</h3>\n";tables_links($fl);}$Bf=driver()->inheritedTables($a);if($Bf){echo"<h3 id='partitions'>".lang(168)."</h3>\n";$Ui=driver()->partitionsInfo($a);if($Ui)echo"<p><code class='jush-".JUSH."'>BY ".h("$Ui[partition_by]($Ui[partition])")."</code>\n";tables_links($Bf);}}elseif(isset($_GET["schema"])){page_header(lang(79),"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));function
schema_column($Q,array$Wj,array&$e){if(!isset($e[$Q])){$e[$Q]=0;foreach((array)idx($Wj,$Q)as$A=>$Yj){if($A!=$Q)$e[$Q]=max($e[$Q],schema_column($A,$Wj,$e)+1);}}return$e[$Q];}function
type_class($T){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$w=>$W){if(preg_match("~$w|$W~",$T))return" class='$w'";}}$Xl=array();$Zl=array();$Yl=array();$Sd=array();$da=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$da,$Kg,PREG_SET_ORDER);foreach($Kg
as$r=>$_){$Xl[$_[1]]=array((float)$_[2],(float)$_[3]);$Zl[]="\n\t'".js_escape($_[1])."': [ $_[2], $_[3] ]";}$K=array();$Wj=array();$le=array();$za=driver()->allFields();$Ye=array();$am=array();foreach(table_status('',true)as$Q=>$R){if(!is_view($R)){if(adminer()->tableName($R)!=""&&!$R["dependent"])$am[$Q]=$R;else$Ye[$Q]=true;}}foreach($am
as$Q=>$R){$E=0;$K[$Q]["fields"]=array();foreach($za[$Q]as$l){$E+=1.25;$Sd[$Q][$l["field"]]=$E;$K[$Q]["fields"][$l["field"]]=$l;}foreach(adminer()->foreignKeys($Q)as$W){if($W["db"]==""&&$W["ns"]==""&&!$Ye[$W["table"]]){$le[$Q][]=$W;$Wj[$W["table"]][$Q]=array();}}}$e=array();$Ce=array();$On=array();$Ie=array();foreach(array_keys($K)as$A)schema_column($A,$Wj,$e);arsort($e);foreach($e
as$A=>$d){$lh=null;foreach((array)idx($le,$A)as$W){if($W["table"]!=$A&&$K[$W["table"]])$lh=($lh===null?$e[$W["table"]]:min($lh,$e[$W["table"]]));}$e[$A]=max($d,(int)$lh-1);}foreach($K
as$A=>$Q){$d=$e[$A];$Ce[$d][]=$A;$pm=.75*strlen($A);foreach($Q["fields"]as$l)$pm=max($pm,.65*strlen($l["field"]));$On[$d]=max(idx($On,$d,0),ceil($pm)+1);}foreach($le
as$A=>$zn){foreach($zn
as$W){$He=$e[$A]+(idx($e,$W["table"],$e[$A])>$e[$A]?1:0);$Ie[$He]=idx($Ie,$He,0)+1;}}ksort($Ce);$We=0;$Nn=0;$Gb=0;$Aj=null;$Tl=array();$cm=array();foreach($Ce
as$d=>$S){if($Aj!==null){$Gb=round($Gb+$On[$Aj]+1.7+idx($Ie,$d,0)*.1,1);$wi=array();foreach($S
as$A){$Jl=0;$fc=0;$Dh=array_keys((array)idx($Wj,$A));foreach((array)idx($le,$A)as$W)$Dh[]=$W["table"];foreach($Dh
as$xh){if($K[$xh]&&$e[$xh]<$d){$Jl+=$K[$xh]["pos"][0];$fc++;}}$wi[$A]=($fc?$Jl/$fc:$We);}asort($wi);$S=array_keys($wi);}$_m=0;foreach($S
as$A){$E=1.25*count($K[$A]["fields"]);$K[$A]["pos"]=($Xl[$A]?:array($_m,$Gb));$Tl[$A]=$K[$A]["pos"][1];$cm[$A]=$On[$d];$_m+=2.5+$E;$We=max($We,$K[$A]["pos"][0]+2.5+$E);$Nn=max($Nn,round($K[$A]["pos"][1]+$On[$d],1));if(!$Xl[$A])$Yl[]="\n\t'".js_escape($A)."': [ ".$K[$A]["pos"][0].", ".$K[$A]["pos"][1]." ]";}$Aj=$d;}$sg=array();$Wa=array();foreach($le
as$A=>$zn){foreach($zn
as$W){$im=idx($Tl,$W["table"],$Tl[$A]);$ql=$Tl[$A]+$cm[$A];$mk=($im-1>$ql);$qg=($mk?$ql+1:min($Tl[$A],$im)-1);$Va=idx($Wa,(string)$qg,0);$Wa[(string)$qg]=$Va+1;$qg=round($mk?min($qg+$Va*.1,$im-1):$qg-$Va*.1,1);while($sg[(string)$qg])$qg-=.0001;$K[$A]["references"][$W["table"]][(string)$qg]=array($W["source"],$W["target"]);$Wj[$W["table"]][$A][(string)$qg]=$W["target"];$sg[(string)$qg]=true;}}echo'<div id="schema" style="height: ',$We,'em; width: ',$Nn,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$Zl)."\n",'};
const tablePosDefault = {',implode(",",$Yl)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$We,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($K
as$A=>$Q){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em; width: ".$cm[$A]."em;'>",'<a href="'.h(ME).'table='.url_escape($A).'"><b>'.h($A)."</b></a>";foreach($Q["fields"]as$l){$W='<span'.type_class($l["type"]).' title="'.h($l["type"].($l["length"]?"($l[length])":"").($l["null"]?" NULL":'')).'">'.h($l["field"]).'</span>';echo"<br>".($l["primary"]?"<i>$W</i>":$W);}foreach((array)$Q["references"]as$jm=>$Yj){foreach($Yj
as$qg=>$Tj){$rg=$qg-$Q["pos"][1];$Gl=($rg>0?"left: 100%; width: calc($rg"."em - 100%)":"left: $rg"."em");$Nn=($rg>0?"100%":(-$rg)."em");$r=0;foreach($Tj[0]as$pl)echo"\n<div class='references' title='".h($jm)."' id='refs$qg-".($r++)."' style='$Gl"."; top: ".$Sd[$A][$pl]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: $Nn;'></div></div>";}}foreach((array)$Wj[$A]as$jm=>$Yj){foreach($Yj
as$qg=>$km){$rg=$qg-$Q["pos"][1];$r=0;foreach($km
as$hm)echo"\n<div class='references arrow' title='".h($jm)."' id='refd$qg-".($r++)."' style='left: $rg"."em; top: ".$Sd[$A][$hm]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$rg)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($K
as$A=>$Q){foreach((array)$Q["references"]as$jm=>$Yj){if($K[$jm]){foreach($Yj
as$qg=>$Tj){$mh=$We;$Sg=-10;foreach($Tj[0]as$w=>$pl){$rj=$Q["pos"][0]+$Sd[$A][$pl];$sj=$K[$jm]["pos"][0]+$Sd[$jm][$Tj[1][$w]];$mh=min($mh,$rj,$sj);$Sg=max($Sg,$rj,$sj);}echo"<div class='references' id='refl$qg' style='left: $qg"."em; top: $mh"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($Sg-$mh)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($da)),'" id="schema-link">',lang(169),'</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$k){$j=array("auto_increment"=>'');foreach(array("type","routine","event","trigger")as$Ll){if(support($Ll))$j[$Ll."s"]='';}save_settings(array_intersect_key($_POST+$j,array_flip(array("output","format","db_style","schema_style","table_style","data_style"))+$j),"adminer_export");$ya=(DB==""||$_GET["ns"]==="");$S=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Id=dump_headers((count($S)==1?key($S):DB),($ya||count($S)>1));$Tf=preg_match('~sql~',$_POST["format"]);if($Tf){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$Gl=$_POST["db_style"];$h=array(DB);if(DB==""){$h=$_POST["databases"];if(is_string($h))$h=explode("\n",rtrim(str_replace("\r","",$h),"\n"));}foreach((array)$h
as$i){adminer()->dumpDatabase($i);if(connection()->select_db($i)){if($Tf&&$Gl)echo
use_sql($i,$Gl).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?array(""):adminer()->schemas()))as$K){if($K!=""){if(DB==""&&information_schema(DB,$K))continue;set_schema($K);}if($Tf&&$_POST["schema_style"]&&function_exists('Adminer\use_schema_sql'))echo
use_schema_sql($_GET["ns"],$_POST["schema_style"]).";\n\n";$Cl=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):array());$Hd=array();$sc=array();foreach($Cl
as$A=>$R){if($ya||in_array($A,(array)$_POST["tables"]))$Hd[$A]=$R;if($ya||in_array($A,(array)$_POST["data"]))$sc[$A]=$R;}if($Tf){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($Hd);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$Om=array();foreach($sc
as$A=>$R){if(!is_view($R)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($Hd[$A])))$Om[]=$A;}echo
truncate_all_sql($Om);}$Ii="";if($_POST["types"]){foreach(types()as$s=>$T){$Cc=type_definition($s);$Uh=($Cc["kind"]=='d'?"DOMAIN":"TYPE");if($Cc["definition"])$Ii
.=($Gl!='DROP+CREATE'?"DROP $Uh IF EXISTS ".table($T).";;\n":"")."CREATE $Uh ".table($T)." $Cc[definition];\n\n";else$Ii
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$I){$A=$I["ROUTINE_NAME"];$pk=$I["ROUTINE_TYPE"];$gc=create_routine($pk,array("name"=>$A)+routine($I["SPECIFIC_NAME"],$pk));set_utf8mb4($gc);$Ii
.=($Gl!='DROP+CREATE'?"DROP $pk IF EXISTS ".table($A).";;\n":"")."$gc;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$I){$gc=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($I["Name"]),3));set_utf8mb4($gc);$Ii
.=($Gl!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($I["Name"]).";;\n":"")."$gc;;\n\n";}}echo($Ii&&JUSH=='sql'?"DELIMITER ;;\n\n$Ii"."DELIMITER ;\n\n":$Ii);}if($_POST["table_style"]||$_POST["data_style"]){$Dn=array();foreach($Cl
as$A=>$R){$Q=array_key_exists($A,$Hd);$qc=array_key_exists($A,$sc);if($Q||$qc){$xm=null;if($Id=="tar"){$xm=new
TmpFile;ob_start(array($xm,'write'),1e5);}adminer()->dumpTable($A,($Q?$_POST["table_style"]:""),(is_view($R)?2:0));if(is_view($R))$Dn[]=$A;elseif($qc){$m=fields($A);$L=array("*");$cc=convert_fields($m,$m);if($cc)$L[]=substr($cc,2);adminer()->dumpData($A,$_POST["data_style"],"",$L);}if($Tf&&$_POST["triggers"]&&$Q&&($Nm=trigger_sql($A)))echo"\nDELIMITER ;;\n$Nm\nDELIMITER ;\n";if($Id=="tar"){ob_end_flush();tar_file((DB!=""?"":"$i/")."$A.csv",$xm);}elseif($Tf)echo"\n";}}if($Tf&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($Hd
as$A=>$R){if(!is_view($R))echo
foreign_keys_sql($A);}}if($Tf){foreach($Dn
as$Cn)adminer()->dumpTable($Cn,$_POST["table_style"],1);}if($Id=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header(lang(85),$k,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$wc=array('','USE','DROP+CREATE','CREATE');$_k=(JUSH=="mssql"?array('','DROP+CREATE','CREATE'):$wc);$bm=array('','DROP+CREATE','CREATE');$rc=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$rc[]='INSERT+UPDATE';$I=get_settings("adminer_export");if(!$I)$I=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"schema_style"=>"","table_style"=>"DROP+CREATE","data_style"=>"INSERT");echo"<tr><th>".lang(170)."<td>".html_radios("output",adminer()->dumpOutput(),$I["output"])."\n","<tr><th>".lang(171)."<td>".html_radios("format",adminer()->dumpFormat(),$I["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".lang(44)."<td>".html_select('db_style',$wc,$I["db_style"]).(support("type")?checkbox("types",1,$I["types"],lang(0)):"").(support("routine")?checkbox("routines",1,$I["routines"],lang(81)):"").(support("event")?checkbox("events",1,$I["events"],lang(83)):"")),(function_exists('Adminer\use_schema_sql')?"<tr><th>".lang(88)."<td>".html_select('schema_style',$_k,$I["schema_style"]):""),"<tr><th>".lang(145)."<td>".html_select('table_style',$bm,$I["table_style"]).checkbox("auto_increment",1,$I["auto_increment"],lang(60)).(support("trigger")?checkbox("triggers",1,$I["triggers"],lang(164)):""),"<tr><th>".lang(172)."<td>".html_select('data_style',$rc,$I["data_style"]),'</table>
';adminer()->dumpPrint();echo'<p><input type=\'submit\' value=\'',lang(85),'\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$zj=array();if($_GET["ns"]===""&&support("scheme")){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(173)."'".on('click','formCheck','^schemas\[').">".lang(88)."</label>","<tbody>\n";foreach(adminer()->schemas()as$K){if(!information_schema(DB,$K))echo"<tr><td>".checkbox("schemas[]",$K,true,$K,"","block")."\n";}}elseif(DB!=""){$tb=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$tb class='jsonly' title='".lang(173)."'".on('click','formCheck','^tables\[').">".lang(154)."</label>","<th style='text-align: right;'><label class='block'>".lang(172)."<input type='checkbox' id='check-data'$tb class='jsonly' title='".lang(173)."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$Dn="";$em=tables_list();foreach($em
as$A=>$T){$yj=preg_replace('~_.*~','',$A);$tb=($a==""||$a==(substr($a,-1)=="%"?"$yj%":$A));$Ej="<tr><td>".checkbox("tables[]",$A,$tb,$A,"","block");if($T!==null&&!preg_match('~table~i',$T))$Dn
.="$Ej\n";else
echo"$Ej<td align='right'><label class='block'><span id='Rows-".h($A)."'></span>".checkbox("data[]",$A,$tb)."</label>\n";$zj[$yj]++;}echo$Dn;if($em)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$h=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($h?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(173)."'".on('click','formCheck','^databases\[').">":"").lang(44)."</label>","<tbody>\n";if($h){foreach($h
as$i){if(!information_schema($i)){$yj=preg_replace('~_.*~','',$i);echo"<tr><td>".checkbox("databases[]",$i,$a==""||$a=="$yj%",$i,"","block")."\n";$zj[$yj]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$be=true;foreach($zj
as$w=>$W){if($w!=""&&$W>1){echo($be?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$w%")."'>".h($w)."</a>";$be=false;}}}elseif(isset($_GET["privileges"])){page_header(lang(80));echo'<p class="links"><a href="'.h(ME).'user=">'.lang(174)."</a>";$G=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$Ae=$G;if(!$G)$G=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($Ae?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".lang(42)."<th>".lang(40)."<td class='hover'><tbody>\n";while($I=$G->fetch_assoc())echo'<tr><td>'.h($I["User"]),"<td>".h($I["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($I["User"]).'&host='.url_escape($I["Host"])).'">'.lang(14)."</a>\n";if(!$Ae||DB!="")echo"<tr><td><input name='user' autocapitalize='off'>","<td><input name='host' value='localhost' autocapitalize='off'>","<td class='hover'><input type='submit' value='".lang(14)."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$k&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}if(!$k&&$_POST["val"]){$ta=0;$Hl=true;$lb=array();$xk=0;foreach($_POST["val"]as$J)$xk+=count($J);$Ya=$xk>1&&driver()->begin();foreach($_POST["val"]as$Rl=>$J){$Q=bracket_escape($Rl,true);$m=fields($Q);$Sl=indexes($Q);foreach($J
as$t=>$I){parse_str(bracket_escape($t,true),$Z);$Ym=array();foreach($Z["where"]as$w=>$W)$Ym[bracket_escape($w,true)]=$W;if(!$m||$Z["null"]||array_diff_key($Ym,$m)||!unique_array($Ym,$Sl)){$Hl=false;break
2;}$N=array();$L=array();foreach($I
as$bg=>$W){$w=bracket_escape($bg,true);$l=idx($m,$w);if(!$l){$Hl=false;break
3;}$N[idf_escape($w)]=(preg_match('~char|text~',$l["type"])||$W!=""?adminer()->processInput($l,$W):"NULL");$L[$bg]=$w;}$Oj=where($Z,$m);if(!driver()->update($Q,$N," WHERE $Oj",0," ")){$Hl=false;break
2;}$ta+=connection()->affected_rows;$e=array();foreach($L
as$w)$e[]=idf_escape($w);$in=driver()->select($Q,$e,array($Oj),$e);$Jh=($in?$in->fetch_row():array());$Xf=0;foreach($L
as$bg=>$w){$l=$m[$w];$Fl=array('type'=>(preg_match('~binary~',$l["type"])?'blob':$l["type"]));$lb["val[$Rl][$t][$bg]"]=select_value(idx($Jh,$Xf++),"",$Fl,null);}}}if($Ya&&$Hl)$Hl=driver()->commit();queries_redirect(null,lang(175,$ta),$Hl);if($Ya&&!$Hl)driver()->rollback();page_headers();page_messages($k);foreach($lb
as$A=>$W)echo"<div data-name='".h($A)."' hidden>$W</div>\n";exit;}restart_session();$cf=&get_session("queries");$bf=&$cf[DB];if(!$k&&$_POST["clear"]){$bf=array();redirect(remove_from_uri("history"));}stop_session();$ra=get_settings("adminer_import");if($_POST&&$ra)save_settings($ra,"adminer_import");page_header((isset($_GET["import"])?lang(84):lang(73)),$k);$_g=driver()->lineComment();if(!$k&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$Ec=driver()->delimiter;$pe=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$ul=adminer()->importServerPath();$pe=@fopen((file_exists($ul)?$ul:"compress.zlib://$ul.gz"),"rb");$F=($pe?fread($pe,1e6):false);}else$F=get_file("sql_file",true,$Ec);if(is_string($F)){if(($ah=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($ah,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$Lj=$F.(preg_match("~$Ec\\s*\$~",$F)?"":$Ec);if(!$bf||first(end($bf))!=$Lj){restart_session();$bf[]=array($Lj,time());set_session("queries",$cf);stop_session();}}$rl="(?:\\s|\xEF\xBB\xBF|/\\*[\s\S]*?\\*/|(?:$_g)[^\n]*\n?|--\r?\n)";$bi=0;$nd=true;$ec=false;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$Ib=0;$vd=array();$Ri='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$_g.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Am=microtime(true);while($F!=""){if(!$bi&&preg_match("~^$rl*+DELIMITER\\s+(\\S+)~i",$F,$_)){$Ec=preg_quote($_[1]);$F=substr($F,strlen($_[0]));}elseif(!$bi&&JUSH=='pgsql'&&preg_match("~^($rl*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$_)){$Ec="\n\\\\\\.\r?\n";$ec=true;$bi=strlen($_[0]);}else{preg_match("($Ec\\s*|$Ri)",$F,$_,PREG_OFFSET_CAPTURE,$bi);list($ne,$E)=$_[0];if(!$ne&&$pe&&!feof($pe))$F
.=fread($pe,1e5);else{if(!$ne&&rtrim($F)=="")break;$bi=$E+strlen($ne);if($ne&&!preg_match("(^$Ec)",$ne)){$ib=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($E>0&&strtolower($F[$E-1])=="e"));$hj=($ne=='/*'?'\*/':($ne=='['?']':(preg_match("~^(?:$_g)~",$ne)?"\n":preg_quote($ne).($ib?'|\\\\.':''))));while(preg_match("($hj|\$)s",$F,$_,PREG_OFFSET_CAPTURE,$bi)){$yk=$_[0][0];if(!$yk&&$pe&&!feof($pe))$F
.=fread($pe,1e5);else{$bi=$_[0][1]+strlen($yk);if(!$yk||$yk[0]!="\\")break;}}}else{$Lj=substr($F,0,$E+($ec?3:0));$F=substr($F,$bi);$bi=0;if($ec){$Ec=driver()->delimiter;$ec=false;}$Ab="<code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($Lj)."</code>";if(preg_match("~^$rl*+\$~",$Lj)&&!preg_match('~/\*M?!~',$Lj)){echo($_POST["only_errors"]?"":"<pre>$Ab</pre>\n");continue;}$nd=false;$Ib++;$Ej="<pre id='sql-$Ib'>$Ab</pre>\n";if(JUSH=="sqlite"&&preg_match("~^$rl*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Lj,$_)!==0){echo$Ej,"<p class='error'>".lang(176,preg_match('~ATTACH~i',$_[1])?'ATTACH':'VACUUM INTO')."\n";$vd[]=" <a href='#sql-$Ib'>$Ib</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Ej;ob_flush();flush();}$_l=microtime(true);if(connection()->multi_query($Lj)&&$g&&preg_match("~^$rl*+USE\\b~i",$Lj))$g->query($Lj);do{$G=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Ej:""),"<p class='error'>".lang(177).(connection()->errno?" (".connection()->errno.")":"").": ".adminer()->error()."\n";$vd[]=" <a href='#sql-$Ib'>$Ib</a>";if($_POST["error_stops"])break
2;}else{$z=ME."sql=".url_escape(trim($Lj));$qm=" <span class='time'>(".format_time($_l).")</span>".(strlen($z)<1900?" <a href='".h($z)."'>".lang(14)."</a>":"");$ta=connection()->affected_rows;$Hn=($_POST["only_errors"]?"":driver()->warnings());$In="warnings-$Ib";if($Hn)$qm
.=", <a href='#$In' class='toggle'>".lang(55)."</a>";$Fd="";$Gd="explain-$Ib";if(is_object($G)){$y=$_POST["limit"];$Sh=$y;$gd=!$_POST["only_errors"];if($gd)echo"<form action='' method='post'>\n";$_i=print_select_result($G,$g,array(),$Sh,$gd);if(!$_POST["only_errors"]){$Sh=max($G->num_rows,$Sh);echo"<p class='sql-footer'>".($Sh?($y&&$Sh>$y?lang(178,$y):"").lang(179,$Sh):""),$qm;if($g&&preg_match("~^($rl|\\()*+SELECT\\b~i",$Lj)&&($Fd=adminer()->explain($g,$Lj,$_i))!="")echo", <a href='#$Gd' class='toggle'>Explain</a>";if($gd)echo", <input type='submit' name='save' value='".lang(18)."' class='jsonly' disabled"." title='".lang(180)."'".on('click','sqlSave',lang(21)).">";$s="export-$Ib";echo", <a href='#$s' class='toggle'>".lang(85)."</a><span id='$s' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ra["output"])." ".html_select("format",adminer()->dumpFormat(),$ra["format"]).input_hidden("query",$Lj)."<input type='submit' name='export' value='".lang(85)."'".($y?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$rl*+(CREATE|DROP|ALTER)$rl++(DATABASE|SCHEMA)\\b~i",$Lj)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang(181,$ta)."$qm\n";}echo($Hn?"<div id='$In' class='hidden'>\n$Hn</div>\n":""),($Fd!=""?"<div id='$Gd' class='hidden explain'>\n$Fd</div>\n":"");}$_l=microtime(true);}while(connection()->next_result());}}}}}if($nd)echo"<p class='message'>".lang(182)."\n";else{$rf=connection()->inTransaction();driver()->rollback();if($rf)echo"<pre><code class='jush-".JUSH."'>ROLLBACK".(JUSH=="mssql"?" TRANSACTION":"")." -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang(183,$Ib-count($vd))," <span class='time'>(".format_time($Am).")</span>\n";elseif($vd&&$Ib>1)echo"<p class='error'>".lang(177).": ".implode("",$vd)."\n";}}else
echo"<p class='error'>".upload_error($F)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form"';$jn="";if(!isset($_GET["import"]))echo
on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"));else
echo
on_upload_progress($jn);echo'>
';$Cd="<input type='submit' value='".lang(184)."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$Lj=$_GET["sql"];if($_POST)$Lj=$_POST["query"];elseif($_GET["history"]=="all")$Lj=$bf;elseif($_GET["history"]!="")$Lj=idx($bf[$_GET["history"]],0);echo"<p>";textarea("query",$Lj,20);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$Cd\n",lang(185).": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$Je=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".lang(186)."</legend><div>",($jn?input_hidden(ini_get("session.upload_progress.name"),$jn):""),"SQL$Je: ".file_input(" name='sql_file[]' multiple","\n$Cd"),($jn?" <progress class='jsonly hidden' max='1' value='0'></progress>":""),"</div></fieldset>\n";$of=adminer()->importServerPath();if($of)echo"<fieldset><legend>".lang(187)."</legend><div>",lang(188,"<code>".h($of)."$Je</code>")," <input type='submit' name='webfile' value='".lang(189)."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(190))."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(191))."\n",input_token();if(!isset($_GET["import"])&&$bf){print_fieldset("history",lang(192),$_GET["history"]!="");for($W=end($bf);$W;$W=prev($bf)){$w=key($bf);list($Lj,$qm,$jd)=$W;echo'<div><a href="'.h(ME."sql=&history=$w").'" class="hover">'.lang(14)."</a>"." <span class='time' title='".@date('Y-m-d',$qm)."'>".@date("H:i:s",$qm)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$_g).*~m",'',$Lj))),80,"</code>").($jd?" <span class='time'>($jd)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".lang(193)."'>\n","<a href='".h(ME."sql=&history=all")."'>".lang(194)."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$m=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$m):""):where($_GET,$m));$gn=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($m
as$A=>$l){if((!$gn&&!isset($l["privileges"]["insert"]))||adminer()->fieldName($l)=="")unset($m[$A]);}if($_POST&&!$k&&!isset($_GET["select"])){$Eg=relative_uri((string)$_POST["referer"]);if($_POST["insert"])$Eg=($gn?null:relative_uri());elseif(!preg_match('~^.+&select=.+$~',$Eg))$Eg=ME."select=".url_escape($a);$v=indexes($a);$Zm=unique_array($_GET["where"],$v);$Oj="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Eg,lang(195),driver()->delete($a,$Oj,$Zm?0:1));else{$N=array();foreach($m
as$A=>$l){$W=process_input($l);if($W!==false&&$W!==null)$N[idf_escape($A)]=$W;}if($gn){if(!$N)redirect($Eg);queries_redirect($Eg,lang(196),driver()->update($a,$N,$Oj,$Zm?0:1));if(is_ajax()){page_headers();page_messages($k);exit;}}else{$G=driver()->insert($a,$N);$pg=($G?last_id($G):0);queries_redirect($Eg,lang(197,($pg?" $pg":"")),$G);}}}$I=null;$F="";$qm="";if($Z){$L=array();$Jk=array("*");foreach($m
as$A=>$l){if(isset($l["privileges"]["select"])){$Ha=($_POST["clone"]&&$l["auto_increment"]?"''":convert_field($l));$d=($Ha?"$Ha AS ":"").idf_escape($A);$L[]=$d;if($Ha)$Jk[]=$d;}}$I=array();if(!support("table")){$L=array("*");$Jk=$L;}if($L){$_l=microtime(true);$G=driver()->select($a,$L,array($Z),$L,array(),(isset($_GET["select"])?2:1));$F=str_replace("SELECT ".implode(", ",$L),"SELECT ".implode(", ",$Jk),driver()->query);$qm=format_time($_l);if(!$G)$k=adminer()->error();else{$I=$G->fetch_assoc();if(!$I)$I=false;}if(isset($_GET["select"])&&(!$I||$G->fetch_assoc()))$I=null;}}if(!$m&&driver()->primary!=""){if(!$Z){$G=driver()->select($a,array("*"),array(),array("*"));$I=($G?$G->fetch_assoc():false);if(!$I)$I=array(driver()->primary=>"");}if($I){foreach($I
as$w=>$W){if(!$Z)$I[$w]=null;$m[$w]=array("field"=>$w,"null"=>($w!=driver()->primary),"auto_increment"=>($w==driver()->primary));}}}if($_POST["save"]){$tj=array();foreach((array)$_POST["fields"]as$w=>$W)$tj[bracket_escape($w,true)]=$W;$I=$tj+($I?$I:array());}edit_form($a,$m,$I,$gn,$k,$F,$qm);}elseif(isset($_GET["create"])){function
referencable_primary($Mk){$H=array();foreach(table_status('',true)as$Vl=>$Q){if($Vl!=$Mk&&!$Q["dependent"]&&fk_support($Q)){foreach(fields($Vl)as$l){if($l["primary"]){if($H[$Vl]){unset($H[$Vl]);break;}$H[$Vl]=$l;}}}}return$H;}$a=$_GET["create"];$Wi=driver()->partitionBy;$aj=($Wi&&$a!=""?driver()->partitionsInfo($a):array());$Vj=referencable_primary($a);$le=array();foreach($Vj
as$Vl=>$l)$le[str_replace("`","``",$Vl)."`".str_replace("`","``",$l["field"])]=$Vl;$Ci=array();$R=array();$Ph=false;if($a!=""){$Ci=fields($a);$R=table_status1($a);$Ph=(count($R)<2);}$Aa=($a==""||driver()->supportsAlterTable($R));$I=$_POST;$I["fields"]=(array)$I["fields"];if($I["auto_increment_col"])$I["fields"][$I["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$k)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($I["fields"])&&!$k){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(198),drop_tables(array($a)));else{$m=array();$za=array();$nn=false;$je=array();$Bi=reset($Ci);$va=" FIRST";foreach($I["fields"]as$l){$o=$le[$l["type"]];$Qm=($o!==null?$Vj[$o]:$l);if($l["field"]!=""){if(!$l["generated"])$l["default"]=null;$Jj=process_field($l,$Qm);$za[]=array($l["orig"],$Jj,$va);if(!$Bi||$Jj!==process_field($Bi,$Bi)){$m[]=array($l["orig"],$Jj,$va);if($l["orig"]!=""||$va)$nn=true;}if($o!==null)$je[idf_escape($l["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$le[$l["type"]],'source'=>array($l["field"]),'target'=>array($Qm["field"]),'on_delete'=>$l["on_delete"],),object_name("FOREIGN",trim($I["name"]),array($l["field"])));$va=" AFTER ".idf_escape($l["field"]);}elseif($l["orig"]!=""){$nn=true;$m[]=array($l["orig"]);}if($l["orig"]!=""){$Bi=next($Ci);if(!$Bi)$va="";}}$Yi=array();if(in_array($I["partition_by"],$Wi)){foreach($I
as$w=>$W){if(preg_match('~^partition~',$w))$Yi[$w]=$W;}foreach($Yi["partition_names"]as$w=>$A){if($A==""){unset($Yi["partition_names"][$w]);unset($Yi["partition_values"][$w]);}}$Yi["partition_names"]=array_values($Yi["partition_names"]);$Yi["partition_values"]=array_values($Yi["partition_values"]);if($Yi==$aj)$Yi=array();}elseif(preg_match("~partitioned~",$R["Create_options"]))$Yi=null;$ch=lang(199);if($a==""){cookie("adminer_engine",$I["Engine"]);$ch=lang(200);}$A=trim($I["name"]);$Eg=ME.(support("table")?"table=":"select=").url_escape($A);$G=alter_table($a,$A,(JUSH=="sqlite"&&($nn||$je)?$za:$m),$je,($I["Comment"]!=$R["Comment"]?$I["Comment"]:null),($I["Engine"]&&$I["Engine"]!=$R["Engine"]?$I["Engine"]:""),($I["Collation"]&&$I["Collation"]!=$R["Collation"]?$I["Collation"]:""),($I["Auto_increment"]!=""?number($I["Auto_increment"]):""),$Yi);if($G&&!Queries::$queries&&$a!=""&&!$m&&!$je)redirect($Eg);queries_redirect($Eg,$ch,$G);}}$Al=($a!=""?"alter":"create");page_header(($a!=""?lang(53):lang(86)),$k,array("table"=>$a),h($a),$Ph,doc_link(array('sql'=>"$Al-table.html",'mariadb'=>($a!=""?"$Al-table":""),'pgsql'=>"sql-$Al"."table.html",'cockroach'=>"$Al-table",'mssql'=>"t-sql/statements/$Al-table-transact-sql",'sqlite'=>"lang_{$Al}table.html",'oracle'=>"sqlrf/".strtoupper($Al)."-TABLE.html",)));if(!$_POST){$Um=driver()->types();$I=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($Um["int"])?"int":(isset($Um["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$I=$R;$I["name"]=$a;$I["fields"]=array();if(!$_GET["auto_increment"])$I["Auto_increment"]="";foreach($Ci
as$l){if($l["generated"])$l["default"]=ltrim($l["default"]);$l["generated"]=$l["generated"]?:(isset($l["default"])?"DEFAULT":"");$I["fields"][]=$l;}if($Wi){$I+=$aj;$I["partition_names"][]="";$I["partition_values"][]="";}}}$Eb=flat_collations();$qd=driver()->engines();foreach($qd
as$pd){if(!strcasecmp($pd,$I["Engine"])){$I["Engine"]=$pd;break;}}$Ng=max_input_vars(12,20);if($Ng){$Ye=(count($I["fields"])>$Ng?"":" hidden");echo"<p".($Ye?" id='max-fields' data-columns='$Ng'":"")." class='error$Ye'>".max_input_vars_error()."\n";}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo
lang(201).": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($I["name"])."' autocapitalize='off'>\n",(!$Aa?h($R["Engine"])."\n":($qd?html_select("Engine",array(""=>"(".lang(202).")")+$qd,$I["Engine"],on('change','helpClose').on_help_value())."\n":""));if($Eb)echo"<datalist id='collations'>".optionlist($Eb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($I["Collation"])."' placeholder='(".lang(124).")'>\n");echo"<input type='submit' value='".lang(18)."'>\n";}if(support("columns")&&$Aa){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($I["fields"],$Eb,"TABLE",$le);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",lang(60).": <input type='number' name='Auto_increment' class='size' value='".h($I["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),lang(203),on('click','columnShowClick',6),"jsonly");$Lb=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$Lb,lang(59),on('click','editingCommentsClick',true),"jsonly").' ';$c=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($Lb?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$c,$I["Comment"]);}echo'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$a)),'>
';if($Wi&&(JUSH=='sql'||$a=="")){$Xi=preg_match('~RANGE|LIST~',$I["partition_by"]);print_fieldset("partition",lang(205),$I["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$Wi),$I["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($I["partition"])."'>)\n",lang(206).": <input type='number' name='partitions' class='size".($Xi||!$I["partition_by"]?" hidden":"")."' value='".h($I["partitions"])."'>\n","<table id='partition-table'".($Xi?"":" class='hidden'").">\n","<thead><tr><th>".lang(207)."<th>".lang(208)."<tbody>\n";foreach($I["partition_names"]as$w=>$W)echo'<tr>','<td><input name="partition_names[]" value="'.h($W).'" autocapitalize="off"'.($w==count($I["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($I["partition_values"],$w)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$xf=array("PRIMARY","UNIQUE","INDEX");$R=table_status1($a,true);$uf=driver()->indexAlgorithms($R);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$R["Engine"]))$xf[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$R["Engine"]))$xf[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$xf[]="VECTOR";$v=indexes($a);$m=fields($a);$Cj=array();if(JUSH=="mongo"){$Cj=$v["_id_"];unset($xf[0]);unset($v["_id_"]);}$I=$_POST;if($I)save_settings(array("index_options"=>$I["options"]));if($_POST&&!$k&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($I["indexes"]as$u){$A=$u["name"];if(in_array($u["type"],$xf)){$e=array();$wg=array();$Hc=array();$pi=array();$vf=(support("partial_indexes")?$u["partial"]:"");$tf=(in_array($u["algorithm"],$uf)?$u["algorithm"]:"");$N=array();ksort($u["columns"]);foreach($u["columns"]as$w=>$d){if($d!=""){$x=idx($u["lengths"],$w);$Fc=idx($u["descs"],$w);$oi=idx($u["opclasses"],$w);$N[]=($m[$d]?idf_escape($d):$d).($x?"(".(+$x).")":"").($oi!=""?" ".idf_escape($oi):"").($Fc?" DESC":"");$e[]=$d;$wg[]=($x?:null);$Hc[]=$Fc;$pi[]="$oi";}}$Dd=$v[$A];if($Dd){ksort($Dd["columns"]);ksort($Dd["lengths"]);ksort($Dd["descs"]);if($u["type"]==$Dd["type"]&&array_values($Dd["columns"])===$e&&(!$Dd["lengths"]||array_values($Dd["lengths"])===$wg)&&array_values($Dd["descs"])===$Hc&&(!$Dd["opclasses"]||array_values($Dd["opclasses"])===$pi)&&$Dd["partial"]==$vf&&(!$uf||$Dd["algorithm"]==$tf)){unset($v[$A]);continue;}}if($e)$b[]=array($u["type"],$A,$N,$tf,$vf);}}foreach($v
as$A=>$Dd)$b[]=array($Dd["type"],$A,"DROP");if(!$b)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),lang(209),alter_indexes($a,$b));}page_header(lang(156),$k,array("table"=>$a),h($a),false,doc_link(array('sql'=>"create-index.html",'pgsql'=>"sql-createindex.html",'cockroach'=>"create-index",'mssql'=>"t-sql/statements/create-index-transact-sql",'sqlite'=>"lang_createindex.html",'oracle'=>"sqlrf/CREATE-INDEX.html",)));$Ud=array_keys($m);if($_POST["add"]){foreach($I["indexes"]as$w=>$u){if($u["columns"][count($u["columns"])]!="")$I["indexes"][$w]["columns"][]="";}$u=end($I["indexes"]);if($u["type"]||array_filter($u["columns"],'strlen'))$I["indexes"][]=array("columns"=>array(1=>""));}if(!$I){foreach($v
as$w=>$u){$v[$w]["name"]=$w;$v[$w]["columns"][]="";}$v[]=array("columns"=>array(1=>""));$I["indexes"]=$v;}$wg=(JUSH=="sql"||JUSH=="mssql");$pi=driver()->indexOpclasses();$gl=($_POST?$_POST["options"]:get_setting("index_options"));$zh=array();foreach($xf
as$T)$zh[$T]=str_replace("{table}",$a,adminer()->namePattern($T));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">',lang(210);$mf=" class='idxopts".($gl?"":" hidden")."'";if($uf)echo"<th id='label-algorithm'$mf>".lang(211).doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html','cockroach'=>'create-index#parameters',));echo'<th><input type="submit" hidden>',lang(212).($wg?"<span$mf> (".lang(213).")</span>":"");if($wg||support("descidx"))echo
checkbox("options",1,$gl,lang(130),on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">',lang(214);if(support("partial_indexes"))echo"<th id='label-condition'$mf>".lang(215);echo'<td><noscript>',icon("plus","add[0]","+",lang(131)),'</noscript>
<tbody>
';if($Cj){echo"<tr><td>PRIMARY<td>";foreach($Cj["columns"]as$w=>$d)echo
select_input(" disabled",array_combine($Ud,$Ud),$d),"<label><input disabled type='checkbox'>".lang(68)."</label> ";echo"<td><td>\n";}$Xf=1;foreach($I["indexes"]as$u){if(!$_POST["drop_col"]||$Xf!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$Xf][type]",array(-1=>"")+$xf,$u["type"],on('change','indexesChangeType',$zh),"label-type");if($uf)echo"<td$mf>".html_select("indexes[$Xf][algorithm]",array_merge(array(""),$uf),$u['algorithm'],"","label-algorithm");echo"<td>";ksort($u["columns"]);$r=1;foreach($u["columns"]as$w=>$d){echo"<span>".select_input(" name='indexes[$Xf][columns][$r]' title='".lang(57)."'".on('change','indexesChangeColumn',$zh),($m&&($d==""||$m[$d])?array_combine($Ud,$Ud):array()),$d)," <span$mf>",($wg?"<input type='number' name='indexes[$Xf][lengths][$r]' class='size' value='".h(idx($u["lengths"],$w))."' title='".lang(129)."'>":"");if($pi){$oi=idx($u["opclasses"],$w);echo
html_select("indexes[$Xf][opclasses][$r]",array(""=>"(".lang(216).")")+array_combine($pi,$pi)+($oi!=""?array($oi=>$oi):array()),$oi),doc_link(array('pgsql'=>'indexes-opclass.html'));}echo(support("descidx")?checkbox("indexes[$Xf][descs][$r]",1,idx($u["descs"],$w),lang(68)):""),"<br>","</span></span>";$r++;}echo"<td><input name='indexes[$Xf][name]' value='".h($u["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$mf><input name='indexes[$Xf][partial]' value='".h($u["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$Xf]","x",lang(133),on('click','editingRemoveRow','indexes$1[type]'));}$Xf++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'',lang(18),'\'>
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$I=$_POST;if($_POST&&!$k&&!$_POST["add"]){$A=trim($I["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(217),drop_databases(array(DB)));}elseif($A!==DB){if(DB!=""){$_GET["db"]=$A;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".url_escape($A),lang(218),rename_database($A,(string)$I["collation"]));}else{$h=explode("\n",str_replace("\r","",$A));$Hl=true;$ng="";foreach($h
as$i){if(count($h)==1||$i!=""){if(!create_database($i,(string)$I["collation"]))$Hl=false;$ng=$i;}}restart_session();set_session("dbs",null);queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($ng),lang(219),$Hl);}}else{if(!$I["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($A).(preg_match('~^[a-z0-9_]+$~i',$I["collation"])?" COLLATE $I[collation]":""),substr(ME,0,-1),lang(220));}}$Al=(DB!=""?"alter":"create");page_header(DB!=""?lang(76):lang(137),$k,array(),h(DB),false,doc_link(array('sql'=>"$Al-database.html",'mariadb'=>(DB!=""?"":"$Al-database"),'pgsql'=>"sql-$Al"."database.html",'cockroach'=>"$Al-database",'mssql'=>"t-sql/statements/$Al-database-transact-sql",'oracle'=>"sqlrf/".strtoupper($Al)."-USER.html",)));$Eb=collations();$A=DB;if($_POST)$A=$I["name"];elseif(DB!="")$I["collation"]=db_collation(DB,$Eb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$Ae){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$Ae,$_)&&$_[1]){$A=stripcslashes(idf_unescape("`$_[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($A,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($A).'</textarea><br>':'<input name="name" autofocus value="'.h($A).'" data-maxlength="64" autocapitalize="off">')."\n",($Eb?html_select("collation",array(""=>"(".lang(124).")")+$Eb,$I["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):"")."\n",'<input type=\'submit\' value=\'',lang(18),'\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".lang(149)."'".confirm(lang(204,DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",lang(131))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$I=$_POST;if($_POST&&!$k){$z=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$z,lang(221));else{$A=trim($I["name"]);$z
.=url_escape($A);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($A),$z,lang(222));elseif($_GET["ns"]!=$A)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($A),$z,lang(223));else
redirect($z);}}$Al=($_GET["ns"]!=""?"alter":"create");page_header($_GET["ns"]!=""?lang(77):lang(78),$k,array(),"",false,doc_link(array('pgsql'=>"sql-$Al"."schema.html",'cockroach'=>"$Al-schema",'mssql'=>"t-sql/statements/create-schema-transact-sql",)));if(!$I)$I["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($I["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'',lang(18),'\'>
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".lang(149)."'".confirm(lang(204,$_GET["ns"])).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ca=($_GET["name"]?:$_GET["call"]);$uk=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$pk=routine($_GET["call"],$uk);page_header(lang(224).": ".h($ca),$k,"#routines","",!$pk,(isset($_GET["callf"])?"":doc_link(array('sql'=>"call.html",'pgsql'=>"sql-call.html",'cockroach'=>"call",'mssql'=>"t-sql/language-elements/execute-transact-sql",))));$pf=array();$Ii=array();foreach($pk["fields"]as$r=>$l){if(substr($l["inout"],-3)=="OUT"&&JUSH=='sql')$Ii[$r]="@".idf_escape($l["field"])." AS ".idf_escape($l["field"]);if(!$l["inout"]||preg_match('~^(IN|OUTPUT)~',$l["inout"]))$pf[]=$r;}if(!$k&&$_POST){$jb=array();foreach($pk["fields"]as$w=>$l){$W="";if(in_array($w,$pf)){$W=process_input($l);if($W===false)$W="''";if(isset($Ii[$w]))connection()->query("SET @".idf_escape($l["field"])." = $W");}if(isset($Ii[$w]))$jb[]="@".idf_escape($l["field"]);elseif(in_array($w,$pf))$jb[]=$W;}$Fa=implode(", ",$jb);$F=(isset($_GET["callf"])||JUSH!="mssql"?(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($pk["returns"],"type")=="record"?"* FROM ":"").table($ca)."($Fa)":"EXEC ".table($ca).($Fa!=""?" $Fa":""));$_l=microtime(true);$G=connection()->multi_query($F);$ta=connection()->affected_rows;echo
adminer()->selectQuery($F,$_l,!$G);if(!$G)echo"<p class='error'>".adminer()->error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$G=connection()->store_result();if(is_object($G))print_select_result($G,$g);else
echo"<p class='message'>".lang(225,$ta)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($Ii)print_select_result(connection()->query("SELECT ".implode(", ",$Ii)));}}echo'
<form action="" method="post">
';if($pf){echo"<table class='layout'>\n";foreach($pf
as$w){$l=$pk["fields"][$w];$A=$l["field"];echo"<tr><th>".adminer()->fieldName($l);$X=idx($_POST["fields"],$A);if($X!=""){if($l["type"]=="set")$X=implode(",",$X);}input($l,$X,idx($_POST["function"],$A,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'',lang(224),'\'>
',input_token(),'</form>

',adminer()->commentValue($uk,$pk['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$A=$_GET["name"];$I=$_POST;if($_POST&&!$k&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$I["source"]=array_filter($I["source"],'strlen');ksort($I["source"]);$hm=array();foreach($I["source"]as$w=>$W)$hm[$w]=$I["target"][$w];$I["target"]=$hm;}$Ub=object_name("FOREIGN",$a,$I["source"]);if(JUSH=="sqlite")$G=recreate_table($a,$a,array(),array(),array(" $A"=>($I["drop"]?"":" ".format_foreign_key($I,$Ub))));else{$b="ALTER TABLE ".table($a);$G=($A==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($A)));if(!$I["drop"])$G=queries("$b ADD".format_foreign_key($I,$Ub));}queries_redirect(ME."table=".url_escape($a),($I["drop"]?lang(226):($A!=""?lang(227):lang(228))),$G);if(!$I["drop"])$k=lang(229);}$Ph=false;if(!$_POST&&$A!=""){$le=foreign_keys($a);$I=idx($le,$A,array());$Ph=!$I;}page_header(($A!=""?lang(230):lang(161)),$k,array("table"=>$a),h($A!=""?$A:$a),$Ph,doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'cockroach'=>"foreign-key",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"sqlrf/constraint.html",)));if($_POST){ksort($I["source"]);if($_POST["change"]||$_POST["change-js"])$I["target"]=array();else$I["source"][]="";}elseif($A!="")$I["source"][]="";else{$I["table"]=$a;$I["source"]=array("");}echo'
<form action="" method="post">
';$pl=array_keys(fields($a));if($I["db"]!="")connection()->select_db($I["db"]);if($I["ns"]!=""){$Di=get_schema();set_schema($I["ns"]);}$Uj=array_keys(array_filter(table_status('',true),function(array$R){return!$R["dependent"]&&fk_support($R);}));$hm=array_keys(fields(in_array($I["table"],$Uj)?$I["table"]:reset($Uj)));$c=on('change','foreignChange');echo"<p><label>".lang(231).": ".html_select("table",$Uj,$I["table"],$c)."</label>\n";if(support("scheme")){$Ak=array_filter(adminer()->schemas(),function($K){return!information_schema(DB,$K);});echo"<label>".lang(88).": ".html_select("ns",$Ak,$I["ns"]!=""?$I["ns"]:$_GET["ns"],$c)."</label>";if($I["ns"]!="")set_schema($Di);}elseif(JUSH!="sqlite"){$xc=array();foreach(adminer()->databases()as$i){if(!information_schema($i))$xc[]=$i;}echo"<label>".lang(87).": ".html_select("db",$xc,$I["db"]!=""?$I["db"]:$_GET["db"],$c)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'',lang(232),'\'></noscript>
<table>
<thead><tr><th id="label-source">',lang(158),'<th id="label-target">',lang(159),'<tbody>
';$Xf=0;foreach($I["source"]as$w=>$W){echo"<tr>","<td>".html_select("source[".(+$w)."]",array(-1=>"")+$pl,$W,($Xf==count($I["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$w)."]",$hm,idx($I["target"],$w),"","label-target");$Xf++;}echo'</table>
<p>
<label>',lang(126),': ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$I["on_delete"]),'</label>
<label>',lang(125),': ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$I["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",array('NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'),$I["deferrable"]):''),'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'',lang(233),'\'></noscript>
';if($A!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$A)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$I=$_POST;$Ei="VIEW";if(JUSH=="pgsql"&&$a!=""){$O=table_status1($a);$Ei=strtoupper($O["Engine"]);}if($_POST&&!$k){$A=trim($I["name"]);$Ha=" AS\n$I[select]";$Eg=ME."table=".url_escape($A);$ch=lang(234);$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$A&&JUSH!="sqlite"&&$T=="VIEW"&&$Ei=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($A).$Ha,$Eg,$ch);else{$lm="adminer_".uniqid();drop_create("DROP $Ei ".table($a),"CREATE $T ".table($A).$Ha,"DROP $T ".table($A),"CREATE $T ".table($lm).$Ha,"DROP $T ".table($lm),($_POST["drop"]?substr(ME,0,-1):$Eg),lang(235),$ch,lang(236),$a,$A);}}$Ph=false;if(!$_POST&&$a!=""){$I=view($a);$Ph=!$I["select"];$I["name"]=$a;$I["materialized"]=($Ei!="VIEW");if(!$k)$k=adminer()->error();}page_header(($a!=""?lang(52):lang(237)),$k,array("table"=>$a),h($a),$Ph,doc_link(array('sql'=>"create-view.html",'pgsql'=>"sql-createview.html",'cockroach'=>"create-view",'mssql'=>"t-sql/statements/create-view-transact-sql",'sqlite'=>"lang_createview.html",'oracle'=>"sqlrf/CREATE-VIEW.html",)));echo'
<form action="" method="post">
<p>',lang(214),': <input name="name" value="',h($I["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$I["materialized"],lang(152)):""),'<p>';textarea("select",$I["select"]);echo'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$Kf=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$Cl=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$I=$_POST;if($_POST&&!$k){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),lang(238));elseif(in_array($I["INTERVAL_FIELD"],$Kf)&&isset($Cl[$I["STATUS"]])){$zk="\nON SCHEDULE ".($I["INTERVAL_VALUE"]?"EVERY ".q($I["INTERVAL_VALUE"])." $I[INTERVAL_FIELD]".($I["STARTS"]?" STARTS ".q($I["STARTS"]):"").($I["ENDS"]?" ENDS ".q($I["ENDS"]):""):"AT ".q($I["STARTS"]))." ON COMPLETION".($I["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?lang(239):lang(240)),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$zk.($aa!=$I["EVENT_NAME"]?"\nRENAME TO ".idf_escape($I["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($I["EVENT_NAME"]).$zk)."\n".$Cl[$I["STATUS"]]." COMMENT ".q($I["EVENT_COMMENT"]).rtrim(" DO\n$I[EVENT_DEFINITION]",";").";"));}}$Ph=false;if(!$I&&$aa!=""){$J=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$Ph=!$J;$I=reset($J);}page_header(($aa!=""?lang(241).": ".h($aa):lang(242)),$k,"#events","",$Ph,doc_link(array('sql'=>"create-event.html")));echo'
<form action="" method="post">
<table class="layout">
<tr><th>',lang(214),'<td><input name="EVENT_NAME" value="',h($I["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">',lang(243),'<td><input name="STARTS" value="',h("$I[EXECUTE_AT]$I[STARTS]"),'">
<tr><th title="datetime">',lang(244),'<td><input name="ENDS" value="',h($I["ENDS"]),'">
<tr><th>',lang(245),'<td><input type="number" name="INTERVAL_VALUE" value="',h($I["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$Kf,$I["INTERVAL_FIELD"]),'<tr><th>',lang(140),'<td>',html_select("STATUS",$Cl,$I["STATUS"]),'<tr><th>',lang(59),'<td><input name="EVENT_COMMENT" value="',h($I["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$I["ON_COMPLETION"]=="PRESERVE",lang(246)),'</table>
<p>';textarea("EVENT_DEFINITION",$I["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ca=($_GET["name"]?:$_GET["procedure"]);$pk=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$I=$_POST;$I["fields"]=(array)$I["fields"];if($_POST&&!process_fields($I["fields"])&&!$k){foreach($I["fields"]as$w=>$l){if($l["field"]=="")unset($I["fields"][$w]);}$hi=routine($_GET["procedure"],$pk);$fi=($hi?routine_id($ca,$hi):"");$Gh=routine_id($I["name"],$I);$gc=create_routine($pk,$I);$Eg=substr(ME,0,-1);$ch=lang(247);if(!$_POST["drop"]&&$fi==$Gh&&connection()->flavor!="mysql")queries_redirect($Eg,$ch,queries(substr_replace($gc,(JUSH=="mssql"?' OR ALTER':' OR REPLACE'),6,0)));else{$lm="adminer_".uniqid();drop_create("DROP $pk $fi",$gc,"DROP $pk $Gh",create_routine($pk,array("name"=>$lm)+$I),"DROP $pk ".routine_id($lm,$I),$Eg,lang(248),$ch,lang(249),$ca,$I["name"]);}}$Ph=false;if(!$_POST&&$ca!=""){$I=routine($_GET["procedure"],$pk);$Ph=!$I;$I["name"]=$ca;}$sk=strtolower($pk);page_header(($ca!=""?(isset($_GET["function"])?lang(165):lang(250)).": ".h($ca):(isset($_GET["function"])?lang(251):lang(252))),$k,"#routines","",$Ph,doc_link(array('sql'=>"create-procedure.html",'mariadb'=>"create-$sk/",'pgsql'=>"sql-create$sk.html",'cockroach'=>"create-$sk",'mssql'=>"t-sql/statements/create-$sk-transact-sql",)));if(!$_POST&&$ca=="")$I["language"]="sql";$Eb=(JUSH=="sql"?flat_collations():array());$qk=routine_languages();echo($Eb?"<datalist id='collations'>".optionlist($Eb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>',lang(214),': <input name="name" value="',h($I["name"]),'" data-maxlength="64" autocapitalize="off">
',($qk?"<label>".lang(24).": ".html_select("language",array_keys($qk),$I["language"],on('change','routineLanguage',$qk))."</label>\n":""),'<input type=\'submit\' value=\'',lang(18),'\'>
<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($I["fields"],$Eb,$pk);if(isset($_GET["function"])){echo"<tr><td>".lang(253);edit_type("returns",(array)$I["returns"],$Eb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$I["definition"],20,80,($qk[$I["language"]]?:JUSH));echo'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
';if($ca!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$ca)),'>
';$tk=routine_options($pk);if($tk){$ui=false;foreach($tk
as$w=>$Y){$j=($Y?reset($Y):"");$I["options"][$w]=idx($I["options"],$w,$j);if($I["options"][$w]!=$j)$ui=true;}print_fieldset("options",lang(130),$ui);echo"<table class='layout'>\n";foreach($tk
as$w=>$Y){$ig="label-option-$w";$tm=str_replace("_"," ",$w);$L=array();foreach($Y
as$X)$L[$X]=(strpos($X,"$tm ")===0?substr($X,strlen($tm)+1):$X);echo"<tr><th id='$ig'>$tm<td>".($L?html_select("options[$w]",$L,$I["options"][$w],"",$ig):"<input name='options[$w]' value='".h($I["options"][$w])."' aria-labelledby='$ig' autocapitalize='off'>")."\n";}echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$ea=$_GET["sequence"];$I=$_POST;if($_POST&&!$k){$z=substr(ME,0,-1);$A=trim($I["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($ea),$z,lang(254));elseif($ea=="")query_redirect("CREATE SEQUENCE ".idf_escape($A),$z,lang(255));elseif($ea!=$A)query_redirect("ALTER SEQUENCE ".idf_escape($ea)." RENAME TO ".idf_escape($A),$z,lang(256));else
redirect($z);}$Ph=(!$_POST&&$ea!=""&&!get_val("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." AND relname = ".q($ea)));page_header(($ea!=""?lang(257).": ".h($ea):lang(258)),$k,"#sequences","",$Ph,doc_link(array('pgsql'=>"sql-createsequence.html",'cockroach'=>"create-sequence",)));if(!$I)$I["name"]=$ea;echo'
<form action="" method="post">
<p><input name="name" value="',h($I["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'',lang(18),'\'>
';if($ea!="")echo"<input type='submit' name='drop' value='".lang(149)."'".confirm(lang(204,$ea)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){function
enum_values($Cc){$X="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$X.'(?:\s*,\s*'.$X.')*)\s*\)$~i',$Cc,$_))return
null;preg_match_all('~'.$X.'~',$_[1],$Kg);return$Kg[0];}function
add_enum_values($T,$di,$Eh){$ji=enum_values($di);$Lh=enum_values($Eh);if($ji===null||$Lh===null)return
null;$H=array();$r=0;foreach($Lh
as$X){if($X===idx($ji,$r))$r++;else$H[]="ALTER TYPE ".idf_escape($T)." ADD VALUE $X".($r<count($ji)?" BEFORE ".$ji[$r]:"");}return($r==count($ji)?$H:null);}$fa=$_GET["type"];$I=$_POST;$Rm=($fa!=""?array_search($fa,types(true)):0);$T=($Rm?type_definition(+$Rm):array());$Uh=($T["kind"]=='d'?"DOMAIN":"TYPE");if($_POST&&!$k){$z=substr(ME,0,-1);$A=trim($I["name"]);$Ha=trim(str_replace("\r","",$I["as"]));$Ih=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Ha)?"DOMAIN":"TYPE");$ch=lang(259);$b=(!$_POST["drop"]&&$fa!=""&&$Ih==$Uh?($Ha==$T["definition"]?array():add_enum_values($fa,$T["definition"],$Ha)):null);if($b!==null){if($fa!=$A)$b[]="ALTER $Uh ".idf_escape($fa)." RENAME TO ".idf_escape($A);if(!$b)redirect($z);$Nd=false;foreach($b
as$F){if(!queries($F)){$Nd=true;break;}}queries_redirect($z,$ch,!$Nd);}else
drop_create("DROP $Uh ".idf_escape($fa),"CREATE $Ih ".idf_escape($A)." $Ha","","","",$z,lang(260),$ch,lang(261),$fa,$A);}page_header(($fa!=""?lang(262).": ".h($fa):lang(263)),$k,"#user-types","",($Rm===false),doc_link(array('pgsql'=>"sql-createtype.html",'cockroach'=>"create-type",)));if(!$I){$I["name"]=$fa;$I["as"]=($fa!=""?$T["definition"]:"AS ");}echo'
<form action="" method="post">
<p>
',lang(214).": <input name='name' value='".h($I['name'])."' autocapitalize='off'>\n";textarea("as",$I["as"]);echo"<p><input type='submit' value='".lang(18)."'>\n";if($fa!="")echo"<input type='submit' name='drop' value='".lang(149)."'".confirm(lang(204,$fa)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$A="$_GET[name]";$I=$_POST;if($I&&!$k){$Eg=ME."table=".url_escape($a);$fh=lang(264);$dh=lang(265);$eh=lang(266);if(JUSH=="sqlite")queries_redirect($Eg,($I["drop"]?$fh:($A!=""?$dh:$eh)),recreate_table($a,$a,array(),array(),array(),"",array(),"$A",($I["drop"]?"":$I["clause"])));else{$b="ALTER TABLE ".table($a);$qb=" CHECK ($I[clause])";$lm="adminer_".uniqid();drop_create("$b DROP CONSTRAINT ".idf_escape($A),"$b ADD".($I["name"]!=""?" CONSTRAINT ".idf_escape($I["name"]):"").$qb,"$b DROP CONSTRAINT ".idf_escape($I["name"]),"$b ADD CONSTRAINT ".idf_escape($lm).$qb,"$b DROP CONSTRAINT ".idf_escape($lm),$Eg,$fh,$dh,$eh,$A,$I["name"]);}}$Ph=false;if(!$I){$ub=driver()->checkConstraints($a);$Ph=($A!=""&&!$ub[$A]);$I=array("name"=>($A!=""?$A:object_name("CHECK",$a,array())),"clause"=>$ub[$A]);}page_header(($A!=""?lang(267):lang(163)),$k,array("table"=>$a),h($A!=""?$A:$a),$Ph,doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'cockroach'=>"check",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",)));echo'
<form action="" method="post">
';if(JUSH!="sqlite")echo'<p>'.lang(214).': <input name="name" value="'.h($I["name"]).'" data-maxlength="64" autocapitalize="off">';echo'<p>';textarea("clause",$I["clause"]);echo'<p><input type=\'submit\' value=\'',lang(18),'\'>
';if($A!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$A)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$A="$_GET[name]";$Mm=trigger_options();$I=trigger($A,$a);$Ph=($A!=""&&!$I);$yh=str_replace("{table}",$a,adminer()->namePattern("TRIGGER"));$I+=array("Trigger"=>strtr($yh,array("{timing}"=>"b","{event}"=>"i","{columns}"=>"","{type}"=>"row")));if($_POST){if(!$k&&in_array($_POST["Timing"],$Mm["Timing"])&&in_array($_POST["Event"],$Mm["Event"])&&in_array($_POST["Type"],$Mm["Type"])){$ki=" ON ".table($a);$Zc="DROP TRIGGER ".idf_escape($A).(JUSH=="pgsql"?$ki:"");$Eg=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($Zc,$Eg,lang(268));else{if($A!="")queries($Zc);queries_redirect($Eg,($A!=""?lang(269):lang(270)),queries(create_trigger($ki,$_POST)));if($A!="")queries(create_trigger($ki,$I+array("Type"=>reset($Mm["Type"]))));}}$I=$_POST;}page_header(($A!=""?lang(271):lang(166)),$k,array("table"=>$a),h($A!=""?$A:$a),$Ph,doc_link(array('sql'=>"create-trigger.html",'pgsql'=>"sql-createtrigger.html",'cockroach'=>"create-trigger",'mssql'=>"t-sql/statements/create-trigger-transact-sql",'sqlite'=>"lang_createtrigger.html",'oracle'=>"lnpls/CREATE-TRIGGER-statement.html",)));$Ah=strtr(preg_quote($yh),array('\{timing\}'=>'[abi]','\{event\}'=>'[iud]*','\{columns\}'=>'.*','\{type\}'=>'(row|statement)'));$Km=on('change','triggerChange',"^$Ah$",$yh);$Xh=on('input','triggerChange',"^$Ah$",$yh);echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>',lang(272),'<td>',html_select("Timing",$Mm["Timing"],$I["Timing"],$Km),'<tr><th>',lang(273),'<td>',html_select("Event",$Mm["Event"],$I["Event"],$Km),(in_array("UPDATE OF",$Mm["Event"])?" <input name='Of' value='".h($I["Of"])."' class='hidden'$Xh>":""),'<tr><th>',lang(58),'<td>',html_select("Type",$Mm["Type"],$I["Type"],$Km),'<tr><th>',lang(214),'<td><input name="Trigger" value="',h($I["Trigger"]),'" data-maxlength="64" autocapitalize="off">
</table>
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$I["Statement"]);echo'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
';if($A!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,$A)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){function
grant($Ae,array$Hj,$e,$ki){if(!$Hj)return
true;if($Hj==array("ALL PRIVILEGES","GRANT OPTION"))return($Ae=="GRANT"?queries("$Ae ALL PRIVILEGES$ki WITH GRANT OPTION"):queries("$Ae ALL PRIVILEGES$ki")&&queries("$Ae GRANT OPTION$ki"));return
queries("$Ae ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$Hj).$e).$ki);}$ga=$_GET["user"];$Hj=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$I){foreach(explode(",",($I["Privilege"]=="Grant option"?"":$I["Context"]))as$Zb)$Hj[$Zb=="File access on server"?"Server Admin":$Zb][$I["Privilege"]]=$I["Comment"];}unset($Hj["Server Admin"]["Usage"]);foreach($Hj["Tables"]as$w=>$W)unset($Hj["Databases"][$w]);$Fh=array();if($_POST){foreach($_POST["objects"]as$w=>$W)$Fh[$W]=(array)$Fh[$W]+idx($_POST["grants"],$w,array());}$Be=array();$G=(isset($_GET["host"])?connection()->query("SHOW GRANTS FOR ".q($ga)."@".q($_GET["host"])):null);$Ph=(isset($_GET["host"])&&!$G);if($G){while($I=$G->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$I[0],$_)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$_[1],$Kg,PREG_SET_ORDER)){foreach($Kg
as$W){if($W[1]!="USAGE")$Be["$_[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$I[0]))$Be["$_[2]$W[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$k){$ii=(isset($_GET["host"])?q($ga)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $ii",ME."privileges=",lang(274));else{$Kh=q($_POST["user"])."@".q($_POST["host"]);$cj=$_POST["pass"];$ic=false;$G=true;if($ii!=$Kh){$ic=queries("CREATE USER $Kh IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($cj));$G=$ic;}elseif($cj!="")$G=queries("SET PASSWORD FOR $Kh = ".(min_version(8,99)||$_POST["hashed"]?q($cj):"PASSWORD(".q($cj).")"));if($G){$lk=array();foreach($Fh
as$Uh=>$Ae){if(isset($_GET["grant"]))$Ae=array_filter($Ae);$Ae=array_keys($Ae);if(isset($_GET["grant"]))$lk=array_diff(array_keys(array_filter($Fh[$Uh],'strlen')),$Ae);elseif($ii==$Kh){$ei=array_keys((array)$Be[$Uh]);$lk=array_diff($ei,$Ae);$Ae=array_diff($Ae,$ei);unset($Be[$Uh]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Uh,$_)&&(!grant("REVOKE",$lk,$_[2]," ON $_[1] FROM $Kh")||!grant("GRANT",$Ae,$_[2]," ON $_[1] TO $Kh"))){$G=false;break;}}}if($G&&isset($_GET["host"])){if($ii!=$Kh)queries("DROP USER $ii");elseif(!isset($_GET["grant"])){foreach($Be
as$Uh=>$lk){if(preg_match('~^(.+)(\(.*\))?$~U',$Uh,$_))grant("REVOKE",array_keys($lk),$_[2]," ON $_[1] FROM $Kh");}}}if($G&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(275):lang(276)),$G);if($ic)connection()->query("DROP USER $Kh");}}page_header((isset($_GET["host"])?lang(42).": ".h("$ga@$_GET[host]"):lang(174)),$k,array("privileges"=>array('',lang(80))),"",$Ph,doc_link(array('sql'=>"grant.html",'mariadb'=>"grant")));$I=$_POST;if($I)$Be=$Fh;else{$I=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$Be[(DB==""||$Be?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>',lang(40),'<td><input name="host" data-maxlength="60" value="',h($I["host"]),'" autocapitalize="off">
<tr><th>',lang(42),'<td><input name="user" data-maxlength="80" value="',h($I["user"]),'" autocapitalize="off">
<tr><th>',lang(43),'<td><input name="pass" id="pass" value="',h($I["pass"]),'" autocomplete="new-password">
',($I["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$I["hashed"],lang(277),on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".lang(80);$r=0;foreach($Be
as$Uh=>$Ae){echo'<th>'.($Uh!="*.*"?"<input name='objects[$r]' value='".h($Uh)."' size='10' autocapitalize='off'>":input_hidden("objects[$r]","*.*")."*.*");$r++;}echo"<tbody>\n";foreach(array(""=>"","Server Admin"=>lang(40),"Databases"=>lang(44),"Tables"=>lang(154),"Procedures"=>lang(278),)as$Zb=>$Fc){foreach((array)$Hj[$Zb]as$Gj=>$Jb){echo"<tr><td".($Fc?">$Fc<td":" colspan='2'").' lang="en" title="'.h($Jb).'">'.h($Gj);$r=0;foreach($Be
as$Uh=>$Ae){$A="'grants[$r][".h(strtoupper($Gj))."]'";$X=$Ae[strtoupper($Gj)];if($Zb=="Server Admin"&&$Uh!=(isset($Be["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$A><option><option value='1'".($X?" selected":"").">".lang(279)."<option value='0'".($X=="0"?" selected":"").">".lang(280)."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$A value='1'".($X?" checked":"").($Gj=="All privileges"?" id='grants-$r-all'":($Gj=="Grant option"?"":on('click','grantsClick',"grants-$r-all"))).">","</label>";$r++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'',lang(18),'\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'',lang(149),'\'',confirm(lang(204,"$ga@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$k){$fg=0;foreach((array)$_POST["kill"]as$W){if(adminer()->killProcess($W))$fg++;}queries_redirect(ME."processlist=",lang(281,$fg),$fg||!$_POST["kill"]);}}page_header(lang(138),$k);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$r=-1;foreach(adminer()->processList()as$r=>$I){if(!$r){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($I
as$w=>$W)echo"<th>$w".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($w),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"refrn/V-SESSION.html",));echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$I[JUSH=="sql"?"Id":"pid"],0):"");foreach($I
as$w=>$W)echo"<td>".($W!=""&&((JUSH=="sql"&&$w=="Info"&&preg_match("~Query|Killed~",$I["Command"]))||(JUSH=="pgsql"&&$w=="query")||(JUSH=="oracle"&&$w=="sql_text"))?"<code class='jush-".JUSH."' data-full='".h($W)."'>".shorten_utf8($W,100,"</code>").' <a href="'.h(($I["db"]!=""?preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($I["db"])."&":ME)."sql=".url_escape($W)).'">'.lang(282).'</a>'.' '.copy_icon():h($W));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo
format_number($r+1)."/".lang(283,max_connections()),"<p><input type='submit' value='".lang(284)."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif($_GET["select"]!=""){$a=$_GET["select"];$R=table_status1($a);$v=indexes($a);$m=fields($a);$le=column_foreign_keys($a);$ci=$R["Oid"];$nk=array();$e=array();$Ek=array();$xi=array();$om=null;foreach($m
as$w=>$l){$A=adminer()->fieldName($l);$_h=html_entity_decode(strip_tags($A),ENT_QUOTES);if(isset($l["privileges"]["select"])&&$A!=""){$e[$w]=$_h;if(is_shortable($l))$om=adminer()->selectLengthProcess();}if(isset($l["privileges"]["where"])&&$A!="")$Ek[$w]=$_h;if(isset($l["privileges"]["order"])&&$A!="")$xi[$w]=$_h;$nk+=$l["privileges"];}list($L,$q)=adminer()->selectColumnsProcess($e,$v);$L=array_unique($L);$q=array_unique($q);$Rf=count($q)<count($L);$Z=adminer()->selectSearchProcess($m,$v,$R);$wi=adminer()->selectOrderProcess($m,$v);$y=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$an=>$I){$Ha=convert_field($m[key($I)]);$L=array($Ha?:idf_escape(key($I)));$Z[]=where_check(bracket_escape($an,true),$m);$H=driver()->select($a,$L,$Z,$L);if($H)echo
first($H->fetch_row());}exit;}$Cj=$dn=array();foreach($v
as$u){if($u["type"]=="PRIMARY"){$Cj=array_flip($u["columns"]);$dn=($L?$Cj:array());foreach($dn
as$w=>$W){if(in_array(idf_escape($w),$L))unset($dn[$w]);}break;}}if($ci&&!$Cj){$Cj=$dn=array($ci=>0);$v[]=array("type"=>"PRIMARY","columns"=>array($ci));}if($_POST&&!$k){$Kn=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ub=array();foreach($_POST["check"]as$qb)$ub[]=where_check($qb,$m);$Kn[]="((".implode(") OR (",$ub)."))";}$Mn=$Kn;$Kn=($Kn?"\nWHERE ".implode(" AND ",$Kn):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$Ik=($L?:array("*"));$cc=convert_fields($e,$m,$L);if($cc)$Ik[]=substr($cc,2);$F="";if(is_array($_POST["check"])&&!$Cj){$se=implode(", ",$Ik)."\nFROM ".table($a);$Ee=($q&&$Rf?"\nGROUP BY ".implode(", ",$q):"").($wi?"\nORDER BY ".implode(", ",$wi):"");$Xm=array();foreach($_POST["check"]as$W)$Xm[]="(SELECT".limit($se,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m).$Ee,1).")";$F=implode(" UNION ALL ",$Xm);}adminer()->dumpData($a,"table",$F,$Ik,$Mn,($Rf?$q:array()),$wi);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$le)){if($_POST["save"]||$_POST["delete"]){$G=true;$ta=0;$Ya=false;$N=array();if(!$_POST["delete"]){foreach($m
as$A=>$W){$t=bracket_escape($A);if(isset($_POST["fields"][$t])||$_FILES["fields-$t"]){$W=process_input($m[$A]);if($W!==null&&($_POST["clone"]||$W!==false))$N[idf_escape($A)]=($W!==false?$W:idf_escape($A));}}}if($_POST["delete"]||$N){$F=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($N)).")\nSELECT ".implode(", ",$N)."\nFROM ".table($a):"");if($_POST["all"]||($Cj&&is_array($_POST["check"]))||$Rf){$G=($_POST["delete"]?driver()->delete($a,$Kn):($_POST["clone"]?queries("INSERT $F$Kn".driver()->insertReturning($a)):driver()->update($a,$N,$Kn)));$ta=connection()->affected_rows;if(is_object($G))$ta+=$G->num_rows;}else{$Ya=count((array)$_POST["check"])>1&&driver()->begin();foreach((array)$_POST["check"]as$W){$Jn="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m);$G=($_POST["delete"]?driver()->delete($a,$Jn,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$Jn)):driver()->update($a,$N,$Jn,1)));if(!$G)break;$ta+=connection()->affected_rows;}if($Ya&&$G&&!driver()->commit())$G=false;}}$ch=lang(175,$ta);if($_POST["clone"]&&$G&&$ta==1){$pg=last_id($G);if($pg)$ch=lang(197," $pg");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$ch,$G);if($Ya)driver()->rollback();if(!$_POST["delete"]){$tj=(array)$_POST["fields"];edit_form($a,array_intersect_key($m,$tj),$tj,!$_POST["clone"],$k);page_footer();exit;}}elseif(!$_POST["import"]){$G=true;$ta=0;$Ya=count((array)$_POST["val"])>1&&driver()->begin();foreach((array)$_POST["val"]as$an=>$I){$N=array();foreach($I
as$w=>$W){$w=bracket_escape($w,true);$N[idf_escape($w)]=(preg_match('~char|text~',$m[$w]["type"])||$W!=""?adminer()->processInput($m[$w],$W):"NULL");}$G=driver()->update($a,$N," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($an,true),$m),($Rf||$Cj?0:1)," ");if(!$G)break;$ta+=connection()->affected_rows;}if($Ya)$G=$G&&driver()->commit();queries_redirect(remove_from_uri(),lang(175,$ta),$G);if($Ya)driver()->rollback();}else{save_settings(array("format"=>$_POST["separator"]),"adminer_import");$Vd=get_file("csv_file",true);if(!is_string($Vd))$k=upload_error($Vd);elseif(!preg_match('~~u',$Vd))$k=lang(285);else{$Fb=array_keys($m);$Ok=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$mc=parse_csv($Vd,$Ok);$ta=count($mc);driver()->begin();$J=array();foreach($mc
as$w=>$Y){if(!$w&&!array_diff($Y,$Fb)){$Fb=$Y;$ta--;}else{$N=array();foreach($Y
as$r=>$Bb)$N[idf_escape($Fb[$r])]=($Bb==""&&$m[$Fb[$r]]["null"]?"NULL":q(csv_value($Bb)));$J[]=$N;}}$G=(!$J||driver()->insertUpdate($a,$J,$Cj));if($G)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang(286,$ta),$G);driver()->rollback();}}}}$Vl=adminer()->tableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(62).": $Vl",$k,array(),"",(!$m&&support("table")),($m?doc_link(array(JUSH=>driver()->tableHelp($a,is_view($R)))):""));$N=null;if(isset($nk["insert"])||!support("table")){$N="";foreach((array)$_GET["where"]as$W){$X=$W["val"];if(is_array($X))$X=(count($X)==1&&preg_match('~^val-(.*)~s',reset($X),$_)?$_[1]:"");if($W["col"]!=""&&$X!=""&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$X)))))$N
.="&set[".url_escape(bracket_escape($W["col"]))."]=".url_escape($X);}}adminer()->selectLinks($R,$N);if(!$e&&support("table"))echo"<p class='error'>".lang(287)."\n";else{echo"<form action='' id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($L,$e);adminer()->selectSearchPrint($Z,$Ek,$v,$R);adminer()->selectOrderPrint($wi,$xi,$v);adminer()->selectLimitPrint($y);if($om!==null)adminer()->selectLengthPrint($om);adminer()->selectActionPrint($v);echo"</form>\n";foreach((array)$_GET["where"]as$W){if($W["op"]=="SQL"&&!in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"))){echo"<p class='error'>".lang(119).' '.lang(120)."\n";page_footer();exit;}}$C=$_GET["page"];$oe=null;if($C=="last"){$oe=get_val(count_rows($a,$Z,$Rf,$q));$C=floor(max(0,intval($oe)-1)/$y);}$Hk=$L;$De=$q;if(!$Hk){$Hk[]="*";$cc=convert_fields($e,$m,$L);if($cc)$Hk[]=substr($cc,2);}foreach($L
as$w=>$W){$l=$m[idf_unescape($W)];if($l&&($Ha=convert_field($l)))$Hk[$w]="$Ha AS $W";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$w=>$W){if(isset($Hk[$w])&&$W["fun"])$Hk[$w].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if(!$Rf&&$dn){foreach($dn
as$w=>$W){$Hk[]=idf_escape($w);if($De)$De[]=idf_escape($w);}}$G=driver()->select($a,$Hk,$Z,$De,$wi,$y,$C,true);if(!is_object($G))echo"<p class='error'>".(adminer()->error()?:lang(26))."\n";else{if(JUSH=="mssql"&&$C)$G->seek($y*$C);$md=array();$J=array();while($I=$G->fetch_assoc()){if($C&&JUSH=="oracle")unset($I["RNUM"]);$J[]=$I;}$Pe=($y&&(support("cursor")?$_GET["next"]!="":count($J)>=$y));if(is_ajax()&&$Pe)header("X-Next-Page: ".pagination_href($C+1));if($_GET["modify"]&&$J){$Tg=max_input_vars(count($J[0])+1,20);echo($Tg&&count($J)>$Tg?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form action='' method='post' enctype='multipart/form-data'".on_upload_progress($jn).">\n";if($_GET["page"]!="last"&&$y&&$q&&$Rf&&JUSH=="sql")$oe=get_val(" SELECT FOUND_ROWS()");if(!$J)echo"<p class='message'>".lang(16)."\n";else{$Ua=adminer()->backwardKeys($a,$Vl);$jk=array();reset($L);foreach($J[0]as$w=>$W){if(!isset($dn[$w])){$W=idx($_GET["columns"],key($L))?:array();$jk[$w]=array("fun"=>$W["fun"],"col"=>($L?$W["col"]:$w));next($L);}}echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$q&&$L?"":"<td class='hover check sticky'><input type='checkbox' id='all-page' class='jsonly' title='".lang(288)."'".on('click','formCheck','^check').">");$Bh=array();$Rj=1;foreach($jk
as$w=>$W){$l=$m[$W["col"]];$A=($l?adminer()->fieldName($l,$Rj):($W["fun"]?"*":h($w)));if($A!=""){$Rj++;$Bh[$w]=$A;$d=idf_escape($w);$ff=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($w);$Fc="&desc[0]=1";$ml=preg_replace('~ DESC( NULLS LAST)?$~','',$wi[0]);$ol=($ml==$d||$ml==$w);echo"<th id='th[".h(bracket_escape($w))."]'".($ol?" aria-sort='".($ml==$wi[0]?"ascending":"descending")."'":"").">";$xe=apply_sql_function(h($W["fun"]),$A);$nl=isset($l["privileges"]["order"])||$W["fun"];echo($nl?"<a href='".h($ff.($ol&&$ml==$wi[0]?$Fc:''))."'>$xe</a>":$xe);$bh=($nl?"<a href='".h($ff.$Fc)."' title='".lang(68)."' class='text'> ↓</a>":'');if(!$W["fun"]&&isset($l["privileges"]["where"]))$bh
.="<a href='#fieldset-search' title='".lang(65)."' class='text jsonly'".on('click','selectSearch',$w)."> =</a>";echo($bh?"<span class='column'>$bh</span>":"");}}$wg=array();if($_GET["modify"]){foreach($J
as$I){foreach($I
as$w=>$W)$wg[$w]=max($wg[$w],min(40,utf8_length((string)$W)));}}$af=array();$Ze=array();foreach((array)$_GET["where"]as$W){$W+=array("col"=>"","op"=>"","val"=>"");$Bb=$W["col"];$Dk=$W["val"];if(!is_array($Dk)&&($Dk!=""||preg_match('~NULL$~',$W["op"]))&&(!$W["op"]||in_array($W["op"],adminer()->operators($R)))){$yg=strtr(preg_quote($Dk),array("%"=>".*?","_"=>"."));$ij=array("LIKE %%"=>$yg,"ILIKE %%"=>$yg,"REGEXP"=>$Dk)+(JUSH=="pgsql"?array("~"=>$Dk,"~*"=>$Dk):array())+($Bb!=""?array():array("="=>'^'.preg_quote($Dk).'\z',"IN"=>'^(?:'.implode("|",array_map('preg_quote',array_map('trim',explode(",",$Dk)))).')\z',"LIKE"=>"^$yg\\z","ILIKE"=>"^$yg\\z","FIND_IN_SET"=>'(?<=^|,)'.preg_quote($Dk).'(?=,|\z)',));foreach(($Bb!=""?array($Bb=>$m[$Bb]):$m)as$A=>$l){if($Bb!=""||is_searchable($l,$W)){$ni=$W["op"]?:(!preg_match('~'.text_type().'~',$l["type"])?"IN":(preg_match('~%~',$Dk)?"LIKE":"LIKE %%"));if(isset($ij[$ni])){$xb=preg_match('~^ILIKE|\*$~',$ni)||($ni!="~"&&preg_match('~^(sql|mssql|sqlite)$~',JUSH));$af[$A][]="(?".($xb?"i":"").":$ij[$ni])";}elseif($ni=="IS NULL"&&$Bb=="")$Ze[$A]=true;}}}}echo($Ua?"<th>".lang(289):"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($J,$le)as$wh=>$I){$Zm=unique_array($J[$wh],$v);if(!$Zm){$Zm=array();foreach($J[$wh]as$w=>$W){if(!in_array(idx(idx($jk,$w,array()),"fun"),driver()->grouping))$Zm[$w]=$W;}}$an="";$r=0;foreach($Zm
as$w=>$W){$ik=idx($jk,$w,array());$xe=idx($ik,"fun","");$Bb=($xe?$ik["col"]:$w);$l=(array)$m[$Bb];$Qf=is_blob($l);if(!$xe&&strlen($W)>64&&driver()->md5(idf_escape($Bb),$l)){$xe="md5";$W=md5($Qf?(string)driver()->value($W,$l):$W);}if($xe){$an
.="&fun[$r]=".url_escape($xe)."&col[$r]=".url_escape($Bb).($W!==null?"&val[$r]=".url_escape($W===false?"f":$W):"");$r++;}else$an
.="&".($W!==null?"where[".url_escape(bracket_escape($Bb))."]=".url_escape($W===false?"f":$W):"null[]=".url_escape($Bb));}echo"<tr>".(!$q&&$L?"":"<td class='hover check sticky'>".($Rf||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$an)."' class='edit'>".lang(290)."</a> ").checkbox("check[]",substr($an,1),in_array(substr($an,1),(array)$_POST["check"])));foreach($I
as$w=>$W){if(isset($Bh[$w])){$xe=$jk[$w]["fun"];$Bb=$jk[$w]["col"];$l=(array)$m[$w];if($W!=""&&(!isset($md[$w])||$md[$w]!=""))$md[$w]=(is_mail($W)?$Bh[$w]:"");$z="";if(is_blob($l)&&$W!="")$z=ME.'download='.url_escape($a).'&field='.url_escape($w).$an;if(!$z&&$W!==null){foreach((array)$le[$w]as$o){if(count($le[$w])==1||end($o["source"])==$w){$z="";foreach($o["source"]as$r=>$pl)$z
.=where_link($r,$o["target"][$r],$J[$wh][$pl]);$z=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.url_escape($o["db"]),ME):ME).'select='.url_escape($o["table"]).$z;if($o["ns"])$z=preg_replace('~([?&]ns=)[^&]+~','\1'.url_escape($o["ns"]),$z);if(count($o["source"])==1)break;}}}if($xe=="count"&&$Bb==""){$z=ME."select=".url_escape($a);$r=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$Zm))$z
.=where_link($r++,$V["col"],$V["val"],$V["op"]);}foreach($Zm
as$ag=>$V){if(idx(idx($jk,$ag,array()),"fun")){$z="";break;}$z
.=where_link($r++,$ag,$V);}}$gf=select_value($W,$z,$l,$om,($xe?array():idx($af,$w,array())));if($W===null&&!$xe&&isset($Ze[$w]))$gf="<mark>$gf</mark>";$t=bracket_escape($an);$s=h("val[$t][".bracket_escape($w)."]");$vj=idx(idx($_POST["val"],$t),bracket_escape($w));$gn=idx($l["privileges"],"update")&&!is_identity_always($l);$id=!is_array($I[$w])&&!is_blob($l)&&is_utf8($W)&&$J[$wh][$w]==$W&&!$xe&&!$l["generated"]&&$gn;$T=($xe=="min"||$xe=="max"?$m[$Bb]["type"]:$l["type"]);$nm=preg_match('~text|json|lob~',$T);$Sf=preg_match(number_type(),$T)||preg_match('~^(avg|ceil|char_length|count|count distinct|floor|len|length|round|sum|time_to_sec)$~',$xe);echo"<td id='$s'".($Sf&&($W===null||is_numeric(strip_tags($gf))||$T=="money")?" class='number'":"");if(($_GET["modify"]&&$id&&$W!==null)||$vj!==null){$Ke=h($vj!==null?$vj:$W);echo">".($nm?"<textarea name='$s' cols='30' rows='".(substr_count($W,"\n")+1)."'>$Ke</textarea>":"<input name='$s' value='$Ke' size='$wg[$w]'>");}else{$Gg=strpos($gf,"<i>…</i>");echo($gn?" data-text='".($Gg?2:($nm?1:0))."'".($id?"":" data-warning='".lang(291)."'"):"").">$gf";}}}if($Ua)echo"<td>";adminer()->backwardKeysPrint($Ua,$J[$wh]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){$sa=get_settings("adminer_import");if($J||$C||$Pe){$Bd=true;if($_GET["page"]!="last"){if(!$y||(count($J)<$y&&($J||!$C)))$oe=($C?$C*$y:0)+count($J);elseif(JUSH!="sql"||!$Rf){$oe=($Rf?null:found_rows($R,$Z));$Bd=!driver()->hasEstimatedRows();if($oe===null||(!$Bd&&$oe<max(1e4,2*($C+1)*$y))){$oe=first(slow_query(count_rows($a,$Z,$Rf,$q)));$Bd=true;}}}if(!support("cursor"))$Pe=(($oe===false?count($J)+1:$oe-$C*$y)>$y);$Ni=($y&&($Pe||$C));if($Ni)echo($Pe?'<p><a href="'.h(pagination_href($C+1)).'" class="loadmore"'.on('click','selectLoadMore',lang(292)).'>'.lang(293).'</a>':''),"\n";echo"<div class='footer'><div>\n";if($Ni){$Rg=($oe===false?$C+($J?(count($J)>=$y?2:1):0):floor(($oe-1)/$y));echo"<fieldset><legend>".lang(294)."</legend>";if(!support("cursor")){echo
pagination(0,$C).($C>5?" …":"");for($r=max(1,$C-4);$r<min($Rg,$C+5);$r++)echo
pagination($r,$C);if($Rg>0)echo($C+5<$Rg?" …":""),($Bd&&$oe!==false?pagination($Rg,$C):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Rg'>".lang(295)."</a>");}else
echo
pagination(0,$C).($C>1?" …":""),($C?pagination($C,$C):""),($Pe?pagination($C+1,$C)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(296)."</legend>";$Nc=($Bd?"":"~ ").$oe;$ig=($oe!==false?($Bd?"":"~ ").lang(179,$oe):"");echo
checkbox("all",1,0,$ig,on('click','countRows',$Nc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".lang(180)."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>',lang(297),'</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'',lang(18),'\'',($_GET["modify"]||$_POST["val"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>',lang(148),' <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'',lang(14),'\'>
<input type=\'submit\' name=\'clone\' value=\'',lang(282),'\'>
<input type=\'submit\' name=\'delete\' value=\'',lang(22),'\'',confirm(),'>
</div></fieldset>
';$me=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($me['sql']);break;}}if($me){print_fieldset("export",lang(85)." <span id='selected2'></span>");$Ji=adminer()->dumpOutput();echo($Ji?html_select("output",$Ji,$sa["output"])." ":""),html_select("format",$me,$sa["format"])," <input type='submit' name='export' value='".lang(85)."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($md,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".lang(84)."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",($jn?input_hidden(ini_get("session.upload_progress.name"),$jn):""),file_input(" name='csv_file'"," ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$sa["format"])." <input type='submit' name='import' value='".lang(84)."'>".($jn?" <progress class='jsonly hidden' max='1' value='0'></progress>":"")),"</span>";echo
input_token(),"</form>\n",(!$q&&$L?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$O=isset($_GET["status"]);page_header($O?lang(140):lang(139));$_n=($O?adminer()->showStatus():adminer()->showVariables());if(!$_n)echo"<p class='message'>".lang(16)."\n";else{echo"<table>\n";foreach($_n
as$I){echo"<tr>";$w=array_shift($I);echo"<th><code class='jush-".JUSH.($O?"status":"set")."'>".h($w)."</code>";foreach($I
as$W)echo"<td>".nl_br(h($W));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$Kl=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$A=>$R){json_row("Comment-$A",h($R["Comment"]).($R["Error"]?" <span class='error'>".h($R["Error"])."</span>":""));if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){foreach(array("Engine","Collation")as$w)json_row("$w-$A",h($R[$w]));foreach(array_keys($Kl+array("Auto_increment"=>0,"Rows"=>0))as$w){if(array_key_exists($w,$R))json_row("$w-$A",format_status($R,$w));if($R[$w]!=""&&isset($Kl[$w]))$Kl[$w]+=($R["Engine"]!="InnoDB"||$w!="Data_free"?$R[$w]:0);}}}if(function_exists('Adminer\db_status'))$Kl=db_status();foreach($Kl
as$w=>$W)json_row("sum-$w",format_number($W));json_row("");}elseif($_GET["script"]=="kill"){if(!$k)connection()->query("KILL ".number($_POST["kill"]));}else{foreach(count_tables(adminer()->databases(false))as$i=>$W){json_row("tables-$i",format_number($W));json_row("size-$i",db_size($i));}json_row("");}exit;}else{if(!isset($_GET["select"])&&support("single_table")){$S=tables_list();if($S)redirect(ME.(support("table")?"table=":"select=").url_escape(key($S)));}$Yg=ME.(isset($_GET["select"])?"select=&":"");$fm=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($fm&&!$k&&!$_POST["search"]){$G=true;$ch="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$G=truncate_tables($_POST["tables"]);$ch=lang(298);}elseif($_POST["move"]){$G=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ch=lang(299);}elseif($_POST["copy"]){$G=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ch=lang(300);}elseif($_POST["drop"]){if($_POST["views"])$G=drop_views($_POST["views"]);if($G&&$_POST["tables"])$G=drop_tables($_POST["tables"]);$ch=lang(301);}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$I)$ch
.="<b>".h($Q)."</b>: ".h($I["integrity_check"])."<br>";}}elseif(JUSH=="mssql"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("DBCC CHECKTABLE (".q(table($Q)).") WITH TABLERESULTS")as$I)$ch
.="<b>".h($Q)."</b>: ".h($I["MessageText"])."<br>";}}elseif(JUSH!="sql"){$G=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$ch=lang(302);}elseif(!$_POST["tables"])$ch=lang(13);elseif($G=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($I=$G->fetch_assoc())$ch
.="<b>".h($I["Table"])."</b>: ".h($I["Msg_text"])."<br>";}queries_redirect(relative_uri(),$ch,$G);}page_header(($_GET["ns"]==""?lang(44).": ".h(DB):lang(88).": ".h($_GET["ns"])),$k,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$wi=$_GET["order"];$ue=($wi||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".lang(303)."</h3>\n";$em=($ue?table_status():tables_list());if(!$em)echo"<p class='message'>".lang(13)."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".lang(304)." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".lang(65)."'>\n","</div></fieldset>\n";if(!$k&&$_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr>','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.lang(173).'"'.on('click','formCheck','^(tables|views)\[').'>','<th class="sticky"'.(!$wi&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr($Yg,0,-1)).'">'.lang(154).'</a>';$e=array("Engine"=>array(lang(305).doc_link(array('sql'=>'storage-engines.html'))));if(collations())$e["Collation"]=array(lang(144).doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')));if(function_exists('Adminer\alter_table'))$e["Data_length"]=array(lang(306).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'refrn/ALL_TABLES.html')),"create",lang(53),);if(support("indexes"))$e["Index_length"]=array(lang(307).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),"indexes",lang(157),);$e["Data_free"]=array(lang(308).doc_link(array('sql'=>'show-table-status.html')),"edit",lang(54));if(function_exists('Adminer\alter_table'))$e["Auto_increment"]=array(lang(60).doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),"auto_increment=1&create",lang(53),);$e["Rows"]=array(lang(309).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'refrn/ALL_TABLES.html')),"select",lang(50),);if(support("comment"))$e["Comment"]=array(lang(59).doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE','cockroach'=>'comment-on')),);$Ia=array('Engine','Collation','Comment');foreach($e
as$w=>$d)echo"<th".($wi==$w?" aria-sort='".(in_array($w,$Ia)?"ascending":"descending")."'":"")."><a href='".h($Yg)."order=$w'>$d[0]</a>";echo"<tbody>\n";if($wi){uasort($em,function($ia,$Ra)use($wi,$Ia){$H=($ia[$wi]<$Ra[$wi]?-1:($ia[$wi]>$Ra[$wi]?1:0));return(in_array($wi,$Ia)?$H:-$H);});}$S=0;$Kl=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach($em
as$A=>$O){$Cn=($ue?is_view($O):$O!==null&&!preg_match('~table|sequence~i',$O));$O=($ue?$O:array('Engine'=>$O));$s=h("Table-".$A);echo'<tr><td class="hover">'.checkbox(($Cn?"views[]":"tables[]"),$A,in_array("$A",$fm,true),"","","",$s),'<th class="sticky">'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($A)."' title='".lang(51)."' id='$s'>".h($A).'</a>':h($A));if($Cn&&!preg_match('~materialized~i',$O['Engine'])){$tm=lang(153);echo'<td colspan="'.(count($e)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($A)."' title='".lang(52)."'>$tm</a>":$tm),"<td align='right'><a href='".h(ME)."select=".url_escape($A)."' title='".lang(50)."'>?</a>";if(support("comment"))echo'<td>'.h($O['Comment']);}else{if($ue){foreach(array_keys($Kl)as$w)$Kl[$w]+=($O["Engine"]!="InnoDB"||$w!="Data_free"?idx($O,$w):0);}foreach($e
as$w=>$d){$s=" id='$w-".h($A)."'";echo($d[1]?"<td align='right'><a href='".h(ME."$d[1]=").url_escape($A)."'$s title='$d[2]'>".format_status($O,$w)."</a>":"<td$s>".h(idx($O,$w,'?')).($w=="Comment"&&$O["Error"]?" <span class='error'>".h($O["Error"])."</span>":""));}$S++;}echo"\n";}echo"<tr><td class='hover'><th class='sticky'>".lang(283,count($em)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),(collations()?"<td>".h(db_collation(DB,collations())):'');if($ue&&function_exists('Adminer\db_status'))$Kl=db_status();foreach($Kl
as$w=>$Jl)echo($e[$w]?"<td align='right' id='sum-$w'>".($ue?format_number($Jl):""):"");echo"\n","</table>\n",($ue?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$wn="<input type='submit' value='".lang(310)."'".on_help("VACUUM")."> ";$si="<input type='submit' name='optimize' value='".lang(311)."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$Ej=(JUSH=="sqlite"?$wn."<input type='submit' name='check' value='".lang(312)."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$wn.$si:(JUSH=="mssql"?"<input type='submit' name='check' value='".lang(312)."'".on_help("DBCC CHECKTABLE")."> ":(JUSH=="sql"?"<input type='submit' value='".lang(313)."'".on_help("ANALYZE TABLE")."> ".$si."<input type='submit' name='check' value='".lang(312)."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".lang(314)."'".on_help("REPAIR TABLE")."> ":"")))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".lang(315)."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".lang(149)."'".confirm().on_help("DROP TABLE").">":"");echo($Ej?"<div class='footer'><div>\n<fieldset><legend>".lang(148)." <span id='selected'></span></legend><div>$Ej\n</div></fieldset>\n":"");$h=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($h)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".lang(316)." <span id='selected3'></span></legend><div>";$i=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($h?html_select("target",$h,$i):'<input name="target" value="'.h($i).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".lang(132)."'>",(support("copy")?" <input type='submit' name='copy' value='".lang(23)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(317)):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$S).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".lang(86)."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".lang(237)."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".lang(81)."</h3>\n";$vk=routines();if($vk){echo"<table class='odds'>\n",'<thead><tr><th>'.lang(214).'<th>'.lang(58).'<th>'.lang(253)."<td class='hover'><tbody>\n";foreach($vk
as$I){$A=($I["SPECIFIC_NAME"]==$I["ROUTINE_NAME"]?"":"&name=".url_escape($I["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($I["SPECIFIC_NAME"]).$A).'" title="'.lang(224).'">'.h($I["ROUTINE_NAME"]).'</a>','<td>'.h($I["ROUTINE_TYPE"]),'<td>'.h($I["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($I["SPECIFIC_NAME"]).$A).'">'.lang(160)."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.lang(252).'</a>':'').'<a href="'.h(ME).'function=">'.lang(251)."</a>\n","</div>\n";}if(support("sequence")){echo"<div>\n","<h3 id='sequences'>".lang(82)."</h3>\n";$Sk=get_vals("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." ORDER BY relname");if($Sk){echo"<table class='odds'>\n","<thead><tr><th>".lang(214)."<tbody>\n";foreach($Sk
as$W)echo"<tr><th><a href='".h(ME)."sequence=".url_escape($W)."'>".h($W)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."sequence='>".lang(258)."</a>\n","</div>\n";}if(support("type")){echo"<div>\n","<h3 id='user-types'>".lang(0)."</h3>\n";$tn=types();if($tn){echo"<table class='odds'>\n","<thead><tr><th>".lang(214)."<tbody>\n";foreach($tn
as$W)echo"<tr><th><a href='".h(ME)."type=".url_escape($W)."'>".h($W)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."type='>".lang(263)."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".lang(83)."</h3>\n";$J=get_rows("SHOW EVENTS");if($J){echo"<table>\n","<thead><tr><th>".lang(214)."<th>".lang(318)."<th>".lang(243)."<th>".lang(244)."<td class='hover'><tbody>\n";foreach($J
as$I)echo"<tr>","<th>".h($I["Name"]),"<td>".($I["Execute at"]?lang(319)."<td>".h($I["Execute at"]):lang(245)." ".h($I["Interval value"])." ".h($I["Interval field"])."<td>".h($I["Starts"])),"<td>".h($I["Ends"]),'<td class="hover"><a href="'.h(ME).'event='.url_escape($I["Name"]).'">'.lang(160).'</a>';echo"</table>\n";$zd=get_val("SELECT @@event_scheduler");if($zd&&$zd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($zd)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.lang(242)."</a>\n","</div>\n";}}elseif(support("extension")){$Kd=get_rows("SELECT e.extname, e.extversion, n.nspname, obj_description(e.oid, 'pg_extension') AS comment
FROM pg_extension e
JOIN pg_namespace n ON n.oid = e.extnamespace
ORDER BY e.extname");if($Kd){echo"<div>\n","<h3 id='extensions'>".lang(320)."</h3>\n","<table class='odds'>\n","<thead><tr><th>".lang(214)."<th>".lang(321)."<th>".lang(88)."<th>".lang(59)."<tbody>\n";foreach($Kd
as$I)echo"<tr><th><code class='jush-pgsqlext'>".h($I["extname"])."</code>","<td>".h($I["extversion"]),"<td><a href='".h(substr(ME,0,-1).url_escape($I["nspname"]))."'>".h($I["nspname"])."</a>","<td>".h($I["comment"]),"\n";echo"</table>\n","</div>\n";}}}}page_footer();