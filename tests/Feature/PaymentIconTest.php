<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons\Tests\Feature;

use Clntdev\BladePaymentIcons\Components\PaymentIcon;
use Clntdev\BladePaymentIcons\Format;
use Clntdev\BladePaymentIcons\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;

class PaymentIconTest extends TestCase
{
    #[Test]
    public function component_renders_svg(): void
    {
        $component = new PaymentIcon('Visa');
        $html = $this->renderComponent($component);

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('</svg>', $html);
    }

    #[Test]
    public function component_includes_width_and_height(): void
    {
        $component = new PaymentIcon('Visa', width: 100);
        $html = $this->renderComponent($component);

        $this->assertStringContainsString('width="100"', $html);
        $this->assertStringContainsString('height=', $html);
    }

    #[Test]
    public function component_maintains_aspect_ratio(): void
    {
        $component = new PaymentIcon('Visa', width: 78);

        // 78 / (780/500) = 50
        $this->assertEquals(78, $component->width);
        $this->assertEquals(50, $component->height);
    }

    #[Test]
    public function component_calculates_width_from_height(): void
    {
        $component = new PaymentIcon('Visa', height: 50);

        // 50 * (780/500) = 78
        $this->assertEquals(78, $component->width);
        $this->assertEquals(50, $component->height);
    }

    #[Test]
    public function component_uses_default_width(): void
    {
        $component = new PaymentIcon('Visa');

        $this->assertEquals(40, $component->width);
    }

    #[Test]
    public function all_formats_render(): void
    {
        collect(Format::cases())
            ->each(function (Format $format): void {
                $component = new PaymentIcon('Visa', format: $format->value);
                $html = $this->renderComponent($component);

                $this->assertStringContainsString('<svg', $html, "Format {$format->value} should render SVG");
            });
    }

    #[Test]
    public function alias_resolution_amex(): void
    {
        $component = new PaymentIcon('Amex');

        $this->assertEquals('AmericanExpress', $component->resolvedType);
    }

    #[Test]
    public function alias_resolution_cvv(): void
    {
        $component = new PaymentIcon('Cvv');

        $this->assertEquals('Generic', $component->resolvedType);
        $this->assertEquals('code', $component->resolvedVariant);
    }

    #[Test]
    public function variant_alias_hiper(): void
    {
        $component = new PaymentIcon('Hiper');

        $this->assertEquals('Hipercard', $component->resolvedType);
        $this->assertEquals('hiper', $component->resolvedVariant);
    }

    #[Test]
    public function explicit_variant_prop(): void
    {
        $component = new PaymentIcon('Hipercard', variant: 'hiper');

        $this->assertEquals('Hipercard', $component->resolvedType);
        $this->assertEquals('hiper', $component->resolvedVariant);
    }

    #[Test]
    public function code_variant_renders(): void
    {
        $component = new PaymentIcon('Code');
        $html = $this->renderComponent($component);

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function fallback_to_generic(): void
    {
        $component = new PaymentIcon('NonExistentCard');
        $html = $this->renderComponent($component);

        // Should still render something (generic fallback)
        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function component_includes_viewbox(): void
    {
        $component = new PaymentIcon('Visa');
        $html = $this->renderComponent($component);

        $this->assertStringContainsString('viewBox="0 0 780 500"', $html);
    }

    #[Test]
    public function component_includes_aria_label(): void
    {
        $component = new PaymentIcon('Visa');
        $html = $this->renderComponent($component);

        $this->assertStringContainsString('aria-label="Visa payment icon"', $html);
    }

    #[Test]
    public function blade_component_renders(): void
    {
        $html = Blade::render('<x-payment-icon type="Visa" />');

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function blade_component_with_format(): void
    {
        $html = Blade::render('<x-payment-icon type="Mastercard" format="flatRounded" />');

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function blade_component_with_width(): void
    {
        $html = Blade::render('<x-payment-icon type="Visa" width="60" />');

        $this->assertStringContainsString('width="60"', $html);
    }

    #[Test]
    public function blade_component_with_class(): void
    {
        $html = Blade::render('<x-payment-icon type="Visa" class="payment-icon" />');

        $this->assertStringContainsString('class="payment-icon"', $html);
    }

    #[Test]
    public function all_card_types_render(): void
    {
        collect([
            'Visa',
            'Mastercard',
            'AmericanExpress',
            'Discover',
            'DinersClub',
            'JCB',
            'Maestro',
            'Elo',
            'Mir',
            'UnionPay',
            'Hipercard',
            'Alipay',
            'PayPal',
            'Swish',
            'Generic',
        ])->each(function (string $type): void {
            $component = new PaymentIcon($type);
            $html = $this->renderComponent($component);

            $this->assertStringContainsString('<svg', $html, "Card type {$type} should render SVG");
        });
    }

    protected function renderComponent(PaymentIcon $component): string
    {
        return $component->render()->with($component->data())->render();
    }
}
