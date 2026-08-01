<?php

if (! function_exists('rp')) {
    /** Format integer rupiah: 68000 -> "Rp 68.000". */
    function rp($n): string
    {
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    }
}
