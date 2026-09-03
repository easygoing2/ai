<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$faq_skin_url.'/style.css">', 0);
?>

<!-- FAQ 시작 { -->
<div class="faqListWrap">
    
    <?php
    if ($himg_src)
        echo '<div class="faq_himg"><img src="'.$himg_src.'" alt=""></div>';

    // 상단 HTML
    if($fm['fm_head_html'])
        echo '<div class="faq_hhtml">'.conv_content($fm['fm_head_html'], 1).'</div>';
    ?>

    <!-- 검색 영역 -->
    <div class="searchBox bg-wh round-m padding-m">
        <form name="faq_search_form" method="get">
            <input type="hidden" name="fm_id" value="<?php echo $fm_id;?>">
            <div class="searchForm">
                <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="stx" value="<?php echo $stx;?>" required id="stx" class="searchInput" placeholder="FAQ 검색어를 입력하세요">
                <button type="submit" class="button bg-pr color-wh-only round-m bxicon mBtn">
                    <i class='bx bx-search'></i> 검색
                </button>
            </div>
        </form>
    </div>

    <?php
    if( count($faq_master_list) ){
    ?>
    <!-- 카테고리 영역 -->
    <div class="catagory">
        <h2 class="sr-only">자주하시는질문 분류</h2>
        <ul>
            <?php
            foreach( $faq_master_list as $v ){
                $category_msg = '';
                $category_option = '';
                if($v['fm_id'] == $fm_id){ // 현재 선택된 카테고리라면
                    $category_option = ' id="bo_cate_on"';
                    $category_msg = '<span class="sound_only">열린 분류 </span>';
                }
            ?>
            <li><a href="<?php echo $category_href;?>?fm_id=<?php echo $v['fm_id'];?>" <?php echo $category_option;?> ><?php echo $category_msg.$v['fm_subject'];?></a></li>
            <?php
            }
            ?>
        </ul>
    </div>
    <?php } ?>

    <div class="faqWrap faq_<?php echo $fm_id; ?>">
        <?php // FAQ 내용
        if( count($faq_list) ){
        ?>
        <div class="faqListBox">
            <h2 class="sr-only"><?php echo $g5['title']; ?> 목록</h2>
            <?php
            foreach($faq_list as $key=>$v){
                if(empty($v))
                    continue;
            ?>
            <div class="faqItem bg-wh round-m">
                <div class="faqQuestion" onclick="return faq_open(this);">
                    <div class="qIcon">Q</div>
                    <div class="qContent">
                        <h3><?php echo conv_content($v['fa_subject'], 1); ?></h3>
                    </div>
                    <button class="toggleBtn" type="button">
                        <i class='bx bx-chevron-down'></i>
                        <span class="sound_only">열기</span>
                    </button>
                </div>
                <div class="faqAnswer">
                    <div class="aIcon">A</div>
                    <div class="aContent">
                        <?php echo conv_content($v['fa_content'], 1); ?>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
        <?php
        } else {
            if($stx){
                echo '<div class="emptyBox bg-wh round-m padding-m"><p class="emptyMessage"><i class="bx bx-search"></i> 검색된 게시물이 없습니다.</p></div>';
            } else {
                echo '<div class="emptyBox bg-wh round-m padding-m">';
                echo '<p class="emptyMessage"><i class="bx bx-info-circle"></i> 등록된 FAQ가 없습니다.</p>';
                if($is_admin)
                    echo '<a href="'.G5_ADMIN_URL.'/faqmasterlist.php" class="button bg-pr color-wh-only round-m bxicon mBtn"><i class="bx bx-plus"></i> FAQ 관리</a>';
                echo '</div>';
            }
        }
        ?>
    </div>

    <!-- 페이징 -->
    <div class="listFooter">
        <?php echo get_paging($page_rows, $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page='); ?>
    </div>

    <?php
    // 하단 HTML
    if($fm['fm_tail_html'])
        echo '<div class="faq_thtml">'.conv_content($fm['fm_tail_html'], 1).'</div>';

    if ($timg_src)
        echo '<div class="faq_timg"><img src="'.$timg_src.'" alt=""></div>';
    ?>

    <?php
    if ($admin_href) {
    ?>
    <div class="admBtn">
        <a href="<?php echo $admin_href; ?>" class="button bg-pr color-wh-only round-m bxicon mBtn">
            <i class='bx bx-cog'></i> FAQ 수정
        </a>
    </div>
    <?php } ?>

</div>
<!-- } FAQ 끝 -->

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script>
function faq_open(el) {
    var $item = $(el).closest(".faqItem"),
        $answer = $item.find(".faqAnswer"),
        $toggleBtn = $item.find(".toggleBtn i");

    if($answer.is(":visible")) {
        $answer.slideUp(200);
        $item.removeClass("active");
        $toggleBtn.removeClass("bx-chevron-up").addClass("bx-chevron-down");
    } else {
        // 다른 열린 항목 닫기
        $(".faqItem.active").each(function() {
            $(this).find(".faqAnswer").slideUp(200);
            $(this).removeClass("active");
            $(this).find(".toggleBtn i").removeClass("bx-chevron-up").addClass("bx-chevron-down");
        });

        $answer.slideDown(200, function() {
            // 이미지 리사이즈
            $answer.viewimageresize2();
        });
        $item.addClass("active");
        $toggleBtn.removeClass("bx-chevron-down").addClass("bx-chevron-up");
    }

    return false;
}

$(function() {
    // 토글 버튼 클릭 시에도 faq_open 실행
    $(".toggleBtn").on("click", function(e) {
        e.stopPropagation();
        faq_open($(this).closest(".faqQuestion"));
    });
});
</script>