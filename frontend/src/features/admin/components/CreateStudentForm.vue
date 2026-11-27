<script setup lang="ts">
import { watch } from "vue";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { z } from "zod";

import BaseButton from "@/components/ui/BaseButton.vue";

import {
  DEFAULT_STUDENT_PASSWORD,
  ROLL_NUMBER_PREFIX,
  UNIVERSITY_EMAIL_DOMAIN,
} from "@/constants/campus";

import type { CreateStudentPayload } from "@/features/admin/api/adminStudents.api";

const props = defineProps<{
  submitting: boolean;
  successMessage?: string;
}>();

const emit = defineEmits<{
  submit: [payload: CreateStudentPayload];
}>();

// ==========================================
// EMAIL NORMALIZATION
// ==========================================

const normalizeEmailUsername = (
  value: string | null | undefined,
): string => {
  const trimmed = (value ?? "").trim();

  if (!trimmed.includes("@")) {
    return trimmed;
  }

  const [localPart = ""] = trimmed.split("@");

  return localPart.trim();
};

// ==========================================
// VALIDATION
// ==========================================

const emailUsernameSchema = z
  .string()
  .trim()
  .min(1, "Enter the university email username.")
  .refine(
    (value) => !/\s/.test(value),
    "University email username cannot contain spaces.",
  )
  .refine((value) => {
    const raw = value ?? "";
    const [localPart = ""] = raw.split("@");

    const normalized = raw.includes("@")
      ? localPart.trim()
      : raw.trim();

    if (!normalized || !/^[A-Za-z0-9._-]+$/.test(normalized)) {
      return false;
    }

    if (
      raw.includes("@") &&
      !raw.toLowerCase().endsWith(UNIVERSITY_EMAIL_DOMAIN)
    ) {
      return false;
    }

    return true;
  }, `Use a username for ${UNIVERSITY_EMAIL_DOMAIN} only.`);

const rollNumberSchema = z
  .string()
  .trim()
  .min(1, "Enter the roll number.")
  .max(
    50 - ROLL_NUMBER_PREFIX.length,
    `Roll number must be ${50 - ROLL_NUMBER_PREFIX.length} digits or fewer.`,
  )
  .regex(
    /^\d+$/,
    "Only numbers are allowed — the prefix is added automatically.",
  );

const formSchema = toTypedSchema(
  z.object({
    name: z.string().trim().min(1, "Enter the student name.").max(120),
    emailUsername: emailUsernameSchema,
    rollNumber: rollNumberSchema,
  }),
);

// ==========================================
// FORM
// ==========================================

const {
  defineField,
  errors,
  handleSubmit,
  resetForm,
} = useForm({
  validationSchema: formSchema,
  initialValues: {
    name: "",
    emailUsername: "",
    rollNumber: "",
  },
});

const [name, nameAttrs] = defineField("name");

const [emailUsername, emailUsernameAttrs] =
  defineField("emailUsername");

const [rollNumber, rollNumberAttrs] =
  defineField("rollNumber");

// ==========================================
// STUDENT NUMBER
// ==========================================

function buildStudentNumber(rawRollNumber: string): string {
  return `${ROLL_NUMBER_PREFIX}${rawRollNumber.trim()}`;
}

// ==========================================
// SUBMIT
// ==========================================

const onSubmit = handleSubmit((values) => {
  const username = normalizeEmailUsername(
    values.emailUsername,
  );

  emit("submit", {
    name: values.name,
    email: `${username}${UNIVERSITY_EMAIL_DOMAIN}`,
    student_number: buildStudentNumber(values.rollNumber),
    password: DEFAULT_STUDENT_PASSWORD,
    password_confirmation: DEFAULT_STUDENT_PASSWORD,
  });
});

// ==========================================
// ROLL NUMBER WATCH
// ==========================================

watch(rollNumber, (value) => {
  if (value && /\D/.test(value)) {
    rollNumber.value = value.replace(/\D/g, "");
  }
});

// ==========================================
// EXPOSE RESET FORM
// ==========================================

defineExpose({ resetForm });
</script>

<template>
  <form
    class="create-student-form"
    novalidate
    @submit="onSubmit"
  >
    <!-- FULL NAME -->
    <label class="form-field">
      <span>Full Name</span>

      <input
        v-model="name"
        v-bind="nameAttrs"
        type="text"
        autocomplete="name"
        required
        :aria-invalid="Boolean(errors.name)"
      />

      <small
        v-if="errors.name"
        class="field-error"
        role="alert"
      >
        {{ errors.name }}
      </small>
    </label>

    <!-- UNIVERSITY EMAIL -->
    <label class="form-field">
      <span>University Email Username</span>

      <div class="email-input-group">
        <input
          v-model="emailUsername"
          v-bind="emailUsernameAttrs"
          type="text"
          autocomplete="username"
          required
          :aria-invalid="Boolean(errors.emailUsername)"
        />

        <span class="input-affix" aria-hidden="true">
          {{ UNIVERSITY_EMAIL_DOMAIN }}
        </span>
      </div>

      <small
        v-if="errors.emailUsername"
        class="field-error"
        role="alert"
      >
        {{ errors.emailUsername }}
      </small>
    </label>

    <!-- ROLL NUMBER -->
    <label class="form-field">
      <span>Roll No</span>

      <div class="email-input-group">
        <span
          class="input-affix input-affix--lead"
          aria-hidden="true"
        >
          {{ ROLL_NUMBER_PREFIX }}
        </span>

        <input
          v-model="rollNumber"
          v-bind="rollNumberAttrs"
          type="text"
          inputmode="numeric"
          required
          :aria-invalid="Boolean(errors.rollNumber)"
        />
      </div>

      <small
        v-if="errors.rollNumber"
        class="field-error"
        role="alert"
      >
        {{ errors.rollNumber }}
      </small>
    </label>

    <!-- ACTIONS -->
    <footer class="form-actions">
      <BaseButton
        type="submit"
        :disabled="props.submitting"
      >
        {{
          props.submitting
            ? "Creating..."
            : "Create Student"
        }}
      </BaseButton>
    </footer>

    <p
      v-if="props.successMessage"
      class="success-message"
      role="status"
    >
      {{ props.successMessage }}
    </p>
  </form>
</template>

<style scoped>
.create-student-form {
  display: grid;
  gap: 14px;
}

.form-field {
  display: grid;
  gap: 7px;
  color: var(--text);
  font-size: 13px;
  font-weight: 700;
}

.email-input-group {
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 43px;
  border: 1px solid #ddd6f7;
  border-radius: 10px;
  background: #fff;
  overflow: hidden;
}

.email-input-group:focus-within {
  border-color: #9a68ed;
  outline: 3px solid #ece4ff;
}

.email-input-group input {
  flex: 1;
  min-width: 0;
  border: 0;
  background: transparent;
  padding: 0 12px;
  min-height: 43px;
  color: var(--text);
  font: inherit;
  font-weight: 400;
}

.input-affix {
  flex-shrink: 0;
  padding: 0 12px;
  border-left: 1px solid #e7ddff;
  background: #f8f5ff;
  color: var(--muted);
  font-size: 12px;
  font-weight: 700;
}

.input-affix--lead {
  border-left: 0;
  border-right: 1px solid #e7ddff;
}

.form-field input {
  width: 100%;
  min-height: 43px;
  padding: 0 12px;
  border: 1px solid #ddd6f7;
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  font: inherit;
  font-weight: 400;
}

.email-input-group input {
  width: auto;
  border: 0;
  border-radius: 0;
}

.form-field input:focus {
  border-color: #9a68ed;
  outline: 3px solid #ece4ff;
}

.email-input-group:focus-within {
  outline: none;
}

.form-field input[aria-invalid="true"] {
  border-color: var(--danger);
}

.email-input-group:has(input[aria-invalid="true"]) {
  border-color: var(--danger);
}

.field-error {
  color: var(--danger);
  font-size: 12px;
  font-weight: 400;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
}

.success-message {
  margin: 0;
  padding: 10px 12px;
  border: 1px solid #d7f6e7;
  border-radius: 10px;
  background: #ecfff5;
  color: #0f7d50;
  font-weight: 700;
}
</style>
