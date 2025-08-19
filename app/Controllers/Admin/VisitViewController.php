<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class VisitViewController extends BaseController
{
    /**
     * Serve a document (image/PDF/etc) by ID.
     * URL: /index.php/admin/visit/file?id=123[&debug=1]
     */
    public function file()
    {
        $id = (int) $this->request->getGet('id');
        $debug = (bool) $this->request->getGet('debug');

        if ($id <= 0) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db  = db_connect();
        $doc = $db->table('documents')->where('id', $id)->get()->getRowArray();
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound("Document #{$id} not found");
        }

        // DB columns we expect
        $path     = (string) ($doc['path'] ?? '');
        $filename = (string) ($doc['filename'] ?? "document-{$id}");
        $mime     = (string) ($doc['mime'] ?? '');

        // 1) If path is an absolute URL → redirect.
        if ($path && preg_match('~^https?://~i', $path)) {
            return redirect()->to($path);
        }

        // 2) Build candidate file paths.
        $candidates = $this->buildCandidates($path, $filename);

        // 3) Try to locate the file.
        $file = $this->firstExisting($candidates);

        // 4) If not found, attempt a shallow recursive scan by filename in common roots.
        if (! $file) {
            $roots = $this->roots();
            foreach ($roots as $root) {
                $file = $this->findByFilename($root, $filename, 3); // depth limit
                if ($file) {
                    break;
                }
            }
        }

        if (! $file || !is_file($file)) {
            if ($debug) {
                return $this->response->setJSON([
                    'error'      => 'file_not_found',
                    'id'         => $id,
                    'doc'        => $doc,
                    'tried'      => $candidates,
                    'searchedIn' => $this->roots(),
                ])->setStatusCode(404);
            }
            throw PageNotFoundException::forPageNotFound("File for document #{$id} not found");
        }

        // 5) MIME detection fallback
        if ($mime === '') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $file) ?: '';
                finfo_close($finfo);
            }
        }

        // 6) Inline common types; otherwise download.
        $disposition = $this->isInlineMime($mime) ? 'inline' : 'attachment';

        if ($disposition === 'inline' && $mime !== '') {
            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
                ->setBody(file_get_contents($file));
        }

        // Fallback to download helper (sets headers)
        return $this->response
            ->download($file, null)
            ->setFileName($filename)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"');
    }

    /**
     * Build a list of candidate absolute paths to try.
     */
    private function buildCandidates(string $path, string $filename): array
    {
        $candidates = [];

        // If DB 'path' looks like a full path to a file, try it as-is
        if ($path !== '' && (str_ends_with($path, $filename) || is_file($path))) {
            $candidates[] = $path;
        }

        // If DB 'path' looks like a directory, join with filename
        if ($path !== '' && !str_ends_with($path, $filename)) {
            $candidates[] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $filename;
        }

        // Optional custom root via .env (e.g. UPLOADS_ROOT=/home/xxx/public_html/uploads)
        $envRoot = rtrim((string) env('UPLOADS_ROOT'), '/\\');
        if ($envRoot !== '') {
            $candidates[] = $envRoot . DIRECTORY_SEPARATOR . $filename;
            $candidates[] = $envRoot . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
            $candidates[] = $envRoot . DIRECTORY_SEPARATOR . trim($path, '/\\') . DIRECTORY_SEPARATOR . $filename;
        }

        // Public and writable common locations
        $roots = $this->roots();
        foreach ($roots as $root) {
            $candidates[] = $root . $filename;
            if ($path !== '') {
                $candidates[] = $root . ltrim($path, '/\\');
                $candidates[] = $root . trim($path, '/\\') . DIRECTORY_SEPARATOR . $filename;
            }
            // typical subfolders
            $candidates[] = $root . 'uploads' . DIRECTORY_SEPARATOR . $filename;
            $candidates[] = $root . 'uploads' . DIRECTORY_SEPARATOR . 'visits' . DIRECTORY_SEPARATOR . $filename;
            $candidates[] = $root . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $filename;
        }

        // Unique-name only guesses
        $candidates = array_values(array_unique(array_filter($candidates)));
        return $candidates;
    }

    /**
     * Roots to search under (trailing slash ensured).
     */
    private function roots(): array
    {
        $roots = [];

        $add = function ($p) use (&$roots) {
            if ($p && is_dir($p)) {
                $p = rtrim($p, '/\\') . DIRECTORY_SEPARATOR;
                $roots[] = $p;
            }
        };

        $add(FCPATH);                         // public/
        $add(FCPATH . 'uploads');             // public/uploads
        $add(WRITEPATH);                      // writable/
        $add(WRITEPATH . 'uploads');          // writable/uploads

        $envRoot = (string) env('UPLOADS_ROOT');
        if ($envRoot) {
            $add($envRoot);
        }

        return array_values(array_unique($roots));
    }

    /**
     * Return the first existing file from candidates.
     */
    private function firstExisting(array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if ($c && is_file($c)) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Shallow recursive find by filename (depth-limited).
     */
    private function findByFilename(string $dir, string $filename, int $maxDepth = 2, int $depth = 0): ?string
    {
        if ($depth > $maxDepth || !is_dir($dir)) {
            return null;
        }

        // Direct candidate
        $direct = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        if (is_file($direct)) {
            return $direct;
        }

        $items = @scandir($dir);
        if (! $items) {
            return null;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $found = $this->findByFilename($path, $filename, $maxDepth, $depth + 1);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function isInlineMime(string $mime): bool
    {
        $mime = strtolower($mime);
        return $mime === 'application/pdf' || str_starts_with($mime, 'image/');
    }
}
