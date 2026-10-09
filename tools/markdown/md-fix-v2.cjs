const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const markdownlint = require('markdownlint');
const markdownlintSync = require('markdownlint/sync');

const root = process.cwd();
const config = JSON.parse(fs.readFileSync(path.join(root, '.markdownlint.json'), 'utf8'));

// Get git-tracked markdown files
const trackedFiles = execSync('git ls-files "*.md"', {
  cwd: root,
  encoding: 'utf8'
}).trim().split('\n').filter(f => f.length > 0);

console.log(`Processing ${trackedFiles.length} tracked markdown files\n`);

// Lint all files
console.log('Linting...');
const results = markdownlintSync.lint({
  files: trackedFiles,
  config: config
});

// Collect all errors
const allErrors = [];
for (const [file, fileErrors] of Object.entries(results)) {
  if (fileErrors && fileErrors.length > 0) {
    for (const err of fileErrors) {
      allErrors.push({
        file,
        lineNumber: err.lineNumber,
        ruleName: err.ruleName,
        detail: err.detail,
        fixInfo: err.fixInfo
      });
    }
  }
}

// Group by rule
const byRule = {};
for (const e of allErrors) {
  byRule[e.ruleName] = byRule[e.ruleName] || [];
  byRule[e.ruleName].push(e);
}

console.log('Errors by rule:');
Object.entries(byRule).sort((a, b) => b[1].length - a[1].length).forEach(([rule, errors]) => {
  const fixable = errors.filter(e => e.fixInfo).length;
  console.log(`  ${rule}: ${errors.length} (${fixable} fixable)`);
});
console.log(`\nTotal: ${allErrors.length} errors in ${new Set(allErrors.map(e => e.file)).size} files`);

// Apply fixes
console.log('\n--- Applying fixes ---');
let filesFixed = 0;

for (const fileName of Object.keys(results)) {
  const fileErrors = results[fileName];
  if (!fileErrors || fileErrors.length === 0) continue;
  
  const filePath = path.join(root, fileName);
  const content = fs.readFileSync(filePath, 'utf8');
  
  // Apply fixes using markdownlint's applyFixes
  const fixedContent = markdownlint.applyFixes(content, fileErrors);
  
  if (fixedContent !== content) {
    fs.writeFileSync(filePath, fixedContent, 'utf8');
    filesFixed++;
    const fixedCount = fileErrors.filter(e => e.fixInfo).length;
    console.log(`Fixed: ${fileName} (${fixedCount} fixable errors)`);
  }
}

console.log(`\nTotal files fixed: ${filesFixed}`);

// Re-lint
console.log('\n--- Re-linting ---');
const results2 = markdownlintSync.lint({
  files: trackedFiles,
  config: config
});

const remainingErrors = [];
for (const [file, fileErrors] of Object.entries(results2)) {
  if (fileErrors && fileErrors.length > 0) {
    for (const err of fileErrors) {
      remainingErrors.push({
        file,
        lineNumber: err.lineNumber,
        ruleName: err.ruleName,
        detail: err.detail,
        fixInfo: err.fixInfo
      });
    }
  }
}

console.log(`Remaining: ${remainingErrors.length} errors in ${new Set(remainingErrors.map(e => e.file)).size} files`);

// Group remaining by rule
const remainingByRule = {};
for (const e of remainingErrors) {
  remainingByRule[e.ruleName] = remainingByRule[e.ruleName] || [];
  remainingByRule[e.ruleName].push(e);
}

console.log('\nRemaining errors by rule:');
Object.entries(remainingByRule).sort((a, b) => b[1].length - a[1].length).forEach(([rule, errors]) => {
  console.log(`  ${rule}: ${errors.length}`);
});

// Group remaining by file
const remainingByFile = {};
for (const e of remainingErrors) {
  if (!remainingByFile[e.file]) remainingByFile[e.file] = [];
  remainingByFile[e.file].push(e);
}

console.log('\nRemaining errors by file:');
Object.entries(remainingByFile).sort((a, b) => b[1].length - a[1].length).forEach(([file, errors]) => {
  const rules = {};
  for (const e of errors) rules[e.ruleName] = (rules[e.ruleName] || 0) + 1;
  const ruleStr = Object.entries(rules).map(([r, c]) => `${r}(${c})`).join(', ');
  console.log(`  ${file}: ${errors.length} [${ruleStr}]`);
});

// Save remaining errors
fs.writeFileSync(
  path.join(root, 'md-remaining-errors.json'),
  JSON.stringify(remainingErrors, null, 2)
);
console.log('\nRemaining errors saved to md-remaining-errors.json');
