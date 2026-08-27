<?php 
// css load
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_module/ui_mainSlider/style.css">', 0);


// G5_IS_DEBUG 상수 정의 확인
if(!defined('G5_IS_DEBUG')) define('G5_IS_DEBUG', false);

// 슬라이더 데이터 조회 (테이블명 변수 사용)
$slider_table = (isset($g5['theme_slider_table']) ? $g5['theme_slider_table'] : 'g5_theme_slider');
$slider_sql = "SELECT * FROM {$slider_table} WHERE slide_use = 1 ORDER BY slide_order ASC, slide_id DESC";
$slider_result = sql_query($slider_sql, false);
$sliders = array();
if($slider_result) {
    while($row = sql_fetch_array($slider_result)) {
        $sliders[] = $row;
    }
}

if(count($sliders) > 0) {
?>
<div class="kvWrap resWidth round-m" data-section="main_kv">
    <?php if($is_admin == 'super') { ?>
    <a href="<?php echo G5_BBS_URL; ?>/uxc_theme_slider.php" class="slider-admin-btn" title="슬라이더 관리">
        <i class='bx bx-cog'></i>
    </a>
    <?php } ?>
    <div class="swiper mainSwiper">
        <div class="swiper-wrapper">
            <?php foreach($sliders as $slide) { 
                // 이미지 파일명 안전하게 처리 (경로 탐색 방지)
                $slide_image = basename($slide['slide_image']);
                $slide_image = preg_replace('/[^a-zA-Z0-9\._-]/', '', $slide_image);
                if(empty($slide_image)) $slide_image = 'default.jpg';
            ?>
            <div class="swiper-slide" data-text-color="<?php echo htmlspecialchars($slide['text_color']); ?>">
                <div class="slideItem" style="background-image: url('<?php echo G5_DATA_URL; ?>/mainkv/<?php echo $slide_image; ?>');">
                    <div class="slideContent">
                        <div class="slideInner">
                            <?php if($slide['slide_title']) { ?>
                            <h2 class="slideTitle <?php echo htmlspecialchars($slide['text_color']); ?>" data-swiper-parallax="-100"><?php echo get_text($slide['slide_title']); ?></h2>
                            <?php } ?>
                            <?php if($slide['slide_subtitle']) { ?>
                            <p class="slideSubtitle <?php echo htmlspecialchars($slide['text_color']); ?>" data-swiper-parallax="-200"><?php echo get_text($slide['slide_subtitle']); ?></p>
                            <?php } ?>
                            <?php if($slide['slide_button'] && $slide['slide_link']) { ?>
                            <a href="<?php echo htmlspecialchars($slide['slide_link']); ?>" target="<?php echo htmlspecialchars(isset($slide['slide_link_target']) ? $slide['slide_link_target'] : '_self'); ?>" data-slide-id="<?php echo htmlspecialchars($slide['slide_id']); ?>" data-swiper-parallax="-300" class="slideButton button mBtn <?php 
                                switch($slide['button_color']) {
                                    case 'white': echo 'bg-wh color-bl shadowline-de'; break;
                                    case 'black': echo 'bg-bl color-wh'; break;
                                    case 'pink': echo 'bg-pink color-wh'; break;
                                    case 'purple': echo 'bg-purple color-wh'; break;
                                    case 'yellow': echo 'bg-yellow color-bl'; break;
                                    case 'green': echo 'bg-green color-wh'; break;
                                    case 'mint': echo 'bg-mint color-wh'; break;
                                    case 'lightblue': echo 'bg-lightblue color-wh'; break;
                                    case 'blue': echo 'bg-blue color-wh'; break;
                                    default: echo 'bg-pr color-wh'; break;
                                }
                            ?> round-m">
                                <?php echo get_text($slide['slide_button']); ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php if(count($sliders) > 1) { ?>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"><i class='bx bx-chevron-left'></i></div>
        <div class="swiper-button-next"><i class='bx bx-chevron-right'></i></div>
        <div class="swiper-button-play" data-playing="true">
            <i class='bx bx-pause'></i>
            <i class='bx bx-play' style="display:none;"></i>
        </div>
        <?php } ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainSwiper = new Swiper('.mainSwiper', {
        loop: <?php echo count($sliders) > 1 ? 'true' : 'false'; ?>,
        autoplay: <?php echo count($sliders) > 1 ? '{delay: 5000, disableOnInteraction: false}' : 'false'; ?>,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        parallax: true,
        speed: 800,
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev'
        },
        on: {
            init: function() {
                updateNavigationColor(this);
                // 슬라이더 초기화 후 클릭 이벤트 설정
                setupSliderClickTracking();
            },
            slideChange: function() {
                updateNavigationColor(this);
            }
        }
    });
    
    function updateNavigationColor(swiper) {
        const activeSlide = swiper.slides[swiper.activeIndex];
        const textColor = activeSlide.getAttribute('data-text-color');
        const prevBtn = document.querySelector('.swiper-button-prev');
        const nextBtn = document.querySelector('.swiper-button-next');
        const playBtn = document.querySelector('.swiper-button-play');
        const pagination = document.querySelector('.swiper-pagination');
        
        if(textColor === 'white') {
            if(prevBtn) prevBtn.classList.add('white');
            if(prevBtn) prevBtn.classList.remove('black');
            if(nextBtn) nextBtn.classList.add('white');
            if(nextBtn) nextBtn.classList.remove('black');
            if(playBtn) playBtn.classList.add('white');
            if(playBtn) playBtn.classList.remove('black');
            if(pagination) pagination.classList.add('white');
            if(pagination) pagination.classList.remove('black');
        } else {
            if(prevBtn) prevBtn.classList.add('black');
            if(prevBtn) prevBtn.classList.remove('white');
            if(nextBtn) nextBtn.classList.add('black');
            if(nextBtn) nextBtn.classList.remove('white');
            if(playBtn) playBtn.classList.add('black');
            if(playBtn) playBtn.classList.remove('white');
            if(pagination) pagination.classList.add('black');
            if(pagination) pagination.classList.remove('white');
        }
    }
    
    // 재생/일시정지 버튼 기능
    const playBtn = document.querySelector('.swiper-button-play');
    if(playBtn) {
        playBtn.addEventListener('click', function() {
            const isPlaying = this.getAttribute('data-playing') === 'true';
            const pauseIcon = this.querySelector('.bx-pause');
            const playIcon = this.querySelector('.bx-play');
            
            if(isPlaying) {
                mainSwiper.autoplay.stop();
                this.setAttribute('data-playing', 'false');
                pauseIcon.style.display = 'none';
                playIcon.style.display = 'block';
            } else {
                mainSwiper.autoplay.start();
                this.setAttribute('data-playing', 'true');
                pauseIcon.style.display = 'block';
                playIcon.style.display = 'none';
            }
        });
    }
    
    // 슬라이더 버튼 클릭 카운트 함수
    function setupSliderClickTracking() {
        // setTimeout을 사용하여 DOM이 완전히 렌더링된 후 실행
        setTimeout(() => {
            const slideButtons = document.querySelectorAll('.slideButton');
            
            slideButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const slideId = this.getAttribute('data-slide-id');
                    
                    // Base64로 인코딩된 엔드포인트 (보안 강화)
                    const endpoint = atob('<?php echo base64_encode(G5_BBS_URL."/uxc_theme_slider.php"); ?>');
                    
                    // 통계 수집
                    fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            'action': 'update_click',
                            'slide_id': slideId,
                            'token': '<?php echo get_session("ss_token"); ?>'
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        // 성공 시 처리 (디버그 모드에서만 콘솔 출력)
                        <?php if(G5_IS_DEBUG) { ?>
                        if(data.success) {
                            console.log('Stats updated');
                        }
                        <?php } ?>
                    })
                    .catch(error => {
                        // 에러는 조용히 처리 (디버그 모드에서만 출력)
                        <?php if(G5_IS_DEBUG) { ?>
                        console.error('Stats error:', error);
                        <?php } ?>
                    });
                });
            });
        }, 100);
    }
});
</script>
<?php } // end if count($sliders) > 0
?>