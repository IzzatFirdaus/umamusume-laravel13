const fs = require('fs');
const markdownlint = require('markdownlint/sync');

const config = JSON.parse(fs.readFileSync('.markdownlint.json', 'utf8'));
const results = markdownlint.lint({
  files: ['docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md'],
  config
});

const errs = results['docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md'] || [];
const md060 = errs.filter(e => e.ruleNames.includes('MD060'));

// Show first few with full detail
console.log('Total MD060 errors:', md060.length);
console.log('\nFirst 5 errors with full detail:');
md060.slice(0, 5).forEach(e => {
  console.log({
    lineNumber: e.lineNumber,
    columnNumber: e.columnNumber,
    detail: e.errorDetail,
    description: e.errorDescription,
    ruleNames: e.ruleNames,
    fixInfo: e.fixInfo
  });
});

// Show the actual line content for the first error
if (md060.length > 0) {
  const firstErr = md060[0];
  const lines = fs.readFileSync('docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md', 'utf8').split('\n');
  const line = lines[firstErr.lineNumber - 1];
  console.log('\nLine ' + firstErr.lineNumber + ' content:');
  console.log('Length:', line.length);
  console.log('Columns around error column', firstErr.detail.columnNumber || firstErr.columnNumber, ':');
  const col = (firstErr.columnNumber || 0);
  if (col > 0 && col <= line.length) {
    console.log('Character at col', col, ':', JSON.stringify(line[col-1]));
    console.log('Context:', JSON.stringify(line.substring(col - 5, col + 5)));
  }
}
