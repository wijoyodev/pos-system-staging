<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Harga</title>
    <style>
        @page {
            margin: 7mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #fff;
        }

        .label-grid {
            display: grid;
            gap: 6mm;
            justify-items: center;
        }
        .label-grid.cols-1 { grid-template-columns: 1fr; }
        .label-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .label-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }

        .label {
            border: 1.5px solid #e2e8f0;
            border-radius: 3mm;
            padding: 4mm 3.5mm;
            display: flex;
            flex-direction: column;
            background: #fff;
            break-inside: avoid;
            page-break-inside: avoid;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            position: relative;
            width: 100%;
        }

        .label-grid.cols-1 .label {
            max-width: 340px;
            padding: 5mm 4mm;
        }
        .label-grid.cols-2 .label {
            max-width: 300px;
        }

        /* ── Header: Store name ── */
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5mm;
            padding-bottom: 1.5mm;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .store-name {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
        }
        .sku-text {
            font-size: 6px;
            font-family: 'Courier New', monospace;
            color: #94a3b8;
        }

        /* ── Product name ── */
        .product-name {
            font-size: 14px;
            font-weight: 800;
            line-height: 1.25;
            color: #0f172a;
            margin-bottom: 2.5mm;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }
        .cols-1 .product-name {
            font-size: 18px;
        }

        /* ── Price area ── */
        .price-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: auto;
            padding: 2mm 0;
        }
        .price {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -0.5px;
            line-height: 1;
            color: #0f172a;
        }
        .price .currency {
            font-size: 16px;
            font-weight: 700;
            color: #64748b;
            margin-right: 1px;
        }
        .price.promo {
            color: #dc2626;
        }
        .price.promo .currency {
            color: #dc2626;
            opacity: 0.7;
        }
        .cols-1 .price {
            font-size: 36px;
        }
        .cols-1 .price .currency {
            font-size: 20px;
        }

        .price-row {
            display: flex;
            align-items: center;
            gap: 3mm;
            margin-top: 2mm;
        }
        .price-original {
            font-size: 13px;
            text-decoration: line-through;
            color: #94a3b8;
        }
        .cols-1 .price-original {
            font-size: 16px;
        }
        .promo-badge {
            font-size: 8px;
            font-weight: 800;
            background: #dc2626;
            color: #fff;
            padding: 1.5px 5px;
            border-radius: 2px;
            display: inline-block;
            letter-spacing: 0.2px;
        }

        /* ── Barcode area ── */
        .barcode-wrap {
            margin-top: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 2mm;
            border-top: 1px solid #f1f5f9;
        }
        .barcode-wrap svg {
            width: 100%;
            max-width: 150px;
            height: 24px;
        }
        .cols-1 .barcode-wrap svg {
            max-width: 200px;
            height: 30px;
        }
        .barcode-number {
            font-size: 6px;
            font-family: 'Courier New', monospace;
            color: #94a3b8;
            letter-spacing: 0.5px;
            margin-top: 0.5mm;
        }

        /* ── Promo ribbon (corner) ── */
        .promo-ribbon {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 14mm 14mm 0;
            border-color: transparent #dc2626 transparent transparent;
        }
        .promo-ribbon-text {
            position: absolute;
            top: 1.5mm;
            right: 1.5mm;
            font-size: 6px;
            font-weight: 900;
            color: #fff;
            transform: rotate(45deg);
            letter-spacing: 0.3px;
            z-index: 1;
        }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #f8fafc; padding: 20px; margin-bottom: 20px; text-align: center; border-radius: 12px; border: 2px dashed #e2e8f0;">
        <button onclick="window.print()"
            style="background: #003f87; color: #fff; border: none; padding: 14px 40px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,63,135,0.25);">
            🖨️ Cetak Label
        </button>
        <p style="margin-top: 10px; font-size: 13px; color: #64748b;">
            Gunakan kertas stiker A4 (3 kolom) atau ukuran 50×30mm
        </p>
    </div>

    @php
        $totalLabels = $products->sum(fn($p) => $copies);
        $cols = $totalLabels <= 2 ? 1 : ($totalLabels <= 4 ? 2 : 3);
    @endphp

    <div class="label-grid cols-{{ $cols }}">
        @foreach($products as $product)
            @for($c = 0; $c < $copies; $c++)
            <div class="label">
                @if($product->isPromoActive())
                    <div class="promo-ribbon"></div>
                    <div class="promo-ribbon-text">{{ $product->getDiscountPercentage() }}%</div>
                @endif

                <div class="label-header">
                    <span class="store-name">{{ $storeName }}</span>
                    @if($product->sku)
                        <span class="sku-text">{{ $product->sku }}</span>
                    @endif
                </div>

                <div class="product-name">{{ $product->name }}</div>

                <div class="price-area">
                    @if($product->isPromoActive())
                        <div class="price promo">
                            <span class="currency">Rp</span>{{ number_format($product->getCurrentPrice(), 0, ',', '.') }}
                        </div>
                        <div class="price-row">
                            <span class="price-original">Rp{{ number_format($product->selling_price, 0, ',', '.') }}</span>
                            <span class="promo-badge">-{{ $product->getDiscountPercentage() }}%</span>
                        </div>
                    @else
                        <div class="price">
                            <span class="currency">Rp</span>{{ number_format($product->selling_price, 0, ',', '.') }}
                        </div>
                    @endif
                </div>

                @if($product->barcode)
                    <div class="barcode-wrap">
                        <svg viewBox="0 0 300 60" xmlns="http://www.w3.org/2000/svg">
                            @php
                                $barcode = $product->barcode;
                                $chars = str_split($barcode);
                                $x = 0;
                                $totalWidth = 300;
                                $usableWidth = $totalWidth - 30;
                                $barCount = count($chars) * 7 + 6;
                                $barW = max(1, round($usableWidth / $barCount));

                                // Start guard
                                echo '<rect x="'.$x.'" y="0" width="'.($barW*2).'" height="46" fill="#000"/>'; $x += $barW*2;
                                echo '<rect x="'.$x.'" y="0" width="'.$barW.'" height="46" fill="#fff"/>'; $x += $barW;
                                echo '<rect x="'.$x.'" y="0" width="'.$barW.'" height="46" fill="#000"/>'; $x += $barW;
                                echo '<rect x="'.$x.'" y="0" width="'.$barW.'" height="46" fill="#fff"/>'; $x += $barW;

                                foreach ($chars as $ch) {
                                    $n = intval($ch);
                                    $pattern = [
                                        '0001101','0011001','0010011','0111101','0100011',
                                        '0110001','0101111','0111011','0110111','0001011'
                                    ];
                                    $p = $pattern[$n] ?? '0001101';
                                    for ($i = 0; $i < 7; $i++) {
                                        if ($p[$i] === '1') {
                                            echo '<rect x="'.round($x).'" y="0" width="'.$barW.'" height="46" fill="#000"/>';
                                        }
                                        $x += $barW;
                                    }
                                }

                                // End guard
                                echo '<rect x="'.round($x).'" y="0" width="'.$barW.'" height="46" fill="#fff"/>'; $x += $barW;
                                echo '<rect x="'.round($x).'" y="0" width="'.$barW.'" height="46" fill="#000"/>'; $x += $barW;
                                echo '<rect x="'.round($x).'" y="0" width="'.$barW.'" height="46" fill="#fff"/>'; $x += $barW;
                                echo '<rect x="'.round($x).'" y="0" width="'.($barW*2).'" height="46" fill="#000"/>';
                            @endphp
                        </svg>
                        <span class="barcode-number">{{ $barcode }}</span>
                    </div>
                @endif
            </div>
            @endfor
        @endforeach
    </div>

</body>
</html>
