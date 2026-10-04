import os, re, subprocess
REPO = r'D:\Projects\umamusume-laravel13'
listing = set(subprocess.run(['git', 'ls-files'], cwd=REPO, capture_output=True, text=True).stdout.splitlines())
for f in subprocess.run(['git', 'ls-files', '*.md'], cwd=REPO, capture_output=True, text=True).stdout.splitlines():
    f = f.strip()
    if not f: continue
    near = os.path.dirname(f)
    try:
        with open(os.path.join(REPO, f), encoding='utf-8', errors='replace') as fp:
            for n, line in enumerate(fp.read().split('\n'), 1):
                for ref in re.findall(r'`((?:[\w./&-]+/)?[\w.-]+\.md)`', line):
                    cand = ref.replace('\\', '/').lstrip('/')
                    tries = [cand]
                    if '/' not in cand:
                        tries += [f'docs/{cand}', f'docs/research-scratch/{cand}', f'{near}/{cand}' if near else cand]
                    else:
                        tries += [f'docs/{cand}']
                    if not any(t in listing for t in tries):
                        target = next((t for t in tries if os.path.exists(os.path.join(REPO, t))), None)
                        if target:
                            print(f'UNTRACKED {f}:{n} -> {ref} (resolves to {target})')
    except: pass