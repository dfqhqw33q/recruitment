import './bootstrap';
import { createApp } from 'vue';
import PipelineKanbanWidget from './components/PipelineKanbanWidget.vue';
import ApplicantProgressTracker from './components/ApplicantProgressTracker.vue';

const app = createApp({});

app.component('pipeline-kanban-widget', PipelineKanbanWidget);
app.component('applicant-progress-tracker', ApplicantProgressTracker);

// Mount Vue if container exists
if (document.getElementById('vue-app') || document.getElementById('app')) {
    app.mount('#vue-app, #app');
}
