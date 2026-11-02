<?php
require __DIR__ . '/config.php';
$sort = $_GET['sort'] ?? 'recent';
$orders = [
    'recent' => 'id DESC',
    'views' => 'views DESC, id DESC',
    'logged' => 'last_login DESC, id DESC',
    'random' => 'RANDOM()'
];
if (!isset($orders[$sort])) $sort = 'recent';
$users = db()->query('SELECT id, username, created_at, views, pfp FROM users ORDER BY ' . $orders[$sort] . ' LIMIT 120')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="listing">
    <div class="listing-nav">
        <a href="members.php?sort=recent">Most Recent</a> |
        <a href="members.php?sort=views">Most Viewed</a> |
        <a href="members.php?sort=logged">Latest Loggedin</a> |
        <a href="members.php?sort=random">Random</a>
    </div>
    <div class="member-list">
        <?php foreach ($users as $u): ?>
            <div class="member-cell">
                <img class="avatar" src="<?=h(avatar_url($u['pfp'] ?? ''))?>" alt="">
                <br><a href="profile.php?id=<?=(int)$u['id']?>"><?=h($u['username'])?></a>
                <br>Created: <?=h(fmt_date($u['created_at']))?>
                <br><span class="red">Views: <?=number_format((int)$u['views'])?></span>
            </div>
        <?php endforeach; ?>
        <?php if (!$users): ?><p class="empty">No members yet.</p><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
