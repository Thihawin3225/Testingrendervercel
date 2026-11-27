
<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import axios from 'axios'

import { Eye, EyeOff, Mail, LockKeyhole } from '@lucide/vue'

import BaseButton from '../../../components/ui/BaseButton.vue'
import { loginSchema } from '../schemas/loginSchema'
import { useLogin } from '../composables/useLogin'

// Vue Router
const router = useRouter()
const route = useRoute()

// Password Visibility
const showPassword = ref(false)

// Login Status Message
const authStatus = ref('')

// TanStack Query Login Mutation
const login = useLogin()

// VeeValidate + Zod
const { defineField, errors, handleSubmit, isSubmitting } = useForm({
  validationSchema: loginSchema,
  initialValues: {
    email: '',
    password: '',
  },
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

// Prevent Multiple Submissions
const isBusy = computed(() => {
  return isSubmitting.value || login.isPending.value
})

// Login Submission
const onSubmit = handleSubmit(async (values) => {
  authStatus.value = ''

  try {
    const response = await login.mutateAsync({
      email: values.email,
      password: values.password,
    })

    authStatus.value = 'Login successful! Redirecting...'

    // Redirect to the requested page if valid
    const redirect = route.query.redirect

    const destination =
      typeof redirect === 'string' &&
      redirect.startsWith('/') &&
      !redirect.startsWith('//')
        ? redirect
        : response.user.role === 'admin'
          ? '/admin/dashboard'
          : '/dashboard'

    await router.replace(destination)
  } catch (error: unknown) {
    console.error('Login Error:', error)

    if (axios.isAxiosError(error)) {
      console.error('Status:', error.response?.status)
      console.error('Response:', error.response?.data)

      const status = error.response?.status

      if (status === 401 || status === 403) {
        authStatus.value =
          'Unable to sign in. Check your credentials or account status.'
      } else if (status === 422) {
        authStatus.value = 'Please check your email and password.'
      } else {
        authStatus.value =
          error.response?.data?.message ??
          'Unable to connect to the server. Please try again.'
      }
    } else {
      authStatus.value = 'An unexpected error occurred.'
    }
  }
})
</script>

<template>
  <form class="login-form" novalidate @submit.prevent="onSubmit">
    <div class="form-field">
      <label for="login-email">University email</label>

      <div class="input-wrap" :class="{ 'input-wrap--invalid': errors.email }">
        <Mail class="field-icon" :size="18" aria-hidden="true" />

        <input
          id="login-email"
          v-model="email"
          v-bind="emailAttrs"
          type="email"
          name="email"
          autocomplete="email"
          autocapitalize="none"
          spellcheck="false"
          placeholder="you@university.edu"
          required
          :aria-invalid="Boolean(errors.email)"
          :aria-describedby="errors.email ? 'login-email-error' : undefined"
        />
      </div>

      <p
        v-if="errors.email"
        id="login-email-error"
        class="field-error"
        role="alert"
      >
        {{ errors.email }}
      </p>
    </div>

    <div class="form-field">
      <label for="login-password">Password</label>

      <div
        class="input-wrap"
        :class="{ 'input-wrap--invalid': errors.password }"
      >
        <LockKeyhole class="field-icon" :size="18" aria-hidden="true" />

        <input
          id="login-password"
          v-model="password"
          v-bind="passwordAttrs"
          :type="showPassword ? 'text' : 'password'"
          name="password"
          autocomplete="current-password"
          placeholder="Enter your password"
          required
          :aria-invalid="Boolean(errors.password)"
          :aria-describedby="
            errors.password ? 'login-password-error' : undefined
          "
        />

        <button
          class="password-toggle"
          type="button"
          :aria-label="showPassword ? 'Hide password' : 'Show password'"
          aria-controls="login-password"
          @click="showPassword = !showPassword"
        >
          <EyeOff v-if="showPassword" :size="18" aria-hidden="true" />
          <Eye v-else :size="18" aria-hidden="true" />
        </button>
      </div>

      <p
        v-if="errors.password"
        id="login-password-error"
        class="field-error"
        role="alert"
      >
        {{ errors.password }}
      </p>
    </div>

    <p v-if="authStatus" class="auth-status" role="status" aria-live="polite">
      {{ authStatus }}
    </p>

    <BaseButton class="login-submit" type="submit" :disabled="isBusy">
      {{ isBusy ? 'Signing in...' : 'Sign in' }}
    </BaseButton>

    <div class="forgot-link">
      <RouterLink to="/forgot-password">
        Forgot password?
      </RouterLink>
    </div>
  </form>
</template>

<style scoped>
.login-form {
  display: grid;
  gap: 20px;
}

.form-field {
  min-width: 0;
}

.form-field label {
  display: block;
  margin-bottom: 7px;
  color: var(--text);
  font-size: 13px;
  font-weight: 700;
}

.input-wrap {
  display: flex;
  align-items: center;
  min-width: 0;
  min-height: 48px;
  gap: 11px;
  padding: 0 13px;
  border: 1px solid #dcd9ed;
  border-radius: 10px;
  background: #fff;
  color: var(--muted);
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}

.input-wrap:focus-within {
  border-color: #9a68ed;
  box-shadow: 0 0 0 3px #ece4ff;
}

.input-wrap--invalid {
  border-color: var(--danger);
}

.field-icon {
  flex: none;
}

.input-wrap input {
  width: 100%;
  min-width: 0;
  padding: 12px 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--text);
}

.input-wrap input::placeholder {
  color: #9999ae;
}

.password-toggle {
  display: grid;
  flex: none;
  place-items: center;
  width: 34px;
  height: 34px;
  margin-right: -6px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--muted);
}

.password-toggle:hover {
  background: #f5f2ff;
  color: var(--purple);
}

.password-toggle:focus-visible {
  outline: 2px solid var(--purple);
  outline-offset: 2px;
}

.field-error {
  margin: 6px 0 0;
  color: var(--danger);
  font-size: 12px;
}

.auth-status {
  margin: 0;
  padding: 12px 14px;
  border: 1px solid #e7dcfd;
  border-radius: 10px;
  background: #f7f2ff;
  color: #6245a7;
  font-size: 12px;
}

:deep(.login-submit) {
  width: 100%;
  min-height: 48px;
}

.forgot-link {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}

.forgot-link a {
  color: var(--purple);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.forgot-link a:hover {
  text-decoration: underline;
}
</style>
