<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$popular_skin_url.'/style.css">', 0);
?>

<!-- 인기검색어 시작 { -->
<div class="popularWrap">

    <div class="popular_inner">
        <ul class="popularList">
        <?php
        if( isset($list) && is_array($list) ){
            for ($i=0; $i<count($list); $i++) {
            ?>
            <li class="item">
                <a href="<?php echo G5_BBS_URL ?>/search.php?sfl=wr_subject||wr_content&amp;sop=and&amp;stx=<?php echo urlencode($list[$i]['pp_word']) ?>" class="keyword">
                    <span class="rank"><?php echo $i + 1; ?></span>
                    <span class="word"><?php echo get_text($list[$i]['pp_word']); ?></span>
                    <?php if($i < 3) { ?>
                    <span class="badge hot">HOT</span>
                    <?php } ?>
                </a>
            </li>
            <?php
            }   //end for
        }   //end if
        
        if (!isset($list) || !$list || count($list) == 0) {
            echo '<li class="empty">등록된 인기검색어가 없습니다.</li>';
        }
        ?>
        </ul>
    </div>
</div>

<?php if (isset($list) && $list && is_array($list) && count($list) > 5) { ?>
<script>
jQuery(function($){
    var $wrap = $('.popularWrap');
    var $list = $wrap.find('.popularList');
    var $items = $list.find('.item');
    var itemWidth = $items.first().outerWidth(true);
    var currentIndex = 0;
    var itemsToShow = 5;
    var totalItems = $items.length;
    
    // 초기 설정
    $list.css({
        'display': 'flex',
        'transition': 'transform 0.3s ease',
        'transform': 'translateX(0)'
    });
    
    // 다음 버튼
    $wrap.find('.pp-next').on('click', function() {
        if (currentIndex < totalItems - itemsToShow) {
            currentIndex++;
            updatePosition();
        } else {
            // 마지막에 도달하면 처음으로
            currentIndex = 0;
            updatePosition();
        }
    });
    
    // 이전 버튼
    $wrap.find('.pp-prev').on('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            updatePosition();
        } else {
            // 처음에서 이전 클릭시 마지막으로
            currentIndex = totalItems - itemsToShow;
            updatePosition();
        }
    });
    
    // 위치 업데이트
    function updatePosition() {
        var offset = -(currentIndex * itemWidth);
        $list.css('transform', 'translateX(' + offset + 'px)');
    }
    
    // 자동 슬라이드 (선택사항)
    /*
    setInterval(function() {
        $wrap.find('.pp-next').trigger('click');
    }, 5000);
    */
});
</script>
<?php } ?>
<!-- } 인기검색어 끝 -->