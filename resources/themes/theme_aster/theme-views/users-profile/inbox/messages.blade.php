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
            <div class="d-flex align-items-end gap-2 py-2">
                <div class="incoming_msg_img avatar rounded-circle">
                    <img class="img-fit aspect-1 rounded-circle dark-support"
                         src="{{ $userType == 'admin' ? getStorageImages(path: $web_config['fav_icon'], type: 'shop') : ( $userType == 'vendor' ? getStorageImages(path: $message?->shop?->image_full_url, type: 'shop') : getStorageImages(path: $message?->deliveryMan?->image_full_url, type: 'avatar')) }}"
                         alt="Image Description">
                </div>
                <div class="received_msg d-flex flex-column gap-2" data-bs-toggle="tooltip"
                     @if($message->created_at->diffInDays() > 6)
                         data-bs-title="{{ $message->created_at->format('M-d-Y h:i A') }}"
                     @elseif($message->created_at->isYesterday())
                         data-bs-title="Yesterday {{ $message->created_at->format('h:i A') }}"
                     @elseif($message->created_at->isToday())
                         data-bs-title="Today {{ $message->created_at->format('h:i A') }}"
                     @else
                         data-bs-title="{{ $message->created_at->format('l h:i A') }}"
                    @endif
                >
                    @if (count($message->attachment_full_url) >0)
                        @if(count($documentFiles) > 0)
                            <div class="d-flex gap-3 justify-content-start align-items-center position-relative">
                                <div class="d-flex gap-2 flex-wrap justify-content-start">
                                    @foreach ($documentFiles as $secondIndex => $attachment)
                                        @php($extension = strrchr($attachment['key'],'.'))
                                        @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                        @php($downloadPath = $attachment['path'])
                                        <div class="d-flex">
                                            <a href="{{$downloadPath}}" target="_blank">
                                                <div class="uploaded-file-item">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between gap-2">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <img
                                                                src="{{theme_asset('assets/img/'.$icon.'.png')}}"
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
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(count($otherFiles) > 0)
                            <div
                                class="zip-wrapper d-flex gap-3 justify-content-start align-items-center  position-relative">
                                <div
                                    class="d-flex gap-2 flex-wrap justify-content-start custom-image-popup-init align-items-center zip-images max-w-150px">
                                        <?php
                                        $isSingleDownload = count($otherFiles) === 1;
                                        $singleFilePath = $isSingleDownload ? $otherFiles[0]['path'] : '';
                                        ?>
                                    @foreach ($otherFiles as $secondIndex => $attachment)
                                        @php($extension = strrchr($attachment['key'],'.'))
                                        @php($downloadPath = $attachment['path'])
                                        <div
                                            class="position-relative img_row{{$secondIndex}} {{$secondIndex > 3 ? 'd-none' : ''}}">
                                            <a data-bs-toggle="modal"
                                               data-bs-target="#imgViewModal{{ $message->id }}"
                                               data-type="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'video' : 'image' }}"
                                               href="javascript:"
                                               download
                                               class="overflow-hidden d-block border rounded position-relative"
                                               data-index="{{ $secondIndex }}">
                                                @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                    <video class="rounded video-element" width="100" height="60"
                                                           preload="metadata"
                                                           >
                                                        <source src="{{ $attachment['path'] ?? '' }}" type="video/mp4">
                                                        <source src="{{ $attachment['path'] ?? '' }}" type="video/ogg">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <button type="button"
                                                            class="btn video-play-btn text--primary rounded-circle bg-white p-1 d-flex justify-content-center align-items-center">
                                                        <i class="bi bi-play-fill"></i>
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
                                <div class="modal fade imgViewModal" id="imgViewModal{{ $message->id }}" tabindex="-1"
                                     aria-labelledby="imgViewModal{{ $message->id }}Label" role="dialog"
                                     aria-modal="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content bg-transparent border-0">
                                            <div class="modal-body pt-0">
                                                <div class="imgView-slider owl-theme owl-carousel" dir="ltr">
                                                    @foreach($otherFiles as $file)
                                                        @php($extension = strrchr($file['key'],'.'))
                                                        <div class="imgView-item">
                                                            <div
                                                                class="d-flex justify-content-between align-items-end">
                                                                <a href="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? ($file['path'] ?? '') : getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                   download
                                                                   class="d-flex align-items-center gap-2 mb-2">
                                                                    <div
                                                                        class="btn btn--download d-flex justify-content-center align-items-center"
                                                                    >
                                                                    <i class="bi bi-download"></i>
                                                                    </div>
                                                                    <h6 class="text-white text-underline mb-0">
                                                                        Download {{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'Video' : 'Image' }}</h6>
                                                                </a>
                                                                <button type="button"
                                                                        class="btn btn-close p-1 border-0"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close">
                                                                    <i class="bi bi-x-lg"></i>
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
                                                                            <img height="14"
                                                                                 src="{{ theme_asset('assets/img/icons/carbon_play-filled.svg') }}"
                                                                                 alt="Play">
                                                                        </button>
                                                                    @else
                                                                        <div class="image-wrapper">
                                                                            <a
                                                                                href="{{ getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                                download
                                                                                class="position-relative">
                                                                                <img class="image" alt=""
                                                                                     src="{{ getStorageImages(path: $file, type: 'backend-basic') }}">
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="imgView-slider_buttons d-flex justify-content-center"
                                                     dir="ltr">
                                                    <button type="button" class="btn owl-btn imgView-owl-prev">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </button>
                                                    <button type="button" class="btn owl-btn imgView-owl-next">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a download
                                   href="{{ $isSingleDownload && $singleFilePath ? $singleFilePath  : 'javascript:'}}"
                                   class="btn btn--download d-flex justify-content-center align-items-center flex-shrink-0 {{ count($otherFiles) > 1 ? 'zip-download' : '' }}">
                                   <i class="bi bi-download"></i>
                                </a>
                            </div>
                        @endif

                    @endif
                    @if($message->message)
                        <div class="d-inline-block">
                            <p class="message_text d-inline-block">
                                {{$message->message}}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="outgoing_msg d-flex flex-column gap-2 py-2" id="outgoing_msg"
                 data-bs-toggle="tooltip"
                 @if($message->created_at->diffInDays() > 6)
                     data-bs-title="{{ $message->created_at->format('M-d-Y h:i A') }}"
                 @elseif($message->created_at->isYesterday())
                     data-bs-title="Yesterday {{ $message->created_at->format('h:i A') }}"
                 @elseif($message->created_at->isToday())
                     data-bs-title="Today {{ $message->created_at->format('h:i A') }}"
                 @else
                     data-bs-title="{{ $message->created_at->format('l h:i A') }}"
                @endif
            >
                @if (count($message->attachment_full_url) >0)
                    @if(count($documentFiles) > 0)
                        <div
                            class="d-flex gap-3 justify-content-start align-items-center position-relative">
                            <div class="d-flex gap-2 flex-wrap justify-content-end">
                                @foreach ($documentFiles as $secondIndex => $attachment)
                                    @php($extension = strrchr($attachment['key'],'.'))
                                    @php($icon = in_array($extension,['.pdf','.doc','docx','.txt']) ? 'word-icon': 'default-icon')
                                    @php($downloadPath = $attachment['path'])
                                    <div class="d-flex gap-2">
                                        <a href="{{$downloadPath}}" target="_blank">
                                            <div class="uploaded-file-item">
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <img
                                                            src="{{theme_asset('assets/img/'.$icon.'.png')}}"
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
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if(count($otherFiles) > 0)
                        <div
                            class="zip-wrapper d-flex gap-3 justify-content-start align-items-center position-relative">
                            <?php
                                $isSingleDownload = count($otherFiles) === 1;
                                $singleFilePath = $isSingleDownload ? $otherFiles[0]['path'] : '';
                            ?>
                            <a download
                                href="{{ $isSingleDownload && $singleFilePath ? $singleFilePath  : 'javascript:'}}"
                                class="btn btn--download d-flex justify-content-center align-items-center flex-shrink-0 {{ count($otherFiles) > 1 ? 'zip-download' : '' }}">
                                <i class="bi bi-download"></i>
                            </a>
                            <div
                                class="d-flex gap-2 flex-wrap justify-content-end custom-image-popup-init align-items-center zip-images max-w-150px">

                                @foreach($otherFiles as $secondIndex => $attachment)
                                    @php($extension = strrchr($attachment['key'],'.'))
                                    <div
                                        class="position-relative img_row{{$secondIndex}} {{$secondIndex > 3 ? 'd-none' : ''}}">
                                        <a data-bs-toggle="modal"
                                           data-bs-target="#imgViewModal{{ $message->id }}"
                                           data-type="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'video' : 'image' }}"
                                           href="javascript:"
                                           download
                                           class="overflow-hidden d-block border rounded position-relative"
                                           data-index="{{ $secondIndex }}">
                                            @if(in_array($extension, GlobalConstant::VIDEO_EXTENSION))
                                                <video class="rounded video-element" width="100" height="60"
                                                       preload="metadata"
                                                >
                                                    <source src="{{ $attachment['path'] ?? '' }}" type="video/mp4">
                                                    <source src="{{ $attachment['path'] ?? '' }}" type="video/ogg">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <button type="button"
                                                        class="btn video-play-btn text--primary rounded-circle bg-white p-1 d-flex justify-content-center align-items-center">
                                                    <i class="bi bi-play-fill"></i>
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
                            <div class="modal fade imgViewModal" id="imgViewModal{{ $message->id }}" tabindex="-1"
                                 aria-labelledby="imgViewModal{{ $message->id }}Label" role="dialog"
                                 aria-modal="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content bg-transparent border-0">
                                        <div class="modal-body pt-0">
                                            <div class="imgView-slider owl-theme owl-carousel" dir="ltr">
                                                @foreach($otherFiles as $file)
                                                    @php($extension = strrchr($file['key'],'.'))
                                                    <div class="imgView-item">
                                                        <div
                                                            class="d-flex justify-content-between align-items-end">
                                                            <a href="{{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? ($file['path'] ?? '') : getStorageImages(path: $file, type: 'backend-basic') }}"
                                                               download
                                                               class="d-flex align-items-center gap-2 mb-2">
                                                                <div
                                                                    class="btn btn--download d-flex justify-content-center align-items-center"
                                                                >
                                                                <i class="bi bi-download"></i>
                                                                </div>
                                                                <h6 class="text-white text-underline mb-0">
                                                                    Download {{ in_array($extension, GlobalConstant::VIDEO_EXTENSION) ? 'Video' : 'Image' }}</h6>
                                                            </a>
                                                            <button type="button"
                                                                    class="btn btn-close p-1 border-0"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close">
                                                                <i class="bi bi-x-lg"></i>
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
                                                                        <img height="14"
                                                                             src="{{ theme_asset('assets/img/icons/carbon_play-filled.svg') }}"
                                                                             alt="Play">
                                                                    </button>
                                                                @else
                                                                    <div class="image-wrapper">
                                                                        <a
                                                                            href="{{ getStorageImages(path: $file, type: 'backend-basic') }}"
                                                                            download
                                                                            class="position-relative">
                                                                            <img class="image" alt=""
                                                                                 src="{{ getStorageImages(path: $file, type: 'backend-basic') }}">
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="imgView-slider_buttons d-flex justify-content-center"
                                                 dir="ltr">
                                                <button type="button" class="btn owl-btn imgView-owl-prev">
                                                    <i class="bi bi-chevron-left"></i>
                                                </button>
                                                <button type="button" class="btn owl-btn imgView-owl-next">
                                                    <i class="bi bi-chevron-right"></i>
                                                </button>
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
                        <p class="message_text d-inline-block">
                            {{$message->message}}
                        </p>
                    </div>
                @endif
            </div>
        @endif
    @endForeach
    <div id="down"></div>
@endif

@push('script')
    <script src="{{ theme_asset('assets/js/js-zip/jszip.min.js')}}"></script>
    <script src="{{ theme_asset('assets/js/js-zip/FileSaver.min.js')}}"></script>
@endpush
