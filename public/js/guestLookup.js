// -----------------------------------------------------------------------------------
// 함수명   : guestLookup
// 설명     : 비회원 주문 조회를 처리하여 결과를 화면에 표시
//            서버에서 주문 데이터를 받아와 주문 정보와 상태를 렌더링
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function guestLookup() {
    const orderId   = document.getElementById('orderId').value; // 입력된 주문 ID
    const resultDiv = document.getElementById('result');        // 결과를 표시할 DIV

    axios.post(`/orders/lookup`, {
        guest_uuid: orderId,
    })
    .then(res => {
        resultDiv.innerHTML = '';
        if (res.data.success) {
            const order     = res.data.order; // 주문 데이터
            const statusText = order.payment.status === 'P'
                ? '결제완료'
                : order.payment.status === 'R'
                ? '환불'
                : '알 수 없음';

            let data = `
                <div class="order-card" id="order-${order.payment.merchant_uid}" data-merchant-uid="${order.payment.merchant_uid}">
                    <p class="order-info"><strong>주문 일자 : </strong> ${new Date(order.created_at).toLocaleString()}</p>
                    <p class="order-info"><strong>수령인 : </strong> ${order.address.recipient}</p>
                    <p class="order-info"><strong>배송지 : </strong> ${order.address.address} ${order.address.detailAddress || ''}</p>
                    <p class="order-info"><strong>상태 : </strong> ${statusText}</p>
                    
                    <h6 class="order-items-title">주문 상품</h6>
            `;

            order.order_items.forEach(item => {
                data += `
                    <div class="order-item">
                        <div class="order-item-image">
                            <img src="${window.appUrl + '/' + (item.product?.img)}" alt="${item.product.name}" class="product-thumbnail">
                        </div>
                        <div class="order-item-details">
                            <span>${item.product.name}, ${item.quantity}개</span><br>
                            <span>${new Intl.NumberFormat().format(item.price)}원</span>
                        </div>
                    </div>
                `;
            });

            data += `
                    <div class="order-actions">
                        <p class="order-info"><strong>총 결제 금액: </strong> ${new Intl.NumberFormat().format(order.payment.price)}원</p>
            `;

            if (order.payment.status === 'P') {
                data += `
                    <button type="button" class="btn btn-outline-danger btn-cancel" onclick="cancelOrder('${order.payment.merchant_uid}', '${order.payment.price}')">주문 취소</button>
                `;
            }

            data += `
                    </div>
                </div>`;
            resultDiv.innerHTML = data;
        } else {
            console.log(res.data.message);
            resultDiv.innerHTML = `<p>${res.data.message}</p>`;
        }
    })
    .catch(err => {
        console.log(err);
        resultDiv.innerHTML = `<p>서버와 통신 중 오류가 발생했습니다 : ${err.response?.data?.message || '알 수 없는 오류'}</p>`;
    });
}

// -----------------------------------------------------------------------------------
// 이벤트명 : click
// 설명     : 조회 버튼 클릭 시 guestLookup 함수 호출
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
document.getElementById('lookupBtn').addEventListener('click', guestLookup);

// -----------------------------------------------------------------------------------
// 함수명   : cancelOrder
// 설명     : 주문을 취소하고, 화면에 상태를 업데이트
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
            showAlertThen(res.data.msg, 'success');

            const orderElement = document.querySelector(`[data-merchant-uid="${merchant_uid}"]`);
            if (orderElement) {
                const statusElements = orderElement.querySelectorAll('.order-info');

                statusElements.forEach(element => {
                    if (element.textContent.includes("상태 :")) {
                        element.innerHTML = "<strong>상태 : </strong> 환불";
                    }
                });

                // 주문 취소 버튼 숨기기
                const cancelButton = orderElement.querySelector('.btn-cancel');
                if (cancelButton) {
                    cancelButton.style.display = 'none';
                }
            }
        })
        .catch(err => {
            showAlert(err.response.data.msg, 'error');
        });
    });
}
