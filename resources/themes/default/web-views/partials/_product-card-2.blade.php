@if(isset($product))
    @php($overallRating = getOverallRating($product->reviews))
    <style>
        .custom-flash-card {
            display: flex;
            align-items: center;
            justify-content: start;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
            transition: 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            cursor: pointer;
        }

        .custom-flash-card:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .custom-flash-card .custom-image {
            width: 320px;
            height: 220px;
            border-radius: 8px;
            object-fit: cover;
            overflow: hidden;
        }

        .custom-flash-card .custom-details {
            padding-left: 20px;
        }

        .custom-flash-card .custom-details h3 a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .custom-flash-card .custom-details h3 a:hover {
            color: #007bff;
        }

        .custom-flash-card .custom-details .flash-product-review {
            margin: 5px 0;
        }

        .custom-flash-card .custom-details i {
            color: #f7b500;
            font-size: 14px;
        }

        .custom-flash-card .custom-details .badge-style2 {
            font-size: 12px;
            margin-left: 5px;
            color: #555;
        }

        .custom-flash-card .custom-details h4 {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .custom-flash-card .custom-details del {
            color: #999;
            font-size: 0.9rem;
        }

        .custom-flash-card .custom-details .flash-product-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: #111;
        }

        .custom-flash-card .for-discount-value {
            position: absolute;
            background: #ff4747;
            color: #fff;
            font-size: 12px;
            border-radius: 4px;
            top: 10px;
            left: 10px;
            font-weight: bold;
        }

        /* Responsivlik */
        @media (max-width: 768px) {
            .custom-flash-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .custom-flash-card .custom-image {
                width: 100%;
                height: 180px;
            }
            .custom-flash-card .custom-details {
                padding: 10px 0 0 0;
            }
        }
    </style>

    <div class="custom-flash-card get-view-by-onclick" data-link="{{ route('product', $product->slug) }}">
        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
            <span class="for-discount-value">
                -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
            </span>
        @endif

        <img class="custom-image" 
             alt="{{ $product->name }}" 
             src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}">

        <div class="custom-details">
            <h3>
                <a href="{{ route('product', $product->slug) }}">
                    {{ Str::limit($product['name'], 80) }}
                </a>
            </h3>

            @if($overallRating[0] != 0)
                <div class="flash-product-review">
                    @for($inc = 1; $inc <= 5; $inc++)
                        @if ($inc <= (int)$overallRating[0])
                            <i class="tio-star text-warning"></i>
                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                            <i class="tio-star-half text-warning"></i>
                        @else
                            <i class="tio-star-outlined text-warning"></i>
                        @endif
                    @endfor
                    <label class="badge-style2">({{ count($product->reviews) }})</label>
                </div>
            @endif

            <h4>
                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                    <del>{{ webCurrencyConverter(amount: $product->unit_price) }}</del>
                @endif
                <span class="flash-product-price">
                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                </span>
            </h4>
        </div>
    </div>
@endif
