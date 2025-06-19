<div class="category-edit-form d--none">
    <div class="card shadow-sm">
        <div class="card-header shadow-none">
            <h5 class="m-0">{{ translate('Update_Category') }}</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs w-fit-content border-0 mb-1">
                @foreach($languages as $lang)
                    <li class="nav-item text-capitalize">
                        <a class="nav-link lang-link form-dynamic-language-tab {{$lang == $defaultLanguage ? 'active':''}}"
                           href="javascript:"
                           data-lang="{{ $lang }}"
                           data-common="edit-category-lang"
                           data-target-selector=".edit-category-lang-{{ $lang }}"
                           id="edit-{{$lang}}-link">
                            {{getLanguageName($lang).'('.strtoupper($lang).')'}}
                        </a>
                    </li>
                @endforeach
            </ul>
            <form action="{{ route('admin.blog.category.update') }}" method="POST" class="category-form-submit" id="blog-category-update-form">
                @csrf
                <div class="bg--primary--light p-4 mb-3">
                    <div class="category-section">
                        @foreach($languages as $lang)
                            <div class="{{$lang != $defaultLanguage ? 'd-none':''}} edit-category-lang edit-category-lang-{{ $lang }}" data-lang="{{ $lang }}">
                                <div class="form-group">
                                    <label class="title-color category-label">{{ translate('Category_Name') }} ({{strtoupper($lang)}})</label>
                                    <input type="text" name="name[{{$lang}}]" class="form-control category_name" id="edit-{{$lang}}_category_name" placeholder="{{translate('ex').':'.translate('LUX')}}" {{$lang == $defaultLanguage ? 'required':''}}>
                                </div>
                            </div>
                            <input type="hidden" name="lang[{{$lang}}]" value="{{$lang}}" id="edit-lang-{{$lang}}">
                        @endforeach
                    </div>
                </div>

                <input type="hidden" name="id" value="" id="edit-category-id">
                <div class="d-flex flex-wrap gap-3 justify-content-end">
                    <button type="reset" id="category-form-cancel-btn" class="btn btn-secondary font-weight-semibold px-4">
                        {{ translate('Cancel') }}
                    </button>
                    <button class="btn btn--primary font-weight-semibold px-4 category-form-submit-btn"
                            data-type="update"
                            data-form="#blog-category-update-form" data-route="{{ route('admin.blog.category.update') }}"
                    >
                        {{ translate('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

