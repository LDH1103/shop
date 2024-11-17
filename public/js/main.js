// -----------------------------------------------------------------------------------
// 이벤트명 : DOMContentLoaded
// 설명     : 페이지 로드 시 초기화 작업을 수행하고, 상품 목록을 동적으로 로드
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    let page                    = 1; // 페이지 번호 초기화
    let isLoading               = false; // 로딩 상태
    const categorySelect        = document.getElementById('category_select');     // 카테고리 선택 드롭다운
    const loadingElement        = document.getElementById("loading");            // 로딩 표시 엘리먼트
    const productListContainer  = document.getElementById("product_list_container"); // 상품 목록 컨테이너

    // -----------------------------------------------------------------------------------
    // 함수명   : renderRating
    // 설명     : 별점 데이터를 기반으로 별 아이콘을 렌더링
    //
    // param    : HTMLElement ratingElement - 별점을 표시할 DOM 엘리먼트
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    function renderRating(ratingElement) {
        const rating = parseFloat(ratingElement.getAttribute('data-rating')); // data-rating 값 가져오기
        const icons  = ratingElement.querySelectorAll('.star-icon');          // 별 아이콘 가져오기

        icons.forEach((icon, index) => {
            if (index < Math.floor(rating * 2)) {
                icon.classList.add('filled'); // 별점에 따라 아이콘 채우기
            }
        });
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : applyRatings
    // 설명     : 상품 목록의 모든 별점을 렌더링
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    function applyRatings() {
        const ratingElements = document.querySelectorAll('.rating'); // 모든 .rating 요소 가져오기
        ratingElements.forEach(renderRating);
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : loadProducts
    // 설명     : 서버에서 상품 데이터를 가져와 목록에 추가
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    function loadProducts() {
        if (isLoading) return; // 이미 로딩 중이면 중복 요청 방지

        isLoading = true;
        loadingElement.style.display = "block"; // 로딩 표시 보이기

        const params = {
            categoryId: categorySelect.value,
            page      : page
        };

        // AJAX 요청
        axios.get('/products/get', { params })
            .then(res => {
                console.log('Page:', page);    // 현재 페이지 번호 출력
                console.log('Response:', res.data); // 응답 데이터 확인
                if (res.data.success) {
                    const products = res.data.data; // 응답에서 상품 데이터 추출
                    products.forEach(product => {
                        const productCard = `
                            <div class="col-md-3 product_list">
                                <div class="card mb-3" onclick="window.location='${product.detail}'" style="cursor: pointer;">
                                    <img src="${product.img}" class="card-img-top" alt="${product.name}" id="card_img">
                                    <div class="card-body">
                                        <h5 class="card-title">${product.name}</h5>
                                        <div class="rating" data-rating="${product.avg_rating}">
                                            <label class="rating__label rating__label--half"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--full"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--half"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--full"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--half"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--full"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--half"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--full"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--half"><span class="star-icon"></span></label>
                                            <label class="rating__label rating__label--full"><span class="star-icon"></span></label>
                                        </div>
                                        <p class="card-text">${product.price} 원</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        productListContainer.insertAdjacentHTML('beforeend', productCard);
                    });
                    applyRatings(); // 상품 로드 후 별점 렌더링 호출
                    page++;         // 다음 페이지로 증가
                } else {
                    alert('더 이상 상품이 없습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('네트워크 오류가 발생했습니다.');
            })
            .finally(() => {
                loadingElement.style.display = "none"; // 로딩 표시 숨기기
                isLoading = false;                   // 로딩 완료
            });
    }

    // -----------------------------------------------------------------------------------
    // 이벤트명 : scroll
    // 설명     : 스크롤이 하단에 도달하면 추가 상품을 로드
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    window.addEventListener('scroll', function () {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
            loadProducts(); // 스크롤이 하단에 도달할 때 상품 로드
        }
    });

    // -----------------------------------------------------------------------------------
    // 이벤트명 : change
    // 설명     : 카테고리를 변경하면 상품 목록을 초기화하고 새로운 상품을 로드
    //
    // param    : 없음
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    categorySelect.addEventListener('change', function () {
        page = 1; // 페이지 번호 초기화
        productListContainer.innerHTML = ''; // 기존 상품 리스트 제거
        loadProducts(); // 새로운 카테고리에 해당하는 첫 페이지 상품 로드
    });

    // 초기 로드
    loadProducts(); // 첫 페이지 상품 로드
});
