<template>
  <div class="content-generator">
    <div class="settings-panel">
      <h3>Content Generation Settings</h3>
      <form @submit.prevent="generateContent">
        <div class="form-group">
          <label>Website URL</label>
          <input v-model="settings.url" type="url" required>
        </div>
        
        <div class="form-group">
          <label>Content Style</label>
          <select v-model="settings.style">
            <option value="professional">Professional</option>
            <option value="casual">Casual</option>
            <option value="friendly">Friendly</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Emoji Preference</label>
          <select v-model="settings.emojiPreference">
            <option value="none">None</option>
            <option value="fewer">Fewer</option>
            <option value="more">More</option>
          </select>
        </div>
        
        <button type="submit" :disabled="generating">
          {{ generating ? 'Generating...' : 'Generate Content' }}
        </button>
      </form>
    </div>

    <div class="preview-panel" v-if="generatedContent">
      <h3>Generated Content</h3>
      <div v-for="post in generatedContent" :key="post.id" class="post-preview">
        <div class="post-content">{{ post.content }}</div>
        <img v-if="post.image" :src="post.image" alt="Post image">
        <div class="actions">
          <button @click="schedulePost(post)">Schedule</button>
          <button @click="editPost(post)">Edit</button>
          <button @click="deletePost(post)">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useContentGeneration } from '@/Composables/useContentGeneration'

const settings = reactive({
  url: '',
  style: 'professional',
  emojiPreference: 'fewer'
})

const generating = ref(false)
const generatedContent = ref([])

const { generatePosts, schedulePost, editPost, deletePost } = useContentGeneration()

async function generateContent() {
  generating.value = true
  try {
    generatedContent.value = await generatePosts(settings)
  } catch (error) {
    console.error('Failed to generate content:', error)
  } finally {
    generating.value = false
  }
}
</script> 