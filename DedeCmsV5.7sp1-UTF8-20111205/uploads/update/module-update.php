<?php
/**
 * @version        $Id: module-update.php 1 13:41 2010年7月26日Z tianya $
 * @package        DedeCMS.Install
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/../include/common.inc.php');
@set_time_limit(0);

$verMsg = ' V5.7 UTF8 SP1';
$errmsg = '';
$insLockfile = dirname(__FILE__).'/update_lock.txt';
$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';
$moduleDir = DEDEROOT.'/data/module';
$AdminBaseDir = DEDEROOT.'/dede/';

if(file_exists($insLockfile))
{
    exit(" 程序已运行升级，如果你确定要重新升级，请先从FTP中删除 install/update_lock.txt！");
}

require_once(DEDEINC.'/dedemodule.class.php');
require_once(dirname(__FILE__).'/modulescache.php');
require_once(dirname(__FILE__).'/update.inc.php');

if(empty($step)) $step = 0;

//完成升级
if($step==9999)
{
    $fp = fopen($insLockfile,'w');
    fwrite($fp,'ok');
    fclose($fp);
    ReWriteConfigAuto();
    UpDateCatCache();
    @unlink('./modules.tmp.inc');
	@rename('index.php','index.php.bak');
	@rename('module-update.php','module-update.php.bak');
    include('./templates/step-5.html');
    exit();
}

//用户选择的模块列表缓存文件
if(!file_exists($moduleCacheFile))
{
    $msg =  "由于无法找到模块缓存文件或者您没有选择任何升级的模块，请手动升级。<br /><br />";
    $msg .=  "<a href='module-update.php?step=9999' target='_top'>点击此完成升级 &gt;&gt;</a>";
    ShowMsg($msg,'javascript:;');
    exit();
}

//模块文件夹权限
if(!TestWrite($moduleDir))
{
    $msg =  "<font color='red'>目录 {$moduleDir} 不支持写入，不能升级模块，请登录后在模块管理处升级。</font><br /><br />";
    $msg .=  "<a href='module-update.php?step=9999' target='_top'>点击此完成升级 &gt;&gt;</a>";
    ShowMsg($msg,"javascript:;");
    exit();
}

include($moduleCacheFile);
$modules = split(',',$selModule);
$totalMod = count($modules);
if($step >= $totalMod)
{
    $msg =  "<font color='red'>完成所有模块的升级！</font><br /><br />";
    $msg .=  "<a href='module-update.php?step=9999' target='_top'>点击此进行下一步操作 &gt;&gt;</a>";
    ShowMsg($msg,'javascript:;');
    exit();
}
$moduleHash = $modules[$step];
$moduleFile = $allmodules[$moduleHash];

$dm = new DedeModule($moduleDir);

$minfos = $dm->GetModuleInfo($moduleHash);
extract($minfos, EXTR_SKIP);
$menustring = addslashes($dm->GetSystemFile($moduleHash,'menustring'));

$query = "INSERT INTO `#@__sys_module`(`hashcode` , `modname` , `indexname` , `indexurl` , `ismember` , `menustring` )
                                    VALUES ('$moduleHash' , '$name' , '$indexname' , '$indexurl' , '$ismember' , '$menustring' ) ";

$rs = $dsql->ExecuteNoneQuery("Delete From `#@__sys_module` where hashcode like '$moduleHash' ");
$rs = $dsql->ExecuteNoneQuery($query);

if(!$rs)
{
    $msg =  "<font color='red'>保存数据库信息失败，无法完成你选择的模块升级！</font><br /><br />";
    $msg .=  "<a href='module-update.php?step=9999' target='_top'>点击此进行下一步操作 &gt;&gt;</a>";
    exit();
}

//写文件
$dm->WriteFiles($moduleHash,1);
$dm->WriteSystemFile($moduleHash,'readme');
if($moduleHash == 'be722dbfa3cb3cb5628aab2d767ff62e') {
	$upLog='';
	$errsql='';
	$sql41tmp = 'ENGINE=MyISAM DEFAULT CHARSET='.$cfg_db_language;
	$askSql = "alter table `#@__ask` CHANGE `title`  `title` varchar(80)  NOT NULL    COMMENT ''  AFTER `anonymous`;
	ALTER TABLE `#@__ask` ADD COLUMN `lastanswer`  int(10) UNSIGNED NOT NULL DEFAULT 0 ;
			DROP TABLE IF EXISTS `#@__ask_scores`;
			CREATE TABLE `#@__ask_scores` (
			`mid` int(11) NOT NULL,
			`userid` char(20) NOT NULL,
			`ascores` mediumint(8) unsigned NOT NULL default '0',
			`mscores` mediumint(8) unsigned NOT NULL default '0',
			PRIMARY KEY  (`mid`)
			)ENGINE=MyISAM;
			alter table `#@__asktype` ADD `askwarrnum` mediumint(8) unsigned  NOT NULL    default '0'  COMMENT ''  AFTER `asknum`;
			alter table `#@__ask` drop column `extra`;
			alter table `#@__askanswer` drop column `brief`;
			REPLACE INTO `#@__sysconfig` (`aid` ,`varname` ,`info` ,`value` ,`type` ,`groupid`) VALUES
			(200, 'cfg_ask', '是否启用问答模块', 'Y', 'bool', 8),
			(201, 'cfg_ask_isdomain', '是否启用二级域名(启用需要设置 跨域共享cookie的域名)', 'N', 'bool', 8),
			(202, 'cfg_ask_domain', '问答模块的二级域名(例如:http://ask.dedecms.com)', '', 'string', 8),
			(203, 'cfg_ask_sitename', '问答系统名称', '织梦问答', 'string', 8),
			(204, 'cfg_ask_directory', '问答的目录名称', '/ask', 'string', 8),
			(205, 'cfg_ask_rewrite', '是否使用Rewrite', 'N', 'bool', 8),
			(206, 'cfg_ask_ifcheck', '问答模块提问是否需要审核', 'N', 'bool', 8),
			(207, 'cfg_ask_dateformat', '问答模块日期格式', 'Y-n-j', 'string', 8),
			(208, 'cfg_ask_timeformat', '问答模块时间格式', 'H:i', 'string', 8),
			(209, 'cfg_ask_timeoffset', '问答模块时区设定', '8', 'string', 8),
			(210, 'cfg_ask_gzipcompress', '是否启用gzip压缩', '1', 'string', 8),
			(211, 'cfg_ask_cookiepre', '问答模块cookie前缀', 'deask_', 'string', 8),
			(212, 'cfg_ask_ifkey', '问答模块回答问题是否需要审核', 'N', 'bool', 8),
			(213, 'cfg_ask_expiredtime', '问答模块问题有效期（天）', '20', 'string', 8),
			(214, 'cfg_ask_tpp', '问答模块列表显示问题数', '14', 'string', 8),
			(215, 'cfg_ask_symbols', '问答模块导航间隔符', '>', 'string', 8),
			(216, 'cfg_ask_ifanscore', ' 是否开启回答问题增加积分', 'Y', 'bool', 8),
			(217, 'cfg_ask_answerscore', ' 会员回答问题就增加积分', '2', 'string', 8),
			(218, 'cfg_ask_bestanswer', '最佳答案系统奖励积分', '20', 'string', 8),
			(219, 'cfg_ask_subtypenum', '首页显示子类数据', '10', 'string', 8),
			(220, 'cfg_ask_anscore', '匿名提问需要花费的积分数', '10', 'string', 8),
			(221, 'gdfaq_ask', '问答是否开启验证问题', 'Y', 'bool', 8);
	";
	$askSqls = explode(';', $askSql);
	foreach($askSqls as $upSql){
		$upSql = trim($upSql);
		if($upSql=='') continue;
		$upSql = preg_replace("#ENGINE=MyISAM#i", 'TYPE=MyISAM', $upSql);
		if($dsql->GetVersion() >= 4.1) {
			$upSql = preg_replace("#TYPE=MyISAM#i", $sql41tmp, $upSql);
		}
		//echo $upSql;
		$rs = $dsql->ExecuteNoneQuery($upSql);
		if(!$rs)
		{
			$upLog .= "{$upSql} \t失败 \t{$dsql->GetError()}\r\n";
			$errsql .= "{$upSql};\r\n";
		}
	}
	if(!empty($upLog)){
		$dm->Clear();
		$step = $step + 1;
		file_put_contents('uplog.txt',$upLog,FILE_APPEND);
		file_put_contents('uperr.txt',$errsql,FILE_APPEND);
		$msg =  "<font color='red'>问答模块升级sql语句错误</font><br /><br />";
		ShowMsg($msg,"module-update.php?step={$step}");
		exit();
	}
}
$dm->Clear();
$step = $step + 1;
ShowMsg("模块 {$name} 升级完成，准备下一模块升级...", "module-update.php?step={$step}");
exit();