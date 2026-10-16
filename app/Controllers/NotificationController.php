<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\View;

class NotificationController extends Controller
{
    public static function recent(int $userId, int $limit = 8): array
    {
        return Database::all('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, $limit), [$userId]);
    }

    public static function unread(int $userId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
    }

    public function index(): void
    {
        $this->view('notifications/index', [
            'title' => 'Notifications',
            'page' => 'notifications',
            'items' => self::recent((int) Auth::id(), 200),
        ]);
    }

    public function feed(): void
    {
        $userId = (int) Auth::id();
        $items = self::recent($userId);
        ob_start();
        View::partial('partials/notification-items', ['items' => $items]);
        $this->json([
            'unread' => self::unread($userId),
            'html' => ob_get_clean(),
            'items' => array_map(fn($n) => ['id' => (int) $n['id'], 'title' => $n['title'], 'message' => $n['message'], 'is_read' => (int) $n['is_read']], $items),
        ]);
    }

    public function open(string $id): void
    {
        $n = Database::first('SELECT * FROM notifications WHERE id = ? AND user_id = ?', [(int) $id, (int) Auth::id()]);
        if (!$n) {
            $this->redirect('notifications');
        }
        Database::run('UPDATE notifications SET is_read = 1 WHERE id = ?', [$n['id']]);
        $this->redirect($n['link'] ? ltrim($n['link'], '/') : 'notifications');
    }

    public function readAll(): void
    {
        $this->requireCsrf('notifications');
        Database::run('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0', [(int) Auth::id()]);
        $this->flash('success', 'All notifications marked as read.');
        $back = (string) $this->input('back');
        $this->redirect(preg_match('#^[a-z0-9/_\-?=&]*$#i', $back) ? $back : 'notifications');
    }
}
