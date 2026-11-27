import type {
  StudentEnrollmentFormData,
} from '../interfaces/studentEnrollment.interface'

export function getEmptyStudentEnrollmentForm(): StudentEnrollmentFormData {
  return {
    user_id: 0,
    academic_year_id: 0,
    year_level_id: 0,
    major_id: 0,
    semester_id: 0,
    status: 'active',
  }
}

export function validateStudentEnrollmentForm(
  formData: StudentEnrollmentFormData,
): string | null {
  if (!formData.user_id) {
    return 'Please select a student.'
  }

  if (!formData.academic_year_id) {
    return 'Please select an academic year.'
  }

  if (!formData.year_level_id) {
    return 'Please select a year level.'
  }

  if (!formData.major_id) {
    return 'Please select a major.'
  }

  if (!formData.semester_id) {
    return 'Please select a semester.'
  }

  if (
    ![
      'active',
      'completed',
      'withdrawn',
      'suspended',
    ].includes(formData.status)
  ) {
    return 'Please select a valid enrollment status.'
  }

  return null
}
