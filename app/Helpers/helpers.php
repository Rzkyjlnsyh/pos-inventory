<?php

if (!function_exists('qr_code')) {
    function qr_code($url, $size = 120)
    {
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($url);
    }
}