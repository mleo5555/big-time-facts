<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { join } from '@/routes';

defineProps<{
    room: { id: number; code: string };
}>();

// Shown on the TV so players know where to go. The full URL, since they type it on their phones.
const joinUrl = window.location.host + join().url; // e.g. "big-time-facts.test/join"
</script>

<template>
    <Head :title="`Room ${room.code}`" />

    <main
        class="flex min-h-screen flex-col items-center justify-center gap-8 bg-background p-8 text-center"
    >
        <p class="text-2xl text-muted-foreground">
            Join at
            <span class="font-semibold text-foreground">{{ joinUrl }}</span>
        </p>

        <p class="text-9xl font-bold tracking-[0.2em]">{{ room.code }}</p>

        <!-- Step E: the live player list from the presence channel goes here. -->
        <p class="text-xl text-muted-foreground">Waiting for players…</p>
    </main>
</template>
