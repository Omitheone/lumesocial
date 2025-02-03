<template>
  <div class="analytics-dashboard">
    <div class="metrics-overview">
      <div v-for="metric in topMetrics" :key="metric.id" class="metric-card">
        <h4>{{ metric.label }}</h4>
        <div class="metric-value">{{ metric.value }}</div>
        <div class="metric-trend" :class="metric.trend">
          {{ metric.percentage }}%
        </div>
      </div>
    </div>

    <div class="charts-section">
      <div class="engagement-chart">
        <h3>Engagement Over Time</h3>
        <line-chart :data="engagementData" :options="chartOptions" />
      </div>
      
      <div class="performance-breakdown">
        <h3>Content Performance</h3>
        <bar-chart :data="performanceData" :options="chartOptions" />
      </div>
    </div>

    <div class="insights-panel">
      <h3>AI-Generated Insights</h3>
      <div v-for="insight in insights" :key="insight.id" class="insight-card">
        <div class="insight-icon" :class="insight.type"></div>
        <div class="insight-content">
          <h4>{{ insight.title }}</h4>
          <p>{{ insight.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Line as LineChart, Bar as BarChart } from 'vue-chartjs'
import { useAnalytics } from '@/Composables/useAnalytics'

const { fetchMetrics, fetchInsights } = useAnalytics()

const topMetrics = ref([])
const insights = ref([])
const engagementData = ref({})
const performanceData = ref({})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  // ... more chart configuration
}

onMounted(async () => {
  await loadAnalyticsData()
})

async function loadAnalyticsData() {
  const [metrics, analyticsInsights] = await Promise.all([
    fetchMetrics(),
    fetchInsights()
  ])
  
  topMetrics.value = metrics.top
  insights.value = analyticsInsights
  engagementData.value = metrics.engagement
  performanceData.value = metrics.performance
}
</script> 