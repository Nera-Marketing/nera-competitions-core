#!/usr/bin/env node
/**
 * codemod-color-utilities.js
 * Replaces forbidden Tailwind palette utilities in PHP files with semantic token utilities.
 *
 * Usage (run from frontend/ directory):
 *   node scripts/codemod-color-utilities.js [--write] [--glob=PATTERN]
 *
 * Without --write: dry-run, prints changed lines only.
 * With --write: applies changes in place.
 *
 * Examples:
 *   node scripts/codemod-color-utilities.js --glob="../../template-parts/homepage/*.php"
 *   node scripts/codemod-color-utilities.js --write --glob="../../woocommerce/myaccount/*.php"
 *   node scripts/codemod-color-utilities.js --write   (all PHP files)
 */

import { readFileSync, writeFileSync } from 'fs';
import globPkg from 'glob';
const { sync: globSync } = globPkg;
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const THEME_ROOT = path.resolve(__dirname, '../..');

// ── Mapping: hue → { shade → semantic token } ───────────────────────────────
// Rule: replace HUE-SHADE with the semantic token.
// The utility prefix (bg-, text-, from-, etc.) and any variant (hover:, lg:, etc.)
// are untouched — only the hue-shade portion is replaced.
const SHADE_MAP = {
  // Indigo = parent's primary. Light shades → secondary tint; mid → primary; dark → primary-dark.
  indigo: {
    50: 'secondary', 100: 'secondary', 200: 'secondary',
    300: 'primary',  400: 'primary',   500: 'primary',  600: 'primary',
    700: 'primary-dark', 800: 'primary-dark', 900: 'primary-dark',
  },

  // Blue: 50/100 info-bg, 200/300 info-border, 400-600 primary/primary-dark, 700-900 info-text
  blue: {
    50: 'info-bg',    100: 'info-bg',
    200: 'info-border', 300: 'info-border',
    400: 'primary',   500: 'primary',   600: 'primary',
    700: 'info-text', 800: 'info-text', 900: 'info-text',
  },

  // Green → success
  green: {
    50: 'success-bg',  100: 'success-bg',
    200: 'success-border', 300: 'success-border',
    400: 'success', 500: 'success', 600: 'success',
    700: 'success-text', 800: 'success-text', 900: 'success-text',
  },

  // Emerald → success (used alongside green for gradients)
  emerald: {
    50: 'success-bg',  100: 'success-bg',
    200: 'success-border', 300: 'success-border',
    400: 'success', 500: 'success', 600: 'success',
    700: 'success-text', 800: 'success-text', 900: 'success-text',
  },

  // Red → danger
  red: {
    50: 'danger-bg',  100: 'danger-bg',
    200: 'danger-border', 300: 'danger-border',
    400: 'danger',    500: 'danger',    600: 'danger',
    700: 'danger-text', 800: 'danger-text', 900: 'danger-text',
  },

  // Yellow → warning
  yellow: {
    50: 'warning-bg',  100: 'warning-bg',
    200: 'warning-border', 300: 'warning-border',
    400: 'warning',    500: 'warning',    600: 'warning',
    700: 'warning-text', 800: 'warning-text', 900: 'warning-text',
  },

  // Orange → warning
  orange: {
    50: 'warning-bg',  100: 'warning-bg',
    200: 'warning-border', 300: 'warning-border',
    400: 'warning',    500: 'warning',    600: 'warning',
    700: 'warning-text', 800: 'warning-text', 900: 'warning-text',
  },

  // Amber → warning (amber is between yellow/orange)
  amber: {
    50: 'warning-bg',  100: 'warning-bg',
    200: 'warning-border', 300: 'warning-border',
    400: 'warning',    500: 'warning',    600: 'warning',
    700: 'warning-text', 800: 'warning-text', 900: 'warning-text',
  },

  // Purple → accent
  purple: {
    50: 'secondary', 100: 'secondary', 200: 'secondary',
    300: 'accent',   400: 'accent',    500: 'accent',   600: 'accent',
    700: 'accent-dark', 800: 'accent-dark', 900: 'accent-dark',
  },

  // Violet → accent (used alongside purple)
  violet: {
    50: 'secondary', 100: 'secondary', 200: 'secondary',
    300: 'accent',   400: 'accent',    500: 'accent',   600: 'accent',
    700: 'accent-dark', 800: 'accent-dark', 900: 'accent-dark',
  },

  // Fuchsia, Pink, Rose → danger territory
  fuchsia: {
    50: 'danger-bg',  100: 'danger-bg',
    200: 'danger-border', 300: 'danger-border',
    400: 'danger',    500: 'danger',    600: 'danger',
    700: 'danger-text', 800: 'danger-text', 900: 'danger-text',
  },
  pink: {
    50: 'danger-bg',  100: 'danger-bg',
    200: 'danger-border', 300: 'danger-border',
    400: 'danger',    500: 'danger',    600: 'danger',
    700: 'danger-text', 800: 'danger-text', 900: 'danger-text',
  },
  rose: {
    50: 'danger-bg',  100: 'danger-bg',
    200: 'danger-border', 300: 'danger-border',
    400: 'danger',    500: 'danger',    600: 'danger',
    700: 'danger-text', 800: 'danger-text', 900: 'danger-text',
  },

  // Slate → background-light (50) or gray equivalent (rest)
  slate: {
    50: 'background-light',
    100: 'gray-100', 200: 'gray-200', 300: 'gray-300',
    400: 'gray-400', 500: 'gray-500', 600: 'gray-600',
    700: 'gray-700', 800: 'gray-800', 900: 'gray-900',
  },

  // Zinc → gray equivalent
  zinc: {
    50: 'gray-50',  100: 'gray-100', 200: 'gray-200', 300: 'gray-300',
    400: 'gray-400', 500: 'gray-500', 600: 'gray-600', 700: 'gray-700',
    800: 'gray-800', 900: 'gray-900', 950: 'gray-900',
  },

  // Stone, Neutral → gray equivalent
  stone: {
    50: 'gray-50',  100: 'gray-100', 200: 'gray-200', 300: 'gray-300',
    400: 'gray-400', 500: 'gray-500', 600: 'gray-600', 700: 'gray-700',
    800: 'gray-800', 900: 'gray-900',
  },
  neutral: {
    50: 'gray-50',  100: 'gray-100', 200: 'gray-200', 300: 'gray-300',
    400: 'gray-400', 500: 'gray-500', 600: 'gray-600', 700: 'gray-700',
    800: 'gray-800', 900: 'gray-900',
  },

  // Teal → success (teal is in the green family)
  teal: {
    50: 'success-bg',  100: 'success-bg',
    200: 'success-border', 300: 'success-border',
    400: 'success', 500: 'success', 600: 'success',
    700: 'success-text', 800: 'success-text', 900: 'success-text',
  },

  // Cyan → info (cyan is blue-adjacent)
  cyan: {
    50: 'info-bg',    100: 'info-bg',
    200: 'info-border', 300: 'info-border',
    400: 'info',      500: 'info',      600: 'info',
    700: 'info-text', 800: 'info-text', 900: 'info-text',
  },

  // Sky → info (lighter blue)
  sky: {
    50: 'info-bg',    100: 'info-bg',
    200: 'info-border', 300: 'info-border',
    400: 'info',      500: 'info',      600: 'info',
    700: 'info-text', 800: 'info-text', 900: 'info-text',
  },

  // Lime → success (lime is in the green family)
  lime: {
    50: 'success-bg',  100: 'success-bg',
    200: 'success-border', 300: 'success-border',
    400: 'success', 500: 'success', 600: 'success',
    700: 'success-text', 800: 'success-text', 900: 'success-text',
  },
};

// ── Build replacement patterns ────────────────────────────────────────────────

const allShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].join('|');

function buildRulesForHue(hue, shadeMap) {
  // Match: hue-shade not preceded by a letter (so "not-indigo-500" won't match the hue)
  // Optionally followed by /opacity modifier
  const shadesPattern = Object.keys(shadeMap).sort((a, b) => b - a).join('|');
  const regex = new RegExp(
    `(?<![a-zA-Z])(${hue})-(${shadesPattern})(\\/[^\\s"'\`),\\]>{}]*)?`,
    'g',
  );
  return { hue, regex, shadeMap };
}

const RULES = Object.entries(SHADE_MAP).map(([hue, shadeMap]) =>
  buildRulesForHue(hue, shadeMap),
);

// ── Core transform ────────────────────────────────────────────────────────────

function transformContent(content) {
  let result = content;
  const replacements = [];

  for (const { hue, regex, shadeMap } of RULES) {
    regex.lastIndex = 0;
    result = result.replace(regex, (match, h, shade, opacity) => {
      const semantic = shadeMap[parseInt(shade, 10)];
      if (!semantic) return match;
      const replacement = `${semantic}${opacity || ''}`;
      replacements.push({ from: match.replace(/^[^a-zA-Z]*/, ''), to: replacement });
      return replacement;
    });
  }

  return { content: result, replacements };
}

// ── Diff printer (concise) ────────────────────────────────────────────────────

function printDiff(relPath, original, updated) {
  const origLines = original.split('\n');
  const newLines  = updated.split('\n');

  let hasDiff = false;
  origLines.forEach((line, i) => {
    if (line !== newLines[i]) {
      if (!hasDiff) {
        console.log(`\n--- ${relPath}`);
        hasDiff = true;
      }
      console.log(`  L${i + 1} - ${line.trim().slice(0, 120)}`);
      console.log(`  L${i + 1} + ${(newLines[i] || '').trim().slice(0, 120)}`);
    }
  });
  return hasDiff;
}

// ── Main ──────────────────────────────────────────────────────────────────────

const args = process.argv.slice(2);
const write = args.includes('--write');
const globArg = args.find(a => a.startsWith('--glob='));
const globPattern = globArg
  ? globArg.replace('--glob=', '')
  : path.join(THEME_ROOT, '**/*.php');

const files = globSync(globPattern, {
  cwd: THEME_ROOT,
  ignore: ['**/node_modules/**', '**/dist/**', '**/vendor/**'],
  absolute: true,
});

let totalFiles = 0;
let totalChanges = 0;

for (const file of files) {
  const original = readFileSync(file, 'utf8');
  const { content: updated, replacements } = transformContent(original);

  if (updated === original) continue;

  totalFiles++;
  totalChanges += replacements.length;

  const relPath = path.relative(THEME_ROOT, file);

  if (write) {
    writeFileSync(file, updated, 'utf8');
    console.log(`✓ ${relPath} (${replacements.length} replacements)`);
  } else {
    printDiff(relPath, original, updated);
  }
}

if (write) {
  console.log(`\n✅ Done: ${totalChanges} replacements across ${totalFiles} files.`);
} else {
  console.log(`\n📋 Dry run: ${totalChanges} replacements would be made in ${totalFiles} files. Pass --write to apply.`);
}
