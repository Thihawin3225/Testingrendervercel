export function formatYearLevelDate(date: string | null): string {
  if (!date) return '-'

  const parsedDate = new Date(date)

  if (Number.isNaN(parsedDate.getTime())) {
    return date
  }

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(parsedDate)
}

export function getEmptyYearLevelForm() {
  return {
    name: '',
    sort_order: 1,
  }
}