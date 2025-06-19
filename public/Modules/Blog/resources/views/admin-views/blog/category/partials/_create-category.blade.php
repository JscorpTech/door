<div class="category-create-form">
    <div class="card shadow-sm">
        <div class="card-header shadow-none">
            <h5 class="m-0">{{ translate('Add_New_Category') }}</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs w-fit-content border-0 mb-1">
                @foreach($languages as $lang)
                    <li class="nav-item text-capitalize {{$lang == $defaultLanguage ? 'active':''}}">
                        <a class="nav-link lang-link form-dynamic-language-tab {{$lang == $defaultLanguage ? 'active':''}}"
                           href="javascript:"
                           data-lang="{{ $lang }}"
                           data-common="category-lang-tab"
                           data-target-selector=".category-lang-{{ $lang }}-tab"
                        >{{getLanguageName($lang).'('.strtoupper($lang).')'}}</a>
                    </li>
                @endforeach
            </ul>
            <form action="{{ route('admin.blog.category.add') }}" method="POST" class="category-form-submit" id="blog-category-add-form">
                @csrf
                <div class="bg--primary--light p-4 mb-3">
                    <div class="category-section">
                        @foreach($languages as $lang)
                        <div class="{{$lang != $defaultLanguage ? 'd-none':''}} category-lang-tab category-lang-{{ $lang }}-tab" data-lang="{{ $lang }}">
                            <div class="form-group">
                                <label class="title-color category-label">{{ translate('Category_Name') }} ({{strtoupper($lang)}})</label>
                                <input type="text" name="name[{{$lang}}]" class="form-control category_name" id="{{$lang}}_category_name" placeholder="{{translate('ex').':'.translate('LUX')}}">
                            </div>
                        </div>
                        <input type="hidden" name="lang[{{$lang}}]" value="{{$lang}}" id="lang-{{$lang}}">
                        @endforeach
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-end">
                    <button type="reset" id="reset" class="btn btn-secondary font-weight-semibold px-4">
                        {{ translate('Reset') }}
                    </button>
                    <button class="btn btn--primary font-weight-semibold px-4 category-form-submit-btn"
                            data-type="add"
                            data-form="#blog-category-add-form" data-route="{{ route('admin.blog.category.add') }}"
                    >
                        {{ translate('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
