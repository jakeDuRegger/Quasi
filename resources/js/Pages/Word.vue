<script setup>
import {Head} from '@inertiajs/vue3'
import {
    PhArrowsCounterClockwise,
    PhCaretDown,
    PhMinus,
    PhPlus,
    PhThumbsDown,
    PhThumbsUp, PhTrash,
    PhX
} from "@phosphor-icons/vue";
import {computed, reactive, ref} from "vue";

const props = defineProps(['word', 'parsedDefinitions', 'categories', 'favorited', 'favorites']);

const word = ref(props.word);
const parsedDefinitions = ref(props.parsedDefinitions);
const categories = ref(props.categories);
const favorites = ref(props.favorites);
const favorited = ref(props.favorited);


const vote_count = computed(() => props.word.vote_count);
const voteState = reactive({
    upvoted: false,
    downvoted: false
});

const refresh = async () => {
    // send in the current id to avoid it on refresh...
    // todo make an achievement to get the same word twice in a row perhaps!
    try {
        const response = await axios.get('/');

        word.value = response.data.word;
        parsedDefinitions.value = response.data.parsedDefinitions;
        categories.value = response.data.categories;
        favorites.value = response.data.favorites;
        favorited.value = response.data.favorited;

    } catch (error) {
        console.error('Error refreshing word:', error);
        alert('Failed to refresh word. Please try again.');
    }

};
const addFavorite = async (wordId) => {
    try {
        const response = await axios.post(`/favorites/${wordId}/add`);
        favorites.value = response.data.favorites;
        favorited.value = true;
    } catch (error) {
        console.error('Error adding favorite:', error);
        alert('Failed to add favorite. Please try again.');
    }
};

const removeFavorite = async (wordId) => {
    try {
        const response = await axios.delete(`/favorites/${wordId}/remove`);
        favorites.value = response.data.favorites;
        favorited.value = false;
    } catch (error) {
        console.error('Error removing favorite:', error);
        alert('Failed to remove favorite. Please try again.');
    }
};

const clearFavorites = async () => {
    try {
        const response = await axios.delete(`/favorites/remove`);
        favorites.value = response.data.favorites;
        favorited.value = false;
    } catch (error) {
        console.error('Error removing all favorites:', error);
        alert('Failed to remove favorites. Please try again.');
    }
};

const vote = async (wordId, voteValue) => {
    try {
        // Check if the current vote is the same as the previous vote
        if (voteState.currentVote === voteValue) {
            // Reset the vote
            voteState.upvoted = false;
            voteState.downvoted = false;
            voteState.currentVote = null;
        } else {
            // Update the vote state based on the new vote
            voteState.upvoted = voteValue === 1;
            voteState.downvoted = voteValue === 0;
            voteState.currentVote = voteValue;
        }
        const response = await axios.post(`/vote/${wordId}/${voteValue}`, {
            'vote': voteValue,
        });

        if (response.data.vote_count != null) {
            props.word.vote_count = response.data.vote_count;
        }
    } catch (error) {
        console.error('Error adding vote:', error);
        alert('Failed to vote. Please try again.');
    }
};
</script>

<template>
    <Head :title="`quasi | ${word.name}`"/>

    <div class="flex flex-col min-h-dvh selection:bg-amber-200">
        <div class="container mx-auto max-w-screen-lg px-16 lg:px-1">
            <header class="flex flex-row items-center justify-between border-b-2 border-b-amber-400 mt-8">
                <h1 class="text-4xl font-bold italic mb-4">
                    quasi
                </h1>
                <div class="flex flex-row items-center gap-4">

                    <!-- Upvote Button -->
                    <button v-if="vote" @click="vote(word.id, 1)"
                            class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                        <ph-thumbs-up v-if="voteState.upvoted" weight="fill" class="text-2xl text-primary-600"/>
                        <ph-thumbs-up v-else weight="duotone" class="text-2xl text-primary-600"/>
                        <span
                            class="absolute z-50 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity">
                        Seen this before
                    </span>
                    </button>

                    <!-- Downvote Button -->
                    <button v-if="vote" @click="vote(word.id, 0)"
                            class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                        <ph-thumbs-down v-if="voteState.downvoted" weight="fill" class="text-2xl text-primary-600"/>
                        <ph-thumbs-down v-else weight="duotone" class="text-2xl text-primary-600"/>
                        <span
                            class="absolute z-50 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity">
                        Never seen before
                    </span>
                    </button>

                    <button v-if="!favorited" @click="addFavorite(word.id)"
                            class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                        <ph-plus :size="24"/>
                        <span
                            class="absolute z-50 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity"
                        >
                        Add to favorites
                    </span>
                    </button>
                    <button v-else @click="removeFavorite(word.id)"
                            class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                        <ph-minus :size="24"/>
                        <span
                            class="absolute z-50 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity"
                        >
                        Remove from favorites
                    </span>
                    </button>
                    <button @click="refresh"
                            class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                        <ph-arrows-counter-clockwise :size="24"/>
                        <span
                            class="absolute z-50 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity"
                        >
                        Refresh word
                    </span>
                    </button>
                </div>
            </header>
            <main class="mb-8">
                <article class="my-8 grid grid-cols-1 place-items-baseline justify-items-center">
                    <!-- Word -->
                    <section class="flex flex-col justify-center text-center sticky top-0 bg-white w-full z-10 p-4">
                        <h2 class="text-3xl font-bold">{{ word.name }}
                        </h2>
                        <small class="text-lg text-gray-500">{{ word.ipa_pronunciation }}</small>
                        <p class="text-sm text-gray-500">Frequency: {{ word.frequency }}</p>
                    </section>
                    <!-- Definitions -->
                    <ul class="flex flex-col gap-y-4 mt-6 mb-8 list-decimal list-inside">
                        <li
                            v-for="(parsed, index) in parsedDefinitions"
                            :key="index"
                            class="shadow-sm shadow-gray-500 max-w-prose font-semibold p-6 rounded grid"
                        >
                            <p class="relative">
                                <span class="text-gray-700">
                                    {{ parsed.definition.charAt(0).toLowerCase() + parsed.definition.slice(1) }}
                                </span>
                                <span v-if="parsed.referencedWord" class="group">
                                <span class="text-amber-400 font-bold cursor-pointer">{{
                                        ' ' + parsed.referencedWord
                                    }}</span>
                                <span
                                    class="absolute z-50 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity"
                                >
                                    {{ parsed.referencedDefinition || "No additional info" }}
                                </span>
                            </span>
                            </p>
                            <small
                                v-if="parsed.small && parsed.small.length > 0 && parsed.pos_string && parsed.pos_string.length > 0"
                                class="text-gray-600 italic block justify-self-end"
                            >
                                {{ parsed.pos_string }} {{ parsed.small }}
                            </small>
                            <small
                                v-else-if="parsed.small && (!parsed.pos_string || parsed.pos_string.length === 0)"
                                class="text-gray-600 italic block justify-self-end"
                            >
                                {{ parsed.small }}
                            </small>
                            <small
                                v-else-if="parsed.pos_string && (!parsed.small || parsed.small.length === 0)"
                                class="text-gray-600 italic block justify-self-end"
                            >
                                {{ parsed.pos_string }}
                            </small>

                        </li>
                    </ul>
                    <!-- More Info -->
                    <section class="flex flex-col justify-self-start my-8">
                        <dl class="max-w-prose grid gap-6 text-wrap">
                            <!-- Reusable logic for each section -->
                            <template v-for="(info, title) in categories" :key="title">
                                <!-- Show section only if data is present -->
                                <dt v-if="info.data && info.data.length > 0" class="pb-1 border-b border-b-amber-400">
                                    {{ title }} (e.g., {{ info.example }})
                                </dt>
                                <dd v-if="info.data && info.data.length > 0"
                                    class="p-6 text-gray-600 shadow-sm shadow-gray-500 rounded">
                                    {{ info.data.join(', ') }}
                                </dd>
                            </template>
                        </dl>
                    </section>
                </article>
            </main>
        </div>
        <!-- TODO Animate this -->
        <footer v-if="favorites.length > 0" class="my-8 sticky bottom-0 bg-white w-full z-10 px-16 py-8">
            <!-- Favorites list -->
            <section class="border-b-2 border-amber-400">
                <header class="flex flex-row justify-between itesm-center">
                    <h1 class="text-2xl font-bold italic mb-4">
                        favorites
                    </h1>
                    <div class="flex flex-row items-center">
                        <button @click="clearFavorites"
                                class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                            <ph-trash :size="24"/>
                        </button>
                        <button @click="collapse"
                                class="outline-amber-400 relative group hover:bg-amber-50 p-1 rounded-xl">
                            <ph-caret-down :size="24"/>
                        </button>
                    </div>

                </header>

                <div class="overflow-x-auto scrollbar-hidden">
                    <ul class="flex flex-row flex-nowrap gap-8">
                        <li v-for="favorite in favorites" :key="favorite.id"
                            class="flex-shrink-0 w-40 bg-white z-10 p-4 text-center">
                            <p class="text-lg font-bold" :class="{ 'bg-amber-400 rounded-lg': favorite.id === word.id}">{{ favorite.name }}</p>
                            <small class="text-sm text-gray-500">Frequency: {{ favorite.frequency }}</small>
                        </li>
                    </ul>
                </div>
            </section>
        </footer>
    </div>
</template>

<style scoped>
</style>
