<?php
/*
 * Tiny QR Fallback
 * This is a minimal wrapper around a small QR generation using Google Charts as last resort
 * Replace with full phpqrcode library when needed.
 */
function qr_png($text, $size=200, $margin=2){
    // Try GD-less fallback via Google Charts (may be blocked by host)
    header('Content-Type: image/png');
    $enc = urlencode($text);
    readfile("https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chld=L|{$margin}&chl={$enc}");
}
