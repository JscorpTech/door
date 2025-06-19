<div class="card">
    <div class="table-responsive height-30vh">
        <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
            <thead class="thead-light thead-50 text-capitalize">
            <tr>
                <th>{{ translate('SL') }}</th>
                <th>{{ translate('Category') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Action') }}</th>
            </tr>
            </thead>
            <tbody>
            @if($categories->count() > 0)
                @foreach($categories as $key => $category)
                    <tr>
                        <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $key + 1 }}</td>
                        <td class="d-block max-w-200 overflow-hidden text-truncate" title="{{ $category->name }}">
                            {{ $category->name }}
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('admin.blog.category.status-update') }}"
                                      method="get" id="blog-list-status{{$category->id}}-form">
                                    @csrf
                                    <label class="switcher mx-auto">
                                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                                        <input type="checkbox" class="switcher_input toggle-switch-message" value="1"
                                               {{ $category->status == 1 ? 'checked' : '' }}
                                               id="blog-list-status{{ $category->id }}" name="status"
                                               data-modal-id="toggle-status-custom-modal"
                                               data-toggle-id="blog-list-status{{ $category->id }}"
                                               data-on-image="blog-status-on.png"
                                               data-off-image="blog-status-off.png"
                                               data-on-title="{{ translate('are_you_sure_to_turn_on_the_category_status') }}"
                                               data-off-title="{{ translate('are_you_sure_to_turn_off_the_category') }}"
                                               data-on-message="<p>{{ translate('when_you_turn_on_this_category_it_can_not_be_Accessed_and_visible_for_selection.') }}</p>"
                                               data-off-message="<p>{{ translate('once_you_turn_off_it_will_not_be_accessed_when_selecting_the_category') }}</p>"
                                               data-on-button-text="{{ translate('turn_on') }}"
                                               data-off-button-text="{{ translate('turn_off') }}">
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a class="btn btn-outline--primary btn-sm edit-category-btn"
                                   data-route="{{ route('admin.blog.category.info') }}"
                                   data-id="{{ $category->id }}">
                                    <i class="tio-edit"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-sm delete-category"
                                   data-route="{{ route('admin.blog.category.delete') }}"
                                   data-id="{{ $category->id }}">
                                    <i class="tio-delete"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">
                        <div class="text-center p-4">
                            <img class="mb-3" src="{{ dynamicAsset('public/assets/back-end/img/empty-blog.png') }}"
                                 alt="{{ translate('empty_blog') }}" width="64">
                            <p class="text-muted">
                                {{ translate('There_are_currently_no_blogs_category_available') }}
                            </p>
                        </div>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    @if($categories->count() > 0)
        <div class="card-footer border-0">
            <div class="d-flex justify-content-end">
                {{ $categories->appends(request()->except('page'))->render() }}
            </div>
        </div>
    @endif
</div>
