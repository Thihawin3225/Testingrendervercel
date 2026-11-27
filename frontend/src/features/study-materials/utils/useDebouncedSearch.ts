import { onBeforeUnmount, ref, watch } from 'vue'

const DEBOUNCE_DELAY = 1000

export function useDebouncedSearch() {
  const searchInput = ref('')
  const searchTerm = ref('')
  let timeout: ReturnType<typeof setTimeout> | undefined

  watch(searchInput, (value) => {
    if (timeout) clearTimeout(timeout)

    timeout = setTimeout(() => {
      searchTerm.value = value.trim()
    }, DEBOUNCE_DELAY)
  })

  function clearSearch(): void {
    if (timeout) clearTimeout(timeout)
    searchInput.value = ''
    searchTerm.value = ''
  }

  onBeforeUnmount(() => {
    if (timeout) clearTimeout(timeout)
  })

  return { searchInput, searchTerm, clearSearch }
}
