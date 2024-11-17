// -----------------------------------------------------------------------------------
// 이벤트명 : wheel
// 설명     : 마우스 휠을 이용하여 가로 스크롤을 구현
//            수직 스크롤 이벤트를 가로 스크롤로 변환
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
const productScrollContainer = document.getElementById('productScrollContainer');
productScrollContainer.addEventListener('wheel', function (event) {
    event.preventDefault(); // 기본 스크롤 방지
    productScrollContainer.scrollBy({
        left    : event.deltaY * 3, // 수직 스크롤 양을 가로 스크롤로 변환
        behavior: 'smooth'          // 부드러운 스크롤 효과
    });
});

// -----------------------------------------------------------------------------------
// 이벤트명 : click
// 설명     : "장바구니에 추가" 버튼 클릭 시 서버에 상품 데이터를 전송하고 결과를 표시
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
document.getElementById('addCartBtn').addEventListener('click', function (event) {
    event.preventDefault(); // 폼 제출 방지
    const proId    = document.getElementById('proId').value;    // 상품 ID
    const quantity = document.getElementById('quantity').value; // 수량

    axios.post('/carts/add', {
        proId   : proId,
        quantity: quantity
    })
    .then(function (res) {
        // SweetAlert 모달로 메시지 표시
        showAlertConfirm(res.data.msg, 'success', () => location.href = '/carts/mycart');
    })
    .catch(function (err) {
        console.error(err);
        showAlert(err.response ? err.response.data.message : '알 수 없는 오류가 발생했습니다.', 'error');
    });
});

// -----------------------------------------------------------------------------------
// 이벤트명 : click
// 설명     : "바로 구매" 버튼 클릭 시 선택된 상품 정보를 서버로 전송하고 결제 페이지로 이동
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
document.getElementById('buyBtn').addEventListener('click', function (event) {
    event.preventDefault(); // 기본 폼 제출 방지

    const proId    = document.getElementById('proId').value;    // 상품 ID
    const quantity = document.getElementById('quantity').value; // 수량

    // 동적으로 폼을 생성하고 POST 방식으로 전송
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/orders/checkout';

    // CSRF 토큰 추가
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type  = 'hidden';
    csrfInput.name  = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // 상품 데이터를 JSON 형식으로 추가
    const productInput = document.createElement('input');
    productInput.type  = 'hidden';
    productInput.name  = 'items[]';
    productInput.value = JSON.stringify({ id: proId, quantity: quantity }); // JSON으로 id와 quantity 전달
    form.appendChild(productInput);

    // 동적으로 생성한 폼을 문서에 추가하고 전송
    document.body.appendChild(form);
    form.submit();
});

// -----------------------------------------------------------------------------------
// 함수명   : renderRating
// 설명     : 별점 데이터를 기반으로 별 아이콘을 렌더링
//
// param    : number rating - 별점 값 (0 ~ 5)
//
// return   : 없음
// -----------------------------------------------------------------------------------
function renderRating(rating) {
    const icons = document.querySelectorAll('.rating .star-icon'); // 모든 별 가져오기

    icons.forEach((icon, index) => {
        if (index < Math.floor(rating * 2)) {
            icon.classList.add('filled'); // 별점에 따라 채움 클래스 추가
        }
    });
}

// -----------------------------------------------------------------------------------
// 별점 데이터 초기화
// 설명     : 서버에서 전달받은 별점 데이터를 렌더링
// -----------------------------------------------------------------------------------
const productRating = document.querySelector('span[data-rating]').getAttribute('data-rating');
renderRating(productRating);
