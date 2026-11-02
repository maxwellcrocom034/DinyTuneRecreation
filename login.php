<?php
require __DIR__.'/config.php'; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$q=db()->prepare('SELECT * FROM users WHERE username=? COLLATE NOCASE');$q->execute([trim($_POST['username']??'')]);$u=$q->fetch();if($u&&password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];$up=db()->prepare('UPDATE users SET last_login=CURRENT_TIMESTAMP WHERE id=?');$up->execute([(int)$u['id']]);redirect('index.php');}$error='Incorrect username or password.';}
require __DIR__.'/includes/header.php'; ?>
<div class="form-box"><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?>
<div><label for="username">User:</label><input id="username" name="username" required></div><div><label for="password">Password:</label><input id="password" type="password" name="password" required></div><button type="submit">Login</button></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>
