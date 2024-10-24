$(document).ready(function() {
    // 주소 추가 폼 제출
    $('#addAddressForm').on('submit', function(e) {
        e.preventDefault(); // 폼의 기본 동작 방지
        let formData = new FormData(this);

        axios.post('/users/addAddress', formData)
            .then(res => {
                showAlertThen(res.data.msg, 'success', () => $('#addAddressModal').modal('hide')); // 모달 닫기
                updateAddressList(res.data.addresses); // 배송지 목록 갱신
            })
            .catch(err => {
                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors;
                    let errorMessages = '';

                    // 오류 객체를 순회하여 메시지 추출
                    Object.keys(errors).forEach((key) => {
                        errorMessages += errors[key].join('<br>') + '<br>';
                    });
                    showAlert(errorMessages, 'error');
                } else {
                    showAlert('알 수 없는 오류가 발생했습니다.', 'error');
                }
                console.error(err);
            });
    });

    // 배송지 목록 갱신
    function updateAddressList(addresses) {
        const addressListContainer = $('#addressList'); // 배송지 목록을 표시할 컨테이너 선택
        addressListContainer.empty(); // 기존 목록 초기화

        // 새로운 배송지 목록을 추가
        addresses.forEach(address => {
            const addressItem = `
                <div class="address-card">
                    <div class="address-details">
                        <span>${address.recipient}</span><br>
                        <span>${address.address}, ${address.detailAddress || ''}</span><br>
                        <span>${address.phone}</span><br>
                        <span>${address.default == '1' ? '기본 배송지' : ''}</span>
                        <span class="hidden-address hidden-postcode" data-postcode="${address.postcode}"></span>
                        <span class="hidden-address hidden-extraAddress" data-extraAddress="${address.eatraAddress}"></span>
                        <span class="hidden-address hidden-addressPk" data-addressPk="${address.add_id}"></span>
                    </div>
                    <div class="address-actions">
                        <button data-id="${address.add_id}" class="btn btn-outline-primary btn-edit">수정</button>
                        <button data-id="${address.add_id}" class="btn btn-outline-danger btn-delete">삭제</button>
                    </div>
                </div>
            `;
            addressListContainer.append(addressItem); // 새로운 배송지 정보를 목록에 추가
        });
    }

    // 배송지 수정하기
    $(document).on('click', '.btn-edit', function() {
        // 배송지 정보 가져오기
        let addressId = $(this).data('id');
        let addressCard = $(this).closest('.address-card');
        let recipient = addressCard.find('.address-details span').eq(0).text();
        let fullAddress = addressCard.find('.address-details span').eq(1).text(); 
        let phone = addressCard.find('.address-details span').eq(2).text();
        let isDefault = addressCard.find('.address-details span').eq(3).text() === '기본 배송지';
        let postcode = addressCard.find('.hidden-postcode').data('postcode');
        let addressPk = addressCard.find('.hidden-addressPk').data('addresspk');
        let extraAddress = addressCard.find('.hidden-extraAddress').data('extraAddress');

        // 모달에 데이터 설정
        $('#edit_address_id').val(addressId);
        $('#edit_recipient').val(recipient);
        $('#edit_phone').val(phone);
        $('#edit_address').val(fullAddress.split(',')[0]); // 주소
        $('#edit_detailAddress').val(fullAddress.split(',')[1] || ''); // 상세주소
        $('#edit_default').prop('checked', isDefault);
        $('#edit_postcode').val(postcode);
        $('#edit_extraAddress').val(extraAddress);
        $('#edit_addId').val(addressPk);

        // 모달 열기
        $('#editAddressModal').modal('show');
    });

    // 배송지 수정 폼 제출
    $('#editAddressForm').on('submit', function(e) {
        e.preventDefault(); // 폼의 기본 동작 방지
        let formData = new FormData(this);

        let data = {
            postcode        : formData.get('postcode'),
            address         : formData.get('address'),
            detailAddress   : formData.get('detailAddress'),
            extraAddress    : formData.get('extraAddress'),
            recipient       : formData.get('recipient'),
            phone           : formData.get('phone'),
            default         : formData.get('default'),
            addId           : formData.get('addId')
        }

        // axios.put('/users/editAddress', formData)
        axios.put('/users/editAddress', data)
            .then(res => {
                showAlertThen(res.data.msg, 'success', () => $('#editAddressModal').modal('hide')); // 모달 닫기
                updateAddressList(res.data.addresses); // 배송지 목록 갱신
            })
            .catch(err => {
                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors;
                    let errorMessages = '';

                    // 오류 객체를 순회하여 메시지 추출
                    Object.keys(errors).forEach((key) => {
                        errorMessages += errors[key].join('<br>') + '<br>';
                    });
                    showAlert(errorMessages, 'error');
                } else {
                    showAlert('알 수 없는 오류가 발생했습니다.', 'error');
                }
                console.error(err);
            });
    });

    // 배송지 삭제하기
    $('#addressList').on('click', '.btn-delete', function() {
        let addressCard = $(this).closest('.address-card');
        let addId = addressCard.find('.hidden-addressPk').data('addresspk');

        showAlertConfirm('정말 이 배송지를 삭제하시겠습니까?', 'warning', () => {
            axios.delete('/users/deleteAddress', { data: { addId: addId } })
                .then(res => {
                    showAlertThen(res.data.msg, 'success', () => {
                        // 성공 시 주소 목록 갱신
                        updateAddressList(res.data.addresses);
                    });
                })
                .catch(err => {
                    if (err.response && err.response.status === 422) {
                        const errors = err.response.data.errors;
                        let errorMessages = '';

                        // 오류 객체를 순회하여 메시지 추출
                        Object.keys(errors).forEach((key) => {
                            errorMessages += errors[key].join('<br>') + '<br>';
                        });
                        showAlert(errorMessages, 'error');
                    } else {
                        showAlert('알 수 없는 오류가 발생했습니다.', 'error');
                    }
                    console.error(err);
                });
        });
    });

    const editPhoneInput = document.getElementById('edit_phone');
    formatPhoneNumber(editPhoneInput);
});
