<?php

namespace App\Helpers;


class ProductHelper
{

    public function transform($products, $user, $lang = null, $size = true)
    {
        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $products->transform(function ($product) use ($likedProductIds, $lang, $user, $size) {
            $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
            $product->isLiked = in_array($product->id, $likedProductIds);

            if ($user && $user->userstatus_id) {
                $isEligible =  $product->eligibleStatuses()->wherePivot('userstatus_id', $user->userstatus->id)->withPivot('discount')->first();
                if ($isEligible && $user->userstatus->isActive($user)) {
                    $product->discountstatus = [
                        'discount' => $isEligible->pivot->discount
                    ];
                    $product->discountprice = $user->getPriceByStatus($product, $product->price, $product->price);
                } else {
                    $product->discountprice = $product->discountprice;
                }
            } else {
                $product->discountprice = $product->discountprice;
            }

            if ($size) {
                if ($product->shoesize->isNotEmpty()) {
                    $product->size_type = "shoesize";
                } elseif ($product->clothsize->isNotEmpty()) {
                    $product->size_type = "clothsize";
                }
            }
            unset($product->media);

            return $product;
        });
    }
    public function transformSingleProduct($product, $user, $lang = null, $size = true)
    {
        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
        $product->isLiked = in_array($product->id, $likedProductIds);

        if ($user && $user->userstatus_id) {
            $isEligible = $product->eligibleStatuses()
                ->wherePivot('userstatus_id', $user->userstatus->id)
                ->withPivot('discount')
                ->first();

            if ($isEligible && $user->userstatus->isActive($user)) {
                $product->discountstatus = [
                    'discount' => $isEligible->pivot->discount
                ];
                $product->discountprice = $user->getPriceByStatus($product, $product->price, $product->price);
            } else {
                $product->discountprice = $product->discountprice;
            }
        } else {
            $product->discountprice = $product->discountprice;
        }

        if ($size) {
            if (!empty($product->shoesize) && $product->shoesize->isNotEmpty()) {
                $product->size_type = "shoesize";
            } elseif (!empty($product->clothsize) && $product->clothsize->isNotEmpty()) {
                $product->size_type = "clothsize";
            }
        }
        $product->MainCategory = [
            'id' => optional($product->MainCategory)->id,
            'ka_name' => optional($product->MainCategory)->ka_name,
            'en_name' => optional($product->MainCategory)->en_name

        ];
        $product->Category = [
            'id' => optional($product->Category)->id,
            'ka_name' => optional($product->Category)->ka_name,
            'en_name' => optional($product->Category)->en_name

        ];
        $product->Subcategory = [
            'id' => optional($product->Subcategory)->id,
            'ka_name' => optional($product->Subcategory)->ka_name,
            'en_name' => optional($product->Subcategory)->en_name

        ];


        unset($product->media);

        return $product;
    }
}
