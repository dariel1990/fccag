const CHROMATIC = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
const FLAT_TO_SHARP: Record<string, string> = {
    Db: 'C#', Eb: 'D#', Gb: 'F#', Ab: 'G#', Bb: 'A#',
};

// Prefer flats for these keys when displaying
const PREFER_FLAT_KEYS = new Set(['F', 'Bb', 'Eb', 'Ab', 'Db']);
const SHARP_TO_FLAT: Record<string, string> = {
    'C#': 'Db', 'D#': 'Eb', 'F#': 'Gb', 'G#': 'Ab', 'A#': 'Bb',
};

function normalizeKey(key: string): string {
    return FLAT_TO_SHARP[key] ?? key;
}

function transposeNote(note: string, semitones: number, targetKey: string): string {
    const normalized = normalizeKey(note);
    const idx = CHROMATIC.indexOf(normalized);
    if (idx === -1) { return note; }
    const result = CHROMATIC[(idx + semitones + 12) % 12];
    // Use flat notation if the target key prefers flats
    if (PREFER_FLAT_KEYS.has(targetKey) && SHARP_TO_FLAT[result]) {
        return SHARP_TO_FLAT[result];
    }
    return result;
}

function transposeChord(chord: string, semitones: number, targetKey = ''): string {
    const match = chord.match(/^([A-G][b#]?)(.*)$/);
    if (!match) { return chord; }
    const [, root, suffix] = match;
    // Handle slash chords like G/B
    const slashIdx = suffix.lastIndexOf('/');
    if (slashIdx !== -1) {
        const bassNote = transposeNote(suffix.slice(slashIdx + 1), semitones, targetKey);
        return transposeNote(root, semitones, targetKey) + suffix.slice(0, slashIdx) + '/' + bassNote;
    }
    return transposeNote(root, semitones, targetKey) + suffix;
}

// Detects whether a token looks like a chord name (e.g. G, Am, F#m7, Bb/D)
function isChordToken(token: string): boolean {
    return /^[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*(\/[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*)?$/.test(token);
}

// Detects whether an entire line is a chord line (all space-separated tokens are chords)
export function isChordLine(line: string): boolean {
    const trimmed = line.trim();
    if (!trimmed) { return false; }
    // Must start with a chord root character (possibly after spaces)
    if (!/^[A-G]/.test(trimmed)) { return false; }
    const tokens = trimmed.split(/\s+/).filter(Boolean);
    return tokens.length > 0 && tokens.every(isChordToken);
}

// Detects whether text uses the old inline [Chord]lyrics format
export function hasInlineChordFormat(text: string): boolean {
    return /\[[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d)*(\/[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d)*)?\]/.test(text);
}

// Transpose a chord line (all chord tokens in the line, preserving spacing)
function transposeChordLine(line: string, semitones: number, targetKey: string): string {
    // Preserve leading whitespace for position alignment
    const leading = line.match(/^(\s*)/)?.[1] ?? '';
    return leading + line.trimStart().replace(/[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*(\/[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*)?/g, (chord) => {
        return transposeChord(chord, semitones, targetKey);
    });
}

// ─── Nashville Number System ──────────────────────────────────────────────────

// Semitone distance from root → Nashville degree label
const NASHVILLE_DEGREES: Record<number, string> = {
    0: '1', 2: '2', 4: '3', 5: '4', 7: '5', 9: '6', 11: '7',
    1: 'b2', 3: 'b3', 6: 'b5', 8: 'b6', 10: 'b7',
};

// Convert numeric quality digits to Unicode superscripts so "17" reads as "1⁷" not "seventeen"
const SUP: Record<string, string> = {
    '0': '⁰', '1': '¹', '2': '²', '3': '³', '4': '⁴',
    '5': '⁵', '6': '⁶', '7': '⁷', '8': '⁸', '9': '⁹',
};

function formatNashvilleChord(degree: string, quality: string): string {
    const superQuality = quality.replace(/\d/g, (d) => SUP[d] ?? d);
    return degree + superQuality;
}

function chordToNashville(chord: string, key: string): string {
    const match = chord.match(/^([A-G][b#]?)(.*)$/);
    if (!match) { return chord; }
    const [, root, suffix] = match;
    const rootIdx = CHROMATIC.indexOf(normalizeKey(root));
    const keyIdx = CHROMATIC.indexOf(normalizeKey(key));
    if (rootIdx === -1 || keyIdx === -1) { return chord; }
    const semitones = (rootIdx - keyIdx + 12) % 12;
    const degree = NASHVILLE_DEGREES[semitones] ?? '?';
    // Handle slash chords: G/B → 5/3
    const slashIdx = suffix.lastIndexOf('/');
    if (slashIdx !== -1) {
        const bassRoot = suffix.slice(slashIdx + 1);
        const bassIdx = CHROMATIC.indexOf(normalizeKey(bassRoot));
        if (bassIdx !== -1) {
            const bassDegree = NASHVILLE_DEGREES[(bassIdx - keyIdx + 12) % 12] ?? '?';
            return formatNashvilleChord(degree, suffix.slice(0, slashIdx)) + '/' + bassDegree;
        }
    }
    return formatNashvilleChord(degree, suffix);
}

// Convert a full chord chart line to Nashville (only converts chord tokens, preserves spacing)
function lineToNashville(line: string, key: string): string {
    return line.replace(/[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*(\/[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*)?/g, (chord) =>
        chordToNashville(chord, key),
    );
}

// Convert full chord chart text to Nashville numbers
export function convertToNashville(text: string, key: string): string {
    if (hasInlineChordFormat(text)) {
        return text.replace(/\[([^\]]+)\]/g, (_, chord) => `[${chordToNashville(chord, key)}]`);
    }
    return text
        .split('\n')
        .map((line) => (isChordLine(line) ? lineToNashville(line, key) : line))
        .join('\n');
}

export function useChordTransposer() {
    const keys = CHROMATIC;

    // Transpose inline [Chord]lyrics format
    function transposeLyrics(lyrics: string, fromKey: string, toKey: string): string {
        const from = normalizeKey(fromKey);
        const to = normalizeKey(toKey);
        const fromIdx = CHROMATIC.indexOf(from);
        const toIdx = CHROMATIC.indexOf(to);
        if (fromIdx === -1 || toIdx === -1 || fromIdx === toIdx) { return lyrics; }
        const delta = (toIdx - fromIdx + 12) % 12;
        return lyrics.replace(/\[([^\]]+)\]/g, (_, chord) => `[${transposeChord(chord, delta, to)}]`);
    }

    // Transpose chord-line format (chords on their own lines above lyrics)
    function transposeChordChart(text: string, fromKey: string, toKey: string): string {
        const from = normalizeKey(fromKey);
        const to = normalizeKey(toKey);
        const fromIdx = CHROMATIC.indexOf(from);
        const toIdx = CHROMATIC.indexOf(to);
        if (fromIdx === -1 || toIdx === -1 || fromIdx === toIdx) { return text; }
        const delta = (toIdx - fromIdx + 12) % 12;
        return text
            .split('\n')
            .map((line) => (isChordLine(line) ? transposeChordLine(line, delta, to) : line))
            .join('\n');
    }

    // Auto-detect format and transpose accordingly
    function transposeAuto(text: string, fromKey: string, toKey: string): string {
        if (hasInlineChordFormat(text)) {
            return transposeLyrics(text, fromKey, toKey);
        }
        return transposeChordChart(text, fromKey, toKey);
    }

    function getSemitones(fromKey: string, toKey: string): number {
        const from = normalizeKey(fromKey);
        const to = normalizeKey(toKey);
        const fromIdx = CHROMATIC.indexOf(from);
        const toIdx = CHROMATIC.indexOf(to);
        return (toIdx - fromIdx + 12) % 12;
    }

    return { keys, transposeLyrics, transposeChordChart, transposeAuto, getSemitones, transposeChord };
}
