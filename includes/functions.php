<?php
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE COLLATE NOCASE,
            email TEXT NOT NULL UNIQUE COLLATE NOCASE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            views INTEGER NOT NULL DEFAULT 0
        )");
        // Safe migration for existing databases: add profile picture and login date.
        $columns = $pdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('pfp', $columns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN pfp TEXT NOT NULL DEFAULT ''");
        }
        if (!in_array('last_login', $columns, true)) {
            $pdo->exec('ALTER TABLE users ADD COLUMN last_login TEXT');
        }
        $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT NOT NULL DEFAULT '',
            filename TEXT NOT NULL,
            thumbnail TEXT NOT NULL DEFAULT '',
            format TEXT NOT NULL DEFAULT 'mp4',
            views INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            video_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            body TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(video_id) REFERENCES videos(id) ON DELETE CASCADE,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }
    return $pdo;
}
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $q = db()->prepare('SELECT id, username, email, created_at, views, pfp, last_login FROM users WHERE id = ?');
    $q->execute([(int)$_SESSION['user_id']]);
    return $q->fetch() ?: null;
}
function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function redirect(string $url): never { header('Location: '.$url); exit; }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400); exit('Invalid request token. Go back and try again.');
    }
}
function fmt_date(string $date): string {
    $t = strtotime($date);
    return $t ? date('m/d/Y', $t) : $date;
}
function video_thumb(array $video): string {
    return !empty($video['thumbnail']) && is_file(THUMB_DIR.'/'.$video['thumbnail'])
        ? THUMB_URL.$video['thumbnail'] : 'assets/default-avatar.svg';
}
function make_thumbnail(string $source, string $dest): bool {
    if (!function_exists('exec')) return false;
    $cmd = escapeshellcmd(FFMPEG).' -y -ss 00:00:02 -i '.escapeshellarg($source).' -frames:v 1 -vf '.escapeshellarg('scale=240:135:force_original_aspect_ratio=decrease,pad=240:135:(ow-iw)/2:(oh-ih)/2').' '.escapeshellarg($dest).' 2>&1';
    exec($cmd, $output, $code);
    return $code === 0 && is_file($dest);
}
function transcode_video(string $source, string $dest): bool {
    if (!function_exists('exec')) return false;
    $cmd = escapeshellcmd(FFMPEG).' -y -i '.escapeshellarg($source).' -c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 128k -movflags +faststart '.escapeshellarg($dest).' 2>&1';
    exec($cmd, $output, $code);
    return $code === 0 && is_file($dest) && filesize($dest) > 0;
}
function comment_count(int $videoId): int { $q=db()->prepare('SELECT COUNT(*) FROM comments WHERE video_id=?'); $q->execute([$videoId]); return (int)$q->fetchColumn(); }
function avatar_url(?string $filename): string {
    if ($filename && preg_match('/^[a-f0-9]{24}\\.(jpg|jpeg|png|gif|webp)$/i', $filename) && is_file(AVATAR_DIR.'/'.$filename)) {
        return AVATAR_URL . rawurlencode($filename);
    }
    return 'assets/default-avatar.svg';
}
