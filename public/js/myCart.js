function validateInput(input) {
    const value = input.value;

    // 숫자만 입력되도록 필터링
    if (!/^\d*$/.test(value)) {
        input.value = value.replace(/[^\d]/g, ''); // 숫자가 아닌 문자는 제거
    }

    if (input.value > 100) {
        input.value = 100;
    }

    const cartItem = input.closest('.cart-item'); // 부모 요소 찾기
    const proId = cartItem.getAttribute('data-product-id'); // 데이터 속성에서 상품 ID 가져오기

    // 유효한 숫자일 경우에만 요청 진행
    const quantity = parseInt(input.value, 10) || 0; // 현재 수량
    if (quantity > 0) {
        updateQuantity(proId, quantity); // 수량 업데이트 함수 호출
    }
}

function changeQuantity(element, delta) {
    const cartItem = element.closest('.cart-item'); // 부모 요소 찾기
    const proId = cartItem.getAttribute('data-product-id'); // 데이터 속성에서 상품 ID 가져오기
    const input = element.parentElement.querySelector('.quantity');
    let currentValue = parseInt(input.value, 10) || 0; // 현재 수량
    let newQuantity = currentValue + delta; // 증가된 수량

    // 수량이 1 이하로 내려갈 경우 함수 종료
    if (newQuantity < 1) {
        return; // 수량이 1 미만일 경우 함수 종료
    }

    if (newQuantity > 100) {
        return;
    }

    input.value = newQuantity; // 입력 필드에 즉시 업데이트

    // 수량 업데이트 함수 호출
    updateQuantity(proId, newQuantity); // 수량 업데이트 함수 호출
}

function updateQuantity(proId, quantity) {
    axios.post('/carts/update', {
        proId: proId,
        quantity: quantity 
    })
    .then(res => {
        // 서버 응답이 오면 상태를 동기화
        const input = document.querySelector(`.cart-item[data-product-id="${proId}"] .quantity`);
        calculateTotal();
        if (res.data.quantity !== quantity) {
            input.value = res.data.quantity; // 실제 수량으로 업데이트
            calculateTotal();
        }
    })
    .catch(err => {
        showAlert('알 수 없는 오류가 발생했습니다.', 'error');
        console.error(err); // 에러 처리
    })
}

// 디바운스 ------------------------------------------------------------------------------------------

// const debounceTimers = {}; // 각 상품 ID별 디바운스 타이머 저장
// let isRequestPending = {}; // 각 상품 ID별 요청 진행 상태 저장

// function validateInput(input) {
//     const value = input.value;

//     // 숫자만 입력되도록 필터링
//     if (!/^\d*$/.test(value)) {
//         input.value = value.replace(/[^\d]/g, ''); // 숫자가 아닌 문자는 제거
//     }

//     if (input.value > 100) {
//         input.value = 100;
//     }

//     const cartItem = input.closest('.cart-item'); // 부모 요소 찾기
//     const proId = cartItem.getAttribute('data-product-id'); // 데이터 속성에서 상품 ID 가져오기

//     // 유효한 숫자일 경우에만 요청 진행
//     const quantity = parseInt(input.value, 10) || 0; // 현재 수량
//     if (quantity > 0) {
//         // 디바운스 적용: axios 요청은 100ms 지연 후 실행
//         clearTimeout(debounceTimers[proId]); // 이전 타이머를 클리어
//         debounceTimers[proId] = setTimeout(() => {
//             updateQuantity(proId, quantity); // 수량 업데이트 함수 호출
//         }, 100);
//     }
// }

// function changeQuantity(element, delta) {
//     const cartItem = element.closest('.cart-item'); // 부모 요소 찾기
//     const proId = cartItem.getAttribute('data-product-id'); // 데이터 속성에서 상품 ID 가져오기
//     const input = element.parentElement.querySelector('.quantity');
//     let currentValue = parseInt(input.value, 10) || 0; // 현재 수량
//     let newQuantity = currentValue + delta; // 증가된 수량

//     // 수량이 1 이하로 내려갈 경우 함수 종료
//     if (newQuantity < 1) {
//         return; // 수량이 1 미만일 경우 함수 종료
//     }

//     if (newQuantity > 100) {
//         return;
//     }

//     input.value = newQuantity; // 입력 필드에 즉시 업데이트

//     // axios 요청을 디바운스하는 함수
//     updateQuantity(proId, newQuantity); // 수량 업데이트 함수 호출
// }

// function updateQuantity(proId, quantity) {
//     if (isRequestPending[proId]) return; // 요청이 진행 중이면 무시
//     isRequestPending[proId] = true; // 요청 진행 중으로 설정

//     axios.post('/carts/update', {
//         proId: proId,
//         quantity: quantity 
//     })
//     .then(res => {
//         // 서버 응답이 오면 상태를 동기화
//         const input = document.querySelector(`.cart-item[data-product-id="${proId}"] .quantity`);
//         if (res.data.quantity !== quantity) {
//             input.value = res.data.quantity; // 실제 수량으로 업데이트
//         }
//         console.log(res.data); // 성공시 처리
//     })
//     .catch(err => {
//         showAlert('알 수 없는 오류가 발생했습니다.', 'error');
//         console.error(err); // 에러 처리
//     })
//     .finally(() => {
//         isRequestPending[proId] = false; // 요청 완료 후 다시 요청 가능 상태로 변경
//     });
// }

// /디바운스 ------------------------------------------------------------------------------------------

// 공통 삭제 함수
function deleteItems(proIds) {
    return axios.delete('/carts/delete', {
        data: { proIds: proIds }
    })
    .then(res => {
        showAlert(res.data.msg, 'success');
        proIds.forEach(proId => {
            const cartItem = document.querySelector(`.cart-item[data-product-id='${proId}']`);
            if (cartItem) {
                cartItem.remove(); // 해당 장바구니 항목 삭제
            }
        });
        
        // 장바구니가 비어있을 경우 처리
        const cartContainer = document.querySelector('.cartContainer');
        if (cartContainer && cartContainer.children.length === 1) { // 전체선택 체크박스만 남아있는 경우
            cartContainer.innerHTML = '<p>장바구니가 비어 있습니다.</p>'; // 비어있을 때 메시지 표시
        }
    })
    .catch(err => {
        showAlert(err.response ? err.response.data.msg : '알 수 없는 오류가 발생했습니다.', 'error'); // 에러 메시지 표시
        console.error(err); // 오류 정보 출력
    });
}

// 장바구니에서 개별 삭제
function delBtn(proId) {
    showAlertConfirm('정말 장바구니에서 삭제하시겠습니까?', 'warning', () => {
        deleteItems([proId]);
    });
}

// 선택된 항목 삭제
function delSelectedItems() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked:not(#selectAll)');
    const proIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.getAttribute('data-product-id'));

    if (proIds.length === 0) {
        showAlert('삭제할 항목을 선택하세요.', 'warning');
        return; // 선택된 항목이 없으면 종료
    }

    showAlertConfirm('정말 선택한 항목을 장바구니에서 삭제하시겠습니까?', 'warning', () => {
        deleteItems(proIds);
    });
}

// 금액 계산
function calculateTotal() {
    let total = 0;
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    checkboxes.forEach(checkbox => {
        const cartItem = checkbox.closest('.cart-item');
        const price = parseFloat(cartItem.querySelector('p').innerText.replace(/,/g, '').replace(' 원', ''));
        const quantity = parseInt(cartItem.querySelector('.quantity').value);
        total += price * quantity;
    });
    document.getElementById('totalAmount').innerText = total.toLocaleString() + ' 원';
}

function updateSelectAllCart() {
    const allCheckboxes = document.querySelectorAll('.product-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    selectAllCheckbox.checked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
    calculateTotal(); // Select all에 따라 총 금액 계산
}