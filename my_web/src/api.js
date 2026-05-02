const API_BASE = '/GCST_Track_System/actions';

async function fetchJson(endpoint, options = {}) {
  const response = await fetch(`${API_BASE}/${endpoint}`, {
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      ...(options.headers || {})
    },
    ...options
  });

  const text = await response.text();
  let payload = null;

  try {
    payload = text ? JSON.parse(text) : null;
  } catch (error) {
    throw new Error(response.statusText || 'Unexpected response format');
  }

  if (!response.ok) {
    const message = payload?.message || payload?.error || response.statusText;
    throw new Error(message || 'Request failed');
  }

  return payload;
}

async function postJson(endpoint, data = {}) {
  return fetchJson(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
}

export { fetchJson, postJson, API_BASE };
