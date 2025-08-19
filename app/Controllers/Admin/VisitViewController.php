<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class VisitViewController extends BaseController
{
    /**
     * Serve a document (image/PDF/etc) by ID.
     * URL: /index.php/admin/visit/file?id=123
     */
    public function file()
    {
        $id = (int) $this->request->getGet('id');
        if ($id <= 0) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db  = db_connect();
        $doc = $db->table('documents')->where('id', $id)->get()->getRowArray();
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound("Document #{$id} not found");
        }

        $path     = (string) ($doc['path'] ?? '');
        $filename = (string) ($doc['filename'] ?? "document-{$id}");
        $mime     = (string) ($doc['mime'] ?? '');

        // If $path is a URL, just redirect.
        if ($path && preg_match('~^https?://~i', $path)) {
            return redirect()->to($path);
        }

        // Try a few typical locations if $path is relative.
        $candidates = [];
        if ($path !== '') {
            $candidates[] = $path;                                   // as-is
            $candidates[] = FCPATH . ltrim($path, '/');              // public/
            $candidates[] = WRITEPATH . ltrim($path, '/');           // writable/
        }
        $candidates[] = FCPATH . 'uploads/' . $filename;             // public/uploads/<filename>
        $candidates[] = WRITEPATH . 'uploads/' . $filename;          // writable/uploads/<filename>

        $file = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $file = $candidate;
                break;
            }
        }

        if (! $file) {
            throw PageNotFoundException::forPageNotFound("File for document #{$id} not found");
        }

        // Inline for common types (images/PDF). Otherwise download.
        $inlineMimes = ['image/', 'application/pdf'];
        $disposition = 'attachment';
        foreach ($inlineMimes as $prefix) {
            if ($mime !== '' && str_starts_with(strtolower($mime), $prefix)) {
                $disposition = 'inline';
                break;
            }
        }

        // If we have a MIME, set it; otherwise let download() infer.
        if ($disposition === 'inline' && $mime !== '') {
            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
                ->setBody(file_get_contents($file));
        }

        // Fallback to download (will set proper headers)
        return $this->response
            ->download($file, null)
            ->setFileName($filename)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"');
    }
}
