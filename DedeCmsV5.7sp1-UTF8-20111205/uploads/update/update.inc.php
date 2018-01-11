<?php
function CheckModelStatus($hash) 
{
	global $moduleLocal;
	if(file_exists($moduleLocal.$hash.'.xml')&&file_exists($moduleLocal.$hash.'-readme.php'))
	{
		echo 'checked = "true"';
		return 1;
	}
	else
	{
		echo 'disabled = "true"';
		return -1;
	}
}

function ReWriteConfigAuto()
{
	global $dsql;
	$configfile = DEDEROOT.'/data/config.cache.inc.php';
	if(!is_writeable($configfile))
	{
		return ;
	}
	$fp = fopen($configfile,'w');
	flock($fp,3);
	fwrite($fp,"<"."?php\r\n");
	$dsql->SetQuery("Select `varname`,`type`,`value`,`groupid` From `#@__sysconfig` order by aid asc ");
	$dsql->Execute();
	while($row = $dsql->GetArray())
	{
		if($row['type']=='number' && trim($row['value']) != '') fwrite($fp,"\${$row['varname']} = ".$row['value'].";\r\n");
		else fwrite($fp,"\${$row['varname']} = '".str_replace("'",'',$row['value'])."';\r\n");
	}
	fwrite($fp,"?".">");
	fclose($fp);
}

function dir_clear($dir) { 
    $directory = dir($dir);                
    while($entry = $directory->read()) {   
        $filename = $dir.'/'.$entry;       
        if(is_file($filename)) {           
            @unlink($filename);
        }
    }
    $directory->close();                   
} 

function TestWrite($d)
{
    $tfile = '_dedet.txt';
    $d = preg_replace("#\/$#", '', $d);
    $fp = @fopen($d.'/'.$tfile,'w');
    if(!$fp) return false;
    else
    {
        fclose($fp);
        $rs = @unlink($d.'/'.$tfile);
        if($rs) return true;
        else return false;
    }
}

function UpDateCatCache()
{
    global $dsql,$cfg_multi_site;
    $cache1 = DEDEDATA."/cache/inc_catalog_base.inc";
    $dsql->SetQuery("Select id,reid,channeltype,issend From `#@__arctype`");
    $dsql->Execute();
    $fp1 = fopen($cache1,'w');
    $phph = '?';
    $fp1Header = "<{$phph}php\r\nglobal \$cfg_Cs;\r\n\$cfg_Cs=array();\r\n";
    fwrite($fp1,$fp1Header);
    while($row=$dsql->GetObject())
    {
        fwrite($fp1,"\$cfg_Cs[{$row->id}]=array({$row->reid},{$row->channeltype},{$row->issend});\r\n");
    }
    fwrite($fp1,"{$phph}>");
    fclose($fp1);
}