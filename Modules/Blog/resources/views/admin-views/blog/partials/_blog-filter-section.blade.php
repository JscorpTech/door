<form action="{{ url()->current() }}" method="GET">
    <div class="d-block border-bottom pb-5 mb-5">
        <h3 class="mb-3">{{ translate('Filter_Blog') }}</h3>
        <div class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-4">
                <div class="form-group mb-0">
                    <label for="name" class="title-color">
                        {{ translate('Select_Category') }}
                    </label>
                    <select class="js-select2-custom form-control" name="category_id"
                            data-url-prefix=""
                            data-element-id=""
                            data-element-type="select">
                        <option value="" selected disabled>
                            {{ translate('select_category') }}
                        </option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{request('category_id')==$category->id ? 'selected' :''}}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="form-group mb-0">
                    <label for="name" class="title-color">
                        {{ translate('Publish_Date') }}
                    </label>
                    <div class="position-relative">
                        <span class="tio-calendar icon-absolute-on-right"></span>
                        <input type="text" name="publish_date"
                               class="js-daterangepicker-with-range form-control cursor-pointer"
                               value="{{request('publish_date')}}"
                               placeholder="{{ translate('Select_Date') }}" autocomplete="off"
                               readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.blog.view') }}"
                       class="btn btn-secondary font-weight-semibold px-5">
                        {{ translate('reset') }}
                    </a>
                    <button type="submit" class="btn btn--primary font-weight-semibold px-6">
                        {{ translate('filter') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
