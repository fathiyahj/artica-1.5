<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');


	
	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["query"])){query();exit;}
	
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if(!isset($_GET["OnlyUsers"])){$_GET["OnlyUsers"]=0;}
	if(!isset($_GET["OnlyGroups"])){$_GET["OnlyGroups"]=0;}
	if(!isset($_GET["OnlyGUID"])){$_GET["OnlyGUID"]=0;}
	if(!isset($_GET["NOComputers"])){$_GET["NOComputers"]=0;}
	
	
	
	$title=$tpl->_ENGINE_parse_body("{browse}::{members}::");
	echo "LoadWinORG('350','$page?popup=yes&field-user={$_GET["field-user"]}&NOComputers={$_GET["NOComputers"]}&prepend={$_GET["prepend"]}&prepend-guid={$_GET["prepend-guid"]}&OnlyUsers={$_GET["OnlyUsers"]}&organization={$_GET["organization"]}&OnlyGroups={$_GET["OnlyGroups"]}&OnlyGUID={$_GET["OnlyGUID"]}&callback={$_GET["callback"]}','$title');";	
	
	
	
}



function popup(){
	$page=CurrentPageName();
	$tpl=new templates();		
	if($_GET["prepend"]==null){$_GET["prepend"]=0;}
	if($_GET["prepend-guid"]==null){$_GET["prepend-guid"]=0;}
	$OnlyGUID=$_GET["OnlyGUID"];
	if(!is_numeric($OnlyGUID)){$OnlyGUID=0;}
	if($_GET["callback"]<>null){$callback="{$_GET["callback"]}(id,prependText,guid);WinORGHide();return;";}
	
	$html="
	<center>
	<table class=form>
		<tr>
		<td>" . Field_text('BrowseUserQuery',null,'width:100%;font-size:14px;padding:3px',null,null,null,null,"BrowseFindUserGroupClick(event);")."</td>
		<td align='right'><input type='button' OnClick=\"javascript:BrowseFindUserGroup();\" value='{search}&nbsp;&raquo;'></td>
		</tR>
	</table>
	</center>
	<br>
	<div style='padding:5px;height:350px;overflow:auto' id='finduserandgroupsidBrwse'></div>
	<script>
	function BrowseFindUserGroupClick(e){
		if(checkEnter(e)){BrowseFindUserGroup();}
	}
	
	var x_BrowseFindUserGroup=function (obj) {
		tempvalue=obj.responseText;
		document.getElementById('finduserandgroupsidBrwse').innerHTML=tempvalue;
	}


	function BrowseFindUserGroup(){
		LoadAjax('finduserandgroupsidBrwse','$page?query='+escape(document.getElementById('BrowseUserQuery').value)+'&prepend={$_GET["prepend"]}&field-user={$_GET["field-user"]}&prepend-guid={$_GET["prepend-guid"]}&OnlyUsers={$_GET["OnlyUsers"]}&OnlyGUID={$_GET["OnlyGUID"]}&organization={$_GET["organization"]}&OnlyGroups={$_GET["OnlyGroups"]}&callback={$_GET["callback"]}&NOComputers={$_GET["NOComputers"]}');
	
	}	
	
	
	function SambaBrowseSelect(id,prependText,guid){
			$callback
			var prepend={$_GET["prepend"]};
			var prepend_gid={$_GET["prepend-guid"]};
			var OnlyGUID=$OnlyGUID;
			if(document.getElementById('{$_GET["field-user"]}')){
				var selected=id;
				if(OnlyGUID==1){
					document.getElementById('{$_GET["field-user"]}').value=guid;
					WinORGHide();
					return;
				}
				
				if(prepend==1){selected=prependText+id;}
				if(prepend_gid==1){
					if(guid>1){
						selected=prependText+id+':'+guid;
					}
				}
				document.getElementById('{$_GET["field-user"]}').value=selected;
				WinORGHide();
			}
		}

		BrowseFindUserGroup();
	
</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function query(){
	$users=new user();
	if($_GET["query"]=='*'){$_GET["query"]=null;}
	$hash=$users->find_ldap_items($_GET["query"],$_GET["organization"]);	
	if($_GET["OnlyUsers"]=="yes"){$_GET["OnlyUsers"]=1;}
	$OnlyUsers=$_GET["OnlyUsers"];
	$OnlyGroups=$_GET["OnlyGroups"];
	$OnlyGUID=$_GET["OnlyGUID"];

	if(!isset($_GET["prepend"])){$_GET["prepend"]=0;}else{
		if($_GET["prepend"]=='yes'){$_GET["prepend"]=1;}
		if($_GET["prepend"]=='no'){$_GET["prepend"]=0;}
	}
	if(!is_array($hash)){return null;}
	
	$html=$html."
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{members}/{groups}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	
while (list ($num, $ligne) = each ($hash) ){
		if($num==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$gid=0;
		
		
		if(preg_match("#^@(.+?):([0-9]+)#",$ligne,$re)){
			if($OnlyUsers==1){continue;}
			$img="wingroup.png";
			$Displayname="{$re[1]}";
			$prepend="group:";
			$gid=$re[2];
		}else{
			if($OnlyGroups==1){continue;}
			$Displayname=$ligne;
			$img="winuser.png";
			$prepend="user:";
		}
		
		if(substr($num,strlen($num)-1,1)=='$'){
			if($_GET["NOComputers"]==1){continue;}
			$Displayname=str_replace('$','',$Displayname);
			$img="base.gif";
			$prepend="computer:";
			
		}
		
		$js="SambaBrowseSelect('$num','$prepend',$gid)";
		if($_GET["callback"]<>null){$js="{$_GET["callback"]}('$num','$prepend',$gid)";}
		
	$html=$html."
		<tr class=$classtr>
		<td width=1% align='center' valign='middle'><img src='img/$img'></td>
		<td 
		onMouseOver=\"this.style.cursor='pointer'\" 
		OnMouseOut=\"this.style.cursor='default'\"
		OnClick=\"javascript:$js;\"
		><strong style='font-size:14px;text-decoration:underline' >$Displayname</td>
		</tr>
	";
	}
	
	$html=$html."</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
	
}

