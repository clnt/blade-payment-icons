<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons\Tests\Feature;

use BladeUI\Icons\Factory;
use Clntdev\BladePaymentIcons\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;

class BladeIconsIntegrationTest extends TestCase
{
    #[Test]
    public function default_icon_set_is_registered(): void
    {
        $factory = app(Factory::class);
        $sets = $factory->all();

        $this->assertArrayHasKey('payment-icons', $sets);
        $this->assertEquals('payicon', $sets['payment-icons']['prefix']);
    }

    #[Test]
    public function all_format_sets_are_registered(): void
    {
        $factory = app(Factory::class);
        $sets = $factory->all();

        $this->assertArrayHasKey('payment-icons-flat', $sets);
        $this->assertArrayHasKey('payment-icons-flat-rounded', $sets);
        $this->assertArrayHasKey('payment-icons-logo', $sets);
        $this->assertArrayHasKey('payment-icons-logo-border', $sets);
        $this->assertArrayHasKey('payment-icons-mono', $sets);
        $this->assertArrayHasKey('payment-icons-mono-outline', $sets);
    }

    #[Test]
    public function format_sets_have_correct_prefixes(): void
    {
        $factory = app(Factory::class);
        $sets = $factory->all();

        $this->assertEquals('payflat', $sets['payment-icons-flat']['prefix']);
        $this->assertEquals('payflatrounded', $sets['payment-icons-flat-rounded']['prefix']);
        $this->assertEquals('paylogo', $sets['payment-icons-logo']['prefix']);
        $this->assertEquals('paylogoborder', $sets['payment-icons-logo-border']['prefix']);
        $this->assertEquals('paymono', $sets['payment-icons-mono']['prefix']);
        $this->assertEquals('paymonooutline', $sets['payment-icons-mono-outline']['prefix']);
    }

    #[Test]
    public function svg_helper_renders_default_format(): void
    {
        $svg = svg('payicon-visa')->toHtml();

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    #[Test]
    public function svg_helper_renders_specific_format(): void
    {
        $svg = svg('paymono-visa')->toHtml();

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    #[Test]
    public function blade_component_renders_default_format(): void
    {
        $html = Blade::render('<x-payicon-visa />');

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function blade_component_renders_specific_format(): void
    {
        $html = Blade::render('<x-paylogo-visa />');

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function blade_directive_renders(): void
    {
        $html = Blade::render("@svg('payicon-visa')");

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function blade_component_with_class_attribute(): void
    {
        $html = Blade::render('<x-payicon-visa class="w-10 h-6" />');

        $this->assertStringContainsString('w-10 h-6', $html);
    }

    #[Test]
    public function all_card_types_accessible_via_default_prefix(): void
    {
        collect([
            'visa',
            'mastercard',
            'americanexpress',
            'discover',
            'dinersclub',
            'jcb',
            'maestro',
            'elo',
            'mir',
            'unionpay',
            'hipercard',
            'alipay',
            'paypal',
            'swish',
            'generic',
        ])->each(function (string $type): void {
            $svg = svg('payicon-' . $type)->toHtml();

            $this->assertStringContainsString('<svg', $svg, "Icon {$type} should render via blade-icons");
        });
    }

    #[Test]
    public function all_format_prefixes_render_visa(): void
    {
        collect([
            'payflat',
            'payflatrounded',
            'paylogo',
            'paylogoborder',
            'paymono',
            'paymonooutline',
        ])->each(function (string $prefix): void {
            $svg = svg($prefix . '-visa')->toHtml();

            $this->assertStringContainsString('<svg', $svg, "Prefix {$prefix} should render visa icon");
        });
    }

    #[Test]
    public function fallback_to_generic_for_unknown_icon(): void
    {
        $svg = svg('payicon-nonexistent')->toHtml();

        $this->assertStringContainsString('<svg', $svg);
    }

    #[Test]
    public function existing_payment_icon_component_still_works(): void
    {
        $html = Blade::render('<x-payment-icon type="Visa" />');

        $this->assertStringContainsString('<svg', $html);
    }

    #[Test]
    public function existing_payment_icon_with_format_still_works(): void
    {
        $html = Blade::render('<x-payment-icon type="Mastercard" format="mono" />');

        $this->assertStringContainsString('<svg', $html);
    }
}
