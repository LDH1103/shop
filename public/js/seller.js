// -----------------------------------------------------------------------------------
// 문서 로드 시 실행되는 주요 이벤트 핸들러
// 설명     : 상품 클릭, 수정, 삭제 및 기타 이벤트 처리
// -----------------------------------------------------------------------------------
$(document).ready(function () {
    // -----------------------------------------------------------------------------------
    // 이벤트 등록
    // 설명     : 상품 클릭 시 모달 데이터 설정
    // -----------------------------------------------------------------------------------
    $('.product-name').on('click', setModalData);

    // -----------------------------------------------------------------------------------
    // 이벤트 등록
    // 설명     : 상품 수정 버튼 클릭 시 수정 처리
    // -----------------------------------------------------------------------------------
    $('#save-product').on('click', updateProduct);

    // -----------------------------------------------------------------------------------
    // 이벤트 등록
    // 설명     : 상품 삭제 버튼 클릭 시 삭제 처리
    // -----------------------------------------------------------------------------------
    $('#del-product').on('click', deleteProduct);

    // -----------------------------------------------------------------------------------
    // 이벤트 등록
    // 설명     : 가로 스크롤 설정
    // -----------------------------------------------------------------------------------
    const scrollablePreview = document.getElementById('descriptionImagesPreview');
    scrollablePreview.addEventListener('wheel', horizontalScroll);

    // -----------------------------------------------------------------------------------
    // 이벤트 등록
    // 설명     : 모달 닫힐 때 파일 입력 초기화
    // -----------------------------------------------------------------------------------
    $('#productModal').on('hidden.bs.modal', resetFileInput);
});

// -----------------------------------------------------------------------------------
// 함수명   : setModalData
// 설명     : 상품 클릭 시 모달에 데이터 설정
//
// param    : Event event - 클릭 이벤트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function setModalData(event) {
    event.preventDefault(); // 링크 기본 동작 방지

    const productData    = $(this).data(); // 모든 데이터 가져오기
    const imageContainer = $('#descriptionImagesPreview');
    imageContainer.empty(); // 이전 이미지 지우기

    // 모달 데이터 설정
    $('#product-id').val(productData.id);
    $('#product-name').val(productData.name);
    $('#product-category').val(productData.category);
    $('#product-status').val(productData.status);
    $('#product-price').val(productData.price);
    $('#product-created').val(productData.created);
    $('#product-updated').val(productData.updated);
    $('#productImage').attr('src', productData.img);

    // 이미지 배열 반복 처리
    productData.description.forEach(image => {
        imageContainer.append(`<img src="${image}" class="img-fluid" alt="Product Image">`); // 이미지 추가
    });

    // 삭제 버튼에 데이터 설정
    $('#del-product').data('id', productData.id);
    $('#del-product').data('name', productData.name);
}

// -----------------------------------------------------------------------------------
// 함수명   : updateProduct
// 설명     : 상품 수정 요청을 서버로 전송
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function updateProduct() {
    const formData = new FormData($('#productForm')[0]); // form 데이터 가져오기

    axios.post('/products/' + $('#product-id').val(), formData)
        .then(res => showAlertThen(res.data.msg, 'success', () => location.reload()))
        .catch(handleError);
}

// -----------------------------------------------------------------------------------
// 함수명   : deleteProduct
// 설명     : 상품 삭제 요청을 서버로 전송
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function deleteProduct() {
    const productId   = $(this).data('id');   // 삭제할 상품 ID
    const productName = $(this).data('name'); // 삭제할 상품 이름

    showAlertConfirm(`정말 ${productName} 을(를) 삭제하시겠습니까?`, 'warning', () => {
        axios.delete('/products/delete', { data: { proIds: [productId] } })
            .then(res => showAlertThen(res.data.msg, 'success', () => location.reload()))
            .catch(handleError);
    });
}


// -----------------------------------------------------------------------------------
// 함수명   : handleError
// 설명     : 서버 요청 실패 시 발생한 에러를 처리
//
// param    : object err - Axios 에러 객체
//
// return   : 없음
// -----------------------------------------------------------------------------------
function handleError(err) {
    if (err.response) {
        // 유효성 에러
        if (err.response.status === 422) {
            showAlert(err.response.data.message, 'error');
        } else {
            showAlert('알 수 없는 오류가 발생했습니다.', 'error');
        }
    } else {
        showAlert(err ? err.msg : '알 수 없는 오류가 발생했습니다.', 'error');
    }
}

// -----------------------------------------------------------------------------------
// 함수명   : horizontalScroll
// 설명     : 수직 스크롤을 가로 스크롤로 변환
//
// param    : Event e - 휠 이벤트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function horizontalScroll(e) {
    e.preventDefault(); // 기본 스크롤 방지
    this.scrollBy({ left: e.deltaY * 3, behavior: 'smooth' }); // 수직 스크롤을 가로로 변환
}

// -----------------------------------------------------------------------------------
// 함수명   : resetFileInput
// 설명     : 모달이 닫힐 때 파일 입력 필드를 초기화
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function resetFileInput() {
    const fileInput = document.getElementById('img');
    fileInput.value = ''; // 선택된 파일 초기화
}

// -----------------------------------------------------------------------------------
// 함수명   : delSelectedItems
// 설명     : 선택된 상품들을 삭제 요청
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function delSelectedItems() {
    const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(checkbox => checkbox.value);

    if (selectedIds.length === 0) {
        showAlert('삭제할 항목을 선택해 주세요.', 'warning'); // 선택되지 않은 경우 경고
        return;
    }

    showAlertConfirm(`정말 선택된 상품들을 삭제하시겠습니까?`, 'warning', () => {
        axios.delete('/products/delete', { data: { proIds: selectedIds } })
            .then(res => showAlertThen(res.data.msg, 'success', () => location.reload()))
            .catch(err => showAlert(err.data.msg, 'error'));
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : previewImage
// 설명     : 상품 대표 이미지 미리보기
//
// param    : Event event - 파일 입력 이벤트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function previewImage(event) {
    const reader  = new FileReader();
    const preview = document.getElementById('productImage');

    reader.onload = function () {
        preview.src           = reader.result;
        preview.style.display = 'block'; // 이미지를 미리 보기로 표시
    };

    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]); // 이미지 파일을 읽음
    } else {
        preview.src           = '#';
        preview.style.display = 'none'; // 파일이 없을 때 미리 보기 숨김
    }
}

// -----------------------------------------------------------------------------------
// 함수명   : previewDescriptionImages
// 설명     : 상품 설명 이미지 미리보기
//
// param    : Event event - 파일 입력 이벤트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function previewDescriptionImages(event) {
    const previewContainer = document.getElementById('descriptionImagesPreview');
    previewContainer.innerHTML = ''; // 이전 이미지를 지우기

    const files = Array.from(event.target.files);

    // 선택된 이미지 수 확인
    if (files.length > 5) {
        showAlert('상품 설명 이미지는 최대 5개까지만 등록할 수 있습니다.', 'error');
        event.target.value = '';
        return;
    }

    files.forEach((file, index) => {
        const imgWrapper = createImagePreviewWrapper(file, index, files, event);
        previewContainer.appendChild(imgWrapper); // 이미지 미리보기 추가
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : createImagePreviewWrapper
// 설명     : 선택된 이미지의 미리보기를 생성하고, 삭제 기능 제공
//
// param    : File file           - 이미지 파일 객체
//            number index        - 이미지 인덱스
//            FileList files      - 전체 파일 목록
//            Event event         - 파일 입력 이벤트
//
// return   : HTMLDivElement imgWrapper - 생성된 이미지 미리보기 래퍼
// -----------------------------------------------------------------------------------
function createImagePreviewWrapper(file, index, files, event) {
    const imgWrapper = document.createElement('div');
    imgWrapper.style.position     = 'relative';  // 포지션 설정
    imgWrapper.style.marginRight  = '10px';      // 오른쪽 여백 추가
    imgWrapper.style.marginBottom = '10px';      // 아래쪽 여백 추가

    const img = document.createElement('img');
    img.src            = URL.createObjectURL(file);
    img.style.maxWidth = '200px';               // 이미지 크기 조정
    img.style.display  = 'block';               // 블록으로 설정

    // X 버튼 추가
    const closeButton = document.createElement('span');
    closeButton.innerHTML            = '&times;';     // X 문자
    closeButton.style.position       = 'absolute';
    closeButton.style.top            = '-5px';
    closeButton.style.right          = '0';
    closeButton.style.color          = 'red';         // X 색상
    closeButton.style.cursor         = 'pointer';     // 마우스 커서 스타일 변경
    closeButton.style.fontSize       = '32px';        // X 크기 조정
    closeButton.style.width          = '20px';
    closeButton.style.height         = '30px';
    closeButton.style.display        = 'flex';
    closeButton.style.alignItems     = 'center';
    closeButton.style.justifyContent = 'center';

    // X 버튼 클릭 이벤트
    closeButton.onclick = (e) => {
        e.stopPropagation();  // 이벤트 전파 방지
        e.preventDefault();   // 기본 동작 방지
        imgWrapper.remove();  // 선택된 이미지 삭제

        // 파일 목록 업데이트
        const newFiles        = Array.from(event.target.files).filter((_, i) => i !== index);
        const newDataTransfer = new DataTransfer(); // 새로운 DataTransfer 객체 생성
        newFiles.forEach(file => newDataTransfer.items.add(file)); // 새로운 파일 추가
        event.target.files = newDataTransfer.files; // 새로운 파일 목록으로 업데이트

        previewDescriptionImages(event); // 미리보기 업데이트
    };

    imgWrapper.appendChild(img);         // 이미지 추가
    imgWrapper.appendChild(closeButton); // X 버튼 추가

    return imgWrapper;
}
