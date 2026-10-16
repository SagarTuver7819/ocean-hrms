<?php

namespace App\Services;

use App\Core\Database;

/**
 * Employee profile photo stored under public/assets/uploads/photos and referenced as an asset path.
 */
class EmployeePhoto
{
    private const DIR = 'public/assets/uploads/photos';
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Replaces the photo with the uploaded file; throws RuntimeException with a user-facing message. */
    public static function save(int $employeeId, ?array $file): void
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new \RuntimeException('Please choose a photo.');
        }
        $path = store_upload($file, self::DIR, self::MIMES, 'emp' . $employeeId . '_', 3 * 1024 * 1024);
        self::deleteFile($employeeId);
        Database::run('UPDATE employees SET profile_photo = ?, updated_at = NOW() WHERE id = ?', [substr($path, strlen('public/')), $employeeId]);
        activity_log('employee', 'photo_updated', $employeeId);
    }

    public static function remove(int $employeeId): void
    {
        self::deleteFile($employeeId);
        Database::run('UPDATE employees SET profile_photo = NULL, updated_at = NOW() WHERE id = ?', [$employeeId]);
        activity_log('employee', 'photo_removed', $employeeId);
    }

    private static function deleteFile(int $employeeId): void
    {
        $old = (string) Database::value('SELECT profile_photo FROM employees WHERE id = ?', [$employeeId]);
        if (str_starts_with($old, 'assets/uploads/photos/') && is_file(BASE_PATH . '/public/' . $old)) {
            @unlink(BASE_PATH . '/public/' . $old);
        }
    }
}
