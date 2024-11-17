// -----------------------------------------------------------------------------------
// 함수명   : selectAddress
// 설명     : 선택한 주소 정보를 화면에 표시하고, 모달 창을 닫는 함수
//            선택된 주소의 상세 정보와 기본 배송지 여부를 DOM에 업데이트
//
// param    : string recipient    - 수령인 이름
//            string postcode     - 우편번호
//            string address      - 상세 주소
//            string phone        - 수령인의 연락처
//            boolean isDefault   - 기본 배송지 여부 (true: 기본 배송지)
//            number|string addressId - 주소 고유 ID
//
// return   : 없음
// -----------------------------------------------------------------------------------
function selectAddress(recipient, postcode, address, phone, isDefault, addressId) {
    // 선택된 주소 정보를 DOM에 업데이트
    document.getElementById('recipient').textContent     = recipient;
    document.getElementById('postcode').textContent      = postcode;
    document.getElementById('address').textContent       = address;
    document.getElementById('phone').textContent         = phone;
    document.getElementById('addressId').setAttribute('data-value', addressId);

    // 기본 배송지 여부에 따라 표시/숨기기
    const defaultLabel = document.getElementById('defaultLabel');
    if (isDefault) {
        defaultLabel.innerHTML = "<strong>기본 배송지</strong>";
    } else {
        defaultLabel.innerHTML = "";
    }

    // 모달 창 닫기
    $('#addressModal').modal('hide');
}

// -----------------------------------------------------------------------------------
// 함수명   : toggleAddAddressForm
// 설명     : "추가하기" 버튼 클릭 시 주소 목록을 숨기고 새 주소 추가 폼을 화면에 표시
//            기존 주소 목록과 버튼은 숨기며, 모달 제목을 "배송지 추가"로 변경
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function toggleAddAddressForm() {
    const addressList       = document.getElementById('addressList');      // 주소 목록 영역
    const addAddressForm    = document.getElementById('addAddressForm');   // 새 주소 추가 폼
    const addBtn            = document.getElementById('addBtn');           // "추가하기" 버튼
    const addressModalLabel = document.getElementById('addressModalLabel'); // 모달 제목

    // 주소 목록과 "추가하기" 버튼 숨기기
    addressList.style.display = 'none';
    addBtn.style.display      = 'none';

    // 새 주소 추가 폼 표시 및 모달 제목 변경
    addAddressForm.style.display    = 'block';
    addressModalLabel.innerHTML     = '배송지 추가';
}

// -----------------------------------------------------------------------------------
// 함수명   : 모달 초기화 이벤트 핸들러
// 설명     : 주소 모달이 열릴 때 화면을 기본 상태로 초기화
//            주소 목록과 "추가하기" 버튼은 표시하고, 추가 폼은 숨기며, 모달 제목을 "배송지 선택"으로 설정
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
$('#addressModal').on('shown.bs.modal', function () {
    const addressList       = document.getElementById('addressList');      // 주소 목록 영역
    const addAddressForm    = document.getElementById('addAddressForm');   // 새 주소 추가 폼
    const addBtn            = document.getElementById('addBtn');           // "추가하기" 버튼
    const addressModalLabel = document.getElementById('addressModalLabel'); // 모달 제목

    // 초기화: 주소 목록과 버튼 표시, 추가 폼 숨기기
    addressList.style.display = 'block';
    addBtn.style.display      = 'block';
    addAddressForm.style.display = 'none';

    // 모달 제목 초기화
    addressModalLabel.innerHTML = '배송지 선택';
});

// -----------------------------------------------------------------------------------
// 함수명   : addNewAddress
// 설명     : 새 주소 추가 폼을 제출하고, 서버로부터 받은 데이터를 화면에 업데이트
//            서버에서 새 주소가 추가되면 이를 선택된 상태로 표시하고 모달을 닫음
//
// param    : Event e - 이벤트 객체 (submit 이벤트 방지용)
//
// return   : 없음
// -----------------------------------------------------------------------------------
function addNewAddress(e) {
    e.preventDefault(); // 폼의 기본 동작 방지

    const formData = new FormData(document.getElementById('addAddressForm'));

    axios.post('/users/addAddress', formData)
        .then(res => {
            const newAddress = res.data.newAddress; // 서버로부터 받은 새 주소 데이터
            console.log(newAddress);

            // 화면에 추가된 주소 정보를 표시
            selectAddress(
                newAddress.recipient,
                newAddress.postcode,
                `${newAddress.address}, ${newAddress.detailAddress}${newAddress.extraAddress ? ', ' + newAddress.extraAddress : ''}`,
                newAddress.phone,
                newAddress.default === 1, // 기본 배송지 여부
                newAddress.add_id
            );

            // 성공 메시지를 표시한 후 모달 닫기
            showAlertThen(res.data.msg, 'success', () => $('#addAddressModal').modal('hide'));

            // 추가 폼 초기화
            document.getElementById('addAddressForm').reset();
        })
        .catch(err => {
            if (err.response && err.response.status === 422) {
                // 유효성 검사 에러 처리
                let errorMessages = '';
                const errors = err.response.data.errors;
                Object.keys(errors).forEach(key => {
                    errorMessages += errors[key].join('<br>') + '<br>';
                });
                showAlert(errorMessages);
            } else {
                // 기타 에러 처리
                showAlert(err);
            }
            console.error(err);
        });
}

// -----------------------------------------------------------------------------------
// 함수명   : daumPostcode
// 설명     : 다음 주소 API를 통해 우편번호와 주소를 입력받아 폼에 자동으로 채워주는 함수
//            사용자가 선택한 주소 유형(도로명 또는 지번)에 따라 주소를 설정하고,
//            추가 주소 항목(참고항목)을 처리
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function daumPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            let addr      = '';  // 주소 변수
            let extraAddr = '';  // 참고 항목 변수

            // 사용자가 선택한 주소 유형에 따라 주소 설정
            if (data.userSelectedType === 'R') {
                addr = data.roadAddress; // 도로명 주소
            } else {
                addr = data.jibunAddress; // 지번 주소
            }

            // 도로명 주소인 경우 참고 항목 처리
            if (data.userSelectedType === 'R') {
                if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                    extraAddr += data.bname;
                }
                if (data.buildingName !== '' && data.apartment === 'Y') {
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if (extraAddr !== '') {
                    extraAddr = '(' + extraAddr + ')';
                }
                document.getElementById("addExtraAddress").value = extraAddr; // 참고 주소 입력
            } else {
                document.getElementById("addExtraAddress").value = ''; // 참고 주소 초기화
            }

            // 우편번호와 주소 입력
            document.getElementById("addPostcode").value = data.zonecode;  // 우편번호
            document.getElementById("addAddress").value = addr;           // 기본 주소

            // 상세 주소 입력란에 포커스
            document.getElementById("addDetailAddress").focus();
        }
    }).open();
}

// -----------------------------------------------------------------------------------
// 이벤트 리스너 설정
// 설명     : DOMContentLoaded 이벤트 발생 시, 초기화 작업을 수행
//            전화번호 입력 필드에 포맷 적용, 우편번호 찾기 버튼 클릭 이벤트 설정,
//            새 주소 추가 폼 제출 이벤트 핸들러를 설정
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const phoneInput = document.getElementById('addPhone'); // 전화번호 입력 필드
    formatPhoneNumber(phoneInput); // 전화번호 입력 필드에 포맷 적용

    // 우편번호 찾기 버튼 클릭 이벤트 설정
    const postcodeButton = document.querySelector('input[onclick="daumPostcode()"]');
    if (postcodeButton) {
        postcodeButton.onclick = daumPostcode; // 다음 주소 API 호출
    }

    // 새 주소 추가 폼 제출 이벤트 핸들러 설정
    const addAddressForm = document.getElementById('addAddressForm');
    if (addAddressForm) {
        addAddressForm.addEventListener('submit', addNewAddress);
    }
});

// -----------------------------------------------------------------------------------
// 이벤트명 : shown.bs.modal
// 설명     : 주소 모달이 열릴 때마다 서버에서 주소 목록을 가져오는 함수 호출
//            주소 목록을 동적으로 업데이트하여 최신 정보를 표시
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
$('#addressModal').on('shown.bs.modal', function () {
    fetchAddresses(); // 모달이 열릴 때 주소 목록을 가져오는 함수 호출
});

// -----------------------------------------------------------------------------------
// 함수명   : fetchAddresses
// 설명     : 서버에서 주소 목록 데이터를 가져와 화면에 업데이트
//            서버에서 반환된 주소 데이터를 기반으로 주소 목록을 최신 상태로 갱신
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function fetchAddresses() {
    axios.get('/users/address')
        .then(res => {
            const addresses = res.data.addresses; // 서버에서 반환된 주소 목록 데이터
            updateAddressList(addresses);        // 주소 목록을 업데이트하는 함수 호출
        })
        .catch(error => {
            console.error("주소 목록을 가져오는 중 오류 발생:", error);
            showAlert("주소 목록을 불러오는 중 문제가 발생했습니다."); // 사용자에게 오류 메시지 표시
        });
}

// -----------------------------------------------------------------------------------
// 함수명   : updateAddressList
// 설명     : 서버에서 가져온 주소 목록 데이터를 기반으로 HTML을 생성하여 화면에 표시
//            기존 주소 목록을 초기화한 뒤, 각 주소를 카드 형태로 추가
//
// param    : array addresses - 주소 목록 배열
//              - recipient    : 수령인 이름
//              - postcode     : 우편번호
//              - address      : 기본 주소
//              - detailAddress: 상세 주소
//              - extraAddress : 추가 주소 정보 (참고 항목)
//              - phone        : 연락처
//              - default      : 기본 배송지 여부 ('1'이면 기본 배송지)
//              - add_id       : 주소 고유 ID
//
// return   : 없음
// -----------------------------------------------------------------------------------
function updateAddressList(addresses) {
    const addressList     = document.getElementById('addressList'); // 주소 목록 DOM 요소
    addressList.innerHTML = ''; // 기존 주소 목록 초기화

    addresses.forEach(address => {
        const isDefault   = address.default === '1'; // 기본 배송지 여부 확인
        const addressHTML = `
            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">${address.recipient}</h5>
                        <span class="card-text d-block">${address.postcode}</span>
                        <span class="card-text d-block">${address.address}, ${address.detailAddress}${address.extraAddress ? ', ' + address.extraAddress : ''}</span>
                        <span class="card-text d-block">${address.phone}</span>
                        ${isDefault ? '<span class="card-text d-block"><strong>기본 배송지</strong></span>' : ''}
                    </div>
                    <button class="btn btn-primary" onclick="selectAddress(
                        '${address.recipient}',
                        '${address.postcode}',
                        '${address.address}, ${address.detailAddress}${address.extraAddress ? ', ' + address.extraAddress : ''}',
                        '${address.phone}',
                        ${isDefault},
                        '${address.add_id}'
                    )">선택</button>
                </div>
            </div>`;
        addressList.insertAdjacentHTML('beforeend', addressHTML); // 주소 카드를 DOM에 추가
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : selectAddress
// 설명     : 선택한 주소 정보를 화면에 표시하고, 모달을 닫음
//            수령인 정보, 주소, 연락처 등을 업데이트하며, 기본 배송지 여부를 표시
//
// param    : string recipient - 수령인 이름
//            string postcode  - 우편번호
//            string address   - 상세 주소
//            string phone     - 수령인의 연락처
//            boolean isDefault- 기본 배송지 여부 (true: 기본 배송지)
//            string addressId - 주소 고유 ID
//
// return   : 없음
// -----------------------------------------------------------------------------------
function selectAddress(recipient, postcode, address, phone, isDefault, addressId) {
    // 선택한 주소 정보를 화면에 표시
    document.getElementById('recipient').textContent     = recipient;
    document.getElementById('postcode').textContent      = postcode;
    document.getElementById('address').textContent       = address;
    document.getElementById('phone').textContent         = phone;
    document.getElementById('addressId').setAttribute('data-value', addressId);

    // 기본 배송지 여부 표시
    const defaultLabel = document.getElementById('defaultLabel');
    defaultLabel.innerHTML = isDefault ? "<strong>기본 배송지</strong>" : "";

    // 모달 닫기
    $('#addressModal').modal('hide');
}

// -----------------------------------------------------------------------------------
// 함수명   : requestPay
// 설명     : 결제 요청을 처리하고, 결제 성공 시 서버로 데이터를 전송
//            실패 시 사용자에게 알림을 표시
//
// param    : number totalPrice - 결제 금액
//
// return   : 없음
// -----------------------------------------------------------------------------------
let IMP = window.IMP;
IMP.init("imp11776700");
function requestPay(totalPrice) {
    const buyerEmail       = document.getElementById("buyerEmail").getAttribute("data-value");        // 구매자 이메일
    const buyerName        = document.getElementById("buyerName").getAttribute("data-value");         // 구매자 이름
    const productNames     = document.getElementById("productsName").getAttribute("data-value");      // 상품 이름 목록
    const addressId        = document.getElementById("addressId").getAttribute("data-value");         // 주소 ID
    const addPostcode      = document.getElementById("addPostcode").value;                            // 우편번호
    const addAddress       = document.getElementById("addAddress").value;                             // 기본 주소
    const addDetailAddress = document.getElementById("addDetailAddress").value;                       // 상세 주소
    const addExtraAddress  = document.getElementById("addExtraAddress").value;                        // 추가 주소
    const addPhone         = document.getElementById("addPhone").value;                               // 연락처
    const addRecipient     = document.getElementById("addRecipient").value;                           // 수령인 이름

    if (!addressId) {
        if (!addPostcode || !addAddress || !addDetailAddress || !addExtraAddress) {
            showAlert("주소 정보를 입력해주세요.", "error");
            return;
        }
        if (!addPhone || !addRecipient) {
            showAlert("수령인 정보를 입력해주세요.", "error");
            return;
        }
    }

    // 아임포트 결제 요청
    IMP.request_pay({
        pg          : "kakaopay.TC0ONETIME", // 카카오페이 PG 설정
        pay_method  : "card",                // 결제 방식
        name        : productNames,          // 상품 이름
        amount      : totalPrice,            // 결제 금액
        buyer_email : buyerEmail,            // 구매자 이메일
        buyer_name  : buyerName,              // 구매자 이름
    }, function (res) {
        if (res.success) {
            // 결제 성공 시 동적으로 폼 생성 후 서버로 데이터 전송
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "/payments/success";

            // CSRF 토큰 추가
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
            const csrfInput = document.createElement("input");
            csrfInput.type  = "hidden";
            csrfInput.name  = "_token";
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // 결제 정보 추가
            const merchantUidInput = document.createElement("input");
            merchantUidInput.type  = "hidden";
            merchantUidInput.name  = "merchant_uid";
            merchantUidInput.value = res.merchant_uid;
            form.appendChild(merchantUidInput);

            // 주소 정보 추가
            if (addressId != 0) {
                const addressIdInput = document.createElement("input");
                addressIdInput.type  = "hidden";
                addressIdInput.name  = "address_id";
                addressIdInput.value = addressId;
                form.appendChild(addressIdInput);
            } else {
                const addPostcodeInput = document.createElement("input");
                addPostcodeInput.type  = "hidden";
                addPostcodeInput.name  = "addPostcode";
                addPostcodeInput.value = addPostcode;
                form.appendChild(addPostcodeInput);

                const addAddressInput = document.createElement("input");
                addAddressInput.type  = "hidden";
                addAddressInput.name  = "addAddress";
                addAddressInput.value = addAddress;
                form.appendChild(addAddressInput);

                const addDetailAddressInput = document.createElement("input");
                addDetailAddressInput.type  = "hidden";
                addDetailAddressInput.name  = "addDetailAddress";
                addDetailAddressInput.value = addDetailAddress;
                form.appendChild(addDetailAddressInput);

                const addExtraAddressInput = document.createElement("input");
                addExtraAddressInput.type  = "hidden";
                addExtraAddressInput.name  = "addExtraAddress";
                addExtraAddressInput.value = addExtraAddress;
                form.appendChild(addExtraAddressInput);

                const addPhoneInput = document.createElement("input");
                addPhoneInput.type  = "hidden";
                addPhoneInput.name  = "addPhone";
                addPhoneInput.value = addPhone;
                form.appendChild(addPhoneInput);

                const addRecipientInput = document.createElement("input");
                addRecipientInput.type  = "hidden";
                addRecipientInput.name  = "addRecipient";
                addRecipientInput.value = addRecipient;
                form.appendChild(addRecipientInput);
            }

            // 결제 금액 추가
            const amountInput = document.createElement("input");
            amountInput.type  = "hidden";
            amountInput.name  = "amount";
            amountInput.value = totalPrice;
            form.appendChild(amountInput);

            // 상품 정보 추가
            items.forEach((item, index) => {
                const productIdInput = document.createElement("input");
                productIdInput.type  = "hidden";
                productIdInput.name  = `products[${index}][product_id]`;
                productIdInput.value = item.id;
                form.appendChild(productIdInput);

                const productNameInput = document.createElement("input");
                productNameInput.type  = "hidden";
                productNameInput.name  = `products[${index}][name]`;
                productNameInput.value = item.name;
                form.appendChild(productNameInput);

                const productPriceInput = document.createElement("input");
                productPriceInput.type  = "hidden";
                productPriceInput.name  = `products[${index}][price]`;
                productPriceInput.value = item.price;
                form.appendChild(productPriceInput);

                const productQuantityInput = document.createElement("input");
                productQuantityInput.type  = "hidden";
                productQuantityInput.name  = `products[${index}][quantity]`;
                productQuantityInput.value = item.quantity;
                form.appendChild(productQuantityInput);
            });

            // 폼 제출
            document.body.appendChild(form);
            form.submit();
        } else {
            // 결제 실패 시 사용자에게 알림
            showAlert("결제에 실패했습니다.<br>" + res.error_msg);
        }
    });
}