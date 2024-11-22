<!-- TagSelect.vue -->
<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Select tags...'
  }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const containerRef = ref(null);

const removeTag = (tag) => {
  const newValue = props.modelValue.filter(t => t !== tag);
  emit('update:modelValue', newValue);
};

const toggleTag = (tag) => {
  const newValue = props.modelValue.includes(tag)
    ? props.modelValue.filter(t => t !== tag)
    : [...props.modelValue, tag];
  emit('update:modelValue', newValue);
};

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

watch(isOpen, (newValue) => {
  if (newValue) {
    document.addEventListener('click', handleClickOutside);
  } else {
    document.removeEventListener('click', handleClickOutside);
  }
});
</script>

<template>
  <div ref="containerRef" class="relative">
    <div
      @click="isOpen = true"
      class="min-h-[38px] w-full p-1 border border-gray-300 rounded-md bg-white cursor-text flex flex-wrap gap-1 focus-within:ring focus-within:ring-indigo-200 focus-within:ring-opacity-50 focus-within:border-indigo-300"
    >
      <template v-if="modelValue.length">
        <span
          v-for="tag in modelValue"
          :key="tag"
          class="inline-flex items-center px-2 py-0.5 rounded text-sm bg-indigo-100 text-indigo-800"
        >
          {{ tag }}
          <button
            type="button"
            class="ml-1 text-indigo-600 hover:text-indigo-800"
            @click.stop="removeTag(tag)"
          >
            ×
          </button>
        </span>
      </template>
      <span v-else class="p-1.5 text-gray-500">{{ placeholder }}</span>
    </div>

    <div
      v-if="isOpen"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg"
    >
      <div
        v-for="option in options"
        :key="option"
        class="px-3 py-2 cursor-pointer hover:bg-gray-100"
      >
        <label class="flex items-center space-x-2 cursor-pointer w-full">
          <input
            type="checkbox"
            :checked="modelValue.includes(option)"
            @change="toggleTag(option)"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          >
          <span>{{ option }}</span>
        </label>
      </div>
    </div>
  </div>
</template>