// 메시지를 표시하는 간단한 경고 모달 함수
// msg: 표시할 메시지
// icon: 모달 아이콘 유형 (예: 'success', 'error', 'warning' 등)
function showAlert(msg, icon) {
    Swal.fire({
        html: msg,
        icon: icon,
        confirmButtonText: '확인'
    });
}

// 메시지를 표시하고 확인 후 요청을 실행하는 함수
// msg: 표시할 메시지
// icon: 모달 아이콘 유형
// req: 사용자가 확인 버튼을 클릭한 후 실행할 함수
function showAlertThen(msg, icon, req) {
    Swal.fire({
        html: msg,
        icon: icon,
        confirmButtonText: '확인'
    }).then(() => {
        req(); // 요청 실행
    });
}

// 사용자에게 확인을 요청하는 모달 함수
// msg: 확인 요청 메시지
// icon: 모달 아이콘 유형
// req: 사용자가 '예' 버튼을 클릭한 후 실행할 함수
function showAlertConfirm(msg, icon, req) {
    Swal.fire({
        html: msg,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: '예',
        cancelButtonText: '아니오'
    }).then((result) => {
        if (result.isConfirmed) { // 사용자가 '예'를 클릭했는지 확인
            req(); // 요청 실행
        }
    });
}