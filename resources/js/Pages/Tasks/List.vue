<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TaskStatusDropdown from '@/Components/TaskStatusDropdown.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    tasks: {
        type: Array,
        default: () => []
    }
});

const getTaskPath = (task) => {
    const path = [];
    let currentTask = task;
    
    while (currentTask?.parent) {
        path.unshift(currentTask.parent);
        currentTask = currentTask.parent;
    }
    
    return path;
};

const form = useForm({});
const sortBy = ref('date');
const showCompleted = ref(false);
const priorityMap = {
  high: 3,
  medium: 2,
  low: 1
};

const sortedTasks = computed(() => {
    let filteredTasks = [...props.tasks];
    
    if (!showCompleted.value) {
        filteredTasks = filteredTasks.filter(task => !task.completed);
    }
    
    if (sortBy.value === 'date') {
        return filteredTasks.sort((a, b) => 
            new Date(b.created_at) - new Date(a.created_at)
        );
    } else if (sortBy.value === 'priority') {
        return filteredTasks.sort((a, b) => {
            const priorityA = priorityMap[a.priority] || 0;
            const priorityB = priorityMap[b.priority] || 0;
            
            if (priorityA !== priorityB) {
                return priorityB - priorityA;
            }
            
            return new Date(b.created_at) - new Date(a.created_at);
        });
    }
    
    return filteredTasks;
});

const toggleTaskStatus = async (taskId) => {
    form.put(route('tasks.toggle-status', taskId));
};

const getLabels = (task) => {
    return typeof task.labels === 'string' ? task.labels.split(',').filter(label => label) : task.labels || [];
};

const deleteTask = (taskId) => {
    if (!confirm('Are you sure you want to delete this task?')) return;
    form.delete(route('tasks.destroy', taskId));
};
</script>

<template>
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-lg font-medium text-gray-700">Tasks List</h1>
                    <Link
                        :href="route('tasks.create')"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Create Task
                    </Link>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between bg-white p-4 rounded-lg shadow-sm space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm text-gray-700">Sort by:</label>
                        <select
                            v-model="sortBy"
                            class="text-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="date">Creation Date</option>
                            <option value="priority">Priority</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            id="showCompleted"
                            v-model="showCompleted"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                        <label 
                            for="showCompleted" 
                            class="text-sm text-gray-700 cursor-pointer"
                        >
                            View all tasks (including completed)
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <template v-if="sortedTasks.length">
                    <div 
                        v-for="task in sortedTasks" 
                        :key="task.id"
                        class="bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div v-if="task.parent" class="flex items-center text-sm text-gray-500">
                                        <template v-for="(parentTask, index) in getTaskPath(task)" :key="parentTask.id">
                                            <h2 class="text-sm font-medium text-gray-600">
                                                {{ parentTask.title }}
                                            </h2>
                                            <svg 
                                                class="h-5 w-5 mx-1" 
                                                fill="currentColor" 
                                                viewBox="0 0 20 20"
                                            >
                                                <path 
                                                    fill-rule="evenodd" 
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" 
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </template>
                                    </div>
                                    <h2 class="text-sm font-medium text-gray-900" :class="{ 'line-through': task.completed }">
                                        {{ task.title }}
                                    </h2>
                                </div>

                                <div class="mt-2 flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-md" 
                                        :class="{
                                            'bg-red-100 text-red-800': task.priority === 'high',
                                            'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                                            'bg-gray-100 text-gray-800': task.priority === 'low'
                                        }"
                                    >
                                        {{ task.priority }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Created {{ task.created_at }}
                                    </span>
                                </div>

                                <p v-if="task.description" class="mt-1 text-sm text-gray-600">
                                    {{ task.description }}
                                </p>

                                <div v-if="task.labels && task.labels.length && task.labels[0] !== ''" class="mt-2 flex items-center gap-2">
                                    <span 
                                        v-for="label in getLabels(task)" 
                                        :key="label"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-sm bg-indigo-100 text-indigo-800"
                                    >
                                        {{ label.trim() }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <TaskStatusDropdown 
                                        :task="task" 
                                        @update-status="toggleTaskStatus"
                                    />
                                    <Link
                                        :href="route('tasks.edit', task.id)"
                                        class="text-sm px-2 py-1 rounded-md transition-colors duration-200 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        title="Edit this task"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteTask(task.id)"
                                        class="text-sm px-2 py-1 rounded-md transition-colors duration-200 bg-red-600 text-white hover:bg-red-700"
                                        title="Delete this task"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div v-else class="bg-white rounded-lg shadow-sm p-6 text-center text-gray-500">
                    No active tasks found. Click 'Create Task' to add one.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>