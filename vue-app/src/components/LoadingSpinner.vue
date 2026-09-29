<template>
  <div class="roadmap-spinner">
    <div class="roadmap-spinner-circle"></div>
  </div>
</template>

<style lang="scss" scoped>
.roadmap-spinner {
  display: inline-block;
  width: 40px;
  height: 40px;
  
  &-circle {
    width: 100%;
    height: 100%;
    border: 3px solid var(--rm-border);
    border-top: 3px solid var(--roadmap-accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>