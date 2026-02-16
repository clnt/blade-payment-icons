@php
$svgContent = $getSvgContent();
$viewBox = $getViewBox();

$innerContent = '';

if (preg_match('/<svg[^>]*>(.*)<\/svg>/s', $svgContent, $matches)) {
    $innerContent = trim($matches[1]);
}
@endphp
<svg {{ $attributes->merge([
    'width' => $width,
    'height' => $height,
    'viewBox' => $viewBox,
    'xmlns' => 'http://www.w3.org/2000/svg',
    'role' => 'img',
    'aria-label' => $resolvedType . ' payment icon',
]) }}>{!! $innerContent !!}</svg>
