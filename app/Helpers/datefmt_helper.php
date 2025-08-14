<?php
if (!function_exists('dmy_to_iso')) {
    function dmy_to_iso(?string $s): ?string {
        $s = trim((string)$s);
        if ($s === '' || $s === '0' || $s === '0000-00-00') return null;
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $s, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s)) {
            return $s;
        }
        $ts = strtotime($s);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
if (!function_exists('iso_to_dmy')) {
    function iso_to_dmy(?string $iso): ?string {
        if (!$iso || $iso === '0000-00-00') return null;
        $ts = strtotime($iso);
        return $ts ? date('d-m-Y', $ts) : null;
    }
}
