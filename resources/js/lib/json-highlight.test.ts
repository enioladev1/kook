import { describe, expect, test } from 'vitest';
import { highlightJson } from '@/lib/json-highlight';

const plain = (raw: string) =>
    (highlightJson(raw) ?? []).map((segment) => segment.text).join('');

describe('highlightJson', () => {
    test('re-indents without reordering keys', () => {
        const raw = '{"z":1,"a":2,"m":{"y":true,"b":null}}';

        expect(plain(raw)).toBe(
            [
                '{',
                '  "z": 1,',
                '  "a": 2,',
                '  "m": {',
                '    "y": true,',
                '    "b": null',
                '  }',
                '}',
            ].join('\n'),
        );
    });

    test('keeps numeric-string keys in their original position', () => {
        const raw = '{"2":"b","1":"a","10":"c"}';

        expect(plain(raw)).toBe(
            ['{', '  "2": "b",', '  "1": "a",', '  "10": "c"', '}'].join('\n'),
        );
    });

    test('tags keys and values with distinct color classes', () => {
        const segments = highlightJson('{"name":"kook","count":3,"ok":true}');
        expect(segments).not.toBeNull();

        const classOf = (text: string) =>
            segments!.find((segment) => segment.text === text)?.className;

        expect(classOf('"name"')).toContain('sky');
        expect(classOf('"kook"')).toContain('emerald');
        expect(classOf('3')).toContain('amber');
        expect(classOf('true')).toContain('violet');
    });

    test('handles empty objects and arrays compactly', () => {
        expect(plain('{"a":{},"b":[]}')).toBe(
            ['{', '  "a": {},', '  "b": []', '}'].join('\n'),
        );
    });

    test('preserves strings that contain braces and escaped quotes', () => {
        const raw = '{"msg":"a {curly} \\"quoted\\" value"}';
        expect(plain(raw)).toBe(
            ['{', '  "msg": "a {curly} \\"quoted\\" value"', '}'].join('\n'),
        );
    });

    test('returns null for non-JSON input so callers can fall back to plain text', () => {
        expect(highlightJson('not json at all')).toBeNull();
        expect(highlightJson('')).toBeNull();
        expect(highlightJson('<html></html>')).toBeNull();
    });
});
