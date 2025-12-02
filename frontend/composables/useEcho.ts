import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import type { WebsocketQueueEvent } from '~/types'

let echoInstance: Echo | null = null

export function useEcho() {
  const config = useRuntimeConfig()
  const queueStore = useQueueStore()

  function initEcho(): Echo {
    if (echoInstance) return echoInstance

    if (typeof window !== 'undefined') {
      (window as any).Pusher = Pusher

      echoInstance = new Echo({
        broadcaster: 'pusher',
        key: config.public.pusherKey,
        cluster: config.public.pusherCluster,
        forceTLS: true,
        disableStats: true,
      })
    }

    return echoInstance!
  }

  function subscribeToQueueChannel(poliId: number, callbacks?: {
    onCreated?: (data: WebsocketQueueEvent) => void
    onCalled?: (data: WebsocketQueueEvent) => void
    onFinished?: (data: WebsocketQueueEvent) => void
  }): void {
    const echo = initEcho()
    if (!echo) return

    echo.channel(`queue.${poliId}`)
      .listen('queue.created', (data: WebsocketQueueEvent) => {
        queueStore.updateQueueFromWebsocket(data.queue, 'created')
        callbacks?.onCreated?.(data)
      })
      .listen('queue.called', (data: WebsocketQueueEvent) => {
        queueStore.updateQueueFromWebsocket(data.queue, 'called')
        callbacks?.onCalled?.(data)
      })
      .listen('queue.finished', (data: WebsocketQueueEvent) => {
        queueStore.updateQueueFromWebsocket(data.queue, 'finished')
        callbacks?.onFinished?.(data)
      })
      .listen('queue.updated', (data: WebsocketQueueEvent) => {
        queueStore.updateQueueFromWebsocket(data.queue, data.action)
      })
  }

  function subscribeToDisplayChannel(poliId: number, callbacks: {
    onCalled: (data: WebsocketQueueEvent) => void
  }): void {
    const echo = initEcho()
    if (!echo) return

    echo.channel(`display.${poliId}`)
      .listen('queue.called', (data: WebsocketQueueEvent) => {
        callbacks.onCalled(data)
      })
  }

  function unsubscribeFromChannel(channelName: string): void {
    if (echoInstance) {
      echoInstance.leave(channelName)
    }
  }

  function disconnect(): void {
    if (echoInstance) {
      echoInstance.disconnect()
      echoInstance = null
    }
  }

  return {
    initEcho,
    subscribeToQueueChannel,
    subscribeToDisplayChannel,
    unsubscribeFromChannel,
    disconnect,
  }
}
