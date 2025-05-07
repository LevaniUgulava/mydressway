<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Brand\BrandRepositoryInterface;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $BrandRepository;

    public function __construct(BrandRepositoryInterface $BrandRepository)
    {
        $this->BrandRepository = $BrandRepository;
    }

    public function display()
    {
        $brands = $this->BrandRepository->display();
        return response()->json($brands);
    }

    public function admindisplay()
    {
        $brands = $this->BrandRepository->admindisplay();
        return response()->json($brands);
    }
    public function displaybyid($id)
    {
        $brand = $this->BrandRepository->displaybyid($id);
        return response()->json($brand);
    }

    public function create(Request $request)
    {
        $request->validate([
            "name" => "required|max:255",
            "image" => "required|image|mimes:jpeg,png,jpg,gif|max:2048"
        ]);

        $brands = $this->BrandRepository->create($request);
        return response()->json($brands);
    }

    public function update($id, Request $request)
    {
        $result = $this->BrandRepository->update($id, $request);
        return response()->json($result);
    }
    public function delete($id)
    {
        $result = $this->BrandRepository->delete($id);
        return response()->json($result);
    }
    public function notactive($id)
    {
        $result = $this->BrandRepository->notactive($id);
        return response()->json($result);
    }
    public function active($id)
    {
        $result = $this->BrandRepository->active($id);
        return response()->json($result);
    }
}
