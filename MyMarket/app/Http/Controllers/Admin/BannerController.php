<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\HeaderContent;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function display()
    {
        $banners = Banner::where('active', 1)->select('id', 'url', 'active')->get();

        $banners->map(function ($banner) {
            $media_desktop = $banner->getMedia('image-desktop')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });
            $media_mobile = $banner->getMedia('image-mobile')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });

            $banner->media_desktop = $media_desktop;
            $banner->media_mobile = $media_mobile;

            unset($banner->media);
        });

        return response()->json($banners);
    }
    public function admindisplay()
    {
        $banners = Banner::select('id', 'url', 'active')->get();

        $banners->map(function ($banner) {
            $media_desktop = $banner->getMedia('image-desktop')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });
            $media_mobile = $banner->getMedia('image-mobile')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });

            $banner->media_desktop = $media_desktop;
            $banner->media_mobile = $media_mobile;
            unset($banner->media);
        });

        return response()->json($banners);
    }

    public function create(Request $request)
    {
        $request->validate([
            'url' => 'string|nullable',
            'image-desktop' => 'required|image',
            'image-mobile' => 'required|image'

        ]);

        $banner = Banner::create([
            'url' => $request->url,
            'active' => 0
        ]);
        $banner->addMedia($request->file('image-desktop'))
            ->toMediaCollection('image-desktop');

        $banner->addMedia($request->file('image-mobile'))
            ->toMediaCollection('image-mobile');
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


    public function displayHeader()
    {
        $headercontents = HeaderContent::where('active', 1)->get();
        return response()->json($headercontents);
    }
    public function displayHeaderadmin()
    {
        $headercontents = HeaderContent::all();
        return response()->json($headercontents);
    }
    public function createHeader(Request $request)
    {
        $request->validate([
            'text' => "required|max:255"
        ]);
        $check = HeaderContent::create([
            'text' => $request->text
        ]);
        if ($check) {
            return response()->json('success', 200);
        }
        return response()->json('failed', 502);
    }
    public function activeHeader($id, $action)
    {

        $content = HeaderContent::findorfail($id);
        $content->update([
            'active' => $action === "active" ?  1 : 0
        ]);
        return response()->json('success');
    }
}
