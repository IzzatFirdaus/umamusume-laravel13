import json
with open('dead_links_analysis.json') as f:
    data = json.load(f)
print('Renamed candidates:')
for r in data['renamed']:
    print(f'  {r["file"]}:{r["line"]} -> {r["ref"]} === {r["match"]}')
print()
print(f'Total: {len(data["renamed"])}')
