<?php

namespace App\Http\Requests\Web;

use App\Enums\GlobalConstant;
use App\Traits\ResponseHandler;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @property string $media
 */
class ChattingRequest extends FormRequest
{
    use ResponseHandler;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uploadMaxFileSize = str_replace('M','', ini_get('upload_max_filesize'));
        $maximumUploadSize = $uploadMaxFileSize * 1024 > 2048 ? ($uploadMaxFileSize * 1024) : 2048;
        $maximumUploadSize = $maximumUploadSize > (10 * 1024) ? (10 * 1024) : $maximumUploadSize;

        return  [
            'message' => 'required_without_all:file,media',
            'media.*' => 'max:'.$maximumUploadSize.'|mimes:' . str_replace('.', '', implode(',', GlobalConstant::MEDIA_EXTENSION)),
            'file.*' => 'file|max:2048|mimes:' . str_replace('.', '', implode(',', GlobalConstant::DOCUMENT_EXTENSION)),
        ];
    }

    public function messages(): array
    {
        $uploadMaxFileSize = str_replace('M','', ini_get('upload_max_filesize'));
        $maximumUploadSize = $uploadMaxFileSize * 1024 > 2048 ? ($uploadMaxFileSize * 1024) : 2048;
        $maximumUploadSize = $maximumUploadSize > (10 * 1024) ? (10 * 1024) : $maximumUploadSize;

        return [
            'required_without_all' => translate('type_something') . '!',
            'media.mimes' => translate('the_media_format_is_not_supported') . ' ' . translate('supported_format_are') . ' ' . str_replace('.', '', implode(',', GlobalConstant::MEDIA_EXTENSION)),
            'media.max' => translate('media_maximum_size') .' '.($maximumUploadSize / 1024).' MB',
            'file.mimes' => translate('the_file_format_is_not_supported') . ' ' . translate('supported_format_are') . ' ' . str_replace('.', '', implode(',', GlobalConstant::DOCUMENT_EXTENSION)),
            'file.max' => translate('file_maximum_size_') . MAXIMUM_MEDIA_UPLOAD_SIZE,
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
