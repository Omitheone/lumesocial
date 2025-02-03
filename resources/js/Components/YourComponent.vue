<template>
  <emoji-picker @select="onSelectEmoji" />
</template>

<script setup>
import { EmojiPicker } from '@kyvg/vue3-emoji-picker';

const onSelectEmoji = (emoji) => {
  // Handle emoji selection
};
</script> 