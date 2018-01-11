<?php
/**
 * @version        $Id: index.php 1 11:37 2011年3月7日 Niap $
 * @package        DedeCMS.Update
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once('update.inc.php');
$verMsg = ' V5.6 UTF8-V5.7 UTF8';
$dsql->Init();
$moduleLocal = DEDEDATA.'/module/';
$insLockfile = dirname(__FILE__).'/install_lock.txt';
$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';
$errmsg = '';
if(empty($step) || $step == 1)
{
	
	include('templates/step-1.html');
	exit();
}else if($step == 2) {
	include('templates/step-2.html');
	exit();
}else if($step == 3) {
	if(isset($isModulesUpdate) && is_array($modules))
	{
		$module = join(',',$modules);
		$fp = fopen($moduleCacheFile,'w');
		fwrite($fp,'<'.'?php'."\r\n");
		fwrite($fp,'$selModule = "'.$module.'"; '."\r\n");
		fwrite($fp,'?'.'>');
		//如果不能写入缓存文件，退出模块安装
		if(!$fp)
		{
			//锁定安装程序
			$fp = fopen($insLockfile,'w');
			fwrite($fp,'ok');
			fclose($fp);
			$errmsg = "<font color='red'>由于无法写入模块缓存，升级可选模块失败，请登录后在模块管理处安装。</font>";
			ShowMsg($errmsg,'javascript:;');
		}
		fclose($fp);
	}
	include('templates/step-3.html');
	exit();
}else if($step==4){
	$upSql = file_get_contents('updatesql.txt');
	$upSqls = explode(';', $upSql);
	$upLog = '';
	$errsql = '';
	foreach($upSqls as $upSql){
		$upSql = trim($upSql);
		if($upSql=='') continue;
		$upSql = preg_replace("#ENGINE=MyISAM#i", 'TYPE=MyISAM', $upSql);
		$sql41tmp = 'ENGINE=MyISAM DEFAULT CHARSET='.$cfg_db_language;
		if($dsql->GetVersion() >= 4.1) {
			$upSql = preg_replace("#TYPE=MyISAM#i", $sql41tmp, $upSql);
		}
		$rs = $dsql->ExecuteNoneQuery($upSql);
		if(!$rs)
		{
			$upLog .= "{$upSql} \t失败 \t{$dsql->GetError()}\r\n";
			$errsql .= "{$upSql};\r\n";
		}
	}
	if(!empty($upLog)){
		file_put_contents('uplog.txt',$upLog);
		file_put_contents('uperr.txt',$errsql);
		$msg =  "<font color='red'>sql语句有部分没有执行，请查找本目录下uperr.txt，并将其内容在后台sql工具中执行,你还可以将uplog.txt发给我们…</font><br /><br />";
		$msg .=  "<a href='module-update.php'>点击此进行模块安装 &gt;&gt;</a>";
		ShowMsg($msg,'javascript:;');
		exit();
	}
	header('Location:module-update.php');
}else if($step==5) {
	include('./templates/step-5.html');
	exit();
}
ReWriteConfigAuto();
?>