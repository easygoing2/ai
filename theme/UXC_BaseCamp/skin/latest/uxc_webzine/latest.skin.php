<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// Toast UI Editor 플러그인 확인 및 로드
if(file_exists(G5_PLUGIN_PATH.'/editor/uxc_toastuieditor/extend.toastuieditor.php')) {
    include_once(G5_PLUGIN_PATH.'/editor/uxc_toastuieditor/extend.toastuieditor.php');
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
$thumb_width = 300;
$thumb_height = 160;
$list_count = (is_array($list) && $list) ? count($list) : 0;

// YouTube ID 추출 함수
if (!function_exists('extractYouTubeID')) {
    function extractYouTubeID($url) {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([^\&\?\/]+)/', $url, $matches);
        return $matches[1] ?? '';
    }
}
?>

<div class="uxc_webzine" data-latest="uxc_webzine">
    <ul>
    <?php for ($i=0; $i<$list_count; $i++) {
        $img = '';
        $thumb_created = false;
        
        // 멀티 게시판 지원: 각 게시글의 게시판 테이블 사용
        $item_bo_table = isset($list[$i]['bo_table']) ? $list[$i]['bo_table'] : $bo_table;
        
        // 1단계: get_list_thumbnail 시도 (이미 썸네일 처리 포함)
        $thumb = get_list_thumbnail($item_bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, true, true);
        
        if($thumb['src']) {
            $img = $thumb['src'];
            $thumb_created = true;
        } 
        
        // 2단계: YouTube 썸네일
        if (!$img && $list[$i]['wr_10']) {
            $youtube_id = extractYouTubeID($list[$i]['wr_10']);
            if ($youtube_id) {
                $img = 'https://img.youtube.com/vi/'.$youtube_id.'/mqdefault.jpg';
                $thumb['alt'] = 'YouTube 동영상';
            }
        }
        
        // 3단계: 에디터 이미지에서 직접 추출 및 썸네일 생성
        if (!$img) {
            // 썸네일 저장 경로 설정
            $thumb_path = G5_DATA_PATH.'/file/'.$item_bo_table;
            if (!is_dir($thumb_path)) {
                @mkdir($thumb_path, G5_DIR_PERMISSION, true);
            }
            
            // 썸네일 파일명 (게시글 ID 기반)
            $thumb_filename = 'latest_'.$list[$i]['wr_id'].'_'.$thumb_width.'x'.$thumb_height.'.jpg';
            $thumb_filepath = $thumb_path.'/'.$thumb_filename;
            
            // 이미 생성된 썸네일이 있는지 확인
            if (file_exists($thumb_filepath)) {
                $img = G5_DATA_URL.'/file/'.$item_bo_table.'/'.$thumb_filename;
                $thumb_created = true;
            } else {
                // 에디터 콘텐츠에서 이미지 추출
                $content = html_entity_decode($list[$i]['wr_content']);
                $src = '';
                
                // Toast UI Editor 함수 사용
                if (function_exists('toastuieditor_get_first_image')) {
                    $src = toastuieditor_get_first_image($content);
                }
                
                // 없으면 정규식으로 추출
                if (!$src && preg_match('/<img[^>]+src=["\']([^"\'
]+)["\']/', $content, $matches)) {
                    $src = $matches[1];
                }
                
                if ($src) {
                    $src = htmlspecialchars_decode($src);
                    
                    // 내부 이미지 경로 처리
                    if (strpos($src, '/data/') === 0 || strpos($src, G5_DATA_URL) !== false) {
                        if (strpos($src, '/data/') === 0) {
                            $local_path = G5_PATH . $src;
                        } else {
                            $local_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $src);
                        }
                        
                        // 원본 파일이 존재하면 썸네일 생성
                        if (file_exists($local_path)) {
                            $info = pathinfo($local_path);
                            $created_thumb = thumbnail($info['basename'], $info['dirname'], $thumb_path, $thumb_width, $thumb_height, false, true, 'center', false, $thumb_filename);
                            
                            if ($created_thumb) {
                                $img = G5_DATA_URL.'/file/'.$item_bo_table.'/'.$created_thumb;
                                $thumb_created = true;
                            }
                        }
                    }
                    
                    // 썸네일 생성 실패시 원본 사용
                    if (!$img) {
                        $img = $src;
                    }
                    
                    $thumb['alt'] = '에디터 이미지';
                }
            }
        }
        
        // 이미지가 없으면 기본 이미지
        if (!$img) {
            $img = G5_THEME_IMG_URL.'/no_img.png';
            $thumb['alt'] = '이미지가 없습니다.';
        }
        $img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'" >';
        // 멀티 게시판 지원: 위에서 정의한 item_bo_table 사용
        $wr_href = get_pretty_url($item_bo_table, $list[$i]['wr_id']);
    ?>
        <li class="uxc_webzine_li">
            <a href="<?php echo $wr_href; ?>" class="webzine_thumb">
                <?php echo run_replace('thumb_image_tag', $img_content, $thumb); ?>
            </a>
            
            <div class="webzine_info">
                <a href="<?php echo $wr_href; ?>">
                    <?php 
                    if ($list[$i]['icon_secret']) echo "<i class=\"bx bx-lock-alt\" aria-hidden=\"true\"></i><span class=\"sound_only\">비밀글</span> ";
                    
                    if ($list[$i]['is_notice'])
                        echo "<strong>".$list[$i]['subject']."</strong>";
                    else
                        echo $list[$i]['subject'];
                        
                    if ($list[$i]['icon_hot']) echo "<span class=\"hot_icon\">H<span class=\"sound_only\">인기글</span></span>";
                    if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
                    
                    if ($list[$i]['comment_cnt']) echo "<span class=\"lt_cmt\">댓글 ".$list[$i]['comment_cnt']."</span>";
                    ?>
                </a>
                
                <?php
                // 본문 내용 추출
                $wr_content = $list[$i]['wr_content'];

                // Toast UI Editor로 작성된 마크다운 콘텐츠인지 확인
                if (strpos($wr_content, '<!--TOASTUI_EDITOR_MARKDOWN-->') === 0) {
                    // 마크다운 구분자 제거
                    $wr_content = str_replace('<!--TOASTUI_EDITOR_MARKDOWN-->', '', $wr_content);
                    $wr_content = ltrim($wr_content, "\n");

                    // 마크다운 문법 제거
                    $wr_content = preg_replace('/^#{1,6}\s+/m', '', $wr_content); // 제목 (#, ##, ### 등)
                    $wr_content = preg_replace('/\*\*(.*?)\*\*/', '$1', $wr_content); // 굵게 (**text**)
                    $wr_content = preg_replace('/\*(.*?)\*/', '$1', $wr_content); // 기울임 (*text*)
                    $wr_content = preg_replace('/~~(.*?)~~/', '$1', $wr_content); // 취소선 (~~text~~)
                    $wr_content = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $wr_content); // 링크 [text](url)
                    $wr_content = preg_replace('/!\[(.*?)\]\(.*?\)/', '', $wr_content); // 이미지 제거
                    $wr_content = preg_replace('/`(.*?)`/', '$1', $wr_content); // 인라인 코드
                    $wr_content = preg_replace('/^>\s+/m', '', $wr_content); // 인용구
                    $wr_content = preg_replace('/^[\*\-]\s+/m', '', $wr_content); // 목록
                    $wr_content = preg_replace('/^\d+\.\s+/m', '', $wr_content); // 숫자 목록
                    $wr_content = preg_replace('/```.*?```/s', '', $wr_content); // 코드 블록
                    $wr_content = preg_replace('/---+/', '', $wr_content); // 구분선
                } else if (strpos($wr_content, '<!--TOASTUI_EDITOR_HTML-->') === 0) {
                    // HTML 모드로 작성된 경우 구분자 제거
                    $wr_content = str_replace('<!--TOASTUI_EDITOR_HTML-->', '', $wr_content);
                    $wr_content = ltrim($wr_content, "\n");
                }

                // HTML 태그 제거
                $wr_content = preg_replace("/<(.*?)\>/","",$wr_content);
                // 공백 문자 제거
                $wr_content = preg_replace("/&nbsp;/","",$wr_content);
                // 연속된 줄바꿈을 하나로
                $wr_content = preg_replace("/\n+/", " ", $wr_content);
                // 연속된 공백을 하나로
                $wr_content = preg_replace("/\s+/", " ", $wr_content);
                // 앞뒤 공백 제거
                $wr_content = trim($wr_content);
                // 150자로 제한
                $wr_content = cut_str($wr_content, 150);

                if($wr_content) {
                ?>
                <div class="webzine_content">
                    <?php echo $wr_content; ?>
                </div>
                <?php } ?>
                
                <div class="lt_info">
                    <?php if (isset($list[$i]['bo_subject']) && $list[$i]['bo_subject']) { ?>
                    <span class="lt_board"><i class='bx bx-folder'></i> <?php echo $list[$i]['bo_subject'] ?></span>
                    <?php } ?>
                    <span class="lt_nick"><i class='bx bx-user'></i> <?php echo $list[$i]['name'] ?></span>
                    <span class="lt_date"><i class="bx bx-time"></i> <?php echo $list[$i]['datetime2'] ?></span>
                </div>
            </div>
        </li>
    <?php } ?>
    
    <?php if ($list_count == 0) { // 게시물이 없을 때 ?>
    <li class="empty_li">
        <i class="bx bx-info-circle"></i>
        게시물이 없습니다.
    </li>
    <?php } ?>
    </ul>
</div>