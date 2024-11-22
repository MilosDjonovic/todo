<template>
    <div class="relative inline-block">
        <!-- Status Display Button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="flex items-center gap-2 text-sm px-3 py-2 rounded-md transition-colors duration-200"
            :class="{
                'bg-green-100 text-green-800': task.completed,
                'bg-yellow-100 text-yellow-800': !task.completed
            }"
        >
            <span>{{ task.completed ? 'Completed' : 'In Progress' }}</span>
            <svg
                class="w-4 h-4"
                :class="{ 'transform rotate-180': isOpen }"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute z-10 mt-1 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
        >
            <div class="py-1" role="menu">
                <button
                    v-for="status in statuses"
                    :key="status.value"
                    @click="updateStatus(status.value)"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                    :class="{ 'font-medium': task.completed === status.value }"
                    role="menuitem"
                >
                    {{ status.label }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    task: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['update-status'])

const isOpen = ref(false)

const statuses = [
    { label: 'Completed', value: true },
    { label: 'In Progress', value: false }
]

const updateStatus = (value) => {
    emit('update-status', { id: props.task.id, completed: value })
    isOpen.value = false
}
</script>