// -----------------------------------------------------------------------------------
// 로드 시 실행되는 주요 이벤트 핸들러
// 설명     : 페이지 내에서 주소 추가, 수정, 삭제 기능을 처리
// -----------------------------------------------------------------------------------
$(document).ready(function () {
    // -----------------------------------------------------------------------------------
    // 주소 추가 폼 제출
    // 설명     : 새 주소를 추가하고 성공 시 주소 목록을 업데이트
    // -----------------------------------------------------------------------------------
    $('#addAddressForm').on('submit', function (e) {
        e.preventDefault(); // 폼의 기본 동작 방지
        let formData = new FormData(this);

        axios.post('/users/addAddress', formData)
            .then(res => {
                console.log('서버 응답 데이터:', res.data);
                showAlertThen(res.data.msg, 'success', () => $('#addAddressModal').modal('hide')); // 모달 닫기
                updateAddressList(res.data.addresses); // 주소 목록 갱신
            })
            .catch(err => handleFormError(err));
    });

    // -----------------------------------------------------------------------------------
    // 배송지 목록 갱신 함수
    // 설명     : 서버에서 전달받은 주소 데이터를 기반으로 UI를 업데이트
    //
    // param    : array addresses - 업데이트할 주소 데이터 배열
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    function updateAddressList(addresses) {
        const addressListContainer = $('#addressList');
        addressListContainer.empty();
    
        if (addresses.length === 0) {
            addressListContainer.append('<p>배송지가 없습니다.</p>');
            return;
        }
    
        addresses.forEach(address => {
            const addressItem = `
                <div class="address-card">
                    <div class="address-details">
                        <span>${address.recipient}</span><br>
                        <span>${address.address}, ${address.detailAddress || ''}</span><br>
                        <span>${address.phone}</span><br>
                        <span>${address.default == '1' ? '기본 배송지' : ''}</span>
                        <span class="hidden-address hidden-postcode" data-postcode="${address.postcode}"></span>
                        <span class="hidden-address hidden-extraAddress" data-extraAddress="${address.extraAddress}"></span>
                        <span class="hidden-address hidden-addressId" data-addressId="${address.add_id}"></span>
                    </div>
                    <div class="address-actions">
                        <button data-id="${address.add_id}" class="btn btn-outline-primary btn-edit">수정</button>
                        <button data-id="${address.add_id}" class="btn btn-outline-danger btn-delete">삭제</button>
                    </div>
                </div>
            `;
            addressListContainer.append(addressItem);
        });
    }

    // -----------------------------------------------------------------------------------
    // 주소 수정 버튼 클릭 이벤트
    // 설명     : 수정 버튼 클릭 시 선택된 주소 데이터를 수정 모달에 표시
    // -----------------------------------------------------------------------------------
    $(document).on('click', '.btn-edit', function () {
        const addressCard = $(this).closest('.address-card');

        // 주소 데이터 추출
        $('#edit_addressId').val(addressCard.find('.hidden-addressId').data('addressid'));
        $('#edit_recipient').val(addressCard.find('.address-details span').eq(0).text());
        $('#edit_phone').val(addressCard.find('.address-details span').eq(2).text());
        $('#edit_address').val(addressCard.find('.address-details span').eq(1).text().split(',')[0]);
        $('#edit_detailAddress').val(addressCard.find('.address-details span').eq(1).text().split(',')[1] || '');
        $('#edit_default').prop('checked', addressCard.find('.address-details span').eq(3).text() === '기본 배송지');
        $('#edit_postcode').val(addressCard.find('.hidden-postcode').data('postcode'));
        $('#edit_extraAddress').val(addressCard.find('.hidden-extraAddress').data('extraAddress'));

        $('#editAddressModal').modal('show'); // 모달 열기
    });

    // -----------------------------------------------------------------------------------
    // 주소 수정 폼 제출
    // 설명     : 수정된 주소 데이터를 서버로 전송하고 성공 시 목록 갱신
    // -----------------------------------------------------------------------------------
    $('#editAddressForm').on('submit', function (e) {
        e.preventDefault(); // 폼 기본 동작 방지
        let formData = new FormData(this);

        axios.put('/users/editAddress', Object.fromEntries(formData))
            .then(res => {
                console.log(res);
                if (res.data.success) {
                    showAlertThen(res.data.message, 'success', () => $('#editAddressModal').modal('hide')); // 모달 닫기
                    updateAddressList(res.data.addresses); // 주소 목록 갱신
                } else {
                    showAlert(res.data.message, 'error');
                }
            })
            .catch(
                err => {
                    console.log(err);
                    handleFormError(err);
                }
            );
    });

    // -----------------------------------------------------------------------------------
    // 주소 삭제 버튼 클릭 이벤트
    // 설명     : 삭제 버튼 클릭 시 확인 메시지를 표시하고 서버로 삭제 요청
    // -----------------------------------------------------------------------------------
    $('#addressList').on('click', '.btn-delete', function () {
        const addressCard = $(this).closest('.address-card');
        const addId       = addressCard.find('.hidden-addressId').data('addressid');

        showAlertConfirm('정말 이 배송지를 삭제하시겠습니까?', 'warning', () => {
            axios.delete('/users/deleteAddress', { data: { addId: addId } })
                .then(res => {
                    showAlertThen(res.data.msg, 'success', () => updateAddressList(res.data.addresses));
                })
                .catch(err => handleFormError(err));
        });
    });

    // -----------------------------------------------------------------------------------
    // 공통 오류 처리 함수
    // 설명     : 폼 전송 시 발생하는 오류를 UI에 표시
    //
    // param    : object err - Axios 에러 객체
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    function handleFormError(err) {
        if (err.response && err.response.status === 422) {
            const errors = err.response.data.errors;
            let errorMessages = '';
            Object.keys(errors).forEach(key => {
                errorMessages += errors[key].join('<br>') + '<br>';
            });
            showAlert(errorMessages, 'error');
        } else {
            showAlert('알 수 없는 오류가 발생했습니다.', 'error');
        }
        console.error(err);
    }

    // 전화번호 입력 필드 포맷팅
    const editPhoneInput = document.getElementById('edit_phone');
    const phoneInput     = document.getElementById('phone');
    formatPhoneNumber(editPhoneInput);
    formatPhoneNumber(phoneInput);
});

// -----------------------------------------------------------------------------------
// 함수명   : daumPostcode
// 설명     : 다음 주소 API를 사용하여 주소 검색 및 선택한 주소 데이터를 입력 필드에 적용
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function daumPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            // 팝업에서 검색결과 항목 클릭 시 실행

            let addr      = ''; // 주소 변수
            let extraAddr = ''; // 참고항목 변수

            // 사용자가 선택한 주소 타입에 따라 주소 값을 가져옴
            if (data.userSelectedType === 'R') { // 도로명 주소
                addr = data.roadAddress;
            } else { // 지번 주소
                addr = data.jibunAddress;
            }

            // 도로명 주소일 때 참고항목 조합
            if (data.userSelectedType === 'R') {
                // 법정동명 추가 (법정리는 제외)
                if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                    extraAddr += data.bname;
                }
                // 공동주택 건물명 추가
                if (data.buildingName !== '' && data.apartment === 'Y') {
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                // 참고항목이 있을 경우 괄호로 감싸 추가
                if (extraAddr !== '') {
                    extraAddr = '(' + extraAddr + ')';
                }
                document.getElementById("extraAddress").value = extraAddr;
            } else {
                document.getElementById("extraAddress").value = ''; // 참고항목 초기화
            }

            // 우편번호와 주소 입력
            document.getElementById('postcode').value = data.zonecode;
            document.getElementById("address").value = addr;

            // 상세주소 필드로 커서 이동
            document.getElementById("detailAddress").focus();
        }
    }).open();
}

// -----------------------------------------------------------------------------------
// 함수명   : cancelOrder
// 설명     : 주문 취소 요청을 서버에 전송하고 성공 시 주문 상태를 UI에 업데이트
//
// param    : string merchant_uid - 결제 고유 ID
//            number price        - 결제 금액
//
// return   : 없음
// -----------------------------------------------------------------------------------
function cancelOrder(merchant_uid, price) {
    showAlertConfirm('정말 이 주문을 취소하시겠습니까?', 'warning', () => {
        axios.post('/payments/cancel', {
            merchant_uid: merchant_uid,
            price       : price
        })
        .then(res => {
            showAlertThen(res.data.msg, 'success'); // 성공 메시지 표시

            const orderElement = document.querySelector(`[data-merchant-uid="${merchant_uid}"]`);
            if (orderElement) {
                const statusElements = orderElement.querySelectorAll('.order-info');

                // 상태 정보 업데이트
                statusElements.forEach(element => {
                    if (element.textContent.includes("상태 :")) {
                        element.innerHTML = "<strong>상태 : </strong> 환불";
                    }
                });

                // 취소 버튼 숨기기
                const cancelButton = orderElement.querySelector('.btn-cancel');
                if (cancelButton) {
                    cancelButton.style.display = 'none';
                }
            }
        })
        .catch(err => {
            showAlert(err.response?.data?.msg || '알 수 없는 오류가 발생했습니다.', 'error');
            console.error(err); // 오류 로그
        });
    });
}


// -----------------------------------------------------------------------------------
// 로드 시 실행되는 주요 이벤트 핸들러
// 설명     : 소셜 사용자와 일반 사용자의 비밀번호 확인 및 변경 로직 처리
// -----------------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const isSocialUser          = document.querySelector('span[data-isSocialUser]')?.getAttribute('data-isSocialUser') === 'true';
    const passwordCheckSection  = document.getElementById('passwordCheckSection');
    const passwordChangeSection = document.getElementById('passwordChangeSection');
    const userInfo              = document.getElementById('userInfo');
    const userName              = document.getElementById('userName');
    const userEmail             = document.getElementById('userEmail');
    const userCreatedAt         = document.getElementById('userCreatedAt');

    // -----------------------------------------------------------------------------------
    // 소셜 사용자 처리
    // 설명     : 소셜 로그인 사용자의 경우 비밀번호 확인 없이 사용자 정보를 표시
    // -----------------------------------------------------------------------------------
    if (isSocialUser) {
        userName.textContent     = document.querySelector('span[data-userName]').getAttribute('data-userName');
        userEmail.textContent    = document.querySelector('span[data-userEmail]').getAttribute('data-userEmail');
        userCreatedAt.textContent = document.querySelector('span[data-userCreatedAt]').getAttribute('data-userCreatedAt');
        userInfo.style.display   = 'block';
    } else {
        // -----------------------------------------------------------------------------------
        // 일반 사용자 처리
        // 설명     : 일반 사용자는 비밀번호 확인 후 사용자 정보를 표시
        // -----------------------------------------------------------------------------------
        passwordCheckSection.style.display = 'block';

        const checkPasswordButton = document.getElementById('checkPasswordButton');
        const passwordInput       = document.getElementById('password');
        const passwordCheckError  = document.getElementById('passwordCheckError');

        // -----------------------------------------------------------------------------------
        // 비밀번호 확인
        // 설명     : 비밀번호 입력 후 확인 버튼 클릭 시 서버 요청을 통해 검증
        // -----------------------------------------------------------------------------------
        passwordInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 기본 동작 방지
                checkPasswordButton.click(); // 확인 버튼 클릭 트리거
            }
        });

        checkPasswordButton.addEventListener('click', function () {
            event.preventDefault(); // 기본 동작 방지
            const formData = new FormData(document.getElementById('passwordCheckForm'));

            axios.post('/mypage/password/check', {
                password: formData.get('password'),
            })
            .then(res => {
                if (res.data.success) {
                    handlePasswordCheckSuccess(res.data.data); // 비밀번호 확인 성공 처리
                }
            })
            .catch(error => {
                if (error.response && error.response.status === 401) {
                    passwordCheckError.style.display = 'block';
                    userInfo.style.display = 'none';
                }
            });
        });

        passwordInput.addEventListener('input', function () {
            passwordCheckError.style.display = 'none'; // 오류 메시지 숨김
        });
    }

    // -----------------------------------------------------------------------------------
    // 함수명   : handlePasswordCheckSuccess
    // 설명     : 비밀번호 확인 성공 시 사용자 정보를 표시하고 비밀번호 변경 필드 활성화
    //
    // param    : object data - 사용자 정보 데이터
    //
    // return   : 없음
    // -----------------------------------------------------------------------------------
    function handlePasswordCheckSuccess(data) {
        userName.textContent         = data.name;
        userEmail.textContent        = data.email;
        userCreatedAt.textContent    = data.created_at;
        userInfo.style.display       = 'block';
        passwordChangeSection.style.display = 'block';
        passwordCheckSection.style.display  = 'none';
    }

    // -----------------------------------------------------------------------------------
    // 비밀번호 변경 처리
    // 설명     : 새 비밀번호 입력 후 변경 버튼 클릭 시 서버 요청을 통해 변경
    // -----------------------------------------------------------------------------------
    const changePasswordButton  = document.getElementById('changePasswordButton');
    const newPasswordInput      = document.getElementById('newPassword');
    const confirmPasswordInput  = document.getElementById('confirmPassword');

    [newPasswordInput, confirmPasswordInput].forEach(input => {
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 기본 동작 방지
                changePasswordButton.click(); // 변경 버튼 클릭 트리거
            }
        });
    });

    changePasswordButton.addEventListener('click', function () {
        const formData = new FormData(document.getElementById('passwordChangeForm'));

        axios.post('/mypage/password/change', {
            new_password    : formData.get('new_password'),
            confirm_password: formData.get('confirm_password'),
        })
        .then(res => {
            if (res.data.success) {
                showAlertThen(res.data.message, 'success', () => {
                    window.location.href = res.data.redirect_url; // 성공 시 리다이렉트
                });
            } else {
                showAlert(res.data.message, 'error'); // 실패 메시지 표시
            }
        })
        .catch(error => {
            if (error.response && error.response.data.errors) {
                showAlert(Object.values(error.response.data.errors).join('\n'), 'error'); // 상세 오류 표시
            } else {
                showAlert('알 수 없는 오류가 발생했습니다.', 'error');
            }
        });
    });
});
