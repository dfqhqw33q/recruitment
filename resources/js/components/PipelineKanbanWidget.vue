<template>
  <div class="glass-card rounded-2xl p-6 shadow-xl transition-all duration-300 border border-white/40">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-600 animate-ping"></span>
          Recruitment Pipeline Summary
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Real-time candidate movement across stages</p>
      </div>
      <div class="flex gap-1.5 bg-slate-100 dark:bg-slate-800/60 p-1 rounded-xl">
        <button 
          v-for="filter in ['All', 'Active', 'Hired']" 
          :key="filter"
          @click="activeFilter = filter"
          :class="[
            'px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-200',
            activeFilter === filter 
              ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' 
              : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
          ]"
        >
          {{ filter }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
      <div 
        v-for="stage in stages" 
        :key="stage.name" 
        class="bg-white/60 dark:bg-slate-900/60 rounded-xl p-3.5 border border-slate-100 dark:border-slate-800/80 hover:border-indigo-300 dark:hover:border-indigo-500/40 transition-all duration-200 group"
      >
        <div class="flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">
          <span>{{ stage.name }}</span>
          <span :class="['w-2 h-2 rounded-full', stage.dotClass]"></span>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-white group-hover:scale-105 transition-transform duration-200 origin-left">
          {{ stage.count }}
        </div>
        <div class="mt-2 w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
          <div 
            class="h-full rounded-full transition-all duration-500" 
            :class="stage.barClass"
            :style="{ width: Math.min(100, (stage.count / maxCount) * 100) + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  appliedCount: { type: Number, default: 12 },
  screeningCount: { type: Number, default: 5 },
  interviewCount: { type: Number, default: 4 },
  offerCount: { type: Number, default: 2 },
  hiredCount: { type: Number, default: 3 }
})

const activeFilter = ref('All')

const stages = computed(() => [
  { name: 'Applied', count: props.appliedCount, dotClass: 'bg-blue-500', barClass: 'bg-blue-500' },
  { name: 'Screening', count: props.screeningCount, dotClass: 'bg-amber-500', barClass: 'bg-amber-500' },
  { name: 'Interview', count: props.interviewCount, dotClass: 'bg-indigo-500', barClass: 'bg-indigo-500' },
  { name: 'Offer', count: props.offerCount, dotClass: 'bg-purple-500', barClass: 'bg-purple-500' },
  { name: 'Hired', count: props.hiredCount, dotClass: 'bg-emerald-500', barClass: 'bg-emerald-500' }
])

const maxCount = computed(() => {
  const counts = stages.value.map(s => s.count)
  return Math.max(...counts, 1)
})
</script>
