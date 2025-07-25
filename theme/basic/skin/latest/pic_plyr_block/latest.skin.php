<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);

add_stylesheet('<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">', 0);
add_javascript('<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>', 0);

// 썸네일 설정
$thumb_width = 210;
$thumb_height = 150;

// 추출개수
$list_count = (is_array($list) && $list) ? count($list) : 0;

// 플레이어 호버
$is_hover = true;

// 랜덤
$is_random = true;
if($is_random) shuffle($list);

// 제목줄
$is_subject = true;
$subject_line = 1;
$line_sheight = 20 * $subject_line + 2;

// 내용줄 출력여부
$is_content = false;
$content_line = 2;
$line_cheight = 18 * $content_line + 2;
$content_len = 100;

// 등록정보 출력여부
$is_info = false;

// 주어진 URL로부터 비디오 ID와 제공자를 추출하여 배열로 반환하는 함수
function get_video_info($url) {
    $video_id = '';
    $provider = '';

    // YouTube URL 패턴
    $youtube_pattern =
        '%^# Match any YouTube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # $1: Required 10-12 character ID
        %x';

    // Vimeo URL 패턴
    $vimeo_pattern =
        '%^# Match Vimeo URL
        (?:https?://)?      # Optional scheme. Either http or https
        (?:player\.)?       # Optional subdomain
        vimeo\.com/         # Domain
        (?:video/)?         # Optional video subdirectory
        (\d+)               # $1: Vimeo video ID
        %x';

    // YouTube URL 확인
    if (preg_match($youtube_pattern, $url, $matches)) {
        $video_id = $matches[1];
        $provider = 'youtube';
    }
    // Vimeo URL 확인
    elseif (preg_match($vimeo_pattern, $url, $matches)) {
        $video_id = $matches[1];
        $provider = 'vimeo';
    }

    // 배열로 반환
    return array(
        'video_id' => $video_id,
        'provider' => $provider
    );
}

?>
<div class="pic_plyr_lt">
    <h2 class="lat_title"><a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a></h2>
    <ul>
		<?php
		for ($i=0; $i<$list_count; $i++) {
		$thumb = get_list_thumbnail($bo_table, $list[$i]['wr_id'], $thumb_width, $thumb_height, false, true);

		if($thumb['src']) {
			$img = $thumb['src'];
		} else {
			$img = G5_IMG_URL.'/no_img.png';
			$thumb['alt'] = '이미지가 없습니다.';
		}
		$img_content = '<img src="'.$img.'" alt="'.$thumb['alt'].'">';
		$wr_href = get_pretty_url($bo_table, $list[$i]['wr_id']);

		$provider = get_video_info($list[$i]['wr_link1']);
		?>
        <li class="gallery_li<?php echo $is_hover?' is_hover':'';?>">
			<div class="img-wrap is-round-post-img">
				<div class="img-item">
				<?php
				if($provider['provider']){

					echo '<div class="vidiom_player" data-plyr-provider="'.$provider['provider'].'" data-plyr-embed-id="'.$provider['video_id'].'"></div>';

				}else{
					echo '<a href="'.$wr_href.'">'.run_replace('thumb_image_tag', $img_content, $thumb).'</a>';
				}
				?>

				<?php if($list[$i]['ca_name']){ ?>
				<div class="post_content_overlay entry_meta">
					<a href="<?php echo $wr_href; ?>"><span class="bg_white"><?php echo $list[$i]['ca_name'];?></span></a>
				</div>
				<?php } ?>

				</div>
			</div>

			<?php if($is_subject){ ?>
			<h2 class="post-title" style="height:<?php echo $line_sheight;?>px;">
            <?php
            if ($list[$i]['icon_secret']) echo "<i class=\"fa fa-lock\" aria-hidden=\"true\"></i><span class=\"sound_only\">비밀글</span> ";

            echo "<a href=\"".$wr_href."\"> ";
            if ($list[$i]['is_notice'])
                echo "<strong>".$list[$i]['subject']."</strong>";
            else
                echo $list[$i]['subject'];
            echo "</a>";

			if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
            if ($list[$i]['icon_hot']) echo "<span class=\"hot_icon\">H<span class=\"sound_only\">인기글</span></span>";

            // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
            // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

			// echo $list[$i]['icon_reply']." ";
			// if ($list[$i]['icon_file']) echo " <i class=\"fa fa-download\" aria-hidden=\"true\"></i>" ;
            // if ($list[$i]['icon_link']) echo " <i class=\"fa fa-link\" aria-hidden=\"true\"></i>" ;

            if ($list[$i]['comment_cnt'])  echo "
            <span class=\"lt_cmt\">".$list[$i]['wr_comment']."</span>";
            ?>
			</h2>
			<?php } ?>
			<?php if($is_content){ ?>
			<div class="post-text" style="height:<?php echo $line_cheight;?>px;">
				<a href="<?php echo $wr_href; ?>"><?php echo cut_str(strip_tags($list[$i]['wr_content']), $content_len)?></a>
			</div>
			<?php } ?>
			<?php if($is_info){ ?>
            <div class="lt_info">
				<span class="lt_nick"><?php echo $list[$i]['name'] ?></span>
            	<span class="lt_date"><?php echo $list[$i]['datetime2'] ?></span>
            </div>
			<?php } ?>
        </li>
		<?php }  ?>
		<?php if ($list_count == 0) { //게시물이 없을 때  ?>
		<li class="empty_li">게시물이 없습니다.</li>
		<?php }  ?>
    </ul>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="lt_more"><span class="sound_only"><?php echo $bo_subject ?></span>더보기</a>
</div>

<script>
// Initilize player js
if (typeof Plyr != "undefined") {
	const vidiom_player = Plyr.setup( '.vidiom_player', {
		controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'pip', 'airplay', 'fullscreen'], // 'settings'
		disableContextMenu: true,
		quality: true
	});

	//var vidiom_player = new Plyr('.vidiom_player', {captions: {active: true}});

	if( vidiom_player ){
		vidiom_player.forEach(( player, index ) =>
			player.on("play", () =>
				vidiom_player
				.filter((p, i) => i !== index)
				.forEach((p) => p.pause())
			)
		);
	}
}
</script>

