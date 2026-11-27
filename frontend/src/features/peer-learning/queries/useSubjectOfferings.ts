import { useQuery } from '@tanstack/vue-query'
import { fetchSubjectOfferings } from '../api/subjectOffering.api'
import { peerLearningQueryKeys } from './peerLearningQueryKeys'

export function useSubjectOfferings() {
  return useQuery({
    queryKey: peerLearningQueryKeys.subjectOfferings(),
    queryFn: fetchSubjectOfferings,
  })
}
