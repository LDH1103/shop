document.addEventListener('DOMContentLoaded', function () {
    let page = 1; // 페이지 번호 초기화
    let isLoading = false; // 로딩 상태
    const categorySelect = document.getElementById('category_select');
    const loadingElement = document.getElementById("loading");
    const productListContainer = document.getElementById("product_list_container");

    // 상품 추가 로드 함수
    function loadProducts() {
        if (isLoading) return; // 이미 로딩 중이면 중복 요청 방지

        isLoading = true;
        loadingElement.style.display = "block"; // 로딩 표시 보이기

        const params = {
            categoryId: categorySelect.value,
            page: page
        };

        // AJAX 요청
        axios.get('/products/get', { params })
        .then(res => {
            if (res.data.success) {
                const products = res.data.data; // 응답에서 상품 데이터 추출
                products.forEach(product => {
                    const productCard = `
                        <div class="col-md-3 product_list">
                            <div class="card mb-3" onclick="window.location='${product.detail}'" style="cursor: pointer;">
                                <img src="${product.img}" class="card-img-top" alt="${product.name}" id="card_img">
                                <div class="card-body">
                                    <h5 class="card-title">${product.name}</h5>
                                    <p class="card-text">${product.price} 원</p>
                                </div>
                            </div>
                        </div>
                    `;
                    productListContainer.insertAdjacentHTML('beforeend', productCard);
                });
                page++; // 다음 페이지로 증가
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
            isLoading = false; // 로딩 완료
        });
    }

    // 스크롤 이벤트 감지
    window.addEventListener('scroll', function () {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
            loadProducts(); // 스크롤이 하단에 도달할 때 상품 로드
        }
    });

    // 카테고리 변경 시 리스트 초기화 및 첫 페이지 로드
    categorySelect.addEventListener('change', function () {
        page = 1; // 페이지 번호 초기화
        productListContainer.innerHTML = ''; // 기존 상품 리스트 제거
        loadProducts(); // 새로운 카테고리에 해당하는 첫 페이지 상품 로드
    });

    // 초기 로드
    loadProducts(); // 첫 페이지 상품 로드
});
