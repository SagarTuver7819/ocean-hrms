<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Letterhead;

class SettingController extends Controller
{
    private const IMAGE_MIMES = ['image/png', 'image/jpeg', 'image/webp'];

    public function index(): void
    {
        if (!can('settings.manage') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $db = $this->db();
        $rows = $db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        $this->view('hr/settings', [
            'title' => 'Company Settings',
            'page' => 'settings',
            'settings' => $settings,
        ]);
    }

    public function save(): void
    {
        if (!can('settings.manage') && !can('*')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $this->requireCsrf('settings');
        $keys = ['company_name','company_tagline','company_email','company_phone','company_website','company_address','careers_intro','careers_positions',
                 'letter_ref_prefix','letter_signatory_name','letter_signatory_designation','letter_week_off','letter_notice_period','letter_jurisdiction'];
        foreach ($keys as $key) {
            $this->put($key, trim((string) $this->input($key, '')));
        }
        activity_log('settings', 'updated');
        $this->flash('success', 'Settings saved.');
        $this->redirect('settings');
    }

    public function letterhead(): void
    {
        if (!can('settings.manage')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $this->view('hr/letterhead', [
            'title'    => 'Letterhead Setup',
            'page'     => 'letterhead',
            'settings' => settings_all(),
            'lh'       => Letterhead::config(),
        ]);
    }

    public function saveLetterhead(): void
    {
        if (!can('settings.manage')) {
            $this->flash('error', 'Permission denied.');
            $this->redirect('dashboard');
        }
        $this->requireCsrf('settings/letterhead');

        $mode = (string) $this->input('letterhead_mode', 'auto');
        $this->put('letterhead_mode', array_key_exists($mode, Letterhead::MODES) ? $mode : 'auto');
        foreach (['letterhead_top_mm' => [0, 120], 'letterhead_bottom_mm' => [0, 80], 'letterhead_side_mm' => [5, 30]] as $key => [$min, $max]) {
            $this->put($key, (string) max($min, min($max, (int) $this->input($key, $min))));
        }

        $current = settings_all();
        try {
            foreach (['letterhead_header', 'letterhead_full', 'letterhead_footer'] as $key) {
                if ($this->input('remove_' . $key)) {
                    $this->removeImage($current[$key] ?? '');
                    $this->put($key, '');
                }
                $path = store_upload($_FILES[$key] ?? null, 'public/assets/uploads/letterhead', self::IMAGE_MIMES, str_replace('letterhead_', 'lh_', $key) . '_', 8 * 1024 * 1024);
                if ($path) {
                    $this->removeImage($current[$key] ?? '');
                    $this->put($key, substr($path, strlen('public/')));
                }
            }
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('settings/letterhead');
        }

        settings_all(true);
        activity_log('settings', 'letterhead_updated');
        $this->flash('success', 'Letterhead saved.');
        $this->redirect('settings/letterhead');
    }

    private function put(string $key, string $value): void
    {
        $this->db()->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        )->execute([$key, $value]);
    }

    private function removeImage(string $relative): void
    {
        $file = BASE_PATH . '/public/' . $relative;
        if ($relative && str_starts_with($relative, 'assets/uploads/letterhead/') && is_file($file)) {
            @unlink($file);
        }
    }
}
