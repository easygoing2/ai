/***********************************************************
 * widget Ui
 ************************************************************/
$(document).ready(function() {
	common();
	initDropdownMenu();
	initSearchFocus();
	displayEditorName();

	// 페이지 로드 시 저장된 테마에 따라 Toast UI Editor 다크모드 적용
	const currentTheme = localStorage.getItem("theme");
	if (currentTheme === "darkMode") {
		// Toast UI Editor 다크모드 적용
		document.querySelectorAll('.listBox[data-board="uxc_blog"], .toastui-editor-contents').forEach(el => {
			el.classList.add('toastui-editor-dark');
		});
	}

	// 페이지 로드 시 저장된 사이드 메뉴 상태 적용
	const savedSideState = localStorage.getItem("sideMenu");
	if (savedSideState) {
		$('[data-side]').attr('data-side', savedSideState);
		// html 태그에도 상태 적용
		if (savedSideState === 'off') {
			document.documentElement.classList.add('sideMenuOff');
		} else {
			document.documentElement.classList.remove('sideMenuOff');
		}
	}
});

/***********************************************************
 * common
 ************************************************************/
function common() {

	//drawerNavOn
	$("#drawerNavOn").click(function(){
		$("#drawerNav").addClass("active");
		$("html").addClass("drawerNavOn");
	})

	$("#drawerDim , #drawerNavOff ").click(function(){
		$("#drawerNav").removeClass("active");
		$("html").removeClass("drawerNavOn");
	})

	// sideGnb Toggle
	$("#sideGnbOnf").click(function(){
		var $container = $('[data-side]');
		var currentState = $container.attr('data-side');
		var newState = (currentState === 'on') ? 'off' : 'on';

		// 상태 변경
		$container.attr('data-side', newState);

		// html 태그에도 상태 적용
		if (newState === 'off') {
			document.documentElement.classList.add('sideMenuOff');
		} else {
			document.documentElement.classList.remove('sideMenuOff');
		}

		// 로컬스토리지에 저장
		localStorage.setItem('sideMenu', newState);
	})

	// search
	var btnSearch = "[data-btn='btnSearch']";
	var uxPopSearch = "[data-ui='uxPopSearch']";

	$(btnSearch).click(function() {
		$(uxPopSearch).removeClass("off").addClass("on");
		$(".wrap").addClass("filter_blur");
		$("html").addClass("drawerNavOn");
	});

	var popHide = "[data-ui='popHide']";
	$(popHide).click(function() {
		$(this).parent().removeClass("on").addClass("off");
		$(".wrap").removeClass("filter_blur");
		$("html").removeClass("drawerNavOn");
	});

  // outlogin user menu
  $("#userMenuOnf").click(function() {
		$(".outloginUserMenu").toggleClass("on");
	});

	$(document).on("click", function(e) {
		if (!$(e.target).closest("#userMenuOnf, .outloginUserMenu").length) {
			$(".outloginUserMenu").removeClass("on");
		}
	});


	
};

/***********************************************************
 * darkMode
 ************************************************************/


// 다크모드 토글 함수
function settingview() {
  const root = document.documentElement;
  const currentTheme = localStorage.getItem("theme");

  if (currentTheme === "darkMode") {
    root.classList.remove("darkMode");
    localStorage.setItem("theme", "lightMode"); // 명시적으로 라이트모드로 저장
    root.setAttribute("data-t2editor-theme", "light"); // 다크모드 해제 시 light로 설정
    
    // Toast UI Editor 다크모드 해제
    document.querySelectorAll('.listBox[data-board="uxc_blog"], .toastui-editor-contents').forEach(el => {
      el.classList.remove('toastui-editor-dark');
    });
  } else {
    root.classList.add("darkMode");
    localStorage.setItem("theme", "darkMode");
    root.setAttribute("data-t2editor-theme", "dark"); // 다크모드 적용 시 dark로 설정
    
    // Toast UI Editor 다크모드 적용
    document.querySelectorAll('.listBox[data-board="uxc_blog"], .toastui-editor-contents').forEach(el => {
      el.classList.add('toastui-editor-dark');
    });
  }

  const themeText = document.getElementById("theme");
  if (themeText) {
    themeText.textContent = localStorage.getItem("theme") || "lightMode";
  }
}

/***********************************************************
 * Bookmark Function
 ************************************************************/
function bookmark() {
  const title = document.title;
  const url = window.location.href;
  
  try {
    // 모던 브라우저 (Chrome, Firefox, Safari)
    if (window.external && ('AddFavorite' in window.external)) {
      // Internet Explorer
      window.external.AddFavorite(url, title);
    } else if (window.sidebar && window.sidebar.addPanel) {
      // Firefox (구버전)
      window.sidebar.addPanel(title, url, '');
    } else {
      // 기타 브라우저 - 수동 안내
      const isApple = /Mac|iPhone|iPad|iPod/.test(navigator.userAgent);
      const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
      
      let message;
      if (isMobile) {
        if (isApple) {
          message = '즐겨찾기에 추가하려면:\n\n1. 하단의 공유 버튼을 탭하세요\n2. "홈 화면에 추가" 또는 "즐겨찾기에 추가"를 선택하세요';
        } else {
          message = '즐겨찾기에 추가하려면:\n\n1. 브라우저 메뉴를 여세요\n2. "즐겨찾기에 추가" 또는 "북마크 추가"를 선택하세요';
        }
      } else {
        if (isApple) {
          message = '즐겨찾기에 추가하려면:\n\nCmd+D를 누르거나 상단 메뉴에서 "즐겨찾기에 추가"를 선택하세요';
        } else {
          message = '즐겨찾기에 추가하려면:\n\nCtrl+D를 누르거나 상단 메뉴에서 "즐겨찾기에 추가"를 선택하세요';
        }
      }
      
      alert(message);
    }
  } catch (e) {
    console.error('즐겨찾기 추가 중 오류:', e);
    alert('즐겨찾기 추가에 실패했습니다. 브라우저 메뉴에서 수동으로 추가해주세요.');
  }
}

/***********************************************************
 * Dropdown Menu
 ************************************************************/
function initDropdownMenu() {
    // 드롭다운 메뉴 호버 효과 개선
    $('.gnb-menu > li.has-dropdown').hover(
        function() {
            // 마우스 진입 시
            clearTimeout($(this).data('hideTimer'));
            $(this).find('.dropdown-menu').stop(true, true).fadeIn(200);
        },
        function() {
            // 마우스 이탈 시
            var $this = $(this);
            var hideTimer = setTimeout(function() {
                $this.find('.dropdown-menu').stop(true, true).fadeOut(200);
            }, 100);
            $this.data('hideTimer', hideTimer);
        }
    );

    // 모바일에서 클릭 이벤트 처리
    if ($(window).width() <= 768) {
        $('.gnb-menu > li.has-dropdown > a').on('click', function(e) {
            var $parent = $(this).parent();
            var $dropdown = $parent.find('.dropdown-menu');
            
            // 서브메뉴가 있고 현재 닫혀있다면 열기
            if ($dropdown.length && !$dropdown.is(':visible')) {
                e.preventDefault();
                $('.gnb-menu .dropdown-menu').not($dropdown).slideUp(200);
                $dropdown.slideToggle(200);
                
                // 화살표 아이콘 회전
                $('.gnb-menu > li.has-dropdown').not($parent).removeClass('active');
                $parent.toggleClass('active');
            }
        });
    }

    // 윈도우 리사이즈 시 이벤트 재설정
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if ($(window).width() > 768) {
                // 데스크톱 모드: 모든 드롭다운 숨기고 hover 효과만 사용
                $('.gnb-menu .dropdown-menu').removeAttr('style');
                $('.gnb-menu > li.has-dropdown').removeClass('active');
            }
        }, 250);
    });

    // 페이지 클릭 시 모바일 드롭다운 닫기
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.gnb-menu').length && $(window).width() <= 768) {
            $('.gnb-menu .dropdown-menu').slideUp(200);
            $('.gnb-menu > li.has-dropdown').removeClass('active');
        }
    });
}

/***********************************************************
 * Search Focus Handler
 ************************************************************/
function initSearchFocus() {
    // 검색 입력 필드에 포커스 이벤트 처리
    $(document).on('focus', '#sch_stx', function() {
        // data-focus 속성을 가진 가장 가까운 부모 요소 찾기
        var $searchWrap = $(this).closest('[data-focus]');
        if ($searchWrap.length) {
            $searchWrap.attr('data-focus', 'on');
        }
    });
    
    // 검색 영역과 popularWrap 영역 외부 클릭 시 data-focus를 off로 변경
    $(document).on('click', function(e) {
        // 클릭한 요소가 검색 영역과 popularWrap 영역 내부가 아닌 경우
        if (!$(e.target).closest('[data-focus], .popularWrap').length) {
            $('[data-focus]').attr('data-focus', 'off');
        }
    });
}

/***********************************************************
 * Display Editor Name
 ************************************************************/
function displayEditorName() {
    // g5_editor 전역 변수에서 에디터 이름 가져오기
    if (typeof g5_editor !== 'undefined' && g5_editor) {
        // 에디터 이름은 그대로 사용
        var editorName = g5_editor;
       
        // viewContText 아래의 content 요소에 에디터 이름을 클래스로 추가
        $('.viewContText .content').each(function() {
            if (!$(this).hasClass(editorName)) {
                $(this).addClass(editorName);
            }
        });
    }
}
