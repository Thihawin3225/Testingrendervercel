import type { StudyMaterial } from '../interfaces/studyMaterial.interface'

/** Preview-only data. Replace with the authenticated My Uploads API response. */
export const myUploadsPreview: StudyMaterial[] = [
  {
    id: 1,
    subject_offering_id: 14,
    title: 'Database relationships and normalization',
    description: 'Lecture notes covering relational design and normalization basics.',
    original_filename: 'database-relationships.pdf',
    file_type: 'pdf',
    mime_type: 'application/pdf',
    file_size: 2_458_624,
    status: 'active',
    created_at: '2026-11-10T09:30:00.000Z',
    updated_at: '2026-11-10T09:30:00.000Z',
  },
  {
    id: 2,
    subject_offering_id: 18,
    title: 'Introduction to Laravel routing',
    description: 'A concise slide deck about routes, controllers, and middleware.',
    original_filename: 'laravel-routing-slides.pptx',
    file_type: 'pptx',
    mime_type: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    file_size: 5_120_000,
    status: 'active',
    created_at: '2026-11-08T14:15:00.000Z',
    updated_at: '2026-11-08T14:15:00.000Z',
  },
  {
    id: 3,
    subject_offering_id: 22,
    title: 'Programming fundamentals study guide',
    description: 'Practice examples and revision questions for the end-of-semester exam.',
    original_filename: 'programming-study-guide.docx',
    file_type: 'docx',
    mime_type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    file_size: 843_776,
    status: 'active',
    created_at: '2026-11-05T11:00:00.000Z',
    updated_at: '2026-11-05T11:00:00.000Z',
  },
]
