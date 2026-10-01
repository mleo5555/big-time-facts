<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/join';

defineOptions({
    layout: {
        title: 'Join a game',
        description: 'Enter the room code shown on the big screen',
    },
});
</script>

<template>
    <Head title="Join a game" />

    <Form
        v-bind="store.form()"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="code">Room code</Label>
                <Input
                    id="code"
                    name="code"
                    required
                    v-focus
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                    placeholder="ABCD"
                    class="text-center text-2xl tracking-[0.5em] uppercase"
                />
                <InputError :message="errors.code" />
            </div>

            <div class="grid gap-2">
                <Label for="nickname">Nickname</Label>
                <Input
                    id="nickname"
                    name="nickname"
                    required
                    maxlength="20"
                    autocomplete="nickname"
                    placeholder="Your name on the big screen"
                />
                <InputError :message="errors.nickname" />
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Join
            </Button>
        </div>
    </Form>
</template>
