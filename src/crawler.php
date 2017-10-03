<?php
require __DIR__ . "/../config.php";
require __DIR__ . "/../vendor/autoload.php";

$config = new Config();
$db = new Db($config);

foreach ($config->target_ch_id_list as $target_ch_id) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config->radios_url . $target_ch_id);
    curl_setopt($ch, CURLOPT_USERAGENT, $config->user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $json_response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode != 200) {
        die("http status code was not 200.");
    }

    $res = json_decode($json_response, true);
    if (is_null($res) || $res['status'] !== 200) {
        die("invalid json");
    }

    $radios_has_sound_source_list = $res['data']['radio']['radios_has_sound_sources'];

    foreach ($radios_has_sound_source_list as $radios_has_sound_source) {
        $sound_source = $radios_has_sound_source['sound_source'];

        if (!isset($sound_source['sound_file']) || !isset($sound_source['sound_file']['url'])) continue;

        $id = $sound_source['id'];
        $url = $sound_source['sound_file']['url'];
        $title = $sound_source['title'];
        $start_at = strtotime($sound_source['start_at']);

//        echo "$id, $target_ch_id, $title, $start_at\n";

        $target_ch_id_dir = "{$config->base_files_dir}/{$target_ch_id}";
        $id_base_path = "{$target_ch_id_dir}/{$id}";

        if (!file_exists($target_ch_id_dir)) mkdir($target_ch_id_dir);

        if ($db->isExistsId($id)) {
            continue;
        }

        $dl_fh = fopen($url, "r") or die('fail open url:{$url}');
        $mp3_fh = fopen($id_base_path . ".mp3", "w") or die('fail write:{$id_base_path}.mp3');

        while (!feof($dl_fh)) {
            fwrite($mp3_fh, fread($dl_fh, 1024 * 1024));
        }

        fclose($mp3_fh);
        fclose($dl_fh);

        if (!$db->insert($id, $target_ch_id, $title, $start_at)) die("db insert error");
    }
}

