<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\ReviewModel;
use Illuminate\Support\Str;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\CategoriesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends BaseController
{
    public function __construct()
    {
        $this->middleware('ifNotSeller')->only(['create', 'createPost']);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : create()
    // 설명     : 상품 등록 페이지를 반환하는 함수
    //            모든 카테고리 목록을 전달하여 새로운 상품 등록 화면을 렌더링
    //
    // param    : 없음
    //
    // return   : View - 상품 등록 페이지
    // -----------------------------------------------------------------------------------
    public function create() {
        $categories = CategoriesModel::all();

        return view('productCreate', compact('categories'));
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : createPost()
    // 설명     : 새로운 상품을 등록하는 함수
    //            입력된 상품 정보와 이미지 데이터를 데이터베이스에 저장
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - cat_id            : 카테고리 ID
    //              - name              : 상품명
    //              - price             : 가격
    //              - descriptionImages : 상품 설명 이미지 배열
    //              - img               : 상품 대표 이미지
    //
    // return   : RedirectResponse - 등록 성공 시 메인 페이지로 리다이렉트
    //              - 성공 시: '상품이 등록되었습니다.' 알림 포함
    //              - 실패 시: '시스템 에러가 발생하여 상품 등록에 실패했습니다.' 알림 포함
    // -----------------------------------------------------------------------------------
    public function createPost(Request $req) {
        // $req->dd();
        $user_id = Auth::user()->user_id;
        $price   = str_replace(',', '', $req->price);
        Log::debug('File size: ' . $req->file('img')->getSize());
        Log::debug('File MIME type: ' . $req->file('img')->getMimeType());

        // 유효성 검사
        $rules = [
            'cat_id'              => 'required|numeric',
            'name'                => 'required|min:2|max:50',
            'descriptionImages'   => 'array',
            'descriptionImages.*' => 'mimes:jpeg,png,jpg,gif,svg|max:10240',
            'img'                 => 'mimes:jpeg,png,jpg,gif,svg|max:10240'
        ];

        $messages = [
            'cat_id.required'              => '카테고리를 입력해야 합니다.',
            'name.required'                => '이름을 입력해야 합니다.',
            'name.min'                     => '이름은 최소 :min 글자 이상이어야 합니다.',
            'name.max'                     => '이름은 최대 :max 글자 이하여야 합니다.',
            'descriptionImages.array'      => '설명 이미지는 배열 형식이어야 합니다.',
            'descriptionImages.*.mimes'    => '설명 이미지는 jpeg, png, jpg, gif, svg 형식이어야 합니다.',
            'descriptionImages.*.max'      => '설명 이미지는 최대 :maxKB를 초과할 수 없습니다.',
            'descriptionImages.uploaded'   => '이미지 파일이 너무 큽니다. 최대 10MB까지 업로드할 수 있습니다.',
            'img.mimes'                    => '대표 이미지는 jpeg, png, jpg, gif, svg 형식이어야 합니다.',
            'img.max'                      => '대표 이미지는 최대 :maxKB를 초과할 수 없습니다.',
            'img.uploaded'                 => '이미지 파일이 너무 큽니다. 최대 10MB까지 업로드할 수 있습니다.'
        ];

        $req->validate($rules, $messages);

        try {            
            DB::beginTransaction();

            $data = [
                'user_id'      => $user_id,
                'cat_id'       => $req->cat_id,
                'name'         => $req->name,
                'price'        => $price,
            ];

            // 이미지가 업로드되었는지 확인
            if ($req->file('img')) {
                $img = $req->file('img');
                // 파일 이름 (작성자 PK + 현재 시간 + 확장자)
                $imgName = $user_id . time() . '_thumbnail.' . $img->getClientOriginalExtension();
                // 이미지를 public/product_img 로 이동
                $img->move(public_path('product_img'), $imgName);
                // 이미지 경로 저장
                $data['img'] = 'product_img/' . $imgName;
            } else {
                $data['img'] = 'fac_img/noImg.jpg';
            }

            if ($req->file('descriptionImages')) {
                // 배열로 선택된 파일들
                $images = $req->file('descriptionImages');
                $imagePaths = []; // 이미지 경로를 저장할 배열

                // 파일을 하나씩 처리
                foreach ($images as $img) {
                    // 파일 이름
                    $descImgName = $user_id . Str::uuid() . '.' . $img->getClientOriginalExtension(); // 고유한 파일 이름 생성
                    // 이미지를 public/product_img 로 이동
                    $img->move(public_path('product_img'), $descImgName);
                    // 이미지 경로 저장
                    $imagePaths[] = 'product_img/' . $descImgName; // 배열에 추가
                }

                $data['description'] = json_encode($imagePaths);
            } else {
                $data['description'] = ['fac_img/noImg.jpg'];
                $data['description'] = json_encode($data['description']);
            }

            $product = ProductModel::create($data);

            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            Log::error($e);
            return redirect()->back()->with('alert', '시스템 에러가 발생하여 상품 등록에 실패했습니다.\n잠시 후에 다시 시도해주세요.');
        }

        return redirect()->route('products.detail', ['id' => $product->pro_id])->with('alert', '상품이 등록되었습니다.');
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : main()
    // 설명     : 메인 페이지를 반환하는 함수
    //            최신 상품 12개와 모든 카테고리를 조회하여 메인 페이지를 렌더링
    //
    // param    : 없음
    //
    // return   : View - 메인 페이지 (상품 및 카테고리 정보 포함)
    // -----------------------------------------------------------------------------------
    public function main() {
        // 상품 정보를 가져옴
        $products =
            ProductModel::withAvg('reviews', 'rating')
                ->where('status', '0')
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get();

        $categories = CategoriesModel::get();

        return view('main', compact('products', 'categories'));
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : productGet()
    // 설명     : 특정 카테고리에 해당하는 상품을 JSON 형식으로 반환하는 함수
    //            페이지네이션 적용 (한 페이지에 12개 표시)
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - categoryId : 카테고리 ID (0일 경우 전체 상품)
    //              - page       : 페이지 번호 (기본값 1)
    //
    // return   : JsonResponse - 해당 카테고리 상품 목록 JSON
    // -----------------------------------------------------------------------------------
    public function productGet(Request $req)
    {
        // Log::debug($req);
    
        // 카테고리 PK 가져오기
        $categoryId = $req->categoryId;
        $page = $req->page ?? 1; // 페이지 번호가 없을 경우 기본값 1
        $perPage = 12; // 페이지당 표시할 상품 수
        $skip = ($page - 1) * $perPage; // 건너뛸 레코드 수
    
        // 전체일 경우
        if ($categoryId === '0') {
            $products = 
                ProductModel::withAvg('reviews', 'rating')
                    ->where('status', '0')
                    ->orderBy('created_at', 'desc')
                    ->skip($skip)
                    ->take($perPage)
                    ->get();
        } else {
            // 특정 카테고리의 상품 조회
            $products = 
                ProductModel::withAvg('reviews', 'rating')
                    ->where('cat_id', $categoryId)
                    ->where('status', '0')
                    ->orderBy('created_at', 'desc')
                    ->skip($skip)
                    ->take($perPage)
                    ->get();
        }
    
        // 상품 데이터 가공
        $productData = $products->map(function ($product) {
            return [
                'detail'    => route('products.detail', ['id' => $product->pro_id]),
                'name'      => $product->name,
                'price'     => number_format($product->price),
                'img'       => asset($product->img),
                'avg_rating'=> number_format($product->reviews_avg_rating ?? 0, 1), // 평균 별점 추가
            ];
        });
    
        return response()->json([
            'success' => true,
            'data'    => $productData,
        ]);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : detail()
    // 설명     : 특정 상품의 상세 정보를 반환하는 함수
    //            해당 상품의 관련 상품도 함께 반환
    //
    // param    : int $id - 조회할 상품의 고유 ID
    //
    // return   : View - 상품 상세 페이지
    // -----------------------------------------------------------------------------------
    public function detail($id) {
        // 상품정보 가져오기
        $product = ProductModel::findOrFail($id);

        // 별점 가져오기
        $rating = ReviewModel::where('pro_id', $id)->avg('rating');
        $reviews = ReviewModel::where('pro_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // 추천상품 가져오기
        $categoryId = $product->cat_id;

        // 해당 카테고리의 상품 중 랜덤으로 5개 가져오기
        $relatedProducts = ProductModel::where('cat_id', $categoryId)
            ->where('status', '0')
            ->inRandomOrder() // 랜덤으로 가져오기
            ->take(10)
            ->get();

        return view('detail', [        
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
            'rating'          => $rating,
            'reviews'         => $reviews,
        ]);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : update()
    // 설명     : 특정 상품 정보를 수정하는 함수
    //            입력된 정보로 상품 데이터와 이미지를 업데이트하고, 기존 이미지를 삭제
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - pro_id            : 상품 ID
    //              - cat_id            : 카테고리 ID
    //              - name              : 상품명
    //              - price             : 가격
    //              - descriptionImages : 상품 설명 이미지 배열
    //              - img               : 상품 대표 이미지
    //
    // return   : JsonResponse - 수정 성공 또는 실패 메시지
    // -----------------------------------------------------------------------------------
    public function update(Request $req) {
        if ($req->hasFile('descriptionImages')) {
            foreach ($req->file('descriptionImages') as $file) {
                Log::debug('Original Name: ' . $file->getClientOriginalName());
                Log::debug('File Size: ' . $file->getSize());
                Log::debug('MIME Type: ' . $file->getMimeType());
                Log::debug('Error Code: ' . $file->getError());
            }
        } else {
            Log::error('No files uploaded in descriptionImages');
        }
        // 유효성 검사
        $rules = [
            'pro_id'              => 'required|numeric',
            'cat_id'              => 'required|numeric',
            'name'                => 'required|min:2|max:50',
            'price'               => 'required',
            'status'              => 'required|max:1',
            'descriptionImages'   => 'array',
            'descriptionImages.*' => 'mimes:jpeg,png,jpg,gif,svg|max:10240',
            'img'                 => 'mimes:jpeg,png,jpg,gif,svg|max:10240'
        ];

        $messages = [
            'cat_id.required'              => '카테고리를 입력해야 합니다.',
            'name.required'                => '이름을 입력해야 합니다.',
            'name.min'                     => '이름은 최소 :min 글자 이상이어야 합니다.',
            'name.max'                     => '이름은 최대 :max 글자 이하여야 합니다.',
            'descriptionImages.array'      => '설명 이미지는 배열 형식이어야 합니다.',
            'descriptionImages.*.mimes'    => '설명 이미지는 jpeg, png, jpg, gif, svg 형식이어야 합니다.',
            'descriptionImages.*.max'      => '설명 이미지는 최대 :maxKB를 초과할 수 없습니다.',
            'descriptionImages.uploaded'   => '이미지 파일이 너무 큽니다. 최대 10MB까지 업로드할 수 있습니다.',
            'img.mimes'                    => '대표 이미지는 jpeg, png, jpg, gif, svg 형식이어야 합니다.',
            'img.max'                      => '대표 이미지는 최대 :maxKB를 초과할 수 없습니다.',
            'img.uploaded'                 => '이미지 파일이 너무 큽니다. 최대 10MB까지 업로드할 수 있습니다.'
        ];

        $req->validate($rules, $messages);

        DB::beginTransaction();
        try {            

            $product    = ProductModel::findOrFail($req->pro_id);
            $price      = preg_replace('/[^\d]/', '', $req->price);
            $user_id    = Auth::user()->user_id;
    
            $product->name      = $req->name;
            $product->cat_id    = $req->cat_id;
            $product->status    = $req->status;
            $product->price     = floatval($price);

            // 이미지가 업로드되었는지 확인
            if ($req->file('img')) {
                $img = $req->file('img');
                // 파일 이름 (작성자 PK + 현재 시간 + 확장자)
                $imgName = $user_id . time() . '_thumbnail.' . $img->getClientOriginalExtension();
                // 이미지를 public/product_img 로 이동
                $img->move(public_path('product_img'), $imgName);

                // 기존 이미지 서버에서 삭제
                $exImg = $product->img; // 기존 이미지 경로

                // 기존 이미지가 존재하는지 확인하고 삭제
                if ($exImg && file_exists(public_path($exImg))) {
                    unlink(public_path($exImg)); // 기존 이미지 삭제
                }

                // 이미지 경로 저장
                $product->img = 'product_img/' . $imgName;
            }

            if ($req->file('descriptionImages')) {
                // 선택된 파일들
                $images = $req->file('descriptionImages');
                $imagePaths = []; // 이미지 경로를 저장할 배열

                // 파일을 하나씩 처리
                foreach ($images as $img) {
                    // 파일 이름
                    $descImgName = $user_id . Str::uuid() . '.' . $img->getClientOriginalExtension(); // 고유한 파일 이름 생성
                    // 이미지를 public/product_img 로 이동
                    $img->move(public_path('product_img'), $descImgName);
                    // 이미지 경로 저장
                    $imagePaths[] = 'product_img/' . $descImgName; // 배열에 추가
                }

                // 기존 이미지 서버에서 삭제
                $exImgJson = $product->description; // 기존 이미지 경로가 담긴 JSON 데이터
                // JSON 데이터를 배열로 변환
                $exImgArray = json_decode($exImgJson); // true를 사용해 배열로 변환
                foreach ($exImgArray as $exImg) {
                    // 각 이미지 경로에 대해 파일이 존재하는지 확인하고 삭제
                    if ($exImg && file_exists(public_path($exImg))) {
                        unlink(public_path($exImg)); // 이미지 삭제
                    }
                }

                $product->description = json_encode($imagePaths);
            }

            $product->save();
            DB::commit();

            return response()->json(['msg' => '수정되었습니다.'], 200);
        } catch (ModelNotFoundException $e) {
            // 해당 ID의 상품이 없는 경우
            return response()->json(['msg' => '상품을 찾을 수 없습니다.'], 404);
        } catch(Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['msg' => '상품 수정 중 오류가 발생했습니다.'], 500);
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : delete()
    // 설명     : 선택한 상품을 삭제하는 함수
    //            소프트 딜리트를 사용하여 데이터베이스에서 상품을 비활성화
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - proIds : 삭제할 상품 ID 배열
    //
    // return   : JsonResponse - 삭제 성공 또는 실패 메시지
    // -----------------------------------------------------------------------------------
    public function delete(Request $req) {
        Log::debug($req);
        $req->validate([
            'proIds'    => 'required|array',
            'proIds.*'  => 'integer|exists:product,pro_id'
        ]);

        DB::beginTransaction();
        try {
            // 배열 형태로 받은 ID를 기반으로 제품 찾기
            $ids = $req->proIds;

            // 해당 ID의 제품들을 소프트 딜리트
            $products = ProductModel::whereIn('pro_id', $ids);
            $deletedCount = $products->count();
    
            if ($deletedCount > 0) {
                $products->delete();
                DB::commit();
                // 성공 메시지 반환
                return response()->json(['msg' => '상품이 삭제되었습니다.'], 200);
            } else {
                // 해당 ID의 상품이 없는 경우
                DB::rollBack();                
                return response()->json(['msg' => '삭제할 상품을 찾을 수 없습니다.'], 404);
            }
    
        } catch (Exception $e) {
            // 기타 예외 처리
            return response()->json(['msg' => '상품 삭제 중 오류가 발생했습니다.'], 500);
        }
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : search()
    // 설명     : 특정 검색어 및 카테고리에 해당하는 상품을 검색하여 반환하는 함수
    //            검색어와 카테고리를 기반으로 상품을 필터링하고 페이지네이션 적용
    //
    // param    : Request $req - 클라이언트에서 전달한 요청 객체
    //              - query    : 검색어
    //              - category : 카테고리 ID
    //
    // return   : View - 검색 결과 페이지 (검색된 상품 목록 포함)
    // -----------------------------------------------------------------------------------
    public function search(Request $req)
    {
        // 유효성 검사
        $req->validate([
            'query'     => 'required|min:2|max:50',
            'category'  => 'required|integer'
        ]);

        // 검색어와 카테고리 가져오기
        $query = $req->input('query');
        $categoryId = $req->input('category');

        // 상품 검색
        $products = ProductModel::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%$query%");
        });

        // 카테고리 필터가 있을 경우 해당 카테고리의 상품만 검색
        if ($categoryId && $categoryId !== 0) {
            $products->where('cat_id', $categoryId);
        }

        // 페이지네이션
        $paginatedProducts = $products->paginate(12);
        $categories = CategoriesModel::all();

        // 검색 결과 페이지로 상품 전달
        return view('search')
            ->with('products', $paginatedProducts)
            ->with('query', $query)
            ->with('selectedCat', $categoryId)
            ->with('categories', $categories);
    }
}
