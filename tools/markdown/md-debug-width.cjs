const stringWidth = require('string-width');
console.log('string-width version:', require('string-width/package.json').version);
console.log('Width of ❌:', stringWidth('\u274C'));
console.log('Width of NORMAL:', stringWidth('NORMAL'));

const fs = require('fs');
const content = fs.readFileSync('md-test-table.md', 'utf8');
const lines = content.split('\n');

const headerLine = lines[0];
const dataLine3 = lines[2];

console.log('\nHeader line length:', headerLine.length);
console.log('Data line 3 length:', dataLine3.length);

// Find where ❌ is in data line 3
const emojiIdx = dataLine3.indexOf('\u274C');
console.log('❌ position in data line 3:', emojiIdx);
console.log('String width before ❌:', stringWidth(dataLine3.substring(0, emojiIdx)));
console.log('Char count before ❌:', emojiIdx);

// Find the pipe at position 275 in both lines
console.log('\nHeader effective width at pos 275:', stringWidth(headerLine.substring(0, 275)));
console.log('Data effective width at pos 275:', stringWidth(dataLine3.substring(0, 275)));
