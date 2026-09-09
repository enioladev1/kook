// Faithfully re-indents a raw JSON string without reordering keys, and tags each
// piece with a color class. Scanning the source left to right keeps the original
// key order; JSON.parse only gates whether we treat the input as JSON at all.

export type JsonSegment = { text: string; className: string };

type TokenType =
    'key' | 'string' | 'number' | 'boolean' | 'null' | 'punctuation';

const COLORS: Record<TokenType, string> = {
    key: 'text-sky-700 dark:text-sky-400',
    string: 'text-emerald-700 dark:text-emerald-400',
    number: 'text-amber-700 dark:text-amber-400',
    boolean: 'text-violet-700 dark:text-violet-400',
    null: 'text-rose-700 dark:text-rose-400',
    punctuation: 'text-muted-foreground',
};

const MAX_LENGTH = 500_000;

type Token = { value: string; type: TokenType };

function tokenize(input: string): Token[] | null {
    const tokens: Token[] = [];
    const n = input.length;
    let i = 0;

    const isWs = (c: string) =>
        c === ' ' || c === '\t' || c === '\n' || c === '\r';

    while (i < n) {
        const c = input[i];

        if (isWs(c)) {
            i++;
            continue;
        }

        if (
            c === '{' ||
            c === '}' ||
            c === '[' ||
            c === ']' ||
            c === ':' ||
            c === ','
        ) {
            tokens.push({ value: c, type: 'punctuation' });
            i++;
            continue;
        }

        if (c === '"') {
            let j = i + 1;
            let closed = false;

            while (j < n) {
                const ch = input[j];

                if (ch === '\\') {
                    j += 2;
                    continue;
                }

                if (ch === '"') {
                    j++;
                    closed = true;
                    break;
                }

                j++;
            }

            if (!closed) {
                return null;
            }

            tokens.push({ value: input.slice(i, j), type: 'string' });
            i = j;
            continue;
        }

        if (c === '-' || (c >= '0' && c <= '9')) {
            const match = /^-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?/.exec(
                input.slice(i),
            );

            if (!match) {
                return null;
            }

            tokens.push({ value: match[0], type: 'number' });
            i += match[0].length;
            continue;
        }

        if (input.startsWith('true', i)) {
            tokens.push({ value: 'true', type: 'boolean' });
            i += 4;
            continue;
        }

        if (input.startsWith('false', i)) {
            tokens.push({ value: 'false', type: 'boolean' });
            i += 5;
            continue;
        }

        if (input.startsWith('null', i)) {
            tokens.push({ value: 'null', type: 'null' });
            i += 4;
            continue;
        }

        return null;
    }

    return tokens;
}

export function highlightJson(raw: string): JsonSegment[] | null {
    const trimmed = raw.trim();

    if (trimmed === '' || trimmed.length > MAX_LENGTH) {
        return null;
    }

    try {
        JSON.parse(trimmed);
    } catch {
        return null;
    }

    const tokens = tokenize(trimmed);

    if (!tokens || tokens.length === 0) {
        return null;
    }

    // A string directly before a colon is an object key.
    for (let k = 0; k < tokens.length; k++) {
        if (tokens[k].type === 'string' && tokens[k + 1]?.value === ':') {
            tokens[k].type = 'key';
        }
    }

    const segments: JsonSegment[] = [];
    let indent = 0;
    const newline = () =>
        segments.push({ text: '\n' + '  '.repeat(indent), className: '' });

    for (let k = 0; k < tokens.length; k++) {
        const token = tokens[k];
        const next = tokens[k + 1];
        const v = token.value;

        if (v === '{' || v === '[') {
            if (next && (next.value === '}' || next.value === ']')) {
                segments.push({
                    text: v + next.value,
                    className: COLORS.punctuation,
                });
                k++;
                continue;
            }

            segments.push({ text: v, className: COLORS.punctuation });
            indent++;
            newline();
            continue;
        }

        if (v === '}' || v === ']') {
            indent = Math.max(0, indent - 1);
            newline();
            segments.push({ text: v, className: COLORS.punctuation });
            continue;
        }

        if (v === ',') {
            segments.push({ text: ',', className: COLORS.punctuation });
            newline();
            continue;
        }

        if (v === ':') {
            segments.push({ text: ': ', className: COLORS.punctuation });
            continue;
        }

        segments.push({ text: v, className: COLORS[token.type] });
    }

    return segments;
}
