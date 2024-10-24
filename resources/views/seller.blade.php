@extends('layouts.app')

@section('title', '판매자 페이지')

@section('css')
<link rel="stylesheet" href="{{asset('css/seller.css')}}">
@endsection

@section('content')
<div class="container mt-4">
    <h2 id="shop_h2" class="shopTitle">내 상품</h2>
    <div class="d-flex justify-content-end align-items-center mb-4">
        <a href="{{ route('products.create') }}" class="btn btn-outline-primary" style="margin-right: 4px">상품 등록하기</a>
        <button class="btn btn-outline-danger ms-2" onclick="delSelectedItems()">선택 항목 삭제</button>
    </div>
    <!-- 상품 리스트 -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th class="text-center"><input type="checkbox" id="selectAll" onchange="selectAll(this)"></th> <!-- 전체 선택 체크박스 -->
                    <th scope="col" class="text-center">이름</th>
                    <th scope="col" class="text-center">카테고리</th>
                    <th scope="col" class="text-center">상태</th>
                    <th scope="col" class="text-center">가격</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="product-item">
                    <td class="text-center">
                        <input type="checkbox" class="product-checkbox" value="{{ $product->pro_id }}" onchange="updateSelectAll()">
                    </td>
                    <td>
                        <span href="#" class="product-name" 
                            data-id="{{ $product->pro_id}}" 
                            data-name="{{ $product->name }}" 
                            data-category="{{ $product->category->cat_id }}" 
                            data-status="{{ $product->status }}"
                            data-price="{{ $product->formatted_price }}" 
                            data-created="{{ $product->created_at }}" 
                            data-updated="{{ $product->updated_at }}" 
                            data-img="{{ asset($product->img) }}" 
                            data-description="{{ $product->description }}"
                            data-toggle="modal" 
                            data-target="#productModal">{{ $product->name }}
                        </span>
                    </td>
                    <td>
                        <span class="category-name">{{ $product->category->name }}</span>
                    </td>
                    <td>
                        <span class="status">{{ $product->status_name }}</span>
                    </td>
                    <td>
                        <span class="price">{{ $product->formatted_price }} 원</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
        {{-- @foreach($products as $product)
        <div class="row product-item p-3 mb-3 border rounded align-items-center">
            <div class="col">
                <span class="product-name">{{ $product->name }}</span>
            </div>
            <div class="col">
                <span class="category">{{ $product->category->name }}</span>
            </div>
            <div class="col">
                <span class="status">{{ $product->status_name }}</span>
            </div>
            <div class="col">
                <span class="price">{{ $product->formatted_price }}</span>
            </div>
            <div class="col">
                <span class="addDate">{{ $product->created_at }}</span>
            </div>
            <div class="col">
                <span class="uptDate">{{ $product->updated_at }}</span>
            </div>
        </div>
        @endforeach --}}
        <div class="pagination-container d-flex justify-content-center mt-4">
            {{ $products->links('vendor.pagination.custom') }}
        </div>
    </div>
    <!-- 페이지네이션 -->

    <!-- 모달 -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">상품 수정</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="productForm" action="" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- 상품 이미지 -->
                        <div class="form-group">
                            <label for="img">썸네일 이미지</label>
                            <div class="form-group text-center">
                                <img id="productImage" src="" alt="상품 이미지" class="img-fluid" style="max-width: 100%; height: auto;">
                            </div>
                            <input type="file" class="form-control-file" id="img" name="img" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" onchange="previewImage(event)">
                            @error('img')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="product-name">이름</label>
                            <input type="text" class="form-control" id="product-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="product-category">카테고리</label>
                            <select class="form-control" id="product-category" name="cat_id" required>
                                <option value="1">의류/잡화</option>
                                <option value="2">뷰티</option>
                                <option value="3">생활용품</option>
                                <option value="4">식품</option>
                                <option value="5">건강식품</option>
                                <option value="6">디지털</option>
                                <option value="7">반려동물</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product-status">상태</label>
                            <select class="form-control" id="product-status" name="status">
                                <option value="0">판매중</option>
                                <option value="1">품절</option>
                                <option value="2">숨김</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product-price">가격</label>
                            <input type="text" class="form-control" id="product-price" name="price" required>
                            <div id="resultDiv"></div>
                        </div>
                        <div class="form-group">
                            <label for="product-created">등록일</label>
                            <input type="text" class="form-control" id="product-created" name="created" readonly>
                        </div>
                        <div class="form-group">
                            <label for="product-updated">수정일</label>
                            <input type="text" class="form-control" id="product-updated" name="updated" readonly>
                        </div>
                        <!-- 상품 설명 이미지 -->
                        <div class="form-group">
                            <label for="descriptionImages">상품 설명 이미지 (최대 5개)</label>
                            <input type="file" class="form-control-file" id="descriptionImages" name="descriptionImages[]" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" multiple onchange="previewDescriptionImages(event)">
                            @error('descriptionImages')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <!-- 상품 설명 이미지 미리 보기 -->
                        <div id="descriptionImagesPreview" class="d-flex overflow-auto" style="white-space: nowrap; max-width: 100%;"></div>
                        <input type="hidden" id="product-id" name="pro_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-danger" id="del-product">삭제</button>
                    <button type="submit" class="btn btn-primary" id="save-product">저장</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/seller.js') }}"></script>
@endsection