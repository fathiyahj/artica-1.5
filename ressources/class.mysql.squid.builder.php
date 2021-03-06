<?php
include_once(dirname(__FILE__).'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
class mysql_squid_builder{
	var $ClassSQL;
	var $ok=false;
	var $mysql_error;
	var $UseMysql=true;
	var $database="squidlogs";
	var $mysql_server;
	var $mysql_admin;
	var $mysql_password;
	var $mysql_port;
	var $MysqlFailed=false;
	
	function mysql_squid_builder(){
		$sock=new sockets();
		$squidEnableRemoteStatistics=$sock->GET_INFO("squidEnableRemoteStatistics");
		$squidRemostatisticsServer=$sock->GET_INFO("squidRemostatisticsServer");
		$squidRemostatisticsPort=$sock->GET_INFO("squidRemostatisticsPort");
		$squidRemostatisticsUser=$sock->GET_INFO("squidRemostatisticsUser");
		$squidRemostatisticsPassword=$sock->GET_INFO("squidRemostatisticsPassword");
		if(!is_numeric($squidEnableRemoteStatistics)){$squidEnableRemoteStatistics=0;}
		
		$this->ClassSQL=new mysql();
		$this->UseMysql=$this->ClassSQL->UseMysql;
		$this->mysql_admin=$this->ClassSQL->mysql_admin;
		$this->mysql_password=$this->ClassSQL->mysql_password;
		$this->mysql_port=$this->ClassSQL->mysql_port;
		$this->mysql_server=$this->ClassSQL->mysql_server;
		
		
		if($squidEnableRemoteStatistics==1){
			$this->ClassSQL->mysql_admin=$squidRemostatisticsUser;
			$this->ClassSQL->mysql_password=$squidRemostatisticsPassword;
			$this->ClassSQL->mysql_port=$squidRemostatisticsPort;
			$this->ClassSQL->mysql_server=$squidRemostatisticsServer;
			$this->mysql_admin=$this->ClassSQL->mysql_admin;
			$this->mysql_password=$this->ClassSQL->mysql_password;
			$this->mysql_port=$this->ClassSQL->mysql_port;
			$this->mysql_server=$this->ClassSQL->mysql_server;			
		}
		
		if($this->TestingConnection()){
			$this->CheckTables();
		}else{
			$this->MysqlFailed=true;
		}
		
	}
	
	public function TestingConnection(){
		$this->ok=true;
		$this->ClassSQL->ok=true;
		$a=$this->ClassSQL->TestingConnection();
		$this->mysql_error=$this->ClassSQL->mysql_error;
		return $a;
	}
	
	public function COUNT_ROWS($table,$database=null){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->COUNT_ROWS($table,$database);
	}
	
	
	public function TABLE_SIZE($table,$database=null){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->TABLE_SIZE($table,$database);		
	}
	
	public function TABLE_EXISTS($table,$database=null){
		if($database==null){$database=$this->database;}
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->TABLE_EXISTS($table,$database);
	}
	private function DATABASE_EXISTS($database){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->DATABASE_EXISTS($database);
	}
	
	private function FIELD_EXISTS($table,$field,$database=null){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->FIELD_EXISTS($table,$field,$database);
	}
	
	public function QUERY_SQL($sql,$database=null){
		if($database<>$this->database){$database=$this->database;}
		$results=$this->ClassSQL->QUERY_SQL($sql,$database);
		$this->ok=$this->ClassSQL->ok;
		$this->mysql_error=$this->ClassSQL->mysql_error;
		return $results;
	}
	
	private function FIELD_TYPE($table,$field,$database){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->FIELD_TYPE($table,$field,$database);
	}
	
	private FUNCTION INDEX_EXISTS($table,$index,$database){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->INDEX_EXISTS($table,$index,$database);
	}
	
	private FUNCTION CREATE_DATABASE($database){
		if($database<>$this->database){$database=$this->database;}
		return $this->ClassSQL->CREATE_DATABASE($database);
	}
	
	public function CheckTable_dansguardian(){
		$this->CheckTables();
	}
	
	public function EVENTS_SUM(){
		$sql="SELECT SUM(TABLE_ROWS) as tsum FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'dansguardian_events_%'";
		$ligne=mysql_fetch_array($this->QUERY_SQL($sql));
		if(!$this->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		if($GLOBALS["VERBOSE"]){writelogs(mysql_num_rows($results)." events for $sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		writelogs("{$ligne["tsum"]} : $sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $ligne["tsum"];
		
	}
	
	public function LIST_TABLES_QUERIES(){
		$array=array();
		$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'dansguardian_events_%'";
		$results=$this->QUERY_SQL($sql);
		if(!$this->ok){writelogs("Fatal Error: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
		if($GLOBALS["VERBOSE"]){echo $sql." => ". mysql_num_rows($results)."\n";}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(preg_match("#dansguardian_events_([0-9]{1,4})([0-9]{1,2})([0-9]{1,2})#", $ligne["c"],$re))
			$array[$ligne["c"]]=$re[1]."-".$re[2]."-".$re[3];
		}
		return $array;
		
	}

	public function CategoryShellEscape($category){
		$category=trim($category);
		if($category==null){return;}
		$category=str_replace("/", "_", $category);
		$category=str_replace("-", "_", $category);
		$category=str_replace(" ", "_", $category);		
		
	}
	

	
	public function LIST_TABLES_HOURS(){
		if(isset($GLOBALS["SQUID_LIST_TABLES_HOURS"])){return $GLOBALS["SQUID_LIST_TABLES_HOURS"];}
		$array=array();
		$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE '%_hour'";
		$results=$this->QUERY_SQL($sql);
		if(!$this->ok){writelogs("Fatal Error: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
		if($GLOBALS["VERBOSE"]){echo $sql." => ". mysql_num_rows($results)."\n";}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(preg_match("#[0-9]+_hour#", $ligne["c"])){
				$GLOBALS["SQUID_LIST_TABLES_HOURS"][$ligne["c"]]=$ligne["c"];
				$array[$ligne["c"]]=$ligne["c"];
			}
		}
		return $array;		
	}
	public function LIST_TABLES_DAYS(){
		if(isset($GLOBALS["SQUID_LIST_TABLES_DAYS"])){return $GLOBALS["SQUID_LIST_TABLES_DAYS"];}
		$array=array();
		$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE '%_day' ORDER BY table_name";
		$results=$this->QUERY_SQL($sql);
		if(!$this->ok){writelogs("Fatal Error: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
		if($GLOBALS["VERBOSE"]){echo $sql." => ". mysql_num_rows($results)." memory count:". count($GLOBALS["SQUID_LIST_TABLES_DAYS"])."\n";}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(preg_match("#[0-9]+_day#", $ligne["c"])){
					$array[$ligne["c"]]=$ligne["c"];
					$GLOBALS["SQUID_LIST_TABLES_DAYS"][$ligne["c"]]=$ligne["c"];
			}
		}
		if($GLOBALS["VERBOSE"]){echo "LIST_TABLES_DAYS count:". count($GLOBALS["SQUID_LIST_TABLES_DAYS"])."\n";}
		return $array;		
	}	
	
	public function LIST_TABLES_MEMBERS(){
		$array=array();
		$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE '%_members' ORDER BY table_name";
		$results=$this->QUERY_SQL($sql);
		if(!$this->ok){writelogs("Fatal Error: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
		if($GLOBALS["VERBOSE"]){echo $sql." => ". mysql_num_rows($results)."\n";}
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if(preg_match("#[0-9]+_members#", $ligne["c"])){$array[$ligne["c"]]=$ligne["c"];}
		}
		return $array;		
	}		
	
	public function HIER(){
		$sql="SELECT DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y-%m-%d') as tdate";
		$ligne=mysql_fetch_array($this->QUERY_SQL($sql));
		return $ligne["tdate"];
	}
	
	public function LIST_TABLES_CATEGORIES(){
		if(isset($GLOBALS["LIST_TABLES_CATEGORIES"])){return $GLOBALS["LIST_TABLES_CATEGORIES"];}
		$array=array();
		$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'category_%'";
		$results=$this->QUERY_SQL($sql);
		if(!$this->ok){writelogs("Fatal Error: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
		
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["c"]=="category_"){$this->QUERY_SQL("DROP TABLE `category_`");}
			$array[$ligne["c"]]=$ligne["c"];
		}
		$GLOBALS["LIST_TABLES_CATEGORIES"]=$array;
		return $array;
		
	}	
	
	public function UPDATE_CATEGORIES_TABLES($sitename,$category){
		if(trim($sitename)==null){return;}
		if(trim($category)==null){return;}
		$array=$this->LIST_TABLES_HOURS();
		while (list ($num, $tablename) = each ($array) ){$this->QUERY_SQL("UPDATE $tablename SET category='$category' WHERE sitename='$sitename'");}
		$array=$this->LIST_TABLES_DAYS();
		while (list ($num, $tablename) = each ($array) ){$this->QUERY_SQL("UPDATE $tablename SET category='$category' WHERE sitename='$sitename'");}		
	}
	
	public function UPDATE_WEBSITES_TABLES($sitename,$newsitename){
		if(trim($sitename)==null){return;}
		if(trim($newsitename)==null){return;}
		$array=$this->LIST_TABLES_HOURS();
		while (list ($num, $tablename) = each ($array) ){$this->QUERY_SQL("UPDATE $tablename SET sitename='$newsitename' WHERE sitename='$sitename'");}
		$array=$this->LIST_TABLES_DAYS();
		while (list ($num, $tablename) = each ($array) ){$this->QUERY_SQL("UPDATE $tablename SET sitename='$newsitename' WHERE sitename='$sitename'");}		
	}	
	
	
	public function CheckTables($table=null){
		
	if(!$this->DATABASE_EXISTS($this->database)){$this->CREATE_DATABASE($this->database);}

	if($table==null){$table="dansguardian_events_".date('Ymd');}	
	if(!$this->TABLE_EXISTS($table,$this->database)){
		writelogs("Checking $table in $this->database NOT EXISTS...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$sql="CREATE TABLE IF NOT EXISTS `$table` (
		  `sitename` varchar(90) NOT NULL,
		  `ID` bigint(100) NOT NULL AUTO_INCREMENT,
		  `uri` varchar(90) NOT NULL,
		  `TYPE` varchar(50) NOT NULL,
		  `REASON` varchar(255) NOT NULL,
		  `CLIENT` varchar(50) NOT NULL DEFAULT '',
		  `zDate` datetime NOT NULL,
		  `zMD5` varchar(90) NOT NULL,
		  `uid` varchar(128) NOT NULL,
		  `remote_ip` varchar(20) NOT NULL,
		  `country` varchar(20) NOT NULL,
		  `QuerySize` int(10) NOT NULL,
		  `cached` int(1) NOT NULL DEFAULT '0',
		  `MAC` varchar(20) NOT NULL,
		  PRIMARY KEY (`ID`),
		  UNIQUE KEY `zMD5` (`zMD5`),
		  KEY `sitename` (`sitename`,`TYPE`,`CLIENT`,`uri`),
		  KEY `zDate` (`zDate`),
		  KEY `cached` (`cached`),
		  KEY `uri` (`uri`),
		  KEY `remote_ip` (`remote_ip`),
		  KEY `uid` (`uid`),
		  KEY `country` (`country`),
		  KEY `MAC` (`MAC`)
		) ";
			 $this->QUERY_SQL($sql,$this->database); 
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->mysql_error=$this->mysql_error."\n$sql";
				return false;
			}else{
				writelogs("Checking $table SUCCESS",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}
		}
		
		
		
		
	$tableblock=date('Ymd')."_blocked";	
	if(!$this->TABLE_EXISTS($tableblock,'artica_events')){		
			$sql="CREATE TABLE `$tableblock` (
			  `ID` bigint(100) NOT NULL AUTO_INCREMENT,
			  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `client` varchar(90) NOT NULL,
			  `website` varchar(125) NOT NULL,
			  `category` varchar(50) NOT NULL,
			  `rulename` varchar(50) NOT NULL,
			  `public_ip` varchar(40) NOT NULL,
			  `uri` varchar(255) NOT NULL,
			  `event` varchar(20) NOT NULL,
			  `why` varchar(90) NOT NULL,
			  `explain` text NOT NULL,
			  `blocktype` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `zDate` (`zDate`),
			  KEY `client` (`client`),
			  KEY `website` (`website`),
			  KEY `category` (`category`),
			  KEY `rulename` (`rulename`),
			  KEY `public_ip` (`public_ip`),
			  KEY `event` (`event`),
			  KEY `why` (`why`)
			)"; 
		$this->QUERY_SQL($sql); 
		
			if(!$this->ok){
					writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->mysql_error=$this->mysql_error."\n$sql";
					return false;
				}else{
					writelogs("Checking $table SUCCESS",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}

		}
		$tableblock=date('Ymd')."_blocked";
		if(!$this->FIELD_EXISTS("$tableblock", "uri")){$this->QUERY_SQL("ALTER TABLE `$tableblock` ADD `uri` VARCHAR( 255 ) NOT NULL");}
		if(!$this->FIELD_EXISTS("$tableblock", "event")){$this->QUERY_SQL("ALTER TABLE `$tableblock` ADD `event` VARCHAR( 20 ) NOT NULL,ADD INDEX ( `event` )");}
		if(!$this->FIELD_EXISTS("$tableblock", "why")){$this->QUERY_SQL("ALTER TABLE `$tableblock` ADD `why` VARCHAR( 90 ) NOT NULL,ADD INDEX ( `why` )");}
		if(!$this->FIELD_EXISTS("$tableblock", "explain")){$this->QUERY_SQL("ALTER TABLE `$tableblock` ADD `explain` TEXT");}
		if(!$this->FIELD_EXISTS("$tableblock", "blocktype")){$this->QUERY_SQL("ALTER TABLE `$tableblock` ADD `blocktype` VARCHAR( 255 )");}
	
		
		
		$tableblockMonth=date('Ym')."_blocked_days";
		if(!$this->TABLE_EXISTS($tableblockMonth,'artica_events')){		
			$sql="CREATE TABLE `$tableblockMonth` (
			`zmd5` VARCHAR( 100 ) NOT NULL PRIMARY KEY ,
			`hits` BIGINT( 100 ),
			`zDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			`client` VARCHAR( 90 ) NOT NULL ,
			`website` VARCHAR( 125 ) NOT NULL ,
			`category` VARCHAR( 50 ) NOT NULL ,
			`rulename` VARCHAR( 50 ) NOT NULL ,
			`public_ip` VARCHAR( 40 ) NOT NULL ,
			KEY `zDate` (`zDate`),
			KEY `hits` (`hits`),
			KEY `client` (`client`),
			KEY `website` (`website`),
			KEY `category` (`category`),
			KEY `rulename` (`rulename`),
			KEY `public_ip` (`public_ip`)
			
			)"; 
		$this->QUERY_SQL($sql); 
		
			if(!$this->ok){
					writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					$this->mysql_error=$this->mysql_error."\n$sql";
					return false;
				}else{
					writelogs("Checking $table SUCCESS",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}

		}		
		
		

		

		
		
		
		if(!$this->FIELD_EXISTS($table,"uid",$this->database)){
			$sql="ALTER TABLE `$table` ADD `uid` VARCHAR( 128 ) NOT NULL,ADD INDEX ( uid )";
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->mysql_error=$this->mysql_error."\n$sql";
			}			
			$this->QUERY_SQL($sql,$this->database);
		}

		if(!$this->TABLE_EXISTS('webfilter_rules',$this->database)){	
			$sql="CREATE TABLE IF NOT EXISTS `webfilter_rules` (
				  `ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				  	groupmode INT(1) NOT NULL,
				  	enabled INT(1) NOT NULL,
					groupname VARCHAR(90) NOT NULL,
					blockdownloads INT(1) NOT NULL DEFAULT '0' ,
					naughtynesslimit INT(2) NOT NULL DEFAULT '50' ,
					searchtermlimit INT(2) NOT NULL DEFAULT '30' ,
					bypass INT(1) NOT NULL DEFAULT '0' ,
					deepurlanalysis  INT(1) NOT NULL DEFAULT '0' ,
					sslcertcheck INT(1) NOT NULL DEFAULT '0' ,
					sslmitm INT(1) NOT NULL DEFAULT '0',
				  KEY `groupname` (`groupname`),
				  KEY `enabled` (`enabled`)
				) ";
			$this->QUERY_SQL($sql,$this->database);
		}
		
		if(!$this->TABLE_EXISTS('webfilter_group',$this->database)){	
			$sql="CREATE TABLE IF NOT EXISTS `webfilter_group` (
				  `ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					groupname VARCHAR(90) NOT NULL,
					localldap INT(1) NOT NULL DEFAULT '0' ,
					enabled INT(1) NOT NULL DEFAULT '1' ,
					gpid INT(10) NOT NULL DEFAULT '0' ,
					description VARCHAR(255) NOT NULL,
				  KEY `groupname` (`groupname`),
				  KEY `gpid` (`gpid`),
				  KEY `enabled` (`enabled`)
				) ";
			$this->QUERY_SQL($sql,$this->database);
		}		

		if(!$this->TABLE_EXISTS('webfilter_members',$this->database)){	
			$sql="CREATE TABLE IF NOT EXISTS `webfilter_members` (
				  `ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					pattern VARCHAR(90) NOT NULL,
					enabled INT(1) NOT NULL DEFAULT '1' ,
					groupid INT(10) NOT NULL DEFAULT '0' ,
					membertype INT(1) NOT NULL DEFAULT '0' ,
					  KEY `pattern` (`pattern`),
					  KEY `groupid` (`groupid`),
					  KEY `membertype` (`membertype`),
					  KEY `enabled` (`enabled`)
				) ";
			$this->QUERY_SQL($sql,$this->database);
		}	
	if(!$this->FIELD_EXISTS("webfilter_members", "membertype")){$this->QUERY_SQL("ALTER TABLE `webfilter_members` ADD `membertype` INT(1) NOT NULL ,ADD KEY `membertype` (`membertype`)");}		
		
		
		if(!$this->TABLE_EXISTS('webfilter_blks',$this->database)){	
			$sql="CREATE TABLE IF NOT EXISTS `webfilter_blks` (
				  `ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				    webfilter_id INT(2) NOT NULL,
				  	modeblk INT(1) NOT NULL,
				  	category VARCHAR(128) NOT NULL,
				  KEY `webfilter_id` (`webfilter_id`),
				  KEY `category` (`category`),
				  KEY `modeblk` (`modeblk`)
				) ";
			$this->QUERY_SQL($sql,$this->database);
		}			
		
		if(!$this->TABLE_EXISTS('webfilter_assoc_groups',$this->database)){	
			$sql="CREATE TABLE IF NOT EXISTS `webfilter_assoc_groups` (
				  `ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				    webfilter_id INT(2) NOT NULL,
				  	group_id INT(2) NOT NULL,
				  	zMD5 VARCHAR(09) NOT NULL,
				  KEY `webfilter_id` (`webfilter_id`),
				  KEY `group_id` (`group_id`),
				  UNIQUE KEY `zMD5` (`zMD5`)
				) ";
			$this->QUERY_SQL($sql,$this->database);
		}			
		if(!$this->FIELD_EXISTS("webfilter_assoc_groups", "zMD5")){$this->QUERY_SQL("ALTER TABLE `webfilter_assoc_groups` ADD `zMD5` VARCHAR( 90 ) NOT NULL ,ADD UNIQUE KEY `zMD5` (`zMD5`)");}		
		
		
		if(!$this->TABLE_EXISTS('tables_day',$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `tables_day` (
			  `tablename` varchar(90) NOT NULL,
			  `zDate` date NOT NULL,
			  `size` BIGINT(100) NOT NULL,
			  `size_cached` BIGINT(100) NOT NULL,
			  `totalsize` INT( 100 ) NOT NULL ,
			  `totalBlocked` BIGINT( 100 ) NOT NULL ,
			  `requests` INT( 100 ) NOT NULL ,
			  `cache_perfs` INT( 2 ) NOT NULL ,
			  `Hour` int(1) NOT NULL,
			  `members` int(1) NOT NULL,
			  `blocks` int(1) NOT NULL,
			  PRIMARY KEY (`tablename`),
			  KEY `zDate` (`zDate`,`size`,`size_cached`,`cache_perfs`),
			  KEY `Hour` (`Hour`),
			  KEY `blocks` (`blocks`),
			  KEY `totalsize` (`totalsize`),
			  KEY `totalBlocked` (`totalBlocked`),
			  KEY `requests` (`requests`)
			) ";
			$this->QUERY_SQL($sql,$this->database);
		}
		
		if(!$this->FIELD_EXISTS("tables_day", "blocks")){$this->QUERY_SQL("ALTER TABLE `tables_day` ADD `blocks` INT( 1 ) NOT NULL ,ADD INDEX ( `blocks` )");}
		if(!$this->FIELD_EXISTS("tables_day", "totalBlocked")){$this->QUERY_SQL("ALTER TABLE `tables_day` ADD `totalBlocked` BIGINT( 100 ) NOT NULL ,ADD INDEX ( `totalBlocked` )");}

		

		if(!$this->TABLE_EXISTS('updateblks_events',$this->database)){	
		$sql="CREATE TABLE `squidlogs`.`updateblks_events` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`zDate` TIMESTAMP NOT NULL ,
			`PID` INT( 5 ) NOT NULL ,
			`function` VARCHAR( 50 ) NOT NULL ,
			`category` VARCHAR( 50 ) NOT NULL ,
			`text` TEXT NOT NULL ,
			INDEX ( `zDate` , `PID` , `function` ),
			KEY `category` (`category`)
			)";
			$this->QUERY_SQL($sql,$this->database);
		}
		if(!$this->FIELD_EXISTS("updateblks_events", "category")){$this->QUERY_SQL("ALTER TABLE `updateblks_events` ADD `category` VARCHAR( 50 ) NOT NULL ,ADD KEY `category` (`category`)");} 		
		
		if(!$this->TABLE_EXISTS('visited_sites',$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `visited_sites` (
			  `sitename` varchar(255) NOT NULL,
			  `Querysize` int(100) NOT NULL,
			  `category` varchar(255) NOT NULL,
			  `HitsNumber` int(100) NOT NULL,
			  `country` varchar(128) NOT NULL,
			  `familysite` varchar(128) NOT NULL,
			  PRIMARY KEY (`sitename`),
			  KEY `Querysize` (`Querysize`,`HitsNumber`,`country`),
			  KEY `familysite` (`familysite`)
			) ";
			$this->QUERY_SQL($sql,$this->database);
		}

		if(!$this->TABLE_EXISTS('categorize',$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `categorize` (
			  `zmd5` varchar(90) NOT NULL,
			  `pattern` varchar(255) NOT NULL,
			  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `uuid` varchar(128) NOT NULL,
			  `category` varchar(80) NOT NULL,
			  PRIMARY KEY (`zmd5`),
			  KEY `zDate` (`zDate`,`category`),
			  KEY `pattern` (`pattern`),
			  KEY `uuid` (`uuid`)
			) ";
			$this->QUERY_SQL($sql,$this->database);
		}

		if(!$this->TABLE_EXISTS('categorize_changes',$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `categorize_changes` (
			  `zmd5` varchar(90) NOT NULL,
			  `sitename` varchar(255) NOT NULL,
			  `category` varchar(255) NOT NULL,
			  PRIMARY KEY (`zmd5`),
			  KEY `sitename` (`sitename`),
			  KEY `category` (`category`)
			)";
			$this->QUERY_SQL($sql,$this->database);
		}

		if(!$this->TABLE_EXISTS('categorize_delete',$this->database)){	
				$sql="CREATE TABLE IF NOT EXISTS `categorize_delete` (
				  `sitename` varchar(255) NOT NULL,
				  `category` varchar(128) NOT NULL,
				  `zmd5` varchar(90) NOT NULL,
				  `sended` INT( 1 ) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `category` (`category`),
				  KEY `sitename` (`sitename`),
				  KEY `sended` (`sended`)
				)";
				
			$this->QUERY_SQL($sql,$this->database);
		}
		
		if(!$this->FIELD_EXISTS("categorize_delete", "sended")){$this->QUERY_SQL("ALTER TABLE `categorize_delete` ADD `sended` INT( 1 ) NOT NULL ,ADD INDEX ( `sended` )");} 
		
		if(!$this->TABLE_EXISTS('personal_categories',$this->database)){	
				$sql="CREATE TABLE `squidlogs`.`personal_categories` (
				`category` VARCHAR( 15 ) NOT NULL ,
				`category_description` VARCHAR( 255 ) NOT NULL ,
				`sended` INT( 1 ) NOT NULL DEFAULT '0',
				INDEX ( `category_description` , `sended` ) ,
				UNIQUE (`category`))";		
				$this->QUERY_SQL($sql,$this->database);
		}
	}
	
	function COUNT_CATEGORIES(){
		$c=0;
		$tablescat=$this->LIST_TABLES_CATEGORIES();
		while (list ($table, $none) = each ($tablescat) ){		
			$count=$this->COUNT_ROWS($table);
			$c=$c+$count;
		}
		return $c;
	}
	
	
	function GET_CATEGORIES($sitename,$nocache=false){
		$cat=array();
		if(trim($sitename)==null){return;}
		$sitename=strtolower(trim($sitename));
		if(preg_match("#^www\.(.+)#", $sitename,$re)){$sitename=$re[1];}
		$cattmp=array();
		if(!$nocache){
			$sql="SELECT category FROM visited_sites WHERE sitename='$sitename'";
			$ligne=mysql_fetch_array($this->QUERY_SQL($sql));
			if(trim($ligne["category"])<>null){return addslashes($ligne["category"]);}
		}
		
		
		$tablescat=$this->LIST_TABLES_CATEGORIES();
		while (list ($table, $none) = each ($tablescat) ){
			$sql="SELECT category FROM $table WHERE pattern='$sitename' AND enabled=1";
			$ligne=mysql_fetch_array($this->QUERY_SQL($sql));
			
			if($ligne["category"]<>null){
				if($GLOBALS["VERBOSE"]){echo "Found {$ligne["category"]} FOR \"$sitename\n";}
				$cattmp[$ligne["category"]]=$ligne["category"];
			}
		}
		
		while (list ($a, $b) = each ($cattmp) ){
			$cat[]=$b;
		}
		
		
		if(count($cat)>0){
			$sitename=addslashes($sitename);
			$category=@implode(",", $cat);
			$category=addslashes($category);
			if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#", $sitename)){
				$familysite="ipaddr";
			}else{
				$tt=explode(".",$sitename);
				$familysite=$tt[count($tt)-2].".".$tt[count($tt)-1];
			}			
			if(!$nocache){	
				$sql="INSERT IGNORE INTO visited_sites (sitename,category,familysite) VALUES ('$sitename','$category','$familysite')";
				$this->QUERY_SQL($sql);
			}
			return $category;
		}
		
		
	}
	
	function category_transform_name($category){
			$category=str_replace('/',"_",$category);
			$category=str_replace('-',"_",$category);
			return $category;	
	}
	
	function CreateCategoryTable($category){
		$category=$this->category_transform_name($category);
		if(!$this->TABLE_EXISTS("category_$category",$this->database)){	
		$sql="CREATE TABLE `$this->database`.`category_$category` (
				`zmd5` VARCHAR( 90 ) NOT NULL ,
				`zDate` DATETIME NOT NULL ,
				`category` VARCHAR( 20 ) NOT NULL ,
				`pattern` VARCHAR( 255 ) NOT NULL ,
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
				`uuid` VARCHAR( 255 ) NOT NULL ,
				`sended` INT( 1 ) NOT NULL DEFAULT '0',
				PRIMARY KEY ( `zmd5` ) ,
				KEY `zDate` (`zDate`),
	  			KEY `pattern` (`pattern`),
	  			KEY `enabled` (`enabled`),
	  			KEY `sended` (`sended`),
	  			KEY `category` (`category`)
			)";
			$this->QUERY_SQL($sql,$this->database);
			if(!$this->ok){writelogs("Failed to create category_$category",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}			
		
	}
	
	function CreateHourTable($tablename){
		if(!$this->TABLE_EXISTS("$tablename",$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `$tablename` (
			  `zMD5` varchar(90) NOT NULL,
			  `sitename` varchar(128) NOT NULL,
			  `familysite` varchar(128) NOT NULL,
			  `client` varchar(50) NOT NULL,
			  `hour` int(2) NOT NULL,
			  `remote_ip` varchar(50) NOT NULL,
			  `MAC` varchar(20) NOT NULL,
			  `country` varchar(50) NOT NULL,
			  `size` int(10) NOT NULL,
			  `hits` int(10) NOT NULL,
			  `uid` varchar(90) NOT NULL,
			  `category` varchar(50) NOT NULL,
			  `cached` int(1) NOT NULL,
			  PRIMARY KEY (`zMD5`),
			  KEY `sitename` (`sitename`),
			  KEY `client` (`client`),
			  KEY `country` (`country`),
			  KEY `hour` (`hour`),
			  KEY `category` (`category`),
			  KEY `size` (`size`),
			  KEY `hits` (`hits`),
			  KEY `uid` (`uid`),
			  KEY `MAC` (`MAC`),
			  KEY `familysite` (`familysite`),
			  KEY `cached` (`cached`)
			) ";
			if(!$this->QUERY_SQL($sql,$this->database)){return false;}
		}			
		return true;
		
	}	
	
	function CreateMonthTable($tablename){
		if(!$this->TABLE_EXISTS("$tablename",$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `$tablename` (
			  `zMD5` varchar(90) NOT NULL,
			  `sitename` varchar(128) NOT NULL,
			  `familysite` varchar(128) NOT NULL,
			  `client` varchar(50) NOT NULL,
			  `day` int(2) NOT NULL,
			  `remote_ip` varchar(50) NOT NULL,
			  `country` varchar(50) NOT NULL,
			  `size` int(10) NOT NULL,
			  `hits` int(10) NOT NULL,
			  `uid` varchar(90) NOT NULL,
			  `category` varchar(50) NOT NULL,
			  `MAC` varchar(20) NOT NULL,
			  `cached` int(1) NOT NULL,
			  PRIMARY KEY (`zMD5`),
			  KEY `sitename` (`sitename`),
			  KEY `client` (`client`),
			  KEY `country` (`country`),
			  KEY `day` (`day`),
			  KEY `category` (`category`),
			  KEY `size` (`size`),
			  KEY `hits` (`hits`),
			  KEY `uid` (`uid`),
			  KEY `MAC` (`MAC`),
			  KEY `familysite` (`familysite`),
			  KEY `cached` (`cached`)
			) ";
			if(!$this->QUERY_SQL($sql,$this->database)){return false;}
		}			
		return true;
		
	}	
	
	function CreateMembersDayTable($tablename=null){
		if($tablename==null){$tablename=date("Ymd")."_members";}
		
		if(!$this->TABLE_EXISTS("$tablename",$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `$tablename` (
			  `zMD5` varchar(90) NOT NULL,
			  `client` varchar(50) NOT NULL,
			  `MAC` varchar(20) NOT NULL,
			  `hour` int(2) NOT NULL,
			  `size` int(10) NOT NULL,
			  `hits` int(10) NOT NULL,
			  `uid` varchar(90) NOT NULL,
			  `cached` int(1) NOT NULL,
			  PRIMARY KEY (`zMD5`),
			  KEY `hour` (`hour`),
			  KEY `client` (`client`),
			  KEY `size` (`size`),
			  KEY `hits` (`hits`),
			  KEY `uid` (`uid`),
			  KEY `MAC` (`MAC`),
			  KEY `cached` (`cached`)
			) ";
			if(!$this->QUERY_SQL($sql,$this->database)){return false;}
		}			
		return true;
		
	}	
	
	function CreateMembersMonthTable($tablename=null){
		if($tablename==null){$tablename=date("Ym")."_members";}
		
		if(!$this->TABLE_EXISTS("$tablename",$this->database)){	
		$sql="CREATE TABLE IF NOT EXISTS `$tablename` (
			  `zMD5` varchar(90) NOT NULL,
			  `client` varchar(50) NOT NULL,
			  `day` int(2) NOT NULL,
			  `size` int(10) NOT NULL,
			  `hits` int(10) NOT NULL,
			  `uid` varchar(90) NOT NULL,
			  `MAC` varchar(20) NOT NULL,
			  `cached` int(1) NOT NULL,
			  PRIMARY KEY (`zMD5`),
			  KEY `day` (`day`),
			  KEY `client` (`client`),
			  KEY `size` (`size`),
			  KEY `hits` (`hits`),
			  KEY `uid` (`uid`),
			  KEY `MAC` (`MAC`),
			  KEY `cached` (`cached`)
			) ";
			if(!$this->QUERY_SQL($sql,$this->database)){return false;}
		}			
		return true;
		
	}

	function FixTables(){
		$array=$this->LIST_TABLES_QUERIES();
		while (list ($tablename, $line) = each ($array)){
			if(!$this->FIELD_EXISTS($tablename, "MAC")){$this->QUERY_SQL("ALTER TABLE `$tablename` ADD `MAC` VARCHAR( 20 ) NOT NULL ,ADD INDEX ( MAC )");}
		}
		
		$array=$this->LIST_TABLES_HOURS();
		while (list ($tablename, $line) = each ($array)){
			if(!$this->FIELD_EXISTS($tablename, "MAC")){$this->QUERY_SQL("ALTER TABLE `$tablename` ADD `MAC` VARCHAR( 20 ) NOT NULL ,ADD INDEX ( MAC )");}
		}		
		
		$array=$this->LIST_TABLES_MEMBERS();
		while (list ($tablename, $line) = each ($array)){
			if(!$this->FIELD_EXISTS($tablename, "MAC")){$this->QUERY_SQL("ALTER TABLE `$tablename` ADD `MAC` VARCHAR( 20 ) NOT NULL ,ADD INDEX ( MAC )");}
		}		
		
		
	}
	
}
function writelogs_squid($text,$function,$file,$line=0,$category=null){
		if($category==null){$category="Unknown";}
		if($function==null){$function="Unknown";}
		writelogs($text,$function,$file,$line);
		$date=date('Y-m-d H:i:s');
		$array["date"]=$date;
		$pid=getmypid();
		$array["category"]=$category;
		$array["text"]=$text."\nline:$line\nscript:".basename($file);
		$array["function"]=$function;
		$array["pid"]=$pid;
		$serial=serialize($array);
		$md5=md5($serial);
		@file_put_contents("/var/log/artica-postfix/artica-squid-events/$md5", $serial);
		
	}