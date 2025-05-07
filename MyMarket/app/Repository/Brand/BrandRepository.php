<?php

namespace App\Repository\Brand;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

class BrandRepository implements BrandRepositoryInterface
{
    public function display()
    {
        $brands = Brand::where('active', 1)->select("id", 'name', "active")->get();
        $brands->map(function ($brand) {
            $media = $brand->getMedia('brand')->first();
            $media_url = url('storage/' . $media->id . '/' . $media->file_name);
            $brand->media_url = $media_url;
            unset($brand->media);
        });
        return $brands;
    }
    public function admindisplay()
    {
        $brands = Brand::select("id", 'name', "active")->get();
        $brands->map(function ($brand) {
            $media = $brand->getMedia('brand')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });

            $brand->media_url = $media;
            unset($brand->media);
        });
        return $brands;
    }
    public function create(Request $request)
    {
        $brand = Brand::create([
            "name" => $request->name,
        ]);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $brand->addMedia($request->file('image'))->toMediaCollection('brand');
        }

        return [
            'success' => true,
            'message' => 'Brand successfully created',
            'brand' => $brand
        ];
    }

    public function ProductRelation($product_id, $brand_id)
    {

        $product = Product::findOrFail($product_id);

        $product->brands()->attach($brand_id);

        return [
            'success' => true,
            'message' => 'Brands successfully attached'
        ];
    }
    public function displaybyid($id)
    {
        $brand = Brand::findOrFail($id);
        $media = $brand->getMedia('brand')->first();

        if ($media) {
            $media_url = asset('storage/' . $media->id . '/' . $media->file_name);
            $brand->media_url = $media_url;
        } else {
            $brand->media_url = null;
        }

        return $brand;
    }

    public function update($id, $request)
    {
        $brand = Brand::findOrFail($id);

        $brand->update([
            "name" => $request->name,
        ]);

        if ($request->hasFile('image')) {
            $brand->addMedia($request->file('image'))->toMediaCollection('brand');
        }

        return [
            'success' => true,
            'message' => 'Brand successfully updated',
            'brand' => $brand
        ];
    }

    public function delete($id)
    {
        $brand = Brand::findorfail($id);
        $media = $brand->getfirstmedia();
        if ($media) {
            $media->delete();
        }
        $deleted = $brand->delete();
        if ($deleted) {
            return ["success" => true];
        }

        return ["success" => false];
    }
    public function notactive($id)
    {
        $Brand = Brand::findOrFail($id);

        $Brand->active = false;
        $Brand->save();

        return ['message' => 'Banner is now inactive'];
    }

    public function active($id)
    {
        $Brand = Brand::findOrFail($id);

        $Brand->active = true;
        $Brand->save();

        return ['message' => 'Brand is now active'];
    }
}
