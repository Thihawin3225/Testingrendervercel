<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import { Camera, CircleUserRound, Mail, ShieldCheck, UserRound, X } from '@lucide/vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import PageHero from '@/components/layout/PageHero.vue'
import { authApi } from '@/modules/auth/api/authApi'
import { profileApi } from '../api/profile.api'
import type { ProfileUser } from '../interfaces/profile.interface'

const user = ref<ProfileUser | null>(null)
const avatarUrl = ref('')
const bio = ref('')
const draftBio = ref('')
const isLoading = ref(true)
const isUploading = ref(false)
const isSavingBio = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const avatarInput = ref<HTMLInputElement | null>(null)
const profileDialog = ref<HTMLDialogElement | null>(null)

const initials = computed(() => {
  const words = user.value?.name.trim().split(/\s+/).filter(Boolean) ?? []
  return words.slice(0, 2).map((word) => word[0]?.toUpperCase()).join('') || '?'
})

const memberSince = computed(() => {
  if (!user.value?.created_at) return 'Unavailable'
  const date = new Date(user.value.created_at)
  return Number.isNaN(date.valueOf())
    ? 'Unavailable'
    : new Intl.DateTimeFormat(undefined, { dateStyle: 'long' }).format(date)
})

async function loadProfile(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    user.value = await authApi.me()
    avatarUrl.value = user.value.avatar_url ?? ''
    bio.value = user.value.bio ?? ''
  } catch {
    errorMessage.value = 'Your profile could not be loaded. Please try again.'
  } finally {
    isLoading.value = false
  }
}

async function saveBio(): Promise<void> {
  errorMessage.value = ''
  successMessage.value = ''
  isSavingBio.value = true

  try {
    bio.value = (await profileApi.updateBio(draftBio.value)) ?? ''
    if (user.value) {
      user.value.bio = bio.value
    }
    successMessage.value = 'Your bio was saved.'
    closeProfileEditor()
  } catch {
    errorMessage.value = 'Your bio could not be saved. Please try again.'
  } finally {
    isSavingBio.value = false
  }
}

async function openProfileEditor(): Promise<void> {
  draftBio.value = bio.value
  errorMessage.value = ''
  successMessage.value = ''
  await nextTick()
  profileDialog.value?.showModal()
}

function closeProfileEditor(): void {
  profileDialog.value?.close()
}

function closeProfileEditorOnBackdrop(event: MouseEvent): void {
  if (event.target === profileDialog.value) {
    closeProfileEditor()
  }
}

function openAvatarPicker(): void {
  avatarInput.value?.click()
}

async function uploadAvatar(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  errorMessage.value = ''
  successMessage.value = ''

  if (!file) return

  const acceptedTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!acceptedTypes.includes(file.type)) {
    errorMessage.value = 'Choose a JPG, PNG, or WebP image.'
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    errorMessage.value = 'The image must be 2 MB or smaller.'
    return
  }

  isUploading.value = true
  try {
    const result = await profileApi.uploadAvatar(file)
    avatarUrl.value = result.avatar_url
    successMessage.value = 'Your profile photo was updated.'
  } catch {
    errorMessage.value = 'Your profile photo could not be uploaded. Please try again.'
  } finally {
    isUploading.value = false
  }
}

onMounted(() => {
  void loadProfile()
})
</script>

<template>
  <section class="profile-page" aria-labelledby="profile-page-title">
    <PageHero
      eyebrow="MY ACCOUNT"
      title="My Profile"
      title-id="profile-page-title"
      description="View your account information and share a little about yourself."
      :icon="UserRound"
    />

    <p v-if="errorMessage && !profileDialog?.open" class="feedback feedback--error" role="alert">
      {{ errorMessage }}
    </p>
    <p v-if="successMessage" class="feedback feedback--success" role="status">
      {{ successMessage }}
    </p>

    <div v-if="isLoading" class="loading-state" role="status">Loading your profile…</div>

    <div v-else-if="user" class="profile-grid">
      <BaseCard as="section" padded class="profile-card profile-actions-card" aria-label="Profile photo and actions">
        <div class="profile-identity">
          <div class="avatar-wrap">
            <img v-if="avatarUrl" class="profile-avatar" :src="avatarUrl" alt="Profile photo" />
            <span v-else class="profile-avatar avatar-fallback" aria-label="No profile photo">
              <CircleUserRound v-if="!initials" :size="42" />
              <span v-else>{{ initials }}</span>
            </span>
            <input
              ref="avatarInput"
              class="visually-hidden"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              aria-label="Choose a profile photo"
              @change="uploadAvatar"
            />
            <button
              class="avatar-camera"
              type="button"
              aria-label="Update profile photo"
              title="Update profile photo"
              :disabled="isUploading"
              @click="openAvatarPicker"
            >
              <Camera :size="16" aria-hidden="true" />
            </button>
          </div>

          <div class="identity-copy">
            <h3>{{ user.name }}</h3>
            <p>{{ user.email }}</p>
          </div>
        </div>

        <div class="profile-actions">
          <BaseButton type="button" variant="outline" @click="openProfileEditor">
            Edit Profile
          </BaseButton>
          <BaseButton
            type="button"
            variant="ghost"
          >
            Manage Mentor Profile
          </BaseButton>
        </div>

        <p class="profile-note">
          Year 1 can only choose CST. Years 2–4 choose CS or CT. Enrollment is normally managed through the academic module.
        </p>
      </BaseCard>

      <BaseCard as="section" padded class="profile-card" aria-labelledby="student-info-title">
        <div class="section-heading">
          <span class="section-icon" aria-hidden="true"><UserRound :size="19" /></span>
          <div>
            <h2 id="student-info-title">Student Information</h2>
            <p>Your CampusBridge account details.</p>
          </div>
        </div>

        <dl class="info-list">
          <div>
            <dt>Full name</dt>
            <dd>{{ user.name }}</dd>
          </div>
          <div>
            <dt>University email</dt>
            <dd>{{ user.email }}</dd>
          </div>
          <div>
            <dt>Account role</dt>
            <dd class="capitalize">{{ user.role }}</dd>
          </div>
          <div>
            <dt>Account status</dt>
            <dd class="capitalize">{{ user.status }}</dd>
          </div>
          <div>
            <dt>Member since</dt>
            <dd>{{ memberSince }}</dd>
          </div>
          <div>
            <dt>Bio</dt>
            <dd class="bio-value" :class="{ 'bio-preview--empty': !bio }">
              {{ bio || 'No bio added yet.' }}
            </dd>
          </div>
        </dl>
      </BaseCard>
    </div>

    <dialog
      ref="profileDialog"
      class="profile-dialog"
      aria-labelledby="profile-editor-title"
      @click="closeProfileEditorOnBackdrop"
      @cancel.prevent="closeProfileEditor"
    >
      <section v-if="user" class="profile-editor">
        <header class="editor-heading">
          <div>
            <p class="editor-eyebrow">MY ACCOUNT</p>
            <h2 id="profile-editor-title">Edit Profile</h2>
          </div>
          <button
            class="editor-close"
            type="button"
            aria-label="Close profile editor"
            @click="closeProfileEditor"
          >
            <X :size="19" aria-hidden="true" />
          </button>
        </header>

        <form class="editor-form" @submit.prevent="saveBio">
          <p class="read-only-note">
            Your account details are read-only here. You can add or update your optional bio.
          </p>

          <dl class="read-only-details">
            <div><dt>Full name</dt><dd>{{ user.name }}</dd></div>
            <div><dt>University email</dt><dd>{{ user.email }}</dd></div>
          </dl>

          <div class="bio-field">
            <label for="profile-bio">Bio <span>(optional)</span></label>
            <textarea
              id="profile-bio"
              v-model="draftBio"
              maxlength="1000"
              rows="6"
              :disabled="isSavingBio"
              placeholder="Tell your campus community a little about yourself, your interests, or what you are learning."
            />
            <span class="bio-count">{{ draftBio.length }} / 1000 characters</span>
          </div>

          <p v-if="errorMessage" class="editor-error" role="alert">{{ errorMessage }}</p>

          <footer class="editor-actions">
            <BaseButton type="button" variant="outline" :disabled="isSavingBio" @click="closeProfileEditor">
              Cancel
            </BaseButton>
            <BaseButton type="submit" :disabled="isSavingBio">
              {{ isSavingBio ? 'Saving…' : 'Save changes' }}
            </BaseButton>
          </footer>
        </form>
      </section>
    </dialog>
  </section>
</template>

<style scoped>
.profile-page {
  display: grid;
  gap: 17px;
  color: var(--text);
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  align-items: start;
  gap: 17px;
}

.profile-card {
  min-width: 0;
  min-height: 100%;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 23px;
}

.section-icon {
  display: grid;
  width: 43px;
  height: 43px;
  flex: none;
  place-items: center;
  border-radius: 13px;
  background: #f1eaff;
  color: #7039dd;
}

.section-heading h2,
.section-heading p {
  margin: 0;
}

.section-heading h2 {
  color: #182246;
  font-size: 18px;
}

.section-heading p {
  margin-top: 5px;
  color: var(--muted);
  font-size: 12px;
  line-height: 1.5;
}

.avatar-wrap {
  position: relative;
  display: grid;
  width: 88px;
  height: 88px;
  flex: none;
}

.profile-avatar {
  display: grid;
  width: 88px;
  height: 88px;
  place-items: center;
  overflow: hidden;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-fallback {
  background: linear-gradient(140deg, #e7dcff, #d4c9ff);
  color: #6035bc;
  font-size: 26px;
  font-weight: 800;
}

.avatar-camera {
  position: absolute;
  right: -2px;
  bottom: -2px;
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  padding: 0;
  border: 2px solid #fff;
  border-radius: 50%;
  background: linear-gradient(125deg, #8233f4, #5727ce);
  color: #fff;
  box-shadow: 0 2px 8px #24124a30;
  cursor: pointer;
}

.avatar-camera:hover:not(:disabled) {
  filter: brightness(1.08);
}

.avatar-camera:focus-visible {
  outline: 2px solid #6c2dea;
  outline-offset: 3px;
}

.avatar-camera:disabled {
  cursor: wait;
  opacity: 0.6;
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  clip-path: inset(50%);
}

.info-list {
  display: grid;
  margin: 12px 0 0;
}

.info-list > div {
  display: grid;
  grid-template-columns: minmax(105px, 0.7fr) minmax(0, 1fr);
  gap: 12px;
  padding: 13px 0;
  border-bottom: 1px solid #f0edf6;
}

.info-list > div:last-child {
  border-bottom: 0;
}

.info-list dt {
  color: var(--muted);
  font-size: 12px;
}

.info-list dd {
  display: flex;
  align-items: center;
  gap: 7px;
  min-width: 0;
  margin: 0;
  color: #272342;
  font-size: 12px;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.info-list dd svg {
  flex: none;
  color: #7a59cd;
}

.capitalize {
  text-transform: capitalize;
}

.bio-preview--empty {
  color: var(--muted);
}

.profile-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-start;
  gap: 12px;
  margin-top: 17px;
}

.profile-note {
  margin: 16px 0 0;
  padding: 14px 17px;
  border: 1px solid #e5d9ff;
  border-radius: 13px;
  background: #f7f2ff;
  color: #6844bb;
  font-size: 13px;
  line-height: 1.7;
}

.profile-actions-card {
  display: grid;
  align-content: start;
  justify-items: start;
  gap: 12px;
  min-height: 100%;
}

.profile-actions-card .profile-actions {
  margin-top: 4px;
}

.profile-actions-card .profile-note {
  margin-top: 4px;
}

.profile-identity {
  display: flex;
  align-items: center;
  gap: 17px;
  min-width: 0;
}

.identity-copy {
  display: grid;
  justify-items: start;
  gap: 7px;
  min-width: 0;
}

.identity-copy h3,
.identity-copy p {
  margin: 0;
  overflow-wrap: anywhere;
}

.identity-copy h3 {
  color: #172244;
  font-size: 18px;
}

.identity-copy p {
  color: var(--muted);
  font-size: 12px;
}

.info-list dd.bio-value {
  display: block;
  font-weight: 500;
  line-height: 1.6;
  white-space: pre-wrap;
}

.profile-dialog {
  position: fixed;
  inset: 0;
  width: min(600px, calc(100vw - 28px));
  max-width: none;
  max-height: calc(100dvh - 28px);
  margin: auto;
  padding: 0;
  overflow: auto;
  border: 1px solid #e7e0f5;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 24px 80px #21134230;
}

.profile-dialog::backdrop {
  background: #17112b75;
  backdrop-filter: blur(6px);
}

.profile-editor {
  overflow: hidden;
}

.editor-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  padding: 20px 22px;
  background: linear-gradient(105deg, #f3edff, #e6ddff);
}

.editor-eyebrow {
  margin: 0 0 6px;
  color: #6a43cc;
  font-size: 10px;
  font-weight: 850;
  letter-spacing: 0.13em;
}

.editor-heading h2 {
  margin: 0;
  color: #172244;
  font-size: 21px;
}

.editor-close {
  display: grid;
  width: 36px;
  height: 36px;
  flex: none;
  place-items: center;
  border: 0;
  border-radius: 10px;
  background: #fff;
  color: #6335c5;
  cursor: pointer;
}

.editor-close:focus-visible {
  outline: 2px solid #6c2dea;
  outline-offset: 2px;
}

.editor-form {
  display: grid;
  gap: 17px;
  padding: 22px;
}

.read-only-note {
  margin: 0;
  color: var(--muted);
  font-size: 12px;
  line-height: 1.5;
}

.read-only-details {
  display: grid;
  gap: 10px;
  margin: 0;
}

.read-only-details > div {
  display: grid;
  grid-template-columns: 140px minmax(0, 1fr);
  gap: 12px;
  padding: 11px 12px;
  border: 1px solid #efedf6;
  border-radius: 10px;
  background: #fcfbff;
}

.read-only-details dt {
  color: var(--muted);
  font-size: 12px;
}

.read-only-details dd {
  min-width: 0;
  margin: 0;
  color: #272342;
  font-size: 12px;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.bio-field {
  display: grid;
  gap: 8px;
}

.bio-field label {
  color: #292345;
  font-size: 13px;
  font-weight: 750;
}

.bio-field label span {
  color: var(--muted);
  font-size: 11px;
  font-weight: 500;
}

.bio-field textarea {
  width: 100%;
  min-height: 150px;
  resize: vertical;
  padding: 12px 13px;
  border: 1px solid #ded7f2;
  border-radius: 11px;
  outline: none;
  background: #fff;
  color: var(--text);
  font: inherit;
  font-size: 13px;
  line-height: 1.6;
}

.bio-field textarea:focus {
  border-color: #966cf0;
  box-shadow: 0 0 0 3px #966cf01a;
}

.bio-field textarea:disabled {
  background: #f8f7fb;
}

.bio-count {
  justify-self: end;
  color: var(--muted);
  font-size: 11px;
}

.editor-error {
  margin: 0;
  color: #ae2b40;
  font-size: 12px;
}

.editor-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 3px;
}

.feedback,
.loading-state {
  margin: 0;
  padding: 12px 14px;
  border-radius: 11px;
  font-size: 13px;
}

.feedback--error {
  border: 1px solid #facbd2;
  background: #fff5f6;
  color: #ae2b40;
}

.feedback--success {
  border: 1px solid #c9efde;
  background: #f0fcf6;
  color: #16734f;
}

.loading-state {
  border: 1px solid #e8e3f3;
  background: #fff;
  color: var(--muted);
}

@media (max-width: 900px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 520px) {
  .profile-card {
    padding: 16px;
  }

  .profile-avatar {
    width: 72px;
    height: 72px;
  }

  .avatar-wrap {
    width: 72px;
    height: 72px;
  }

  .info-list > div {
    grid-template-columns: 1fr;
    gap: 5px;
  }

  .profile-actions {
    align-items: stretch;
    flex-direction: column;
  }

  .profile-actions :deep(button) {
    width: 100%;
  }

  .editor-form {
    padding: 17px;
  }

  .read-only-details > div {
    grid-template-columns: 1fr;
    gap: 5px;
  }
}
</style>
