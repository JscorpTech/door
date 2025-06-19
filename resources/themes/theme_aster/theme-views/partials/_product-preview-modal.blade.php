<div class="modal fade rtl text-align-direction" id="product-preview-modal" tabindex="-1" role="dialog" aria-labelledby="product-preview-modal"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered
    {{
    $previewFileInfo['mime_category'] == 'video' ||
    ($previewFileInfo['mime_category'] == 'document' && $previewFileInfo['mime_type'] == 'application/pdf') ? 'modal-xl' : ''
    }}" role="document">
        <div class="modal-content">
            <div class="modal-header {{
    $previewFileInfo['mime_category'] == 'video' ||
    ($previewFileInfo['mime_category'] == 'document' && $previewFileInfo['mime_type'] == 'application/pdf') ? '' : 'd-none'
    }}">
                <h6 class="modal-title text-truncate">{{ $product->name }}</h6>
                <button type="button" class="btn-close small" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div>
                    @if($previewFileInfo['mime_category'] == 'video')
                        <div class="embed-responsive embed-responsive-16by9">
                            <video class="embed-responsive-item" controls>
                                <source src="{{ $product->preview_file_full_url['path'] }}" type="video/mp4">
                            </video>
                        </div>
                    @elseif($previewFileInfo['mime_category'] == 'audio')
                        <div class="audio-player">
                            <audio class="w-100" controls>
                                <source src="{{ $product->preview_file_full_url['path'] }}" type="{{ $previewFileInfo['mime_type'] }}">
                            </audio>
                        </div>
                    @elseif($previewFileInfo['mime_category'] == 'image')
                        <div>
                            <img class="w-100" src="{{ $product->preview_file_full_url['path'] }}" alt="">
                        </div>
                    @elseif($previewFileInfo['mime_category'] == 'document' && $previewFileInfo['mime_type'] == 'application/pdf')
                        <div class="pdf-preview">
                            <iframe src="{{ $product->preview_file_full_url['path'] }}" width="100%" height="500px"></iframe>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2 align-items-center py-4">
                            <img src="{{ theme_asset(path: 'assets/img/icons/export.svg') }}" alt="" width="50">
                            <h4 class="mt-2">{{ translate('Download_Preview_File.') }}</h4>
                            <span class="text-muted text-sm">{{ $product->preview_file }}</span>
                            <span class="text-sm">{{ $previewFileInfo['sizeReadable'] }}</span>
                            <a class="btn btn-primary text-capitalize mt-3"
                               href="{{ $product->preview_file_full_url['path'] }}" download
                            >
                                {{ translate('Download_Now') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
