import type {
  SubjectOfferingFormData,
} from '../interfaces/subjectOffering.interface'

export function getEmptySubjectOfferingForm(): SubjectOfferingFormData {
  return {
    academic_year_id: 0,
    year_level_id: 0,
    major_id: 0,
    semester_id: 0,
    subject_id: 0,
  }
}

export function validateSubjectOfferingForm(
  formData: SubjectOfferingFormData,
): string | null {
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

  if (!formData.subject_id) {
    return 'Please select a subject.'
  }

  return null
}
