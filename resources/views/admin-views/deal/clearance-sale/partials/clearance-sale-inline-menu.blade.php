@php
    use App\Enums\ViewPaths\Admin\ClearanceSale;
@endphp
<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/deal/clearance-sale') ? 'active' : '' }}">
            <a href="{{ route('admin.deal.clearance-sale.index') }}">{{ translate('Manage_Inhouse_Offer') }}</a>
        </li>

        <li class="{{ Request::is('admin/deal/clearance-sale/' . ClearanceSale::VENDOR_OFFERS[URI]) ? 'active' : '' }}">
            <a href="{{ route('admin.deal.clearance-sale.vendor-offers') }}">{{ translate('Vendor_Offers') }}</a>
        </li>

        <li class="{{ Request::is('admin/deal/clearance-sale/' . ClearanceSale::PRIORITY_SETUP[URI]) ? 'active' : '' }}">
            <a href="{{ route('admin.deal.clearance-sale.priority-setup') }}">{{ translate('priority_setup') }}</a>
        </li>
    </ul>
</div>
