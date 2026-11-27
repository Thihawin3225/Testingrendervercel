<template>
  <main class="auth-layout">
    <slot />
  </main>
</template>

<style scoped>
.auth-layout {
  display: grid;
  min-height: 100vh;
  min-height: 100dvh;
  place-items: center;
  padding: 40px 24px;
  background:
    radial-gradient(ellipse at 12% 15%, #ece4ff 0, transparent 34%),
    radial-gradient(ellipse at 88% 85%, #e8e8ff 0, transparent 35%),
    var(--page-background);
}

@media (max-width: 700px) {
  .auth-layout {
    padding: 16px 12px;
  }
}
</style>
