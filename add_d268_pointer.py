with open('docs/research-scratch/DESIGN-CORPUS.md', 'r', encoding='utf-8') as f:
    content = f.read()

old = 'absent from the local catalogue.\n\n### 10r. Run Completion state rules'
new = 'absent from the local catalogue.\n\n*(moved to GOVERNANCE.md §"D-20 and D-268 rulings (re-homed 2026-10-18)" on 2026-10-18; original text preserved above per AGENTS.md §11.)*\n\n### 10r. Run Completion state rules'

if old in content:
    content = content.replace(old, new)
    with open('docs/research-scratch/DESIGN-CORPUS.md', 'w', encoding='utf-8') as f:
        f.write(content)
    print('SUCCESS: D-268 pointer added')
else:
    print('OLD STRING NOT FOUND')
    idx = content.find('absent from the local catalogue.')
    if idx >= 0:
        print(repr(content[idx:idx+80]))