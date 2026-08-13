<template>
  <div class="glass-card rounded-2xl p-6 shadow-xl border border-white/40">
    <div class="flex items-center justify-between mb-4">
      <h4 class="font-bold text-slate-800 dark:text-white text-base">Application Progress Tracker</h4>
      <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold border border-indigo-200 dark:border-indigo-800">
        {{ currentStatus }}
      </span>
    </div>

    <div class="relative flex items-center justify-between mt-6">
      <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-slate-200 dark:bg-slate-800 w-full rounded-full z-0"></div>
      <div 
        class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all duration-700 rounded-full z-0"
        :style="{ width: progressPercentage + '%' }"
      ></div>

      <div 
        v-for="(step, index) in steps" 
        :key="step.key"
        class="relative z-10 flex flex-col items-center cursor-pointer group"
        @click="activeStep = index"
      >
        <div 
          :class="[
            'w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 shadow-md',
            index <= currentStepIndex
              ? 'bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white ring-4 ring-indigo-100 dark:ring-indigo-950 shadow-indigo-500/30'
              : 'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:bg-slate-300'
          ]"
        >
          <span v-if="index < currentStepIndex">✓</span>
          <span v-else>{{ index + 1 }}</span>
        </div>
        <span class="text-xs font-semibold mt-2 text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
          {{ step.label }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  status: { type: String, default: 'Submitted' }
})

const steps = [
  { key: 'Submitted', label: 'Submitted' },
  { key: 'Screening', label: 'Screening' },
  { key: 'Interview', label: 'Interview' },
  { key: 'Offered', label: 'Offered' },
  { key: 'Hired', label: 'Hired' }
]

const currentStatus = computed(() => props.status)

const currentStepIndex = computed(() => {
  const index = steps.findIndex(s => s.key.toLowerCase() === props.status.toLowerCase())
  return index !== -1 ? index : 0
})

const progressPercentage = computed(() => {
  if (steps.length <= 1) return 0
  return (currentStepIndex.value / (steps.length - 1)) * 100
})

const activeStep = ref(currentStepIndex.value)
</script>
