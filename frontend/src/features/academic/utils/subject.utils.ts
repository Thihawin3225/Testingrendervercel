import type {
  SubjectFormData,
} from '../interfaces/subject.interface'

export function getEmptySubjectForm(): SubjectFormData {
  return {
    subject_code: '',
    subject_name: '',
    description: '',
  }
}

export function formatSubjectDate(
  date: string | null,
): string {
  if (!date) return '—'

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}
