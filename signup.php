<?php
require __DIR__.'/config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 check_csrf();
 $username=trim($_POST['username']??''); $email=trim($_POST['email']??'');
 $password=$_POST['password']??''; $confirm=$_POST['confirm']??'';
 if (!preg_match('/^[a-zA-Z0-9_.-]{3,24}$/',$username)) $error='Username must be 3-24 characters (letters, numbers, _ . -).';
 elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Enter a valid email address.';
 elseif (strlen($password)<8) $error='Password must contain at least 8 characters.';
 elseif ($password!==$confirm) $error='Passwords do not match.';
 else {
  try { $q=db()->prepare('INSERT INTO users(username,email,password_hash) VALUES(?,?,?)'); $q->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT)]); $_SESSION['user_id']=(int)db()->lastInsertId(); redirect('index.php'); }
  catch(Throwable $e){$error='That username or email is already registered.';}
 }
}
require __DIR__.'/includes/header.php'; ?>
<div class="form-box"><h3>Sign Up</h3><?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
<div><label for="username">User:</label><input id="username" name="username" maxlength="24" required></div>
<div><label for="email">Email:</label><input id="email" type="email" name="email" required></div>
<div><label for="password">Password:</label><input id="password" type="password" name="password" required></div>
<div><label for="confirm">Confirm:</label><input id="confirm" type="password" name="confirm" required></div>
<button type="submit">Sign Up</button></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>
