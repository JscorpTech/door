<?php

namespace Modules\Blog\app\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Modules\Blog\app\Models\BlogTranslation;

trait BlogCategoryTrait
{
    public function getPriorityWiseBlogCategoryQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $categoryPriority = getWebConfig(name: 'blog_category_list_priority');
        if ($categoryPriority && ($categoryPriority['custom_sorting_status'] == 1)) {
            $query = $query->get();

            if ($categoryPriority['sort_by'] == 'most_clicked') {
                $query = $query->sortByDesc('click_count');
            } elseif ($categoryPriority['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($categoryPriority['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator($results, $totalSize, $dataLimit, $currentPage, [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        $query = $query->orderBy('id', 'desc');
        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->get();
    }

    public function addBlogCategoryTranslation(object $request, int|string $id): bool
    {
        foreach ($request->lang as $index => $key) {
            $type = 'name';
            if (isset($request[$type][$index]) && $key != 'en') {
                BlogTranslation::insert([
                    'translation_type' => 'Modules\Blog\app\Models\BlogCategory',
                    'translation_id' => $id,
                    'locale' => $key,
                    'key' => $type,
                    'value' => $request[$type][$index],
                ]);
            }
        }
        return true;
    }

    public function updateBlogCategoryTranslation(object $request, int|string $id): bool
    {
        foreach ($request->lang as $index => $key) {
            $type = 'name';
            if (isset($request[$type][$index]) && $key != 'en') {
                BlogTranslation::updateOrInsert(
                    [
                        'translation_type' => 'Modules\Blog\app\Models\BlogCategory',
                        'translation_id' => $id,
                        'locale' => $key,
                        'key' => $type,
                    ],
                    [
                        'value' => $request[$type][$index],
                        'translation_type' => 'Modules\Blog\app\Models\BlogCategory',
                        'translation_id' => $id,
                        'locale' => $key,
                        'key' => $type,
                    ]
                );
            }
        }
        return true;
    }
}
