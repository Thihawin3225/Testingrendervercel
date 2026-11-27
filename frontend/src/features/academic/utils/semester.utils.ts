import type { SemesterFormData } from '../interfaces/semester.interface'

export function formatSemesterDate(date: string | null): string {
  if (!date) return '-'

  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

export function getEmptySemesterForm(): SemesterFormData {
  return {
    name: '',
    sort_order: 1,
  }
}