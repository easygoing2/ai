<?php
if (!defined('_GNUBOARD_')) exit;
@include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// 새글 목록 추출
// $cache_time 캐시 갱신시간
function get_new_list($skin_dir = '', $rows = 10, $subject_len = 40, $cache_time = 1, $options = '')
{
  global $g5;

  if (!$skin_dir) $skin_dir = 'basic';

  $time_unit = 3600;  // 1시간으로 고정

  if (preg_match('#^theme/(.+)$#', $skin_dir, $match)) {
    if (G5_IS_MOBILE) {
      $new_skin_path = G5_THEME_MOBILE_PATH . '/' . G5_SKIN_DIR . '/new/' . $match[1];
      if (!is_dir($new_skin_path))
        $new_skin_path = G5_THEME_PATH . '/' . G5_SKIN_DIR . '/new/' . $match[1];
      $new_skin_url = str_replace(G5_PATH, G5_URL, $new_skin_path);
    } else {
      $new_skin_path = G5_THEME_PATH . '/' . G5_SKIN_DIR . '/new/' . $match[1];
      $new_skin_url = str_replace(G5_PATH, G5_URL, $new_skin_path);
    }
    $skin_dir = $match[1];
  } else {
    if (G5_IS_MOBILE) {
      $new_skin_path = G5_MOBILE_PATH . '/' . G5_SKIN_DIR . '/new/' . $skin_dir;
      $new_skin_url  = G5_MOBILE_URL . '/' . G5_SKIN_DIR . '/new/' . $skin_dir;
    } else {
      $new_skin_path = G5_SKIN_PATH . '/new/' . $skin_dir;
      $new_skin_url  = G5_SKIN_URL . '/new/' . $skin_dir;
    }
  }

  $caches = false;

  if (G5_USE_CACHE) {
    $cache_file_name = "new-list-{$skin_dir}-{$rows}-{$subject_len}-" . g5_cache_secret_key();
    $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
    $cache_list = isset($caches['list']) ? $caches['list'] : array();
  }

  if ($caches === false) {

    $list = array();

    // 새글 목록 가져오기 (new.php 로직)
    $sql_common = " from {$g5['board_new_table']} a, {$g5['board_table']} b, {$g5['group_table']} c where a.bo_table = b.bo_table and b.gr_id = c.gr_id and b.bo_use_search = 1 ";
    $sql_order = " order by a.bn_id desc ";

    // 새글 지정 개수만큼 가져오기
    $sql = " select a.*, b.bo_subject, b.bo_mobile_subject, c.gr_subject, c.gr_id {$sql_common} {$sql_order} limit 0, {$rows} ";
    $result = sql_query($sql);

    for ($i = 0; $row = sql_fetch_array($result); $i++) {
      $tmp_write_table = $g5['write_prefix'] . $row['bo_table'];

      if ($row['wr_id'] == $row['wr_parent']) {
        // 원글
        $comment = "";
        $comment_link = "";
        $row2 = sql_fetch(" select * from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");
        $list[$i] = $row2;

        $name = get_sideview($row2['mb_id'], get_text(cut_str($row2['wr_name'], $config['cf_cut_name'])), $row2['wr_email'], $row2['wr_homepage']);
        // 당일인 경우 시간으로 표시함
        $datetime = substr($row2['wr_datetime'], 0, 10);
        $datetime2 = $row2['wr_datetime'];
        if ($datetime == G5_TIME_YMD) {
          $datetime2 = substr($datetime2, 11, 5);
        } else {
          $datetime2 = substr($datetime2, 5, 5);
        }
      } else {
        // 코멘트
        $comment = '[코] ';
        $comment_link = '#c_' . $row['wr_id'];
        $row2 = sql_fetch(" select * from {$tmp_write_table} where wr_id = '{$row['wr_parent']}' ");
        $row3 = sql_fetch(" select mb_id, wr_name, wr_email, wr_homepage, wr_datetime from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");
        $list[$i] = $row2;
        $list[$i]['wr_id'] = $row['wr_id'];
        $list[$i]['mb_id'] = $row3['mb_id'];
        $list[$i]['wr_name'] = $row3['wr_name'];
        $list[$i]['wr_email'] = $row3['wr_email'];
        $list[$i]['wr_homepage'] = $row3['wr_homepage'];

        $name = get_sideview($row3['mb_id'], get_text(cut_str($row3['wr_name'], $g5['config']['cf_cut_name'])), $row3['wr_email'], $row3['wr_homepage']);
        // 당일인 경우 시간으로 표시함
        $datetime = substr($row3['wr_datetime'], 0, 10);
        $datetime2 = $row3['wr_datetime'];
        if ($datetime == G5_TIME_YMD) {
          $datetime2 = substr($datetime2, 11, 5);
        } else {
          $datetime2 = substr($datetime2, 5, 5);
        }
      }

      $list[$i]['gr_id'] = $row['gr_id'];
      $list[$i]['bo_table'] = $row['bo_table'];
      $list[$i]['name'] = $name;
      $list[$i]['comment'] = $comment;
      $list[$i]['href'] = get_pretty_url($row['bo_table'], $row2['wr_id'], $comment_link);
      $list[$i]['datetime'] = $datetime;
      $list[$i]['datetime2'] = $datetime2;

      $list[$i]['gr_subject'] = $row['gr_subject'];
      $list[$i]['bo_subject'] = ((G5_IS_MOBILE && $row['bo_mobile_subject']) ? $row['bo_mobile_subject'] : $row['bo_subject']);
      $list[$i]['wr_subject'] = $row2['wr_subject'];

      // 이미지 정보 가져오기
      $list[$i]['image'] = '';
      $file_table = $g5['board_file_table'];
      $sql = " select bf_file, bf_content from {$file_table} where bo_table = '{$row['bo_table']}' and wr_id = '{$row2['wr_id']}' and bf_type between '1' and '3' order by bf_no limit 0, 1 ";
      $file = sql_fetch($sql);
      if ($file['bf_file']) {
        $list[$i]['image'] = G5_DATA_URL . '/file/' . $row['bo_table'] . '/' . $file['bf_file'];
      }

      // 썸네일 추가
      if ($options && is_string($options)) {
        $options_arr = explode(',', $options);
        $thumb_width = $options_arr[0];
        $thumb_height = $options_arr[1];
        $thumb = get_list_thumbnail($row['bo_table'], $row2['wr_id'], $thumb_width, $thumb_height, false, true);
        // 이미지 썸네일
        if ($thumb['src']) {
          $img_content = '<img src="' . $thumb['src'] . '" alt="' . $thumb['alt'] . '" width="' . $thumb_width . '" height="' . $thumb_height . '">';
          $list[$i]['img_thumbnail'] = '<a href="' . $list[$i]['href'] . '" class="lt_img">' . $img_content . '</a>';
        }
      }
    }

    if (G5_USE_CACHE) {
      $caches = array(
        'list' => $list,
      );

      g5_set_cache($cache_file_name, $caches, (int) $time_unit * (int) $cache_time);
    }
  } else {
    $list = $cache_list;
  }

  ob_start();
  include $new_skin_path . '/new.skin.php';
  $content = ob_get_contents();
  ob_end_clean();

  return $content;
}
