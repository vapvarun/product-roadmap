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
    border: 3px solid #f3f3f3;
    border-top: 3px solid #2271b1;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.roadmap-dark .roadmap-spinner-circle {
  border-color: #50575e;
  border-top-color: #2271b1;
}
</style>