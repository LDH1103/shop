// -----------------------------------------------------------------------------------
// 변수 선언 및 초기화
// 설명     : 별점 평가 UI를 위한 변수 설정 및 초기화
// -----------------------------------------------------------------------------------
const rateWraps     = document.querySelectorAll('.rating'); // '.rating' 컨테이너
const opacityHover  = '1.0'; // 호버 시 별의 투명도 설정

// 초기화
checkedRate(); // 초기 별 상태 표시

// -----------------------------------------------------------------------------------
// 이벤트 등록
// 설명     : 각 '.rating' 컨테이너에 별점 관련 이벤트를 추가
// -----------------------------------------------------------------------------------
rateWraps.forEach(wrap => {
    const stars  = wrap.querySelectorAll('.star-icon');     // 해당 컨테이너의 별 아이콘들
    const inputs = wrap.querySelectorAll('.rating__input'); // 해당 컨테이너의 입력 필드들

    stars.forEach((starIcon, idx) => {
        // 별에 마우스를 올릴 때
        starIcon.addEventListener('mouseenter', () => {
            resetStars(stars);        // 모든 별 초기화
            fillStars(idx, stars);   // 별 채우기
            setHoverOpacity(stars);  // 호버 시 투명도 설정
        });

        // 별에서 마우스가 벗어날 때
        starIcon.addEventListener('mouseleave', () => {
            resetOpacity(stars);     // 투명도 초기화
            checkedRate();           // 선택된 별 표시
        });

        // 별 클릭 시 선택
        starIcon.addEventListener('click', () => {
            inputs[idx].checked = true; // 해당 입력 선택
            checkedRate();              // 선택된 별 표시
        });
    });

    // 컨테이너에서 마우스가 나갈 때
    wrap.addEventListener('mouseleave', () => {
        resetOpacity(stars);         // 투명도 초기화
        checkedRate();               // 선택된 별 표시
    });
});

// -----------------------------------------------------------------------------------
// 함수명   : fillStars
// 설명     : 선택된 별까지 채우기
//
// param    : number index - 채울 별의 인덱스
//            NodeList stars - 별 아이콘 노드 리스트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function fillStars(index, stars) {
    for (let i = 0; i <= index; i++) {
        stars[i].classList.add('filled'); // 선택된 별 클래스 추가
    }
}

// -----------------------------------------------------------------------------------
// 함수명   : resetStars
// 설명     : 모든 별 초기화
//
// param    : NodeList stars - 별 아이콘 노드 리스트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function resetStars(stars) {
    stars.forEach(star => star.classList.remove('filled')); // 'filled' 클래스 제거
}

// -----------------------------------------------------------------------------------
// 함수명   : checkedRate
// 설명     : 선택된 별을 표시
//
// param    : 없음
//
// return   : 없음
// -----------------------------------------------------------------------------------
function checkedRate() {
    rateWraps.forEach(wrap => {
        const stars        = wrap.querySelectorAll('.star-icon');
        const checkedInput = wrap.querySelector('.rating__input:checked');

        resetStars(stars); // 모든 별 초기화

        if (checkedInput) {
            const idx = [...wrap.querySelectorAll('.rating__input')].indexOf(checkedInput);
            fillStars(idx, stars); // 선택된 별 채우기
        }
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : setHoverOpacity
// 설명     : 호버된 별의 투명도를 설정
//
// param    : NodeList stars - 별 아이콘 노드 리스트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function setHoverOpacity(stars) {
    stars.forEach(star => {
        if (star.classList.contains('filled')) {
            star.style.opacity = opacityHover; // 채워진 별의 투명도 설정
        }
    });
}

// -----------------------------------------------------------------------------------
// 함수명   : resetOpacity
// 설명     : 별의 투명도를 초기화
//
// param    : NodeList stars - 별 아이콘 노드 리스트
//
// return   : 없음
// -----------------------------------------------------------------------------------
function resetOpacity(stars) {
    stars.forEach(star => (star.style.opacity = '1')); // 투명도 초기화
}
