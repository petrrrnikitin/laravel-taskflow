import axios from 'axios'

export default axios.create({
    baseURL: '/api/v1',
    withCredentials: true,
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
})
