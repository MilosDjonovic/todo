<template>
    <div>
      <select
        id="parent_id"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
      >
        <option value="">No parent task</option>
        <template v-for="task in hierarchicalTasks" :key="task.id">
          <option :value="task.id">{{ task.title }}</option>
          <template v-for="child in task.children" :key="child.id">
            <option :value="child.id">─› {{ child.title }}</option>
            <template v-for="grandChild in child.children" :key="grandChild.id">
              <option :value="grandChild.id">──› {{ grandChild.title }}</option>
            </template>
          </template>
        </template>
      </select>
      <InputError :message="error" class="mt-2" />
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  
  const props = defineProps({
    modelValue: {
      type: [String, Number],
      default: ''
    },
    tasks: {
      type: Array,
      default: () => []
    },
    error: {
      type: String,
      default: ''
    }
  });
  
  defineEmits(['update:modelValue']);
  
  const buildTaskHierarchy = (tasks) => {
    if (!tasks?.length) return [];
    
    const taskMap = new Map();
    const roots = [];
  
    tasks.forEach(task => {
      taskMap.set(task.id, { ...task, children: [] });
    });
  
    tasks.forEach(task => {
      const node = taskMap.get(task.id);
      if (task.parent_id && taskMap.has(task.parent_id)) {
        const parent = taskMap.get(task.parent_id);
        parent.children.push(node);
      } else {
        roots.push(node);
      }
    });
  
    return roots;
  };
  
  const hierarchicalTasks = computed(() => buildTaskHierarchy(props.tasks));
  </script>