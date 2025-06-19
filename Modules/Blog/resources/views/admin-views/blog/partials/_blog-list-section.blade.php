<div class="table-responsive datatable-custom">
    <table
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
        <thead class="thead-light thead-50 text-capitalize">
        <tr>
            <th>{{ translate('SL') }}</th>
            <th>{{ translate('Id') }}</th>
            <th>{{ translate('category') }}</th>
            <th>{{ translate('title') }}</th>
            <th>{{ translate('writer') }}</th>
            <th>{{ translate('publish_date') }}</th>
            <th>
                <div class="d-flex justify-content-center gap-1">
                    <span>{{ translate('Status') }}</span>
                    <span class="trx-y-2" data-toggle="tooltip" data-placement="top" title=""
                          data-original-title="{{ translate('when_the_status_is_off_the_blog_is_saved_as_a_draft_and_will_not_be_displayed_on_the_website_setting_it_to_on_will_publish_it_to_the_website.') }}">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                             alt="Image">
                    </span>
                </div>
            </th>
            <th class="text-center">{{ translate('Action') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($blogs as $key => $blog)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>#{{ $blog?->readable_id }}</td>
                <td class="text-truncate max-w-165px">{{ $blog?->category?->name ?? 'N/A' }}</td>
                <td>
                    <div class="w-250 text-wrap line--limit-1">
                        {{ $blog->title }}
                    </div>
                </td>
                <td class="max-w-215px overflow-hidden text-truncate"> {{ $blog?->writer ?? 'N/a' }}</td>
                <td>{{ $blog->is_published ? date('d M, y', strtotime($blog->publish_date)) : 'N/A' }}</td>
                <td>
                    <div class="d-flex justify-content-center">
                        <form action="{{ route('admin.blog.blog-status-update', $blog->id) }}"
                              method="post" id="blog-list-status{{$blog->id}}-form"
                              data-id="blog-list-status-form">
                            @csrf
                            <label class="switcher mx-auto">
                                <input type="hidden" name="product_id" value="">
                                <input type="checkbox" class="switcher_input toggle-switch-message"
                                       value="1"
                                       {{$blog['status'] == 1 ? 'checked' : ''}}
                                       {{ $blog->is_published ? '' : 'disabled' }}
                                       id="blog-list-status{{$blog->id}}" name="status"
                                       data-modal-id="toggle-status-custom-modal"
                                       data-toggle-id="blog-list-status{{$blog->id}}"
                                       data-on-image="blog-status-on.png"
                                       data-off-image="blog-status-off.png"
                                       data-on-title="{{ translate('are_you_sure_to_turn_on_the_blog_status') }}"
                                       data-off-title="{{ translate('are_you_sure_to_turn_off_the_blog_status') }}"
                                       data-on-message="<p>{{ translate('once_you_turn_on_this_blog_it_will_be_visible_to_the_blog_list_for_users.') }}</p>"
                                       data-off-message="<p>{{ translate('when_you_turn_off_this_blog_it_will_not_be_visible_to_the_blog_list_for_users') }}</p>"
                                       data-on-button-text="{{ translate('turn_on') }}"
                                       data-off-button-text="{{ translate('turn_off') }}">
                                @if($blog->is_published)
                                    <span class="switcher_control"></span>
                                @else
                                    <span class="switcher_control" data-toggle="tooltip"
                                          title="{{ translate('This_blog_is_not_published_yet_Status_change_option_is_disabled') }}"
                                    ></span>
                                @endif
                            </label>
                        </form>
                    </div>
                </td>
                <td>
                    <div class="d-flex justify-content-center">
                        @if($blog->is_draft == 1)
                            <a class="btn btn-outline-success square-btn btn-sm mr-1"
                               href="{{ route('admin.blog.draft-edit', $blog->id)  }}">
                                <i class="tio-receipt-outlined"></i>
                            </a>
                        @endif
                        @if($blog->is_published)
                            <a class="btn btn-outline--primary square-btn btn-sm mr-1"
                               href="{{ route('admin.blog.edit', ['id' => $blog->id])  }}">
                                <i class="tio-edit"></i>
                            </a>
                        @else
                            <span class="btn btn-outline--primary square-btn btn-sm mr-1 disabled cursor-pointer"
                                data-toggle="tooltip"
                                title="{{ translate('This blog is not published yet. Edit option is disabled.') }}">
                               <i class="tio-edit"></i>
                            </span>
                        @endif
                        <a class="btn btn-outline-danger square-btn btn-sm mr-1 delete-data-without-form"
                           data-id="{{ $blog->id }}" data-action="{{ route('admin.blog.delete')  }}">
                            <i class="tio-delete"></i>
                        </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive mt-4">
    <div class="px-4 d-flex justify-content-lg-end">
        {{ $blogs->links() }}
    </div>
</div>
