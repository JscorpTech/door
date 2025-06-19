<div class="card mb-20">
    <form action="{{ route('admin.blog.intro') }}" method="post" id="blog-custom-status-form"
          data-id="blog-custom-status-form">
        @csrf
        <div class="card-body">
            <h3 class="mb-3">{{ translate('Intro_Section') }}</h3>
            <ul class="nav nav-tabs w-fit-content mb-4">
                @foreach($languages as $lang)
                    <li class="nav-item text-capitalize {{$lang == $defaultLanguage ? 'active':''}}">
                        <a class="nav-link lang-link form-system-language-tab {{$lang == $defaultLanguage ? 'active':''}}"
                           href="javascript:"
                           id="{{$lang}}-link">{{getLanguageName($lang).'('.strtoupper($lang).')'}}</a>
                    </li>
                @endforeach
            </ul>
            <div>
                @php
                    $titleData = getWebConfig(name: 'blog_feature_title') ?? [];
                    $subTitleData = getWebConfig(name: 'blog_feature_sub_title') ?? [];
                @endphp

                @foreach($languages as $lang)
                    <div class="{{$lang != $defaultLanguage ? 'd-none':''}} form-system-language-form"
                         id="{{$lang}}-form">
                        <div class="form-group">
                            <label class="title-color" for="en_name">
                                {{ translate('title') }}({{strtoupper($lang)}})
                                <span class="input-required-icon">{{ $lang == 'en' ? '*' : '' }}</span>
                            </label>
                            <input type="text" value="{{ $titleData[$lang] ?? '' }}" name="title[{{$lang}}]"
                                   id="" class="form-control"
                                   placeholder="{{ translate('Enter_title') }}" {{$lang == $defaultLanguage ? 'required':''}}>
                        </div>
                        <div class="form-group">
                            <label class="title-color" for="">
                                {{ translate('sub_title') }}({{strtoupper($lang)}})
                            </label>
                            <textarea name="sub_title[{{$lang}}]" id="" class="form-control h-90px"
                                      placeholder="{{ translate('Enter_sub_title') }}">{{ $subTitleData[$lang] ?? '' }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-end gap-3 mt-4">
                <div class="d-flex gap-2">
                    <button type="reset" class="btn btn-secondary font-weight-semibold px-5">
                        {{ translate('reset') }}
                    </button>
                    <button type="submit" class="btn btn--primary font-weight-semibold px-5">
                        @if($titleData || $subTitleData)
                            {{ translate('update') }}
                        @else
                            {{ translate('save') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
