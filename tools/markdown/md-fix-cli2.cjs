const fs = require('fs');
const markdownlint = require('markdownlint/sync');

const config = JSON.parse(fs.readFileSync('.markdownlint.json', 'utf8'));

// Test with AGENTS.md
const result = markdownlint.lint({
  files: ['AGENTS.md'],
  config: config,
  fix: false
});

const errors = result['AGENTS.md'] || [];
const md060 = errors.filter(e => e.ruleNames.includes('MD060'));
console.log('MD060 errors in AGENTS.md:', md060.length);

// Try with fix
const result2 = markdownlint.lint({
  files: ['AGENTS.md'],
  config: config,
  fix: true
});
console.log('After fix - MD060 errors:', (result2['AGENTS.md'] || []).filter(e => e.ruleNames.includes('MD060')).length);

// Check what changes were made
const original = fs.readFileSync('AGENTS.md', 'utf8');
const lines = original.split('\n');
console.log('\nLine 87 length:', lines[86].length);

// Check if line 87 changed
const afterContent = fs.readFileSync('AGENTS.md', 'utf8');
fs.writeFileSync('AGENTS.md', afterContent);
console.log('Done');
