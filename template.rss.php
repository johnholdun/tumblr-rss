<? 
  # header('Content-Type: text/xml');
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?= $tumblelog['title'] ?></title>
    <link><?= $tumblelog['url'] ?></link>
    <description><?= $tumblelog['description'] ?></description>
    <atom:link href="<?= $tumblelog['feed-url'] ?>" rel="self" type="application/rss+xml" />
<? foreach ($tumblelog['posts'] as $post): ?>
    <item>
      <title><?= $post['title'] ?></title>
      <description><![CDATA[ <?= $post['description'] ?> ]]></description>
      <link><?= $post['link'] ?></link>
<? foreach ($post['tags'] as $tag): ?>
      <category><?= $tag ?></category>
<? endforeach; ?>
      <guid isPermaLink="false"><?= $post['title'] ?> <?= $post['published-at'] ?></guid>
      <pubDate><?= date('r', $post['published-at']) ?></pubDate>
    </item>
<? endforeach; ?>
  </channel>
</rss>