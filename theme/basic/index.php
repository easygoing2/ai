<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
	include_once(G5_THEME_MOBILE_PATH . '/index.php');
	return;
}

if (G5_COMMUNITY_USE === false) {
	include_once(G5_THEME_SHOP_PATH . '/index.php');
	return;
}

include_once(G5_THEME_PATH . '/head.php');
?>

<h2 class="sound_only">최신글</h2>
<!-- 새글 게시판 링크 -->
<div style="text-align: center; margin: 20px 0;">
	<a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=newposts" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">새글 게시판 보기</a>
</div>
<div class="latest_top_wr">
	<?php
	// 이 함수가 바로 최신글을 추출하는 역할을 합니다.
	// 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
	// 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
	echo latest('theme/pic_list', 'free', 4, 23);		// 최소설치시 자동생성되는 자유게시판
	echo latest('theme/pic_list', 'qa', 4, 23);			// 최소설치시 자동생성되는 질문답변게시판
	echo latest('theme/pic_list', 'notice', 4, 23);		// 최소설치시 자동생성되는 공지사항게시판
	?>
</div>
<div class="latest_wr">
	<!-- 사진 최신글2 { -->
	<?php
	// 이 함수가 바로 최신글을 추출하는 역할을 합니다.
	// 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
	// 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
	echo latest('theme/pic_block', 'gallery', 4, 23);		// 최소설치시 자동생성되는 갤러리게시판
	?>
	<!-- } 사진 최신글2 끝 -->
</div>

<div class="latest_wr">
	<!-- 최신글 시작 { -->
	<?php
	//  최신글
	$sql = " select bo_table
                from `{$g5['board_table']}` a left join `{$g5['group_table']}` b on (a.gr_id=b.gr_id)
                where a.bo_device <> 'mobile' ";
	if (!$is_admin)
		$sql .= " and a.bo_use_cert = '' ";
	$sql .= " and a.bo_table not in ('notice', 'gallery') ";     //공지사항과 갤러리 게시판은 제외
	$sql .= " order by b.gr_order, a.bo_order ";
	$result = sql_query($sql);
	for ($i = 0; $row = sql_fetch_array($result); $i++) {
		$lt_style = '';
		if ($i % 3 !== 0) $lt_style = "margin-left:2%";
	?>
		<div style="float:left;<?php echo $lt_style ?>" class="lt_wr">
			<?php
			// 이 함수가 바로 최신글을 추출하는 역할을 합니다.
			// 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
			// 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
			echo latest('theme/basic', $row['bo_table'], 6, 24);
			?>
		</div>
	<?php
	}
	?>
	<!-- } 최신글 끝 -->
</div>

<!-- 새글 목록 시작 { -->
<div class="latest_wr" style="clear: both; margin-top: 30px;">
	<h3 style="margin-bottom: 15px; color: #333; font-size: 18px;">새글 목록</h3>
	<?php
	// 새글 목록 가져오기 (new.php 로직)
	$sql_common = " from {$g5['board_new_table']} a, {$g5['board_table']} b, {$g5['group_table']} c where a.bo_table = b.bo_table and b.gr_id = c.gr_id and b.bo_use_search = 1 ";
	$sql_order = " order by a.bn_id desc ";

	// 새글 10개만 가져오기
	$sql = " select a.*, b.bo_subject, b.bo_mobile_subject, c.gr_subject, c.gr_id {$sql_common} {$sql_order} limit 0, 10 ";
	$result = sql_query($sql);

	$new_list = array();
	for ($i = 0; $row = sql_fetch_array($result); $i++) {
		$tmp_write_table = $g5['write_prefix'] . $row['bo_table'];

		if ($row['wr_id'] == $row['wr_parent']) {
			// 원글
			$comment = "";
			$comment_link = "";
			$row2 = sql_fetch(" select * from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");
			$new_list[$i] = $row2;

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
			$new_list[$i] = $row2;
			$new_list[$i]['wr_id'] = $row['wr_id'];
			$new_list[$i]['mb_id'] = $row3['mb_id'];
			$new_list[$i]['wr_name'] = $row3['wr_name'];
			$new_list[$i]['wr_email'] = $row3['wr_email'];
			$new_list[$i]['wr_homepage'] = $row3['wr_homepage'];

			$name = get_sideview($row3['mb_id'], get_text(cut_str($row3['wr_name'], $config['cf_cut_name'])), $row3['wr_email'], $row3['wr_homepage']);
			// 당일인 경우 시간으로 표시함
			$datetime = substr($row3['wr_datetime'], 0, 10);
			$datetime2 = $row3['wr_datetime'];
			if ($datetime == G5_TIME_YMD) {
				$datetime2 = substr($datetime2, 11, 5);
			} else {
				$datetime2 = substr($datetime2, 5, 5);
			}
		}

		$new_list[$i]['gr_id'] = $row['gr_id'];
		$new_list[$i]['bo_table'] = $row['bo_table'];
		$new_list[$i]['name'] = $name;
		$new_list[$i]['comment'] = $comment;
		$new_list[$i]['href'] = get_pretty_url($row['bo_table'], $row2['wr_id'], $comment_link);
		$new_list[$i]['datetime'] = $datetime;
		$new_list[$i]['datetime2'] = $datetime2;

		$new_list[$i]['gr_subject'] = $row['gr_subject'];
		$new_list[$i]['bo_subject'] = ((G5_IS_MOBILE && $row['bo_mobile_subject']) ? $row['bo_mobile_subject'] : $row['bo_subject']);
		$new_list[$i]['wr_subject'] = $row2['wr_subject'];

		// 이미지 정보 가져오기
		$new_list[$i]['image'] = '';
		$file_table = $g5['board_file_table'];
		$sql = " select bf_file, bf_content from {$file_table} where bo_table = '{$row['bo_table']}' and wr_id = '{$row2['wr_id']}' and bf_type between '1' and '3' order by bf_no limit 0, 1 ";
		$file = sql_fetch($sql);
		if ($file['bf_file']) {
			$new_list[$i]['image'] = G5_DATA_URL . '/file/' . $row['bo_table'] . '/' . $file['bf_file'];
		}
	}
	?>

	<div class="tbl_head01 tbl_wrap">
		<table style="width: 100%; border-collapse: collapse;">
			<caption>새글 목록</caption>
			<thead>
				<tr>
					<th scope="col" style="padding: 8px; border: 1px solid #ddd; background-color: #f8f9fa;">번호</th>
					<th scope="col" style="padding: 8px; border: 1px solid #ddd; background-color: #f8f9fa;">이미지</th>
					<th scope="col" style="padding: 8px; border: 1px solid #ddd; background-color: #f8f9fa;">게시판</th>
					<th scope="col" style="padding: 8px; border: 1px solid #ddd; background-color: #f8f9fa;">제목</th>
					<th scope="col" style="padding: 8px; border: 1px solid #ddd; background-color: #f8f9fa;">글쓴이</th>
					<th scope="col" style="padding: 8px; border: 1px solid #ddd; background-color: #f8f9fa;">날짜</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for ($i = 0; $i < count($new_list); $i++) {
					$row = $new_list[$i];
				?>
					<tr>
						<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo ($i + 1); ?></td>
						<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
							<?php if ($row['image']) { ?>
								<img src="<?php echo $row['image']; ?>" alt="첨부이미지" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
							<?php } else { ?>
								<span style="color: #999; font-size: 12px;">이미지 없음</span>
							<?php } ?>
						</td>
						<td style="padding: 8px; border: 1px solid #ddd;">
							<a href="<?php echo G5_BBS_URL . '/board.php?bo_table=' . $row['bo_table']; ?>" style="color: #007bff; text-decoration: none;"><?php echo $row['bo_subject']; ?></a>
						</td>
						<td style="padding: 8px; border: 1px solid #ddd;">
							<a href="<?php echo $row['href']; ?>" style="color: #333; text-decoration: none;"><?php echo $row['comment'] . $row['wr_subject']; ?></a>
						</td>
						<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $row['name']; ?></td>
						<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><?php echo $row['datetime2']; ?></td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>

	<div style="text-align: center; margin-top: 15px;">
		<a href="<?php echo G5_BBS_URL; ?>/new.php" style="display: inline-block; padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">더보기</a>
	</div>
</div>
<!-- } 새글 목록 끝 -->

<div class="latest_wr">
	<!-- 최신글 시작 { -->
	<?php
	//  최신글
	$sql = " select bo_table
                from `{$g5['board_table']}` a left join `{$g5['group_table']}` b on (a.gr_id=b.gr_id)
                where a.bo_device <> 'mobile' ";
	if (!$is_admin)
		$sql .= " and a.bo_use_cert = '' ";
	$sql .= " and a.bo_table not in ('notice', 'gallery') ";     //공지사항과 갤러리 게시판은 제외
	$sql .= " order by b.gr_order, a.bo_order ";
	$result = sql_query($sql);
	for ($i = 0; $row = sql_fetch_array($result); $i++) {
		$lt_style = '';
		if ($i % 3 !== 0) $lt_style = "margin-left:2%";
	?>
		<div style="float:left;<?php echo $lt_style ?>" class="lt_wr">
			<?php
			// 이 함수가 바로 최신글을 추출하는 역할을 합니다.
			// 사용방법 : latest(스킨, 게시판아이디, 출력라인, 글자수);
			// 테마의 스킨을 사용하려면 theme/basic 과 같이 지정
			echo latest('theme/basic', $row['bo_table'], 6, 24);
			?>
		</div>
	<?php
	}
	?>
	<!-- } 최신글 끝 -->
</div>
PC에서 작업중.
<?php
include_once(G5_THEME_PATH . '/tail.php');
