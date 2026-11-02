<?php
declare(strict_types=1);
session_start();

define('SITE_NAME', 'DinyTune');
define('DB_FILE', __DIR__ . '/data/DinyTune.sqlite');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('THUMB_DIR', __DIR__ . '/thumbs');
define('AVATAR_DIR', __DIR__ . '/avatars');
define('VIDEO_URL', 'uploads/');
define('THUMB_URL', 'thumbs/');
define('AVATAR_URL', 'avatars/');
define('FFMPEG', '/usr/local/bin/ffmpeg');
define('FFPROBE', '/usr/local/bin/ffprobe');

foreach ([__DIR__.'/data', UPLOAD_DIR, THUMB_DIR, AVATAR_DIR] as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
}
require_once __DIR__ . '/includes/functions.php';
