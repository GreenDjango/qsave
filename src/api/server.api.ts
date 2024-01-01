import axios, { AxiosInstance } from 'axios'
import { useAuth, usePopup } from '@/store'

const DEFAULT_URL = 'http://localhost:8081/api/'

export type Qnote = {
  id: number
  date: string
  parseDate?: Date
  tags: string[]
  url?: string
  text?: string
  code?: string
  code_lang?: string
}

export type QnotePartial = { tags: string[]; url?: string; text?: string; code?: string; code_lang?: string }

export type Tags = { [key: string]: number }

export type Stats = {
  total_qnotes: number
  last_qnote: Qnote | null
  older_qnote: Qnote | null
  last_update: string
  all_tags: Tags
  db_size: number
}

class ApiServer {
  url: string
  instance: AxiosInstance

  constructor() {
    this.url = process.env.VUE_APP_API_SERVER_URL || DEFAULT_URL
    this.instance = axios.create({
      // headers: { 'API-Key': process.env.VUE_APP_API_SERVER_KEY || 'foobar' },
      baseURL: this.url,
      // withCredentials: true,
    })
    this.instance.interceptors.request.use(function (config) {
      const auth = useAuth()
      if (auth.apiKey) {
        config.headers['API-key'] = `${auth.apiKey}`
      }
      return config
    })
    this.instance.interceptors.response.use(
      function (response) {
        return response
      },
      function (error) {
        if (error.response?.status === 401) {
          const popup = usePopup()
          popup.$patch({ is401: true })
        }
        return Promise.reject(error)
      }
    )
  }

  changeUrl(url = DEFAULT_URL) {
    this.url = url
    this.instance.defaults.baseURL = this.url
  }

  // -----
  //      [ Authentification ]
  // -----
  logIn(email: string, password: string) {
    return this.instance.post('/signin', { email, password })
  }

  // -----
  //      [ Data ]
  // -----
  fetchStats() {
    return this.instance.get('/stats')
  }

  fetchTags() {
    return this.instance.get('/tags')
  }

  fetchQnotes() {
    return this.instance.get('/qnotes')
  }

  fetchQnote(qnoteID: number) {
    return this.instance.get('/qnote', { params: { qnoteID } })
  }

  searchQnotes(q?: string, tags?: string) {
    return this.instance.get('/search', { params: { q, tags } })
  }

  createQnote(qnote: QnotePartial) {
    const form = ApiServer.qnoteToForm(qnote)
    return this.instance.post('/qnote/create', form)
  }

  updateQnote(qnoteID: number, qnote: QnotePartial) {
    const form = ApiServer.qnoteToForm(qnote)
    return this.instance.post('/qnote/update', form, { params: { qnoteID } })
  }

  deleteQnote(qnoteID: number) {
    return this.instance.delete('/qnote', { params: { qnoteID } })
  }

  // -----
  //      [ Utils ]
  // -----
  static populateQnote(...qnotes: Qnote[]) {
    for (const qnote of qnotes) {
      if (qnote.date) qnote.parseDate = new Date(qnote.date)
    }
  }

  static qnoteToForm(qnote: QnotePartial) {
    if (qnote.tags) qnote.tags = ApiServer.tagsToString(qnote.tags) as any
    const form = new FormData()
    Object.keys(qnote).forEach((key) => {
      const value = (qnote as any)[key]
      if (value === null || value === undefined) return
      form.append(key, value)
    })
    return form
  }

  static tagsToString(tags: string[]) {
    return tags
      .map((str) => str.trim())
      .filter((str) => str)
      .sort()
      .join(';')
  }
}

const apiServer = new ApiServer()

export { ApiServer, apiServer }
