import { http } from '@/services/http'
import type { LaravelPaginatedResponse } from '../interfaces/apiResponse.interface'
import type { SubjectOffering } from '../interfaces/subjectOffering.interface'

export async function fetchSubjectOfferings(): Promise<
  LaravelPaginatedResponse<SubjectOffering>
> {
  const response = await http.get<LaravelPaginatedResponse<SubjectOffering>>(
    '/academic/subject-offerings',
    { params: { per_page: 100 } },
  )

  return response.data
}
