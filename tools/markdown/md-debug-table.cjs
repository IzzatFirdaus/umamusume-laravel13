const fs = require('fs');
const path = require('path');

const filePath = process.argv[2] || 'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md';
const lineNum = parseInt(process.argv[3] || '1153');
const content = fs.readFileSync(filePath, 'utf8');
const lines = content.split('\n');

function isTableSeparator(line) {
  const s = line.trim();
  return /^[-:|\s]+$/.test(s) && s.includes('-') && s.includes('|');
}

function isTableRow(line) {
  return line.startsWith('|') && !isTableSeparator(line);
}

function pipePositions(line) {
  const positions = [];
  for (let i = 0; i < line.length; i++) {
    if (line[i] === '|') positions.push(i);
  }
  return positions;
}

// Find table containing the specified line
let start = -1;
let end = -1;
for (let i = lineNum - 2; i >= 0; i--) {
  if (isTableRow(lines[i]) || isTableSeparator(lines[i])) {
    start = i;
  } else {
    break;
  }
}
if (start === -1) start = lineNum - 1;

for (let i = lineNum - 1; i < lines.length; i++) {
  if (isTableRow(lines[i]) || isTableSeparator(lines[i])) {
    end = i;
  } else {
    break;
  }
}

console.log(`Looking at line ${lineNum} in ${filePath}`);
console.log(`Table found at lines ${start + 1} to ${end + 1}`);
console.log('');

for (let i = start; i <= end; i++) {
  const pipes = pipePositions(lines[i]);
  console.log(`Line ${i + 1}: pipes at [${pipes}]`);
  console.log(`  Content: ${lines[i].substring(0, 70)}`);
}

// Check if header and separator pipes match
if (start <= end && start + 1 <= end) {
  const headerPipes = pipePositions(lines[start]);
  const sepPipes = pipePositions(lines[start + 1]);
  console.log('\nHeader pipes:', headerPipes);
  console.log('Separator pipes:', sepPipes);
  if (JSON.stringify(headerPipes) !== JSON.stringify(sepPipes)) {
    console.log('MISMATCH: Header and separator pipes are NOT aligned');
  } else {
    console.log('Header and separator ARE aligned');
  }
  
  // Check data rows against header
  for (let i = start + 2; i <= end; i++) {
    const rowPipes = pipePositions(lines[i]);
    if (JSON.stringify(rowPipes) !== JSON.stringify(headerPipes)) {
      console.log(`\nMISMATCH: Line ${i + 1} pipes don't match header`);
      console.log('  Line pipes:', rowPipes);
      console.log('  Header pipes:', headerPipes);
      
      // Show cell-by-cell
      const headerCells = lines[start].split('|');
      const rowCells = lines[i].split('|');
      for (let c = 0; c < Math.max(headerCells.length, rowCells.length); c++) {
        const hCell = headerCells[c] || '';
        const rCell = rowCells[c] || '';
        if (hCell.length !== rCell.length) {
          console.log(`  Cell ${c}: header="${hCell.trim()}" (${hCell.length} chars) vs row="${rCell.trim()}" (${rCell.length} chars)`);
        }
      }
    }
  }
}
