<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 포인트 목록 래퍼 -->
<div class="pointListWrap">
    
    <!-- 헤더 영역 -->
    <div class="pointHeader">
        <h2><i class='bx bx-coin-stack'></i> <?php echo $g5['title'] ?></h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>

    <!-- 포인트 요약 -->
    <div class="pointSummary bg-pr round-m padding-m">
        <div class="summaryItem">
            <span class="label">보유포인트</span>
            <strong class="value"><?php echo number_format($member['mb_point']); ?>P</strong>
        </div>
    </div>

    <!-- 포인트 목록 -->
    <div class="pointListBox">
        <?php
        $sum_point1 = $sum_point2 = $sum_point3 = 0;
        $i = 0;
        
        foreach((array) $list as $row){
            $point1 = $point2 = 0;
            $point_use_class = '';
            if ($row['po_point'] > 0) {
                $point1 = '+' .number_format($row['po_point']);
                $sum_point1 += $row['po_point'];
                $point_use_class = 'plus';
            } else {
                $point2 = number_format($row['po_point']);
                $sum_point2 += $row['po_point'];
                $point_use_class = 'minus';
            }

            $po_content = $row['po_content'];

            $expr = '';
            if($row['po_expired'] == 1)
                $expr = ' expired';
        ?>
        <div class="pointItem bg-wh round-m padding-m <?php echo $point_use_class; ?>">
            <div class="pointInfo">
                <div class="pointContent">
                    <strong class="pointTitle"><?php echo $po_content; ?></strong>
                    <div class="pointMeta">
                        <span class="pointDate"><i class='bx bx-time'></i> <?php echo date("y-m-d H:i", strtotime($row['po_datetime'])); ?></span>
                        <?php if ($row['po_expired'] == 1) { ?>
                        <span class="pointExpire expired"><i class='bx bx-error-circle'></i> 만료 <?php echo substr(str_replace('-', '', $row['po_expire_date']), 2); ?></span>
                        <?php } else if($row['po_expire_date'] != '9999-12-31') { ?>
                        <span class="pointExpire"><i class='bx bx-calendar-x'></i> ~<?php echo $row['po_expire_date']; ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="pointNum <?php echo $point_use_class; ?>">
                    <?php if ($point1) echo $point1; else echo $point2; ?>P
                </div>
            </div>
        </div>
        <?php
            $i++;
        }

        if ($i == 0) {
            echo '<div class="emptyBox bg-wh round-m padding-m">';
            echo '<p class="emptyMessage"><i class="bx bx-coin-stack"></i> 포인트 내역이 없습니다.</p>';
            echo '</div>';
        }
        ?>
        
        <?php if ($i > 0) { 
            if ($sum_point1 > 0)
                $sum_point1 = "+" . number_format($sum_point1);
            $sum_point2 = number_format($sum_point2);
        ?>
        <!-- 포인트 소계 -->
        <div class="pointTotal bg-gray-100 round-m padding-m">
            <div class="totalItem">
                <span class="label">소계</span>
                <div class="values">
                    <span class="plus"><?php echo $sum_point1; ?>P</span>
                    <span class="minus"><?php echo $sum_point2; ?>P</span>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 페이징 -->
    <div class="listFooter">
        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page='); ?>
    </div>

</div>