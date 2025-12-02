export function useTTS() {
  const isSpeaking = ref(false)
  const isSupported = ref(false)

  onMounted(() => {
    isSupported.value = 'speechSynthesis' in window
  })

  function speak(text: string, options?: {
    lang?: string
    rate?: number
    pitch?: number
    volume?: number
  }): Promise<void> {
    return new Promise((resolve, reject) => {
      if (!isSupported.value) {
        reject(new Error('Text-to-Speech tidak didukung'))
        return
      }

      const utterance = new SpeechSynthesisUtterance(text)
      utterance.lang = options?.lang || 'id-ID'
      utterance.rate = options?.rate || 0.9
      utterance.pitch = options?.pitch || 1
      utterance.volume = options?.volume || 1

      utterance.onstart = () => {
        isSpeaking.value = true
      }

      utterance.onend = () => {
        isSpeaking.value = false
        resolve()
      }

      utterance.onerror = (event) => {
        isSpeaking.value = false
        reject(event)
      }

      speechSynthesis.speak(utterance)
    })
  }

  function speakQueueNumber(queueNumber: string, poliName: string): Promise<void> {
    const spokenNumber = queueNumber.split('').join('... ')
    const text = `Nomor antrian... ${spokenNumber}... silakan ke... ${poliName}`
    return speak(text)
  }

  function stop(): void {
    if (isSupported.value) {
      speechSynthesis.cancel()
      isSpeaking.value = false
    }
  }

  return {
    isSpeaking,
    isSupported,
    speak,
    speakQueueNumber,
    stop,
  }
}
