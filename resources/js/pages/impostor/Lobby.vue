<script setup lang="ts">
import GameLayout from '@/layouts/GameLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { KeyRound, Pencil, Sparkles } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    currentPlayer: { id: number; name: string } | null;
}>();

const ROUND_OPTIONS = [1, 3, 5, 7] as const;

const totalRounds = ref<number>(3);
const joinCode = ref('');
const showJoin = ref(false);

const nameInput = ref(props.currentPlayer?.name ?? '');
const isEditingName = ref(props.currentPlayer === null);

const errors = ref<{ name?: string; code?: string; total_rounds?: string }>({});
const processing = ref(false);

function submitName() {
    if (!nameInput.value.trim()) {
        errors.value = { name: 'Name is required.' };
        return;
    }

    processing.value = true;
    router.post(
        '/impostor/identify',
        { name: nameInput.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditingName.value = false;
                errors.value = {};
            },
            onError: (e) => {
                errors.value = e as Record<string, string>;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function startEditingName() {
    nameInput.value = props.currentPlayer?.name ?? '';
    isEditingName.value = true;
}

function createRoom() {
    processing.value = true;
    router.post(
        '/impostor/rooms',
        { total_rounds: totalRounds.value },
        {
            onError: (e) => {
                errors.value = e as Record<string, string>;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function joinRoom() {
    const code = joinCode.value.toUpperCase().trim();
    if (!code) return;

    processing.value = true;
    router.post(
        '/impostor/rooms/join',
        { code },
        {
            preserveScroll: true,
            onError: (e) => {
                errors.value = e as Record<string, string>;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Impostor" />

    <GameLayout>
        <!-- Hero -->
        <header class="mb-8 mt-4 text-center">
            <div class="mq-wobble mb-3 inline-block text-6xl drop-shadow-lg">🎭</div>
            <h1 class="font-display mq-shimmer-text text-4xl leading-none sm:text-5xl">
                Find the<br />Impostor
            </h1>
            <p class="mt-3 text-sm font-semibold text-white/70">
                A masquerade of liars. <span class="text-white/90">Trust no one.</span>
            </p>
        </header>

        <!-- Identity: name entry / edit -->
        <section v-if="!currentPlayer || isEditingName" class="mq-card mq-bounce-in mb-5 p-5">
            <form class="flex flex-col gap-3" @submit.prevent="submitName">
                <label for="name" class="text-xs font-bold uppercase tracking-wider text-white/60">
                    What's your name?
                </label>
                <input
                    id="name"
                    v-model="nameInput"
                    class="mq-input text-center text-xl"
                    maxlength="32"
                    placeholder="Your alias"
                    autocomplete="off"
                    spellcheck="false"
                    required
                />
                <p v-if="errors.name" class="text-sm font-semibold text-[var(--mq-coral)]">
                    {{ errors.name }}
                </p>
                <div class="flex gap-2">
                    <button
                        v-if="currentPlayer"
                        type="button"
                        class="mq-btn mq-btn-ghost flex-1"
                        @click="isEditingName = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="mq-btn mq-btn-primary flex-1"
                        :disabled="processing"
                    >
                        Save name
                    </button>
                </div>
            </form>
        </section>

        <!-- Identity card (shows when registered) -->
        <section v-else class="mq-card mq-bounce-in mb-5 px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-xl">
                    🕵️
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-white/50">Playing as</p>
                    <p class="truncate text-base font-extrabold text-white">{{ currentPlayer.name }}</p>
                </div>
                <button
                    type="button"
                    class="mq-btn mq-btn-ghost !px-3 !py-2 !text-xs"
                    @click="startEditingName"
                >
                    <Pencil class="size-3" />
                    Change
                </button>
            </div>
        </section>

        <!-- Create / join — only enabled once player is registered -->
        <template v-if="currentPlayer && !isEditingName">
            <!-- Rounds picker -->
            <section class="mb-3">
                <p class="mb-2 px-1 text-xs font-bold uppercase tracking-wider text-white/60">
                    Rounds per game
                </p>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="n in ROUND_OPTIONS"
                        :key="n"
                        type="button"
                        class="mq-btn !py-3 !text-base"
                        :class="totalRounds === n ? 'mq-btn-gold' : 'mq-btn-ghost'"
                        @click="totalRounds = n"
                    >
                        {{ n }}
                    </button>
                </div>
            </section>

            <button
                type="button"
                class="mq-btn mq-btn-primary mq-pulse-glow mb-3 w-full !py-5 text-lg"
                :disabled="processing"
                @click="createRoom"
            >
                <Sparkles class="size-5" />
                Host a Game
            </button>

            <button
                v-if="!showJoin"
                type="button"
                class="mq-btn mq-btn-secondary mb-4 w-full !py-5 text-lg"
                @click="showJoin = true"
            >
                <KeyRound class="size-5" />
                Join a Game
            </button>

            <section v-else class="mq-card mq-bounce-in mb-4 p-5">
                <form class="flex flex-col gap-3" @submit.prevent="joinRoom">
                    <label for="code" class="text-xs font-bold uppercase tracking-wider text-white/60">
                        Enter room code
                    </label>
                    <input
                        id="code"
                        v-model="joinCode"
                        class="mq-input font-display text-center text-3xl uppercase tracking-[0.5em]"
                        maxlength="6"
                        placeholder="ABCDEF"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        required
                    />
                    <p v-if="errors.code" class="text-sm font-semibold text-[var(--mq-coral)]">
                        {{ errors.code }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="mq-btn mq-btn-ghost flex-1"
                            @click="showJoin = false"
                        >
                            Cancel
                        </button>
                        <button type="submit" class="mq-btn mq-btn-secondary flex-1" :disabled="processing">
                            Sneak in
                        </button>
                    </div>
                </form>
            </section>
        </template>

        <p class="mt-2 text-center text-xs leading-relaxed text-white/45">
            Everyone gets a secret word — except the impostor.<br />
            Discuss. Vote. Unmask the liar.
        </p>
    </GameLayout>
</template>
