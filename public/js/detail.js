const productScrollContainer = document.getElementById('productScrollContainer');

// 마우스 휠로 가로 스크롤
productScrollContainer.addEventListener('wheel', function(event) {
    event.preventDefault(); // 기본 스크롤 방지
    productScrollContainer.scrollBy({
        left: event.deltaY * 3, // 수직 스크롤 양을 가로 스크롤로 변환
        behavior: 'smooth' // 부드러운 스크롤 효과
    });
});

// 장바구니에 추가
document.getElementById('addCartBtn').addEventListener('click', function() {
    event.preventDefault(); // 폼 제출 방지
    const proId = document.getElementById('proId').value;
    const quantity = document.getElementById('quantity').value;

    axios.post('/carts/add', {
        proId: proId,
        quantity: quantity
    })
    .then(function(res) {
        // SweetAlert 모달로 메시지 표시
        showAlertConfirm(res.data.msg, 'success', () => location.href='/carts/mycart');
    })
    .catch(function (err) {
        console.error(err);
        showAlert(err.response ? err.response.data.message : '알 수 없는 오류가 발생했습니다.', 'error');
    });
});