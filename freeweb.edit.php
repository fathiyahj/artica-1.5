<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-tabs"])){popup_tabs();exit;}
	if(isset($_GET["params"])){params();exit;}
	if(isset($_GET["params2"])){params2();exit;}
	if(isset($_GET["enable_ldap_authentication"])){params_enable_ldap_authentication();exit;}
	if(isset($_POST["AddDefaultCharset"])){OthersValuesSave();exit;}
	if(isset($_GET["uid-check"])){uid_check();exit;}
	if(isset($_GET["groupwares"])){groupwares_index();exit();}
	if(isset($_POST["FreeWebToGroupWare"])){groupwares_save();exit;}
	if(isset($_POST["FreeWebsRebuildvHosts"])){FreeWebsRebuildvHosts();exit;}
	if(isset($_POST["FreeWebsRebuildGroupware"])){FreeWebsRebuildGroupware();exit;}
	
	if(isset($_GET["webserver-aliases"])){webserver_aliases_list();exit;}
	
	
	
	if(isset($_GET["authip-add"])){authip_add();exit;}
	if(isset($_GET["authip-del"])){authip_del();exit;}
	if(isset($_GET["authip-list"])){authip_list();exit;}
	if(isset($_GET["LimitByIp"])){authip_enable();exit;}
	
	if(isset($_GET["loops-list"])){loops_list();exit;}
	
	
	if(isset($_GET["rewrite"])){rewrite_js();exit;}
	if(isset($_GET["rewrite-tabs"])){rewrite_tabs();exit;}
	if(isset($_GET["rewrite-source"])){rewrite_source();exit;}
	if(isset($_POST["rewrite-source"])){rewrite_source_save();exit;}
	
	if(isset($_GET["servername"])){Save();exit;}
	
	js();
	
	
	
function rewrite_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{RewriteRules}::{$_GET["servername"]}");
	echo "YahooWin6('800','$page?rewrite-tabs=yes&servername={$_GET["servername"]}','$title');";	
	
	
}	

function rewrite_source_save(){
	$q=new mysql();
	
	$sql="UPDATE freeweb SET `mod_rewrite`='{$_POST["rewrite-source"]}' WHERE servername='{$_POST["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "Failed:$q->mysql_error";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-restart=yes");	
	
}

function rewrite_source(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT mod_rewrite FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$mod_rewrite=base64_decode($ligne["mod_rewrite"]);
	
$tt=base64_encode('RewriteEngine on
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
# REwrite for W3 Total Cache
RewriteCond %{HTTP_USER_AGENT} (2\.0\ mmp|240x320|alcatel|amoi|asus|au\-mic|audiovox|avantgo|benq|bird|blackberry|blazer|cdm|cellphone|danger|ddipocket|docomo|dopod|elaine/3\.0|ericsson|eudoraweb|fly|haier|hiptop|hp\.ipaq|htc|huawei|i\-mobile|iemobile|j\-phone|kddi|konka|kwc|kyocera/wx310k|lenovo|lg|lg/u990|lge\ vx|midp|midp\-2\.0|mmef20|mmp|mobilephone|mot\-v|motorola|netfront|newgen|newt|nintendo\ ds|nintendo\ wii|nitro|nokia|novarra|o2|openweb|opera\ mobi|opera\.mobi|palm|panasonic|pantech|pdxgw|pg|philips|phone|playstation\ portable|portalmmm|ppc|proxinet|psp|pt|qtek|sagem|samsung|sanyo|sch|sec|sendo|sgh|sharp|sharp\-tq\-gx10|small|smartphone|softbank|sonyericsson|sph|symbian|symbian\ os|symbianos|toshiba|treo|ts21i\-10|up\.browser|up\.link|uts|vertu|vodafone|wap|willcome|windows\ ce|windows\.ce|winwap|xda|zte) [NC]
RewriteRule .* - [E=W3TC_UA:_low]
RewriteCond %{HTTP_USER_AGENT} (acer\ s100|android|archos5|blackberry9500|blackberry9530|blackberry9550|cupcake|docomo\ ht\-03a|dream|htc\ hero|htc\ magic|htc_dream|htc_magic|incognito|ipad|iphone|ipod|lg\-gw620|liquid\ build|maemo|mot\-mb200|mot\-mb300|nexus\ one|opera\ mini|samsung\-s8000|series60.*webkit|series60/5\.0|sonyericssone10|sonyericssonu20|sonyericssonx10|t\-mobile\ mytouch\ 3g|t\-mobile\ opal|tattoo|webmate|webos) [NC]
RewriteRule .* - [E=W3TC_UA:_high]
RewriteCond %{HTTPS} =on
RewriteRule .* - [E=W3TC_SSL:_ssl]
RewriteCond %{SERVER_PORT} =443
RewriteRule .* - [E=W3TC_SSL:_ssl]
RewriteCond %{HTTP:Accept-Encoding} gzip
RewriteRule .* - [E=W3TC_ENC:.gzip]
RewriteCond %{REQUEST_METHOD} !=POST
RewriteCond %{QUERY_STRING} =""
RewriteCond %{REQUEST_URI} \/$
RewriteCond %{REQUEST_URI} !(\/wp-admin\/|\/xmlrpc.php|\/wp-(app|cron|login|register|mail)\.php|wp-.*\.php|index\.php) [NC,OR]
RewriteCond %{REQUEST_URI} (wp-comments-popup\.php|wp-links-opml\.php|wp-locations\.php) [NC]
RewriteCond %{HTTP_COOKIE} !(comment_author|wp-postpass|wordpress_\[a-f0-9\]\+|wordpress_logged_in) [NC]
RewriteCond "/var/www/yourserver/www/wp-content/w3tc/pgcache/$1/_index%{ENV:W3TC_UA}%{ENV:W3TC_SSL}.html%{ENV:W3TC_ENC}" -f
RewriteRule (.*) "/wp-content/w3tc/pgcache/$1/_index%{ENV:W3TC_UA}%{ENV:W3TC_SSL}.html%{ENV:W3TC_ENC}" [L]
# END W3TC Page Cache');
	
	$html="
	<div style='font-size:16px;'>{$_GET["servername"]}</div>
	<div style='margin:5px;text-align:right'><a href=\"javascript:blur();\" OnClick=\"javascript:RewriteExample()\" style='text-decoration:underline;font-size:13px'>{example}</a></div>
	<textarea style='width:100%;height:80%;font-size:13px;border:4px solid #CCCCCC;font-family:\"Courier New\",
	Courier,monospace;background-color:white;color:black' id='rewrite-source-edit'>$mod_rewrite</textarea>
	<center style='margin:5px'>". button("{edit}","SaveReWriteRule()")."</center>
	
<script>
	function RewriteExample(){
		var example='$tt'
		document.getElementById('rewrite-source-edit').value=base64_decode(example);
	}
	
		var x_SaveReWriteRule=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}	
			RefreshTab('main_config_rewritetabs');
		}
	
	
		function SaveReWriteRule(){
			var XHR = new XHRConnection();
			var content=base64_encode(document.getElementById('rewrite-source-edit').value);
			XHR.appendData('rewrite-source',content);
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'POST',x_SaveReWriteRule);
		}
	
</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function rewrite_tabs(){
	
	$tpl=new templates();	
	$page=CurrentPageName();
	
	
	$array["rewrite-source"]='{source}';
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_rewritetabs style='width:100%;height:670px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_rewritetabs\").tabs();});
		</script>";		
	
}
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sql="SELECT mod_rewrite FROM freeweb WHERE servername='{$_GET["hostname"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
	$title=$tpl->_ENGINE_parse_body("{free_web_servers}::{$ligne["ou"]}&nbsp;&raquo;&nbsp;{$_GET["hostname"]}");
	echo "YahooWin5('890','$page?popup-tabs=yes&servername={$_GET["hostname"]}','$title');";
	}
	
function groupwares_save(){
	$sql="UPDATE freeweb SET groupware='{$_POST["FreeWebToGroupWare"]}' WHERE servername='{$_POST["servername"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='DELETE_FREEWEB' AND `servername`='{$ligne["servername"]}'";
	$ligneDrup=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if(!is_numeric($ligneDrup["ID"])){$ligneDrup["ID"]=0;}
	if($ligneDrup["ID"]==0){
		$sql="INSERT INTO drupal_queue_orders(`ORDER`,`servername`) VALUES('INSTALL_GROUPWARE','{$_POST["servername"]}')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
	}
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");	
}
	
	
function groupwares_index(){
	$h=new vhosts();
	$hash=$h->listOfAvailableServices(true);
	$sql="SELECT groupware FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));		
	if($ligne["groupware"]<>null){
		$groupware_text="
		<table style='width:100%' class=form>
		<tr>
			<td width=1% valign='top'><img src='img/{$h->IMG_ARRAY_64[$ligne["groupware"]]}'></td>
			<td valign='top' width=99%>
				<div style='font-size:16px'>{current}:&nbsp;<strong>&laquo;&nbsp;{$hash[$ligne["groupware"]]}&nbsp;&raquo;</strong><hr>
					<i style='font-size:13px'>{{$h->TEXT_ARRAY[$ligne["groupware"]]["TEXT"]}}</i>
				</div>
			</td>
		</tr>
		</table>";
		
	}
	
	$page=CurrentPageName();
	while (list ($key, $title) = each ($hash) ){
		if($h->IMG_ARRAY_64[$key]==null){continue;}
		$js="javascript:FreeWebToGroupWare('$key');";
		
		$tr[]=$tpl->_ENGINE_parse_body(Paragraphe($h->IMG_ARRAY_64[$key],$title,"{{$h->TEXT_ARRAY[$key]["TEXT"]}}",$js));
	}
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	
$groupware_text=$tpl->_ENGINE_parse_body($groupware_text);
$freeweb_groupware_explain=$tpl->_ENGINE_parse_body("{freeweb_groupware_explain}");
$html="
<div class=explain>$freeweb_groupware_explain</div>
$groupware_text
<div style='width:700px'>". implode("\n",$tables)."</div>
<script>
		var x_FreeWebToGroupWare=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);return;}	
			RefreshTab('main_config_freewebedit');
		}
	
	
		function FreeWebToGroupWare(key){
			var XHR = new XHRConnection();
			XHR.appendData('FreeWebToGroupWare',key);
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebToGroupWare);
		}
</script>


";

echo $html;
	
	
	
}	

function popup_tabs(){
	
	$tpl=new templates();	
	$page=CurrentPageName();
	$sock=new sockets();
	$q=new mysql();
	$ApacheDisableModDavFS=$sock->GET_INFO("ApacheDisableModDavFS");
	$FreeWebEnableModFcgid=$sock->GET_INFO("FreeWebEnableModFcgid");
	if(!is_numeric($FreeWebEnableModFcgid)){$FreeWebEnableModFcgid=0;}
	if(!is_numeric($ApacheDisableModDavFS)){$ApacheDisableModDavFS=0;}
	
	
	
	
	
	
	if($_GET["servername"]<>null){
		$table_name=$q->APACHE_TABLE_NAME($_GET["servername"]);
		if($q->TABLE_EXISTS($table_name, "apachelogs")){$array["statistics"]='{statistics}';}
		$table_name="apache_stats_".date('Ym');
		$sql="SELECT COUNT(servername) as tcount FROM $table_name WHERE servername='{$_GET["servername"]}'";
		if($q->mysql_error){if(!preg_match("#doesn.+?t exist#", $q->mysql_error)){echo "<H2>$q->mysql_error</H2>";}}else{$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));}
		if($ligne["tcount"]>0){$array["status"]='{status}';}
	}
	
	//http://jmatrix.net/dao/case/case.jsp?case=7F000001-1C888D9-111189408B9-80
	
	$array["popup"]='{website}';
	

	
	if($_GET["servername"]<>null){
		$users=new usersMenus();
		if($users->APACHE_MOD_FCGID && $users->APACHE_MOD_SUEXEC){
			if($FreeWebEnableModFcgid==1){
				$array["ModFcgid"]='{APP_PHPFCGI}';
			}
		}
		
		
		$apache=new freeweb($_GET["servername"]);
		
		if($apache->groupware=="DRUPAL"){
			$array["drupal"]='{APP_DRUPAL}';
		}			
		
		if($apache->UseReverseProxy==1){
			$array["reverse"]='{reverse_proxy}';
		}
		$array["params"]='{security}';
		$array["params2"]='{parameters}';
		
		
		
		
		
		if($users->APACHE_MODE_WEBDAV){
			if($ApacheDisableModDavFS==0){
				$array["webdav"]='{TAB_WEBDAV}';
			}
		}
		
		if($users->APACHE_MOD_QOS){
			$array["qos"]='{QOS}';
			
		}
		
		if($users->APACHE_MOD_CACHE){
			$array["mod_cache"]='{cache_engine}';
		}
		
		if($apache->groupware<>"DRUPAL"){
			$array["groupwares"]='{groupwares}';
		}
		
		if($apache->groupware==null){
			$array["aliases"]='{aliases}';
		}
		
		if($apache->groupware=="GROUPOFFICE"){
			unset($array["groupwares"]);
			$array["groupoffice"]='{APP_GROUPOFFICE}';
		}
		

		
		$users=new usersMenus();
		if($users->awstats_installed){
			//$array["awstats"]='{APP_AWSTATS}';
		}
	}
	
	while (list ($num, $ligne) = each ($array) ){
		if($num=="statistics"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.statistics.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		if($num=="status"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.status.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		if($num=="ModFcgid"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.fcgid.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="awstats"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"awstats.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}
		
		if($num=="webdav"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.webdav.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}

		if($num=="reverse"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.reverse.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}			

		if($num=="qos"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.qos.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}		
		
		if($num=="mod_cache"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.cache.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}

		if($num=="aliases"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.edit.aliases.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}			
		
		if($num=="drupal"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.drupal.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
				continue;
		}

		if($num=="groupoffice"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"freeweb.groupoffice.php?servername={$_GET["servername"]}&freewebs=1&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&servername={$_GET["servername"]}&group_id={$_REQUEST["group_id"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_freewebedit style='width:100%;height:690px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_freewebedit\").tabs();});
		</script>";		
	
}

function params_enable_ldap_authentication(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize(base64_decode($ligne["Params"]));	
	$Params["LDAP"]["enabled"]=$_GET["enable_ldap_authentication"];
	$Params["LDAP"]["authentication_banner"]=$_GET["authentication_banner"];
	$Params["LDAP"]["EnableLDAPAllSubDirectories"]=$_GET["EnableLDAPAllSubDirectories"];
	$Params["SECURITY"]["ServerSignature"]=$_GET["ApacheServerSignature"];
	$Params["SECURITY"]["DisableHtAccess"]=$_GET["DisableHtAccess"];
	
	
	
	$data=addslashes(base64_encode(serialize($Params)));
	$sql="UPDATE freeweb SET `Params`='$data' WHERE servername='{$_GET["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-restart=yes");
	
	
}

function params2(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	$users=new usersMenus();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize(base64_decode($ligne["Params"]));
	
	if(!isset($Params["JkMount"])){$Params["JkMount"]=0;}
	
	$APACHE_MOD_TOMCAT=0;
	if($users->TOMCAT_INSTALLED){if($user->APACHE_MOD_TOMCAT){$APACHE_MOD_TOMCAT=1;}}
	
	
	if($users->OPENEMM_INSTALLED){
		$OpenEMMEnable=$sock->GET_INFO("OpenEMMEnable");
		if(!is_numeric($OpenEMMEnable)){$OpenEMMEnable=1;}
		if($OpenEMMEnable==1){$APACHE_MOD_TOMCAT=0;}
	}
	
	$mod_pagespeedEnable=1;
	$mod_pagespeed="
		<tr>
			<td class=legend>{enable_mod_pagespeed}:</td>
			<td>". Field_checkbox("PageSpeed",1,$Params["PageSpeed"])."</td>
			<td><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('freeweb.mod.pagespeed.php?servername={$_GET["servername"]}')\" style='font-size:13px;text-decoration:underline'>{edit}</a></td>
			<td width=1%>". help_icon("{enable_mod_pagespeed_explain}")."</td>
		</tr>
	";

	if(!$users->APACHE_MOD_PAGESPEED){
		$mod_pagespeedEnable=0;
	$mod_pagespeed="
		<tr>
			<td class=legend>{enable_mod_pagespeed}:</td>
			<td>". Field_checkbox("PageSpeed",1,$Params["PageSpeed"])."</td>
			<td><span style='font-size:13px;text-decoration:underline;color:#CCCCCC'>{edit}</a></td>
			<td width=1%>". help_icon("{enable_mod_pagespeed_explain}")."</td>
		</tr>
	";		
		
	}
	
	
	
$html="
	<div style='font-size:16px;font-weight:bold'>{language}</div>
	<div id='other-apache-div'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>Default Charset:</td>
		<td>". Field_text("AddDefaultCharset",$Params["AddDefaultCharset"],"font-size:13px;padding:3px;width:220px")."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{AddDefaultCharset_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{enable_tomcatjconet}:</td>
		<td>". Field_checkbox("JkMount",1,$Params["JkMount"])."</td>
		<td>&nbsp;</td>
		<td width=1%>". help_icon("{jkMount_explain}")."</td>
	</tr>	
	$mod_pagespeed
	<tr>
		<td colspan=4 align='right'><hr>". button("{apply}","ApacheOthersValuesSave()")."</td>
	</tr>
	</table>
	<br>
	</div>
	
	<script>
		var x_ApacheOthersValuesSave=function (obj) {
			var results=obj.responseText;			
			if(results.length>0){alert(results);}			
			RefreshTab('main_config_freewebedit');
		}
	
	
		function ApacheOthersValuesSave(){
			var XHR = new XHRConnection();
			XHR.appendData('AddDefaultCharset',document.getElementById('AddDefaultCharset').value);
			if(document.getElementById('JkMount').checked){XHR.appendData('JkMount',1);}else{XHR.appendData('JkMount',0);}
			if(document.getElementById('PageSpeed').checked){XHR.appendData('PageSpeed',1);}else{XHR.appendData('PageSpeed',0);}
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'POST',x_ApacheOthersValuesSave);
		} 
		
		function Checkjkmount(){
			var APACHE_MOD_TOMCAT=$APACHE_MOD_TOMCAT;
			var mod_pagespeedEnable=$mod_pagespeedEnable;
			if(APACHE_MOD_TOMCAT==0){document.getElementById('JkMount').disabled=true;document.getElementById('JkMount').checked=false;}
			if(mod_pagespeedEnable==0){document.getElementById('PageSpeed').disabled=true;document.getElementById('PageSpeed').checked=false;}
		}
		Checkjkmount();
	</script>";	

	echo $tpl->_ENGINE_parse_body($html);
	
}

function OthersValuesSave(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_POST["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize(base64_decode($ligne["Params"]));
	$Params["AddDefaultCharset"]=$_POST["AddDefaultCharset"];
	$Params["JkMount"]=$_POST["JkMount"];
	$Params["PageSpeed"]=$_POST["PageSpeed"];
	$data=addslashes(base64_encode(serialize($Params)));
	$sql="UPDATE freeweb SET `Params`='$data' WHERE servername='{$_POST["servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-restart=yes");	
	
	
}


function params(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	$FreeWebsEnableModSecurity=$sock->GET_INFO("FreeWebsEnableModSecurity");
	$FreeWebsEnableModEvasive=$sock->GET_INFO("FreeWebsEnableModEvasive");
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	$Params=unserialize(base64_decode($ligne["Params"]));
	$apache_auth_ip_explain=$tpl->javascript_parse_text("{apache_auth_ip_explain}");
	$users=new usersMenus();
	$APACHE_MOD_AUTHNZ_LDAP=0;
	$APACHE_MOD_GEOIP=0;
	if($users->APACHE_MOD_AUTHNZ_LDAP){$APACHE_MOD_AUTHNZ_LDAP=1;}
	if($users->APACHE_MOD_GEOIP){$APACHE_MOD_GEOIP=1;}
	$ServerSignature=$sock->GET_INFO("ApacheServerSignature");
	if(!is_numeric($ServerSignature)){$ServerSignature=1;}	
	if(!is_numeric($FreeWebsEnableModSecurity)){$FreeWebsEnableModSecurity=0;}
	if(!is_numeric($FreeWebsEnableModEvasive)){$FreeWebsEnableModEvasive=0;}
	$ZarafaWebNTLM=0;
	if($ligne["groupware"]=="ZARAFA"){
		$ZarafaWebNTLM=$sock->GET_INFO("ZarafaWebNTLM");
		if(!is_numeric($ZarafaWebNTLM)){$ZarafaWebNTLM=0;}
		
	}
	
	
	
	$authentication_banner=$Params["LDAP"]["authentication_banner"];
	$EnableLDAPAllSubDirectories=$Params["LDAP"]["EnableLDAPAllSubDirectories"];
	if(strlen($authentication_banner)<3){
		$authentication_banner=base64_encode($tpl->javascript_parse_text("{$_GET["servername"]}::{authentication}"));
	}

	$ApacheServerSignature=$Params["SECURITY"]["ServerSignature"];
	$DisableHtAccess=$Params["SECURITY"]["DisableHtAccess"];
	if(!is_numeric($ApacheServerSignature)){$ApacheServerSignature=$ServerSignature;}
	if(!is_numeric($EnableLDAPAllSubDirectories)){$EnableLDAPAllSubDirectories=0;}

$mod_security="
	<tr>
		<td class=legend>{security_enforcement}:</td>
		<td><a href=\"javascript:blur();\" OnClick=\"Loadjs('freeweb.mod.security.php?servername={$_GET["servername"]}');\"
		style='font-size:13px;text-decoration:underline'>{edit}<a></td>
	</tr>
";

$mod_geoip="	<tr>
		<td class=legend style='color:#CCCCCC'>{country_block}:</td>
		<td><span style='font-size:13px;text-decoration:underline;color:#CCCCCC'>{edit}</span></td>
	</tr>
";




$mod_evasive="
	<tr>
		<td class=legend>{DDOS_prevention}:</td>
		<td><a href=\"javascript:blur();\" OnClick=\"Loadjs('freeweb.mod.evasive.php?servername={$_GET["servername"]}');\"
		style='font-size:13px;text-decoration:underline'>{edit}<a></td>
	</tr>
";
	
if($FreeWebsEnableModSecurity==0){
	$mod_security="
	<tr>
		<td class=legend style='color:#CCCCCC'>{security_enforcement}:</td>
		<td><a href=\"javascript:blur();\"
		style='font-size:13px;color:#CCCCCC'>{edit}<a></td>
	</tr>
";
}
if($FreeWebsEnableModEvasive==0){
	$mod_evasive="
	<tr>
		<td class=legend style='color:#CCCCCC'>{DDOS_prevention}:</td>
		<td><a href=\"javascript:blur();\"
		style='font-size:13px;color:#CCCCCC'>{edit}<a></td>
	</tr>
";
}

if($APACHE_MOD_GEOIP==1){

$mod_geoip="	<tr>
		<td class=legend>{country_block}:</td>
		<td><a href=\"javascript:blur();\" OnClick=\"Loadjs('freeweb.mode.geoip.php?servername={$_GET["servername"]}');\"
		style='font-size:13px;text-decoration:underline'>{edit}<a></td>
	</tr>
";
}


	
	
	$html="
	<div style='font-size:16px;font-weight:bold'>{authentication}</div>
	<div id='auth-apache-div'>
	<input type='hidden' id='EnableLDAPAllSubDirectories' value='$EnableLDAPAllSubDirectories'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable_ldap_authentication}:</td>
		<td>". Field_checkbox("enable_ldap_authentication",1,$Params["LDAP"]["enabled"],"CheckApacheLdap()")."</td>
	</tr>
	<tr>
		<td class=legend>{authentication_banner}:</td>
		<td>". Field_text("authentication_banner",base64_decode($authentication_banner),"font-size:13px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td class=legend>{members}:</td>
		<td><input type='button' OnClick=\"javascript:Loadjs('freeweb.edit.ldap.users.php?servername={$_GET["servername"]}')\" value='{browse}...'></td>
	</tr>
	
	
	</table>
	<br>
	<div style='font-size:16px;font-weight:bold'>{security}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ApacheServerSignature}:</td>
		<td>". Field_checkbox("ApacheServerSignature",1,$ApacheServerSignature)."</td>
	</tr>	
	<tr>
		<td class=legend>{DisableHtAccess}:</td>
		<td>". Field_checkbox("DisableHtAccess",1,$DisableHtAccess)."</td>
	</tr>
	<tr>
		<td class=legend>{RewriteRules}:</td>
		<td><a href=\"javascript:blur();\" OnClick=\"Loadjs('$page?rewrite=yes&servername={$_GET["servername"]}');\"
		style='font-size:13px;text-decoration:underline'>{edit}<a></td>
	</tr>				
	<tr>
		<td class=legend>{files_and_folders_permissions}:</td>
		<td><a href=\"javascript:blur();\" OnClick=\"Loadjs('freeweb.permissions.php?servername={$_GET["servername"]}');\"
		style='font-size:13px;text-decoration:underline'>{edit}<a></td>
	</tr>		
	$mod_security	
	$mod_evasive	
	$mod_geoip
	</table>
	<div style='text-align:right;width:100%'><hr>". button("{apply}","CheckApacheLdapButt()")."</div>

	<br>
	<div style='font-size:16px;font-weight:bold'>{clients_restrictions}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{enable_limit_by_addresses}:</td>
		<td>". Field_checkbox("LimitByIp",1,$Params["LimitByIp"]["enabled"],"enable_ip_authentication_save()")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'>". button("{add}","AuthIpAdd()")."</td>
	</tr>	
	<tr>
		<td colspan=2><div id='authip-list' style='width:100%;height:220px;overflow:auto'></div></td>
	</tr>
	</table>
	
	
	
	</div>
	
	<script>
		var x_CheckApacheLdap=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
		}	
	
	
		function CheckApacheForm(){
			document.getElementById('enable_ldap_authentication').disabled=true;
			document.getElementById('authentication_banner').disabled=true;
			document.getElementById('EnableLDAPAllSubDirectories').disabled=true;
			
			
			var ZarafaWebNTLM=$ZarafaWebNTLM;
			var APACHE_MOD_AUTHNZ_LDAP=$APACHE_MOD_AUTHNZ_LDAP;
			if(APACHE_MOD_AUTHNZ_LDAP==1){
				document.getElementById('enable_ldap_authentication').disabled=false;
				document.getElementById('authentication_banner').disabled=false;
				if(document.getElementById('enable_ldap_authentication').checked){
					document.getElementById('EnableLDAPAllSubDirectories').disabled=false;
				}
			}
			
			if(ZarafaWebNTLM==1){
				document.getElementById('enable_ldap_authentication').disabled=true;
				document.getElementById('enable_ldap_authentication').checked=true;
			
			}
			
		}
		
		function CheckApacheLdapButt(){
			CheckApacheLdap();
			document.getElementById('auth-apache-div').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			RefreshTab('main_config_freewebedit');
		}
	
		
		function CheckApacheLdap(){
			var XHR = new XHRConnection();
			if(document.getElementById('ApacheServerSignature').checked){
				XHR.appendData('ApacheServerSignature',1);
			}else{
				XHR.appendData('ApacheServerSignature',0);
			}
			
			if(document.getElementById('enable_ldap_authentication').checked){
				XHR.appendData('enable_ldap_authentication',1);
				document.getElementById('EnableLDAPAllSubDirectories').disabled=false;
			}else{
				XHR.appendData('enable_ldap_authentication',0);
				document.getElementById('EnableLDAPAllSubDirectories').disabled=true;
			}

			if(document.getElementById('DisableHtAccess').checked){
				XHR.appendData('DisableHtAccess',1);
			}else{
				XHR.appendData('DisableHtAccess',0);
			}

			if(document.getElementById('EnableLDAPAllSubDirectories').checked){
				XHR.appendData('EnableLDAPAllSubDirectories',1);
			}else{
				XHR.appendData('EnableLDAPAllSubDirectories',0);
			}				

			
			
			
			XHR.appendData('authentication_banner',base64_encode(document.getElementById('authentication_banner').value));
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'GET',x_CheckApacheLdap);
		}
		
		function RefreshAuthIp(){
			LoadAjax('authip-list','$page?authip-list=yes&servername={$_GET["servername"]}');
		}
		
		function enable_ip_authentication_save(){
			var XHR = new XHRConnection();
			if(document.getElementById('LimitByIp').checked){XHR.appendData('LimitByIp',1);}else{XHR.appendData('LimitByIp',0);}
			XHR.appendData('servername','{$_GET["servername"]}');
    		XHR.sendAndLoad('$page', 'GET',x_AuthIpAdd);
		}
		
		var x_AuthIpAdd=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshAuthIp();			
		}			
		
		function AuthIpAdd(){
			var ip=prompt('$apache_auth_ip_explain');
			if(ip){
				var XHR = new XHRConnection();
				XHR.appendData('authip-add',ip);
				XHR.appendData('servername','{$_GET["servername"]}');
				XHR.sendAndLoad('$page', 'GET',x_AuthIpAdd);
			}
		}
	CheckApacheForm();
	RefreshAuthIp();
	</script>
	
	
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function countloops(){
	
	$q=new mysql();
	$sql="SELECT count(*) as tcount FROM loop_disks";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["tcount"]==null){$ligne["tcount"]=0;}
	return $ligne["tcount"];
}
	
	
function popup(){
	$sql="SELECT * FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();
	$q=new mysql();
	$sock=new sockets();
	$APACHE_PROXY_MODE=0;
	$DNS_INSTALLED=false;
	$countloops=countloops();
	$no_usersameftpuser=$tpl->javascript_parse_text("{no_usersameftpuser}");
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$error_field_max_length=$tpl->javascript_parse_text("{error_field_max_length}");
	$error_please_fill_field=$tpl->javascript_parse_text("{error_please_fill_field}");
	$acl_dstdomain=$tpl->javascript_parse_text("{acl_dstdomain}");
	$mysql_database=$tpl->javascript_parse_text("{mysql_database}");
	$username=$tpl->javascript_parse_text("{username}");
	$password=$tpl->javascript_parse_text("{password}");
	$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
	$checkboxes=1;
	if($ligne["groupware"]=="cachemgr"){$checkboxes=0;}
	$users=new usersMenus();
	$PUREFTP_INSTALLED=1;
	if(!$users->PUREFTP_INSTALLED){$PUREFTP_INSTALLED=0;}
	$ServerPort=trim($ligne["ServerPort"]);
	$UseDefaultPort=0;
	if(!is_numeric($ServerPort)){$UseDefaultPort=1;}
	if($ServerPort==null){$UseDefaultPort=1;}
	if($ServerPort==0){$UseDefaultPort=1;}
	if($users->APACHE_PROXY_MODE){$APACHE_PROXY_MODE=1;}
	$parcourir_domaines="<input type='button' OnClick=\"javascript:Loadjs('browse.domains.php?field=domainname')\" value='{browse}...'>";
	if($users->dnsmasq_installed){$DNS_INSTALLED=true;}
	if($users->POWER_DNS_INSTALLED){$DNS_INSTALLED=true;}
	$FreeWebDisableSSL=trim($sock->GET_INFO("FreeWebDisableSSL"));
	if(!is_numeric($FreeWebDisableSSL)){$FreeWebDisableSSL=0;}			
	
	if($vgservices["freewebs"]<>null){
		if(!is_numeric($ligne["lvm_size"])){$ligne["lvm_size"]=5000;}
		if($ligne["lvm_vg"]==null){$ligne["lvm_vg"]=$vgservices["freewebs"];}
		$sizelimit="
		<tr>
		<td class=legend>{size}:</td>
		<td style='font-size:13px;'>". Field_text("vg_size",$ligne["lvm_size"],"font-size:13px;padding:3px;width:60px")."&nbsp;MB</td>
		<td>&nbsp;</td>
		</tr>";
		
	}
	$freeweb=new freeweb($_GET["servername"]);
	$groupwarelink=$freeweb->groupwares_InstallLink();
	$groupwares_textintro=$freeweb->groupwares_textintro();
	if($groupwarelink<>null){
		
		$explain="
		<div class=explain>$groupwares_textintro:<br><strong style='font-size:14px'>
			<a href=\"javascript:blur()\" OnClick=\"javascript:s_PopUpFull('$groupwarelink',1024,768)\" style='text-decoration:underline;font-weight:bold;color:#969696'>$groupwarelink</a></strong></div>		
		";
		
	}
	
	$freeweb=new freeweb($_GET["servername"]);
	
	
		if($ligne["domainname"]==null){
			$dda=explode(".",$ligne["servername"]);
			$hostname=$dda[0];
			unset($dda[0]);
			$domainname=@implode(".",$dda);
		}else{
			$ff=explode(".",$ligne["servername"]);
			if(count($ff)>2){
				$hostname=str_replace(".{$ligne["domainname"]}","",$ligne["servername"]);
			}else{
				$hostname=null;
			}
			$domainname=$ligne["domainname"];
			$parcourir_domaines=null;
		}
		
	if($hostname=="_default_"){$parcourir_domaines=null;}
	
	if($DNS_INSTALLED){
		include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
		include_once(dirname(__FILE__)."/ressources/class.pdns.inc");
		$pdns=new pdns();
		if($ligne["servername"]==null){
			$ip=new networking();
			$ips=$ip->ALL_IPS_GET_ARRAY();
			$ips[null]="{none}";
			$dns_field="<tr>
				<td class=legend nowrap>{dns_entry}:</td>
				<td>". Field_array_Hash($ips, "ADD_DNS_ENTRY",null,"style:font-size:14px")."</td>
				<td>". help_icon("freeweb_add_dns_entry_explain")."</td>
			</tr>";
		}else{
		
		$hostip=$pdns->GetIp($ligne["servername"]);
		if($hostip<>null){
		$dns_field="<tr>
				<td class=legend nowrap>{dns_entry}:</td>
				<td style='font-size:14px'>$hostip</td>
				<td>&nbsp;</td>
			</tr>";	
			
		}else{
			$ip=new networking();
			$ips=$ip->ALL_IPS_GET_ARRAY();
			$ips[null]="{none}";
			$dns_field="<tr>
				<td class=legend nowrap>{dns_entry}:</td>
				<td>". Field_array_Hash($ips, "ADD_DNS_ENTRY",null,"style:font-size:14px")."</td>
				<td>". help_icon("freeweb_add_dns_entry_explain")."</td>
			</tr>";			
			
		}
		
		}
		
		
	}
	
	
	$domain="<table style='width:100%'>
		<tr>
			<td>".Field_text("servername",$hostname,"font-size:15px;padding:3px;font-weight:bold;width:60px")."</td>
			<td style='font-size:14px' align='center' width=1%>&nbsp;.&nbsp;</td>
			<td>".Field_text("domainname",$domainname,"font-size:15px;padding:3px;width:220px;font-weight:bold")."</td>
			<td>$parcourir_domaines</td>
		</tr>
		</table>";
	
	if(!$users->AsSystemAdministrator){
		if($ligne["domainname"]==null){
			$dd=explode(".",$ligne["servername"]);
			$hostname=$dd[0];
			unset($dd[0]);
			$domainname=@implode(".",$dd);
		}else{
			$ff=explode(".",$ligne["servername"]);
			if(count($ff)>2){
				$hostname=str_replace(".{$ligne["domainname"]}","",$ligne["servername"]);
			}else{
				$hostname=null;
			}
			
			$domainname=$ligne["domainname"];
		}

		$ldap=new clladp();
		$domains=$ldap->Hash_domains_table($_SESSION["ou"]);
		while (list ($a, $b) = each ($domains) ){$c[$a]=$a;}
		
		$domain="
		<table style='width:100%'>
		<tr>
			<td>".Field_text("servername",$hostname,"font-size:15px;padding:3px;font-weight:bold")."</td>
			<td style='font-size:14px' align='center' width=1%>&nbsp;.&nbsp;</td>
			<td>". Field_array_Hash($c,"domainname",$domainname,"style:font-size:15px;padding:3px;font-weight:bold")."</td>
		</tr>
		</table>";
		
	}
	$NewServer=0;
	$reconfigure=ParagrapheTEXT("48-settings-refresh.png", "{rebuild}", "{rebuild_apache_text}","javascript:FreeWebsRebuildvHosts()");
	if(trim($ligne["servername"]==null)){
		$reconfigure=ParagrapheTEXT_disabled("48-settings-refresh.png", "{rebuild}", "{rebuild_apache_text}");
		$NewServer=1;}
	
	if($NewServer==0){
		$domain="<div style='font-size:16px'>{$ligne["servername"]}</div>
			<input type='hidden' value='{$ligne["servername"]}' id='servername'>
			<input type='hidden' value='{$ligne["domainname"]}' id='domainname'>";
	}
	
	$img="website-64.png";
	if($ligne["groupware"]<>null){
		$apache=new vhosts();
		$img=$apache->IMG_ARRAY_64[$ligne["groupware"]];
		$rebuild_groupware=ParagrapheTEXT("setup-icon-48.png", "{software}", "{rebuild_apache_groupware_text}","javascript:FreeWebsRebuildGroupware()");
		$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='REBUILD_GROUPWARE' AND `servername`='$servername'";
		$ligneDrup=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
		if(!is_numeric($ligneDrup["ID"])){$ligneDrup["ID"]=0;}	
		if($ligneDrup["ID"]>0){
			$rebuild_groupware=ParagrapheTEXT_disabled("setup-icon-48.png", "{software}", "{rebuild_apache_groupware_text}");
		}
	}
	
	$uid_uri=urlencode(base64_encode($ligne["uid"]));
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
			<center>
				<img src='img/$img'>
			</center><br>
			<div style='width:190px'>
			$reconfigure$rebuild_groupware
			</div>
		</td>
		<td valign='top' width=99%>
	$explain
	<div id='freewebdiv'>
	<table style='width:100%' class=form>
	<tr> 
		<td class=legend nowrap>{acl_dstdomain}:</td>
		<td colspan=2>$domain</td>
	</tr>
	<tr> 
		<td class=legend nowrap>{aliases}:</td>
		<td colspan=2><span id='webserver-aliases'></span></td>
	</tr>		
	<tr>
		<td class=legend nowrap>{listen_port}:</td>
		<td>
			<table>
			<tr>
				<td class=legend width=1% nowrap>{default}:</td>
				<td width=1%>". Field_checkbox("UseDefaultPort", 1,$UseDefaultPort,"CheckUseDefaultPort()")."</td>
				<td>". Field_text("ServerPort",$ServerPort,"font-size:13px;padding:3px;width:65px")."</td>
			</tr>
			</table>
	</tr>
	$dns_field
	<tr> 
		<td class=legend nowrap>{www_forward}:</td>
		<td width=1%>". Field_checkbox("Forwarder", 1,$ligne["Forwarder"],"CheckForwarder()")."</td>
	</tr>				
	<tr> 
		<td class=legend nowrap>{reverse_proxy}:</td>
		<td width=1%>". Field_checkbox("UseReverseProxy", 1,$ligne["UseReverseProxy"],"CheckUseReverseProxy()")."</td>
	</tr>		
	
	$sizelimit
	<tr>
		<td class=legend nowrap>{UseLoopDisk}:</td>
		<td>". Field_checkbox("UseLoopDisk",1,$ligne["UseLoopDisk"],"CheckLoops()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr style='height:auto'>
		<td>&nbsp;</td>
		<td colspan=2 style='height:auto'><span id='loops-list'></span></td></tr>		
	<tr>
		<td class=legend>{member}:</td>
		<td>". Field_text("www_uid",$ligne["uid"],"font-size:13px;padding:3px;")."</td>
		<td><span id='bb_button'><input type='button' OnClick=\"javascript:Loadjs('user.browse.php?field=www_uid&YahooWin=6')\" value='{browse}...'></span>
		<span id='status-uid-www' style='float:right'></span></td>
	</tr>	
	<tr>
		<td class=legend>{ssl}:</td>
		<td>". Field_checkbox("useSSL",1,$ligne["useSSL"])."</td>
		<td>&nbsp;</td>
	</tr>
	</table>
	<div id='block2'>
	<table style='width:100%' class=form>
	<tr>
		<td colspan=3><span style='font-size:16px'>{mysql_database}<hr style='border-color:005447'></td>
	</tr>
	<tr>
		<td class=legend>{useMySQL}:</td>
		<td>". Field_checkbox("useMysql",1,$ligne["useMysql"],"useMysqlCheck()")."</td>
		<td>&nbsp;</td>
	</tr>	
	
	
	<tr>
		<td class=legend>{mysql_database}:</td>
		<td>". Field_text("mysql_database",$ligne["mysql_database"],"width:150px;font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{mysql_username}:</td>
		<td>". Field_text("mysql_username",$ligne["mysql_username"],"width:120px;font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("mysql_password",$ligne["mysql_password"],"width:90px;font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=3><span style='font-size:16px'>{ftp_access}<hr style='border-color:005447'></td>
	</tr>	
	
	
	<tr>
		<td class=legend>{allowftp_access}:</td>
		<td>". Field_checkbox("useFTP",1,$ligne["useFTP"],"useMysqlCheck()")."</td>
		<td>&nbsp;</td>
	</tr>	
	
	<tr>
		<td class=legend>{ftp_user}:</td>
		<td>". Field_text("ftpuser",$ligne["ftpuser"],"width:120px;font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password("ftppassword",$ligne["ftppassword"],"width:90px;font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>	
	</table>
	</div>
	
	<div id='block3' style='display:none'>
		<table style='width:100%' class=form>
		<tr>
			<td class=legend>{www_ForwardTo}:</td>
			<td>". Field_text("ForwardTo",$ligne["ForwardTo"],"width:270px;font-size:14px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>
		</table>	
	</div>	
	
		<div style='width:100%;text-align:right'><hr>". button("{apply}","SaveFreeWebMain()")."</div>
	</div>


	
	
	</td>
	</tr>
	</table>
<script>

	function CheckDatas(){
		var APACHE_PROXY_MODE=$APACHE_PROXY_MODE;
		var FreeWebDisableSSL=$FreeWebDisableSSL;
		 
		
		if(APACHE_PROXY_MODE==0){
			document.getElementById('UseReverseProxy').checked=false;
			document.getElementById('UseReverseProxy').disabled=true;
		}
		
		var x=document.getElementById('servername').value;
		var z=document.getElementById('domainname').value;
		var w=x.length+z.length;
		if(w>0){
			document.getElementById('servername').disabled=true;
			document.getElementById('domainname').disabled=true;
			}
		var x=document.getElementById('mysql_database').value;
		if(x.length>0){document.getElementById('mysql_database').disabled=true;}	

		if(FreeWebDisableSSL==1){
			document.getElementById('useSSL').disabled=true;
		}
		
	}
	
	function CheckUseDefaultPort(){
		if(document.getElementById('UseDefaultPort').checked){
			document.getElementById('ServerPort').disabled=true;
			return;
		}
		document.getElementById('ServerPort').disabled=false;
	}
	
	function useMysqlCheck(){
		var checkboxes=$checkboxes;
		var PUREFTP_INSTALLED=$PUREFTP_INSTALLED;
		document.getElementById('useFTP').disabled=true;
		document.getElementById('useMysql').disabled=true;
		
		if(checkboxes==1){
			if(PUREFTP_INSTALLED==1){document.getElementById('useFTP').disabled=false;}
			document.getElementById('useMysql').disabled=false;	
		}
		if(PUREFTP_INSTALLED==1){
			document.getElementById('useFTP').disabled=false;
		}else{
			document.getElementById('useFTP').disabled=true;
			document.getElementById('useFTP').checked=false;
		}
		
		document.getElementById('mysql_database').disabled=true;
		document.getElementById('mysql_username').disabled=true;
		document.getElementById('mysql_password').disabled=true;
		document.getElementById('ftpuser').disabled=true;
		document.getElementById('ftppassword').disabled=true;
	
		if(document.getElementById('useMysql').checked){
			var mysql_database=document.getElementById('mysql_database').value;
			if(mysql_database.length==0){document.getElementById('mysql_database').disabled=false;}
			document.getElementById('mysql_username').disabled=false;
			document.getElementById('mysql_password').disabled=false;
		}

		if(!document.getElementById('useFTP').checked){return;}
		document.getElementById('ftpuser').disabled=false;
		document.getElementById('ftppassword').disabled=false;		
		
		
	}
	
	function CheckForwarder(){
		if(document.getElementById('Forwarder').checked){
			document.getElementById('block2').style.display='none';
			document.getElementById('block3').style.display='block';
			document.getElementById('UseReverseProxy').disabled=true;
			document.getElementById('UseLoopDisk').disabled=true;
		}else{
			document.getElementById('block2').style.display='block';
			document.getElementById('block3').style.display='none';
			document.getElementById('UseReverseProxy').disabled=false;
			document.getElementById('UseLoopDisk').disabled=false;
			CheckLoops();
		}
	
	}
		
	function FreeWebsRebuildvHosts(){
		var XHR = new XHRConnection();
		XHR.appendData('FreeWebsRebuildvHosts','{$_GET["servername"]}');
		AnimateDiv('freewebdiv');
		XHR.sendAndLoad('$page', 'POST',x_SaveFreeWebMain);
	}
	
	function FreeWebsRebuildGroupware(){
		var XHR = new XHRConnection();
		XHR.appendData('FreeWebsRebuildGroupware','{$_GET["servername"]}');
		AnimateDiv('freewebdiv');
		XHR.sendAndLoad('$page', 'POST',x_SaveFreeWebMain);
	}			


	var x_SaveFreeWebMain=function (obj) {
		    var NewServer=$NewServer;
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
			RefreshTab('main_config_freewebedit');
			RefreshTab('main_config_freeweb');
			if(document.getElementById('container-www-tabs')){RefreshTab('container-www-tabs');}
			if(NewServer==1){YahooWin5Hide();}
		}	
		
		function SaveFreeWebMain(){
			var NewServer=$NewServer;
			var XHR = new XHRConnection();
			if(NewServer==1){
				var sitename=document.getElementById('servername').value;
				var www_a=document.getElementById('domainname').value;
				var www_b=document.getElementById('servername').value;
				var www_t=www_a.length+www_b.length;
				if(www_t<2){
					alert('$error_please_fill_field:$acl_dstdomain'); 
					return;
				}
			
				if(sitename!=='_default_'){
					var x=document.getElementById('domainname').value;
					if(x.length==0){alert('$error_please_fill_field:$acl_dstdomain');return;}
				}else{
					document.getElementById('domainname').value='';
				}
			}
			if(document.getElementById('ADD_DNS_ENTRY')){
				XHR.appendData('ADD_DNS_ENTRY',document.getElementById('ADD_DNS_ENTRY').value);
			
			}
			
			
			
			if(document.getElementById('useMysql').checked){
				var mysql_database=document.getElementById('mysql_database').value;
				if(mysql_database.length==0){
					alert('$error_please_fill_field:$mysql_database');
					return;						
				}	
				var x=document.getElementById('mysql_password').value;
				if(x.length==0){
					alert('$error_please_fill_field:$mysql_database/$password');
					return;
				}	
				var x=document.getElementById('mysql_username').value;
				if(x.length==0){
					alert('$error_please_fill_field:$mysql_database/$username');
					return;
				}
				
				if(mysql_database.length>16){
					alert('mysql_database:$error_field_max_length: 16');
					document.getElementById('mysql_database').disabled=false;
					return;
				}
			}
			
			if(document.getElementById('useSSL').checked){XHR.appendData('useSSL',1);}else{XHR.appendData('useSSL',0);}
			if(document.getElementById('useMysql').checked){XHR.appendData('useMysql',1);}else{XHR.appendData('useMysql',0);}
			if(document.getElementById('useFTP').checked){XHR.appendData('useFTP',1);}else{XHR.appendData('useFTP',0);}
			if(document.getElementById('UseDefaultPort').checked){XHR.appendData('UseDefaultPort',1);}else{XHR.appendData('UseDefaultPort',0);}
			if(document.getElementById('UseReverseProxy').checked){XHR.appendData('UseReverseProxy',1);}else{XHR.appendData('UseReverseProxy',0);}
			if(document.getElementById('Forwarder').checked){XHR.appendData('Forwarder',1);}else{XHR.appendData('Forwarder',0);}
			
			
			
			
			if(document.getElementById('LoopMounts')){
				var LoopMounts=document.getElementById('LoopMounts').value;
				if(LoopMounts.length>3){
					if(document.getElementById('UseLoopDisk').checked){XHR.appendData('UseLoopDisk',1);}else{XHR.appendData('UseLoopDisk',0);}
					XHR.appendData('LoopMounts',LoopMounts);
				}
			
			}
			
			
			var ftpuser=trim(document.getElementById('ftpuser').value);
			var uid=trim(document.getElementById('www_uid').value);
			if(document.getElementById('useFTP').checked){	
				if(uid==ftpuser){
					alert('$no_usersameftpuser');
					return;
				}
			}
			
			if(document.getElementById('vg_size')){XHR.appendData('vg_size',document.getElementById('vg_size').value);}
			XHR.appendData('lvm_vg','{$ligne["lvm_vg"]}');
			if(NewServer==1){
				if(sitename!=='_default_'){
					var www_b=document.getElementById('domainname').value;
					var www_a=document.getElementById('servername').value;
					if(www_a.length>0){XHR.appendData('servername',www_a+'.'+www_b);}else{XHR.appendData('servername',www_b);}
    				}else{
    				XHR.appendData('servername','_default_');
    			}
    		}
    		if(NewServer==0){XHR.appendData('servername',document.getElementById('servername').value);}
    		XHR.appendData('domainname',document.getElementById('domainname').value);
    		XHR.appendData('uid',uid);
    		XHR.appendData('mysql_database',document.getElementById('mysql_database').value);
    		XHR.appendData('mysql_password',document.getElementById('mysql_password').value);
    		XHR.appendData('mysql_username',document.getElementById('mysql_username').value);
    		XHR.appendData('ftpuser',ftpuser);
    		XHR.appendData('ftppassword',document.getElementById('ftppassword').value);
    		XHR.appendData('ServerPort',document.getElementById('ServerPort').value);
    		XHR.appendData('ForwardTo',document.getElementById('ForwardTo').value);
			AnimateDiv('freewebdiv');
    		XHR.sendAndLoad('$page', 'GET',x_SaveFreeWebMain);
			
		}	
		
	function CheckLoops(){
		var countloops=$countloops;
		document.getElementById('UseLoopDisk').disabled=true;
		if(countloops>0){
			document.getElementById('UseLoopDisk').disabled=false;
		}
		document.getElementById('loops-list').innerHTML='';
		
		if(document.getElementById('UseLoopDisk').checked){
			if(document.getElementById('vg_size')){
				document.getElementById('vg_size').disabled=true;
			}
			LoadAjax('loops-list','$page?loops-list=yes&servername={$ligne["servername"]}');
		}
	}
	
		
	function CheckLoops(){
		var countloops=$countloops;
		document.getElementById('UseLoopDisk').disabled=true;
		if(countloops>0){document.getElementById('UseLoopDisk').disabled=false;}
		document.getElementById('loops-list').innerHTML='';
		
		if(document.getElementById('UseLoopDisk').checked){
			if(document.getElementById('vg_size')){
				document.getElementById('vg_size').disabled=true;
			}
			LoadAjax('loops-list','$page?loops-list=yes&servername={$ligne["servername"]}');
		}
	}
	
	function CheckUseReverseProxy(){
		CheckDatas();
		useMysqlCheck();
		CheckLoops();
	}
	
	function CheckUId(){
		LoadAjaxTiny('status-uid-www','$page?uid-check=$uid_uri');
	}
	
	function WebServerAliasesRefresh(){
		LoadAjaxTiny('webserver-aliases','$page?webserver-aliases=yes&servername={$ligne["servername"]}');
	}

	CheckUseDefaultPort();
	CheckDatas();
	useMysqlCheck();
	CheckLoops();
	CheckForwarder();
	CheckUId();
	WebServerAliasesRefresh();
	</script>		
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function authip_add(){
	$freeweb=new freeweb($_GET["servername"]);
	$freeweb->LimitByIp_add($_GET["authip-add"]);
	
}

function authip_del(){
	$freeweb=new freeweb($_GET["servername"]);
	$freeweb->LimitByIp_del($_GET["authip-del"]);	
}

function authip_enable(){
	$freeweb=new freeweb($_GET["servername"]);
	$freeweb->Params["LimitByIp"]["enabled"]=$_GET["LimitByIp"];
	$freeweb->SaveParams();
	
}

function authip_list(){
	$freeweb=new freeweb($_GET["servername"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$hash=$freeweb->LimitByIp_list();
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{ipaddr}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	if(is_array($hash)){
		while (list ($num, $ligne) = each ($hash) ){
			
			if($ligne==null){continue;}
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			
		$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/folder-network-32.png'></td>
			<td nowrap><strong style='font-size:14px'>$ligne</strong></td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","AuthIpDel('$ligne')")."</td>
			</tr>
			";	
		}
	}

		$html=$html."</table>
		<script>
		function AuthIpDel(ip){
				var XHR = new XHRConnection();
				XHR.appendData('authip-del',ip);
				XHR.appendData('servername','{$_GET["servername"]}');
				XHR.sendAndLoad('$page', 'GET',x_AuthIpAdd);
			
		}		
		</script>
		";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function Save(){
	
	$servername=trim(strtolower($_GET["servername"]));
	if(substr($servername, 0,1)=='.'){echo $servername. " FAILED\n";return;}
	$users=new usersMenus();
	$sock=new sockets();
	$FreewebsStorageDirectory=$sock->GET_INFO("FreewebsStorageDirectory");
	
	if(!$users->AsWebMaster){return "FALSE";}
	$uid=$_GET["uid"];
	$mysql_database=format_mysql_table($_GET["mysql_database"]);
	$mysql_password=$_GET["mysql_password"];
	$mysql_username=$_GET["mysql_username"];
	$lvm_vg=$_GET["lvm_vg"];
	$vg_size=$_GET["vg_size"];
	$ServerPort=$_GET["ServerPort"];
	if(!is_numeric($ServerPort)){$ServerPort=0;}
	if($_GET["UseDefaultPort"]==1){$ServerPort=0;}

	if(!is_numeric($vg_size)){$vg_size=5000;}
	$ftpuser=$_GET["ftpuser"];
	$ftppassword=$_GET["ftppassword"];
	$useSSL=$_GET["useSSL"];
	
	if(!$users->PUREFTP_INSTALLED){
		$_GET["useFTP"]=0;
		$ftpuser=null;
		$ftppassword=null;
	}	
	
	
	
	$sql="SELECT servername FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$q=new mysql();

	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["servername"]<>null){
		if($uid<>null){$u=new user($uid);$ou=$u->ou;}
		if(!$users->AsSystemAdministrator){$ou=$_SESSION["ou"];}
			
		$sql="UPDATE freeweb SET 
			mysql_password='$mysql_password',
			mysql_username='$mysql_username',
			mysql_database='$mysql_database',
			ftpuser='$ftpuser',
			ftppassword='$ftppassword',
			uid='$uid',
			useMysql='{$_GET["useMysql"]}',
			useFTP='{$_GET["useFTP"]}',
			lvm_vg='{$_GET["lvm_vg"]}',
			lvm_size='{$_GET["vg_size"]}',
			UseLoopDisk='{$_GET["UseLoopDisk"]}',
			LoopMounts='{$_GET["LoopMounts"]}',
			UseReverseProxy='{$_GET["UseReverseProxy"]}',
			ProxyPass='{$_GET["ProxyPass"]}',
			useSSL='$useSSL',
			ServerPort='$ServerPort',
			ou='$ou',
			Forwarder='{$_GET["Forwarder"]}',
			ForwardTo='{$_GET["ForwardTo"]}'
			
			WHERE servername='$servername'
		";
	}else{
		if($uid<>null){$u=new user($uid);$ou=$u->ou;}
		if($ou<>null){if($FreewebsStorageDirectory<>null){$www_dir="$FreewebsStorageDirectory/$servername";}}
		$sock=new sockets();
		$servername=str_replace('..', '.', $servername);
		$servername=str_replace('/', '.', $servername);
		$servername=str_replace('\\', '.', $servername);
		$servername=str_replace(' ', '.', $servername);
		$servername=str_replace('$', '.', $servername);
		$servername=str_replace('#', '.', $servername);
		$servername=str_replace('%', '.', $servername);
		$servername=str_replace('*', '.', $servername);
		
		if(substr($servername, strlen($servername)-1,1)=='.'){$servername=substr($servername, 0,strlen($servername)-1);}
		if(substr($servername,0,1)=='.'){$servername=substr($servername, 1,strlen($servername));}
		
		$sock->getFrameWork("freeweb.php?force-resolv=yes");
		$sql="INSERT INTO freeweb (mysql_password,mysql_username,ftpuser,ftppassword,useSSL,servername,mysql_database,
		uid,useMysql,useFTP,lvm_vg,lvm_size,UseLoopDisk,LoopMounts,ou,domainname,www_dir,ServerPort,UseReverseProxy,ProxyPass,Forwarder,ForwardTo)
		VALUES('$mysql_password','$mysql_username','$ftpuser','$ftppassword','$useSSL','$servername','$mysql_database',
		'$uid','{$_GET["useMysql"]}',
		'{$_GET["useFTP"]}','{$_GET["lvm_vg"]}','{$_GET["vg_size"]}','{$_GET["UseLoopDisk"]}','{$_GET["LoopMounts"]}','$ou',
		'{$_GET["domainname"]}','$FreewebsStorageDirectory','$ServerPort','{$_GET["UseReverseProxy"]}','{$_GET["ProxyPass"]}',
		'{$_GET["Forwarder"]}','{$_GET["ForwardTo"]}'
		)";
	}
	
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->BuildTables();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	
	if($_GET["useFTP"]==1){
		if($users->PUREFTP_INSTALLED){
			if(trim($ftpuser)<>null){
				if(trim($ftppassword)<>null){
					$pure=new pureftpd_user();
					if(!$pure->CreateUser($ftpuser,$ftppassword,$servername)){
						echo "FTP: Failed\n";
						return;
					}
				$sock->getFrameWork("services.php?reload-pure-ftpd=yes");
				}
			}
		}
	}
	
	if($_GET["useMysql"]==1){
		if(!$q->DATABASE_EXISTS($mysql_database)){$q->CREATE_DATABASE("$mysql_database");}
		if(!$q->PRIVILEGES($mysql_username,$mysql_password,$mysql_database)){
			echo "GRANT $mysql_database FAILED FOR $mysql_username\n$q->mysql_error";
		}
	}
	
	if(isset($_GET["ADD_DNS_ENTRY"])){
		$dnsDOM=explode(".", $_GET["servername"]);
		$netbiosname=$dnsDOM[0];
		unset($dnsDOM[0]);
		$domainname=implode(".", $dnsDOM);
		include_once(dirname(__FILE__)."/ressources/class.pdns.inc");
		$pdns=new pdns($domainname);
		$pdns->EditIPName($netbiosname, $_GET["ADD_DNS_ENTRY"], "A");
	}
	

	$sock->getFrameWork("cmd.php?freeweb-restart=yes");
	
}


function loops_list(){
	$sql="SELECT * FROM loop_disks ORDER BY `size` DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$size=FormatBytes($ligne["size"]*1024);
		$hash["/automount/{$ligne["disk_name"]}"]="{$ligne["disk_name"]} ($size)";
		
	}
	
	$hash[null]="{select}";
	$sql="SELECT LoopMounts FROM freeweb WHERE servername='{$_GET["servername"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$LoopMounts=$ligne["LoopMounts"];
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(Field_array_Hash($hash,"LoopMounts",$LoopMounts,"style:font-size:13px;padding:3px"));
}

function FreeWebsRebuildvHosts(){
	$sock=new sockets();
	$sock->getFrameWork("freeweb.php?rebuild-vhost=yes&servername={$_POST["FreeWebsRebuildvHosts"]}");
}

function FreeWebsRebuildGroupware(){
	$servername=$_POST["FreeWebsRebuildGroupware"];
	$q=new mysql();
	$sql="SELECT ID FROM drupal_queue_orders WHERE `ORDER`='REBUILD_GROUPWARE' AND `servername`='$servername'";
	$ligneDrup=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if(!is_numeric($ligneDrup["ID"])){$ligneDrup["ID"]=0;}
	if($ligneDrup["ID"]==0){
		$sql="INSERT INTO drupal_queue_orders(`ORDER`,`servername`) VALUES('REBUILD_GROUPWARE','$servername')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
	}
	
	$sock=new sockets();
	$sock->getFrameWork("drupal.php?perform-orders=yes");	
}

function uid_check(){
	
	$uid=urlencode($_GET["uid-check"]);
	$sock=new sockets();
	$datas=trim($sock->getFrameWork("freeweb.php?getidof=$uid"));
	if($datas=="FALSE"){
		$html=imgtootltip("status_warning.gif","{FREEWEB_WRONG_USER_SYSTEM_TEXT}");
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	}
	
	
}

function webserver_aliases_list(){
	$servername=$_GET["servername"];
	$page=CurrentPageName();
	$tpl=new templates();
	$free=new freeweb($servername);
	if($servername==null){return;}
	while (list ($host,$num) = each ($free->Params["ServerAlias"]) ){
		$f[]=$host;
	}
	
	$aliases=@implode(", ",$f);
	
	$html="<table><tbody><tr><td style='font-size:11px'><i>$aliases</i></td><td width=1%>". imgtootltip("edit.gif","{edit}","Loadjs('freeweb.edit.ServerAlias.php?servername=$servername')")."</td></tr></tbody></table>";
	echo $tpl->_ENGINE_parse_body($html);
}


/*
 * 
ServerName ngmlx441
SSLEngine sur
KeepAliveEnabled ON
SSLCipherSuite HIGH: MEDIUM
SSLProtocol tous
SSLProxyEngine sur
SecureProxy ON
SSLProxyEngine sur
SSLCertificateFile / etc / httpd / conf.d / servername.crt
SSLCertificateKeyFile / etc / httpd / conf.d / servername.key
SSLCACertificateFile / etc / httpd / conf.d / orgination.crt 
 */
?>