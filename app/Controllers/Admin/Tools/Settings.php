<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\ResponseInterface;

class Settings extends BaseController
{
    protected SettingsModel $settings;

    public function __construct()
    {
        $this->settings = new SettingsModel();
    }

    // -------- UI (HTML) --------
    // GET /admin/tools/settings-ui
    public function index(): ResponseInterface
    {
        $data = [
            'auto_map_orphans'    => $this->settings->get('auto_map_orphans', '0') === '1',
            'date_display_format' => $this->settings->get('date_display_format', 'dd-mm-yyyy'),
            'visits_lite_public'  => $this->settings->get('visits_lite_public', '1') === '1',
            // environment info (read-only, don't write .env here)
            'android_token'       => getenv('ANDROID_API_TOKEN') ?: '',
            'dev_no_auth'         => getenv('DEV_NO_AUTH') ?: '',
        ];
        return $this->response->setBody(view('admin/tools/settings', $data));
    }

    // POST /admin/tools/settings-save
    public function save(): ResponseInterface
    {
        $auto   = $this->request->getPost('auto_map_orphans') ? '1' : '0';
        $format = $this->request->getPost('date_display_format') ?: 'dd-mm-yyyy';
        $pub    = $this->request->getPost('visits_lite_public') ? '1' : '0';

        $this->settings->set('auto_map_orphans', $auto);
        $this->settings->set('date_display_format', $format);
        $this->settings->set('visits_lite_public', $pub);

        return redirect()->to(site_url('admin/tools/settings-ui'))
            ->with('msg', 'Settings saved.');
    }

    // -------- JSON (back-compat) --------
    // GET /admin/tools/settings  → { ok: true, auto_map_orphans: "1"|"0", ... }
    public function json(): ResponseInterface
    {
        $payload = [
            'ok'                 => true,
            'auto_map_orphans'   => $this->settings->get('auto_map_orphans', '0'),
            'date_display_format'=> $this->settings->get('date_display_format', 'dd-mm-yyyy'),
            'visits_lite_public' => $this->settings->get('visits_lite_public', '1'),
        ];
        return $this->response->setJSON($payload);
    }

    // POST /admin/tools/settings/toggle  (legacy single-flag toggle kept for compatibility)
    // enable=1|0
    public function toggle(): ResponseInterface
    {
        $enable = $this->request->getPost('enable') === '1' ? '1' : '0';
        $this->settings->set('auto_map_orphans', $enable);

        return $this->response->setJSON([
            'ok'               => true,
            'auto_map_orphans' => $enable,
        ]);
    }
}
