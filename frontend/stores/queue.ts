import type { Queue, QueueStats, Poli } from '~/types'

interface DashboardResponse {
  message: string
  data: {
    current_queue: any | null
    waiting_queues: any[]
    history_queues: any[]
    statistics: {
      total_waiting: number
      total_served: number
      total_skipped: number
    }
  }
}

interface QueueActionResponse {
  message: string
  data: any
}

export const useQueueStore = defineStore('queue', () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const currentQueue = ref<any | null>(null)
  const waitingList = ref<any[]>([])
  const historyList = ref<any[]>([])
  const stats = ref<QueueStats>({
    total_waiting: 0,
    total_served: 0,
    total_skipped: 0,
  })
  const poliList = ref<Poli[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function fetchPoli(): Promise<void> {
    try {
      const response = await $fetch<{ message: string; data: Poli[] }>(
        `${config.public.apiBase}/poli`
      )

      if (response.data) {
        poliList.value = response.data
      }
    } catch (err) {
      console.error('Error fetching poli:', err)
    }
  }

  async function fetchQueues(): Promise<void> {
    isLoading.value = true
    error.value = null

    try {
      const response = await $fetch<DashboardResponse>(
        `${config.public.apiBase}/dashboard/queues`,
        {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        }
      )

      if (response.data) {
        currentQueue.value = response.data.current_queue
        waitingList.value = response.data.waiting_queues || []
        historyList.value = response.data.history_queues || []
        stats.value = {
          total_waiting: response.data.statistics?.total_waiting || 0,
          total_served: response.data.statistics?.total_served || 0,
          total_skipped: response.data.statistics?.total_skipped || 0,
        }
      }
    } catch (err: any) {
      error.value = err.data?.message || err.message
      console.error('Error fetching queues:', err)
    } finally {
      isLoading.value = false
    }
  }

  async function callNext(): Promise<any | null> {
    isLoading.value = true
    try {
      const response = await $fetch<QueueActionResponse>(
        `${config.public.apiBase}/queue/call-next`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        }
      )

      if (response.data) {
        currentQueue.value = response.data
        // Remove from waiting list
        waitingList.value = waitingList.value.filter(q => q.id !== response.data.id)
        stats.value.total_waiting = Math.max(0, stats.value.total_waiting - 1)
        return response.data
      }
      return null
    } catch (err: any) {
      error.value = err.data?.message || err.message
      throw new Error(err.data?.message || 'Gagal memanggil antrian')
    } finally {
      isLoading.value = false
    }
  }

  async function callSpecific(queueId: number): Promise<any | null> {
    isLoading.value = true
    try {
      const response = await $fetch<QueueActionResponse>(
        `${config.public.apiBase}/queue/${queueId}/call`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        }
      )

      if (response.data) {
        currentQueue.value = response.data
        waitingList.value = waitingList.value.filter(q => q.id !== queueId)
        stats.value.total_waiting = Math.max(0, stats.value.total_waiting - 1)
        return response.data
      }
      return null
    } catch (err: any) {
      error.value = err.data?.message || err.message
      throw new Error(err.data?.message || 'Gagal memanggil antrian')
    } finally {
      isLoading.value = false
    }
  }

  async function recall(queueId: number): Promise<void> {
    try {
      await $fetch<QueueActionResponse>(
        `${config.public.apiBase}/queue/${queueId}/recall`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        }
      )
    } catch (err: any) {
      error.value = err.data?.message || err.message
      throw new Error(err.data?.message || 'Gagal memanggil ulang')
    }
  }

  async function finish(queueId: number): Promise<void> {
    isLoading.value = true
    try {
      const response = await $fetch<QueueActionResponse>(
        `${config.public.apiBase}/queue/${queueId}/finish`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        }
      )

      if (response.data) {
        // Add to history
        historyList.value.unshift(response.data)
        stats.value.total_served++
      }
      currentQueue.value = null
    } catch (err: any) {
      error.value = err.data?.message || err.message
      throw new Error(err.data?.message || 'Gagal menyelesaikan antrian')
    } finally {
      isLoading.value = false
    }
  }

  async function skip(queueId: number): Promise<void> {
    isLoading.value = true
    try {
      await $fetch<QueueActionResponse>(
        `${config.public.apiBase}/queue/${queueId}/skip`,
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`,
          },
        }
      )

      currentQueue.value = null
      stats.value.total_skipped++
    } catch (err: any) {
      error.value = err.data?.message || err.message
      throw new Error(err.data?.message || 'Gagal melewati antrian')
    } finally {
      isLoading.value = false
    }
  }

  function updateQueueFromWebsocket(queue: Queue, action: string): void {
    switch (action) {
      case 'created':
        waitingList.value.push(queue)
        stats.value.total_waiting++
        break
      case 'called':
        currentQueue.value = queue
        waitingList.value = waitingList.value.filter(q => q.id !== queue.id)
        break
      case 'finished':
        if (currentQueue.value?.id === queue.id) {
          currentQueue.value = null
        }
        historyList.value.unshift(queue)
        stats.value.total_served++
        stats.value.total_waiting = Math.max(0, stats.value.total_waiting - 1)
        break
      case 'skipped':
        if (currentQueue.value?.id === queue.id) {
          currentQueue.value = null
        }
        stats.value.total_skipped++
        stats.value.total_waiting = Math.max(0, stats.value.total_waiting - 1)
        break
    }
  }

  return {
    currentQueue,
    waitingList,
    historyList,
    stats,
    poliList,
    isLoading,
    error,
    fetchPoli,
    fetchQueues,
    callNext,
    callSpecific,
    recall,
    finish,
    skip,
    updateQueueFromWebsocket,
  }
})
