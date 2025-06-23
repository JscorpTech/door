<div class="p-3 px-xl-4 py-sm-5">
    <h3 class="mb-4 view-mail-title text-capitalize">
        {{$title}}
    </h3>
    <div class="view-mail-body">
        {!! $body !!}
    </div>
    <div class="main-table-inner mb-4">
        <div class="d-flex justify-content-center pt-3">
            <img width="76" class="mb-4" id="view-mail-logo" src="{{$template->logo_full_url['path'] ?? getStorageImages(path: $companyLogo, type:'backend-logo')}}" alt="">
        </div>
        <h3 class="mb-3 text-center">{{translate('order_Info')}}</h3>
        <div class="main-table-inner bg-white">
            <div class="d-flex mb-3 p-2">
                <div class="flex-1 w-49">
                    <h3 class="mb-2">{{translate('order_Summary')}}</h3>
                    <div class="mb-2">{{translate('Order').' # '.($data['order']->id ?? '432121')}} </div>
                    <div>{{date('d M, Y : h:i:A' ,strtotime($data['order']->created_at ?? now()))}}</div>
                </div>
            </div>
          
            <hr>
            <p class="view-footer-text">
                {{$footerText}}
            </p>
            <p>{{translate('Thanks_&_Regards')}}, <br> {{getWebConfig('company_name')}}</p>
        </div>
    </div>
    @include('admin-views.business-settings.email-template.partials-design.footer-design-without-logo')
</div>
