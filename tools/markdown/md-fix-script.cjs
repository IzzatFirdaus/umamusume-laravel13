const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = process.cwd();
const config = JSON.parse(fs.readFileSync(path.join(root, '.markdownlint.json'), 'utf8'));

// Get git-tracked markdown files
const trackedFiles = execSync('git ls-files "*.md"', {
  cwd: root,
  encoding: 'utf8'
}).trim().split('\n').filter(f => f.length > 0);

console.log(`Processing ${trackedFiles.length} tracked markdown files\n`);

const Markdownlint = require('markdownlint');
const fileDict = {};
trackedFiles.forEach(f => {
  fileDict[f] = fs.readFileSync(path.join(root, f), 'utf8');
});

console.log('Linting...');
const result = Markdownlint({
  files: Object.keys(fileDict),
  config: config,
  resultMode: 'errorlist'
});

// Group by rule and file
const byRule = {};
const byFile = {};
result.forEach(err => {
  byRule[err.ruleName] = byRule[err.ruleName] || [];
  byRule[err.ruleName].push(err);
  
  if (!byFile[err.fileName]) byFile[err.fileName] = {};
  byFile[err.fileName][err.ruleName] = byFile[err.fileName][err.ruleName] || 0;
  byFile[err.fileName][err.ruleName]++;
});

console.log('Remaining errors by rule:');
Object.entries(byRule).sort((a, b) => b[1].length - a[1].length).forEach(([rule, errors]) => {
  console.log(`  ${rule}: ${errors.length}`);
});
console.log(`\nTotal: ${result.length} errors in ${Object.keys(byFile).length} files`);

// Try built-in fixer
console.log('\n--- Running built-in fixer ---');
const fixed = Markdownlint({
  files: Object.keys(fileDict),
  config: config,
  resultMode: 'errorlist',
  fix: true
});

// Re-lint
const result2 = Markdownlint({
  files: Object.keys(fileDict),
  config: config,
  resultMode: 'errorlist'
});

console.log(`After fix: ${result2.length} errors in ${new Set(result2.map(e => e.fileName)).size} files`);

// Summary of remaining by file
const byFile2 = {};
result2.forEach(err => {
  if (!byFile2[err.fileName]) byFile2[err.fileName] = [];
  byFile2[err.fileName].push(err);
});

console.log('\n--- Remaining errors by file ---');
Object.entries(byFile2).sort((a, b) => b[1].length - a[1].length).forEach(([file, errors]) => {
  const rules = {};
  errors.forEach(e => {
    rules[e.ruleName] = (rules[e.ruleName] || 0) + 1;
  });
  const ruleStr = Object.entries(rules).map(([r, c]) => `${r}(${c})`).join(', ');
  console.log(`  ${file}: ${errors.length} [${ruleStr}]`);
});

// Save remaining errors for further processing
fs.writeFileSync(
  'md-remaining-errors.json',
  JSON.stringify(result2.map(e => ({
    fileName: e.fileName,
    ruleName: e.ruleName,
    lineNumber: e.lineNumber,
    detail: e.detail
  })), null, 2)
);
console.log('\nRemaining errors saved to md-remaining-errors.json');
