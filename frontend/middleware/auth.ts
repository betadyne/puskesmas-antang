export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()

  await authStore.initAuth()

  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }

  if (to.path.startsWith('/admin') && !authStore.isAdmin) {
    return navigateTo('/dashboard')
  }
})
