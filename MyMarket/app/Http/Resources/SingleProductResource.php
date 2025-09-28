<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleProductResource extends JsonResource
{
    protected $varinats;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function __construct($resource, $varinats)
    {
        parent::__construct($resource);
        $this->varinats = $varinats;
    }
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'spu' => $this->spu->name,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'size_type' => $this->size_type,
            'discount' => $this->discount,
            'discountprice' => $this->discountprice,
            'discountstatus' => $this->discountstatus,
            'MainCategory' => $this->MainCategory,
            'Category' => $this->Category,
            'SubCategory' => $this->Subcategory,
            'additionalinfo' => $this->additionalinfo,
            'brand' => [
                'name' => $this->brands->first()->name,
                'image' => $this->brands->first()->getMedia('brand')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                })
            ],

            'variants' => $this->varinats
        ];
    }
}
