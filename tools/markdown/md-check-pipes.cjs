const fs = require('fs');
const content = fs.readFileSync('md-test-table.md', 'utf8');
const lines = content.split('\n');

lines.forEach((line, i) => {
  const pipes = [];
  for (let j = 0; j < line.length; j++) {
    if (line[j] === '|') pipes.push(j);
  }
  console.log(`Line ${i+1} (${line.length} chars): pipes at [${pipes.join(', ')}]`);
});
