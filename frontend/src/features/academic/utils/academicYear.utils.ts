export function formatAcademicYearDate(date: string): string {
  if (!date) return ''

  const parsedDate = new Date(`${date}T00:00:00`)

  if (Number.isNaN(parsedDate.getTime())) {
    return date
  }

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(parsedDate)
}

export function getEmptyAcademicYearForm() {
  return {
    name: '',
    start_date: '',
    end_date: '',
    is_active: true,
  }
}
