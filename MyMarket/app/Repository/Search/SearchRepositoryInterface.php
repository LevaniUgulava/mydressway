<?php

namespace App\Repository\Search;



interface SearchRepositoryInterface
{
    public function getSearchHistory(int $userId);
    public function setSearchHistory(int $userId, string $searchQuery);
}
