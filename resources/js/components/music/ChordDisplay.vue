<script setup lang="ts">
import { computed } from 'vue';
import { convertToNashville, hasInlineChordFormat, isChordLine, useChordTransposer } from '@/composables/useChordTransposer';

const props = defineProps<{
    lyrics: string;
    originalKey: string;
    displayKey: string;
    mode?: 'chord' | 'nashville';
    live?: boolean;
}>();

const chordClass = computed(() =>
    props.live ? '' : 'text-primary',
);

const chordStyle = computed(() =>
    props.live ? { color: 'var(--lt-chord, #fde047)' } : {},
);

const { transposeAuto } = useChordTransposer();

// ─── Types ────────────────────────────────────────────────────────────────────

type SectionLine    = { kind: 'section';      label: string };
type ChordLyricLine = { kind: 'chord-lyric';  chords: string; lyrics: string };
type ChordOnlyLine  = { kind: 'chord-only';   chords: string };
type LyricOnlyLine  = { kind: 'lyric-only';   lyrics: string };
type EmptyLine      = { kind: 'empty' };
type InlineToken    = { chord: string | null; text: string };
type InlineLine     = { kind: 'inline'; tokens: InlineToken[] };

type ParsedLine = SectionLine | ChordLyricLine | ChordOnlyLine | LyricOnlyLine | EmptyLine | InlineLine;

// ─── Helpers ──────────────────────────────────────────────────────────────────

// [Intro], [Verse 1] etc — NOT single-chord brackets like [G]
function isSectionHeader(line: string): boolean {
    const trimmed = line.trim();
    if (!/^\[.+\]$/.test(trimmed)) { return false; }
    const inner = trimmed.slice(1, -1);
    return !/^[A-G][b#]?(m|M|maj|min|dim|aug|sus|add|no|\d+)*(\/[A-G][b#]?)?$/.test(inner);
}

// ─── Chord-line format parser ─────────────────────────────────────────────────

function parseChordLineFormat(text: string): ParsedLine[] {
    const lines = text.split('\n');
    const result: ParsedLine[] = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];

        if (line.trim() === '') {
            result.push({ kind: 'empty' });
            i++;
            continue;
        }

        if (isSectionHeader(line)) {
            result.push({ kind: 'section', label: line.trim().slice(1, -1) });
            i++;
            continue;
        }

        if (isChordLine(line)) {
            const next = lines[i + 1];
            const nextIsLyric =
                next !== undefined &&
                next.trim() !== '' &&
                !isChordLine(next) &&
                !isSectionHeader(next);

            if (nextIsLyric) {
                result.push({ kind: 'chord-lyric', chords: line, lyrics: next });
                i += 2;
            } else {
                result.push({ kind: 'chord-only', chords: line });
                i++;
            }
            continue;
        }

        result.push({ kind: 'lyric-only', lyrics: line });
        i++;
    }

    return result;
}

// ─── Inline [Chord]lyrics parser (backward compat) ───────────────────────────

function parseInlineFormat(text: string): ParsedLine[] {
    return text.split('\n').map((line): ParsedLine => {
        if (line.trim() === '') { return { kind: 'empty' }; }
        if (isSectionHeader(line)) { return { kind: 'section', label: line.trim().slice(1, -1) }; }

        const tokens: InlineToken[] = [];
        const firstIdx = line.indexOf('[');
        if (firstIdx > 0) { tokens.push({ chord: null, text: line.slice(0, firstIdx) }); }
        const regex = /\[([^\]]+)\]([^[]*)/g;
        let match: RegExpExecArray | null;
        while ((match = regex.exec(line)) !== null) {
            tokens.push({ chord: match[1], text: match[2] });
        }
        if (tokens.length === 0) { tokens.push({ chord: null, text: line }); }
        return { kind: 'inline', tokens };
    });
}

// ─── Chord-lyric pair → inline tokens ────────────────────────────────────────

function chordLyricToTokens(chords: string, lyrics: string): InlineToken[] {
    const positions: { chord: string; pos: number }[] = [];
    const regex = /\S+/g;
    let m: RegExpExecArray | null;
    while ((m = regex.exec(chords)) !== null) {
        positions.push({ chord: m[0], pos: m.index });
    }

    if (!positions.length) {
        return [{ chord: null, text: lyrics }];
    }

    // When chords extend past the end of the lyric line, pad the lyric with
    // spaces so the original chord-line column spacing is preserved.
    const last = positions[positions.length - 1];
    const chordLineEnd = last.pos + last.chord.length;
    const paddedLyrics = lyrics.length >= chordLineEnd ? lyrics : lyrics.padEnd(chordLineEnd, ' ');

    // Snap a split position back to the start of the word it falls inside,
    // but never before `min` (the previous split point).
    function snapToWordStart(pos: number, min: number): number {
        if (pos <= min || pos >= paddedLyrics.length) return Math.max(pos, min);
        if (paddedLyrics[pos - 1] === ' ' || paddedLyrics[pos - 1] === undefined) return pos;
        let i = pos;
        while (i > min && paddedLyrics[i - 1] !== ' ') i--;
        return i;
    }

    const tokens: InlineToken[] = [];
    const adjusted: { chord: string; pos: number }[] = [];
    let prev = 0;

    for (const { chord, pos } of positions) {
        const snapped = snapToWordStart(pos, prev);
        adjusted.push({ chord, pos: snapped });
        prev = snapped;
    }

    if (adjusted[0].pos > 0) {
        tokens.push({ chord: null, text: paddedLyrics.slice(0, adjusted[0].pos) });
    }

    for (let i = 0; i < adjusted.length; i++) {
        const { chord, pos } = adjusted[i];
        const end = i + 1 < adjusted.length ? adjusted[i + 1].pos : undefined;
        const text = end !== undefined ? paddedLyrics.slice(pos, end) : paddedLyrics.slice(pos);
        tokens.push({ chord, text: text || ' ' });
    }

    return tokens;
}

// ─── Computed ─────────────────────────────────────────────────────────────────

const parsedLines = computed<ParsedLine[]>(() => {
    const transposed = transposeAuto(props.lyrics, props.originalKey, props.displayKey);

    if (props.mode === 'nashville') {
        if (hasInlineChordFormat(transposed)) {
            return parseInlineFormat(convertToNashville(transposed, props.displayKey));
        }
        // Parse structure using original chord text, then convert chord fields to Nashville
        return parseChordLineFormat(transposed).map((line): ParsedLine => {
            if (line.kind === 'chord-lyric') {
                return { ...line, chords: convertToNashville(line.chords, props.displayKey) };
            }
            if (line.kind === 'chord-only') {
                return { ...line, chords: convertToNashville(line.chords, props.displayKey) };
            }
            return line;
        });
    }

    return hasInlineChordFormat(transposed)
        ? parseInlineFormat(transposed)
        : parseChordLineFormat(transposed);
});
</script>

<template>
    <div class="select-text text-sm leading-relaxed" style="font-family: var(--font-chord)">
        <template v-for="(line, i) in parsedLines" :key="i">
            <!-- [Intro] / [Verse 1] / [Chorus] section headers -->
            <div
                v-if="line.kind === 'section'"
                class="mt-5 mb-1 text-xs font-bold uppercase tracking-widest first:mt-0"
                :style="live ? { color: 'var(--lt-muted, rgba(255,255,255,0.4))' } : {}"
                :class="{ 'text-muted-foreground': !live }"
            >
                {{ line.label }}
            </div>

            <!-- Chord line paired with lyric line — rendered as two stacked rows -->
            <div v-else-if="line.kind === 'chord-lyric'" class="mb-3">
                <div
                    :class="[chordClass, 'whitespace-pre leading-snug']"
                    :style="chordStyle"
                >{{ line.chords }}</div>
                <div class="whitespace-pre leading-snug">{{ line.lyrics }}</div>
            </div>

            <!-- Chord line with no following lyric (e.g. intro pattern) -->
            <div
                v-else-if="line.kind === 'chord-only'"
                :class="[chordClass, 'mb-1 whitespace-pre leading-snug']"
                :style="chordStyle"
            >
                {{ line.chords }}
            </div>

            <!-- Plain lyric line -->
            <div v-else-if="line.kind === 'lyric-only'" class="mb-1 whitespace-pre leading-snug">
                {{ line.lyrics }}
            </div>

            <!-- Inline [Chord]lyrics format — backward compat for old songs -->
            <div v-else-if="line.kind === 'inline'" class="mb-2 flex flex-wrap">
                <span
                    v-for="(token, j) in line.tokens"
                    :key="j"
                    class="inline-flex flex-col"
                >
                    <span
                        v-if="token.chord"
                        :class="[chordClass, 'min-w-[1ch] leading-snug']"
                        :style="chordStyle"
                    >{{ token.chord }}</span>
                    <span v-else class="leading-snug">&nbsp;</span>
                    <span class="leading-snug">{{ token.text || ' ' }}</span>
                </span>
            </div>

            <!-- Empty line → paragraph spacing -->
            <div v-else class="h-3" />
        </template>
    </div>
</template>
