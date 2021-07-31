export function stringifyError(error: any) {
  console.error({ error })
  if (error?.isAxiosError) return stringifyAxiosError(error)
  return error
}

export function stringifyAxiosError(error: any) {
  return error?.response?.data?.message || error?.response?.statusText || error?.message || error
}

export function errorTitle(error: any) {
  if (error?.response?.status) return `Error ${error.response.status}`
  return 'Error'
}

export function waitXtime(ms = 1000) {
  return new Promise((resolve) => {
    setTimeout(() => resolve(ms), ms)
  })
}
