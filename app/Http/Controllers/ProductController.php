<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;
use App\Models\ProductModel;
use App\Models\CategoriesModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends BaseController
{
    public function __construct()
    {
        $this->middleware('ifNotSeller')->only(['create', 'createPost']);
    }

    public function create() {
        $categories = CategoriesModel::all();

        return view('productCreate', compact('categories'));
    }

    // 상품 등록
    public function createPost(Request $req) {
        // $req->dd();
        $user_id = Auth::user()->user_id;
        $price   = str_replace(',', '', $req->price);

        // 유효성 검사
        $rules = [
            'cat_id'              => 'required|numeric',
            'name'                => 'required|min:2|max:50',
            'descriptionImages'   => 'array',
            'descriptionImages.*' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img'                 => 'mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];
        $req->validate($rules);

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
                // Log::debug("if걸림");
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

        return redirect()->route('main')->with('alert', '상품이 등록되었습니다.');
    }

    public function main() {
        // 상품 정보를 가져옴
        $products = 
            ProductModel::select('pro_id', 'name', 'price', 'img')
            ->where('status', '0')
            ->orderBy('created_at', 'desc') // 랜덤 정렬
            ->take(12) // 12개만 가져오기
            ->get();

        $categories = CategoriesModel::get();

        return view('main', compact('products', 'categories'));
    }

    // 메인페이지 카테고리
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
                ProductModel::select('pro_id', 'name', 'price', 'img')
                ->where('status', '0')
                ->orderBy('created_at', 'desc')
                ->skip($skip)
                ->take($perPage)
                ->get();
        } else {
            // 특정 카테고리의 상품 조회
            $products = 
                ProductModel::where('cat_id', $categoryId)
                ->where('status', '0')
                ->orderBy('created_at', 'desc')
                ->skip($skip)
                ->take($perPage)
                ->get();
        }

        // 상품 데이터 가공
        $productData = $products->map(function($product) {
            return [
                'detail'    => route('products.detail', ['id' => $product->pro_id]),
                'name'  => $product->name,
                'price' => number_format($product->price),
                'img'   => asset($product->img),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $productData,
        ]);
    }

    // 상세 페이지
    public function detail($id) {
        // 상품정보 가져오기
        $product = ProductModel::findOrFail($id);

        // 추천상품 가져오기
        $categoryId = $product->cat_id;

        // 해당 카테고리의 상품 중 랜덤으로 5개 가져오기
        $relatedProducts = ProductModel::where('cat_id', $categoryId)
            ->where('status', '0')
            ->inRandomOrder() // 랜덤으로 가져오기
            ->take(10)
            ->get();

        return view('detail', [        
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    // 수정
    public function update(Request $req) {
        // $req->dd();
        // 유효성 검사
        $rules = [
            'pro_id'              => 'required|numeric',
            'cat_id'              => 'required|numeric',
            'name'                => 'required|min:2|max:50',
            'price'               => 'required',
            'status'              => 'required|max:1',
            'descriptionImages'   => 'array',
            'descriptionImages.*' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img'                 => 'mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];
        $req->validate($rules);

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

    // 삭제
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

    // 검색
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
