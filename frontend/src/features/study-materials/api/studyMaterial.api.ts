import { http } from '@/services/http'
import type {
  LaravelPaginatedResponse,
  LaravelResourceResponse,
} from '../../peer-learning/interfaces/apiResponse.interface'
import type {
  CreateStudyMaterialPayload,
  StudyMaterial,
  StudyMaterialFilters,
  UpdateStudyMaterialPayload,
} from '../interfaces/studyMaterial.interface'

export async function fetchStudyMaterials(
  filters: StudyMaterialFilters = {},
): Promise<LaravelPaginatedResponse<StudyMaterial>> {
  const response = await http.get<LaravelPaginatedResponse<StudyMaterial>>(
    '/study-materials',
    { params: filters },
  )

  return response.data
}

export async function fetchMyStudyMaterials(
  filters: StudyMaterialFilters = {},
): Promise<LaravelPaginatedResponse<StudyMaterial>> {
  const response = await http.get<LaravelPaginatedResponse<StudyMaterial>>(
    '/study-materials/mine',
    { params: filters },
  )

  return response.data
}

export async function uploadStudyMaterial(
  payload: CreateStudyMaterialPayload,
): Promise<StudyMaterial> {
  const formData = new FormData()
  if (payload.subject_offering_id !== null) {
    formData.append('subject_offering_id', String(payload.subject_offering_id))
  }
  formData.append('title', payload.title)
  if (payload.description) formData.append('description', payload.description)
  formData.append('file', payload.file)

  const response = await http.post<LaravelResourceResponse<StudyMaterial>>(
    '/study-materials',
    formData,
  )

  return response.data.data
}

export async function updateStudyMaterial(
  payload: UpdateStudyMaterialPayload,
): Promise<StudyMaterial> {
  const formData = new FormData()
  formData.append('_method', 'PATCH')
  if (payload.subject_offering_id !== undefined) {
    formData.append(
      'subject_offering_id',
      payload.subject_offering_id === null
        ? ''
        : String(payload.subject_offering_id),
    )
  }
  if (payload.title !== undefined) formData.append('title', payload.title)
  if (payload.description !== undefined) {
    formData.append('description', payload.description ?? '')
  }
  if (payload.file) formData.append('file', payload.file)

  const response = await http.post<LaravelResourceResponse<StudyMaterial>>(
    `/study-materials/${payload.id}`,
    formData,
  )

  return response.data.data
}

export async function deleteStudyMaterial(id: number): Promise<void> {
  await http.delete(`/study-materials/${id}`)
}

export async function downloadStudyMaterial(id: number): Promise<Blob> {
  const response = await http.get<Blob>(`/study-materials/${id}/download`, {
    responseType: 'blob',
  })

  return response.data
}

export async function fetchStudyMaterialPreviewUrl(id: number): Promise<string> {
  const response = await http.get<{ url: string }>(
    `/study-materials/${id}/preview-url`,
  )

  return response.data.url
}

export async function fetchBookmarkedStudyMaterials(
  filters: StudyMaterialFilters = {},
): Promise<LaravelPaginatedResponse<StudyMaterial>> {
  const response = await http.get<LaravelPaginatedResponse<StudyMaterial>>(
    '/study-materials/bookmarks',
    { params: filters },
  )

  return response.data
}

export async function removeStudyMaterialBookmark(id: number): Promise<void> {
  await http.delete(`/study-materials/${id}/bookmark`)
}
