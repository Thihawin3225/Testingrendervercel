import { http } from '@/services/http'

import type { StudentEnrollment } from '@/features/academic/interfaces/studentEnrollment.interface'

export interface CreateStudentWithEnrollmentPayload
  extends CreateStudentPayload {
  academic_year_id: number
  year_level_id: number
  major_id: number
  semester_id: number
  status: 'active' | 'completed' | 'withdrawn' | 'suspended'
}

export interface CreateStudentWithEnrollmentResponse {
  message: string
  student: AdminStudent
  enrollment: StudentEnrollment
}

export async function createStudentWithEnrollment(
  payload: CreateStudentWithEnrollmentPayload,
): Promise<CreateStudentWithEnrollmentResponse> {
  const response = await http.post<CreateStudentWithEnrollmentResponse>(
    '/admin/students/with-enrollment',
    payload,
  )

  return response.data
}

export type StudentStatus = 'active' | 'suspended' | 'inactive'

export interface AdminStudent {
  id: number
  name: string
  email: string
  student_number: string | null
  role: 'student'
  status: StudentStatus
  created_at: string
}

export interface CreateStudentPayload {
  name: string
  email: string
  student_number: string
  password: string
  password_confirmation: string
}

export interface LaravelPaginatedResponse<T> {
  data: T[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface StudentUpdatePayload {
  name?: string
  email?: string
  password?: string
  status?: StudentStatus
}

export async function fetchStudents(
  params: { page?: number; per_page?: number } = {},
): Promise<LaravelPaginatedResponse<AdminStudent>> {
  const response = await http.get<LaravelPaginatedResponse<AdminStudent>>(
    '/admin/students',
    { params },
  )

  return response.data
}

export async function fetchAllStudents(): Promise<AdminStudent[]> {
  const firstPage = await fetchStudents({ page: 1, per_page: 100 })
  const students = [...firstPage.data]

  if (firstPage.meta.last_page <= 1) {
    return students
  }

  const pageRequests = Array.from(
    { length: firstPage.meta.last_page - 1 },
    (_, index) => fetchStudents({ page: index + 2, per_page: 100 }),
  )

  const remainingPages = await Promise.all(pageRequests)

  remainingPages.forEach((page) => {
    students.push(...page.data)
  })

  return students
}

export async function createStudent(
  payload: CreateStudentPayload,
): Promise<AdminStudent> {
  const response = await http.post<{ user: AdminStudent }>('/admin/students', payload)

  return response.data.user
}

export async function updateStudent(
  studentId: number,
  payload: StudentUpdatePayload,
): Promise<AdminStudent> {
  const response = await http.patch<{ user: AdminStudent }>(
    `/admin/users/${studentId}`,
    payload,
  )

  return response.data.user
}

export async function deleteStudent(studentId: number): Promise<void> {
  await http.delete(`/admin/users/${studentId}`)
}
