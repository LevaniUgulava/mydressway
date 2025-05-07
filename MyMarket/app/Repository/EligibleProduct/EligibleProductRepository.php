<?php

namespace App\Repository\EligibleProduct;

use App\Helpers\ProductHelper;
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
            $userStatus = Userstatus::with('eligibleProducts')->findorfail($id);
            if (!$userStatus) {
                return response()->json([
                    'message' => "User status not found"
                ], 404);
            }

            return response()->json([
                'status' => $userStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "error to display",
                'error' => $e->getMessage()
            ]);
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
            $status = $user->userstatus()->first();
            if ($status->isActive()) {
                $products = $status->eligibleProducts()
                    ->select(
                        "products.id",
                        "products.name",
                        "products.description",
                        "products.price",
                        "products.discount",
                        "products.discountprice",
                        "products.active"
                    )
                    ->withAvg('rateproduct', 'rate')
                    ->where("products.active", 1)
                    ->get();
                $products = $products->map(function ($product) {
                    $product->Rate = number_format((float)$product->rateproduct_avg_rate, 1);
                    unset($product->rateproduct_avg_rate);
                    return $product;
                });


                $this->productHelper->transform($products, $user, null, false);

                if ($products) {
                    return [
                        'products' => $products
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
