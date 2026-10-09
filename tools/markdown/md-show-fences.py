import re
import sys

file_path = sys.argv[1]
with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, line in enumerate(lines, 1):
    stripped = line.strip()
    if stripped.startswith('```'):
        print(f'{i}: {repr(stripped)}')
