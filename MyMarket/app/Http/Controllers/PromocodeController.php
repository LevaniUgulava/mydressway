<?php

namespace App\Http\Controllers;

use App\Models\Promocode;
use App\Repository\Promocode\PromocodeRepositoryInterface;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
    protected $promocodeRepository;
    public function __construct(PromocodeRepositoryInterface $promocodeRepository)
    {
        $this->promocodeRepository = $promocodeRepository;
    }

    public function display()
    {
        $promocodes = $this->promocodeRepository->display();
        return response()->json($promocodes);
    }
    public function displayByid($id)
    {
        $promocode = $this->promocodeRepository->getbyid($id);
        return response()->json($promocode);
    }
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:promocodes,name',
            'type' => 'nullable|string',
            'usage_quantity' => 'nullable|integer',
            'expires_at' => 'nullable|date',
            'discount_percentage' => 'nullable|numeric',
            'fixed_discount' => 'nullable|numeric',
        ]);
        $this->promocodeRepository->create($validatedData);
    }

    public function createRelation(Request $request)
    {
        $promocode = $this->promocodeRepository->getbyid($request->promocodeid);
        $promocode->categories()->attach($request->categoryids);
    }
}
