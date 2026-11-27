import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'

const loginZodSchema = z.object({
  email: z
    .string()
    .trim()
    .min(1, 'Enter your university email.')
    .email('Enter a valid email address.'),
  password: z.string().min(1, 'Enter your password.'),
})

export const loginSchema = toTypedSchema(loginZodSchema)
