<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons;

use Illuminate\Support\Str;

enum Format: string
{
    case Flat = 'flat';
    case FlatRounded = 'flatRounded';
    case Logo = 'logo';
    case LogoBorder = 'logoBorder';
    case Mono = 'mono';
    case MonoOutline = 'monoOutline';

    public const FALLBACK_ICON = 'generic';

    public function directory(): string
    {
        return Str::kebab($this->value);
    }
}
