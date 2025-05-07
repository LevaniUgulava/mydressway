<?php

namespace App\Repository\Search;

use App\Models\Product;
use App\Models\SearchHistory;
use App\Models\Subcategory;

class SearchRepository implements SearchRepositoryInterface
{
    public function getSearchHistory(int $userId)
    {
        $searchHistory = SearchHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('term')
            ->values()
            ->take(6);
        return $searchHistory;
    }
    public function setSearchHistory(int $userId, string $searchQuery)
    {
        SearchHistory::where('user_id', $userId)
            ->where('term', $searchQuery)
            ->delete();

        SearchHistory::create([
            'user_id' => $userId,
            'term' => $searchQuery
        ]);

        $history = SearchHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('term')
            ->values();

        if ($history->count() > 6) {
            $toDelete = $history->last();
            SearchHistory::where('id', $toDelete->id)->delete();
        }
    }
    public function getPopularSearches()
    {
        $popular = SearchHistory::groupBy('term')
            ->selectRaw('term, count(*) as count')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        return $popular;
    }
    public function getwithCategories(string $searchQuery)
    {
        $subcategories = Subcategory::withCount(['Products' => function ($query) use ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->limit(2)
            ->get();


        return $subcategories;
    }
}
