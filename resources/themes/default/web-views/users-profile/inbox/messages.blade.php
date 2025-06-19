@php
    use App\Enums\GlobalConstant;
    use App\Utils\FileManagerLogic;
@endphp
@if (isset($chattingMessages))
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
        @if ($message->sent_by_seller || $message->sent_by_admin || $message->sent_by_delivery_man)
            <div class="incoming_msg d-flex align-items-end">
                <div class="incoming_msg_img">
                    <img
                        src="{{ $userType == 'admin' ? getStorageImages(path: $web_config['fav_icon'], type: 'shop') : ( $userType == 'vendor' ? getStorageImages(path: $message?->shop?->image_full_url, type: 'shop') : getStorageImages(path: $message?->deliveryMan?->image_full_url, type: 'avatar')) }}"
                        alt="Image Description">
                </div>
                <div class="received_msg d-flex flex-column align-items-start">
                    <div class="received_withdraw_msg d-flex flex-column align-items-start gap-2"
                         data-toggle="tooltip"
                         @if($message->created_at->diffInDays() > 6)
                             title="{{ $message->created_at->format('M-d-Y h:i A') }}"
                         @elseif($message->created_at->isYesterday())
                             title="Yesterday {{ $message->created_at->format('h:i A') }}"
                         @elseif($message->created_at->isToday())
                             title="Today {{ $message->created_at->format('h:i A') }}"
                         @else
                             title="{{ $message->created_at->format('l h:i A') }}"
                        @endif
                    >
                        @if (count($message->attachment_full_url) >0)
                            @if(count($documentFiles) > 0)
                                <div class="d-flex flex-wrap">
                                    <div
                                        class="d-flex gap-2 justify-content-start align-items-center position-relative">
                                        <div class="row g-1 flex-wrap pt-1 justify-content-start gap-2">
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
                                                                        src="{{theme_asset('public/assets/front-end/img/'.$icon.'.png')}}"
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
                                <div class="d-flex justify-content-end align-items-center flex-wrap">
                                    <div
                                        class="zip-wrapper d-flex justify-content-start align-items-center position-relative gap-3">
                                            <?php
                                            $isSingleDownload = count($otherFiles) === 1;
                                            $singleFilePath = $isSingleDownload ? $otherFiles[0]['path'] : '';
                                            ?>
                                        <div
                                            class="row g-1 gap-2 flex-wrap justify-content-start align-items-center max-w-150px zip-images">
                                            @foreach ($otherFiles as $secondIndex => $attachment)
                                                @php($extension = strrchr($attachment['key'],'.'))
                                                @php($downloadPath = $attachment['path'])
                                                <div
                                                    class="position-relative align-items-center img_row{{$secondIndex}} {{$secondIndex > 3 ? 'd-none' : 'd-flex'}}">
                                                        <a data-toggle="modal"
                                                           data-target="#imgViewModal{{ $message->id }}"
                                                           data-type="video"
                                                           href="javascript:"
                                                           download
                                                           class="position-relative {{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? '' : 'aspect-1 overflow-hidden d-block border rounded' }}"
                                                           data-index="{{ $secondIndex }}">
                                                            @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                                <video class="rounded video-element" width="100"
                                                                       height="60" preload="metadata">
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
                                                <div class="modal-content bg-transparent border-0">
                                                    <div class="modal-body pt-0">
                                                        <div class="imgView-slider owl-theme owl-carousel"
                                                             dir="ltr">
                                                            @foreach($otherFiles as $file)
                                                                @php($extension = strrchr($file['key'],'.'))
                                                                <div class="imgView-item">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-end">
                                                                        <a href="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? ($file['path'] ?? '') :  getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                           download
                                                                           class="d-flex align-items-center gap-2 mb-10px">
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
                                                                            <i class="tio-clear mt-0"></i>
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
                                                                                    Your browser does not
                                                                                    support
                                                                                    the video tag.
                                                                                </video>
                                                                                <button type="button"
                                                                                        class="btn video-play-btn modal_video-play-btn p-1">
                                                                                    <img height="14"
                                                                                         src="{{theme_asset('public/assets/front-end/img/icons/carbon_play-filled.svg')}}"
                                                                                         alt="Play">
                                                                                </button>
                                                                            @else
                                                                                <div
                                                                                    class="image-wrapper position-relative">
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
                                        <a download
                                           href="{{ $isSingleDownload && $singleFilePath ? $singleFilePath : 'javascript:' }}"
                                           class="btn btn--download d-flex justify-content-center align-items-center flex-shrink-0 {{ count($otherFiles) > 1 ? 'zip-download' : '' }}">
                                            <i class="tio-download-to"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if($message->message)
                            <div class="d-inline-block">
                                <p>
                                    {{$message->message}}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="outgoing_msg">
                <div class="send_msg d-flex flex-column align-items-end gap-2" data-toggle="tooltip"
                     @if($message->created_at->diffInDays() > 6)
                         title="{{ $message->created_at->format('M-d-Y h:i A') }}"
                     @elseif($message->created_at->isYesterday())
                         title="Yesterday {{ $message->created_at->format('h:i A') }}"
                     @elseif($message->created_at->isToday())
                         title="Today {{ $message->created_at->format('h:i A') }}"
                     @else
                         title="{{ $message->created_at->format('l h:i A') }}"
                    @endif
                >
                    @if (count($message->attachment_full_url) >0)
                        @if(count($documentFiles) > 0)
                            <div class="d-flex justify-content-end flex-wrap">
                                <div
                                    class="d-flex gap-2 justify-content-end align-items-center position-relative">
                                    <div class="row g-1 flex-wrap pt-1 justify-content-end gap-2">
                                        @foreach ($documentFiles as $secondIndex => $attachment)
                                            @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                            @php($downloadPath = $attachment['path'])
                                            <div class="d-flex">
                                                <a class="text--title" href="{{$downloadPath}}" target="_blank">
                                                    <div class="uploaded-file-item">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between gap-2">
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <img
                                                                    src="{{theme_asset('public/assets/front-end/img/'.$icon.'.png')}}"
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
                            <div class="d-flex justify-content-end flex-wrap">
                                <div
                                    class="zip-wrapper d-flex gap-3 justify-content-end align-items-center position-relative">
                                        <?php
                                        $isSingleDownload = count($otherFiles) === 1;
                                        $singleFilePath = $isSingleDownload ? $otherFiles[0]['path'] : '';
                                        ?>

                                    <a download
                                       href="{{ $isSingleDownload && $singleFilePath ? $singleFilePath : 'javascript:' }}"
                                       class="btn btn--download d-flex justify-content-center align-items-center flex-shrink-0 {{ count($otherFiles) > 1 ? 'zip-download' : '' }}">
                                       <i class="tio-download-to"></i>
                                    </a>
                                    <div class="row g-1 gap-2 flex-wrap pt-1 justify-content-end  max-w-150px zip-images">
                                        @foreach ($otherFiles as $secondIndex => $attachment)
                                            @php($extension = strrchr($attachment['key'],'.'))
                                            <div
                                                class="position-relative img_row{{$secondIndex}} {{$secondIndex > 3 ? 'd-none' : 'd-flex'}}">
                                                <a data-toggle="modal"
                                                   data-target="#imgViewModal{{ $message->id }}"
                                                   data-type="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'video' : 'image' }}"
                                                   href="javascript:"
                                                   download
                                                   class="position-relative {{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? '' : 'aspect-1 overflow-hidden d-block border rounded' }}"
                                                   data-index="{{ $secondIndex }}">
                                                    @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                        <video class="rounded video-element" width="100"
                                                               height="60"
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
                                            <div class="modal-content bg-transparent border-0">
                                                <div class="modal-body pt-0">
                                                    <div class="imgView-slider owl-theme owl-carousel"
                                                         dir="ltr">
                                                        @foreach($otherFiles as $file)
                                                            @php($extension = strrchr($file['key'],'.'))
                                                            <div class="imgView-item">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-end">
                                                                    <a href="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? ($file['path'] ?? '') :  getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                       download
                                                                       class="d-flex align-items-center gap-2 mb-10px">
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
                                                                        <i class="tio-clear mt-0"></i>
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
                                                                                Your browser does not
                                                                                support
                                                                                the video tag.
                                                                            </video>
                                                                            <button type="button"
                                                                                    class="btn video-play-btn modal_video-play-btn p-1">
                                                                                <img height="14"
                                                                                     src="{{theme_asset('public/assets/front-end/img/icons/carbon_play-filled.svg')}}"
                                                                                     alt="Play">
                                                                            </button>
                                                                        @else
                                                                            <div
                                                                                class="image-wrapper position-relative">
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
                        <div class="d-inline-block">
                            <p class="btn--primary">
                                {{$message->message}}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endForeach
    <div id="down"></div>
@endif

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/js-zip/jszip.min.js')}}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/js-zip/FileSaver.min.js')}}"></script>
@endpush
