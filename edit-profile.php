<?php
require __DIR__ . '/config.php';
require_login();
$me = current_user();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (isset($_POST['change_pfp'])) {
        if (!isset($_FILES['pfp']) || $_FILES['pfp']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Choose an image to upload.';
        } elseif ($_FILES['pfp']['size'] > 3 * 1024 * 1024) {
            $error = 'The image must be smaller than 3 MB.';
        } else {
            $tmp = $_FILES['pfp']['tmp_name'];
            $mime = '';
            if (function_exists('finfo_open')) {
                $fi = finfo_open(FILEINFO_MIME_TYPE);
                $mime = $fi ? finfo_file($fi, $tmp) : '';
                if ($fi) finfo_close($fi);
            }
            $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp'];
            if (!isset($allowed[$mime]) || @getimagesize($tmp) === false) {
                $error = 'Please upload a valid JPG, PNG, GIF or WebP image.';
            } elseif (!is_dir(AVATAR_DIR) || !is_writable(AVATAR_DIR)) {
                $error = 'The avatars folder is not writable. Check its permissions.';
            } else {
                $newName = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
                if (move_uploaded_file($tmp, AVATAR_DIR . '/' . $newName)) {
                    try {
                        $old = $me['pfp'] ?? '';
                        $q = db()->prepare('UPDATE users SET pfp = ? WHERE id = ?');
                        $q->execute([$newName, $me['id']]);
                        if ($old && preg_match('/^[a-f0-9]{24}\\.(jpg|jpeg|png|gif|webp)$/i', $old)) {
                            $oldPath = AVATAR_DIR . '/' . $old;
                            if (is_file($oldPath)) @unlink($oldPath);
                        }
                        $message = 'Profile picture updated.';
                    } catch (Throwable $e) {
                        @unlink(AVATAR_DIR . '/' . $newName);
                        $error = 'Could not save the profile picture.';
                    }
                } else {
                    $error = 'Could not upload the image. Check folder permissions.';
                }
            }
        }
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } else {
            try {
                $q = db()->prepare('UPDATE users SET email = ? WHERE id = ?');
                $q->execute([$email, $me['id']]);
                $message = 'Profile updated.';
            } catch (Throwable $e) {
                $error = 'That email address is already in use.';
            }
        }
    }
    $me = current_user();
}
require __DIR__ . '/includes/header.php';
?>
<div class="form-box">
    <h3>Edit Profile</h3>
    <?php if ($message): ?><p class="success"><?=h($message)?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>

    <h4>Profile Picture</h4>
    <p><img class="avatar" src="<?=h(avatar_url($me['pfp'] ?? ''))?>" alt="Profile picture" width="80" height="80"></p>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
        <input type="hidden" name="change_pfp" value="1">
        <div><label>Choose image (JPG, PNG, GIF or WebP; max. 3 MB):</label><input type="file" name="pfp" accept="image/jpeg,image/png,image/gif,image/webp" required></div>
        <button type="submit">Upload Picture</button>
    </form>

    <h4>Account Details</h4>
    <form method="post">
        <input type="hidden" name="csrf" value="<?=h(csrf_token())?>">
        <div><label>Username:</label><input value="<?=h($me['username'])?>" disabled></div>
        <div><label>Email:</label><input type="email" name="email" value="<?=h($me['email'])?>" required></div>
        <button type="submit">Save Profile</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
