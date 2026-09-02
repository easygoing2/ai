<?php
if (!defined('_GNUBOARD_')) exit;

function wzy_render_watch_assets() {
    global $g5, $member, $bo_table, $wr_id, $view, $write;

    if (empty($member['mb_id'])) return;
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/config.php');
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/youtube_watch.lib.php');
    if (!wzy_schema_installed()) return;
    $watch_config = wzy_get_config();
    if (empty($watch_config['wyc_use'])) return;

    $current_post = null;
    $post = !empty($view['wr_id']) ? $view : (!empty($write['wr_id']) ? $write : array());
    if (!empty($bo_table) && !empty($wr_id) && $post) {
        $video_ids = wzy_post_video_ids($post);
        if ($video_ids) {
            $current_post = array(
                'boTable' => (string)$bo_table,
                'wrId' => (int)$wr_id,
                'videoId' => $video_ids[0]
            );
        }
    }

    $runtime = array(
        'apiBase' => WZY_PLUGIN_URL,
        'csrfToken' => wzy_csrf_token(),
        'completionPercent' => wzy_completion_threshold($watch_config),
        'countShortSeek' => !empty($watch_config['wyc_count_short_seek']),
        'saveInterval' => max(5, min(60, (int)$watch_config['wyc_save_interval'])),
        'showListBadge' => !empty($watch_config['wyc_show_list_badge']),
        'calendarUse' => !empty($watch_config['wyc_calendar_use']),
        'calendarPercent' => wzy_calendar_threshold($watch_config),
        'currentPost' => $current_post
    );
    $json = json_encode($runtime, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo '<link rel="stylesheet" href="'.WZY_PLUGIN_URL.'/style.css?v='.rawurlencode(WZY_VERSION).'">'.PHP_EOL;
    echo '<script type="application/json" id="wzyRuntimeConfig">'.$json.'</script>'.PHP_EOL;
    echo '<script src="'.WZY_PLUGIN_URL.'/youtube-watch.js?v='.rawurlencode(WZY_VERSION).'" defer></script>'.PHP_EOL;
}

function wzy_mark_calendar_event_deleted($mb_id, $event_id) {
    global $g5;
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/config.php');
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/youtube_watch.lib.php');
    if (!wzy_schema_installed()) return;

    $mb_sql = sql_escape_string((string)$mb_id);
    sql_query("UPDATE `{$g5['wzy_watch_table']}` SET ww_calendar_status='deleted', ww_updated_at=NOW()
        WHERE mb_id='{$mb_sql}' AND ww_calendar_event_id=".(int)$event_id, false);
}

function wzy_delete_member_watch_data($mb_id) {
    global $g5;
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/config.php');
    include_once(G5_PLUGIN_PATH.'/wz.youtube_watch/youtube_watch.lib.php');
    if (!wzy_schema_installed()) return;

    $mb_sql = sql_escape_string((string)$mb_id);
    sql_query("DELETE FROM `{$g5['wzy_watch_table']}` WHERE mb_id='{$mb_sql}'", false);
}

add_event('tail_sub', 'wzy_render_watch_assets', 20);
add_event('wzc_event_deleted', 'wzy_mark_calendar_event_deleted', 10, 2);
add_event('member_delete_after', 'wzy_delete_member_watch_data', 10, 1);
