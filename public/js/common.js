// -----------------------------------------------------------------------------------
// CSRF 토큰 설정
// 설명     : Axios 요청에 CSRF 토큰을 자동으로 포함하도록 설정
// -----------------------------------------------------------------------------------
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// -----------------------------------------------------------------------------------
// 함수명   : selectAll
// 설명     : "전체 선택" 체크박스를 클릭하면 개별 체크박스 상태를 변경
//
// param    : HTMLInputElement selectAllCheckbox - "전체 선택" 체크박스
//
// return   : 없음
// -----------------------------------------------------------------------------------
function selectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox:not(#selectAll)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : updateSelectAll
// 설명     : 개별 체크박스의 상태에 따라 "전체 선택" 체크박스 상태를 업데이트
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function updateSelectAll() {
    const checkboxes       = document.querySelectorAll('.product-checkbox:not(#selectAll)');
    const selectAllCheckbox = document.getElementById('selectAll');

    // 체크박스 중 하나라도 체크되지 않은 경우 "전체 선택" 체크박스 해제
    const allChecked       = Array.from(checkboxes).every(checkbox => checkbox.checked);
    selectAllCheckbox.checked = allChecked; // 모든 체크박스가 체크된 경우에만 체크
}

// -----------------------------------------------------------------------------------
// 가격 입력 처리
// 설명     : #product-price 입력란에 숫자만 입력되도록 처리하고, 천 단위로 콤마 추가
//            최댓값을 초과하면 경고 메시지를 표시
// -----------------------------------------------------------------------------------
const input      = document.querySelector('#product-price');
const resultDiv  = document.querySelector('#resultDiv');
const maxValue   = 100000000; // 가격의 최댓값

if (input) {
    input.addEventListener('input', function (e) {
        input.value = input.value.replace(/[^0-9]/g, ''); // 숫자만 입력 가능
        let value   = e.target.value.replace(/,/g, '');  // 입력값에서 쉼표 제거

        if (Number(value) > maxValue) {
            input.value         = maxValue.toLocaleString('ko-KR');
            resultDiv.innerHTML = '일억 원';
            showAlert(`최대 입력값은 ${maxValue.toLocaleString('ko-KR')}입니다.`, 'error');
            return;
        }

        if (!isNaN(value) && value !== '') {
            e.target.value      = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            resultDiv.innerHTML = numberToKorean(value) + ' 원';
        } else {
            e.target.value      = value; // 유효하지 않은 경우 입력값 그대로 유지
        }
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : numberToKorean
// 설명     : 숫자를 한국어로 변환
//
// param    : string num - 변환할 숫자 문자열
//
// return   : string - 변환된 한국어 문자열
// -----------------------------------------------------------------------------------
function numberToKorean(num) {
    const units      = ['', '만', '억']; // 만 단위로 구분
    const smallUnits = ['', '십', '백', '천'];
    const numbers    = ['영', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];

    if (num === '0') return numbers[0];

    let result    = '';
    let unitIndex = 0;

    while (num.length > 0) {
        let chunk = num.slice(-4); // 4자리씩 끊음
        num       = num.slice(0, -4);

        if (chunk !== '0000') {
            let chunkResult = '';
            for (let i = 0; i < chunk.length; i++) {
                let digit = chunk[i];
                if (digit !== '0') {
                    chunkResult += numbers[digit] + smallUnits[chunk.length - i - 1];
                }
            }

            if (chunkResult) {
                result = chunkResult + units[unitIndex] + result;
            }
        }

        unitIndex++;
    }

    return result;
}

// -----------------------------------------------------------------------------------
// 함수명   : formatPhoneNumber
// 설명     : 전화번호 입력란에 자동으로 하이픈을 추가하여 형식 지정
//
// param    : HTMLInputElement inputElement - 전화번호 입력 필드
//
// return   : 없음
// -----------------------------------------------------------------------------------
function formatPhoneNumber(inputElement) {
    inputElement.addEventListener('input', function (e) {
        let value = e.target.value.replace(/-/g, ''); // 하이픈 제거

        // 최대 자릿수 제한 (11자리)
        if (value.length > 11) {
            value = value.slice(0, 11);
        }

        // 하이픈 추가 로직
        if (value.length < 4) {
            e.target.value = value;
        } else if (value.length < 8) {
            e.target.value = value.replace(/(\d{3})(\d+)/, '$1-$2'); // 첫 번째 하이픈
        } else {
            e.target.value = value.replace(/(\d{3})(\d{4})(\d+)/, '$1-$2-$3'); // 두 번째 하이픈
        }
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : goToCheckout
// 설명     : 선택된 상품 정보를 서버로 전송하여 결제 페이지로 이동
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function goToCheckout() {
    const selectedItems = [];
    const checkboxes    = document.querySelectorAll('.product-checkbox:checked');

    // 선택된 상품의 ID와 수량을 배열에 추가
    checkboxes.forEach((checkbox) => {
        const productId = checkbox.getAttribute('data-product-id');
        const quantity  = checkbox.closest('.cart-item').querySelector('.quantity').value;
        selectedItems.push({ id: productId, quantity: quantity });
    });

    if (selectedItems.length > 0) {
        // 동적으로 폼 생성 후 POST 방식으로 전송
        const form = document.createElement('form');
        form.method = "POST";
        form.action = "/orders/checkout";

        // CSRF 토큰
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type  = 'hidden';
        csrfInput.name  = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        // 선택된 상품 목록 추가
        selectedItems.forEach(item => {
            const productIdInput = document.createElement('input');
            productIdInput.type  = 'hidden';
            productIdInput.name  = 'items[]';
            productIdInput.value = JSON.stringify(item); // id, quantity를 JSON으로 전달
            form.appendChild(productIdInput);
        });

        document.body.appendChild(form);
        form.submit();
    } else {
        showAlert('선택된 상품이 없습니다.', 'warning');
    }
}
