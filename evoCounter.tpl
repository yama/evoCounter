include_once(MODX_BASE_PATH . 'assetssnippetsevoCounterevoCounter.class.inc.php');
$counter = new EVOCOUNTER;
$count = $counter-getCount();
if(!isset($display)  !empty($display)) return $count;
