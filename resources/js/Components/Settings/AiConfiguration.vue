<template>
  <div class="ai-settings">
    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="section">
        <h3>Content Generation Settings</h3>
        
        <div class="form-group">
          <label>Default Content Style</label>
          <select v-model="settings.contentStyle">
            <option value="professional">Professional</option>
            <option value="casual">Casual</option>
            <option value="friendly">Friendly</option>
          </select>
        </div>

        <div class="form-group">
          <label>Brand Voice</label>
          <textarea 
            v-model="settings.brandVoice"
            placeholder="Describe your brand's voice and tone..."
            rows="4"
          ></textarea>
        </div>

        <div class="form-group">
          <label>Content Topics</label>
          <vue-tags-input
            v-model="tag"
            :tags="settings.contentTopics"
            @tags-changed="newTags => settings.contentTopics = newTags"
            placeholder="Add topics"
          />
        </div>
      </div>

      <div class="section">
        <h3>Image Generation</h3>
        
        <div class="form-group">
          <label>Image Style</label>
          <select v-model="settings.imageStyle">
            <option value="corporate">Corporate</option>
            <option value="creative">Creative</option>
            <option value="minimal">Minimal</option>
          </select>
        </div>

        <div class="form-group">
          <label>Color Palette</label>
          <color-picker v-model="settings.colorPalette" />
        </div>
      </div>

      <div class="section">
        <h3>Posting Schedule</h3>
        
        <div class="form-group">
          <label>Optimal Times</label>
          <time-selector v-model="settings.postingTimes" />
        </div>

        <div class="form-group">
          <label>Post Frequency</label>
          <select v-model="settings.postFrequency">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="custom">Custom</option>
          </select>
        </div>
      </div>

      <button type="submit" :disabled="saving">
        {{ saving ? 'Saving...' : 'Save Settings' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VueTagsInput from '@johmun/vue-tags-input'
import ColorPicker from '../UI/ColorPicker.vue'
import TimeSelector from '../UI/TimeSelector.vue'
import { useSettings } from '@/Composables/useSettings'

const { fetchSettings, updateSettings } = useSettings()
const settings = ref({
  contentStyle: 'professional',
  brandVoice: '',
  contentTopics: [],
  imageStyle: 'corporate',
  colorPalette: [],
  postingTimes: [],
  postFrequency: 'daily'
})
const tag = ref('')
const saving = ref(false)

onMounted(async () => {
  const savedSettings = await fetchSettings()
  settings.value = { ...settings.value, ...savedSettings }
})

async function saveSettings() {
  saving.value = true
  try {
    await updateSettings(settings.value)
    // Show success notification
  } catch (error) {
    // Show error notification
  } finally {
    saving.value = false
  }
}
</script> 