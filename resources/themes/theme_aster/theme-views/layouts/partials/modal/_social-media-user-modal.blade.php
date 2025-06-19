@if($user && $user?->login_medium != $socialLoginNewCustomer['login_medium'])
    <div class="modal fade social-media-user-modal" id="social-media-user-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <img alt="" class="dark-support mb-2 rounded-circle object-cover" width="100"
                         src="{{ getStorageImages(path: $user?->image_full_url ?? '', type: 'avatar') }}">
                    @if($user)
                        <div>{{ $user?->f_name }} {{ $user?->l_name }}</div>
                    @endif
                    <h4 class="mt-4">
                        {{translate('Is_It_You') }}?
                    </h4>
                    <div class="my-4 px-5">
                        @php
                            $decodedIdentity = base64_decode($identity);
                            $atPosition = strpos($decodedIdentity, '@');
                            if ($atPosition !== false) {
                                $localPart = substr($decodedIdentity, 0, $atPosition);
                                $domainPart = substr($decodedIdentity, $atPosition);
                                if (strlen($localPart) > 4) {
                                    $maskedIdentity = substr($localPart, 0, 4) . '****' . $domainPart;
                                } else {
                                    $maskedIdentity = $localPart . $domainPart;
                                }
                            } else {
                                $maskedIdentity = str_repeat('*', strlen($decodedIdentity));
                            }
                        @endphp
                        {{ translate('it_looks_like_the_email') }}
                        <span class="text-primary">{{ $maskedIdentity }}</span>
                        {{ translate('you_entered_has_already_been_used_and_has_an_existing_account') }}
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-center pb-4">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            {{ translate('No') }}
                        </button>
                        <a type="button" class="btn btn-primary get-view-by-onclick"
                           data-link="{{ route('customer.auth.social-login-confirmation', [
                                        'identity' => $identity,
                                        'status' => 'approve'
                                        ]) }}"
                        >
                            {{ translate('Yes') }}, {{ translate('It’s_Me') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
