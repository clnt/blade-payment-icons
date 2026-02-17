# Blade Payment Icons

A package to easily make use of Payment Icons in your Laravel Blade views.

## Installation

```bash
composer require clntdev/blade-payment-icons
```

The service provider is auto-discovered. To publish the config:

```bash
php artisan vendor:publish --tag=blade-payment-icons-config
```

Optionally publish SVGs or views for customization:

```bash
php artisan vendor:publish --tag=blade-payment-icons-svg
php artisan vendor:publish --tag=blade-payment-icons-views
```

## Usage

This package provides two ways to use icons:

1. **Blade Icons syntax** - standard `blade-ui-kit/blade-icons` integration for simple icon rendering
2. **Payment Icon component** - enhanced component with alias resolution, auto-sizing, and variant support

### Blade Icons (Recommended for simple usage)

Icons are available as Blade components, directives, and helpers via the [blade-icons](https://github.com/blade-ui-kit/blade-icons) ecosystem.

```blade
{{-- Component --}}
<x-payicon-visa />
<x-payicon-mastercard class="w-10 h-6" />

{{-- Directive --}}
@svg('payicon-visa', 'w-10 h-6')

{{-- Helper --}}
{{ svg('payicon-visa') }}
```

The `payicon` prefix uses the configured default format (flat). To use a specific format, use the format-specific prefix:

| Format | Prefix | Example |
|--------|--------|---------|
| Default (config) | `payicon` | `<x-payicon-visa />` |
| Flat | `payflat` | `<x-payflat-visa />` |
| Flat Rounded | `payflatrounded` | `<x-payflatrounded-visa />` |
| Logo | `paylogo` | `<x-paylogo-visa />` |
| Logo Border | `paylogoborder` | `<x-paylogoborder-visa />` |
| Mono | `paymono` | `<x-paymono-visa />` |
| Mono Outline | `paymonooutline` | `<x-paymonooutline-visa />` |

> **Note:** Blade Icons syntax uses SVG filenames directly (e.g., `americanexpress`, not `amex`). For alias resolution and variant support, use the Payment Icon component below.

### Payment Icon Component

The `<x-payment-icon>` component provides additional features including alias resolution, automatic aspect ratio sizing, and variant support.

```blade
<x-payment-icon type="Visa" />
<x-payment-icon type="Mastercard" />
<x-payment-icon type="AmericanExpress" />
```

### Formats

Six visual formats are available: `flat`, `flatRounded`, `logo`, `logoBorder`, `mono`, `monoOutline`.

```blade
<x-payment-icon type="Visa" format="logo" />
<x-payment-icon type="Visa" format="mono" />
<x-payment-icon type="Visa" format="flatRounded" />
```

### Sizing

Width defaults to `40px`. Height is auto-calculated to maintain the 780:500 aspect ratio. You can set either dimension - the other adjusts automatically.

```blade
<x-payment-icon type="Visa" :width="60" />
<x-payment-icon type="Visa" :height="30" />
```

### Aliases

Card types can be referenced by their aliases. These are case-insensitive and ignore dashes/underscores.

```blade
<x-payment-icon type="Amex" />          {{-- Resolves to AmericanExpress --}}
<x-payment-icon type="GenericCard" />    {{-- Resolves to Generic --}}
```

### Variants

Some cards have variants. You can reference them directly by alias or use the `variant` prop.

```blade
{{-- Via alias (resolves to Generic type with "code" variant) --}}
<x-payment-icon type="Cvv" />
<x-payment-icon type="CvvFront" />

{{-- Via explicit variant prop --}}
<x-payment-icon type="Hipercard" variant="hiper" />
<x-payment-icon type="Generic" variant="code-front" />
```

### Attribute Forwarding

Standard HTML/Blade attributes are forwarded to the rendered `<svg>` element.

```blade
<x-payment-icon type="Visa" class="inline-block shadow-sm" />
```

The SVG includes `role="img"` and an `aria-label` by default for accessibility.

## Available Icons

| Type | Display Name | Aliases | Variants |
|------|-------------|---------|----------|
| Visa | Visa | - | - |
| Mastercard | Mastercard | - | - |
| AmericanExpress | American Express | Amex | - |
| Discover | Discover | - | - |
| DinersClub | Diners Club | - | - |
| JCB | JCB | - | - |
| Maestro | Maestro | - | - |
| Elo | Elo | - | - |
| Mir | Mir | - | - |
| UnionPay | UnionPay | - | - |
| Hipercard | Hipercard | - | Hiper |
| Alipay | Alipay | - | - |
| PayPal | PayPal | - | - |
| Swish | Swish | - | - |
| Generic | Generic Card (Front) | GenericCard, GenericCardFront | Code (back/CVV), CodeFront (front CVV) |

The **Generic** card's `Code` variant has additional aliases: `GenericBack`, `GenericCardBack`, `CvvBack`, `CvcBack`, `Cvv`, `Cvc`. The `CodeFront` variant has: `GenericFrontCode`, `CvvFront`, `CvcFront`.

## Icon Formats

| Format | Directory | Description |
|--------|-----------|-------------|
| `flat` | `flat/` | Flat design |
| `flatRounded` | `flat-rounded/` | Flat with rounded corners |
| `logo` | `logo/` | Centered logo only |
| `logoBorder` | `logo-border/` | Logo with border |
| `mono` | `mono/` | Monochrome |
| `monoOutline` | `mono-outline/` | Monochrome outline |

## Configuration

After publishing the config (`config/blade-payment-icons.php`), you can customize:

### Defaults

```php
'default_format' => 'flat',   // Default icon format
'default_width' => 40,        // Default width in pixels (height auto-calculated)
```

### Card Definitions

Each card entry in the `cards` array supports the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `type` | `string` | Canonical type name used for file resolution (e.g. `Visa`, `AmericanExpress`) |
| `displayName` | `string` | Human-readable name |
| `aliases` | `string[]` | Alternative names that resolve to this type |
| `variants` | `array` | Named sub-types with their own SVG files (see below) |
| `testNumbers` | `string[]` | Sample card numbers for testing |
| `patterns` | `array` | Regex patterns for card detection - `full` (complete numbers) and `prefix` (partial input) |
| `lengthRange` | `array\|null` | `min`/`max` digit count, or `null` for non-card payment methods |
| `formatPattern` | `array\|null` | Digit grouping for display formatting (e.g. `[4, 4, 4, 4]` or length-keyed like `[14 => [4, 6, 4], 16 => [4, 4, 4, 4]]`) |
| `issuingCountries` | `string[]\|null` | ISO country codes or `GLOBAL` |

#### Variants

Variants are defined as a keyed array within a card definition:

```php
'variants' => [
    'Hiper' => [
        'slug' => 'hiper',           // Used for SVG filename resolution
        'displayName' => 'Hiper',
        'aliases' => [],              // Optional additional aliases
    ],
],
```

The `slug` maps to an SVG file. For example, Hipercard's `Hiper` variant resolves to `hiper.svg` in the chosen format directory.

#### Pattern Matching

Cards are detected by regex patterns. The `prefix` pattern matches partial input (useful for real-time detection as users type), while `full` patterns validate complete card numbers:

```php
'patterns' => [
    'full' => ['^4[0-9]{12}(?:[0-9]{3})?$'],  // Complete Visa number
    'prefix' => '^4',                            // Starts with 4
],
```

Cards with more specific patterns are checked first to avoid false matches (e.g. Elo's specific BIN ranges are checked before Mastercard's broader 5xxx range).

## Card Utilities

The package registers `CardUtilities` and `CardMetadata` as singletons. Resolve them from the container to detect, validate, format, and mask card numbers.

```php
use Clntdev\BladePaymentIcons\CardUtilities;

$utilities = app(CardUtilities::class);

// Detect card type from number
$utilities->getCardType('4242424242424242');          // 'Visa'
$utilities->getCardType('378282246310005');            // 'AmericanExpress'
$utilities->getCardType('4242');                       // 'Visa' (partial match)

// Validate with Luhn algorithm
CardUtilities::validateCardNumber('4242424242424242'); // true

// Format with appropriate spacing
$utilities->formatCardNumber('4242424242424242');      // '4242 4242 4242 4242'
$utilities->formatCardNumber('378282246310005');       // '3782 822463 10005'

// Mask showing last 4 digits
$utilities->maskCardNumber('4242424242424242');        // '**** **** **** 4242'

// Validate number matches a specific type
$utilities->validateCardForType('4242424242424242', 'Visa');       // true
$utilities->validateCardForType('4242424242424242', 'Mastercard'); // false

// Filter cards by issuing country
$utilities->getCardsByCountry('BR');                              // ['Elo', 'Hipercard', 'Visa', ...]
$utilities->getCardsByCountry('BR', ['includeGlobal' => false]); // ['Elo', 'Hipercard']
```

## Credits

SVG icons and card utilities sourced from [react-svg-credit-card-payment-icons](https://github.com/marcovoliveira/react-svg-credit-card-payment-icons)

