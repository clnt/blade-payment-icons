<?php

declare(strict_types=1);

use Clntdev\BladePaymentIcons\Format;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Icon Format
    |--------------------------------------------------------------------------
    |
    | The default format to use when rendering payment icons.
    | Available formats: flat, flatRounded, logo, logoBorder, mono, monoOutline
    |
    */
    'default_format' => Format::Flat,

    /*
    |--------------------------------------------------------------------------
    | Default Icon Width
    |--------------------------------------------------------------------------
    |
    | The default width in pixels for payment icons.
    | Height is automatically calculated to maintain the 780:500 aspect ratio.
    |
    */
    'default_width' => 40,

    /*
    |--------------------------------------------------------------------------
    | Blade Icons Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used when accessing icons via the blade-icons ecosystem.
    | This applies to the default format set. Icons are accessible as:
    | <x-{prefix}-visa />, @svg('{prefix}-visa'), {{ svg('{prefix}-visa') }}
    |
    */
    'prefix' => 'payicon',

    /*
    |--------------------------------------------------------------------------
    | Blade Icons Fallback
    |--------------------------------------------------------------------------
    |
    | The fallback icon to use when a requested icon is not found.
    |
    */
    'fallback' => Format::FALLBACK_ICON,

    /*
    |--------------------------------------------------------------------------
    | Blade Icons Format Sets
    |--------------------------------------------------------------------------
    |
    | Each format is registered as a separate blade-icons set with its own
    | prefix. Set enabled to false to disable blade-icons integration entirely.
    |
    */
    'blade_icons' => [
        'enabled' => true,
        'sets' => [
            Format::Flat->directory() => ['prefix' => 'payflat'],
            Format::FlatRounded->directory() => ['prefix' => 'payflatrounded'],
            Format::Logo->directory() => ['prefix' => 'paylogo'],
            Format::LogoBorder->directory() => ['prefix' => 'paylogoborder'],
            Format::Mono->directory() => ['prefix' => 'paymono'],
            Format::MonoOutline->directory() => ['prefix' => 'paymonooutline'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Card Definitions
    |--------------------------------------------------------------------------
    |
    | Card metadata definitions including patterns for detection, aliases,
    | variants, and formatting rules.
    |
    */
    'cards' => [
        [
            'type' => 'Visa',
            'displayName' => 'Visa',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '4242424242424242',
                '4000056655665556',
                '4012888888881881',
            ],
            'patterns' => [
                'full' => ['^4[0-9]{12}(?:[0-9]{3})?$'],
                'prefix' => '^4',
            ],
            'lengthRange' => ['min' => 13, 'max' => 19],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['GLOBAL'],
        ],
        [
            'type' => 'Mastercard',
            'displayName' => 'Mastercard',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '5555555555554444',
                '2223003122003222',
                '5200828282828210',
                '5105105105105100',
            ],
            'patterns' => [
                'full' => [
                    '^5[1-5][0-9]{14}$',
                    '^2(?:2(?:2[1-9]|[3-9][0-9])|[3-6][0-9][0-9]|7(?:[01][0-9]|20))[0-9]{12}$',
                ],
                'prefix' => '^(5[1-5]|2(22[1-9]|2[3-9]|[3-6]|7[01]|720))',
            ],
            'lengthRange' => ['min' => 16, 'max' => 16],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['GLOBAL'],
        ],
        [
            'type' => 'AmericanExpress',
            'displayName' => 'American Express',
            'aliases' => ['Amex'],
            'variants' => [],
            'testNumbers' => [
                '378282246310005',
                '371449635398431',
            ],
            'patterns' => [
                'full' => ['^3[47][0-9]{13}$'],
                'prefix' => '^3[47]',
            ],
            'lengthRange' => ['min' => 15, 'max' => 15],
            'formatPattern' => [4, 6, 5],
            'issuingCountries' => ['US'],
        ],
        [
            'type' => 'Discover',
            'displayName' => 'Discover',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '6011111111111117',
                '6011000990139424',
                '6011981111111113',
            ],
            'patterns' => [
                'full' => ['^6(?:011|5[0-9]{2})[0-9]{12}$'],
                'prefix' => '^6(011|5[0-9]{2}|[45])',
            ],
            'lengthRange' => ['min' => 16, 'max' => 16],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['US'],
        ],
        [
            'type' => 'DinersClub',
            'displayName' => 'Diners Club',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '3056930009020004',
                '30569309025904',
                '38520000023237',
                '36227206271667',
            ],
            'patterns' => [
                'full' => [
                    '^3[0689][0-9]{12}$',
                    '^30[0-5][0-9]{11}$',
                    '^3[0689][0-9]{14}$',
                ],
                'prefix' => '^3(0[0-5]|6|8)',
            ],
            'lengthRange' => ['min' => 14, 'max' => 16],
            'formatPattern' => [
                14 => [4, 6, 4],
                16 => [4, 4, 4, 4],
            ],
            'issuingCountries' => ['US'],
        ],
        [
            'type' => 'JCB',
            'displayName' => 'JCB',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '3566002020360505',
            ],
            'patterns' => [
                'full' => ['^(?:2131|1800|35[0-9]{3})[0-9]{11}$'],
                'prefix' => '^(2131|1800|35)',
            ],
            'lengthRange' => ['min' => 15, 'max' => 16],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['JP'],
        ],
        [
            'type' => 'Maestro',
            'displayName' => 'Maestro',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '6771798021000008',
                '6771798021000016',
            ],
            'patterns' => [
                'full' => ['^(?:5[0678][0-9]{2}|6304|6390|67[0-9]{2})[0-9]{8,15}$'],
                'prefix' => '^(5[0678]|6304|6390|67)',
            ],
            'lengthRange' => ['min' => 12, 'max' => 19],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['GLOBAL'],
        ],
        [
            'type' => 'Elo',
            'displayName' => 'Elo',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '5066991111111118',
            ],
            'patterns' => [
                'full' => ['^(?:4011(78|79)|43(1274|8935)|45(1416|7393|763(1|2))|50(4175|6699|67[0-6][0-9]|677[0-8]|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])|627780|63(6297|6368|6369))[0-9]*$'],
                'prefix' => '^(4011(78|79)|43(1274|8935)|45(1416|7393|763[12])|50(4175|6699|67[0-6]|677[0-8])|627780|636(297|368|369))',
            ],
            'lengthRange' => ['min' => 16, 'max' => 16],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['BR'],
        ],
        [
            'type' => 'Mir',
            'displayName' => 'Mir',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '2200000000000004',
                '2200000000000012',
                '2202207558945880',
            ],
            'patterns' => [
                'full' => ['^220[0-4][0-9]{12}$'],
                'prefix' => '^220[0-4]',
            ],
            'lengthRange' => ['min' => 16, 'max' => 16],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['RU'],
        ],
        [
            'type' => 'UnionPay',
            'displayName' => 'UnionPay',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [
                '6200000000000005',
                '6200000000000047',
                '6205500000000000004',
            ],
            'patterns' => [
                'full' => ['^62[0-9]{14,17}$'],
                'prefix' => '^62',
            ],
            'lengthRange' => ['min' => 16, 'max' => 19],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['CN'],
        ],
        [
            'type' => 'Hipercard',
            'displayName' => 'Hipercard',
            'aliases' => [],
            'variants' => [
                'Hiper' => [
                    'slug' => 'hiper',
                    'displayName' => 'Hiper',
                ],
            ],
            'testNumbers' => [
                '6062825624254001',
                '6062823936268330',
                '6062828888666688',
            ],
            'patterns' => [
                'full' => ['^(606282\d{10}(\d{3})?)|(3841\d{15})$'],
                'prefix' => '^(606282|3841)',
            ],
            'lengthRange' => ['min' => 16, 'max' => 16],
            'formatPattern' => [4, 4, 4, 4],
            'issuingCountries' => ['BR'],
        ],
        [
            'type' => 'Alipay',
            'displayName' => 'Alipay',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [],
            'patterns' => [
                'full' => [],
                'prefix' => null,
            ],
            'lengthRange' => null,
            'formatPattern' => null,
            'issuingCountries' => ['GLOBAL'],
        ],
        [
            'type' => 'PayPal',
            'displayName' => 'PayPal',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [],
            'patterns' => [
                'full' => [],
                'prefix' => null,
            ],
            'lengthRange' => null,
            'formatPattern' => null,
            'issuingCountries' => ['GLOBAL'],
        ],
        [
            'type' => 'Swish',
            'displayName' => 'Swish',
            'aliases' => [],
            'variants' => [],
            'testNumbers' => [],
            'patterns' => [
                'full' => [],
                'prefix' => null,
            ],
            'lengthRange' => null,
            'formatPattern' => null,
            'issuingCountries' => ['SE'],
        ],
        [
            'type' => 'Generic',
            'displayName' => 'Generic Card (Front)',
            'aliases' => ['GenericCard', 'GenericCardFront'],
            'variants' => [
                'Code' => [
                    'slug' => 'code',
                    'displayName' => 'Generic Card (Back) - Security Code',
                    'aliases' => ['GenericBack', 'GenericCardBack', 'CvvBack', 'CvcBack', 'Cvv', 'Cvc'],
                ],
                'CodeFront' => [
                    'slug' => 'code-front',
                    'displayName' => 'Generic Card (Front) - Security Code',
                    'aliases' => ['GenericFrontCode', 'CvvFront', 'CvcFront'],
                ],
            ],
            'testNumbers' => [],
            'patterns' => [
                'full' => [],
                'prefix' => null,
            ],
            'lengthRange' => null,
            'formatPattern' => null,
            'issuingCountries' => null,
        ],
    ],
];
