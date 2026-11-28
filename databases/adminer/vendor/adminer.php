<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.3.1
*/namespace
Adminer;if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];const
VERSION="6.3.1";error_reporting(24575);set_error_handler(function($Bd,$Fd){return!!preg_match('~^Undefined (array key|offset|index)~',$Fd);},E_WARNING|E_NOTICE);if(!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"))||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$X){$Nn=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Nn)$$X=$Nn;}}$_COOKIE=array_filter($_COOKIE,'is_scalar');if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($h=null){return($h?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$mc=adminer()->credentials();$I=Driver::connect($mc[0],$mc[1],$mc[2]);return(is_object($I)?$I:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$_g=substr($u,-1);return
str_replace($_g.$_g,$_g,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
idx($Ga,$x,$l=null){return($Ga&&array_key_exists($x,$Ga)?$Ga[$x]:$l);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|number|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
text_type(){return'char|text'.(JUSH=="sql"?'|enum|set':'');}function
is_user_type($U){return
in_array($U,idx(driver()->structuredTypes(),lang(0),[]));}function
full_type_sql(array$n){$U=$n["type"];return(is_user_type($U)?idf_escape($U).substr($n["full_type"],strlen($U)):$n["full_type"]);}function
is_searchable(array$n,array$X){if(!isset($n["privileges"]["where"]))return
false;if(preg_match('~NULL$~',$X["op"]))return
true;$U=$n["type"];$gl=$X["val"];$Za='binary$|bytea|raw|image|bfile|^vector$'.(JUSH=="mssql"?'|^timestamp$':'|^bit').(JUSH=="oracle"?'|^blob|^long|rowid':'');if(preg_match("~$Za~",$U))return
false;if(preg_match(number_type(),$U)){$ki='-?\d+(\.\d+)?';$Eh='';if(preg_match('~IN$~',$X["op"])){$gl=preg_replace('~^\s*\(\s*(.*?)\s*\)\s*$~s','\1',$gl);$Eh="( *, *$ki)*";}elseif($X["op"]=="BETWEEN")$Eh="\\s+AND\\s+$ki";return(bool)preg_match('~^'.$ki.$Eh.'$~i',$gl);}if(preg_match('~^(small)?date|^timestamp~',$U))return(bool)preg_match('~^\d+-\d+-\d+~',$gl);if(preg_match('~^time~',$U))return(bool)preg_match('~^\d+:\d+~',$gl);if(preg_match('~^bool~',$U)||(JUSH=="mssql"&&$U=="bit"))return(bool)preg_match('~^(t|f|true|false|[01])$~i',$gl);return
true;}function
bracket_escape($u,$Sa=false){static$on=[':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5'];return
strtr($u,($Sa?array_flip($on):$on));}function
url_escape($Q){static$on=[];if(!$on){$on=[' '=>'+'];foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$qb)$on[$qb]=sprintf('%%%02X',ord($qb));for($s=0;$s<256;$s++){if($s<32||$s>126)$on[chr($s)]=sprintf('%%%02X',$s);}}return
strtr((string)$Q,$on);}function
min_version($po,$Vg="",$h=null){$h=connection($h);$El=$h->server_info;if($Vg&&preg_match('~([\d.]+)-MariaDB~',$El,$A)){$El=$A[1];$po=$Vg;}return$po&&version_compare($El,$po)>=0;}function
charset(Db$g){return(min_version("5.5.3",0,$g)?"utf8mb4":"utf8");}function
ini_set($Ii,$Y){return(function_exists('ini_set')?\ini_set($Ii,$Y):false);}function
ini_bool($Pf){$X=ini_get($Pf);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($Pf){$X=ini_get($Pf);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
max_input_vars($J,$aj){$Zg=(int)ini_get("max_input_vars");return($Zg?(int)floor(($Zg-$aj)/$J):0);}function
max_input_vars_error(){$Pf="max_input_vars";return
lang(1,"<b>$Pf = ".ini_get($Pf)."</b>");}function
sid(){static$I;if($I===null)$I=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$I;}function
set_password($oo,$N,$V,$E){if(is_string($E)&&!$_COOKIE["adminer_key"]){$kj=session_get_cookie_params();$_COOKIE["adminer_key"]=rand_string();cookie("adminer_key",$_COOKIE["adminer_key"],$kj["lifetime"]);}$_SESSION["pwds"][$oo][$N][$V]=(is_string($E)?[encrypt_string($E,$_COOKIE["adminer_key"])]:$E);}function
get_password(){$I=get_session("pwds");if(is_array($I))$I=($_COOKIE["adminer_key"]?decrypt_string($I[0],$_COOKIE["adminer_key"]):false);return$I;}function
get_val($G,$n=0,$Ub=null){$Ub=connection($Ub);$H=$Ub->query($G);if(!is_object($H))return
false;$J=$H->fetch_row();return($J?$J[$n]:false);}function
get_vals($G,$d=0){$I=[];$H=connection()->query($G);if(is_object($H)){while($J=$H->fetch_row())$I[]=$J[$d];}return$I;}function
get_key_vals($G,$h=null,$Il=true){$h=connection($h);$I=[];$H=$h->query($G);if(is_object($H)){while($J=$H->fetch_row()){if($Il)$I[$J[0]]=$J[1];else$I[]=$J[0];}}return$I;}function
get_rows($G,$h=null,$m="<p class='error'>"){$Ub=connection($h);$I=[];$H=$Ub->query($G);if(is_object($H)){while($J=$H->fetch_assoc())$I[]=$J;}elseif(!$H&&!$h&&$m&&(defined('Adminer\PAGE_HEADER')||$m=="-- "))echo$m.adminer()->error()."\n";return$I;}function
unique_array($J,array$w){foreach($w
as$v){if(preg_match("~^(PRIMARY|UNIQUE)$~",$v["type"])&&!$v["partial"]&&!$v["invalid"]){$I=[];foreach($v["columns"]as$x){if(!isset($J[$x]))continue
2;$I[$x]=$J[$x];}return$I;}}}function
where_function($He,$d,array$n){if($He=="md5")return
driver()->md5($d,$n)?:$d;return(in_array($He,driver()->functions)||in_array($He,driver()->grouping)?apply_sql_function($He,$d):$d);}function
where(array$Z,array$o=[]){$I=[];foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=idf_escape($x);$n=idx($o,$x,[]);$ee=$n["type"];$cg=$n&&(is_blob($n)||preg_match('~binary~',$ee));$I[]=$d.($cg&&!is_utf8($X)?" = ".driver()->quoteBinary($X):(JUSH=="sql"&&$ee=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$n["full_type"])?"::jsonb = ".q($X)."::jsonb":(JUSH=="pgsql"&&preg_match('~\.~',$ee)?"::text = ".q($X):(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($ee,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($n,q($X))))))));if(JUSH=="sql"&&preg_match('~char|text~',$ee)&&preg_match("~[^ -@]~",$X))$I[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$I[]=idf_escape($x)." IS NULL";foreach((array)$Z["col"]as$s=>$Fb){$X=idx($Z["val"],$s);$I[]=where_function(idx($Z["fun"],$s),idf_escape($Fb),idx($o,$Fb,[])).($X!==null?" = ".q($X):" IS NULL");}return
implode(" AND ",$I);}function
where_columns(array$o){$I=[];foreach((array)$_GET["null"]as$x)$I[$x]=true;foreach(array_keys((array)$_GET["where"])as$x)$I[bracket_escape($x,true)]=true;foreach((array)$_GET["col"]as$Fb)$I[$Fb]=true;return
array_intersect_key($I,$o);}function
where_check($X,array$o=[]){parse_str($X,$tb);return
where($tb,$o);}function
where_link($s,$d,$Y,$Fi="="){$Ci=($Y!==null?$Fi:"IS NULL");return"&where[$s][col]=".url_escape($d).($Ci!=first(adminer()->operators())?"&where[$s][op]=".url_escape($Ci):"")."&where[$s][val]=".url_escape($Y);}function
convert_fields(array$e,array$o,array$M=[]){$I="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$Ha=convert_field($o[$x]);if($Ha)$I
.=", $Ha AS ".idf_escape($x);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($B,$Y,$Jg=2592000){header("Set-Cookie: $B=".rawurlencode($Y).($Jg?"; expires=".gmdate("D, d M Y H:i:s",time()+$Jg)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($B=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
php_error_text($m){return(ini_bool("html_errors")?html_entity_decode(strip_tags($m)):$m);}function
php_errors($lb){$Ed=[];set_error_handler(function($Bd,$m)use(&$Ed){$Ed[]=preg_replace('~^\w+\([^)]*\)( \[[^]]*])?:\s*~','',php_error_text($m));return
true;},E_WARNING|E_NOTICE);$lb();restore_error_handler();return$Ed;}function
get_url($Wn,$dc){$Ed=php_errors(function()use($Wn,$dc,&$I,&$gf){$http_response_header=null;$I=file_get_contents($Wn,false,$dc);$gf=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);});return[$I,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($gf,0,''),$A)?$A[1]:''),(array)$gf,($I===false?implode("\n",$Ed):''),];}function
json_decode_exact($kg){$kg=str_replace(['\\\\','\\"'],['\\u005c','\\u0022'],$kg);$kg=preg_replace('~"(\\\\u0001[^"]*+")|"[^"]*+"(*SKIP)(*FAIL)~','"\\\\u0001$1',$kg);return
json_decode(preg_replace('~"[^"]*+"(*SKIP)(*FAIL)|-?\d[-+.\deE]*+~','"\\\\u0001$0"',$kg));}function
json_scalar($X){return(is_string($X)&&substr($X,0,1)=="\1"?substr($X,1):$X);}function
json_encode_exact($X,$qe=0){$kg=str_replace(['\\\\','\\"'],["\2","\3"],json_encode($X,$qe));$kg=preg_replace('~"\\\\u0001(-?\d[^"\\\\]*)"|(")\\\\u0001(\\\\u0001[^"]*+")|"[^"]*+"(*SKIP)(*FAIL)~','$1$2$3',$kg);return
str_replace(["\2","\3"],['\\\\','\\"'],$kg);}function
get_settings($hc){parse_str($_COOKIE[$hc],$Jl);return$Jl;}function
get_setting($x,$hc="adminer_settings",$l=null){return
idx(get_settings($hc),$x,$l);}function
save_settings(array$Jl,$hc="adminer_settings"){$Y=http_build_query($Jl+get_settings($hc));cookie($hc,$Y);$_COOKIE[$hc]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($te=false){$Zn=ini_bool("session.use_cookies");if(!$Zn||$te){session_write_close();if($Zn&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($oo,$N,$V,$k=null){$Vn=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($k!==null?"db|":"").($oo=='mssql'||$oo=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$Vn,$A);return"$A[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($oo!="server"||$N!=""?url_escape($oo)."=".url_escape($N)."&":"")."username=".url_escape($V).($k!=""?"&db=".url_escape($k):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Rg,$ph=null,$P=303){if($ph!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Rg!==null?$Rg:$_SERVER["REQUEST_URI"]))][]=$ph;}if($Rg!==null){if($Rg=="")$Rg=".";header("Location: $Rg",true,$P);exit;}}function
query_redirect($G,$Rg,$ph,$uk=true,$Md=true,$Xd=false,$an=""){if($Md){$im=microtime(true);$Xd=!connection()->query($G);$an=format_time($im);}$Yl=($G?adminer()->messageQuery($G,$an,$Xd):"");if($Xd){adminer()->error
.=adminer()->error().$Yl.script("messagesPrint();")."<br>";return
false;}if($uk)redirect($Rg,$ph.$Yl);return
true;}class
Queries{static$queries=[];static$start=0;}function
remember_query($G){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$G:(preg_match('~;$~',$G)?"DELIMITER ;;\n$G;\nDELIMITER ":$G).";");}function
queries($G){remember_query($G);return
connection()->query($G);}function
apply_queries($G,array$T,$Gd='Adminer\table'){foreach($T
as$R){if(!queries("$G ".$Gd($R)))return
false;}return
true;}function
queries_redirect($Rg,$ph,$uk){$lk=implode("\n",Queries::$queries);$an=format_time(Queries::$start);return
query_redirect($lk,$Rg,$ph,$uk,false,!$uk,$an);}function
format_time($im){return
lang(2,max(0,microtime(true)-$im));}function
relative_uri($Vn=''){return
preg_replace_callback('~^[^?]*~',function($A){return
str_replace(":","%3A",$A[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',($Vn?:$_SERVER["REQUEST_URI"])));}function
remove_from_uri($ij=""){return
substr(preg_replace("~(?<=[?&])($ij".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($B,$Ac=false){$ge=$_FILES[$B];if(!$ge)return
null;foreach($ge
as$x=>$X)$ge[$x]=(array)$X;$I=[];foreach($ge["error"]as$x=>$m){if($m)return$m;$p=$ge["name"][$x];$in=$ge["tmp_name"][$x];$bc=file_get_contents($Ac&&preg_match('~\.gz$~',$p)?"compress.zlib://$in":$in);if($Ac){$im=substr($bc,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$im))$bc=iconv("utf-16","utf-8",$bc);elseif($im=="\xEF\xBB\xBF")$bc=substr($bc,3);}$I[]=[$p,$bc];}return$I;}function
get_file($x,$Ac=false,$Ic=""){$je=get_files($x,$Ac);if(!is_array($je))return$je;$I='';foreach($je
as$ge){$bc=$ge[1];$I
.=$bc;if($Ic)$I
.=(preg_match("($Ic\\s*\$)",$bc)?"":$Ic)."\n\n";}return$I;}function
upload_error($m){$hh=($m==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($m?lang(3).($hh?" ".lang(4,$hh):""):lang(5));}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
utf8_length($X){return
strlen(preg_replace('~[\x80-\xBF]~','',$X));}function
format_number($X){preg_match('~^#+([^#0]+)(?:(#+)\1)?(#*0)$~u',lang(6),$A);$Nl=strlen($A[3]);$I=number_format($X,0,".","");$I=preg_replace('~\B(?=(\d{'.(strlen($A[2])?:$Nl).'})*\d{'.$Nl.'}$)~',$A[1],$I);return
strtr($I,preg_split('~~u',lang(7),-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$S,$x){$X=idx($S,$x,'?');if(!is_numeric($X))return
h($X);if($X<0)return'?';$Ca=($x=="Rows"&&(JUSH=="sqlite"||$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));$X=in_array($x,["Data_length","Index_length","Data_free"])?adminer()->formatSizeValue($X):format_number($X);return($Ca?"~ ":"").$X;}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$Yd=false){$I=table_status($R,$Yd);return($I?reset($I):["Name"=>$R]);}function
column_foreign_keys($R){$I=[];foreach(adminer()->foreignKeys($R)as$q){foreach($q["source"]as$X)$I[$X][]=$q;}return$I;}function
fields_from_edit(){$I=[];foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$B=bracket_escape($x,true);$I[$B]=["field"=>$B,"full_type"=>"","type"=>"","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($B==driver()->primary),];}return$I;}function
dump_headers($uf,$Ih=false){$I=adminer()->dumpHeaders($uf,$Ih);$cj=$_POST["output"];if($cj!="text"||$I=="tar"){$Qb=($cj!="text"&&$cj!="file"&&preg_match('~^[0-9a-z]+$~',$cj)?".$cj":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($uf).".$I$Qb");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$I;}function
dump_csv(array$J){$An=$_POST["format"]=="tsv";foreach($J
as$x=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($An?'\t':'[,;]|^$').'~',$X))$J[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($An?"\t":";")),$J)."\r\n";}function
parse_csv($pc,$tl){$I=[];preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$pc,$Xg);foreach($Xg[0]as$J){preg_match_all("~((?>\"[^\"]*\")+|[^$tl]*)$tl~",$J.$tl,$Yg);$I[]=$Yg[1];}return$I;}function
csv_value($X){return(preg_match('~^".*"$~s',$X)?idf_unescape($X):$X);}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($p){if(is_link($p))return;$_e=@fopen($p,"c+");if(!$_e)return;@chmod($p,0660);if(!flock($_e,LOCK_EX)){fclose($_e);return;}return$_e;}function
file_write_unlock($_e,$tc){rewind($_e);fwrite($_e,$tc);ftruncate($_e,strlen($tc));file_unlock($_e);}function
file_unlock($_e){flock($_e,LOCK_UN);fclose($_e);}function
first(array$Ga){return
reset($Ga);}function
password_file($i){$p=get_temp_dir()."/adminer.key";if(!$i&&!file_exists($p))return'';$_e=file_open_lock($p);if(!$_e)return'';$I=stream_get_contents($_e);if(!$I){$I=rand_string();file_write_unlock($_e,$I);}else
file_unlock($_e);return$I;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($X,$_,array$n,$Ym,array$Dj=[]){if(is_array($X)){$I="";if(array_filter($X,'is_array')==array_values($X)){$qg=[];foreach($X
as$W)$qg+=array_fill_keys(array_keys($W),null);foreach(array_keys($qg)as$ng)$I
.="<th>".h($ng);foreach($X
as$W){$I
.="<tr>";foreach(array_merge($qg,$W)as$ho)$I
.="<td>".select_value($ho,$_,$n,$Ym,$Dj);}}else{foreach($X
as$ng=>$W)$I
.="<tr>".($X!=array_values($X)?"<th>".h($ng):"")."<td>".select_value($W,$_,$n,$Ym,$Dj);}return"<table>$I</table>";}if(!$_)$_=adminer()->selectLink($X,$n);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$X=driver()->value($X,$n);$I=adminer()->editVal($X,$n);if($I!==null){if(!is_utf8($I))$I="\0";elseif($Ym!=""&&is_shortable($n))$I=shorten_utf8($I,max(0,+$Ym),"",$Dj);else$I=highlight_matches($I,$Dj);}return
adminer()->selectVal($I,$_,$n,$X);}function
is_blob(array$n){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$n["type"])&&!in_array($n["type"],idx(driver()->structuredTypes(),lang(0),[]));}function
is_identity_always(array$n){return$n["auto_increment"]&&(JUSH=="mssql"||$n["default"]=="GENERATED ALWAYS AS IDENTITY");}function
is_mail($rd){$Ja='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$bd='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Cj="$Ja+(\\.$Ja+)*@($bd?\\.)+$bd";return
is_string($rd)&&preg_match("(^$Cj(,\\s*$Cj)*\$)i",$rd);}function
is_url($Q){$bd='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($bd?\\.)+$bd(:\\d+)?(/\\S*)?(\\?\\S*)?(#\\S*)?\$~iD",$Q);}function
is_ipv6($pa){$Oe='[\da-f]{1,4}';$bg='\d{1,3}(\.\d{1,3}){3}';return(bool)preg_match("~^(($Oe:){7}$Oe|($Oe:){6}$bg|(($Oe:)*$Oe)?::(($Oe:)*($Oe|$bg))?)$~iD",$pa);}function
is_shortable(array$n){return!preg_match('~'.number_type().'|date|time|year~',$n["type"]);}function
url_host($qf){return(strpos($qf,":")!==false?"[$qf]":$qf);}function
server_parts(array$xj){return["scheme"=>(string)$xj["scheme"],"host"=>(string)$xj["host"],"port"=>(string)$xj["port"],"socket"=>(string)$xj["socket"],"path"=>(string)$xj["path"],];}function
parse_server($N){if($N=="")return
server_parts([]);if($N[0]==":"&&!is_ipv6($N)){$Hk=substr($N,1);if(preg_match('~^\d+$~D',$Hk))return
server_parts(["port"=>$Hk]);return(preg_match('~^/[-\w.:/]*$~D',$Hk)?server_parts(["socket"=>$Hk]):null);}$el="";if(preg_match('~^([-+.\w]+)://~',$N,$A)){$el=strtolower($A[1]);$N=substr($N,strlen($A[0]));}if(preg_match('~^\[(.+)](:(\d+))?(/[-\w./]*)?$~D',$N,$A))return(is_ipv6($A[1])?server_parts(["scheme"=>$el,"host"=>$A[1],"port"=>$A[3],"path"=>$A[4]]):null);if(is_ipv6($N))return
server_parts(["scheme"=>$el,"host"=>$N]);if(preg_match('~^(/[-\w./]*)(:(\d+))?$~D',$N,$A))return
server_parts(["scheme"=>$el,"host"=>$A[1],"port"=>$A[3]]);return(preg_match('~^([-\w.]*)(:(\d+))?(/[-\w./]*)?$~D',$N,$A)?server_parts(["scheme"=>$el,"host"=>$A[1],"port"=>$A[3],"path"=>$A[4]]):null);}function
count_rows($R,array$Z,$dg,array$Oe){$G=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($dg&&(JUSH=="sql"||count($Oe)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$Oe).")$G":"SELECT COUNT(*)".($dg?" FROM (SELECT 1$G GROUP BY ".implode(", ",$Oe).") x":$G));}function
slow_query($G){$Pl=driver()->slowQuery($G,adminer()->queryTimeout());ob_flush();flush();return@get_key_vals(($Pl?:$G),null,false);}function
get_token(){$rk=rand_string();return
bin2hex($rk^"$_SESSION[token]").":$rk";}function
verify_token(){list($jn,$rk)=explode(":",$_POST["token"]);$il=$_SESSION["token"];return
is_string($il)&&strlen($rk)==strlen($il)&&bin2hex($rk^$il)===$jn&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Q,$Pc=""){$_a=array_flip(str_split(compress_alphabet()));$y=strlen($Q);$ko=($y?13*($y-1)/2-$_a[$Q[0]]:0);$Za="";$Hk=0;$Ik=0;for($s=1;$s<$y;$s+=2){$Hk=($Hk<<13)+$_a[$Q[$s]]*93+$_a[$Q[$s+1]];$Ik+=13;while($Ik>=8&&$ko>=8){$Ik-=8;$ko-=8;$Za
.=chr($Hk>>$Ik);$Hk&=(1<<$Ik)-1;}}if($Za=="")return"";if($Pc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,['dictionary'=>$Pc]),$Za,ZLIB_FINISH);return($Pc==""&&function_exists('gzinflate')?gzinflate($Za):inflate($Za,$Pc));}function
inflate($Za,$Pc=""){$Gg=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$Hg=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$Tc=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$Vc=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$I=$Pc;$F=0;do{$le=inflate_bits($Za,$F,1);$U=inflate_bits($Za,$F,2);if(!$U){$F=($F+7)&~7;$y=inflate_bits($Za,$F,16);$F+=16;$I
.=substr($Za,$F>>3,$y);$F+=$y<<3;}else{if($U==1){$Pg=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Wc=array_fill(0,30,5);}else{$Og=inflate_bits($Za,$F,5)+257;$Uc=inflate_bits($Za,$F,5)+1;$Ni=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$wh=array_fill(0,19,0);$vh=inflate_bits($Za,$F,4)+4;for($s=0;$s<$vh;$s++)$wh[$Ni[$s]]=inflate_bits($Za,$F,3);$xh=inflate_table($wh);$Ig=[];while(count($Ig)<$Og+$Uc){$xm=inflate_symbol($Za,$F,$xh);if($xm==16)$Ig=array_merge($Ig,array_fill(0,inflate_bits($Za,$F,2)+3,end($Ig)));elseif($xm==17)$Ig=array_merge($Ig,array_fill(0,inflate_bits($Za,$F,3)+3,0));elseif($xm==18)$Ig=array_merge($Ig,array_fill(0,inflate_bits($Za,$F,7)+11,0));else$Ig[]=$xm;}$Pg=array_slice($Ig,0,$Og);$Wc=array_slice($Ig,$Og);}$Qg=inflate_table($Pg);$Yc=inflate_table($Wc);while(($xm=inflate_symbol($Za,$F,$Qg))!=256){if($xm<256)$I
.=chr($xm);else{$y=$Gg[$xm-257]+inflate_bits($Za,$F,$Hg[$xm-257]);$Xc=inflate_symbol($Za,$F,$Yc);$C=strlen($I)-$Tc[$Xc]-inflate_bits($Za,$F,$Vc[$Xc]);for($s=0;$s<$y;$s++)$I
.=$I[$C+$s];}}}}while(!$le);return($Pc==""?$I:substr($I,strlen($Pc)));}function
inflate_bits($Za,&$F,$jc){$I=0;for($s=0;$s<$jc;$s++){$I+=((ord($Za[$F>>3])>>($F&7))&1)<<$s;$F++;}return$I;}function
inflate_table(array$Ig){$R=[];$Eb=0;for($ab=1;$ab<=max($Ig);$ab++){foreach($Ig
as$xm=>$y){if($y==$ab){$R[$ab][$Eb]=$xm;$Eb++;}}$Eb<<=1;}return$R;}function
inflate_symbol($Za,&$F,array$R){$Eb=0;$ab=0;do{$Eb=($Eb<<1)+inflate_bits($Za,$F,1);$ab++;}while(!isset($R[$ab][$Eb]));return$R[$ab][$Eb];}function
script($Ul,$nn="\n"){return"<script".nonce().">$Ul</script>$nn";}function
script_src($Wn,$Ec=false){return"<script src='".h($Wn)."'".nonce().($Ec?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($Hd,$Xe,$Da=null){$Fa=[];foreach(array_slice(func_get_args(),2)as$X)$Fa[]=json_encode($X,JSON_UNESCAPED_UNICODE);return" data-on$Hd='".str_replace(['&','<',"'"],['&amp;','&lt;','&#039;'],"$Xe(".implode(", ",$Fa).")")."'";}function
input_hidden($B,$Y=""){return"<input type='hidden' name='".h($B)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace(['&','<','"',"'","\0"],['&amp;','&lt;','&quot;','&#039;','&#0;'],$Q);}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$wb,$ug="",$c="",$Bb="",$wg=""){$I="<input type='checkbox' name='".h($B)."' value='".h($Y)."'".($wb?" checked":"").($ug==""&&$Bb?" class='$Bb'":"").($wg?" aria-labelledby='$wg'":"").$c.">";return($ug!=""?"<label".($Bb?" class='$Bb'":"").">$I".h($ug)."</label>":$I);}function
optionlist($Ki,$pl=null,$ao=false){$I="";foreach($Ki
as$ng=>$W){$Mi=[$ng=>$W];if(is_array($W)){$I
.='<optgroup label="'.h($ng).'">';$Mi=$W;}foreach($Mi
as$x=>$X)$I
.='<option'.($ao||is_string($x)?' value="'.h($x).'"':'').($pl!==null&&($ao||is_string($x)?(string)$x:$X)===$pl?' selected':'').'>'.h($X);if(is_array($W))$I
.='</optgroup>';}return$I;}function
group_system(array$Qh,$dl=false){$I=[];$ym=[];foreach($Qh
as$B){if($dl?driver()->isSystem(DB,$B):driver()->isSystem($B))$ym[]=$B;else$I[]=$B;}if($ym)$I[lang(8,'')]=$ym;return$I;}function
html_select($B,array$Ki,$Y="",$c="",$wg=""){static$ug=0;$vg="";if(!$wg&&substr($Ki[""],0,1)=="("){$ug++;$wg="label-$ug";$vg="<option value='' id='$wg'>".h($Ki[""]);unset($Ki[""]);}return"<select name='".h($B)."'".($wg?" aria-labelledby='$wg'":"")."$c>".$vg.optionlist($Ki,$Y)."</select>";}function
html_radios($B,array$Ki,$Y="",$tl=""){$I="";foreach($Ki
as$x=>$X)$I
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$tl";return$I;}function
confirm($ph=""){return
on('click','confirmClick',$ph?:lang(9));}function
print_fieldset($t,$Fg,$so=false){echo"<fieldset><legend>","<a href='#fieldset-$t' class='toggle'>$Fg</a>","</legend>","<div id='fieldset-$t'".($so?"":" class='hidden'").">\n";}function
bold($bb,$Bb=""){return($bb?" class='active $Bb'":($Bb?" class='$Bb'":""));}function
js_escape($Q){return
str_replace("<","\\x3C",addcslashes($Q,"\r\n'\\"));}function
js_escape_re($Q){return
addcslashes(preg_quote($Q,"/"),"\r\n");}function
pagination_href($D){return
remove_from_uri("page|next").($D?"&page=$D".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($D,$qc){return" ".($D==$qc?($D?"<b>".($D+1)."</b>":$D+1):'<a href="'.h(pagination_href($D)).'">'.($D+1)."</a>");}function
hidden_fields(array$ek,array$zf=[],$Tj=''){$I=false;foreach($ek
as$x=>$X){if(!in_array($x,$zf)){if(is_array($X))hidden_fields($X,[],$x);else{$I=true;echo
input_hidden(($Tj?$Tj."[$x]":$x),$X);}}}return$I;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
on_upload_progress(&$Un){$Un=(ini_bool("session.upload_progress.enabled")&&ini_get("session.upload_progress.name")?rand_string():"");return($Un?on('submit','uploadProgress',ME."upload=$Un",SESSION_NAME."=$Un"):"");}function
file_input($c,$Hk=""){$bh="max_file_uploads";$ch=ini_get($bh);$hh="upload_max_filesize";$ih=ini_bytes($hh);$Pj=ini_bytes("post_max_size");if($Pj&&$Pj<$ih){$hh="post_max_size";$ih=$Pj;}$jh=ini_get($hh);return(ini_bool("file_uploads")?"<input type='file'$c".on('change','fileChange',(int)$ch,lang(10,"$bh = $ch"),$ih,lang(10,"$hh = $jh")).">$Hk":lang(11));}function
enum_input($U,$c,array$n,$Y,$ud=""){preg_match_all("~".driver()->enumLength."~",$n["length"],$Xg);$Tj=($n["type"]=="enum"?"val-":"");$wb=(is_array($Y)?in_array("null",$Y):$Y===null);$I=($n["null"]&&$Tj?"<label><input type='$U'$c value='null'".($wb?" checked":"")."><i>$ud</i></label>":"");foreach($Xg[0]as$X){$X=stripcslashes(idf_unescape($X));$wb=(is_array($Y)?in_array($Tj.$X,$Y):$Y===$X);$I
.=" <label><input type='$U'$c value='".h($Tj.$X)."'".($wb?' checked':'').'>'.h(adminer()->editVal($X,$n)).'</label>';}return$I;}function
input(array$n,$Y,$r,$Qa=false,$Rn=false){$B=h(bracket_escape($n["field"]));echo"<td class='function'>";$Ad=driver()->enumLength($n);if($Ad){$n["type"]="enum";$n["length"]=$Ad;}$Ki=($n["type"]=="enum"||$n["type"]=="set");if(is_array($Y)&&!$r&&!$Ki)$r="json";$kg=($r=="json"||preg_match('~^jsonb?$~',$n["full_type"]));if($kg&&$Y!=''&&(JUSH!="pgsql"||$n["type"]!="json")&&(is_array($Y)||!$_POST["save"])){$qe=JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;$Y=(is_array($Y)?json_encode($Y,$qe):json_encode_exact(json_decode_exact($Y),$qe));}$Gk=($Rn&&is_identity_always($n));if($Gk&&!$_POST["save"])$r=null;$Ie=(isset($_GET["select"])||$Gk?["orig"=>lang(12)]:[])+adminer()->editFunctions($n);$c=" name='fields[$B]".($Ki?"[]":"")."'".($Qa?" autofocus":"");echo
driver()->unconvertFunction($n)." ";$R=$_GET["edit"]?:$_GET["select"];if($n["type"]=="enum")echo
h($Ie[""])."<td>".adminer()->editInput($R,$n,$c,$Y);else{$Ze=(in_array($r,$Ie)||isset($Ie[$r]));$me=0;foreach($Ie
as$x=>$X){if($x===""||!$X)break;$me++;}echo(count($Ie)>1?"<select name='function[$B]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($Ie,$r===null||$Ze?$r:"")."</select>":h(reset($Ie)))."<td".($me&&count($Ie)>1?on('input','skipOriginal',$me):"").">";$Rf=adminer()->editInput($R,$n,$c,$Y);if($Rf!="")echo$Rf;elseif(preg_match('~bool~',$n["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked":"")."$c value='1'>";elseif($n["type"]=="set")echo
enum_input("checkbox",$c,$n,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($n)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($kg)echo"<textarea$c cols='50' rows='12' class='jush-json'>\n".h($Y).'</textarea>';elseif(($Xm=preg_match('~text|lob|memo~i',$n["type"]))||preg_match("~\n~",$Y)){if($Xm&&JUSH!="sqlite")$c
.=" cols='50' rows='12'";else{$K=min(12,substr_count($Y,"\n")+1);$c
.=" cols='30' rows='$K'";}echo"<textarea$c>\n".h($Y).'</textarea>';}else{$Fn=driver()->types();$Dn=$Fn[$n["type"]];$Ga=preg_match('~\[]~',$n["full_type"]);if($Ga)$kh=0;elseif(preg_match('~date|time|year~',$n["type"])){$Sj=($n["length"]==""&&JUSH=="pgsql"?6:$n["length"]);$Ae=(preg_match('~time~',$n["type"])&&preg_match('~^[1-9]\d*$~',$Sj)?$Sj+1:0);$kh=($Dn?$Dn+$Ae:0);}elseif(!preg_match('~int|vector~',$n["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$n["length"],$A))$kh=(preg_match("~binary~",$n["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$n["unsigned"]?1:0);else$kh=($Dn?$Dn+($n["unsigned"]?0:1):0);echo"<input".((!$Ze||$r==="")&&preg_match('~^'.int_type().'$~',$n["type"])&&!$Ga?" type='number'":"")." value='".h($Y)."'".($kh?" data-maxlength='$kh'":"").(preg_match('~char|binary~',$n["type"])&&$kh>20?" size='".($kh>99?60:40)."'":"")."$c>";}echo
adminer()->editHint($R,$n,$Y),(count($Ie)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$n){$u=bracket_escape($n["field"]);$r=idx($_POST["function"],$u);if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$n["on_update"])?idf_escape($n["field"]):false);if($r=="NULL")return"NULL";if(is_blob($n)&&ini_bool("file_uploads")){$ge=get_file("fields-$u");if(!is_string($ge))return
false;return(JUSH=="sql"?"_binary".q($ge):driver()->quoteBinary($ge));}$Y=idx($_POST["fields"],$u);if($Y===null){if(!isset($_FILES["fields"]["name"][$u]))return
false;$Y="";}if($n["type"]=="enum"||driver()->enumLength($n)){$Y=idx($Y,0);if($Y=="orig"||!$Y)return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($n["auto_increment"]&&$Y=="")return
null;if($n["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
adminer()->processInput($n,$Y,$r);}function
jump_input(){$xg=['select'=>lang(13),'insert'=>lang(14),'search'=>(support("table")?lang(15):''),'schema'=>lang(16),'database'=>lang(17),];$I="<span id='jump'><input type='search' form='jump-form' name='filter' value='".h($_GET["filter"])."'"." placeholder='".lang(18)."' aria-label='".lang(18)."' autocomplete='off' autocapitalize='off'"." role='combobox' aria-expanded='false' aria-autocomplete='list' aria-controls='jump-list'".on('input','jumpInput').on('keydown','jumpKeydown');foreach($xg
as$x=>$X)$I
.=($X!=""?" data-$x='".h($X)."'":"");return"$I><input type='submit' form='jump-form' value='".lang(18)."' class='hidden'></span> ";}function
search_tables($G,$Ci=""){$_GET["where"]=[["op"=>$Ci,"val"=>$G]];$sl="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=adminer()->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$H=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),[],$S)),1));if(!$H||$H->fetch_row()){$ak="<a href='".h(ME."select=".url_escape($R)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$B</a>";echo"$sl<li>".($H?$ak:"<p class='error'>$ak: ".adminer()->error())."\n";$sl="";}}}echo($sl?"<p class='message'>".lang(19):"</ul>")."\n";}function
on_help($Xm,$Ml=0){return
on('mouseover','helpMouseover',$Xm,$Ml).on('mouseout','helpMouseout');}function
on_help_value($Ak="",$Fk=""){return
on('mouseover','helpValueMouseover',$Ak,$Fk).on('mouseout','helpMouseout');}function
edit_form($R,array$o,$J,$Rn,$m='',$G='',$an=''){$Fm=adminer()->tableName(table_status1($R,true));page_header(($Rn?lang(20):lang(21)),$m,["select"=>[$R,$Fm]],$Fm);adminer()->editRowPrint($R,$o,$J,$Rn,$G,$an);if($J===false){echo"<p class='error'>".lang(22)."\n";return;}echo"<form method='post' enctype='multipart/form-data' id='form'>\n";$pd=false;$zo=($Rn&&!isset($_GET["select"])?where_columns($o):[]);$ec=(count($zo)!=count($o));if(!$ec)$zo=[];if(!$o)echo"<p class='error'>".lang(23)."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$Qa=!$_POST;foreach($o
as$B=>$n){echo"<tr".($zo[$B]?on('change','whereChange'):"")."><th>".adminer()->fieldName($n);$l=idx($_GET["set"],bracket_escape($B));if($l===null){$l=$n["default"];if($n["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$l,$Ck))$l=$Ck[1];if(JUSH=="sql"&&preg_match('~binary~',$n["type"]))$l=bin2hex($l);}$Y=($J!==null?($n["type"]=="set"&&is_array($J[$B])?implode(",",$J[$B]):(is_bool($J[$B])?+$J[$B]:$J[$B])):(!$Rn&&$n["auto_increment"]?"":(isset($_GET["select"])?false:$l)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$n);if(($Rn&&!isset($n["privileges"]["update"]))||$n["generated"])echo"<td class='function'><td>".select_value($Y,'',$n,null);else{$pd=true;$r=($_POST["save"]?idx($_POST["function"],bracket_escape($B),""):($Rn&&preg_match('~^CURRENT_TIMESTAMP~i',$n["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Rn&&$Y==$n["default"]&&($n["default_sql"]||preg_match('~^[\w.]+\(~',$Y)))$r="SQL";if(preg_match("~time~",$n["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r=(in_array("now",adminer()->editFunctions($n))?"now":"current_timestamp");}if(preg_match("~date~",$n["type"])&&preg_match('~^CURRENT_DATE~i',$Y)){$Y="";$r=(in_array("now",adminer()->editFunctions($n))?"now":"current_date");}if($n["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Qa!==false)$Qa=($n["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($n,$Y,$r,$Qa,$Rn);if($Qa)$Qa=false;}}if(driver()->primary!=""&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(["null"=>isset($_GET["select"])]))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($pd){echo"<input type='submit' value='".lang(24)."'>\n";if(!isset($_GET["select"])&&$ec){$Qc=($zo&&($m!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($Rn?lang(25):lang(26))."' title='Ctrl+Shift+Enter'$Qc".($Rn?on('click','ajaxForm',lang(27)):"").">\n";}}echo($Rn?"<input type='submit' name='delete' value='".lang(28)."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
repeat_pattern($Cj,$y){return
str_repeat("$Cj{0,65535}",$y/65535)."$Cj{0,".($y%65535)."}";}function
shorten_utf8($Q,$y=80,$sm="",array$Dj=[]){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);$y=strlen(isset($A[2])?$A[1]:preg_replace('~\n[^\n]*\z~',"\n",$A[1]));return
highlight_matches($Q,$Dj,$y).$sm.(isset($A[2])?"":"<i>…</i>");}function
highlight_matches($Q,array$Dj,$y=null){if($y===null)$y=strlen($Q);$I="";$F=0;if($Dj&&@preg_match_all("((?|".implode("|",$Dj)."))su",$Q,$Xg,PREG_OFFSET_CAPTURE)){foreach($Xg[0]as$A){list($Xm,$im)=$A;if($Xm!=""&&$im<$y){$wd=min($im+strlen($Xm),$y);$I
.=h(substr($Q,$F,$im-$F))."<mark>".h(substr($Q,$im,$wd-$im))."</mark>";$F=$wd;}}}return$I.h(substr($Q,$F,$y-$F));}function
icon($tf,$B,$sf,$dn,$c=""){return"<button ".($B?"type='submit' name='$B'":"draggable='true' tabindex='-1'")." title='".h($dn)."' class='icon icon-$tf".($B?"":" jsonly")."'$c><span>$sf</span></button>";}function
copy_icon(){$ic=lang(29);return"<a href='' class='jsonly icon-copy' title='$ic'><span>$ic</span></a>";}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string(')c(GViDWPFloQuDeeQUO^DgIF+y?4O<cX<jDcJA*[iuQ/YeF96jOuG0x[iu&n>{/Lt(peel""twnUSV4BjfRQHIaBSj#}4}^YKK<$BQMFP!+g0;Oaei]~TuoHWgJWsnlo=Q<zp
uKaG7hs1<!+Oi==O^}->/9Ea]~3synPV<Bm<c_L,Wm<:hI3>D]aslZ$SmG>H4Jg&]_G3uM_JP:Uxi;FAxIn_^J<D92mCE[jOq`wQHqqX[abvndl6
BuYT?K{gCgwCMO.[Bh>G.8O8j4cIh5,JaCXgWkoIQ!Xe%"4xCJ:#SsY.cQ)wF)[oAV@<X0jd2+]KMoD[>B4-v8{,,5LHUM34%n.ryR|:FR{0:aICtMI@=)$E^(E*:+o^~E%/JJVY<6`t<36)7mKyhI2:4Q%)Fo6^*oXn"r:NoSCF7=8WtpI91EqD&q]%mr`%zJG0Us@Lmi0lzD?Jm_.!%96.
05-6$9gJ5ax@d2@W#%4dOJ)dKrIhH<fII!d{G[[I[ajMju&sY5,h-_prPbj1%#EWXF1Gq.K+lD:sy4U_,<r&5^8e0eNI+ZclG?lfYe9UG!7:$;LSenK}0zA}Hr"r6Hk&7{RDK%2"Lqg8d<iC84Z1HzadoTQIv^Nm637v.T[>ekSylqlA2,Sd0UOW[GOnj]?I(rr3D?<fp#f77<J++3-&%V.~ZnmNJH[y]`-^0uG@G4iId}ogQQE
<?w%giV}f@vlXb
MvCbX8f:=d3Ax]qKEp-m^PAQ60knO9)+,(K`(k_*pRZtYK2oEe>T<T19
8/KE0QRqMQ2Zi{y|s_*Z*3nf*SKkX~pV^ZNE&eF+E=*;AA"3>99[b}$CN/W3C*?dXP.!6E:7&
8DN`6p1]rtfE4<_
f(P[Ux2ry0nf<PY]XTU^Ll=8vSj8kmWn;8oo8dfJYdbKvrr6d%L0F6vmh%?=RDtJ>od.Kf_;-K&lem4.[s6-U8?_qZ!30>e
#,<5F60=.DmmVj*!6Y,vLqPc2443S*-I32v==XWfqK[TF2if,l72YBe~P:GUq|
X/rlx#"KguG#c7PC`orer$85!$5Tj8oa_"6XJgGlS7}9bUBF
:+9e"$IUt8G/YUY$<R%(D.F*RHQ:R>7?7T
:;`qB5t:65dN2oNk|;n3M^iUQN&@e-?9zZ?.b`%JIj8_J)
`cMhjz
][wofYiC
gdGrH?eQrkjyw-N|Be##pH#<Lc>6W5y#!tpHi439wyIApL)uX2<QNy,BPQ4}75%o0I.;0i;.J%(pu9KQ^{&h)*yu9S-q@n7`rr$s$qf78+R|D,B"/R"Sd})z=(Ed>Q,IpF&r#&g"P}1G<Cq&*"[f?fU85I0e::"@.fa;1W7q(QoXN">i)rx,jnB>]4-}`5EFEN4C2Z&IW"dxGF(]u[$EwudHf#X>!zsbDN31mN7/?E[{/U:1;Zw/e{YysExxYZM6eH,KIC1x72,.aQ"rHSs99nJ%d
Nf7^n6N:mxZXNKlGB4T*hRa*>nL-ZO^pl/$W=|^uQr`Pd"B;
5Ztg*$}E[pbUg5NY@=se`iV;,T0NVF7p,3`HFXRCH"j%tYQJFf~)h-uKw4BIUbI,&<}AFHP4ApY.r;;
0m?fQ4<mpL)wya|rpAEHX6"EK.xg.u:X]QZ:I>Q:_e*oVAtEIG8Tw]6#ura,4q,yjoUK{2x2Q<s8V!8YX_QZgJJVf!wdV]qWh?6^^48=msu1g(B%jO@Dv/krFZ<gQ(h$6BBa%/]=1Os^zfTZMn684",4rZmBm6f-)1Q!p%D6-azWWSqrT0PgSB90R<.2nK~x
@oln1hq7O+Aq%HS^!j31w6P.*!P1Fuez2Hg2F7[wTUAMI[!7daU^NVgGj,(*sud9I%PQ:?as)ZeRjm"7P!3F7)7cfrnHqB]TUQn&*~?yBd/f*H:J]~6,#9nu3646kl/VY2-F"pVVvb/4Lp@yY>^%1i#[
R#?q]:E,DDTcKxEsah{FO5ql6##D.^U
9lP7cB+#dtG9=_ce&gubW4L;gi_v{,[Bu/ze2=6C3f0v;.gnQ+=Cqy>Wu7aS3m`=/9eotIE"WigNR6W[xiMjqP#7g)xry3KMzUQILw~5o"/"fVz]8&i4~"H@}!v^cIlk>nf(j00GV!c%w0u3N[XQ
4FCk-&%,qxg03ekJnCA/fJ*AGxq_6O8@)];9={1=b&UTTX$uBrk%#QoM:r&huE`/FzQL%NbKRzbMiAQN1Ty)!IWWFXDRh#D$91F4
aj>R#u`<W4t1Gle.GCy3hYOE$`:H]m4pBN@#9c?>~nU
@7-Ts2bRIB}*[NjBCVhMH5h.+HLiA,7hUq{H!:=di6gt-W.qJ_sHC5xy5P%LOiv>bQJ<Z?HnK;:0#8c.~brLL`#,5i"u]n~o<#llm)#vxUI2%yZZJhA<;wp.UB3bs3?6)>2ZI%qQndQ
T]5gzW2-n?gm@"p)H<w^@Fw?@2[KO<H@NPr9x6J2WkpFe#_:`sS;
GXbLGn+r)`Eo4ey<#;J%XTCwn0,>=p[>U_a"J;kW/pDY/_x_#)w-v7s`C$?Nlw!-H=ZqKs
`:vt@c?@J.4:qc5ZZgXGjTRuV45fbr?[%$Hvf$MkWC>ULwvjWX1Z9?~XGV,[XDs?o?GgmHN0_X
L^]0=@$D;[IS8u/J_#n,0"_GG[WS3rWgE=3dmk`L<$?
THTnl7Rf#X9pU5%{6FM8t
I"gBZqYQyz!f`0z#-o%5vUX*dsG.A]a_0M%"K9)Qw#e
mHtqBU79kPRdi|e-EZp{H|_&7)/
N_I.m5%,Z0o/OGXvnRm#[x6ekMvnjMM3UUP!TBn1O![*hz>"Kr1]&D
W
Z5$v%dUWG=7$;-<ECt^3+%DW=XK:
1"K0qn_2.@)LiPG_n[^PVQqCWQ`==P0+jkvK@1KknKh.ZwoeA6
~eznxnCbm?Mwo.#,*,qM37xV/pIbYBwnSQXS>oWa5xkV+W
xb92pkfK,yb9[PvzK|@1rjy2R(.Y;"x"^5:&T*TqJDr%=@qh_:UzXhcD!0WGGXZ;yEhiFKUTJ*Kk8#wWEi"Mc@aVQY;h8#n~=O$i2A6b^-g0Z?)E$qWnlMSRpkmM(mVM`cp&LSsrxAssM"o(_.&{uj1Xcp2GCsT},D0p+JVt$a2<WI
fmg7|`7/[LodN6i,FG
Q_??cJuY,CbYEVVtJx:2vlAyr)N!P)bs2PMq/jUtW.SQ5z$axfc5c)RwY&R"IqTc+FbU55Jy>*y>j
qeskK>9ywq93J2i_3{+-9H6gB(dNv:C0"!b`d2,:^XdZ[jbQ(O%~EIXlv<xqlWr2DgXucm1h/pDKu_2]8c(m!>O;3g7EaWUfEM;VJGF~86X%DB>~Kmc16H]prlDLk?X##s*OJpy/^M2(Ft=+=]M~rEWLI/(Ed`d-,gNPb3UZ#r[_MiN@I0qisyAblwd1>p(*@od#I^OxQMysaxAt^Pw2;txV)Vh}oeAll&+33dRQBmh/vH(B@rt.Lo!(^Tct`z?^C~9Q&xn2/KRZ"rh+?]s,QS>XrDPIDS)Pp?5U!K1]qjZ_7tadl)(w-8F~->nbf>=;
T*5>HOv^S*De*8<5mnd9QO})f+}L(vmCc7r2=r[wa[~;NWpr<ZYgB"O[n_H!b<Fx_N6Y^nT
1D++zt@cqt%Fpi8ok7zmFDkhpZGATQv]O##sDJ>P|&)lX6(1p5iU]AbU25gPwQ9hCX$xu7sVPdrG5@|CG*]*d7fsUy|tXV3Z<o_QFu#l=vfIBEIl,MWlNkGc%kS1NI$!>tP`r)/jwQJ0Mmz+t$uy#lFKoS>fV88!HKxp+=qR<(6ZyH>m|pRmAw,a&Z"aE6pVz/fW0DEr0qr$$sEB1i_k15[L9S>/iAS]NV-74b>Xd+.gv>>0qF6Yx6[g2_A9b;fX5Hm,Jojgkw0"rWp9iC`l<1h+z
QY"cvdCodF)?W,7v|ce>a,9:1y&E*f*$SqJE&Pm?nZQ%D#xo8SXyNj#ke]eN}tE3|AyB9K-+DA><h1(jZps!qH-wO+@ABI?jd/*<J_B,@<HcsUx56fADck+,(Ft.`h
0J1^GL;Yws*)pU`J
Y^N&##tKUjZ9}WYO~q-a*4=misg7^qV_sdlZ{,I_Kj]urbQktoM^6`BH@LttF!>Rtl)FqHveX!0T^[#A7nsg58]a:;>Iiiu+{[OQG5z9Wy_-{!3jE4(.Jl"yh/N>6bi1`xO0dK00KndG4-+IOC9-i
MtAK{J_F]^-[_K5f}*#W8s">W:?;y3u8+SZ65SeUq:K8>5EuOf6,LB,AZ/TF.7wF%E8refF-oEV/x]Uu%,[9MaYdbpKG_;BeQUW?Gd4Z:Qljb*A00N`(zi:9#0M/0sj2rAKk0W*/I$q7+RSe{:"yrN0S8:{YiJ!5KuSIqBcB(_f$YnJAoqCJHy@s#%m(6UjP3oO;K$?WZN;.eN@"suLeyu7Om:3I>rG/!Bvka;56`hT*ktV*xWXZ<H$eqt$j`ovPRn,.pNza>1#/Du5#nhVUr2h8dG-TUlGF(7[Y/TuNOA,^g4edCldg.MkqwVfB%]WD~e#+h#rwG"md(2yx<,+rk*TmtNIjfE8Lu)oyywJ.BR(b#Xdc?gTHmG":|eN@RD+AX]HhoQ"q[n#vLpg?jy>`s<Wcl.d3>PALYJ[tSZXhc?|?N=S^J0^RlZf8K:f?mFSc"n0lm`w>DN@;:rG0=["rzTT(Opk?6O}HNEYcgTP@hR=>TsK;<f]Te*nCqKRpTo2VpIOSI!+6W>k)7$5v%:#vXS^=M(qJYFR+j82YKu5YaZI*{Xz1IB^:M@=IHv
yl#HhmP|9;sbiw[b+z&^Jcx!C"(|$PL:y5eIHy(GVXQjsRN+z"WT;ln$,`qmvT
G"r@)>o7}#E');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('#OsbOb3V?!K0U*,j#-4P2Bz+35Y(O]|GIGFDsyf"RV_CWG&(<hG7poe1|](vZ=6gKb*-e*bfu`XNvNZUFTxP<kqOjr%%43Ge]*BE&fsS#$XGQs=jy?$Jqw)[=$pZs!#`BwhTS`Hx]c/
gWB0SBBXi)^FyF(MQLF,

CntAU.ADCkT^/<4hx@Sg^
Q:{DP1JO0$3OTgwkiNq>)k;xr&Qh*bS(@Bze[?PjZu!*h&UmI7<lxWc9)SaQvxR#;%$<pQAd+6,HWq@]vu3^lY1Y-Ns9y3L5H!=QP`I(rW5Hmx2spr4^XPbOa]@XKH,y^@Lq?=.-z7}qzeIF=B7yM-[7f*y?wDLK<WkFEA"t,#vW6>r<#Kf"]LY,kcXWxcX43
Ass`W9ARbvKD)]-@s[:$!rwYav_$?oR*(xrD_R;EA_gOpnVg]m(S7@|g#&qK8`wv8>Ve+uJAe<BCTZ=x251_N$KrVP!Q@9e&?&z={f4CNVgErN_i?URd=Xht|Qr-%P%uCSWYu<D86`]A3;1;d$x"Gm))J17Q
ZpWmu{G[:S:^$855HJcKZKE;S1-dlyr~:<jt
vN|yIqN`"?)$N>~l|SM3|;g7*iHMqkDXK,u=-Hto:2;IM[pN!44bS5#B},0<$3}je*,c@B7eav8x71@N{#iT(Z;#k)kUdlPj<1JMoyDm5@QZU5N:f5nPG96r~P]]M=:)mv=gMBhrpK&S2geLF@mf&uke2lkXlf8RiG~[JJkKov&yU$W
^-NPh_0Dd/,;3Lh5Xqqi+oH&|*hdQ%wk_U3(QJAG!:|Lxc<M4!tXBPGbzHyeN$$4U![h!BOH|/uZL0S@0?`M=qbV;Bk,}@3+n@Eq"FY;GKX`tx."s9E7uY,[6Rh;.v
(<8kBLH"Ke-/4^wv
dI2k`b#;@i+L}<oMML3kc
T*|N]?c(A]J!zj7Z6v?+[v"xR^qVr&HXOk_>=2=n10?w,o5J{Y;tk.Fnt^[!s$xmrAHN.V+S6?wVou8,Nt:OJ3#T6');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string(')`KbOh%Z51pK,ikTs?)"H;V,IIr:rC[t5,_F@ncm`@yoj3"_:"vA_VBRR$RI?[f_[w-[fNe2X4^yyl=Tq
x8x"wGoLxJJW6("@rvP__?ee$FXD
,"hNFt[01(c0@O1M
7cgR,9];2Vqh$;omxg<Z1+I`g40]
N"NMpph0J>.4woWEa9x,H~KyMz)^$~&=7y&,)3Zi/:#LuN9`cwOcNF<?p+mWOvqk^)lzHt%*ysfUs!_&74]IKI[=7l6yD%Ojl.PWvS,{1$@RW%HFpi8pXIotu
9vqP3l
9@k@b"CyS)^]UvEFq)f"lY^5p7"nPgWQ|pw)Me6V7oIeN2NFbY<NqbStJb7Y/
.1tWaHzr=-GDr&>Go;tQ.BK^T*:x^0PT1y1DsH*Ok=QJ*/~dv*qfQbO(X(XUDZ&"5,P2;yGI)3n2<9R_CsTh0T!%bPMYYTuaTDM0`+(IC/M>Ypv60-@gK7Nie
gFAOe]e__m8_~"l^9hki:Kc(#BF92R-4~W#A5_>QMyFx"hZln3g`T;WJ(ArvB=^V5?XpDYVi6mv_S=LnA8NT2=F=2o&v{X+mYJXnTc`STxNxcIF`+p1uuxaXiV;pE7Z7}5y.LFYknDJs1Vv7h_RQvkg#>M+odggKSiS2@[g/)6IBFW^%bi^PMv/l<MSO+1#T@&^*61^B"
7K2vVu=I.w#DxX|7Q%TUVc,CdL7;*;$(Xi?QbS2vl%+,e+Y,<goAui+:-iO-h/H;?1O/W%!AwZ7*&_y@U9:1otdYJ)5B};Kg&kZpbtxOm*ubdOP/awC!L)Kd<NJ[@d&rgb+(ojc^?S};RQvMZQ8qPeTUnE}3hA4WaX$Fb6[w7#j.|M^<&a8`(p2mPhBA?brn6`gKMc[:v=CJ]3aP`;")-8Xo^yiGyc]PGjFD`N~69p$di
LlR!SStMMcS>5&VM#cXL]FCfd
bNz/a,:Zr6([8BbJu1fjKHrj9Ea(;?qaiOr1^>nWp5)8e,{xGYpdr^%qs)
cLthctKR;6(sk5h$xbP)C]
@.:.J&$k;#GpyjqU?N1s>)=]GMKF-&+TQOhc,NAfX9N:&&2d)+CccK,KG`ptmluSbEVs+AQB#W1-c&*Ut/2m8#YSB]!#39x9TEM6RY
vqDY7Xw0wvfo9Y.nT9GwVR
%+UVSjIw$&PoJ7+5IYF:Qr+m2+:M`c<a6`AB!wpXBU8_5!^$-*6%i8:6$>)>mu4N9!Px
`Ga|m:>qTYNg9(WiXNAFl7lzYn97$]p{K,@*$WyQ,"#G=biZ[_g`wxNI`ACZm#
0!/$.$kG0pg`vU"y>KXxvwLDXT+&bTDeBWg5>j~]G,z@_AcBsWM"HW,=7rnR0TrYs4_HJCaye0FVbbAZS$
h6U"E-MAI
_GR1Olah,&WSed$fUtSWa%Osh@0)C8
iScU(]c?C<mdfbb9>+{8m:|Tz7ydRYzhX%5Q]XaMm(ypvytUeJ-n1K}yd#n(qJ]y^K{sd6km2=7eBLmPGeiK<al;LuBn}^X#Ip+IIlrV-Fn=jYG*sd$RvO]:[qQLeX~"i2E,,_mlwgPXkK*0EKkccF,P!bsOE"`XDEKGgATQ?9B1g9bJc
KyME)+LwPWk]SED>4oTJ6<%WlaYdOu2;I#aX#XStlD)=@>>USXpe7oCF/Tk@?6Oel0guR/k>]Q70<Y#45&5LE<>3f]#&U0?f!`/Xw%4
jDUJpWS>28V5T=M*~C9B?,Z(_&98ini6R$;@cSF"a)}sN2&c/Dfjb9GwNih<6!lhl-:[rF.cPo4&nP29{AI$m+r4J_B3HS#cQS{J=uehbK=b|.e;f3@6_IAGrGy+G:_pXFo`^_=WoPY>AOEW~/h8F/y]bQ&dc0HND-|UVbKjWeKq?dh+Z+(hh6Liiivq4H*i~Oob;sp"&`Reqy6%(eZIDv1ymiLP
vTh@ortB=K&}0ithe#BmfgXq(WjkX
G)*fd2f_fh@Pr=3PDhsda1iLIXM&ww16Q"G6gZ;YLjs>j?,.c~J8vma;6P)koE-)y]5rmv_+qsYk[Inzada/2JtMyc>fKIc2Kqy<Jl2VS?JoG&
!cpVIMP[~c^Gm1Fe>j6DN_Wdg$KGR*>5Z+_O?UXc_e)lmnL0t6}S~(A]qDR"t%eAXlM3>,zfJ0vZpp$_dZ,,zt..wTFU~!r"s8522AgB+>YDDI4T]lnolcFU;Lo]^;Sgp4Z(c$0oW=ZOP1PNvY1^?4q;l-ovUs<,1Y^8D%Rc)5dB!&9[.EeM^1mT1)Bg1/_jv<RllYu^]hurh)VlF>>ttiu!_&B2R-#(h%D#]Sw@g28+^i|"YB!_cBEa,fz)Ckf@&`ukf+#^x1MBv31,4d7D#yCOdVLZeq{<]2w*.+$m0r#3)wd)#<KJqOvs_73
<,?Pf]r2S]OK5;/-DX<u#
Uvc,ZX&DnNq(t2,F>#0H2ak.^"Rr=:&r~(4yuU_OIHq#YaS*=?c[dBn+
vpX3=v1I!Lg%k)tyCMRvJI;dxvmcC-M0/tJQkmlu!S>joCKgbh9v/iY@S
rB
R9p".6dPEe/KZsh8dC/C?S]5d2aR#d+`mrV1JYyx]6<vtS>lkU]j#t
j(vHx:=@HVX
/Fo@E#qrOe.ZD^"+$]DC#_&FHjMqN]yr$Tl"49j5e;A5A.V0P|ZiNbS>gW-`&PPr4H=Q8I&//7z"6sxM%_NYct,ofp*P#Lmi#j;__-wIqO=na1rrw~8q!5=m,0q$#M0,,X[W%/_HR#k6JTao6`wf@(
)z(UsmzkY_@LwUW$c:OQm9BorP1]/_y1,%yj>@RU*F}bN>e==
T)}S%^43Ilb73?E`1hGL`cWhPqH6I.GZ*CvI`/N0/i`)H9A#]05.F]A9Zxy[W"zTj#U8fI5P5UGUzSGNIN?.V
Rt537;Q-b=Lv>/f*$HQw;"DtT"pl7>.FpT}j%JVv1Cz-4<<hL#1o/)Mj3i;,qogAC=qUD
qn;Hnx(Pn^.nDht&*M&k^QM.rT
]aGhkf"`3)A8Yu2L*VF[R(nhV>=A.&giZ1rC<;l8v*Q=fn,IRr83MGO,-Rl<ThcvR;QSk(J--DWLFoyg-M#n?Z+kpoG<MsC^.s,(,Y=Zhew%(Aep^L4*39Cg3q<:2(>(k7O#
l%5*swkC<83^I6JVG
~wWJK5&x|d>3HnC"A^:wKRXbH;kWg/O6t6G4>sW:
vLL!W#D6_#CyQe+;$$JaCk<"0#RWu?mJ2elz9{U%lCxXpoM[q_lSsOwr<(yJsXlDN`nZ1%lIFW,9g%<"lv_if&xG`qdAcd.df<iI)H#~04XHr>NZ/Hdx!U1c"%y!RPXl1[3,btUAxpn3jyxiM3W,wJW7lE72G}8ZuZs7K)w)4k(%hXvN"RN9/Ts%hQ<~1t#O<>SpcG%Zd-ZRj"gFN:T:U=)W8i8omD0riCX7)>BJSsRV13%NT~,YYI"p$Vm@:3.>HiPv?C(]6#HCAky71lRN34kcb1ts3eCzEDY1=Ij*kDEU()rU/:WVj1WW821:g+h<G5cui=tO_~[XQ#nzRttfvJ55=wIflTW"6#&z)zCj(VCqR"VpNnR!b@Y_-<.Rr<M#NzSCx)"K8IA#+*Y6l},YeY_WB[[5Q5riJ4Ib*+Vhj(TI0,G$.xx*:t)*fbqsL0xyG`,]UlBPhm8;nZ;qpFz)t_tWw$M~H0feaG%961uRdKm.gPrPLT-
BIqbnzD<SPvMy{M,UJy~+|d!m"y<Yb
H.p%T^I==y
=@cSDmTo`NYgVi4FC[_j]v[C9kmgi`46.IiP8hQ^DH-mh?!]h=X"yUW=Mm`@5v*;IR(Lw3cse&l%Go=$tn0
n5Rxr>Sm#:[|;Sx>F$W
P4r9&RjKP2>6P~
7&Ni7^%aWmuamqU>]y7t[NBI*xZg,i#_v.J<$#gj3;"4/A0RAq5[#7+f%&H:(ww0;!
u(BS`7jmqF0ft99g=`&*?t)BZR`e+ru-f@"<c.o,QH<UDe0xbptroB92=sl";.yMrNJ(Y<WYf?Ns*FL|x6%?j`p~x~q*%"K{;"uTA",b,8>ah-`UiT1,E$iRiuDGQeqL)#;WVe%wlh!8fIH&Dz&Yh/$zG}_($Dv)nAJ@v/cu$"_;u/i~gKNhEf.x?:x3)q7)tgnP=2)xcBs"Dd)W2ycImsMuJ->$AUd6Sr)Z]MkL>>F*G[HK*=]"i{$tV0N->a`N9`jBMbVmhDhYNZ(?b^G9#7:~1
n#T~BMH^lJN^9rmJbMJaa!G}W~K$1zP<]cyHiUC22K[_I.G&X
K"v5Ha``!gqR_d=^s6Cup6rE?Fc~#">Boj^zcoh7,Tx%wq-B/m@(I=bu&Ia=O>e.Kye:CyoTwuv25$CE(wB;]]pemQnJ:rv{u9XVlsDmqxG_Uz]912RZn060Z3gEDyo
/oFfHjt-!vr8KwQ.2H
EIcG~i[y,)d:2R
XyWo<]2
W8tShKa"Y&<)c>yx1hu7C~@UQ#WC)u;dDl:/o*kGv|^A+_ecc|iK*<KTF,2$1bQ.D~b63bdRkE2Vh?I)>_-[Qo:fEcu$W=ugI9ZL"`C7H[uK!"pQ>3D5A/T}]te0F#f:_ry7M{.$`2:2b,lYEV+Tjk[[UXlY/8<rlPC{3+9&:t[mDiY=@H#A(AR>`_,([{xF8{=]R"IaP]!$AaX_*=I;3fL4Gp!=>.h_^y#*f!/"h9BlmE_)rXPf9NN3PL:L+mc/YH
M.8Z[d3;I@daqQRjzlQC3%TFVDe_IS|E"91nmaM"p8vsovQfyiQyB:G]_E:t[=p.>LR6T,
$6TPZS_h&>%Ar-J;C}[1&R)VsK-NbSHbcIA`QKBV1V<xR,c$bM-))wY[mEUc1:]tYW_~Lc.{9_f-;5A_l]fI-/WBy}4jnRa&Q-O_WnSQFD7,4!C*VNIu/d+W7%d/?l.]$3Xe!;W_7[E:-P";TG^%>ynRbUT5MIjsij@N$hv:
mGLo_.z
d$Wu;lZ2`P<7VgZ.HO-jF]()YTW9_N<*sPStyD{j#ffW-leZvZixA0a#4`If878O6]@&YSDK}x*pX1iO>g4ITAJ6$Oh<%XvmuSUdR=705F@T++eDa@ADKe$*93g.zkH`!a(GTQ`qD*6q~TO#8N!F|b=hhj/56CCObQG1H%Ew^v]>1D~#OE/7:UWX0Me2
_(jpZFk)V)Ge%ic7%
!)U0O/1"HS(_WWwzyCyE@shKq?!#M6`;
V2UajI-"rthK%/C-)OPo59BL3N#a}8ACJQT`V%f%C7@7|-%O[n+"ol7FZQKX3UE$)(O5B4eLy`{[+M+*82Q6?WBQxQ&epu(a{L7QYijjppkv1!FyUZ1.,A
/P+t7..)glu>R>[QQF3*`k
G>7Y5c%$?gvFedsFKrvE(O)OIXz_cdMfO(W=wjyCi
y[J^@&Pk.89C.^K[XORHia!0m.+o]XyRvfH%|$M/*srk/1lwyi|AaM$oe3#efkb2fcx9;q#n;])1nrm4?Kw@qY
n5(tnE=MUK%,B6)Wd}<XnDmT,6-GEX,TdqR%ON%4bov2jOz)mh81Vz4,s/)jG4^/Up
kc$UNEf]gqH6Qu~`s6!%VLsotnndz`-sbR?X^$,9S6[FDWIvelBQOVPsVaDEC&Phmoec4cJ5hkjqV.wI3-9/=sdE,;<YmW|#/<n*]g8PT+l$Wys[ryp97<{9kPG=m8V#&.NSOy4Aia17pXwa88bNcZ.G3[Np$,N;0w:/:+]*vUxW8al^jOr9+"
C}kZ6[kTa5s,I.2*&;hZG?p)p@*3z#dNDKUdSL%sb#:vS+aK/0mbv"]7.cn~`>lU+P/J93kn^&+@*5U(uJ@E#/<B/:g]G[YG*r_a7vwXB_gC5JrBasu44c/fJ+k1jC1BMH6K@=J2>)ERXd1Cf2nb;Y&-N;_KuQ?kRlmfFgfp`0RI<u(pW3eC-Qo>p5:fCo?Za~.U7[!4jg:f#XO`cY/:PGr/&_2eQ@xl)Cy5t*J&OP<2Vz
LIPD4$u@umM#X%msUlze32_+4mJ<{JN2,%-F)s%/
.{M]H-+~IZ2Yx;1"OJvOG:rVVMc
lz2+]vVAR*M}7eKJCi.lDn-#taG:oA[n;|fR101Yt-OYn8v=KuU5Uc]tj(J~9]l-V(&S1;D~=)XEcZQdkN>Q.gTe2GQ.U%)G=qW<G6!E`KIiU{;b["FF
b83)(qqju<`+vvaW/n;fb7p$w/MedWb?:6MY?20QefZXW*U?t5*(lr+wo45DPaayGX/[RiN6@w$=WoV7$HU)L-)g(n8I5*<N@o`eN*FjPGco*o<dvMfEKiJkrxU!J5VBR1hni,fjikn9}1^uhN^-N$mrwLO_-)LDJ-^Y._Y2BmurMGhPVh3]^ALQr(I6ym(`^Rjn$AOq4U|3`
X5G[1=~!cy"bMM$K@gD69jYZ89X3XbRRn4eSh,vxtw6*jR.o7<`@K7P=<iOWdK*In=9ZT.uI(5}M-$;km.*A(RbjRCc
7TM
N!b(A.uY2Bqip?;w8]ue/8
J!`kF
SYsUbIG^cu35F]%J!0S]!gtXv@vMGj
_=e"hQj:59QZ{xVWQ!~8Mn0/)N5/z8JqqnYqKUfA-p9
xUdX~W80=kE:Gh{=SQX_L(UOU/*Ko@*TOo0_!/!4pHzFqKuM*729fIk/B:7*TVsDTFOoBt7Fm^&Ox9ib4K.[U)0$|+gb-[M9Phgk,;eHZ4(^Htr@%m4r!nlWGw%A/5,6y29W*Qm)1dU)iF<_xTOZhI~3Y`2W{,^Y1$JZ"`nU0Q!!9?Nf*
f(jte7z`B%v71&Cx$0au@jPk5`%Lh*HFG,5]H(4.#@DVmmYO8RPd8-GS7fV)g39y3<(bc
`9))[DMEs_j&;=x@pyy7qnBDwSWBtJh<yoFVP,^m>ooq4,2JG3un9/SUS%5&12,pughmTDL.9YS(G`?nW&gO?t!lG(G<Z"pC;,:XW)biV"9!T7fFzU+OFYPVJ&/r5+*1D&2"U6d<d:s]aAv9oDCMJ2n>1TPWkw,^:^ldq!6UcOc?M!oR`.u%c]F$"u^s5NqEsKHV{p+9iX7_bR`*0"slbB.i>P
]INGG#qr6BK_FvCR+,w7;Q3~qQHP@3&wu!mmk2KE-q<##
xfJN^N_[`5m.RX&&,aQS)"D)L

mE9(T>.!~N:8>jo;r!N$h@%JveqX<r;;Hs<.m:)Ei<B<0aUfa
H-tmKRB)00VYp$y]zTHLW3(72O3;htH^4L^s/0z?fo@cwfd0;%_NyfbGCaPqbiVt269fPTWJbb)>HfxmWQ5F%KOYUY~JgEmMClo%U45kDpT#$BjFMT1ya1?H,;6NVTo)e0l4-8UJ+M5MQ(]dnU5cqKzu`OAU=nr2DK]0Wb7wkJN$:Y+vJ8,h&y:hyuQ8!T6M&9}1P5F+4_$LabuFd`+3@Gd$f]O#]O>_QDl[LVq4V-B^sEOs6;3n/8_R-;c#:waY;fw_NIx3{gy8wC!%Hm61Lj01k"RdK%w`?Jgm[RqYQm%49N%%
CA5#E<C]&`WbsNdH(dc]FmL[pK=^tq0AgvR?g*wSmD)$A3`P64JQ`kWrlr]N(cX~V>g#e#8g_DyZ4-ZV>f({3N)OjX!9jb!1whhj]F)7TT:4PVE^=BO&,<k^tIY2DFWjZjW;aP7k`n8W[9OTELeFI-@Sk$u:csqW?(vAQdvlLex=)`o;YssPN~@id/m|S?F0WvaZ/6@.0T&J?>X[^6Z9&!f?)LGb">.!=dqX5-)$T2Gm%~mffvdAyw/)NgQ]f/[w(L8V3-oF$J>;[YZn2(FD9oM$i[gQ-HS|p0ldH0`]vRVgUGt,)[Q0YpF<Y-c>(Q]]?gKgy01qxyj-aC*V;0fQ5~N))3poqN1
&@Tp?]Q>wBz#9~4_f%=>Lb.n8j:WO.j4:j2tJpN5mTUWS;:yk`/M(&.B:!U.nHA?-?Px8T2VVSGOTU4WPPi{p)AB/irBXn,Pu*"=U:t]T2M4(S+pobo%+l:]Sm
TZ5fCg-p/`!ftgHcA^MePfw)p_:i>?lU|?z0*>U=A[f9B[Rg`gYpVu{4%M&)Jt-j&Lue|W^%-@btFM6%(^VM)*3ayO[!{y5-#]|PMW-.8,/qDB`K~h*ek&hK;%?&KEn
5cVonyoiZaSMl6.=D0[B4cFXZ)jw0[Py@nOZ%.QYw
c=`K!Y&g}M`^JxP54!oon`>tyHaHGDS3@8Rx0q=%)EVR^w?@dt#2r=NA0r"p9Z<np8K;eZ.A)8X4~(M9.0Q^MH#Fuo->Hck#n;g+Kco:zUy)8Y(R!Al_%-CjK&Sn9VYv+?=!U`iuoH<gX=/C_!r2Vtv:_5^dS@BX"v)`5QJ/Z_39T>_A8+L`6+sDuv$;s$J%goJ>p)$"Ay@XHf0yvYXhlhb::i[-jqv9@M;Me:-2<;r9g#/YAr_%!h!dS
J
UdvCom3#Yn.i,vC/v<9p5G^lviKFcD`:m;ir8t/8ghk6KkClcA
3~Ef2=LCa9VqE@hRk*ejM(SoVTgT8VLLV1QQteGkDDXVE1<IB8dOhwU[JonSjl=e`#`MUcHt
CNa,331q84/`fG(S0&94VF-YVpspfQ46@cNmO^JR9I5abJM5YLMYzDf^Ixt[Jtm>e<A4_@F;$CHRC!l[mM!&~fW5.a
;yf>l%+sZND*M*?@d!/HVy8|FaRk#MT$e1u|w;?]D^$$t1MUjhUcMoaS=>s=[lUgl{RHlHkP!+v[N%M9]*UgE1mkB"H:=ZE94^f0&IR&?6Z`[xR)a39,MnA,2X4_G2kst:K7xnIb)alL&d6iQzJ{n:5ht;gl%lxM*l!dB:oXm`hCESUBb7_x`_42:]5!*dRx_y7|47n%XhFe:[88<H.!UjX<Dx>1E|GNYoj/(rA`FLK0GG=2aB_fU`EB&7_5t744t`mn6[fn`DVHPW:9m4u?X5k[5{t}qCIR1=Heah
`GN[oTM)UmnJdUuL49`F:dC*CF-B1b4Ta@00cT4DP!.>Yf(rELUw:w&x"lwiVyzu>s*
GKNZ3kQ,E@m>Yp<$0waa%yv?%J"k>Nu%y`f"epVvv#dbC*x5iTYP4$LFAC)qdtOX!W@(Mb?y%o*-#VK;sp^gpabmU]mBjt)G"+8G;g+ua,R],!HPnm<=%Jj.8Hy1p@xV2in")@&VUUvN-aX&N/KRN*m+PnQY{".Q3ub5[I$k<n8Z3`5a6clQNSK::+.l=yP1$ZrpD..]4$4#:cBDclJ
*=sEwX@Z~EU(*uNGhWGE*QT`USb.:d@3$jh[x-nrm#6]rg1n&X.XFc=9I*!g?Twcib/`4!57R2X[U3c*M!%=zvI6($hba@SrVQ;_vxA]O3G2*aXm:h3@I?,oN#CW7VGrlBzY&=Zs)TlJ}%0ws/8v}>bE*X!.LOli-FV2m4`${erjZ6lTXuh=<6j.Zy1baDXr8[N)E$
]iV;WM,<;9!EfnSi(di&:V$HIW_YZ`/6;hgM?S;bc#S;S[xWG}+-_*C]%t<~++k#t83urZ)m.Gmc-yw<1O^>#_*f)]aWX&<~8Gaz8V.AN+uSTis:sD(C7cC<On>:=}-tc2W$-;R$UBqHi$BSUV;D%B1#E3;;FDrEBf$.LVLA*QO`GBpp:gVk!}iZA:"j7BU1g@W=94c_&<r|uFalNWtrwe@+1-NiZ9$[+>&Wd!O[J&GSB@d(#rIc1+nu(@fZA9H^Arb6F|D/MuOx(o4<uT+@S#JMQV2RcQ.?Jc$Y1{@@_hLy%;]((zLu^vU}
i/X.prqYan{P5H&/8PV8%Y^[y#PS!#`Pvhy#(33T@,dVs[o(z]w/<JR4R2nY*EQcZ"Tm
UTZ
!X3%KMcpYJdydNJ*=j9hp&eH`m_?`t*4u+(>Hx":3yAby/??[s"=1k1,0)srNCkuGpO(b)e9Ew*k`3(`y*]R1ZhL[b
&R$pK$uP*z!z"W^P.Y4[.h.ehdhDppcf6wMEGgOnv3G/)s+&^HMI>:DucS++5Tk-5?7@Q?8uXWmwd=iL]@6P1UvbW_0!JhI(],ekPl-Zj;+)#B=CJ
C:{<RCU4:.ZLo1O3+d{#e`CQ[=jG{SM(oUu<3NCgb]<PMRi+:S?r.qe6+J#*Z>sPz62S]!T@_k"1`T;##@_3BMlQ+K#`Fy1MbQlR~ar``R*<Uy1nDAt0-sZI6H~qRDtF=dpQ_O;4Prcd0iU^"fU&JiR5<5$x}<a%)atw^pnw[0h:s<zH_d/Z]^"<Ak9>LI(BzCp8*!dF$Z6pR@G_eW3H2rr1
4(+KykywSK,aCd<x[$Ng/o8`gzn3,Gf;1P&%i@"=PF@NTDd37=vl=?QPq-)aNx!ni,S9Lc
z.}:j.o)X_[ox5DU5RzNt-!arq1?zXy?PPpW6?/W*,J!TYnf;dX]sXHsktonxC]inw,%4hB%jB@!}+A_m>I-UkJlw/~[E2#;h28h6"[3jo]8F*<(1B1vzbSfLq(tTEOOvL"qP.oJ>m(-JTidL]P(#TxC;%F=vbB1Fq}b<Z"l?_3Ehi<Q|y`EYKM&JdwS6fsjQBM0K2*P=A7bs=SsqPDkDiaT`#L&s/V]-7,a|2MHK
6VMs~$73!t3>x`UIfQcR,*m8M7~67+pN]fI&d_(eNx*O$!2[v]e0k6k*KDw+kj)P4#XsLGZ(NbeZc"TABFYAAWttjNSY;Z%Z/SVAd-0W`szQyk,ErgR!{>#^
UJ_JU~1G`p@h,[+02.+wLba/YXXStkVLf/KT76;q%UVX^J:^JX?m-wPylRU+QMTZmYA/$0uj&(S[U84_X=G(Lt=YY{ionXikhgW]D]fP6S!9^:#F=to&)0T`P$c;INZ0O3)@O?5dkOQ&jGc>2wfElU6RuIqoUVno5A+x84vNi2onF5x75{EPVtCtNWfBU@/NCgKju^0@<n((uQUB`0RQ0jV;V?S&Zr;J<&yx:bwP9Or_&E;Q@`u<sV`s7a:B&l-J-KXaUUv:*<mP5%1(7;6Q[Eo?,;c%!mw/a)*7D~QtaWXY4mg"h*_]fCk)L*BvLE]&57nO&`cJ"LTMul9Sk@5>VmQ
<-7zWRY~/$5q`%x=mD95ab;@0O;IY+y_]s)kf:v!%[a!vZ
0gurMH1Y_3}@YB"y.GJm@C4Cv(+0!E5*OL"+U$9+ke
BPUxP*E@<bUwaw2%ML$}Wzqxsl)JxS!7
7F-MyI#__cL*.ZYf_a%aQ)RYzY0YUyK8f?Nc=Hu/+E}KOE;#tR`k__AN]ci/+(4Q3d:TQop
pZvtyePMR#$khpr6kyzb[00mP27i?ENyZ4TO%l5m];Z^qdj6`,nZKNOY}*<ET!KxkNmv|`*mVwIL{`H3;;R2^Vav.e121=t(}yn86l8K
2|%H6-h~#T53,(DvFG_v!oUzimFC:d8Fy4d8GW$`].!Y?U-/%fe5)N:J?bS~Y)--[[qBZ^_4=sA^#Tsuh$LoP}P_k^IV
qll/`7<#SU"?c&p2).v]I&OmX9kY3Yl:|fWg"db5UEwR0@-WxqQ+Vq/NE1]Qqs,*duSD$OrZYJlwk=oZ#b#vbOn-OW+Fr+766(^CSXqAJ`q2Ub1_*aS?+Dzc.b}^<@mkrd!t[am2lE!QX@c`TC=X/3v.c^9l?JL"rKSWXEcADwKNgG,8uFY=8xjb9w!bbW$T#wPx1l;w8qkxPBq^AiRHVnDm-9+(z`_&Hd>yuwo!(DY(_:`jtDl^A"?E4IW7l!LKy5ck1@so_tEbtv4V7%x-GM|TWGf["S]Y{f"5w?V-:Qp
a;ZFPi1_8;H6d#}R+ue@hBeD"j#,um$ihp%]zx])+gpuG:|?[W^`ZPVB3ecqu;JKx^svjMHF;ix/@T(rJXJZUmTvxaJR1+Sf`s%N%0-H9mn5=ejYyRfw1l-Jai/m&fg[x_ghl8-()eY2bAE`r"<fa8dS+$lL2dpH&oy&)$<Bd24=O;j1`oRs!s9^p`c%(L~E[+0e9M}tW0nna<_&jEA[I!}`^@4,c&]P$2{FhX$46loh
^S=0^s?Oip2UMGSC`iKZ*cMLJ{>m@3P}U=AKEbF-y/ONGMJ?)Wg]/-#%t*)X0HHi:X&i,gyXPu/g:v-NI^#YFRFr$sTxPqMsNPo0e;
3Ppe_`g7A[^YqrH)jD$7?,ZNhph7U,(8?^,84XppH"v4KX0mh>o+bn)6}w4;Se3L<>zB{w6v
>tvB/|%L)XOuA+ZyJXP5>3OGQ^Cp#y#{aAUrS(!"v4CScC<-oZjK`rZMv~nAGVOO$ql-j8d(gQ
h9F)Ks<_ri21EVyrcaBWqmV*GklZaVfN;?YiVE@Unc,Ae)KA^`iuWLtKE1_<@oK;YTM1?YP3G
S$UqmdfFc&$5
W7nuX[d%$`[py<aF7
4^r)p7Ux=*SY)>-(EN+|D0LgV=u>[aLZifXf!OG-K?G,,a"Fy;q|RG!L]42HbK[;4R2:T87beViZa<T4r+"W!dsPmI-#MQasmLM)>8AzBN`18Fcpiwp@6[J,b
!s%wF"B&]U"U>NyWILqaFG1N"|g>)04Gu^>"n4QL;]C%_3KJ<bFzUJ*8fg=.]_4-15s;=,d-aPN`rU5VSs[YT{j;?~*prp+cE>;;a}]uP"]/1%KJ(N`[^lEDQ%Hm5FWan;0,tp[R`QLwA,]*o1B!!0LZ2de-Q`5!Z./Qebp-mPA(t)D$lmxkxVAA[>B},!y7+^&Yz%#Y"11p=i+MU?vj)q>v
jwrh2.&XAk%jBHam"1RRoQ!HTfXQn.HjEGW+Pk;m^"Pm4nun}QR4?Aey-<BqQD`F`k|h&Z,"Hyw&D9[`njW/aNby&*V<!I9;5qDaw_*$D2EI(XeH|1Zu5+n^/cLD.y|g(H-pXc|EOg;d-EvWS5&(GOh+=$p_qN7^f-2>hue]%JNZ}Pp&wA%JNGn
hcGFxervl%8OK@Gf=u`:#a3J+W/i|ksRWk-(}_&;Csrn{F+Wu=p;/qL>h4@>}:XQZ7mVp0sxw0Jg*O)NuR
Tx+H"bdSR<s{CTtz_i
:K
Jxc!+5jm*]+%2ExN`&GnI(fZ;5"A;-Et6;gQL.dEZ{rF57(k9!b^<MDp,hn}L3BP^RHAK
9xH3aixs<CtqLB7wt[&Kiab"u|IsY`*23"sJO&XhbwnVv?3l-]mzLuUJ5vbbOB_CHJnI9W+CahsMtc#*e7P4D>OEj23YJg!M)UyEfOAi.2"6
ECf48D(W"(eM4KMXk7&&6#"b|O[1N2t3/5OI6Gt4t>Y<:c2L16FUHsgZr/t1NB"y&-zIn
d"&f}p7VGVIJeG|rn@|/=
it[F#AGF#`aueAYkce@7Jt#:&voMLs
QB/Ekc]4A)*2MXvO,YK6UrG4J9A9M_.2TS"3?g8f`i2)U/_m"S`eMu+R
Q`x`W/)iA,|2p4dBiHOd1a;yvAEByiK5ZiJl=o0,z]AG?:~F!50V?_Qe_+N0z&P-PZlX{=<+)a:UU8FSv(unsc4M]k(,Ja]%3P0+[2
/oQOVC%WNzK^e|BmL/Y6Bq]iI_rhqlQS@6tAlzXEIxIC]19fN*llbkM0kVGr[?2=v5
aSj^$Qf]1f$vO4MJt]<de=ZEiNS#0$>%uSD[lO*9MQAN)-G>33_VZ?M;A`pp)gz04$})!9rg;##dwImTD
Q--k[bb*UKwD88iUFFe)wFAPg:=lf]6uyN)N)j@31QPiub2*uY:nQ+@S8M5x)2I>Cw!R6G=ELBXE:=IWv1J*9m7__]"BCcAK;C"cxnrhc+qf![l/@5I*z"a?I/zSwbR">$q4K0gtj3v.nL>,}Cl1mSj"Qr8C0a"=pnx)F(}4xT9(7+x?.#:);EE1:W5PUH!=l,NNcd8@4F7.|p~&^FI!^bwmb;.&hPH]"R;1c-qOj=hdHcFRXGj4aIixw6UTl`cez,5Y&n]`zgIc!wB%gBh>V">muR}je$KLpI$Rgc;%!b`o8CrwH-k3hEBy&ZDtL+?OvR
(>D<R5.pP.8=Ec3<.*33yiw;qq6ZKnHbWBnpoq&g(:f5yU
/c-wY4*/GM#aQf+6M/JfY7Mpo-(%<4@!9X5+K>*sbRZM(_Ca@fb[=vz6L^:Hbmlk5eo>y;3reY`Wr?;HqPBE9[Jwkdnnm<hq3NP6
U^"&=cyG2)>oelr9(rh713i%E7Wr`JuO+U7~ikZR4dengyZyH?5yu|%
.EJ#g.^a//8c/Ixmk]!i;Ca&wI4t6Rt$_^Fh6%22dOfo7%m{sU%+nm+"C5Q`24+Va@XDA;/A/7hg[Q7qPYAMD(eDGbvq5wy@lckgc1nG1sHggCqhYJ1eT*YIx@.cIr5=sB%tq[,*fahvcF"#4
_D.7`n_!jZ)WPp(?&4_]W1s%9Xs[Vw0lj}QI,T1CHDdp/ZJ;q3g.<@+Ic+kvuDD)KV`^)mNm&2b?S9+FP0p0VhdfyXc7-SFo[uQ]<xA,,E(Y/QP%+
#<$]%i,s%dNK*yqdFo=0ukj]J?iy1`d%M%dExHYjTrg!(F&m"A)n*jcr]Uwz!mCZ18-~lK?I#Ihd!ee.,W[@.gC8o=d,c!HL*+2J#DMj*OD_QZlFjE,4=kitY@+iTp2BnD/L#]+db,snFc+32H81xb
DSc1/L!-Y3L;;q+ueSsT9"{eFwjc_H0&XiW<`.|@XCJ/bnZm61:FS3u_wDsI5,}B^<~g=$
55#kt|k&dcy7-]p=rbt;T(*w0#=ToclAROiu=02ur#Cs>,9G*)<3X@ne6a:B.:dPvG"U%5j!!`>zTm.4(
@NT7HYB)a^bc,EyELTO7ht62.TfY%VXMTGt2i]N><A(dt`e~:``CvJEykg_V@<Tpq]*x*]kv@J9`XAxPa<"[IV#yqvPSC?]*Uk3"!-gg:s3`^7q~n(?8cIhV7Vm^&m^5$4RY_!q.Zc0wh|2&n5,hJUH2VMZx.k&qw#X
i6pH)y^^74$@H2GaoGx7$qN
vCs@_w
3mSNOHt6Dqs[Q$LLu=gT5G)v$WfW%;tx<p3UNJoZ[qn^xEh88:|b4L;RN1KD[b$S3:?:mtBz)u4l;pl
0pD
eX+yiW"Amid!sflH$rE)[8bm^)5bq#zbzs/HhV6)9sZJ}X#MPH}D6)fhX[WGRnb/Mp6anh6;!aL!an0w)QCrxK+:gT$89Y,#$K$N!N#k*/G3uL%a;o}T>!{OrV2SwpIf0HP4I8(v+S]nFFVx1s
E*Ky5rDls)BmJehN_V4v]3/*^u&vNu4}J$rmlEJDY+PC%v<==Ui<EqX0XHFvSNXe*G@Ui^Ue*+r/]DfWn|?)
aTQ)ZwRLUl*.2,bv@C!$Lq#5F4*=u1$9`-t=>r6bKDndzb
>
f-3X$p:x-nlvF<Dpck
6Sp&`c>82?e0joy`s[R[ERRA
xI]*OvdvWw(<!9mr0ANY"4R75M^Sk=bPHz9-4,6Q1PM_b"BYkjSY,@Vr%Fe,:Km/RdIU+~%eP2D]CWEU4},>?bIX#cBbqpo(N^ls0nG3fo=~"=@UXBS-sU/WQT>%-VwV=8R6O^l)1(HG<_,M=OGUN&T8<5?!/9s&/j"a*IfH$uND87p].uU2^uaD2?8_)upKVkK1p/#iJZn&XM>0--/oD>Rsa]N";AXd6|0~%x5l0jSYy!k$R$MZx"k<;/K5v_V[+fsrBj"SD-)w?vMULnwp>39i>[`B:>vTY-O/;|5gmS#;J%*y!&2[2IA+0G
yVB@@fiRxd{NhJp$rY#/Pwsq!Ey2mJx%8K}k8h{(ceI#.#:/)tqm"tmMc:X][ZkJC3}(RvE)M0g)Ffu0x,@=&*I=d6Zi#tb
E7gpe:*F@ImcPxR<W7:hAiCJB!4j2.K2XIS/J42"K(Q^X(;A59_+VsBO[U%iJQj8%!>#jOfWwe%I(F62G[;XI<z1vNMjRtbR#?Uj~%Nm,oy?P-$pG_C!1GwY@di:G`J*]BB3DG[#:4GH8mP4NT6pv
*?O)_3MSj<fpT`-@=*/rAjTk]KGYQ@8dSb#*tgf@o->^hJnWdXd;WINr/=%AXQ2iQ!G80g!#dW/PB&eFYhz3<w?"X")SlpLFmM|PXXEdTJSBV5.RS3@(F13qC[{Y-TwNCCJ*ve8#y7^d(`w<T-Gqw+j$H$/N59"4hQO.A(8v-wF=)@X%1o0qNK@CEK%#GuDYTkMd#%dG
P:J?"Hv!F;+_OQBKna2Dt:!j0(H"agR2@J?b6b@6H?<fXsDvxUk>jdHh&QW4W!PzXz9}d|_:eOds(7-$LrkNmBP0Sw6`g#c
`WBS5<8e]LpsH|&ov/SON,uCD*?yu+F}B.2uX^
a8d8p
2PyVu3njBmWe3/AnS#b#Wq
AQ]}D[788``SgAZ1CCR*Vvw+v{#(m>Dz,1pK@,dmO_u6`:*D-Im9E?nHCJNL8W#J<PB,y1D
ACOB0KRC-#j4uag)f7dB5cMO)cv)E<Y#=]p5P6OU<`ijQ=-$3kAHfw15<VL_At`dA;YEWh^_)47OV3<Dcy)[DVQ(ts
mm!c:`ljQF&SOd^J7f|:h/A0R=vfi?<!FWKZAWe9z([.Uc!<R#"jS@3rcmO-0F{(G6P"HYx+.I[`wcra{F,9u^=Pr*]p"N)py&bLaBMKUfLIu`{Y[/2^;v~O4$@I}&lm|GEjqEVAdCdoaHXhBL#FD1:syoe&_ol]k=q#A:tkU#N5u`|WyUT."ZNG_+cD)7/q}Kd*WQYf1
}gALpGE$gX0v*76JNf&"d&(sZz"<F56jI&giLyBrrZQ3{jTPkPMyrtkBpSaerUBR#htG][B..H$^CYnLpt8GATP0P;@Y{$-h**js.*):1"AC!Pdj"=:kFpgcO1N!2vw)F_TX;3Nl~(z^LRxtx6UeX25`VoesFXO!96~T9Qu5vhm=rizA(DYwe`=&Y&B!iZ^v|07y?8Y;;joq{@CXCyO3
e)#lJ9]0AT]*I~4Y:FOKc>(p9b]!18daq33e7-rk(f5JD~WL<xgR]6b=d$&.');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('%hk^CG^ZE.5v]nf>]Jce[)I4
vksP"e$sSk._%<(:+l$J@n;gd9=*1
;0,cpiiV]^
gkO1//h0It!.BFd@7w/^+kWb9?hNMRQMN.bo&iSEM2}2i%?W8rOlkwT]kbsGLOzB_[fz%JgXpu"Fn_,RqnWpi]7HD_pQrbYcTt58Bxr]ylV
hw-(HS/8c=Khzj(BVbN<`Mjk:A_ss<!xZW`;k1L1(Ir9KyQg[Shv.upYdnot3mw7&Wp*3y2xmr
uXH.Q[21RT*5I4L_:jMrTfw~)8)?1FmQ
>&tSJ#!e&.hHzCQXaX7vI#z+<jDTt&@Mgw-knFa%K,y1HBJjAkZ,APu!y?)(H2&7vXsuHL3udHxV3GQ$7(r?o/*KBA&Ph3[*O(-OTxVe~OW-vA9w0GQy2>!B[TS<h%gmAt!k&ho*,uynwwD^OHg,6cx;<"y4ypcFr6iF]Fo6zv#7,6[4yt2S=y6Z"a0qxe@M:EG#U1d=OKQu!yWVeEzkiEB_g*3r!%Hax&0)@Ag]MX_eDV~ravc;~JS%CFcU2n0[AT?3sz%xryChU[zTuyB4gUJe=Z$KKH4lEkpR2B-UNE-CO49L~B)d^AD5Np?J<FBT"h)72ujiGBbvVDf`(I.8Y$^0?PdJAdZ:<SGCwj2ANT{QO>-BJ)AJ;tAEA8OQyXpY?,URlSG)"wPU,9Z-.n9`ZWJ&Ycs0ErUs@e%PZMz7JE2>RM_n9H;W
*kk78v!.OiNOnZa&*$0M0jsi)^]_90/qU]x"c0W:mCx~W^R/:fGfnih(U`OuW"M
yoGI`m#&MJ=="M4O_1u@65=vO
H:[cq"EvT:mJ#qO85UHt(4j@xkR()&]!JB4)LBcc]^q`sp1<)l:H9]2}J/_#Tb&Os%<r6ArH4MZ%1mw/]>qAft,aNl(PeH3vZm3dAdpvkpgYQ5Xi
i!u*~I^WOF0rpkQ?QraMYXf;]ktm!RlyWZ6m|g.=`GR<!44I#:0*>[#<p.O?^U.2.9!4D"#7rD%2GMXmMZuk|Ds>ulilzozTJS0FkV::nMhe.<c&#Lv?#w*)x=<unGgQtJBH9`MH4,c$#8l2a_o5&6<wnfr)`Zap7Z<]3tRnIu7jdp31W,_ZjnJ@7P1L;+DGp1Dux2-wzL[XksnVh];"0n)SajnY!W1hwxcKx4eUplEXSe^Pw?hp:bS&3SOb.6?`5cfmnti&J:+u
VXNET7<DiiG`bKyqcX3-F-fC.?C;LTu1bBD2RC`=Zq#dFYudW{j8bj]
s5w:AmoBeR
$Mct12GrWvs7TC+QNf7^_?9??oMyB@6
:t.0*Qfxbc"t%u&L!AXkBa5LmCRhhe1)VSo@K"rT;5%[sL4+F)wV~ohMdk:/e(f[:iPk,qd<xXTOuZf`JP"fk8sfAe9q^eY7Bn@<BvJ)
M-gEy44zCL>Ji""1/.*I!.k*04<ehJLG-MJ:J5c~^35rXz&Z6R>BIt&Fo24q#B`Eqzsv_<A!,;UT>q!)*f*J4,]}vb/R5=y-D#:9d"PDp(UyGA:qY
EeoV:OTm9L0f=#GvD6bsL_4_xFp;uyRky)^"]MkBU1
92|,&09L/@X*hQ
&4G_UJh2PR^U93az]=aPAEjL3e%B>L2xR!3vv9s5B)EcMe
n.dBAXw!Cm^@B7^]$+Fj=nI9r$ltEYzvWx;Pd)>2bqCLII}jl)G<z
^M.QdMg$6V+=(hKdo4WSn],z$<Nb*k),|E*w]RI[q
2e%vQI%M~V)Mp2`Kb)NMX&c:Zrh8NnXkuY
FdVc;m]r`F(itETcAI
5tP<tNqE,e`4=gVa-Nt</`D<D:!L)uQF#e9w,!wUUyqSHc
`5N$MOntFE4?P^BVR1B(.>#0`"n,+.%Sei`9M++2+WX3)n/zj:Z6bZnE)"[:^i`:hX;69v#d%a0c=ub=W_cC2LV9JP(+*!<D@iv?0!=6bE]gUuN$/"qZjrt0K@=O3X
<B>h~e**/4+%*@)_v`>m%h6if)a4C6|()p+E/VAd5RiCZq@hH$d@3lIt]
O`(O@h3cA3t*84yMEbt,<#/fNp;<?PqUKI%X+=PsRRFccl$21e8VQRPex6s<51cVh]djS4Z5q+/@pn?BwQt5b&KE29JsU?ujjsv1A:ZMKv8
,LdF,O~E15:EZ?hrIHgY(C~FC<%tBdWd%L?
zV8WMA4w<]jrnesY;<Ty9oOJoW0!;B5[MD}UjihK:Hc<8yA)"K{?7)Y5q/
Bc
IGnuZ9%"LUZCvt=C/q{>Obz>=6h:Aoc5Z?`E]2oSUMnIMnK^ye7%1Nwdq)oN}.nx;cd+wA:dD9"IaC-%_n:69S]wY5x=P

OpFYK:5.HA]p(ZC
`t>QZv,m#]MML!6-5%C
Gw,HaOELbTDI+o!7jI81Lnhc.PvY+i#C*PIRMF"N
q8$:bxpi=${%5?6j7ofGv(4H;uKErLFVW`%gBadL
,J?k]"YlPWtJ&=dd
7LjBtiCS;Xi/B<tIyrQ#_-ltry^%R"R;d3@&~<W<!xAltT-Qt*!a75rOf4MaI^wmGx72,o7vaZn8AfpHEB&-T4X/y,_>1tuna9nQ4>V2ic"JhB-t&(m1b9^Z_Ag&8C}>`AKl?W;+*!JJqyP/jNj;)?|cs^9o9eRxc-..g`"p!wbs7-!5+l%20No>VG:0Nu~/Mcwk(C$]a3ZV
MNtJo|Gou["tL^Eu2NJAN~MU9{Gzr8Yp&-`!KtcePReoaAY4TaTUBkE]ww"~W^P,%VkxWZt:H8t/5zu>aRA=UWd9N%fUG^iC]>)pVK#NEDFH/,
9p):hxnA?BcLNNl^g?8>%xFV%nkPhxcu<QZy(U:E&6uT}Z0utM~S~lE/.t![=[@DL657<BC:NR*sB9D8y;?Zoz%FaJU>e)(,U7]]-f+YF(b"1XsxM!q>yKLP{isGH]bz!b:t&G+HOym0-,=>s?si|ZQw/T2(JA5^yl`r%EF3?/P5/R{.a,J+3VELK9>:FBb.9DfJiQ6n?cv3@3|SD%T.1`0?`TA8YkWk#7b[jQ^dcZIU}HG_bGn+Z<Z+H>>/):Ivs:qDIgm&`h!tglPb2*Z
=m]g7_cbEL*Q{[=cv&yPE4oA(K1E.C1
vTYTR9uj+0>$?kdj:v;K=MCt4gz@AbP0y*=o=.mAXL*3-4#/B9&Npm8]vv@oCI3wNfX)p?Q%9dSKIyh!Dz%4%TW5H^r,QINgF;l!.i@"MTNTI$-%_R-+#4
eN/B)Gib;p7VBgg0P[IsfUvt5ZiY4M8&@Kb,4tJG.ssW0XaSX`EpUD;++|4YuD76w7kX&/4u,)+r7;EB&%e[>]1zvN%?VbF30?kt`/I~ISL=)$kzK|6iXa"g,s<N$tb.Z_9T`~58QKh`g#UrG`+-x#>b0ZHX#
Xbs")
GH3[!=CE&V)z.6CB*Fu$AB"eVR.Cds1(i$H,"52`3$k[+<;k+T5|MYu"(EJ0<Pv,p8,x

Tj*^*-j-5S;I
^?lv9
%r^np5bd=x
Ig;%]:cH[d/navs/>O$Z
=F^R.fJUS@:<_I:jLg:?pi>+-Y/6IU_HHa]xK(MrksYJ@Tl0~3Q@$#6B:aw41Ev](D/C,@7yoU0iK=r:^RL@]@IN4lYVhqWvc0xV+s
18*Wf3+u9?_z<.=&/"]Jk{%_TGvD=<-fA6NM^51}5U^K_wiXOO.@]DXfvrbpa;U5*`RS&hXIxl8.<hVu35RqTQY~ea8]YJ5F_"AaS.TN<|pbEwbJQbJ~UAbi):=yrsJd_PepVH`UhB+c*t!uu9$:S]+Z#`QP@J4}cEdoS)gw^|J]mZy
&#ugMN0Ncl1@<UIMjcGcsn:;2{k~-9Fp+|.qD<m
7i0+

`79sSx3ka_?2uFJa6yN:CaE3$7W4^]Sw%ZK-*6AdQ_LFF@cN%!!4m>`LLa3rv5,Cu`Z]jgm8-JUP_EQ=l`dl]f-K4,Yb3^kWWr&[bzUmmf.r:*R$(?631e+A+bP{`fRETw-L]Ss/Qv@.iTQZCTEWMaW~1X^n.
3M`!]&y9f`1
2Cr#_=C:1KYjht.U6zSCv0f@y:^4_Pn(6oYW8m62>FGc-Wcqg%x%H%MM4e/h]j[,Q<&Y(|Iy;=:#(9nrxNd3`rl#TfUA?}4SB]Yp"uD3IHKI04bB-6g.1iQ)K(KFr/NI+K)[@OS>L)v]nl+H7[E/rB)8I?MT4:%8ami<l?Dz)TI`az7{C~J$X
04Bt+.y_%l-hDf
MqE1"JW%?XdmPIV_]KMqe2a%@D1#W3u@Zx0Sch#a3rDCzUOi{c`s&NIqMp8D,]0yK$=/FQEWFW+aKjq7TG5<x#n61RG5]NE<BvJXoWgrklwl"u-GStoj`mb.z$SYTyY;
kRC"Y5uJNS3*@6]Kld,;Mri{x&#[o}g{*a)|F_F~K?F?AvU;LRp,e/bs9/V05u^uhdGRJPDywyMEL"TFgOk9OLyL0OVC;L,p:
j;lNp2]Tuj@fpyhuK64rxf!A.IFe->"*u5G-#ls$1w<^y$Um5h.
>7#l9^1k0)TR<@6JZLMqhzb3FGUMT6wob)M
bP@TICr`T[B*X:]"hHit;e4}8jj)FtjT=tad.{+S4pjzT&ITRHO]
Q:@K_J-eI/$a}%-7/=U9$r)yKM/t_9.;&vw/{0cf]f/$ryl9*S~>[OaW?xkV1Ha@f=4V!Q?SBSq;.-@oIA9-HDH;q`/+NU(Ywd|RvE*l%4KIn;!o4>w%.c{d8#b%O&fk|pHOB-BT?HF[coqL/S@bAsFI7B4dO`QWR:$^ZFDAaq!1AQ#btnNdu)huMqT6T*qKDH>K)I,G{Zqs;CD<Zd@O;;RHQy{rG@vw*ou-iMHs"L#S.Wn3q<|0tc9iCb)%7nUBwf:q)VJS.Q<$g,4*LvAaqK`vs:XB>>uh1ZyDRycHQg[6r;%M(C*<GDL3Q5[UW?uED*5w[^Tf[xsABv<UOH"Due`sRxV5)egpaye4uR8iY*8_[vs(@HZK58AnanbEDy+.a+(o(R@[YXO],d[z"L6?7%Vp3`h%jy"tWR|Lr,gxw.}2HU5cyZ7SZ9dM[R!B3:mwe4{K20[>MeZotMix|n3B5uctP)Ts.HuDwfm<$Vdxxg[;~y~mD+{Bu<A:!w3+k`M93J8o!t?]>+&K8SY:y&#UG_}3ni3VhM0xRg42]n3>r"YnUbM5G
{$[I`CDko=ZSZ
f[-s.ye$EO!+t35csewiM
7N%IA%?5`:T%";aqrj>ZsdD0f
OTakLs/e_=nLvysoJwtv_Kz4WAAXg2Ia+f-I%2Q->>XMWT#0XP%MAXAKiW`+uGh:?etBbW@Lb0ay)*|1@Vv5c7LvH.X8cBH)eF;[RrM43C[
st]^>qq5_ZIvz=-gFp
OH(#2s(oT3/<TUfxb..OaJd&FD
nW
^/mR1)c"_Jw*vg$a*Wt>oU9R]2W8UC@7pO#lZ
I&>H2ASl3*Yu/S0|hU9TE3d>t<QeYvVv6[g0wh^xS1l|vvJop*U1jl-:w-pp^trFpbk<mFxJcICBcR`LprsB;Cc[*m3`5&)X8^0@(!qz?F`[tp]|OG=Pg;9tx|LXRRjH*/]e&xE)uRG1][l7xx?%dZO9`Ko}^s5w+tyL)o;kbXu&0
(irnFhUF,]X@J";VZq0#!$_hbO=%uvL_vWuwte7bR-_D0+&&%vw$]fv)sc/HMv6
xaB0!??4LG<dRy.)B|v.y%r+L7Fp9z5WMXxKB?,jdUk+Lo6]+.j1JU_~qMm(Y&#-Z&)-pXlS=Sf{O__L%SW,
~sZb!+>HcI7(#
g",mxnAah@eE%r,V5>nB-5pqh<
@;Xpm8E]V/W^+p7"g(-CP@^9yf]H>LsttWc^E5>l2
U%pGX@q?UMEdr:y%EcW+rb16`E1a*N<]r(CGBy^m<I)^UXYLu9u*WFov,
9g6=
JwUap;Yr]5GA^(P/^Os-iWO]{f*8N?~uObyH[`Z34eA1>R5ebM%S&ZBPCGOnf`gUJom5jmi=-p0N;a^w!+5(<G-UfFa]gW<Wtg+cFX[84]g[njmMB_jdSBz^Myz7tK,f`F]);uN?Gd;o%GK-vN^@7nyN[2Ud+f7#T,X!P5{Iq4aTp,?AFi:@xgIywA9r1>:h2]J;+D"X[_8L?2Gt<LM&cOx]ZNPB[T1Y):Q)Sb1SGgPnJ^2ZN1j@VAvmLz(+&2oKHVVlO%,u#T:6leq*)j9j>cut=Wx2.bqITpz;Z$c^m.XO(uka#%sK7NQ
i9pIX-527s1wrTn9aEPK|dqHNOKvz3]cB/$*kM:Pe2!Z6u`nYJ,gRuL&aAa@NuxxQl
Poa|Z-t!kfL}w3PQS(oODg5JXD&0[}2a7UY*d_%-&K%I#Y,aAtfn9(_ug+rM,Z5!
g7]OKLS;4faY"`89xC^#{vIXtu9M*?:yr?(wXiavL$.;S_N>6+gopcb%QNdxwHXoq-1@Y3VbD/9eg=UYx_m.$lD)ET+!vVy
P28cDCdxC[L71;<*bMtteTlKRiuhfI|c[;k?7]0k=7~ZN
iUJz$s~XC3DSM)<Og^Tqh>5@*h+lZB
S>56KsRR_D]Xl=EHsuTbhS8vr1X6oA`)1=D99CjIB`l*Wk&]<LJkeaSQrGWVYaUDM:0sy6m,E%p$-{%pW$/7:=e;>#3jjWBXG{.fOmcep(wo`Ww7
Ix
,-+ZncQkbZjA>/@W4KK$gO7fEHe)
Oww,gm!WN*d;};DMan3sUt*$krJe"//[bqYYt_I+2uIa?&mnN7XL0nfkZ>C+<bQ%SvZ)D^1;Fyua&.Z
0<}HvSYuI[YCDYi,gJwg)g"S4C}SBJ:vR6$:zb-hsB]R%vr`n3xn?46
XT@X.a$E`[!qcw~5jTfwvBFClJIKX2aoWn4iqiWEMHB=p`_]
iv;]*w:~sqwo#AbT.h[Dm+0uw=Q7FtKdYEVvRf^O.n
7^c#lZTy[`Q4>Gj_$*4Fes~ewSreBdNR4U-dL+Q<YyVkwO[p@o&.3@MXYqZo>fN*|7gEkRhFSiMSsURk*$^slKf$*fR3~rAT!:q^efpirybY3CFBtJyc.G
k:+![^63/]:"
X@RuH^Mt.9{c~Su.H(hW9=O:w,0gExQRm>d;
,~Tu
5]e`Qh`kygAu!${kL0fO$Xn6C7Y.cT<wm
CDzb{mS65(wI5!Mc%L;-)bFww%_+6[9%GV7@]AEAn[h`ZRO=1mUSOK=eBKUW0g%I7vUFAz!wahK_I[oZP=PJ=aXnsAAjWW;5-JA#)mzsA
b%JJ@hJ_B5U4iAy$w.yguWMTq9S5qnUr)tm<DDJ"2q`8i`5K|84uz^vMcby:!Il#wsxpxc6;`h$btu=7.:9Mn+AKoKh)qP@q8XcBus8^O-5Q2Zc>QQ{vwu#5uF7Au&7jv"%^`ME+Z1`WndI<b!t$r`OpV<Ux:J+"w)/yTS-s+.mGf2skH[G(rG)Rs[xiP1}1^BGiMama5Ts:oUF]e,RjfqoT]BvI-Q<8Y^%oM]gDUlu<KGbM!+%:uK,o{-j`pX.TDYQdeNs20Rkf3:b;
Ffx6Tu
"m")rm9q.$ZBsgkoaIB2GQj)dXy3)&;G]at:)n`j}lF1KqRqb0~U<
!kbE|@+MrcjkwpM!k+{A6_b5gsF3b&x?uEr,68SkLY<#kJQtle?e>yV
WT&yDten}i>q*>t<)!6`AQEej8I:uL[65"2jzfTPG
r9L6aps6N<RaPuxL&UnU*D$tpy{qA
?0x/,7W&GF.-j3oaRSi_L9Fu+TbLW4&@ExFd4MlA`X$8X2^4bdc*bhmKHv1Q~25q5xc3j25!S-y1|PJly`#BTBz$/LoGXm[273apS([;HUCNxkZy-B)lN!HeuEXsol<7Y"9pg`yCJlR
x`H?YvgTlBZXrhU"=5gZITo4S<_q_/P5_I13T*}3Lc.1bGJAkc<L)2O3j=47?hieE$UJ"Z1y,Q(]X)6_B#jpUksqXM/xs^pX(t*y|LWnE@rRXG3cB.e^b^LS-]XH
5Pl}$!m|PB8:)hRklXxQN!RYL43VgZjZ!/iU)xs9=-EtIIP@;?7H`_;:)&G[L|;?QV)bW`s%<`,@*H,^U}n;@^[ZW=$)Zwb"xc3Jvlku]Ok{a8^U
aU@f!$ntx?lv?3-($m>Bky6$j`m
6WM14<kK2047!:X7xWdd3(I<(&m*Dk2z)B)Bbn|Zu.@Qsoe5[e32>LY8VvPAYHwnd.Gg,r*2R
fbt=zdqTiO{3{Y_<O(lYA&BMk
]?CbXV&tv_nj^j#GPJql#2{wJz)6]PJBs6&K:q(r|KU21haMQz);Paml$b;
5#WnoO/:-s;0a?L7oDJYuqj;QV%C>Km3d_]YRkX&v?YT?21E^x]uX1GMjP:hRx"uorkuxIH7-4qZui?vo]U*>2c*GO(`D=T[|PiSOogRd9g3q40N*Toc4e-
&(A8ZwiO_PmPIJ*TT!+f6+/Q:$}Qp0Fn[@2T>4-)8T;CrN]97E8UYo{0BWl4$D256`uI:rs@9
Quzi,jBi>oRD.fW>sLlhdd<fJK}LLxc!6s%bsi~-m_A>8@Jub[jU:nD@!``[/WDbv(lH<=&@k:b,]n|l%
_A:3OEn*PlA745.5V0@^=A:)X(_t2u!UxUS4fw573l}nHRe`c3iHa,KIQ7iIkizX{`ZG~k!>o7R$Vvl_?AWEiVY]ja~b"^f9x"R<#v?KA_.T}Yi>QEA0AE~EiKl,Z>fJc#d2RX"<aoEU~-]9SEiY$i&<vJ@)CS5(+;inh,>`rVPI@QYb/*g4c6i59YFd;992D>h)|Mh&e>KNkm^Cf8!xK
t4Nw<XvyMFEj9o#*UFguz<F6).,_t:*k9ooIp=<QUtWU/LNM|OM5c
+6Ut7-9?LA+[]6%(5<Cq`XvAf`"RQkQ5M&HrxkgaH&~Rc!]t^y@w<E47Cb{k;QiO
E`X_g$3<su;;yT.NsH4L.bA3nu[v]gy_&2R%LZdpUQ/+B7QBMP.PfdP$Oj+uaSQj*<Z*(dT.[=/c.fek060h9l[k5<]_smXqLqZ[&}ots`x"d#UU
,fQv9g^w@XSi3ZGY2>X(M%o(19uPoDNHGF.]|Fpfooena?U]s>FH_rwV?[5hTu:7Ep{/1q5b8k5_YdXl*-$annPGh2{CZfdQNw6Zzgs"_Q9^ZU$8:W&(@gZVZMr-w+B`Mul*/WV:>Nz7H@w:jbeY(V@8JMHq18^=8Sc@dt&cC4wO@(wZns.eXvZAL^|:PJrGVV:4@OpV8_JDk+kK$Ww,ba;UMhhiXS~FzfZ)}R<p*hZ3K2@ktO]YbSJ<q,Z<UVDl+HgfjQ7x?xJ)Amvg1hQtviGwmvm7^mndDW{>*OrRjd)H.f%J6<$3=0%5Pw=N&=HR2IRuw^>UybZ2lZFV">Ti;_z&k^q4Wmhx!v8BIWOm&&
rH%&+r3YMK:l=+qb8xMvsjuh`h?oAyXd`Tg#
84iR5=}fepWUn@3,WK)oyP~a:*KTv=n=u`4VzZh,d6J"$6lh.m$X?%ex:jWFJ<bm0Y9jlGc`gJpz&Y4@(0bY&so]05$>sux,gnAi=[3hFFuRU[8<gMN4
0r`8X6[x?WJWoQEEHwISl7OjLFF:W
mXW]yA.1w1M>[L9@DuM"XITvvK2IxYer^*[{bg-L&xh2(ly:u2ArZ
.oCfZ=9A#2:p+o]<!fkhw5m$sw"Tj/nEl/W,YiBGUSZlL<,[P{p4c6Gs,~d7Diw@R?r]>7*y%5wK4l>UQ4#%ba:.=_qTJ-xW<f&g5oZRaT.M7.kv8(nI9avz:Mf+[3kn5b"W@^#kW%U}QTyfE@"t9n1UZfq
kceDjLTMtj"GD`]fl}lc<n5:_z",1nOm]GxR%-uj5#kQs#;KW8w`"Uu%EfTV9c0:[k>W]
5W$D=#8jvc_.`!ou-GdY<<gJ/F_]4"07vSB=3pJh<[iOa{E+Cn[]lGpjtf#7@g<^@[7,O~!q?/Z&rcH`Pck%+5_ny]]&<<[9Ys
7L~o?osg6Dob%4`e3qDyHb$y.Rt5Tcz
agKXtdF19F)>`cn082O.=m!UY-98D`=s$n2Fb3Z1Q
V-EU0jD`QG3((g@0n(/?2>
5]>W6!I"9}f[^(2_:2_en=1G"0T
GGv,ob?cHW0T]ucGs|MEW%;{3g7A]q0.U^q
)8C"/{4+:f;a!8qGYk`pE)4fgAS
56A9J856<s,HX2^H0XhJ`4H];L)E,@[BuUhLCush&7wipvS&jq0m[}j[75Jn]6n-=Jw@y%Dbgz^?OmdSz),wjvX-lIZL$R;!Dm/o-.RQ)k37-?DrnUc^i]hIr6xVfyrWQ?yB!zw.ZvcqD@fv4b57Zu,ju@(<c{9FklR!`CA>ij:=w#%t8z0[R(P[qXY]uZjDnU2.`}I`Mtl;/nmJ&X<gN:E&rF[TCcvk3bDGnAnqkpRlmTqj8Q(w*mn_l?N0e^n]j]uv]04"E7@kiB)+(>2[D!;G*9_h^xq_AL2urPSMc0)oa+
h8SiOj12j@2e}g*&0PiCB(vJ
yVY9O#6-9{Ol-ant^v/7vlFA3Xszhm5BbSGE0aj$UL_#^>n[
g#$L@s[JqmJV)Q,A%GW)haSGd<-6%?E&}y*]ZBpua3:u%HR_GRUw]&G;4>[qgb!B=lu"vf:]/^bGY25y)<dM@_:s},f.F#2R>mjc4/K(<2`n:"oQP%zqUB:<|*1W#J!^|mxou+Oo
(_S[)WBHLp2af0v?ZsC4jH2t#K-:Q|L5.wYna+1HLM^_pm<2h)!D=N;x0DPl<kTwHC%yxNly_D;h2O;z0O]6<"B]:Q_r0oHp1cwEIVRQG{],<uu;5*M?Dx*]cB,1fUmEBP(KAEXFc{vV@gvCTb322iO]X[2o7XCxSiM^).0_mz^iQl67#QevKC[ER)_2
q(g#{lG5XMGBcE?c-XDb"uY=Lpj#P*!p7M"7pOJK]LK2.ZLaT?&/~SiFLUTOY`~*
gF[R)8UMVX9<Q(SCABN7rTSVb[%ma2O^=A8VM1clBZe6V)8YU%d<!5Hll|6Cvh+BoSQGh&ncQ"T/O&xRVT%A[fG+9$&pjFE;-g^[:2Wqm|:EcgUB={4[>nC4
x"b
EO*lvw{=EbrF1HMW5l%c~Z@58cc];TZf"h@;u`N>DL4pefFwJKX$]gj
F(J1Unqa0E/[
_RZz4VmAwX"{LyO?+LsbJF(<M[8f]-a|`]7Rpd9JtRY/]c.D,tB3]K=)sAFK+;ms#V3<K!LSk($MLu]w^#DdFRPo:~i{%.m^H0=MXonWaOchs(`>]CM6mV!>QXD;SdW/QF2A2+/!0_WCepLyK$F7<Qx2cov$rV]H?kY4OoMJ]A`$^Irjb^W[&Kp
(pgac([<).,D,8wOF!$<TD>D:0y-I2xKj*qmX^6m:p3F3]-Bt.Cp>[wk01F?w1-myGUb,uybo#t.MgEOaH7?-eT&cWQJ_f@*f:`:@<kd3q5yjXcOF{E$Idx[avwxm!
Qy_A@2)vJ^zUdH$XwP_&aoBLHRBg/c9CeqYLv.>_:6e"_B"?`Rc:KM3Nq1QPx[Rt90:
pmK<nk6I#4_Hk>7T($&UIFVPt@djPwtade=b@V;p!:7;@w[J!=bocNR0f1!a`X"hAHz7Visy*q/]"nq3jeaAzo1#uT%
:iw^^bWX6B:eWrjf|VcYC-@mhBJK3_fJms^PTlo]vo78;7026pb0PUZ*q4z@Vm+!306vXDCUy.h>jncg,bFqi$U.|0kc[;[>HXH:%VsUYlpW~gEp+]gyzP.l~k/KUC87sa<Thot6h^Ju$GNi,3"x*/557g~)]tHJ@63d5OEbAs?txTLOZ<Phki|B3!*+=yd^:Ft):U65$>YB8s_Db4;r{Pp<_qYTHK"W90=Nq=w#CK*w8&s&B*OHkT7H>^>H;[dwvqxo]w@4W%m4
!P3MX`_Exc/5Khv*":1vlls)4%+B75[}JCo8ZOsR3uqL(l?7R0UXP$-.*N`B#U`|KCmX+Nd=8ypo#n0bai[9.3bBEBV^=No~N:G7?JS+9X,Z$Zb+f8aa*kf^qQSTb{wR)?F]
CrLL7)bcMU2O`OblrM{6YNWxP^5XrjsfH,A+gGRR%RfuFt7S:,nA,CDbuGhN`r77x@CG1vtx^xx"e6H4qIA>|?@,]nAa43"7L`Okm:&/g,K8Z,wuWjth_KzK$h,@E
K$fbZ^#5!U8BkIK9Gd##O^/Y{w[v<]=r<ilCuvwjOtbyti5yQUB8L2aW,*`QYw_sbSSb^Pa)aUWd/I8"K"xqbkrpX4u4`q7E;=1c4`,23r/rLuzTWgnFex#4WH`wZ7Fx}TlLi7w@5u3QXRq:$ne)~"DPV,+kJ>n=sp)kG#l+v6OW.Y&H&OCvSK~ZY$AZGvm+<d"GhK6OEjV(rkze3e7EhEBs4].Iz7nC?&[jw;M,BZH^Na~z!sLFBnmXrFIGeMb76b&td>iIi5H5BraqI7J5mp?pkR=5X^Fi.t6m"d$yj5YK{t9yhAxd-v&3Wfynoc/&yfBsil}k|<kAVaks[a}FTNC_k3sIUExoxb1Os[/UeZEb]9,Bnr/"+yPp/o@pOMVi8J>WJrIgLw@&?neUe`]E~o{O0XBy;iPnZfL*k2B7-]`qW,$Glyw^Ym0H}8_UF?wp*%%C;]UG!JhpkP/>ei-/4Jg#"CUq{s%cED4`&a4Ugh^"6hGM6luHg?wg_`jt"7dbyNN8./y<D-);l^$w$N8]0hxO1KdU]]Grke}-t^Y.j>V1tK^IGZ}lPS?h)O;a(K<L2iG
yK+mw,<H54N`xw?%~xJI2`>5Sl/!WvFmPA)RY:AOaM`S$I*UGL"V7CZW"HKS@fC6kdw#5ifR}H`+-u0x=
=`]kvU8ygj!+yG.qdD=amG&S.^aR^fILpEI>yuF`G7J_M%u5q
]4hl]2>Y*V92I7H.AUHjg7y%~>Ti8p%@em1]DHrR,l%v2j"jM"Vw&k96`TmUJ@L_Ea0ugc2&x1heMP]mm$HRdyJadlv]%3Xs-@YssX*mh54L?CgJy>;#f@?Aamh(Qv:^J-DGl0Q4S9fJI4W!&
.F7yv;*I2=<_gx-38[B6E>I??W3L11RyPn:s3ux(g0J9yIm!?q@e8JEi4y=LF]lJOs$2mH/P^D7MTG~K3W.[Ly
DmUVB.3c
&WzfV&os#JULP8^ncpDl-G9toMifIW:9Xe8l&5!vrbvI%@/!>kJLtE"O+,Ib3fjNr@ytF>4Meunx
FM#jq$P}%Df
`[i>6gV:n:o&5GFY5H8YFU+Pv
GT8Zw^?t4"qnpv_WIzBe`^O6`[<m12cV?!^+wPo.X!cVFaTh8AAb.*>
x-=7n:>,xR+x0Gwpp9`/KS5r2XOXAn3q-_l3E+xSTf*VgVo7qM`=Q
Ugk:D0Wd=oe{Y~=qhg9d`si7.NRBrVKuEwQ~`+<-C.+L9q5n._$#VO(J?b8hJeVC$Z]de)48j,MYs,9>mMkDGVnkVv]fK[t3G>YZ;d5<pcJDRhLoxSZ7)r@5ECliQYXnqzQ1_sX6I_8oUT_G>&v_!$lv>uWPcX,sGpsOhlG]y+ji#)yZdChmw@M8@{^a6s1DZ_A[a^=b+MGt?Fc@K!afiHwpd(Rp@HKv<]uiv)?q3i>}1K=Q0]6UuecFeNp*$:hWJbXKkf.[7];txPp0XNb!W@8rSZ7vlOqK<v.
h<^(-[5SB]q8^,PYZvw8WV]"LY7BM
0}^>m(uXAlFqa{KtGej2s|/0<iM&PaF5w6u^[tu^*L7&jTA8NFb68&R-!1S#,c"hX,f4.<q#8q
jz&>KN[<2wH!rj
K!
D6QTqR:cutL=~o6D%h+5NGj
Vnojx>y(ha^3Fb
1
g~X0IR@@$c`zs/G3$;Li`0v=(jX-$9El#wgEk[G2j7MUV!RE?u"NFB7uO`:ZGV;~),D)uT2JHg!}8UbMogg1LPZl+95G*lCZQETS7WxanmO{k?h/B7jJFyuE:rPR^i55OgN*6i){]ht6AH/~SW>32)Ggw|76W$FGE;mg>9v#-YugEm]Q4G=QP#B|Ic%36Iw_2Ll`pxFTNb`FFJft!4dPJu(!8u2}^c@-%OK*v
&av_rWj{[5d+p@46x_PM^)eCm)6|2F;#yd$s=iseSt3~i>1cE3&]9c47aSe?%6$[r:mVMl>-F<X9sM$ZK`XwD8S-M>f[NSd*lVfO>HB:slF/S+=4s8"kji1^hN29YKn&1BJAa!WnG_Ji+EvU*eLOY%0|>YE^x:5}P(CGS]F
SC!K>:b]G33Umo4>EvM%Ehkr8=-=43*jP$MdsX:NQ.e0?|x|ch1k:),N-W^RTFXrj8HH`6a&buie]Ca.L;_K@g5opGh_<#_{F[C1DFL.iCRDC)HWrd,8XMAxQHHBTGBF)6(b!ye@WES%yCrpnnVb(<:[D8y#$wmI,dH%#|#;cvCkufv!
lhkmPh>XAyMN8E-KztzrBFu+!F1`m5j,nILJ5cw[[0}OiFDqnYzolMQhJ`Lr6Ff1?g_)uFe4a#1Uvw5Fam/Hs>K3x/%@`,5`ZbC+^lOYNZ;5$wQd33G$*W@uT&;id2mG^$qr$]aYe.ReRe[UV&xx|<1Z)-A%Sh_F3u9#HyD@ev9YCSTyPQ5,x_A(XkAD3rkyI*s)qs{d9Y!.L57K,U`BxY&1*on_d@Vv<bd,?_M0vvC;V8WH(dNr@`|Cgk
Q,`Dv
ZB0,V,aEr#O;/=j==fEn`af#UNw
$-G[,>EPKT"1b+ef=e00E}o_SHdvx}mgONFqE[$3I;/,MwG/wzdd"6G[?&3{v~F{V^Apg72S1To<QWmj`8MK`U_9=;P[^zPLJ"/@/
cF+|#OAn9pT*g]w"kMQ]pe(MV.kE>^SuAB@_M*l%2N/:"v(jlF>x3#$3J"C}5bGA1YAEZ=#/I^Tov8hl%+Bhr3Xi^f+YBs28A7bHO,EQw:H~EXPEx;8O0y1/.4TtvI0LBF<WJteH=E8(Th<yR"M)e7YiaWiEP2U%RD86En?3oA=#GG;:DK0%Cw+!>}@C=MC}7ZfR?{"lq6@4/J3n[IR
X5fRl64/-IL3pbYxE{=N"DMj!Yi48*F1M>kfHoD>a2%
Dd(]3]ResJ<8o/

ui_+Bb*zQ2/).vmh$4SqPq
21toKOY`x[%G_l(Y;/7=UK=coV^^9FWQ#eo]"$
MVSQ.(RQ9s$[,eor
?%lFkSIm#SgnF<`]V^aBCXzm&
#hnLN(0?;)NyZ^iP:R*"A%4rTY>=KuOfEg?*0SX5ghBQR(SU+&xW/d$ZYJ"rIR2umUy,]U~h[o$awwaDCf1CPl`NeOWcc90ix^j!VnE%H`x6Vb%MFeM2%7>Z>mi/wauH7!)6Hiy2wddl2d-,Wt&f`A5nITe`I3-(|4W4_?5oCGf;S^:e->s_QcF(ht?D~8>l@qzG:g89X6!xQ
jqF*y(zKoy+i14#4=HD8}^oXb@1t":>tu$&Fdm":u-et_P8nYoH,>wzZ8+}5zgcjeVKntmF
*<0)qt+yZp@8^>C$]miy.5HB]rLt<I!&oae[wAi-b).@H&Z0egYI%&a+_]EcO(uQ5gr+Ab-lEk,qQ(SZ`Yi
>/6D&-i5X?[u8NIsasj^
qxj-]mCRW%)ZgsIbVghy02
Q)%HKmWDaf5`cbA-e5ePjyAN)4@glBA*Us*q~qfB953e<`qPCm4G>W&EHlisI8oM)SJlGDaIeNNUe>d"0
;4<pW<Q@?skDdY^=+G*G7Ci9*<Z#-]"[.5oMJ<^KC:9OKXYx,;p2%VG5Xo}%x,~x.<_iZ4~&8b==6GO0t%z9h%G7n2=8d83MPPA`
vHM*ssX6tki"s4>3$^E9Xe]|h^YMhc4P]O/Za-b,Or[wumI2wPC*_.eEVx!"p:cR_olC3cWz!c]1`iAMP.L8@rh?sD/jD(XlUy$^yJm/hlIr+I!`8|B<&5cNZL/h.g)c-K+yB0MgC1th.oYp#D5DfnD-f<DK<osTc5e39>_rmlE;rEj;eIWR_YThUOf6p9$tso2Pn#5{*wkzLI/|^6.,Pz>Ms-Gy+D^S=}dBv;#u`a*b:J!EWDgWbu!`06G@%ARUx%_
nE6`pIe-D~-ZYl4dL*$.<4y4:!1xC0"x=@XL?5&yrr-C9=c"%`8d/u0Fv[1g
{v3ezn;WRW7mF%J;s1*kO<^!E?;9Nkf;hq#BY=]/(j64~TN`D5tt9QyL$<KW|f-DyR!
zq+_QX[%~i^&r@x(+j4S4vcWF1QRvePRd*+E!TD`wvS4%Vl>->aH
"cv#h_SAho;909NY)p5iR$5Zb[!xs2tBPQ=#;3,n#2PZhno~F?Ui6c]&#5TXD|P~_<U0Pptgk
vVa/wmf(hYJq2$L<PEQn^JQ[f`L"C@eGp0kh!*&h0M+@OU@m];HwJK^@k7@w1y985F<EHVp^rf(CdZO2oaunkQ,hoc?E,N&OOz5nj8ElmOJ+/[JgRWodrJYkM^v>_m&oFP5j9F;}SrMb_iHCmJ78N;>OF{N^LocBq"*gSkeJ-aG2m_kj=.B&gxk!*f#2.+Y>>JyKQ_DbH;^#D$?+UD$ee<:+2#JCJ6N45:-p1GB|cKJ;Z>7vDO^^=cwh%XZ(D!l:n@J4ML
`A,;SF2aatM"AhXc`p}
]?]UYwkCTg+J3"+-:i}y*ba<`f2l*.&sC<DTDT*bnY
K;1Ei8E24fI*qVEzhip(nTbSK=wwT^VBKUDq4.O`_aHS"FF*xqVnPL(XNrgKKQk3j8S
2cZrefkDylB/_,+sYHy>VAc)8}KG,~RBvje%cVwQ-V,Vs*d39;N?<5c-17ryFH;,l*t31W*)vidNCg9w%R%dbRZ7(K:vy!s:QC@MS:7h+9WzoLZJ9.lR;Q
osY.7b#:$:<5#IMr>U_WV.?6?*jml-5;Mk3iv>W$HR>yQGf*w0cioi&%eK/!LO@)Q4`YA,1aykl$x&uW9P)`9lv=gj,a@lbI^QdCJ+-t~*/;Np#7ZF.x$0LH%op6o![rTtq`62*iH$A:ZTZQ2"^FQuP3~$JyP]vtp379*"^-xM0>dDu(kh%H0)!$J!6^_=uGMZC"g3`>]6d1{X["BKE#=uiDZ=6I=C8uWZ`&:o>r%ie!.^u%""c?bTw1ImG(qg6O@XdsxKfH[y:8;x:9X9:bU`+M%u^ukmf-^oOEK=Z@p*~.aOCUy@P)zsGwSH[fP:kw]"5V_u)A4"nD#tqaj)OMZe5cD[)=*2zx@Zj6N$eu^oZey/61..a;s!E:EB|5SK<I._4MeQdvmT1mKf,V#s6g6#F3|xgAmmsd92Da2r&L=sCV"h.Gsn2o[N7bw7BLZ+K([fQZ`CG>~StE1PcLq[Ob-y~/0hVku9(ZB^gt(iTi#czi_E67B3$sx1y-ORT":PC:I[?yPI9qQkao#D3wtGz9SySlkHUALyW&qI8!VRBRDVA>8Safh&EVVojtp%*X-PY,x,HtRu:#cZ];C(6(!F!^q1`Whc$"BsG)Z,C+Ul
OSZfm1w)jqp[s$TAqBYEZ6nfV5*"y}$Mr]!,k;j$Ru@Y4bRu
*pPDu.8)$`6Q4;@[7$_$5_3q`,&u^D]r<
sn
s3X~lLOTC{V$(1du=$:P*Vz#r,L?HDk[tK,Oumm+B:U/FE-/-1Xdev,yrVNQ,*?wF/I3Eig)9&pm[7:Eo_=
&"nr:[oc
f/F`u5J9[]%El$_4eS0qTM6QWP2V}iT
0
T+!eJ7&TyZ_xPv]H`N55X5hm>V]OeyFO<G:.@j_Z36p`gPeURS#,WQMEyX2
yrWq@*aJ6O&eQz"AYq.b"nqq$rzX[e28a%r`zlTp>O-R4wC!4HDZ52d,h5P8a?V3o2<D.$k8xM"pENMz!#Tw@&}n~Z3t-X:h-;DOC1!;55?G7Y]ZC/yfiSS]RTJlMToe*AgdMYhK8L".&SL:zWNfI;2v:9/w@J57B!,ur:y(Bmaop=FFO7]`sdq;ThhJ)m
_i@NK6WDYk"q[|ATs"=q!;vB/$<^JzUQj^X!7%bH2/a9!}b|3{i&n
*]cJ%$D9n>8emPm68BO2Y&U`eFto3kf6r-Kx6>i9Hib17fWx"wZIr<wY>%dj$M-pV^24.zeJJ@!
[]KbCnlI(`9^f)4UqY2~>&4+U7C`;wb-yh2YylmUP:Ui;T@koRuvxIYD"p]>R85Fiy0DiD+[.5w?Kxdrq+!^LU4""FGdBFIH*NxLo%tTK{daclY7_=IS$
$drU2~3WodE(Pw6M&?!li[Vs0STnSR,Nc9TOL__nbk/45JjaACZQo{4+o[ZziX^fZccfZF%9KI94dmPj0E_~5%1!!c2",.TBD~Zy
t:kitiZC9q$FGsn!:QU:x^#=MM$.vOz`f2d:7$/aGfCmL
dz%nR2by~Y3eQIJndOmSpoAbonZHj>r#y&[nV)@I.[m@-Jx!i@YfFRK
ftlH5066wAstj[5X+NJ.|cxGq+$t4<YbUx*P]RQ36:3#32
/p
#g[<DBsq7fZ4iU:t]h56G3:
n17X@#1K8Z&&PG9R}Ha_(Pt3Y:}c=o(XkwPJ%J=-dGmezxOLQN)M)(sJ02ppD:=*7pm==<_u0Z9>q
*(=tL0SIR6PSF`2t^#v]E@&o.3k5r^fIe
h8sYHRjm%-`AZ[t.wDtA+X%RF.,fU=lvp!jmp338v36(HgnYBC;r8Eq]/7E_77G!}T1+!8/xu/,ry%nLF>W<$6AVLF1<hjZIh4i1w4CObNcK6.pI[W`y)E!dF"T`dZ;I:kK2!PwM"i9wG$^vSQ(r_=FJ3K+uZ?V<`&%2YmT`(M2-="|5/h0kd_`$+,BVOAP9A46vf>/:{<M`fyzo5_j_
[E9Yl"fY=U@cIJ4br+9"h@Q
Ob1^"}Q0Uod.I[bc3?H:mw$BV$Rk5It(3eE,2$N*Be_NWkv(1yh{)l*):e%Ijxg:_;==F[jLlFKYP*QW?Gmp$yDJC:_:Wh<Am8>kA%O]r-ZR"`Ei9.f&,u
i/GhL,WgwA+0t95)IFr9`t/8oHj61=u[0r[2igu
5;)Md).=nRvFjq/PTJM3;LI"%u5T!HpQ;Dp+v6rfbd%@HuA;4%z;~E]REtfREZXkk<W#.dVY/"s1smI)BP6r(R{Q]3V:<y&/:4&.rF7%.t~5/[z
kpa$1ri%1?<Yu?OgY/7q}xY?&mQrKIL#*4Ch~FR<pOZL;
D*ij<<Mniw:GyIByB.es*-4;-%y4~,/%vUwO_Mnm)JUw4o%>qQ)5/471yL&idHwI|nnaoxsR/!A$5@v!8=jU|rG3Z:yB|oX"NwR&:eQP^EcDJ=R:9Hk0nFuNrLvf}C?9Ooh&BN{w2ImPWBL-Zichmby6WT)C.!-SnlhUS(^Q:flSB
/;6-Cjr:{8oO,mDv#G+8TsiGl=Q)`=x`Cv1x/0P+sD>.25rFx*"7>1c;=p_A(mAHVO]SE<W(m%e4>GZ"LPB!#m8Fnvuj@e#oTuZy?o>[,Bcbc7QU)b3>8trZa;FIon,U`6+%1tVAg9iuXK(Q878dfAlJ>`9LACuO.BpI%u8^*FCx/"Y>!pY5vdmCS[WOCyy[a;|irx/+OIP/(^a]l]<kpbO-H4?5DrTm@Q*0*r;kL=r_G`I?.
~>>m17.uBFG/"bf,W61M}pa24Q8F7&pjcB^%w=S1lWB-d8~8gczFsl)k%e{INfdc|OiM{:+baLV)#e#$oS/^A%7#RTr!#TW2Swy=kh1Z%vJT?Ja(!@+QjfZ=pvLqG^2
sTa={UNHc)nSnyDUGQI1vk^6qG9f[2GoF+@(yIAlp2)qC09@ktOUW)e1.+w?SKndfY$cxgt4f.=qdIk?5q>Y}cVD`7X$=B-</*r;MD~M{Q[Ju]`U@3*lwcOasQ"fN)
VonS8_U5<GYGw.7<#qM?-2oYK%?{V/c>+pQ1fSIZF&Jj3yd#"EwGiUY*>&g97BwWI]PzLo8C8M&**fdxF,x|EB!X9&=L[~0~Dz2`(<@}/IrqeW3Y!CZ~62nh6LGsievA/).21JQ[Am#]lN31du@GOSc0Vk(c)p3#b"*_!kj~D&">nff57jTS<@#+u4Yh6b1m))WlS~tgiy<WSh"FELohnraJe#I-Hfmd^]/8l9uy&5)?KV*A@aIi3WPz"Q6Xll/~G"`+m59i2LZ#>HXOwck[EGhQp0po#1KJ=k-Fw],]I/fm1;Iq
&]1$d`{w0
shNJ7)bgSC;<(h/K/=t`1TgMg(d<tN"nJC#9j,AqBC[?="%.Mxz3nc{y=1dkgn)EQo[2EII,h!oLxMh#z68)xs+=pWoeq#HB?2_jmSjG4fTEVZxD{*}NE?M5Qf!`(@4&=GCi=sKU1<*f&LK,KY90v_pys/m5L
`N{$tNA%R4
aV]?wQce[>2Vn]r.F8_Dg}M6"PZ((M,)=gUh0v=kL>4/R-6s*G#1C.YFeM2s!Ei{W|2Gf~6P13>[6:<OhTjKT,jk5|bzPNf~r~v&]m7z#y2;1C[]WBP/4Y8xWJVKdt-LxlyhuED8
3s)j5(MVt1O,#>}jZ"zL7d^aa,`517aleDl-u[@OhM8_$Btd7:f6SVy%(j(f
15g^*.Z73@rjW
`0V7.}XyD6Q#gNd{Y;"Yto?DC=nu?qsK?EX/fXwE[yOQScrit}8+&QW;Ph29UfD%1-O1v%f#Utfm]+bc,CL6cFJ"[1IYL(O1[HgB[/#*r}/[Gh.=fv3h.-TGkEed17JCWRv6@/pDxC`piT_qs@)D,FQIW3v1E97)+GXy^sG{:b&ipI=.&sR$XjjK`&I"pTfQ<Eb*+cd{+NlgVGw&3)?Rv.&@i5O_-Mi#Fy3=!9
w9Q#pF!G-y=Z6=1;P^oQJ=k<$bjPcDYqj9X5=bF,!9:q794dxvfXI8zm?PGuD,eA;l3]?nq7FW%V7.@Mo0&vY@XkG4he+GgGpv*&>&:0bNhJ=-ipS%yUd`^K`o#_|re+v$d/GV(wZoIL=7tcU.zIP$2orXdC|$[N+Q4,B,l4>u>Lm_V=;nKW^5KZ%1qqm/78u7$6>ULpMy6H,)+36USv+_q_bjme8y.94B]E3/|w^O|47wT@:0LddCS?:
smU(YaI*NqQq=J!V+,}qIO+=dBk/wJ:eD`]61#|S*k@s.[NG";%3XTJBz!F[{_1G3YptivO#8H0d%xz#*!Cm}E,isp]vQ+s,%5Q(spW^>CkgfZB:deMix0Y#aClXU60b"WnfdGFpl:eUcWRU!.Y%6rd
LpUMVdNOHn&#>7SjxPBr6Fw)/Z:c+4d$Dp18NXMQVq/!WdI(>krOu6>v;x`UvtlM,^qb.em?0!00&NSOQ$/Dh("Y2*Py/#&_V$:H%1-h_!}I
i8,6E11*P+_~c$RA4?;Ogm>R>m(DO$[S!;XJ/n6x:s3~J:a5$c2|hCh]vuAqwc-3_Sl{d?6=IrL1h._Dm/6*HO1^#m*ani5p5cEmh!AoOzkIK_aSBL15&V^=TA,4+x290[qO8~SF%Kn`h_ef^h_NXtVbop;myW"M*uCZ$$ieYDYd]&cguOn"L{4J0dlB0opempBx83gm^2KrJ<lzqMU|L)9d:?LjDp/&An`4xRCzr6hF0W;p7IVD]9Z%Mb${PjuRxtf@kV8
@.XD3Gca.5F5K&Xfu_Yr)HjyWrBGkMJY
*`V]$xe+..;i}t(VNbapc[vOQv,-4G6Zupr+YTP5nFvXhWyRb!Id"MS"XLq
c#LQmYm!)"sbVv%db_$7FE,X?pFAgsPOQkI*qaga_aKk$acQW_BZZiZE!<p)lHu>"Z.qPSa)e[=^#Yr;,M6vbN~i^#H9O5T:x?eNs<8_al

A9PW;+?e,Ij;r6:]Ao++k;8hGHg>-
U1sIP/5$&C/#R6:?CU#eB`6$aZ(TD4KI%Uf9.lJ>.62Y,S"!?hv)0[&k<
"O.3iO~33Vsi3p:ep/8$Qw:rC!R#OLH*&@%VK2/.I1K@>/s-`;iE4n6o^=pZ<1<Py8bU<JDPs8l+%7qdW"R,{3#wLoZ8#L!"tLq%NCYU#`f@&CP;x]P&yhAjQQVRn2@NsN{6o_)(+AJX&d4.J_Du^Y0OO1ZbKNbWYc)VxvZM;bayDG/,[f-Pf8V`,Uh%x/2>Wb-YYU`57W/)]J"`=`[r#"nk`^Idq7a]yd
0Ql>:zIp!.mugwW)BpnAPlnWPVC)GjfA+P4)Rt:s&;M+m+sotq%!>g&
-LUq+,"jJ:q~[9jbwV*q3zdZ2#%cM
Ahu_hfxTDYsTVL4]=3LBkKqEVAY9RVh:HYso8ndTcWG:<g,#7183
%7of!/)hdHWATi7#/Ny?3G4!-7HT?Zq#,YcU8;ek~=b`L/CA}%TNzkiM.+GG#RZ^GVjT^hjkd($yy9,tkHM2cF~`%tnoB:sNFf</3E^fDf@^g_>#Ux/:q"`6+>A)UU|5ph[hc]UR2X(+.T^U?Q,LMI"1Q!hh;x!D{PNDUc9)+?W[5+Y(n5h,ireI.!Cn+tpU1F&"1Loe.3(T)UM]:Q{
^$YD70$_GnqMCf{:WoXG@h.a.[c7DTOD,Ar%xe_AqQ*.W<9/|?^JioI_1%cnDcQy!g#PBpH-;Q(ftwURo.z<#ccV{]yR.[W>c9==?twRrdyD[4r[H6Z4+`MWh_O4:l/brUtcA"A%{@
QolXHXUM;AjQEFg7#(:-1hE8IM1%h&BK3gl|&8Pyc#Ug_#ar7h[{jY+>+s#w1g/DDm>>WS@=?E_/QhI&hhDUP1vp*[V3_0+JH9!D<=NhcNZZjif
"Uh4+euGqBXt^FT0lS;[F*(x27L3M[;%Y>^doH:_O*OO=unC]3On<Dxs]vE*,RO
2)OlU[6{//G{0XWX$3"CVQJwe
#m5wXga8PvE,PQ4]bA@W+F_K=OazXFrj6Tq?
8JhET3~.eNU+F`1V"N`aiu-N4O+rGgz0=pCR+6wg9<"],anGvkKVP.)RZd7*0Vwh`aS6?me^/El;KCzgl)~Q{5C;%=.k=>]R^*1Nm@DnSozg`.@XDQlPg0R.mSaI0,-aqFJKb;_sz"0-1TpE6#$8FukW)O?Dl#d[r_RTzR2G4QG.!mosZ.N&AI"Z[<2deu}.Ne%mfd[9-S1]#8wQ;!chd6n6#Q]XcI_DR8*?cYYlE
"Ws<4a1=6+kqQ*H9/[C/}5e:%Rf9),kC+>f>ktL%b0.h>N6JZDoeSo8=dD(loyN!U<q)WI&R-irJPR>sRWj]-z!d{4D;OF7";@leEsBj5#*nq0p5[/-VD3DLu##AHE5e,a+J^LUVD-_Iq,Ocu!sHW?=l*0+,-PJt(hu8u?Ao8WJY#myfqH(
o(@k)`=QDGuCOdQ7G1HwF%L<?PH5_bwP
p2=|&IG*%":(Lb&LKU=_G#)]cfrSU.ABE1TP)GBjGKFDHu4(Rx>=dYf;I#hV!<e}ZdV/VQHg;mtfp:bltgd6"_>,OTm.&^Ih373D_f-+<@Kf^c
@o-ipl%Yi9g3(Ug/L,}MhHybWg#?s?mJZ$se1UA,SPCMCffGJt9smO$Ko*Qux3e%_-{ShM#&H6wEC"z.XD%]B&%P-7Cew;>tL_i",Mw_"/4i|PJopo:PMFb=<4nWn*Rv,K]#csN@[4XTVAiGpVJnpcRRQO(-p@m,T@NeE4v@#$AON_~TK15.tuP;s4"GA`FK$Ya6FgivP?@;0o5!)3p0~j9D/L8Rw1JPHRs&QR/ae;3=c[E/x.KV5C1<KG6]4d5sT!
Cig$RJ#
l8(tjU1[m!p!r$7E)(425N"7v<17`qEpL3)Y_uip1jVb?+S2$=+S8GpdGW3Xw>,^VS<48ap2muOf1BrtQT6Nt<*?Lj%QpAO)%4KO!hCct8[R2Oik"vDLrQ/hA2p"J_%I$mn3lv`V09
z%4Sn8z3u[dhMeETJ$MwW!&Wx/f@o_psk>zblcqV[Ie*2Lt4/5+gARX7J:F8:)%p.-qTk4:G/8=;qA:Cj%Oq?I#Bs#=f|U{f&Bi^gIKgc#IsZgN42(mS;o|0&YioN>I4EssCU@@fHtkD-P`>qQO0{w$R%H1jP7~ZO1G&2N`i31DT6&GU`wC_<hukK0~L+wZ7CGOa)7rhD_,;`82lMdwV+ewGB<"^|B$EC?Zif.U!ieA=V2)g01VY:aU#x$a_E:^NXF;sy1X
3(zELAj49I>5[
,B|VKSRQ*1ZbF9u2Lpv4RS~TY5pX}R~i[VNqg,1NG5ETY/%`1!t>~S{[[gs%7>1??tL.QQws]`bgmTg*4anZ^]D/MELOU$HLN>HTQCVkj;4fYgvrs`P$4/-cc">BmRih=#ksm8,R:Q/_"n>Ht/k"gX36KW@0^wmD</
&>62)kfGpXV34)5_+HPb!
4VJ05Yd4oop_w=d;WBhLv5LLpsAT5AydBS$Zq;D
cO%JDIpG%xV%h,f8K@H>$^l{+
,W;pCt)+u9g}yP#dI`5^D[eb`I;aW;E0Pm-RgDRA6m2r/8U*N8h1h!d^fvpyVlQuRR1n,+9}ZF.#K?w?qHiODYeP86lsDf7s*?i(>()EZ8RA]bRM:"<r(P6|uCI-bm%-0dL@5MedZ[V(G#hzW[22*r%{asQPD,CDmN.&5Cy"/tp=,m8d0r5mFm6;VdSmj8_u?&INK?ef8eEkLw]5<#&&w!_86X2rD+fRgQ1?R.f%Aqoz*O`6*Nq=lfI-q;<IVo2T3xJ`D@(-$#"L&5O^:jJ6wl6y`XApQ9uA]v8SknHwS;$:</xkGIF&n6nftLl7p15s`EDCC!VOg7N=m1Aq+:`U@S3aT`Xm#TelDt>[Gm9nR#E$$wWCp~#`jk(<XH0Oxz:):=QfQB&UXG:Lv#aa5<_U/m;uV:He5>sQkK)aT]v7^!)4"P_*PXLIYcK_OMMI;y(r6fJP?P%EFwtC+cF&.V>triAk
N#JH^0M2xO6#^g1+}&ZRVL^3M`g#}dGf<NRmKY7>h"BD#_/_*mJlKk.iJT[y[UXphb:DB]xK?*Yt,M[u."Tln5pBkj,RhIS3m?I1cM^;{DulaXb0]J7YERGu7Hf92!rs9;K+5J,Nxt*Cbp-qKFj,HTh.bLI9wR<?asv2:)JG+l73Yxw2*)D
zg)w]${g$3BGhCZaZ2By@#6h~G,WTFg^^.GZ1%p/)Cvi#_$Sz%h/lhs(II^%w<g(#uG[B(h)]#QDM5q.mjKb]$"xa.=OC0"<}O$#(Sq-pZ=(*K2G2?x9Bj<GNQ7Co3@
F<vP,ZUq[%~LBdyu+4d/>UgHPwg^`d=Y0?$,M3UYhXs!wCGkXC!Sww^ufOUo@>-A"E&%lj8YS-p7zP$O(l0uO;|V@88)Pw3Q(v^uLJA*l[{5|36eeO{vr^3TN/_7CG72/:#C~6Q=E@/H5j>9sR-w(+U=?/8mrGA10@zT&wDu
#p,3o-pW)U+x*_-G"QMS.xBr:YtnILp$S=gtBaR/F;./N(U]<Td)0sQ*8+x:]&eT?yO=h/eVSs]G?_f0R(W,1$>FPJ]byta&<()HN~7Fg7usE3I&EW]yo_B&VK_vx}@:#G=F;G+1oHqS0?b[:)2XHR:2`kO@2HW#!aDibcqFQ.aoP.U/.FF@t1,PX+DrJ~9LL`ks38EO6"2*ewt98g*rg")d(i*W;|DvB[=zEvY?_i^p3;Y4IG?dVECpAAZ0%WE[wH;M_"*e+vT3xxH7r<S}Ue^N>*)ga2DT+"NKSI8L6r,FhX_o0rEKoT2vWBY[YCT@8/W-KJ(Z?P-b;`*,spL@p0;ex5Ut(gL$P=]q@uvmR!$.WhraR(LOkbEe%AZx3PIGa$V:oluL!GQX<ovyt?RIlOJYispdDUubIaveqTv1g<wpX{siO(Sn&&.7Ga3,e^og,o8Jlk
Vi<>FxNJ67PBl%?7Goa2*j@_vWWPT^}+aTonh={JvhphSHa.;-qV4jUIh3g>zhe4t_P!Slmj%#wPb
V@b&,uQu)6y,RC<nM`T-/R4)@%{+Jg#Eb&F1qNSR@aTkVuZtH*c.l>$#ySqh<JX].2HW&tq7Dej<vKT57Mb5MQ&6bGJXrG*f_DTgWpkK[=^U/h.jk$rbn*SG)JqC~E~(jFyNEB-<_C1GF%S)flcu7#xvR/vPdMgBke!E!M}rwSW5deKm~h38j0&WJD(E*%]sT]$PXsPkYB8sEG9l8C6VtI?=,b!CukG[rt=RZQ=?w28?w5-]+wq@|)"`BhoPTcIFl-Q+?=E"(1j3W+vT]N<%Ml
hJvG`o2%2`+<.v6BCj<,7X@}kQT2#8boW2GtA"n$x:A?WB^KYJK[:&P%r4PAi3m!e/NUkD&MOLYtG&F=-#2H@@%Nn~LVv(gWz"UfI2h=QGN]B
8H^aRGxD%7%W^;0jRB:z2Fg[I#92w[Cjpzb6094PB>B6dScT;eP6C,@u+eh!)RZX3FxiflPoaTWE7-%8u(e_*_g-jsw`^4jsTCZ)WGUaPaFe1)>w5lM#9_6cg5q9aV6$Xx.cVb*]fFuDa0!w:rjMeE?POdT$^a4|vxs54QxcP-+ZL$QS%tD:]dA?Eu0:Dkm`]A<46L3.SH-);Xf=BN+=Q~__T+LY0@Yla5@(Gh.c@)={YlIF``OYfLD3n>FF&iL{jH0.uL$z)=PV8yc=7^)sTAP#-Pp,]eU;Sd(3,9L]ju1|Fjd[1F6BopV^I-fkJ/S.I$laBZAdVA5zjWUWWl>)ibrs.ese.9KoqHJD
vbn%G-=h1@aWML-Px&"cabXeL]rOU_lWPA&G^>McpI?.Fp+t-rMisK[1xyT#>b+>w&Ub}V%-Pf}TXGnh!I)Y7+$rr=fc0&xwo9UCC<s*f`}NaAlh?aS!#T8ayi^gkF`6ibPah+NSM"5h4#=_"tD].J"A8(a;Qe<"#s7C77<%JcnHwq!EF2ytE5/k0bPZ)<l[";14HU2!7DQsix28sa@xx:mncc,8v4^elMxuqTOdGhN;[s+#-*|:f022KouysH_y8i)UC6)mVQRe]ZFiXcF?{%z>YRj0PAo>Hf40.oWMVs$o,h.8=_[1rf#$wqVc:BV(yW|,tLd.lh11ce*uF(p6{`GicE)k$R7))gW*d;B4Bvqw;,TUF,gL$J`nl`z+8K[#
=x)1"]WG3R<JvGdw:=g1A1OS+<o>u{TJS*s<ORNi73mGCQJk+|XOx7DzlZ"01/e*H}osEc[+-xA`aC%HcwWu*n!w;MWarY>0tD:KemH
D2]*70Fja)7=7(wijQOef(@B_cVi#t[R6]Ih[uQh=IlzZ*#gnrwu?&G":=_Fgg+Mo0q:sZr^%Wi
GqY(N}SMfsb|ZzS809m|,(;Y7gL
Eo0|`wQD&4S1?4t<:~;q1WhUeCwZHq)Z&/`7_m72sIxSgBJ7uJo^E[q&oVG}pfy0OeW.G[WBv(&9Y)V>%4:}7&X3=u*BEad.vAf?b10
1hI/<FPXrTScHp3pQW*%O-gZUfe)RG,v#9GEWKOFwk`X#>.
5@P*l*drk,`&w!Up2gYzce<^^FQ;v+nK`)/dCwXL>G(hvk_)Xs
W>"54y!teOMbn
IVA%$*iZrgcbS1Eh
l@$=:}"U3$1C/-!Ahlj;.H[|oXQy6hK^GVPSMm00G((wKi
$?C$2dy4wUFkUXk!o0mv$GlWX=>kd4MC76C.P!UxdTd6hT,PJD0x@EU9fC73+/XE>-So{4&(%pX=U@tQ{^TnwkwuZTP?hgp`z`
ZMcAa&k!74t6kIB*1J*`8jGD?LauR_"<g"<SID=#x5#W8-+&-37c8FT8-C_UQWD1A$,kbrT<_1F_"+fJT?(MjL&^i~OV$*Fw2Tf:S<^B?5x>8n4e#
9Uh|*bu`T`bvC>js1C7<%j:oo!!7]lBDoe(s^1/tWHd9AF,Dijn3H_#Qr_i)k[Ahb1N"Vy0pJpim/irNb#=*PQ;uOUi<iX%}pHA!)-QqJ&_6[SoDT}+Oek(?1)syt#i?;m?C6ODKjms(A.5Fb,Rk5MXj]/j&yw)lgT3fd}OtVQR!.gm>1|0DZ3(x_{geGYc
!w:D^"iSV5n(&>LO>GfR/,%6*.3_M`)aM?$|"AXx%MqFh<tKlR.#5_f}<h:_^Kb^>o<[8}]VEQ)4"~wL-Y@96:!f)e<"1|x=USL_KU1-W3yFEME`Q+&qs-psg;xKV6Bt0~n!ikE^qQiml.(w]$*er
.S/,$.mv+39O:[$aOWeYPKSF%a]6OL2e:`"I^Wt~Te0>pDcW(?;A=9iAiI+D8<me#{
}%DpCPtqgM=g%.?VWFHEj4k1qkmInKr+>t}e:/E(]*T*<li1QOkVF6gaJ&!s&(Dis+mm+s=T~PQ^(CEnraMDKWL`w.wd19T`PuSOp?L+9U7l<(V$_<zTpD.31p@xKCOE6Y_l{P>[Oc18*uo1zgGu(Ib3e[ZF|.!H/06CyPf2!Y.B/DdmICuET9f"w^ONC^FU2;`OvEF(hMxQ~NU(aC|l/pH4wE&n>p-3SuFSPrvFeb>:H%^v}fL0UyOC@e-

.Y5[kTq7xM_Fe7t-[
$vTy_a2*YwD0OaOu&oYX5!S0=}t:cdV+e6)]r+Wdae-yy]u=Iz39qusVmmDI_M#P.yhOh0,,q[Ou,r`BNR^tARW6psC$/MZY9I:R]5>x)-R.t(%imTH~LA!i7[ySh:bs4|P_4L>]=Fmf#tQ}+4G$FR
h$j;L"N5ma]_A*3N4V:V23b=1G^uu[6L}Oe<0kcD21jLX)wPU&SUxY;RSbqYKPMK#uUwmZg9W?K=zC}pXSLd{xl93)k:&my1mxf7<Y<oy
.C/(#.dQP%aoob2N5]O,;z"YJ0{#BY!ee[O[f=mIw7#"BYqA{5z"jQkx=/k=`LjA2l>w[y&xejxi/%}!v7yCmneo$B6<1qeZ]Kt0_`a9js5V|KQ`!Ah/=!Jun=0ER&~GA.+k
DrV(B*yqBD?QXO2!=E"
iJ4wsP,F.iaSF68GPoO4CmZ:3fAWC19Gwp;5fB7*JH@[1D5bHs9B!^UMQ{#2j@@.<KcOoKZx;$x0?O?LnoPHR#R,16uyI(@I)W>K(+4|P4OHO"E=v&-TtQgoi#OoRYu5w9vz.DKa0Y]F*=`v8wRk&_Qc!79}6+bETkPHJPSH7hQ.e49jEh6ALw)n`cubO`#z2D;%5^*gj-H`/Eu^.y1`6zljdRXh5Fnpm!h
6f.*^TGQ_OuVVk)JC*F;e"tjM|&@(i-oF?9ihcKf_`qeQDx%6;t%
:Z;1?km
^`X+[2%5@rf#;Sqbr>xF@O{-03noPp5:{^|&dK7g:VmFQstD#/|TAP^T==5ivtG`~KAJ:29HR/~L27swQn7nzI3j$T*l~={soO!i&@uVeq#ZJ:Kx]*Rt:.2E7s9hO@
QumjXljs:fd)BxN0]T.AoaMAp,A4cl<k!khANU<o2W?dlc
p[O)4o?sYi|9$LlmetmMf,qnj`GM$(]*45YF^AlErnyO}p],l*a${.rwAQD^CLbL0e{J2_n4D@R16lB_97_P@VueknC"4hR_iI;kr?G$l5#o0lzO*LjVQ0joV+SisMvW~%Lub1N0BA>G%UH7"6BJg`dXE@s=G%!dWw)ofDoXys
[o]d5H6Za;H0m^dOisJJu1+WbN4pBiAE8)U>1=qCBR<VND?ip
4/p~p$pC`:+JU[l$j&fx^[XR=n%/u[m,
0@4hBNBMdY
n-u`"v>{g)1kH{JgtKBC$#Cu8<mvD>X;[0vs;9RiC
&)ELo0;9k]P^/YKHO+q9+6({3v[JNBUIK0<GSlZY,0<8gzX_Crizp>/a><1rC#Yyn:SYj}"KA8d,QKQG_CR>`bC,^#*j
mt#+?
HASB[n<1+fOK_`HuP,dZP=bHyW7.;dJ]7`dDK5siS?W/0?t#sF_2Lpd-a1OTdEtq2f$pyA~HXKkL4v:*,ye`dkFxexhv?>|W/f0bX+p<#:2j+Z0!,(oXz&B?1M4.#AGFRK2Bm"JOwC:u<pfWanENmx[ikD6-"tCWv[728w2NkJm%8"17$.rQI`vY^=cD
E
$<55OvDYN[m#:1+d3L/j?p
v)47hBj1ggehAPGF"D=iKDSU9P{CEtz+8omp_gi#&9ajFf=Pa?k4?y?wex#)=Q!M~qYy.<_>Fu=(gvHtgm>`3U1?|NV[k;|;Zdo>RPuKjiH=`Y%e!4dW!^K,(Gj%(Kt
b*2/*TWxi`a"5](rH/lmpH-Xd-uu"D7LmQ4[k/VGmm%>~y`0x,g`/JEA>_>V_uG>jZ/1Or3RTmH_bvL8waH=A,2X`jtA}txtephCuN,IW6v<[0.0J#8!7Yn!X.cj_+Mh4n<.?Tn*qq3%#1hO+=wJF.9MuPjVHOgc9g002b%r?J[D1W@9f8S3=d?_$[M&Z6B.*1z+twtOu@/AB@Fu*e;)E_VA[SKCmdBZP1xg4*Rb/w}V%ss=kM]OI?~v}("r}PCbkr11?q8SB"0gpaDNh!//K2@<MIPb&bg_>>Ru;q;Meq,9W>R`%y"1Eukm)Ld[7DP?NKMQ%WXq<2&b|IyDvpY(oR/2q4+bMXt%M#([E!9yr?umI@,cBJ2Y+0B#>orOwn.YuseI^$=o9@l.1"j$4.j_LFl2!)Fow?C&_M[Zcx$t#q(9)
>nhSm0
u@SVd3?d/7^5A_?Aun/g`|]wdI={YvX;&{]+HbZtQ2@y:DrKVF?Dd7=(DgCRTa/f5.-:!l(33L8c>o9Q<<o/SF;-b3K@CHe0D,A8T3j`1{jIdZlqU2p|"-p]Z}E^45YRH#q6,Dn>nXHqnmayn:2Wk(x}q2M_rcP2J4dV[G%m%
A3LA(2xs+p&_0B<xvlVk_8#Fau+??dG:;@20`t!iul0p(XLJ`*wTZ+P~nx[6*b_6pK.gu^u>,j6#GYdg]HydJcK7PSYT"pg
^CqW^G7)w;Q`#w^(/;SlrRFbIrF(O{iKVu]1?HJ.HRcGTQfL+V?Kd1A*DK6dI+n55KpA)[[KfHM/_#Iz]^u.&o;;DDa6Zk"IZ#lFl=AA^ld?TV=t?2`/`2joWs2H59Unq&sq4PE$"1I}[_nf6/wt%@Jf8hc6UE,,m!1Xka-3fx`q-#fkx:Apxd/x&p&71")>h1J#eHSSRh_PF
c|`]5`YF[bD>K8n<uxbHKtfOyhaH%6=IB@1icqcbK:/fqC!<e5n<;cvJ,^[Zu{w6+2V|Q^L;^0<Ay6kLH$sAHGd=God"@(EnP6%2Bw+%nl+R@-ajGr6Y&?BdMXt#BYkEZ!G!dA42F!rzvxAlL[kaD{4!e7it?1"8pB33-5t(EtxKLY"it!h?4HA|X=>1maN[L-Lh7<6-qKI}&,Xw^$vvi]D_uv`UF-A?K
HQn8@1!}2>My>rcY,QIzxQuVc<o#dbZ@9R5fEoCta~q#1J_xPg>sx2a7JHG0&_nYPr4gh{YN&ldeZ98u5RgI6kMU6kp0gNK*4I0AF1NY=RqycSiWRv]RXgR@&]WH
AZG>Y@,&#(:Rf0&o3a
rEF85gVaA]y)V*n
0QC|5bX7avymh6rFN7P^)E!^H<%V5Z<B,pwmcs2?s2;aRn(ubG=-9vN
WqsJ%
4d#!VM_
^j.he*A;MD?8+Tt53PYRBh/Tx!

doSZ8}nR/6CpRU<}^7E6&p(G=+c$1`!vr.<3;pVSTd*E_{(X8yFUM]
,-YkC4ryXyhuX1Z8gm1${,G`Jm5J>FuIKMli<eza363M2h2,uAd:LYQ6@coUF?J8C98G;kU1lg1)!O>@*+zf,Ux-JC>-ZilH/khFWYymC`mW$="-*[CyYprrMW{TuY&d<B,a=%G!pkqi*2-hpYB]sFT;6m2Q*0q?-v:ADUqxLW~yAnMv"TUiLVglgv"@9x
lq+"n=TGI?O{bASfV|%X4FP|u8xYH*9]tcES(;gYe~@h)a5.qU>O+c/ZIO3%Z`0)!YLJ;tvA@ODquj%Gs`t7X5ud4Gu^j{es5AfZnwp_y|B5OQ6A?X1q)x2{UELu!g]r(c&_Btl7H&4)$r@8,rQW*44W;}=4U9ig6T&VEP&Mx>;i#1=^!39Jk,My;f(r_[$twyAw3"`99*!pI(?-F1k}Y^H!4=Rg>vALC.lzeu"okZy;bfhDFDCYrXS]J234=Th&T0<SLp11y%Dek*yCSb>!:p0F@`OKA6%Dgub[#LZZ7w^EgGn:Izmid#;TDcMU1Pr2Bt
1_Q"oD}y?_8aT4B[7Vil[0B-_]}SwJGv_(FYJ?,Oj:ca)rUy.-U:|B+sdgm>pDE[$m7=D,2@~=~r2I7DpgaD0UBuV;ea%U8tI
6.pT&Ea^8H*-xunkNR/m=oa@]`l&]!<8dCUx!6UA_5jH{_}!vr}LCr@6Auj549nI2i=4AmH`#@aS9*0L;#[K.v!B0V|FlDB*QX,nC,^EUx=TU#{GRrM^PrA+QkTmP/DDp:hqC0x#1D7[@H:i@,W[s6Mq[X/UyDBf?AaGt&p65,gbB3-q&VqRml8E</X<qxpu0L?sQcP%cN=p<6q!@8B,Vc,&ILvBLh%v{_nLPh%T]PKRdL{.LBH8w+K3<hc9mSg;TM//p,0W+G|S4@D=.NLw-1?ny/ax^ii7t"cP![<^_>QJjQ~4a&u/1=V^).>jh0@&@dCQT-[k!:scU+K<N,,a`C1uKw8o96?hEPEr:+CxZ&uihgHK`I3H!i.Z`?:VWk^
_DfXZm&#ql4W{kQWo/5t$GS.-?h=6`%-BAP
QbJ#<,nX%h?8L
KIlZ?gE"Gb06$gwcqT]&Tn
b3^0`pb^tuK8=_nHJMHp4sEKLDQ
]:F^7%8Q.0^Q8o1:vQx4F*beVh7V+15XS/+1SpC?/Mw8W%X[w!fdWN<q8KAz_,-*;Mkd)+KZYF4af];u)vL;]_lghkj)_H0:JsCcH*hEJpgJ6RE1yJhg+V%:bY`62kUE;KHCI-hMV6Z`ODA$l&c,X@I)M*,%l!qegb
^AVu_9D
+XvBI,%qo2:VdQ?z!Y6Az(i;eL5
*sol>FqQVhnkLTGVYU[_jl-kw6LB866u0SNtFb+@{3{U?:U<NJ
qOM],%tIaavE,5&To&q^J[fap?K0ANWb$<+3yq$Sfw
;xblGy1l!*ZnWM~J6)KdeTyge7~H5?K>u!1sh2~urvY%F6K=$5T^Wa_Na&
mRya
D7N(
>DRUB;kBt;XxZOJ4X<;^!,A~vb#H&/4x6t)>,vJyL)ATS.Nwe6v^R-1@mC;p_TVF]o+eQZGsK@^.9ReMkm:/["1@("dl,G)[=t_:dTTdikJd)aSkk`W~eU0f)mt;
0!?.|8<s-A|xH5P=^+IXXNl?kt1Y
H_pHJAEF*?V{y&O%S_%f$HHzS}Y&>yI]@^q%YJP5Xt*&<6w8%+EY,@-Y7Noii@o_mT)orUqDrlD?=.m{h>^WRGy"Gb?I?IaTCk0`(Vfa?A!5^q_1
P(*Cr#JLCFv"<sOU^Ny#5>ZFf6hXFDs3VQT/>O*Zk%ltsNVj*0=2-<lP=([S29L%uGy-7Y[hY<Y#KSUxrS&t^o$Nb3{I71i;n"U1`Rur`YZHrkLXRDbX@JmpUaP@DNYEg9XY~2QDV&yk1Xn1A!+6%c1g8t^DiK4L2O"O?QEvS6K/VH0>YxrMLQMH.LZA.bWuh[@,6R,w[XE#!IBndHR
q`i,bwy9n.OR;yTl;ZHG|#9Yaa40H]#4$,pC_+68@v?(>d,qR]i8lREKZX7jy<WM>hFAL!8^%kcEXP3Up
7Nz!g/OJOlPjr)?9:"Z,Xer/xE"Wp)gAnv8%jb{[y
i40
>)qChfVySY0a2>hm)O"%=cg"n5yOa`2MS&G5K4B(Fc+xft#a<vHkC@S0:jlt|0qS^12HzGC;IVq[&Zzo}gsbl-7LL4L=>[b:q(A+!ybeM2&I3Z)@`bgk:1Vedu^C1RMRFH4t^`WEB3;CH8(Ng:GO1T9FAVI$*,[w>x|RMT:c4W{5gF<Oo9kfD=s1yRcdjd|o{;175f"kobov|eZ3ZK[cn6PCAOQfJ:j!AHv<(+P6Q@
DoFHRnH03XL/f)Lb!0/lj^h2^8MMqlE]HS,K=exP1X$}o:go2
]E%@SNhTm0.XiYgT4lQR3r35*VdF(,]=kAFRy1B$+$P:kD@Rz&@.sVjt^_KxB>9tds5-0jEtx|%]</^[VowjA7RjbdeG(<))B{E-a)PpUYfwjEx?-((Vc8BiNloVl6-PC)HTo(#3+
sJP)F<uvnyFJldJJr_D4m[t50WR#53&41r$Ds+@TA;FzmD<O=)`9SJ$O?L!}ey1QxAm&-f-{#-f@^vlRL"v7#UOF/he$466]m"AnT(VUdnT.Xg(Gl:OrNx
MXKSMb+f<4(70XXem3Bo?."*wb^;BGn@hc
YM;=VIuby00x.e./=YW*&{P<a;w]?t5S+R8C9Z(G10>q;:]CM1d(:RT%j~uMDT4U3c!&]W^s={bB)/RWGC$9@A=W[hfLfv@Fs{NZBJGO@s.IksQL]?-b1(!S@m:6.Cb>)+dnYW[-L3Bz.3ol0L!U-/5_O)%9o-#_3HGpf+L*Q3N7^[_pRMc$box]*CO[D`20?xegn";dN/LDi
u-(L3{sM.1Y!GLK]YsU>7tNis3ZRi&D7s@@.QhQ?q5e<oe(:wM1
>[8^XAE{.-3P]~;"X19A-xkm,7h,^gwGDBpd_:u9p
mjSiGF!tip[i%=#%+cOX-E%7W-p9kcP#1v>tJ@=dJJxsQ%E!`Pd)_9@x4cqI0y"X`>O|j6exMBI?Z5IgqG]%?W.$*E?;wzW)e>+SBxl:W,9hc5,@9.oVSfXA&X*Q]"$jUCJD0sKsYl6X6g0B?UY5(9lN]n&(jbm8t<L(Lu#e4,K,#E8k+ggbM]@A`RkcP<:P_1Tfyr@gpSI3hm!TecsC]#vlP+kf49IVs-JVpU2"oCd0,8xY)hiN@X@?MO5qM$Q5,E%UmBC`B5)sx3Md0f5u8$#$li"XkL1]-_3H+ZIP&
U5CLocXrfKP%Mr7K(R)cEzT(E~`
Ed4O+"-M>
2Jaq]>b2P""Ed-t])#`b;FlDe/_bFfHu541/-k7AZ46:N]ClNgci_=)wa%&(A=7BQiECJ]4bNz9_lGLs!KIX
i2.Z#R/=HMlXMdc.dpU5u5==fdx"jk0;7woBHC
BdT}[n=c(`GrcEe8E3W!&YebT[+.PtxeJ;Ax5Y2$Y?f"TySDaD&6Z-@Bh->mHU.k0K[;/ZT
K0ubJqc^M;E>R~LsK20myTo6fXlO#cw$7AP.dcNWt!akISdqNAS~swsiXLZ8)O*gCJwQ@wvf9y[6vdchN6Cq#9@"%}>}R)lcWxDPj6m^.?CPGS_3J(
+,,K]Xm5%u1,&VT-QM-LCPeaAGdl`6Z0sXs6h5Ve?U/#D+FIlP;p_8A@r>`a.hfW_rxW8b:+haV["Bx)Kx!smad7-a)JV8F%hW[x4m#yj<fxSNFF%NK[_f=3c+b.wTB!s/~..G_`npQu5Re1fyt=!,~Q
IU6+x1x_QZ2DG=_AN
m0s{*#vQB?<S=JrMZ)sr7(hi&pV*vXG81jN`W$t4+3
)62P<[Q?"_L=9&)`kSS0[ql*-B7bBH/H>H3"t=x7vjwy
:I+Y4u-y^tUL/?WiFgA;hV9b7d[D6aJ6^c&gVkby
SOvw-`OHRQw*1asM26Wb5S|a8
n#0QhR.vE?MPA]
e8y-
yv{,>O5vR7>Q;
H`)<Gtfuzv+-D8jtB30GWp;+q>3l8(p:+Hm6/aCq@#S(dL_x1uw%4.%kyGk(IJI@Y?;LQf
1&JPFmb06;6Esmdo0)JU?5D&SDY:>NfgvG<Ak&sKQLKC$=0To(!uj"C}a^Y3nq`?B+IiRa8Ae>Q(:=Y"wJd;svBHt@_no*,*A"^2+;7@AJ]AbOr%m|:h.bHt
.r:YMLU&-/8S3W!Q0N2:9pguFN0`]X#l6:I%JwdMPb%h8a7h8l2,biC`LC]Wvff_)@x7,m)/8MY"lu"/E5,)VmrZ)/5B$3F,"cX$"5B]s9NV2LQ`zA860bcj1GwpJyxVqP~Yi&~lSoTKVZSM*7S5N$x$.:u2,:i:rj:fWPOn4
I_C29V:6"&6-p<j<!by61BvH5m1jsfym{LH*-]Tk.56Q)==qF%tRRuFm9qtB
ZNe._+RvK_a%UW"C)jbkE]%Ud;qWi|,]AM"P.n7<b8Msk`U"q7vaOy/#Gq*7oGXwSni)o/4)]&3+wnDqS5Z2+-lve;pKC;SZn;^hXw5CQI.=$.E!!C&GFW/dgZn}XQ8G(9bt;X1IU%PnKAC{Wp!$B@V^&F&d41Ep?^@}EN1CmItct|F`"yMO
"Z
YU@l!kK&:rAA,tgTvoR80Hh:;.1=&6KdH8g[Dnh*?0)`?<2sQM]}p
o_5c#.q
_,oudENB.IrbRC#xYL,.(Da:.m`S/9"KH5Ol*m[>6@om5-7w%3olW]
[_`Ms
kgqN`U<k+f]t<P+^gd)A1P4Msd;PMF:">7_6%UDBD@aOx+sd[f[g*Gv]ge]N@GO*5ps[<7Hi"nm7TBuV2ZW24`]oW_@;gptVRJk2
5OQnoAP,;LJ[!UIse,vQ_F7N5^vkNVegMcxkGHFh(e)6dY#LHT7LRL96f
S7BIj:X{J#R]2khQh9GILeg,%5FjwQq-o{?g%G?w,d2"^g%Aw-.>aXGm$y<Y:T1#hwidq78]#CW:tWYmKrpTP!-5c*96$R
$f>Nd=9z(3i-cse1W-OrkBTXPjG+hLUQT6~t{]+skx@Hiu>98#MUY^nz#$RV5,!CQ%my@lk2Z;&9WWD44"1-wcim!a!/dRy
c[Ute3&3Lca;zrVv+m0)zj/:*Xw=#oQ<8J@9LD
`>g+x_:4$5xA@f*&@fK;.IW;k-^hC`F?HtW9x4^:*)ES:{AXnMD9iH&%<{7_jPsMJA3lotEt^&-iXe#@l[$bQ^p(fmcQ9&4nN6oH%xR:fm8&g+205u/a
{y(Q,2=5ZSV-Tym_|Ym/!$a_2p3E0K}o$,XO^+>y[ij^q9^RX^`9x+f6p$Zeiido;;?WUE~!8gzfU1J30@~6Fe3F&G^dK5n+ums/!R:yyR20)2SJx)iC|yJ"{=y4WS).RC}({J>e2I9$Ingxb`+v{;?x[=Dp_F*1uZ,yO2D)"q-Lm,yitVbK8EwVGizcfN@qsxr$_@BN9@_$BDyKU?XOY9f_j4l
#mK:yWxS,m?*gC(D>ZOF9LdSSX
J#GcZXgtD;%!-yu?oBVg#(`
Kr,yCn7IdqGrmsPxZwOWMyoZOPm>1!V0v?.<fPT)Tp>o8&*k"T!Wcxe%8(e|r+22T>;mS~)#DML+#LUJV"8_1q%}fd3Mu04~0T(W3=3V=0.n`!i|?aCqA_l~q4tJ2iFC",a;5YF?1]Rfb+n5H~$--Sdb^a)_4eZvgmZ!2-35&pP]Qx9(uT9`D7Gs@r[O$v#@%}.X6rO0C]e8IArs/|fqW3^?wguJ^ld|0den-R=0ji/vZRgG
/>Nm.<6dXx`*eV*O,Tj_EXixyWGw%rLaus~aJkSGYiG=8d~_(f@<EIE1SGguHf1eaf;QliFr
X)#l?6@?4k(
GBAL8=,aoaqm*@3nOuv.0}F*=/HW4g(ymEgF2l1e%Db;I)>VgR&RT%>uJ]3.$uPHumhvg=fx;vTA]x1;H{YgMlxm+p-MOmQiu6`9^W#+,]*,_9/9AdWA@l3x$oeh(y5>l]KE,)3+
582I{$#f*4dGg$rY-+bsFHbNmF^2bT4pQo<>WyON9*(sx+u%ntc-ipL%RGi$.c!Vi5](A2:5sR69``"K}HF%4Qyl$V0;BaC4@P&)Y:v+DFR2"Q=QU4<%6(esu6p6tEBf)cLwvw+v1]iaa(;<{;Y!D5A@a&Q.UI)-8J}2(p?20rw1t1`%l9=Vd_k6BgY&O0GC.Q$c@ZHf:>ID8oJYCuVW0CPB=_*)ja5G=#{.b=>:a,7=a.oL@.!QG?E9!1Pa.hORKGhqb+Bk@^oZ+rk@Ovk1g5~4V*l"{Y]Ez+IL)
mKO](Lse@TrZL*M-;:2hxc%CM
a*P5JVI8s7lVifQ%MN9!1o(CfCicmXQ]dY)<Dl}^^@mOS1`S.5(;pVw/UouxpR|!I!]J82n!cEIG}J;RB86S,!hA?ckcN
<[Y?eO%Ck_WYrpVt

0s>Ke+|R*BMPn#D**#hy;_kievqt|D0l)M!/dpD=nxkp-!K,1,555!IUPGNg]QqR]C@Nut3F2X]g=/Gv$.,_LVKBiT4DZY:Tp2fnZ:L[`Ez?8wxPo*p&4[mMR%Fx#7@2|M<[{:%(A->))&G!D?3hu
](UJfw6$BVi?6YP/icg%:a"hJ4e9b
7hg-NWz3C-K<ejsSMNqs-vB-!Y4[x[WEChY0VN6D]tk0]Tp_Z?DK}:|7YdL@{@w%DMEBRDY!wOMhZ27+-S[1cHEU~"as?5>#A8[c[9D#De<2Aq@5yYqy1azL04Yxni4i%Z@[)[.l{JVMSP%ojp_Jc>D"W<#%S*KRqIB],
-%*O&3P7<"U(YuDL9pO)I9)"5c@B+X#F&e<GYDP]c+b%CLa4FE;Fty3ZjG9Q/[oR%u(=yQ>n&hjqD5!&l:#?,g_I+Q+Gtc*8]kq@INmf(.@q_SCAkvs$O/:P6.>DMo4sm=]i|T{7D==YSdhezN$=XhUg1G{/WE:PlVoy&#w(BWM^Yh*ELC)Sv>6?EvBja2^6noG#XO)^VW0NwOgA?le!nW2(-r*+Ei]$-1M6Qc9VhP/nvFFYx#?M
mVs-x}L1y$PhbKEbkT%*E}>Mn@/z(r</5nC#sUNj1NH,]H;"hIws?.!{0xi-j~3hck4!orTX_>Ek>Um!:NPHeFT~b,Fj<OMl_jO`=5x!t).P="r-Q%9)EHERTh(]>gHy-tQ}:
*
,@9vO=iEoL&7uilR7g5hkG`g;q<hRwec&&mGaJ$PWxYK.zT|_<JE&`PLtG;m>bDCPF&TQAh_lvy_)b1?W)Mj4os~
WiK+*RH
|<9cIx~
!y]oa&92vc`gP0Sw6ua<<@uA{l_
jD?kH6V@2@QMw`lN/Ms@b6=.<5StJiT?D+tAG3%-cq.
jsw3Ko+^uJ(?c+f%{g9beH}Cnkn_Js./yd#3C(|y:-7B]+N@32LGK[zk2?Yh]RK4y
lOq^ACwZnWKrjUSl+hP
}2BEmPzIXB72_/M`>k0_VA5;R$F/#W&n(9eij3m8.OHb9rmtVsN^+5IZ^)GOwCB5!rI%A^rHzYvm{nq]`)BU]PQ4!#FB,h#7AP-m`2"uu7AHqoiWA]sS3t9Z9;!U]o68OJF.?EL(*9l`[GS(.y|ftJ9?*00fd]ReJ/|B|/,@#9wI%?vuEce9L
J0l)?/=!?%-7B.(vM?6L$^GYibHScpv:K;)vNGI>gq2TDgHtE$bokF$2UV;(U&%@F0JUMNKDm?@a%1Ckb?0^TvVuloQNJYRbw+3"
I:p,uUO88UbwMP:D*ck;q|NtT.!YA!?>fELi&Y2t-_r9%:k2#+*L3@[|e6xC#_:{C[w&6.$+J|hUr/]_fl_d*YViu<k1>E*m@~CbPzT-iwI9))RfWor]3,eH6j=BbDJDT^::7!/oS|q43
sb9S8s%lS:7`4EiS^^jBBv,uX6EnG}.8+nQxnA`?al@#ae>E:0)^3BC=ZZ[{v!"hZ$_!:5g7u[R3pZ@VhjwwisQ*LO(4xnHDolO9ph17M"_a5s9X&0k*8Fg#*j+ZN@d`S^%
Sjpb0-AVjSqg7<5o<cz)!qtj5LmV.zJvFeO@&nN:md&z?(d|&o#NBB&H8!6@Rof%XLvcxkgT8>@8QKga,Edb"`DI0PR[RNH8(>%rZ_n<8tB7I*#KPu3IfC)grD1sU`Bl=lht6S8h:G^,7eG<(h51-p-(0w"oA7FTgD-|_Y&T-(*?vwEwUvwF8xw[@+@VFqLL5Q8Dd/a>NA+YC=.7-JMo.O0GJc(=_"6%kk8J?_5ynF
T>c&n]Q&U6C4:Ogk"FV(XAg,s%b.0W%l)T/?@O=&XS@>,R:v4"!eQ/Uo&;"MJxQ
nQV7MP$`b7EQ*^".7jN/GI0aC
?,STP8pn@!">h!)E
;O%}#VF?ZiC7tc7PQ"Z"`7"/v%+.cm3WH8Uu<jpMkeg^Oq00qNL87ysmuq+I62A["}pcJpblJbt7c"RlB*5w9W
<XC!U:DIy*s^e5>$qgPHI.3aoJq3"nH)Af}nJi!bmi:7YD~4yXvWGMXY=k^Z6wp8kPsPs&JFjn-U`.B.`LY=?.9iCriS#5mk`aL4|?+JpcAQM>[BSC4O5UBDzEcp2-UPax`owc1oZu/:.3!aFaQ``aw
91[4IV;@C8zXai,_e1o2(gr].lY-/7pix*Os
4X+AwZMZ]huho!2x&8=H;qIRP?EGQ",QaCFcPZ9!l;k6$R*3y78uYNe`Q*"?ks&{OEE+<S,OTHeN^CEdeS&9Q<8[hq,R?IO5xJZ4S
[-`}R[Q|M@$=KvLDD8PuQa@DN>r9C?R&<ng7Zh5b-,n87BK367N5(<:_l1RC-"G^Yb>vl+vVs{Wz*&5kb_^[X..mA:9
M7sv"Ux#7t
HaSP%p._ph)wV6>60`H0]BuhKNt*JFG&_HNh7Ji`W-t%+`j*)t0G+CquYtB1lAG.kH!.qhJh=N4An$MH"j"TSy!BxgAf|t[^4SWv9`S?F02,9bf#t(2S:"~CRRf29*Q[FtW.ZPzc!FKSD)_WI"z7r;s[oK5?fP(N
(cZFm7g/HT*b`cXS$=_%k1dI:OW{LBWpWz"R(ydg3Qv3*_(astjvH-M19J&IMg;Wc[4+vw(4K|!Dsnk^lj5W9.p#"_Q<#JwB7T0NSjLC.Ywy`i7Y0{P>xfYBBmSlQ
ps7~92q_)u.0Wn>&m<s[(,ni"C((/zi6MeJVJs+GJ&.CjypoQ,dZ.DN>i3U/a=OwO:CYbf=GYslGF)%/_*rzooQ+qGy/f58!E-&IO3e+X*;+V-0Bx:[t5W2$g=<K1|MoH_$)G&N;@r$SZGoGm4+IYW[AGS]~DG_TO)
5I~(p?1@c*Hx7-_NXrisAP+x4Ld5H,MYk)Ec2)9`%_.ycJY,.&-P,Ts-DtTr)s=N)6STXMv.(XvHII~!y%1vkei.ju7CCu&9C>+Q!i$KLKj$.u%C?6MI`]K<y&FhfGK$]l$IKL5RRZ7Y8]FeOkyCkq9D>xKhS`Clnb|d;^%9
Shgm
b6;%-2IqY(jIk?tZr"#uDI~ivB
(7Px!t0M#{owKt
@l"SL.P4r
LCI#+PIE:4{w?Vw!irH91W$?3
d#Q7^T,%!N_w-]OJj<:u,x/KKK:*IL!e<Y.X`la#GZdF+=W.c95d!T|N38zCk%^fs]s&f^/Z#F8"ET>y
"PV%0QTnQBH[Vc!gs-VX.9X3*H
n5!l]18vrc`*#^H6,<Par_6A^Kv(mU,iOA%?@N+&@j&C|M5*FXE4Q-7]up/)QZ/Hz"XP7k""T0#(mu&Z~<
wpD+[AGG/<5V^N6).yh<<wM$_+m_Oum_szn0h>)rsKlq
ZJ{Hj1_m=qcLbs/.aApkKr{2n#[H}j*-5&gmvu^<4n@bAcVsS1""X"w0Vqf2tPq?-[p@F8.9LvjjPKi;
kp!ZY*71E(c.xKl(?~7}/ny|+{v6YA3Nb*dR/S)ksE=/Qhtw+e1AHWrStpwtrPrmx+nJ3.Ik@|wv_I%q.{(E5ETK4PhWKyd|Hf17o{_08C<j$BFYbqfZ:qM7oV#/Z.<%xj)wEmhi#
Iy5u2osLE!;F1[lI^_eqHI%3h(Vs0hpg$2+0WZ1DFjh+8K+y1ip{RV;ZlyPaxv]k(GIORX/n.M4xs=`2)l106#VCq#3!
;>UTQFNKSe!C@;?LT/K(5e&Dj?fS0dB!<Z{g|%S7$8s4Yv;kz83@%[(E45.P}B^QcI:BzU9S|6Mq"aw`hSVmIQuIqi9f`Oz-B:yWU0B9`M7?*
mK7[6sJ;Wb_MV
oxdEA>)Fm>FvtG>BGyf-cW+b#[&b!H]bGFI5IBNnx%@DK9YCfGzk*pP
Vu96E5(b$z%k.8te,gn*&l2TY!uvg?$txaYC^nE"]1O&tD[[-xc+q=N:f*Ua@hdavO1e$RTGqb:kxgciuvLa2W{vBce^ENo2~g|/D^?U|+-!e3T3pU`^LkZdqol6qJ7aZgAe8+K#w?t&[4?Ut!/7:"o
R_yw!d]-T<0qv2t!J92!t#FeNe$V8g]PCJ}35k;wr(2:NDeU[m~UrCp
%%4s;=Z
8iA8&V7T&K(tO1j4w!7E$.8[}g+K^2Bp^]/;zkcca5?QhMRK-Aqr.If/vmXshIXOTg@]IY:xH>gP[XL"1L]m@v.3cYFo^^^V<x:cbHz_Ho2O!RHR{
R$$+rBla]Ro@
h=2*
Vm|v)=pl?GK`YH{n(l].<HJ9<,IOT*N4O!!4rJ}_~!mdtV"4yXCZju%pBSA4=@W(8>Sk^3<goR5B1wwA3DX&&;xqDG{&}yw;2TDIao(:axRZw(/w;fh+w-i.B@zXX!FR)Yn@B!N1|E#v+@*`G.M@1OZZtOXhE2Vp81sBTG"4&6G>
K>!T-0^9,%bwyq&el9PCiz2Jk/Ugq|
!v9:U)}.~X^O4q8LpZ8FtSs6MF4fy3Ajtc2q-&GTjb5DJGtoh;AlK@(Efqt-qyk
hB&3Z9`Y*4}?r*oo]f?LO(MQB_`2.-*6pE,c?rd#3usyItdEbN4P=Jz=717J@K

8<4u~qfrK&_ftuBc}Hu?Grz2gnn?2@m$(P%N^y]*[TC-9FHWZfVXp(Jh^?MyKgb7OQ-715ffyPq5|>ULeV0t]-5`pjavl3$Vm!K(_Z#BKURR!hOAS<(Wlg*CVAk3u1/+$D,ncaaB
OB1k*=(5gu$:*D<(=^Km8?,RkL(nbU[sapBX%(v
:|9]iw23Q5QzE&E_;JO1f])K,H_
D]7tHX_L8K679o,]Yq,A4BET3LD5LI!E!5&;a`e*C1#Pq#@AhUTo`LF^k.e/CG%4c?N*R.A</_o3u*%13O)Ct}9yl9r#Y`ed5b79R|fM];Tj?Q^_u@f1i>F#-uiNoU0J9i,:EBC{OD0GC;%|u$DMIr&c;Ck(.T4l]Wte)o1?X17mKlss3aEPvVqTB369:WQ<L&1jmb<G6<k$OqgFpyd(o%*0<,?vlzszy!R>K+z$K7^LT0V.YF&5->mYq[M`xBB]:9U>cN0*l51Iw#q%]6I8M3uYM<qF/
yk"Sv~AYJF_p]x6$^~7#feUm@XqEm/?hx:4-Hq$g#32kXfim:N2
fou`SCwSCyW%X.Kx!BMOtj?F*QL|QgrF#Wk#:+Um`c!+A!m}xs;d#BDAS4
LoUV0Jn%41;d
&yi|Rz_,FQT`1E5AlbHSEhck0NcpAtB?yF&[
1X~Z125r-%XD)1,`KMAtR[n^GS29~_<.xeE-_QxkNL;c-7gRhhZK#u/OSKsXSF~9vU?D}yRO~DM(1=sO*!)(*6hfZdig=bs;<<Q+y7UcpHJoGc-O1G{j@<qMo:<pN<y&xNyMBY)%=J~8^%NIS[ae
B)bRl]J
d?<X-j4jPHqc23_@A~Qsv:n
3/<!R_FQ(J=F9>p(4)x1+Z[K4rM
G(FFkX3C;][Cx.N3QmE6Zvu2L0+.&<YHm_G.a1q&OC>bi+:(d"W~Lqv=x)vn(Mp$Ey";@KABaNCO>#lz[V_bF#^[&&
Z@Y5^W..$!,!&8Qi+>-4yAvPT4S/08TQBC)[3Oh+ulK9p>SdrA+()?^!J,>:2iWhvp1#D[o%sp?UY>bVZ8rW;4M#fL/g7GA)%1*$3:PTGxcU|[,:hA&cca)D!4c<eR2l]BX,t[*Yu%@ESW}f&O{[O7fbQ1S@52fP|FZrp`mp:YUqy#A*/?JF3`
wu+Upa@EsJhy,sGl;(J^$
synktyX^${f$@)${*y(#tA[hTVxtZ%%KIU=s]^04T#X
>JPjuwo[AqZFvcc1:xNIlJRdj.;u[V6N@$8s*;h?+RSx`s8;qXc#`953lb]>MzarR{C@*L((Py;Rx1"g#{GV+-ym
Cp8,^v_9YmLYi,95dsvl
v/rBZ_F6I&5i@rt}n$1wh_#
;k5>idKVe{XMnLh`KZHenbHoh^HK(t>DHAs6Lecy-vl
5YhelY[pq@8v&y-sg4M+[P[b"B7AG2Kf&:;.GzI!fWECC6ILG8LwB,/hZ^,eUd7uf7s!3e^+*aH<M|i=V!D^(c-5LK*;UOLu29E}0zGZg5?[<}c`s0*t6)YKU|)aWq.)0XjzDWCf
R$Vmd#U(^bH%ZO29fF"Y5u^As9}.z:1VRn51ue
Q4TIksZcYxPmYWh=&|2L6;Pa,0?>Wak`9Jy[ANa4B*g:M;-?/qn?
YW/3*SMA2GFSRrM,p_%M^jQG,nQ("KsCn42t1
s[?nhuu$O<y)3F|q<]kKgJj,3]Sv0Xsc4)"@D[pZavTVFf5$C7=_Ri6pSsg:jhPswpXHM@fWzbX)wLw0i%=j00R_,u;c!*+S`62rz2?GMgFDAntGc#Wv9Rq!ow8s/i>Q;L[c7w/^t4&j-t[n!0,nDKeBBMx]bBtobMpTb,~`n!sK<?9=2sKf"srl&hi`,Gl68o$vxl36X=P:776ltvWx)sSv}::
J4]B]cdR-4/nyFlbv>*f-xQ/x>DK!EgAE4KTXb}hzVjp(=3@P%*2G:rPmf@?~^{V#9}U_o;]<S;aO,fQ:5c;gu&$iuxb.YagJVm,v8uXAow<D*OK(7>WZ.}CmMA;V3z(
1ZE~Z~=0mSJ_FGX+XMJ@4_GB-Kt8ZpqYj+f5*c7~CW0tEFx59&=^INWr.tm3>
4[@zIVUQZTuMvJXrZs!WA"9~`Of.?d1X8%L1c$,(]Io^x0kRp-&J#KKlUe?EqcTUFKt
0}8w,TSHt]/ONMcLr
FA>!hzvy`EvWT{@.H0Oa6(YQ6du%Khf0NOJK=WrFiw&:ZY7P-5q-J(%6m$yG&E!%%2<z&)WnWR7Bj^`sRFF=U:cb-r4VNnbB`*e"%FNO-hlE0{`#X]15&W:`&R<dgN4rT![!EX:-Nuf28Y?G2+pg>oMXM^
,C-4xA]pt4%YE0cO=jwXq,ZrFRD_9#X>B]hjVE0&"oyt;%e
DP>AVNX-iH#.L`m!0+AT-USA>7MV5W,eAD5C|%o]-8{q91@*4<uQtm2!HGx#O[9$RN4B}(PGO+IO~lGu_etk~Z|6kO;@oQ^e=gp?AFV(?SCfHE"XEe=i(L(
$==!%@VFS4dP=,tGer|]RH.Zt>[Du^#(=BL9%@0h-RxC_^h@Agsj>Yof
uiHpU[gqwVq=;!QgA!%i4c;%`,16P*#"bkHnAudbZxUFeEDmE-t[qQy$.vFqO{"G)x%Y0RQJ4B5g6P+=P8l06E<DDF,:T-(x?(]3w`Qy+s.-,i#KNK"47Z5<?bSxOl:GG+_+E1hy*,6Q6:NoPs4U/W;BCPkuFV].1gyJ_A$P`~P>?@mE3dW9MZwt!<aVRQ:(5ALV:-kD<vIqP`+69&0VYA:$d`TUA(gp7Fmy!+?+kyk,V<g?6kb##*
1K.Mp&p[0,|3)o*CkW;[ktKJR@6_IeW=G1T+cWbdc(/yI(SH]::S6HMbn$LF~-2rM*;k;m|aRtJ=$%v^w]s;#
F>p=g#GU%:AKsL6V{$eAN%^A%>IVdnL`[Ze:2[K$%@@W*?ER<@m0:?">QYpK/r%GVscjMBHRt^ME][ABySBl{y6f3hyS<x_uzz%l{vFM~Y.V|N$m=D"HbtUw`a%w"KT+Hj`itIn%OZ$]m@cK/8
S6*[q2x%V78j>Y,
]nm;6B(rc!
wC$V#d05hvjOhS,hk(.VL7}=WirE<Z*BGQ^*p0hZu(9wS/DZt]3weE$,/urWIqDlbc[tgFy*L+w:bjOcyY24:%FO6qD)u<|itlgARO8ix&t#zQ-JnDoN;?woWDX.<=%6paA.i&S4`lMEy0Vl+tv.:,%RmaUd}^#d23e6)uq?,u,6NCQ`5Q.J7Jpdo@Qt,`C7;WXl*ID/d[
+P!Cv4,Y@>-%L13/GYH):ek<4nnU"},5%o?ANcb`.[GC+
e=Z#h%7T$>Q5p9I!V1p0f%)wstbo#1ZkJ5kvL++Fj$Q&Ed2%RPhQ$~3Z^):)0t=$1F1S
&Q|8:
lfoh^@&Sm-yH<R#]dj=[.R2"9)hPb1hVuOf>COm4;Q{t0@%N<%~LNu.n|XidqFR;<1oZ2GgcJ)g(kdXcOC2/UC$#$;YIBEEb@?l^{nmO?MR$FeOqF#;Txo5pGn-fP@L<z_M6H?I@$sLHx>uS{B/kaNSn^bgo=M[XqFVBg1b,uKVOh`mfYjMm@9~]+Ef8pK3Seu0+T[hHZ0GM-2
w$QG/&GPP0w8+QU4?)O%k6_5L.b]qGiG(^XGqM79%$!Zt>ylWO$_kRFoyjN<l?Cu,<ww@D[zlr(bL{EWsvuZ,Fj7Jj/ohSY1&]x>I=q[ibmgZD?fTnObvtZzH(oZ2nfN"_pc=p+
k*A$M?I5^E>nLMbvNX8cL9G#kaUWaJ!<5HYWFX2o$[-pV2y+aLcpGlog7M-i
m%q:RB4O~pWJU
*8tM!rO0Q<n.!k@e/cIf-iF?PAy%q%0+t`!$KS@NQDiE7L@p(
"bJ`nYr^]uWtL3-@In_5k2neHI<qvrz4g9z7w*S"ae1u,YA<@nj)R*@5+@I@/_LC.doB>WabZ&og~=WHAbI#.XZ9:KhO_W0!P1`nT!0B7yv5s<4lZhpdfDO_:X$xtg-qSlOPJeS&,a!w$yn4jFrx]PO0."0W{mKL)J.$jP>TDu|g|WgWMW1fP/`$NThw"l9>UnG97-%b`aCX1hx.?gO"+>+R$9}dm"DONh:h,2AXRT7o]7(Pkt09Y1n=DY]t#9:&1Nwqb$`egeAu%yN8*fT8w.f0|"MK]R*Jfp3`*VwLZuGtC;BmH(FA<*<)&
@g*a|0:H7(mWU9ERF[{j1*Z
G.V."FcLSB6d<uOw.OI]t<f(u7LFxvgr74!jNl}XX-3`9jir6W~EH5a>}45;7rJ%MQCLxf<g$B:nJc#1<Sy%X>76.G1+W.m#kc~0>0:XUEp_U80t|#KCIv0-p$18{dFR.7,:S${^QG!q&Bhior#y+;d=.)|,`1}r%@.KF[/nLodI<U](A(+$
vRr58Dh-4wBhN@q.vm$`sM=KM_#Di}fYk8RlKpr|0-,%M?M5?gbn@@5h!i-sY-PfNku[6^^9MxBEa!Y"eeXGqG5B81[~Y=jym&eAvhinX01Wt]q}CHRcZT#G=
K~8#FDwsjj!]jgl^&>GB!<<W1fnbw+eH
spA-cy](()V$|.(*.kDcu,H
4O}Y8C#fY`oE%mRcCiZy;L#]:H?>jMpqHq%+cFSaY=r0LtzB&y_%R2g8[A2a9(("^?fX;v/*9mOfW]922n0FW8C/WXKo@<H,cgUMdXB+pIZ<Auc/da"O[Nbie]v;dyGMciNBXckgZ%+yNz&V}fII:FDs29+K}LZ1YMUjrrQZ^4dMkxf%6jF9{Xn,k]tsN3`5CLOQbbzH)<_yvCJ,-%/N<159n2iX:Xm2!`jGC0f@<`,w>TBP#$d#m=jFe)ft%Ngo)^#9;b[uhlTo])eW4b"2QLQiH.IRCIX9?e>M+5k$]&-Bu[G/;L`1R:-FRPCg}")TI9,vJ_gNb%aI:XFK_g2&O>)*K`[9*Rq7.D]Xb(*Jx+`yMxMosJ?]0/h6;n@2<5,Qyl*itPQ6:<Et=%`Z9T.+-*W0s#"N+>U[_*zf)9?HnUBC
mJs`8N*hSsSC2l,M&BqI;W[~kaNcick?8h=sSQ?Rc2$Y"XYn,V/u#fv%VZt&!:_qy0yY3*y/=",%-5wwLBi^r9R|fk_-iGQUYf*^y+^BNsk-O&y9L{G_j>!E*tGFX~BzD]T!rxL9%H)k#oxxy;v
d!q6&J/F[rq`@{aLffA>uuSa*6U`!tl]H?Ea=N?54A^y,M"PLiUM"f,~O#73S9suC"-;KKMA$&d?re.#60`,i%4Qy}hss(d%bkx4LzY.q
]1mnZ[f)XGx
kfn7Q14[C0QYdWsS*or.j#k`On=U45W%AI(gkn="K},0q)(KQVa=D22TBiT[;k)z(oN&!k$!m~w)Awy{uy3-UnR[w|Sshf4|:;e^`2cIPs8_v03yR,&
sU,{n{UvU[7WFTtU%br_:Tpzt7B3qX:.oXUCc)cXqTVE0$wo/B@6a]O:Y}DT80W:@_@oQMfb:Ccp&EUwDFR=lqXM@G&QS;hu=p>GVY`/kMRn
UVzo50cLP[p@YYYfi1VFC!Iek=fk3]M.bnbt$;e(>HfZT3Q9rs.n7bF`9jni4U]3tAF#{wudP!;ql:1A=d^Rkw"K;RY20wi(z-E%S/|qI#e.TO#_0,!X+0wL4HgMBybMRcdIuIg,7yO>}>lW$<9)aCh647"?D-A6e:Rof3:3qomE6I:uqM^&ikmP.T`6hwM!3IG?6sHRZ55G52@kto3;825;7=R&%"En&1n=6Y!`cc0&%x,W?:ZVha*.}$=xEpPh>_Gi30Nr2de7Hptcfnoqxi7Hzex9IN4du2c2I?XdZ%{8>Sj*/
^6N]Im};Sq{5vp@I;7[9b5(fg))#"C!Fs]_v&)IVVs%bpv^9
/,-$QQrrEsa[PI8oqM-OXzg|;MF%p7-dtGJ)y%67^PRD+LCs(V?TB,E{NGiKY*Trno4(H{N~5g"_)+B)h9f3`6E(H7UeM>2jj/hQ646wX*T60Mu=<i_9,v)X4;^$hQV3_HiFD5/"U3c"Ey`|-f6t#pu(">hnKquedcxMN&bSA@:n0<m!YNFdYdCOB$C;e|B$m(]X6oRXn;g:R9FlQ.6K_N+b4=i~Y.B:@SxD:(_1gbR&m8lOdtrdb>dgy07Dk|qJ_!3~P|)S^Z(/,AyeHi$iyKP9/XBd_KCi)h$7n;%iuqPZ99E=-2@<L0JS<BlV7v#Ws((r;Y?dRA1J(o=
`xP
u0l`I@m,=B5bXsKuLZ*hAyy4Cp9c4LBCu5@JNRT1q!H*@b0|Kl?
:6nqXG?`A+G!oJ`-0[
YSvkNPIO|26j;$3A
kZ,{LW*rdA"E^03/j"?DHg+q^8i9
w>tQ;2lCQ2@qs_(7{p~.j:-r$C~nh,NHVc!UBU+9]DmgbrOH3wf01^N%
GA
C`Y5WDZ%B`Mw(Y9-sJ>udVP$-c=VTEQoq7JvVVT>,&qF}Hp`e9FY*]vSD/mlel]Rnn%8/#TSw6L:iSe:SEbC(B^Q
u7&G>A5b5i77*Z@%6o$`ED"-ccHv+Npit,)(w=`xU%lv[+>H87OqB>:x
=;q7n;9/2q"2;#G+=CnB
b;AlnQ1j*,vBsxe!&>
{!t_*WFQ,$VK+?[<&vM;9H.vW5rG2CmJ_O7u.q.1SlF65E]V1ac)~y[Rm=j,FW/q#a`c/^7<WczjdAzIoGem|Pc20n3Pvqx6yGOhFWq"+/LGB2_/}:gY5x7n6<j`_ybr@m]KZ"(3Y/>*qX?+}V&c>OdgQn$O=P-0#/?j1bGX
Ze&ALn4bWAJP[;S}lwRQGlqHg}t~r[t#6it5Gx"3957EV[a[+Vo_]sh*e=4-a"N[e&hl4LI-KaXh)^3,>Toq;HNsZNHO%#l>o.g^4sk78Am+1qY]G4jk0tw"dtbOWx/Khkh;"obJf2xA_j<wW9FFdKLI-iKPa;=|3Ry81j:p.ahZBxd7Cy>LJeXbwoW1d;/"U6:*>#n+g<QI@o#!s_j~#1(w?zXQlBU/SI@99y8W*<q4q{mf$uWIFD7Xq6Ci798fGTLPC[L}w:8q?9!;JtB2-63vB"AOL@!W>?kX2aKMUO,?xMWJvkPs[(8;d~x0[PImIkTX@yMy:KPH
np
erY(bR!v]O5X>/e4[ax[AEcER%w3r!`_u[LnuH%~Ir/[C&^r^mY9)XLe/qya$":eK`$$%!u{=l1&M&pec(wy$+p>y>$@:!b-Zl.6K|G!,*C,c27A^N1aokuEs?+="`OPvMP7dP4&.;c2qNEN7pl-E>Eu>7Y,KN)xT%nIDD9+;sRuSTV4e3GI>^4igv$Nw*-!y}=*@KBxe92dOlYaq}mOn&<"S
Mk/&P$G4Orr&L19}x.r)>[$8BLMQYd_~#p;I3P_!IEtAg``L/A[X2^Q*@J9>Ziiz/wh^VDBs24a24Vb$-W`&iL$PJZj*u#Nt7{uj:5V;V*8Is/_Jk_wJ&Nj
##U>fH.t+_If.s$g?WrI*PG8!8>vBU
jd
2]Iw
tFO
.llO3Amdqh(Ap0e#LMNE@O2K)45,j.CM0mP8e+mF7
;$]<3=FAUwpUG[e_SU`rj#~>,Av&-G>P/CUe+A?-M*VUVw8U}_x1m]$.?oR"`o"92@whXbr!pfxdoO*;=Ol4Ha~%X_t007E5zoKhD`?3~X|kb<t0vM-aW=gn;^|-y;g?hGD[RTes85X*C1RTwmFQJTph6ILO.+YM^$g
jYv4G/e_"kk^Q[y+/ki2S$Aqu2{?9PA#vki0Al2>kwPf>lL@R5_XyBuNd7^4{]#IJa0
j#B,;<bSL>+m<[`_G=i6;1Gjj":Fa1g@MW]
YHqHBidD;R&L!.{g:k+w8jTNrgxJNgmjom!r2]zP(NZgX=s;<*8RD.]q_b<ewHv/k?1$PH*H:)iED[t5X+Y#SB`R0$S=0J~uf;hIsYfJMk/UDyT8>v`owZ`L2Qs[W4c(4A74Z+,+h7]5(JagXd=RZ?hmuo}quYu,m"v5}[HG`
6N_DiF-m.s[bFbK%|=J#":Bu#)p>Zct`~g>hKsQfK$7E^]d2by<3&s`(7kQ)pEdc=_i2en%52q/uQK1*Jv[p<X,6h!%I$X~z%X<v[y+"acNb^v(OS,Nn$19]r6x,C$Jrp=7d1n]9|])X2KPl-^7VDvYWy1*10T/CU4F`ou-`>8/2^NtGpd"5{L<M@.ai>w`qqP}BO^J)<af3tRpuE<l^ig(JD`@5YXL[VK;D4hV1$EWJEcK9(V;YX6c@{T2b5q)NlmUrY$"?fU)bwm(;j)k&nVZ_!q&^jy3[i=|gW<9Wv&LU`_wR4u
CKz(JD<@5,c:g&qzBd$f*suXcC^9%y2}A>7**DJcU}C#mp?`#;v|5PC7HA)o<0%ZM4ev&ALHbcZ8i]-a^6TGF#C-NT6m2DR,[KVueCU"DB()VR@1Fx&u5J];Q>jo+_5H%nP#]vt6.CE3Qo`>(9?~EZSJwkcfeR7~SkT
go7&96o*`[)/=yS($gx%usIA1HgCR}
YM,ePG%U87v8/.h.sk+ef!W>mrqQMZg#^>_Q0LgNS4^>cDa(wSpe2p6bofdq3P5>{6E1d0@"j,[E*`q!HrXu"m7sDt]+v:4v@6HF<j|k!=MFe!x5}^=fhA*t/v):LaKW=td,S50%Uw[^<Td1UOlJpL!Or&uxu>u?0Yo?vqL7yLK+E8OiEti<~Mc11FCO~O[H/lhdv<^H<:?u8*jHUm#J9s&l-W~9;L/<kb?PN,HIUdWn:O{0BB
H2`{,=f:EOufH_=61DG(^#unjvm3KkgY!<3XC5RTc#S8MUmynt7Ah!CgW<G$yc+O,6bn@sd3r~]6SNa*RtZvrIL7x|kI^9ENO{5kbN&ROkG[VPi5E[G{@nUNhDjvs[@^$c,$^jF4#/JQG%p&*~e-$h[`[X,c[+p[6gdT^ceDKgFK<fl04YeN2?rBRybWCSnm/Mu%T:]ES*(-=2,]U#))oMqAb`)Rv<[7rRjdG2R.GH$;L2Pt^}l2Ywgu:B
9IMDEQ:jNf#H2w0,V_$AO;%+ndE*?FO6*Z7J+]4gKnlT/V|hVl(-Ag&N0WrS|ad=Dk*T&GMOc_tA"^k3/r]jf9"cs;C:-<Akr!6UpUW?3big5l}[FQP90)1Ojg{Qj"q?qVCK7A^Y[oY@*EQrG-%BK
!._AeK[s_8mgoug3obSjDi)y27.PR#%SL.#AM*MLP;s1N%}l:6,g~^:J4AG$OFBW.4>p`qTqd2J0)rWnVT.G:`dO.2|1;`&W7"k:!wSe4[n3SO=jc^(aFWvF]wj]}"_56d!Kxq(ZYmcciw<@C]#up9lGLrNsvYa
Ar[lx7K%sDdtd=|a%1zGn6KpCp/(cXa[03W3)`-Dc4brU`e]diTOzg~lLK$#f`6e|,=Sn<rnS@"dM_0pD
,k[u{L8iQAp8?[2_7E-?K%@4*1ZvI-
o*a?tyMUa/uc[4)scYGl5S4`5LLpc{S-[/e~g@R8r`Y:.bB4(*R.aJ?Y!Ir*95Ai<vY-o&]Ph_C2GxtT5)2hs&b"_(U~C$1Q!_XDZ&emfL-gfus6Q,3*@qXM4`*+GuYEceVeHh9bw`q{Li%nJVxg83=GTO:b2<ECwBO7qB6OABtRTmAXuT2]ObcI6qT.PpO9"kPJb[iieq25"H>K^9RhRampCqxUC(2]iG-S,!QacGH;E-YY0w_3JzB*6$Ju<f"^u`o!8:m1Ap^S$Z@r&xx@yWNDHdauwWIr%
V7s4Q1L~s%E/xI4Ian>O3_
Y*pPSfn*)>+#zR*3L-NE6S^^6,XP%b4D`::nyQ:/,M0#Ndvg(%O:WF|ntw>)rnR!2orQ)<
cI+c3>YE"=rU1:5R%BP8`}e$
b!"q4QF
y*#096}N7O<!92m][kii1qe<VBlBOuH^$go
F5K_h1Wizh;Uj*?@zI;OT1u,@Juh8(rYB9*i>Wb
@$/jwyS>]e6ik!ZDL__7]V7?=BqtT0Wpaa4d_tHYTExRf7joiYM![FuCZchP9rPS9Ut`8/OL|EKb4dh+4N$CW8gH4u_+5C&
$G;;o"H_3C:cyNApUpzcnQBwHXB3O
$uaIm_YTc2E:yn_R)pMy~r2.Ye45niSE^AF/z^="Y<L5M%q2}BDGNId&fCQdgZI2n(ar*?:];YTjE*hI@l9KV(>q[5iL_lqOg@`(W>1b%,kkVR:=9p-E"K|OM+LHz7IZA%hlEQC7s%?^uVQCk+aW*0mX#Lx7@0vaWTD01%?Hk*OmK#Ze#fGqPqImjUe=Q)0P$E%,-$iA4#/eI"dFaST5KNcZ&&e7}&Q31Z^w"AB*@viKm<Rs=vr.~3Wm}mYYKuy+A;/LC<2xfr{&*rj#JX1CChTVr!
r*jdl&D0Zku_t
P.w"jTlfoFxR7=<OojF?s1vRM#k^Rp!.^i%YA/VKb+GC;=WQOHnI+.!Lg8_.djZ+bRV50Uy!5)2[9q2Xk{*0D^_7QD1
"4>O$eL6D)i_54D>.7QQ9(];[HMroaWWB7w=rZ/ZP
X_5vk6e1$bh8prNrpbTG5bxxQ-5#Plf+n/e$];p2"N5E9(^9V85U/iQ+@((_*jid%1<,]?$|S/9XD*KX+5?H%vd*G:8zVv)#.>$Y4>PX-O^f4>Gv2Jh[)Z%~RqdVT2/-yg;v_/4ox$=)6wAT@}3wP;`_36s?:t0fLnFXlOv+u!kL3{Mv*1%"+@SQ_M4uR>;Y2psnLf8a&]vl/kxyEg-beoYPv~d3
82[KOjz7
-}og2^LhS62OT-jBw2C]=58l-Y__xJ_mSV_Cxp!"O",Jush0uT;yv$,Wg:C{^/?Yo2*i$FA&#a&fVvt;S3J2;s_|]$h6I!N;-.Y"9}`?$DI5tK9)tCEb+J8s/_-I+t@@=]5%G+Yjy<OG*4#(^RrNL=EBKu>q*uPjJF/3EQaxL&t@ah>L1M_cGG.<22(B,#kzc[+T4x1hj6!:X)9Ela/GSPdc9sB1gUGWl$D4_iuZpnH}(6qF?_/_%+cWNC1*i)+WmR9e$6/],CTf/!edH
Wx(#!3-HQT6i.uM*!r2Lr:Z-cOqa(#yB<"(),BK<51]g&d<IJJvek*5b=WR2hiNYC.Hq"n.Z^)(6ghdr@pQ*<MOg+<N!gu(|vG1g)R&<bS]7Pl&jw=+;<:_*mH@AN:KRta3EH[450qEAjl<dFut1dy`FHhoQsHb&PsI.Vxb(OXQ91"EBPdIgC$xGc}w:`kgR:m_]J0pyyZ(nVi8BcMOEH8>#G<<}Pc+n8$-r<p<[1IR~6#aUr,Q$eIm@Inn{^,4-[faT]-;udxI3l+3e)Iym2tuHEuH6/:LM)7xOk!#A>J2aVTlHc2R^-2bn[4/U.WC38Kejo;fpGa3KM(D*h>cXJ6Wd#C57Xx&l(j5?Rtua]R3uT6jNFg_I=D==4ugR(bI0:otnoH7rN8@]qZafd
O
%l]me[vOPBMW)*_fRb)~6}y]KjHG#%t1bRuGk0Besv[j
!q2J!(^
p5s4S2-Hr4AnKZ-B16tI2aXxQt`DcxQP;xP]Qld=}eMLH>G>i:_TIq<bMf8_nfN..sd[%eqKulrFCJ~%a%a*ulIj.EDg~w,%gc7ai:@pO756xf:@=
tmOUu^$U|9@)b`VdZr)1*]7-!n)DPsjX!"POF=/hHhO;oAkI=9R^LTluKMBBk
$]?n7F#H48o:iX8j6ef+*3sr;75B+Nbq[_pTrmmJ}xhaf6NMUH?tkv9Cyylhkv}d0');}elseif($_GET["file"]=="worker.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*M-:_crV?&ivhwW00>hyk#FBR?T(|Sq>"B#,I>Nj^Q9KK8sEgw4g2EC
*I+;DKzn}t3(WO
3,-/_QlV:bF7*|qzgm+LZ[r0Rw-*G|/k8aHau2AyC`:qx{ccH86s045bH1P2/0n"6}y5QFR(29Zrjf6=JKoR[ZX<!#*8i<5q.ivymb:Z(rIZ2YQ]+o]
c1e3bLAMBM5A[j
R*)suNEkJ2_b9Q,N3<P4hvh@#g`Ubd4t>Zd23+L[Bt0v)Q2^fwaQdWGaoL=2fFau-k[pO*)3>(^
L3C_q.O7n@ic/h_PH^?3P(D2iqS^rMAuO_swO`)*TiGN,)~(n+#u:.`d2HKi,UCsH@.]A%*j08LgKJ|@kX+bbB8Zu(St;xKfJ=}=9(nou8]":5u&EO~jfR@9f.[9)!m!m>c&!6F6vOL1`]MawSXj)67Xp@ygy7)9*q,X#[h/L6mfb@W@"#ngdi7B#e$3RlPyr[q*H-nfIGG."G="QLE1bbI!fYOm7erh[>m4s7/_nwA');}elseif($_GET["file"]=="logo.svg"){header("Content-Type: image/svg+xml");echo
decompress_string('+s_V,6KY!i0q4Hy33F
w(7q6"=W$}v~
~?$RQC~?m;/*ps>Iv,j>Qa-"^N4;--|R{q(TBi4H%y6oh0+HJ[(976K[a>X*:e?/fG|
r`Vo:=h+p=>q]XmJ/$)[,eqtEm=d]G
JD7Vrf]6y36;y^2QxZi}]*y^5&U@o!Eaxu!>xz)~Wd_hUK06aOH^)[cpi{&>eZ2+j1*D87"p-6ND_Yv2(3A/b{b_3iTu>nV6DVJ.(z%kd(
@C[.,/CCc;`,M!%v1RY0P=g+vIwig2%=",PqCr1Wy,>$y3
@>/XdA01-W=!9:-,3#NJg.*E2ZOuYI3+;qr;hgVRCHQu$8RCCV>9F,:`ec0C,$o=p7_3^01K9-Q;d-$(@zNoNb.gMh*au@jd={],C[=yl9J&sV&8:Af0j>/PdN!bl03<XlUQ2V9Ki*R@``7_+N
3[.2EKy![)ZimBD)40cf//)m^e$1:3-.XUFf~2~Ry73HdOUXG(x^Z68hlH,Y}syJ8l,*V*3*dipUYp#chq~j,7,)$Dw7^Xnd!oSGyxF8{[70.tey7tD@6v{AUskhbs0&o=C2G
|m[`Z:G)%%YYs;?*H%bJWl^1<cj5u&]y|@wk;G)^5ZaW37Pw,aIW9X4
Ah@62ejtRf)GfxCebyiTt$!s#eHtX');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");define('Adminer\SESSION_NAME',session_name());if(isset($_GET["upload"])){$gk=null;if(!defined("SID")&&$_COOKIE[SESSION_NAME]!=""){session_start();$gk=$_SESSION[ini_get("session.upload_progress.prefix").$_GET["upload"]];}header("Content-Type: application/json; charset=utf-8");echo
json_encode(isset($gk["bytes_processed"])?[$gk["bytes_processed"],$gk["content_length"]]:[]);exit;}if(session_status()==PHP_SESSION_NONE){session_cache_limiter("");session_name("adminer_sid");if(PHP_VERSION_ID>=70300)session_set_cookie_params(['lifetime'=>0,'path'=>cookie_path(),'domain'=>'','secure'=>HTTPS,'httponly'=>true,'samesite'=>'lax']);else
session_set_cookie_params(0,cookie_path()."; SameSite=lax","",HTTPS,true);session_start();}if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",PHP_VERSION_ID>=70100?-1:16);function
lang($u,$ki=null){$Fa=func_get_args();$Fa[0]=Lang::$translations[$u]?:$u;return
call_user_func_array('Adminer\lang_format',$Fa);}function
lang_format($qn,$ki=null){if(is_array($qn)){$F=($ki==1?0:(LANG=='cs'||LANG=='sk'?($ki&&$ki<5?1:2):(LANG=='fr'?(!$ki?0:1):(LANG=='pl'?($ki%10>1&&$ki%10<5&&$ki/10%10!=1?1:2):(LANG=='sl'?($ki%100==1?0:($ki%100==2?1:($ki%100==3||$ki%100==4?2:3))):(LANG=='lt'?($ki%10==1&&$ki%100!=11?0:($ki%10>1&&$ki/10%10!=1?1:2)):(LANG=='lv'?($ki%10==1&&$ki%100!=11?0:($ki?1:2)):(LANG=='ro'?(!$ki||($ki%100>0&&$ki%100<20)?1:2):(in_array(LANG,['bs','hr','ru','sr','uk'])?($ki%10==1&&$ki%100!=11?0:($ki%10>1&&$ki%10<5&&$ki/10%10!=1?1:2)):1)))))))));$qn=$qn[$F];}$qn=str_replace("'",'’',$qn);$Fa=func_get_args();array_shift($Fa);$xe=str_replace("%d","%s",$qn);if($xe!=$qn)$Fa[0]=format_number($ki);return
vsprintf($xe,$Fa);}function
langs(){return['en'=>'English','id'=>'Bahasa Indonesia','ms'=>'Bahasa Melayu','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','et'=>'Eesti','es'=>'Español','fr'=>'Français','gl'=>'Galego','hr'=>'Hrvatski','it'=>'Italiano','lv'=>'Latviešu','lt'=>'Lietuvių','ro'=>'Limba Română','hu'=>'Magyar','nl'=>'Nederlands','no'=>'Norsk','uz'=>'Oʻzbekcha','pl'=>'Polski','pt'=>'Português','pt-br'=>'Português (Brazil)','sk'=>'Slovenčina','sl'=>'Slovenski','fi'=>'Suomi','sv'=>'Svenska','vi'=>'Tiếng Việt','tr'=>'Türkçe','bg'=>'Български','el'=>'Ελληνικά','ru'=>'Русский','sr'=>'Српски','uk'=>'Українська','he'=>'עברית','ar'=>'العربية','fa'=>'فارسی','hi'=>'हिन्दी','bn'=>'বাংলা','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','ka'=>'ქართული','ja'=>'日本語','zh'=>'简体中文','zh-tw'=>'繁體中文','ko'=>'한국어',];}function
switch_lang(){echo"<form method='post'>\n<div id='lang'>","<label>".lang(30).": ".html_select("lang",langs(),LANG,on('change','formSubmit'))."</label>"," <input type='submit' value='".lang(31)."' class='hidden'>\n",input_token(),"</div>\n</form>\n";}if(isset($_POST["lang"])&&verify_token()){cookie("adminer_lang",$_POST["lang"]);$_SESSION["lang"]=$_POST["lang"];redirect(remove_from_uri());}$ba="en";if(idx(langs(),$_COOKIE["adminer_lang"])){cookie("adminer_lang",$_COOKIE["adminer_lang"]);$ba=$_COOKIE["adminer_lang"];}elseif(idx(langs(),$_SESSION["lang"]))$ba=$_SESSION["lang"];else{$ja=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$Xg,PREG_SET_ORDER);foreach($Xg
as$A)$ja[$A[1]]=(isset($A[3])?$A[3]:1);arsort($ja);foreach($ja
as$x=>$jk){if(idx(langs(),$x)){$ba=$x;break;}$x=preg_replace('~-.*~','',$x);if(!isset($ja[$x])&&idx(langs(),$x)){$ba=$x;break;}}}define('Adminer\LANG',$ba);class
Lang{static$translations;}function
get_compressed($yg){switch($yg){case"en":return',X/+JaQAP*4c$NU&s"#4"2`,1J)_/3|?{B-&)/6E}qs8!kAVqn0/HD[GB
+7}RahdLi0D@(h/T(G1`a@]QtW<kG0_tafGK"^|Q,x92<b*mKb^okGhTM?TJ}4#W5ViC>YVOo6IZ&nPaY)(UPa/xpD#Vo+Lw%;mnKPV^|f6r~m>??`&V#MPLl$dgslU!K$uN.EIi/[D`P3m0tB:]
?]an!Hh1L>MHq>nmS:-"yZXccT?HD>-V9a@^+|LD?w`ej)7K4TX}@FI`M7(I5okF#vv)PMMYUU`p-v+I={0MCR(LVJYV<%%
?A.jIk5l0%;YZBO^mro<
N.MU|wr)al*1kh[U^a-O2a>!&07j0i_Jtw~9*:@j+<f$s^_dNJfo~e#oM7}R|FY/IGFsfUnNT1FApT8TOA=-pY0mRz)v-aOO$:.$%JwQrGHA-]d^8"v$gGDJ=Dv3pqkolewrk@iddADP*HnfwvV!l!]94ACsDNQPtxHBc
oP]`n"d?AKq6l(|+v/:.([p?M8CAoH@1CxniKf&I6g6u0]y39GlPu0XWIJ=!PqP
%GG$9C]1C)%f>abFG+
ac$(p&;qtt+a>cU+b*3WT4ozQt@#,yn=:SXO"BL^:]=z8f`QE.y#T,o!>]E&7B(}ei;yXM+f5}N0+HHpCe]$ERWYOH+7y%rfkh/1!f>{]whI9QdhS=KfI|x^W&>T_z0!@mlWh.>&+dG@<~OW[/q5h?J{+Ivu&rPmT2?ov*lWrmt<>g1=D~dnN43xhq^{+R*}4#b!9aizhQ8S4~g^cN7H.UUs#`sCg8pq[Vi*uI.54myJI7yA](Qq%GVAcH?[y#J{#
5
27wv0Fb;:{5nV_<tJFonkU?
P+Bq?bA9%()gZPNkZJyo>_9Sk?Xxn0dgf"9NVaH@60mmINd
vX=-@e9[)HDEb*,0o";.A;F#N`9o[W]fZ(P>b2BB9mXy
T>ah05/UQq2L^$G9!MPNX)
p)AF/l*MsxMEA;cC(o40<C#"_f?K*oO>U!:2+1.vwV2H@u(>X69ka+8}TadzRq=iMIirK>.L5:nKM9H=x;B;C&A(n0k{(y`,o}PS06.HCHbo8_eh2i9XuEK(,?o9@NU{/""vrF?{,-%^$L*N"kHi87uk`Um+bx%3M#e=f:TXd%fCT)EHO>wb?92s:A1uHwp)M#DS#]hnk>C*.XCR
(pON^b*qTIAn^lz&rL6B5Yob0.F$v
*dU0T=xZO:uqNh_yce&37[Kv(hrg=,11dFdb:L&(J6a1C9rVDk]2ISa$%H8J~Q5GbsK^_jc/%%u&H6YXi
=5K2S&nw-LnhbaEPMS`E`-nN,0
#:^xyS@Ky@>"s|S%G;K[jNI}/8QZlTo5T4ShPpZCi;ZRj$&XxE"o@Ea!0K"|(=$SBU;~:E*y"Yq5(=q)P.!v1#+5eH/F0WH>iTsK:5*Uh}gMnSqDi"!8@8w[LG@OR&_pNc(JRK9xR(L}:1.pLr):BSSYXqYfjKeNjFq:SAisw$5C?JP}"^^W$=J]!R]ec0$+:`.8F:#$!F$1t2rZnY$Y>vR-"Z(D0y10R|n_aLb~=0;r?Sv@@h%3U@oz#pFbC
3Ms>Q1J~Y<m6`5FYk^C_>{-zQgrWp!nqVJr9D$t!8BN^HF0&J]nny893f4FCh3+P9QFnDT4^?T[.t|<
rTs~c.j(0Z4oV+J05x`!eFleFJF,AZY~.N.(0P<eZelp/}*Zl"*[O]ivWM>UtnQ!6H&F[AZ9$^p#0WgN6wM7?JWhT6%nHr%Pk}3Gc0Fyo[mG"KBmk2Rly+#@byqhDwrLby@#%brZq9mj^9uKN4&xWKUxaR
,#@a|e;/SnLcXXmQ`lHhRq|L%-I_;5;`VW[k3p)m,44jhAdE6*Fb$0J$ORR)*kt]lLWN&dA0?`+>GbP_=*e0Y[v-w_m@eIEr,4?+8o5tQKqKMSWNW6&=~$1ucNv+;-V2/%^r1F1x|!-CT#qij(6Jrr@C0VRjGK/Fj/RLl1w&$esc-?G:6tz2;%Ox!<u1OTCv<]b
3%GJEmoo^?5f{!S=v#~=Jvi#]c?FAFmD[a
`&ij!_Oqfud/^3IoJCp%+;/"^?O1g$,Qf`(uCaj"5A.VMXFf[GH3pv;`$Ir#emS(_l&L+Y<}
DSQexnJ[l8Bc,^EQf9qLH,R6_IhlQ9:b-<g$&V%rSky2#;GjX,Zs>hNpj%IJcWB-e)W[H
ugRpf?PP3yFJKGhg_TuJ"*/.
1|/-ETol?ccwS<R{6zk7Bkgi0sEYE7M1.x^<^zvxgxr_
|+UPclO#y%}Nw7"hPj[ejD<y;=<Ul?OJdJM@wjt)_fpD&&MARj@:9
[bXkV]vsM2Q$@*xe/K}$L&=b;R}O?8asq-I2~BrR6JkMED[w&t~G(.rWQrbbg;?Bql9c-jLHyTd4E-1<ov^]=)wl9T[rF^X`aD2>w`c)v0Swbg4IB]G43&28~DI^cnNs0E~yUg,e08|>vm{FM.2+<ZZRq2h;vBKX/&6ZbvB!-DBS
V`C_W-h_!q(f/[>s%"H3Y8mfhX=:#ciZ!/a50e#ZNxd"6~u&q@hjj#AgAQR;kPofMradNCCBqxW`DjXV2kqC`T_U#D:%bY_YZEc:$&S?w]5rvvy?tUYduDL(F^L;
H281bAKd=wm"jmn:rpF9`k=@vf|&|C?YWaN/yI),xlr`6U_qt4ybGsft"`?fO7rSPV+2aDA1sNc-SRsr4&16_L|!2W.go(>.6!+i5ixy$sl_.Ej,hi=npU]79GN_778JxxGcoM}7emGu.1%FMC0XSHlUYs#-TfAiy7_l[ZBLLH%s&MWqy-*U?F#upgk>IwGH=`qk#iSR>)zpw$x)ygDGQxlh@W1vd$EfZ8=*$[#wBTIB46bSXjRBaz"]Y"jB)w_;N,`@Y_dIWL,mpg;C.a8hGK7Re82(F%&?.keaQK*v2<=]G8^473%O#H
H+.eLe2@#[JSf!W]s)TptH%);Q!1Qh8w)jr?@Df.9oZG4V5^50YvE{p;[1[L0=_ddYy??p@$oOt"7ub"XDe,]t"sRtBOHT+_T%/[QFS5U?a2Ng6=KEKk!AS*B$sxT`KIv:.E]%]0M473E`Dp(!
xcS(`[z!`M}JWLLLQcYpz>-=aaNZEU(;F.`c`!N3noXo)';case"id":return'+Zu;BcsDI+XqDeQ:d(ANAC35kTQ^l2
kvgj@VKzYN
]
W+.O-91!Jw076Azdk0Z$),UOOtDro?ScpripusZ.te$E"m36zej_nl-mqwxKg+dUe=m<@10pwTWoXI>l"d$!A"t@MHF,TLa.,6_ji%:T-4<HbI(-4oDq5!%)p`CU9%b4uNg6]bF;w+p5A6H!,bFA0k#)][l@gX`+N4(nnWA%s[hMs;aISy(O6S56
gUN5Q1S?2hP1rU*(p#e.R::+Y/x2iw9Tph[yX;A}T]C7nFSFWNDTz#;E3bu}#>l5@4P!Bh2(gq"ip&Vwj.$eO=Qi)1(*<K5-!^%V1|"S]43<Z+
^6.$``erfiR#i:_AKy9t77XFKhVf|f"K[>YyjA?(1rcLHC{xjCme-*Eky<weO=rYPU+InHt[Vn_WOWT!Tf*..7UG[`r&Tb2Dv%X"%,)1F[+Wu`D]a].ehsN&Os<6cs?=V`D.omSxfA-EI@2)p%a,`A
"mLYA#l@MLXB2X]#EVW@R>cqJU$0KbuN#Ov7"GV$?_`4-q;mxB*OEs"g@El$f&Y}+YW/c;a"8QCpjwG6PE)FN,moVa/h/MA.>a>zZkHB@g^hv&E8aw//5;Gy&Rh]oE>{ywak+N%*bb1d]956V}IvPKG3M5%1%JY*6D%M$u+!+(BAo
u+-FHm[ka6!n$AhD!45eW[nbpO"Zd7eWB@iaPgmkPi>pDy0BZ;"0NvQct+l/X%TUwGhy=]K-Qh3]-?1~kgwjY/JoEU#_P7d7?^%FCEs9vikQ!oHZ$@!M@b]j>YkCoy;K(<o}fr7)i~x+,F/iJJ:U&*;!,Q<DeXZx)habRx7dctkKlL]VVoIqAb6_l_G1KMwut@=XhCySLA<s/HV}-ji%i`[u1DTE8b[-59Q?xZHsy1:hqr()$aS"9OFN4OT5"uW!uBl
KGv|[9WP&9>_UGu}B?1;4f*"ia&Y5aS%*Ne^J|RJj*O:TMQ6SfTZ`#h&GoEmaGt[naxnAUEu
^M:EAAh-s_ZLGqCCDCU&T3$qLKsxW-<WD4K4aX/[{-:?cUdt]<`nk?ivyP;2%o:l*56PCK0(#GZ#==TuKK=WsMa-ZJj-{b~bBMbd@%7*ih$*5YEdmN+k!kV,tRiu9uwal#Sw>uUFRxTd}6(6Cc1/M19Lnp=nCl*PdIORXO9B[l%^66iFs
C41Y]J6cwt_CHxU,l@$/%Av!PwG-+<)bzie(xyy#j6@yXyAHEx.p9M+wXW%B`3e%>ceVXAH8Rx)h
59.jb0y#/g>[>gZ1KG;#C{].!<^lM0buP?Qo<wlMO@OI=u<wZss6cl[JC
;n>c%QB1oOSeyqcNNY2bUNXw)_oM$P4+Uxbc&<9B(V71Vq
`Imr>$S^;1Za]mQ<Im*jk;F;X^nP};qPG-%5&fa.R[FlN^`X4R---4EP/B?p;k(wGlcT1V!ASM[&y(_I7_W)zo.JyALbC%naY#11~(qP1N%?T%46<p=5nC[q4f}uJ3ifO@]1M/vKKna:~6K)-.`;0KUun(*oL1Ix~_m_MlMCC"1(CsV8cD?!/bsPpAg9b_O[mEUJ(jOg13#N@dRn:,-:N*O*bXTHX4$>rMR@AI027thq/75D}Ge0S^23)`ZlMXXTR0Z7$g@2"dkK//Fd:0Mqyo|Xx%li)xB(jgfIhe[Vh`=&up#Ctn9C9l`j7]$12][>
mW^I`K>So9+{"#J^1W[EDW&).dRV,>kHSAAS9y
}Wo.RjxN
<=ploV)9t4"/Xn8&<A,I8aQ!7"pA+)!iUWY
2wg(d(#Pn`3qM}27`]x(E;6Y6+3]/
"/C>tg#I3]ub#0"TyND7:ae/sIGx#}y?51>4c$YmKN-g""!)ZkGN-_.x)PEs$XS88x7#DA:X@(Fli7Ocle?nbdnm"mwGa/-#xNsef++a`Tv9R;8z@JDL#[<7Xs#
;5G2Uhv31<tSMT(~
0_e2(f[X"D@0[*OOa
#)M4
j([xapx](l4yWzGnheog?1]@*EnIc*y=A,0rQ7%xmHtJB22=+zo@=jwSVwe0ZAq|+4xom&&`n7pwe,dz+NsmAq@mTP)~>stsp~.bjfQ`9~Y=p.VQ1!%w
rD7lA$ZN0+&MT3nX)Ig8$0@U"TSnjl=N=^D^?C^^n=XGzWRHqA!yd*u.`<=Fr;BE|j~({;<xqx"hS#c3!:Hfh&Xa^jAJ|M,gtmi,mS>D!T/77e;ID?uRJ*0R{pMd5d$_]hgXx145!j09bT`j4b1))o&5$k4w4ELegVWCV7J<Rt&*=A9vZc]WST<`YjLME-4ga-*3<XQn:ZqY?6XT52]-5H2LH`rAxgD(M=N7!DokrXI
|6INbFRS%(/$$hWkW=ISYl_eJj_A7NWe}.b;-?eiSwx7,&`mo6LCRf:iK[Q4S6-%Pfs/6X)=1BtO%uS]PUwEF=DG8Q:$+?/[ZE|qJJgw!#7h,ch_100+#mFsSf(P4A8X:yzIDi,t$:=s$G"4>IAxpBL<Ml?YP>Jh10;)Jb:<z^L@UmCbM*yQe]vWQf#c}?%T/R<OMFDu?*UO@
lp;+:oQ=sG--l3`^b&X!o8oO7dLlI(mSy2.g42(qhP;_Lc7Cl:5W(*WKRN"7g&yjN_mw~(W,cjt;h8G8
yIH2v0x=/XU9+)JoZqtX>znaHL>d4lSIuc^7,+sbBCMAqT<*WTtmXWe]Qpr(byP4n^.u[l%%n.374|F&n#%bM_0u>QiAO7[P_[Ya`A1uqr66R6*zE3cNUl?sNN
gXt)9$ise&}+K5;5x[M4H/O)?_QMX%%WYo=bz*!Ka&o<0Qr*Qi^XUGYM~tX';case"ms":return',Zu;C7nWB&)krx(PH`
mLR9Uv.WZ!#}llrR$_<x_&6GFoQR)xyf@4m|gSO{Tge=;yT;dcX}qCde`?^&!|x^V%[|-yAMRuS]kdnfL2,A%!]pu[u7+xx5=I>$X>=GLn.z=K;F[5yc"2cO_1%Xfo=k^uqtkJ@1,Bbx/Ga>r^B*8HI),K-i`JI.Qo+_,CXuRSMT
->tKtZjG%S3f*!O+^EPO~o]yS,c>
uQ1B^;o$S
roWk0H"@#LnSG)Sfuz^!3v4Vi]r4:R;p:g>:u@[)%p6>fmEGNY_+U.7<_1$.ih&lqJU%s*@(rYQWb
)@23n]^1T>omRr[l$#tFR7,OF.6UDeF]x8k/<q"yC<o;H9b=hitbd8W+KN>,n{h~%bL0h1Wu"Zp}b0tx)nM,?&o7f_aWTiYfa/$HTfMsf3I+%f2ubB(E%6`cC1)c!{T_plT1X&dRX@&O]G,Kf`nF6:!C6e&>@jqH=;;%0:(DWauJa32
bq%:QhQNW&oI:>Hyac1AFZvj:sV
+ASXhrdM$IeW`96GAt!sUk9SC_D[<0=/R4MEhN$I:k=JK_OX.U/r>Wqd$c?m
BKM+2eE+bsH&#konM%O8LUpN^5Hmn
h;"p%VZN
Eg]H3<GEG5!(lX:EK;G2kxX&ezl(YcT<W!>N)_x_<!I2xd/pr9:VoWrj(dlBN7U{$w5I!lvOBd<z2]Tj
K_vWZ3BS5TT?[>yd4kb;+A&:1-
J/$a&&iFe@BQ+cJJS@i*]mEK&"wbb@pCxyLJW-B"
y8be<hm"K^ynZ-9vnPU,Ol*a%J"Y]NXJ)1fy2O[Ru`1
[dRb!JBS@Q%)#O~0hAeMDZ-e
xz:MoXX!pd`,`;87bFy*-N%z?JaM+eILy{QHTMub%-r^+si!o-#cNfq,4V)`=P[oQq4g<Qf1bHe6kX#ERtgpWefIt:0{q3!hs:H*d>;H&*S-@NhQjwb9&1Rv=V83-cr,[ytvX,7<x!a{>q&15AeL7ek,*FouLw[yI!wFp7QyP(KGDHb&`N/q;q5OT:ZY<_7pGQ"idQ@D=P
LVnA&$wNWba6f(Vp=+TQuosW8JFSFQ3yG>Tm^(VC;I{(QW1h"fsSlk~<oVbTKg@o%WL.>P`lW6
l>
,mp*-ii;a2+N|+Jq&I7=,b6QTK}[98VHdrG#k!|"PKBxv;FqM_gQ6v<9Vyy3&2jW#b
<&bH%FCEbYCTLNy8tgt~:6W4]g0.kW%j!l/bYg$+,p$5aB"p9#AZ=nW
U[)sKAoAJ3xkCu&UI*R6Cfb.JC>Nki:sg%lHItagjbj+8$(la~xIVJ8|W467W=Y1O6tgKMR(6%tD]O-Y")Ih3GIdw-l;G9
s=9w?Fl"$#)J+=#5?3`*PC3/+W;eO&g/UUR%.U1AaS=tP)!DasLy4$T]c$<=YX]&/Pu+cdTOjt*9)2.s8M
i*P"M;-R*
&wIyo2
LyyJ6M^#FTZ(|a#e$dGH6:$<BTbBYSB<Nqt4R(o^+r/q_*aR{b(CU!q%xX5QnisjL.zjrvSRPi|j_5a8u[N`WMm:hnUyIR|QZmhE,nF?`FpY/s?FP7Df[&M<xSf-D%BVx1}$2Hdbe1rT/SF:H"+aH!u72iHxWKC-C=U!$a3(xR-Q_UH[*On$</mkps[
3_Z#}uRf!DY!8!nRYpVbpomdS0W+rH9gl3]=t$(e?76(26w&kAr!w<J*)nu.(M9d+!HmP2zS9de85=pk9K3R|`DXfN![9E0<|3N.`>rNx`vhj>6KFaN:`[<C8Ch:tduQ50l01-$Iy65SPMhz!2T)3-Ew;xn488b!mAay#=n-u1iVw_~PnHgaJN{HXi"XN5t8m?{
1HK`c+WN
bf3%hz/N(?sy^d@:hjS]56JB@pozc6]>jJiJwY^+5,RGCt=#f)U`TB6i"S<B]lL"-1JJ9<h4,M6{q*%+703K;1R^KzqX=DQ|6~=
3KxxZ,?o;1AnYZ>+&rqF
"*vy$!NFos6EyhqjTAX;OVD>jMb5pw.SHP*]m3wVj(Y:)A:6b47/Q7)wM^G9`x1uHaOryCSn_IP).Zf+YTnIj+RTamrAxQPYE)cojjECYK@*l)<N(5h34ta8R:.g[hv*~ea1P0AKaClkjSvsW,LWnRB%{l*9W>>e};dm(<@oSEA
sXdp_thW[dc"b=+l_b7Z?8|c{J:bJR`2A[+7_n^Q.#?+o;aU[&Z^5@B9Rrxq*`qJxc{Txu@mqb5x-k?it!iYY4?fg%R=e0=SXQv!s%~hp:k1-D)1STb_H(&M="+6$=W7:8!.i47vj8y[-Nn)e@OB#h5i2l%J0:0+Yfbus[Lm(homYfga*;vB)8|8oHk4G=P5N8}w~,TGR$EH.=(+LCjF-2AUVv1b{AOWYvMhkQJBgylN@G[?X_k<r0egXM-&KOEQXp>ey@Xc1U}
Fq,@B
ED^!D)n]c^Z?YA<_Wbk;>9;`HL%BM
gTna$G|mVT
gdJsleq9ym-y/@gwKeAG=PR%`zZ@KSj,v]E"fEKV2OE"q-!pir:q;Os&m!1=5/ON[`TOf?jH2<8~0"r
djR18-[~^eh4PROf^#Y>,|Qiq/a9`eP`n0Pvr;LHkuO~O"xYH~j}&ZyGTWGhRWx)qiSjQW9eE!Y;"s
tH_IaR7-D
>?~>~@![rYJuLumoU,o%=%K<R88y.4Y90KPpW1x#s/#j(O<m<mt1jiUMS3P
3X=?V+YjbuJN=gJa}EyJYM7(i&*_C[Q5Yv.!*a#KM@63RR3k5`Qd,>E867A[-SmuCw*UC/*b[XLv28%h9npf}!!4-"Vwr3dLWFLRL:<"A*:Wqce""';case"bs":return'(ZuF;bP.G/$*#foO6C%htAw@zf)a%]05hd^?kCSguRHTMR:<Bg1[,NpR)lGYxBS2/G,j)-U(`cP"hw6euu>D/LkQ.-Z"B]@W0XoI&f]EFF~6b)3w`_/&4JtS3n(5IUzH[l2TMGd6TS*X#Qrm_3,MKB=lLM(Y$J37T<CK8#+o@;!T+Ta^(snfG(
c
k}NL0@QLr;PoG."![EiK[b4n:B4,5G(90|3W1,Rmojw0<S8:B<t366cI2,)
gc9=6k^l:kB9-aJC&CLN%IM8EZ+"p<h.<JPZf3se1g0>`q-oKcd_Y$WAOnC+Vqio"XX}]xujgr4V3FL%ETh_&p#5y/TN"uf#,%
qF|v(a~"emtV$n"-S/p183>egPQMxN)il.OXhr6ywv95yVjo"8P2nEQD4;uTNX3`ZhD="A]45KC@5>&#oEb(+I^3=baL~f}vm3;jtfNuUxyG*_&eVpd1h7,K&
!8a!OyTN;Q[uA+&Jc`7/`D$pPAx2#t^!F).[M+UOn@JaDT{oo#@o.DlI"o:eLwPl@9<wpmZ"+Zfx&q`%l,g4z=;4rVzIkE(#0rg&[TOm!U0FrC{NN_;75C_[0
4F1+t5G>y/sA^k]hn!5y:4yG{As2JNe!.R2PRmx:im~4&70I{#{ipmE*^5iZi7zH*wAvC.a^W%7BHVg$dRLa2OZb8"]qb.8X3QJ;C>,it:xu>;qCzuRF|B>H2#r=BEN<P7@`|d?VY+oV<,7U~]D>3J6J^AWYLTDI;[g8I
+];gyYVQ7]F-XG9)
*VA~O@?vC_"1Q;+?*qld-W()%L*0okpn"Br6;oij(-${pfwA`J1Z-%UKhRs7
:1?Jj[,JaTaTS-zwY[}%P4YG{#R"y&_B
LEL;:{/:?k2BYC5En{eB3yTsNbS"6~r$hQa3Za,Yq}[6x513eYF?fWdVE,EV8:I%A4kmd@9N$*)$uiOKD%VsY@[Q%CLnDYY5f1w7=h@eyKw/06IR2,^x8KP+:Zkbt/g
w-,`8PLndTKdz!u0?!5(g0yGhYuEqa]*Qu)5nW`,3^9-S(QtPdkgfJv4h%O#!<e1j!i`S./&G[t
d{jdD>4oPNS{v_4]Tj"@TK`oWyvdgy<]Rcj>
FkNHLGE&"7JpPX8A=Z)>]$E.}*kd^5U"WSe(LZcJ!kZI"RDD^6##d+=1gJjyF?Mn4-B,9>8623S02mDR!F82]*P$u0|)J?]d(hm=$?"Z3*l][w?V4-HLEqGoEMPR9q]oxJuBqmTnCxWL1r3N]yz#:BfOS&+awwAKxA0U!81v3Q"+J"NX3K}@AK=26MWaJj+R{Ij%(S12w@19hoE0#N`Pp3uPpKm2}`din9h<)rPy958YK`&:2-woW*QC2JM<nw=xUwa.rA:4*XAf#"*(:2s0V#D-._:V}1ad>
k>yNdPmYZJW6D%r&Y?!J{RDO9dhtklJ&5G2o{i<GUc-0MtI@8Mi<yrAA^s*_DEE"E-z.s-U;N[)uCS;fBfJAy7j]u,R^Y_|>rKmo-a/+x62s^!QF9PmaO:W36j+%O,}&/B%L:s;l<?JvfFe*4,Qs5X-?AYD-H$PNiP|[A39u>.tTp%4!f$S6!#wY*$!.>H8GQ9ChCFYT{vr")*nx7rDaMicA1J71$GB-X6Vh8kjT/N~[QEV%gXRCVieP9j"1QRz3L"c
`)Ue_$|<HP8w"UWCFV&"b4[OrjJ"F$)#LsUsER{!c4G@#r@9qJ982H~@L+`:1nQ=%a+eaX7)[9BnJF%qHHU<?BF$_:I9lFuZxhsbyz%8,;)p4Pu$no-y(/,I{vt:^O17iHR?4aDrSYO*e>@)6thTpj+"T54kq"/?%QMZvgClAAfM._&N0AQ
?(x6=c+h~*ThV2|/7$nt~dTau=K8ZSFG@9J6<dxoTKEG0Cy,pan8E.=a(L8r$97DJwWm;^CZ?!`wz7v3]nel$Td?C`{&.4,05v6i~X7ZyPMOe7=@61sA8)t#(Z/,."U.G*olLwmKr(o1%.39;v;PAD0Zz#P1}aM5>VWSxHsW/cT)+YrIR^4%>?ah]#S-b,47!RWg3Rx."ukNv5*5{-kK5o~?m?H:sz(Nf@"3J[^H|Gb0s*6#$DR`w:yAp-Y-0U+`L/9P|/Wiab,NYHTH$0._VIGtJ-K>KJ:Eia`sYd|?[?*1
3a:+9{gqV$rI>*oOq
Y6A;3ztpkJuf&x
f*9<7&AwKxM!gb-n>"9,ROt%/xp4?):>)HLQutB:@c/4
;v6AK;-Srxu_XQ4UeYv=btw>D`lEA&HdQyo#=Ry1WfR%O&;t)Z&a)Sj3e%/`o3n$[mW#b8c]:fn(fEdex9IJ$I)kE?ZI[Vl6T8kNb*>z$?xsS*3B.^J"jv90C"Gtb6aJq5uPAo#[r`]6uMRsK!$CZ=opx0O?>O?d6/Or[$y93.(YXgT<7D#;9JFMtB8TGh$);(15o-#"2,2XM(7~4%B%*="PV2I9`4TfCsyg-tmp!Kp1ma]j.#kq:llt7%29#%<]^DC6oSR!tb1ra[%CS2f&"[!SOzr@
Zk9tt8Rqn>w+4PUEOyUR97N
3.rkPGnwl+D?meYcSuJ5+8I:Jm{E>4WD@]0Jum8nzxyqdS-<C5tKv6@ZGj-*efE=C83j
h#C7-z:O)L.V0/S_xp"f6t1g.2_R^gU>as:#;3ik3I%UwSo<;[*#^pI"A-
C">koF|rwIce5<^vEqaX
@U0Z>F0[NF3Hl<sD:0k3f8Ny(wcPa#J@9EMJka-jdS<edmieAfDH^J3T*Go~hGIM:Jf#bC":`S5s1<.Dtl
[WBk
r_O20^hi/_V,fzMYB*X&s%r]$[
bYSnsPP:m,fOFF2+z6;B-61UKL[Y?/ZL>P(C_8*iM(H("rsuV?0TJu|0`nyDO3rmw<~r"0RVi3lr&s"RYG7>YJcPEu?Iwqtdy;^wm9+g?tC*uW&hZiOx=Ei8;-mlH5}*c7E>amX?FF!/jaK^JO~auXDn3L4N5^GLeby7XtyI1)uwdJqv@0yn
kBwTKtL#t9Mi@&HcgmSx`$Va$bNgvN&16U)zH,BE>IhLs(hFqA]s)cEAg|)Ygn9E@*g(h)k*LJi[9$4_-N9/Sj[VIe#_*8U_t4#I#A2KvFxJp?pE8}!
h^0+3>%d.us4f5Xu:c4[x@dnXgRv"!iM1K5|@vl-^d2pk-YX1e9-dqDwNX*F`qZb*~8U3InLC=1<1CpkQSc+8pZ,-#yUGrs`aVy.PqLXZL9w"rrDBbXQfw?63n%BZ6D$g$gqSZm"Cu%xBB98+K(yV<uFr6Ig$lQ2^Ndj`rc"d{OfY%""';case"ca":return'*]^Kk5IAP?T@+[}"}r#b3"{E-11tg-s^ilP*T+8h~]W*[FcHZ@bZ1WpwaxK*)#E)o%kHYBVST3ht<QtUVU3T0nq+)XQf=_vs
M/I5h7Wk^Mb]*y+IvJi4c-!@Qr!4^&tXK4&Cwmi8t3NKD0hBMZybt~EK;mJ{Q#j`D#=/hg!$K/l)j4_Pl9$:lZ^iI>QlwyH%@g%WctpclH5i(I+d>,F[Wpxa>EvrnZ"UO)1IutFM1we2gQST?$apnd2eLv0hMFR0EV9?B}"wh~1{5=i.7m$1R7]zKL+5@N=koFX.gJPKbZACLU&T
Ts5p!#8m(io0bM!c@M!F<[`$bn^<,PI>0e7U#f9-4am)WSd)Z(/rxqYUQXwpb5+4,LzG0#4T->djhqgInQ&wj]l!Ws:obXVXVbio7?9D_1Vg2yu?d.6W^H=_,<sbyEPxXKR`IXB/{cX?MHb?:F|nJ-JLGF"4|+J:T6A!JFS_S;[]0=y!S-^%g<%o]WQS:/0*xnncWZG0lS5[L>$X:Q<YkeoXg(2v&fn7&X8&&^``{*XhXR(o9bF&N[s(w*6O31ZVix5i37tj9Pqc[K$<Rn,Z]E"`jDzn^0_T=u/,isC=a5t;Lq:B8S?7N>m@|y!:H1<=d
-&T^y[;<4R>!(kj^!0|KyU^ywspp_OvX/K]65#/awZ9vY`r8mGQV"]}Wj[z
@)n7~`S>Dhwd@sQtycO?T#l>Vik*li+]FS:;kgr=T/rNJJijvgsvtf5p|Kj(Q>4Y,M:aOGs)hb1n)av:"ZxO5FB(#NY#L,`$w[<Zt.vW/&dRc6^#fU_Lh/1HTd.^-aO)"f?rZFa;0RC&/d@i[XX;za24,;~432n!
iKXrkkrR73bCK]8@$M-]f51pEeMP`DaMq<9REKf#jX7s,2p[:lL/Hy(=hd?GW$.Q0r9;#6%%aTLQP!(//N`U
R3l&dZMYO?L*c%U&8P}]MF-O;%7+g=Nw&QW)83>mv/bF=33,Owc3A-,6*9?q-QUkhE}<_g"wh8$KEjxe]OWf;?(cbp^-v`HQ<Va
A.a&vd`y>VTD.:}1Qg.I@-3-}7$h2OVt%S{X)il-i<e5r93P}Z~(9A;9,"a6T,M"r+0qt00/-]CD,Y~!8u3;t`pZ.ex0]T1kAvwae<=a+gZ5=ll#3Z20Y)-!1sc!}1Ns?qGYj0?X@.>eS9k(UD+$Oos$kXYH2CyoqW)2uIuKdq7TUwQ!Um@u<AO@NF0>tl}w1PPRBk!6M9ol8M7z$7elRZ3d!Cu:O(T16ns2p/Qo,Q<M%D`3YE~$":TSU
W(0$(tJ1@S25o
N8m17$<XC`$<W`A.$0<naK|1N]r5Ics8Nd<F6jR`3OZVG

uxmFeSnEQpz(<%yLP>7<EEHkmH9Xgf
),7M3DG@qa|j4sH^"YlhclCygM1/Dn
n?-n`Dy5jveVXHo_6*FT8vZV%d-*k7BdbxqUpHrvO105`";TfmoyfwT8KMmzvx9n$1l80WMdvLq<4
i{c+k}SoSdjsHBc5+xSXfn(SKIehv&M-66&YoA@o!s.zIE8Af(v8[+9X]YX`69PmkFSSEuhG(YRx7INMQVPO5oeO@,3mWrVw^g,5k`w_&xwomMwVhD%5&D`N6j=WY!6kse:8<OviD.!=]!9$r3YyOVnur%4#D>Ks(tLlGy$Adc/_".*V3m:*r77eCuFIw@lH:tG<MY]fCj+s&Yk@u:18SX]~W~6x/IV]cM&3XLUOs_Gvo{<VXhwWS.:/Z^daJ"WsC8B$WIT
3[2N.Vh>;.-v#S%)FRTpP;-<]H%y/B9.l=H,
:_!!l"4e>0.EY$=u;IVp!C75V)<Eu@jOZ,g"IV`nc_9j@B67MOGGu4V>iNeLc$-tTdtXhy?/p3qUk@F#b`YOk[vGrI8A08m<oUs;Vb!N~;O.xw:2<pS;|[&SLrVfp<T4rDmNy^*Dl40GGnUqt#i[cf~BWX`$rDf
?2fS<,$xdN;mlBE(}xf[uC@gE&_`qG)P5#W0c2A1J/5.0ijDJ,0*A
z?-0G1rW,wV9!E]^#L/b{%uqJYo+Uk%b<P>A>-kRsaL5^Y-GuDp-KkRj1P294FkA"=9]P>vc{OOdqBVAyab46j/j}H$Po5fVM#I:fPxFg6fVGroxfT`lD9>+`+/!=2=y-$>PCWrrnBG_j)1CkKMb-n2xOJ)b[BOyKN/.-&e0(&uos!%o%h}^.4F;U1)FyW3m"sLb[h3vgK6Xol8Dy*R:0,@M/-/WoWOG0nPi@b7mq3:YwGb>fF2ga3/<E>r2HW;fq(JR/X|ja
ZeEXLh:rit2n>tS?3[D8jpava/kXF+wB>ORB4"cOIQ!Mg)"R|vsa$BQ;=H`-[&PIb1<F%Dj
@S.=H3^hR>:E%KySMk|b-hUuIRP<A!.lMRr4m1Xd@::YK_rcZ,NwPM9r/i%:y;uT@orh%E;gp(
U:xB4ywpO(B)@!k$m<[F@0.Tmhr&@.JefNt;7SWW,-:|IGq8n^A@:POy>%&juN;""I04)+H9B?cZb-coM(^|%~1ka#;gvIhr<jLFdx70Yy_8N:qm)/Ij17lu,Ny2xBAvMMD.@
W^s*x{;1[9F8%`?B#Yq,pJG&!sBDU.A(aK86J69m1-`DWnoq_Tw(n~DB(UHkKg*FRb*s!~vmgufSU.`ni.sFF,=EUGaE;V$v[/Aa)Z:h>>&zbok<#1QupE6GG6x`/%mI>K^t&F
-gtD>-&;OW.7wK^!OaL5m_SPtgY*We$0<Osu(eX/^7v_W?2OZ,b
(h$$tTtJs<ijItw.R3yit>Y6%xNSoqLc|geu.<8r-d^f3L8iJe}*1Fhumk3#bRo@3A?2$g6GiHsn{jq$1X0`93,X}kP_%qPJ)5CsON&==GT&3[[A#:CKIj*D|JRE2i}/Us
-0WodY!zoQ&vtQ:`?s>$F?<M2](hGEP*./9sXVJtFw9g1@JlQ_8Jgl6_EL4M8t)j*DJw@
0=qHfZ?tVv5WI4u<K_pc_Y0h>2kz#{4jnFdQ`H>C[P;X$(N(0ThJl7QvQr[r"wG(%=Hy(u
:0:vFD|N:L$EbRDmM9`xud3uN%gaN
Cm:3}!WRF3^,lg|Ct`xi{5r/N9q4V@eDld}JLg;$IC)nS-_<n3=knG09h+nW,Vb]CK7o!H$3!iJz)RUL,o6r69ES,Yj#m3G:S&1;V8D<
pY>vX*/A[#]VEZD4gJal:@fwk>;$&0Hajp>R(]QWb}5;';case"cs":return'"]^@)bTG2*U!(ie"]Vx!QJ7*QE8#v98o;2Ud?0`(
kj/`GoR.lLWq7mD]Kz=Sd`qw%Qe8"GG@dJyiIt=N.`_bJz&hV1klb=h^;`w[XbCyq9
S(.jd*1c,0H+Zq:Qf[Vv1=HWe7Dabc>S#vJ*%m_-iy!-rFgUSBrS4`7^98c?Z^2H,A^"e`fGUh#IIB,&`*g
/!)*l
l_Qn{B[n7waWF7N%5bGYNBNw*K50!r4VP0o?o>(fmw<^Fq>`]HdN(,QwG:5n-h[=F*3+W;hyA!?ixdDL6/ZT7C@mcM8Pzmp!&
8eUmPrnN8`0Y2,uJ%[$OHO]sHep`RN:f,+=acKFfI>xtt!#3WJ)ffmi#3:PK3&RwWsnY"694$K<*>DWVi%YhjCtcbX[nH*@h&SeB-Ufy
mIbE5q34rw.no|s!x)S$GmLl[wp_=Q1k%4ps+^iF
b=o<e-sD~V@7NjFM=nH][ntw<Olr*v9^[XrNP[1#tHt3@dmM_mgw4d/]!uFCwM#]xcoGZV*1zX@$E^spX3y5Y,f*yy<@_`B<,?=xp"G<)O/x7#_@;v8GvL*oOns3mv54<R*uuh1x>9UTVpc+KK~MFRj-"sAs>+wm>BfOxO~Fl,ikmBP_Rb/WixGR._#3~DV/f=/m@6_Bayio^6]Y,$p:{Te"+9?Ij_]PQ2tPFskmB0Uhe<mjOcl]:^oy9%aUdCZCEXiGPY):J(3[I*kh@$.L$ilAZ9@mEXc>Lob:Xe:2E*Gw;wQZ@<rs2LsDlUE)skVgw&QfuD="srXY@5:>."VF.ej3LS7UL<4`nr/EDM4p>Ay*at[xF;HIjKQ?.+zHD?)_:7Bby8[@U*dIHGA8*ReDTm%T5!;O$Z&01JN;p;f.cMm@8srT7f>y,QchYg};h-qu%)blFcbbqsyv<)|L5SB?VOD#OS"K8*:^![jv3yGS9@sTptdf_1{1p%KO@H6j.^ad_+v80Ogy9KZynrgMcCwvNx{@y51#?*N9?R&T5?gA>kWS8&94g"FkvN|*ht!K!ku/Zwc+g.$,=-II~r(iid[Z@rly>>3ESOj2J%Gh1&EwvWXOPl[22:;V#NQL"-38|X!Pzg)fnECN.H9c1+,*+l.fm#})(bNvz)N(>)mPdxve%XxF:?Gl?F&ZQLst}1D(-$9&%^QLbds#9P,3}3vcwN[h]t}
JM@D"jlyN6<0),-B.jiY:)2$Zm%b+Nqu$XgL#fe9oolxw#{8&4bhcBCSK83kT]o%9>ZF3j_=rt1=Cw#h^kRoOF&#|D.P;_>msFO26K8L5%pjLp[Z55.r@xRV;=2K=he1=_o,-lON9ayTbn+*$x#+B"rrrWg-:nYK/L{3jX{sC&Z#7I7GmL-@V;Gp-WO0@4}0O[1hP+[?:Pfx5(BLgCRL$^~KmV5$x
qR1Y&u:a)F9!
9k<ZA+2Ra?[yn^R]nufpct_}y}GQsb]FA;^(dl1n$aQaF|dT"R[-%(xeW~.j([LQT[xzt)T^-V/?in*F!6_cs`D[eBc6eq<5heTOTA5#N;J:"Ldf%?P/R4jX%$.aS75:-Y`j_H??l1nF*?J,^BDP*zRu%oVqbU.d.}#GUHk.Y>Ses:9:YPfWwWg8U!Un_dfr]34Q2v@DHoVncF/eLq
a<7dDZV%$Q3BQ1#m[&}8K_AUCT0Q.>q3O,}Cg^Vr71^pul47>Y!ZA
Fpc`wR3j,x"r6A8eW5^JOG5G"*_OQxM6n<S:a;HsQ8SjgP^dN)7m"&Qt;Wc)"G*Bi-M#gY6
2#Jo)aaj`5QmAU;":k:-yeWqzb"r"lVFwREHz$krREFop%""6!VI`^S&ks"ois|Dln^:*DIGa^aq~,svxISt^`yuEESxPbRqMja,gJo9i;*-CDNG&^yWm_f$$L=awY!,GdFlie^g}G[eMwRQr%#2}qg
03In}N4462g8Dxn[it&TT-w.62Yj0ZsE
>U4*K=3.cWm([
S]9_!)t02KZUUf-&>-B=0"
RNW5GrmaHOB"$SFt_.,!JjTF0hx?.qYRaDIe(#z;GuNj@K%u=p4N~3Q5VLf<;Te><u|Nx9c#(Iv%.*]I@0#vCcJb:h5Q?x4xC:%Z
`6iC"68#8!NI6-;X;N&|-`i+[["i3L]s&`BWl:%oYTb<(J#NDl%`O)`wkKgVu{@yk8&79zxa#5>O0_@mdY]mE"X)&}A}<Q:g(&9R"#os<"V"dukEbKW@N^lFkcdwvz-X_>_-fYEyM#Cl$$UH
rfp=XC2N<%T"4hpL$KfOm^*!(%5!3Pqei2Y)2T?226hIrIs54:})BxJM&ubd+cWY,9HxZ)3F[U[rPKB_IC0$jc>Fd;PJ&:5K=?
)9kP#ub62oG:<[yf^KX/J{srV8y1m}:OXR!LK.L0i-])iZ(CJr2AL;xA>$Kj`fK%BuN-G+-U1+fd+i1Uroug;MUatMJk9Mw{V*x-BW0=7}#4GT.:I1O<Q>a+O(O8?OE[P,[J/_tf[*V?8"T^30>VKk[NjvxWt$08Gi[*A[Dz:?hO@Cu__,I!U?NAM}VhGvH&Zt3?8Ti?-`qvWkJJD;u5mOFDINe1G!wNB&n7$Eo^XMkuK|pbJ7(7bCo}[-&~$58NBksHljwN`$LF_7giiIgU](sKp7H!"Un(![w$s`8tW[;1_i4G#e/9?,pJV,40fMA3iqE,O0:^L]KN>;UXXv:n3[Zz);mEQhch==:80djGbRHUoMd,2skh$;>[:n6We8ym%BUrX6+<+;]~,D!a(-(Sy_E<20@UTGMt%.UST4T;)YUe=CD<2|9$$?)d$6>!p~7XEps<)k1sCD>[Kn&q=IK8VkEe903/(}-SqK#!%WUpD"#-oQAWT$BoBP5^1X-QaJ8E<|UCa
_Ee{.I-]H_P,(oC-ozZg7%8!`:!y8wjzj-/JPFm7.4N0%p^m9G^<)+)iey^yVF;;1t2jo?H(/_S+yip3UT"+W;KG5hmPP|d~@ov"6}#bx!b@
{O^X*Hq7%tuOmUY:$uhjDKF2.!lbC>f6@;6G88~-@?8&qjz1sW3X04Yg?<y5@kpdo7:6&t?78&Fifme>0,s`RfGOAx)4>HkM$2/E;rBZIhBP
tl1MXM.p4):{wAxB?Bc^),CzTrPD*G_.Izr&#IV8!7/k8mFXK_</L

&v-`s>M.xg7x(Uhe^-<5=HNKX*2.|[DxXXRYWkQgxixqg$<N5"
[puVOP6k/4ZmfDg#/?gs[Eka;hQ]7$R#L%O`c>]n"C]Z)uEegjVO.alC
b[P7m?g#+TDPVaqj%uR2FE:yJl/1o;%]or[@9&a+;"6Lt#3:QA-<VJ1R=Zl:Zn`_{+&m@0b9qn8W$Gl$9Tm:=<F2{Ov
.,`"H[2.RKB+
Pk*]mWsBl$7EVb"hSpYo
GpLYR+,fIqY-_?-y.6WR:?R=na{q+v5`d]Ft(tpk0.
r,fU7yb8qSQyc7bnr,2(Hy,bW[8vBywH';case"da":return'%Zu@ibPD)/$4oh>#WX,qqfs@
U`Q;O21mN1tD6J*t0AP8I7tZm/^%hGQ8&Yb<_ry@Bt<~
e!Jg4pc$(j0bSL7K7WfAmBcQ,De,G8!/f
+co=e^,^,JN4TX/>He!KGv[O#^RisJo:EqEt$1+!UaFB
D5c&yJ6[,)M5l@*h_FSa4_2Yo_&#2S$ESh"#NE/_87#F",.%?$z)yQyZs=%:_5Lm]%Cs*1<Ex74Up7"Bh$6=5ufY?Qb6d<rlO2WnuAFj/W`?`(oHLaEZ9]3u+zO)qn::)WSNGIi[8S=NC
k:*:,4Id?6P[m-1oNyL!H%(-Q9O>B{cRsXBIImD,Z"2MTsyRD=mIC=)|C{^L,n
!]^kbkTs2S-Ab[
$/@msGA-
$GT%t#9s:AXfhQ"@7$4mdc=TIw0G8"Nb&t(vD$SY5=wF~=#_o8Yg4bMa2!d!X/I3TirT5`*0JB89LK
NB[zsUFW"jxwdy9$-)TXNqwT<*+hj2KR3P=&r?Wz;BZ])W4;Xm3qlLR2=wYNWPe2:5ZQC6Qh^J)+>ac>O3@4ha/f%4ZLBP<0/S0jNJJQH|d}e>[f--SO&
P@98*WAzn?^N)j[6%0Z&1>&~@".,T3PhWqE@66te2bWii.,yGmNURITbIqa"Lvv)
GH2Uw[pO:kn#vltA*(wC5duw4a)$6Y`N,,L))FMQbaj`SXxB7?fsredy@p|oSYcS,Jre0HbX/.bT~U|;vN==7o;PT&KvGP
k79aJBaQ$Ki6,)U~Xm`]f@e@7%1qtN*8*PO!"@C}kx!w5PwQN"*ABeC,-]u>5Z"k<4:@]0+b2pmNWp?@W`50e*y``vEKkPr..Jyg#q+Zi4Z7Li#L9Bt<W&&fuq+jlG274[R0Z4/)!Cz$=b-9?efS=rvvhI[$8+"2frV#!bOmSmddO7qoN~)U(M7-8nbr9J-m2b@+:`9{W"ChV6Xp8@(5/a:nS?7x+5#KoSp!Z
lksVn_Entt%99L`vDqaJ?hUnb*+N^}&o2_Q~xQHI6O6!%eZ6rr;qoL0#SY-8mB=dI7(4D3(cN(*!l[]<V/rUtz]Di~XkPzMR!+49WG,~iw_U6}KZ10Y`wIAf5,bA:<@AZQy1bAMJ/fM1/P%i2Z!7SuyTc0HyT<i~;0+RfZ`RB._1"+@+a=5A3G$>tNR*y9^1SEqtt5#If2d*b_3)^-uVb`B+)][x7y+-S+p2G;GPl>qYo(,yMNaiEk_/Ysqx9o.*=)8yH~($S70A1k5@+N6Lp
/
>]I}nw/;)@[Oj
"5#j[3H#mC.9HfJ>D`]*,E:`h=`$RE5Q]2o~T(qSQ8k]qZ9rGdIj6%]%3XwDvfBsCS]+&N8+"{&>gEbI;U7Ega*7-%4tS_X&T!lKMYc~,Ac+92W9LXg&Oo<;I.9@JXhkU3QOJt2z$VHiw0)&RV-0Qu=Kx`7a:4n_DT%4mNI+I1s2B-JUW~trf9c($8/J3ofyXt%8um7+-o.Kimfb&=MD(`9IB/k;ORizVKJZ%$7}HelT6VNcE.<=1%:Wov[P9n2hhTlI=6qH&bN}3y]X/MYoV"Pt$4ZA(FR9C!]G,z-~Xk(,f|.yDE>BT{HXhxI9(ME.[rrap4HT1tU+1Ufbwekh.ZQHT27YR{G1&+g5_<XSkhTu?-FcRRr"iZZ
xY`p)}u[0x3Vx&J8N.[5p.Cq[M4J$7hW${gh>bA-t[WTbCA06k87Nz-D1?h,uSC/Qa8?yI*=xA7![x+w?SB0N|>oc?*Q_b<y32:CGi2>N!d"!LlHD~Uj3e.4%?I*6rr9*$MvnBq,_P]r2/oG&:68s.Dpf4g*&A]W4,-5(#7Cnd:=RIk.Kt2{sc5u
DM+iH(&b*8iI,u)M7ES$Dix:ESv8P1ZiUAR=ZfJGMlZ%Q7+;WOSeD)
!oMGPfW$7*@8,R,wh`vpq~8N?J[1ev
AM+04Eu.|T7-W8M`K=>@]0$ks-l2(%{3YJ7_d*6BCl>"KB
8NfjW[asSjyquud7u&xD[xl:9/9AH]/2TAY)p_SQ-1;
)ixaVAyaeN(RMF?>h{484Mt==`+mR
D9&<!|<EKR?0=uMl*!Kj#ctQ6`$[;!J._/$f(U5S7O(2uyd4sZqg99(nZP%*D"B%(A*[-hI~"u_
H@?yFc#Tk
["##6]Q#(L@@PPw=P1F8`?i|*7To13
4m#Il=1f,B&wEZ)&R]ANs65>WPblWO{;X`L@F8h]@^|stC]Wx7L"<HFrkxbU%[bwk4$
qGJNDiz`K*&3W(13Q[:2V9vs]YrM0it#z(9;B6k`=7!Kf??9bQl$yN.BU!"!r`Mf!;71o9m&Ds"NRoqe
_(fnPhpSBK9n[sTJnTZucO8oYcXTpz(MQ<2NM11NL}W3*Ww.Rz5en{QK-!>LNh7PMf=d##A(BoVV<-8yReZp9TP(`-U
Wc"{*e`&AVws*KZIE13gj@69G.*_GbsfOw"l0}.Np|pE+?,"Ys
/`>]9[!)-#:5j1_x"(Cjh=z<"`)DD5j?RbS!j[EBwY@*&R(A,-l<dC:q87IPXcZFm`f,!dABIf.i?:-EFAhth%CCBT0Q__;$tez2.)zHd

5Zp
[DT}>lsGMb2*Iw?PeKN@5F(.0xFau$$=AsG3C:qRv/.(eA<A0_1!0x!6P|*>y]9acpHcFz.K5K8eJqhBYn:9bGM]"g>I-GndDwo3-Avu1y"o/CSq>)PCO!;hH;Uy@EJY2=HBe5MAvXL
v[7*bU1QoN.OVdaxOu0VcKhf5)Fm2a&
1u[fI~?7o#_0P_43fJ073{%`P_L}iYSIRaSajw_$9g2,yDjhSR]HQP
#hccvIak(?y++q&DKmKr-A]$P';case"de":return'.]^@iaPp=?SXcj"71"ECe.XDzdLYI7eT^:@1_4h2!3N.a<dA#7uU,B.xg2ju#nq`OjOo((#g87^A4hn6l"nO^k{v[Ef5YeILmspAZ=hlrqITi%&N!wj*^R!Y#[gYg@v
Ua!W@&)D#GF3mB}06yO5>-f_nEtnsvcI
ox*ty0&h]"P]g}j6=Q*qP+1a*1Cc8BZN/%P5MP`w>X++hj3/gU+R>94v]vfj0hSROOx!.O@PYz-ks^1"tsK<xbQ6J{PR_kFW"b:4[lOv%fi;j,`n`2gmg*D)7&Hymu)q5Yi:cqdit!#o(W=*e1N$tLlC!++3VA#e4
-q3#Q{O)g33^Hap<ZFmJqO)R1pb]1bR((d6!A{ng%$o<Vmr@IViWF=W
O68,(J/&M$b@nTykf#n8:4+4-VVLfChiRqxtJ|KYTpkw+ceJn3tvY$)u83opAV8j*uL~)M8&>PXVt5<TcJx*KSB@EKu1U3CdmWbB?dQOP<,&W<muhR1%k/ne*SV-rU[>h8R0^/&kxiDMF"lvcSrH(1aYuDiq`*V{U)Ma`J.wtfF7NmKG5<KTEaIMq
HpB;6U^}EzJ,ch(m],&-m*(5(!=C8:_,2|B4V6?zPmk_!ioYGr%RbG<rN}rKqwK@rHrZNYnDdnH6M,CiiG[#V5k"U:ed]5OhTwe(cF8s21&{mYD6`OW^f=bc2AN+dyj9>PC
78H">e]am]Wvr`R/jo,BQX5tGO.-@u(`O8*Jj0A0EcH62X**w,mV>~2GTc5A$e/~$B$rFNU/SIfvqd2vB,5X3o4=&UI+FINcHr/Y3K`5
L*uOMr<x/VFE")Vny5uvp`t
|1sNGLbF/!tXK"uT&1h5m8c8C"0?$Q/9y=-9H:Fi1.HnIc-3/6|Eh
yA18ObHaritx&ouRO

wk3e#}INWMa%$?]R&"_F
zFioR$+9B+fem:o+O"W2/G4L58_)w=Y^uN1,>?YMRFs9&k~2+b{Ts)-Y"9)e[j$W#u42+Taw:]PAr$nDB6.[iUa5u9bA-n*[R!VVg2H`-0K@9ySmneCa~27:?FK7NIbe<sv(+-s=7QVZOB0EDy|Gy0t)n"y6Qb}#&FDo4/&JV/uuB%cp,GPFm0J=laQhGK8q(x}+^J(T%K?M)@iy)VFvdZL-9HAZ6SLrDCY,lo0Wgl2E.TS&(a{F>x#5^CUSYtlsFN7erGw<=NG?LYrDTUk*["
CnVz/NGGC|,5lkQ*e
:U#C
VgX&8p@)`]?C,lh&{V,5MVvj>c<8{AoAWi<J&KdtWgP1
;+]
@dyKY|;10Hg$qh^U5
nY6Qz)e$IggRXUt#L+PtcZV{N{)_uf_@V"^k1k3lx(!YkyQv(70wg8pEj&E
QRO._&,81<n9+q2gXdB#_@J?ME5GLj$
eFEjxzk58SZV*+5BG%ei@yDFc|l#bp?/BIah+Nsk!Db,9gv1by2k8@4d?UpG.-F(&^CkYRgD4EjVh6VwU.V-HsX4_M`*^a-zji-iTx$Bl^MuZrhX"+lo"6!Z%Lc>>?UhdS8LTirW>
XNbzP>4vp0Y2uPQP5^-sP$,_9f1e/3)d!h){^<J(=mr?T3di=:d_Xj]zr&%axkpa<1k>AH(~*^;QYI+:V<%M.Gf7_n@O@[4<.E*Mk{3YenWon/GQF@h%;{tWF^8tH}&Bt~DJ`3sm?$KHJAp$3>fxByNpVUA>o+jE2`#q@luB"Evitpw+NrITHDnD(<eThFri=Z>@[O/Jcrqqo.W@e(?SS>,WO]aH>2[do?sPJ5W<lN_-3W2n
s,i_D1K=&dn#e;qOVZdGnpDbvE7l)Wku71Ji8<?e0wla9_7K8G#x6.-e7WQ;>vg"WyWFS<EQe`Y<i/"2U-dtoX!AP1KUk;)
];"sj.$#LeyN^]z!/O>q9+N6vDprAwQ]:E[X(/uIvIgC&=ma)j7v.d<g-g3d{B72B.e*{cyG6ddoskh+&7sJ*:)XBhAF4!aAyMmyS*]9BH:%5;3"Q4
dI%T^/1gOI^n^|r[@5Oyx|b!)"e6CNy"#P4i%0*CwtS@&d1$<%Bvl@B
T[N@862Pks9RV>pF;@)d+fw.
:KA[8b32:Y|C=U2b=s1WU#(3&ZX7Psw%qiG_olzKU:z8sYp1s`7B<.wNNeT4SFY.61!1"#okL8E"}e7C}=qt!#quw%iTa)}^^6N260
Yv^:kl<B?0ed:$>&9>`r9"
v42K`4=Hjj4uoOHHAG1CM]oTMLHIw)sA1082FMgnw-N)k<e;q67r.J3Q&)d`454xF4uLU*">6(&gG6WuzHaV+x3=<?G8c.ocnP&;jLI+|hQD[SKe{3[Vfsr
FTF-h-77AF1u{V^^4<4YYI~M>l#em^%C^7|QW=~#gEn8{:PjW]//Ot-tn3;$l;_-}P=0X?re,0hg{<om3V3<_G31|45"1X-fZ6rO!aRx})wvJa".HFSLNJm/(3F&UVsM]V:SQVbN;Szq.f{$`3zH!r~YaXtL0VO42*IPmh[(vRQ2v"^n5uL4Civ17W_p|^o>3f];,jppk0?Q*>s0?sq"_#[`OW/aS=iU9pb/"v<6{J-w*u92o)rV&X4$&:~,^Dk[+EdHv4?BPZtX+xVI%uSC0OlcsygtAr3<hucuwctE""=^|uZ%05gMr6/8>.+f@Y+=60"]];cY5L&FgV2K/88"S4HJz[H).7mulyD7BuAHXp(49"g1Z[:3!6YEI.bg5]]3>[^Plrq6%#)w5ZKFLWo@*Ba-IO[$OFF?&.e[-L_0UgD`z=-D1X5p5
:5sJzS$7.-`+fc1C:6rfkFRDg)gd{#[&O%LQjM(
,6T7@y%
_5U1t,n7tYj4@L88y-AqcxEk-`64HwvQ@H-Us"?wPtaF5"cO6<ne73|9}c[0AEQ.G>%H>/adoP9]zTX&_s.,
/n%b+yr>jD73mQQ:C
CG)ST3:B*4dCs!
)dwa#0XF?HAoX%
uk&RkvW,Qk;@dkaG#`D#o3%(c
DSaScm@.PcKK
tXxJ6
sfB"%t+:U:m^75^OLyHjC:e9mGC*>%_:?NxN8sOZ5`^bG!e<0Gze<9{S~9A"oPO5}6KIwhM6xtrsjH^WbFq7;>2Z+RsC:6nR2vuLLxz<1cICxH|[2qbtTIJg-u_037Ap$lg(BV@S/j[vZl<%y>7MR$M"&p[.~K;?^*ZZg:zbVg%sA-"N7w]
lT+`A-1%<2W+(kjg5v"EPZs>7-K`K(
fL_j>6SU?uQ&Dq[$%Le=86*K/z=L0Fga?/.7.9u}Z|^8fJ^)xdN&';case"et":return'.s`;C6KWB:&S>eMN*^f0?bJ8}3B7*-=EM^#2t9{puCf.]c=UXdeV,F|ve;47*56o%T4Q1KP<@/CZ"&3^zrd2Dyvu9LJCXY|sen.MadRO!&&GkPn.KJ(n1b7,
#B"]Q^K[SZ2gl*A{4-[v;!w^%n&?-%nJ/(?714:n]<&cO8:tU8b18Fb,chQFg]^8#wAkN7Cf
gTB0_E"tIfcw@7y"7Pj,x"B!T;ba{Y!QGBm7L0A4B:]n!rIN?+$aQ*d2ris1Sy4>-4Q1Rj]hEA#-WK,H00SF?A2#XI*=q=APm/<)Xe&w(Ac+"1k9VO3ZZfGYuKmAE0cjZm:/NOnykL_^!n1
@:WtH(DrNO5i<"OFJC10<fKd)PEmB"_oGhd4V_Fo
7!!{e8O~mP@q4VQ0%8)JdBYKaKREl)ktJ(nS!VG"0%>Z*i_JFzLb6@)!,(ntc_clR&9_A*i<Q/T-g,N0sG_cmcdVr2dIC8xFYuZv:XSrxYAQ$hpwZ=]myV1SVl]T^+cYS
VfgsVAbh"$I5bVerN3U|)20zCMEoTBZQ9)RzMfLVd0g]B3I#4gnt=/6)!}/|oN-ZDRD4c`*+Eil`iE@mD0k+c)jy?nH0_[0TDkr(7Cb,B8Hf;6FTRsR%(0Of!tS`+^]}PEU/6q``;Bez4Gme.@Z?M;S_cOladh=?Hl!zB(.u$8ig=TBs0e`W>+8{#_wuE>m&0~>oe!o9q!b:6n:;a^89_Fc+jP
bD.CvS$l]9q%%M8CMm%`V^+w]wM&h9,PQXs4bPV2%&_Sb7>>rRo(tajVO(!gvW<t(4U;qMN)L@sd%p)x-9T93Zsa=&!bxIop0UIloW&rCcu.*4fyUEKo+JySO][MGO5MTyOIi3Twcj./qK;TcE1e0c~VUn~h%_%JT:&B@a(t")h>qpk"B72"ea:/RRsl&f^kA5a"#R{l?&u7uM#_N$MPC81-d0)-$"bJFM4g3FZCz9N^M>|_R)K#%X~Z
.v@C@K.-.@6C@}(+XTlzp"m[%:]?EcR?k1$XR7o]ox9St=RpX}pLbSel6L<*-(
PZ|^_FL":[M3G@<ceIL(B39D;#9nc_e]D>zjx3]N-8(u+V"_EbE(g]I*naVpbBv?zY,B1fr1W9UawJlcD&N2;B~>#i]W&4<U;pM:cA_N^xW,/XD]/]>EL5(F/r9Dfpbq-+qds>eZc:yi&**/M)zYV-)/9w!5^20h7u-,=u4>=%L!>,J5*g.6,izNra]E2dL,<^W7)&z@lPVb^;8%0v7YP25q2b9l!91FQ
g5`o^;-!TP&6U$/^^53-VR#)Kx_PvJ,l8XKq`I/IfBYs.u!2L9VJ[
,g)o6T%SHmt)ZL$BQoqa@pslH:h?StGO>3XVRl`^2!>"H%]=pvZNOr:dx-D=>ESM(`3MK"rEzv1$4i(=gOS<tpyDZ^]n<yk$^)T6dJ.pkJ[^kZ9g!nxg:"oKcceS=sIw:RQvWt^m6./#>MI.NwTAm5e7l+?CqbNZ$2r&P4dF2iw*{;IH1&*?Z]E#*U$5[V6.6qz4QjNcbaR_zAGy?.S?M;`l2Ke7Q#}clJ5RPAS2s;l`,bquI5"OCr+=GL8Eod4VkcNvc8OEj2.p-f]3{i_GQH=X(%ZgSIpTd,G&su*:4&)oYT:J%:Ne~D;]vQplMh)smp*>[IwUW/h4f=xDoqVkCfTNctYq?.u^GYT
^6>L2!37ZgHIn;YD5qu:kc*ZfMfe%:;j8eb:Y&r"O-7P:pRq4/,t9>LGFQ7GctL`b`a?B?Dk)>"6r>D-$YvdlJ?pRY&@J&/&xW"P]f($eM~sFn/*7+1;f0{=?GYG)*5+JwzW`<}[$g^_%wU@#q6LEGD^CJF?z?HL|-N>7kOsSVG;V
x3JI[C=V9y+es^Q*Q5xE1e0BCk=ZbaW]S"EdRRa,Q;BtaG^NqO}?&vYt5e9!uLJDacS?xT1@au;62>7V+U`yr7ac+F|KL*Wpg[`q&`m@954-v^jP{;u%KF7PkBhpNhwwW[Tw@KtpntlWOu+o%Dyd7SQ_Z8"2%u2lN(,?g(ByHffYgilT.;z,&g@7r_<;k+yxlZGDs2Dr`X,nai&=L/"jb;VE*#d3Tu6EBdjFE1o!;-55YNnx2E{h~8
7^/#KHq(L~!n42]F&|Xg$R&cOwQZ^3IHt}&"hW%#t|X|FN;&7%*#>nv7%=?]uQ`<5DcR0gljG%x
6?bo%CF3e!-TOub*["K"<}8&ip-XFZB(`l*_avKc`=RXL(-E%ATL8EtKLxFO!Iw.jvtl?@:0eD,]-#D+H-&-3Tu]=ls|@O2JUZb^&7G&450M"exc$,kuN29RFB_#LN0jL8plhTy8v@0SSj8s`q_?6VGa?;n%)r:esa(JCrIW!!D2i3XS,^=]![k);.$ILnL!IJ=gbZ%rv"N&';case"es":return'.`G;BbpD9(nk|^~)HGqT/ou.z#jks"#*C&B[zb@Vrl%*pL/^Js}lp[,SXwERy8J,P<bo+E*g6yLtB
axj_{5H5RI=tFV]k?cBMM]qd%^.NfS"yueBwWR@D{N33pH~9F#4N:a8RKD&:?,chxHMoX_6^1_8]$h2u`%L,f"%;J:1&u,>67/&@]"931+qX]jL%xHi/gJa%z!A495|HIGLMd$A5++*GKt)<Rf5j@dBvyt[G0w4iTuz12U
Xyo+HSxOn4q>!?&#T?tpx~_f=(H=aujvyMrie3Vm2n!G9>t6Jodp<Cc]a`<tf7/~hiEKt{w98s"_^C=^!IZB!(]@KwV3vY?Vt}jg39"7q9u4?Fy?ex5qEq]vwmfw4TWEe+BqL!XEMou"NuTJU`"S3
`*.WLPx~Ly6zGaqv`[vIr$-B#5Punga$<}gEDW(#2/7N]EI3fO0|6Vrc,DaG+lSDE%dAa;4j>!1
Cb[I2be#5tUM:Zwg1~J|xj0,SKdGnH,*f/j^(:RAMJi}MHp>,Vm@hM8#)<]2E!!EvC!i@yMI$"8W%kIW9,y"7nqb7v`IyCIr72IC!4``&]

N+BaNI
FbnB}Zl@X(+__Tr.z5LWln">?4K)+h!qbnrTrUSN;16yy1x]}=(qt@
iJ/g7?Rq-$T/5C1*_4]]hl=)</=;T-0uF,VH]0O[y%MQ)0^(
Fgf7V$0[o;P`@3mRMG(2y=!?3+A,vp#&X,*+aJ#OfaSIW#Ud-59&AUtENDdWpvf11;),WpK<05oJ0)unl<uPlBp`bpfHKDQA5"r%,=(eU+rig8L&Hv
wfPo;k,.,Rb9)`J]$0sRsfspVy[|l"#vDR2o*GshGA5It."^"x,Pr4uQ:q7t+~Eu3;ldGSOz+&D~od;A+w!Ql!W[oru4Dn
"Q5g7M{*((C`#Rrmb!NlNZv8}(]]w$f
CJJ=~F+kvF3Sekt*p(au8GvMy*Z;[[(Z7oqpU?w&H>ptK<P.F?7X.gJh
JW^]3ju=
X8e:oQ-yd$;qo7&X"nM=eFR:5.0DpZOA$#e%H<?Y%%-R^9Rv2p^[/4IFG<
w
DOu=A-0Y;8BH
f"$:#.[fbCnXRrX#wa50vnWx`Xho?g%<$A)RP+@2
Y`cB.x^|N=sH.Hl,wf<4nO1[)busc=.8>%=Hx99*(]`BtJB6*Z7oq~$H9Vj^y"R)q4N&-i.GB^$JS64dx%`NvJc%u{nuO7ck0G!b=wMVs/D&9YhwtN7D@_=`Pl.yw+R"b=hrL]wa&:[s!8ca_M/
9>DFSX-aRg+FQ"0`:11~s65!>4+]($c(&LCDCH;}ubv{r:U(P~0d=A];*`=2biB`qgk(-9%]gpXTPMg4MiUDN"YH/Y;(p[Lj4,7]=9sEnFG%tL^Roha|i)c)0=]$iBNLq}[d<n?_O%"^H+o>,x.d_XOU=3HDypu?f8Ah-{&i
+`}=rgW
K6>o}Vhc`:XNb[q/}d!)_Zj^*Ami^C99(6),h8j(07@^qwe
NDdG}E[d-"8!1^[2&]Gh_8j7y!hUcS<p3"Uo{M(L5n_pYhL4qtzI@OC%_uRIC(.i2c$-nl`QrLaSrOa^PstAs.g)F&4y6S,:-m,jqhz=yr-,Fk#B"9yYi&5t6=!Wdg"U,LR5I<@m-8y8fO]$3W<c,MfOmIvMI.A#>*~Z*D=+05B8TtnHd+WNJE[m"r>$zdh"p_$1[@7Q9"&f_/x1Sl_;$6b08W/YLUF+QQUJ~e>v|b?ecU"imI6^f`,<dr;1/cCMzXPZXb@A)o-]
+z"Sdrl!C^*>pa`9lz&#jnBo"
<i+h*v,+PPRCJ/m~_265ENxabIV#B{vYpByVB,0[^>?thpS*fS+1]I4Yj{*g&a-+ND]|$xT<^c;]u0P#[GIouL="Q?p&_|Ybm%#3NAKX.cCvRVg9=?D6;*qPm)poaT?IHk?3]ZOEs["kO"%DW}VP+LDY@/BDJ/c$+f4|c4>KT=J.a>%&k0XVtEKAluP9I9X^X:X+28+WhOZJEJeihK^I3/NT&crXPHt<Rs@Dx&ULJ:pV=Akf3
"<Rd]^By1^/:#hdjoze$vH:)9h]l<~l|Yg9cL+?E;N_
dF
m"{FCMW0g]Y>6
h8|8j^W3`F-*9]DfP;]i0$vO}KlO:oh9Kew0wN9@orh`4WML*Yg$PEGOy@h(>^<;(vtM(dz^</Sv+Er.o.YKA]P(H!!Ev21);UW&hO~fMnQULH$9bg&n/u[e2JA^v:;$8]c]!1,"?jK$m:bP/qSmje3L~]ei2wZbyuCx-nhY*.[e:;l+~bgAh]j64:
f#]rm{nL=&=4UBOX<Ic~[qSw6r4cDm#e%o(lG;s`n
iSF(FdV4[@3:pOp1),)#T_#%IRL:uWa(:(9YD4#rp%-8Q8-Zp/H@&T>e$%0Wgz4aXxk$_$Ul-uI@0S88#hU^2ZdWwxI6@(,6khu1Gqft;`
#I9]DssC7(:?r4lP
dC-_/+6Mh+6.4jCK(|Tx8|CR&dGcDF?!(srKq8?2A:H&U-FcXO<^YmUv<A$&Bz:F(6Mtw#=Hfo<0.-`VmAvhQ~DC(BnDU9%VGv`,A%^St;mljY%IF1B&1SNqOq/[=!Q,]KoP]Z9C_2REIL&YN6PcOh(&`vLBVh(/)[#TJ4Mbf3Ayn:Du%Ii-Mpg"TFb"b@B6%`q9(O1(GR!*9mM*)c`XcJT%;Ys-@pt3Co^kv[,#/P5&MUVnBa=C^h/Hm{7m#>
sTs:&VjLUHh^s=5vl^S`pnyM81-A&>HX,w"$Z%PwmA8>*k)ey4|b,A*GNkgL,y-i;_sP%WTc2&PZEUXrPnb<.
31=5`,2P+@s>`F8llA,a#2FxfW7Z-k2^-%`_cLw@.I>LQch1NTMJM@8k!NdLMF:NOKcQl>S];0Q[0Iu&kP0>h2Col1W?pg<Youo4M86V0FkudAdq_s&,*^d
l;?>W=updDTOVx2F|jEp)qD]S@)v{MvQYdl^%,N9kJ,/$125:_|NP(fm!y}?F[:I6t26|@]>#g6Ju
2Q</RQ3cG[Rp{2z?=aihzyo$.>MFQ
}qHL~L}P[a4Wy1T)MJgtm]y,8?>-*Z
tY4dR8#OrFYK$2*<E.1U;?P.*_62dgF/S)h
:;fhZ{5)0+$cL+lmr:`QaV`E](/WQ=suG*Fh,?($[_e_E$dVjLxAZ[L|B6/vNLcV(5Q8VTBC]^-t"mg
")2;>U%Qo~;o/`$T:J+^EtI=k>,7+vr>3)faYJN&';case"fr":return'(Zu;C7oD)(nv}NNH/*=FN*^:Uo2DQ?a3+8H*hGJp*Vy;}:x7^jZG^7`(_u@Ry:i(?1v7OMu<.<+?XaSNI%IhjBprPgn?8c2=qKn1%)cJ_[{%z>%:l=*HS&x%4^Da3bMjy"8q|"Xp?3LUWH9;{R^p,wkA<yb<!%m/j4q;N6w_$H*-41f?qsbc>H?1RJ*du?WN}kPS|J
K
#::8^`HNX+RmsF`Eg8RiQNz%w{o@#u7Nd_YY6jx+pKhtF,9kJ:I|1;y"2G:hcT9+jyS~W_!g92[Rv+q]e|t?u0W:>xKWh6_de0CRAMY8;#dZ!g)?i%xSMU?3d%Y&l^Qv6hnr/w52r:;2-TNW8Xr0:7Kivn>8cMk#w4<OZ3OEC7[,c&fy1kJ:(YPEI!OH,?
cyhcFd%4}Y%x?_AK(TYy>U=!c?)va?U$O9!,1<H=kn>Ho(u@~0kkYt5n@Cc5S1a0eTY@7g>a7)dCjLvI~*!b~W/Je(a/aC)OIqt!=2YQN#q/s?^KXfDp)PPdVoX1[7RGT[PU/pDA_smGh
qKZhl,W_0u%t{P<1=Idp
?H]16]_q
?:;IVN|@Wm8z$E2NetPf3:kOx
nf}=!7<&fkCpfi2D1B}3W;2*0:o$*$3sxt]qg77]jlOiwfIh)(5e+nTIo^+Y1gp-z6F`34+PEj?,9hce.T}KIKt2u5u6(hZ(@(;Y"FRF@JU[@RCgPvoRz^:%nQMg(H6UV5BX9]^T#l/e}j,cF9J"5#O66G_duB`?+;C"5sbV%gU<=S<H9UDoB.>f!.?2f("Bb,sjq;N/ZL:cVguh,uoJDQ?"BG=@,RL0
^Xi^`wD.2;8);AN01ru/Me+q(|i6(U2hd8.pFl:>H@3/H
TQpjyq%xPt?=B"_(3m9@DXM~)_>G>N-L.qDoCdP(ZQ_g2@+I2[g#!F5X:5$cgUu+JLb)P:0]Fm3!JuV3-:>3Xw"YR*h.5$
eRwrW1u8:8n+H[UuG;Q)"dD6=C~s8OC(D3?wo1c8l4&]iZ3f<Cv[P7jmpRAS$$BScSbDYcO=4MN4e-)JH>yGYEH,t6
E&`3pE]zl**yEpGQ4y/H"O2:uDnP,lTzum)+HpaK;!8(IgPA+dU&2hq{*|=-&tG.C&OS`E>qA?u$y<5N;`L$Ov
%(!XH^P;_%B5m:yaQ57^MKMfu#o1Q0!&NY`V9>1Sdp,6OY?0W=?TP=bAq(xH
+^qwSFQHMF_H!]ATv)<d"fk[FI?8G;B"w`aw?mMeV#ps<_Dg7TQ4$7yf#l"w[@]EbP)[*+ksmqS2-<8e<;60!lZJu
O&[V]S,o&.K^kN:`o|qc.JGn.`pLQCG$K.E@z!RIl_f9-fI$BzkSHMXHG!y5p=/bSXn}3(bDZ<rV.,E>Uu"A!ICu^w_&)`bFjnNvKF7OU1*9^/(d1L%6iV:x_!oU+rrJq5XFG<$a.d;ss,*Kblh%;lPC8Ve
3PNc%7?X
l0L.s/=HIZokpE4cPhN=FTr1w=YoHv"e++jo|$_tXD@Lv;GvT?e&UjC$ON6AK3vYay}:)g~IDECE[ZjYb>odTn!ME)ajP(B
7ZumR@J9BoQ>4Gf?wd{<v<X"$abcoGtO;lcHzr
%&..-f^z;+#?dQ2<4PZi?iQ1x}%LWDg+nVf7Qg7>u]Ld#jFHci*FfwV;<Fg)wwk7O1v$E
?wr"a
meJTKR;YvcxE6Ak/7gfoFz3~3wj%S@(}FZ/BUaei
cKg02]bF0hG2ivJcWUjV2;q7Q+`vb4k/cVln_-5Q)Rg%(8vi]JxPdV038
f5GuX--71A(X}1Z$NeQHs:layB)@#m
E4Ak*vOVWY+XSYusH(xb-jwfYi-ybbVm)>>|.FU),*Suet<4]sG})wq?YkmgLPpdCjm`nkipjN*P
8>=Gd_#,t*}eQOUxP)r7:RJ5w7h%4f.&y/[&Mfuez@#vAaU>vTk&K4n]H9026a0C-w0eymifIgmy=2=6EN|a7WF<X_TJjrn`SBT`b:^hyHVFqV6051[b^1/4Ov`;2unm#>1=@G,m0H|%sE=-!Vh.rH&[9jJ`Q7&:FS{Ts$a@Q;r/@P*Ve%S-+n^c8C5O
@n6~u?3e"|E,(|Szte8eZ%TTv9haw^g;Ub_hCeE@rHog?Jxe">({.e_PD%[maEV<U"O;v.+(e7Y**F`}5R_W.o,<Dd0Fh/ZI*X@sPwYEw_`!F(Erb.[vZM$.<TmKV7%I!Cu;Lbxx%/YHetSYT7xnq*ym4Z7w!@(mdo@l2?NIwTimkSh8gOiEL)Z.Xz&`YG+"Ps(<V+L$w#IxGOn>;p
rd,$l>Ipw(ShgK0p4_FZDFS[L)^h-$-T-D-d630JgW)
5_w:o7rAb=}IGF$8EA%B0A"]fXLrR%_EI/I)C+qZ&-TR`7#p06a0mO0a_c6WF9BMzg)FgLD"&&Hvf,LhGGRq#TgBMPtr^Zs>wvzi_>53:!O14Hlm&Q%)_m9&$]j(G!u%=`aHNVN?Z_IZD[-)88[RV>UHj6?:6X2J-h<uc#Y:aw*g=V|G+<T!;Gsq{R11O9Qwu*4Jub)%k6;4=?43yjLFBVhlFOjyz.P%(P}Jl#3B3KKb:im@0sLhA<!S`gPrM?A($fSV#Ucx
jT!|gdoj!q8C&i9hGev2iS>{8XM-[w/Et48{UpIZP>)CIRkdP8x`YjPNy2&Ua)CWnKS{
y7~0%6RWWI~//.lD;IAu.A&/1f++v":(G>CsCF`/;LxO#=Ct{2_!p?{XN0&2%qhR<1jEsbjq9Ba8GD:ugtk5Y7pBylnU=R,k!Gv5AabV0qdh01AFw1E;SgLn<V9;AU0oTE0=3ePHc^do%]>2Fs[G}/;Q3hC)[q%nSTaD?U^m
[]V61?g*"|+pYZ2|t,GCGpUJZ/p^#pfYs.jB]ccl1N<a0**QVcWXR|xFU,7G@oU[@20:qFt9jFVN`pv$MNs,?}g?2=
[p:.Batus7n(%iH4:A:%FPD6qOn_GH
/Qln>mG4J]Vd;]5T1r<R%d(&%t-pTj[G1&3gL@,WnRN?I-!qkc2;>G9X76c/%8.$q9aO9|1ZT-BW%>[M;gl}n/CPkk1OK!@ZP4b3t%d
D%[&ZwM,vhczT`nWLe
;hc,(396@;c5y:]FyOol&g09B(>_1m78#:-cP_ae5y6T3E5)@xGaCd]qk<iZR<zB9G%m(%A8jqdq#s?Yp5T-=Vt/|vid8D&q`x$kb)1Y<mVybYpgf:7]Y"rEQ9t;yuu.u
u;0+eB8
^_wNUWQ
t#=/=2DXO<3Ii3S&8WwBz_wpHUe!(%@4eeztV$(';case"gl":return'$ZuF<6L.!/$*#fq"=^V(z3AuQg)k/Ga0tmsAOKAH:@^l]86^""m71Z~+.Mc9%
Z1r!gW/@WwUXQLB35M.A/h9coC{9+E1K5cv[rK96oo6+HneD"z%^WaAq8Rid(v.k$T%rh*$M"vyY*UAb^Iu*8"3h&:VxHxeX*$+0)2VH+H&o%rf`mCm"dU~l>!4aX6s
34}P?@q3CE{v`
gHhpBH3k`C9H@q%p3GcV@oOA^cWn^"Z&2y^*1z)^//zKIsQ"x=CwPu^_)C__HQ{_~l~^DXQMvuSV=L-*kPK:@HSaEkD+e?`h9$>:<0Ao,vL//7GPrnJN8Y$qJ#}^J<e6Hh)-(n#-5G_yWPc33PG74UGlCo.]l=Bi*tDgM0_>L.@"(bH]r`&V][uWGTD181q--3DWsn}I~)`M-JghxhKOL5?Q?ljHHw6@&xNs)$@U!71PWnj9J"@_r%UwGMNi8d1Kcxdm{Ww<[&,=i_qXv;>pJ&B>uHPa-cL_jun[G_.g.(TqSsf!e26ZmGW3[i[2_okV]+E_SK8va[ty])w:OLhf5`_bCM+j/=|)lt(<3#+Rbt+Yg?2wN9|H2/r[4L}OJ`^5O8dLED,h0P94-kqu+SG4IVVB|5R<MxJ"8H8!=Z.1T`+b7tG=nEmEJDeGOp[4k<Clg2&d2rZAVj.clz&1;I-)MC[A/f"+PC(>X%Ndz&;WEq-o}kp4S6R[O9z$fEK;jPv[0KphK>Pi~@JU+eYT+9]RGunm4b0K2Tm]fGQiS?h
sYm(*_CJPTv<ohHJEk4:0]fU5<tHSXKi31/g~Joug1am;R0d4OLq/
N;[)}1Wg.dFsNgX&rGu+*5~81PU"[Ug!1y;"z*b9JVhh9JL)cZuCTZJ;7emiK5oJHb%]64d3Pic$<7$XB[Q_<iTizpe#>N5Itp|./DM"<:lZZNKK-`Y+Ev5n_2kBtWAdXn{JgVjUg.d7<QxE|^RZr1ZF%Xk#pW~
PR3qRRV;@;3udbk-D(8Y[.iyA,WRnuO1Zra7]Zz$~?@%Z*;F6(lRhHLol5!d^O?gX+6^[8Q6>x2Lp/<YqW%"TqS<N;7RI%.DW?-q*=/XJed+^3y4cik
Kp&aOrVY-qQOv-y.iIkjNYzF@?GnJ@b6^vm/-@~!UdFuF
4jV=4XEiNSvoSGJ7y6DG>eYFr1,vu"b4/3c8qGZ*D6%X_)Y=MGYc
fyQ4CCy0^l3+9X:=Mrl%NSBq]B25%_<>*AG]CMnfR4tN+$?.H@.v,%CAd&w0SsY6L^De"74<u`a4_qxtt=$_/!.lq`OkU,Vvt:+G.Jv3w@goJ++)N:@GRD:|&)Q6w8dW]e(XDghuRjU($dejAyPgbcSG*:NB%LXbai3&e|DTfHuSm9EY^AZXAFRsM;Je0)^e2E./FD1~lS@U:.Lye3YTO/*2w
r5BGP3^>e*pyRzoPke;ppK$u
3]$C@I:/70n//KH((:HU$Z5_j^
nN6zf@SvJ:
8F?.5nff(i]+eqGDM>vrZ4?=i-)Fcb_igG}>H6^;5RCw1sB@[b~5$<*C4HL2[&eU2H{>jO2s#30q0h0^*!Jr]^rhk]@xb<wU-4O8]8984n,nA-Qu<%bZXQ`I;xjR
yXeJntwg!EI07&Cj8pxLh/V]uPC@b_qrj=0ad#!.-c#+rC

^n:jI&*tDzawXNvgu
y3^SQK40x)
9OM2?V|t#[i+SEe+MUnD=T8ZNT:MCV]-KbiCyu1rvNK?az&"Gb+4@`R)9)yOs8zeQvACgkuO12TWv&gRy,xFnYels+-w{;nW(Rg7VW@7XcrpJw}>Ul5y5bj3!EWBMl5sj>bj6IW1)KWg$ho2!Baa_PsQ7fv&/!/]3EhZoRN.J&y`!II22QgZjCUg02BnftJ<v@X6&W>1m!QXxI@UD*Dmv#_N-oFn#npGG)Mq%bcJIf?*ZGQ`j(56$1BqhZC1)Dtb0@1:?nA&CP4L}Bt!RB~U%r
_3iO!uJk3o%Tv7Qia>IwE}cZBiBaL?h<::r)i5P1gXW9
@uDcfi=&Rjc"a"8-rQs9"/N<z[?SduK8K!hsbJ[IY9vDc7F*^%L^q8UmV0rYpO*1_X.R/0gtzRoI*"!JA$Hyg%=rKk^R_#M
4N;pt-_K~6R>8x#IoEs6fA~G_?Jb*P1>,3)7&Hd(X=PGI<%s[yA:hk{N~lN*IbvsD=f1/r:PI$G0e4C9f%Ks)Z95MH~Jws/#@7&_`-R0!ee0%$A(Dw7i{&iH"Uf@Uu7wSgwF(i;gwWsuN)4Q~!>_}9<7Q9[Fw(BJE_*FDR&b1z![FjGAe[j5`&K*g1>jK,$9CB6i~!O?N_EMR,@x@[xa7TG,HQ!RVBw-+-epmGU?OKcf)J^Lwp>@Sf}1$INv#Hffj-KXUVOD|m/"~5C9@9v[$=W(1^aX;)oj6`kB55Kc"d}lT$YkDTQB,Ietp)c-b:Y](2HZ0RbU$_d^y@,]9kC_2O;?7rP9Z4*1o
+<G^FP8Yy;uOSLERue)kg=z5-fvvzmx;
1K*xk)e93"h(#1*!-2msAGDZ#0c%xcc~@]M
FR^g(ZX;av.-V65_4
2<^t-p
+YS"ShwGiHFX@j|jt*[&E[h9p/RZ5(xjhAStJdW*|B2eY9`FI]D-LG&YQ9&/1Uo-4IM9iLI_eW9[}LIy|-?ZOqg:8fD;@5NO!ATXCJT2]Ony&P
1ZJQ7--{b&dH2X[8dSUP=HNE2rU9;y4PTM`Fuf$)@R$SL[/l]Kt!fS5V`Dr~_RI]9MriIH&~({^hW]CbQY=l**Gxclt9S<9.*HG.S)aP[$(U7"8c5vjV`1J-^(,A2Nb@jb8Fi.]Pp%KV^c7Oip19D>dIuI]h*,<_u3,r<t0BnwGkU#@i]Dcsr9hXo`;5I<,I`(6~@3>Q!#%$CnJ}&ZFu=K#2XHJ
<
Gp+zCwh|9+j8lHYR`(ltn9^o>$7k;NKd*i0/%ep&rn9:CmaFdt-I()[Pyw=iDMWbn-97$@*b0%t6Ue_Jd]:=J.+&z$gEc6]5d2c)im::<}n(iEq/,0_IwqlwBigNScBGSKKZUhqXxR!@lDqN:zm4o?@g1t!y3FXBNU/zgI,dhoe3x/4n<!&/ytY2M]R)*{y--E$bV+UPIR!yqMy$F9R$>e_MguXTTU["u>c1j;%2#)RC+O8VTfU<yd:HT#<$jA-z"?WI4!0b
|M+5Ncwxm+}OH``0&>;k|o-';case"hr":return'$]^@ibTD)?T)nie"XSU+i+&3LCa)Y$N0^eA?l-zB+c!0>@m^N%c#De0:)S^xV0q/huB7`A$t)]iTg$1]^Qg]"nBa(mU"l$)l;uIG=f-5f3PG@A6@!3Wm=u.`lEaF)@zA<tUBMNqGL@!])9F/r7zU6ujA>H%o6)PK|Vm;}eLsrq4CkgUg,1%WQIz8.9f]_Jyc@WWP1d^rf-da%M%TU07Gs_zO6j"Lb4Zc`<n5RW/)o&u4v@}>vf}u{Uqt^cPNQ]@V~@)Er6pW0dY$"b&vDV1x$-6xP"]VAPqbzb/8dvdn]X~fv8rv#U-C7!?hf3Sb@F-fL17k:4Ir3blA7D"xpaGAidl*(o}EI3,k*6f%;nou1lSL
77Z%.GoQpwceu_(~*<7lyyN~-kX/"lkO_S5vuk[=ZMTxL1v^)Fd11{5Lvyve^Mv9-{lD8qs_Y_@Pj=6jU1#XAF:U`E^Qjw@",1fc$+v#:W#WI0@)
BOo)a161O+R!AT~"`>%nT&pXxx5V9?+na]]kt_iG(rxFJGbBae+#htlM).7f7<lKCFM?Z
Z%c$ajQM$lDx0C(yH]+dYU8B.oXcZ:Cq:;ePWdpL>,j!<okW(7u)CX+B.>0MR0/elS*eHaq%f>[NXbb(am!%fGpk2,_A
(MjYj3Y;`MHnK>0^C<>Afk/dI7cwPU
dCW"+3cO%lpShol=b72fm2D*>ow)1.aq*FS0{DNa*4}YdJwi];fsLW0!GErtbB?`A/FDn^xDjx&-"%B6C$f[WC1AHAZonjh4>aYEK0Do
3_.X%,;!o<oN5w3:PQW%dzs/?iuz8sYK;a/wXP"iYo=#dBAoYXj,c~^7$Rd4"I(o4&g`idmUuu8Qw9W9=BrCHLEGd]%z$+K?TV?gD+4!j694OaLO8yZ:45%a%SG:Us?nXE#bjuO<@Qs@I%[cm}q|`.BkR2u%r`djXl>FB11r@o"Yob6;QBOl<Kpm*@V@RC2_8w;rRrD
I*S}Ga6VRy7J$!R70c]uW}NzmPdL?vlpw,w-X}wn,)Sf]Mn61.o_G3rjJhnlWPLp1YXyH&:6!W17l"5h71L|s$53BXoXJ"nhVxt9-Y=(j_79u-yxFC"8gBg=&&S7)h-/5c`w<wGj!!iW`ZS..-S=eLPu/kdwp.NvIDMXHx,-H~[Br3q]9$?v:z(6RmR$)RUeHamwx8Cz
pwBg;>,.&G|1CU3wX9!IH,ST_GkCYS)Zn$1pe"hM6"f^Z<@Y2DYX:
BmG$TQWy-#TU`o
/Hi0_2._5<n-hnZHh
x8EE%J4~1<%yMzi|g1//OvLRv
#Q<
<Tqo7~0QS2gnSSxV:s*?Bk:L0#_YN`GI7z1w@/AD)2ppGh+e6c@.*OH3W:0>3-x47gHSy>tW,V?g:s`EB""BikyHL}m"yNy=8iP}h*XFMa#_&-Cp+~xV,^8cw~YVo#,
,l3+O:hxn**OEOH(&GQ@M&02`Ys7"$c[/g?l;.d$/H,n1h=-<IBaK:Cs)Xb#?GirJ=8I=7pTW4.:RwT24pUuc6Ed"$8
2yZ[VbB+QDGX[:)8-
a_TX;)y7h/ou%[a=!saEtMtc1zyVt|NZPa0(7,@jhu>64@.6O#x:*a^f*&UI@$K@VSgwnU"d-N$|@L4Uhh"22th&jja:N29w<sy%Zxo_n)D-@.hI#u":b^=)m_A#(w9tB@f;sILvx$WC=lirA`>~[$1
YM[!?"^5-
0^V"RrVi4yfJXq!WJN`U+(&!>}Z7g`Lm0)
q(M[@kcJ*;Rj9VR!8NfNU.Oq{;0kA"iGxdNcvQ.^^<(/LU(>@3<8&bz_@ub]+HEt#Njyrm(
,YOF1lQc#UU"+#IbX*Fj9)>xfDzMsw?X#(6`-htq7.ca?mqF3Ri$GxT>F?9mcPF&8/8$w;4Cs1ey<9l_qEv<?@q>KIw?yS#ym?0O9WmZ%vRQf&N@a<>^@2
gS[$CJme.q
>>qu0KQ*`5A0*EsAMPFNRlwf`_3x.4&ABqzQRE(Kt29
@J"Q7ElLwx58B^bk<:qtyVzO3>?+._[%jZXH=fC<SV/out!dg<Pi^OhOz3?"GWUf$uVoFE-AO/oqI#Jf%wk"E99E0B.Cc8CstnD)Vp^U81t7<Df.fC>^a8fjCpLu~6S2If}GNdoE=?btTy2Nq4D/5v&$)h}169_>RdkG~YzfWg2%RQG3_x:C5Kndu?&5EbQ2ZsbbeDtJ9W!@F.H[sqr?E<:bCeP,o=cLLYQN?Vs[x6thCPL^@ALt<Qyt$Z9>JbDQn3X)$UXz#gf&fKlVYyM5}^=k+uv0pfH
j,s(x9?`q=&xY@6DNVag-a2-.naV>:_w[KX+=Du+2U}p_*8X(A)bg,qRFy|2+X}]k"/J4393u#WM^wg.s0UnaJM*Sk*]jJ/t@?I<VwQHf;Fmh?;+yd-_hFJ/u.A/13(L@QV-FrNcGDnePe2bGQ)U74G7,+>/-5pJ:fpro%$(OA5V/KiE=d.`>Hxw.]in18NR<Qf0.ctWDcV.^=yk1(T$oJl1NAHOL:P0#Z
j7K@X=GBTp/Cjj&N3U23p[JhdEjM:B_k[y,!nN/vrNXnnQ)n<CLOf<GCw
Kj<KGmJT8Q0??-6O%GXebi7WyQ5#y`?1Agn8]NuSK1o3EZ<?:y`v6I#2F$po7W#)<IOkDPH>38L@ck6G<YK8`Za*WR+<`.0g<cc},K1yo*X@"+6Urg!
)gs1@-O^23opjw-OcJuaL~RRZPN~>~M2cpP)A%j.,yt+YYY&Rd^Za]$>"K:of:C64Nsfs<HI]uVcB[H|Or1&*I1Cwo,SLz<5e5b&nN!~)T("0Zgt%S"q9SxKQKN~-^jKTv-&ffac.nYkr_l$^G4oh~l[PdB{xT07T>BlHiqzMRJKgh;L?z<`Bi(aJO_<5h?F?Z.UPItl5W(y,?T/_4LPttl5/ck,ZWx{SM("<N:snFp,8`>*>`L:G%>"Vz99[,dro[y`OinK0BI`H3e9:Q[GN/<VP1Z0g;?cb49`tE.mlC;-WOoCGB/2:>P1;oL.QV<Ln0>vL1:[QUkJ%,;QpMqu1(x]!udIQvhkqWd3Y)lTZqH<hA&D!|Z{8}`B"p;&"68fd(G8[KLa&(*Xr|/#&^PF-U19-eAf1o!nwUR$N5>xC>L26PJP-@q>C9Y~B`*7x5C?`6L|D^_@VN,U!b[9<bd]cH.iXU:WG@K&+o"&!u->lo(|d0)Fl;kzZ@Y!+mY%t,Ld):oQm05$[mQ?O=oml9N<Ez?[VBF;N*yD)et.AW6]5yW0<jFw_{"km/R6^F,v/l]3f3I)+~p6tVdT';case"it":return'-]^@iaLZ;#>)ni`"0I=-CT[]s.I-#8@2wf|n>Rk)ZA@gE7`"cBtLSQ>pQ>u1YbVveTD<8D$]@sh_oK1h2?!cKSK#"S&Ef({w2KWy{(R#3E{tP+51DHkD&8^
x:Kd68G*gy0N,o.BX"vml!bGtH[Md%"rM%7D@>v,hm=l`-0mq&c>-@kV1BfdJu1S=o/(xcl&5kS.A#=`{Q1Q(=B^?a7`1E=WLAlim
`-k/e%u[B"7,K"+q/W-!s%mrn9vjo"MB.tR4wut-$,,H~"uU_fqo}Zb2F-Qt{E_h!"),@BIi^$J4.>UB=!@"7Xm#nC#5!?89<uaY0*zJ[M^aW_B0L,Eph`/[@[dEB[v:3n],/NFM>N~tuN^)7=cOA6>+Ji=VmCU2tsf?p1itS"Ph=]X#%30_M$Q$>!M&wXBIqy<R"=9eH.i_1X]-F]d
(8+2lbZv_se>",GF#=YeECwdZ!ne[g&=AqyG|t$
+6`X:<l;+Umi8h?6_S<z#P)"/C[R_/KINA<r4+I*=Fl[&yw#hWlX._+[~=Z):r"_UFf+da3ZW,I$^UGA#5v+05daL>7Ft^b%hFuGnbohjGNX-R
)XQ><*Xl$)2^j0iIt~-KGvy{B<+2=l--?PmlX1`{D{%.Q^rT/<BL:VCe.z22pu&ag{c3_X)#jZ1":W4xT*i@%FeP68-nNS"9@)7m[1Nr<b)GVP0-e@kDSJ0E0Y/8%<LLm,)ikM^B<m/,`$.^auxtw;7i>`x&<X%sZH/XD:wG9F%Ts}+rG/^P,G]VC(Udp}(jSxdfp&!4_HSH/E_S8F57d$s^U20q
X8DN?G@8L*Wop80aByAb80rnI&5H_`$$`qO
Op,]y_ybvr{&g?/!WH{044
fc#|MwnSdI,9-WlG#XkI4RUk-K`}iD3f<DuE!!!F)H:;bvS?^bglCgA}rR(Jpx3ZLV6[7.7:ZBd^SsYKQvS88>/hBDP1%>yVC~64,5j`L>nnn5"0RCx#]&`:#,]bBorv.`4|p6E$r)tDDp`nrnC"F39HqZ@trIw3FRg
uwKw^Ye"S,g9VR#u7JlN88peaVHs0.d*k21H9G-q2Q-ueE?l>$_KXYxD6`$?Ws+s40yV[h,fp"&@Ox0-<p
aVnA~u,cBwhD%8Lo_sHVyGwQz6(N8fQYD,{]YVWF=^7*|Lr1WUh#lCWN{U5um^{9DA)9dm$41r5Zs?P>Y+U&J6p)hWLM5C;m?YT$L@/_dl`xnPCy#Uy-2laoPMSOqBCJnC98&IAC
Ra3<km%m`MP<RTe@9uN2N<,[9.#gW6Tu0dJwz)*m_=aMxR[z<oE+NvX8b|sn(EIx/q%kl,q)^z]Kc;BbrFWki*i)_<?H8u(|t_c@K2[arn*{TYOElZF9)P*1NPTA#EH6p?e57?T:Dl.V=boq)ON89J%=ne:%%L1#d[Ya$HCbDZ)S,@n43>kiqS.~,+=AbNZJ]vsK
?.{<%U=Co
h9hH|UW@2g^7+H_J>$8i4$"fo+/S1jVQWsF(Vhkx:=h)b>?xdMpnx+hgUQsrg(7!g:r8b"$/N$fNbQO!:Rjx*Rd?,f3R.g<t<7@]j`V^8yt<TN+<F*{U4h~<ahImgrpn#G3.[;oQ6`WjBT,,8<CY;D.67/^gv-
n(36/GnB;|l-%:h:>G6+8@uP/P2$>-CEUNo]IE&utr!WanLV)TZ%<`*?4xGrH)Q~Ek&9UbX/;V3CZ`f]Y|%E02;AXbW&8<#pdIJyY-$?lNG}cB6`WF-bAciQNtgSXu4+jesgpsn!vL
N=4mC3-I>KMw|<KHBx0ImDC:MmbO7X6(?iPqKDZKH?;mw9ST$rs`e8MQ
u`]h3wbWm_kq_i*u%cb{p1f(wqgsgvf:JANR3)(!K)2K"EH6*H5$*^dA22w[P0sofJ0do?8K^Jk<2jKz.P[E,b
D:w=gvVwZym7Ybz%cTtfnrc32#^o~JpJ>Vq^KVPJh2Bxs;hPB6;UBD!MlTmf)H+VIRGW#-=sGj"fpC_"="QwceS!4D`9)o!aH%@%U<^(|AVSB:eAgG$IZ+v0btd01W:x2
s[
Th[uMj>c*Xm}>CK;3w]Is[TtZmgY8kR2ryt%YeB6)dbb>p
<YkEet<U"t~Y5we`B6`ax$7HFCgO&J>PyhV02UuQ$*pfB@hk^,!%1/>:oY+>j3j1*KDQ
4<-8qE.Cp|
5o9QoIX6U8~yr.t5y0T5Ay;o)1[m+Iln;demQf`Mzn;fx"2=65i*ipLyNG9r2+mhLgm8Gb~ZzNy+A[KD<r.AjiN/N&nviG=6YnOJs-:"ee@ynm6*sS$<4`)MEvqlTUeD=hPK}]YxR./Z%DhH4<z0hszU,vvXxY6`!):%P(nuyy}w0rC^~m5@":soA#J#cldsa[=@"`2k7:]AK1JmfBMWWE-;2s_e<
vrO?bjgP+087e(&iZFf-aIe4!eGZj3aGy`o5$G3K|76+Lz#;1ucyV<nYPw@6@t=mEx?#0IZ[bqmO^;=xEOGGon~rJyi@X9b,HZhqHbub:b_J19eMvMB3=<L31o5*4
^"a??F2@bB)<L>[D_-qR#4:OSg#/T7OhZs@r?y/oRq*-]H}x-JLW3QAWy1JW<h`mO&"*]9_R)xBIR/et=$zP2<GsT0BPYZ+#m[Y?-WX;,J);Yj%-~9;o9^E1sr.gjS8URQl^$q:uvx?ydm<HV^HJMn7
0?O0&HM>SI7hy0qe7055E^Qrqj9chr|2qs}sRq,SgKrs$*$aDA?kRD@hTP~niEnH3%"l).y.h@)c)uXryC:,Ko^r2l[by!vG9<TKn(XW~ekmW#x*YaC?E[K>:P:c(%c_ZJ|oslK6yKN"v1jEk9=4Ojn.}xd8$';case"lv":return'#s`@2;zpM*W%ti[]~JJ7`o07=X.UEsd>L`[1o$PY_"*)wvf#VV]"9Y2fpvgd
-r=ut`c>/`M:[iO4c7>]5i/NFg*KhLrOy.czLoJG^(EF:(?e0,LZ6AYA3uX9n>@(vM[wmf_ogyhjXB?PUt:ElZ:;0ec?c{myEfw*kkhC9/j*b2-x_]h_#3vrL5y-X85:/mtN)BgZwaMyZ`JxcZ6^S2RUWxrP4dJW0Cqdp3w2FN*Uh[=_I7E>CO7t[Xfmy|.
6c#9w|jTl(uQsjt.4y+SaBwsE$5AkL=&n=5Qfj,&f`r{=vjR7UhsFW`#,Sv;&RQw*!jx(Oo~cze.Ca%-dvP<xq[Wp9Z%axsW/9mTx8Uv.c!%&s78.?C5#Ms@q{rC95eBS.q]Nb^71*ps%dC|Qi3VQFmrjNe|7:?^D.e!x.S]xe[4Fri>.mOOlp)Y3~8U
{jrU5hb[n
p^=1l7
tppRJH>N`QM4X[/UoU*qjv6(n/;G6`J1sOYAmd$(GN5+^Tp`+Ql{/py&"]pRsX,dxo%<3![brrG9.3@+9nh;y-iBAO/X^X4(3"-[M=HC&es6h,@9;+!Ir-OVnpXMT!9{%WKP4t0#c<AEFnEnH<a$9$
yjyo^Wjim/s=iT&/$
n];wdcN%?g.ZC;?2s,)#LxrB0`SDTPdw*njp=MW=eA?%<<*Z2HxDXwX9T,Rb{:#@KDh,mB@RLs{<>#jI9?[wzdf!E:Vk~J7UPnb&/aW?PcXKkOzL>07l2Ny=S";)o=Kpw!qm#Puan6nxXxn+OP}&L!<Wf+Vkc"2/F6)N2.6bB`ZTEFoZ"qeg&$vy-.8@qAF<F1"$xT}3]]-cXXOu$k{`bhRcM8jXBKMM),g!/C9jjIuj/aJ"7fpH9m56t8rMz;|5otFa(B9=;YA
K,*.#oDe#S/v7F?,OKQ-<Du^t/;+p
co=JWg)@IIjR}Ly)E5?h#M54mwE.O%a%LgB,|=NczugVP5Xw."=69VbI7@8F~,
)DG
2"exw|oDhTJZ#[>}jW]9UPl7X|Bd:abPTw<#nB7T[25?15Q7Z9Wu9Au1GyuLVFp6n,(@-iY*Y5E}"BM3.P/]]COpiIsC
pq#YhfkrDu2;VFJL0m0OwXjVwy
w&uKp`B&&ognRM,C?YO3!|Bd48*co=C6!RbLWf`KgYj>[=ig%<;snE!v
sJFgDwsH]ILpYM
0%GT+qKHpqsw-HI=V`k4/=..`<bMV*R#HGy`$<y$s8*j,@1!63m4yYFbd7ew@
:J+2OBL=I%1p7SWlu&`,p1-w*B7k$g?LC>2_g`%>[!8h&4`?f^axZU@n:wf{(W6KJ"PUR,xdobnTh|4q
YlsOUYo%o"(Zp+]L/x"v+"wm6%E)iPu>DYuD6/;mp*zK=Yo5g,wT.9Yw3,T$]1w
T_[3pTc4Af2qmNOJrNv6[j}>-NZ>l?];LNSG^2;):S(F^XwSe!appV%E.#U^j3Z&/;N<Pc
bBJ)9Zj7-~mXa?pnp-,_^/O<.km8a.4n/ndFe%:C]EG(:1])Jp$,h,m@u>C)MZwAb9GBu|q=]
6Juq(
23>~6vKssWVEn^k8/6u_1t3?*%-Fe_7<3`R8rZq"@0W|YJ)WHG]:hMl
)qjvvvLnujw,h|7-r"G&822rwQrn*)2Co&c@dZC"/e!+Tz&hM{#{CM3U-Yu_TFdeK9Ee2("s;5nm
<t:[,
2/W;<-XKS!n6K_7DKi>cY#pSa4d7b]AI#F`m7El`TU+ThRt-!Mv"j@U34Tg3s8fYAe1fV^v*YK
b88C^fn-Mp>7+o&*%f><*6<Zy[%I*E-v,O4+T+*@7=.XZOD.M5M/!X)4_Oa3E>R6"KGC!|F:*c9}NOi:
|C*rQ/b7<v{a}Y37"(aiy%7&;^Ag%s
Ji
!si)>;TqCoX^pe_`|DFZstpHm4#Ayi_Wzk%kV_PNRF7
8ILxxbU9#J@q"
#ii0Hb7"-r}4BhbkuV-i6ug
{qTKrA0q^JEI|&P_GQai-1vsP)lHvl#t3GI9l-2i;P=+R7Zu=Pk:1eV[U%Gt01yFegDqB%3Ib0W[:+rcN4AFFqH8[aWDsdBu}ge.9/dsC#j$s9(9e5;i;n<9h[!J`Aq9%Cae;Az^8Nv0y=8z!J%$SoEg^T]hUh:cj@|1nfR6))Nnnp^8cE78:TbapNr]6].cLt!k?Yr*1dGuYr$
XYQGxBo;ZXzL@LA(6CN2~=8q`)s]0v2f=Y$lKTo`.7}uh]kF)F$BP,R*&pyVvu`<w=A<.J8r;.vyvbzx18k]yOnef&b&%MPb)yz2huSAFusaFWdwc^bcE+knYe]HImJ,Hk
K"=%:HAYi~k%cL#AdVdGaXG:Gi/!D)m
P]$
M7"/bxL{*MQfNK`c(t/wcM"I*tlo`pC`]LFPm^QJR]wM6P,x+&mVYY-n7J]M2q[Ic@JCf;>;gxYGB{xdKeVRPd#yU@qxcv_M2k;4MLu.fZ*p+@.F:kbSFI;>V2(b4u!6FO3-wCI[aPr4c*pbo"E<:;
O$&2-!oq]OnFJ1B:P@B/|N#pHxT2|uU:md!`z!g^74dU][#h^Tv4~kZ.m3K2:fy]es-
stq
CtSVE*5OxkdWhq%Q9-q2@3"]r5Cr7mS1qHF)0Hdh)3RA}4zhoFT.F=g?KQ3rtvd5
ZLJRjp_c6,N9_[6eN-jOlo"Xo2+"YxFehF5c%jBQj{MMAV
([VLGP|P%(cEM7jLd!6>(xc)Ot9)l$obol</?fQ=1mrc/a,?)10S88;dG`"3{Z@eDdV!$4E+dy]OUR>I9#>H/S0c:Y"FV18)
syI}oc`!v)d6Y{S/j48bbXM&]4[75iJ-tK4m%4ZSG9[CavOx4/Bo)9`T)![*Xb]%/]dSo7ZnXU8w
8u7A19?V.0~5Y@YK{aGK>^JXTN2iX[dZ|@ItX';case"lt":return'!s`5hbOZ+:$`sf{"*iW?PxtY?_9rj$-S@y,))4}g$8)&s$mM=u1<_$Ec&j:`56V^O.E_CI4uoX8]JR&=iB=(tb%w|
nqjh~6*4)`@e-eqEVHOLXk_$mSb"Px_Ks&;Y9kvj:@&fWbvU%yXm9R1VY?3JqpoXFY{r`<R[}xnNC:C@##}$/mAHJT]l]^2ruOJIIcHp{jkC7FD.mN),wUw"<w$IX(vxC-I?kX-eD2/z(nmaW6,%%r=+;C/C!X@<Eb?Z@2>U:AM
ruMav=S@e6hW
*$Omm]_$7h%@MMyQe:_T_ykqFa&M73L;hi`(ec22kF](jgxpR}I-`ZAnfPZK1y8o`wuP72u!oIw%(0q@Fp!|yakHm,IsdF[]OLAM]6h_Ke)mX&bEd^-6dv-XKPJz)yp.BNJzO[]fl$EAKQ=_;|e"jp>!+]IC;h;%R>`t
vP/w
A
#c>k`?sv^^nJ=I7Sp]PB@u0Bis&AX~sF=n9pG<k4W3c*[0U@;M6w,[)+Hm<Gn,T`"7GGwVAKcM5vN4+oo$D"i^C-;gI2CMiE-ft_X7n@?~F)kXeQ.~A
P(%fc].FiLWeK)JlXzn>Q+x^ccGYTok~Jrw4(;<WMmlL!"5m"1Y|&2R+X{G1jkB}i@eSN`ZN.A5I"_B!BK7!yw+nXu;Z^fs8VV`Zh-
npSnnQEhtSnwfgg!V$fD%bNA,Jc`:bv5pQ"e}O)a4bCuHm=
zp]AXN{3iciUV5@r7G
2v7IRHl>&.mXCg1CEv?!!cThuiZS+l5auCRnQ,uG&77865`jc^sA=agBnjC=;U={##+yPdo(a]SxEb6vi<PQBxXY71e%QA8&3Jm*Mq*%Y~B_r.-;vB)aQSf-rMQuBHyi"SiLfbV|RS=6M&bQo_ffnenFyawXB+R]h9s^uUu8>6>C^[`.O*c{+L"[m7J,=x:T8GLP84A[:Z;%yN.hRr5h(.5ky.c:"-y^%YJG+Xf3uBje7}6-2(m(]QW4j=^Wwp%}MGyRT<Q+G|i}?0&^;>7Z1;E7=L+*eY$p<scg7o+XZOifQ*>(VP<>^eQjYB(71xYE
dRzb_GmX!/I</H9q_tI3(H8CL)hpsSE3q$#mG3p3a>77R]7PTRyU?O{C?2W0rNlZK$a9LNM86832w_Q?j@IwG^
VYEvLf<sED
`4>9p*s%pB!">A!f.=ha&"koBp?>Wv0bt1@y~VVXwg_daB`#Oh&:r%ZT5<L%wp<9
>0;y$ESjIU4uWI"M+-(>CJ-U-Z(4trS~CP&1m?Y;-FXPa_5/&cIy?A)e(YCUJYR8BRpwPtiy>m
yx,_Gaz3e7I*!
d5JDL8~6[7~>op04fm)=(5U.qN6pvu3QgjQ7l*~&;u!$rAv$x>6g-Ewr@iFDTC@bjp:&b0np75Hi/,|LpC
cB10t^8Ik9)_nQsihM6fM[S)&Aqo"h,Ct<dZ04]Wx0/$8k%4!c1[E@:59a^r[slhYFEViS<qwFTy%I#%N/=~2!OHIsnq+$fi/T_q9Iu$q78qqO
/YRSB#!D=T1332itncw4btv*aT&!7OI+o9`#Zr_N*xX!U7q)RE5[];JX"9N8EN_Wmee;v("v7Eu.0,Tii,XRXEgiz#c1`Yl^V*[XDa6gjn4N^q8.HF)N;k/<CCt%f`de!(DE_,UY@&)b4H97JYosy9r$Q[D3BrS,:+Ucs77";uNGMg[":Hty/ZTFDkSxqO.UZKrdEXTh8nlR`4%^_$Sk.T6j;v^0#-NWs<fyjb|9GretdeVtR#s[9ubcE]"Fin9OvBV!yd!OiFTed2]EN#sRrCKBA1y8#%^O?<[ue]7)]e!ftQjV*hemmMA&bj]cCweZ+]6M!@4T=Mf8{Gm"aWilG%=^C8~*]xqe.5ro0WV@ny35ex.Z~W$VtDW3l90aS3|23I`>A<LG"iJ0t44Pcg}/Y&07b`,A>Or2iioT]w#ijZo1z(j4=YKhG6H7ih*^?<B(gV{2BxXQj;&D%FXFZ<zUYb.hx]{TQ=D3P;.*R$zD]Xu4#gIxjJGE-bGN5MjpkW22P3.Ny*}+hNyjKs!+_wSV<JU&AYGN/E75}7P
(^NUG3,s}X)h2TERQ+f5]cdf#&sM:@~W*+:ZM1&K0BC5ps!S>1Sn5miVbUpN7db:k5:Dcn5B,P.o3W?j"A`kED?0_k|CbD|wcJRPcjU?.W5lQQX<:IP@L-C5NR86Jem$0.HL(b;!w
{o_KvNs2}l+W@NV_[A}T/"RPU1sT=@rq}JO:l#LvmR]#dpo[Hv4jmR!#[K[WXAG?Nu"]xe6scLV4]]8.AQr^%Q!TyK)vT0Lc&K]Wt&cb0
3r`Lkt/pv^~%c;@jg1+cRFk;{WKl<wq9Z2M@!-U)<s$^cw{6_Nyr<y7P>r<KMQc
7+_/Y"HD!t(CCuRXp)4mI`
I|$DG:KN%$3?^%2dDiB2-%3_g7/}Wk@yu?k:EJY40k:0P&pTdWFt]8KW<uGJdNaI#_">,u!q';case"ro":return'%]^@iaQCv/$K,r#.J#_;>xd&l;?O#%+2~Kt,o_n6!4DrgF[?kp*m<tC6Q;#@,9[;,VQc;:h1HdO.1x5HW"lhmX7Ii5w=Kc!n]x
-!JHn#Fq:j&bl
d/P0"onVxR$:`etSwTV1y]S+cEK
_5G.l78Y4~SZmd^l6RMnrca&aBK|Ro0S2_cC3TCPZ7/fg
RcR]<2f
xD4;x(=n10c/pbwmMWITcUI~@%I=YL?VE@]Qt"p{7l.@fq*%!^1v]
SC3K=&w7HsMiBWW
Pm)tt?wjA`,8G,mCkSc;"*HXnml":-Rw!d]:$[J/=M(0cKpBBrOgYZFzpNG+Cfig:|H)h,xR7;>=U{rlF?e+j0o_Lk4|+Kx{>sgGs692D-cOvfZFG>M3j8.u"DM4FJFtSMQMIFa#-V96J37[^7u-(;"ab!?.gk2XvV]"7UUK._W.]BywKPmD/|O{""aA+7g]BB^&gM
#;]JrGfV:]YWpM7YmOW"v8{v(3WO]3wJmjj?:!ovl%[pd1,_23A<#2cR2j5GUKjnGoi
]C,kw^mpaLdEI52*.H>)G"*!3.8r|KF?mPJN8K5wDo1.8eA5f(yG~^[duK&8zpwM#>>s%ukf&Y@XIoI@8:@XIO*X2hf(i[w*9*ttr-g$^-f]|:~,][):Y/zm0iP.58<.D3t)5ErOk487&JVXDAp(gq@8X^&r!UB<@Cj4l<@c38np!*<,Zs[Fm+"pP^E89e
HQ*8%ZQ%:>jun*)F<o_D>d$KFq9#uPqM3k[]?Z+V(R$hnr//V`k*40ce_juo<zu^Si4K!r4
F(h{I,@X*6&{VQ==KHWQ)"N9JDS-tgM;)|qUq7yX`/Lg9;f=dhy-LoKIyi6x])JNwDkS.|QbWqt`r@3l3zymTGn9e(T&Tho-f?19L+X+^c^[LG;cqg?,#_Sg%(/qS}C$/;ib)CF=$vNOnu+mPJqPXE$wbqf(w/.|&8!j+h"v
+S6lXdBF)>IPG,cVw/@26Qc*R_$P-bL#sI~KA?NJMLAih2a+N)7Ciobq9^8hV&8x_v[4._AEI5QqvdU0Ekmx$dD=U/;,TDg?X>1j@
H-)%A"O:Iqr@p#}-yct*QmJ+:VVsW,FesY4`
L>ykRa&l&6T.h5e3&d)XrabU3C3K=)n6mz5NWLis/.m|]3kjNT@=4(H2(;#8G%FRV!+lG4tU(b:7(5XM"h/e^Bp/X9-s]_Z)53n>M:-J%GVy/~jU^]EJ_a:%=Vp&V0o[Z{Y",
A4d-/K55f:3Fd*8YD:28s>-%9A,LCZ(/j`y5hs`NMtA:8XR8mtI#w3<!onURP7j=/;Z8l^&:72O-vv;xcO2X2>$3%#Bddia,L.%Xs)%d4^<%Sm3,SZeXhfd;&|]BFOpdV
i`L#`zP2
!ys"~UlhZ9<W`IvG_KX<,s451j:=:voe[-1t";s?VY6hMHCif?a247xyXsf.>^P>0;cQ"SGS<fX60%f%43cHi[BV-JFY!I~;U<X&y01<NT*?@[XfYRnOV#[9#+5P;Q:D1G+uh*k4WSAM{]UT,"BX*ls>a
mJ"O3ZnGe4qoe
vv)-;Bd&B.d<.XBC1D,h>_U`3l"+ftG,GVUgH5<A[5oCF3aVC9]kTvug{NZxlw1XWC@2XduRD_FE};g8Jl*<=/[$FR-]F);^?f}Gv8Ptpm9F8Qd&CPH!y%ILU"8lkuF;X>=?^v0IwI&J8(Ui8#^KbcI/CU4/-s;H|077FschR*?jZGdi?=x_&Dots6Q<^CmKBr&_B8fQAkHEaHCTB^2L/][%-2s,=S%r4bpQ+)7b+P+/8;=9ie*KX[+@hrFOv*l,mv5Zj>~=v:?_-;W<U!*?]JwS{9*`DDI9_s-2wgR_VM/Jl*E[L3t"o!-)}@2ikB{?t>68^g#4gS55S.6(85`qZLimpT^]7*odm9o;EZ.KdZKTYLr#-)*4<S1E=VF*nf1Ti!
V?
9"8uzrdoA]Z>VQWjZjyM!dl,;3V$K*F+$a~sW5(,|.>$6BDvkNAd*;N
6mPU5[NekNF,)u6cbEw9)0.[J/M=="80Q_B_:QkTnK?v:@iGqghgH8bF}r#mEk`3Wik_x*Z_.j8=7o-$9WrbJWy0a9IS>08Qx$nB1+782M(mh0D2F)s6_
)#dQcMZp0!wDu=vWNsU"B@[;N:(BB
SQPBFQx65V6]M]l9/Zq,)mAA/0B_QA;#%/!`?4:)QH2:

G=nYC6RLvl[-Nnp9lWwg@MWjrno9@O;,"2Ipl@yXQiHfJ
do2q"kfZjCj!",^C^dLf#EOuM7P
H6lUr6k]xU9B_8|X#8xk2iO5D1m3]W!!yI<6
K43iP31a1A!3BiRTd?n/msx)(H#1"A1447VnO<&F`~^r#8gVM(g5kd.:nL^L7`:d]_LB%BQ7]zNtCT?[HpFhc)rw-R!i[kwtjK.|Kx5BTV0)I:Y):sN_WT$isCez5]w7m-IZ4Yq3;XZUDUF9AU5MbC_MXeZb(@D|`FwXdO
n/rvK`=MOg*WOPq$v2{v}FXQb&L;[%h8)eDa%3/qk@t]6<x4O-3Bm9*t$/9PB[$%asn!C`yJ4ivs@UELj>n;leN7U
e0TM:H6G"
`?.[i^&u7C4G[#IvxRE[/@O9CL=iNPsno1MK8WK;C-[
$wq_IdSG?.>:uxWy~Urn8=>x^JY7"D1*A!`TbA%%C;nLy%823-,1"%3S90"+Bd0aX`nq#9gz)=HmhY3f&
NWE1X[_sE:o%dX3GxBjAN=vbMF|-g<_54fYumR,<s2)_NJB!2*xE;JB
1mYUd:
f$TpY}(~&,Kd$|[l0@KBQ?>v_?,,ia=!]b`j.*=/e79!;)9Vi1=HKk^SiGJF7-BlUO[C
PP7VO^(MD%%mwf!1_P/gLTA$G4s/+%9d:NoaSP%9n=Lp*AGY7Ri6=MY2pUb5)eC9;ARrEj&i0xb@shaDEbYXn<f5~L8F%Y~pxXUCF5gq^JOj?l2QF6Jm+c6okGKm{X+]HP+-5(!)}_@VlD_6l/b>RK#9;euMNv,(r`YL7mh5$daJg)Cq(4[_)4fweD48c?}JAn3&HX9R`!DRT9r3@p=:.Ry5nDg`%lu
D1y1XGiO-TN9,1:51@6vb3Cw^4zUCpIIHn1B#XfL_bcT86a17T>j-GvJ;E3h3
+Td.vr}@_X(P}"c6sYPsr
9"8A*;|[-H(_RsLRgh&_CH[`b`Fo>a7AQAd"^xI+2mr]5&Pk@DK0%2SUWmwsOL[AfSdE[Y-nI<7Eu+^yj5L+.AEcW[u$Vl1-w>mS9n_wn=E*kL/.VBgTK*?Wl^?7:0*ayY&%=&5H8KK%PFue4S+k<,;
)m)_0H46o0Cenb1IG8ZIC<hZ|_wADIr/(r2Z.rqZ`;cjC]"8WxZO2v=R5VPM0">Y{>piOK?.sKWs4x
';case"hu":return'!Zu:_cs.G1JuEnHdD^WK`qo[Mq$nH71?O!HH_hC:{[PS]CP$wI/M#I&I}:qP59`>&)XC)/k3eo$.Dg@b@J@bFvsX]fQ"jF
g0kW
fc40kWYlX"E,k!"a-c>JMstms0HQ,y|bKN~2~ZBM"L26UlV$kM_,3*GwhBZf8$5r`b[5]i.Zy[OlB@W=/)kCjBn+z3TH^!hc3giv~*7Z_WAP$eB<6j>qh11+@SZY(+
IqM5P:X*<avk^r]2:=tKHKz)r|(#N9(N2J_Tc{COvK$QGlGjK:T!En>{f[w
caUAsvm}WJbP,l^#l~Ea<RYzD(t$F6kv+#DV[B"F)#cIq%GE-m,VxH)6LUfxod#;BH,=9
1xw!qva?L]R&Pb7+X@sH`eF=EgBOMslPkx@iXsJBM{JG^21=a<f5LJXkmu=Tf$Xq<~l^px69HObiU<45!!so^5CgT5hoWO>H$uywZHqnb@,1y#K&^QDL/K?}j]U*FvC(f!J+Mxg:B`d^_=_wq?X1t3UtPr9P08]rquNqF~NEU4G"=Qu:`<J,jX;~jW_v4fZN[y&8O*90
kM=T"+`b[kZsfRg$Uw7CeGsFg5BLz6kV}HEf.dSc:?:BO4WK=Ima15(>XrDKZON)NCwa?MU>TQz34.@W.7#3QPVm$W|HKB)R!ZpP8/Qk;4C`IQ`KnW<q:3{1CaU8$B
NhDmq+]^b|dg=L%V0J%O[KBQogG!.!@;J?b*+cyuf$KA^DC-POR}[X1bd5qPi=A4Ah_G)?&Y:S<eLmUJJMulnN?Ol=r7;QR7:DLaWLVKX~D.7iG!;lCHFc4ExZ<Y?*2*xw_Lob6GZQ828Y)g1=rym|;OiL7s8l5$Z)e
p9Y<>QOOnA&MF@s"CVdAa}O9^W[$$$r*6wcd;*Y:^!y5CYOA-;g%Z)N@1aZm
e1xNLvaEhckh(a:$h:bUzk|FU
R?O5AoNR,PMCSD,3{4t%Ms}r^3-"J%C:+7PK*^K?uM8a3768s"%0T-(SL_%T
DPYCs*kT=msL1dUrbR35I.@IY`iKDFna[HdBUssG>V@|j@^e[IV:`WGraf)AiG#L`+0)MqjsT2B7./,W^b:A[$c!%966Uyo2bA1m2.]hAO+4wr5K*YaY4u:olp@x-.
]GD7(4s5u(Q0sr%J4&9l
yJR%N0bH.*tHNY2S
yklRee[i->sgd-%y$
v`pc(CPuiUXlLP=N;8Z$LEtW&@O4C)Oit?#p*rjePYaKfo*U5;2dV!ce/BKL:J8$5%NbP;t_PviRTOQDA*=eIAQc~3W!]<@sty+<oetlj1{O.sZY,]dN!6EFoLIS{$Z*>lE=["rgV1?4=U_m[l>U9KW!r"[rXT
y**~EMcVsBui#%Cd".vTtSH%ntT~i(g%=YY_=k26b$o@uDtlO.xYR{xYl{t-3WlK3"gr1[lk@ou}KKyd1vV>Q}`le/n:NVi<C}M$rXC@xN0&)uB|/M1=q;rZMLF>-CL*Trd^l}];StA|t!%p$Jh(aTT|m!>yBZ/46#<v/l,E"Xt3n]:[SOH#P}:X^_D0BTWVuQG7g{2J5pvr_UN#h)mP<6vYy`R<#).SkxBiKJ&FmXvlePxOh3fs)]-feF_C>^@|VJgT<"FR47E-cA&g!.Z9^7Q,1R/`L?)6oR0{Fv$,TjC79-&rj$l.JQ8/Q"4c-(0i#1xh)`qwed(%44.?Ur
qx@:6o&MajZO:FE8G$Eiy6sh%*pj
jMYKfR1
KH+8n
v$x)t1FvMSOCg7"R*$e<]#23c:7"Sw5L#QXeA3"*N7u?.bHwmFlcw"PK8Qf.kvje9rWK-}d=VJd09d0m.q2TnI^p5;&d4FeA:}n
PteCUOpe/cHXwrT~5YFWGwe"beMD##LIXZUEu=pkD6iUCTd3T%%CNx
Ny=O#"29wU>4k"r$8qK[Y,e-a*gF"qSSq`R^lJ1x$OUk_2
_]#+xdoU]20KpaP9WMB{%lRJx&Y@Oo"=R
h.79:w.T=ajNndwyd!y//4H?FUL+e.gv-Ji_KdaXfL?:a6Q3>wJZjMb<M82z?~Nq^cEk#>
|PP/XDR2*"_;"TCq.5*V]q|5|aJ&K7.z)?n:1xOFKSlCo`s<Q8wGWt`X0R4>A?e)y,Xdbc1X]&Yw<f
oJq1>60G_Ao8f@@N]CS7TZo@G)Rm3CE]Oypi4./a/u[,i#ujWfZS3#4C/u6?jn1`.K)O1m[y(m8@K(rS%A46W4P>$Kr"4DdBCW@>k~2[<F>i35`suNTybN#hGX:IFs.GUJ_nnsUc@.&#YbiI[i]%7K@EQ@#i0;i&Yz0x"ohRTY%@d:f)f}Y<3un6e;n3ScB1vk#mU@FT+7X8[vrS3R3~-&T
o{A)>Xm6rS4N`0J"a},8Z3f2"/j[H)1(y&/EkZD
:($;g7bYm*
BsQrLF?*(x%]$Y<=E_IAj09_Tx_Prbf6&$N8u`!tY6|d,DX:#VK-pNc]<YE/%rx%?dAWw*zOo5=XtA%*DYQ<23T9|$7Hch&^j-^DX5_$^kE<VT].IwEa+GZ7F!yAo9M#}/,&BcBCZOODK_71w%ME)kX#GXZ]Ny{%%)$39OKA%av_%8{4(/}]A$#WeN3FUvHTza^v?Mi"+&:wIxKpy;%n-&;K,QCZL<rthsl8Qo((1hM5;n_MPh+6%m%]Aa&/07&eTZ*V)[Xf^5LNTO68^tSxA>f^e5
CxW[RpSqlIGgdY<U$S&>`;+^"d&lYD4>GHXJPGSr3eUH@z1wHV5Uf1@dm(n_!~);v(J+="vRmZSO@CYdlaKdf4y:iH0/yF6=iVEq%7F@+oit4IX|)oOgU.U?^?JZRCSx(c[7h]"P;xmjTeHP
PUGjnb)mQ&I%gl$DE>,PxLpWLQzBm:qZSCHC~l`MS;%0h15j|SM=d4H[Y3~k4BX_2"AF*(-rQD}49kNQ
/,KVRVp8_b.d*O)t6nZMV:I|COJ=-:"`..I0L.:lGa*<W"o~[K1+1VozJ?:d:OF7*5qO.B(TQ9KXN=[AxpLr?tKzUp?3FTQq><E`Y).L2u0uwZW>9V/^)2$}s&Fi
t6(d&L+-y6lr},%)NVd4@,n>NQ/wO,WxK
0+9`[BtN6o.P?$q-@FP0p#Zti-J3UDccQ-hm8#nj]+n@]"
0+_9IBciYC+(<`inmHePn7E:/UCi6-0K4csa9KPv$15Dl;$7`_E8TJf5"myGgYeKhu+2L18Jx5
u.*Ru7$m8n-;d1H
77_M@Sh6jG%e-QnpWgHgW]{<pBcZ3
5h~L2J?S}?g$<i^ub/5IUuG+
Waq0%DQj1lSp1s[^oL9`rC%nDOGHNDI7N~dBT^eS4
`Mj?sbJ3u.4(ZttP5?D)7O1/8iZD@[[v+yKF"zv1yZ$+hou&US8I=usoNdHLJlcYg:t%c1TqkOh%?DjkBzxQX["UwU+
1F,aNn&1woV:pXwmqY"Yp%**jFV~+6,?JMI&cacZc@io(@Uk_^^YiM7z!1';case"nl":return'(ZuALbOZ+$c,/Y9"uv"[38CIa?zYW"pUa:!?z#4k$#NA1DP(9@q,xZ.-Vu?MxW|510gF58$-9e!t9t5WT5aKYB[="FkP,dx+6aw2IwS0>3Dr"#0B/LBO-vs1(d[>^EIKg$s33Z9Vp]sq%.+HHt,lNjhklTEd7:2Sx2cx/;)0UhQa,J3f<Ik8=L
L83|m}KF>y;l+d^u5_&a#4dIX{k@B~LbeJ[8.Av$Ar-PiuMpB=VibCn4t9Hd.n9;R#n!l@`yqT=#wXLqT>Zp>@cQYP^[2~2,nMYyo]C1w~f~R2?+v=bub^5fn",M5kM;
GvOXoWJ?Yoqj3Q8>}jO.!=9b>H"qe+>/dO4?hsgsKx).kMIXWsI4{:IO8r=m&7at+<8#ghmZFyU;aRy6jbc%VXQ>|kX#@Z:%7W_n]/C>
fy$Qa-Or)iLX;1%gHhA9ieRF.occU@s2mkfeSVGmdX`eWKqIP%VOQ<!]4q
/+YkbUISu$:&kQo)Kpwe[9NPN$c42b6rW@-rUSn4kR!_8+%PX&{]|*~_&-Tx,4l<G7+UqYk]fl<u9K[$2xQ7_R<D]c8Lr"1D*PN.88q(04T1;)Y:ommtg+[a&(ymp;"1?4M+L-fquXPKG,zN&r%k=m.9-LJ*GOsS<?QsWqpS|WrIz[@.]O|46tZ/r5`M%rIuL)Tk~JswoR/HUc,R=)tMTH-&J5Tly+WGig@`/oQ9:9-<x,(VuiTxY_JoZ)5YFtU8-X@c,KCM^S(=UWBNL-v&=qX/Mlo%wFD$gej-p]9U3%j]v*hOVaX9cNHSlb^cR@bjT*;9"vKAIPTQY./%M]}A2RMGKQh;WK}aztwP~jB2G9HmVh(G@GKwzmw.yKAdn2K,@$hxH1=Xs*g6`Mrv+H^!Lv|P5okTjv^weH,PVZnP)+Uh|399;O|Oe*x_*rxcV:vVgg34w;IP,0Hp2#mS2hL-=O=ggcM7(_D*eb1,,Bu)(P2l]X>)AJB;M"L.hh_JC=678j9ps2Q8V9889-[@2f{f{0$)Ge2Y}97wC&&<>umcdGFh7mkDeS*i,L
jf`YodRl=$,:BHTF/WYiT1<4N&W}&6xa$U)}GwP|y),BPXiHoXs&pBK
"x7St,+?aSQ44W
("b8I>a`o2MXwYQ/(ETHAdnmy4(k5ls2njr[}j17H;Nf;$c-CP
T_L/PHx`N81kU7iFcKMA?qk)*X:.J]iQ/Bz))81.pG/#Nzve@1"<>]d+[@?>I$J@@`jtq{e[PXJ?YBU6Ycg:Jse"$lx0)nSW#nQ>KLqo_go>0>[y?H,R@j^Z^&f~9~o_2_CVu@LuA]r@x$^vSNaXg5:o`d^s*TCTXI>;1=Wbp"r_]@=9v4RKIhlZkA6.=7
50Ab9T1FPNvoPJ-MK?XV9FVXz
LON<@-2K)tZ$A2=W:@oi$J#=[Gj)}h
]TO.gza;SEf0;kH6%z_lRq,*sdgiCd%nAmU}_<6~WR%+ENgb9Y1~G"T241Dx!fKmn,)g
YSoNH+:h&?3(F90RRv,u{1We{(D"%rSEWLM3?@
&lE>;%/j=VI2
7BGrHmAR5=N>gcO!c:&vQ*"p^_A:[G9xI-e0=XU1/.&V(&xT*D72.ehw*yIu_RNtN)>;%c(<ggzZ|XLEEt7#}EN`!?*P^DythgJ.)/FhAjh=C>VhWgKa(O3Y3c#pG:V&3`Ui7J3R60qC15/>T6P=LSwUP_fo@?}OodB^4ba,S9i35.p1dx2k1+>phksF(Hw!^vC+lsPO;Cy;ikN-zVvUqqFS1N[m)mJ&ni`+>vogoIh__otW|Z}7qPu
HehmNq-J7Z441Dgab:5fan_T(9*H:Bj+XS6*&ouC2S]($Ya?y5GG}>13&w(RJ
5SQH[s2,nmlbqs5y
$a>d^yg5>p.y#q1M]-(9EJ#6>?S$+779WPpP5"ln^l_!]:]:Nogky->)#toyDVFVCKQ169qbx[w88`ENKZyf#iNrIf/-pl.<,]Ykth*F7NdH;QX*i)[.gHY@xoV&6_A!
CVK`k"S8
pVMwt2S`i9[0;kmxb_B-hTNe@~/3T$$
K1f"8FA@kCd)GfYOPy5nmY.He6y[9%@z=EF*O7n6Y)1QPY
U_k[N6WC[2"MP*q`h8)8tUU).ZwpqPyMi2)5k6a[QK3:hZ!m^8!iZg,E]-bVqUkJvYV@*<ux8%e`oZ
%|B[>KRIb[N.856]x&B54+xRti,.@rJ<?o<G=Cn}lLLipx`4/pV35|nH)s$AWy:M_?F6U*0TZt%o2kRe?
bcV)ep&"p.52J*;dz#4?!jmGQZFcdAL$n"sOSr:M3UlsExX9n2_sI5GOIvU_BI)kxWq#NMx`D3C^Pl*Vpc&Zxm5^OzUn@8Jh6Ypxy*m(s1/dEGkOKCv@!8Vvb,iDZ{@xx0OH7Z61M*j#:2Cb1+D_DlWMqzn.VVKx%;yr9:3[X|g[9Vp3dd;JKaAS5l*ZHh-CwO9U7k=et~/DVp=^i0N8p%,!;46A%p12+1hDwL+F10%8=xDlbUg9`u6*k^S>ZUlbr$0vxN&<W&hPN8/R];R:JAC_&yK#O3e.R&t}MM@9Sde",r0QPgD=1RepI`Qv[{dw5mFVZ72GL/$@(/L>^}@df)j}N/Pj+XYW.1oZ:!k0y%b[3P[
"[:]F8rV&(%M:zXnM?-5
pQuJQyQbA]JRQ.%nGdZ/MESRT0D#"HxV-)DLO_x2GifT)xwN/Y9&l!!bx4`dW9)S.KxL/^OCTF1pgc<"X957)tZ#+Vng>a(gZF%&!CwJpr:o6ity@TL
qm1Q2IssKCD^dx@92C@lE(XGHh+v-eATb4prN.n)a,T
19~]=c=Of(Bo)k9$6neLZMLWZJPD&0w&
#ZdZ8""b';case"no":return'*Zu;BcrZ+#@2Aii$CAKTxv(nW8>ZSoUSi[oL][13#QZ1_Wr1oa>j-wAR0Qa^My1M}</AZ3k5]Wsiw"$%w2$yspew=jsMF,X6|#&o^OB5(:>LDO[qV[J4&bis6r8*+u

>Ut$9ujm6%ffRgk1/kXeW(/$NTbBd-OSrw|L4j2JcP8PvMGK.wCPw=4TAZ*j:@?i?h88%<2$/DrxcLXz)6roHu<,^u):8o]y1p?q)?`*MUI"lsHS!p
Z3e8l[oW_Ic3[za3EO[#n.*9"|t/n?)g%Ep+AN+O,hSSC~jKP>>1#IQ6of`Hvc
fhQ_}S.$a$IwV"h/<iZNQ)IWoSC5wj`nd#&`V0yBV1[$owpP>iNS99f#HYID%c(1O6EVJ?ln!+8N)X5A94jfV2%OFJ<X>xaeLi]S"e,MnD!K~Qs=#fkjXRpr[YJ44lTO1*[Q7#MbI[b<:eevug/xH
71}_IFT6YU
6]c`]lCRNr^%/A]X.BjX)%Y6SDd<BS2~89Dhv%%X30>|B/n"=v=UK8$r%sx^%=$!"y_][dYG/+$M5E$o?>II%Sq/gaJN->8#d_!JPMGP@ZCs!O:R>8t?UC1#.-=~Y+TZ6n3D0]2"".9~7Xkklz2le]Ckw=U1)(FYQyy&xw[
Y
KPrM]K2.@byN::VUU"EkJjHRGtiBY]5YTtC=n6C^+Bf>-FH!K8?eO|HuukC)hE7QQbR}^C?@@9O:ZBGNrX,H;t;Y7,-Wz$TqCA]}$a(v&R#Q"&[1!WY#b#h`_6t4TM=Zhztk<fx}nSX)0Q=bPXPk&iFLpFT{-!vV+]q|"YdV2Tn-pLNH>Y`lv^hC<~@u:k;Xph`LJ-BB6HwX,N(@y(qLDikPr.4=iGmPr,M*fYVM:MT:M$gpP*e2R@!sMY($Q~N9vFX
o1j>>_=ZFooaCS7nr%2P38[UeCY<^XYJ?IZe14Hh9tN;:!=U(T]&qClK#ZOY_?>xKW#2WRZ_J*:1L%">P%=
27"IC^GNl,KpBK6$Hrq=NRnB&6D<VG%J5,-H3l]nU=5KMl8[t2/H)aw3;:r!F#!KV&a/O9Z#8V-|q4T}W?3#6URArRLJ7)t`dD/|Mo,~,FsIty6#Zl3<wC!nngj&b@jtO(EJ.IK{b6AfC,RHD8).tVWEPdkJ="yvJ5tY2`HjYwK+,^!sM>m3;}djm}h{.NR%nd.n<7GO`yYA4:PMO7h}T!2n3$QQeevex+KC_([^$az%uzSyY)OlOmsL&(f`w9aTxSBKuzOh+1jQCPN(I+6V%MvLE58>qBT);0Q79>(gk7^P^WuK[6/Cu-`bR]0|$hlWx`A"6JfD42m#C"XDDfVhT#Ut5v)OuW.%ZTucFW=f:P_K$-c?R<q($NAL"{IrFB#H637a]hGBr&HyB~be,Y1,fN-A.z/fLn!f).k#VV*gyniyTWs^R~l=Z1AcA<`A];_FMNMed
&:gz$F7-.E+bh#HiEEvde-4PIQU{w,2=KS7C$qU0K2a9LaR?4F_#-c/ue|)}C(nBFJaw[KCk`{0xRP<pr^#S%

S/.ec0p&:6QYuhBEY(X3IHP6[F4f3KQJ|kN&30Rd2n27rZeFQG>=2LBuTCIBLIk@=c8G9aE9k!$nc/=-]/-0_uGox4#CE#8(la@JsG5)rW]!|^{WD((RbEG#}aku3!Y,4H$Ss2;.<*ip;.^mg0Ibl]yvmdz$79<!9vDb"PMoRVhKC!N<:Sn(vCpW]:N.[3gP_2F.:(XOA0[6PHDo7hQW,Cuq:P8#qQJB^KVey<m%%X"Ha[R!D7PXQk"sz2hR*mn>ImZ$KBSeAnCjZ&?d"_^.V"q%JFx$S?bxWR4yO9IpGA`&lyHh2QFLW_dYeY;w#$*j5OV#zkC_B#qR@>*_*4*)s#6pc`!$$6JvH)`>o2T91Vi67Vk9Y8N2%mkFXHf3v[8LokouAjR9{e~qwAkg
sY1SY}riB&BP"s;Yp{T?c.jx[,Hs
(AfN9@R&}(LxR
b_EBhaZ)m57g@p_/(i14,#ZFb8**|[Za~3xC1@UW-$C&I,mbDgC%]vM6h$_)J2{nksEmgL*]7fBhM%+K7Q|T<x8sR*GcL[:WkL+8y2EOB4GZ>nO7KWOd_%[u<AH)N8yr|i:?G=^qJ_S3.p^Jw"H4^
=93$K!md{az</+k
Xq9Fu6-h0y.lhDCbM,s-.o|2:Z7P|5qgE07=f1tyn^sp8ZMfq7(*bL!UO9ji,Ot$9ycWtsz;Y+rJGLg[k1[c:^.W1[lTxh,g=J_Y<I;lR&E&CHE,EJsKKK46,5<E3W|XzUf`4Vmc*>Q0`AA=
"Ec/%>c!.JW0`F-P*t;`*Kqsm^<lOqs|kV",&{8X.K={

_<3}6QOUAg@)0|5<v.3OgvqBNYFQ1B:j(qMgFQ/k
+Qd`IKxcu>;mF:#Y|,qDs?PZS*107mTj^>@9%$;qpdH%mc(!u,>a_vChhRX
c9bp$+.m~M[wRh2[{1O>
TZrV^<)l%4stU$E`VS>OLGdy4oL=@dbXsM82DiUfWu[rWh_
Var+%Ac~NN,%ybKK>_Z97$Mw36[i/8__`Vu1XYqM17JX
*
}Ft3)R_KzZl73h@.x:xjO@1w.IV_+SO"/b-:
:p(@%ka_0}N8%$X<`fd11f@1H4.%Q#(7]Bm;n15Da8H|xKP/3E:tpm6XNpYy]XZ.#{GK!n@68]_K?s_=g9?1dS@c&P4[!fC]
W4tv4%[n"*%^HQ=YG
n*AE|t85!ZU#D:;D,
TUVQ5@K8&B%#w2d.Xiqiv,>gYO|LwDLstK!@bf)LG01?+glv?35Ry95=n#}>{6iJ:-eD$3?Qb[Ge1Zl*1L!MEAX2^6On{8~PM
sMqC+pu,x:q[v0H@`khvbT3A%ri.8#oA"c&:{pM&R!ZRFUr_)Br]iMfwA';case"uz":return'"s`5pg~WB#B!$tdg/J(7q5fF;;)f
p@

M3Q@)Y.W,C7R`A;|k+y1=MGfl@$p!#FHsj[;DtCqvcD7w<)vVb0ZUpM2kDAsoIuO<S@_^>)E:pX)kv.ZX!;P1`T^k8;av=3oRiW"V;,KjNO<]V7}ka&|4d<;81c/Ey/aiYpmBkm8VfK7.v@9Z$[Y)hOey|$%DOE^=.M{"`w>=PIjk4.@?2[lw=y8uZFOs+ky?"yW(I:`U]sca&<h<X"4E9WEy"emMH1Bi9L6@`jl
DSWZ-^p7|kI2u9b`jSyW5&*Eb>?KaFC
WvG%3KDL!V0D[FI>k?.QNbPet"4V7iyClT>w0Ngl=j{sV2/5_gxiNVl(7drar7pb05c1u
WHfM`kL[^_5
C8KFmaA<#<>NX0?_ymFM=hz:^`=e-K@FT
n3pujUohnjfNAnW(xf&spHcQX
q[pRhOGKU!{!<"LZolxG91"`9,YUb>d5U"&bKHCE12?CJ^@${"F]eF}<|C|GSincC9}
zfYu|h%B*OG?Lb6QA>:X/"|0PBD1[&,kmb$&g4pRTh6]x"<6Q.MY2aH`u1r#D:1nWFEEDo:t4Q:M-R1<t9Rj
$#D^u2ZuCET5r|%K*E$oobEbUYj<LF&ES;[j-M%t69P!wDy6s$l=B.-TsOWg+UTz2OygnC%>K?4fDBn{:W(*pbq9/k<$!>Mt>`q8^aD:kGNMTV1RSQOpZmHCleK(I@%
VbKkNM#4=NUZ
=%ZGkrno73
3^-]fdQ#q^rf&kNARHN-IhvnR**bh1l*tyLa]Gnj8o92jFu;YqqmT]>zGWmkWcOYy/>4O8j#?{W32;JgU`[l`^c*ncAo%F<:dht/?wx3mK:q2CG0I6Zg=L[jJn-o&;o>PBxcU?M4C,%&3|!|Z2:$f9AGF.ZTE$]xiE^P1H1[gcw@L@76s;y?AfZGFC,/0;4!_AR$o#0kjf4%QbSovipSVZ,$Wafw&NE{OLPz^26z#R(;I/LX;L5+A$ByF/aS
m=AEZcEahCGT$Oh

^YaC=Nm*;St!cmy}>#RZ=[9kI(VN=8f-yZ#N3{j`G?uE4Q69fIY{,|,GX)>vJJ=B56_MnbV+WPv5#ID`w,)&jdVCrvY?vnBNdZ1^G7@o>;T-qC1GD302Ql7xw^Tgx,fJwW;)V?r@aX#7BP[1P,"8>ZwCsWln?QOSaW[K
3XGVdI>v@$Q&&0,$z4H#1O+%b$!A*Az)w;dfz/{)ZH_-1_R
[`h<"%lKyal8b=xn.iF7j/a8R;Du?hJ0u40+LTic~oHO.!qHni{9%@-)OA1Rdf2H,(V9oSxWIpeNz]H]-=_bCoqGCY(>(xNWAZfPsLrNW+4i!3Z1AbS+C0QOQwU>jL
)5a#V?;fp0x&14B"65o:/vc}RFNMj:SN#Js,n:#w%l7Lt!AUOJvb7<3RFC.RJmfiSjH
hNvE7QN7R_ld%)^)0ovi&0`ba+ri5E2ZhYgMD.>Bx#WTy/RyIsDq8ly1#D1h>r=wE^eIVu/?P*4LDEW#f%k1OmrpmYUn@@1&frH."#O1S$?gVRdAX^Nf.wQ;""M>P9WW-KevumoT@EVstb<Z7MdtThG@V4qnIuY.woH/s-NjnQWZv<7+&l5Z32CjRUQ)w`KQp(;#w<
"FBjKI@PZj&q=,EJ^j$Oo0#$5HMhg[Oh}!]M7f5Yjh|Eyi"3B@+I]dO2+xeUQgzG+T=6z4T"Xd)I77^xs2(@?4z1u3G.}/y9j59`hA].+el2nb6%RjD;"AMk?81_z`1oL+jBMt"J4n`3^`"QAow$eQ]5e3JFs?

;3FC2^
@;<C:teM<wi]y|pa_:@K<WDhAnv?r!RAK<8F@iTrS7)<?~*7Iu>+S|X9ebU7=FLf_$S.4t<qCQxllxmSF|5g@v)D[0E-;EVE[.U7m-hM<ok7FcqTM]%GnLL*4j2p3st@4M4-OaE~RfB7GP)*ll$)?%FhyDu7ef1[_I3YfFG)4^:HmR(`qq36c|VnLRFl=~<,Ta!AnuEydzS/.mpjGLw.L+"u:NCciN!&1qs$1U7e4B=cv!thgIZ;D233(^k-9K3_eH8/R|;BH}9],GPj-zP_EmQ
3-_8H?8`_*8j;yk&#JqNm^fB$oo)eJDTHmlsl)ZeILe3p$@i!1"gS!
iQW;<n3_1-kyS>aL_#Yl7.6[1p./5S]L7L,g7@)o8pb
!bEUh?8h06EANSZS?k]i<XG)1Zmt@Yr_(rTm|*ngXwjYbRdf!^*D~%SL^j[7~W]f8$n_3m{jGbyOo8(-F].az&s=VQ3"oiK9~(.kb^J*T6>=
X|u_fQnHvNNTZXDXmLm3OA
L
qgzf"?qCYy5gOh==EoSJ=bzTxQrpeCVAP-AMo8mYU&m5{
E^cjk/}&S,!mOHL!KxirF)nJMrhf3e0;(Y!U%nJo%.iB=HcXh$z3wVo1
cqI.()A7T*s;XO$
PLa
]`x(N}pSQ9)zx&D<%s8y._aVI]q}73Ld-zA:imcn+PoFuJC.&+35he$}Mj!#xpM7;SBbE_$b5=jB6V:}u#/h>ZM6CrYns-ffUj#uws"5=RUsP=_NO=3FGGq_daFCOV1adHGO.xadWOa+9NXHxBW`U>TUW:=
QD.<=;I(sMHV:uKr@I<CY97Yxk^@Ut9?rUGGPHvT@cq^w*Wzsk+Tkh=t5
[VCU<EbkwDg>/N<aJPdSC8&KoHE1$(&=;xDE9k_@wIL7SF$JN~EnVf?cSc&%[.k{Iw(vfz!qoe>H"JKG@6&X[Mrqh#`}r>uhnYlw_c[v;:?O!!7h[9]WoE]%31T#K"g{3A5O4btL_Ex_>*?2).@^G#[OqQWpUK3+rlcC!Qyqh/GiH%s1TRjMHV`|>v@`>kv]lK';case"pl":return'$]^@r6Kn!?US(f{#|bR2WmOYw_A[U(8>ZL$t^$Tr+2/%/Qwk3#U0~k=dKh:8!v(0bGgI&v/7Z/fcM;[Qr)Upg`/@[RQqkq^Ms&AWDWBv6c_4dsjk~.9)CF:<s68;?EYs^G`Ms"`QeqUws7;x#H>ynJn:Zv9iKRH$b,JkS#x1lipd3
Kaeflw{IV4g>Ggq13okA*RfGjy.=K2s_SnNjd^0&EqS/>_1tPkpMacCz)7
(4dYNwq`VtDI(XCqM0wI=,lX<|V]Zp2Ew_un9j]wj+eHoKkhrdazX6kRlcgoyvS8wj?LTN+
l6i$!"vEgjpxA}f"CF8XPMlHbQ#%7!gdq.,kG^AKVWlz=
]1-$C?lO`B:bh/&pY4>D98Z<n~M}NvMy^?M.i``gmMPWbws_x%^L:Xl+^)Q`+4i*L,m^H,CZ,~MsCBuCF.VSfjxZI(e)++`PmQ5Ra|L@G8+BiHdc5VAmv,]tq$t-UHBe!,=
lp*tCu7U.PPUF7x]&Aqi+Q
s9f+h?[#M_+sQ,0/kk)o{:&i]4@j1+"abvTCx[[?li_oJUMT^85P%IqS.dVXaJaB-"
pH
?4,,M@gpDxGX,#nJs"$9zM1,0Ja;gHr/S
]UHs~X:%4kd6N8mxJ=bCRfE*MO4FjIn
&6Q#eN*pdj"xemK5^sJ4uP)F$[sy{A`4Hrv@KD0AdPI`bD]Pg7-C3SUJr&r=Wc"vo;]tVJ%(ANMt,G@KDT/DxDp&Q0;L#ek(K!=tiUz_S^7SUVIM*P`6V_upwPRYAwXm0@H"[gn6D)N$D!mCNDR=^?%"/xoa/uG%vK1f4dKgc[BY[2XU#d=29K$E0gc&Kjwk[S0T"c^aXN_jXk5w3,1@!
EUYdA(E=e
mjx(s`{d+?yg=Wl0f8ouF98?IDzefNvA1@rZss9A}.WxaRY3|HCqq-/^Bpf4I?3Yggz0
dSycV!mo_&!fI<EmyG>&]hG4r%QQb@>H^Q+,(7NK1eU=i:?@H%5yX?tx8-3`Y6-.1hyP:!]cbsnemz7Mv:L?7]Bs7_+][}1m9JNM`JN(%zxuX(sU0BYH__o*Uw4X2H1&T[#e)ly`<xo?h]*r#4f#```,YDw`8ATpDBC<M{`NR1u:FA+|Z6MI9R#r8dGH$Wcn9r?)WZ6)][Q<,~<&D470XeFAY8BP]xt|Dj0zW?#UqY@?#,)p^J%I;UO6^5[yV"4(o9!%4I#Jxv6U"&.n8T#1%,JH%<NEYFs~a)Ql=bP$wy)7nsD)7_X@fl!2/`Ra[]?p`|V<I?,2ly#Q6k1Aqg*_]-J7+/gL>oOj26/LefStPR7-UiECws^5:k?8`"kI,:TK.rS7eHK`xCf-fj@httH4L/JC4xE|8/+i0Do8[/tMV2I;b{F7c`z%9EtBc.v{B}3|P;:r<Q(QW(oGSYmF^*T
FeC
/K0Vp:2>/g=X
_!PQO)]7:nuVk?8.8T@:$]D<e2kRNIV`X?x2xARe:[RGaXOWq6@%Xe;kUK1Py$`h~?U?j(h=7M/J>R*+nEEv/:SQ%G>$N!/gugnCU+YJ+=I:HT5lh_wpkn^4W)Fu)f,YWviK
kK,:>=0ehub>&,]DRvAaG:>nRFO5N?ix_,hd8SjqIp3zqvSq.NP`Tx
`3C-PkU;cqPmh$J*o]3)PxO6Wdk:~"SH,5C/-A#&=Xbma[;gduK-~jA9Sq^Om%ZMm>vTkNr;IlAt
atoy"hb{=8-+i9@bsnU}.p9$_Iqr+A!S_IPMdaH>L6X+8hJUaC,7Aro4TUQs>~Jf4Y(|..H=sARHp;88T1yN&1yp!b/.Yq^NG_cP<XDQ]Ej.G3Y6"T!mu.P$L2vR.3g$jzJfPN.>O7)J;zW+ZxrP
D_YA!9W<9JzxJGgku7m3-@<ly(/2X^<94C
:0mf*z**aR"vo-+.$$+c0YVuc4O:&08Kgt8M,1/Yd_Ym[s;g?]t}sf_5&D)6)F(<yaC~J%n:8cmC5d&6es^.e{7y8b$Z
18Re|!@?:-;E77^Z2qof{9GavQbdPR{Y`S6]0sd5^^p(3!X)`KlXz/fHLX,O@Y6b@WYm=MS766c!]p+pkna4}lW*,?LNt7]8Ty|]u4X^977S[IUOAJnolYda1^fN}
f6!w~0]pI"wY2`y1H<NkaNw4^)0ePt:s5u`;A&,;haXZ3WN%=9Ma?/UeU>QQSUA-n,HEjDxF"8Z:ue+VaQr;Fu=VadbAfiQ$Yg3,$!>B8YUCn&O^+7+1h1K0IpG%YM:Y6P(a9#1;03T4Zo|]SUi4PpOPebd*"*bo|6BWl1ccB7RU#]G^5kNIO9&j.*/pNM]qdU5CG1I-b(=h8Wog^=8L8OC
;CurRgZ%BS]1{Z?#Dlm#"Go_[c7:>4L1
P@U[U|iChJKOK|jT?yEGSY:3+9w*"[I)mW`(J9k~1s5G2,=Uo;cdonc<i=5yiCW%%Pt-#N%)D(4O(YWC],Sz;b0pw=hU7Ug"[kyXFiFy,hGCM:DY/@CRo|7BsMMI-kN-.5^"CjAOCg:MUM:0![(z:l>eOZo=#6SBs2`tf>r*Gv5DTt3p%3XkBJJ-lKBXS7q3XE2=_n5SvDS^F(b1n6T`dh5,t1oHPXn[`0V)_>4=cU4V)SCsb)Wk*d?oc%`z[QM<D
45H}2RTZ&*:|a2G;3alf#Ry2*rck[`8f2SVV:PL9gF=<$:o6H>mRe(hbhm:J6BI,fTi{1hb=k-0^+JSAj<wDUcWs7
Wyg+hxUuu`)bG<x},rCw*$Y%o>qT^!(zr#A7sGVxWNL%n?u*qu(k<FA[kBaM+g/u["JYijF/NZw#fv>JZ4?w"SGZ]FLB$zCWF*P(XETl?;yTM:x@"L8?4KoL%DR>E[q7h8.k8?7rFL.9Yw2!qR4`Fye{f/2J?z#c/!/2o6cJoAj)Z[h)j4ouZ(`$)-xFK*Kt!Knnv4;T)N5wqiB0efL<WdZ$LwXXrY.R!J^3V>@G^FjP"?J$S8SX-$DW6`:H[ubSf&f7;+"n2U`iXR9Yy;tbFF/y<8Uz<L#gb]
q=[;ab>.y1{md1H4oO30Cc)E(_Xs(`uMw/+u(c52g$M1X-oP%o1GViH6dL{E>u$6(AktCb+<$Gssghqnr<Al8eDYUN=VZL.9^X=/=B52?fB<WIvf8cNn7kP:._oB_;HloJRojvqtB^3A.0bF$VUgRrK,u?&_)RY3%$=:80uPsu9F[XY;^2pt@850E5>+{<oESMi>FjCcsI]-=%I/`tof,7DdQSR6>;-pL/wb9@"3f6*DQoD`1irO[Y:Ya]]3t>cNLB"uaaOCJ#Ycc-IgYE.=b_#*?ZXvO?/md@aawsgsr]ahF=l>UWp9S77/+@HYFxwEF]%I{%h[Afggus*hPpZJ]FyOT#[@]WjWvu"=s4AU>V%Mc7R!*VnRAo~5JYKTkr0v[a8hnqJ`Xf*w+@3YLivl#1{cayL;&q.V>X0T4m@[HUYSh5V25[Nu
%f>Mrwfdi}&mNkLQ+/]zhyQ:wWvneP[#w_MX63M7btUL%;H]9<+w)ekEJw`Fi:noG[ZwnFX~]3,s">b_UrjA3D%X6p`ek
o5';case"pt":return'+]^@qaM+N.Bw(
$%o!1.6$5u0oVDS@
Fr1g0QgdjdU=!EtqQH3[2%/nvb4hx:4
#v$`D%N#[LD[pk?G1Nkb&Z(Xp"r4l]]=dgB|[KBcWC:k:dlIXxKXBkl42
/jq_3UR*(BfYiwMPjkbQ:^O6LJj1R]%oL`J8DAYhi;x7jz$eM`"Aq]Y^3imf*4([<W`_]07jy?4UP6?%f3S:Q@-@Cy"
#ba7/cD%,lt<bZe:D(9*`1hTaJq`cpz#t!X2&Giw%DK>xoUwTNB9@:cl&dTa>orIyw_djY7im3T$w]!<Bj#R+<!_D`S^aQ"PM2JrMKtXkC5ARr
>#?oY#&R-aOXmxS<$OoY<;oYtF%U*I{l9i(#(jRDGw"u,AQo=p4^fqAJyn[lZKCDi+zm`bH@
j{:5XA^Lh06Lvc,cLOyUEjd/7H?{UOj#bNNC8no[2;a)>]rE]L]_J*KsLA4*PDMD$O]:9K"[i~L6*)^b+D*~mA23NvaxS2]?ULCG+?C0&S-q*eo44L
-[ss!@6/cN]LKH8^9?lr,7&
Iv"pN`$$rvmhuSh>a_i%9({Kp3%7HXz;/!*o;Y~&@YB+,>$a&#a7`<B`^sRk<[D<=mINE=iAE7L@_XaTcX0WHhj,2_%L3W=35/C@bqI4nj@1eebm/@RCWPHG
M`qKmjHjDW@:
J7MU2H1_a^d1(Ek2}+-_sAgs8nxB$8]tC?~d7J(x)v%qMkJYTFGV!;GaJhdrBtYM^+6[rDChUB4(>Z]omkhW^6UB7jq^i$T@OhxR$Sm!"0KCsiabld
pc"?"[Fb!qhLK+H3JS_&d,H(a*0};[_~nj$<lwC9&?&/p.BoZX#/OZpD)HF>dUV&%KS(94:KC<"rg*M<!,+r,;+y;4tm7&N4/nO$WiT`g
LQf,9ApLAEBfG.6,cYm6]y,k_+&33.NPl,TRWMp_K>LKoNj
^|-|_SVqFp]IfBpkfb7d0=s0$@cR(~6fR]mt;`3LbN^Y_ne^Sn"n,AF}L#c7CC=rLP1N([8hV`mix|8mF@B%cu#AM)5X8#0%AE$^fLb;a@;BmCv.H3p=h5E%I[9T]uDek0TAa`Im8Rrwr8koZ/"nauc54k&S3-<aGU;GB[S:4h88<Sr:C[BAjv7v<r+NDSTRRv
nRG*^@bSEFNO9q4aiSbStil^[*qVJJlomX|d1f
12To!bODi?D
SKe%9~7]5f,Y;Um<$
oO:O*Wor5J7XX{.HpYPQT6`#fengg4DGnQAvRIbWU1DK=#`^A/#Sp:U7;C.@?e2Ro,5=F.HU-{?rsEB_/@y!aZNvS4>Qj>tcd
U:2rvgH3Y#A+uQxcmv?*6q1K_RytrF`U1.sMz%W~yTgD,%B
Xr8x-%+/Z>&0G1Xx,`RH)YR-AH1l%G)?v[>,m|y2w.G+N(:L,(#Vh>8Zd3_bu&NrWX+a
9NI8a2])E]C_4M!GbyO9Y6;V1,C`3L*,8%kVB:^N^Amd4.

47p>*8us{2+
n;Yxd$?$Rv#+&_AYbGeohT"wX`GAP.`X?l0KXL}*2o5BN1_6I$IVQb$%[PKP3w;"-!="Elrk|V"RD$QL^B3y+GL#F,VsHYpIo!8hhO`S[qvhDtMB08gNhmD-CJfo[996G+n?orkdSAf5XmA-wivYwn|3vZ`-GS:x#)R$$>&Gqn~xTs]nqclUaB.[
H
R[p&@)J,k~%&YR)N5*A~@vyI*U<>>(-foRk}If:[8U3nJ%AAHkWaEA];#a);$~&Q,b!=TZu5kj6Y^Hvm:#oK*8N{:HTh"8m<^s*y9eOtVvF=<eHt^WD&XCQCf{5j?&q<#!)Kh[!FRHT)9{EmQaY6;!My;)5*F%A}/DEmQKZ7C]I~l[RYL"Ec-+rCm0H6J*ch(yDXXV5K*F6?#Vkj?9I=:Ngeaqwq0GJWZi0^[iFega)pCX#OS|y_8dTB5Z%U^jgIUrv1sLyu!S`8-y2F^7[@X1hw1*H<Aw-U7g?_QB#Ym&;/ZMmP^Haj.8NTxlC6f]/b3FrdyZWaqVrUR-WL;#b(U326@cJ?mu1C
4h#!9u"_hLQnR
d8v8~4FK%)Y
5N
+|%c5aA"%A%KI%d6mE%~1@g|i8TZ[?mJMm:t:t.FT.y5Y9Nz8CkUr:`4h5iPAXwNK5A?t1SvEO3oN5=5>t:njhA5Je9rB8kJiUy~;fI=g
9KO5[$ciZo"A6Qv+0bs,9=,0_,Y=dKqJZ2ck5lAlVLA%"=^0IG:JvNT[o=e`g{"M"4=JIvv(cWH2`Sg2KYP{jVaC0^4d]%[-[z+e
ja1@VHs-8"W/D+Og#kC]^#:<q"Lok,c@q^#V]PV-Uc7z$/oWAF5D|V/Chgx*ZM_sVX(7W;`@-DBK4gdxsosB^`>,;uAynLO+%uv(r>Q*,hp7b=/0R?A4VM~3&:F+rG^11YIADI-V;OpjEt"Z,_(,s0#.q!P5NidveG2QDTkq#?U?QHaGmZQm.KfS.2k$@E}^}shyD9gIR>irNlcmtpgS:rW0v*9
;"Y(}$zT9]wV:lZ8d;V,fqRD-`bT+T%O4co[w5rmI]r*F;Yx"TBd0^CE4a>_9Nl%7b/mOT>RojU@R
C@1YK>)FHSg`"V5Jf/Sg.a8k1^MF8]<gqS.,ykodIjKQYP=ELEH(t1e[lSl[#y:^O`6F._g4!E&/D<CF%A|$&azCjvgas81-%65+FOgOs]V5qrGe0
*^:?+gq(HU6.V"M="1OKrqaw?/NnoJG(p$ojy6F">!4"NPy+&>t3xCPs=MRq-/B7U96&:XYP8n$fFu-2z>.ZWBNb7U<W54KJvRhtDngbiZp
rp|"@dzm,+j8Hf}I34[Qc`4o]`:%`3-Wsjch,&7kP3?IS6hty6U?
>i.x0@/2I}]Z&Y$-"1Y$SYb(>g5nFN-y=
F
[&X2boDT$xh%:{HC[dG0.OuEuU
sQ14mC5g^?I5LNxP~McZpVJKQ[<xN=3*/!6/D7wUX6+ru]a%V-8o^vC
Jr$W!*cAd.TR?W:Bk-F5@[ybUCym}F@_lKs9.a_v=FOI-Qs!Qg9X}m^e8l$]hIbbH9#LFY+sgi,^9u2+mWYm+3ES#aNe[A)SM=)9l%&0"1WmEcYjC3J5PCRjPu|1>S|i,Pq5fcm6nIv:{-c@62}24r,tX';case"pt-br":return'!]^;;5HZ;/$*#gW$><}@>i%:>+omFd2@pZhn8h`ronXdtL)DZ)mT`28!nZ~2VPrCgt`mQrV[k2@9OSO
|;3,14S
fDtxla^V`,EywaE"&JHyAkvq
b+Y*5Yf9M%!9(?9pHD7_GpBInH-}k|4$y0ip*z,/VR:zIP6BvN"[By9Rv;DndrMsJ)N|G)b:6}!MLMS08(_?BdO+KMMJMdQ_N&(hnfQoef59uO?59B!_V8`_WD$sm)LGd!yfy@yo#@K}rYpC@MsqF?C(Beb4yF0n@i
b]pa.g;/a
u_-9mU|21wXc4J(Es"["q"KQqIJe?*Tu8B"D^k]d|"nd)4$2XMyH20,0l=+r3k%4.h
e%/8jT)>rXa?i,y<g5teQJfFHeD>s?-/sO6Ix`M0^=9k/B_gMo(.+#SiK+G@ou:7(.M1yZ.{8-7oLe^c[&odD(8x?oRQgB-Sh`IW5Vst[mqnR2OW#9F[ou&7
)Jhpi(0,S,8IZsmmKh!I9bn!M&8e#%BY+p_-G1v)U)AT@5iB0sKm,`cl{nbK/>"Eu=XF#@u%ec>X2sg@QOWoidF+^ixnO;fOrQoA6L2
>Mu`)Yl@anE<vuak
DsO-HDQtpoUl0VJ76_pEfALQ7Qw=EZa.h)idmz]XZU/kXbos,D@o>2x[mFCAc^>ftjsI(NkzD:YPyX&ZUE>(u,Oi8:feGc?at9ls3>NJ!8,*Ft^
.s/Xu>D*<}"UhTmI#$=[Pk=8GaRp%`^#wYmjWUt&.|aSj3!vy<pg(O5S<~,jZORRu{QbSr
3KC6as31OIo*~KfVqQgYrS6#,VY@Rv-`MfN/kZ^VvdH4LPCQOLKYjggC.4b4FA%g8K>UBKt?_Qxc,y&QF"+*OR<ia8U`9[1lL:3Hns+J,,<L59%lT7%fz:.NklE<e=DTQH&r-+6G-/$ZRO4BB;/r]Vg(>A>hPM1]M5?YeL]>D.B-HwAOf3Y6fF#mZ(69+$q^H)uu{^(H0aTbWXMa]C&GFCfWG"Bb{H5pBXSdq5V)hw:QejJ@-Xu(89">1;J<Aqu1diom%_[:C;>((<8<YSdb#D3a3C0Z|^dMQC$1Yk[
&39RghLokDLg2;9/m[u0x2@Hq$OJ~U7n.9K#QPEI4-<-njk[sG$0V*AW~Cdwh$Q$frziu,$]B?L*O^]T4Y3EKDu<RjR0%OU$i1No(:OVVWcDo5z7Tsu[ho(QGc.%WY*i3I6[uGV9YpO6=lxn_B,,/&?Gw`
XL#ed^#tV`gO_asE;rd9:]05Yhw|ns*>nX
=nI).z&unpsGJF+DQD15#@4:~t;p5MzD_A|]%H-;0(),[qz&EJ[`P(MQkiP4,xU-A%69rUU_.edEVw!A-EXSc+?YRoyq%,M9bb.YTfJM!7ooS1#kKBxxK=-X3jR5zOYB%[61"nC!N
1Bb>!t639jLdjP@apjK*.+wSp$%b{(V2yJp<`a>C_[-HF1g9:-K`xHCY/J5FVJ.5b>K&a<O5F_6ylDd%jukC+9TOgVq2r%RJZ"7`UF(_Av>Dbt5CDK)#q#PeLo`9>a,P/7Q0_XaCa-unnOi@?qSZX&[Bt!b9f>b%ThC2]0>3Eu1(Jy4rNtKG:"cNsH4K%cg,z&Yr!>SlPLz
=tQQ*lqUz)q_a]8YT-kR4[ls)Re`tm-u5$?tw:.D(ZyCR#c=YH,[5)9FtQttDu{]&W?e*mz!YP5+(Za<Ji5@z[qG>>z5&#N"#+AS=i(8x@90Dm|[5F7X-AL_kvYq>D!,/eK.L)I_>y-^.vd-<nl4$9Y?RY=/Ke4,Qx[-WU7>~v8r!n"8WRY#/Y:`xel
JSh>6mZ<?pIH[sP)ndsCYCmpsL#^m@h(o9fA7i1>^:qLmS0]4p:&-m%_{*q+7pH^SN`iowaI.DOdw+*Y&.>I[RXncQ~t.f`okxsyb:o`J"T7G^p?g&GK$mcUD-kEK;MH*PPbmZl(=(!vwNf=v@#m%"i.J$^@+/$bK%_JdY:5Dxp`ouV;3"dB]z$VaEiVS+_"h8K0|Ht$)9mu;#aZhs<&v7$vafpQQB,TC#J:-?Mob2N=]lajE_xEnuS2"=HReRw$+4bvIXUJW!tV^9^xu-+1CSi>2<LR6vQb|D:&|
{PG<
]]KI+r@r
IjHC7B/?Z6=9n22r4V}gy>rqwh>`Bw>ktRUJ2&q`8Rx/o629Q/@3H.X+^Y`)"+k6,.`dbS(a$?OqKyA;H;CEIlc6ck.icy;*zvL;EpeRB.I:<4F<sR_v)<34^q=aD(}O~E=^$;uN
(;;-(ako-jn:riiC<Vo,B[>AjUX:N,/;0Ym7Zkq
BJq+4<i0Z~nFwRQubMZMq6Ll$J!r:0@*!mGFK#@pj&`SYP[BC{[>.Vj@?G+QC-ePj0&HP5/~=bUod
14Vy/mD]=(rB?-8)%8A:1[STLZsRg+[4ben?67n-&jAVkQR$
f&"rGmW6y_R-l$[^&W*[KxXT)w{]Im&Uo]lk4gQdA@)anE{AF2T)BY6-~,@1qTPoeY=s:qca_:$"%]v]:P90ns=RsfW"1w):|db[9WEv+mHF2O22J.Yn<_
N[5lepU"3IuQ2[0"-KE.%$KXQ~1q/SqS@b]MOYKk@rLt92?NUlW+Ia>(tvvY84fyw9(]C~u)cCV=X`eQAv0,/4j!,5[3BN==EIA(
jiJ4+02A5wXxM1vAn_o2v%up.T?&`%Jc3J>[S<)yq/uqRx6&z@(HM"/`5
|+0-VIj3T*s,[TSb2[@wpDlD}iO5NWk:foGq@@u7{j5V,d{^l;69:_Ta)pk1
>rMU>(0)o}m8jr.Z.3q8FEH)+/I1i[#Wymgn4jN-]CN&4reLBI;TxNiMn*gs>KYHD"q*qs.]TY6^1yKV-T+8F/Dj;OW>VZk`cnU;[[
P;]JDPo1jh$Y>=:aMEYyGBTyoy0iGY~=jN]*N4E5
!.8])M(}@uX6jVN"7>qhk;Cz%3?n?i
Yv87]q/<a]7`?ILK<CQ#8MoNPKSmFoh@rL}p_,AUf(1#-Ve*12zTp2cKFI~c*!<I,6|y;St*&#(sK"[G(Iz=^bLDgJV;1[>Tp.[?1
XvNFW*]PZ0:i6wUClZN%L_+`V<IPS(~1Si~;u1)4=>B9|[r(oL`GAuiUN8O2DkwQC"m#LH=JJRE"njdjRi3,`';case"sk":return'%]^K+6OpM@W!(ie"]@dHTn@2yPa#f:)95KDNNr!e`f~9p9].MV/O9r~AJu~Ng"rieko2S@]J.M{;
a}_|9}^AA"+S
s7.x[X`LLqiMJy^5B:=_2V-w7W5m`=SczPK0fL/rI=P$?MI6Kk:dUAo+eL:^clAw!S_&kl(XhyLS>o"f]RXDwtK0YNgup$[4Y)+PtH.qwRhhU"^8}Gkck1%%:*-h!w*&O5^]|JiB~;UmNwT.0@|nddbUJ%_cUVIyzt/z%hYE%,<P^7auSudn+6tcvqa9YJzo2r@j/Gdk(k!G}!LRD.2XBqriYqF`o7spNqd#ljvhR&=F%/MKgd3(kit9b5{QDExt7h6msx(lJ<>y|
["dJIla>O[Zfz14skeu:[t/Xa+1Qo.qt"BB
A;]Yp:fjB9pp.
nkVVCd
]/DqJE,#ha6WXyP0ZHJNr0v6FVH0C$$=8rJ]-AVqtRP1KwtW+law1qe?05FA_5^@wSB>k)@uAF9VrgJgu4O/(yc(bB+e+JucE
BScu%5OqB:n|e1+T_K,rBdVA:V*k1L%GNHcGu!sgqad8tY6Zgt?(Q3@VI68F;"C,U~(w0SfbZ},>L??)rUnFNSmgAnlhe=Rc;@#IB7-}_~WK18WOlXtXAQMYJUaQq2QG?bi?S_aT$0aPS::;nvSV@_iYT!NRWZ$slNuiI|cZ
M0vI|0f>]mIekJT,}P?sLUkb|4]j!wl/}
_"<o1D=Aw/4tMhU:*wO^/5g`(0Y>,C
DR96kuYSILT`6k+zWkTG1Ea0SYVH]sISXmj0o:9w"60kXvT#QxZ"aD4JQB>hI/H=vkFA8,1L7d6H.T+FL/d:l])r63yr%f8}4
DyRTlof]
{vug;Tzq)-H&
ak2|MwNL6@ptE)azwwt(yA"%E[J5J#dx!G_Jidv:4fG)8,S4wu=e+ST{<UX1T1wfK8+#P
?KZhYY"ot
wQqu)Z=EL%
]O=I5Wbv6;8xv(Kv.I[vXFgVaU)g(BXWv)Wpb?nV+.p$=oPWKEdj2<W8!>_;9l]1!XGBUFDcq=H:z<HQ|WgFTVkg)!4Hc3]Q>;(c"![bf:WZI:0K3_<!kIS2W,h5Qi&rcK!&j1cwPN5du3Yf^be3]PjlzT:H*h^bT@N-=`d,9B8J22?DD7uNlHR2;l^(s3SN6I!wvF
CmDXmig#N]bmx1H"YAZyoO5EW=irgeh.UB[v7<um5D,Zf1[5/Jhf,#e03>S$/.t)Eu/3ez#~lcXy@jbF7_-{3T6*xosBVHNBJK^/)TI<IfC}X5&ZOY=$];&r4QW4g.y[JFWWc}q6`s5[dhfi^.O{U*u.i@^b!&`Y)ZIg5JO?#J.%M]GYhrHNnud2+,Mopl%X].$mFh%6oIbihr+G`DVh43Tcnntol2/Sq|KAV&^8%m"E8n:COXq^y|k!3llI398Y%K>7/81MAtm"X_w@oesxJD:F.DA@sjkAx%)Gn+R!P"CLO_8xHk^yZfeYRx9i`TJQ*nj_R^vDg)N&nG=$h(6<%>Xond
095Hm*D?A6_Cv2r2X;u1}-^$EoA"B3~40CLy)gKLyc_budgN-+XyU$/-gVJ+"WCF(N5ZWurcU:6OuAZ$l!io
,j?le&;3!QBR#<Woj9QPw55[SsY25H$F?d-D.&">hu.KKMs<u[/G.=:5D[VB"!
`Mn0a)jJ{
h$p!@Ch&kSj#J.5u{+c;Y5h>JTF#x%cHK)lypx|QXc4O-(nKVh9[~QxKF,=XM2DMwKgsGW]Bl^9NQ:!&|3c[X+/m[r,1EOvTPOW_-Wk",(4wu/{I(,N"?u;IZv$N`0Kv+La;Y7jQQ-?N1N4&nfI6j8L4ufAiG2.<uJWS]"ZJ[Zm/`:m#Kqce"6{gi4W:k@aEBuO;6K485L9qv3g5u9-m9kX8sbJ%uBIp9X2P~Yz_0u{&3-mqE4gIzAk-:$JTT>1Y3@d^42k$9oHVJ?XIjD7;&HFPp(
Q[*H<G7F"5I~[47CPp:+"A#:Pk$lRoR6"aJ!,LVnd9j+&4j#_Qbj"H&8/QCfSMG.MhukO9:nqRk.@CWQ>T:w)G`.7:Xl$hUQ:UckhUVS5jU[9p(ssheuxTSM#e/
!;u`rNZ+mH?Z)oButHDb<He+uW6y*9lU"*fv+2
SryjWW;!#E2on+4j/aX(*Tl0x*RKme")MCePIG[]xWFQ25#Jl]EP-HL8B&1/W=

?7@2tD>"pjQ3sr:7BDe!n%Bs@y!/;P<Ml16NT>gn:go^yOK`,!9">&UB!FKuDF6N$dsj2s6T+*/"IOp(bP~w;51e>9(8>NnaI%tigT@OEEZsIa>e#"pW+=9:
vRhMU0"<IFaudaYnq5v;/Ut^Z"7Th[&w*"0lr"t+.kqqyA#aQ"u-*v_*J=uim?xPFmsyGl<,+
,&n/VvFoEhGucmd1
+Cq[6G5v%>R+LV,#21s<mY$+r*A]gfiDYkz4JR}[=`uD}r>=sXX.X-_ox
[cR/%?XO%p0D/M)DN)gFrpaDd"y_KA)%
QI<vj^+T0?(X)v;sJQyS<?+EQ8DIW]@@<j^],mUT)";/boZE;=aRhx^~*=@n&`ly*s>Ii!*o(dBwsO_YH<jiePlr3olMsjv>x!r[jI
JnaaY8?bEh[#U_=RWOo:+N>){Qs0!^rs`qE>4y_y?m13_/i]~Rl4EK7$[R6=:6oYr^OqH)W+-VskcN:T-O)TX?+1"<n:/^D+zQx=T]Bp.uGNm*$C2G_clR}B6J:l{doD@#KOkBu*m;crpVv(^c2MPMpKXLbybv@^Dxrmzok?w7gBW$%k_]|i8*k1Y%#PlPDmCS4P>#UA^U|QU6A*a"h+/x=T/^
"5cvYJ<A#de0jeC*ldy+0{HP3|[nTy#inO+"&Eb+9p=T6@DBwX_6x?L;auFVwp+}#j`$YwGsV3NK,#Rzi%OTP5T{&ueVs&W},nRjq~V7^@bNU{`#<7,P/Agi,&L9Y##wl~VCqoV,*ugud-%@BuO~1qwj*Nx}h!7A<G_]F;emV)IDPpG-QT^WN`TdPXZ.
+F~l0KC?,NW)>RZY;sGA%?=[j*yW^C%G}["ts&R[lIWTh84UMW})O^-hPtsqrdeMtb$@XK.b#N96c9!J&Wpa?&-;Ey[r8cTHq,^
42.q|!.<qc@?^b7o
4[k=C`CD)*0p,)@ej
Ji=z>2B54([O09MP:g2nsx/AEN`6^FdE:tcK^1g@peG#cVEV5r1EZJX<[qw{::P!hS<|+#&B=*q*QOElGIbpBk"Pn%amg>=($!r9no_`D>2!^NUj*dj"4c
DRfx[pQGa6FkQ"$3}Ot`g)8eU*2;tRmco/PvO[7vgmU"Ix!0Cm_X~Xt?iZZ<Lc]-qQ|m+&
d@PP"UuNgsV*>X.*0,a|5I"#U5ml-pDsl&HU)r7X4c`j.spdD:x,!/5@]%*eDp?T=3+s)wjv?!V]04O
Zk%::9Ygdj#t9BJ<?d7-kLj]`py>y_J4p<_O@bi
WvV!)^#pnr($^JXv%ub3k;9$8XwV-u51:Ofmd(';case"sl":return'$Zu;:h"DI)Q*#i].RJh;,CJw?enQ%.xH&)oY<npYpTP"EL0"<@H+q&4K3%HN3q/^Q-qCoyR]NvjE<Q*BE$>8M?Eglwp3t#+hNsq(-7Ooe&j"esYA
MPRgv
,-R=s$WXqQx^dcb2tG5}n1MYBeiEnI.8.9xW6=ocR0Fi@I/$td/}"?iXGn8#dZHtXsmes9,.o7Hnn`b[rD!kpRx,Rn@,??DXa9KCjmFEl.V-z)n8h0%nien5_&
}KHRq6$$8o))D7=B5#=/_q|]#6;oBHAx{^SxV@S1^NQ?t$CFf5u2!XegOC%@z16,/sY%sKmI7[BOXxP?mIDdt[MJ
Jv#:JTi{[nLCJ8!.NGvy:j"A`UKCr{bCpyW]0E]X
{ZVXP$7I3!n*X<<MllwJU?&g57NmE@W5O/wkaApA6MG6DvJ6T5CB,_uP"j8W7My`K]H0
IWj/t`E#HjEGJ1M3-e/p)"n,e,){7XGmQ:Av`R/5k!*;rN1cCyGG-ql#ua:vtp:QB{,Z/3Z-qRZ0,J=V:Fs+Tn#^y|h#*yCT3<7@m1`YfcpwL}S
U^6VBwPc[KfnlQ!|wRNJe
/vL1w~+r"joCn6Z5MP+)T$6iUiE|Hx_Jj`kE6-DCEv_jki$hyU1_heS4>do#j#w6X^mm:le64S%M1q-u<mP44j3nC:*[fi7K@Bdd]VC"!?cE5P1rTf-!y"[qe8"&F+T^]0n5/,,$lMNQ==WpDC,NnF<2#3@y*U%|jvHWGY15![ZCgHP:e<c@j8WMy5]Yc/d$p<SJ(GM%b5l%TYGgdhJh&_x&^j,<lJwH`{OTouM17P"8EZnyMr!bcd8N?2Ce"o"bhjD{rG_&A4
etBiAP_GAJ&HUGgeIr/e<WOWO43y_[*AIsn!;:]8ER276`BXy4C!axaSN-e1[$us`Q;.E05ryD;`4-rb:L}j~oxz)%w)+^c3G^pt8>>]j#fNfi+IgKH*nD7op
"r~-Kd.2%FYb:f<j!"|LW#c*|/aaNxc)Z03]uJRt7#3TxY_*z9Ls<0Tb]d=)wy,:ww6gyqbG8&1YKY=Qz,.^^-2=>^Q8kAb&rdkrZplK+q9C|22r.SKepAKa_w-8RI{LcY*/%LV4B5N#oA]C(:6?"Z+Flvn-nt]i+Sl>.a,e}3#OE[>X*d?:S-uLMhzv_`-[MRm(eNqQ40N<[@rPkPADzFCh9M>xbOP5n=B!:uC!s[VUw<k[^f[b:"Y%Ctj?Jq+$Ed!K3yAH3+:yhmLYW".94!No8CEwe
8B}w<rEa<.|duYfP1a=)vSReg`eCg/9#c?Z!3>i(i=nQ-Q&&vtfLkfESdXZWZt.!n5I[uS7DXT5KAMBy.I]c3QB(p
T0QF[1`A8u3n@y{GLf:[$?S9`od
@HgN-z"22)`H&oCfC=cHUqHBg=n_oGn5)l&$)D>BG$):j;UR.2r5Z"ol`ga48u?GL)<t0m_``D!@uy0UOBI<*"6q(HCNv(^#qqu;bixSs,kj<VEqHPDl59D8/Pm!}:V90Q&I%&%DTikw]snwT8$Hv1nP6CydHK~n>3_:f`!Z-U#u^DQ9zHx(,JBws""#1_</(`e8!rV;VdDrF8CqqTVk@_y`*jhdh@=!cN&sw5)@vxAd81Sjn;A<T-Tx"dYs`?V+
Mtq-!rP>dN$C@5[]1{
-8(aG-2&ZvLgV`2;cugvfB@9zIjlE(BQ,/MPu=AXk[*0IycoJI05lT(WD<J?~rSpTMVC~8?UqaLqwlny):[c!JQ376<gNxOpkhEF2eb*I;-e32siTK"kW#)O`j(mWoiiDE%O]8aZz>VE_UWL`/+C1uir,L[sYT*q/((_loqH40p9APvL>i}F
Auh$ARb/Zy#?8dkTxtoMpGg/VRW;(%(fRDcO%Y4$>eF!sDv/*.:STI#pl{#_9eSW;P`.7:+omS%`w/38BgDjo-Wqnr5nM"+io-K}H3"Sq6O&TZ-_*hDmZ!=}MG%>C_9E.mrJArS[Ug<;D1.Zn0Tx4+Nh^Ve6,sOFWYf7F.Ia&2[,:;;)Tr/wQ;ci`GixG~[6cgQk?5(XwHGwWeeOS!QlY.>ZLI?a:$TWlr=gZ[gGKY7oR,bTu4T]_f2bpmJ:wFsmRG^0q]uC73E?k-H}-7j;3w=L_gu<,p;]3&u`gy0"7f2`f9lxB<P)2e<BCg@w8>VlK+$9VBn3?(1vmNvrs~dEk%w28d@i<!3kWOREr<*[,xg-/YCS>d/dU44Xv?o.:gm[5k6ld%s]Gg)pym?F,ie,XE1{wB]mbuYDi<;zCF1-eDEnKG^:(BxRu_lprvt>5q>_^)W{1[mLf9UqKi`ZWib`gy-Ew0GT<}te/<gZ-RkuB8@fPGEKw?lANpD.LU2(r*tz1m%phqo&=lbKDhDq
Zp7:xj:[j8dtA(.[LuK
2BTQl<-bs.4?Ew1[1u&,{1FR(QpEn(:rSF;=v-i5(@eQ?[GAQ1Ge~>s7G%PdA7eO|5NFA"Lyw.GCR[r+K#A+ujae1IWGfSgpJCHeY#W>Cdq
p]/[FEP
~e-P:prQH[8/a&GUT]$)ZZ+?ox$=PmO2GwBp*dZG#0i0#7hYf.!3aPh(bTW>o*
2-gHRSFJB(A$L/]Mo<R,$W,qfinMLqeVE#B`#0pCd%:4xbDcW(u7Rm7ooOshi@),g`^kK3CpL+mfU?B,*U&@0xfK-Llj:<J$%<]!M{B~<D&X8IJ4G.[}O[3i[2"``HG(SfC&ODJ"?Mq1qilP6m!XkP`7Yu>@&Gj-L6-)c!VV?~"so/q}&y8ph^x5[/_UNXO#lChZ8QQk_}"~]EEL_3Wi=0v}#$q2JqXsY)*EphQY(Rm0i8FEW<3L"JFF7j`6Bupi<_tzMWY?6.D}[P$;*ojOHIS;ICiEOSKz1OUY
ZiIOv?-<5A`86q~<.
e;=]K^Fm45`_:65Oej-P#Bz<iI=uky$RfSzyFgK[/QYqPK9ReIL$[BGitKY[U_@6}h6NzbL
,ysJ`;Paz*_,Zr,i-wz8`0MvvDt/`e$-BReCp_pOg"FNSVv0`Z%N.x-2_Dd%-rwx,Kd%m$1y}A.kUw{wrr?wnNqn<*
6p_5k-6mmN3bo6;}(~;$n]FO7owrO9q>xp,3pNcqm%#L:#Cio3-A7fk3xQ0v,nezeH_.NH0?<(DWA((,k6;ZrRCu@Ir4uB%j;#&qJr[~8M0l^S2WLQV~*;cQA.vbt})0.Lg
rUed#TLsPRgJN&hnl>UakpuGs)9phY(w<HW$qLi+iVSxr
k2N9>X+P0m6(OLQke|eCDH:Dhu+&%m=%s[,;Aa]ZGQ0/g:F**!>BJr[FO/r{fM6W]zE$rBF-SIKq2cO0Q5xIf;1QNOEGV}o-';case"fi":return'#X/;:bT+^/$2%ZX#eJsf_"YRU@l4@3RH~ZGcJaJ"leMDrDhTA3578r*H@x@B&&jhGhCCj$-.mH[+.OtDQY|&/u*BH!ByERg6RM*G$Ld,/2d]aYeVeSF-hES>o<rI6XIq-wr#DB]x#?sEcicyYb}LRsqHeGjuG^#_"tYO~5k3[s
w4@kUJ1H.YE9Gx!#!#&<8}KVhSMA)dV4#DhfURxs`yKsHza>w8mbyDr4=E#{#>;Nx$&x%+ceLNfQC}+xy~#o%4Cp7;"PoM5uf[=(HX(l9,kfi(_}&:C5>&]_JPIGIC)&jzsBH2Q@NVx/g%8_M$O]t7dPdk
W;H]Qb{fTpzxW?58rI1Vp@
Vy6v$XiX1-<5*[_PRnVMM,gaHVAfRf)`^
MGBmW$:bSd98J8]<iGMd;@b[?32c]B]cF.Wk0N20M.&<bsT.yjZ]Ep-I!>"!gn5LSewH34O/H
YmK|H86!^fb"7Le_M*)dT6[Sx^V|c5$lPII}FO^Q72vX#[k6Lwbys62+I%-;](8|bh9VVR!1i7uX=F28R8:qCtu7[*jA0L`e(jq>]zIbMdlDLs"bIm/a^1)W)~j[0IoTcbh2RhP_JGIc&v0Lp{H4n%OZ3%r3)-:N7`"J(/O-R9l#vNwg1wN}-=JFP?pO3S7y>jYJA[AN.1s&Z:m?^[b7*RtE3=>>"
G+DSq.o^<-s`Jl#JjHQK5O4&!Mw5>}B(1B1`dBD9X:>xPH%b^$kjNV9Z?a(^ZY=(GypTp
7k69g*@%sr]`qG6caHd#*fdIf=6}CZP}=[hW]%KYsjAdwmRA]RFv+iBHCuW|Qk2+oaM@F(&RG-3]D=Jj,:0<E$"21r]E&B&;&cjyq,&CXPZ!3(1OSB&qSAWKC#C<pB=w9#)phHouI%]df+4_(360(Q.W
C8}I0(Rj#eA5{BKJ/::owGqK-dd*:?$J(teLq6~.iw-2K&@xarciwv=HU^B$%/48]NFg+=Rvp+k2o0geAv[bt[CDLC"SB_YZ;rT*.bGdx4pv`sDF25>208wn
gi?6w=hQ%%TIM!yD+DT^OJ[ODQF&K+=>)foO?fX(V*v
aRdLZuCehflB$YEcKPp[7,O-"M)Gbh3@,L,Ew07Yjw*XWUqb31"s",@#bd7))#_?i}n|8{&[,a#yeT=3O]"xDGdbd(Gd@
W=Rg/MS<v!Jbg.T;%?Af6r1I5NZ;_qLqA?P;t0+Mj5_#x}M%lI;(nfJv({F0%VvJn47eLZNReh
b*Rctmf@dt,;r"~^M-Ai!3Q$2$UI6-@/IX:jG&dT}dQdZ0*A"eO3GBVHH*$-qu,d9+^%g(o+%wg?<.X4:NEWF#9PNn"ZW.#M
g2"TwR#E`N*[HOY7C%bN5I)l"3+"UCaM5x3<hC<i8($Zmj[s`!d]d0obk^Vlk>$eit[ERrC*
4^uFE#QRA#`&b_o2Z4Xv>CJURK[<5YWukNzy[w?o,/^?HV34>;$C2k%]TheI:w;Odx&Ynw@=bf-7FNJb:/9Ij!Ie`;)^/m{<!KN)]&jm93OD"Ar2.+}@wq2l=]%P?YN(%+%k%T&hbxT`V>Z?UG,OPx[
wK]_3C1m7O.;SGNH%p:OaBhE-y[v=.#r/qcNeN2p+lm_X$y?AF1tA<8>AsGQ^NzG.wZ]*#C(dXc2d&z:",[KHVNwRE)i9_>P/d7m4pu
b]%kR<jEa:S3D9h%4chZ29?oW-W
@ZK8))8Do43=eMH,1OoXg:]f
OkLh/t^F2s<NK0[x;d"8,lQ^
b8hD@,!IS"0*:Mtb>%nidWI%xVQ6O8)G!i9qNve`)vj><n3_7"{t^C(<@+0",v32y:jS%^-9+A=8[EyPClr.Eg8Ey^:*^#9PBrIdzFKPF7e*W"!<5p<F:ad/Q"OsVFBKAy;S[^
[WlJSDd4Gc27gA_(qgxuKm8sdF*@U4=hA"gYk8
j);`"Q-Af00+d7nH^kCnUurLHVFZ>,Q`^joQB)mifFEm2tB)Qh-(rv=iKSp0nMv![m)_Ab-vaeO7hlCP{V
VH@G$"#Ww/Fwr5F14OJpjj4J"O<645A8tq$:8tB.eHYC=J?;@u5&.^N:Se9,t#O-+L+^vw)>WSm.;/9WCcy144;{q73RWFp;
]Z"<Px,=%WfqK_Jn1Z%oRywZqYwT&`(-s!*t:BG58oO5#NRQPN_Qa^-a>R=n,rSX]v0?/<B8J
Cf02
aKuz<$PLShSA*@Jf>3,~#2_EAHne#KgjD$(U-O^4R;=ZrQh4q>o5x](IgI,"AM=e3pDu<Fu7;
Q<Xp,(En;:-DHNe:R;M`[!(jS:+(%)g+Iq"XP&2;CwTY?~&!
bs{1%Z.V
Aw
Z$~
A<}&+;`U}ZVLC:3@Es}W2mYE5;_f,CE1)RuV:SC63r?n[J3u;Y7m[MjPe#eoU64HEWwC4J|-Tik:AZuluG,:#UoMSZwP]DwOvBMq^!I9IUO=PR<d89(khe%ozC(6+FMXcZ,kO.h&hlIbu1{*?9|BHK[N&[RM_*ZpBU/08g
5g<4Y=*^]X1n8q&(v_
b^3/(GS0Kg<@7@$]Zs1Q9Dz^,P~Id?yVE9trq5Un$vt!ojJ<pO6(!`zl3EcJF+no
AwlbkoH|0W1]Qi@C4yDuYoE$3ae1;gL3F[(r_}=z!QWVS/,pV_10Y2LPABf2#AVKhFssr1FRI$loU}w@?:s/XK?iU%5GhN"|Ny1*heL%tBhlYr7#uVHy^Awpq6^v,=pAm<<~Bu5lKt+?!b@=E}!Ag?FwdgUKByvMlooog>]vkQ?Mk[Qoe8xoQ+l8a!qOhH4}O@C;yPFA(SizQqY0&uDfSi
jPu!.q!"^Ujp,qBKStC%6Ck@*>gC<o"-L>>.ynjL/5T<;TfJkN{Fm]N^KVY`9b@exm-SWoGgyy5vlfhhyO*`N#kD.
13Tub
dbGH%/JwZi?m2H=W?usI*qdUN@=cFkessB0nY&csK*-;Ku(U_rz72/W$.jR,<A#0+hYFbvp-M=RZ);6__iS4;^&eZ`dh>KD!B8V7f%$Js[MwZ=^Eu1{tS?SI+EUc:r8I.MyQEiu8T(HsB_b!v)z"|s]9+baY3XxYkI>%D1@HM=%ZNA_I:wT$jLYXu,mlY2&u"l_
jE4`&e,-?wREHD
e>=:j{rZE5I-dZ!-tO-1:xc8V$1#y**XVgfvqX5.8bBEFnZr=DY~s+5:g=$LM(o_ta$/oo_}LNK7?^3?94(ZFR?)@}E*F9gi4.&(#R&TiFB~&y(+v$y6gl0LHt%hZ#42P-g~*"_tCLtfp&#1As([3?./T^bxv-<3fzWDj.ILebtBsVWF/6/1pi7lY}9F:h';case"sv":return'!Zu;:g~Z+$"5$iY]EX=)@TD[IZ&S8TU`0VTB
R28r!T&y"%-8K(.WBf#_:TscN)J,TtV5b.o9%S?,Vg*CunuvxBK!Klmo!6d*XF0!FA,pk;
qy$fkE3G?"9$@x^EiBUd(=V.AFL6d0`d~whiT3LwP_L@!!&=KL+XN%BQ*"v9+KA`1YwQ<+<P-"P,4*$S^HoJ}4XR{4L_S4(,D"Kktg9@k_Nn}y~kkB5$`,$ABXrwG&EsVmJfAW+%6+8GzwkWUuRl&iu[NvHh<8e"//?L9somR0>?W+pv+.q>Te!WIDfS[7b8ICn*J!%+
d8O7mH+z1m9aiuuc^Ve#0XYGG)h"PPp>[dU,v#["N+TJ
QsjC1Q4c`-<x:L
QzSP4yMb$hja4l3rfN2<=A$8vYn|kykr]l7>EJnP0qU~,3eoXU/RSpQzwa6CudUV
M?3cZh6+[r)GPL}^W#w$zA["M%]xdU1V<u71aO[aalZDL;WU>6<`Q0pqw`R163]xC
(6Ms%@n%ZO]$e
s?uO:dWcI8H@)8EB{Ph9@:H[>qxS$w}=o7b?40:l_r"_zC-h{#ID
$!xM"0IZC~DdptUq8eXn#_yoB?-08dE.3{-TewXB+0@#@xhx(}-[r7(S->O(!YK5k;=V
+,ATgv%6GIf8s?(>1@>`_>t/O_AP9!gI*hS$Z.Fe5I4&w:YT,=E%}y!YZ][[X#AG4D0("-@y79z)h]NON
.@YR[,wkKt)XF6i*:H@j

S3VV]g
4?@$xeFS]bRg=MM,PAF.w1[S"^CGv~*,vU?cidv6T#Y!L"S1#]W]A1=;C`%$%9+OZNSjoB4,p(@}@3wSBu&+W8Dpb9]e/D-F+;-~EcZ?w"gc(+$oWg
Ko(uHh8kTtD2Ql]_km@aU2P_m($2ACQVq6DecT*@|86(Cn/<5wk1ZPS];ep*c1!7M]K!dIOUyeZ=Z.ur<(1?#_yV".oOYV+&Hj
KMU%Xf"jvW6zOcbcWN%@ShougrobhHShg?8Brf%7ZS&^NZ2TMWx[Ib[F)QOG0l3efxO0a*L>llG2
<6O$^<8V5<rnN;~"X
Gt,)qf2x7(S#yZb_T#
l+cunZ0,9#d2L-/[dpB%a:8|.:@;*xv:IcF?,OqDjyT(Vtse^9kd/}:b.yP
9Pp}N$q/7!2<p05vbA$P?(>3LSMAy>Kh*[4ns%Z)WV&JaT`1<NlW)(SWCP/1yg/%(lk55KYIK;s}]$lC2K:rnv"kCTY(cKa<@(d!v]]E5tB}-1-W!Htr!orVA^XH$G6f@/][c{WFAOd}eH_y$&T:5=+{(No6rJ;%MyPtcF&4.zSM)=ZQdEV`48;DWs2hO/-(s,N&SKr4I55H=T!W[k^[x/=&x#oWnj`]U|IR1m2JixB~YUFF.P/RhE>IxVRhdeVURbew^(Oo3@EoElMRZf$[E%);tmAi<4Zh8m`*NAx^RBd+>!=e2BI!@/N.#tX3"SA&fP:nOzr;-z"OJ$yWN.+1(4-NKM,*d<hEIkRV.?.87R(8<@Y)vo5ORK6OHlQLT#%k)JCrLQ1[;`n"$Yx:yyE3C;:zH0Vo#R.:9:&]QhOLynDk)]DMg@8Yt:xf+4Fy"(WrEv:-Zgnp>n+sa8b]AfaC]qY~F"7q%a9qA5ZluRWd(+CSI#AI<
!R;"K_quE{Ou
PuPCIC^frf!1/_2O@M]-3+J"?OUsJn=^LI6yLP?1rm`JygOvLG8m^ITc
Lzj:N>49]1Pwx`*+9"q|eIF{*^W9eY/K"YSuww:Q!<.&=Mvx_oNW9X$:Tg0f9#ICtyZMxhU),WRB@AhUC:0Ff?M`Fa^>75=RIPlPs#wO
b;HVW^wQA/i.&<hE?P}bi^oBTOhI].8egj9nAZ5^|orQl=G>
Y!3>.$VIb!Y:74usG@Ssywc4[Igqts=SPH:H@FbsUiiB<(L]</Gh2*F+[3C4tdC(?gh1?1p*.h3k?4^
SBJh6|tasv@B=kXRFNA7VQmKPYEFCBFyK
tq6@Dh7?Gq)V?J5=G;<=#P
[*VmS"8aDO6oiokoPRcOjMw*).w;(A@)~=@`0Q712[c[D.n`N)ANja-"J45?M,BI#d_^o_~!Ju0TZVOW7bn/kUB>!ut@>@Ory-E$l
nsE(
uUP}^M@q
mS$sB7$:a7YDJRrB16B*b=vai(@O+F`sz7pp^yOqIN:G=17k.3%iFi*fbTcFAF8i^gpD-G?*#oH4>LV02_i6%sYMvTpmI%|"mGgOZm0j[O!5sDV=4yS6}.AyZ2K*l_~q?u<m2?)vmC2V.Bd"Mog8noI/>E{BwRE`
mZL;s
Al))DlIW*<TC2/PhLk8w$BWb$X9`lmg8tDo@,B?w@b<"ZbMMstp,FJ6+Rn$3a`EWw1xv,Fe>yCMj[Ks&-+"o[?1W@
X4i;xqONu+@yq?V"%=4NN/67Mi*u1JZo)xZ|`#:,OMN>G4&%]w5uH[nfuS_/XT[t]7hb2*tGVFl-SO9o,a3;w4CC)zLMR(MxpjQQ1MK&*d`ic_s)f+
mwn7]"l![5G=)>4j+&tBZ`s0MD&HGgq@d<2Gt>3N<qq
N1,#Pb-ogB)k*D,pAe(m*#@Anj!6{-_7^M^p9n?t2II-QmIMT>5KLfjqCTgkA=9H{,rhOIkLy=A*3yf4w5>z!3/i65U`OS4.>-Nm}ikcBCq["3FW4I{72476eOP,}3P`jj-4lgRl)?EUb7XE)_Ewd9kTQSBK+/V
DtMDrwuA[/=NZ%,d9bx1t/z5tj?^I4I?k$6jNCO`)OA=-t,lyctgFj[Vs!s!{mGvBN/V&ej/!8<@*:lpYFOGB4/S}34"<Em2](21X-i2U-,ezdH.jg&*GP
h|kxO`S4oHs^pP2dtpm)yf>vbTH3e.Oz278ilgn3KDdY.eGK]=vw7mE_K4aGLFIt$ocHOM/$g?8Wo/ZQ$(FMEjMkkDZ8,`NLeN8wcBta9uajM+F>aA/Ka$
fyRCIkk8J1];30urbA70JA(9r$A?>;y_A7;/77a';case"vi":return',X/;B]A.W1*^ArU1vC5J;V9mgLNdk1EF&5,MbCz+JlJ
9:_;W[~td7bV^e{*kP-W9Ge7X0gErQz)s<l*I/(Pk
_ve^xZ/E8GGCT_^9z__?&Rcp$aXB[uf*+U=rIR0yN)7@{Qa3f^y<,HZEmP7W@.eCyhMv;;J+44Gu9aTS4!Iy]H29YyFKG9F)TiNvt1@w&uTb{+fediHAzeK4o7E^AT2LlPMNfpyw_<h7T10lMVuWGt[R,
AuGo#m@F)=~]dy2yk18R<2N
,F(y.2tn{+GU&^P`8]X49t:94gd`m?(`Db$BFqL_R_x).NY@ng$y-%r
)G}J@I2[uh4NAxu3fA2q~_,7jDUHNr/;vN+sqvt,DXJO^GB-COY&fm30B^2Hgr"G]0(8ilgj9k+uD(HEevUD;eQ20MfC;EL%;^^sLr6vST-U|r/_,j9E]1jhp?/ytHNB-54?NMf-zmT+P3vCsF8uv
fROHSRIy}RlAlw@ZU7fv95,9?/x7d^7GXQ**xyqwbyx,ZR+:q@X?|?CtAg/H9J$1BtY241hP3@Ka<Fuj*80[s+aa!ngDW#Q!Zr$XHD?oenK_nTM^%y*+{WI^5
|;XD}L;x#SK(1^[]Evwe!,q@6Xwe4XGgrMSDF),,Vj&X]B+Y&Ski5+xrjIowWmHgLfvUO+gLG"v.B%ipnYx(7VREM)9r7q?onM2P<<`iKMACwWt"fk/J
FTC|lMs&h%8`$d#uAQm2YC<<HEn.`%v)aUqSCkeIP$:?1NkCnEfWZjgHFaxsj
`T<2=~Z(7DY]
D1},1eWYoA.e]mK
:#z<YGFcUDJCiO.Od=uJ6V(G-ei?/*2({[`QIQFVz[UpD.3
RR^^s9b5Bj*iNV{d(h=4JLyF=&aU5nZJ6LJ9W^G`8C"dyqd5L+Neg
iG?S#@R262jNJn`oAcX@b84QrE"[S(FmTX^]%qbU*r@Q2ff0$"<>SxydlvnH2[`?iD,F8j%Btc]Px2k*wfaxqozSCmQmJA<n=^WS)QqQ7k_G.EQR;iX^@+Q;nBBnr(mr%<_Q"2Pu{0)DyS=;%!2]W@#sl%Ro0E#Dp(s<V]x=wpk<l=J#ECqiP()oM6i(jOm!Z/~=}a@f{biGo
Y#5Y
g]1*,r]Rti.4&PGoM^W?hv8&,mDGIVT0O`;EMb
dcTje)uODM_$hvN@&97,6atuH;g7x&g#92.p5+8h6/U(69D:(<f0mi~ydT1y_;8RWPbSo:yngn|k83@TVp
E2NIB?!A^nDkTXy.*"[Lo)o1m}(Pk2"fH@H4DT_rVL!N)Vy3"hA$P3rpTcTYRxGgr7P-9N4F"X$:K;cNV$r"m3TpAPE9n#k>7%VB<)iD.Fr!y^H9<4B,PoIS1e[mc5fasB24KG019MLcE3fSJ];#ivOeyNHo$Z125T=I]YXX$JqpQU(7sy/:Ybn3OCZFR^;XEM]2q$:jxS5"OFOy#l37mURX4STURy<<h"g%[vg/*l!tXM8Pte(kjbSU`[IPb#]0AIMbsQAdudrhVHtxs7!oP{GiY:G7[(rlpH?q7jFFLresj&xJI#]E-8I`c=nIkB?K&R>Ez)
zw-4q.R816r2U4.ymyN7s
Ktu#qHk9_<M1G`oeJWS
Y7aJ941C>lu.4l6-jBvPyb/r|in-^;sZ]"/OXrYv=/{!An-+i"C(KII##p>;PGCq/.}),Xn[6f<UDC1,nU<IdMlTL#O+<J+:nVU+=+$xs&M(qT?<^5M-]L)s<KU?qQomeb=F!-D=;GXiF2&V%o&W"p#RfA8cLlz@P#uGs%53,Df.V;X(DA:1@*7.D1V_+&t%u7eJzr=jY;Q2;U%B#>5@"Kg9H*88&Z7.h"n)EY[d~bNbMxr.~QCIK.n@Mb25{vhth8z3[E[*p4`gymDY(R%L^j7/zTCYv.acGDdxs)jhbt6-DIUs$B|"~s"6sEeWT6NI1s$wkW"2vJHA~ZRphCh&}Y,6gG4/2L.FeZ|PvcOZDO[9Cn;Oz?$x+Ynos
n^+X@.BGl)XZb:*R!BJB68UTAg.^ZsN"L>T,x.zgbe6.l>6y`C
O]U6%LY!4}WX$"MK./$VQ.?(h!e4[yt-1{,e<4Ru#9A6F/YW[1nNeFc+>6,u/yQ!]LPz;lMIWR81KN
yiOb@&5!9JvQ.r*I}Yv#_HoV_hn9.5[pOFR/Ucswu]BC:Y
-c!CE|Ufp%GV
v:xi[L=Bb-4:jp*lEmi+lbm3)`*"olPWXeA-*p~!N8~I0Fe<g_>rce{>i"Gt>T#?-:xx|r}`njca!%u01"Sj3&%#8N7m`=ynh>F>(R;*=#H.Oa|dT4N@hb&C@:cEkIrIOowf.7]9ptr&D
"tJh2<=vJ>J0d
Pb=BMVK$NDG^rv?$(<9Pt
=m}-}IRe#ACi0_$b:uEBDn@.TsHr:d0PJKrUcj5=hombEc>=[y)V_m@QaaM.RBm<wt|z#Q)5DIprP4X0()HB2InwQ&-7JG!UEh@ST7t+^H<Iyoq-.=js:t
+yLdIQd&$mYj1+kV])l1f:-`>V-QY+b_Pu_cAH
}<}WJ9Wy@"^OKjOWhR8/2;ytI:=D3tQj:k*6H"#FCbAAwd[y#5;59ObJQjU-m+xerqq:wMu@X@Ty$T&U)T,uo-v4SdXTgBJvy0-+aL><Lp}%+h&W`IyxvirQ?^=N!!Ff@V>50+bEk#x2c"/S"6
/|/#
iGIXxnn$^bSZ$%BJ#WbO50Y8?
otfP3HB!c_!0$ihKI>{oYQl"5F)K5/u6;Arsb*0o4Jh&rj18ax/K(//t.GSuUDGeR2<IkWb%@;fb*)/n,?P#RJHkXEvQ5iDO}/e,7&Ee1Ct;A4MsIs7l;,XU|eBnUd$Qv&"E"0li2iQA<YX`0(4VhtM_6Kai5?WUJ`[6sFINmvPAZ9D
^%&ad2&`vyR
tfno-erA."Onrb,4opa3.6^gu>
B~bRPz
@WWE:XXa@Qc
"WxK
+1hz9),^WQ=7#q!9E
g}*548wV7;6Z@sG1tGMRL^
[y.HnRDT0n0i23FH[ochnA[kc6Ca*3_s!5`L{4mnN!yQ}18sg(I&Kh0Asi3`;FF%0I@pkx1g
bB:sS]h_[V>Miz#v
R%xuI3@"B2Gy<pd60LH>5r=+8</BEHoakkWrUgHu*=ZlR<=
GX84-YLv&CvI9@KfxXh>tI##B<9H.
"2-^h"DF5MA,vl8WjjwVXo_=yAi-xJ1e3?wy?r4sDZnN:M?
RjUB^R%IH^{
>jUiz:"3}pGV),?+W]&Z0U%^C
WMad>qc/~>E
5E//%8@K.WL@hk
ORn@d:.u
Gg4;+51In82(-b16z*9ho0y!w<![dyl.{3qu=(Jg.s_q`dFKVCMbz6.yLVhP&y`/p.mJ.Sx_8+J!7Ux5b?X>Ae]bV!=hXjC&&nzcP#YdXa/Or[ouz>_#LUMN-yHo)';case"tr":return'(UF@iaMD9?T=Br&$@PmYg&@GGBE""gaU[*;eL/i@v`>2DAtxlMI,!wAy[?f>a@r,UsAngL|uxt-i:%ngrG(@&s*Fulxw<Qyu2-BnkfitsO0rj@92rI17Fh7iM(r!X",u^t%TUFj+f.
h*w^J
GPJbw8F=R"a%Bzw7vQ]A(th[B:J0;<cefl]xAn6]U{Z&)AZw4VtuX88.`kLyF)k[CJTDlbsr;(N{]|Djk}a(#8=7"xg]qlJ<7vjUAaXidWKo`
V1)KX+wP$G-~%1jV],pI@OXewo#`Md`x,C[f#[ZSULIGC=gkT[2uK4uBX`=Awr+6>+b:U
2tpef/7qtKVw7PwUkt?5UPcp$D;0+`1:_9o{UQ24Ll6-!eN{q->m7cbE!c=Rdfec6C_cs@$FsIhMc:u%uU1{$yl[yP*006a{-`vN()Qge*W!@;O}aGkp(;i;Rzq,`qM#kD/cMUYbwgG8$.6[%Ji[]AbBd&E1,Vyg;B-x@d"Dgw
{xb5l%och$PCdGt?{_sOfWRrI`:!IQw;AOSa_z("Zy+_N%/O{#&97xeNml(ur78aKXE9$[CFTU5=G
PoZWoxs*4.]!FOF$S!z!<d=RRHB7]gkktlXn-!z=x)il
_k?l@2A<]dFj:P6^&ap*v(P5n>Ji&qH*nMQD%RE,t60;-^r@mP,0N8;]tUQgK:eFU"+Cxm8.C+NXD:yZK)C=-,c90zZexI2&#Y6)pZg^9A*PWc6A4aQ9J]:lsP=C9V_V&RQC@P0/%8W)jX+)0F@kA9F$pnH:LI`3Vu4n7dS0pO+IHL`X7hTWfgCXpClM3gy>4H#y(T3CQ<U-%-c(K"
@[XYv?u`f19:B_y1|(OsxMx:uU(nHS:eaysa)y0*vOPoI;/7%K_=8>fbU`IHuQ#:TR1U5jNel"t_Y@GS0(SaR,`E6EWkRd*>me}e{oiudOKGt&RtjD:73-sE>]QgAj1(fGCQt^pR78O:9R5,A^tWO<i9;1fHywR3WN-_2^6TZ#O3Fu|I.*-t~Lo3BZz8qc9^S<{tmOIl6*c[;5Clw1d]6-[`!R
t==kj(q3Q0Qk51idI+v"8/s{!Qqo)Y8~V~kdyaV9e&z)UxpmgNnM=ajvyqq_/^C}q.z(H3?@Uvd7w$exMx_?p_o>FIO^mmhnP>R75>Zgrt?Ya<!1wK@Qf]_<?30}mcSC1tH_:IDl=U2EJV5PVwRTcb=$X]%-YINGyA;=:BF~hAS9AsG#5B]4,|=6
2KlrJ330]EHDa^V%dH$
{T`K
_)@w$o<`>kIF-2G7S-$Nyg@.2e_!F[<!#.tbwQ
N+x/vrcln;yt2!k;"Oa!z
Bm-VYE5rhO2i8lNA|/?,I1l-jc=De
[!q("?!w&13DB=f;CiI0diK"jIW[]U7V$>F5goOb#d?Q5bv#}U@ct(Siyu&+-a0y0p=TypFrp#Zi,nR5$/+?ZVh/`vb
=aBM%@z]"TEE*1Z1.hcj[Q_#blo17k]$umL=4_,;O3u!kD-v1`]h:ur4+#M"Gf10CCeuDgRI8j&,~fx$8SU2OS|D
6o3|yIZZ7i)Jn/H]<GNdmf`
?,u)OYhSi>s+CZB35-=KZnF`/(cy@Zayi}1;6ti)wQA%<!d=$k^i"-,UOD"8Q
;rQt-hS{63kiIXZx"5t<-7;Ungp5.AL4:olD7k/EZPT/o-]ML.Di58Kn5;dGZrn$Secw<$EPjd<BC
NsN}?@TGO#"zis[p$4Wb6}+o&z<Nj/OFqRw{7t](!yI"_3WTl3`SN>8?JB6bvOr59H-/sE)aT)wltrbZ(WKIU<$
NJ
GP3Zk!"
sh~?~2NR0d*#|<d?coedL_>>"
qS{O}w"Z~)S/Udl$S$zw%8?!iASbT/=DAM6QTgi9(nW"D+bOf-j[=^<IQZqQd8v"4Um-TDkyI*fVy+(/wA_$*%*,ud0_@q_$7K7%~nFOrx~j@kP[l<rtnX77w@>1iHg)c=zz(-t0iu97NIaI:%opqPet,SAZsjW/qpnU3xn^]N).`qI%gQ*ojr">7NOa[y7Bd/tO}ly(7kMRjD#1h,nPqGWcJEl1Q]aXt>|X|EYPngx.`mJ
(l:%P8xT;5Py7Q
QkyX[cIIg}e[(*@U/!BpW)>zjk*%fUtE;n@y/j#W_qZTdI_H0X[<vEXv:#C9AST@<Rj/BPy3Fa;Gg.HdS`P/v,tEl@AA`@.6`t4LEHgnD/epK:jJk<d
mrD(Gk6Xgt-(xz#_-V2vgufbL;<M3+ev$B[xMVk=!9Z.Y(@jo+VC7}abxl4bQmLt3td6I5dq,!+VJNXPBQ-/+ABrQBlYFYW?hFc/V%`q>OPB<)VjaX)usf!jXDjT6
h.9@wH(a`;o.wL6xkiEz
}xEPVCXL{7<Cy)-yj);SSBPixU!"C_fu^vz.z0_=Z8^8b/Cr:m!H0U5J
4lRHZ.lDt3:j$LBr^sf{o[Un$Db[S.(zwJ*r^,4|o1t^[n*w!@^y.v/`#89P3$O]7ydq)%ZAZ&mU"8)0Y2Qn?{Ax5Vo)SaR}^|R|IWyj6nRso7d$^LN$[B^&hs+u,y)!cDJXo1g/`yTX(l)`&Dg)9Cmimd@H8}GWo;8Z"+sZ-s8)YjlOEK8w599X)fCzs^W[`;
N23^i/cL/W&u%bt>]C0:18u0[l<GQ7pRYrFm?gQq3+^v[)5P^x9D`]EWYOE.*O^e3ZBWWf}(9)Xi^Q}k_C+;%b,%x@J%/>M.P!Mk&,&WfO&oS]I8e_(i[
4YD/^5F<M3CJ-F]=:O-kQ(~i9W_[m</1uF,)tKdO=s42di;g&^$=+T&g1<]])lMnIxzD!v"H5x$WMd#P42YagBQI;1g#~m<[^y}C-gv%%DcoQF.?!^y8KW,FZLi8bhbuIORtT/)J^lG`Av0%Ff38V6+p&8.tzj?U(6qPCf.QCh9PHF7.8%tv
P0!I9,+1uJ#Pq51/:,CvU,x=s0s>a|Cmdw*rTh3D9Nfz_3>)Kiy
F*-`5CS&1g*Hy:;1V<cGhHTGX^i*;6Cnra_D(dl9kKd+f:N.cXRJ(r?FK{TL@tk3p?G=@
/L%Vs&FA[n>NRp6ra|u
1pm9:TEp-SQ
ZTYq8wqxZ}prF@-YEz;ky!k1FKc>6r28N^W;a%&Dx:z(>4lZ#-;1FD[}L]=+m8m)N|?4!`xkW2(rB!_KOr`U..scZdafXE9``
Csao*Wo,mX.tLV#g^Mt1dVo*^]eId/v%sZ#
Ppt(gAI@LCQp&[(.9v(6M2qM/*[._U.i6=u4x1h_I0VM,aayM["v"
?#K*EaeK2x0;xA=>asiqL9m_+$U`Jj2u+:2.#)&TA5=GMC';case"bg":return'!ev;BbSpM?VH!S^"%hLdj8:(l^WIKR#$1XR4tBs`9aEn#2(mPEOgw+(-k`6Tm#q>Mfw7LL[^EmGT*D`&`^{4c
zyFuUqJvqJkh]RHKcH&nWyt[InLjzc{Z0oD:6jdYvgy;gL"BoJhP>l=]ZkW2[vpk7^F_RaeYx`1O~6~n6)}X.l>^RwW#U0eje_m@+Wi+(^3B.;)@#%7dvv;jk5Xr1sareQe#/8yJ.2w,7y#=N6#/WO8tLl-4(s6H<AL/A2Vtd!Q"9uONKUQa@(L#s$O^6XLB>r!l+)WVy_lL/f}9:"hHfJLb1-2yz7ut$],N49bO<a-VH_L54)R3:U4cj(Tu<Fug
ejU*17wvuJ5*]dRp-w/*v?a0pMqva`_}Z*_)@$J=CLS_VKuSSWMlC_s,9lL^0-ja9PVhiD?h*]H_vggy0(!A!E:C)GxGnX@EwrH5P?8nC7Iewv]<ZR=32(]?3%;-.zeTax7FiW3ZN!Ar*%B2!r;[0IN*sK=.BO;:%M`A!RGU-cc9H0+h3D#vC[*jI</HfYpb(d9&m7LFMOLJ@
)IHvSGN`eD&[7cJrT3U]FfSv&.AYfV8sk^[2Fc`_LgqsQ{LvbfXb0r>1.,9lcoFg#c6{*PG>Hzeh`B:3EWD(Y8F|]G3zTwO"AMxN/OL$m>mkaZ^:yxb::WxfymkO!,
u]Ee0y,qV5DS
j~LW:IQpC,s3Nm$7c#IOKj04.Z3(89k@X)T,<-_Q7kRr%buF*XFd>)[a]XYAnW/E1c`[$3Zkdh-D0Y?HC4Qt%Vdeo-rEHz?rtm_"aAj]6L=K)h6T$`ZW<S57K2"X5!Z6Q8n0w-R.9~K;?RHTtqCp%NS[sFAusR^`B!<NIyn#VoiX+xTeke+bE)-/bs.*P[P/j<nt=fX+WhK4IAXk:RNd%mx|7ybw
<"+p$0"ygS{-n985OGgUGxl.B+1:zE.YNgG_L,:=$C0R,#;ka-+.La4N3mgyAlzT(gTClX()SX0:tZY!F;;Ycmk;w5kB|^of,/!R!`,??g"#SkR;x-F8X+i5p)-b9Qv#7!wZi)U%>O8C(*RDc%XC4IPmh<%TfCteTlWKH5DZyZy8?fM6,t5#?iRU5k^J[DZU6C1W>(gP*XytROq7@(qOzrqjP^s-|Y)F"qyVekZfF7,""Hjw#5/WVT
T+u3up3Fy]Ptd/H?tmAKfCPs2?Tqf1O,a]uE+.#k;:q9&oL?3`/-<mu/.`Wj[VSH4Lp9fzfr;>WKO0]zduyCS0#c,bTZNT92#9C98eF0mMDdg
Q`@]9JNR?`;f[^5I*}cy[5jZ5zf?.Fx2CH`>K-KjR/c"2AeV/0"WqQtm,88^@MqaI1h`B{=s>qYQ;vdV#h!~a{M]d~7*
q`fTs.b:5:M&N!RL9-vUCb#wSsMk0iMs[QC;=-F6L.
sXA"Wd6s>="G#_]{t8gJxm#n,{&3B[$Oox)4&`,P(<J/j88`F[,^iJTP9L!n2h3GD<_mGbac5lwG%0Y]vT)L+,.865a?rfpdl~36S_)=:u;sIXust9]0>*e7msaK5zD==XJ]iT0%3U*UwYtsDmTN?!,&@"?+Gx)9ZOqDVkwO.R9IfztP27jEP<DaYoV0@&046_.WXQ@;w@&J[xsT<?r3h3x4i!Xl*/5O7@bnnEj".6VkWVKI*H?F
Hj$4Ib`!>Uol%8V0&,?I
hEp5gksFfju>w/"?J<w#pAOU@[+oHJ2di86r(AC)UsLtJk5
8Xk"3S1I6qcDl(-(3_t&w:M0"lD(sd7ZseM~u8.7=0V:d:5UpXl=Y+bv(DPkZ%9;pZ&ko>TNYoZ6fSjzxMQQbuLpCW8#";py)WfnQ$Q:dr$5W[`0vbG&CD9Hfuo]RK(p2F<R=P.cA
DS@E+q+353,jDznByiFr-s?6uQxW@d.pu`0lM"dcoNVoG6wn]+7,EZ>aF;_9;H:w1*<b9njB87@h45M>"7L!]ZO-;dDmL1)cOme1&>B4/s>;mXY}NaXzUah:+1N;<^Fzmw
&$P2wMe:+8,C!v!]-x/qo5S>y9d3I-?aj]71
`5M3g.Q_fx_:0Esa[n&"`VAj*wTnQN`>MHnPtcH|<M?TKtq,=4+ZCBBQc+^h2#F.F.J4Tm?,?N1}aJ]#*!"I`0H&C{d;r]K<Obsp)05GCydQ$y*~GKQuR~;S/}wX2vJ"A)AJJUGn!}Wv1Pwy2>BE)m(,l#o*g&-zT8?|AT#z#78V9AMlB.@hF2.aA))b9}Oh<(0;q<qGDSt,%IUuy[Hw@0w&e@
0vY(sfocMe{1Wsp;yZAM,F*1--_W5w];wn&u&F
!t$)eqBDP8GU&OlNlyRkbG]@fy:8p!b=v)R$W_*zp5EsZ_oQx)p9T3NK?V]jI.jWOg=-xs4z2D&n?]QoyHH]A26Y0AfA>aTN>un?:UU!uo$s?,=&8JZwtlK(W?fZK.
Kp{ef,T-Aml,4kG7!<
9im"wb=t9$m1Fl:$/F4&`>q
XZE"&
t>-;n^F

Joxpke/n5*Z&oRpU1#2$i*6:.!?7{I:_?e)R/8hwUv06a;~MAfiush7D4UEf2!e<@,__1^75eko9@0fNje|`?K6"YA^vCuRG2q|Q-T;G}4%k-N[D-<v-.@^QwN}m2<L0HE(uZNdBVY6LA%,!5BXy1;GVdL~tzC6#0!7MV$mU`MK+<*2ddktO@(h/~wo6y_j#)a;u
;"d7ps8}?N;zO]NX]ZyA#MuhfSO{eUWq%31|iqHqCVp}O(&Z?jv/1_e)qi<wMmdHqw2xq|sg/dBy.erU9|c?51(`Ny26a!+3VFF7hRCb$a",gB!D;S*gl0iP+kmHJt.*V
+gdU7*K>5sp^H#YwjVa!
DO4/9Y;:
P_S}Q&Xxi$%:8eK<$qj^D!j2.sDyO&7G>6l>yb2X:OFQRdR5@n:8GU]L,No;/rt
9:rF>G%!rn(g.mXz@PN4N4YNlUrhA;9%lljes.VMP8i2-v^oA,a}w"VUG{.g<b"3v[hd+EwDZ/hjn<3
qQ1)]BF@H("@9M-CXk(qGavrVkFVm=RGT}qFmXN^;
jJN9MT.Zf7d6NnnqJxtf[{CfH[;kdr.|8[8vviwOH74YEfS]/%0ZgbTz=hIhLv"6-%Fk2)&yFGD*TONz2uOL8L`QR;E^hG^nNj-ekx@4pA6Ts6FX9x/W`>DB*=Y+CNe<AT[VqF?
vhkrS_I12LaI,X1Fwj;9JC5}i+:$ag1RFc/Diud]9e]et79A=EyW`chao:iqM]L#VY./xF=lL,QMA7d:+N4&TZ(%q{ljnRbJdI+JT)0i/:UqAS>=)}>*te;jeC>_Y~d2d"FpJpuP!zOQ8
a13(_rv5FZhw,gD7;(n.Jpf"4=EUOs2V`_]/D
Yn&c
9)69IE&EURG0urs:_&G,kLU;f5~Z2"%*FDW"VGCL(P#!t;VIcYg?_=/1;qS/39Z!P[`G1:0^AT$TqZ}D7MK*Xlfc&l9DieM3nV5<5@}h<blB-.:L#8AK>(0w_2*pX
mgk1uZ;TV2"Rk%-7=80d_;RsDqf?eZ~y*GV2.Ru)$&Lp2wA2L6u9f7~d9jY[lqyJ_2xqYl[%wbcUOv^w86khd&EaXn8`o]xu5:B9lsf04t1Sy7"@zkI08`TPuu^9wD4#u(rp0K:Ztc])8B%O6YOr}!"gl!K.=Wj9B<j&.q0MCKkCX-3+ZVy*"#b+L
FJUUmpJeQW?^JyXtBqsacQPe~2%%Of$+;;U%0W5W#T_t:y"`Q_Kqy%V1(g@viGwOO:#OZFcfGQhN$n@=EpK1n<rHJ`Qgu15p5e17V5!_hj@H(B]M@_wN16XB!`,ZDDqA0nkD+6b3c0~2KnBMly#%0f{J&boSpANYHW"
`WW422IGZtqM%
(+=7PoKm,grC+,@5HqlXmEFa.-_hob27#nA8u>I/hO$J[66VgVz-Pm4wg43m{>_2,po?+kysH[B70*Gv^!"r_km/=g8lwKU
D^Bg_45:q1ifJad@$:6E9=m;<BoJ2F}K:&)MwTI@zMoi2XdpDgJ[l#e';case"el":return'"h_Kjalp])R4omo&zs5N1._"X;GQTOh-2"~NTu_Fw9PT9V^731P)&9g*hi9l~2V
T>ZQ&h;h_wj?5rimYw>
?E4o;"R^fs?y
kG`:Kv!LD!HGw!3"
mcJmS(aaFmKEJcaiUB7D!Af,kse3^ByoAnA@RVIsg+ghpVWR/`#3=[j^}3A+x@Jk[Av%yq$KtmUjQ4emv_fanmjA]q$f3Jw>UkSm:b1GQxQZe6O@t3kr/RGbyPmho]h(:3B/cxJPcDOI=g@U1n:whGrsKCyg>$GIZxTVOm|4XM^gA.5A.Feoy(6e|C]4NRN;jecG[so,XMobZd%nIypDPodj(c%tCTZ1PkUs>ji&Zpor`NF$I=8%mbY2E$sGu*,f5b~#jCX79d1TSYPXN/Cy2n(]/_Y@$SFKO3mdYSY5IE%9Kc]v7#snO!!yB;6x"=%m>ag0J5K
<wEby-]n$Yacc"5M2J6wl0QVCAM:/[*V2c[AnCKgajssKVm(`c[hy!8ME.n+~69rLC^9,qgGHU>$O1PVwYnAJR2A@
#0FdcK-PjKeddW^2FA!0*B!$*[ip#8SV?
AxX=BdhZU+B-vg:ctqtSDr`B0&kmn<TLY..>T?`2$PR
M(3q
70rjr#lo4&B@>T]l3SmUGy#a)9%l>bZQ#}3"^o[tT
-g&ZAW])vG9(A`hdqk/{cAT<L>]1Pr2DciEG;}c%$.B!?k]sZltSg@&HHxOHEZ#Zi%cJ1?]+8?^+6PaidA<y5fq;(KU*cDTid9re:n"nGEX;P<);?3_z
V>tF(Cl;b9v0xOkh,XT6kIa>v3Z+M!cqE#:hfV-U+K2mccH%!**g<tC;X2-(pcB=r9G2!^
vm@AkGwU?j:4^fEGyg.i?AD#"Oey>N8&k_SX1T(`2DO/?,Wet}!ss7G&G%;y5pYR;^Z=.8CK&w"29b7=YYlB"S>xNH&F_7Pq4Wh=o(y^s;)RnngQ`)@!Q>Xrra,3*--7nz:wmS
QOo(e#PeT2b9IU-G{<f/.XK[g&=3daEZE[viXVNv&
mqdp`aeDa0Mo7d,_I(y!p1&]nFG)qs*Eufh`<qhNf<*->Antfv.Zw*6vD0Eb$$DwW]6<;`B;mFRd}nPf2^&>iQx!"^?.}!-WYuL+OUJZVnBu8!ykz@lAgKzbTHHYJ+5gaMehlX$trrH=Cd*n<Fy!hL}x-I(:T%q:~:Mlq@]nZPA"LkBj
15Oa]}_^$]jTEJg$8ODn-Uw.$A?Cx3t*L385D,w^rSQU00:%Xl_dl9P9]QdfO=Evg%a59F_p:~%Pn/H)?#.wG
O%8.D
tcM60-BuP>,?QS

EpN)_p"~WMY`?R!Qd3t`TJ#zGy+JW9$!F~0&Q?o>ldwRgB)wb5Om,0Zf/eRPboa>x#AXr+@lKsnH8O[5T*eD?WOxLOcCJz7`%C7xmm3CJfNu4/?[]o*(*8HM
eC[2OU$9Cf[_245JCVk?{:o8?g1,GOg[Jp3:E/"iD$L_,Ko,#Ap8|X51Hwa>fkuiU?%RZltI+MCh.^La7mkt|Fl9.u0l(##pBDsI*)TK*i}ANY%<RCR"sC/xY"XEy,s#YxU*I9=qVE{)OLDokRg2mC@%ZA{E8ltNls=<KTgRr&qo8gWx[=U!Ny/dhCmJpW)Wa.+Hn/L19pv40Ri21*o>ws.o+&7A!-!8M)rsfPO$E983{6Tc|sI!R"D6`-Ytkh0
mOJ]~f))t[0xA&Mi<$4/{XDI.;T7e]J1y+7cdI+<jd,>^nFi0?EqRe6Hoq.3jcKTtkkYQW:0j$lh~2t-75I#1fd4~`9S4lRROu4pRn}o8E?;281Aq/gf2k+FAo?fx^,>}96d!/gPO$G].H`1wLwOYk(,?y-H!d$Nq7,:}w>q
/|!nf5dXUK#.3YF`??]FM.t:9a7ZQD?4Sw
G9mtc:]"4$4:n&6M2MD]:>*O@8SKOf^6wd:<^g8eLw8B7,%lis{VOomZd7b0McNu;O8-FqzR{ZR-i=Wj$+8dS4sx(^Sw_v3%x){6p%@<Mu1:)N{:}JOGZy16j&mvU^O*[oe:>W-nVqU/r7QFX&C/bW[`My8VhSu<Wq6y2Xq-crN+F`r
Ke?]v@$B8&>s:0}UN0qmi=9I&Q#sn3k6$3x(%BEN/(j&JW8-7<TT+M,g<.vB1W2fAS]1{yF)jPa#)+_$w1@1
wgnvf!GY`kO?ks=Vm|6KKitjna/5%I60VenZ
hna,BB+1Lx~
d`P-[&fEft(o`-OcGQ,3NraQl&g=1(,B+Nbj3_C5=r>=eE^J:-;/wEt8dimJndLb/IA>+/u>7Ne0/;;R+Ud[$Hgfy*ms+AZG&dFrS-]*{ds_yjZfHJm6
5#cUOX]-Jg@]$T/?1nE#_qs?2(%NkrUEVEpRB{D5IG&t=_ofut(l2!,d;)UT8
FVy(;O>M^:A;,$[H]v+=[OjFA~-@A;4BMQ>yi1qNvunVw`ADlu"m)_**Mc]9YWvAELp]s"897*t"<Uu`j0ixM{HW03u:&Os4&wk,"[p3A1_sSFIA=-$R
.^F7Q?O8+Fq+PcogDax&tME
eXFL&m8,P]k$C;(V7f#t?L50N.JLm+^
t^tP"oQr2-x^=a+B.*b["C6*$Hy
W)NT[n%b5.}nz[l<t^fr9O<@!sud^vgiHqZI)8[O99m7p.ASM^?2/,,3FQ"8CE7AY/PhU4G>Zhb%ACsT$Ij$hlig5=_VUEles@:u$D*0ur5)2xG.SL}OFn/8n(
@}o%__5P^6SG9$fu1S>{4x`=(h3>!5A3P-2E1!X=R~9^]=a#a>Z
Q69X/hq*RNOK4hdkT)v^9#;kF^*0KyKce0tcNX@&uTv$Ev_g<*<0p1Td>_]v+WbZ+ps-+*->-U3HA)X>Ta<3HU/z*?iqfv1PxsDK2jbw)Pa;E@Wwk](IS3Y?S@5N49,
YuE!MH91efABV:XJr)+3
Z^P])DR2wWI@(gUjy%$Gj35IPA##}ej#~<zaI$-*u@[KUkCU%p?=FVEl*(fS(%B._:#
7l2/v-|pMq"-s4yFrPYBP;**u-WQ#^Lq`&ilxa>ddF)>Au<_S*m@w={V7sIE`cZ4M>
^-5{YBk<;.`R(E"EEa/((/Arg0L(X8,&I,4[rxI5pSOIc/_BLx]9O]mChAZHdH8S4Y*l)F65HtLo;ClZp-H*$<Ev-F$y){mIILPvo@ApRgL+(QckR0dm-XgbX9T~8_l%,ECy7,g*Q.P_(elT2Xjji;V5B.UmDQ5oTaHlJ4VP%niaZjtK]"w>^Evy-Y=$A3M3XOR&=!$aqAX^TFkSPaMs"+K*5El2TiZFCu^hsDk]mpxn]k%R5tl&A!vdqX8gp3_2q7`3]R)HMP>jO;h}?!w%cSJ2g
ytDQ2n#AN$0U3aljrah]]b1d_?/[t14xVWrMHhk!TR,3R@u^jEat"w$2Qt<;IBCc;!S3
3fb!{DLdLeK3igE=6L4$Y$}]liC%[6)S},?Wf,K#hXfJXDaqfmUM%)j,vVD"o7nY5.r%EtDj^iCd[Uj_Du+WKrq-^mkI$-A/c6ylc*z1Oyuji`7PW/TV0NVcz80U,5hW__IspF;WhJVYwyDk(l-`|R!anyb2OW:Y01SsnlR_hbSIE5Q7ZL`c"o"ca^754!,UFZ*UiWbZi@>2kj9hpe|>!/q6,h1I74~@^`R["c=?d2^CpYXiyC:v6P0Sm`HLT/J4)M/sjept3;d
G#`#EOyD3P-H:jFp7,N_O1NnkAUEW4B76s#C5/
J}L<gLOo_lp)>aEVb2>EjGolS&BAZy5s9($;=&>oG98c@oP0?9nevtdc&M>{V]F%P<
l"@ri"ta]L6dZ)4iQjenpq=1
]44WZd):0!<Jj%O/w5&;WDl6J:Fd#dID*&PhZiCve%`r9rs<M}[2t+m5b=^aE^-1*8n-U@DEgfjwZImLPb$w=Cxv:OLuQE,kCMud"<cq>}!J$c`mUTS@Ufvsh0a+]Oo0d(RT5ng5LY1JKt,0kXMdBj1cQNxEiC0dx]%];`sQS=s,VXs#spHJ0Q?1Klle<f2!YYsvrXSIO_vEGAi`[fZg/5v=EtjD){T.
}/@OmD?gOG#)TOIwqK>FYd@1S!-4u%fFg-)Q{,h^ma&Dc0#/6ar1>RYx7Yzv7Tq
Bv7^/5D:9^)>ITT%7EGw&x30v"qu;my=L`3&=#9=~nR,_.Mkd9+IM>W&5";P3@Q<?s+q2xdc94<S3s7tTsq,p3x65E@S4.oVVM/m_u@H-uKIVUb3w)`=r4#`_]!4uA!hL!bLdGYDXMV,kxUonFPTj_=Cbu-i(U6#r0Tjcdfrv>AQ}DU0Sr~?w7g@*5XB?62PFB4bgYmXa*fYR=fp*f`bA[VKn,Gm":013r&Yik.0ooRdNe3QIUjkTT>&x)nFUbCGjX`4<Eq.%jW5E7alslZnkNSbws@1MR"P7q8pV^Ung>DjUkQ
Y3S)%qu2e%2rMM0kNg0KW&giZ]qrF1.Pq=oUkoq1eDxH8go/kT?wF';case"ru":return'#evLUaLp=A[,!o;#X6Q/jtDhqtDDKv!cM%}(}a+jP`O>xPIId@mA-Q#9id{B00"8Io-xEv`XHX#t?<MVq_pCwIy#M0Yf)/2WHi6x"TU?e^+M"U(H8yirg@K]&V;^:m5r!?@jL)hG^AEO&?%^KOPss
FX]A^U3G^<bFSa?*9NP0f8VYc(c1*%C(agxN/6%)7
h-f:O_%)h?gLKB6(g?`p3/xd4b9K1G8jXY5ASU-#[XGYOBRMu[=:v5N=4IgG[mZ?J1DsHBZUh@Zh.G&ivNz(z8qG!5|APr,!F]|^Nvsw)C*.[;lh+BIvM733pO"CQ`u*P]:u/c?<U)Q$PK$"hV@C%-&=j>:a]xYI&.bn]f"KHa~RAgGWor[<RM+%La|5Mhfpil&G#BT8^nqlSoAtf?No5m95Qk,(v1}EsE&I2B@[UUf#Eg2p]&WK4tx.Opvq["%q4=E(T/.-DK}Ty9j5cPj++IA,Y>0X})+!^*38??%sISGiIU{$`y5A=Pb"vdeO-.E]$OTOlv26gKf;!0DO1W"Tc5dT%u""y[25|jjT5!H<KNMSxIXQZ2kkDC*kGE?rQ<ld(_}>xW0:<&+Dg^mrmK*R_Zv%bY72U.3xYx+B<rXiDVL<tH|O;]Ls>Y8RepvQ+FpkAeb_Mho)5K90KEeQ%_4!g$)3iAzO3<7W4oS,u3@It%x-cP46C4N)FDF)~X{FN26D$Ow?49-lgoOCe
Q_m)+AepG.4lW*q-oGrK)a2II,nm/RYDldeF)t/rrF@)6!7*]G]2bB9M-<gcVojC.Wt"BOhp02>0"&oI[(nM?;n"eOvT)tWh*m-x&X4
PHX.G8Kf8=-e*K79`X]kb^3hW_.=:&6:.`B8fhDW+F.("Y~h.[Nmi=/o])x`vpa9^AX"ExA/i[LLGfGlp;mZa!lB1&w(U2ItKa0w3Bg%elJ5oSg0:W6]V4ME3PHbwiAh__c&h)in&bFNK2a
QmT,<%uS^wv%:RibEcOZjJqV@==H)33E(99$DY~H0t-Aq)P&(d;/VujYcJ%)U#pjh4:P#)o-C_dTLDn71I0)L*H
zFK,:?R1+,XP
I`(-h%
{C
Q[_}I:yyj[VPEY%/a2e(:]"l8emN2RtU#u:3Q*Pb

BuuY)B>9:?i-01
vT6&UPOZb;R3{xfh$Uf0:<!0/x2%bdcgeA]%Fi^1MPxE>:Em/dL$kK^[qdrxl
B(#?6ZpNYt4Oa8
vUa&ATW"c;83[-=Yg-lWK9DyK;Lb3WbzwK^.=oLxM2J~LZMW>{Z7o`968h
iy6qQJb4n%~5gx(It[k
/o0-Vg`P%=kj?A7Y:kaQXG$p?]+$,C!anFi=2!r[CT5u<!gD/-BfVB`_&$a-,FyAcgcl{9!=n9hRf?5`$qR)Y^20Ymi<89>1
.!5}0P4
9+h0O=Kx8KR{&E-cB*I.8E#eMf
7^Ocm*S8lVq8/jG9=O-xBE`(vd=#v=P+^JR&cB~ejx7t[Iw_%t>(#xYX8!N2SO7*c;^r6:,bv5<3`,RjSsJpuQ@@#aC#x7GWurzxZN@
r6x%VB-$R$FP2OLp{"g4t-z0?I{CODKJv1f;GDC0voo&AW&=gBfL<)|AKHhuXV`pa&A6OvIvA68]vdJ]]yyW9>&K8A=Hij@e=Y#lh-)(q;t-;5r/{oJiMah]5qKqQ(#S~X8;$<=bgptTXyS-M,;;}K9mP/wlK.$Z!^<Ph;g^|S[B[G#0no+b7U!t)>pR!_M
v7&U/jaaO?J,6_Drj@B-d/WbFU:f:UD1QuARM-TAkpdY|W""d_,*|XUl?t>41SNsB.-nY5u6>yE)lY]>#;lyucdn?Z.U+1yo;8yqAW~qFhf:4_m:L3q
E+M`C/Q%kn&GwYWa4*=LytGTc,/<OV(gaQEPrvum_Z4aKI^No1x*y4@=$x9r(0GxR5]g@>(/}EyiTI1w7^v/3KWjJ;34aLKb+oRKdX7r]tR?Hx?<
bMSn9:u%1IY=NPZvB<_g(T[]YGjApm3BKUZQ>[3LAcq_?-.Q=zVF0&S
qJnLWkViUwDB2[Yd>+n|n*.A7oVZ<~D.`eUg4[$p`c%b"m;DoJO_WL)a;mqG3{:fXUq#ewu2Mwdh*|,H]$aWM|d6=N!kdnpUTcW6ozlglU-l7=riYuWstS_J<dYxHCw(XG9QtEv-IH?[D{*,4)`=c/
Zu}hWZ&o{iDa!f0B$:4ewq^G!C{
F
i9gtBdjIoHN?Ujwwjl|Ssh>sz
jn7:%[xl!Z?.iDyJBb&VO`m??(*KSXuR>lS
(@RVX!(Q=#tmiWAq,9Ubs4!iuWTIQCR#Zd)4eYpH.AYVh),v-.HwEi$pE[-o+dqE3.{QbmR5N,dN0l&PJ_6k-OHeG@G!^%X0q1B6j-ySD=HX)wx(>
MrWuUIandJA&6l*jxD/9G!#d#8F8:Mw5eiq8"S5=fG2_a%GK<fx]Hh2dfIFF"Crj>&[E}7]x5d"J,?uPT[Z4EtmI9
dZnj|#
UnpeE%)lOl[EgWv/CK>Ak"A^>1k&"[_MP
Y|&`G,*lY{KI-4"xV;<oCVwkgf>;WwaNdi+FSKiPj`-A4O^%1=O>E%1R%:D#)T7j6q9L9!@7Jce:W.2IJ;Nq4^`ge&>JGV2%t
]=q:9,D4sJ-tyH/fV`Nu*A67Ta;xtSKq#f"Je@cUw6U)%@#%+y"s:%
m-68y3dY5;+cI3Ca9i`GL+@
mVJP)s09SIKply9A5M"FcvV0;,7M2(5_PT6
E0S/G;}42e+<3MbqlWS9|P$*lh$m%^39ydo7>>H]Eq"-=i,TEs)J9L>Jiw)5~Nv3OyT5r_N*?E7nG,s:wQ:0*,P.Qq%"1KFS@8b[,ky?rjUdsd>nAuqk:BZJ>uRDkW7mb:P^O4GH0OHYMe?k`P,.pkwA_yv$;i&,tO%;3F$4sD+!40EN.bBGf0&D`?p-}1Qdk#_(}WZ%BD292jrk$8([
O/h+7rCrV9j4`WtHB
e!1P0MWQR2s1_vZr33O-hPVD/s1,"G0Vjt#O#GWYxf;Qml#*QUar"

w8g3r<2<Mmk:#cTw,Bl*![A+<f-Q}4BIU-W20?^M}ST2&Q3lW-Qi;Xfue105Y_3q)Qqr8@`Q:+n^qv
2O(%8k[TV
,(;gce[qX]BjPrmg4`]"]<QpCD;f>[4eFODh4{tpsP8(bFb1Y;:OWO]J164f6As%AHH=e/nh*p>HL=LxH%D>VzYz55O0m0ATT;<&Os4{<x_z5bx.3]YLPw>Qf1!u*0r?MT["^u%SklGGJR6k@E^7cSui
Iqb-iZG&:@3<CaM64"tN(")a}J:1AAe><;:R[NSeJG2<%]S7deR0B&Says^caimQiRndXlSJSe1Pv8$y$A#?_jtG
X`j186FZlG+?B^x}c#+*LP1Pa{ZO+T*`!a!:UW**f0Wwp915u7JFmGUhOLV"EL^dV#XQD!@EGHk09{_setR,PJ7v%Z:ho7f{5+qdp=e3FwC)KY?3kPW;0&1$2pc(C0S8fVP}m{Xw>+GK3/FsRMVziR@I:?B2YmS
cbo^Iq2]w!MA/Ynv:E+`1/bSAsqEq@4w){N>,XP$dWdio}*,4v4*@98T0{T}J#hTfu<cf}hj1)?]SP6KQyRcpN])NY#SX#s./Vi5Q.&j[*2N(!7B<{+lT,5pHvm/IVZPQHyq%*2Ah.PklH=N>[enW6"w]R>{d_PG4FQR`
^/s)2?%J
(rV9?V4ofHW_Ln6[1vzX
HpG^_=nmrAM5_z@|nD:^F5+t12qA#/T~,lfY*~1-W<B_K0n&ARi*M9);BycTdHX!UuX_y/]:C$5o-|ls=b2YTvm.Yo+t=Nf*np7Yr#CW%D6NRGZBC`cM.bbXP?j]eAwhuu*<>ES=;f(jF+CnVEkEu`3#w>ZlHDabECA
;Oo,jnaGRxC>LCFgv.U`6*0{=(R7"_FDR.>1,<wK9#s;SBLvLE!I#".%`#_1k|q/*<Pe8:,7fveb<=-hX*tMmXkVua;W;YAxUcP(gr>ZBV.xhge[B%`}#
C`I$t53DFu`Vh;p?*1YB!D]*%5hgdyP<mBc*fSE=ST.6
Wjy(J-z4
@Fb!^0NCGD@HBECMxXcjm;je[nPNeT(7Eey-tH*`U_#gc,xVwL9y"zd#"-m9=z`]a.LabUr8qC
^chL!u8(WhiG2]0i#m,8ak["y/.ZWpDx!jl7EGmlQ4ju&Gs]0!|E;KaB9vC4Kt&,/I;W/hSHO/*0w>ubL*imDeduPGB@eb>2%$Siwsl6ZbL%JlP`pj4.T!1VwkodbqIR(X!`nJY_xA>&|;u*M;$]cvrk"t:]*:X:_7hhq@<gMCUhvNc
G8G"NLy(5g]bi(6%^dLlJ<-jZt"`tb<c)*t/1^*M(cQ:oWPW[w$#`_Ni#JM$1mSAm=G&gn{aq.5*vI}7poG';case"sr":return',c0@qaLZ;.C4khB#+NQa?PD7=drBnm`NU8-4rULixA@03(6`8*ID9:x4L%.PAx!hW]pvM
_JHW$ft!YJ0PRL7@&xCy.dqX+r4`;
7D-tW6>+8JFxj4[PtBm=llJK;K1.#
/CTq.^UAvY<7Ask_NAv<9
uJ
;u*!y3dpe#ylKgs4R8^eq%"jnkERk
%1w`^lUE7cJ<V}V;Q>DcKZ5EoTiZ]2#2`wgI??^aP~#ecE%C=;i!^x!/7Q]kwFXt4:Q%J+&]kmVS9tcX)#WN/KG5]0Q]4FRemq(kh.*uY&]GwN0lRl@|CH@~W}(ES`Aa[EayFJ?ULB,HA{E*Ldi#XjDSHeFD){S-,?q5/QS~/zf0Yr8}:@u/*V4?VeP_0mZuF.,LwPow,Ivds9mvwlM`MCCv9&T"<N+u4<o5Ge/5Eg2P[x@{%RK[H{R89m^y..XfP</0RLokr<;hc//_Gt:3uY#fN)JdXW:/>0XN9+uM(~]sk9?sYvT
F*P+1mgmm+p#tVh7XR^S5a
ToHy,V@3eSW?Ff6d}efM]Bz/P#dOg`A9Y8+p_D9ceL3Pr8|C]x+$-Q2YIu;!b35P01GB&Ijc"RhdR&8Xidx8Rwli<FN8LbH"V#%<P1:bk"OU"T=b{/YeRj43F.^d[UNOG>5N:j@:Nf6`2k_crsE34g0k(uaP,4n=@`+.AS?*/$06d1y(AZat*iOQ73oOsz#$vWw(kwmtv;j5sj4KsIXQ,D8<ZQ8H},g.Ij+gIav)B.xeC#0<_OPNpoocJ`(NOJ5@<Er/jW
9{B##.DNno$~BitwJnx:#Av,KXaTKpHE-l)yE(0%fG4w(Yfl^JEcBTw3NvCtJcPd%
ix>p4lq"Zwva3Cm%:IiU@sr-N/Ox^w3i(iO):#HqB)b!$/,./<,{a}c|DfI+uDh?Dwx84x_%r<g;>VMZz"irj|btX+L`iU8o(*lQ#/gCNiE`%n]R)zUY.Ds`u]X"PeT_oT$GNot8:pV`JjkdvC=Kw1FZ!OEa
x
dVd@-Z13!5zHxE!TgN_;M=~w(X,3pup1-e9Esm$Z_"k=ayXKi>;YUxh#X1t53m^Y,&>1%MOyO9WkF3^S)T5,_SiJmnL307a@*HCH3
Hv%Gs[s8[EUJc$!$g%xarl)])sy$fhrk!#ob#om$Inb0=BCw<-k!+VL@{5EWKrurw`CK:qz-m:-OID%MfhLp"*{PEHT:
f_A4]sVq<4>8=O.uTc-zfYZyVKixTwjN/$r"+MAdsB,Y3wCGcc9@wI,e%:f?88Qxr(_q9s
@kwh_8E?EKg5*IH^l,+o;&+K8U@cQG#p<$}9b&!9+n&>W`x<hW(P/dQYMaKAi"a[{JhLhh_,%:u</2#!sky7c3n5fe9ml@sL[a40ARl#*G9ueom.Godmn(IQ)p.r.T0];wUDo-hBqlVo@?WrCEEjPkDm86zu2o4kh)<XxG*s38=vKx|%|U&J*;#uTQrAy(V<n[-C%
osWNe]O+dMfwE2]Z#!V=+>]ZzEK)ZP$kA5%i?GvO!KNY,cyt"yx&s*_[dAQ,Oks/]nqg"
q9Dwp?K-5XE$m4iLvZgGNE;hw`9a?vrL_n17mZ?0pM^rLRdfx`%z)yKD0CYk+^;MrXScJ)Dg[Nr9s:X[g^yGIZ!=mQ"F"Z##UAGqa4VHxXRnL@gm
sXbx/~G`;^
02Tz#Z
?LR_8^gqN
m)Z?Ky1$K<rb6:]WpTqf2Fg]8`CPO$K$j0mB%gMsPiknk&/1*{X*;G47,#m,Hxf|?`nvfzNr6S5-r8cP>vEL$QPBkr.(!?&<c%x>4CXE`T$ll#@Lj_]cj]Kt@[y^aN`ndsqWf3,(wE:zxVD@<dBpKB"3=/By/i1|.eP8^*Nb<B`^l@U[A"WC7*?s]yEWA&[:vO_^+3=zB&k/BtSI.FfaT!e"!G&C!ypz_Z]*"tW?u`2),;4@B"hpMx;Vaxj3BJe%mBeBW0oCfi@3m~K=I#;YN|Bisr=i`GDa7zc$j=?g@U<0JT6a#(B6mqN[
ZSok!_;whc5h([4uRjio;OJy1eIGFlY
&H:.GG~ed@.lo!d-(cynx<4e{ix!P894aucQ)F?IRS[nSkH/
Tl,|?ny-mi=8)a;?M0+uIm@yD1R;MI4(i5iP3B&$sUfeIK;{lqxRU8i;fh=|!9s&^%vPQsxN
3i-
LRni]YjA*UBQGsIT$/UBJJDp/$q6
gSEsSQkm3&t{Ti0<R,kcqKNM1,hI?RHGFPiNO:C:jy<XGij1XgLUMLb%>BU{vGySkQ?KW)a=YTJLI}1d,|wo*cUPD4
frrVg4f,QcR8)AQK-y_500Z6p*1AG_]N{@P<nLZ?NNyLB&+DVvQjtX91(QTHo<5-&1*MmIR`P8nT.UDd?S8Nq/~Ya[rXH5fpS#8?G(^xOHPAP3{Qp+,TX86FiBpt{P7FeZXa+wJ`eH%+oV"=5ZJ>"G@^"_w87X>G[fdlv0Byd@_KN
_51vu+Zvq1Qu/e3MlC;GQu_@kxpJ$g|(G_Hbym7k/Ab)1P"4kMPS_`J]1F8"L-Zo;A$h*^^
UoB2fgt:~uD
u^}!C)4G}/<-fKvgny*L
jFFWfJ_+xTNyOVl`_7#cu-f9:m0Xk^x2@Q>B^V?!S@D#Z@1sB<[G@)C"L79OAZum2!E/Ra<LUGFu({ERIp^!v4t-75rcBPL+Xd<V]3GQ&Y,0J
96i-ivAwp,DQ>B=Eu`=j@902GWBebAt?+#[G#G)>6&A>JQI$(w/>w&E5E3F?Jw0%LfUb10Vfi;:AG
HX]0AWLrcg_wxT9~1f7LvpFq)#D9Y-UC=&SIeYUJA/l]]F659W]|]bGy=a_bRkEi,j<ih+#=
C+d/aBtmJ&0BHc:RVTE4B[nM[qPW_nX0gY9b8E8OB8Ga8-`y=xE&m2($oeYdz_SG~Ku;PK&rwb4r"]M?
S3V"hIS9J@U0QD#<m9[&PL<sB!C;=)HweqHA49uR
8D:bCbXb>qlnj,C(WL59*#fn)cA[86dJceo=WshR0SLl$/5`OwGNc[ISq*SZi9$d2T]`tH;H0m?qV3!C
VQsdR7P*upc`E"I.7lgt
[um*{-y3/DqA9n06hA2+EcT<](r:
#y^r?%U9<HD7VE%4Fsu{ajSZRAM6Xd;~+mdrTUi(1`%m+5=>Y?BwF_A3EB22uKc(wEdQuB>:tg,*L71I)LtdWx,hu/DF+PU^`FE/?+3zK2V3G6:k^/c;PbijrwKN%h@.L`U&m7J?H[tO9B5a>3;d[phB*$sH@F"enQ*CUh@BTU%-mr&nu`T|JALKuEZiD}+hT0FrlN$f)(h0I4-V&P-Qqq<H_S4v/O1"R;;[hvVspg0qfFL>.O9UcQLqn]/.JW3ILKNX+Uss;
E#w1[@(E"gHbT]v,LR1UC}V0
RU:w^7<I-d>7Xx8j]Z(t%Dad@B.KH%zH!1Tp;]AgkHJ4^U=L+p"R-1&/1n3%DLEG8du.=RiR8fe"gy(6|uDBHxLXfbGV{K1b}iP,@CSl1.lYruNkPUbZx;ARNr~/af5kg<,5fPcq4?w_FnH.@URAhdwoWdq
6IMx},vqwnhR7.CA8c(R#CHCqef(ltB!y^_VS5Z.fo4X$5^
]*WNV1ox<]^XxsW!j^D&.?~!Dz!*+;]b.AcUDPv$r,osB9Cq^*n%eK/RgZ3Q6hR_fldh8yjT"k..rPmlykU[s&4ktfIO6(crFnrmca"t-H6+j4LUIFuQ{Z5gGM{j9;OBhV(m).WfKw1SSyn0.8"pBh07dknH2N`X{y/aQvb!jSx7bfz&]V054Oj<Ir>F_eivS*B]bB?:$@=)/H?*#,DD/7aS2-Ys%]*ow8j6I:gdLMfW.?O@d<=K"-Gn.E2^jD"Qj&va4Vuq{hlCA@:5`=Z_EFXh!&iY2,w$CN;f`ai:eWh894Mw7r$cd8:pntSk"3++B[vSZ0YLsD"`/y)&YW"1l^t_zqdlOZ@@2!dEQMo*
A"B[UZ-E`OUcGy<+r)xco)';case"uk":return'$ev@if{p=?S)nh>]i;pCN?}Vg`uEh2Qd/",?+Y<%mX(!k%h!y;^3kJ]S[Pl"07E1MC#(STG5z_s+=.zqadjf5v{7RyVM9^7
]6D,MmOK(HbhLb9`D7!3"lnA>`hn;Vtu..9/6_`q8l2lCwdW>Vp`AV!/oGrBk;phxEV5WM|*1Ip>i.jB3wlAz3bo]^Rip4Ndtx6[SBBU9D#h./shLL5q7hmkNbMSAOV`%W"b}0zE%VYuPJPDMpLdU
x*)86r^R27r?XFWw_<6A]iNOx94:}Qv1}e6i/UZXa9K,1]eor;=K:*7SW8TqeA-z$YY>-aTsv413RB`xa4w#
]`R3cO@^BuCl(NO32"[3P&Uhox_9w8yf3gx*>(XfuU7um9;rw`9|86,]Atbvp0D6FKsG`4mzdrLW#yu2_JG!a"7wq7:9Z%"H.ghS55o`pV4ZfV.8Hr;sY}WoYTx)c.g|!-]R`,a4=BVlD.F.Lei]-`I
`8M4VpL<JQKW-Kxh"/C^3f@Z0#5
/bplL!"6W7hYH/-C8TlQl`h<uK"V"s[Dn|(MT
0WqQ[Q&E)W2p,Q7B+S_M]b2i2N^+#828Ws36uPoB(__Hat6<n&c!3p>e!D*wZS"MGrbmoWI_#HckvgK[G3riM<6_czr59k>Mn^ic(}C&M!)03}4;s>I%t.%"-k-|xX$,2vp
=R2,JXp/-EFG)sRtbDmTXjS#fD@>EI#L$g/YRe/{d=(v)h5W25sE5rtdbB<@"p4<-G-%yXtleu;B:^"F!ojzxLfjI*TNl/p}yHP[>w^f7R?J*SBSEP9;[oK3/q8uYU"petZ08b
S/ii,hgF(9B1I8<kXCx2p9S-9#/-1<v2?blW`gBj?;7CI%0`CT3aD>lto-~O4+
yxsfC%@PS=T%>/C|U(O%bZ(4ba[9?&w:lk5LrpoKE?aU%)wuXvBopPReL5i7feigD}Fo"9maW?B846N[$hUS3h(;];AUs*(3aVe-41&m;uFtxfEl^u(^d2P}3XN0y[2VRU>{]--|)mN}pB^B>0"E5{>m-2"*J
9Uui9
r!Xzj|
(l
;M[+f1-Li7fYj]=|QvZ$UmF_OI]~"2yG>,.Mu2nu
Iub1O.sZVKb!`"K=-!54rrU9VAxF{Ef<0/9Nr>8p[UNq|ra,_^eUC2`8O`QNE^K(6=hvLEnQU2Rw0DUrkl6",)8>R_6nOMnSGV&y(Km)M=mO^sWA6_%3X?|0W(~3:d;9W^$2"K.A537#EZg
t$L
R^[`i7A+OO/d@m5Kw.fXQMC0
QZ
(&0y).Krh_},IV%VH.!H
5#!l/a
GG97k#{W_i+Bt&a.S4faZ[*:n]4&ZGmK1e@!Kx>!3&`*_Go6Ln("t`U^66@A~JR5xriGBTyQw^fF&!%A[o@*N$MNm2|_}J~mq
lba_F$6&p`?6XXuv:m
Si?}1)1
qq$sP|A_?],F6(m0
GQ^NFo_tUKA(rlk[7eN94Xkb3Olkf6.+hKwnW)zwW?S[S.Q>URc=C).)hVg*v;&w1k`b0D5tHDGG9tX7-<yq|2c9}#OpyB]=heV4D.Vg<pr@O.OK?9Saw(Lm1!NSbq&Eq%s>v+YEVcEU*&VEQ=%Sm8OKHp:)+x;`K(yOD[ij
s=N5km,G:=Wk;NfbdEn;@>!%O?CpKGK),s4&hFk.A6,8QgY
ZFTg.3+q%OX+Wi&MNv,pCvT_P1?!_*FN4Y-PIvvjutcx6|tS]-WUDu:XByv8kY?nGlyyv
y0uL7^_7CZ`bUQ&f[Bb"fEg4LD5lJvaHpl*=5=9KHeP]05/1Im:+U`
5PY,UO2;JZ^0:
YYc`sSz*[Anx=*{AqfOtWrB:RPZi4SS/Ag."~KDs:E}^5`{^I</i
3j
Aok,L;QEAG
?N.3JSC>$.TO-x=m-nt>M*%sYl8klaC9k307P00BBa/Y10%e(wIIu=(?6A"T^OH;%,UM(#G9R`P0
ALoZ)BQ4+"#ITJ,6nAj"cyXc[(0g5K-*Im>FRI?%r]+[Or4Du1@R%$`+)Am2.SC5RXb?_&zc9woGM@v84kgNN^{gx%S>.[jAnPFlrXDCV$GS@_OB;e"EV2gW{_PkiO].YRuBjlz@FfvZeX[6@.^jHYQ/4bb##VEu:.ifK"
v#MMsn0V1Dx6qd;knx?{2CR|`e9$pU6_6dI1g3uqRlW%IhphHPl!/}F^/Xa+rT(OF68KZS*&ArS^CIN<;$f`
.YKZt1MmS1mxU,+34k(P}NyxKDnhQio@ALgfv)CHTGu#pb$)Wp@cTU2`28dR=X6wr[@*_mB]A".EBBx4pSKfw!,-$Rwb#(aDI&cNr,7.mFgxdke12xtHO,[Nbr?[rl2smt{U^"MU=v"$rsrhhF??F0OArCI>K%|GxFxsO"r0X-:>zmSAc?&^L/);G+;<7q!Jt];(dAugdbI*(26x3C)<n1RVIE/ZL[>,{WebvMn9Yp6&Crn%`m.>9oKm=F_CRD-,d!MdGeBi6R^?uwfx@VAwTEIV]weYf>H7/VEF?"r6=mHnJB2;05eJn8.>KDYM8R!,3nPP{xOZX)P
#]X_~feU{lq-*"BgzRbl|2LZ?TiF4=lJ=CB52Ht</0g,%U)jnNO@|r[56P]i+mT8zAV0vhmjGCTOJ7P[4qyA*Z(l(nSjYIZTF&VMUx;+Xb,kyGK$1m

edFN_R7
fnRxlEaW#E@_s11X#
DY;YKr8az0#tij_Lqz#&IX1"x@w&L<I(0P2M33/QLqQ3H%hlc1iA"L-iVU+m2kov.sZ[=M1CFZmY9;??3f<b@cn+iWULdbiI0j1aB0`mJ#yU{2eAKOe+r)<jQW"8U>-3w_Xd.vm/Fm]sh`"tJtXKg^])Q^?9UjRwA)T1Pe40:%pj[;aAM*D8kP9K"tbR6)}@b56A<&%
5)P3!H<S=;22[-XL9:=Vf/bUl`bJUQLcS;"gZ)|MpXt=c@,1NNo?]d#,]NSlVZwI)Q,]|a(GZV/PvCp%sL84D7wX?ABrk:-/*J%lE0|jCa*6pAVA>,sj?b]1Gt0?oD;sX[Dqf:_%3^v!/LG;t
g(cm7#X_eHw7M(y`bX-cIdS;-tk<{?1f=Wp]TEKL5%1->a=.wC8O!4M?WE[CsqXl^CR`2"Tr{#hG-NMH}3Y$3F=/*Cy
W-]"0go1)yP![,0n_OqENNp46N^u_i&U_&J;s.{C|6_;9)V3JWPn0S|AXo`W:+I
Cs3X4s"wShMfdCybPE-R<U+E
iQ2v7mgvvB]|tmbUf9F>HhY|C"wSG!751Q8G*zab-GGxDtZ@g7Y]*p:&5xBs2B<g>=@)&w%/<jH7;6eUG^U8?ab|`&w9kFM9f4eSu$.VmHB$a(k{`kYFW[,7x%-OiOw4dviNZ3BrH8a3joO
_$!eftVrf6AEqXxhTq<6.iaKrib4y>>DM52-IPPiy}4_&,Qvhf/y6R#s/NXrIPOi1((X]fRYQeMLC<HSW^8Ab]K5nK%V0*ns(5d^ol[%JWJ+>^owH1*;B^-SI`xXC*dF3=K@?$&,PyL<s@r@yp[ZYVdgP)LsCbrvT5.q4Fx@!m"DDk;
$@-{BL_P7rBN0e6/mRm&
iYR[bP.X<2>GhFdk?DUy")lz!F
"%Z`iAfg[:[2HLI/2~`_]U/~9M5YfUq*
_^@MnHYbo^"1]GdiM([3=aVINZ@p|4(w5Wxo1EX;7h)Y78tEEu9sorYJoZu^EqZ6RCJb<OBR(;F`i?L+hB(*Um;V7WAZ{p(J^/Ow>Mvm+([`Pg+!qROc@h>CRkJ1e3i4zc&q
0e=?%pIfLeUZ&|K^g<cPoQ:%;*[!?u^)cQ2MWay(Ja*Kmq]z"lX9d4:trn0CJ8:Kd]B/W-Q1L%NiA4bFeg)}yzjuBE<jhjAX]kS(hQN5062y!B[+R{5`$mjyJpk~U:5B"_yoX3Z3T{(%l8yBuU7R/y8NoC5{JEG0pzr?-En6YF(&qkS6b]X>?xktdj]?BSp-U}7Qv98o_h/"<c[3J~H>Ey[(I)bnX3AQ,%%%@b]X3n`ZB~g`AerjG^`NFl+P*uw#$W+PF-tM=nE:Fz8/v)=NA=Q?Qsdr<+37tsE@JX1[y"QExZM:JHlfM_g,c%G!p+]><`F2i0U$Vhq}2!bh%w%(;m"&k<l7crYxicySI?Y@fimCp6tMc+([PwK/-XelJ27@Ek,"M9w},&5UV-D$^$+WksA[]yQ]yKADA9Kh47=7r--^A/*:ZGpf<<ijqh&O
{T^YwLj!<iaYKctO8h0Iq34o5';case"he":return'&s`/W;zZ+&Hp.Q
7W0Zn9Ciq&J{gC%9xg-#g>QSTHVN9G?|9up;kZs8"[#z0e<}P7dMi^*x70^Lv,bD*M2&-]K;Bm[N0=mP:,!T@.3%oXu?u#M6%}YYcb5n$,q@:M0n!F8g[uE8LTtU2J;AyCpAOsin4^;-82W9aGc7tll]KoD_0DD`BiHf&Sa78xggs-U$u~`@KYnr,ttE=k,qE])CSg_",:(
I*fJEoVtlF$*O`#z(|63O$`>R{*qVlCZ%`3L$%Ls-Lg=-XqyYDlN#iO7*~@56>T?&;mwe)4}4}L&hMlXK5]WO1P*FMh$[9EUn69ct4IBf,tBtjB9SfZ5.JD9W+*EeXN<up6+?DQ/I00@3Q?.1Aca8`XLG(--:+RW$Lo%uQu/?g3<pEwlJ[ci%<2t,biUK).`;[wL&BOH=^+/ANEF4^&0
(5tfe/T>JY8OiZW<I9ZL8Ac#=uYI+%NpFpo8rM-nH=mmKm76J/d&_&6SK>lV{sDB3u|$TW72d5Q]_#?MPO)bBBX*sV!FG2PiWT"`"LaWbcOe)
h;/.1e*aF<.c-HYlVA=Yc9t`Yu$K;_Yi{s*pzXF<6rR8BM{y(+[WrV"g)EL=:0<_
5*Y0CjK,,Y(7/4a%W$0]XBdpG`x%peD..`+Ed~km([^K*"xdnniq-
UU9j>$Dgl[.hMXkn4o1w^BA@4K?}2fYtCy+$q-eR4g((#h.&j,J[V/tN/qbL+EC}p(_I/I1UQWfr)82}&"*-z)%5@MAj2`A7/dttpI;q,F]3TSn[P),EUEY[qYo#-V.:YN3U<{LR
)*XLSba"-wX6i,[ESZ6tNCJTNS4[j:Q(<v/%<[.[}O&q75]Q4=,s|x7ac_qJ+EV_xK
MSF3$_E+5qiwR]D7QPk+P^?]V>48pm
g4b&o,u1I6I;Q`^9K%I[Zp2&La~rTe0
RhYg@I[F}uv7lx3f~2wTBU&u3Jun~#IWg3IV~uIXYTf<J@i-5A1[`R30saJr>I8A/f04;&GQO)juQFHhV"QOlP_1kLzn~F(@egS*p*Y+Def:<#uillY:oY4Q(Yd[C>#>XJd
"N,<q
p(;k{(7#GY#LL$DX;S=CfUHZ=NfJ^+@_l
J?xbu.#;YeC
!,.k{QHe+$Y2[cO8?yAJnX>Yc#5V,!G@kSNX8@!bp(2"lrd/i/I&0!p%!Pi-Gc_[j*Ki;"3&p^"Rs+e+O!C9WqZNXB{Svt=NP?TPq@Y3q8fy1vI!X`5<$Zi3b#jySnZg=U!lXi+RI/Q)oi!qsV`p6l=U=YVPwmgkU?hF{R;c!@4^Z0vuD+O[2nRJZU8hG/8Lyj$_LkMKWBSYAD(,Fn&B2fiqfRJ:|
$v
7COJb6dic(3-tp-S0u0"aO86@J`]QDLKXh
p#Lg
NqT5;i(QSd7y,JmD
jH_7)v`v5:KcIMiJp@f$FUlX1Ua
In?1`9m]M#u]Ll|
too<E+Ocr22SsVXV"P[qp;(0Bg.&5DH@l0vDpc;32w/Ou
XRL>gEGE3fVXJNZ,FlXq0aUL@Vae~pRJ(0b:b][e/1wVX.+ttCA:F0TUN?f9j@u<XlsDo"fK[04?9]4@6ZCD#Smo]^H;q9?`H,c@P]ExI
O5$nQg]2h=zPLjetGKMk^,sW}:/RD`hb3K+JK1X][ruxf
TLLN-!0"G1]e7ewvMp>#Kg$4z8|
kf!93?|
kW}s_RZ5%BHZBjzw:;W9&(I#BH}V4bZ*#F-)!P6Qa8Q/0+QS1QOFwZ9J=[2T(Se_sB%2TV-Usg
v3p#_1&]Zolw4#@q)Y)r@H&4vX=5wF.I8hIN!x-xlpsT_7n7.O?)VZd`]p.U3-]h&M0GDV/,$&j5w-%pIuGtuPJo
fF3s_5ZBAN0RDUF]Ebm;bJKuRGTa%b8.Kmr%L$Uu6IPE)K3toeAE1p`FU0Tz!dl<b5MB5M(WxaY;kN<W*A44A+FQO`4QvC]o=v9qJ!PZ:iuoWGvw~fgXJl3x.:!bgx}@vA6FDBr[2m8h!IldB4euqusc1F$(S:{j|RNc~8W

nTt#eaW//`&DKVMb]&6</DZa-v6geZR3gfjC48NGQ&p(!FqABj],]PLy)n]&4Om,.iEh;/Z.gFT$63,MEyDzO}Xq,Ti!r#a`#|
1S,*t5rAiUO.{6:xo.;iIS:W)D122N|j$A{jx+hUY+J_@$1%PRm>s^<s%6l:0RIorrc_iJ+

`H#{x#j^+nR$4B(s:5G`.]C/p31e7r,]8Di,&I)Sp0v7Uch)hrCW:D3Wlm1LEQBb?CQ6iyY+X-Enpbj?$TW{R>yahcfyG9Z,n"?2.GZ2L~2o8%9zh,q(20,0gt2wB,DzlJP4r<BF7tf^s/K|),H7FFF.HK/|*~*@Y,UIVAEO,oTQ
M9
So_..(J~J@Uct0yV-7s/BUKdXLA(jX"SBD0qK(4LGjE1SRS:^nuIs
]zGt7,VvB`for{/UO[E@j)b~ScM<HI[mZ?N|LI(l?!cER.3!J-
z]UP*i]e*XR&kD++Si}0y]C3{TFaz%{OCZ&qtQ#U5kA9,Pq4!OohbA8bMg,;=+e=fb?.psv_o8<hQw37>M.y3q97nG,0!w|I$J,Rl3/r4V,CdNF';case"ar":return',c0@qaPp=(pw(VQ$@8%O)3>>+uFT+8
xV2h=9"j=~90/Kqm"z2T=o=gV"?+ayTCk{/G^K@*^)jy`-9^.vvt)5Xan-s2vHUrF[bC(PrhGhV461VFl^d|51bTIid`k[H@WXR0#!m1%Msx">=8I[g_Mp1Ea}J]cx`n0GX~X8f|CYq{Ipv`I
x"l*w9xT>Zh~glq{fU@zD#
OUY^irgO`#zgEYn</b
nKnYi7EbR;S+@u%)2JLf_&N{P.c7nW_l0g1{]z0s6;c/bk9r]o]x&f/4WZ+L4w4Edz:a5e:5!v_rbGWsk;k,%=+y:?&b3C<,n/xNSd_KC
AG;njzr1O@6(S+v7w
b8Ng!C%Jn_w->@d38b$aC5)j-u>)4`U&BK@[7:1GL}wh)C3de(i`RICUXpJd[z[ytW7:`jTAMucGo7`eotG[c*6`<fj*rz^fRlgt)JX)*&ozOz>D@qDrol=th1H?wgj#bg%fVnmAf6.9cM%no2]T
BUf"1K0R3t^o#nxo,X?2f5e=6+&`);e=aZtrQ:<xc0t09fd[lum51+x2EX?+z+8sA3Pm<lc0(7;#Ev>Y&!faSlff7pngI[U
_2-fC3<QOfZ5M*&1wqh:X%;w4Xq6W%O.u<_)3+
,24.6*1v+$+gG(lhe(%q2n(%P8QukUZpZ~AJb)AsO}L/%DNuX!C_f"ju)4OOw+X[>YA_Xa`HO<o2X{Jf>]h8]qchW4]V@Fu9#M5Tyd2qYw]9M"(!u!rYEG:g=FUSJG]z)Y.}fP11-{AkSX1cG_FXez)`I7h_teLCbb"3AHNb9((x2U:7KsP?<P#707bcY+gz_oT=%/lT-^m-S8kCkk7Rf,>AKCS~_a:B1bEZVMgx<2:gf2LCSmXQ%+Sr5EL43DBnX]1Vk#/a?{l6<k$5jaQmFpWl!qe-gRUj;&>l$)E}Clq871Oo"Z+Pg>c!*"wz"YI#5<)4og3;h|RNfssu#WCCgCX&&t5UmQtl<6ib(Jd:p]kL$iBWAYdYonx9((,!2}r&+eR.@%)&kV4%"-8wm8npE$w,b8i&6o"!Y%fQ]b)18EU"tuY]v<Y+66esEvQgu]Xl$E^R$;y[3}
8bido6uCfn2<a#T#zP~x3Ko&f@^kc;P<xfA]J&KZ8st+vk:-67{eE-QA)ttGZMz-Okv7$/W[;2s&xc)nb:jVTD-4otSpS(/ii+#":o3=/ru^nta]3]R8qa~P}O~,-RiIU;$@"9h
T&%5#:ms2"i+9RjY&l^h*)*vEKDB5B;va@A&4/,N,_mP:7k9N@Vve2rZ(F:`,.j3ux~;cIV!lZimNwV9i3a-~iZNap~:wsyQtN^#YtLyEcDy;[5x.l~CR@T5PE@9CNB0kkVRBTQP|=<"HO@i[e4<6U|U|T#j%z"P#NtPD&)#(J^Hx"O:u*HDI%1(>V<rqvrLgXm`DIBFhz!t{E2u1bd)/mX+<w1h[Lq][*&@yJ-!{AAZ3,bNdAW,
gJNre@j*y(dz@LL+bLW#0J&_(IO:A)$VoL;(:DAv"-u6tG>]7awQM+;kXS!P^ZB:M!YS(%e&Z5@JWQg;WqOy0{Va-`LuH;xFo1KLO?uI#]Z~DWI(!AnzI?bf3P_+u$72lZ%YhGAq&vWn)9
X:,KnAb&?]|f.5@+58:WS2Qf6t6jZ.1g9Ag-^xmr}ko[zSyH=7.(iH_4ey(Dz"&uueAY"TA<*"ei~DPU54zfR)n-W8A-(6z"MN)S[0XF,W)M1BIeoYpRZ2j[,XgD,Qr)GSJ#TVx_@);yCJ4T4h3k?wGh_fsFBPS=
wzqjvt+XbteV_:t)Iuh@Ji%W#|+phf$P32:[SdRGpbtjRAV63UsM:w
{Eo0jx"kEB&t)CPH0bhT2rU^J?iwl8UYz-8xc$%eme&1%k<oXu0H5N^A"Of8cK=^[FaB&381)xOv72QFYZ`G`.RKg:fj,fp
^/iK(;@[I+g>}
Nl(^$Ou-V5{xH$@&7@TlSbJg_b#i[CpU3UdY
HtZG+]h=+`g$G
/Ne
+w3{lYMDes`_]F@K,-SXK`41=|5*fWiyvnYsS@BjQt?./2Rz72B49pF[TnV,H}*ktmsqgkvb!4=NvFGDp8]virxnh{AWqWSb?+JM+6aPFF%
p5ZN1JytO.p03Pf65<5CEtQ{H`Gc4"
d&aW56V*"#f[>?O/u#}
b/YUV`
<T
"iHX[A,:g<h]Lj&>Ba64724A.uZZ>mp-+Hqt)#9A
UU$#"(<6m,$?v%^&ccxRKNTXgsg
V~U&@Ai{>!icQ7b9fV:XskwjkitSQLdAYpd!H_@!BGT>re%~,oNNiXc*826p&b[8W[RwM=/WvP(MUF6](<s}<c@d?4MwdpE
W|8J5aVjXShW95M0[_xJ%n:O32xKQFQX5eRF%uyryVa?(G`XpGd|d~e0L^-DS9h>Q;UiG5eUl3aU&ig4Rw((h)6P#nBkG!9j^crHmEiJ^Q1Hh1:hf?@/tW%qaE6p<$YdItW3=k0!)?&w?:sQ^7`B#>cnCZ(*M%,+FZ5hk^/x"|SNk.RP?L/9XU:7%vq`(eNfqXey5$FXCzoSJzZXNaI0ITk5&Wi03dEu*jY}g
HKtNX{`}!2(VHaJwbY6Frla=Ju=@qEvYkO;uI|6$TAI`_c:5:.Yv[,?VN(LJKRn.E3LnF@eyLXk{aT^p6hF%,$0aq{om6)P,J94>3%AV>)t~g$Ay[Y8`PbROluJ-3FIgnwo|M!7+[,;nUns_IMn;)`g%uzqH!5Vt:NtU9|k+uRCDh
i*Fl)tk!LUC*xT-6y8g)
qJu<ktT#k$~<#/vK6D6>
A,6tioj5d4v;lg+0utuNLNTfb"R2+TxN+5CJ;aMX(SV<Ci;/O.wV$vR)&@q
s"^>a;)eZ]x8@*/4Gdv^>K?]b[k@>z#S#zt(r3,S<TDuPJNLm#P+Awa"B>O2s8MIiT%%e"c"JT@+u?e;
t/:md;M<D$^pz/Z,$FzH]C[H5ebuB"jsN>8NVNaw6k,hlXqh`r}K+7D#J
Mjr#VYo-Y.u=7ymk)gK^EKE?pV|cn5=*p5(GYiviIS1#(9v)3YPnDi&sa@DP"BLYw)>j<)+&jxp/%
_Z:GZI47L0-kIslyDZ8G@F$nTmThKIdyLW&iRbST}e5!Z-Q7$9Zr+c%T=5cEr][3LM0Oa#.i
_u,:CtJe.No{9G<r_Je9Q(f<y)q7SCc0Eo3_/5>3+sx>,Wn&aG.:%=W"y!jj;zY%,9lQX`1>g8ZVZF&ZxKu<dC]$lU1*pPEPA&J=Lwk5eFXRUGf)BkyHQYr_K0]hj#i8l1y[*hdiMRm*7@iP@#b,9^DwI@;y@WH6>~PwYa>(l"?}/X[1IBTEHq7vSwp3V*?JK3,Ce(PM_VcmHd#tc"57cD>g:a9/tK[UtS.c("Cgeu3fGsS%*:`}ANox:QVD`:U+G[3E:A
aFrUM$i41fVRaK+Zs?GQ^K!il5i?f`*ntO4+FN^@z>FidL1):%SMw-fxQv+o)';case"fa":return')s`;:h%Z*hoq40r?w/b.`?N_BAs&!4+M$6RVf1x__:L>(JM8:7Ix2t~K>tM$1Q
6D3@.X?[`w1a7N-&w>"=%bv{HO]GbTy@7zjq6Gk_c<Fi@%5CMZUPhyB`B-H,2Bs!yA`ww"
|;lmxmLMmf8^LCX!gKj&k1#b0T!Ao]T(g?c:yKDR@?6;vOI77k^ypt>$[BWy;mt3ccC_vdsvPgqC<q"t2P/bTR3r."rqC@eaSJ?+)(
xX>*M`M^g"Ro)s6GcR!0ZE:+teB0(e5
c$NXbcjYPeaCl*!GL21-,_H([:xV0}d$d~-gHrr(rLf|8y9REt5T0OZyno*2JTO):EbrPC=S+KBc%EU$dl=l[|#g*/GMclGHf$U>TB-Z@Py*JrXi
}YF$[&T0$$2
I[-)K),C8Ol){^+=Eq7w#hH"WEs-/
v*hhYZZ>U:Z62/J$.ZGZ>N/*ZF}TIDmM!1ZC#*k1zLJ0DonblL0Dm>4-H7__(R0k4u"ul<Z/MMN^ytBqvL"]S)1Nwn|sW%,S[
^=86O-~nNHy]iDOyYwLkr>F.?@46LF_*O%o/Upp$3IMh_-+cu3RWaj3G!?~5#a9RU/87Q(?6lxl7(a@wyX@DDVVCZb~m2DFP
9N9z]B^WD<pZh9>^z&5(M0eb(BLLce@{k61&J=L1R]0L;#e{G_r3mkjG;G"QfpZG>-iU=uy}:s4K9NP]
XXXV*=}x@bKum.DrV:bw%B,VvA++7cZN4xqsUB:P80;"OHXo$UDu8)~1/MS30@NNNHW]^:ij0MU_wJVS-4OO#
74Foq+hyx@$5SXr&J&T,afvra*I;_:KpBH>t}6p*BSooE+N)73P(B9+JMKUjex%bL/o^3QB*9yA>428D(=_!p<@H#Cr)RT^!gX9phuinSh%
2-!#~v+aB=5hc%2cEc]VgpBB}oc&%AO]7%AXM
u73"iC
9/S!U6t{g_Ul/O>#G*NE"=qXE~>#U]iRkH]Y>u[Zd}mJ!*^;
{tWr$2I=pwQs?
>`Kd=U]X}iA=+j:B
$3%$-(T@Pl<=Uj[c3nAY6n//(PJC$52@b{^K6Uia5=rj!4hX`nq.k
Q-[")-&8^nRR7=_=0J=qh5IBKjG"255x4_Wt#%;4u!r<*!)Y9MXL$q&5wNR"GetgJF<QgEac7lC!fuG]&8$!NoH;@YV|b:AB9Ta{[m
LJ
RsxwO{"^J92)XYMc]}lgV;g4A^"JI@W9?=Z7"1M|35
#O)hs1mJqL>;vDYX&A?];4St9&v0kbiSOe+*vemDM"Njc`Cg=be4p!zhI!Q?x[fky0Su2m+Uix^S{R!.~RCQyyvhu6v`,u$Qy"4IrO%^,g_^0$C._JFk3tpd9oYA>%tZf-[d8`ofu9=4FQ/MP:;3o*duq;9J2ppiy=#ylT*gcO%lS/o2b*.[kCtS14XITHAk*4_V;4+VvJXo=25h3%."+0;.R0v15Q{[g>CGQANN?Su0QxK6Wi#-/
waf"lqA`e6VfY-}QBP1*[=>g
3).?#7`1[6/FW^jg+_+NO`eO6wp51ZE
-hR;VhT.TLo="yW!u4%:VG2`Wtb(y1wv(n4MCr0wk%AT^2$i[7&{!
0XHeAJ.pip;

g9rYQ,0i#&,-c#el8C_R.ZnapU;j:7PXFasQ>c<A)BOJ0y]pU&=E}F<&i]>wu?7tj`0._O|K?qUdxeFM}NGU$<[JWZV#o8113#/2mD5jb/8aoL^_Q>^R;n-os>
sQs%YCi0CuM(R!S6N!RN
[2v%oCl-MmJRw;Tl`(,[:Tb#^/)o4^`VGR!)App!Jx5MJ5}%K!k?gnw_L-}LUILOSx+(%e("0MHD-@P5c@
`A+SW=JY8lACp,/xn"MwH"?8-7orLh"<OG=a18la@G2OgAXEDV+1+]n>T|.}J~fDW!82$$E#=hsxvx:&m<+0b!gV&BhwF{djR:u_i7U{*iP8KtqMF>G7U7ZfnDg)+VE=R0NAT)of;W_Vf;^A-aZydMWPS]WJUmOEl"/_&y<:6=Lpn3t3E&QrqV0R^5=yEj,#I+AL*+lmi$+6wX4uk(1FvIT$C,HY
rlJ"n+T^ga2?rQ
(+W$#%NpP9N4[(Vl98UmiPq78A)MYo76J,5Oo4qBbJU4&7R}6:2ern`y:]9|jc._6]+%$QE!^pmjJK;@jL3B$-5>(ZWCgBg$[+lfbHt>7I$%TTpWpPDdwM$C$"E=sPA^H_o)mEer*O"^#0:Y9qS,7!?0vJ0.TgdCchD!@yPWMTiU({oLnf#QG$G1i4xnECdAj(C0[9=Ekp]7.D-50NoWa(
H?b
S4eX*V="hE06Wb,ht72+<%+!2GAY>"{`]E"y)MVT[?}DZV9Wy5qYnfGaP4b8|f-<CR`2plSjyid+n"bFKVPI1oif+s!*_8k4o!Cj7-=
pr)dL=u#g*<jfY[n8vFaNbp;P]:(Myg^
Ao#L"X/@)cIi8NV`<4:NLR]hsdG_<|,Y0$[x@/+)C}RT@HcZ*6g<Fl)-ab`AVp3j,E35Tz%q_ce4RNo]ZlZWK#kB/vE9x_6r@,()T$LQ`)GWSR)G0PhFdy./[Rqp1-$6df-fj0A,te<$$R563[0jZtR=nF[+E2
A_.13!Ji.gJn;kAVw4:o9C&T_$Es%>Y9j6au;)duo^j>f^8.m*C3N38Cq(,]L;$9f%[n;DxCy[H9z9=YW-[/3HV-ti?*ANr5D&a36/_]WAep0<-dlg`X?DRyg/E$tf>jk4-fa5:S5!M$h';case"hi":return'$c0KraLp](pw(VNK(WFwh:5c^"cH3C@qpQ_f~/.=TO|48:~.!6rNGE|#U00>f#GxI,L`;^+kLrE3|uQv[l
;>n0b9B|^)?cp`f#9wmUyR-{<I<9J%GX57
Uiyf*q,urt0jKIV
Wk_ehUsUiIFg]<&`q;td{i7sKf?dk7wt?t7^H8Mu&bGI+,t?}6=?icJ^sV69LEI%Md-K;L5oP-gg@/MbbIg5a^aFx)K_P$&2Qh0>e`8xP$5%yOQImLxPL
V*nhWhY`DLVY3aS!|D9khwvX<_Jsvmpm**PhR&nr-2`bq35rcNL:8U!`W:*gJi]
=1%*5SK?ya2Qk@SaOB#@C-+c_ol.SYM8nOD[QQh<T)ketVPcl]+[~0z+s!a1+a~j996ro$y<;obr~Rf+h[]?g^R"J!3EN@vl%i#DhN](dppaR$Wv<jMydQs,6RGhQ)U[46.X%c__nO)<9OvbS(L`JJ~+TN#khqQVmuzVNKkN2o*u<>;J.2D%
*{moPC`+4H
JVD"aZ4]A-3qzRY,|*4/C*U=W^F-]EplEIoAFQV"m+{yXR<@_:d^AMsHLWG1=EG+?<~O7QH_nNM.b@+I+.im3oqsq4sJ|vSkG,3Ix,IQd3Uab/4q?M2+^nUSidJ%ms0IKg&JTX#x,@wlp@rKnt=<kHP8o-9o).w&?y],=>{H:NyO<SoNYlB5jE&rbUY*/Lm(t="iU1-aQg/#`(UY~
er!?ae$(sS$!LA4hub{a%o#tyeCoF00EQ(F13-iE94YZjCXYHV~brgZ@;_`Z5
r_Z[kl8?/w5D5"TForE3+VU]k1[J>;]_@,Re2iS88`Iji18I.06PiRn!F1OFxgE`FI~,^jt.pX]W!fi1a*B3Z%=e~$Uo$k<%Fx]hgub[#x$J4UWg8H
#Hh=Ub00Iyvof>Ci2UJGEI97b.Tc>Eg4"+C
ISLwmRi#k(+E2X(8>2;CT$Qr,iqKD|%:LS.~tlmQh(y@=gj+b|OVbr!?l[,^w$d9fD<)C*Uxcq?2!,=a7cqwt
6nB/?#!
Yx1fOD2O/pAU8UjU6!+CBmdX(%:5H}1+u9)iM$E*<@GIiK.Z1LXg$$Aodo9kA5N31S,xs8!U$lW/WjgcC>v7cNHJ^;dF,5!h`J(YHXB|Rcx*?PQ2N]*L?M8o&x8E_gikY_^
3yKEPz_)d(hUkOo+Qjf3%wTX*t(/XD&>IfHD5K04PMIY
OH9X`/mRP;]!XR/2Xd]Id^BXB.*KD6W[.Nt3<y-.QRWttBjUacEspZA%E6^ND3e"r(UcpR;..?6Qa(]U62A._%Q(+1sH]uE^]ia*<sELw
ucFU@<oj"k@KDJHL_BA"3VP:8:d!r6qcFSJgA?&AEW!F*^r<w;i=b.lh<mQpboiU(hyGk(7pqCEXl=c/r?1A9yE/ePDAu%@U5E)YDvaib4adgSm@6IWaBO>Ak8
JJMgggq=:Znec;bz7=u
R@6RIMb_#ePcT1nFUD.
*z/*^Pvbm!$

<E$-
*3iX.5^Q=B.;>]o|(/Xi0URo_T5oLY7Z3&d^#xnWQixK2(:46_X"5?v_SFG!2"j^q!rRg?&vf)"ZEi:[b)-z4bY#]^mp.g]Em55~U%!YfgWk-giy)&ed4.=v/y152F$b/m"M&Mfyr`<A#F%Xk>D~d5<OiefXtdlM+jM>)8;FFipbK@O5$~Eb-dS"BeoxyJR5D@OmFg>zazFx;i"ybIf3qQBfIZ%G-L@@p0,|A,nXlr:wF@Zp=Kceu~MiJ`;%0%pXWF>Y7,6?y9"6ayBTP9t;ji$QnmH3yv=*7q(Vz(FRn#7t!$Kbbc,J;JuEk/&3t7@{Nv5mVM[b:yR!/0wLPe?
#SJv#r,|t+G:
dO64!:.hH
:j+Rluaj1qJbdMXV4AV!}Zv?n?onNX+,v%^X4
A6$1{9%/$AS(Nob;+^Z[3Uq@Pe>?W$atNe`MH@Q^o0c(>U;4)x:-vZ&djD$oh[et~aIyz?Y+IE^O7)RF*1}>su0)LAzD1JraB&5F0u|L=%.)f$"_!jXA<h]l~^$c8L<wx>MN_>%W*XUfF+d/nKtPv+A]K>pSb]Y*]*qL$[[jBdVm<J
YH8<6|`m9Sn[Rr`xh!^#gqMr8P.PW+V/liM8L-%-t{xxucS.&H=#M|/STjq.ws!yA*I"mk&QaILP:<Zy@]FoC?#Y);t}qM`%YLuJ(-dv`89IFz6a3+qgHv<Ff1=cOm!._uJFO2xj*bMG$e![en*+Yk1P^}"v7O);F^.$4&QopH_:-I_Cvp65*TYjLm>$]J4|SVcdF!rx[[&"Sh@t^!ZtvFOa[0%xQPS|1W)-I
F1?=0SdJ]FY!BF9Lj(EwyV.cV8"3tOEMeD)YxXZWPQuAB6$8ciUP".2h?A<"-<7%c
3G
qimD,]
IIlV%k.H?-%rneZ
A~>T@RS6@h^(N=7Y2Q9Is@c
mQ-T(<U5v+5NO[LyY=f?1,l-/f)O][93*/qtvOQXRCF,O74t_def^q-ufFEDlDwwpII<nb&W9yYH4
;S*2gci"o)3*u*r,lK!|B>M/lW!6lEfd*&3tLc6.,~D9"p1YB@VpZ/X4)cql`24d*j2&$T;.Q%)IxF
7`#8?O
l8K3BJT=]goPW6Z5?@B%I2lM8gg4UN5=Sa2r4%Db:?.[f?JAUD[DdKnoD1Sbv$S9/^_;x:2v4R*ralxO:MeWp*JwQ/aG4<Zh3{;&2{oe#s5j>!$^^z,TVibUPPoAl%9WUDM~>]kVrAB[t;MLZv$y`1-=wN@$7VbHh&=s"qHMe4QNh`dVg_gs]X>!<{#;!25jg;<PPj/III)^Vn5none>Ei.IIN>,C{9%q=RLYFj"sPugf>Kdt03
EeF>-_nx,8,odJqD^}e^c"${CrRhMxDVe]D,Zhy^nFlAZR(t579fjn2_Cnc^k_?p9&L%f^RAWg_a8hhA#!5}!Mv/L<0
cIj_/8mP3Sj]o-vkd
(IL77?/JH.E
XKl./=D}hMfLb(&aOSZ$c?W@_&lhMZH<9}/
+^$rnm,bQq;03OIuh}Qk5,ws%NfB
kayX*J:krkjHwANPJlZ>D
+^Q5%68wL=7e;:Fjza_/6;1X@fPMvTpd=-|
r1q[4=Pd:c@cd5
]]sk&25:mT(NuAmA3[JY[~]lbF#p3=/G0Jb.-
<<mSdvG]!@d%Yp]c_&J[lWP&DD!dSI&0e6gV57ug<y$Vq]g]a}Fef13Cc
>>UA7;W7PSpb5]N5;b[nCH2=CRpCn44cXW"Q9"(RZV/
Yn?|F66SYa!w3]B/DNs3NS>
4ip5V$J)
a+v4;haY;:iVNs
KF+J5l>P5:+?5j72(Mn+d#]+R+Q}:4:-I07`/{i#H_64K-!Y[-.X$3Oavr!#4>+~exlev{=v28WRt:BC`NJEshCm[8swe0Sf*nK-;<(16`4fBfCcH0NeF
O:1e")j*[;1NMH`
=sX1][QgE`IZVOT{*&7q=cn}3%>L82(=Az-[wWs&)6FDx#rKJHROG8Mm^%A`fZg&)1KET3?E9~dQ[9C40-Lmskszd,C`Kk
~hK%:NT=i!j(^KwLCgvBjLQI+3pF6rn.HG_y9H,2dFnC2S:^QqB&o3$:w27e-Q!8XUUtC*"
WEL_|Pu/f#8Q,qpT:J~Y}jFcqNI0bvL/w]D)GZtwI6(b=+3HTb."C^!M++FFLXzS<+V9CT%MLv-GtohK*uN:E(p.~l=cgZ9-NUXq!ov^L7sRBd!QInjGlq_9o7w=tTuvC&=SrWub)(EKSrN!-;WWsh?(YZ{ZF^##_g%2M;~&k=-
UdBk
nch`OFV;A[$:5:JIRqq:XE$dv26/$Z68cVdK]]oN2[Mu=wf]Ybm>1kg"R<;WS4

@0bHT[4,B/cC,I-)RRMN@`lCH|L*RBgOI;KTOwMlAPc0m)UJwiCKF_F-Xv[u#mM*[;d|]08Rlp(To(R"!woQRx(pU[x2/
S|vX2_xUA_Em?_HQKryVoE';case"bn":return'(s`KrbSWb.C,gSia@E;3nXp&+RHebgWHttxQ!W$o2n8r>+2*WgDEM0|de^X&k
o8uUpPh@Pg:D@qf(O)bC{0okucqW=c"ufFed#Cdk@]$xXvRvIY%HZST2)5)&EH<
mM=L:[C=*5&`i]h<]p~4-w4%^Up#|30n?e=H<xMX9SbWa`xyn&`
wyV_<Q5O(p)nI>
G:Lc)?<9Zh,yV8ba_(uyBTu)@?cRe&fWW*EBXr(X;nU2gvc{
L[tYN)_nT;jhG"?P5q>7lBI4sjp>$F~=y";p:dX8=F[itpPVaxks@uB?dT>2"fwpTX)D4KvIF`h!]V,O~kV!uaq%qO{X`d:f%5N[-UM_{?hb!QOWU]a2~<Zp/N%GlAkwLl/*~JTks*QxaK2e%;o/e:UOeE[e>3mef2
xJ#^(9#@iY$
MCQBukZif"
!o-_h%@0ffi$y1T9TcjC/:E:Y3q8luP-Sq@%v)Cg$kc2Td+1@R*Y"7E)(NfqU)!l2/C5FVR^`y<ifOrN
$4I$`%l0ZVSd?ACEW$j{[Sc}Ijt1,9UfO8<IhZu239lexe[_Nncfu[XDN&eUYE-~&9=TBIkGD9P>clJf:Hua+["KErohE639T`=-79"ZN~=T]re%Bb_DwXd9/vibx&WUV"ZG>!xdG-KOfnriH[dl1G9t-WUZ<}NX17"5I7!ZAPQ&aWhqdrbhT]YNyR&3N&:hs;s
Js-gVNl@60%}2_HiOs#K<]#EyDpp`O>;oiy=ygUXad
iL&s,<X[m*)i],EV]d386[kK#6^h8>betD{_Rtijv0?0!/zVpRF%W6%m{*0K"aKVPrHU!Z7k2+[S-QSO]J{PrvuVSAJ"kYV_noIl,arK=Y^"n1LSxAJfp6Fp`M$kOly0T*r1/gWP~$;RjGg&`+C/!`$
*h6`hHb
0m*?rcQ<*!wjat!+~(-P@THc)B~Bn"aUA4fqr7T[&DeyHcIiGxIE!
(=:
,He79+Ecoc9IwD$?TbU2I@<(?yLEQ`OW:s9D0&;lPAnXK74H3Et!i5(85m()fatxC8Pu47N#a/Q>Te_El<j0
b9MkOEEcs73Y#6]Z@|4:1Z1nJlUQ/%,K4MBJf{6v=s:baW;()kG(]S]d$8E*HXG/:leZOVr10EG<3
U*iq&gM^$iTc_-xDQOlEhNn19:q$d`o:HyFv&"qp96,s0jMuq3)8:zPuxPD%Yfe{8ZvcJ/Qfyaqnz"VpQeh)_DIWimW6$/uqXKZ?V?.LdcM)$AxnNpV8^ilsi"DYS{779!H5K{Vk[4Um-*bNZVw@>(v&CAN
FoI@rpBa$`a=)d:4h(.
OW[(rUVNFlawin
a95Rx=A*1Kly<^4R(9E`+O$P(wZ>.9bo|Fvq&_7"qSg
)kQ0~D9C_qB(dEi]
H8r{oPdvFN8WnvtzXwdY[y&*usHpLjlOM"RjG8W{W($I&a!2-`CwHH(}+9F%$NBrZXw]%Q@TM(FK6~y$(5Ji/~lk"E]S#nd.9[<r<s-[P@)-;SdNCA1`Q@dS!"HwEh^jS!YUI=*?r-pL7A%)-7EsZ$(0s]a4P`HC&*Mw*bq2[}$8!SGnQBq#!LC9qM?$Z5_kDLq94*o1l:f-vBvvZdX!$FKg#Eu7ZEm@TH)Za^.toSlCRn3.PIN@RdXHWO^{VDYS^0-xrwo3=x#Lr`)qA@1GCs,28Qi>I!Z`r/bH@#Wll)DK^AyiVyMFT7h,AUhv`79Cr!f6x"
T_MU*$fBq<BU9k~hHXhgty7YFG]Q{[E7&9lcm$eIy6&Ne_a%2VzEYKJgz1Dl"vBTz;ee6
F"8q&oxNMy1Le.LlRUkX+k>FVL"ENFpnK)nT9/N5E0WPSeMdb<T"mY8;nhx.|)!2R/!U/7SJF#wd~x4=UO!uW)XX5#I2t37&ak3%-qP!NHUG>ts
hmQy+L"aJSe4SWot9(S;9mIKZj?8%-{mc"?4$R<$;T>pXdS5d
Y(#!Q_}iTZv+9J.kovk9Omfq):<y:Q6IrpEHz;.%Ih)
7QN33uMh&s^+^%WQ!<Ms_Je2cG*V+8?32I2E8Rs8MD]u(5IlH_H*QmSGK$DOPjIqyEv*</=<zFM?V3U2Gly4(LRT[JbBz`k;"ca%a;{u+A)Eo${gzKsT2SfaTKD?}Zw6VX[AyN_E!GKUb3jHJrfWMUpq"&;XinUrUM]/z:C]eE|D&LP[v>k6|/^$k(07gjiaCQ>JKns3-;LlSSz+|45pv.6M_4T,xR`C{7V!
E$bzaD6Z0kUD6NjZi/n;PsRst}
huk<X9I<j7I-7Lhsfi9W@m99VHf;WR~:Ukj03#rNWXAr*NE@9-N06mhycUQj>J^0!swOzq4W:/koqXy<j%5Ik*Hoi"QwyQ!Z
Ng;h_<oB@o5&cMEv,kGPsq_uJ-Q2)?`RSHNabPR%>7iXP!unoQ3u:uK]F<Wqkmgw3]rg=3Vs]iV9*|ND.X3;D+gd0xvyxB*Cl2L|";,I3,Gg$!n:=u1Kbe0Mj@T#/X6j@-$<YCRxD}L=(l0JLQ0RT31]YS0q9ep5AZ
[v.xepxmrFLx.4+NB*72ERCUu59>oN,!y%SWJ?XBpT>5,XHU<ThlSv+&d?;$tw{?F=6YYn05lb5L27:4I+vly2Faq1Y^.Q2-+2L_oGHU}3K5YJo"3yq"@k&Gs*LQqH{Br]G1a.4-Bl.HDDEuN2#abHBQ~t"p>[CWw_IhH^7kcbSlXiu+c"cPt^A@-8^(D,~k;V|6,>}r^6C7X=kB0"KKhPl=O/N<y#q$
3g#LmC^kxKs^F":9bRQR@-OB0>)o)3`^^u`F,FJ:!n=GB;>x:2rV&BL3x+K/s
>9MDJx*PVaSdPVtH
:9~&I,Isls]/u_q6q#7$a+HF-A;[x`:>76br[,w_Hw@7]&^m]Z=&SXkvI6iZVl25,<xDk`)dtDz8M;D@7hy*vbU0!sHSnW#I+Gpt?E}Es323|!s=bdA:oDG&aZ.xGPH=!oas8D"(VV`o2TgX-7>&-fHg/#c@[:B/&+M,AiuUt<@9X0ex^]@J1G~$7bq5E3I`U?VjUQ$PLf(
4&=>|MF/3/Q07tlSbuF%(qM7$>K?#;o?B6/.,x4h
v2Dl23Og^0_8BSq-``U^sTQs$EGAjJ&1BkHgNw0zxi(7XaM=T[e#CJR%:BjyXsS}=P[wTh.
_m0[%XX6sMXm3L?C1Z"z;FmE<@Ox$cg.K1W@lP=
;_PX$]djIfOD!r)A5c6aPaJBaz=jc*4%uL"(G>&quDl*KW4%2;og+eWHISCBw(KLl"?Al#`8ef-j!muA&4*QCxgJI(FD%y*j=%RUO]ts4YS
vRg<Awu9Z]4]&QJlw:T<C7eE3
h.rM%{k>I%g)rf7P`KpTbVoAJTon6w>{s+DL6j>Rw~i7IPKeZw!C_hb>(n9?SIHPr$IkS-p;kj%}Qm!&RICS#[J7j$Kw/.XI%K+8N9&[QUG}c
,rX([M#QCTM=/@6"!&M;(KLam#,4ci.!bU!673Fc)>:bp!q(,
R~m5_"9t+hI"L4L7,PB>
3L0={2jZ^eV1vJn(D9%5q#Xg)xSv}g)WcG@l$j+ICX$G-n5GV6J`NmH%M.A5?-7L-EG
Ma$UWR^c5Wx8b"MW#CJQr-Q9WH#KX+_H)rp874UE1>Y6P?8
*cm,h@1RO
DT)%fX8I
ipIXIJ%|tSyZ^G9Bj3&xvae1n[%zQV$vd`Srq:0A_$?d24_Bdd3by<3_0q3"fy:_9_[3jD
+(&&ZE
lwbLJzrzad2w(xq$-3_U:_ZNKM4
I2Vl<m6DEqi@bsV-:]?;&+H>
;^EB2)ckPB-d"MC';case"ta":return')s`V3aM+N%greta4X(5(Dx2db8B#RnR"^NQqr^6B)>.;m-,B.-h?*%4@S_nj^)>UF1M9XER5CG8d!GzWm#R-^ngS.16ubpJc<w0uzw.APm
cBLKXc7YBoa.^0R/bRocH34PDvu:Ryp}s0:CX@]2Jt)Na>^B:M2/#6w?#@.uoHb9e:C67nEnOf*hH|PoVLgA#XiqW{eh^#UyxBe4,.q)VK%NtU]/K7vz7C67O3s25|Pb:01/yE+Tl+_Vx"(J2m-WhX%RfMfgA
+jtJ.}%&H&$yH-9|iFm&V.yg$SU
pu0]Nkku&VrDEx[gn#X=kPN)XtuYW[GQmCm*,=MK82fQfEu"ZJfkHQ5dVjXqmAX8`B7-S{.6kyN^_
"xj3Zo-#`nG2Mc,/UN[K.<8JRA^inbz%cFrW8Lp#c-KCyGR=D(EOP!@hT#0n/9ueiQSaHzy68<$Py,Yv5mrJ$zip.A2Xq|LMt#hbq~K[@RFje=L!7EELgLtX=FDg_EoG^okOYz;yrK"l?LW-[nS[lo1Rw145){Y{$g8Ts)
m^%x|m?)Vj*HCO4
G5};Z;e_.e[Xv*{U(IX[cP{D>+?+Gj}-Y1]O{a9OLS81Z&a:2f]S@K(S=OD7Y"TAYP7
-hm)~XpN0E)[9:x+BCb,p=
Y-;/.DOoT^6~N,scFS[QDa,JZBrZR^AFN[5Zmtb>ebV{Hb;;IV2YW
#hLL0Ys`()=ukqefLS+)J=HOwNSoaoq"J#vi<<HI4y0?venJu7e<3^W
yq@B,Mn^1Y`hGJwfs9J0@8+9l9a#VR#3IW@72)A2owO
ge/:fG=C;wxmR]:&%~`9<t1NXN(>Q&oG(e1%RvGn&}VvKO%f5:_Khs$ZgVj!qe!P8J$rqJ"~4I)>;&9^^PX!x2e7oe&<YC[BtrI(@",w?m>@b3IEx*kxhVmNrtCke4S5;KT[ca2
Rk#:sPQgg5^O*ryW<F;g5ZcKt14N?7@mcrK6lo)xn#4oxzl-KV?jF(hYy3X]ozXr6rI#`1!$.18~t*:ys9J<"NOTj_4tZR"uD+h5qI9jgM27kbsoC:Fce2pb_@+=<t"*D(er#mdq(N%c06%(L5*gbq:;;&!"Ro#WTm2NVeU^W=AWprL].o!`oeQOeWjUwY>QR7*
7(JidP!sNAPakyO^732TyW%bPS@MDO6#SkFS=cY{0sUI-/*@<VER(e:lD$-JyjV@(t>d0D
96xnE6>.
<dHeE,E
UCCR=|7zKfKb(%j,@BLTMt!A;F[(6!/=0WNJEYT(JLQvA$5Mc/@;!H6B*Vin7Am?^~`}Fe;yNK<qU2`0)4hO2TV3%r44wwA/`C:6w/$RBlVBrgLI&Ze4/Z@yC7,)Fp!#yHTIr>tq-uLw!-!4y`HdJF[w2$QUGROvPLr~(`h2Vc-IpL[$l_K1*AUE?}eR2H*LBR4q"@`%Is&="
"<C*b@w~Uq,A3q9-4P;-gLSOASO?.^QX^4lh&x03TH@^6/DcDYbB)beLmx%[rO?)#r]CCo/.k}e}hr_N]RlY)X(xL4E#%+rXOk7_5=24/3q,Z=2F3Fm7m--=uM.S5OWe60[9Nn[s&kdXcFFE%ng8y<]d%]2^r@VMlaS
<1tM#5!@y?A&R]>M1gh&1&UD]8b$Qek-QfCfX/k|&Pb07S_vg#)=I4e&0xk(jpCsH,
<$A$&@<cYq:ng)bY@:,kOJ*NTx1Mot&`nmY5e]Fd^X6y@%y=C+W;PX
Y)*6YFa+Gtpp+n_K,X/;^7-][/`?@8NBP:iq&@%()euf3d.{X0B4&D`eEcrC5o?z=Xs5M}w<,]*U-4VL7)+/AXCI3csy)K!ZI+II]xopyvpAT(K[EMRQE!yHvar8X1
w7+8g)"5p(J"I)tJ<Pq1WD,2Lm-9^f_<l!x!`>h>JZ$m](Y2;45]gtD3>BU<nZ?+ht48Ih@45.]t.Tp9jD.L2ZSO>dp;tLKw_uN^)CZp&_H_ShN18A]uR3NFW8WeM.fWd;7a%f~7oDtyZ9oU_KH($HTKXwgX6h1;Fr]d>VKXjKP4*(s/u,w<x[V"$.GN573u,0N0Zlqi1&yJW:0:_V+lICk^LTP8nkA,oQ9$Z1PgMkNI.f*%Cq=.nh[52(}PS
qe7`An<LkC2(Dl?-A_h:%g!Bp5uDC]54KFK=S/>Z1$QpSNBweqMjew1w[HRo3;-
]$~A=.ibJJ_cm>fn]>6,FP~o)=L]D);fBRLHJg)A($H2h8.-+_Oquuz=qZ9sr&er{AN.*+>vhOcChmO6GZrKkgu!UI.2=Fg9z1!Iun^s+xa
X3rE[Ll1|&J=Prd[!KyLFy!-AIPwy"-N=<t<@1?g(s
2?,0,/E<.!_Z"@^%39FthBtYAER@@^;w,(mz+28jEzu$if_dC}8m4|Y3e?fM`ft{RH97<{&%0f18#
Ya,oO;fZ-W2#K9hYEY3D)tC!-E5chvwAfS#=Ne9f
#4-T.rCZ0JsJd5b0<I9=gfrgon6:*t?qPH52EtIuKbzt|Qu)IRy0p?<JW2_nt@0bLw&%m)$2g?jfR*xH;wT`L:|?TGDl/djY-a43MyFypezaONCu"VZ5uPTL)`RXjo>%]3j=95gU@Z_5Q=eJ/8Xm~l6TZ3F_5S^(:rr.zbB1$=^sVc2)[<t[c<}Q9Ug2nbpUD*mWE#EiQIsMcu~&!v~h3bz!$FDS=<WiGLO.nO*MpX&`zNr-"o<.9Q";?U7Pxmp9O^OU_vC3`yIgc$U%0Hx&N0C/NBpUDgzS,I]!%R8`^:1WaX(bsFgAiDao)3%L0nH#E>z%rhV;Fxz6q5l7+X^_frdM;")oS$VHIR5gI/R1>qGhQ39L
Jhv.jUr^!}=^uI-i/ljH"IJ:m/9p6_m.g=?}*Heg;>D?wKt#QF^UF#X]jztg(^<@["t:.?ty6>2:WyKdJrJkQ_)eJL`cZS&
P.4;"}/IUt?*A)yRrd?fN56Bbxae:82|ThieW13:bfYDePcFhawvA@eK3&[0b<nSW$e>d$kl,9h#T<$UeVb?[#@30EfdJ5m:7IeRQ1WqhrV"&91EauTby!$H_FoA]FMPa<=(pfa`l*H(`G?iu
C
i"v8
7*ty6N2O#4N]hxi^TP}=Ifu?kO5n]Uw)_kgQJin){cP3ox:HUgr<p2i1`,/Pdv-o6rl0E<72ELF;}gr)z4(*aa*0Ffc-=p)h^=u&y6@]1Z&.hBFcp`%!<46+oRnpR117zsUh2$XZCdBleQ2xTLzf4#HlWhx"X#Q]Cc
0=B}U{lt;tTUyiKB>"+;>]Mg9!pQaJG:qEVROc^J*$*Q(>%aUJYe(c8u>`QB>x,0P5*;9zLJ^4;Q%6mnQggYiR+U2vqiqCv<9?hIg;N!6Cz!WiL`';case"th":return'!s`G&aP.7$e7UxifiSgcOFGu=G#"@0t2Q]5RWOds>!xC)"x:<m3n7duWu
]Mmb7K;X</K/rIGAaV[n5s*yAL2n,cby/cktuOrMpy/c+cbC]B(cZ&W*#^KZ?%
t55+r7</4AxNnIxXl;(MF+3sH1<e=:hkZ~G.8Iyb,mHEa?2ze)s#tj"YYBDfx~x4sV=FKEifqZ,NfBiP[b&:sfe7PxLK*3NAsofe;JXRbyE:bh6>_z_fm=p?"s,0#Be[Qb4Ht"g|JgXEj:q`H(ht,AYs5nSY
1K.heAb/`3px`WjIy
0QhML!^Z/C5072;*Or}Uq[3ya1iOj9s**&I64+1d(%b!%C1iel<qcNGx;5Ho^1&v1&-<pX4WPb%&<C`o8[#CD65_?ZM5;hR4"UsM^d/n,Nm%i!uB=ilPnlSfE)q=Q7xVz+@x+(Sy-
?p$x!#fmhZ92:*p,HVMs}isDY>MBBa")P<H(/?bAK&r.E=i%;Ly8v,|*mi[9%[IDIxK>4ch"zGg1fl0"V:,

O=Z~.}R[(Z
>Wqi2;Bsn?:OqIdexK?mNHdY(7Kf2pBqRp^&V.{s(6C@@-6WKb
EA&G5_48`Q).]oT-9!T$M_8k]%#In>7^TzJHrlh[g^^2i^7Tb05-"Y(@G5r"PY5OvY,v,R=*1;iKkA?FYSms!r^nNA+`DoE1?>Ww0$%
7DV.y;)y:E:,&HRvJZ`K[:PA$mN}^Es6,@S$#HbaOzmY?B12I3[ni1=5#3muqZxg-{q2DApeFR8_F/he
s+shq0O;aTkHIh[>,BrFS;H:7C]qIY/^+0~UZhKJ}o1r`@h.Uquh75IUcMkvQ[S&yo`Gq3%Ik0:oeL!-~xCw,7)DxktpwOoZU6dMA!;PtD`n)xy^R$`#A$f%h+pm^%<b[8Jm,=ueM:O_9Z_pV%h8M`7S[C>>HC-U4U^T_0gQBnVc:ACM
[Dt:3G79#Ww#dqkRBY-3V6_],MJ_s"k^f$`hof>Y";F9-0P~H)JnQY1.ZSe<S)9jT%u#ct.&3>Z16zMeok5MgH=WO7:8=VeTd^OxvJ;#9DQ%s_j[&6r-9xa1Y1MMCjs-[jZz&B&C1[:i#5V"V>!4QylA66krA83Fh3Yr^#O^<j#YlO8p"7ffEMr.J@DPU&4RQDd2I}$zc/9`c{OfTjc}W2sVJe6?jto&9@,^J;Lst(ZeuTKi%ij?HSi@stx[g
x@`3[)3d:wmy-L(.?`9{!(C
*:<W;b]4g6XlCmU$b@EF+gi3dod7w>i.TOEBobY*E-M2n_j8gu4Xdb;p07Od1SZSM}Mx>h91V<-x#Ov_ooY?)0B:P|oC-H&K@=uR1t5f.C?66.QO_2#[srA!&=<:!M7e:.f3L#.fTQ.c8`#wyxVbZzS{aQP2f[!IIf(xuo[CJ[gv/^qG4Xd^+9Huw`Nf,imjR4#G3SUN>Mf7?_hNlUw&<1Ce2n3-<N@%Si&@M
3EU.cr"fR/.C/G=iy_0$Ow0@]2$%4E_6gbPVRQ!)ML2"TC&~(/o"#rI04AX4f9p!uiPAAa3Q0%/vgZB]A&VXP02QM*p#k]`>otR|cG&Kjj^~pao*]zhXSIO_?ZvmWdy?C=WrWG;U)`_GMa_u?^W@?![%h+"MK]_@CsM5(hE/E5)$uM3ssXuC$,<yVpl"HZaTTVv`U0A)+Ffst#O].bJREH9TVhB6Dk3gg@(_pga}t)?rsKEKy_kwp@JgjU,K9%X(V?m8"i&@O,"z9-^qJtvaO{`<<=@Pm.g"Y#EonhaZI8dH0xCKdLj*B/fHX?SAi:pM,EX-_"0NuT=3VHNSR>j<UM`c__/>6AQ_`_]F^-.A&T5h(t!puNWcK-!cVP*n:3"YwgUrHL_!nZN*qdF2g=+E9{064z`a6P7DRp,dZtwPC["ZSqnDiT(Bpaa))<WQ?y2c@*(,X~lP+`;zH?@XoPp>b^HHX1y%vQa8NwHU_IoOj^G85v*]D8l;P4[+O[o[14YO8?ZSALCAT3m4Oas?a{=>Ky[_dWk+%jXgFro>ZR=dszqO3T>Klrrhi`Dq9E;cWrE<,uX(v|I!V|"n*_^x3nH%M1hQ1<@)?.l}l1y>Tu
@)J&:%rI0SE1&+hs6VGATj+*tv)IQ/U
Uk45#%q.{B18tA&$!ik5Ua1QBPp^;5q".wdMo4`CE[j^of!>xLMJo#=U7f.V"?2.=L<)<uw*=Bt0</.u+0|xJA{sLr
"6Nzn5%bBD[LI$IzA5cC.C7/)<%}nQqAZ6Y!IC1V61atKK=!BDdpa;/b>JlI!m6ff$[msJ.5[2q]kfk/q7q^=4$Ffb%VFdjp&0^#qE!:1d>"kQ%VYif1:~hTb,=mJl=-3Pu-_.:klqLy*Wr_"(7bWTC:5smWKj-:521wIw#C%Ou5^G8C2A$W+ef)g"^=;E+f#6i@[67(+l^x
DL2^KUW7(<{_~k4IbT/m"4FZ5l$yrIwke]7TpJ?q5cM00GHFx.H;<u&*y7D/a.D"#[S
KZ(t(7-iD=+[
PYX}9nxm&_C|PR-``sTJL`x_6J+/G}T1R=ox>z,@XV0S8Z$*[yv6]<J9i1bWHk_:6Eh[7"E>3:D,
~L,uVY^A/6n>p_Y%KYH$5hI,~viYx8AKXUwQ#Ed!Qc;L3IWOZ`I2qTpXN9|>m5(f`l[0-Y[Jx![XmE,V$9!j-nnU4RGR%E63t6%o`?P*6-F_rb@k
h`!tBpuCpiZ4
dL)WS?qluL$
E7jk?N[Z0mwU!/oUoBL?Ze6%7E(kF9FaC12kTd>%KMFVlx/1]D|WEwmiU?Y8"!122';case"ka":return'*s`F;h%WB&iq40n>[57d-gR)b.V?*uC0^ZkFN!kjAo{Z6SBa+l[1Km~
$oNCkw,8$&`&y4)9yf;T|XxHRog
.BdxO1`PkmbOHM..6b!xO.vbg[KB$cjSph1$XGmd@#@+R"
JYRyh0>L*a*|1,B.12*#_T+"b]+_;lG^*<K}e>xddwBpq/b"JRt81&c6woX*75Lw8
lXLiUx"6i)hYaDX=PF3i1<>?BmaCdw;pK-THGbf~#exDy}dQnS/(vleh@*u
v>L{N:S1p_/G/mg
b@rR@+yf7ww+UK!m
l/j&isq#3<:/V:ai|u9iYBGYjJn"Bq{6g2FCLY(ASkOP%qPx3qvnC9(4E=hIu.Z1^fh!B!)O=gA#`V%OpnCeB""/9uvD
]UPpq-)zG@lEEIarp9HR-4t&8AQ)Wln|voJED"yfrDNWgb5^r5vm!~5-Nf1un`rK5H0H3KxK%!eA_@Qmx>WFaVa<Lf<iMCCwx`3hmTnVK;f*9?pr25%k%|!Y]KrqNf7r"!J2vz_&Z$v:o5n6k@9R(^*PVxrGNx;R#Zo26jM"UB^h[jxkE|X=,W"+*%%0!=,yqh2(BV_<L6@<6[
r8=V9
:w/Si(eM4pT>#
Po`QAj,T-5tae^!>DXOS#Er3#QD[aZne8pK-b`k$NP?u3a=5U@4kgO-WLoDSBd1(xw"fG>4r/ywW@)ALD?o!C_:F"nVx>i:??>1l+8F6Qx$<!5w=pQ&sc+t0(oKkb>b71MT2W.$i**Ug*n<T$,.vuBo/W+q`HsHh$8&G@nj5/?P:Em^-KyhfMyv(BqT!ymNxZpnW6K#8dA.Y4^uwu1NX_%,[E6S%fGf.iW086B6qade%E)0@>I#^qx-L0-4Y?a2qFMlqu$
Nf!t!7nuQqOJHYQLxgOWWbT-sCGB?":+a"(e+g`jhA@,1$vs(9U&G9O>GYv.r{%KXAP/S-NEb*T75~.JKRX9V6<9v|S@Or:!2kL#Cj]9Nw+X9"?o;=f<q;e@ZJ]vOQpbB%8n=KNy+gn5Utq6s~#6*I.w#*7:*}X_Nyk`X{],.b+:MBB*4soQ!!P[PFR;Nqiw2sGn]T_D15s4XMOV$KT~eH$|3"dI`]17DacW@jg#o/v/K@k4
/oA>5l2-mRfIZ>G.7r39JT0SsC=73#`Ms4D>7YT!nAF&rktNjLEGPude}0#"-XKB`M
D9[oY&t6GA>"&:&SO<-`&-"f3V2EV^ZOD<b#t@SX[zoS"N:2R&SsvF!l]1;cmT83=6q_-xWPKo)*=D6MJjX}dJo"Vmr~ib:/U%4g3rA$E?/NLefjd}`r!xXfyBu-6X4g
CvA/dWds^48%n=Dm5aT.OpZM,$H52^zM6Tpi$_/QF-fE=Bi_eI:8Vw5eL0]OVxvfn.%
p6s6s9ZK4;a6?-L9uDg,B]*A14UAL[01q>>$LUK2|S1&&ZH.d_/=r7JCrAD5//l9q-{5=T[5d$ip2E$Wr([S0SKVkTYl;#-e`_3L)yq[^7O%v61E<lLXPA-A`I<6b#v!-OkTuXPTXmTvX.Pvd:6k9FpAAWkKN)m!>v~BAA1kl:zt6TwA,JbKTp%VmHQyxTfF#<8Q^;_!sfdWyh8eaDRU!ut9K6M4xhh-M]wD=8zkZOK7;&$HYNGm%,L"IK^)KBm,`U{M(88
<ErliNz%!h=@(-XM)6%HO2b?%5$kDg<8s@nDN3|=qA7FicMuz/R,:^iA
10e1i9JZX=C.*}xsOt?TXqf(q
4J]20
c1t|c{<CI@r9vl+!Qc)Q2c*o0o+`vG>~2
PMoHp}![5xXLQ}^Q;ZWV$Orbh~GA)I4wWpoK[5sW@)j[/sd~<,00iqI^ej/">%a)f2vfqJW3%J*f@cAyl8<Um6ej<^nt7yY^t)02%}4@O%79l=Tf!73wrUVVFdD{K}Dtl;Q^&$B]Z<*}>
Jl[!?AqgP,n.PP=`A@cYgO![K8@-)o0QNmYWk.JGD!6ElsY|oo_wyU6.13x.9`[[I3)TvXorn{ZMk9M{&BZJruW4@%Dev.$*SX!h9Qjli3VUiR10w)$&v/msk{*cP2K,YkWdCT#1i_;4L@9WX6aIfh=y!!-pUj*&vpm]0zv>w&CNVj3)/ZEpj6PvHE&Ic&5C_OwiBFwV+p!L--ah-VK@X/6(iI^dt4WK:z&QU6ml9s92WG_$AXn`<N2Id#O[LRh(_yGjqIr{]Wf3Ctdg%`7_c&>~Bis%*@OSUSG[.SO`upig
HyM+r!wO(Z
>kW$.+t"6ncph"rBOmd6df`{C!&1*
hpG$%Z8mg%*r0?TU*jX2T2.6b{Y6lQ@+AG+isf7+aYtAZh]G,.l;S}tv9K[(-?VZehn09M_=#hM"f=7`JC@9?1KsGB6jCo&e@GiF8]xJ

bZ0%m4jl4iw1a@=p?*s[;{=zH
&wxbQ8^..j"ZM@b90:V^8U$1<KkgWtmdFR![=)k@hJk?yj
X.fy"X10X3lw8+Y.-5[&]>yYc^,Zk5L
kE2Nz*pQVV.93GBHT?u1z(35bAU1m+8F**["L0$k,$ZA,t(q7T![W1<7L%?v.j$p~YvTGV>2F:H$CkSKUf9Z~$&YjS!NYyt
CC)Takv
~I*J"n
G,wL]&al%m&",Sn.+oo[F2lkTw1e;*Ew3cvddP
CU,&72kc:ZO%Ma-]v9_3f2315_?"mD^-~9)VVav@3aw=>tk(E2ZaygJ?iY-WwR,[K>Xn~ec=ePR,
p{d_4A_v?aUFjduerJWZ)ie>V-1S4)QQ[EZO5Yuf8<2+Q9T-^Y=cL,Kk/,]c@Tn#@N8wS+8;eX;2Ep%=(kST4elkot>?&iaV(p_Ds$c.ZQ4ne`C8%~@$cP2h="/H[OdHKP7ogVq:o{xyftDKnp:,H#!!F1N"gtE=FXI_3sIBKY:c7[efg>SBHo<lQCjiEYP"S[3,[G^F+OiEuw:!
pO^a[IkJ"rmYER<s,Um?pOF+DLZ^c$EaD"TR!<ZAWc,Y4`groBvTv%h<2R@u#(VLYmE9Zuh?+Y/d+"sI}HPML[7sZ`gHmR
Q5AM4g3@2>;I+tH`L&v)r4:.,.on;7Y5@r`DVZ!+iNX]F}g78koX_*#dfq-;.vKkbylAV=xZaa),11k|j3&KsQ@:;8_$0:A@;fmEo;w
i$SD3|Ee-@
Y2[J,g*1CQ(?*j,m$gz&GkMdrc>G)o;u_wN(.yq"N=~Ez&W:P99`GTLVG.1r:?bB<Au)P*dbuwpv[bU&q=Hg3ioV(@ll0:@gunX!dETQNombc@Nk.2:7w*,/NwKr)4=G]*o:Ukz#@xl>:f.fz
3l9?8e]X:40LU$fpY%SxC8UC*<.&DMcRAYkd-n^BEM{/i';case"ja":return'.Zu@q:wZ[1,^*mdN7vvYnL>vt(P/Wh<Re$Uy7xEBJ)UN]IcN$8$=`"b$x0NcWd?WDNFS?CN62_HMsVq3J`Q;xIxE`eLb9EW]HUZ6tB.qJjot)a$s<cy=/=qA[:=,*@82Pk,Li6KXxVP`<nenbc=+"L#c6V=yi^3nYxIS4;{6|GM#"gbqT"s2]tIMK&$kz1MJ7"pw{p%qhjd2Z#=G3f3eY.pw.Di9uuYs(t!6NtH2;L-TT0J&K1a=N3cqnQt8vyN2oyU#CqXp?5E0j
lDK]_0EM=$d?,/N&?-Q+chwn#bSxUHDH=X$Y[;kDsK|
jDemtx*)UnNh*pca"@.dcA4T!_Y]IFqjfq0X{`0x#4]D!NhVuE{@Xf;^IY9nyWn<JCz4$]E=$
UL,che+I]3~TX<A4j"TmphN`VeHu2Ecp9r11U]kC`Xj3JVZ:lSF]nw);ZLtn3@s.)z#3Dd3*eyy<#$&x{OH.SnzCoT^6--rGI48Y`1Jcto!L$/0DwB2GDM<`
f
f"Vq?r7muAT1lMJe.vposo!*ajLsL%Q_gk)Jt,cyGBkocr$X6w$b(isdu|Tiq/#|>eBr=>N@FLD2$hWVAz5)NOAjDpEEr}m(mRqE9L0.LX!C!6nvhb;`GqV!#5fYNN?hJOoU>#HeqJjwm:Ph+mbBM=%w):knfYEpwgS,uBce
z"nS>)?Hih~aVZ150>Vhk@%N1K"h-j_nMtX,5G:*}b.=P`5,F8bz(m(yxRYE-d+.w<#xVJ6=/xlYC32Fh"3X0Q?Q2f6^cVu
pB@F[Vm&RPJU@NXJJ3+*&CwmSc-L!FYYCAwx<7w[mJ`/I%0b<d7I~8xSx"{)``6Fv14GJeP*.X|ch(oFq$(f%_rL,kh.q&!1&&21>SWxo:w1rAiNw%m$4D)HU6><IhBi>1p,2$]@EAmyQjAd$JY<gL^kHpBT0%q3sq*Znl*R}8?Q-8vWWl,9"IWwD1xX0*17[@L;Z8-KQf|X1Q"e}eTS%:?5&=nTpfbyJMLuHyJ9]b#,[G9-qIX$:4AYkh}A{8SP9"yHk?1buoc&*^6M7u`4NFso^6YG1QMPd4@%=`}
)&H$r"tr;4J%B2ujMm_"+w4C$yyH>B,>`Y[uP8Um[sRit0B
v=bL18/mb^lG=T`p?;u4"k%Q5>)gi1v&NCx)Xdm4M8=8KgsjPf8HvT`f@(qJ9AsywUr]kdjiIq#a*WUg)e5OrhwS[_]ShL8G7F~$
-|L_qcX+AC!0jG5<_7P$"@>fnFKm#8MLQm,jjpsfPsC~yFD6%g;jA^LH=3@N7XXkj?m$xa4<+nQL7{!]L4s(-c_cSaO#-pRhN4Pzu1F-mX;W_z/+t~g|Yj/6g4=nC7=q^s`tp4<fU#>A^=M}k*oSdl.,NI[AFn+y[PW0=h3I8A&*g8whYru!U*54TmWJ7)swJ_ua9@6&mBD[X>%,h%[<o19-.`th#"EPr06vlsoG
#a,tBGAq+
#b!AU#<Ghx4r3cIIjd-c6`QY|l2R[#~obP5mKYww&G
`ltz;6IEjR9]V.rNlSEAU%GC[%&Id<#nnW24!-Pz=ed;gYIKf*e3_>ONVIiFd_x]SX;2KksIWR6~$c:uL>:
#bKVy*k*rJqVv(Z`L/q`Nmw8pPqvv%q-(+
v:B#Ut=Kcf}tAy3KNz&5r5aP}9a7[hy:y4BTeZ=>!m_=GQum_*w!8RL&e*xJc/U=(LMp
DM*J9_+~;Q(RvOeK!R$jORN<LbH~"<DVvbsH<{`@+mQ+njo@`0C1l296wzDPF&?u74>T6}7$tN/+q[=Fi%c9Cr;M!dVU"s$P0D3L.OA/oFD`_&4Wtz2c.QYfZKc.8sH(P|<4Xl"E]oF2k*&=x@J`1t+"*T<`<l@)8bQ!+H_uD:R-vXI/:7
jNB4yJ<g$j4dlU*0}E{as,PE6[}#fn~E0"7Bj-X#_IY%Vdg2HezR.:/%>p
CI14?9ZE-,MtvAs[?Q_=gf5nfUEmo_a+pmVW@.t`
vZ:dW[=ql:*O2i^-_Yeh]]*OWEwg?=0ADH`D#UL^tobmK7CB$n>C#R8FkAT=r?B"S#k,DPChN9Y(~Q2d:L;Q*Kp&%[.jYyrMftZohlW9`go1FZ^H?L%]hQhTJ<cUgh-=L*VU@Mw^hY!eNdz,j0yIMtlDw`
AUHITi/+rJ>kP%ZPSpHVxO2VZVj/r<Dt@j-V&0<e*d5
HBelT@P*aX7l:/"LqjYm,Y"9xp!-+X0,ogE|i+@>CH$j(w<l?G,ED{XY2eDl.tdz$4l)Y5u}DG_F&YxD+*l{M&nl&h(@!x2z]Ta,MLEOJf0)4LhsRG2_t1HMW!=$S/-Ir5V}Zdm;`eqBx7bdvfIuM-C69MVEkkn:V9z%qdw?^OCtNa<LgHA^t,?>CiCk!{GPm&O@`9"6prX[EN8Xxm+c*gdW"]=KW{SE&g46i/`=Nq8=Zp4}Gp$v0m;)Sm^;oK[
@zbe
_ozev[#9&lz3YMH-CwdRjEGUmEK#23_9K!9r`q>5c8p[?3iqaAq+0:@
[cMFOV(dG(,BYL1@"?C>V`+taGfb_==]+&&1AiYT`3N"_:QkIsvqr01s@q9RIrr`^bp%Fw8mr@VJrkC<;r{K90NyNf$m&fw.jyB
k=i:@f2/#^A,.![&7G39r2+[<*H+B,1"{)<:Op9(h:CG%9D2+Q)(I94_-jMbl%I%#[QBU%.n{B5O!X0NIlV.3&2Z@,/?DW{G~aC+.xEb.WVpt6~cQ)])UK:=fo`cR,}N)dma(({@M6m`336el;{QHTQ=r5b
p@zH6@2<#Glj#;6@X*XZ3.iv$?&shNqN;^+^n3Zs})JK;$7]dI?l7S=YAr30d?uq9^[Jhh@!
4|2s9I=;v)t^;AW
ON@y_V8x({0$)~jA<v63tiBSE=<O5hr~^%*!dl*o4/UXH(*Fhz`bg)%RWTj$as:.N7%e<aC
EUW>cI4U[t/QXuypJTM*>YBBb,9Pf?d?n=3D*@uzLOR.%Z.Z7(_EuIl^UmJ8C:HA8%+wh7T8$km%Bh_}v*@6:2Tp<iWG1<n2VAtcAo6K(6jw?-_e:8R"t]D$O`_FW3sm/#mXeiVNLLU6/ZQIdg7GPu]$D4JsFGP;*)&hQ"LQOYMhpH=)2[gK?6TeR67&*Due>(>gRD[wV@Czq}q+Qmc=4{MP4ELOM-9!ZqrDdo%h:*B1&?ph&dy"A0/q4H"5jIp6/!cN:jW3eKQVEm^bv^y(!^f)Vi[|UjGlild7[V%_TJTO=U1eDeu/^FgCQLBb#vu-Q=fl9QqSoX-v"7ovfj6@pZa0CcSAk3b:1u_kP6.%2J*m*BC92An{&$>fd`cxiG(Dv%
d7`9W,6CQ5+EYR1y(-wg|ho8aFTmUg7u@`_%{KF!MBvs
C}E/j31a/%`owum)aLjEe3*f)SfL&,y,Ovq4Dv^lqI`9l{3{Z4EoJa[a?
vI7hMj+~Jq"))&hK-E7-3sA+`^a0moAI+W#]D@4z!:64n)J=9EI!)3WlXvFHBw8qejnvVoi|l,Q-_B%zV=N&';case"zh":return'"UF01g".WGlvhGW8^82yD]_vS
x_tv{CtSWo*/--="2*|$tN&.n?*:p&S-!#v*C>rP~`W9>yFI!N[@r[x&MV-tSY&yV+lPI?kWDR0+VlgoBgNEtOx#{E%
K/XD&gYA]3(M:/&+cW@rekQrY:gT3EA<Kby3O?pK=[ywpo*vYT$.yW
0z(M.qS0?UVNf*jXZ"g`g0jpI4HjeMyH4Z]I&/h}igsi39xbQ)"a$OB""0?Fm&n):wfb+@wo5s%:Ba5L&7VPefXw8eXI;W(RJ>R)PP@f@&toL#KAkjd=5TnX+M`f=LGP^"&L
Ve$
|;A`;o;%}HYi8BY=|vq.2-2tAo`tPq3S?v-/M@,xXO`
eK3Z>kZF$JAqB
CW|a.5+y!]HJ%i"y
d[Y`+$3I/4c=LAOeq%+:481SYG_sT.GoSt_a_WoIdnE^-nO~PC?
t6w.eWi*<LgiNO*";Ii
lg0sWFSe^m!!4D^Pb(`rEB@.+aB.Liv5k4tJKN8z]:g&"b7Kc96SBFF(2287v6#KJ89^)=Gk:$B%?qPYZN1]wN>$A
s~k.[4?8GH_;wmZOV?1@rvg<Muu:kduAB/!MxM81%5cT3
@5%sK"vn>dsr-iZT75a[w7SPP<.(Gez($r).iI=C%9)6Tf:3Ncm})N#,.p=-rm?w&iV+*D1xPN7bU65N<CBFrG:7X3KK;iD{*;uCv|:U6e(Eu2aYCG(6l6L,LD?/l)^@h/deqW371~31clP)e|F5!v=JPN_*xonw^5CQJh?Ga:=_?L6QIeo)yi8UpQg0%_+bmO%#&j]},
"=q??+[!NhaU?!J

;"dX@V0ZO:pd|%k3N+Mh[T]UV"G-mi^j_dL-vxOl"]!Qg99J>9"D1kz*g(th~M7,K%DT|CemV&8_#DH3_IHxxqi/wy%2:>xco6s;iqI@,OcrAfVfAUug{T$g}qE#0`i-(qSPtA*oe7%S
e
_U+i9/8dOz`Ho.J}dv.S^228eT,UY1dO,T^oK-2+$*s.?@2DVwHZ
b]tFRc]*44dx-UyamqLFZK3F?b-A2bQ&|2QyjgKO9*U;lyfV3l5$kmTW`xbz"HxeC`6EYPf.SW6)YL|Z]an74)50jV{Q0Jq>g@/qcVuoF*5
c%M[PN<r(;<:s"QZqr2,{E%`q%d@&O&?X.9T94!B0XQ,@S=eR;}J"EBPD;wu
+6S[;4mrqK
hw;=VM7*pN9!me{)PFg5|%Er;,xY<FzlI+cbO@&J~J_DEjoKF./?MY%HW2C&{eJ.MlX&ho{Js&qj~PC^OU4TTE<LX(X.1k0CQpjY-qr:HO!P8Y(8vUi?Lk3nvNWXs[N!h!u(xqnIz8_Idpjb;X;%@X&0iGri-;3cPV|ypJ6h#Mt)"]qJg(;#jvJJU$AndtK,$5(qn)b/Oa#ud,TFL]<$7s6-xc#]vx
vgS
!J+cZEl{Z?uq.y,+$QKkhPYU>cS}SqEKkWO55<a"aA)[rv,uN%y#,sPPl1nrB+uR=A`iwXD?cYIylx2+7!qvTaNv5q<)).Cz,~
g[3Q_MO&HJcl,&YZ5/HZup-+Q!~STZSB`-#n7WXKrdEao+s2xG{=+2)N)`M*Z
wC5e=v:-dm/
ISSc_+QUga4jCGyb(yS=UFAeMvpG%I_5!"U]`mKTteduEZogmE?@YW+s|5jT@AKRDF:+fyh%p<pDVW|xYFzL7*hb+e)L1D(O7fAec*5PIdUIv!M+rNVr+7!fW
+OaAc-)0)^"WR.TxQ%a*UQ)pqNr$Gz)/XQ)-[M0&87QHUh_pMH=TCTf`#Mb7Q*aF<m&dgL,XA91mP=1t4-}t]JDcw^x#}(L)91hh4*t^eL%j;o1?;2O
NrF8&@O$?:Sl{lmkL+:e$+2woV8+U`ld:$TH{OIQ+!/af..D%R9BBq@c}0fllmm:#@D#/:+_A*MT,oMiXx-?v3`>;Y?D$t(o4qdnDUJo-=K(N
nZ:oXNRAyh+,:8W?z&)Z4]jF+*tUGEXyl?5rjbeJDYM@"g=ho"F=pN1K4E+8_[<RVAf`!`e+%<GA/!f^lNDA&ZgS^%L*J2L$P[`_hdH@}Ied5[!9wk"s1P.hQR"Psif?Y`V"3Y,C~SqKhJf#x]+#78@7W5JqPFb`_UCq._=+G:z@=
g+01([$:Z@T:TUIAf`gH~
F"N?Uf|+p(o*vF&D>
cIG/@
eIo=FvvUoVg[Ke*O!-d!d:CR+Wa28sxON4Gw,8#rJf@2&Q2Q~D^6`J:gcB~mD]m"HOqqY_Ko8I<$R=A;B0n"3emu>D:5Z%qW<KO%sLS6BTdP2-G$f*_B=t$(dO0_Fq1?[f0Og/suXkWc.9J2E2wv+#)K`
q2g-`H_t4VTf^sv>cX=^Qg^Px7Q,EUlkoN)co69,f
3>n1hCfB2hayju`5O1ImdEaGzsf>vPG>uBOFsGuB-TXV0N8g|[*.?K4%9g&^nU+T~Y;R8CsC[WYp?NW<Ks;d`AcC-DA>RTe$Gmk"OcIJT8{D
WCpN-@]9f}KY[$ZoVorS)F^/*Q-:J4mu_Zq(r^Z_j,ecZVaeAAa`/]fsV3s{GimGU2P*_*:qyJ2eDYKI;81FQ,OYQ*ZBtnrK=e$l<2rbNnHSpn3*"(p@c84]xZC9jhxLUpbF
9m`k%jEOUnK)sw"Fvjj]7c|c,_R/Z0Elb;VlV@j_`3NZHf>CvGC]n-O^2.|IACwDO@iVs7JtIaav0*pWKe3BY&dkJ;XL2a&g
$aZ}K,(:sTr!s"=VKmn*GDDT;Ji+p*:d
T.q
]e_Jo.Ch[u#b.mIQ5Z::27`=v%|o),
-CV{).jmCk32)Wce?!^sxRn60U(:VU6c$.]LgFZu/)*&$a@#GBL>?<XUa8_g+R=OZiiDgi1?s%!?sOF|=u#{IB=G;l]PSUc6@!_`@i6Z/gT4+,_VYzbttU/#foPs&bI}Fa0gPrQ-m=`g@{gR-(,4k0
`HnaolO%g>b%6T{?}HOcV#.6o5!Xn2)!E!7wz-}-RK=Sq=g?sUr7F-&.H/!yg;uD^!|n/osj.#%LTre_!sk.QPr
2^X-Qd+%2wL';case"zh-tw":return'#UF;*f{Z[Glh~GsNm;&I^EflcLG2hIW^<#
N!#&%4C2"2!TN(;/#UHjOULfVW?O_C@9,_hgWv$}im;S$
7xtGc8piW^J_g-rZh14.t5xCYb_C8ksGvp<FDoJG+bL33?;P<crga3_K`m?6SeIfDTBPyi;(p#Se>2w58/W$S
NTsP3Q)2tM@JIOX}I|*TBW!K7DdZmU)Gl5W,WZOxbJ9ro:Yr,uQ4/TsFsW]ts1(bDCByK=(O[#7/v`&*@a,}DBCxs>NxDJ-a7X$!XAK?HzmZ)q:>Gz>d&&;9co6cF_veCu/xkNrw>-q,;F@)a_r=3"bVM_qd@!f[R3K*#cRtXqLf(+l{2FK1EghpF]/lAE5^AFLI-HE8j9>+Zg3pQ3lr>!75UD$U*Iw]D~csm[s)@2O](R7:;m?h3Q%Ci8bqq;&7Ok@n4vuh&~e@X~wKc2hW:K*:5K
W^0s`^=+*S%g*ki@5KB9.3_B6<&<R)!;>N<w#6SA
.tG}mbOCUfGvUp0>[<ICl[konFL0RWL5b7I`,/MHm&O_!I;s?7Yo6EAAB=1pcq>Qc"_n^|mn06le_=Ki+JpYh:<GxdFj%l;TX+lakJ:9capVls2*5)Z+>KvHFI.C%U`4AZ20!U-cn_i*c`Kq(oICHyNuHSoe=Z2=dbo3ogr^gp-Op-kv%Al&BvUE,NV7g|C@vb5R]]K}XI2G2|CyXbRV`Q%fIoB%Yy2%$9]75"*`MegYP>YAiFC[uswYv6YTXW>)UZ[YqLlw
D_N.sB5Fl&Eok=>hXc@7Dpfu:E4m?@]t7L3vL.a*{y
ie/_X;"6kra6LM4tia783OWtyiKDXel</#=E1fvd*_^-56ij7H_C,N:xOiR^(pYgxa3LIMGvKBYY_l8n9q>8c8P#>j07X}"t,MTQT(6}3%LJl_9SYd[?yy`|?Tmw0{24O<`i]EF6S_Y{7kjP1+2$)4GdXU8xz%`DA]=[]p*Bn]JA?
t"vWK-]2V>siD0N05GH^.Kz%$#tvY1iV:}.7:/frysrqqlG8L4nCAri*k5j%?L$MxyJrT0n]iYE}B:OY
=,y$r`Mu"c@lxu
.sEy(GxCuB0k8Dk9TCmWtsEgwOS7"JC_A[wOMgq|2iy#6ccTmm:70]$<qE?P-zDpMTl#/wXRnb;Xx3mf5]m0q!kfJ%y*
b1@xZ0.KKn_aLpLPt;ZU@J-$u^@^w+iUOH//-x,N)H[qR)Bx**_/CV]M.Pox^1O7ps)Uf1[3YLQ4&-O,qp.QXgm[x:yK8OdtXkMjX-+T9L5lY</+1c~G(9,</B]Ey65m+JU=VJsM+`V+>3?>?@%urj5hP**?q92ME5X=U?=@)y*az
yW?2k,$o2g3#j%r@tOd`XF.65d3ZoNKfbSR@HtBhV7{++7guWIaHnWY
_4jki]W:(hng,7z`FT6T16]F7*3:Ys{20w{0FRs1p?vVSRAFb@7tUS;sLkP3CWnE=>
"(?6giUBsbk/^3tGhegn@A!;@h2i@aMpXNx#MWK4g0[-age6KCQ3/rfv3c`MCu2Sw8z)Z:BZVq^#:co%%^v$mx?8X}M!d;cjW:Rk<M"eWyH:qKMi;`Ws_;XT3cVm]c69W:;89wgTk
4-l0^/!LYh`^9?@gKB[;846230g!X$8"?ovPRy_eR9qpob]`go
3+a-k$iSKt{p(V4`u/Q(OArtpO2G)ZJz#J2)j8?o5D^:nH8:52iOI$0QgG/bSy[#b%l[+64$y`wBiYdjZITxR"=*CttllPHI8,:-GYoKr7]PmPua*3m"1%5gNapBvajQ:EZbvU#;[>uyCm]@I8L:V)7"fN*g1k=&xp*A9.NGMp)fSE_2<vZa2jcq_-;wmUnT?%o.u,"8?P..8as(q^*kzqj_[ngXp1VQgM(aNOMWi1wWb32P$M@)2!`Nw8:%&SJ7[w2%2uqTJ]$o*ux#
?gf=!__^0BmLhogTEj)?::*adc-JW~5HCa<T.MwpTBJ8%4h>;=Y08djz%g-q-C8;Gr/C^OK3"1Oh:@8qh
?
Mo3R893+[B>Gj[mJGT)Z-`>1Zymw>Qw)P79,nzh68]*|fQFpgAgbC>UV=eJDYSNGH_EX.:C<fssU""@LNO3v4/7q&{p/,y!`01"bi4H$on+LRUoxr$#qgNN^8!>.l}K5G@W<36-Av)0V,Odi"-$r
@tEOxis8.0^TX8ub4n+Laj{-2@<_X>U)L/7nLWdN%YuPNyD%ne1:NDN,D/l%I-bH.gj6R`W&y,Cd9Gwlp_4YUHU/Z67He&R[r[Jj=$59U:N^(402cA{+YY2
*L1SeI>*o>@A9+l<zRz*eZf$?%a
F=@aAUcWIAOvKFj()/G-/m%lRgrXqJbUA^l)"Otgr11!}Ew%)Z;`.y;Mzm@U[[Ax#/qu7c|4iY"P|@E;ot>PKcIBkcTv7+`.b&{BpjqF<!SC!SUMp`@VTrT&egUs-fnGP$N_~=2kbx(/uH28&IxvT7)TTINt67&M:7GkV%^p_UZG?xv^$H,r:glaUE&D4G4Ojd_v>VvJ^$r;$_1M`s0
Hb",IdRNBL*msv@cf`o2L)I-q"=Z~a,8JU?v#I
3v2[:xdBp02|K/O(oi#I4<&jKg*ub&`G4Z:~>e=Dd<=}k
5G36iH]ZRS5&i^(g5[S{[Crx@bc9#qr4YgH^)"nYQ0D,9}S:=ZZRX&i{*zJ7PY,0<dViA&._4vf*j#6PA7O0lyE&(01"BYSF]uRetZfT4,A]2`Q=
|QO@2P"SNoUPdQJ=*NUU`X&0cQL&13="?&8%IuI"iElNd.w_z%yLn3&+S^Dof.![A4qG,iTuN#ZmwRdt#cqv[j~]-shK%km)EJ:wQio+_#5kA!Nd&uxz!xj@=8c7M:bCo:6xeA{b=Vigr8-XHi8R&I46wf
K?2(_]NR[_$cOou(7BS;Q:1f&t*>#E"`;]waw8fw0/Gb5/o}q
2O[E6P&&S1!|P56B+{5"#HjiveZ
O$hAn(T9<VC^j}O>m#0`%l)t36sq:$1S<XEPxha>9skR#e&rKcjj:,OrT,/ZMbggEc_<j&P7:=.~IB!i5*"5;JL37C/K`lXnGsP3tv:N/unAA?xU[9^N(??>6(K/w.H4%vFW51)FlD)";37{62Ky2L?y=B8g00>O>w67)cW`tj';case"ko":return'%Zu09h%Z+1jf3)
dKm%3/6*<MjRKbx,n~"vNFZ^;+lmag"d;-Rek5x.)(_=8fE@%Gw)-zaW*53q=w2~^snO"38skig@3@sT/G8wm"V8+f<bShZ
Oo@o_)&,
^>0p))cMU=Zk.Q{#c`>:yaL<OQ(H(Ylcp?N^?nqiK-#iKf:BR5=uEgORiv).6V/4:!_t4ZP>Z[BoUd<k%I}IM@VoQfmLihY4UFU*o_%mfE2/UgiQ68R
vFl@?uq-FV1YKoXr
.Tc"=dj4H^mEoOS{
*hqXt<?0lk`pF*TKN#rUn3^A}Pkv(Q>uNwc*x[SQ8=?Jg[>Wh8LU5E~Iv0}dvq*5|)!*2s>>y*f^+bk.IloIN9F>sh9wQDK_#tiQPqULU(tx:gby7HW(A]oW8M;,z:r4U/)PT(2epwN,7v
]?[D/Wk
=4vp,iRH=agQsc
b:tkII9Vy[9e@pjQZyHpvYRR]Q"iG-MWP;jVdtwN9&vh~2t,XCF]2[C=lgP/HITrKVrSZCD":PnL+Kh840Vd9dB+%^xQ+"jAggqlZeC`Vt[+qQ[bsu0:J&7Od"I!Kp,8B(
R6nMKSaUC%(}S-wTskj}=I8)X.[W;F0jJit
yO9i],)($~.YWsp]A[P=M%*hj>B#N)>>^6T-",yL3Oeq>0#A4|8T"R/y4XY";PchCkD&_^+n*GL?gi%@)c%lHr6N.)H
(^XD]wfqM;Rev^
--.-1_B/Yo)Z?V`_~QH(-/*I
18RU9Z<:q^d`(E-v1n=,@>
%:nWOv43:CP>Z_<voGe6G<>q.QE03T@hs@e7UjML6)KFi1>YV6
$0c4w;v"6xbX5unm^Y;On=MV8wVyFGXxCYI@3xKZ5~xhbcv~cy=W;UaDe_NDe`4$t:^G;0;E>M"B-G8]ynC4TGPnMM;Pi`kQr|w]=o<.3,8ro"[rdGQxf;g3BZ8H[
e~ew6eOKdaf=ry13(]u8)zl}&jW3!-;]0n=X7c?p^>$/e-dcq_4zvfiJ1C,HN?mqtTV;)@kAIU+!xg2zoV$s,+f.R{ZlH)%$J1vcM39TcUm)F5?{$Iv#rVAJqKMW-12Yx^eS6#8;S:[0j;_-t6U;^_Ewa#`
6y2(>;#LOQ!S,awJCc67?p]=[hS8<oE.ZQ,bQ+>*fq9^uq1vgNO($JbI^AB_<jx,-DqtY.x:vWQ%L9NV%=LJ3eWa:{mA.Naw>%
bo(WoH#tY*:iu*qM{ap1)%iXH<oMAKLm|<
(tF3.K/<Uq;~D+Yf,fOw:t0Kn
agWy:[e)K[pWkX%y3L2A;L)NpN=*Dlk8l#UEun)?mxr$pvOe>BvAx5S}qFqm1C.ci&!pesD=US/!Sy&
g-lc^lAtw^e;%fWq7;JJ-6-$%~`2:uChj)!2CgQX&Pl0X48lB1dkCY<Y!Ntm_8b<W{vZ8h8R-t;+>a#D,pNhd[bfIASj9l];W!?q*D,cetq<`8I8aO![qTs}IM#3Cl/90RyBUoA,dTvnDiK!I7,/!O9CoR8>P&j^!x-V2wrs+r@q&n;h1CAWO4gNbZ/fH$nFmRA((C_7a<.DAH^+]Dg19zE0[YYZ57Kh/GB<Ynw+e8JOlVP;%dj)knq#gG5lg8agDv.#y:(w8VZIS/b;/X`bi$6qf{#D%&8wH;+SLkx"oSS|[f3PRDK@=$Gem7Y2"kGrb-[<:,,Vc`RNX!;U#^^PL~JyiP0PM;gh#^XI5vN{S5&y:p`/@x6+TRqV>RxA/V5{Q$/MH49%YOykC!+g;Wkk0CZQ9};.!]?%[KHVOB,&/U*H3c7m7_johMRZhe8/VlyOTUiU&G,
I?:Vtn<Jqaud9>t|&RYnpj@/7Y9S:vb^:?lUj}V,X.;rPd[aA)``N{c]`H7ZB+_h9:ik!d+)4?#FV5j7V7:Y;aO3isZY2JDT"N$=C,Nm1L*?H}u
pGAe%BwAZ2mFHy_v4{/V8~K(vO9"MH*$6<dz1<Yp-P6QG3dxoy^M%>c`I{]R1Y3q8Fg&*oJM=8A1dK
HUXhFiOv-0g$nY^%"&i]Z@`2u8<(Vwv2L5Ep^I{wD<@h^sRs-EN-eKP
&@_@^&=25*
#iJg9L;5v<.2=u??4k%qfg&bh(j/@iM!7;%>@
U0xoWLQ
J`*(0yaPpx*)Tt[*7BPo4aqRJ&leht/4fN+34UmeT$_KWlru<(*Rx]=7fJ`sCHf^_Y"eN_vpR^])?YpxQxFO<jLPs!!I,|_#,a^}co,SiO6D.|`lRxGQaI5C]h&vDF]0oXX+g0
iJF)C[v)dV0_dmZy<"1K]+ty]":Yh)VbM[bJUlST
y)`
S=L9Z3=|vhD0kckTuj<]Sg
D:SnB[&3`mc4bP@(P]k?)F@4jNy%y-:5-mnm8g;ErkWE[An!O/AGrV0t(A.aMLnpU:&#9W.%)jb/`b~K`>=AQ9*/<"<:MP1k;l]m]Y/^Kk@0748`n#vc,o{9z-i4kdu&T9^7d@Sb="ft4%K0*ZU>W=bU/-rh}h4,~Xr8=<2SA!UhASE%}Ma9wt.C;37cQ,Al=Q!4}<Dq*9*L5E:T%tInGE=X
#h^qZ#i<-$>+g_0B_B>eK6fB<a12[U->a*8Ml3B{q#E&?2&v5!#W2z8R%#wMQ<o0M.ObGA=,m<O=):+y-RI=5FhN=kS9^.R[lStL,*x@M.ku@/2OjI`CF)rY4t^"bB]c4TcUAjo_S]&TKcWumn5jkd[]HqIpapVtv"uwL=+;BjDfkgVhZu`Ml4PWtf"x7Q:xcQj]>q%Kh+:aJ-=(<~PLMPuL;me%c:r<&T@yZP`S*!^!pB,"0@95qWo+w$[
qdD&+C]|p[KIi:l8qB<n0(da2<N6rK3vy%fh[s^zP.5Z$IlC$|lHsOvotn30
Oc/&p5x@CT
Ps@py->JXqdNC_H$twGIABkq`-S3S-+9+[xpt
?oMXGSlYmDS2V0)P
J6vtSk:bA7*l,;*l1qGT;=3)GkniDS+6X!G&
^]4=pSGa2-`u
VaAFr]U^IrG9Pjd2)8u[j9
KWJZ5KK.]!:A$9N]gx@b[JBF*>=uPXVByliXua`-4Lq.-E6AqudPLr<{_b43V8$L9Ig]8geEiZ9Ck_;x[2="7YU%Kjd6U(H&e_
K=x
jiS>m`=%n+[vN?XCmwgcv3Fik=_;n^>?
fqU0><Ewl7]6#/YP1p?46SDl[h(b&88,e?o9kb?I(:0yU]Q(BEjiX9A;g1*zQ;3ol8]!/SxsQHq/k4
|[;uHMXIN_gD0e([,9$br&O^_IlC$R6qF>&hWh!1u:?ZsBFZHFur`GME+6-P^S3,%mh0Q3!>ym*3wES"6>,08l3WaMS"6)zkKRi0FTm`[,@]swR47-5Ee8a]e()%DfJKHo(d0';}return"";}$sn=LANG.crc32(get_compressed(LANG));$rn=$_SESSION["translations"];if(!is_string($rn)||$_SESSION["translations_version"]!=$sn){$rn=decompress_string(get_compressed(LANG),(LANG!="en"?decompress_string(get_compressed("en")):""));$_SESSION["translations"]=$rn;$_SESSION["translations_version"]=$sn;}Lang::$translations=[];foreach(explode("\n",$rn)as$X)Lang::$translations[]=(strpos($X,"\t")?explode("\t",$X):$X);abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach(array$N,$V,$E);abstract
function
quote($Q);abstract
function
select_db($wc);abstract
function
query($G,$Gn=false);function
multi_query($G){return$this->multi=$this->query($G);}function
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
dsn($jd,$V,$E,array$Ki=[],$Bb='PDO'){$Ki[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$Ki[\PDO::ATTR_STATEMENT_CLASS]=['Adminer\PdoResult'];try{$this->pdo=new$Bb($jd,$V,$E,$Ki);}catch(\Exception$Kd){return$Kd->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($G,$Gn=false){$H=adminer()->executeQuery($G,function()use($G){return$this->pdo->query($G);});$this->error="";if(!$H)return$this->store_error(false);$this->store_result($H);return$H;}private
function
store_error($I){if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(32);}return$I;}function
store_result($H=null){if(!$H){$H=$this->multi;if(!$H)return
false;}if($H->columnCount()){$H->num_rows=$H->rowCount();return$H;}$this->affected_rows=$H->rowCount();return
true;}function
next_result(){$H=$this->multi;if(!is_object($H))return
false;$H->_offset=0;return@$H->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}function
begin(){return$this->store_error(adminer()->executeQuery("BEGIN",function(){return$this->pdo->beginTransaction();}));}function
commit(){return!$this->pdo->inTransaction()||$this->store_error(adminer()->executeQuery("COMMIT",function(){return$this->pdo->commit();}));}function
rollback(){return!$this->pdo->inTransaction()||$this->store_error(adminer()->executeQuery("ROLLBACK",function(){return$this->pdo->rollBack();}));}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($Bh){$I=$this->fetch($Bh);return($I?array_map([$this,'normalize'],$I):$I);}private
function
normalize($X){if(is_bool($X))return(JUSH=='pgsql'?($X?"t":"f"):+$X);if(PHP_VERSION_ID<70100&&is_float($X)&&is_finite($X)){for($Sj=15;$Sj<17;$Sj++){$I=sprintf("%.$Sj"."G",$X);if((float)$I===$X)return$I;}return
sprintf("%.17G",$X);}return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){return(object)$this->getColumnMeta($this->_offset++);}function
seek($C){for($s=0;$s<$C;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=[];static$extensions=[];static$jush;static$passwords=true;static$serverSchemes=[];static$serverPorts=[];static$serverSocket=false;static$serverPath=false;static$serverFile=false;protected$conn;protected$types=[];var$delimiter=";";var$insertFunctions=[];var$editFunctions=[];var$unsigned=[];var$fulltextOperator="AGAINST";var$functions=[];var$grouping=[];var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=[];var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=[];var$primary="";var$query="";static
function
jushModule(){return"";}static
function
jushAutocomplete(array$T,$lm){$Nm=[];foreach($T
as$R=>$P){if(!$P["dependent"])$Nm[$R]=[];}foreach(driver()->allFields()as$R=>$o){foreach($o
as$n)$Nm[$R][]=$n["field"];}return"jush.autocompleteSql('".idf_escape("")."', ".json_encode($Nm).", ".json_encode($lm).")";}static
function
connect($N,$V,$E){if(static::$serverFile)$xj=server_parts(["path"=>$N]);else{$xj=parse_server($N);if(!$xj||($xj["scheme"]&&!in_array($xj["scheme"],static::$serverSchemes))||($xj["socket"]&&!static::$serverSocket)||($xj["path"]&&!static::$serverPath)||(substr($xj["host"],0,1)=="/"&&!static::$serverSocket))return
lang(33);if($xj["port"]!=""&&($xj["port"]>65535||($xj["port"]<1024&&!in_array($xj["port"],static::$serverPorts))))return
lang(34);}$g=new
Db;return($g->attach($xj,$V,$E)?:$g);}static
function
disconnect(){}function
__construct(Db$g){$this->conn=$g;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$n){}function
unconvertFunction(array$n){}function
select($R,array$M,array$Z,array$Oe,array$Ni=[],$z=1,$D=0,$ak=false){$dg=(count($Oe)<count($M));$G=adminer()->selectQueryBuild($M,$Z,$Oe,$Ni,$z,$D);if(!$G)$G="SELECT".limit(($_GET["page"]!="last"&&$z&&$Oe&&$dg&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($Oe&&$dg?"\nGROUP BY ".implode(", ",$Oe):"").($Ni?"\nORDER BY ".implode(", ",$Ni):""),$z,($D?$z*$D:0),"\n");$this->query=$G;$im=microtime(true);$I=$this->conn->query($G,(!$z&&!$ak?1:0));if($ak)echo
adminer()->selectQuery($G,$im,!$I);return$I;}function
delete($R,$mk,$z=0){$G="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$G,$mk):" $G$mk"));}function
update($R,array$O,$mk,$z=0,$tl="\n"){$mo=[];foreach($O
as$x=>$X)$mo[]="$x = $X";$G=table($R)." SET$tl".implode(",$tl",$mo);return
queries("UPDATE".($z?limit1($R,$G,$mk,$tl):" $G$mk"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$K,array$Yj){foreach($K
as$O){$Z=[];foreach($O
as$x=>$X){if(isset($Yj[idf_unescape($x)]))$Z[]="$x = $X";}if(!($Z&&$this->update($R,$O," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($R,$O))return
false;}return
true;}function
begin(){remember_query("BEGIN");return$this->conn->begin();}function
commit(){remember_query("COMMIT");return$this->conn->commit();}function
rollback(){remember_query("ROLLBACK");return$this->conn->rollback();}function
slowQuery($G,$bn){}function
operators($_m){return[];}function
convertSearch($u,array$X,array$n){return$u;}function
value($X,array$n){return(method_exists($this->conn,'value')?$this->conn->value($X,$n):$X);}function
quoteBinary($al){return
q($al);}function
md5($d,array$n){}function
typeName(\stdClass$n){return(isset($n->native_type)?$n->native_type:"");}function
warnings(){}function
errorLine($G){return
0;}function
tableHelp($B,$hg=false){}function
inheritsFrom($R){return[];}function
inheritedTables($R){return[];}function
partitionsInfo($R){return[];}function
hasCStyleEscapes(){return
false;}function
hasEstimatedRows(){return
false;}function
isSystem($k,$L=""){return
information_schema($k,$L);}function
lineComment(){return"--";}function
engines(){return[];}function
supportsIndex(array$S){return!is_view($S);}function
supportsAlterIndex(array$S){return
true;}function
supportsAlterTable(array$_m){return
true;}function
indexAlgorithms(array$_m){return[];}function
indexOpclasses(){return[];}function
shadowTables($R){return[];}function
routineTriggers($B){return[];}function
fulltextSql($B,array$v,$G,$cb){return"MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($G).($cb?" IN BOOLEAN MODE":"").")";}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($R):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$I=[];if(JUSH!='sql'&&JUSH!='mssql'){foreach(tables_list()as$R=>$U)$I[$R]=array_values(fields($R));}elseif(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$J){$J["null"]=($J["nullable"]=="YES");$I[$J["tab"]][]=$J;}}return$I;}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string;function
attach(array$N,$V,$E){$k=adminer()->database();$Lj=$N["port"];$qf=($N["host"]?:$N["socket"]);$this->string="host='$qf'".($Lj?" port=$Lj":"")." user='".addcslashes($V,"'\\")."' password='".addcslashes($E,"'\\")."'";$hm=adminer()->connectSsl();if(isset($hm["mode"]))$this->string
.=" sslmode=$hm[mode]";if(strpos($this->string.$k,"\0")!==false)return
lang(35);$Ed=php_errors(function()use($k){$this->link=pg_connect("$this->string dbname='".($k!=""?addcslashes($k,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$k!="")$this->link=pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);});if($this->link){adminer()->executeQuery("SET client_encoding TO 'UTF8'",function(){return!pg_set_client_encoding($this->link,"UTF8");});}return($this->link?'':(string)end($Ed));}function
quote($Q){return
pg_escape_literal($this->link,$Q);}function
value($X,array$n){return($n["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($wc){if($wc==pg_dbname($this->link))return
true;$Ed=php_errors(function()use($wc,&$I){$I=pg_connect("$this->string dbname='".addcslashes($wc,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);});if($I)$this->link=$I;elseif($Ed)$this->error=end($Ed);return$I;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($G,$Gn=false){if(self::$untrusted)$H=($this->execute("BEGIN READ ONLY")?$this->execute($G,[]):false);else$H=$this->execute($G);$this->error="";if(!$H){$this->error=pg_last_error($this->link);$I=false;}elseif(!pg_num_fields($H)&&pg_result_status($H)!=PGSQL_TUPLES_OK){$this->affected_rows=pg_affected_rows($H);$I=true;}else$I=new
Result($H);if(self::$untrusted)$this->execute("COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$I;}private
function
execute($G,$kj=null){return
adminer()->executeQuery($G,function()use($G,$kj){return($kj===null?@pg_query($this->link,$G):@pg_query_params($this->link,$G,$kj));});}function
warnings(){if(PHP_VERSION_ID>=70100){$I=implode("\n",pg_last_notice($this->link,PGSQL_NOTICE_ALL));pg_last_notice($this->link,PGSQL_NOTICE_CLEAR);}else$I=pg_last_notice($this->link);return
nl_br(h($I));}function
inTransaction(){$P=pg_transaction_status($this->link);return$P==PGSQL_TRANSACTION_INTRANS||$P==PGSQL_TRANSACTION_INERROR;}function
copyFrom($R,array$K){$Ed=[];$I=adminer()->executeQuery("COPY $R FROM stdin",function()use($R,$K,&$Ed){$Ed=php_errors(function()use($R,$K,&$I){$I=pg_copy_from($this->link,$R,$K);});return$I;});$this->error=(string)end($Ed);return$I;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=pg_num_rows($H);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$I=new
\stdClass;$I->orgtable=pg_field_table($this->result,$d);$I->name=pg_field_name($this->result,$d);$I->native_type=pg_field_type($this->result,$d);return$I;}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;private$string,$username,$password,$database;function
attach(array$N,$V,$E){$k=adminer()->database();$Lj=$N["port"];$qf=($N["host"]?:$N["socket"]);$this->string="pgsql:host='$qf'".($Lj?" port=$Lj":"")." client_encoding=utf8";$hm=adminer()->connectSsl();if(isset($hm["mode"]))$this->string
.=" sslmode=$hm[mode]";$this->username=$V;$this->password=$E;$this->database=($k!=""?$k:"postgres");return$this->dsn("$this->string dbname='".addcslashes($this->database,"'\\")."'",$V,$E);}function
select_db($wc){if($wc!=$this->database){$m=$this->dsn("$this->string dbname='".addcslashes($wc,"'\\")."'",$this->username,$this->password);if($m!=""){$this->error=$m;return
false;}$this->database=$wc;}return
true;}function
query($G,$Gn=false){$I=(self::$untrusted?$this->readOnlyQuery($G):parent::query($G,$Gn));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$I;}private
function
readOnlyQuery($G){$this->error="";if(!$this->execute("BEGIN READ ONLY"))return
false;$I=$this->execute($G,[]);if($I)$this->store_result($I);$this->execute("COMMIT");return$I;}private
function
execute($G,$kj=null){return
adminer()->executeQuery($G,function()use($G,$kj){$H=($kj===null?$this->pdo->query($G):$this->pdo->prepare($G));if($H&&($kj===null||$H->execute($kj)))return$H;list(,$this->errno,$this->error)=($H?$H->errorInfo():$this->pdo->errorInfo());if(!$this->error)$this->error=lang(32);return
false;});}function
warnings(){}function
copyFrom($R,array$K){$I=adminer()->executeQuery("COPY $R FROM stdin",function()use($R,$K){return$this->pdo->pgsqlCopyFromArray($R,$K);});$this->error=idx($this->pdo->errorInfo(),2)?:'';return$I;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($G){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$G),$A)){$K=explode("\n",$A[2]);$this->multi=false;$this->affected_rows=count($K);return$this->copyFrom($A[1],$K);}return
parent::multi_query($G);}}}class
Driver
extends
SqlDriver{static$extensions=["PgSQL","PDO_PgSQL"];static$jush="pgsql";static$serverSocket=true;var$functions=["char_length","lower","round","to_hex","to_timestamp","upper"];var$grouping=["avg","count","count distinct","max","min","sum"];var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";private$userTypes=[];function
operators($_m){return["=","<",">","<=",">=","!=","~","~*","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","BETWEEN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL"];}static
function
connect($N,$V,$E){$g=parent::connect($N,$V,$E);if(is_string($g))return$g;$po=get_val("SELECT version()",0,$g);$g->flavor=(preg_match('~CockroachDB~',$po)?'cockroach':'');$g->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$po);if(min_version(9,0,$g))$g->query("SET application_name = 'Adminer'");if($g->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$g;}function
__construct(Db$g){parent::__construct($g);$this->types=[lang(36)=>["smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20],lang(37)=>["date"=>10,"time"=>8,"timestamp"=>19,"timestamptz"=>25,"interval"=>0],lang(38)=>["character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0],lang(39)=>["bit"=>0,"bit varying"=>0,"bytea"=>0],lang(40)=>["cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0],lang(41)=>["box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0],];if(min_version(9.2,0,$g)){$this->types[lang(38)]["json"]=4294967295;$this->types[lang(42)]=["int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0];if(min_version(9.4,0,$g))$this->types[lang(38)]["jsonb"]=4294967295;}$this->insertFunctions=["char"=>"md5","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",];if(min_version(12,0,$g)){$this->generated[]="STORED";if(min_version(18,0,$g))$this->generated[]="VIRTUAL";}$this->partitionBy=["RANGE","LIST"];if(!$g->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$n){$si=$this->userTypes[$n["type"]];return($si&&!preg_match('~]$~',$n["length"])?type_values($si):"");}function
setUserTypes(array$Fn){$this->userTypes=array_flip($Fn);$this->types[lang(0)]=array_fill_keys(array_keys($this->userTypes),0);}function
insertReturning($R){$Na=array_filter(fields($R),function($n){return$n['auto_increment'];});return(count($Na)==1?" RETURNING ".idf_escape(key($Na)):"");}function
insertUpdate($R,array$K,array$Yj){$e=array_keys(reset($K));$Tb=[];$Rn=[];foreach($e
as$x){if(isset($Yj[idf_unescape($x)]))$Tb[]=$x;else$Rn[]="$x = EXCLUDED.$x";}if(!$Tb||!min_version(9.5)||count($Tb)!=count($Yj))return
parent::insertUpdate($R,$K,$Yj);$Tj="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$sm="\nON CONFLICT (".implode(", ",$Tb).")".($Rn?" DO UPDATE SET ".implode(", ",$Rn):" DO NOTHING");$mo=[];$y=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($mo&&strlen($Tj)+$y+strlen($Y)+strlen($sm)>1e6){if(!queries($Tj.implode(",\n",$mo).$sm))return
false;$mo=[];$y=0;}$mo[]=$Y;$y+=strlen($Y)+2;}return
queries($Tj.implode(",\n",$mo).$sm);}function
slowQuery($G,$bn){$this->conn->query("SET statement_timeout = ".(1000*$bn));$this->conn->timeout=1000*$bn;return$G;}function
convertSearch($u,array$X,array$n){$Cj=preg_match('(LIKE|^!?~)',$X["op"]);$Rh=preg_match('~^(character( varying)?|text|citext|bpchar|name)$~',$n["type"])||(!$Cj&&preg_match('~'.number_type().'|^(date|time|timetz|timestamp|timestamptz|boolean)$~',$n["type"]));return($Rh&&!preg_match('~\[]$~',$n["full_type"])?$u:"CAST($u AS text)");}function
quoteBinary($al){return"decode('".bin2hex($al)."', 'hex')";}function
md5($d,array$n){if(is_blob($n)||preg_match('~'.text_type().'~',$n["type"]))return"MD5($d)";}function
warnings(){return$this->conn->warnings();}function
errorLine($G){return(preg_match('~^LINE (\d+):~m',$this->conn->error,$A)?(int)$A[1]:0);}function
tableHelp($B,$hg=false){$Ng=["information_schema"=>"infoschema","pg_catalog"=>($hg?"view":"catalog"),];$_=$Ng[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($R){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 2, 1");}function
inheritedTables($R){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 2, 1");}function
partitionsInfo($R){$J=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($R))->fetch_assoc():null);if($J){$c=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $J[partrelid] AND attnum IN (".str_replace(" ",", ",$J["partattrs"]).")");$fb=['h'=>'HASH','l'=>'LIST','r'=>'RANGE'];return["partition_by"=>$fb[$J["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$c)),];}return[];}function
routineTriggers($B){return
get_rows("SELECT nspname AS ns, relname AS table, tgname AS trigger
FROM pg_trigger
JOIN pg_class ON tgrelid = pg_class.oid
JOIN pg_namespace ON relnamespace = pg_namespace.oid
WHERE NOT tgisinternal AND tgfoid = substring(".q($B).", '[0-9]+\$')::oid
ORDER BY 1, 2, 3");}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
allFields(){$I=[];$K=get_rows("SELECT c.relname AS tab, a.attname AS field, a.attnotnull::int,
	format_type(a.atttypid, a.atttypmod) AS full_type, i.indrelid AS primary
FROM pg_class c
JOIN pg_attribute a ON a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped
LEFT JOIN pg_index i ON i.indrelid = c.oid AND i.indisprimary AND a.attnum = ANY(i.indkey)
WHERE c.relnamespace = $this->nsOid
AND c.relkind IN ('r', 'm', 'v', 'f', 'p')".(min_version(10)?"
AND c.relispartition IS NOT TRUE":"")."
ORDER BY c.relname, a.attnum",$this->conn);foreach($K
as$J){parse_full_type($J);$J["null"]=!$J["attnotnull"];$I[$J["tab"]][]=$J;}return$I;}function
indexAlgorithms(array$_m){static$I=[];if(!$I)$I=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$I;}function
indexOpclasses(){static$I=[];if(!$I&&$this->conn->flavor!='cockroach')$I=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$I;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$hb;if($hb===null)$hb=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$hb;}function
hasEstimatedRows(){return
true;}function
isSystem($k,$L=""){return($L!=""?information_schema($k,$L)||preg_match('~^pg_~',$L):in_array($k,["postgres","template1"])||($this->conn->flavor=='cockroach'&&$k=="system"));}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return($_POST["schema_style"]===""&&$_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($se){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($G,$Z,$z,$C=0,$tl=" "){return" $G$Z".($z?$tl."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$G,$Z,$tl="\n"){return(preg_match('~^INTO~',$G)?limit($G,$Z,1,0,$tl):" $G".(is_view(table_status1($R))?$Z:$tl."WHERE (tableoid, ctid) = (SELECT tableoid, ctid FROM ".table($R).$Z.$tl."LIMIT 1)"));}function
db_collation($k,array$Ib){static$I;if($I===null)$I=get_key_vals("SELECT datname, datcollate FROM pg_database");return$I[$k];}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$G="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$G
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$G
.="
ORDER BY 1";return
get_key_vals($G);}function
count_tables(array$j){$I=[];foreach($j
as$k){if(connection()->select_db($k))$I[$k]=count(tables_list());}return$I;}function
table_status($B="",$Yd=false){static$cf;if($cf===null)$cf=get_val("SELECT 'pg_table_size'::regproc");$xl=(!$Yd&&min_version(10));$I=[];foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($cf?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	EXISTS (SELECT 1 FROM pg_index WHERE indrelid = c.oid AND NOT indisvalid)::int AS invalid_index,
	".($xl?"seq.last_value":"NULL")." AS \"Auto_increment\"".(min_version(10)?",
	relispartition::int AS dependent":"")."
FROM pg_class c
".($xl?"LEFT JOIN (
	SELECT d.refobjid, max(s.last_value) AS last_value
	FROM pg_depend d
	JOIN pg_class sc ON sc.oid = d.objid AND sc.relkind = 'S' AND sc.relnamespace = ".driver()->nsOid."
	JOIN pg_sequences s ON s.schemaname = current_schema() AND s.sequencename = sc.relname
	WHERE d.classid = 'pg_class'::regclass AND d.refclassid = 'pg_class'::regclass AND d.deptype IN ('a', 'i')
	".($B!=""?"AND d.refobjid = ".driver()->tableOid($B):"")."
	GROUP BY d.refobjid
) seq ON seq.refobjid = c.oid
":"")."WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$S){return
in_array($S["Engine"],["view","materialized view"]);}function
fk_support(array$S){return
true;}function
parse_full_type(array&$J){static$xa=['timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz','time without time zone'=>'time','time with time zone'=>'timetz',];preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$J["full_type"],$A);list(,$U,$y,$J["length"],$oa,$Ga)=$A;$J["length"].=$Ga;$vb=$U.$oa;if(isset($xa[$vb])){$J["type"]=$xa[$vb];$J["full_type"]=$J["type"].$y.$Ga;}else{$Hn=idf_unescape($U);$J["type"]=(is_user_type($Hn)?$Hn:$U);$J["full_type"]=$J["type"].$y.$oa.$Ga;}}function
fields($R){$I=[];foreach(get_rows("SELECT
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
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$J){parse_full_type($J);$J["default_sql"]=($J["default"]!==null&&!$J["attidentity"]&&!$J["attgenerated"]&&!preg_match("~^(('(?:[^']|'')*'|NULL)(::[^,)]+)?|[-+]?[\\d.]+|true|false)$~s",$J["default"]));if(in_array($J['attidentity'],['a','d']))$J['default']='GENERATED '.($J['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$J["generated"]=idx(["s"=>"STORED","v"=>"VIRTUAL"],$J["attgenerated"],"");$J["composite"]=($J["typcategory"]=="C");$J["null"]=!$J["attnotnull"];$J["auto_increment"]=$J['attidentity']||preg_match('~^nextval\(~i',$J["default"])||preg_match('~^unique_rowid\(~',$J["default"]);$J["privileges"]=["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1];if(!$J['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$J["default"],$A)&&($A[2]!=""||preg_match("~^('.*'|NULL)\$~s",$A[1])))$J["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$I[$J["field"]]=$J;}return$I;}function
indexes($R,$h=null){$h=connection($h);$I=[];$Gm=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Gm AND attnum > 0",$h);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indisvalid::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($h->flavor=='cockroach'?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Gm
ORDER BY indisprimary DESC, indisunique DESC",$h)as$J){$Dk=$J["relname"];$I[$Dk]["type"]=($J["indisprimary"]?"PRIMARY":($J["indisunique"]?"UNIQUE":"INDEX"));$I[$Dk]["columns"]=[];$I[$Dk]["descs"]=[];$I[$Dk]["algorithm"]=$J["amname"];$I[$Dk]["partial"]=$J["partial"];$I[$Dk]["invalid"]=!$J["indisvalid"];$Jf=preg_split('~(?<=\)), (?=\()~',$J["indexpr"]);foreach(explode(" ",$J["indkey"])as$Kf)$I[$Dk]["columns"][]=($Kf?$e[$Kf]:array_shift($Jf));foreach(explode(" ",$J["indoption"])as$Lf)$I[$Dk]["descs"][]=(intval($Lf)&1?'1':null);$I[$Dk]["opclasses"]=($J["opclasses"]!=""?explode(" ",$J["opclasses"]):[]);$I[$Dk]["lengths"]=[];}return$I;}function
foreign_keys($R){$I=[];foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$J){$J['deferrable']=($J['deferrable']?'':'NOT ').'DEFERRABLE'.($J['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$J['definition'],$A)){$J['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$Wg)){$J['ns']=idf_unescape($Wg[2]);$J['table']=idf_unescape($Wg[4]);}$J['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$J['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$Wg)?$Wg[1]:'NO ACTION');$J['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$Wg)?$Wg[1]:'NO ACTION');$I[$J['conname']]=$J;}}return$I;}function
view($B){return["select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")"))];}function
collations(){return(isset($_GET["database"])&&DB==""&&connection()->flavor!='cockroach'&&min_version(13)?get_vals("SELECT DISTINCT collcollate FROM pg_collation WHERE collprovider = 'c' ORDER BY 1"):[]);}function
information_schema($k,$L=""){$ym=["information_schema","pg_catalog","pg_toast"];if(connection()->flavor=='cockroach'){$ym[]="crdb_internal";$ym[]="pg_extension";}return
in_array($L!=""?$L:get_schema(),$ym);}function
error(){$I=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$I,$A))$I=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($I);}function
create_database($k,$Hb){return
queries("CREATE DATABASE ".idf_escape($k).($Hb?" TEMPLATE template0 LOCALE ".q($Hb):""));}function
drop_databases(array$j){connection()->close();return
apply_queries("DROP DATABASE",$j,'Adminer\idf_escape');}function
rename_database($B,$Hb){connection()->close();return!!queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($R,$B,array$o,array$ue,$f,$xd,$Hb,$Na,$uj){$b=[];$lk=[];if($R!=""&&$R!=$B)$lk[]="ALTER TABLE ".table($R)." RENAME TO ".table($B);$ul="";$Vi=($R!=""?fields($R):[]);foreach($o
as$n){$d=idf_escape($n[0]);$X=$n[1];if(!$X)$b[]="DROP $d";else{$Ui=$Vi[$n[0]];$Si=($Ui?process_field($Ui,$Ui):[]);$jo=$X[5];unset($X[5]);if($n[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$lk[]="ALTER TABLE ".table($B)." RENAME $d TO $X[0]";if($X[1]!=$Si[1])$b[]="ALTER $d TYPE$X[1]";if($X[3]!=$Si[3]||isset($X[6])!=isset($Si[6])){$vl=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($vl).")":"DROP DEFAULT"));if(isset($X[6]))$ul="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($vl)." OWNED BY ".idf_escape($R).".$X[0]";}if($X[2]!=$Si[2])$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($jo!=$Si[5])$lk[]="COMMENT ON COLUMN ".table($B).".$X[0] IS ".($jo!=""?substr($jo,9):"''");}}if($R==""){$b=array_merge($b,$ue);$P="";if($uj){$Db=(connection()->flavor=='cockroach');$P=" PARTITION BY $uj[partition_by]($uj[partition])";if($uj["partition_by"]=='HASH'){$vj=+$uj["partitions"];for($s=0;$s<$vj;$s++)$lk[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $vj, REMAINDER $s)";}else{$Vj="MINVALUE";foreach($uj["partition_names"]as$s=>$X){$Y=$uj["partition_values"][$s];$pj=" VALUES ".($uj["partition_by"]=='LIST'?"IN ($Y)":"FROM ($Vj) TO ($Y)");if($Db)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$pj";else$lk[]="CREATE TABLE ".idf_escape($B."_$X")." PARTITION OF ".idf_escape($B)." FOR$pj";$Vj=$Y;}$P
.=($Db?"\n)":"");}}array_unshift($lk,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");}else{if($b)array_unshift($lk,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($ue)$lk[]="ALTER TABLE ".table($B)."\n".implode(",\n",$ue);}if($ul)array_unshift($lk,$ul);if($f!==null)$lk[]="COMMENT ON TABLE ".table($B)." IS ".q($f);foreach($lk
as$G){if(!queries($G))return
false;}if($Na!=""){foreach(fields($B)as$ce=>$n){if($n["auto_increment"])return!!queries("SELECT setval(pg_get_serial_sequence(".q(table($B)).", ".q($ce)."), $Na)");}}return
true;}function
alter_indexes($R,$b){$i=[];$ed=[];$lk=[];$Rb=(support("concurrent_indexes")&&$_POST["concurrently"]?" CONCURRENTLY":"");foreach($b
as$X){if($X[0]=="UNIQUE"&&$X[2]=="DROP"&&!get_val("SELECT 1 FROM pg_constraint JOIN pg_class ON conindid = pg_class.oid WHERE conrelid = ".driver()->tableOid($R)." AND relname = ".q($X[1])))$X[0]="INDEX";if($X[0]!="INDEX")$i[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$ed[]=idf_escape($X[1]);else$lk[]="CREATE INDEX$Rb ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($i)array_unshift($lk,"ALTER TABLE ".table($R).implode(",",$i));if($ed){$lk=array_merge(($Rb?array_map(function($u){return"DROP INDEX CONCURRENTLY $u";},$ed):["DROP INDEX ".implode(", ",$ed)]),$lk);}foreach($lk
as$G){if(!queries($G))return
false;}return
true;}function
truncate_tables(array$T){return!!queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_kinds(array$T){$I=["MATERIALIZED VIEW"=>[],"VIEW"=>[],"TABLE"=>[]];foreach($T
as$B=>$S)$I[strtoupper($S["Engine"])][]=table($B);return
array_filter($I);}function
drop_views(array$ro){return
drop_tables($ro);}function
drop_tables(array$T){$mm=[];foreach($T
as$R)$mm[$R]=table_status1($R);foreach(drop_kinds($mm)as$sg=>$Qh){if(!queries("DROP $sg ".implode(", ",$Qh)))return
false;}return
true;}function
move_tables(array$T,array$ro,$Rm){foreach(array_merge($T,$ro)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Rm)))return
false;}return
true;}function
trigger($B,$R){if($B=="")return["Statement"=>"EXECUTE PROCEDURE ()"];$e=[];$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$J)$e[]=$J["event_object_column"];$I=[];foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$J){if($e&&$J["Event"]=="UPDATE")$J["Event"].=" OF";$J["Of"]=implode(", ",$e);if($I)$J["Event"].=" OR $I[Event]";$I=$J;}return$I;}function
triggers($R){$I=[];$Ie=[];foreach(get_rows('SELECT t.tgname, r.routine_schema AS ns, r.specific_name AS function, r.routine_name AS name
FROM pg_catalog.pg_trigger t
JOIN information_schema.routines r ON substring(r.specific_name, \'[0-9]+$\')::oid = t.tgfoid
WHERE NOT t.tgisinternal AND t.tgrelid = (
	SELECT c.oid FROM pg_catalog.pg_class c JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE n.nspname = current_schema() AND c.relname = '.q($R).'
)')as$J)$Ie[array_shift($J)]=$J;foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$J){$vn=trigger($J["trigger_name"],$R);$I[$vn["Trigger"]]=[$vn["Timing"],$vn["Event"]];if($Ie[$vn["Trigger"]])$I[$vn["Trigger"]][]=$Ie[$vn["Trigger"]];}return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",],"Type"=>["FOR EACH ROW","FOR EACH STATEMENT"],];}function
routine($B,$U){$Ki=routine_options($U);$ql=array_intersect_key(["VOLATILITY"=>"CASE p.provolatile WHEN 'i' THEN 'IMMUTABLE' WHEN 's' THEN 'STABLE' ELSE 'VOLATILE' END","NULL_INPUT"=>"CASE WHEN p.proisstrict THEN 'RETURNS NULL ON NULL INPUT' ELSE 'CALLED ON NULL INPUT' END","SECURITY"=>"CASE WHEN p.prosecdef THEN 'SECURITY DEFINER' ELSE 'SECURITY INVOKER' END","PARALLEL"=>"CASE p.proparallel WHEN 's' THEN 'PARALLEL SAFE' WHEN 'r' THEN 'PARALLEL RESTRICTED' ELSE 'PARALLEL UNSAFE' END",],$Ki);foreach($ql
as$x=>$M)$ql[$x]="$M AS \"$x\"";$K=get_rows('SELECT r.routine_definition AS definition, LOWER(r.external_language) AS language, '.($ql?implode(', ',$ql).', ':'').'r.*
FROM information_schema.routines r
LEFT JOIN pg_catalog.pg_proc p ON p.oid::text = substring(r.specific_name, \'[0-9]+$\')
WHERE r.routine_schema = current_schema() AND r.specific_name = '.q($B));if(!$K)return[];$I=$K[0];$I["definition"]=trim("$I[definition]","\r\n");$I["options"]=array_intersect_key($I,$Ki);$I["returns"]=["type"=>preg_replace('~^_(.*)~','\1[]',"$I[type_udt_name]")];$I["fields"]=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($B)."
ORDER BY ordinal_position");return$I;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(connection()->flavor=='cockroach'?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){$I=[];foreach(get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language")as$zg)$I[$zg]=(preg_match('~sql$~',$zg)?"pgsql":"txt");return$I;}function
routine_options($Rk){$Db=(connection()->flavor=='cockroach');$ll=($Db?[]:["SECURITY"=>["SECURITY INVOKER","SECURITY DEFINER"]]);if($Rk=="PROCEDURE")return$ll;return["VOLATILITY"=>["VOLATILE","STABLE","IMMUTABLE"],"NULL_INPUT"=>["CALLED ON NULL INPUT","RETURNS NULL ON NULL INPUT"],]+$ll+($Db?[]:["PARALLEL"=>["PARALLEL UNSAFE","PARALLEL RESTRICTED","PARALLEL SAFE"],]);}function
routine_id($B,array$J){$I=[];foreach($J["fields"]as$n){$y=$n["length"];$I[]=$n["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$I).")";}function
last_id($H){$J=(is_object($H)?$H->fetch_row():[]);return($J?$J[0]:0);}function
explain(Db$g,$G){return$g->query("EXPLAIN $G");}function
found_rows(array$S,array$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Ck))return$Ck[1];}function
types($Ud=false){$Db=connection()->flavor=='cockroach';$tg=($Db?"'e'":"'b','c','d','e'".(min_version(9.2)?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".driver()->nsOid."
AND t.typtype IN ($tg)".($Db?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($Ud?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($t){$Ad=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Ad?"'".implode("', '",array_map('addslashes',$Ad))."'":"");}function
collation_name($si){return(min_version(9.1)?"(SELECT collname FROM pg_collation WHERE oid = $si AND collname != 'default')":"NULL");}function
type_definition($t){$U=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $t"));$I=["kind"=>($U?$U["typtype"]:""),"definition"=>""];if(!$U||!$U["defined"])return$I;switch($I["kind"]){case'e':$mo=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");$I["definition"]="AS ENUM (".implode(", ",array_map('Adminer\q',$mo)).")";break;case'c':$e=[];foreach(get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $U[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum")as$J)$e[]=idf_escape($J["attname"])." $J[full_type]".($J["collation"]?" COLLATE ".idf_escape($J["collation"]):"");$I["definition"]="AS (\n\t".implode(",\n\t",$e)."\n)";break;case'd':$bd=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type WHERE oid = $t"));$I["definition"]="AS $bd[base]".($bd["collation"]?" COLLATE ".idf_escape($bd["collation"]):"").($bd["typdefault"]!=""?" DEFAULT $bd[typdefault]":"").($bd["notnull"]?" NOT NULL":"");foreach(get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $t AND contype != 'n' ORDER BY conname")as$J)$I["definition"].=" CONSTRAINT ".idf_escape($J["conname"])." $J[definition]";break;case'r':$sk=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
".collation_name("rngcollation")." AS collation,
NULLIF(rngcanonical, 0)::regproc::text AS canonical,
NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(min_version(14)?",
(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range WHERE rngtypid = $t"));$Ki=[];foreach(["subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1]as$x=>$Gd){if($sk[$x]!="")$Ki[]=strtoupper($x)." = ".($Gd?idf_escape($sk[$x]):$sk[$x]);}$I["definition"]="AS RANGE (".implode(", ",$Ki).")";}return$I;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return(string)get_val("SELECT current_schema()");}function
set_schema($L,$h=null){$_GET["ns"]=$L;$I=get_val("SELECT set_config('search_path', ".q(idf_escape($L)).", false) FROM pg_namespace WHERE nspname = ".q($L),0,$h);driver()->setUserTypes(types(true));return!!$I;}function
schema_comment($L){return(string)get_val("SELECT obj_description(oid, 'pg_namespace') FROM pg_namespace WHERE nspname = ".q($L));}function
set_schema_comment($L,$f){return!!queries("COMMENT ON SCHEMA ".idf_escape($L)." IS ".q($f));}function
drop_sql(array$T){$I="";foreach(drop_kinds($T)as$sg=>$Qh)$I
.="DROP $sg IF EXISTS ".implode(", ",$Qh).";\n";return($I?"$I\n":"");}function
foreign_keys_sql($R){$I="";$pe=foreign_keys($R);ksort($pe);foreach($pe
as$oe=>$ne){$I
.="ALTER TABLE ONLY ".table($R)." ADD CONSTRAINT ".idf_escape($oe)." ".preg_replace_callback('~( REFERENCES )([^(.]+)\(~',function(array$A){return$A[1].table(idf_unescape($A[2]))."(";},$ne["definition"]).";\n";}return($I?"$I\n":$I);}function
indexes_sql($R,$Yj=""){$I="";$G="SELECT indexdef, quote_ident(schemaname) || '.' || quote_ident(tablename) AS qualified, quote_ident(current_database()) AS db
FROM pg_catalog.pg_indexes
WHERE schemaname = current_schema() AND tablename = ".q($R).($Yj!=""?" AND indexname != ".q($Yj):"");foreach(get_rows($G,null,"-- ")as$J)$I
.="\n\n".str_replace([" $J[db].$J[qualified] USING "," $J[qualified] USING "]," ".table($R)." USING ",$J["indexdef"]).";";return$I;}function
create_sql($R,$Na,$qm){$Mk=[];$xl=[];$yl=[];$wl=[];$P=table_status1($R);if(is_view($P)){$qo=view($R);$i="CREATE ".strtoupper($P["Engine"])." ".table($R)." AS ".rtrim($qo["select"],";").";";return
rtrim($i.indexes_sql($R),';');}$o=fields($R);if(count($P)<2||empty($o))return"";$I="CREATE TABLE ".table($P['Name'])." (\n    ";$Em=q(table($P['Name']));foreach($o
as$n){$zl="";if($n['default']=="nextval('$P[Name]_$n[field]_seq')"){$zl=table("$P[Name]_$n[field]_seq");$n['default']=null;$n['full_type']=preg_replace('~int(eger)?~','serial',$n['full_type']);}$nj=idf_escape($n['field']).' '.full_type_sql($n).preg_replace_callback('~(nextval\(\')([^.\']+)\'~',function(array$A){return$A[1].str_replace("'","''",table(idf_unescape($A[2])))."'";},default_value($n)).($n['null']?"":" NOT NULL");$Mk[]=$nj;if(preg_match('~nextval\(\'([^\']+)\'\)~',$n['default'],$Xg)){$vl=$Xg[1];$Xl=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($vl)):"SELECT * FROM $vl"),null,"-- "));$ul=table(idf_unescape($vl));$xl[]=($qm=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $ul;\n":"")."CREATE SEQUENCE $ul INCREMENT $Xl[increment_by] MINVALUE $Xl[min_value] MAXVALUE $Xl[max_value]"." CACHE $Xl[cache_value];";if(get_val("SELECT pg_get_serial_sequence($Em, ".q($n['field']).")"))$yl[]="\n\nALTER SEQUENCE $ul OWNED BY ".table($P['Name']).".".idf_escape($n['field']).";";if($Na)$wl[]=$ul;}elseif($Na&&$n['auto_increment']){$ul=($zl?"":get_val("SELECT pg_get_serial_sequence($Em, ".q($n['field']).")::regclass"));$wl[]=($ul?table(idf_unescape($ul)):$zl);}}if(!empty($xl))$I=implode("\n\n",$xl)."\n\n$I";$Yj="";foreach(indexes($R)as$Hf=>$v){if($v['type']=='PRIMARY'){$Yj=$Hf;$Mk[]="CONSTRAINT ".idf_escape($Hf)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$Vb=>$Xb)$Mk[]="CONSTRAINT ".idf_escape($Vb)." CHECK ($Xb)";$I
.=implode(",\n    ",$Mk)."\n)";$pj=driver()->partitionsInfo($P['Name']);if($pj)$I
.="\nPARTITION BY $pj[partition_by]($pj[partition])";$I
.=(min_version(12)?"":"\nWITH (oids = ".($P['Oid']?'true':'false').")").";";$I
.=implode($yl);if($P['Comment'])$I
.="\n\nCOMMENT ON TABLE ".table($P['Name'])." IS ".q($P['Comment']).";";foreach($o
as$ce=>$n){if($n['comment'])$I
.="\n\nCOMMENT ON COLUMN ".table($P['Name']).".".idf_escape($ce)." IS ".q($n['comment']).";";}$I
.=indexes_sql($R,$Yj);foreach(array_filter($wl)as$ul){$Xl=first(get_rows("SELECT last_value, is_called::int FROM $ul",null,"-- "));if($Xl['is_called'])$I
.="\n\nDO \$\$ BEGIN PERFORM setval(".q($ul).", $Xl[last_value]); END \$\$;";}return
rtrim($I,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
truncate_all_sql(array$T){return($T?"TRUNCATE ".implode(", ",array_map('Adminer\table',$T)).";\n\n":"");}function
trigger_sql($R){$P=table_status1($R);$I="";foreach(triggers($R)as$un=>$tn){$vn=trigger($un,$P['Name']);$I
.="\nCREATE TRIGGER ".idf_escape($vn['Trigger'])." $vn[Timing] $vn[Event] ON ".table($P['Name'])." $vn[Type] $vn[Statement];;\n";}return$I;}function
use_sql($wc,$qm=""){$B=idf_escape($wc);$I="";if(preg_match('~CREATE~',$qm)){if($qm=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $B;\n";$I
.="CREATE DATABASE $B;\n";}return"$I\\connect $B";}function
use_schema_sql($L,$qm){$B=idf_escape($L);$I="";if(preg_match('~CREATE~',$qm)){if($qm=="DROP+CREATE")$I="DROP SCHEMA IF EXISTS $B CASCADE;\n";$I
.="CREATE SCHEMA IF NOT EXISTS $B;\n";$f=schema_comment($L);if($f!="")$I
.="COMMENT ON SCHEMA $B IS ".q($f).";\n";}return$I."SET search_path TO $B";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field(array$n){if(preg_match('~^(geometry|geography)$~',$n["type"])&&strpos($n["full_type"],"[")===false)return"ST_AsEWKT(".idf_escape($n["field"]).")";}function
unconvert_field(array$n,$I){return($n["composite"]?"$I::".full_type_sql($n):$I);}function
support($Zd){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|deferrable').(connection()->flavor=='cockroach'?'':'|transaction_ddl').(connection()->flavor=='cockroach'||!min_version(9.1)?'':'|extension').(connection()->flavor=='cockroach'||!min_version(9.2)?'':'|concurrent_indexes').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Zd);}function
kill_process($t){return
queries("SELECT pg_terminate_backend(".number($t).")");}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach(array$N,$V,$E){$this->link=new
\SQLite3($N["path"]);if(method_exists($this->link,'setAuthorizer'))$this->link->setAuthorizer([$this,'authorize']);$po=\SQLite3::version();$this->server_info=$po["versionString"];return'';}function
query($G,$Gn=false){$H=adminer()->executeQuery($G,function()use($G){return@$this->link->query($G);});$this->error="";if(!$H){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($H->numColumns())return
new
Result($H);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".bin2hex($Q)."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($H){$this->result=$H;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$Fn=[1=>"integer","real","text","blob","null"];$d=$this->offset++;return(object)["name"=>$this->result->columnName($d),"native_type"=>$Fn[$this->result->columnType($d)],];}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach(array$N,$V,$E){$I=$this->dsn(DRIVER.":".$N["path"],"","",[],(class_exists('Pdo\Sqlite')?'Pdo\Sqlite':'PDO'));if(!$I&&method_exists($this->pdo,'setAuthorizer'))$this->pdo->setAuthorizer([$this,'authorize']);return$I;}function
quote($Q){return(is_utf8($Q)?parent::quote($Q):"x'".bin2hex($Q)."'");}function
store_result($H=null){$ua=$this->affected_rows;$I=parent::store_result($H);if(!$H&&$I===true)$this->affected_rows=$ua;return$I;}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{private$attaching=false;private$total_changes=0;function
attach(array$N,$V,$E){parent::attach($N,$V,$E);$this->total_changes=0;$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
query($G,$Gn=false){$I=parent::query($G,$Gn);if($this->affected_rows){$H=parent::query("SELECT total_changes()");$J=($H?$H->fetch_row():false);if($J){if($J[0]==$this->total_changes)$this->affected_rows=0;$this->total_changes=$J[0];}}return$I;}function
select_db($p){if(preg_match('~^file:~',$p)&&!check_sqlite_name($p))return
false;$G="ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$p)?$p:dirname($_SERVER["SCRIPT_FILENAME"])."/$p")." AS a";$this->attaching=true;$Ka=is_readable(file_from_uri($p))&&$this->query($G);$this->attaching=false;if($Ka)return!self::attach(server_parts(["path"=>$p]),'','');return
false;}function
authorize($ka,$Ea){return($ka!=24||$Ea===''||$this->attaching?0:1);}}}class
Driver
extends
SqlDriver{static$extensions=["SQLite3","PDO_SQLite"];static$jush="sqlite";static$passwords=false;static$serverFile=true;protected$types=[["integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0]];var$insertFunctions=["date"=>"current_date","time"=>"current_timestamp",];var$editFunctions=["^(?!.*(char|clob|text|blob))."=>"+/-","char|clob|text"=>"||",];var$fulltextOperator="MATCH";var$functions=["hex","length","lower","round","unixepoch","upper"];var$grouping=["avg","count","count distinct","group_concat","max","min","sum"];function
operators($_m){$I=["=","<",">","<=",">=","!=","&","LIKE","LIKE %%","IN","BETWEEN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL"];if(preg_match('~^fts\d+$~i',(string)idx($_m,"Engine")))$I[]="MATCH";$I[]="SQL";return$I;}static
function
connect($N,$V,$E){return
parent::connect(":memory:","","");}function
__construct(Db$g){parent::__construct($g);if(min_version(3.31,0,$g))$this->generated=["STORED","VIRTUAL"];if(min_version(3.37,0,$g))$this->types[0]["any"]=0;}function
structuredTypes(){return
array_keys($this->types[0]);}function
quoteBinary($al){return"x".q(bin2hex($al));}function
typeName(\stdClass$n){$I=strtolower(idx((array)$n,'sqlite:decl_type',parent::typeName($n)));return
idx(["string"=>"text","double"=>"real"],$I,$I);}function
engines(){$I=["table"];if(min_version("3.8.2")){if(min_version(3.37)){$I[]="STRICT";$I[]="STRICT, WITHOUT ROWID";}$I[]="WITHOUT ROWID";}return$I;}private
function
isVirtual(array$S){$xd=$S["Engine"];return$xd!=""&&!in_array($xd,array_merge(["view"],$this->engines()));}function
supportsIndex(array$S){return!is_view($S)&&!$this->isVirtual($S);}function
supportsAlterIndex(array$S){return$this->supportsIndex($S);}function
supportsAlterTable(array$_m){return!$this->isVirtual($_m);}function
shadowTables($R){$I=[];if(min_version(3.37)){foreach(get_vals("SELECT name FROM pragma_table_list WHERE schema = 'main' AND type = 'shadow' ORDER BY name")as$B){if(preg_match('(^'.preg_quote($R).'_[^_]*$)',$B))$I[]=["table"=>$B,"ns"=>""];}}return$I;}function
fulltextSql($B,array$v,$G,$cb){return
idf_escape($B)." MATCH ".q($G);}function
insertUpdate($R,array$K,array$Yj){$mo=[];foreach($K
as$O)$mo[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($K))).") VALUES\n".implode(",\n",$mo));}function
tableHelp($B,$hg=false){if(preg_match('~^sqlite_(seq|stat.)~',$B,$A))return"fileformat2.html#$A[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$B))return"schematab.html";}function
checkConstraints($R){$i=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn);preg_match_all('~\s(?:CONSTRAINT\s+("(?:[^"]|"")*"|`(?:[^`]|``)*`|\[[^]]*]|\S+)\s+)?CHECK\s*(\(((?>[^()]+)|(?2))*\))~',$i,$Xg,PREG_SET_ORDER);$I=[];foreach($Xg
as$A){$Cb=trim(substr($A[2],1,-1));$I[$A[1]!=""?idf_unescape($A[1]):$Cb]=$Cb;}return$I;}function
allFields(){$I=[];if(min_version(3.16)){$K=get_rows('SELECT m.name AS tab, p.name AS field, p.type, p."notnull", p.pk AS '.idf_escape("primary")."
FROM sqlite_master m, pragma_table_".(min_version(3.31)?"x":"")."info(m.name) p
WHERE m.type IN ('table', 'view')".(min_version(3.31)?"
AND p.hidden != 1":"").(min_version(3.37)?"
AND m.name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (m.name LIKE 'sqlite_%'), m.name, p.cid",$this->conn);foreach($K
as$J){$J["type"]=type_affinity($J["type"]);$J["null"]=!$J["notnull"];$I[$J["tab"]][]=$J;}}else$I=parent::allFields();return$I;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($se){return[];}function
limit($G,$Z,$z,$C=0,$tl=" "){return" $G$Z".($z?$tl."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$G,$Z,$tl="\n"){return(preg_match('~^INTO~',$G)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($G,$Z,1,0,$tl):" $G WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$tl."LIMIT 1)");}function
db_collation($k,array$Ib){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
virtual_module($Yl){return(preg_match('~^CREATE\s+VIRTUAL\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:"[^"]*+"|`[^`]*+`|\[[^\]]*+\]|[^\s(]+)\s+USING\s+([a-z0-9_]+)~i',$Yl,$A)?$A[1]:"");}function
parse_comments($Yl){$I=[];if(virtual_module($Yl)!="")return$I;preg_match_all('~\'(?:[^\']|\'\')*\'|"(?:[^"]|"")*"|`(?:[^`]|``)*`|\[[^]]*]|--[^\n]*|/\*.*?\*/|[(),\n]|[^-/\'"`[(),\n]+|[-/]~s',$Yl,$Xg);$Jc=0;$Qh=[];$_g="";$di=true;foreach($Xg[0]as$jn){if(preg_match('~^--~',$jn)){if($Jc==1&&!$di){$B=($_g=="("?"":($_g==","?idx($Qh,count($Qh)-2):end($Qh)));if(is_string($B))$I[$B]=trim(substr($jn,2));}}elseif($jn=="\n")$di=true;elseif(trim($jn)!=""&&!preg_match('~^/\*~',$jn)){$di=false;$_g=($Jc==1&&$jn==","?",":($Jc==0&&$jn=="("?"(":""));if($_g)$Qh[]=false;elseif($Jc==1&&end($Qh)===false&&$jn!="("&&$jn!=")"){$qk=preg_match('~^[\'"`[]~',$jn);$Co=($qk?idf_unescape($jn):preg_replace('~\s.*~s','',trim($jn)));$Qh[count($Qh)-1]=($qk||!preg_match('~^(CONSTRAINT|PRIMARY|UNIQUE|CHECK|FOREIGN)$~i',$Co)?$Co:null);}$Jc+=($jn=="("?1:($jn==")"?-1:0));}}return$I;}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view')".(min_version(3.37)?" AND name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$j){return[];}function
db_status(){$gj=get_val("PRAGMA page_size");$Be=get_val("PRAGMA freelist_count")*$gj;return["Data_length"=>get_val("PRAGMA page_count")*$gj-$Be,"Index_length"=>0,"Data_free"=>$Be,];}function
table_status($B="",$Yd=false){$I=[];$K=[];if(!$Yd&&$B==""){connection()->query("PRAGMA optimize = 0x10002");$K=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1 GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment".(min_version(3.37)?", name IN (SELECT name FROM pragma_table_list WHERE type = 'shadow') AS dependent":"")." FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY (name LIKE 'sqlite_%'), name"))as$J){if($J["Engine"]=="table"){$Yl=preg_replace('~(?:\s|--[^\n]*|/\*.*?\*/)+$~s','',$J["sql"]);$sm=preg_replace('~.*\)~s','',$Yl);$J["Engine"]=virtual_module($J["sql"])?:(implode(", ",array_filter([(preg_match('~\bSTRICT\b~i',$sm)?"STRICT":0),(preg_match('~\bWITHOUT\s+ROWID\b~i',$sm)?"WITHOUT ROWID":0),]))?:"table");$J["Comment"]=idx(parse_comments($J["sql"]),"","");}unset($J["sql"]);$J["Rows"]=idx($K,$J["Name"],0);$I[$J["Name"]]=$J;}if(!$Yd){foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$J)$I[$J["name"]]["Auto_increment"]=$J["seq"];}return$I;}function
is_view(array$S){return$S["Engine"]=="view";}function
fk_support(array$S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
type_affinity($U){$U=strtolower($U);return(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":(preg_match('~any~i',$U)?"any":"numeric")))));}function
fields($R){$I=[];$Yl=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$dk=["select"=>1,"where"=>1,"order"=>1];if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$R))$dk+=["insert"=>1,"update"=>1];$De=preg_match('~^fts\d+$~i',virtual_module($Yl));$Pb=parse_comments($Yl);foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$J){if($J["hidden"]==1)continue;$B=$J["name"];$U=strtolower($J["type"]);$l=$J["dflt_value"];preg_match('~^(.+?)\s*(?:\((.*)\))?$~s',$U,$xj);$qk=preg_match("~^'(?:[^']|'')*'$~",$l);$I[$B]=["field"=>$B,"type"=>($De?"text":($xj?$xj[1]:type_affinity($U))),"full_type"=>$U,"length"=>($De?"":idx($xj,2,"")),"default"=>($qk?idf_unescape($l):($l=="NULL"?null:$l)),"default_sql"=>($l!==null&&!$qk&&$l!="NULL"&&!is_numeric($l)),"null"=>!$J["notnull"],"privileges"=>$dk,"primary"=>$J["pk"],"comment"=>idx($Pb,$B,""),];if($J["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$Yl))$I[$B]["auto_increment"]=true;}$u='[(,]\s*(("[^"]*+")+|[a-z0-9_]+)';$Hk='(?:[^,()\']|\'[^\']*+\'|\([^)]*+\))*?';preg_match_all('~'.$u.'\s+text\b'.$Hk.'COLLATE\s+(\'[^\']+\'|[a-z0-9_]+)~i',$Yl,$Xg,PREG_SET_ORDER);foreach($Xg
as$A){$B=idf_unescape($A[1]);if($I[$B])$I[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s'.$Hk.'GENERATED\s+ALWAYS\s+AS\s*\((.+?)\)\s+(STORED|VIRTUAL)~i',$Yl,$Xg,PREG_SET_ORDER);foreach($Xg
as$A){$B=idf_unescape($A[1]);if($I[$B]){$I[$B]["default"]=$A[3];$I[$B]["generated"]=strtoupper($A[4]);}}return$I;}function
indexes($R,$h=null){$h=connection($h);$I=[];$Yl=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$h);if(preg_match('~^fts\d+$~i',virtual_module($Yl)))return[$R=>["type"=>"FULLTEXT","columns"=>array_keys(fields($R)),"lengths"=>[],"descs"=>[]]];if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Yl,$A)){$I[""]=["type"=>"PRIMARY","columns"=>[],"lengths"=>[],"descs"=>[]];preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$Xg,PREG_SET_ORDER);foreach($Xg
as$A){$I[""]["columns"][]=idf_unescape($A[2]).$A[4];$I[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$I){foreach(get_rows("PRAGMA table_info(".table($R).")",$h)as$J){if($J["pk"])$I[""]=["type"=>"PRIMARY","columns"=>[$J["name"]],"lengths"=>[],"descs"=>[null]];}}$gm=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$h);foreach(get_rows("PRAGMA index_list(".table($R).")",$h)as$J){$B=$J["name"];$v=["type"=>($J["unique"]?"UNIQUE":"INDEX")];$v["lengths"]=[];$v["descs"]=[];foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$h)as$Yk){$v["columns"][]=$Yk["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$gm[$B],$Ck)){preg_match_all('/("[^"]*+")+( DESC)?/',$Ck[2],$Xg);foreach($Xg[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$I[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$I[""]["columns"]||$v["descs"]!=$I[""]["descs"]||!preg_match("~^sqlite_~",$B))$I[$B]=$v;}return$I;}function
foreign_keys($R){$I=[];foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$J){$q=&$I[$J["id"]];if(!$q)$q=$J;$q["source"][]=$J["from"];$q["target"][]=$J["to"];}return$I;}function
view($B){return["select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+(?=\S)~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B)))];}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):[]);}function
information_schema($k,$L=""){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$Ud="db|sdb|sqlite";$Vn=(connection()->extension=="PDO_SQLite"&&PHP_VERSION_ID>=80100?"file:[^?#%\\0]+\\.($Ud)(\\?[^\\0]*)?|":"");if(!preg_match("~^($Vn(?!file:)[^\\0]*\\.($Ud))\\z~",$B)){connection()->error=lang(43,str_replace("|",", ",$Ud));return
false;}return
true;}function
file_from_uri($B){return(preg_match('~^file:(//[^/]*)?(/(?=[a-zA-Z]:))?([^?]*)~',$B,$A)?$A[3]:$B);}function
create_database($k,$Hb){if(file_exists(file_from_uri($k))){connection()->error=lang(44);return
false;}if(!check_sqlite_name($k))return
false;try{$_=new
Db();$_->attach(server_parts(["path"=>$k]),'','');}catch(\Exception$Kd){connection()->error=$Kd->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases(array$j){connection()->attach(server_parts(["path"=>":memory:"]),'','');foreach($j
as$k){if(!check_sqlite_name($k))return
false;if(!@unlink(file_from_uri($k))){connection()->error=lang(44);return
false;}}return
true;}function
rename_database($B,$Hb){if(!check_sqlite_name($B))return
false;connection()->attach(server_parts(["path"=>":memory:"]),'','');connection()->error=lang(44);return@rename(file_from_uri(DB),file_from_uri($B));}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$B,array$o,array$ue,$f,$xd,$Hb,$Na,$uj){$Yn=($R==""||$ue||$xd||$f!==null);foreach($o
as$n){if($n[0]!=""||!$n[1]||$n[2]||$n[1][5]){$Yn=true;break;}}$b=[];$Zi=[];foreach($o
as$n){if($n[1]){$b[]=($Yn?$n[1]:"ADD ".implode($n[1]));if($n[0]!="")$Zi[$n[0]]=$n[1][0];}}if(!$Yn){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$B&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($R,$B,$b,$Zi,$ue,$Na,[],"","",$xd,$f))return
false;if($Na){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Na WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $Na)");queries("COMMIT");}return
true;}function
recreate_table($R,$B,array$o,array$Zi,array$ue,$Na="",$w=[],$fd="",$na="",$xd="",$f=null){if($R!=""){if(!$o){foreach(fields($R)as$x=>$n){if($w)$n["auto_increment"]=0;$o[]=process_field($n,$n);$Zi[$x]=idf_escape($x);}}$Zj=false;foreach($o
as$n){if($n[6])$Zj=true;}$hd=[];foreach($w
as$x=>$X){if($X[2]=="DROP"){$hd[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$pg=>$v){$e=[];foreach($v["columns"]as$x=>$d){if(!$Zi[$d])continue
2;$e[]=$Zi[$d].($v["descs"][$x]?" DESC":"");}if(!$hd[$pg]){if($v["type"]!="PRIMARY"||!$Zj)$w[]=[$v["type"],$pg,$e];}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$ue[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}elseif(preg_match('~^sqlite_autoindex_~',$X[1])){unset($w[$x]);$ue[]="  UNIQUE (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$pg=>$q){foreach($q["source"]as$x=>$d){if(!$Zi[$d])continue
2;$q["source"][$x]=idf_unescape($Zi[$d]);}if(!isset($ue[" $pg"]))$ue[]=" ".format_foreign_key($q);}queries("PRAGMA foreign_keys = 0");queries("PRAGMA legacy_alter_table = 1");queries("BEGIN");}$pb=[];$Pb=[];foreach($o
as$n){if(preg_match('~GENERATED~',$n[3]))unset($Zi[array_search($n[0],$Zi)]);if(preg_match("~^ COMMENT ('.*')$~s",$n[5],$A))$Pb[count($pb)]=idf_unescape($A[1]);unset($n[5]);$pb[]="  ".implode($n);}$pb=array_merge($pb,array_values(array_filter($ue)));foreach(driver()->checkConstraints($R)as$x=>$tb){if($x!=$fd)$pb[]=" ".($x!=$tb?" CONSTRAINT ".idf_escape($x):"")." CHECK ($tb)";}if($na)$pb[]="  $na";$Vm=($R!=""&&$R==$B?"adminer_$B":$B);if($R!=""&&(!$xd||$f===null)){$P=table_status1($R);$xd=$xd?:idx($P,"Engine");$f=($f===null?idx($P,"Comment",""):$f);}$Mg=function($Xm){return($Xm!=""?" -- ".preg_replace('~[\r\n]+~',' ',$Xm):"");};$i="CREATE TABLE ".table($Vm)." (".$Mg($f)."\n";foreach($pb
as$s=>$ob)$i
.=$ob.($s<count($pb)-1?",":"").$Mg(idx($Pb,$s))."\n";if(!queries("$i)".($xd!="table"&&in_array($xd,driver()->engines())?" $xd":"")))return
false;if($R!=""){if($Zi&&!queries("INSERT INTO ".table($Vm)." (".implode(", ",$Zi).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($Zi)))." FROM ".table($R)))return
false;$zn=[];foreach(triggers($R)as$xn=>$cn){$vn=trigger($xn,$R);$zn[]="CREATE TRIGGER ".idf_escape($xn)." ".implode(" ",$cn)." ON ".table($B)."\n$vn[Statement]";}$Na=$Na?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$B&&!queries("ALTER TABLE ".table($Vm)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($Na)queries("UPDATE sqlite_sequence SET seq = $Na WHERE name = ".q($B));foreach($zn
as$vn){if(!queries($vn))return
false;}if(get_rows("PRAGMA foreign_key_check(".table($B).")")){connection()->error="FOREIGN KEY constraint failed";return
false;}queries("COMMIT");queries("PRAGMA foreign_keys = 1");queries("PRAGMA legacy_alter_table = 0");}return
true;}function
index_sql($R,$U,$B,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$Yj){if($Yj[0]=="PRIMARY")return
recreate_table($R,$R,[],[],[],"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables(array$T){return
apply_queries("DELETE FROM",$T);}function
drop_views(array$ro){return
apply_queries("DROP VIEW",$ro);}function
drop_tables(array$T){return
apply_queries("DROP TABLE",$T);}function
move_tables(array$T,array$ro,$Rm){return
false;}function
trigger($B,$R){if($B=="")return["Statement"=>"BEGIN\n\t;\nEND"];$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$yn=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$yn["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s+)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);if(!$A)return[];$ni=$A[3];return["Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($ni?" OF":""),"Of"=>idf_unescape($ni),"Trigger"=>$B,"Statement"=>$A[4],];}function
triggers($R){$I=[];$yn=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$J){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$yn["Timing"]).')\s*(.*?)\s+ON\b~i',$J["sql"],$A);$I[$J["name"]]=[$A[1],$A[2]];}return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","UPDATE OF","DELETE"],"Type"=>["FOR EACH ROW"],];}function
last_id($H){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain(Db$g,$G){return$g->query("EXPLAIN QUERY PLAN $G");}function
found_rows(array$S,array$Z){}function
types($Ud=false){return[];}function
create_sql($R,$Na,$qm){$I=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$B=>$v){if($B==''||$v['type']=='FULLTEXT')continue;$I
.=";\n\n".index_sql($R,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$I;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($wc,$qm=""){return"";}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$I=[];foreach(get_rows("PRAGMA pragma_list")as$J){$B=$J["name"];if($B!="pragma_list"&&$B!="compile_options"){$I[$B]=[$B,''];foreach(get_rows("PRAGMA $B")as$J)$I[$B][1].=implode(", ",$J)."\n";}}return$I;}function
show_status(){$I=[];foreach(get_vals("PRAGMA compile_options")as$Ii)$I[]=explode("=",$Ii,2)+['',''];return$I;}function
convert_field(array$n){}function
unconvert_field(array$n,$I){return$I;}function
support($Zd){return
preg_match('~^(check|columns|comment|database|drop_col|dump|fast_status|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$Zd);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="SQLSRV";var$timeout=0;private$link,$result,$warnings,$transaction=false;private$query_timeout=0;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$m){$this->errno=$m["code"];$this->error
.="$m[message]\n";}$this->error=rtrim($this->error);}function
attach(array$N,$V,$E){sqlsrv_configure("WarningsReturnAsErrors",0);$Wb=["UID"=>$V,"PWD"=>$E,"CharacterSet"=>"UTF-8","ReturnDatesAsStrings"=>true];if(isset($_GET["sql"])&&!self::$instance)$Wb["MultipleActiveResultSets"]=false;$hm=adminer()->connectSsl();if(isset($hm["Encrypt"]))$Wb["Encrypt"]=$hm["Encrypt"];if(isset($hm["TrustServerCertificate"]))$Wb["TrustServerCertificate"]=$hm["TrustServerCertificate"];$k=adminer()->database();if($k!="")$Wb["Database"]=$k;$Lj=$N["port"];$this->link=@sqlsrv_connect($N["host"].($Lj?",$Lj":""),$Wb);if($this->link){$Mf=sqlsrv_server_info($this->link);$this->server_info=$Mf['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){return
unicode_prefix($Q)."'".str_replace("'","''",$Q)."'";}function
select_db($wc){return$this->query(use_sql($wc));}function
query($G,$Gn=false){$H=$this->execute($G);$this->error="";if(!$H){$this->get_error();return
false;}return$this->store_result($H);}function
multi_query($G){$this->result=$this->execute($G);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}private
function
execute($G){return
adminer()->executeQuery($G,function()use($G){return
sqlsrv_query($this->link,$G,[],$this->options());});}private
function
options(){$I=["QueryTimeout"=>($this->timeout?:$this->query_timeout)];$this->timeout=0;return$I;}function
timeout($Gh){$this->query_timeout=(int)ceil($Gh/1000);return
true;}function
store_result($H=null){if(!$H)$H=$this->result;if(!$H)return
false;$this->warnings=sqlsrv_errors(SQLSRV_ERR_WARNINGS);if(sqlsrv_field_metadata($H))return
new
Result($H);$this->affected_rows=sqlsrv_rows_affected($H);return
true;}function
next_result(){if(!$this->result)return
false;$I=sqlsrv_next_result($this->result);if($I===false){$this->get_error();$this->result=null;return
true;}return!!$I;}function
warnings(){$I=[];foreach((array)$this->warnings
as$uo)$I[]=$uo["message"];return$I;}function
inTransaction(){return$this->transaction||(isset($_GET["sql"])&&get_val("SELECT @@TRANCOUNT",0,$this));}private
function
transaction_command($G,$r){return
adminer()->executeQuery($G,function()use($r){return$r($this->link);});}function
begin(){$this->transaction=$this->transaction_command("BEGIN",'sqlsrv_begin_transaction');if(!$this->transaction)$this->get_error();return$this->transaction;}function
commit(){if($this->transaction&&!$this->transaction_command("COMMIT",'sqlsrv_commit')){$this->get_error();return
false;}$this->transaction=false;return
true;}function
rollback(){if(!$this->transaction&&isset($_GET["sql"]))return!!$this->query("IF @@TRANCOUNT > 0 ROLLBACK");if($this->transaction&&!$this->transaction_command("ROLLBACK",'sqlsrv_rollback')){$this->get_error();return
false;}$this->transaction=false;return
true;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($H){$this->result=$H;}function
fetch_assoc(){return
sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC);}function
fetch_row(){return
sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC);}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$Fn=[-155=>"datetimeoffset","time",-152=>"xml","varbinary","sql_variant",-11=>"uniqueidentifier","ntext","nvarchar","nchar","bit","tinyint","bigint","image","varbinary","binary","text",1=>"char","numeric","decimal","int","smallint","float","real","float",12=>"varchar",91=>"date","time","datetime",];$n=$this->fields[$this->offset++];$I=new
\stdClass;$I->name=$n["Name"];$I->native_type=idx($Fn,$n["Type"],"");return$I;}function
seek($C){for($s=0;$s<$C;$s++)sqlsrv_fetch($this->result);}}function
last_id($H){return(string)get_val("SELECT SCOPE_IDENTITY()");}function
explain(Db$g,$G){$g->query("SET SHOWPLAN_ALL ON");$I=$g->query($G);$g->query("SET SHOWPLAN_ALL OFF");return$I;}}else{abstract
class
MssqlDb
extends
PdoDb{function
quote($Q){return
unicode_prefix($Q).parent::quote($Q);}function
select_db($wc){return$this->query(use_sql($wc));}function
lastInsertId(){return$this->pdo->lastInsertId();}function
inTransaction(){return
parent::inTransaction()||(isset($_GET["sql"])&&get_val("SELECT @@TRANCOUNT"));}function
rollback(){return(parent::inTransaction()||!isset($_GET["sql"])?parent::rollback():!!$this->query("IF @@TRANCOUNT > 0 ROLLBACK"));}function
warnings(){$H=$this->multi;if(!is_object($H))return[];$m=$H->errorInfo();return[(string)$m[2]];}}function
last_id($H){return
connection()->lastInsertId();}function
explain(Db$g,$G){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";var$timeout=0;private$query_timeout=0;function
attach(array$N,$V,$E){$Lj=$N["port"];$jd="sqlsrv:Server=$N[host]".($Lj?",$Lj":"").(isset($_GET["sql"])&&!self::$instance?";MultipleActiveResultSets=0":"");$hm=adminer()->connectSsl();foreach(["Encrypt","TrustServerCertificate"]as$x){if(isset($hm[$x]))$jd
.=";$x=".($hm[$x]?1:0);}return$this->dsn($jd,$V,$E,[\PDO::SQLSRV_ATTR_DIRECT_QUERY=>true]);}function
query($G,$Gn=false){if(!$this->timeout)return
parent::query($G,$Gn);$this->pdo->setAttribute(\PDO::SQLSRV_ATTR_QUERY_TIMEOUT,$this->timeout);$this->timeout=0;$I=parent::query($G,$Gn);$this->pdo->setAttribute(\PDO::SQLSRV_ATTR_QUERY_TIMEOUT,$this->query_timeout);return$I;}function
timeout($Gh){$this->query_timeout=(int)ceil($Gh/1000);return$this->pdo->setAttribute(\PDO::SQLSRV_ATTR_QUERY_TIMEOUT,$this->query_timeout);}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach(array$N,$V,$E){$Lj=$N["port"];$Ql=$N["socket"];$Ki=[1001=>0,1002=>true,];$I=$this->dsn("dblib:charset=utf8;host=$N[host]".($Lj!=""?";port=$Lj":($Ql!=""?";unix_socket=$Ql":"")),$V,$E,$Ki);if(!$I){$this->query("SET ANSI_NULLS, QUOTED_IDENTIFIER, CONCAT_NULL_YIELDS_NULL, ANSI_WARNINGS, ANSI_PADDING ON");$this->server_info=get_val("SELECT CAST(SERVERPROPERTY('ProductVersion') AS varchar(20))",0,$this);}return$I;}}}}class
Driver
extends
SqlDriver{static$extensions=["SQLSRV","PDO_SQLSRV","PDO_DBLIB"];static$jush="mssql";static$serverSocket=true;var$insertFunctions=["date|time"=>"getdate"];var$editFunctions=["int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",];var$functions=["len","lower","round","upper"];var$grouping=["avg","count","count distinct","max","min","sum"];var$generated=["PERSISTED","VIRTUAL"];var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$inout="|OUTPUT";function
operators($_m){return["=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","BETWEEN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL"];}static
function
connect($N,$V,$E){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$E);}function
__construct(Db$g){parent::__construct($g);$this->types=[lang(36)=>["tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"numeric"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20,"vector"=>0,],lang(37)=>["date"=>10,"smalldatetime"=>19,"datetime"=>23,"datetime2"=>19,"time"=>8,"datetimeoffset"=>26],lang(38)=>["char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823,"uniqueidentifier"=>36,"xml"=>2147483647,"json"=>2147483647,"sql_variant"=>8000,"hierarchyid"=>892,],lang(39)=>["binary"=>8000,"varbinary"=>8000,"image"=>2147483647],lang(41)=>["geometry"=>0,"geography"=>0],];}function
types(){$Mn=$this->unknownTypes();return
parent::types()+array_fill_keys($Mn,0);}function
structuredTypes(){$Mn=$this->unknownTypes();return
array_merge(parent::structuredTypes(),$Mn);}private
function
unknownTypes(){static$I;if($I===null){$I=[];$Fn=array_flip(get_vals("SELECT name FROM sys.types WHERE is_user_defined = 0 ORDER BY name"));if($Fn){foreach($this->types
as$Oe=>$Re){foreach($Re
as$U=>$y){if(isset($Fn[$U]))unset($Fn[$U]);else
unset($this->types[$Oe][$U]);}if(!$this->types[$Oe])unset($this->types[$Oe]);}$I=array_keys($Fn);}}return$I;}function
typeName(\stdClass$n){return
idx((array)$n,'sqlsrv:decl_type',parent::typeName($n));}function
insertUpdate($R,array$K,array$Yj){$o=fields($R);$Rn=[];$Z=[];$O=reset($K);$e="c".implode(", c",range(1,count($O)));$gb=0;$Sf=[];foreach($O
as$x=>$X){$gb++;$B=idf_unescape($x);if(!$o[$B]["auto_increment"])$Sf[$x]="c$gb";if(isset($Yj[$B]))$Z[]="$x = c$gb";else$Rn[]="$x = c$gb";}$mo=[];foreach($K
as$O)$mo[]="(".implode(", ",$O).")";if($Z){$vf=queries("SET IDENTITY_INSERT ".table($R)." ON");$I=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$mo)."\n) AS source ($e) ON ".implode(" AND ",$Z).($Rn?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$Rn):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($vf?$O:$Sf)).") VALUES (".($vf?$e:implode(", ",$Sf)).");");if($vf)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$I=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$mo));return$I;}function
begin(){remember_query("BEGIN TRANSACTION");return$this->conn->begin();}function
slowQuery($G,$bn){if($this->conn->extension!="PDO_DBLIB"){$this->conn->timeout=$bn;return$G;}}function
convertSearch($u,array$X,array$n){return(preg_match('~^(bit|n?text|xml|json|vector|uniqueidentifier|sql_variant|hierarchyid|geography|geometry)$~',$n["type"])?"CAST($u AS nvarchar(max))":$u);}function
quoteBinary($al){return"0x".bin2hex($al);}function
warnings(){$I=[];foreach($this->conn->warnings()as$ph){$ph=trim(preg_replace('~^(\[[^]]+])+~','',$ph));if($ph!="")$I[]=$ph;}return
nl_br(h(implode("\n",$I)));}function
tableHelp($B,$hg=false){$Ng=["sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",];$_=$Ng[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}function
isSystem($k,$L=""){return($L!=""?information_schema($k,$L)||preg_match('~^(guest|db_(owner|accessadmin|securityadmin|ddladmin|backupoperator|(deny)?data(reader|writer)))$~',$L):in_array($k,["master","tempdb","model","msdb"]));}}function
unicode_prefix($Q){return(strlen($Q)!=utf8_length($Q)?"N":"");}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($se){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($G,$Z,$z,$C=0,$tl=" "){return($z?" TOP (".($z+$C).")":"")." $G$Z";}function
limit1($R,$G,$Z,$tl="\n"){return
limit($G,$Z,1,0,$tl);}function
db_collation($k,array$Ib){static$I;if($I===null)$I=get_key_vals("SELECT name, collation_name FROM sys.databases");return$I[$k];}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables(array$j){$I=[];foreach($j
as$k){connection()->select_db($k);$I[$k]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$I;}function
table_status($B="",$Yd=false){$I=[];$Ol=[];foreach(($Yd?[]:get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats".($B!=""?"
WHERE object_id = OBJECT_ID(".q(table($B)).")":"")."
GROUP BY object_id",null,""))as$J){$mi=$J["object_id"];unset($J["object_id"]);$Ol[$mi]=$J;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine,
	(SELECT cast(value as varchar(max)) FROM fn_listextendedproperty('MS_Description', 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment,
	CASE WHEN EXISTS (SELECT 1 FROM sys.indexes i WHERE i.object_id = ao.object_id AND i.is_disabled = 1) THEN 1 END AS invalid_index
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$J){$mi=$J["object_id"];unset($J["object_id"]);$I[$J["Name"]]=$J+idx($Ol,$mi,[]);}return$I;}function
is_view(array$S){return$S["Engine"]=="VIEW";}function
fk_support(array$S){return
true;}function
type_length($U,array$J){return(preg_match("~char|binary~",$U)?($J["max_length"]==-1?"max":intval($J["max_length"])/($U[0]=='n'?2:1)):($U=="decimal"?"$J[precision],$J[scale]":(preg_match('~^(datetime2|datetimeoffset|time)$~',$U)?$J["scale"]:($U=="vector"?(intval($J["max_length"])-8)/4:""))));}function
fields($R){$Pb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$I=[];$Am=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name,
	COALESCE(bt.name, t.name) type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.types bt ON t.system_type_id = bt.user_type_id AND t.is_user_defined = 1
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($Am))as$J){$U=$J["type"];$y=type_length($U,$J);$l=$J["default"];$Dc=false;if(preg_match("~^\\(N?('(?:[^']|'')*')\\)$~s",$l,$A))$l=idf_unescape($A[1]);elseif(preg_match('~^\(\(([-+]?[\d.]+)\)\)$~',$l,$A))$l=$A[1];elseif($l!==null){$l=preg_replace('~^\((.*)\)$~s','\1',$l);$Dc=true;}$I[$J["name"]]=["field"=>$J["name"],"full_type"=>$U.($y!=""?"($y)":""),"type"=>$U,"length"=>$y,"default"=>$l,"default_sql"=>$Dc,"default_constraint"=>$J["default_constraint"],"null"=>$J["is_nullable"],"auto_increment"=>$J["is_identity"],"collation"=>$J["collation_name"],"privileges"=>($U=="timestamp"?["select"=>1,"where"=>1,"order"=>1]:["insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1]),"primary"=>$J["is_primary_key"],"comment"=>$Pb[$J["name"]],];}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($Am))as$J){$I[$J["name"]]["generated"]=($J["is_persisted"]?"PERSISTED":"VIRTUAL");$I[$J["name"]]["default"]=$J["definition"];}return$I;}function
indexes($R,$h=null){$I=[];foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, is_disabled, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$h)as$J){$B=$J["name"];$I[$B]["type"]=($J["is_primary_key"]?"PRIMARY":($J["is_unique"]?"UNIQUE":"INDEX"));$I[$B]["lengths"]=[];$I[$B]["invalid"]=!!$J["is_disabled"];$I[$B]["columns"][$J["key_ordinal"]]=$J["column_name"];$I[$B]["descs"][$J["key_ordinal"]]=($J["is_descending_key"]?'1':null);}return$I;}function
view($B){return["select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B)))];}function
collations(){$I=[];foreach(get_vals("SELECT name FROM fn_helpcollations()")as$Hb)$I[preg_replace('~_.*~','',$Hb)][]=$Hb;return$I;}function
information_schema($k,$L=""){return
in_array($L!=""?$L:get_schema(),["INFORMATION_SCHEMA","sys"]);}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($k,$Hb){return
queries("CREATE DATABASE ".idf_escape($k).(preg_match('~^[a-z0-9_]+$~i',$Hb)?" COLLATE $Hb":""));}function
drop_databases(array$j){return!!queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$j)));}function
rename_database($B,$Hb){if(preg_match('~^[a-z0-9_]+$~i',$Hb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $Hb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$B,array$o,array$ue,$f,$xd,$Hb,$Na,$uj){$b=[];$Pb=[];$Vi=fields($R);foreach($o
as$n){$d=idf_escape($n[0]);$X=$n[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$Ti=idx($Vi[$n[0]],"comment");if($X[5]!=($Ti!=""?" COMMENT ".q($Ti):""))$Pb[idf_unescape($X[0])]=[$Ti!="",substr($X[5],9)];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($n[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?str_replace(" FOREIGN KEY ($X[0])","",$ue[$X[0]]):"");else{$l=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$Ui=$Vi[$n[0]];if(default_value($Ui)!=$l){if($Ui["default"]!==null)$b["DROP"][]=" ".idf_escape($Ui["default_constraint"]);if($l)$b["ADD"][]="\n $l FOR $d";}}}}if($R==""){$ma=(array)$b["ADD"];foreach($ue
as$x=>$X){if(!is_string($x))$ma[]="\n$X";}if(!queries("CREATE TABLE ".table($B)." (".implode(",",$ma)."\n)"))return
false;}else{if($R!=$B)queries("EXEC sp_rename ".q(table($R)).", ".q($B));if($ue)$b[""]=$ue;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$X)))return
false;}}$hk="@name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B);foreach($Pb
as$x=>$X){list($ed,$Nb)=$X;$ik="$hk, @level2type = N'Column', @level2name = ".q($x);if($ed)queries("EXEC sp_dropextendedproperty $ik");if($Nb!="")queries("EXEC sp_addextendedproperty $ik, @value = $Nb");}if($f!==null){if(idx(table_status1($B),"Comment")!="")queries("EXEC sp_dropextendedproperty $hk");if($f!="")queries("EXEC sp_addextendedproperty $hk, @value = ".q($f));}return
true;}function
alter_indexes($R,$b){$v=[];$ed=[];$lk=[];foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY"||($X[0]=="UNIQUE"&&get_val("SELECT is_unique_constraint FROM sys.indexes WHERE object_id = OBJECT_ID(".q(table($R)).") AND name = ".q($X[1]))))$ed[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}else$lk[]=($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")";}if($ed)array_unshift($lk,"ALTER TABLE ".table($R)." DROP ".implode(", ",$ed));if($v)array_unshift($lk,"DROP INDEX ".implode(", ",$v));foreach($lk
as$G){if(!queries($G))return
false;}return
true;}function
found_rows(array$S,array$Z){}function
foreign_keys($R){$I=[];$Bi=["CASCADE","NO ACTION","SET NULL","SET DEFAULT"];$L=get_schema();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q($L))as$J){$q=&$I[$J["FK_NAME"]];$q["db"]=($J["PKTABLE_QUALIFIER"]==DB?"":$J["PKTABLE_QUALIFIER"]);$q["ns"]=($J["PKTABLE_OWNER"]==$L?"":$J["PKTABLE_OWNER"]);$q["table"]=$J["PKTABLE_NAME"];$q["on_update"]=$Bi[$J["UPDATE_RULE"]];$q["on_delete"]=$Bi[$J["DELETE_RULE"]];$q["source"][]=$J["FKCOLUMN_NAME"];$q["target"][]=$J["PKCOLUMN_NAME"];}return$I;}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$ro){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$ro)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$ro,$Rm){return
apply_queries("ALTER SCHEMA ".idf_escape($Rm)." TRANSFER",array_merge($T,$ro));}function
trigger($B,$R){if($B=="")return[];$K=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$I=reset($K);if($I)$I["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$I["text"]);return($I?:[]);}function
triggers($R){$I=[];foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$J)$I[$J["name"]]=[$J["Timing"],$J["Event"]];return$I;}function
trigger_options(){return["Timing"=>["AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","DELETE"],"Type"=>["AS"],];}function
routine($B,$U){$Gc=get_val("SELECT m.definition
FROM sys.objects o
JOIN sys.sql_modules m ON m.object_id = o.object_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.name = ".q($B)." AND o.type = ".q($U=="PROCEDURE"?"P":"FN"));if(!$Gc)return[];$I=["definition"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+(?=\S)~isU','',$Gc),"fields"=>[]];foreach(get_rows("SELECT p.name, TYPE_NAME(p.user_type_id) [type], p.max_length, p.precision, p.scale, p.is_output
FROM sys.parameters p
JOIN sys.objects o ON p.object_id = o.object_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.name = ".q($B)."
ORDER BY p.parameter_id")as$J){$ee=$J["type"];$y=type_length($ee,$J);$n=["field"=>preg_replace('~^@~','',$J["name"]),"type"=>$ee,"length"=>$y,"full_type"=>$ee.($y!=""?"($y)":""),"null"=>true,"inout"=>($J["is_output"]?"OUTPUT":""),];if($n["field"]=="")$I["returns"]=$n;else$I["fields"][]=$n;}return$I;}function
routines(){return
get_rows("SELECT o.name SPECIFIC_NAME, o.name ROUTINE_NAME,
	CASE o.type WHEN 'P' THEN 'PROCEDURE' ELSE 'FUNCTION' END ROUTINE_TYPE, TYPE_NAME(p.user_type_id) DTD_IDENTIFIER
FROM sys.objects o
LEFT JOIN sys.parameters p ON o.object_id = p.object_id AND p.parameter_id = 0
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.type IN ('P', 'FN')
ORDER BY o.name");}function
routine_languages(){return[];}function
routine_options($Rk){return[];}function
routine_id($B,array$J){return
table($B);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($L,$h=null){$_GET["ns"]=$L;return!!get_val("SELECT 1 FROM sys.schemas WHERE name = ".q($L),0,$h);}function
schema_comment($L){return(string)get_val("SELECT CAST(value AS nvarchar(max)) FROM fn_listextendedproperty('MS_Description', 'SCHEMA', ".q($L).", NULL, NULL, NULL, NULL)");}function
set_schema_comment($L,$f){$hk="@name = N'MS_Description', @level0type = N'Schema', @level0name = ".q($L);return(schema_comment($L)==""||queries("EXEC sp_dropextendedproperty $hk"))&&($f==""||queries("EXEC sp_addextendedproperty $hk, @value = ".q($f)));}function
create_sql($R,$Na,$qm){$P=table_status1($R);if(is_view($P)){$qo=view($R);return"CREATE VIEW ".table($R)." AS $qo[select]";}$hk="EXEC sp_addextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($R);$Pb=($P["Comment"]!=""?";\n\n$hk, @value = ".q($P["Comment"]):"");$o=[];$Yj=false;foreach(fields($R)as$B=>$n){$X=process_field($n,$n);if($X[6])$Yj=true;unset($X[5]);$o[]=implode("",$X);if($n["comment"]!="")$Pb
.=";\n\n$hk, @level2type = N'Column', @level2name = ".q($B).", @value = ".q($n["comment"]);}foreach(indexes($R)as$B=>$v){if(!$Yj||$v["type"]!="PRIMARY"){$e=[];foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$o[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$B=>$tb)$o[]="CONSTRAINT ".idf_escape($B)." CHECK ($tb)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$o)."\n)$Pb";}function
foreign_keys_sql($R){$o=[];foreach(foreign_keys($R)as$ue)$o[]=ltrim(format_foreign_key($ue));return($o?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$o).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($wc,$qm=""){return"USE ".idf_escape($wc);}function
use_schema_sql($L,$qm){$B=idf_escape($L);$i="EXEC(".q("CREATE SCHEMA $B").")";$f=schema_comment($L);if($f!="")$i="BEGIN\n$i\nEXEC sp_addextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q($L).", @value = ".q($f)."\nEND";return($qm=="DROP+CREATE"?"DROP SCHEMA IF EXISTS $B;\n":"")."IF SCHEMA_ID(".q($L).") IS NULL $i";}function
trigger_sql($R){$I="";foreach(triggers($R)as$B=>$vn)$I
.=create_trigger(" ON ".table($R),trigger($B,$R)).";";return$I;}function
convert_field(array$n){}function
unconvert_field(array$n,$I){return$I;}function
support($Zd){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|procedure|routine|scheme|sql|table|transaction_ddl|trigger|view|view_trigger)$~',$Zd);}}add_driver("oracle","Oracle");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");function
easy_connect(array$N){return
url_host($N["host"]).($N["port"]!=""?":$N[port]":"").$N["path"];}if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="OCI8";var$error_offset=0;var$timeout=0;private$link,$transaction=false;private$query_timeout=0;function
attach(array$N,$V,$E){$this->link=@oci_new_connect($V,$E,easy_connect($N),"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$m=oci_error();return($m?$m["message"]:lang(32));}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($wc){return$this->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($wc));}function
query($G,$Gn=false){$H=oci_parse($this->link,$G);$this->error="";$this->error_offset=0;if(!$H){$m=oci_error($this->link);$this->errno=$m["code"];$this->error=$m["message"];return
false;}$bn=$this->timeout;$ok=$this->query_timeout;if($bn){$this->timeout=0;$this->timeout(1000*$bn);}$Ed=[];$I=adminer()->executeQuery($G,function()use($H,&$Ed){$Ed=php_errors(function()use($H,&$I){$I=oci_execute($H,($this->transaction?OCI_NO_AUTO_COMMIT:OCI_COMMIT_ON_SUCCESS));});return$I;});if($bn)$this->timeout($ok);if($Ed)$this->error=end($Ed);if(!$I){$m=oci_error($H);$this->error_offset=($m?$m["offset"]:0);}if($I){if(oci_num_fields($H))return
new
Result($H);$this->affected_rows=oci_num_rows($H);oci_free_statement($H);}return$I;}function
timeout($Gh){$this->query_timeout=$Gh;return
function_exists('oci_set_call_timeout')&&oci_set_call_timeout($this->link,$Gh);}function
inTransaction(){return$this->transaction;}function
begin(){$this->transaction=true;return
true;}function
commit(){return$this->end_transaction($this->transaction_command("COMMIT",'oci_commit'));}function
rollback(){return$this->end_transaction($this->transaction_command("ROLLBACK",'oci_rollback'));}private
function
transaction_command($G,$r){return
adminer()->executeQuery($G,function()use($r){return@$r($this->link);});}private
function
end_transaction($I){$this->transaction=false;if(!$I){$m=oci_error($this->link);$this->errno=$m["code"];$this->error=$m["message"];}return$I;}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($H){$this->result=$H;}private
function
convert($J){foreach((array)$J
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$J[$x]=$X->load();}return$J;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$I=new
\stdClass;$I->name=oci_field_name($this->result,$d);$U=oci_field_type($this->result,$d);$I->native_type=idx([100=>"binary_float","binary_double"],$U,$U);return$I;}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$timeout=0;private$query_timeout=0;function
attach(array$N,$V,$E){return$this->dsn("oci:dbname=//".easy_connect($N).";charset=AL32UTF8",$V,$E);}function
select_db($wc){return$this->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($wc));}function
query($G,$Gn=false){$bn=$this->timeout;if(!$bn)return
parent::query($G,$Gn);$ok=$this->query_timeout;$this->timeout=0;$this->timeout(1000*$bn);$I=parent::query($G,$Gn);$this->timeout($ok);return$I;}function
timeout($Gh){$this->query_timeout=$Gh;return$this->pdo->setAttribute(1004,$Gh);}}}class
Driver
extends
SqlDriver{static$extensions=["OCI8","PDO_OCI"];static$jush="oracle";static$serverPath=true;var$insertFunctions=["date"=>"current_date","timestamp"=>"current_timestamp",];var$editFunctions=["number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",];var$functions=["length","lower","round","upper"];var$grouping=["avg","count","count distinct","max","min","sum"];function
operators($_m){return["=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","BETWEEN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL"];}static
function
connect($N,$V,$E){$g=parent::connect($N,$V,$E);if(is_object($g))$g->query("ALTER SESSION SET CURSOR_SHARING = FORCE"." NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'"." NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF'"." NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF TZH:TZM'");return$g;}function
__construct(Db$g){parent::__construct($g);$this->types=[lang(36)=>["number"=>38,"binary_float"=>12,"binary_double"=>21],lang(37)=>["date"=>19,"timestamp"=>29,"interval year"=>12,"interval day"=>28],lang(38)=>["char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295],lang(39)=>["raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296],lang(41)=>["sdo_geometry"=>0],];}function
begin(){return$this->conn->begin();}function
slowQuery($G,$bn){$this->conn->timeout=$bn;return$G;}function
convertSearch($u,array$X,array$n){$U=$n["type"];$Cj=strpos($X["op"],"LIKE")!==false;if($U=="xmltype")return"XMLSERIALIZE(CONTENT $u AS VARCHAR2(4000))";if($U=="json")return"JSON_SERIALIZE($u)";if(preg_match('~^(date|timestamp)~',$U))return"TO_CHAR($u, 'YYYY-MM-DD HH24:MI:SS')";if(preg_match('~char~',$U)||(preg_match('~clob~',$U)&&$Cj))return$u;return(!$Cj&&preg_match(number_type(),$U)?$u:"TO_CHAR($u)");}function
quoteBinary($al){return"HEXTORAW(".q(bin2hex($al)).")";}function
typeName(\stdClass$n){return
strtolower(parent::typeName($n));}function
hasCStyleEscapes(){return
true;}function
errorLine($G){$C=(isset($this->conn->error_offset)?$this->conn->error_offset:0);return($C?substr_count($G,"\n",0,min($C,strlen($G)))+1:0);}function
tableHelp($B,$hg=false){if(DB=="SYS"){if(preg_match('~^G?V_\$(.+)~',$B,$A))return"refrn/V-$A[1].html";if(preg_match('~^(ALL|DBA|USER)_~',$B))return"refrn/$B.html";}}function
select($R,array$M,array$Z,array$Oe,array$Ni=[],$z=1,$D=0,$ak=false){if(in_array("*",$M)){$fc=[];$ye=false;foreach(fields($R)as$B=>$n){$Ha=convert_field($n);$ye=($ye||$Ha);$fc[]=($Ha?"$Ha AS ":"").idf_escape($B);}if($ye)$M=$fc;}return
parent::select($R,$M,$Z,$Oe,$Ni,$z,$D,$ak);}function
allFields(){$I=[];$K=get_rows('SELECT c.table_name "tab", c.column_name "field", c.data_type "type", c.nullable "nullable",
	c.data_precision "precision", c.data_scale "scale", c.char_col_decl_length "char_length"
FROM all_tab_columns c
WHERE '.where_owner("c.owner").'
ORDER BY c.table_name, c.column_id',$this->conn);foreach($K
as$J){$y=($J["precision"]==""&&$J["scale"]!=""?"*":$J["precision"]).",$J[scale]";$J["length"]=(strpos($J["type"],"(")?"":($y==","?$J["char_length"]:$y));$J["type"]=strtolower($J["type"]);$J["null"]=($J["nullable"]=="Y");$I[$J["tab"]][]=$J;}return$I;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($se){$I=get_vals("SELECT username FROM all_users WHERE oracle_maintained = 'N' ORDER BY 1");return($I?:get_vals("SELECT username FROM all_users ORDER BY 1"));}function
limit($G,$Z,$z,$C=0,$tl=" "){return($C?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $G$Z) t WHERE rownum <= ".($z+$C).") WHERE rnum > $C":($z?" * FROM (SELECT $G$Z) WHERE rownum <= ".($z+$C):" $G$Z"));}function
limit1($R,$G,$Z,$tl="\n"){return" $G$Z";}function
db_collation($k,array$Ib){static$I;if($I===null)$I=get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");return$I;}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
where_owner($ej="owner"){return"$ej = ".q(DB);}function
views_table($e){return"(SELECT $e FROM all_views WHERE ".where_owner().")";}function
objects_table(){return"(SELECT object_name, DECODE(object_type, 'VIEW', 'view', 'table') object_type FROM all_objects WHERE ".where_owner()." AND object_type IN ('TABLE', 'VIEW'))";}function
tables_list(){return
get_key_vals("SELECT * FROM ".objects_table()." ORDER BY 1");}function
count_tables(array$j){$I=[];foreach($j
as$k)$I[$k]=get_val("SELECT COUNT(*) FROM all_objects WHERE object_type IN ('TABLE', 'VIEW') AND owner = ".q($k));return$I;}function
table_status($B="",$Yd=false){$I=[];$gl=q($B);if($Yd||$B!=""){foreach(get_rows('SELECT object_name "Name", object_type "Engine" FROM '.objects_table().($B!=""?" WHERE object_name = $gl":"").' ORDER BY 1')as$J)$I[$J["Name"]]=$J;return$I;}foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i
	JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE '.where_owner("t.owner")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM ".views_table("view_name")."
ORDER BY 1")as$J)$I[$J["Name"]]=$J;foreach(get_vals("SELECT table_name FROM all_indexes ai WHERE ".where_owner("table_owner")." AND (status = 'UNUSABLE'
	OR EXISTS (SELECT 1 FROM all_ind_partitions aip WHERE aip.index_owner = ai.owner AND aip.index_name = ai.index_name AND aip.status = 'UNUSABLE')
	OR EXISTS (SELECT 1 FROM all_ind_subpartitions ais WHERE ais.index_owner = ai.owner AND ais.index_name = ai.index_name AND ais.status = 'UNUSABLE'))")as$R){if($I[$R])$I[$R]["invalid_index"]="1";}return$I;}function
is_view(array$S){return$S["Engine"]=="view";}function
fk_support(array$S){return
true;}function
fields($R){$I=[];$vf=null;$Yj=[];foreach(table_constraints($R)as$Yb){if($Yb["type"]=="P")$Yj=$Yb["columns"];}foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)." AND ".where_owner()." ORDER BY column_id")as$J){$U=$J["DATA_TYPE"];$y=($J["DATA_PRECISION"]==""&&$J["DATA_SCALE"]!=""?"*":$J["DATA_PRECISION"]).",$J[DATA_SCALE]";if($y==","){$y=($U=="RAW"?$J["DATA_LENGTH"]:(preg_match('~CHAR~',$U)?$J["CHAR_COL_DECL_LENGTH"]:""));if($J["CHAR_USED"]=="C"&&preg_match('~^(VARCHAR2|CHAR)$~',$U))$y="$J[CHAR_LENGTH] CHAR";}elseif(strpos($U,"("))$y="";$l=$J["DATA_DEFAULT"];$Dc=false;if($l!==null){$l=rtrim($l);if(preg_match("~^'(?:[^']|'')*'\$~s",$l))$l=idf_unescape($l);elseif(preg_match('~^NULL$~i',$l))$l=null;else$Dc=!is_numeric($l);}if($J["IDENTITY_COLUMN"]=="YES"){if($vf===null)$vf=get_key_vals("SELECT column_name, generation_type FROM all_tab_identity_cols WHERE table_name = ".q($R)." AND ".where_owner());$l="GENERATED ".$vf[$J["COLUMN_NAME"]].($J["DEFAULT_ON_NULL"]=="YES"?" ON NULL":"")." AS IDENTITY";}$dk=["insert"=>1,"select"=>1,"update"=>1,"order"=>1];if($J["DATA_TYPE_OWNER"]==""||$U=="XMLTYPE")$dk["where"]=1;$I[$J["COLUMN_NAME"]]=["field"=>$J["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$l,"default_sql"=>($Dc&&$J["IDENTITY_COLUMN"]!="YES"),"null"=>($J["NULLABLE"]=="Y"),"auto_increment"=>($J["IDENTITY_COLUMN"]=="YES"),"privileges"=>$dk,"primary"=>in_array($J["COLUMN_NAME"],$Yj),];}return$I;}function
table_constraints($R,$h=null){static$jb=[];if(!$h&&isset($jb[$R]))return$jb[$R];$I=[];foreach(get_rows('SELECT c.constraint_name "name", c.constraint_type "type", c.r_owner "r_owner", c.r_constraint_name "r_constraint", c.delete_rule "delete_rule", cc.column_name "column"
FROM all_constraints c
JOIN all_cons_columns cc ON cc.owner = c.owner AND cc.constraint_name = c.constraint_name
WHERE c.constraint_type IN (\'P\', \'U\', \'R\') AND '.where_owner("c.owner")." AND c.table_name = ".q($R).'
ORDER BY cc.position',$h)as$J){$B=$J["name"];$I[$B]["type"]=$J["type"];$I[$B]["r_owner"]=$J["r_owner"];$I[$B]["r_constraint"]=$J["r_constraint"];$I[$B]["delete_rule"]=$J["delete_rule"];$I[$B]["columns"][]=$J["column"];}if(!$h)$jb[$R]=$I;return$I;}function
indexes($R,$h=null){$I=[];$Zb=[];$tj=[];foreach(table_constraints($R,$h)as$B=>$Yb)$Zb[$B]=$Yb["type"];foreach(get_rows("SELECT aic.*, atc.data_default, ai.status, ai.partitioned
FROM all_ind_columns aic
JOIN all_indexes ai ON ai.owner = aic.index_owner AND ai.index_name = aic.index_name
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)." AND ".where_owner("aic.table_owner")."
ORDER BY aic.column_position",$h)as$J){$Hf=$J["INDEX_NAME"];$Lb=$J["DATA_DEFAULT"];$Lb=($Lb?trim($Lb,'"'):$J["COLUMN_NAME"]);$U=idx($Zb,$Hf);$I[$Hf]["type"]=($U=="P"?"PRIMARY":($U=="U"?"UNIQUE":"INDEX"));$I[$Hf]["invalid"]=($J["STATUS"]=="UNUSABLE");$I[$Hf]["columns"][]=$Lb;$I[$Hf]["lengths"][]=($J["CHAR_LENGTH"]&&$J["CHAR_LENGTH"]!=$J["COLUMN_LENGTH"]?$J["CHAR_LENGTH"]:null);$I[$Hf]["descs"][]=($J["DESCEND"]&&$J["DESCEND"]=="DESC"?'1':null);if($J["PARTITIONED"]=="YES")$tj[$Hf]=q($Hf);}if($tj){$Af=where_owner("index_owner")." AND index_name IN (".implode(", ",$tj).") AND status = 'UNUSABLE'";foreach(get_rows("SELECT index_name FROM all_ind_partitions WHERE $Af UNION SELECT index_name FROM all_ind_subpartitions WHERE $Af",$h)as$J)$I[$J["INDEX_NAME"]]["invalid"]=true;}uasort($I,function($ia,$Ra){$Ni=["PRIMARY"=>0,"UNIQUE"=>1,"INDEX"=>2];return$Ni[$ia["type"]]-$Ni[$Ra["type"]];});return$I;}function
view($B){$K=get_rows('SELECT text "select" FROM '.views_table("view_name, text").' WHERE view_name = '.q($B));return($K?$K[0]:[]);}function
collations(){return[];}function
information_schema($k,$L=""){return
in_array($L!=""?$L:$k,["INFORMATION_SCHEMA","SYS","SYSTEM"]);}function
error(){return
h(connection()->error);}function
explain(Db$g,$G){$g->query("EXPLAIN PLAN FOR $G");return$g->query("SELECT * FROM plan_table");}function
found_rows(array$S,array$Z){}function
auto_increment(){return"";}function
alter_table($R,$B,array$o,array$ue,$f,$xd,$Hb,$Na,$uj){$b=$ed=[];$Vi=($R?fields($R):[]);foreach($o
as$n){$X=$n[1];if($X&&$n[0]!=""&&idf_escape($n[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($n[0])." TO $X[0]");$Ui=$Vi[$n[0]];if($X&&$Ui){$ti=process_field($Ui,$Ui);if(array_slice($X,1)==array_slice($ti,1))continue;if($X[2]==$ti[2])$X[2]="";}if($X){list($X[2],$X[3])=[$X[3],$X[2]];$b[]=($R!=""?($n[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");}else$ed[]=idf_escape($n[0]);}$b=array_merge($b,$ue);if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$ed||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$ed).")"))&&($R==$B||queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)));}function
alter_indexes($R,$b){$ed=[];$lk=[];foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$i=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($lk,"ALTER TABLE ".table($R).$i);}elseif($X[2]=="DROP")$ed[]=idf_escape($X[1]);else$lk[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($ed)array_unshift($lk,"DROP INDEX ".implode(", ",$ed));foreach($lk
as$G){if(!queries($G))return
false;}return
true;}function
foreign_keys($R){$I=[];$Um=[];foreach(table_constraints($R)as$B=>$Yb){if($Yb["type"]=="R"){$I[$B]=["source"=>$Yb["columns"],"target"=>[],"on_delete"=>$Yb["delete_rule"],"on_update"=>null,];$Um[$B]=[$Yb["r_owner"],$Yb["r_constraint"]];}}if($Um){$Z=[];foreach($Um
as$Rm)$Z[]="(owner = ".q($Rm[0])." AND constraint_name = ".q($Rm[1]).")";foreach(get_rows("SELECT owner, constraint_name, table_name, column_name FROM all_cons_columns WHERE ".implode(" OR ",array_unique($Z))." ORDER BY position")as$J){foreach($Um
as$B=>$Rm){if($Rm==[$J["OWNER"],$J["CONSTRAINT_NAME"]]){$I[$B]["db"]=($J["OWNER"]==DB?"":$J["OWNER"]);$I[$B]["table"]=$J["TABLE_NAME"];$I[$B]["target"][]=$J["COLUMN_NAME"];}}}}return$I;}function
trigger($B,$R){if($B=="")return[];$K=get_rows('SELECT trigger_name "Trigger", trigger_type "Type", triggering_event "Event", trigger_body "Statement"
FROM all_triggers
WHERE trigger_name = '.q($B)." AND ".where_owner());$I=reset($K);if($I){$U=$I["Type"];$I["Timing"]=(preg_match('~^(BEFORE|AFTER|INSTEAD OF)~',$U,$A)?$A[1]:$U);$I["Type"]=(preg_match('~EACH ROW~',$U)||$U=="INSTEAD OF"?"FOR EACH ROW":"");}return($I?:[]);}function
triggers($R){$I=[];foreach(get_rows("SELECT trigger_name, trigger_type, triggering_event FROM all_triggers WHERE table_name = ".q($R)." AND ".where_owner())as$J)$I[$J["TRIGGER_NAME"]]=[preg_replace('~ (STATEMENT|EACH ROW)$~','',$J["TRIGGER_TYPE"]),$J["TRIGGERING_EVENT"]];return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER","INSTEAD OF"],"Event"=>["INSERT","UPDATE","DELETE","INSERT OR UPDATE","INSERT OR DELETE","UPDATE OR DELETE","INSERT OR UPDATE OR DELETE"],"Type"=>["FOR EACH ROW",""],];}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$ro){return
apply_queries("DROP VIEW",$ro);}function
drop_tables(array$T){return
apply_queries("DROP TABLE",$T);}function
last_id($H){return"0";}function
create_database($k,$Hb){$I=queries("CREATE USER ".idf_escape($k)." NO AUTHENTICATION");return($I?queries("GRANT UNLIMITED TABLESPACE TO ".idf_escape($k)):$I);}function
drop_databases(array$j){$I=true;foreach($j
as$k)$I=!!queries("DROP USER ".idf_escape($k)." CASCADE")&&$I;return$I;}function
rename_database($B,$Hb){return!!queries("ALTER USER ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$I=[];$K=get_rows('SELECT * FROM v$instance');foreach(reset($K)as$x=>$X)$I[]=[$x,$X];return$I;}function
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
convert_field(array$n){if($n["type"]=="sdo_geometry")return"SDO_UTIL.TO_WKTGEOMETRY(".idf_escape($n["field"]).")";}function
unconvert_field(array$n,$I){return($n["type"]=="sdo_geometry"?"SDO_UTIL.FROM_WKTGEOMETRY($I)":$I);}function
support($Zd){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|sql|status|table|trigger|variables|view|view_trigger)$~',$Zd);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.3.1+5c48c0d4")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return[SERVER,$_GET["username"],get_password()];}function
connectSsl(){}function
permanentLogin($i=false){return
password_file($i);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
verifyLoginToken(){return
true;}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($se=true){return
get_databases($se);}function
pluginsLinks(){}function
operators($_m=null){return
driver()->operators($_m);}function
schemas(){$I=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$I))array_unshift($I,$_GET["ns"]);return$I;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$nc){return$nc;}function
verifyVersion(){return
true;}function
donation(){return"<a href='https://www.adminer.org/donation/'".target_blank()." id='donation' title='".lang(45)."'>❤️</a>";}function
serviceWorker(){service_worker();}function
manifest(){$qf=$_SERVER["HTTP_HOST"]?:$_SERVER["SERVER_NAME"];$rl=preg_replace('~\?.*~','',ME)?:'.';return['name'=>"Adminer".($qf!=""?" - $qf":""),'short_name'=>'Adminer','description'=>lang(46),'start_url'=>$rl,'scope'=>$rl,'display'=>'minimal-ui','icons'=>[['src'=>preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.3.1+5c48c0d4",'sizes'=>'any','type'=>'image/svg+xml']],];}function
head($sc=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$I=[];foreach(["","-dark"]as$Bh){$p="adminer$Bh.css";if(file_exists($p)){$ge=file_get_contents($p);$I["$p?v=".crc32($ge)]=($Bh?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$ge)?'':'light'));}}return$I;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.lang(47).'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,on('change','loginDriver'))),adminer()->loginFormField('server','<tr><th>'.lang(48).'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".lang(49)."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.lang(50).'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("fire(qs('#username').form['auth[driver]'], 'change');")),adminer()->loginFormField('password','<tr><th>'.lang(51).'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.lang(17).'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".lang(52)."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],lang(53))."\n";}function
loginFormField($B,$hf,$Y){return$hf.$Y."\n";}function
login($Sg,$E){if($E=="")return
lang(54).require_password_link(null);if(!Driver::$passwords)return
lang(55).require_password_link($E);if(!password_required())return
lang(56).require_password_link($E);return
true;}function
tableName(array$_m){return
h($_m["Name"]);}function
fieldName(array$n,$Ni=0){$U=$n["full_type"].($n["null"]?" NULL":"");$f=$n["comment"];return'<span title="'.h($U.($f!=""?($U?": ":"").$f:'')).'">'.h($n["field"]).'</span>';}function
commentValue($U,$f){if($f==""||$U=='SCHEMA'||$U=='TABLE'||$U=='COLUMN')return
h($f);$Rj=function($al,$mb='td'){return
preg_replace('~^~m','<tr>',preg_replace('~\|~',"<$mb>",preg_replace('~\|$~m',"",rtrim($al))));};$R='(\+--[-+]+\+\n)';$J='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$R?$J$R?($J*)$R?~m",function($A)use($Rj){return"<table>\n".($A[1]?"<thead>".$Rj($A[2],'th')."<tbody>\n":$Rj($A[2])).$Rj($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($f))))."</pre>\n";}function
commentInput($U,$c,$f){$Y=h($f);return(preg_match('~\n~',$Y)?"<textarea$c rows='2' cols='".($U=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$c value='$Y'>");}function
selectLinks(array$_m,$O=""){$B=$_m["Name"];echo'<p class="links">';$Ng=[];if($B!="")$Ng["select"]=lang(57);if(support("table")||support("indexes"))$Ng["table"]=lang(58);if(support("table")){if(is_view($_m)){if(support("view"))$Ng["view"]=lang(59);}elseif(function_exists('Adminer\alter_table')&&$B!="")$Ng["create"]=lang(60);}if($O!==null)$Ng["edit"]=lang(61);foreach($Ng
as$x=>$X)echo" <a href='".h(ME)."$x=".url_escape($B).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$zm){return[];}function
backwardKeysPrint(array$Ta,array$J){}function
selectQuery($G,$im,$Xd=false){$I="\n";if(!$Xd&&($vo=driver()->warnings())){$t="warnings";$I=", <a href='#$t' class='toggle'>".lang(62)."</a>"."$I<div id='$t' class='hidden'>\n$vo</div>\n";}return"<p><code class='sql jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>(".format_time($im).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($G)."' class='hover'>".lang(20)."</a>":"").$I;}function
sqlCommandQuery($G){return
shorten_utf8(trim($G),1000);}function
sqlPrintAfter(){}function
sqlUrlLength(){return
2000;}function
explain(Db$g,$G,array$Ri){$H=explain($g,$G);if(!$H)return"";ob_start();print_select_result($H,$g,$Ri);return
ob_get_clean();}function
rowDescription($R){return"";}function
rowDescriptions(array$K,array$ve){return$K;}function
selectLink($X,array$n){}function
selectVal($X,$_,array$n,$Yi){$I=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$n["type"])&&!preg_match("~var~",$n["type"])?"<code>$X</code>":(preg_match('~^jsonb?$~',$n["full_type"])?"<code class='jush-json'>$X</code>":$X)));if(is_blob($n)&&!is_utf8($X))$I="<i>".lang(63,strlen($Yi))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$I</a>":$I);}function
editVal($X,array$n){return$X;}function
config(){return[];}function
tableStructurePrint(array$o,$_m=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".lang(64)."<th>".lang(65).(support("comment")?"<th>".lang(66):"")."<tbody>\n";$fo=(support("type")?types():[]);foreach($o
as$n){echo"<tr><th>".h($n["field"]).doc_link([JUSH=>$n["doc"]]);$U=h($n["full_type"]);$Hb=h($n["collation"]);echo"<td><span title='$Hb'>".(in_array($U,$fo)?"<a href='".h(ME.'type='.url_escape($U))."'>$U</a>":$U.($Hb&&isset($_m["Collation"])&&$Hb!=$_m["Collation"]?" $Hb":""))."</span>",($n["null"]?" <i>NULL</i>":""),($n["auto_increment"]?" <i>".lang(67)."</i>":""),(isset($n["default"])?" <span title='".lang(68)."'>[<b>".($n["generated"]||$n["default_sql"]?"<code class='sql jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($n["default"])),80,"</code>"):h($n["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$n["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$_m){$oj=false;foreach($w
as$B=>$v)$oj|=!!$v["partial"];echo"<table>\n";$Bc=first(driver()->indexAlgorithms($_m));foreach($w
as$B=>$v){ksort($v["columns"]);$ak=[];foreach($v["columns"]as$x=>$X)$ak[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".h($v["lengths"][$x]).")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>".h($v["type"]).($Bc&&$v['algorithm']!=$Bc?" (".h($v['algorithm']).")":""),"<td>".implode(", ",$ak).($v["invalid"]?" <span class='error'>INVALID</span>":"");if($oj)echo"<td>".($v['partial']?"<code class='sql jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
namePattern($U){if($U=="FOREIGN"||$U=="CHECK")return"";if($U=="TRIGGER")return"{table}_{timing}{event}";return(JUSH=="sql"?"":"{table}_")."{columns}";}function
selectColumnInput($c,array$e,$Y="",$Gj=""){return
select_input($c,$e,$Y,$Gj);}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",lang(69),$M);$s=0;$M[""]=[];foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,[]);$d=adminer()->selectColumnInput(" name='columns[$s][col]' data-default=''".on('change',($x!==""?'selectFieldChange':'selectAddRow')),$e,$X["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",[-1=>""]+array_filter([lang(70)=>driver()->functions,lang(71)=>driver()->grouping]),$X["fun"]," data-default=''".on('change',($x!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w,$_m=null){print_fieldset("search",lang(18),$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) ".h(driver()->fulltextOperator)," <input type='search' name='fulltext[".h($s)."]' value='".h(idx($_GET["fulltext"],$s))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"):''),"</div>\n";}$Gi=adminer()->operators($_m);foreach(array_merge((array)$_GET["where"],[[]])as$s=>$X){$s=number($s);if(!$X||(("$X[col]$X[val]"!=""||preg_match('~NULL$~',$X["op"]))&&in_array($X["op"],$Gi)))echo"<div>".adminer()->selectColumnInput(" name='where[$s][col]' data-default=''".on('change',($X?'selectFieldChange':'selectAddRow')),$e,$X["col"],"(".lang(72).")"),html_select("where[$s][op]",$Gi,$X["op"]," data-default='".h(first($Gi))."'".on('change','selectFirstChange')),"<input type='search' name='where[$s][val]' value='".h($X["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$Ni,array$e,array$w){print_fieldset("sort",lang(73),$Ni);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".adminer()->selectColumnInput(" name='order[$s]' data-default=''".on('change','selectFieldChange'),$e,$X),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),lang(74))."</div>\n";$s++;}}echo"<div>".adminer()->selectColumnInput(" name='order[$s]' data-default=''".on('change','selectAddRow'),$e),checkbox("desc[$s]",1,false,lang(74))."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".lang(75)."</legend><div>","<input type='number' name='limit' class='size' value='".h($z?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($Ym){echo"<fieldset><legend>".lang(76)."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Ym)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".lang(77)."</legend><div>","<input type='submit' value='".lang(69)."'>"," <span id='noindex' title='".lang(78)."'></span>","<script".nonce().">\n","const indexColumns = ";$e=[];foreach($w
as$v){$rc=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$rc)$e[$rc]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$sd,array$e){}function
selectColumnsProcess(array$e,array$w){$M=[];$Oe=[];foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$Oe[]=$M[$x];}}return[$M,$Oe];}function
selectSearchProcess(array$o,array$w,$_m=null){$I=[];foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$I[]=driver()->fulltextSql($s,$v,$_GET["fulltext"][$s],isset($_GET["boolean"][$s]));}$Gi=adminer()->operators($_m);foreach((array)$_GET["where"]as$x=>$X){$X+=["col"=>"","op"=>first($Gi),"val"=>""];$_GET["where"][$x]=$X;$Fb=$X["col"];if(("$Fb$X[val]"!=""||preg_match('~NULL$~',$X["op"]))&&in_array($X["op"],$Gi)){if($X["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$Sb=[];foreach(($Fb!=""?[$Fb=>idx($o,$Fb,[])]:$o)as$B=>$n){if($Fb!=""||is_searchable($n,$X))$Sb[]=adminer()->selectSearchCondition(driver()->convertSearch(idf_escape($B),$X,$n),$X["op"],$X["val"]);}$I[]=(count($Sb)==1?$Sb[0]:($Sb?"(".implode(" OR ",$Sb).")":"1 = 0"));}}return$I;}function
selectSearchCondition($d,$Ci,$X){if(preg_match('~IN$~',$Ci))return"$d $Ci ".($X!=""?process_in($X):"(NULL)");if($Ci=="BETWEEN")return"$d $Ci ".process_between($X);if($Ci=="SQL")return"$d $X";if(preg_match('~^(I?LIKE) %%$~',$Ci,$A))return"$d $A[1] ".q("%$X%");if($Ci=="FIND_IN_SET")return"$Ci(".q($X).", $d)";return"$d $Ci".(!preg_match('~NULL$~',$Ci)?" ".q($X):"");}function
selectOrderProcess(array$o,array$w){$I=[];foreach((array)$_GET["order"]as$x=>$X){if($X!="")$I[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`\\\\]|``)+`|"(?:[^"\\\\]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC".(JUSH=='pgsql'&&idx($o[$X],"null")?" NULLS LAST":""):"");}return$I;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$ve){return
false;}function
selectQueryBuild(array$M,array$Z,array$Oe,array$Ni,$z,$D){return"";}function
executeQuery($G,$lb){return$lb();}function
messageQuery($G,$an,$Xd=false){restart_session();$nf=&get_session("queries");if(!idx($nf,$_GET["db"]))$nf[$_GET["db"]]=[];if(strlen($G)>1e6)$G=preg_replace('~[\x80-\xFF]+$~','',substr($G,0,1e6))."\n…";$nf[$_GET["db"]][]=[$G,time(),$an];$bm="sql-".count($nf[$_GET["db"]]);$I="<a href='#$bm' class='toggle'>".lang(79)."</a> ".copy_icon()."\n";if(!$Xd&&($vo=driver()->warnings())){$t="warnings-".count($nf[$_GET["db"]]);$I="<a href='#$t' class='toggle'>".lang(62)."</a>, $I<div id='$t' class='hidden'>\n$vo</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $I<div id='$bm' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($G,1e4)."</code></pre>".($an?" <span class='time'>($an)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($nf[$_GET["db"]])-1)).'">'.lang(20).'</a>':'').'</div>';}function
error(){return
error();}function
editRowPrint($R,array$o,$J,$Rn,$G='',$an=''){echo($G!=""?"<p><code class='sql jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>($an)</span>\n":"");}function
editFunctions(array$n){$I=($n["null"]?"NULL/":"");$df=isset($_GET["select"])||where($_GET);foreach([driver()->insertFunctions,driver()->editFunctions]as$x=>$Ie){if(!$x||(!isset($_GET["call"])&&$df)){foreach($Ie
as$Cj=>$X){if(!$Cj||preg_match("~$Cj~",$n["type"]))$I
.="/$X";}}if($x&&$Ie&&!preg_match('~set|bool~',$n["type"])&&!is_blob($n))$I
.="/SQL";}if($n["auto_increment"]&&!$df)$I=lang(67);return
explode("/",$I);}function
editInput($R,array$n,$c,$Y){if($n["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='orig' checked><i>".lang(12)."</i></label> ":"").enum_input("radio",$c,$n,$Y,"NULL");return"";}function
editHint($R,array$n,$Y){return"";}function
processInput(array$n,$Y,$r=""){if($r=="SQL")return$Y;$B=$n["field"];$I=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$I="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$I=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$I=idf_escape($B)." $r $I";elseif(preg_match('~^[+-] interval$~',$r))$I=idf_escape($B)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$I);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$I="$r(".idf_escape($B).", $I)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$I="$r($I)";return
unconvert_field($n,$I);}function
dumpOutput(){$I=['text'=>lang(80),'file'=>lang(81)];if(function_exists('gzencode'))$I['gz']='gzip';return$I;}function
dumpFormat(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
dumpPrint(){}function
dumpDatabase($k){}function
dumpTable($R,$qm,$hg=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($qm)dump_csv(array_keys(fields($R)));}else{if($hg==2){$o=[];foreach(fields($R)as$B=>$n)$o[]=idf_escape($B)." ".full_type_sql($n);$i="CREATE TABLE ".table($R)." (".implode(", ",$o).")";}else$i=create_sql($R,$_POST["auto_increment"],$qm);set_utf8mb4($i);if($qm&&$i){if(($qm=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$hg==1)echo"DROP ".($hg==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($hg==1)$i=remove_definer($i);echo"$i;\n\n";}}}function
dumpData($R,$qm,$G,array$M=[],array$Z=[],array$Oe=[],array$Ni=[]){if($qm){$dh=(JUSH=="sqlite"?0:1048576);$o=[];$wf=false;if($_POST["format"]=="sql"){if($qm=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($R).";\n";$o=fields($R);if(JUSH=="mssql"){foreach($o
as$n){if($n["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$wf=true;break;}}}}$H=($G!=""?connection()->query($G,1):driver()->select($R,($M?:["*"]),$Z,$Oe,$Ni,0));if($H){$Sf="";$eb="";$qg=[];$Je=[];$dj="";$sm="";$be=($R!=''?'fetch_assoc':'fetch_row');$jc=0;while($J=$H->$be()){if(!$qg){$mo=[];foreach($J
as$X){$n=$H->fetch_field();if(idx($o[$n->name],'generated')){$Je[$n->name]=true;continue;}$qg[]=$n->name;if(JUSH=="pgsql"&&isset($o[$n->name])&&is_identity_always($o[$n->name]))$dj=" OVERRIDING SYSTEM VALUE";$x=idf_escape($n->name);$mo[]="$x = VALUES($x)";}$sm=($qm=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$mo):"").";\n";}foreach($J
as$x=>$X){if(is_array($X))$J[$x]=json_encode($X,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}if($_POST["format"]!="sql"){if($qm=="table"){dump_csv($qg);$qm="INSERT";}dump_csv($J);}else{if(!$Sf)$Sf="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$qg)).")$dj VALUES";foreach($J
as$x=>$X){if($Je[$x]){unset($J[$x]);continue;}$n=$o[$x];$J[$x]=($X===null?"NULL":($X===false?0:unconvert_field($n,preg_match(number_type(),$n["type"])&&!preg_match('~\[~',$n["full_type"])&&is_numeric($X)?$X:(!is_blob($n)||is_utf8($X)?q($X):driver()->quoteBinary($X)))));}$al=($dh?"\n":" ")."(".implode(",\t",$J).")";if(!$eb)$eb=$Sf.$al;elseif(JUSH=='mssql'?$jc%1000!=0:strlen($eb)+4+strlen($al)+strlen($sm)<$dh)$eb
.=",$al";else{echo$eb.$sm;$eb=$Sf.$al;}}$jc++;}if($eb)echo$eb.$sm;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";else
dump_csv([connection()->error]);if($wf)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($uf){return
friendly_url($uf!=""?$uf:(SERVER?:"localhost"));}function
dumpHeaders($uf,$Ih=false){$cj=$_POST["output"];$Sd=(preg_match('~sql~',$_POST["format"])?"sql":($Ih?"tar":"csv"));header("Content-Type: ".($cj=="gz"?"application/x-gzip":($Sd=="tar"?"application/x-tar":($Sd=="sql"||$cj!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($cj=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$Sd;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.lang(82)."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?lang(83):lang(84))."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.lang(85)."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".lang(86)."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".lang(87)."</a>\n":""),(support("sequence")?"<a href='#sequences'>".lang(88)."</a>\n":""),(support("type")?"<a href='#user-types'>".lang(0)."</a>\n":""),(support("event")?"<a href='#events'>".lang(89)."</a>\n":"");return
true;}function
navigation($Ah){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$ci=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$ci)<0?h($ci):"").version_iframe()."</a>","</span></h1>\n";switch_lang();if($Ah=="auth"){$cj="";foreach((array)$_SESSION["pwds"]as$oo=>$Gl){foreach($Gl
as$N=>$go){$B=h(get_setting("vendor-$oo-$N")?:get_driver($oo));foreach($go
as$V=>$E){if($B&&$E!==null){$_c=$_SESSION["db"][$oo][$N][$V];foreach(($_c?array_keys($_c):[""])as$k)$cj
.="<li><a href='".h(auth_url($oo,$N,$V,$k))."'>($B) ".h("$V@").($N!=""?adminer()->serverName($N):"").h($k!=""?" - $k":"")."</a>\n";}}}}if($cj)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$cj</ul>\n";}else{$T=[];if($_POST&&!$Ah&&DB!=""){connection()->select_db(DB);if(support("scheme")&&$_GET["ns"]!="")set_schema($_GET["ns"]);}if($_GET["ns"]!==""&&!$Ah&&DB!="")$T=table_status('',true);adminer()->syntaxHighlighting($T);adminer()->databasesPrint($Ah);$la=[];if(DB==""||!$Ah){if(support("sql")){$la['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".lang(79)."</a>";$la['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".lang(90)."</a>";}$la['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".lang(91)."</a>";}$Bf=$_GET["ns"]!==""&&!$Ah&&DB!="";if($Bf&&function_exists('Adminer\alter_table'))$la['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".lang(92)."</a>";$la=adminer()->menuActions($la,$Ah);echo($la?"<p class='links'>\n".implode("\n",$la)."\n":"");$kl=[];if($Bf){if(function_exists('Adminer\alter_table'))$kl[]=[lang(92),ME."create="];if(support("view"))$kl[]=[lang(93),ME."view="];$kl[]=[lang(85),ME."schema="];$ae=['routine'=>['routines',lang(87)],'sequence'=>['sequences',lang(88)],'type'=>['user-types',lang(0)],'event'=>['events',lang(89)],];foreach($ae
as$Zd=>$jl){if(support($Zd))$kl[]=[$jl[1],substr(ME,0,-1)."#$jl[0]"];}}if(DB!=""&&!$Ah&&support("extension"))$kl[]=[lang(94),preg_replace('~&ns=[^&]*~','',ME)."ns=#extensions"];$Fl=['privileges'=>lang(86),'processlist'=>lang(95),'variables'=>lang(96),'status'=>lang(97),];foreach($Fl
as$x=>$X){if(support($x))$kl[]=[$X,preg_replace('~&(db|ns)=[^&]*~','',ME)."$x="];}echo($kl?script("jumpSections.unshift(...".json_encode($kl,JSON_UNESCAPED_UNICODE).");"):"");if($Bf){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".lang(19)."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.3.1+5c48c0d4",true);$Dh=preg_replace('~<(?=/script)~i','<\\',Driver::jushModule());echo($Dh?script("addEventListener('DOMContentLoaded', () => {\n$Dh\n});"):"");if(support("sql")){echo"<script".nonce().">\n";if($T){$Ng=[];foreach($T
as$R=>$U)$Ng[]=js_escape_re($R);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$Ng).')(?!\$)\b/g',false);$dm=["sql","check","event","procedure","trigger","view","type","table","processlist"];if(support("routine")&&array_intersect_key($_GET,array_flip($dm))){foreach(routines()as$J)json_row(js_escape(ME).'function='.url_escape($J["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($J["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(array_intersect_key($_GET,array_flip(["sql","check","event","procedure","trigger","view"]))){$lm=(isset($_GET["trigger"])?['INSERT INTO','UPDATE','DELETE FROM']:(isset($_GET["check"])?[]:(isset($_GET["view"])?['SELECT']:null)));$Pa=Driver::jushAutocomplete($T,$lm);echo($Pa?"addEventListener('DOMContentLoaded', () => { autocompleter = $Pa; });\n":"");}}echo"</script>\n";}echo
script("syntaxHighlighting('".doc_version()."', '".connection()->flavor."');");}function
databasesPrint($Ah){if(support("single_db"))return;$j=adminer()->databases();if(DB&&$j&&!in_array(DB,$j))array_unshift($j,DB);echo"<form>\n<p id='dbs'>\n";hidden_fields_get();$xc=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".lang(17)."'>".lang(98).": ".($j?html_select("db",[""=>""]+group_system($j),DB,$xc):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".lang(31)."'".($j?" class='hidden'":"").">\n";if(support("scheme")){if($Ah!="db"&&DB!="")echo"<br><label>".lang(16).": ".html_select("ns",[""=>""]+group_system(adminer()->schemas(),true),$_GET["ns"],$xc)."</label>";}foreach(["import","sql","schema","dump","privileges"]as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
menuActions(array$la,$Ah){return$la;}function
tablesPrint(array$T){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($T
as$R=>$P){$R="$R";$B=adminer()->tableName($P);if($B!=""&&!$P["dependent"])echo'<li><a href="'.h(ME).'select='.url_escape($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select hover")." title='".lang(57)."'>".lang(13)."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($R).'"'.bold(in_array($R,[$_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]),(is_view($P)?"view":"structure"))." title='".lang(58)."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}function
formatSizeValue($X){return
format_number($X);}}class
Plugins{private
static$append=['dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true];var$plugins;var$disabled=[];var$drivers=[];var$driverFiles=[];var$error='';private$hooks=[];function
__construct($Kj){$dd=SqlDriver::$drivers;$jf=" href='https://www.adminer.org/plugins/#use'".target_blank();if($Kj===null){$Kj=[];$Xa="adminer-plugins";if(is_dir($Xa)){foreach(glob("$Xa/*.php")as$p){$he=SqlDriver::$drivers;$this->includeOnce($p);foreach(array_diff_key(SqlDriver::$drivers,$he)as$t=>$B)$this->driverFiles[$t]=$p;}}if(file_exists("$Xa.php")){$Df=$this->includeOnce("$Xa.php");if(is_array($Df)){foreach($Df
as$x=>$Hj)$Kj[is_object($Hj)?get_class($Hj):$x]=$Hj;}else$this->error
.=lang(99,"<b>$Xa.php</b>",$jf)."<br>";}foreach(get_declared_classes()as$Bb){if(!$Kj[$Bb]&&(preg_match('~^Adminer\w~i',$Bb)||is_subclass_of($Bb,'Adminer\Plugin'))){$zk=new
\ReflectionClass($Bb);$ac=$zk->getConstructor();if($ac&&$ac->getNumberOfRequiredParameters())$this->error
.=lang(100,$jf,"<b>$Bb</b>","<b>$Xa.php</b>")."<br>";else$Kj[$Bb]=new$Bb;}}}$Xf=array_filter($Kj,function($Hj){return!is_object($Hj);});if($Xf){$this->error
.=lang(101,$jf)."<br>";$Kj=array_diff_key($Kj,$Xf);}$wm=get_setting("plugins","adminer_config");foreach($Kj
as$x=>$Hj){if($Hj
instanceof
Plugin&&$Hj::$optional){$jm=idx($wm,get_class($Hj));if($jm!==null?!$jm:!$Hj->enabled){$this->disabled[]=$Hj;unset($Kj[$x]);}}}$this->drivers=array_diff_key(SqlDriver::$drivers,$dd);$this->plugins=$Kj;$qa=new
Adminer;$Kj[]=$qa;$zk=new
\ReflectionObject($qa);foreach($zk->getMethods()as$yh){foreach($Kj
as$Hj){$B=$yh->getName();if(method_exists($Hj,$B))$this->hooks[$B][]=$Hj;}}}function
includeOnce($p){return
include_once"./$p";}static
function
pluginName($Hj){return
preg_replace('~^ Adminer\\\\? ~','',preg_replace('~[A-Z]~',' $0',get_class($Hj)));}static
function
describe($Hj){$Lc=(method_exists($Hj,'description')?$Hj->description():"");if(!$Lc){$zk=new
\ReflectionObject($Hj);if(preg_match('~^/[\s*]+(.+?)(?:\s*\*/)?$~m',$zk->getDocComment(),$A))$Lc=$A[1];}$fl=(method_exists($Hj,'screenshot')?$Hj->screenshot():"");return
h($Lc).($fl?($Lc?" ":"")."(<a href='".h($fl)."'".target_blank().">".lang(102)."</a>)":"");}static
function
checksum($p){$ge=str_replace("\r","",file_get_contents($p));$ge=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$ge);return
dechex(crc32($ge));}function
checksums(){$ie=array_values($this->driverFiles);foreach($this->plugins
as$Hj){$zk=new
\ReflectionObject($Hj);$ie[]=$zk->getFileName();}$I=[];foreach($ie
as$p)$I[basename($p,'.php')]=self::checksum($p);return$I;}static
function
officialChecksums(){return['adminer.js'=>'a0599090','backward-keys'=>'1c017d59','before-unload'=>'9674d220','config'=>'bd5d79e5','dark-switcher'=>'8c201319','database-hide'=>'e304a899','designs'=>'c0a56b18','donation'=>'365aa745','dump-alter'=>'896b579e','dump-bz2'=>'c0d6de78','dump-date'=>'512aad63','dump-json'=>'f8aa6b99','dump-xml'=>'aedd2d3a','dump-zip'=>'96882e78','edit-foreign'=>'d04a0ac8','edit-textarea'=>'a178d7d9','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'e9005cb0','file-upload'=>'e7f2b388','foreign-system'=>'634ce119','frames'=>'b0e1d11a','highlight-codemirror'=>'ada4b569','highlight-monaco'=>'95cf93b3','highlight-prism'=>'19933b19','import-csv'=>'bcecfad2','login-ip'=>'4d174fea','login-otp'=>'66bd013d','login-passkey'=>'a73c386f','login-password-less'=>'e150daac','login-reverse-proxy'=>'24558ea2','login-servers'=>'19c42e45','login-ssl'=>'2b06a5b7','login-table'=>'811f8cef','menu-links'=>'55b9b15c','name-patterns'=>'84c10d09','remote-color'=>'c5e74631','row-numbers'=>'1ad1b6cf','select-email'=>'c33a2661','select-expression'=>'b6a4b3f1','select-foreign'=>'2c6bb6c3','select-image'=>'19dd2499','slugify'=>'7b422f0e','sql-gemini'=>'d65d01df','sql-log'=>'b9aa4ea6','sql-url-length'=>'cd70f1a0','table-indexes-structure'=>'e0b1433','table-structure'=>'f71ec61b','tables-filter'=>'2729bf7','timeout'=>'9e9bce60','version-github'=>'627cadf9','version-noverify'=>'966937e9','clickhouse'=>'79f6dd80','elastic'=>'b7e4a70d','firebird'=>'5f3d9493','igdb'=>'39c028f6','imap'=>'35808394','mongo'=>'2d9cae00','redis'=>'c2f04eae','simpledb'=>'9eb464cf',];}function
__call($B,array$kj){$Fa=[];foreach($kj
as$x=>$X)$Fa[]=&$kj[$x];$I=null;foreach($this->hooks[$B]as$Hj){$Y=call_user_func_array([$Hj,$B],$Fa);if($Y!==null){if(!self::$append[$B])return$Y;$I=$Y+(array)$I;}}return$I;}}abstract
class
Plugin{static$optional=false;var$enabled=true;protected$translations=[];function
disable(){$this->enabled=false;return$this;}function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$ki=null){$Fa=func_get_args();$Fa[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$Fa);}}class
Password{private$password_hash;private$password_matches=null;function
__construct($zj){$this->password_hash=$zj;}function
description(){return
lang(103);}function
credentials(){$E=get_password();return[SERVER,$_GET["username"],($this->passwordMatches($E)&&!password_required()?"":$E)];}function
login($Sg,$E){if($this->passwordMatches($E))return
true;if(!Driver::$passwords||!password_required())return
false;}protected
function
passwordMatches($E){if($this->password_matches===null)$this->password_matches=(function_exists('password_verify')&&password_verify(strval($E),$this->password_hash));return$this->password_matches;}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=["server"=>"MySQL / MariaDB"]+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\mysqli{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach(array$N,$V,$E){mysqli_report(MYSQLI_REPORT_OFF);$Lj=$N["port"];$ud=("$N[host]$Lj$N[socket]"=="");$hm=adminer()->connectSsl();$bo=($hm&&($hm['key']||$hm['cert']||$hm['ca']||isset($hm['verify'])));if($bo)$this->ssl_set($hm['key'],$hm['cert'],$hm['ca'],'','');$I=@$this->real_connect((!$ud?$N["host"]:ini_get("mysqli.default_host")),(!$ud||$V!=""?$V:ini_get("mysqli.default_user")),(!$ud||$V.$E!=""?$E:ini_get("mysqli.default_pw")),null,($Lj!=""?intval($Lj):ini_get("mysqli.default_port")),($Lj!=""?null:$N["socket"]),($bo?($hm['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($I?'':$this->error);}function
set_charset($rb){if($this->send_charset($rb))return
true;$this->send_charset('utf8');return$this->query("SET NAMES $rb");}private
function
send_charset($rb){return
adminer()->executeQuery("SET NAMES $rb",function()use($rb){return
parent::set_charset($rb);});}function
select_db($wc){return
adminer()->executeQuery("USE ".idf_escape($wc),function()use($wc){return
parent::select_db($wc);});}function
query($G,$Lk=MYSQLI_STORE_RESULT){return
adminer()->executeQuery($G,function()use($G,$Lk){return
parent::query($G,$Lk);});}function
multi_query($G){return
adminer()->executeQuery($G,function()use($G){return
parent::multi_query($G);});}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}function
inTransaction(){return
false;}function
begin(){return(method_exists($this,'begin_transaction')?adminer()->executeQuery("BEGIN",function(){return$this->begin_transaction();}):!!$this->query("BEGIN"));}function
commit($qe=0,$B=null){return
adminer()->executeQuery("COMMIT",function(){return
parent::commit();});}function
rollback($qe=0,$B=null){return
adminer()->executeQuery("ROLLBACK",function(){return
parent::rollback();});}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach(array$N,$V,$E){if(ini_bool("mysql.allow_local_infile"))return
lang(104,"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$Lj="$N[port]$N[socket]";$B=$N["host"].($Lj!=""?":$Lj":"");$this->link=@mysql_connect(($B!=""?$B:ini_get("mysql.default_host")),($B.$V!=""?$V:ini_get("mysql.default_user")),($B.$V.$E!=""?$E:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($rb){return$this->send_charset($rb)||$this->send_charset('utf8');}private
function
send_charset($rb){return
adminer()->executeQuery("SET NAMES $rb",function()use($rb){return
mysql_set_charset($rb,$this->link);});}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($wc){return
adminer()->executeQuery("USE ".idf_escape($wc),function()use($wc){return
mysql_select_db($wc,$this->link);});}function
query($G,$Gn=false){$H=adminer()->executeQuery($G,function()use($G,$Gn){return@($Gn?mysql_unbuffered_query($G,$this->link):mysql_query($G,$this->link));});$this->error="";if(!$H){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($H===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($H);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=mysql_num_rows($H);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$I=mysql_fetch_field($this->result,$this->offset++);$I->orgtable=$I->table;$I->native_type=idx(["string"=>"varchar","real"=>"double"],$I->type,$I->type);return$I;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach(array$N,$V,$E){$Ki=[\PDO::MYSQL_ATTR_LOCAL_INFILE=>false];if(isset($_GET["select"]))$Ki[1012]=false;$hm=adminer()->connectSsl();if($hm){if($hm['key'])$Ki[\PDO::MYSQL_ATTR_SSL_KEY]=$hm['key'];if($hm['cert'])$Ki[\PDO::MYSQL_ATTR_SSL_CERT]=$hm['cert'];if($hm['ca'])$Ki[\PDO::MYSQL_ATTR_SSL_CA]=$hm['ca'];if(isset($hm['verify']))$Ki[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$hm['verify'];}$qf=$N["host"];$Lj=$N["port"];$Ql=$N["socket"];return$this->dsn("mysql:charset=utf8".($qf!=""?";host=$qf":'').($Lj!=""?";port=$Lj":($Ql!=""?";unix_socket=$Ql":"")),$V,$E,$Ki);}function
set_charset($rb){return$this->query("SET NAMES $rb");}function
select_db($wc){return$this->query("USE ".idf_escape($wc));}function
query($G,$Gn=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Gn);return
parent::query($G,$Gn);}}}class
Driver
extends
SqlDriver{static$extensions=["MySQLi","MySQL","PDO_MySQL"];static$jush="sql";static$serverSocket=true;var$unsigned=["unsigned","zerofill","unsigned zerofill"];var$functions=["char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper"];var$grouping=["avg","count","count distinct","group_concat","max","min","sum"];var$partitionBy=["HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST"];function
operators($_m){return["=","<",">","<=",">=","!=","&","LIKE","LIKE %%","REGEXP","IN","BETWEEN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL"];}static
function
connect($N,$V,$E){$g=parent::connect($N,$V,$E);if(is_string($g)){if(function_exists('iconv')&&!is_utf8($g)&&strlen($al=iconv("windows-1252","utf-8//IGNORE",$g))>strlen($g))$g=$al;return$g;}$g->set_charset(charset($g));$g->query("SET sql_quote_show_create = 1, autocommit = 1");$g->flavor=(preg_match('~MariaDB~',$g->server_info)?'maria':'mysql');add_driver(DRIVER,($g->flavor=='maria'?"MariaDB":"MySQL"));return$g;}function
__construct(Db$g){parent::__construct($g);$this->types=[lang(36)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21],lang(37)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4],lang(38)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295],lang(105)=>["enum"=>65535,"set"=>64],lang(39)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295],lang(41)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0],];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if(min_version('5.7.8',10.2,$g))$this->types[lang(38)]["json"]=4294967295;if(min_version('',10.7,$g)){$this->types[lang(38)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$g)){$this->types[lang(40)]["inet6"]=39;if(min_version('','10.10',$g))$this->types[lang(40)]["inet4"]=15;}if(min_version(9,11.7,$g))$this->types[lang(36)]["vector"]=16383;if(min_version(5.7,10.2,$g))$this->generated=["STORED","VIRTUAL"];}function
unconvertFunction(array$n){return(preg_match("~binary~",$n["type"])?"<code class='jush-sql'>UNHEX</code>":($n["type"]=="bit"?doc_link(['sql'=>'bit-value-literals.html'],"<code>b''</code>"):($n["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geom|point|linestring|polygon~",$n["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$K,array$Yj){$e=array_keys(reset($K));$Tj="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$mo=[];foreach($e
as$x)$mo[$x]="$x = VALUES($x)";$sm="\nON DUPLICATE KEY UPDATE ".implode(", ",$mo);$mo=[];$y=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($mo&&(strlen($Tj)+$y+strlen($Y)+strlen($sm)>1e6)){if(!queries($Tj.implode(",\n",$mo).$sm))return
false;$mo=[];$y=0;}$mo[]=$Y;$y+=strlen($Y)+2;}return
queries($Tj.implode(",\n",$mo).$sm);}function
slowQuery($G,$bn){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$bn FOR $G";elseif(preg_match('~^(SELECT\b)(.+)~is',$G,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($bn*1000).") */ $A[2]";}}function
convertColumn($u,array$n){if(preg_match("~binary~",$n["type"]))return"HEX($u)";if($n["type"]=="bit")return"BIN($u + 0)";if($n["type"]=="vector")return($this->conn->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."($u)";if(preg_match("~geom|point|linestring|polygon~",$n["type"]))return(min_version(8)?"ST_":"")."AsWKT($u)";return"";}function
convertSearch($u,array$X,array$n){return($this->convertColumn($u,$n)?:(preg_match('~'.text_type().'~',$n["type"])&&!preg_match("~^utf8~",$n["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u));}function
typeName(\stdClass$n){$B=parent::typeName($n);if($B!=""){$Fn=["TINY"=>"tinyint","SHORT"=>"smallint","LONG"=>"int","INT24"=>"mediumint","LONGLONG"=>"bigint","NEWDECIMAL"=>"decimal","VAR_STRING"=>"varchar","STRING"=>"char",];return
idx($Fn,$B,strtolower($B));}$Fn=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$I=idx($Fn,$n->type,"");return($n->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$I):$I);}function
quoteBinary($al){return"X".q(bin2hex($al));}function
md5($d,array$n){if(is_blob($n)||preg_match('~'.text_type().'~',$n["type"]))return"MD5(".(is_blob($n)||preg_match("~^utf8~",$n["collation"])?$d:"CONVERT($d USING ".charset($this->conn).")").")";}function
warnings(){$H=$this->conn->query("SHOW WARNINGS");if($H&&$H->num_rows){ob_start();print_select_result($H);return
ob_get_clean();}}function
errorLine($G){return(preg_match('~ at line (\d+)$~',$this->conn->error,$A)?(int)$A[1]:0);}function
tableHelp($B,$hg=false){$Ug=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($Ug?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="sys")return($Ug?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$B)).".html"));if(DB=="mysql")return($Ug?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($R){$Ce="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$H=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Ce ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=($H?$H->fetch_row():null);if(!$J)return[];$I=[];list($I["partition_by"],$I["partition"],$I["partitions"])=$J;$vj=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Ce AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$I["partition_names"]=array_keys($vj);$I["partition_values"]=array_values($vj);return$I;}function
checkConstraints($R){$I=parent::checkConstraints($R);return($this->conn->flavor=='maria'?$I:array_map('stripslashes',$I));}function
hasCStyleEscapes(){static$hb;if($hb===null){$cm=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$hb=(strpos($cm,'NO_BACKSLASH_ESCAPES')===false);}return$hb;}function
hasEstimatedRows(){return
true;}function
isSystem($k,$L=""){return
information_schema($k,$L)||in_array($k,["mysql","sys"]);}function
lineComment(){return"#|-- ";}function
engines(){$I=[];foreach(get_rows("SHOW ENGINES")as$J){if(preg_match("~YES|DEFAULT~",$J["Support"]))$I[]=$J["Engine"];}return$I;}function
indexAlgorithms(array$_m){return(preg_match('~^(MEMORY|NDB)$~',$_m["Engine"])?["HASH","BTREE"]:[]);}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($se){$I=get_session("dbs");if($I===null){$G="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$im=microtime(true);$I=($se?slow_query($G):get_vals($G));if(microtime(true)-$im>0.1){restart_session();set_session("dbs",$I);stop_session();}}return$I;}function
limit($G,$Z,$z,$C=0,$tl=" "){return" $G$Z".($z?$tl."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$G,$Z,$tl="\n"){return
limit($G,$Z,1,0,$tl);}function
db_collation($k,array$Ib){static$I;if($I===null)$I=get_key_vals("SELECT SCHEMA_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA");return$I[$k];}function
logged_user(){return
get_val("SELECT CURRENT_USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$j){$I=[];foreach($j
as$k)$I[$k]=count(get_vals("SHOW TABLES IN ".idf_escape($k)));return$I;}function
table_status($B="",$Yd=false){$I=[];$G="SELECT ENGINE AS Engine, TABLE_NAME AS Name, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name");$L=[];foreach(($Yd?[]:get_rows($G))as$J)$L[$J["Name"]]=$J;$Xj=null;foreach(get_rows($Yd?$G:"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$J){$Yi=idx($L,$J["Name"]);if($Yi){if($J["Comment"]!==$Yi["Comment"]&&$J["Comment"]!==$Xj)$J["Error"]=$J["Comment"];$Xj=$J["Comment"];$J["Comment"]=$Yi["Comment"];$J["Engine"]=$Yi["Engine"];}if($J["Engine"]=="InnoDB")$J["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$J["Comment"]);if(!isset($J["Engine"]))$J["Comment"]="";if($B!="")$J["Name"]=$B;$I[$J["Name"]]=$J;}return$I;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
parse_type($Fe){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$Fe,$A);return[$A[1],$A[2],ltrim($A[3].$A[4])];}function
fields($R){$Ug=(connection()->flavor=='maria');$I=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$J){$n=$J["COLUMN_NAME"];$U=$J["COLUMN_TYPE"];$Ke=$J["GENERATION_EXPRESSION"];$Vd=$J["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Vd,$Je);list($En,$y,$Pn)=parse_type($U);$l=$J["COLUMN_DEFAULT"];$Dc=false;if($l!=""){$qk=$Ug;if($Ug)$Dc=!preg_match("~^('(?:[^'\\\\]|\\\\.|'')*'|NULL|[-+]?[\\d.]+)$~s",$l);elseif(preg_match('~text|json~',$En)||preg_match('~\bDEFAULT_GENERATED\b~',$Vd)){$l=stripslashes($l);$qk=preg_match("~^(_\\w+)?('(?:[^'\\\\]|\\\\.|'')*')$~s",$l,$A);if($qk)$l=$A[2];else$Dc=true;}elseif(preg_match("~^[bx]'[\\da-f]*'$~i",$l))$Dc=true;if($qk)$l=($l=="NULL"?null:(preg_match("~^'.*'$~s",$l)?stripslashes(idf_unescape($l)):$l));if(preg_match('~binary~',$En)&&preg_match("~^(?:0x(\\w*)|x'(\\w*)')$~",$l,$A)){$l=pack("H*",$A[1].idx($A,2));$Dc=false;}}$I[$n]=["field"=>$n,"full_type"=>$U,"type"=>$En,"length"=>$y,"unsigned"=>$Pn,"default"=>($Je?($Ug?$Ke:stripslashes($Ke)):$l),"default_sql"=>(!$Je&&$Dc),"null"=>($J["IS_NULLABLE"]=="YES"),"auto_increment"=>($Vd=="auto_increment"),"on_update"=>(preg_match('~\bon update current_timestamp\b~i',$Vd)?"CURRENT_TIMESTAMP":""),"collation"=>$J["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$J[PRIVILEGES],where,order")),"comment"=>$J["COLUMN_COMMENT"],"primary"=>($J["COLUMN_KEY"]=="PRI"),"generated"=>($Je[1]=="PERSISTENT"?"STORED":$Je[1]),];}return$I;}function
indexes($R,$h=null){$I=[];foreach(get_rows("SHOW INDEX FROM ".table($R),$h)as$J){$B=$J["Key_name"];$I[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($J["Index_type"]=="FULLTEXT"?"FULLTEXT":($J["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$J["Index_type"])?$J["Index_type"]:"INDEX"):"UNIQUE")));$I[$B]["columns"][]=$J["Column_name"];$I[$B]["lengths"][]=($J["Index_type"]=="SPATIAL"?null:$J["Sub_part"]);$I[$B]["descs"][]=null;$I[$B]["algorithm"]=$J["Index_type"];}return$I;}function
foreign_keys($R){static$Cj='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$I=[];$kc=get_val("SHOW CREATE TABLE ".table($R),1);if($kc){preg_match_all("~CONSTRAINT ($Cj) FOREIGN KEY ?\\(((?:$Cj,? ?)+)\\) REFERENCES ($Cj)(?:\\.($Cj))? \\(((?:$Cj,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$kc,$Xg,PREG_SET_ORDER);foreach($Xg
as$A){preg_match_all("~$Cj~",$A[2],$Ul);preg_match_all("~$Cj~",$A[5],$Rm);$I[idf_unescape($A[1])]=["db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$Ul[0]),"target"=>array_map('Adminer\idf_unescape',$Rm[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),];}}return$I;}function
view($B){return["select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1))];}function
collations(){$I=[];foreach(get_rows("SHOW COLLATION")as$J)$I[$J["Charset"]][]=$J["Collation"];ksort($I);foreach($I
as$x=>$X)sort($I[$x]);return$I;}function
information_schema($k,$L=""){return($k=="information_schema")||(min_version(5.5)&&$k=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($k,$Hb){return
queries("CREATE DATABASE ".idf_escape($k).($Hb?" COLLATE ".q($Hb):""));}function
drop_databases(array$j){$I=apply_queries("DROP DATABASE",$j,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$I;}function
rename_database($B,$Hb){$I=false;if(create_database($B,$Hb)){$T=[];$ro=[];foreach(tables_list()as$R=>$U){if($U=='VIEW')$ro[]=$R;else$T[]=$R;}$I=(!$T&&!$ro)||move_tables($T,$ro,$B);drop_databases($I?[DB]:[]);}return$I;}function
auto_increment(){$Oa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Oa="";break;}if($v["type"]=="PRIMARY")$Oa=" UNIQUE";}}return" AUTO_INCREMENT$Oa";}function
alter_table($R,$B,array$o,array$ue,$f,$xd,$Hb,$Na,$uj){$b=[];foreach($o
as$n){if($n[1]){$l=$n[1][3];if(preg_match('~ GENERATED~',$l)){$n[1][3]=(connection()->flavor=='maria'?"":$n[1][2]);$n[1][2]=$l;}$b[]=($R!=""?($n[0]!=""?"CHANGE ".idf_escape($n[0]):"ADD"):" ")." ".implode($n[1]).($R!=""?$n[2]:"");}else$b[]="DROP ".idf_escape($n[0]);}$b=array_merge($b,$ue);$P=($f!==null?" COMMENT=".q($f):"").($xd?" ENGINE=".q($xd):"").($Hb?" COLLATE ".q($Hb):"").($Na!=""?" AUTO_INCREMENT=$Na":"");if($uj){$vj=[];if($uj["partition_by"]=='RANGE'||$uj["partition_by"]=='LIST'){foreach($uj["partition_names"]as$x=>$X){$Y=$uj["partition_values"][$x];$vj[]="\n  PARTITION ".idf_escape($X)." VALUES ".($uj["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $uj[partition_by]($uj[partition])";if($vj)$P
.=" (".implode(",",$vj)."\n)";elseif($uj["partitions"])$P
.=" PARTITIONS ".(+$uj["partitions"]);}elseif($uj===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");if($R!=$B)$b[]="RENAME TO ".table($B);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$pb=[];foreach($b
as$X)$pb[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$pb));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$ro){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$ro)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$ro,$Rm){$Ek=[];foreach($T
as$R)$Ek[]=table($R)." TO ".idf_escape($Rm).".".table($R);if(!$Ek||queries("RENAME TABLE ".implode(", ",$Ek))){$Hc=[];foreach($ro
as$R)$Hc[table($R)]=view($R);connection()->select_db($Rm);$k=idf_escape(DB);foreach($Hc
as$B=>$qo){if(!queries("CREATE VIEW $B AS ".str_replace(" $k."," ",$qo["select"]))||!queries("DROP VIEW $k.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$ro,$Rm){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$B=($Rm==DB?table("copy_$R"):idf_escape($Rm).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($R))||!queries("INSERT INTO $B SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){$vn=$J["Trigger"];list($Hd,$ni)=trigger_event($J);if(!queries("CREATE TRIGGER ".($Rm==DB?idf_escape("copy_$vn"):idf_escape($Rm).".".idf_escape($vn))." $J[Timing] $Hd".($ni!=""?" $ni":"")." ON $B FOR EACH ROW\n$J[Statement];"))return
false;}}foreach($ro
as$R){$B=($Rm==DB?table("copy_$R"):idf_escape($Rm).".".table($R));$qo=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $qo[select]"))return
false;}return
true;}function
trigger_event(array$J){$Jd=explode(",",$J["Event"]);$I=[];foreach(["DELETE","INSERT","UPDATE"]as$Hd){if(in_array($Hd,$Jd))$I[]=$Hd;}$I=implode(" OR ",$I);if(in_array("UPDATE",$Jd)&&min_version('','12.0.1')&&preg_match('~\s(?:BEFORE|AFTER)\s+(.+?)\s+ON\s~is',get_val("SHOW CREATE TRIGGER ".idf_escape($J["Trigger"]),2),$A)&&preg_match('~\bOF\s+(.+)~is',$A[1],$ni))return["$I OF",$ni[1]];return[$I,""];}function
trigger($B,$R){if($B=="")return[];$K=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));$I=reset($K);if($I)list($I["Event"],$I["Of"])=trigger_event($I);return($I?:[]);}function
triggers($R){$I=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){list($Hd)=trigger_event($J);$I[$J["Trigger"]]=[$J["Timing"],$Hd];}return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>(min_version('','12.0.1')?["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",]:["INSERT","UPDATE","DELETE"]),"Type"=>["FOR EACH ROW"],];}function
routine($B,$U){$K=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, COLLATION_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND SPECIFIC_NAME = ".q($B)."
ORDER BY ORDINAL_POSITION");$o=[];foreach($K
as$J){$Fe=$J["DTD_IDENTIFIER"];list($En,$y,$Pn)=parse_type($Fe);$o[]=["field"=>$J["PARAMETER_NAME"],"type"=>$En,"length"=>$y,"unsigned"=>$Pn,"null"=>true,"full_type"=>$Fe,"inout"=>($U=="FUNCTION"?"":$J["PARAMETER_MODE"]),"collation"=>$J["COLLATION_NAME"],];}$I=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	ROUTINE_DEFINITION definition,
	LOWER(EXTERNAL_LANGUAGE) language,
	IF(DEFINER = CURRENT_USER(), '', DEFINER) definer,
	IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC', 'NOT DETERMINISTIC') is_deterministic,
	SQL_DATA_ACCESS data_access,
	CONCAT('SQL SECURITY ', SECURITY_TYPE) security
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND ROUTINE_NAME = ".q($B))->fetch_assoc();if(!$I)return[];if($I['language']!='javascript'&&preg_match('~^[^\'"]+(?=[\'"])~',$I['definition'],$A)){$i=get_val("SHOW CREATE $U ".idf_escape($B),2);$F=strpos($i,"\n$A[0]");if($F)$I['definition']=substr($i,$F+1);}$I['options']=["DEFINER"=>$I['definer'],"DETERMINISTIC"=>$I['is_deterministic'],"SQL_DATA_ACCESS"=>$I['data_access'],"SQL_SECURITY"=>$I['security'],"COMMENT"=>$I['comment'],];if($o&&$o[0]['field']=='')$I['returns']=array_shift($o);$I['fields']=$o;return$I;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return(min_version(9,99)?["sql"=>"sql","javascript"=>"js"]:[]);}function
routine_options($Rk){return["DEFINER"=>[],"DETERMINISTIC"=>["NOT DETERMINISTIC","DETERMINISTIC"],"SQL_DATA_ACCESS"=>["CONTAINS SQL","NO SQL","READS SQL DATA","MODIFIES SQL DATA"],"SQL_SECURITY"=>["SQL SECURITY DEFINER","SQL SECURITY INVOKER"],"COMMENT"=>[],];}function
routine_id($B,array$J){return
idf_escape($B);}function
last_id($H){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$g,$G){return$g->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$G);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$Na,$qm){$I=get_val("SHOW CREATE TABLE ".table($R),1);$I=preg_replace_callback('~(?<=^CREATE TABLE )`(?:[^`]|``)+`~',function()use($R){return
table($R);},$I);if(!$Na)$I=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$I);return$I;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($wc,$qm=""){$B=idf_escape($wc);$I="";if(preg_match('~CREATE~',$qm)&&($i=get_val("SHOW CREATE DATABASE $B",1))){set_utf8mb4($i);if($qm=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $B;\n";$I
.="$i;\n";}return$I."USE $B";}function
trigger_sql($R){$I="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$J){list($J["Event"],$J["Of"])=trigger_event($J);$I
.="\n".create_trigger(" ON ".table($J["Table"]),$J+["Type"=>"FOR EACH ROW"]).";\n";}return$I;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$n){return
driver()->convertColumn(idf_escape($n["field"]),$n);}function
unconvert_field(array$n,$I){if(preg_match("~binary~",$n["type"]))$I="UNHEX($I)";if($n["type"]=="bit")$I="CONVERT(b$I, UNSIGNED)";if($n["type"]=="vector")$I=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($I)";if(preg_match("~geom|point|linestring|polygon~",$n["type"])){$Tj=(min_version(8)?"ST_":"");$I=$Tj."GeomFromText($I, $Tj"."SRID(".idf_escape($n["field"])."))";}return$I;}function
support($Zd){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$Zd);}function
kill_process($t){return
queries("KILL ".number($t));}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($Ud=false){return[];}function
type_values($t){return"";}function
type_definition($t){return["kind"=>"","definition"=>""];}function
schemas(){return[];}function
get_schema(){return"";}function
set_schema($L,$h=null){return
true;}function
schema_comment($L){return"";}function
set_schema_comment($L,$f){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(isset($_GET["db"])?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));if(isset($_GET["manifest"])){header("Content-Type: application/manifest+json; charset=utf-8");header("Cache-Control: no-cache");echo
json_encode(adminer()->manifest(),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);exit;}function
page_header($dn,$m="",$db=[],$en="",$gi=false,$Zc=""){if($gi){header("HTTP/1.1 404 Not Found");$m=($m?:lang(106));}page_headers();if(is_ajax()&&$m){page_messages($m);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$fn=$dn.($en!=""?": $en":"");$gn=strip_tags($fn.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'',LANG,'\' dir=\'',lang(107),'\' class=\'',lang(107),' nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$gn,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.3.1+5c48c0d4"),'">
';$oc=adminer()->css();if(is_int(key($oc)))$oc=array_fill_keys($oc,'light');$af=in_array('light',$oc)||in_array('',$oc);$Ye=in_array('dark',$oc)||in_array('',$oc);$sc=($af?($Ye?null:false):($Ye?:null));$mh=" media='(prefers-color-scheme: dark)'";if($sc!==false)echo"<link rel='stylesheet'".($sc?"":$mh)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.3.1+5c48c0d4")."'>\n";echo"<meta name='color-scheme' content='".($sc===null?"light dark":($sc?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.3.1+5c48c0d4");if(adminer()->head($sc))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.3.1+5c48c0d4")."'>\n";if(adminer()->manifest())echo"<link rel='manifest' href='".h(preg_replace('~\?.*~','',ME)."?manifest=")."' crossorigin='use-credentials'>\n";foreach($oc
as$Wn=>$Bh){$c=($Bh=='dark'&&!$sc?$mh:($Bh=='light'&&$Ye?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$c href='".h($Wn)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape(lang(108))."';
const numberFormat = '".js_escape(lang(6))."';
const numberDigits = '".js_escape(lang(7))."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".lang(109)."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($db!==null){$lg=(class_exists('Adminer\Db')&&connection());$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb">'.($lg?jump_input():'').'<a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~&(db|ns)=[^&]*~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:lang(48));if($db===false)echo"$N\n";else{echo"<a href='".h($_.(DB!=""&&support("single_db")?"&db=":""))."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";$jl="";if(is_string($db)){$jl=$db;$db=[];}if($_GET["ns"]!=""||(DB!=""&&is_array($db))){$yc="$_&db=".url_escape(DB).(support("scheme")?"&ns=":"").(support("single_table")?"&select=":"");echo'<a href="'.h($yc.($_GET["ns"]==""?$jl:"")).'">'.h(DB).'</a> » ';}if(is_array($db)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1).$jl).'">'.h($_GET["ns"]).'</a> » ';foreach($db
as$x=>$X){$Kc=(is_array($X)?$X[1]:h($X));if($Kc!="")echo"<a href='".h(ME."$x=").url_escape(is_array($X)?$X[0]:$X)."'>$Kc</a> » ";}}echo"$dn\n";}if($lg){echo"<form id='jump-form'>\n";hidden_fields_get();echo(DB!=""?input_hidden("db",DB):""),(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""),"</form>\n",script("jumpSetup();");}}echo"<h2>$fn$Zc</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($m);adminer()->serviceWorker();$j=&get_session("dbs");if(DB!=""&&$j&&!in_array(DB,$j,true))$j=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();if($gi){page_footer($gi===true?"":$gi);exit;}}function
service_worker(){$Bk=has_passwords();$Eb=($Bk?"navigator.serviceWorker.register('".js_escape(preg_replace('~\?.*~','',ME)."?file=worker.js&version=6.3.1+5c48c0d4")."', {scope: location.pathname}).catch(() => {});":"navigator.serviceWorker.getRegistration().then(registration => registration && registration.unregister());
	caches.keys().then(keys => keys.forEach(key => key.startsWith('adminer-') && caches.delete(key)));");echo
script("if (navigator.serviceWorker) {\n\t$Eb\n}");}function
has_passwords(){foreach((array)$_SESSION["pwds"]as$Gl){foreach($Gl
as$go){foreach($go
as$E){if($E!==null)return
true;}}}return
false;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$nc){$ff=[];foreach($nc
as$x=>$X)$ff[]="$x $X";header("Content-Security-Policy: ".implode("; ",$ff));}adminer()->headers();}function
csp(){return[["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",],];}function
design_checksums(){$co=[];foreach(array_keys(adminer()->css())as$Wn)$co[preg_replace('~\?.*~','',$Wn)]=true;$I=[];foreach(["adminer.css","adminer-dark.css"]as$p){if($co[$p]&&file_exists($p)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($p),$A);$I[$p]=[(string)$A[1],Plugins::checksum($p)];}}return$I;}function
official_design_checksums(){return['adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','cpanel/adminer.css'=>'59ce604e','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'c9d1d11c','konya/adminer.css'=>'2b409696','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'d9169f12','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'a8babfc5','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'bded6cdf','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'81be9a85','rmsoft/adminer.css'=>'6cd4a237','rmsoft_blue-dark/adminer.css'=>'32102a8','rmsoft_blue/adminer.css'=>'7d8d5b18','win98/adminer.css'=>'d20077a7',];}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$fi;if(!$fi)$fi=base64_encode(rand_string());return$fi;}function
page_messages($m){$Vn=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$th=idx($_SESSION["messages"],$Vn);if($th){echo"<div class='message'>".implode("</div>\n<div class='message'>",$th)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$Vn]);}if($m)echo"<div class='error'>$m</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($Ah=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($Ah);echo"</div>\n";if($Ah!="auth")echo'<form method="post">
<p class="logout">
<span title="',lang(50),'">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'',lang(110),'\' id=\'logout\'>
',adminer()->donation()."\n",input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($Kh){while($Kh>=2147483648)$Kh-=4294967296;while($Kh<=-2147483649)$Kh+=4294967296;return(int)$Kh;}function
long2str(array$W,$to){$al='';foreach($W
as$X)$al
.=pack('V',$X);if($to)return
substr($al,0,end($W));return$al;}function
str2long($al,$to){$W=array_values(unpack('V*',str_pad($al,4*ceil(strlen($al)/4),"\0")));if($to)$W[]=strlen($al);return$W;}function
xxtea_mx($Eo,$Do,$tm,$ng){return
int32((($Eo>>5&0x7FFFFFF)^$Do<<2)+(($Do>>3&0x1FFFFFFF)^$Eo<<4))^int32(($tm^$Do)+($ng^$Eo));}function
encrypt_string($nm,$x){if($nm=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($nm,true);$Kh=count($W)-1;$Eo=$W[$Kh];$Do=$W[0];$jk=floor(6+52/($Kh+1));$tm=0;while($jk-->0){$tm=int32($tm+0x9E3779B9);$kd=$tm>>2&3;for($fj=0;$fj<$Kh;$fj++){$Do=$W[$fj+1];$Jh=xxtea_mx($Eo,$Do,$tm,$x[$fj&3^$kd]);$Eo=int32($W[$fj]+$Jh);$W[$fj]=$Eo;}$Do=$W[0];$Jh=xxtea_mx($Eo,$Do,$tm,$x[$fj&3^$kd]);$Eo=int32($W[$Kh]+$Jh);$W[$Kh]=$Eo;}return
long2str($W,false);}function
decrypt_string($nm,$x){if($nm=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($nm,false);$Kh=count($W)-1;$Eo=$W[$Kh];$Do=$W[0];$jk=floor(6+52/($Kh+1));$tm=int32($jk*0x9E3779B9);while($tm){$kd=$tm>>2&3;for($fj=$Kh;$fj>0;$fj--){$Eo=$W[$fj-1];$Jh=xxtea_mx($Eo,$Do,$tm,$x[$fj&3^$kd]);$Do=int32($W[$fj]-$Jh);$W[$fj]=$Do;}$Eo=$W[$Kh];$Jh=xxtea_mx($Eo,$Do,$tm,$x[$fj&3^$kd]);$Do=int32($W[0]-$Jh);$W[0]=$Do;$tm=int32($tm-0x9E3779B9);}return
long2str($W,true);}$Fj=[];if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$Fj[$x]=$X;}}function
add_invalid_login(){$Va=get_temp_dir()."/adminer-invalid";foreach(glob("$Va*")?:[$Va]as$p){$_e=file_open_lock($p);if($_e)break;}if(!$_e)$_e=file_open_lock("$Va-".rand_string());if(!$_e)return;$Zf=json_decode(stream_get_contents($_e),true);$an=time();if($Zf){foreach($Zf
as$ag=>$X){if($X[0]<$an)unset($Zf[$ag]);}}$Xf=&$Zf[adminer()->bruteForceKey()];if(!$Xf)$Xf=[$an+30*60,0];$Xf[1]++;file_write_unlock($_e,json_encode($Zf));}function
check_invalid_login(array&$Fj){$Zf=[];foreach(glob(get_temp_dir()."/adminer-invalid*")as$p){$_e=file_open_lock($p);if($_e){$Zf=json_decode(stream_get_contents($_e),true);file_unlock($_e);break;}}$x=adminer()->bruteForceKey();$Xf=idx($Zf,$x,[]);$ei=($Xf[1]>29?$Xf[0]-time():0);if($ei>0){$m=lang(111,ceil($ei/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$x==$_SERVER["REMOTE_ADDR"])$m
.='<br>'.lang(112,'<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($m,$Fj,false);}}function
password_required(){static$I;if($I===null){$I=(bool)get_session("password_required");if(!$I){$mc=adminer()->credentials();$I=!is_object(Driver::connect($mc[0],$mc[1],""));if($I)set_session("password_required",true);}}return$I;}function
require_password_link($E){$Fh="<a href='https://www.adminer.org/password/'".target_blank().">".lang(113)."</a>";if(!function_exists('password_hash'))return" $Fh";$Ij=($E!==null?$E:base64_encode(substr(hex2bin(rand_string()),0,12)));$ef=password_hash($Ij,PASSWORD_DEFAULT);$p="adminer-plugins.php";$Od=file_exists("adminer-plugins.php");if($Od)$Vf=($E!==null?lang(114,"<b>$p</b>"):lang(115,"<b>$p</b>","<b>$Ij</b>"));else{$p="<button name='password_less' value='".h($ef)."' class='link'>$p</button>";$Vf=($E!==null?lang(116,$p):lang(117,$p,"<b>$Ij</b>"));}$Lg="\t<a>new</a> Adminer\\Password(<span class='jush-apo'>'".h($ef)."'</span>),";$I="<p>$Vf
<pre><code class='jush'>".($Od?$Lg:"&lt;?php\n<a>return</a> <a>array</a>(\n$Lg\n);")."</code></pre>
<p>$Fh
";return" <a href='#password-less' class='toggle'>".lang(118)."</a>
<div id='password-less' class='hidden'>".($Od?$I:"<form method='post'>\n".$I.input_token()."</form>")."</div>";}if(preg_match('~^[-\w$./]+$~',$_POST["password_less"])&&verify_token()){header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=adminer-plugins.php");echo"<?php\nreturn array(\n\tnew Adminer\\Password('$_POST[password_less]'),\n);\n";exit;}$Ma=$_POST["auth"];if($Ma&&(!adminer()->verifyLoginToken()||verify_token())){session_regenerate_id();$oo=$Ma["driver"];$N=$Ma["server"];$V=$Ma["username"];$E=(string)$Ma["password"];$k=$Ma["db"];set_password($oo,$N,$V,$E);$_SESSION["db"][$oo][$N][$V][$k]=true;if($Ma["permanent"]){$x=implode("-",array_map('base64_encode',[$oo,$N,$V,$k]));$bk=adminer()->permanentLogin(true);$Fj[$x]="$x:".base64_encode($bk?encrypt_string($E,$bk):"");cookie("adminer_permanent",implode(" ",$Fj));}if(!array_diff(array_keys($_POST),["auth","token"])||$oo!=DRIVER||$N!=SERVER||$V!==$_GET["username"]||$k!=DB)redirect(auth_url($oo,$N,$V,$k));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){Driver::disconnect();foreach(["pwds","db","dbs","queries"]as$x)set_session($x,null);unset_permanent($Fj);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),lang(119).' '.lang(120," href='https://www.adminer.org/donation/'".target_blank()));}elseif($Fj&&!$_SESSION["pwds"]){session_regenerate_id();$bk=adminer()->permanentLogin();foreach($Fj
as$x=>$X){list(,$Ab)=explode(":",$X);list($oo,$N,$V,$k)=array_map('base64_decode',explode("-",$x));set_password($oo,$N,$V,decrypt_string(base64_decode($Ab),$bk));$_SESSION["db"][$oo][$N][$V][$k]=true;}}function
unset_permanent(array&$Fj){foreach($Fj
as$x=>$X){list($oo,$N,$V,$k)=array_map('base64_decode',explode("-",$x));if($oo==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$k==DB)unset($Fj[$x]);}cookie("adminer_permanent",implode(" ",$Fj));}function
auth_error($m,array&$Fj,$Yf=true){$Hl=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Hl]||$_GET[$Hl])&&!$_SESSION["token"])$m=lang(121);elseif($Yf&&($E=get_password())!==null){restart_session();add_invalid_login();if($E===false)$m
.=($m?'<br>':'').lang(122,target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);unset_permanent($Fj);}}if(!$_COOKIE[$Hl]&&$_GET[$Hl]&&ini_bool("session.use_only_cookies"))$m=lang(123);$kj=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$kj["lifetime"]);if(!is_string($_SESSION["token"]))$_SESSION["token"]=rand_string();page_header(lang(52),$m,null);echo"<form method='post'>\n","<div>";if($_SERVER["HTTP_SEC_FETCH_SITE"]=="same-origin"&&hidden_fields($_POST,["auth","token"]))echo"<p class='message'>".lang(124)."\n";echo
input_token(),"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Fj);page_header(lang(125),lang(126,implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$g='';if(isset($_GET["username"])&&is_string(get_password())){check_invalid_login($Fj);$mc=adminer()->credentials();$g=Driver::connect($mc[0],$mc[1],$mc[2]);if(is_object($g)){Db::$instance=$g;Driver::$instance=new
Driver($g);if($g->flavor)save_settings(["vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)]);}}$Sg=null;if(!is_object($g)||($Sg=adminer()->login($_GET["username"],get_password()))!==true){$m=(is_string($g)?nl_br(h($g)):(is_string($Sg)?$Sg:lang(35))).(preg_match('~^ | $~',get_password())?'<br>'.lang(127):'');auth_error($m,$Fj);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header(lang(110),lang(128));page_footer("db");exit;}if(!is_string($_SESSION["token"]))$_SESSION["token"]=rand_string();stop_session(true);$m='';if($_POST){if(!verify_token()){header("HTTP/1.1 403 Forbidden");$m=lang(128).' '.lang(129);}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){header("HTTP/1.1 413 Content Too Large");$m=lang(130,"<b>post_max_size</b>");if(isset($_GET["sql"]))$m
.=' '.lang(131);}function
print_select_result($H,$h=null,array$Ri=[],&$z=0,&$ld=false){$Ng=[];$w=[];$e=[];$T=[];$Yj=[];$pd=[];$Fn=[];$I=[];$Ch=$ld;$ld=false;for($s=0;(!$z||$s<$z)&&is_array($J=$H->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'".($Ch?on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown'):"").">\n","<thead><tr>";for($jg=0;$jg<count($J);$jg++){$n=$H->fetch_field();$B=$n->name;$R=(isset($n->table)?$n->table:"");$Qi=(isset($n->orgtable)?$n->orgtable:"");$Pi=(isset($n->orgname)?$n->orgname:$B);$En=driver()->typeName($n);if($Ri&&JUSH=="sql")$Ng[$jg]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($Qi!=""){$wa=($R!=""?$R:$Qi);if($R!="")$I[$R]=$Qi;if(!isset($w[$wa])){if(!isset($Yj[$Qi])){$Yj[$Qi]=[];foreach(indexes($Qi,$h)as$v){if($v["type"]=="PRIMARY"){$Yj[$Qi]=array_flip($v["columns"]);break;}}}$T[$wa]=$Qi;$w[$wa]=$Yj[$Qi];$e[$wa]=$Yj[$Qi];}if(isset($e[$wa][$Pi])){unset($e[$wa][$Pi]);$w[$wa][$Pi]=$jg;$Ng[$jg]=$wa;}elseif($Ch&&isset($n->orgname)&&$n->db==DB&&!is_blob(["type"=>$En]))$pd[$jg]=[$wa,$Pi,preg_match('~text|json|lob~',$En)];}$Fn[$jg]=$En;echo"<th title='".h(trim(($Qi!=""?"$Qi.$Pi":($n->name!=$Pi?$Pi:""))." ".$En))."'>".h($B).($Ri?doc_link(['sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",]):"");}foreach($pd
as$jg=>$mb){if($e[$mb[0]])unset($pd[$jg]);}echo"<tbody>\n";}$xf=[];foreach($w
as$wa=>$v){if($v&&!$e[$wa]){$u="";foreach($v
as$Fb=>$jg){if($J[$jg]===null){$u=null;break;}$u
.="&where[".url_escape(bracket_escape($Fb))."]=".url_escape($J[$jg]);}$xf[$wa]=$u;}}echo"<tr>";foreach($J
as$x=>$X){$_="";if(isset($Ng[$x])){if($Ri&&JUSH=="sql"){$R=$J[array_search("table=",$Ng)];$_=ME.$Ng[$x].url_escape($Ri[$R]!=""?$Ri[$R]:$R);}elseif(idx($xf,$Ng[$x])!==null)$_=ME."edit=".url_escape($T[$Ng[$x]]).$xf[$Ng[$x]];}$sf=select_value($X,$_,['type'=>(preg_match('~binary~',$Fn[$x])?'blob':$Fn[$x])],null);$c="";$mb=idx($pd,$x);if($mb&&idx($xf,$mb[0])!==null&&is_utf8($X)){$ld=true;$c=" data-name='".h("val[".bracket_escape($T[$mb[0]])."][".bracket_escape(substr($xf[$mb[0]],1))."][".bracket_escape($mb[1])."]")."' data-text='".($mb[2]?1:0)."'".(html_entity_decode(strip_tags($sf),ENT_QUOTES,"UTF-8")!==$X?" data-value='".h($X)."'":"");}echo"<td".(preg_match(number_type(),$Fn[$x])?" class='number'":"")."$c>$sf";}}$z=$s;echo($s?"</table>\n</div>":"<p class='message'>".lang(22))."\n";return$I;}function
textarea($B,$Y,$K=10,$Jb=80,$mg=JUSH,$Cd=0){echo"<textarea name='".h($B)."' rows='$K' cols='$Jb' class='sqlarea jush-".h($mg)."' spellcheck='false' wrap='off'".($Cd?" data-error-line='$Cd'":"").">\n";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($c,array$Ki,$Y="",$Gj=""){if($Ki&&$Y!=""&&!isset($Ki[$Y]))$Ki=[$Y=>$Y]+$Ki;$Qm=($Ki?"select":"input");return"<$Qm$c".($Ki?"><option value=''>$Gj".optionlist($Ki,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Gj'>");}function
json_row($x,$X=null,$Gd=true){static$me=true;if($me)echo"{";if($x!=""){echo($me?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?($Gd?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$me=false;}else{echo"\n}\n";$me=true;}}function
flat_collations(){$Ib=collations();return(is_array(reset($Ib))?call_user_func_array('array_merge',array_values($Ib)):$Ib);}function
edit_type($x,array$n,array$Ib,array$we=[],array$Wd=[]){$U=(string)$n["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($U&&!array_key_exists($U,driver()->types())&&!isset($we[$U])&&!in_array($U,$Wd))$Wd[]=$U;$om=driver()->structuredTypes();if($we)$om[lang(132)]=$we;echo
optionlist(array_merge($Wd,$om),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($n["length"])."' size='3'".(!$n["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($Ib?"<input list='collations' name='".h($x)."[collation]'".option_types($U,'('.text_type().')$')." value='".h($n["collation"])."' placeholder='(".lang(133).")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".option_types($U,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$n["unsigned"]).'</select>':''),(isset($n['on_update'])?"<select name='".h($x)."[on_update]'".option_types($U,'timestamp|datetime').'>'.optionlist([""=>"(".lang(134).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$n["on_update"])?"CURRENT_TIMESTAMP":$n["on_update"])).'</select>':''),($we?"<select name='".h($x)."[on_delete]'".option_types($U,'`')."><option value=''>(".lang(135).")".optionlist(explode("|",driver()->onActions),$n["on_delete"])."</select> ":" ");}function
option_types($U,$Fn){return" data-types='".h($Fn)."'".(preg_match("~$Fn~",$U)?"":" class='hidden'");}function
process_length($y){if(JUSH=="mssql"&&preg_match('~^\s*\(?\s*max\s*\)?\s*$~i',$y))return"(max)";if(JUSH=="oracle"&&preg_match('~^\s*\(?\s*(\d+)\s+(CHAR|BYTE)\s*\)?\s*$~i',$y,$A))return"($A[1] ".strtoupper($A[2]).")";if(JUSH=="oracle"&&preg_match('~^\s*\(?\s*\*\s*,\s*(-?\d+)\s*\)?\s*$~',$y,$A))return"(*,$A[1])";$_d=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$_d(?:\\s*,\\s*$_d)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$_d~",$y,$Xg)?"(".implode(",",$Xg[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_in($X){$X=preg_replace('~^\s*\(\s*(.*?)\s*\)\s*$~s','\1',$X);$qk="'(?:[^']|'')*+'";$ig=(preg_match("~^\\s*$qk(?:\\s*,\\s*$qk)*+\\s*\$~",$X)&&preg_match_all("~$qk~",$X,$Xg)?array_map('Adminer\idf_unescape',$Xg[0]):array_map('trim',explode(",",$X)));return"(".implode(", ",array_map('Adminer\q',$ig)).")";}function
process_between($X){$qk="('(?:[^']|'')*+')";$sk=(preg_match("~^\\s*$qk\\s+AND\\s+$qk\\s*\$~i",$X,$A)?[idf_unescape($A[1]),idf_unescape($A[2])]:preg_split('~\s+AND\s+~i',trim($X)));return(count($sk)==2?q($sk[0])." AND ".q($sk[1]):"NULL AND NULL");}function
process_type(array$n,$Gb="COLLATE"){return" ".(is_user_type($n["type"])?idf_escape($n["type"]):$n["type"]).process_length($n["length"]).(preg_match(number_type(),$n["type"])&&in_array($n["unsigned"],driver()->unsigned)?" $n[unsigned]":"").(preg_match('~'.text_type().'~',$n["type"])&&$n["collation"]?" $Gb ".(JUSH=="mssql"&&preg_match('~^\w+$~',$n["collation"])?$n["collation"]:q($n["collation"])):"");}function
process_field(array$n,array$Bn){return[idf_escape(trim($n["field"])),process_type($Bn),($n["null"]?" NULL":" NOT NULL"),default_value($n),(preg_match('~timestamp|datetime~',$n["type"])&&$n["on_update"]?" ON UPDATE $n[on_update]".process_length($n["length"]):""),(support("comment")&&$n["comment"]!=""?" COMMENT ".q($n["comment"]):""),($n["auto_increment"]?auto_increment():null),];}function
default_value(array$n){if($n["default"]===null)return"";$l=str_replace("\r","",$n["default"]);$Je=$n["generated"];$Q=!preg_match('~]$~',$n["length"])&&(preg_match('~char|binary|text|clob|json|enum|set|String~',$n["type"])||driver()->enumLength($n));$lj=(JUSH=="sqlite"||(JUSH=="sql"&&$n["default_sql"]&&!preg_match("~^(current_timestamp(\\(\\d*\\))?|[bx]'[\\da-f]*')$~i",$l)));return(in_array($Je,driver()->generated)?(JUSH=="mssql"?" AS ($l)".($Je=="VIRTUAL"?"":" $Je"):" GENERATED ALWAYS AS ($l) $Je"):(preg_match('~^GENERATED ~i',$l)?" $l":" DEFAULT ".(!$n["default_sql"]&&($Q||preg_match('~^(?![a-z])~i',$l))?(JUSH=="sql"&&preg_match('~text|json~',$n["type"])?"(".q($l).")":q($l)):($lj?"($l)":$l))));}function
edit_fields(array$o,array$Ib,$U="TABLE",array$we=[]){$o=array_values($o);$Cc=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$Ob=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?lang(136):lang(137)),"<th id='label-type'>".lang(65)."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<th id='label-length'>".lang(138),"<th>".lang(139);if($U=="TABLE")echo"<th id='label-null'>NULL\n","<th><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".lang(67)."'>A_I</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'cockroach'=>"serial",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",]),"<th id='label-default'$Cc>".lang(68),(support("comment")?"<th id='label-comment'$Ob>".lang(66):"");$Ag=!support("move_col");echo"<td>".icon("plus","add[".($Ag?count($o):0)."]","+",lang(140),($Ag?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($o
as$s=>$n){$s++;$Si=$n[($_POST?"orig":"field")];$Rc=(isset($_POST["add"][$s-1])||(isset($n["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$Si=="");echo"<tr".($Rc?"":" hidden").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$n["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",lang(141))." ":"");if($Rc)echo"<input name='fields[$s][field]' value='".h($n["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$Si);edit_type("fields[$s]",$n,$Ib,$we);if($U=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$s][null]",1,$n["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($n["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Cc>".html_select("fields[$s][generated]",array_merge(["","DEFAULT","DEFAULT SQL"],driver()->generated),$n["generated"],"","label-default")." ";$c=" name='fields[$s][default]' aria-labelledby='label-default'";$Y=h($n["default"]);echo(preg_match('~\n~',$n["default"])?"<textarea$c rows='2' cols='30' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$c value='$Y'>");if(support("comment")){$c=" name='fields[$s][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$Ob>".adminer()->commentInput('COLUMN',$c,$n["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$s]","+",lang(140))." ":""),($Si==""||support("drop_col")?icon("cross","drop_col[$s]","x",lang(142)):"");}}function
process_fields(array&$o){if($_POST["add"]){$o=array_values($o);array_splice($o,key($_POST["add"]),0,[[]]);}return$_POST["add"]||$_POST["drop_col"];}function
drop_create($ed,$i,$gd,$Wm,$id,$Rg,$sh,$qh,$rh,$wi,$Xh){if($_POST["drop"])query_redirect($ed,$Rg,$sh);elseif($wi=="")query_redirect($i,$Rg,$rh);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($Rg,$qh,queries($ed)&&queries($i)&&driver()->commit());driver()->rollback();}elseif($wi!=$Xh){$lc=queries($i);queries_redirect($Rg,$qh,$lc&&queries($ed));if($lc&&$gd)queries($gd);}else
queries_redirect($Rg,$qh,queries($Wm)&&queries($id)&&queries($ed)&&queries($i));}function
create_trigger($_i,array$J){$cn=" $J[Timing] $J[Event]".(preg_match('~ OF~',$J["Event"])?" $J[Of]":"");return"CREATE TRIGGER ".idf_escape($J["Trigger"]).(JUSH=="mssql"?$_i.$cn:$cn.$_i).preg_replace('~[\s;]+$~',''," $J[Type]\n$J[Statement]").";";}function
q_dollar($Q){$Ic='$$';while(strpos($Q.$Ic,$Ic)!=strlen($Q))$Ic='$_'.substr($Ic,1);return$Ic.$Q.$Ic;}function
routine_collate($Hb){static$sb=[];if($Hb&&!$sb){foreach(collations()as$rb=>$lo){foreach((array)$lo
as$X)$sb[$X]=$rb;}}return($sb[$Hb]?"CHARACTER SET ".q($sb[$Hb])." ":"")."COLLATE";}function
create_routine($Rk,array$J){$O=[];$o=$J["fields"];ksort($o);foreach($o
as$n){if($n["field"]!=""){$Qf=(preg_match("~^(".driver()->inout.")\$~",$n["inout"])?$n["inout"]:"");$O[]="\n  ".(JUSH=="mssql"?"@$n[field]".process_type($n).($Qf?" $Qf":""):($Qf?"$Qf ":"").idf_escape($n["field"]).process_type($n,routine_collate($n["collation"])));}}$Fc="";$Ki=[];foreach(routine_options($Rk)as$x=>$mo){$Y=idx($J["options"],$x,"");if($x=="DEFINER")$Fc=($Y?" $x=".implode("@",array_map('Adminer\q',explode("@",$Y,2))):"");elseif(!$mo){if($Y!="")$Ki[]="$x ".q($Y);}elseif($Y!=reset($mo)&&in_array($Y,$mo))$Ki[]=$Y;}$zg=$J["language"];$Gc=preg_replace('~[\s;]+$~','',$J["definition"]);$ad=(JUSH=="pgsql"||($zg&&$zg!="sql"));$jj=($O?implode(",",$O)."\n":"");return"CREATE$Fc $Rk ".table(trim($J["name"])).(JUSH=="mssql"&&$Rk=="PROCEDURE"?rtrim($jj):" ($jj)").($Rk=="FUNCTION"?"\nRETURNS".process_type($J["returns"],routine_collate($J["returns"]["collation"])):"").($zg?" LANGUAGE $zg":"").($Ki?"\n".implode(" ",$Ki):"").($ad?" AS ".q_dollar("\n".trim($Gc)."\n"):(JUSH=="mssql"?"\nAS":"")."\n$Gc;");}function
remove_definer($G){$Fc=implode("@",array_map('Adminer\idf_escape',explode("@",logged_user(),2)));return
preg_replace('(^([A-Z =]+) DEFINER='.preg_quote($Fc).')','\1',$G);}function
object_name($U,$R,array$e){return
str_replace(["{table}","{columns}"],[$R,implode("_",$e)],adminer()->namePattern($U));}function
format_foreign_key(array$q,$B=""){$k=$q["db"];$hi=$q["ns"];return($B!=""?" CONSTRAINT ".idf_escape($B):"")." FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$q["source"])).") REFERENCES ".($k!=""&&$k!=$_GET["db"]?idf_escape($k).".":"").($hi!=""&&$hi!=$_GET["ns"]?idf_escape($hi).".":"").idf_escape($q["table"])." (".implode(", ",array_map('Adminer\idf_escape',$q["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$q["on_delete"])?" ON DELETE $q[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$q["on_update"])?" ON UPDATE $q[on_update]":"").($q["deferrable"]?" $q[deferrable]":"");}function
tar_file($p,$hn){$I=pack("a100a8a8a8a12a12",$p,644,0,0,decoct($hn->size),decoct(time()));$yb=8*32;for($s=0;$s<strlen($I);$s++)$yb+=ord($I[$s]);$I
.=sprintf("%06o\0 ",$yb);echo$I,str_repeat("\0",512-strlen($I));$hn->send();echo
str_repeat("\0",511-($hn->size+511)%512);}function
doc_version(){$El=connection()->server_info;if(JUSH=='oracle'){preg_match('~(?:.* |^)(\d+)\.\d+\.\d+\.\d+\.\d+~s',$El,$A);return($A[1]>=18?$A[1]:"19");}$Ak=(JUSH=='sql'||connection()->flavor=='cockroach'?'~^\d+\.\d+~':'~^\d\.?\d~');$po=(preg_match($Ak,$El,$A)?$A[0]:"");if(JUSH=='mssql')return($po>=15?"sql-server-ver$po":($po==12?"azuresqldb-current":"sql-server-2017"));return$po;}function
doc_link(array$Bj,$Xm="📖"){$po=doc_version();$Xn=['sql'=>"https://dev.mysql.com/doc/refman/$po/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$po)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://docs.oracle.com/en/database/oracle/oracle-database/$po/",];if(connection()->flavor=='maria'){$Xn['sql']="https://mariadb.com/kb/en/";$Bj['sql']=($Bj['mariadb']?:str_replace(".html","/",$Bj['sql']));}if(connection()->flavor=='cockroach'&&$Bj['cockroach']){$Xn['pgsql']="https://docs.cockroachlabs.com/docs/v$po/";$Bj['pgsql']=$Bj['cockroach'];}return($Bj[JUSH]?" <a href='".h($Xn[JUSH].$Bj[JUSH].(JUSH=='mssql'?"?view=$po":""))."'".target_blank()." class='doc' title='".lang(143)."'>$Xm</a>":"");}function
db_size($k){if(!connection()->select_db($k))return"?";$I=0;foreach(table_status()as$S)$I+=$S["Data_length"]+$S["Index_length"];return
adminer()->formatSizeValue($I);}function
set_utf8mb4($i){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$i)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'),null,301);if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!="")page_header(lang(17).": ".h(DB),adminer()->error(),true,"","db");else{if(!isset($_GET["db"])&&support("single_db")){$j=adminer()->databases();if($j)redirect(ME."db=".url_escape($j[0]));}if($_POST["db"]&&!$m)queries_redirect(substr(ME,0,-1),lang(144),drop_databases($_POST["db"]));page_header(lang(145),$m,false);echo"<p class='links'>\n";foreach(['database'=>lang(146),'privileges'=>lang(86),'processlist'=>lang(95),'variables'=>lang(96),'status'=>lang(97),]as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".lang(147,get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".lang(148,"<b>".h(logged_user())."</b>")."\n";$j=adminer()->databases();$ke=$_GET["filter"];if($ke!=""){foreach($j
as$x=>$k){if(stripos($k,$ke)===false)unset($j[$x]);}}if($j){$el=support("scheme");echo"<form method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".lang(17).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(149)."</a>":"")."<th>".lang(150)."<th>".lang(151)."<th>".lang(152)." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".lang(153)."</a>"."<tbody>\n";$j=($_GET["dbsize"]?count_tables($j):array_flip($j));foreach($j
as$k=>$T){$Qk=h(preg_replace('~&db=[^&]*~','',ME))."db=".url_escape($k);$t=h("Db-".$k);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$k,in_array($k,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$Qk' id='$t'>".h($k)."</a>";$Hb=h(db_collation($k,[]));echo"<td>".(support("database")?"<a href='$Qk".($el?"&amp;ns=":"")."&amp;database=' title='".lang(82)."'>$Hb</a>":$Hb),"<td align='right'><a href='$Qk&amp;schema=' id='tables-".h($k)."' title='".lang(85)."'>".($_GET["dbsize"]?format_number($T):"?")."</a>","<td align='right' id='size-".h($k)."'>".($_GET["dbsize"]?db_size($k):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".lang(154)." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".lang(155)."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$qa=adminer();$Kj=($qa
instanceof
Plugins?$qa->plugins:[]);$dd=($qa
instanceof
Plugins?$qa->drivers:[]);$Oc=design_checksums();if($Kj||$dd||$Oc){$zb=($qa
instanceof
Plugins?$qa->checksums():[]);$pi=Plugins::officialChecksums();$Sn=function($Wn){return" (<a href='$Wn'".target_blank()." class='update'>".VERSION."</a>)";};$Jj=function($ge)use($zb,$pi,$Sn){return($zb[$ge]&&$pi[$ge]&&$zb[$ge]!==$pi[$ge]?$Sn("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".lang(156)."</h3>\n<ul>\n";foreach($Kj
as$Hj){$zk=new
\ReflectionObject($Hj);$Lc=Plugins::describe($Hj);echo"<li><b>".h(Plugins::pluginName($Hj))."</b>".($Lc!=""?": $Lc":"").$Jj(basename((string)$zk->getFileName(),'.php'))."\n";}foreach($dd
as$t=>$B)echo"<li><b>".h($t)."</b>: ".h($B).$Jj(basename((string)$qa->driverFiles[$t],'.php'))."\n";if($Oc){$ri=official_design_checksums();foreach($Oc
as$p=>$Nc){list($B,$yb)=$Nc;$qi=$ri["$B/$p"];echo"<li><b>".h($p)."</b>".h($B?": $B":"").($qi&&$qi!==$yb?$Sn("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~&db=[^&]+~','\0&ns='.url_escape(get_schema()),relative_uri()));if(!set_schema($_GET["ns"]))page_header(lang(16).h(": $_GET[ns]"),adminer()->error(),true,"","ns");}}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($cc){$this->size+=strlen($cc);fwrite($this->handler,$cc);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if($_GET["select"]!=""&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$o=fields($a);header("Content-Type: application/octet-stream");$mo=array_merge((array)$_GET["where"],(array)$_GET["val"]);header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$mo)).".".friendly_url($_GET["field"]));$M=[idf_escape($_GET["field"])];$H=driver()->select($a,$M,[where($_GET,$o)],$M);$J=($H?$H->fetch_row():[]);echo
driver()->value($J[0],$o[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$o=fields($a);if(!$o)$m=adminer()->error();$S=table_status1($a);$B=adminer()->tableName($S);$m=$m?:h($S["Error"]);page_header(($o&&is_view($S)?$S['Engine']=='materialized view'?lang(157):lang(158):lang(159)).": ".($B!=""?$B:h($a)),$m,[],"",!$o,($o?doc_link([JUSH=>driver()->tableHelp($a,is_view($S))]):""));$Pk=[];foreach($o
as$x=>$n)$Pk+=$n["privileges"];adminer()->selectLinks($S,(isset($Pk["insert"])||!support("table")?"":null));$f=$S["Comment"];if($f!="")echo"<p class='nowrap'>".lang(66).": ".adminer()->commentValue('TABLE',$f)."\n";if($o)adminer()->tableStructurePrint($o,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$J){$_=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($J["ns"]),ME);echo"<li><a href='".h($_."table=".url_escape($J["table"]))."'>".($J["ns"]!=$_GET["ns"]?"<b>".h($J["ns"])."</b>.":"").h($J["table"])."</a>";}echo"</ul>\n";}$Of=driver()->inheritsFrom($a);if($Of){echo"<h3>".lang(160)."</h3>\n";tables_links($Of);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<div>\n","<h3 id='indexes'>".lang(161)."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);if(driver()->supportsAlterIndex($S))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.lang(162)."</a>\n";echo"</div>\n";}if(!is_view($S)&&driver()->supportsAlterTable($S)){if(fk_support($S)){echo"<div>\n","<h3 id='foreign-keys'>".lang(132)."</h3>\n";$we=foreign_keys($a);$Aa=function_exists('Adminer\alter_table');if($we){echo"<table>\n","<thead><tr><th>".lang(163)."<th>".lang(164)."<th>".lang(135)."<th>".lang(134).($Aa?"<td class='hover'>":"")."<tbody>\n";foreach($we
as$B=>$q){echo"<tr".(!is_int($B)?" title='".h($B)."'":"").">","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$q["source"]))."</i>";$_=($q["db"]!=""?preg_replace('~&db=[^&]*~',"&db=".url_escape($q["db"]),ME):($q["ns"]!=""?preg_replace('~&ns=[^&]*~',"&ns=".url_escape($q["ns"]),ME):ME));echo"<td><a href='".h($_."table=".url_escape($q["table"]))."'>".($q["db"]!=""&&$q["db"]!=DB?"<b>".h($q["db"])."</b>.":"").($q["ns"]!=""&&$q["ns"]!=$_GET["ns"]?"<b>".h($q["ns"])."</b>.":"").h($q["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$q["target"]))."</i>)","<td>".h($q["on_delete"]),"<td>".h($q["on_update"]),($Aa?'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($B)).'">'.lang(165).'</a>':''),"\n";}echo"</table>\n";}echo($Aa?'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.lang(166)."</a>\n":''),"</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".lang(167)."</h3>\n";$ub=driver()->checkConstraints($a);if($ub){echo"<table>\n";foreach($ub
as$x=>$X)echo"<tr".($x!=$X?" title='".h($x)."'":"").">","<td><code class='sql jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($X)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($x))."'>".lang(165)."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.lang(168)."</a>\n","</div>\n";}}if(support(is_view($S)?"view_trigger":"trigger")&&driver()->supportsAlterTable($S)){echo"<div>\n","<h3 id='triggers'>".lang(169)."</h3>\n";$zn=triggers($a);if($zn){echo"<table>\n";foreach($zn
as$x=>$X){echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($x))."'>".lang(165)."</a>";$Rk=$X[2];if($Rk){$Tk=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($Rk["ns"]),ME).'function='.url_escape($Rk["function"]).'&name='.url_escape($Rk["name"]);echo", <a href='".h($Tk)."' title='".h($Rk["name"])."'>".lang(170)."</a>";}echo"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.lang(171)."</a>\n","</div>\n";}$Kl=driver()->shadowTables($a);if($Kl){echo"<h3 id='shadow-tables'>".lang(172)."</h3>\n";tables_links($Kl);}$Nf=driver()->inheritedTables($a);if($Nf){echo"<h3 id='partitions'>".lang(173)."</h3>\n";$pj=driver()->partitionsInfo($a);if($pj)echo"<p><code class='sql jush-".JUSH."'>BY ".h("$pj[partition_by]($pj[partition])")."</code>\n";tables_links($Nf);}}elseif(isset($_GET["schema"])){page_header(lang(85),"",[],h(DB.($_GET["ns"]?".$_GET[ns]":"")));function
schema_column($R,array$yk,array&$e){if(!isset($e[$R])){$e[$R]=0;foreach((array)idx($yk,$R)as$B=>$_k){if($B!=$R)$e[$R]=max($e[$R],schema_column($B,$yk,$e)+1);}}return$e[$R];}function
type_class($U){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}$Hm=[];$Jm=[];$Im=[];$de=[];$da=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$da,$Xg,PREG_SET_ORDER);foreach($Xg
as$s=>$A){$Hm[$A[1]]=[(float)$A[2],(float)$A[3]];$Jm[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$L=[];$yk=[];$we=[];$za=driver()->allFields();$kf=[];$Km=[];foreach(table_status('',true)as$R=>$S){if(!is_view($S)){if(adminer()->tableName($S)!=""&&!$S["dependent"])$Km[$R]=$S;else$kf[$R]=true;}}foreach($Km
as$R=>$S){$F=0;$L[$R]["fields"]=[];foreach($za[$R]as$n){$F+=1.25;$de[$R][$n["field"]]=$F;$L[$R]["fields"][$n["field"]]=$n;}foreach(adminer()->foreignKeys($R)as$X){if($X["db"]==""&&$X["ns"]==""&&!$kf[$X["table"]]){$we[$R][]=$X;$yk[$X["table"]][$R]=[];}}}$e=[];$Ne=[];$Bo=[];$Ue=[];foreach(array_keys($L)as$B)schema_column($B,$yk,$e);arsort($e);foreach($e
as$B=>$d){$zh=null;foreach((array)idx($we,$B)as$X){if($X["table"]!=$B&&$L[$X["table"]])$zh=($zh===null?$e[$X["table"]]:min($zh,$e[$X["table"]]));}$e[$B]=max($d,(int)$zh-1);}$Mm=[];foreach($L
as$B=>$R){$d=$e[$B];$Ne[$d][]=$B;$Zm=.75*strlen($B);foreach($R["fields"]as$n)$Zm=max($Zm,.65*strlen($n["field"]));$Mm[$B]=ceil($Zm)+1;$Bo[$d]=max(idx($Bo,$d,0),$Mm[$B]);}foreach($we
as$B=>$lo){foreach($lo
as$X){$Te=$e[$B]+(idx($e,$X["table"],$e[$B])>$e[$B]?1:0);$Ue[$Te]=idx($Ue,$Te,0)+1;}}ksort($Ne);$if=0;$Ao=0;$Kb=0;$Wj=null;$Dm=[];foreach($Ne
as$d=>$T){if($Wj!==null){$Kb=round($Kb+$Bo[$Wj]+1.7+idx($Ue,$d,0)*.1,1);$Ni=[];foreach($T
as$B){$tm=0;$jc=0;$Sh=array_keys((array)idx($yk,$B));foreach((array)idx($we,$B)as$X)$Sh[]=$X["table"];foreach($Sh
as$Lh){if($L[$Lh]&&$e[$Lh]<$d){$tm+=$L[$Lh]["pos"][0];$jc++;}}$Ni[$B]=($jc?$tm/$jc:$if);}asort($Ni);$T=array_keys($Ni);}$kn=0;foreach($T
as$B){$F=1.25*count($L[$B]["fields"]);$L[$B]["pos"]=($Hm[$B]?:[$kn,$Kb]);$Dm[$B]=$L[$B]["pos"][1];$kn+=2.5+$F;$if=max($if,$L[$B]["pos"][0]+2.5+$F);$Ao=max($Ao,round($L[$B]["pos"][1]+$Bo[$d],1));if(!$Hm[$B])$Im[]="\n\t'".js_escape($B)."': [ ".$L[$B]["pos"][0].", ".$L[$B]["pos"][1]." ]";}$Wj=$d;}$Eg=[];$Wa=[];foreach($we
as$B=>$lo){foreach($lo
as$X){$Sm=idx($Dm,$X["table"],$Dm[$B]);$Vl=$Dm[$B]+$Bo[$e[$B]];$Ok=($Sm-1>$Vl);$Cg=($Ok?$Vl+1:min($Dm[$B],$Sm)-1);$Va=idx($Wa,(string)$Cg,0);$Wa[(string)$Cg]=$Va+1;$Cg=round($Ok?min($Cg+$Va*.1,$Sm-1):$Cg-$Va*.1,1);while($Eg[(string)$Cg])$Cg-=.0001;$L[$B]["references"][$X["table"]][(string)$Cg]=[$X["source"],$X["target"]];$yk[$X["table"]][$B][(string)$Cg]=$X["target"];$Eg[(string)$Cg]=true;}}echo'<div id="schema" style="height: ',$if,'em; width: ',$Ao,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$Jm)."\n",'};
const tablePosDefault = {',implode(",",$Im)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$if,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($L
as$B=>$R){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em; width: ".$Mm[$B]."em;'>",'<a href="'.h(ME).'table='.url_escape($B).'"><b>'.h($B)."</b></a>";foreach($R["fields"]as$n){$X='<span'.type_class($n["type"]).' title="'.h($n["type"].($n["length"]?"($n[length])":"").($n["null"]?" NULL":'')).'">'.h($n["field"]).'</span>';echo"<br>".($n["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$Tm=>$_k){foreach($_k
as$Cg=>$vk){$Dg=$Cg-$R["pos"][1];$qm=($Dg>0?"left: 100%; width: calc($Dg"."em - 100%)":"left: $Dg"."em");$Ao=($Dg>0?"100%":(-$Dg)."em");$s=0;foreach($vk[0]as$Ul)echo"\n<div class='references' title='".h($Tm)."' id='refs$Cg-".($s++)."' style='$qm"."; top: ".$de[$B][$Ul]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: $Ao;'></div></div>";}}foreach((array)$yk[$B]as$Tm=>$_k){foreach($_k
as$Cg=>$Um){$Dg=$Cg-$R["pos"][1];$s=0;foreach($Um
as$Rm)echo"\n<div class='references arrow' title='".h($Tm)."' id='refd$Cg-".($s++)."' style='left: $Dg"."em; top: ".$de[$B][$Rm]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Dg)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($L
as$B=>$R){foreach((array)$R["references"]as$Tm=>$_k){if($L[$Tm]){foreach($_k
as$Cg=>$vk){$_h=$if;$fh=-10;foreach($vk[0]as$x=>$Ul){$Mj=$R["pos"][0]+$de[$B][$Ul];$Nj=$L[$Tm]["pos"][0]+$de[$Tm][$vk[1][$x]];$_h=min($_h,$Mj,$Nj);$fh=max($fh,$Mj,$Nj);}echo"<div class='references' id='refl$Cg' style='left: $Cg"."em; top: $_h"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($fh-$_h)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($da)),'" id="schema-link">',lang(174),'</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$m){$l=["auto_increment"=>''];foreach(["type","routine","event","trigger"]as$vm){if(support($vm))$l[$vm."s"]='';}save_settings(array_intersect_key($_POST+$l,array_flip(["output","format","db_style","schema_style","table_style","data_style"])+$l),"adminer_export");$ya=(DB==""||$_GET["ns"]==="");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Sd=dump_headers((count($T)==1?key($T):DB),($ya||count($T)>1));$fg=preg_match('~sql~',$_POST["format"]);if($fg){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$qm=$_POST["db_style"];$j=[DB];if(DB==""){$j=$_POST["databases"];if(is_string($j))$j=explode("\n",rtrim(str_replace("\r","",$j),"\n"));}foreach((array)$j
as$k){adminer()->dumpDatabase($k);if(connection()->select_db($k)){if($fg&&$qm)echo
use_sql($k,$qm).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:adminer()->schemas()))as$L){if($L!=""){if(DB==""&&information_schema(DB,$L))continue;set_schema($L);}if($fg&&$_POST["schema_style"]&&function_exists('Adminer\use_schema_sql'))echo
use_schema_sql($_GET["ns"],$_POST["schema_style"]).";\n\n";$mm=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):[]);$Rd=[];$vc=[];foreach($mm
as$B=>$S){if($ya||in_array($B,(array)$_POST["tables"]))$Rd[$B]=$S;if($ya||in_array($B,(array)$_POST["data"]))$vc[$B]=$S;}if($fg){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($Rd);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$_n=[];foreach($vc
as$B=>$S){if(!is_view($S)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($Rd[$B])))$_n[]=$B;}echo
truncate_all_sql($_n);}$bj="";if($_POST["types"]){foreach(types()as$t=>$U){$Gc=type_definition($t);$li=($Gc["kind"]=='d'?"DOMAIN":"TYPE");if($Gc["definition"])$bj
.=($qm!='DROP+CREATE'?"DROP $li IF EXISTS ".table($U).";;\n":"")."CREATE $li ".table($U)." $Gc[definition];\n\n";else$bj
.="-- Could not export type ".preg_replace('~[\r\n]+~',' ',$U)."\n\n";}}if($_POST["routines"]){foreach(routines()as$J){$B=$J["ROUTINE_NAME"];$Rk=$J["ROUTINE_TYPE"];$Mf=routine($J["SPECIFIC_NAME"],$Rk);if($Mf["definition"]!=""){$i=create_routine($Rk,["name"=>$B]+$Mf);set_utf8mb4($i);$bj
.=($qm!='DROP+CREATE'?"DROP $Rk IF EXISTS ".table($B).";;\n":"")."$i;\n\n";}else$bj
.="-- Could not export ".strtolower($Rk)." ".preg_replace('~[\r\n]+~',' ',$B)."\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$J){$i=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($J["Name"]),3));set_utf8mb4($i);$bj
.=($qm!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($J["Name"]).";;\n":"")."$i;;\n\n";}}echo($bj&&JUSH=='sql'?"DELIMITER ;;\n\n$bj"."DELIMITER ;\n\n":$bj);}if($_POST["table_style"]||$_POST["data_style"]){$ro=[];foreach($mm
as$B=>$S){$R=array_key_exists($B,$Rd);$tc=array_key_exists($B,$vc);if($R||$tc){$hn=null;if($Sd=="tar"){$hn=new
TmpFile;ob_start([$hn,'write'],1e5);}adminer()->dumpTable($B,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$ro[]=$B;elseif($tc){$o=fields($B);$M=["*"];$gc=convert_fields($o,$o);if($gc)$M[]=substr($gc,2);adminer()->dumpData($B,$_POST["data_style"],"",$M);}if($fg&&$_POST["triggers"]&&$R&&($zn=trigger_sql($B)))echo"\nDELIMITER ;;\n$zn\nDELIMITER ;\n";if($Sd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$k/")."$B.csv",$hn);}elseif($fg)echo"\n";}}if($fg&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($Rd
as$B=>$S){if(!is_view($S))echo
foreign_keys_sql($B);}}if($fg){foreach($ro
as$qo)adminer()->dumpTable($qo,$_POST["table_style"],1);}if($Sd=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header(lang(91),$m,($_GET["export"]!=""?["table"=>$_GET["export"]]:[]),h(DB));echo'
<form method="post">
<table class="layout">
';$zc=['','USE','DROP+CREATE','CREATE'];$cl=(JUSH=="mssql"?['','DROP+CREATE','CREATE']:$zc);$Lm=['','DROP+CREATE','CREATE'];$uc=['','TRUNCATE+INSERT','INSERT'];if(JUSH=="sql")$uc[]='INSERT+UPDATE';$J=get_settings("adminer_export");if(!$J)$J=["output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"schema_style"=>"","table_style"=>"DROP+CREATE","data_style"=>"INSERT"];echo"<tr><th>".lang(175)."<td>".html_radios("output",adminer()->dumpOutput(),$J["output"])."\n","<tr><th>".lang(176)."<td>".html_radios("format",adminer()->dumpFormat(),$J["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".lang(17)."<td>".html_select('db_style',$zc,$J["db_style"]).(support("type")?checkbox("types",1,$J["types"],lang(0)):"").(support("routine")?checkbox("routines",1,$J["routines"],lang(87)):"").(support("event")?checkbox("events",1,$J["events"],lang(89)):"")),(function_exists('Adminer\use_schema_sql')?"<tr><th>".lang(16)."<td>".html_select('schema_style',$cl,$J["schema_style"]):""),"<tr><th>".lang(151)."<td>".html_select('table_style',$Lm,$J["table_style"]).checkbox("auto_increment",1,$J["auto_increment"],lang(67)).(support("trigger")?checkbox("triggers",1,$J["triggers"],lang(169)):""),"<tr><th>".lang(177)."<td>".html_select('data_style',$uc,$J["data_style"]),'</table>
';adminer()->dumpPrint();echo'<p><input type=\'submit\' value=\'',lang(91),'\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$Uj=[];if($_GET["ns"]===""&&support("scheme")){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".lang(178)."'".on('click','formCheck','^schemas\[').">".lang(16)."</label>","<tbody>\n";foreach(adminer()->schemas()as$L){if(!information_schema(DB,$L))echo"<tr><td>".checkbox("schemas[]",$L,true,$L,"","block")."\n";}}elseif(DB!=""){$wb=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$wb class='jsonly' title='".lang(178)."'".on('click','formCheck','^tables\[').">".lang(159)."</label>","<th style='text-align: right;'><label class='block'>".lang(177)."<input type='checkbox' id='check-data'$wb class='jsonly' title='".lang(178)."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$ro="";$Om=tables_list();foreach($Om
as$B=>$U){$Tj=preg_replace('~_.*~','',$B);$wb=($a==""||$a==(substr($a,-1)=="%"?"$Tj%":$B));$ak="<tr><td>".checkbox("tables[]",$B,$wb,$B,"","block");if($U!==null&&!preg_match('~table~i',$U))$ro
.="$ak\n";else
echo"$ak<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$wb)."</label>\n";$Uj[$Tj]++;}echo$ro;if($Om)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$j=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($j?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".lang(178)."'".on('click','formCheck','^databases\[').">":"").lang(17)."</label>","<tbody>\n";if($j){foreach($j
as$k){if(!information_schema($k)){$Tj=preg_replace('~_.*~','',$k);echo"<tr><td>".checkbox("databases[]",$k,$a==""||$a=="$Tj%",$k,"","block")."\n";$Uj[$Tj]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$me=true;foreach($Uj
as$x=>$X){if($x!=""&&$X>1){echo($me?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$x%")."'>".h($x)."</a>";$me=false;}}}elseif(isset($_GET["privileges"])){page_header(lang(86));echo'<p class="links"><a href="'.h(ME).'user=">'.lang(179)."</a>";$H=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$Le=$H;if(!$H)$H=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($Le?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".lang(50)."<th>".lang(48)."<td class='hover'><tbody>\n";while($J=$H->fetch_assoc())echo'<tr><td>'.h($J["User"]),"<td>".h($J["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($J["User"]).'&host='.url_escape($J["Host"])).'">'.lang(20)."</a>\n";if(!$Le||DB!="")echo"<tr><td><input name='user' autocapitalize='off'>","<td><input name='host' value='localhost' autocapitalize='off'>","<td class='hover'><input type='submit' value='".lang(20)."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){$Mg=driver()->lineComment();$Wl="(?:\\s|\xEF\xBB\xBF|/\\*[\s\S]*?\\*/|(?:$Mg)[^\n]*\n?|--\r?\n)";$fm="~^(?:$Wl|;)*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is";if(!$m&&$_POST["export"]&&JUSH=="sqlite"&&preg_match($fm,$_POST["query"],$A)!==0)$m=lang(180,preg_match('~ATTACH~i',$A[1])?'ATTACH':'VACUUM INTO');if(!$m&&$_POST["export"]){save_settings(["output"=>$_POST["output"],"format"=>$_POST["format"]],"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}if(!$m&&$_POST["val"]){$ta=0;$rm=true;$nb=[];$Zk=0;foreach($_POST["val"]as$K)$Zk+=count($K);$Ya=$Zk>1&&driver()->begin();foreach($_POST["val"]as$Bm=>$K){$R=bracket_escape($Bm,true);$o=fields($R);$Cm=indexes($R);foreach($K
as$u=>$J){parse_str(bracket_escape($u,true),$Z);$Jn=[];foreach($Z["where"]as$x=>$X)$Jn[bracket_escape($x,true)]=$X;if(!$o||$Z["null"]||array_diff_key($Jn,$o)||!unique_array($Jn,$Cm)){$rm=false;break
2;}$O=[];$M=[];foreach($J
as$og=>$X){$x=bracket_escape($og,true);$n=idx($o,$x);if(!$n){$rm=false;break
3;}$O[idf_escape($x)]=(preg_match('~char|text~',$n["type"])||$X!=""?adminer()->processInput($n,$X):"NULL");$M[$og]=$x;}$pk=where($Z,$o);if(!driver()->update($R,$O," WHERE $pk",0," ")){$rm=false;break
2;}$ta+=connection()->affected_rows;$e=[];foreach($M
as$x)$e[]=idf_escape($x);$Tn=driver()->select($R,$e,[$pk],$e);$Zh=($Tn?$Tn->fetch_row():[]);$jg=0;foreach($M
as$og=>$x){$n=$o[$x];$pm=['type'=>(preg_match('~binary~',$n["type"])?'blob':$n["type"])];$nb["val[$Bm][$u][$og]"]=select_value(idx($Zh,$jg++),"",$pm,null);}}}if($Ya&&$rm)$rm=driver()->commit();queries_redirect(null,lang(181,$ta),$rm);if($Ya&&!$rm)driver()->rollback();page_headers();page_messages($m);foreach($nb
as$B=>$X)echo"<div data-name='".h($B)."' hidden>$X</div>\n";exit;}restart_session();$of=&get_session("queries");$nf=&$of[DB];if(!$m&&$_POST["clear"]){$nf=[];redirect(remove_from_uri("history"));}stop_session();$ra=get_settings("adminer_import");if($_POST&&$ra)save_settings($ra,"adminer_import");page_header((isset($_GET["import"])?lang(90):lang(79)),$m);$Cd=0;function
query_error_line($Xm,$C,$G,$Lg){return
substr_count(substr($Xm,0,$C),"\n")+($Lg?:substr_count(substr($G,0,strspn($G," \t\r\n")),"\n")+1);}if(!$m&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$Ic=driver()->delimiter;$_e=false;if(!isset($_GET["import"]))$G=$_POST["query"];elseif($_POST["webfile"]){$am=adminer()->importServerPath();$_e=@fopen((file_exists($am)?$am:"compress.zlib://$am.gz"),"rb");$G=($_e?fread($_e,1e6):false);}else$G=get_file("sql_file",true,$Ic);if(is_string($G)){if(($nh=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($nh,strval(2*strlen($G)+memory_get_usage()+8e6)));if($G!=""&&strlen($G)<1e6){$jk=$G.(preg_match("~$Ic\\s*\$~",$G)?"":$Ic);if(!$nf||first(end($nf))!=$jk){restart_session();$nf[]=[$jk,time()];set_session("queries",$of);stop_session();}}$C=0;$nk=0;$ud=true;$ic=false;$uh=false;$h=connect();if($h&&DB!=""){$h->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$h);}$Mb=0;$Ed=[];$mj='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Mg.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$mn=microtime(true);while($G!=""){if(!$C&&preg_match("~^$Wl*+DELIMITER\\s+(\\S+)~i",$G,$A)){$Ic=preg_quote($A[1]);$G=substr($G,strlen($A[0]));$nk+=strlen($A[0]);}elseif(!$C&&JUSH=='pgsql'&&preg_match("~^($Wl*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$G,$A)){$Ic="\n\\\\\\.\r?\n";$ic=true;$C=strlen($A[0]);}elseif(!$C&&JUSH=='pgsql'&&preg_match("~^$Wl*+\\\\connect[ \t][^\n]*~",$G,$A)){$Ic="\n";$uh=true;$C=strlen($A[0]);}else{preg_match("($Ic\\s*|$mj)",$G,$A,PREG_OFFSET_CAPTURE,$C);list($ye,$F)=$A[0];if(!$ye&&$_e&&!feof($_e))$G
.=fread($_e,1e5);else{if(!$ye&&rtrim($G)=="")break;$C=$F+strlen($ye);if($ye&&!preg_match("(^$Ic)",$ye)){$ib=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($F>0&&strtolower($G[$F-1])=="e"));$Cj=($ye=='/*'?'\*/':($ye=='['?']':(preg_match("~^(?:$Mg)~",$ye)?"\n":preg_quote($ye).($ib?'|\\\\.':''))));while(preg_match("($Cj|\$)s",$G,$A,PREG_OFFSET_CAPTURE,$C)){$al=$A[0][0];if(!$al&&$_e&&!feof($_e))$G
.=fread($_e,1e5);else{$C=$A[0][1]+strlen($al);if(!$al||$al[0]!="\\")break;}}}else{$jk=substr($G,0,$F+($ic?3:0));$G=substr($G,$C);$kk=$nk;$nk+=$C;$C=0;if($ic||$uh){$Ic=driver()->delimiter;$ic=false;$uh=false;}$Eb="<code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($jk)."</code>";if(preg_match("~^$Wl*+\$~",$jk)&&!preg_match('~/\*M?!~',$jk)){echo($_POST["only_errors"]?"":"<pre>$Eb</pre>\n");continue;}$ud=false;$Mb++;$ak="<pre id='sql-$Mb'>$Eb</pre>\n";if(JUSH=="sqlite"&&preg_match($fm,$jk,$A)!==0){echo$ak,"<p class='error'>".lang(180,preg_match('~ATTACH~i',$A[1])?'ATTACH':'VACUUM INTO')."\n";$Ed[]=" <a href='#sql-$Mb'>$Mb</a>";if(!$Cd&&!isset($_GET["import"]))$Cd=query_error_line($_POST["query"],$kk,$jk,0);if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$ak;ob_flush();flush();}$im=microtime(true);if(JUSH=="pgsql"&&preg_match("~^$Wl*+\\\\connect\\s+(\"(?:[^\"]|\"\")+\"|[^\\s\";]+)\\s*;*\\s*\$~",$jk,$A)){$k=idf_unescape($A[1]);if(connection()->select_db($k)){if($h)$h->select_db($k);echo($_POST["only_errors"]?"":"<p class='message'>".lang(17).": ".h($k)." <span class='time'>(".format_time($im).")</span>\n");continue;}}elseif(connection()->multi_query($jk)&&$h&&preg_match("~^$Wl*+USE\\b~i",$jk))$h->query($jk);do{$H=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$ak:""),"<p class='error'>".lang(182).(connection()->errno?" (".connection()->errno.")":"").": ".adminer()->error()."\n";$Ed[]=" <a href='#sql-$Mb'>$Mb</a>";if(!$Cd&&!isset($_GET["import"]))$Cd=query_error_line($_POST["query"],$kk,$jk,driver()->errorLine($jk));if($_POST["error_stops"])break
2;}else{$_=ME."sql=".url_escape(trim($jk));$an=" <span class='time'>(".format_time($im).")</span>".(strlen($_)<adminer()->sqlUrlLength()-100?" <a href='".h($_)."'>".lang(20)."</a>":"");$ta=connection()->affected_rows;$vo=($_POST["only_errors"]?"":driver()->warnings());$wo="warnings-$Mb";if($vo)$an
.=", <a href='#$wo' class='toggle'>".lang(62)."</a>";$Pd="";$Qd="explain-$Mb";if(is_object($H)){$z=$_POST["limit"];$ji=$z;$ld=!$_POST["only_errors"];if($ld)echo"<form method='post'>\n";$Ri=print_select_result($H,$h,[],$ji,$ld);if(!$_POST["only_errors"]){$ji=max($H->num_rows,$ji);echo"<p class='sql-footer'>".($ji?($z&&$ji>$z?lang(183,$z):"").lang(184,$ji):""),$an;if($h&&preg_match("~^($Wl|\\()*+SELECT\\b~i",$jk)&&($Pd=adminer()->explain($h,$jk,$Ri))!="")echo", <a href='#$Qd' class='toggle'>Explain</a>";if($ld)echo", <input type='submit' name='save' value='".lang(24)."' class='jsonly' disabled"." title='".lang(185)."'".on('click','sqlSave',lang(27)).">";$t="export-$Mb";echo", <a href='#$t' class='toggle'>".lang(91)."</a><span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ra["output"])." ".html_select("format",adminer()->dumpFormat(),$ra["format"]).input_hidden("query",$jk)."<input type='submit' name='export' value='".lang(91)."'".($z?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$Wl*+(CREATE|DROP|ALTER)$Wl++(DATABASE|SCHEMA)\\b~i",$jk)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang(186,$ta)."$an\n";}echo($vo?"<div id='$wo' class='hidden'>\n$vo</div>\n":""),($Pd!=""?"<div id='$Qd' class='hidden explain'>\n$Pd</div>\n":"");}$im=microtime(true);}while(connection()->next_result());}}}}}if($ud)echo"<p class='message'>".lang(187)."\n";else{$Cf=connection()->inTransaction();driver()->rollback();if($Cf)echo"<pre><code class='jush-".JUSH."'>ROLLBACK".(JUSH=="mssql"?" TRANSACTION":"")." -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang(188,$Mb-count($Ed))," <span class='time'>(".format_time($mn).")</span>\n";elseif($Ed&&$Mb>1)echo"<p class='error'>".lang(182).": ".implode("",$Ed)."\n";}}else
echo"<p class='error'>".upload_error($G)."\n";}echo'
<form method="post" enctype="multipart/form-data" id="form"';$Un="";if(!isset($_GET["import"]))echo
on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"),adminer()->sqlUrlLength());else
echo
on_upload_progress($Un);echo'>
';$Md="<input type='submit' value='".lang(189)."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$jk=$_GET["sql"];if($_POST)$jk=$_POST["query"];elseif($_GET["history"]=="all")$jk=$nf;elseif($_GET["history"]!="")$jk=idx($nf[$_GET["history"]],0);echo"<p>";textarea("query",$jk,20,80,JUSH,$Cd);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$Md\n",lang(190).": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$Ve=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".lang(191)."</legend><div>",($Un?input_hidden(ini_get("session.upload_progress.name"),$Un):""),"SQL$Ve: ".file_input(" name='sql_file[]' multiple","\n$Md"),($Un?" <progress class='jsonly hidden' max='1' value='0'></progress>":""),"</div></fieldset>\n";$_f=adminer()->importServerPath();if($_f)echo"<fieldset><legend>".lang(192)."</legend><div>",lang(193,"<code>".h($_f)."$Ve</code>")," <input type='submit' name='webfile' value='".lang(194)."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(195))."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(196))."\n",input_token();if(!isset($_GET["import"])&&$nf){print_fieldset("history",lang(197),$_GET["history"]!="");for($X=end($nf);$X;$X=prev($nf)){$x=key($nf);list($jk,$an,$qd)=$X;echo'<div><a href="'.h(ME."sql=&history=$x").'" class="hover">'.lang(20)."</a>"." <span class='time' title='".@date('Y-m-d',$an)."'>".@date("H:i:s",$an)."</span>"." <code class='sql jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$Mg).*~m",'',$jk))),80,"</code>").($qd?" <span class='time'>($qd)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".lang(198)."'>\n","<a href='".h(ME."sql=&history=all")."'>".lang(199)."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$o=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$o):""):where($_GET,$o));$Rn=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($o
as$B=>$n){if((!$Rn&&!isset($n["privileges"]["insert"]))||adminer()->fieldName($n)=="")unset($o[$B]);}if($_POST&&!$m&&!isset($_GET["select"])){$Rg=relative_uri((string)$_POST["referer"]);if($_POST["insert"])$Rg=($Rn?null:relative_uri());elseif(!preg_match('~^.+&select=.+$~',$Rg))$Rg=ME."select=".url_escape($a);$w=indexes($a);$Kn=unique_array($_GET["where"],$w);$pk="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Rg,lang(200),driver()->delete($a,$pk,$Kn?0:1));else{$O=[];foreach($o
as$B=>$n){$X=process_input($n);if($X!==false&&$X!==null)$O[idf_escape($B)]=$X;}if($Rn){if(!$O)redirect($Rg);queries_redirect($Rg,lang(201),driver()->update($a,$O,$pk,$Kn?0:1));if(is_ajax()){page_headers();page_messages($m);exit;}}else{$H=driver()->insert($a,$O);$Bg=($H?last_id($H):0);queries_redirect($Rg,lang(202,($Bg?" $Bg":"")),$H);}}}$J=null;$G="";$an="";if($Z){$M=[];$ol=["*"];foreach($o
as$B=>$n){if(isset($n["privileges"]["select"])){$Ha=($_POST["clone"]&&$n["auto_increment"]?"''":convert_field($n));$d=($Ha?"$Ha AS ":"").idf_escape($B);$M[]=$d;if($Ha)$ol[]=$d;}}$J=[];if(!support("table")){$M=["*"];$ol=$M;}if($M){$im=microtime(true);$H=driver()->select($a,$M,[$Z],$M,[],(isset($_GET["select"])?2:1));$G=str_replace("SELECT ".implode(", ",$M),"SELECT ".implode(", ",$ol),driver()->query);$an=format_time($im);if(!$H)$m=adminer()->error();else{$J=$H->fetch_assoc();if(!$J)$J=false;}if(isset($_GET["select"])&&(!$J||$H->fetch_assoc()))$J=null;}}if(!$o&&driver()->primary!=""){if(!$Z){$H=driver()->select($a,["*"],[],["*"]);$J=($H?$H->fetch_assoc():false);if(!$J)$J=[driver()->primary=>""];}if($J){foreach($J
as$x=>$X){if(!$Z)$J[$x]=null;$o[$x]=["field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary)];}}}if($_POST["save"]){$Oj=[];foreach((array)$_POST["fields"]as$x=>$X)$Oj[bracket_escape($x,true)]=$X;$J=$Oj+($J?$J:[]);}edit_form($a,$o,$J,$Rn,$m,$G,$an);}elseif(isset($_GET["create"])){function
referencable_primary($rl){$I=[];foreach(table_status('',true)as$Fm=>$R){if($Fm!=$rl&&!$R["dependent"]&&fk_support($R)){foreach(fields($Fm)as$n){if($n["primary"]){if($I[$Fm]){unset($I[$Fm]);break;}$I[$Fm]=$n;}}}}return$I;}$a=$_GET["create"];$rj=driver()->partitionBy;$wj=($rj&&$a!=""?driver()->partitionsInfo($a):[]);$xk=referencable_primary($a);$we=[];foreach($xk
as$Fm=>$n)$we[str_replace("`","``",$Fm)."`".str_replace("`","``",$n["field"])]=$Fm;$Vi=[];$S=[];$gi=false;if($a!=""){$Vi=fields($a);$S=table_status1($a);$gi=(count($S)<2);}$Aa=($a==""||driver()->supportsAlterTable($S));$J=$_POST;$J["fields"]=(array)$J["fields"];if($J["auto_increment_col"])$J["fields"][$J["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$m)save_settings(["comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]]);if($_POST&&!process_fields($J["fields"])&&!$m){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(203),drop_tables([$a]));else{$o=[];$za=[];$Yn=false;$ue=[];$Ui=reset($Vi);$va=" FIRST";foreach($J["fields"]as$n){$q=$we[$n["type"]];$Bn=($q!==null?$xk[$q]:$n);if($n["field"]!=""){if(!$n["generated"])$n["default"]=null;$n["default_sql"]=($n["generated"]=="DEFAULT SQL");$fk=process_field($n,$Bn);$za[]=[$n["orig"],$fk,$va];if(!$Ui||$fk!==process_field($Ui,$Ui)){$o[]=[$n["orig"],$fk,$va];if($n["orig"]!=""||$va||(JUSH=="sqlite"&&$n["comment"]!=""))$Yn=true;}if($q!==null)$ue[idf_escape($n["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$we[$n["type"]],'source'=>[$n["field"]],'target'=>[$Bn["field"]],'on_delete'=>$n["on_delete"],],object_name("FOREIGN",trim($J["name"]),[$n["field"]]));$va=" AFTER ".idf_escape($n["field"]);}elseif($n["orig"]!=""){$Yn=true;$o[]=[$n["orig"]];}if($n["orig"]!=""){$Ui=next($Vi);if(!$Ui)$va="";}}$uj=[];if(in_array($J["partition_by"],$rj)){foreach($J
as$x=>$X){if(preg_match('~^partition~',$x))$uj[$x]=$X;}foreach($uj["partition_names"]as$x=>$B){if($B==""){unset($uj["partition_names"][$x]);unset($uj["partition_values"][$x]);}}$uj["partition_names"]=array_values($uj["partition_names"]);$uj["partition_values"]=array_values($uj["partition_values"]);if($uj==$wj)$uj=[];}elseif(preg_match("~partitioned~",$S["Create_options"]))$uj=null;$ph=lang(204);if($a==""){cookie("adminer_engine",$J["Engine"]);$ph=lang(205);}$B=trim($J["name"]);$Rg=ME.(support("table")?"table=":"select=").url_escape($B);$f=($J["Comment"]!=$S["Comment"]?$J["Comment"]:null);$xd=($J["Engine"]&&$J["Engine"]!=$S["Engine"]?$J["Engine"]:"");$H=alter_table($a,$B,(JUSH=="sqlite"&&($Yn||$ue||$f!==null||$xd)?$za:$o),$ue,$f,$xd,($J["Collation"]&&$J["Collation"]!=$S["Collation"]?$J["Collation"]:""),($J["Auto_increment"]!=""?number($J["Auto_increment"]):""),$uj);if($H&&!Queries::$queries&&$a!=""&&!$o&&!$ue)redirect($Rg);queries_redirect($Rg,$ph,$H);}}$km=($a!=""?"alter":"create");page_header(($a!=""?lang(60):lang(92)),$m,["table"=>$a],h($a),$gi,doc_link(['sql'=>"$km-table.html",'mariadb'=>($a!=""?"$km-table":""),'pgsql'=>"sql-$km"."table.html",'cockroach'=>"$km-table",'mssql'=>"t-sql/statements/$km-table-transact-sql",'sqlite'=>"lang_{$km}table.html",'oracle'=>"sqlrf/".strtoupper($km)."-TABLE.html",]));if(!$_POST){$Fn=driver()->types();$J=["Engine"=>$_COOKIE["adminer_engine"],"fields"=>[["field"=>"","type"=>(isset($Fn["int"])?"int":(isset($Fn["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$J=$S;$J["name"]=$a;$J["fields"]=[];if(!$_GET["auto_increment"])$J["Auto_increment"]="";foreach($Vi
as$n){if($n["generated"])$n["default"]=ltrim($n["default"]);$n["generated"]=$n["generated"]?:(isset($n["default"])?($n["default_sql"]?"DEFAULT SQL":"DEFAULT"):"");$J["fields"][]=$n;}if($rj){$J+=$wj;$J["partition_names"][]="";$J["partition_values"][]="";}}}$Ib=flat_collations();$yd=driver()->engines();foreach($yd
as$xd){if(!strcasecmp($xd,$J["Engine"])){$J["Engine"]=$xd;break;}}$ah=max_input_vars(12,20);if($ah){$kf=(count($J["fields"])>$ah?"":" hidden");echo"<p".($kf?" id='max-fields' data-columns='$ah'":"")." class='error$kf'>".max_input_vars_error()."\n";}echo'
<form method="post" id="form">
<p>
';if(support("columns")||$a==""){echo
lang(206).": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($J["name"])."' autocapitalize='off'>\n",(!$Aa?h($S["Engine"])."\n":($yd?html_select("Engine",[""=>"(".lang(207).")"]+$yd,$J["Engine"],on('change','helpClose').on_help_value())."\n":""));if($Ib)echo"<datalist id='collations'>".optionlist($Ib)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($J["Collation"])."' placeholder='(".lang(133).")'>\n");echo"<input type='submit' value='".lang(24)."'>\n";}if(support("columns")&&$Aa){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($J["fields"],$Ib,"TABLE",$we);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",lang(67).": <input type='number' name='Auto_increment' class='size' value='".h($J["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),lang(208),on('click','columnShowClick',6),"jsonly");$Pb=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$Pb,lang(66),on('click','editingCommentsClick',true),"jsonly").' ';$c=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($Pb?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$c,$J["Comment"]);}echo'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$a)),'>
';if($rj&&(JUSH=='sql'||$a=="")){$sj=preg_match('~RANGE|LIST~',$J["partition_by"]);print_fieldset("partition",lang(210),$J["partition_by"]);echo"<p>".html_select("partition_by",array_merge([""],$rj),$J["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($J["partition"])."'>)\n",lang(211).": <input type='number' name='partitions' class='size".($sj||!$J["partition_by"]?" hidden":"")."' value='".h($J["partitions"])."'>\n","<table id='partition-table'".($sj?"":" class='hidden'").">\n","<thead><tr><th>".lang(212)."<th>".lang(213)."<tbody>\n";foreach($J["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off"'.($x==count($J["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($J["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$If=["PRIMARY","UNIQUE","INDEX"];$S=table_status1($a,true);$Ff=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$If[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$If[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$S["Engine"]))$If[]="VECTOR";$w=indexes($a);$o=fields($a);$Yj=[];if(JUSH=="mongo"){$Yj=$w["_id_"];unset($If[0]);unset($w["_id_"]);}$J=$_POST;if($J)save_settings(["index_options"=>$J["options"]]);if($_POST&&!$m&&!$_POST["add"]&&!$_POST["drop_col"]){$b=[];foreach($J["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$If)){$e=[];$Ig=[];$Mc=[];$Ei=[];$Gf=(support("partial_indexes")?$v["partial"]:"");$Ef=(in_array($v["algorithm"],$Ff)?$v["algorithm"]:"");$O=[];ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$Kc=idx($v["descs"],$x);$Di=idx($v["opclasses"],$x);$O[]=($o[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($Di!=""?" ".idf_escape($Di):"").($Kc?" DESC":"");$e[]=$d;$Ig[]=($y?:null);$Mc[]=$Kc;$Ei[]="$Di";}}$Nd=$w[$B];if($Nd){ksort($Nd["columns"]);ksort($Nd["lengths"]);ksort($Nd["descs"]);if($v["type"]==$Nd["type"]&&array_values($Nd["columns"])===$e&&(!$Nd["lengths"]||array_values($Nd["lengths"])===$Ig)&&array_values($Nd["descs"])===$Mc&&(!$Nd["opclasses"]||array_values($Nd["opclasses"])===$Ei)&&$Nd["partial"]==$Gf&&(!$Ff||$Nd["algorithm"]==$Ef)&&!$Nd["invalid"]){unset($w[$B]);continue;}}if($e)$b[]=[$v["type"],$B,$O,$Ef,$Gf];}}foreach($w
as$B=>$Nd)$b[]=[$Nd["type"],$B,"DROP"];if(!$b)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),lang(214),alter_indexes($a,$b));}page_header(lang(161),$m,["table"=>$a],h($a),false,doc_link(['sql'=>"create-index.html",'pgsql'=>"sql-createindex.html",'cockroach'=>"create-index",'mssql'=>"t-sql/statements/create-index-transact-sql",'sqlite'=>"lang_createindex.html",'oracle'=>"sqlrf/CREATE-INDEX.html",]));$fe=array_keys($o);if($_POST["add"]){foreach($J["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$J["indexes"][$x]["columns"][]="";}$v=end($J["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$J["indexes"][]=["columns"=>[1=>""]];}if(!$J){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=["columns"=>[1=>""]];$J["indexes"]=$w;}$Ig=(JUSH=="sql"||JUSH=="mssql");$Ei=driver()->indexOpclasses();$Ll=($_POST?$_POST["options"]:get_setting("index_options"));$Nh=[];foreach($If
as$U)$Nh[$U]=str_replace("{table}",$a,adminer()->namePattern($U));echo'
<form method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">',lang(215);$yf=" class='idxopts".($Ll?"":" hidden")."'";if($Ff)echo"<th id='label-algorithm'$yf>".lang(216).doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html','cockroach'=>'create-index#parameters',]);echo'<th><input type="submit" hidden>',lang(217).($Ig?"<span$yf> (".lang(218).")</span>":"");if($Ig||support("descidx"))echo
checkbox("options",1,$Ll,lang(139),on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">',lang(219);if(support("partial_indexes"))echo"<th id='label-condition'$yf>".lang(220);echo'<td><noscript>',icon("plus","add[0]","+",lang(140)),'</noscript>
<tbody>
';if($Yj){echo"<tr><td>PRIMARY<td>";foreach($Yj["columns"]as$x=>$d)echo
select_input(" disabled",array_combine($fe,$fe),$d),"<label><input disabled type='checkbox'>".lang(74)."</label> ";echo"<td><td>\n";}$jg=1;foreach($J["indexes"]as$v){if(!$_POST["drop_col"]||$jg!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$jg][type]",[-1=>""]+$If,$v["type"],on('change','indexesChangeType',$Nh),"label-type");if($Ff)echo"<td$yf>".html_select("indexes[$jg][algorithm]",array_merge([""],$Ff),$v['algorithm'],"","label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$jg][columns][$s]' title='".lang(64)."'".on('change','indexesChangeColumn',$Nh),($o&&($d==""||$o[$d])?array_combine($fe,$fe):[]),$d)," <span$yf>",($Ig?"<input type='number' name='indexes[$jg][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".lang(138)."'>":"");if($Ei){$Di=idx($v["opclasses"],$x);echo
html_select("indexes[$jg][opclasses][$s]",[""=>"(".lang(221).")"]+array_combine($Ei,$Ei)+($Di!=""?[$Di=>$Di]:[]),$Di),doc_link(['pgsql'=>'indexes-opclass.html']);}echo(support("descidx")?checkbox("indexes[$jg][descs][$s]",1,idx($v["descs"],$x),lang(74)):""),"<br>","</span></span>";$s++;}echo"<td><input name='indexes[$jg][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$yf><input name='indexes[$jg][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$jg]","x",lang(142),on('click','editingRemoveRow','indexes$1[type]')),(idx($w[$v["name"]],"invalid")?" <span class='error'>INVALID</span>":""),"\n";}$jg++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';if(support("concurrent_indexes"))echo
checkbox("concurrently",1,$_POST["concurrently"],"concurrently").doc_link(['pgsql'=>'sql-createindex.html#SQL-CREATEINDEX-CONCURRENTLY'])."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["database"])){$J=$_POST;if($_POST&&!$m&&!$_POST["add"]){$B=trim($J["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(222),drop_databases([DB]));}elseif($B!==DB){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($B),lang(223),rename_database($B,(string)$J["collation"]));}else{$j=explode("\n",str_replace("\r","",$B));$rm=true;$_g="";foreach($j
as$k){if(count($j)==1||$k!=""){if(!create_database($k,(string)$J["collation"]))$rm=false;$_g=$k;}}restart_session();set_session("dbs",null);queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($_g),lang(224),$rm);}}else{if(!$J["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$J["collation"])?" COLLATE $J[collation]":""),substr(ME,0,-1),lang(225));}}$km=(DB!=""?"alter":"create");page_header(DB!=""?lang(82):lang(146),$m,[],h(DB),false,doc_link(['sql'=>"$km-database.html",'mariadb'=>(DB!=""?"":"$km-database"),'pgsql'=>"sql-$km"."database.html",'cockroach'=>"$km-database",'mssql'=>"t-sql/statements/$km-database-transact-sql",'oracle'=>"sqlrf/".strtoupper($km)."-USER.html",]));$Ib=collations();$B=DB;if($_POST)$B=$J["name"];elseif(DB!="")$J["collation"]=db_collation(DB,$Ib);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$Le){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$Le,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?"<textarea autofocus name='name' rows='10' cols='40'>\n".h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n",($Ib?"<datalist id='collations'>".optionlist(flat_collations())."</datalist>"."<input list='collations' name='collation' value='".h($J["collation"])."' placeholder='(".lang(133).")'>".doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",]):"")."\n",'<input type=\'submit\' value=\'',lang(24),'\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".lang(155)."'".confirm(lang(209,DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",lang(140))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$J=$_POST;$f=(support("comment")&&$_GET["ns"]!=""?schema_comment($_GET["ns"]):"");if($_POST&&!$m){$_=preg_replace('~&ns=[^&]*~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,lang(226));else{$B=trim($J["name"]);$_
.=url_escape($B);$Uh=(support("comment")?$J["comment"]:$f);$H=true;$ph=lang(227);if($_GET["ns"]==""){$H=queries("CREATE SCHEMA ".idf_escape($B));$ph=lang(228);}elseif($_GET["ns"]!=$B)$H=queries("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B));elseif($Uh===$f)redirect($_);if($H&&$Uh!==$f)$H=set_schema_comment($B,$Uh);queries_redirect($_,$ph,$H);}}$km=($_GET["ns"]!=""?"alter":"create");page_header($_GET["ns"]!=""?lang(83):lang(84),$m,[],"",false,doc_link(['pgsql'=>"sql-$km"."schema.html",'cockroach'=>"$km-schema",'mssql'=>"t-sql/statements/create-schema-transact-sql",]));if(!$J){$J["name"]=$_GET["ns"];$J["comment"]=$f;}echo'
<form method="post">
<p><input name="name" autofocus value="',h($J["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'',lang(24),'\'>
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".lang(155)."'".confirm(lang(209,$_GET["ns"])).">\n";echo(support("comment")?"<p>".lang(66).": ".adminer()->commentInput('SCHEMA'," name='comment'",$J["comment"])."\n":""),input_token(),'</form>
';}elseif(isset($_GET["call"])){$ca=($_GET["name"]?:$_GET["call"]);$Wk=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$Rk=routine($_GET["call"],$Wk);$gg=(idx($Rk["returns"],"type")=="trigger");page_header(($gg?lang(169):lang(229)).": ".h($ca),$m,"#routines","",!$Rk,(isset($_GET["callf"])?"":doc_link(['sql'=>"call.html",'pgsql'=>"sql-call.html",'cockroach'=>"call",'mssql'=>"t-sql/language-elements/execute-transact-sql",])));$Af=[];$bj=[];foreach($Rk["fields"]as$s=>$n){if(substr($n["inout"],-3)=="OUT"&&JUSH=='sql')$bj[$s]="@".idf_escape($n["field"])." AS ".idf_escape($n["field"]);if(!$n["inout"]||preg_match('~^(IN|OUTPUT)~',$n["inout"]))$Af[]=$s;}if(!$m&&$_POST){$kb=[];foreach($Rk["fields"]as$x=>$n){$X="";if(in_array($x,$Af)){$X=process_input($n);if($X===false)$X="''";if(isset($bj[$x]))connection()->query("SET @".idf_escape($n["field"])." = $X");}if(isset($bj[$x]))$kb[]="@".idf_escape($n["field"]);elseif(in_array($x,$Af))$kb[]=$X;}$Fa=implode(", ",$kb);$G=(isset($_GET["callf"])||JUSH!="mssql"?(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($Rk["returns"],"type")=="record"?"* FROM ":"").table($ca)."($Fa)":"EXEC ".table($ca).($Fa!=""?" $Fa":""));$im=microtime(true);$H=connection()->multi_query($G);$ta=connection()->affected_rows;echo
adminer()->selectQuery($G,$im,!$H);if(!$H)echo"<p class='error'>".adminer()->error()."\n";else{$h=connect();if($h)$h->select_db(DB);do{$H=connection()->store_result();if(is_object($H))print_select_result($H,$h);else
echo"<p class='message'>".lang(230,$ta)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($bj)print_select_result(connection()->query("SELECT ".implode(", ",$bj)));}}if($gg){$zn=driver()->routineTriggers($_GET["call"]);if($zn){echo"<table>\n";foreach($zn
as$J){$_=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($J["ns"]),ME);echo"<tr><th>".h($J["trigger"])."<td><a href='".h($_."table=".url_escape($J["table"])."#triggers")."'>".($J["ns"]!=$_GET["ns"]?"<b>".h($J["ns"])."</b>.":"").h($J["table"])."</a>"."<td class='hover'><a href='".h($_."trigger=".url_escape($J["table"])."&name=".url_escape($J["trigger"]))."'>".lang(165)."</a>\n";}echo"</table>\n";}}else{echo'<form method="post">
';if($Af){echo"<table class='layout'>\n";foreach($Af
as$x){$n=$Rk["fields"][$x];$B=$n["field"];echo"<tr><th>".adminer()->fieldName($n);$Y=idx($_POST["fields"],$B);if($Y!=""){if($n["type"]=="set")$Y=implode(",",$Y);}input($n,$Y,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'',lang(229),'\'>
',input_token(),'</form>
';}echo'
',adminer()->commentValue($Wk,$Rk['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$J=$_POST;if($_POST&&!$m&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$J["source"]=array_filter($J["source"],'strlen');ksort($J["source"]);$Rm=[];foreach($J["source"]as$x=>$X)$Rm[$x]=$J["target"][$x];$J["target"]=$Rm;}$Ya=false;if(JUSH=="sqlite")$H=recreate_table($a,$a,[],[],[" $B"=>($J["drop"]?"":" ".format_foreign_key($J))]);else{$Yb=$J["constraint"];$b="ALTER TABLE ".table($a);$ed="$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B?:'');if($J["drop"])$H=queries($ed);else{$ma=" ADD".format_foreign_key($J,$Yb);if($B=="")$H=queries($b.$ma);elseif(JUSH=="pgsql"||(JUSH=="sql"&&$Yb!=$B))$H=queries("$ed,$ma");else{$Ya=support("transaction_ddl")&&driver()->begin();$H=($Ya||idx(foreign_keys($a),$B)?queries($ed):true)&&queries($b.$ma)&&(!$Ya||driver()->commit());}}}queries_redirect(ME."table=".url_escape($a),($J["drop"]?lang(231):($B!=""?lang(232):lang(233))),$H);if($Ya)driver()->rollback();if(!$J["drop"])$m=lang(234);}$gi=false;if(!$_POST&&$B!=""){$we=foreign_keys($a);$J=idx($we,$B,[]);$gi=!$J;}$Mh=str_replace("{table}",$a,adminer()->namePattern("FOREIGN"));$Ph="^".str_replace('\{columns\}','.*',preg_quote($Mh))."$";page_header(($B!=""?lang(235):lang(166)),$m,["table"=>$a],h($B!=""?$B:$a),$gi,doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'cockroach'=>"foreign-key",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"sqlrf/constraint.html",]));if($_POST){ksort($J["source"]);if($_POST["change"]||$_POST["change-js"])$J["target"]=[];else$J["source"][]="";}elseif($B!=""){$J["source"][]="";$J["constraint"]=$B;}else{$J["table"]=$a;$J["source"]=[""];$J["constraint"]=str_replace("{columns}","",$Mh);}echo'
<form method="post">
';$Ul=array_keys(fields($a));if($J["db"]!="")connection()->select_db($J["db"]);$Wi=null;if($J["ns"]!=""){$Wi=get_schema();set_schema($J["ns"]);}$wk=array_keys(array_filter(table_status('',true),function(array$S){return!$S["dependent"]&&fk_support($S);}));$Rm=array_keys(fields(in_array($J["table"],$wk)?$J["table"]:reset($wk)));$c=on('change','foreignChange');echo"<p><label>".lang(236).": ".html_select("table",$wk,$J["table"],$c)."</label>\n";if(support("scheme")){$hi=($J["ns"]!=""?$J["ns"]:$_GET["ns"]);$dl=array_filter(adminer()->schemas(),function($L)use($hi){return!driver()->isSystem(DB,$L)||$L==$hi||$L==$_GET["ns"];});echo"<label>".lang(16).": ".html_select("ns",$dl,$hi,$c)."</label>";if($Wi!==null)set_schema($Wi);}elseif(JUSH!="sqlite"){$pl=($J["db"]!=""?$J["db"]:DB);$_c=[];foreach(adminer()->databases()as$k){if(!driver()->isSystem($k)||$k==$pl||$k==DB)$_c[]=$k;}echo"<label>".lang(98).": ".html_select("db",$_c,$pl,$c)."</label>";}if($J["db"]!="")connection()->select_db(DB);echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'',lang(237),'\'></noscript>
<table',on('change','foreignName',$Ph,$Mh),'>
<thead><tr><th id="label-source">',lang(163),'<th id="label-target">',lang(164),'<tbody>
';$jg=0;foreach($J["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",[-1=>""]+$Ul,$X,($jg==count($J["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Rm,idx($J["target"],$x),"","label-target");$jg++;}echo'</table>
<p>
<label>',lang(135),': ',html_select("on_delete",[-1=>""]+explode("|",driver()->onActions),$J["on_delete"]),'</label>
<label>',lang(134),': ',html_select("on_update",[-1=>""]+explode("|",driver()->onActions),$J["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$J["deferrable"]):''),'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'',lang(238),'\'></noscript>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$B)),'>
';if(JUSH!="sqlite"){print_fieldset("options",lang(139),$J["constraint"]!=$B&&!preg_match("($Ph)",$J["constraint"]));echo"<label>".lang(219).": <input name='constraint' value='".h($J["constraint"])."' autocapitalize='off'></label>\n","</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$J=$_POST;$Xi="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$Xi=strtoupper($P["Engine"]);}if($_POST&&!$m){$B=trim($J["name"]);$Ha=" AS\n$J[select]";$Rg=ME."table=".url_escape($B);$ph=lang(239);$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$U=="VIEW"&&$Xi=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$Ha,$Rg,$ph);else{$Vm="adminer_".uniqid();drop_create("DROP $Xi ".table($a),"CREATE $U ".table($B).$Ha,"DROP $U ".table($B),"CREATE $U ".table($Vm).$Ha,"DROP $U ".table($Vm),($_POST["drop"]?substr(ME,0,-1):$Rg),lang(240),$ph,lang(241),$a,$B);}}$gi=false;if(!$_POST&&$a!=""){$J=view($a);$gi=!$J["select"];$J["name"]=$a;$J["materialized"]=($Xi!="VIEW");if(!$m)$m=adminer()->error();}page_header(($a!=""?lang(59):lang(93)),$m,["table"=>$a],h($a),$gi,doc_link(['sql'=>"create-view.html",'pgsql'=>"sql-createview.html",'cockroach'=>"create-view",'mssql'=>"t-sql/statements/create-view-transact-sql",'sqlite'=>"lang_createview.html",'oracle'=>"sqlrf/CREATE-VIEW.html",]));echo'
<form method="post">
<p>',lang(219),': <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$J["materialized"],lang(157)):""),'<p>';textarea("select",$J["select"]);echo'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$Wf=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$mm=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$J=$_POST;if($_POST&&!$m){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),lang(242));elseif(in_array($J["INTERVAL_FIELD"],$Wf)&&isset($mm[$J["STATUS"]])){$bl="\nON SCHEDULE ".($J["INTERVAL_VALUE"]?"EVERY ".q($J["INTERVAL_VALUE"])." $J[INTERVAL_FIELD]".($J["STARTS"]?" STARTS ".q($J["STARTS"]):"").($J["ENDS"]?" ENDS ".q($J["ENDS"]):""):"AT ".q($J["STARTS"]))." ON COMPLETION".($J["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?lang(243):lang(244)),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$bl.($aa!=$J["EVENT_NAME"]?"\nRENAME TO ".idf_escape($J["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($J["EVENT_NAME"]).$bl)."\n".$mm[$J["STATUS"]]." COMMENT ".q($J["EVENT_COMMENT"]).rtrim(" DO\n$J[EVENT_DEFINITION]",";").";"));}}$gi=false;if(!$J&&$aa!=""){$K=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$gi=!$K;$J=reset($K);}page_header(($aa!=""?lang(245).": ".h($aa):lang(246)),$m,"#events","",$gi,doc_link(['sql'=>"create-event.html"]));echo'
<form method="post">
<table class="layout">
<tr><th>',lang(219),'<td><input name="EVENT_NAME" value="',h($J["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">',lang(247),'<td><input name="STARTS" value="',h("$J[EXECUTE_AT]$J[STARTS]"),'">
<tr><th title="datetime">',lang(248),'<td><input name="ENDS" value="',h($J["ENDS"]),'">
<tr><th>',lang(249),'<td><input type="number" name="INTERVAL_VALUE" value="',h($J["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$Wf,$J["INTERVAL_FIELD"]),'<tr><th>',lang(97),'<td>',html_select("STATUS",$mm,$J["STATUS"]),'<tr><th>',lang(66),'<td><input name="EVENT_COMMENT" value="',h($J["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$J["ON_COMPLETION"]=="PRESERVE",lang(250)),'</table>
<p>';textarea("EVENT_DEFINITION",$J["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ca=($_GET["name"]?:$_GET["procedure"]);$Rk=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$J=$_POST;$J["fields"]=(array)$J["fields"];if($_POST&&!process_fields($J["fields"])&&!$m){foreach($J["fields"]as$x=>$n){if($n["field"]=="")unset($J["fields"][$x]);}$xi=routine($_GET["procedure"],$Rk);$vi=($xi?routine_id($ca,$xi):"");$Wh=routine_id($J["name"],$J);$i=create_routine($Rk,$J);$Rg=substr(ME,0,-1);$ph=lang(251);if(!$_POST["drop"]&&$vi==$Wh&&connection()->flavor!="mysql")queries_redirect($Rg,$ph,queries(substr_replace($i,(JUSH=="mssql"?' OR ALTER':' OR REPLACE'),6,0)));else{$Vm="adminer_".uniqid();drop_create("DROP $Rk $vi",$i,"DROP $Rk $Wh",create_routine($Rk,["name"=>$Vm]+$J),"DROP $Rk ".routine_id($Vm,$J),$Rg,lang(252),$ph,lang(253),$ca,$J["name"]);}}$gi=false;if(!$_POST&&$ca!=""){$J=routine($_GET["procedure"],$Rk);$gi=!$J;$J["name"]=$ca;}$Uk=strtolower($Rk);page_header(($ca!=""?(isset($_GET["function"])?lang(170):lang(254)).": ".h($ca):(isset($_GET["function"])?lang(255):lang(256))),$m,"#routines","",$gi,doc_link(['sql'=>"create-procedure.html",'mariadb'=>"create-$Uk/",'pgsql'=>"sql-create$Uk.html",'cockroach'=>"create-$Uk",'mssql'=>"t-sql/statements/create-$Uk-transact-sql",]));if(!$_POST&&$ca=="")$J["language"]="sql";$Ib=(JUSH=="sql"?flat_collations():[]);$Sk=routine_languages();echo($Ib?"<datalist id='collations'>".optionlist($Ib)."</datalist>":""),'
<form method="post" id="form">
<p>',lang(219),': <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',($Sk?"<label>".lang(30).": ".html_select("language",array_keys($Sk),$J["language"],on('change','routineLanguage',$Sk))."</label>\n":""),'<input type=\'submit\' value=\'',lang(24),'\'>
<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($J["fields"],$Ib,$Rk);if(isset($_GET["function"])){echo"<tr><td>".lang(257);edit_type("returns",(array)$J["returns"],$Ib,[],(JUSH=="pgsql"?["void","trigger"]:[]));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$J["definition"],20,80,($Sk[$J["language"]]?:JUSH));echo'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';if($ca!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$ca)),'>
';$Vk=routine_options($Rk);if($Vk){$Li=false;foreach($Vk
as$x=>$mo){$l=($mo?reset($mo):"");$J["options"][$x]=idx($J["options"],$x,$l);if($J["options"][$x]!=$l)$Li=true;}print_fieldset("options",lang(139),$Li);echo"<table class='layout'>\n";foreach($Vk
as$x=>$mo){$ug="label-option-$x";$dn=str_replace("_"," ",$x);$M=[];foreach($mo
as$Y)$M[$Y]=(strpos($Y,"$dn ")===0?substr($Y,strlen($dn)+1):$Y);echo"<tr><th id='$ug'>$dn<td>".($M?html_select("options[$x]",$M,$J["options"][$x],"",$ug):"<input name='options[$x]' value='".h($J["options"][$x])."' aria-labelledby='$ug' autocapitalize='off'>")."\n";}echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$ea=$_GET["sequence"];$J=$_POST;if($_POST&&!$m){$_=substr(ME,0,-1);$B=trim($J["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($ea),$_,lang(258));elseif($ea=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,lang(259));elseif($ea!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($ea)." RENAME TO ".idf_escape($B),$_,lang(260));else
redirect($_);}$gi=(!$_POST&&$ea!=""&&!get_val("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." AND relname = ".q($ea)));page_header(($ea!=""?lang(261).": ".h($ea):lang(262)),$m,"#sequences","",$gi,doc_link(['pgsql'=>"sql-createsequence.html",'cockroach'=>"create-sequence",]));if(!$J)$J["name"]=$ea;echo'
<form method="post">
<p><input name="name" value="',h($J["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'',lang(24),'\'>
';if($ea!="")echo"<input type='submit' name='drop' value='".lang(155)."'".confirm(lang(209,$ea)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){function
enum_values($Gc){$Y="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$Y.'(?:\s*,\s*'.$Y.')*)\s*\)$~i',$Gc,$A))return
null;preg_match_all('~'.$Y.'~',$A[1],$Xg);return$Xg[0];}function
add_enum_values($U,$ti,$Th){$zi=enum_values($ti);$bi=enum_values($Th);if($zi===null||$bi===null)return
null;$I=[];$s=0;foreach($bi
as$Y){if($Y===idx($zi,$s))$s++;else$I[]="ALTER TYPE ".idf_escape($U)." ADD VALUE $Y".($s<count($zi)?" BEFORE ".$zi[$s]:"");}return($s==count($zi)?$I:null);}$fa=$_GET["type"];$J=$_POST;$Cn=($fa!=""?array_search($fa,types(true)):0);$U=($Cn?type_definition(+$Cn):[]);$li=($U["kind"]=='d'?"DOMAIN":"TYPE");if($_POST&&!$m){$_=substr(ME,0,-1);$B=trim($J["name"]);$Ha=trim(str_replace("\r","",$J["as"]));$Yh=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Ha)?"DOMAIN":"TYPE");$ph=lang(263);$b=(!$_POST["drop"]&&$fa!=""&&$Yh==$li?($Ha==$U["definition"]?[]:add_enum_values($fa,$U["definition"],$Ha)):null);if($b!==null){if($fa!=$B)$b[]="ALTER $li ".idf_escape($fa)." RENAME TO ".idf_escape($B);if(!$b)redirect($_);$Xd=false;foreach($b
as$G){if(!queries($G)){$Xd=true;break;}}queries_redirect($_,$ph,!$Xd);}else{$Vm="adminer_".uniqid();drop_create("DROP $li ".idf_escape($fa),"CREATE $Yh ".idf_escape($B)." $Ha","DROP $Yh ".idf_escape($B),"CREATE $Yh ".idf_escape($Vm)." $Ha","DROP $Yh ".idf_escape($Vm),$_,lang(264),$ph,lang(265),$fa,$B);}}page_header(($fa!=""?lang(266).": ".h($fa):lang(267)),$m,"#user-types","",($Cn===false),doc_link(['pgsql'=>"sql-createtype.html",'cockroach'=>"create-type",]));if(!$J){$J["name"]=$fa;$J["as"]=($fa!=""?$U["definition"]:"AS ");}echo'
<form method="post">
<p>
',lang(219).": <input name='name' value='".h($J['name'])."' autocapitalize='off'>\n";textarea("as",$J["as"]);echo"<p><input type='submit' value='".lang(24)."'>\n";if($fa!="")echo"<input type='submit' name='drop' value='".lang(155)."'".confirm(lang(209,$fa)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B="$_GET[name]";$J=$_POST;if($J&&!$m){$Rg=ME."table=".url_escape($a);$sh=lang(268);$qh=lang(269);$rh=lang(270);if(JUSH=="sqlite"&&!(min_version(3.53)&&($B==""||idx(driver()->checkConstraints($a),$B)!=$B))){$ma=($J["drop"]?"":($J["name"]!=""?"CONSTRAINT ".idf_escape($J["name"])." ":"")."CHECK ($J[clause])");queries_redirect($Rg,($J["drop"]?$sh:($B!=""?$qh:$rh)),recreate_table($a,$a,[],[],[],"",[],"$B",$ma));}else{$b="ALTER TABLE ".table($a);$tb=" CHECK ($J[clause])";$Vm="adminer_".uniqid();drop_create("$b DROP CONSTRAINT ".idf_escape($B),"$b ADD".($J["name"]!=""?" CONSTRAINT ".idf_escape($J["name"]):"").$tb,"$b DROP CONSTRAINT ".idf_escape($J["name"]),"$b ADD CONSTRAINT ".idf_escape($Vm).$tb,"$b DROP CONSTRAINT ".idf_escape($Vm),$Rg,$sh,$qh,$rh,$B,$J["name"]);}}$gi=false;if(!$J){$xb=driver()->checkConstraints($a);$gi=($B!=""&&!$xb[$B]);$J=["name"=>($B==""?object_name("CHECK",$a,[]):($xb[$B]!=$B?$B:"")),"clause"=>$xb[$B]];}page_header(($B!=""?lang(271):lang(168)),$m,["table"=>$a],h($B!=""?$B:$a),$gi,doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'cockroach'=>"check",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",]));echo'
<form method="post">
<p><label>',lang(219),': <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off"></label>
<p>';textarea("clause",$J["clause"]);echo'<p><input type=\'submit\' value=\'',lang(24),'\'>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$yn=trigger_options();$J=trigger($B,$a);$gi=($B!=""&&!$J);$Mh=str_replace("{table}",$a,adminer()->namePattern("TRIGGER"));$J+=["Trigger"=>strtr($Mh,["{timing}"=>"b","{event}"=>"i","{columns}"=>"","{type}"=>"row"])];if($_POST){if(!$m&&in_array($_POST["Timing"],$yn["Timing"])&&in_array($_POST["Event"],$yn["Event"])&&in_array($_POST["Type"],$yn["Type"])){$_i=" ON ".table($a);$ed="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$_i:"");$Rg=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($ed,$Rg,lang(272));else{if($B!="")queries($ed);queries_redirect($Rg,($B!=""?lang(273):lang(274)),queries(create_trigger($_i,$_POST)));if($B!="")queries(create_trigger($_i,$J+["Type"=>reset($yn["Type"])]));}}$J=$_POST;}page_header(($B!=""?lang(275):lang(171)),$m,["table"=>$a],h($B!=""?$B:$a),$gi,doc_link(['sql'=>"create-trigger.html",'pgsql'=>"sql-createtrigger.html",'cockroach'=>"create-trigger",'mssql'=>"t-sql/statements/create-trigger-transact-sql",'sqlite'=>"lang_createtrigger.html",'oracle'=>"lnpls/CREATE-TRIGGER-statement.html",]));$Ph=strtr(preg_quote($Mh),['\{timing\}'=>'[abi]','\{event\}'=>'[iud]*','\{columns\}'=>'.*','\{type\}'=>'(row|statement)']);$wn=on('change','triggerChange',"^$Ph$",$Mh);$oi=on('input','triggerChange',"^$Ph$",$Mh);echo'
<form method="post" id="form">
<table class="layout">
<tr><th>',lang(276),'<td>',html_select("Timing",$yn["Timing"],$J["Timing"],$wn),'<tr><th>',lang(277),'<td>',html_select("Event",$yn["Event"],$J["Event"],$wn),(in_array("UPDATE OF",$yn["Event"])?" <input name='Of' value='".h($J["Of"])."' class='hidden'$oi>":""),'<tr><th>',lang(65),'<td>',html_select("Type",$yn["Type"],$J["Type"],$wn),'<tr><th>',lang(219),'<td><input name="Trigger" value="',h($J["Trigger"]),'" data-maxlength="64" autocapitalize="off">
</table>
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$J["Statement"]);echo'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){function
grant($Le,array$dk,$e,$_i){if(!$dk)return
true;if($dk==["ALL PRIVILEGES","GRANT OPTION"])return($Le=="GRANT"?queries("$Le ALL PRIVILEGES$_i WITH GRANT OPTION"):queries("$Le ALL PRIVILEGES$_i")&&queries("$Le GRANT OPTION$_i"));return
queries("$Le ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$dk).$e).$_i);}function
queries_password($xe,$do,$yj){remember_query(sprintf($xe,$do,"<password>"));return
connection()->query(sprintf($xe,$do,q($yj)));}$ga=$_GET["user"];$dk=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$J){foreach(explode(",",($J["Privilege"]=="Grant option"?"":$J["Context"]))as$dc)$dk[$dc=="File access on server"?"Server Admin":$dc][$J["Privilege"]]=$J["Comment"];}unset($dk["Server Admin"]["Usage"]);foreach($dk["Tables"]as$x=>$X)unset($dk["Databases"][$x]);$Vh=[];if($_POST){foreach($_POST["objects"]as$x=>$X)$Vh[$X]=(array)$Vh[$X]+idx($_POST["grants"],$x,[]);}$Me=[];$H=(isset($_GET["host"])?connection()->query("SHOW GRANTS FOR ".q($ga)."@".q($_GET["host"])):null);$gi=(isset($_GET["host"])&&!$H);if($H){while($J=$H->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$J[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$Xg,PREG_SET_ORDER)){foreach($Xg
as$X){if($X[1]!="USAGE")$Me["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$J[0]))$Me["$A[2]$X[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$m){$yi=(isset($_GET["host"])?q($ga)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $yi",ME."privileges=",lang(278));else{$ai=q($_POST["user"])."@".q($_POST["host"]);$yj=$_POST["pass"];$lc=false;$H=true;if($yi!=$ai){$lc=queries_password("CREATE USER %s IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD %s":"%s"),$ai,$yj);$H=$lc;}elseif($yj!="")$H=queries_password("SET PASSWORD FOR %s = ".(min_version(8,99)||$_POST["hashed"]?"%s":"PASSWORD(%s)"),$ai,$yj);if($H){$Nk=[];foreach($Vh
as$li=>$Le){if(isset($_GET["grant"]))$Le=array_filter($Le);$Le=array_keys($Le);if(isset($_GET["grant"]))$Nk=array_diff(array_keys(array_filter($Vh[$li],'strlen')),$Le);elseif($yi==$ai){$ui=array_keys((array)$Me[$li]);$Nk=array_diff($ui,$Le);$Le=array_diff($Le,$ui);unset($Me[$li]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$li,$A)&&(!grant("REVOKE",$Nk,$A[2]," ON $A[1] FROM $ai")||!grant("GRANT",$Le,$A[2]," ON $A[1] TO $ai"))){$H=false;break;}}}if($H&&isset($_GET["host"])){if($yi!=$ai)queries("DROP USER $yi");elseif(!isset($_GET["grant"])){foreach($Me
as$li=>$Nk){if(preg_match('~^(.+)(\(.*\))?$~U',$li,$A))grant("REVOKE",array_keys($Nk),$A[2]," ON $A[1] FROM $ai");}}}if($H&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(279):lang(280)),$H);if($lc)connection()->query("DROP USER $ai");}}page_header((isset($_GET["host"])?lang(50).": ".h("$ga@$_GET[host]"):lang(179)),$m,["privileges"=>['',lang(86)]],"",$gi,doc_link(['sql'=>"grant.html",'mariadb'=>"grant"]));$J=$_POST;if($J)$Me=$Vh;else{$J=$_GET+["host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];$Me[(DB==""||$Me?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=[];}echo'<form method="post">
<table class="layout">
<tr><th>',lang(48),'<td><input name="host" data-maxlength="60" value="',h($J["host"]),'" autocapitalize="off">
<tr><th>',lang(50),'<td><input name="user" data-maxlength="80" value="',h($J["user"]),'" autocapitalize="off">
<tr><th>',lang(51),'<td><input name="pass" id="pass" value="',h($J["pass"]),'" autocomplete="new-password">
',($J["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$J["hashed"],lang(281),on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".lang(86);$s=0;foreach($Me
as$li=>$Le){echo'<th>'.($li!="*.*"?"<input name='objects[$s]' value='".h($li)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"<tbody>\n";foreach([""=>"","Server Admin"=>lang(48),"Databases"=>lang(17),"Tables"=>lang(159),"Procedures"=>lang(282),]as$dc=>$Kc){foreach((array)$dk[$dc]as$ck=>$f){echo"<tr><td".($Kc?">$Kc<td":" colspan='2'").' lang="en" title="'.h($f).'">'.h($ck);$s=0;foreach($Me
as$li=>$Le){$B="'grants[$s][".h(strtoupper($ck))."]'";$Y=$Le[strtoupper($ck)];if($dc=="Server Admin"&&$li!=(isset($Me["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($Y?" selected":"").">".lang(283)."<option value='0'".($Y=="0"?" selected":"").">".lang(284)."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($Y?" checked":"").($ck=="All privileges"?" id='grants-$s-all'":($ck=="Grant option"?"":on('click','grantsClick',"grants-$s-all"))).">","</label>";$s++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'',lang(24),'\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'',lang(155),'\'',confirm(lang(209,"$ga@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$m){$rg=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$rg++;}queries_redirect(ME."processlist=",lang(285,$rg),$rg||!$_POST["kill"]);}}page_header(lang(95),$m);echo'
<form method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$s=-1;foreach(adminer()->processList()as$s=>$J){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($J
as$x=>$X)echo"<th>$x".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"refrn/V-SESSION.html",]);echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$J[JUSH=="sql"?"Id":"pid"],0):"");foreach($J
as$x=>$X)echo"<td>".($X!=""&&((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$J["Command"]))||(JUSH=="pgsql"&&$x=="query")||(JUSH=="oracle"&&$x=="sql_text"))?"<code class='sql jush-".JUSH."' data-full='".h($X)."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(($J["db"]!=""?preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($J["db"])."&":ME)."sql=".url_escape($X)).'">'.lang(286).'</a>'.' '.copy_icon():h($X));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo
format_number($s+1)."/".lang(287,max_connections()),"<p><input type='submit' value='".lang(288)."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif($_GET["select"]!=""){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$o=fields($a);$we=column_foreign_keys($a);$si=$S["Oid"];$Pk=[];$e=[];$hl=[];$Oi=[];$Ym=null;foreach($o
as$x=>$n){$B=adminer()->fieldName($n);$Oh=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($n["privileges"]["select"])&&$B!=""){$e[$x]=$Oh;if(is_shortable($n))$Ym=adminer()->selectLengthProcess();}if(isset($n["privileges"]["where"])&&$B!="")$hl[$x]=$Oh;if(isset($n["privileges"]["order"])&&$B!="")$Oi[$x]=$Oh;$Pk+=$n["privileges"];}list($M,$Oe)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$Oe=array_unique($Oe);$dg=count($Oe)<count($M);$Z=adminer()->selectSearchProcess($o,$w,$S);$Ni=adminer()->selectOrderProcess($o,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Ln=>$J){$Ha=convert_field($o[key($J)]);$M=[$Ha?:idf_escape(key($J))];$Z[]=where_check(bracket_escape($Ln,true),$o);$I=driver()->select($a,$M,$Z,$M);if($I)echo
first($I->fetch_row());}exit;}$Yj=$On=[];foreach($w
as$v){if($v["type"]=="PRIMARY"){$Yj=array_flip($v["columns"]);$On=($M?$Yj:[]);foreach($On
as$x=>$X){if(in_array(idf_escape($x),$M))unset($On[$x]);}break;}}if($si&&!$Yj){$Yj=$On=[$si=>0];$w[]=["type"=>"PRIMARY","columns"=>[$si]];}if($_POST&&!$m){$yo=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$xb=[];foreach($_POST["check"]as$tb)$xb[]=where_check($tb,$o);$yo[]="((".implode(") OR (",$xb)."))";}$_o=$yo;$yo=($yo?"\nWHERE ".implode(" AND ",$yo):"");if($_POST["export"]){save_settings(["output"=>$_POST["output"],"format"=>$_POST["format"]],"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$nl=($M?:["*"]);$gc=convert_fields($e,$o,$M);if($gc)$nl[]=substr($gc,2);$G="";if(is_array($_POST["check"])&&!$Yj){$Ce=implode(", ",$nl)."\nFROM ".table($a);$Qe=($Oe&&$dg?"\nGROUP BY ".implode(", ",$Oe):"").($Ni?"\nORDER BY ".implode(", ",$Ni):"");$In=[];foreach($_POST["check"]as$X)$In[]="(SELECT".limit($Ce,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$o).$Qe,1).")";$G=implode(" UNION ALL ",$In);}adminer()->dumpData($a,"table",$G,$nl,$_o,($dg?$Oe:[]),$Ni);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$we)){if($_POST["save"]||$_POST["delete"]){$H=true;$ta=0;$Ya=false;$O=[];if(!$_POST["delete"]){foreach($o
as$B=>$X){$u=bracket_escape($B);if(isset($_POST["fields"][$u])||$_FILES["fields-$u"]){$X=process_input($o[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}}if($_POST["delete"]||$O){$G=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($Yj&&is_array($_POST["check"]))||$dg){$H=($_POST["delete"]?driver()->delete($a,$yo):($_POST["clone"]?queries("INSERT $G$yo".driver()->insertReturning($a)):driver()->update($a,$O,$yo)));$ta=connection()->affected_rows;if(is_object($H))$ta+=$H->num_rows;}else{$Ya=count((array)$_POST["check"])>1&&driver()->begin();foreach((array)$_POST["check"]as$X){$xo="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$o);$H=($_POST["delete"]?driver()->delete($a,$xo,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$xo)):driver()->update($a,$O,$xo,1)));if(!$H)break;$ta+=connection()->affected_rows;}if($Ya&&$H&&!driver()->commit())$H=false;}}$ph=lang(181,$ta);if($_POST["clone"]&&$H&&$ta==1){$Bg=last_id($H);if($Bg)$ph=lang(202," $Bg");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$ph,$H);if($Ya)driver()->rollback();if(!$_POST["delete"]){$Oj=(array)$_POST["fields"];edit_form($a,array_intersect_key($o,$Oj),$Oj,!$_POST["clone"],$m);page_footer();exit;}}elseif(!$_POST["import"]){$H=true;$ta=0;$Ya=count((array)$_POST["val"])>1&&driver()->begin();foreach((array)$_POST["val"]as$Ln=>$J){$O=[];foreach($J
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$o[$x]["type"])||$X!=""?adminer()->processInput($o[$x],$X):"NULL");}$H=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($Ln,true),$o),($dg||$Yj?0:1)," ");if(!$H)break;$ta+=connection()->affected_rows;}if($Ya)$H=$H&&driver()->commit();queries_redirect(remove_from_uri(),lang(181,$ta),$H);if($Ya)driver()->rollback();}else{save_settings(["format"=>$_POST["separator"]],"adminer_import");$ge=get_file("csv_file",true);if(!is_string($ge))$m=upload_error($ge);elseif(!preg_match('~~u',$ge))$m=lang(289);else{$Jb=array_keys($o);$tl=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$pc=parse_csv($ge,$tl);$ta=count($pc);driver()->begin();$K=[];foreach($pc
as$x=>$mo){if(!$x&&!array_diff($mo,$Jb)){$Jb=$mo;$ta--;}else{$O=[];foreach($mo
as$s=>$Fb)$O[idf_escape($Jb[$s])]=($Fb==""&&$o[$Jb[$s]]["null"]?"NULL":q(csv_value($Fb)));$K[]=$O;}}$H=(!$K||driver()->insertUpdate($a,$K,$Yj));if($H)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang(290,$ta),$H);driver()->rollback();}}}}$Fm=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(69).": $Fm",$m,[],"",(!$o&&support("table")),($o?doc_link([JUSH=>driver()->tableHelp($a,is_view($S))]):""));$O=null;if(isset($Pk["insert"])||!support("table")){$O="";foreach((array)$_GET["where"]as$X){$Y=$X["val"];if(is_array($Y))$Y=(count($Y)==1&&preg_match('~^val-(.*)~s',reset($Y),$A)?$A[1]:"");if($X["col"]!=""&&$Y!=""&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$Y)))))$O
.="&set[".url_escape(bracket_escape($X["col"]))."]=".url_escape($Y);}}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".lang(291)."\n";else{echo"<form id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$hl,$w,$S);adminer()->selectOrderPrint($Ni,$Oi,$w);adminer()->selectLimitPrint($z);if($Ym!==null)adminer()->selectLengthPrint($Ym);adminer()->selectActionPrint($w);$Zl=[];foreach((array)$_GET["where"]as$X){if($X["op"]=="SQL")$Zl[]=$X["val"];}$em=($Zl&&$_SERVER["HTTP_SEC_FETCH_SITE"]!="same-origin"?hash_hmac('sha256',json_encode([$a,$Zl]),$_SESSION["token"]):"");echo($em?input_hidden("sql_sig",$em):""),"</form>\n";if($em&&$_GET["sql_sig"]!==$em){echo"<p class='error'>".lang(128).' '.lang(129)."\n";page_footer();exit;}$D=$_GET["page"];$ze=null;if($D=="last"){$ze=get_val(count_rows($a,$Z,$dg,$Oe));$D=floor(max(0,intval($ze)-1)/$z);}$ml=$M;$Pe=$Oe;if(!$ml){$ml[]="*";$gc=convert_fields($e,$o,$M);if($gc)$ml[]=substr($gc,2);}foreach($M
as$x=>$X){$n=$o[idf_unescape($X)];if($n&&($Ha=convert_field($n)))$ml[$x]="$Ha AS $X";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$x=>$X){if(isset($ml[$x])&&$X["fun"])$ml[$x].=" AS ".idf_escape(apply_sql_function($X["fun"],($X["col"]!=""?$X["col"]:"*")));}}if(!$dg&&$On){foreach($On
as$x=>$X){$ml[]=idf_escape($x);if($Pe)$Pe[]=idf_escape($x);}}$H=driver()->select($a,$ml,$Z,$Pe,$Ni,$z,$D,true);if(!is_object($H))echo"<p class='error'>".(adminer()->error()?:lang(32))."\n";else{if(JUSH=="mssql"&&$D)$H->seek($z*$D);$td=[];$K=[];while($J=$H->fetch_assoc()){if($D&&JUSH=="oracle")unset($J["RNUM"]);$K[]=$J;}$bf=($z&&(support("cursor")?$_GET["next"]!="":count($K)>=$z));if(is_ajax()&&$bf)header("X-Next-Page: ".pagination_href($D+1));if($_GET["modify"]&&$K){$gh=max_input_vars(count($K[0])+1,20);echo($gh&&count($K)>$gh?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form method='post' enctype='multipart/form-data'".on_upload_progress($Un).">\n";if($_GET["page"]!="last"&&$z&&$Oe&&$dg&&JUSH=="sql")$ze=get_val(" SELECT FOUND_ROWS()");if(!$K)echo"<p class='message'>".lang(22)."\n";else{$Ua=adminer()->backwardKeys($a,$Fm);$Kk=[];reset($M);foreach($K[0]as$x=>$X){if(!isset($On[$x])){$X=idx($_GET["columns"],key($M))?:[];$Kk[$x]=["fun"=>$X["fun"],"col"=>($M?$X["col"]:$x)];next($M);}}echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$Oe&&$M?"":"<td class='hover check sticky'><input type='checkbox' id='all-page' class='jsonly' title='".lang(292)."'".on('click','formCheck','^check').">");$Qh=[];$tk=1;foreach($Kk
as$x=>$X){$n=$o[$X["col"]];$B=($n?adminer()->fieldName($n,$tk):($X["fun"]?($X["col"]!=""?h($X["col"]):"*"):h($x)));if($B!=""){$tk++;$Qh[$x]=$B;$d=idf_escape($x);$rf=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($x);$Kc="&desc[0]=1";$Rl=preg_replace('~ DESC( NULLS LAST)?$~','',$Ni[0]);$Tl=($Rl==$d||$Rl==$x);echo"<th id='th[".h(bracket_escape($x))."]'".($Tl?" aria-sort='".($Rl==$Ni[0]?"ascending":"descending")."'":"").">";$He=apply_sql_function(h($X["fun"]),$B);$Sl=isset($n["privileges"]["order"])||$X["fun"];echo($Sl?"<a href='".h($rf.($Tl&&$Rl==$Ni[0]?$Kc:''))."'>$He</a>":$He),doc_link([JUSH=>idx($n,"doc")]);$oh=($Sl?"<a href='".h($rf.$Kc)."' title='".lang(74)."' class='text'> ↓</a>":'');if(!$X["fun"]&&isset($n["privileges"]["where"]))$oh
.="<a href='#fieldset-search' title='".lang(18)."' class='text jsonly'".on('click','selectSearch',$x)."> =</a>";echo($oh?"<span class='column'>$oh</span>":"");}}$Ig=[];if($_GET["modify"]){foreach($K
as$J){foreach($J
as$x=>$X)$Ig[$x]=max($Ig[$x],min(40,utf8_length((string)$X)));}}$mf=[];$lf=[];foreach((array)$_GET["where"]as$X){$X+=["col"=>"","op"=>"","val"=>""];$Fb=$X["col"];$gl=$X["val"];if(!is_array($gl)&&($gl!=""||preg_match('~NULL$~',$X["op"]))&&(!$X["op"]||in_array($X["op"],adminer()->operators($S)))){$Kg=strtr(preg_quote($gl),["%"=>".*?","_"=>"."]);$Dj=["LIKE %%"=>$Kg,"ILIKE %%"=>$Kg,"REGEXP"=>$gl]+(JUSH=="pgsql"?["~"=>$gl,"~*"=>$gl]:[])+($Fb!=""?[]:["="=>'^'.preg_quote($gl).'\z',"IN"=>'^(?:'.implode("|",array_map('preg_quote',array_map('trim',explode(",",$gl)))).')\z',"LIKE"=>"^$Kg\\z","ILIKE"=>"^$Kg\\z","FIND_IN_SET"=>'(?<=^|,)'.preg_quote($gl).'(?=,|\z)',]);foreach(($Fb!=""?[$Fb=>$o[$Fb]]:$o)as$B=>$n){if($Fb!=""||is_searchable($n,$X)){$Ci=$X["op"]?:(!preg_match('~'.text_type().'~',$n["type"])?"IN":(preg_match('~%~',$gl)?"LIKE":"LIKE %%"));if(isset($Dj[$Ci])){$_b=preg_match('~^ILIKE|\*$~',$Ci)||($Ci!="~"&&preg_match('~^(sql|mssql|sqlite)$~',JUSH));$mf[$B][]="(?".($_b?"i":"").":$Dj[$Ci])";}elseif($Ci=="IS NULL"&&$Fb=="")$lf[$B]=true;}}}}echo($Ua?"<th>".lang(293):"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($K,$we)as$Kh=>$J){$Kn=unique_array($K[$Kh],$w);if(!$Kn){$Kn=[];foreach($K[$Kh]as$x=>$X){if(!in_array(idx(idx($Kk,$x,[]),"fun"),driver()->grouping))$Kn[$x]=$X;}}$Ln="";$s=0;foreach($Kn
as$x=>$X){$Jk=idx($Kk,$x,[]);if($M&&$Jk&&!isset($o[$Jk["col"]]))continue;$He=idx($Jk,"fun","");$Fb=($He?$Jk["col"]:$x);$n=(array)$o[$Fb];$cg=is_blob($n);if(!$He&&strlen($X)>64&&driver()->md5(idf_escape($Fb),$n)){$He="md5";$X=md5($cg?(string)driver()->value($X,$n):$X);}if($He){$Ln
.="&fun[$s]=".url_escape($He)."&col[$s]=".url_escape($Fb).($X!==null?"&val[$s]=".url_escape($X===false?"f":$X):"");$s++;}else$Ln
.="&".($X!==null?"where[".url_escape(bracket_escape($Fb))."]=".url_escape($X===false?"f":$X):"null[]=".url_escape($Fb));}echo"<tr>".(!$Oe&&$M?"":"<td class='hover check sticky'>".($dg||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$Ln)."' class='edit'>".lang(294)."</a> ").checkbox("check[]",substr($Ln,1),in_array(substr($Ln,1),(array)$_POST["check"])));foreach($J
as$x=>$X){if(isset($Qh[$x])){$He=$Kk[$x]["fun"];$Fb=$Kk[$x]["col"];$n=(array)$o[$x];if($X!=""&&(!isset($td[$x])||$td[$x]!=""))$td[$x]=(is_mail($X)?$Qh[$x]:"");$_="";if(is_blob($n)&&$X!="")$_=ME.'download='.url_escape($a).'&field='.url_escape($x).$Ln;if(!$_&&$X!==null){foreach((array)$we[$x]as$q){if(count($we[$x])==1||end($q["source"])==$x){$_="";foreach($q["source"]as$s=>$Ul)$_
.=where_link($s,$q["target"][$s],$K[$Kh][$Ul]);$_=($q["db"]!=""?preg_replace('~&db=[^&]*~',"&db=".url_escape($q["db"]),ME):ME).'select='.url_escape($q["table"]).$_;if($q["ns"]!="")$_=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($q["ns"]),$_);if(count($q["source"])==1)break;}}}if($He=="count"&&$Fb==""){$_=ME."select=".url_escape($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Kn))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($Kn
as$ng=>$W){if(idx(idx($Kk,$ng,[]),"fun")){$_="";break;}$_
.=where_link($s++,$ng,$W);}}$sf=select_value($X,$_,$n,$Ym,($He?[]:idx($mf,$x,[])));if($X===null&&!$He&&isset($lf[$x]))$sf="<mark>$sf</mark>";$u=bracket_escape($Ln);$t=h("val[$u][".bracket_escape($x)."]");$Qj=idx(idx($_POST["val"],$u),bracket_escape($x));$Rn=idx($n["privileges"],"update")&&!is_identity_always($n);$od=$K[$Kh][$x];$pd=!is_array($J[$x])&&!is_blob($n)&&is_utf8($od)&&!$He&&!$n["generated"]&&$Rn;$U=($He=="min"||$He=="max"?$o[$Fb]["type"]:$n["type"]);$Xm=preg_match('~text|json|lob~',$U);$eg=preg_match(number_type(),$U)||preg_match('~^(avg|ceil|char_length|count|count distinct|floor|len|length|round|sum|time_to_sec)$~',$He);echo"<td id='$t'".($eg&&($X===null||is_numeric(strip_tags($sf))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$pd&&$od!==null)||$Qj!==null){$We=h($Qj!==null?$Qj:$od);echo">".($Xm?"<textarea name='$t' cols='30' rows='".(substr_count($od,"\n")+1)."'>\n$We</textarea>":"<input name='$t' value='$We' size='$Ig[$x]'>");}else{$Tg=strpos($sf,"<i>…</i>");$nd=adminer()->editVal($od,$n);echo($Rn?" data-text='".($Tg?2:($Xm?1:0))."'".($pd?"":" data-warning='".lang(295)."'").($pd&&!$Tg&&html_entity_decode(strip_tags($sf),ENT_QUOTES,"UTF-8")!==$nd?" data-value='".h($nd)."'":""):"").">$sf";}}}if($Ua)echo"<td>";adminer()->backwardKeysPrint($Ua,$K[$Kh]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){$sa=get_settings("adminer_import");if($K||$D||$bf){$Ld=true;if($_GET["page"]!="last"){if(!$z||(count($K)<$z&&($K||!$D)))$ze=($D?$D*$z:0)+count($K);elseif(JUSH!="sql"||!$dg){$ze=($dg?null:found_rows($S,$Z));$Ld=!driver()->hasEstimatedRows();if($ze===null||(!$Ld&&$ze<max(1e4,2*($D+1)*$z))){$ze=first(slow_query(count_rows($a,$Z,$dg,$Oe)));$Ld=true;}}}if(!support("cursor"))$bf=(($ze===false?count($K)+1:$ze-$D*$z)>$z);$hj=($z&&($bf||$D));if($hj)echo($bf?'<p><a href="'.h(pagination_href($D+1)).'" class="loadmore"'.on('click','selectLoadMore',lang(296)).'>'.lang(297).'</a>':''),"\n";echo"<div class='footer'><div>\n";if($hj){$eh=($ze===false?$D+($K?(count($K)>=$z?2:1):0):floor(($ze-1)/$z));echo"<fieldset><legend>".lang(298)."</legend>";if(!support("cursor")){echo
pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($eh,$D+5);$s++)echo
pagination($s,$D);if($eh>0)echo($D+5<$eh?" …":""),($Ld&&$ze!==false?pagination($eh,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$eh'>".lang(299)."</a>");}else
echo
pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($bf?pagination($D+1,$D)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(300)."</legend>";$Sc=($Ld?"":"~ ").$ze;$ug=($ze!==false?($Ld?"":"~ ").lang(184,$ze):"");echo
checkbox("all",1,0,$ug,on('click','countRows',$Sc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".lang(185)."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>',lang(301),'</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'',lang(24),'\'',($_GET["modify"]||$_POST["val"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>',lang(154),' <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'',lang(20),'\'>
<input type=\'submit\' name=\'clone\' value=\'',lang(286),'\'>
<input type=\'submit\' name=\'delete\' value=\'',lang(28),'\'',confirm(),'>
</div></fieldset>
';$xe=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]||($d["col"]!=""&&!isset($o[$d["col"]]))){unset($xe['sql']);break;}}if($xe){print_fieldset("export",lang(91)." <span id='selected2'></span>");$cj=adminer()->dumpOutput();echo($cj?html_select("output",$cj,$sa["output"])." ":""),html_select("format",$xe,$sa["format"])," <input type='submit' name='export' value='".lang(91)."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($td,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".lang(90)."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",($Un?input_hidden(ini_get("session.upload_progress.name"),$Un):""),file_input(" name='csv_file'"," ".html_select("separator",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$sa["format"])." <input type='submit' name='import' value='".lang(90)."'>".($Un?" <progress class='jsonly hidden' max='1' value='0'></progress>":"")),"</span>";echo
input_token(),"</form>\n",(!$Oe&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?lang(97):lang(96));$no=($P?adminer()->showStatus():adminer()->showVariables());if(!$no)echo"<p class='message'>".lang(22)."\n";else{echo"<table>\n";foreach($no
as$J){echo"<tr>";$x=array_shift($J);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($J
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$um=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];foreach(table_status()as$B=>$S){json_row("Comment-$B",h($S["Comment"]).($S["Error"]?" <span class='error'>".h($S["Error"])."</span>":""));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(["Engine","Collation"]as$x)json_row("$x-$B",h($S[$x]));foreach(array_keys($um+["Auto_increment"=>0,"Rows"=>0])as$x){if(array_key_exists($x,$S))json_row("$x-$B",format_status($S,$x));if($S[$x]!=""&&isset($um[$x]))$um[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}}}if(function_exists('Adminer\db_status'))$um=db_status();foreach($um
as$x=>$X)json_row("sum-$x",adminer()->formatSizeValue($X));json_row("");}else{foreach(count_tables(adminer()->databases(false))as$k=>$X){json_row("tables-$k",format_number($X));json_row("size-$k",db_size($k));}json_row("");}exit;}else{if(!isset($_GET["select"])&&support("single_table")){$T=tables_list();if($T)redirect(ME.(support("table")?"table=":"select=").url_escape(key($T)));}$lh=ME.(isset($_GET["select"])?"select=&":"");$Pm=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Pm&&!$m&&!$_POST["search"]){$H=true;$ph="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$H=truncate_tables($_POST["tables"]);$ph=lang(302);}elseif($_POST["move"]){$H=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ph=lang(303);}elseif($_POST["copy"]){$H=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ph=lang(304);}elseif($_POST["drop"]){if($_POST["views"])$H=drop_views($_POST["views"]);if($H&&$_POST["tables"])$H=drop_tables($_POST["tables"]);$ph=lang(305);}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$J)$ph
.="<b>".h($R)."</b>: ".h($J["integrity_check"])."<br>";}}elseif(JUSH=="mssql"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("DBCC CHECKTABLE (".q(table($R)).") WITH TABLERESULTS")as$J)$ph
.="<b>".h($R)."</b>: ".h($J["MessageText"])."<br>";}}elseif(JUSH!="sql"){$H=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$ph=lang(306);}elseif(!$_POST["tables"])$ph=lang(19);elseif($H=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($J=$H->fetch_assoc())$ph
.="<b>".h($J["Table"])."</b>: ".h($J["Msg_text"])."<br>";}queries_redirect(relative_uri(),$ph,$H);}page_header(($_GET["ns"]==""?lang(17).": ".h(DB):lang(16).": ".h($_GET["ns"])),$m,true);if(adminer()->homepage()){if($_GET["ns"]!==""){if(support("scheme")&&support("comment")){$f=schema_comment($_GET["ns"]);if($f!="")echo"<p class='nowrap'>".lang(66).": ".adminer()->commentValue('SCHEMA',$f)."\n";}$Ni=$_GET["order"];$Ee=($Ni||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".lang(307)."</h3>\n";$Om=($Ee?table_status():tables_list());$ke=$_GET["filter"];if($ke!=""){foreach($Om
as$R=>$P){if(stripos("$R",$ke)===false)unset($Om[$R]);}}if(!$Om)echo"<p class='message'>".lang(19)."\n";else{echo"<form method='post'>\n";if(support("table")){echo"<fieldset><legend>".lang(15)." <span id='selected2'></span></legend><div>";$Ci=idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%");echo
html_select("op",adminer()->operators(),$Ci);$G=($_POST?$_POST["query"]:$_GET["query"]);echo" <input type='search' name='query' value='".h($G!=""?$G:$ke)."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".lang(18)."'>\n","</div></fieldset>\n";if(!$m&&(!$_POST||$_POST["search"])&&$G!="")search_tables($G,$Ci);}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr>','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.lang(178).'"'.on('click','formCheck','^(tables|views)\[').'>','<th class="sticky"'.(!$Ni&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr($lh,0,-1)).'">'.lang(159).'</a>';$e=["Engine"=>[lang(308).doc_link(['sql'=>'storage-engines.html'])]];$Ib=collations();if($Ib)$e["Collation"]=[lang(150).doc_link(['sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/'])];if(function_exists('Adminer\alter_table'))$e["Data_length"]=[lang(309).doc_link(['sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'refrn/ALL_TABLES.html']),"create",lang(60),];if(support("indexes"))$e["Index_length"]=[lang(310).doc_link(['sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"indexes",lang(162),];$e["Data_free"]=[lang(311).doc_link(['sql'=>'show-table-status.html']),"edit",lang(61)];if(function_exists('Adminer\alter_table'))$e["Auto_increment"]=[lang(67).doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/']),"auto_increment=1&create",lang(60),];$e["Rows"]=[lang(312).doc_link(['sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'refrn/ALL_TABLES.html']),"select",lang(57),];if(support("comment"))$e["Comment"]=[lang(66).doc_link(['sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE','cockroach'=>'comment-on']),];$Ia=['Engine','Collation','Comment'];foreach($e
as$x=>$d)echo"<th".($Ni==$x?" aria-sort='".(in_array($x,$Ia)?"ascending":"descending")."'":"")."><a href='".h($lh)."order=$x'>$d[0]</a>";echo"<tbody>\n";if($Ni){uasort($Om,function($ia,$Ra)use($Ni,$Ia){$I=($ia[$Ni]<$Ra[$Ni]?-1:($ia[$Ni]>$Ra[$Ni]?1:0));return(in_array($Ni,$Ia)?$I:-$I);});}$T=0;$um=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];foreach($Om
as$B=>$P){$qo=($Ee?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$P=($Ee?$P:['Engine'=>$P]);$t=h("Table-".$B);echo'<tr><td class="hover">'.checkbox(($qo?"views[]":"tables[]"),$B,in_array("$B",$Pm,true),"","","",$t),'<th class="sticky">'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($B)."' title='".lang(58)."' id='$t'>".h($B).'</a>':h($B));if($qo&&!preg_match('~materialized~i',$P['Engine'])){$dn=lang(158);echo'<td colspan="'.(count($e)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($B)."' title='".lang(59)."'>$dn</a>":$dn),"<td align='right'><a href='".h(ME)."select=".url_escape($B)."' title='".lang(57)."'>?</a>";if(support("comment"))echo'<td>'.h($P['Comment']);}else{if($Ee){foreach(array_keys($um)as$x)$um[$x]+=($P["Engine"]!="InnoDB"||$x!="Data_free"?idx($P,$x):0);}foreach($e
as$x=>$d){$t=" id='$x-".h($B)."'";echo($d[1]?"<td align='right'>".($x=="Index_length"&&$P["invalid_index"]?"<span class='error'>INVALID</span> ":"")."<a href='".h(ME."$d[1]=").url_escape($B)."'$t title='$d[2]'>".format_status($P,$x)."</a>":"<td$t>".h(idx($P,$x,'?')).($x=="Comment"&&$P["Error"]?" <span class='error'>".h($P["Error"])."</span>":""));}$T++;}echo"\n";}echo"<tr><td class='hover'><th class='sticky'>".lang(287,count($Om)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),($Ib?"<td>".h(db_collation(DB,$Ib)):'');if($Ee&&function_exists('Adminer\db_status'))$um=db_status();foreach($um
as$x=>$tm)echo($e[$x]?"<td align='right' id='sum-$x'>".($Ee?adminer()->formatSizeValue($tm):""):"");echo"\n","</table>\n",($Ee?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$io="<input type='submit' value='".lang(313)."'".on_help("VACUUM")."> ";$Hi="<input type='submit' name='optimize' value='".lang(314)."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$ak=(JUSH=="sqlite"?$io."<input type='submit' name='check' value='".lang(315)."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$io.$Hi:(JUSH=="mssql"?"<input type='submit' name='check' value='".lang(315)."'".on_help("DBCC CHECKTABLE")."> ":(JUSH=="sql"?"<input type='submit' value='".lang(316)."'".on_help("ANALYZE TABLE")."> ".$Hi."<input type='submit' name='check' value='".lang(315)."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".lang(317)."'".on_help("REPAIR TABLE")."> ":"")))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".lang(318)."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".lang(155)."'".confirm().on_help("DROP TABLE").">":"");echo($ak?"<div class='footer'><div>\n<fieldset><legend>".lang(154)." <span id='selected'></span></legend><div>$ak\n</div></fieldset>\n":"");$j=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($j)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".lang(319)." <span id='selected3'></span></legend><div>";$k=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($j?html_select("target",$j,$k):'<input name="target" value="'.h($k).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".lang(141)."'>",(support("copy")?" <input type='submit' name='copy' value='".lang(29)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(320)):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$T).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".lang(92)."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".lang(93)."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".lang(87)."</h3>\n";$Xk=routines();if($Xk){echo"<table class='odds'>\n",'<thead><tr><th>'.lang(219).'<th>'.lang(65).'<th>'.lang(257)."<td class='hover'><tbody>\n";foreach($Xk
as$J){$B=($J["SPECIFIC_NAME"]==$J["ROUTINE_NAME"]?"":"&name=".url_escape($J["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($J["SPECIFIC_NAME"]).$B).'"'.' title="'.($J["DTD_IDENTIFIER"]=="trigger"?lang(169):lang(229)).'">'.h($J["ROUTINE_NAME"]).'</a>','<td>'.h($J["ROUTINE_TYPE"]),'<td>'.h($J["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($J["SPECIFIC_NAME"]).$B).'">'.lang(165)."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.lang(256).'</a>':'').'<a href="'.h(ME).'function=">'.lang(255)."</a>\n","</div>\n";}if(support("sequence")){echo"<div>\n","<h3 id='sequences'>".lang(88)."</h3>\n";$xl=get_vals("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." ORDER BY relname");if($xl){echo"<table class='odds'>\n","<thead><tr><th>".lang(219)."<tbody>\n";foreach($xl
as$X)echo"<tr><th><a href='".h(ME)."sequence=".url_escape($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."sequence='>".lang(262)."</a>\n","</div>\n";}if(support("type")){echo"<div>\n","<h3 id='user-types'>".lang(0)."</h3>\n";$fo=types();if($fo){echo"<table class='odds'>\n","<thead><tr><th>".lang(219)."<tbody>\n";foreach($fo
as$X)echo"<tr><th><a href='".h(ME)."type=".url_escape($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."type='>".lang(267)."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".lang(89)."</h3>\n";$K=get_rows("SHOW EVENTS");if($K){echo"<table>\n","<thead><tr><th>".lang(219)."<th>".lang(321)."<th>".lang(247)."<th>".lang(248)."<td class='hover'><tbody>\n";foreach($K
as$J)echo"<tr>","<th>".h($J["Name"]),"<td>".($J["Execute at"]?lang(322)."<td>".h($J["Execute at"]):lang(249)." ".h($J["Interval value"])." ".h($J["Interval field"])."<td>".h($J["Starts"])),"<td>".h($J["Ends"]),'<td class="hover"><a href="'.h(ME).'event='.url_escape($J["Name"]).'">'.lang(165).'</a>';echo"</table>\n";$Id=get_val("SELECT @@event_scheduler");if($Id&&$Id!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Id)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.lang(246)."</a>\n","</div>\n";}}elseif(support("extension")){$Ud=get_rows("SELECT e.extname, e.extversion, n.nspname, obj_description(e.oid, 'pg_extension') AS comment
FROM pg_extension e
JOIN pg_namespace n ON n.oid = e.extnamespace
ORDER BY e.extname");if($Ud){echo"<div>\n","<h3 id='extensions'>".lang(94)."</h3>\n","<table class='odds'>\n","<thead><tr><th>".lang(219)."<th>".lang(323)."<th>".lang(16)."<th>".lang(66)."<tbody>\n";foreach($Ud
as$J)echo"<tr><th><code class='jush-pgsqlext'>".h($J["extname"])."</code>","<td>".h($J["extversion"]),"<td><a href='".h(substr(ME,0,-1).url_escape($J["nspname"]))."'>".h($J["nspname"])."</a>","<td>".h($J["comment"]),"\n";echo"</table>\n","</div>\n";}}}}page_footer();