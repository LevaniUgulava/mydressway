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
}
