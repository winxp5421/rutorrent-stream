<?php

# Location of stream.php
$webaddr = 'http://torr.dgby.org/stream.php';

# Path to torrent data, should be same root as /stream in nginx config.
$datapath = '/var/rtorrent/torrents';

# Accepted Extentions (streamable)
$accepted_extentions = array(
	'avi', 'mkv', 'ts', 'mp4', 'flv', 'wmv',
	'mpg', 'mpeg', 'ogg', 'wma', 'mp3', 'flac'
);

?>