<?php

namespace App\Services;

class ProductService
{
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
                            'quantity' => $color->quantity
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
                            'quantity' => $color->quantity
                        ];
                    }),
                ];
            });
        }


        return null;
    }
}
