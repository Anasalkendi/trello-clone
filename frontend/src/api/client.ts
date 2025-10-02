import axios from 'axios';

const getDefaultApiUrl = () => {
  if (window.location.hostname === 'localhost') {
    return 'http://localhost:8000/api';
  }

  return `${window.location.origin}/api`;
};

const API_BASE_URL = import.meta.env.VITE_API_URL ?? getDefaultApiUrl();

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 419) {
      window.dispatchEvent(new CustomEvent('session:expired'));
    }
    return Promise.reject(error);
  }
);
