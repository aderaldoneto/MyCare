<template>
  <footer class="login-footer">© 2026 AN Tecnologia</footer>
</template>

<style scoped>

.login-footer {
  background-color: #f8f9fc;
  position: fixed;
  z-index: 3;
  right: 0;
  bottom: 18px;
  left: 47.5%;
  color: #9aa4ba;
  font-size: 11px;
  text-align: center;
}

@media (max-width: 900px) {
  .login-footer {
    left: 40%;
  }
}

@media (max-width: 700px) {
  .login-footer {
    left: 0;
  }
}
</style>
