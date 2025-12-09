import axios from 'axios';

// Set CSRF token dari meta tag
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

// Atau pakai cookie (lebih aman)
axios.defaults.withCredentials = true;

export default axios;