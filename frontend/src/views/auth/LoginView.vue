<script setup lang="ts">
import { GraduationCap, ShieldCheck } from '@lucide/vue'
import AuthLayout from '../../layouts/AuthLayout.vue'
import LoginForm from '../../modules/auth/components/LoginForm.vue'
</script>

<template>
  <AuthLayout>
    <section class="login-card" aria-labelledby="login-heading">
      <aside class="brand-panel">
        <div class="brand">
          <span class="brand-icon"><GraduationCap :size="27" aria-hidden="true" /></span>
          <span>CampusBridge</span>
        </div>

        <div class="brand-message">
          <p class="eyebrow">Campus life, connected</p>
          <h2>Find your community. Build your future.</h2>
          <p>
            Learn together, share what you know, and make the most of your campus experience.
          </p>
        </div>

        <div class="brand-footer">
          <ShieldCheck :size="18" aria-hidden="true" />
          <span>Made for your campus community</span>
        </div>
      </aside>

      <div class="login-content">
        <p class="eyebrow">Your campus community awaits</p>
        <h1 id="login-heading">Welcome back</h1>
        <p class="login-intro">Sign in with your university email to continue.</p>
        <LoginForm />
      </div>
    </section>
  </AuthLayout>
</template>

<style scoped>
.login-card {
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  width: min(100%, 1040px);
  min-height: 610px;
  overflow: hidden;
  border: 1px solid #e6e4f5;
  border-radius: 22px;
  background: #fff;
  box-shadow: 0 24px 80px #2d215b18;
}

.brand-panel {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-width: 0;
  padding: 38px;
  background:
    radial-gradient(circle at 85% 20%, #ffffff29 0 8%, transparent 8.3%),
    radial-gradient(ellipse at 100% 100%, #8f50f833 0, transparent 48%),
    linear-gradient(155deg, #201438, #151438 63%, #48108f);
  color: #fff;
}

.brand {
  display: inline-flex;
  align-items: center;
  align-self: flex-start;
  gap: 11px;
  color: #fff;
  font-size: 21px;
  font-weight: 800;
  letter-spacing: -0.5px;
  text-decoration: none;
}

.brand-icon {
  display: grid;
  width: 43px;
  height: 43px;
  place-items: center;
  border: 1px solid #ffffff35;
  border-radius: 13px;
  background: #ffffff19;
  color: #d7bdff;
}

.brand-message {
  max-width: 355px;
  padding: 44px 0;
}

.eyebrow {
  margin: 0 0 12px;
  color: #7a4adb;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.13em;
  text-transform: uppercase;
}

.brand-panel .eyebrow {
  color: #d3b9ff;
}

.brand-message h2 {
  margin: 0;
  font-size: clamp(29px, 3.4vw, 40px);
  font-weight: 750;
  letter-spacing: -1.2px;
  line-height: 1.15;
}

.brand-message > p:last-child {
  margin: 19px 0 0;
  color: #d5c7ef;
  font-size: 14px;
  line-height: 1.7;
}

.brand-footer {
  display: flex;
  align-items: center;
  gap: 9px;
  color: #e1d7f3;
  font-size: 12px;
}

.login-content {
  align-self: center;
  width: 100%;
  max-width: 510px;
  margin: 0 auto;
  padding: 62px clamp(30px, 6vw, 72px);
}

.login-content .eyebrow {
  margin-bottom: 10px;
}

.login-content h1 {
  margin: 0;
  font-size: clamp(28px, 3vw, 34px);
  font-weight: 750;
  letter-spacing: -1px;
}

.login-intro {
  margin: 9px 0 30px;
  color: var(--muted);
  font-size: 14px;
}

@media (max-width: 700px) {
  .login-card {
    grid-template-columns: minmax(0, 1fr);
    min-height: 0;
    border-radius: 18px;
  }

  .brand-panel {
    gap: 24px;
    padding: 22px 24px;
  }

  .brand {
    font-size: 18px;
  }

  .brand-icon {
    width: 38px;
    height: 38px;
  }

  .brand-message {
    padding: 0;
  }

  .brand-message .eyebrow {
    margin-bottom: 7px;
    font-size: 10px;
  }

  .brand-message h2 {
    max-width: 420px;
    font-size: 24px;
    letter-spacing: -0.6px;
  }

  .brand-message > p:last-child,
  .brand-footer {
    display: none;
  }

  .login-content {
    max-width: none;
    padding: 30px 24px 32px;
  }

  .login-intro {
    margin-bottom: 24px;
  }
}

@media (max-width: 350px) {
  .brand-panel {
    padding: 20px;
  }

  .login-content {
    padding-right: 20px;
    padding-left: 20px;
  }

  .login-content .eyebrow {
    font-size: 10px;
    letter-spacing: 0.1em;
  }
}
</style>
