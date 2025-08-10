<?php namespace App\Libraries;

// Minimal Code128 (subset B) PNG generator (no GD extension assumptions except basic GD)
class Barcode128
{
    private $codes = [
        ' '=>'212222','!' =>'222122','"' =>'222221','#' =>'121223','$' =>'121322','%' =>'131222','&' =>'122213','\''=>'122312','(' =>'132212',')' =>'221213','*' =>'221312','+' =>'231212',',' =>'112232','-' =>'122132','.' =>'122231','/' =>'113222',
        '0' =>'123122','1' =>'123221','2' =>'223211','3' =>'221132','4' =>'221231','5' =>'213212','6' =>'223112','7' =>'312131','8' =>'311222','9' =>'321122',':' =>'321221',';' =>'312212','<' =>'322112','=' =>'322211','>' =>'212123','?' =>'212321',
        '@' =>'232121','A' =>'111323','B' =>'131123','C' =>'131321','D' =>'112313','E' =>'132113','F' =>'132311','G' =>'211313','H' =>'231113','I' =>'231311','J' =>'112133','K' =>'112331','L' =>'132131','M' =>'113123','N' =>'113321','O' =>'133121',
        'P' =>'313121','Q' =>'211331','R' =>'231131','S' =>'213113','T' =>'213311','U' =>'213131','V' =>'311123','W' =>'311321','X' =>'331121','Y' =>'312113','Z' =>'312311','[' =>'332111','\\'=>'314111',']' =>'221411','^' =>'431111','_' =>'111224',
        '`' =>'111422','a' =>'121124','b' =>'121421','c' =>'141122','d' =>'141221','e' =>'112214','f' =>'112412','g' =>'122114','h' =>'122411','i' =>'142112','j' =>'142211','k' =>'241211','l' =>'221114','m' =>'413111','n' =>'241112','o' =>'134111',
        'p' =>'111242','q' =>'121142','r' =>'121241','s' =>'114212','t' =>'124112','u' =>'124211','v' =>'411212','w' =>'421112','x' =>'421211','y' =>'212141','z' =>'214121','{' =>'412121','|' =>'111143','}' =>'111341','~' =>'131141','DEL'=>'114113',
    ];
    private $startB = '211214'; // start code B
    private $stop   = '2331112';

    public function renderPng(string $text, int $height = 50, int $scale = 2)
    {
        if (!function_exists('imagecreatetruecolor')) {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'GD not available';
            return;
        }
        $patterns = $this->encode($text);
        $width = array_sum($patterns) * $scale;
        $img = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($img, 255,255,255);
        $black = imagecolorallocate($img, 0,0,0);
        imagefilledrectangle($img, 0,0, $width, $height, $white);
        $x=0; $bar = true;
        foreach ($patterns as $w) {
            $w *= $scale;
            if ($bar) imagefilledrectangle($img, $x, 0, $x+$w-1, $height, $black);
            $x += $w; $bar = !$bar;
        }
        header('Content-Type: image/png');
        imagepng($img);
        imagedestroy($img);
    }

    private function encode(string $text): array
    {
        // checksum
        $vals = [];
        $sum = 104; // start B value
        $i = 1;
        $full = $this->startB;
        $chars = str_split($text);
        foreach ($chars as $ch) {
            $code = $this->codes[$ch] ?? $this->codes[' '];
            $val = array_search($code, $this->codes, true);
            $val = is_int($val) ? $val : 0;
            $sum += $val * $i;
            $i++;
            $full .= $code;
        }
        $checkVal = $sum % 103;
        $checkPattern = array_values(array_filter($this->codes, fn($v)=>true))[$checkVal] ?? '111111';
        $full .= $checkPattern . $this->stop;

        // convert to module widths
        $modules = [];
        for ($i=0; $i<strlen($full); $i++) {
            $modules[] = intval($full[$i]);
        }
        return $modules;
    }
}
