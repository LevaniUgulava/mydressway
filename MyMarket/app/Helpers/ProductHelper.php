<?php

namespace App\Helpers;

use Carbon\Carbon;

class ProductHelper
{

    public function transform($products, $user, $lang = null, $size = true)
    {
        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $cartItems = $user ? $user->cart?->products->map(function ($product) {
            return [
                'product_id' => $product->pivot->product_id,
                'quantity' => $product->pivot->quantity
            ];
        }) : [];


        $userStatus = optional(optional($user)->userstatusinfo)->userstatus;

        $products->transform(function ($product) use ($likedProductIds, $lang, $user, $userStatus, $size, $cartItems) {
            $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
            $product->isLiked = in_array($product->id, $likedProductIds);
            $productInCart = collect($cartItems)->firstWhere('product_id', $product->id);
            $product->inCartQuantity = $productInCart ? $productInCart['quantity'] : 0;


            if ($user && $userStatus) {
                $isEligible = $product->eligibleStatuses()
                    ->wherePivot('userstatus_id', $userStatus->id)
                    ->withPivot('discount')
                    ->first();

                if ($isEligible && $userStatus->isActive($user)) {
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

        $this->getDiscountPrice($product, $user);

        if ($size) {
            $this->getSizetype($product);
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


    public function getDiscountPrice($product, $user)
    {
        $userStatus = optional(optional($user)->userstatusinfo)->userstatus;
        if ($user && $userStatus) {
            $isEligible = $product->eligibleStatuses()
                ->wherePivot('userstatus_id', $userStatus->id)
                ->withPivot('discount')
                ->first();

            if ($isEligible && $userStatus->isActive($user)) {
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
    }

    public function getSizetype($product)
    {
        if (!empty($product->shoesize) && $product->shoesize->isNotEmpty()) {
            $product->size_type = "shoesize";
        } elseif (!empty($product->clothsize) && $product->clothsize->isNotEmpty()) {
            $product->size_type = "clothsize";
        }
    }

    public function addDuration($what, string $expansion, int $time)
    {
        $method = 'add' . ucfirst($expansion) . 's';
        return $what->$method($time);
    }
}
