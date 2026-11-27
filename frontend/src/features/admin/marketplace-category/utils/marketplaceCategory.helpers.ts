import type {
  MarketplaceCategoryFormData,
} from '../interfaces/marketplaceCategory.interface'

export function getEmptyMarketplaceCategoryForm(): MarketplaceCategoryFormData {
  return {
    name: '',
    description: '',
  }
}

export function formatMarketplaceCategoryDate(
  date: string | null,
): string {
  if (!date) return '—'

  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}
