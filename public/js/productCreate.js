// -----------------------------------------------------------------------------------
// 함수명   : previewImage
// 설명     : 상품 대표 이미지 미리보기 기능
//
// param    : Event event - 파일 입력 필드의 change 이벤트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function previewImage(event) {
    const reader  = new FileReader();
    const preview = document.getElementById('imgPreview');

    reader.onload = function () {
        preview.src            = reader.result;
        preview.style.display  = 'block'; // 이미지를 미리 보기로 표시
        preview.style.maxWidth = '200px';
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
// 설명     : 상품 설명 이미지 미리보기 기능 (최대 5개 이미지)
//            X 버튼으로 개별 이미지 삭제 가능
//
// param    : Event event - 파일 입력 필드의 change 이벤트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function previewDescriptionImages(event) {
    const previewContainer = document.getElementById('descriptionImagesPreview');
    previewContainer.innerHTML = ''; // 이전 이미지를 지우기

    const files = Array.from(event.target.files);

    // 선택된 이미지 수 확인
    if (files.length > 5) {
        alert("상품 설명 이미지는 최대 5개까지만 등록할 수 있습니다.");
        event.target.value = '';
        return;
    }

    files.forEach((file, index) => {
        const imgWrapper = document.createElement('div');
        imgWrapper.style.position      = 'relative';
        imgWrapper.style.marginRight   = '10px';
        imgWrapper.style.marginBottom  = '10px';

        const img = document.createElement('img');
        img.src            = URL.createObjectURL(file);
        img.style.maxWidth = '300px';
        img.style.display  = 'block';

        // X 버튼 추가
        const closeButton = document.createElement('span');
        closeButton.innerHTML               = '&times;'; // X 문자
        closeButton.style.position          = 'absolute';
        closeButton.style.top               = '-5px';
        closeButton.style.right             = '0';
        closeButton.style.color             = 'red';
        closeButton.style.cursor            = 'pointer';
        closeButton.style.fontSize          = '32px';
        closeButton.style.width             = '20px';
        closeButton.style.height            = '30px';
        closeButton.style.display           = 'flex';
        closeButton.style.alignItems        = 'center';
        closeButton.style.justifyContent    = 'center';

        closeButton.onclick = (e) => {
            e.stopPropagation(); // 이벤트 전파 방지
            const newFiles = files.filter((_, i) => i !== index);
            const dataTransfer = new DataTransfer();
            newFiles.forEach(file => dataTransfer.items.add(file));
            event.target.files = dataTransfer.files;
            previewDescriptionImages(event); // 다시 미리보기 업데이트
        };

        imgWrapper.appendChild(img);
        imgWrapper.appendChild(closeButton);
        previewContainer.appendChild(imgWrapper);
    });

    // -----------------------------------------------------------------------------------
    // 가로 스크롤 이벤트
    // 설명     : 상품 설명 이미지 미리보기 영역에서 휠로 가로 스크롤 가능
    // -----------------------------------------------------------------------------------
    const scrollablePreview = document.getElementById('descriptionImagesPreview');

    scrollablePreview.addEventListener('wheel', function (e) {
        e.preventDefault(); // 기본 스크롤 방지
        scrollablePreview.scrollBy({
            left    : e.deltaY * 3, // 수직 스크롤 양을 가로 스크롤로 변환
            behavior: 'smooth'     // 부드러운 스크롤 효과
        });
    });
}
