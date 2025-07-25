<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $new_skin_url . '/style.css">', 0);
$list_count = (is_array($list) && $list) ? count($list) : 0;
?>

<div class="new_list">
  <h2 class="new_title">새글 목록</h2>

  <?php if ($list_count > 0) { ?>
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
          for ($i = 0; $i < $list_count; $i++) {
            $row = $list[$i];
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
  <?php } else { ?>
    <div style="text-align: center; padding: 50px 0; color: #666;">
      새글이 없습니다.
    </div>
  <?php } ?>
</div>