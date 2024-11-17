// -----------------------------------------------------------------------------------
// 함수명   : validateInput
// 설명     : 수량 입력 필드에서 유효성을 검사하고, 수량을 서버에 업데이트
//
// param    : HTMLInputElement input - 입력 필드 요소
//
// return   : 없음
// -----------------------------------------------------------------------------------
function validateInput(input) {
    const value = input.value;

    // 숫자만 입력되도록 필터링
    if (!/^\d*$/.test(value)) {
        input.value = value.replace(/[^\d]/g, ''); // 숫자가 아닌 문자는 제거
    }

    if (input.value > 100) {
        input.value = 100; // 최대 수량 제한
    }

    const cartItem = input.closest('.cart-item');            // 부모 요소 찾기
    const proId    = cartItem.getAttribute('data-product-id'); // 데이터 속성에서 상품 ID 가져오기

    // 유효한 숫자일 경우에만 요청 진행
    const quantity = parseInt(input.value, 10) || 0; // 현재 수량
    if (quantity > 0) {
        updateQuantity(proId, quantity); // 수량 업데이트 함수 호출
    }
}

// -----------------------------------------------------------------------------------
// 함수명   : changeQuantity
// 설명     : 버튼 클릭으로 수량을 증가 또는 감소시키고, 서버에 업데이트 요청
//
// param    : HTMLElement element - 버튼 요소
//            number delta        - 수량 변경값 (1 또는 -1)
//
// return   : 없음
// -----------------------------------------------------------------------------------
function changeQuantity(element, delta) {
    const cartItem   = element.closest('.cart-item');            // 부모 요소 찾기
    const proId      = cartItem.getAttribute('data-product-id'); // 데이터 속성에서 상품 ID 가져오기
    const input      = element.parentElement.querySelector('.quantity');
    let currentValue = parseInt(input.value, 10) || 0; // 현재 수량
    let newQuantity  = currentValue + delta;          // 증가된 수량

    // 수량이 1 이하로 내려갈 경우 또는 100 초과 시 함수 종료
    if (newQuantity < 1 || newQuantity > 100) {
        return;
    }

    input.value = newQuantity; // 입력 필드에 즉시 업데이트

    // 수량 업데이트 함수 호출
    updateQuantity(proId, newQuantity);
}

// -----------------------------------------------------------------------------------
// 함수명   : updateQuantity
// 설명     : 서버에 수량 업데이트 요청을 전송하고, 결과를 동기화
//
// param    : string proId  - 상품 ID
//            number quantity - 변경된 수량
//
// return   : 없음
// -----------------------------------------------------------------------------------
function updateQuantity(proId, quantity) {
    axios.post('/carts/update', {
        proId   : proId,
        quantity: quantity 
    })
    .then(res => {
        // 서버 응답이 오면 상태를 동기화
        const input = document.querySelector(`.cart-item[data-product-id="${proId}"] .quantity`);
        calculateTotal(); // 총합 계산 호출
        if (res.data.quantity !== quantity) {
            input.value = res.data.quantity; // 실제 수량으로 업데이트
            calculateTotal();
        }
    })
    .catch(err => {
        showAlert('알 수 없는 오류가 발생했습니다.', 'error'); // 에러 메시지 표시
        console.error(err); // 에러 로그
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : deleteItems
// 설명     : 서버에 삭제 요청을 전송하고, 성공 시 해당 상품을 DOM에서 제거
//
// param    : array proIds - 삭제할 상품 ID 배열
//
// return   : Promise - 삭제 요청 결과
// -----------------------------------------------------------------------------------
function deleteItems(proIds) {
    return axios.delete('/carts/delete', {
        data: { proIds: proIds }
    })
    .then(res => {
        showAlert(res.data.msg, 'success'); // 성공 메시지 표시
        proIds.forEach(proId => {
            const cartItem = document.querySelector(`.cart-item[data-product-id='${proId}']`);
            if (cartItem) {
                cartItem.remove(); // 해당 장바구니 항목 삭제
            }
        });

        // 장바구니가 비어있을 경우 처리
        const cartContainer = document.querySelector('.cartContainer');
        if (cartContainer && cartContainer.children.length === 1) { // 전체 선택 체크박스만 남아있는 경우
            cartContainer.innerHTML = '<p>장바구니가 비어 있습니다.</p>'; // 비어있을 때 메시지 표시
        }
    })
    .catch(err => {
        showAlert(err.response ? err.response.data.msg : '알 수 없는 오류가 발생했습니다.', 'error'); // 에러 메시지 표시
        console.error(err); // 오류 정보 출력
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : delBtn
// 설명     : 개별 삭제 버튼 클릭 시 확인 메시지 후 삭제 요청
//
// param    : string proId - 삭제할 상품 ID
//
// return   : 없음
// -----------------------------------------------------------------------------------
function delBtn(proId) {
    showAlertConfirm('정말 장바구니에서 삭제하시겠습니까?', 'warning', () => {
        deleteItems([proId]);
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : delSelectedItems
// 설명     : 선택된 항목을 확인 후 삭제 요청
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function delSelectedItems() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked:not(#selectAll)');
    const proIds             = Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-product-id'));

    if (proIds.length === 0) {
        showAlert('삭제할 항목을 선택하세요.', 'warning');
        return; // 선택된 항목이 없으면 종료
    }

    showAlertConfirm('정말 선택한 항목을 장바구니에서 삭제하시겠습니까?', 'warning', () => {
        deleteItems(proIds);
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : calculateTotal
// 설명     : 선택된 상품의 총 금액을 계산하여 화면에 표시
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function calculateTotal() {
    let total = 0;
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    checkboxes.forEach(checkbox => {
        const cartItem = checkbox.closest('.cart-item'); // 선택된 항목의 부모 요소
        const price    = parseFloat(cartItem.querySelector('p').innerText.replace(/,/g, '').replace(' 원', '')); // 상품 가격
        const quantity = parseInt(cartItem.querySelector('.quantity').value); // 수량
        total += price * quantity; // 총 금액 계산
    });
    document.getElementById('totalAmount').innerText = total.toLocaleString() + ' 원'; // 금액 업데이트
}

// -----------------------------------------------------------------------------------
// 함수명   : updateSelectAllCart
// 설명     : "전체 선택" 체크박스 상태를 업데이트하고 총 금액 계산
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function updateSelectAllCart() {
    const allCheckboxes         = document.querySelectorAll('.product-checkbox'); // 모든 체크박스
    const selectAllCheckbox     = document.getElementById('selectAll');           // 전체 선택 체크박스
    selectAllCheckbox.checked   = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
    calculateTotal(); // "전체 선택" 상태에 따라 총 금액 계산
}