import { defineStore } from 'pinia'

interface State {
  show: boolean
  message: string
  color: string
}

export const useNotificationsStore = defineStore('notifications', {
  state: (): State => ({
    show: false,
    message: '',
    color: 'success',
  }),

  actions: {
    notify (message: string, color = 'success') {
      this.message = message
      this.color = color
      this.show = true
    },
  },
})
