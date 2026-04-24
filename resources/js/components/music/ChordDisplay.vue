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
    props.live
        ? 'text-yellow-300 font-bold'
        : 'text-primary font-semibold',
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
    <div class="select-text font-mono leading-relaxed">
        <template v-for="(line, i) in parsedLines" :key="i">
            <!-- [Intro] / [Verse 1] / [Chorus] section headers -->
            <div
                v-if="line.kind === 'section'"
                :class="[
                    'mt-5 mb-1 text-xs font-bold uppercase tracking-widest first:mt-0',
                    live ? 'text-white/40' : 'text-muted-foreground',
                ]"
            >
                {{ line.label }}
            </div>

            <!-- Chord line paired with lyric line below it -->
            <div v-else-if="line.kind === 'chord-lyric'" class="mb-3">
                <div :class="[chordClass, 'whitespace-pre leading-tight']">{{ line.chords }}</div>
                <div class="whitespace-pre leading-snug">{{ line.lyrics }}</div>
            </div>

            <!-- Chord line with no following lyric (e.g. intro pattern) -->
            <div
                v-else-if="line.kind === 'chord-only'"
                :class="[chordClass, 'mb-1 whitespace-pre leading-snug']"
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
                        :class="[chordClass, 'min-w-[1ch] text-xs leading-none']"
                    >{{ token.chord }}</span>
                    <span v-else class="text-xs leading-none">&nbsp;</span>
                    <span class="leading-snug">{{ token.text || ' ' }}</span>
                </span>
            </div>

            <!-- Empty line → paragraph spacing -->
            <div v-else class="h-3" />
        </template>
    </div>
</template>
