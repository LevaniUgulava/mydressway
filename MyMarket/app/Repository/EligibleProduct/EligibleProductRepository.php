<?php

namespace App\Repository\EligibleProduct;

use App\Helpers\ProductHelper;
use App\Http\Resources\ProductResource;
use App\Models\Eligibleproduct;
use App\Models\Userstatus;

class EligibleProductRepository implements EligibleProductRepositoryInterface
{
    private $productHelper;
    public function __construct(ProductHelper $productHelper)
    {
        $this->productHelper = $productHelper;
    }

    public function display($id)
    {
        try {
            $userStatus = Userstatus::with([
                'eligibleProducts' => fn($q) =>
                $q->select('products.id', 'products.name', 'products.price', 'products.discountprice', 'products.discount as product_discount', 'eligibleproducts.discount as status_discount')
                    ->with('media')
            ])->findOrFail($id);

            $userStatus->eligibleProducts->transform(function ($p) {
                $p->image_urls = $p->getMedia('default')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                })->toArray();
                unset($p->media);
                unset($p->pivot);


                return $p;
            });



            return response()->json(['status' => $userStatus]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'error to display',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function create($statusid, array $ids, $discount)
    {

        try {
            $userStatus = Userstatus::findorfail($statusid);
            if (!$userStatus) {
                return response()->json([
                    'message' => 'user status doesnot exist'
                ]);
            }
            foreach ($ids as $id) {
                $userStatus->eligibleProducts()->attach($id, ['discount' => $discount]);
            }
            return response()->json([
                'message' => "Added succesfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "cant Create",
                'error' => $e->getMessage()
            ]);
        }
    }

    public function delete($statusid, array $ids)
    {
        try {
            $userStatus = Userstatus::findorfail($statusid);
            if (!$userStatus) {
                return response()->json([
                    'message' => 'user status doesnot exist'
                ]);
            }
            foreach ($ids as $id) {
                $userStatus->eligibleProducts()->detach($id);
            }
            return response()->json([
                'message' => "deleted succesfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "cant Create",
                'error' => $e->getMessage()
            ]);
        }
    }

    public function displayEligibleProduct($user)
    {
        if ($user) {
            $status = $user->userstatusinfo->userstatus;
            if ($status->isActive($user)) {
                $products = $status->eligibleProducts()
                    ->select(
                        "products.id",
                        "products.name",
                        "products.description",
                        "products.price",
                        "products.discount",
                        "products.discountprice",
                    )
                    ->get();



                $this->productHelper->transform($products, $user, null, false);

                if ($products) {
                    return [
                        'products' => ProductResource::collection($products)
                    ];
                }
            } else {
                return [
                    'products' => []
                ];
            }
        }
    }
}
