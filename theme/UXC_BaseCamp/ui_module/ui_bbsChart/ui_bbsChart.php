<?php if ($board['bo_subject']) { ?>
<?php 
// css load
add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/ui_module/ui_bbsChart/style.css">', 0);
?>
<div class="boardInfo">
    <?php
    // 총 게시글 수
    $total_sql = "SELECT COUNT(*) as cnt FROM {$write_table} WHERE wr_is_comment = 0";
    $total_row = sql_fetch($total_sql);
    $total_posts = $total_row['cnt'];
    
    // 오늘 작성된 글 수
    $today_start = date('Y-m-d 00:00:00');
    $today_sql = "SELECT COUNT(*) as cnt FROM {$write_table} 
                    WHERE wr_datetime >= '".sql_escape_string($today_start)."' 
                    AND wr_is_comment = 0";
    $today_row = sql_fetch($today_sql);
    $today_posts = $today_row['cnt'];
    ?>
    <div class="boardStats">
        <div class="statItem">
            <span class="statLabel">Total</span>
            <span class="statValue"><?php echo number_format($total_posts); ?></span>
        </div>
        <div class="statItem">
            <span class="statLabel">Today</span>
            <span class="statValue"><?php echo number_format($today_posts); ?></span>
        </div>
    </div>
</div>
<?php } ?> 