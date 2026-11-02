<?php
require __DIR__.'/config.php';
$sort = $_GET['sort'] ?? 'recent';
$order = match($sort) { 'views' => 'v.views DESC', 'random' => 'RANDOM()', default => 'v.created_at DESC' };
$videos = db()->query("SELECT v.*, u.username FROM videos v JOIN users u ON u.id=v.user_id ORDER BY $order LIMIT 12")->fetchAll();
$members = db()->query("SELECT u.*, (SELECT COUNT(*) FROM videos v WHERE v.user_id=u.id) AS video_count FROM users u ORDER BY u.views DESC, u.created_at DESC LIMIT 6")->fetchAll();
$tags = ['video','music','games','retro','CietTune'];
require __DIR__.'/includes/header.php';
?>
<div class="columns">
 <section class="panel videos-panel">
  <h2>Most Viewed Videos</h2>
  <div class="video-grid">
  <?php if (!$videos): ?><p class="empty">No videos yet. <a href="upload.php">Upload the first video!</a></p><?php endif; ?>
  <?php foreach ($videos as $v): ?>
   <div class="video-cell">
    <a href="watch.php?id=<?=$v['id']?>"><img class="video-thumb" src="<?=h(video_thumb($v))?>" alt=""></a>
    <a href="watch.php?id=<?=$v['id']?>"><?=h($v['title'])?></a><br>
    Created: <?=h(fmt_date($v['created_at']))?><br>
    By: <a href="profile.php?id=<?=$v['user_id']?>"><?=h($v['username'])?></a><br>
    Duration: --:--:--<br><span class="red">Views: <?=number_format((int)$v['views'])?> | Comments: <?=comment_count((int)$v['id'])?></span>
   </div>
  <?php endforeach; ?>
  </div><p class="center"><a class="red" href="videos.php">Browse Videos</a></p>
 </section>
 <!-- MOST VIEWED MEMBERS -->
<aside class="panel members-panel">

    <h2>Most Viewed Members</h2>

    <div class="member-grid">

        <?php foreach ($members as $m): ?>

            <div class="member-cell">

                <a href="profile.php?id=<?= (int)$m['id'] ?>">
                    <img
                        class="avatar"
                        src="<?= h(avatar_url($m['pfp'] ?? '')) ?>"
                        alt="<?= h($m['username']) ?>"
                    >
                </a>

                <br>

                <a href="profile.php?id=<?= (int)$m['id'] ?>">
                    <?= h($m['username']) ?>
                </a>

                <br>

                Created:
                <?= h(fmt_date($m['created_at'])) ?>

                <br>

                <span class="red">
                    Views: <?= number_format((int)$m['views']) ?>
                </span>

            </div>

        <?php endforeach; ?>

        <?php if (!$members): ?>
            <p class="empty">No members yet.</p>
        <?php endif; ?>

    </div>

    <p class="center">
        <a class="red" href="members.php">Browse Members</a>
    </p>

</aside>
 <div class="recent-members"><strong>The last 16 Members Loggedin</strong><?php foreach(db()->query('SELECT id,username,created_at FROM users ORDER BY id DESC LIMIT 16') as $m): ?><div><a href="profile.php?id=<?=$m['id']?>"><?=h($m['username'])?></a><span><?=h(fmt_date($m['created_at']))?></span></div><?php endforeach; ?></div>
 </aside>
</div>
<div class="tags"><h3>Most Recent Tags</h3><?php foreach($tags as $tag): ?><a href="videos.php?q=<?=urlencode($tag)?>"><?=h($tag)?></a> &nbsp; <?php endforeach; ?></div>
<?php require __DIR__.'/includes/footer.php'; ?>
