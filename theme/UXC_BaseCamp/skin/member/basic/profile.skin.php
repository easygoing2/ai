<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 자기소개 시작 { -->
<div class="profileViewWrap">
    <div class="profileViewHeader">
        <h2><i class='bx bx-user-circle'></i> <?php echo $mb_nick ?>님의 프로필</h2>
        <button type="button" class="button shadowline-de round-s bxicon color-gray-800 sBtn" onclick="window.close();">
            <i class='bx bx-x'></i>
            <span class="text">닫기</span>
        </button>
    </div>
    
    <!-- 프로필 요약 -->
    <div class="profileSummary bg-pr round-m padding-m">
        <div class="profileUser">
            <div class="profileImg">
                <?php echo get_member_profile_img($mb['mb_id']); ?>
            </div>
            <div class="profileName">
                <strong><?php echo $mb_nick ?></strong>
                <span class="level">레벨 <?php echo $mb['mb_level'] ?></span>
            </div>
        </div>
    </div>

    <!-- 프로필 상세 정보 -->
    <div class="profileDetailBox">
        <div class="profileItem bg-wh round-m padding-m">
            <div class="profileItemInfo">
                <div class="profileItemContent">
                    <strong class="itemTitle"><i class='bx bx-coin-stack'></i> 포인트</strong>
                    <span class="itemValue"><?php echo number_format($mb['mb_point']) ?>P</span>
                </div>
            </div>
        </div>

        <div class="profileItem bg-wh round-m padding-m">
            <div class="profileItemInfo">
                <div class="profileItemContent">
                    <strong class="itemTitle"><i class='bx bx-calendar-plus'></i> 가입일</strong>
                    <span class="itemValue"><?php echo ($member['mb_level'] >= $mb['mb_level']) ?  substr($mb['mb_datetime'],0,10) ." (".number_format($mb_reg_after)." 일)" : "알 수 없음";  ?></span>
                </div>
            </div>
        </div>

        <div class="profileItem bg-wh round-m padding-m">
            <div class="profileItemInfo">
                <div class="profileItemContent">
                    <strong class="itemTitle"><i class='bx bx-time-five'></i> 최종접속</strong>
                    <span class="itemValue"><?php echo ($member['mb_level'] >= $mb['mb_level']) ? $mb['mb_today_login'] : "알 수 없음"; ?></span>
                </div>
            </div>
        </div>

        <?php if ($mb_homepage) {  ?>
        <div class="profileItem bg-wh round-m padding-m">
            <div class="profileItemInfo">
                <div class="profileItemContent">
                    <strong class="itemTitle"><i class='bx bx-home'></i> 홈페이지</strong>
                    <span class="itemValue"><a href="<?php echo $mb_homepage ?>" target="_blank"><?php echo $mb_homepage ?></a></span>
                </div>
            </div>
        </div>
        <?php }  ?>

        <?php if ($mb_profile) { ?>
        <div class="profileItem bg-wh round-m padding-m">
            <div class="profileItemInfo">
                <div class="profileItemContent full">
                    <strong class="itemTitle"><i class='bx bx-message-rounded-dots'></i> 인사말</strong>
                    <div class="profileGreeting">
                        <?php echo nl2br($mb_profile) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<!-- } 자기소개 끝 -->