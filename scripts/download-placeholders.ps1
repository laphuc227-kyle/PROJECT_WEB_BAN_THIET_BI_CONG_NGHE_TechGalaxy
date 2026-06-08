$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$headers = @{ 'User-Agent' = 'Mozilla/5.0 (TechGalaxy setup)' }

function Save-Url($url, $path) {
    New-Item -ItemType Directory -Force -Path (Split-Path $path) | Out-Null
    Invoke-WebRequest -Uri $url -OutFile $path -Headers $headers -UseBasicParsing
    Write-Host "OK $path"
}

@(
    'assets\images\hero',
    'assets\images\products',
    'assets\images\blog',
    'assets\images\brands',
    'assets\images\promo',
    'assets\images\payments',
    'public\uploads\avatars'
) | ForEach-Object { New-Item -ItemType Directory -Force -Path (Join-Path $root $_) | Out-Null }

Save-Url 'https://picsum.photos/seed/tg-hero-phone/520/520' (Join-Path $root 'assets\images\hero\iphone-hero.png')
Save-Url 'https://picsum.photos/seed/tg-hero-laptop/520/520' (Join-Path $root 'assets\images\hero\laptop-hero.png')
Save-Url 'https://picsum.photos/seed/tg-promo/480/480' (Join-Path $root 'assets\images\promo\iphone-promo.png')
Save-Url 'https://picsum.photos/seed/tg-og/1200/630' (Join-Path $root 'assets\images\og-cover.jpg')
Save-Url 'https://picsum.photos/seed/tg-favicon/64/64' (Join-Path $root 'assets\images\favicon.png')
Save-Url 'https://picsum.photos/seed/tg-avatar/80/80' (Join-Path $root 'public\uploads\avatars\default.png')

1..8 | ForEach-Object {
    Save-Url "https://picsum.photos/seed/tg-product-$_/400/400" (Join-Path $root "assets\images\products\product-$_.jpg")
}
1..3 | ForEach-Object {
    Save-Url "https://picsum.photos/seed/tg-blog-$_/640/400" (Join-Path $root "assets\images\blog\blog-$_.jpg")
}

$brands = @('apple','samsung','sony','lg','asus','dell','xiaomi','logitech')
foreach ($b in $brands) {
    $label = (Get-Culture).TextInfo.ToTitleCase($b)
    $svg = @"
<svg xmlns="http://www.w3.org/2000/svg" width="120" height="40" viewBox="0 0 120 40">
  <text x="60" y="26" text-anchor="middle" font-family="Inter,Arial,sans-serif" font-size="14" font-weight="700" fill="#64748B">$label</text>
</svg>
"@
    Set-Content -Path (Join-Path $root "assets\images\brands\$b.svg") -Value $svg -Encoding UTF8
}

@('visa','mastercard','momo','vnpay') | ForEach-Object {
    $svg = @"
<svg xmlns="http://www.w3.org/2000/svg" width="48" height="24" viewBox="0 0 48 24">
  <rect width="48" height="24" rx="4" fill="#E2E8F0"/>
  <text x="24" y="15" text-anchor="middle" font-family="Inter,Arial,sans-serif" font-size="8" font-weight="600" fill="#64748B">$_</text>
</svg>
"@
    Set-Content -Path (Join-Path $root "assets\images\payments\$_.svg") -Value $svg -Encoding UTF8
}

Write-Host 'Done.'
