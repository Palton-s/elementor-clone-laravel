import { describe, it, expect } from 'vitest';
import { escHtml, structureIcon } from '../editor/utils.js';

describe('escHtml', () => {
    it('should escape HTML special characters', () => {
        expect(escHtml('<script>alert("xss")</script>')).toBe('&lt;script&gt;alert("xss")&lt;/script&gt;');
    });

    it('should return empty string for empty input', () => {
        expect(escHtml('')).toBe('');
    });

    it('should keep plain text unchanged', () => {
        expect(escHtml('hello world')).toBe('hello world');
    });
});

describe('structureIcon', () => {
    it('should return an svg icon for known types', () => {
        for (const type of ['section', 'column', 'heading', 'text', 'image', 'button']) {
            const svg = structureIcon(type);
            expect(svg.startsWith('<svg')).toBe(true);
            expect(svg).toContain('class="pb-icon');
        }
        expect(structureIcon('section')).not.toBe(structureIcon('heading'));
    });

    it('should return a fallback icon for unknown type', () => {
        const svg = structureIcon('unknown');
        expect(svg.startsWith('<svg')).toBe(true);
        expect(svg).not.toBe(structureIcon('section'));
    });
});
