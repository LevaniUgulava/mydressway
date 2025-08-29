<?php

namespace App\Services;

use Carbon\Carbon;

class ProductService
{

    public function checkNew($created_at)
    {
        $createdAt = Carbon::parse($created_at);
        $sevenDaysAgo = Carbon::now()->subDays(7);
        if ($createdAt->greaterThanOrEqualTo($sevenDaysAgo)) {
            return true;
        } else {
            return false;
        }
    }
    public function getQuantity($product, $size, $color)
    {
        if ($product) {
            $clothsize = $product->clothsize->where('size', $size)->first();
            $shoesize = $product->shoesize->where('size', $size)->first();
            $size = $clothsize ?? $shoesize;
            if ($size) {
                $fullquantity = $size->colors()->where('color', $color)->first()->quantity;
                return $fullquantity;
            }
        }
    }
    public function getSizeData($product)
    {
        if ($product->clothsize->isNotEmpty()) {
            return $product->clothsize->map(function ($size) {
                return [
                    'size' => $size->size,
                    'details' => $size->colors->map(function ($color) {
                        return [
                            'color' => $color->color,
                            'quantity' => $color->quantity,
                            'hex' => $color->hex
                        ];
                    }),
                ];
            });
        }
        if ($product->shoesize->isNotEmpty()) {
            return $product->shoesize->map(function ($size) {
                return [
                    'size' => $size->size,
                    'details' => $size->colors->map(function ($color) {
                        return [
                            'color' => $color->color,
                            'quantity' => $color->quantity,
                            'hex' => $color->hex

                        ];
                    }),
                ];
            });
        }


        return null;
    }
}
