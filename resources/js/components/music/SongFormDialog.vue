<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeSong,
    update as updateSong,
} from '@/actions/App/Http/Controllers/Music/SongController';
import { useChordTransposer } from '@/composables/useChordTransposer';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const KEYS = ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'G#', 'A', 'Bb', 'B'];
const TIME_SIGNATURES = ['4/4', '3/4', '6/8', '2/4', '12/8'];

type Song = {
    id: number;
    title: string;
    artist: string | null;
    composer: string | null;
    original_key: string;
    tempo: number | null;
    time_signature: string | null;
    lyrics_with_chords: string;
    video_link: string | null;
    notes: string | null;
    is_active: boolean;
};

const props = defineProps<{
    open: boolean;
    song?: Song | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const { transposeAuto } = useChordTransposer();

const activeTab = ref<'manual' | 'upload'>('manual');
const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    title: '',
    artist: '',
    composer: '',
    original_key: 'G',
    tempo: '' as string | number,
    time_signature: '4/4',
    lyrics_with_chords: '',
    video_link: '',
    notes: '',
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            activeTab.value = 'manual';
            if (props.song) {
                isEdit.value = true;
                form.value = {
                    title: props.song.title,
                    artist: props.song.artist ?? '',
                    composer: props.song.composer ?? '',
                    original_key: props.song.original_key,
                    tempo: props.song.tempo ?? '',
                    time_signature: props.song.time_signature ?? '4/4',
                    lyrics_with_chords: props.song.lyrics_with_chords,
                    video_link: props.song.video_link ?? '',
                    notes: props.song.notes ?? '',
                    is_active: props.song.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = {
                    title: '',
                    artist: '',
                    composer: '',
                    original_key: 'G',
                    tempo: '',
                    time_signature: '4/4',
                    lyrics_with_chords: '',
                    video_link: '',
                    notes: '',
                    is_active: true,
                };
            }
        }
    },
);

function selectKey(key: string): void {
    const oldKey = form.value.original_key;
    if (key !== oldKey && form.value.lyrics_with_chords.trim()) {
        form.value.lyrics_with_chords = transposeAuto(form.value.lyrics_with_chords, oldKey, key);
    }
    form.value.original_key = key;
}

function handleUploadFile(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) { return; }
    const reader = new FileReader();
    reader.onload = (e) => {
        form.value.lyrics_with_chords = (e.target?.result as string) ?? '';
        activeTab.value = 'manual';
    };
    reader.readAsText(file);
}

function submit(): void {
    isProcessing.value = true;
    errors.value = {};

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            handleClose();
        },
        onError: (errs: Record<string, string>) => {
            errors.value = errs;
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    };

    const payload = {
        ...form.value,
        tempo: form.value.tempo !== '' ? Number(form.value.tempo) : null,
        composer: form.value.composer || null,
        video_link: form.value.video_link || null,
    };

    if (isEdit.value) {
        router.put(updateSong(props.song!.id).url, payload, options);
    } else {
        router.post(storeSong().url, payload, options);
    }
}

function handleClose(): void {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="flex h-dvh max-w-2xl flex-col overflow-hidden rounded-none p-0 sm:h-[96vh] sm:rounded-lg">
            <DialogHeader class="shrink-0 px-6 pt-6">
                <DialogTitle>{{ isEdit ? 'Edit Song' : 'Add New Song' }}</DialogTitle>
                <DialogDescription class="sr-only">
                    {{ isEdit ? 'Update song details and chord chart' : 'Add a new song with chords and lyrics' }}
                </DialogDescription>
            </DialogHeader>

            <!-- Manual / Upload tabs -->
            <div class="shrink-0 px-6">
                <div class="bg-muted inline-flex rounded-md p-1">
                    <button
                        type="button"
                        :class="[
                            'rounded px-4 py-1.5 text-sm font-medium transition-colors',
                            activeTab === 'manual'
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                        @click="activeTab = 'manual'"
                    >
                        Manual
                    </button>
                    <button
                        type="button"
                        :class="[
                            'rounded px-4 py-1.5 text-sm font-medium transition-colors',
                            activeTab === 'upload'
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                        @click="activeTab = 'upload'"
                    >
                        Upload
                    </button>
                </div>
            </div>

            <!-- Upload tab content -->
            <div v-if="activeTab === 'upload'" class="flex flex-1 flex-col gap-4 overflow-y-auto px-6 pb-6">
                <p class="text-muted-foreground text-sm">
                    Upload a plain text file (<code>.txt</code> or <code>.cho</code>) with chords in
                    <code>[Chord]</code> format. The contents will be pasted into the chord chart.
                </p>
                <div
                    class="border-muted-foreground/30 flex flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed px-6 py-12 text-center"
                >
                    <p class="text-muted-foreground text-sm">Drop a file here or click to browse</p>
                    <label class="cursor-pointer">
                        <span class="bg-primary text-primary-foreground rounded px-4 py-2 text-sm font-medium">
                            Choose file
                        </span>
                        <input
                            type="file"
                            accept=".txt,.cho,.chordpro"
                            class="sr-only"
                            @change="handleUploadFile"
                        />
                    </label>
                </div>
            </div>

            <!-- Manual tab content -->
            <form
                v-else
                class="flex flex-1 flex-col overflow-hidden"
                @submit.prevent="submit"
            >
                <div class="flex-1 space-y-4 overflow-y-auto px-6 py-2">
                    <!-- Title -->
                    <div class="space-y-1.5">
                        <Input
                            v-model="form.title"
                            required
                            placeholder="Song title"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <!-- Artist & Composer -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Input v-model="form.artist" placeholder="Artist" />
                            <InputError :message="errors.artist" />
                        </div>
                        <div class="space-y-1.5">
                            <Input v-model="form.composer" placeholder="Composer" />
                            <InputError :message="errors.composer" />
                        </div>
                    </div>

                    <!-- Video link -->
                    <div class="space-y-1.5">
                        <Input v-model="form.video_link" type="url" placeholder="Video link (optional)" />
                        <InputError :message="errors.video_link" />
                    </div>

                    <!-- Time signature & BPM -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Select v-model="form.time_signature">
                                <SelectTrigger>
                                    <SelectValue placeholder="Time signature (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="ts in TIME_SIGNATURES" :key="ts" :value="ts">
                                        {{ ts }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.time_signature" />
                        </div>
                        <div class="space-y-1.5">
                            <Input
                                v-model="form.tempo"
                                type="number"
                                min="20"
                                max="300"
                                placeholder="BPM (optional)"
                            />
                            <InputError :message="errors.tempo" />
                        </div>
                    </div>

                    <!-- Key button grid -->
                    <div class="space-y-2">
                        <Label class="text-xs font-semibold uppercase tracking-wide">Key</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="k in KEYS"
                                :key="k"
                                type="button"
                                :class="[
                                    'rounded-md border px-3 py-1.5 text-sm font-medium transition-colors',
                                    form.original_key === k
                                        ? 'bg-primary border-primary text-primary-foreground'
                                        : 'border-input bg-background hover:bg-accent hover:text-accent-foreground',
                                ]"
                                @click="selectKey(k)"
                            >
                                {{ k }}
                            </button>
                        </div>
                        <InputError :message="errors.original_key" />
                    </div>

                    <!-- Chord chart -->
                    <div class="space-y-1.5">
                        <Textarea
                            v-model="form.lyrics_with_chords"
                            required
                            :placeholder="'[Verse 1]\nG        Em\nAmazing grace how sweet the sound\nC        G\nThat saved a wretch like me'"
                            class="min-h-52 font-mono text-sm"
                        />
                        <p class="text-muted-foreground text-xs">
                            Write chords on their own line above the lyrics. Use <code>[Verse 1]</code>, <code>[Chorus]</code> etc. for section labels.
                        </p>
                        <InputError :message="errors.lyrics_with_chords" />
                    </div>

                    <!-- Active toggle -->
                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="song_is_active"
                            :model-value="form.is_active"
                            @update:model-value="form.is_active = $event as boolean"
                        />
                        <Label for="song_is_active">Active</Label>
                    </div>
                </div>

                <DialogFooter class="shrink-0 border-t px-6 py-4">
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Add song' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
