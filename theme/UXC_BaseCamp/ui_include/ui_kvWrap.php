<?php if (!defined("_INDEX_")) { ?>
<div class="kvWrap skvWrap">
    <div class="skvWrap">
        <div class="skv resWidth">
            
            <div class="boardTitle">
                <div class="titleBox">
                    <div class="title">
                        <h2>
                            <?php if ($board['bo_subject']) { ?>
                                <?php echo get_text($board['bo_subject']) ?>
                            <?php } else { ?>
                                <?php echo get_text($g5['title']); ?>
                            <?php } ?>
                        </h2>
                    </div>
                    <ul class="breadcrumbs">
                        <li>Home</li>
                        <?php
                        // 현재 페이지가 속한 메뉴 그룹(대메뉴) 가져오기
                        $current_menu_group = uxc_get_current_menu_group();

                        // 메뉴 그룹 표시 (없으면 게시판 그룹으로 폴백)
                        if ($current_menu_group) {
                            echo '<li>'.get_text($current_menu_group).'</li>';
                        } else if (isset($group['gr_subject']) && $group['gr_subject']) {
                            echo '<li>'.get_text($group['gr_subject']).'</li>';
                        }

                        // 현재 게시판 제목 표시
                        if ($bo_table){
                            echo '<li>'.get_text($board['bo_subject']).'</li>';
                        }
                        ?>
                    </ul>
                </div>
                <!-- bbsChart  -->
                <div class="chart" data-item="bbsChart">
                    <?php
                    include G5_THEME_PATH.'/ui_module/ui_bbsChart/ui_bbsChart.php'; // bbsChart
                    ?> 
                </div>
            </div>
            
        </div>
    </div>
    
    
</div>
<?php } ?>