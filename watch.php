<?php
require __DIR__.'/config.php';$id=(int)($_GET['id']??0);$q=db()->prepare('SELECT v.*,u.username,u.id AS owner_id FROM videos v JOIN users u ON u.id=v.user_id WHERE v.id=?');$q->execute([$id]);$v=$q->fetch();if(!$v){http_response_code(404);exit('Video not found.');}db()->prepare('UPDATE videos SET views=views+1 WHERE id=?')->execute([$id]);$v['views']++;$me=current_user();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){require_login();check_csrf();$body=trim($_POST['body']??'');if($body!==''){db()->prepare('INSERT INTO comments(video_id,user_id,body) VALUES(?,?,?)')->execute([$id,$me['id'],$body]);redirect('watch.php?id='.$id.'#comments');}}
$comments=db()->prepare('SELECT c.*,u.username FROM comments c JOIN users u ON u.id=c.user_id WHERE c.video_id=? ORDER BY c.id DESC');$comments->execute([$id]);$related=db()->prepare('SELECT v.*,u.username FROM videos v JOIN users u ON u.id=v.user_id WHERE v.id<>? ORDER BY v.created_at DESC LIMIT 5');$related->execute([$id]);require __DIR__.'/includes/header.php';?>
<div class="watch-layout"><section class="watch-main"><h2><?=h($v['title'])?></h2><div class="player">
<video controls preload="metadata" poster="<?=h(video_thumb($v))?>" width="100%"><source src="<?=h(VIDEO_URL.$v['filename'])?>" type="video/mp4">Your browser does not support HTML5 video. Please use a modern browser.</video>
<!-- JW Player 5.7 legacy integration: place jwplayer.js and player.swf in /jwplayer/ and enable the block below.
<script type="text/javascript" src="jwplayer/jwplayer.js"></script>
<div id="legacy-player"></div>
<script>jwplayer('legacy-player').setup({file:'<?=h(VIDEO_URL.$v['filename'])?>',image:'<?=h(video_thumb($v))?>',width:'640',height:'360',flashplayer:'jwplayer/player.swf'});</script>
-->
</div><div class="video-info">By: <a href="profile.php?id=<?=$v['owner_id']?>"><?=h($v['username'])?></a> | <span class="red">Views: <?=number_format($v['views'])?></span><p><?=nl2br(h($v['description']))?></p></div>
<h3 id="comments">Comments (<?=count($comments->fetchAll())?>)</h3><?php $comments->execute([$id]);foreach($comments as $c):?><div class="comment"><a href="profile.php?id=<?=$c['user_id']?>"><?=h($c['username'])?></a> — <?=h(fmt_date($c['created_at']))?><p><?=nl2br(h($c['body']))?></p></div><?php endforeach;?>
<?php if($me):?><form class="form-box" method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><textarea name="body" rows="3" maxlength="2000" required placeholder="Write a comment"></textarea><button>Post Comment</button></form><?php else:?><p><a href="login.php">Log in</a> to comment.</p><?php endif;?></section>
<aside class="watch-side"><h3>Related Videos</h3><?php foreach($related as $r):?><div class="video-cell"><a href="watch.php?id=<?=$r['id']?>"><img class="video-thumb" src="<?=h(video_thumb($r))?>" alt=""><?=h($r['title'])?></a></div><?php endforeach;?></aside></div>
<?php require __DIR__.'/includes/footer.php';?>
