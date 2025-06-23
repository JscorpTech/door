<div class="p-3 px-xl-4 py-sm-5">
    <h3 class="mb-4 view-mail-title text-capitalize">
        {{$title}}
    </h3>
    <div class="view-mail-body">
        {!! $body !!}
    </div>
    <p>{{translate('Customer')}}: {{$data['user']['f_name'].' '.$data['user']['l_name'] ?? 'John Doe'}}</p>
    <p>{{translate('Email')}}: {{$data['user']['email'] ?? 'example@gmail.com'}}</p>
    <p>{{translate('Phone')}}: {{$data['user']['phone'] ?? '123-456-7890'}}</p>
    <p>{{translate('product')}}:</p>
    <ul>
        @foreach($data['products'] ?? [['name'=>'Product 1', 'variant'=>'Variant 1']] as $product)
            <li>{{$product['name'].' - '.$product['variant']}}</li>
        @endforeach
    </ul>
    @include('admin-views.business-settings.email-template.partials-design.footer-design-without-logo')
</div>

