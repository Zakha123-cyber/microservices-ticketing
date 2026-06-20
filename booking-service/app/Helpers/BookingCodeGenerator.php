<?php

namespace App\Helpers;

class BookingCodeGenerator
{
    public static function generate(): string
    {
        return 'BKG-' . time() . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
