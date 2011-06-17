<?php

error_reporting(0);

$username = $_GET['username'];
$feed_filename = "cache/$username.rss";
$expiration_time = 600; # seconds

$use_cache = true;

if ($use_cache && file_exists($feed_filename) && (time() - filemtime($feed_filename) < $expiration_time)) {
  echo file_get_contents($feed_filename);
} else {
  # stop anyone else from trying to update this right now
  # but only if they would get an outdated feed in return
  # (rather than a blank file)
  if (file_exists($feed_filename)) touch($feed_filename);
  
  $domain = "http://$username.tumblr.com";
  $api_url = '/api/read/json?num=10&debug=true';
  
  $fhandle = fopen("{$domain}{$api_url}", 'rb');
  fread($fhandle, 1);

  # check for a custom domain
  $stream_meta_data = stream_get_meta_data($fhandle);
  $headers = implode("\n", $stream_meta_data['wrapper_data']['headers']);
  // var_dump($stream_meta_data);die;
  if (strpos($headers, 'HTTP/1.1 301') === 0) {
    preg_match('/\nLocation: (.+)\n/', $headers, $matches);
    if (count($matches) >= 2) {
      fclose($fhandle);
      $domain = str_replace($api_url, '', $matches[1]);
      $fhandle = fopen("{$domain}{$api_url}", 'rb');
    } else {
      die;
    }

    $tumblr_data = '';
  } else {
    $tumblr_data = '{'; # DERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERPDERP
  }
  
  while (!feof($fhandle)) {
    $tumblr_data .= fread($fhandle, 8192);
  }
  fclose($fhandle);
    
  $tumblr_data = json_decode($tumblr_data);

  date_default_timezone_set($tumblr_data->tumblelog->timezone);

  require_once 'parse_post.php';

  $tumblelog = array(
    'title' => $tumblr_data->tumblelog->title,
    'description' => strip_tags($tumblr_data->tumblelog->description),
    'url' => $domain,
    'feed-url' => "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}",
    'posts' => array()
  );

  foreach($tumblr_data->posts as $post_data) {
    $parsed = parse_post($post_data);
  
    $post = array(
      'title' => $parsed['title'],
      'description' => $parsed['description'],
      'link' => $parsed['link'],
      'published-at' => $post_data->{'unix-timestamp'},
      'tags' => ($post_data->tags == NULL ? array() : $post_data->tags)
    );
  
    $tumblelog['posts'][] = $post;
  }

  ob_start();
  require_once 'template.rss.php';
  file_put_contents($feed_filename, ob_get_contents());
  ob_end_flush();
}