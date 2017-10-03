<?php
require __DIR__ . "/../config.php";
require __DIR__ . "/../vendor/autoload.php";

use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

$config = new Config();
$db = new Db($config);
$tracks = $db->getLastItems();

$feed = new Feed();

$last_updated_at = time();

$channel = new Channel();
$channel
    ->title('non-block.am')
    ->description('my private ch')
    ->url('http://example.jp')
    ->feedUrl($config->base_url . '/rss.php')
    ->language('ja-JP')
    ->copyright('jhon doe')
    ->pubDate($tracks[0]['start_at'])
    ->lastBuildDate(time())
    ->ttl(60)
    ->appendTo($feed);

foreach ($tracks as $track) {

    $media_file_url = "{$config->base_url}/files/{$track['ch_id']}/{$track['id']}.mp3";
    $start_at_str = date("Y-m-d", $track['start_at']);

    $item = new Item();
    $item
        ->title("{$track['title']} - {$start_at_str}")
        ->description("<div>{$track['title']}</div>")
        ->url($config->base_url . '/dummy')
        ->enclosure($media_file_url, 3600, 'audio/mpeg')
        ->pubDate($track['start_at'])
        ->appendTo($channel);
}

header('Content-type:application/rss+xml');
echo $feed;