<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons\Components;

use Clntdev\BladePaymentIcons\CardMetadata;
use Clntdev\BladePaymentIcons\Format;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PaymentIcon extends Component
{
    public string $type;

    public string $format;

    public ?string $variant;

    public int|float $width;

    public int|float $height;

    public string $svgPath;

    public string $resolvedType;

    public ?string $resolvedVariant;

    protected const ASPECT_RATIO = 780 / 500;

    protected const VIEW_BOX = '0 0 780 500';

    public function __construct(
        string $type,
        ?string $format = null,
        ?string $variant = null,
        int|float|null $width = null,
        int|float|null $height = null
    ) {
        $this->type = $type;
        $configFormat = config('blade-payment-icons.default_format', Format::Flat);
        $defaultFormat = $configFormat instanceof Format ? $configFormat->value : $configFormat;
        $this->format = $format ?? $defaultFormat;
        $this->variant = $variant;

        $resolved = app(CardMetadata::class)->resolveAlias($type);

        $this->resolvedType = $resolved['type'];
        $this->resolvedVariant = $variant ?? $resolved['variant'];

        [$this->width, $this->height] = $this->calculateDimensions($width, $height);

        $this->svgPath = $this->resolveSvgPath();
    }

    /**
     * @return array{0: int|float, 1: int|float}
     */
    protected function calculateDimensions(int|float|null $width, int|float|null $height): array
    {
        if ($width !== null) {
            return [$width, $height ?? $width / self::ASPECT_RATIO];
        }

        if ($height !== null) {
            return [$height * self::ASPECT_RATIO, $height];
        }

        $defaultWidth = config('blade-payment-icons.default_width', 40);

        return [$defaultWidth, $defaultWidth / self::ASPECT_RATIO];
    }

    protected function resolveSvgPath(): string
    {
        $format = Format::tryFrom($this->format) ?? Format::Flat;
        $formatDir = $format->directory();

        $typeForFile = strtolower($this->resolvedType);

        $filename = $this->resolvedVariant !== null ? $typeForFile . '-' . $this->resolvedVariant : $typeForFile;

        $basePath = $this->getSvgBasePath();
        $path = $basePath.'/'.$formatDir.'/'.$filename.'.svg';

        if (file_exists($path)) {
            return $path;
        }

        if ($this->resolvedVariant !== null) {
            $path = $basePath . '/' . $formatDir . '/' . $this->resolvedVariant . '.svg';

            if (file_exists($path)) {
                return $path;
            }
        }

        $genericPath = $basePath . '/' . $formatDir . '/' . Format::FALLBACK_ICON . '.svg';

        if (file_exists($genericPath)) {
            return $genericPath;
        }

        return $basePath . '/' . Format::FlatRounded->directory() . '/' . Format::FALLBACK_ICON . '.svg';
    }

    protected function getSvgBasePath(): string
    {
        $publishedPath = resource_path('vendor/blade-payment-icons/svg');

        if (is_dir($publishedPath)) {
            return $publishedPath;
        }

        return dirname(__DIR__, 2) . '/resources/svg';
    }

    public function getSvgContent(): string
    {
        if (file_exists($this->svgPath) === false) {
            return '';
        }

        $content = file_get_contents($this->svgPath);

        if ($content === false) {
            return '';
        }

        return $content;
    }

    public function getViewBox(): string
    {
        return self::VIEW_BOX;
    }

    public function render(): View
    {
        return view('blade-payment-icons::components.payment-icon');
    }
}
