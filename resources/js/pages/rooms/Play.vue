<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    room: { id: number; code: string };
    player: { nickname: string };
}>();
</script>

<template>
    <Head :title="`Room ${room.code}`" />

    <main
        class="flex min-h-screen flex-col items-center justify-center gap-4 bg-background p-6 text-center"
    >
        <p class="text-3xl font-bold">You're in, {{ player.nickname }}!</p>
        <p class="text-lg text-muted-foreground">
            Waiting for the host to start…
        </p>
    </main>
</template>
