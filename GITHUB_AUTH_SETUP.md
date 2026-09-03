# GitHub 원격 저장소 인증 문제 해결

Git 원격 주소에 개인 액세스 토큰(PAT)이 사용자명처럼 포함되어 비밀번호 입력창이 나타날 때 사용하는 해결 절차입니다.

## 1. 비밀번호 입력창 취소

VS Code에 비밀번호 입력창이 떠 있다면 비밀번호나 토큰을 입력하지 말고 `Esc`를 눌러 취소합니다.

## 2. 노출된 토큰 폐기

원격 주소나 화면에 `ghp_`로 시작하는 토큰이 노출되었다면 해당 토큰을 더 이상 사용하지 않습니다.

1. GitHub의 [Personal access tokens 설정](https://github.com/settings/tokens)으로 이동합니다.
2. 원격 주소에 사용했던 토큰을 찾습니다.
3. `Delete` 또는 `Revoke`를 선택하여 폐기합니다.

폐기된 토큰은 복구할 수 없으며, 이후 절차에서는 URL에 새 토큰을 직접 넣지 않습니다.

## 3. 저장소 디렉터리로 이동

```bash
cd /home/smlee/gnuboard/ai
```

## 4. 원격 저장소 주소 정리

토큰이 포함되지 않은 정상적인 HTTPS 주소로 변경합니다.

```bash
git remote set-url origin https://github.com/easygoing2/ai.git
```

토큰이 제거되었는지 확인합니다.

```bash
git remote -v
```

다음과 같이 표시되어야 합니다.

```text
origin  https://github.com/easygoing2/ai.git (fetch)
origin  https://github.com/easygoing2/ai.git (push)
```

## 5. 저장소에 설정된 평문 자격 증명 도우미 제거

이 저장소에 설정된 `credential.helper=store`를 제거합니다.

```bash
git config --local --unset-all credential.helper
```

설정이 이미 없다면 명령이 별도의 메시지 없이 종료될 수 있습니다.

## 6. GitHub CLI로 로그인

```bash
gh auth login
```

화면에 질문이 나오면 다음 순서로 선택합니다.

1. `GitHub.com`
2. `HTTPS`
3. `Login with a web browser`
4. 표시된 일회용 코드를 복사한 뒤 브라우저에서 승인

로그인이 끝나면 Git이 GitHub CLI의 인증정보를 사용하도록 설정합니다.

```bash
gh auth setup-git
```

## 7. 로그인 상태 확인

```bash
gh auth status
```

GitHub 계정에 로그인되었다는 메시지가 나오면 인증 설정이 완료된 것입니다.

## 8. 변경사항 확인 및 커밋

```bash
git status
git diff
```

커밋할 파일을 선택하여 추가합니다. 모든 변경사항을 커밋하려는 경우에만 `git add -A`를 사용합니다.

```bash
git add <파일경로>
git commit -m "변경 내용 설명"
```

예시:

```bash
git add lib/new.lib.php config.php theme/basic/head.php
git commit -m "모바일 화면 설정 및 PHP 경고 수정"
```

## 9. GitHub로 푸시

현재 브랜치가 `main`이면 다음 명령을 실행합니다.

```bash
git push origin main
```

이미 `origin/main`이 upstream으로 연결되어 있으면 다음 명령만 사용해도 됩니다.

```bash
git push
```

## 10. 최종 확인

```bash
git status
git remote -v
gh auth status
```

정상적인 경우 `git status`에 로컬 브랜치가 `origin/main`과 일치한다는 메시지가 표시되고, 원격 주소에는 사용자명·비밀번호·토큰이 포함되지 않습니다.

## 주의사항

- GitHub 계정 비밀번호를 Git 비밀번호 입력창에 입력하지 않습니다.
- 개인 액세스 토큰을 원격 URL, 소스 코드, 문서 또는 셸 스크립트에 넣지 않습니다.
- `git add -A`를 실행하기 전에 `git status`와 `git diff`로 커밋 대상을 확인합니다.
- 새 토큰이 꼭 필요한 경우 최소 권한과 만료일을 설정하고 안전한 자격 증명 저장소를 사용합니다.

