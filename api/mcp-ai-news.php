<?php

/**
 * 23yellow MCP - AI News API
 *
 * 지원 action
 *
 * list
 *   AI 뉴스 게시물 목록 조회
 *
 * get
 *   특정 게시물 상세 조회
 *
 * create
 *   게시물 등록
 *
 * update
 *   게시물 수정
 *
 * 인증:
 * Authorization: Bearer <AI_NEWS_API_TOKEN>
 */


/* =========================================================
 * 기본 설정
 * ========================================================= */

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');


/*
 * API 파일 위치가:
 *
 * /웹루트/api/mcp-ai-news.php
 *
 * 라고 가정합니다.
 *
 * 따라서 common.php는 한 단계 위에 있습니다.
 */

$common_file = dirname(__DIR__) . '/common.php';

if (!file_exists($common_file)) {

	http_response_code(500);

	echo json_encode(
		[
			'success' => false,
			'error' => '그누보드 common.php를 찾을 수 없습니다.',
			'expected_path' => $common_file
		],
		JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
	);

	exit;
}

require_once $common_file;


/* =========================================================
 * 게시판 설정
 * ========================================================= */

/*
 * 현재 ai.23yellow.com의 실제 게시판 URL 기준
 *
 * AI 소식        /ai_agent
 * AI Agent       /ai_agent_01
 * AI 엔지니어링   /ai_Engineering_01
 * AI MCP         /ai_mcp_01
 */

$AI_NEWS_BOARDS = [

	'AI소식' => 'ai_agent',

	'AI Agent' => 'ai_agent_01',

	'AI 엔지니어링' => 'ai_Engineering_01',

	'AI MCP' => 'ai_mcp_01',

];


/* =========================================================
 * 공통 함수
 * ========================================================= */

function api_response(
	array $data,
	int $status = 200
): void {

	http_response_code($status);

	echo json_encode(
		$data,
		JSON_UNESCAPED_UNICODE |
			JSON_UNESCAPED_SLASHES |
			JSON_PRETTY_PRINT
	);

	exit;
}


function api_success(
	array $data = [],
	int $status = 200
): void {

	api_response(
		array_merge(
			[
				'success' => true
			],
			$data
		),
		$status
	);
}


function api_error(
	string $message,
	int $status = 400,
	array $extra = []
): void {

	api_response(
		array_merge(
			[
				'success' => false,
				'error' => $message
			],
			$extra
		),
		$status
	);
}


/*
 * SQL 실행
 */

function api_sql(string $sql)
{

	$result = sql_query(
		$sql,
		false
	);

	if ($result === false) {
		throw new Exception(
			'Database query failed.'
		);
	}

	return $result;
}


/*
 * 문자열 SQL escape
 */

function api_escape(string $value): string
{

	return sql_escape_string($value);
}


/*
 * 게시판 카테고리 이름 정규화
 */

function api_normalize_category(
	string $category
): string {

	$category = trim($category);

	$aliases = [

		'AI 소식' => 'AI소식',
		'ai소식' => 'AI소식',
		'ai 소식' => 'AI소식',

		'AI 에이전트' => 'AI Agent',
		'ai agent' => 'AI Agent',

		'AI엔지니어링' => 'AI 엔지니어링',
		'ai 엔지니어링' => 'AI 엔지니어링',

		'AI MCP' => 'AI MCP',
		'ai mcp' => 'AI MCP',

	];

	if (isset($aliases[$category])) {
		return $aliases[$category];
	}

	return $category;
}


/*
 * category → bo_table
 */

function api_get_board(
	string $category
): array {

	global $AI_NEWS_BOARDS;
	global $g5;

	$category = api_normalize_category(
		$category
	);

	if (!isset(
		$AI_NEWS_BOARDS[$category]
	)) {

		api_error(
			'지원하지 않는 카테고리입니다.',
			400,
			[
				'category' => $category,
				'allowed_categories' =>
				array_keys(
					$AI_NEWS_BOARDS
				)
			]
		);
	}

	$bo_table =
		$AI_NEWS_BOARDS[$category];

	/*
     * 혹시라도 table name을 잘못 설정했을 경우
     * SQL table injection 방지
     */

	if (!preg_match(
		'/^[A-Za-z0-9_]+$/',
		$bo_table
	)) {

		api_error(
			'잘못된 bo_table 값입니다.',
			500
		);
	}


	/*
     * 실제 그누보드 게시판 존재 확인
     */

	$escaped_bo_table =
		api_escape($bo_table);

	$board = sql_fetch(
		"
        SELECT *
        FROM {$g5['board_table']}
        WHERE bo_table = '{$escaped_bo_table}'
        "
	);

	if (
		!$board ||
		empty($board['bo_table'])
	) {

		api_error(
			'그누보드 게시판을 찾을 수 없습니다.',
			500,
			[
				'category' => $category,
				'bo_table' => $bo_table
			]
		);
	}

	return [
		'category' => $category,
		'bo_table' => $bo_table,
		'board' => $board
	];
}


/*
 * 게시물 URL 생성
 */

function api_post_url(
	string $bo_table,
	int $wr_id
): string {

	/*
     * SEO URL 기능이 있으면
     * 현재 사이트 형식의 URL 반환
     */

	if (function_exists(
		'get_pretty_url'
	)) {

		return get_pretty_url(
			$bo_table,
			$wr_id
		);
	}

	/*
     * fallback
     */

	return
		G5_BBS_URL .
		'/board.php?bo_table=' .
		urlencode($bo_table) .
		'&wr_id=' .
		$wr_id;
}


/*
 * 게시물 내용 일부만 반환
 */

function api_excerpt(
	string $content,
	int $length = 300
): string {

	$text = strip_tags(
		$content
	);

	$text = html_entity_decode(
		$text,
		ENT_QUOTES |
			ENT_HTML5,
		'UTF-8'
	);

	$text = preg_replace(
		'/\s+/u',
		' ',
		$text
	);

	$text = trim($text);

	if (mb_strlen(
		$text,
		'UTF-8'
	) > $length) {

		$text =
			mb_substr(
				$text,
				0,
				$length,
				'UTF-8'
			) .
			'...';
	}

	return $text;
}


/*
 * Authorization 헤더 읽기
 */

function api_get_authorization_header(): string
{

	/*
     * 일반적인 경우
     */

	if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {

		return trim(
			$_SERVER['HTTP_AUTHORIZATION']
		);
	}

	/*
     * 일부 Nginx / PHP-FPM 환경
     */

	if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {

		return trim(
			$_SERVER['REDIRECT_HTTP_AUTHORIZATION']
		);
	}

	/*
     * getallheaders fallback
     */

	if (function_exists(
		'getallheaders'
	)) {

		$headers =
			getallheaders();

		foreach (
			$headers as
			$name => $value
		) {

			if (
				strtolower($name)
				===
				'authorization'
			) {

				return trim(
					$value
				);
			}
		}
	}

	return '';
}


/* =========================================================
 * API TOKEN 읽기
 * ========================================================= */

/*
 * 권장 방식:
 *
 * /etc/23yellow/mcp-ai-news.env
 *
 * AI_NEWS_API_TOKEN=xxxx
 *
 * 한 곳에서 token을 관리하고
 * systemd와 PHP가 같은 파일을 사용합니다.
 */

$env_file =
	'/etc/23yellow/mcp-ai-news.env';

$API_TOKEN = 'e7d290b43863feaa893449590d369137687a76cd973f69a248e1ea8f65b12a22';


/*
 * 먼저 환경변수 확인
 */

$token_from_env =
	getenv(
		'AI_NEWS_API_TOKEN'
	);

if (
	$token_from_env !== false &&
	$token_from_env !== ''
) {

	$API_TOKEN =
		trim(
			$token_from_env
		);
}


/*
 * 환경변수가 없으면
 * env 파일에서 읽기
 */

if (
	!$API_TOKEN &&
	file_exists(
		$env_file
	)
) {

	$env_values =
		parse_ini_file(
			$env_file,
			false,
			INI_SCANNER_RAW
		);

	if (
		$env_values &&
		isset(
			$env_values['AI_NEWS_API_TOKEN']
		)
	) {

		$API_TOKEN =
			trim(
				$env_values['AI_NEWS_API_TOKEN']
			);
	}
}


if (!$API_TOKEN) {

	api_error(
		'AI_NEWS_API_TOKEN이 설정되어 있지 않습니다.',
		500
	);
}


/* =========================================================
 * 인증
 * ========================================================= */

$authorization =
	api_get_authorization_header();

$expected_auth =
	'Bearer ' .
	$API_TOKEN;


if (
	!$authorization ||
	!hash_equals(
		$expected_auth,
		$authorization
	)
) {

	api_error(
		'Unauthorized',
		401
	);
}


/* =========================================================
 * HTTP Method 확인
 * ========================================================= */

if (
	($_SERVER['REQUEST_METHOD'] ?? '')
	!==
	'POST'
) {

	api_error(
		'POST 요청만 허용됩니다.',
		405
	);
}


/* =========================================================
 * JSON 입력
 * ========================================================= */

$raw_input =
	file_get_contents(
		'php://input'
	);


/*
 * 너무 큰 요청 차단
 *
 * 약 2MB
 */

if (
	strlen($raw_input)
	>
	2 * 1024 * 1024
) {

	api_error(
		'요청 데이터가 너무 큽니다.',
		413
	);
}


$input =
	json_decode(
		$raw_input,
		true
	);


if (
	!is_array(
		$input
	)
) {

	api_error(
		'JSON 형식이 올바르지 않습니다.',
		400
	);
}


$action =
	trim(
		$input['action']
			??
			''
	);


if (!$action) {

	api_error(
		'action 값이 필요합니다.',
		400
	);
}


/* =========================================================
 * LIST
 * 기존 게시물 목록 조회
 * ========================================================= */

if ($action === 'list') {

	$category =
		$input['category']
		??
		'AI소식';

	$limit =
		intval(
			$input['limit']
				??
				10
		);

	$limit =
		max(
			1,
			min(
				$limit,
				30
			)
		);


	$board_info =
		api_get_board(
			$category
		);


	$category =
		$board_info['category'];

	$bo_table =
		$board_info['bo_table'];


	$write_table =
		$g5['write_prefix'] .
		$bo_table;


	$sql =
		"
        SELECT
            wr_id,
            wr_subject,
            wr_content,
            wr_link1,
            wr_10,
            mb_id,
            wr_name,
            wr_datetime,
            wr_hit

        FROM {$write_table}

        WHERE wr_is_comment = 0

        ORDER BY
            wr_num ASC

        LIMIT {$limit}
        ";


	$result =
		api_sql(
			$sql
		);


	$posts = [];


	while (
		$row =
		sql_fetch_array(
			$result
		)
	) {

		$wr_id =
			intval(
				$row['wr_id']
			);


		$posts[] = [

			'post_id' =>
			$wr_id,

			'title' =>
			$row['wr_subject'],

			'excerpt' =>
			api_excerpt(
				$row['wr_content']
			),

			'source_url' =>
			$row['wr_link1'],

			'youtube_url' =>
			$row['wr_10'],

			'author' =>
			$row['wr_name'],

			'created_at' =>
			$row['wr_datetime'],

			'views' =>
			intval(
				$row['wr_hit']
			),

			'url' =>
			api_post_url(
				$bo_table,
				$wr_id
			)
		];
	}


	api_success(
		[
			'category' =>
			$category,

			'bo_table' =>
			$bo_table,

			'count' =>
			count(
				$posts
			),

			'posts' =>
			$posts
		]
	);
}


/* =========================================================
 * GET
 * 게시물 상세 조회
 * ========================================================= */

if ($action === 'get') {

	$post_id =
		intval(
			$input['post_id']
				??
				0
		);


	/*
     * 현재 server.py의 get_ai_news_post()가
     * category를 보내지 않으므로
     * 기본 AI소식으로 처리
     */

	$category =
		$input['category']
		??
		'AI소식';


	if ($post_id <= 0) {

		api_error(
			'올바른 post_id가 필요합니다.',
			400
		);
	}


	$board_info =
		api_get_board(
			$category
		);


	$category =
		$board_info['category'];

	$bo_table =
		$board_info['bo_table'];


	$write_table =
		$g5['write_prefix'] .
		$bo_table;


	$row =
		sql_fetch(
			"
            SELECT
                wr_id,
                wr_subject,
                wr_content,
                wr_link1,
                wr_10,
                mb_id,
                wr_name,
                wr_datetime,
                wr_last,
                wr_hit

            FROM {$write_table}

            WHERE
                wr_id = {$post_id}
                AND wr_is_comment = 0
            "
		);


	if (
		!$row ||
		empty($row['wr_id'])
	) {

		api_error(
			'게시물을 찾을 수 없습니다.',
			404,
			[
				'category' =>
				$category,

				'post_id' =>
				$post_id
			]
		);
	}


	api_success(
		[
			'category' =>
			$category,

			'bo_table' =>
			$bo_table,

			'post' => [

				'post_id' =>
				intval(
					$row['wr_id']
				),

				'title' =>
				$row['wr_subject'],

				'content' =>
				$row['wr_content'],

				'source_url' =>
				$row['wr_link1'],

				'youtube_url' =>
				$row['wr_10'],

				'author' =>
				$row['wr_name'],

				'created_at' =>
				$row['wr_datetime'],

				'updated_at' =>
				$row['wr_last'],

				'views' =>
				intval(
					$row['wr_hit']
				),

				'url' =>
				api_post_url(
					$bo_table,
					$post_id
				)
			]
		]
	);
}


/* =========================================================
 * CREATE
 * 새 게시물 작성
 * ========================================================= */

if ($action === 'create') {

	$category =
		trim(
			$input['category']
				??
				'AI소식'
		);


	$title =
		trim(
			$input['title']
				??
				''
		);


	$content =
		trim(
			$input['content']
				??
				''
		);


	$source_url =
		trim(
			$input['source_url']
				??
				''
		);


	$youtube_url =
		trim(
			$input['youtube_url']
				??
				''
		);


	if (!$title) {

		api_error(
			'게시물 제목이 없습니다.',
			400
		);
	}


	if (!$content) {

		api_error(
			'게시물 내용이 없습니다.',
			400
		);
	}


	/*
     * 제목에 HTML 제거
     */

	$title =
		strip_tags(
			$title
		);


	if (
		mb_strlen(
			$title,
			'UTF-8'
		)
		>
		255
	) {

		$title =
			mb_substr(
				$title,
				0,
				255,
				'UTF-8'
			);
	}


	/*
     * HTML 본문 정리
     *
     * 그누보드 purifier가 있으면 사용
     */

	if (
		function_exists(
			'html_purifier'
		)
	) {

		$content =
			html_purifier(
				$content
			);
	}


	/*
     * Toast UI Markdown 식별자 추가
     *
     * 게시판 스킨이 이 주석을 확인해
     * wr_content를 Markdown으로 렌더링합니다.
     * html_purifier() 이후에 추가해야
     * HTML comment가 제거되지 않습니다.
     */

	$markdown_marker =
		'<!--TOASTUI_EDITOR_MARKDOWN-->';

	if (
		strpos(
			$content,
			$markdown_marker
		)
		!==
		0
	) {

		$content =
			$markdown_marker .
			"\r\n" .
			$content;
	}


	/*
     * 게시판 확인
     */

	$board_info =
		api_get_board(
			$category
		);


	$category =
		$board_info['category'];

	$bo_table =
		$board_info['bo_table'];


	$write_table =
		$g5['write_prefix'] .
		$bo_table;


	/*
     * 링크 처리
     *
     * wr_link1 = source_url
     * wr_10    = youtube_url
     */

	$primary_link =
		trim($source_url);

	$youtube_link =
		trim($youtube_url);


	/*
     * 같은 URL 게시물 중복 등록 방지
     * source_url이 있으면 source_url,
     * 없으면 youtube_url 기준으로 검사
     */

	$duplicate_check_link =
		$primary_link
		?: $youtube_link;

	if ($duplicate_check_link) {

		$escaped_link =
			api_escape(
				$duplicate_check_link
			);


		$duplicate =
			sql_fetch(
				"
                SELECT
                    wr_id,
                    wr_subject

                FROM {$write_table}

                WHERE
                    wr_is_comment = 0

                    AND (
                        wr_link1 = '{$escaped_link}'
                        OR
                        wr_10 = '{$escaped_link}'
                    )

                LIMIT 1
                "
			);


		if (
			$duplicate &&
			!empty($duplicate['wr_id'])
		) {

			$duplicate_id =
				intval(
					$duplicate['wr_id']
				);


			api_error(
				'같은 원본 URL의 게시물이 이미 존재합니다.',
				409,
				[
					'existing_post' => [

						'post_id' =>
						$duplicate_id,

						'title' =>
						$duplicate['wr_subject'],

						'url' =>
						api_post_url(
							$bo_table,
							$duplicate_id
						)
					]
				]
			);
		}
	}


	/*
     * 그누보드 관리자 계정을
     * 작성자로 사용
     */

	$admin_id =
		trim(
			$config['cf_admin']
				??
				''
		);


	if (!$admin_id) {

		api_error(
			'그누보드 최고관리자 계정을 확인할 수 없습니다.',
			500
		);
	}


	$escaped_admin_id =
		api_escape(
			$admin_id
		);


	$admin =
		sql_fetch(
			"
            SELECT
                mb_id,
                mb_name,
                mb_nick,
                mb_email,
                mb_password

            FROM {$g5['member_table']}

            WHERE
                mb_id =
                '{$escaped_admin_id}'
            "
		);


	if (
		!$admin ||
		empty($admin['mb_id'])
	) {

		api_error(
			'최고관리자 회원 정보를 찾을 수 없습니다.',
			500
		);
	}


	/*
     * 관리자 닉네임을
     * 게시물 작성자 이름으로 사용
     */

	$writer_name =
		$admin['mb_nick']
		?: $admin['mb_name'];


	/*
     * 다음 wr_num 계산
     */

	$num_row =
		sql_fetch(
			"
            SELECT
                MIN(wr_num)
                AS min_wr_num

            FROM {$write_table}
            "
		);


	$min_wr_num =
		intval(
			$num_row['min_wr_num']
				??
				0
		);


	$wr_num =
		$min_wr_num - 1;


	/*
     * SQL escape
     */

	$sql_title =
		api_escape(
			$title
		);


	$sql_content =
		api_escape(
			$content
		);


	$sql_link1 =
		api_escape(
			$primary_link
		);


	$sql_youtube_url =
		api_escape(
			$youtube_link
		);


	$sql_mb_id =
		api_escape(
			$admin['mb_id']
		);


	$sql_password =
		api_escape(
			$admin['mb_password']
		);


	$sql_writer_name =
		api_escape(
			$writer_name
		);


	$sql_email =
		api_escape(
			$admin['mb_email']
		);


	$remote_ip =
		$_SERVER['REMOTE_ADDR']
		??
		'127.0.0.1';


	$sql_ip =
		api_escape(
			$remote_ip
		);


	/*
     * Transaction 시작
     */

	api_sql(
		'START TRANSACTION'
	);


	try {

		/*
         * 게시물 INSERT
         */

		api_sql(
			"
            INSERT INTO {$write_table}

            SET

                wr_num =
                    {$wr_num},

                wr_reply =
                    '',

                wr_parent =
                    0,

                wr_is_comment =
                    0,

                wr_comment =
                    0,

                wr_comment_reply =
                    '',

                ca_name =
                    '',

                wr_option =
                    'html1',

                wr_subject =
                    '{$sql_title}',

                wr_content =
                    '{$sql_content}',

                wr_link1 =
                    '{$sql_link1}',

                wr_link2 =
                    '',

                wr_10 =
                    '{$sql_youtube_url}',

                wr_link1_hit =
                    0,

                wr_link2_hit =
                    0,

                wr_hit =
                    0,

                wr_good =
                    0,

                wr_nogood =
                    0,

                mb_id =
                    '{$sql_mb_id}',

                wr_password =
                    '{$sql_password}',

                wr_name =
                    '{$sql_writer_name}',

                wr_email =
                    '{$sql_email}',

                wr_homepage =
                    '',

                wr_datetime =
                    '" .
				G5_TIME_YMDHIS .
				"',

                wr_file =
                    0,

                wr_last =
                    '" .
				G5_TIME_YMDHIS .
				"',

                wr_ip =
                    '{$sql_ip}'
            "
		);


		/*
         * 생성된 wr_id
         */

		$wr_id =
			intval(
				sql_insert_id()
			);


		if ($wr_id <= 0) {

			throw new Exception(
				'게시물 ID 생성 실패'
			);
		}


		/*
         * 원글이므로
         * wr_parent = 자기 자신의 wr_id
         */

		api_sql(
			"
            UPDATE {$write_table}

            SET
                wr_parent =
                    {$wr_id}

            WHERE
                wr_id =
                    {$wr_id}
            "
		);


		/*
         * 새글 테이블 등록
         */

		$escaped_bo_table =
			api_escape(
				$bo_table
			);


		api_sql(
			"
            INSERT INTO
                {$g5['board_new_table']}

            SET

                bo_table =
                    '{$escaped_bo_table}',

                wr_id =
                    {$wr_id},

                wr_parent =
                    {$wr_id},

                bn_datetime =
                    '" .
				G5_TIME_YMDHIS .
				"',

                mb_id =
                    '{$sql_mb_id}'
            "
		);


		/*
         * 게시판 글 개수 증가
         */

		api_sql(
			"
            UPDATE
                {$g5['board_table']}

            SET
                bo_count_write =
                bo_count_write + 1

            WHERE
                bo_table =
                '{$escaped_bo_table}'
            "
		);


		api_sql(
			'COMMIT'
		);
	} catch (
		Throwable $e
	) {

		sql_query(
			'ROLLBACK',
			false
		);


		api_error(
			'게시물 등록 중 오류가 발생했습니다.',
			500,
			[
				'detail' =>
				$e->getMessage()
			]
		);
	}


	/*
     * 최종 URL
     */

	$post_url =
		api_post_url(
			$bo_table,
			$wr_id
		);


	api_success(
		[

			'message' =>
			'게시물이 등록되었습니다.',

			'category' =>
			$category,

			'bo_table' =>
			$bo_table,

			'post_id' =>
			$wr_id,

			'title' =>
			$title,

			'url' =>
			$post_url

		],
		201
	);
}


/* =========================================================
 * UPDATE
 * 게시물 수정
 * ========================================================= */

if ($action === 'update') {

	$post_id =
		intval(
			$input['post_id']
				??
				0
		);


	/*
     * 현재 server.py 구조에서는
     * category가 비어 있으면
     * AI소식 게시판으로 처리
     */

	$category =
		trim(
			$input['category']
				??
				''
		);


	if (!$category) {
		$category =
			'AI소식';
	}


	if ($post_id <= 0) {

		api_error(
			'올바른 post_id가 필요합니다.',
			400
		);
	}


	$board_info =
		api_get_board(
			$category
		);


	$category =
		$board_info['category'];


	$bo_table =
		$board_info['bo_table'];


	$write_table =
		$g5['write_prefix'] .
		$bo_table;


	/*
     * 게시물 존재 확인
     */

	$existing =
		sql_fetch(
			"
            SELECT
                *

            FROM {$write_table}

            WHERE
                wr_id =
                    {$post_id}

                AND
                wr_is_comment =
                    0
            "
		);


	if (
		!$existing ||
		empty($existing['wr_id'])
	) {

		api_error(
			'수정할 게시물을 찾을 수 없습니다.',
			404,
			[
				'post_id' =>
				$post_id,

				'category' =>
				$category
			]
		);
	}


	$updates = [];


	/*
     * title
     */

	if (
		isset(
			$input['title']
		)
		&&
		trim(
			$input['title']
		)
		!==
		''
	) {

		$title =
			strip_tags(
				trim(
					$input['title']
				)
			);


		$title =
			mb_substr(
				$title,
				0,
				255,
				'UTF-8'
			);


		$updates[] =
			"wr_subject = '" .
			api_escape(
				$title
			) .
			"'";
	}


	/*
     * content
     */

	if (
		isset(
			$input['content']
		)
		&&
		trim(
			$input['content']
		)
		!==
		''
	) {

		$content =
			trim(
				$input['content']
			);


		if (
			function_exists(
				'html_purifier'
			)
		) {

			$content =
				html_purifier(
					$content
				);
		}


		/*
         * Toast UI Markdown 식별자 추가
         * html_purifier() 처리 후 붙여야
         * 식별용 HTML comment가 유지됩니다.
         */

		$markdown_marker =
			'<!--TOASTUI_EDITOR_MARKDOWN-->';

		if (
			strpos(
				$content,
				$markdown_marker
			)
			!==
			0
		) {

			$content =
				$markdown_marker .
				"\r\n" .
				$content;
		}


		$updates[] =
			"wr_content = '" .
			api_escape(
				$content
			) .
			"'";
	}


	/*
     * source_url
     */

	if (
		isset(
			$input['source_url']
		)
		&&
		trim(
			$input['source_url']
		)
		!==
		''
	) {

		$source_url =
			trim(
				$input['source_url']
			);


		$updates[] =
			"wr_link1 = '" .
			api_escape(
				$source_url
			) .
			"'";
	}


	/*
     * youtube_url
     * 사이트의 유튜브동영상 URL 입력란(name="wr_10")에 저장
     */

	if (
		isset(
			$input['youtube_url']
		)
		&&
		trim(
			$input['youtube_url']
		)
		!==
		''
	) {

		$youtube_url =
			trim(
				$input['youtube_url']
			);


		$updates[] =
			"wr_10 = '" .
			api_escape(
				$youtube_url
			) .
			"'";
	}


	if (
		count(
			$updates
		)
		===
		0
	) {

		api_error(
			'수정할 내용이 없습니다.',
			400
		);
	}


	/*
     * 마지막 수정 시간
     */

	$updates[] =
		"wr_last = '" .
		G5_TIME_YMDHIS .
		"'";


	api_sql(
		"
        UPDATE {$write_table}

        SET
            " .
			implode(
				",\n",
				$updates
			) .
			"

        WHERE
            wr_id =
                {$post_id}
        "
	);


	api_success(
		[

			'message' =>
			'게시물이 수정되었습니다.',

			'category' =>
			$category,

			'bo_table' =>
			$bo_table,

			'post_id' =>
			$post_id,

			'url' =>
			api_post_url(
				$bo_table,
				$post_id
			)

		]
	);
}


/* =========================================================
 * 알 수 없는 action
 * ========================================================= */

api_error(
	'지원하지 않는 action입니다.',
	400,
	[
		'action' =>
		$action,

		'allowed_actions' => [
			'list',
			'get',
			'create',
			'update'
		]
	]
);
