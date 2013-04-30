<?php

require_once(dirname(__FILE__) . '/../../php/xmlrpc.php');
require_once(dirname(__FILE__) . '/../_task/task.php');
eval(getPluginConf('stream'));

function getRelativePath($from, $to)
{
    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach($from as $depth => $dir) {
        if($dir === $to[$depth]) {
            array_shift($relPath);
        } else {
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
	
    return implode('/', $relPath);
}

function getDuration($filename) {
	$ffmpeg = getExternal('ffmpeg');
	if ($ffmpeg == '')
		return '';
	
	$mediainfo = trim(exec($ffmpeg . ' -i ' . escapeshellarg($filename) . ' 2>&1 | grep "Duration" | cut -d " " -f 4 | sed s/,//'));
	
	if ($mediainfo == '')
		return '';
		
	$times = explode(':', $mediainfo);
	$secs = explode('.', $times[2]);
	$duration = intval($times[0]) * 3600000 +
				intval($times[1]) * 60000 + 
				intval($secs[0]) * 1000 +
				intval($secs[1]);
	
	return '
			<duration>' . strval($duration) . '</duration>';
}

if (isset($_REQUEST['hash']) && isset($_REQUEST['no'])) {
	$req = new rXMLRPCRequest(new rXMLRPCCommand("f.get_frozen_path", array($_REQUEST['hash'], intval($_REQUEST['no']))));
	if ($req->success()) {
		$filename = $req->val[0];
		if (empty($filename)) {
			$req = new rXMLRPCRequest(array(
				new rXMLRPCCommand("d.open", $_REQUEST['hash']),
				new rXMLRPCCommand("f.get_frozen_path", array($_REQUEST['hash'], intval($_REQUEST['no']))),
				new rXMLRPCCommand("d.close", $_REQUEST['hash'])
			));
		
			if ($req->success())
				$filename = $req->val[1];
		}
		
		if (!empty($filename)) {
			$duration = getDuration($filename);
			$filename = getRelativePath(addslash($datapath), $filename);
		
			$playlist = '<?xml version="1.0" encoding="UTF-8"?>
<playlist xmlns="http://xspf.org/ns/0/" xmlns:vlc="http://www.videolan.org/vlc/playlist/ns/0/" version="1">
	<trackList>
		<track>
			<title>' . basename($filename) . '</title>
			<location>' . $webaddr . '?file=' . urlencode($filename) .'</location>' . $duration . '
			<extension application="http://www.videolan.org/vlc/playlist/0">
				<vlc:id>0</vlc:id>
				<vlc:option>network-caching=300000</vlc:option>
			</extension>
		</track>
	</trackList>
	<extension application="http://www.videolan.org/vlc/playlist/0">
		<vlc:item tid="0" />
	</extension>
</playlist>';
				
			header('Content-Type: application/vlc');
			header('Content-Disposition: attachment; filename="' . basename($filename) . '.xspf"');
			header('Content-Length: ' . strlen($playlist));
			echo $playlist;
			exit();
		}
	}
}

?>