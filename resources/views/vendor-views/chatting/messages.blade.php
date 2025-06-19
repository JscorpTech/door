@php
    use App\Enums\GlobalConstant;
    use App\Utils\FileManagerLogic;
@endphp

@foreach($chattingMessages as $key => $message)
        <?php
        $documentExtensions = GlobalConstant::DOCUMENT_EXTENSION;
        $documentFiles = [];
        $otherFiles = [];
        if (!empty($message->attachment_full_url)) {
            foreach ($message->attachment_full_url as $attachment) {
                $extension = strrchr($attachment['key'], '.');
                if (in_array($extension, $documentExtensions)) {
                    $documentFiles[] = $attachment;
                } else {
                    $otherFiles[] = $attachment;
                }
            }
        }
        ?>
    @if ($message->sent_by_customer || $message->sent_by_delivery_man)
        <div class="incoming_msg d-flex align-items-end gap-2 my-2">
            <div class="">
                <img class="avatar-img user-avatar-image border inbox-user-avatar-25" id="profile_image" width="40"
                     height="40"
                     src="{{ getStorageImages(path: $lastChatUser->image_full_url,type: 'backend-profile')}}"
                     alt="Image Description">
            </div>
            <div class="received_msg d-flex flex-column align-items-end" data-toggle="tooltip"
                 data-custom-class="chatting-time min-w-0"
                 @if($message->message || count($message->attachment_full_url) > 0)
                     @if($message->created_at->diffInDays() > 6)
                         title="{{ $message->created_at->format('M-d-Y h:i A') }}"
                 @elseif($message->created_at->isYesterday())
                     title="Yesterday {{ $message->created_at->format('h:i A') }}"
                 @elseif($message->created_at->isToday())
                     title="Today {{ $message->created_at->format('h:i A') }}"
                 @else
                     title="{{ $message->created_at->format('l h:i A') }}"
                @endif
                @endif
            >
                <div class="received_withdraw_msg d-flex flex-column align-items-start">
                    @if (count($message->attachment_full_url)>0)
                        @if(count($documentFiles) > 0)
                            <div class="d-flex justify-content-end flex-wrap pt-1">
                                <div
                                    class="d-flex gap-2 justify-content-start align-items-center position-relative">
                                    <div class="row g-1 flex-wrap pt-1 justify-content-start">
                                        @foreach ($documentFiles as $secondIndex => $attachment)
                                            @php($extension = strrchr($attachment['key'],'.'))
                                            @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                            @php($downloadPath = $attachment['path'])
                                            <div class="d-flex">
                                                <a class="text--title" href="{{$downloadPath}}" target="_blank">
                                                    <div class="uploaded-file-item">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between gap-2">
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <img
                                                                    src="{{dynamicAsset('public/assets/back-end/img/'.$icon.'.png')}}"
                                                                    class="file-icon" alt="">
                                                                <div class="upload-file-item-content">
                                                                    <div class="pdf-file-name">
                                                                        {{($attachment['key'])}}
                                                                    </div>
                                                                    <small>{{FileManagerLogic::getFileSize($downloadPath)}}</small>
                                                                </div>
                                                            </div>
                                                            <a
                                                                class="btn btn--download d-flex justify-content-center align-items-center"
                                                                href="{{ $attachment['path'] ?? '' }}"
                                                                download>
                                                                <i class="tio-download-to"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(count($otherFiles) > 0)
                            <div class="d-flex justify-content-end align-items-center flex-wrap pt-1">
                                <div
                                    class="zip-wrapper d-flex gap-3 justify-content-start align-items-center position-relative">
                                        <?php
                                        $isSingleDownload = count($otherFiles) === 1;
                                        $singleFilePath = $isSingleDownload ? $otherFiles[0]['path'] : '';
                                        ?>
                                    <div
                                        class="row g-1 gap-1 flex-wrap pt-1 justify-content-start align-items-center zip-images max-w-150px">
                                        @foreach ($otherFiles as $secondIndex => $attachment)
                                            @php($extension = strrchr($attachment['key'],'.'))
                                            @php($downloadPath = $attachment['path'])
                                            <div
                                                class="position-relative img_row{{$secondIndex}} {{$secondIndex > 3 ? 'd-none' : ''}}">
                                                <a data-toggle="modal"
                                                   data-target="#imgViewModal{{ $message->id }}"
                                                   data-type="video"
                                                   href="javascript:"
                                                   download
                                                   class="position-relative {{ !in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'aspect-1 overflow-hidden d-block border rounded ' : '' }}"
                                                   data-index="{{ $secondIndex }}">
                                                    @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                        <video class="rounded video-element" width="100" height="60"
                                                               preload="metadata">
                                                            <source src="{{ $attachment['path'] ?? '' }}"
                                                                    type="video/mp4">
                                                            <source src="{{ $attachment['path'] ?? '' }}"
                                                                    type="video/ogg">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                        <button type="button"
                                                                class="btn video-play-btn text-primary rounded-circle bg-white p-1 d-flex justify-content-center align-items-center">
                                                            <i class="tio-play"></i>
                                                        </button>
                                                    @else
                                                        <img class="img-fit aspect-1 w-60px" alt=""
                                                             src="{{ getStorageImages(path: $attachment, type: 'backend-basic') }}">
                                                    @endif
                                                    @if($secondIndex == 3 && count($otherFiles) > 4 )
                                                        <div class="extra-images">
                                                            <span class="extra-image-count">
                                                                +{{ count($otherFiles) - 3 }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                    <a download
                                       href="{{ $isSingleDownload && $singleFilePath ?  $singleFilePath : 'javascript:' }}"
                                       class="btn btn--download d-flex justify-content-center align-items-center flex-shrink-0 {{ $isSingleDownload ? '' : 'zip-download' }}">
                                        <i class="tio-download-to"></i>
                                    </a>
                                    <div class="modal fade imgViewModal" id="imgViewModal{{ $message->id }}"
                                         tabindex="-1"
                                         aria-labelledby="imgViewModal{{ $message->id }}Label" role="dialog"
                                         aria-modal="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content bg-transparent">
                                                <div class="modal-body pt-0">
                                                    <div class="imgView-slider owl-theme owl-carousel"
                                                         dir="ltr">
                                                        @foreach($otherFiles as $file)
                                                            @php($extension = strrchr($file['key'],'.'))
                                                            <div class="imgView-item">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-end">
                                                                    <a href="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? $file['path'] : getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                       class="d-flex align-items-center gap-2 mb-10px"
                                                                       download>
                                                                        <div
                                                                            class="btn btn--download d-flex justify-content-center align-items-center"
                                                                        >
                                                                            <i class="tio-download-to"></i>
                                                                        </div>
                                                                        <h6 class="text-white text-underline mb-0">
                                                                            Download {{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'Video' : 'Image' }}</h6>
                                                                    </a>
                                                                    <button type="button"
                                                                            class="btn btn-close p-1 border-0"
                                                                            data-dismiss="modal"
                                                                            aria-label="Close">
                                                                        <i class="tio-clear"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="image-wrapper">
                                                                    <div class="position-relative">
                                                                        @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                                            <video
                                                                                class="rounded video-element"
                                                                                width="450"
                                                                                height="260"
                                                                                preload="metadata"
                                                                            >
                                                                                <source
                                                                                    src="{{ $file['path'] ?? '' }}"
                                                                                    type="video/mp4">
                                                                                <source
                                                                                    src="{{ $file['path'] ?? '' }}"
                                                                                    type="video/ogg">
                                                                                Your browser does not support
                                                                                the video tag.
                                                                            </video>
                                                                            <button type="button"
                                                                                    class="btn video-play-btn modal_video-play-btn p-1">
                                                                                <img class=""
                                                                                     src="{{dynamicAsset('public/assets/back-end/img/icons/carbon_play-filled.svg')}}"
                                                                                     alt="Play">
                                                                            </button>
                                                                        @else
                                                                            <div class="image-wrapper">
                                                                                <img class="image" alt=""
                                                                                     src="{{ getStorageImages(path: $file, type: 'backend-basic') }}">
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div
                                                        class="imgView-slider_buttons d-flex justify-content-center"
                                                        dir="ltr">
                                                        <button type="button"
                                                                class="btn owl-btn imgView-owl-prev">
                                                            <i class="tio-chevron-left"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn owl-btn imgView-owl-next">
                                                            <i class="tio-chevron-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                    @if($message->message)
                        <div class="message-text-section rounded mt-1 d-inline-block mt-2">
                            <p class="m-0 pb-1">
                                {{$message->message}}
                            </p>
                            <span class="small text-end w-100 d-block text-muted"></span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="outgoing_msg my-2">
            <div class="sent_msg px-2 d-flex flex-column align-items-end" data-toggle="tooltip"
                 data-custom-class="chatting-time min-w-0"
                 @if($message->message || count($message->attachment_full_url) > 0)
                     @if($message->created_at->diffInDays() > 6)
                         title="{{ $message->created_at->format('M-d-Y h:i A') }}"
                 @elseif($message->created_at->isYesterday())
                     title="Yesterday {{ $message->created_at->format('h:i A') }}"
                 @elseif($message->created_at->isToday())
                     title="Today {{ $message->created_at->format('h:i A') }}"
                 @else
                     title="{{ $message->created_at->format('l h:i A') }}"
                @endif
                @endif
            >
                @if (count($message->attachment_full_url) > 0)
                    @if(count($documentFiles) > 0)
                        <div class="d-flex justify-content-end flex-wrap pt-1">
                            <div
                                class="d-flex gap-2 justify-content-end align-items-center position-relative">
                                <div class="row g-1 flex-wrap pt-1 justify-content-end">
                                    @foreach ($documentFiles as $secondIndex => $attachment)
                                        @php($extension = strrchr($attachment['key'],'.'))
                                        @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                        @php($downloadPath = $attachment['path'])
                                        <div class="d-flex">
                                            <a class="text--title" href="{{$downloadPath}}" target="_blank">
                                                <div class="uploaded-file-item">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between gap-2">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <img
                                                                src="{{dynamicAsset('public/assets/back-end/img/'.$icon.'.png')}}"
                                                                class="file-icon" alt="">
                                                            <div class="upload-file-item-content">
                                                                <div class="pdf-file-name">
                                                                    {{($attachment['key'])}}
                                                                </div>
                                                                <small>{{FileManagerLogic::getFileSize($downloadPath)}}</small>
                                                            </div>
                                                        </div>
                                                        <a
                                                            class="btn btn--download d-flex justify-content-center align-items-center"
                                                            href="{{ $attachment['path'] ?? '' }}"
                                                            download>
                                                            <i class="tio-download-to"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(count($otherFiles) > 0)
                        <div class="d-flex justify-content-end flex-wrap pt-1">
                            <div
                                class="zip-wrapper d-flex gap-3 justify-content-end align-items-center position-relative">
                                    <?php
                                    $isSingleDownload = count($otherFiles) === 1;
                                    $singleFilePath = $isSingleDownload ? $otherFiles[0]['path'] : '';
                                    ?>
                                <a download
                                   href="{{ $isSingleDownload && $singleFilePath ?  $singleFilePath : 'javascript:' }}"
                                   class="btn btn--download d-flex justify-content-center align-items-center flex-shrink-0 {{ $isSingleDownload ? '' : 'zip-download' }}">
                                    <i class="tio-download-to"></i>
                                </a>
                                <div class="row g-1 gap-1 flex-wrap pt-1 justify-content-end zip-images max-w-150px">
                                    @foreach ($otherFiles as $secondIndex => $attachment)
                                        @php($extension = strrchr($attachment['key'],'.'))
                                        <div
                                            class="position-relative img_row{{$secondIndex}} {{$secondIndex > 3 ? 'd-none' : ''}}">
                                            <a data-toggle="modal"
                                               data-target="#imgViewModal{{ $message->id }}"
                                               data-type="video"
                                               href="javascript:"
                                               download
                                               class="position-relative {{ !in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'aspect-1 overflow-hidden d-block border rounded ' : '' }}"
                                               data-index="{{ $secondIndex }}">
                                                @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                    <video class="rounded video-element" width="100" height="60"
                                                           preload="metadata">
                                                        <source src="{{ $attachment['path'] ?? '' }}"
                                                                type="video/mp4">
                                                        <source src="{{ $attachment['path'] ?? '' }}"
                                                                type="video/ogg">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <button type="button"
                                                            class="btn video-play-btn text-primary rounded-circle bg-white p-1 d-flex justify-content-center align-items-center">
                                                        <i class="tio-play"></i>
                                                    </button>
                                                @else
                                                    <img class="img-fit aspect-1 w-60px" alt=""
                                                         src="{{ getStorageImages(path: $attachment, type: 'backend-basic') }}">
                                                @endif
                                                @if($secondIndex == 3 && count($otherFiles) > 4 )
                                                    <div class="extra-images">
                                                            <span class="extra-image-count">
                                                                +{{ count($otherFiles) - 3 }}
                                                            </span>
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="modal fade imgViewModal" id="imgViewModal{{ $message->id }}"
                                     tabindex="-1"
                                     aria-labelledby="imgViewModal{{ $message->id }}Label" role="dialog"
                                     aria-modal="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content bg-transparent">
                                            <div class="modal-body pt-0">
                                                <div class="imgView-slider owl-theme owl-carousel"
                                                     dir="ltr">
                                                    @foreach($otherFiles as $file)
                                                        @php($extension = strrchr($file['key'],'.'))
                                                        <div class="imgView-item">
                                                            <div
                                                                class="d-flex justify-content-between align-items-end">
                                                                <a href="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? $file['path'] : getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                   class="d-flex align-items-center gap-2 mb-10px"
                                                                   download>
                                                                    <div
                                                                        class="btn btn--download d-flex justify-content-center align-items-center"
                                                                    >
                                                                        <i class="tio-download-to"></i>
                                                                    </div>
                                                                    <h6 class="text-white text-underline mb-0">
                                                                        Download {{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'Video' : 'Image' }}</h6>
                                                                </a>
                                                                <button type="button"
                                                                        class="btn btn-close p-1 border-0"
                                                                        data-dismiss="modal"
                                                                        aria-label="Close">
                                                                    <i class="tio-clear"></i>
                                                                </button>
                                                            </div>
                                                            <div class="image-wrapper">
                                                                <div class="position-relative">
                                                                    @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                                        <video
                                                                            class="rounded video-element"
                                                                            width="450"
                                                                            height="260"
                                                                            preload="metadata"
                                                                        >
                                                                            <source
                                                                                src="{{ $file['path'] ?? '' }}"
                                                                                type="video/mp4">
                                                                            <source
                                                                                src="{{ $file['path'] ?? '' }}"
                                                                                type="video/ogg">
                                                                            Your browser does not support
                                                                            the video tag.
                                                                        </video>
                                                                        <button type="button"
                                                                                class="btn video-play-btn modal_video-play-btn p-1">
                                                                            <img class=""
                                                                                 src="{{dynamicAsset('public/assets/back-end/img/icons/carbon_play-filled.svg')}}"
                                                                                 alt="Play">
                                                                        </button>
                                                                    @else
                                                                        <div class="image-wrapper">
                                                                            <img class="image" alt=""
                                                                                 src="{{ getStorageImages(path: $file, type: 'backend-basic') }}">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @if(count($otherFiles) > 1)
                                                    <div
                                                        class="imgView-slider_buttons d-flex justify-content-center"
                                                        dir="ltr">
                                                        <button type="button"
                                                                class="btn owl-btn imgView-owl-prev">
                                                            <i class="tio-chevron-left"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn owl-btn imgView-owl-next">
                                                            <i class="tio-chevron-right"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                @if(!empty($message->message))
                    <div class="message-text-section rounded mt-1 d-inline-block mt-2">
                        <p class="m-0 pb-1">
                            {{$message->message}}
                        </p>
                        <span class="small text-start w-100 d-block text-muted"></span>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endForeach

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/js-zip/jszip.min.js')}}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/js-zip/FileSaver.min.js')}}"></script>
@endpush
