<div class="sideGnb" data-item="side_gnb">
	<nav class="nav">
		<h2 class="groupTitle"><i class='bx bx-grid-alt'></i> 카테고리</h2>
		<ul>
			<?php
			// 전역변수 선언
			global $is_admin;

			$menu_datas = get_menu_db(0, true);
			$i = 0;
			
			if (is_array($menu_datas)) {
				foreach ($menu_datas as $row) {
					if (empty($row) || !is_array($row)) continue;

					$add_class = (isset($row['sub']) && is_array($row['sub']) && count($row['sub']) > 0) ? 'subdepth' : '';
					$is_active = false;
			
					$k = 0;
					ob_start(); // 서브 메뉴 버퍼링 시작
			
					if (isset($row['sub']) && is_array($row['sub'])) {
						foreach ($row['sub'] as $row2) {
							if (empty($row2) || !is_array($row2)) continue;

							if ($k == 0) echo '<ul>'.PHP_EOL;

							$sub_active = false;
			
							if (isset($row2['me_link']) && !empty($row2['me_link'])) {
								$parsed2 = parse_url($row2['me_link']);
								if (is_array($parsed2) && isset($parsed2['query'])) {
									parse_str($parsed2['query'], $q2);
									if (isset($q2['bo_table']) && isset($_GET['bo_table']) && $q2['bo_table'] === $_GET['bo_table']) {
										$sub_active = true;
									}
								}
								
								if (!$sub_active && is_array($parsed2) && isset($parsed2['path'])) {
									$path_parts2 = explode('/', trim($parsed2['path'], '/'));
									if (count($path_parts2) >= 1 && isset($_GET['bo_table']) && $path_parts2[0] === $_GET['bo_table']) {
										$sub_active = true;
									}
								}
							}
			
							if ($sub_active) $is_active = true;
			
							// 서브메뉴 링크와 타겟 안전하게 가져오기
							$sub_link = isset($row2['me_link']) ? $row2['me_link'] : '#';
							$sub_target = isset($row2['me_target']) ? $row2['me_target'] : 'self';
							$sub_name = isset($row2['me_name']) ? $row2['me_name'] : '';
							?>
							<li>
								<a href="<?php echo htmlspecialchars($sub_link); ?>"
								target="_<?php echo htmlspecialchars($sub_target); ?>"
								class="<?php echo $sub_active ? 'active' : ''; ?>">
									<?php echo htmlspecialchars($sub_name); ?>
								</a>
							</li>
							<?php
							$k++;
						}
					}
			
					if ($k > 0) echo '</ul>'.PHP_EOL;
					$sub_output = ob_get_clean(); // 버퍼 저장
					
					// 메인메뉴 링크와 타겟 안전하게 가져오기
					$main_link = isset($row['me_link']) ? $row['me_link'] : '#';
					$main_target = isset($row['me_target']) ? $row['me_target'] : 'self';
					$main_name = isset($row['me_name']) ? $row['me_name'] : '';
				?>
					<li class="<?php echo $add_class; ?>">
						<a href="<?php echo htmlspecialchars($main_link); ?>"
						target="_<?php echo htmlspecialchars($main_target); ?>"
						class="<?php echo $is_active ? 'on' : ''; ?>">
							<?php echo htmlspecialchars($main_name); ?>
						</a>
						<?php echo $sub_output; ?>
					</li>
				<?php
					$i++;
				}
			}
			
			if ($i == 0) {
			?>
				<li class="gnb_empty">
					메뉴 준비 중입니다.
					<?php if (isset($is_admin) && $is_admin): ?>
						<a href="<?php echo defined('G5_ADMIN_URL') ? G5_ADMIN_URL : ''; ?>/menu_list.php">관리자모드 &gt; 환경설정 &gt; 메뉴설정</a>에서 설정하실 수 있습니다.
					<?php endif; ?>
				</li>
			<?php
			}
			?>

		</ul>
	</nav>
</div>