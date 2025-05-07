<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function display()
    {
        $banners = Banner::where('active', 1)->select('id', 'url', 'active')->get();

        $banners->map(function ($banner) {
            $media = $banner->getMedia('banner')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });

            $banner->media_url = $media;
            unset($banner->media);
        });

        return response()->json($banners);
    }
    public function admindisplay()
    {
        $banners = Banner::select('id', 'url', 'active')->get();

        $banners->map(function ($banner) {
            $media = $banner->getMedia('banner')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });
            $banner->media_url = $media;
            unset($banner->media);
        });

        return response()->json($banners);
    }

    public function create(Request $request)
    {
        $request->validate([
            'url' => 'string|nullable',
            'image' => 'required'
        ]);

        $banner = Banner::create([
            'url' => $request->url,
            'active' => 0
        ]);
        $banner->addMedia($request->file('image'))
            ->toMediaCollection('banner');
        if ($banner) {
            return response()->json('success');
        }
        return response()->json('failed');
    }
    public function delete($id)
    {
        $banner = Banner::findOrFail($id);

        $media = $banner->getFirstMedia('banner');
        if ($media) {
            $media->delete();
        }
        $deleted = $banner->delete();
        if ($deleted) {
            return response()->json('success');
        }
        return response()->json('failed');
    }
    public function notactive($id)
    {
        $banner = Banner::findOrFail($id);

        $banner->active = false;
        $banner->save();

        return response()->json(['message' => 'Banner is now inactive'], 200);
    }

    public function active($id)
    {
        $banner = Banner::findOrFail($id);

        $banner->active = true;
        $banner->save();

        return response()->json(['message' => 'Banner is now active'], 200);
    }
}
