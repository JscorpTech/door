<?php

namespace Modules\Blog\app\Services\Frontend;

use DOMDocument;
use Illuminate\Support\Facades\App;
use Modules\Blog\app\Traits\BlogTrait;

class FrontendBlogService
{
    public function getCheckLocale(object|array $request): void
    {
        if ($request->has('locale') && !empty($request['locale'])) {
            foreach (getWebConfig('language') as $language) {
                if ($request['locale'] == getLanguageCode($language['code'])) {
                    session()->put('local', $language['code']);
                    App::setLocale(getLanguageCode(country_code: $language['code']));
                    if (array_key_exists('direction', $language)) {
                        session()->put('direction', $language['direction']);
                    }
                }
            }
        }
    }

    function getModifiedDescription(string|null $description): bool|string
    {
        if ($description) {
            $domDocument = new DOMDocument('1.0', 'UTF-8');
            libxml_use_internal_errors(true);
            $domDocument->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            foreach ($domDocument->getElementsByTagName('h2') as $index => $blogTag) {
                $blogTag->setAttribute('id', 'article-section-' . $index);
            }
            return $domDocument->saveHTML();
        }
        return $description;
    }

    function getModifiedDescriptionLinks($description): array
    {
        $blogLinks = [];
        if ($description) {
            $domDocument = new DOMDocument('1.0', 'UTF-8');
            libxml_use_internal_errors(true);
            $domDocument->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            foreach ($domDocument->getElementsByTagName('h2') as $index => $blogTag) {
                $blogLinks[] = [
                    'id' => 'article-section-' . $index,
                    'text' => $blogTag->textContent
                ];
            }
        }
        return $blogLinks;
    }


}
