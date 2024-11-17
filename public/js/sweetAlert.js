// -----------------------------------------------------------------------------------
// 함수명   : showAlert
// 설명     : 간단한 메시지를 경고 모달로 표시
//
// param    : string msg  - 표시할 메시지
//            string icon - 모달 아이콘 유형 (예: 'success', 'error', 'warning' 등)
//
// return   : 없음
// -----------------------------------------------------------------------------------
function showAlert(msg, icon) {
    Swal.fire({
        html              : msg,
        icon              : icon,
        confirmButtonText : '확인'
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : showAlertThen
// 설명     : 메시지를 표시한 후 확인 버튼 클릭 시 요청 실행
//
// param    : string msg    - 표시할 메시지
//            string icon   - 모달 아이콘 유형
//            function req  - 사용자가 확인 버튼 클릭 후 실행할 함수
//
// return   : 없음
// -----------------------------------------------------------------------------------
function showAlertThen(msg, icon, req) {
    Swal.fire({
        html              : msg,
        icon              : icon,
        confirmButtonText : '확인'
    }).then(() => {
        req(); // 요청 실행
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : showAlertConfirm
// 설명     : 사용자에게 확인을 요청하는 모달 표시
//
// param    : string msg    - 확인 요청 메시지
//            string icon   - 모달 아이콘 유형
//            function req  - 사용자가 '예' 버튼 클릭 후 실행할 함수
//
// return   : 없음
// -----------------------------------------------------------------------------------
function showAlertConfirm(msg, icon, req) {
    Swal.fire({
        html              : msg,
        icon              : icon,
        showCancelButton  : true,
        confirmButtonText : '예',
        cancelButtonText  : '아니오'
    }).then((result) => {
        if (result.isConfirmed) { // 사용자가 '예'를 클릭했는지 확인
            req(); // 요청 실행
        }
    });
}
