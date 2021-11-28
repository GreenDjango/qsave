import axios, { AxiosInstance } from 'axios'
import { usePopup } from '@/store'

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

export type QnotePartial = { tags: string; url?: string; text?: string; code?: string; code_lang?: string }

export type Stats = {
  total_qnotes: number
  last_qnote: Qnote
  last_update: string
  all_tags: { [key: string]: number }
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
      // const account = useAccount()
      // if (account.token) {
      //   config.headers.Authorization = `Bearer ${account.token}`
      // }
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
    const form = new FormData()
    Object.keys(qnote).forEach((key) => {
      form.append(key, (<any>qnote)[key])
    })
    return this.instance.post('/qnote', form)
  }
}

const apiServer = new ApiServer()

export { ApiServer, apiServer }
