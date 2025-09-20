@extends('layouts.back-end.app')
@section('title', 'Детали продукта')

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Product detail card */
    .product-detail-card {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        padding: 25px;
        border: 1px solid #ddd;
        border-radius: 15px;
        background-color: #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .product-detail-card img {
        border-radius: 12px;
        max-width: 250px;
        height: auto;
        object-fit: cover;
        border: 1px solid #eee;
    }

    .product-info {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .info-item {
        flex: 1 1 45%;
        display: flex;
        flex-direction: column;
        margin-bottom: 10px;
    }

    .info-item label {
        font-weight: 600;
        color: #222;
        margin-bottom: 4px;
    }

    .info-item input {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        color: #333;
        font-size: 15px;
        overflow-x: auto;
        white-space: nowrap;
    }

    .product-description {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-left: 4px solid #4caf50;
        border-radius: 8px;
        color: #444;
        font-size: 15px;
        line-height: 1.6;
        max-height: 200px;
        overflow-y: auto;
    }

    /* Shop info card */
   /* Shop info card */
.shop-info-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    flex-direction: column; /* Продавец tepada chiqishi uchun */
}

.shop-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.shop-title {
    margin-bottom: 15px;
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    text-align: left; /* chapda turadi */
    width: 100%;
}

.shop-info-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #9e9e9e;
    color: #fff;
    font-size: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.shop-details h4 {
    margin-bottom: 12px;
    color: #1a1a1a;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.shop-details p {
    margin-bottom: 8px;
    color: #4a4a4a;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.shop-details p i {
    color: #4caf50;
    font-size: 1.1rem;
    transition: color 0.3s ease;
}

.shop-details p a {
    color: #4a4a4a;
    text-decoration: none;
    transition: color 0.3s ease;
}

.shop-details p a:hover {
    color: #4caf50;
    text-decoration: underline;
}

.shop-details p:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .shop-info-card {
        flex-direction: column;
        text-align: center;
        padding: 15px;
    }

    .shop-title {
        text-align: center;
    }

    .shop-info-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
    }

    .shop-details h4 {
        font-size: 1.1rem;
        justify-content: center;
    }

    .shop-details p {
        font-size: 0.9rem;
        justify-content: center;
    }
}

</style>
@endpush

@section('content')
@php
    $detail = $detail ?? null;
    $productDetails = $detail && $detail->product_details ? json_decode($detail->product_details) : null;

    $categoryName = $productDetails && isset($productDetails->category_id)
                    ? ($categories[$productDetails->category_id]->name ?? 'Нет категории')
                    : 'Нет категории';

    $brandName = $productDetails && isset($productDetails->brand_id)
                 ? ($brands[$productDetails->brand_id]->name ?? 'Нет бренда')
                 : 'Нет бренда';

    $productCreatedAt = $detail?->created_at ? $detail->created_at->format('d.m.Y H:i') : '—';
    $orderCreatedAt = $detail?->order?->created_at ? $detail->order->created_at->format('d.m.Y H:i') : '—';
@endphp

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset('public/assets/back-end/img/all-orders.png') }}" alt="">
            Детали продукта
        </h2>
    </div>

    {{-- Shop Info --}}
   <div class="shop-info-card">
    <div class="shop-title" style="margin-bottom: 15px;">
        <h3>Продавец</h3>
    </div>

    <div class="shop-info-icon">
        @if(!empty($detail->seller->shop->image))
            <img src="{{ asset('storage/' . $detail->seller->shop->image) }}" 
                alt="{{ $detail->seller->shop->name }}" 
                style="width: 70px; height: 70px; object-fit: cover; border-radius: 50%;">
        @else
            <i class="bi bi-shop"></i>
        @endif
    </div>

    <div class="shop-details">
        <h4><i class="bi bi-building"></i> {{ $detail->seller->shop->name ?? 'Shop Name' }}</h4>

        <p><i class="bi bi-telephone"></i> 
            <a href="tel:{{ $detail->seller->shop->contact ?? '#' }}">
                {{ $detail->seller->shop->contact ?? 'No contact available' }}
            </a>
        </p>
        @if(!empty($detail->seller->shop->email))
            <p><i class="bi bi-envelope"></i> 
                <a href="mailto:{{ $detail->seller->shop->email }}">
                    {{ $detail->seller->shop->email }}
                </a>
            </p>
        @endif
        @if(!empty($detail->seller->shop->address))
            <p><i class="bi bi-geo-alt"></i> {{ $detail->seller->shop->address }}</p>
        @endif
    </div>
</div>


    @if($productDetails)
    <div class="card mb-4">

    
        <div class="card-body product-detail-card">
            <div class="shop-title" style="margin-bottom: 15px;">
             <h3>Информация о продукте</h3>
    </div>
            {{-- Product Image --}}
            <div>
                <img src="{{ getStorageImages(path: $detail?->productAllStatus?->thumbnail_full_url, type: 'backend-product') }}"
                     alt="{{ $productDetails->name ?? 'Product Image' }}">
            </div>

            {{-- Product Info as Inputs --}}
            <div class="product-info">
                <div class="info-item">
                    <label>Название</label>
                    <input type="text" value="{{ $productDetails->name ?? '—' }}" readonly>
                </div>

                <div class="info-item">
                    <label>Цена</label>
                    <input type="text" value="{{ setCurrencySymbol(amount: $productDetails->unit_price ?? 0) }}" readonly>
                </div>

                @if(!empty($productDetails->variant))
                <div class="info-item">
                    <label>Вариант</label>
                    <input type="text" value="{{ $productDetails->variant }}" readonly>
                </div>
                @endif

                <div class="info-item">
                    <label>Остаток на складе</label>
                    <input type="text" value="{{ $productDetails->current_stock ?? 0 }}" readonly>
                </div>

                @if(isset($productDetails->tax))
                <div class="info-item">
                    <label>Налог</label>
                    <input type="text" value="{{ $productDetails->tax ?? 0 }}{{ ($productDetails->tax_type ?? '') === 'percent' ? '%' : '' }} ({{ $productDetails->tax_model ?? '—' }})" readonly>
                </div>
                @endif

                @if(isset($productDetails->unit))
                <div class="info-item">
                    <label>Единица</label>
                    <input type="text" value="{{ $productDetails->unit }}" readonly>
                </div>
                @endif

                <div class="info-item">
                    <label>Категория</label>
                    <input type="text" value="{{ $categoryName }}" readonly>
                </div>

                <div class="info-item">
                    <label>Бренд</label>
                    <input type="text" value="{{ $brandName }}" readonly>
                </div>

                <div class="info-item">
                    <label>Дата создания продукта</label>
                    <input type="text" value="{{ $productCreatedAt }}" readonly>
                </div>

                <div class="info-item">
                    <label>Дата заказа</label>
                    <input type="text" value="{{ $orderCreatedAt }}" readonly>
                </div>
            </div>

            {{-- Description --}}
            @if(!empty($productDetails->details))
            <div class="product-description">
                <strong>Описание:</strong>
                <div>{!! $productDetails->details !!}</div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="alert alert-warning">Детали продукта не найдены.</div>
    @endif
</div>
@endsection
