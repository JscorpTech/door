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

    @media (max-width: 768px) {
        .product-detail-card {
            flex-direction: column;
            align-items: center;
        }
        .info-item {
            flex: 1 1 100%;
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

    @if($productDetails)
    <div class="card mb-4">
        <div class="card-body product-detail-card">
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
