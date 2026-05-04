<?php

namespace App\Enums;

class Bank
{
    // Bank codes => labels (short codes used in dropdown values)
    const BANKS = [
        'dk'  => 'Druk (DK)',
        'bnd' => 'Bhutan National Bank (BND)',
        'bob' => 'Bank of Bhutan (BOB)',
        'bdl' => 'Bhutan Development Bank (BDL)',
        'other' => 'Other Bank',
    ];

    public static function getList(): array
    {
        return self::BANKS;
    }

    public static function getLabel(?string $code): ?string
    {
        if (empty($code)) {
            return null;
        }

        return self::BANKS[$code] ?? $code;
    }
}
