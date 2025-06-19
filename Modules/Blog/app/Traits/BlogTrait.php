<?php

namespace Modules\Blog\app\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Modules\Blog\app\Models\Blog;

trait BlogTrait
{
    public function getBlogReadableId(): int
    {
        try {
            $autoIncrement = DB::table('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'blogs')
                ->value('AUTO_INCREMENT');

            return ($autoIncrement + 100000) ?? 100001;
        } catch (\Throwable $th) {
            $lastBlog = Blog::latest('id')->first();
            if ($lastBlog) {
                return $lastBlog->id + 100001;
            }
            return 100001;
        }
    }

    public function getPriorityWiseBlogQuery($query, $dataLimit = 'all', $offset = null, $appends = null)
    {
        $blogPriority = getWebConfig(name: 'blog_list_priority');
        if ($blogPriority && ($blogPriority['custom_sorting_status'] == 1)) {
            $query = $query->get();

            if ($blogPriority['sort_by'] == 'most_clicked') {
                $query = $query->sortByDesc('click_count');
            }  elseif ($blogPriority['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($blogPriority['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('title', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator($results, $totalSize, $dataLimit, $currentPage, [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => request()->all(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset))->appends(request()->all());
        }

        return $query->get();
    }

}
