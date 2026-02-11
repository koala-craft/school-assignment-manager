<script setup lang="ts">
// Main app component
</script>

<template>
  <v-app class="app-modern">
    <v-main class="app-main">
      <router-view />
    </v-main>
  </v-app>
</template>

<style scoped>
.app-modern {
  background: #f1f5f9;
}
.app-main {
  min-height: 100vh;
}
</style>

