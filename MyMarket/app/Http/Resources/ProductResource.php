<?php

namespace App\Http\Resources;

use App\Helpers\Translator;
use App\Http\Controllers\DiscountController;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Type\Decimal;

use function PHPUnit\Framework\returnSelf;

class ProductResource extends JsonResource
{

    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $productService = app(ProductService::class);

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'size_type' => $this->size_type,
            'size' => $productService->getSizeData($this),
            'discount' => $this->discount,
            'discountstatus' => $this->discountstatus,
            'discountprice' => $this->discountprice,
            'MainCategory' => $this->MainCategory,
            'Category' => $this->Category,
            'SubCategory' => $this->Subcategory,
            'additionalinfo' => $this->additionalinfo,
            'image_urls' => $this->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            }),
            'brand' => $this->brands->first()?->name,
            'isLiked' => $this->isLiked ?? false,
            'inCartQuantity' => $this->inCartQuantity ?? 0,
            "isNew" => $productService->checkNew($this->created_at)
        ];
    }
}
