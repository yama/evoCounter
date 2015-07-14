<?php
class EVOCOUNTER {
	
	var $modx;
	var $docid;
	var $lasthit;
	var $ip;
	var $ua;
	var $expire;
	var $tv;
	
	function __construct()
	{
		global $modx;
		
		$this->modx    = $modx;
		$this->docid   = $modx->documentIdentifier;
		$this->lasthit = $_SERVER['REQUEST_TIME'];
		$this->ip      = $_SERVER['REMOTE_ADDR'];
		$this->ua      = $modx->db->escape($_SERVER['HTTP_USER_AGENT']);
		
		$params = $this->modx->event->params;
		$expire = $params['expire'];
		if(isset($expire)&&!empty($expire)&&preg_match('@^[0-9\*\+\-/ ]+$@',$expire))
			$this->expire = $expire;
		else
			$this->expire = '60*60*24*90';
		$this->expire = eval("return {$this->expire};");
		
		if(isset($params['tv'])&&!empty($params['tv']))
			$this->tv = $params['tv'];
		else
			$this->tv = 'counter';
	}

	function getCount()
	{
		$this->purgeLog();
		$this->countUp();
		return $this->count();
	}

	function countUp()
	{
		$where = array();
		$where[] = "docid='{$this->docid}'";
		$where[] = "ip='{$this->ip}'";
		$where[] = "ua='{$this->ua}'";
		$where = join(' AND ', $where);
		$counter = $this->modx->db->getObject('counter', $where);
		if(empty($where) || !$counter)
			$this->insertLog();
	}
	
	function purgeLog()
	{
		$now = time();
		$this->modx->db->delete('[+prefix+]counter',"lasthit+{$this->expire}<{$now}");
	}
	
	function insertLog()
	{
		$f['lasthit'] = $this->lasthit;
		$f['docid']   = $this->docid;
		$f['ip']      = $this->ip;
		$f['ua']      = $this->ua;
		$this->modx->db->insert($f, '[+prefix+]counter');
	}
	
	function updateTv()
	{
	}
	
	function count($docid='')
	{
		if(empty($docid)) $docid = $this->docid;
		$rs = $this->modx->db->select('*', '[+prefix+]counter', "docid='{$docid}'");
		$count = $this->modx->db->getRecordCount($rs);
		$this->modx->loadExtension('DocAPI');
		$this->modx->doc->update(array($this->tv=>$count),$docid);
		return $count;
	}
	
	function createTable() {
	/*
DROP TABLE IF EXISTS `modx_counter`;
CREATE TABLE `modx_counter` (
  `internalKey` int(10) NOT NULL auto_increment,
  `docid` int(8) default NULL,
  `lasthit` int(20) NOT NULL default '0',
  `ip` varchar(50) NOT NULL default '',
  `ua` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`internalKey`)
) ENGINE=MyISAM CHARSET=utf8 COLLATE utf8_general_ci;
	*/
	}
}
