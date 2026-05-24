/**
 * Vite plugin: lint PHP templates for forbidden raw-palette color utilities.
 *
 * Phase A: mode = 'warn'  — prints violations, build succeeds.
 * Phase B end: switch to mode = 'error' in vite.config.js to block builds.
 *
 * Forbidden palette (all Tailwind named hues except gray-*):
 *   slate, zinc, stone, neutral, red, orange, amber, yellow, lime, green,
 *   emerald, teal, cyan, sky, blue, indigo, violet, purple, fuchsia, pink, rose
 *
 * OK to use: gray-*, semantic tokens (primary, secondary, surface, success, …)
 */

import { sync as globSync } from 'glob';
import { readFileSync } from 'fs';
import path from 'path';

const FORBIDDEN_HUES = [
  'slate', 'zinc', 'stone', 'neutral',
  'red', 'orange', 'amber', 'yellow', 'lime',
  'green', 'emerald', 'teal', 'cyan', 'sky', 'blue',
  'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose',
];

const PREFIXES = [
  'bg', 'text', 'border', 'from', 'via', 'to',
  'ring', 'outline', 'shadow', 'fill', 'stroke',
  'placeholder', 'caret', 'accent', 'decoration', 'divide',
];

// Matches e.g. `bg-blue-500`, `text-indigo-600`, `from-purple-400`
// Also catches responsive variants like `lg:bg-blue-500`, `hover:text-red-600`
const PATTERN = new RegExp(
  `(?:^|[\\s"'=])(?:[\\w-]+:)*(${PREFIXES.join('|')})-(${FORBIDDEN_HUES.join('|')})-\\d+`,
  'g',
);

/**
 * @param {{ mode?: 'warn' | 'error' }} options
 * @returns {import('vite').Plugin}
 */
export function templateColorLintPlugin({ mode = 'warn' } = {}) {
  let rootDir;

  return {
    name: 'nera-template-color-lint',

    configResolved(config) {
      rootDir = path.resolve(config.root, '..');
    },

    buildStart() {
      const templateFiles = globSync('**/*.{php,twig}', {
        cwd: rootDir,
        ignore: ['**/node_modules/**', '**/dist/**', '**/vendor/**'],
        absolute: true,
      });

      const violations = [];

      for (const file of templateFiles) {
        const source = readFileSync(file, 'utf8');
        const lines = source.split('\n');

        lines.forEach((line, idx) => {
          let match;
          PATTERN.lastIndex = 0;
          while ((match = PATTERN.exec(line)) !== null) {
            violations.push({
              file: path.relative(rootDir, file),
              line: idx + 1,
              match: match[0].trim(),
            });
          }
        });
      }

      if (violations.length === 0) return;

      const header = `[nera-lint] Found ${violations.length} forbidden palette-color utilities in PHP templates:`;
      const body = violations
        .map(v => `  ${v.file}:${v.line}  →  ${v.match}`)
        .join('\n');
      const footer = `\nReplace with semantic tokens (primary, success-bg, info-text, …). See EXTENDING.md.`;
      const message = `${header}\n${body}${footer}`;

      if (mode === 'error') {
        this.error(message);
      } else {
        this.warn(message);
      }
    },
  };
}
