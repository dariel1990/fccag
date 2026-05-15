<script setup lang="ts">
import GameLayout from '@/layouts/GameLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import {
    Check,
    Copy,
    Crown,
    Eye,
    Forward,
    LogOut,
    PartyPopper,
    Search,
    Share2,
    Skull,
    Sparkles,
    Trophy,
    Vote,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

type Player = {
    id: number;
    name: string;
    is_eliminated: boolean;
    vote_count: number;
};

type RevealPlayer = {
    id: number;
    name: string;
    is_impostor: boolean;
    assigned_word: string | null;
    vote_count: number;
};

type ScoreboardPlayerStat = {
    id: number;
    name: string;
    times_as_impostor: number;
    successful_bluffs: number;
    votes_received_as_impostor: number;
    votes_received_as_innocent: number;
    correct_votes: number;
    total_votes_cast: number;
};

type ScoreboardRound = {
    round: number;
    category: string;
    real_word: string;
    impostor_word: string | null;
    impostor: { id: number; name: string } | null;
    outcome: 'caught' | 'escaped' | 'tie';
};

type Scoreboard = {
    rounds: ScoreboardRound[];
    players: ScoreboardPlayerStat[];
    best_impostor: { id: number; name: string; successful_bluffs: number; times_as_impostor: number } | null;
    best_detective: { id: number; name: string; correct_votes: number } | null;
    most_suspicious: { id: number; name: string; votes_received_as_innocent: number } | null;
};

const props = defineProps<{
    room: {
        id: number;
        code: string;
        host_player_id: number;
        status: 'waiting' | 'playing' | 'voting' | 'round_end' | 'finished' | 'ended';
        category: string | null;
        round_number: number;
        total_rounds: number;
        players: Player[];
    };
    myPivot: {
        is_impostor: boolean;
        assigned_word: string | null;
        is_eliminated: boolean;
    } | null;
    reveal: {
        real_word: string | null;
        impostor_word: string | null;
        players: RevealPlayer[];
    } | null;
    scoreboard: Scoreboard | null;
    currentPlayer: { id: number; name: string };
}>();

const localVoteCounts = ref<Record<number, number>>({});
const hasVoted = ref(false);
const codeCopied = ref(false);
const peeking = ref(false);
const selectedTarget = ref<number | null>(null);
const voteProcessing = ref(false);
const voteError = ref<string | null>(null);

const isHost = computed(() => props.currentPlayer.id === props.room.host_player_id);
const channelName = computed(() => `room.${props.room.code}`);

const playersWithLiveVotes = computed(() =>
    props.room.players.map((p) => ({
        ...p,
        vote_count: localVoteCounts.value[p.id] ?? p.vote_count,
    })),
);

const voteablePlayers = computed(() =>
    playersWithLiveVotes.value.filter((p) => p.id !== props.currentPlayer.id && !p.is_eliminated),
);

const canStart = computed(() => props.room.players.length >= 3);

// ----- Role reveal flip state (per round, stored in sessionStorage) -----
const revealKey = computed(() => `mq_revealed_${props.room.code}_${props.room.round_number}`);
const isRevealed = ref(false);
function loadRevealedState() {
    isRevealed.value = sessionStorage.getItem(revealKey.value) === '1';
}
function revealRole() {
    isRevealed.value = true;
    sessionStorage.setItem(revealKey.value, '1');
    if ('vibrate' in navigator) navigator.vibrate?.(20);
}
watch(
    () => props.room.round_number,
    () => loadRevealedState(),
);

// ----- Result computed for round_end / finished states -----
const isRevealState = computed(() =>
    ['round_end', 'finished', 'ended'].includes(props.room.status),
);
const impostorPlayer = computed(() => props.reveal?.players.find((p) => p.is_impostor) ?? null);
const result = computed<'caught' | 'escaped' | 'tie' | null>(() => {
    if (!isRevealState.value) return null;
    if (!props.reveal || !impostorPlayer.value) return 'tie';
    const sorted = [...props.reveal.players].sort((a, b) => b.vote_count - a.vote_count);
    if (sorted.length === 0) return 'tie';
    if (sorted.length > 1 && sorted[0].vote_count === sorted[1].vote_count) return 'tie';
    if (sorted[0].vote_count === 0) return 'tie';
    return sorted[0].is_impostor ? 'caught' : 'escaped';
});

const resultMeta = computed(() => {
    switch (result.value) {
        case 'caught':
            return {
                emoji: '🎉',
                title: 'Impostor Caught!',
                subtitle: 'The sleuths win this round.',
                tone: 'mint',
            };
        case 'escaped':
            return {
                emoji: '😈',
                title: 'Impostor Escaped!',
                subtitle: 'The liar slipped through the masks.',
                tone: 'coral',
            };
        case 'tie':
        default:
            return {
                emoji: '🎭',
                title: 'A Stalemate',
                subtitle: 'The impostor hides another day.',
                tone: 'gold',
            };
    }
});

// ----- Confetti pieces (CSS-only, generated once on mount of ended state) -----
const confettiPieces = computed(() => {
    const emojis = ['🎉', '🎭', '✨', '🌟', '💫', '🎊'];
    return Array.from({ length: 36 }, (_, i) => ({
        emoji: emojis[i % emojis.length],
        left: `${Math.random() * 100}%`,
        delay: `${Math.random() * 1.6}s`,
        duration: `${2.5 + Math.random() * 2}s`,
        drift: `${(Math.random() - 0.5) * 80}px`,
    }));
});

// ----- Actions -----
function copyCode() {
    navigator.clipboard?.writeText(props.room.code);
    codeCopied.value = true;
    setTimeout(() => (codeCopied.value = false), 1600);
}

async function shareCode() {
    const shareData = {
        title: 'Find the Impostor',
        text: `Join my masquerade — code: ${props.room.code}`,
        url: window.location.href,
    };
    try {
        if (navigator.share) {
            await navigator.share(shareData);
        } else {
            copyCode();
        }
    } catch {
        // user cancelled — no-op
    }
}

function startGame() {
    router.post(`/impostor/rooms/${props.room.code}/start`, {}, { preserveScroll: true });
}

function leaveRoom() {
    if (!confirm('Leave this room?')) return;
    router.post(`/impostor/rooms/${props.room.code}/leave`, {}, { preserveScroll: false });
}

function openVoting() {
    router.post(`/impostor/rooms/${props.room.code}/open-voting`, {}, { preserveScroll: true });
}

function selectTarget(p: Player) {
    if (hasVoted.value || voteProcessing.value) return;
    if (selectedTarget.value === p.id) {
        // tap again to confirm
        confirmVote(p);
    } else {
        selectedTarget.value = p.id;
    }
}

function confirmVote(p: Player) {
    voteProcessing.value = true;
    voteError.value = null;
    router.post(
        `/impostor/rooms/${props.room.code}/vote`,
        { target_player_id: p.id },
        {
            preserveScroll: true,
            onSuccess: () => {
                hasVoted.value = true;
                selectedTarget.value = null;
                if ('vibrate' in navigator) navigator.vibrate?.(15);
            },
            onError: (e) => {
                voteError.value = (e as Record<string, string>).target_player_id ?? (e as Record<string, string>).target ?? 'Vote failed.';
            },
            onFinish: () => {
                voteProcessing.value = false;
            },
        },
    );
}

function resolve() {
    router.post(`/impostor/rooms/${props.room.code}/resolve`, {}, { preserveScroll: true });
}

function nextRound() {
    router.post(`/impostor/rooms/${props.room.code}/next-round`, {}, { preserveScroll: true });
}

// ----- Avatar — deterministic mask + color per player id -----
const masks = ['🎭', '🦊', '🐺', '🦝', '🦁', '🐯', '🐱', '🦉', '🐧', '🐸', '🐲', '🦋'];
function maskFor(id: number) {
    return masks[id % masks.length];
}
const avatarHues = [326, 189, 48, 151, 280, 200, 30, 0];
function hueFor(id: number) {
    return avatarHues[id % avatarHues.length];
}

// ----- Echo (public channel — room code is the access secret) -----
useEcho(
    channelName.value,
    'PlayerJoined',
    () => {
        router.reload({ only: ['room'] });
    },
    [],
    'public',
);

useEcho(
    channelName.value,
    'PlayerLeft',
    () => {
        router.reload({ only: ['room'] });
    },
    [],
    'public',
);

useEcho(
    channelName.value,
    'GameStarted',
    () => {
        hasVoted.value = false;
        localVoteCounts.value = {};
        selectedTarget.value = null;
        router.reload({ only: ['room', 'myPivot'] });
    },
    [],
    'public',
);

useEcho<{ players: Player[] }>(
    channelName.value,
    'VoteCast',
    (e) => {
        const next: Record<number, number> = {};
        for (const p of e.players) next[p.id] = p.vote_count;
        localVoteCounts.value = next;
    },
    [],
    'public',
);

useEcho(
    channelName.value,
    'RoundEnded',
    () => {
        router.reload({ only: ['room', 'myPivot', 'reveal'] });
    },
    [],
    'public',
);

useEcho(
    channelName.value,
    'GameEnded',
    () => {
        router.reload({ only: ['room', 'myPivot', 'reveal', 'scoreboard'] });
    },
    [],
    'public',
);

onMounted(() => {
    loadRevealedState();
});
</script>

<template>
    <Head :title="`Room ${room.code}`" />

    <GameLayout>
    <!-- Top bar -->
    <div class="mb-4 flex items-center justify-between gap-2">
        <Link
            href="/impostor"
            class="mq-btn mq-btn-ghost !px-3 !py-2 !text-sm"
            aria-label="Leave room"
        >
            <LogOut class="size-4" /> Leave
        </Link>
        <span
            v-if="['playing', 'voting', 'round_end'].includes(room.status)"
            class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white/80"
        >
            Round {{ room.round_number }} / {{ room.total_rounds }}
        </span>
        <span class="text-xs font-bold uppercase tracking-wider text-white/50">
            🎭 {{ room.players.length }}
        </span>
    </div>

    <!-- ============== WAITING ============== -->
    <template v-if="room.status === 'waiting'">
        <section class="mq-card mq-bounce-in mb-5 p-6 text-center">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-white/50">
                Room code
            </p>
            <p class="font-display mq-shimmer-text text-5xl tracking-[0.3em]">
                {{ room.code }}
            </p>
            <p class="mt-3 inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white/70">
                {{ room.total_rounds }} round{{ room.total_rounds === 1 ? '' : 's' }} per game
            </p>
            <div class="mt-5 flex justify-center gap-2">
                <button
                    type="button"
                    class="mq-btn mq-btn-ghost !py-2.5 !text-sm"
                    @click="copyCode"
                >
                    <component :is="codeCopied ? Check : Copy" class="size-4" />
                    {{ codeCopied ? 'Copied!' : 'Copy' }}
                </button>
                <button
                    type="button"
                    class="mq-btn mq-btn-gold !py-2.5 !text-sm"
                    @click="shareCode"
                >
                    <Share2 class="size-4" /> Share
                </button>
            </div>
        </section>

        <section class="mq-card mb-5 p-5">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 class="font-display text-xl text-white">Sleuths gathered</h2>
                <span class="text-sm font-bold text-white/60">{{ room.players.length }}/8</span>
            </div>
            <ul class="grid grid-cols-2 gap-2.5">
                <li
                    v-for="p in room.players"
                    :key="p.id"
                    class="mq-chip mq-bounce-in flex items-center gap-2 px-3 py-2.5"
                >
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full text-xl"
                        :style="{
                            background: `linear-gradient(135deg, hsl(${hueFor(p.id)} 80% 55%), hsl(${(hueFor(p.id) + 30) % 360} 80% 45%))`,
                        }"
                    >
                        {{ maskFor(p.id) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-extrabold text-white">{{ p.name }}</p>
                        <p
                            v-if="p.id === room.host_player_id"
                            class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-[var(--mq-gold)]"
                        >
                            <Crown class="size-3" /> Host
                        </p>
                        <p v-else class="text-[10px] font-bold uppercase tracking-wider text-white/40">
                            Sleuth
                        </p>
                    </div>
                </li>
            </ul>
        </section>

        <button
            v-if="isHost"
            type="button"
            class="mq-btn w-full !py-5 text-lg"
            :class="canStart ? 'mq-btn-primary mq-pulse-glow' : 'mq-btn-ghost'"
            :disabled="!canStart"
            @click="startGame"
        >
            <Sparkles class="size-5" />
            <span v-if="canStart">Start the masquerade</span>
            <span v-else>Need {{ 3 - room.players.length }} more sleuth(s)</span>
        </button>
        <div v-else class="mq-card flex items-center justify-center gap-3 p-5">
            <span class="text-2xl">🎭</span>
            <div>
                <p class="font-display text-base text-white">Waiting for the host</p>
                <span class="mq-dots mt-1"><span /><span /><span /></span>
            </div>
        </div>

        <button type="button" class="mq-btn mq-btn-ghost w-full" @click="leaveRoom">
            <LogOut class="size-4" />
            Leave room
        </button>
    </template>

    <!-- ============== PLAYING ============== -->
    <template v-else-if="room.status === 'playing'">
        <!-- Role reveal flip card -->
        <section v-if="!isRevealed" class="mq-flip-scene mb-5">
            <button
                type="button"
                class="mq-flip-card mq-card relative aspect-[3/4] w-full overflow-hidden p-0 text-left"
                @click="revealRole"
            >
                <div class="mq-flip-face absolute inset-0 flex flex-col items-center justify-center gap-4 p-6">
                    <div class="mq-wobble text-7xl">🎭</div>
                    <p class="font-display text-2xl text-white">Your role awaits</p>
                    <p class="text-sm text-white/60">Tap to reveal</p>
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 mq-dots"><span /><span /><span /></div>
                </div>
            </button>
        </section>

        <section v-else class="mb-5 mq-bounce-in">
            <!-- Role banner -->
            <div
                v-if="myPivot?.is_impostor"
                class="mq-card mb-4 flex flex-col items-center gap-2 border-2 p-6 text-center"
                style="border-color: hsl(0 100% 68% / 0.6); background: linear-gradient(180deg, hsl(0 70% 25%), hsl(340 70% 22%));"
            >
                <div class="text-5xl">🎭</div>
                <p class="font-display text-3xl text-white">You are the Impostor</p>
                <p class="text-sm font-semibold text-white/80">Blend in. Bluff hard. Find the real word.</p>
            </div>
            <div
                v-else
                class="mq-card mb-4 flex flex-col items-center gap-2 p-5 text-center"
            >
                <div class="text-4xl">🕵️</div>
                <p class="text-xs font-bold uppercase tracking-wider text-white/60">
                    Category · {{ room.category }}
                </p>
                <p class="text-xs font-semibold text-white/50">
                    Hold to peek your word — don't let neighbors see!
                </p>
                <button
                    type="button"
                    class="mq-btn mq-btn-gold mt-1 select-none !py-3"
                    @pointerdown="peeking = true"
                    @pointerup="peeking = false"
                    @pointerleave="peeking = false"
                    @pointercancel="peeking = false"
                >
                    <Eye class="size-5" />
                    <span v-if="peeking" class="font-display text-2xl tracking-wider">
                        {{ myPivot?.assigned_word }}
                    </span>
                    <span v-else>Hold to peek</span>
                </button>
            </div>

            <!-- Players -->
            <div class="mq-card p-5">
                <div class="mb-3 flex items-baseline justify-between">
                    <h2 class="font-display text-xl text-white">The suspects</h2>
                    <span class="text-sm font-bold text-white/60">{{ room.players.length }}</span>
                </div>
                <ul class="grid grid-cols-2 gap-2.5">
                    <li
                        v-for="p in room.players"
                        :key="p.id"
                        class="mq-chip flex items-center gap-2 px-3 py-2.5"
                    >
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-xl"
                            :style="{
                                background: `linear-gradient(135deg, hsl(${hueFor(p.id)} 80% 55%), hsl(${(hueFor(p.id) + 30) % 360} 80% 45%))`,
                            }"
                        >
                            {{ maskFor(p.id) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-white">{{ p.name }}</p>
                            <p
                                v-if="p.id === currentPlayer.id"
                                class="text-[10px] font-bold uppercase tracking-wider text-[var(--mq-cyan)]"
                            >
                                You
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

            <button
                v-if="isHost"
                type="button"
                class="mq-btn mq-btn-primary mq-pulse-glow mt-5 w-full !py-5 text-lg"
                @click="openVoting"
            >
                <Vote class="size-5" /> Open the vote
            </button>
            <p v-else class="mt-5 text-center text-sm font-semibold text-white/60">
                🗣️ Discuss freely. The host will open the vote.
            </p>
        </section>
    </template>

    <!-- ============== VOTING ============== -->
    <template v-else-if="room.status === 'voting'">
        <section class="mq-card mq-bounce-in mb-5 p-5 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/50">
                Round {{ room.round_number }}
            </p>
            <h2 class="font-display mt-1 text-3xl text-white">Who is the impostor?</h2>
            <p class="mt-1 text-sm text-white/60">
                <span v-if="hasVoted">Vote locked in. Waiting on the others…</span>
                <span v-else-if="selectedTarget !== null">Tap again to confirm your vote.</span>
                <span v-else>Tap a suspect to select. Tap again to lock it in.</span>
            </p>
        </section>

        <ul class="mb-5 grid grid-cols-2 gap-3">
            <li
                v-for="p in voteablePlayers"
                :key="p.id"
            >
                <button
                    type="button"
                    class="mq-chip relative flex w-full flex-col items-center gap-2 p-4 transition-all duration-200"
                    :class="{
                        'ring-2 ring-[var(--mq-magenta)] scale-[1.03]': selectedTarget === p.id,
                        'opacity-50': hasVoted && selectedTarget !== p.id,
                    }"
                    :style="selectedTarget === p.id ? 'box-shadow: 0 0 24px hsl(326 100% 62% / 0.5);' : ''"
                    :disabled="hasVoted || voteProcessing"
                    @click="selectTarget(p)"
                >
                    <div
                        class="flex size-14 items-center justify-center rounded-full text-3xl"
                        :style="{
                            background: `linear-gradient(135deg, hsl(${hueFor(p.id)} 80% 55%), hsl(${(hueFor(p.id) + 30) % 360} 80% 45%))`,
                        }"
                    >
                        {{ maskFor(p.id) }}
                    </div>
                    <p class="text-sm font-extrabold text-white">{{ p.name }}</p>
                    <span
                        class="absolute right-2 top-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-black/40 px-2 text-xs font-extrabold text-white"
                        :class="{ 'mq-pop': true }"
                        :key="`${p.id}-${p.vote_count}`"
                    >
                        {{ p.vote_count }}
                    </span>
                    <span
                        v-if="selectedTarget === p.id && !hasVoted"
                        class="text-[10px] font-bold uppercase tracking-wider text-[var(--mq-magenta)]"
                    >
                        Tap again to vote
                    </span>
                </button>
            </li>
        </ul>

        <p
            v-if="voteError"
            class="mb-3 text-center text-sm font-semibold text-[var(--mq-coral)]"
        >
            {{ voteError }}
        </p>

        <button
            v-if="isHost"
            type="button"
            class="mq-btn mq-btn-danger w-full !py-5 text-lg"
            @click="resolve"
        >
            <Skull class="size-5" /> Reveal the impostor
        </button>
    </template>

    <!-- ============== ROUND END (between rounds) ============== -->
    <template v-else-if="room.status === 'round_end'">
        <section class="mq-card mq-bounce-in mb-5 overflow-hidden p-6 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/50">
                Round {{ room.round_number }} of {{ room.total_rounds }}
            </p>
            <div class="mt-2 text-5xl">{{ resultMeta.emoji }}</div>
            <h2
                class="font-display mt-1 text-2xl"
                :style="{
                    color: resultMeta.tone === 'mint' ? 'var(--mq-mint)'
                         : resultMeta.tone === 'coral' ? 'var(--mq-coral)'
                         : 'var(--mq-gold)'
                }"
            >
                {{ resultMeta.title }}
            </h2>
            <p class="mt-1 text-sm font-semibold text-white/70">{{ resultMeta.subtitle }}</p>

            <div v-if="impostorPlayer" class="mq-chip mq-pop mt-4 mx-auto inline-flex items-center gap-3 px-4 py-2.5">
                <div
                    class="flex size-10 items-center justify-center rounded-full text-xl"
                    :style="{
                        background: `linear-gradient(135deg, hsl(${hueFor(impostorPlayer.id)} 80% 55%), hsl(${(hueFor(impostorPlayer.id) + 30) % 360} 80% 45%))`,
                    }"
                >
                    {{ maskFor(impostorPlayer.id) }}
                </div>
                <div class="text-left">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--mq-coral)]">
                        Impostor
                    </p>
                    <p class="font-display text-lg leading-none text-white">{{ impostorPlayer.name }}</p>
                </div>
            </div>

            <div class="mt-4">
                <div class="mq-chip p-3 text-left">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/50">The word was</p>
                    <p class="font-display mt-1 text-base text-[var(--mq-mint)]">{{ reveal?.real_word }}</p>
                </div>
            </div>
        </section>

        <button
            v-if="isHost"
            type="button"
            class="mq-btn mq-btn-primary mq-pulse-glow w-full !py-5 text-lg"
            @click="nextRound"
        >
            <Forward class="size-5" />
            Start round {{ room.round_number + 1 }}
        </button>
        <div v-else class="mq-card flex items-center justify-center gap-3 p-5">
            <span class="text-2xl">🎭</span>
            <div>
                <p class="font-display text-base text-white">Waiting for the host…</p>
                <span class="mq-dots mt-1"><span /><span /><span /></span>
            </div>
        </div>
    </template>

    <!-- ============== FINISHED (final scoreboard) ============== -->
    <template v-else-if="room.status === 'finished' || room.status === 'ended'">
        <!-- Confetti rain -->
        <div v-if="result === 'caught'" class="mq-confetti" aria-hidden="true">
            <span
                v-for="(c, i) in confettiPieces"
                :key="i"
                :style="{
                    left: c.left,
                    animationDelay: c.delay,
                    animationDuration: c.duration,
                    '--mq-confetti-drift': c.drift,
                } as Record<string, string>"
            >{{ c.emoji }}</span>
        </div>

        <!-- Final round result -->
        <section class="mq-card mq-bounce-in mb-5 overflow-hidden p-5 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/50">
                Final round
            </p>
            <div class="mt-1 text-4xl">{{ resultMeta.emoji }}</div>
            <h3
                class="font-display mt-1 text-xl"
                :style="{
                    color: resultMeta.tone === 'mint' ? 'var(--mq-mint)'
                         : resultMeta.tone === 'coral' ? 'var(--mq-coral)'
                         : 'var(--mq-gold)'
                }"
            >
                {{ resultMeta.title }}
            </h3>
            <div class="mt-3 text-left">
                <div class="mq-chip p-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/50">The word was</p>
                    <p class="font-display mt-1 text-sm text-[var(--mq-mint)]">{{ reveal?.real_word }}</p>
                </div>
            </div>
        </section>

        <!-- Game complete hero -->
        <section class="mq-card mq-bounce-in mb-5 p-6 text-center">
            <Trophy class="mx-auto size-10 text-[var(--mq-gold)]" />
            <p class="mt-2 text-xs font-bold uppercase tracking-[0.2em] text-white/50">
                {{ scoreboard?.rounds.length ?? room.total_rounds }} rounds played
            </p>
            <h2 class="font-display mt-1 text-3xl mq-shimmer-text">Game Complete</h2>
        </section>

        <!-- Best Impostor (hero card) -->
        <section
            v-if="scoreboard?.best_impostor"
            class="mq-card mq-bounce-in mb-3 overflow-hidden p-5 text-center"
            style="background: linear-gradient(180deg, hsl(326 60% 25%), hsl(286 60% 22%)); border: 1px solid hsl(326 100% 62% / 0.5);"
        >
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[var(--mq-magenta)]">
                🎭 Best Impostor
            </p>
            <div
                class="mx-auto mt-3 flex size-16 items-center justify-center rounded-full text-3xl"
                :style="{
                    background: `linear-gradient(135deg, hsl(${hueFor(scoreboard.best_impostor.id)} 80% 55%), hsl(${(hueFor(scoreboard.best_impostor.id) + 30) % 360} 80% 45%))`,
                }"
            >
                {{ maskFor(scoreboard.best_impostor.id) }}
            </div>
            <p class="font-display mt-3 text-2xl text-white">{{ scoreboard.best_impostor.name }}</p>
            <p class="mt-1 text-sm font-semibold text-white/80">
                {{ scoreboard.best_impostor.successful_bluffs }} of
                {{ scoreboard.best_impostor.times_as_impostor }} bluff{{ scoreboard.best_impostor.times_as_impostor === 1 ? '' : 's' }}
                successful
            </p>
        </section>

        <!-- Best Detective + Most Suspicious -->
        <div class="mb-5 grid grid-cols-2 gap-3">
            <section
                v-if="scoreboard?.best_detective"
                class="mq-card p-4 text-center"
            >
                <Search class="mx-auto size-6 text-[var(--mq-cyan)]" />
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-[var(--mq-cyan)]">
                    Best Detective
                </p>
                <div
                    class="mx-auto mt-2 flex size-10 items-center justify-center rounded-full text-xl"
                    :style="{
                        background: `linear-gradient(135deg, hsl(${hueFor(scoreboard.best_detective.id)} 80% 55%), hsl(${(hueFor(scoreboard.best_detective.id) + 30) % 360} 80% 45%))`,
                    }"
                >
                    {{ maskFor(scoreboard.best_detective.id) }}
                </div>
                <p class="font-display mt-1 text-base text-white">{{ scoreboard.best_detective.name }}</p>
                <p class="text-xs text-white/60">
                    {{ scoreboard.best_detective.correct_votes }} correct vote{{ scoreboard.best_detective.correct_votes === 1 ? '' : 's' }}
                </p>
            </section>

            <section
                v-if="scoreboard?.most_suspicious"
                class="mq-card p-4 text-center"
            >
                <span class="block text-2xl leading-none">🚨</span>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-[var(--mq-coral)]">
                    Most Suspicious
                </p>
                <div
                    class="mx-auto mt-2 flex size-10 items-center justify-center rounded-full text-xl"
                    :style="{
                        background: `linear-gradient(135deg, hsl(${hueFor(scoreboard.most_suspicious.id)} 80% 55%), hsl(${(hueFor(scoreboard.most_suspicious.id) + 30) % 360} 80% 45%))`,
                    }"
                >
                    {{ maskFor(scoreboard.most_suspicious.id) }}
                </div>
                <p class="font-display mt-1 text-base text-white">{{ scoreboard.most_suspicious.name }}</p>
                <p class="text-xs text-white/60">
                    {{ scoreboard.most_suspicious.votes_received_as_innocent }} wrong accusation{{ scoreboard.most_suspicious.votes_received_as_innocent === 1 ? '' : 's' }}
                </p>
            </section>
        </div>

        <!-- Round-by-round table -->
        <section v-if="scoreboard?.rounds.length" class="mq-card mb-5 p-5">
            <h3 class="font-display mb-3 text-xl text-white">Every round</h3>
            <ul class="flex flex-col gap-2">
                <li
                    v-for="r in scoreboard.rounds"
                    :key="r.round"
                    class="mq-chip flex items-center gap-3 px-3 py-2.5"
                >
                    <span class="font-display flex size-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-sm">
                        {{ r.round }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-extrabold text-white">
                            {{ r.real_word }}
                        </p>
                        <p class="text-[11px] text-white/60">
                            {{ r.category }} ·
                            <span v-if="r.impostor">Impostor: {{ r.impostor.name }}</span>
                        </p>
                    </div>
                    <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                        :class="{
                            'bg-[var(--mq-mint)]/20 text-[var(--mq-mint)]': r.outcome === 'caught',
                            'bg-[var(--mq-coral)]/20 text-[var(--mq-coral)]': r.outcome === 'escaped',
                            'bg-[var(--mq-gold)]/20 text-[var(--mq-gold)]': r.outcome === 'tie',
                        }"
                    >
                        {{ r.outcome }}
                    </span>
                </li>
            </ul>
        </section>

        <Link href="/impostor" class="mq-btn mq-btn-primary mq-pulse-glow w-full !py-5 text-lg">
            <PartyPopper class="size-5" /> Back to lobby
        </Link>
    </template>
    </GameLayout>
</template>
