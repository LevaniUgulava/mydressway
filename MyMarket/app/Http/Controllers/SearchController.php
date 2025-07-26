<?php

namespace App\Http\Controllers;

use App\Repository\Search\SearchRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//in product Route
class SearchController extends Controller
{
    protected $searchRepository;

    public function __construct(SearchRepositoryInterface $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    public function getSearcHistory()
    {
        $user = Auth::user();
        $history = $this->searchRepository->getSearchHistory($user->id);
        return response()->json($history);
    }
    public function setSearchHistory(Request $request)
    {
        $user = Auth::user();
        $this->searchRepository->setSearchHistory($user->id, $request->term);
    }
}
