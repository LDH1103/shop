// CSRF 토큰 설정
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// 체크박스 전체선택
function selectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox:not(#selectAll)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// 선택된 체크박스 상태 업데이트
function updateSelectAll() {
    const checkboxes = document.querySelectorAll('.product-checkbox:not(#selectAll)');
    const selectAllCheckbox = document.getElementById('selectAll');

    // 체크박스 중 하나라도 체크되지 않은 경우 전체 선택 체크박스 해제
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    selectAllCheckbox.checked = allChecked; // 모든 체크박스가 체크된 경우에만 체크
}

// 가격 입력 처리
const input = document.querySelector('#product-price');
const resultDiv = document.querySelector('#resultDiv');
const maxValue = 100000000; // 가격의 최댓값

// #product-price가 존재하는 페이지에서만 실행되게 처리
if (input) {
    input.addEventListener('input', function(e) {
        input.value = input.value.replace(/[^0-9]/g, ''); // 숫자만 입력가능
        let value = e.target.value.replace(/,/g, ''); // 입력값에서 쉼표를 제거

        if (Number(value) > maxValue) {
            input.value = maxValue.toLocaleString('ko-KR');
            resultDiv.innerHTML = '일억 원';
            showAlert(`최대 입력값은 ${maxValue.toLocaleString('ko-KR')}입니다.`, 'error');
            return;
        }

        if (!isNaN(value) && value !== '') {
            e.target.value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            resultDiv.innerHTML = numberToKorean(value) + ' 원';
        } else {
            e.target.value = value; // 유효하지 않은 경우 입력값을 그대로 유지
        }    
    });
}

// 숫자를 한국어로 변환하는 함수
function numberToKorean(num) {
    const units = ['', '만', '억']; // 만 단위로 구분
    const smallUnits = ['', '십', '백', '천'];
    const numbers = ['영', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];

    if (num === '0') return numbers[0];

    let result = '';
    let unitIndex = 0;

    while (num.length > 0) {
        let chunk = num.slice(-4); // 4자리씩 끊음
        num = num.slice(0, -4);

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

// 전화번호 입력 형식 설정 함수
function formatPhoneNumber(inputElement) {
    inputElement.addEventListener('input', function(e) {
        // 입력된 값에서 하이픈 제거
        let value = e.target.value.replace(/-/g, '');

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

// 다음 주소 API
function daumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

            // 각 주소의 노출 규칙에 따라 주소를 조합한다.
            // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
            var addr = ''; // 주소 변수
            var extraAddr = ''; // 참고항목 변수

            //사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
            if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                addr = data.roadAddress;
            } else { // 사용자가 지번 주소를 선택했을 경우(J)
                addr = data.jibunAddress;
            }

            // 사용자가 선택한 주소가 도로명 타입일때 참고항목을 조합한다.
            if(data.userSelectedType === 'R'){
                // 법정동명이 있을 경우 추가한다. (법정리는 제외)
                // 법정동의 경우 마지막 문자가 "동/로/가"로 끝난다.
                if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                    extraAddr += data.bname;
                }
                // 건물명이 있고, 공동주택일 경우 추가한다.
                if(data.buildingName !== '' && data.apartment === 'Y'){
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                // 표시할 참고항목이 있을 경우, 괄호까지 추가한 최종 문자열을 만든다.
                if(extraAddr !== ''){
                    extraAddr = '(' + extraAddr + ')';
                }
                // 조합된 참고항목을 해당 필드에 넣는다.
                document.getElementById("extraAddress").value = extraAddr;
            
            } else {
                document.getElementById("extraAddress").value = '';
            }

            // 우편번호와 주소 정보를 해당 필드에 넣는다.
            document.getElementById('postcode').value = data.zonecode;
            document.getElementById("address").value = addr;
            // 커서를 상세주소 필드로 이동한다.
            document.getElementById("detailAddress").focus();
        }
    }).open();
}

// 결제페이지로 이동
// function goToCheckout() {
//     const selectedItems = [];
//     const checkboxes = document.querySelectorAll('.product-checkbox:checked');
//     checkboxes.forEach((checkbox) => {
//         const productId = checkbox.getAttribute('data-product-id');
//         const quantity = checkbox.closest('.cart-item').querySelector('.quantity').value;
//         selectedItems.push({ id: productId, quantity: quantity });
//     });

//     if (selectedItems.length > 0) {
//         const queryString = selectedItems.map(item => `items[]=${item.id},${item.quantity}`).join('&');
//         window.location.href = `/orders/checkout?${queryString}`;
//     } else {
//         showAlert('선택된 상품이 없습니다.', 'warning');
//     }
// }

function goToCheckout() {
    const selectedItems = [];
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');

    // 선택된 상품의 ID와 수량을 배열에 추가
    checkboxes.forEach((checkbox) => {
        const productId = checkbox.getAttribute('data-product-id');
        const quantity = checkbox.closest('.cart-item').querySelector('.quantity').value;
        selectedItems.push({ id: productId, quantity: quantity });
    });

    if (selectedItems.length > 0) {
        // 동적으로 폼을 생성하고 POST 방식으로 전송
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/orders/checkout';

        // CSRF 토큰
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type  = 'hidden';
        csrfInput.name  = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        // 선택된 상품 목록을 폼에 추가
        selectedItems.forEach(item => {
            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'items[]';
            productIdInput.value = JSON.stringify(item); // id, quantity를 JSON으로 전달
            form.appendChild(productIdInput);
        });

        // 동적으로 생성한 폼을 문서에 추가하고 전송
        document.body.appendChild(form);
        form.submit();
    } else {
        showAlert('선택된 상품이 없습니다.', 'warning');
    }
}