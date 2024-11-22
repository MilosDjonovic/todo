<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TagSelect from '@/Components/TagSelect.vue';
import NestedTaskSelector from '@/Components/NestedTaskSelector.vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    task: {
        type: Object,
        required: true
    },
    tasks: {
        type: Array,
        default: () => []
    }
});

const initialLabels = typeof props.task.labels === 'string' 
    ? props.task.labels.split(',').filter(label => label) 
    : props.task.labels || [];

const form = useForm({
    title: props.task.title,
    description: props.task.description,
    priority: props.task.priority,
    completed: props.task.completed,
    labels: initialLabels,
    parent_id: props.task.parent_id,
});

const availableLabels = ['personal', 'work', 'shopping'];
</script>

<template>
    <Head title="Edit Task" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-4">
                <a
                    :href="route('tasks.index')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Back to Task list
                </a>
            </div>
            <form @submit.prevent="form.patch(route('tasks.update', task.id))">
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Task title
                        </label>
                        <input
                            id="title"
                            type="text"
                            v-model="form.title"
                            maxlength="255"
                            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        />
                        <InputError :message="form.errors.title" class="mt-2" />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Task Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        ></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                            Priority Level
                        </label>
                        <select 
                            id="priority"
                            v-model="form.priority" 
                            class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <InputError :message="form.errors.priority" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <label class="inline-flex items-center">
                            <input 
                                type="checkbox" 
                                v-model="form.completed"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-600">Mark as completed</span>
                        </label>
                        <InputError :message="form.errors.completed" class="mt-2" />
                    </div>

                    <div>
                        <label for="labels" class="block text-sm font-medium text-gray-700 mb-1">
                            Labels
                        </label>
                        <TagSelect
                            id="labels"
                            v-model="form.labels"
                            :options="availableLabels"
                            placeholder="Select labels..."
                            class="block w-full"
                        />
                        <InputError :message="form.errors.labels" class="mt-2" />
                    </div>

                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent Task
                        </label>
                        <NestedTaskSelector 
                        v-model="form.parent_id"
                        :tasks="currentTask ? tasks.filter(t => t.id !== currentTask.id) : tasks"
                        :error="form.errors.parent_id"
                        />
                    </div>

                    <div>
                        <PrimaryButton type="submit">Update Task</PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>