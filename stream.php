<?php

require_once(dirname(__FILE__) . '/../../php/util.php');
eval(getPluginConf('stream'));

$filename = '';
if (isset($_GET['file']))
	$filename = $_GET['file'];

if (empty($filename) || !file_exists(addslash($datapath) . $filename)) {
	header('HTTP/1.1 404 Not Found');
	echo '<h1>404 Not Found</h1>';
	echo 'The file that you have requested cannot be found.';
	exit();
}

if (!in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $accepted_extentions)) {
	header('HTTP/1.1 403 Forbidden');
	echo '<h1>403 Forbidden</h1>';
	echo 'The file that you have requested cannot be streamed.';
	exit();
}

header('X-Accel-Redirect: /stream/' . $filename);
?>