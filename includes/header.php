<?php $me = current_user(); ?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=h(SITE_NAME)?></title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="site">
 <header class="top">
  <a href="index.php" class="logo" aria-label="CietTune">Diny<span>Tune</span></a>
  <div class="tagline">Your video publishing site <span class="release">Release v1.0.0</span><form class="search" action="videos.php" method="get"><input name="q" value="<?=h($_GET['q'] ?? '')?>"><button>Search Videos</button></form></div>
  <div class="account"><?php if($me): ?><a href="profile.php?id=<?=$me['id']?>"><?=h($me['username'])?></a> | <a href="logout.php">Logout</a><?php else: ?><a href="login.php">Login</a> | <a href="signup.php">Sign Up</a><?php endif; ?> | <a href="help.php">Help</a></div>
 </header>
 <nav class="main-nav"><a href="index.php">Home</a><a href="members.php">Browse Members</a><a href="videos.php">Browse Videos</a><a href="upload.php">Upload Videos</a><a href="comments.php">Comments Room</a></nav>
 <nav class="sub-nav"><a href="my-videos.php">My Videos</a> &nbsp; | &nbsp; <a href="messages.php">My Messages</a> &nbsp; | &nbsp; <a href="edit-profile.php">Edit Profile</a> &nbsp; | &nbsp; <a href="vote.php">Vote</a></nav>
 <main>
