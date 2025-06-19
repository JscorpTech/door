<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Enums\WebConfigKey;
use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Color;
use App\Models\DealOfTheDay;
use App\Models\DeliveryMan;
use App\Models\DigitalProductVariation;
use App\Models\FlashDealProduct;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductSeo;
use App\Models\PublishingHouse;
use App\Models\Review;
use App\Models\StockClearanceProduct;
use App\Models\Tag;
use App\Models\Translation;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Services\ProductService;
use App\Traits\CacheManagerTrait;
use App\Traits\FileManagerTrait;
use App\Traits\ProductTrait;
use App\Utils\Convert;
use App\Utils\Helpers;
use App\Utils\ProductManager;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }
    use CacheManagerTrait, ProductTrait;

    public function __construct(
        private readonly AuthorRepositoryInterface                 $authorRepo,
        private readonly PublishingHouseRepositoryInterface        $publishingHouseRepo,
        private readonly DigitalProductAuthorRepositoryInterface   $digitalProductAuthorRepo,
        private readonly DigitalProductPublishingHouseRepository   $digitalProductPublishingHouseRepo,
        private readonly StockClearanceProductRepositoryInterface  $stockClearanceProductRepo,
        private readonly StockClearanceSetupRepositoryInterface    $stockClearanceSetupRepo,
        private readonly ProductRepositoryInterface                $productRepo,
        private readonly BrandRepositoryInterface                  $brandRepo,
        private readonly ProductService                            $productService,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
    )
    {
    }

    public function getProductList(Request $request): JsonResponse
    {
        $seller = $request->seller;
        $products = Product::with(['clearanceSale' => function ($query) {
            return $query->active();
        }])
            ->withCount('reviews')
            ->where(['added_by' => 'seller', 'user_id' => $seller['id']])
            ->orderBy('id', 'DESC')->get();
        return response()->json($products, 200);
    }

    public function getVendorAllProducts($seller_id, Request $request): JsonResponse
    {
        $brandIds = json_decode($request['brand_ids'] ?? '', true);
        $categoryIds = json_decode($request['category_ids'] ?? '', true);
        $publishingHouseIds = json_decode($request['publishing_house_ids'] ?? '', true);
        $authorIds = json_decode($request['author_ids'] ?? '', true);

        $publishingHouseList = PublishingHouse::with(['publishingHouseProducts'])
            ->whereHas('publishingHouseProducts.product', function ($query) {
                return $query->active();
            })
            ->withCount(['publishingHouseProducts' => function ($query) {
                return $query->whereHas('product', function ($query) {
                    return $query->active();
                });
            }])->get();

        $productIdsForPublisher = [];
        $publishingHouseList->each(function ($publishingHouseGroup) use (&$productIdsForPublisher) {
            $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$productIdsForPublisher) {
                $productIdsForPublisher[] = $publishingHouse->product_id;
            });
        });

        $productIdsForUnknownPublisher = Product::active()->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForPublisher)->pluck('id')->toArray();

        $authorList = Author::withCount(['digitalProductAuthor' => function ($query) {
            return $query->whereHas('product', function ($query) {
                return $query->active();
            });
        }])->get();

        $productIdsForAuthor = [];
        $authorList->each(function ($authorGroup) use (&$productIdsForAuthor) {
            $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$productIdsForAuthor) {
                $productIdsForAuthor[] = $authorItem->product_id;
            });
        });
        $productIdsForUnknownAuthor = Product::active()->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForAuthor)->pluck('id')->toArray();

        $products = Product::when($request['offer_type'] == 'clearance_sale', function ($query) {
                return $query->active();
            })->with(['brand', 'category', 'rating', 'tags', 'reviews', 'seoInfo', 'digitalVariation', 'digitalProductAuthors' => function ($query) {
                return $query->with(['author']);
            }, 'digitalProductPublishingHouse' => function ($query) {
                return $query->with(['publishingHouse']);
            }, 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount('reviews')
            ->where(['user_id' => $seller_id, 'added_by' => 'seller'])
            ->when($request['search'], function ($query) use ($request) {
                $key = explode(' ', $request['search']);
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%");
                }
            })->when($request['search'] && $request['offer_type'] == 'clearance_sale', function ($query) {
                $clearanceSaleProductIds = StockClearanceProduct::pluck('product_id')->toArray();
                return $query->whereNotIn('id', $clearanceSaleProductIds);
            })
            ->when(in_array($request['product_type'], ['physical', 'digital']), function ($query) use ($request) {
                return $query->where(['product_type' => $request['product_type']]);
            })
            ->when($request->has('brand_ids') && json_decode($request['brand_ids'] ?? '', true), function ($query) use ($request) {
                $query->whereIn('brand_id', json_decode($request['brand_ids'] ?? '', true));
            })
            ->when($request->has('category_ids') && $categoryIds, function ($query) use ($categoryIds) {
                $query->where(function ($query) use ($categoryIds) {
                    return $query->whereIn('category_id', $categoryIds)
                        ->orWhereIn('sub_category_id', $categoryIds)
                        ->orWhereIn('sub_sub_category_id', $categoryIds);
                });
            })
            ->when(($request['min_price'] != null && $request['min_price'] > 0), function ($query) use ($request) {
                $minPrice = Convert::usdPaymentModule($request['min_price'] ?? 0, \request('currency_code'));
                return $query->where('unit_price', '>=', $minPrice);
            })
            ->when(($request['max_price'] != null), function ($query) use ($request) {
                $maxPrice = Convert::usdPaymentModule($request['max_price'] ?? 0, \request('currency_code'));
                return $query->where('unit_price', '<=', $maxPrice);
            })
            ->when(isset($request['start_date']) && checkDateFormatInMDYAndTime(dateTime: $request['start_date']), function ($query) use ($request) {
                return $query->whereDate('created_at', '>=', Carbon::createFromFormat('m/d/Y h:i:s A', $request['start_date']));
            })
            ->when(isset($request['end_date']) && checkDateFormatInMDYAndTime(dateTime: $request['end_date']), function ($query) use ($request) {
                return $query->whereDate('created_at', '<=', Carbon::createFromFormat('m/d/Y h:i:s A', $request['end_date']));
            })
            ->when($request->has('publishing_house_ids') && !empty($publishingHouseIds), function ($query) use ($request, $productIdsForUnknownPublisher, $publishingHouseIds) {
                $publishingHouseList = PublishingHouse::whereIn('id', $publishingHouseIds)->with(['publishingHouseProducts'])->withCount(['publishingHouseProducts' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $publishingHouseProductIds = [];
                $publishingHouseList->each(function ($publishingHouseGroup) use (&$publishingHouseProductIds) {
                    $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$publishingHouseProductIds) {
                        $publishingHouseProductIds[] = $publishingHouse->product_id;
                    });
                });

                if (in_array(0, $publishingHouseIds)) {
                    $publishingHouseProductIds = array_merge($publishingHouseProductIds, $productIdsForUnknownPublisher);
                }

                return $query->where(['product_type' => 'digital'])->whereIn('id', $publishingHouseProductIds);
            })
            ->when($request->has('author_ids') && !empty($authorIds) && is_array($authorIds), function ($query) use ($request, $productIdsForUnknownAuthor, $authorIds) {
                $authorList = Author::whereIn('id', $authorIds)->withCount(['digitalProductAuthor' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $authorProductIds = [];
                $authorList->each(function ($authorGroup) use (&$authorProductIds) {
                    $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$authorProductIds) {
                        $authorProductIds[] = $authorItem->product_id;
                    });
                });
                if (in_array(0, $authorIds)) {
                    $authorProductIds = array_merge($authorProductIds, $productIdsForUnknownAuthor);
                }
                return $query->where(['product_type' => 'digital'])->whereIn('id', $authorProductIds);
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $products->map(function ($product) {
            $product->digital_product_authors_names = $this->productService->getProductAuthorsInfo(product: $product)['names'];
            $product->digital_product_publishing_house_names = $this->productService->getProductPublishingHouseInfo(product: $product)['names'];
            return $product;
        });

        $productsFinal = Helpers::product_data_formatting($products->items(), true);

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsFinal,
            'brand_ids' => $request->has('brand_ids') ? $brandIds : null,
            'category_ids' => $request->has('category_ids') ? $categoryIds : null,
            'product_type' => $request['product_type'] ?? null,
            'search' => $request['search'] ?? null,
            'max_price' => $request['max_price'] ?? null,
            'min_price' => $request['min_price'] ?? null,
            'start_date' => $request['start_date'] ?? null,
            'end_date' => $request['end_date'] ?? null,
            'offer_type' => $request['offer_type'] ?? null,
            'publishing_house_ids' => $request->has('publishing_house_ids') ? $publishingHouseIds : null,
            'author_ids' => $request->has('author_ids') ? $authorIds : null,
        ], 200);
    }

    public function details(Request $request, $id): JsonResponse
    {
        $seller = $request->seller;
        $product = Product::with(['seoInfo', 'digitalProductAuthors', 'digitalProductPublishingHouse', 'clearanceSale' => function ($query) {
            return $query->active();
        }])
            ->withCount('reviews')->where(['added_by' => 'seller', 'user_id' => $seller->id])
            ->find($id);

        if (isset($product)) {
            $product = Helpers::product_data_formatting($product, false);
        }
        return response()->json($product, 200);
    }

    public function getProductImages(Request $request, $id)
    {
        $seller = $request->seller;
        $product = Product::where(['added_by' => 'seller', 'user_id' => $seller->id])->find($id);
        $productImage = [];
        if (isset($product)) {
            $productImage = [
                'images' => json_decode($product->images),
                'color_image' => json_decode($product->color_image),
                'images_full_url' => $product->images_full_url,
                'color_images_full_url' => $product->color_images_full_url,
            ];
        }
        return response()->json($productImage, 200);
    }

    public function stock_out_list(Request $request)
    {
        $seller = $request->seller;
        $stock_limit = getWebConfig(name: 'stock_limit');

        $products = Product::withCount('reviews')
            ->with(['seoInfo', 'digitalProductAuthors', 'digitalProductPublishingHouse', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->where(['added_by' => 'seller', 'user_id' => $seller->id, 'product_type' => 'physical', 'request_status' => 1])
            ->where('current_stock', '<', $stock_limit)
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $products->map(function ($data) {
            $data = Helpers::product_data_formatting($data);
            return $data;
        });

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $products->items()
        ], 200);
    }

    public function upload_images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
            'type' => 'required|in:product,thumbnail,meta',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $path = $request['type'] == 'product' ? '' : $request['type'] . '/';
        $image = $this->upload('product/' . $path, 'webp', $request->file('image'));
        if ($request['colors_active'] == "true") {
            $color_image = array(
                "color" => !empty($request['color']) ? str_replace('#', '', $request['color']) : null,
                "image_name" => $image,
            );
        } else {
            $color_image = null;
        }

        return response()->json([
            'image_name' => $image,
            'type' => $request['type'],
            'color_image' => $color_image,
            'storage' => config('filesystems.disks.default') ?? 'public',
        ], 200);
    }

    // Digital product file upload
    public function upload_digital_product(Request $request)
    {
        $seller = $request->seller;

        try {
            $validator = Validator::make($request->all(), [
                'digital_file_ready' => 'required|mimes:jpg,jpeg,png,gif,zip,pdf',
            ]);

            if ($validator->errors()->count() > 0) {
                return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
            }

            $file = $this->fileUpload('product/digital-product/', $request->digital_file_ready->getClientOriginalExtension(), $request->file('digital_file_ready'));

            return response()->json(['digital_file_ready_name' => $file], 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }


    public function deleteDigitalProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'variant_key' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $variation = DigitalProductVariation::where(['product_id' => $request['product_id'], 'variant_key' => $request['variant_key']])->first();
        if ($variation) {
            DigitalProductVariation::where(['id' => $variation['id']])->update(['file' => null]);
            return response()->json([
                'status' => 1,
                'message' => translate('delete_successful')
            ]);
        }
        return response()->json([
            'status' => 0,
            'message' => translate('delete_unsuccessful')
        ]);
    }

    public function add_new(Request $request)
    {
        $seller = $request->seller;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'product_type' => 'required',
            'unit' => 'required_if:product_type,==,physical',
            'images' => 'required',
            'thumbnail' => 'required',
            'discount_type' => 'required|in:percent,flat',
            'tax' => 'required|min:0',
            'tax_model' => 'required',
            'lang' => 'required',
            'unit_price' => 'required|min:1',
            'discount' => 'required|gt:-1',
            'shipping_cost' => 'required_if:product_type,==,physical|gt:-1',
            'code' => 'required|min:6|max:20|regex:/^[a-zA-Z0-9]+$/|unique:products',
            'minimum_order_qty' => 'required|numeric|min:1',
        ], [
            'name.required' => translate('Product name is required!'),
            'unit.required_if' => translate('Unit is required!'),
            'category_id.required' => translate('category is required!'),
            'shipping_cost.required_if' => translate('Shipping Cost is required!'),
            'images.required' => translate('Product images is required!'),
            'image.required' => translate('Product thumbnail is required!'),
            'code.required' => translate('Code is required!'),
            'minimum_order_qty.required' => translate('The minimum order quantity is required!'),
            'minimum_order_qty.min' => translate('The minimum order quantity must be positive!'),
        ]);

        if ($request['preview_file']) {
            $disallowedExtensions = ['php', 'java', 'js', 'html', 'exe', 'sh'];
            $maxFileSize = 10 * 1024 * 1024; // 10 MB in bytes
            $extension = $request['preview_file']->getClientOriginalExtension();
            $fileSize = $request['preview_file']->getSize();

            if ($fileSize > $maxFileSize) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('files', translate('File_size_exceeds_the_maximum_limit_of_10MB') . '!');
                });
            } elseif (in_array($extension, $disallowedExtensions)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('files', translate('Files_with_extensions_like') . (' .php,.java,.js,.html,.exe,.sh ') . translate('are_not_supported') . '!');
                });
            }
        }

        if (getWebConfig(name: 'product_brand') && empty($request['brand_id']) && $request['product_type'] == 'physical') {
            $validator->after(function ($validator) {
                $validator->errors()->add('brand_id', translate('brand_is_required'));
            });
        }

        $discount = $request['discount_type'] == 'percent' ? (($request['unit_price'] / 100) * $request['discount']) : $request['discount'];

        if ($request['unit_price'] <= $discount) {
            $validator->after(function ($validator) {
                $validator->errors()->add('unit_price', translate('Discount can not be more or equal to the price!'));
            });
        }

        $category = [];
        if ($request['category_id'] != null) {
            $category[] = ['id' => $request['category_id'], 'position' => 1];
        }
        if ($request['sub_category_id'] != null) {
            $category[] = ['id' => $request['sub_category_id'], 'position' => 2];
        }
        if ($request['sub_sub_category_id'] != null) {
            $category[] = ['id' => $request['sub_sub_category_id'], 'position' => 3];
        }

        $requestLanguage = json_decode($request['lang'], true);
        $requestName = json_decode($request['name'], true);
        $requestDescription = json_decode($request['description'], true);
        $requestColors = json_decode($request['colors'], true);
        $requestImages = json_decode($request['images'], true);
        $requestColorImages = json_decode($request['color_image'], true);
        $requestTags = json_decode($request['tags'], true);
        $requestChoiceNo = json_decode($request['choice_no'], true);
        $requestChoiceAttributes = json_decode($request['choice_attributes'], true);
        $storage = config('filesystems.disks.default') ?? 'public';
        $productArray = [
            'user_id' => $seller->id,
            'added_by' => "seller",
            'name' => $requestName[array_search(Helpers::default_lang(), $requestLanguage)],
            'slug' => Str::slug($requestName[array_search(Helpers::default_lang(), $requestLanguage)], '-') . '-' . Str::random(6),
            'category_ids' => json_encode($category),
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
            'brand_id' => isset($request->brand_id) && $request['product_type'] == 'physical' ? $request->brand_id : null,
            'unit' => $request['product_type'] == 'physical' ? $request['unit'] : null,
            'product_type' => $request['product_type'],
            'digital_product_type' => $request['product_type'] == 'digital' ? $request['digital_product_type'] : null,
            'code' => $request['code'],
            'minimum_order_qty' => $request['minimum_order_qty'],
            'details' => $requestDescription[array_search(Helpers::default_lang(), $requestLanguage)],
            'images' => json_encode($requestImages),
            'color_image' => json_encode($requestColorImages),
            'thumbnail' => $request['thumbnail'],
            'thumbnail_storage_type' => $request['thumbnail'] ? $storage : null,
        ];

        if ($request['product_type'] == 'digital' && $request['digital_product_type'] == 'ready_product' && $request['digital_file_ready']) {
            $productArray['digital_file_ready'] = $request['digital_file_ready'];
            $productArray['digital_file_ready_storage_type'] = $storage;

        }

        if ($request->has('colors_active') && $request->has('colors') && count($requestColors) > 0) {
            $productArray['colors'] = $request['product_type'] == 'physical' ? json_encode($requestColors) : json_encode([]);
        } else {
            $colors = [];
            $productArray['colors'] = $request['product_type'] == 'physical' ? json_encode($colors) : json_encode([]);
        }

        $choiceOptions = [];
        if ($request->has('choice')) {
            foreach ($requestChoiceNo as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request['choice'][$key];
                $item['options'] = $request[$str];
                $choiceOptions[] = $item;
            }
        }
        $productArray['choice_options'] = $request['product_type'] == 'physical' ? json_encode($choiceOptions) : json_encode([]);

        //combinations start
        $options = [];
        if ($request->has('colors_active') && $request->has('colors') && count($requestColors) > 0) {
            $colors_active = 1;
            $options[] = $requestColors;
        }
        if ($request->has('choice_no')) {
            foreach ($requestChoiceNo as $key => $no) {
                $name = 'choice_options_' . $no;
                $options[] = $request[$name];
            }
        }

        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        $variations = [];
        $stock_count = 0;
        if (count($combinations[0]) > 0) {

            foreach ($combinations as $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($requestColors) > 0) {
                            $color_name = Color::where('code', $item)->first()->name ?? '';
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = Convert::usd(abs($request['price_' . str_replace('.', '_', $str)]));
                $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
                $item['qty'] = $request['qty_' . str_replace('.', '_', $str)];

                $variations[] = $item;
                $stock_count += $item['qty'];
            }
        } else {
            $stock_count = (int)$request['current_stock'];
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $digitalFileOptions = self::getDigitalVariationOptions(request: $request);
        $digitalFileCombinations = self::getDigitalVariationCombinations(arrays: $digitalFileOptions);

        $previewFile = '';
        if ($request['product_type'] == 'digital' && $request->has('preview_file')) {
            $previewFile = $this->fileUpload(dir: 'product/preview/', format: $request['preview_file']->getClientOriginalExtension(), file: $request['preview_file']);
        }

        //combinations end
        $productArray += [
            'variation' => $request['product_type'] == 'physical' ? json_encode($variations) : json_encode([]),
            'unit_price' => Convert::usd($request['unit_price']),
            'purchase_price' => 0,
            'tax' => $request['tax'],
            'tax_type' => $request->get('tax_type', 'percent'),
            'tax_model' => $request['tax_model'],
            'discount' => $request['discount_type'] == 'flat' ? Convert::usd($request['discount']) : $request['discount'],
            'discount_type' => $request['discount_type'],

            'attributes' => $request['product_type'] == 'physical' ? json_encode($requestChoiceAttributes) : json_encode([]),
            'current_stock' => $request['product_type'] == 'physical' ? abs($stock_count) : 999999999,

            'video_provider' => 'youtube',
            'video_url' => $request['video_link'],
            'request_status' => getWebConfig(name: 'new_product_approval') == 1 ? 0 : 1,
            'status' => 0,
            'shipping_cost' => $request['product_type'] == 'physical' ? Convert::usd($request['shipping_cost']) : 0,
            'multiply_qty' => ($request['product_type'] == 'physical') ? ($request['multiplyQTY'] == 1 ? 1 : 0) : 0,
            'digital_product_file_types' => $request->has('extensions_type') ? json_decode($request['extensions_type'], true) : [],
            'digital_product_extensions' => $digitalFileCombinations,
            'preview_file' => $previewFile,
            'preview_file_storage_type' => $request->has('preview_file') ? $storage : null,
        ];

        $product = Product::create($productArray);

        $this->updateProductAuthorAndPublishingHouse(request: $request, product: $product);
        $digitalFileArray = self::getAddProductDigitalVariationData(request: $request, product: $product);
        foreach ($digitalFileArray as $digitalFile) {
            DigitalProductVariation::create($digitalFile);
        }

        ProductSeo::create(self::getProductSEOData(request: $request, product: $product));

        $productTagIds = [];
        if ($request['tags'] && count($requestTags) > 0) {
            foreach ($requestTags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => trim($value)]);
                $tag->save();
                $productTagIds[] = $tag->id;
            }
        }
        $product->tags()->sync($productTagIds);

        $data = [];
        foreach ($requestLanguage as $index => $key) {
            if ($requestName[$index] && $key != Helpers::default_lang()) {
                $data[] = [
                    'translationable_type' => 'App\Models\Product',
                    'translationable_id' => $product->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $requestName[$index],
                ];
            }
            if ($requestDescription[$index] && $key != Helpers::default_lang()) {
                $data[] = [
                    'translationable_type' => 'App\Models\Product',
                    'translationable_id' => $product->id,
                    'locale' => $key,
                    'key' => 'description',
                    'value' => $requestDescription[$index],
                ];
            }
        }
        Translation::insert($data);
        return response()->json(['message' => translate('successfully product added!'), 'request' => $request->all()], 200);
    }

    public function updateProductAuthorAndPublishingHouse(object|array $request, object|array $product): void
    {
        if ($request['product_type'] == 'digital') {
            if ($request->has('authors')) {
                $authorIds = [];
                foreach (json_decode($request['authors'], true) as $author) {
                    $authorId = $this->authorRepo->updateOrCreate(params: ['name' => $author], value: ['name' => $author]);
                    $authorIds[] = $authorId?->id;
                }

                foreach ($authorIds as $author) {
                    $productAuthorData = ['author_id' => $author, 'product_id' => $product->id];
                    $this->digitalProductAuthorRepo->updateOrCreate(params: $productAuthorData, value: $productAuthorData);
                }

                $this->digitalProductAuthorRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['author_id' => $authorIds]);
            } else {
                $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            }

            if ($request->has('publishing_house')) {
                $publishingHouseIds = [];
                foreach (json_decode($request['publishing_house'], true) as $publishingHouse) {
                    $publishingHouseId = $this->publishingHouseRepo->updateOrCreate(params: ['name' => $publishingHouse], value: ['name' => $publishingHouse]);
                    $publishingHouseIds[] = $publishingHouseId?->id;
                }

                foreach ($publishingHouseIds as $publishingHouse) {
                    $publishingHouseData = ['publishing_house_id' => $publishingHouse, 'product_id' => $product->id];
                    $this->digitalProductPublishingHouseRepo->updateOrCreate(params: $publishingHouseData, value: $publishingHouseData);
                }
                $this->digitalProductPublishingHouseRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['publishing_house_id' => $publishingHouseIds]);
            } else {
                $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
            }
        } else {
            $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
        }
    }

    public function getAddProductDigitalVariationData(object $request, object|array $product)
    {
        $digitalFileOptions = self::getDigitalVariationOptions(request: $request);
        $digitalFileCombinations = self::getDigitalVariationCombinations(arrays: $digitalFileOptions);

        $digitalFiles = [];
        foreach ($digitalFileCombinations as $combinationKey => $combination) {
            foreach ($combination as $item) {
                $string = $combinationKey . '-' . str_replace(' ', '', $item);
                $uniqueKey = strtolower(str_replace('-', '_', $string));
                $fileItem = $request->file('digital_files_' . $uniqueKey);
                $uploadedFile = '';
                if ($fileItem) {
                    $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                }
                $digitalFiles[] = [
                    'product_id' => $product->id,
                    'variant_key' => json_decode($request['digital_product_variant_key'], true)[$uniqueKey],
                    'sku' => json_decode($request['digital_product_sku'], true)[$uniqueKey],
                    'price' => json_decode($request['digital_product_price'], true)[$uniqueKey],
                    'file' => $uploadedFile,
                ];
            }
        }
        return $digitalFiles;
    }

    public function getDigitalVariationOptions(object $request): array
    {
        $options = [];
        if ($request->has('extensions_type')) {
            foreach (json_decode($request['extensions_type'], true) as $type) {
                $type = str_replace(' ', '_', $type);
                $name = 'extensions_options_' . $type;
                $options[$type] = json_decode($request[$name], true);
            }
        }
        return $options;
    }

    public function getDigitalVariationCombinations(array $arrays = []): array
    {
        $result = [];
        if (count($arrays) > 0) {
            foreach ($arrays as $arrayKey => $array) {
                foreach ($array as $key => $value) {
                    if ($value) {
                        $result[$arrayKey][] = $value;
                    }
                }
            }
        }
        return $result;
    }

    public function edit(Request $request, $id)
    {
        $product = Product::withoutGlobalScopes()->with('translations', 'tags', 'digitalVariation', 'seoInfo')->withCount('reviews')->find($id);
        $product = Helpers::product_data_formatting($product);

        return response()->json($product, 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $seller = $request->seller;
        $product = Product::with(['digitalVariation', 'seoInfo'])->withCount('reviews')->find($id);
        $oldProductData = $product;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'product_type' => 'required',
            'unit' => 'required_if:product_type,==,physical',
            'discount_type' => 'required|in:percent,flat',
            'tax' => 'required|min:0',
            'tax_model' => 'required',
            'lang' => 'required',
            'unit_price' => 'required|min:1',
            'discount' => 'required|gt:-1',
            'shipping_cost' => 'required_if:product_type,==,physical|gt:-1',
            'minimum_order_qty' => 'required|numeric|min:1',
            'code' => 'required|min:6|max:20|regex:/^[a-zA-Z0-9]+$/|unique:products,code,' . $product->id,
        ], [
            'name.required' => 'Product name is required!',
            'category_id.required' => 'category  is required!',
            'unit.required_if' => 'Unit is required!',
            'code.min' => 'The code must be positive!',
            'code.digits_between' => 'The code must be minimum 6 digits!',
            'code.required' => 'Product code sku is required!',
            'minimum_order_qty.required' => 'The minimum order quantity is required!',
            'minimum_order_qty.min' => 'The minimum order quantity must be positive!',
        ]);

        if ($request['preview_file']) {
            $disallowedExtensions = ['php', 'java', 'js', 'html', 'exe', 'sh'];
            $maxFileSize = 10 * 1024 * 1024; // 10 MB in bytes
            $extension = $request['preview_file']->getClientOriginalExtension();
            $fileSize = $request['preview_file']->getSize();

            if ($fileSize > $maxFileSize) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('files', translate('File_size_exceeds_the_maximum_limit_of_10MB') . '!');
                });
            } elseif (in_array($extension, $disallowedExtensions)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('files', translate('Files_with_extensions_like') . (' .php,.java,.js,.html,.exe,.sh ') . translate('are_not_supported') . '!');
                });
            }
        }

        $brandSetting = BusinessSetting::where('type', 'product_brand')->first()->value;
        if ($brandSetting && empty($request->brand_id) && $request['product_type'] == 'physical') {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'brand_id', 'Brand is required!'
                );
            });
        }

        if ($request['discount_type'] == 'percent') {
            $discount = ($request['unit_price'] / 100) * $request['discount'];
        } else {
            $discount = $request['discount'];
        }

        if ($request['unit_price'] <= $discount) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'unit_price',
                    translate('Discount can not be more or equal to the price!')
                );
            });
        }

        $requestLanguage = json_decode($request['lang'], true);
        $requestName = json_decode($request['name'], true);
        $requestDescription = json_decode($request['description'], true);
        $requestColors = json_decode($request['colors'], true);
        $requestImages = json_decode($request['images'], true);
        $requestColorImages = json_decode($request['color_image'], true);
        $requestTags = json_decode($request['tags'], true);
        $requestChoiceNo = json_decode($request['choice_no'], true);
        $requestChoiceAttributes = json_decode($request['choice_attributes'], true);

        $modifiedColors = [];
        foreach ($requestColors as $color) {
            $modifiedColors[] = str_replace('#', '', $color);
        }

        $modifiedColorImages = [];
        $modifiedColorImagePath = [];
        foreach ($requestColorImages as $colorImage) {
            if ($colorImage['color'] !== null && !in_array($colorImage['color'], $modifiedColors)) {
                $colorImage['color'] = null;
            }
            $modifiedColorImages[] = $colorImage;
            $modifiedColorImagePath[] = $colorImage['image_name'];
        }

        foreach ($requestImages as $requestImage) {
            if ($requestImage['image_name'] !== null && !in_array($requestImage['image_name'], $modifiedColorImagePath)) {
                $modifiedColorImages[] = [
                    'color' => null,
                    'image_name' => $requestImage['image_name'],
                    'storage' => $requestImage['storage'] ?? 'public',
                ];
            }
        }

        $allImagesData = [];
        foreach ($modifiedColorImages as $image) {
            $allImagesData[] = [
                'image_name' => $image['image_name'],
                'storage' => $image['storage'],
            ];
        }

        $product->user_id = $seller->id;
        $product->added_by = "seller";
        $product->name = $requestName[array_search(Helpers::default_lang(), $requestLanguage)];
        $category = [];

        if ($request->category_id != null) {
            $category[] = [
                'id' => $request['category_id'],
                'position' => 1,
            ];
        }
        if ($request->sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_category_id,
                'position' => 2,
            ];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ];
        }

        $product->category_ids = json_encode($category);
        $product->category_id = $request['category_id'];
        $product->sub_category_id = $request['sub_category_id'];
        $product->sub_sub_category_id = $request['sub_sub_category_id'];
        $product->brand_id = isset($request->brand_id) && $request['product_type'] == 'physical' ? $request['brand_id'] : null;
        $product->unit = $request['product_type'] == 'physical' ? $request['unit'] : null;
        $product->product_type = $request['product_type'];
        $product->digital_product_type = $request['product_type'] == 'digital' ? $request['digital_product_type'] : null;
        $product->code = $request->code;
        $product->minimum_order_qty = $request['minimum_order_qty'];
        $product->details = $requestDescription[array_search(Helpers::default_lang(), $requestLanguage)];

        $product->images = json_encode($allImagesData);
        $product->color_image = json_encode($modifiedColorImages);
        $product->thumbnail = $request->thumbnail;
        $product->thumbnail_storage_type = $product->thumbnail == $request->thumbnail ? $product->thumbnail_storage_type : $storage;

        if ($request->product_type == 'digital') {
            if ($request->digital_product_type == 'ready_product' && $request->digital_file_ready) {
                $product->digital_file_ready = $request->digital_file_ready;
                $product->digital_file_ready_storage_type = $storage;
            } elseif (($request->digital_product_type == 'ready_after_sell') && $product->digital_file_ready) {
                $product->digital_file_ready = null;
            }

            if ($request->has('extensions_type') && $request->has('digital_product_variant_key')) {
                $product->digital_file_ready = null;
            }
        } elseif ($request->product_type == 'physical' && $product->digital_file_ready) {
            $product->digital_file_ready = null;
        }

        if ($request->has('colors_active') && $request->has('colors') && count($requestColors) > 0) {
            $product->colors = $request->product_type == 'physical' ? json_encode($requestColors) : json_encode([]);
        } else {
            $colors = [];
            $product->colors = $request->product_type == 'physical' ? json_encode($colors) : json_encode([]);
        }

        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($requestChoiceNo as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = $request[$str];
                $choice_options[] = $item;
            }
        }
        $product->choice_options = $request->product_type == 'physical' ? json_encode($choice_options) : json_encode([]);

        //combinations start
        $options = [];
        if ($request->has('colors_active') && $request->has('colors') && count($requestColors) > 0) {
            $colors_active = 1;
            $options[] = $requestColors;
        }
        if ($request->has('choice_no')) {
            foreach ($requestChoiceNo as $key => $no) {
                $name = 'choice_options_' . $no;
                $options[] = $request[$name];
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        $variations = [];
        $stock_count = 0;
        if (count($combinations[0]) > 0) {

            foreach ($combinations as $combination) {
                $str = '';
                foreach ($combination as $k => $item) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($requestColors) > 0) {
                            $color_name = Color::where('code', $item)->first()->name ?? '';
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = Convert::usd(abs($request['price_' . str_replace('.', '_', $str)]));
                $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
                $item['qty'] = $request['qty_' . str_replace('.', '_', $str)];

                array_push($variations, $item);
                $stock_count += $item['qty'];
            }
        } else {
            $stock_count = (int)$request['current_stock'];
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        //combinations end
        $product->variation = $request->product_type == 'physical' ? json_encode($variations) : json_encode([]);
        $product->unit_price = Convert::usd($request->unit_price);
        $product->purchase_price = 0;
        $product->tax = $request->tax;
        $product->tax_type = $request->get('tax_type', 'percent');
        $product->tax_model = $request->tax_model;
        $product->discount = $request->discount_type == 'flat' ? Convert::usd($request->discount) : $request->discount;
        $product->discount_type = $request->discount_type;
        $product->attributes = $request->product_type == 'physical' ? json_encode($requestChoiceAttributes) : json_encode([]);
        $product->current_stock = $request->product_type == 'physical' ? $request->current_stock : 999999999;

        $product->meta_title = '';
        $product->meta_description = '';

        $product->shipping_cost = $request->product_type == 'physical' ? (getWebConfig(name: 'product_wise_shipping_cost_approval') == 1 ? $product->shipping_cost : Convert::usd($request->shipping_cost)) : 0;
        $product->multiply_qty = ($request->product_type == 'physical') ? ($request->multiplyQTY == 1 ? 1 : 0) : 0;

        if (getWebConfig(name: 'product_wise_shipping_cost_approval') == 1 && ($product->shipping_cost != Convert::usd($request->shipping_cost)) && ($request->product_type == 'physical')) {
            $product->temp_shipping_cost = Convert::usd($request->shipping_cost);
            $product->is_shipping_cost_updated = 0;
        }

        if ($request->has('meta_image')) {
            $product->meta_image = null;
        }

        if ($request->file('preview_file')) {
            $product->preview_file = $this->updateFile(dir: 'product/preview/', oldImage: $product['preview_file'], format: $request['preview_file']->getClientOriginalExtension(), image: $request['preview_file'], fileType: 'file');
            $product->preview_file_storage_type = $storage;
        }

        $product->video_provider = 'youtube';
        $product->video_url = $request->video_link;

        if ($product->request_status == 2) {
            $product->request_status = 0;
        }
        $product->save();

        $this->updateProductAuthorAndPublishingHouse(request: $request, product: $product);
        self::getDigitalProductUpdateProcess($request, $product);

        $ProductSeo = ProductSeo::where(['product_id' => $product['id']])->first();
        if ($ProductSeo) {
            ProductSeo::find($ProductSeo['id'])->update(self::getProductSEOData(request: $request, product: $product));
        } else {
            ProductSeo::create(self::getProductSEOData(request: $request, product: $product));
        }

        $tag_ids = [];
        if ($requestTags) {
            foreach ($requestTags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => trim($value)]
                );
                $tag->save();
                $tag_ids[] = $tag->id;
            }
        }
        $product->tags()->sync($tag_ids);

        foreach ($requestLanguage as $index => $key) {
            if ($requestName[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Models\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'name'
                    ],
                    ['value' => $requestName[$index]]
                );
            }
            if ($requestDescription[$index] && $key != 'en') {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Models\Product',
                        'translationable_id' => $product->id,
                        'locale' => $key,
                        'key' => 'description'
                    ],
                    ['value' => $requestDescription[$index]]
                );
            }
        }

        $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $product['id']]);
        $this->updateRestockRequestListAndNotify(product: $oldProductData, updatedProduct: $updatedProduct);
        $this->updateStockClearanceProduct(product: $updatedProduct);

        return response()->json(['message' => translate('successfully product updated!')], 200);
    }

    public function updateStockClearanceProduct($product): void
    {
        $config = $this->stockClearanceSetupRepo->getFirstWhere(params: [
            'setup_by' => $product['added_by'] == 'admin' ? $product['added_by'] : 'vendor',
            'shop_id' => $product['added_by'] == 'admin' ? 0 : $product?->seller?->shop?->id,
        ]);
        $stockClearanceProduct = $this->stockClearanceProductRepo->getFirstWhere(params: ['product_id' => $product['id']]);

        if ($config && $config['discount_type'] == 'product_wise' && $stockClearanceProduct && $stockClearanceProduct['discount_type'] == 'flat') {
            $minimumPrice = $product['unit_price'];
            foreach ((json_decode($product['variation'], true) ?? []) as $variation) {
                if ($variation['price'] < $minimumPrice) {
                    $minimumPrice = $variation['price'];
                }
            }

            if ($minimumPrice < $stockClearanceProduct['discount_amount']) {
                $this->stockClearanceProductRepo->updateByParams(params: ['product_id' => $product['id']], data: ['is_active' => 0]);
            }
        }
    }

    public function getProductSEOData(object $request, object|null $product = null): array
    {
        return [
            "product_id" => $product['id'],
            "title" => $request['meta_title'] ?? ($product ? $product['meta_title'] : null),
            "description" => $request['meta_description'] ?? ($product ? $product['meta_description'] : null),
            "index" => $request['meta_index'] == 'index' ? '' : 'noindex',
            "no_follow" => $request['meta_no_follow'] == 'nofollow' ? 'nofollow' : '',
            "no_image_index" => $request['meta_no_image_index'] ? 'noimageindex' : '',
            "no_archive" => $request['meta_no_archive'] ? 'noarchive' : '',
            "no_snippet" => $request['meta_no_snippet'] ?? 0,
            "max_snippet" => $request['meta_max_snippet'] ?? 0,
            "max_snippet_value" => $request['meta_max_snippet_value'] ?? 0,
            "max_video_preview" => $request['meta_max_video_preview'] ?? 0,
            "max_video_preview_value" => $request['meta_max_video_preview_value'] ?? 0,
            "max_image_preview" => $request['meta_max_image_preview'] ?? 0,
            "max_image_preview_value" => $request['meta_max_image_preview_value'] ?? 0,
            "image" => $request->meta_image ?? ($product ? ($product->seoInfo->image ?? $product['meta_image']) : null),
            "created_at" => now(),
            "updated_at" => now(),
        ];
    }

    public function getDigitalProductUpdateProcess($request, $product): void
    {
        if ($request['digital_product_type'] == 'ready_product' && $request->has('digital_product_variant_key') && !$request->hasFile('digital_file_ready')) {
            $getAllVariation = DigitalProductVariation::where(['product_id' => $product['id']])->get();
            $getAllVariationKey = $getAllVariation->pluck('variant_key')->toArray();
            $getRequestVariationKey = json_decode($request['digital_product_variant_key'], true);
            $differenceFromDB = array_diff($getAllVariationKey, $getRequestVariationKey);
            $differenceFromRequest = array_diff($getRequestVariationKey, $getAllVariationKey);
            $newCombinations = array_merge($differenceFromDB, $differenceFromRequest);

            foreach ($newCombinations as $newCombination) {
                if (in_array($newCombination, $getRequestVariationKey)) {
                    $uniqueKey = strtolower(str_replace('-', '_', $newCombination));
                    $fileItem = $request->file('digital_files_' . $uniqueKey);
                    $uploadedFile = '';
                    if ($fileItem) {
                        $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                    }
                    DigitalProductVariation::insert([
                        'product_id' => $product['id'],
                        'variant_key' => $getRequestVariationKey[$uniqueKey],
                        'sku' => json_decode($request['digital_product_sku'], true)[$uniqueKey],
                        'price' => json_decode($request['digital_product_price'], true)[$uniqueKey],
                        'file' => $uploadedFile,
                    ]);
                }
            }

            foreach ($differenceFromDB as $variation) {
                $variation = DigitalProductVariation::where(['product_id' => $product['id'], 'variant_key' => $variation])->first();
                if ($variation) {
                    DigitalProductVariation::where(['id' => $variation['id']])->delete();
                }
            }

            foreach ($getAllVariation as $variation) {
                if (in_array($variation['variant_key'], $getRequestVariationKey)) {
                    $uniqueKey = strtolower(str_replace('-', '_', $variation['variant_key']));
                    $fileItem = $request->file('digital_files_' . $uniqueKey);
                    $uploadedFile = $variation['file'] ?? '';
                    $variation = DigitalProductVariation::where(['product_id' => $product['id'], 'variant_key' => $variation['variant_key']])->first();
                    if ($fileItem) {
                        $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                    }
                    DigitalProductVariation::where(['product_id' => $product['id'], 'variant_key' => $variation['variant_key']])->update([
                        'variant_key' => $getRequestVariationKey[$uniqueKey],
                        'sku' => json_decode($request['digital_product_sku'], true)[$uniqueKey],
                        'price' => json_decode($request['digital_product_price'], true)[$uniqueKey],
                        'file' => $uploadedFile,
                    ]);
                }

                if ($request['product_type'] == 'physical' || $request['digital_product_type'] == 'ready_after_sell') {
                    $variation = DigitalProductVariation::where(['product_id' => $product['id'], 'variant_key' => $variation['variant_key']])->first();
                    if ($variation && $variation['file']) {
                        DigitalProductVariation::where(['id' => $variation['id']])->update(['file' => '']);
                    }
                    if ($request['product_type'] == 'physical') {
                        $variation->delete();
                    }
                }
            }
        } else {
            DigitalProductVariation::where(['product_id' => $product['id']])->delete();
        }
    }

    public function updateProductQuantity(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
        if ($product) {
            $this->productRepo->updateByParams(params: ['id' => $request['product_id']], data: [
                'current_stock' => $request['current_stock'],
                'variation' => $request['variation'],
            ]);
            $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
            $this->updateRestockRequestListAndNotify(product: $product, updatedProduct: $updatedProduct);

            return response()->json(['message' => translate('successfully_product_updated')], 200);
        }
        return response()->json(['message' => translate('update_fail')], 403);
    }

    public function status_update(Request $request)
    {
        $seller = $request->seller;
        $product = Product::withCount('reviews')->where(['added_by' => 'seller', 'user_id' => $seller->id])->find($request->id);
        if (!$product) {
            return response()->json(['message' => translate('invalid_prodcut')], 403);
        }
        $product->status = $request->status;
        $product->save();

        return response()->json([
            'success' => translate('status_update_successfully'),
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $product = Product::withCount('reviews')->find($id);
        foreach (json_decode($product['images'], true) as $image) {
            $imageName = is_string($image) ? $image : $image['image_name'];
            $this->deleteFile('/product/' . $imageName);
        }
        $this->deleteFile('/product/thumbnail/' . $product['thumbnail']);
        $this->productService->deletePreviewFile(product: $product);
        $product->delete();
        FlashDealProduct::where(['product_id' => $id])->delete();
        DealOfTheDay::where(['product_id' => $id])->delete();
        cacheRemoveByType(type: 'products');
        return response()->json(['message' => translate('successfully product deleted!')], 200);
    }

    public function barcode_generate(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'quantity' => 'required',
        ], [
            'id.required' => 'Product ID is required',
            'quantity.required' => 'Barcode quantity is required',
        ]);

        if ($request['limit'] > 270) {
            return response()->json(['code' => 403, 'message' => 'You can not generate more than 270 barcode']);
        }
        $product = Product::withCount('reviews')->where('id', $request->id)->first();
        $quantity = $request->quantity ?? 30;

        if (isset($product->code)) {
            $pdf = app()->make(PDF::class);
            $pdf->loadView('vendor-views.product.barcode-pdf', compact('product', 'quantity'));
            $pdf->save(storage_path('app/public/product/barcode.pdf'));
            return response()->json(asset('storage/app/public/product/barcode.pdf'));
        } else {
            return response()->json(['message' => translate('Please update product code!')], 203);
        }

    }

    public function top_selling_products(Request $request): JsonResponse
    {
        $seller = $request->seller;

        $orders = OrderDetail::with(['product' => function ($query) {
                return $query->with(['rating', 'clearanceSale' => function ($query) {
                    return $query->active();
                }]);
            }])
            ->select('product_id', DB::raw('SUM(qty) as count'))
            ->where(['seller_id' => $seller['id'], 'delivery_status' => 'delivered'])
            ->whereHas('product', function ($query) {
                $query->where(['added_by' => 'seller']);
            })
            ->groupBy('product_id')
            ->orderBy("count", 'desc')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $ordersFinal = $orders->map(function ($order) {
            $order['product'] = Helpers::product_data_formatting($order['product'], false);
            return $order;
        });

        $data = array(
            'total_size' => $orders->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'products' => $ordersFinal,
        );

        return response()->json($data, 200);
    }

    public function most_popular_products(Request $request): JsonResponse
    {
        $seller = $request->seller;
        $products = Product::with(['rating', 'tags', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount('reviews')
            ->whereHas('reviews', function ($query) {
                return $query;
            })
            ->where(['user_id' => $seller['id'], 'added_by' => 'seller'])
            ->withCount(['reviews'])->orderBy('reviews_count', 'DESC')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $productsFinal = Helpers::product_data_formatting($products, true);

        $data = [
            'total_size' => $products->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'products' => $productsFinal
        ];
        return response()->json($data, 200);
    }

    public function top_delivery_man(Request $request)
    {
        $seller = $request->seller;
        $delivery_men = DeliveryMan::with(['rating', 'orders' => function ($query) {
            $query->select('delivery_man_id', DB::raw('COUNT(delivery_man_id) as count'));
        }])
            ->withCount(['deliveredOrders'])
            ->whereHas('deliveredOrders', function ($query) use ($seller) {
                $query->where(['seller_is' => 'seller', 'seller_id' => $seller['id']])->whereNotNull('delivery_man_id');
            })
            ->where(['seller_id' => $seller['id']])
            ->when(!empty($request['search']), function ($query) use ($request) {
                $key = explode(' ', $request['search']);
                foreach ($key as $value) {
                    $query->where('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%");
                }
            })
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $data = array();
        $data['total_size'] = $delivery_men->total();
        $data['limit'] = $request['limit'];
        $data['offset'] = $request['offset'];
        $data['delivery_man'] = $delivery_men->items();
        return response()->json($data, 200);
    }

    public function review_list(Request $request, $product_id)
    {
        $product = Product::withCount('reviews')->find($product_id);
        $average_rating = count($product->rating) > 0 ? number_format($product->rating[0]->average, 2, '.', ' ') : 0;
        $reviews = Review::with(['customer', 'product', 'reply'])->where(['product_id' => $product_id])
            ->latest('updated_at')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $rating_group_count = Review::where(['product_id' => $product_id])
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->get();

        $data = array();
        $data['total_size'] = $reviews->total();
        $data['limit'] = $request['limit'];
        $data['offset'] = $request['offset'];
        $data['group-wise-rating'] = $rating_group_count;
        $data['average_rating'] = $average_rating;
        $data['reviews'] = $reviews->items();

        return response()->json($data, 200);
    }

    public function get_categories(Request $request)
    {
        $categories = Category::with(['childes.childes', 'childes' => function ($query) {
            $query->with(['childes' => function ($query) {
                $query->withCount(['subSubCategoryProduct'])->where('position', 2);
            }])->withCount(['subCategoryProduct'])->where('position', 1);
        }])
            ->where('position', 0)
            ->priority()
            ->get();

        return response()->json($categories, 200);

    }

    public function deleteImage(Request $request)
    {
        $product = Product::withCount('reviews')->find($request['id']);
        $array = [];
        if (count(json_decode($product['images'])) < 2) {
            return response()->json(['message' => translate('you_can_not_delete_all_images')], 403);
        }
        $colors = json_decode($product['colors']);
        $color_image = json_decode($product['color_image']);
        $color_image_arr = [];
        if ($colors && $color_image) {
            foreach ($color_image as $img) {
                if ($img->color != $request['color'] && $img->image_name != $request['name']) {
                    $color_image_arr[] = [
                        'color' => $img->color != null ? $img->color : null,
                        'image_name' => $img->image_name,
                        'storage' => $img?->storage ?? 'public',
                    ];
                } else {
                    $this->deleteFile('/product/' . $request['name']);
                    if ($img->color != null) {
                        $color_image_arr[] = [
                            'color' => $img->color,
                            'image_name' => null,
                        ];
                    }
                }
            }
        }

        foreach (json_decode($product['images']) as $image) {
            $imageName = $image->image_name ?? $image;
            if ($imageName != $request['name']) {
                array_push($array, $image);
            } else {
                $this->deleteFile('/product/' . $request['name']);
            }
        }
        Product::withCount('reviews')->where('id', $request['id'])->update([
            'images' => json_encode($array),
            'color_image' => json_encode($color_image_arr),
        ]);
        return response()->json(translate('product_image_removed_successfully'), 200);
    }

    public function getStockLimitStatus(Request $request)
    {
        $seller = $request->seller;
        $filters = [
            'added_by' => 'seller',
            'product_type' => 'physical',
            'request_status' => 1,
            'user_id' => $seller->id,
        ];
        $stockLimit = getWebConfig(name: 'stock_limit');
        $products = Product::where($filters)->where('current_stock', '<', $stockLimit)->get();
        if ($products->count() == 1) {
            return response()->json(['status' => 'one_product', 'product_count' => 1, 'product' => $products->first()], 200);
        } else {
            return response()->json(['status' => 'multiple_product', 'product_count' => $products->count()], 200);
        }
    }

    public function deletePreviewFile(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['product_id']]);
        $this->productService->deletePreviewFile(product: $product);
        $this->productRepo->update(id: $request['product_id'], data: ['preview_file' => null]);
        return response()->json([
            'status' => 1,
            'message' => translate('Preview_file_deleted')
        ]);
    }

    public function getDigitalProductsAuthorList(Request $request): JsonResponse
    {
        $authors = ProductManager::getProductAuthorList();
        return response()->json($authors->values());
    }

    public function getDigitalPublishingHouseList(Request $request): JsonResponse
    {
        $publishingHouseList = ProductManager::getPublishingHouseList();
        return response()->json($publishingHouseList->values());
    }

    public function getRestockRequestList(Request $request): JsonResponse
    {
        $seller = Helpers::getSellerByToken($request);

        if (!$seller) {
            return response()->json([
                'auth-001' => translate('Your_existing_session_token_does_not_authorize_you_any_more')
            ], 401);
        }

        $filters = [
            'added_by' => 'seller',
            'seller_id' => $seller->id,
            'category_id' => $request['category_id'],
            'brand_ids' => $request['brand_ids'] ? json_decode($request['brand_ids']) : [],
        ];

        $startDate = $request['restock_start_date'];
        $endDate = $request['restock_end_date'];

        $restockProducts = $this->restockProductRepo->getListWhereBetween(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['search'],
            filters: $filters,
            relations: ['product'],
            whereBetween: 'created_at',
            whereBetweenFilters: $startDate && $endDate ? [$startDate, $endDate] : [],
            dataLimit: $request['limit'] ?? getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
            offset: $request['offset'] ?? '1',
        );

        $restockProducts->map(function ($data) {
            $data->variant_keys = $this->restockProductRepo->getListWhere(filters: ['product_id' => $data['product_id']], dataLimit: 'all')?->pluck('variant')->toArray() ?? [];
            $data->product = Helpers::product_data_formatting($data->product, false);
            return $data;
        });

        return response()->json([
            'total_size' => $restockProducts->total(),
            'limit' => (int)($request['limit'] ?? getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)),
            'offset' => (int)$request['offset'],
            'data' => $restockProducts->items(),
        ], 200);
    }

    public function deleteRestockRequest(Request $request): JsonResponse
    {
        $this->restockProductRepo->delete(params: ['id' => $request['id']]);
        $this->restockProductCustomerRepo->delete(params: ['restock_product_id' => $request['id']]);
        return response()->json([
            'message' => translate('product_restock_removed_successfully')
        ], 200);
    }

    public function updateRestockQuantity(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);

        if ($product && $request['current_stock'] >= 0) {
            $this->productRepo->updateByParams(params: ['id' => $request['product_id']], data: [
                'current_stock' => $request['current_stock'],
                'variation' => $request['variation'],
            ]);

            $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
            $this->updateRestockRequestListAndNotify(product: $product, updatedProduct: $updatedProduct);

            return response()->json(['message' => translate('successfully_product_updated!')], 200);
        }
        return response()->json(['message' => translate('update_fail!')], 403);
    }

    public function getRestockRequestBrands(Request $request): JsonResponse
    {
        $seller = Helpers::getSellerByToken($request);

        if (!$seller) {
            return response()->json([
                'auth-001' => translate('Your_existing_session_token_does_not_authorize_you_any_more')
            ], 401);
        }

        $filters = [
            'added_by' => 'seller',
            'seller_id' => $seller->id
        ];

        $restockRequests = $this->restockProductRepo->getListWhereBetween(
            orderBy: ['updated_at' => 'desc'],
            filters: $filters,
            relations: ['product'],
            dataLimit: 'all',
        );

        $products = $this->productRepo->getListWithScope(whereIn: ['id' => $restockRequests->pluck('product_id')->toArray()], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhereIn(whereIn: ['id' => $products->pluck('brand_id')->toArray()], dataLimit: 'all');

        $brands = $brands->map(function ($brand) use ($restockRequests) {
            $brand->product_count = $restockRequests->filter(function ($restockRequest) use ($brand) {
                return $restockRequest?->product?->brand_id === $brand->id;
            })->count();
            return $brand;
        });

        return response()->json([
            'brands' => $brands,
        ], 200);
    }
}
