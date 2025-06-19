<?php

namespace Modules\Blog\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Traits\BlogResponseHandlerTrait;

/**
 * @property string $about_us
 */
class BlogCategoryUpdateRequest extends FormRequest
{
    use BlogResponseHandlerTrait;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->merge([
            'lang' => array_combine(array_keys($this['lang']), array_keys($this['lang']))
        ]);

        $rules = [];
        foreach (array_keys($this['lang']) as $locale) {
            $rules["name.$locale"] = $locale == 'en' ? 'required|string|max:255' : 'nullable|string|max:255';
        }

        $rules += [
            'name' => 'required|array',
        ];
        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'name.required' => translate('The_name_field_is_required'),
            'name.string' => translate('The_name_must_be_a_string'),
            'name.max' => translate('The_name_may_not_be_greater_than_255_characters'),
        ];

        foreach (array_keys($this['lang']) as $locale) {
            $languageName = $this->getLanguageName(code: $locale);
            if ($locale == 'en') {
                $messages["name.$locale.required"] = translate("The_name_in_{$languageName}_is_required");
            }
            $messages["name.$locale.string"] = translate("The_name_in_{$languageName}_must_be_a_string");
            $messages["name.$locale.max"] = translate("The_name_in_{$languageName}_may_not_be_greater_than_255_characters");
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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (BlogCategory::where('name', $this['name']['en'])->where('id', '!=', $this['id'])->exists()) {
                    $validator->errors()->add(
                        'name', translate('The_name_must_be_unique')
                    );
                }
            }
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
