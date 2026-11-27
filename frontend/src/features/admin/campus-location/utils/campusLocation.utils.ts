import type {
  CampusLocationFormData,
} from '../interfaces/campusLocation.interface'

export function getEmptyCampusLocationForm(): CampusLocationFormData {
  return {
    name: '',
    building: '',
    description: '',
    is_active: true,
  }
}

export function formatCampusLocationDate(
  date: string | null,
): string {
  if (!date) return '—'

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

export function getCampusLocationDisplayName(
  name: string,
  building: string | null,
): string {
  if (!building) return name

  return `${name} - ${building}`
}
