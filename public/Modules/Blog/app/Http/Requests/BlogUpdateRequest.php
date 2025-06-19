<?php

namespace Modules\Blog\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Blog\app\Traits\BlogResponseHandlerTrait;

/**
 * @property string $about_us
 */
class BlogUpdateRequest extends FormRequest
{
    use BlogResponseHandlerTrait;

    protected $stopOnFirstFailure = false;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];
        foreach (array_keys($this['lang']) as $locale) {
            $rules["title.$locale"] = $locale == 'en' ? 'required|string|max:255' : 'nullable|string|max:255';
            $rules["description.$locale"] = $locale == 'en' ? 'required|string' : 'nullable|string';
        }

        $rules += [
            'description' => 'required|array',
            'image' => 'mimes:jpeg,jpg,png,gif,webp|max:10000',
            'writer' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:blog_categories,id',
            'publish_date' => 'nullable|date',
        ];
        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'title.required' => translate('The_title_field_is_required'),
            'title.string' => translate('The_title_must_be_a_string'),
            'title.max' => translate('The_title_may_not_be_greater_than_255_characters'),
            'description.required' => translate('The_description_field_is_required'),
            'description.array' => translate('The_description_must_be_an_array'),
            'image.mimes' => translate('The_image_must_be_a_file_of_type_jpeg,jpg,png,gif,webp'),
            'image.max' => translate('The_image_may_not_be_greater_than_10000_kilobytes'),
            'writer.string' => translate('The_writer_must_be_a_string'),
            'writer.max' => translate('The_writer_may_not_be_greater_than_255_characters'),
            'category_id.integer' => translate('The_category_id_must_be_an_integer'),
            'category_id.exists' => translate('The_selected_category_id_is_invalid'),
            'publish_date.date' => translate('The_publish_date_is_not_a_valid_date'),
        ];

        foreach (array_keys($this['lang']) as $locale) {
            $languageName = $this->getLanguageName(code: $locale);
            if ($locale == 'en') {
                $messages["title.$locale.required"] = translate("The_title_in_{$languageName}_is_required");
                $messages["description.$locale.required"] = translate("The_description_in_{$languageName}_is_required");
            }
            $messages["title.$locale.string"] = translate("The_title_in_{$languageName}_must_be_a_string");
            $messages["title.$locale.max"] = translate("The_title_in_{$languageName}_may_not_be_greater_than_255_characters");
            $messages["description.$locale.string"] = translate("The_description_in_{$languageName}_must_be_a_string");
        }
        return $messages;
    }

    public function getLanguageName($code): mixed
    {
        $name = 'english';
        foreach (getWebConfig('language') as $language) {
            if ($language['code'] == $code) {
                $name = $language['name'];
            }
        }
        return $name;
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
